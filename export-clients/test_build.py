# /// script
# requires-python = ">=3.9"
# dependencies = ["argon2-cffi>=23.1"]
# ///
"""Tests de build.py sur une petite extraction synthétique (aucune donnée réelle).

Usage : uv run export-clients/test_build.py
"""
import hashlib
import json
import sys
import tempfile
from pathlib import Path

sys.path.insert(0, str(Path(__file__).resolve().parent))
import build  # noqa: E402
from argon2 import PasswordHasher  # noqa: E402

KEY = "cle-de-test"


def md5(pw):
    return hashlib.md5((KEY + pw).encode()).hexdigest()  # Tools::encrypt de PrestaShop 1.6


def customer(id, email, guest=False, created="2020-01-01 00:00:00", updated=None, pw="secret", **kw):
    return {"id": id, "email": email, "first_name": "Prénom", "last_name": "Nom", "passwd": md5(pw) if not guest else None,
            "lang": "fr", "guest": guest, "active": True, "deleted": False, "newsletter": False, "newsletter_at": None,
            "created_at": created, "updated_at": updated or created, **kw}


def order(id, customer_id, placed="2024-05-01 10:00:00", state=4, lines=None, invoice_number=0, **amounts):
    base = {"total_products": "100.00", "total_discounts_tax_excl": "0", "total_discounts_tax_incl": "0",
            "total_shipping_tax_excl": "10.00", "total_shipping_tax_incl": "11.50", "total_wrapping_tax_excl": "0",
            "total_paid_tax_excl": "110.00", "total_paid_tax_incl": "126.47", "total_paid_real": "126.47"}
    base.update(amounts)
    return {"id": id, "reference": f"REF{id}", "customer_id": customer_id, "lang": "fr", "currency": "CAD", "state": state,
            "state_name": "Envoyé", "valid": True, "module": "cheque", "payment": "Chèque", "carrier": "Poste Canada",
            "address_delivery_id": 1, "address_invoice_id": 1,
            "invoice_number": id if invoice_number == 0 else invoice_number, "invoice_date": placed,
            "delivery_date": None, "placed_at": placed, **base,
            "lines": lines if lines is not None else [
                {"product_id": 10, "variant_id": 100, "name": "Mouche - Taille : 12", "sku": "M12", "ean13": None,
                 "quantity": 2, "unit_price_tax_excl": "50.00", "total_price_tax_excl": "100.00",
                 "total_price_tax_incl": "114.98", "taxes": [{"tax_id": 1, "amount": "5.00"}, {"tax_id": 2, "amount": "9.98"}]}],
            "shipping_taxes": [{"tax_id": 1, "amount": "0.50"}, {"tax_id": 2, "amount": "1.00"}],
            "cart_rules": [], "messages": []}


def cart(id, customer_id, updated, lines):
    return {"id": id, "customer_id": customer_id, "lang": "fr", "currency": "CAD", "created_at": updated,
            "updated_at": updated, "lines": [{"product_id": p, "variant_id": v, "quantity": q, "added_at": updated}
                                             for p, v, q in lines]}


