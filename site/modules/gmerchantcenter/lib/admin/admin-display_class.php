<?php
/**
 * admin-display_class.php file defines method to display content tabs of admin page
 */

class BT_AdminDisplay implements BT_IAdmin
{
	/**
	 * @var array $aFlagIds : array for all flag ids used in option translation
	 */
	protected $aFlagIds = array();

	/**
	 * Magic Method __construct
	 */
	private function __construct()
	{

	}

	/**
	 * Magic Method __destruct
	 */
	public function __destruct()
	{

	}

	/**
	 * run() method display all configured data admin tabs
	 *
	 * @param string $sType => define which method to execute
	 * @param array $aParam
	 * @return array
	 */
	public function run($sType, array $aParam = null)
	{
		// set variables
		$aDisplayData = array();

		if (empty($sType)) {
			$sType = 'tabs';
		}

		switch ($sType) {
			case 'tabs' : // use case - display first page with all tabs
			case 'basics' : // use case - display basics settings page
			case 'feed' : // use case - display feed settings page
			case 'google' : // use case - display google settings page
			case 'googleCategories' : // use case - display google categories settings page
			case 'customLabel' : // use case - display google custom label settings popup
			case 'autocomplete' : // use case - display autocomplete for google categories
			case 'feedList' : // use case - display feed list settings page
			case 'reporting' : // use case - display reporting settings page
			case 'reportingBox' : // use case - display reporting fancybox
			case 'searchProduct' : // use case - handle products autocomplete
				// include
				require_once(_GMC_PATH_LIB . 'module-dao_class.php');

				// execute match function
				$aDisplayData = call_user_func_array(array($this, '_display' . ucfirst($sType)), array($aParam));
				break;
			case 'tag' : // use case - display adult tag settings page
				// include
				require_once(_GMC_PATH_LIB . 'module-dao_class.php');

				// execute match function
				$aDisplayData = call_user_func_array(array($this, '_displayAdvancedTagCategory'), array($aParam));
				break;
			default :
				break;
		}
		// use case - generic assign
		if (!empty($aDisplayData)) {
			$aDisplayData['assign'] = array_merge($aDisplayData['assign'], $this->_assign());
		}

		return (
			$aDisplayData
		);
	}

	/**
	 * _assign() method assigns transverse data
	 *
	 * @return array
	 */
	private function _assign()
	{
		// set smarty variables
		$aAssign = array(
			'sURI' 			    => BT_GmcModuleTools::truncateUri(array('&sAction')),
			'sCtrlParamName' 	=> _GMC_PARAM_CTRL_NAME,
			'sController' 	    => _GMC_ADMIN_CTRL,
			'aQueryParams' 	    => $GLOBALS[_GMC_MODULE_NAME . '_REQUEST_PARAMS'],
			'sDisplay'          => Tools::getValue('sDisplay'),
			'iCurrentLang' 	    => intval(GMerchantCenter::$iCurrentLang),
			'sCurrentLang' 	    => GMerchantCenter::$sCurrentLang,
			'sCurrentIso'		=> Language::getIsoById(GMerchantCenter::$iCurrentLang),
			'sFlagIds' 	        => $this->_getFlagIds(),
			'aFlagIds' 	        => $this->aFlagIds,
			'sTs'				=> time(),
			'bAjaxMode'			=> (GMerchantCenter::$sQueryMode == 'xhr'? true : false),
			'bCompare149'	    => GMerchantCenter::$bCompare149,
			'bCompare15'	    => GMerchantCenter::$bCompare15,
			'bCompare16'	    => GMerchantCenter::$bCompare16,
			'bPsVersion1606'	=> version_compare(_PS_VERSION_, '1.6.0.6', '>='),
			'sLoadingImg'       => _GMC_URL_IMG . 'admin/' . _GMC_LOADER_GIF,
			'sBigLoadingImg'       => _GMC_URL_IMG . 'admin/' . _GMC_LOADER_GIF_BIG,
			'sHeaderInclude'    => BT_GmcModuleTools::getTemplatePath(_GMC_PATH_TPL_NAME . _GMC_TPL_ADMIN_PATH . _GMC_TPL_HEADER),
			'sErrorInclude'     => BT_GmcModuleTools::getTemplatePath(_GMC_PATH_TPL_NAME . _GMC_TPL_ADMIN_PATH . _GMC_TPL_ERROR),
			'sConfirmInclude'   => BT_GmcModuleTools::getTemplatePath(_GMC_PATH_TPL_NAME . _GMC_TPL_ADMIN_PATH . _GMC_TPL_CONFIRM),
		);

		return $aAssign;
	}

