<?php
/**
 * verbose.class.php file defines methods to display configurable verbose
 */

class BT_Verbose
{
	/**
	 * @var array $_aOptions : array of set configuration
	 */
	private $_aOptions = array();

	/**
	 * @var string $_sDatePattern : date pattern
	 */
	private $_sDatePattern = '';

	/**
	 * @var array $_aDisplayOrder : array of displayed order
	 */
	private $_aDisplayOrder = array();

	/**
	 * @var string $_sSeparator : separator
	 */
	private $_sSeparator = '';

	/**
	 * @var string $_sStepLine : step line
	 */
	private $_sStepLine = '';

	/**
	 * @var array $_aCallBack : callback
	 */
	private $_aCallBack = null;

	/**
	 * @var array $_aTraces : array for traces
	 */
	private $_aTraces = array();

	/**
	 * __construct() save configuration options.
	 *
	 * @param array $aOptions : display options array
	 */
	public function __construct(array $aOptions = null)
	{
		if (isset($aOptions['callBack'])) {
			$this->_aCallBack = $aOptions['callBack'];
		}
		$this->_aOptions = array(
			'd' => '^date',
			'm' => '^memory',
			'c' => '^caller',
			'l' => '^line',
			'p' => '^process_id',
		);
		if (isset($aOptions['stepLine'])) {
			$this->_sStepLine = $aOptions['stepLine'];
		}
		else {
			$this->_sStepLine = '   ';
		}
		if (isset($aOptions['separator'])) {
			$this->_sSeparator = $aOptions['separator'];
		}
		else {
			$this->_sSeparator = ' ~ ';
		}
		if (isset($aOptions['datePattern'])) {
			$this->_sDatePattern = $aOptions['datePattern'];
		}
		else {
			$this->_sDatePattern = 'H:i:s';
		}
		if (isset($aOptions['mask'])) {
			for ($i = 0 ; $i < strlen($aOptions['mask']) ; $i++) {
				$sOptionCode = substr($aOptions['mask'], $i, 1);
				if ('^' == $sOptionCode) {
					$i++;
					$sOptionCode = substr($aOptions['mask'], $i, 1);
					if (isset($this->_aOptions[$sOptionCode])) {
						$this->_aDisplayOrder[] = $this->_aOptions[$sOptionCode];
					}
				}
				else {
					$this->_aDisplayOrder[] = $sOptionCode;
				}
			}
		}
	}

	/**
	 * startTrace() makes start trace point
	 * 
	 * @param string $sTraceCode :  trace 's unique code
	 */
	public function startTrace($sTraceCode)
	{
		if ($this->_aCallBack != null) {
			if (!isset($this->_aTraces[$sTraceCode])) {
				$this->_aTraces[$sTraceCode] = microtime(true);
			}
		}
	}

	/**
	 * endTrace() finish trace end and execute callback with specified data of trace
	 * 
	 * @param string $sTraceCode : trace's unique code
	 * @param string $sTraceDetail : trace detail (optional)
	 * @param string $sTraceError : error (optional)
	 */
	public function endTrace($sTraceCode, $sTraceDetail = '', $sTraceError = '')
	{
		if ($this->_aCallBack != null) {
			if (!isset($this->_aTraces[$sTraceCode])) {
				$this->_aTraces[$sTraceCode] = microtime(true);
			}
			$aResult = array(
				'ComponentCode' => $this->_aCallBack[0],
				'Time' => microtime(true) - $this->_aTraces[$sTraceCode],
				'Detail' => $sTraceCode . ' -> ' . $sTraceDetail
			);
			if ($sTraceError) {
				$aResult['Error'] = $sTraceError;
			}
			// call back
			call_user_func($this->_aCallBack[1], 
				$aResult
			);
			// raz trace
			unset($this->_aTraces[$sTraceCode]);
		}
	} // endTrace()

