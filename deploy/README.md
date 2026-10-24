# Infrastructure de production

Hôte : droplet DigitalOcean `143.198.43.249` (Ubuntu 24.04, 1 vCPU, 1 Go de RAM, région tor1). DNS et pare-feu applicatif chez Cloudflare (proxy actif).

## Architecture

```
Visiteur → Cloudflare → UFW (80/443 depuis les IP Cloudflare seulement)
         → Caddy d'entrée (root, TLS Let's Encrypt)
         → caddy (Docker, 127.0.0.1:8081) → apache (PHP 5.6, mod_php) → mysql 5.6
```

Tout est dans `/srv/amimoucheur`, sous l'usager `amimoucheur` (sans sudo, Docker rootless). Les comptes `sass` et `deploy` servent à l'administration ; la connexion SSH en root est désactivée.

| Fichier | Rôle |
|---|---|
| `docker-compose.prod.yml` | Copié en `/srv/amimoucheur/docker-compose.yml`. Services `mysql` (400 Mo max), `apache` (700 Mo max), `caddy`. |
| `Dockerfile` | Image `amimoucheur-php56` : Apache et PHP 5.6 de `ppa:ondrej/php`, qui publie le correctif php2shell. L'image officielle `prestashop/base:5.6-apache` n'est plus corrigée. |
| `php-hardening.ini` | Atténuations php2shell, `disable_functions`, envoi des courriels par msmtp. |
| `apache-site.conf` | Vraie IP client (`CF-Connecting-IP`), HTTPS vu par PrestaShop, blocage des POST multipart hors `/admin0181/`, pas de PHP dans les dossiers inscriptibles. |
| `apache-mpm.conf`, `mysql-lowmem.cnf` | Réglages pour 1 Go de RAM. |
| `Caddyfile` | En http derrière le Caddy d'entrée, `amimoucheur.com` → `www`, proto. |
| `entree.caddy`, `entree-root.sh` | Domaines de ce site dans le Caddy d'entrée du serveur, et sa mise en place (voir « Caddy d'entrée »). |
| `crontab`, `cron-run.sh` | Crons PrestaShop (taux de change, sitemap, flux Google Shopping) avec courriel d'alerte en cas d'échec. |
| `backup-db.sh`, `restore-db.sh` | Sauvegarde nocturne de la BD et restauration. |
| `ufw-cloudflare.sh` | Pare-feu : SSH ouvert, 80/443 depuis Cloudflare seulement. À lancer en sudo, et à relancer si Cloudflare change ses plages. |
| `.env.example`, `msmtprc.example` | Modèles des fichiers secrets (voir plus bas). |

## Déploiement

Depuis la racine du dépôt :

```bash
./deploy.sh --dry-run --infra   # vérifier
./deploy.sh --infra             # envoie deploy/, reconstruit les conteneurs, installe la crontab
```

Fichiers présents seulement sur le serveur, jamais dans le dépôt :

| Fichier dans `/srv/amimoucheur` | Contenu |
|---|---|
| `.env` | `MYSQL_ROOT_PASSWORD` (modèle : `.env.example`). |
| `msmtprc` | Accès au relais SMTP Brevo (modèle : `msmtprc.example`). |
| `restic-password` | Mot de passe du dépôt de sauvegarde. **À garder aussi hors du serveur**, sans lui les sauvegardes sont illisibles. |
| `site/config/settings.inc.php` | Accès BD et clés de PrestaShop. |
| `~amimoucheur/.config/rclone/rclone.conf` | Accès Google Drive (portée `drive.file`). |

## proto.amimoucheur.com (page de revue pour le client)

La page de recatégorisation (`index.html`) et le catalogue (`catalogue.html`), servis par Caddy depuis `/srv/amimoucheur/proto`. Publication : `./deploy-proto.sh` à la racine du dépôt (extraction de la base de prod puis envoi ; `--sans-export`, `--dry-run`).

Accès par code envoyé par courriel (`proto-auth.py`, conteneur `proto-auth`, Python sans dépendance) : le visiteur tape son courriel, reçoit un code à 6 chiffres par Brevo (mêmes accès que `msmtprc`), et obtient une session de 30 jours. Caddy vérifie la session avant chaque fichier (`forward_auth`).

- Gérer les accès : `./proto-acces.sh` (liste), `./proto-acces.sh ajouter a@b.com @client.com`, `./proto-acces.sh retirer a@b.com`.
- Liste des accès : `/srv/amimoucheur/proto-auth/courriels.txt`, une adresse par ligne ou `@domaine.com`. Relue à chaque requête : pas de redémarrage, et retirer une adresse coupe son accès tout de suite.
- `/srv/amimoucheur/proto-auth/secret` signe les sessions ; le supprimer puis `docker compose restart proto-auth` déconnecte tout le monde.
- Déconnexion : `https://proto.amimoucheur.com/deconnexion`.
- DNS Cloudflare : `A proto → 143.198.43.249`, proxied.

## Caddy d'entrée (un serveur, plusieurs sites)

Le serveur héberge plusieurs sites, chacun sous son propre usager, sans accès aux fichiers des autres et sans
dépendre d'eux. Seul le Caddy d'entrée, géré par root (paquet `caddy`), écoute sur 80/443 : il obtient les
certificats et renvoie chaque domaine au Caddy de son site, sur 127.0.0.1.

- `/etc/caddy/Caddyfile` n'a que `import /etc/caddy/sites/*.caddy`.
- Chaque site fournit, dans son dépôt, le fichier de ses domaines, copié par root en
  `/etc/caddy/sites/<usager>.caddy`, puis `systemctl reload caddy`. Ici : `deploy/entree.caddy` (`www`,
  `amimoucheur.com`, `proto` → `127.0.0.1:8081`).
