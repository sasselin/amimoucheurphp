<?php
if (! defined('_PS_VERSION_'))
    exit();

class ShopyMind extends Module
{

    protected $client;

    protected $output = '';

    protected $promo_code = '';

    public function __construct()
    {
        $this->name = 'shopymind';
        $this->tab = 'advertising_marketing';
        $this->version = '2.1.1';
        $this->author = 'ShopiMind';
        $this->need_instance = 1;
        $this->module_key = '6b8340d426116d75ce10b85c707229da';
        $this->secure_key = Tools::encrypt($this->name);

        parent::__construct();

        $this->displayName = $this->l('ShopiMind');
        $this->description = $this->l('Relancer vos paniers abandonnés, contacter vos prospects, fidéliser vos clients. Shopimind vous permet d\'automatiser votre relation client et d\'augmenter votre chiffre d\'affaires.');

        if (_PS_VERSION_ < '1.5')
            require (_PS_MODULE_DIR_ . $this->name . '/backward_compatibility/backward.php');
    }

    public function updateModule()
    {
        if (_PS_VERSION_ < '1.5')
            Configuration::updateValue('SPM_voucherCumulablePromo', true);

        Db::getInstance()->Execute('DROP TABLE IF EXISTS  `' . _DB_PREFIX_ . 'spmcartoorder`');
    }

    public function install()
    {
        if (! function_exists('curl_init'))
            $this->_errors[] = $this->l('ShopiMind nécessite l\'extension PHP Curl, veuillez demander à votre fournisseur d\'hébergement d\'activer cette extension avant d\'installer ce module.');
        $this->updateModule();
        if (version_compare(_PS_VERSION_, "1.5", '<')) {
            if ((is_array($this->_errors) && count($this->_errors)) || ! parent::install() || ! $this->registerHook('footer') || ! $this->registerHook('header') || ! $this->registerHook('updateOrderStatus') || ! $this->registerHook('createAccount') || ! $this->registerHook('backOfficeHeader') || ! $this->registerHook('updateProduct') || ! $this->registerHook('deleteProduct') || ! $this->registerHook('addProduct') || ! $this->registerHook('categoryAddition') || ! $this->registerHook('categoryUpdate') || ! $this->registerHook('categoryDeletion') || ! $this->registerHook('updateQuantity'))
                return false;
        } else {
            if ((is_array($this->_errors) && count($this->_errors)) || ! parent::install() || ! $this->registerHook('displayFooter') || ! $this->registerHook('actionUpdateOrderStatus') || ! $this->registerHook('actionOrderStatusUpdate') || ! $this->registerHook('actionObjectUpdateAfter') || ! $this->registerHook('actionObjectDeleteAfter') || ! $this->registerHook('actionObjectAddAfter') || ! $this->registerHook('displayBackOfficeHeader'))
                return false;
        }
        $this->connectShopToSPM();
        return true;
    }

    public function getContext()
    {
        return $this->context;
    }

    public function uninstall()
    {
        if (/*!Configuration::deleteByName('SPM_apiIdentification') || !Configuration::deleteByName('SPM_apiPassword') ||  */! parent::uninstall())
            return false;
        return true;
    }

    public function getCustomerLocale($id_customer)
    {
        if (_PS_VERSION_ < '1.5.4') {
            $query = 'SELECT c.`iso_code`, SUBSTRING(d.`accept_language`,1,2) AS `iso_code2`,`country`.`iso_code` as `country_code`
                FROM `' . _DB_PREFIX_ . 'customer` a
                LEFT JOIN `' . _DB_PREFIX_ . 'address` AS `address` ON (`address`.`id_customer` = `a`.`id_customer`)
              LEFT JOIN `' . _DB_PREFIX_ . 'country` AS `country` ON (`address`.`id_country` = `country`.`id_country`)
                LEFT JOIN `' . _DB_PREFIX_ . 'cart` b ON (a.`id_customer` = b.`id_customer`)
                LEFT JOIN `' . _DB_PREFIX_ . 'lang` c ON (b.`id_lang` = c.`id_lang`)
                LEFT JOIN `' . _DB_PREFIX_ . 'guest` d ON (a.`id_customer` = d.`id_customer`)
                WHERE a.`id_customer` = ' . (int) $id_customer . '';
        } else {
            $query = 'SELECT b.`iso_code`, `country`.`iso_code` as `country_code`
                FROM `' . _DB_PREFIX_ . 'customer` a
                LEFT JOIN `' . _DB_PREFIX_ . 'address` AS `address` ON (`address`.`id_customer` = `a`.`id_customer`)
              LEFT JOIN `' . _DB_PREFIX_ . 'country` AS `country` ON (`address`.`id_country` = `country`.`id_country`)
                LEFT JOIN `' . _DB_PREFIX_ . 'lang` b ON (a.`id_lang` = b.`id_lang`)
                WHERE a.`id_customer` = ' . (int) $id_customer . '';
        }
        $row = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($query);
        return self::getLocaleFromSqlRow($row);
    }

    public static function getLocaleFromSqlRow($row)
    {
        if (isset($row['iso_code']) && $row['iso_code'])
            $lang = $row['iso_code'];
        elseif (isset($row['iso_code2']) && $row['iso_code2'])
            $lang = $row['iso_code2'];
        else
            $lang = Language::getIsoById(Configuration::get('PS_LANG_DEFAULT'));
        if (isset($row['country_code']) && $row['country_code'])
            $country = $row['country_code'];
        else
            $country = Country::getIsoById(Configuration::get('PS_COUNTRY_DEFAULT'));
        return Tools::strtolower($lang) . '_' . Tools::strtoupper($country);
    }

    public function _isLogged()
    {
        if (_PS_VERSION_ < 1.5)
            $isLogged = (isset($this->context->customer) && isset($this->context->customer->id));
        else
            $isLogged = @$this->context->customer->isLogged();
        return $isLogged;
    }