	/**
	 * _displayTabs() method displays admin's first page with all tabs
	 *
	 * @param array $aPost
	 * @return array
	 */
	private function _displayTabs(array $aPost = null)
	{
		// set smarty variables
		$aAssign = array(
			'sDocUri'           => _MODULE_DIR_ . _GMC_MODULE_SET_NAME . '/',
			'sDocName'          => 'readme_' . ((GMerchantCenter::$sCurrentLang == 'fr')? 'fr' : 'en') . '.pdf',
			'sContactUs'        => 'http://www.businesstech.fr/' . ((GMerchantCenter::$sCurrentLang == 'fr')? 'fr/contactez-nous' : 'en/contact-us'),
			'sCurrentIso'		=> Language::getIsoById(GMerchantCenter::$iCurrentLang),
		);

		// check warnings
		// PHP script well copied - file exists
		BT_GmcWarning::create()->run('file-exists', _GMC_PATH_ROOT . _GMC_XML_PHP_NAME, _PS_ROOT_DIR_ . '/' . _GMC_XML_PHP_NAME);
		$aAssign['bFileStopExec'] = BT_GmcWarning::create()->bStopExecution;

		// check curl_init and file_get_contents to get the distant Google taxonomy file
		BT_GmcWarning::create()->run('directive', 'allow_url_fopen', array(), true);
		$bTmpStopExec = BT_GmcWarning::create()->bStopExecution;
		BT_GmcWarning::create()->bStopExecution = false;
		BT_GmcWarning::create()->run('function', 'curl_init', array(), true);
		if ($bTmpStopExec && BT_GmcWarning::create()->bStopExecution) {
			$aAssign['bCurlAndContentStopExec'] = true;
		}

		// check if multi-shop configuration
		if (version_compare(_PS_VERSION_, '1.5', '>')
			&& Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE')
			&& strpos(Context::getContext()->cookie->shopContext, 'g-') !== false
		) {
			$aAssign['bMultishopGroupStopExec'] = true;
		}

		// check if shipping weight unit
		$sWeightUnit = Configuration::get('PS_WEIGHT_UNIT');
		if (!empty($sWeightUnit)
			&& !in_array($sWeightUnit, $GLOBALS[_GMC_MODULE_NAME . '_WEIGHT_UNITS'])
		) {
			$aAssign['bWeightUnitStopExec'] = true;
		}

		// check if we hide the config
		if (!empty($aAssign['bFileStopExec'])
			|| !empty($aAssign['bCurlAndContentStopExec'])
			|| !empty($aAssign['bMultishopGroupStopExec'])
			|| !empty($aAssign['bWeightUnitStopExec'])
		) {
			$aAssign['bHideConfiguration'] = true;
		}

		if (!empty(GMerchantCenter::$bCompare15)) {
			$aAssign['autocmp_js'] = __PS_BASE_URI__.'js/jquery/plugins/autocomplete/jquery.autocomplete.js';
			$aAssign['autocmp_css'] = __PS_BASE_URI__.'js/jquery/plugins/autocomplete/jquery.autocomplete.css';
		}
		else {
			$aAssign['autocmp_js'] = __PS_BASE_URI__.'js/jquery/jquery.autocomplete.js';
			$aAssign['autocmp_css'] = __PS_BASE_URI__.'css/jquery.autocomplete.css';
		}

		// use case - get display prerequisites
		$aData = $this->_displayPrerequisites($aPost);

		$aAssign = array_merge($aAssign, $aData['assign']);

		// use case - get display data of basics settings
		$aData = $this->_displayBasics($aPost);

		$aAssign = array_merge($aAssign, $aData['assign']);

		// use case - get display data of feed data settings
		$aData = $this->_displayFeed($aPost);

		$aAssign = array_merge($aAssign, $aData['assign']);

		// use case - get display data of google settings
		$aData = $this->_displayGoogle($aPost);

		$aAssign = array_merge($aAssign, $aData['assign']);

		// use case - get display data of feed list settings
		$aData = $this->_displayFeedList($aPost);

		$aAssign = array_merge($aAssign, $aData['assign']);

		// use case - get display data of feed list settings
		$aData = $this->_displayReporting($aPost);

		$aAssign = array_merge($aAssign, $aData['assign']);

		// assign all included templates files
		$aAssign['sPrerequisitesInclude'] = BT_GmcModuleTools::getTemplatePath(_GMC_PATH_TPL_NAME . _GMC_TPL_ADMIN_PATH . _GMC_TPL_PREREQUISITES);
		$aAssign['sBasicsInclude'] = BT_GmcModuleTools::getTemplatePath(_GMC_PATH_TPL_NAME . _GMC_TPL_ADMIN_PATH . _GMC_TPL_BASICS);
		$aAssign['sFeedInclude'] = BT_GmcModuleTools::getTemplatePath(_GMC_PATH_TPL_NAME . _GMC_TPL_ADMIN_PATH . _GMC_TPL_FEED_SETTINGS);
		$aAssign['sGoogleInclude'] = BT_GmcModuleTools::getTemplatePath(_GMC_PATH_TPL_NAME . _GMC_TPL_ADMIN_PATH . _GMC_TPL_GOOGLE_SETTINGS);
		$aAssign['sFeedListInclude'] = BT_GmcModuleTools::getTemplatePath(_GMC_PATH_TPL_NAME . _GMC_TPL_ADMIN_PATH . _GMC_TPL_FEED_LIST);
		$aAssign['sReportingInclude'] = BT_GmcModuleTools::getTemplatePath(_GMC_PATH_TPL_NAME . _GMC_TPL_ADMIN_PATH . _GMC_TPL_REPORTING);
		$aAssign['sModuleVersion'] = GMerchantCenter::$oModule->version;

		return (
			array(
				'tpl'		=> _GMC_TPL_ADMIN_PATH . _GMC_TPL_BODY,
				'assign'	=> $aAssign,
			)
		);
	}

	/**
	 * _displayPrerequisites() method displays prerequisites
	 *
	 * @param array $aPost
	 * @return array
	 */
	private function _displayPrerequisites(array $aPost = null)
	{
		return (
			array(
				'tpl'	    => _GMC_TPL_ADMIN_PATH . _GMC_TPL_PREREQUISITES,
				'assign'	=> array(),
			)
		);
	}


