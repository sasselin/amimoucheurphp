<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2018 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

class Recaptcha extends Module
{
    private $html;

    public function __construct()
    {
        $this->author = 'Charlie';
        $this->name = 'recaptcha';
        $this->tab = 'front_office_features';
        $this->version = '1.2.3';
        $this->need_instance = 1;
        $this->bootstrap = true;
        $this->module_key = '043ded363584caaa9e93cf89cbaac5e9';
        parent::__construct();
        if (version_compare(_PS_VERSION_, '1.5', '<')) {
            require(_PS_MODULE_DIR_.$this->name.'/backward_compatibility/backward.php');
        }
        $this->ps_versions_compliancy = array('min' => '1.4.0.2', 'max' => '1.7.99.99');
        $this->displayName = $this->l('reCaptcha');
        $this->description = $this->l('Add a captcha to the registration and contact forms.');
        if ($this->active &&
            (!Configuration::get('CAPTCHA_PUBLIC_KEY') || !Configuration::get('CAPTCHA_PRIVATE_KEY'))) {
            $this->warning = $this->l('ReCaptcha Module needs to be configured.');
        }
    }

    public function install()
    {
        Configuration::updateValue('CAPTCHA_OVERLOAD', '1');
        Configuration::updateValue('CAPTCHA_VERSION', 'v2');
        Configuration::updateValue('CAPTCHA_THEME', 'light');

        $this->warning = 'warning';
        if (version_compare(_PS_VERSION_, '1.5', '<')) {
            if (!Db::getInstance()->getValue('SELECT id_hook FROM '._DB_PREFIX_.'hook WHERE name = "contactFormBottom"')) {
                Db::getInstance()->autoExecute(_DB_PREFIX_.'hook', array(
                        'name' => 'contactFormBottom',
                        'title' => 'Contact form bottom',
                        'description' => null,
                        'position' => 1,
                        'live_edit' => 1
                    ), 'INSERT');
            }
            if (!Db::getInstance()->getValue('SELECT id_hook FROM '._DB_PREFIX_.'hook WHERE name = "contactCaptchaValidate"')) {
                Db::getInstance()->autoExecute(_DB_PREFIX_.'hook', array(
                        'name' => 'contactCaptchaValidate',
                        'title' => 'Contact Captcha Validate',
                        'description' => null,
                        'position' => 1,
                        'live_edit' => 1
                    ), 'INSERT');
            }
            if (!Db::getInstance()->getValue('SELECT id_hook FROM '._DB_PREFIX_.'hook WHERE name = "storespagesCaptchaValidate"')) {
                Db::getInstance()->autoExecute(_DB_PREFIX_.'hook', array(
                        'name' => 'storespagesCaptchaValidate',
                        'title' => 'Storespages Captcha Validate',
                        'description' => null,
                        'position' => 1,
                        'live_edit' => 1
                    ), 'INSERT');
            }
            if (!Db::getInstance()->getValue('SELECT id_hook FROM '._DB_PREFIX_.'hook WHERE name = "displayStorespagesCaptcha"')) {
                Db::getInstance()->autoExecute(_DB_PREFIX_.'hook', array(
                        'name' => 'displayStorespagesCaptcha',
                        'title' => 'Display Storespages Captcha',
                        'description' => null,
                        'position' => 1,
                        'live_edit' => 1
                    ), 'INSERT');
            }
            if (!Db::getInstance()->getValue('SELECT id_hook FROM '._DB_PREFIX_.'hook WHERE name = "displayMobileHeader"')) {
                Db::getInstance()->autoExecute(_DB_PREFIX_.'hook', array(
                        'name' => 'displayMobileHeader',
                        'title' => 'Display mobile header',
                        'description' => null,
                        'position' => 1,
                        'live_edit' => 1
                    ), 'INSERT');
            }
            if (!parent::install()
                || !$this->registerHook('header')
                || !$this->registerHook('displayMobileHeader')
                || !$this->registerHook('createAccountForm')
                || !$this->registerHook('storespagesCaptchaValidate')
                || !$this->registerHook('displayStorespagesCaptcha')
                || !$this->registerHook('contactFormBottom')) {
                return false;
            }
            if (file_exists(_PS_ROOT_DIR_.'/override/controllers/ContactController.php')
                || !copy(
                    _PS_MODULE_DIR_.'recaptcha/override/controllers/front/ContactController.php',
                    _PS_ROOT_DIR_.'/override/controllers/ContactController.php'
                )) {
                return false;
            }

            if (file_exists(_PS_ROOT_DIR_.'/override/controllers/AuthController.php')
                || !copy(
                    _PS_MODULE_DIR_.'recaptcha/override/controllers/front/AuthController.php',
                    _PS_ROOT_DIR_.'/override/controllers/AuthController.php'
                )) {
                return false;
            }
        } else {
            if (!parent::install()
                || !$this->registerHook('header')
                || !$this->registerHook('displayMobileHeader')
                || !$this->registerHook('displayCustomerAccountForm')
                || !$this->registerHook('actionBeforeSubmitAccount')
                || !$this->registerHook('storespagesCaptchaValidate')
                || !$this->registerHook('displayStorespagesCaptcha')
                || !$this->registerHook('contactFormBottom')
                || !$this->registerHook('contactCaptchaValidate')) {
                return false;
            }

            if (version_compare(_PS_VERSION_, '1.7.0', '>=')) {
                $dir = _PS_ROOT_DIR_.'/override/modules/contactform';
                if (!is_dir($dir)) {
                    @mkdir($dir, 0777);
                }

                if (is_dir($dir)) {
                    $source = _PS_MODULE_DIR_.$this->name.'/override/modules/contactform/contactform.php';
                    $dest =  $dir.'/contactform.php';
                    Tools::copy($source, $dest);
                }
            }
        }

        $this->clearSmartyCache();

        return true;
    }