def raw():
    customers = [
        customer(1, "Marie@Exemple.ca", created="2018-01-01 00:00:00", updated="2019-01-01 00:00:00", pw="ancien"),
        customer(2, "marie@exemple.ca", created="2021-01-01 00:00:00", updated="2024-01-01 00:00:00", pw="recent",
                 newsletter=True, newsletter_at="2021-02-02 00:00:00"),
        customer(3, "marie@exemple.ca", guest=True, created="2022-01-01 00:00:00"),
        customer(4, "dormant@exemple.ca", created="2019-06-01 00:00:00"),
        customer(5, "nouveau@exemple.ca", created="2025-06-01 00:00:00"),
        customer(9, "panier@exemple.ca", created="2018-01-01 00:00:00", newsletter=True, newsletter_at="2018-01-01 00:00:00"),
        customer(6, "invite@exemple.ca", guest=True, newsletter=True, newsletter_at="2023-03-03 00:00:00"),
        customer(7, "parti@exemple.ca", deleted=True),
        customer(8, "ancien-panier@exemple.ca", created="2025-01-01 00:00:00"),
    ]
    orders = [
        order(1, 1, placed="2019-05-01 10:00:00"),
        order(2, 3, messages=None),
        order(3, 6),
        order(4, 7, state=6),
        # Rabais de 10 % : le détail brut des lignes est avant rabais, le total de taxe après
        order(5, 2, total_discounts_tax_excl="10.00", total_discounts_tax_incl="11.50", total_paid_tax_excl="100.00",
              total_paid_tax_incl="114.97", total_paid_real="114.97"),
        # Ligne sans aucun détail de taxe
        order(6, 2, lines=[{"product_id": 11, "variant_id": None, "name": "Vieux produit", "sku": None, "ean13": None,
                            "quantity": 1, "unit_price_tax_excl": "100.00", "total_price_tax_excl": "100.00",
                            "total_price_tax_incl": "114.98", "taxes": []}]),
        # Restées ouvertes : vieille et payée, vieille sans facture, récente
        order(7, 2, placed="2021-04-01 10:00:00", state=3),
        order(8, 2, placed="2021-04-02 10:00:00", state=13, invoice_number=None, total_paid_real="0"),
        order(9, 2, placed="2026-09-01 10:00:00", state=3),
    ]
    for o in orders:
        o["messages"] = []
        if o["id"] == 1:
            o["shipping_taxes"] = []
    orders[1]["messages"] = [{"from": "client", "private": False, "at": "2024-05-01 10:00:00",
                              "text": "Ma carte : 4111 1111 1111 1111, appelez-moi au 418 555 1234"}]
    total = lambda o: (o["placed_at"][:4], o["total_paid_tax_incl"])  # noqa: E731
    by = {}
    for o in orders:
        y, t = total(o)
        n, s = by.get(y, (0, 0))
        by[y] = (n + 1, s + float(t))
    return {
        "meta": {"generated_at": "2026-10-24T00:00:00", "source": "test", "invoice_prefix": "#IN",
                 "counts": {"customers": len(customers), "threads": 0},
                 "check": {"by_year_currency": [{"year": y, "currency": "CAD", "orders": n, "total": f"{s:.2f}"}
                                                for y, (n, s) in by.items()],
                           "lines": sum(len(o["lines"]) for o in orders)}},
        "taxes": {"1": {"name": "TPS", "rate": "5.000"}, "2": {"name": "TVQ", "rate": "9.975"}},
        "customers": customers,
        "addresses": [{"id": 1, "customer_id": 2, "alias": "Maison", "company": None, "first_name": "Prénom",
                       "last_name": "Nom", "line1": "1 rue", "line2": None, "postal_code": "G1A 1A1", "city": "Québec",
                       "region_code": "QC", "country_code": "CA", "phone": None, "other": None, "deleted": False}],
        "orders": orders,
        "carts": [
            # Marie : le plus récent de ses fiches de compte ; celui de sa fiche invitée ne compte pas
            cart(10, 2, "2026-09-01 00:00:00", [(10, 100, 1), (10, 100, 2), (12, None, 1), (13, 999, 1)]),
            cart(11, 1, "2026-08-01 00:00:00", [(10, 100, 5)]),
            cart(12, 3, "2026-10-01 00:00:00", [(10, 100, 9)]),
            # Nouveau : son dernier panier est vide (il l'a vidé), PrestaShop ne lui redonne rien
            cart(20, 5, "2026-06-01 00:00:00", [(10, 100, 1)]),
            cart(21, 5, "2026-07-01 00:00:00", []),
            cart(30, 8, "2025-05-01 00:00:00", [(10, 100, 1)]),
            # Vieux compte sans commande, mais avec un panier en cours : gardé
            cart(40, 9, "2026-10-01 00:00:00", [(10, 100, 2)]),
        ],
        "newsletter": [{"email": "Dormant@exemple.ca", "at": "2020-01-01 00:00:00"},
                       {"email": "seul@exemple.ca", "at": "2020-01-01 00:00:00"}],
        "threads": [],
    }


