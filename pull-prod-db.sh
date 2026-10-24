#!/usr/bin/env bash
# Remplace la BD locale par une sauvegarde de prod (dépôt restic, voir deploy/README.md).
# Usage : ./pull-prod-db.sh             (dernier snapshot)
#         ./pull-prod-db.sh <snapshot>  (un snapshot précis)
#         ./pull-prod-db.sh --list      (liste les snapshots)
# Les données de ps_ccofflinepayment (cartes du module creditcardofflinepayment) ne
# viennent jamais en local : elles sont retirées du dump sur le serveur, avant le
# transfert, et la table locale est vidée après l'import par sécurité. Les snapshots
# antérieurs au 2026-10-24 les contiennent encore.
set -euo pipefail

PROD_SSH="${PROD_SSH:-amimoucheur@143.198.43.249}"
PROD_DIR="${PROD_DIR:-/srv/amimoucheur}"
ROOT="$(cd "$(dirname "$0")" && pwd)"

if [[ ${1:-} == --list ]]; then
  ssh "$PROD_SSH" "$PROD_DIR/restore-db.sh"
  exit 0
fi
SNAP="${1:-latest}"
[[ $SNAP =~ ^[0-9a-f]+$|^latest$ ]] || { sed -n '3,5p' "$0" >&2; exit 2; }

local_mysql() {
  docker compose -f "$ROOT/docker-compose.yml" exec -T mysql sh -c 'exec mysql -uroot -p"$MYSQL_ROOT_PASSWORD" amimoucheur "$@"' sh "$@"
}

echo "Import du snapshot $SNAP dans la BD locale..."
ssh "$PROD_SSH" "set -o pipefail && export RESTIC_REPOSITORY='$PROD_DIR/backups/restic' RESTIC_PASSWORD_FILE='$PROD_DIR/restic-password' \
  && restic dump '$SNAP' amimoucheur.sql | grep -av '^INSERT INTO \`ps_ccofflinepayment\`'" \
  | local_mysql

local_mysql -e 'TRUNCATE TABLE ps_ccofflinepayment'
COUNT=$(local_mysql -N -e 'SELECT COUNT(*) FROM ps_ccofflinepayment')
[[ $COUNT == 0 ]] || { echo "ps_ccofflinepayment contient encore $COUNT lignes" >&2; exit 1; }
echo "BD locale restaurée depuis $SNAP (ps_ccofflinepayment vide)."
