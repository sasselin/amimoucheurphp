# /// script
# requires-python = ">=3.9"
# dependencies = ["openpyxl>=3.1"]
# ///
"""Applique la nouvelle taxonomie (taxonomie.json) au catalogue extrait.

Pour chaque produit : nouvelle catégorie, marque, filtres normalisés, tags et badges.
Le libellé du fabricant n'est jamais réécrit : on lui associe une valeur de filtre.

Usage : uv run export-catalogue/recategorise.py <catalogue.json> <dossier_sortie>
Sortie : <dossier_sortie>/recategorisation/ (CSV, XLSX, rapport.md, avant_apres.html)
"""
import json
import re
import sys
import unicodedata
from collections import Counter, defaultdict
from datetime import datetime, timedelta
from fractions import Fraction
from pathlib import Path

from openpyxl import Workbook

sys.dont_write_bytecode = True  # pas de __pycache__ à côté de build.py
from build import add_sheet, t, write_csv  # noqa: E402

HERE = Path(__file__).parent


def norm(s):
    """Minuscules, apostrophes et espaces uniformisés (les accents sont gardés)."""
    s = (s or "").replace("’", "'").replace("´", "'").replace("`", "'").replace("_", " ")
    return re.sub(r"\s+", " ", s).strip().lower()


def slug(s):
    s = unicodedata.normalize("NFKD", s).encode("ascii", "ignore").decode()
    return re.sub(r"[^a-z0-9]+", "-", s.lower()).strip("-")


def has_word(text, word):
    """`word` présent dans `text` sans être collé à une autre lettre."""
    return re.search(r"(?<![a-zà-ÿ0-9])" + re.escape(word) + r"(?![a-zà-ÿ])", text) is not None


def uniq(items):
    return list(dict.fromkeys(i for i in items if i not in (None, "")))


# ---------------------------------------------------------------- taxonomie

class Taxonomie:
    def __init__(self, data):
        self.data = data
        self.nodes = {}
        for n in data["arbre"]:
            self._add(n, None, n["famille"], 1)
        self.old = {int(k): v for k, v in data["anciennes_categories"].items() if not k.startswith("_")}
        self.produits = {int(k): v for k, v in data["produits"].items() if not k.startswith("_")}
        self.regles = [dict(r, rx=re.compile(r["motif"], re.I)) for r in data["regles_nom"]["regles"]]
        self.aliases = sorted(((norm(a), m) for m, al in data["marques"].items() for a in al + [m]),
                              key=lambda x: -len(x[0]))
        self.alias_map = dict(self.aliases)
        self.couleurs = data["couleurs"]

    def _add(self, n, parent, famille, depth):
        famille = n.get("famille", famille)
        self.nodes[n["id"]] = {"id": n["id"], "fr": n["fr"], "en": n["en"], "parent": parent, "famille": famille,
                               "depth": depth, "utilitaire": n.get("utilitaire", False),
                               "enfants": [c["id"] for c in n.get("enfants", [])]}
        for c in n.get("enfants", []):
            self._add(c, n["id"], famille, depth + 1)

    def chain(self, cid):
        out = []
        while cid:
            out.append(cid)
            cid = self.nodes[cid]["parent"]
        return out[::-1]

    def path(self, cid, lang="fr"):
        return " > ".join(self.nodes[c][lang] for c in self.chain(cid)) if cid else ""

    def univers(self, cid):
        return self.chain(cid)[0] if cid else None

    def url(self, cid, lang):
        if lang == "fr":
            return "/".join(self.chain(cid))
        return "/".join(slug(self.nodes[c]["en"]) for c in self.chain(cid))


# ---------------------------------------------------------------- marques

def brand_of(p, tax, old_brands):
    """(marque, source) : champ marque, puis préfixe du nom, puis ancienne catégorie-marque."""
    if p["brand"]:
        b = tax.alias_map.get(norm(p["brand"]).rstrip("."))
        if b:
            return b, "champ"
    for name in (norm(t(p["name"])), norm(t(p["name"], "en"))):
        for alias, b in tax.aliases:
            if name.startswith(alias) and not re.match(r"[a-zà-ÿ]", name[len(alias):len(alias) + 1]):
                return b, "nom"
        for seg in re.split(r"\s+-\s+", name)[1:3]:
            b = tax.alias_map.get(seg.strip(" ."))
            if b:
                return b, "nom"
    if old_brands:
        return old_brands[0], "ancienne_categorie"
    if p["brand"]:
        return p["brand"].strip(" ."), "champ_non_reconnu"
    return "", ""


# ---------------------------------------------------------------- classement

def classify(p, tax, cats_old):
    """Catégorie principale, catégories secondaires, source, infos héritées des anciennes catégories."""
    name = norm(t(p["name"]) + " | " + t(p["name"], "en"))
    old_ids = [c["id"] for c in p["categories"]]
    default = (p.get("default_category") or {}).get("id")
    infos = [tax.old[i] for i in old_ids if tax.old.get(i)]
    old_brands = uniq([tax.old[i].get("marque") for i in ([default] if default else []) + old_ids if tax.old.get(i)])

    if p["id"] in tax.produits:
        o = tax.produits[p["id"]]
        return o["cat"], list(o.get("cat_secondaires", [])), "manuel", infos, old_brands

    cat, source = None, ""
    cands = ([default] if default and (tax.old.get(default) or {}).get("cat") else []) + \
        sorted((i for i in old_ids if (tax.old.get(i) or {}).get("cat")), key=lambda i: -cats_old[i]["depth"])
    if cands:
        cat, source = tax.old[cands[0]]["cat"], "ancienne_categorie"
    provisional = cat
    for r in tax.regles:
        if r["rx"].search(name) and (not r["si_cat"] or cat in r["si_cat"]):
            cat, source = r["cat"], "regle"
            break
    # Secondaires : seulement les anciennes catégories feuilles, pour ne pas hériter d'un parent fourre-tout
    second = uniq(tax.old[i]["cat"] for i in old_ids
                  if (tax.old.get(i) or {}).get("cat") and not cats_old[i]["has_children"])
    second = [c for c in second if c not in (cat, provisional)]
    return cat, second, source, infos, old_brands


# ---------------------------------------------------------------- lecture des valeurs

COLOR_GROUP = re.compile(r"couleur|color|colour", re.I)
COLOR_GROUPS = {norm(g) for g in [
    "Flashabou Holo Magnum", "Polarflash", "SHIMMER", "SHIMMER BACK", "Craft fur brush", "McFlyFoam",
    "Pattes élastique", "Grizzly Flutter Legs", "Lanière de lapin Two-Tones.", "Boosted Krinkle Mirror Flash",
    "Fly Tail", "Diamond Braid Holo.", "Patagonia", "Cling", "Bajio - Monture"]}
LENS_GROUPS = {norm(g) for g in ["Bajio - Verres", "Guide's Choice"]}


def is_color_group(g):
    return bool(COLOR_GROUP.search(g)) or norm(g) in COLOR_GROUPS


