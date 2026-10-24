"""Accès à proto.amimoucheur.com par code envoyé par courriel.

Caddy demande /verifier (forward_auth) avant chaque fichier : 200 si le témoin de
session est valide, sinon une redirection vers /connexion. Le visiteur y tape son
courriel ; s'il figure dans la liste, il reçoit un code à 6 chiffres (relais Brevo,
mêmes accès que mail() de PrestaShop). Le bon code donne un témoin signé de 30 jours.

Données (dossier /data, monté depuis /srv/amimoucheur/proto-auth) :
- courriels.txt : une adresse par ligne, ou « @domaine.com » pour tout un domaine.
  Relu à chaque requête : retirer une adresse coupe son accès tout de suite.
- secret : clé de signature des témoins, créée au premier démarrage.
  La changer déconnecte tout le monde.
Python seul, sans dépendance.
"""

import base64
import hashlib
import hmac
import html
import secrets
import smtplib
import threading
import time
from email.message import EmailMessage
from http.cookies import SimpleCookie
from http.server import BaseHTTPRequestHandler, ThreadingHTTPServer
from pathlib import Path
from urllib.parse import parse_qs

DATA = Path("/data")
MSMTPRC = Path("/etc/msmtprc")
COOKIE = "proto_session"
SESSION = 30 * 24 * 3600
CODE_VALIDITE = 15 * 60
CODE_ESSAIS = 5
ENVOIS_PAR_HEURE = 5        # par adresse
ENVOIS_GLOBAL_HEURE = 50    # tous visiteurs confondus

verrou = threading.Lock()
codes = {}       # courriel -> (empreinte du code, expiration, essais restants)
envois = {}      # courriel -> [instants d'envoi]
envois_global = []


def cle():
    f = DATA / "secret"
    if not f.exists():
        f.write_text(secrets.token_hex(32))
        f.chmod(0o600)
    return f.read_text().strip().encode()


SECRET = cle()


def autorise(courriel):
    try:
        lignes = (DATA / "courriels.txt").read_text().splitlines()
    except FileNotFoundError:
        return False
    for l in lignes:
        l = l.split("#")[0].strip().lower()
        if not l:
            continue
        if l == courriel or (l.startswith("@") and courriel.endswith(l)):
            return True
    return False


def signe(texte):
    return hmac.new(SECRET, texte.encode(), hashlib.sha256).hexdigest()


def temoin(courriel):
    fin = int(time.time()) + SESSION
    corps = base64.urlsafe_b64encode(courriel.encode()).decode() + "." + str(fin)
    return corps + "." + signe(corps)


def lit_temoin(valeur):
    try:
        b64, fin, sig = valeur.split(".")
        corps = b64 + "." + fin
        if not hmac.compare_digest(sig, signe(corps)) or int(fin) < time.time():
            return None
        courriel = base64.urlsafe_b64decode(b64).decode()
    except (ValueError, UnicodeDecodeError):
        return None
    return courriel if autorise(courriel) else None


def smtp_config():
    conf = {}
    for l in MSMTPRC.read_text().splitlines():
        parts = l.split(None, 1)
        if len(parts) == 2 and not parts[0].startswith("#"):
            conf[parts[0]] = parts[1].strip()
    return conf


def envoie_code(courriel, code):
    c = smtp_config()
    m = EmailMessage()
    m["From"] = f"A mi moucheur <{c['from']}>"
    m["To"] = courriel
    m["Subject"] = f"Votre code d'accès : {code}"
    m.set_content(
        f"Bonjour,\n\nVotre code pour voir la proposition du nouveau catalogue : {code}\n\n"
        f"Il est valide 15 minutes. Si vous n'avez rien demandé, ignorez ce message.\n"
    )
    with smtplib.SMTP(c["host"], int(c.get("port", 587)), timeout=20) as s:
        s.starttls()
        s.login(c["user"], c["password"])
        s.send_message(m)


def peut_envoyer(courriel):
    """Limite les envois ; à appeler sous verrou."""
    maintenant = time.time()
    global envois_global
    envois_global = [t for t in envois_global if t > maintenant - 3600]
    recents = [t for t in envois.get(courriel, []) if t > maintenant - 3600]
    if len(envois_global) >= ENVOIS_GLOBAL_HEURE or len(recents) >= ENVOIS_PAR_HEURE:
        return False
    if recents and recents[-1] > maintenant - 60:
        return False
    envois[courriel] = recents + [maintenant]
    envois_global.append(maintenant)
    return True


PAGE = """<!doctype html>
<html lang="fr"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<title>Accès : proposition du catalogue</title>
<style>
body{{font:16px/1.5 system-ui,sans-serif;background:#f4f4f2;color:#222;margin:0;display:grid;place-items:center;min-height:100vh}}
main{{background:#fff;padding:2rem;border-radius:10px;box-shadow:0 2px 12px #0001;width:min(24rem,calc(100vw - 2rem));box-sizing:border-box}}
h1{{font-size:1.25rem;margin:0 0 1rem}}
input,button{{font:inherit;width:100%;box-sizing:border-box;padding:.6rem;margin-top:.5rem;border-radius:6px}}
input{{border:1px solid #bbb}}
button{{background:#2f5d3a;color:#fff;border:0;cursor:pointer}}
p.note{{color:#555;font-size:.9rem}}
p.erreur{{color:#a12}}
</style></head><body><main>
<h1>Proposition du nouveau catalogue</h1>
{contenu}
</main></body></html>"""

