<?php
/**
 * module-tools_class.php file defines all tools method in module - transverse
 */

class BT_GmcModuleTools
{
	/**
	 * translateJsMsg() method returns good translated errors
	 */
	public static function translateJsMsg()
	{
		$GLOBALS[_GMC_MODULE_NAME . '_JS_MSG']['link'] = GMerchantCenter::$oModule->l('You have not filled out the shop URL option', 'module-tools_class');
		$GLOBALS[_GMC_MODULE_NAME . '_JS_MSG']['token'] = GMerchantCenter::$oModule->l('Field is required or Token must be 32 characters', 'module-tools_class');
		$GLOBALS[_GMC_MODULE_NAME . '_JS_MSG']['customlabel'] = GMerchantCenter::$oModule->l('You have not filled out the custom label', 'module-tools_class');
		$GLOBALS[_GMC_MODULE_NAME . '_JS_MSG']['category'] = GMerchantCenter::$oModule->l('You did not select any category to export', 'module-tools_class');
		$GLOBALS[_GMC_MODULE_NAME . '_JS_MSG']['brand'] = GMerchantCenter::$oModule->l('You did not select any brand to export', 'module-tools_class');
		$GLOBALS[_GMC_MODULE_NAME . '_JS_MSG']['color'] = GMerchantCenter::$oModule->l('You did not select any attribute or feature to fit to your color tag', 'module-tools_class');

		foreach (Language::getLanguages() as $aLang) {
			$GLOBALS[_GMC_MODULE_NAME . '_JS_MSG']['homecat'][$aLang['id_lang']] = GMerchantCenter::$oModule->l('You have not filled out your home category name with language', 'module-tools_class')
				. ' ' . $aLang['name'] . '. ' . GMerchantCenter::$oModule->l('Click on the drop-down flag list in order to fill out the correct language field(s).', 'module-tools_class');
		}
	}

	/**
	 * updateConfiguration() method update new keys in new module version
	 */
	public static function updateConfiguration()
	{
		// check to update new module version
		foreach ($GLOBALS[_GMC_MODULE_NAME . '_CONFIGURATION'] as $sKey => $mVal) {
			// use case - not exists
			if (Configuration::get($sKey) === false) {
				// update key/ value
				Configuration::updateValue($sKey, $mVal);
			}
		}
	}

	/**
	 * getConfiguration() method set all constant module in ps_configuration
	 *
	 * @param array $aOptionListToUnserialize
	 * @param int $iShopId
	 */
	public static function getConfiguration(array $aOptionListToUnserialize = null, $iShopId = null)
	{
		// get configuration options
		if (null !== $iShopId && is_numeric($iShopId)) {
			GMerchantCenter::$aConfiguration = Configuration::getMultiple(array_keys($GLOBALS[_GMC_MODULE_NAME . '_CONFIGURATION']), null, null, $iShopId);
		}
		else {
			GMerchantCenter::$aConfiguration = Configuration::getMultiple(array_keys($GLOBALS[_GMC_MODULE_NAME . '_CONFIGURATION']));
		}
		if (!empty($aOptionListToUnserialize)
			&& is_array($aOptionListToUnserialize)
		) {
			foreach ($aOptionListToUnserialize as $sOption) {
				if (!empty(GMerchantCenter::$aConfiguration[strtoupper($sOption)])
					&& is_string(GMerchantCenter::$aConfiguration[strtoupper($sOption)])
					&& !is_numeric(GMerchantCenter::$aConfiguration[strtoupper($sOption)])
				) {
					GMerchantCenter::$aConfiguration[strtoupper($sOption)] = unserialize(GMerchantCenter::$aConfiguration[strtoupper($sOption)]);
				}
			}
		}
	}

	/**
	 * isActiveLang() method defines if the language is active
	 *
	 * @param mixed $mLang
	 * @return bool
	 */
	public static function isActiveLang($mLang)
	{
		if (is_numeric($mLang)) {
		   $sField = 'id_lang';
		}
		else {
			$sField = 'iso_code';
			$mLang = strtolower($mLang);
		}

		$mResult = Db::getInstance()->getValue('SELECT count(*) FROM `'._DB_PREFIX_.'lang` WHERE active = 1 AND `' . $sField . '` = "' . pSQL($mLang) . '"');

		return (
			!empty($mResult)? true : false
		);
	}

	/**
	 * getLangIso() method set good iso lang
	 *
	 * @return string
	 */
	public static function getLangIso($iLangId = null)
	{
		if (null === $iLangId) {
			$iLangId = GMerchantCenter::$iCurrentLang;
		}

		// get iso lang
		$sIsoLang = Language::getIsoById($iLangId);

		if (false === $sIsoLang) {
			$sIsoLang = 'en';
		}
		return $sIsoLang;
	}

	/**
	 * getLangId() method return Lang id from iso code
	 *
	 * @param string $sIsoCode
	 * @return int
	 */
	public static function getLangId($sIsoCode, $iDefaultId = null)
	{
		// get iso lang
		$iLangId = Language::getIdByIso($sIsoCode);

		if (empty($iLangId) && $iDefaultId !== null) {
			$iLangId = $iDefaultId;
		}
		return $iLangId;
	}


	/**
	 * getAvailableLanguages() method Performs a "UNION" of installed shop languages and languages
	 * supported by Google shopping in the gMerchantCenterCountries variable
	 *
	 * @param int $iShopId
	 * @return array
	 */
	public static function getAvailableLanguages($iShopId)
	{
		// set
		$aAvailableLanguages = array();

		if (!empty(GMerchantCenter::$bCompare15) && !empty($iShopId)) {
			$aShopLanguages = Language::getLanguages(false, (int)($iShopId));
		}
		else {
			$aShopLanguages = Language::getLanguages(false);
		}

		foreach ($aShopLanguages as $aLanguage) {
			if ($aLanguage['active']
				&& array_key_exists($aLanguage['iso_code'], $GLOBALS[_GMC_MODULE_NAME . '_AVAILABLE_COUNTRIES'])
			) {
				$aAvailableLanguages[] = $aLanguage;
			}
		}
		return $aAvailableLanguages;
	}


	/**
	 * getLangCurrencyCountry() method returns information about languages / countries and currencies available for Google
	 *
	 * @param array $aAvailableLanguages
	 * @param array $aAvailableCountries
	 * @return array
	 */
	public static function getLangCurrencyCountry(array $aAvailableLanguages, array $aAvailableCountries)
	{
		// set
		$aLangCurrencyCountry = array();

		foreach ($aAvailableLanguages as $aLanguage) {
			foreach ($aAvailableCountries[$aLanguage['iso_code']] as $sCountry => $aLocaleData) {
				if (Currency::getIdByIsoCode($aLocaleData['currency'])) {
					$aLangCurrencyCountry[] = array(
						'langId' => $aLanguage['id_lang'],
						'langIso' => $aLanguage['iso_code'],
						'countryIso' => $sCountry,
						'currencyIso' => $aLocaleData['currency'],
						'currencyId' => Currency::getIdByIsoCode($aLocaleData['currency']),
					);
				}
			}
		}

		return $aLangCurrencyCountry;
	}


