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

include_once(_PS_MODULE_DIR_.'creditcardofflinepayment/classes/CreditCardOfflinePaymentBrands.php');
include_once(_PS_MODULE_DIR_.'creditcardofflinepayment/classes/CreditCardOfflinePaymentOrder.php');
include_once(_PS_MODULE_DIR_.'creditcardofflinepayment/classes/CreditCardOfflinePaymentStates.php');


if (!defined('_PS_VERSION_')) {
    exit;
}

class CreditCardOfflinePayment extends PaymentModule
{
    private $errors = array();
    private $success;
    public $order_states;

    public function __construct()
    {
        $this->name = 'creditcardofflinepayment';
        $this->tab = 'payments_gateways';
        $this->version = '2.10.9';
        $this->author = 'idnovate.com';
        $this->module_key = '9d9a3e22e4399d5d9caa8abd3cf256d6';

        $this->currencies = true;
        $this->currencies_mode = 'checkbox';

        parent::__construct();

        $this->displayName = $this->l('Card payment');
        $this->description = $this->l('This module lets you make charges to your customer by a physical POS.');
        $this->confirmUninstall = $this->l('Are you sure that you want to delete the module and the related data?');

        /* Backward compatibility */
        if (version_compare(_PS_VERSION_, '1.5', '<')) {
            require(_PS_MODULE_DIR_.$this->name.'/backward_compatibility/backward.php');
        }

        if (!count(Currency::checkPaymentCurrencies($this->id))) {
            $this->warning = $this->l('Module not enabled');
        }

        if (Configuration::get('CCOFFLINE_WORKING_MODE') == '1' && (!Validate::isEmail(Configuration::get('CCOFFLINE_ADMIN_MAIL')))) {
            $this->warning = $this->l('You have to set your mail address to receive the card information.');
        }

        if (version_compare(_PS_VERSION_, '1.6', '>=')) {
            if (Configuration::get('PS_DISABLE_NON_NATIVE_MODULE')) {
                $this->warning = $this->l('You have to enable non PrestaShop modules at ADVANCED PARAMETERS - PERFORMANCE');
            }
        }

    }

    public function install()
    {
        if (!parent::install()
            || !$this->registerHook('payment')
            || (version_compare(_PS_VERSION_, '1.5', '>=') && !$this->registerHook('paymentEU'))
            || !$this->registerHook('paymentReturn')
            || !$this->registerHook('adminOrder')
            || !$this->registerHook('header')
            || !$this->registerHook('updateOrderStatus')) {
            return false;
        }

        //Inicialización de las variables
        Configuration::updateValue('CCOFFLINE_SHOW_FORM', 0);
        Configuration::updateValue('CCOFFLINE_MINIMUM_AMOUNT', 0);
        Configuration::updateValue('CCOFFLINE_MINIMUM_AMOUNT_CUR', Configuration::get('PS_CURRENCY_DEFAULT'));
        Configuration::updateValue('CCOFFLINE_REQUIREISSUERNAME', 1);
        Configuration::updateValue('CCOFFLINE_REQUIREDISSUERNAME', 1);
        Configuration::updateValue('CCOFFLINE_REQUIRECED', 0);
        Configuration::updateValue('CCOFFLINE_REQUIREDCED', 0);
        Configuration::updateValue('CCOFFLINE_REQUIREADDRESS', 0);
        Configuration::updateValue('CCOFFLINE_REQUIREDADDRESS', 0);
        Configuration::updateValue('CCOFFLINE_REQUIREZIPCODE', 0);
        Configuration::updateValue('CCOFFLINE_REQUIREDZIPCODE', 0);
        Configuration::updateValue('CCOFFLINE_REQUIRECITY', 0);
        Configuration::updateValue('CCOFFLINE_REQUIREDCITY', 0);
        Configuration::updateValue('CCOFFLINE_REQUIRESTATE', 0);
        Configuration::updateValue('CCOFFLINE_REQUIREDSTATE', 0);
        Configuration::updateValue('CCOFFLINE_REQUIRECOUNTRY', 0);
        Configuration::updateValue('CCOFFLINE_REQUIREDCOUNTRY', 0);
        Configuration::updateValue('CCOFFLINE_REQUIRECARDNUMBER', 1);
        Configuration::updateValue('CCOFFLINE_REQUIREDCARDNUMBER', 1);
        Configuration::updateValue('CCOFFLINE_REQUIRECVC', 1);
        Configuration::updateValue('CCOFFLINE_REQUIREDCVC', 1);
        Configuration::updateValue('CCOFFLINE_REQUIREISSUER', 1);
        Configuration::updateValue('CCOFFLINE_REQUIREDISSUER', 1);
        Configuration::updateValue('CCOFFLINE_REQUIREEXP', 1);
        Configuration::updateValue('CCOFFLINE_REQUIREDEXP', 1);
        Configuration::updateValue('CCOFFLINE_REQUIREPIN', 0);
        Configuration::updateValue('CCOFFLINE_REQUIREDPIN', 0);
        Configuration::updateValue('CCOFFLINE_YEARS', 10);
        Configuration::updateValue('CCOFFLINE_DEBUG_MODE', 0);
        Configuration::updateValue('CCOFFLINE_LEFT_COLUMN', 0);
        Configuration::updateValue('CCOFFLINE_RIGHT_COLUMN', 0);
        Configuration::updateValue('CCOFFLINE_WORKING_MODE', 1);
        Configuration::updateValue('CCOFFLINE_ADMIN_MAIL_LANG', 1);

        //Instaladores de las clases
        $this->creditCardOfflinePaymentStatesSetup();
        CreditCardOfflinePaymentBrands::setup();
        CreditCardOfflinePaymentOrder::setup();

        return true;
    }

