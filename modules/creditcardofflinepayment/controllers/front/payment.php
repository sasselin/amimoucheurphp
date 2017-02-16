<?php
/**
* Credit card offline payment
*
* NOTICE OF LICENSE
*
* This product is licensed for one customer to use on one installation (test stores and multishop included).
* Site developer has the right to modify this module to suit their needs, but can not redistribute the module in
* whole or in part. Any other use of this module constitues a violation of the user agreement.
*
* DISCLAIMER
*
* NO WARRANTIES OF DATA SAFETY OR MODULE SECURITY
* ARE EXPRESSED OR IMPLIED. USE THIS MODULE IN ACCORDANCE
* WITH YOUR MERCHANT AGREEMENT, KNOWING THAT VIOLATIONS OF
* PCI COMPLIANCY OR A DATA BREACH CAN COST THOUSANDS OF DOLLARS
* IN FINES AND DAMAGE A STORES REPUTATION. USE AT YOUR OWN RISK.
*
*  @author    idnovate.com <info@idnovate.com>
*  @copyright 2016 idnovate.com
*  @license   See above
*/

class CreditCardOfflinePaymentPaymentModuleFrontController extends ModuleFrontController
{
    public $ssl = true;

    public function setMedia()
    {
        parent::setMedia();
        $this->addJS(_PS_JS_DIR_.'validate.js');
    }

    public function initContent()
    {
        try {
            if (Configuration::get('CCOFFLINE_LEFT_COLUMN')) {
                $this->display_column_left = true;
            } else {
                $this->display_column_left = false;
            }

            if (Configuration::get('CCOFFLINE_RIGHT_COLUMN')) {
                $this->display_column_right = true;
            } else {
                $this->display_column_right = false;
            }

            parent::initContent();

            $cart = $this->context->cart;
            if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$this->module->active) {
                Tools::redirect('index.php?controller=order&step=1');
            }

            // Check that this payment option is still available in case the customer changed his address just before the end of the checkout process
            $authorized = false;

            foreach (Module::getPaymentModules() as $module) {
                if ($module['name'] == 'creditcardofflinepayment') {
                    $authorized = true;
                    break;
                }
            }

            if (!$authorized) {
                die($this->module->l('This payment method is not available.', 'payment'));
            }

            $customer = new Customer($cart->id_customer);
            if (!Validate::isLoadedObject($customer)) {
                Tools::redirect('index.php?controller=order&step=1');
            }

            if (!$this->module->checkCurrency($cart)) {
                Tools::redirect('index.php?controller=order');
            }

            $this->context->smarty->assign(array(
                'nbProducts'        => $cart->nbProducts(),
                'default_currency'  => new Currency(Configuration::get('PS_CURRENCY_DEFAULT')),
                'currency'          => new Currency((int)$this->context->cookie->id_currency),
                'id_currency'       => (int)$this->context->cookie->id_currency,
                'total'             => number_format($cart->getOrderTotal(true, 3), 2, '.', ''),
                'issuers'           => CreditCardOfflinePaymentBrands::getIssuers(),
                'requireIssuerName' => Configuration::get('CCOFFLINE_REQUIREISSUERNAME') == '1' ? true : false,
                'requiredIssuerName'=> Configuration::get('CCOFFLINE_REQUIREDISSUERNAME') == '1' ? true : false,
                'requireCedule'     => Configuration::get('CCOFFLINE_REQUIRECED') == '1' ? true : false,
                'requiredCedule'    => Configuration::get('CCOFFLINE_REQUIREDCED') == '1' ? true : false,
                'requireAddress'    => Configuration::get('CCOFFLINE_REQUIREADDRESS') == '1' ? true : false,
                'requiredAddress'   => Configuration::get('CCOFFLINE_REQUIREDADDRESS') == '1' ? true : false,
                'requireZipCode'    => Configuration::get('CCOFFLINE_REQUIREZIPCODE') == '1' ? true : false,
                'requiredZipCode'   => Configuration::get('CCOFFLINE_REQUIREDZIPCODE') == '1' ? true : false,
                'requireCity'       => Configuration::get('CCOFFLINE_REQUIRECITY') == '1' ? true : false,
                'requiredCity'      => Configuration::get('CCOFFLINE_REQUIREDCITY') == '1' ? true : false,
                'requireState'      => Configuration::get('CCOFFLINE_REQUIRESTATE') == '1' ? true : false,
                'requiredState'     => Configuration::get('CCOFFLINE_REQUIREDSTATE') == '1' ? true : false,
                'requireCountry'    => Configuration::get('CCOFFLINE_REQUIRECOUNTRY') == '1' ? true : false,
                'requiredCountry'   => Configuration::get('CCOFFLINE_REQUIREDCOUNTRY') == '1' ? true : false,
                'requireCardNumber' => Configuration::get('CCOFFLINE_REQUIRECARDNUMBER') == '1' ? true : false,
                'requiredCardNumber'=> Configuration::get('CCOFFLINE_REQUIREDCARDNUMBER') == '1' ? true : false,
                'requireIssuer'     => Configuration::get('CCOFFLINE_REQUIREISSUER') == '1' ? true : false,
                'requiredIssuer'    => Configuration::get('CCOFFLINE_REQUIREDISSUER') == '1' ? true : false,
                'requireExpiration' => Configuration::get('CCOFFLINE_REQUIREEXP') == '1' ? true : false,
                'requiredExpiration'=> Configuration::get('CCOFFLINE_REQUIREDEXP') == '1' ? true : false,
                'requireCVC'        => Configuration::get('CCOFFLINE_REQUIRECVC') == '1' ? true : false,
                'requiredCVC'       => Configuration::get('CCOFFLINE_REQUIREDCVC') == '1' ? true : false,
                'requirePin'        => Configuration::get('CCOFFLINE_REQUIREPIN') == '1' ? true : false,
                'requiredPin'       => Configuration::get('CCOFFLINE_REQUIREDPIN') == '1' ? true : false,
                'years'             => Configuration::get('CCOFFLINE_YEARS'),
                'skip_validation'   => Tools::getValue('_') ? 'true' : 'false',
                'cc_path'           => $this->module->getPathUri(),
                'mobile_device'     => $this->context->getMobileDevice(),
            ));

            $this->setTemplate('payment_execution.tpl');
        } catch (Exception $e) {
            error_log($e);
        }
    }
}
