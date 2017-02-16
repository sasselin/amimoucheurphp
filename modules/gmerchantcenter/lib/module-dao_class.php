<?php
/**
 * module-dao_class.php file defines method of management of DATA ACCESS OBJECT
 */

class BT_GmcModuleDao
{
	/**
	 * Magic Method __construct
	 */
	private function __construct()
	{

	}

	/**
	 * searchProducts() method search matching product names for autocomplete
	 *
	 * @param string $sSearch
	 * @param bool $bCombination
	 * @return array
	 */
	public static function searchProducts($sSearch, $bCombination = false)
	{
		$sQuery = 'SELECT p.`id_product`, pl.`name`' . ($bCombination? ',pa.`id_product_attribute`' : '')
			. ' FROM ' . _DB_PREFIX_ . 'product p'
			. (version_compare(_PS_VERSION_, '1.5', '>') ? Shop::addSqlAssociation('product', 'p', false) : '')
			. ($bCombination? ' LEFT JOIN `'._DB_PREFIX_.'product_attribute` pa ON (p.id_product = pa.id_product)' : '')
			. ' LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (p.id_product = pl.id_product ' . (version_compare(_PS_VERSION_, '1.5', '>') ? Shop::addSqlRestrictionOnLang('pl') : '') .')'
			. ' WHERE pl.name LIKE \'%' . pSQL($sSearch) . '%\' AND pl.id_lang = ' . (int)GMerchantCenter::$iCurrentLang;

		$aResult = Db::getInstance()->ExecuteS($sQuery);

		return $aResult;
	}


	/**
	 * countProducts() method count the number of product by combination or not
	 *
	 * @param int $iShopId
	 * @param bool $bCombination
	 * @return int
	 */
	public static function countProducts($iShopId, $bCombination = false)
	{
		$sQuery = 'SELECT COUNT(p.id_product) as cnt'
			. ' FROM ' . _DB_PREFIX_ . 'product p'
			. (version_compare(_PS_VERSION_, '1.5', '>') ? Shop::addSqlAssociation('product', 'p', false) : '')
			. ($bCombination? ' LEFT JOIN `'._DB_PREFIX_.'product_attribute` pa ON (p.id_product = pa.id_product)' : '')
			. ' WHERE ' . ((version_compare(_PS_VERSION_, '1.5', '>')) ? 'product_shop.active = 1'  : 'p.`active` = 1');

		$aResult = Db::getInstance()->getRow($sQuery);

		return (
			!empty($aResult['cnt'])? $aResult['cnt'] : 0
		);
	}