	/**
	 * _displayBasics() method displays basic settings
	 *
	 * @param array $aPost
	 * @return array
	 */
	private function _displayBasics(array $aPost = null)
	{
		$aAssign = array(
			'sDocUri' => _MODULE_DIR_ . _GMC_MODULE_SET_NAME . '/',
			'sDocName' => 'readme_' . ((GMerchantCenter::$sCurrentLang == 'fr')? 'fr' : 'en') . '.pdf',
			'sLink' => (!empty(GMerchantCenter::$aConfiguration['GMERCHANTCENTER_LINK'])?GMerchantCenter::$aConfiguration['GMERCHANTCENTER_LINK']:GMerchantCenter::$sHost),
			'sPrefixId' => GMerchantCenter::$aConfiguration['GMERCHANTCENTER_ID_PREFIX'],
			'iProductPerCycle' => GMerchantCenter::$aConfiguration['GMERCHANTCENTER_AJAX_CYCLE'],
			'sImgSize' => GMerchantCenter::$aConfiguration['GMERCHANTCENTER_IMG_SIZE'],
			'aHomeCatLanguages' => GMerchantCenter::$aConfiguration['GMERCHANTCENTER_HOME_CAT'],
			'iHomeCatId' => GMerchantCenter::$aConfiguration['GMERCHANTCENTER_HOME_CAT_ID'],
			'bAddCurrency' => GMerchantCenter::$aConfiguration['GMERCHANTCENTER_ADD_CURRENCY'],
			'iAdvancedProductName' => GMerchantCenter::$aConfiguration['GMERCHANTCENTER_ADV_PRODUCT_NAME'],
			'iAdvancedProductTitle' => GMerchantCenter::$aConfiguration['GMERCHANTCENTER_ADV_PROD_TITLE'],
			'bFeedProtection' => GMerchantCenter::$aConfiguration['GMERCHANTCENTER_FEED_PROTECTION'],
			'sFeedToken' => GMerchantCenter::$aConfiguration['GMERCHANTCENTER_FEED_TOKEN'],
			'aImageTypes' => ImageType::getImagesTypes('products'),
			'sCondition' => GMerchantCenter::$aConfiguration['GMERCHANTCENTER_COND'],
			'aAvailableCondition' => BT_GmcModuleTools::getConditionType(),
		);

		$aCategories = Category::getCategories(intval(GMerchantCenter::$iCurrentLang), false);
		$aAssign['aHomeCat'] = BT_GmcModuleTools::recursiveCategoryTree($aCategories, array(), current(current($aCategories)), 1);
		unset($aCategories);

		// get all active languages in order to loop on field form which need to manage translation
		$aAssign['aLangs'] = Language::getLanguages();

		// use case - detect if home category name has been filled
		$aAssign['aHomeCatLanguages'] = $this->_getDefaultTranslations('GMERCHANTCENTER_HOME_CAT', 'HOME_CAT_NAME');

		foreach ($aAssign['aLangs'] as $aLang) {
			if (!isset($aAssign['aHomeCatLanguages'][$aLang['id_lang']])) {
				$aAssign['aHomeCatLanguages'][$aLang['id_lang']] = $GLOBALS[_GMC_MODULE_NAME . '_HOME_CAT_NAME']['en'];
			}
		}

		return (
			array(
				'tpl'	    => _GMC_TPL_ADMIN_PATH . _GMC_TPL_BASICS,
				'assign'	=> $aAssign,
			)
		);
	}


