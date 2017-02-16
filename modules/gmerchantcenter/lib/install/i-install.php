<?php
/**
 * i-install.php file defines mandatory method to make module install / uninstall
 */
interface BT_IInstall
{
	/**
	 * install() method make installation of module
	 *
	 * @param mixed $mParam: array (constant to update with Configuration:updateValue) in config install / string of sql filename in sql install / array of admin tab to install
	 * @return bool
	 */
	public static function install($mParam = null);
	
	/**
	 * uninstall() method make uninstallation of module
	 *
	 * @param mixed $mParam: array (constant to update with Configuration:deleteByName) in config install / string of sql filename in sql install / array of admin tab to uninstall
	 * @return bool
	 */
	public static function uninstall($mParam = null);
}