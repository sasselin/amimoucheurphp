#!/usr/bin/env bash
# Restaure un dump de la BD depuis le dépôt restic (voir backup-db.sh).
# Usage : restore-db.sh                         liste les snapshots
#         restore-db.sh <snapshot|latest> --to-file X.sql   extrait le dump dans un fichier
#         restore-db.sh <snapshot|latest> --into-db         remplace la BD de prod (confirmation demandée,
#                                                           snapshot de sécurité de l'état actuel avant)
# Depuis une autre machine (droplet perdu) : copier le dépôt depuis Google Drive
# (rclone copy gdrive:amimoucheur-backup/restic ./restic), puis
# RESTIC_REPOSITORY=./restic restic dump latest amimoucheur.sql > amimoucheur.sql
set -euo pipefail

DIR=/srv/amimoucheur
export RESTIC_REPOSITORY="$DIR/backups/restic"
export RESTIC_PASSWORD_FILE="$DIR/restic-password"
# Docker rootless : le socket est celui de l'usager amimoucheur
export DOCKER_HOST="${DOCKER_HOST:-unix:///run/user/$(id -u)/docker.sock}"
cd "$DIR"

if [[ $# -eq 0 ]]; then
  restic snapshots --tag db
  exit 0
fi
[[ $# -eq 2 || ( $# -eq 3 && $2 == --to-file ) ]] || { sed -n '3,6p' "$0" >&2; exit 2; }

SNAP="$1"
case "$2" in
  --to-file)
    restic dump "$SNAP" amimoucheur.sql >"$3"
    echo "Dump écrit dans $3"
    ;;
  --into-db)
    read -r -p "Remplacer la BD de prod par le snapshot $SNAP ? Taper « oui » : " ANSWER
    [[ $ANSWER == oui ]] || { echo "Annulé."; exit 1; }
    echo "Snapshot de sécurité de l'état actuel..."
    "$DIR/backup-db.sh"
    restic dump "$SNAP" amimoucheur.sql \
      | docker compose exec -T mysql sh -c 'exec mysql -uroot -p"$MYSQL_ROOT_PASSWORD" amimoucheur'
    # Vide les caches Smarty et l'index des classes ; fichiers créés par www-data
    docker compose exec -T apache sh -c 'rm -rf /var/www/html/cache/smarty/compile/* /var/www/html/cache/smarty/cache/* /var/www/html/cache/class_index.php'
    echo "BD restaurée depuis $SNAP."
    ;;
  *) sed -n '3,6p' "$0" >&2; exit 2 ;;
esac