	/**
	 * _displayFeedSettings() method displays feeds settings
	 *
	 * @param array $aPost
	 * @return array
	 */
	private function _displayFeed(array $aPost = null)
	{
		if (GMerchantCenter::$sQueryMode == 'xhr') {
			// clean headers
			@ob_end_clean();
		}

		$aAssign = array(
			'bExportMode' => GMerchantCenter::$aConfiguration['GMERCHANTCENTER_EXPORT_MODE'],
			'bExportOOS' => GMerchantCenter::$aConfiguration['GMERCHANTCENTER_EXPORT_OOS'],
			'bExcludeNoEan' => GMerchantCenter::$aConfiguration['GMERCHANTCENTER_EXC_NO_EAN'],
			'bExcludeNoMref' => GMerchantCenter::$aConfiguration['GMERCHANTCENTER_EXC_NO_MREF'],
			'iMinPrice' => GMerchantCenter::$aConfiguration['GMERCHANTCENTER_MIN_PRICE'],
			'sDefaultCurrency' => BT_GmcModuleTools::getCurrency('sign'),
			'bProductCombos' => GMerchantCenter::$aConfiguration['GMERCHANTCENTER_P_COMBOS'],
			'iDescType' => GMerchantCenter::$aConfiguration['GMERCHANTCENTER_P_DESCR_TYPE'],
			'aDescriptionType' => BT_GmcModuleTools::getDescriptionType(),
			'iIncludeStock' => GMerchantCenter::$aConfiguration['GMERCHANTCENTER_INC_STOCK'],
			'bIncludeTagAdult' => GMerchantCenter::$aConfiguration['GMERCHANTCENTER_INC_TAG_ADULT'],
			'bIncludeSize' => GMerchantCenter::$aConfiguration['GMERCHANTCENTER_INC_SIZE'],
			'aAttributeGroups' => AttributeGroup::getAttributesGroups((int)GMerchantCenter::$oContext->cookie->id_lang),
			'aFeatures' => Feature::getFeatures((int)GMerchantCenter::$oContext->cookie->id_lang),
			'aSizeOptions' => GMerchantCenter::$aConfiguration['GMERCHANTCENTER_SIZE_OPT'],
			'sIncludeColor' => GMerchantCenter::$aConfiguration['GMERCHANTCENTER_INC_COLOR'],
			'aExcludedProducts' => GMerchantCenter::$aConfiguration['GMERCHANTCENTER_PROD_EXCL'],
			'bIncludeMaterial' => GMerchantCenter::$aConfiguration['GMERCHANTCENTER_INC_MATER'],
			'bIncludePattern' => GMerchantCenter::$aConfiguration['GMERCHANTCENTER_INC_PATT'],
			'bIncludeGender' => GMerchantCenter::$aConfiguration['GMERCHANTCENTER_INC_GEND'],
			'bIncludeAge' => GMerchantCenter::$aConfiguration['GMERCHANTCENTER_INC_AGE'],
			'bShippingUse' => GMerchantCenter::$aConfiguration['GMERCHANTCENTER_SHIPPING_USE'],
			'sGtinPreference' => GMerchantCenter::$aConfiguration['GMERCHANTCENTER_GTIN_PREF'],
			'aShippingCarriers' => array(),
		);

		// handle product IDs and Names list to format them for the autocomplete feature
		if (!empty($aAssign['aExcludedProducts'])) {
			$sProdIds = '';
			$sProdNames = '';

			foreach ($aAssign['aExcludedProducts'] as $iKey => $sProdId) {
				$aProdIds = explode('¤', $sProdId);
				$oProduct = new Product($aProdIds[0], false, GMerchantCenter::$iCurrentLang);

				// check if we export with combinations
				if (!empty($aProdIds[1])) {
					$oProduct->name .= BT_GmcModuleTools::getProductCombinationName($aProdIds[1], GMerchantCenter::$iCurrentLang, GMerchantCenter::$iShopId);
				}

				$sProdIds .= $sProdId . '-';
				$sProdNames .= $oProduct->name . '||';

				$aAssign['aProducts'][] = array('id' => $sProdId, 'name' => $oProduct->name, 'attrId' => $aProdIds[1], 'stringIds' => $sProdId);
				unset($oProduct);
			}
			$aAssign['sProductIds'] = $sProdIds;
			$aAssign['sProductNames'] = str_replace('"', '', $sProdNames);
			unset($sProdIds);
			unset($sProdNames);
		}

		$aAssign['aColorOptions']['attribute'] = !empty(GMerchantCenter::$aConfiguration['GMERCHANTCENTER_COLOR_OPT']['attribute'])? GMerchantCenter::$aConfiguration['GMERCHANTCENTER_COLOR_OPT']['attribute'] : array(0);
		$aAssign['aColorOptions']['feature'] = !empty(GMerchantCenter::$aConfiguration['GMERCHANTCENTER_COLOR_OPT']['feature'])? GMerchantCenter::$aConfiguration['GMERCHANTCENTER_COLOR_OPT']['feature'] : array(0);

		// get available categories and manufacturers
		$aCategories = Category::getCategories(intval(GMerchantCenter::$iCurrentLang), false);
		$aBrands = Manufacturer::getManufacturers();

		$aStartCategories = current($aCategories);
		$aFirst = current($aStartCategories);
		$iStart = 1;

		// get registered categories and brands
		$aIndexedCategories = array();
		$aIndexedBrands = array();

		// use case - get categories or brands according to the export mode
		if (GMerchantCenter::$aConfiguration['GMERCHANTCENTER_EXPORT_MODE'] == 1) {
			$aIndexedBrands = BT_GmcModuleDao::getGmcBrands(GMerchantCenter::$iShopId);
		}
		else {
			$aIndexedCategories = BT_GmcModuleDao::getGmcCategories(GMerchantCenter::$iShopId);
		}

		// format categories and brands
		$aAssign['aFormatCat'] = BT_GmcModuleTools::recursiveCategoryTree($aCategories, $aIndexedCategories, $aFirst, $iStart, null, true);
		$aAssign['aFormatBrands'] = BT_GmcModuleTools::recursiveBrandTree($aBrands, $aIndexedBrands, $aFirst, $iStart);

		$aAssign['iShopCatCount'] = count($aAssign['aFormatCat']);
		$aAssign['iMaxPostVars'] = ini_get('max_input_vars');
//		$aAssign['iMaxPostVars'] = 3;

		unset($aIndexedCategories);
		unset($aIndexedBrands);
		unset($aCategories);
		unset($aBrands);

		// handle tax and shipping fees
		foreach ($GLOBALS[_GMC_MODULE_NAME . '_AVAILABLE_COUNTRIES'] as $sLang => $aCountries) {
			if (BT_GmcModuleDao::checkActiveLanguage($sLang)) {
				foreach ($aCountries as $sCountry => $aLocaleData) {
					$iCountryId = Country::getByIso($sCountry);
					if (!empty($iCountryId)) {
						$iCountryZone = Country::getIdZone($iCountryId);
						if (!empty($iCountryZone)) {
							$aCarriers = BT_GmcModuleDao::getAvailableCarriers((int)$iCountryZone);
							if (Currency::getIdByIsoCode($aLocaleData['currency'])) {
								if (!empty($aCarriers) && Currency::getIdByIsoCode($aLocaleData['currency'])) {
									if (!array_key_exists($sCountry, $aAssign['aShippingCarriers'])) {
										$aAssign['aShippingCarriers'][$sCountry] = array(
											'name' => $sCountry,
											'carriers' => $aCarriers,
											'shippingCarrierId' => (!empty(GMerchantCenter::$aConfiguration['GMERCHANTCENTER_SHIP_CARRIERS'][$sCountry]) ? GMerchantCenter::$aConfiguration['GMERCHANTCENTER_SHIP_CARRIERS'][$sCountry] : 0),
										);
									}
								}
							}
						}
					}
				}
			}
		}

		return (
			array(
				'tpl'	    => _GMC_TPL_ADMIN_PATH . _GMC_TPL_FEED_SETTINGS,
				'assign'	=> $aAssign,
			)
		);
	}


	/**
	 * _displayGoogle() method displays Google settings
	 *
	 * @param array $aPost
	 * @return array
	 */
	private function _displayGoogle(array $aPost = null)
	{
		if (GMerchantCenter::$sQueryMode == 'xhr') {
			// clean headers
			@ob_end_clean();
		}

		$aAssign = array(
			'aCountryTaxonomies' => BT_GmcModuleDao::getAvailableTaxonomyCountries($GLOBALS[_GMC_MODULE_NAME . '_AVAILABLE_COUNTRIES']),
			'sGoogleCatListInclude' => BT_GmcModuleTools::getTemplatePath(_GMC_PATH_TPL_NAME . _GMC_TPL_ADMIN_PATH . _GMC_TPL_GOOGLE_CATEGORY_LIST),
			'aTags' => BT_GmcModuleDao::getGmcTags(GMerchantCenter::$iShopId),
			'sUtmCampaign' => GMerchantCenter::$aConfiguration['GMERCHANTCENTER_UTM_CAMPAIGN'],
			'sUtmSource' => GMerchantCenter::$aConfiguration['GMERCHANTCENTER_UTM_SOURCE'],
			'sUtmMedium' => GMerchantCenter::$aConfiguration['GMERCHANTCENTER_UTM_MEDIUM'],
		);

		foreach ($aAssign['aCountryTaxonomies'] as $sIsoCode => &$aTaxonomy) {
			$aTaxonomy['countryList'] = implode(', ', $aTaxonomy['countries']);
			$aTaxonomy['updated'] = BT_GmcModuleDao::checkTaxonomyUpdate($sIsoCode);
		}

		return (
			array(
				'tpl'	    => _GMC_TPL_ADMIN_PATH . _GMC_TPL_GOOGLE_SETTINGS,
				'assign'	=> $aAssign,
			)
		);
	}

