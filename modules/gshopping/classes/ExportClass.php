<?php

/**
 * 2007-2015 PrestaShop
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2015 PrestaShop SA
 * @license   http://addons.prestashop.com/en/content/12-terms-and-conditions-of-use
 * International Registered Trademark & Property of PrestaShop SA
 * -------------------------------------------------------------------
 *
 * Description :
 *   This is a PHP abstract class for setting all the method require for generating export feed.
 */
abstract class ExportClass
{

    public $productRes;
    const MPN_ACTIVE = true;

    public function __construct($id_category, $id_lang)
    {
        $this->productRes = Db::getInstance()->ExecuteS(
            'SELECT DISTINCT(p.id_product), p.id_category_default, pl.name title, pl.description_short, pl.description,
            pl.link_rewrite, p.weight product_weight, p.price, p.ecotax, p.upc, m.name brand, i.id_image, l.id_lang,
            cl.link_rewrite category_link, cl.name category_name, p.ean13, p.id_tax_rules_group, p.condition, p.quantity, p.weight
            FROM ' . _DB_PREFIX_ . 'product p
            LEFT JOIN ' . _DB_PREFIX_ . 'product_lang pl ON (pl.id_product = p.id_product)
            LEFT JOIN ' . _DB_PREFIX_ . 'lang l ON (l.id_lang = pl.id_lang)
            LEFT JOIN ' . _DB_PREFIX_ . 'manufacturer m ON (m.id_manufacturer = p.id_manufacturer)
            LEFT JOIN ' . _DB_PREFIX_ . 'image i ON (i.id_image = p.id_product)
            LEFT JOIN ' . _DB_PREFIX_ . 'category_lang cl ON (cl.id_category = p.id_category_default AND cl.id_lang = l.id_lang)
            LEFT JOIN ' . _DB_PREFIX_ . 'product_attribute pa ON (pa.id_product = p.id_product)
            WHERE p.active = 1 AND l.id_lang = ' . (int) $id_lang . '
            AND p.id_category_default = ' . (int) $id_category
            , false);
    }

    /*
     * Get the active category for the export for the feed
     *
     * @param int $id_country
     * @param int $id_shop
     * @param string $table
     * @return array
     */

    public function getActiveExportCategory($id_country, $id_shop, $table)
    {
        $sql = 'SELECT SQL_SMALL_RESULT id_object, id_category
			FROM `' . _DB_PREFIX_ . bqSQL($table) . '`
			WHERE id_shop = ' . (int) $id_shop . '
			AND active = 1
			AND id_country = ' . (int) $id_country;

        return Db::getInstance()->executeS($sql);
    }

    /**
     * Get description of the product
     *
     * @param int $data
     * @return string $description
     */
    public function getDescription($short_description, $description)
    {
        if ($short_description) {
            $clean_short = strip_tags($short_description);
            return ($clean_short);
        } else {
            $clean_description = strip_tags($description);
            return ($clean_description);
        }
    }

    /**
     * Get link to the product
     *
     * @param int $id_product
     * @param int $id_lang
     * @param int $id_shop
     * @return string $link
     */
    public function getProductLink($id_product, $id_lang, $id_shop)
    {
        $context = Context::getContext();
        $link = $context->link->getProductLink($id_product, null, null, null, $id_lang, $id_shop);

        return $link;
    }

    /**
     * Get link to specific combination of the product
     *
     * @param int $id_product
     * @param int $id_lang
     * @param int $id_shop
     * @return string $link
     */
    public function getProductLinkCombination($id_lang, $id_product_attribute)
    {
        $combination = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            'SELECT SQL_SMALL_RESULT a.id_attribute, agl.public_name, al.name
            FROM `' . _DB_PREFIX_ . 'attribute` a
            LEFT JOIN `' . _DB_PREFIX_ . 'product_attribute_combination` pac ON (a.id_attribute = pac.id_attribute)
            LEFT JOIN `' . _DB_PREFIX_ . 'attribute_lang` al ON (a.id_attribute = al.id_attribute)
            LEFT JOIN `' . _DB_PREFIX_ . 'attribute_group_lang` agl ON (agl.id_attribute_group = a.id_attribute_group )
            WHERE id_product_attribute = ' . (int) $id_product_attribute . '
            AND al.id_lang = ' . (int) $id_lang . '
            AND agl.id_lang = ' . (int) $id_lang
        );

