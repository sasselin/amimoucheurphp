<?php
/**
 * Extraction brute des comptes clients et des commandes, telles qu'elles sont en base.
 * La mise en forme (fusion des comptes, sélection, statuts, montants en cents) est faite par build.py.
 *
 * Usage (CLI seulement, JSON sur stdout, progression sur stderr) : voir run.sh, qui passe ce script par
 * l'entrée standard (local et prod) et envoie sa sortie directement à build.py, sans l'écrire sur disque :
 * elle contient les empreintes md5 des mots de passe.
 *
 *   php -- [--source=local]
 *
 * Ne lit jamais ps_ccofflinepayment (numéros de carte et CVC) : voir README.md.
 */

if (php_sapi_name() !== 'cli') {
    header('HTTP/1.1 403 Forbidden');
    exit;
}

// Lu depuis l'entrée standard, PHP ne définit pas STDERR et le nom du script vaut « - » :
// PrestaShop s'arrête alors sans message pendant config.inc.php (même contournement qu'export-catalogue/export.php)
if (!defined('STDERR')) {
    define('STDERR', fopen('php://stderr', 'w'));
}
if ($_SERVER['SCRIPT_NAME'] === '-') {
    $_SERVER['SCRIPT_NAME'] = $_SERVER['PHP_SELF'] = '/export-clients/export.php';
    $_SERVER['SCRIPT_FILENAME'] = getcwd().'/../export-clients/export.php';
}

// Sous la limite du conteneur apache de prod (700 Mo, partagée avec le site) : PHP s'arrête avec un message plutôt
// que de laisser le noyau tuer un processus du conteneur
ini_set('memory_limit', '400M');
set_time_limit(0);

$opts = getopt('', array('source::'));
$source = isset($opts['source']) && $opts['source'] ? $opts['source'] : 'local';

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

// La sortie est écrite au fil de l'eau, élément par élément, et les commandes sont lues par lots : en prod, le
// conteneur apache est limité à 700 Mo, et tout garder en mémoire avant un seul json_encode le faisait tuer (OOM).

/** json_encode d'un élément ; texte non UTF-8 en base : on convertit les chaînes fautives plutôt que d'échouer. */
function enc($v)
{
    $flags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
    $json = json_encode($v, $flags);
    if ($json === false) {
        array_walk_recursive($v, function (&$x) {
            if (is_string($x) && !preg_match('//u', $x)) {
                $x = utf8_encode($x);
            }
        });
        $json = json_encode($v, $flags);
    }
    if ($json === false) {
        progress('Erreur JSON : '.json_last_error_msg());
        exit(1);
    }
    return $json;
}

$first_key = true;

/** Écrit « "clé": » ; la valeur suit. */
function key_out($key)
{
    global $first_key;
    echo ($first_key ? '{' : ','), enc($key), ':';
    $first_key = false;
}

/** Écrit « "clé": [ … ] » élément par élément. */
function list_out($key, $items)
{
    key_out($key);
    echo '[';
    $i = 0;
    foreach ($items as $item) {
        echo $i++ ? ',' : '', enc($item);
    }
    echo ']';
    return $i;
}

$langs = array();
foreach (rows('SELECT id_lang, iso_code FROM '.$p.'lang') as $r) {
    $langs[(int)$r['id_lang']] = $r['iso_code'];
}
$currencies = array();
foreach (rows('SELECT id_currency, iso_code FROM '.$p.'currency') as $r) {
    $currencies[(int)$r['id_currency']] = $r['iso_code'];
}
$id_fr = (int)Language::getIdByIso('fr');
$counts = array();

