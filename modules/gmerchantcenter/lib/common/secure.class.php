<?php
/**
 * secure.class.php file defines methods secure all contents via HTML request
 */
class BT_Secure
{
	/**
	 * @const FILTER_VALIDATE_MD5 : MD5 filter
	 */
	const FILTER_VALIDATE_MD5 = 'md5';

	/**
	 * @const FILTER_VALIDATE_EMAIL : MAIL filter
	 */
	const FILTER_VALIDATE_EMAIL = 'e-mail';

	/**
	 * @const FILTER_VALIDATE_LIST : list filter
	 */
	const FILTER_VALIDATE_LIST = 'list';

	/**
	 * filterVar() method can validate according to its variable type or to apply filter. It can encode in HTML according to usage
	 * NB : filter could have parameters, in this way you have to specify :
	 * $mFilter : array(FILTER => array('filter's parameters'))
	 *
	 * @param mixed $mParameter : var to filter
	 * @param string $sType : filter's type ('numeric', 'integer', 'array', 'float' ou 'string')
	 * @param string $sUsage : usage ('db' ou 'display')
	 * @param mixed $mDefaultValue : default value
	 * @param mixed $mFilter : filter to apply
	 * @return mixed
	 */
	public static function filterVar($mParameter, $sType = null, $sUsage = 'display', $mDefaultValue = false, $mFilter = null)
	{
		// get default value
		$mResult = $mParameter;

		// its has value, verification have to be done
		$bVerify = true;

		// default type is string
		if (null === $sType) {
			$sType = 'string';
		}
		// type checking
		$mResult = self::verifyType($mResult, $mDefaultValue, $sType);

		// applying filter if asked
		if (null !== $mFilter) {
			$mResult = self::applyFilter($mResult, $mFilter, $mDefaultValue);
		}
		// HTML encoding if usage different to 'db' and type is string
		if ($sUsage != 'db' && $sType === 'string' && is_string($mResult)) {
			$mResult = htmlspecialchars($mResult);
		}

		return $mResult;
	}

	/**
	 * issetParameter() method calls filterParameter() method and throw an exception if value equal to default value
	 *
	 * @param array $aArraySrc : enter array
	 * @param string $sKey : key field to check
	 * @param string $sType : var type ('numeric', 'integer', 'array', 'float' or 'string')
	 * @param string $sUsage : usage ('db' or 'display')
	 * @param mixed $mDefaultValue : default value
	 * @param mixed $mFilter : filter to apply
	 * @return mixed
	 */
	public static function issetParameter($aArraySrc, $sKey, $sType = null, $sUsage = 'display', $mDefaultValue = false, $mFilter = null)
	{
		$mResult = self::filterParameter($aArraySrc, $sKey, $sType, $sUsage, $mDefaultValue, $mFilter);

		if ($mDefaultValue === $mResult) {
			throw new Exception('Invalid parameter ' . $sKey);
		}

		return $mResult;
	}

	/**
	 * filterParameter() method can validate according to its variable type or to apply filter. It can encode in HTML according to usage
	 * NB : filter could have parameters, in this way you have to specify :
	 * $mFilter : array(FILTER => array('filter's parameters'))
	 *
	 * @param array $aArraySrc : enter array
	 * @param string $sKey : key field to validate
	 * @param string $sType : var type ('numeric', 'integer', 'array', 'float' or 'string')
	 * @param string $sUsage : usage ('db' or 'display')
	 * @param mixed $mDefaultValue : default value
	 * @param mixed $mFilter : filter to apply
	 * @return mixed
	 */
	public static function filterParameter($aArraySrc, $sKey, $sType = null, $sUsage = 'display', $mDefaultValue = false, $mFilter = null)
	{
		// init default value
		$mResult = $mDefaultValue;
		// init action type of checking. No action by default
		$bVerify = false;
		if (isset($aArraySrc[$sKey])) {
			// get value
			$mResult = $aArraySrc[$sKey];
			// its has value, verification have to be done
			$bVerify = true;
			// If its not declared type, it will check acording to key format
			if (null === $sType) {
				switch (substr($sKey, 0, 1)) {
					case 'n':
						$sType = 'numeric';
						break;
					case 'i':
						$sType = 'integer';
						break;
					case 'a':
						$sType = 'array';
						break;
					case 'f':
						$sType = 'float';
						break;
					case 'b':
						$sType = 'boolean';
						break;
					case 's':
					default:
						$sType = 'string';
						break;
				}
			}
		}
		// check variable's values
		if ($bVerify) {
			$mResult = self::verifyType($mResult, $mDefaultValue, $sType);
		}
		// applying filter if asked
		if (null !== $mFilter) {
			$mResult = self::applyFilter($mResult, $mFilter, $mDefaultValue);
		}
		// HTML encoding if usage different to 'db' and type is string
		if ($sUsage != 'db' && $sType === 'string' && is_string($mResult)) {
			$mResult = htmlspecialchars($mResult);
		}

		return $mResult;
	}

