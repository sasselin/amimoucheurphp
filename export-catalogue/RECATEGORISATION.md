# Recatégorisation du catalogue : proposition

Proposition pour le nouveau site de L'Ami du Moucheur. Elle couvre l'arborescence, les filtres, les tags, les badges et les collections. Elle s'appuie sur l'extraction actuelle (1166 produits, 7482 variantes, 226 catégories) et sur l'étude de 7 sites du marché.

Tout est **calculé et vérifiable**, rien n'est fait à la main dans un tableur :
- `taxonomie.json` décrit la proposition : l'arbre, les filtres, les tags, les marques, les couleurs et les règles ;
- `recategorise.py` l'applique à chaque produit et à chaque variante ;
- `out/local/recategorisation/index.html` montre le résultat.

```bash
./export-catalogue/run.sh                       # extraction + recatégorisation
uv run export-catalogue/recategorise.py export-catalogue/out/local/catalogue.json export-catalogue/out/local
```

## 1. Ce qui ne va pas aujourd'hui

| Constat | Chiffre |
|---|---|
| La marque sert de niveau de catégorie (Simms, Rio, Sage, Neptunes…), répétée dans plusieurs branches | 97 catégories sur 226 sont des marques |
| Le champ marque est vide | 656 produits sur 1166 (56 %) |
| Un groupe d'attributs par modèle (« Sector », « Arrow », « R8 Core »…) : longueur, numéro de soie et sections sont noyés dans du texte libre (« 790-4 (9' # 7) ») | 144 groupes, aucun filtrable |
| Un même concept est éclaté : Couleur / Couleur Pata / Couleur Lamson / Yeti Couleurs… | 846 libellés de couleur |
| Les tags ne sont presque pas utilisés | 17 produits |
| Mouches classées par fournisseur (Neptunes, Shadows) : « Streamers » et « Bombers » apparaissent deux fois | — |
| Catégories hors logique : « POPULAIRES », « Produits Yeti », « Collection pour femmes » au premier niveau | — |
| Produits rangés dans Home, ou sans catégorie par défaut | 24 + 7 |

Le montage de mouches représente **41 % du catalogue** (478 produits). C'est là que la différence se joue.

## 2. Ce que fait le marché

| Site | À prendre | À éviter |
|---|---|---|
| **Salmo Nature** | La meilleure structure : matériaux de montage par animal, soies par usage (froide/tempérée, tropicale, spey), hameçons tagués par usage, vrais filtres longueur et # | Valeurs non normalisées (13' et 13'0'' ; grains et lb mélangés) ; un fourre-tout « Tags » ; collections FR et EN en double |
| **Mouche Expert** | Le vocabulaire québécois (ouananiche, achigan, maskinongé, bar rayé) ; mouches et soies par espèce ; Démonstrateurs/Usagés ; ensembles complets | Les filtres ne sont que des tags et des tranches de prix ; aucun filtre technique ; marques en double |
| **Atelier du Moucheur** | Des libellés FR clairs ; Running line, Corde de réserve et Matériel d'avançon comme catégories distinctes ; un bloc « même marque » | La facette fourre-tout « Caractéristiques » ; couleurs FR et EN en double ; quantité du paquet mélangée à la taille d'hameçon ; tri par prix seulement |
| **Hooké** | Des pages par activité ; contenu (films) relié aux collections ; ensembles prêts-à-pêcher ; badges (Nouveauté, Retour en inventaire) | Une seule facette « Grandeur » qui mélange #, lb et diamètres |
| **Sexton & Sexton** | Une identité saumon atlantique ; une page Niveaux d'eau ; la commande de mouches sur mesure ; les marques québécoises mises en avant | Boutique en anglais seulement ; marque comme seul filtre ; une fiche par taille de canne |
| **Coin du Moucheur**, **Boîte à Mouches** | Pages de services (atelier, restauration) ; matériaux pour la mouche-tube | Marque comme catégorie, aucun filtre, prix cachés, données sales |

