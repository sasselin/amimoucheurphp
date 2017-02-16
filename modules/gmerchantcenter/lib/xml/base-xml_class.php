<?php
/**
 * base-xml_class.php file defines method to handle XML obj
 */

abstract class BT_BaseXml
{
	/**
	 * @var bool $bProductProcess : define if the product has well added
	 */
	protected $bProductProcess = false;

	/**
	 * @var array $aParams : array of params
	 */
	protected $aParams = array();

	/**
	 * @var obj $data : store currency / shipping / zone / carrier / product data into this obj as properties
	 */
	protected $data = null;


	/**
	 * Magic Method __construct
	 *
	 * @param array $aParams
	 */
	protected function __construct(array $aParams = null) {
		$this->aParams = $aParams;
		$this->data = new stdClass();
	}

	/**
	 * hasCombination() method load products combination
	 *
	 * @param int $iProductId
	 * @return array
	 */
	abstract public function hasCombination($iProductId);

	/**
	 * buildDetailProductXml() method build product XML tags
	 *
	 * @return array
	 */
	abstract public function buildDetailProductXml();


	/**
	 * getImages() method get images of one product or one combination
	 *
	 * @param int $iProdId
	 * @param int $iProdAttributeId
	 * @return array
	 */
	abstract public function getImages($iProdId, $iProdAttributeId = null);

	/**
	 * getSupplierReference() method get supplier reference
	 *
	 * @param int $iProdId
	 * @param int $iSupplierId
	 * @param string $sSupplierRef
	 * @param string $sProductRef
	 * @param int $iProdAttributeId
	 * @param string $sCombiSupplierRef
	 * @param string $sCombiRef
	 * @return string
	 */
	abstract public function getSupplierReference($iProdId, $iSupplierId, $sSupplierRef = null, $sProductRef = null, $iProdAttributeId = null, $sCombiSupplierRef = null, $sCombiRef = null);

	/**
	 * formatProductName() method format the product name
	 *
	 * @param int $iAdvancedProdName
	 * @param int $iAdvancedProdTitle
	 * @param string $sProdName
	 * @param string $sCatName
	 * @param string $sManufacturerName
	 * @param int $iLength
	 * @param int $iProdAttrId
	 * @return string
	 */
	abstract public function formatProductName($iAdvancedProdName, $iAdvancedProdTitle, $sProdName, $sCatName, $sManufacturerName, $iLength, $iProdAttrId = null);


	/**
	 * setProductData() method store into the matching object the product and combination
	 *
	 * @param obj $oData
	 * @param obj $oProduct
	 * @param array $aCombination
	 * @return array
	 */
	public function setProductData(&$oData, $oProduct, $aCombination)
	{
		$this->data = $oData;
		$this->data->p = $oProduct;
		$this->data->c = $aCombination;
	}

	/**
	 * hasProductProcessed() method define if the current product has been processed or refused for some not requirements matching
	 *
	 * @return bool
	 */
	public function hasProductProcessed()
	{
		return $this->bProductProcess;
	}

