# /// script
# requires-python = ">=3.9"
# ///
"""Teste le snippet Caddy des redirections dans un vrai Caddy (même image que la prod).

Chaque ligne de redirections.csv doit donner un 301 vers sa cible. On ajoute des URL piège : segment de catégorie
différent, sans préfixe de langue, suffixe EAN13, filtres du blocklayered, pagination, utm, formes index.php (produit,
catégorie, marque, page de compte), page CMS et nouvelles URL (qui ne doivent pas être redirigées).

Usage : uv run export-catalogue/test_redirections.py <dossier_sortie>   (ex. export-catalogue/out/local_tout)
Code de sortie ≠ 0 au moindre écart.
"""
import csv
import http.client
import shutil
import subprocess
import sys
import tempfile
import time
from pathlib import Path

IMAGE = "caddy:2.8.4"  # deploy/docker-compose.prod.yml
PORT = 18080
CADDYFILE = """{
	auto_https off
	admin off
}
:8080 {
	import /etc/caddy/redirections.caddy
	respond 404
}
"""


def pieges(rows):
    """(url, code attendu, Location attendue) déduits de la table elle-même."""
    by = {}
    for r in rows:
        by.setdefault((r["type"], r["langue"]), []).append(r)
    cible = {(r["type"], r["langue"], r["id"]): r["cible"] for r in rows}
    prod = next(r for r in by[("produit", "fr")] if r["regle"] == "produit actif")
    pid, cat, brand = prod["id"], by[("categorie", "fr")][-1]["id"], by[("marque", "fr")][0]["id"]
    pf, pe = cible[("produit", "fr", pid)], cible[("produit", "en", pid)]
    cf, ce = cible[("categorie", "fr", cat)], cible[("categorie", "en", cat)]
    # Pages de compte : formes index.php (la langue suit les mêmes règles), avec une requête ignorée
    page_cases = []
    if ("page", "fr") in by and ("page", "en") in by:
        pg = by[("page", "fr")][0]["id"]
        en = next(r["cible"] for r in by[("page", "en")] if r["id"] == pg)
        fr = by[("page", "fr")][0]["cible"]
        page_cases = [
            (f"/index.php?controller={pg}", 301, fr),
            (f"/index.php?controller={pg}&isolang=en", 301, en),
            (f"/index.php?controller={pg}&id_lang=1", 301, en),
            (f"{by[('page', 'fr')][0]['ancienne_url']}?back=history", 301, fr),
            ("/index.php?controller=search&search_query=mouche", 404, None),
            (fr, 404, None),
            (en, 404, None),
        ]
    return page_cases + [
        (f"/fr/autre-categorie/{pid}-slug-modifie.html", 301, pf),
        (f"/fr/{pid}-x.html", 301, pf),
        (f"/{pid}-x-0123456789012.html", 301, pf),
        (f"/en/whatever/{pid}-x.html", 301, pe),
        (f"/fr/x/{pid}-y.html?utm_source=google&gmc_currency=1", 301, pf),
        (f"/index.php?controller=product&id_product={pid}", 301, pf),
        (f"/index.php?id_product={pid}&controller=product&isolang=en", 301, pe),
        (f"/index.php?id_product={pid}&controller=product&id_lang=1", 301, pe),
        (f"/index.php?id_product={pid}&controller=product&id_lang=1&isolang=fr", 301, pf),
        (f"/fr/{cat}-x?p=2&orderby=price", 301, cf),
        (f"/fr/{cat}-x/couleur-rouge", 301, cf),
        (f"/en/{cat}-x/", 301, ce),
        (f"/{cat}-x", 301, cf),
        (f"/index.php?controller=category&id_category={cat}", 301, cf),
        (f"/fr/{brand}_x", 301, cible[("marque", "fr", brand)]),
        (f"/index.php?controller=manufacturer&id_manufacturer={brand}&isolang=en", 301, cible[("marque", "en", brand)]),
        ("/fr/x/99999999-inconnu.html", 301, "/fr/"),
        ("/en/99999999-unknown", 301, "/en/"),
        ("/", 301, "/fr/"),
        ("/fr/", 404, None),
        ("/en/", 404, None),
        ("/fr/content/4-a-propos", 404, None),
        ("/img/p/1/2/3/123.jpg", 404, None),
        (pf, 404, None),
        (pe, 404, None),
        (cf, 404, None),
    ]


def main():
    out = Path(sys.argv[1])
    caddy_dir = out / "recategorisation" / "caddy"
    with open(caddy_dir / "redirections.csv", encoding="utf-8-sig") as f:
        rows = list(csv.DictReader(f))
    cases = [(r["ancienne_url"], 301, r["cible"]) for r in rows] + pieges(rows)

    tmp = Path(tempfile.mkdtemp())
    try:
        (tmp / "Caddyfile").write_text(CADDYFILE)
        shutil.copy(caddy_dir / "redirections.caddy", tmp / "redirections.caddy")
        tmp.chmod(0o755)
        mount = ["-v", f"{tmp}:/etc/caddy:ro"]
        v = subprocess.run(["docker", "run", "--rm", *mount, IMAGE, "caddy", "validate",
                            "--config", "/etc/caddy/Caddyfile", "--adapter", "caddyfile"],
                           capture_output=True, text=True)
        if v.returncode:
            sys.exit(f"caddy validate a échoué :\n{v.stderr[-3000:]}")
        cid = subprocess.run(["docker", "run", "-d", "--rm", *mount, "-p", f"127.0.0.1:{PORT}:8080", IMAGE],
                             capture_output=True, text=True, check=True).stdout.strip()
        try:
            conn = None
            for _ in range(50):
                try:
                    conn = http.client.HTTPConnection("127.0.0.1", PORT, timeout=5)
                    conn.request("GET", "/fr/")
                    conn.getresponse().read()
                    break
                except OSError:
                    conn = None
                    time.sleep(0.2)
            if conn is None:
                sys.exit("Caddy ne répond pas.")
            bad = []
            for url, code, loc in cases:
                conn.request("GET", url)
                r = conn.getresponse()
                r.read()
                got = (r.status, r.getheader("Location"))
                if got != (code, loc):
                    bad.append(f"{url}\n    attendu {code} {loc}\n    obtenu  {got[0]} {got[1]}")
        finally:
            subprocess.run(["docker", "stop", cid], capture_output=True)
    finally:
        shutil.rmtree(tmp, ignore_errors=True)

    if bad:
        print(f"Redirections : {len(bad)} écart(s) sur {len(cases)} URL testées", file=sys.stderr)
        print("\n".join(bad[:40]), file=sys.stderr)
        sys.exit(1)
    print(f"Redirections : {len(cases)} URL testées dans {IMAGE}, toutes conformes.")


if __name__ == "__main__":
    main()
