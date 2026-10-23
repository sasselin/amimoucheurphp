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
	public static function setCurrency()
	{
		global $cookie;

		// ### MOD BY BUSINESS TECH ###
		if (self::getIsset('gmc_currency') && is_numeric(self::getValue('gmc_currency')))
		{
			$currency = Currency::getCurrencyInstance((int)self::getValue('gmc_currency'));
			if ($currency->id && !$currency->deleted && $currency->active)
			{
				$cookie->id_currency = (int)$currency->id;
				return $currency;
			}
		}
		// ### END MOD BY BUSINESS TECH ###

		if (self::isSubmit('SubmitCurrency') && self::getIsset('id_currency') && is_numeric(self::getValue('id_currency')))
		{
			$currency = Currency::getCurrencyInstance((int)self::getValue('id_currency'));
			if ($currency->id && !$currency->deleted && $currency->active)
			{
				$cookie->id_currency = (int)$currency->id;
				return $currency;
			}
		}

		if ((int)$cookie->id_currency)
		{
			$currency = Currency::getCurrencyInstance((int)$cookie->id_currency);
			if ((int)$currency->id && !$currency->deleted && $currency->active)
				return $currency;
		}
		$currency = Currency::getCurrencyInstance((int)_PS_CURRENCY_DEFAULT_);
		if ($currency->id)
			$cookie->id_currency = (int)$currency->id;

		return $currency;
	}
}