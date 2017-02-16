<?php
/**
 * admin-update_class.php file defines method to add or update content for basic settings / FILL ALL update data type
 */

class BT_AdminUpdate implements BT_IAdmin
{
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
	 * run() method update all tabs content of admin page
	 *
	 * @param string $sType => define which method to execute
	 * @param array $aParam
	 * @return array
	 */
	public function run($sType, array $aParam = null)
	{
		// set variables
		$aDisplayData = array();

		switch ($sType) {
			case 'basic'	: // use case - update basic settings
			case 'feed'		: // use case - update feed settings
			case 'feedList'	: // use case - update feed list settings
			case 'tag'		: // use case - update advanced tag settings
			case 'label'	: // use case - update custom label settings
			case 'google'	: // use case - update google campaign settings
			case 'googleCategoriesMatching'		: // use case - update google categories matching settings
			case 'reporting'		: // use case - update reporting settings
			case 'googleCategoriesSync'		: // use case - update google categories sync action
			case 'xml'		: // use case - update the xml file
				// execute match function
				$aDisplayData = call_user_func_array(array($this, '_update' . ucfirst($sType)), array($aParam));
				break;
			default :
				break;
		}
		return (
			$aDisplayData
		);
	}

	/**
	 * _updateBasic() method update basic settings
	 *
	 * @param array $aPost
	 * @return array
	 */
	private function _updateBasic(array $aPost)
	{
		// clean headers
		@ob_end_clean();

		// set
		$aData = array();

		try {
			// register title
			$sShopLink = Tools::getValue('bt_link');

			// clean the end slash if exists
			if (substr($sShopLink, -1) == '/') {
				$sShopLink = substr($sShopLink, 0, strlen($sShopLink)-1);
			}
			if (!Configuration::updateValue('GMERCHANTCENTER_LINK', $sShopLink)) {
				throw new Exception(GMerchantCenter::$oModule->l('An error occurred during shop link update', 'admin-update_class') . '.', 501);
			}
			unset($sShopLink);
			// register prefix
			$sPrefix = Tools::getValue('bt_prefix-id');
			if (!Configuration::updateValue('GMERCHANTCENTER_ID_PREFIX', $sPrefix)) {
				throw new Exception(GMerchantCenter::$oModule->l('An error occurred during shop prefix ID update', 'admin-update_class') . '.', 502);
			}
			unset($sPrefix);

			// register home category name in all active languages
			$this->_updateLang($aPost, 'bt_home-cat-name', 'GMERCHANTCENTER_HOME_CAT', false, GMerchantCenter::$oModule->l('home category name', 'admin-update_class'));

			// register ajax cycle
			$iAjaxCycle = Tools::getValue('bt_ajax-cycle');
			if (!Configuration::updateValue('GMERCHANTCENTER_AJAX_CYCLE', $iAjaxCycle)) {
				throw new Exception(GMerchantCenter::$oModule->l('An error occurred during ajax cycle update', 'admin-update_class') . '.', 503);
			}
			unset($iAjaxCycle);
			// register image type
			$sImageType = Tools::getValue('bt_image-size');
			if (!Configuration::updateValue('GMERCHANTCENTER_IMG_SIZE', $sImageType)) {
				throw new Exception(GMerchantCenter::$oModule->l('An error occurred during image size update', 'admin-update_class') . '.', 504);
			}
			unset($sImageType);
			// register home category ID
			$iHomeCatId = Tools::getValue('bt_home-cat-id');
			if (!Configuration::updateValue('GMERCHANTCENTER_HOME_CAT_ID', $iHomeCatId)) {
				throw new Exception(GMerchantCenter::$oModule->l('An error occurred during home category ID update', 'admin-update_class') . '.', 505);
			}
			unset($iHomeCatId);
			// register if add currency or not
			$bAddCurrency = Tools::getValue('bt_add-currency');
			if (!Configuration::updateValue('GMERCHANTCENTER_ADD_CURRENCY', $bAddCurrency)) {
				throw new Exception(GMerchantCenter::$oModule->l('An error occurred during adding currency update', 'admin-update_class') . '.', 506);
			}
			unset($bAddCurrency);
			// register product condition
			$sProductCondition = Tools::getValue('bt_product-condition');
			if (!Configuration::updateValue('GMERCHANTCENTER_COND', $sProductCondition)) {
				throw new Exception(GMerchantCenter::$oModule->l('An error occurred during product condition update', 'admin-update_class') . '.', 507);
			}
			unset($sProductCondition);
			// register advanced product name
			$sAdvancedProdName = Tools::getValue('bt_advanced-prod-name');
			if (!Configuration::updateValue('GMERCHANTCENTER_ADV_PRODUCT_NAME', $sAdvancedProdName)) {
				throw new Exception(GMerchantCenter::$oModule->l('An error occurred during advanced format name update', 'admin-update_class') . '.', 508);
			}
			unset($sAdvancedProdName);
			// register protection mode
			$bProtectionMode = Tools::getValue('bt_protection-mode');
			if (!Configuration::updateValue('GMERCHANTCENTER_FEED_PROTECTION', $bProtectionMode)) {
				throw new Exception(GMerchantCenter::$oModule->l('An error occurred during protection mode update', 'admin-update_class') . '.', 509);
			}
			unset($bProtectionMode);
			// register feed token
			$sFeedToken = Tools::getValue('bt_feed-token');
			if (!Configuration::updateValue('GMERCHANTCENTER_FEED_TOKEN', $sFeedToken)) {
				throw new Exception(GMerchantCenter::$oModule->l('An error occurred during feed token update', 'admin-update_class') . '.', 510);
			}
			unset($sFeedToken);

			// register advanced product title
			$sAdvancedProdTitle = Tools::getValue('bt_advanced-prod-title');
			if (!Configuration::updateValue('GMERCHANTCENTER_ADV_PROD_TITLE', $sAdvancedProdTitle)) {
				throw new Exception(GMerchantCenter::$oModule->l('An error occurred during format title update', 'admin-update_class') . '.', 511);
			}
		}
		catch (Exception $e) {
			$aData['aErrors'][] = array('msg' => $e->getMessage(), 'code' => $e->getCode());
		}

		// get configuration options
		BT_GmcModuleTools::getConfiguration();

		// require admin configure class - to factorise
		require_once(_GMC_PATH_LIB_ADMIN . 'admin-display_class.php');

		// get run of admin display in order to display first page of admin with basics settings updated
		$aDisplay = BT_AdminDisplay::create()->run('basics');

		// use case - empty error and updating status
		$aDisplay['assign'] = array_merge($aDisplay['assign'], array(
			'bUpdate' => (empty($aData['aErrors']) ? true : false),
		), $aData);

		// force xhr mode
		GMerchantCenter::$sQueryMode = 'xhr';

		// destruct
		unset($aData);

		return $aDisplay;
	}

