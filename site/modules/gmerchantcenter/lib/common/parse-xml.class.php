<?php
/**
 * parse-xml.class.php file defines methods to manage XML methods
 */

class BT_ParseXml
{
	/**
	 * @var object $_oXmlResource : store the current xml resource
	 */
	protected $_oXmlResource;

	/**
	 * @var string $_sCharset : set current charset
	 */
	protected $_sCharset;

	/**
	 * @var array $_aErrorList : store current errors as array
	 */
	protected $_aErrorList = array();

	/**
	 * @var bool $_bDecodeNamespaces : detect if need to decode namespace
	 */
	protected $_bDecodeNamespaces;

	/**
	 * @var array $_aFunctionsList : set current function to apply to any node
	 */
	protected $_aFunctionsList = array();

	/**
	 * @var string $_sFilePath : defines current file path
	 */
	protected $_sFilePath = null;

	/**
	 * @var array $_aErrorsRepair : store current errors to repai
	 */
	protected $_aErrorsRepair = array();

	/**
	 * @var array $_aAcceptEncoding : store available charset
	 */
	protected $_aAcceptEncoding = array();

	/**
	 * @var bool $_bRepairErrors : define if repair errors
	 */
	protected $_bRepairErrors = false;

	/**
	 * @var bool $_bDecodeWord : check if decode words
	 */
	protected $_bDecodeWord = false;

	/**
	 * @var array $_aCallBack : set callback to execute
	 */
	protected $_aCallBack = null;

	/**
	 * @var string $_startTime : define start time
	 */
	protected $_startTime = null;

	/**
	 * @var string $_sCallBackTitle : callback's title
	 */
	protected $_sCallBackTitle = null;

	/**
	 * __construct()
	 * 
	 * @param bool $bRepairErrors
	 */
	public function __construct($bRepairErrors = false)
	{
		$this->_bDecodeNamespaces = false;
		$this->_oXmlResource = null;
		$this->_aAcceptEncoding = array('UTF-8','ISO-8859-1','WINDOWS-1252','UTF-16');
		$this->_bRepairErrors = $bRepairErrors;
	}

	/**
	 * __destruct()
	 */
	public function __destruct() {}

	/**
	 * addCallBack() method add callback functions to any parse execution
	 *
	 * @param array $aCallBack
	 */
	public function addCallBack($aCallBack)
	{
		$this->_aCallBack = $aCallBack;
	}

	/**
	 * _doCallBack() method execute callback function if declared
	 * 
	 * @param string $sError
	 */
	protected function _doCallBack($sError = null)
	{
		if ($this->_aCallBack != null) {
			if (null == $sError) {
				call_user_func($this->_aCallBack[1], 
					array(
						'ComponentCode' => $this->_aCallBack[0],
						'Time' => microtime(true) - $this->_startTime,
						'Detail' => $this->_sCallBackTitle
					)
				);
			}
			else {
				call_user_func($this->_aCallBack[1], 
					array(
						'ComponentCode' => $this->_aCallBack[0],
						'Time' => microtime(true) - $this->_startTime,
						'Detail' => $this->_sCallBackTitle,
						'Error' => $sError
					)
				);
			}
		}
	}

	/**
	 * decodeWordChars() method defines if decode words action need to be executed
	 *
	 * @param bool $bDecode
	 */
	public function decodeWordChars($bDecode)
	{
		$this->_bDecodeWord = $bDecode;
	}

	/**
	 * addFunction() method allow to apply specific functions to any nodes
	 */
	public function addFunction()
	{
		// get function args
		$aParamsList = func_get_args();

		// add each function to protected array
		foreach ($aParamsList as $sFunctionName) {
			$this->_aFunctionsList[] = $sFunctionName;
		}
	}

