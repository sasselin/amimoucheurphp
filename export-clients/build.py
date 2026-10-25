# /// script
# requires-python = ">=3.9"
# dependencies = ["argon2-cffi>=23.1"]
# ///
"""Met en forme l'extraction brute d'export.php : clients.json (contrat de l'import du nouveau site), rapport.md
et l'archive du service à la clientèle.

L'extraction brute arrive par l'entrée standard et n'est jamais écrite sur disque : elle contient les empreintes
md5 des mots de passe. Dans clients.json, chaque empreinte est enveloppée dans argon2id (voir README.md).

Usage : <export.php> | uv run export-clients/build.py - <dossier_sortie> <recategorisation.json>
            [--comptes-depuis=AAAA-MM-JJ] [--paniers-depuis=AAAA-MM-JJ]
"""
import calendar
import csv
import json
import os
import re
import sys
from collections import Counter, defaultdict
from concurrent.futures import ProcessPoolExecutor
from datetime import date, timedelta
from decimal import ROUND_HALF_UP, Decimal
from pathlib import Path

from argon2 import PasswordHasher, Type

DIR = Path(__file__).resolve().parent
SCHEMA_VERSION = 1
COMPTES_DEPUIS = "2023-01-01"
PANIERS_JOURS = 365  # défaut de --paniers-depuis : un an avant l'extraction
# Mêmes paramètres que le nouveau site (argon2 0.6, Params::default : m = 19 MiB, t = 2, p = 1) :
# le hash final, après la première connexion, a la même forme
HASHER = PasswordHasher(time_cost=2, memory_cost=19456, parallelism=1, hash_len=32, salt_len=16, type=Type.ID)
PASSWORD_SCHEME = "prestashop_md5+argon2id"
MD5 = re.compile(r"^[0-9a-f]{32}$")
SHIPPING_TAX = "Taxes sur la livraison"
# Clés qui ne doivent jamais sortir (table ps_ccofflinepayment) : garde-fou sur toute la sortie
FORBIDDEN_KEYS = {"pan", "cvc", "pin", "cedula", "tipo_tarjeta", "nombre_titular", "mes_caducidad", "ano_caducidad"}
CARD = re.compile(r"(?<!\d)(?:\d[ -]?){12,18}\d(?!\d)")
CARD_MASK = "[numéro de carte masqué]"


def cents(v):
    return int((Decimal(v or 0) * 100).quantize(Decimal(1), rounding=ROUND_HALF_UP))


def norm_email(e):
    return (e or "").strip().lower() or None


def luhn(digits):
    s = 0
    for i, c in enumerate(reversed(digits)):
        d = int(c) * (2 if i % 2 else 1)
        s += d - 9 if d > 9 else d
    return s % 10 == 0


def mask_cards(text, counter):
    """Masque les numéros de carte tapés dans un message (13 à 19 chiffres, clé de Luhn valide)."""
    if not text:
        return text

    def sub(m):
        digits = re.sub(r"\D", "", m.group())
        if 13 <= len(digits) <= 19 and luhn(digits):
            counter["cartes masquées"] += 1
            return CARD_MASK
        return m.group()
    return CARD.sub(sub, text)


def wrap_md5(md5hex):
    return HASHER.hash(md5hex)


def address(a):
    if not a:
        return None
    return {k: a[k] for k in ("first_name", "last_name", "company", "line1", "line2", "postal_code", "city",
                              "region_code", "country_code", "phone")} | {"instructions": a["other"]}


