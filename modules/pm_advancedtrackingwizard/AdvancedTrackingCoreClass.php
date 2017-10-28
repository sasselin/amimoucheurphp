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

class AdvancedTrackingCoreClass extends Module
{
    // Begin AttributesDeclaration
    protected $_html;
    protected $_html_at_end;
    protected $_base_config_url;
    protected $_default_language;
    protected $_fields_options;
    protected $_iso_lang;
    protected $_languages;
    protected $_css_files;
    protected $_js_files;
    protected $_smarty;
    protected $_cookie;
    protected $_employee;
    protected $_coreClassName;
    protected $_registerOnHooks;
    public static $_module_prefix = 'ATW';
    public static $_debug_mode = false;
    protected $_copyright_link = false;
    protected $_support_link = false;
    protected $_getting_started = false;
    protected $_initTinyMceAtEnd = false;
    protected $_initColorPickerAtEnd = false;
    protected $_initBindFillSizeAtEnd = false;
    protected static $_gradient_separator = '-';
    protected static $_border_separator = ' ';
    protected static $_shadow_separator = ' ';
    protected $_temp_upload_dir = '/uploads/temp/';
    public function __construct()
    {
        $this->_coreClassName = Tools::strtolower(get_class());
        parent::__construct();
        $this->_initClassVar();
    }
    public function install()
    {
        if (parent::install() == false or $this->_registerHooks() == false) {
            return false;
        }
        return true;
    }
    protected function _registerHooks()
    {
        if (!isset($this->_registerOnHooks) || !self::_isFilledArray($this->_registerOnHooks)) {
            return true;
        }
        foreach ($this->_registerOnHooks as $hook) {
            if (!$this->registerHook($hook)) {
                return false;
            }
        }
        return true;
    }
    protected function pmClearCache()
    {
        $this->clearCompiledTpl();
        if (Configuration::get('PS_FORCE_SMARTY_2')) {
            return $this->context->smarty->clear_cache(null, self::$_module_prefix);
        } else {
            return $this->context->smarty->clearCache(null, self::$_module_prefix);
        }
        return true;
    }
    protected static function clearCompiledTplAlternative($tplFileName, $compileDir)
    {
        $result = false;
        $compileDir = rtrim($compileDir, '/');
        $files = scandir($compileDir);
        if ($files && sizeof($files)) {
            foreach ($files as $filename) {
                if ($filename != '.' && $filename != '..' && is_dir($compileDir.'/'.$filename)) {
                    self::clearCompiledTplAlternative($tplFileName, $compileDir.'/'.$filename);
                } else {
                    $ext = self::_getFileExtension($filename);
                    if ($filename == '.' && $filename == '..' || is_dir($compileDir.'/'.$filename) || $filename == 'index.php' || $ext != 'php' || !preg_match('/file\.'.preg_quote($tplFileName).'\.php/', $filename)) {
                        continue;
                    }
                    if (Tools::file_exists_cache($compileDir.'/'.$filename) && @unlink($compileDir.'/'.$filename)) {
                        $result = true;
                    }
                }
            }
        }
        return $result;
    }
    protected function clearCompiledTpl()
    {
        $files = scandir(dirname(__FILE__));
        if ($files && sizeof($files)) {
            foreach ($files as $filename) {
                $ext = self::_getFileExtension($filename);
                if ($ext != 'tpl') {
                    continue;
                }
                if (Configuration::get('PS_FORCE_SMARTY_2')) {
                    $this->context->smarty->clear_compiled_tpl($filename);
                } else {
                    if (!$this->context->smarty->clearCompiledTemplate($filename)) {
                        self::clearCompiledTplAlternative($filename, $this->context->smarty->getCompileDir());
                    }
                }
            }
        }
    }
    protected function _checkPermissions()
    {
        $errors = array();
        if (isset($this->_file_to_check) && is_array($this->_file_to_check) && count($this->_file_to_check)) {
            foreach ($this->_file_to_check as $fileOrDir) {
                if (!is_writable(dirname(__FILE__) . '/' . $fileOrDir)) {
                    $errors[] = dirname(__FILE__) . '/' . $fileOrDir;
                }
            }
        }
        return $errors;
    }
    protected function getContent()
    {
        $return = '';
        if ($this->_require_maintenance) {
            $return .= $this->_maintenanceWarning();
            $return .= $this->_maintenanceButton();
            $return .= '<hr class="pm_hr" />';
        }
        return $return;
    }
    public static function _getFileExtension($filename)
    {
        $split = explode('.', $filename);
        $extension = end($split);
        return Tools::strtolower($extension);
    }
    public function _showWarning($text)
    {
        $vars = array(
            'text' => $text
        );
        return $this->fetchTemplate('core/warning.tpl', $vars);
    }
    protected function _showRating($show = false)
    {
        $dismiss = (int)Configuration::getGlobalValue('PM_'.self::$_module_prefix.'_DISMISS_RATING');
        if ($show && $dismiss != 1 && self::_getNbDaysModuleUsage() >= 3) {
            return $this->fetchTemplate('core/rating.tpl');
        }
        return '';
    }
    public function _showInfo($text)
    {
        $vars = array(
            'text' => $text
        );
        return $this->fetchTemplate('core/info.tpl', $vars);
    }
    public function _displayTitle($title)
    {
        $vars = array(
            'text' => $title
        );
        return $this->fetchTemplate('core/title.tpl', $vars);
    }
    public function _displaySubTitle($title)
    {
        $vars = array(
            'text' => $title
        );
        return $this->fetchTemplate('core/sub_title.tpl', $vars);
    }
    public function _displayErrorsJs($include_script_tag = false)
    {
        $vars = array(
            'include_script_tag' => $include_script_tag,
            'js_errors' => $this->errors,
        );
        $this->_html .= $this->fetchTemplate('core/js_errors.tpl', $vars);
    }
    