**Aucun des 7 sites** n'a :
- de filtres techniques normalisés ;
- de familles de couleurs ;
- d'entrée Débutant, Nouveautés ou Fait au Québec ;
- de lien de compatibilité entre canne, moulinet et soie.

C'est là qu'on se démarque.

## 3. Principes

1. **Catégorie = ce qu'est le produit.** Au plus 2 niveaux, jamais une marque ni un fournisseur.
2. **Filtre = attribut typé, propre à la famille du produit.** Une canne a un numéro de soie, une longueur et des sections ; un hameçon a une taille, un usage et une quantité par paquet.
3. **Deux couches : le libellé du fabricant et la valeur de filtre** (voir section 6).
4. **Tag = vocabulaire contrôlé** : espèce, technique, milieu. **Badge = mise en avant**, calculée automatiquement autant que possible.
5. **Marque = filtre + page marque.** Les 97 anciennes catégories-marques redirigent (301) vers la page marque, qui est indexable. Elles ne redirigent pas vers une URL filtrée, que Google conseille de ne pas indexer.
6. **URL du nouveau site** (`taxonomie.json` → `urls`) : `/fr/produit/{slug}`, `/fr/{univers}/{categorie}`, `/fr/marques/{marque}`, et l'équivalent en `/en/`. La catégorie n'est pas dans l'URL du produit : l'URL reste stable si on recatégorise, et un produit n'a qu'une URL. Les raisons et les sources sont dans `CLAUDE.md`.

## 4. Arborescence : 9 univers, 65 catégories

Nombre de produits entre parenthèses, en comptant les catégories secondaires.

- **Cannes** (65) : Cannes une main · Cannes Spey & Switch · Ensembles prêts-à-pêcher · Étuis & supports à cannes
- **Moulinets** (40) : Moulinets · Bobines de rechange (22 moulinets vendus aussi en bobine) · Étuis à moulinet
- **Soies & bas de ligne** (111) : Soies une main · Têtes & soies Spey · Running lines · Pointes & polyleaders · Bas de ligne · Avançons & matériel (dont les indicateurs de touche) · Corde de réserve · Entretien & accessoires
- **Mouches** (197) : Sèches · Nymphes · Noyées · Streamers & tandems · Mouches à saumon · Surface & souris · Eau salée · Flottants & calants
- **Montage** (478) :
  - Étaux · Outils · Hameçons · Fils de montage
  - Plumes · Poils & fourrures · Dubbing · Synthétiques : l'animal (coq, canard, faisan, lapin, renard…) ou le type (flash & fibres, chenille, corps, pattes, mousse) est un filtre **Matière**, pas un 3e niveau
  - Billes, cônes & yeux · Tinsels, floss & fils métalliques · Colles, vernis & UV · Ensembles & kits. Les outils de mouche-tube et le rangement de l'établi sont dans Outils (filtre Type d'outil)
- **Waders & bottes** (52) : Waders · Bottes de wading · Accessoires de wading (crampons, semelles, réparation, bâtons)
- **Vêtements** (90) : Manteaux & pluie · Hauts & couches · Pantalons · Chapeaux, casquettes & tuques · Gants & bas (dont les bas de wading). Homme et Femme deviennent un filtre Genre.
- **Sacs & rangement** (73) : Vestes, sacs de poitrine & de taille · Sacs à dos · Sacs d'équipement & duffels · Boîtes à mouches
- **Accessoires** (65) : Pinces, coupe-fil & outils · Rétracteurs · Puises · Lunettes polarisées · Glacières, gourdes & camping · Montage de cannes · Livres & guides · Divers
- Utilitaires du menu : Nouveautés · Promos · Signature · **Yeti** · Cartes-cadeaux · Marques · Par espèce

Une catégorie de 1 ou 2 produits rejoint sa voisine la plus proche : elle allonge le menu sans aider à choisir.