def order_taxes(o, taxes):
    """Taxes de la commande par (nom, taux). Retourne (taxes, exact).

    Le total de taxe de la commande (TTC - HT) fait foi : le détail lui est toujours égal. PrestaShop garde aussi un
    détail par taxe, pour les lignes (ps_order_detail_tax) et la livraison (ps_order_invoice_tax), mais il n'est pas
    fiable : calculé avant les rabais, et faux sur des milliers de lignes. Il ne sert qu'à répartir le total au prorata,
    ce qui donne le bon partage TPS/TVQ. exact : le détail brut, rabais retirés, tombait déjà sur le total à 2 cents près.
    Une ligne sans détail compte sous « Taxes »."""
    by = defaultdict(Decimal)

    def key(tid):
        t = taxes.get(str(tid)) or {"name": f"Taxe {tid}", "rate": None}
        return t["name"], t["rate"]
    for line in o["lines"]:
        for t in line["taxes"]:
            by[key(t["tax_id"])] += Decimal(t["amount"])
        if not line["taxes"]:  # anciennes lignes sans détail : leur taxe, sans nom
            by[("Taxes", None)] += Decimal(line["total_price_tax_incl"]) - Decimal(line["total_price_tax_excl"])
    ship_tax = Decimal(o["total_shipping_tax_incl"]) - Decimal(o["total_shipping_tax_excl"])
    if o["shipping_taxes"]:
        for t in o["shipping_taxes"]:
            by[key(t["tax_id"])] += Decimal(t["amount"])
    elif ship_tax:
        by[(SHIPPING_TAX, None)] += ship_tax
    target = cents(o["total_paid_tax_incl"]) - cents(o["total_paid_tax_excl"])
    gross = sum(by.values())
    disc_tax = Decimal(o["total_discounts_tax_incl"]) - Decimal(o["total_discounts_tax_excl"])
    if not target:
        return [], not gross or abs(cents(gross - disc_tax)) <= 2
    if gross <= 0:
        return [{"name": "Taxes", "rate": None, "amount": target}], False
    exact = abs(cents(gross - disc_tax) - target) <= 2
    # Répartition au prorata, arrondie au plus fort reste : la somme vaut exactement target
    shares = sorted(((target * v / gross, k) for k, v in by.items()), key=lambda x: -x[0])
    out = [{"name": k[0], "rate": k[1], "amount": int(sh)} for sh, k in shares]
    rest = target - sum(t["amount"] for t in out)
    for i in sorted(range(len(out)), key=lambda i: -(shares[i][0] - int(shares[i][0])))[:rest]:
        out[i]["amount"] += 1
    return [t for t in out if t["amount"]], exact


def months_before(d, months):
    """La date (AAAA-MM-JJ) « months » mois avant d, ramenée au dernier jour du mois au besoin."""
    y, m = divmod(d.year * 12 + d.month - 1 - months, 12)
    return date(y, m + 1, min(d.day, calendar.monthrange(y, m + 1)[1])).isoformat()


def load_catalogue(path):
    """Fonction (produit, variante PrestaShop) -> id de la fiche du nouveau catalogue (après fusion), ou None.

    Seules les fiches actives y sont. Une variante est cherchée par son id ; un produit simple (sans variante) par
    son propre id, s'il est resté simple : le nouveau site lui donne une variante par défaut sans prestashop_id."""
    rec = json.loads(Path(path).read_text(encoding="utf-8"))
    owner, simple = {}, set()
    for p in rec["products"]:
        for v in p["variant_ids"]:
            owner[v] = p["id"]
        if not p["variant_ids"]:
            simple.add(p["id"])

    def find(product_id, variant_id):
        if variant_id:
            return owner.get(variant_id)
        return product_id if product_id in simple else None
    return find, rec["meta"]