	/**
	 * _updateFeed() method update feed management settings
	 *
	 * @param array $aPost
	 * @return array
	 */
	private function _updateFeed(array $aPost)
	{
		// clean headers
		@ob_end_clean();

		// set
		$aData = array();

		try {
			// include
			require_once(_GMC_PATH_LIB . 'module-dao_class.php');

			/* USE CASE - update categories and brands to export */
			if (Tools::getIsset('bt_export')) {
				$bExportMode = Tools::getValue('bt_export');
				if (!Configuration::updateValue('GMERCHANTCENTER_EXPORT_MODE', $bExportMode)) {
					throw new Exception(GMerchantCenter::$oModule->l('An error occurred during export mode update', 'admin-update_class') . '.', 520);
				}

				// handle categories and brands to export
				if ($bExportMode == 0) {
					$aCategoryBox = Tools::getValue('bt_category-box');

					if (empty($aCategoryBox)) {
						throw new Exception(GMerchantCenter::$oModule->l('An error occurred because you would select one category at least', 'admin-update_class') . '.', 521);
					} else {
						// delete previous categories
						$bResult = BT_GmcModuleDao::deleteCategories(GMerchantCenter::$iShopId);

						foreach ($aCategoryBox as $iCatId) {
							// insert
							$bResult = BT_GmcModuleDao::insertCategory($iCatId, GMerchantCenter::$iShopId);
						}
					}
					unset($aCategoryBox);
				}
				else {
					$aBrandBox = Tools::getValue('bt_brand-box');

					if (empty($aBrandBox)) {
						throw new Exception(GMerchantCenter::$oModule->l('An error occurred because you would select one brand at least', 'admin-update_class') . '.', 522);
					}
					else {
						// delete previous brands
						BT_GmcModuleDao::deleteBrands(GMerchantCenter::$iShopId);

						foreach ($aBrandBox as $iBrandId) {
							// insert
							BT_GmcModuleDao::insertBrand($iBrandId, GMerchantCenter::$iShopId);
						}
					}
					unset($aBrandBox);
				}
				unset($bExportMode);
			}

			/* USE CASE - update exclusion rules */
			// handle if we export or not products out of stock
			if (Tools::getIsset('bt_export-oos')) {
				$bExportOOSMode = Tools::getValue('bt_export-oos');
				if (!Configuration::updateValue('GMERCHANTCENTER_EXPORT_OOS', $bExportOOSMode)) {
					throw new Exception(GMerchantCenter::$oModule->l('An error occurred during export out of stock mode update', 'admin-update_class') . '.', 523);
				}
				unset($bExportOOSMode);
			}
			// handle if we export or not products without EAN code
			if (Tools::getIsset('bt_excl-no-ean')) {
				$bExportNoEan = Tools::getValue('bt_excl-no-ean');
				if (!Configuration::updateValue('GMERCHANTCENTER_EXC_NO_EAN', $bExportNoEan)) {
					throw new Exception(GMerchantCenter::$oModule->l('An error occurred during export without EAN code update', 'admin-update_class') . '.', 524);
				}
				unset($bExportNoEan);
			}
			// handle if we export or not products without manufacturer code
			if (Tools::getIsset('bt_excl-no-mref')) {
				$bExportNoMref = Tools::getValue('bt_excl-no-mref');
				if (!Configuration::updateValue('GMERCHANTCENTER_EXC_NO_MREF', $bExportNoMref)) {
					throw new Exception(GMerchantCenter::$oModule->l('An error occurred during export without manufacturer ref update', 'admin-update_class') . '.', 525);
				}
				unset($bExportNoMref);
			}
			// handle if we export products over a min price
			if (Tools::getIsset('bt_min-price')) {
				$fMinPrice = Tools::getValue('bt_min-price');
				if (!Configuration::updateValue('GMERCHANTCENTER_MIN_PRICE', (!empty($fMinPrice) ? number_format(str_replace(',', '.', $fMinPrice), 2) : 0.00))) {
					throw new Exception(GMerchantCenter::$oModule->l('An error occurred during export with a min price update', 'admin-update_class') . '.', 526);
				}
				unset($fMinPrice);
			}

			/* USE CASE - update feed data options */
			if (Tools::getIsset('bt_prod-combos')) {
				// how to export products
				$bProductCombos = Tools::getValue('bt_prod-combos');
				if (!Configuration::updateValue('GMERCHANTCENTER_P_COMBOS', $bProductCombos)) {
					throw new Exception(GMerchantCenter::$oModule->l('An error occurred during export one product or combinations update', 'admin-update_class') . '.', 527);
				}
				unset($bProductCombos);
			}

			// how to use the product desc
			if (Tools::getIsset('bt_prod-desc-type')) {
				$iProdDescType = Tools::getValue('bt_prod-desc-type');
				if (!Configuration::updateValue('GMERCHANTCENTER_P_DESCR_TYPE', $iProdDescType)) {
					throw new Exception(GMerchantCenter::$oModule->l('An error occurred during desc type update', 'admin-update_class') . '.', 528);
				}
				unset($iProdDescType);
			}

			// product availability
			if (Tools::getIsset('bt_incl-stock')) {
				$bInclStock = Tools::getValue('bt_incl-stock');
				if (!Configuration::updateValue('GMERCHANTCENTER_INC_STOCK', $bInclStock)) {
					throw new Exception(GMerchantCenter::$oModule->l('An error occurred during product availability update', 'admin-update_class') . '.', 529);
				}
				unset($bInclStock);
			}

			// include adult tag
			if (Tools::getIsset('bt_incl-tag-adult')) {
				$bInclAdultTag = Tools::getValue('bt_incl-tag-adult');
				if (!Configuration::updateValue('GMERCHANTCENTER_INC_TAG_ADULT', $bInclAdultTag)) {
					throw new Exception(GMerchantCenter::$oModule->l('An error occurred during include tag adult update', 'admin-update_class') . '.', 530);
				}
				unset($bInclAdultTag);
			}

			// include size tag
			if (Tools::getIsset('bt_incl-size')) {
				$bInclSize = Tools::getValue('bt_incl-size');
				$aSizeIds = Tools::getValue('bt_size-opt');
				if (!Configuration::updateValue('GMERCHANTCENTER_INC_SIZE', $bInclSize)) {
					throw new Exception(GMerchantCenter::$oModule->l('An error occurred during include size tag update', 'admin-update_class') . '.', 531);
				}
				// update attributes and the feature for size tag
				if (!empty($bInclSize) && !empty($aSizeIds)) {
					if (!Configuration::updateValue('GMERCHANTCENTER_SIZE_OPT', serialize($aSizeIds))) {
						throw new Exception(GMerchantCenter::$oModule->l('An error occurred during size IDs update', 'admin-update_class') . '.', 532);
					}
				}
				unset($bInclSize);
				unset($aSizeIds);
			}

			// include color tag
			if (Tools::getIsset('bt_incl-color')) {
				$sInclColor = Tools::getValue('bt_incl-color');
				$aColorIds = Tools::getValue('bt_color-opt');
				if (!Configuration::updateValue('GMERCHANTCENTER_INC_COLOR', $sInclColor)) {
					throw new Exception(GMerchantCenter::$oModule->l('An error occurred during include color tag update', 'admin-update_class') . '.', 533);
				}
				// update attributes and the feature for color tag
				if (!empty($sInclColor) && !empty($aColorIds)) {
					if (!Configuration::updateValue('GMERCHANTCENTER_COLOR_OPT', serialize($aColorIds))) {
						throw new Exception(GMerchantCenter::$oModule->l('An error occurred during color IDs update', 'admin-update_class') . '.', 534);
					}
				}
				unset($aColorIds);
				unset($bInclSize);
			}

			/* USE CASE - update apparel feed options */
			// include material tag
			if (Tools::getIsset('bt_incl-material')) {
				$bInclMaterial = Tools::getValue('bt_incl-material');
				if (!Configuration::updateValue('GMERCHANTCENTER_INC_MATER', $bInclMaterial)) {
					throw new Exception(GMerchantCenter::$oModule->l('An error occurred during include material update', 'admin-update_class') . '.', 535);
				}
				unset($bInclMaterial);
			}

			// include pattern tag
			if (Tools::getIsset('bt_incl-pattern')) {
				$bInclPattern = Tools::getValue('bt_incl-pattern');
				if (!Configuration::updateValue('GMERCHANTCENTER_INC_PATT', $bInclPattern)) {
					throw new Exception(GMerchantCenter::$oModule->l('An error occurred during include pattern update', 'admin-update_class') . '.', 536);
				}
				unset($bInclPattern);
			}

			// include gender tag
			if (Tools::getIsset('bt_incl-gender')) {
				$bInclGender = Tools::getValue('bt_incl-gender');
				if (!Configuration::updateValue('GMERCHANTCENTER_INC_GEND', $bInclGender)) {
					throw new Exception(GMerchantCenter::$oModule->l('An error occurred during include gender update', 'admin-update_class') . '.', 537);
				}
				unset($bInclGender);
			}

			// include age group tag
			if (Tools::getIsset('bt_incl-age')) {
				$bInclAge = Tools::getValue('bt_incl-age');
				if (!Configuration::updateValue('GMERCHANTCENTER_INC_AGE', $bInclAge)) {
					throw new Exception(GMerchantCenter::$oModule->l('An error occurred during include age group update', 'admin-update_class') . '.', 538);
				}
				unset($bInclAge);
			}

			/* USE CASE - update tax and shipping fees options */
			// include age group tag
			if (Tools::getIsset('bt_manage-shipping')) {
				$bShippingUse = Tools::getValue('bt_manage-shipping');
				if (!Configuration::updateValue('GMERCHANTCENTER_SHIPPING_USE', $bShippingUse)) {
					throw new Exception(GMerchantCenter::$oModule->l('An error occurred during shipping use update', 'admin-update_class') . '.', 539);
				}
				unset($bShippingUse);
			}

			if (Tools::getIsset('bt_ship-carriers')) {
				$aShippingCarriers = array();
				$aPostShippingCarriers = Tools::getValue('bt_ship-carriers');

				if (!empty($aPostShippingCarriers)
					&& is_array($aPostShippingCarriers)
				) {
					foreach ($aPostShippingCarriers as $iKey => $mVal) {
						$aShippingCarriers[$iKey] = $mVal;
					}
					$sShippingCarriers = serialize($aShippingCarriers);
				} else {
					$sShippingCarriers = '';
				}
				if (!Configuration::updateValue('GMERCHANTCENTER_SHIP_CARRIERS', $sShippingCarriers)) {
					throw new Exception(GMerchantCenter::$oModule->l('An error occurred during shipping carriers update', 'admin-update_class') . '.', 540);
				}
				unset($sShippingCarriers);
				unset($aPostShippingCarriers);
				unset($aShippingCarriers);
			}

			// update attributes and the feature for size tag
			if (Tools::getIsset('hiddenProductIds')) {
				$sExcludedIds = Tools::getValue('hiddenProductIds');

				// get an array of
				$aExcludedIds = !empty($sExcludedIds) ? explode('-', $sExcludedIds) : array();

				if (!empty($aExcludedIds)) {
					array_pop($aExcludedIds);
				}

				if (!Configuration::updateValue('GMERCHANTCENTER_PROD_EXCL', serialize($aExcludedIds))) {
					throw new Exception(GMerchantCenter::$oModule->l('An error occurred during excluded product IDs update', 'admin-update_class') . '.', 541);
				}
				unset($sExcludedIds);
				unset($aExcludedIds);
			}
			// select the order to check the EAN-13 or UPC
			if (Tools::getIsset('bt_gtin-pref')) {
				$sGtinPref = Tools::getValue('bt_gtin-pref');
				if (!Configuration::updateValue('GMERCHANTCENTER_GTIN_PREF', $sGtinPref)) {
					throw new Exception(GMerchantCenter::$oModule->l('An error occurred during gtin preference update', 'admin-update_class') . '.', 542);
				}
				unset($sGtinPref);
			}
		}
		catch (Exception $e) {
			$aData['aErrors'][] = array('msg' => $e->getMessage(), 'code' => $e->getCode());
		}

		// get configuration options
		BT_GmcModuleTools::getConfiguration(array('GMERCHANTCENTER_COLOR_OPT', 'GMERCHANTCENTER_SIZE_OPT', 'GMERCHANTCENTER_SHIP_CARRIERS', 'GMERCHANTCENTER_PROD_EXCL'));

		// require admin configure class - to factorise
		require_once(_GMC_PATH_LIB_ADMIN . 'admin-display_class.php');

		// get run of admin display in order to display first page of admin with feed management settings updated
		$aDisplay = BT_AdminDisplay::create()->run('feed');

		// use case - empty error and updating status
		$aDisplay['assign'] = array_merge($aDisplay['assign'], array(
			'bUpdate' => (empty($aData['aErrors']) ? true : false),
		), $aData);

		// destruct
		unset($aData);

		return $aDisplay;
	}

