<?php
/**
 * ws-gmerchantcenter.php file execute module for Front Office and back-office as necessary
 *
 * @author    Business Tech SARL <http://www.businesstech.fr/en/contact-us>
 * @copyright 2003-2016 Business Tech SARL
 */

require_once(dirname(__FILE__) . '/../../config/config.inc.php');
require_once(dirname(__FILE__) . '/../../init.php');
require_once(dirname(__FILE__) . '/gmerchantcenter.php');

// get type of content to display
$sAction = Tools::getIsset('sAction') ? Tools::getValue('sAction') : '';
$sType = Tools::getIsset('sType') ? Tools::getValue('sType') : '';

// instantiate
$oModule = new GMerchantCenter();

$sUseCase = $sAction . $sType;

switch ($sUseCase) {
//	case 'searchProduct' :
//		// search product
//		echo $oModule->getContent();
//		break;
	default:
		break;
}