    private function _jsonEncode($a)
    {
        if (! function_exists('json_encode')) {
            if (is_null($a))
                return 'null';
            if ($a === false)
                return 'false';
            if ($a === true)
                return 'true';
            if (is_scalar($a)) {
                if (is_float($a)) {
                    // Always use "." for floats.
                    return floatval(str_replace(",", ".", strval($a)));
                }

                if (is_string($a)) {
                    static $jsonReplaces = array(
                        array(
                            "\\",
                            "/",
                            "\n",
                            "\t",
                            "\r",
                            "\b",
                            "\f",
                            '"'
                        ),
                        array(
                            '\\\\',
                            '\\/',
                            '\\n',
                            '\\t',
                            '\\r',
                            '\\b',
                            '\\f',
                            '\"'
                        )
                    );
                    return '"' . str_replace($jsonReplaces[0], $jsonReplaces[1], $a) . '"';
                } else
                    return $a;
            }
            $isList = true;
            for ($i = 0, reset($a); $i < count($a); $i ++, next($a)) {
                if (key($a) !== $i) {
                    $isList = false;
                    break;
                }
            }
            $result = array();
            if ($isList) {
                foreach ($a as $v)
                    $result[] = Tools::jsonEncode($v);
                return '[' . join(',', $result) . ']';
            } else {
                foreach ($a as $k => $v)
                    $result[] = Tools::jsonEncode($k) . ':' . Tools::jsonEncode($v);
                return '{' . join(',', $result) . '}';
            }
        } else
            return Tools::jsonEncode($a);
    }

    private function __httpBuildQuery($data, $prefix = null, $sep = null, $key = '', $urlencode = true)
    {
        if (! function_exists('http_build_query')) {
            $ret = array();

            foreach ((array) $data as $k => $v) {
                if ($urlencode)
                    $k = urlencode($k);
                if (is_int($k) && $prefix != null)
                    $k = $prefix . $k;
                if (! empty($key))
                    $k = $key . '%5B' . $k . '%5D';
                if ($v === NULL)
                    continue;
                elseif ($v === FALSE)
                    $v = '0';

                if (is_array($v) || is_object($v))
                    array_push($ret, $this->__httpBuildQuery($v, '', $sep, $k, $urlencode));
                elseif ($urlencode)
                    array_push($ret, $k . '=' . urlencode($v));
                else
                    array_push($ret, $k . '=' . $v);
            }

            if (NULL === $sep)
                $sep = ini_get('arg_separator.output');

            return implode($sep, $ret);
        } else
            return http_build_query($data);
    }

    private function _getCurrentUserInfos()
    {
        $currentUserInfos = array();
        $cookieVisitorId = new Cookie('cookieVisitorId', '', (time() + 657000));
        $cookieSessionVisitorId = new Cookie('cookieSessionVisitorId', '', 0);
        if (! $cookieVisitorId->visitor_id)
            $cookieVisitorId->visitor_id = md5(uniqid());
        if (! $cookieSessionVisitorId->session_id)
            $cookieSessionVisitorId->session_id = md5(uniqid());
        $url = '//' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $currentUserInfos['url'] = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
        if ($this->_isLogged()) {
            $currentUserInfos['user'] = array(
                'id_customer' => htmlentities($this->context->customer->id, ENT_COMPAT, 'UTF-8'),
                'optin' => htmlentities($this->context->customer->optin, ENT_COMPAT, 'UTF-8'),
                'newsletter' => htmlentities($this->context->customer->newsletter, ENT_COMPAT, 'UTF-8'),
                'customer_since' => htmlentities($this->context->customer->date_add, ENT_COMPAT, 'UTF-8'),
                'email_address' => htmlentities(md5($this->context->customer->email), ENT_COMPAT, 'UTF-8'),
                'birthday' => htmlentities($this->context->customer->birthday, ENT_COMPAT, 'UTF-8'),
                'gender' => htmlentities(($this->context->customer->id_gender === 3 ? 2 : $this->context->customer->id_gender), ENT_COMPAT, 'UTF-8')
            );
        } else
            $currentUserInfos['user'] = null;

            // Id du panier
        if (isset($this->context->cart) && isset($this->context->cart->id)) {
            include_once (_PS_MODULE_DIR_ . $this->name . '/ShopymindClient/callback.php');
            $currentUserInfos['id_cart'] = htmlentities($this->context->cart->id, ENT_COMPAT, 'UTF-8');

            $currency = new Currency($this->context->cart->id_currency);

            $vouchers = ShopymindClient_Callback::getCartVouchers();
            if (is_array($vouchers) && count($vouchers))
                $vouchers = array_map('htmlentities', $vouchers);
            else
                $vouchers = null;

            $currentUserInfos['cart']['id_customer'] = htmlentities($this->context->cart->id_customer, ENT_COMPAT, 'UTF-8');
            $currentUserInfos['cart']['id_cart'] = htmlentities($this->context->cart->id, ENT_COMPAT, 'UTF-8');
            $currentUserInfos['cart']['date_add'] = htmlentities($this->context->cart->date_add, ENT_COMPAT, 'UTF-8');
            $currentUserInfos['cart']['date_upd'] = htmlentities($this->context->cart->date_upd, ENT_COMPAT, 'UTF-8');
            $currentUserInfos['cart']['amount'] = htmlentities($this->context->cart->getOrderTotal(true, Cart::BOTH_WITHOUT_SHIPPING), ENT_COMPAT, 'UTF-8');
            $currentUserInfos['cart']['tax_rate'] = htmlentities($currency->conversion_rate, ENT_COMPAT, 'UTF-8');
            $currentUserInfos['cart']['currency'] = htmlentities($currency->iso_code, ENT_COMPAT, 'UTF-8');
            $currentUserInfos['cart']['voucher_used'] = $vouchers;
            $currentUserInfos['cart']['voucher_amount'] = htmlentities(abs($this->context->cart->getOrderTotal(true, Cart::ONLY_DISCOUNTS)), ENT_COMPAT, 'UTF-8');

            $cartProducts = $this->context->cart->getProducts();

            if (is_array($cartProducts) && count($cartProducts)) {
                foreach ($cartProducts as $k => $product) {
                    $currentUserInfos['cart']['products'][] = array(
                        'id_product' => htmlentities($product['id_product'], ENT_COMPAT, 'UTF-8'),
                        'id_combination' => htmlentities($product['id_product_attribute'], ENT_COMPAT, 'UTF-8'),
                        'id_manufacturer' => htmlentities($product['id_manufacturer'], ENT_COMPAT, 'UTF-8'),
                        'qty' => htmlentities($product['cart_quantity'], ENT_COMPAT, 'UTF-8'),
                        'price' => htmlentities(Tax::excludeTaxeOption() ? $product['price_wt'] : $product['price'], ENT_COMPAT, 'UTF-8')
                    );
                }
            } else
                $currentUserInfos['cart']['products'] = null;
        } else
            $currentUserInfos['id_cart'] = null;

        $currentUserInfos['id_product'] = htmlentities(Tools::getValue('id_product'), ENT_COMPAT, 'UTF-8');
        $currentUserInfos['id_category'] = htmlentities(Tools::getValue('id_category'), ENT_COMPAT, 'UTF-8');
        $currentUserInfos['id_manufacturer'] = htmlentities(Tools::getValue('id_manufacturer'), ENT_COMPAT, 'UTF-8');
        $currentUserInfos['spm_ident'] = htmlentities(Configuration::get('SPM_apiIdentification'), ENT_COMPAT, 'UTF-8');
        $currentUserInfos['visitor_id'] = htmlentities($cookieVisitorId->visitor_id, ENT_COMPAT, 'UTF-8');
        $currentUserInfos['session_id'] = htmlentities($cookieSessionVisitorId->session_id, ENT_COMPAT, 'UTF-8');

        return $currentUserInfos;
    }

