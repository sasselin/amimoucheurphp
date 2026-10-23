# Extraction du catalogue

Extrait le catalogue **tel qu'il est vendu sur le site**, dans un format simple à retravailler pour la recatégorisation et l'import dans le nouveau site. On y trouve les produits, les variantes, les prix réels, les promos en cours, l'inventaire, les catégories, les images originales et le SEO/URL.

```bash
./export-catalogue/run.sh                    # base locale (docker compose up requis)
./export-catalogue/run.sh --verify           # + compare chaque page produit du site local à l'export
./export-catalogue/run.sh --tout             # garde aussi tout ce qui est exclu (voir plus bas)
./export-catalogue/run.sh --prod             # base de prod via SSH
ABANDON_AVANT=2024-01-01 ./export-catalogue/run.sh   # change la date limite « abandonné »
```

Le résultat est écrit dans `export-catalogue/out/<source>/` (`local`, `prod`, suffixe `_tout` avec `--tout`). Le dossier est vidé et réécrit à chaque extraction : le chemin ne change pas, il suffit de rafraîchir la page ou la fenêtre déjà ouverte. Le dossier `out/` n'est pas versionné.

En local, `docker-compose.yml` monte ce dossier en lecture seule dans `/var/www/html/export-catalogue` (le site est dans `site/`), comme en prod.

## Ce qui est exclu par défaut

| Exclu | Pourquoi |
|---|---|
| Produits non affichés | Inactifs, masqués, ou présents dans aucune catégorie affichée. |
| Variantes à 0 $ | Elles ne sont pas vendables. |
| Produits abandonnés | Stock à 0, sans vente ni création depuis le 2023-01-01. |
| Catégories masquées ou vides | Aucune catégorie active ne contient de produit exporté. |
| Orphelins | Les 462 restes de la migration de 2016, dont les produits démo de PrestaShop. Ils ne sont rattachés à aucune boutique. |

Les compteurs sont dans `meta.excluded`. Avec `--tout`, on garde tout, et chaque produit ou catégorie porte un champ `excluded_reasons` qui dit pourquoi il serait exclu.

## Fichiers

| Fichier | Contenu |
|---|---|
| `catalogue.html` | Le mini catalogue à ouvrir dans un navigateur : recherche, filtres (catégorie, marque, promo, rupture), fiche produit avec variantes et prix, onglets Catégories et Promotions. Le fichier est autonome, avec les données intégrées. Les images viennent du site de prod. |
| `catalogue.json` | La source de vérité, imbriquée : `meta`, `categories[]` et `products[]` (avec `variants[]`, `images[]`, `categories[]`, `seo`). |
| `catalogue.xlsx` | Onglets Produits, Variantes, Categories, Images, Promotions (en cours), URL. |
| `variantes.csv` | Une ligne par article vendable : chaque variante, ou le produit lui-même s'il n'a pas de variante. |
| `produits.csv` | Une ligne par produit, avec les descriptions HTML en fr et en. |
| `categories.csv` | L'arbre : parent, profondeur, chemin complet, nombre de produits (direct et avec les sous-catégories). |
| `url_mapping.csv` | Les anciennes URL en fr et en, avec une colonne `nouvelle_url` à remplir pour les redirections 301. |
| `images-descriptions/` | Seulement les images de description récupérées d'archive.org, dont l'original n'existe plus : à héberger sur le nouveau site. La correspondance est dans `meta.description_images.recovered_files`. |
| `images_descriptions.csv` | Le rapport des images des descriptions : `ok`, `archive` (URL de la copie archive.org) ou `perdue` (balise retirée). |
| `verification.csv` | Avec `--verify` : le résultat produit par produit. |
| `recategorisation/` | La nouvelle taxonomie appliquée au catalogue : `avant_apres.html`, les mappings produits, variantes, catégories et couleurs, et `rapport.md`. Voir plus bas. |

Les CSV sont en UTF-8 avec BOM et séparateur virgule.

## Recatégorisation

`recategorise.py` est lancé par `run.sh` après `build.py`. Il applique `taxonomie.json` à chaque produit : la nouvelle catégorie, la marque, les filtres normalisés, les tags d'espèce et de technique, et les badges. La proposition et le benchmark sont dans [RECATEGORISATION.md](RECATEGORISATION.md).

```bash
uv run export-catalogue/recategorise.py export-catalogue/out/local/catalogue.json export-catalogue/out/local
```

On ajuste la proposition dans `taxonomie.json` (arbre, règles sur le nom, cas particuliers par produit, notations de moulinet, couleurs), puis on relance le script. Le libellé du fabricant n'est jamais réécrit : chaque option reçoit une valeur de filtre, ou reste une option libre. Sur le catalogue actif, le script s'arrête dans trois cas : un produit sans catégorie, une catégorie vide, une ancienne catégorie sans cible.

## Prix

- Tous les prix sont **HT, en CAD**, tels qu'un visiteur les voit sur le site.
- Le calcul passe par la logique de prix de PrestaShop elle-même. Ça règle les prix croisés : variante + promo produit ou variante + règle catalogue + prix fixe.
- Chaque prix est donné ainsi :
  - `regular` : le prix avant rabais (le prix barré) ;
  - `final` : le prix payé ;
  - `promo` : `null`, ou le type (`pourcentage` ou `montant`), la valeur, `discount_pct`, la source (`produit`, `variante` ou `regle_catalogue`) et les dates.
- Au niveau du produit, `price` donne la variante par défaut, plus `min` et `max` pour toute la gamme.

## Images des descriptions

`description_images.py` est lancé par `run.sh` entre l'extraction et la génération des fichiers. Pour chaque `<img>` des descriptions de produits et de catégories :
- le chemin relatif (`/img/cms/...`) est résolu sur le site de prod ;
- le `src` devient toujours une URL complète (site, fournisseur, ou copie archive.org si l'original est mort). Les images récupérées d'archive.org sont aussi téléchargées dans `images-descriptions/` ;
- si l'image est introuvable partout, la balise est retirée et l'image est signalée dans le rapport.

Les liens `<a href="/...">` relatifs sont aussi réécrits en URL complètes. La base PrestaShop n'est jamais modifiée.

## Stock et images

- `stock.quantity` est le total des variantes, `variants[].quantity` la quantité par variante. `sell_when_out_of_stock` indique si la commande reste possible en rupture.
- `images[].url` pointe vers le **fichier original** (`/img/p/1/2/3/123.jpg`). La principale vient en premier. `variants[].images` liste les images propres à une variante.

## Prod

`--prod` exécute `export.php` dans le conteneur apache de la prod par SSH (`amimoucheur@143.198.43.249`, `/srv/amimoucheur`) et récupère le JSON en local : rien n'est écrit sur le serveur. Il faut d'abord y avoir copié ce dossier (`site/export-catalogue/`). On peut changer la cible avec `PROD_SSH`, `PROD_DIR` et `BASE_URL`. Le `.htaccess` bloque l'accès web au dossier, et `export.php` refuse de tourner hors CLI.

## Vérification

`verify.py` charge la page de chaque produit sur le site local. Il vérifie que la page est bien servie, et que le prix affiché et le prix de chaque variante correspondent à l'export. Le prix des variantes est recalculé comme le fait `themes/default-bootstrap/js/product.js`.

Options : `--ids 12,34`, `--sample N`, `--base URL`.
