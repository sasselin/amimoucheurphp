<?php
/**
 * gmerchantcenter.php file defines main class of module
 *
 * @author    Business Tech SARL <http://www.businesstech.fr/en/contact-us>
 * @copyright 2003-2016 Business Tech SARL
 * @version   4.3.7
 * @category  main class
 * @uses      Please read included installation and configuration instructions (PDF format)
 * @see       lib/install
 *              => i-install.php => interface
 *              => install-ctrl_class.php => controller, manage factory with config or sql install object
 *              => install.config classes => manage install / uninstall of config values (register hook)
 *            lib/admin
 *              => i-admin.php => interface
 *              => admin-ctrl_class.php => controller, manage factory with configure or update admin object
 *              => display and update admin classes => manage display of admin form and make action of updating config like (add, edit, delete, update, ... see PHP Doc in class)
 *            lib/hook
 *              => hook-base_class.php => abstract
 *              => hook-ctrl_class.php => controller, manage strategy with hook object. Like this, you can add hook easily with declare a new file class
 *              => hook-home_class.php => manage displaying content on your home page
 *            lib/module-dao_class.php
 *              D A O = Data Access Object => manage all sql queries
 *            lib/module-tools_class.php
 *              declare all transverse functions which are unclassifiable in specific class
 *            lib/warnings_class.php
 *              manage all displaying warnings when module isn't already configured after installation
 *
 * @date      30/05/2016
 */

if (!defined('_PS_VERSION_')) {
	exit(1);
}

class GMerchantCenter extends Module
{
	/**
	 * @var array $aConfiguration : array of set configuration
	 */
	public static $aConfiguration = array();

	/**
	 * @var int $iCurrentLang : store id of default lang
	 */
	public static $iCurrentLang = null;

	/**
	 * @var int $sCurrentLang : store iso of default lang
	 */
	public static $sCurrentLang = null;

	/**
	 * @var obj $oCookie : store cookie obj
	 */
	public static $oCookie = null;

	/**
	 * @var obj $oModule : obj module itself
	 */
	public static $oModule = array();

	/**
	 * @var string $sQueryMode : query mode - detect XHR
	 */
	public static $sQueryMode = null;

	/**
	 * @var string $sBASE_URI : base of URI in prestashop
	 */
	public static $sBASE_URI = null;

	/**
	 * @var string $sHost : store the current domain
	 */
	public static $sHost = '';

	/**
	 * @var int $iShopId : shop id used for 1.5 and for multi shop
	 */
	public static $iShopId = 1;

	/**
	 * @var bool $bCompare149 : get compare version for PS 1.4.9
	 */
	public static $bCompare149 = false;

	/**
	 * @var bool $bCompare15 : get compare version for PS 1.5
	 */
	public static $bCompare15 = false;

	/**
	 * @var bool $bCompare1550 : get compare version for PS 1.5.5.0
	 */
	public static $bCompare1550 = false;

	/**
	 * @var bool $bCompare16 : get compare version for PS 1.6
	 */
	public static $bCompare16 = false;

	/**
	 * @var obj $oContext : get context object
	 */
	public static $oContext;

	/**
	 * @var array $aAvailableLanguages : store the available languages
	 */
	public static $aAvailableLanguages = array();

	/**
	 * @var array $aAvailableLangCurrencyCountry : store the available related languages / countries / currencies
	 */
	public static $aAvailableLangCurrencyCountry = array();

	/**
	 * @var string $sFilePrefix : store the XML file's prefix
	 */
	public static $sFilePrefix = '';


	/**
	 * @var array $aErrors : array get error
	 */
	public $aErrors = null;