	/**
	 * _encodeResult() method apply encoding process before returning each node's value .
	 *
	 * @param string $sValue
	 * @return string
	 */
	protected function _encodeResult($sValue)
	{
		// verify if have to decode word chars
		if ($this->_bDecodeWord) {
			$aConvert = array(
				"…" => "..."
				,"‘" => "'"
				,"„" => '"'
				,"‚" => "'"
				,"’" => "'"
				,"“" => '"'
				,"�?" => '"'
				,"œ" => 'oe'
				,"›" => ')'
				,"™" => '[TM]'
				,"—" => '-'
				,"–" => '-'
				,"•" => '-'
				,"Œ" => 'OE'
				,"‹" => '('
				,"‡" => '[xx]'
				,"�" => "[x]"
				,"ƒ" => "[F]"
				,"€" => "Euros"
				,"ˆ" => ''
				,"˜" => ''
			);
			$sValue = strtr($sValue, $aConvert);
		}

		// if encoding charset is specified
		if (!is_null($this->_sCharset)) {// && mb_detect_encoding($sValue, "ASCII,JIS,UTF-8,EUC-JP,SJIS") != $this->_sCharset) {
			// convert charset to asked
			$sValue = @mb_convert_encoding($sValue, $this->_sCharset, 'auto');
		}

		// if no encoding charset specified, return original value
		return $sValue;
	}

	/**
	 * setResultCharset() method define return charset
	 *
	 * @param string $sCharset
	 */
	public function setResultCharset($sCharset)
	{
		$this->_sCharset = $sCharset;
	}

	/**
	 * decodeNamespaces() method define to activate decoding namespace to the current parser.
	 *
	 * @param bool $bDecode
	 */
	public function decodeNamespaces($bDecode = true)
	{
		$this->_bDecodeNamespaces = $bDecode;
	}

	/**
	 * getErrors() method return found errors when XML feed is not valid
	 *
	 * @return array
	 */
	public function getErrors()
	{
		// return error list
		return $this->_aErrorList;
	}

	/**
	 * getRepairedErrors() method return errors to repair as array
	 *
	 * @return array
	 */
	public function getRepairedErrors()
	{
		// return error list
		return $this->_aErrorsRepair;
	}

	/**
	 * loadXml() method read current XML feed as passed string parameter
	 *
	 * @param string $sXml
	 * @param int $iLoadOption = LIBXML_NOCDATA
	 */
	public function loadXml($sXml, $iLoadOption = LIBXML_NOCDATA)
	{
		if ($this->_aCallBack != null) {
			$this->_startTime = microtime(true);
			$this->_sCallBackTitle = 'xml content';
		}
		// clear file path
		$this->_sFilePath = '';

		// call _loadXmlWithErrors method
		if ($this->_bRepairErrors) {
			$this->_oXmlResource = $this->_loadXmlWithErrors($sXml, null, $iLoadOption);
		}
		else {
			$this->_oXmlResource = $this->_loadXml($sXml, $iLoadOption);
		}
	}

	/**
	 *  loadFile() method read XML data deed from a physical path
	 *
	 * @param string $sFilePath
	 * @param int $iLoadOption = LIBXML_NOCDATA
	 */
	public function loadFile($sFilePath, $iLoadOption = LIBXML_NOCDATA)
	{
		if ($this->_aCallBack != null) {
			$this->_startTime = microtime(true);
			$this->_sCallBackTitle = $sFilePath;
		}

		// clear file path
		$this->_sFilePath = $sFilePath;
		$bLocalFile = true;

		if (strpos(strtolower($sFilePath), 'http://') == 0
			|| strpos(strtolower($sFilePath), 'https://') == 0
			|| strpos(strtolower($sFilePath), 'ftp://') == 0) {
			$bLocalFile = false;
		}
		// specify using error report
		libxml_use_internal_errors(true);

		// test if file exist
		if ($bLocalFile && !file_exists($sFilePath)) {
			$this->_doCallBack('Specified file not found (' . $this->_sFilePath . ')');
			throw new Exception('Specified file not found (' . $this->_sFilePath . ')', 201);
		}

		// test if file is readable
		if ($bLocalFile && !is_readable($sFilePath)) {
			$this->_doCallBack('Specified file can\'t be read (' . $this->_sFilePath . ')');
			throw new Exception('Specified file can\'t be read (' . $this->_sFilePath . ')', 202);
		}

		// get content
		if ($bLocalFile) {
			$fp =  @fopen($sFilePath, 'r');

			if (false === $fp) {
				$this->_doCallBack('Unable to load file (' . $this->_sFilePath . ')');
				throw new Exception('Unable to load file (' . $this->_sFilePath . ')', 201);
			}
			$sFileContent = @fread($fp, filesize($sFilePath));
			@fclose($fp);
		}
		else {
			$fileContent = @file_get_contents($sFilePath);
		}

		if ($sFileContent === false) {
			$this->_doCallBack('Unable to load file (' . $this->_sFilePath . ')');
			throw new Exception('Unable to load file (' . $this->_sFilePath . ')', 201);
		}

		// call _loadXmlWithErrors method
		if ($this->_bRepairErrors) {
			$this->_oXmlResource = $this->_loadXmlWithErrors($sFileContent, null, $iLoadOption);
		}
		else {
			$this->_oXmlResource = $this->_loadXml($sFileContent, $iLoadOption);
		}
	}