	/**
	 * _updateFeedList() method update feed list settings
	 *
	 * @param array $aPost
	 * @return array
	 */
	private function _updateFeedList(array $aPost)
	{
		// clean headers
		@ob_end_clean();

		// set
		$aData = array();

		try {
			// update cron export
			$aCronExport = Tools::getValue('bt_cron-export');

			if (!Configuration::updateValue('GMERCHANTCENTER_CHECK_EXPORT', serialize($aCronExport))) {
				throw new Exception(GMerchantCenter::$oModule->l('An error occurred during color IDs update', 'admin-update_class') . '.', 550);
			}
			unset($aCronExport);
		}
		catch (Exception $e) {
			$aData['aErrors'][] = array('msg' => $e->getMessage(), 'code' => $e->getCode());
		}

		// get configuration options
		BT_GmcModuleTools::getConfiguration(array('GMERCHANTCENTER_CHECK_EXPORT'));

		// require admin configure class - to factorise
		require_once(_GMC_PATH_LIB_ADMIN . 'admin-display_class.php');

		// get run of admin display in order to display first page of admin with feed management settings updated
		$aDisplay = BT_AdminDisplay::create()->run('feedList');

		// use case - empty error and updating status
		$aDisplay['assign'] = array_merge($aDisplay['assign'], array(
			'bUpdate' => (empty($aData['aErrors']) ? true : false),
		), $aData);

		// destruct
		unset($aData);

		return $aDisplay;
	}

