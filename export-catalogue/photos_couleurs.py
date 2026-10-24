"""Associe une photo à chaque couleur d'un produit quand PrestaShop ne le fait pas.

PrestaShop peut relier une photo à une variante, mais la plupart des produits ne le font pas : la fiche
garde la même photo quelle que soit la couleur choisie. Or les photos montrent souvent les couleurs
une par une (un moulinet noir, un marine, un champagne). On mesure la couleur dominante de chaque photo
(fond blanc retiré) et on la compare à la famille de chaque couleur du fabricant. Une photo n'est
associée que si une seule couleur lui ressemble nettement ; sinon, rien n'est deviné.

Le résultat est une proposition « à confirmer » : la boutique la valide, puis relie les photos dans
PrestaShop. Une association se force ou s'interdit dans taxonomie.json -> photos_couleur.
"""
import io
import json
import urllib.request
from concurrent.futures import ThreadPoolExecutor
from pathlib import Path

from PIL import Image

FOND_MIN = 0.3    # une photo d'ambiance (rivière, personne) n'a pas de fond blanc : sa couleur est celle du décor
MARGE = 0.7       # la couleur la plus proche doit l'être nettement plus que la suivante
ECART_MIN = 20    # deux couleurs du produit plus proches que ça (ΔE) ne se distinguent pas sur une photo
DISTANCE_MAX = 45  # au-delà (ΔE), la photo ne ressemble à aucune couleur du produit
DISPERSION_MAX = 25  # une photo de groupe (toutes les couleurs, un nuancier) a des teintes très dispersées
UA = {"User-Agent": "amimoucheur-export-catalogue/1.0"}  # le site refuse « Python-urllib »


def _lab(rgb):
    """sRGB (0-255) -> CIELAB (D65)."""
    def lin(c):
        c /= 255
        return c / 12.92 if c <= 0.04045 else ((c + 0.055) / 1.055) ** 2.4
    r, g, b = (lin(float(c)) for c in rgb)
    x = (0.4124 * r + 0.3576 * g + 0.1805 * b) / 0.95047
    y = 0.2126 * r + 0.7152 * g + 0.0722 * b
    z = (0.0193 * r + 0.1192 * g + 0.9505 * b) / 1.08883
    f = lambda t: t ** (1 / 3) if t > 0.008856 else 7.787 * t + 16 / 116
    fx, fy, fz = f(x), f(y), f(z)
    return 116 * fy - 16, 500 * (fx - fy), 200 * (fy - fz)


def _hex_lab(h):
    h = h.lstrip("#")
    return _lab(tuple(int(h[i:i + 2], 16) for i in (0, 2, 4)))


def _distance(a, b):
    return sum((x - y) ** 2 for x, y in zip(a, b)) ** 0.5


def _photo_lab(url):
    """[L, a, b, part du fond blanc, dispersion des teintes] : couleur moyenne de l'objet, fond presque
    blanc et transparence retirés. La dispersion est l'écart type des pixels sur a et b (la clarté varie
    avec les ombres, pas la teinte)."""
    try:
        req = urllib.request.Request(url.replace(".jpg", "-home_default.jpg"), headers=UA)
        with urllib.request.urlopen(req, timeout=20) as r:
            img = Image.open(io.BytesIO(r.read())).convert("RGBA").resize((64, 64))
    except Exception:
        return None
    data = list(img.getdata())
    px = [(r, g, b) for r, g, b, a in data if a > 128 and min(r, g, b) < 225]
    if len(px) < 200:  # presque tout est blanc : rien à mesurer
        return None
    n = len(px)
    pl = [_lab(c) for c in px[::4]]
    ma, mb = sum(x[1] for x in pl) / len(pl), sum(x[2] for x in pl) / len(pl)
    spread = (sum((x[1] - ma) ** 2 + (x[2] - mb) ** 2 for x in pl) / len(pl)) ** 0.5
    return [*_lab(tuple(sum(c[i] for c in px) / n for i in range(3))), round(1 - n / len(data), 3), round(spread, 1)]


def mesurer(urls, cache_path):
    """Couleur de chaque photo, gardée en cache (une URL d'image PrestaShop ne change jamais de contenu)."""
    cache_path = Path(cache_path)
    cache = json.loads(cache_path.read_text()) if cache_path.exists() else {}
    cache = {u: v for u, v in cache.items() if v is None or len(v) == 5}  # ancien format : on remesure
    todo = sorted({u for u in urls if u and u not in cache})
    if todo:
        with ThreadPoolExecutor(16) as ex:
            for u, lab in zip(todo, ex.map(_photo_lab, todo)):
                cache[u] = lab
        cache_path.parent.mkdir(parents=True, exist_ok=True)
        cache_path.write_text(json.dumps(cache))
    return cache


def associer(options, photos, labs, hex_familles):
    """options : {libellé : [familles]} ; photos : URLs. Retourne {libellé : (url, distance)}.

    Une photo va à la couleur la plus proche si celle-ci l'est nettement plus que la suivante ;
    une couleur garde sa photo la plus proche."""
    cibles = {}
    for lib, fams in options.items():
        hexes = [hex_familles[f] for f in fams if hex_familles.get(f, "").startswith("#")]
        if hexes:
            cibles[lib] = _hex_lab(hexes[0])
    # Moins de photos que de couleurs : ce sont des photos de groupe ou d'emballage, pas une par couleur
    if len(cibles) < 2 or len(photos) < len(options):
        return {}
    best = {}
    for url in photos:
        lab = labs.get(url)
        if not lab or lab[3] < FOND_MIN or lab[4] > DISPERSION_MAX:
            continue
        lab = lab[:3]
        d = sorted((_distance(lab, c), lib) for lib, c in cibles.items())
        (d1, lib), (d2, _) = d[0], d[1]
        ecart = min(_distance(cibles[lib], c) for o, c in cibles.items() if o != lib)
        if d1 <= DISTANCE_MAX and d1 < MARGE * d2 and ecart >= ECART_MIN and (lib not in best or d1 < best[lib][1]):
            best[lib] = (url, round(d1, 1))
    return best
