<?php
/**
 * admin-delete_class.php file defines method to delete content
 */

class BT_AdminDelete implements BT_IAdmin
{
	/**
	 * Magic Method __construct
	 */
	private function __construct()
	{

	}

	/**
	 * Magic Method __destruct
	 */
	public function __destruct()
	{

	}

	/**
	 * run() method delete content
	 *
	 * @param string $sType => define which method to execute
	 * @param array $aParam
	 * @return array
	 */
	public function run($sType, array $aParam = null)
	{
		// set variables
		$aDisplayData = array();

		switch ($sType) {
			case 'label' : // use case - delete custom label
				// execute match function
				$aDisplayData = call_user_func_array(array($this, '_delete' . ucfirst($sType)), array($aParam));
				break;
			default :
				break;
		}
		return (
			$aDisplayData
		);
	}

	/**
	 * _delete() method delete one tag label
	 *
	 * @param array $aPost
	 * @return array
	 */
	private function _deleteLabel(array $aPost)
	{
		// clean headers
		@ob_end_clean();

		// set
		$aData = array();

		try {
			$iTagId = Tools::getValue('iTagId');

			if (empty($iTagId)) {
				throw new Exception(GMerchantCenter::$oModule->l('Your Custom label ID is not valid', 'admin-update_class') . '.', 700);
			}
			else {
				// include
				require_once(_GMC_PATH_LIB . 'module-dao_class.php');

				BT_GmcModuleDao::deleteGmcTag($iTagId, $GLOBALS[_GMC_MODULE_NAME . '_LABEL_LIST']);
			}
			unset($iTagId);
		}
		catch (Exception $e) {
			$aData['aErrors'][] = array('msg' => $e->getMessage(), 'code' => $e->getCode());
		}

		// get configuration options
		BT_GmcModuleTools::getConfiguration();

		// require admin configure class - to factorise
		require_once(_GMC_PATH_LIB_ADMIN . 'admin-display_class.php');

		// get run of admin display in order to display first page of admin with basics settings updated
		$aDisplay = BT_AdminDisplay::create()->run('google');

		// use case - empty error and updating status
		$aDisplay['assign'] = array_merge($aDisplay['assign'], array(
			'bUpdate' => (empty($aData['aErrors']) ? true : false),
		), $aData);

		// destruct
		unset($aData);

		return $aDisplay;
	}


	/**
	 * create() method set singleton
	 *
	 * @param
	 * @return obj
	 */
	public static function create()
	{
		static $oDelete;

		if (null === $oDelete) {
			$oDelete = new BT_AdminDelete();
		}
		return $oDelete;
	}
}