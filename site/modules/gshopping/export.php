<?php

/**
 * 2007-2014 PrestaShop
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2015 PrestaShop SA
 * @license   http://addons.prestashop.com/en/content/12-terms-and-conditions-of-use
 * International Registered Trademark & Property of PrestaShop SA
 */
include_once(dirname(__FILE__).'/../../config/config.inc.php');

require_once(dirname(__FILE__).'/classes/ExportClass.php');
require_once(dirname(__FILE__).'/classes/GoogleFeedConverter.php');
require_once(dirname(__FILE__).'/gshopping.php');

// Security check

$true_token = Configuration::get('token');
$token = Tools::getValue('token');

if ($token !== $true_token) {
    die(Tools::displayError('Tentative to hack !!!'));
}

$export_path = Configuration::get('export_path');
$table = GShopping::$categories_table;

$country = Tools::getValue('country');
$id_country = Tools::getValue('id_country');
$id_shop = Tools::getValue('id_shop');

$export_file_path = $export_path.$id_shop.'/'.$country.'/';

if(realpath($export_path) !== realpath(dirname(dirname($export_file_path))))	{
	 die (Tools::displayError('You do not have permission to view this.'));
}
$enable = new GoogleFeedConverter(0, 0);
$feed = '<?xml version="1.0"  encoding="UTF-8"?><rss xmlns:g="http://base.google.com/ns/1.0" version="2.0"><channel>';
$category_enable = $enable->getActiveExportCategory($id_country, $id_shop, $table);

foreach ($category_enable as $key => $id_object) {
    $id_object = $id_object['id_object'];

    $feed .= Tools::file_get_contents($export_file_path.$id_object.'.txt');
}
$feed .= '</channel></rss>';

header("Content-Type:text/xml; charset=utf-8");
exit($feed);
