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

class ContactController extends ContactControllerCore
{



    public function preProcess()
    {
        if (version_compare(_PS_VERSION_, '1.5', '<')) {
            self::$smarty->assign('HOOK_CONTACT_FORM_BOTTOM', Module::hookExec('contactFormBottom'));
        }
        if (version_compare(_PS_VERSION_, '1.5', '<') &&
            Tools::isSubmit('submitMessage') && Module::isInstalled('recaptcha')) {
            require_once(_PS_ROOT_DIR_.'/modules/recaptcha/recaptcha.php');
            $recaptcha = new Recaptcha();
            if (!$recaptcha->validateCaptcha()) {
                $this->errors[] = $recaptcha->l('Invalid captcha.');
                unset($_POST['submitMessage']);
            }
        }
        parent::preProcess();
    }

    public function postProcess()
    {
        if (Tools::isSubmit('submitMessage') && version_compare(_PS_VERSION_, '1.7', '<')) {
            Hook::exec('contactCaptchaValidate');
        }
        if (empty($this->errors)) {
            parent::postProcess();
        }
    }


    public function init()
    {
        if (version_compare(_PS_VERSION_, '1.5', '>=')) {
            $this->context->smarty->assign('HOOK_CONTACT_FORM_BOTTOM', Hook::exec('contactFormBottom'));
        }

        parent::init();
    }



    public function initContent()
    {
        parent::initContent();

        if (version_compare(_PS_VERSION_, '1.7.0', '<')&&Module::isInstalled('recaptcha')&&Configuration::get('CAPTCHA_OVERLOAD')==1) {
            //1.6
            if (version_compare(_PS_VERSION_, '1.6.0', '>=') === true) {
                $html = _PS_MODULE_DIR_ . 'recaptcha/views/templates/front/front-contact-form-1-6.tpl';
            } //1.5
            else {
                $html = _PS_MODULE_DIR_ . 'recaptcha/views/templates/front/front-contact-form-1-5.tpl';
            }
            $this->setTemplate($html);
        }
    }
}