def color_families(value, tax):
    """(familles, modificateurs, statut) d'un libellé de couleur."""
    v = norm(value)
    v_clean = re.sub(r"[#(]?\s*#?\d{3,5}\)?", " ", v)  # retire les numéros de teinte (#6908)
    fams = []
    key = re.sub(r"\s*[-–]?\s*$", "", v_clean).strip(" .")
    manual = tax.couleurs["manuel"].get(v) or tax.couleurs["manuel"].get(v.rstrip(". ")) or tax.couleurs["manuel"].get(key)
    if manual:
        fams, statut = [manual], "manuel"
    else:
        for f in tax.couleurs["familles"]:
            if any(has_word(v_clean, m) for m in f["mots"]):
                fams.append(f["id"])
        statut = "auto" if fams else "à_classer"
    mods = [k for k, mots in tax.couleurs["modificateurs"].items()
            if any(m in v for m in mots if m.endswith((".", " "))) or any(has_word(v, m.strip()) for m in mots)]
    return fams, mods, statut


def inch_mm(txt):
    """'3/32' (pouce) -> 2.4 ; '2.8 mm' -> 2.8."""
    m = re.search(r"(\d+(?:[.,]\d+)?)\s*mm", txt)
    if m:
        return float(m.group(1).replace(",", "."))
    m = re.search(r"\b(\d+)\s*/\s*(\d+)\b", txt)
    if m and int(m.group(2)) in (8, 16, 32, 64):
        return round(float(Fraction(int(m.group(1)), int(m.group(2)))) * 25.4, 1)
    return None


def feet_inches(txt):
    """Première longueur pieds/pouces du texte -> (pouces, '9\\'0"')."""
    m = re.search(r"(\d{1,2})\s*'\s*(?:(\d{1,2})\s*(?:''|\"|”|′′)?)?", txt)
    if not m:
        return None
    ft, inch = int(m.group(1)), int(m.group(2) or 0)
    if not 5 <= ft <= 16 or inch > 11:
        return None
    return ft * 12 + inch, f"{ft}'{inch}\""


def rod_code(txt):
    """Code fabricant '790-4' / '376-4' / '1190-4' -> (numéro, pouces, sections)."""
    m = re.match(r"^\s*[A-Z]{0,2}\s*(\d+?)(100|\d{2})\s*[-/]\s*(\d)\b", txt)
    if not m:
        return None
    w, ln, sec = int(m.group(1)), m.group(2), int(m.group(3))
    inches = 120 if ln == "100" else int(ln[0]) * 12 + int(ln[1])
    if not 1 <= w <= 15 or not 72 <= inches <= 120:
        return None
    return w, inches, sec


def weights(txt):
    """Numéros de soie mentionnés : '# 7/8' -> [7, 8] ; '8WT' ; 'WF-5-F' ; 'soie 6 - 7'."""
    out = []
    for a, b in re.findall(r"#\s*(\d{1,2})(?:\s*[/-]\s*(\d{1,2}))?", txt):
        out += [int(a)] + ([int(b)] if b else [])
    for a, b in re.findall(r"\b(\d{1,2})(?:\s*/\s*(\d{1,2}))?\s*wt\b", txt, re.I):
        out += [int(a)] + ([int(b)] if b else [])
    for a in re.findall(r"\b(?:wf|dt|tt|st|sh)[\s-]*(\d{1,2})\b", txt, re.I):
        out.append(int(a))
    return [w for w in out if 0 <= w <= 15]


def span(ws):
    return list(range(min(ws), max(ws) + 1)) if ws else []


def fmt_len(inches):
    return f"{inches // 12}'{inches % 12}\""


SIZES = [("xxxl", "3XL"), ("3xl", "3XL"), ("xxl", "XXL"), ("2xl", "XXL"), ("extra large", "XL"), ("x-large", "XL"),
         ("xl", "XL"), ("extra small", "XS"), ("xsmall", "XS"), ("xs", "XS"), ("large", "L"), ("medium", "M"),
         ("small", "S")]
SIZE_ORDER = ["XS", "S", "M", "L", "XL", "XXL", "3XL"]
PATA_SIZE = {"x": "XL", "2": "XXL", "3": "3XL", "s": "S", "m": "M", "l": "L"}


def apparel(group, value):
    """Taille, coupe, pointure, genre d'un libellé de vêtement, wader ou botte."""
    v = norm(value)
    r = {}
    if "patagonia" in norm(group) and re.match(r"^[0-9a-z]{3}\b", v):
        # Code Patagonia : taille + longueur + pied (MLM = Medium Long, pied 9-11)
        inner = re.search(r"\(([^)]*)\)", v)
        inner = inner.group(1) if inner else ""
        # Le libellé entre parenthèses fait foi : XSM = Extra Large Short chez l'homme, XSS = Extra Small Short chez la femme
        taille = next((s for k, s in (("extra small", "XS"), ("extra large", "XL"), ("xxl", "XXL"), ("2xl", "XXL"),
                                      ("3x", "3XL"), ("small", "S"), ("medium", "M"), ("large", "L")) if k in inner), None)
        r["taille"] = [taille or PATA_SIZE.get(v[0], v[0].upper())]
        r["coupe"] = [c for k, c in (("extra short", "Extra court"), ("short", "Court"), ("king", "King"),
                                     ("long", "Long"), ("reg", "Régulier")) if k in inner][:1]
        m = re.search(r"(\d{1,2})\s*-\s*(\d{1,2})", v)
        if m:
            r["pointure"] = [f"{m.group(1)}-{m.group(2)}"]
        if "femme" in norm(group):
            r["genre"] = ["Femme"]
        return r
    if "pantalon" in norm(group):
        m = re.search(r"\d+", v)
        r["taille"] = [m.group(0)] if m else []
        return r
    m = re.search(r"men.s\s*(\d+(?:\.5)?)\s*/\s*women.s\s*(\d+(?:\.5)?)", v)
    if m:
        r["pointure"] = [m.group(1)]
        return r
    m = re.search(r"(\d+(?:\.5)?)\s*(us|eu)\b", v)
    if m:
        r["pointure"] = [m.group(1)]  # « 8 EU ou 41 Europe » : le premier nombre est la pointure US
        return r
    tailles, rest = [], v
    for part in re.split(r"[/]", v):
        part = part.strip()
        for k, s in SIZES:
            if re.match(re.escape(k) + r"\b", part) or part == k[0] and len(k) > 1 and False:
                tailles.append(s)
                rest = rest.replace(k, " ")
                break
        else:
            m2 = re.match(r"^(xs|s|m|l|xl|xxl)\b", part)
            if m2:
                tailles.append(m2.group(1).upper())
    r["taille"] = uniq(tailles)
    coupe = [c for k, c in (("short", "Court"), ("king", "King"), ("long", "Long"), ("tall", "Long"),
                            ("full", "Ample"), ("petite", "Petite")) if re.search(r"\b" + k + r"\b", v)]
    if coupe:
        r["coupe"] = coupe[:1]
    m = re.search(r"\((\d{1,2})\s*-\s*(\d{1,2})\)", v)
    if m:
        r["pointure"] = [f"{m.group(1)}-{m.group(2)}"]
    return r


