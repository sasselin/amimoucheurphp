#!/usr/bin/env bash
# Lance un cron PrestaShop et envoie un courriel en cas d'échec.
# Usage : cron-run.sh <nom> <chemin-url> [fichier-attendu]
#   ex. : cron-run.sh sitemap "/modules/gsitemap/gsitemap-cron.php?token=..." "1_index_sitemap.xml"
# [fichier-attendu] : motif de fichier à la racine du site qui doit être régénéré
# (ces modules répondent 200 même quand ils n'arrivent pas à écrire).
# Appelé par la crontab de l'usager amimoucheur (voir deploy/crontab).
set -uo pipefail

NAME="$1"
URL_PATH="$2"
EXPECTED="${3:-}"
DIR=/srv/amimoucheur
ALERT_TO=sebastien.asselin@gmail.com
LOG="$DIR/cron.log"

# Docker rootless : le socket est celui de l'usager amimoucheur
export DOCKER_HOST="${DOCKER_HOST:-unix:///run/user/$(id -u)/docker.sock}"
cd "$DIR"

BODY="$(mktemp)"
ERR="$(mktemp)"
START="$(mktemp)"   # repère de date pour [fichier-attendu]
trap 'rm -f "$BODY" "$ERR" "$START"' EXIT

# Appel direct à Caddy en local : contourne Cloudflare (challenge sur /admin0181)
# en gardant le vrai Host et le vrai certificat.
HTTP_CODE="$(curl -sS -L --max-time 600 -o "$BODY" -w '%{http_code}' \
  --resolve www.amimoucheur.com:443:127.0.0.1 \
  "https://www.amimoucheur.com$URL_PATH" 2>"$ERR")"
CURL_RC=$?

FAIL=""
if [[ $CURL_RC -ne 0 ]]; then
  FAIL="curl a échoué (code $CURL_RC)"
elif [[ ! $HTTP_CODE =~ ^2 ]]; then
  FAIL="HTTP $HTTP_CODE"
elif grep -qE 'Bad token|Fatal error|Parse error|Exception' "$BODY"; then
  FAIL="erreur dans la réponse (HTTP $HTTP_CODE)"
elif [[ -n $EXPECTED && -z $(find "$DIR/site" -maxdepth 1 -name "$EXPECTED" -newer "$START" -print -quit) ]]; then
  FAIL="aucun fichier $EXPECTED régénéré"
fi

[[ -z $FAIL ]] && exit 0

echo "$(date '+%F %T') $NAME : $FAIL" >>"$LOG"

# Envoi via le msmtp de l'image apache (relais Brevo) ; « run » fonctionne même si le conteneur est arrêté.
SUBJECT="=?UTF-8?B?$(printf '[amimoucheur cron] échec %s' "$NAME" | base64 -w0)?="
{
  printf 'To: %s\nFrom: info@amimoucheur.com\nSubject: %s\nContent-Type: text/plain; charset=UTF-8\n\n' "$ALERT_TO" "$SUBJECT"
  printf 'Cron : %s\nURL : %s\nDate : %s\nProblème : %s\n\n' "$NAME" "$URL_PATH" "$(date '+%F %T')" "$FAIL"
  printf -- '--- Erreur curl ---\n%s\n\n--- Réponse (50 premières lignes) ---\n' "$(cat "$ERR")"
  head -n 50 "$BODY"
} | docker compose run --rm --no-deps -T --entrypoint msmtp apache -t \
  || echo "$(date '+%F %T') $NAME : envoi du courriel d'alerte impossible" >>"$LOG"

exit 1
