# /// script
# requires-python = ">=3.9"
# ///
"""Corrige les images utilisées dans les descriptions (produits et catégories).

Pour chaque <img src> :
  - un chemin relatif (/img/cms/...) devient une URL complète du site (base_url du JSON) ;
  - l'URL est testée ; si elle ne répond plus, le src pointe vers la copie d'archive.org, et cette
    copie est téléchargée dans <sortie>/images-descriptions/ (seule copie restante : à héberger
    sur le nouveau site ; correspondance dans meta.description_images.recovered_files) ;
  - si l'image est introuvable partout, la balise <img> est retirée.
Les liens <a href="/..."> relatifs deviennent aussi des URL complètes.

Modifie catalogue.json sur place et écrit images_descriptions.csv (rapport).

Usage : uv run export-catalogue/description_images.py <catalogue.json> <dossier_sortie>
"""
import csv
import hashlib
import html
import json
import re
import sys
import urllib.error
import urllib.parse
import urllib.request
from concurrent.futures import ThreadPoolExecutor
from pathlib import Path

DIR_NAME = "images-descriptions"
UA = {"User-Agent": "Mozilla/5.0 (catalogue export)"}
IMG_RE = re.compile(r"<img\b[^>]*?>", re.I)
SRC_RE = re.compile(r"""(\bsrc\s*=\s*)(["'])(.*?)\2""", re.I | re.S)
HREF_RE = re.compile(r"""(\bhref\s*=\s*)(["'])(/[^/"'][^"']*)\2""", re.I)
EMPTY_A_RE = re.compile(r"<a\b[^>]*>\s*</a>", re.I)
MAGIC = (b"\x89PNG", b"\xff\xd8\xff", b"GIF8", b"RIFF", b"<svg", b"<?xml")


def absolute(src, base_url):
    src = html.unescape(src.strip())
    if src.startswith("//"):
        return "https:" + src
    if "://" not in src:
        return base_url.rstrip("/") + "/" + src.lstrip("/")
    return src


def is_image(url, timeout=30):
    try:
        with urllib.request.urlopen(urllib.request.Request(url, headers=UA), timeout=timeout) as r:
            return r.read(64).lstrip().startswith(MAGIC)
    except (urllib.error.URLError, OSError, ValueError):
        return False


def download(url, dest_dir):
    """Télécharge l'image dans dest_dir ; retourne le chemin relatif à la sortie, ou ""."""
    try:
        with urllib.request.urlopen(urllib.request.Request(url, headers=UA), timeout=60) as r:
            data = r.read()
    except (urllib.error.URLError, OSError, ValueError):
        return ""
    head = data[:64].lstrip()
    ext = next((e for magic, e in ((b"\x89PNG", "png"), (b"\xff\xd8\xff", "jpg"), (b"GIF8", "gif"),
                                   (b"RIFF", "webp"), (b"<svg", "svg"), (b"<?xml", "svg")) if head.startswith(magic)), None)
    if not ext:
        return ""
    dest_dir.mkdir(parents=True, exist_ok=True)
    name = hashlib.sha1(url.encode()).hexdigest()[:16] + "." + ext
    (dest_dir / name).write_bytes(data)
    return f"{DIR_NAME}/{name}"


def archived(url):
    """URL brute (im_) de la copie archive.org la plus proche, ou None."""
    try:
        api = "https://archive.org/wayback/available?url=" + urllib.parse.quote(url, safe="")
        with urllib.request.urlopen(urllib.request.Request(api, headers=UA), timeout=40) as r:
            snap = json.load(r).get("archived_snapshots", {}).get("closest")
    except (urllib.error.URLError, OSError, ValueError):
        return None
    if not snap or snap.get("status") not in (None, "200"):
        return None
    return re.sub(r"/web/(\d+)/", r"/web/\1im_/", snap["url"], count=1)