	/**
	 * _loadXml() method manage XML feed reading. Called by loadXml() et loadFile()
	 *
	 * @param string $sXml
	 * @param int $iLoadOption = LIBXML_NOCDATA
	 * @return obj $oXml
	 */
	protected function _loadXml($sXml, $iLoadOption = LIBXML_NOCDATA)
	{
		// verify if not empty XML
		if (empty($sXml)) {
			$this->_doCallBack("Empty Xml " . (empty($this->_sFilePath) ? '' : ' (' . $this->_sFilePath . ')'));

			throw new Exception("Empty Xml " . (empty($this->_sFilePath) ? '' : ' (' . $this->_sFilePath . ')'), 203);
		}
		// specify using error report
		libxml_use_internal_errors(true);

		// load xml
		$oXml = @simplexml_load_string($sXml, null, $iLoadOption);

		// test if error(s)
		if (false === $oXml) {
			$this->_aErrorList = libxml_get_errors();
			$errors = '';

			foreach ($this->_aErrorList as $err) {
				$errors .= 'Code : ' . $err->code . ', line : ' . $err->line
					. ', column : ' . $err->column . ', Message : ' . $err->message;
			}

			// send exception
			$this->_doCallBack("Bad Xml format "
				. (empty($this->_sFilePath) ? '' : ' (' . $this->_sFilePath . ')')
				. " \n" . $errors);

			throw new Exception("Bad Xml format "
				. (empty($this->_sFilePath) ? '' : ' (' . $this->_sFilePath . ')')
				. " \n" . $errors, 203);
		}

		// return
		return $oXml;
	}