def account_cart(carts, since, find, n):
    """Le panier à redonner au compte : comme PrestaShop à la connexion (PS_CART_FOLLOWING, Cart::lastNoneOrderedCart),
    le plus récent de ses paniers non commandés, même vide. Gardé s'il a été modifié depuis since et qu'il lui reste
    une ligne encore vendue. Les quantités d'une même variante (une ligne par adresse de livraison) sont additionnées."""
    if not carts:
        return None
    cart = max(carts, key=lambda c: (c["updated_at"] or "", c["id"]))
    if not cart["lines"]:
        return None
    if (cart["updated_at"] or "") < since:
        n["paniers trop anciens (non migrés)"] += 1
        return None
    lines = {}
    for li in cart["lines"]:
        owner = find(li["product_id"], li["variant_id"])
        if owner is None:
            n["lignes de panier retirées (produit plus vendu)"] += 1
            continue
        key = (li["product_id"], li["variant_id"])
        if key in lines:
            lines[key]["quantity"] += li["quantity"]
        else:
            lines[key] = {"prestashop_product_id": li["product_id"], "prestashop_variant_id": li["variant_id"],
                          "product_id": owner, "quantity": li["quantity"], "added_at": li["added_at"]}
    lines = [li for li in lines.values() if li["quantity"] > 0]
    if not lines:
        n["paniers sans produit encore vendu (non migrés)"] += 1
        return None
    return {"prestashop_id": cart["id"], "updated_at": cart["updated_at"], "currency": cart["currency"], "lines": lines}


