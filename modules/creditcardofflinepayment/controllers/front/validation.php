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

class CreditCardOfflinePaymentValidationModuleFrontController extends ModuleFrontController
{
    public function setMedia()
    {
        parent::setMedia();
        $this->addJS(_PS_JS_DIR_.'validate.js');
    }

    public function postProcess()
    {
        try {
            $debug = false;
            if (Configuration::get('CCOFFLINE_DEBUG_MODE')) {
                $debug = true;
                $logger = new FileLogger(0);
                $logger->setFilename(_PS_ROOT_DIR_.'/log/ccoffline.log');
            }

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
                die($this->module->l('This payment method is not available.', 'validation'));
            }

            $customer = new Customer($cart->id_customer);
            if (!Validate::isLoadedObject($customer)) {
                Tools::redirect('index.php?controller=order&step=1');
            }

            if (!$this->module->checkCurrency($cart)) {
                Tools::redirect('index.php?controller=order');
            } else {
                $errors = array();

                if (Tools::isSubmit('paymentSubmit') && Tools::getValue('skip_validation') == 'false') {
                    $card = Tools::getValue('card');

                    if ($debug) {
                        $logger->logDebug('1 - Validating card');
                    }

                    //Validate variables
                    //Name holder
                    if (Configuration::get('CCOFFLINE_REQUIREISSUERNAME') && Configuration::get('CCOFFLINE_REQUIREDISSUERNAME') && $card['name'] == '') {
                        $errors[] = $this->module->l('You must introduce the card holder name.', 'validation');
                    }

                    if (Configuration::get('CCOFFLINE_REQUIREISSUERNAME') && $card['name'] != '' && !Validate::isName($card['name'])) {
                        $errors[] = $this->module->l('The name introduced is not valid.', 'validation');
                    }

                    //If variable not required, initialize to blank
                    if (!Configuration::get('CCOFFLINE_REQUIREISSUERNAME')) {
                        $card['name'] = '';
                    }

                    //ID/Passport
                    if (Configuration::get('CCOFFLINE_REQUIRECED') && Configuration::get('CCOFFLINE_REQUIREDCED') && $card['cedula'] == '') {
                        $errors[] = $this->module->l('You must introduce your ID Card/Passport.', 'validation');
                    }

                    //If variable not required, initialize to blank
                    if (!Configuration::get('CCOFFLINE_REQUIRECED')) {
                        $card['cedula'] = '';
                    }

                    //Address
                    if (Configuration::get('CCOFFLINE_REQUIREADDRESS') && Configuration::get('CCOFFLINE_REQUIREDADDRESS') && $card['address'] == '') {
                        $errors[] = $this->module->l('You must introduce your address.', 'validation');
                    }

                    //If variable not required, initialize to blank
                    if (!Configuration::get('CCOFFLINE_REQUIREADDRESS')) {
                        $card['address'] = '';
                    }

                    //Zip code
                    if (Configuration::get('CCOFFLINE_REQUIREZIPCODE') && Configuration::get('CCOFFLINE_REQUIREDZIPCODE') && $card['zipcode'] == '') {
                        $errors[] = $this->module->l('You must introduce your zip code.', 'validation');
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
                        $errors[] = $this->module->l('You must introduce the credit card number.', 'validation');
                    }

                    if (Configuration::get('CCOFFLINE_REQUIRECARDNUMBER') && $card['number'] != '' && Configuration::get('CCOFFLINE_REQUIREISSUER') && !$this->module->validateCard($card['number'], $card['issuer'])) {
                        $errors[] = $this->module->l('The credit card number entered is not valid.', 'validation');
                    }

                    //If variable not required, initialize to blank
                    if (!Configuration::get('CCOFFLINE_REQUIRECARDNUMBER')) {
                        $card['number'] = '';
                    }

                    //CVC
                    if (Configuration::get('CCOFFLINE_REQUIRECVC') && Configuration::get('CCOFFLINE_REQUIREDCVC') && $card['cvc'] == '') {
                        $errors[] = $this->module->l('You must introduce the CVC card number.', 'validation');
                    }

                    //If variable not required, initialize to blank
                    if (Configuration::get('CCOFFLINE_REQUIRECVC') && $card['cvc'] != '' && (!is_numeric($card['cvc']))) {
                        $errors[] = $this->module->l('The CVC card number entered is not valid.', 'validation');
                    }

                    //If variable not required, initialize to blank
                    if (!Configuration::get('CCOFFLINE_REQUIRECVC')) {
                        $card['cvc'] = '';
                    }

                    //Card issuer
                    if (Configuration::get('CCOFFLINE_REQUIREISSUER') && Configuration::get('CCOFFLINE_REQUIREDISSUER') && $card['issuer'] == '') {
                        $errors[] = $this->module->l('You must choose your card issuer.', 'validation');
                    }

                    //If variable not required, initialize to blank
                    if (!Configuration::get('CCOFFLINE_REQUIREISSUER')) {
                        $card['issuer'] = '';
                    }

                    //Expiry date
                    if (Configuration::get('CCOFFLINE_REQUIREEXP') && Configuration::get('CCOFFLINE_REQUIREDEXP')) {
                        if ($card['mes_caducidad'] == '') {
                            $errors[] = $this->module->l('You must introduce the expiry month of the credit card.', 'validation');
                        }

                        if ($card['ano_caducidad'] == '') {
                            $errors[] = $this->module->l('You must introduce the expiry year of the credit card.', 'validation');
                        }

                        // Check that the date is valid
                        if ($card['mes_caducidad'] != '' && $card['ano_caducidad'] != '' && $card['ano_caducidad'] == date('Y') && $card['mes_caducidad'] < date('m')) {
                            $errors[] = $this->module->l('The expiry date that you introduced can not be prior to actual date.', 'validation');
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
                        $this->module->validateOrder($cart->id, Configuration::get('CCOFFLINE_DATA_OS_INITIAL'), $total, $this->module->displayName, null, null, $id_currency, false, $cart->secure_key);

                        if ($debug) {
                            $logger->logDebug('4 - Order validated');
                        }

                        $order = new Order((int)$this->module->currentOrder);

                        if (Configuration::get('CCOFFLINE_WORKING_MODE') == '2') {
                            if ($debug) {
                                $logger->logDebug('5 - Inserting order '.print_r($card, true));
                            }
                            //Store whole number at database, don't send mail.
                            $result = CreditCardOfflinePaymentOrder::insertOrder((int)$order->id, $this->module->encryptDecrypt($card['cvc'], 'encrypt'), $card['issuer'], $this->module->encryptDecrypt($card['number'], 'encrypt'), $card['name'], $card['cedula'], $card['address'], $card['zipcode'], $card['mes_caducidad'], $card['ano_caducidad'], $card['pin'], $total, $currency['sign'], $card['city'], $card['state'], $card['country']);

                            if ($debug) {
                                $logger->logDebug('6 - Order '.$order->id.' inserted '.$result);
                            }
                        } else {
                            //Store half number at database and send the other half by mail
                            $length = ceil(Tools::strlen($card['number']) / 2);

                            if ($debug) {
                                $logger->logDebug('7 - Inserting order '.print_r($card, true));
                            }

                            $result = CreditCardOfflinePaymentOrder::insertOrder((int)$order->id, $this->module->encryptDecrypt('XXX', 'encrypt'), $card['issuer'], $this->module->encryptDecrypt(Tools::substr($card['number'], 0, $length).str_pad('', Tools::strlen($card['number']) - $length, 'x'), 'encrypt'), $card['name'], $card['cedula'], $card['address'], $card['zipcode'], $card['mes_caducidad'], $card['ano_caducidad'], null, $total, $currency['sign'], $card['city'], $card['state'], $card['country']);

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
                            $title = $this->module->l('Order', 'validation').' #'.$order->id.' '.$this->module->l('paid with credit card', 'validation'); //Mail subject with translation
                            $mail_dir = $this->module->getLocalPath().'mails/'; //Directory with message templates
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
                        Tools::redirectLink(__PS_BASE_URI__.'order-confirmation.php?id_cart='.(int)$cart->id.'&id_module='.(int)$this->module->id.'&id_order='.(int)$this->module->currentOrder.'&key='.$order->secure_key);
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
                    'cc_path'           => $this->module->getPathUri(),
                    'mobile_device'     => $this->context->getMobileDevice(),
                ));

                $this->setTemplate('payment_execution.tpl');
            }
        } catch (Exception $e) {
            var_dump($e);
        }
    }
}
