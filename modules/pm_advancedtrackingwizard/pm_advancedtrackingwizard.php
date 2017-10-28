<?php
/**
 * Advanced Tracking Wizard
 *
 * @author    Presta-Module.com <support@presta-module.com> - http://www.presta-module.com
 * @copyright Presta-Module 2017 - http://www.presta-module.com
 * @license   Commercial
 * @version   1.6.0
 *
 *           ____     __  __
 *          |  _ \   |  \/  |
 *          | |_) |  | |\/| |
 *          |  __/   | |  | |
 *          |_|      |_|  |_|
 */

require_once(_PS_ROOT_DIR_.'/modules/pm_advancedtrackingwizard/models/ShopOverrided.php');
require_once(_PS_ROOT_DIR_.'/modules/pm_advancedtrackingwizard/models/AdvancedTrackingWizardClass.php');
require_once(_PS_ROOT_DIR_.'/modules/pm_advancedtrackingwizard/models/extensionTrackerClass.php');
require_once(_PS_ROOT_DIR_.'/modules/pm_advancedtrackingwizard/AdvancedTrackingCoreClass.php');
class PM_AdvancedTrackingWizard extends AdvancedTrackingCoreClass
{
    protected $_valid_page_labels = false;
    public static $_valid_hooks = array();
    public static $_valid_page = array('index', 'category', 'product', 'cms', 'manufacturer', 'supplier', 'cart', 'order', 'order-confirmation', 'all');
    protected $_tracking_vars_label = false;
    public static $_tracking_vars = array(
        'category'  => array('id_category', 'category_name'),
        'product'   => array('id_product', 'product_ref', 'product_name', 'product_price'),
        'customer'  => array('id_customer', 'email', 'lastname', 'firstname', 'postcode', 'city', 'iso_country', 'id_lang', 'id_gender', 'birthday', 'is_new_customer', 'currency'),
        'cart'      => array('id_cart', 'id_products_cart', 'qty_products_cart', 'price_products_cart', 'id_categories_cart', 'quantity_cart', 'currency', 'voucher', 'voucher_list'),
        'order'     => array('id_order', 'total', 'shipping', 'taxe_only', 'payment', 'commission', 'payment_module'),
        'others'    => array('gtm_trans_products'));
    protected $_page_related_vars = array(
        'index'     => array('customer'),
        'category'  => array('category', 'customer'),
        'product'   => array('category', 'product', 'customer'),
        'cms'       => array('customer'),
        'manufacturer' => array('customer'),
        'supplier'  => array('customer'),
        'cart'  => array('customer', 'cart', 'order', 'others'),
        'order' => array('customer', 'cart', 'order', 'others'),
        'order-confirmation' => array('customer', 'cart', 'order', 'others'),
        'customer-form' => array('cart'),
        'customer-created' => array('customer', 'cart'),
        'all'   => array('customer', 'cart') );
    protected $_var_to_config = false;
    protected $_tracking_group_vars_label = false;
    protected $_files_to_check = array();
    protected $_require_maintenance = true;
    protected $_copyright_link = array(
        'link'  => '',
        'img'   => '//www.presta-module.com/img/logo-module.JPG'
    );
    protected $_support_link = false;
    const INSTALL_SQL_FILE = 'sql/install.sql';
    const UNINSTALL_SQL_FILE = 'sql/uninstall.sql';
    public static $_module_prefix = 'ATW';
    protected $_css_js_to_load = array('core', 'jquerytiptip', 'jgrowl', 'datatables', 'codemirrorcore', 'codemirrormixed', 'collision', 'multiselect', 'chosen');
    public function __construct()
    {
        $this->author = 'Presta-Module';
        $this->need_instance = 0;
        $this->name = 'pm_advancedtrackingwizard';
        $this->tab = 'market_place';
        $this->version = '1.6.0';
        $this->module_key = 'a95760d1a577bc92ccaa111a9aa7d997';
        parent::__construct();
        if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
            self::$_valid_hooks = array('displayBeforeBodyClosingTag', 'displayCustomerAccountForm');
        } else {
            self::$_valid_hooks = array('displayFooter', 'displayCustomerAccountForm');
        }
        if ($this->_onBackOffice()) {
            $this->displayName = $this->l('Advanced Tracking Wizard');
            $this->description = $this->l('Add and manage your partners tracking codes with ease. Measure the effectiveness of each campaign and platform.');
            $this->_tracking_group_vars_label = array('category' => $this->l('Category'), 'product' => $this->l('Product'), 'customer' => $this->l('Customer'), 'cart' => $this->l('Cart'), 'order' => $this->l('Order'), 'others' => $this->l('Others') );
            $this->_tracking_vars_label = array(
                'category' => array(
                    'id_category' => $this->l('Category ID'),
                    'category_name' => $this->l('Category Name')
                ),
                'product' => array(
                    'id_product' => $this->l('Product ID'),
                    'product_ref' => $this->l('Product Ref'),
                    'product_name' => $this->l('Product Name'),
                    'product_price' => $this->l('Product Price'),
                ),
                'customer' => array(
                    'id_customer' => $this->l('Customer ID'),
                    'email' => $this->l('Email'),
                    'lastname' => $this->l('Last Name'),
                    'firstname' => $this->l('First Name'),
                    'postcode' => $this->l('Post Code'),
                    'city' => $this->l('City'),
                    'iso_country' => $this->l('Country'),
                    'id_lang' => $this->l('Lang ID'),
                    'id_gender' => $this->l('Gender ID'),
                    'birthday' => $this->l('Birthday'),
                    'is_new_customer' => $this->l('New customer ?'),
                    'currency' => $this->l('Currency'),
                ),
                'cart' => array(
                    'id_cart' => $this->l('Cart ID'),
                    'id_products_cart' => $this->l('ID Products on Cart'),
                    'qty_products_cart' => $this->l('Quantity of each product'),
                    'price_products_cart' => $this->l('Price of each product'),
                    'id_categories_cart' => $this->l('ID categories on Cart'),
                    'quantity_cart' => $this->l('Quantity Products on Cart'),
                    'currency' => $this->l('Currency'),
                    'voucher' => $this->l('Voucher'),
                    'voucher_list' => $this->l('Vouchers list'),
                ),
                'order' => array(
                    'id_order' => $this->l('Order ID'),
                    'total' => $this->l('Total'),
                    'shipping' => $this->l('Shipping only'),
                    'payment' => $this->l('Payment Method'),
                    'payment_module' => $this->l('Payment module name'),
                    'commission' => $this->l('Commission'),
                    'taxe_only' => $this->l('Taxe only'),
                ),
                'others' => array(
                    'gtm_trans_products' => $this->l('Google Tag Manager - Product List'),
                )
            );
            $this->_var_to_config = array('total', 'id_products_cart', 'id_categories_cart', 'qty_products_cart', 'product_price', 'price_products_cart', 'shipping', 'currency', 'voucher_list', 'commission');
            $this->_valid_page_labels = array(
                'index' => $this->l('Home'),
                'category' => $this->l('Category'),
                'product' => $this->l('Product'),
                'cart' => $this->l('Cart'),
                'order' => $this->l('Pre-Payment '),
                'order-confirmation' => $this->l('Post-Payment'),
                'customer-form' => $this->l('Customer creation form'),
                'customer-created' => $this->l('Customer account created'),
                'cms' => $this->l('CMS'),
                'manufacturer' => $this->l('Manufacturer '),
                'supplier' => $this->l('Supplier'),
                'all' => $this->l('All')
            );
            $doc_url_tab = array();
            $doc_url_tab['fr'] = '#/fr/advancedtrackingwizard/';
            $doc_url_tab['en'] = '#/en/advancedtrackingwizard/';
            $doc_url = $doc_url_tab['en'];
            if ($this->_iso_lang == 'fr') {
                $doc_url = $doc_url_tab['fr'];
            }
            $forum_url_tab = array();
            $forum_url_tab['fr'] = 'http://www.prestashop.com/forums/topic/143218-modulepm-advanced-tracking-wizard-editeur-de-codes-de-tracking/';
            $forum_url_tab['en'] = 'http://www.prestashop.com/forums/topic/143202-modulepm-advanced-tracking-wizard/';
            $forum_url = $forum_url_tab['en'];
            if ($this->_iso_lang == 'fr') {
                $forum_url = $forum_url_tab['fr'];
            }
            $this->_support_link = array(
                array('link' => $forum_url, 'target' => '_blank', 'label' => $this->l('Forum topic')),
                
                array('link' => 'http://addons.prestashop.com/contact-community.php?id_product=3747', 'target' => '_blank', 'label' => $this->l('Support contact')),
            );
        }
    }
    public function install()
    {
        if (!$this->installDb() || !parent::install()) {
            return false;
        }
        foreach (self::$_valid_hooks as $hook_name) {
            if (!$this->registerHook($hook_name)) {
                return false;
            }
        }
        $this->checkIfModuleIsUpdate(true, false);
        Configuration::updateValue('PM_'.self::$_module_prefix.'_MAINTENANCE', 0);
        return true;
    }
    public function checkIfModuleIsUpdate($updateDb = false, $displayConfirm = true)
    {
        $this->registerHook('displayBackOfficeHeader');
        $isUpdate = true;
        if (! $updateDb && $this->version != Configuration::get('PM_'.self::$_module_prefix.'_LAST_VERSION', false)) {
            return false;
        }
        if ($updateDb) {
            Configuration::updateValue('PM_'.self::$_module_prefix.'_LAST_VERSION', $this->version);
            $this->installDb();
            $this->updateDb();
            foreach (self::$_valid_hooks as $hook_name) {
                if (!$this->registerHook($hook_name)) {
                    $isUpdate = false;
                    break;
                }
            }
            if ($isUpdate && $displayConfirm) {
                $this->context->controller->confirmations[] = $this->l('Module updated successfully');
            } elseif (!$isUpdate) {
                $this->context->controller->confirmations[] = $this->l('Module update fail');
            }
        }
        return $isUpdate;
    }
    public function installDB()
    {
        if (!file_exists(dirname(__FILE__).'/'.self::INSTALL_SQL_FILE)) {
            return false;
        } elseif (!$sql = Tools::file_get_contents(dirname(__FILE__).'/'.self::INSTALL_SQL_FILE)) {
            return false;
        }
        $sql = str_replace('PREFIX_', _DB_PREFIX_, $sql);
        $sql = str_replace('MYSQL_ENGINE', _MYSQL_ENGINE_, $sql);
        $sql = preg_split("/;\s*[\r\n]+/", $sql);
        foreach ($sql as $query) {
            if (!Db::getInstance()->Execute(trim($query))) {
                return false;
            }
        }
        return true;
    }
    public function updateDb()
    {
        if (! $this->columnExists('pm_advancedtrackingwizard_extensions', 'extension_name', false, ' varchar(256) NOT NULL', 'name')) {
            Db::getInstance()->Execute('ALTER TABLE `' . _DB_PREFIX_ .'pm_advancedtrackingwizard_extensions` CHANGE `name` `extension_name` VARCHAR( 256 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL');
        }
    }
    private function columnExists($table, $column, $createIfNotExist = false, $type = false, $insertAfter = false)
    {
        $resultset = Db::getInstance()->ExecuteS("SHOW COLUMNS FROM `"._DB_PREFIX_.$table."`");
        foreach ($resultset as $row) {
            if ($row ['Field'] == $column) {
                return true;
            }
        }
        if ($createIfNotExist && Db::getInstance()->Execute('ALTER TABLE `'._DB_PREFIX_.$table.'` ADD `'.$column.'` '.$type.' '.($insertAfter ? ' AFTER `'.$insertAfter.'`' : '').'')) {
            return true;
        }
        return false;
    }
    public function getContent()
    {
        if (Tools::getValue('makeUpdate')) {
            $this->checkIfModuleIsUpdate(true);
        }
        $moduleIsUpToDate = $this->checkIfModuleIsUpdate(false);
        if ($moduleIsUpToDate) {
            extensionTrackerClass::InitExtensionsFromDisk();
            if (Shop::getContext() == Shop::CONTEXT_SHOP) {
                $this->_preProcess();
                $this->_postProcess();
            }
        }
        $vars = array(
            'module_display_name' => $this->displayName,
            'module_is_up_to_date' => $moduleIsUpToDate,
            'context_is_shop' => (Shop::getContext() == Shop::CONTEXT_SHOP),
            'css_js_assets' => $this->_loadCssJsLibraries(),
            'rating_invite' => $this->_showRating(true),
            'parent_content' => parent::getContent(),
        );
        if ($moduleIsUpToDate) {
            if (Shop::getContext() == Shop::CONTEXT_SHOP) {
                $vars['tracking_table'] = $this->displayTrackingTable();
            }
        }
        return $this->fetchTemplate('module/content.tpl', $vars);
    }
    protected function _postProcess()
    {
        parent::_postProcess();
        if (Tools::isSubmit('load_tracker')) {
            $this->_cleanOutput();
            $this->_html .= $this->displayFormExtensionConfig(Tools::getValue('load'));
            $this->_echoOutput(true);
        } elseif (Tools::getIsset('submit_tracker')) {
            $return = '';
            $this->postProcessAddTracker();
        } elseif ($extension_name = Tools::getValue('postExtensionConfigurationForm')) {
            $this->_cleanOutput();
            $objExtension = extensionTrackerClass::loadExtension($extension_name);
            $postReturn = $objExtension->_postConfigurationForm($_POST);
            if ($postReturn) {
                $this->_html .= '<script type="text/javascript">
                                        parent.parent.show_info("' . $this->l('Saved') . '");
                                        parent.parent.reloadPanel("displayTrackingTable");
                                        parent.parent.closeDialogIframe();
                                    </script>';
            } else {
                $this->_html .= '<script type="text/javascript">parent.show_error("' . $this->l('You already have this code installed! Please delete it first.') . '")</script>';
            }
            $this->_echoOutput(true);
        } elseif (Tools::getValue('deleteTracking') && Tools::getValue('id_tracking')) {
            $return = '';
            $Obj = new AdvancedTrackingWizardClass(Tools::getValue('id_tracking'));
            if ($Obj->delete()) {
                $return .= 'reloadPanel("displayTrackingTable");show_info("' . $this->l('Tracker has been deleted.') . '");';
            } else {
                $return .= 'show_error("' . $this->l('Error while deleting tracking') . '");';
            }
            self::_cleanBuffer();
            echo $return;
            die();
        } elseif (Tools::getValue('activeTracking') && Tools::getValue('id_tracking')) {
            $return = '';
            $Obj = new AdvancedTrackingWizardClass(Tools::getValue('id_tracking'));
            $Obj->active = ($Obj->active ? 0 : 1);
            if ($Obj->save()) {
                $return .= '$("#imgActiveTracking' . $Obj->id . '").attr("src","' . $this->_path . 'views/img/module_' . ($Obj->active ? 'install' : 'disabled') . '.png").attr("alt","' . ($Obj->active ? 'Active' : 'Inactive') . '").attr("title","' . ($Obj->active ? 'Active' : 'Inactive') . '");';
                $return .= '$("#spanActiveTracking' . $Obj->id . '").parents("td").children("span").html("' . ($Obj->active ? '1' : '0') . '");';
                $return .= 'show_info("' . $this->l('Saved') . '");';
            } else {
                $return .= 'show_info("' . $this->l('Error while updating tracker') . '");';
            }
            self::_cleanBuffer();
            echo $return;
            die();
        } elseif (Tools::getValue('getPageRelatedItems') && Tools::getValue('pageName')) {
            $return = '';
            $page_name = Tools::getValue('pageName');
            $return .= '$(".tracker_add_button").hide("fast");';
            $return .= '$(".button_group").hide("fast");';
            $return .= '$(".category_picker").hide("fast");';
            $return .= '$(".product_picker").hide("fast");';
            if (isset($this->_page_related_vars[$page_name]) && self::_isFilledArray($this->_page_related_vars[$page_name])) {
                if ($page_name == 'category') {
                    $return .= '$(".product_picker").hide("fast");$(".category_picker").fadeIn("fast");';
                }
                if ($page_name == 'product') {
                    $return .= '$(".category_picker").hide("fast");$(".product_picker").fadeIn("fast");';
                }
                foreach ($this->_page_related_vars[$page_name] as $item) {
                    $return .= '$(".button_group_'.$item.'").fadeIn("fast");';
                    foreach ($this->_tracking_vars_label [$item] as $key => $value) {
                        $return .= '$(".tracker_add_button_'.$key.'").fadeIn("fast");';
                    }
                }
            }
            $this->_html = $return;
            $this->_echoOutput(true);
        } elseif (Tools::getValue('getCategory')) {
            $this->_html = '';
            $query = Tools::getValue('q', false);
            if (!$query || Tools::strlen($query) < 1) {
                self::_cleanBuffer();
                die();
            }
            $limit = Tools::getValue('limit', 100);
            $start = Tools::getValue('start', 0);
            $items = Db::getInstance()->ExecuteS(
                'SELECT c.`id_category`, cl.`name`
                FROM `' . _DB_PREFIX_ . 'category` c
                LEFT JOIN `' . _DB_PREFIX_ . 'category_lang` cl ON (c.`id_category` = cl.`id_category`)
                '.self::addSqlAssociation('category', 'c', 'id_category').'
                WHERE (cl.name LIKE \'%' . pSQL($query) . '%\')
                AND cl.`id_lang` = ' . (int) $this->context->language->id . '
                AND c.`active` = 1
                ORDER BY cl.`name`
                ' . ($limit ? 'LIMIT ' . $start . ', ' . (int) $limit : '')
            );
            if ($items) {
                foreach ($items as $row) {
                    $this->_html .= $row ['id_category'] . '=' . $row ['name'] . "\n";
                }
            }
            self::_cleanBuffer();
            echo $this->_html;
            die();
        } elseif (Tools::getValue('getProduct')) {
            $this->_html = '';
            $query = Tools::getValue('q', false);
            if (! $query || Tools::strlen($query) < 1) {
                self::_cleanBuffer();
                die();
            }
            $limit = Tools::getValue('limit', 100);
            $start = Tools::getValue('start', 0);
            $items = Db::getInstance()->ExecuteS(
                'SELECT p.`id_product`, CONCAT(p.`id_product`, \' - \', IFNULL(CONCAT(NULLIF(TRIM(p.reference), \'\'), \' - \'), \'\'), pl.`name`) AS name
                FROM `' . _DB_PREFIX_ . 'product` p, `' . _DB_PREFIX_ . 'product_lang` pl, `' . _DB_PREFIX_ . 'product_shop` ps ' . '
                WHERE p.`id_product`=pl.`id_product`
                 AND p.`id_product`=ps.`id_product`
                '.Shop::addSqlRestriction(false, 'ps').'
                AND pl.`id_lang`=' . (int)$this->_default_language . '
                AND p.`active` = 1
                AND ((p.`id_product` LIKE \'%' . pSQL($query) . '%\') OR (pl.`name` LIKE \'%' . pSQL($query) . '%\') OR (p.`reference` LIKE \'%' . pSQL($query) . '%\') OR (pl.`description` LIKE \'%' . pSQL($query) . '%\') OR (pl.`description_short` LIKE \'%' . pSQL($query) . '%\'))
                GROUP BY p.`id_product`
                ORDER BY pl.`name` ASC '.($limit ? 'LIMIT '.$start.', '.(int) $limit : '')
            );
            if ($items) {
                foreach ($items as $row) {
                    $this->_html .= $row ['id_product'] . '=' . $row ['name'] . "\n";
                }
            }
            self::_cleanBuffer();
            echo $this->_html;
            die();
        } elseif (Tools::getIsset('submit_total')) {
            $this->_html = '';
            $key = 'total';
            $button = 'total';
            if (Tools::getValue('shipping')) {
                $key .= '_with_shipping';
                $button .= '_with_shipping';
                if (Tools::getValue('discount')) {
                    $key .= '_with_discount';
                    $button .= '_with_discount';
                }
            } else {
                if (Tools::getValue('discount')) {
                    $key .= '_with_discount';
                    $button .= '_with_discount';
                } else {
                    $key .= '_product';
                    $button .= '_product';
                }
            }
            if (Tools::getValue('taxe')) {
                $key .= '_with_taxes';
                $button .= '_with_taxes';
            }
            $this->_html .= '<script type="text/javascript">
                                parent.parent.closeDialogIframe();
                                parent.parent.addVarToTracker("' . $key . '","' . $button . '");
                             </script>';
            self::_cleanBuffer();
            echo $this->_html;
            die();
        } elseif (Tools::getIsset('submit_product_cart')) {
            $this->_cleanOutput();
            if (in_array(Tools::getValue('spacer'), array('%'))) {
                $this->_html .= '<script type="text/javascript">
                 alert("' . $this->l('Spacer is not allowed.') . '");
                </script>';
                $this->_echoOutput(true);
            }
            $key = 'id_products_cart';
            $button = 'id_products_cart';
            if (Tools::getValue('spacer')) {
                if ((Tools::strlen(Tools::getValue('spacer'))) > 1) {
                    $this->_html .= '<script type="text/javascript">
                                        alert("' . $this->l('Spacer must be 1 character long.') . '");
                                    </script>';
                } else {
                    $spacer = Tools::getValue('spacer');
                    if ($spacer  == "\\") {
                        $spacer = "\\\\";
                    }
                    $key .= '_~#' . $spacer . '#~';
                    $this->_html .= '<script type="text/javascript">
                                    parent.parent.closeDialogIframe();
                                    parent.parent.addVarToTracker("' . $key . '","' . $button . '");
                                 </script>';
                }
            } else {
                $this->_html .= '<script type="text/javascript">
                                    alert("' . $this->l('You must set up a spacer for Id\'s.') . '");
                                </script>';
            }
            self::_cleanBuffer();
            $this->_echoOutput(true);
        } elseif (Tools::getIsset('submit_category_cart')) {
            $this->_html = '';
            if (in_array(Tools::getValue('spacer'), array('%'))) {
                $this->_html .= '<script type="text/javascript">
                 alert("' . $this->l('Spacer is not allowed.') . '");
                </script>';
                self::_cleanBuffer();
                echo $this->_html;
                die();
            }
            $key = 'id_categories_cart';
            $button = 'id_categories_cart';
            if (Tools::getValue('spacer')) {
                if ((Tools::strlen(Tools::getValue('spacer'))) > 1) {
                    $this->_html .= '<script type="text/javascript">
                                        alert("' . $this->l('Spacer must be 1 character long.') . '");
                                    </script>';
                } else {
                    $spacer = Tools::getValue('spacer');
                    if ($spacer  == "\\") {
                        $spacer = "\\\\";
                    }
                    $key .= '_~#' . $spacer . '#~';
                    $this->_html .= '<script type="text/javascript">
                                    parent.parent.closeDialogIframe();
                                    parent.parent.addVarToTracker("' . $key . '","' . $button . '");
                                 </script>';
                }
            } else {
                $this->_html .= '<script type="text/javascript">
                                    alert("' . $this->l('You must set up a spacer for Id\'s.') . '");
                                </script>';
            }
            self::_cleanBuffer();
            $this->_echoOutput(true);
        } elseif (Tools::getIsset('submit_shipping')) {
            $this->_html = '';
            $key = 'shipping_only';
            $button = 'shipping_only';
            if (Tools::getValue('taxe')) {
                $key .= '_with_taxes';
                $button .= '_with_taxes';
            }
            $this->_html .= '<script type="text/javascript">
                                parent.parent.closeDialogIframe();
                                parent.parent.addVarToTracker("' . $key . '","' . $button . '");
                             </script>';
            self::_cleanBuffer();
            echo $this->_html;
            die();
        } elseif (Tools::getIsset('submit_currency')) {
            $this->_html = '';
            $key = 'currency';
            $button = 'currency';
            if (Tools::getValue('currency')) {
                $key .= '_' . Tools::getValue('currency');
            }
            $button .= '_' . Tools::getValue('currency');
            $this->_html .= '<script type="text/javascript">
                                parent.parent.closeDialogIframe();
                                parent.parent.addVarToTracker("' . $key . '","' . $button . '");
                             </script>';
            self::_cleanBuffer();
            $this->_echoOutput(true);
        } elseif (Tools::getIsset('submit_qty_product_cart')) {
            $this->_cleanOutput();
            if (in_array(Tools::getValue('spacer'), array('%'))) {
                $this->_html .= '<script type="text/javascript">
                 alert("' . $this->l('Spacer is not allowed.') . '");
                </script>';
                $this->_echoOutput(true);
            }
            $key = 'qty_products_cart';
            $button = 'qty_products_cart';
            if (Tools::getValue('spacer')) {
                if ((Tools::strlen(Tools::getValue('spacer'))) > 1) {
                    $this->_html .= '<script type="text/javascript">
                                        alert("' . $this->l('Spacer must be 1 character long.') . '");
                                    </script>';
                } else {
                    $spacer = Tools::getValue('spacer');
                    if ($spacer  == "\\") {
                        $spacer = "\\\\";
                    }
                    $key .= '_~#' . $spacer . '#~';
                    $this->_html .= '<script type="text/javascript">
                                    parent.parent.closeDialogIframe();
                                    parent.parent.addVarToTracker("' . $key . '","' . $button . '");
                                 </script>';
                }
            } else {
                $this->_html .= '<script type="text/javascript">
                                    alert("' . $this->l('You must set up a spacer for Id\'s.') . '");
                                </script>';
            }
            self::_cleanBuffer();
            $this->_echoOutput(true);
        } elseif (Tools::getIsset('submit_voucher_list')) {
            $this->_cleanOutput();
            if (in_array(Tools::getValue('spacer'), array('%', '-'))) {
                $this->_html .= '<script type="text/javascript">alert("' . $this->l('Spacer is not allowed.') . '");</script>';
                $this->_echoOutput(true);
            }
            $key = 'voucher_list';
            $button = 'voucher_list';
            if (Tools::getValue('spacer')) {
                if ((Tools::strlen(Tools::getValue('spacer'))) > 1) {
                    $this->_html .= '<script type="text/javascript">alert("' . $this->l('Spacer must be 1 character long.') . '");</script>';
                } else {
                    $spacer = Tools::getValue('spacer');
                    if ($spacer  == "\\") {
                        $spacer = "\\\\";
                    }
                    $key .= '_~#' . $spacer . '#~';
                    $this->_html .= '<script type="text/javascript">
                                    parent.parent.closeDialogIframe();
                                    parent.parent.addVarToTracker("' . $key . '","' . $button . '");
                                 </script>';
                }
            } else {
                $this->_html .= '<script type="text/javascript">
                                    alert("' . $this->l('You must set up a spacer for Id\'s.') . '");
                                </script>';
            }
            self::_cleanBuffer();
            $this->_echoOutput(true);
        } elseif (Tools::getIsset('submit_price_product_cart')) {
            $this->_cleanOutput();
            if (in_array(Tools::getValue('spacer'), array('%'))) {
                $this->_html .= '<script type="text/javascript">
                 alert("' . $this->l('Spacer is not allowed.') . '");
                </script>';
                $this->_echoOutput(true);
            }
            $key = 'price_products_cart';
            $button = 'price_products_cart';
            if (Tools::getValue('taxes')) {
                $key = 'price_products_cart_wt';
                $button = 'price_products_cart_wt';
            }
            if (Tools::getValue('spacer')) {
                if ((Tools::strlen(Tools::getValue('spacer'))) > 1) {
                    $this->_html .= '<script type="text/javascript">
                                        alert("' . $this->l('Spacer must be 1 character long.') . '");
                                    </script>';
                } else {
                    $spacer = Tools::getValue('spacer');
                    if ($spacer  == "\\") {
                        $spacer = "\\\\";
                    }
                    $key .= '_~#' . $spacer . '#~';
                    $this->_html .= '<script type="text/javascript">
                                    parent.parent.closeDialogIframe();
                                    parent.parent.addVarToTracker("' . $key . '","' . $button . '");
                                 </script>';
                }
            } else {
                $this->_html .= '<script type="text/javascript">
                                    alert("' . $this->l('You must set up a spacer for Id\'s.') . '");
                                </script>';
            }
            self::_cleanBuffer();
            $this->_echoOutput(true);
        } elseif (Tools::getIsset('submit_product_price')) {
            $this->_cleanOutput();
            $key = 'product_price';
            $button = 'product_price';
            if (Tools::getValue('taxes')) {
                if (Tools::getValue('eco_taxes')) {
                    $key = 'product_price_wt_wet';
                    $button = 'product_price_wt_wet';
                } else {
                    $key = 'product_price_wt';
                    $button = 'product_price_wt';
                }
            } else {
                if (Tools::getValue('eco_taxes')) {
                    $key = 'product_price_wet';
                    $button = 'product_price_wet';
                }
            }
            $this->_html .= '<script type="text/javascript">
                                parent.parent.closeDialogIframe();
                                parent.parent.addVarToTracker("' . $key . '","' . $button . '");
                             </script>';
            self::_cleanBuffer();
            $this->_echoOutput(true);
        } elseif (Tools::getIsset('submit_commission')) {
            $this->_cleanOutput();
            $key = 'commission';
            $button = 'commission';
            if (Tools::getValue('commission_amount')) {
                $key .= '_~#' . Tools::getValue('commission_amount') . '#~';
                $this->_html .= '<script type="text/javascript">
                                    parent.parent.closeDialogIframe();
                                    parent.parent.addVarToTracker("' . $key . '","' . $button . '");
                                 </script>';
            } else {
                $this->_html .= '<script type="text/javascript">
                                    alert("' . $this->l('You must set up a spacer for Id\'s.') . '");
                                </script>';
            }
            self::_cleanBuffer();
            $this->_echoOutput(true);
        }
    }
    protected function displayTrackingTable()
    {
        $trackers = AdvancedTrackingWizardClass::getTrackers();
        if ($trackers && count($trackers) > 0) {
            foreach ($trackers as &$tracker) {
                $tracker['object'] = extensionTrackerClass::loadExtension($tracker['internal_name']);
                $tracker['page'] = ($tracker['page'] ? $this->_valid_page_labels[$tracker['page']] : '');
            }
        }
        $vars = array(
            'trackers' => $trackers,
        );
        return $this->fetchTemplate('module/tables/tracking.tpl', $vars);
    }
    protected function displayFormCreateTracker($params)
    {
        $extensions = new extensionTrackerClass();
        $load = $extensions->getExtensionsFromDb();
        $vars = array(
            'load' => $load,
        );
        return $this->fetchTemplate('module/forms/load.tpl', $vars);
    }
    protected function displayFormAddTracker($params)
    {
        $categories = '';
        $products = '';
        $option = array();
        $option = array_merge(array(0 => $this->l('-- Please select a page --')), $this->_valid_page_labels);
        $obj = false;
        if (Tools::getValue('alter') && $idTracking = (int) Tools::getValue('id_tracking')) {
            if (Tools::getValue('is_ext')) {
                $extensions = extensionTrackerClass::getExtensionId($idTracking);
                foreach ($extensions as $extension) {
                    $this->_html .= $this->displayFormExtensionConfig($extension ['id_extension'], $idTracking);
                }
                $this->_echoOutput(true);
            } else {
                $obj = new AdvancedTrackingWizardClass($idTracking);
            }
        }
        if ($obj) {
            $mode ='update';
            $categories = AdvancedTrackingWizardClass::getAssignedCategories($obj->id, (int) $this->context->language->id);
            $products = AdvancedTrackingWizardClass::getAssignedProducts($obj->id, (int) $this->context->language->id);
        } else {
            $mode ='add';
        }
        $vars = array(
            'mode' => $mode,
            'obj' => $obj,
            'categories' => $categories,
            'products' => $products,
            'option' => $option,
            'tracking_group_vars_label' => $this->_tracking_group_vars_label,
            'tracking_vars_label' => $this->_tracking_vars_label,
            'var_to_config' => $this->_var_to_config,
        );
        return $this->fetchTemplate('module/forms/add.tpl', $vars);
    }
    protected function displayFormConfig()
    {
        $var = Tools::getValue('var');
        $vars = array();
        if ($var == 'currency') {
            $vars = array(
                'options' => array('id' => $this->l('ID Currency'), 'name' => $this->l('Name'), 'isocode' => $this->l('ISO Code'), 'sign' => $this->l('Sign')),
            );
        }
        return $this->fetchTemplate('module/forms/config/'.$var.'.tpl', $vars);
    }
    protected function displayFormExtensionConfig($loadTracker = null, $idTracking = false)
    {
        if ($loadTracker !== null) {
            $extensionName = $loadTracker;
        } else {
            $extensionName = Tools::getValue('load_tracker');
        }
        $loadClass = extensionTrackerClass::loadExtensionsClass($extensionName);
        $extension = new $loadClass();
        $vars = array(
            'form' => $extension->displayForm((int) $idTracking),
        );
        return $this->fetchTemplate('module/forms/extension_config.tpl', $vars);
    }
    private function postProcessAddTracker()
    {
        $this->_cleanOutput();
        $obj = new AdvancedTrackingWizardClass(Tools::getValue('id_tracking', false));
        $this->_html = '';
        $this->_errors = $obj->validateController();
        if (!sizeof($this->_errors)) {
            $this->copyFromPost($obj);
            $obj->active = Tools::getValue('active_tracking');
            if (! $obj->save()) {
                $this->_errors [] = $this->l('Error while saving');
            }
            if (!sizeof($this->_errors)) {
                $this->_html .= '<script type="text/javascript">
                                    parent.parent.show_info("' . $this->l('Saved') . '");
                                    parent.parent.reloadPanel("displayTrackingTable");
                                    parent.parent.closeDialogIframe();
                                </script>';
            }
        }
        if (sizeof($this->_errors)) {
            $this->_html .= '<script type="text/javascript">
                                parent.parent.show_error("' . implode('<br />', $this->_errors) . '");
                            </script>';
        }
        $this->_echoOutput(true);
    }
    protected function getExtensionCode($params, $tracker, $page_name, $fromHook)
    {
        if ($this->_isFilledArray($tracker)) {
            $extensions = extensionTrackerClass::getExtensionId($tracker['id_tracking']);
        }
        foreach ($extensions as $ext) {
            $load_class = extensionTrackerClass::loadExtensionsClass($ext['id_extension']);
        }
        include_once(_PS_ROOT_DIR_.extensionTrackerClass::$extension_dir . $load_class . '/' . $load_class . '.php');
        $class = new $load_class();
        return $class->createTag($params, $tracker['id_tracking'], $page_name, $fromHook);
    }
    protected function trackerParser($params, $text, $page_name)
    {
        preg_match_all('$rel="(.[^"]*)"$', $text, $pat);
        foreach ($pat [1] as $button) {
            $text = preg_replace('/\{\%.[^%]*\%\}/', '{' . $button . '}', $text, 1);
        }
        return $this->getParams($params, $text, $page_name);
    }
    public function addTax($tax_free, $rate)
    {
        return round($tax_free * (100 + $rate) / 100, 4);
    }
    private function _getCustomerNbValidOrders($id_customer)
    {
        $sql = 'SELECT COUNT(`id_order`) AS nb FROM `'._DB_PREFIX_.'orders` WHERE `valid`= 1 AND `id_customer` = '.(int)$id_customer . Shop::addSqlRestriction();
        $result = Db::getInstance()->getRow($sql);
        return isset($result['nb']) ? (int)$result['nb'] : 0;
    }
    public function getParams($params, $output, $page_name)
    {
        $cart_obj = null;
        $id_order = $id_product = $id_category = $id_customer = $postcode = $city = $id_gender = $birthday = $iso_country = '';
        $email = $lastname = $firstname = $product_name = $product_ref = $category_name = $quantity_cart = $id_products_cart = $qty_products_cart = '';
        $voucher = $price_products_cart = $price_products_cart_wt = $id_categories_cart = $payment = $payment_module = '';
        $total_with_shipping = $total_with_shipping_with_taxes = $total_with_shipping_with_discount = $total_with_shipping_with_discount_with_taxes = 0;
        $total_with_discount = $total_with_discount_with_taxes = $shipping_only = $shipping_only_with_tax = $total_product = $total_product_with_taxes = 0;
        $product_price = $product_price_wt = $product_price_wet = $product_price_wt_wet = $taxe_only = $is_new_customer = 0;
        $defaultSpacer = ',';
        $voucher_list = '';
        $gtm_trans_products = array();
        $id_products = null;
        $id_lang = (int) $this->context->language->id;
        if ($page_name == 'order-confirmation') {
            $id_cart    = Tools::getValue('id_cart');
            $id_order   = Order::getOrderByCartId((int)$id_cart);
            $cart_obj   = new Cart($id_cart);
            $order      = new Order($id_order);
            $id_customer = $order->id_customer;
            $payment = str_replace(' ', '_', $order->payment);
            $payment_module = $order->module;
        } else {
            if (isset($params['cart']) && Validate::isLoadedObject($params['cart'])) {
                $id_cart = (int)$params['cart']->id;
                $cart_obj = $params['cart'];
            } else {
                $cart_obj = $this->context->cart;
                if (Validate::isLoadedObject($cart_obj)) {
                    $id_cart = $cart_obj->id;
                } else {
                    $id_cart = 0;
                }
            }
            if (isset($cart_obj->id_customer)) {
                $id_customer = $cart_obj->id_customer;
            } elseif (isset($this->context->cookie->id_customer)) {
                $id_customer = $this->context->cookie->id_customer;
            }
        }
        $quantity_cart = Cart::getNbProducts($id_cart);
        if (Validate::isLoadedObject($cart_obj)) {
            $total_with_shipping = round($cart_obj->getOrderTotal(false, Cart::ONLY_PRODUCTS), 2);
            $total_with_shipping_with_taxes = round($cart_obj->getOrderTotal(true, Cart::ONLY_PRODUCTS), 2);
            $total_with_shipping_with_discount = round($cart_obj->getOrderTotal(false, Cart::BOTH), 2);
            $total_with_shipping_with_discount_with_taxes = round($cart_obj->getOrderTotal(true, Cart::BOTH), 2);
            $total_with_discount = round($cart_obj->getOrderTotal(false, Cart::BOTH_WITHOUT_SHIPPING), 2);
            $total_with_discount_with_taxes = round($cart_obj->getOrderTotal(true, Cart::BOTH_WITHOUT_SHIPPING), 2);
            $shipping_only = round($cart_obj->getOrderTotal(false, Cart::ONLY_SHIPPING), 2);
            $shipping_only_with_tax = round($cart_obj->getOrderTotal(true, Cart::ONLY_SHIPPING), 2);
            $total_product = round($cart_obj->getOrderTotal(false, Cart::ONLY_PRODUCTS_WITHOUT_SHIPPING), 2);
            $total_product_with_taxes = round($cart_obj->getOrderTotal(true, Cart::ONLY_PRODUCTS_WITHOUT_SHIPPING), 2);
            $taxe_only = $total_with_shipping_with_discount_with_taxes - $total_with_shipping_with_discount;
            $id_products = $cart_obj->getProducts();
            $voucher_list_tmp = $cart_obj->getCartRules();
            if (self::_isFilledArray($voucher_list_tmp)) {
                $voucher_list = array();
                foreach ($voucher_list_tmp as $voucher) {
                    $voucher_list[] = rawurlencode($voucher['name']);
                }
                $voucher = $voucher_list[0];
                $voucher_list = implode($defaultSpacer, $voucher_list);
            }
        }
        if (Validate::isLoadedObject($this->context->currency)) {
            $currency_id = (int) $this->context->currency->id;
            $currency_name = $this->context->currency->name;
            $currency_isocode = $this->context->currency->iso_code;
            $currency_sign = $this->context->currency->sign;
        }
        if (version_compare(_PS_VERSION_, '1.5.6.0', '>=') && method_exists($this->context->controller, 'getProduct') && Validate::isLoadedObject($this->context->controller->getProduct())) {
            $id_product = (int)$this->context->controller->getProduct()->id;
        } elseif (Tools::getIsset('id_product') && Tools::getValue('id_product')) {
            $id_product = (int)Tools::getValue('id_product');
        }
        if (isset($params['product'])) {
            $product_name = $params['product']->name;
            $product_ref = $params['product']->reference;
        } else {
            $req = $this->getProductNameRefFromId($id_product, (int) $this->context->language->id);
            foreach ($req as $row) {
                $product_name = $row['name'];
                $product_ref = $row['reference'];
            }
        }
        if (isset($params['category'])) {
            $id_category = $params['category']->id_category;
            $category_name = $params['category']->name;
        } elseif (version_compare(_PS_VERSION_, '1.5.6.0', '>=') && method_exists($this->context->controller, 'getCategory') && Validate::isLoadedObject($this->context->controller->getCategory())) {
            $id_category = (int)$this->context->controller->getCategory()->id;
        } elseif (Tools::getValue('id_category')) {
            $id_category = (int)Tools::getValue('id_category');
        } else {
            if ($id_product) {
                $id_category = $this->getProductCategoryFromId($id_product);
            }
        }
        if ($category_name == '' && $id_category && $id_lang) {
            $category_name = $this->getCategoryNameFromId($id_category, $id_lang);
        }
        if (isset($id_customer) && $id_customer > 0) {
            if (Validate::isLoadedObject($cart_obj)) {
                $reqAddressInfo = $this->getCustomerAddressInfo($id_customer, (isset($cart_obj->id_address_delivery) && $cart_obj->id_address_delivery ? $cart_obj->id_address_delivery : (isset($cart_obj->id_address_invoice) && $cart_obj->id_address_invoice ? $cart_obj->id_address_invoice : false)));
            } else {
                $reqAddressInfo = $this->getCustomerAddressInfo($id_customer);
            }
            if (is_array($reqAddressInfo)) {
                $postcode = $reqAddressInfo ['postcode'];
                $city = $reqAddressInfo ['city'];
                $iso_country = $reqAddressInfo ['iso_code'];
                $email = $reqAddressInfo ['email'];
                $lastname = $reqAddressInfo ['lastname'];
                $firstname = $reqAddressInfo ['firstname'];
                $id_gender = $reqAddressInfo ['id_gender'];
                $birthday = $reqAddressInfo ['birthday'];
            }
            $is_new_customer = ($this->_getCustomerNbValidOrders($id_customer) > 0 ? 0 : 1);
        }
        if (isset($id_products) && is_array($id_products)) {
            foreach ($id_products as $product) {
                $id_products_cart .= $product ['id_product'] . $defaultSpacer;
                $qty_products_cart .= $product ['quantity'] . $defaultSpacer;
                $price_products_cart .= round($product ['price'], 2) . $defaultSpacer;
                $price_products_cart_wt .= round($product ['price_wt'], 2) . $defaultSpacer;
                $id_categories_cart .= $product ['id_category_default'] . $defaultSpacer;
                $category_default = new Category($product ['id_category_default'], (int) $this->context->language->id);
                $gtm_trans_products[] = array('sku' => (int)$product['id_product'], 'name' => $product['name'], 'category' => (isset($category_default->name) ? $category_default->name : ''), 'price' => $product['price'], 'quantity' => (int)$product['quantity']);
            }
        }
        $id_products_cart = Tools::substr($id_products_cart, 0, - 1);
        $qty_products_cart = Tools::substr($qty_products_cart, 0, - 1);
        $price_products_cart = Tools::substr($price_products_cart, 0, - 1);
        $price_products_cart_wt = Tools::substr($price_products_cart_wt, 0, - 1);
        $id_categories_cart = Tools::substr($id_categories_cart, 0, - 1);
        if (Tools::getValue('id_product') && (int)Tools::getValue('id_product') > 0) {
            $specific_price_output = null;
            $product_price = Product::getPriceStatic((int)Tools::getValue('id_product'), false, null, 6, null, false, true, 1, false, null, null, null, $specific_price_output, false);
            $product_price_wt = Product::getPriceStatic((int)Tools::getValue('id_product'), true, null, 6, null, false, true, 1, false, null, null, null, $specific_price_output, false);
            $product_price_wet = Product::getPriceStatic((int)Tools::getValue('id_product'), false, null, 6, null, false, true, 1, false, null, null, null, $specific_price_output, true);
            $product_price_wt_wet = Product::getPriceStatic((int)Tools::getValue('id_product'), true, null, 6, null, false, true, 1, false, null, null, null, $specific_price_output, true);
        }
        $to_replace = array('{product_price}', '{product_price_wt}', '{product_price_wet}', '{product_price_wt_wet}', '{id_cart}', '{id_order}', '{id_category}', '{id_product}', '{id_customer}', '{iso_country}', '{id_lang}', '{email}', '{lastname}', '{firstname}', '{postcode}', '{city}', '{id_gender}', '{birthday}', '{product_name}', '{product_ref}', '{category_name}', '{currency_id}', '{currency_name}', '{currency_isocode}', '{currency_sign}', '{quantity_cart}', '{shipping_only_with_taxes}', '{shipping_only}', '{total_product}', '{total_product_with_taxes}', '{total_with_shipping}', '{total_with_shipping_with_taxes}', '{total_with_discount}', '{total_with_discount_with_taxes}', '{total_with_shipping_with_discount}', '{total_with_shipping_with_discount_with_taxes}','{payment}', '{voucher}', '{taxe_only}', '{gtm_trans_products}', '{is_new_customer}', '{payment_module}');
        $replacement = array($product_price, $product_price_wt, $product_price_wet, $product_price_wt_wet, $id_cart, $id_order, $id_category, $id_product, $id_customer, $iso_country, $id_lang, $email, $lastname, $firstname, $postcode, $city, $id_gender, $birthday, $product_name, $product_ref, $category_name, $currency_id, $currency_name, $currency_isocode, $currency_sign, $quantity_cart, $shipping_only_with_tax, $shipping_only, $total_product, $total_product_with_taxes, $total_with_shipping, $total_with_shipping_with_taxes, $total_with_discount, $total_with_discount_with_taxes, $total_with_shipping_with_discount, $total_with_shipping_with_discount_with_taxes, $payment, $voucher, $taxe_only, (method_exists('Tools', 'jsonEncode') ? Tools::jsonEncode($gtm_trans_products) : json_encode($gtm_trans_products)), $is_new_customer, $payment_module);
        if (preg_match('/id_categories_cart_/', $output)) {
            preg_match_all('/\{id_categories_cart_~#(.([^#][^~])*)#~\}/', $output, $spacers);
            foreach ($spacers [1] as $key => $spacer) {
                $output = preg_replace('/\{id_categories_cart_~#(.([^#][^~])*)#~\}/', str_replace(',', $spacer, $id_categories_cart), $output, 1);
            }
        }
        if (preg_match('/qty_products_cart_/', $output)) {
            preg_match_all('/\{qty_products_cart_~#(.([^#][^~])*)#~\}/', $output, $spacers);
            foreach ($spacers [1] as $key => $spacer) {
                $output = preg_replace('/\{qty_products_cart_~#(.([^#][^~])*)#~\}/', str_replace(',', $spacer, $qty_products_cart), $output, 1);
            }
        }
        if (preg_match('/voucher_list_/', $output)) {
            preg_match_all('/\{voucher_list_~#(.([^#][^~])*)#~\}/', $output, $spacers);
            foreach ($spacers [1] as $key => $spacer) {
                $output = preg_replace('/\{voucher_list_~#(.([^#][^~])*)#~\}/', str_replace(',', $spacer, $voucher_list), $output, 1);
            }
        }
        if (preg_match('/price_products_cart_/', $output)) {
            preg_match_all('/\{price_products_cart_~#(.([^#][^~])*)#~\}/', $output, $spacers);
            foreach ($spacers [1] as $key => $spacer) {
                $output = preg_replace('/\{price_products_cart_~#(.([^#][^~])*)#~\}/', str_replace(',', $spacer, $price_products_cart), $output, 1);
            }
        }
        if (preg_match('/price_products_cart_wt_/', $output)) {
            preg_match_all('/\{price_products_cart_wt_~#(.([^#][^~])*)#~\}/', $output, $spacers);
            foreach ($spacers [1] as $key => $spacer) {
                $output = preg_replace('/\{price_products_cart_wt_~#(.([^#][^~])*)#~\}/', str_replace(',', $spacer, $price_products_cart_wt), $output, 1);
            }
        }
        if (preg_match('/id_products_cart_/', $output)) {
            preg_match_all('/\{id_products_cart_~#(.([^#][^~])*)#~\}/', $output, $spacers);
            foreach ($spacers [1] as $key => $spacer) {
                $output = preg_replace('/\{id_products_cart_~#(.([^#][^~])*)#~\}/', str_replace(',', $spacer, $id_products_cart), $output, 1);
            }
        }
        if (preg_match('/commission_/', $output)) {
            preg_match_all('/\{commission_~#(.([^#][^~])*)#~\}/', $output, $values);
            if (isset($values[1][0])) {
                $output = preg_replace('/\{commission_~#(.([^#][^~])*)#~\}/', (int)$values [1][0]/100 * $total_with_shipping_with_discount_with_taxes, $output, 1);
            }
        }
        $output = str_replace($to_replace, $replacement, $output);
        return $output;
    }
    public function getActiveTrackersByPage($page, $id_shop = false, $exclude_all = false)
    {
        if (in_array($page, self::$_valid_page)) {
            switch ($page) {
                case 'category':
                    return AdvancedTrackingWizardClass::getActiveTrackers($page, true, false, $id_shop, $exclude_all);
                case 'product':
                    return AdvancedTrackingWizardClass::getActiveTrackers($page, false, true, $id_shop, $exclude_all);
                case 'order':
                    if (Tools::getValue('step') == 3) {
                        return AdvancedTrackingWizardClass::getActiveTrackers($page, false, false, $id_shop, $exclude_all);
                    } elseif (version_compare(_PS_VERSION_, '1.7.0.0', '<') && !Tools::getValue('step')) {
                        return AdvancedTrackingWizardClass::getActiveTrackers('cart', false, false, $id_shop, $exclude_all);
                    } elseif (version_compare(_PS_VERSION_, '1.7.0.0', '>=') && Tools::getValue('action') == 'show') {
                        return AdvancedTrackingWizardClass::getActiveTrackers('cart', false, false, $id_shop, $exclude_all);
                    }
                    break;
            }
        }
        return AdvancedTrackingWizardClass::getActiveTrackers($page, false, false, $id_shop, $exclude_all);
    }
    protected static function getCustomerAddressInfo($id_customer, $id_address = false)
    {
        $result = Db::getInstance()->getRow(
            'SELECT cst.`firstname`,cst.`lastname`,cst.`email`,cst.`id_gender`,cst.`birthday`, a.`id_country`, a.`postcode`, a.`city`,c.`id_country`,c.`iso_code`
            FROM `' . _DB_PREFIX_ . 'customer` cst
            LEFT JOIN `' . _DB_PREFIX_ . 'address`a ON (cst.`id_customer` = a.`id_customer`)
            LEFT JOIN `' . _DB_PREFIX_ . 'country` c ON (a.`id_country` = c.`id_country`)
            WHERE cst.`id_customer` = ' . (int)$id_customer .
            ((int)$id_address ? ' AND a.`id_address`='.(int)$id_address : '')
        );
        return $result;
    }
    protected static function getProductCategoryFromId($id_product)
    {
        $defaultCategoryId = Db::getInstance()->getValue(
            'SELECT p.`id_category_default`
            FROM `' . _DB_PREFIX_ . 'product` p
            '.Shop::addSqlAssociation('product', 'p').'
            WHERE p.`id_product` = ' . (int)$id_product
        );
        return $defaultCategoryId;
    }
    protected static function getProductNameRefFromId($id_product, $id_lang)
    {
        $result = Db::getInstance()->ExecuteS(
            'SELECT p.`reference`, pl.`name`
            FROM `' . _DB_PREFIX_ . 'product` p
            '.Shop::addSqlAssociation('product', 'p').'
            LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON (p.`id_product` = pl.`id_product`)
            WHERE p.`id_product` = ' . (int)$id_product . '
            AND pl.`id_lang` = ' . (int)$id_lang
        );
        return $result;
    }
    protected static function getCategoryNameFromId($id_category, $id_lang)
    {
        $categoryName = Db::getInstance()->getValue(
            'SELECT c.`name`
            FROM ' . _DB_PREFIX_ . 'category_lang c
            WHERE c.`id_category` = ' . (int)$id_category . '
            '.Shop::addSqlRestrictionOnLang('c').'
            AND c.`id_lang` =  ' . (int)$id_lang
        );
        return $categoryName;
    }
    public function getCurrentIdCustomer($params)
    {
        $id_customer = false;
        if (isset($params['cart']) && Validate::isLoadedObject($params['cart'])) {
            $cart_obj = $params['cart'];
        } else {
            $cart = $this->context->cart;
            if (Validate::isLoadedObject($cart)) {
                $cart_obj = new Cart($cart->id);
            }
        }
        if (isset($cart_obj->id_customer)) {
            $id_customer = (int)$cart_obj->id_customer;
        } elseif (isset($this->context->cookie->id_customer)) {
            $id_customer = (int)$this->context->cookie->id_customer;
        }
        return $id_customer;
    }
    public function getCustomerIsNew($params)
    {
        $id_customer = $this->getCurrentIdCustomer($params);
        if ($id_customer) {
            $customer = new Customer($id_customer);
            if (Validate::isLoadedObject($customer) && (time() - strtotime($customer->date_add)) <= 30) {
                return true;
            }
        }
        return false;
    }
    public function hookDisplayFooter($params)
    {
        if (version_compare(_PS_VERSION_, '1.7.0.0', '<')) {
            return $this->outputTrackingCodes($params);
        }
    }
    public function hookDisplayBeforeBodyClosingTag($params)
    {
        if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
            return $this->outputTrackingCodes($params);
        }
    }
    public function hookDisplayCustomerAccountForm($params)
    {
        return $this->outputTrackingCodes($params, 'create-account-form', 'customer-form');
    }
    public function outputTrackingCodes($params, $fromHook = 'footer', $forcePageName = null)
    {
        if ($this->_isInMaintenance()) {
            return;
        }
        $id_shop = false;
        if (Shop::isFeatureActive()) {
            $id_shop = (int) $this->context->shop->id;
        }
        $page_name = self::getPageName();
        if ($forcePageName != null) {
            $page_name = $forcePageName;
        }
        $output = '';
        $trackers = $this->getActiveTrackersByPage($page_name, $id_shop);
        if ($trackers) {
            foreach ($trackers as $tracker) {
                if ($tracker ['is_extension']) {
                    $output .= $this->getExtensionCode($params, $tracker, $page_name, $fromHook);
                } else {
                    $output .= $this->trackerParser($params, $tracker['tracking_code'], $page_name);
                }
            }
        }
        if ($this->getCustomerIsNew($params)) {
            $trackers = $this->getActiveTrackersByPage('customer-created', $id_shop, true);
            if ($trackers) {
                foreach ($trackers as $tracker) {
                    if ($tracker['is_extension']) {
                        $output .= $this->getExtensionCode($params, $tracker, 'customer-created', $fromHook);
                    } else {
                        $output .= $this->trackerParser($params, $tracker['tracking_code'], 'customer-created');
                    }
                }
            }
        }
        return $output;
    }
    public static function getPageName()
    {
        $page_name = false;
        if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
            $page = self::_getSmartyVarValue('page');
            $page_name = $page['page_name'];
        } else {
            $page_name = self::_getSmartyVarValue('page_name');
        }
        $request_uri = self::_getSmartyVarValue('request_uri');
        if ($page_name == 'order-opc') {
            $page_name = 'order';
        }
        if ($page_name == 'module-be2bill-validation') {
            $page_name = 'order-confirmation';
        }
        if (preg_match('/module-be2bill-validation/', $page_name) && (preg_match('/validation.php/', $request_uri) || preg_match('/validation/', $request_uri))) {
            $page_name = 'order-confirmation';
        }
        if ($page_name == 'module-przelewy24-paymentConfirmation') {
            $page_name = 'order';
        }
        if (preg_match('/paypal/', $request_uri) && (preg_match('/submit.php/', $request_uri) || preg_match('/\/submit/', $request_uri))) {
            $page_name = 'order-confirmation';
        }
        if (preg_match('/paypal/', $request_uri) && preg_match('/express_checkout/', $request_uri) && (preg_match('/payment.php/', $request_uri) || preg_match('/\/payment/', $request_uri))) {
            $page_name = 'order-confirmation';
        }
        if (version_compare(_PS_VERSION_, '1.7.0.0', '>=') && $page_name == 'checkout') {
            $page_name = 'order';
        }
        return $page_name;
    }
}
