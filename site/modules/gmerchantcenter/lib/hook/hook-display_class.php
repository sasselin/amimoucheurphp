<?php
/**
 * hook-display_class.php file defines controller which manage hooks sequentially
 */

class BT_GmcHookDisplay extends BT_GmcHookBase
{
	/**
	 * Magic Method __destruct
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

		// detect if there is any error to stop the execution
		if (BT_GmcWarning::create()->bStopExecution) {
			/* TODO : assign or execute a related action to the stop execution */
		}

		switch ($this->sHook) {
			case 'header'           :
				// use case - display in header
				$aDisplayHook = call_user_func(array($this, '_displayHeader'));
				break;
			default :
				break;
		}

		return $aDisplayHook;
	}

	/**
	 * _displayHeader() method
	 *
	 * @return array
	 */
	private function _displayHeader()
	{
		// set
		$aAssign = array();
		$aAssign['bAddJsCss'] = false;

		// set js msg translation
		BT_GmcModuleTools::translateJsMsg();

		$aAssign['oJsTranslatedMsg']  = BT_GmcModuleTools::jsonEncode($GLOBALS[_GMC_MODULE_NAME . '_JS_MSG']);
		$aAssign['sModuleURI'] = _GMC_MODULE_URL . 'ws-' . _GMC_MODULE_SET_NAME . '.php';

		// add in minify process by prestahsop
		Context::getContext()->controller->addJS(_GMC_URL_JS . 'module.js');
		Context::getContext()->controller->addCSS(_GMC_URL_CSS . 'front.css');

		return (
			array('tpl' => _GMC_TPL_HOOK_PATH . _GMC_TPL_HEADER, 'assign' => $aAssign)
		);
	}
}