def hook_size(v):
    v = norm(v).lstrip("#").strip()
    m = re.match(r"^(\d{1,2}/0|\d{1,2})$", v)
    return m.group(1) if m else None


def hook_sort(s):
    return -int(s.split("/")[0]) if "/0" in s else int(s)


# ---------------------------------------------------------------- facettes par famille

def facets_for(p, cat, famille, tax, infos, brand):
    """Facettes du produit et lignes de mapping des variantes.

    Retourne (facettes: {clé: [valeurs]}, specs: [(clé, libellé fabricant, valeur filtre)], lignes, alertes)."""
    name = norm(t(p["name"]) + " | " + t(p["name"], "en"))
    F = defaultdict(list)
    specs, rows, alerts = [], [], []
    old_info = {k: v for i in infos for k, v in i.items() if k in ("matiere", "type_outil", "usage_hamecon")}

    def add(key, vals):
        for v in vals if isinstance(vals, list) else [vals]:
            if v not in (None, "") and v not in F[key]:
                F[key].append(v)

    def row(v, a, facette, valeur, statut):
        rows.append({"produit_id": p["id"], "variante_id": v["id"], "sku": v["reference"] or "",
                     "produit": t(p["name"]), "groupe": t(a["group"]), "libelle_fabricant": t(a["value"]),
                     "facette": tax.data["facettes"].get(facette, {}).get("fr", facette) if facette else "",
                     "valeur_filtre": valeur, "statut": statut})

    # ---- niveau produit
    if famille == "cannes":
        if cat == "cannes-spey-switch":
            add("type_canne", "Switch" if "switch" in name and "spey" not in name else "Spey")
        elif re.search(r"euro|tactical", name):
            add("type_canne", "Euro-nymphe")
        elif cat in ("cannes-une-main", "ensembles"):
            add("type_canne", "Une main")
        m = re.search(r"(\d)\s*(?:sections|pcs|pi[eè]ces)", name)
        if m:
            add("sections", m.group(1))
    if famille == "soies":
        for k, lab in (("skagit", "Skagit"), ("scandi", "Scandi"), ("integrated|intégrée", "Spey intégrée"),
                       ("euro", "Euro-nymphe"), ("running line|shooting line|gripshooter|powerflex core", "Running line"),
                       ("tête de lancer|shooting head|ambush", "Tête de lancer"), ("triangle taper", "Triangle taper"),
                       ("polyleader|versileader|mow tip|flo tip", "Pointe")):
            if re.search(k, name):
                add("profil", lab)
        for k, lab in ((r"sink tip|streamertip|versitip|depthfinder|deep water", "Pointe plongeante"),
                       (r"flottant|floating|\bfloat\b", "Flottante"), (r"intermédiaire|intermediate|\bint\b", "Intermédiaire"),
                       (r"callante|plongeante|sinking|fathom|\bsink\b", "Plongeante"), (r"\bhover\b", "Hover")):
            if re.search(k, name):
                add("densite", lab)
        if re.search(r"tropic|bonefish|tarpon|permit|grand slam|outbound", name):
            add("climat", "Tropical")
        elif re.search(r"cold salt|striper|ridge striper", name):
            add("climat", "Eau salée froide")
        elif cat in ("soies-une-main", "soies-spey"):
            add("climat", "Froid & tempéré")
        add("grains", [int(g) for g in re.findall(r"(\d{3})\s*gr", name)])
    if famille == "avancons":
        add("materiau_avancon", "Acier" if re.search(r"wire|acier", name) else
            "Fluorocarbone" if "fluoro" in name else "Nylon")
        add("longueur", [f"{y} vg" for y in re.findall(r"(\d+)\s*(?:yds|vgs|verges)", name)])
    if famille == "mouches":
        tm = {"seches": "Sèche", "nymphes": "Nymphe", "noyees": "Noyée", "surface": "Surface & souris",
              "mouches-eau-salee": "Eau salée"}.get(cat)
        if cat == "streamers":
            tm = "Tandem" if "tandem" in name else "Woolly Bugger" if "bugger" in name else \
                "Muddler" if "muddler" in name else "Streamer"
        if cat == "mouches-saumon":
            tm = "Bomber" if "bomber" in name else "Noyée à saumon"
        add("type_mouche", tm)
        add("lestee", "Oui" if re.search(r"bille|tungst|lest|bead|cone|jig", name) else "Non")
        for seg in re.split(r"\s+-\s+| et ", t(p["name"])):
            fams, _, st = color_families(seg, tax)
            if st != "à_classer" and len(seg) < 25:
                add("couleur", fams)
    if famille == "hamecons":
        for k, lab in tax.data["hamecons_mots"]:
            if re.search(k, name):
                add("usage_hamecon", lab)
        for model, usages in tax.data["hamecons_modeles"].get(brand, {}).items():
            if re.search(r"(?<![0-9a-z])" + re.escape(model.lower()) + r"(?![0-9])", name):
                add("usage_hamecon", usages)
        add("usage_hamecon", old_info.get("usage_hamecon"))
        add("sans_ardillon", "Oui" if re.search(r"barbless|sans ardillon|\bbl\b", name) else "Non")
    if famille in ("materiaux", "billes", "outils") and old_info.get("matiere"):
        add("matiere", old_info["matiere"])
    if famille == "outils":
        add("type_outil", old_info.get("type_outil") or ("Étau" if cat == "etaux" else None))
    if famille == "billes":
        for k, lab in (("tungst", "Tungstène"), ("laiton|brass", "Laiton"), ("plomb|lead", "Plomb"),
                       ("plastique", "Plastique"), ("chaine|chaîne", "Chaînette"), ("fish skull|helmet|mask", "Tête sculptée")):
            if re.search(k, name):
                add("materiau_bille", lab)
        if "fendu" in name:
            add("materiau_bille", "Fendue")
    if famille == "vetements":
        if re.search(r"women|femme|\bw's\b|\bw s\b", name):
            add("genre", "Femme")
        elif re.search(r"\bmen's\b|\bhomme\b|\bm's\b|\bm s\b", name):
            add("genre", "Homme")
        if any("genre:femme" in i.get("tags", []) for i in infos):
            add("genre", "Femme")
    if famille == "lunettes":
        add("materiau_verre", "Verre minéral" if re.search(r"\bverre\b|glass", name) else
            "Polycarbonate" if "poly" in name else None)

    # ---- niveau variante
    for v in p["variants"]:
        for a in v["attributes"]:
            g, val = t(a["group"]), t(a["value"])
            gn, vn = norm(g), norm(val)
            if gn == "moulinet ou bobine":
                add("moulinet_bobine", val)
                row(v, a, "moulinet_bobine", val, "ok")
                continue
            if is_color_group(g) and famille != "lunettes":
                fams, mods, st = color_families(val, tax)
                add("couleur", fams)
                row(v, a, "couleur", ", ".join(fams) + (" (" + ", ".join(mods) + ")" if mods else ""), st)
                continue
            if famille == "cannes":
                fi, ws = feet_inches(val), weights(val)
                code = rod_code(val)
                if code and not ws:
                    ws = [code[0]]
                if code and not fi:
                    fi = (code[1], fmt_len(code[1]))
                sec = re.search(r"(\d)\s*sections", vn) or re.search(r"^\s*[A-Z]{0,2}\s*\d{3,5}\s*[-/]\s*(\d)\b", val)
                parts = []
                if ws:
                    add("numero_soie", span(ws))
                    parts.append("#" + "/".join(map(str, sorted(set(ws)))))
                if fi:
                    add("longueur", fi[1])
                    parts.append(fi[1])
                    if cat in ("cannes-spey-switch",) or (cat == "cannes-une-main" and fi[0] >= 126):
                        add("type_canne", "Spey" if fi[0] >= 144 else "Switch")
                if sec:
                    add("sections", sec.group(1))
                    parts.append(f"{sec.group(1)} sections")
                if re.search(r"\bfb\b|fighting butt|talon de combat", vn):
                    add("talon_combat", "Oui")
                    parts.append("talon de combat")
                row(v, a, "numero_soie" if ws else "longueur", " · ".join(parts),
                    "ok" if ws and fi else "partiel" if parts else "à_réviser")
                specs.append(("Modèle", val, " · ".join(parts)))
                continue
            if famille == "moulinets":
                key = f"{brand}|{val.strip()}"
                table = tax.data["moulinets"].get(brand, {})
                if key in tax.data["moulinets"]["a_confirmer"]:
                    row(v, a, "numero_soie", "", "à_confirmer")
                    alerts.append(f"moulinet {brand} « {val} » : {tax.data['moulinets']['a_confirmer'][key]}")
                    specs.append(("Taille", val, "à confirmer"))
                    continue
                rng = None
                for k, r in table.items():
                    if norm(val).startswith(norm(k)):
                        rng = r
                if not rng:
                    s = re.sub(r"\d+(?:\.\d+)?\s*oz\.?", " ", vn)
                    s = re.sub(r"^[a-z]+-\d+\s*(lt)?\s*;", " ", s)  # « T-6 ; Soie 6 - 7 »
                    m = re.search(r"soie\s*(\d{1,2})(?:\s*-\s*(\d{1,2}))?", s)
                    if m:
                        rng = [int(m.group(1)), int(m.group(2) or m.group(1))]
                    else:
                        inner = re.search(r"\(([^)]*)\)", s)
                        s2 = inner.group(1) if inner else re.sub(r"^[a-z]+\b", " ", s)
                        nums = [int(x) for x in re.findall(r"\b(\d{1,2})\b", s2) if int(x) <= 15]
                        if "+" in s2 and nums:
                            nums.append(min(max(nums) + 2, 15))
                        if nums:
                            rng = [min(nums), max(nums)]
                if rng:
                    add("numero_soie", span(rng))
                    label = f"#{rng[0]}–{rng[1]}" if rng[0] != rng[1] else f"#{rng[0]}"
                    row(v, a, "numero_soie", label, "ok")
                    specs.append(("Taille", val, label))
                elif gn.startswith("grandeur"):
                    row(v, a, "taille", val, "option libre")
                else:
                    row(v, a, "numero_soie", "", "à_réviser")
                    alerts.append(f"moulinet « {val} » non lu")
                continue
            if famille == "soies":
                ws = weights(val)
                if gn == "soie" and vn.isdigit():
                    ws = [int(vn)]
                gr = [int(x) for x in re.findall(r"(\d{2,4})\s*(?:gr\b|grains?|g\.r)", vn)]
                prof = []
                for pre, lab in (("wf", "WF"), ("dt", "DT"), ("tt", "Triangle taper")):
                    if re.match(pre + r"[\s-]*\d", vn):
                        prof.append(lab)
                dens = []
                if re.search(r"wf-\d+-f|dt-\d+-f", vn) or vn in ("flottant",) or "float" in vn and "/" not in vn:
                    dens.append("Flottante")
                if gn == "densité" or gn == "densite":
                    for k, lab in (("flottant", "Flottante"), ("hover", "Hover"), (r"\bh\b", "Hover"), (r"\bf\b", "Flottante"),
                                   ("interm", "Intermédiaire"), (r"\bint\b|\bi\b", "Intermédiaire"), ("lent", "Plongeante S2-S3"),
                                   ("extra super", "Plongeante S7-S8"), ("super rapide", "Plongeante S6"),
                                   ("rapide", "Plongeante S4-S5"), (r"type\s*(\d)", None), (r"(\d)\s*ips", None), (r"s(\d)", None)):
                        for m in re.finditer(k, vn):
                            dens.append(lab or f"Plongeante S{m.group(1)}")
                if gn == "tip configuration" or gn == "skagit tip":
                    dens.append("Pointe plongeante" if re.search(r"t-\d", vn) else "Flottante")
                add("numero_soie", span(ws))
                add("grains", gr)
                add("profil", prof)
                add("densite", uniq(dens))
                parts = uniq(["#" + "/".join(map(str, uniq(ws))) if ws else None,
                              f"{gr[0]} gr" if gr else None] + prof + uniq(dens))
                facette = "numero_soie" if ws else "grains" if gr else "densite" if dens else ""
                st = "ok" if parts else "option libre"
                row(v, a, facette, " · ".join(parts), st)
                specs.append((g, val, " · ".join(parts)))
                continue
            if famille == "avancons":
                x = re.search(r"\b(\d(?:\.5)?)\s*x\b", vn)
                lb = re.search(r"(\d+(?:[.,]\d+)?)\s*(?:lb|lbs|livres)\b", vn)
                ln = feet_inches(val) if gn == "longueur" else None
                parts = []
                if x:
                    add("diametre_x", x.group(1) + "X")
                    parts.append(x.group(1) + "X")
                if lb:
                    n = float(lb.group(1).replace(",", "."))
                    add("resistance_lb", int(n) if n.is_integer() else n)
                    parts.append(f"{lb.group(1)} lb")
                if ln:
                    add("longueur", ln[1])
                    parts.append(ln[1])
                row(v, a, "diametre_x" if x else "resistance_lb" if lb else "longueur" if ln else "",
                    " · ".join(parts), "ok" if parts else "option libre")
                specs.append((g, val, " · ".join(parts)))
                continue
            if famille in ("mouches", "hamecons"):
                if "quantit" in gn:
                    add("quantite_paquet", vn)
                    row(v, a, "quantite_paquet", vn, "ok")
                    continue
                if re.search(r"hame|grosseur", gn):
                    hs = hook_size(val)
                    if hs:
                        add("taille_hamecon", hs)
                        row(v, a, "taille_hamecon", hs, "ok")
                    else:
                        add("taille_hamecon", val)
                        row(v, a, "taille_hamecon", val, "option libre")
                    continue
            if famille == "billes":
                mm = inch_mm(vn)
                if mm and re.search(r"grosseur|taille|bille", gn):
                    add("diametre_mm", f"{mm:g} mm")
                    row(v, a, "diametre_mm", f"{mm:g} mm", "ok")
                    specs.append((g, val, f"{mm:g} mm"))
                    continue
            if famille == "vetements" and re.search(r"grandeur|taille|pantalon", gn):
                r = apparel(g, val)
                for k, vals in r.items():
                    add(k, vals)
                lab = " · ".join(f"{tax.data['facettes'][k]['fr']} {', '.join(vals)}" for k, vals in r.items() if vals)
                row(v, a, next((k for k, vals in r.items() if vals), ""), lab, "ok" if lab else "à_réviser")
                if not lab:
                    alerts.append(f"taille « {val} » non lue")
                continue
            if famille == "lunettes" or gn in LENS_GROUPS:
                if gn in LENS_GROUPS or "verre" in gn:
                    add("teinte_verre", val)
                    row(v, a, "teinte_verre", val, "ok")
                elif is_color_group(g):
                    fams, _, st = color_families(val, tax)
                    add("couleur", fams)
                    row(v, a, "couleur", ", ".join(fams), st)
                else:
                    row(v, a, "", "", "option libre")
                continue
            if famille in ("materiaux", "billes", "mouches", "outils", "generique", "rangement") and \
                    re.search(r"grosseur|grandeur|largeur|longueur|taille|quantit|fil|chenille|anneau", gn):
                add("taille", re.sub(r"\s+", " ", val.strip(" .")))
                row(v, a, "taille", val.strip(" ."), "ok")
                continue
            row(v, a, "", "", "option libre")

    # Taille de moulinet portée par le nom (« Ace 7/8 »)
    if famille == "moulinets" and not F["numero_soie"] and cat == "moulinets-mouche":
        ws = [int(x) for x in re.findall(r"\b(\d{1,2})\b", re.sub(r"\d{4,}", "", t(p["name"]))) if int(x) <= 15]
        if ws:
            add("numero_soie", span(ws))
            specs.append(("Taille (nom)", t(p["name"]), f"#{min(ws)}–{max(ws)}"))
    if famille == "soies" and not F["numero_soie"]:
        add("numero_soie", span(weights(name)))
    if famille == "cannes" and F["numero_soie"] and not F["type_canne"]:
        add("type_canne", "Une main")
    if famille == "billes" and not F["materiau_bille"] and re.search(r"yeux|eyes", name):
        add("materiau_bille", "Yeux")

    for k in ("numero_soie",):
        if F[k]:
            F[k] = sorted(set(F[k]))
    if F["longueur"]:
        F["longueur"] = sorted(set(F["longueur"]), key=lambda s: feet_inches(s)[0] if feet_inches(s) else 0)
    if F["taille_hamecon"]:
        F["taille_hamecon"] = sorted(set(F["taille_hamecon"]), key=lambda s: hook_sort(s) if hook_size(s) else 99)
    if F["taille"] and all(s in SIZE_ORDER for s in F["taille"]):
        F["taille"] = sorted(F["taille"], key=SIZE_ORDER.index)
    return {k: v for k, v in F.items() if v}, specs, rows, alerts