def main():
    with tempfile.TemporaryDirectory() as tmp:
        rec = Path(tmp) / "recategorisation.json"
        rec.write_text(json.dumps({"meta": {"catalogue_generated_at": "x"}, "products": [{"id": 10, "variant_ids": [100]},
                                                                                         {"id": 12, "variant_ids": []}]}))
        doc, archive, stats = build.build(raw(), rec)
    build.check_forbidden(doc)
    accounts = {a["email"]: a for a in doc["accounts"]}
    orders = {o["prestashop_id"]: o for o in doc["orders"]}

    # Fusion : un compte par courriel, la fiche la plus récente l'emporte, la fiche invitée suit
    assert set(accounts) == {"marie@exemple.ca", "panier@exemple.ca"}, accounts.keys()
    marie = accounts["marie@exemple.ca"]
    assert marie["prestashop_ids"] == [1, 2, 3]
    assert marie["created_at"] == "2018-01-01 00:00:00"
    assert [orders[i]["account"] for i in (1, 2, 5, 6)] == ["marie@exemple.ca"] * 4
    assert marie["newsletter"]["consent"] and marie["addresses"][0]["region_code"] == "QC"

    # Mot de passe : celui de la fiche gardée, vérifiable comme le fera le site
    pw = marie["password"]
    assert pw["scheme"] == "prestashop_md5+argon2id" and pw["hash"].startswith("$argon2id$v=19$m=19456,t=2,p=1$")
    assert PasswordHasher().verify(pw["hash"], md5("recent"))
    try:
        PasswordHasher().verify(pw["hash"], md5("ancien"))
        raise AssertionError("l'ancien mot de passe ne doit pas passer")
    except Exception as e:
        assert "VerifyMismatch" in type(e).__name__, e
    assert md5("recent") not in json.dumps(doc), "aucune empreinte md5 brute dans la sortie"

    # Sélection : sans commande, gardé seulement avec un panier en cours (même vieux compte) ; sans commande ni panier
    # (jamais rien, panier vidé, panier trop ancien) écarté. Invité seul et fiche supprimée : pas de compte, commandes gardées
    assert stats["n"]["comptes sans commande ni panier écartés"] == 3
    assert stats["n"]["comptes sans commande gardés pour leur panier"] == 1
    assert accounts["panier@exemple.ca"]["cart"]["lines"][0]["quantity"] == 2
    assert accounts["panier@exemple.ca"]["newsletter"]["consent"]
    assert orders[3]["account"] is None and orders[3]["email"] == "invite@exemple.ca"
    assert orders[4]["account"] is None and orders[4]["status"] == "cancelled"
    subs = {s["email"]: s["source"] for s in doc["newsletter_subscribers"]}
    assert subs == {"dormant@exemple.ca": "formulaire", "seul@exemple.ca": "formulaire", "invite@exemple.ca": "commande invité"}, subs

    # Taxes : toujours égales au total de taxe, réparties TPS/TVQ
    for o in doc["orders"]:
        assert sum(t["amount"] for t in o["taxes"]) == o["tax"], o["prestashop_id"]
    o5 = orders[5]
    assert o5["tax"] == 1497 and o5["taxes_exact"], o5
    assert {t["name"]: t["amount"] for t in o5["taxes"]} == {"TPS": 500, "TVQ": 997}, o5["taxes"]
    # Ligne sans détail : sa taxe sous « Taxes », la livraison garde son détail
    assert {t["name"]: t["amount"] for t in orders[6]["taxes"]} == {"Taxes": 1497, "TPS": 50, "TVQ": 100}, orders[6]["taxes"]
    assert orders[1]["invoice_number"] == "#IN000001"

    # Recommander : seule la variante du catalogue actif
    assert orders[1]["lines"][0]["reorderable"] and orders[1]["lines"][0]["product_id"] == 10
    assert not orders[6]["lines"][0]["reorderable"]

    # Paniers en cours : le plus récent des fiches de compte (défaut : modifié dans l'année), produits encore vendus,
    # quantités d'une même variante additionnées, produit simple retrouvé par son id
    assert doc["meta"]["paniers_depuis"] == "2025-10-24"
    mc = marie["cart"]
    assert mc["prestashop_id"] == 10, mc
    assert [(li["product_id"], li["prestashop_variant_id"], li["quantity"]) for li in mc["lines"]] == [(10, 100, 3), (12, None, 1)]
    assert stats["n"]["lignes de panier retirées (produit plus vendu)"] == 1
    assert stats["n"]["paniers trop anciens (non migrés)"] == 1
    assert doc["meta"]["counts"]["carts"] == 2

    # Vieilles commandes ouvertes (plus de 6 mois) : fermées par la migration, statut d'origine gardé
    assert doc["meta"]["fermeture_auto_avant"] == "2026-04-24"
    assert (orders[7]["status"], orders[7]["status_inferred"]) == ("shipped", True), orders[7]["status"]
    assert (orders[8]["status"], orders[8]["status_inferred"]) == ("cancelled", True), orders[8]["status"]
    assert orders[8]["prestashop_status"] == "Envoyé"
    assert (orders[9]["status"], orders[9]["status_inferred"]) == ("processing", False)
    assert not orders[1]["status_inferred"]
    assert [o[1] for o in stats["open_orders"]] == [9]

    # Carte tapée dans un message : masquée, le téléphone reste
    assert orders[2]["notes"][0]["text"] == f"Ma carte : {build.CARD_MASK}, appelez-moi au 418 555 1234"

    # Garde-fou : une clé de ps_ccofflinepayment arrête tout
    try:
        build.check_forbidden({"orders": [{"pan": "x"}]})
        raise AssertionError("garde-fou inactif")
    except SystemExit:
        pass
    print("test_build : OK")


if __name__ == "__main__":
    main()