	/**
	 * buildCommonProductXml() method build common product XML tags
	 *
	 * @param obj $oProduct
	 * @param array $aCombination
	 * @return true
	 */
	public function buildProductXml()
	{
		// reset the current step data obj
		$this->data->step = new stdClass();

		// define the product Id for reporting
		$this->data->step->attrId = !empty($this->data->c['id_product_attribute'])? $this->data->c['id_product_attribute'] : 0;
		$this->data->step->id_reporting = $this->data->p->id . '_' . (!empty($this->data->c['id_product_attribute'])? $this->data->c['id_product_attribute'] : 0);

		// check if there is an excluded products list
		if (!empty($this->aParams['excluded'])) {
			if ((isset($this->aParams['excluded'][$this->data->p->id])
				&& $this->data->step->attrId != 0
				&& in_array($this->data->step->attrId, $this->aParams['excluded'][$this->data->p->id]))
				|| (isset($this->aParams['excluded'][$this->data->p->id])
				&& $this->data->step->attrId == 0
				&& in_array($this->data->step->attrId, $this->aParams['excluded'][$this->data->p->id]))
				|| (isset($this->aParams['excluded'][$this->data->p->id])
				&& $this->data->step->attrId != 0
				&& !in_array($this->data->step->attrId, $this->aParams['excluded'][$this->data->p->id]))
			) {
				BT_GmcReporting::create()->set('excluded', array('productId' => $this->data->step->id_reporting));
				return false;
			}
		}

		// check qty , export type and the product name, available for order
		if (!isset($this->data->p->available_for_order)
			|| (isset($this->data->p->available_for_order)
			&& $this->data->p->available_for_order == 1)
		) {
			if (!empty($this->data->p->name)) {
				if ((int)$this->data->p->quantity > 0
					|| (int)GMerchantCenter::$aConfiguration['GMERCHANTCENTER_EXPORT_OOS'] == 1
				) {
					// get  the product category object
					$this->data->step->category = new Category((int)($this->data->p->id_category_default), (int)$this->aParams['iLangId']);

					// set the product ID
					$this->data->step->id = $this->data->p->id;

					// format product name
					$this->data->step->name = $this->formatProductName(
						GMerchantCenter::$aConfiguration['GMERCHANTCENTER_ADV_PRODUCT_NAME'],
						GMerchantCenter::$aConfiguration['GMERCHANTCENTER_ADV_PROD_TITLE'],
						$this->data->p->name,
						$this->data->step->category->name,
						$this->data->p->manufacturer_name,
						_GMC_FEED_TITLE_LENGTH,
						(!empty($this->data->c['id_product_attribute']) ? $this->data->c['id_product_attribute'] : null)
					);
					// use case export title with brands in suffix
					if (GMerchantCenter::$aConfiguration['GMERCHANTCENTER_ADV_PRODUCT_NAME'] != 0
						&& Tools::strlen($this->data->step->name) >= _GMC_FEED_TITLE_LENGTH
					) {
						BT_GmcReporting::create()->set('title_length', array('productId' => $this->data->step->id_reporting));
					}

					// set product description
					$this->data->step->desc = $this->getProductDesc($this->data->p->description_short, $this->data->p->description, $this->data->p->meta_description);

					// use case - reporting if product has no description as the merchant selected as type option
					if (empty($this->data->step->desc)) {
						BT_GmcReporting::create()->set('description', array('productId' => $this->data->step->id_reporting));
						return false;
					}

					// set product URL
					$this->data->step->url = BT_GmcModuleTools::getProductLink($this->data->p, $this->aParams['iLangId'], $this->data->step->category->link_rewrite);

					// use case - reporting if product has no valid URL
					if (empty($this->data->step->url)) {
						BT_GmcReporting::create()->set('link', array('productId' => $this->data->step->id_reporting));
						return false;
					}

					$this->data->step->url_default = $this->data->step->url;

					// format the current URL with currency or Google campaign parameters
					if (!empty(GMerchantCenter::$aConfiguration['GMERCHANTCENTER_ADD_CURRENCY'])) {
						$this->data->step->url .= (strpos($this->data->step->url, '?') !== false) ? '&gmc_currency=' . (int)$this->data->currencyId : '?gmc_currency=' . (int)$this->data->currencyId;
					}
					if (!empty(GMerchantCenter::$aConfiguration['GMERCHANTCENTER_UTM_CAMPAIGN'])) {
						$this->data->step->url .= (strpos($this->data->step->url, '?') !== false) ? '&utm_campaign=' . GMerchantCenter::$aConfiguration['GMERCHANTCENTER_UTM_CAMPAIGN'] : '?utm_campaign=' . GMerchantCenter::$aConfiguration['GMERCHANTCENTER_UTM_CAMPAIGN'];
					}
					if (!empty(GMerchantCenter::$aConfiguration['GMERCHANTCENTER_UTM_SOURCE'])) {
						$this->data->step->url .= (strpos($this->data->step->url, '?') !== false) ? '&utm_source=' . GMerchantCenter::$aConfiguration['GMERCHANTCENTER_UTM_SOURCE'] : '?utm_source=' . GMerchantCenter::$aConfiguration['GMERCHANTCENTER_UTM_SOURCE'];
					}
					if (!empty(GMerchantCenter::$aConfiguration['GMERCHANTCENTER_UTM_CAMPAIGN'])) {
						$this->data->step->url .= (strpos($this->data->step->url, '?') !== false) ? '&utm_medium=' . GMerchantCenter::$aConfiguration['GMERCHANTCENTER_UTM_MEDIUM'] : '?utm_medium=' . GMerchantCenter::$aConfiguration['GMERCHANTCENTER_UTM_MEDIUM'];
					}

					// set the product path
					$this->data->step->path = $this->getProductPath($this->data->p->id_category_default, $this->aParams['iLangId']);

					// get the condition
					$this->data->step->condition = BT_GmcModuleTools::getProductCondition((!empty($this->data->p->condition) ? $this->data->p->condition : null));

					// execute the detail part
					if ($this->buildDetailProductXml()) {
						// get the default image
						$this->data->step->image_link = BT_GmcModuleTools::getProductImage($this->data->p, (!empty(GMerchantCenter::$aConfiguration['GMERCHANTCENTER_IMG_SIZE']) ? GMerchantCenter::$aConfiguration['GMERCHANTCENTER_IMG_SIZE'] : null), $this->data->step->images['image'], GMerchantCenter::$aConfiguration['GMERCHANTCENTER_LINK']);

						// use case - reporting if product has no cover image
						if (empty($this->data->step->image_link)) {
							BT_GmcReporting::create()->set('image_link', array('productId' => $this->data->step->id_reporting));
							return false;
						}

						// get additional images
						if (!empty($this->data->step->images['others']) && is_array($this->data->step->images['others'])) {
							$this->data->step->additional_images = array();
							foreach ($this->data->step->images['others'] as $aImage) {
								$sExtraImgLink = BT_GmcModuleTools::getProductImage($this->data->p, (!empty(GMerchantCenter::$aConfiguration['GMERCHANTCENTER_IMG_SIZE']) ? GMerchantCenter::$aConfiguration['GMERCHANTCENTER_IMG_SIZE'] : null), $aImage, GMerchantCenter::$aConfiguration['GMERCHANTCENTER_LINK']);
								if (!empty($sExtraImgLink)) {
									$this->data->step->additional_images[] = $sExtraImgLink;
								}
							}
						}
						// get Google Categories
						$this->data->step->google_cat = BT_GmcModuleDao::getGoogleCategories($this->aParams['iShopId'], $this->data->p->id_category_default, $GLOBALS[_GMC_MODULE_NAME . '_AVAILABLE_COUNTRIES'][$this->aParams['sLangIso']][$this->aParams['sCountryIso']]['taxonomy']);

						//get all product categories
						$aProductCategories = $this->data->p->getCategories($this->data->p->id);

						$this->data->step->google_tags = BT_GmcModuleDao::getTagsForXml($this->data->p->id, $aProductCategories, $this->data->p->id_manufacturer, $this->data->p->id_supplier);

						// get features by category
						$this->data->step->features = BT_GmcModuleDao::getFeaturesByCategory($this->data->p->id_category_default);

						// get color options
						$this->data->step->colors = $this->getColorOptions($this->data->p->id, (int)$this->aParams['iLangId'], (!empty($this->data->c['id_product_attribute']) ? $this->data->c['id_product_attribute'] : 0));

						// get size options
						if (!empty(GMerchantCenter::$aConfiguration['GMERCHANTCENTER_INC_SIZE'])
							&& !empty(GMerchantCenter::$aConfiguration['GMERCHANTCENTER_SIZE_OPT'])
						) {
							if (is_array(GMerchantCenter::$aConfiguration['GMERCHANTCENTER_SIZE_OPT'])) {
								$sGroupAttrIds = implode(',', GMerchantCenter::$aConfiguration['GMERCHANTCENTER_SIZE_OPT']);
							} else {
								$sGroupAttrIds = (int)GMerchantCenter::$aConfiguration['GMERCHANTCENTER_SIZE_OPT'];
							}
							$this->data->step->sizes = BT_GmcModuleDao::getProductAttribute($this->data->p->id, $sGroupAttrIds, (int)$this->aParams['iLangId'], (!empty($this->data->c['id_product_attribute']) ? $this->data->c['id_product_attribute'] : 0));
						}

						// get material options
						if (!empty(GMerchantCenter::$aConfiguration['GMERCHANTCENTER_INC_MATER'])
							&& !empty($this->data->step->features['material'])
						) {
							$this->data->step->material = $this->getFeaturesOptions($this->data->step->features['material'], (int)$this->aParams['iLangId']);
						}

						// get pattern options
						if (!empty(GMerchantCenter::$aConfiguration['GMERCHANTCENTER_INC_PATT'])
							&& !empty($this->data->step->features['pattern'])
						) {
							$this->data->step->pattern = $this->getFeaturesOptions($this->data->step->features['pattern'], (int)$this->aParams['iLangId']);
						}
						return true;
					}
				} // use case - reporting if product was excluded due to no_stock
				else {
					BT_GmcReporting::create()->set('_no_export_no_stock', array('productId' => $this->data->step->id_reporting));
				}
			} // use case - reporting if product was excluded due to the empty name
			else {
				BT_GmcReporting::create()->set('_no_product_name', array('productId' => $this->data->step->id_reporting));
			}
		} // use case - reporting if product isn't available for order
		else{
			BT_GmcReporting::create()->set('_no_available_for_order', array('productId' => $this->data->step->id_reporting));
		}
		return false;
	}