# ---------------------------------------------------------------- tags et badges

FAMILLES_ESPECE = {"cannes", "moulinets", "soies", "avancons", "mouches", "hamecons"}
CAT_DEFAULTS = {
    "seches": ["technique:seche", "espece:truite"], "nymphes": ["technique:nymphe", "espece:truite"],
    "noyees": ["technique:noyee", "espece:truite"], "streamers": ["technique:streamer"],
    "mouches-saumon": ["espece:saumon"], "mouches-eau-salee": ["technique:eau-salee", "milieu:eau-salee"],
    "cannes-spey-switch": ["technique:spey"], "soies-spey": ["technique:spey"], "running-lines": ["technique:spey"],
    "pointes": ["technique:spey"], "flottants": ["technique:seche"],
}


def tags_for(p, cat, famille, tax, infos, brand, facets, ref_date):
    name = norm(t(p["name"]) + " | " + t(p["name"], "en"))
    tags = set(CAT_DEFAULTS.get(cat, []))
    for i in infos:
        tags.update(x for x in i.get("tags", []) if not x.startswith("genre:"))
    if famille in FAMILLES_ESPECE or cat in ("kits-montage", "livres", "ensembles"):
        for kind in ("espece", "technique"):
            for tg in tax.data["tags"][kind]:
                if any(has_word(name, m) for m in tg["mots"]):
                    tags.add(f"{kind}:{tg['id']}")
    if famille == "hamecons":
        tags.update(f"technique:{u}" for u in (("seche" if "Sèche" in facets.get("usage_hamecon", []) else None),
                                                 ("nymphe" if "Nymphe" in facets.get("usage_hamecon", []) else None),
                                                 ("streamer" if "Streamer" in facets.get("usage_hamecon", []) else None)) if u)
    if "Tropical" in facets.get("climat", []):
        tags.add("espece:tropical")
    if "Eau salée froide" in facets.get("climat", []):
        tags.add("espece:bar-raye")
    if {"espece:tropical", "espece:bar-raye", "technique:eau-salee"} & tags or re.search(r"\bsalt|salée", name):
        tags.add("milieu:eau-salee")
        if famille in FAMILLES_ESPECE:
            tags.add("technique:eau-salee")
    # Une mouche à saumon n'est pas une mouche à truite
    if "espece:saumon" in tags and cat == "mouches-saumon":
        tags.discard("espece:truite")

    br = tax.data["badges_regles"]
    badges = []
    if p["date_add"] and datetime.fromisoformat(p["date_add"]) >= ref_date - timedelta(days=br["nouveau_jours"]):
        badges.append("nouveau")
    if p["price"]["promo"] or p["price"].get("some_variants_on_promo"):
        badges.append("promo")
    q = p["stock"]["quantity"]
    if 0 < q <= br["dernieres_unites_max"]:
        badges.append("dernieres-unites")
    if q <= 0:
        badges.append("sur-commande" if p["stock"]["sell_when_out_of_stock"] else "epuise")
    if brand in br["signature_marques"] or any(m in name for m in br["signature_mots"]):
        badges.append("signature")
    if brand in br["quebec_marques"] or any(m in name for m in br["quebec_mots"]):
        badges.append("quebec")
    if any(c["id"] in br["choix_equipe_anciennes_categories"] for c in p["categories"]):
        badges.append("choix-equipe")
    if cat in br["debutant_categories"] or any(m in name for m in br["debutant_mots"]):
        badges.append("debutant")
    return sorted(tags), badges