    public function uninstall($keep_data = false)
    {
        if (!$keep_data) {
            Configuration::deleteByName('CAPTCHA_PUBLIC_KEY');
            Configuration::deleteByName('CAPTCHA_PRIVATE_KEY');
            Configuration::deleteByName('CAPTCHA_ENABLE_ACCOUNT');
            Configuration::deleteByName('CAPTCHA_ENABLE_CONTACT');
            Configuration::deleteByName('CAPTCHA_VERSION');
            Configuration::deleteByName('CAPTCHA_OVERLOAD');
            Configuration::deleteByName('CAPTCHA_THEME');
        }
        if (!parent::uninstall()) {
            return false;
        }
        $this->clearSmartyCache();
        return true;
    }

    public function reset($keep_data = true)
    {
        if ($this->uninstall($keep_data)) {
            return $this->install($keep_data);
        }
    }




    //Form configuration backoffice
    public function getContent()
    {
        $controller = $this->getHookController('getContent');
        return $controller->run();
    }

    public function hookHeader()
    {
        //to alter ajax request on OPC registration
        if (version_compare(_PS_VERSION_, '1.7', '<') &&
            ($this->context->controller instanceof OrderOpcController || $this->context->controller instanceof ControllerBackwardModule) &&
            Configuration::get('CAPTCHA_ENABLE_ACCOUNT')) {
            if (method_exists($this->context->controller, 'addJS')) {
                $this->context->controller->addJS($this->_path.'views/js/front.js');
            } elseif (method_exists(Tools, 'addJS')) {
                Tools::addJS($this->_path.'views/js/front.js');
            }
        }
        if ($this->context->controller instanceof ControllerBackwardModule
            || $this->context->controller instanceof ProductController
            || ($this->context->controller instanceof ContactController && Configuration::get('CAPTCHA_ENABLE_CONTACT'))
            || ($this->context->controller instanceof AuthController && Configuration::get('CAPTCHA_ENABLE_ACCOUNT'))
            || ($this->context->controller instanceof ParentOrderController && Configuration::get('CAPTCHA_ENABLE_ACCOUNT'))
            || ($this->context->controller instanceof OrderOpcController && Configuration::get('CAPTCHA_ENABLE_ACCOUNT'))
            || ($this->context->controller instanceof OrderController && Configuration::get('CAPTCHA_ENABLE_ACCOUNT'))
            || $this->context->controller instanceof StoresPagesStoreModuleFrontController) {
            if (method_exists($this->context->controller, 'registerJavascript')) {
               // $this->context->controller->registerJavascript(
               //     'remote-recaptcha',
               //     'https://www.google.com/recaptcha/api.js?hl='.$this->context->language->language_code,
               //     array('server' => 'remote')
               // );
                $this->context->controller->registerJavascript(
                    'remote-recaptcha',
                    $this->_path.'views/js/front.js',
                    array('server' => 'remote')
                );
            } elseif (version_compare(_PS_VERSION_, '1.5.0', '>') && method_exists($this->context->controller, 'addJS')) {
                $this->context->controller->addJS($this->_path.'views/js/front.js');
            } elseif (version_compare(_PS_VERSION_, '1.5.0', '>') && method_exists(Tools, 'addJS')) {
                Tools::addJS($this->_path.'views/js/front.js');
            } elseif (method_exists(Tools, 'addJS')) {
                Tools::addJS($this->_path.'views/js/front.js');
            }
        }
    }

