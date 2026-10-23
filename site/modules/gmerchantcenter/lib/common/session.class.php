<?php
/**
 * session.class.php define Session class
 * Session implement some methods around session content (switch on session to cookie)
 */
class BT_GmcSession
{
	/**
	 * @var object $obj
	 */
	public static $obj = null;

	/**
	 * @var bool $p_bSESSION
	 */
	public static $p_bSESSION = false;

	/**
	 * @var string $_sPrefix : prefix
	 */
	private $_sPrefix = '';

	/**
	 * __construct() method instantiate object
	 *
	 * @param array	$aParams
	 */
	public function __construct(array $aParams = null) {
		self::$p_bSESSION = @session_start();

		if (!empty($aParams)){
			if (!empty($aParams['sPrefix']) && is_string($aParams['sPrefix'])) {
				$this->_sPrefix = $aParams['sPrefix'];
			}
		}
	}

	/**
	 * __destruct() method destruct current object
	 */
	public function __destruct() {}

	/**
	 * set() method register in session or in cookie
	 *
	 * @param string $sKey
	 * @param mixed $mValue
	 * @return true
	 */
	public function set($sKey, $mValue) {
		// use case - session
		if (self::$p_bSESSION) {
			$_SESSION[$this->_sPrefix . $sKey] = $mValue;
		}
		else {
			$_COOKIE[$this->_sPrefix . $sKey] = $mValue;
		}

		return true;
	}

	/**
	 * get() method return session data
	 *
	 * @param string $sKey
	 * @return mixed $mValue
	 */
	public function get($sKey) {
		$mReturn = null;

		// use case - session
		if (self::$p_bSESSION) {
			if (isset($_SESSION[$this->_sPrefix . $sKey])) {
				$mReturn = $_SESSION[$this->_sPrefix . $sKey];
			}
		}
		elseif (isset($_COOKIE[$this->_sPrefix . $sKey])) {
			$mReturn = $_COOKIE[$this->_sPrefix . $sKey];
		}

		return $mReturn;
	}

	/**
	 * update() method update session data
	 *
	 * @param string $sKey
	 * @param string $mValue
	 * @return bool $bReturn
	 */
	public function update($sKey, $mValue) {
		$bReturn = false;

		// use case - session
		if (self::$p_bSESSION) {
			if (isset($_SESSION[$this->_sPrefix . $sKey])) {
				$_SESSION[$this->_sPrefix . $sKey] = $mValue;
				$bReturn = true;
			}
		}
		elseif (isset($_COOKIE[$this->_sPrefix . $sKey])) {
			$_COOKIE[$this->_sPrefix . $sKey] = $mValue;
			$bReturn = true;
		}

		return $bReturn;
	}

	/**
	 * delete() method delete data session
	 *
	 * @param string $sKey
	 * @return bool
	 */
	public function delete($sKey) {
		$bReturn = false;

		// use case - session
		if (self::$p_bSESSION) {
			if (isset($_SESSION[$this->_sPrefix . $sKey])) {
				unset($_SESSION[$this->_sPrefix . $sKey]);
				$bReturn = true;
			}
		}
		elseif (isset($_COOKIE[$this->_sPrefix . $sKey])) {
			unset($_COOKIE[$this->_sPrefix . $sKey]);

			$bReturn = true;
		}

		return $bReturn;
	}

	/**
	 * create() method create instance of object
	 * @param 	mixed 	$mParams
	 * @return  object 	$obj
	 */
	public static function create($mParams = null) {
		if (null === self::$obj) {
			self::$obj = new BT_GmcSession($mParams);
		}

		return self::$obj;
	}

	/**
	 * destruct() method destruct current object
	 */
	public static function destruct() {
		if (self::$obj !== null) {
			unset(self::$obj);
		}
	}
}