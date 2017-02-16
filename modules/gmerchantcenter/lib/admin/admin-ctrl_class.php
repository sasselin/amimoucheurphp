<?php
/**
 * admin-ctrl_class.php file defines controller which manage type of derived admin object of abstract type as factory pattern
 */

class BT_AdminCtrl extends BT_GmcBaseCtrl
{
	/**
	 * Magic Method __construct
	 *
	 * @param array $aParams
	 */
	public function __construct(array $aParams = null)
	{
		// defines type to execute
		// use case : no key sAction sent in POST mode (no form has been posted => first page is displayed with admin-display.class.php)
		// use case : key sAction sent in POST mode (form or ajax query posted ).
		$sAction = (!Tools::getIsset('sAction') || (Tools::getIsset('sAction') && 'display' == Tools::getValue('sAction')))? (Tools::getIsset('sAction')?Tools::getValue('sAction') : 'display') : Tools::getValue('sAction');

		// set action
		$this->setAction($sAction);

		// set type
		$this->setType();
	}

	/**
	 * Magic Method __destruct
	 */
	public function __destruct()
	{

	}


	/**
	 * run() method execute abstract derived admin object
	 *
	 * @param array $aRequest : request
	 * @return array $aDisplay : empty => false / not empty => true
	 */
	public function run($aRequest)
	{
		// set
		$aDisplay = array();
		$aParams = array();

		// include interface
		require_once(_GMC_PATH_LIB_ADMIN . 'i-admin.php');

		switch (self::$sAction) {
			case 'display' :
				// include admin display object
				require_once(_GMC_PATH_LIB_ADMIN . 'admin-display_class.php');
				require_once(_GMC_PATH_LIB . 'warning_class.php');

				// check warning for prerequisites
				BT_GmcWarning::create()->bStopExecution = (BT_GmcModuleTools::checkOutputFile() == true? false : true);

				$oAdminType = BT_AdminDisplay::create();

				BT_GmcModuleTools::getConfiguration();

				// update new module keys
				BT_GmcModuleTools::updateConfiguration();

				// use case - type not define => first page requested
				if (empty(self::$sType)) {
					// update module version
					Configuration::updateValue('GMERCHANTCENTER_VERSION', GMerchantCenter::$oModule->version);

					// update module if necessary
					$aParams['aUpdateErrors'] = GMerchantCenter::$oModule->updateModule();
				}

				// get configuration options
				BT_GmcModuleTools::getConfiguration(array('GMERCHANTCENTER_HOME_CAT', 'GMERCHANTCENTER_COLOR_OPT', 'GMERCHANTCENTER_SIZE_OPT', 'GMERCHANTCENTER_SHIP_CARRIERS', 'GMERCHANTCENTER_CHECK_EXPORT', 'GMERCHANTCENTER_PROD_EXCL'));

				// set js msg translation
				BT_GmcModuleTools::translateJsMsg();

				// set params
				$aParams['oJsTranslatedMsg'] = BT_GmcModuleTools::jsonEncode($GLOBALS[_GMC_MODULE_NAME . '_JS_MSG']);
				break;
			case 'update'   :
				// include admin update object
				require_once(_GMC_PATH_LIB_ADMIN . 'admin-update_class.php');

				$oAdminType = BT_AdminUpdate::create();
				break;
			case 'delete'   :
				// include admin delete object
				require_once(_GMC_PATH_LIB_ADMIN . 'admin-delete_class.php');

				$oAdminType = BT_AdminDelete::create();
				break;
			case 'generate'   :
				// include admin generate object
				require_once(_GMC_PATH_LIB_ADMIN . 'admin-generate_class.php');

				$oAdminType = BT_AdminGenerate::create();
				break;
			case 'send'   :
				// include admin send object
				require_once(_GMC_PATH_LIB_ADMIN . 'admin-send_class.php');

				$oAdminType = BT_AdminSend::create();
				break;
			default :
				$oAdminType = false;
				break;
		}

		// process data to use in view (tpl)
		if (!empty($oAdminType)) {
			// execute good action in admin
			// only displayed with key : tpl and assign in order to display good smarty template
			$aDisplay = $oAdminType->run(parent::$sType, $aRequest);

			if (!empty($aDisplay)) {
				$aDisplay['assign'] = array_merge($aDisplay['assign'], $aParams, array('bAddJsCss' => true));
			}

			// destruct
			unset($oAdminType);
		}

		return $aDisplay;
	}
}