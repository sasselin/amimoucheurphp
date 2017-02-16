<?php
if (!defined('_PS_VERSION_'))
    exit;
abstract class ExportClassOverride extends ExportClass
{
    public function __construct($id_category, $id_lang)
    {
        // $this->productRes = Db::getInstance()->ExecuteS(
        //     'SELECT DISTINCT(p.id_product), p.id_category_default, pl.name title, pl.description_short, pl.description,
        //     pl.link_rewrite, p.weight product_weight, IF(pa.price IS NULL, p.price, (p.price+pa.price)) as price, p.ecotax, p.upc, m.name brand, i.id_image, l.id_lang,
        //     cl.link_rewrite category_link, cl.name category_name, p.ean13, p.id_tax_rules_group, p.condition, p.quantity, p.weight
        //     FROM ' . _DB_PREFIX_ . 'product p
        //     LEFT JOIN ' . _DB_PREFIX_ . 'product_lang pl ON (pl.id_product = p.id_product)
        //     LEFT JOIN ' . _DB_PREFIX_ . 'lang l ON (l.id_lang = pl.id_lang)
        //     LEFT JOIN ' . _DB_PREFIX_ . 'manufacturer m ON (m.id_manufacturer = p.id_manufacturer)
        //     LEFT JOIN ' . _DB_PREFIX_ . 'image i ON (i.id_image = p.id_product)
        //     LEFT JOIN ' . _DB_PREFIX_ . 'category_lang cl ON (cl.id_category = p.id_category_default AND cl.id_lang = l.id_lang)
        //     LEFT JOIN ' . _DB_PREFIX_ . 'product_attribute pa ON (pa.id_product = p.id_product)
        //     WHERE p.price > 0 AND p.active = 1 AND l.id_lang = ' . (int) $id_lang . '
        //     AND p.id_category_default = ' . (int) $id_category
        //     , false);
    }
}
