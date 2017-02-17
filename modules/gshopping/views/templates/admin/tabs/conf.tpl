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

<h3><i class="icon-book"></i> {l s='Configruration' mod='gshopping'} <small>{$module_display|escape:'htmlall':'UTF-8'}</small></h3>
<div class="row">
	<div class="form-group clear">
		<div class="panel panel-default">
			<p><b>{l s='Welcome to the Configuration tab.' mod='gshopping'}</b></p>
			<p>
				{l s='Here you can configure your feed to Google for entire catalogue or single category feed generation' mod='gshopping'}
			</p>
			<p>&nbsp;</p>
				{if !empty($countries)}
					<div class="alert alert-info">
						<button class="close" aria-hidden="true" data-dismiss="alert" type="button">×</button>
						{l s='First, select the country for which you want to set the feed. You can configure as many as feeds for all countries that you want. For further information about feed destinations see this' mod='gshopping'}
						<a href='https://support.google.com/merchants/topic/3406788?hl=en&ref_topic=3404780'>{l s=' link.' mod='gshopping'}</a>
						<br/>
						{l s='You should configure the categories for each country that you want to export. You can active new countries from the location tab.' mod='gshopping'}
						<br/>
						<br/>
						<b>{l s='Be careful:' mod='gshopping'}</b> {l s='The currency configured for all countries is your shop default currency. You can change the default currency for a country on Localization/ Countries tab. See User documentation for further information.' mod='gshopping'}
						<a href='{$module_dir|escape:'htmlall':'UTF-8'}{$guide_link|escape:'htmlall':'UTF-8'}' target="_blank">(C.f. documentation).</a>
					</div>
				{/if}
			<div>
				{if !empty($countries)}
				<select id="select_country" class="selectpicker show-menu-arrow show-tick select_country" data-show-subtext="true" data-live-search="true" data-table="">
					{foreach from=$countries item=country}
						<option value="{$country.id_country|intval}" {if $country.id_country|intval == $country_default}selected="selected"{/if} id_country="{$country.id_country|intval}" export="0"> {$country.name}</option>
					{/foreach}
				</select>
				{/if}
			</div>
			<div>
				<br/>
				<div class="alert alert-info">
					<button class="close" aria-hidden="true" data-dismiss="alert" type="button">×</button>
					{l s='Below you will find all the categories of your shop (both, root categories and sub-categories). Note that “Home” is not an eligible category for the feed. You can configure the categories that you want to include on your feed.' mod='gshopping'}
					<br/>
					<br/>
					<b>{l s='Be careful: ' mod='gshopping'}</b>{l s='If you configure a category for the feed but this category has not been enabled as a default category for any product, the feed will be empty. Do not forget to active the categories that you want to export! Only the activate categories will be included on the feed. ' mod='gshopping'}
					<a href="{$module_dir|escape:'htmlall':'UTF-8'}{$guide_link|escape:'htmlall':'UTF-8'}" target="_blank">(C.f. documentation).</a>
				</div>
				{counter start=0 assign="count_rule" print=false}
				{include file=$table_tpl_path node=$rule_history role='metas'}
			</div>
		</div>
	</div>
</div>