	/**
	 * _loadXmlWithErrors() method manage XML feed reading by trying to solve current errors. Called by loadXml() et loadFile()
	 *
	 * @param string $sXml
	 * @param string $sPrevError = null
	 * @param int $iLoadOption = LIBXML_NOCDATA
	 *
	 * @return obj $oXml
	 */
	protected function _loadXmlWithErrors($sXml, $sPrevError = null, $iLoadOption = LIBXML_NOCDATA)
	{
		// verify if not empty XML
		if (empty($sXml)) {
			$this->_doCallBack("Empty Xml "
				. (empty($this->_sFilePath) ? '' : ' (' . $this->_sFilePath . ')'));
			throw new Exception("Empty Xml "
				. (empty($this->_sFilePath) ? '' : ' (' . $this->_sFilePath . ')'), 203);
		}
		// replace bad char
		$aXmlLines = explode("\n", $sXml);

		// load xml encoding
		$sXmlEncoding = $this->_aAcceptEncoding[0];
		$posEncoding = strpos(strtoupper($aXmlLines[0]), 'ENCODING');

		if (is_int($posEncoding)) {
			foreach ($this->_aAcceptEncoding as $posEncoding) {
				if (is_int(strpos(strtoupper($aXmlLines[0]), strtoupper($posEncoding)))) {
					$sXmlEncoding = $posEncoding;
					break;
				}
			}
		}

		// replace RC char
		$sXml = str_replace(array("\n\r","\r\n","\r"), "\n", $sXml);

		// specify using error report
		libxml_use_internal_errors(true);

		// load xml
		$oXml = @simplexml_load_string($sXml, null, $iLoadOption);

		// test if error(s)
		if (false === $oXml) {
			// get errors
			$aErrorList = libxml_get_errors();

			if (isset($aErrorList[0])) {
				// get first error
				$oError = $aErrorList[0];

				// get md5 of error
				$sErrorMd5 = md5(print_r($oError,true));

				// check if not same error than previous
				$bNewError = !($sErrorMd5 == $sPrevError);

				if ($bNewError && $oError->code == 9 && is_int($oError->column) && is_int($oError->line)) {
					// Input is not proper UTF-8, indicate encoding ! Bytes: 0xE9 0x5D 0x20 0x63
					// get mb_string difference
					$mbSize = strlen(mb_substr($aXmlLines[$oError->line-1], 0, $oError->column-1, $sXmlEncoding));
					$strSize = strlen(substr($aXmlLines[$oError->line-1], 0, $oError->column-1));
					// recalculate column step
					if ($mbSize != $strSize) {
						$oError->column = $oError->column + ($mbSize-$strSize);
					}
					// utf8 encode only specified char
					$aXmlLines[$oError->line-1] = substr($aXmlLines[$oError->line-1], 0, $oError->column-1)
						. utf8_encode(substr($aXmlLines[$oError->line-1], $oError->column-1, 1))
						. substr($aXmlLines[$oError->line-1], $oError->column);
					// store error � repaired
					$this->_aErrorsRepair[] = $oError;
					// clear errors
					libxml_clear_errors();
					// recursive call
					return($this->_loadXmlWithErrors(join("\n", $aXmlLines), $sErrorMd5, $iLoadOption));
				}
				elseif ($bNewError && $oError->code == 68 && is_int($oError->column) && is_int($oError->line)) {
					// xmlParseEntityRef: no name
					// get mb_string difference
					$mbSize = strlen(mb_substr($aXmlLines[$oError->line-1], 0, $oError->column-1, $sXmlEncoding));
					$strSize = strlen(substr($aXmlLines[$oError->line-1], 0, $oError->column-1));
					// recalculate column step
					if ($mbSize != $strSize) {
						$oError->column = $oError->column + ($mbSize-$strSize);
					}
					$oError->column--;
					// html encode only specified char
					$aXmlLines[$oError->line-1] = substr($aXmlLines[$oError->line-1], 0, $oError->column-1)
						. htmlspecialchars(substr($aXmlLines[$oError->line-1], $oError->column-1, 1))
						. substr($aXmlLines[$oError->line-1], $oError->column);
					// store error � repaired
					$this->_aErrorsRepair[] = $oError;
					// clear errors
					libxml_clear_errors();
					// recursive call
					return($this->_loadXmlWithErrors(join("\n", $aXmlLines), $sErrorMd5, $iLoadOption));
				}
				elseif ($bNewError && $oError->code == 73 && is_int($oError->column) && is_int($oError->line)) {
					// expected '>'
				}
				elseif ($bNewError && $oError->code == 76 && is_int($oError->column) && is_int($oError->line)) {
					// Opening and ending tag mismatch
				}
				// send exception if error not repaired
				// list errors in exception
				$this->_aErrorList = libxml_get_errors();
				$sErrors = '';

				foreach ($this->_aErrorList as $err) {
					$sErrors .= 'Code : ' . $err->code . ', line : ' . $err->line
						. ', column : ' . $err->column . ', Message : ' . $err->message;
				}
				// send exception
				$this->_doCallBack("Bad Xml format "
					. (empty($this->_sFilePath) ? '' : ' (' . $this->_sFilePath . ')')
					. " \n" . $sErrors);

				throw new Exception("Bad Xml format "
					. (empty($this->_sFilePath) ? '' : ' (' . $this->_sFilePath . ')')
					. " \n" . $sErrors, 203);
			}
		}
		return $oXml;
	}

	/**
	 * parse() method parse data feed according to an array of parameters to return another array
	 *
	 * @param array $aParameters
	 * @return array
	 */
	public function parse($aParameters)
	{
		// verify if some xml is loaded
		if (empty($this->_oXmlResource)) {
			$this->_doCallBack('Can\'t parse empty xml');
			throw new Exception('Can\'t parse empty xml', 204);
		}
		// analyse parameters
		$aParams = $this->_translateParameters($aParameters);
		// parse xml from recursive array
		$aParsed = $this->_parseXml($aParams, $this->_oXmlResource);
		$this->_doCallBack();

		return $aParsed;
	}

