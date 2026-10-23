<?php
/**
 * base-ctrl_class.php file defines method to handle back-office controllers
 */

abstract class BT_GmcBaseCtrl
{
	/**
	 * @var string $sAction : defines action
	 */
	protected static $sAction = null;

	/**
	 * @var string $sType : defines type
	 */
	protected static $sType = null;

	/**
	 * get params keys
	 *
	 * @param array $aParams
	 */
	private  function __construct(array $aParams = null) {
		// defines type to execute
		// use case : no key sAction sent in POST mode (no form has been posted => first page is displayed with admin-display.class.php)
		// use case : key sAction sent in POST mode (form or ajax query posted ).
		$sAction = (!Tools::getIsset('sAction') || (Tools::getIsset('sAction') && 'display' == Tools::getValue('sAction')))? (Tools::getIsset('sAction')? Tools::getValue('sAction') : 'display') : Tools::getValue('sAction');

		// set action
		$this->setAction($sAction);

		// set type
		$this->setType();
	}

	/**
	 * run() method set value to a property of object
	 *
	 * @param array $aRequest
	 * @return array
	 */
	abstract public function run($aRequest);

	/**
	 * setType() method set type of method each controller has to execute
	 *
	 * @param string $sType
	 */
	public static function setType($sType = null)
	{
		self::$sType = $sType !== null? $sType : Tools::getValue('sType');
	}

	/**
	 * setAction() method set action and select which controller
	 *
	 * @param string $sAction
	 */
	public static function setAction($sAction = null)
	{
		self::$sAction = $sAction !== null? $sAction : Tools::getValue('sAction');
	}


	/**
	 * get() method instantiate matched ctrl object
	 *
	 * @param string $sCtrlType
	 * @param array $aParams
	 * @return obj ctrl type
	 */
	public static function get($sCtrlType, array $aParams = null)
	{
		$sCtrlType = strtolower($sCtrlType);

		// if valid controller
		if (file_exists(_GMC_PATH_LIB_ADMIN . $sCtrlType . '-ctrl_class.php')) {
			// require
			require_once($sCtrlType . '-ctrl_class.php');

			// set class name
			$sClassName = 'BT_' . ucfirst($sCtrlType) . 'Ctrl';

			try {
				$oReflection = new ReflectionClass($sClassName);

				if ($oReflection->isInstantiable()) {
					return $oReflection->newInstance($aParams);
				}
				else {
					throw new Exception(GMerchantCenter::$oModule->l('Internal server error => controller isn\'t instantiable', 'base-ctrl_class'), 900);
				}
			}
			catch (ReflectionException $e) {
				throw new Exception(GMerchantCenter::$oModule->l('Internal server error => invalid controller', 'base-ctrl_class'), 901);
			}
		}
		else {
			throw new Exception(GMerchantCenter::$oModule->l('Internal server error => the controller file doesn\'t exist', 'base-ctrl_class'), 902);
		}
	}
}