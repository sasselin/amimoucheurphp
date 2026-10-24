# export-catalogue : aide-mémoire

Extraction du catalogue PrestaShop 1.6.1.4, recatégorisation proposée pour le nouveau site, et redirections 301 du jour J. Le détail est dans README.md (l'extraction) et RECATEGORISATION.md (la proposition).

## Pipeline

`run.sh` : `export.php` (dans le conteneur apache, local ou prod par SSH ; en prod, le script passe par l'entrée standard, rien n'est copié sur le serveur) → `description_images.py` → `build.py` → `recategorise.py` → `test_redirections.py`.

- La sortie va dans `out/<source>[_tout]/`, à un chemin fixe. Le dossier est vidé puis réécrit à chaque run. Il n'est pas versionné.
- Le run local exige `docker compose up -d` à la racine du dépôt.
- `recategorise.py` importe `build.py` (pour `t`, `write_csv` et `add_sheet`).
- `view_recategorisation.html` est le gabarit versionné de la page `out/.../recategorisation/index.html` (même principe que `view.html` pour `catalogue.html`). `write_html` y remplace `/*__DATA__*/` par les données. Toute la page (HTML, CSS, JS) se modifie dans le gabarit.
- On ajuste la proposition dans `taxonomie.json`, jamais dans les CSV de sortie.
- Photo d'une couleur (`photos_par_couleur` dans recategorise.py, `photos_couleurs.py`) : celle que PrestaShop relie à la variante, sinon une photo devinée d'après sa couleur (marquée « à confirmer », listée dans `photos_couleurs.csv`), sinon `taxonomie.json` → `photos_couleur`. Les couleurs mesurées sont en cache dans `export-catalogue/.cache/` (non versionné). Le site refuse l'agent « Python-urllib » : toute requête vers amimoucheur.com envoie un User-Agent.
- Fiche produit de la page (`openProduct`) : *Aujourd'hui* charge la page en ligne dans une iframe (le site actuel l'autorise), *Demain* est une maquette tirée des données. Les descriptions passent par `description_html` (recategorise.py), qui ne garde aucune balise ni aucun attribut d'origine.
- Catégorie principale (`classify`) : la catégorie par défaut de PrestaShop l'emporte si c'est une feuille. Un parent fourre-tout (« Montage de mouches », « Accessoires ») cède à une sous-catégorie précise du produit.
- Marques partenaires (`taxonomie.json` → `partenaires`, aujourd'hui Yeti) : une entrée du menu vers la page marque, jamais une catégorie.
- Le libellé du fabricant n'est jamais réécrit : on lui associe une valeur de filtre. Seule exception : une couleur prend toujours une majuscule initiale (`libelle_couleur`).
- Cas réglés à la main, par id de produit, dans `taxonomie.json` : `marques_produit` (`null` = sans marque, voulu), `filtres_produit` (filtre lu sur la fiche quand les variantes ne le donnent pas, avec `_source`), `moulinets.par_produit`, `noms_produit` (nom corrigé) et `fusions` (même modèle sur plusieurs fiches : variantes et images réunies sur la fiche gardée). Une couleur classée à la main (`couleurs.manuel`) peut recevoir une liste de familles.
- Les filtres affichés par catégorie sont décidés par `filtres_categorie` (recategorise.py), seuils dans `taxonomie.json` → `filtres_affichage`. La page (`valuesOf`) et `filtres_categorie` doivent lire les mêmes valeurs.
- Arrêts bloquants sur le catalogue actif (de simples avertissements avec `--tout`) : un produit sans catégorie, une catégorie vide, une ancienne catégorie sans cible.

## Règle : tenir les redirections à jour

Les redirections sont produites par `recategorise.py` à partir de `taxonomie.json`. Après **tout** changement de recatégorisation (arbre, règles, cas particuliers, slugs, gabarits d'URL) :

1. relancer `./export-catalogue/run.sh` (ou `--tout`) : le test des redirections doit passer ;
2. si une décision change (schéma d'URL, cibles, procédure du jour J), mettre à jour ce fichier, README.md, RECATEGORISATION.md et le texte `LISEZMOI` de `recategorise.py`.

On ne modifie jamais à la main un fichier de `out/.../caddy/`.

## Graphe « Produits liés »

`related_links` dans recategorise.py prépare les liens ; la compatibilité est calculée dans la page (`compatible()`).
- **Même modèle** (`model_groups`) : noms identiques à 1 à 3 mots de fin près. Le préfixe commun garde au moins deux mots significatifs (`MOTS_GENERIQUES` exclus). On ne retire le premier segment « X - » que si X est une marque connue (`taxonomie.json` → `marques`) : sinon « Sabre de Paon - Couleur Naturelle » et « Ailes de Dinde - Couleur Naturelle » tombent dans la même gamme.
- **Achetés ensemble** : `export.php` extrait les paires présentes dans au moins 3 commandes valides. Le score est le cosinus n / √(commandes a × commandes b), pour qu'une soie ou un bas de ligne vendus partout ne soient pas liés à tout ; chaque produit garde ses 4 meilleurs partenaires.

## Schéma des nouvelles URL (décidé le 2026-10-23)

```
Accueil     /fr/                          /en/
Catégorie   /fr/{univers}/{categorie}     /en/{universe}/{category}   (fr : id des nœuds ; en : slug du nom anglais)
Produit     /fr/produit/{slug}            /en/product/{slug}
Marque      /fr/marques/{marque}          /en/brands/{marque}         (+ liste /fr/marques, /en/brands)
Racine /    301 -> /fr/
```

Gabarits : `taxonomie.json` → `urls`. Pourquoi ce schéma, d'après les sources (ne pas le rediscuter sans raison nouvelle) :
- **Pas de catégorie dans l'URL produit.** Google ne favorise ni les URL plates ni les profondes (Mueller, 2021 : « Google does not count the number of slashes »). Le choix repose sur deux autres règles :
  - une seule URL par contenu (« Minimize the number of alternative URLs », guide e-commerce de Google) : un produit présent dans deux catégories aurait deux chemins ;
  - la stabilité : chaque changement d'URL coûte un 301 et quelques semaines de fluctuation.

  Magento retire aussi la catégorie de l'URL canonique par défaut. Le fil d'Ariane passe par le JSON-LD `BreadcrumbList`.
- **`/fr/` et `/en/`.** Google exige une URL distincte par langue, reliée par `hreflang` (chaque version liste toutes les autres, plus `x-default`), mais le préfixe lui-même ne compte pas : Google détecte la langue d'après le contenu visible. On garde les préfixes parce que les accueils `/fr/` et `/en/` ne changent pas d'URL, et qu'aucun slug ne peut alors entrer en collision avec une langue. Il ne faut pas rediriger automatiquement selon la langue du visiteur.
- **Les mots dans l'URL** sont un signal « very small » (Mueller), mais Google recommande des mots lisibles dans la langue de la page, séparés par des tirets, plutôt que des id.
- **Filtres** (`?marque=`) : ce sont des paramètres `?cle=valeur`, non indexés, et jamais la cible d'un 301. Les anciennes catégories-marques redirigent donc vers la page marque.
- **Slug produit** (`Taxonomie.assign_slugs`) : on garde le slug PrestaShop s'il est sain. Il est refait à partir du nom actuel s'il a perdu ses accents (`cordes-de-rserve`) ou s'il est partagé avec un autre produit. Le slug anglais est aussi refait à partir du nom anglais quand il est resté celui du français alors que le nom a été traduit (186 produits). Beaucoup de produits ont été dupliqués puis renommés en gardant le slug de l'original (les 7 Neptune Deceiver). Un vrai doublon (même nom) reçoit `-{id}` en suffixe : c'est la liste « Doublons à fusionner » du rapport, à régler par `fusions` (même modèle) ou `noms_produit` (nom resté celui de l'original, souvent en anglais). Un produit renommé reçoit un slug refait à partir du nouveau nom. Une surcharge manuelle du slug se met dans `urls.slugs`.

Sources :
- Google : [URL structure](https://developers.google.com/search/docs/crawling-indexing/url-structure), [e-commerce URL](https://developers.google.com/search/docs/specialty/ecommerce/designing-a-url-structure-for-ecommerce-sites), [multilingue](https://developers.google.com/search/docs/specialty/international/managing-multi-regional-sites), [hreflang](https://developers.google.com/search/docs/specialty/international/localized-versions), [facettes](https://developers.google.com/search/docs/crawling-indexing/crawling-managing-faceted-navigation), [site move](https://developers.google.com/search/docs/crawling-indexing/site-move-with-url-changes), [fonctionnalités IA](https://developers.google.com/search/docs/appearance/ai-features)
- [Mueller sur la structure plate (SEJ, 2021)](https://www.searchenginejournal.com/google-no-benefit-to-an-artificially-flat-url-structure/400609/)

## Redirections (`write_redirections` dans recategorise.py)

L'ancien site utilise les routes par défaut de PrestaShop 1.6 (vérifié dans `site/classes/Dispatcher.php` et `ps_configuration` : aucun `PS_ROUTE_*`). Le routage se fait **par id** ; la prod sert `/fr/n-importe-quoi/1175-x.html` et répond par un 302 vers l'URL canonique. On capture donc l'id, jamais le chemin exact.

| Forme | Exemple |
|---|---|
| Produit | `/{fr\|en}/{segment quelconque ou absent}/{id}-{slug}[-{ean13}].html` |
| Catégorie | `/{lang}/{id}-{slug}`, plus les filtres du blocklayered `/{lang}/{id}-{slug}/couleur-rouge`, `?p=`, `?orderby=` |
| Marque | `/{lang}/{id}_{slug}` |
| Non réécrite | `/index.php?…&id_product=` / `id_category=` / `id_manufacturer=`. Langue : `isolang`, sinon `id_lang` (1 = en, 2 = fr), sinon fr |
| Sans préfixe de langue | fr (langue par défaut) |
| Page CMS | `/{lang}/content/{id}-…` : non redirigée (hors catalogue) |

Les regex sont dans `ANCIENNES_URL` (syntaxe commune à RE2 et à Python) : on les modifie là, pas dans le fichier Caddy.

- Cibles : produit actif → sa fiche ; fiche fusionnée (`taxonomie.json` → `fusions`) → la fiche gardée ; produit exclu (`--tout`) → sa nouvelle catégorie, sinon la cible de son ancienne catégorie par défaut, sinon l'accueil. Catégorie sans cible → cible de son ancien parent. Marque sans produit actif → liste des marques. Id inconnu → accueil de la langue. Une cible est toujours une page qui existera.
- Garde-fous bloquants : deux produits actifs avec la même URL ; une nouvelle URL qui ressemble à une ancienne (boucle de redirection) ; une cible absente du nouveau site.
- Format Caddy : un fichier de directives (et non un snippet) importé dans le bloc du site. Pour chaque type et langue, un `map` id → cible. Les `redir` sont dans un `route`, où l'ordre écrit est l'ordre d'exécution : le produit avant la catégorie (le `.html` les distingue), et l'anglais avant le français, puisqu'une URL sans préfixe est en fr. Les cibles sont des chemins relatifs, et la requête d'origine (utm…) est abandonnée.
- `test_redirections.py` lance `caddy:2.8.4` dans Docker (`caddy validate`, puis une requête par ligne de la table et des URL piège).

**Jour J** : `./export-catalogue/run.sh --prod --tout`, puis on suit `out/prod_tout/recategorisation/caddy/LISEZMOI.md`. On importe `redirections.caddy` dans le bloc `www.amimoucheur.com` de `deploy/Caddyfile`, avant le `reverse_proxy` vers le nouveau site. Le `deploy/Caddyfile` actuel n'est pas encore modifié.

## Pour le nouveau site (rien à coder ici)

- **JSON-LD** : `Product` et `Offer` (url = la nouvelle URL canonique, prix, disponibilité, marque, EAN13 comme `gtin`), `BreadcrumbList` (la catégorie), `Organization` (politique de retour). Google y gagne des résultats enrichis ([doc Product](https://developers.google.com/search/docs/appearance/structured-data/product)), mais pas de classement documenté. Pour le GEO, l'étude contrôlée d'Ahrefs (mai 2026) ne montre aucun gain de citations. Les faits importants (prix, stock, compatibilité) doivent donc aussi être visibles sur la page.
- **Google Merchant Center** : au jour J, le flux doit pointer vers les nouvelles URL (l'ancien est `site/gmerchantcenter*.xml`).
- **Cohérence** : sitemap, `hreflang`, canonical, liens internes et JSON-LD utilisent tous les nouvelles URL. On garde les redirections au moins 1 an.
