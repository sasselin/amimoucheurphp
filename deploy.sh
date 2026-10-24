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
# Propriétaires en prod : le code appartient à amimoucheur, les données générées
# (cache, img, upload, log, sitemaps...) à www-data du conteneur, qui apparaît
# sur l'hôte comme ct-www-data (UID/GID 165568, Docker rootless). Tout est au
# groupe ct-www-data, dossiers setgid. D'où -rlt sans -p/-g : rsync n'envoie que
# le contenu et ne touche pas aux propriétaires ni aux permissions de la prod.
echo "Code (site/)..."
rsync -rltzO --itemize-changes "${DRY_RUN[@]}" \
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
    Dockerfile php-hardening.ini apache-site.conf apache-mpm.conf mysql-lowmem.cnf Caddyfile proto-auth.py \
    "$PROD_SSH:$PROD_DIR/"
  rsync -az --itemize-changes "${DRY_RUN[@]}" docker-compose.prod.yml "$PROD_SSH:$PROD_DIR/docker-compose.yml"
  rsync -az --chmod=F755 --itemize-changes "${DRY_RUN[@]}" cron-run.sh backup-db.sh restore-db.sh "$PROD_SSH:$PROD_DIR/"
  rsync -az --itemize-changes "${DRY_RUN[@]}" crontab "$PROD_SSH:$PROD_DIR/"
fi

if [[ ${#DRY_RUN[@]} -eq 0 ]]; then
  if [[ $INFRA == 1 ]]; then
    remote_compose up -d --build
    # Le Caddyfile est un fichier monté : rsync le remplace, Caddy ne voit la nouvelle version qu'au redémarrage
    remote_compose restart caddy
    # Remplace la crontab de l'usager amimoucheur
    ssh "$PROD_SSH" crontab "$PROD_DIR/crontab"
  fi
  # Vide les caches Smarty et l'index des classes (overrides) ; fichiers créés par www-data
  remote_compose exec -T apache sh -c "'rm -rf /var/www/html/cache/smarty/compile/* /var/www/html/cache/smarty/cache/* /var/www/html/cache/class_index.php'"
  echo "Déploiement terminé."
fi
