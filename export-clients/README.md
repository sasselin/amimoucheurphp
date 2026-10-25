# Extraction des comptes clients et des commandes

Prépare, pour l'import du nouveau site (dépôt `amimoucheur-rust`), les comptes clients et tout l'historique des commandes de PrestaShop. La structure est pensée pour ce dont la boutique a besoin, sans recopier les 40 tables de PrestaShop. Les clients se connectent avec le même courriel et le même mot de passe. Ils retrouvent leur panier en cours et leurs commandes, et peuvent les recommander, et la boutique retrouve une ancienne commande pour le service après-vente ou la comptabilité.

Ce dépôt ne fait que préparer les données. L'import, les tables et l'affichage sont dans `amimoucheur-rust`. Ce qu'il doit faire est décrit plus bas, sous forme de recommandations. Vue d'ensemble de la migration (tous les fichiers lus par le nouveau site, liens, jour J) : [MIGRATION.md](../MIGRATION.md).

```bash
./export-catalogue/run.sh && ./export-clients/run.sh            # base locale (docker compose up requis)
./export-clients/run.sh --comptes-depuis=2022-01-01             # garde plus de comptes sans commande
./export-clients/run.sh --paniers-depuis=2025-01-01             # redonne des paniers plus anciens (défaut : un an)
./export-catalogue/run.sh --prod && ./export-clients/run.sh --prod          # jour J (et --prod --tout pour les redirections)
uv run export-clients/test_build.py                              # tests sur des données synthétiques
```

Le catalogue de la même source doit être extrait avant : les lignes de commande et de panier sont reliées au nouveau catalogue (`export-catalogue/out/<source>/recategorisation/recategorisation.json`, catalogue actif) pour savoir lesquelles peuvent être recommandées.

## Données personnelles

- La sortie va dans `export-clients/out/<source>/` (non versionné, dossiers en 700). `clients.json` et `service_clientele.csv` sont en 600.
- Une fois l'import fait, on **supprime** ce dossier.
- `rapport.md` ne contient que des nombres, des montants et des n° de commande, jamais de nom ni de courriel : il peut être montré au client.
- L'extraction brute d'`export.php`, qui contient les empreintes md5 des mots de passe, passe directement de PHP à `build.py` par un tuyau, sans jamais être écrite sur disque.
- **`ps_ccofflinepayment` n'est jamais lu.** Cette table du module de paiement « carte hors ligne », encore actif, garde le numéro complet et le CVC de 17 000 cartes. Ils sont chiffrés avec une clé qui se trouve sur le même serveur, ce que PCI DSS interdit (le CVC ne doit jamais être conservé). Un garde-fou arrête `build.py` si une de ses colonnes apparaît dans la sortie. Les numéros de carte tapés par des clients dans des messages (clé de Luhn valide, 13 à 19 chiffres) sont masqués.

## Fichiers

| Fichier | Contenu |
|---|---|
| `clients.json` | Le contrat de l'import (ci-dessous). |
| `rapport.md` | Pour le client, en langage simple : comptes gardés et écartés, commandes ouvertes à traiter avant le jour J, totaux par année et devise, contrôles. |
| `service_clientele.csv` | Les fils du formulaire de contact (`ps_customer_thread`), archivés et non migrés. |

## Ce qui est fait

- **Un compte par courriel.** Le courriel est mis en minuscules. Les fiches en double (523 courriels, souvent un compte et des achats en invité) sont fusionnées :
  - le mot de passe, le nom et la langue viennent de la fiche non invitée modifiée le plus récemment ;
  - les commandes de toutes les fiches suivent ;
  - les id d'origine sont dans `prestashop_ids`.
- **Comptes gardés** :
  - tous les comptes qui ont au moins une commande ;
  - les comptes sans commande créés depuis `--comptes-depuis` (défaut 2023-01-01). Les autres, des comptes dormants, sont écartés : c'est la minimisation des données personnelles demandée par la Loi 25. Ils pourront se réinscrire.
  - Les fiches supprimées et les comptes désactivés ne passent pas.
- **Toutes les commandes passent**, même sans compte (invité, fiche supprimée) : la comptabilité a besoin de l'historique complet. Elles gardent leur courriel.
- **Paniers en cours** : chaque compte retrouve son dernier panier non commandé, comme PrestaShop le fait aujourd'hui à la connexion (`PS_CART_FOLLOWING` est activé, `Cart::lastNoneOrderedCart`).
  - Le panier est le plus récent des fiches de compte fusionnées, **même vide** : un client qui a vidé son panier ne retrouve pas un panier plus ancien. Les paniers des fiches invitées et des visiteurs (sans compte) ne passent pas.
  - Il est gardé s'il a été modifié depuis `--paniers-depuis` (défaut : un an avant l'extraction).
  - Les produits qui ne sont plus vendus en sont retirés. Les quantités d'une même variante (PrestaShop fait une ligne par adresse de livraison) sont additionnées.
  - Pas de prix : un panier n'a pas de montant figé, le prix est celui du nouveau site. Les bons de réduction appliqués au panier ne suivent pas.
