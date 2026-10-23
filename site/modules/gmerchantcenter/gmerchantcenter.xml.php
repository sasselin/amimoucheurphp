<?php
/**
 * gmerchantcenter.xml.php file execute the fly output data feed
 *
 * @author    Business Tech SARL <http://www.businesstech.fr/en/contact-us>
 * @copyright 2003-2016 Business Tech SARL
 */

require_once(dirname(__FILE__).'/config/config.inc.php');
require_once(dirname(__FILE__).'/init.php');
require_once(_PS_MODULE_DIR_.'/gmerchantcenter/gmerchantcenter.php');

// instantiate
$oMainClass = new GMerchantCenter();

// use case - handle to generate XML files
$_POST['sAction'] = Tools::getIsset('sAction')? Tools::getValue('sAction') : 'generate';
$_POST['sType'] = Tools::getIsset('sType')? Tools::getValue('sType') : 'flyOutput';

echo $oMainClass->getContent();