        $link = '#';

        foreach ($combination as $attribute) {
            $link .= '/' . $attribute['id_attribute'] . '-' . $attribute['public_name'] . '-' . $attribute['name'];
            $link = str_replace(' ', '', $link);
        }

        return (Tools::strtolower($link));
    }

    /**
     * Get imagelink of the product
     *
     * @param array $product
     * @return string
     */
    public function getImageLink($product)
    {
        $id_product = $product['id_product'];
        $name = $product['title'];

        $ps_url = Tools::usingSecureMode() && Configuration::get('PS_SSL_ENABLED_EVERYWHERE') ? Tools::getShopDomainSsl(true) : Tools::getShopDomain(true);
        $ps_url .= __PS_BASE_URI__;

        $cover_dir = Product::getCover((int) $id_product);

        if (Configuration::get('PS_REWRITING_SETTINGS') == 0) {
            $img_dir = implode('/', str_split($cover_dir['id_image']));
            return $ps_url . _PS_IMG_ . 'p/' . $img_dir . '/' . $cover_dir['id_image'] . '-' . ImageType::getFormatedName('home') . '.jpg';
        } else {
            return $ps_url . $cover_dir['id_image'] . '-' . ImageType::getFormatedName('large') . '/' . str_replace(' ', '-', $name) . '.jpg';
        }
    }

    /**
     * Get imagelinkcombination of the product
     *
     * @param int $id_product_attribute
     * @param array $product
     * @return string
     */
    public function getImageLinkCombination($id_product_attribute, $product)
    {
        $id_product = $product['id_product'];
        $name = $product['title'];

        $id_images = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            'SELECT SQL_SMALL_RESULT id_image
            FROM `' . _DB_PREFIX_ . 'product_attribute_image`
            WHERE id_product_attribute = ' . (int) $id_product_attribute
        );

        $id_image = $id_images[0]['id_image'];

        if (Configuration::get('PS_REWRITING_SETTINGS') == 0) {
            $img_dir = implode('/', str_split($id_image));
            return _PS_BASE_URL_ . _PS_IMG_ . 'p/' . $img_dir . '/' . $id_image . '-' . ImageType::getFormatedName('home') . '.jpg';
        } else {
            if (!empty($id_image)) {
                return _PS_BASE_URL_ . '/' . $id_image . '-' . ImageType::getFormatedName('large') . '/' . str_replace(' ', '-', $name) . '.jpg';
            } else {
                return ($this->getImageLink($product));
            }
        }
    }

    /**
     * Convert Price in a different currency
     *
     * @param float $amout
     * @param int $id_currency
     * @return foat new amount
     */
    private function convertPriceCurrency($amount, $currency)
    {
        $currency_from = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));

        if ($currency_from->id === $currency['id_currency']) {
            return $amount;
        } else {
            $conversion_rate = ($currency_from->conversion_rate == 0 ? 1 : $currency_from->conversion_rate);
            // Convert amount to default currency (using the old currency rate)
            $amount = $amount / $conversion_rate;
            // Convert to new currency
            $amount *= $currency['conversion_rate'];
        }

        return $amount;
    }

    /**
     * Get Price of the product
     *
     * @param int $data
     * @return float $price
     */
    protected function getPrice($id_product, $id_country, $country_iso, $base_price, $currency)
    {
        if ($country_iso == 'US') {
            $price = $base_price;
        } else {
            $address = new Address();
            $address->id_country = $id_country;
            $address->id_state = 0;
            $address->postcode = 0;
            $context = Context::getContext();

            $tax_manager = TaxManagerFactory::getManager($address, Product::getIdTaxRulesGroupByIdProduct((int) $id_product, $context));
            $product_tax_calculator = $tax_manager->getTaxCalculator();

            $price = $product_tax_calculator->addTaxes($base_price);
        }

        return $this->convertPriceCurrency($price, $currency);
    }

    /**
     * Get Price of the product
     *
     * @param int $id_product
     * @param float $price
     * @return $specificPrice
     */

    protected function getSpecificPrice($id_product, $finalPrice)
    {
        $reduction = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow(
            'SELECT SQL_SMALL_RESULT reduction, reduction_type
            FROM `' . _DB_PREFIX_ . 'specific_price`
            WHERE id_product = ' . (int) $id_product . '
            AND id_group = 0
            AND id_specific_price_rule = 0'
        );

        $specificPrice = $finalPrice;
        if ($reduction['reduction_type'] == 'percentage'){
            $specificPrice -= ($finalPrice * $reduction['reduction']);
        } else {
            $specificPrice -= $reduction['reduction'];
        }
        return $specificPrice;
    }

    /**
     * Get Global international Trade number of the product
     * The GTIN can be EAN13, UPC, JPN
     *
     * @param int $product
     * @return string ean13 or GTIN
     */
    public function getGtin($product)
    {
        if ($product['ean13']) {
            return $product['ean13'];
        } elseif ($product['upc']) {
            return $product['upc'];
        } else {
            return false;
        }
    }

    /**
     * Get Manufacturer Part Number (MPN)
     * MPN was not support natively,
     * This method extract reference of the product
     * MPN will be support in 1.7
     *
     * @param int $id_product
     * @param int $id_product_attribute
     * @return string MPN
     */
    public function getMpn($id_product, $id_product_attribute)
    {
        if (self::MPN_ACTIVE === true) {
            if ($id_product_attribute != 0) {
                $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow(
                    'SELECT ps.product_supplier_reference
                FROM `' . _DB_PREFIX_ . 'product_supplier`ps
                WHERE ps.id_product = ' . (int) $id_product . '
                    AND ps.id_product_attribute = ' . (int) $id_product_attribute
                );
                return $result['product_supplier_reference'];
            } else {
                $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow(
                    'SELECT ps.product_supplier_reference
                FROM `' . _DB_PREFIX_ . 'product_supplier`ps
                WHERE ps.id_product = ' . (int) $id_product
                );

                return $result['product_supplier_reference'];
            }
        } else {
            return '';
        }
    }

    private function getCarriers($id_product, $id_shop)
    {
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->execute(
            'SELECT c.*
            FROM `' . _DB_PREFIX_ . 'product_carrier` pc
            INNER JOIN `' . _DB_PREFIX_ . 'carrier` c
                ON (c.`id_reference` = pc.`id_carrier_reference` AND c.`deleted` = 0)
            WHERE pc.`id_product` = ' . (int) $id_product . '
                AND pc.`id_shop` = ' . (int) $id_shop
        );
    }

    private static function getDeliveryPriceByWeight($id_carrier, $total_weight, $id_zone)
    {
        $sql = 'SELECT d.`price`
            FROM `' . _DB_PREFIX_ . 'delivery` d
            LEFT JOIN `' . _DB_PREFIX_ . 'range_weight` w ON d.`id_range_weight` = w.`id_range_weight`
            WHERE d.`id_zone` = ' . (int) $id_zone . '
                AND ' . (float) $total_weight . ' >= w.`delimiter1`
                AND ' . (float) $total_weight . ' < w.`delimiter2`
                AND d.`id_carrier` = ' . (int) $id_carrier . '
                ' . Carrier::sqlDeliveryRangeShop('range_weight') . '
            ORDER BY w.`delimiter1` ASC';

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
        return $result['price'];
    }

    /**
     * Check delivery prices for a given order
     *
     * @param id_carrier
     * @param floatval $orderTotal Order total to pay
     * @param integer $id_zone Zone id (for customer delivery address)
     * @param integer $id_currency
     * @return float Delivery price
     */
    private static function getDeliveryPriceByPrice($id_carrier, $order_total, $id_zone, $id_currency = null)
    {
        if (!empty($id_currency)) {
            $order_total = Tools::convertPrice($order_total, $id_currency, false);
        }

        $sql = 'SELECT d.`price`
                FROM `' . _DB_PREFIX_ . 'delivery` d
                LEFT JOIN `' . _DB_PREFIX_ . 'range_price` r ON d.`id_range_price` = r.`id_range_price`
                WHERE d.`id_zone` = ' . (int) $id_zone . '
                    AND ' . (float) $order_total . ' >= r.`delimiter1`
                    AND ' . (float) $order_total . ' < r.`delimiter2`
                    AND d.`id_carrier` = ' . (int) $id_carrier . '
                    ' . Carrier::sqlDeliveryRangeShop('range_price') . '
                ORDER BY r.`delimiter1` ASC';

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);

        return isset($result['price']) ? $result['price'] : 0;
    }

    /**
     * Get Shipping cost for the product
     *
     * @param flaot priuce
     * @param int product
     * @param int $id_zone
     * @param int $currency
     * @param int $id_shop
     * @param $data
     * @return float $shippingcost
     */
    public function getShipping($product, $country, $currency, $carriers)
    {
        $carrierClasse = new Carrier();
        $rangeTable = $carrierClasse->getRangeTable();
        $carrier_information = array();

        foreach ($carriers as $carrier) {
            if ($carrier['id_zone'] == $country['id_zone']) {
                $id_carrier = $carrier['id_carrier'];
                $range = Carrier::getDeliveryPriceByRanges($rangeTable, $id_carrier);

                $shipping_cost = $this->getDeliveryPriceByPrice($id_carrier, $product['price'], $country['id_zone'], $currency[$country['id_country']]['id_currency']);
                if ($shipping_cost == 0) {
                    $shipping_cost = $this->getDeliveryPriceByWeight($id_carrier, $product['weight'], $country['id_zone']);
                }
                if ($shipping_cost == 0) {
                    $shipping_cost = 0;
                }

                $shipping_cost = round($shipping_cost, 2);
                $carrier_information[] = array(
                    'id_carrier' => $id_carrier,
                    'name' => $carrier['name'],
                    'price' => $shipping_cost . ' ' . $currency[$country['id_country']]['iso_code'],
                );
            }
        }

        return ($carrier_information);
    }

    /**
     * Get product attribute combination by id_product_attribute
     *
     * @param integer $id_product_attribute
     * @param integer $id_lang Language id
     * @param integer $id_product
     * @return array Product attribute combination by id_product_attribute
     */
    public function getAttributeCombinationsById($id_product_attribute, $id_lang, $id_product)
    {
        if (!Combination::isFeatureActive()) {
            return array();
        }

        $sql = 'SELECT pa.*, product_attribute_shop.*, ag.`id_attribute_group`, ag.`is_color_group`, agl.`name` AS group_name, al.`name` AS attribute_name,
                    a.`id_attribute`
                FROM `' . _DB_PREFIX_ . 'product_attribute` pa
                ' . Shop::addSqlAssociation('product_attribute', 'pa') . '
                LEFT JOIN `' . _DB_PREFIX_ . 'product_attribute_combination` pac ON pac.`id_product_attribute` = pa.`id_product_attribute`
                LEFT JOIN `' . _DB_PREFIX_ . 'attribute` a ON a.`id_attribute` = pac.`id_attribute`
                LEFT JOIN `' . _DB_PREFIX_ . 'attribute_group` ag ON ag.`id_attribute_group` = a.`id_attribute_group`
                LEFT JOIN `' . _DB_PREFIX_ . 'attribute_lang` al ON (a.`id_attribute` = al.`id_attribute` AND al.`id_lang` = ' . (int) $id_lang . ')
                LEFT JOIN `' . _DB_PREFIX_ . 'attribute_group_lang` agl ON (ag.`id_attribute_group` = agl.`id_attribute_group` AND agl.`id_lang` = ' . (int) $id_lang . ')
                WHERE pa.`id_product` = ' . (int) $id_product . '
                AND pa.`id_product_attribute` = ' . (int) $id_product_attribute . '
                GROUP BY pa.`id_product_attribute`, ag.`id_attribute_group`
                ORDER BY pa.`id_product_attribute`';

        $res = Db::getInstance()->executeS($sql);

        //Get quantity of each variations
        foreach ($res as $key => $row) {
            $cache_key = $row['id_product'] . '_' . $row['id_product_attribute'] . '_quantity';

            if (!Cache::isStored($cache_key)) {
                Cache::store(
                    $cache_key, StockAvailable::getQuantityAvailableByProduct($row['id_product'], $row['id_product_attribute'])
                );
            }

            $res[$key]['quantity'] = Cache::retrieve($cache_key);
        }

        return $res;
    }

    public function getProductAttribute($id_product)
    {
        $sql = 'SELECT `id_product_attribute`
            FROM `' . _DB_PREFIX_ . 'product_attribute`
            WHERE `id_product` =' . (int) $id_product;

        $res = Db::getInstance()->executeS($sql);

        return $res;
    }

    /*
     * Get the avability for the feed depends of the quantity
     *
     * @param $quantity
     */

    abstract protected function getAvailability($quantity);
}
