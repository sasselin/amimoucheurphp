<?php
/**
 * common.conf.php file defines all required constants and variables for entire module's context - install / admin / hook / tab
 *
 * @author    Business Tech SARL <http://www.businesstech.fr/en/contact-us>
 * @copyright 2003-2016 Business Tech SARL
 */

/* defines constant of module name */
define('_GMC_MODULE_NAME', 'GMC');
/* defines module name */
define('_GMC_MODULE_SET_NAME', 'gmerchantcenter');
/* defines root path of the shop */
define('_GMC_SHOP_PATH_ROOT', _PS_ROOT_DIR_ . '/');
/* defines root path of module */
define('_GMC_PATH_ROOT', _PS_MODULE_DIR_ . _GMC_MODULE_SET_NAME . '/');
/* defines conf path */
define('_GMC_PATH_CONF', _GMC_PATH_ROOT . 'conf/');
/* defines library path */
define('_GMC_PATH_LIB', _GMC_PATH_ROOT . 'lib/');
/* defines common library path */
define('_GMC_PATH_LIB_COMMON', _GMC_PATH_LIB . 'common/');
/* defines sql path */
define('_GMC_PATH_SQL', _GMC_PATH_ROOT . 'sql/');
/* defines views folder */
define('_GMC_PATH_VIEWS', 'views/');
/* defines js URL */
define('_GMC_URL_JS', _MODULE_DIR_ . _GMC_MODULE_SET_NAME . '/' . _GMC_PATH_VIEWS . 'js/');
/* defines css URL */
define('_GMC_URL_CSS', _MODULE_DIR_ . _GMC_MODULE_SET_NAME . '/' . _GMC_PATH_VIEWS . 'css/');
/* defines MODULE URL */
define('_GMC_MODULE_URL', _MODULE_DIR_ . _GMC_MODULE_SET_NAME . '/');
/* defines img path */
define('_GMC_PATH_IMG', 'img/');
/* defines img URL */
define('_GMC_URL_IMG', _MODULE_DIR_ . _GMC_MODULE_SET_NAME . '/' . _GMC_PATH_VIEWS . _GMC_PATH_IMG);
/* defines tpl path name */
define('_GMC_PATH_TPL_NAME', _GMC_PATH_VIEWS . 'templates/');
/* defines tpl path */
define('_GMC_PATH_TPL', _GMC_PATH_ROOT . _GMC_PATH_TPL_NAME);
/* defines constant of error tpl */
define('_GMC_TPL_ERROR', 'error.tpl');
/* defines confirm tpl */
define('_GMC_TPL_CONFIRM', 'confirm.tpl');
/* defines activate / deactivate debug mode */
define('_GMC_DEBUG', true);
/* defines constant to use or not js on submit action */
define('_GMC_USE_JS', true);
/* defines variable for admin ctrl name */
define('_GMC_PARAM_CTRL_NAME', 'sController');
/* defines variable for admin ctrl name */
define('_GMC_ADMIN_CTRL', 'admin');
/* defines variable for the php script file to copy */
define('_GMC_XML_PHP_NAME', 'gmerchantcenter.xml.php');
/* defines variable for the php script file to copy */
define('_GMC_FEED_PHP_NAME', 'gmerchantcenter.feed.php');