	/**
	 * _updateTag() method update advanced tag settings
	 *
	 * @param array $aPost
	 * @return array
	 */
	private function _updateTag(array $aPost)
	{
		// clean headers
		@ob_end_clean();

		// set
		$aAssign = array();
		$aCategoryList = array();

		try {
			// include
			require_once(_GMC_PATH_LIB . 'module-dao_class.php');

			/* USE CASE - handle all tags configured */
			foreach ($GLOBALS[_GMC_MODULE_NAME . '_TAG_LIST'] as $sTagType) {
				if (!empty($aPost[$sTagType])
					&& is_array($aPost[$sTagType])
				) {
					foreach ($aPost[$sTagType] as $iCatId => $mVal) {
						$aCategoryList[$iCatId][$sTagType] = strip_tags($mVal);
					}
				}
			}

			// delete all features
			BT_GmcModuleDao::deleteFeatureByCat();

			if (!empty($aCategoryList)) {
				foreach ($aCategoryList as $iCatId => $aValues) {
					BT_GmcModuleDao::insertFeatureByCat($iCatId, $aValues);
				}
			}
		}
		catch (Exception $e) {
			$aAssign['aErrors'][] = array('msg' => $e->getMessage(), 'code' => $e->getCode());
		}

		// check update OK
		$aAssign['bUpdate'] = empty($aAssign['aErrors']) ? true : false;
		$aAssign['sErrorInclude'] = BT_GmcModuleTools::getTemplatePath(_GMC_PATH_TPL_NAME . _GMC_TPL_ADMIN_PATH . _GMC_TPL_ERROR);

		// force xhr mode
		GMerchantCenter::$sQueryMode = 'xhr';

		return (
			array(
				'tpl'	    => _GMC_TPL_ADMIN_PATH . _GMC_TPL_ADVANCED_TAG_UPD,
				'assign'	=> $aAssign,
			)
		);
	}

