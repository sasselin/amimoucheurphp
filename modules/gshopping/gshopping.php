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
 */
if (defined('_PS_VERSION_') === false) {
    exit;
}

if (!class_exists('GoogleTinyCache')) {
    include dirname(__FILE__) . '/classes/GoogleTinyCache.php';
}
if (!class_exists('ExportClass')) {
    include dirname(__FILE__) . '/classes/ExportClass.php';
}
if (!class_exists('GoogleFeedConverter')) {
    include dirname(__FILE__) . '/classes/GoogleFeedConverter.php';
}

class gshopping extends Module
{

    public static $categories_table = 'module_gshopping_table';
    public static $google_category_table = 'module_gshopping_category';
    public static $attributes_table = 'module_gshopping_attributes';
    public $history = null;

    /**
     * @var string Admin Module template path
     * (eg. '/home/prestashop/modules/module_name/views/templates/admin/')
     */
    protected $admin_tpl_path = null;

    /**
     * @var string Admin Module template path
     * (eg. '/home/prestashop/modules/module_name/views/templates/hook/')
     */
    protected $hooks_tpl_path = null;

    /** @var string Module js path (eg. '/shop/modules/module_name/js/') */
    protected $js_path = null;

    /** @var string Module css path (eg. '/shop/modules/module_name/css/') */
    protected $css_path = null;

    /** @var string Module sql path (eg. '/shop/modules/module_name/sql/') */
    protected $sql_path = null;

    /** @var string Module export path (eg. '/shop/modules/module_name/export/') */
    protected $export_path = null;

    /** @var protected array cache filled with active countries */
    protected static $countries_cache;

    /** @var protected array cache filled with lang informations */
    protected static $lang_cache;

    /** @var protected array cache filled with active countries */
    protected static $categories_cache;

    /** @var protected array cache filled with active countries */
    protected static $getTaxonomyLang_cache;

    /** @var protected array cache filled with active countries */
    protected static $currency_cache;

    /** @var protected array cache filled with active countries */
    protected static $carrier_cache;

    /** @var protected string cache filled with informations */
    protected $path;

    /** @var public string allow to increase memory size */
    public $increase_memory = false;

    /** SQL files */
    const INSTALL_SQL_FILE = 'install.sql';
    const UNINSTALL_SQL_FILE = 'uninstall.sql';

    public function __construct()
    {
        $this->name = 'gshopping';
        $this->tab = 'smart_shopping';
        $this->version = '3.1.1';
        $this->author = 'PrestaShop';

        $this->need_instance = '0';

        $this->bootstrap = true;
        $this->secure_key = Tools::encrypt($this->name);
        $this->module_key = '7c35a8cd476e2dca4788a13911f45e13';

        parent::__construct();

        $this->displayName = $this->l('Google Shopping');
        $this->description = $this->l('Export your catalog to Google Shopping (also called Google Merchant Center)');

        $this->js_path = $this->_path . 'views/js/';
        $this->css_path = $this->_path . 'views/css/';
        $this->sql_path = dirname(__FILE__) . '/sql/';
        $this->path = $this->local_path . 'cache/';
        $this->export_path = $this->local_path . 'export/';
        $this->admin_tpl_path = $this->local_path . 'views/templates/admin/';
        $this->hooks_tpl_path = $this->local_path . 'views/templates/hook/';

        $this->increase_memory = false;

        GoogleTinyCache::setPath($this->path);

        // Set all the cache who are require
        $this->cacheFactory();

        $this->history = array(
            'product' => array(),
            'category' => array(),
            'cms' => array(),
            'cmscategory' => array(),
            'supplier' => array(),
            'manufacturer' => array(),
            'static' => array(),
        );
    }

