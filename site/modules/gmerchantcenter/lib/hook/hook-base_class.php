<?php
/**
 * hook-base_class.php file defines controller which manage hooks sequentially
 */
abstract class BT_GmcHookBase
{
	/**
	 * @var string $sHook : define hook display or action
	 */
	protected $sHook = null;

	/**
	 * Magic Method __construct assigns few information about hook
	 *
	 * @param string $sHook
	 */
	public function __construct($sHook)
	{
		// set hook
		$this->sHook = $sHook;
	}

	/**
	 * run() method execute hook
	 *
	 * @category hook collection
	 * @uses
	 *
	 * @param array $aParams
	 * @return array
	 */
	abstract public function run(array $aParams = null);
}