/* defines variables to configuration settings */
$GLOBALS[_GMC_MODULE_NAME . '_CONFIGURATION'] = array(
	'GMERCHANTCENTER_VERSION' => '',
	'GMERCHANTCENTER_HOME_CAT' => '',
	'GMERCHANTCENTER_LINK' => '',
	'GMERCHANTCENTER_ID_PREFIX' => '',
	'GMERCHANTCENTER_AJAX_CYCLE' => 200,
	'GMERCHANTCENTER_EXPORT_OOS' => 0,
	'GMERCHANTCENTER_COND' => 'new',
	'GMERCHANTCENTER_P_COMBOS' => 0,
	'GMERCHANTCENTER_P_DESCR_TYPE' => 1,
	'GMERCHANTCENTER_IMG_SIZE' => '',
	'GMERCHANTCENTER_EXC_NO_EAN' => 1,
	'GMERCHANTCENTER_EXC_NO_MREF' => 1,
	'GMERCHANTCENTER_MIN_PRICE' => 0,
	'GMERCHANTCENTER_INC_STOCK' => 1,
	'GMERCHANTCENTER_INC_FEAT' => 0,
	'GMERCHANTCENTER_FEAT_OPT' => 0,
	'GMERCHANTCENTER_INC_GENRE' => 0,
	'GMERCHANTCENTER_GENRE_OPT' => 0,
	'GMERCHANTCENTER_INC_SIZE' => 0,
	'GMERCHANTCENTER_SIZE_OPT' => 0,
	'GMERCHANTCENTER_INC_COLOR' => '',
	'GMERCHANTCENTER_COLOR_OPT' => '',
	'GMERCHANTCENTER_INC_MATER' => 0,
	'GMERCHANTCENTER_MATER_OPT' => 0,
	'GMERCHANTCENTER_INC_PATT' => 0,
	'GMERCHANTCENTER_PATT_OPT' => 0,
	'GMERCHANTCENTER_INC_GEND' => 0,
	'GMERCHANTCENTER_GEND_OPT' => 0,
	'GMERCHANTCENTER_INC_ADULT' => 0,
	'GMERCHANTCENTER_ADULT_OPT' => 0,
	'GMERCHANTCENTER_INC_AGE' => 0,
	'GMERCHANTCENTER_AGE_OPT' => 0,
	'GMERCHANTCENTER_SHIP_CARRIERS' => '',
	'GMERCHANTCENTER_REPORTING' => 1,
	'GMERCHANTCENTER_HOME_CAT_ID' => 1,
	'GMERCHANTCENTER_MPN_TYPE' => 'supplier_ref',
	'GMERCHANTCENTER_INC_ID_EXISTS' => 0,
	'GMERCHANTCENTER_ADD_CURRENCY' => 0,
	'GMERCHANTCENTER_UTM_CAMPAIGN' => '',
	'GMERCHANTCENTER_UTM_SOURCE' => '',
	'GMERCHANTCENTER_UTM_MEDIUM' => '',
	'GMERCHANTCENTER_FEED_PROTECTION' => 1,
	'GMERCHANTCENTER_FEED_TOKEN' => md5(rand(1000, 1000000).time()),
	'GMERCHANTCENTER_EXPORT_MODE' => 0,
	'GMERCHANTCENTER_ADV_PRODUCT_NAME' => 0,
	'GMERCHANTCENTER_ADV_PROD_TITLE' => 0,
	'GMERCHANTCENTER_CHECK_EXPORT' => '',
	'GMERCHANTCENTER_INC_TAG_ADULT' => 0,
	'GMERCHANTCENTER_SHIPPING_USE' => 1,
	'GMERCHANTCENTER_PROD_EXCL' => '',
	'GMERCHANTCENTER_GTIN_PREF' => 'ean',
);

/* defines variable to translate js msg */
$GLOBALS[_GMC_MODULE_NAME . '_JS_MSG'] = array();

/* defines variable to define available weight units */
$GLOBALS[_GMC_MODULE_NAME . '_WEIGHT_UNITS'] = array('kg', 'lb', 'g', 'oz');

/* defines variable to define default home cat name translations */
$GLOBALS[_GMC_MODULE_NAME . '_HOME_CAT_NAME'] = array(
	'en' => 'home',
	'fr' => 'accueil',
	'it' => 'ignazio',
	'es' => 'ignacio',
);