	/**
	 * getProductIds() method count the number of product or return all product IDs to export
	 *
	 * @param int $iShopId
	 * @param bool $bExportMode
	 * @param bool $bCountMode
	 * @param int $iFloor
	 * @param int $iStep
	 * @return mixed
	 */
	public static function getProductIds($iShopId, $bExportMode = 0, $bCountMode = false, $iFloor = null, $iStep = null)
	{
		$sQuery = 'SELECT '
			. ($bCountMode? 'COUNT(DISTINCT(p.id_product)) as cnt ' : 'DISTINCT(p.id_product) as id')
			. ' FROM ' . _DB_PREFIX_ . 'product p '
			. (version_compare(_PS_VERSION_, '1.5', '>') ? Shop::addSqlAssociation('product', 'p', false) : '')
			. (!$bExportMode? ' LEFT JOIN `'._DB_PREFIX_.'category_product` cp ON (p.id_product = cp.id_product)' : ' LEFT JOIN `'._DB_PREFIX_.'manufacturer` man ON (p.id_manufacturer = man.id_manufacturer)')
			. ' WHERE ' . ((version_compare(_PS_VERSION_, '1.5', '>')) ? 'product_shop.active = 1'  : 'p.`active` = 1')
			. ' AND '.(!$bExportMode? 'cp.`id_category`' : 'man.`id_manufacturer`').' IN (SELECT id_'.(!$bExportMode? 'category' : 'brands').' FROM `'._DB_PREFIX_.'gmc_' . (!$bExportMode? 'categories' : 'brands') . '` gc '.((version_compare(_PS_VERSION_, '1.5', '>') && Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE')) ? ' WHERE gc.`id_shop` = ' . (int)$iShopId : '') . ')';

		// range or not
		if ($iFloor !== null && !empty($iStep)) {
			$sQuery .= ' LIMIT '.$iFloor.', '.$iStep;
		}
		// count products number
		if ($bCountMode) {
			$aResult = Db::getInstance()->getRow($sQuery);

			$mReturn = $aResult['cnt']? $aResult['cnt'] : 0;
		}
		// return product IDs
		else {
			$mReturn = Db::getInstance()->ExecuteS($sQuery);
		}
		return $mReturn;
	}

	/**
	 * getProductAttribute() method returns specific attributes
	 *
	 * @param int $iProdId
	 * @param mixed $mGroupAttributeId
	 * @param int $iLangId
	 * @param int $iProdAttrId
	 * @return array
	 */
	public static function getProductAttribute($iProdId, $mGroupAttributeId, $iLangId, $iProdAttrId = 0)
	{
		$sQuery = 'SELECT distinct(al.`name`)'
			. ' FROM ' . _DB_PREFIX_ . 'product_attribute pa '
			. (!empty(GMerchantCenter::$bCompare15)? Shop::addSqlAssociation('product_attribute', 'pa', false) : '')
			. ' LEFT JOIN `'._DB_PREFIX_.'product_attribute_combination` pac ON pac.`id_product_attribute` = pa.`id_product_attribute`'
			. ' LEFT JOIN `'._DB_PREFIX_.'attribute` a ON a.`id_attribute` = pac.`id_attribute`'
			. ' LEFT JOIN `'._DB_PREFIX_.'attribute_group` ag ON ag.`id_attribute_group` = a.`id_attribute_group`'
			. ' LEFT JOIN `'._DB_PREFIX_.'attribute_lang` al ON a.`id_attribute` = al.`id_attribute`'
			. ' WHERE pa.`id_product` = '.(int)$iProdId
			. (($iProdAttrId) ? ' AND pac.`id_product_attribute` = '.(int)$iProdAttrId : '')
			. ' AND al.`id_lang` = '.(int)$iLangId
			. ' AND ag.`id_attribute_group` IN ('.$mGroupAttributeId.')'
			. ' ORDER BY al.`name`'
			. 'LIMIT 0, 30';


		$aResult = Db::getInstance()->ExecuteS($sQuery);

		return (
			!empty($aResult)? $aResult : array()
		);
	}

	/**
	 * getProductFeature() method returns specific feature
	 *
	 * @param int $iProdId
	 * @param int $iFeatureId
	 * @param int $iLangId
	 * @return string
	 */
	public static function getProductFeature($iProdId, $iFeatureId, $iLangId)
	{
		$sQuery = 'SELECT fvl.`value`'
			. ' FROM ' . _DB_PREFIX_ . 'feature_value_lang fvl '
			. ' LEFT JOIN `'._DB_PREFIX_.'feature_value` fv ON fvl.`id_feature_value` = fv.`id_feature_value`'
			. ' LEFT JOIN `'._DB_PREFIX_.'feature_product` fp ON fv.`id_feature_value` = fp.`id_feature_value`'
			. ' WHERE fp.`id_product` = '.(int)$iProdId
			. ' AND fvl.`id_lang` = '.(int)$iLangId
			. ' AND fp.`id_feature` = '.$iFeatureId;

		$aResult = Db::getInstance()->getRow($sQuery);

		return (
			!empty($aResult['value'])? $aResult['value'] : ''
		);
	}




	/**
	 * getProductCombination() method returns the product's combinations
	 *
	 * @param int $iShopId
	 * @param int $iProductId
	 * @return mixed
	 */
	public static function getProductCombination($iShopId, $iProductId)
	{

		if (!empty(GMerchantCenter::$bCompare15)) {
			$sQuery = 'SELECT *, pa.id_product_attribute, pas.id_shop, sa.`quantity` as combo_quantity'
				. ' FROM ' . _DB_PREFIX_ . 'product_attribute pa '
				. ' LEFT JOIN `'._DB_PREFIX_.'product_attribute_shop` pas ON (pa.id_product_attribute = pas.id_product_attribute AND pas.id_shop = ' . $iShopId . ')'
				. ' LEFT JOIN `'._DB_PREFIX_.'stock_available` sa ON (pas.id_product_attribute = sa.id_product_attribute AND pas.id_shop = sa.id_shop AND pas.id_shop = ' . $iShopId . ')';
		}
		else {
			$sQuery = 'SELECT *, `quantity` as combo_quantity'
				. ' FROM ' . _DB_PREFIX_ . 'product_attribute pa ';
		}
		$sQuery .= ' WHERE pa.`id_product` = '.(int)$iProductId;

		$aResult = Db::getInstance()->ExecuteS($sQuery);

		return (
			(!empty($aResult)? $aResult : false)
		);
	}

	/**
	 * getProductComboAttributes() method returns the product's combination attributes
	 *
	 * @param int $iProdAttributeId
	 * @param int $iLangId
	 * @param int $iShopId
	 * @return mixed
	 */
	public static function getProductComboAttributes($iProdAttributeId, $iLangId, $iShopId)
	{
		if (!empty(GMerchantCenter::$bCompare15)) {
			$sQuery = 'SELECT distinct(al.`name`)'
				. ' FROM `'._DB_PREFIX_.'product_attribute_shop` pa'
				. ' LEFT JOIN `'._DB_PREFIX_.'product_attribute_combination` pac ON pac.`id_product_attribute` = pa.`id_product_attribute`'
				. ' LEFT JOIN `'._DB_PREFIX_.'attribute_lang` al ON (pac.`id_attribute` = al.`id_attribute` AND al.`id_lang` = '.(int)$iLangId.')'
				. ' WHERE pac.`id_product_attribute` = '.(int)($iProdAttributeId)
				. ' AND pa.id_shop = '.(int)$iShopId;
		}
		else {
			$sQuery = 'SELECT distinct(al.`name`)'
				. ' FROM `'._DB_PREFIX_.'product_attribute` pa'
				. ' LEFT JOIN `'._DB_PREFIX_.'product_attribute_combination` pac ON pac.`id_product_attribute` = pa.`id_product_attribute`'
				. ' LEFT JOIN `'._DB_PREFIX_.'attribute_lang` al ON (pac.`id_attribute` = al.`id_attribute` AND al.`id_lang` = '.(int)$iLangId.')'
				. ' WHERE pac.`id_product_attribute` = '.(int)($iProdAttributeId);
		}
		$sQuery .= ' ORDER BY al.`name`';

		$aResult = Db::getInstance()->ExecuteS($sQuery);

		return (
			(!empty($aResult)? $aResult : false)
		);
	}

	/**
	 * getProductComboLink() method returns the product's combination link
	 *
	 * @param string $sBaseLink
	 * @param int $iProdAttributeId
	 * @param int $iLangId
	 * @param int $iShopId
	 * @return mixed
	 */
	public static function getProductComboLink($sBaseLink, $iProdAttributeId, $iLangId, $iShopId)
	{
		$sQuery = 'SELECT distinct(al.`name`), agl.`name` as group_name, a.`id_attribute`'
			. ' FROM `'._DB_PREFIX_.'product_attribute_shop` pas'
			. ' LEFT JOIN `'._DB_PREFIX_.'product_attribute_combination` pac ON pac.`id_product_attribute` = pas.`id_product_attribute`'
			. ' LEFT JOIN `'._DB_PREFIX_.'attribute` a ON pac.`id_attribute` = a.`id_attribute`'
			. ' LEFT JOIN `'._DB_PREFIX_.'attribute_lang` al ON (pac.`id_attribute` = al.`id_attribute`)'
			. ' LEFT JOIN `'._DB_PREFIX_.'attribute_group_lang` agl ON a.id_attribute_group = agl.id_attribute_group'
			. ' WHERE pac.`id_product_attribute` = '.(int)$iProdAttributeId
			. ' AND al.`id_lang` = '.(int)$iLangId
			. ' AND agl.`id_lang` = '.(int)$iLangId
			. ' AND pas.id_shop = '.(int)$iShopId
			. ' ORDER BY al.`name`';

		$aResult = Db::getInstance()->ExecuteS($sQuery);

		if (!empty($aResult)) {
			$sBaseLink .= '#/';

			foreach ($aResult as $id => $aRow) {
				$sBaseLink .= (version_compare(_PS_VERSION_, '1.6.0.13', '>=') ? $aRow['id_attribute'] . Configuration::get('PS_ATTRIBUTE_ANCHOR_SEPARATOR') : '');
				$sBaseLink .= str_replace(Configuration::get('PS_ATTRIBUTE_ANCHOR_SEPARATOR'), '_', Tools::link_rewrite($aRow['group_name']));
				$sBaseLink .= Configuration::get('PS_ATTRIBUTE_ANCHOR_SEPARATOR') . str_replace(Configuration::get('PS_ATTRIBUTE_ANCHOR_SEPARATOR'), '_', Tools::link_rewrite($aRow['name'])) . ((isset($aResult[$id+1]))? '/' : '');
			}
		}

		return $sBaseLink;
	}

	/**
	 * getHomeCategories() method returns home categories
	 *
	 * @param int $iLangId
	 * @return array
	 */
	public static function getHomeCategories($iLangId)
	{
		$sQuery = 'SELECT c.id_category, cl.name, cl.id_lang'
			. ' FROM ' . _DB_PREFIX_ . 'category c'
			. (!empty(GMerchantCenter::$bCompare15) ? Shop::addSqlAssociation('category', 'c', false) : '')
			. ' LEFT JOIN ' . _DB_PREFIX_ . 'category_lang cl ON c.id_category = cl.id_category AND cl.id_lang = ' . $iLangId . (!empty(GMerchantCenter::$bCompare15) ? Shop::addSqlRestrictionOnLang('cl'):'')
			. ' WHERE cl.id_lang = ' . $iLangId . ' AND level_depth < 2 AND c.active = 1'
			. ' ORDER BY level_depth, name';

		return (
			Db::getInstance()->ExecuteS($sQuery)
		);
	}

	/**
	 * getGmcCategories() method returns categories to export
	 *
	 * @param int $iShopId
	 * @return array
	 */
	public static function getGmcCategories($iShopId)
	{
		// set
		$aCategories = array();

		// get categories
		$aResult =  Db::getInstance()->ExecuteS('SELECT * FROM `'._DB_PREFIX_.'gmc_categories`'.(!empty(GMerchantCenter::$bCompare15) ? ' WHERE `id_shop` = '.(int)$iShopId : ''));

		if (!empty($aResult)) {
			foreach ($aResult as $aCat) {
				$aCategories[] = $aCat['id_category'];
			}
		}

		return $aCategories;
	}

	/**
	 * getGmcBrands() method returns brands to export
	 *
	 * @param int $iShopId
	 * @return array
	 */
	public static function getGmcBrands($iShopId)
	{
		// set
		$aBrands = array();

		// get brands
		$aResult =  Db::getInstance()->ExecuteS('SELECT * FROM `'._DB_PREFIX_.'gmc_brands`'.(!empty(GMerchantCenter::$bCompare15) ? ' WHERE `id_shop` = '.(int)$iShopId : ''));

		if (!empty($aResult)) {
			foreach ($aResult as $aCat) {
				$aBrands[] = $aCat['id_brands'];
			}
		}

		return $aBrands;
	}

	/**
	 * getGmcTags() method returns specific categories or brands or suppliers for one tag
	 *
	 * @param int $iShopId
	 * @param int $iTagId
	 * @param string $sTableType
	 * @param string $sFieldType
	 * @return array
	 */
	public static function getGmcTags($iShopId = null, $iTagId = null, $sTableType = null, $sFieldType = null)
	{
		// set
		$aReturn = array();

		// get specific tags
		$sQuery = 'SELECT * FROM `'._DB_PREFIX_.'gmc_tags' . ($sTableType !== null? '_' . $sTableType : '') . '` WHERE 1 = 1 '.($iShopId !== null? ' AND id_shop = '.(int)$iShopId : '') . ($iTagId !== null?' AND `id_tag` = '.(int)$iTagId : '');
		$aResult =  Db::getInstance()->ExecuteS($sQuery);

		if (!empty($aResult) && $sFieldType !== null) {
			foreach ($aResult as $aCat) {
				$aReturn[] = $aCat['id_' . $sFieldType];
			}
		}
		else {
			$aReturn = $aResult;
		}

		return $aReturn;
	}


	/**
	 * insertGmcTag() method insert a specific tag
	 *
	 * @param int $iShopId
	 * @param string $sLabelName
	 * @param string $sLabelType
	 * @return int
	 */
	public static function insertGmcTag($iShopId, $sLabelName, $sLabelType)
	{
		Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'gmc_tags` (`id_shop`, `name`, `type`) VALUES ('. $iShopId . ',"'.pSQL($sLabelName).'", "'.pSQL($sLabelType).'")');

		return Db::getInstance()->Insert_ID();
	}

