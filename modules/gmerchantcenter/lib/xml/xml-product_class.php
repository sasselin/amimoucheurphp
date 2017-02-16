<?php
/**
 * xml-product_class.php file defines methods to handle a product in the data feed
 */

class BT_XmlProduct extends BT_BaseXml
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
	 * @param int $iProductId
	 * @return array
	 */
	public function hasCombination($iProductId)
	{
		return array($iProductId);
	}

	/**
	 * buildDetailProductXml() method build product XML tags
	 *
	 * @return array
	 */
	public function buildDetailProductXml() {
		// get weight
		$this->data->step->weight = (float)$this->data->p->weight;

		// handle different prices and shipping fees
		$this->data->step->price_default_currency_no_tax = Tools::convertPrice(Product::getPriceStatic((int)$this->data->p->id, false, null), $this->data->currency, false);

		// Exclude based on min price
		if (!empty(GMerchantCenter::$aConfiguration['GMERCHANTCENTER_MIN_PRICE'])
			&& ((float)$this->data->step->price_default_currency_no_tax < (float)GMerchantCenter::$aConfiguration['GMERCHANTCENTER_MIN_PRICE'])
		) {
			BT_GmcReporting::create()->set('_no_export_min_price', array('productId' => $this->data->step->id_reporting));
			return false;
		}

		$this->data->step->price_raw = Product::getPriceStatic((int)$this->data->p->id, true, null, 6);
		$this->data->step->price_raw_no_discount = Product::getPriceStatic((int)$this->data->p->id, true, null, 6, null, false, false);
		$this->data->step->price = number_format(BT_GmcModuleTools::round($this->data->step->price_raw), 2, '.', '').' '.$this->data->currency->iso_code;
		$this->data->step->price_no_discount = number_format(BT_GmcModuleTools::round($this->data->step->price_raw_no_discount), 2, '.', '').' '.$this->data->currency->iso_code;

		// shipping fees
		if (!empty(GMerchantCenter::$aConfiguration['GMERCHANTCENTER_SHIPPING_USE'])) {
			$this->data->step->shipping_fees = number_format((float)$this->getProductShippingFees((float)BT_GmcModuleTools::round($this->data->step->price_raw)), 2, '.', '') . ' ' . $this->data->currency->iso_code;
		}

		// get images
		$this->data->step->images = $this->getImages($this->data->step->id);

		// quantity
		// Do not export if the quantity is 0 for the combination and export out of stock setting is not On
		if ((int)$this->data->p->quantity < 1
			&& (int)GMerchantCenter::$aConfiguration['GMERCHANTCENTER_EXPORT_OOS'] == 0
		) {
			BT_GmcReporting::create()->set('_no_export_no_stock', array('productId' => $this->data->step->id_reporting));
			return false;
		}

		// quantity
		$this->data->step->quantity = (int)$this->data->p->quantity;

		// EAN13 or UPC
		$this->data->step->ean13 = trim($this->data->p->ean13);
		$this->data->step->upc = !empty($this->data->p->upc)? trim($this->data->p->upc) : '';

		// Exclude without EAN
		if (GMerchantCenter::$aConfiguration['GMERCHANTCENTER_EXC_NO_EAN']
			&& (empty($this->data->step->ean13) || Tools::strlen($this->data->step->ean13) < 10)
			&& (empty($this->data->step->upc) || Tools::strlen($this->data->step->upc) < 10)
		) {
			BT_GmcReporting::create()->set('_no_export_no_ean_upc', array('productId' => $this->data->step->id_reporting));
			return false;
		}

		// supplier reference
		$this->data->step->mpn = $this->getSupplierReference($this->data->p->id, $this->data->p->id_supplier, $this->data->p->supplier_reference, $this->data->p->reference);

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
		$sProdName = BT_GmcModuleTools::truncateProductTitle($iAdvancedProdName, $sProdName, $sCatName, $sManufacturerName, $iLength);

		return BT_GmcModuleTools::formatProductTitle($sProdName, $iAdvancedProdTitle);
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

		// get cover
		$aImage = Image::getCover((int)$iProdId);

		// Additional images
		$aOtherImages = Image::getImages((int)$this->aParams['iLangId'], (int)$iProdId);
		foreach ($aOtherImages as $img) {
			if ((int)$img['id_image'] != (int)$aImage['id_image'] && $iCounter <= _GMC_IMG_LIMIT) {
				$aResultImages[] = array('id_image' => (int)$img['id_image']);
				$iCounter++;
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
			$sReturnRef = BT_GmcModuleDao::getProductSupplierReference($iProdId, $iSupplierId);
		}
		elseif (!empty($sSupplierRef)) {
			$sReturnRef = $sSupplierRef;
		}

		if (empty($sReturnRef) && !empty($sProductRef)) {
			$sReturnRef = $sProductRef;
		}

		return $sReturnRef;
	}
}