    protected function displayCaptcha()
    {
        $this->context->smarty->assign('site_key', Configuration::get('CAPTCHA_PUBLIC_KEY'));
        $this->context->smarty->assign('theme', Configuration::get('CAPTCHA_THEME'));
        $this->context->smarty->assign('lang', $this->context->language->language_code);

        if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
            return $this->fetch('module:recaptcha/views/templates/hook/captcha1.7.tpl');
        } else {
            return $this->display(__FILE__, '/views/templates/hook/captcha.tpl');
        }
    }


    public function validateCaptcha()
    {
        $google_request_url =
            'https://www.google.com/recaptcha/api/siteverify?secret='.
            Configuration::get('CAPTCHA_PRIVATE_KEY').
            '&response='.Tools::getValue('g-recaptcha-response').
            '&remoteip='.$_SERVER['REMOTE_ADDR'];

        $ch = curl_init($google_request_url);
        curl_setopt_array($ch, array(
            CURLOPT_TIMEOUT => 10,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_HEADER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ));
        $google_response = curl_exec($ch);
        curl_close($ch);

        if (version_compare(_PS_VERSION_, '1.7.0.0', '<')) {
            $google_response = Tools::jsonDecode($google_response);
        } else {
            $google_response = json_decode($google_response);
        }

        if (empty($google_response->success) || $google_response->success !== true) {
            if (version_compare(_PS_VERSION_, '1.5', '>=')) {
                Context::getContext()->controller->errors[] = $this->l('Invalid captcha.');
            }
            return false;
        }

        return true;
    }



    /*
     * Display Hooks
     */
    public function hookDisplayMobileHeader()
    {
        return $this->hookHeader();
    }

    public function hookCreateAccountForm()
    {
        if (Configuration::get('CAPTCHA_ENABLE_ACCOUNT') == 1) {
            return $this->displayCaptcha();
        }
    }

    public function hookDisplayCustomerAccountForm()
    {
        if (Configuration::get('CAPTCHA_ENABLE_ACCOUNT') == 1) {
            return $this->displayCaptcha();
        }
    }

    public function hookDisplayStorespagesCaptcha()
    {
        return $this->displayCaptcha();
    }

    public function hookContactFormBottom()
    {
        if (Configuration::get('CAPTCHA_ENABLE_CONTACT') == 1) {
            return $this->displayCaptcha();
        }
    }


    /*
     * Validation Hooks
     */
    public function hookContactCaptchaValidate()
    {
        if (Configuration::get('CAPTCHA_ENABLE_CONTACT') == 1) {
            return $this->validateCaptcha();
        }
    }

    public function hookActionBeforeSubmitAccount()
    {
        if (Configuration::get('CAPTCHA_ENABLE_ACCOUNT') == 1) {
            return $this->validateCaptcha();
        }
        return true;
    }

    // For storespages module
    public function hookStorespagesCaptchaValidate()
    {
        return $this->validateCaptcha();
    }




    /*
     * Other functions
     */
    protected function clearSmartyCache()
    {
        if (method_exists('ToolsCore', 'clearSmartyCache')) {
            Tools::clearSmartyCache();
        } else {
            $smarty = Context::getContext()->smarty;
            $smarty->clearAllCache();
            $smarty->clearCompiledTemplate();
        }

        if (file_exists(_PS_ROOT_DIR_.'/cache/class_index.php')) {
            unlink(_PS_ROOT_DIR_.'/cache/class_index.php');
        }
    }
    public function getHookController($hook_name)
    {
        require_once(dirname(__FILE__).'/controllers/hook/'.$hook_name.'.php');
        $controller_name = $this->name.$hook_name.'Controller';
        $controller = new $controller_name($this, __FILE__, $this->_path);
        return $controller;
    }
}
