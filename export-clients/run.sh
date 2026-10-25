#!/usr/bin/env bash
# Extraction des comptes clients et des commandes -> export-clients/out/<source>/ (clients.json, rapport.md, service_clientele.csv).
# Le dossier est vidé et réécrit à chaque extraction : même chemin, il suffit de rafraîchir.
# Usage : ./export-clients/run.sh                          base locale (conteneur apache du docker-compose.yml)
#         ./export-clients/run.sh --prod                   base de prod via SSH (rien n'est écrit sur le serveur)
#         ./export-clients/run.sh --paniers-depuis=2025-01-01   redonne les paniers modifiés depuis cette date (défaut : un an)
# Exige l'extraction du catalogue de la même source (export-catalogue/run.sh [--prod]) : les lignes de commande
# sont reliées au nouveau catalogue pour le bouton « Recommander ».
# Jour J : ./export-catalogue/run.sh --prod && ./export-clients/run.sh --prod
#          (--prod --tout, pour les redirections, écrit dans out/prod_tout : il ne remplace pas le run sans --tout)
set -euo pipefail

DIR="$(cd "$(dirname "$0")" && pwd)"
ROOT="$(dirname "$DIR")"
PROD_SSH="${PROD_SSH:-amimoucheur@143.198.43.249}"
PROD_DIR="${PROD_DIR:-/srv/amimoucheur}"
BASE_URL="${BASE_URL:-https://www.amimoucheur.com}"

SOURCE=local
BUILD_ARGS=()
while [[ $# -gt 0 ]]; do
  case "$1" in
    --prod) SOURCE=prod ;;
    --paniers-depuis=*) BUILD_ARGS+=("$1") ;;
    *) echo "Option inconnue : $1" >&2; exit 2 ;;
  esac
  shift
done

# Le catalogue actif (et non _tout) : seuls les produits encore vendus peuvent être recommandés
REC="$ROOT/export-catalogue/out/$SOURCE/recategorisation/recategorisation.json"
if [[ ! -f $REC ]]; then
  echo "Introuvable : $REC. Lancer d'abord ./export-catalogue/run.sh${SOURCE/local/}${SOURCE/prod/ --prod}" >&2
  exit 1
fi

OUT="$DIR/out/$SOURCE"
mkdir -p "$OUT"
chmod 700 "$DIR/out" "$OUT"
find "$OUT" -mindepth 1 -delete

# export.php passe par l'entrée standard (local et prod) et sa sortie va directement à build.py : l'extraction brute,
# qui contient les empreintes md5 des mots de passe, n'est jamais écrite sur disque.
# Depuis stdin, dirname(__FILE__) vaut « . » : on l'exécute depuis classes/ pour que « ./../config/config.inc.php »
# trouve la configuration PrestaShop. HTTP_HOST doit être le domaine de la boutique, sinon PrestaShop redirige en silence.
echo "Extraction ($SOURCE)..."
if [[ $SOURCE == local ]]; then
  docker compose -f "$ROOT/docker-compose.yml" exec -T -w /var/www/html/classes apache php -- --source=local \
    < "$DIR/export.php" \
    | uv run --quiet "$DIR/build.py" - "$OUT" "$REC" "${BUILD_ARGS[@]}"
else
  # Docker rootless : le socket est celui de l'usager amimoucheur
  HOST="${BASE_URL#*://}"; HOST="${HOST%%/*}"
  ssh "$PROD_SSH" "cd '$PROD_DIR' && export DOCKER_HOST=\${DOCKER_HOST:-unix:///run/user/\$(id -u)/docker.sock} \
    && docker compose exec -T -e HTTP_HOST=$HOST -w /var/www/html/classes apache php -- --source=prod" \
    < "$DIR/export.php" \
    | uv run --quiet "$DIR/build.py" - "$OUT" "$REC" "${BUILD_ARGS[@]}"
fi

echo "OK : $OUT"
echo "Données personnelles : supprimer $OUT une fois l'import du nouveau site fait." >&2