	/**
	 * buildXmlTags() method build XML tags from the current stored data
	 *
	 * @return true
	 */
	public function buildXmlTags()
	{
		// set vars
		$sContent = '';
		$aReporting = array();

		$this->bProductProcess = false;

		// check if data are ok - 4 data are mandatory to fill the product out
		if (!empty($this->data->step)
			&& !empty($this->data->step->name)
			&& !empty($this->data->step->desc)
			&& !empty($this->data->step->url)
			&& !empty($this->data->step->image_link)
		) {
			$sContent .= "\t" . '<item>' . "\n"
				. "\t\t" . '<g:id>' . Tools::strtoupper(GMerchantCenter::$aConfiguration['GMERCHANTCENTER_ID_PREFIX']) . $this->aParams['sCountryIso'] . $this->data->step->id . '</g:id>' . "\n";

			// ****** PRODUCT NAME ******
			if (!empty($this->data->step->name)) {
				$sContent .= "\t\t" . '<title><![CDATA[' . BT_GmcModuleTools::cleanUp($this->data->step->name) . ']]></title>' . "\n";
			}
			else {
				$aReporting[] = 'title';
			}

			// ****** DESCRIPTION ******
			if (!empty($this->data->step->desc)) {
				$sContent .= "\t\t" . '<description><![CDATA[' . $this->data->step->desc . ']]></description>' . "\n";
			}
			else {
				$aReporting[] = 'description';
			}

			// ****** PRODUCT LINK ******
			if (!empty($this->data->step->url)) {
				$sContent .= "\t\t" . '<link><![CDATA[' . $this->data->step->url . ']]></link>' . "\n";
			}
			else {
				$aReporting[] = 'link';
			}

			// ****** IMAGE LINK ******
			if (!empty($this->data->step->image_link)) {
				$sContent .= "\t\t" . '<g:image_link><![CDATA[' . $this->data->step->image_link . ']]></g:image_link>' . "\n";
			}
			else {
				$aReporting[] = 'image_link';
			}

			// ****** PRODUCT CONDITION ******
			$sContent .= "\t\t" . '<g:condition>' . $this->data->step->condition . '</g:condition>' . "\n";

			// ****** ADDITIONAL IMAGES ******
			if (!empty($this->data->step->additional_images)) {
				foreach ($this->data->step->additional_images as $sImgLink) {
					$sContent .= "\t\t" . '<g:additional_image_link><![CDATA[' . $sImgLink . ']]></g:additional_image_link>' . "\n";
				}
			}

			// ****** PRODUCT TYPE ******
			if (!empty($this->data->step->path)) {
				$sContent .= "\t\t" . '<g:product_type><![CDATA[' . $this->data->step->path . ']]></g:product_type>' . "\n";
			}
			else {
				$aReporting[] = 'product_type';
			}

			// ****** GOOGLE MATCHING CATEGORY ******
			if (!empty($this->data->step->google_cat['txt_taxonomy'])) {
				$sContent .= "\t\t" . '<g:google_product_category><![CDATA[' . $this->data->step->google_cat['txt_taxonomy'] . ']]></g:google_product_category>' . "\n";
			}
			else {
				$aReporting[] = 'google_product_category';
			}

			// ****** GOOGLE CUSTOM LABELS ******
			if (!empty($this->data->step->google_tags['custom_label'])) {
				$iCounter = 0;
				foreach ($this->data->step->google_tags['custom_label'] as $sLabel) {
					if ($iCounter < _GMC_CUSTOM_LABEL_LIMIT) {
						$sContent .= "\t\t" . '<g:custom_label_' . $iCounter . '><![CDATA[' . $sLabel . ']]></g:custom_label_' . $iCounter . '>' . "\n";
						$iCounter++;
					}
				}
			}

			// ****** PRODUCT AVAILABILITY ******
			if (GMerchantCenter::$aConfiguration['GMERCHANTCENTER_INC_STOCK'] == 2
				|| $this->data->step->quantity > 0
			) {
				$sContent .= "\t\t" . '<g:availability>in stock</g:availability>' . "\n";
			}
			else {
				$sContent .= "\t\t" . '<g:availability>out of stock</g:availability>' . "\n";
			}

			// ****** PRODUCT PRICES ******
			if ($this->data->step->price_raw < $this->data->step->price_raw_no_discount) {
				$sContent .= "\t\t" . '<g:price>' . $this->data->step->price_no_discount . '</g:price>' . "\n"
					. "\t\t" . '<g:sale_price>' . $this->data->step->price . '</g:sale_price>' . "\n";
			}
			else {
				$sContent .= "\t\t" . '<g:price>' . $this->data->step->price . '</g:price>' . "\n";
			}

			// ****** UNIQUE PRODUCT IDENTIFIERS ******
			// ****** GTIN - EAN13 AND UPC ******
			if (GMerchantCenter::$aConfiguration['GMERCHANTCENTER_GTIN_PREF'] == 'ean') {
				if (!empty($this->data->step->ean13)
					&& (Tools::strlen($this->data->step->ean13) == 8
					|| Tools::strlen($this->data->step->ean13) == 13)
				) {
					$sContent .= "\t\t" . '<g:gtin>' . $this->data->step->ean13 . '</g:gtin>' . "\n";
				}
				elseif (!empty($this->data->step->upc)
					&& Tools::strlen($this->data->step->upc) == 12
				) {
					$sContent .= "\t\t" . '<g:gtin>' . $this->data->step->ean13 . '</g:gtin>' . "\n";
				}
				elseif (empty(GMerchantCenter::$aConfiguration['GMERCHANTCENTER_INC_ID_EXISTS'])) {
					$aReporting[] = 'gtin';
				}
			}
			// ****** GTIN - UPC AND EAN13 ******
			else {
				if (!empty($this->data->step->upc)
					&& Tools::strlen($this->data->step->upc) == 12
				) {
					$sContent .= "\t\t" . '<g:gtin>' . $this->data->step->upc . '</g:gtin>' . "\n";
				}
				elseif (!empty($this->data->step->ean13)
					&& (Tools::strlen($this->data->step->ean13) == 8
					|| Tools::strlen($this->data->step->ean13) == 13)
				) {
					$sContent .= "\t\t" . '<g:gtin>' . $this->data->step->ean13 . '</g:gtin>' . "\n";
				}
				elseif (empty(GMerchantCenter::$aConfiguration['GMERCHANTCENTER_INC_ID_EXISTS'])) {
					$aReporting[] = 'gtin';
				}
			}

			// ****** MANUFACTURER ******
			if (!empty($this->data->p->manufacturer_name)) {
				$sContent .= "\t\t" . '<g:brand><![CDATA[' . BT_GmcModuleTools::cleanUp($this->data->p->manufacturer_name) . ']]></g:brand>' . "\n";
			}
			else {
				$aReporting[] = 'brand';
			}

			// ****** MPN ******
			if (!empty($this->data->step->mpn)) {
				$sContent .= "\t\t" . '<g:mpn><![CDATA[' . $this->data->step->mpn . ']]></g:mpn>' . "\n";
			}
			elseif (empty(GMerchantCenter::$aConfiguration['GMERCHANTCENTER_INC_ID_EXISTS'])) {
				$aReporting[] = 'mpn';
			}

			// ****** IDENTIFIER EXISTS ******
			if (Tools::strlen($this->data->step->upc) != 12
				&& (Tools::strlen($this->data->step->ean13) != 8
				|| Tools::strlen($this->data->step->ean13) != 13 )
				&& (empty($this->data->step->mpn)
				|| empty($this->data->p->manufacturer_name))
			) {
				$sContent .= "\t\t" . '<g:identifier_exists>FALSE</g:identifier_exists>' . "\n";
			}

			// ****** APPAREL PRODUCTS ******
			// ****** TAG ADULT ******
			if (!empty($this->data->step->features['adult'])
				&& !empty(GMerchantCenter::$aConfiguration['GMERCHANTCENTER_INC_TAG_ADULT'])
			) {
				$sContent .= "\t\t" . '<g:adult><![CDATA[' . Tools::stripslashes(Tools::strtoupper($this->data->step->features['adult'])) . ']]></g:adult>' . "\n";
			}

			// ****** TAG GENDER ******
			if (!empty($this->data->step->features['gender'])
				&& !empty(GMerchantCenter::$aConfiguration['GMERCHANTCENTER_INC_GEND'])
			) {
				$sContent .= "\t\t" . '<g:gender><![CDATA[' . Tools::stripslashes($this->data->step->features['gender']) . ']]></g:gender>' . "\n";
			}
			elseif (!empty(GMerchantCenter::$aConfiguration['GMERCHANTCENTER_INC_GEND'])) {
				$aReporting[] = 'gender';
			}

			// ****** TAG AGE GROUP ******
			if (!empty($this->data->step->features['agegroup'])
				&& !empty(GMerchantCenter::$aConfiguration['GMERCHANTCENTER_INC_AGE'])
			) {
				$sContent .= "\t\t" . '<g:age_group><![CDATA[' . Tools::stripslashes($this->data->step->features['agegroup']) . ']]></g:age_group>' . "\n";
			}
			elseif (!empty(GMerchantCenter::$aConfiguration['GMERCHANTCENTER_INC_AGE'])) {
				$aReporting[] = 'age_group';
			}

			// ****** TAG COLOR ******
			if (!empty($this->data->step->colors)
				&& is_array($this->data->step->colors)
			) {
				foreach ($this->data->step->colors as $aColor) {
					$sContent .= "\t\t" . '<g:color><![CDATA[' . Tools::stripslashes($aColor['name']) . ']]></g:color>' . "\n";
				}
			}
			elseif (!empty(GMerchantCenter::$aConfiguration['GMERCHANTCENTER_INC_COLOR'])) {
				$aReporting[] = 'color';
			}

			// ****** TAG SIZE ******
			if (!empty($this->data->step->sizes)
				&& is_array($this->data->step->sizes)
			) {
				foreach ($this->data->step->sizes as $aSize) {
					$sContent .= "\t\t" . '<g:size><![CDATA[' . Tools::stripslashes($aSize['name']) . ']]></g:size>' . "\n";
				}
			}
			elseif (!empty(GMerchantCenter::$aConfiguration['GMERCHANTCENTER_INC_SIZE'])) {
				$aReporting[] = 'size';
			}

			// ****** VARIANTS PRODUCTS ******
			// ****** TAG MATERIAL ******
			if (!empty($this->data->step->material)) {
				$sContent .= "\t\t" . '<g:material><![CDATA[' . $this->data->step->material . ']]></g:material>' . "\n";
			}
			elseif (!empty(GMerchantCenter::$aConfiguration['GMERCHANTCENTER_INC_MATER'])) {
				$aReporting[] = 'material';
			}

			// ****** TAG PATTERN ******
			if (!empty($this->data->step->pattern)) {
				$sContent .= "\t\t" . '<g:pattern><![CDATA[' . $this->data->step->pattern . ']]></g:pattern>' . "\n";
			}
			elseif (!empty(GMerchantCenter::$aConfiguration['GMERCHANTCENTER_INC_PATT'])) {
				$aReporting[] = 'pattern';
			}

			// ****** ITEM GROUP ID ******
			if (!empty($this->data->step->id_no_combo)) {
				$sContent .= "\t\t" . '<g:item_group_id>' . Tools::strtoupper(GMerchantCenter::$aConfiguration['GMERCHANTCENTER_ID_PREFIX']) . $this->aParams['sCountryIso'] . '-' . $this->data->step->id_no_combo . '</g:item_group_id>' . "\n";
			}

			// ****** TAX AND SHIPPING ******
			$sWeightUnit = Configuration::get('PS_WEIGHT_UNIT');
			if (!empty($this->data->step->weight) && !empty($sWeightUnit)) {
				if (in_array($sWeightUnit, $GLOBALS[_GMC_MODULE_NAME . '_WEIGHT_UNITS'])) {
					$sContent .= "\t\t" . '<g:shipping_weight>' . number_format($this->data->step->weight, 2, '.', '') . ' ' . $sWeightUnit . '</g:shipping_weight>' . "\n";
				}
				else {
					$aReporting[] = 'shipping_weight';
				}
			}

			if (!empty(GMerchantCenter::$aConfiguration['GMERCHANTCENTER_SHIPPING_USE'])) {
				$sContent .= "\t\t" . '<g:shipping>' . "\n"
					. "\t\t\t" . '<g:country>' . $this->aParams['sCountryIso'] . '</g:country>' . "\n"
					. "\t\t\t" . '<g:price>' . $this->data->step->shipping_fees . '</g:price>' . "\n"
					. "\t\t" . '</g:shipping>' . "\n";
			}

			$sContent .= "\t" . '</item>' . "\n";

			$this->bProductProcess = true;
		}
		else {
			$aReporting[] = '_no_required_data';
		}

		// execute the reporting
		if (!empty($aReporting)) {
			foreach ($aReporting as $sLabel) {
				BT_GmcReporting::create()->set($sLabel, array('productId' => $this->data->step->id_reporting));
			}
		}

		return $sContent;
	}

