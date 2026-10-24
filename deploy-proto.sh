#!/usr/bin/env bash
# Publie la page de revue du catalogue sur https://proto.amimoucheur.com (accès filtré par Cloudflare Access).
# Usage : ./deploy-proto.sh                 extraction de la base de prod, puis envoi
#         ./deploy-proto.sh --sans-export   envoie la dernière extraction (export-catalogue/out/prod/)
#         ./deploy-proto.sh --dry-run       affiche les changements sans rien envoyer
#         Options combinables.
# index.html = page de recatégorisation, catalogue.html = catalogue complet. Les images
# pointent vers www.amimoucheur.com : seuls les HTML sont envoyés.
set -euo pipefail

PROD_SSH="${PROD_SSH:-amimoucheur@143.198.43.249}"
PROD_DIR="${PROD_DIR:-/srv/amimoucheur}"
ROOT="$(cd "$(dirname "$0")" && pwd)"
OUT="$ROOT/export-catalogue/out/prod"

DRY_RUN=()
EXPORT=1
while [[ $# -gt 0 ]]; do
  case "$1" in
    --dry-run|-n) DRY_RUN=(--dry-run) ;;
    --sans-export) EXPORT=0 ;;
    *) echo "Option inconnue : $1" >&2; exit 2 ;;
  esac
  shift
done

if [[ $EXPORT == 1 ]]; then
  "$ROOT/export-catalogue/run.sh" --prod --sans-test
fi

# Dossier de publication temporaire : on n'envoie que ce que le client doit voir
STAGE="$(mktemp -d)"
trap 'rm -rf "$STAGE"' EXIT
cp "$OUT/recategorisation/index.html" "$STAGE/index.html"
cp "$OUT/catalogue.html" "$STAGE/catalogue.html"

# Lisible par le conteneur caddy (Docker rootless : autre UID que amimoucheur)
rsync -rltz --delete --chmod=D755,F644 --itemize-changes "${DRY_RUN[@]}" \
  "$STAGE/" "$PROD_SSH:$PROD_DIR/proto/"
if [[ ${#DRY_RUN[@]} -eq 0 ]]; then echo "Publié : https://proto.amimoucheur.com/"; fi
