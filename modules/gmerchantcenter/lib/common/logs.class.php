<?php
/**
 * logs.class.php file defines methods manage log files
 */

class BT_Logs
{
	/**
	 * @var array $_aConfig
	 */
	private $_aConfig = array();

	/**
	 * @var object $obj
	 */
	private $_aLogs = array();

	/**
	 * __construct() magic method initialize variables
	 *
	 * @param array $config
	 */
	public function __construct($config)
	{
		// store conf array
		$this->_aConfig = $config;

		// verify if file path is set
		if (!isset($this->_aConfig['FilePath'])) {
			throw new Exception('No log file specified', -1);
		}
		// verify if file is writable
		if (is_file($this->_aConfig['FilePath']) && ! is_writable($this->_aConfig['FilePath'])) {
			throw new Exception('Unable to write into ' . $this->_aConfig['FilePath'], -1);
		}

		// verify if folder is writable
		if (is_dir(dirname($this->_aConfig['FilePath'])) && ! is_writable(dirname($this->_aConfig['FilePath']))) {
			throw new Exception('Unable to write into ' . dirname($this->_aConfig['FilePath']), -1);
		}
		// verify functions
		if (isset($this->_aConfig['InfosFunctions'])) {
			foreach ($this->_aConfig['InfosFunctions'] as $pattern => $method) {
				if (is_array($method)) {
					if (!class_exists($method[0])) {
						throw new Exception('Class \'' . $method[0] . '\' not found', -1);
					}
					if (!method_exists($method[0], $method[1])) {
						throw new Exception('Call to undefined method ' . $method[0] . '::' . $method[1] . '()', -1);
					}
				}
				elseif (!function_exists($method)) {
					throw new Exception('Call to undefined function ' . $method . '()', -1);
				}
			}
		}
		// verify is asked to erase file
		if (isset($this->_aConfig['EraseFile']) && $this->_aConfig['EraseFile'] === true) {
			$this->eraseFile();
		}

		// set default values if not exists
		if (!isset($this->_aConfig['Separator'])) {
			$this->_aConfig['Separator'] = "\t";
		}

		// set default values if not exists
		if (!isset($this->_aConfig['DateTimeFormat'])) {
			$this->_aConfig['DateTimeFormat'] = 'Y-m-d H:i:s';
		}

		if (!isset($this->_aConfig['SendByMail']) || ($this->_aConfig['SendByMail'] && !isset($this->_aConfig['MailTo']))) {
			$this->_aConfig['SendByMail'] = false;
		}

		if (!isset($this->_aConfig['EmptyValue'])) {
			$this->_aConfig['EmptyValue'] = '';
		}

		if (!isset($this->_aConfig['Pattern'])) {
			$this->_aConfig['Pattern'] = array(
				'datetime',
				'script',
				'script-args'
			);
		}
	}

	/**
	 * __destruct() magic method
	 */
	public function __destruct()
	{
		// send mail if needed
		if ($this->_aConfig['SendByMail']) {
			mail($this->_aConfig['MailTo'], basename($this->_aConfig['FilePath']), join("\n", $this->_aLogs));
		}
	}

	/**
	 * getLogs() method return the current log content
	 *
	 * @return array
	 */
	public function getLogs()
	{
		return (
		$this->_aLogs
		);
	}

	/**
	 * eraseFile() method delete log file
	 *
	 */
	public function eraseFile()
	{
		if (file_exists($this->_aConfig['FilePath'])) {
			@unlink($this->_aConfig['FilePath']);
		}
	}

	/**
	 * write() write log file
	 *
	 * @param string $content
	 * @return mixed : false or int
	 */
	public function write($content)
	{
		$line = '';

		// get base info from pattern
		foreach ($this->_aConfig['Pattern'] as $key) {
			$line .= (empty($line) ? '' : $this->_aConfig['Separator']) . $this->_getValueOf($key);
		}
		// add content to log
		if (is_array($content)) {
			$line .= (empty($line) ? '' : $this->_aConfig['Separator']) . join($this->_aConfig['Separator'], $content);
		}
		else {
			$line .= (empty($line) ? '' : $this->_aConfig['Separator']) . $content;
		}

		// store line into array
		$this->_aLogs[] = $line;

		// verify max file size
		if (isset($this->_aConfig['MaxFileSize']) && file_exists($this->_aConfig['FilePath'])) {
			if (filesize($this->_aConfig['FilePath']) >= $this->_aConfig['MaxFileSize']) {
				$archiveName = substr($this->_aConfig['FilePath'], 0, strrpos($this->_aConfig['FilePath'],'.')) . '-' . time() . substr($this->_aConfig['FilePath'], strrpos($this->_aConfig['FilePath'],'.'));
				rename($this->_aConfig['FilePath'], $archiveName);
			}
		}
		// verify if log file is empty to write header
		if (!file_exists($this->_aConfig['FilePath']) || '' == file_get_contents($this->_aConfig['FilePath'])) {
			file_put_contents($this->_aConfig['FilePath'],
				join($this->_aConfig['Separator'], array_merge($this->_aConfig['Pattern'], array('content'))) . "\n", FILE_APPEND);
		}
		// append line to log file
		return (
			file_put_contents($this->_aConfig['FilePath'], $line . "\n", FILE_APPEND)
		);
	}

	/**
	 * _getValueOf() get value of key
	 * @param string $key
	 * @return string
	 */
	private function _getValueOf($key)
	{
		global $argv;

		switch ($key) {
			// date and time
			case 'datetime':
				return (date($this->_aConfig['DateTimeFormat']));
				break;

			// script name or uri
			case 'script':
				if (isset($_SERVER['SCRIPT_NAME'])) {
					return ($_SERVER['SCRIPT_NAME']);
				}
				elseif (isset($argv[0]) && !empty($argv[0])) {
					return ($argv[0]);
				}
				elseif (isset($_SERVER['PHP_SELF'])) {
					return ($_SERVER['PHP_SELF']);
				}
				return ($this->_aConfig['EmptyValue']);
				break;

			// script args or query string
			case 'script-args':
				if (isset($_SERVER['QUERY_STRING'])) {
					return ($_SERVER['QUERY_STRING']);
				}
				elseif (isset($argv) && count($argv) > 1) {
					return (str_replace($argv[0] . ' ', '', join(' ', $argv)));
				}
				elseif (isset($_SERVER['PHP_SELF'])) {
					return ($_SERVER['PHP_SELF']);
				}
				return ($this->_aConfig['EmptyValue']);
				break;

			// ip address or user
			case 'remote-ip':
				if (isset($_SERVER['REMOTE_ADDR'])) {
					return ($_SERVER['REMOTE_ADDR']);
				}
				return ($this->_aConfig['EmptyValue']);
				break;

			// http user agent
			case 'http-user-agent':
				if (isset($_SERVER['HTTP_USER_AGENT'])) {
					return ($_SERVER['HTTP_USER_AGENT']);
				}
				return ($this->_aConfig['EmptyValue']);
				break;

			// http host
			case 'http-host':
				if (isset($_SERVER['HTTP_HOST'])) {
					return ($_SERVER['HTTP_HOST']);
				}
				return ($this->_aConfig['EmptyValue']);
				break;

			// call user's info if needed
			default:
				if (isset($this->_aConfig['InfosFunctions'][$key])) {
					return (call_user_func($this->_aConfig['InfosFunctions'][$key]));
				}
				return ($this->_aConfig['EmptyValue']);
				break;
		}
	}
}