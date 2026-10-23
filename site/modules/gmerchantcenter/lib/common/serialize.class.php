<?php
/**
 * serialize.class.php define Serialize class
 * Serialize implement some methods to set and get serialized content
 */

class BT_Serialize
{
	/**
	 * @var object $obj
	 */
	public static $obj = null;
	
	/**
	 * @var array $_aSerialized
	 */
	private $_aSerialized = array();

	
	/**
	 * __construct() method instantiate object
	 * 
	 * @param array	$aParams
	 */
	public function __construct($aParams = null) {
		
	}
	
	/**
	 * __destruct() method destruct current object
	 *
	 */
	public function __destruct() {}
	
	/**
	 * The set() method set serialize data
	 * @param array $aParams
	 * @return mixed : false or string
	 */
	public function set($mData) {
		// serialize all php variable except resource
		if (is_resource($mData)) {
			return false;
		}
		return (
			serialize($mData)
		);
	}
	
	/**
	 * The get() method get specific serialized data
	 * @param array $sData
	 * @param string $sKey
	 * @return mixed
	 */
	public function get($sData, $sKey = null) {
		// check if string - unserialize only serialized string
		if (is_string($sData)) {
			$mData = unserialize($sData);
			
			if (false !== $mData) {
				if (null !== $sKey) {
					if (is_object($mData) && property_exists($mData, $sKey)) {
						return $mData->$sKey;
					}
					elseif (is_array($mData) && isset($mData[$sKey])) {
						return $mData[$sKey];
					}
				}
				return $mData;
			}
		}
		// use case - string declared or unserialize doesn't works
		return false;
	}
	
	/**
	 * The setErrorHandler() method format error
	 * @param int $errno
	 * @param string $errstr
	 * @param string $errfile
	 * @param int $errLine
	 * @param array $errcontext
	 * @return string
	 */
	public function setErrorHandler($errno, $errstr, $errfile, $errLine, $errcontext) {
		if (E_STRICT != $errno && E_NOTICE != $errno) {
			throw new Exception($errstr . ' (line: ' . $errLine . ', file:' . $errfile . ')' , $errno);
		}
	}
	
	/**
	 * create() method create instance of object
	 * @example
	 * @param 	mixed 	$mParams
	 * @return  object 	$obj
	 */
	public static function create($mParams = null) {
		if (null === self::$obj) {
			self::$obj = new BT_Serialize($mParams);
		}

		return self::$obj;
	}
	
	/**
	 * destruct() method destruct current object
	 * @example
	 */
	public static function destruct() {
		if (self::$obj !== null) {
			unset(self::$obj);
		}
	}
}