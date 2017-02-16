<?php
/**
 * timer.class.php define Timer class and set timers
 */
class BT_Timer
{
	/**
	 * @var array $_aTimer : stock all timer
	 */
	private $_aTimer = array();

	/**
	 * @var array $_aTimerConf : define available parameters
	 */
	private $_aTimerConf = array(
		'MicroTime'	 	=> false,
		'DisplayMin' 	=> false,
		'SecSuffix' 	=> 'sec',
		'MinSuffix' 	=> 'min',
		'Separator' 	=> "\t",
	);

	/**
	 * __construct() affect values in properties
	 *
	 * @param array $aParams
	 */
	public function __construct($aParams = null)
	{
		if (null !== $aParams && is_array($aParams)) {
			if (isset($aParams['MicroTime']) && is_bool($aParams['MicroTime'])) {
				$this->_aTimerConf['MicroTime'] = $aParams['MicroTime'];
			}
			if (isset($aParams['DisplayMin']) && is_bool($aParams['DisplayMin'])) {
				$this->_aTimerConf['DisplayMin'] = $aParams['DisplayMin'];
			}
			if (isset($aParams['SecSuffix']) && is_string($aParams['SecSuffix'])) {
				$this->_aTimerConf['SecSuffix'] = $aParams['SecSuffix'];
			}
			if ($this->_aTimerConf['DisplayMin']
				&&
				(isset($aParams['MinSuffix']) && is_string($aParams['MinSuffix']))
			) {
				$this->_aTimerConf['MinSuffix'] = $aParams['MinSuffix'];
			}
			if (isset($aParams['Separator']) && is_string($aParams['Separator'])) {
				$this->_aTimerConf['Separator'] = $aParams['Separator'];
			}
		}
	}

	/**
	 * __destruct()
	 */
	public function __destruct() {}

	/**
	 * start() method define timer name and related time
	 *
	 * @param string $sTimerName
	 */
	public function start($sTimerName)
	{
		if ($this->_aTimerConf['MicroTime']) {
			list($iUsec , $iSec) = explode(' ', microtime());
			$this->_aTimer[$sTimerName] = ((float)$iUsec + (float)$iSec);
		}
		else {
			$this->_aTimer[$sTimerName] = time();
		}
	}


	/**
	 * get() method test if timer name exists and return difference between 2 time
	 * @param string $sTimerName
	 * @return mixed: false or int
	 */
	public function get($sTimerName)
	{

		$sTime = false;

		// if exists
		if (array_key_exists($sTimerName, $this->_aTimer)) {
			//get diff time in microtime or time
			if ($this->_aTimerConf['MicroTime']) {
				list($iUsec , $iSec) = explode(' ', microtime());
				$iTime = (((float)$iUsec + (float)$iSec) - $this->_aTimer[$sTimerName]);
			}
			else {
				$iTime = time() - $this->_aTimer[$sTimerName];
			}

			$sTime = $iTime . $this->_aTimerConf['Separator'] . $this->_aTimerConf['SecSuffix'];

			// test if display min
			if ($this->_aTimerConf['DisplayMin']) {
				$fMin = round($iTime);

				if ($fMin > 60) {
					$aTimeTmp = explode('.', $fMin / 60);
					// return
					$sTime = $aTimeTmp[0] . $this->_aTimerConf['Separator']
						. $this->_aTimerConf['MinSuffix']
						. $this->_aTimerConf['Separator']
						. ($fMin - ($aTimeTmp[0] * 60)
							. $this->_aTimerConf['Separator']
							. $this->_aTimerConf['SecSuffix']);
				}
			}
		}

		return $sTime;
	}
}