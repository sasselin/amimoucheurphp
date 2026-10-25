<?php
/**
 * Extraction du catalogue tel qu'il est vendu sur le site : produits affichés, variantes,
 * prix croisés, promos en cours, inventaire, catégories, images originales, SEO/URL.
 *
 * Les prix passent par Product::getPriceStatic(), la même logique que la boutique :
 * impact de variante + promo produit/variante + règle catalogue + prix fixe.
 *
 * Usage (CLI seulement, JSON sur stdout, progression sur stderr) :
 *   php export-catalogue/export.php [--base-url=https://www.amimoucheur.com] [--source=local]
 *                                   [--abandon-avant=2025-01-01] [--tout]
 *
 * Exclus par défaut : produits non affichés, variantes à 0 $, produits abandonnés (stock à 0 et
 * aucune vente ni création depuis --abandon-avant), catégories masquées ou sans produit.
 * --tout : garde tout, avec les raisons d'exclusion dans « excluded_reasons ».
 */

if (php_sapi_name() !== 'cli') {
    header('HTTP/1.1 403 Forbidden');
    exit;
}

// Lu depuis l'entrée standard (extraction de prod, voir run.sh), PHP ne définit pas STDERR et le nom
// du script vaut « - » : PrestaShop s'arrête alors sans message pendant config.inc.php
if (!defined('STDERR')) {
    define('STDERR', fopen('php://stderr', 'w'));
}
if ($_SERVER['SCRIPT_NAME'] === '-') {
    $_SERVER['SCRIPT_NAME'] = $_SERVER['PHP_SELF'] = '/export-catalogue/export.php';
    $_SERVER['SCRIPT_FILENAME'] = getcwd().'/../export-catalogue/export.php';
}

ini_set('memory_limit', '1536M');
set_time_limit(0);

$opts = getopt('', array('base-url::', 'source::', 'abandon-avant::', 'tout'));
$base_url = rtrim(isset($opts['base-url']) && $opts['base-url'] ? $opts['base-url'] : 'https://www.amimoucheur.com', '/');
$source = isset($opts['source']) && $opts['source'] ? $opts['source'] : 'local';
$keep_all = isset($opts['tout']);
$abandon_before = isset($opts['abandon-avant']) && $opts['abandon-avant'] ? $opts['abandon-avant'] : '2025-01-01';

// config.inc.php s'attend à un contexte HTTP
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['HTTP_HOST'] = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
require dirname(__FILE__).'/../config/config.inc.php';

function progress($msg)
{
    fwrite(STDERR, $msg."\n");
}

function rows($sql)
{
    $r = Db::getInstance()->executeS($sql);
    return $r ? $r : array();
}

function dt($v)
{
    return ($v === null || $v === '' || strpos($v, '0000-00-00') === 0) ? null : $v;
}

/** Texte vide => null, pour ne pas traîner des "" partout. */
function txt($v)
{
    $v = trim((string)$v);
    return $v === '' ? null : $v;
}

$p = _DB_PREFIX_;
$id_shop = (int)Configuration::get('PS_SHOP_DEFAULT');
$context = Context::getContext();
$context->shop = new Shop($id_shop);
$context->currency = new Currency((int)Configuration::get('PS_CURRENCY_DEFAULT'));
$context->country = new Country((int)Configuration::get('PS_COUNTRY_DEFAULT'));
$context->customer = new Customer();
$context->cart = new Cart();
$context->language = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
$home_id = (int)Configuration::get('PS_HOME_CATEGORY');
$root_id = (int)Configuration::get('PS_ROOT_CATEGORY');
$order_out_of_stock_default = (int)Configuration::get('PS_ORDER_OUT_OF_STOCK');
$now = date('Y-m-d H:i:s');

// Langues : id => iso
$langs = array();
foreach (Language::getLanguages(false) as $l) {
    $langs[(int)$l['id_lang']] = $l['iso_code'];
}
$multi_lang = count($langs) > 1;

/** Regroupe des lignes *_lang en [id][champ][iso] = valeur. */
function by_lang($rows, $id_key, $fields, $langs)
{
    $out = array();
    foreach ($rows as $r) {
        if (!isset($langs[(int)$r['id_lang']])) {
            continue;
        }
        foreach ($fields as $f) {
            $out[(int)$r[$id_key]][$f][$langs[(int)$r['id_lang']]] = txt($r[$f]);
        }
    }
    return $out;
}

