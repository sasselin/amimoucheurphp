# amimoucheur.com

Boutique en ligne d'articles de pêche à la mouche, sous PrestaShop 1.6.1.4 (PHP 5.6, MySQL 5.6). Ce dépôt contient le code du site tel qu'il tourne en production, l'infrastructure du serveur et les outils de migration vers le nouveau site.

## Contenu du dépôt

| Chemin | Rôle |
|---|---|
| `site/` | Le code PrestaShop, même arborescence qu'en prod (`/srv/amimoucheur/site`). Admin : `/admin0181/`. Thème : `themes/pos_rossi*`. |
| `deploy/` | L'infrastructure de prod : image Docker PHP 5.6, Caddy, crons, sauvegardes de la BD. Voir [deploy/README.md](deploy/README.md). |
| `export-catalogue/` | L'extraction du catalogue, la recatégorisation proposée pour le nouveau site et les redirections 301 du jour J. Voir [export-catalogue/README.md](export-catalogue/README.md) et [RECATEGORISATION.md](export-catalogue/RECATEGORISATION.md). |
| `docker-compose.yml` | L'environnement local (MySQL 5.6 et `prestashop/base:5.6-apache`). |
| `deploy.sh` | Envoie `site/` (et avec `--infra`, `deploy/`) en production. |
| `sync-prod.sh` | Ramène les fichiers de prod dans `site/`. |

## Environnement local

```bash
docker compose up -d          # site sur http://localhost, MySQL sur le port 3306
./sync-prod.sh --dry-run      # voir ce qui diffère de la prod
./sync-prod.sh                # récupérer les fichiers de prod (images, modules modifiés dans l'admin...)
```

- `site/config/settings.inc.php` n'est pas versionné : il contient les accès à la BD et les clés de cookie propres à chaque environnement. Il n'est jamais écrasé par `sync-prod.sh` ni par `deploy.sh`.
- La BD locale s'alimente à partir d'une sauvegarde de prod (voir « Restaurer » dans [deploy/README.md](deploy/README.md)), toujours avec `./pull-prod-db.sh` (`--list` pour choisir un snapshot). Ne pas copier un dump de prod à la main : le script retire sur le serveur les données de `ps_ccofflinepayment` (numéros de cartes), qui ne doivent jamais arriver en local.
- Après une modification de gabarit ou d'override, vider `site/cache/smarty/` et `site/cache/class_index.php` (le `sync-prod.sh` vide déjà le cache Smarty).

## Production

DigitalOcean (droplet `143.198.43.249`, Ubuntu 24.04, 1 Go), derrière Cloudflare. Le site tourne sous l'usager `amimoucheur` en Docker rootless, jamais en root. Le détail est dans [deploy/README.md](deploy/README.md).

```bash
./deploy.sh --dry-run         # liste ce qui partirait en prod
./deploy.sh                   # envoie site/ et vide les caches
./deploy.sh --infra           # + fichiers de deploy/, reconstruction des conteneurs et crontab
```

`deploy.sh` ne supprime rien en prod (pas de `--delete`) et ne touche ni aux propriétaires ni aux permissions. La config de prod, les images, les caches, les logs et les fichiers générés (sitemaps, flux Merchant Center) sont exclus.

## Migration vers le nouveau site

`export-catalogue/run.sh` extrait le catalogue (local ou `--prod`), propose la nouvelle arborescence à partir de `export-catalogue/taxonomie.json` et produit les redirections 301 au format Caddy, testées dans Docker. Le schéma des nouvelles URL et la procédure du jour J sont dans [export-catalogue/CLAUDE.md](export-catalogue/CLAUDE.md).