	/**
	 * getCurrency() method returns current currency sign or id
	 *
	 * @param string $sField : field name has to be returned
	 * @param string $iCurrencyId : currency id
	 * @return mixed : string or array
	 */
	public static function getCurrency($sField = null, $iCurrencyId = null)
	{
		// set
		$mCurrency = null;

		// get currency id
		if (null === $iCurrencyId) {
			$iCurrencyId = Configuration::get('PS_CURRENCY_DEFAULT');
		}

		$aCurrency = Currency::getCurrency($iCurrencyId);

		if ($sField !== null) {
			switch ($sField) {
				case 'id_currency' :
					$mCurrency = $aCurrency['id_currency'];
					break;
				case 'name' :
					$mCurrency = $aCurrency['name'];
					break;
				case 'iso_code' :
					$mCurrency = $aCurrency['iso_code'];
					break;
				case 'iso_code_num' :
					$mCurrency = $aCurrency['iso_code_num'];
					break;
				case 'sign' :
					$mCurrency = $aCurrency['sign'];
					break;
				case 'conversion_rate' :
					$mCurrency = $aCurrency['conversion_rate'];
					break;
				case 'format' :
					$mCurrency = $aCurrency['format'];
					break;
				default:
					$mCurrency = $aCurrency;
					break;
			}
		}

		return $mCurrency;
	}

	/**
	 * getTimeStamp() method returns timestamp
	 *
	 * @param string $sDate
	 * @param string $sType
	 * @return mixed : bool or int
	 */
	public static function getTimeStamp($sDate, $sType = 'en')
	{
		// set variable
		$iTimeStamp = false;

		// get date
		$aTmpDate = explode(' ', str_replace(array('-', '/', ':'), ' ', $sDate));

		if (count($aTmpDate) > 1) {
			if ($sType == 'en') {
				$iTimeStamp = mktime(0, 0, 0, $aTmpDate[0], $aTmpDate[1], $aTmpDate[2]);
			}
			elseif ($sType == 'db') {
				$iTimeStamp = mktime(0, 0, 0, $aTmpDate[1], $aTmpDate[2], $aTmpDate[0]);
			}
			else {
				$iTimeStamp = mktime(0, 0, 0, $aTmpDate[1], $aTmpDate[0], $aTmpDate[2]);
			}
		}
		// destruct
		unset($aTmpDate);

		return $iTimeStamp;
	}


	/**
	 * formatTimestamp() method returns a formatted date
	 *
	 * @param int $iTimestamp
	 * @param mixed $mLocale
	 * @param string $sLangIso
	 * @return string
	 */
	public static function formatTimestamp($iTimestamp, $sTemplate = null, $mLocale = false, $sLangIso = null)
	{
		// set
		$sDate = '';

		if ($mLocale !== false) {
			if (null === $sTemplate) {
				$sTemplate = '%d %h. %Y';
			}
			// set date with locale format
			$sDate = strftime($sTemplate, $iTimestamp);
		}
		else {
			// get Lang ISO
			$sLangIso = ($sLangIso !== null)? $sLangIso : GMerchantCenter::$sCurrentLang;

			switch ($sTemplate) {
				case 'snippet' :
					$sDate = date('d', $iTimestamp)
						. ' '
						. (!empty($GLOBALS[_GMC_MODULE_NAME . '_MONTH'][$sLangIso])? $GLOBALS[_GMC_MODULE_NAME . '_MONTH'][$sLangIso]['long'][date('n', $iTimestamp)] : date('M', $iTimestamp))
						. ' '
						. date('Y', $iTimestamp);
					break;
				default:
					// set date with matching month or with default language
					$sDate = date('d', $iTimestamp)
						. ' '
						. (!empty($GLOBALS[_GMC_MODULE_NAME . '_MONTH'][$sLangIso])? $GLOBALS[_GMC_MODULE_NAME . '_MONTH'][$sLangIso]['short'][date('n', $iTimestamp)] : date('M', $iTimestamp))
						. ' '
						. date('Y', $iTimestamp);
					break;
			}
		}
		return $sDate;
	}


	/**
	 * getPageName() method returns formatted URI for page name type
	 *
	 * @return mixed
	 */
	public static function getPageName()
	{
		$sScriptName = '';

		// use case - script name filled
		if (!empty($_SERVER['SCRIPT_NAME'])) {
			$sScriptName = $_SERVER['SCRIPT_NAME'];
		}
		// use case - php_self filled
		elseif ($_SERVER['PHP_SELF']) {
			$sScriptName = $_SERVER['PHP_SELF'];
		}
		// use case - default script name
		else {
			$sScriptName = 'index.php';
		}
		return (
			substr(basename($sScriptName), 0, strpos(basename($sScriptName), '.'))
		);
	}


	/**
	 * getTemplatePath() method returns template path
	 *
	 * @param string $sTemplate
	 * @return string
	 */
	public static function getTemplatePath($sTemplate)
	{
		// set
		$mTemplatePath = null;

		if (version_compare(_PS_VERSION_, '1.5', '>')) {
			$mTemplatePath = GMerchantCenter::$oModule->getTemplatePath($sTemplate);
		}
		else {
			if (file_exists(_PS_THEME_DIR_ . 'modules/' . GMerchantCenter::$oModule->name . '/' . $sTemplate)) {
				$mTemplatePath = _PS_THEME_DIR_ . 'modules/' . GMerchantCenter::$oModule->name . '/' . $sTemplate;
			}
			elseif (file_exists(_PS_MODULE_DIR_ . GMerchantCenter::$oModule->name . '/' . $sTemplate)) {
				$mTemplatePath = _PS_MODULE_DIR_ . GMerchantCenter::$oModule->name . '/' . $sTemplate;
			}
		}

		return $mTemplatePath;
	}

	/**
	 * getLinkObj() method returns link object
	 *
	 * @return obj
	 */
	public static function getLinkObj()
	{
		return (
			empty(GMerchantCenter::$bCompare15)? new Link() : GMerchantCenter::$oContext->link
		);
	}

	/**
	 * getProductLink() method returns product link
	 *
	 * @param obj $oProduct
	 * @param int $iLangId
	 * @param string $sCatRewrite
	 * @return string
	 */
	public static function getProductLink($oProduct, $iLangId, $sCatRewrite = '')
	{
		$sProdUrl = '';

		if (!empty(GMerchantCenter::$bCompare1550)) {
			$sProdUrl = self::getLinkObj()->getProductLink($oProduct, null, null, null, (int)$iLangId, null, 0, false);
		}
		elseif (!empty(GMerchantCenter::$bCompare15)) {
			if (Configuration::get('PS_REWRITING_SETTINGS')) {
				$sProdUrl = self::getLinkObj()->getProductLink($oProduct, null, null, null, (int)$iLangId, null, 0, true);
			}
			else {
				$sProdUrl = self::getLinkObj()->getProductLink($oProduct, null, null, null, (int)$iLangId, null, 0, false);
			}
		}
		else {
			$sProdUrl = self::getLinkObj()->getProductLink((int)($oProduct->id), $oProduct->link_rewrite, $sCatRewrite, $oProduct->ean13, (int)$iLangId);
		}
		return $sProdUrl;
	}