	/**
	 * _parseXml() execute entire parsing of XML feed
	 *
	 * @param array $aParameters
	 * @param resource $oXmlRsc
	 * @return array
	 */
	protected function _parseXml ($aParameters, $oXmlRsc) {
		// create empty array for result
		$aReturn = array();

		foreach ($aParameters as $key => $val) {
			// create empty entry in result array
			$aReturn[$key] = null;

			// create empty result key
			$aResult = null;

			// load xpath into temporary array
			$datasFromXPath = $oXmlRsc->xpath($val['xPath']);

			// no datas founds
			if (empty($datasFromXPath)) {
				if ($val['isArray']) {
					$aResult = array();
				}
			}
			else {
				// evaluate res
				foreach ($datasFromXPath as $item) {
					$txt = null;
					if (empty($val['attribute'])) { // basic node content
						// get xml tag name
						$tagName = $val['xPath'];
						// clear selector
						$selectorP = strpos($tagName, '[');
						while (is_int($selectorP)) {
							$tagName = substr($tagName, 0, $selectorP) . substr($tagName, strpos($tagName, ']')+1);
							$selectorP = strpos($tagName, '[');
						}
						if (is_integer(strrpos($tagName, '/'))) {
							$tagName = substr($tagName, strrpos($tagName, "/")+1);
						}
						if (is_integer(strrpos($tagName, '['))) {
							$tagName = substr($tagName, 0, strrpos($tagName, '['));
						}
						$tpsTags = explode('|', $tagName);
						foreach ($tpsTags as &$tag) {
							$tag = trim($tag);
						}
						$tpsTags = array_unique($tpsTags);
						$txt = (string)$item->asXML();
						foreach ($tpsTags as $tagOr) {
							$txt = preg_replace('/<' . $tagOr . '[^>]*>/','',
								$this->_encodeResult($txt));
							$txt = trim(str_replace('</' . $tagOr . '>','', $txt));
						}
						unset($tpsTags);
					}
					elseif (isset($item[$val['attribute']])) { // attribute asked
						$txt = $this->_encodeResult((string)$item[$val['attribute']]);
					}

					// decode namespaces
					if ($this->_bDecodeNamespaces) {
						$namespaces = $oXmlRsc->getNamespaces(true);
						// replace each namespaces
						foreach ($namespaces as $ns => $url) {
							$txt = str_replace(array("<" . (string)$ns . ":", "</" . (string)$ns . ":"),
								array("<", "</"), $txt);
						}
					}
					// paste g�n�ral user function
					$this->_executeFunctions($this->_aFunctionsList, $txt);
					// paste user's function
					$this->_executeFunctions($val['userFunctions'], $txt);

					if (empty($val['arrayKeyPath'])) { // no array key specified
						// recursive call if asked
						if(isset($val['childs']) && is_array($val['childs']) && count($val['childs']) > 0) {
							$aResult[] = $this->_parseXml($val['childs'], $item);
						}
						else {
							$aResult[] = $txt;
						}
					}
					else { // array key specified
						$kpDatasFromXPath = $item->xpath($val['arrayKeyPath']);
						if (isset($kpDatasFromXPath[0])) {
							if (!empty($val['arrayKeyAttributes']) && isset($kpDatasFromXPath[0][$val['arrayKeyAttributes']])) {
								$keyCode = (string)$kpDatasFromXPath[0][$val['arrayKeyAttributes']];
								// paste g�n�ral user function
								for ($i=0 ; $i < count($this->_aFunctionsList) ; $i++) {
									if (!empty($this->_aFunctionsList[$i])) {
										$keyCode = $this->_aFunctionsList[$i]($keyCode);
									}
								}
								// paste user's function
								for ($i=0 ; $i<count($val['arrayKeyFct']) ; $i++) {
									if (!empty($val['arrayKeyFct'][$i])) {
										$keyCode = $val['arrayKeyFct'][$i]($keyCode);
									}
								}
								// recursive call if asked
								if(isset($val['childs']) && is_array($val['childs']) && count($val['childs']) > 0) {
									$aResult[$keyCode] = $this->_parseXml($val['childs'], $item);
								}
								else {
									$aResult[$keyCode] = $txt;
								}
							}
							else {
								$keyCode = (string)$kpDatasFromXPath[0];
								// paste g�n�ral user function
								for ($i=0 ; $i < count($this->_aFunctionsList) ; $i++) {
									if (!empty($this->_aFunctionsList[$i])) {
										$keyCode = $this->_aFunctionsList[$i]($keyCode);
									}
								}
								// paste user's function
								for ($i=0 ; $i<count($val['arrayKeyFct']) ; $i++) {
									if (!empty($val['arrayKeyFct'][$i])) {
										$keyCode = $val['arrayKeyFct'][$i]($keyCode);
									}
								}
								// recursive call if asked
								if(isset($val['childs']) && is_array($val['childs']) && count($val['childs']) > 0) {
									$aResult[$keyCode] = $this->_parseXml($val['childs'], $item);
								}
								else {
									$aResult[$keyCode] = $txt;
								}
							}
						}
						else {
							// recursive call if asked
							if(isset($val['childs']) && is_array($val['childs']) && count($val['childs']) > 0) {
								$aResult[] = $this->_parseXml($val['childs'], $item);
							}
							else {
								$aResult[] = $txt;
							}
						}
					}
				}
			}
			// set result for key
			if (!$val['isArray']) {
				if (isset($aResult[0])) {
					$aReturn[$key] = $aResult[0];
				}
				else {
					$aReturn[$key] = null;
				}
			}
			else {
				// paste user's function
				$this->_executeFunctions($val['userAFunctions'], $aResult);
				$aReturn[$key] = $aResult;
			}
		}
		// return result
		return $aReturn;
	}

