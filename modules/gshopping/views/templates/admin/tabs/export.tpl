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

<h3><i class="icon-upload"></i> {l s='Export' mod='gshopping'} <small>{$module_display|escape:'htmlall':'UTF-8'}</small></h3>
<div class="row">
	<div class="form-group clear">
		<div class="panel panel-default">
			{if !empty($countries)}
				<div class="alert alert-info">
					<button class="close" aria-hidden="true" data-dismiss="alert" type="button">×</button>
					{l s='Select the country to which you are going to send the feed. For further information about feed destinations see this'  mod='gshopping'}
					<a target="_blank" href='https://support.google.com/merchants/answer/160157?hl=en&ref_topic=3406788'>{l s=' link.' mod='gshopping'}</a>
				</div>
			{/if}
			<div>
				{if !empty($countries)}
				<select id="select_country_export" class="selectpicker show-menu-arrow show-tick select_country" data-show-subtext="true" data-live-search="true">
					{foreach from=$countries item=country}
						<option value="{$country.id_country|intval}" {if $country.id_country|intval == $country_default}selected="selected"{/if} id_country="{$country.id_country|intval}" export="1"> {$country.name}</option>
					{/foreach}
				</select>
				{/if}
			</div>
			<br/>
			<div class="alert alert-info">
				<button class="close" aria-hidden="true" data-dismiss="alert" type="button">×</button>
				{l s='You will find below the link that contain your feed for the selected country. You only need to copy-paste this link to include it to Google merchant center'  mod='gshopping'}
				<a target="_blank" href='https://merchants.google.com'>{l s=' here.' mod='gshopping'}</a>
				<br/>
				{l s='See User documentation for further information.'  mod='gshopping'}
				<a href="{$module_dir|escape:'htmlall':'UTF-8'}{$guide_link|escape:'htmlall':'UTF-8'}" target="_blank">(C.f. documentation).</a>
			</div>
			<div class="panel panel-default" id="export_link">
				<h3>
					{l s='Link for google merchant center for : '  mod='gshopping'}
					<b>{$country.name|escape:'htmlall':'UTF-8'}</b>
				</h3>
				<label>
					{l s='You must configure the export first for this country.' mod='gshopping'}
				</label>
			</div>
			<br/>
			<br/>
			<div class="panel panel-default" id="export_info">
				<h3>
					{l s='Reminder of the feed caracterisitics : '  mod='gshopping'}
				</h3>
				<br/>
				<label>
					{l s='Number of categories selected for the feed'  mod='gshopping'}
				</label>
				<span class="badge">None</span>
				<hr/>
				<label>
					{l s='Number of products concern by the feed'  mod='gshopping'}
				</label>
				<span class="badge">None</span>
			</div>
		</div>
	</div>
</div>
