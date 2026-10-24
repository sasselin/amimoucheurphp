#!/usr/bin/env bash
# Sauvegarde nocturne de la BD dans un dépôt restic local, puis copie vers Google Drive.
# restic déduplique : chaque snapshot est un dump complet, mais seuls les blocs
# modifiés depuis la veille sont stockés. Le dépôt est chiffré (mot de passe dans
# $DIR/restic-password, à garder aussi hors du serveur).
# Appelé par la crontab de l'usager amimoucheur (voir deploy/crontab).
# Restauration : voir restore-db.sh.
set -uo pipefail

DIR=/srv/amimoucheur
ALERT_TO=sebastien.asselin@gmail.com
LOG="$DIR/cron.log"
MIN_FREE_MB=3072      # espace libre minimal sur le disque avant de sauvegarder
MAX_REPO_MB=5120      # taille maximale du dépôt local
RCLONE_DEST=gdrive:amimoucheur-backup/restic   # copie hors-site (dossier de Mon Drive créé par rclone) ; ignorée tant que le remote « gdrive » n'existe pas

export RESTIC_REPOSITORY="$DIR/backups/restic"
export RESTIC_PASSWORD_FILE="$DIR/restic-password"
# Docker rootless : le socket est celui de l'usager amimoucheur
export DOCKER_HOST="${DOCKER_HOST:-unix:///run/user/$(id -u)/docker.sock}"
cd "$DIR"

OUT="$(mktemp)"
trap 'rm -f "$OUT"' EXIT

fail() {
  echo "$(date '+%F %T') sauvegarde-bd : $1" >>"$LOG"
  # Envoi via le msmtp de l'image apache (relais Brevo), comme cron-run.sh
  local subject="=?UTF-8?B?$(printf '[amimoucheur] échec sauvegarde BD' | base64 -w0)?="
  {
    printf 'To: %s\nFrom: info@amimoucheur.com\nSubject: %s\nContent-Type: text/plain; charset=UTF-8\n\n' "$ALERT_TO" "$subject"
    printf 'Date : %s\nProblème : %s\n\n--- Sortie (50 dernières lignes) ---\n' "$(date '+%F %T')" "$1"
    tail -n 50 "$OUT"
  } | docker compose run --rm --no-deps -T --entrypoint msmtp apache -t \
    || echo "$(date '+%F %T') sauvegarde-bd : envoi du courriel d'alerte impossible" >>"$LOG"
  exit 1
}

# Garde d'espace : ne jamais remplir le disque du serveur
FREE_MB=$(df -Pm "$DIR" | awk 'NR==2 {print $4}')
REPO_MB=$(du -sm "$RESTIC_REPOSITORY" | cut -f1)
(( FREE_MB < MIN_FREE_MB )) && fail "espace disque insuffisant (${FREE_MB} Mo libres, minimum ${MIN_FREE_MB})"
(( REPO_MB > MAX_REPO_MB )) && fail "dépôt trop gros (${REPO_MB} Mo, maximum ${MAX_REPO_MB})"

# Dump non compressé (sinon pas de déduplication) ; --skip-dump-date pour une sortie
# stable. restic 0.16 (Ubuntu 24.04) n'a pas --stdin-from-command : si mysqldump
# échoue, le snapshot (dump tronqué) est supprimé.
# ps_ccofflinepayment (numéros de cartes du module creditcardofflinepayment) : structure
# seulement, jamais les données.
docker compose exec -T mysql sh -c \
  'mysqldump -uroot -p"$MYSQL_ROOT_PASSWORD" --single-transaction --quick --routines --triggers --events --skip-dump-date --ignore-table=amimoucheur.ps_ccofflinepayment amimoucheur \
   && exec mysqldump -uroot -p"$MYSQL_ROOT_PASSWORD" --no-data --skip-dump-date amimoucheur ps_ccofflinepayment' \
  2>"$OUT" | restic backup --json --tag db --stdin --stdin-filename amimoucheur.sql >>"$OUT" 2>&1
RC=("${PIPESTATUS[@]}")
if (( RC[0] != 0 )); then
  SNAP=$(grep -o '"snapshot_id":"[0-9a-f]*"' "$OUT" | cut -d'"' -f4)
  [[ -n $SNAP ]] && restic forget --quiet "$SNAP" >>"$OUT" 2>&1
  fail "mysqldump (code ${RC[0]})"
fi
(( RC[1] != 0 )) && fail "restic backup (code ${RC[1]})"

# Rotation : 14 jours, 8 semaines, 12 mois. Le prune (gourmand) et la vérification seulement le dimanche.
SUNDAY=0
[[ $(date +%u) == 7 ]] && SUNDAY=1
PRUNE=()
(( SUNDAY )) && PRUNE=(--prune)
restic forget --quiet --tag db --keep-daily 14 --keep-weekly 8 --keep-monthly 12 "${PRUNE[@]}" \
  >"$OUT" 2>&1 || fail "restic forget"
if (( SUNDAY )); then
  restic check >"$OUT" 2>&1 || fail "restic check : dépôt corrompu"
fi

# Copie hors-site : les fichiers du dépôt sont chiffrés et immuables
if rclone listremotes 2>/dev/null | grep -qx "${RCLONE_DEST%%:*}:"; then
  rclone sync --quiet "$RESTIC_REPOSITORY" "$RCLONE_DEST" >"$OUT" 2>&1 || fail "rclone sync vers $RCLONE_DEST"
else
  echo "$(date '+%F %T') sauvegarde-bd : copie hors-site non configurée (remote ${RCLONE_DEST%%:*} absent)" >>"$LOG"
fi