	/**
	 * _translateParameters() method transform parsing parameters in to a recursive array
	 *
	 * @param array $aParameters
	 * @return array
	 */
	protected function _translateParameters($aParameters)
	{
		// create return array
		$aReturn = array();

		// loop on each parameters
		foreach ($aParameters as $k => $v) {
			$xPath = trim($v);
			$attribute = null;
			$isArray = false;
			$userFunctions = array();
			$userAFunctions = array();
			$kpAttrib = null;
			$kpPath = null;
			list($xPath, $attribute, $isArray, $userFunctions, $kpAttrib, $kpPath, $kpFct, $userAFunctions) = $this->_analysePattern($xPath);
			$tps = str_replace(array('[',']'),array(',',''),$k);
			$kList = explode(',', $tps);
			$objTarget = &$aReturn;
			// childs option
			for ($i=0 ; $i<count($kList) ; $i++) {
				if ($i == 0) {
					$objTarget = &$objTarget[$kList[$i]];
				}
				else {
					$objTarget = &$objTarget['childs'][$kList[$i]];
				}
			}
			$objTarget = array(
				'xPath' => $xPath,
				'attribute' => $attribute,
				'isArray' => $isArray,
				'userFunctions' => $userFunctions,
				'userAFunctions' => $userAFunctions,
				'arrayKeyPath' => $kpPath,
				'arrayKeyAttributes' => $kpAttrib,
				'arrayKeyFct' => $kpFct,
				'childs' => array()
			);
		}

		// return array
		return $aReturn;
	}

	/**
	 * _analysePattern() method analyze each row of parameters array
	 *
	 * @param string $aPath
	 * @return array
	 */
	protected function _analysePattern (array $aPath)
	{
		// set local variables
		$xPath = trim($aPath);
		$attribute = null;
		$isArray = false;
		$userFunctions = array();
		$userAFunctions = array();
		$keyPrefix = null;
		$kpAttrib = null;
		$kpPath = null;
		$kpFct = array();

		// evaluate pattern : array function asked (pattern = <func> xpath)
		while (substr($xPath, 0, 1) == '<') {
			$sep = strpos($xPath, '>');
			$userAFunctions[] = trim(substr($xPath, 1, $sep - 1));
			$xPath = trim(substr($xPath, $sep + 1));
		}

		// evaluate pattern : function asked (pattern = (func) xpath)
		while (substr($xPath, 0, 1) == '(') {
			$sep = strpos($xPath, ')');
			$userFunctions[] = trim(substr($xPath, 1, $sep - 1));
			$xPath = trim(substr($xPath, $sep + 1));
		}

		// evaluate pattern : array asked (pattern = [] xpath)
		if (substr($xPath, 0, 1) == '[') {
			$sep = strpos($xPath, ']');
			$isArray = true;
			$keyPrefix = trim(substr($xPath, 1, $sep - 1));
			$xPath = trim(substr($xPath, $sep + 1));
		}

		// evaluate pattern : attribute asked (pattern = @att xpath)
		if (substr($xPath, 0, 1) == '@') {
			$sep = strpos($xPath, ' ');
			if (is_integer($sep)) {
				// get xPath and asked attribute value
				$attribute = trim(substr($xPath, 1, $sep));
				$xPath = trim(substr($xPath, $sep));
			}
			else {
				$attribute = trim(substr($xPath, 1));
				$xPath = '.';
			}
		}

		// evaluate pattern for keyPrefix
		if (!empty($keyPrefix)) {
			$kpAttrib = null;
			$kpPath = trim($keyPrefix);
			$kpFct = array();
			list($kpPath, $kpAttrib, , $kpFct, , ,) = $this->_analysePattern($keyPrefix);
		}

		// set default xpath to '.'
		if (empty($xPath)) {
			$xPath = '.';
		}

		// return values
		return(
			array(
				$xPath,
				$attribute,
				$isArray,
				$userFunctions,
				$kpAttrib,
				$kpPath, 
				$kpFct,
				$userAFunctions
			)
		);
	}

