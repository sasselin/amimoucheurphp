#!/usr/bin/env bash
# Caddy d'entrée du serveur, en root, une fois : il prend 80/443 à la place du conteneur caddy de ce site, qui
# passe derrière lui en http sur 127.0.0.1:8081. Coupure de www et proto d'environ une seconde : à lancer à
# une heure creuse. Depuis la racine du dépôt :
#   scp deploy/entree-root.sh deploy/entree.caddy deploy/Caddyfile deploy/docker-compose.prod.yml \
#       sass@143.198.43.249:/tmp/
#   ssh -t sass@143.198.43.249 sudo bash /tmp/entree-root.sh
#
# Le Caddyfile et le docker-compose.yml de ce site ne sont remplacés qu'au moment de recréer le conteneur :
# avant, un « docker compose up » aurait retiré 80/443 au site sans Caddy d'entrée pour les reprendre.
# Tout est vérifié avant de toucher au service.
#
# Le Caddy d'entrée n'a que des imports : un fichier de domaines par site dans /etc/caddy/sites/, fourni par le
# dépôt de chaque site (ici deploy/entree.caddy → amimoucheur.caddy). Relançable : il ne fait que ce qui manque.
set -euo pipefail
here=$(dirname "$0")
dir=/srv/amimoucheur
uid=$(id -u amimoucheur)
volume=/home/amimoucheur/.local/share/docker/volumes/amimoucheur_caddy-data/_data/caddy
storage=/var/lib/caddy/.local/share/caddy

grep -q '127.0.0.1:8081:80' "$here/docker-compose.prod.yml" || { echo "docker-compose.prod.yml pas à jour" >&2; exit 1; }
grep -q '^http://www.amimoucheur.com' "$here/Caddyfile" || { echo "Caddyfile pas à jour" >&2; exit 1; }

if ! command -v caddy >/dev/null; then
    apt-get install -y debian-keyring debian-archive-keyring apt-transport-https curl
    curl -1sLf https://dl.cloudsmith.io/public/caddy/stable/gpg.key |
        gpg --dearmor -o /usr/share/keyrings/caddy-stable-archive-keyring.gpg
    curl -1sLf https://dl.cloudsmith.io/public/caddy/stable/debian.deb.txt > /etc/apt/sources.list.d/caddy-stable.list
    apt-get update
    # Le paquet démarre caddy aussitôt ; il échoue tant que le conteneur tient 80/443 : sans effet.
    apt-get install -y caddy || true
    systemctl stop caddy || true
fi

install -d -m 755 /etc/caddy/sites
install -m 644 "$here/entree.caddy" /etc/caddy/sites/amimoucheur.caddy
cat > /etc/caddy/Caddyfile <<'CADDY'
# Caddy d'entrée du serveur (root) : seul sur 80/443, TLS, puis chaque domaine vers le Caddy de son site.
# Un fichier par site, fourni par son dépôt. Ports réservés par usager : deploy/README.md du dépôt amimoucheur.
import /etc/caddy/sites/*.caddy
CADDY
caddy validate --config /etc/caddy/Caddyfile --adapter caddyfile

if ! systemctl is-active --quiet caddy; then
    # Certificats du conteneur : www et proto servis sans attendre Let's Encrypt.
    install -d -o caddy -g caddy "$storage"
    cp -a "$volume/certificates" "$volume/acme" "$storage/"
    chown -R caddy:caddy "$storage"
    install -m 644 -o amimoucheur -g amimoucheur "$here/Caddyfile" "$dir/Caddyfile"
    install -m 644 -o amimoucheur -g amimoucheur "$here/docker-compose.prod.yml" "$dir/docker-compose.yml"
    sudo -u amimoucheur env XDG_RUNTIME_DIR="/run/user/$uid" DOCKER_HOST="unix:///run/user/$uid/docker.sock" \
        docker compose -f "$dir/docker-compose.yml" --project-directory "$dir" up -d caddy
    systemctl enable --now caddy
else
    systemctl reload caddy
fi

for host in www.amimoucheur.com proto.amimoucheur.com; do
    printf '%s : ' "$host"
    curl -sS -o /dev/null -w '%{http_code}\n' --resolve "$host:443:127.0.0.1" "https://$host/" || true
done
echo "✓ Caddy d'entrée en service. Retour en arrière : systemctl disable --now caddy, remettre l'ancien Caddyfile"
echo "  et docker-compose.yml dans $dir (git du dépôt), puis « docker compose up -d caddy » en tant qu'amimoucheur."
