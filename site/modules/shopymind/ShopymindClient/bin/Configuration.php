<?php
/**
 * Configuration
 *
 * @package     ShopymindClient
 * @copyright   Copyright (c) 2013 - IDVIVE SARL (http://www.idvive.com)
 * @license     New BSD license (http://license.idvive.com)
 * @author      Yannick Dalbin <yannick@idvive.com>
 * @version     $Id Configuration.php 2013-04-24$
 */

if (!isset($SHOPYMIND_CLIENT_CONFIGURATION)) {
    global $SHOPYMIND_CLIENT_CONFIGURATION;
    $SHOPYMIND_CLIENT_CONFIGURATION = array_merge_recursive(
        require dirname(__FILE__) . '/../src/definitions.php',
        require dirname(__FILE__) . '/../configuration.php'
    );
}

class ShopymindClient_Configuration {

    /**
     * Permet de configurer l'identifiant API
     *
     * @var string
     */
    protected $_apiIdentification;

    /**
     * Permet de configurer un mot de passe pour l'API
     *
     * @var string
     */
    protected $_apiPassword;

    /**
     * URL de connexion à l'api
     *
     * @var string
     */
    protected $_apiUrl;

    /**
     * URL du client
     *
     * @var string
     */
    protected $_urlClient;

    /**
     * Lang par défaut
     *
     * @var string
     */
    protected $_defaultLang;

    /**
     * La defaultCurrency par défaut
     *
     * @var string
     */
    protected $_defaultCurrency;

    /**
     * URL de page de contact
     *
     * @var string
     */
    protected $_contactPage;

    /**
     * Numéro de téléphone du service client
     *
     * @var string
     */
    protected $_phoneNumber;

    /**
     * Timezone de la boutique
     *
     * @var string
     */
    protected $_timezone;

    /**
     * Multi-boutiques activé ?
     *
     * @var boolean
     */
    protected $_multishopEnabled;

    /**
     * Id de la boutique (multi-boutiques)
     *
     * @var string
     */
    protected $_shopIdShop;

    /**
     * Force de la synchro produits
     *
     * @var string
     */
    protected $_forceSyncProducts;

    /**
     * Constructeur de l'objet config
     *
     * @return void
     */
    public function __construct() {
        $this->retrieveUrlClient();
    }

    /**
     * Permet de construire l'objet Configuration
     *
     * @param string $identifiantAPI
     * @param string $passwordAPI
     * @param string $defaultLang
     * @param string $defaultCurrency
     * @return ShopymindClient_Configuration
     */
    public static function factory($identifiantAPI, $passwordAPI, $defaultLang, $defaultCurrency, $contactPage = null, $phoneNumber = null, $timezone = null,$multishop_enabled = null,$shop_id_shop = null,$forceSyncProducts = false) {
        $config = new self;
        $config->setApiIdentification($identifiantAPI)
               ->setApiPassword($passwordAPI)
               ->setDefaultLang($defaultLang)
               ->setDefaultCurrency($defaultCurrency)
               ->setContactPage($contactPage)
               ->setPhoneNumber($phoneNumber)
               ->setTimezone($timezone)
               ->setMultishopEnabled($multishop_enabled)
        	   ->setShopIdShop($shop_id_shop)
               ->setForceSyncProducts($forceSyncProducts);
        return $config;
    }
    /**
     * Va tanter de trouver l'url du client
     *
     * @return string
     */
	public function retrieveUrlClient() {
		$base_dir = __DIR__;
		$doc_root = preg_replace("!{$_SERVER['SCRIPT_NAME']}$!", '', $_SERVER['SCRIPT_FILENAME']);
		$base_url = preg_replace("!^{$doc_root}!", '', $base_dir);
		$protocol = empty($_SERVER['HTTPS']) ? 'http' : 'https';
		$port = $_SERVER['SERVER_PORT'];
		$disp_port = ($protocol == 'http' && $port == 80 || $protocol == 'https' && $port == 443) ? '' : ":$port";
		$domain = $_SERVER['SERVER_NAME'];
		$url = "$protocol://{$domain}{$disp_port}{$base_url}";
		$url = preg_replace('#bin$#','',$url);
        $this->setUrlClient($url);
        return $url;
    }