	/**
	 * _displayGoogleCategories() method displays Fancybox Google categories
	 *
	 * @param array $aPost
	 * @return array
	 */
	private function _displayGoogleCategories(array $aPost = null)
	{
		// clean headers
		@ob_end_clean();

		$aAssign = array(
			'iLangId' => Tools::getValue('iLangId'),
			'sLangIso' => Tools::getValue('sLangIso'),
			'sCurrentIso' => Language::getIsoById(GMerchantCenter::$iCurrentLang),
		);
		// get shop categories
		$aShopCategories = BT_GmcModuleDao::getShopCategories(GMerchantCenter::$iShopId, $aAssign['iLangId'], GMerchantCenter::$aConfiguration['GMERCHANTCENTER_HOME_CAT_ID']);

		foreach ($aShopCategories as &$aCategory) {
			// get google taxonomy
			$aGoogleCat = BT_GmcModuleDao::getGoogleCategories(GMerchantCenter::$iShopId, $aCategory['id_category'], $aAssign['sLangIso']);
			// assign the current taxonomy
			$aCategory['google_category_name'] = is_array($aGoogleCat) && isset($aGoogleCat['txt_taxonomy'])? $aGoogleCat['txt_taxonomy'] : '';
		}

		$aAssign['aShopCategories'] = $aShopCategories;
		$aAssign['iShopCatCount'] = count($aShopCategories);
		$aAssign['iMaxPostVars'] = ini_get('max_input_vars');
//		$aAssign['iMaxPostVars'] = 3;
		
		// force xhr mode
		GMerchantCenter::$sQueryMode = 'xhr';

		return (
			array(
				'tpl'	    => _GMC_TPL_ADMIN_PATH . _GMC_TPL_GOOGLE_CATEGORY_POPUP,
				'assign'	=> $aAssign,
			)
		);
	}


	/**
	 * _displayAutocomplete() method displays autocomplete google categories
	 *
	 * @param array $aPost
	 * @return array
	 */
	private function _displayAutocomplete(array $aPost = null)
	{
		// clean headers
		@ob_end_clean();

		// set
		$sOutput = '';

		$sLangIso = Tools::getValue('sLangIso');
		$sQuery = Tools::getValue('q');

		// explode query string
		$aWords = explode(' ', $sQuery);

		// get matching query
		$aItems = BT_GmcModuleDao::autocompleteSearch($sLangIso, $aWords);

		if (!empty($aItems)
			&& is_array($aItems)
		) {
			foreach ($aItems AS $aItem) {
				$sOutput .= trim($aItem['value']) . "\n";
			}
		}
		echo $sOutput;
		exit(0);
	}

	/**
	 * _displayCustomLabel() method displays custom labels
	 *
	 * @param array $aPost
	 * @return array
	 */
	private function _displayCustomLabel(array $aPost = null)
	{
		// clean headers
		@ob_end_clean();

		$aAssign = array();

		// get available categories and manufacturers
		$aCategories = Category::getCategories(intval(GMerchantCenter::$iCurrentLang), false);
		$aBrands = Manufacturer::getManufacturers();
		$aSuppliers = Supplier::getSuppliers();

		$aStartCategories = current($aCategories);
		$aFirst = current($aStartCategories);
		$iStart = 1;

		// get registered categories and brands and suppliers
		$aIndexedCategories = array();
		$aIndexedBrands = array();
		$aIndexedSuppliers = array();

		// use case - get categories or brands or suppliers according to the id tag
		$iTagId = Tools::getValue('iTagId');
		$aTag = array();
		if (!empty($iTagId)) {
			$aTag = BT_GmcModuleDao::getGmcTags(GMerchantCenter::$iShopId, $iTagId);
			$aIndexedCategories = BT_GmcModuleDao::getGmcTags(null, $iTagId, 'cats', 'category');
			$aIndexedBrands = BT_GmcModuleDao::getGmcTags(null, $iTagId, 'brands', 'brand');
			$aIndexedSuppliers = BT_GmcModuleDao::getGmcTags(null, $iTagId, 'suppliers', 'supplier');
		}

		// format categories and brands and suppliers
		$aAssign['aTag'] = (count($aTag) == 1 && isset($aTag[0]))? $aTag[0] : $aTag;
		$aAssign['aFormatCat'] = BT_GmcModuleTools::recursiveCategoryTree($aCategories, $aIndexedCategories, $aFirst, $iStart);
		$aAssign['aFormatBrands'] = BT_GmcModuleTools::recursiveBrandTree($aBrands, $aIndexedBrands, $aFirst, $iStart);
		$aAssign['aFormatSuppliers'] = BT_GmcModuleTools::recursiveSupplierTree($aSuppliers, $aIndexedSuppliers, $aFirst, $iStart);
		$aAssign['iShopCatCount'] = count($aAssign['aFormatCat']);
		$aAssign['iMaxPostVars'] = ini_get('max_input_vars');
//		$aAssign['iMaxPostVars'] = 3;

		unset($aTag);
		unset($aIndexedCategories);
		unset($aIndexedBrands);
		unset($aIndexedSuppliers);
		unset($aCategories);
		unset($aBrands);
		unset($aSuppliers);

		// force xhr mode
		GMerchantCenter::$sQueryMode = 'xhr';

		return (
			array(
				'tpl'	    => _GMC_TPL_ADMIN_PATH . _GMC_TPL_GOOGLE_CUSTOM_LABEL,
				'assign'	=> $aAssign,
			)
		);
	}


