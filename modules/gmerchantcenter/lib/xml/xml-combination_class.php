<?php
/**
 * xml-combination_class.php file defines methods to handle a product in the data feed
 */

class BT_XmlCombination extends BT_BaseXml
{
	/**
	 * Magic Method __construct
	 *
	 * @param array $aParams
	 */
	public function __construct(array $aParams = null)
	{
		parent::__construct($aParams);
	}

	/**
	 * Magic Method __destruct
	 */
	public function __destruct()
	{

	}


	/**
	 * hasCombination() method load products combination
	 *
	 * @param int $iShopId
	 * @param int $iProductId
	 * @param bool $bHasAttributes
	 * @return array
	 */
	public function hasCombination($iProductId)
	{
		return (
			BT_GmcModuleDao::getProductCombination($this->aParams['iShopId'], $iProductId)
		);
	}

	/**
	 * buildDetailProductXml() method build product XML tags
	 *
	 * @return mixed
	 */
	public function buildDetailProductXml() {

		// set the product ID
		$this->data->step->id = $this->data->p->id.'v'.$this->data->c['id_product_attribute'];
		$this->data->step->id_no_combo = $this->data->p->id;

		// format the product URL for PS 1.5 and over with attribute combination
		if (!empty(GMerchantCenter::$bCompare15)
			&& !empty($this->data->step->url)
		) {
			$this->data->step->url = BT_GmcModuleDao::getProductComboLink($this->data->step->url, $this->data->c['id_product_attribute'], $this->aParams['iLangId'], $this->aParams['iShopId']);
		}

		// get weight
		$this->data->step->weight = (float)$this->data->p->weight + (float)$this->data->c['weight'];

		// handle different prices and shipping fees
		$this->data->step->price_default_currency_no_tax = Tools::convertPrice(Product::getPriceStatic((int)$this->data->p->id, false, (int)$this->data->c['id_product_attribute']), $this->data->currency, false);

		// Exclude based on min price
		if (!empty(GMerchantCenter::$aConfiguration['GMERCHANTCENTER_MIN_PRICE'])
			&& ((float)$this->data->step->price_default_currency_no_tax < (float)GMerchantCenter::$aConfiguration['GMERCHANTCENTER_MIN_PRICE'])
		) {
			BT_GmcReporting::create()->set('_no_export_min_price', array('productId' => $this->data->step->id_reporting));
			return false;
		}

		$this->data->step->price_raw = Product::getPriceStatic((int)$this->data->p->id, true, (int)$this->data->c['id_product_attribute']);
		$this->data->step->price_raw_no_discount = Product::getPriceStatic((int)$this->data->p->id, true, (int)$this->data->c['id_product_attribute'], 6, null, false, false);
		$this->data->step->price = number_format(BT_GmcModuleTools::round($this->data->step->price_raw), 2, '.', '').' '.$this->data->currency->iso_code;
		$this->data->step->price_no_discount = number_format(BT_GmcModuleTools::round($this->data->step->price_raw_no_discount), 2, '.', '').' '.$this->data->currency->iso_code;

		// shipping fees
		if (!empty(GMerchantCenter::$aConfiguration['GMERCHANTCENTER_SHIPPING_USE'])) {
			$this->data->step->shipping_fees = number_format((float)$this->getProductShippingFees((float)BT_GmcModuleTools::round($this->data->step->price_raw)), 2, '.', '') . ' ' . $this->data->currency->iso_code;
		}

		// get images
		$this->data->step->images = $this->getImages($this->data->step->id, $this->data->c['id_product_attribute']);

		// quantity
		// Do not export if the quantity is 0 for the combination and export out of stock setting is not On
		if ((int)$this->data->c['combo_quantity'] <= 0
			&& (int)GMerchantCenter::$aConfiguration['GMERCHANTCENTER_EXPORT_OOS'] == 0
		) {
			BT_GmcReporting::create()->set('_no_export_no_stock', array('productId' => $this->data->step->id_reporting));
			return false;
		}
		$this->data->step->quantity = (int)$this->data->c['combo_quantity'];

		// EAN13 or UPC
		$this->data->step->ean13 = $this->data->c['ean13'];
		$this->data->step->upc = !empty($this->data->c['upc'])? $this->data->c['upc'] : '';

		// Exclude without EAN
		if (GMerchantCenter::$aConfiguration['GMERCHANTCENTER_EXC_NO_EAN']
			&& (empty($this->data->step->ean13) || Tools::strlen($this->data->step->ean13) < 10)
			&& (empty($this->data->step->upc) || Tools::strlen($this->data->step->upc) < 10)
		) {
			BT_GmcReporting::create()->set('_no_export_no_ean_upc', array('productId' => $this->data->step->id_reporting));
			return false;
		}

		// supplier reference
		$this->data->step->mpn = $this->getSupplierReference($this->data->p->id, $this->data->p->id_supplier, $this->data->p->supplier_reference, $this->data->p->reference, (int)$this->data->c['id_product_attribute'],$this->data->c['supplier_reference'], $this->data->c['reference']);

		// exclude if mpn is empty
		if (!empty(GMerchantCenter::$aConfiguration['GMERCHANTCENTER_EXC_NO_MREF'])
			&& !GMerchantCenter::$aConfiguration['GMERCHANTCENTER_INC_ID_EXISTS']
			&& empty($this->data->step->mpn)
		) {
			BT_GmcReporting::create()->set('_no_export_no_supplier_ref', array('productId' => $this->data->step->id_reporting));
			return false;
		}

		return true;
	}

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
	public function formatProductName($iAdvancedProdName, $iAdvancedProdTitle, $sProdName, $sCatName, $sManufacturerName, $iLength, $iProdAttrId = null) {
		// get the combination attributes to format the product name
		$sProdName .= BT_GmcModuleTools::getProductCombinationName($iProdAttrId, $this->aParams['iLangId'], $this->aParams['iShopId']);

		// encode
		$sProdName = BT_GmcModuleTools::truncateProductTitle($iAdvancedProdName, $sProdName, $sCatName, $sManufacturerName, $iLength);
		$sProdName = BT_GmcModuleTools::formatProductTitle($sProdName, $iAdvancedProdTitle);

		return $sProdName;
	}


