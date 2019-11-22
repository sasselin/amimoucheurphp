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
class AuthController extends AuthControllerCore
{
    /*
    * module: recaptcha
    * date: 2019-05-02 21:33:27
    * version: 1.2.3
    */
    public function preProcess()
    {
        if (version_compare(_PS_VERSION_, '1.5', '<') &&
            (Tools::isSubmit('submitAccount') || Tools::isSubmit('submitGuestAccount')) &&
            Module::isInstalled('recaptcha') &&
            Configuration::get('CAPTCHA_ENABLE_ACCOUNT')) {
            require_once(_PS_ROOT_DIR_.'/modules/recaptcha/recaptcha.php');
            $recaptcha = new Recaptcha();
            $recaptcha->validateCaptcha();
        }
        parent::preProcess();
    }
    /**
     * Fix for the missing hook in 1.7.0.*
     *
     * @access public
     * @return void
     */
    /*
    * module: recaptcha
    * date: 2019-05-02 21:33:27
    * version: 1.2.3
    */
    public function postProcess()
    {
        if (version_compare(_PS_VERSION_, '1.7', '>=') &&
            version_compare(_PS_VERSION_, '1.7.1', '<') &&
            Tools::isSubmit('submitCreate') &&
            Module::isInstalled('recaptcha') &&
            Module::isEnabled('recaptcha') &&
            Configuration::get('CAPTCHA_ENABLE_ACCOUNT')) {
            require_once(_PS_ROOT_DIR_.'/modules/recaptcha/recaptcha.php');
            $recaptcha = new Recaptcha();
            $recaptcha->validateCaptcha();
            if (!empty($this->errors)) {
                unset($_POST['submitCreate']);
            }
        }
        parent::postProcess();
    }
	
	protected function sendConfirmationMail(Customer $customer)
    {
        if (!Configuration::get('PS_CUSTOMER_CREATION_EMAIL')) {
            return true;
        }

        return Mail::Send(
            $this->context->language->id,
            'account',
            Mail::l('Welcome!'),
            array(
                '{firstname}' => $customer->firstname,
                '{lastname}' => $customer->lastname,
                '{email}' => $customer->email,
                '{passwd}' => Tools::getValue('passwd')),
            $customer->email,
            $customer->firstname.' '.$customer->lastname,
			null, null, null, null,
			_PS_MAIL_DIR_, false, null, null, null, true
        );
    }
}
