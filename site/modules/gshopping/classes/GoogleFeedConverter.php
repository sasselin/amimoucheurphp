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
 *   This is a PHP class for generating export feed fopr google.
 */
class GoogleFeedConverter extends ExportClass
{

    /**
     * Get the google category who correspond to the google taxonomy
     * @param int $id_product
     * @return string category google
     */
    public function getCategory($id_product)
    {
        $google_category = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
            'SELECT SQL_SMALL_RESULT mgc.google_category
            FROM `' . _DB_PREFIX_ . 'module_gshopping_category` mgc
            LEFT JOIN `' . _DB_PREFIX_ . 'product` p ON (p.`id_category_default` = mgc.`id_category`)
            WHERE p.id_product = ' . (int) $id_product);

        $clean_google_category = explode('>', $google_category);
        $flag = trim(end($clean_google_category));

        if (empty($flag)) {
            array_pop($clean_google_category);
            $clean_google_category = implode($clean_google_category);
            trim($clean_google_category);
        } else {
            $clean_google_category = trim($google_category);
        }

        return $clean_google_category;
    }

    /**
     * Get the google category who correspond to the google taxonomy
     * @param int $product_quantity
     * @return string
     */
    public function getAvailability($product_quantity)
    {
        if ($product_quantity !== 0) {
            return 'in stock';
        } else {
            return 'out of stock';
        }
    }

    /**
     * Get the price adapt to google
     * @param int $id_product
     * @param sting country_id
     * @return float price
     */
    public function getGooglePrice($id_product, $id_country, $country_iso, $base_price, $currency)
    {
        $price = ExportClass::getPrice($id_product, $id_country, $country_iso, $base_price, $currency);
        $googlePrice = round($price, 2) . ' ' . $currency['iso_code'];

        return $googlePrice;
    }

    public function getAdultWarning($google_category)
    {
        $lexicon = array(
            'Adult', // Fr
            'Mature', //En
        );

        foreach ($lexicon as $cat) {
            if (trim($google_category) === $cat) {
                return 'TRUE';
            } else {
                return 'FALSE';
            }

        }
    }

    /**
     * Get the Specific price adapt to google
     * @param int $id_product
     * @param float price
     *  @param array currency
     * @return float specificPrice
     */

    public function getGoogleSpecificPrice($id_product, $price, $currency)
    {
        $specificPrice = ExportClass::getSpecificPrice($id_product, $price);
        $googleSpecificPrice = round($specificPrice, 2) . ' ' . $currency['iso_code'];

        return $googleSpecificPrice;
    }