    public function hookDisplayFooter($params)
    {
        return $this->hookFooter($params);
    }

    public function hookHeader($params)
    {
        if ($this->_isLogged() && (Tools::isSubmit('submitIdentity') || Tools::isSubmit('submitAddress'))) {

            if (file_exists(_PS_MODULE_DIR_ . $this->name . '/ShopymindClient/callback.php')) {
                include_once (_PS_MODULE_DIR_ . $this->name . '/ShopymindClient/callback.php');
                try {
                    ShopymindClient_Callback::saveCustomer($this->context->customer->id);
                } catch (Exception $e) {}
            }
        }
    }

    public function hookFooter($params)
    {
        try {
            $SHOPYMIND_CLIENT_CONFIGURATION = require _PS_MODULE_DIR_ . $this->name . '/ShopymindClient/src/definitions.php';
        } catch (Exception $e) {}
        $spm_user_infos = $this->_getCurrentUserInfos();
        $spm_user_infos_encode = $this->__httpBuildQuery($spm_user_infos);
        $spm_user_infos = $this->_jsonEncode($spm_user_infos);
        $spm_client_url = htmlentities(@$SHOPYMIND_CLIENT_CONFIGURATION['api']['public_url'], ENT_COMPAT, 'UTF-8');

        $tpl = "<script type=\"text/javascript\">
  var _spmq = $spm_user_infos;
  var _spm_id_combination = function() { return ($('#idCombination').length ? $('#idCombination').val():'');};
  $(function() {
    (function() {
      var spm = document.createElement('script');
      spm.type = 'text/javascript';
      spm.async = true;
      spm.src = '$spm_client_url/remote/shopimindV2.js?$spm_user_infos_encode&id_combination='+_spm_id_combination()+'';
      var s_spm = document.getElementsByTagName('script')[0];
      s_spm.parentNode.insertBefore(spm, s_spm);
    })();
  });
</script>";
            return $tpl;
    }

    public function hookCreateAccount($params)
    {
        try {
        if ($params['newCustomer'] instanceof Customer) {
            if (isset($params['newCustomer']->id) && $params['newCustomer']->id) {
                include_once (_PS_MODULE_DIR_ . $this->name . '/ShopymindClient/callback.php');
                ShopymindClient_Callback::saveCustomer($params['newCustomer']->id);
            }
        }
        } catch (Exception $e) {}
    }

    public function hookBackOfficeHeader($params)
    {
        try {
        /**
         * Add/update Customer From Backoffice
         */
        if (Tools::getValue('tab') == 'AdminCustomers' && Tools::getValue('id_customer') && ! Tools::getIsset('deletecustomer')) {
            include_once (_PS_MODULE_DIR_ . $this->name . '/ShopymindClient/callback.php');
            ShopymindClient_Callback::saveCustomer(Tools::getValue('id_customer'));
        }

        /**
         * Delete Customer From Backoffice
         */
        if (Tools::getIsset('deletecustomer') && Tools::getIsset('deleteMode') && Tools::getValue('id_customer')) {
            include_once (_PS_MODULE_DIR_ . $this->name . '/ShopymindClient/callback.php');
            ShopymindClient_Callback::deleteCustomer(Tools::getValue('id_customer'));
        }

        /**
         * Add/update Customer Address From Backoffice
         */
        if (Tools::getValue('tab') == 'AdminAddresses' && Tools::getValue('id_address') && ! Tools::getIsset('deleteaddress')) {
            include_once (_PS_MODULE_DIR_ . $this->name . '/ShopymindClient/callback.php');
            $address = new Address(Tools::getValue('id_address'));
            ShopymindClient_Callback::saveCustomer($address->id_customer);
        }

    /**
     * Add/update Customer Group From Backoffice
     */
        /*
         * if(Tools::getValue('tab') == 'AdminGroups' && Tools::getValue('id_group') && !Tools::getIsset('deletegroup')){
         * include_once (_PS_MODULE_DIR_ . $this->name . '/ShopymindClient/callback.php');
         * ShopymindClient_Callback::deleteCustomerGroup(Tools::getValue('id_group'));
         * }
         */
        } catch (Exception $e) {}
    }

