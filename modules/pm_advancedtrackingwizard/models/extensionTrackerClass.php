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

require_once(_PS_ROOT_DIR_.'/modules/pm_advancedtrackingwizard/AdvancedTrackingCoreClass.php');
class extensionTrackerClass extends AdvancedTrackingCoreClass
{
    public $id = null;
    public $category = null;
    public $author = null;
    public $version = null;
    public $sqlInstall = 'install.sql';
    public $sqlUninstall = 'uninstall.sql';
    public $extension_name = '';
    public $displayName = null;
    public $description = null;
    public $_base_config_url;
    public $_default_language;
    public $_iso_lang;
    public $_languages;
    public $_path;
    public $is_installed;
    public $_base_module_name;
    public $tracker_page = 'all';
    public static $extension_loaded = array();
    public static $extension_dir = '/modules/pm_advancedtrackingwizard/models/extensions/';
    protected static $l_cache = array();
    protected static $_generateConfigXmlMode = false;
    protected $table = 'pm_advancedtrackingwizard_extensions';
    protected $_html = '';
    protected $identifier = 'id_extension';
    protected $fieldsRequired = array('id_extension','extension_name');
    protected $fieldsSize = array();
    protected $_html_at_end;
    protected $fieldsValidate = array();
    protected $_css_js_lib_loaded = array();
    protected $_css_js_to_load = array('core', 'jquerytiptip', 'jgrowl', 'datatables', 'codemirrorcore', 'codemirrormixed', 'collision', 'multiselect', 'chosen');
    protected $context;
    public $name = 'pm_advancedtrackingwizard';
    protected $_dB_insert_exclude = array(
        'name',
        'postExtensionConfigurationForm',
        'id_tracking',
        'submit',
        'active'
    );
    public function __construct()
    {
        if (!isset($this->context)) {
            $this->context = Context::getContext();
        }
        $this->context->smarty = Context::getContext()->smarty;
        $this->_base_module_name = 'pm_advancedtrackingwizard';
        $this->_initClassVar();
        return true;
    }
    protected function postProcess()
    {
    }
    protected function _initClassVar()
    {
        $this->_path = __PS_BASE_URI__.'modules/pm_advancedtrackingwizard/';
        parent::_initClassVar();
    }
    public function install()
    {
        if (!Validate::isModuleName($this->extension_name)) {
            die(Tools::displayError());
        }
        if ($this->extensionExistsOnDb($this->extension_name)) {
            return false;
        }
        if (!$this->installSQL()) {
            return false;
        }
        if (!$this->extensionExistsOnDb($this->extension_name)) {
            $result = AdvancedTrackingCoreClass::autoExecute($this->table, array('extension_name' => $this->extension_name));
            if (!$result) {
                return false;
            }
            $this->id = Db::getInstance()->Insert_ID();
        }
        return true;
    }
    public function installSQL()
    {
        if (file_exists(_PS_ROOT_DIR_ . self::$extension_dir.$this->extension_name.'/'. $this->sqlInstall) && $sql = Tools::file_get_contents(_PS_ROOT_DIR_ . self::$extension_dir.$this->extension_name.'/'.  $this->sqlInstall)) {
            $sql = str_replace('PREFIX_', _DB_PREFIX_, $sql);
            $sql = str_replace('MYSQL_ENGINE', _MYSQL_ENGINE_, $sql);
            $sql = preg_split("/;\s*[\r\n]+/", $sql);
            foreach ($sql as $query) {
                if (!Db::getInstance()->Execute(trim($query))) {
                    return false;
                }
            }
        }
        $row = array('id_extension' => (int)$this->id, 'extension_name' => pSQL($this->extension_name));
        AdvancedTrackingCoreClass::autoExecute($this->table, $row);
        return true;
    }
    public function uninstallSQL()
    {
        if (file_exists(_PS_ROOT_DIR_ . self::$extension_dir.$this->extension_name.'/'. $this->sqlUninstall) && $sql = Tools::file_get_contents(_PS_ROOT_DIR_ . self::$extension_dir.$this->extension_name.'/'.  $this->sqlUninstall)) {
            $sql = str_replace('PREFIX_', _DB_PREFIX_, $sql);
            $sql = str_replace('MYSQL_ENGINE', _MYSQL_ENGINE_, $sql);
            $sql = preg_split("/;\s*[\r\n]+/", $sql);
            foreach ($sql as $query) {
                if (!Db::getInstance()->Execute(trim($query))) {
                    return false;
                }
            }
        }
        return true;
    }
    public function uninstall()
    {
        if (!$this->uninstallSQL()) {
            return false;
        }
        return true;
    }
    protected function getContent()
    {
        parent::getContent();
        $this->postProcess();
    }
    public function displayForm($id_tracking)
    {
        $vars = array(
            'internal_name' => $this->extension_name,
            'id_tracking' => (int) $id_tracking,
            'vars' => $this->getFormVars((int) $id_tracking),
        );
        return $this->fetchTemplate('module/forms/extension/'.$this->template.'.tpl', $vars);
    }
    public function getFormVars($id_tracking)
    {
        return array();
    }
    public function extensionExistsOnDb($name)
    {
        $row = Db::getInstance()->getRow('
        SELECT `id_extension`
        FROM `'._DB_PREFIX_.$this->table.'`
        WHERE `extension_name` = "'.pSQL($this->extension_name).'"');
        return (isset($row['id_extension'])?$row['id_extension']:false);
    }
    public static function isExtension($name)
    {
        if (file_exists(_PS_ROOT_DIR_ . self::$extension_dir.$name.'/'.$name.'.php')) {
            return true;
        }
        return false;
    }
    public static function loadExtension($name)
    {
        if (isset(self::$extension_loaded[Tools::strtolower($name)])) {
            return self::$extension_loaded[Tools::strtolower($name)];
        }
        if (file_exists(_PS_ROOT_DIR_ . self::$extension_dir.$name.'/'.$name.'.php')) {
            include_once(_PS_ROOT_DIR_ . self::$extension_dir.$name.'/'.$name.'.php');
            $objExtension = new $name();
            self::$extension_loaded[Tools::strtolower($name)] = $objExtension;
            return $objExtension;
        }
        return false;
    }
    public static function initExtensionsFromDisk()
    {
        $extensions = scandir(_PS_ROOT_DIR_ . self::$extension_dir);
        foreach ($extensions as $file) {
            if ($file != '.' && $file != '..' && is_dir(_PS_ROOT_DIR_ . self::$extension_dir.$file)) {
                if (self::isExtension($file)) {
                    $currentExtension = self::loadExtension($file);
                    $currentExtension->install();
                }
            }
        }
    }
    public static function delete($id_tracking)
    {
        Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'pm_advancedtrackingwizard_extensions_id` WHERE `id_tracking` = '.(int)$id_tracking);
        Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'pm_advancedtrackingwizard_extensions_var` WHERE `id_tracking` = '.(int)$id_tracking);
    }
    protected function getVar($var_name, $id_tracking)
    {
        $return = '';
        $results = Db::getInstance()->ExecuteS(
            'SELECT `value`
            FROM `'._DB_PREFIX_.'pm_advancedtrackingwizard_extensions_var`
            WHERE `id_tracking` = '.(int)$id_tracking.'
            AND   `var_name` = "'.pSQL($var_name).'"'
        );
        foreach ($results as $row) {
            $return = $row['value'];
        }
        return $return;
    }
    protected function getAllVar($id_tracking)
    {
        $return = '';
        $results = Db::getInstance()->ExecuteS(
            'SELECT *
            FROM `'._DB_PREFIX_.'pm_advancedtrackingwizard_extensions_var`
            WHERE `id_tracking` = '.(int)$id_tracking
        );
        foreach ($results as $row) {
            $return[$row['var_name']] = $row['value'];
        }
        return $return;
    }
    protected function regVar($post, $id_tracking)
    {
        foreach ($post as $key => $value) {
            if (in_array($key, $this->_dB_insert_exclude)) {
                continue;
            } else {
                if ($this->getVar($key, $id_tracking)) {
                    AdvancedTrackingCoreClass::autoExecute('pm_advancedtrackingwizard_extensions_var', array('id_tracking' => (int)$id_tracking,'value' => $value), 'UPDATE', '`var_name` = "'.$key.'"');
                } else {
                    AdvancedTrackingCoreClass::autoExecute('pm_advancedtrackingwizard_extensions_var', array('id_tracking' => (int)$id_tracking,'var_name' => $key,'value' => $value));
                }
            }
        }
        if (!self::getExtensionId($id_tracking)) {
            $id_extension = (int)$this->extensionExistsOnDb($post['internal_name']);
            AdvancedTrackingCoreClass::autoExecute('pm_advancedtrackingwizard_extensions_id', array('id_tracking' => (int)$id_tracking, 'id_extension' => (int)$id_extension));
        }
    }
    public static function initExtensionsFromDb()
    {
        $extensions = self::getExtensionsFromDb();
        foreach ($extensions as $extension) {
            if (self::isExtension($extension['extension_name'])) {
                self::loadExtension($extension['extension_name']);
            }
        }
    }
    public static function getExtensionsFromDb()
    {
        $results = Db::getInstance()->ExecuteS(
            'SELECT DISTINCT *
            FROM `'._DB_PREFIX_.'pm_advancedtrackingwizard_extensions`'
        );
        $return = array();
        foreach ($results as $result) {
            $ext = self::loadExtension($result['extension_name']);
            if (is_object($ext)) {
                $return[$result['id_extension']] = $ext->displayName;
            }
        }
        return $return;
    }
    public static function getExtensionId($id_tracking)
    {
        $results = Db::getInstance()->ExecuteS(
            'SELECT *
            FROM `'._DB_PREFIX_.'pm_advancedtrackingwizard_extensions_id`
            WHERE `id_tracking` = '.(int)$id_tracking
        );
        return $results;
    }
    public static function loadExtensionsClass($extension_id)
    {
        $result = Db::getInstance()->ExecuteS(
            'SELECT `extension_name`
            FROM `'._DB_PREFIX_.'pm_advancedtrackingwizard_extensions`
            WHERE `id_extension` = '.(int)$extension_id
        );
        $return = '';
        foreach ($result as $row) {
            $return = $row['extension_name'];
        }
        return $return;
    }
    public function _postConfigurationForm($post)
    {
        $id_tracking = Tools::getValue('id_tracking', false);
        $tracker = new AdvancedTrackingWizardClass((int) $id_tracking);
        $tracker->internal_name = $this->extension_name;
        $tracker->active        = Tools::getValue('active');
        $tracker->page          = $this->tracker_page;
        $tracker->is_extension  = true;
        $tracker->tracking_code = '';
        if (!$tracker->save()) {
            return false;
        } else {
            $this->regVar($post, (int) $tracker->id);
            return true;
        }
        $this->_echoOutput(true);
    }
    protected function getMobileDeviceType()
    {
        $siteType = 'd';
        if (version_compare(_PS_VERSION_, '1.6.0.0', '>=') && method_exists('Context', 'getDevice')) {
            $device = Context::getContext()->getDevice();
            if ($device == Context::DEVICE_TABLET) {
                $siteType = 't';
            } elseif ($device == Context::DEVICE_MOBILE) {
                $siteType = 'm';
            }
        } elseif (method_exists('Context', 'getMobileDevice') && Context::getContext()->getMobileDevice()) {
            $siteType = 'm';
        } elseif (isset($_SERVER['HTTP_USER_AGENT']) && (preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i', $_SERVER['HTTP_USER_AGENT'])||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i', Tools::substr($_SERVER['HTTP_USER_AGENT'], 0, 4)))) {
            $siteType = 'm';
        }
        return $siteType;
    }
}
