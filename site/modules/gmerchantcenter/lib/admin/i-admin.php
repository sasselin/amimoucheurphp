<?php
/**
 * i-admin.php file defines mandatory method to manage module's admin
 */

interface BT_IAdmin
{
	/**
	 * run() method process display or updating or etc ... admin
	 *
	 * @param string $sType => defines which method to execute
	 * @param mixed $aParam => $_GET or $_POST
	 * @return bool
	 */
	public function run($sType, array $aParam = null);
}