	/**
	 * verifyType() method can check var according to its type 
	 * available type : 'numeric', 'integer', 'array', 'float' or 'string'
	 *
	 * @param mixed $mValue : var to validate.
	 * @param mixed $mDefaultValue : default value.
	 * @param string $sType : var type ('numeric', 'integer', 'array', 'float' or 'string').
	 * @return mixed
	 */
	public static function verifyType($mValue, $mDefaultValue, $sType)
	{
		switch ($sType) {
			case 'bool':
			case 'boolean':
				if ($mValue !== true && $mValue !== false) {
					$mValue = $mDefaultValue;
				}
				break;
			case 'int':
			case 'integer':
				if ($mValue != strval(intval($mValue))) {
					$mValue = $mDefaultValue;
				}
				else {
					$mValue = intval($mValue);
				}
				break;
			case 'float':
				if ($mValue != strval(floatval($mValue))) {
					$mValue = $mDefaultValue;
				}
				else {
					$mValue = floatval($mValue);
				}
				break;
			case 'numeric':
				if (!is_numeric($mValue)) {
					$mValue = $mDefaultValue;
				}
				elseif ($mValue == strval(intval($mValue))) {
					$mValue = intval($mValue);
				}
				elseif ($mValue == strval(floatval($mValue))) {
					$mValue = floatval($mValue);
				}
				break;
			case 'array' :
				if (!is_array($mValue)) {
					$mValue = $mDefaultValue;
				}
				break;
			case 'string':
			default:
				$mValue = strval($mValue);
				break;
		}

		return $mValue;
	}

	/**
	 * applyFilter() method can apply filter to var
	 *
	 * @param mixed $mVar
	 * @param mixed $mFilter
	 * @param mixed $mDefaultValue
	 * @return mixed
	 */
	public static function applyFilter($mVar, $mFilter, $mDefaultValue)
	{
		$mResult = $mDefaultValue;
		switch (is_array($mFilter)) {
				// use case - filter with args
			case true:
				// filter retrieval
				list($nFilterVal, $aOptions) = each($mFilter);
				switch ($nFilterVal) {
					case self::FILTER_VALIDATE_LIST :
						if (!in_array($mVar, $aOptions)) {
							$mResult = $mDefaultValue;
						}
						else {
							$mResult = $mVar;
						}
						break;
					default :
						// filter's execution
						$mResult = filter_var($mVar, $nFilterVal, $aOptions);
						if (false === $mResult) {
							$mResult = $mDefaultValue;
						}
						break;
				}
				break;
				// use case - filter without args
			default:
				switch ($mFilter) {
					case self::FILTER_VALIDATE_MD5:
						$mResult = filter_var($mVar, FILTER_VALIDATE_REGEXP, 
							array('options' => array('regexp' => '/^[A-Fa-f0-9]{32}$/'))
						);
						if (false === $mResult) {
							$mResult = $mDefaultValue;
						}
						break;
					case self::FILTER_VALIDATE_EMAIL:
						// PHP filter applied
						$mResult = filter_var($mVar, FILTER_VALIDATE_EMAIL);
						// adds checking on autorized characters by filter
						if (false !== $mResult) {
							if (0 != preg_match("/[\!\#\$\%\&\'\*\+\/\=\?\^\`\{\|\}\~\[\]]/", $mVar)) {
								$mResult = false;
							}
						}
						if (false === $mResult) {
							$mResult = $mDefaultValue;
						}
						break;
					default:
						$mResult = filter_var($mVar, $mFilter);
						if (false === $mResult) {
							$mResult = $mDefaultValue;
						}
						break;
				}
		}
		return $mResult;
	}

	/**
	 * validateParameters() can validate set of vars from list (exclusive or inclusive)
	 *
	 * @param array $aSrc
	 * @param array $aList
	 * @param bool $bExclude
	 */
	public static function validateParameters(&$aSrc, $aList, $bExclude = false)
	{
		foreach ($aSrc as $sKey => $mValue) {
			if (($bExclude === false && in_array($sKey, $aList))
				|| ($bExclude === true && !in_array($sKey, $aList))
			) {
				$mResult = self::filterParameter($aSrc, $sKey);
				if (false === $mResult) {
					throw new Exception('Invalid parameter ' . $sKey . ' (' . $aSrc[$sKey] . ')');
				}
				else {
					$aSrc[$sKey] = $mResult;
				}
			}
		}
	}
}