- Le conteneur `caddy` de ce site est publié sur `127.0.0.1:8081` en http et reprend les en-têtes
  `X-Forwarded-*` du Caddy d'entrée (`trusted_proxies`) : Apache voit toujours `HTTPS=on`, et la vraie IP par
  `CF-Connecting-IP`.
- `cron-run.sh` interroge toujours `https://www…` sur 127.0.0.1:443, c'est-à-dire le Caddy d'entrée.
- Un nouveau site : un usager, des ports libres (tableau ci-dessous), un fichier dans `/etc/caddy/sites/`.

| Usager | Domaines | Ports (127.0.0.1) |
|---|---|---|
| `amimoucheur` (ce dépôt, Docker rootless) | www, amimoucheur.com, proto | 8081 |
| `amimoucheur-rust` (nouveau site) | admin, dev | 8090, 8091, 3000, 3001, 3010, 3011, 7700 |

Mise en place, une fois, en root et à une heure creuse : `deploy/entree-root.sh` (mode d'emploi en tête du
script). Il installe le paquet `caddy`, le `Caddyfile` d'entrée et `entree.caddy`, copie les certificats du
conteneur, pose le nouveau `Caddyfile` et `docker-compose.yml` de ce site, recrée le conteneur `caddy` sur
127.0.0.1:8081 et démarre le Caddy d'entrée : une à deux secondes de coupure.

**Ne pas lancer `./deploy.sh --infra` avant cette mise en place** : il appliquerait le nouveau
`docker-compose.yml` et retirerait 80/443 au site sans Caddy d'entrée pour les reprendre.

Ensuite, `./deploy.sh --infra` reste le même.

## Courriels

PrestaShop appelle `mail()`, qui passe par msmtp dans le conteneur, puis par Brevo (`smtp-relay.brevo.com:2525`, STARTTLS). DigitalOcean bloque les ports 25, 465 et 587, et le SwiftMailer de PrestaShop 1.6 ne gère pas STARTTLS. Les courriels entrants passent par Google Workspace.

## Crons et alertes

La crontab de l'usager `amimoucheur` (heure de Toronto) appelle `cron-run.sh`, qui interroge Caddy en local (`--resolve 127.0.0.1`, pour éviter le challenge Cloudflare sur l'admin). Un cron est en échec si le code HTTP n'est pas 2xx, si la réponse contient une erreur PHP, ou si le fichier attendu (sitemap, flux XML) n'a pas été régénéré. L'échec est noté dans `/srv/amimoucheur/cron.log` et envoyé par courriel.

## Sauvegardes de la BD

Chaque nuit à 3 h 30, `backup-db.sh` envoie un `mysqldump` complet dans un dépôt restic chiffré et dédupliqué (`/srv/amimoucheur/backups/restic`), puis le copie sur Google Drive (`gdrive:amimoucheur-backup/restic`) avec rclone.

- La table `ps_ccofflinepayment` (cartes du module `creditcardofflinepayment`) est exclue : seule sa structure est sauvegardée, jamais son contenu.
- Rétention : 14 jours, 8 semaines, 12 mois. Le nettoyage (`prune`) et la vérification (`restic check`) se font le dimanche.
- Garde-fous : pas de sauvegarde s'il reste moins de 3 Go libres ou si le dépôt dépasse 5 Go.
- Tout échec envoie un courriel.

### Restaurer

Sur le serveur, en tant qu'`amimoucheur` :

```bash
./restore-db.sh                                   # liste les sauvegardes
./restore-db.sh latest --to-file /tmp/bd.sql      # extrait un dump
./restore-db.sh <snapshot> --into-db              # remplace la BD de prod (demande « oui », sauvegarde l'état actuel avant)
```

Si le serveur est perdu, depuis une autre machine avec rclone et restic :

```bash
rclone copy gdrive:amimoucheur-backup/restic ./restic
RESTIC_REPOSITORY=./restic restic dump latest amimoucheur.sql > amimoucheur.sql
```

## Sécurité (php2shell, CVE-2026-109618 / CVE-2026-109619)

Plusieurs couches de protection :

1. PHP 5.6.40-105 corrigé (`ppa:ondrej/php`).
2. `session.upload_progress.enabled = 0`.
3. POST multipart bloqués hors `/admin0181/`, dans Apache et par une règle Cloudflare.
4. Admin bloqué hors Canada et protégé par un challenge Cloudflare.
5. Port 80/443 fermé à tout sauf Cloudflare (UFW).

## Permissions

Le `www-data` du conteneur correspond sur l'hôte à l'usager `ct-www-data` (UID/GID 165568, Docker rootless). Le code appartient à `amimoucheur:ct-www-data` ; ce que PrestaShop génère (cache, img, upload, log, sitemaps, flux XML...) appartient à `ct-www-data`. Les dossiers sont en setgid. C'est pourquoi `deploy.sh` utilise `rsync -rlt`, sans `-p` ni `-g`.

## Jour J (nouveau site)

Les redirections 301 de l'ancien site sont produites par `export-catalogue/`. Au jour J, `www.amimoucheur.com` et `amimoucheur.com` passent de `deploy/entree.caddy` au fichier de domaines du nouveau site, et les redirections s'importent dans le Caddy du nouveau site, avant son `reverse_proxy`. La procédure est dans [export-catalogue/CLAUDE.md](../export-catalogue/CLAUDE.md). Le `Caddyfile` actuel n'est pas encore modifié.