    public function hookActionObjectAddAfter(Array $params)
    {
        try {
        if (isset($params['object'])) {
            if ($params['object'] instanceof Product) {
                include_once (_PS_MODULE_DIR_ . $this->name . '/ShopymindClient/callback.php');
                ShopymindClient_Callback::saveProduct($params['object']->id);
            } elseif ($params['object'] instanceof Customer) {
                include_once (_PS_MODULE_DIR_ . $this->name . '/ShopymindClient/callback.php');
                ShopymindClient_Callback::saveCustomer($params['object']->id);
            }            // Fonctionne pas. Voir PK ?
            elseif ($params['object'] instanceof Address) {
                include_once (_PS_MODULE_DIR_ . $this->name . '/ShopymindClient/callback.php');
                if (isset($params['object']->id_customer) && $params['object']->id_customer) {
                    ShopymindClient_Callback::saveCustomer($params['object']->id_customer);
                }
            } elseif ($params['object'] instanceof Category) {
                include_once (_PS_MODULE_DIR_ . $this->name . '/ShopymindClient/callback.php');
                ShopymindClient_Callback::saveProductCategory($params['object']->id);
            }
        }
        } catch (Exception $e) {}
    }

    public function hookActionObjectUpdateAfter(Array $params)
    {
        try {
        if (isset($params['object'])) {
            if ($params['object'] instanceof Product) {
                include_once (_PS_MODULE_DIR_ . $this->name . '/ShopymindClient/callback.php');
                ShopymindClient_Callback::saveProduct($params['object']->id);
            } elseif ($params['object'] instanceof Customer) {
                include_once (_PS_MODULE_DIR_ . $this->name . '/ShopymindClient/callback.php');
                ShopymindClient_Callback::saveCustomer($params['object']->id);
            } elseif ($params['object'] instanceof Category) {
                include_once (_PS_MODULE_DIR_ . $this->name . '/ShopymindClient/callback.php');
                ShopymindClient_Callback::saveProductCategory($params['object']->id);
            }
        }
        } catch (Exception $e) {}
    }

    public function hookActionObjectDeleteAfter(Array $params)
    {
        try {
        if (isset($params['object'])) {
            if ($params['object'] instanceof Product) {
                include_once (_PS_MODULE_DIR_ . $this->name . '/ShopymindClient/callback.php');
                ShopymindClient_Callback::deleteProduct($params['object']->id);
            } elseif ($params['object'] instanceof Customer) {
                include_once (_PS_MODULE_DIR_ . $this->name . '/ShopymindClient/callback.php');
                ShopymindClient_Callback::deleteCustomer($params['object']->id);
            } elseif ($params['object'] instanceof Category) {
                include_once (_PS_MODULE_DIR_ . $this->name . '/ShopymindClient/callback.php');
                ShopymindClient_Callback::deleteProductCategory($params['object']->id);
            }
        }
        } catch (Exception $e) {}
    }

    public function hookActionOrderStatusUpdate($params)
    {
        return $this->hookUpdateOrderStatus($params);
    }

    public function hookUpdateOrderStatus($params)
    {
        $order = new Order(intval($params['id_order']));

        if ($order && ! Validate::isLoadedObject($order))
            return;
        //On vérifie que la commande n'est pas trop ancienne
        $date = new DateTime();
        $date->sub(new DateInterval('P3M'));
        if($date->format('Y-m-d H:i:s') > $order->date_add)
            return;
        try {
            include_once (_PS_MODULE_DIR_ . $this->name . '/ShopymindClient/callback.php');
            $date = new DateTime();
            $date->sub(new DateInterval('P3M'));
            if($date->format('Y-m-d H:i:s') < $order->date_add) {
                ShopymindClient_Callback::saveOrder($order->id);
            }
        } catch (Exception $e) {}
    }

    public function hookAddProduct(Array $params)
    {
        try {
        if ($params['product'] instanceof Product) {
            include_once (_PS_MODULE_DIR_ . $this->name . '/ShopymindClient/callback.php');
            ShopymindClient_Callback::saveProduct($params['product']->id);
        }
        } catch (Exception $e) {}
    }

    public function hookUpdateProduct(Array $params)
    {
        $this->hookAddProduct($params);
    }

    public function hookdeleteProduct(Array $params)
    {
        try {
        if ($params['product'] instanceof Product) {
            include_once (_PS_MODULE_DIR_ . $this->name . '/ShopymindClient/callback.php');
            ShopymindClient_Callback::deleteProduct($params['product']->id);
        }
        } catch (Exception $e) {}
    }

    public function hookCategoryAddition(Array $params)
    {
        try {
        if ($params['category'] instanceof Category) {
            include_once (_PS_MODULE_DIR_ . $this->name . '/ShopymindClient/callback.php');
            ShopymindClient_Callback::saveProductCategory($params['category']->id);
        }
        } catch (Exception $e) {}
    }

    public function hookCategoryUpdate(Array $params)
    {
        $this->hookCategoryAddition($params);
    }

    public function hookCategoryDeletion(Array $params)
    {
        try {
        if ($params['category'] instanceof Category) {
            include_once (_PS_MODULE_DIR_ . $this->name . '/ShopymindClient/callback.php');
            ShopymindClient_Callback::deleteProductCategory($params['category']->id);
        }
        } catch (Exception $e) {}
    }