    public function uninstall()
    {
        CreditCardOfflinePaymentBrands::remove();
        CreditCardOfflinePaymentOrder::remove();

        Configuration::deleteByName('CCOFFLINE_SHOW_FORM');
        Configuration::deleteByName('CCOFFLINE_MINIMUM_AMOUNT');
        Configuration::deleteByName('CCOFFLINE_MINIMUM_AMOUNT_CUR');
        Configuration::deleteByName('CCOFFLINE_REQUIREISSUERNAME');
        Configuration::deleteByName('CCOFFLINE_REQUIREDISSUERNAME');
        Configuration::deleteByName('CCOFFLINE_REQUIREADDRESS');
        Configuration::deleteByName('CCOFFLINE_REQUIREDADDRESS');
        Configuration::deleteByName('CCOFFLINE_REQUIREZIPCODE');
        Configuration::deleteByName('CCOFFLINE_REQUIREDZIPCODE');
        Configuration::deleteByName('CCOFFLINE_REQUIRECARDNUMBER');
        Configuration::deleteByName('CCOFFLINE_REQUIREDCARDNUMBER');
        Configuration::deleteByName('CCOFFLINE_REQUIREISSUER');
        Configuration::deleteByName('CCOFFLINE_REQUIREEXP');
        Configuration::deleteByName('CCOFFLINE_REQUIRECVC');
        Configuration::deleteByName('CCOFFLINE_REQUIRECITY');
        Configuration::deleteByName('CCOFFLINE_REQUIREDCITY');
        Configuration::deleteByName('CCOFFLINE_REQUIRESTATE');
        Configuration::deleteByName('CCOFFLINE_REQUIREDSTATE');
        Configuration::deleteByName('CCOFFLINE_REQUIRECOUNTRY');
        Configuration::deleteByName('CCOFFLINE_REQUIREDCOUNTRY');
        Configuration::deleteByName('CCOFFLINE_REQUIRECED');
        Configuration::deleteByName('CCOFFLINE_DATA_ISSUERS');
        Configuration::deleteByName('CCOFFLINE_ADMIN_MAIL');
        Configuration::deleteByName('CCOFFLINE_REQUIREDCVC');
        Configuration::deleteByName('CCOFFLINE_REQUIREDISSUER');
        Configuration::deleteByName('CCOFFLINE_REQUIREDEXP');
        Configuration::deleteByName('CCOFFLINE_REQUIREDCED');
        Configuration::deleteByName('CCOFFLINE_WORKING_MODE');
        Configuration::deleteByName('CCOFFLINE_ADMIN_MAIL_LANG');
        Configuration::deleteByName('CCOFFLINE_DATA_OS_INITIAL');
        Configuration::deleteByName('CCOFFLINE_DATA_OS_REM');
        Configuration::deleteByName('CCOFFLINE_REQUIREPIN');
        Configuration::deleteByName('CCOFFLINE_REQUIREDPIN');
        Configuration::deleteByName('CCOFFLINE_YEARS');
        Configuration::deleteByName('CCOFFLINE_DEBUG_MODE');
        Configuration::deleteByName('CCOFFLINE_LEFT_COLUMN');
        Configuration::deleteByName('CCOFFLINE_RIGHT_COLUMN');

        return parent::uninstall();
    }

    private function creditCardOfflinePaymentStatesSetup()
    {
        if (!Configuration::get('CCOFFLINE_DATA_OS_INITIAL') > 0) {
            $os = new OrderState();
            $os->name = array_fill(0, 10, $this->l('Card - Payment pending'));
            $os->send_email = false;
            $os->invoice = 0;
            $os->color = '#FFFFAA';
            $os->unremovable = false;
            $os->logable = 0;
            $os->add();

            Configuration::updateValue('CCOFFLINE_DATA_OS_INITIAL', $os->id);

            copy(_PS_MODULE_DIR_.$this->name.'/logo.gif', _PS_IMG_DIR_.'os/'.$os->id.'.gif');
        }
    }

