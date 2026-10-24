#!/usr/bin/env bash
# Extraction du catalogue -> export-catalogue/out/<source>/ (JSON, CSV, XLSX, recatégorisation proposée).
# Le dossier est vidé et réécrit à chaque extraction : même chemin, il suffit de rafraîchir.
# Usage : ./export-catalogue/run.sh             base locale (conteneur apache du docker-compose.yml)
#         ./export-catalogue/run.sh --prod      base de prod via SSH (rien n'est écrit sur le serveur)
#         ./export-catalogue/run.sh --verify    + contrôle des pages produit sur le site local
#         ./export-catalogue/run.sh --tout      garde aussi les exclus (non affichés, 0 $, abandonnés, catégories vides)
#         ./export-catalogue/run.sh --sans-test ne teste pas les redirections Caddy (testées par défaut si Docker répond)
#         Jour J : ./export-catalogue/run.sh --prod --tout (redirections de toutes les anciennes URL)
#         Options combinables. --sample N limite la vérification à N produits.
#         ABANDON_AVANT=2024-01-01 change la date limite des produits abandonnés (défaut 2023-01-01).
set -euo pipefail

DIR="$(cd "$(dirname "$0")" && pwd)"
ROOT="$(dirname "$DIR")"
PROD_SSH="${PROD_SSH:-amimoucheur@143.198.43.249}"
PROD_DIR="${PROD_DIR:-/srv/amimoucheur}"
BASE_URL="${BASE_URL:-https://www.amimoucheur.com}"

SOURCE=local
VERIFY=0
TOUT=""
TEST=1
SAMPLE=()
while [[ $# -gt 0 ]]; do
  case "$1" in
    --prod) SOURCE=prod ;;
    --verify) VERIFY=1 ;;
    --tout) TOUT=" --tout" ;;
    --sans-test) TEST=0 ;;
    --sample) SAMPLE=(--sample "$2"); shift ;;
    *) echo "Option inconnue : $1" >&2; exit 2 ;;
  esac
  shift
done

OUT="$DIR/out/$SOURCE${TOUT:+_tout}"
mkdir -p "$OUT"
# On vide le contenu sans supprimer le dossier : une fenêtre ou un onglet déjà ouverts restent valides
find "$OUT" -mindepth 1 -delete
JSON="$OUT/catalogue.json"
PHP_ARGS="--base-url=$BASE_URL --source=$SOURCE --abandon-avant=${ABANDON_AVANT:-2023-01-01}$TOUT"

echo "Extraction ($SOURCE)..."
if [[ $SOURCE == local ]]; then
  docker compose -f "$ROOT/docker-compose.yml" exec -T apache \
    php /var/www/html/export-catalogue/export.php $PHP_ARGS > "$JSON"
else
  # Docker rootless : le socket est celui de l'usager amimoucheur.
  # export.php n'est pas déployé en prod : il passe par l'entrée standard, rien n'est copié sur le serveur.
  # Lu depuis stdin, son dirname(__FILE__) vaut « . » : on l'exécute depuis un sous-dossier du site
  # (classes/) pour que « ./../config/config.inc.php » trouve la configuration PrestaShop.
  # HTTP_HOST doit être le domaine de la boutique, sinon PrestaShop redirige (et s'arrête) en silence.
  HOST="${BASE_URL#*://}"; HOST="${HOST%%/*}"
  ssh "$PROD_SSH" "cd '$PROD_DIR' && export DOCKER_HOST=\${DOCKER_HOST:-unix:///run/user/\$(id -u)/docker.sock} \
    && docker compose exec -T -e HTTP_HOST=$HOST -w /var/www/html/classes apache php -- $PHP_ARGS" \
    < "$DIR/export.php" > "$JSON"
fi

uv run --quiet "$DIR/description_images.py" "$JSON" "$OUT"
uv run --quiet "$DIR/build.py" "$JSON" "$OUT"
uv run --quiet "$DIR/recategorise.py" "$JSON" "$OUT"

if [[ $TEST == 1 ]]; then
  if docker info > /dev/null 2>&1; then
    uv run --quiet "$DIR/test_redirections.py" "$OUT"
  else
    echo "Test des redirections ignoré : Docker ne répond pas." >&2
  fi
fi

if [[ $VERIFY == 1 ]]; then
  if [[ $SOURCE == prod ]]; then
    echo "Vérification ignorée : elle compare au site local, pas à la prod." >&2
  else
    uv run --quiet "$DIR/verify.py" "$JSON" --report "$OUT/verification.csv" "${SAMPLE[@]}" \
      || echo "Écarts détectés : voir $OUT/verification.csv" >&2
  fi
fi

echo "OK : $OUT"