	/**
	 * Magic Method __construct assigns few information about module and instantiate parent class
	 */
	public function __construct()
	{
		// hack for older version than 1 4 5 1
		if (is_file(dirname(__FILE__) . '/conf/common.conf.php')) {
			require_once(dirname(__FILE__) . '/conf/common.conf.php');
		}
		else {
			require_once(_PS_MODULE_DIR_ . 'gmerchantcenter/conf/common.conf.php');
		}
		require_once(_GMC_PATH_LIB . 'module-tools_class.php');

		$this->name = 'gmerchantcenter';
		$this->module_key = '315713e1154d1eeae38c07f1548fef39';
		$this->tab = 'seo';
		$this->version = '4.3.8';
		$this->author = 'Business Tech';

		parent::__construct();

		$this->displayName = $this->l('Google Merchant Center');
		$this->description = $this->l('Export your product catalog to Google Merchant Center');
		$this->confirmUninstall = $this->l('Are you sure you want to remove Google Merchant Center ?');

		// compare PS version
		self::$bCompare149 = version_compare(_PS_VERSION_, '1.4.9', '>=');
		self::$bCompare15 = version_compare(_PS_VERSION_, '1.5', '>=');
		self::$bCompare1550 = version_compare(_PS_VERSION_, '1.5.5.0', '>=');
		self::$bCompare16 = version_compare(_PS_VERSION_, '1.6', '>=');

		// use case - under PS 15
		if (empty(self::$bCompare15)) {
			global $cookie, $smarty;
			self::$oContext = new stdClass();
			self::$oContext->cookie = $cookie;
			self::$oContext->smarty = $smarty;
		}
		// use case - over PS 15
		else {
			self::$oContext = $this->context;
			// get shop id
			self::$iShopId = self::$oContext->shop->id;
		}

		// get current  lang id
		self::$iCurrentLang = self::$oContext->cookie->id_lang;
		// get current lang iso
		self::$sCurrentLang = BT_GmcModuleTools::getLangIso();

		// stock itself obj
		self::$oModule = $this;

		// set base of URI
		self::$sBASE_URI = $this->_path;
		self::$sHost = BT_GmcModuleTools::setHost();

		// get configuration options
		BT_GmcModuleTools::getConfiguration(array('GMERCHANTCENTER_HOME_CAT', 'GMERCHANTCENTER_COLOR_OPT', 'GMERCHANTCENTER_SIZE_OPT', 'GMERCHANTCENTER_SHIP_CARRIERS', 'GMERCHANTCENTER_CHECK_EXPORT'));

		// get available languages
		self::$aAvailableLanguages = BT_GmcModuleTools::getAvailableLanguages(self::$iShopId);

		// get available languages / currencies / countries
		self::$aAvailableLangCurrencyCountry = BT_GmcModuleTools::getLangCurrencyCountry(self::$aAvailableLanguages, $GLOBALS[_GMC_MODULE_NAME . '_AVAILABLE_COUNTRIES']);

		// get call mode - Ajax or dynamic - used for clean headers and footer in ajax request
		self::$sQueryMode = Tools::getValue('sMode');
	}
	
	/**
	 * install() method installs all mandatory structure (DB or Files) => sql queries and update values and hooks registered
	 *
	 * @return bool
	 */
	public function install()
	{
		require_once(_GMC_PATH_CONF . 'install.conf.php');
		require_once(_GMC_PATH_LIB_INSTALL . 'install-ctrl_class.php');

		// set return
		$bReturn = true;

		if (!parent::install()
			|| !BT_InstallCtrl::run('install', 'sql', _GMC_PATH_SQL . _GMC_INSTALL_SQL_FILE)
			|| !BT_InstallCtrl::run('install', 'config', array('bConfigOnly' => true))
		) {
			$bReturn = false;
		}

		if (!empty($bReturn)) {
			// copy output files
			BT_GmcModuleTools::copyOutputFile();
		}

		return $bReturn;
	}
	
	/**
	 * uninstall() method uninstalls all mandatory structure (DB or Files)
	 *
	 * @return bool
	 */
	public function uninstall()
	{
		require_once(_GMC_PATH_CONF . 'install.conf.php');
		require_once(_GMC_PATH_LIB_INSTALL . 'install-ctrl_class.php');
		
		// set return
		$bReturn = true;

		// clean up all generated XML files
		BT_GmcModuleTools::cleanUpFiles();

		if (!parent::uninstall()
			|| !BT_InstallCtrl::run('uninstall', 'sql', _GMC_PATH_SQL . _GMC_UNINSTALL_SQL_FILE)
			|| !BT_InstallCtrl::run('uninstall', 'config')
		) {
			$bReturn = false;
		}

		return $bReturn;
	}