    private function _getPMdata()
    {
        $param = array();
        $param[] = 'ver-'._PS_VERSION_;
        $param[] = 'current-'.$this->name;
        
        $result = $this->getPMAddons();
        if ($result && self::_isFilledArray($result)) {
            foreach ($result as $moduleName => $moduleVersion) {
                $param[] = $moduleName . '-' . $moduleVersion;
            }
        }
        return self::getDataSerialized(implode('|', $param));
    }
    private function getPMAddons()
    {
        $pmAddons = array();
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('SELECT DISTINCT name FROM '._DB_PREFIX_.'module WHERE name LIKE "pm_%"');
        if ($result && self::_isFilledArray($result)) {
            foreach ($result as $module) {
                $instance = Module::getInstanceByName($module['name']);
                if ($instance && isset($instance->version)) {
                    $pmAddons[$module['name']] = $instance->version;
                }
            }
        }
        return $pmAddons;
    }
    private function doHttpRequest($data = array(), $c = 'prestashop', $s = 'api.addons')
    {
        $data = array_merge(array(
            'version' => _PS_VERSION_,
            'iso_lang' => Tools::strtolower($this->_iso_lang),
            'iso_code' => Tools::strtolower(Country::getIsoById(Configuration::get('PS_COUNTRY_DEFAULT'))),
            'module_key' => $this->module_key,
            'method' => 'contributor',
            'action' => 'all_products',
        ), $data);
        $postData = http_build_query($data);
        $context = stream_context_create(array(
            'http' => array(
                'method' => 'POST',
                'content' => $postData,
                'header' => 'Content-type: application/x-www-form-urlencoded',
                'timeout' => 15,
            )
        ));
        $response = Tools::file_get_contents('https://' . $s . '.' . $c . '.com', false, $context);
        if (empty($response)) {
            return false;
        }
        $responseToJson = Tools::jsonDecode($response);
        if (empty($responseToJson)) {
            return false;
        }
        return $responseToJson;
    }
    private function getAddonsModulesFromApi()
    {
        $modules = Configuration::get('PM_' . self::$_module_prefix . '_AM');
        $modules_date = Configuration::get('PM_' . self::$_module_prefix . '_AMD');
        if ($modules && strtotime('+2 day', $modules_date) > time()) {
            return Tools::jsonDecode($modules, true);
        }
        $jsonResponse = $this->doHttpRequest();
        if (empty($jsonResponse->products)) {
            return array();
        }
        $dataToStore = array();
        foreach ($jsonResponse->products as $addonsEntry) {
            $dataToStore[(int)$addonsEntry->id] = array(
                'name' => $addonsEntry->name,
                'displayName' => $addonsEntry->displayName,
                'url' => $addonsEntry->url,
                'compatibility' => $addonsEntry->compatibility,
                'version' => $addonsEntry->version,
                'description' => $addonsEntry->description,
            );
        }
        Configuration::updateValue('PM_' . self::$_module_prefix . '_AM', Tools::jsonEncode($dataToStore));
        Configuration::updateValue('PM_' . self::$_module_prefix . '_AMD', time());
        return Tools::jsonDecode(Configuration::get('PM_' . self::$_module_prefix . '_AM'), true);
    }
    private function getPMModulesFromApi()
    {
        $modules = Configuration::get('PM_' . self::$_module_prefix . '_PMM');
        $modules_date = Configuration::get('PM_' . self::$_module_prefix . '_PMMD');
        if ($modules && strtotime('+2 day', $modules_date) > time()) {
            return Tools::jsonDecode($modules, true);
        }
        $jsonResponse = $this->doHttpRequest(array('list' => $this->getPMAddons()), 'presta-module', 'api-addons');
        if (empty($jsonResponse)) {
            return array();
        }
        Configuration::updateValue('PM_' . self::$_module_prefix . '_PMM', Tools::jsonEncode($jsonResponse));
        Configuration::updateValue('PM_' . self::$_module_prefix . '_PMMD', time());
        return Tools::jsonDecode(Configuration::get('PM_' . self::$_module_prefix . '_PMM'), true);
    }
    public function _displaySupport()
    {
        $get_started_image_list = array();
        if (isset($this->_getting_started) && self::_isFilledArray($this->_getting_started)) {
            foreach ($this->_getting_started as $get_started_image) {
                $get_started_image_list[] = "{ 'href': '".$get_started_image['href']."', 'title': '".htmlentities($get_started_image['title'], ENT_QUOTES, 'UTF-8')."' }";
            }
        }
        $pm_addons_products = $this->getAddonsModulesFromApi();
        $pm_products = $this->getPMModulesFromApi();
        if (!is_array($pm_addons_products)) {
            $pm_addons_products = array();
        }
        if (!is_array($pm_products)) {
            $pm_products = array();
        }
        self::shuffleArray($pm_addons_products);
        if (self::_isFilledArray($pm_addons_products)) {
            if (!empty($pm_products['ignoreList']) && self::_isFilledArray($pm_products['ignoreList'])) {
                foreach ($pm_products['ignoreList'] as $ignoreId) {
                    if (isset($pm_addons_products[$ignoreId])) {
                        unset($pm_addons_products[$ignoreId]);
                    }
                }
            }
            $addonsList = $this->getPMAddons();
            if ($addonsList && self::_isFilledArray($addonsList)) {
                foreach (array_keys($addonsList) as $moduleName) {
                    foreach ($pm_addons_products as $k => $pm_addons_product) {
                        if ($pm_addons_product['name'] == $moduleName) {
                            unset($pm_addons_products[$k]);
                            break;
                        }
                    }
                }
            }
        }
        $vars = array(
            'support_links' => (self::_isFilledArray($this->_support_link) ? $this->_support_link : array()),
            'copyright_link' => (self::_isFilledArray($this->_copyright_link) ? $this->_copyright_link : false),
            'get_started_image_list' => (isset($this->_getting_started) && self::_isFilledArray($this->_getting_started) ? $this->_getting_started : array()),
            'pm_module_version' => $this->version,
            'pm_data' => $this->_getPMdata(),
            'pm_products' => $pm_products,
            'pm_addons_products' => $pm_addons_products,
            'html_at_end' =>  $this->_includeHTMLAtEnd(),
        );
        return $this->fetchTemplate('core/support.tpl', $vars);
    }
    protected function _preProcess()
    {
        if (Tools::getIsset('dismissRating')) {
            $this->_cleanOutput();
            Configuration::updateGlobalValue('PM_'.self::$_module_prefix.'_DISMISS_RATING', 1);
            die;
        } elseif (Tools::getIsset('pm_load_function')) {
            if (method_exists($this, Tools::getValue('pm_load_function'))) {
                $this->_cleanOutput();
                if (Tools::getValue('class')) {
                    if (class_exists(Tools::getValue('class'))) {
                        $class = Tools::getValue('class');
                        $obj = new $class();
                        if (Tools::getValue($obj->identifier)) {
                            $obj = new $class(Tools::getValue($obj->identifier));
                        }
                        $pmLoadFunction = Tools::getValue('pm_load_function');
                        $params = array('obj'=>$obj,'class'=>$class, 'method'=> $pmLoadFunction,'reload_after'=>Tools::getValue('pm_reload_after'),'js_callback'=>Tools::getValue('pm_js_callback'));
                        $this->_preLoadFunctionProcess($params);
                        $this->_html .= $this->$pmLoadFunction($params);
                    } else {
                        $this->_cleanOutput();
                        $this->_html .= $this->_showWarning($this->l('Class', $this->_coreClassName).' '.Tools::getValue('class').' '.$this->l('does not exists', $this->_coreClassName));
                        $this->_echoOutput(true);
                    }
                } else {
                    $pmLoadFunction = Tools::getValue('pm_load_function');
                    $params = array('method' => $pmLoadFunction,'reload_after'=>Tools::getValue('pm_reload_after'),'js_callback'=>Tools::getValue('pm_js_callback'));
                    $this->_preLoadFunctionProcess($params);
                    $this->_html .= $this->$pmLoadFunction($params);
                }
                $this->_echoOutput(true);
            } else {
                $this->_cleanOutput();
                $this->_html .= $this->_showWarning($this->l('Method unvailable', $this->_coreClassName));
                $this->_echoOutput(true);
            }
        }
    }
    protected function _maintenanceButton()
    {
        $vars = array(
            'maintenance_enabled' => Configuration::get('PM_' . self::$_module_prefix . '_MAINTENANCE'),
        );
        return $this->fetchTemplate('core/components/maintenance_button.tpl', $vars);
    }
    protected function _maintenanceWarning()
    {
        $ip_maintenance = Configuration::get('PS_MAINTENANCE_IP');
        $addIpWarning = '';
        $return = '<div id="maintenanceWarning" ' . ((Configuration::get('PM_' . self::$_module_prefix . '_MAINTENANCE')) ? '' : 'style="display:none"') . '">';
        if (!$ip_maintenance || empty($ip_maintenance)) {
            $tab_http_key = 'controller';
            $tab_http_value = 'AdminMaintenance';
            $addIpWarning = '<br /><br />' . $this->l('You must define a maintenance IP in your', $this->_coreClassName) . '
                <a href="index.php?'.$tab_http_key.'='.$tab_http_value.'&token=' . Tools::getAdminToken($tab_http_value . (int)Tab::getIdFromClassName($tab_http_value) . (int)$this->context->employee->id) . '" style="text-decoration:underline;">
                ' . $this->l('Preferences Panel.', $this->_coreClassName) . '
                </a>';
        }
        $return .= $this->_showWarning(
            sprintf($this->l('%s is currently running in maintenance mode.', $this->_coreClassName), $this->displayName) .
            $addIpWarning
        );
        $return .= '</div>';
        return $return;
    }
    protected function _postProcessMaintenance()
    {
        $return = '';
        $maintenance = Configuration::get('PM_' . self::$_module_prefix . '_MAINTENANCE');
        $maintenance = ($maintenance ? 0 : 1);
        Configuration::updateValue('PM_' . self::$_module_prefix . '_MAINTENANCE', (int)$maintenance);
        $return .= '$("a#buttonMaintenance").after("'.addcslashes($this->_maintenanceButton(), '"').'").remove();';
        if ($maintenance) {
            $return .= '$("#pmImgMaintenance").attr("class", "ui-icon ui-icon-locked");';
            $return .= '$("#maintenanceWarning").slideDown();';
            $return .= 'show_info("' . sprintf($this->l('%s is now in maintenance mode.', $this->_coreClassName), $this->displayName) . '");';
        } else {
            $return .= '$("#pmImgMaintenance").attr("class", "ui-icon ui-icon-unlocked");';
            $return .= '$("#maintenanceWarning").slideUp();';
            $return .= 'show_info("' . sprintf($this->l('%s is now running in normal mode.', $this->_coreClassName), $this->displayName) . '");';
        }
        $this->pmClearCache();
        self::_cleanBuffer();
        return $return;
    }
    protected function _isInMaintenance()
    {
        if (isset($this->_cacheIsInMaintenance)) {
            return $this->_cacheIsInMaintenance;
        }
        if (Configuration::get('PM_'.self::$_module_prefix.'_MAINTENANCE')) {
            $ips = explode(',', Configuration::get('PS_MAINTENANCE_IP'));
            if (in_array($_SERVER['REMOTE_ADDR'], $ips)) {
                $this->_cacheIsInMaintenance = false;
                return false;
            }
            $this->_cacheIsInMaintenance = true;
            return true;
        }
        $this->_cacheIsInMaintenance = false;
        return false;
    }
    protected function _preCopyFromPost()
    {
    }
    protected function _postCopyFromPost($params)
    {
    }
    protected function _preDuplicateProcess($params)
    {
    }
    protected function _preDeleteProcess($params)
    {
    }
    protected function _preLoadFunctionProcess(&$params)
    {
    }
    protected function _postDuplicateProcess($params)
    {
        if (isset($params['include_script_tag']) && $params['include_script_tag']) {
            $this->_html .= '<script type="text/javascript">';
        }
        if (Tools::getIsset('pm_reload_after') && Tools::getValue('pm_reload_after')) {
            $this->_reloadPanels(Tools::getValue('pm_reload_after'));
        }
        if (Tools::getIsset('pm_js_callback') && Tools::getValue('pm_js_callback')) {
            $this->_getJsCallback(Tools::getValue('pm_js_callback'));
        }
        $this->_html .= 'parent.parent.show_info("'.$this->l('Successfully duplicated', $this->_coreClassName).'");';
        if (isset($params['include_script_tag']) && $params['include_script_tag']) {
            $this->_html .= '</script>';
        }
    }
    protected function _postDeleteProcess($params)
    {
        if (isset($params['include_script_tag']) && $params['include_script_tag']) {
            $this->_html .= '<script type="text/javascript">';
        }
        if (Tools::getIsset('pm_reload_after') && Tools::getValue('pm_reload_after')) {
            $this->_reloadPanels(Tools::getValue('pm_reload_after'));
        }
        if (Tools::getIsset('pm_js_callback') && Tools::getValue('pm_js_callback')) {
            $this->_getJsCallback(Tools::getValue('pm_js_callback'));
        }
        $this->_html .= 'parent.parent.show_info("'.$this->l('Successfully deleted', $this->_coreClassName).'");';
        if (isset($params['include_script_tag']) && $params['include_script_tag']) {
            $this->_html .= '</script>';
        }
    }
    protected function _getJsCallback($js_callback)
    {
        $js_callbacks = explode('|', $js_callback);
        foreach ($js_callbacks as $js_callback) {
            $this->_html .= 'parent.parent.'.$js_callback.'();';
        }
    }
    protected function _reloadPanels($reload_after)
    {
        $reload_after = explode('|', $reload_after);
        foreach ($reload_after as $panel) {
            $this->_html .= 'parent.parent.reloadPanel("'.$panel.'");';
        }
    }
    protected function _postSaveProcess($params)
    {
        if (isset($params['include_script_tag']) && $params['include_script_tag']) {
            $this->_html .= '<script type="text/javascript">';
        }
        if (isset($params['reload_after']) && $params['reload_after']) {
            $this->_reloadPanels($params['reload_after']);
        }
        if (isset($params['js_callback']) && $params['js_callback']) {
            $this->_getJsCallback($params['js_callback']);
        }
        $this->_html .= 'parent.parent.show_info("'.$this->l('Successfully saved', $this->_coreClassName).'");';
        if (isset($params['include_script_tag']) && $params['include_script_tag']) {
            $this->_html .= '</script>';
        }
    }
    protected function _postProcess()
    {
        if (Tools::getValue('pm_save_obj')) {
            if (class_exists(Tools::getValue('pm_save_obj'))) {
                $class = Tools::getValue('pm_save_obj');
                $obj = new $class();
                if (Tools::getValue($obj->identifier)) {
                    $obj = new $class(Tools::getValue($obj->identifier));
                }
                $this->errors = $obj->validateController();
                if (!self::_isFilledArray($this->errors)) {
                    $this->copyFromPost($obj);
                    if ($obj->save()) {
                        $this->_cleanOutput();
                        $this->_postSaveProcess(array('class'=>$class, 'obj'=>$obj, 'include_script_tag'=>true, 'reload_after'=>Tools::getValue('pm_reload_after'), 'js_callback'=>Tools::getValue('pm_js_callback')));
                        $this->_echoOutput(true);
                    } else {
                        $this->_cleanOutput();
                        $this->_html .= $this->_showWarning($this->l('Error while saving object', $this->_coreClassName));
                        $this->_echoOutput(true);
                    }
                } else {
                    $this->_cleanOutput();
                    $this->_displayErrorsJs(true);
                    $this->_echoOutput(true);
                }
            } else {
                $this->_cleanOutput();
                $this->_html .= $this->_showWarning($this->l('Class', $this->_coreClassName).' '.Tools::getValue('class').' '.$this->l('does not exists', $this->_coreClassName));
                $this->_echoOutput(true);
            }
        } elseif (Tools::getValue('activeMaintenance')) {
            echo $this->_postProcessMaintenance(self::$_module_prefix);
            die();
        } elseif (Tools::getValue('uploadTempFile')) {
            $this->_postProcessUploadTempFile();
        }
    }
    protected function _postProcessUploadTempFile()
    {
        $this->_cleanOutput();
        if (isset($_REQUEST["name"])) {
            $fileName = $_REQUEST["name"];
        } elseif (!empty($_FILES)) {
            $fileName = $_FILES["fileUpload"]["name"];
        } else {
            $fileName = uniqid("file_" . self::$_module_prefix . mt_rand());
        }
        $extension = self::_getFileExtension($fileName);
        $filePath = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR . uniqid(self::$_module_prefix . mt_rand()) . '.' . $extension;
        if (!$out = @fopen("{$filePath}.part", "wb")) {
            die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
        }
        if (!empty($_FILES)) {
            if ($_FILES["fileUpload"]["error"] || !is_uploaded_file($_FILES["fileUpload"]["tmp_name"])) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
            }
            if (!$in = @fopen($_FILES["fileUpload"]["tmp_name"], "rb")) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
            }
        } else {
            if (!$in = @fopen("php://input", "rb")) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
            }
        }
        while ($buff = fread($in, 4096)) {
            fwrite($out, $buff);
        }
        @fclose($out);
        @fclose($in);
        rename("{$filePath}.part", $filePath);
        die('{"jsonrpc" : "2.0", "filename" : "'. basename($filePath) .'"}');
    }
    protected function _initClassVar()
    {
        $this->_base_config_url = $_SERVER['SCRIPT_NAME'].(($controller = Tools::getValue('controller')) ? '?controller='.$controller: '') . '&configure=' . $this->name . '&token=' . Tools::getValue('token');
        $this->_default_language = (int) Configuration::get('PS_LANG_DEFAULT');
        $this->_iso_lang = Language::getIsoById((int)$this->context->language->id);
        $this->_languages = Language::getLanguages(false);
    }
    public function _startForm($configOptions)
    {
        $defaultOptions = array(
            'action' => false,
            'target' => 'dialogIframePostForm',
            'iframetarget' => true
        );
        $configOptions = $this->_parseOptions($defaultOptions, $configOptions);
        $return = '';
        if ($configOptions['iframetarget']) {
            $return .= $this->_headerIframe();
        }
        $vars = array(
            'form_action' => ($configOptions['action'] ? $configOptions['action'] : $this->_base_config_url),
            'form_id' => $configOptions['id'],
            'form_target' => $configOptions['target'],
            'obj_id' => (isset($configOptions['obj']) && is_object($configOptions['obj']) && !empty($configOptions['obj']->id) ? $configOptions['obj']->id : false),
            'obj_identifier' => (isset($configOptions['obj']) && is_object($configOptions['obj']) && !empty($configOptions['obj']->id) ? $configOptions['obj']->identifier : false),
            'obj_class' => (isset($configOptions['obj']) && is_object($configOptions['obj']) ? get_class($configOptions['obj']) : false),
            'pm_reload_after' => (!empty($configOptions['params']['reload_after']) ? $configOptions['params']['reload_after'] : false),
            'pm_js_callback' => (!empty($configOptions['params']['js_callback']) ? $configOptions['params']['js_callback'] : false),
        );
        $return .= $this->fetchTemplate('core/components/form/start_form.tpl', $vars, $configOptions);
        return $return;
    }
    public function _endForm($configOptions)
    {
        $defaultOptions = array(
            'id' => null,
            'iframetarget' => true,
            'jquerytoolsvalidatorfunction' => false
        );
        $configOptions = $this->_parseOptions($defaultOptions, $configOptions);
        $vars = array(
            'form_id' => $configOptions['id'],
            'has_jquerytools' => ($configOptions['id'] != null && in_array('jquerytools', $this->_css_js_to_load)),
            'jquerytools_validator_function' => $configOptions['jquerytoolsvalidatorfunction'],
        );
        $return = $this->fetchTemplate('core/components/form/end_form.tpl', $vars, $configOptions);
        if ($configOptions['iframetarget']) {
            $return .= $this->_footerIframe();
        }
        return $return;
    }
    public function _retrieveFormValue($type, $fieldName, $fieldDbName = false, $obj, $defaultValue = '', $compareValue = false, $key = false)
    {
        if (!$fieldDbName) {
            $fieldDbName = $fieldName;
        }
        switch ($type) {
            case 'text':
                if (is_array($obj)) {
                    if ($key) {
                        return htmlentities(Tools::stripslashes(Tools::getValue($fieldName, (self::_isFilledArray($obj) && isset($obj[$fieldDbName] [$key]) ? $obj[$fieldDbName] [$key] : $defaultValue))), ENT_COMPAT, 'UTF-8');
                    } else {
                        return htmlentities(Tools::stripslashes(Tools::getValue($fieldName, (self::_isFilledArray($obj) && isset($obj[$fieldDbName]) ? $obj[$fieldDbName] : $defaultValue))), ENT_COMPAT, 'UTF-8');
                    }
                } else {
                    if ($key) {
                        return htmlentities(Tools::stripslashes(Tools::getValue($fieldName, ($obj && isset($obj->{$fieldDbName}[$key]) ? $obj->{$fieldDbName}[$key] : $defaultValue))), ENT_COMPAT, 'UTF-8');
                    } else {
                        return htmlentities(Tools::stripslashes(Tools::getValue($fieldName, ($obj && isset($obj->{$fieldDbName}) ? $obj->{$fieldDbName} : $defaultValue))), ENT_COMPAT, 'UTF-8');
                    }
                }
                break;
            case 'textpx':
                if (is_array($obj)) {
                    if ($key) {
                        return (int)preg_replace('#px#', '', Tools::getValue($fieldName, (self::_isFilledArray($obj) && isset($obj[$fieldDbName]) ? $obj[$fieldDbName] [$key] : $defaultValue)));
                    } else {
                        return (int)preg_replace('#px#', '', Tools::getValue($fieldName, (self::_isFilledArray($obj) && isset($obj[$fieldDbName]) ? $obj[$fieldDbName] : $defaultValue)));
                    }
                } else {
                    if ($key) {
                        return (int)preg_replace('#px#', '', Tools::getValue($fieldName, ($obj && isset($obj->{$fieldDbName}) ? $obj->{$fieldDbName}[$key] : $defaultValue)));
                    } else {
                        return (int)preg_replace('#px#', '', Tools::getValue($fieldName, ($obj && isset($obj->{$fieldDbName}) ? $obj->{$fieldDbName} : $defaultValue)));
                    }
                }
                break;
            case 'select':
                if (is_array($obj)) {
                    return ((Tools::getValue($fieldName, (self::_isFilledArray($obj) && isset($obj[$fieldDbName]) ? $obj[$fieldDbName] : $defaultValue)) == $compareValue) ? ' selected="selected"' : '');
                } else {
                    return ((Tools::getValue($fieldName, ($obj && isset($obj->{$fieldDbName}) ? $obj->{$fieldDbName} : $defaultValue)) == $compareValue) ? ' selected="selected"' : '');
                }
                break;
            case 'radio':
            case 'checkbox':
                if (is_array($obj)) {
                    if (isset($obj[$fieldName]) && is_array($obj[$fieldName]) && sizeof($obj[$fieldName]) && isset($obj[$fieldDbName])) {
                        return ((in_array($compareValue, $obj[$fieldName])) ? ' checked="checked"' : '');
                    }
                    return ((Tools::getValue($fieldName, (self::_isFilledArray($obj) && isset($obj[$fieldDbName]) ? $obj[$fieldDbName] : $defaultValue)) == $compareValue) ? ' checked="checked"' : '');
                } else {
                    if (isset($obj->$fieldName) && is_array($obj->$fieldName) && sizeof($obj->$fieldName) && isset($obj->{$fieldDbName})) {
                        return ((in_array($compareValue, $obj->$fieldName)) ? ' checked="checked"' : '');
                    }
                    return ((Tools::getValue($fieldName, ($obj && isset($obj->{$fieldDbName}) ? $obj->{$fieldDbName} : $defaultValue)) == $compareValue) ? ' checked="checked"' : '');
                }
                break;
        }
    }
    public function _displayAjaxSelectMultiple($configOptions)
    {
        $defaultOptions = array(
            'remoteurl' => false,
            'limit' => 50,
            'limitincrement' => 20,
            'remoteparams' => false,
            'tips' => false,
            'triggeronliclick' => true,
            'displaymore' => true,
            'idcolumn' => '',
            'namecolumn' => ''
        );
        $configOptions = $this->_parseOptions($defaultOptions, $configOptions);
        $vars = array(
            'index_column' => (isset($configOptions['namecolumn']) && isset($configOptions['idcolumn']) && !empty($configOptions['namecolumn']) && !empty($configOptions['idcolumn'])),
        );
        return $this->fetchTemplate('core/components/ajax_select_multiple.tpl', $vars, $configOptions);
    }
    public function _displayInputActive($configOptions)
    {
        $defaultOptions = array(
            'defaultvalue' => false,
            'tips' => false,
            'onclick' => false,
            'on_label' => false,
            'off_label' => false,
        );
        $configOptions = $this->_parseOptions($defaultOptions, $configOptions);
        $vars = array(
            'selected_on' => $this->_retrieveFormValue('radio', $configOptions['key_active'], $configOptions['key_db'], $configOptions['obj'], $configOptions['defaultvalue'], 1),
            'selected_off' => $this->_retrieveFormValue('radio', $configOptions['key_active'], $configOptions['key_db'], $configOptions['obj'], $configOptions['defaultvalue'], 0),
        );
        return $this->fetchTemplate('core/components/input_active.tpl', $vars, $configOptions);
    }
    private function _parseOptions($defaultOptions = array(), $options = array())
    {
        if (self::_isFilledArray($options)) {
            $options = array_change_key_case($options, CASE_LOWER);
        }
        if (isset($options['tips']) && !empty($options['tips'])) {
            $options['tips'] = htmlentities($options['tips'], ENT_QUOTES, 'UTF-8');
        }
        if (self::_isFilledArray($defaultOptions)) {
            $defaultOptions = array_change_key_case($defaultOptions, CASE_LOWER);
            foreach (array_keys($defaultOptions) as $option_name) {
                if (!isset($options[$option_name])) {
                    $options[$option_name] = $defaultOptions[$option_name];
                }
            }
        }
        return $options;
    }
    public function _displayInputText($configOptions)
    {
        $defaultOptions = array(
            'type' => 'text',
            'size' => '150px',
            'defaultvalue' => false,
            'min' => false,
            'max' => false,
            'maxlength' => false,
            'onkeyup' => false,
            'onchange' => false,
            'required' => false,
            'tips' => false,
            'placeholder' => false,
        );
        $configOptions = $this->_parseOptions($defaultOptions, $configOptions);
        $vars = array(
            'current_value' => $this->_retrieveFormValue('text', $configOptions['key'], false, $configOptions['obj'], $configOptions['defaultvalue']),
        );
        return $this->fetchTemplate('core/components/input_text.tpl', $vars, $configOptions);
    }
    public function _displaySelect($configOptions)
    {
        $defaultOptions = array(
            'size' => '200px',
            'defaultvalue' => false,
            'options' => array(),
            'onchange' => false,
            'tips' => false
        );
        $configOptions = $this->_parseOptions($defaultOptions, $configOptions);
        $selected_attr = array();
        foreach (array_keys($configOptions['options']) as $value) {
            $selected_attr[$value] = $this->_retrieveFormValue('select', $configOptions['key'], false, $configOptions['obj'], $configOptions['defaultvalue'], $value);
        }
        $vars = array(
            'selected_attr' => $selected_attr,
        );
        return $this->fetchTemplate('core/components/select.tpl', $vars, $configOptions);
    }
    protected function _loadCssJsLibrary($library)
    {
        $return = '';
        switch ($library) {
            case 'core':
                $return .= '<script type="text/javascript">
                    var _base_config_url = "' . $this->_base_config_url . '";
                    var PS_ALLOW_ACCENTED_CHARS_URL = '.(int)Configuration::get('PS_ALLOW_ACCENTED_CHARS_URL').';
                    var _modulePath = "' . $this->_path . '";
                    var _id_employee = ' . ( int ) $this->context->cookie->id_employee . ';
                    var id_language = Number(' . $this->_default_language . ');
                    var baseAdminDir = "'. __PS_BASE_URI__.basename(_PS_ADMIN_DIR_).'/";
                    var iso_user = "'.$this->context->language->iso_code.'";
                </script>';
                $this->context->controller->addJqueryUI(array('ui.draggable', 'ui.droppable', 'ui.sortable', 'ui.widget', 'ui.dialog', 'ui.tabs', 'ui.progressbar'), '../../../../modules/'.$this->name.'/views/css/jquery-ui-theme');
                $this->context->controller->addJS($this->_path . 'views/js/adminCore.js');
                $this->context->controller->addJS($this->_path . 'views/js/admin.js');
                $this->context->controller->addCSS($this->_path . 'views/css/adminCore.css');
                $this->context->controller->addCSS($this->_path . 'views/css/admin.css');
                break;
            case 'jquerytiptip':
                $this->context->controller->addJS($this->_path . 'views/js/jquery.tipTip.js');
                break;
            case 'jgrowl':
                $this->context->controller->addJS($this->_path . 'views/js/jGrowl/jquery.jgrowl_minimized.js');
                $this->context->controller->addCSS($this->_path . 'views/css/jGrowl/jquery.jgrowl.css');
                break;
            case 'datatables':
                $this->context->controller->addJS($this->_path . 'views/js/datatables/jquery.dataTables.min.js');
                $this->context->controller->addCSS($this->_path . 'views/css/datatables/demo_table_jui.css');
                break;
            case 'codemirrorcore':
                $this->context->controller->addJS($this->_path . 'views/js/codemirror/codemirror.js');
                $this->context->controller->addCSS($this->_path . 'views/css/codemirror/codemirror.css');
                $this->context->controller->addCSS($this->_path . 'views/css/codemirror/default.css');
                break;
            case 'codemirrorcss':
                $this->context->controller->addJS($this->_path . 'views/js/codemirror/css.js');
                break;
            case 'codemirrormixed':
                $this->context->controller->addJS($this->_path . 'views/js/codemirror/xml.js');
                $this->context->controller->addJS($this->_path . 'views/js/codemirror/css.js');
                $this->context->controller->addJS($this->_path . 'views/js/codemirror/javascript.js');
                $this->context->controller->addJS($this->_path . 'views/js/codemirror/htmlmixed.js');
                break;
            case 'collision':
                $this->context->controller->addJS($this->_path . 'views/js/jquerycollision/jquery.collision.js');
                break;
            case 'multiselect':
                $this->context->controller->addJS($this->_path . 'views/js/multiselect/jquery.tmpl.1.1.1.js');
                $this->context->controller->addJS($this->_path . 'views/js/multiselect/jquery.blockUI.js');
                $this->context->controller->addJS($this->_path . 'views/js/multiselect/ui.multiselect.js');
                $this->context->controller->addCSS($this->_path . 'views/css/multiselect/ui.multiselect.css');
                break;
            case 'chosen':
                $this->context->controller->addJqueryPlugin('chosen');
                break;
        }
        return $return;
    }
    protected function _loadCssJsLibraries()
    {
        $return = '';
        if (self::_isFilledArray($this->_css_js_to_load)) {
            foreach ($this->_css_js_to_load as $library) {
                $return .= $this->_loadCssJsLibrary($library);
            }
        }
        return $return;
    }
    private function _includeHTMLAtEnd()
    {
        $return = '';
        if ($this->_initTinyMceAtEnd) {
            $return .= $this->_initTinyMce();
        }
        if ($this->_initColorPickerAtEnd) {
            $return .= $this->_initColorPicker();
        }
        if ($this->_initBindFillSizeAtEnd) {
            $return .= $this->_initBindFillSize();
        }
        $return .= '<script type="text/javascript">$(\'.hideAfterLoad\').hide();</script>';
        $return .= $this->_html_at_end;
        return $return;
    }
    public function _addButton($configOptions)
    {
        $defaultOptions = array(
            'text' => '',
            'href' => '',
            'title' => '',
            'onclick' => false,
            'icon_class' => false,
            'class' => false,
            'rel' => false,
            'target' => false,
            'id' => false
        );
        $configOptions = $this->_parseOptions($defaultOptions, $configOptions);
        if (!$configOptions['id']) {
            $curId = 'button_' . uniqid(self::$_module_prefix . mt_rand());
        } else {
            $curId = $configOptions['id'];
        }
        $vars = array(
            'currentId' => $curId,
        );
        return $this->fetchTemplate('core/components/button.tpl', $vars, $configOptions);
    }
    public function _displaySubmit($value, $name)
    {
        $vars = array(
            'value' => $value,
            'name' => $name,
        );
        return $this->fetchTemplate('core/components/submit_button.tpl', $vars);
    }
    protected function _headerIframe()
    {
        $return = '';
        $assets = array();
        $backupHtml = $this->_html;
        $inline = $this->_loadCssJsLibraries();
        foreach ($this->context->controller->css_files as $cssUri => $media) {
            if (!preg_match('/gamification/i', $cssUri)) {
                $assets[] = '<link href="'.$cssUri.'" rel="stylesheet" type="text/css" media="'.$media.'" />';
            }
        }
        foreach ($this->context->controller->js_files as $jsUri) {
            if (!preg_match('#gamification|notifications\.js|help\.js|helpAccess\.js#i', $jsUri)) {
                $assets[] = '<script type="text/javascript" src="'.$jsUri.'"></script>';
            }
        }
        $return = $backupHtml;
        $vars = array(
            'ps_version' => Tools::substr(str_replace('.', '', _PS_VERSION_), 0, 2),
            'assets' => $assets,
            'inline' => $inline,
        );
        $return .= $this->fetchTemplate('core/iframe/header.tpl', $vars);
        $return .= $inline;
        return $return;
    }
    protected function _footerIframe()
    {
        $vars = array(
            'debug_mode' => self::$_debug_mode,
            'html_at_end' => $this->_includeHTMLAtEnd(),
        );
        return $this->fetchTemplate('core/iframe/footer.tpl', $vars);
    }
    public function _initDataTable($configOptions)
    {
        $defaultOptions = array(
            'returnAsScript' => false,
        );
        $configOptions = $this->_parseOptions($defaultOptions, $configOptions);
        $vars = array(
            'idDataTable' => $configOptions['id_table'],
            'returnAsScript' => $configOptions['returnasscript'],
        );
        return $this->fetchTemplate('core/components/datatable.tpl', $vars);
    }
    protected function _initTinyMce()
    {
        $vars = array(
            'init_tinymce_iso' => (Tools::file_exists_cache(_PS_ROOT_DIR_ . '/js/tiny_mce/langs/' . $this->_iso_lang . '.js') ? $this->_iso_lang : 'en'),
            'init_tinymce_ad' => dirname($_SERVER["PHP_SELF"]),
            'init_tinymce_css_dir' => _THEME_CSS_DIR_,
        );
        return $this->fetchTemplate('core/init_tinymce.tpl', $vars);
    }
    protected function _initBindFillSize()
    {
        return $this->fetchTemplate('core/init_bind_fill_size.tpl');
    }
    protected function _initColorPicker()
    {
        return $this->fetchTemplate('core/init_color_picker.tpl');
    }
    protected function _getBgPositionFromArray($type, $key)
    {
        $type = (int) $type;
        if ($type==1) {
            $array = Tools::getValue($key.'_position');
            $vertical_position = array('top','center' ,'bottom' );
            $horizontal_position = array('right','center' ,'left' );
            if (in_array($array[0], $vertical_position) && in_array($array[1], $horizontal_position)) {
                return implode(' ', array_reverse($array));
            }
        } elseif ($type==2) {
            $array = Tools::getValue($key.'_percent');
            foreach ($array as &$value) {
                if (!isset($value) || trim($value) == '') {
                    $value = 0;
                }
            }
            if (Validate::isInt($array[0]) && Validate::isInt($array[1])) {
                return implode('% ', array_reverse($array)).'%';
            }
        } elseif ($type==3) {
            $array = Tools::getValue($key.'_px');
            foreach ($array as $value) {
                if (!isset($value) || trim($value) == '') {
                    $value = 0;
                }
            }
            if (Validate::isInt($array[0]) && Validate::isInt($array[1])) {
                return implode('px ', array_reverse($array)).'px';
            }
        } elseif ($type==4) {
            $val = Tools::getValue($key.'_free');
            return psql($val);
        }
        return false;
    }
    protected function copyFromPost(&$destination, $destination_type = 'object', $data = false)
    {
        $this->_preCopyFromPost();
        $clearTempDirectory = false;
        if (!$data) {
            if (method_exists('Tools', 'getAllValues')) {
                $data = Tools::getAllValues();
            } else {
                $data = $_POST;
            }
        }
        foreach ($data as $key => $value) {
            if (preg_match('/_temp_file$/', $key) && $value) {
                $final_destination = dirname(__FILE__) . Tools::getValue($key . '_destination');
                $final_file = $final_destination . $value;
                $temp_file = dirname(__FILE__) . $this->_temp_upload_dir . $value;
                if (self::_isRealFile($temp_file)) {
                    rename($temp_file, $final_file);
                }
                $key = preg_replace('/_temp_file$/', '', $key);
                if ($old_file = Tools::getValue($key . '_old_file')) {
                    if (self::_isRealFile($final_destination . Tools::getValue($key . '_old_file'))) {
                        @unlink($final_destination . Tools::getValue($key . '_old_file'));
                    }
                }
                $clearTempDirectory = true;
            } elseif (preg_match('/_unlink$/', $key)) {
                $key = preg_replace('/_unlink$/', '', $key);
                $final_file = dirname(__FILE__) . Tools::getValue($key . '_temp_file_destination') . Tools::getValue($key . '_temp_file');
                $temp_file = dirname(__FILE__) . $this->_temp_upload_dir . Tools::getValue($key . '_temp_file');
                if (self::_isRealFile($final_file)) {
                    @unlink($final_file);
                }
                if (self::_isRealFile($temp_file)) {
                    @unlink($temp_file);
                }
                $value = '';
                $clearTempDirectory = true;
            } elseif (preg_match('/activestatus/', $key)) {
                $key = 'active';
            } elseif (preg_match('/height$|width$/i', $key)) {
                $value=trim($value);
                if (!Validate::isInt($value)) {
                    $value = '' ;
                    continue;
                }
                $unit = (Tools::getValue($key . '_unit') == 1?'px':'%');
                $value = $value.$unit ;
            }
            if (key_exists($key, $destination)) {
                if ($destination_type == 'object') {
                    $destination->{$key} = $value;
                } else {
                    $destination[$key] = $value;
                }
            }
        }
        if ($destination_type == 'object') {
            $rules = call_user_func(array(get_class($destination), 'getValidationRules' ), get_class($destination));
            if (sizeof($rules['validateLang'])) {
                $languages = Language::getLanguages(false);
                foreach ($languages as $language) {
                    foreach (array_keys($rules['validateLang']) as $field) {
                        if ((isset($data[$field . '_' . (int)$language['id_lang'] . '_temp_file_lang'])
                        && $data[$field . '_' . (int)$language['id_lang'] . '_temp_file_lang'])
                        || (isset($data[$field . '_all_lang']) && !$destination->{$field}[(int)$language['id_lang']]
                        && $data[$field . '_all_lang']
                        && isset($data[$field . '_' . (int)$this->_default_language . '_temp_file_lang'])
                        && $data[$field . '_' . (int)$this->_default_language . '_temp_file_lang'])) {
                            if (isset($data[$field . '_all_lang'])
                            && $data[$field . '_all_lang']
                            && $language['id_lang'] != $this->_default_language) {
                                $key_default_language = $field . '_' . (int)$this->_default_language . '_temp_file_lang';
                                $old_file = $data[$key_default_language];
                                $new_temp_file_lang = uniqid(self::$_module_prefix . mt_rand()).'.'.self::_getFileExtension($data[$key_default_language]);
                            }
                            $key = $field . '_' . (int)$language['id_lang'] . '_temp_file_lang';
                            $final_destination = dirname(__FILE__) . Tools::getValue($key . '_destination_lang');
                            if (isset($data[$field . '_all_lang']) && $data[$field . '_all_lang'] && $language['id_lang'] != $this->_default_language) {
                                $final_file = $final_destination . $new_temp_file_lang;
                                $temp_file = dirname(__FILE__) . $this->_temp_upload_dir . $old_file;
                            } else {
                                $final_file = $final_destination . Tools::getValue($key);
                                $temp_file = dirname(__FILE__) . $this->_temp_upload_dir . Tools::getValue($key);
                            }
                            if (self::_isRealFile($temp_file)) {
                                copy($temp_file, $final_file);
                            }
                            $key = preg_replace('/_temp_file_lang$/', '', $key);
                            if ($old_file = Tools::getValue($key . '_old_file_lang')) {
                                if (self::_isRealFile($final_destination . Tools::getValue($key . '_old_file_lang'))) {
                                    @unlink($final_destination . Tools::getValue($key . '_old_file_lang'));
                                }
                            }
                            if (isset($data[$field . '_all_lang'])
                            && $data[$field . '_all_lang']
                            && $language['id_lang'] != $this->_default_language) {
                                $destination->{$field}[(int)$language['id_lang']] = $new_temp_file_lang;
                            } else {
                                $destination->{$field}[(int)$language['id_lang']] = Tools::getValue($field . '_' . (int)$language['id_lang'] . '_temp_file_lang');
                            }
                            $clearTempDirectory = true;
                        }
                        if (Tools::getIsset($field . '_' . (int)$language['id_lang'] . '_unlink_lang') && Tools::getValue($field . '_' . (int)$language['id_lang'] . '_unlink_lang')) {
                            $key = $field . '_' . (int)$language['id_lang'] . '_unlink_lang';
                            $key = preg_replace('/_unlink_lang$/', '', $key);
                            $final_file = dirname(__FILE__) . Tools::getValue($key . '_temp_file_lang_destination_lang') . Tools::getValue($key . '_old_file_lang');
                            $temp_file = dirname(__FILE__) . $this->_temp_upload_dir . Tools::getValue($key . '_old_file_lang');
                            if (self::_isRealFile($final_file)) {
                                @unlink($final_file);
                            }
                            if (self::_isRealFile($temp_file)) {
                                @unlink($temp_file);
                            }
                            $destination->{$field}[(int)$language['id_lang']] = '';
                            $clearTempDirectory = true;
                        }
                        if (Tools::getIsset($field . '_' . (int)$language['id_lang'])) {
                            $destination->{$field}[(int)$language['id_lang']] = Tools::getValue($field . '_' . (int)$language['id_lang']);
                        }
                    }
                }
            }
        } else {
            $rules = call_user_func(array($destination['class_name'], 'getValidationRules'), $destination['class_name']);
            if (sizeof($rules['validateLang'])) {
                $languages = Language::getLanguages();
                foreach ($languages as $language) {
                    foreach (array_keys($rules['validateLang']) as $field) {
                        if (isset($data[$field . '_' . (int)$language['id_lang'] . '_temp_file_lang']) && Tools::getValue($field . '_' . (int)$language['id_lang'] . '_temp_file_lang')) {
                            $key = $field . '_' . (int)$language['id_lang'] . '_temp_file_lang';
                            $final_destination = dirname(__FILE__) . Tools::getValue($key . '_destination_lang');
                            $final_file = $final_destination . Tools::getValue($key);
                            $temp_file = dirname(__FILE__) . $this->_temp_upload_dir . Tools::getValue($key);
                            if (self::_isRealFile($temp_file)) {
                                rename($temp_file, $final_file);
                            }
                            $key = preg_replace('/_temp_file_lang$/', '', $key);
                            if ($old_file = Tools::getValue($key . '_old_file_lang')) {
                                if (self::_isRealFile($final_destination . Tools::getValue($key . '_old_file_lang'))) {
                                    @unlink($final_destination . Tools::getValue($key . '_old_file_lang'));
                                }
                            }
                            $destination[$field][(int)$language['id_lang']] = Tools::getValue($field . '_' . (int)$language['id_lang'] . '_temp_file_lang');
                            $clearTempDirectory = true;
                        }
                        if (isset($destination[$field . '_' . (int)$language['id_lang'] . '_unlink_lang']) && Tools::getValue($field . '_' . (int)$language['id_lang'] . '_unlink_lang')) {
                            $key = $field . '_' . (int)$language['id_lang'] . '_unlink_lang';
                            $key = preg_replace('/_unlink_lang$/', '', $key);
                            $final_file = dirname(__FILE__) . Tools::getValue($key . '_temp_file_lang_destination_lang') . Tools::getValue($key . '_old_file_lang');
                            $temp_file = dirname(__FILE__) . $this->_temp_upload_dir . Tools::getValue($key . '_old_file_lang');
                            if (self::_isRealFile($final_file)) {
                                @unlink($final_file);
                            }
                            if (self::_isRealFile($temp_file)) {
                                @unlink($temp_file);
                            }
                            $destination[$field][(int)$language['id_lang']] = '';
                            $clearTempDirectory = true;
                        }
                        if (isset($destination[$field . '_' . (int)$language['id_lang']])) {
                            $destination[$field][(int)$language['id_lang']] = $destination[$field . '_' . (int)$language['id_lang']];
                        }
                    }
                }
            }
        }
        if ($clearTempDirectory) {
            $this->_clearDirectory(dirname(__FILE__) . $this->_temp_upload_dir);
        }
        $this->_postCopyFromPost(array('destination'=>$destination));
    }
    public static function _isFilledArray($array)
    {
        return ($array && is_array($array) && sizeof($array));
    }
    public static function shuffleArray(&$a)
    {
        if (is_array($a) && sizeof($a)) {
            $ks = array_keys($a);
            shuffle($ks);
            $new = array();
            foreach ($ks as $k) {
                $new[$k] = $a[$k];
            }
            $a = $new;
            return true;
        }
        return false;
    }
    public static function getDataSerialized($data, $type = 'base64')
    {
        if (is_array($data)) {
            return array_map($type . '_encode', array($data));
        } else {
            return current(array_map($type . '_encode', array($data)));
        }
    }
    public static function getDataUnserialized($data, $type = 'base64')
    {
        if (is_array($data)) {
            return array_map($type . '_decode', array($data));
        } else {
            return current(array_map($type . '_decode', array($data)));
        }
    }
    protected function _cleanOutput()
    {
        $this->_html = '';
        self::_cleanBuffer();
    }
    public static function _cleanBuffer()
    {
        if (ob_get_length() > 0) {
            ob_clean();
        }
    }
    protected function _echoOutput($die = false)
    {
        echo $this->_html;
        if ($die) {
            die();
        }
    }
    protected function _clearDirectory($dir)
    {
        if (!$dh = @opendir($dir)) {
            return;
        }
        while (false !== ($obj = readdir($dh))) {
            if ($obj == '.' || $obj == '..' || $obj == 'index.php') {
                continue;
            }
            if (!@unlink($dir . '/' . $obj)) {
                $this->_clearDirectory($dir . '/' . $obj);
            }
        }
        closedir($dh);
    }
    public static function _isRealFile($filename)
    {
        return (Tools::file_exists_cache($filename) && ! is_dir($filename));
    }
    public function _getTplPath($tpl_name, $view = 'hook')
    {
        if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
            return $this->getTemplatePath('views/templates/' . $view . '/1.7/' . $tpl_name);
        }
        return $this->getTemplatePath('views/templates/' . $view . '/' . $tpl_name);
    }
    protected static function hex2rgb($hexstr)
    {
        if (Tools::strlen($hexstr) < 7) {
            $hexstr = $hexstr.str_repeat(Tools::substr($hexstr, -1), 7-Tools::strlen($hexstr));
        }
        $int = hexdec($hexstr);
        return array(
            0 => 0xFF & ($int >> 0x10),
            1 => 0xFF & ($int >> 0x8),
            2 => 0xFF & $int
        );
    }
    protected static function tls2rgb($t, $l, $s)
    {
        if ($t<0) {
            $t = 360+$t;
        }
        if ($l<0) {
            $l = 0;
        }
        if ($s<0) {
            $s = 0;
        }
        if ($t>360) {
            $t = $t-360;
        }
        if ($l>255) {
            $l = 255;
        }
        if ($s>250) {
            $s = 250;
        }
        $l /= 255;
        $s /= 255;
        if ($l < 1/2) {
            $q = $l * (1 + $s);
        } elseif ($l >= 1/2) {
            $q = $l + $s - ($l * $s);
        }
        $p = 2 * $l - $q;
        $hk = $t / 360;
        $a = array(
            0 => $hk + 1/3,
            1 => $hk,
            2 => $hk - 1/3,
        );
        $z = array();
        foreach ($a as $k => &$tc) {
            if ($tc < 0) {
                $tc++;
            } elseif ($tc > 1) {
                $tc--;
            }
            if ($tc < 1/6) {
                $z[$k] = $p + (($q - $p) * 6 * $tc);
            } elseif ($tc >= 1/6 && $tc < 1/2) {
                $z[$k] = $q;
            } elseif ($tc >= 1/2 && $tc < 2/3) {
                $z[$k] = $p + (($q - $p) * 6 * (2/3 - $tc));
            } else {
                $z[$k] = $p;
            }
        }
        $z[0] = (int)round($z[0] * 255);
        $z[1] = (int)round($z[1] * 255);
        $z[2] = (int)round($z[2] * 255);
        return $z;
    }
    protected static function rgb2tls($r, $v, $b)
    {
        $max = max($r, $v, $b);
        $min = min($r, $v, $b);
        if ($max == $min) {
            $t = 0;
        }
        if ($max == $r) {
            @$t = 60 * (($v - $b) / ($max - $min));
        } elseif ($max == $v) {
            @$t = 60 * (($b - $r) / ($max - $min)) + 120;
        } elseif ($max == $b) {
            @$t = 60 * (($r - $v) / ($max - $min)) + 240;
        }
        $t = (int)round($t);
        $l = 1/2 * ($max + $min);
        $l2 = $l / 255;
        $l = (int)round($l);
        if ($max == $min) {
            $s = 0;
        } elseif ($l2 <= 1/2) {
            $s = ($max - $min) / (2*$l2);
        } elseif ($l2 > 1/2) {
            $s = ($max - $min) / (2 - 2*$l2);
        }
        $s = (int)round($s);
        if ($t<0) {
            $t = 360+$t;
        }
        if ($l<0) {
            $l = 0;
        }
        if ($s<0) {
            $s = 0;
        }
        if ($t>360) {
            $t = $t-360;
        }
        if ($l>255) {
            $l = 255;
        }
        if ($s>250) {
            $s = 250;
        }
        return array($t, $l , $s);
    }
    protected static function rgb2hex($r, $g, $b)
    {
        if (is_array($r) && sizeof($r) == 3) {
            list($r, $g, $b) = $r;
        }
        $r = (int)$r;
        $g = (int)$g;
        $b = (int)$b;
        $r = dechex($r<0?0:($r>255?255:$r));
        $g = dechex($g<0?0:($g>255?255:$g));
        $b = dechex($b<0?0:($b>255?255:$b));
        $color = (Tools::strlen($r) < 2?'0':'').$r;
        $color .= (Tools::strlen($g) < 2?'0':'').$g;
        $color .= (Tools::strlen($b) < 2?'0':'').$b;
        return '#'.$color;
    }
    public static function _getCssRule($selector, $rule, $value, $is_important = false, $params = false, &$css_rules = array())
    {
        $css_rule = '';
        switch ($rule) {
            case 'width':
                $value ? $value : 0;
                $css_rule .= ' width:' . $value . ($params && isset($params['suffix']) ? $params['suffix'] : 'px') . ($is_important ? '!important' : '') . ';';
                break;
            case 'height':
                $value ? $value : 0;
                $css_rule .= ' height:' . $value . ($params && isset($params['suffix']) ? $params['suffix'] : 'px') . ($is_important ? '!important' : '') . ';';
                break;
            case 'bg_gradient':
                $val = explode(self::$_gradient_separator, $value);
                if (isset($val[1]) && $val[1]) {
                    $color1 = htmlentities($val[0], ENT_COMPAT, 'UTF-8');
                    $color2 = htmlentities($val[1], ENT_COMPAT, 'UTF-8');
                } elseif (isset($val[0]) && $val[0]) {
                    $color1 = htmlentities($val[0], ENT_COMPAT, 'UTF-8');
                }
                if (!isset($color1)) {
                    return '';
                }
                $css_rule .= 'background:' . $color1 . ($is_important ? '!important' : '') . ';';
                if (isset($color2)) {
                    $css_rule .= 'background: -webkit-gradient(linear, 0 0, 0 bottom, from(' . $color1 . '), to(' . $color2 . '))' . ($is_important ? '!important' : '') . ';';
                    $css_rule .= 'background: -webkit-linear-gradient(' . $color1 . ', ' . $color2 . ')' . ($is_important ? '!important' : '') . ';';
                    $css_rule .= 'background: -moz-linear-gradient(' . $color1 . ', ' . $color2 . ')' . ($is_important ? '!important' : '') . ';';
                    $css_rule .= 'background: -ms-linear-gradient(' . $color1 . ', ' . $color2 . ')' . ($is_important ? '!important' : '') . ';';
                    $css_rule .= 'background: -o-linear-gradient(' . $color1 . ', ' . $color2 . ')' . ($is_important ? '!important' : '') . ';';
                    $css_rule .= 'background: linear-gradient(' . $color1 . ', ' . $color2 . ')' . ($is_important ? '!important' : '') . ';';
                    $css_rule .= '-pie-background: linear-gradient(' . $color1 . ', ' . $color2 . ')' . ($is_important ? '!important' : '') . ';';
                }
                break;
            case 'css3button':
                if (!trim($value)) {
                    return '';
                }
                $base_color_hex = $value;
                $base_color_rgb = self::hex2rgb($base_color_hex);
                $base_color_tls = self::rgb2tls($base_color_rgb[0], $base_color_rgb[1], $base_color_rgb[2]);
                $border_color_rgb = self::tls2rgb((int)$base_color_tls[0], (int)$base_color_tls[1]-49, (int)$base_color_tls[2]-16);
                $top0_color_rgb = self::tls2rgb((int)$base_color_tls[0], (int)$base_color_tls[1]+42, (int)$base_color_tls[2]-1);
                $bottom50_color_rgb = self::tls2rgb((int)$base_color_tls[0], (int)$base_color_tls[1]-13, (int)$base_color_tls[2]+18);
                $bottom100_color_rgb = self::tls2rgb((int)$base_color_tls[0], (int)$base_color_tls[1]-10, (int)$base_color_tls[2]+15);
                $boxshadow_color_rgb = self::tls2rgb((int)$base_color_tls[0], (int)$base_color_tls[1]+19, (int)$base_color_tls[2]-29);
                $border_color_hex = self::rgb2hex($border_color_rgb[0], $border_color_rgb[1], $border_color_rgb[2]);
                $top0_color_hex = self::rgb2hex($top0_color_rgb[0], $top0_color_rgb[1], $top0_color_rgb[2]);
                $bottom50_color_hex = self::rgb2hex($bottom50_color_rgb[0], $bottom50_color_rgb[1], $bottom50_color_rgb[2]);
                $bottom100_color_hex = self::rgb2hex($bottom100_color_rgb[0], $bottom100_color_rgb[1], $bottom100_color_rgb[2]);
                $boxshadow_color_hex = self::rgb2hex($boxshadow_color_rgb[0], $boxshadow_color_rgb[1], $boxshadow_color_rgb[2]);
                $css_rule .= 'border: 1px '.$border_color_hex.' solid;'."\n";
                $css_rule .= '-webkit-box-shadow: 0px 0px 0px #aaa, inset 0 5px 10px '.$boxshadow_color_hex.';'."\n";
                $css_rule .= '-moz-box-shadow: 0px 0px 0px #aaa, inset 0 5px 10px '.$boxshadow_color_hex.';'."\n";
                $css_rule .= 'box-shadow: 0px 0px 0px #aaa, inset 0 5px 10px '.$boxshadow_color_hex.';'."\n";
                $css_rule .= 'background-color: '.$base_color_hex.'; /* Old browsers */'."\n";
                $css_rule .= 'background-image: -webkit-gradient(linear, left top, left bottom, color-stop(0%, '.$top0_color_hex.'), color-stop(50%, '.$base_color_hex.'), color-stop(50%, '.$bottom50_color_hex.'), color-stop(100%, '.$bottom100_color_hex.')); /* Chrome,Safari4+ */'."\n";
                $css_rule .= 'background-image: -webkit-linear-gradient(top, '.$top0_color_hex.' 0%, '.$base_color_hex.' 50%, '.$bottom50_color_hex.' 50%, '.$bottom100_color_hex.' 100%); /* Chrome10+,Safari5.1+ */'."\n";
                $css_rule .= 'background-image: -moz-linear-gradient(top, '.$top0_color_hex.' 0%, '.$base_color_hex.' 50%, '.$bottom50_color_hex.' 50%, '.$bottom100_color_hex.' 100%); /* FF3.6+ */'."\n";
                $css_rule .= 'background-image: -ms-linear-gradient(top, '.$top0_color_hex.' 0%, '.$base_color_hex.' 50%, '.$bottom50_color_hex.' 50%, '.$bottom100_color_hex.' 100%); /* IE10+ */'."\n";
                $css_rule .= 'background-image: -o-linear-gradient(top, '.$top0_color_hex.' 0%, '.$base_color_hex.' 50%, '.$bottom50_color_hex.' 50%, '.$bottom100_color_hex.' 100%); /* Opera 11.10+ */'."\n";
                $css_rule .= 'background-image: linear-gradient(top, '.$top0_color_hex.' 0%, '.$base_color_hex.' 50%, '.$bottom50_color_hex.' 50%, '.$bottom100_color_hex.' 100%); /* W3C */'."\n";
                $css_rule .= 'filter: progid:DXImageTransform.Microsoft.gradient(startColorstr=\''.$top0_color_hex.'\', endColorstr=\''.$base_color_hex.'\'); /* IE7,8,9 */';
                break;
            case 'bg_image':
                $css_rule .= 'background-image: url(' . $value . ')' . ($is_important ? '!important' : '') . ';';
                break;
            case 'float':
                $css_rule .= 'float:' . $value . ($is_important ? '!important' : '') . ';';
                break;
            case 'position':
                $css_rule .= 'position:' . $value . ($is_important ? '!important' : '') . ';';
                break;
            case 'color':
                $css_rule .= 'color:' . $value . ($is_important ? '!important' : '') . ';';
                break;
            case 'font_size':
                $value ? $value : 0;
                $css_rule .= 'font-size:' . $value . ($params && isset($params['suffix']) ? $params['suffix'] : 'px') . ($is_important ? '!important' : '') . ';';
                break;
            case 'font_style':
                $value ? $value : 'none';
                $css_rule .= 'font-style:' . $value . ($is_important ? '!important' : '') . ';';
                break;
            case 'font_weight':
                $value ? $value : 'none';
                $css_rule .= 'font-weight:' . $value . ($is_important ? '!important' : '') . ';';
                break;
            case 'text_decoration':
                $value ? $value : 'none';
                $css_rule .= 'text-decoration:' . $value . ($is_important ? '!important' : '') . ';';
                break;
            case 'line_height':
                $value ? $value : 0;
                $css_rule .= 'line-height:' . $value . ($params && isset($params['suffix']) ? $params['suffix'] : 'px') . ($is_important ? '!important' : '') . ';';
                break;
            case 'text_align':
                $value ? $value : 'none';
                $css_rule .= 'text-align:' . $value . ($is_important ? '!important' : '') . ';';
                break;
            case 'border':
                if ($value == 'none') {
                    $css_rule .= 'border:none!important;';
                } else {
                    $val = explode(self::$_border_separator, $value);
                    if (isset($val[5]) && $val[5]) {
                        $top    = htmlentities(str_replace('px', '', $val[0]), ENT_COMPAT, 'UTF-8');
                        $right    = htmlentities(str_replace('px', '', $val[1]), ENT_COMPAT, 'UTF-8');
                        $bottom    = htmlentities(str_replace('px', '', $val[2]), ENT_COMPAT, 'UTF-8');
                        $left    = htmlentities(str_replace('px', '', $val[3]), ENT_COMPAT, 'UTF-8');
                        $style    = htmlentities(str_replace('px', '', $val[4]), ENT_COMPAT, 'UTF-8');
                        $color    = htmlentities(str_replace('px', '', $val[5]), ENT_COMPAT, 'UTF-8');
                    } else {
                        return '';
                    }
                    $css_rule .= 'border-top:'   . $top . ($top ? ($params && isset($params['suffix']) ? $params['suffix'] : 'px'):'') . ($is_important ? '!important' : '') . ';';
                    $css_rule .= 'border-right:'  . $right . ($right ? ($params && isset($params['suffix']) ? $params['suffix'] : 'px'):'') . ($is_important ? '!important' : '') . ';';
                    $css_rule .= 'border-bottom:' . $bottom . ($bottom ? ($params && isset($params['suffix']) ? $params['suffix'] : 'px'):'') . ($is_important ? '!important' : '') . ';';
                    $css_rule .= 'border-left:'  . $left .  ($left ? ($params && isset($params['suffix']) ? $params['suffix'] : 'px'):'') . ($is_important ? '!important' : '') . ';';
                    $css_rule .= 'border-style:' . $style . ($is_important ? '!important' : '') . ';';
                    $css_rule .= 'border-color:' . $color . ($is_important ? '!important' : '') . ';';
                }
                break;
            case 'text_transform':
                $css_rule .= 'text-transform:' . $value . ($is_important ? '!important' : '') . ';';
                break;
            case 'border_size':
                $css_rule .= 'border-size:' . $value . ($is_important ? '!important' : '') . ';';
                break;
            case 'border_radius':
                $css_rule .= '-webkit-border-radius:' . $value . ($is_important ? '!important' : '') . ';';
                $css_rule .= '-moz-border-radius:' . $value . ($is_important ? '!important' : '') . ';';
                $css_rule .= 'border-radius:' . $value . ($is_important ? '!important' : '') . ';';
                break;
            case 'shadow':
                if ($value == 'none') {
                    $css_rule .= '-webkit-box-shadow:none!important;';
                    $css_rule .= '-moz-box-shadow:none!important;';
                    $css_rule .= 'box-shadow:none!important;';
                } else {
                    $val = explode(self::$_shadow_separator, $value);
                    $css_rule .= '-webkit-box-shadow:' . $val[0] .' '. $val[1] .' '. $val[2] .' '. $val[3].($is_important ? '!important' : '') . ';';
                    $css_rule .= '-moz-box-shadow:' . $val[0] .' '. $val[1] .' '. $val[2] .' '. $val[3].($is_important ? '!important' : '') . ';';
                    $css_rule .= 'box-shadow:' . $val[0] .' '. $val[1] .' '. $val[2] .' '. $val[3].($is_important ? '!important' : '') . ';';
                }
                break;
            case 'margin':
                $css_rule .= 'margin:' . $value . ($is_important ? '!important' : '') . ';';
                break;
            case 'padding':
                $css_rule .= 'padding:' . $value . ($is_important ? '!important' : '') . ';';
                break;
            case 'opacity':
                $css_rule .= '-ms-filter:"progid:DXImageTransform.Microsoft.Alpha(Opacity=' . $value . ')"';
                $css_rule .= 'filter: alpha(opacity=' . $value . ')' . ($is_important ? '!important' : '') . ';';
                $css_rule .= '-khtml-opacity:' . ($value / 100) . ($is_important ? '!important' : '') . ';';
                $css_rule .= '-moz-opacity:' . ($value / 100) . ($is_important ? '!important' : '') . ';';
                $css_rule .= 'opacity:' . ($value / 100) . ($is_important ? '!important' : '') . ';';
                break;
            case 'custom':
                $css_rule .= $value;
                break;
        }
        if (!isset($css_rules[$selector])) {
            $css_rules[$selector] = array();
        }
        $css_rules[$selector][] = $css_rule;
        return $css_rules;
    }
    protected function getKeyForLanguageFlags()
    {
        return uniqid(self::$_module_prefix . mt_rand());
    }
    protected function displayPMFlags($key = false, $class = false)
    {
        if (!$key) {
            $key = $this->getKeyForLanguageFlags();
        }
        $vars = array(
            'flag_key' => $key,
            'class' => $class,
        );
        $return = $this->fetchTemplate('core/flags.tpl', $vars);
        return $return;
    }
    public static function addSqlAssociation($table, $alias, $identifier, $inner_join = true, $on = null, $shops = false)
    {
        if (Shop::isFeatureActive()) {
            if ($shops == 'all') {
                $ids_shop = array_values(Shop::getCompleteListOfShopsID());
            } elseif (is_array($shops) && sizeof($shops)) {
                $ids_shop = array_values($shops);
            } elseif (is_numeric($shops)) {
                $ids_shop = array($shops);
            } else {
                $ids_shop = array_values(Shop::getContextListShopID());
            }
            $table_alias = $alias.'_shop';
            $tableExplode = explode('.', $table);
            if (sizeof($tableExplode) > 1) {
                list($table_alias, $table) = $tableExplode;
            }
            $sql = (($inner_join) ? ' INNER' : ' LEFT').' JOIN `'._DB_PREFIX_.$table.'_shop` '.$table_alias.'
                        ON '.$table_alias.'.'.$identifier.' = '.$alias.'.'.$identifier.'
                        AND '.$table_alias.'.id_shop IN ('.implode(', ', $ids_shop).') '
                        .(($on) ? ' AND '.$on : '');
            return $sql;
        }
    }
    protected static function _getNbDaysModuleUsage()
    {
        $sql = 'SELECT DATEDIFF(NOW(),date_add)
                FROM '._DB_PREFIX_.'configuration
                WHERE name = \''.pSQL('PM_'.self::$_module_prefix.'_LAST_VERSION').'\'
                ORDER BY date_add ASC';
        return (int)Db::getInstance()->getValue($sql);
    }
    public static function _getSmartyVarValue($varName)
    {
        $smarty = Context::getContext()->smarty;
        if (method_exists($smarty, 'getTemplateVars')) {
            return $smarty->getTemplateVars($varName);
        } elseif (method_exists($smarty, 'get_template_vars')) {
            return $smarty->get_template_vars($varName);
        }
        return false;
    }
    protected function _onBackOffice()
    {
        if (isset($this->context->cookie->id_employee) && Validate::isUnsignedId($this->context->cookie->id_employee)) {
            return true;
        }
        return false;
    }
    public static function arrayMapRecursive($fn, $arr)
    {
        if (!is_array($arr)) {
            return array();
        }
        $rarr = array();
        foreach ($arr as $k => $v) {
            if (is_array($v)) {
                $rarr[$k] = self::arrayMapRecursive($fn, $v);
                if (!count($rarr[$k])) {
                    unset($rarr[$k]);
                }
            } else {
                if (preg_match('#~#', $v)) {
                    $interval = explode('~', $v);
                } else {
                    $interval = null;
                }
                if ($interval != null && is_array($interval) && count($interval) == 2) {
                    $isValidInterval = true;
                    foreach ($interval as $kInterval => $intervalValue) {
                        if ($kInterval == 1 && $intervalValue == '' && is_numeric($interval[0])) {
                            continue;
                        } elseif (!is_numeric($intervalValue)) {
                            $isValidInterval = false;
                            break;
                        }
                    }
                    if ($isValidInterval) {
                        $rarr[$k] = $v;
                    }
                } else {
                    $rarr[$k] = call_user_func($fn, $v);
                    if ($rarr[$k] == 0) {
                        unset($rarr[$k]);
                    }
                }
            }
        }
        return $rarr;
    }
    public function smartyNoFilterModifier($s)
    {
        return $s;
    }
    protected function registerFrontSmartyObjects()
    {
        static $registeredFO = false;
        if (!$registeredFO && !empty($this->context->smarty)) {
            $this->context->smarty->unregisterPlugin('modifier', Tools::strtolower(self::$_module_prefix).'_nofilter');
            $this->context->smarty->registerPlugin('modifier', Tools::strtolower(self::$_module_prefix).'_nofilter', array($this, 'smartyNoFilterModifier'));
            $registeredFO = true;
        }
    }
    protected function registerSmartyObjects()
    {
        static $registered = false;
        if (!$registered && !empty($this->context->smarty)) {
            $this->registerFrontSmartyObjects();
            $this->context->smarty->unregisterPlugin('function', Tools::strtolower(self::$_module_prefix) . '_startForm');
            $this->context->smarty->unregisterPlugin('function', Tools::strtolower(self::$_module_prefix) . '_endForm');
            $this->context->smarty->unregisterPlugin('function', Tools::strtolower(self::$_module_prefix) . '_button');
            $this->context->smarty->unregisterPlugin('function', Tools::strtolower(self::$_module_prefix) . '_ajaxSelectMultiple');
            $this->context->smarty->unregisterPlugin('function', Tools::strtolower(self::$_module_prefix) . '_inputActive');
            $this->context->smarty->unregisterPlugin('function', Tools::strtolower(self::$_module_prefix) . '_inputText');
            $this->context->smarty->unregisterPlugin('function', Tools::strtolower(self::$_module_prefix) . '_select');
            $this->context->smarty->unregisterPlugin('function', Tools::strtolower(self::$_module_prefix) . '_initDataTable');
            $this->context->smarty->registerPlugin('function', Tools::strtolower(self::$_module_prefix) . '_startForm', array($this, '_startForm'));
            $this->context->smarty->registerPlugin('function', Tools::strtolower(self::$_module_prefix) . '_endForm', array($this, '_endForm'));
            $this->context->smarty->registerPlugin('function', Tools::strtolower(self::$_module_prefix) . '_button', array($this, '_addButton'));
            $this->context->smarty->registerPlugin('function', Tools::strtolower(self::$_module_prefix) . '_ajaxSelectMultiple', array($this, '_displayAjaxSelectMultiple'));
            $this->context->smarty->registerPlugin('function', Tools::strtolower(self::$_module_prefix) . '_inputActive', array($this, '_displayInputActive'));
            $this->context->smarty->registerPlugin('function', Tools::strtolower(self::$_module_prefix) . '_inputText', array($this, '_displayInputText'));
            $this->context->smarty->registerPlugin('function', Tools::strtolower(self::$_module_prefix) . '_select', array($this, '_displaySelect'));
            $this->context->smarty->registerPlugin('function', Tools::strtolower(self::$_module_prefix) . '_initDataTable', array($this, '_initDataTable'));
            $this->context->smarty->registerObject('module', $this, array(
                '_showWarning',
                '_showInfo',
                '_displayTitle',
                '_displaySubTitle',
                '_displaySubmit',
                '_displaySupport',
            ), false);
            $registered = true;
        }
    }
    protected function fetchTemplate($tpl, $customVars = array(), $configOptions = array())
    {
        $this->registerSmartyObjects();
        $this->context->smarty->assign(array(
            'ps_major_version' => Tools::substr(str_replace('.', '', _PS_VERSION_), 0, 2),
            'module_name' => $this->name,
            'module_path' => $this->_path,
            'base_config_url' => $this->_base_config_url,
            'current_iso_lang' => $this->_iso_lang,
            'current_id_lang' => (int)$this->context->language->id,
            'default_language' => $this->_default_language,
            'languages' => $this->_languages,
            'options' => $configOptions,
        ));
        if (sizeof($customVars)) {
            $this->context->smarty->assign($customVars);
        }
        return $this->context->smarty->fetch(_PS_MODULE_DIR_ . $this->name . '/views/templates/admin/' . $tpl);
    }
    public static function autoExecute($table, $data, $type = 'INSERT', $where = '', $limit = 0, $use_cache = true, $use_null = false)
    {
        if (version_compare(_PS_VERSION_, '1.5.0.5', '>=')) {
            $type = Tools::strtoupper($type);
            switch ($type) {
                case 'INSERT':
                    return Db::getInstance()->insert($table, $data, $use_null, $use_cache, Db::INSERT, true);
                case 'INSERT IGNORE':
                    return Db::getInstance()->insert($table, $data, $use_null, $use_cache, Db::INSERT_IGNORE, true);
                case 'REPLACE':
                    return Db::getInstance()->insert($table, $data, $use_null, $use_cache, Db::REPLACE, true);
                case 'UPDATE':
                    return Db::getInstance()->update($table, $data, $where, $limit, $use_null, $use_cache, true);
                default:
                    throw new PrestaShopDatabaseException('Wrong argument (miss type) in Db::autoExecute()');
            }
        } else {
            Db::getInstance()->AutoExecute(_DB_PREFIX_ . $table, $data, $type, $where, $limit, $use_cache, $use_null);
        }
    }
}