def build(raw, since, rec_path, carts_since=None):
    statuts = json.loads((DIR / "statuts.json").read_text(encoding="utf-8"))
    status_of = {ps: k for k, s in statuts["statuts"].items() for ps in s["prestashop"]}
    find, rec_meta = load_catalogue(rec_path)
    extracted = date.fromisoformat(raw["meta"]["generated_at"][:10])
    if carts_since is None:
        carts_since = (extracted - timedelta(days=PANIERS_JOURS)).isoformat()
    auto = statuts["fermeture_auto"]
    close_before = months_before(extracted, auto["apres_mois"])
    n = Counter()

    # -------- comptes : un par courriel, fiches fusionnées
    customers = {c["id"]: c for c in raw["customers"]}
    addresses = {a["id"]: a for a in raw["addresses"]}
    addr_by_customer = defaultdict(list)
    for a in raw["addresses"]:
        if not a["deleted"]:
            addr_by_customer[a["customer_id"]].append(a)
    orders_by_customer = defaultdict(list)
    for o in raw["orders"]:
        orders_by_customer[o["customer_id"]].append(o)
    carts_by_customer = defaultdict(list)
    for c in raw.get("carts", []):
        carts_by_customer[c["customer_id"]].append(c)

    groups = defaultdict(list)
    for c in raw["customers"]:
        if c["deleted"]:
            n["fiches supprimées (non migrées)"] += 1
        elif not c["active"] and not c["guest"]:
            n["comptes désactivés (non migrés)"] += 1
        elif norm_email(c["email"]):
            groups[norm_email(c["email"])].append(c)

    accounts, account_of, to_hash = [], {}, []
    created_by_year, dormant_by_year, carts_by_year = Counter(), Counter(), Counter()
    for email, rows in groups.items():
        regs = [c for c in rows if not c["guest"]]
        has_orders = any(orders_by_customer[c["id"]] for c in rows)
        if len(rows) > 1:
            n["courriels avec plusieurs fiches (fusionnées)"] += 1
        if not regs:
            n["courriels d'invités seulement (pas de compte)"] += 1
            continue
        main = max(regs, key=lambda c: (c["updated_at"] or "", c["id"]))
        created = min(c["created_at"] or "9999" for c in regs)
        if not has_orders and created < since:
            dormant_by_year[created[:4]] += 1
            n["comptes dormants écartés"] += 1
            continue
        for c in rows:
            account_of[c["id"]] = email
        created_by_year[created[:4]] += 1
        seen, book = set(), []
        for c in sorted(regs, key=lambda c: c["id"] != main["id"]):
            for a in addr_by_customer[c["id"]]:
                ad = address(a)
                sig = json.dumps({k: (v or "").strip().lower() for k, v in ad.items()}, sort_keys=True)
                if sig not in seen:
                    seen.add(sig)
                    book.append({"label": a["alias"], **ad, "prestashop_id": a["id"]})
        news = [c for c in rows if c["newsletter"]]
        # Seulement les fiches de compte : un invité ne se reconnecte jamais, PrestaShop ne lui redonne rien
        cart = account_cart([k for c in regs for k in carts_by_customer[c["id"]]], carts_since, find, n)
        if cart:
            carts_by_year[cart["updated_at"][:7]] += 1
        md5 = (main["passwd"] or "").lower()
        if MD5.match(md5):
            to_hash.append((len(accounts), md5))
        else:
            n["comptes sans empreinte md5 valide (réinitialisation obligatoire)"] += 1
        accounts.append({
            "email": email,
            "first_name": main["first_name"],
            "last_name": main["last_name"],
            "locale": main["lang"] if main["lang"] in ("fr", "en") else "fr",
            "created_at": created,
            "password": None,
            "newsletter": {"consent": bool(news), "at": max((c["newsletter_at"] or "" for c in news), default="") or None,
                           "source": "compte" if news else None},
            "prestashop_ids": sorted(c["id"] for c in rows),
            "addresses": book,
            "cart": cart,
        })

    # Abonnés du bloc infolettre (ps_newsletter) et invités abonnés : consentement sur le compte s'il existe,
    # sinon liste à part
    by_email = {a["email"]: a for a in accounts}
    subscribers = {}
    for s in raw["newsletter"]:
        e = norm_email(s["email"])
        if not e:
            continue
        if e in by_email:
            nl = by_email[e]["newsletter"]
            if not nl["consent"]:
                nl.update(consent=True, at=s["at"], source="formulaire")
        else:
            subscribers.setdefault(e, {"email": e, "at": s["at"], "source": "formulaire"})
    for e, rows in groups.items():
        if e not in by_email:
            for c in rows:
                if c["newsletter"]:
                    subscribers.setdefault(e, {"email": e, "at": c["newsletter_at"], "source": "commande invité"})

    # -------- commandes
    out_orders, tax_ko, ht_ko = [], [], []
    by_status, open_orders, totals = Counter(), [], defaultdict(lambda: [0, 0])
    auto_closed = Counter()
    lines_total = lines_reorder = 0
    for o in raw["orders"]:
        if o["state"] not in status_of:
            sys.exit(f"Statut PrestaShop {o['state']} ({o['state_name']}) absent de statuts.json")
        status = status_of[o["state"]]
        c = customers.get(o["customer_id"])
        acc = account_of.get(o["customer_id"])
        if acc:
            n["commandes rattachées à un compte"] += 1
        elif not c:
            n["commandes sans fiche client"] += 1
        elif c["deleted"]:
            n["commandes d'une fiche supprimée"] += 1
        elif c["guest"]:
            n["commandes d'invités"] += 1
        else:
            n["commandes d'un compte désactivé"] += 1
        taxes, exact = order_taxes(o, raw["taxes"])
        if not exact:
            tax_ko.append(o["id"])
        sub, disc = cents(o["total_products"]), cents(o["total_discounts_tax_excl"])
        ship, wrap = cents(o["total_shipping_tax_excl"]), cents(o["total_wrapping_tax_excl"])
        total_ht, total = cents(o["total_paid_tax_excl"]), cents(o["total_paid_tax_incl"])
        if abs(sub - disc + ship + wrap - total_ht) > 1:
            ht_ko.append(o["id"])
        lines = []
        for li in o["lines"]:
            owner = find(li["product_id"], li["variant_id"])
            lines.append({
                "prestashop_product_id": li["product_id"],
                "prestashop_variant_id": li["variant_id"],
                "product_id": owner,
                "reorderable": owner is not None,
                "name": li["name"],
                "sku": li["sku"],
                "ean13": li["ean13"],
                "quantity": li["quantity"],
                "unit_price": cents(li["unit_price_tax_excl"]),
                "line_total": cents(li["total_price_tax_excl"]),
            })
            lines_total += 1
            lines_reorder += owner is not None
        inv = f"{raw['meta']['invoice_prefix'] or ''}{o['invoice_number']:06d}" if o["invoice_number"] else None
        paid = cents(o["total_paid_real"])
        # Vieille commande restée ouverte : fermée ici, sans toucher PrestaShop (un changement de statut y envoie un courriel)
        inferred = status in statuts["ouverts"] and o["placed_at"] < close_before
        if inferred:
            status = auto["payee"] if inv and paid > 0 else auto["non_payee"]
            auto_closed[(o["placed_at"][:4], status)] += 1
        out_orders.append({
            "prestashop_id": o["id"],
            "reference": o["reference"],
            "invoice_number": inv,
            "invoice_date": o["invoice_date"],
            "placed_at": o["placed_at"],
            "account": acc,
            "email": norm_email(c["email"]) if c else None,
            "locale": o["lang"] if o["lang"] in ("fr", "en") else "fr",
            "status": status,
            "prestashop_status": o["state_name"] or "(aucun statut)",
            "status_inferred": inferred,
            "currency": o["currency"],
            "subtotal": sub,
            "discount": disc,
            "shipping": ship,
            "wrapping": wrap,
            "tax": total - total_ht,
            "total": total,
            "paid": paid,
            "taxes": taxes,
            "taxes_exact": exact,
            "payment": o["payment"],
            "carrier": o["carrier"],
            "shipping_address": address(addresses.get(o["address_delivery_id"])),
            "billing_address": address(addresses.get(o["address_invoice_id"])),
            "lines": lines,
            "adjustments": [{"name": r["name"], "amount": cents(r["value_tax_excl"]), "amount_tax_incl": cents(r["value_tax_incl"]),
                             "free_shipping": r["free_shipping"]} for r in o["cart_rules"]],
            "notes": [{**m, "text": mask_cards(m["text"], n)} for m in o["messages"]],
        })
        by_status[(status, (o["state_name"] or "(aucun statut)") + (" (fermée par la migration)" if inferred else ""))] += 1
        if status in statuts["ouverts"]:
            open_orders.append((o["placed_at"], o["id"], o["state_name"], total, o["currency"], bool(inv)))
        t = totals[(o["placed_at"][:4], o["currency"])]
        t[0] += 1
        t[1] += total

    # -------- rapprochement avec les totaux calculés par MySQL
    check = {(str(r["year"]), r["currency"]): (int(r["orders"]), cents(r["total"])) for r in raw["meta"]["check"]["by_year_currency"]}
    mine = {k: tuple(v) for k, v in totals.items()}
    if check != mine:
        diff = sorted(set(check.items()) ^ set(mine.items()))
        sys.exit(f"Rapprochement : les totaux par année et devise ne correspondent pas à MySQL : {diff}")
    if lines_total != raw["meta"]["check"]["lines"]:
        sys.exit(f"Rapprochement : {lines_total} lignes lues, MySQL en compte {raw['meta']['check']['lines']}")

    # -------- mots de passe (argon2id coûte ~20 ms chacun : en parallèle)
    with ProcessPoolExecutor() as pool:
        for (i, _), h in zip(to_hash, pool.map(wrap_md5, [m for _, m in to_hash], chunksize=64)):
            accounts[i]["password"] = {"scheme": PASSWORD_SCHEME, "hash": h}

    # -------- service à la clientèle : archive, pas migré
    archive = []
    for th in raw["threads"]:
        for m in th["messages"]:
            archive.append({"fil": th["id"], "ouvert_le": th["created_at"], "statut": th["status"], "courriel": th["email"],
                            "commande": th["order_id"] or "", "produit": th["product_id"] or "", "date": m["at"],
                            "de": m["from"], "prive": "oui" if m["private"] else "", "message": mask_cards(m["text"], n)})

    doc = {
        "meta": {
            "schema_version": SCHEMA_VERSION,
            "generated_at": raw["meta"]["generated_at"],
            "source": raw["meta"]["source"],
            "catalogue_generated_at": rec_meta.get("catalogue_generated_at"),
            "comptes_depuis": since,
            "fermeture_auto_avant": close_before,
            "paniers_depuis": carts_since,
            "password_scheme": PASSWORD_SCHEME,
            "amounts": "cents ; prix des lignes, sous-total, rabais et livraison HT ; tax et total TTC ; devise de la commande",
            "statuses": {k: {"fr": s["fr"], "en": s["en"]} for k, s in statuts["statuts"].items()},
            "counts": {"accounts": len(accounts), "orders": len(out_orders), "lines": lines_total,
                       "carts": sum(a["cart"] is not None for a in accounts),
                       "newsletter_subscribers": len(subscribers)},
        },
        "accounts": accounts,
        "orders": out_orders,
        "newsletter_subscribers": sorted(subscribers.values(), key=lambda s: s["email"]),
    }
    stats = {
        "n": n, "created_by_year": created_by_year, "dormant_by_year": dormant_by_year, "by_status": by_status,
        "carts_by_month": carts_by_year,
        "open_orders": sorted(open_orders), "auto_closed": auto_closed, "totals": totals, "tax_ko": tax_ko, "ht_ko": ht_ko,
        "lines_total": lines_total, "lines_reorder": lines_reorder, "raw_counts": raw["meta"]["counts"],
        "accounts": accounts, "subscribers": subscribers,
    }
    return doc, archive, stats