	/**
	 * getContent() method manages all data in Back Office
	 *
	 * @return string
	 */
	public function getContent()
	{
		require_once(_GMC_PATH_CONF . 'admin.conf.php');
		require_once(_GMC_PATH_LIB_ADMIN . 'base-ctrl_class.php');
		require_once(_GMC_PATH_LIB_ADMIN . 'admin-ctrl_class.php');

		try {
			// transverse execution
			self::$sFilePrefix = BT_GmcModuleTools::setXmlFilePrefix();

			// get controller type
			$sControllerType = (!Tools::getIsset(_GMC_PARAM_CTRL_NAME) || (Tools::getIsset(_GMC_PARAM_CTRL_NAME) && 'admin' == Tools::getValue(_GMC_PARAM_CTRL_NAME)))? (Tools::getIsset(_GMC_PARAM_CTRL_NAME)? Tools::getValue(_GMC_PARAM_CTRL_NAME) : 'admin') : Tools::getValue(_GMC_PARAM_CTRL_NAME);

			// instantiate matched controller object
			$oCtrl = BT_GmcBaseCtrl::get($sControllerType);

			// execute good action in admin
			// only displayed with key : tpl and assign in order to display good smarty template
			$aDisplay = $oCtrl->run(array_merge($_GET, $_POST));

			// free memory
			unset($oCtrl);

			if (!empty($aDisplay)) {
				$aDisplay['assign'] = array_merge($aDisplay['assign'], array('oJsTranslatedMsg' => BT_GmcModuleTools::jsonEncode($GLOBALS[_GMC_MODULE_NAME . '_JS_MSG']), 'bAddJsCss' => true));

				// get content
				$sContent = $this->displayModule($aDisplay['tpl'], $aDisplay['assign']);

				if (!empty(self::$sQueryMode)) {
					echo $sContent;
				}
				else {
					return $sContent;
				}
			}
			else {
				throw new Exception('action returns empty content', 110);
			}
		}
		catch (Exception $e) {
//			ppp($e->getTraceAsString());
////			ppp($e->getTrace());
//			ddd($e->getFile() . ' - ' . $e->getMessage() . ' - ' . $e->getLine());
			$this->aErrors[] = array('msg' => $e->getMessage(), 'code' => $e->getCode());

			// get content
			$sContent = $this->displayErrorModule();

			if (!empty(self::$sQueryMode)) {
				echo $sContent;
			}
			else {
				return $sContent;
			}
		}
		// exit clean with XHR mode
		if( !empty(self::$sQueryMode)) {
			exit(0);
		}
	}

	/**
	 * hookHeader() method displays customized module content on header
	 *
	 * @return string
	 */
	public function hookHeader()
	{
		return (
			$this->_execHook('display', 'header')
		);
	}

	/**
	 * hookDisplayHeader() method displays customized module content on header
	 *
	 * @return string
	 */
	public function hookDisplayHeader()
	{
		return (
			$this->_execHook('display', 'header')
		);
	}


	/**
	 * _execHook() method displays selected hook content
	 *
	 * @param string $sHookType
	 * @param array $aParams
	 * @return string
	 */
	private function _execHook($sHookType, $sAction,  array $aParams = null)
	{
		// include
		require_once(_GMC_PATH_CONF . 'hook.conf.php');
		require_once(_GMC_PATH_LIB_HOOK . 'hook-ctrl_class.php');

		// set
		$aDisplay = array();

		try {
			// use cache or not
			if (!empty($aParams['cache'])
				&& !empty($aParams['template'])
				&& !empty($aParams['cacheId'])
				&& !empty(self::$bCompare15)
			) {
				$bUseCache = !$this->isCached($aParams['template'], $this->getCacheId($aParams['cacheId']))? false : true;

				if ($bUseCache) {
					$aDisplay['tpl'] = $aParams['template'];
					$aDisplay['assign'] = array();
				}
			}
			else {
				$bUseCache = false;
			}

			// detect cache or not
			if (!$bUseCache) {
				// define which hook class is executed in order to display good content in good zone in shop
				$oHook = new BT_GmcHookCtrl($sHookType, $sAction);

				// displays good block content
				$aDisplay = $oHook->run($aParams);

				// free memory
				unset($oHook);
			}

			// execute good action in admin
			// only displayed with key : tpl and assign in order to display good smarty template
			if (!empty($aDisplay)) {
				return (
					$this->displayModule($aDisplay['tpl'], $aDisplay['assign'], $bUseCache, (!empty($aParams['cacheId'])? $aParams['cacheId'] : null))
				);
			}
			else {
				throw new Exception('Chosen hook returned empty content', 110);
			}
		}
		catch (Exception $e) {
			$this->aErrors[] = array('msg' => $e->getMessage(), 'code' => $e->getCode());

			return (
				$this->displayErrorModule()
			);
		}
	}