    /*
    Validación del Backoffice
    */
    private function postValidation()
    {
        $errors = array();

        if (Tools::getValue('minimum_amount') || Tools::getValue('moneda')) {
            if (!Validate::isPrice(Tools::getValue('minimum_amount'))) {
                $errors[] = $this->l('Minimum amount is not valid.');
            } elseif (!Validate::isInt(Tools::getValue('moneda'))) {
                $errors[] = $this->l('Currency is not valid.');
            } else {
                Configuration::updateValue('CCOFFLINE_MINIMUM_AMOUNT', Tools::getValue('minimum_amount'));
                Configuration::updateValue('CCOFFLINE_MINIMUM_AMOUNT_CUR', Tools::getValue('moneda'));
            }
        } else {
            $this->_errors[] = $this->l('You have to set a minimum order amount.');
        }

        //Actualizar tarjetas habilitadas
        $authorized_issuers = Tools::getValue('issuers');
        if (is_array($authorized_issuers)) {
            CreditCardOfflinePaymentBrands::setAuthorizedIssuers($authorized_issuers);
        } else {
            $errors[] = $this->l('You have to set one card issuer at least.');
        }

        if (Tools::getValue('working_mode') == 1) {
            if (Tools::getValue('admin_mail') == '') {
                $errors[] = $this->l('The mail address can not be empty.');
            } elseif (!Validate::isEmail(Tools::getValue('admin_mail'))) {
                $errors[] = $this->l('The mail address is not valid.');
            } else {
                Configuration::updateValue('CCOFFLINE_ADMIN_MAIL', Tools::getValue('admin_mail'));
            }
        } else {
            Configuration::updateValue('CCOFFLINE_ADMIN_MAIL', Tools::getValue('admin_mail'));
        }

        //Actualizar resto de configuración
        Configuration::updateValue('CCOFFLINE_SHOW_FORM', Tools::getValue('show_form') == 1 ? '1' : '0');
        Configuration::updateValue('CCOFFLINE_REQUIREISSUERNAME', Tools::getValue('requireIssuerName') == 1 ? '1' : '0');
        Configuration::updateValue('CCOFFLINE_REQUIREDISSUERNAME', Tools::getValue('requiredIssuerName') == 'on' ? '1' : '0');
        Configuration::updateValue('CCOFFLINE_REQUIRECED', Tools::getValue('requireCedule') == 1 ? '1' : '0');
        Configuration::updateValue('CCOFFLINE_REQUIREDCED', Tools::getValue('requiredCedule') == 'on' ? '1' : '0');
        Configuration::updateValue('CCOFFLINE_REQUIREADDRESS', Tools::getValue('requireAddress') == 1 ? '1' : '0');
        Configuration::updateValue('CCOFFLINE_REQUIREDADDRESS', Tools::getValue('requiredAddress') == 'on' ? '1' : '0');
        Configuration::updateValue('CCOFFLINE_REQUIREZIPCODE', Tools::getValue('requireZipCode') == 1 ? '1' : '0');
        Configuration::updateValue('CCOFFLINE_REQUIREDZIPCODE', Tools::getValue('requiredZipCode') == 'on' ? '1' : '0');
        Configuration::updateValue('CCOFFLINE_REQUIRECITY', Tools::getValue('requireCity') == 1 ? '1' : '0');
        Configuration::updateValue('CCOFFLINE_REQUIREDCITY', Tools::getValue('requiredCity') == 'on' ? '1' : '0');
        Configuration::updateValue('CCOFFLINE_REQUIRESTATE', Tools::getValue('requireState') == 1 ? '1' : '0');
        Configuration::updateValue('CCOFFLINE_REQUIREDSTATE', Tools::getValue('requiredState') == 'on' ? '1' : '0');
        Configuration::updateValue('CCOFFLINE_REQUIRECOUNTRY', Tools::getValue('requireCountry') == 1 ? '1' : '0');
        Configuration::updateValue('CCOFFLINE_REQUIREDCOUNTRY', Tools::getValue('requiredCountry') == 'on' ? '1' : '0');
        Configuration::updateValue('CCOFFLINE_REQUIRECARDNUMBER', Tools::getValue('requireCardNumber') == 1 ? '1' : '0');
        Configuration::updateValue('CCOFFLINE_REQUIREDCARDNUMBER', Tools::getValue('requiredCardNumber') == 'on' ? '1' : '0');
        Configuration::updateValue('CCOFFLINE_REQUIREISSUER', Tools::getValue('requireIssuer') == 1 ? '1' : '0');
        Configuration::updateValue('CCOFFLINE_REQUIREDISSUER', Tools::getValue('requiredIssuer') == 'on' ? '1' : '0');
        Configuration::updateValue('CCOFFLINE_REQUIRECVC', Tools::getValue('requireCVC') == 1 ? '1' : '0');
        Configuration::updateValue('CCOFFLINE_REQUIREDCVC', Tools::getValue('requiredCVC') == 'on' ? '1' : '0');
        Configuration::updateValue('CCOFFLINE_REQUIREEXP', Tools::getValue('requireExpiration') == 1 ? '1' : '0');
        Configuration::updateValue('CCOFFLINE_REQUIREDEXP', Tools::getValue('requiredExpiration') == 'on' ? '1' : '0');
        Configuration::updateValue('CCOFFLINE_REQUIREPIN', Tools::getValue('requirePin') == 1 ? '1' : '0');
        Configuration::updateValue('CCOFFLINE_REQUIREDPIN', Tools::getValue('requiredPin') == 'on' ? '1' : '0');
        Configuration::updateValue('CCOFFLINE_YEARS', Tools::getValue('years'));
        Configuration::updateValue('CCOFFLINE_DEBUG_MODE', Tools::getValue('debugMode') == 1 ? '1' : '0');
        Configuration::updateValue('CCOFFLINE_LEFT_COLUMN', Tools::getValue('leftColumn') == 1 ? '1' : '0');
        Configuration::updateValue('CCOFFLINE_RIGHT_COLUMN', Tools::getValue('rightColumn') == 1 ? '1' : '0');
        Configuration::updateValue('CCOFFLINE_WORKING_MODE', Tools::getValue('working_mode'));
        Configuration::updateValue('CCOFFLINE_DATA_OS_INITIAL', Tools::getValue('id_os_initial'));
        Configuration::updateValue('CCOFFLINE_DATA_OS_REM', implode(',', (array)Tools::getValue('id_os_remove')));

        if (version_compare(_PS_VERSION_, '1.5', '<')) {
            Configuration::updateValue('CCOFFLINE_ADMIN_MAIL_LANG', $this->context->cookie->id_lang);
        } else {
            Configuration::updateValue('CCOFFLINE_ADMIN_MAIL_LANG', $this->context->language->id);
        }

        if (count($errors) < 1) {
            return $this->success = true;
        }

        return $this->errors = $errors;
    }