FORM_COURRIEL = """<p>Entrez votre courriel : vous recevrez un code d'accès.</p>
{message}
<form method="post" action="/connexion">
<input type="email" name="courriel" required autofocus autocomplete="email" placeholder="vous@exemple.com">
<button>Recevoir un code</button>
</form>"""

FORM_CODE = """<p>Si <b>{courriel}</b> a accès, un code vient d'y être envoyé (vérifiez aussi les indésirables).</p>
{message}
<form method="post" action="/connexion/code">
<input type="hidden" name="courriel" value="{courriel}">
<input name="code" required autofocus inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{{6}}" maxlength="6" placeholder="123456">
<button>Entrer</button>
</form>
<p class="note"><a href="/connexion">Changer d'adresse ou redemander un code</a></p>"""


class Handler(BaseHTTPRequestHandler):
    def log_message(self, fmt, *args):
        pass  # pas de courriels dans les journaux

    def page(self, contenu, statut=200):
        corps = PAGE.format(contenu=contenu).encode()
        self.send_response(statut)
        self.send_header("Content-Type", "text/html; charset=utf-8")
        self.send_header("Cache-Control", "no-store")
        self.send_header("Content-Length", str(len(corps)))
        self.end_headers()
        self.wfile.write(corps)

    def redirige(self, cible, cookie=None):
        self.send_response(302)
        self.send_header("Location", cible)
        self.send_header("Cache-Control", "no-store")
        if cookie is not None:
            self.send_header("Set-Cookie", cookie)
        self.send_header("Content-Length", "0")
        self.end_headers()

    def formulaire(self):
        n = int(self.headers.get("Content-Length") or 0)
        donnees = parse_qs(self.rfile.read(min(n, 4096)).decode(errors="replace"))
        return {k: v[0].strip() for k, v in donnees.items()}

    def session(self):
        c = SimpleCookie(self.headers.get("Cookie", ""))
        return lit_temoin(c[COOKIE].value) if COOKIE in c else None

    def do_GET(self):
        chemin = self.path.split("?")[0]
        if chemin == "/verifier":
            if self.session():
                self.send_response(200)
                self.send_header("Content-Length", "0")
                self.end_headers()
            else:
                self.redirige("/connexion")
        elif chemin == "/connexion":
            if self.session():
                self.redirige("/")
            else:
                self.page(FORM_COURRIEL.format(message=""))
        elif chemin == "/deconnexion":
            self.redirige("/connexion", f"{COOKIE}=; Path=/; Max-Age=0; Secure; HttpOnly; SameSite=Lax")
        else:
            self.redirige("/connexion")

    def do_POST(self):
        chemin = self.path.split("?")[0]
        f = self.formulaire()
        courriel = f.get("courriel", "").lower()[:254]
        if chemin == "/connexion":
            if "@" not in courriel:
                self.page(FORM_COURRIEL.format(message='<p class="erreur">Adresse invalide.</p>'))
                return
            if autorise(courriel):
                with verrou:
                    ok = peut_envoyer(courriel)
                    if ok:
                        code = f"{secrets.randbelow(10**6):06d}"
                        codes[courriel] = (signe(code), time.time() + CODE_VALIDITE, CODE_ESSAIS)
                if ok:
                    try:
                        envoie_code(courriel, code)
                    except Exception as e:  # noqa: BLE001
                        print("Envoi impossible :", type(e).__name__, e, flush=True)
            # Même réponse que l'adresse soit autorisée ou non
            self.page(FORM_CODE.format(courriel=html.escape(courriel), message=""))
        elif chemin == "/connexion/code":
            code = f.get("code", "")
            with verrou:
                attendu = codes.get(courriel)
                bon = False
                if attendu and attendu[1] > time.time() and attendu[2] > 0:
                    bon = hmac.compare_digest(attendu[0], signe(code))
                    if bon:
                        del codes[courriel]
                    else:
                        codes[courriel] = (attendu[0], attendu[1], attendu[2] - 1)
            if bon and autorise(courriel):
                self.redirige("/", f"{COOKIE}={temoin(courriel)}; Path=/; Max-Age={SESSION}; Secure; HttpOnly; SameSite=Lax")
            else:
                msg = '<p class="erreur">Code incorrect ou expiré.</p>'
                self.page(FORM_CODE.format(courriel=html.escape(courriel), message=msg), 401)
        else:
            self.redirige("/connexion")


if __name__ == "__main__":
    ThreadingHTTPServer(("0.0.0.0", 8080), Handler).serve_forever()