	/**
	 * _displayFeedList() method displays feed list
	 *
	 * @param array $aPost
	 * @return array
	 */
	private function _displayFeedList(array $aPost = null)
	{
		if (GMerchantCenter::$sQueryMode == 'xhr') {
			// clean headers
			@ob_end_clean();
		}

		$aAssign = array(
			'iShopId' => GMerchantCenter::$iShopId,
			'sGmcLink' => GMerchantCenter::$aConfiguration['GMERCHANTCENTER_LINK'],
			'bReporting' => GMerchantCenter::$aConfiguration['GMERCHANTCENTER_REPORTING'],
			'iTotalProductToExport' => BT_GmcModuleDao::getProductIds(GMerchantCenter::$iShopId, (int)GMerchantCenter::$aConfiguration['GMERCHANTCENTER_EXPORT_MODE'], true),
			'iTotalProduct' => BT_GmcModuleDao::countProducts(GMerchantCenter::$iShopId, (int)GMerchantCenter::$aConfiguration['GMERCHANTCENTER_P_COMBOS']),
			'bCheckOutputFile' => BT_GmcModuleTools::checkOutputFile(),
			'aFeedFileList' => array(),
			'aFlyFileList' => array(),
		);
		$aAssign['aCronLang'] = (!empty(GMerchantCenter::$aConfiguration['GMERCHANTCENTER_CHECK_EXPORT'])? GMerchantCenter::$aConfiguration['GMERCHANTCENTER_CHECK_EXPORT'] : array());

//		$aAssign['iTotalProductToExport'] = 60;
//		$aAssign['iTotalProduct'] = 100;

		// handle data feed file name
		if (!empty($aAssign['sGmcLink'])) {
			if (!empty(GMerchantCenter::$aAvailableLangCurrencyCountry)) {
				foreach (GMerchantCenter::$aAvailableLangCurrencyCountry as $aData) {
					// check if file exist
					$sFileSuffix = BT_GmcModuleTools::buildFileSuffix($aData['langIso'], $aData['countryIso']);
					$sFilePath = GMerchantCenter::$sFilePrefix . '.' . $sFileSuffix . '.xml';

					if (is_file(_GMC_SHOP_PATH_ROOT . $sFilePath)) {
						$aAssign['aFeedFileList'][] = array(
							'link' => $aAssign['sGmcLink'] . __PS_BASE_URI__ . $sFilePath,
							'filename' => $sFilePath,
							'filemtime' => date("d-m-Y H:i:s", filemtime(_GMC_SHOP_PATH_ROOT . $sFilePath)),
							'checked' => (in_array($aData['langIso'] . '_' . $aData['countryIso'], $aAssign['aCronLang']) ? true : false),
							'country' => $aData['countryIso'],
							'lang' => $aData['langIso'],
							'langId' => $aData['langId'],
						);
					}
				}
			}

			// handle on-the-fly output
			if (!empty($aAssign['bCheckOutputFile'])) {
				if (!empty(GMerchantCenter::$aAvailableLangCurrencyCountry)) {
					foreach (GMerchantCenter::$aAvailableLangCurrencyCountry as $aData) {
						$sLink = $aAssign['sGmcLink'] . __PS_BASE_URI__ . 'gmerchantcenter.xml.php?id_shop='.GMerchantCenter::$iShopId.'&id_lang='.(int)$aData['langId'].'&country='.$aData['countryIso'].'&currency_iso='.$aData['currencyIso'];
						$sLink .= (!empty(GMerchantCenter::$aConfiguration['GMERCHANTCENTER_FEED_PROTECTION'])? '&token=' . GMerchantCenter::$aConfiguration['GMERCHANTCENTER_FEED_TOKEN'] : '');

						$aAssign['aFlyFileList'][] = array(
							'link' => $sLink,
							'country' => $aData['countryIso'],
							'iso_code' => $aData['langIso'],
						);
					}
				}
			}
			// handle the cron URL
			$aAssign['sCronUrl'] = $aAssign['sGmcLink'] . _GMC_MODULE_URL . 'cron.php?id_shop=' . GMerchantCenter::$iShopId;

			// check if the feed protection is activated
			if (!empty(GMerchantCenter::$aConfiguration['GMERCHANTCENTER_FEED_PROTECTION'])
				&& !empty(GMerchantCenter::$aConfiguration['GMERCHANTCENTER_FEED_TOKEN'])
			) {
				$aAssign['sCronUrl'] .= '&token=' . GMerchantCenter::$aConfiguration['GMERCHANTCENTER_FEED_TOKEN'];
			}
		}

		return (
			array(
				'tpl'	    => _GMC_TPL_ADMIN_PATH . _GMC_TPL_FEED_LIST,
				'assign'	=> $aAssign,
			)
		);
	}

	/**
	 * _displayReporting() method displays reporting settings
	 *
	 * @param array $aPost
	 * @return array
	 */
	private function _displayReporting(array $aPost = null)
	{
		$aAssign = array(
			'aLangCurrencies' => BT_GmcModuleTools::getGeneratedReport(),
			'bReporting' => GMerchantCenter::$aConfiguration['GMERCHANTCENTER_REPORTING'],
		);

		return (
			array(
				'tpl'	    => _GMC_TPL_ADMIN_PATH . _GMC_TPL_REPORTING,
				'assign'	=> $aAssign,
			)
		);
	}