# ---------------------------------------------------------------- principal

def main():
    src, out = Path(sys.argv[1]), Path(sys.argv[2])
    doc = json.loads(src.read_text(encoding="utf-8"))
    tax = Taxonomie(json.loads((HERE / "taxonomie.json").read_text(encoding="utf-8")))
    dest = out / "recategorisation"
    dest.mkdir(parents=True, exist_ok=True)
    base = doc["meta"]["base_url"]

    cats_old = {c["id"]: dict(c, has_children=False) for c in doc["categories"]}
    for c in doc["categories"]:
        if c["parent_id"] in cats_old:
            cats_old[c["parent_id"]]["has_children"] = True
    ref_date = max(datetime.fromisoformat(p["date_add"]) for p in doc["products"] if p["date_add"])

    tag_label = {f"{k}:{x['id']}": x["fr"] for k in ("espece", "technique") for x in tax.data["tags"][k]}
    tag_label["milieu:eau-salee"] = "Eau salée"
    badge_label = {b["id"]: b["fr"] for k in ("badges_auto", "badges_manuels") for b in tax.data["tags"][k]}
    fac_label = {k: v["fr"] for k, v in tax.data["facettes"].items()}

    results, var_rows, prod_rows = [], [], []
    for p in doc["products"]:
        cat, second, source, infos, old_brands = classify(p, tax, cats_old)
        brand, bsrc = brand_of(p, tax, old_brands)
        famille = tax.nodes[cat]["famille"] if cat else "generique"
        facets, specs, rows, alerts = facets_for(p, cat, famille, tax, infos, brand)
        if famille == "moulinets" and "Bobine" in facets.get("moulinet_bobine", []) and "bobines" not in second:
            second.append("bobines")
        tags, badges = tags_for(p, cat, famille, tax, infos, brand, facets, ref_date)
        var_rows += rows

        raisons = uniq(alerts)
        if not cat:
            raisons.insert(0, "aucune catégorie")
        # Les matériaux en vrac emballés par la boutique n'ont pas de marque : ce n'est pas une erreur
        if not brand and famille not in ("materiaux", "billes", "outils"):
            raisons.append("marque inconnue")
        need = {"cannes": ["numero_soie", "longueur"], "moulinets": ["numero_soie"], "soies": ["numero_soie"],
                "hamecons": ["taille_hamecon", "usage_hamecon"], "mouches": ["taille_hamecon"]}.get(famille, [])
        if cat in ("etuis-cannes", "etuis-moulinets", "entretien-soies", "flottants", "corde-reserve", "bobines",
                   "pointes", "running-lines"):
            need = []
        miss = [fac_label[k] for k in need if not facets.get(k)
                and not (k == "numero_soie" and famille == "soies" and facets.get("grains"))]
        if miss and p["variants"]:
            raisons.append("filtre manquant : " + ", ".join(miss))
        a_classer = sum(1 for r in rows if r["statut"] == "à_classer")
        if a_classer:
            raisons.append(f"{a_classer} couleur(s) à classer")
        grave = not cat or any(r.startswith(("canne", "moulinet", "taille", "filtre")) for r in raisons)
        confiance = "basse" if not cat else "moyenne" if (grave or source == "regle" or bsrc in ("", "champ_non_reconnu")) else "haute"

        seo = p["seo"]
        new_url = {l: f"{base}/{l}/{tax.url(cat, l)}/{seo[l]['slug']}" if cat else "" for l in ("fr", "en")}
        dc = p.get("default_category") or {}
        r = {
            "id": p["id"], "cat": cat, "second": second, "source": source, "brand": brand, "brand_src": bsrc,
            "famille": famille, "facets": facets, "specs": specs, "tags": tags, "badges": badges,
            "raisons": raisons, "confiance": confiance, "new_url": new_url, "nv": len(p["variants"]),
            "old_path": t(dc.get("path")) or " ; ".join(t(c["path"]) for c in p["categories"]),
        }
        results.append(r)
        prod_rows.append({
            "produit_id": p["id"], "nom_fr": t(p["name"]), "nom_en": t(p["name"], "en"),
            "marque_avant": p["brand"] or "", "marque": brand, "source_marque": bsrc,
            "ancienne_categorie": r["old_path"],
            "nouvelle_categorie_id": cat or "", "nouvelle_categorie": tax.path(cat),
            "nouvelle_categorie_en": tax.path(cat, "en"),
            "categories_secondaires": " | ".join(tax.path(c) for c in second),
            "source_classement": source,
            "especes": " | ".join(tag_label[x] for x in tags if x.startswith("espece:")),
            "techniques": " | ".join(tag_label[x] for x in tags if x.startswith("technique:")),
            "milieu": "Eau salée" if "milieu:eau-salee" in tags else "",
            "badges": " | ".join(badge_label[b] for b in badges),
            "filtres": " ; ".join(f"{fac_label[k]} : {', '.join(map(str, v))}" for k, v in facets.items()),
            "confiance": confiance, "a_reviser": "oui" if raisons else "",
            "raisons": " ; ".join(raisons),
            "url_actuelle_fr": seo["fr"]["url"], "nouvelle_url_fr": new_url["fr"], "nouvelle_url_en": new_url["en"],
        })

    by_id = {r["id"]: r for r in results}

    # -------- vérifications (bloquantes sur le catalogue actif, simples avertissements avec --tout)
    count = Counter(r["cat"] for r in results)
    count_all = Counter()
    for r in results:
        for c in {x for cc in [r["cat"]] + r["second"] if cc for x in tax.chain(cc)}:
            count_all[c] += 1
    problems = [
        ("Produits sans catégorie", [r["id"] for r in results if not r["cat"]]),
        ("Catégories vides", [c for c in tax.nodes if not count_all[c]]),
        ("Anciennes catégories sans cible", [c["id"] for c in doc["categories"] if c["id"] not in tax.old]),
    ]
    for label, ids in problems:
        if ids:
            if doc["meta"].get("mode") == "catalogue_actif":
                sys.exit(f"{label} : {ids}")
            print(f"Avertissement — {label} : {ids}", file=sys.stderr)

    # -------- catégories
    old_rows = []
    for c in sorted(doc["categories"], key=lambda c: t(c["path"])):
        m = tax.old.get(c["id"])
        cible = (m or {}).get("redirige") or (m or {}).get("cat")
        marque = (m or {}).get("marque")
        if c["id"] == 2:
            devient, fr, en = "accueil", f"{base}/fr/", f"{base}/en/"
        elif marque and cible and slug(marque) in slug(t(c["name"])):
            devient = f"filtre Marque = {marque} (dans {tax.path(cible)})"
            fr = f"{base}/fr/{tax.url(cible, 'fr')}?marque={slug(marque)}"
            en = f"{base}/en/{tax.url(cible, 'en')}?brand={slug(marque)}"
        elif cible:
            devient = f"catégorie {tax.path(cible)}"
            fr, en = f"{base}/fr/{tax.url(cible, 'fr')}", f"{base}/en/{tax.url(cible, 'en')}"
        else:
            devient = (m or {}).get("devient", "supprimée")
            fr, en = f"{base}/fr/", f"{base}/en/"
        old_rows.append({
            "ancienne_id": c["id"], "ancien_chemin": t(c["path"]), "nb_produits": c["product_count"],
            "devient": devient, "nouvelle_categorie_id": cible or "", "marque": marque or "",
            "ancienne_url_fr": c["seo"]["fr"]["url"], "redirection_301_fr": fr,
            "ancienne_url_en": c["seo"]["en"]["url"], "redirection_301_en": en,
        })
    redirect = {(r["ancienne_id"], "fr"): r["redirection_301_fr"] for r in old_rows}
    redirect.update({(r["ancienne_id"], "en"): r["redirection_301_en"] for r in old_rows})

    new_rows = []
    for cid, n in tax.nodes.items():
        new_rows.append({
            "id": cid, "chemin_fr": tax.path(cid), "chemin_en": tax.path(cid, "en"), "niveau": n["depth"],
            "famille": n["famille"], "nb_produits_principale": count[cid], "nb_produits_total": count_all[cid],
            "filtres": ", ".join(fac_label[k] for k in tax.data["familles"][n["famille"]]),
            "url_fr": f"{base}/fr/{tax.url(cid, 'fr')}", "url_en": f"{base}/en/{tax.url(cid, 'en')}",
        })

    # -------- couleurs
    col = {}
    for r in var_rows:
        if r["facette"] == fac_label["couleur"]:
            k = r["libelle_fabricant"].strip()
            e = col.setdefault(k, {"libelle": k, "nb_variantes": 0, "groupes": set(), "produits": set()})
            e["nb_variantes"] += 1
            e["groupes"].add(r["groupe"])
            e["produits"].add(r["produit"])
    color_rows = []
    for k, e in sorted(col.items(), key=lambda x: (-x[1]["nb_variantes"], x[0])):
        fams, mods, st = color_families(k, tax)
        color_rows.append({"libelle_fabricant": k, "familles": ", ".join(fams), "uv": "oui" if "uv" in mods else "",
                           "fluo": "oui" if "fluo" in mods else "", "holo": "oui" if "holo" in mods else "",
                           "statut": st, "famille_a_assigner": "", "nb_variantes": e["nb_variantes"],
                           "groupes": " | ".join(sorted(e["groupes"])),
                           "exemples_produits": " | ".join(sorted(e["produits"])[:3])})

    # -------- url
    url_rows = []
    for p in doc["products"]:
        for l, s in p["seo"].items():
            url_rows.append({"type": "produit", "id": p["id"], "langue": l, "nom": t(p["name"], l),
                             "ancienne_url": s["url"], "nouvelle_url": by_id[p["id"]]["new_url"][l]})
    for c in doc["categories"]:
        for l, s in c["seo"].items():
            url_rows.append({"type": "categorie", "id": c["id"], "langue": l, "nom": t(c["name"], l),
                             "ancienne_url": s["url"], "nouvelle_url": redirect[(c["id"], l)]})

    # -------- écriture
    write_csv(dest / "mapping_produits.csv", prod_rows)
    write_csv(dest / "mapping_variantes.csv", var_rows)
    write_csv(dest / "correspondance_categories.csv", old_rows)
    write_csv(dest / "nouvelles_categories.csv", new_rows)
    write_csv(dest / "couleurs.csv", color_rows)
    write_csv(dest / "url_mapping.csv", url_rows)
    wb = Workbook()
    wb.remove(wb.active)
    for title, rows in (("Produits", prod_rows), ("Nouvelles categories", new_rows),
                        ("Anciennes categories", old_rows), ("Variantes", var_rows), ("Couleurs", color_rows),
                        ("URL", url_rows)):
        add_sheet(wb, title, rows)
    wb.save(dest / "recategorisation.xlsx")

    stats = compute_stats(doc, results, var_rows, tax, count, color_rows)
    (dest / "rapport.md").write_text(report(stats, results, prod_rows, tax), encoding="utf-8")
    write_html(dest / "avant_apres.html", doc, results, tax, stats, old_rows, color_rows)
    print(f"Recatégorisation : {len(results)} produits -> {len(tax.nodes)} catégories ; "
          f"{stats['a_reviser']} à réviser. Voir {dest}")