	/**
	 * updateGmcTag() method update a specific tag
	 *
	 * @param int $iTagId
	 * @param string $sLabelName
	 * @param string $sLabelType
	 * @return bool
	 */
	public static function updateGmcTag($iTagId, $sLabelName, $sLabelType)
	{
		return Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'gmc_tags` SET `name` = "'.pSQL($sLabelName).'", `type` = "'.pSQL($sLabelType).'" WHERE `id_tag` = '.(int)$iTagId);
	}

	/**
	 * insertGmcCatTag() method insert categories / brands / manufacturers for a specific tag
	 *
	 * @param int $iTagId
	 * @param int $iCatId
	 * @param string $sTableName
	 * @param string $sFieldType
	 * @return int
	 */
	public static function insertGmcCatTag($iTagId, $iCatId, $sTableName, $sFieldType)
	{
		Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'gmc_tags_' . $sTableName . '` (`id_tag`, `id_'.$sFieldType.'`) VALUES ('.$iTagId.', '.$iCatId.')');
	}

	/**
	 * deleteGmcTag() method delete a specific tag
	 *
	 * @param int $iTagId
	 * @param array $aLabelList
	 * @return bool
	 */
	public static function deleteGmcTag($iTagId, array $aLabelList = null)
	{
		if (Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'gmc_tags` WHERE `id_tag` = '.(int)$iTagId)) {
			if (!empty($aLabelList)) {
				foreach ($aLabelList as $sTableName => $sFieldType) {
					self::deleteGmcCatTag($iTagId, $sTableName);
				}
			}
		}
	}

	/**
	 * deleteGmcCatTag() method delete a specific related categories / brands / manufacturers tag
	 *
	 * @param int $iTagId
	 * @param string $sTableType
	 * @return bool
	 */
	public static function deleteGmcCatTag($iTagId, $sTableType)
	{
		return Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'gmc_tags_' . $sTableType . '` WHERE `id_tag` = '.(int)$iTagId);
	}

	/**
	 * getTagsForXml() method returns Google tags for XML
	 *
	 * @param int $iProdId
	 * @param int $iDefaultProdCat
	 * @param int $iManufacturerId
	 * @param int $iSupplierId
	 * @return array
	 */
	public static function getTagsForXml($iProdId, $iDefaultProdCat, $iManufacturerId, $iSupplierId)
	{
		$sIn  = implode(",",$iDefaultProdCat);

		$sQuery = 'SELECT distinct(gt.id_tag), gt.name, gt.type, "cats" as source'
			. ' FROM `'._DB_PREFIX_.'gmc_tags` gt'
			. ' LEFT JOIN `'._DB_PREFIX_.'gmc_tags_cats` gtc ON (gt.id_tag = gtc.id_tag)'
//			. ' WHERE gtc.id_category = '.(int)$iDefaultProdCat
			. ' WHERE gtc.id_category in ( ' . $sIn . ')'
			. ' UNION'
			. ' SELECT distinct(gt.id_tag), gt.name, gt.type, "brands" as source'
			. ' FROM `'._DB_PREFIX_.'gmc_tags` gt'
			. ' LEFT JOIN `'._DB_PREFIX_.'gmc_tags_brands` gtb ON (gt.id_tag = gtb.id_tag)'
			. ' WHERE gtb.id_brand = '.(int)$iManufacturerId
			. ' UNION ';

		if (!empty(GMerchantCenter::$bCompare15)) {
			$sQuery .= ' SELECT distinct(gt.id_tag), gt.name, gt.type, "suppliers" as source'
				. ' FROM `' . _DB_PREFIX_ . 'gmc_tags` gt'
				. ' LEFT JOIN `' . _DB_PREFIX_ . 'gmc_tags_suppliers` gts ON (gt.id_tag = gts.id_tag)'
				. ' WHERE gts.id_supplier IN (SELECT distinct(id_supplier) FROM `' . _DB_PREFIX_ . 'product_supplier` WHERE id_product = ' . (int)$iProdId . ')';
		}
		else {
			$sQuery .= ' SELECT distinct(gt.id_tag), gt.name, gt.type, "suppliers" as source'
				. ' FROM `' . _DB_PREFIX_ . 'gmc_tags` gt'
				. ' LEFT JOIN `' . _DB_PREFIX_ . 'gmc_tags_suppliers` gts ON (gt.id_tag = gts.id_tag)'
				. ' WHERE gts.id_supplier = ' . (int)$iSupplierId;
		}

		$aData = Db::getInstance()->ExecuteS($sQuery);
		$aTags = array('custom_label' => array());

		if (!empty($aData) && is_array($aData))
			foreach ($aData as $row)
				if (!in_array($row['name'], $aTags[$row['type']]))
					$aTags[$row['type']][] = $row['name'];

		return $aTags;
	}

	/**
	 * insertCategory() method insert a category in our table gmc_categories
	 *
	 * @param int $iCategoryId
	 * @param int $iShopId
	 * @return bool
	 */
	public static function insertCategory($iCategoryId, $iShopId)
	{
		return Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'gmc_categories` (`id_category`, `id_shop`) values ('.(int)$iCategoryId.', '.(int)$iShopId.')');
	}

	/**
	 * insertBrand() method insert a brand in our table gmc_brands
	 *
	 * @param int $iBrandId
	 * @param int $iShopId
	 * @return bool
	 */
	public static function insertBrand($iBrandId, $iShopId)
	{
		return Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'gmc_brands` (`id_brands`, `id_shop`) values ('.(int)$iBrandId.', '.(int)$iShopId.')');
	}

	/**
	 * deleteCategories() method delete the previous selected categories
	 *
	 * @param int $iShopId
	 * @return bool
	 */
	public static function deleteCategories($iShopId)
	{
		return Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'gmc_categories`'.(!empty(GMerchantCenter::$bCompare15) ? ' WHERE `id_shop` = '.(int)$iShopId : ''));
	}

	/**
	 * deleteBrands() method delete the previous selected brands
	 *
	 * @param int $iShopId
	 * @return bool
	 */
	public static function deleteBrands($iShopId)
	{
		return Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'gmc_brands`'.(!empty(GMerchantCenter::$bCompare15) ? ' WHERE `id_shop` = '.(int)$iShopId : ''));
	}

	/**
	 * getShopCategories() method returns shop's categories
	 *
	 * @param int $iShopId
	 * @param int $iLangId
	 * @param int $iHomeCatId
	 * @return array
	 */
	public static function getShopCategories($iShopId, $iLangId, $iHomeCatId = null)
	{
		$sQuery = 'SELECT c.`id_category`, cl.`name`, cl.`id_lang` '
			. ' FROM `' . _DB_PREFIX_ . 'category` c'
			. (!empty(GMerchantCenter::$bCompare15) ? ' INNER JOIN `' . _DB_PREFIX_ . 'category_shop` cs ON (c.id_category = cs.id_category AND cs.id_shop = ' . intval($iShopId) . ') ': '')
			. ' LEFT JOIN `' . _DB_PREFIX_ . 'category_lang` cl ON (c.id_category = cl.id_category AND cl.`id_lang` = ' . $iLangId . (!empty(GMerchantCenter::$bCompare15) ? Shop::addSqlRestrictionOnLang('cl'):'') . ')'
			. (!empty(GMerchantCenter::$bCompare15) ? ' WHERE level_depth > 0' : '')
			. ' ORDER BY `level_depth`, `name`';

		$aCategories = Db::getInstance()->ExecuteS($sQuery);

		if ($iHomeCatId !== null) {
			$aTranslations =  is_string(GMerchantCenter::$aConfiguration['GMERCHANTCENTER_HOME_CAT'])? unserialize(GMerchantCenter::$aConfiguration['GMERCHANTCENTER_HOME_CAT']) : GMerchantCenter::$aConfiguration['GMERCHANTCENTER_HOME_CAT'];
		}

		foreach ($aCategories as $k => &$aCat) {
			// set category path
			$aCat['path'] = $aCat['id_category'] == $iHomeCatId? (!empty($aTranslations[$iLangId])? $aTranslations[$iLangId] : $aCat['name']) : BT_GmcModuleTools::getProductPath((int)$aCat['id_category'], $iLangId);
			$aCat['len'] = strlen($aCat['path']);

			$bHasToDelete = trim($aCat['path']);

			if (empty($bHasToDelete)) {
				unset($aCategories[$k]);
			}
		}

		return $aCategories;
	}

	/**
	 * getGoogleCategories() method returns google's categories
	 *
	 * @param int $iShopId
	 * @param int $iLangId
	 * @param string $sIsoLang
	 * @return array
	 */
	public static function getGoogleCategories($iShopId, $iCatId, $sIsoLang)
	{
		$sQuery = 'SELECT *'
			. ' FROM `'._DB_PREFIX_.'gmc_taxonomy_categories` gtc'
			. ' WHERE `id_category` = '.(int)$iCatId
			. ' AND gtc.`lang` = "'.pSQL($sIsoLang).'"'
			. ' AND id_shop = ' . (int)$iShopId;

		return Db::getInstance()->getRow($sQuery);
	}

	/**
	 * deleteGoogleCategory() method delete google categories
	 *
	 * @param int $iShopId
	 * @param string $sIsoCode
	 * @return bool
	 */
	public static function deleteGoogleCategory($iShopId, $sIsoCode)
	{
		return (
			Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'gmc_taxonomy_categories` WHERE `lang` = "'.pSQL($sIsoCode).'" AND id_shop = ' . (int)$iShopId)
		);
	}

	/**
	 * insertGoogleTaxonomy() method add google categories
	 *
	 * @param int $iShopId
	 * @param int $iShopCatId
	 * @param string $sGoogleCat
	 * @param string $sIsoCode
	 * @return bool
	 */
	public static function insertGoogleCategory($iShopId, $iShopCatId, $sGoogleCat, $sIsoCode)
	{
		return (
			Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'gmc_taxonomy_categories` VALUES ('.(int)$iShopCatId.',' .(int)$iShopId. ',"'.pSQL($sGoogleCat).'", "'.pSQL($sIsoCode).'")')
		);
	}

	/**
	 * getFeaturesByCategory() method returns features by category
	 *
	 * @param int $iCategoryId
	 * @return string
	 */
	public static function getFeaturesByCategory($iCategoryId)
	{
		$saResult = array();

		$aData = Db::getInstance()->getRow('SELECT * FROM `'._DB_PREFIX_.'gmc_features_by_cat` WHERE `id_cat` = '.(int)$iCategoryId);

		if (!empty($aData) && is_array($aData)) {
			$saResult = unserialize($aData['values']);
		}
		unset($aData);

		return $saResult;
	}

	/**
	 * deleteFeatureByCat() method delete features related to all selected categories
	 *
	 * @param int $iCategoryId
	 * @return bool
	 */
	public static function deleteFeatureByCat($iCategoryId = null)
	{
		return Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'gmc_features_by_cat` WHERE ' . ($iCategoryId !== null? '`id_cat` = '.(int)$iCategoryId : 1 ));
	}

	/**
	 * insertFeatureByCat() method insert features related to all selected categories
	 *
	 * @param int $iCategoryId
	 * @param array $aData
	 * @return bool
	 */
	public static function insertFeatureByCat($iCategoryId, $aData)
	{
		return Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'gmc_features_by_cat` VALUES('.(int)$iCategoryId.', \''.serialize($aData).'\')');
	}

	/**
	 * getAvailableTaxonomyCountries() method return available countries supported by Google
	 *
	 * @param array $aMerchantCountries
	 * @return array
	 */
	public static function getAvailableTaxonomyCountries(array $aMerchantCountries)
	{
		$aShopCountries = Country::getCountries((int)GMerchantCenter::$oContext->cookie->id_lang, false);
		$aTaxonomy = array();

		foreach ($aMerchantCountries as $sLang => $aCountries) {
			foreach ($aCountries as $sCountryIso => $aLocaleData) {
				$iLangID = Db::getInstance()->getValue('SELECT `id_lang` FROM `'._DB_PREFIX_.'lang` WHERE `active` = 1 AND `iso_code` = \''.pSQL(strtolower($sLang)).'\'');

				if (!empty($iLangID) && Currency::getIdByIsoCode($aLocaleData['currency'])) {
					$iCountryId = Country::getByIso($sCountryIso);
					$sCountryName = $aShopCountries[$iCountryId]['name'];

					if (!array_key_exists($aLocaleData['taxonomy'], $aTaxonomy)) {
						// fix for brazil
						if ($aLocaleData['taxonomy'] == 'pt-BR') {
							$iLangID = Language::getIdByIso((Language::getIdByIso('pb') ? 'pb' : 'br'));
						}
						$aTaxonomy[$aLocaleData['taxonomy']] = array();
					}
					$aTaxonomy[$aLocaleData['taxonomy']]['countries'][] = $sCountryName;
					$aTaxonomy[$aLocaleData['taxonomy']]['id_lang'] = (int)$iLangID;
				}
			}
		}

		return $aTaxonomy;
	}

	/**
	 * checkTaxonomyUpdate() method checks if the current country has already been updated
	 *
	 * @param string $sIsoCode
	 * @return bool
	 */
	public static function checkTaxonomyUpdate($sIsoCode)
	{
		$aResult = Db::getInstance()->ExecuteS('SELECT COUNT(`id_taxonomy`) as count FROM  '._DB_PREFIX_.'gmc_taxonomy WHERE lang = "'.pSQL($sIsoCode).'"');

		return (
			($aResult[0]['count'] > 1)? true : false
		);
	}

	/**
	 * deleteGoogleTaxonomy() method delete google taxonomy
	 *
	 * @param string $sIsoCode
	 * @return bool
	 */
	public static function deleteGoogleTaxonomy($sIsoCode)
	{
		return (
			Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'gmc_taxonomy` WHERE `lang` = "'.pSQL($sIsoCode).'"')
		);
	}

	/**
	 * insertGoogleTaxonomy() method add google taxonomy
	 *
	 * @param string $sText
	 * @param string $sIsoCode
	 * @return bool
	 */
	public static function insertGoogleTaxonomy($sText, $sIsoCode)
	{
		return (
			Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'gmc_taxonomy` (`value`, `lang`) VALUES ("'.pSQL($sText).'", "'.pSQL($sIsoCode).'")')
		);
	}

	/**
	 * checkActiveLanguage() method check a language as active
	 *
	 * @param string $sIsoCode
	 * @return bool
	 */
	public static function checkActiveLanguage($sIsoCode)
	{
		$aResult = Db::getInstance()->ExecuteS('SELECT * from `'._DB_PREFIX_.'lang` where `active` = 1 AND `iso_code` = "'.pSQL($sIsoCode).'"');

		return (
			!empty($aResult) && count($aResult)? true : false
		);

	}

	/**
	 * getAvailableCarriers() method returns available carriers for one country zone
	 *
	 * @param int $iCountryZone
	 * @return array
	 */
	public static function getAvailableCarriers($iCountryZone)
	{
		if (version_compare(_PS_VERSION_, '1.4.0.1', '>')) {
			$aCarriers = Carrier::getCarriers((int)GMerchantCenter::$oContext->cookie->id_lang, true, false, (int)$iCountryZone, null, 5);
		}
		else {
			$sQuery = 'SELECT c.*, cl.delay'
				. ' FROM `'._DB_PREFIX_.'carrier` c'
				. ' LEFT JOIN `'._DB_PREFIX_.'carrier_lang` cl ON (c.`id_carrier` = cl.`id_carrier` AND cl.`id_lang` = '.(int)GMerchantCenter::$oContext->cookie->id_lang.')'
				. ' LEFT JOIN `'._DB_PREFIX_.'carrier_zone` cz  ON (cz.`id_carrier` = c.`id_carrier`)'
				. ' LEFT JOIN `'._DB_PREFIX_.'zone` z  ON (cz.`id_zone` = z.`id_zone` AND z.`id_zone` = '.(int)$iCountryZone.')'
				. ' WHERE c.`deleted` = 0'
				. ' AND c.`active` = 1'
				. ' AND cz.`id_zone` = '.(int)$iCountryZone
				. ' GROUP BY c.`id_carrier`';

			$aCarriers = Db::getInstance()->ExecuteS($sQuery);
		}

		return $aCarriers;
	}

	/**
	 * getCarrierTaxRate() method returns carrier tax rate
	 *
	 * @param int $iCarrierId
	 * @return mixed : int or float
	 */
	public static function getCarrierTaxRate($iCarrierId)
	{
		$sQuery = 'SELECT rate '
			. ' FROM `'._DB_PREFIX_.'carrier` c'
			. ' LEFT JOIN `'._DB_PREFIX_.'tax` t ON (c.id_tax = t.id_tax)'
			. ' WHERE c.`id_carrier` = ';

		return Db::getInstance()->getValue($sQuery);
	}

	/**
	 * getAdditionalShippingCost() method returns the additional shipping cost
	 *
	 * @param int $iProdId
	 * @param int $iShopId
	 * @return mixed : int or float
	 */
	public static function getAdditionalShippingCost($iProdId, $iShopId)
	{
		$sQuery = 'SELECT additional_shipping_cost '
			. ' FROM `'._DB_PREFIX_.'product_shop` '
			. ' WHERE id_product = ' . $iProdId
			. ' AND id_shop = '.(int)$iShopId;

		return Db::getInstance()->getValue($sQuery);
	}

	/**
	 * getProductSupplierReference() method returns the good supplier reference
	 *
	 * @param int $iProdId
	 * @param int $iSupplierId
	 * @param int $iAttributeProdId
	 * @return string
	 */
	public static function getProductSupplierReference($iProdId, $iSupplierId, $iAttributeProdId = 0)
	{
		// set vars
		$sRefSupplier = '';

		if ($iSupplierId != 0) {
			$sRefSupplier = ProductSupplier::getProductSupplierReference($iProdId, $iAttributeProdId, $iSupplierId);

			if (empty($sRefSupplier)) {
				$sQuery = 'SELECT product_supplier_reference '
					. ' FROM `'._DB_PREFIX_.'product_supplier` as ps '
					. ' INNER JOIN `'._DB_PREFIX_.'product_attribute` as pa ON (pa.id_product_attribute = ps.id_product_attribute AND pa.default_on = 1)'
					. ' WHERE ps.id_product = ' . $iProdId
					. ' AND ps.id_supplier = '.(int)$iSupplierId;

				$sRefSupplier = Db::getInstance()->getValue($sQuery);
			}
		}
		elseif (!empty($iAttributeProdId)) {
			$sQuery = 'SELECT product_supplier_reference '
				. ' FROM `'._DB_PREFIX_.'product_supplier`'
				. ' WHERE id_product = ' . $iProdId
				. ' AND id_product_attribute = '.(int)$iAttributeProdId
				. ' AND product_supplier_reference != ""';

			$sRefSupplier = Db::getInstance()->getValue($sQuery);
		}

		return $sRefSupplier;
	}

	/**
	 * deleteGoogleTaxonomy() method delete taxonomy
	 *
	 * @param string $sIsoCode
	 * @param array $aWords
	 * @return array
	 */
	public static function autocompleteSearch($sIsoCode, array $aWords)
	{
		$sQuery = 'SELECT `value`'
			. ' FROM `'._DB_PREFIX_.'gmc_taxonomy`'
			. ' WHERE lang = "'.pSQL($sIsoCode).'" ';

		foreach ($aWords as $w) {
			$sQuery .= ' AND value LIKE \'%' . pSQL($w) . '%\'';
		}

		return (
			Db::getInstance()->ExecuteS($sQuery)
		);
	}
}