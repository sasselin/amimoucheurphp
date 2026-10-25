# Migration vers le nouveau site : ce que ce dépôt fournit

Ce dépôt (`amimoucheur`, PrestaShop 1.6.1.4) **prépare** les données. Le nouveau site (`/home/sass/amimoucheur-rust`) les **importe** : les tables, l'import et l'affichage sont de son côté. Ici, on ne modifie jamais le projet Rust ; ce qu'il doit faire est écrit sous forme de recommandations, dans les README cités plus bas.

Ce fichier est le point d'entrée pour le projet Rust : quoi lire, où, dans quel ordre, et comment les fichiers se relient.

## Ce que le nouveau site lit

Chemins absolus, à lire en place. Les dossiers `out/` ne sont pas versionnés et sont réécrits à chaque extraction : ne jamais les modifier à la main.

| Fichier | Contenu | Contrat | Produit par | Décrit dans |
|---|---|---|---|---|
| `/home/sass/amimoucheur/export-catalogue/out/prod/catalogue.json` | Les **faits** du catalogue : produits, variantes, prix, stock, images, descriptions, SEO, `bought_together`, `pages` | `meta.generated_at` | `export-catalogue/run.sh --prod` | [export-catalogue/README.md](export-catalogue/README.md) |
| `…/export-catalogue/out/prod/recategorisation/recategorisation.json` | Les **décisions** : arbre, marques, facettes, familles, tags, badges, fusions, et par produit noms, slugs, catégories, variantes | `schema_version` 2 | idem (`recategorise.py`, `write_json`) | [export-catalogue/README.md](export-catalogue/README.md#recategorisationjson-contrat-de-limport-du-nouveau-site) |
| `…/export-catalogue/out/prod_tout/recategorisation/caddy/redirections.caddy` | Les **redirections 301** de toutes les anciennes URL (produits, catégories, marques, pages de compte), à importer dans le Caddy du nouveau site | testées dans `caddy:2.8.4` | `export-catalogue/run.sh --prod --tout` | `caddy/LISEZMOI.md` à côté du fichier |
| `/home/sass/amimoucheur/export-clients/out/prod/clients.json` | **Comptes** (mot de passe, carnet d'adresses, infolettre, panier en cours), **toutes les commandes**, abonnés sans compte | `schema_version` 1 | `export-clients/run.sh --prod` | [export-clients/README.md](export-clients/README.md#clientsjson-contrat-de-limport-du-nouveau-site) |
| `…/export-clients/out/prod/rapport.md` | Rapport pour le client (aucun nom ni courriel) et totaux de rapprochement | — | idem | — |
| `…/export-clients/out/prod/service_clientele.csv` | Archive des fils du formulaire de contact : **pas à importer**, à garder | — | idem | — |
| `export-catalogue/taxonomie.json` → `urls` | Le schéma des nouvelles URL, dont `pages_compte` (chemins des pages de compte fr/en) | versionné | à la main | [export-catalogue/CLAUDE.md](export-catalogue/CLAUDE.md) |

Pour les essais, les mêmes fichiers existent pour la base locale : `out/local/` (et `out/local_tout/`). Le catalogue s'importe déjà : `cargo run -- import-catalogue /home/sass/amimoucheur/export-catalogue/out/prod --images-root /home/sass/amimoucheur/site`. Les clients s'importent ensuite : `cargo run -- import-clients /home/sass/amimoucheur/export-clients/out/prod/clients.json --catalogue /home/sass/amimoucheur/export-catalogue/out/prod` (branche `import-clients` de amimoucheur-rust, `admin/docs/clients-import.md`).

**Secret à part.** La vérification des anciens mots de passe a besoin de `_COOKIE_KEY_` de la prod (`site/config/settings.inc.php` du serveur, non versionné). Il se remet comme secret de l'application. Il ne doit jamais être écrit dans un dépôt ni dans un JSON.

## Comment les fichiers se relient

Tous les liens passent par les **id PrestaShop**, la clé d'idempotence des imports.

| Dans | Champ | Pointe vers |
|---|---|---|
| `recategorisation.json` | `products[].id` | `catalogue.json → products[].id` ; côté Rust, `products.prestashop_id` |
| `recategorisation.json` | `products[].variant_ids[]` | `catalogue.json → variants[].id` ; côté Rust, `variants.prestashop_id`. Une liste vide = produit simple (variante par défaut sans `prestashop_id`) |
| `recategorisation.json` | `meta.catalogue_generated_at` | `catalogue.json → meta.generated_at` (l'import refuse un écart) |
| `clients.json` | `meta.catalogue_generated_at` | la même extraction du catalogue : celle qui a été importée |
| `clients.json` | `orders[].lines[]` et `accounts[].cart.lines[]` : `product_id` | `products.prestashop_id` (la fiche après fusion) ; `null` dans une commande = plus vendu |
| `clients.json` | `…lines[].prestashop_variant_id` | `variants.prestashop_id` ; `null` = produit simple : la variante par défaut de `product_id` |
| `clients.json` | `orders[].account` | `accounts[].email` (minuscules), ou `null` : commande d'invité, à rattacher après confirmation du courriel |
| `taxonomie.json` | `urls.pages_compte` | les routes du site Rust : si elles changent, ajuster ici puis relancer `export-catalogue/run.sh` (redirections retestées) |

**Ordre d'import** : le catalogue d'abord (`out/prod`), ensuite `clients.json`, qui y fait référence.

## Ce que le nouveau site doit faire

Le détail et les raisons sont dans les README. En bref :

- **Mots de passe** ([export-clients/README.md](export-clients/README.md#recommandations-pour-le-nouveau-site)) : schéma `prestashop_md5+argon2id`. À la connexion, faire `md5(_COOKIE_KEY_ . trim(saisie))`, le vérifier contre le hash argon2, puis réécrire un argon2id normal. Une réimportation ne doit pas écraser un hash déjà réécrit.
- **Commandes** : une seule table pour les commandes migrées et les nouvelles. `status_inferred` signale un statut déduit à la migration (vieille commande restée ouverte). Montants en cents, figés tels que vendus. `taxes[]` est toujours égal à `tax`. Les adresses sont copiées dans la commande.
- **Commandes d'invités** : rattachées au compte de même courriel, seulement une fois ce courriel confirmé.
- **Paniers** : à l'import, créer le panier seulement si le compte n'en a pas déjà un. À la connexion, si le panier de la session est vide, reprendre celui du compte, comme PrestaShop. Le prix et le stock sont ceux du nouveau site.
- **Pages de compte** : servir les chemins de `urls.pages_compte`, ou les y corriger.
- **Redirections** : importer `redirections.caddy` dans le bloc du domaine, avant le `reverse_proxy`.
- **Archive comptable** : garder 6 ans un dump PrestaShop chiffré, sans `ps_ccofflinepayment`.

## Changer un contrat

- Un changement **incompatible** (champ renommé, retiré ou de sens différent) incrémente `meta.schema_version` et se décrit dans le README du dossier, puis ici.
- Un **ajout** de champ garde la version.
- Côté Rust, l'import vérifie la version attendue (`admin/src/import/source.rs` pour `recategorisation.json`).

| Contrat | Version | Changement |
|---|---|---|
| `recategorisation.json` | 1 | Première version |
| `recategorisation.json` | 2 | `families[]` ajouté |
| `clients.json` | 1 | Première version : comptes, commandes, abonnés, paniers (`accounts[].cart`), `orders[].status_inferred` |
| `catalogue.json` | — | Ajout de `pages` (slugs des pages PrestaShop, pour les redirections de compte) |

## Jour J (ordre complet)

1. **La veille** : traiter dans PrestaShop les commandes récentes encore ouvertes que liste `export-clients/out/prod/rapport.md`, avec « Envoyé (sans notification) » pour ne pas envoyer de courriel. Les commandes ouvertes depuis plus de 6 mois sont fermées par la migration (`status_inferred`).
2. **Maintenance** : mettre PrestaShop en maintenance, pour qu'aucune commande ni aucun compte n'arrive après l'extraction.
3. **Extractions**, dans cet ordre :
   ```bash
   ./export-catalogue/run.sh --prod          # out/prod : catalogue importé, et lu par export-clients
   ./export-catalogue/run.sh --prod --tout   # out/prod_tout : redirections de toutes les anciennes URL
   ./export-clients/run.sh --prod            # out/prod : comptes, commandes, paniers (relie les lignes à out/prod)
   ```
   `--tout` écrit dans `out/prod_tout/` et non dans `out/prod/`. Les deux extractions du catalogue sont donc nécessaires.
4. **Imports, côté Rust** : `import-catalogue …/export-catalogue/out/prod`, puis `import-clients …/export-clients/out/prod/clients.json --catalogue …/export-catalogue/out/prod` (rapprochement refait à l'import) ; `_COOKIE_KEY_` est donné au site public comme secret.
5. **Bascule** : redirections et domaines, selon `out/prod_tout/recategorisation/caddy/LISEZMOI.md` et [deploy/README.md](deploy/README.md#jour-j-nouveau-site).
6. **Après** : supprimer `export-clients/out/` (données personnelles). Vider ensuite `ps_ccofflinepayment` et les anciennes sauvegardes qui la contiennent.

## Données sensibles

- `export-clients/out/` contient des données personnelles. Il n'est pas versionné, les dossiers sont en 700 et les fichiers en 600. On le supprime après l'import, et on ne copie jamais ses fichiers dans le dépôt Rust.
- **`ps_ccofflinepayment` n'est jamais lu.** Cette table contient des numéros de carte complets et des CVC, une non-conformité PCI DSS à régler avec le client. Elle ne doit jamais arriver en local : utiliser `./pull-prod-db.sh`.

## Décisions encore ouvertes

| Décision | Qui | Où ça se règle |
|---|---|---|
| Ancienneté des paniers redonnés (défaut un an), qui décide aussi des comptes sans commande gardés | client | `export-clients/run.sh --paniers-depuis=` |
| Délai de fermeture automatique des commandes ouvertes (défaut 6 mois) et traitement des 25 récentes | client | `export-clients/statuts.json` → `fermeture_auto` ; PrestaShop |
| Chemins des pages de compte | projet Rust | `export-catalogue/taxonomie.json` → `urls.pages_compte` |
| Liste d'infolettre qui fait foi (PrestaShop ou Mailchimp) | client | comparer `newsletter` et `newsletter_subscribers[]` à Mailchimp |
| Numérotation des nouvelles factures (suite de `#IN…` ou nouveau préfixe) | client et projet Rust | dernier n° dans `rapport.md` |
| Arrêt du module de paiement par carte hors ligne et purge des cartes | client | avant le jour J, indépendamment de la migration |
