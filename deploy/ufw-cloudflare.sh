#!/usr/bin/env bash
# N'autorise 80/443 que depuis les plages IP de Cloudflare.
# Usage : ./ufw-cloudflare.sh [IP_DE_TEST]   (IP_DE_TEST : accès direct temporaire)
set -euo pipefail

ufw default deny incoming
ufw default allow outgoing
ufw allow OpenSSH

# Retire les anciennes règles cloudflare/test avant de les recréer
while ufw status numbered | grep -qE '# (cloudflare|test)$'; do
  n=$(ufw status numbered | grep -E '# (cloudflare|test)$' | head -1 | sed -E 's/^\[ *([0-9]+)\].*/\1/')
  ufw --force delete "$n"
done

for ip in $(curl -fsS https://www.cloudflare.com/ips-v4) $(curl -fsS https://www.cloudflare.com/ips-v6); do
  ufw allow proto tcp from "$ip" to any port 80,443 comment cloudflare
done

if [[ -n "${1:-}" ]]; then
  ufw allow proto tcp from "$1" to any port 80,443 comment test
fi

ufw --force enable
ufw status verbose