def check_forbidden(obj, path="$"):
    """Garde-fou : aucune donnée de ps_ccofflinepayment dans la sortie."""
    if isinstance(obj, dict):
        for k, v in obj.items():
            if k.lower() in FORBIDDEN_KEYS or "ccoffline" in k.lower():
                sys.exit(f"Garde-fou : clé interdite « {k} » dans {path}")
            check_forbidden(v, f"{path}.{k}")
    elif isinstance(obj, list):
        for i, v in enumerate(obj):
            check_forbidden(v, f"{path}[{i}]")


def money(c):
    return f"{c / 100:,.2f}".replace(",", " ").replace(".", ",")


def write_report(path, doc, stats):
    n, m = stats["n"], doc["meta"]
    auto = json.loads((DIR / "statuts.json").read_text(encoding="utf-8"))["fermeture_auto"]
    acc = stats["accounts"]
    L = [
        "# Comptes clients et commandes : ce qui passe au nouveau site",
        "",
        f"Extraction `{m['source']}` du {m['generated_at']}. Comptes sans commande gardés s'ils ont été créés depuis le "
        f"{m['comptes_depuis']} (option `--comptes-depuis`). Aucun nom ni courriel dans ce rapport.",
        "",
        "## En bref",
        "",
        f"- **{len(acc)} comptes** passent au nouveau site, sur {stats['raw_counts']['customers']} fiches dans PrestaShop. "
        f"Ils se connectent avec le même courriel et le même mot de passe.",
        f"- **{m['counts']['orders']} commandes** passent, toutes : un client retrouve son historique, la boutique aussi.",
        f"- {stats['lines_reorder']} lignes de commande sur {stats['lines_total']} "
        f"({100 * stats['lines_reorder'] // max(stats['lines_total'], 1)} %) pointent vers un produit encore vendu : "
        "elles peuvent être recommandées en un clic.",
        f"- {sum(a['newsletter']['consent'] for a in acc)} comptes ont accepté l'infolettre, "
        f"plus {len(stats['subscribers'])} abonnés sans compte.",
        f"- **{m['counts']['carts']} paniers en cours** sont redonnés à leur compte, comme PrestaShop le fait à la "
        f"connexion (paniers modifiés depuis le {m['paniers_depuis']}, option `--paniers-depuis`).",
        f"- {sum(stats['auto_closed'].values())} vieilles commandes restées ouvertes (passées avant le "
        f"{m['fermeture_auto_avant']}) sont fermées par la migration, sans courriel au client.",
        f"- **{len(stats['open_orders'])} commandes récentes sont encore ouvertes** (en attente ou en préparation) : "
        "à traiter dans PrestaShop avant le jour J. Liste plus bas.",
        "",
        "## Comptes",
        "",
        "| | Nombre |",
        "|---|---:|",
    ]
    for k, v in sorted(n.items()):
        if not k.startswith(("commandes", "paniers", "lignes de panier")) and k != "cartes masquées":
            L.append(f"| {k} | {v} |")
    L += ["", "Par année de création :", "", "| Année | Gardés | Dormants écartés (jamais commandé) |", "|---|---:|---:|"]
    for y in sorted(set(stats["created_by_year"]) | set(stats["dormant_by_year"])):
        L.append(f"| {y} | {stats['created_by_year'][y]} | {stats['dormant_by_year'][y]} |")
    carts = [a["cart"] for a in acc if a["cart"]]
    L += ["", "## Paniers en cours", "",
          "Chaque compte retrouve son dernier panier non commandé, s'il a été modifié depuis le "
          f"{m['paniers_depuis']}. Les produits qui ne sont plus vendus en sont retirés. Les prix sont ceux du nouveau "
          "site, pas ceux du jour où le produit a été ajouté. Les bons de réduction appliqués au panier ne suivent pas.",
          "", "| | Nombre |", "|---|---:|",
          f"| paniers redonnés | {len(carts)} |",
          f"| produits dans ces paniers | {sum(len(c['lines']) for c in carts)} |"]
    for k, v in sorted(n.items()):
        if k.startswith(("paniers", "lignes de panier")):
            L.append(f"| {k} | {v} |")
    L += ["", "Par mois de dernière modification :", "", "| Mois | Paniers |", "|---|---:|"]
    for k, v in sorted(stats["carts_by_month"].items()):
        L.append(f"| {k} | {v} |")
    L += ["", "## Commandes", "", "| | Nombre |", "|---|---:|"]
    for k, v in sorted(n.items()):
        if k.startswith("commandes"):
            L.append(f"| {k} | {v} |")
    L += ["", "Une commande sans compte garde son courriel : elle se rattachera au compte créé plus tard avec ce courriel, "
          "une fois le courriel confirmé.", "", "| Nouveau statut | Statut PrestaShop | Commandes |", "|---|---|---:|"]
    for (s, ps), v in sorted(stats["by_status"].items(), key=lambda x: (x[0][0], -x[1])):
        L.append(f"| {m['statuses'][s]['fr']} | {ps} | {v} |")
    st = m["statuses"]
    L += ["", "### Vieilles commandes fermées par la migration", "",
          f"Passées avant le {m['fermeture_auto_avant']} et jamais passées à « Envoyé » ou « Annulé » dans PrestaShop. "
          "Avec une facture et un paiement encaissé, la commande devient « Expédiée » ; sans facture, « Annulée ». "
          "PrestaShop n'est pas modifié, donc aucun courriel ne part. Le statut d'origine reste visible "
          "(`prestashop_status`, `status_inferred`).", "",
          f"| Année | {st[auto['payee']]['fr']} | {st[auto['non_payee']]['fr']} |", "|---|---:|---:|"]
    for y in sorted({y for y, _ in stats["auto_closed"]}):
        L.append(f"| {y} | {stats['auto_closed'][(y, auto['payee'])]} | {stats['auto_closed'][(y, auto['non_payee'])]} |")
    L += ["", "### Commandes encore ouvertes : à traiter avant le jour J", "",
          "Dans PrestaShop, une commande partie passe à **« Envoyé (sans notification) »** (ou « Envoyé » pour prévenir le "
          "client par courriel). Attention : « Annulé » envoie toujours un courriel au client.", "",
          "| N° | Date | Statut PrestaShop | Total | Facture |", "|---:|---|---|---:|---|"]
    for d, i, s, tot, cur, inv in stats["open_orders"]:
        L.append(f"| {i} | {d[:10]} | {s} | {money(tot)} {cur} | {'oui' if inv else 'non'} |")
    L += ["", "### Totaux par année et devise (TTC)", "",
          "Identiques au calcul direct de MySQL : sinon l'extraction s'arrête.", "",
          "| Année | Devise | Commandes | Total |", "|---|---|---:|---:|"]
    for (y, cur), (cnt, tot) in sorted(stats["totals"].items()):
        L.append(f"| {y} | {cur} | {cnt} | {money(tot)} |")
    L += ["", "### Contrôles", "",
          f"- Détail des taxes (TPS, TVQ, TVH…) : le total de taxe de chaque commande est exact, et son détail par taxe "
          f"lui est toujours égal. Pour {m['counts']['orders'] - len(stats['tax_ko'])} commandes, le détail enregistré par "
          f"PrestaShop tombait déjà juste. Pour les {len(stats['tax_ko'])} autres, il est réparti au prorata "
          f"(`taxes_exact` = false)."
          + (f" Exemples : {', '.join(map(str, stats['tax_ko'][:15]))}." if stats["tax_ko"] else ""),
          f"- Commandes dont le sous-total, le rabais et la livraison ne donnent pas le total HT : {len(stats['ht_ko'])}"
          + (f" ({', '.join(map(str, stats['ht_ko'][:15]))})." if stats["ht_ko"] else "."),
          f"- Numéros de carte trouvés dans des messages et masqués : {n['cartes masquées']}.",
          f"- Dernière facture : {max((o['invoice_number'] for o in doc['orders'] if o['invoice_number']), default='aucune')} "
          f"(le nouveau site peut continuer la numérotation). Commandes sans facture : "
          f"{sum(not o['invoice_number'] for o in doc['orders'])}.",
          "",
          "## Pas migré",
          "",
          "- Les données de carte de crédit (`ps_ccofflinepayment`) : jamais lues.",
          "- Les paniers plus anciens, ceux des invités et des visiteurs, les bons de réduction des paniers.",
          "- Les visites, les listes de souhaits, l'historique détaillé des statuts.",
          f"- Les {stats['raw_counts']['threads']} fils du formulaire de contact : archivés dans `service_clientele.csv`.",
          ""]
    path.write_text("\n".join(L), encoding="utf-8")


