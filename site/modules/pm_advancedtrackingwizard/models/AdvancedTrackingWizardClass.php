<?php
/**
 * Advanced Tracking Wizard
 *
 * @author    Presta-Module.com <support@presta-module.com> - http://www.presta-module.com
 * @copyright Presta-Module 2017 - http://www.presta-module.com
 * @license   Commercial
 *
 *           ____     __  __
 *          |  _ \   |  \/  |
 *          | |_) |  | |\/| |
 *          |  __/   | |  | |
 *          |_|      |_|  |_|
 */

class AdvancedTrackingWizardClass extends ObjectModel
{
    public $id;
    public $tracking_code;
    public $internal_name;
    public $active = 1;
    public $page;
    public $is_extension;
    protected $tables = array('pm_advancedtrackingwizard');
    protected $fieldsRequired = array('active', 'internal_name');
    protected $fieldsSize = array('active' => 1,' internal_name' => 64);
    protected $fieldsValidate = array('active' => 'isBool', 'internal_name' => 'isGenericName', 'is_extension' => 'isBool');
    protected $table = 'pm_advancedtrackingwizard';
    protected $identifier = 'id_tracking';
    protected $fieldsRequiredLang = array();
    protected $fieldsSizeLang = array();
    protected $fieldsValidateLang = array();
    public static $valid_page = array();
    public static $definition = array(
        'table' => 'pm_advancedtrackingwizard',
        'primary' => 'id_tracking',
        'multishop' => true,
        'fields' => array(
            'internal_name' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'tracking_code' => array('type' => self::TYPE_HTML, 'validate' => 'isAnything'),
            'page' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'active' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'is_extension' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
        ),
    );
    public function __construct($id = null, $id_lang = null, $id_shop = null)
    {
        if (version_compare(_PS_VERSION_, '1.5.2.0', '<=') && class_exists("ShopPrestaModule")) {
            ShopPrestaModule::PrestaModule_setAssoTable(self::$definition['table']);
        } else {
            Shop::addTableAssociation(self::$definition['table'], array('type' => 'shop'));
        }
        parent::__construct($id, $id_lang, $id_shop);
    }
    public function getFields()
    {
        $fields = array();
        parent::validateFields();
        if (isset($this->id)) {
            $fields['id_tracking'] = (int) $this->id;
        }
        $fields['internal_name'] = pSQL($this->internal_name);
        $fields['tracking_code'] = pSQL($this->tracking_code, true);
        $fields['page'] = pSQL($this->page);
        $fields['active'] = (int) $this->active;
        $fields['is_extension'] = (int) $this->is_extension;
        return $fields;
    }
    public function save($nullValues = false, $autodate = true)
    {
        $installed_ext = $this->getInstalledExt();
        foreach ($installed_ext as $k => $v) {
            if ($this->id == $k && $this->internal_name == $v) {
                if (parent::save()) {
                    $this->updateCategory(Tools::getValue('categories'));
                    $this->updateProduct(Tools::getValue('products'));
                    return true;
                }
            }
        }
        if (in_array($this->internal_name, $installed_ext)) {
            return false;
        }
        if (parent::save()) {
            $this->updateCategory(Tools::getValue('categories'));
            $this->updateProduct(Tools::getValue('products'));
            return true;
        }
        return false;
    }
    public function updateCategory($categories)
    {
        $this->cleanCategories();
        if ($this->page == 'category' && $categories && sizeof($categories)) {
            $this->addCategories($categories);
        }
    }
    public function cleanCategories()
    {
        Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'pm_advancedtrackingwizard_category` WHERE `'.$this->identifier.'` = '.(int) $this->id);
    }
    public function addCategories($categories)
    {
        foreach ($categories as $category) {
            if (!trim($category)) {
                continue;
            }
            $row = array($this->identifier => (int) $this->id, 'id_category' => (int) $category);
            AdvancedTrackingCoreClass::autoExecute('pm_advancedtrackingwizard_category', $row);
        }
    }
    public function updateProduct($products)
    {
        $this->cleanProducts();
        if ($this->page == 'product' && $products && sizeof($products)) {
            $this->addProducts($products);
        }
    }
    public function cleanProducts()
    {
        Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'pm_advancedtrackingwizard_product` WHERE `'.$this->identifier.'` = '.(int) $this->id);
    }
    public function addProducts($products)
    {
        foreach ($products as $product) {
            if (!trim($product)) {
                continue;
            }
            $row = array($this->identifier => (int) $this->id, 'id_product' => (int) $product);
            AdvancedTrackingCoreClass::autoExecute('pm_advancedtrackingwizard_product', $row);
        }
    }
    public function delete()
    {
        $this->cleanCategories();
        $this->cleanProducts();
        $is_ext = extensionTrackerClass::getExtensionId($this->id);
        if (AdvancedTrackingCoreClass::_isFilledArray($is_ext)) {
            extensionTrackerClass::delete($this->id);
        }
        return parent::delete();
    }
    protected function getInstalledExt($multishop = true)
    {
        $result = Db::getInstance()->ExecuteS(
            'SELECT atw.`id_tracking`, atw.`internal_name`
            FROM `'._DB_PREFIX_.'pm_advancedtrackingwizard` atw
            '.($multishop ? AdvancedTrackingCoreClass::addSqlAssociation('pm_advancedtrackingwizard', 'atw', 'id_tracking'):'').'
            WHERE `is_extension` = 1'
        );
        $return = array();
        foreach ($result as $row) {
            $return[$row['id_tracking']] = $row['internal_name'];
        }
        return $return;
    }
    public static function getAssignedCategories($id, $id_lang)
    {
        $return = array();
        $result = Db::getInstance()->ExecuteS(
            'SELECT atwc.`id_category`, cl.`name`
            FROM `'._DB_PREFIX_.'pm_advancedtrackingwizard_category` atwc
            LEFT JOIN `'._DB_PREFIX_.'category_lang` cl ON (atwc.`id_category` = cl.`id_category`)
            WHERE atwc.`id_tracking` = '.(int)$id.'
            AND cl.`id_lang` = '.(int)$id_lang
        );
        if ($result) {
            foreach ($result as $row) {
                $return[$row['id_category']] = $row['name'];
            }
        }
        return $return;
    }
    public static function getAssignedProducts($id, $id_lang)
    {
        $return = array();
        $result = Db::getInstance()->ExecuteS(
            'SELECT atwp.`id_product`, pl.`name`
            FROM `'._DB_PREFIX_.'pm_advancedtrackingwizard_product` atwp
            LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (atwp.`id_product` = pl.`id_product`)
            WHERE atwp.`id_tracking` = '.(int)$id.'
            AND pl.`id_lang` = '.(int)$id_lang
        );
        if ($result) {
            foreach ($result as $row) {
                $return[$row['id_product']] = $row['name'];
            }
        }
        return $return;
    }
    public static function getTrackers($multishop = true)
    {
        $result = Db::getInstance()->ExecuteS(
            'SELECT atw.*, atw.`id_tracking`
            FROM `' . _DB_PREFIX_ . 'pm_advancedtrackingwizard` atw
            '.($multishop ? AdvancedTrackingCoreClass::addSqlAssociation('pm_advancedtrackingwizard', 'atw', 'id_tracking'):'').'
            WHERE 1
            GROUP BY atw.`id_tracking`'
        );
        return $result;
    }
    public static function getActiveTrackers($page, $include_categories = false, $include_products = false, $multishop = false, $exclude_all = false)
    {
        if ($include_categories) {
            if (version_compare(_PS_VERSION_, '1.5.6.0', '>=') && method_exists(Context::getContext()->controller, 'getCategory') && Validate::isLoadedObject(Context::getContext()->controller->getCategory())) {
                $cur_id_category = (int)Context::getContext()->controller->getCategory()->id;
            } else {
                $cur_id_category = Tools::getValue('id_category', false);
            }
        }
        if ($include_products) {
            if (version_compare(_PS_VERSION_, '1.5.6.0', '>=') && method_exists(Context::getContext()->controller, 'getProduct') && Validate::isLoadedObject(Context::getContext()->controller->getProduct())) {
                $cur_id_product = (int)Context::getContext()->controller->getProduct()->id;
            } else {
                $cur_id_product = Tools::getValue('id_product', false);
            }
        }
        $result = Db::getInstance()->ExecuteS(
            'SELECT atw.`id_tracking`, atw.`tracking_code`,atw.`is_extension`
            FROM `' . _DB_PREFIX_ . 'pm_advancedtrackingwizard` atw
            LEFT JOIN `' . _DB_PREFIX_ . 'pm_advancedtrackingwizard_category` atwc ON (atwc.`id_tracking` = atw.`id_tracking`)
            LEFT JOIN `' . _DB_PREFIX_ . 'pm_advancedtrackingwizard_product` atwp ON (atwp.`id_tracking` = atw.`id_tracking`)
            '.($multishop ? AdvancedTrackingCoreClass::addSqlAssociation('pm_advancedtrackingwizard', 'atw', 'id_tracking'):'').'
            WHERE (atw.`page` = "' . pSQL($page) . '"' . (!$exclude_all ? ' OR atw.`page`="all"' : ' OR atw.`page`="all" AND atw.`is_extension`="1"') . ')
            ' . ($include_categories ? 'AND (atwc.`id_category` = ' . (int) $cur_id_category . ' OR ISNULL(atwc.`id_category`))' : '') . '
            ' . ($include_products ? 'AND (atwp.`id_product` = ' . (int) $cur_id_product . ' OR ISNULL(atwp.`id_product`))' : '') . '
            AND atw.`active` = 1'
        );
        return $result;
    }
}