	/**
	 * getImages() method get images of one product or one combination
	 *
	 * @param int $iProdId
	 * @param int $iProdAttributeId
	 * @return array
	 */
	public function getImages($iProdId, $iProdAttributeId = null)
	{
		// set vars
		$aResultImages = array();
		$iCounter = 1;

		// get images of combination
		$aAttributeImages = Product::_getAttributeImageAssociations($iProdAttributeId);

		if (!empty($aAttributeImages) && is_array($aAttributeImages)) {
			$aImage = array('id_image' => $aAttributeImages[0]);
		}
		else {
			$aImage = Image::getCover((int)$iProdId);
		}

		// Additional images
		unset($aAttributeImages[0]);

		if (!empty($aAttributeImages) && is_array($aAttributeImages)) {
			foreach ($aAttributeImages as $k => $img) {
				if ($iCounter <= _GMC_IMG_LIMIT) {
					$aResultImages[] = array('id_image' => $img);
					$iCounter++;
				}
			}
		}

		return array('image' => $aImage, 'others' => $aResultImages);
	}

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
	public function getSupplierReference($iProdId, $iSupplierId, $sSupplierRef = null, $sProductRef = null, $iProdAttributeId = 0, $sCombiSupplierRef = null, $sCombiRef = null)
	{
		// set  vars
		$sReturnRef = '';

		// detect the MPN type
		if (!empty(GMerchantCenter::$bCompare15)) {
			$sReturnRef = BT_GmcModuleDao::getProductSupplierReference($iProdId, $iSupplierId, $iProdAttributeId);

			if (empty($sReturnRef)
				&& !empty($sCombiRef)
			) {
				$sReturnRef = $sCombiRef;
			}
		}
		elseif (!empty($sCombiSupplierRef)) {
			$sReturnRef = $sCombiSupplierRef;
		}
		elseif (!empty($sCombiRef)) {
			$sReturnRef = $sCombiRef;
		}
		elseif (!empty($sSupplierRef)) {
			$sReturnRef = $sSupplierRef;
		}
		elseif (!empty($sProductRef)) {
			$sReturnRef = $sProductRef;
		}

		return $sReturnRef;
	}
}