{*
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
*}

<div class="btn btn-default pull-right">
	<a data-idobject="{$prod.id_object|intval}" data-idcategory="{$prod.id_category|intval}" data-name="{$prod.name|escape:'htmlall':'UTF-8'}" data-idcountry="{$prod.id_country|escape:'htmlall':'UTF-8'}" class="pointer edit">
		{if $check|intval != 1}
		<i class="icon-pencil"></i> {l s='Manage details category' mod='gshopping'}
		{else}
		<i class="icon-pencil"></i> {l s='Edit category configuration' mod='gshopping'}
		{/if}
	</a>
</div>