	/**
	 * setErrorHandler() method manages module error
	 *
	 * @param string $sTplName
	 * @param array $aAssign
	 */
	public function setErrorHandler($iErrno, $sErrstr, $sErrFile, $iErrLine, $aErrContext)
	{
		switch ($iErrno) {
			case E_USER_ERROR :
				$this->aErrors[] = array('msg' => 'Fatal error <b>' . $sErrstr . '</b>', 'code' => $iErrno, 'file' => $sErrFile, 'line' => $iErrLine, 'context' => $aErrContext);
				break;
			case E_USER_WARNING :
				$this->aErrors[] = array('msg' => 'Warning <b>' . $sErrstr . '</b>', 'code' => $iErrno, 'file' => $sErrFile, 'line' => $iErrLine, 'context' => $aErrContext);
				break;
			case E_USER_NOTICE :
				$this->aErrors[] = array('msg' => 'Notice <b>' . $sErrstr . '</b>', 'code' => $iErrno, 'file' => $sErrFile, 'line' => $iErrLine, 'context' => $aErrContext);
				break;
			default :
				$this->aErrors[] = array('msg' => 'Unknow error <b>' . $sErrstr . '</b>', 'code' => $iErrno, 'file' => $sErrFile, 'line' => $iErrLine, 'context' => $aErrContext);
				break; 
		}
		return (
			$this->displayErrorModule()
		);
	}

	/**
	 * displayModule() method displays views
	 *
	 * @param string $sTplName
	 * @param array $aAssign
	 * @param bool $bUseCache
	 * @param int $iICacheId
	 * @return string html
	 */
	public function displayModule($sTplName, $aAssign, $bUseCache = false, $iICacheId = null)
	{
		if (file_exists(_GMC_PATH_TPL . $sTplName) && is_file(_GMC_PATH_TPL . $sTplName)) {
			$aAssign = array_merge($aAssign, array('sModuleName' => Tools::strtolower(_GMC_MODULE_NAME), 'bDebug' => _GMC_DEBUG));

			// use cache
			if (!empty(self::$bCompare15) && !empty($bUseCache) && !empty($iICacheId)) {
				return (
					$this->display(__FILE__, $sTplName, $this->getCacheId($iICacheId))
				);
			}
			// not use cache
			else {
				self::$oContext->smarty->assign($aAssign);

				return (
					$this->display(__FILE__, _GMC_PATH_TPL_NAME . $sTplName)
				);
			}
		}
		else {
			throw new Exception('Template "' . $sTplName . '" doesn\'t exists', 120);
		}
	}

	/**
	 * displayErrorModule() method displays view with error
	 *
	 * @param string $sTplName
	 * @param array $aAssign
	 * @return string html
	 */
	public function displayErrorModule()
	{
		self::$oContext->smarty->assign(
			array(
				'sHomeURI'      => BT_GmcModuleTools::truncateUri(),
				'aErrors'       => $this->aErrors,
				'sModuleName'   => Tools::strtolower(_GMC_MODULE_NAME),
				'bDebug'        => _GMC_DEBUG,
			)
		);

		return (
			$this->display(__FILE__, _GMC_PATH_TPL_NAME . _GMC_TPL_ERROR)
		);
	}

	/**
	 * updateModule() method updates module as necessary
	 * @return array
	 */
	public function updateModule()
	{
		require(_GMC_PATH_LIB . 'module-update_class.php');

		// check if update tables
		BT_GmcModuleUpdate::create()->run('tables');

		// check if update fields
		BT_GmcModuleUpdate::create()->run('fields');

		// check if update templates
		BT_GmcModuleUpdate::create()->run('templates');

		// check if update some configuration options
		BT_GmcModuleUpdate::create()->run('configuration', 'languages');
		BT_GmcModuleUpdate::create()->run('configuration', 'color');
		BT_GmcModuleUpdate::create()->run('configuration', 'cronlang');

		$aErrors = BT_GmcModuleUpdate::create()->getErrors();

		// initialize XML files
		BT_GmcModuleUpdate::create()->run('xmlFiles', array('aAvailableData' => GMerchantCenter::$aAvailableLangCurrencyCountry));

		// initialize XML files
		BT_GmcModuleUpdate::create()->run('phpFiles', array());

		if (empty($aErrors)
			&& BT_GmcModuleUpdate::create()->getErrors()
		) {
			BT_GmcWarning::create()->bStopExecution = true;
		}

		return (
			BT_GmcModuleUpdate::create()->getErrors()
		);
	}
}