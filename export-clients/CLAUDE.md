# export-clients : aide-mémoire

Extraction des comptes clients et des commandes de PrestaShop 1.6.1.4, mis en forme pour l'import du nouveau site. Le détail (contrat, recommandations, jour J) est dans README.md.

- **Ici on prépare les données, on ne touche pas à `amimoucheur-rust`.** Ce que le site doit faire s'écrit comme recommandation dans README.md. Le projet Rust vient lire ici : tout changement de contrat, de chemin ou de procédure se reporte aussi dans `../MIGRATION.md`.
- Pipeline (`run.sh`) : `export.php`, passé par l'entrée standard au conteneur apache (local ou prod par SSH, comme export-catalogue), puis `build.py` **par un tuyau**. L'extraction brute contient les empreintes md5 : elle n'est jamais écrite sur disque.
- `build.py` lit `export-catalogue/out/<source>/recategorisation/recategorisation.json` (catalogue actif) pour `reorderable` et les paniers : extraire le catalogue d'abord.
- `clients.json` est le contrat lu par l'import du nouveau site : changer sa forme incrémente `meta.schema_version` et se décrit dans README.md.
- Données personnelles : sortie dans `out/` (non versionnée, 700/600). Ne jamais afficher de nom ni de courriel dans `rapport.md` ou dans la conversation ; vérifier la sortie par des comptes agrégés.
- `ps_ccofflinepayment` (numéros de carte et CVC) n'est jamais lu ; `check_forbidden` et `mask_cards` (Luhn) sont les garde-fous.
- Mots de passe : `argon2id(md5hex)` avec les paramètres de `amimoucheur-rust/src/password.rs` (m = 19456, t = 2, p = 1). PrestaShop fait un `trim` sur le mot de passe à la connexion.
- Taxes (`order_taxes`) : le total TTC - HT de la commande fait foi ; le détail de PrestaShop (`ps_order_detail_tax`, avant rabais, souvent faux) ne sert qu'au prorata.
- Paniers (`account_cart`) : comme `Cart::lastNoneOrderedCart` (PS_CART_FOLLOWING), le plus récent des paniers non commandés des fiches de compte, même vide ; gardé s'il date de moins de `--paniers-depuis` (défaut un an). Lignes reliées au catalogue par `load_catalogue` : variante par son id, produit simple par son id.
- Statuts : `statuts.json`. Un statut PrestaShop absent arrête le run.
- Rapprochement bloquant : commandes et total TTC par année et devise, et nombre de lignes, comparés à MySQL.
- Tests : `uv run export-clients/test_build.py` (données synthétiques). Les redirections des pages de compte sont dans export-catalogue (`taxonomie.json` → `urls.pages_compte`).