	/**
	 * getProductCondition() method returns the product condition
	 *
	 * @param string $sCondition
	 * @return string
	 */
	public static function getProductCondition($sCondition = null)
	{
		$sResult = '';

		if ($sCondition !== null
			&& in_array($sCondition, array('new', 'used', 'refurbished'))
		) {
			$sResult = $sCondition;
		}
		else {
			$sResult = !empty(GMerchantCenter::$aConfiguration['GMERCHANTCENTER_COND'])? GMerchantCenter::$aConfiguration['GMERCHANTCENTER_COND'] : 'new';
		}

		return $sResult;
	}


	/**
	 * getProductImage() method returns product image
	 *
	 * @param obj $oProduct
	 * @param string $sImageType
	 * @param array $aForceImage
	 * @param string $sForceDomainName
	 * @return obj
	 */
	public static function getProductImage(Product &$oProduct, $sImageType = null, $aForceImage = false, $sForceDomainName = null)
	{
		$sImgUrl = '';

		if (Validate::isLoadedObject($oProduct)) {
			// use case - get Image
			$aImage = $aForceImage !== false ? $aForceImage : Image::getCover($oProduct->id);

			if (!empty($aImage)) {
				// get image url
				if ($sImageType !== null) {
					$sImgUrl = self::getLinkObj()->getImageLink($oProduct->link_rewrite, $oProduct->id . '-' . $aImage['id_image'], $sImageType);
				}
				else {
					$sImgUrl = self::getLinkObj()->getImageLink($oProduct->link_rewrite, $oProduct->id . '-' . $aImage['id_image']);
				}

				// use case - get valid IMG URI before  Prestashop 1.4
				$sImgUrl = self::detectHttpUri($sImgUrl, $sForceDomainName);
			}
		}
		return $sImgUrl;
	}

