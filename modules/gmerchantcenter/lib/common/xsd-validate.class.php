<?php
/**
 * xsd-validate.class.php define XsdValidate class with methods to validate a XSD flux from a XSD scheme
 * @example
 *
 * <code>
 * $oXsdValid = new XsdValidate();
 * $oXsdValid->load('/var/www/tmp/flux.xml');
 * $bXmlValid = $oXsdValid->xsdValidate('/var/www/tmp/schema.xsd');
 * if (!$bXmlValid) {
 *  print_r($oXsdValid->getXmlErrors());
 * }
 * unset($oXsdValid);
 * </code>
 */

class BT_XsdValidate
{
	/**
	 * @var obj $_oDomDocument : DOMDocument object used to validate XML document
	 */
	protected $_oDomDocument = null;

	/**
	 * @var array  $_aXmlErrors : store XML and XSD errors
	 */
	protected $_aXmlErrors = array();

	/**
	 * @var array $_aCallBack : store callback information
	 */
	protected $_aCallBack = null;

	/**
	 * @var integer $_nStartExec : start timestamp execution
	 */
	protected $_nStartExec = null;

	/**
	 * Flux's path returned returned during the callback
	 *
	 * @var string $_sCallBackDetail : XML path flux during callback execution
	 */
	protected $_sCallBackDetail = null;

	/**
	 * __construct() create an instance of DOM Document
	 */
	public function __construct()
	{
		$this->_oDomDocument = new DOMDocument();
	}

	/**
	 * load() load XML file as parameter and check if it do not include errors. If there are errors into XML flux, an exception is thrown by the getXmlErrors() method
	 *
	 * @param string $sFile : XML file to validate
	 */
	public function load($sFile)
	{
		libxml_clear_errors();
		$this->_aXmlErrors = array();

		if ($this->_aCallBack) {
			$this->_nStartExec = microtime(true);
			$this->_sCallBackDetail = $sFile;
		}
		libxml_use_internal_errors(true);
		$bLoaded = @$this->_oDomDocument->load($sFile);
		$aErrors = libxml_get_errors();
		$this->_aXmlErrors = array_merge($this->_aXmlErrors, $aErrors);

		if (!$bLoaded) {
			if ($this->_aCallBack != null) {
				call_user_func($this->_aCallBack[1], 
					array(
						'ComponentCode' => $this->_aCallBack[0],
						'Time' => microtime(true) - $this->_nStartExec,
						'Detail' => $this->_sCallBackDetail,
						'Error' => 'Error while loading file ' . $sFile,
					)
				);
			}
			throw new Exception ('Error while loading file ' . $sFile);
		}
	}

	/**
	 * loadXML() load XML content as parameter and check if it do not include errors. If there are errors into XML flux, an exception is thrown by the getXmlErrors() method
	 *
	 * @param string $sXml : XML content to validate
	 */
	public function loadXML($sXml)
	{
		libxml_clear_errors();
		$this->_aXmlErrors = array();
		if ($this->_aCallBack) {
			$this->_sCallBackDetail = 'XML';
			$this->_nStartExec = microtime(true);
		}
		libxml_use_internal_errors(true);
		$bLoaded = @$this->_oDomDocument->loadXML($sXml);
		$aErrors = libxml_get_errors();
		$this->_aXmlErrors = array_merge($this->_aXmlErrors, $aErrors);

		if (false === $bLoaded) {
			if ($this->_aCallBack != null) {
				call_user_func($this->_aCallBack[1], 
					array(
						'ComponentCode' => $this->_aCallBack[0],
						'Time' => microtime(true) - $this->_nStartExec,
						'Detail' => $this->_sCallBackDetail,
						'Error' => 'Error while loading xml',
					)
				);
			}
			throw new Exception ('Error while loading xml');
		}
		
	}

	/**
	 * xsdValidate() method validate the current XML content, previously loaded by loadXML() or load() method, via the current given XSD path
	 *
	 * @param string $sXsd : XSD path to validate
	 * @return bool
	 */
	public function xsdValidate($sXsd)
	{
		$bValidate = $this->_oDomDocument->schemaValidate($sXsd);
		$aErrors = libxml_get_errors();
		$this->_aXmlErrors = array_merge($this->_aXmlErrors, $aErrors);
		if ($this->_aCallBack != null) {
			if ($bValidate) {
				call_user_func($this->_aCallBack[1], 
					array(
						'ComponentCode' => $this->_aCallBack[0],
						'Time' => microtime(true) - $this->_nStartExec,
						'Detail' => $this->_sCallBackDetail,
					)
				);
			}
			else {
				call_user_func($this->_aCallBack[1], 
					array(
						'ComponentCode' => $this->_aCallBack[0],
						'Time' => microtime(true) - $this->_nStartExec,
						'Detail' => $this->_sCallBackDetail,
						'Error' => 'Xsd validation errors (' . count($this->_aXmlErrors) . ')',
					)
				);
			}
		}
		return $bValidate;
	}

	/**
	 * xsdValidateSource() method validate XML content , previously loaded by loadXML() or load() method, via the current given XSD resource
	 *
	 * @param string $sXsd : XML content to validate
	 * @return bool
	 */
	public function xsdValidateSource($sXsd)
	{
		libxml_use_internal_errors(true);
		$bValidate = @$this->_oDomDocument->schemaValidateSource($sXsd);
		$aErrors = libxml_get_errors();
		$this->_aXmlErrors = array_merge($this->_aXmlErrors, $aErrors);
		if ($this->_aCallBack != null) {
			if ($bValidate) {
				call_user_func($this->_aCallBack[1], 
					array(
						'ComponentCode' => $this->_aCallBack[0],
						'Time' => microtime(true) - $this->_nStartExec,
						'Detail' => $this->_sCallBackDetail,
					)
				);
			}
			else {
				call_user_func($this->_aCallBack[1], 
					array(
						'ComponentCode' => $this->_aCallBack[0],
						'Time' => microtime(true) - $this->_nStartExec,
						'Detail' => $this->_sCallBackDetail,
						'Error' => 'Xsd validation errors (' . count($this->_aXmlErrors) . ')',
					)
				);
			}
		}
		return ($bValidate);
	}

	/**
	 * getDomDocument() method returns the current DOM document reference
	 *
	 * @return DOMDocument
	 */
	protected function getDomDocument()
	{
		return ($this->_oDomDocument);
	}

	/**
	 * getXmlErrors() method returns  XMD / XSD errors as array
	 *
	 * @return array
	 */
	public function getXmlErrors()
	{
		return $this->_aXmlErrors;
	}

	/**
	 * addCallBack() method can specify callback function to execute for every validation
	 *
	 * @param array $aCallBack
	 */
	public function addCallBack($aCallBack)
	{
		$this->_aCallBack = $aCallBack;
	}

	/**
	 * create() method returns singleton
	 *
	 * @param array $aOptions
	 * @return obj
	 */
	public static function create(array $aOptions = null)
	{
		static $oXsdValidate;

		if( null === $oXsdValidate) {
			$oXsdValidate = new BT_XsdValidate($aOptions);
		}
		return $oXsdValidate;
	}
}