	/**
	 * _updateLabel() method update custom label settings
	 *
	 * @param array $aPost
	 * @return array
	 */
	private function _updateLabel(array $aPost)
	{
		// clean headers
		@ob_end_clean();

		// set
		$aAssign = array();

		try {
			// include
			require_once(_GMC_PATH_LIB . 'module-dao_class.php');

			// get the label name
			$sLabelName = Tools::getValue('bt_label-name');
			$iTagId = Tools::getValue('bt_tag-id');
			$sLabelType = Tools::getValue('bt_custom-type');

			// if empty label name
			if (empty($sLabelName)) {
				throw new Exception(GMerchantCenter::$oModule->l('You haven\'t filled out the label name', 'admin-update_class') . '.', 560);
			}
			else {
				// use case - update tag
				if (!empty($iTagId)) {
					BT_GmcModuleDao::updateGmcTag($iTagId, $sLabelName, $sLabelType);
					foreach ($GLOBALS[_GMC_MODULE_NAME . '_LABEL_LIST'] as $sTableName => $sFieldType) {
						// delete related tables
						BT_GmcModuleDao::deleteGmcCatTag($iTagId, $sTableName);
					}
				}
				// use case - create tag
				else {
					$iTagId = BT_GmcModuleDao::insertGmcTag(GMerchantCenter::$iShopId, $sLabelName, $sLabelType);
				}
				// use case - insert
				foreach ($GLOBALS[_GMC_MODULE_NAME . '_LABEL_LIST'] as $sTableName => $sFieldType) {
					if (Tools::getIsset('bt_'. $sFieldType . '-box')) {
						$aSelectedIds = Tools::getValue('bt_'. $sFieldType . '-box');

						foreach ($aSelectedIds as $iSelectedId) {
							BT_GmcModuleDao::insertGmcCatTag($iTagId, $iSelectedId, $sTableName, $sFieldType);
						}
					}
				}
			}

		}
		catch (Exception $e) {
			$aAssign['aErrors'][] = array('msg' => $e->getMessage(), 'code' => $e->getCode());
		}

		// check update OK
		$aAssign['bUpdate'] = empty($aAssign['aErrors']) ? true : false;
		$aAssign['sErrorInclude'] = BT_GmcModuleTools::getTemplatePath(_GMC_PATH_TPL_NAME . _GMC_TPL_ADMIN_PATH . _GMC_TPL_ERROR);

		// force xhr mode
		GMerchantCenter::$sQueryMode = 'xhr';

		return (
			array(
				'tpl'	    => _GMC_TPL_ADMIN_PATH . _GMC_TPL_GOOGLE_CUSTOM_LABEL_UPD,
				'assign'	=> $aAssign,
			)
		);
	}