def compute_stats(doc, results, var_rows, tax, count, color_rows):
    P = doc["products"]
    groups = {t(a["group"]) for p in P for v in p["variants"] for a in v["attributes"]}
    used = {k for r in results for k in r["facets"]}
    st = Counter(r["statut"] for r in var_rows)
    fam_need = {"cannes": "numero_soie", "moulinets": "numero_soie", "soies": "numero_soie",
                "hamecons": "taille_hamecon", "mouches": "taille_hamecon"}
    couverture = {}
    for fam, key in fam_need.items():
        rs = [r for r in results if r["famille"] == fam and r["nv"] and r["cat"] not in
              ("etuis-cannes", "etuis-moulinets", "entretien-soies", "flottants", "corde-reserve", "pointes", "running-lines")]
        couverture[fam] = (sum(1 for r in rs if r["facets"].get(key)), len(rs))
    mat = [r for r in results if r["famille"] in ("materiaux", "billes", "mouches") and r["nv"]]
    return {
        "produits": len(P), "variantes": sum(len(p["variants"]) for p in P),
        "cat_avant": len(doc["categories"]), "cat_apres": len(tax.nodes),
        "prof_avant": max(c["depth"] for c in doc["categories"]), "prof_apres": max(n["depth"] for n in tax.nodes.values()),
        "cat_marque_avant": sum(1 for v in tax.old.values() if v and v.get("marque")
                                and v.get("cat") and True),
        "groupes_avant": len(groups), "facettes_apres": len(used),
        "marque_avant": sum(1 for p in P if p["brand"]), "marque_apres": sum(1 for r in results if r["brand"]),
        "tags_avant": sum(1 for p in P if (p.get("tags") or {}).get("fr")),
        "espece_apres": sum(1 for r in results if any(x.startswith("espece:") for x in r["tags"])),
        "tags_apres": sum(1 for r in results if r["tags"]),
        "badges_apres": Counter(b for r in results for b in r["badges"]),
        "var_statuts": dict(st), "couverture": couverture,
        "couleurs": Counter(c["statut"] for c in color_rows), "nb_couleurs": len(color_rows),
        "a_reviser": sum(1 for r in results if r["raisons"]),
        "confiance": Counter(r["confiance"] for r in results),
        "sources": Counter(r["source"] for r in results), "brand_src": Counter(r["brand_src"] for r in results),
        "mat_couleur": (sum(1 for r in mat if r["facets"].get("couleur")), len(mat)),
        "count": dict(count),
    }