    /**
     * Permet de connecter le serveur au module
     *
     * @return boolean
     */
    public function connectServer() {
        $data = array();

        if ($this->getDefaultLang() !== null) {
            $data['defaultLang'] = $this->getDefaultLang();
        }

        if ($this->getDefaultCurrency() !== null) {
            $data['defaultCurrency'] = $this->getDefaultCurrency();
        }

        if ($this->getContactPage() !== null) {
            $data['contactPage'] = $this->getContactPage();
        }

        if ($this->getPhoneNumber() !== null) {
            $data['phoneNumber'] = $this->getPhoneNumber();
        }

        if ($this->getUrlClient() !== null) {
            $data['urlClient'] = $this->getUrlClient();
        }

        if ($this->getTimezone() !== null) {
            $data['timezone'] = $this->getTimezone();
        }

        if ($this->getMultishopEnabled() !== null) {
            $data['multishopEnabled'] = $this->getMultishopEnabled();
        }

        if ($this->getShopIdShop() !== null) {
        	$data['shopIdShop'] = $this->getShopIdShop();
        }

        if ($this->getForceSyncProducts() !== null) {
            $data['forceSyncProducts'] = $this->getForceSyncProducts();
        }

        require_once dirname(__FILE__) . '/../src/Client.php';
        $client = new ShopymindClient_Client;
        $client->setRestService('connection');
        $client->setParameterPost($data);
        $response = $client->sendRequest('POST');

        if (is_array($response) && isset($response['success']) && $response['success'] === true) {
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * Permet de tester la communication avec le serveur
     *
     * @return boolean
     */
    public function testConnection() {
        require_once dirname(__FILE__) . '/../src/Client.php';
        $client = new ShopymindClient_Client;
        return $client->sayHello();
    }

    /**
     * Permet de lancer la configuration du client
     *
     * @return boolean
     */
    public function saveConfig() {
    	return true;
        global $SHOPYMIND_CLIENT_CONFIGURATION;
        $lastConfig = require dirname(__FILE__) . '/../configuration.php';

        if ($this->getApiIdentification() !== null) {
            $lastConfig['api']['identifiant'] = $this->getApiIdentification();
        }

        if ($this->getApiPassword() !== null) {
            $lastConfig['api']['password'] = $this->getApiPassword();
        }

        if ($this->getApiUrl() !== null) {
            $lastConfig['api']['url'] = $this->getApiUrl();
        }

        $content = "<?php\n"
                 . "return " . var_export($lastConfig, true) . ";\n";

        if (file_put_contents(dirname(__FILE__) . '/../configuration.php', $content) !== false) {
            $SHOPYMIND_CLIENT_CONFIGURATION = array_merge_recursive(
                require dirname(__FILE__) . '/../src/definitions.php',
                require dirname(__FILE__) . '/../configuration.php'
            );

            return true;
        }

        return false;
    }

    /**
     * Permet d'obtenir l'identifiant de l'API
     *
     * @return string
     */
    public function getApiIdentification() {
        return $this->_apiIdentification;
    }

    /**
     * Permet de modifier l'identifiant de l'API
     *
     * @param string $apiIdentification
     * @return ShopymindClient_Configuration
     */
    public function setApiIdentification($apiIdentification) {
        $this->_apiIdentification = $apiIdentification;
        return $this;
    }

    /**
     * Permet d'obtenir le mot de passe de l'API
     *
     * @return string
     */
    public function getApiPassword() {
        return $this->_apiPassword;
    }

    /**
     * Permet de modifier le mot de passe de l'API
     *
     * @param string $apiPassword
     * @return ShopymindClient_Configuration
     */
    public function setApiPassword($apiPassword) {
        $this->_apiPassword = $apiPassword;
        return $this;
    }

    /**
     * Permet d'obtenir l'url de l'api
     *
     * @return string
     */
    public function getApiUrl() {
        return $this->_apiUrl;
    }

    /**
     * Permet de modifier l'url de l'api
     *
     * @param string $apiUrl
     * @return ShopymindClient_Configuration
     */
    public function setApiUrl($apiUrl) {
        $this->_apiUrl = $apiUrl;
        return $this;
    }

    /**
     * Permet d'obtenir l'url du ShopymindClient
     *
     * @return string
     */
    public function getUrlClient() {
        return $this->_urlClient;
    }

    /**
     * Permet de modifier l'url du ShopymindClient
     *
     * @param string $urlClient
     * @return ShopymindClient_Configuration
     */
    public function setUrlClient($urlClient) {
        $this->_urlClient = $urlClient;
        return $this;
    }

    /**
     * Permet d'obtenir la langue par défaut de la boutique
     *
     * @return string
     */
    public function getDefaultLang() {
        return $this->_defaultLang;
    }

    /**
     * Permet de modifier la langue par défaut de la boutique
     *
     * @param string $defaultLang
     * @return ShopymindClient_Configuration
     */
    public function setDefaultLang($defaultLang) {
        $this->_defaultLang = $defaultLang;
        return $this;
    }

    /**
     * Permet d'obtenir la defaultCurrency de la boutique
     *
     * @return string
     */
    public function getDefaultCurrency() {
        return $this->_defaultCurrency;
    }

    /**
     * Permet de modifier la defaultCurrency de la boutique
     *
     * @param string $defaultCurrency
     * @return ShopymindClient_Configuration
     */
    public function setDefaultCurrency($defaultCurrency) {
        $this->_defaultCurrency = $defaultCurrency;
        return $this;
    }

    /**
     * Url de la page contact
     *
     * @return string
     */
    public function getContactPage() {
        return $this->_contactPage;
    }

    /**
     * Modifier la page de contact de page
     *
     * @param string $contactPage
     * @return ShopymindClient_Configuration
     */
    public function setContactPage($contactPage) {
        $this->_contactPage = $contactPage;
        return $this;
    }

    /**
     * Obtenir le numéro de téléphone du service client
     *
     * @return string
     */
    public function getPhoneNumber() {
        return $this->_phoneNumber;
    }

    /**
     * Modifier le numéro de téléphone du service client
     *
     * @param string $phoneNumber
     * @return ShopymindClient_Configuration
     */
    public function setPhoneNumber($phoneNumber) {
        $this->_phoneNumber = $phoneNumber;
        return $this;
    }

    /**
     * Permet de récupérer le timezone de la boutique
     *
     * @return string
     */
    public function getTimezone() {
        return $this->_timezone;
    }

    /**
     * Permet de modifier le timezone de la boutique
     *
     * @param string $timezone
     * @return ShopymindClient_Configuration
     */
    public function setTimezone($timezone) {
        $this->_timezone = $timezone;
        return $this;
    }

    /**
     * Permet de récupérer le statut de l'option multi-boutiques
     *
     * @return boolean
     */
    public function getMultishopEnabled() {
        return (bool) $this->_multishopEnabled;
    }

    /**
     * Permet de modifier le statut de l'option multi-boutiques
     *
     * @param boolean $timezone
     * @return ShopymindClient_Configuration
     */
    public function setMultishopEnabled($multishopEnabled) {
        $this->_multishopEnabled = (bool) $multishopEnabled;
        return $this;
    }

    /**
     * Permet de récupérer l'id de la boutique (multi-boutiques)
     *
     * @return string
     */
    public function getShopIdShop() {
    	return $this->_shopIdShop;
    }

    /**
     * Permet de modifier l'id de la boutique (multi-boutiques)
     *
     * @param string $shopIdShop
     * @return ShopymindClient_Configuration
     */
    public function setShopIdShop($shopIdShop) {
    	$this->_shopIdShop = $shopIdShop;
    	return $this;
    }

    /**
     * Permet de savoir si on doit re-synchroniser les produits
     *
     * @return string
     */
    public function getForceSyncProducts() {
        return $this->_forceSyncProducts;
    }

    /**
     * Permet d'indiquer si on doit re-synchroniser les produits
     *
     * @param string $forceSyncProducts
     * @return ShopymindClient_Configuration
     */
    public function setForceSyncProducts($forceSyncProducts) {
        $this->_forceSyncProducts = $forceSyncProducts;
        return $this;
    }
}