	/**
	 * _displayReporting() method displays reporting fancybox
	 *
	 * @param array $aPost
	 * @return array
	 */
	private function _displayReportingBox(array $aPost = null)
	{
		// clean headers
		@ob_end_clean();

		$aAssign = array();
		$aTmp = array();

		// get the current lang ID
		$sLang = Tools::getValue('lang');
		$iProductCount = Tools::getValue('count');

		if (!empty($sLang)
			&& strstr($sLang, '_')
		) {
			list($sLangIso, $sCountryIso) = explode('_', $sLang);

			// get the identify lang ID
			$iLangId = BT_GmcModuleTools::getLangId($sLangIso);

			// include
			require_once(_GMC_PATH_LIB . 'module-reporting_class.php');

			// set reporting object
			BT_GmcReporting::create(true)->setFileName(_GMC_REPORTING_DIR . 'reporting-' . $sLangIso . '-' . Tools::strtolower($sCountryIso) . '.txt');

			// get the current report
			$aReporting = BT_GmcReporting::create()->get();

			if (!empty($aReporting)) {

				static $aTmpProduct = array();

				// get the language name
				$aLanguage = Language::getLanguage($iLangId);
				$sLanguageName = $aLanguage['name'];
				// get the country name
				$sCountryName = Country::getNameById($iLangId, Country::getByIso(Tools::strtolower($sCountryIso)));
				unset($aLanguage);

				// check if exists counter key in the reporting
				if (!empty($aReporting['counter'][0])) {
					if (empty($iProductCount)) {
						$iProductCount = $aReporting['counter'][0]['products'];
					}
					unset($aReporting['counter']);
				}

				// load google tags
				$aGoogleTags = BT_GmcModuleTools::loadGoogleTags();

				foreach ($aReporting as $sTagName => &$aGTag) {
					$aTmp[$aGoogleTags[$sTagName]['type']][$sTagName]['count']       = count($aGTag);
					$aTmp[$aGoogleTags[$sTagName]['type']][$sTagName]['label']       = (isset($aGoogleTags[$sTagName])? $aGoogleTags[$sTagName]['label'] : '');
					$aTmp[$aGoogleTags[$sTagName]['type']][$sTagName]['msg']         = (isset($aGoogleTags[$sTagName])? $aGoogleTags[$sTagName]['msg'] : '');
					$aTmp[$aGoogleTags[$sTagName]['type']][$sTagName]['faq_id']      = (isset($aGoogleTags[$sTagName])? (int)($aGoogleTags[$sTagName]['faq_id']) : 0);
					$aTmp[$aGoogleTags[$sTagName]['type']][$sTagName]['anchor']      = (isset($aGoogleTags[$sTagName])? $aGoogleTags[$sTagName]['anchor'] : '');
					$aTmp[$aGoogleTags[$sTagName]['type']][$sTagName]['mandatory']   = (isset($aGoogleTags[$sTagName])? $aGoogleTags[$sTagName]['mandatory'] : false);

					// detect the old format system and the new format
					if (isset($aGTag[0]['productId'])
						&& strstr($aGTag[0]['productId'], '_')
					) {
						foreach ($aGTag as $iKey => &$aProdValue) {
							list($iProdId, $iAttributeId) = explode('_', $aProdValue['productId']);
							if (empty($aTmpProduct[$aProdValue['productId']])) {
								// get the product obj
								$oProduct = new Product((int)$iProdId, true, (int)$iLangId);
								$oCategory = new Category((int)($oProduct->id_category_default), (int)$iLangId);

								// set the product URL
								$aProdValue['productUrl'] = BT_GmcModuleTools::getProductLink($oProduct, $iLangId, $oCategory->link_rewrite);
								// set the product name
								$aProdValue['productName'] = $oProduct->name;

								// if combination
								if (!empty($iAttributeId)) {
									if (!empty(GMerchantCenter::$bCompare15)){
										$aProdValue['productUrl'] = BT_GmcModuleDao::getProductComboLink($aProdValue['productUrl'], $iAttributeId, $iLangId, GMerchantCenter::$iShopId);
									}

									// get the combination attributes to format the product name
									$aCombinationAttr = BT_GmcModuleDao::getProductComboAttributes($iAttributeId, $iLangId, GMerchantCenter::$iShopId);

									if (!empty($aCombinationAttr)) {
										$sExtraName = '';
										foreach ($aCombinationAttr as $c) {
											$sExtraName .= ' '.Tools::stripslashes($c['name']);
										}
										$aProdValue['productName'] .= $sExtraName;
									}
								}
								unset($oProduct);
								unset($oCategory);

								$aTmpProduct[$aProdValue['productId']] = array(
									'productId' => $iProdId,
									'productAttrId' => $iAttributeId,
									'productUrl' => $aProdValue['productUrl'],
									'productName' => $aProdValue['productName'],
								);
							}
							$aProdValue = $aTmpProduct[$aProdValue['productId']];
						}
					}
					$aTmp[$aGoogleTags[$sTagName]['type']][$sTagName]['data'] = $aGTag;
				}
				$aTmpProduct = array();
				ksort($aTmp);
				unset($aReporting);
				unset($aGoogleTags);

				$aAssign = array(
					'sLangName'   => $sLanguageName,
					'sCountryName'   => $sCountryName,
					'aReport'   => $aTmp,
					'iProductCount'   => (int)$iProductCount,
					'sPath'     => _GMC_PATH_ROOT,
					'sFaqURL'	=> _GMC_BT_FAQ_MAIN_URL . 'faq.php?id=',
					'sFaqLang'	=> $sLangIso,
				);
			}
			else {
				$aAssign['aErrors'][] = array('msg' => GMerchantCenter::$oModule->l('There isn\'t any report for this language and country', 'admin-display_class.php') . ' : ' . $sLangIso . ' - ' . $sCountryIso , 'code' => 190);
			}
		}
		else {
			$aAssign['aErrors'][] = array('msg' => GMerchantCenter::$oModule->l('Language ISO and country ISO aren\'t well formatted', 'admin-display_class.php'), 'code' => 191);
		}

		// force xhr mode
		GMerchantCenter::$sQueryMode = 'xhr';

		return (
			array(
				'tpl'	    => _GMC_TPL_ADMIN_PATH . _GMC_TPL_REPORTING_BOX,
				'assign'	=> $aAssign,
			)
		);
	}