    /**
     * Get currency
     * @return Array currencie by country
     */
    private function getcurrencyCache()
    {
        self::$currency_cache = GoogleTinyCache::getCache('currency');

        if (self::$currency_cache === null || empty(self::$currency_cache)) {
            $this->getCountriesCache();
            $countries = self::$countries_cache;

            $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
                SELECT id_currency, iso_code, conversion_rate
                FROM `' . _DB_PREFIX_ . 'currency`'
            );

            foreach ($countries as $country) {
                if ($country['id_currency'] == 0) {
                    self::$currency_cache[$country['id_country']] = array(
                        'id_currency' => $this->context->currency->id,
                        'iso_code' => $this->context->currency->iso_code,
                        'conversion_rate' => $this->context->currency->conversion_rate,
                    );
                } else {
                    foreach ($result as $currency) {
                        if ($country['id_currency'] == $currency['id_currency']) {
                            self::$currency_cache[$country['id_country']] = array(
                                'id_currency' => $currency['id_currency'],
                                'iso_code' => $currency['iso_code'],
                                'conversion_rate' => $currency['conversion_rate'],
                            );
                        } else {
                            continue;
                        }
                    }
                }
            }
            GoogleTinyCache::setCache('currency', self::$currency_cache);
            unset($countries, $country, $result, $currency);
        }
    }

    /**
     * Get currency
     * @return Array currencie by country
     */
    private function getcarrierCache()
    {
        self::$carrier_cache = GoogleTinyCache::getCache('carrier');

        if (self::$carrier_cache === null || empty(self::$carrier_cache)) {
            $carrier = array();
            $carrier_list = array();
            $zone = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
                SELECT id_zone
                FROM `' . _DB_PREFIX_ . 'zone`
                WHERE active = 1');

            foreach ($zone as $id_zone) {
                $carrier_list = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
                SELECT cz.id_zone ,c.id_carrier, c.name
                FROM `' . _DB_PREFIX_ . 'carrier` c
                LEFT JOIN `' . _DB_PREFIX_ . 'carrier_zone` cz ON (c.`id_carrier` = cz.`id_carrier`)
                WHERE cz.id_zone = ' . (int) $id_zone['id_zone'] . '
                AND c.active = 1
                AND c.deleted = 0');
                if (!empty($carrier_list[0])) {
                    array_push($carrier, $carrier_list[0]);
                }
            }

            if (empty($carrier)) {
                $carrier = $carrier_list[0];
            }

            self::$carrier_cache = $carrier;
            GoogleTinyCache::setCache('carrier', self::$carrier_cache);
        }
    }

    private function getActiveCountriesByIdShop($id_shop, $id_lang, $active = false)
    {
        $countries = array();
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
            SELECT cl.id_country, c.id_zone, cl.id_lang, cl.name, c.id_country, c.id_currency, c.iso_code, c.active
            FROM `' . _DB_PREFIX_ . 'country` c ' . Shop::addSqlAssociation('country', 'c') . '
            LEFT JOIN `' . _DB_PREFIX_ . 'country_shop` cs ON (cs.`id_country`= c.`id_country`)
            LEFT JOIN `' . _DB_PREFIX_ . 'country_lang` cl ON (c.`id_country` = cl.`id_country` AND cl.`id_lang` = ' . (int) $id_lang . ')
            WHERE 1' . ($active ? ' AND c.active = 1' : '') . ($id_shop ? ' AND cs.`id_shop` = ' . (int) $id_shop : '') . '
            ORDER BY cl.name ASC'
        );

        foreach ($result as $row) {
            $countries[$row['id_country']] = $row;
        }

        unset($result, $row);

        return $countries;
    }

    /**
     * Get cacheFactory
     * Set all needed require
     */
    private function cacheFactory()
    {
        $this->getCountriesCache();
        $this->getLangCache();
        $this->getCategoryListCache();
        $this->getcurrencyCache();
        $this->getcarrierCache();
    }

    /**
     * Get Countries
     * @return array Countries
     */
    private function getCountriesCache()
    {
        $id_shop = (int) $this->context->shop->id;
        self::$countries_cache = GoogleTinyCache::getCache('countries');

        if (self::$countries_cache === null || empty(self::$countries_cache)) {
            $id_lang = (int) $this->context->language->id;

            if ($id_shop !== false && Shop::isFeatureActive()) {
                Shop::setContext(Shop::CONTEXT_SHOP, (int) $id_shop);
            }

            self::$countries_cache = $this->getActiveCountriesByIdShop($id_shop, $id_lang, true);

            // Cache Data
            GoogleTinyCache::setCache('countries', self::$countries_cache);
        }
    }

    /**
     * Get Language
     */
    private function getLangCache()
    {
        self::$lang_cache = GoogleTinyCache::getCache('language');

        if (self::$lang_cache === null || empty(self::$lang_cache)) {
            if ($languages = Language::getLanguages()) {
                foreach ($languages as &$row) {
                    $exprow = explode(' (', $row['name']);
                    $subtitle = (isset($exprow[1]) ? trim(Tools::substr($exprow[1], 0, -1)) : '');
                    $upper = Tools::strtoupper(Tools::substr($row['language_code'], 3, 5));
                    $row['language_code'] = substr_replace($row['language_code'], $upper, 3, 5);
                    self::$lang_cache[$row['iso_code']] = array(
                        'id' => (int) $row['id_lang'],
                        'title' => trim($exprow[0]),
                        'subtitle' => $subtitle,
                        'language_code' => $row['language_code'],
                    );
                }
                // Cache Data
                GoogleTinyCache::setCache('language', self::$lang_cache);
                // Clean memory
                unset($row, $exprow, $subtitle, $languages);
            }
        }
    }

    /**
     * Get Cache fot taxonomy
     */
    private function getTaxonomyCache()
    {
        $language = self::$lang_cache;

        // Test if the Lang cache is already and build it if require.
        if (!$language) {
            $this->getLangCache();
            $language = self::$lang_cache;
        }

        foreach ($language as $row) {
            $language_code = $row['language_code'];
            $cacheName = 'taxonomy_' . $language_code;

            //Test cache
            $data = GoogleTinyCache::getCache($cacheName);

            if (!$data) {
                $data = Tools::file_get_contents('http://www.google.com/basepages/producttype/taxonomy-with-ids.' . $language_code . '.txt', FILE_USE_INCLUDE_PATH);

                if ($data) {
                    $data = explode("\n", $data);
                    GoogleTinyCache::setCache($cacheName, $data, 48);
                }
            }
            if (!$data) {
                $data = GoogleTinyCache::getCache('taxonomy_en-US');
                if (!$data) {
                    $data = Tools::file_get_contents('http://www.google.com/basepages/producttype/taxonomy-with-ids.en-US.txt', FILE_USE_INCLUDE_PATH);
                    $data = explode("\n", $data);
                    GoogleTinyCache::setCache('taxonomy_en-US', $data, 48);
                }
            }
        }
    }

    /**
     * Get CategoryList
     * @return array Lang
     */
    private function getCategoryListCache()
    {

        $cacheName = 'categories_shop' . (int) $this->context->shop->id;

        //test cache
        $data = GoogleTinyCache::getCache($cacheName);

        //if no cache
        if (!$data) {
            //get data
            $data = $this->getSimpleCategories();

            //else set cache
            if ($data) {
                GoogleTinyCache::setCache($cacheName, $data, 48);
            }
        }

        //assign data to static var
        if (!$data) {
            $data = array();
        }

        self::$categories_cache = $data;
    }

    /**
     * Install SQL
     * @return boolean
     */
    public function installSQL()
    {
        // Create database tables from install.sql
        if (!file_exists($this->sql_path . self::INSTALL_SQL_FILE)) {
            return false;
        }

        if (!$sql = Tools::file_get_contents($this->sql_path . self::INSTALL_SQL_FILE)) {
            return false;
        }

        $replace = array(
            'PREFIX' => _DB_PREFIX_,
            'ENGINE_DEFAULT' => _MYSQL_ENGINE_,
        );
        $sql = strtr($sql, $replace);
        $sql = preg_split("/;\s*[\r\n]+/", $sql);

        foreach ($sql as &$q) {
            if ($q && count($q) && !Db::getInstance()->Execute(trim($q))) {
                return false;
            }
        }

        // Clean memory
        unset($sql, $q, $replace);

        return true;
    }

    /**
     * Uninstall SQL
     * @return boolean
     */
    private function uninstallSQL()
    {
        // Create database tables from uninstall.sql
        if (!file_exists($this->sql_path . self::UNINSTALL_SQL_FILE)) {
            return false;
        }

        if (!$sql = Tools::file_get_contents($this->sql_path . self::UNINSTALL_SQL_FILE)) {
            return false;
        }

        $replace = array(
            'PREFIX' => _DB_PREFIX_,
            'ENGINE_DEFAULT' => _MYSQL_ENGINE_,
        );
        $sql = strtr($sql, $replace);
        $sql = preg_split("/;\s*[\r\n]+/", $sql);

        foreach ($sql as &$q) {
            if ($q && count($q) && !Db::getInstance()->Execute(trim($q))) {
                return false;
            }
        }

        // Clean memory
        unset($sql, $q, $replace);

        return true;
    }

    /**
     * Refresh Table data
     * @return boolean
     * */
    public function refreshTableSQLData()
    {
        $id_shop = (int) $this->context->shop->id;
        $data = array();

        $category_cache = 'categories_shop' . (int) $id_shop;

        foreach (self::$countries_cache as $country) {
            foreach (self::$categories_cache as $category) {
                $sql = 'SELECT SQL_SMALL_RESULT id_object, active
                FROM `' . _DB_PREFIX_ . bqSQL('module_gshopping_table') . '` mgt
                WHERE mgt.id_country = ' . (int) ($country['id_country']) . '
                AND mgt.id_category = ' . (int) ($category['id_category']) . '
                AND mgt.id_shop = ' . (int) ($id_shop);

                $object = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

                $data[] = array(
                    'id_object' => isset($object[0]['id_object']) ? $object[0]['id_object'] : null,
                    'id_country' => $country['id_country'],
                    'id_shop' => $id_shop,
                    'id_category' => $category['id_category'],
                    'active' => isset($object[0]['active']) ? $object[0]['active'] : 0,
                );

                unset($object);
            }
        }

        $this->replace('module_gshopping_table', $data, true, true);
        unset($country, $category, $data);
    }

    /**
     * Install Tab
     * @return boolean
     */
    public function installTab()
    {
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = 'AdminGShopping';
        $tab->name = array();
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = 'Gshopping';
        }
        unset($lang);
        $tab->id_parent = -1;
        $tab->module = $this->name;
        return $tab->add();
    }

    /**
     * Uninstall Tab
     * @return boolean
     */
    public function uninstallTab()
    {
        $id_tab = (int) Tab::getIdFromClassName('AdminGShopping');
        if ($id_tab) {
            $tab = new Tab($id_tab);
            if (Validate::isLoadedObject($tab)) {
                return $tab->delete();
            } else {
                return false;
            }
        } else {
            return true;
        }
    }

    /**
     * Check MySQL Engine
     * @return boolean
     */
    public function isMyisam()
    {
        if (_MYSQL_ENGINE_ === 'MyISAM') {
            return true;
        }

        return false;
    }

    /**
     * Insert module into datable
     * @return boolean result
     */
    public function install()
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        // Clean up cache
        GoogleTinyCache::clearAllCache();

        // Set a value for the modal
        if (!Configuration::get('GSHOPPING_MODULE_SCHOOL')) {
            Configuration::updateValue('GSHOPPING_MODULE_SCHOOL', 0);
        }

        if (
            parent::install() === false ||
            $this->registerHook('actionObjectCategoryAddAfter') === false ||
            $this->registerHook('actionObjectCategoryUpdateAfter') === false ||
            $this->registerHook('actionObjectCategoryDeleteAfter') === false ||
            $this->registerHook('actionObjectCarrierAddAfter') === false ||
            $this->registerHook('actionObjectCarrierUpdateAfter') === false ||
            $this->registerHook('actionObjectCarrierDeleteAfter') === false ||
            $this->registerHook('actionObjectCurrencyAddAfter') === false ||
            $this->registerHook('actionObjectCurrencyUpdateAfter') === false ||
            $this->registerHook('actionObjectCurrencyDeleteAfter') === false ||
            $this->registerHook('actionObjectProductAddAfter') === false ||
            $this->registerHook('actionObjectProductUpdateAfter') === false ||
            $this->registerHook('actionObjectProductDeleteAfter') === false ||
            $this->registerHook('actionObjectCountryAddAfter') === false ||
            $this->registerHook('actionObjectCountryUpdateAfter') === false ||
            $this->registerHook('actionObjectCountryDeleteAfter') === false ||
            $this->registerHook('actionObjectLanguageAddAfter') === false ||
            $this->registerHook('actionObjectLanguageUpdateAfter') === false ||
            $this->registerHook('actionObjectLanguageDeleteAfter') === false ||
            $this->registerHook('displayHeader') === false ||
            $this->registerHook('displayBackOfficeHeader') === false ||
            $this->installSQL() === false || $this->installTab() === false ||
            $this->refreshTableSQLData() === false ||
            $this->genTaxonomyCache() === false ||
            $this->getToken() === false ||
            $this->getExportPath() === false
        ) {
            return false;
        }

        return true;
    }

    /**
     * Delete module from datable
     * @return boolean result
     */
    public function uninstall()
    {
        if (parent::uninstall() === false || $this->uninstallSQL() === false || $this->uninstallTab() === false) {
            return false;
        }

        return true;
    }

    /**
     * Loads asset resources
     */
    public function loadAsset()
    {
        $css_compatibility = $js_compatibility = array();

        // Load CSS
        $css = array(
            $this->css_path . 'font-awesome.min.css',
            $this->css_path . 'bootstrap-select.min.css',
            $this->css_path . 'bootstrap-dialog.min.css',
            $this->css_path . 'bootstrap.vertical-tabs.min.css',
            $this->css_path . 'bootstrap-responsive.min.css',
            $this->css_path . 'DT_bootstrap.css',
            $this->css_path . 'jstree.min.css',
            $this->css_path . 'faq.css',
            $this->css_path . 'moduleschool.css',
            $this->css_path . $this->name . '.css',
        );

        $this->context->controller->addCSS($css, 'all');

        // Load JS
        $jss = array(
            $this->js_path . 'bootstrap-select.min.js',
            $this->js_path . 'bootstrap-dialog.js',
            $this->js_path . 'jquery.autosize.min.js',
            $this->js_path . 'jquery.dataTables.js',
            $this->js_path . 'jquery.smartWizard.js',
            $this->js_path . 'DT_bootstrap.js',
            $this->js_path . 'dynamic_table_init.js',
            $this->js_path . 'jstree.min.js',
            $this->js_path . 'faq.js',
            $this->js_path . $this->name . '.js',
        );

        if (method_exists($this->context->controller, 'addJquery')) {
            $this->context->controller->addJquery('2.1.0', $this->js_path);
        }

        $this->context->controller->addJS($jss);

        // Clean memory
        unset($jss, $css, $js_compatibility, $css_compatibility);
    }

    public function getDocLang()
    {
        $iso_code = Context::getContext()->language->iso_code;
        $lang = 'EN';

        if ($iso_code == 'fr' || $iso_code == 'FR') {
            $lang = 'FR';
        }

        if ($iso_code == 'es' || $iso_code == 'ES') {
            $lang = 'ES';
        }

        return $lang;
    }

    /**
     * Show the configuration module
     */
    public function getContent()
    {
        $this->refreshTableSQLData();
        $countries_key = array_keys(self::$countries_cache);
        $country_default = array_shift($countries_key);

        // We load asset
        $this->loadAsset();

        $controller_name = 'AdminGShopping';
        $current_id_tab = (int) $this->context->controller->id;
        $controller_url = $this->context->link->getAdminLink($controller_name);

        $lang = $this->getDocLang();

        $this->context->smarty->assign(array(
            'module_name' => $this->name,
            'module_version' => $this->version,
            'rule_history' => $this->history,
            'debug_mode' => (int) _PS_MODE_DEV_,
            'lang_select' => self::$lang_cache,
            'countries' => self::$countries_cache,
            'country_default' => $country_default,
            'current_id_tab' => $current_id_tab,
            'controller_url' => $controller_url,
            'controller_name' => $controller_name,
            'module_display' => $this->displayName,
            'multishop' => (int) Shop::isFeatureActive(),
            'guide_link' => 'docs/documentation_gshopping_' . $lang . '.pdf',
            'table_tpl_path' => $this->admin_tpl_path . 'table/table.tpl',
            'actions_tpl_path' => $this->admin_tpl_path . 'table/actions.tpl',
            'ps_version' => (bool) version_compare(_PS_VERSION_, '1.6', '>'),
            'rewriting_allow' => (int) Configuration::get('PS_REWRITING_SETTINGS'),
            'gshopping_module_school' => (int) Configuration::get('GSHOPPING_MODULE_SCHOOL'),
        ));

        return $this->display(__FILE__, 'views/templates/admin/configuration.tpl');
    }

    /**
     * Switch the status of one rule
     */
    public function switchAction($id_object, $id_country, $active = 0)
    {
        $check = $this->checkConfiguration($id_country, $id_object);
        if ($check !== 1) {
            return 0;
        }

        $status = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
            SELECT SQL_SMALL_RESULT mgt.active FROM `' . _DB_PREFIX_ . bqSQL(self::$categories_table) . '` mgt WHERE mgt.id_object = ' . (int) $id_object
        );

        if ((int) $status === 1 && $active === 0) {
            $data = array('active' => 0);
        } else {
            $data = array('active' => 1);
        }

        $data['id_object'] = (int) $id_object;

        return ($this->update(pSQL(self::$categories_table), $data));
    }

    /**
     * Counts the number of object with respect to the previous query
     * See DataTables (http://goo.gl/C5ho60)
     * @return int
     */
    public function countRules($type = 'product')
    {
        return (int) Db::getInstance()->getValue('SELECT SQL_SMALL_RESULT FOUND_ROWS() `' . trim(bqSQL($type)) . '`');
    }

    /**
     * Get all categories with childs
     * @return array
     */
    public function getSimpleCategories()
    {
        // Remove root only if storeCommander is not installed
        $root = '';
        $storecommander = Module::getInstanceByName('storecommander');

        if (empty($storecommander) || !file_exists(_PS_MODULE_DIR_ . 'storecommander/storecommander.php')) {
            $root = 'AND c.`id_category` != ' . (int) Configuration::get('PS_ROOT_CATEGORY') . ' AND c.`active` = 1 AND c.`id_category` <> 2';
        }

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            'SELECT SQL_BIG_RESULT c.`id_parent`, c.`id_category`, cl.`name`
            FROM `' . _DB_PREFIX_ . 'category` c
            LEFT JOIN `' . _DB_PREFIX_ . 'category_lang` cl ON (c.`id_category` = cl.`id_category`' . Shop::addSqlRestrictionOnLang('cl') . ')
            ' . Shop::addSqlAssociation('category', 'c') . '
            WHERE cl.`id_lang` = ' . (int) $this->context->language->id . '
            ' . $root . '
            GROUP BY c.id_category
            ORDER BY c.`id_category`, category_shop.`position`'
        );
    }

    /**
     * Get all category table
     * @return array
     */
    public function getHistory($filter = '', $order = '', $limit = '', $id_country = '')
    {
        if (!$this->active) {
            return;
        }

        $calc = '';
        $numargs = func_num_args();
        if ($numargs > 1) {
            $calc = 'SQL_BIG_RESULT SQL_CALC_FOUND_ROWS';
        }

        $sql = 'SELECT ' . $calc . ' mgt.id_object, mgt.id_country, mgt.id_category, mgt.id_shop, cl.name, mgt.active
            FROM `' . _DB_PREFIX_ . bqSQL(self::$categories_table) . '` mgt
            LEFT JOIN ' . _DB_PREFIX_ . 'category cat ON (mgt.id_category = cat.id_category)
            LEFT JOIN ' . _DB_PREFIX_ . 'category_lang cl ON ( mgt.id_category = cl.id_category )
            LEFT JOIN ' . _DB_PREFIX_ . 'shop s ON (mgt.id_shop = s.id_shop)
            AND mgt.id_shop = "' . (int) $this->context->shop->id . '"
            WHERE mgt.id_country = ' . (int) ($id_country) . '
            ' . $filter . '
            GROUP BY mgt.id_object
            ' . (!empty($order) ? pSQL($order) : 'ORDER BY mgt.id_object ASC') . pSQL($limit);

        if (!empty($sql)) {
            return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        } else {
            return (array());
        }
    }

    /**
     * Get country repertory
     */
    public function getRepertoryForCategory()
    {
        $countries = self::$countries_cache;
        foreach ($countries as $key => $val) {
            mkdir($this->export_path . $val['iso_code'], '755');
        }
        unset($countries, $key, $val);
    }

    /**
     * Get country repertory
     */
    public function checkConfiguration($id_country, $id_object)
    {
        (int) $id_shop = $this->context->shop->id;
        $country = self::$countries_cache;
        $country_iso = $country[$id_country]['iso_code'];
        $filename = $this->export_path . $id_shop . '/' . $country_iso . '/' . (int) $id_object . '.txt';

        if (Validate::isLanguageIsoCode($country_iso) === false) {
            return false;
        }

        if (file_exists($filename)) {
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * Get id_object by category
     * @param int $id_category
     * @return array $id_object
     */
    protected function getIdObjectByIdCategory($id_category)
    {
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
            SELECT `id_object`
            FROM `' . _DB_PREFIX_ . bqSQL(self::$categories_table) . '`
            WHERE `id_category` = ' . (int) $id_category
        );
    }

    /**
     * Delete id_object in table
     * @param id_category
     */
    public function removeCategory($id_category)
    {
        $arrayObject = $this->getIdObjectByIdCategory($id_category);

        Db::getInstance(_PS_USE_SQL_SLAVE_)->execute(
            'DELETE
            FROM `' . _DB_PREFIX_ . bqSQL(self::$google_category_table) . '`
            WHERE id_category = ' . (int) $id_category);

        foreach ($arrayObject as $value) {
            Db::getInstance(_PS_USE_SQL_SLAVE_)->execute('
                DELETE FROM `' . _DB_PREFIX_ . bqSQL(self::$categories_table) . '`
                WHERE id_object = ' . (int) $value['id_object']
            );

            Db::getInstance(_PS_USE_SQL_SLAVE_)->execute('
                DELETE FROM `' . _DB_PREFIX_ . bqSQL(self::$attributes_table) . '`
                WHERE id_object = ' . (int) $value['id_object']);
        }
    }

    /**
     * Load the Taxonomy for modal
     * @return html
     */
    public function loadTaxonomy($id_lang, $sub = false)
    {

        // Get lang for the taxonomy
        if ($id_lang) {
            $iso_code = Language::getIsoById($id_lang);
        } else {
            $langage_code = 'en-US';
        }

        $iso_upper = Tools::strtoupper($iso_code);
        $langage_code = $iso_code . '-' . $iso_upper;

        switch ($langage_code) {
            // Exception for Spanish
            case 'gl-GL';
                $langage_code = 'es-ES';
                break;
            case 'ca-CA';
                $langage_code = 'es-ES';
                break;
            case 'mx-MX';
                $langage_code = 'es-ES';
                break;

            // Exception for english
            case 'en-GB';
                $langage_code = 'en-US';
                break;

            // Exception for ireland
            case 'en-IE';
                $langage_code = 'en-US';
                break;

            // Exception for Sweden
            case 'sv-SV';
                $langage_code = 'sv-SE';
                break;

            // Exception for Cz
            case 'cs-CS';
                $langage_code = 'cs-CZ';
                break;

            // Exception for french Canadian
            case 'ca-CA';
                $langage_code = 'fr-FR';
                break;

            // Exception for Japan
            case 'ja-JA';
                $langage_code = 'ja-JP';
                break;

            // Exception for Brazil
            case 'br-BR';
                $langage_code = 'pt-BR';
                break;

            // Exception for Dansk
            case 'da-DA';
                $langage_code = 'da-DK';
                break;
        }

        $data_res = '<option value="">' . $this->l('Nothing selected') . '</option>';
        $data = GoogleTinyCache::getCache('taxonomy_' . $langage_code);

        //Set cache if not exited
        if (!$data) {
            $this->getTaxonomyCache($langage_code);
        }if (!$data) {
            $data = GoogleTinyCache::getCache('taxonomy_en-US');
        }

        if ($sub == false) {
            foreach ($data as $key => $val) {
                // Mother categories for the first level of the select
                if (strpos($val, 'Google_')) {
                    continue;
                }
                if (strpos($val, '>')) {
                    continue;
                }
                $id = explode('-', $val);
                // Can't do that in the tpl, too heavy.
                $data_res .= '<option value="' . $val . '">' . $id[1] . '</option>';
            }
        } else {
            $sub = explode('-', $sub);
            foreach ($data as $key => $categories_list) {
                $val = explode('-', $categories_list);
                // Sub-categories for the second level of the select
                if (strpos($categories_list, 'Google_')) {
                    continue;
                }

                if (!strpos($categories_list, '>')) {
                    continue;
                }
                if (strpos($val[1], $sub[1]) === false) {
                    continue;
                }
                $val[1] = str_replace($sub[1] . ' >', '', $val[1]);

                // Can't do that in the tpl, too heavy.
                $data_res .= '<option value="' . $val[0] . '">' . $val[1] . '</option>';
            }
        }
        return $data_res;
    }

    /**
     * Load the Attributes for modal
     *
     * @param int $id_lang
     * @param int $id_category
     * @return html
     */
    public function loadAttributes($id_lang, $category)
    {
        // Check if the category require attributes, this values can change with the time.

        $lexicon = array(
            '166 - Vêtements et accessoires', //Fr
            '166 - Apparel & Accessories', //En
            '166 - Bekleidung & Accessoires', //De
            '166 - Vestuário e acessórios', //Br
        );
        $firstline = array();
        $firstline[] = array(
            'public_name' => $this->l('Nothing Selected'),
            'id' => '',
        );

        foreach ($lexicon as $cat) {
            if (trim($category) === $cat) {
                $sqlFeature = 'SELECT SQL_BIG_RESULT fl.name as public_name, fl.id_feature as id
                FROM `' . _DB_PREFIX_ . 'feature_lang` fl
                WHERE fl.id_lang = ' . (int) $id_lang;
                $features_res = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sqlFeature);

                // Format the data for the merge
                $features = array();
                foreach ($features_res as $key => $value) {
                    $value['id'] = 'feature_' . $value['id'];
                    $features[] = $value;
                }
                unset($features_res, $key, $value);

                $sqlAttributes = 'SELECT SQL_BIG_RESULT agl.public_name, agl.id_attribute_group as id
                FROM `' . _DB_PREFIX_ . 'attribute_group_lang` agl
                WHERE agl.id_lang = ' . (int) $id_lang;
                $attributes_res = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sqlAttributes);

                // Format the data for the merge
                $attributes = array();
                foreach ($attributes_res as $key => $value) {
                    $value['id'] = 'attribute_' . $value['id'];
                    $attributes[] = $value;
                }
                unset($attributes_res, $key, $value);

                $data_res = array_merge($firstline, $attributes, $features);

                return ($data_res);
            }
        }
        unset($cat, $lexicon);
    }

    /**
     * Load the different value for Attributes by ID and lang
     * @return html
     */
    public function loadAttributesDetails($id_lang, $id_attributes, $type)
    {
        $value = array();

        if (strpos($id_attributes, 'attribute_') !== false) {
            $group_id = Tools::str_replace_once('attribute_', '', $id_attributes);
            $sqlAttributeValue = 'SELECT al.name, a.id_attribute
            FROM `' . _DB_PREFIX_ . 'attribute` a
            INNER JOIN ' . _DB_PREFIX_ . 'attribute_lang al ON (a.`id_attribute` = al.`id_attribute`)
            WHERE a.id_attribute_group = ' . (int) $group_id . '
            AND al.id_lang = ' . (int) $id_lang;
            $value = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sqlAttributeValue);
        } elseif (strpos($id_attributes, 'feature_') !== false) {
            $group_id = Tools::str_replace_once('feature_', '', $id_attributes);

            $sqlFeatureValue = 'SELECT fvl.value as name, fvl.id_feature_value as id_attribute
            FROM `' . _DB_PREFIX_ . 'feature_value_lang` fvl
            INNER JOIN ' . _DB_PREFIX_ . 'feature_value fv ON (fv.`id_feature_value` = fvl.`id_feature_value`)
            WHERE fv.id_feature = ' . (int) $group_id . '
            AND fvl.id_lang = ' . (int) $id_lang;
            $value = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sqlFeatureValue);
        }

        $value['attr'] = $value;
        $value['type'] = $type;

        return (Tools::jsonEncode($value));
    }

    /**
     * Generate cache for the taxonomy
     * @return none
     */
    public function genTaxonomyCache()
    {
        $this->getTaxonomyCache();
    }

    /**
     * Load the form template file
     * @return html
     */
    public function loadModuleSchool()
    {
        $lang = $this->getDocLang();
        $this->context->smarty->assign(array(
            'guide_link' => '/docs/documentation_gshopping_' . $lang . '.pdf',
        ));

        Configuration::updateValue('GSHOPPING_MODULE_SCHOOL', 1);

        return $this->display(__FILE__, 'views/templates/admin/moduleschool/moduleschool.tpl');
    }

    /**
     * Load the form template file
     * @return html
     */
    public function loadForm($id_object, $id_category, $cat_name, $id_country)
    {
        $lang = $this->getDocLang();

        $this->context->smarty->assign(array(
            'object' => $id_object,
            'presta_cat' => $cat_name,
            'id_category' => $id_category,
            'lang_select' => self::$lang_cache,
            'countries' => self::$countries_cache,
            'id_country' => $id_country,
            'guide_link' => '/docs/documentation_gshopping_' . $lang . '.pdf',
        ));

        return $this->display(__FILE__, 'views/templates/admin/forms/form.tpl');
    }

    /**
     * Load the status of a rule with an icon
     *
     * @param int $status
     * @return html
     */
    public function loadStatus($actions, $status)
    {
        $this->context->smarty->assign(array(
            'prod' => $actions,
            'status' => $status,
        ));

        return $this->display(__FILE__, 'views/templates/admin/table/status.tpl');
    }

    /**
     * Load action buttons that apply, modify or delete the rule
     *
     * @param array $actions
     * @param string $type
     * @param string $role
     * @return html
     */
    public function loadActions($actions, $id_country, $id_object)
    {
        $check = $this->checkConfiguration($id_country, $id_object);

        $this->context->smarty->assign(array(
            'prod' => $actions,
            'check' => $check,
        ));

        return $this->display(__FILE__, 'views/templates/admin/table/actions.tpl');
    }

    /**
     * Load action buttons that apply, export information per country
     *
     * @param array $id_country
     * @return html
     */
    public function loadExportInfo($id_country)
    {
        if ($id_country === 0) {
            $countries_key = array_keys(self::$countries_cache);
            $id_country = array_shift($countries_key);
        }

        $category = 'SELECT SQL_SMALL_RESULT COUNT(id_object)
        FROM `' . _DB_PREFIX_ . bqSQL(self::$categories_table) . '` msr
        WHERE msr.id_country = ' . (int) $id_country . '
        AND msr.active = 1';

        $category_quantity = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($category);

        $id_shop = $this->context->shop->id;
        $table = GShopping::$categories_table;

        $enable = new GoogleFeedConverter(0, 0);
        $category_enable = $enable->getActiveExportCategory($id_country, $id_shop, $table);
        $counter = 0;
        $where = '';

        if (!empty($category_enable)) {
            foreach ($category_enable as $value) {
                $counter++;
                $id_category = $value['id_category'];
                $where .= 'id_category_default =' . (int) $id_category;
                if ($category_quantity - $counter != 0) {
                    $where .= ' OR ';
                }
            }

            $product_quantity = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT COUNT( id_product )
            FROM  `' . _DB_PREFIX_ . 'product`
            WHERE (' . $where . ')'
            );
        } else {
            $product_quantity = 0;
            $category_quantity = 0;
        }

        $this->context->smarty->assign(array(
            'category_quantity' => $category_quantity,
            'product_quantity' => $product_quantity,
        ));

        return $this->display(__FILE__, '/views/templates/admin/export/info.tpl');
    }

    /**
     * Load action buttons that apply, export link per country
     *
     * @param array $id_country
     * @return html
     */
    public function loadExportLink($id_country)
    {
        if ($id_country === 0) {
            $countries_key = array_keys(self::$countries_cache);
            $id_country = array_shift($countries_key);
        }

        $token = Configuration::get('token');
        $country = self::$countries_cache;
        $country_name = $country[$id_country]['name'];
        $country_iso = $country[$id_country]['iso_code'];
        $id_shop = (int) $this->context->shop->id;

        $link = Tools::getShopDomain(true) . __PS_BASE_URI__ . 'modules/gshopping/export.php?country=' . $country_iso . '&id_country=' . $id_country . '&id_shop=' . $id_shop . '&token=' . $token;
        $this->context->smarty->assign(array(
            'country_name' => $country_name,
            'country_link' => $link,
            'id_shop' => $id_shop,
        ));

        return $this->display(__FILE__, '/views/templates/admin/export/link.tpl');
    }

    public function confirmation()
    {
        return $this->display(__FILE__, '/views/templates/admin/forms/confirmation.tpl');
    }

    /*     * ************************* */
    /*              CRUD              */
    /*     * ************************* */

    /*
     * Replace the attributes preference to database (add or update)
     *
     * @param string $table
     * @param array $data
     */

    public function replaceDetails($table, $data)
    {
        foreach ($data as $dat) {
            $vals = array_values($dat);
            $vals = array_map('pSQL', $vals);

            $sql = 'REPLACE INTO `' . _DB_PREFIX_ . bqSQL($table) . '`';
            $sql .= ' (id_parameter, id_object, type, type_attribute, attribute_value) VALUES';
            $sql .= " ('" . implode("', '", array_values($vals)) . "'),";
            $sql = rtrim($sql, ',') . ';';
            unset($data, $keys, $vals);
            Db::getInstance()->execute($sql);
        }
        unset($data, $dat);
    }

    /**
     * Replac current object to database (add or update)
     *
     * @param string $table
     * @param array $data
     */
    public function replaceObj($table, $data)
    {
        $keys = array_keys($data);
        $keys = array_map('bqSQL', $keys);
        $vals = array_values($data);
        $vals = array_map('pSQL', $vals);

        $sql = 'REPLACE INTO `' . _DB_PREFIX_ . bqSQL($table) . '`';
        $counter_meta = 0;
        if ($counter_meta === 0) {
            $sql .= ' (`' . implode('`, `', $keys) . '`) VALUES';
        }
        $sql .= " ('" . implode("', '", array_values($vals)) . "'),";
        $counter_meta++;
        $sql = rtrim($sql, ',') . ';';
        unset($data, $keys, $vals);

        if (Db::getInstance()->execute($sql)) {
            return Db::getInstance()->Insert_ID();
        }
    }

    public function replace($table, $data)
    {
        $sql = 'REPLACE INTO `' . _DB_PREFIX_ . bqSQL($table) . '`';
        if (is_array($data)) {
            $counter_meta = 0;
            foreach ($data as $dat) {
                $keys = array_keys($dat);
                $keys = array_map('bqSQL', $keys);
                $vals = array_values($dat);
                $vals = array_map('pSQL', $vals);
                if ($counter_meta === 0) {
                    $sql .= ' (`' . implode('`, `', $keys) . '`) VALUES';
                }
                $sql .= " ('" . implode("', '", array_values($vals)) . "'),";
                $counter_meta++;
            }
        } else {
            $keys = array_keys($data);
            $keys = array_map('bqSQL', $keys);
            $vals = array_values($data);
            $vals = array_map('pSQL', $vals);

            $counter_meta = 0;
            if ($counter_meta === 0) {
                $sql .= ' (`' . implode('`, `', $keys) . '`) VALUES';
            }
            $sql .= " ('" . implode("', '", array_values($vals)) . "'),";
            $counter_meta++;
            unset($data, $keys, $vals);
        }
        $sql = rtrim($sql, ',');
        if (Db::getInstance()->execute($sql)) {
            return Db::getInstance()->Insert_ID();
        }
    }

    /**
     * Save current object to database (add or update)
     *
     * @param string $table
     * @param int $id_obj
     * @param array $data
     */
    public function saveObj($table, $data)
    {
        $keys = array_keys($data);
        $keys = array_map('bqSQL', $keys);
        $vals = array_values($data);
        $vals = array_map('pSQL', $vals);

        $sql = 'INSERT INTO `' . _DB_PREFIX_ . bqSQL($table) . '`';
        $counter_meta = 0;
        if ($counter_meta === 0) {
            $sql .= ' (`' . implode('`, `', $keys) . '`) VALUES';
        }

        $sql .= " ('" . implode("', '", array_values($vals)) . "'),";
        $counter_meta++;
        $sql = rtrim($sql, ',') . ';';
        unset($data, $keys, $vals);
        if (Db::getInstance()->execute($sql)) {
            return Db::getInstance()->Insert_ID();
        }
    }

    /**
     * Save current object to database
     *
     * @param string $table
     * @param array $data
     * @return boolean Insertion result
     */
    public function save($table, $data)
    {
        $sql = 'INSERT INTO `' . _DB_PREFIX_ . bqSQL($table) . '`';

        if (is_array($data)) {
            $counter_meta = 0;
            foreach ($data as $dat) {
                $keys = array_keys($dat);
                $keys = array_map('bqSQL', $keys);
                $vals = array_values($dat);
                $vals = array_map('pSQL', $vals);

                if ($counter_meta === 0) {
                    $sql .= ' (`' . implode('`, `', $keys) . '`) VALUES';
                }
                $sql .= " ('" . implode("', '", array_values($vals)) . "'),";
                $counter_meta++;
            }
        } else {
            $keys = array_keys($data);
            $keys = array_map('bqSQL', $keys);
            $vals = array_values($data);
            $vals = array_map('pSQL', $vals);

            $counter_meta = 0;
            if ($counter_meta === 0) {
                $sql .= ' (`' . implode('`, `', $keys) . '`) VALUES';
            }
            $sql .= " ('" . implode("', '", array_values($vals)) . "'),";
            $counter_meta++;
            unset($data, $keys, $vals);
        }
        $sql = rtrim($sql, ',') . ';';

        if (Db::getInstance()->execute($sql)) {
            return Db::getInstance()->Insert_ID();
        }
    }

    /**
     * Update current object to database
     *
     * @param string $table
     * @param array $data
     * @return boolean Insertion result
     */
    public function update($table, $data, $active_false = false, $id_lang_false = false)
    {
        $set = $where = '';
        $sql = 'UPDATE `' . _DB_PREFIX_ . bqSQL($table) . '` SET ';
        $counter_meta = 0;

        foreach ($data as $key => $value) {
            if ($key === 'pattern') {
                $set = '`' . bqSQL($key) . '` = "' . pSQL($value) . '"';
            } elseif ($key === 'date_upd' || $key === 'name') {
                $counter_meta = -1;
                $set = '`' . bqSQL($key) . '` = "' . pSQL($value) . '"';
            } elseif ($key === 'active') {
                if ($active_false === false) {
                    $counter_meta = -1;
                    $set = '`' . bqSQL($key) . '` = ' . (int) $value;
                }
            } elseif ($key === 'id_lang_export') {
                if ($id_lang_false === false) {
                    $counter_meta = -1;
                    $set = '`' . bqSQL($key) . '` = ' . (int) $value;
                }
            } elseif ($key !== 'field') {
                if ($counter_meta === 0) {
                    $where .= ' WHERE `' . bqSQL($key) . '` = ' . (int) $value;
                } else {
                    $where .= ' AND `' . bqSQL($key) . '` = ' . (int) $value;
                }
            } else {
                $where .= ' AND `' . bqSQL($key) . '` = "' . pSQL($value) . '"';
            }

            $counter_meta++;
        }
        unset($key, $value, $data);

        return Db::getInstance()->execute($sql . $set . $where);
    }

    /**
     * Update current object to database
     *
     * @param string $table
     * @param array $data
     * @return boolean Insertion result
     */
    public function updateLang($table, $data)
    {
        $set = $where = '';
        $sql = 'UPDATE `' . _DB_PREFIX_ . bqSQL($table) . '` SET ';
        $counter_meta = 0;

        foreach ($data as $key => $value) {
            if ($key === 'id_lang_export') {
                $counter_meta = -1;
                $set = '`' . bqSQL($key) . '` = ' . (int) $value;
            } elseif ($key !== 'field') {
                if ($counter_meta === 0) {
                    $where .= ' WHERE `' . bqSQL($key) . '` = ' . (int) $value;
                } else {
                    $where .= ' AND `' . bqSQL($key) . '` = ' . (int) $value;
                }
            } else {
                $where .= ' AND `' . bqSQL($key) . '` = "' . pSQL($value) . '"';
            }
            $counter_meta++;
        }
        unset($key, $value, $data);
        return Db::getInstance()->execute($sql . $set . $where);
    }

    public function getToken()
    {
        $export_token = Tools::passwdGen(32);
        Configuration::updateValue('token', $export_token);
    }

    public function getExportPath()
    {
        $path = $this->export_path;
        Configuration::updateValue('export_path', $path);
    }

    public function hookDisplayBackOfficeHeader()
    {
        if (!$this->active) {
            return;
        }

        $this->context->smarty->assign(array(
            'domain' => Tools::getShopDomain(false),
        ));

        return $this->display(__FILE__, 'views/templates/hook/displayBackOfficeHeader.tpl');
    }

    public function deleteCategoryFeed($id_category)
    {
        static $validate_iso_code = array();

        (int) $id_shop = $this->context->shop->id;

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS(
            'SELECT id_object, id_country, id_lang_export as id_lang
            FROM `' . _DB_PREFIX_ . 'module_gshopping_table`
            WHERE id_shop =' . $id_shop . '
            AND id_category=' . (int) $id_category
        );

        $country = self::$countries_cache;

        foreach ($result as $value) {
            (int) $id_country = $value['id_country'];
            $country_iso = $country[$id_country]['iso_code'];

            if (isset($validate_iso_code[$country_iso])) {
                if (Validate::isLanguageIsoCode($country_iso) === false) {
                    return false;
                } else {
                    $validate_iso_code[$country_iso] = 1;
                }
            }
            unlink($this->export_path . $id_shop . '/' . $country_iso . '/' . $value['id_object'] . '.txt');
        }

        unset($value, $result, $id_shop, $id_country, $country_iso);
    }

    public function reBuildFeed($id_category)
    {
        $id_shop = $this->context->shop->id;

        $res = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
            SELECT id_object, id_country, id_lang_export as id_lang, active
            FROM `' . _DB_PREFIX_ . 'module_gshopping_table`
            WHERE id_shop = ' . (int) $id_shop . '
            AND id_category= ' . (int) $id_category, false
        );

        while ($value = Db::getInstance()->nextRow($res)) {
            if (
                (isset($value['active']) && $value['active'] != 0) &&
                (isset($value['id_country']) && $value['id_country'] != 0) &&
                (isset($value['id_lang']) && $value['id_lang'] != 0)
            ) {
                $this->buildFeed($value['id_country'], $id_category, $value['id_lang'], $value['id_object']);
            }
        }
        unset($value, $res, $id_shop);
    }

    public function buildFeed($id_country, $id_category, $id_lang, $id_object)
    {
        $feed = new GoogleFeedConverter($id_category, $id_lang);
        $country = self::$countries_cache;
        $currency = self::$currency_cache;
        $carrier = self::$carrier_cache;
        (int) $id_shop = $this->context->shop->id;
        $result = '';

        //Create directory for each country of exportation

        $country_iso = $country[$id_country]['iso_code'];
        @mkdir($this->export_path . $id_shop, 0755);
        @mkdir($this->export_path . $id_shop . '/' . $country_iso, 0755);

        while ($product = Db::getInstance()->nextRow($feed->productRes)) {
            $image_link = $feed->getImageLink($product);
            $google_category = htmlspecialchars($feed->getCategory($product['id_product']));
            $price = $feed->getGooglePrice($product['id_product'], $id_country, $country_iso, $product['price'], $currency[$id_country]);
            $gender = $feed->getGenderProduct($product['id_product'], $id_object);
            $product_link = $feed->getProductlink($product['id_product'], $id_lang, $id_shop);
            $decription = htmlspecialchars($feed->getDescription($product['description_short'], $product['description']));
            $adult_warning = $feed->getAdultWarning($google_category);
            $shipping = $feed->getShipping($product, $country[$id_country], $currency, $carrier);
            $availability = $feed->getAvailability($product['quantity']);
            $id_product_attributes = $feed->getProductAttribute($product['id_product']);
            $specificPrice = $feed->getGoogleSpecificPrice($product['id_product'], $price, $currency[$id_country]);

            if ($specificPrice == $price) {
                $specificPrice = '';
            }

            if (!empty($id_product_attributes)) {
                foreach ($id_product_attributes as $id_product_attribute => $value) {
                    $combination = $feed->getAttributeCombinationsById($value['id_product_attribute'], $id_lang, $product['id_product']);
                    foreach ($combination as $product_attribute) {
                        $id_product_attribute = $product_attribute['id_product'] . '-' . $product_attribute['id_product_attribute'];
                        if (empty($product_attribute['ean13'])) {
                            if (empty($ean13) || empty($upc)) {
                                $gtin = $feed->getGtin($product);
                            } elseif (!empty($ean13)) {
                                $gtin = $ean13;
                            } else {
                                $gtin = $upc;
                            }
                        } else {
                            if (!empty($product_attribute['ean13'])) {
                                $gtin = $product_attribute['ean13'];
                            } else {
                                $gtin = $product_attribute['upc'];
                            }
                        }

                        $imageLinkCombination = $feed->getImageLinkCombination($product_attribute['id_product_attribute'], $product);
                        $productLinkCombination = $product_link . $feed->getProductlinkCombination($id_lang, $product_attribute['id_product_attribute']);

                        if (!empty($product_attribute['quantity'])) {
                            $availability = $feed->getAvailability($product_attribute['quantity']);
                        }
                    }

                    $this->context->smarty->assign(array(
                        'id_product' => $id_product_attribute,
                        'title' => $product['title'],
                        'brand' => $product['brand'],
                        'condition' => $product['condition'],
                        'shipping_weight' => (isset($product['shipping_weight']) ? $product['shipping_weight'] : ''),
                        'product_link' => $productLinkCombination,
                        'product_type' => $product['category_name'],
                        'gtin' => $gtin,
                        'mpn' => $feed->getMpn($product_attribute['id_product'], $product_attribute['id_product_attribute']),
                        'google_category' => $google_category,
                        'description' => $decription,
                        'image_link' => $imageLinkCombination,
                        'availability' => $availability,
                        'adult_warning' => $adult_warning,
                        'price' => $price,
                        'specific_price' => $specificPrice,
                        'shipping' => $shipping,
                        'country_iso' => $country_iso,
                        'gender' => $feed->getGenderProduct($product['id_product'], $id_object, true),
                        'agegroup' => $feed->getAgeGroupProduct($product['id_product'], $id_object, true),
                        'color' => $feed->getColor($product['id_product'], $id_object, $id_lang, $product_attribute['id_product_attribute'], true),
                        'size' => $feed->getSize($product['id_product'], $id_object, $id_lang, $product_attribute['id_product_attribute'], true),
                        'material' => $feed->getMaterial($product['id_product'], $id_object, $id_lang, $product_attribute['id_product_attribute'], true),
                        'pattern' => $feed->getPattern($product['id_product'], $id_object, $id_lang, $product_attribute['id_product_attribute'], true),
                        'groupid' => $product['id_product'],
                    ));
                    $result .= $this->context->smarty->fetch(dirname(__FILE__) . '/export/tpl/export.tpl');
                }
                continue;
            }

            $this->context->smarty->assign(array(
                'id_product' => $product['id_product'],
                'title' => $product['title'],
                'brand' => $product['brand'],
                'condition' => $product['condition'],
                'shipping_weight' => $product['shipping_weight'],
                'product_link' => $product_link,
                'product_type' => $product['category_name'],
                'gtin' => $feed->getGtin($product),
                'mpn' => $feed->getMpn($product['id_product'], 0),
                'google_category' => $google_category,
                'description' => $decription,
                'image_link' => $image_link,
                'availability' => $availability,
                'adult_warning' => $adult_warning,
                'price' => $price,
                'specific_price' => $specificPrice,
                'shipping' => $shipping,
                'country_iso' => $country_iso,
                'gender' => $feed->getGenderProduct($product['id_product'], $id_object),
                'agegroup' => $feed->getAgeGroupProduct($product['id_product'], $id_object),
                'color' => $feed->getColor($product['id_product'], $id_object, $id_lang),
                'size' => $feed->getSize($product['id_product'], $id_object, $id_lang),
                'material' => $feed->getMaterial($product['id_product'], $id_object, $id_lang),
                'pattern' => $feed->getColor($product['id_product'], $id_object, $id_lang),
            ));

            $result .= $this->context->smarty->fetch(dirname(__FILE__) . '/export/tpl/export.tpl');
        }
        file_put_contents($this->export_path . (int) $id_shop . '/' . $country_iso . '/' . (int) $id_object . '.txt', $result);
    }

    /*     * ************************* */
    /*          HOOK Action          */
    /*     * ************************* */

    public function hookactionObjectCategoryAddAfter($params)
    {
        if (!$this->active) {
            return;
        }

        $this->cleanObj('category');
    }

    public function hookactionObjectCategoryUpdateAfter($params)
    {
        if (!$this->active) {
            return;
        }

        if (!isset($params['object']) || !Validate::isLoadedObject($params['object'])) {
            return;
        }

        $this->cleanObj('category');

        $obj = $params['object'];

        $this->reBuildFeed($obj->id);
    }

    public function hookactionObjectCategoryDeleteAfter($params)
    {
        if (!$this->active) {
            return;
        }

        if (!isset($params['object']) || !Validate::isLoadedObject($params['object'])) {
            return;
        }

        $obj = $params['object'];

        $this->removeCategory($obj->id);
        $this->deleteCategoryFeed($obj->id);
        $this->cleanObj('category');
    }

    public function hookactionObjectProductAddAfter($params)
    {
        if (!$this->active) {
            return;
        }

        if (!isset($params['object']) || !Validate::isLoadedObject($params['object'])) {
            return;
        }

        $obj = $params['object'];
        $id_category = $obj->getDefaultCategory();

        if (!$id_category) {
            return;
        }

        $this->reBuildFeed($id_category);
    }

    public function hookactionObjectProductUpdateAfter($params)
    {
        if (!$this->active) {
            return;
        }

        if (!isset($params['object']) || !Validate::isLoadedObject($params['object'])) {
            return;
        }

        $obj = $params['object'];
        $id_category = $obj->getDefaultCategory();

        if (!$id_category) {
            return;
        }

        $this->reBuildFeed($id_category);
    }

    public function hookactionObjectProductDeleteAfter($params)
    {
        if (!$this->active) {
            return;
        }

        if (!isset($params['object']) || !Validate::isLoadedObject($params['object'])) {
            return;
        }

        $obj = $params['object'];
        $id_category = $obj->getDefaultCategory();

        if (!$id_category) {
            return;
        }

        $this->reBuildFeed($id_category);
    }

    public function hookactionObjectCarrierAddAfter($params)
    {
        if (!$this->active) {
            return;
        }

        $this->cleanObj('carrier');
    }

    public function hookactionObjectCarrierUpdateAfter($params)
    {
        if (!$this->active) {
            return;
        }

        $this->cleanObj('carrier');
    }

    public function hookactionObjectCarrierDeleteAfter($params)
    {
        if (!$this->active) {
            return;
        }

        $this->cleanObj('carrier');
    }

    public function hookactionObjectCurrencyAddAfter($params)
    {
        if (!$this->active) {
            return;
        }

        $this->cleanObj('currency');
    }

    public function hookactionObjectCurrencyUpdateAfter($params)
    {
        if (!$this->active) {
            return;
        }

        $this->cleanObj('currency');
    }

    public function hookactionObjectCurrencyDeleteAfter($params)
    {
        if (!$this->active) {
            return;
        }

        $this->cleanObj('currency');
    }

    public function hookactionObjectCountryAddAfter($params)
    {
        if (!$this->active) {
            return;
        }

        $this->cleanObj('countries');
    }

    public function hookactionObjectCountryUpdateAfter($params)
    {
        if (!$this->active) {
            return;
        }

        $this->cleanObj('countries');
    }

    public function hookactionObjectCountryDeleteAfter($params)
    {
        if (!$this->active) {
            return;
        }

        $this->cleanObj('countries');
    }

    public function hookactionObjectLanguageAddAfter($params)
    {
        if (!$this->active) {
            return;
        }

        $this->cleanObj('language');
    }

    public function hookactionObjectLanguageUpdateAfter($params)
    {
        if (!$this->active) {
            return;
        }

        $this->cleanObj('language');
    }

    public function hookactionObjectLanguageDeleteAfter($params)
    {
        if (!$this->active) {
            return;
        }

        $this->cleanObj('language');
    }

    public function cleanerObj($obj)
    {
        GoogleTinyCache::clearAllCache(Tools::strtolower(get_class($obj)));
    }

    public function cleanObj($name)
    {
        //Clear cache
        if ($name === 'category') {
            $name = 'categories_shop' . (int) $this->context->shop->id;
        }

        GoogleTinyCache::clearCache($name);

        if ($name === 'carrier') {
            $this->getcarrierCache();
        } elseif ($name === 'currency') {
            $this->getcurrencyCache();
        } elseif ($name === 'countries') {
            $this->getCountriesCache();
            $this->cleanObj('currency');
        } else {
            $this->getCategoryListCache();
        }
    }

}