    /*
    Relleno de campos del BackOffice
    */
    public function getContent()
    {
        if (Tools::isSubmit('btnSubmit')) {
            $this->postValidation();
        }

        if (!count(Currency::checkPaymentCurrencies($this->id))) {
            $this->errors[] = $this->l('Module not enabled');
        }

        if (Configuration::get('CCOFFLINE_WORKING_MODE') == '1' && (!Validate::isEmail(Configuration::get('CCOFFLINE_ADMIN_MAIL')))) {
            $this->errors[] = $this->l('You have to set your mail address to receive the card information.');
        }

        if (version_compare(_PS_VERSION_, '1.6', '>=')) {

            if (Configuration::get('PS_DISABLE_NON_NATIVE_MODULE')) {
                $this->errors[] = $this->l('You have to enable non PrestaShop modules at ADVANCED PARAMETERS - PERFORMANCE');
            }
        }

        $this->context->smarty->assign(array(
            'displayName'       => $this->displayName,
            'issuers'           => CreditCardOfflinePaymentBrands::getIssuers(),
            'show_form'         => Configuration::get('CCOFFLINE_SHOW_FORM'),
            'admin_mail'        => Configuration::get('CCOFFLINE_ADMIN_MAIL'),
            'currencies'        => Currency::getCurrencies(),
            'minimum_amount'    => Configuration::get('CCOFFLINE_MINIMUM_AMOUNT'),
            'moneda'            => Configuration::get('CCOFFLINE_MINIMUM_AMOUNT_CUR'),
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
            'debugMode'         => Configuration::get('CCOFFLINE_DEBUG_MODE'),
            'leftColumn'        => Configuration::get('CCOFFLINE_LEFT_COLUMN'),
            'rightColumn'       => Configuration::get('CCOFFLINE_RIGHT_COLUMN'),
            'working_mode'      => Configuration::get('CCOFFLINE_WORKING_MODE'),
            'errors'            => $this->errors,
            'success'           => $this->success,
            'states'            => CreditCardOfflinePaymentStates::getOrderStates(),
            'id_os_initial'     => Configuration::get('CCOFFLINE_DATA_OS_INITIAL'),
            'id_os_remove'      => explode(',', Configuration::get('CCOFFLINE_DATA_OS_REM')),
            'cc_path'           => _MODULE_DIR_.$this->name.'/',
        ));

        if (version_compare(_PS_VERSION_, '1.5', '<')) {
            return $this->display(__FILE__, 'views/templates/hook/admin_form.tpl');
        } else {
            return $this->display(__FILE__, 'admin_form.tpl');
        }
    }

