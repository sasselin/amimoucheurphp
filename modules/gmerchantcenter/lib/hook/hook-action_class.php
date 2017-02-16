<?php
/**
 * hook-action_class.php file defines controller which manage hooks sequentially
 */

class BT_GmcHookAction extends BT_GmcHookBase
{
	/**
	 * Magic Method __destruct
	 *
	 * @category hook collection
	 *
	 */
	public function __destruct()
	{
		unset($this);
	}

	/**
	 * run() method execute hook
	 *
	 * @param array $aParams
	 * @return array
	 */
	public function run(array $aParams = null)
	{
		// set variables
		$aDisplayHook = array();

		switch ($this->sHook) {
			case 'searchProduct' :
				// use case - display nothing only process storage in order to send an email
				$aDisplayHook = call_user_func_array(array($this, 'searchProduct'), array($aParams));
				break;
			default :
				break;
		}

		return $aDisplayHook;
	}

	/**
	 * searchProduct() method search product with the autocomplete feature
	 *
	 * @param array $aParams
	 * @return array
	 */
	private function searchProduct(array $aParams = null)
	{



		return (
			array('tpl' => _GMC_TPL_HOOK_PATH . _GMC_TPL_ORDER_CONFIRMATION, 'assign' => array())
		);
	}
}