def report(s, results, prod_rows, tax):
    pct = lambda a, b: f"{a}/{b} ({100 * a // max(b, 1)} %)"
    L = ["# Rapport de recatégorisation", "",
         f"{s['produits']} produits, {s['variantes']} variantes.", "",
         "## Avant / après", "",
         "| | Avant | Après |", "|---|---|---|",
         f"| Catégories | {s['cat_avant']} | {s['cat_apres']} |",
         f"| Profondeur max | {s['prof_avant']} | {s['prof_apres']} |",
         f"| Groupes d'attributs → filtres | {s['groupes_avant']} groupes | {s['facettes_apres']} filtres |",
         f"| Produits avec une marque | {pct(s['marque_avant'], s['produits'])} | {pct(s['marque_apres'], s['produits'])} |",
         f"| Produits avec des tags | {s['tags_avant']} | {s['tags_apres']} |",
         f"| Produits avec une espèce | 0 | {s['espece_apres']} |", "",
         "## Couverture des filtres clés", "",
         "| Famille | Filtre | Couverture |", "|---|---|---|"]
    for fam, (a, b) in s["couverture"].items():
        L.append(f"| {fam} | {'Numéro de soie' if fam in ('cannes', 'moulinets', 'soies') else 'Taille d’hameçon'} | {pct(a, b)} |")
    a, b = s["mat_couleur"]
    L += [f"| matériaux, billes, mouches | Couleur | {pct(a, b)} |", "",
          "## Variantes", "",
          "Chaque option de variante (groupe + valeur) est reliée à un filtre ou gardée comme option libre du produit.", ""]
    L += [f"- {k} : {v}" for k, v in sorted(s["var_statuts"].items(), key=lambda x: -x[1])]
    L += ["", f"## Couleurs ({s['nb_couleurs']} libellés distincts)", ""]
    L += [f"- {k} : {v}" for k, v in s["couleurs"].items()]
    L += ["", "Les libellés `à_classer` sont des noms commerciaux sans mot de couleur : colonne `famille_a_assigner` de `couleurs.csv`.", "",
          "## Classement", "",
          "Source de la catégorie : " + ", ".join(f"{k or '—'} {v}" for k, v in s["sources"].items()) + ".",
          "Source de la marque : " + ", ".join(f"{k or 'aucune'} {v}" for k, v in s["brand_src"].items()) + ".",
          "Confiance : " + ", ".join(f"{k} {v}" for k, v in s["confiance"].items()) + ".", "",
          "## Badges calculés", ""]
    lab = {b["id"]: b["fr"] for k in ("badges_auto", "badges_manuels") for b in tax.data["tags"][k]}
    L += [f"- {lab[k]} : {v}" for k, v in s["badges_apres"].most_common()]
    L += ["", "## Nouvelle arborescence", "", "| Catégorie | Produits (principale) |", "|---|---|"]
    for cid, n in tax.nodes.items():
        L.append(f"| {'  ' * (n['depth'] - 1)}{'**' if n['depth'] == 1 else ''}{n['fr']}{'**' if n['depth'] == 1 else ''} | {s['count'].get(cid, 0)} |")
    rev = [r for r in prod_rows if r["a_reviser"]]
    L += ["", f"## À réviser ({len(rev)})", "",
          "Hors « marque inconnue » et « couleurs à classer », qui se règlent en lot dans les CSV. Couverture calculée sur les produits qui ont des variantes.", "",
          "| Id | Produit | Catégorie | Raisons |", "|---|---|---|---|"]
    for r in rev:
        rs = [x for x in r["raisons"].split(" ; ") if x != "marque inconnue" and "couleur(s) à classer" not in x]
        if rs:
            L.append(f"| {r['produit_id']} | {r['nom_fr']} | {r['nouvelle_categorie']} | {' ; '.join(rs)} |")
    return "\n".join(L) + "\n"