/** Valeur multilingue [iso => texte], ou null si vide dans toutes les langues. */
function ml($data, $id, $field)
{
    if (!isset($data[$id][$field]) || !array_filter($data[$id][$field], 'strlen')) {
        return null;
    }
    return $data[$id][$field];
}

progress('Chargement des catégories...');

$cats = array();
foreach (rows('SELECT c.id_category, c.id_parent, c.level_depth, c.active, cs.position
    FROM '.$p.'category c LEFT JOIN '.$p.'category_shop cs ON cs.id_category = c.id_category AND cs.id_shop = '.$id_shop.'
    ORDER BY c.level_depth, cs.position, c.id_category') as $r) {
    $cats[(int)$r['id_category']] = $r;
}
$cat_lang = by_lang(
    rows('SELECT id_category, id_lang, name, link_rewrite, meta_title, meta_description, description
        FROM '.$p.'category_lang WHERE id_shop = '.$id_shop),
    'id_category',
    array('name', 'link_rewrite', 'meta_title', 'meta_description', 'description'),
    $langs
);
$cat_visitor = array();
foreach (rows('SELECT id_category FROM '.$p.'category_group WHERE id_group = '.(int)Configuration::get('PS_UNIDENTIFIED_GROUP')) as $r) {
    $cat_visitor[(int)$r['id_category']] = true;
}

// Chemin (sans Accueil) et affichage (toute la chaîne active jusqu'à Accueil) de chaque catégorie
$cat_info = array();
foreach ($cats as $id => $c) {
    $chain = array();
    $cur = $id;
    $guard = 0;
    while (isset($cats[$cur]) && $cur != $root_id && $guard++ < 20) {
        $chain[] = $cur;
        if ($cur == $home_id) {
            break;
        }
        $cur = (int)$cats[$cur]['id_parent'];
    }
    $chain = array_reverse($chain);
    $displayed = $chain && $chain[0] == $home_id;
    foreach ($chain as $cid) {
        if (!(int)$cats[$cid]['active'] || !isset($cat_visitor[$cid])) {
            $displayed = false;
        }
    }
    $path_ids = $id == $home_id ? array($home_id) : array_values(array_diff($chain, array($home_id)));
    $path = array();
    foreach ($langs as $iso) {
        $names = array();
        foreach ($path_ids as $cid) {
            $names[] = isset($cat_lang[$cid]['name'][$iso]) ? $cat_lang[$cid]['name'][$iso] : '#'.$cid;
        }
        $path[$iso] = implode(' > ', $names);
    }
    $cat_info[$id] = array('path' => $path, 'path_ids' => $path_ids, 'displayed' => $displayed);
}

function cat_url($id, $iso)
{
    global $cat_lang, $base_url, $multi_lang, $home_id;
    $prefix = $base_url.'/'.($multi_lang ? $iso.'/' : '');
    if ($id == $home_id) {
        return $prefix;
    }
    return $prefix.$id.'-'.(isset($cat_lang[$id]['link_rewrite'][$iso]) ? $cat_lang[$id]['link_rewrite'][$iso] : '');
}

progress('Chargement des produits...');

$products = rows('SELECT p.id_product, p.reference, p.id_manufacturer, p.date_add, p.date_upd, p.weight, p.ean13,
        ps.price, ps.active, ps.visibility, ps.id_category_default
    FROM '.$p.'product p JOIN '.$p.'product_shop ps ON ps.id_product = p.id_product AND ps.id_shop = '.$id_shop.'
    ORDER BY p.id_product');

// Produits en base mais rattachés à aucune boutique (restes de la migration de 2016) : invisibles partout
$orphans = (int)Db::getInstance()->getValue('SELECT COUNT(*) FROM '.$p.'product p
    LEFT JOIN '.$p.'product_shop ps ON ps.id_product = p.id_product AND ps.id_shop = '.$id_shop.'
    WHERE ps.id_product IS NULL');

$prod_lang = by_lang(
    rows('SELECT id_product, id_lang, name, description_short, description, link_rewrite, meta_title, meta_description
        FROM '.$p.'product_lang WHERE id_shop = '.$id_shop),
    'id_product',
    array('name', 'description_short', 'description', 'link_rewrite', 'meta_title', 'meta_description'),
    $langs
);

$last_sale = array();
foreach (rows('SELECT od.product_id, MAX(o.date_add) last FROM '.$p.'order_detail od
        JOIN '.$p.'orders o ON o.id_order = od.id_order GROUP BY od.product_id') as $r) {
    $last_sale[(int)$r['product_id']] = $r['last'];
}

$brands = array();
$brands_out = array();
foreach (rows('SELECT id_manufacturer, name, active FROM '.$p.'manufacturer ORDER BY id_manufacturer') as $r) {
    $mid = (int)$r['id_manufacturer'];
    $brands[$mid] = $r['name'];
    // Format des liens marque de PrestaShop 1.6 : /{lang}/{id}_{rewrite} (route manufacturer_rule par défaut)
    $seo = array();
    foreach ($langs as $iso) {
        $seo[$iso] = array('url' => $base_url.'/'.($multi_lang ? $iso.'/' : '').$mid.'_'.Tools::link_rewrite($r['name']));
    }
    $brands_out[] = array('id' => $mid, 'name' => $r['name'], 'active' => (bool)$r['active'], 'seo' => $seo);
}

// Pages fixes (mon compte, historique, connexion…) : leur slug par langue (ps_meta_lang), pour les redirections
// des pages de compte. Format des liens de PrestaShop 1.6 : /{lang}/{url_rewrite}
$pages = array();
foreach (rows('SELECT m.page, ml.id_lang, ml.url_rewrite FROM '.$p.'meta m
        JOIN '.$p.'meta_lang ml ON ml.id_meta = m.id_meta AND ml.id_shop = '.$id_shop.'
        WHERE ml.url_rewrite <> \'\' ORDER BY m.page') as $r) {
    if (isset($langs[(int)$r['id_lang']])) {
        $pages[$r['page']][$langs[(int)$r['id_lang']]] = '/'.($multi_lang ? $langs[(int)$r['id_lang']].'/' : '').$r['url_rewrite'];
    }
}

$prod_cats = array();
foreach (rows('SELECT id_product, id_category FROM '.$p.'category_product ORDER BY id_product, id_category') as $r) {
    $prod_cats[(int)$r['id_product']][] = (int)$r['id_category'];
}

$tags = array();
foreach (rows('SELECT pt.id_product, t.id_lang, t.name FROM '.$p.'product_tag pt JOIN '.$p.'tag t ON t.id_tag = pt.id_tag ORDER BY t.name') as $r) {
    if (isset($langs[(int)$r['id_lang']])) {
        $tags[(int)$r['id_product']][$langs[(int)$r['id_lang']]][] = $r['name'];
    }
}

// Images : URL du fichier original (sans suffixe de taille)
$image_lang = by_lang(rows('SELECT id_image, id_lang, legend FROM '.$p.'image_lang'), 'id_image', array('legend'), $langs);
$images = array();
$image_url = array();
foreach (rows('SELECT i.id_image, i.id_product, i.position, ish.cover
        FROM '.$p.'image i JOIN '.$p.'image_shop ish ON ish.id_image = i.id_image AND ish.id_shop = '.$id_shop.'
        ORDER BY i.id_product, ish.cover DESC, i.position') as $r) {
    $id_image = (int)$r['id_image'];
    $file = 'img/p/'.Image::getImgFolderStatic($id_image).$id_image.'.jpg';
    $image_url[$id_image] = $base_url.'/'.$file;
    $images[(int)$r['id_product']][] = array(
        'url' => $image_url[$id_image],
        'cover' => (bool)$r['cover'],
        'legend' => ml($image_lang, $id_image, 'legend'),
        'file_missing' => !file_exists(_PS_ROOT_DIR_.'/'.$file),
    );
}

$stock = array();
foreach (rows('SELECT id_product, id_product_attribute, quantity, out_of_stock
        FROM '.$p.'stock_available WHERE id_shop = '.$id_shop) as $r) {
    $stock[(int)$r['id_product']][(int)$r['id_product_attribute']] = $r;
}

// Variantes
$combos = array();
foreach (rows('SELECT pa.id_product_attribute, pa.id_product, pa.reference, pas.price, pas.weight, pas.default_on
        FROM '.$p.'product_attribute pa
        JOIN '.$p.'product_attribute_shop pas ON pas.id_product_attribute = pa.id_product_attribute AND pas.id_shop = '.$id_shop.'
        ORDER BY pa.id_product, pa.id_product_attribute') as $r) {
    $combos[(int)$r['id_product']][(int)$r['id_product_attribute']] = $r;
}
$group_lang = by_lang(rows('SELECT id_attribute_group, id_lang, public_name FROM '.$p.'attribute_group_lang'), 'id_attribute_group', array('public_name'), $langs);
$attr_lang = by_lang(rows('SELECT id_attribute, id_lang, name FROM '.$p.'attribute_lang'), 'id_attribute', array('name'), $langs);
$combo_attrs = array();
foreach (rows('SELECT pac.id_product_attribute, a.id_attribute, a.id_attribute_group
        FROM '.$p.'product_attribute_combination pac
        JOIN '.$p.'attribute a ON a.id_attribute = pac.id_attribute
        JOIN '.$p.'attribute_group ag ON ag.id_attribute_group = a.id_attribute_group
        ORDER BY pac.id_product_attribute, ag.position, a.id_attribute_group') as $r) {
    $combo_attrs[(int)$r['id_product_attribute']][] = $r;
}
$combo_images = array();
foreach (rows('SELECT id_product_attribute, id_image FROM '.$p.'product_attribute_image WHERE id_image > 0') as $r) {
    $combo_images[(int)$r['id_product_attribute']][] = (int)$r['id_image'];
}

// Prix spécifiques bruts, pour décrire la promo appliquée
$rule_names = array();
foreach (rows('SELECT id_specific_price_rule, name FROM '.$p.'specific_price_rule') as $r) {
    $rule_names[(int)$r['id_specific_price_rule']] = $r['name'];
}
$specific_by_id = array();
foreach (rows('SELECT * FROM '.$p.'specific_price WHERE id_shop IN (0, '.$id_shop.')') as $r) {
    $specific_by_id[(int)$r['id_specific_price']] = $r;
}

/** Prix vus par un visiteur non connecté : régulier (barré) et final, plus la promo qui fait la différence. */
function compute_price($id_product, $ipa)
{
    global $specific_by_id, $rule_names;
    $sp = null;
    $final = round(Product::getPriceStatic($id_product, false, $ipa, 6, null, false, true, 1, false, null, null, null, $sp), 2);
    $unused = null;
    $regular = round(Product::getPriceStatic($id_product, false, $ipa, 6, null, false, false, 1, false, null, null, null, $unused), 2);

    $promo = null;
    // getPriceStatic() réécrit $sp['price'] : on reprend la ligne brute
    if ($final < $regular && is_array($sp) && isset($sp['id_specific_price'], $specific_by_id[(int)$sp['id_specific_price']])) {
        $raw = $specific_by_id[(int)$sp['id_specific_price']];
        $rule = (int)$raw['id_specific_price_rule'];
        $promo = array(
            'type' => $raw['reduction_type'] == 'percentage' ? 'pourcentage' : 'montant',
            // pourcentage en % (20 = -20 %), montant en $
            'value' => $raw['reduction_type'] == 'percentage' ? round($raw['reduction'] * 100, 2) : round($raw['reduction'], 2),
            'discount_pct' => $regular > 0 ? round(($regular - $final) / $regular * 100, 1) : null,
            'source' => $rule ? 'regle_catalogue' : ((int)$raw['id_product_attribute'] ? 'variante' : 'produit'),
            'rule' => $rule && isset($rule_names[$rule]) ? $rule_names[$rule] : null,
            'start' => dt($raw['from']),
            'end' => dt($raw['to']),
        );
    }
    return array('regular' => $regular, 'final' => $final, 'promo' => $promo);
}

$out_products = array();
$skipped = array('not_displayed' => 0, 'zero_price_variants' => 0, 'zero_price_products' => 0, 'abandoned' => 0);
$used_cats = array();
$n = count($products);
$i = 0;
foreach ($products as $row) {
    if (++$i % 200 == 0) {
        progress("  $i / $n");
    }
    $id = (int)$row['id_product'];
    $default_cat = (int)$row['id_category_default'];

    // Affichage : actif, visible dans le catalogue, et au moins une catégorie affichée
    $reasons = array();
    if (!(int)$row['active']) {
        $reasons[] = 'inactif';
    }
    if (!in_array($row['visibility'], array('both', 'catalog'))) {
        $reasons[] = 'visibilite:'.$row['visibility'];
    }
    $cat_ids = array();
    foreach (isset($prod_cats[$id]) ? $prod_cats[$id] : array() as $cid) {
        if (isset($cat_info[$cid]) && ($keep_all || $cat_info[$cid]['displayed'])) {
            $cat_ids[] = $cid;
        }
    }
    $has_displayed_cat = (bool)array_filter($cat_ids, function ($cid) use ($cat_info) {
        return $cat_info[$cid]['displayed'];
    });
    if (!$has_displayed_cat) {
        $reasons[] = 'aucune_categorie_affichee';
    }
    if ($reasons && !$keep_all) {
        $skipped['not_displayed']++;
        continue;
    }

    // Variantes (celles à 0 $ ne sont pas vendables : on les écarte)
    $variants = array();
    foreach (isset($combos[$id]) ? $combos[$id] : array() as $ipa => $c) {
        $price = compute_price($id, $ipa);
        if ($price['final'] <= 0 && !$keep_all) {
            $skipped['zero_price_variants']++;
            continue;
        }
        $attrs = array();
        $label = array();
        foreach (isset($combo_attrs[$ipa]) ? $combo_attrs[$ipa] : array() as $a) {
            $group = ml($group_lang, (int)$a['id_attribute_group'], 'public_name');
            $value = ml($attr_lang, (int)$a['id_attribute'], 'name');
            $attrs[] = array('group' => $group, 'value' => $value);
            foreach ($langs as $iso) {
                $label[$iso][] = (isset($group[$iso]) ? $group[$iso] : '?').': '.(isset($value[$iso]) ? $value[$iso] : '?');
            }
        }
        foreach ($label as $iso => $parts) {
            $label[$iso] = implode(' / ', $parts);
        }
        $vimages = array();
        foreach (isset($combo_images[$ipa]) ? $combo_images[$ipa] : array() as $id_image) {
            if (isset($image_url[$id_image])) {
                $vimages[] = $image_url[$id_image];
            }
        }
        $variants[] = array(
            'id' => $ipa,
            'reference' => txt($c['reference']),
            'default' => (bool)$c['default_on'],
            'label' => $label,
            'attributes' => $attrs,
            'price' => $price,
            'quantity' => isset($stock[$id][$ipa]) ? (int)$stock[$id][$ipa]['quantity'] : 0,
            'weight' => (float)$row['weight'] + (float)$c['weight'],
            'images' => $vimages,
        );
    }
    if (isset($combos[$id]) && !$variants) {
        // Toutes les variantes étaient à 0 $
        $skipped['zero_price_products']++;
        continue;
    }

    // Prix du produit : prix réel s'il n'a pas de variante, sinon fourchette des variantes
    if ($variants) {
        $finals = array();
        $regulars = array();
        $default_price = null;
        foreach ($variants as $v) {
            $finals[] = $v['price']['final'];
            $regulars[] = $v['price']['regular'];
            if ($v['default'] || $default_price === null) {
                $default_price = $v['price'];
            }
        }
        $price = array(
            'regular' => $default_price['regular'],
            'final' => $default_price['final'],
            'min' => min($finals),
            'max' => max($finals),
            'promo' => $default_price['promo'],
            'some_variants_on_promo' => (bool)array_filter($variants, function ($v) {
                return $v['price']['promo'] !== null;
            }),
        );
        $quantity = array_sum(array_map(function ($v) {
            return $v['quantity'];
        }, $variants));
    } else {
        $price = compute_price($id, 0);
        if ($price['final'] <= 0) {
            $reasons[] = 'prix_0';
            if (!$keep_all) {
                $skipped['zero_price_products']++;
                continue;
            }
        }
        $quantity = isset($stock[$id][0]) ? (int)$stock[$id][0]['quantity'] : 0;
    }

    // Abandonné : plus de stock et rien (ni vente ni création) depuis la date limite
    $last_activity = max((string)$row['date_add'], isset($last_sale[$id]) ? (string)$last_sale[$id] : '');
    if ($quantity <= 0 && $last_activity < $abandon_before) {
        $reasons[] = 'abandonne';
        if (!$keep_all) {
            $skipped['abandoned']++;
            continue;
        }
    }

    // Politique de rupture : 2 = valeur par défaut de la boutique
    $oos = isset($stock[$id][0]) ? (int)$stock[$id][0]['out_of_stock'] : 2;
    if ($oos == 2) {
        $oos = $order_out_of_stock_default;
    }

    // SEO / URL (format de la prod : /{lang}/{categorie}/{id}-{slug}[-{ean13}].html)
    $seo = array();
    foreach ($langs as $iso) {
        $slug = isset($prod_lang[$id]['link_rewrite'][$iso]) ? $prod_lang[$id]['link_rewrite'][$iso] : '';
        $cat_rw = isset($cat_lang[$default_cat]['link_rewrite'][$iso]) ? $cat_lang[$default_cat]['link_rewrite'][$iso] : '';
        $seo[$iso] = array(
            'url' => $base_url.'/'.($multi_lang ? $iso.'/' : '').($cat_rw !== '' ? $cat_rw.'/' : '')
                .$id.'-'.$slug.(!empty($row['ean13']) ? '-'.$row['ean13'] : '').'.html',
            'slug' => $slug,
            'meta_title' => isset($prod_lang[$id]['meta_title'][$iso]) ? $prod_lang[$id]['meta_title'][$iso] : null,
            'meta_description' => isset($prod_lang[$id]['meta_description'][$iso]) ? $prod_lang[$id]['meta_description'][$iso] : null,
        );
    }

    $categories = array();
    foreach ($cat_ids as $cid) {
        $categories[] = array('id' => $cid, 'path' => $cat_info[$cid]['path']);
        $used_cats[$cid] = isset($used_cats[$cid]) ? $used_cats[$cid] + 1 : 1;
    }

    $product = array(
        'id' => $id,
        'reference' => txt($row['reference']),
        'name' => ml($prod_lang, $id, 'name'),
        'brand' => isset($brands[(int)$row['id_manufacturer']]) ? $brands[(int)$row['id_manufacturer']] : null,
        'brand_id' => isset($brands[(int)$row['id_manufacturer']]) ? (int)$row['id_manufacturer'] : null,
        'price' => $price,
        'stock' => array(
            'quantity' => $quantity,
            'sell_when_out_of_stock' => (bool)$oos,
        ),
        'default_category' => isset($cat_info[$default_cat])
            ? array('id' => $default_cat, 'path' => $cat_info[$default_cat]['path']) : null,
        'categories' => $categories,
        'images' => isset($images[$id]) ? $images[$id] : array(),
        'variants' => $variants,
        'description_short' => ml($prod_lang, $id, 'description_short'),
        'description' => ml($prod_lang, $id, 'description'),
        'tags' => isset($tags[$id]) ? $tags[$id] : null,
        'weight' => (float)$row['weight'],
        'last_sale' => isset($last_sale[$id]) ? $last_sale[$id] : null,
        'seo' => $seo,
        'date_add' => dt($row['date_add']),
        'date_upd' => dt($row['date_upd']),
    );
    if ($keep_all) {
        $product['excluded_reasons'] = $reasons;
    }
    $out_products[] = $product;
}

// Nombre de produits exportés dans chaque catégorie et ses sous-catégories
$subtree_count = array();
foreach ($used_cats as $cid => $count) {
    foreach ($cat_info[$cid]['path_ids'] as $ancestor) {
        $subtree_count[$ancestor] = isset($subtree_count[$ancestor]) ? $subtree_count[$ancestor] + $count : $count;
    }
}
$subtree_count[$home_id] = count($out_products);

// Achetés ensemble : paires de produits exportés présents dans au moins 3 mêmes commandes valides,
// et le nombre de commandes de chaque produit (pour pondérer : un produit vendu partout n'est lié à rien en particulier)
progress('Produits achetés ensemble...');
$exported = array();
foreach ($out_products as $op) {
    $exported[$op['id']] = true;
}
$order_count = array();
foreach (rows('SELECT od.product_id, COUNT(DISTINCT od.id_order) n FROM '.$p.'order_detail od
        JOIN '.$p.'orders o ON o.id_order = od.id_order AND o.valid = 1 GROUP BY od.product_id') as $r) {
    if (isset($exported[(int)$r['product_id']])) {
        $order_count[(int)$r['product_id']] = (int)$r['n'];
    }
}
$pairs = array();
foreach (rows('SELECT a.product_id x, b.product_id y, COUNT(DISTINCT a.id_order) n
        FROM '.$p.'order_detail a
        JOIN '.$p.'order_detail b ON b.id_order = a.id_order AND a.product_id < b.product_id
        JOIN '.$p.'orders o ON o.id_order = a.id_order AND o.valid = 1
        GROUP BY a.product_id, b.product_id HAVING n >= 3') as $r) {
    if (isset($exported[(int)$r['x']], $exported[(int)$r['y']])) {
        $pairs[] = array((int)$r['x'], (int)$r['y'], (int)$r['n']);
    }
}

// Arbre des catégories : seulement les catégories affichées et non vides (ou toutes avec --tout)
$categories_out = array();
$skipped['empty_or_hidden_categories'] = 0;
foreach ($cats as $id => $c) {
    if ($id == $root_id) {
        continue;
    }
    $cat_reasons = array();
    if (!$cat_info[$id]['displayed']) {
        $cat_reasons[] = 'masquee';
    }
    if (empty($subtree_count[$id])) {
        $cat_reasons[] = 'vide';
    }
    if ($cat_reasons && !$keep_all) {
        $skipped['empty_or_hidden_categories']++;
        continue;
    }
    $seo = array();
    foreach ($langs as $iso) {
        $seo[$iso] = array(
            'url' => cat_url($id, $iso),
            'slug' => isset($cat_lang[$id]['link_rewrite'][$iso]) ? $cat_lang[$id]['link_rewrite'][$iso] : null,
            'meta_title' => isset($cat_lang[$id]['meta_title'][$iso]) ? $cat_lang[$id]['meta_title'][$iso] : null,
            'meta_description' => isset($cat_lang[$id]['meta_description'][$iso]) ? $cat_lang[$id]['meta_description'][$iso] : null,
        );
    }
    $category = array(
        'id' => $id,
        'parent_id' => (int)$c['id_parent'] == $root_id ? null : (int)$c['id_parent'],
        'depth' => (int)$c['level_depth'] - 1,
        'position' => (int)$c['position'],
        'name' => ml($cat_lang, $id, 'name'),
        'path' => $cat_info[$id]['path'],
        'product_count' => isset($used_cats[$id]) ? $used_cats[$id] : 0,
        'product_count_with_children' => isset($subtree_count[$id]) ? $subtree_count[$id] : 0,
        'description' => ml($cat_lang, $id, 'description'),
        'seo' => $seo,
    );
    if ($keep_all) {
        $category['excluded_reasons'] = $cat_reasons;
    }
    $categories_out[] = $category;
}

$total_variants = array_sum(array_map(function ($x) {
    return count($x['variants']);
}, $out_products));
$doc = array(
    'meta' => array(
        'generated_at' => date('c'),
        'source' => $source,
        'mode' => $keep_all ? 'tout' : 'catalogue_actif',
        'abandoned_before' => $abandon_before,
        'base_url' => $base_url,
        'currency' => $context->currency->iso_code,
        'prices' => 'HT, vus par un visiteur non connecté, quantité 1',
        'counts' => array(
            'products' => count($out_products),
            'variants' => $total_variants,
            'categories' => count($categories_out),
            'brands' => count($brands_out),
        ),
        'excluded' => array_merge($skipped, array('orphans_not_in_shop' => $orphans)),
    ),
    'categories' => $categories_out,
    'brands' => $brands_out,
    'pages' => $pages,
    'products' => $out_products,
    'bought_together' => array('min_orders' => 3, 'orders_by_product' => $order_count, 'pairs' => $pairs),
);

$flags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT;
$json = json_encode($doc, $flags);
if ($json === false) {
    // Texte non UTF-8 en base : on convertit les chaînes fautives plutôt que d'échouer
    array_walk_recursive($doc, function (&$v) {
        if (is_string($v) && !preg_match('//u', $v)) {
            $v = utf8_encode($v);
        }
    });
    $json = json_encode($doc, $flags);
}
if ($json === false) {
    progress('Erreur JSON : '.json_last_error_msg());
    exit(1);
}
echo $json, "\n";
progress(sprintf('Terminé : %d produits, %d variantes, %d catégories. Exclus : %s',
    count($out_products), $total_variants, count($categories_out), json_encode($doc['meta']['excluded'])));
