<?php
/**
 * 2007-2016 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2016 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0) International Registered Trademark & Property of PrestaShop SA
 */

class Tools extends ToolsCore
{
	/**
	 * Set cookie currency from POST or default currency
	 *
	 * Overriden by Business Tech to allow setting from $_GET as well
	 * for our Google Merchant Center module
	 *
	 * @return Currency object
	 */
	public static function setCurrency($cookie)
	{
		// ### MOD BY BUSINESS TECH ###
		if (self::getIsset('gmc_currency') && is_numeric(self::getValue('gmc_currency'))) {
			$currency = Currency::getCurrencyInstance((int)(self::getValue('gmc_currency')));
			if (is_object($currency) && $currency->id && !$currency->deleted && $currency->isAssociatedToShop()) {
				$cookie->id_currency = (int)$currency->id;
			}
		}
		// ### END MOD BY BUSINESS TECH ###

		if (Tools::isSubmit('SubmitCurrency'))
			if (self::getIsset('id_currency') && is_numeric(self::getValue('id_currency')))
			{
				$currency = Currency::getCurrencyInstance(self::getValue('id_currency'));
				if (is_object($currency) && $currency->id && !$currency->deleted && $currency->isAssociatedToShop())
					$cookie->id_currency = (int)$currency->id;
			}

		$currency = Currency::getCurrencyInstance(Configuration::get('PS_CURRENCY_DEFAULT'));
		if ((int)$cookie->id_currency)
			$currency = Currency::getCurrencyInstance((int)$cookie->id_currency);

		if (is_object($currency) && (int)$currency->id && (int)$currency->deleted != 1 && $currency->active)
		{
			$cookie->id_currency = (int)$currency->id;
			if ($currency->isAssociatedToShop())
				return $currency;
			else
			{
				// get currency from context
				$currency = Shop::getEntityIds('currency', Context::getContext()->shop->id, true, true);
				if (isset($currency[0]) && $currency[0]['id_currency'])
				{
					$cookie->id_currency = $currency[0]['id_currency'];
					return Currency::getCurrencyInstance((int)$cookie->id_currency);
				}
			}
		}
		return $currency;
	}
}