def resolve(url):
    """Retourne (statut, url_finale) ; statut = ok | archive | perdue."""
    if is_image(url):
        return "ok", url
    arch = archived(url)
    if arch and is_image(arch, timeout=60):
        return "archive", arch
    return "perdue", None


def descriptions(doc):
    """(objet, champ, langue, type, id) de chaque description HTML non vide."""
    for p in doc["products"]:
        for field in ("description_short", "description"):
            for lang in (p.get(field) or {}):
                if p[field][lang]:
                    yield p, field, lang, "produit", p["id"]
    for c in doc["categories"]:
        for lang in (c.get("description") or {}):
            if c["description"][lang]:
                yield c, "description", lang, "categorie", c["id"]


def main():
    if len(sys.argv) != 3:
        sys.exit(__doc__)
    src_json, out = Path(sys.argv[1]), Path(sys.argv[2])
    doc = json.loads(src_json.read_text(encoding="utf-8"))
    base_url = doc["meta"]["base_url"]

    urls = set()
    for obj, field, lang, *_ in descriptions(doc):
        for tag in IMG_RE.findall(obj[field][lang]):
            m = SRC_RE.search(tag)
            if m and not m.group(3).startswith("data:"):
                urls.add(absolute(m.group(3), base_url))

    print(f"Images de descriptions : {len(urls)} à vérifier...", file=sys.stderr)
    with ThreadPoolExecutor(6) as ex:
        resolved = dict(zip(urls, ex.map(resolve, urls)))

    # Copie locale des seules images récupérées d'archive.org (l'original n'existe plus nulle part)
    recovered = {}
    for url, (status, final) in resolved.items():
        if status == "archive":
            recovered[url] = download(final, out / DIR_NAME)

    report = []

    def fix_img(tag, ctx):
        m = SRC_RE.search(tag)
        if not m or m.group(3).startswith("data:"):
            return tag
        status, final = resolved[absolute(m.group(3), base_url)]
        report.append({**ctx, "src_original": m.group(3), "statut": status, "url_finale": final or "",
                       "copie_locale": recovered.get(absolute(m.group(3), base_url), "")})
        if not final:
            return ""  # introuvable partout : on retire la balise
        return tag[:m.start()] + f'{m.group(1)}"{html.escape(final)}"' + tag[m.end():]

    for obj, field, lang, kind, oid in descriptions(doc):
        ctx = {"type": kind, "id": oid, "champ": field, "langue": lang}
        h = IMG_RE.sub(lambda m: fix_img(m.group(0), ctx), obj[field][lang])
        h = HREF_RE.sub(lambda m: f'{m.group(1)}{m.group(2)}{absolute(m.group(3), base_url)}{m.group(2)}', h)
        obj[field][lang] = EMPTY_A_RE.sub("", h)

    counts = {s: sum(1 for r in report if r["statut"] == s) for s in ("ok", "archive", "perdue")}
    doc["meta"]["description_images"] = {
        "unique_images": len(urls), "references": counts,
        "recovered_files": [{"original": u, "archive": resolved[u][1], "file": f} for u, f in sorted(recovered.items())],
        "lost": sorted({r["src_original"] for r in report if r["statut"] == "perdue"}),
    }
    src_json.write_text(json.dumps(doc, ensure_ascii=False, indent=4), encoding="utf-8")
    if report:
        with open(out / "images_descriptions.csv", "w", newline="", encoding="utf-8-sig") as f:
            w = csv.DictWriter(f, fieldnames=list(report[0].keys()))
            w.writeheader()
            w.writerows(sorted(report, key=lambda r: (r["statut"] != "perdue", r["type"], r["id"])))
    print(f"  Références : {counts['ok']} ok, {counts['archive']} via archive.org "
          f"({len(recovered)} images copiées dans {DIR_NAME}/), "
          f"{counts['perdue']} perdues (balise retirée)", file=sys.stderr)


if __name__ == "__main__":
    main()
