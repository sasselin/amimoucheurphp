#!/usr/bin/env bash
# Déploie le code de site/ vers la production (droplet, /srv/amimoucheur/site).
# Usage : ./deploy.sh                    (déploiement réel du code)
#         ./deploy.sh --dry-run          (affiche les changements sans rien envoyer)
#         ./deploy.sh --infra            + fichiers de deploy/ et reconstruction des conteneurs
#         Options combinables.
# Pas de --delete : ce qui manque en local n'est jamais effacé en prod.
set -euo pipefail

PROD_SSH="${PROD_SSH:-amimoucheur@143.198.43.249}"
PROD_DIR="${PROD_DIR:-/srv/amimoucheur}"
ROOT="$(cd "$(dirname "$0")" && pwd)"

DRY_RUN=()
INFRA=0
while [[ $# -gt 0 ]]; do
  case "$1" in
    --dry-run|-n) DRY_RUN=(--dry-run) ;;
    --infra) INFRA=1 ;;
    *) echo "Option inconnue : $1" >&2; exit 2 ;;
  esac
  shift
done

# Docker rootless : le socket est celui de l'usager amimoucheur
remote_compose() {
  ssh "$PROD_SSH" "export DOCKER_HOST=\${DOCKER_HOST:-unix:///run/user/\$(id -u)/docker.sock} && cd '$PROD_DIR' && docker compose $*"
}

# Exclus : config propre à la prod, fichiers générés par PrestaShop (.htaccess,
# config/xml, caches, rapports), fichiers créés par la prod (images et fichiers
# ajoutés dans l'admin, logs) et vieux exports à la racine.
echo "Code (site/)..."
rsync -azO --itemize-changes "${DRY_RUN[@]}" \
  --exclude /config/settings.inc.php \
  --exclude /config/defines_custom.inc.php \
  --exclude /.htaccess \
  --exclude /config/xml/ \
  --exclude /modules/gmerchantcenter/reporting/ \
  --exclude '/themes/*/cache/' \
  --exclude /cache/ \
  --exclude /img/ \
  --exclude /upload/ \
  --exclude /download/ \
  --exclude /log/ \
  --exclude /export-catalogue/ \
  --exclude '/*.csv' \
  --exclude '/gmerchantcenter*.xml' \
  --exclude '/*sitemap.xml' \
  "$ROOT/site/" "$PROD_SSH:$PROD_DIR/site/"

if [[ $INFRA == 1 ]]; then
  echo "Infra (deploy/)..."
  cd "$ROOT/deploy"
  rsync -az --itemize-changes "${DRY_RUN[@]}" \
    Dockerfile php-hardening.ini apache-site.conf apache-mpm.conf mysql-lowmem.cnf Caddyfile \
    "$PROD_SSH:$PROD_DIR/"
  rsync -az --itemize-changes "${DRY_RUN[@]}" docker-compose.prod.yml "$PROD_SSH:$PROD_DIR/docker-compose.yml"
fi

if [[ ${#DRY_RUN[@]} -eq 0 ]]; then
  [[ $INFRA == 1 ]] && remote_compose up -d --build
  # Vide les caches Smarty et l'index des classes (overrides) ; fichiers créés par www-data
  remote_compose exec -T apache sh -c "'rm -rf /var/www/html/cache/smarty/compile/* /var/www/html/cache/smarty/cache/* /var/www/html/cache/class_index.php'"
  echo "Déploiement terminé."
fi