	/**
	 * _displayAdvancedTagCategory() method displays advanced tag category settings
	 *
	 * @param array $aPost
	 * @return array
	 */
	private function _displayAdvancedTagCategory(array $aPost = null)
	{
		// clean headers
		@ob_end_clean();

		$aShopCategories = BT_GmcModuleDao::getShopCategories(GMerchantCenter::$iShopId, GMerchantCenter::$iCurrentLang, GMerchantCenter::$aConfiguration['GMERCHANTCENTER_HOME_CAT_ID']);

		foreach ($aShopCategories as &$aCat) {
			// get feature by category Id
			$aFeatures = BT_GmcModuleDao::getFeaturesByCategory($aCat['id_category']);

			if (!empty($aFeatures)) {
				$aCat['material'] = $aFeatures['material'];
				$aCat['pattern'] = $aFeatures['pattern'];
				$aCat['agegroup'] = $aFeatures['agegroup'];
				$aCat['gender'] = $aFeatures['gender'];
				$aCat['adult'] = $aFeatures['adult'];
			}
			else {
				$aCat['material'] = '';
				$aCat['pattern'] = '';
				$aCat['agegroup'] = '';
				$aCat['gender'] = '';
				$aCat['adult'] = '';
			}
		}

		$aAssign = array(
			'bCompare149' => GMerchantCenter::$bCompare149,
			'aShopCategories' => $aShopCategories,
			'aFeatures' => Feature::getFeatures(GMerchantCenter::$iCurrentLang),
			'sUseTag' => Tools::getValue('sUseTag'),
			'bMaterial' => GMerchantCenter::$aConfiguration['GMERCHANTCENTER_INC_MATER'],
			'bPattern' => GMerchantCenter::$aConfiguration['GMERCHANTCENTER_INC_PATT'],
			'bGender' => GMerchantCenter::$aConfiguration['GMERCHANTCENTER_INC_GEND'],
			'bAgeGroup' => GMerchantCenter::$aConfiguration['GMERCHANTCENTER_INC_AGE'],
			'bTagAdult' => GMerchantCenter::$aConfiguration['GMERCHANTCENTER_INC_TAG_ADULT'],
		);
		unset($aShopCategories);

		// force xhr mode
		GMerchantCenter::$sQueryMode = 'xhr';

		return (
			array(
				'tpl'	    => _GMC_TPL_ADMIN_PATH . _GMC_TPL_ADVANCED_TAG_CATEGORY,
				'assign'	=> $aAssign,
			)
		);
	}


	/**
	 * _displaySearchProduct() method displays search product name for autocomplete
	 *
	 * @param array $aPost
	 * @return array
	 */
	private function _displaySearchProduct(array $aPost = null)
	{
		// clean headers
		@ob_end_clean();

		// set
		$sOutput = '';

		// get the query to search
		$sSearch = Tools::getValue('q');

		if (!empty($sSearch)) {
			$aMatchingProducts = BT_GmcModuleDao::searchProducts($sSearch, (int)GMerchantCenter::$aConfiguration['GMERCHANTCENTER_P_COMBOS']);

			if (!empty($aMatchingProducts)) {
				foreach ($aMatchingProducts as $aProduct) {
					// check if we export with combinations
					if (!empty($aProduct['id_product_attribute'])) {
						$aProduct['name'] .= BT_GmcModuleTools::getProductCombinationName($aProduct['id_product_attribute'], GMerchantCenter::$iCurrentLang, GMerchantCenter::$iShopId);
					}
					$sOutput .= trim($aProduct['name']) . '|' . (int)$aProduct['id_product'] . '|' . (!empty($aProduct['id_product_attribute'])? $aProduct['id_product_attribute'] : '0') .  "\n";
				}
			}
		}

		// force xhr mode
		GMerchantCenter::$sQueryMode = 'xhr';

		return (
			array(
				'tpl'	    => _GMC_TPL_ADMIN_PATH . _GMC_TPL_PROD_SEARCH,
				'assign'	=> array('json' => $sOutput),
			)
		);
	}

	/**
	 * _getDefaultTranslations() method returns the matching requested translations
	 *
	 * @param string $sSerializedVar
	 * @param string $sGlobalVar
	 * @return array
	 */
	private function _getDefaultTranslations($sSerializedVar, $sGlobalVar)
	{
		$aTranslations = array();

		if (!empty(GMerchantCenter::$aConfiguration[strtoupper($sSerializedVar)])) {
			$aTranslations =  is_string(GMerchantCenter::$aConfiguration[strtoupper($sSerializedVar)])? unserialize(GMerchantCenter::$aConfiguration[strtoupper($sSerializedVar)]) : GMerchantCenter::$aConfiguration[strtoupper($sSerializedVar)];
		}
		else {
			foreach ($GLOBALS[_GMC_MODULE_NAME . '_' . strtoupper($sGlobalVar)] as $sIsoCode => $sTranslation) {
				$iLangId = BT_GmcModuleTools::getLangId($sIsoCode);

				if ($iLangId) {
					// get Id by iso
					$aTranslations[$iLangId] = $sTranslation;
				}
			}
		}

		return $aTranslations;
	}


	/**
	 * _getFlagIds() method returns ids used for PrestaShop flags displaying
	 *
	 * @return string
	 */
	private function _getFlagIds()
	{
		// set
		$sFlagIds = '';

		if (!empty($this->aFlagIds)) {
			// loop on each ids
			foreach ($this->aFlagIds as $sId) {
				$sFlagIds .= $sId . '¤';
			}

			$sFlagIds = substr($sFlagIds, 0, (strlen($sFlagIds) - 2));
		}

		return $sFlagIds;
	}

	/**
	 * _setFlagIds() method sets ids used for PrestaShop flags displaying
	 */
	private function _setFlagIds()
	{
		// set
		$sFlagIds = '';

		$this->aFlagIds = array(
			strtolower(_GMC_MODULE_NAME) . 'Title',
		);
	}

	/**
	 * create() method set singleton
	 *
	 * @return obj
	 */
	public static function create()
	{
		static $oDisplay;

		if (null === $oDisplay) {
			$oDisplay = new BT_AdminDisplay();
		}
		return $oDisplay;
	}
}