def write_html(path, doc, results, tax, stats, old_rows, color_rows):
    """Page autonome avant/après : gabarit avant_apres.html + données intégrées."""
    by_id = {r["id"]: r for r in results}
    prods = []
    for p in doc["products"]:
        r = by_id[p["id"]]
        prods.append({
            "id": p["id"], "n": t(p["name"]), "ne": t(p["name"], "en"), "bo": p["brand"] or "",
            "img": p["images"][0]["url"] if p["images"] else "", "pr": p["price"]["final"],
            "rp": p["price"]["regular"], "mn": p["price"].get("min"), "mx": p["price"].get("max"),
            "st": p["stock"]["quantity"], "url": p["seo"]["fr"]["url"],
            "oc": (p.get("default_category") or {}).get("id"), "ocs": [c["id"] for c in p["categories"]],
            "op": r["old_path"], "ot": (p.get("tags") or {}).get("fr") or [],
            "oa": uniq(f"{t(a['group'])} : {t(a['value'])}" for v in p["variants"] for a in v["attributes"])[:60],
            "nv": len(p["variants"]),
            # Variantes telles que vendues : libellés d'origine, prix, stock ; la famille de couleur sert à la pastille
            "vs": [{"a": [[t(a["group"]), t(a["value"]),
                           color_families(t(a["value"]), tax)[0] if is_color_group(t(a["group"])) else None]
                          for a in v["attributes"]],
                    "pr": v["price"]["final"], "rp": v["price"]["regular"], "q": v["quantity"]}
                   for v in p["variants"]],
            "c": r["cat"], "c2": r["second"], "b": r["brand"], "f": r["facets"], "sp": r["specs"][:40],
            "tg": r["tags"], "bd": r["badges"], "cf": r["confiance"], "rs": r["raisons"], "nu": r["new_url"]["fr"],
        })
    data = {
        "meta": {"generated": doc["meta"]["generated_at"], "source": doc["meta"]["source"]},
        "stats": {k: v for k, v in stats.items() if k not in ("count",)} | {
            "badges_apres": dict(stats["badges_apres"]), "confiance": dict(stats["confiance"]),
            "couleurs": dict(stats["couleurs"]), "sources": dict(stats["sources"]), "brand_src": dict(stats["brand_src"])},
        "old": [{"id": c["id"], "p": c["parent_id"], "n": t(c["name"]), "path": t(c["path"]),
                 "pos": c["position"], "d": c["depth"]} for c in doc["categories"]],
        "tree": tax.data["arbre"], "familles": tax.data["familles"],
        "facettes": {k: {"fr": v["fr"], "type": v.get("type", "")} for k, v in tax.data["facettes"].items()},
        "tags": {k: [{"id": x["id"], "fr": x["fr"]} for x in tax.data["tags"][k]] for k in tax.data["tags"]},
        "couleurs": [{"id": f["id"], "fr": f["fr"], "hex": f["hex"]} for f in tax.couleurs["familles"]],
        "corr": [{k: r[k] for k in ("ancienne_id", "ancien_chemin", "nb_produits", "devient")} for r in old_rows],
        "col": [{k: r[k] for k in ("libelle_fabricant", "familles", "statut", "nb_variantes", "groupes")} for r in color_rows],
        "products": prods,
    }
    tpl = (HERE / "avant_apres.html").read_text(encoding="utf-8")
    payload = json.dumps(data, ensure_ascii=False, separators=(",", ":")).replace("</", "<\\/")
    path.write_text(tpl.replace("/*__DATA__*/", payload), encoding="utf-8")


if __name__ == "__main__":
    main()