	/**
	 * _executeFunctions() method execute each defined function into specific content
	 *
	 * @param array $aFunctions
	 * @param string $sVal
	 */
	protected function _executeFunctions($aFunctions, &$sVal)
	{
		// loop on each functions
		foreach ($aFunctions as $function) {
			if (!empty($function)) {
				// verify if static method
				if (is_int(strpos($function,'::'))) {
					list($class, $method) = explode('::', $function);
					if (!method_exists($class, $method)) {
						$this->_doCallBack('Method ' . $class . '::' . $method . '() doesn\'t exist');
						throw new Exception('Method ' . $class . '::' . $method . '() doesn\'t exist', 205);
					}
					$sVal = call_user_func_array(array($class,$method), $sVal);
				}
				else {
					if (!function_exists($function)) {
						$this->_doCallBack('Function ' . $function . '() doesn\'t exist');
						throw new Exception('Function ' . $function . '() doesn\'t exist', 205);
					}
					$sVal = $function($sVal);
				}
			}
		}
	}

	/**
	 * parseFile() method parse file according to the parameters array and return an array at least => matching to call loadFile() method and parseFile() next
	 *
	 * @param string $sFilePath
	 * @param array $aParams
	 * @param int $iLoadOption
	 * @return array
	 */
	public function parseFile($sFilePath, $aParams, $iLoadOption = LIBXML_NOCDATA)
	{
		// load file
		$this->loadFile($sFilePath, $iLoadOption);

		// return parsed xml
		return(
			$this->parse($aParams)
		);
	}

	/**
	 * xml2Array() method transform current data feed in to an associative array
	 *
	 * @param string $sFilePath
	 * @return array
	 */
	public function xml2Array($sFilePath)
	{
		if ($this->_aCallBack != null) {
			$this->_startTime = microtime(true);
			$this->_sCallBackTitle = $sFilePath;
		}
		// load file
		$this->loadFile($sFilePath);

		// return parsed xml
		return(
			$this->getData()
		);
	}

	/**
	 * getData() method return an associative array
	 *
	 * @return array
	 */
	public function getData()
	{
		// verify if some xml is loaded
		if (empty($this->_oXmlResource)) {
			$this->_doCallBack('Can\'t parse empty xml');
			throw new Exception('Can\'t parse empty xml', 204);
		}
		$aResult = array();
		$aResult[$this->_oXmlResource->getName()] = $this->_getData($this->_oXmlResource);
		// get attributes
		foreach($this->_oXmlResource->attributes() as $a => $b) {
			$var = $this->_encodeResult((string)$b);
			$this->_executeFunctions($this->_aFunctionsList, $var);
			$aResult['@' . $this->_oXmlResource->getName()][$a] = $var;
		}
		$this->_doCallBack();

		return $aResult;
	}

	/**
	 * _getData() method parse current node and return value
	 *
	 * @return array
	 */
	protected function _getData($object)
	{
		$res = null;

		if (is_a($object, 'SimpleXMLElement')) {
			$tps = (array)$object;
			foreach ($tps as $k => $v) {
				if ($k != '@attributes') {
					$res[$k] = $this->_getData($v);
				}
			}
			foreach ($object as $k => $v) {
				if (is_a($v, 'SimpleXMLElement')) {
					foreach($v->attributes() as $a => $b) {
						$var = $this->_encodeResult((string)$b);
						$this->_executeFunctions($this->_aFunctionsList, $var);
						$res['@' . $k][$a] = $var;
					}
				}
			}
		}
		elseif (is_array($object)) {
			foreach ($object as $k => $v) {
				if (is_a($v, 'SimpleXMLElement')) {
					foreach($v->attributes() as $a => $b) {
						$var = $this->_encodeResult((string)$b);
						$this->_executeFunctions($this->_aFunctionsList, $var);
						$res['@' . $k][$a] = $var;
					}
				}
				$res[$k] = $this->_getData($v);
			}
		}
		else {
			$var = $this->_encodeResult((string)$object);
			$this->_executeFunctions($this->_aFunctionsList, $var);
			$res = $var;
		}
		return $res;
	}
}