	/**
	 * _updateGoogle() method update google settings
	 *
	 * @param array $aPost
	 * @return array
	 */
	private function _updateGoogle(array $aPost)
	{
		// clean headers
		@ob_end_clean();

		// set
		$aData = array();

		try {
			// add google UTM campaign
			$sUtmCampaign = Tools::getValue('bt_utm-campaign');
			if (!Configuration::updateValue('GMERCHANTCENTER_UTM_CAMPAIGN', $sUtmCampaign)) {
				throw new Exception(GMerchantCenter::$oModule->l('An error occurred during utm campaign update', 'admin-update_class') . '.', 570);
			}
			unset($sUtmCampaign);
			// add google UTM source
			$sUtmSource = Tools::getValue('bt_utm-source');
			if (!Configuration::updateValue('GMERCHANTCENTER_UTM_SOURCE', $sUtmSource)) {
				throw new Exception(GMerchantCenter::$oModule->l('An error occurred during utm source update', 'admin-update_class') . '.', 571);
			}
			unset($sUtmSource);
			// add google UTM medium
			$sUtmMedium = Tools::getValue('bt_utm-medium');
			if (!Configuration::updateValue('GMERCHANTCENTER_UTM_MEDIUM', $sUtmMedium)) {
				throw new Exception(GMerchantCenter::$oModule->l('An error occurred during utm medium update', 'admin-update_class') . '.', 572);
			}
			unset($sUtmMedium);

		}
		catch (Exception $e) {
			$aData['aErrors'][] = array('msg' => $e->getMessage(), 'code' => $e->getCode());
		}

		// get configuration options
		BT_GmcModuleTools::getConfiguration(array('GMERCHANTCENTER_COLOR_OPT', 'GMERCHANTCENTER_SIZE_OPT', 'GMERCHANTCENTER_SHIP_CARRIERS'));

		// require admin configure class - to factorise
		require_once(_GMC_PATH_LIB_ADMIN . 'admin-display_class.php');

		// get run of admin display in order to display first page of admin with feed management settings updated
		$aDisplay = BT_AdminDisplay::create()->run('google');

		// use case - empty error and updating status
		$aDisplay['assign'] = array_merge($aDisplay['assign'], array(
			'bUpdate' => (empty($aData['aErrors']) ? true : false),
		), $aData);

		// destruct
		unset($aData);

		return $aDisplay;
	}

	/**
	 * _updateGoogleCategoriesMatching() method update google categories matching
	 *
	 * @param array $aPost
	 * @return array
	 */
	private function _updateGoogleCategoriesMatching(array $aPost)
	{
		// clean headers
		@ob_end_clean();

		// set
		$aAssign = array();

		try {
			$iLangId = Tools::getValue('iLangId');
			$sLangIso = Tools::getValue('sLangIso');
			$aGoogleCategory = Tools::getValue('bt_google-cat');

			if (empty($sLangIso)
				|| !Language::getIsoById((int)$iLangId)
			) {
				throw new Exception(GMerchantCenter::$oModule->l('Invalid language parameters', 'admin-update_class') . '.', 580);
			}
			if (!is_array($aGoogleCategory)){
				throw new Exception(GMerchantCenter::$oModule->l('Your matching Google categories is not a valid array', 'admin-update_class') . '.', 581);
			}
			// include
			require_once(_GMC_PATH_LIB . 'module-dao_class.php');

			// delete previous google matching categories
			if (BT_GmcModuleDao::deleteGoogleCategory(GMerchantCenter::$iShopId, $sLangIso)) {
				foreach ($aGoogleCategory as $iShopCatId => $sGoogleCat) {
					if (!empty($sGoogleCat)) {
						// insert each category
						BT_GmcModuleDao::insertGoogleCategory(GMerchantCenter::$iShopId, $iShopCatId, $sGoogleCat, $sLangIso);
					}
				}
			}
		}
		catch (Exception $e) {
			$aAssign['aErrors'][] = array('msg' => $e->getMessage(), 'code' => $e->getCode());
		}

		// check update OK
		$aAssign['bUpdate'] = empty($aAssign['aErrors']) ? true : false;
		$aAssign['sErrorInclude'] = BT_GmcModuleTools::getTemplatePath(_GMC_PATH_TPL_NAME . _GMC_TPL_ADMIN_PATH . _GMC_TPL_ERROR);

		// force xhr mode
		GMerchantCenter::$sQueryMode = 'xhr';

		return (
			array(
				'tpl'	    => _GMC_TPL_ADMIN_PATH . _GMC_TPL_GOOGLE_CATEGORY_UPD,
				'assign'	=> $aAssign,
			)
		);
	}


	/**
	 * _updateReporting() method update reporting settings
	 *
	 * @param array $aPost
	 * @return array
	 */
	private function _updateReporting(array $aPost)
	{
		// clean headers
		@ob_end_clean();

		// set
		$aData = array();

		try {
			// register reporting mode
			$bReporting = Tools::getValue('bt_reporting');
			if (!Configuration::updateValue('GMERCHANTCENTER_REPORTING', $bReporting)) {
				throw new Exception(GMerchantCenter::$oModule->l('An error occurred during reporting update', 'admin-update_class') . '.', 590);
			}
			unset($bReporting);
		}
		catch (Exception $e) {
			$aData['aErrors'][] = array('msg' => $e->getMessage(), 'code' => $e->getCode());
		}

		// get configuration options
		BT_GmcModuleTools::getConfiguration();

		// require admin configure class - to factorise
		require_once(_GMC_PATH_LIB_ADMIN . 'admin-display_class.php');

		// get run of admin display in order to display first page of admin with feed management settings updated
		$aDisplay = BT_AdminDisplay::create()->run('reporting');

		// use case - empty error and updating status
		$aDisplay['assign'] = array_merge($aDisplay['assign'], array(
			'bUpdate' => (empty($aData['aErrors']) ? true : false),
		), $aData);

		// destruct
		unset($aData);

		return $aDisplay;
	}


