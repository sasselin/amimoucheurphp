# /// script
# requires-python = ">=3.9"
# ///
"""Compare l'export aux pages produit réellement servies par la boutique (local par défaut).

Pour chaque produit : statut HTTP / page « indisponible », prix affiché (productPrice,
productPriceWithoutReduction) et prix de chaque variante recalculé comme le fait product.js
du thème (base + impact, prix fixe, puis % ou montant).

Usage : uv run export-catalogue/verify.py <catalogue.json> [--base http://localhost] [--sample N]
                                          [--report verification.csv] [--workers 6]
"""
import argparse
import csv
import http.cookiejar
import json
import random
import re
import sys
import urllib.error
import urllib.request
from concurrent.futures import ThreadPoolExecutor

TOL = 0.011
UNAVAILABLE = ("Ce produit n'est plus disponible", "This product is no longer available",
               "Produit introuvable", "Product not found")


def js_var(html, name):
    m = re.search(r"var %s\s*=\s*" % re.escape(name), html)
    if not m:
        return None
    try:
        value, _ = json.JSONDecoder().raw_decode(html, m.end())
        return value
    except ValueError:
        return None


def js_combo_prices(base, combo):
    """Réplique updatePrice() de themes/default-bootstrap/js/product.js (affichage HT, taxRate=0 pour un visiteur)."""
    regular = base + float(combo.get("price") or 0)
    sp = combo.get("specific_price") or {}
    if sp and float(sp.get("price") or 0) > 0:
        regular = float(sp["price"])
    final = regular
    red = float(sp.get("reduction") or 0) if sp else 0
    if red > 0:
        final = final - red if sp.get("reduction_type") == "amount" else final * (1 - red)
    return round(regular, 2), round(final, 2)


def opener():
    return urllib.request.build_opener(urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()))


def fetch(op, url):
    try:
        with op.open(url, timeout=60) as r:
            return r.status, r.read().decode("utf-8", "replace")
    except urllib.error.HTTPError as e:
        return e.code, e.read().decode("utf-8", "replace")


def check(product, base):
    pid = product["id"]
    url = f"{base}/index.php?id_product={pid}&controller=product&id_lang=2"
    for _ in range(3):  # 5xx passagers du PHP local sous charge
        status, html = fetch(opener(), url)
        if status < 500:
            break
    shown = status == 200 and js_var(html, "id_product") == pid and not any(u in html for u in UNAVAILABLE)
    issues = []
    # En mode --tout, seuls ces motifs rendent la page indisponible (abandonné / 0 $ restent en ligne)
    reasons = product.get("excluded_reasons", [])
    expected = not any(r in ("inactif", "aucune_categorie_affichee") or r.startswith("visibilite") for r in reasons)
    if shown != expected:
        if shown and reasons == ["aucune_categorie_affichee"]:
            issues.append("page accessible par URL mais listée dans aucune catégorie")
        else:
            issues.append(f"affichage: attendu={expected} site={shown} (HTTP {status})")
    checked = 0
    if shown:
        price = product["price"]
        page_final = js_var(html, "productPrice")
        page_regular = js_var(html, "productPriceWithoutReduction")
        if page_final is not None and abs(round(float(page_final), 2) - price["final"]) > TOL:
            issues.append(f"prix final page={round(float(page_final), 2)} export={price['final']}")
        if page_regular is not None and abs(round(float(page_regular), 2) - price["regular"]) > TOL:
            issues.append(f"prix régulier page={round(float(page_regular), 2)} export={price['regular']}")
        combos = js_var(html, "combinationsFromController") or js_var(html, "combinations") or {}
        # Thème default-bootstrap : base = prix produit HT sans variante (productPriceTaxExcluded)
        base_price = float(js_var(html, "productPriceTaxExcluded") or 0)
        variants = {v["id"]: v for v in product["variants"]}
        if isinstance(combos, dict):
            for ipa, combo in combos.items():
                v = variants.get(int(ipa))
                reg, fin = js_combo_prices(base_price, combo)
                if not v:
                    if fin > 0:  # les variantes à 0 $ sont exclues volontairement
                        issues.append(f"variante {ipa} sur le site absente de l'export")
                    continue
                checked += 1
                if abs(fin - v["price"]["final"]) > TOL or abs(reg - v["price"]["regular"]) > TOL:
                    issues.append(f"variante {ipa} ({v['label'].get('fr', '')}) site={reg}->{fin} "
                                  f"export={v['price']['regular']}->{v['price']['final']}")
            missing = set(variants) - {int(k) for k in combos}
            if missing:
                issues.append(f"{len(missing)} variante(s) de l'export absentes du site: {sorted(missing)[:10]}")
    return {"produit_id": pid, "nom": (product["name"] or {}).get("fr", ""), "affiche_attendu": expected,
            "affiche_site": shown, "http": status, "variantes_verifiees": checked,
            "ok": not issues, "ecarts": " | ".join(issues), "url": url}


def pick(products, sample):
    if not sample or sample >= len(products):
        return products
    tricky = [p for p in products if p["price"]["promo"] or p["price"].get("some_variants_on_promo") or
              len({v["price"]["final"] for v in p["variants"]}) > 1 or p.get("excluded_reasons")]
    rest = [p for p in products if p not in tricky]
    random.seed(42)
    chosen = random.sample(tricky, min(len(tricky), sample * 2 // 3))
    chosen += random.sample(rest, min(len(rest), sample - len(chosen)))
    return chosen


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("json")
    ap.add_argument("--base", default="http://localhost")
    ap.add_argument("--sample", type=int, default=0)
    ap.add_argument("--workers", type=int, default=6)
    ap.add_argument("--report")
    ap.add_argument("--ids", help="liste d'ids produits séparés par des virgules")
    a = ap.parse_args()
    doc = json.load(open(a.json, encoding="utf-8"))
    products = doc["products"]
    if a.ids:
        wanted = {int(i) for i in a.ids.split(",")}
        products = [p for p in products if p["id"] in wanted]
    products = pick(products, a.sample)
    print(f"Vérification de {len(products)} produits sur {a.base}...", file=sys.stderr)
    results = []
    with ThreadPoolExecutor(a.workers) as ex:
        for n, r in enumerate(ex.map(lambda p: check(p, a.base), products), 1):
            results.append(r)
            if n % 100 == 0:
                print(f"  {n} / {len(products)}", file=sys.stderr)
    bad = [r for r in results if not r["ok"]]
    if a.report:
        with open(a.report, "w", newline="", encoding="utf-8-sig") as f:
            w = csv.DictWriter(f, fieldnames=list(results[0].keys()))
            w.writeheader()
            w.writerows(sorted(results, key=lambda r: (r["ok"], r["produit_id"])))
    print(f"{len(results) - len(bad)} OK, {len(bad)} avec écarts, "
          f"{sum(r['variantes_verifiees'] for r in results)} variantes vérifiées")
    for r in bad[:30]:
        print(f"  #{r['produit_id']} {r['nom'][:50]} : {r['ecarts'][:300]}")
    return 1 if bad else 0


if __name__ == "__main__":
    sys.exit(main())
