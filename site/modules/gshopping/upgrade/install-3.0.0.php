<?php

/**
 * 2007-2015 PrestaShop
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
if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_3_0_0($object)
{
    //Remove element of the old module
    Configuration::deleteByName('GSHOPPING_ID_FEATURE_AUTHOR');
    Configuration::deleteByName('GSHOPPING_ID_FEATURE_YEAR');
    Configuration::deleteByName('GSHOPPING_ID_FEATURE_EDITION');
    Configuration::deleteByName('GSHOPPING_ID_ATTRIBUTE_COLOR');
    Configuration::deleteByName('GSHOPPING_ID_ATTRIBUTE_SIZE');
    Configuration::deleteByName('GSHOPPING_COMBINATIONS');
    Configuration::deleteByName('GSHOPPING_REFERENCES');
    Configuration::deleteByName('GSHOPPING_COMPLEMENT');
    Configuration::deleteByName('GSHOPPING_SHIPPING');
    Configuration::deleteByName('GSHOPPING_CARRIER');

    Db::getInstance()->execute('DROP TABLE`'._DB_PREFIX_.'google_shopping`');

    $object->installSQL();
    $object->installTab();
    $object->refreshTableSQLData();
    $object->genTaxonomyCache();
    $object->getToken();
    $object->getExportPath();

    $object->registerHook('actionObjectCategoryAddAfter');
    $object->registerHook('actionObjectCategoryAddAfter');
    $object->registerHook('actionObjectCategoryUpdateAfter');
    $object->registerHook('actionObjectCategoryDeleteAfter');

    $object->registerHook('actionObjectCarrierAddAfter');
    $object->registerHook('actionObjectCarrierUpdateAfter');
    $object->registerHook('actionObjectCarrierDeleteAfter');

    $object->registerHook('actionObjectCurrencyAddAfter');
    $object->registerHook('actionObjectCurrencyUpdateAfter');
    $object->registerHook('actionObjectCurrencyDeleteAfter');

    $object->registerHook('actionObjectProductAddAfter');
    $object->registerHook('actionObjectProductUpdateAfter');

    $object->registerHook('actionObjectCMSCategoryAddAfter');
    $object->registerHook('actionObjectCMSCategoryUpdateAfter');
    $object->registerHook('actionObjectCMSCategoryDeleteAfter');

    $object->registerHook('actionObjectCountriesAddAfter');
    $object->registerHook('actionObjectCountriesUpdateAfter');
    $object->registerHook('actionObjectCountriesDeleteAfter');
    $object->registerHook('actionObjectLanguageUpdateAfter');
    $object->registerHook('actionObjectLanguageDeleteAfter');

    $object->registerHook('displayHeader');
    $object->registerHook('displayBackOfficeHeader');

    return true;
}