def private_open(path):
    """Fichier lisible par l'usager seul : données personnelles."""
    return os.fdopen(os.open(path, os.O_WRONLY | os.O_CREAT | os.O_TRUNC, 0o600), "w", encoding="utf-8", newline="")


def main():
    args = [a for a in sys.argv[1:] if not a.startswith("--")]
    opt = {a.split("=", 1)[0]: a.split("=", 1)[1] for a in sys.argv[1:] if a.startswith("--") and "=" in a}
    since = opt.get("--comptes-depuis", COMPTES_DEPUIS)
    carts_since = opt.get("--paniers-depuis")
    if len(args) != 3:
        sys.exit(__doc__)
    src, dest, rec = args
    raw = json.load(sys.stdin if src == "-" else open(src, encoding="utf-8"))
    dest = Path(dest)
    dest.mkdir(parents=True, exist_ok=True)
    dest.chmod(0o700)

    doc, archive, stats = build(raw, since, rec, carts_since)
    check_forbidden(doc)
    with private_open(dest / "clients.json") as f:
        json.dump(doc, f, ensure_ascii=False, indent=1)
    with private_open(dest / "service_clientele.csv") as f:
        w = csv.DictWriter(f, fieldnames=list(archive[0]) if archive else ["fil"])
        w.writeheader()
        w.writerows(archive)
    write_report(dest / "rapport.md", doc, stats)
    print(f"{len(doc['accounts'])} comptes, {len(doc['orders'])} commandes, {doc['meta']['counts']['carts']} paniers, "
          f"{len(doc['newsletter_subscribers'])} abonnés sans compte -> {dest}", file=sys.stderr)


if __name__ == "__main__":
    main()