progress('Comptes...');
$customers = array();
foreach (rows('SELECT id_customer, email, firstname, lastname, passwd, id_lang, is_guest, active, deleted,
        newsletter, newsletter_date_add, date_add, date_upd FROM '.$p.'customer ORDER BY id_customer') as $r) {
    $customers[] = array(
        'id' => (int)$r['id_customer'],
        'email' => txt($r['email']),
        'first_name' => txt($r['firstname']),
        'last_name' => txt($r['lastname']),
        'passwd' => txt($r['passwd']),
        'lang' => isset($langs[(int)$r['id_lang']]) ? $langs[(int)$r['id_lang']] : null,
        'guest' => (bool)$r['is_guest'],
        'active' => (bool)$r['active'],
        'deleted' => (bool)$r['deleted'],
        'newsletter' => (bool)$r['newsletter'],
        'newsletter_at' => dt($r['newsletter_date_add']),
        'created_at' => dt($r['date_add']),
        'updated_at' => dt($r['date_upd']),
    );
}
$counts['customers'] = list_out('customers', $customers);
unset($customers);

progress('Adresses...');
$addresses = array();
foreach (rows('SELECT a.id_address, a.id_customer, a.alias, a.company, a.firstname, a.lastname, a.address1, a.address2,
        a.postcode, a.city, a.other, a.phone, a.phone_mobile, a.deleted, c.iso_code country, s.iso_code state
        FROM '.$p.'address a
        LEFT JOIN '.$p.'country c ON c.id_country = a.id_country
        LEFT JOIN '.$p.'state s ON s.id_state = a.id_state
        WHERE a.id_customer > 0 ORDER BY a.id_address') as $r) {
    $addresses[] = array(
        'id' => (int)$r['id_address'],
        'customer_id' => (int)$r['id_customer'],
        'alias' => txt($r['alias']),
        'company' => txt($r['company']),
        'first_name' => txt($r['firstname']),
        'last_name' => txt($r['lastname']),
        'line1' => txt($r['address1']),
        'line2' => txt($r['address2']),
        'postal_code' => txt($r['postcode']),
        'city' => txt($r['city']),
        'region_code' => txt($r['state']),
        'country_code' => txt($r['country']),
        'phone' => txt($r['phone_mobile']) ?: txt($r['phone']),
        'other' => txt($r['other']),
        'deleted' => (bool)$r['deleted'],
    );
}
$counts['addresses'] = list_out('addresses', $addresses);
unset($addresses);

progress('Taxes...');
$taxes = array();
foreach (rows('SELECT t.id_tax, t.rate, tl.name FROM '.$p.'tax t
        LEFT JOIN '.$p.'tax_lang tl ON tl.id_tax = t.id_tax AND tl.id_lang = '.$id_fr) as $r) {
    $taxes[(int)$r['id_tax']] = array('name' => txt($r['name']), 'rate' => $r['rate']);
}
key_out('taxes');
echo enc((object)$taxes);

$states = array();
foreach (rows('SELECT id_order_state, name FROM '.$p.'order_state_lang WHERE id_lang = '.$id_fr) as $r) {
    $states[(int)$r['id_order_state']] = txt($r['name']);
}

/** Les commandes dont l'id est dans [$from, $to), complètes (lignes, taxes, rabais, messages). */
function orders_batch($from, $to)
{
    global $p, $langs, $currencies, $states;
    $orders = array();
    foreach (rows('SELECT o.*, ca.name carrier_name FROM '.$p.'orders o
            LEFT JOIN '.$p.'carrier ca ON ca.id_carrier = o.id_carrier
            WHERE o.id_order >= '.(int)$from.' AND o.id_order < '.(int)$to.' ORDER BY o.id_order') as $r) {
        $id = (int)$r['id_order'];
        $orders[$id] = array(
            'id' => $id,
            'reference' => txt($r['reference']),
            'customer_id' => (int)$r['id_customer'],
            'lang' => isset($langs[(int)$r['id_lang']]) ? $langs[(int)$r['id_lang']] : null,
            'currency' => isset($currencies[(int)$r['id_currency']]) ? $currencies[(int)$r['id_currency']] : null,
            'state' => (int)$r['current_state'],
            'state_name' => isset($states[(int)$r['current_state']]) ? $states[(int)$r['current_state']] : null,
            'valid' => (bool)$r['valid'],
            'module' => txt($r['module']),
            'payment' => txt($r['payment']),
            'carrier' => txt($r['carrier_name']),
            'address_delivery_id' => (int)$r['id_address_delivery'],
            'address_invoice_id' => (int)$r['id_address_invoice'],
            'invoice_number' => (int)$r['invoice_number'] ?: null,
            'invoice_date' => dt($r['invoice_date']),
            'delivery_date' => dt($r['delivery_date']),
            'placed_at' => dt($r['date_add']),
            // Montants tels qu'en base (chaînes décimales) : build.py les convertit en cents
            'total_products' => $r['total_products'],
            'total_discounts_tax_excl' => $r['total_discounts_tax_excl'],
            'total_discounts_tax_incl' => $r['total_discounts_tax_incl'],
            'total_shipping_tax_excl' => $r['total_shipping_tax_excl'],
            'total_shipping_tax_incl' => $r['total_shipping_tax_incl'],
            'total_wrapping_tax_excl' => $r['total_wrapping_tax_excl'],
            'total_paid_tax_excl' => $r['total_paid_tax_excl'],
            'total_paid_tax_incl' => $r['total_paid_tax_incl'],
            'total_paid_real' => $r['total_paid_real'],
            'lines' => array(),
            'shipping_taxes' => array(),
            'cart_rules' => array(),
            'messages' => array(),
        );
    }
    if (!$orders) {
        return $orders;
    }
    $range = '>= '.(int)$from.' AND %s < '.(int)$to;
    $in = function ($col) use ($range) {
        return $col.' '.sprintf($range, $col);
    };

    $line_taxes = array();
    foreach (rows('SELECT odt.id_order_detail, odt.id_tax, odt.total_amount FROM '.$p.'order_detail_tax odt
            JOIN '.$p.'order_detail od ON od.id_order_detail = odt.id_order_detail
            WHERE '.$in('od.id_order')) as $r) {
        $line_taxes[(int)$r['id_order_detail']][] = array('tax_id' => (int)$r['id_tax'], 'amount' => $r['total_amount']);
    }
    foreach (rows('SELECT id_order_detail, id_order, product_id, product_attribute_id, product_name, product_reference,
            product_ean13, product_quantity, unit_price_tax_excl, total_price_tax_excl, total_price_tax_incl
            FROM '.$p.'order_detail WHERE '.$in('id_order').' ORDER BY id_order, id_order_detail') as $r) {
        $oid = (int)$r['id_order'];
        if (!isset($orders[$oid])) {
            continue;
        }
        $did = (int)$r['id_order_detail'];
        $orders[$oid]['lines'][] = array(
            'product_id' => (int)$r['product_id'],
            'variant_id' => (int)$r['product_attribute_id'] ?: null,
            'name' => txt($r['product_name']),
            'sku' => txt($r['product_reference']),
            'ean13' => txt($r['product_ean13']),
            'quantity' => (int)$r['product_quantity'],
            'unit_price_tax_excl' => $r['unit_price_tax_excl'],
            'total_price_tax_excl' => $r['total_price_tax_excl'],
            'total_price_tax_incl' => $r['total_price_tax_incl'],
            'taxes' => isset($line_taxes[$did]) ? $line_taxes[$did] : array(),
        );
    }

    foreach (rows('SELECT oi.id_order, oit.id_tax, oit.amount FROM '.$p.'order_invoice_tax oit
            JOIN '.$p.'order_invoice oi ON oi.id_order_invoice = oit.id_order_invoice
            WHERE oit.type = \'shipping\' AND oit.amount <> 0 AND '.$in('oi.id_order')) as $r) {
        if (isset($orders[(int)$r['id_order']])) {
            $orders[(int)$r['id_order']]['shipping_taxes'][] = array('tax_id' => (int)$r['id_tax'], 'amount' => $r['amount']);
        }
    }

    foreach (rows('SELECT id_order, name, value, value_tax_excl, free_shipping FROM '.$p.'order_cart_rule
            WHERE '.$in('id_order').' ORDER BY id_order_cart_rule') as $r) {
        if (isset($orders[(int)$r['id_order']])) {
            $orders[(int)$r['id_order']]['cart_rules'][] = array(
                'name' => txt($r['name']),
                'value_tax_incl' => $r['value'],
                'value_tax_excl' => $r['value_tax_excl'],
                'free_shipping' => (bool)$r['free_shipping'],
            );
        }
    }

    foreach (rows('SELECT id_order, id_employee, message, private, date_add FROM '.$p.'message
            WHERE '.$in('id_order').' ORDER BY id_order, date_add, id_message') as $r) {
        if (isset($orders[(int)$r['id_order']]) && txt($r['message'])) {
            $orders[(int)$r['id_order']]['messages'][] = array(
                'from' => (int)$r['id_employee'] ? 'boutique' : 'client',
                'private' => (bool)$r['private'],
                'at' => dt($r['date_add']),
                'text' => txt($r['message']),
            );
        }
    }
    return array_values($orders);
}

/** Toutes les commandes, par lots de 1 000 id : seul le lot en cours est en mémoire. */
function all_orders()
{
    global $p;
    $max = (int)Db::getInstance()->getValue('SELECT MAX(id_order) FROM '.$p.'orders');
    for ($from = 1; $from <= $max; $from += 1000) {
        progress(sprintf('Commandes %d / %d...', min($from + 999, $max), $max));
        foreach (orders_batch($from, $from + 1000) as $o) {
            yield $o;
        }
    }
}

$counts['orders'] = list_out('orders', all_orders());

// Paniers non commandés des fiches clients (PS_CART_FOLLOWING : PrestaShop redonne le plus récent à la connexion,
// même vide). build.py choisit le panier de chaque compte. Les paniers de visiteurs (id_guest seul) ne servent à rien
// sans leur cookie.
progress('Paniers...');
$carts = array();
foreach (rows('SELECT c.id_cart, c.id_customer, c.id_lang, c.id_currency, c.date_add, c.date_upd FROM '.$p.'cart c
        WHERE c.id_customer > 0 AND NOT EXISTS (SELECT 1 FROM '.$p.'orders o WHERE o.id_cart = c.id_cart)
        ORDER BY c.id_cart') as $r) {
    $carts[(int)$r['id_cart']] = array(
        'id' => (int)$r['id_cart'],
        'customer_id' => (int)$r['id_customer'],
        'lang' => isset($langs[(int)$r['id_lang']]) ? $langs[(int)$r['id_lang']] : null,
        'currency' => isset($currencies[(int)$r['id_currency']]) ? $currencies[(int)$r['id_currency']] : null,
        'created_at' => dt($r['date_add']),
        'updated_at' => dt($r['date_upd']),
        'lines' => array(),
    );
}
foreach (rows('SELECT cp.id_cart, cp.id_product, cp.id_product_attribute, cp.quantity, cp.date_add FROM '.$p.'cart_product cp
        JOIN '.$p.'cart c ON c.id_cart = cp.id_cart AND c.id_customer > 0
        ORDER BY cp.id_cart, cp.date_add, cp.id_product, cp.id_product_attribute') as $r) {
    if (isset($carts[(int)$r['id_cart']])) {
        $carts[(int)$r['id_cart']]['lines'][] = array(
            'product_id' => (int)$r['id_product'],
            'variant_id' => (int)$r['id_product_attribute'] ?: null,
            'quantity' => (int)$r['quantity'],
            'added_at' => dt($r['date_add']),
        );
    }
}
$counts['carts'] = list_out('carts', array_values($carts));
unset($carts);

progress('Infolettre et service à la clientèle...');
$newsletter = array();
foreach (rows('SELECT email, newsletter_date_add FROM '.$p.'newsletter WHERE active = 1 ORDER BY id') as $r) {
    $newsletter[] = array('email' => txt($r['email']), 'at' => dt($r['newsletter_date_add']));
}
$counts['newsletter'] = list_out('newsletter', $newsletter);
unset($newsletter);

$threads = array();
foreach (rows('SELECT id_customer_thread, id_customer, id_order, id_product, email, status, date_add
        FROM '.$p.'customer_thread ORDER BY id_customer_thread') as $r) {
    $threads[(int)$r['id_customer_thread']] = array(
        'id' => (int)$r['id_customer_thread'],
        'email' => txt($r['email']),
        'order_id' => (int)$r['id_order'] ?: null,
        'product_id' => (int)$r['id_product'] ?: null,
        'status' => txt($r['status']),
        'created_at' => dt($r['date_add']),
        'messages' => array(),
    );
}
foreach (rows('SELECT id_customer_thread, id_employee, message, private, date_add FROM '.$p.'customer_message
        ORDER BY id_customer_thread, date_add, id_customer_message') as $r) {
    if (isset($threads[(int)$r['id_customer_thread']])) {
        $threads[(int)$r['id_customer_thread']]['messages'][] = array(
            'from' => (int)$r['id_employee'] ? 'boutique' : 'client',
            'private' => (bool)$r['private'],
            'at' => dt($r['date_add']),
            'text' => txt($r['message']),
        );
    }
}
$counts['threads'] = list_out('threads', array_values($threads));
unset($threads);

// Rapprochement : les mêmes totaux calculés par MySQL, comparés par build.py à ce qu'il a lu
$check = rows('SELECT YEAR(o.date_add) year, c.iso_code currency, COUNT(*) orders, SUM(o.total_paid_tax_incl) total
    FROM '.$p.'orders o LEFT JOIN '.$p.'currency c ON c.id_currency = o.id_currency
    GROUP BY YEAR(o.date_add), c.iso_code ORDER BY year, currency');
$check_lines = (int)Db::getInstance()->getValue('SELECT COUNT(*) FROM '.$p.'order_detail od
    JOIN '.$p.'orders o ON o.id_order = od.id_order');

key_out('meta');
echo enc(array(
    'generated_at' => date('c'),
    'source' => $source,
    // Le préfixe est multilingue, mais souvent rempli dans une seule langue
    'invoice_prefix' => Configuration::get('PS_INVOICE_PREFIX', $id_fr) ?: Configuration::get('PS_INVOICE_PREFIX', (int)Language::getIdByIso('en')),
    'counts' => $counts,
    'check' => array('by_year_currency' => $check, 'lines' => $check_lines),
));
echo "}\n";
progress(sprintf('Terminé : %d comptes, %d adresses, %d commandes (mémoire max %d Mo).', $counts['customers'],
    $counts['addresses'], $counts['orders'], memory_get_peak_usage(true) / 1048576));
