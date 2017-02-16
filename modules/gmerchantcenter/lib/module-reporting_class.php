<?php
/**
 * module-reporting_class.php file defines method to manage reporting content
 */

class BT_GmcReporting
{
	/**
	 * @var obj $oReporting
	 */
	public static $oReporting;

	/**
	 * @var array $aReport : stock msg reported
	 */
	public $aReport = array();

	/**
	 * @var string $sFileName : store file name
	 */
	public $sFileName = '';

	/**
	 * @var bool $bActivate : activate or not reporting
	 */
	public $bActivate = null;

	/**
	 * Magic Method __construct
	 * @param bool $bActivate
	 */
	public function __construct($bActivate = true)
	{
		$this->bActivate = $bActivate;
	}

	/**
	 * Magic Method __destruct
	 */
	public function __destruct()
	{

	}

	/**
	 * set() method stock reporting
	 *
	 * @param string $Key
	 * @param array $aParams
	 * @return array
	 */
	public function set($Key, $aParams)
	{
		if ($this->bActivate) {
			$this->aReport[$Key][] = $aParams;
		}
	}

	/**
	 * setFileName() method set file name
	 *
	 * @param string $sFileName
	 */
	public function setFileName($sFileName)
	{
		$this->sFileName = $sFileName;
	}

	/**
	 * get() method return available serialized content
	 *
	 * @return array
	 */
	public function get()
	{
		$aData = array();

		if ($this->bActivate && file_exists($this->sFileName) && filesize($this->sFileName)) {
			$sContent = method_exists('Tools', 'file_get_contents')? Tools::file_get_contents($this->sFileName) : file_get_contents($this->sFileName);

			if (!empty($sContent)) {
				$aData = unserialize($sContent);
			}
		}

		return $aData;
	}

	/**
	 * delete() method delete reporting file
	 *
	 * @return bool
	 */
	public function delete()
	{
		return (
			is_file($this->sFileName) && unlink($$this->sFileName)? true : false
		);
	}

	/**
	 * mergeData() merge data between current data and stored data in reporting file
	 *
	 * @return array
	 */
	public function mergeData()
	{
		$aReport = array();

		if ($this->bActivate && !empty($this->aReport)) {
			// get unserialized reporting
			$aReport = $this->get();

			if (!empty($aReport) && is_array($aReport)) {
				foreach ($this->aReport as $sKeyName => $aProducts) {
					foreach ($this->aReport[$sKeyName] as $iKey => $mValue) {
						$aReport[$sKeyName][] = $mValue;
					}
				}
			}
			else {
				$aReport = $this->aReport;
			}
			$this->aReport = array();
		}

		return $aReport;
	}


	/**
	 * writeFile() method write Reporting file
	 *
	 * @param string $sContent
	 * @param string $sMode
	 * @param bool $bDebug
	 * @return int
	 */
	public function writeFile($sContent, $sMode = 'w', $bDebug = false)
	{
		$bWritten = 0;

		$rFile = @fopen($this->sFileName, $sMode);

		if (!empty($rFile)) {
		   $bWritten = @fwrite($rFile, serialize($sContent));

			if (!empty($bWritten)) {
				@fclose($rFile);
			}
		}

		return $bWritten;
	}

	/**
	 * create() method creates singleton
	 *
	 * @param bool $bActivate
	 * @return obj
	 */
	public static function create($bActivate = true)
	{
		if (null === self::$oReporting) {
			self::$oReporting = new BT_GmcReporting($bActivate);
		}
		return self::$oReporting;
	}

	/**
	 * create() method creates singleton
	 *
	 * @param bool $bActivate
	 * @return obj
	 */
	public static function destruct()
	{
		self::$oReporting = null;
	}
}