	/**
	 * _updateGoogleCategoriesSync() method update the google categories by sync action
	 *
	 * @param array $aPost
	 * @return array
	 */
	private function _updateGoogleCategoriesSync(array $aPost)
	{
		// clean headers
		@ob_end_clean();

		// set
		$aAssign = array();

		try {
			// include
			require_once(_GMC_PATH_LIB . 'module-dao_class.php');

			$sLangIso = Tools::getValue('sLangIso');
			if ($sLangIso != false) {
				// Get and check content is here
				$sContent = BT_GmcModuleTools::getGoogleFile(_GMC_GOOGLE_TAXONOMY_URL . 'taxonomy.' . $sLangIso . '.txt');

				// use case - the Google file content is KO
				if (!$sContent || Tools::strlen($sContent) == 0) {
					throw new Exception(GMerchantCenter::$oModule->l('An error occurred during the Google file get content', 'admin-update_class') . '.', 591);
				}
				else {
					// Convert to array and check all is still OK
					$aLines = explode("\n", trim($sContent));

					// use case - wrong format
					if (!$aLines || !is_array($aLines)) {
						throw new Exception(GMerchantCenter::$oModule->l('The Google taxonomy file content is not formatted well', 'admin-update_class') . '.', 592);
					}
					else {
						// Delete past data
						Db::getInstance()->Execute('DELETE FROM `' . _DB_PREFIX_ . 'gmc_taxonomy` WHERE `lang` = "' . pSQL($sLangIso) . '"');

						// Re-insert
						foreach ($aLines as $index => $sLine) {
							// First line is the version number, so skip it
							if ($index > 0) {
								$sQuery = 'INSERT INTO `' . _DB_PREFIX_ . 'gmc_taxonomy` (`value`, `lang`) VALUES ("' . pSQL($sLine) . '", "' . pSQL($sLangIso) . '")';
								Db::getInstance()->Execute($sQuery);
							}
						}
					}
				}
				$aAssign['aCountryTaxonomies'] = BT_GmcModuleDao::getAvailableTaxonomyCountries($GLOBALS[_GMC_MODULE_NAME . '_AVAILABLE_COUNTRIES']);

				foreach ($aAssign['aCountryTaxonomies'] as $sIsoCode => &$aTaxonomy) {
					$aTaxonomy['countryList'] = implode(', ', $aTaxonomy['countries']);
					$aTaxonomy['currentUpdated'] = $sLangIso == $sIsoCode? true : false;
					$aTaxonomy['updated'] = BT_GmcModuleDao::checkTaxonomyUpdate($sIsoCode);
				}
			}
			else {
				throw new Exception(GMerchantCenter::$oModule->l('The server has returned an unsecure request error (wrong parameters)!', 'admin-update_class') . '.', 593);
			}
		}
		catch (Exception $e) {
			$aAssign['aErrors'][] = array('msg' => $e->getMessage(), 'code' => $e->getCode());
		}

		// check update OK
		$aAssign['bUpdate'] = empty($aAssign['aErrors']) ? true : false;
		$aAssign['sURI'] = BT_GmcModuleTools::truncateUri(array('&sAction'));
		$aAssign['sCtrlParamName'] = _GMC_PARAM_CTRL_NAME;
		$aAssign['sController'] = _GMC_ADMIN_CTRL;
		$aAssign['aQueryParams'] = $GLOBALS[_GMC_MODULE_NAME . '_REQUEST_PARAMS'];
		$aAssign['iCurrentLang'] = intval(GMerchantCenter::$iCurrentLang);
		$aAssign['sCurrentLang'] = GMerchantCenter::$sCurrentLang;

		// force xhr mode
		GMerchantCenter::$sQueryMode = 'xhr';

		return (
			array(
				'tpl'	    => _GMC_TPL_ADMIN_PATH . _GMC_TPL_GOOGLE_CATEGORY_LIST,
				'assign'	=> $aAssign,
			)
		);
	}