	/**
	 * getProductPath() method returns the product path according to the category ID
	 *
	 * @param int $iProdCatId
	 * @param int $iLangId
	 * @return string
	 */
	public function getProductPath($iProdCatId, $iLangId)
	{
		if (is_string(GMerchantCenter::$aConfiguration['GMERCHANTCENTER_HOME_CAT'])) {
			GMerchantCenter::$aConfiguration['GMERCHANTCENTER_HOME_CAT'] = unserialize(GMerchantCenter::$aConfiguration['GMERCHANTCENTER_HOME_CAT']);
		}

		if ($iProdCatId == GMerchantCenter::$aConfiguration['GMERCHANTCENTER_HOME_CAT_ID']
			&& !empty(GMerchantCenter::$aConfiguration['GMERCHANTCENTER_HOME_CAT'][$iLangId])
		) {
			$sPath = Tools::stripslashes(GMerchantCenter::$aConfiguration['GMERCHANTCENTER_HOME_CAT'][$iLangId]);
		}
		else {
			$sPath = BT_GmcModuleTools::getProductPath((int)$iProdCatId, (int)$iLangId, '', false);
		}

		return $sPath;
	}

	/**
	 * loadProduct() method load products from DAO
	 *
	 * @param float $fProductPrice
	 * @return float
	 */
	public function getProductShippingFees($fProductPrice)
	{
		// set vars
		$fShippingFees = (float)0;
		$bProcess = true;

		// Free shipping on price ?
		if (((float)$this->data->shippingConfig['PS_SHIPPING_FREE_PRICE'] > 0)
			&& ((float)$fProductPrice >= (float)$this->data->shippingConfig['PS_SHIPPING_FREE_PRICE'])
		) {
			$bProcess = false;
		}
		// Free shipping on weight ?
		if (((float)$this->data->shippingConfig['PS_SHIPPING_FREE_WEIGHT'] > 0)
			&& ((float)$this->data->step->weight >= (float)$this->data->shippingConfig['PS_SHIPPING_FREE_WEIGHT'])
		) {
			$bProcess = false;
		}
		// check if the

		// only in case of not free shipping weight or price
		if ($bProcess && is_a($this->data->currentCarrier, 'Carrier')) {
			// Get shipping method - Version 1.4 / 1.5
			if (method_exists('Carrier', 'getShippingMethod')) {
				$sShippingMethod = ($this->data->currentCarrier->getShippingMethod() == Carrier::SHIPPING_METHOD_WEIGHT) ? 'weight' : 'price';
			} // Version 1.2 / 1.3
			else {
				$sShippingMethod = $this->data->shippingConfig['PS_SHIPPING_METHOD'] ? 'weight' : 'price';
			}

			// Get main shipping fee
			if ($sShippingMethod == 'weight') {
				$fShippingFees += $this->data->currentCarrier->getDeliveryPriceByWeight($this->data->step->weight, $this->data->currentZone->id);
			}
			else {
				$fShippingFees += $this->data->currentCarrier->getDeliveryPriceByPrice($fProductPrice, $this->data->currentZone->id);
			}
			unset($sShippingMethod);

			// Add product specific shipping fee for 1.4 / 1.5 only
			if (!empty(GMerchantCenter::$bCompare15)
				&& empty($this->data->currentCarrier->is_free)
			) {
				$fShippingFees += (float)BT_GmcModuleDao::getAdditionalShippingCost($this->data->p->id, $this->aParams['iShopId']);
			}
			elseif (!empty($this->data->p->additional_shipping_cost)
				&& empty($this->data->currentCarrier->is_free)
			) {
				$fShippingFees += (float)$this->data->p->additional_shipping_cost;
			}

			// Add handling fees if applicable
			if (!empty($this->data->shippingConfig['PS_SHIPPING_HANDLING'])
				&& !empty($this->data->currentCarrier->shipping_handling)
			) {
				$fShippingFees += (float)$this->data->shippingConfig['PS_SHIPPING_HANDLING'];
			}

			// Apply tax
			// Get tax rate - Version 1.4 / 1.5
			if (method_exists('Tax', 'getCarrierTaxRate')) {
				$fCarrierTax = Tax::getCarrierTaxRate((int)$this->data->currentCarrier->id);
			}
			// Version 1.2 / 1.3
			else {
				$fCarrierTax = BT_GmcModuleDao::getCarrierTaxRate($this->data->currentCarrier->id);
			}
			$fShippingFees *= (1 + ($fCarrierTax / 100));
			unset($fCarrierTax);

			// Covert to correct currency and format
			$fShippingFees = Tools::convertPrice($fShippingFees, $this->data->currency);
			$fShippingFees = number_format((float)($fShippingFees), 2, '.', '') . $this->data->currency->iso_code;
		}

		return $fShippingFees;
	}

