<?php
/**
 * zip.class.php define Zip class
 * can create zip file from many files as PHP method
 */

class BT_Zip
{
	/**
	 * @var array $_aDataSec
	 */
	protected $_aDataSec = array();

	/**
	 * @var array  $_aCtrlDir
	 */
	protected $_aCtrlDir = array();

	/**
	 * @var string $_sEoFCtrlDir
	 */
	protected $_sEoFCtrlDir = null;

	/**
	 * @var int $_iOldOffset
	 */
	protected $_iOldOffset = null;

	/**
	 * __construct() method initialize vars
	 */
	public function __construct()
	{
		$this->_aDataSec = array();
		$this->_aCtrlDir = array();
		$this->_sEoFCtrlDir = "\x50\x4b\x05\x06\x00\x00\x00\x00";
		$this->_iOldOffset = 0;
	}

	/**
	 * add() method add file to the current archive
	 *
	 * @param string $sFileName : file name in archive file
	 * @param string $sFilePath : file path to add
	 * @param int $iTimeStamp : file date
	 */
	public function add($sFileName, $sFilePath, $iTimeStamp = 0)
	{
		$data = file_get_contents($sFilePath);
		$dtime    = dechex($this->unix2DosTime($iTimeStamp));
		$hexdtime = '\x' . $dtime[6] . $dtime[7]
			. '\x' . $dtime[4] . $dtime[5]
			. '\x' . $dtime[2] . $dtime[3]
			. '\x' . $dtime[0] . $dtime[1];
		$hexdtime = $this->hex2bin($hexdtime);

		/**
		 * @deprecated do not use eval
		 */
//		eval('$hexdtime = "' . $hexdtime . '";');
		$fr   = "\x50\x4b\x03\x04";
		$fr   .= "\x14\x00";
		$fr   .= "\x00\x00";
		$fr   .= "\x08\x00";
		$fr   .= $hexdtime;
		$unc_len = strlen($data);
		$crc     = crc32($data);
		$zdata   = gzcompress($data);
		$zdata   = substr(substr($zdata, 0, strlen($zdata) - 4), 2);
		$c_len   = strlen($zdata);
		$fr      .= pack('V', $crc);
		$fr      .= pack('V', $c_len);
		$fr      .= pack('V', $unc_len);
		$fr      .= pack('v', strlen($sFileName));
		$fr      .= pack('v', 0);
		$fr      .= $sFileName;
		$fr .= $zdata;
		$this->_aDataSec[] = $fr;
		$cdrec = "\x50\x4b\x01\x02";
		$cdrec .= "\x00\x00";
		$cdrec .= "\x14\x00";
		$cdrec .= "\x00\x00";
		$cdrec .= "\x08\x00";
		$cdrec .= $hexdtime;
		$cdrec .= pack('V', $crc);
		$cdrec .= pack('V', $c_len);
		$cdrec .= pack('V', $unc_len);
		$cdrec .= pack('v', strlen($sFileName));
		$cdrec .= pack('v', 0 );
		$cdrec .= pack('v', 0 );
		$cdrec .= pack('v', 0 );
		$cdrec .= pack('v', 0 );
		$cdrec .= pack('V', 32 );
		$cdrec .= pack('V', $this->_iOldOffset);
		$this->_iOldOffset += strlen($fr);
		$cdrec .= $sFileName;
		$this->_aCtrlDir[] = $cdrec;
	}

	/**
	 * createZip() method create zip file
	 *
	 * @param string $sZipPath : file path
	 */
	public function createZip($sZipPath)
	{
		$data    = implode('', $this->_aDataSec);
		$ctrldir = implode('', $this->_aCtrlDir);

		file_put_contents($sZipPath,
			$data . $ctrldir . $this->_sEoFCtrlDir .
			pack('v', sizeof($this->_aCtrlDir)) .
			pack('v', sizeof($this->_aCtrlDir)) .
			pack('V', strlen($ctrldir)) .
			pack('V', strlen($data)) .
			"\x00\x00");
	}

	/**
	 * unix2DosTime() called by add() method
	 *
	 * @param int $iUnixTime
	 * @return int
	 */
	protected function unix2DosTime($iUnixTime = 0)
	{
		$aTime = ($iUnixTime == 0) ? getdate() : getdate($iUnixTime);

		if ($aTime['year'] < 1980) {
			$aTime['year']    = 1980;
			$aTime['mon']     = 1;
			$aTime['mday']    = 1;
			$aTime['hours']   = 0;
			$aTime['minutes'] = 0;
			$aTime['seconds'] = 0;
		}
		return (
			($aTime['year'] - 1980) << 25)
			| ($aTime['mon'] << 21)
			| ($aTime['mday'] << 16)
			| ($aTime['hours'] << 11)
			| ($aTime['minutes'] << 5)
			| ($aTime['seconds'] >> 1
		);
	}

	/**
	 * hex2bin() transform an HEXA string to an BINARY string
	 *
	 * @param string $data : HEXA content
	 * @return string : BINARY content
	 */
	protected function hex2bin($data)
	{
		$sNewData = null;
		$len = strlen($sNewData);

		for ($i=0;$i<$len;$i+=4) {
			$sNewData .= pack('C',hexdec(substr($data,$i,2)));
		}
		return $sNewData;
	}
}