/* defines available languages / countries / currencies for Google */
$GLOBALS[_GMC_MODULE_NAME . '_AVAILABLE_COUNTRIES'] = array(
	'en' => array(
		'GB' => array('currency' => 'GBP', 'taxonomy' => 'en-GB'),
		'US' => array('currency' => 'USD', 'taxonomy' => 'en-US'),
		'AU' => array('currency' => 'AUD', 'taxonomy' => 'en-GB'),
		'CA' => array('currency' => 'CAD', 'taxonomy' => 'en-US'),
		'IN' => array('currency' => 'INR', 'taxonomy' => 'en-GB'),
		'CH' => array('currency' => 'CHF', 'taxonomy' => 'en-GB')
	),
	'gb' => array(
		'GB' => array('currency' => 'GBP', 'taxonomy' => 'en-GB'),
		'AU' => array('currency' => 'AUD', 'taxonomy' => 'en-GB'),
		'IN' => array('currency' => 'INR', 'taxonomy' => 'en-GB'),
		'CH' => array('currency' => 'CHF', 'taxonomy' => 'en-GB')
	),
	'en-gb' => array(
		'GB' => array('currency' => 'GBP', 'taxonomy' => 'en-GB'),
		'AU' => array('currency' => 'AUD', 'taxonomy' => 'en-GB'),
		'IN' => array('currency' => 'INR', 'taxonomy' => 'en-GB'),
		'CH' => array('currency' => 'CHF', 'taxonomy' => 'en-GB')
	),
	'en-us' => array(
		'US' => array('currency' => 'USD', 'taxonomy' => 'en-US'),
		'CA' => array('currency' => 'CAD', 'taxonomy' => 'en-US'),

	),
	'fr' => array(
		'FR' => array('currency' => 'EUR', 'taxonomy' => 'fr-FR'),
		'CH' => array('currency' => 'CHF', 'taxonomy' => 'fr-FR'),
		'CA' => array('currency' => 'CAD', 'taxonomy' => 'fr-FR'),
		'BE' => array('currency' => 'EUR', 'taxonomy' => 'fr-FR')
	),
	'de' => array(
		'DE' => array('currency' => 'EUR', 'taxonomy' => 'de-DE'),
		'CH' => array('currency' => 'CHF', 'taxonomy' => 'de-DE'),
		'AT' => array('currency' => 'EUR', 'taxonomy' => 'de-DE')
	),
	'it' => array(
		'IT' => array('currency' => 'EUR', 'taxonomy' => 'it-IT'),
		'CH' => array('currency' => 'CHF', 'taxonomy' => 'it-IT')
	),
	'nl' => array(
		'NL' => array('currency' => 'EUR', 'taxonomy' => 'nl-NL'),
		'BE' => array('currency' => 'EUR', 'taxonomy' => 'nl-NL')
	),
	'es' => array(
		'ES' => array('currency' => 'EUR', 'taxonomy' => 'es-ES'),
		'MX' => array('currency' => 'MXN', 'taxonomy' => 'es-ES')
	),
	'zh' => array(
		'CN' => array('currency' => 'CNY', 'taxonomy' => 'zh-CN')
	),
	'ja' => array(
		'JP' => array('currency' => 'JPY', 'taxonomy' => 'ja-JP')
	),
	'br' => array(
		'BR' => array('currency' => 'BRL', 'taxonomy' => 'pt-BR')
	),
	'cs' => array(
		'CZ' => array('currency' => 'CZK', 'taxonomy' => 'cs-CZ')
	),
	'ru' => array(
		'RU' => array('currency' => 'RUB', 'taxonomy' => 'ru-RU')
	),
	'sv' => array(
		'SE' => array('currency' => 'SEK', 'taxonomy' => 'sv-SE')
	),
	'da' => array(
		'DK' => array('currency' => 'DKK', 'taxonomy' => 'da-DK')
	),
	'no' => array(
		'NO' => array('currency' => 'NOK', 'taxonomy' => 'no-NO')
	),
	'pl' => array(
		'PL' => array('currency' => 'PLN', 'taxonomy' => 'pl-PL')
	),
	'tr' => array(
		'TR' => array('currency' => 'TRY', 'taxonomy' => 'tr-TR')
	),
);

/* defines variable to set request parameters */
$GLOBALS[_GMC_MODULE_NAME . '_MONTH'] = array(
	'en' => array(
		'short' => array('','Jan.','Feb.','March','Apr.','May','June','July','Aug.','Sept.','Oct.','Nov.','Dec.'),
		'long' => array('','January','February','March','April','May','June','July','August','September','October','November','December'),
	),
	'fr' => array(
		'short' => array('','Jan.','F&eacute;v.','Mars','Avr.','Mai','Juin','Juil.','Aout','Sept.','Oct.','Nov.','D&eacute;c.'),
		'long' => array('','Janvier','F&eacute;vrier','Mars','Avril','Mai','Juin','Juillet','Aout','Septembre','Octobre','Novembre','D&eacute;cembre'),
	),
	'de' => array(
		'short' => array('','Jan.','Feb.','M' . chr(132) . 'rz','Apr.','Mai','Juni','Juli','Aug.','Sept.','Okt.','Nov.','Dez.'),
		'long' => array('','Januar','Februar','M' . chr(132) . 'rz','April','Mai','Juni','Juli','August','September','Oktober','November','Dezember'),
	),
	'it' => array(
		'short' => array('','Gen.','Feb.','Marzo','Apr.','Mag.','Giu.','Lug.','Ago.','Sett.','Ott.','Nov.','Dic.'),
		'long' => array('','Gennaio','Febbraio','Marzo','Aprile','Maggio','Giugno','Luglio','Agosto','Settembre','Ottobre','Novembre','Dicembre'),
	),
	'es' => array(
		'short' => array('','Ene.','Feb.','Marzo','Abr.','Mayo','Junio','Jul.','Ago.','Sept.','Oct.','Nov.','Dic.'),
		'long' => array('','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'),
	),
);