    public function getContent()
    {
        if (! function_exists('curl_init'))
            return '<div class="error">' . $this->l('ShopiMind nécessite l\'extension PHP Curl, veuillez demander à votre fournisseur d\'hébergement d\'activer cette extension avant d\'utiliser ce module.') . '</div>';

        $this->_process();
        $this->output .= '<br /><br /><div style="text-align:center;">
    <a href="https://www.shopimind.com/" target="_blank"><img src="https://media.shopimind.com/img/logo.png" /></a></div><br /><br />
    <div class="info" style="font-size:14px;">
    ' . $this->l('ShopiMind est un service en-ligne dédié aux e-commerçants désirant améliorer leur relation client et leur chiffre d\'affaires, en leur permettant d\'automatiser l\'envoi d\'e-mails marketing personnalisés et automatisés.') . '<br />
    ' . $this->l('Un ensemble de 10 campagnes préconfigurées sont à votre disposition. Elles vous permettent de créer vos propre scénarios, telles des relances de paniers abandonnés ou bien l\'envoi d\'une offre spéciale pour l\'anniversaire de vos meilleurs clients.') . '<br /><br />
    ' . $this->l('N\'attendez plus ! Inscrivez-vous, essayez gratuitement notre service, et commencez à définir vos premières campagnes.') . '
    </div><br />';
        $this->_displayForm();
        return $this->output;
    }

    protected function getContactPageUrl()
    {
        if (_PS_VERSION_ < 1.5) {
            return Tools::getHttpHost(true) . __PS_BASE_URI__ . 'contact-form.php';
        } else {
            return $this->context->link->getPageLink('contact-form.php', true, Configuration::get('PS_LANG_DEFAULT'));
        }
    }

    protected function getProductImageSizes()
    {
            $results = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
    SELECT `name`, `width`, `height`
    FROM `' . _DB_PREFIX_ . 'image_type`
    WHERE `products` = 1
        ORDER BY `width` ASC');
            $return = array();
            if ($results && is_array($results) && sizeof($results)) {
                foreach ($results as $row) {
                    // Return
                    $return[$row['name']] = $row['name'].' ('.$row['width'].' * '.$row['height'].')';
                }
            }
            return $return;
    }

    protected function _displayForm()
    {
        $SPM_apiIdentification = Configuration::get('SPM_apiIdentification');
        if (! $SPM_apiIdentification) {
            require_once _PS_MODULE_DIR_ . $this->name . '/ShopymindClient/bin/Configuration.php';
            $ShopymindClient_Configuration = new ShopymindClient_Configuration();
            $this->output .= '<fieldset' . ($this->promo_code !== '' ? '' : ' style="display:none"') . ' id="SPM_registerForm">
            <legend style="background:#fff;"><img src="' . $this->_path . 'logo.gif" alt="" title=""/> ' . $this->l('Activation du service ShopiMind') . '</legend>
                  <p style="text-align:right;"><a href="javascript:void(0);" onclick="$(\'#SPM_registerForm\').slideUp(\'medium\');$(\'#SPM_ApiForm\').slideDown(\'medium\');" style="color:#DC2A0C;font-size:16px;"><b>' . $this->l('Vous avez déjà un compte ShopiMind ? Cliquez ici pour saisir directement vos informations API >>>') . '</b></a></p>
                <br />
                  <form method="post" action="https://client.shopimind.com/registration/remote" name="remoteRegister">
                  <div class="control-group">
                 <label for="shop[user][email]">' . $this->l('Adresse email') . ' <span style="color:#cc0000;">*</span> :</label>
                 <input type="text" value="' . htmlentities(Configuration::get('PS_SHOP_EMAIL'), ENT_COMPAT, 'UTF-8') . '" name="shop[user][email]" style="width:400px;" /><br />
                 </div><br />
                 <div class="control-group">
                    <label for="shop[user][password]">' . $this->l('Mot de passe') . ' <span style="color:#cc0000;">*</span> :</label>
                    <input type="password" value="" name="shop[user][password]" style="width:400px;" /><br />
                 </div><br />
                 <div class="control-group">
                      <label for="shop[user][passwordConfirm]">' . $this->l('Confirmez le mot de passe') . ' <span style="color:#cc0000;">*</span> :</label>
                    <input type="password" value="" name="shop[user][passwordConfirm]" style="width:400px;" /><br />
                 </div><br />
                 <div class="control-group">
                      <label for="shop[url]">' . $this->l('Url de votre boutique') . ' <span style="color:#cc0000;">*</span> :</label>
                 <input type="text" value="' . Tools::getHttpHost(true) . __PS_BASE_URI__ . '" name="shop[url]" placeholder="http://" style="width:400px;" /><br />
                 </div><br />
                 <div class="control-group">
                 <label for="shop[user][firstName]">' . $this->l('Prénom') . ' <span style="color:#cc0000;">*</span> :</label>
                 <input type="text" value="" name="shop[user][firstName]" style="width:400px;" /><br />
                 </div><br />
                 <div class="control-group">
                 <label for="shop[user][lastName]">' . $this->l('Nom') . ' <span style="color:#cc0000;">*</span> :</label>
                 <input type="text" value="" name="shop[user][lastName]" style="width:400px;" /><br />
                 </div><br />
                 <div class="control-group">
                 <label for="shop[user][societe]">' . $this->l('Société') . ' <span style="color:#cc0000;">*</span> :</label>
                 <input type="text" value="' . htmlentities(Configuration::get('PS_SHOP_NAME'), ENT_COMPAT, 'UTF-8') . '" name="shop[user][societe]" style="width:400px;" /><br />
                 </div><br />
                 <div class="control-group">
                 <label for="shop[user][societe]">' . $this->l('Numéro de TVA') . ' :</label>
                 <input type="text" value="" name="shop[user][vat_number]" style="width:400px;" /><br />
                 </div><br />
                 <div class="control-group">
                 <label for="shop[user][phoneFixe]">' . $this->l('Numéro de téléphone fixe') . ' <span style="color:#cc0000;">*</span> :</label>
                 <input type="text" value="' . htmlentities(Configuration::get('PS_SHOP_PHONE'), ENT_COMPAT, 'UTF-8') . '" name="shop[user][phoneFixe]" style="width:400px;" /><br />
                 </div><br />
                 <div class="control-group">
                 <label for="shop[user][phoneMobile]">' . $this->l('Numéro de téléphone mobile') . ' <span style="color:#cc0000;">*</span> :</label>
                 <input type="text" value="" name="shop[user][phoneMobile]" style="width:400px;" /><br />
                 </div><br />
                 <div class="control-group">
                 <label for="shop[user][rue]">' . $this->l('Rue') . ' <span style="color:#cc0000;">*</span> :</label>
                 <input type="text" value="' . htmlentities(Configuration::get('PS_SHOP_ADDR1'), ENT_COMPAT, 'UTF-8') . '" name="shop[user][rue]" style="width:400px;" /><br />
                 </div><br />
                 <div class="control-group">
                 <label for="shop[user][complement]">' . $this->l('Complément d\'adresse') . ' :</label>
                 <input type="text" value="' . htmlentities(Configuration::get('PS_SHOP_ADDR2'), ENT_COMPAT, 'UTF-8') . '" name="shop[user][complement]" style="width:400px;" /><br />
                 </div><br />
                 <div class="control-group">
                 <label for="shop[user][bp]">' . $this->l('Boite Postale') . ' :</label>
                 <input type="text" value="" name="shop[user][bp]" style="width:400px;" /><br />
                 </div><br />
                 <div class="control-group">
                 <label for="shop[user][zipCode]">' . $this->l('Code Postale') . ' <span style="color:#cc0000;">*</span> :</label>
                 <input type="text" value="' . htmlentities(Configuration::get('PS_SHOP_CODE'), ENT_COMPAT, 'UTF-8') . '" name="shop[user][zipCode]" style="width:400px;" /><br />
                 </div><br />
                 <div class="control-group">
                 <label for="shop[user][city]">' . $this->l('Ville') . ' <span style="color:#cc0000;">*</span> :</label>
                 <input type="text" value="' . htmlentities(Configuration::get('PS_SHOP_CITY'), ENT_COMPAT, 'UTF-8') . '" name="shop[user][city]" style="width:400px;" /><br />
                 </div><br />
                 <div class="control-group">
                 <label for="shop[user][country]">' . $this->l('Pays') . ' <span style="color:#cc0000;">*</span> :</label>
                 <select name="shop[user][country]" style="width:400px;" />';
            $countries = Country::getCountries(Configuration::get('PS_LANG_DEFAULT'), false);
            foreach ($countries as $country) {
                $this->output .= '<option value="' . $country['iso_code'] . '"' . (Configuration::get('PS_SHOP_COUNTRY_ID') === $country['id_country'] ? ' selected="selected"' : '') . '>' . $country['name'] . '</option>';
            }
            $this->output .= '<select>
                 <br />
                 </div><br />
                 <input type="hidden" value="prestashop" name="shop[solutionEcommerce]" />
                 <input type="hidden" value="' . htmlentities($this->promo_code, ENT_COMPAT, 'UTF-8') . '" name="shop[user][promoCode]" />
                    <center><input type="button" class="button" id="submitRemoteRegister" name="submitRemoteRegister" value="' . $this->l('Valider') . '" /></center>
                  </form>
           </fieldset>
          <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
           <script type="text/javascript">
              var jqspm = jQuery.noConflict();
             $("#submitRemoteRegister").bind("click", function () {
          $("#submitRemoteRegister").attr("disabled","disabled");
          var defaultSubmitValue = $("#submitRemoteRegister").attr("value");
          $("#submitRemoteRegister").attr("value","' . $this->l('Veuillez patienter') . '");
               var $form = $(\'form[name="remoteRegister"]\');
               $("ul.errors",$form).remove();
               jqspm.ajax({
            type: "POST",
            crossDomain: true,
            dataType: "json",
            url: $form.attr("action"),
            data: $form.serialize(),
            success:function(response){
              if (response.success === true) {
                      var apiKey = response.apiKey;
                      var identifiant = response.identifiant;
                      $(\'input[name="apiIdentification"]\').val(identifiant);
                      $(\'input[name="apiPassword"]\').val(apiKey);
                      $(\'input[name="just_activate"]\').val("1");
                      $(\'input[name="submitShopymind"]\').trigger("click");
                    }else {
                      $("#submitRemoteRegister").removeAttr("disabled");
                $("#submitRemoteRegister").attr("value",defaultSubmitValue);
                      alert("' . $this->l('Veuillez vérifier les erreurs dans le formulaire') . '");
                      $form.displayFormErrors(response.messages);
                    }
                },
                error:function(xhr,status,error){
                  $("#submitRemoteRegister").removeAttr("disabled");
              $("#submitRemoteRegister").attr("value",defaultSubmitValue);
                    alert(status);
                }
          });
            });

            var $form = null;

          var setFormErrorsMsg = function (objMSG, subFormName) {
              for (fieldKey in objMSG) {
                  if (typeof objMSG[fieldKey] === \'object\') {
                      if (typeof subFormName === \'undefined\') {
                          sub = fieldKey;
                      }
                      else {
                          sub = subFormName + \'[\' + fieldKey + \']\';
                      }

                      setFormErrorsMsg(objMSG[fieldKey], sub);
                  }
                  else {
                      var $li = $("<li></li>").append(objMSG[fieldKey]),
                          $parent = null;

                      if ($(\'label[for="\' + subFormName + \'"]\', $form).length > 0) {
                          $parent = $(\'label[for="\' + subFormName + \'"]\', $form).parent(".control-group");

                          if ($parent.length === 0) {
                              $parent = $(\'label[for="\' + subFormName + \'"]\', $form).parent();
                          }
                      }
                      else {
                          $parent = $(\'select[name="\' + subFormName + \'"], textarea[name="\' + subFormName + \'"], input[name="\' + subFormName + \'"]\', $form).parent(".controls");

                          if ($parent.length === 0) {
                              $parent = $(\'select[name="\' + subFormName + \'"], textarea[name="\' + subFormName + \'"], input[name="\' + subFormName + \'"]\', $form).parent("label");
                          }
                      }

                      if ($parent.find("ul.errors").length === 0) {
                          $parent.append($("<ul></ul>").addClass("errors"));
                      }

                      $parent.find("ul.errors").eq(0).append($li.hide());
                      $li.slideDown().css("color","#cc0000");
                  }
              }
          }

          $.fn.displayFormErrors = function (messages) {
              return this.each(function () {
                  $form = $(this);
                  setFormErrorsMsg(messages);
              });
          }
           </script>

           ';
        }

        $productImageSizes = $this->getProductImageSizes();
        $this->output .= '<fieldset' . ($SPM_apiIdentification || $this->promo_code == '' ? '' : ' style="display:none"') . ' id="SPM_ApiForm">
          <legend style="background:#fff;"><img src="' . $this->_path . 'logo.gif" alt="" title=""/> ' . $this->l('Données d\'identification ShopiMind') . '</legend>
            ' . (! $SPM_apiIdentification ? '<p style="text-align:right;"><a href="javascript:void(0);" onclick="$(\'#SPM_registerForm\').slideDown(\'medium\');$(\'#SPM_ApiForm\').slideUp(\'medium\');" style="color:#DC2A0C;font-size:16px;"><b>' . $this->l('Si vous n\'avez pas encore de compte ShopiMind, cliquez ici >>>') . '</b></a></p>
                <br />	' : '') . '
          <form method="post" action="" name="shopymindForm">
               <label for="apiIdentification">' . $this->l('Identifiant de l\'API') . ' <span style="color:#cc0000;">*</span> :</label>
                   <div class="margin-form">
                    <input type="text" value="' . htmlentities(Configuration::get('SPM_apiIdentification'), ENT_COMPAT, 'UTF-8') . '" name="apiIdentification" autocomplete="off" /><br /><br />
                   </div>
                   <label for="apiPassword">' . $this->l('Mot de passe API') . ' <span style="color:#cc0000;">*</span> :</label>
                  <div class="margin-form">
                      <input type="password" value="' . htmlentities(Configuration::get('SPM_apiPassword'), ENT_COMPAT, 'UTF-8') . '" name="apiPassword" autocomplete="off" /><br /><br />
                    </div>
                  <label>' . $this->l('Générer des codes de réduction nominatifs') . '</label>
        <div class="margin-form">
          <input type="radio" name="voucherNominative" id="voucherNominative_on" value="1" ' . (Tools::getValue('voucherNominative', Configuration::get('SPM_voucherNominative')) ? 'checked="checked" ' : '') . '/>
          <label class="t" for="voucherNominative_on"> <img src="../img/admin/enabled.gif" alt="' . $this->l('Activer') . '" title="' . $this->l('Activer') . '" /></label>
          <input type="radio" name="voucherNominative" id="voucherNominative_off" value="0" ' . (! Tools::getValue('voucherNominative', Configuration::get('SPM_voucherNominative')) ? 'checked="checked" ' : '') . '/>
          <label class="t" for="voucherNominative_off"> <img src="../img/admin/disabled.gif" alt="' . $this->l('Désactiver') . '" title="' . $this->l('Désactiver') . '" /></label>
          <p class="clear">' . $this->l('Les codes de réduction générés par ShopiMind doivent-ils être associés aux comptes de vos clients (cela implique que votre client doit être connecté pour l\'utiliser)') . '</p>
        </div>';
        if (_PS_VERSION_ < '1.5') {
            $this->output .= '<label>' . $this->l('Codes de réduction cumulables avec vos promotions ?') . '</label>
        <div class="margin-form">
          <input type="radio" name="voucherCumulablePromo" id="voucherCumulablePromo_on" value="1" ' . (Tools::getValue('voucherCumulablePromo', Configuration::get('SPM_voucherCumulablePromo')) ? 'checked="checked" ' : '') . '/>
          <label class="t" for="voucherCumulablePromo_on"> <img src="../img/admin/enabled.gif" alt="' . $this->l('Activer') . '" title="' . $this->l('Activer') . '" /></label>
          <input type="radio" name="voucherCumulablePromo" id="voucherCumulablePromo_off" value="0" ' . (! Tools::getValue('voucherCumulablePromo', Configuration::get('SPM_voucherCumulablePromo')) ? 'checked="checked" ' : '') . '/>
          <label class="t" for="voucherCumulablePromo_off"> <img src="../img/admin/disabled.gif" alt="' . $this->l('Désactiver') . '" title="' . $this->l('Désactiver') . '" /></label>
        <p class="clear">' . $this->l('Les codes de réduction générés par ShopiMind doivent-ils être cumulables avec vos promotions en cours ?') . '</p>
          </div>';
        }
        $this->output .= '<label>' . $this->l('Codes de réduction cumulables avec d\'autres codes ?') . '</label>
        <div class="margin-form">
          <input type="radio" name="voucherCumulableVoucher" id="voucherCumulableVoucher_on" value="1" ' . (Tools::getValue('voucherCumulableVoucher', Configuration::get('SPM_voucherCumulableVoucher')) ? 'checked="checked" ' : '') . '/>
          <label class="t" for="voucherCumulableVoucher_on"> <img src="../img/admin/enabled.gif" alt="' . $this->l('Activer') . '" title="' . $this->l('Activer') . '" /></label>
          <input type="radio" name="voucherCumulableVoucher" id="voucherCumulableVoucher_off" value="0" ' . (! Tools::getValue('voucherCumulableVoucher', Configuration::get('SPM_voucherCumulableVoucher')) ? 'checked="checked" ' : '') . '/>
          <label class="t" for="voucherCumulableVoucher_off"> <img src="../img/admin/disabled.gif" alt="' . $this->l('Désactiver') . '" title="' . $this->l('Désactiver') . '" /></label>
                <p class="clear">' . $this->l('Les codes de réduction générés par ShopiMind doivent-ils être cumulables avec les autres codes promotionnels ?') . '</p>
          </div>';
        $this->output .= '<label>' . $this->l('Format d\'image produit à utiliser dans mes Emails') . '</label>
        <div class="margin-form">
          <select name="productImageSize">
            <option value="">' . $this->l('Automatique') . '</option>';
        $currentProductImageSize = Configuration::get('SPM_productImageSize');
        foreach ($productImageSizes as $k=>$v)
            $this->output .= '<option value="'.htmlentities($k, ENT_COMPAT, 'UTF-8').'"'.($currentProductImageSize && $currentProductImageSize === $k ?' selected="selected"':'').'>' . htmlentities($v, ENT_COMPAT, 'UTF-8') . '</option>';
          $this->output .= '</select>
                <p class="clear">' . $this->l('Il s\'agit du format d\'image qui sera utilisé dans vos rappels et recommandations de produits.') . '</p>
          </div>';
        $this->output .= '<label>' . $this->l('URL Alternative webservice ShopiMind') . '</label>
        <div class="margin-form">
                      <input type="text" value="' . htmlentities(Configuration::get('SPM_apiClientUrl'), ENT_COMPAT, 'UTF-8') . '" name="apiClientUrl" autocomplete="off" /><br />
                      <p class="clear">' . $this->l('Ne remplissez cette option que si vous n\'arrivez pas à connecter votre module. Dans ce cas, veuillez contacter notre service client qui vous donnera les informations nécessaires.') . '</p>
                    </div>';

        $this->output .= '<input type="hidden" value="0" name="just_activate">
                    <center><input type="submit" class="button" name="submitShopymind" value="' . $this->l('Valider') . '" /></center>
                </form>
         </fieldset>';
    }

    protected function _process()
    {
        if (Tools::isSubmit('submitShopymind')) {
            $apiIdentification = Tools::getValue('apiIdentification');
            $apiPassword = Tools::getValue('apiPassword');
            $voucherNominative = (int) Tools::getValue('voucherNominative');
            $apiClientUrl = Tools::getValue('apiClientUrl');


            if (_PS_VERSION_ < '1.5')
                $voucherCumulablePromo = (int) Tools::getValue('voucherCumulablePromo');
            $voucherCumulableVoucher = (int) Tools::getValue('voucherCumulableVoucher');
            Configuration::updateValue('SPM_apiIdentification', html_entity_decode($apiIdentification, ENT_COMPAT, 'UTF-8'));
            Configuration::updateValue('SPM_apiPassword', html_entity_decode($apiPassword, ENT_COMPAT, 'UTF-8'));
            Configuration::updateValue('SPM_voucherNominative', $voucherNominative);
            if (_PS_VERSION_ < '1.5')
                Configuration::updateValue('SPM_voucherCumulablePromo', $voucherCumulablePromo);
            Configuration::updateValue('SPM_voucherCumulableVoucher', $voucherCumulableVoucher);
            if (trim($apiClientUrl))
                Configuration::updateValue('SPM_apiClientUrl', $apiClientUrl);
            else
                Configuration::deleteByName('SPM_apiClientUrl');
            $this->connectShopToSPM();
        }
    }

    protected function connectShopToSPM()
    {
        // Include
        require_once _PS_MODULE_DIR_ . $this->name . '/ShopymindClient/bin/Configuration.php';
        $apiIdentification = Configuration::get('SPM_apiIdentification');
        $apiPassword = Configuration::get('SPM_apiPassword');
        if (! $apiIdentification || ! $apiPassword)
            return;
        $defaultCurrency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
        $defaultCurrency = $defaultCurrency->iso_code;

        $shop_id_shop = null;
        if ($this->multiShopIsActivate()) {
            $shop_id_shop = $this->context->shop->id;
        }

        $timezone = Configuration::get('PS_TIMEZONE');
        if (is_numeric($timezone)) {
            $tmz = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('SELECT `name` FROM ' . _DB_PREFIX_ . 'timezone WHERE id_timezone = ' . intval(Configuration::get('PS_TIMEZONE')));
            $timezone = $tmz['name'];
        }
        $defaultLang = Language::getIsoById(Configuration::get('PS_LANG_DEFAULT'));

        if (! $apiIdentification || ! $apiPassword)
            return;
        $forceSyncProducts = false;
        //Check if new product image sync is nedeed
        if(isset($_POST['productImageSize'])) {
            $oldProductImageSize = Configuration::get('SPM_productImageSize');

            if($oldProductImageSize != $_POST['productImageSize']) $forceSyncProducts = true;

            Configuration::updateValue('SPM_productImageSize', $_POST['productImageSize']);
        }
            // Récupération d'un instance et renseignement des paramètres
        $config = ShopymindClient_Configuration::factory($apiIdentification, $apiPassword, $defaultLang, $defaultCurrency, $this->getContactPageUrl(), Configuration::get('PS_SHOP_PHONE'), $timezone, (bool) $this->isMultiShopContext(), $shop_id_shop, $forceSyncProducts);
        try {
            // Sauvegarde du fichier de configuration
            if (! $config->testConnection())
                throw new Exception($this->l('Erreur lors du test de connexion au serveur') . '<br /><b>' . $this->l('Veuillez vérifier que votre site n’est pas en mode maintenance ou protégé par un htaccess.') . '</b>');
            else {
                // Connexion au serveur et sauvegarde des informations
                $connect = $config->connectServer();

                if ($connect !== true) {
                    throw new Exception($this->l('Erreur lors de la connexion au serveur') . '<br /><b>' . $this->l('Veuillez vérifier que votre site n’est pas en mode maintenance ou protégé par un htaccess.') . '</b>');
                } else {




                    if ((int) Tools::getValue('just_activate'))
                        $this->output .= $this->displayConfirmation($this->l('Activation réussi !') . '<br />' . $this->l('Nous venons de vous envoyer un email afin de finaliser votre inscription. Ouvrez-le et laissez vous guider.'));
                    else
                        $this->output .= $this->displayConfirmation($this->l('Configuration sauvegardée'));
                }
            }
        } catch (Exception $e) {
            $this->output .= '<div class="warning warn clear">' . $e->getMessage() . '</div>';
        }
    }

    public function multiShopIsActivate()
    {
        return (bool) (_PS_VERSION_ >= 1.5 && Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE'));
    }

    public function isMultiShopContext()
    {
        if (_PS_VERSION_ < 1.5 || ! Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE'))
            return false;
        $shop_context = Shop::getContext();
        if ($shop_context == Shop::CONTEXT_ALL || ((! isset($this->context->controller->multishop_context_group) || $this->context->controller->multishop_context_group == false) && $shop_context == Shop::CONTEXT_GROUP))
            return false;
        return true;
    }
}