	/**
	 * detectHttpUri() method detects and returns available URI - resolve Prestashop compatibility
	 *
	 * @param string $sURI
	 * @param string $sForceDomainName
	 * @return mixed
	 */
	public static function detectHttpUri($sURI, $sForceDomainName = null)
	{
		// use case - only with relative URI
		if (!strstr($sURI, 'http')) {
			$sHost = $sForceDomainName !== null? $sForceDomainName : $_SERVER['HTTP_HOST'];
			$sURI = 'http' . (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off'? 's' : '')  . '://' . $sHost . $sURI;
		}
		elseif ($sForceDomainName !== null
			&& !strstr($sURI, $sForceDomainName)
			&& strstr($sURI, $_SERVER['HTTP_HOST'])
		) {
			$sTmpDomainName = str_replace('//', '' , substr($sForceDomainName, strpos($sForceDomainName, '//'), strlen($sForceDomainName)));
			$sURI = str_replace($_SERVER['HTTP_HOST'], $sTmpDomainName, $sURI);
		}
		return $sURI;
	}

	/**
	 * truncateUri() method truncate current request_uri in order to delete params : sAction and sType
	 *
	 * @param mixed: string or array $mNeedle
	 * @return mixed
	 */
	public static function truncateUri($mNeedle = '&sAction')
	{
		// set tmp
		$aQuery = is_array($mNeedle)? $mNeedle : array($mNeedle);

		// get URI
		$sURI = $_SERVER['REQUEST_URI'];

		foreach ($aQuery as $sNeedle) {
			$sURI = strstr($sURI, $sNeedle)? substr($sURI, 0 , strpos($sURI, $sNeedle)) : $sURI;
		}
		return $sURI;
	}

	/**
	 * jsonEncode() method detects available method and apply json encode
	 *
	 * @return string
	 */
	public static function jsonEncode($aData)
	{
		if (function_exists('json_encode')) {
			$aData = json_encode($aData);
		}
		elseif (method_exists('Tools', 'jsonEncode')) {
			$aData = Tools::jsonEncode($aData);
		}
		else {
			if (is_null($aData)) {
				return 'null';
			}
			if ($aData === false) {
				return 'false';
			}
			if ($aData === true) {
				return 'true';
			}
			if (is_scalar($aData)) {
				$aData = addslashes($aData);
				$aData = str_replace("\n", '\n', $aData);
				$aData = str_replace("\r", '\r', $aData);
				$aData = preg_replace('{(</)(script)}i', "$1'+'$2", $aData);
				return "'$aData'";
			}
			$isList = true;
			for ($i=0, reset($aData); $i<count($aData); $i++, next($aData)) {
				if (key($aData) !== $i) {
					$isList = false;
					break;
				}
			}
			$result = array();

			if ($isList) {
				foreach ($aData as $v) {
					$result[] = self::json_encode($v);
				}
				$aData = '[ ' . join(', ', $result) . ' ]';
			}
			else {
				foreach ($aData as $k => $v) {
					$result[] = self::json_encode($k) . ': ' . self::json_encode($v);
				}
				$aData = '{ ' . join(', ', $result) . ' }';
			}
		}

		return $aData;
	}

	/**
	 * jsonDecode() method detects available method and apply json decode
	 *
	 * @return mixed
	 */
	public static function jsonDecode($aData)
	{
		if (function_exists('json_decode')) {
			$aData = json_decode($aData);
		}
		elseif (method_exists('Tools', 'jsonDecode')) {
			$aData = Tools::jsonDecode($aData);
		}
		return $aData;
	}

	/**
	 * isInstalled() method check if specific module and module's vars are available
	 *
	 * @param int $sModuleName
	 * @param array $aCheckedVars
	 * @param bool $bObjReturn
	 * @param bool $bOnlyInstalled
	 * @return mixed : true or false or obj
	 */
	public static function isInstalled($sModuleName, array $aCheckedVars = array(), $bObjReturn = false, $bOnlyInstalled = false)
	{
		$mReturn = false;

		// use case - check module is installed in DB
		if (Module::isInstalled($sModuleName)) {
			if (!$bOnlyInstalled) {
				$oModule = Module::getInstanceByName($sModuleName);

				if (!empty($oModule)) {
					// check if module is activated
					$aActivated = Db::getInstance()->ExecuteS('SELECT id_module as id, active FROM ' . _DB_PREFIX_ . 'module WHERE name = "' . pSQL($sModuleName)  .'" AND active = 1');

					if (!empty($aActivated[0]['active'])) {
						$mReturn = true;

						if (version_compare(_PS_VERSION_, '1.5', '>')) {
							$aActivated = Db::getInstance()->ExecuteS('SELECT * FROM ' . _DB_PREFIX_ . 'module_shop WHERE id_module = ' . pSQL($aActivated[0]['id'])  .' AND id_shop = ' . Context::getContext()->shop->id);

							if (empty($aActivated)) {
								$mReturn = false;
							}
						}

						if ($mReturn) {
							if (!empty($aCheckedVars)) {
								foreach ($aCheckedVars as $sVarName) {
									$mVar = Configuration::get($sVarName);

									if (empty($mVar)) {
										$mReturn = false;
									}
								}
							}
						}
					}
				}
				if ($mReturn && $bObjReturn) {
					$mReturn = $oModule;
				}
				unset($oModule);
			}
			else {
				$mReturn = true;
			}
		}
		return $mReturn;
	}

	/**
	 * isProductObj() method check if the product is a valid obj
	 *
	 * @param int $iProdId
	 * @param int $iLangId
	 * @param bool $bObjReturn
	 * @param bool $bAllProperties
	 * @return mixed : true or false
	 */
	public static function isProductObj($iProdId, $iLangId, $bObjReturn = false, $bAllProperties = false)
	{
		// set
		$bReturn = false;

		$oProduct = new Product($iProdId, $bAllProperties, $iLangId);

		if (Validate::isLoadedObject($oProduct)) {
			$bReturn = true;
		}

		return (
			!empty($bObjReturn) && $bReturn? $oProduct : $bReturn
		);
	}

	/**
	 * getProductPath() method write breadcrumbs of product for category
	 *
	 * @param int $iCatId
	 * @param int $iLangId
	 * @param string $sPath
	 * @param bool $bEncoding
	 * @return string
	 */
	public static function getProductPath($iCatId, $iLangId, $sPath = '', $bEncoding = true)
	{
		$oCategory = new Category($iCatId);

		return (
			(Validate::isLoadedObject($oCategory)? strip_tags(self::getPath((int)$oCategory->id, (int)$iLangId, $sPath, $bEncoding)) : '')
		);
	}
	
	/**
	 * getPath() method write breadcrumbs of product for category
	 * 
	 * Forced to redo the function from Tools here as it works with cookie
	 * for language, not a passed parameter in the function
	 *
	 * @param int $iCatId
	 * @param int $iLangId
	 * @param string $sPath
	 * @param bool $bEncoding
	 * @return string
	 */
	public static function getPath($iCatId, $iLangId, $sPath = '', $bEncoding = true)
	{
		$mReturn = '';

		if ($iCatId == 1) {
			$mReturn = $sPath;
		}
		else {
			// get pipe
			$sPipe = ' > ';

			$sFullPath = '';

			/* Old way: v1.2 - v1.3 */
			if (version_compare(_PS_VERSION_, '1.4.1') == -1) {
				// instantiate
				$oCategory = new Category((int)($iCatId), (int)($iLangId));

				if (Validate::isLoadedObject($oCategory)) {
					$sCatName = Category::hideCategoryPosition($oCategory->name);

					// htmlentities because this method generates some view
					if ($sPath != $sCatName) {
						$sDisplayedPath = ($bEncoding? htmlentities($sCatName, ENT_NOQUOTES, 'UTF-8') : $sCatName). $sPipe . $sPath;
					}
					else {
						$sDisplayedPath = ($bEncoding? htmlentities($sPath, ENT_NOQUOTES, 'UTF-8') : $sPath);
					}

					$mReturn = self::getPath((int)($oCategory->id_parent), $iLangId, trim($sDisplayedPath, $sPipe));
				}
			}
			/* New way for versions between v1.4 to v1.5.6.0 */
			elseif (version_compare(_PS_VERSION_, '1.5.6.0', '<')) {
				$aCurrentCategory = Db::getInstance()->getRow('
					SELECT id_category, level_depth, nleft, nright
					FROM '._DB_PREFIX_.'category
					WHERE id_category = '.(int)$iCatId
				);

				if (isset($aCurrentCategory['id_category'])) {
					$sQuery = 'SELECT c.id_category, cl.name, cl.link_rewrite FROM '._DB_PREFIX_.'category c';

					// use case 1.5
					if (version_compare(_PS_VERSION_, '1.5', '>')) {
						Shop::addSqlAssociation('category', 'c', false);
					}

					$sQuery .= ' LEFT JOIN '._DB_PREFIX_.'category_lang cl ON (cl.id_category = c.id_category AND cl.`id_lang` = ' . (int)($iLangId) . (version_compare(_PS_VERSION_, '1.5', '>') ? Shop::addSqlRestrictionOnLang('cl') : '') . ')';

					$sQuery .= '
						WHERE c.nleft <= '.(int)$aCurrentCategory['nleft'].' AND c.nright >= '.(int)$aCurrentCategory['nright'].' AND cl.id_lang = '.(int)($iLangId).' AND c.id_category != 1
						ORDER BY c.level_depth ASC
						LIMIT '.(int)$aCurrentCategory['level_depth'];

					$aCategories = Db::getInstance()->ExecuteS($sQuery);

					$iCount = 1;
					$nCategories = count($aCategories);

					foreach ($aCategories as $aCategory) {
						$sFullPath .=
							($bEncoding? htmlentities($aCategory['name'], ENT_NOQUOTES, 'UTF-8') : $aCategory['name']).
							(($iCount++ != $nCategories OR !empty($sPath)) ? $sPipe : '');
					}
					$mReturn = $sFullPath . $sPath;
				}
			}
			else {
				$aInterval = Category::getInterval($iCatId);
				$aIntervalRoot = Category::getInterval(Context::getContext()->shop->getCategory());

				if (!empty($aInterval) && !empty($aIntervalRoot)) {
					$sQuery = 'SELECT c.id_category, cl.name, cl.link_rewrite'
						. ' FROM '._DB_PREFIX_.'category c'
						. (version_compare(_PS_VERSION_, '1.5', '>') ? Shop::addSqlAssociation('category', 'c', false) : '')
						. ' LEFT JOIN '._DB_PREFIX_.'category_lang cl ON (cl.id_category = c.id_category'.Shop::addSqlRestrictionOnLang('cl').')'
						. 'WHERE c.nleft <= '.$aInterval['nleft']
						. ' AND c.nright >= '.$aInterval['nright']
						. ' AND c.nleft >= '.$aIntervalRoot['nleft']
						. ' AND c.nright <= '.$aIntervalRoot['nright']
						. ' AND cl.id_lang = '.(int)$iLangId
						. ' AND c.level_depth > '.(int)$aIntervalRoot['level_depth']
						. ' ORDER BY c.level_depth ASC';

					$aCategories = Db::getInstance()->executeS($sQuery);

					$iCount = 1;
					$nCategories = count($aCategories);

					foreach ($aCategories as $aCategory) {
						$sFullPath .=
							($bEncoding? htmlentities($aCategory['name'], ENT_NOQUOTES, 'UTF-8') : $aCategory['name']).
							(($iCount++ != $nCategories || !empty($sPath)) ? $sPipe  : '');
					}
					$mReturn = $sFullPath . $sPath;
				}
			}
		}
		return $mReturn;
	}


	/**
	 * recursiveCategoryTree() method process categories to generate tree of them
	 *
	 * @param array $aCategories
	 * @param array $aIndexedCat
	 * @param array $aCurrentCat
	 * @param int $iCurrentIndex
	 * @param int $iDefaultId
	 * @param bool $bFirstExec
	 * @return array
	 */
	public static function recursiveCategoryTree(array $aCategories, array $aIndexedCat, $aCurrentCat, $iCurrentIndex = 1, $iDefaultId = null, $bFirstExec = false)
	{
		// set variables
		static $_aTmpCat;
		static $_aFormatCat;

		if ($bFirstExec) {
			$_aTmpCat = null;
			$_aFormatCat = null;
		}

		if (!isset($_aTmpCat[$aCurrentCat['infos']['id_parent']])) {
			$_aTmpCat[$aCurrentCat['infos']['id_parent']] = 0;
		}
		$_aTmpCat[$aCurrentCat['infos']['id_parent']] += 1;

		// calculate new level
		$aCurrentCat['infos']['iNewLevel'] = $aCurrentCat['infos']['level_depth'] + (version_compare(_PS_VERSION_, '1.5.0') != -1? 0 : 1);

		// calculate type of gif to display - displays tree in good
		$aCurrentCat['infos']['sGifType'] = (count($aCategories[$aCurrentCat['infos']['id_parent']]) == $_aTmpCat[$aCurrentCat['infos']['id_parent']] ? 'f' : 'b');

		// calculate if checked
		if (in_array($iCurrentIndex, $aIndexedCat)) {
			$aCurrentCat['infos']['bCurrent'] = true;
		}
		else {
			$aCurrentCat['infos']['bCurrent'] = false;
		}

		// define classname with default cat id
		$aCurrentCat['infos']['mDefaultCat'] = ($iDefaultId === null)? 'default' : $iCurrentIndex;

		$_aFormatCat[] = $aCurrentCat['infos'];

		if (isset($aCategories[$iCurrentIndex])) {
			foreach ($aCategories[$iCurrentIndex] as $iCatId => $aCat) {
				if ($iCatId != 'infos') {
					self::recursiveCategoryTree($aCategories, $aIndexedCat, $aCategories[$iCurrentIndex][$iCatId], $iCatId);
				}
			}
		}
		return $_aFormatCat;
	}

	/**
	 * recursiveBrandTree() method process brands to generate tree of them
	 *
	 * @param array $aBrands
	 * @param array $aIndexedBrands
	 * @return array
	 */
	public static function recursiveBrandTree(array $aBrands, array $aIndexedBrands)
	{
		// set
		$aFormatBrands = array();

		foreach ($aBrands as $iIndex => $aBrand) {
			$aFormatBrands[] = array(
				'id' => $aBrand['id_manufacturer'],
				'name' => $aBrand['name'],
				'checked' => (in_array($aBrand['id_manufacturer'], $aIndexedBrands)? true : false)
			);
		}

		return $aFormatBrands;
	}

	/**
	 * recursiveSupplierTree() method process suppliers to generate tree of them
	 *
	 * @param array $aSuppliers
	 * @param array $aIndexedSuppliers
	 * @return array
	 */
	public static function recursiveSupplierTree(array $aSuppliers, array $aIndexedSuppliers)
	{
		// set
		$aFormatSuppliers = array();

		foreach ($aSuppliers as $iIndex => $aSupplier) {
			$aFormatSuppliers[] = array(
				'id' => $aSupplier['id_supplier'],
				'name' => $aSupplier['name'],
				'checked' => (in_array($aSupplier['id_supplier'], $aIndexedSuppliers)? true : false)
			);
		}

		return $aFormatSuppliers;
	}

	/**
	 * round() method round on numeric
	 *
	 * @param float $fVal
	 * @param int $iPrecision
	 * @return float
	 */
	public static function round($fVal, $iPrecision = 2)
	{
		if (method_exists('Tools', 'ps_round')) {
			$fVal = Tools::ps_round($fVal, $iPrecision);
		}
		else {
			$fVal = round($fVal, $iPrecision);
		}

		return $fVal;
	}

	/**
	 * setHost() method set host
	 *
	 * @return string
	 */
	public static function setHost()
	{
		if (Configuration::get('PS_SHOP_DOMAIN') != false) {
			$sURL = 'http://' . Configuration::get('PS_SHOP_DOMAIN');
		}
		else {
			$sURL = 'http://'.$_SERVER['HTTP_HOST'];
		}

		return $sURL;
	}


	/**
	 * setXmlFilePrefix() method set the XML file's prefix
	 *
	 * @return string
	 */
	public static function setXmlFilePrefix()
	{
		return (
			empty(GMerchantCenter::$aConfiguration['GMERCHANTCENTER_FEED_PROTECTION']) ? 'gmerchantcenter' : 'gmerchantcenter' . GMerchantCenter::$aConfiguration['GMERCHANTCENTER_FEED_TOKEN']
		);
	}

	/**
	 * copyOutputFile() method copy module's php file
	 *
	 * @return bool
	 */
	public static function copyOutputFile()
	{
		@copy(_GMC_PATH_ROOT . _GMC_XML_PHP_NAME, _PS_ROOT_DIR_ . '/' . _GMC_XML_PHP_NAME);
		return true;
	}


	/**
	 * copyOutputOnFlyFile() method copy module's on-fly php file
	 *
	 * @return bool
	 */
	public static function copyOutputOnFlyFile()
	{
		@copy(_GMC_PATH_ROOT . _GMC_FEED_PHP_NAME, _PS_ROOT_DIR_ . '/' . _GMC_FEED_PHP_NAME);
		return true;
	}


	/**
	 * checkOutputFile() method check the copy of the php script file
	 *
	 * @return bool
	 */
	public static function checkOutputFile()
	{
		return is_file(_PS_ROOT_DIR_ . '/' . _GMC_XML_PHP_NAME);
	}


	/**
	 * cleanUpFiles() method clear all generated files
	 *
	 * @return bool
	 */
	public static function cleanUpFiles()
	{
		foreach (GMerchantCenter::$aAvailableLanguages as $aLanguage)
		{
			// get each countries by language
			$aCountries = $GLOBALS[_GMC_MODULE_NAME . '_AVAILABLE_COUNTRIES'][$aLanguage['iso_code']];

			foreach ($aCountries as $sCountry => $aLocaleData)
			{
				// detect file's suffix and clear file
				$fileSuffix = self::buildFileSuffix($aLanguage['iso_code'], $sCountry);
				@unlink(_GMC_SHOP_PATH_ROOT . GMerchantCenter::$sFilePrefix . '.' . $fileSuffix . '.xml');
			}
		}
	}


	/**
	 * buildFileSuffix() method Build file suffix based on language and country ISO code
	 *
	 * @param string $sLangIso
	 * @param string $sCountryIso
	 * @param int $iShopId
	 * @return string
	 */
	public static function buildFileSuffix($sLangIso, $sCountryIso, $iShopId = 0)
	{
		if (Tools::strtolower($sLangIso) == Tools::strtolower($sCountryIso)) {
			$sSuffix = Tools::strtolower($sLangIso);
		}
		else {
			$sSuffix = Tools::strtolower($sLangIso) . '.' . Tools::strtolower($sCountryIso);
		}

		if (!empty(GMerchantCenter::$bCompare15)) {
			$sSuffix .= ($iShopId? '.shop' . $iShopId : '.shop' . GMerchantCenter::$iShopId);
		}

		return $sSuffix;
	}

	/**
	 * getConditionType() method returns all available condition
	 */
	public static function getConditionType()
	{
		return (
			array(
				'new' => GMerchantCenter::$oModule->l('New', 'module-tools_class'),
				'used' => GMerchantCenter::$oModule->l('Used', 'module-tools_class'),
				'refurbished' => GMerchantCenter::$oModule->l('Refurbished', 'module-tools_class'),
			)
		);
	}

	/**
	 * getDescriptionType() method returns all available description
	 */
	public static function getDescriptionType()
	{
		return (
			array(
				1 => GMerchantCenter::$oModule->l('Short description', 'module-tools_class'),
				2 => GMerchantCenter::$oModule->l('Long description', 'module-tools_class'),
				3 => GMerchantCenter::$oModule->l('Both', 'module-tools_class'),
				4 => GMerchantCenter::$oModule->l('Meta-description', 'module-tools_class')
			)
		);
	}

	/**
	 * loadGoogleTags() method set all available attributes managed in google flux
	 */
	public static function loadGoogleTags()
	{
		return array(
			'_no_available_for_order'    => array('label' => 'no_available_for_order', 'type' => 'notice', 'mandatory' => false, 'msg' =>  GMerchantCenter::$oModule->l('Products not exported due to the "available for order" option not activated for them', 'module-tools_class') . '.', 'faq_id' => 0, 'anchor' => ''),
			'_no_product_name'           => array('label' => 'no_product_name', 'type' => 'error', 'mandatory' => false, 'msg' =>  GMerchantCenter::$oModule->l('Products not exported due to missing the product name', 'module-tools_class') . '.', 'faq_id' => 0, 'anchor' => ''),
			'_no_required_data'          => array('label' => 'no_required_data', 'type' => 'error', 'mandatory' => false, 'msg' =>  GMerchantCenter::$oModule->l('Products not exported due to missing one of these information: product name or product description or product URL or URL image link', 'module-tools_class') . '.', 'faq_id' => 0, 'anchor' => ''),
			'_no_export_no_supplier_ref' => array('label' => 'not_export_without_supplier_ref', 'type' => 'notice', 'mandatory' => false, 'msg' =>  GMerchantCenter::$oModule->l('Products not exported due to missing supplier reference and Unique Product Identifier settings', 'module-tools_class') . '.', 'faq_id' => 22, 'anchor' => ''),
			'_no_export_no_ean_upc'		 => array('label' => 'not_export_without_EAN13_UPC_ref', 'type' => 'notice', 'mandatory' => false, 'msg' =>  GMerchantCenter::$oModule->l('Products not exported due to missing EAN / UPC and Unique Product Identifier settings', 'module-tools_class') . '.', 'faq_id' => 22, 'anchor' => ''),
			'_no_export_no_stock'		 => array('label' => 'not_export_no_stock', 'type' => 'notice', 'mandatory' => false, 'msg' =>  GMerchantCenter::$oModule->l('Products not exported due to out of stock export settings', 'module-tools_class') . '.', 'faq_id' => 22, 'anchor' => ''),
			'_no_export_min_price'		 => array('label' => 'not_export_under_min_price', 'type' => 'notice', 'mandatory' => false, 'msg' =>  GMerchantCenter::$oModule->l('Products not exported due to minimum price settings', 'module-tools_class') . '.', 'faq_id' => 0, 'anchor' => ''),
			// Product exported but missing information
			'excluded'                   => array('label' => 'excluded_product_list', 'type' => 'notice', 'mandatory' => false, 'msg' =>  GMerchantCenter::$oModule->l('this product or combination has been excluded from your feed as you define it in the exclusion rules tab', 'module-tools_class') . '.', 'faq_id' => 0, 'anchor' => ''),
			'id'                         => array('label' => '<g:id>', 'type' => 'error', 'mandatory' => true, 'msg' =>  GMerchantCenter::$oModule->l('tag "ID" => The identifier for each item has to be unique within your account, and cannot be re-used between feeds', 'module-tools_class') . '.', 'faq_id' => 100, 'anchor' => 'prod_id'),
			'title'                      => array('label' => 'title', 'type' => 'error', 'mandatory' => true, 'msg' =>  GMerchantCenter::$oModule->l('tag "TITLE" => This is the name of your item which is required', 'module-tools_class') . '.', 'faq_id' => 100, 'anchor' => 'title'),
			'description'                => array('label' => 'description', 'type' => 'error', 'mandatory' => true, 'msg' =>  GMerchantCenter::$oModule->l('tag "DESCRIPTION" => Include only information relevant to the item, but be comprehensive since we use this text to find your item', 'module-tools_class') . '.', 'faq_id' => 100, 'anchor' => 'prod_description'),
			'google_product_category'    => array('label' => '<g:google_product_category>', 'type' => 'warning', 'mandatory' => false, 'msg' =>  GMerchantCenter::$oModule->l('tag "GOOGLE PRODUCT CATEGORY" => The Google product category attribute indicates the category of the product being submitted, according to the Google product taxonomy', 'module-tools_class') . '.', 'faq_id' => 100, 'anchor' => 'google_category'),
			'product_type'               => array('label' => '<g:product_type>', 'type' => 'warning', 'mandatory' => false, 'msg' =>  GMerchantCenter::$oModule->l('tag "PRODUCT TYPE" => This attribute also indicates the category of the product being submitted, but you can provide your own classification.', 'module-tools_class') . '.', 'faq_id' => 100, 'anchor' => 'prod_type'),
			'link'                       => array('label' => 'link', 'type' => 'error', 'mandatory' => true, 'msg' =>  GMerchantCenter::$oModule->l('tag "LINK" => The user is sent to this URL when your item is clicked on Google Shopping. We also refer to this as the landing page', 'module-tools_class') . '.', 'faq_id' => 100, 'anchor' => 'prod_link'),
			'image_link'                 => array('label' => '<g:image_link>', 'type' => 'error', 'mandatory' => true, 'msg' =>  GMerchantCenter::$oModule->l('tag "IMAGE LINK" => This is the URL of an associated image for a product. Submit full-size images for your products and do not submit thumbnail versions of the images', 'module-tools_class') . '.', 'faq_id' => 100, 'anchor' => 'image_link'),
			'condition'                  => array('label' => '<g:condition>', 'type' => 'error', 'mandatory' => true, 'msg' =>  GMerchantCenter::$oModule->l('tag "CONDITION" => There are only three accepted values : "new", "refurbished", "used"', 'module-tools_class') . '.', 'faq_id' => 100, 'anchor' => 'prod_condition'),
			'availability'               => array('label' => '<g:availability>', 'type' => 'error', 'mandatory' => true, 'msg' =>  GMerchantCenter::$oModule->l('tag "AVAILABILITY" => The availability attribute only has four accepted values : "in stock", "available for order", "out of stock", "preorder"', 'module-tools_class') . '.', 'faq_id' => 100, 'anchor' => 'prod_availability'),
			'price'                      => array('label' => '<g:price>', 'type' => 'error', 'mandatory' => true, 'msg' =>  GMerchantCenter::$oModule->l('tag "PRICE" => The price of the item has to be the most prominent price on the landing page', 'module-tools_class') . '.', 'faq_id' => 100, 'anchor' => 'prod_price'),
			'gtin'                       => array('label' => '<g:gtin>', 'type' => 'warning', 'mandatory' => false, 'msg' =>  GMerchantCenter::$oModule->l('tag "GTIN" => In this attribute, you will include Global Trade Item Numbers (GTINs) for your products', 'module-tools_class') . '.', 'faq_id' => 100, 'anchor' => 'prod_gtin'),
			'brand'                      => array('label' => '<g:brand>', 'type' => 'warning', 'mandatory' => false, 'msg' =>  GMerchantCenter::$oModule->l('tag "BRAND" => When to include: Required for all items - except for books, media and custom made goods, or if you\'re providing \'mpn\' and \'gtin\'', 'module-tools_class') . '.', 'faq_id' => 100, 'anchor' => 'prod_brand'),
			'mpn'                        => array('label' => '<g:mpn>', 'type' => 'warning', 'mandatory' => false, 'msg' =>  GMerchantCenter::$oModule->l('tag "MPN" => This code uniquely identifies the product to its manufacturer. In particular, the combination of brand and MPN clearly specifies one product', 'module-tools_class') . '.', 'faq_id' => 100, 'anchor' => 'prod_mpn'),
			'adult'                      => array('label' => '<g:adult>', 'type' => 'warning', 'mandatory' => false, 'msg' =>  GMerchantCenter::$oModule->l('tag "ADULT" => The adult status assigned to your product listings through the ‘adult’ attribute affects where product listings can show', 'module-tools_class') . '.', 'faq_id' => 100, 'anchor' => 'adult'),
			'gender'                     => array('label' => '<g:gender>', 'type' => 'warning', 'mandatory' => false, 'msg' =>  GMerchantCenter::$oModule->l('tag "GENDER" => Three predefined values accepted : "male", "female", "unisex"', 'module-tools_class') . '.', 'faq_id' => 100, 'anchor' => 'gender'),
			'age_group'                  => array('label' => '<g:age_group>', 'type' => 'warning', 'mandatory' => false, 'msg' =>  GMerchantCenter::$oModule->l('tag "AGE GROUP" => The only two accepted values of this attribute are : "adults", "kids"', 'module-tools_class') . '.', 'faq_id' => 100, 'anchor' => 'age_group'),
			'color'                      => array('label' => '<g:color>', 'type' => 'warning', 'mandatory' => false, 'msg' =>  GMerchantCenter::$oModule->l('tag "COLOR" => This defines the dominant color(s) of an item', 'module-tools_class') . '.', 'faq_id' => 100, 'anchor' => 'size_color'),
			'size'                       => array('label' => '<g:size>', 'type' => 'warning', 'mandatory' => false, 'msg' =>  GMerchantCenter::$oModule->l('tag "SIZE" => This indicates the size of a product. You may any provide values which are appropriate to your items', 'module-tools_class') . '.', 'faq_id' => 100, 'anchor' => 'size_color'),
			'material'                   => array('label' => '<g:material>', 'type' => 'warning', 'mandatory' => false, 'msg' =>  GMerchantCenter::$oModule->l('tag "MATERIAL" => The material or fabric that a product is made out of', 'module-tools_class') . '.', 'faq_id' => 100, 'anchor' => 'pattern'),
			'pattern'                    => array('label' => '<g:pattern>', 'type' => 'warning', 'mandatory' => false, 'msg' =>  GMerchantCenter::$oModule->l('tag "PATTERN" => The pattern or graphic print featured on a product', 'module-tools_class') . '.', 'faq_id' => 100, 'anchor' => 'pattern'),
			'item_group_id'              => array('label' => '<g:item_group_id>', 'type' => 'warning', 'mandatory' => false, 'msg' =>  GMerchantCenter::$oModule->l('tag "ITEM GROUP ID" => All items that are color/material/pattern/size variants of the same product must have the same item group id', 'module-tools_class') . '.', 'faq_id' => 0, 'anchor' => ''),
			'shipping_weight'            => array('label' => '<g:shipping_weight>', 'type' => 'warning', 'mandatory' => false, 'msg' =>  GMerchantCenter::$oModule->l('tag "SHIPPING WEIGHT" => This is the weight of the product used to calculate the shipping cost of the item. It is required to provide this attribute if you have specified a global shipping rule in Settings that is dependent on shipping weight. Google accept the following units: lb, oz, g, kg (respect the lowercase)', 'module-tools_class') . '.', 'faq_id' => 100, 'anchor' => 'shipping_weight'),
			'shipping'                   => array('label' => '<g:shipping>', 'type' => 'error', 'mandatory' => true, 'msg' =>  GMerchantCenter::$oModule->l('tag "SHIPPING" => This attribute provides the specific shipping estimate for the product. Providing this attribute for an item overrides the global shipping settings you defined in your Google Merchant Center settings. It is required to provide shipping information for all items either by specifying default shipping values in your Google Merchant Center account settings, or by providing this attribute', 'module-tools_class') . '.', 'faq_id' => 100, 'anchor' => ''),
			// Product exported which do not respect Google prerequisites
			'title_length'		         => array('label' => 'not_respect_title_length', 'type' => 'notice', 'mandatory' => false, 'msg' =>  GMerchantCenter::$oModule->l('Google will still require your product titles to be no more than 150 characters long', 'module-tools_class') . '.', 'faq_id' => 0, 'anchor' => ''),
		);
	}

	/**
	 * getGoogleFile() method returns the Google taxonomy file's content
	 *
	 * @param string $sUrl
	 * @return string
	 */
	public static function getGoogleFile($sUrl)
	{
		$sContent = false;

		// Let's try first with file_get_contents
		if (ini_get('allow_url_fopen')) {
			$sContent =  (method_exists('Tools', 'file_get_contents')? Tools::file_get_contents($sUrl) : file_get_contents($sUrl));
		}

		// Returns false ? Try with CURL if available
		if ($sContent === false && function_exists('curl_init')) {
			$ch = curl_init();

			curl_setopt_array($ch, array(
				CURLOPT_URL => $sUrl,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_CONNECTTIMEOUT => 10,
				CURLOPT_VERBOSE => true
			));

			$sContent = @curl_exec($ch);
			curl_close($ch);
		}

		// Will return false if no method is available, or if either fails
		// This will cause a JavaScript alert to be triggered by the AJAX call
		return $sContent;
	}

	/**
	 * getGeneratedReport() method returns the generated report files
	 *
	 * @return array
	 */
	public static function getGeneratedReport()
	{
		$aLangCurrencies = array();

		foreach (GMerchantCenter::$aAvailableLanguages as $aLanguage) {
			foreach ($GLOBALS[_GMC_MODULE_NAME . '_AVAILABLE_COUNTRIES'][$aLanguage['iso_code']] as $sCountry => $aLocaleData) {
				if (Currency::getIdByIsoCode($aLocaleData['currency'])) {
					if (self::checkReportFile($aLanguage['iso_code'], $sCountry)) {
						$aLangCurrencies[] = $aLanguage['iso_code'] . '_' . $sCountry;
					}
				}
			}
		}

		return $aLangCurrencies;
	}

	/**
	 * formatProductTitle() method format the product title by uncap or not or leave uppercase only first character of each word
	 *
	 * @param string $sTitle
	 * @param string $sBrand
	 * @return string
	 */
	public static function formatProductTitle($sTitle, $iFormatMode = 0)
	{
		$sResult = '';

		// format title
		if ($iFormatMode == 0) {
			$sResult = self::strToUtf8($sTitle);
		}
		else {
			$sResult = self::strToLowerUtf8($sTitle);

			if ($iFormatMode == 1) {
				$aResult = explode(' ', $sResult);

				foreach ($aResult as &$sWord) {
					$sWord = Tools::ucfirst(trim($sWord));
				}

				$sResult = implode(' ', $aResult);
			}
			else {
				$sResult = Tools::ucfirst(trim($sResult));
			}
		}

		return $sResult;
	}

	/**
	 * getProductCombinationName() method format the product name with combination
	 *
	 * @param int $iAttrId
	 * @param int $iCurrentLang
	 * @param int $iShopId
	 * @return string
	 */
	public static function getProductCombinationName($iAttrId, $iCurrentLang, $iShopId)
	{
		require_once(_GMC_PATH_LIB . 'module-dao_class.php');

		// set var
		$sProductName = '';

		$aCombinations = BT_GmcModuleDao::getProductComboAttributes($iAttrId, $iCurrentLang, $iShopId);

		if (!empty($aCombinations)) {
			$sExtraName = '';
			foreach ($aCombinations as $c) {
				$sExtraName .= ' ' . Tools::stripslashes($c['name']);
			}
			$sProductName .= $sExtraName;
			unset($sExtraName);
		}
		unset($aCombinations);

		return $sProductName;
	}


	/**
	 * uncapProductTitle() method uncap the product title
	 *
	 * @param int $iAdvancedProdName
	 * @param string $sProdName
	 * @param string $sCatName
	 * @param string $sManufacturerName
	 * @param int $iLength
	 * @return string
	 */
	public static function truncateProductTitle($iAdvancedProdName, $sProdName, $sCatName, $sManufacturerName, $iLength)
	{
		if (function_exists('mb_substr')) {
			switch ($iAdvancedProdName) {
				case 0:
					$sProdName = mb_substr($sProdName, 0, $iLength);
					break;
				case 1:
					$sProdName = mb_substr($sCatName.' - '.$sProdName, 0, $iLength);
					break;
				case 2:
					$sProdName = mb_substr($sProdName.' - '.$sCatName, 0, $iLength);
					break;
				case 3:
					$sBrand = !empty($sManufacturerName)? $sManufacturerName.' - ' : '';
					$sProdName = mb_substr($sBrand . $sProdName, 0, $iLength);
					break;
				case 4:
					$sBrand = !empty($sManufacturerName)? ' - '.$sManufacturerName: '';
					$sProdName = mb_substr($sProdName . $sBrand, 0, $iLength);
					break;
				default:
					break;
			}
		}

		return Tools::stripslashes($sProdName);
	}

	/**
	 * strToLowerUtf8() method Used by uncapProductTitle. strtolower doesn't work with UTF-8
	 * The second solution if no mb_strtolower available is not perfect but will work
	 * with most European languages. Worse comes to worse, the person may chose not to uncap
	 *
	 * @param $sString
	 * return string
	 */
	public static function strToLowerUtf8($sString)
	{
		return (
			(function_exists('mb_strtolower')? mb_strtolower($sString, 'utf-8') : utf8_encode(Tools::strtolower(utf8_decode($sString))))
		);
	}

	/**
	 * strToUtf8() method Used by uncapProductTitle. strToUtf8 doesn't work with UTF-8
	 * The second solution if no mb_convert_encoding available is not perfect but will work
	 * with most European languages. Worse comes to worse, the person may chose not to uncap
	 *
	 * @param $sString
	 * return string
	 */
	public static function strToUtf8($sString)
	{
		return (
			(function_exists('mb_convert_encoding')? mb_convert_encoding($sString, 'utf-8')  : utf8_encode(utf8_decode($sString)))
		);
	}

	/**
	 * getGeneratedReport() method Check file based on language and country ISO code
	 *
	 * @param string $sIsoLang
	 * @param string $sIsoCountry
	 * @return bool
	 */
	public static function checkReportFile($sIsoLang, $sIsoCountry)
	{
		$sFilename = _GMC_REPORTING_DIR . 'reporting-' . $sIsoLang . '-' . Tools::strtolower($sIsoCountry) . '.txt';

		return (
			(file_exists($sFilename) && filesize($sFilename))? true : false
		);
	}

	/* Clean up MS Word style quotes and other characters Google does not like */
	/**
	 * cleanUp() method clean up MS Word style quotes and other characters Google does not like
	 *
	 * @param string $str
	 * @return string
	 */
	public static function cleanUp($str)
	{
		$str = str_replace('<br>', "\n", $str);
		$str = str_replace('<br />', "\n", $str);
		$str = str_replace('</p>', "\n", $str);
		$str = str_replace('<p>', '', $str);

		$quotes = array(
			"\xC2\xAB"     => '"', // « (U+00AB) in UTF-8
			"\xC2\xBB"     => '"', // » (U+00BB) in UTF-8
			"\xE2\x80\x98" => "'", // ‘ (U+2018) in UTF-8
			"\xE2\x80\x99" => "'", // ’ (U+2019) in UTF-8
			"\xE2\x80\x9A" => "'", // ‚ (U+201A) in UTF-8
			"\xE2\x80\x9B" => "'", // ‛ (U+201B) in UTF-8
			"\xE2\x80\x9C" => '"', // “ (U+201C) in UTF-8
			"\xE2\x80\x9D" => '"', // ” (U+201D) in UTF-8
			"\xE2\x80\x9E" => '"', // „ (U+201E) in UTF-8
			"\xE2\x80\x9F" => '"', // ‟ (U+201F) in UTF-8
			"\xE2\x80\xB9" => "'", // ‹ (U+2039) in UTF-8
			"\xE2\x80\xBA" => "'", // › (U+203A) in UTF-8
			"\xE2\x80\x94" => '-', // —
		);

		$str = strtr($str, $quotes);
		return trim(strip_tags($str));
	}
}