- **Statuts** : les 16 statuts de PrestaShop sont réduits à 5, dans `statuts.json`. Un statut inconnu arrête le run.
- **Montants en cents, figés tels que vendus** :
  - le nom de la ligne est celui de la facture, variante comprise (« Ahrex - SA270 - Grosseur d'Hameçons : 4/0 ») ;
  - les prix sont HT, comme le catalogue ;
  - `tax` et `total` sont TTC.
- **Taxes** : le total de taxe de la commande fait foi, et `taxes[]` (TPS, TVQ, TVH…) lui est toujours égal.
  - Le détail enregistré par PrestaShop n'est pas fiable : il est calculé avant les rabais et faux sur 7 000 lignes. Il ne sert qu'à répartir le total au prorata.
  - `taxes_exact` dit si le détail d'origine tombait déjà juste.
- **Adresses de livraison et de facturation copiées dans chaque commande**, comme sur la facture. Le carnet d'adresses n'est gardé que pour les comptes migrés.
- **Rapprochement** : le nombre de commandes et le total TTC par année et par devise, calculés à partir du JSON, doivent être identiques au calcul direct de MySQL. Il en va de même pour le nombre de lignes. Sinon le run s'arrête.

## `clients.json` (contrat de l'import du nouveau site)

Un changement incompatible incrémente `meta.schema_version` (aujourd'hui 1) et se décrit ici. Les dates sont en heure locale du serveur (`AAAA-MM-JJ HH:MM:SS`, America/Toronto).

### `meta`

- `schema_version`, `generated_at`, `source` (`local` | `prod`) ;
- `catalogue_generated_at` : l'extraction du catalogue à laquelle les lignes sont reliées. Elle doit être celle qui a été importée ;
- `comptes_depuis`, `paniers_depuis`, `password_scheme` ;
- `statuses` : libellés fr/en des 5 statuts ;
- `counts`.

### `accounts[]`

- `email` : la clé, en minuscules ;
- `first_name`, `last_name`, `locale` (`fr` | `en`), `created_at` (la plus ancienne des fiches fusionnées) ;
- `password` : `{scheme: "prestashop_md5+argon2id", hash: "$argon2id$v=19$m=19456,t=2,p=1$…"}` (voir plus bas) ;
- `newsletter` : `{consent, at, source}`. `source` vaut `compte` (case cochée dans le compte) ou `formulaire` (bloc infolettre) ;
- `prestashop_ids[]` ;
- `addresses[]` : `label`, `first_name`, `last_name`, `company`, `line1`, `line2`, `postal_code`, `city`, `region_code` (ISO, p. ex. `QC`), `country_code` (ISO), `phone`, `instructions`, `prestashop_id` ;
- `cart` : le panier en cours, ou `null`. `{prestashop_id, updated_at, currency, lines[]}`, chaque ligne `{prestashop_product_id, prestashop_variant_id, product_id, quantity, added_at}`. Toutes les lignes sont encore vendues (`product_id` jamais `null`).

### `orders[]`

**Identité de la commande**
- `prestashop_id`, `reference` (les 9 lettres que voit le client), `invoice_number` (`#IN018149`, ou `null` si aucune facture n'a été émise), `invoice_date`, `placed_at` ;
- `account` : le courriel du compte, ou `null` ;
- `email` : celui de la fiche d'origine, `null` si la fiche n'existe plus ;
- `locale`, `status` (`shipped`, `cancelled`, `refunded`, `pending_payment`, `processing`), `prestashop_status` (le libellé d'origine).

**Montants** (cents, dans la devise de la commande)
- `currency` (`CAD`, `EUR`, `USD`) ;
- `subtotal`, `discount` et `shipping`, en HT ;
- `wrapping` (toujours 0 aujourd'hui) ;
- `tax`, `total` (TTC), `paid` (montant réellement encaissé selon PrestaShop) ;
- `taxes[]` : `{name, rate, amount}`, `rate` en pourcentage (chaîne, p. ex. `"9.975"`) ou `null` ;
- `taxes_exact`.

**Livraison et paiement**
- `payment` (libellé), `carrier` ;
- `shipping_address`, `billing_address` : mêmes champs que le carnet, sans `label` ni `prestashop_id`.

**Lignes** (`lines[]`)
- `prestashop_product_id`, `prestashop_variant_id` ;
- `product_id` : l'id de la fiche du nouveau catalogue, après fusion (`products.prestashop_id`), ou `null` ;
- `reorderable` : la variante est encore vendue. Un produit simple (`prestashop_variant_id` = `null`) est retrouvé par son id s'il est resté simple : c'est la variante par défaut, sans `prestashop_id`, du nouveau site ;
- `name`, `sku`, `ean13`, `quantity`, `unit_price`, `line_total` (HT).

**Rabais et notes**
- `adjustments[]` : `{name, amount (HT), amount_tax_incl, free_shipping}`. « Transport gratuit » est un rabais égal à la livraison.
- `notes[]` : `{from (client | boutique), private, at, text}`, tirés de `ps_message`. `private` = visible dans l'admin seulement.

### `newsletter_subscribers[]`

`{email, at, source}` : les abonnés qui n'ont pas de compte migré (bloc infolettre, ou case cochée lors d'un achat en invité). Les modules Mailchimp sont actifs : la liste Mailchimp fait peut-être foi, à comparer avant de s'en servir.

## Recommandations pour le nouveau site

**Mots de passe : le même, sans md5 stocké.**
- PrestaShop 1.6 stocke `md5(_COOKIE_KEY_ . mdp)` (`Tools::encrypt`, vérifié dans `site/classes/Tools.php`). `build.py` enveloppe cette empreinte dans argon2id, avec les mêmes paramètres que `src/password.rs` : l'empreinte md5 seule n'est donc jamais stockée.
- À la connexion d'un compte dont le hash porte ce schéma :
  1. `m` = md5 hexadécimal, en minuscules, de `_COOKIE_KEY_` concaténé au mot de passe saisi **sans les espaces de début et de fin** (PrestaShop fait un `trim`, `AuthController.php:270`), en octets UTF-8 ;
  2. vérifier `m` contre le hash argon2 ;
  3. en cas de succès, remplacer le hash par un argon2id normal du mot de passe saisi, et oublier le schéma.
- `_COOKIE_KEY_` est celle de la prod (`site/config/settings.inc.php`). Elle se remet à part, comme secret de l'application, et n'est jamais écrite dans un dépôt ni dans le JSON.
- Après un an et demi, les comptes encore à l'ancien schéma pourraient passer en « réinitialisation obligatoire », et la clé être retirée.
- Une réimportation ne doit pas écraser un hash déjà réécrit : `build.py` tire un sel neuf à chaque run.

**Commandes sans compte.** Elles se rattachent au compte créé plus tard avec le même courriel, mais **seulement une fois ce courriel confirmé** : sinon, n'importe qui pourrait voir les commandes d'un autre en s'inscrivant avec son courriel.

**Paniers.** À l'import, le panier du compte est créé seulement si le compte n'en a pas déjà un sur le nouveau site, pour qu'une réimportation n'écrase pas un panier modifié depuis. À la connexion, on fait comme PrestaShop : si le panier de la session est vide, on reprend celui du compte. Sinon, on garde celui de la session (ou on fusionne les deux, au choix). La variante se retrouve par `prestashop_variant_id` (`variants.prestashop_id`), ou par la variante par défaut de `product_id` pour un produit simple. Le stock et le prix se vérifient à l'affichage, comme pour tout panier.

**Une seule table de commandes.** Une commande Stripe et une commande migrée ont la même forme : montants figés, adresses copiées, lignes nommées. `prestashop_id` et `invoice_number` identifient les anciennes. Les nouvelles factures peuvent continuer la numérotation (le dernier n° est dans le rapport) ou prendre un autre préfixe.

**Commandes ouvertes.** Le rapport liste celles qui sont « en attente » ou « en préparation ». Les plus anciennes (2020-2021) ont sans doute été traitées sans changer de statut : il faut les fermer dans PrestaShop avec le client avant l'extraction du jour J.

**Pages de compte.** Les anciennes URL de compte redirigent vers `/fr/compte`, `/fr/compte/commandes`, `/fr/compte/adresses`, `/fr/connexion` et `/fr/mot-de-passe-oublie` (et `/en/…`). Ces chemins sont proposés dans `export-catalogue/taxonomie.json` → `urls.pages_compte`, à ajuster selon les routes du site.

**Archive comptable.** Revenu Québec demande de garder les registres 6 ans. En plus de l'import, on garde un dump de la base PrestaShop sans `ps_ccofflinepayment`, chiffré.

## Jour J

1. La veille : traiter ou fermer dans PrestaShop les commandes ouvertes que liste `rapport.md`.
2. Mettre PrestaShop en maintenance, pour qu'aucune commande n'arrive entre l'extraction et la bascule.
3. Lancer `./export-catalogue/run.sh --prod` (le catalogue importé, lu par export-clients), `./export-catalogue/run.sh --prod --tout` (les redirections, dans `out/prod_tout/`), puis `./export-clients/run.sh --prod`. `--tout` n'écrit pas dans `out/prod/` : sans le premier run, les lignes seraient reliées à un catalogue périmé.
4. Importer dans le nouveau site `export-catalogue/out/prod/` et `export-clients/out/prod/clients.json`, avec `_COOKIE_KEY_` comme secret.
5. Supprimer `export-clients/out/`.
6. Vider ensuite `ps_ccofflinepayment` et les anciennes sauvegardes qui la contiennent (restic, Google Drive, base locale).