**Yeti, partenaire de la boutique.** Une marque n'est jamais une catégorie, mais un partenaire a sa place dans le menu. L'entrée « Yeti » mène à la page marque `/fr/marques/yeti`, qui réunit tous ses produits : glacières, gourdes, sacs et chaise. L'ancienne catégorie « Produits Yeti » y redirige déjà. Les partenaires se déclarent dans `taxonomie.json` → `partenaires`.

## 5. Filtres

**Communs à tout le catalogue** : Marque · Prix · Disponibilité · Promo · Espèce · Technique · Milieu (eau douce / eau salée) · Mise en avant (badges).

**Par famille** :

| Famille | Filtres |
|---|---|
| Cannes | Numéro de soie · Longueur (format unique 9'0") · Sections · Type (une main, switch, spey, euro-nymphe) · Talon de combat |
| Moulinets | Numéro de soie (plage) · Moulinet ou bobine · Couleur |
| Soies | Numéro de soie · Poids en grains · Profil (WF, DT, Skagit, Scandi, intégrée, running line, euro…) · Densité (flottante, hover, intermédiaire, plongeante S1–S8, pointe plongeante) · Climat (froid & tempéré, tropical, eau salée froide) |
| Bas de ligne et avançons | Diamètre (0X–8X) · Résistance (lb) · Matériau (nylon, fluorocarbone, acier) · Longueur |
| Mouches | Type · Taille d'hameçon · Famille de couleur · Lestée |
| Hameçons | Taille · Usage (sèche, nymphe, streamer, saumon, tube, jig, eau salée, prédateur) · **Quantité par paquet, enfin séparée de la taille** · Sans ardillon |
| Matériaux de montage | Famille de couleur (19 familles + indicateurs UV, fluo, holo) · Matière ou animal · Taille |
| Billes et cônes | Matériau (laiton, tungstène, plomb…) · Diamètre **en mm** (3/32" → 2,4 mm) · Couleur |
| Vêtements, waders et bottes | Genre · Taille (XS–3XL) · Coupe (court, régulier, long, king) · Pointure · Couleur |
| Lunettes | Teinte du verre · Matériau du verre |

Ces 30 filtres remplacent les 144 groupes d'attributs.

**Un filtre ne s'affiche que s'il aide à choisir.** Les listes ci-dessus sont des candidats. Chaque catégorie n'en garde que ceux qui trient vraiment ses produits (`filtres_categorie` dans `recategorise.py`, seuils dans `taxonomie.json` → `filtres_affichage`) :

- moins de 8 produits : aucun filtre, la liste tient sur un écran ;
- un filtre propre à une famille doit concerner au moins 30 % des produits affichés (pas de « Talon de combat » sur tout le catalogue) ;
- l'information doit exister pour au moins 30 % des produits, sinon filtrer fait disparaître les autres (sauf pour les cases à cocher : disponibilité, eau salée, mise en avant, oui/non) ;
- il faut au moins 2 choix qui gardent chacun 2 produits ou plus sans les garder tous : dans « Sèches », la marque (toutes Neptune), le type et la technique (toutes « Sèche ») ne retireraient rien.

Les filtres propres à la famille passent avant les filtres communs. Le principe vient de Baymard : des filtres propres à la catégorie, aucun filtre hors sujet, les plus utiles en premier ([Macy's filtering](https://baymard.com/blog/macys-filtering-experience), [filtres et contenu des listes](https://baymard.com/blog/have-filters-for-list-item-info)). Les seuils sont un choix d'ingénierie, pas un chiffre d'étude. Le résultat par catégorie est dans `nouvelles_categories.csv` (colonnes `filtres` et `filtres_ecartes`) et dans le rapport ; la page le montre sous les filtres (« Filtres masqués dans cette catégorie »), avec la raison.

## 6. Deux couches : le libellé du fabricant et la valeur de filtre

Chaque marque a sa propre notation, et le site doit la respecter : c'est ce que le client lit dans le catalogue du fabricant. On ne réécrit donc **jamais** le libellé. On lui **associe** une valeur normalisée, qui ne sert qu'aux filtres et à la compatibilité. Seule retouche : une couleur commence toujours par une majuscule (« earth » devient « Earth »).

| Produit | Libellé affiché | Valeur de filtre |
|---|---|---|
| Lamson Guru S | `- 7 +` | # 6–8 (table Lamson) |
| Galvan Torque | `T-6 ; Soie 6 - 7` | # 6–7 |
| Hardy Zane Carbon | `6000 (6/7/8)` | # 6–8 |
| Nautilus X | `XL (# 6/7)` | # 6–7 |
| Sage Click | `Click (soie 3 - 5)` | # 3–5 |
| Galvan Brookie | `B 2-3 2.72 oz.` | # 2–3 |
| Sage R8 Core | `590-4 (9' # 5)` | # 5 · 9'0" · 4 sections |
| Classic Trout | `376-4` (code seul) | # 3 · 7'6" · 4 sections (code lu) |
| Patagonia Swiftcurrent | `MLM (Medium Long Lenght) pied 9-11` | Taille M · Coupe Long · Pointure 9-11 |
| Rio Powerflex | `5X 5 Lb. 2.3 Kg.` | 5X · 5 lb |
| Billes tungstène | `3/32` | 2,4 mm |
| Flashabou | `Bleu Électrique - #6908` | Bleu (+ fluo) |
| Lamson Litespeed M | `#6` | **à confirmer** |

- Le numéro de soie d'un moulinet est une **plage**. Choisir #7 trouve tous les moulinets qui couvrent 7, peu importe la notation de la marque.
- Toute valeur que les règles ne savent pas convertir est marquée `à_confirmer` ou `à_réviser`, **sans être devinée**.
- Une option qui n'est pas un filtre reste une **option libre** de la fiche : un modèle de kit Shor, le montant d'une carte-cadeau, un grossissement Flip Focal.

**Couleurs.** Chaque produit garde ses noms commerciaux (Riviera, River Rock Green, Bimini Pink…). On leur ajoute une ou plusieurs **familles** qui servent au filtre et à la pastille. Sur 846 libellés :
- 750 sont classés par mot-clé ;
- 81 sont classés à la main (Gingembre, Root Beer, PMD, Perchaude…) ;
- **15 restent à classer**, surtout des noms de mode Simms et Patagonia : la boutique les assigne une fois dans `couleurs.csv`.

## 7. Tags et badges

- **Espèce** : Truite & omble de fontaine · Saumon atlantique · Ouananiche · Bar rayé · Brochet & maskinongé · Achigan · Carpe · Eau salée tropicale
- **Technique** : Sèche · Nymphe & euro · Streamer · Noyée · Spey & Switch · Eau salée
- **Badges calculés automatiquement** :
  - Nouveau (ajouté depuis moins de 120 jours)
  - Promo
  - Dernières unités (stock de 2 ou moins)
  - Sur commande (rupture, commande possible)
  - Épuisé
- **Badges posés par règle** (modifiables) :
  - Signature L'Ami du Moucheur (Musquaro, boîtes, vêtements maison) : 48 produits
  - Fait au Québec : 62 produits
  - Idéal pour débuter : ensembles, kits, Wrangler, Classic Trout…
  - Choix de l'équipe : remplace « POPULAIRES »

Les espèces ne sont posées que là où le nom ou la catégorie le dit : 190 produits aujourd'hui. Une canne à truite dont le nom ne mentionne pas l'espèce reste sans tag. C'est un enrichissement à faire avec l'équipe, voir la section 10.

## 8. Collections éditoriales

- Une page par espèce, avec un texte, les mouches, les soies et les cannes recommandées. C'est le vocabulaire québécois de Mouche Expert, avec de vrais filtres.
- Débuter à la mouche · Débuter le montage
- Saumon atlantique, avec les livres de Jacques Héroux et les mouches de saumon
- Signature L'Ami du Moucheur (Musquaro, Fiset, Galarneau…) · Fait au Québec (Capsize, Gaspé Fly, Hop La Mouche)
- Cadeaux (moins de 50 $, moins de 100 $) · Yeti (partenaire : entrée du menu et page marque)
- Plus tard : Démonstrateurs/Usagés, Retour en inventaire

## 9. Pour aller plus loin que le marché

1. **Complète ton montage.** Sur la fiche d'une canne #7, on propose les moulinets et les soies qui couvrent #7, calculés à partir des plages normalisées. Aucun des 7 sites ne le fait. La démonstration est dans la page avant/après : ouvrir une canne.
2. **Familles de couleurs avec pastilles** pour les 478 produits de montage. Le tieur filtre « Olive » et trouve le dubbing, la chenille, le marabou et la bille olive, quel que soit le nom du fabricant.
3. **Fiche technique uniforme** : une seule grille par famille, au lieu d'un groupe d'attributs par modèle.
4. **Recettes de mouches** (phase 2) : un patron (Musquaro, mouches de Jacques Héroux) mène vers la liste de ses matériaux à acheter. Il faut les recettes de la boutique.
5. **Badges calculés** à partir du stock, des dates et des promos, sans saisie manuelle.

## 10. Questions ouvertes

1. **Tailles Lamson** `#6`, `#8`, `#10`, `#12` (Liquid Max, Litespeed M, Centerfire, Hyperspeed) : est-ce un numéro de soie ou une taille de modèle ? Même question pour Nautilus « Silver King » et Hardy Fortuna Regent 8000. Une fois tranché, une ligne dans `taxonomie.json` → `moulinets` suffit.
2. **Espèces** : la liste vous convient-elle ? Faut-il distinguer omble de fontaine et truite, et ajouter doré et perchaude ?
3. **Yeti** : réglé. Yeti est un partenaire, avec une entrée dans le menu et une page marque. Ses produits restent dans leur catégorie, surtout Accessoires > Glacières, gourdes & camping.
4. **Fait au Québec** : quelles marques en plus de L'Ami du Moucheur, Capsize, Gaspé Fly et Hop La Mouche ? Les mouches Neptune sont-elles montées ici ?
5. **Tag espèce des cannes et des soies** : le nom suffit rarement. Il faudra 30 minutes avec l'équipe pour taguer les modèles à la main, par exemple R8 Core = truite.
6. **Les 15 couleurs à classer** et les 42 produits « à réviser » du rapport.

## 11. Fichiers produits

Dans `export-catalogue/out/local/recategorisation/` :

| Fichier | Contenu |
|---|---|
| `index.html` | Page autonome. Onglets : **Après** (méga-menu, filtres retenus pour la catégorie et filtres masqués avec leur raison, badges, et la fiche produit : *Aujourd'hui* (la page en ligne, dans un cadre), *Demain* (maquette du futur site : photos, choix des variantes en boutons, une option du fabricant qui mêle numéro de soie et longueur séparée en deux choix, stock, caractéristiques, « Complète ton montage » selon le numéro choisi, « Souvent achetés avec »), *Côte à côte*, et la liste de ce qui change pour le client ; les correspondances détaillées restent dans « Détails pour l'équipe ») ; **Avant** (arbre actuel, filtre marque) ; correspondance des catégories ; familles de couleurs ; **Redirections (jour J)** : diagramme de flux des anciennes URL vers les nouvelles pages, test d'une ancienne URL, table filtrable. **Graphe** : vue en graphe à la Obsidian, en deux modes. *Redirections* relie les anciennes catégories à leur cible. *Arborescences avant / après* montre l'arbre actuel et la nouvelle taxonomie côte à côte, sans redirections. Les produits sont en option. *Produits liés* relie les produits par compatibilité (numéro de soie), même modèle ou gamme (déclinaisons vendues séparément, à fusionner en un produit à variantes), même marque et achats communs (au moins 3 commandes communes, d'après l'historique de PrestaShop). La page est générée à partir du gabarit `view_recategorisation.html`. |
| `mapping_produits.csv` | Une ligne par produit : ancienne et nouvelle catégorie, catégories secondaires, marque (et sa source), espèces, techniques, milieu, badges, filtres, confiance, raisons de révision, nouvelle URL. |
| `mapping_variantes.csv` | Une ligne par option de variante : groupe, libellé du fabricant, filtre, valeur de filtre et statut (`ok`, `auto`, `manuel`, `partiel`, `à_classer`, `à_confirmer`, `option libre`). |
| `correspondance_categories.csv` | Chaque ancienne catégorie : ce qu'elle devient, avec les redirections 301 en FR et en EN. |
| `nouvelles_categories.csv` | L'arbre proposé, avec le nombre de produits, les filtres affichés, les filtres écartés (et pourquoi) et les URL. |
| `couleurs.csv` | Chaque libellé de couleur, ses familles et la colonne `famille_a_assigner`. |
| `photos_couleurs.csv` | Les photos associées à une couleur quand PrestaShop ne relie pas la photo à la variante. `photos_couleurs.py` mesure la couleur de chaque photo et la compare à la famille de chaque couleur. Il ne propose rien quand le produit a moins de photos que de couleurs (photos de groupe), quand la photo n'a pas de fond blanc (photo d'ambiance) ou quand deux couleurs se ressemblent trop. Sur les produits déjà reliés dans PrestaShop : 57 justes, 4 fausses. À confirmer, puis à relier dans PrestaShop ; une correction se met dans `taxonomie.json` → `photos_couleur`. |
| `url_mapping.csv` | `url_mapping.csv` de l'extraction, avec la colonne `nouvelle_url` remplie. |
| `caddy/redirections.caddy` | Les redirections 301 de toutes les anciennes URL (produits, catégories, marques), prêtes à importer dans Caddy au jour J. Elles sont testées dans Caddy à chaque `run.sh`. |
| `caddy/redirections.csv` | Chaque ancienne URL, sa cible et la règle appliquée. |
| `caddy/LISEZMOI.md` | La procédure du jour J. |
| `rapport.md` | Avant/après chiffré, couverture des filtres, produits à réviser. |
| `recategorisation.json` | Les décisions, typées, pour l'import du nouveau site : arbre, marques, facettes (valeurs et libellés fr/en), tags, badges manuels, fusions, et pour chaque produit ses noms, slugs, catégories, marque, facettes, tags, badges, variantes et images. Les facettes de chaque option de variante sont dans `variant_options`. Les faits (prix, stock, descriptions) restent dans `catalogue.json`. Contrat décrit dans README.md. |
| `recategorisation.xlsx` | Les mêmes tableaux en onglets. |

**Ajuster la proposition** : modifier `taxonomie.json` et relancer `recategorise.py`. Les possibilités :
- renommer ou déplacer une catégorie ;
- ajouter une règle sur le nom ;
- forcer un produit dans `produits` ;
- ajouter une notation de moulinet ;
- classer une couleur dans `couleurs.manuel` ;
- surcharger le slug d'un produit dans `urls.slugs`.

Les redirections sont recalculées à chaque run : il n'y a jamais rien à corriger à la main dans `caddy/`.

Le script refuse de produire un résultat dans trois cas : un produit sans catégorie, une catégorie vide, une ancienne catégorie sans cible. Il refuse aussi de produire des redirections incohérentes : deux produits avec la même URL, une nouvelle URL qui ressemble à une ancienne, ou une cible qui n'existera pas.