    /**
     * Get the specific information for the apparel
     * @param int $id_product
     * @return string category google
     */
    private function typeValue($id_object, $id_parameter)
    {
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
            'SELECT SQL_SMALL_RESULT mga.type
            FROM `' . _DB_PREFIX_ . 'module_gshopping_attributes` mga
            WHERE mga.id_object = ' . (int) $id_object . '
            AND mga.id_parameter =  ' . (int) $id_parameter
        );
    }

    private function featureSimpleValue($id_product, $id_object, $id_lang, $id_parameter)
    {
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
            'SELECT SQL_SMALL_RESULT fvl.value
            FROM `' . _DB_PREFIX_ . 'feature_value_lang` fvl
            LEFT JOIN `' . _DB_PREFIX_ . 'feature_product` fp ON (fvl.`id_feature_value` = fp.`id_feature_value`)
            LEFT JOIN `' . _DB_PREFIX_ . 'module_gshopping_attributes` mga ON (mga.`attribute_value` = fp.`id_feature`)
            WHERE mga.id_object = ' . (int) $id_object . '
            AND fp.id_product = ' . (int) $id_product . '
            AND mga.id_parameter = ' . (int) $id_parameter . '
            AND fvl.id_lang =' . (int) $id_lang
        );
    }

    private function attributeSimpleValue($id_product_attribute, $id_object, $id_lang, $id_parameter)
    {
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
            'SELECT al.name FROM `' . _DB_PREFIX_ . 'attribute` a
            JOIN `' . _DB_PREFIX_ . 'attribute_lang` al ON (a.id_attribute = al.id_attribute AND al.id_lang = '
            . (int) $id_lang . ')
            WHERE a.id_attribute_group = (
              SELECT mga.attribute_value
              FROM `' . _DB_PREFIX_ . 'module_gshopping_attributes` mga
              WHERE mga.id_object = ' . (int) $id_object . '
              AND mga.id_parameter = ' . (int) $id_parameter . '
            )
            AND a.id_attribute IN (
              SELECT pa.id_attribute
              FROM `' . _DB_PREFIX_ . 'product_attribute_combination` pa
              WHERE pa.id_product_attribute = ' . (int) $id_product_attribute . '
            )'
        );
    }

    /**
     * Get the specific information for the apparel
     * @param int $id_product
     * @return string category google
     */
    public function getGenderProduct($id_product, $id_object, $combinaison = false)
    {
        if ($combinaison === true) {
            $feature = $this->typeValue($id_object, 1);
        } else {
            $feature = true;
        }

        if ($feature == true) {
            $gender = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
                'SELECT SQL_SMALL_RESULT mga.type_attribute
                FROM `' . _DB_PREFIX_ . 'module_gshopping_attributes` mga
                LEFT JOIN `' . _DB_PREFIX_ . 'feature_product` fp ON (fp.`id_feature_value` = mga.`attribute_value`)
                WHERE mga.id_object = ' . (int) $id_object . '
                AND fp.id_product = ' . (int) $id_product . '
                AND (mga.id_parameter = 1 OR mga.id_parameter = 2 OR mga.id_parameter = 3)'
            );
        } else {
            $gender = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
                'SELECT SQL_SMALL_RESULT mga.type_attribute
                FROM `' . _DB_PREFIX_ . 'module_gshopping_attributes` mga
                LEFT JOIN `' . _DB_PREFIX_ . 'product_attribute_combination` pac ON (mga.attribute_value = pac.id_attribute)
                LEFT JOIN `' . _DB_PREFIX_ . 'product_attribute` pa ON (pa.id_product_attribute = pac.id_product_attribute)
                WHERE pa.id_product = ' . (int) $id_product . '
                AND mga.id_object =' . (int) $id_object . '
                AND (mga.id_parameter = 1 OR mga.id_parameter = 2 OR mga.id_parameter = 3)'
            );
        }
        return $gender;
    }

    public function getAgeGroupProduct($id_product, $id_object, $combinaison = false)
    {
        if ($combinaison === true) {
            $feature = $this->typeValue($id_object, 4);
        } else {
            $feature = true;
        }

        if ($feature == true) {
            $age_group = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
                'SELECT SQL_SMALL_RESULT mga.type_attribute
                FROM `' . _DB_PREFIX_ . 'module_gshopping_attributes` mga
                LEFT JOIN `' . _DB_PREFIX_ . 'feature_product` fp ON (fp.`id_feature_value` = mga.`attribute_value`)
                WHERE mga.id_object = ' . (int) $id_object . '
                AND fp.id_product = ' . (int) $id_product . '
                AND (mga.id_parameter = 4 OR
                    mga.id_parameter = 5 OR
                    mga.id_parameter = 6 OR
                    mga.id_parameter = 7 OR
                    mga.id_parameter = 8)'
            );
        } else {
            $age_group = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
                'SELECT SQL_SMALL_RESULT mga.type_attribute
                FROM `' . _DB_PREFIX_ . 'module_gshopping_attributes` mga
                LEFT JOIN `' . _DB_PREFIX_ . 'product_attribute_combination` pac ON (mga.attribute_value = pac.id_attribute)
                LEFT JOIN `' . _DB_PREFIX_ . 'product_attribute` pa ON (pa.id_product_attribute = pac.id_product_attribute)
                WHERE pa.id_product = ' . (int) $id_product . '
                AND mga.id_object =' . (int) $id_object . '
                AND (mga.id_parameter = 4 OR
                    mga.id_parameter = 5 OR
                    mga.id_parameter = 6 OR
                    mga.id_parameter = 7 OR
                    mga.id_parameter = 8)'
            );
        }
        return $age_group;
    }

    public function getColor($id_product, $id_object, $id_lang, $id_product_attribute = false, $combinaison = false)
    {
        if ($combinaison === true) {
            $feature = $this->typeValue($id_object, 9);
        } else {
            $feature = true;
        }

        if ($feature == true) {
            $color = $this->featureSimpleValue($id_product, $id_object, $id_lang, 9);
        } else {
            $color = $this->featureSimpleValue($id_product, $id_object, $id_lang, 9);
        }

        return $color;
    }

    public function getSize($id_product, $id_object, $id_lang, $id_product_attribute = false, $combinaison = false)
    {
        if ($combinaison === true) {
            $feature = $this->typeValue($id_object, 10);
        } else {
            $feature = true;
        }

        if ($feature == true) {
            $size = $this->featureSimpleValue($id_product, $id_object, $id_lang, 10);
        } else {
            $size = $this->attributeSimpleValue($id_product_attribute, $id_object, $id_lang, 10);
        }

        return $size;
    }

    public function getMaterial($id_product, $id_object, $id_lang, $id_product_attribute = false, $combinaison = false)
    {
        if ($combinaison === true) {
            $feature = $this->typeValue($id_object, 11);
        } else {
            $feature = true;
        }

        if ($feature == true) {
            $material = $this->featureSimpleValue($id_product, $id_object, $id_lang, 11);
        } else {
            $material = $this->attributeSimpleValue($id_product_attribute, $id_object, $id_lang, 11);
        }

        return $material;
    }

    public function getPattern($id_product, $id_object, $id_lang, $id_product_attribute = false, $combinaison = false)
    {
        if ($combinaison == true) {
            $feature = $this->typeValue($id_object, 12);
        } else {
            $feature = true;
        }

        if ($feature == true) {
            $pattern = $this->featureSimpleValue($id_product, $id_object, $id_lang, 12);
        } else {
            $pattern = $this->attributeSimpleValue($id_product_attribute, $id_object, $id_lang, 12);
        }

        return $pattern;
    }
}