	/**
	 * display() displays verbose line
	 *
	 * @param string $sCaller : caller (section / module / component)
	 * @param string $sLine : display line
	 * @param mixed $iLevel : tab of line
	 */
	public function line($sCaller, $sLine, $iLevel = 1)
	{
		$sOut = '';
		foreach($this->_aDisplayOrder as $sDisplay) {
			if ($sDisplay == '^date') {
				$sOut .= date($this->_sDatePattern);
			}
			elseif ($sDisplay == '^memory') {
				$sOut .= round(memory_get_usage()/1000);
			}
			elseif ($sDisplay == '^process_id' && function_exists('posix_getpid')) {
				$sOut .= posix_getpid();
			}
			elseif ($sDisplay == '^caller') {
				$sOut .= $sCaller;
			}
			elseif ($sDisplay == '^line') {
				$sOut .= $sLine;
			}
			else {
				$sOut .= $sDisplay;
			}
		}
		if (is_integer($iLevel) && ($iLevel > 1) && count($this->_aDisplayOrder)) {
			$sStep = '';
			for ($i = 1 ; $i < $iLevel ; $i++) {
				$sStep .= $this->_sStepLine;
			}
			$sOut = $sStep . $sOut;
		}
		if ($sOut != '') {
			echo ($sOut . "\n");
		}
	}

	/**
	 * debug() displays variable content.
	 * Display next, ask to continue or stop script
	 * 
	 * @param string $sTitle : debug title
	 * @param string $sVar : displayed variable
	 * @param string $sDisplayFunc : function applied on it
	 */
	public function debug($sTitle, $sVar, $sDisplayFunc = 'var_dump')
	{
		echo ("\n" . str_pad('', 80, "#") . "\n"
			. "#" . str_pad('', 78, " ") . "#\n"
			. "#" . str_pad('', 30, " ") . "DEBUG MODE" . str_pad('', 38, " ") . "#\n"
			. "#" . str_pad('', 78, " ") . "#\n"
			. str_pad('', 80, "#") . "\n\n"
			. str_pad('', 21, "#") . " " . $sTitle . " "
			. str_pad('', 57-strlen($sTitle), "#") . "\n");

		call_user_func($sDisplayFunc, $sVar);
		echo ("***********************************************************\n"
			. "Type <enter> to continue, 'q' to stop execution\nBT>");
		$sLine = trim(fgets(STDIN));

		if ($sLine == 'q') {
			exit(0);
		}
	}

	/**
	 * backTrace() allow to display a script backtrace.
	 */
	public function backTrace()
	{
		echo ("\n" . str_pad('', 80, "#") . "\n"
			. "#" . str_pad('', 78, " ") . "#\n"
			. "#" . str_pad('', 30, " ") . "BACK TRACE" . str_pad('', 38, " ") . "#\n"
			. "#" . str_pad('', 78, " ") . "#\n"
			. str_pad('', 80, "#") . "\n\n");
		// get back traces
		$aBackTrace = array_reverse(debug_backtrace());
		array_pop($aBackTrace);
		$loop = 0;
		foreach ($aBackTrace as $aTrace) {
			$loop++;
			echo (str_pad('', ceil(39-(strlen($loop)/2)), "#") . " " . $loop . " "
				. str_pad('', floor(39-(strlen($loop)/2)), "#") . "\n");
			if (isset($aTrace['file']) && isset($aTrace['line'])) {
				echo (" . " . $aTrace['file'] . " (" . $aTrace['line'] . ")\n");
			}

			if (isset($aTrace['class'])) {
				echo (" . " . $aTrace['class'] . $aTrace['type']
					. $aTrace['function'] . "(");
			}
			else {
				echo (" . " . $aTrace['function'] . "(");
			}
			if (isset($aTrace['args'])) {
				$iLoopArg = 0;
				foreach ($aTrace['args'] as $arg) {
					$iLoopArg++;
					$argLine = var_export($arg, true);
					$argLine = explode("\n", $argLine);
					foreach ($argLine as &$tps) {
						$tps = trim($tps);
					}
					$argLine = join('', $argLine);
					echo (($iLoopArg>1 ? ",\n\t" : "\n\t") . $argLine);
				}
				echo (($iLoopArg ? "\n    " : "") . ")\n");
			}
			else {
				echo (")\n");
			}
			echo ("\n");
		}
		echo ("***********************************************************\n"
			. "Type <enter> to continue, 'q' to stop execution\nBT>");
		$sLine = trim(fgets(STDIN));
		if ($sLine == 'q') {
			exit(0);
		}
	}

	/**
	 * create() method returns singleton
	 *
	 * @param array $aOptions
	 * @return obj
	 */
	public static function create(array $aOptions = null)
	{
		static $oVerbose;

		if( null === $oVerbose) {
			$oVerbose = new BT_Verbose($aOptions);
		}
		return $oVerbose;
	}
}