#!/usr/bin/env bash
# Gère qui peut entrer sur https://proto.amimoucheur.com (code envoyé par courriel).
# Usage : ./proto-acces.sh                        affiche la liste
#         ./proto-acces.sh ajouter a@b.com @c.com une adresse, ou tout un domaine avec « @domaine »
#         ./proto-acces.sh retirer a@b.com        coupe l'accès tout de suite (même si la session est ouverte)
# La liste est /srv/amimoucheur/proto-auth/courriels.txt, relue à chaque requête : rien à redémarrer.
set -euo pipefail

PROD_SSH="${PROD_SSH:-amimoucheur@143.198.43.249}"
LISTE="${PROD_DIR:-/srv/amimoucheur}/proto-auth/courriels.txt"

action="${1:-liste}"; shift || true
case "$action" in
  liste) ;;
  ajouter|retirer)
    [[ $# -gt 0 ]] || { echo "Donne au moins une adresse." >&2; exit 2; }
    for a in "$@"; do
      a="${a,,}"
      [[ $a =~ ^[a-z0-9._%+-]*@[a-z0-9.-]+\.[a-z]+$ ]] || { echo "Adresse invalide : $a" >&2; exit 2; }
      if [[ $action == ajouter ]]; then
        ssh "$PROD_SSH" "grep -qxF '$a' '$LISTE' || echo '$a' >> '$LISTE'"
      else
        ssh "$PROD_SSH" "grep -vxF '$a' '$LISTE' > '$LISTE.tmp'; cat '$LISTE.tmp' > '$LISTE'; rm '$LISTE.tmp'"
      fi
    done ;;
  *) echo "Action inconnue : $action (liste, ajouter, retirer)" >&2; exit 2 ;;
esac
echo "Accès à https://proto.amimoucheur.com :"
ssh "$PROD_SSH" "grep -v '^#' '$LISTE' | sed '/^\s*\$/d; s/^/  /'"