    /*  execPayment($cart)
     *  Se ejecUta cuando el usuario clica "Pagar con tarjeta de crédito"
    */
    public function execPayment($cart)
    {
        try {
            if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$this->active) {
                Tools::redirect('index.php?controller=order&step=1');
            }

            if (version_compare(_PS_VERSION_, '1.4.4', '>=')) {
                // Check that this payment option is still available in case the customer changed his address just before the end of the checkout process
                $authorized = false;

                foreach (Module::getPaymentModules() as $module) {
                    if ($module['name'] == 'creditcardofflinepayment') {
                        $authorized = true;
                        break;
                    }
                }

                if (!$authorized) {
                    die($this->l('This payment method is not available.', 'validation'));
                }
            }

            $customer = new Customer($cart->id_customer);
            if (!Validate::isLoadedObject($customer)) {
                Tools::redirect('index.php?controller=order&step=1');
            }

            if (!$this->checkCurrency($cart)) {
                Tools::redirect('index.php?controller=order');
            } else {
                $errors = array();

                if (Tools::isSubmit('paymentSubmit') && Tools::getValue('skip_validation') == 'false') {
                    $card = Tools::getValue('card');

                    $debug = false;
                    if (Configuration::get('CCOFFLINE_DEBUG_MODE')) {
                        $debug = true;
                        $logger = new FileLogger(0);
                        $logger->setFilename(_PS_ROOT_DIR_.'/log/ccoffline.log');
                    }

                    if ($debug) {
                        $logger->logDebug('1 - Validating card');
                    }

                    //Validate variables
                    //Name holder
                    if (Configuration::get('CCOFFLINE_REQUIREISSUERNAME') && Configuration::get('CCOFFLINE_REQUIREDISSUERNAME') && $card['name'] == '') {
                        $errors[] = $this->l('You must introduce the card holder name.', 'validation');
                    }

                    if (Configuration::get('CCOFFLINE_REQUIREISSUERNAME') && $card['name'] != '' && !Validate::isName($card['name'])) {
                        $errors[] = $this->l('The name introduced is not valid.', 'validation');
                    }

                    //If variable not required, initialize to blank
                    if (!Configuration::get('CCOFFLINE_REQUIREISSUERNAME')) {
                        $card['name'] = '';
                    }

                    //ID/Passport
                    if (Configuration::get('CCOFFLINE_REQUIRECED') && Configuration::get('CCOFFLINE_REQUIREDCED') && $card['cedula'] == '') {
                        $errors[] = $this->l('You must introduce your ID Card/Passport.', 'validation');
                    }

                    //If variable not required, initialize to blank
                    if (!Configuration::get('CCOFFLINE_REQUIRECED')) {
                        $card['cedula'] = '';
                    }

                    //Address
                    if (Configuration::get('CCOFFLINE_REQUIREADDRESS') && Configuration::get('CCOFFLINE_REQUIREDADDRESS') && $card['address'] == '') {
                        $errors[] = $this->l('You must introduce your address.', 'validation');
                    }

                    //If variable not required, initialize to blank
                    if (!Configuration::get('CCOFFLINE_REQUIREADDRESS')) {
                        $card['address'] = '';
                    }

                    //Zip code
                    if (Configuration::get('CCOFFLINE_REQUIREZIPCODE') && Configuration::get('CCOFFLINE_REQUIREDZIPCODE') && $card['zipcode'] == '') {
                        $errors[] = $this->l('You must introduce your zip code.', 'validation');
                    }

                    //If variable not required, initialize to blank
                    if (!Configuration::get('CCOFFLINE_REQUIREZIPCODE')) {
                        $card['zipcode'] = '';
                    }

                    //City
                    if (Configuration::get('CCOFFLINE_REQUIRECITY') && Configuration::get('CCOFFLINE_REQUIREDCITY') && $card['city'] == '') {
                        $errors[] = $this->l('You must introduce your city.', 'validation');
                    }

                    //If variable not required, initialize to blank
                    if (!Configuration::get('CCOFFLINE_REQUIRECITY')) {
                        $card['city'] = '';
                    }

                    //State
                    if (Configuration::get('CCOFFLINE_REQUIRESTATE') && Configuration::get('CCOFFLINE_REQUIREDSTATE') && $card['state'] == '') {
                        $errors[] = $this->l('You must introduce your state.', 'validation');
                    }

                    //If variable not required, initialize to blank
                    if (!Configuration::get('CCOFFLINE_REQUIRESTATE')) {
                        $card['state'] = '';
                    }

                    //Country
                    if (Configuration::get('CCOFFLINE_REQUIRECOUNTRY') && Configuration::get('CCOFFLINE_REQUIREDCOUNTRY') && $card['country'] == '') {
                        $errors[] = $this->l('You must introduce your country.', 'validation');
                    }

                    //If variable not required, initialize to blank
                    if (!Configuration::get('CCOFFLINE_REQUIRECOUNTRY')) {
                        $card['country'] = '';
                    }

                    //Card number
                    if (Configuration::get('CCOFFLINE_REQUIRECARDNUMBER') && Configuration::get('CCOFFLINE_REQUIREDCARDNUMBER') && $card['number'] == '') {
                        $errors[] = $this->l('You must introduce the credit card number.', 'validation');
                    }

                    if (Configuration::get('CCOFFLINE_REQUIRECARDNUMBER') && $card['number'] != '' && Configuration::get('CCOFFLINE_REQUIREISSUER') && !$this->validateCard($card['number'], $card['issuer'])) {
                        $errors[] = $this->l('The credit card number entered is not valid.', 'validation');
                    }

                    //If variable not required, initialize to blank
                    if (!Configuration::get('CCOFFLINE_REQUIRECARDNUMBER')) {
                        $card['number'] = '';
                    }

                    //CVC
                    if (Configuration::get('CCOFFLINE_REQUIRECVC') && Configuration::get('CCOFFLINE_REQUIREDCVC') && $card['cvc'] == '') {
                        $errors[] = $this->l('You must introduce the CVC card number.', 'validation');
                    }

                    //If variable not required, initialize to blank
                    if (Configuration::get('CCOFFLINE_REQUIRECVC') && $card['cvc'] != '' && (!is_numeric($card['cvc']))) {
                        $errors[] = $this->l('The CVC card number entered is not valid.', 'validation');
                    }

                    //If variable not required, initialize to blank
                    if (!Configuration::get('CCOFFLINE_REQUIRECVC')) {
                        $card['cvc'] = '';
                    }

                    //Card issuer
                    if (Configuration::get('CCOFFLINE_REQUIREISSUER') && Configuration::get('CCOFFLINE_REQUIREDISSUER') && $card['issuer'] == '') {
                        $errors[] = $this->l('You must choose your card issuer.', 'validation');
                    }

                    //If variable not required, initialize to blank
                    if (!Configuration::get('CCOFFLINE_REQUIREISSUER')) {
                        $card['issuer'] = '';
                    }

                    //Expiry date
                    if (Configuration::get('CCOFFLINE_REQUIREEXP') && Configuration::get('CCOFFLINE_REQUIREDEXP')) {
                        if ($card['mes_caducidad'] == '') {
                            $errors[] = $this->l('You must introduce the expiry month of the credit card.', 'validation');
                        }

                        if ($card['ano_caducidad'] == '') {
                            $errors[] = $this->l('You must introduce the expiry year of the credit card.', 'validation');
                        }

                        // Check that the date is valid
                        if ($card['mes_caducidad'] != '' && $card['ano_caducidad'] != '' && $card['ano_caducidad'] == date('Y') && $card['mes_caducidad'] < date('m')) {
                            $errors[] = $this->l('The expiry date that you introduced can not be prior to actual date.', 'validation');
                        }
                    }

                    //If variable not required, initialize to blank
                    if (!Configuration::get('CCOFFLINE_REQUIREEXP')) {
                        $card['mes_caducidad'] = '';
                        $card['ano_caducidad'] = '';
                    }

                    //Card PIN code
                    if (Configuration::get('CCOFFLINE_REQUIREPIN') && Configuration::get('CCOFFLINE_REQUIREDPIN') && $card['pin'] == '') {
                        $errors[] = $this->l('You must introduce the card pin code.', 'validation');
                    }

                    //If variable not required, initialize to blank
                    if (!Configuration::get('CCOFFLINE_REQUIREPIN') && !isset($card['pin'])) {
                        $card['pin'] = '';
                    }

                    if ($debug) {
                        $logger->logDebug('2 - Card validated');
                    }

                    //Si todos los campos son válidos
                    if (!count($errors)) {
                        $id_currency = (int)Tools::getValue('id_currency');
                        $currency = Currency::getCurrency($id_currency);

                        //lets put a name for the issuer instead of an object id
                        /*if ($card['issuer'])
                            $card['issuer'] = CreditCardOfflinePaymentBrands::getNameById($card['issuer']);
                        */
                        $total = (float)number_format($cart->getOrderTotal(true, 3), 2, '.', '');

                        if ($debug) {
                            $logger->logDebug('3 - Validating order');
                        }

                        //and place the order
                        $this->validateOrder($cart->id, Configuration::get('CCOFFLINE_DATA_OS_INITIAL'), $total, $this->displayName, null, null, $id_currency, false, $cart->secure_key);

                        if ($debug) {
                            $logger->logDebug('4 - Order validated');
                        }

                        $order = new Order((int)$this->currentOrder);

                        if (Configuration::get('CCOFFLINE_WORKING_MODE') == '2') {
                            if ($debug) {
                                $logger->logDebug('5 - Inserting order '.print_r($card, true));
                            }
                            //Store whole number at database, don't send mail.
                            $result = CreditCardOfflinePaymentOrder::insertOrder((int)$order->id, $this->encryptDecrypt($card['cvc'], 'encrypt'), $card['issuer'], $this->encryptDecrypt($card['number'], 'encrypt'), $card['name'], $card['cedula'], $card['address'], $card['zipcode'], $card['mes_caducidad'], $card['ano_caducidad'], $card['pin'], $total, $currency['sign'], $card['city'], $card['state'], $card['country']);

                            if ($debug) {
                                $logger->logDebug('6 - Order '.$order->id.' inserted '.$result);
                            }
                        } else {
                            //Store half number at database and send the other half by mail
                            $length = ceil(Tools::strlen($card['number']) / 2);

                            if ($debug) {
                                $logger->logDebug('7 - Inserting order '.print_r($card, true));
                            }

                            $result = CreditCardOfflinePaymentOrder::insertOrder((int)$order->id, $this->encryptDecrypt('XXX', 'encrypt'), $card['issuer'], $this->encryptDecrypt(Tools::substr($card['number'], 0, $length).str_pad('', Tools::strlen($card['number']) - $length, 'x'), 'encrypt'), $card['name'], $card['cedula'], $card['address'], $card['zipcode'], $card['mes_caducidad'], $card['ano_caducidad'], null, $total, $currency['sign'], $card['city'], $card['state'], $card['country']);

                            if ($debug) {
                                $logger->logDebug('8 - Order '.$order->id.' inserted '.$result);
                            }

                            //Send the email to the seller. Set the variables for the template
                            $template_vars = array();
                            $template_vars['{id_order}'] = $order->id;
                            $template_vars['{nombre_titular}'] = $card['name'];
                            $template_vars['{cedula}'] = $card['cedula'];
                            $template_vars['{direccion}'] = $card['address'];
                            $template_vars['{cp}'] = $card['zipcode'];
                            $template_vars['{importe}'] = $total;
                            $template_vars['{moneda}'] = $currency['sign'];
                            $template_vars['{pan}'] = str_pad('', $length, 'x').Tools::substr($card['number'], $length, (Tools::strlen($card['number'] - $length)));
                            $template_vars['{cvc}'] = $card['cvc'];
                            $template_vars['{mes_caducidad}'] = $card['mes_caducidad'];
                            $template_vars['{ano_caducidad}'] = $card['ano_caducidad'];
                            $template_vars['{pin}'] = $card['pin'];
                            $template_vars['{city}'] = $card['city'];
                            $template_vars['{state}'] = $card['state'];
                            $template_vars['{country}'] = $card['country'];

                            $id_lang = (int)Configuration::get('PS_LANG_DEFAULT'); //Language template
                            $template_name = 'mail'; //Template file name
                            $title = $this->l('Order', 'validation').' #'.$order->id.' '.$this->l('paid with credit card', 'validation'); //Mail subject with translation
                            $mail_dir = dirname(__FILE__).'/mails/'; //Directory with message templates
                            $to_mail = Configuration::get('CCOFFLINE_ADMIN_MAIL'); //To mail address

                            if ($debug) {
                                $logger->logDebug('8 - Sending mail for order '.$order->id);
                            }

                            Mail::Send($id_lang, $template_name, $title, $template_vars, $to_mail, '', null, null, null, null, $mail_dir);

                            if ($debug) {
                                $logger->logDebug('9 - Mail sent '.$order->id);
                            }
                        }

                        // Once complete, redirect to order-confirmation.php
                        Tools::redirectLink(__PS_BASE_URI__.'order-confirmation.php?id_cart='.(int)$cart->id.'&id_module='.(int)$this->id.'&id_order='.(int)$this->currentOrder.'&key='.$order->secure_key);
                    } else {
                        $this->context->smarty->assign('card', $card);
                    }
                }

                $this->context->smarty->assign(array(
                    'nbProducts'        => $cart->nbProducts(),
                    'default_currency'  => new Currency(Configuration::get('PS_CURRENCY_DEFAULT')),
                    'currency'          => new Currency((int)$this->context->cookie->id_currency),
                    'id_currency'       => (int)$this->context->cookie->id_currency,
                    'total'             => number_format($cart->getOrderTotal(true, 3), 2, '.', ''),
                    'errores'           => $errors,
                    'issuers'           => CreditCardOfflinePaymentBrands::getIssuers(),
                    'adminMail'         => Configuration::get('CCOFFLINE_ADMIN_MAIL'),
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
                    'skip_validation'   => 'false',
                    'cc_path'           => _MODULE_DIR_.$this->name.'/',
                    'mobile_device'     => $this->context->getMobileDevice(),
                ));

                if (version_compare(_PS_VERSION_, '1.5', '<')) {
                    return $this->display(__FILE__, 'views/templates/front/payment_execution.tpl');
                } elseif (version_compare(_PS_VERSION_, '1.6', '<')) {
                    return $this->display(__FILE__, 'payment_execution.tpl');
                }
            }
        } catch (Exception $e) {
            error_log($e);
        }
    }

    /** hookPayment($params)
    *   Mostrar plantilla para pagar con el módulo
    */
    public function hookPayment($params)
    {
        //It's only shown if:
        // - Card information is stored at database
        // - Card information is sent by mail and mail is defined
        // - Order amount is equal or higher than defined
        $currency_to = new Currency(Currency::getIdByIsoCodeNum(Configuration::get('CCOFFLINE_MINIMUM_AMOUNT_CUR')));
        $currency_from = new Currency((int)$params['cart']->id_currency);

        if (version_compare(_PS_VERSION_, '1.5', '<')) {
            $order_amount = $this->convertPriceFull($params['cart']->getOrderTotal(true, Cart::BOTH), $currency_from, $currency_to);
        } else {
            $order_amount = Tools::convertPriceFull($params['cart']->getOrderTotal(true, Cart::BOTH), $currency_from, $currency_to);
        }

        if (Configuration::get('CCOFFLINE_WORKING_MODE') == '2'
            || (Configuration::get('CCOFFLINE_WORKING_MODE') == '1' && (Configuration::get('CCOFFLINE_ADMIN_MAIL') <> ''))
            || $order_amount >= (Configuration::get('CCOFFLINE_MINIMUM_AMOUNT'))) {
            $this->context->smarty->assign(array(
                'cookie_currency'       => $this->context->cookie->id_currency,
                'currency_authorized'   => $this->checkCurrency($params['cart']),
                'cc_path'               => _MODULE_DIR_.$this->name.'/',
                'show_form'             => false,
            ));

            if (Configuration::get('CCOFFLINE_SHOW_FORM')) {
                $cart = $this->context->cart;
                $this->context->smarty->assign(array(
                    'nbProducts'        => $cart->nbProducts(),
                    'default_currency'  => new Currency(Configuration::get('PS_CURRENCY_DEFAULT')),
                    'currency'          => new Currency((int)$this->context->cookie->id_currency),
                    'id_currency'       => (int)$this->context->cookie->id_currency,
                    'total'             => number_format($cart->getOrderTotal(true, 3), 2, '.', ''),
                    'issuers'           => CreditCardOfflinePaymentBrands::getIssuers(),
                    'errores'           => null,
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
                    'cc_path'           => _MODULE_DIR_.$this->name.'/',
                    'mobile_device'     => $this->context->getMobileDevice(),
                    'show_form'         => true,
                ));
            }

            if (version_compare(_PS_VERSION_, '1.5', '<')) {
                return $this->display(__FILE__, 'views/templates/hook/payment.tpl');
            } elseif (version_compare(_PS_VERSION_, '1.6', '<')) {
                return $this->display(__FILE__, 'payment.tpl');
            } else {
                return $this->display(__FILE__, 'payment_1.6.tpl');
            }
        }
    }

    public function hookDisplayPaymentEU($params)
    {
        return array(
            'cta_text' => $this->l('Pay with credit card'),
            'logo' => Media::getMediaPath(_PS_MODULE_DIR_.$this->name.'/views/img/creditcardofflinepayment.jpg'),
            'action' => $this->context->link->getModuleLink($this->name, 'payment', array(), true)
        );
    }

    /** hookAdminOrder($params)
    *   Información del pedido en el backoffice. Sólo se muestra si el pedido se ha realizado con este módulo
    */
    public function hookAdminOrder($params)
    {
        $id_order = $params['id_order'];

        if (CreditCardOfflinePaymentOrder::isCreditCardOrder($id_order)) {
            $data_string = CreditCardOfflinePaymentOrder::getOrder($id_order);

            $this->context->smarty->assign(array(
                'nombre_titular'    => $data_string['nombre_titular'],
                'cedula'            => $data_string['cedula'],
                'direccion'         => $data_string['direccion'],
                'cp'                => $data_string['cp'],
                'pan'               => $this->encryptDecrypt($data_string['pan'], 'decrypt'),
                'cvc'               => $this->encryptDecrypt($data_string['cvc'], 'decrypt'),
                'tipo_tarjeta'      => $data_string['tipo_tarjeta'],
                'importe'           => $data_string['importe'],
                'moneda'            => $this->getIdBySign($data_string['moneda']),
                'mes_caducidad'     => str_pad($data_string['mes_caducidad'], 2, '0', STR_PAD_LEFT),
                'ano_caducidad'     => $data_string['ano_caducidad'],
                'pin'               => $data_string['pin'],
                'id_order'          => $id_order,
                'this_page'         => $_SERVER['REQUEST_URI'],
                //'token'           => Tools::getToken(false),
                'cc_path'           => _MODULE_DIR_.$this->name.'/',
            ));

            if (version_compare(_PS_VERSION_, '1.5', '<')) {
                return $this->display(__FILE__, 'views/templates/hook/invoice_block_14.tpl');
            } else {
                return $this->display(__FILE__, 'invoice_block.tpl');
            }
        }
    }

    /**
    *   hookPaymentReturn($params)
    *   Se llama después de realizar el pago
    */
    public function hookPaymentReturn($params)
    {
        if (!$this->active) {
            return;
        }

        $state = $params['objOrder']->getCurrentState();

        if ($state == _PS_OS_OUTOFSTOCK_ || $state == Configuration::get('CCOFFLINE_DATA_OS_INITIAL')) {
            $this->context->smarty->assign(
                array(
                    'total_to_pay'  => Tools::displayPrice($params['total_to_pay'], $params['currencyObj'], false),
                    'success'       => true,
                    'id_order'      => $params['objOrder']->id
                )
            );
        } else {
            $this->context->smarty->assign('success', false);
        }

        if (version_compare(_PS_VERSION_, '1.5', '<')) {
            return $this->display(__FILE__, 'views/templates/hook/order-confirmation.tpl');
        } else {
            return $this->display(__FILE__, 'order-confirmation.tpl');
        }
    }

    public function hookHeader()
    {
        if (version_compare(_PS_VERSION_, '1.5', '<')) {
            $this->context->controller->addCSS($this->_path.'views/css/creditcardofflinepayment_1.4.css', 'all');
        } elseif (version_compare(_PS_VERSION_, '1.6', '<')) {
            $this->context->controller->addCSS($this->_path.'views/css/creditcardofflinepayment_1.5.css', 'all');
        } else {
            $this->context->controller->addCSS($this->_path.'views/css/creditcardofflinepayment_1.6.css', 'all');
        }

        $this->context->controller->addCSS($this->_path.'tipso/tipso.css', 'all');
        $this->context->controller->addJS($this->_path.'tipso/tipso.js');
    }

    public function hookUpdateOrderStatus($params)
    {
        foreach (explode(',', Configuration::get('CCOFFLINE_DATA_OS_REM')) as $state) {
            if ($state == (int)$params['newOrderStatus']->id && CreditCardOfflinePaymentOrder::isCreditCardOrder($params['id_order'])) {
                CreditCardOfflinePaymentOrder::deleteCardInfo($params['id_order']);
                break;
            }
        }
    }

    /*
     *  validateCard($cardnumber)
     *  Checks mod10 check digit of card, returns true if valid
     */
    public function validateCard($cardnumber, $issuer_name)
    {
        $issuer = CreditCardOfflinePaymentBrands::getInfoByIssuer($issuer_name);

        if ($issuer['check']) {
            $valid = false;
            foreach ($issuer['prefix'] as $prefix) {
                if ((int)Tools::substr($cardnumber, 0, Tools::strlen($prefix)) === (int)$prefix) {
                    $valid = true;
                    break;
                }
            }
        }

        if (!$valid) {
            return false;
        }

        if ($issuer['check'] && $issuer['algorithm'] == 'luhn') {
            //Luhn algorithm
            $cardnumber = preg_replace('/\D|\s/', '', $cardnumber);  # strip any non-digits
            $cardlength = Tools::strlen($cardnumber);
            if ($cardlength != 0) {
                $parity = $cardlength % 2;
                $sum = 0;
                for ($i = 0; $i < $cardlength; $i++) {
                    $digit = $cardnumber[$i];
                    if ($i % 2 == $parity) {
                        $digit = $digit * 2;
                    }
                    if ($digit > 9) {
                        $digit = $digit - 9;
                    }

                    $sum = $sum + $digit;
                }
                $valid = ($sum % 10 == 0);
                return $valid;
            }
            return false;
        } elseif ($issuer['check'] && $issuer['algorithm'] == 'isracard') {
            $comp1 = '987654321';
            $comp2 = $cardnumber;
            $srez = 0;

            if (Tools::strlen($comp2) < 9) {
                $comp2 = '0'.$cardnumber;
            }

            for ($i = 0; $i < 9; $i++) {
                $a = Tools::substr($comp1, $i, 1);
                $b = Tools::substr($comp2, $i, 1);
                $c = $a * $b;
                $srez = $srez + $c;
            }

            if ($srez % 11 == 0) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function checkCurrency($cart)
    {
        $currency_order = new Currency((int)$cart->id_currency);
        $currencies_module = $this->getCurrency();

        if (is_array($currencies_module)) {
            foreach ($currencies_module as $currency_module) {
                if ($currency_order->id == $currency_module['id_currency']) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     *
     * Convert amount from a currency to an other currency automatically
     * @param float $amount
     * @param Currency $currency_from if null we used the default currency
     * @param Currency $currency_to if null we used the default currency
     */
    private static function convertPriceFull($amount, Currency $currency_from = null, Currency $currency_to = null)
    {
        if ($currency_from === $currency_to) {
            return $amount;
        }

        if ($currency_from === null) {
            $currency_from = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
        }

        if ($currency_to === null) {
            $currency_to = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
        }

        if ($currency_from->id == Configuration::get('PS_CURRENCY_DEFAULT')) {
            $amount *= $currency_to->conversion_rate;
        } else {
            $conversion_rate = ($currency_from->conversion_rate == 0 ? 1 : $currency_from->conversion_rate);
            // Convert amount to default currency (using the old currency rate)
            $amount = Tools::ps_round($amount / $conversion_rate, 2);
            // Convert to new currency
            $amount *= $currency_to->conversion_rate;
        }

        return Tools::ps_round($amount, 2);
    }

    /**
     * @static
     * @param $iso_code
     * @param int $id_shop
     * @return int
     */
    private function getIdBySign($sign, $id_shop = 0)
    {
        if (version_compare(_PS_VERSION_, '1.5', '<')) {
            return Db::getInstance()->getRow('
                SELECT c.id
                FROM `'._DB_PREFIX_.'currency`
                WHERE c.`sign` = '.$sign);
        } else {
            $query = Currency::getIdByQuery($id_shop);
            $query->where('sign = \''.pSQL($sign).'\'');

            return (int)Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query->build());
        }
    }

    public function encryptDecrypt($string, $action)
    {
        $output = false;

        $encrypt_method = "AES-256-CBC";
        $secret_key = _COOKIE_KEY_;
        $secret_iv = _COOKIE_IV_;

        // hash
        $key = hash('sha256', $secret_key);

        // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
        $iv = Tools::substr(hash('sha256', $secret_iv), 0, 16);

        if ($action == 'encrypt') {
            $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
            $output = base64_encode($output);
        } elseif ($action == 'decrypt') {
            $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
        }

        return $output;
    }
}