	/**
	 * getProductDesc() method returns a cleaned desc string
	 *
	 * @param int $iProdCatId
	 * @param int $iLangId
	 * @return string
	 */
	public function getProductDesc($sShortDesc, $sLongDesc, $sMetaDesc)
	{
		// set product description
		switch (GMerchantCenter::$aConfiguration['GMERCHANTCENTER_P_DESCR_TYPE']) {
			case 1:
				$sDesc = $sShortDesc;
				break;
			case 2:
				$sDesc = $sLongDesc;
				break;
			case 3:
				$sDesc = $sShortDesc . '<br />' . $sLongDesc;
				break;
			case 4:
				$sDesc = $sMetaDesc;
				break;
			default:
				$sDesc = $sLongDesc;
				break;
		}
		return (
			(function_exists('mb_substr')? mb_substr(BT_GmcModuleTools::cleanUp($sDesc), 0, 4999) : Tools::substr(BT_GmcModuleTools::cleanUp($sDesc), 0, 4999))
		);
	}


	/**
	 * getColorOptions() method returns attributes and features
	 *
	 * @param int $iProdId
	 * @param int $iLangId
	 * @param int $iProdAttrId
	 * @return array
	 */
	public function getColorOptions($iProdId, $iLangId, $iProdAttrId = 0)
	{
		// set
		$aColors = array();

		if (!empty(GMerchantCenter::$aConfiguration['GMERCHANTCENTER_INC_COLOR'])) {
			if (!empty(GMerchantCenter::$aConfiguration['GMERCHANTCENTER_COLOR_OPT']['attribute'])) {
				$sAttributes = implode(',', GMerchantCenter::$aConfiguration['GMERCHANTCENTER_COLOR_OPT']['attribute']);
			}
			if (!empty(GMerchantCenter::$aConfiguration['GMERCHANTCENTER_COLOR_OPT']['feature'])) {
				$iFeature = implode(',', GMerchantCenter::$aConfiguration['GMERCHANTCENTER_COLOR_OPT']['feature']);
			}
			if (!empty($sAttributes)) {
				$aColors = BT_GmcModuleDao::getProductAttribute((int)$this->data->p->id, $sAttributes, (int)$iLangId, (int)$iProdAttrId);
			}

			// use case - feature selected and not empty
			if (!empty($iFeature)) {
				$sFeature = BT_GmcModuleDao::getProductFeature((int)$this->data->p->id, (int)$iFeature, (int)$iLangId);

				if (!empty($sFeature)) {
					$aColors[] = array('name' => $sFeature);
				}
				unset($sFeature);
			}
			// clear
			unset($iFeature);
			unset($sAttributes);
		}

		return $aColors;
	}

	/**
	 * getFeaturesOptions() method features for material or pattern
	 *
	 * @param int $iFeatureId
	 * @param int $iLangId
	 * @return string
	 */
	public function getFeaturesOptions($iFeatureId, $iLangId)
	{
		// set
		$aFeatureLang = array();

		// get available feature by lang
		$aFeatureAvailable = Feature::getFeature((int)$iLangId, (int)$iFeatureId);

		if (!empty($aFeatureAvailable)) {
			$aFeatureProduct = Product::getFeaturesStatic($this->data->p->id);
			foreach ($aFeatureProduct as $aFeature) {
				if ($aFeature['id_feature'] == $iFeatureId) {
					$aFeatureLang = FeatureValue::getFeatureValueLang((int)$aFeature['id_feature_value']);
				}
			}
		}

		return (
			!empty($aFeatureLang[0]['value'])? $aFeatureLang[0]['value'] : ''
		);
	}
}