	/**
	 * _updateXml() method update the XML file
	 *
	 * @param array $aPost
	 * @return array
	 */
	private function _updateXml(array $aPost)
	{
		// clean headers
		@ob_end_clean();

		// set
		$aAssign = array();

		try {
			$iShopId = Tools::getValue('iShopId');
			$sFilename = Tools::getValue('sFilename');
			$iLangId = Tools::getValue('iLangId');
			$sLangIso = Tools::getValue('sLangIso');
			$sCountryIso = Tools::getValue('sCountryIso');
			$iFloor = Tools::getValue('iFloor');
			$iTotal = Tools::getValue('iTotal');
			$iProcess = Tools::getValue('iProcess');

			if (($iShopId != false && is_numeric($iShopId))
				&& ($sFilename != false && is_string($sFilename))
				&& ($iLangId != false && is_numeric($iLangId))
				&& ($sLangIso != false && is_string($sLangIso))
				&& ($sCountryIso != false && is_string($sCountryIso))
				&& ($iFloor !== false && is_numeric($iFloor))
				&& ($iTotal != false && is_numeric($iTotal))
				&& ($iProcess !== false && is_numeric($iProcess))
			) {
				$_POST['iShopId'] = $iShopId;
				$_POST['sFilename'] = $sFilename;
				$_POST['iLangId'] = $iLangId;
				$_POST['sLangIso'] = $sLangIso;
				$_POST['sCountryIso'] = Tools::strtoupper($sCountryIso);
				$_POST['iFloor'] = $iFloor;
				$_POST['iStep'] = GMerchantCenter::$aConfiguration['GMERCHANTCENTER_AJAX_CYCLE'];
				$_POST['iTotal'] = $iTotal;
				$_POST['iProcess'] = $iProcess;

				// require admin configure class - to factorise
				require_once(_GMC_PATH_LIB_ADMIN . 'admin-generate_class.php');

				// exec the generate class to generate the XML files
				$aGenerate = BT_AdminGenerate::create()->run('xml', array('reporting' => GMerchantCenter::$aConfiguration['GMERCHANTCENTER_REPORTING']));

				if (empty($aGenerate['assign']['aErrors'])) {
					$aAssign['status'] = 'ok';
					$aAssign['counter'] = $iFloor + $_POST['iStep'];
					$aAssign['process'] = $aGenerate['assign']['process'];
				}
				else {
					$aAssign['status'] = 'ko';
					$aAssign['error'] = $aGenerate['assign']['aErrors'];
				}
			}
			else {
				$sMsg = GMerchantCenter::$oModule->l('The server has returned an unsecure request error (wrong parameters)! Please check each parameter by comparing type and value below!', 'admin-update_class') . '.' ."<br/>";
				$sMsg .= GMerchantCenter::$oModule->l('Shop ID', 'admin-update_class') . ': ' . $iShopId ."<br/>"
					. GMerchantCenter::$oModule->l('File name', 'admin-update_class') . ': ' . $sFilename ."<br/>"
					. GMerchantCenter::$oModule->l('Language ID', 'admin-update_class') . ': ' . $iLangId ."<br/>"
					. GMerchantCenter::$oModule->l('Language ISO', 'admin-update_class') . ': ' . $sLangIso ."<br/>"
					. GMerchantCenter::$oModule->l('country ISO', 'admin-update_class') . ': ' . $sCountryIso ."<br/>"
					. GMerchantCenter::$oModule->l('Step', 'admin-update_class') . ': ' . $iFloor ."<br/>"
					. GMerchantCenter::$oModule->l('Total products to process', 'admin-update_class') . ': ' . $iTotal ."<br/>"
					. GMerchantCenter::$oModule->l('Total products to process (without counting combinations)', 'admin-update_class') . ': ' . $iTotal ."<br/>"
					. GMerchantCenter::$oModule->l('Stock the real number of products to process', 'admin-update_class') . ': ' . $iProcess ."<br/>";

				throw new Exception($sMsg, 594);
			}
		}
		catch (Exception $e) {
			$aAssign['status'] = 'ko';
			$aAssign['error'][] = array('msg' => $e->getMessage(), 'code' => $e->getCode());
		}

		// force xhr mode
		GMerchantCenter::$sQueryMode = 'xhr';

		return (
			array(
				'tpl'	    => _GMC_TPL_ADMIN_PATH . _GMC_TPL_FEED_GENERATE,
				'assign'	=> array('json' => BT_GmcModuleTools::jsonEncode($aAssign)),
			)
		);
	}



	/**
	 * _updateLang() method check and update lang of multi-language fields
	 *
	 * @param array $aPost : params
	 * @param string $sFieldName : field name linked to the translation value
	 * @param string $sGlobalName : name of GLOBAL variable to get value
	 * @param bool $bCheckOnly
	 * @param string $sErrorDisplayName
	 * @return array
	 */
	private function _updateLang(array $aPost, $sFieldName, $sGlobalName, $bCheckOnly = false, $sErrorDisplayName = '')
	{
		// check title in each active language
		$aLangs = array();

		foreach (Language::getLanguages() as $nKey => $aLang) {
			if (empty($aPost[$sFieldName . '_' . $aLang['id_lang']])) {
				$sException = GMerchantCenter::$oModule->l('One title of', 'admin-update_class')
					. ' " ' . (!empty($sErrorDisplayName)? $sErrorDisplayName : $sFieldName) . ' " '
					. GMerchantCenter::$oModule->l('have not been filled', 'admin-update_class')
					. '.';
				throw new Exception($sException, 595);
			}
			else {
				$aLangs[$aLang['id_lang']] = strip_tags($aPost[$sFieldName . '_' . $aLang['id_lang']]);
			}
		}
		if (!$bCheckOnly) {
			// update titles
			if (!Configuration::updateValue($sGlobalName, serialize($aLangs))) {
				$sException = GMerchantCenter::$oModule->l('An error occurred during', 'admin-update_class')
					. ' " ' . $sGlobalName . ' " '
					. GMerchantCenter::$oModule->l('update', 'admin-update_class')
					. '.';
				throw new Exception($sException, 596);
			}
		}
		return $aLangs;
	}


	/**
	 * create() method set singleton
	 * 
	 * @category admin collection
	 * @param
	 * @return obj
	 */
	public static function create()
	{
		static $oUpdate;

		if( null === $oUpdate) {
			$oUpdate = new BT_AdminUpdate();
		}
		return $oUpdate;
	}
}