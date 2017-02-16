{*
* 2003-2016 Business Tech
*
* @author Business Tech SARL <http://www.businesstech.fr/en/contact-us>
* @copyright  2003-2016 Business Tech SARL
*}
{if !empty($bUpdate)}
	{include file="`$sConfirmInclude`"}
{elseif !empty($aErrors)}
	{include file="`$sErrorInclude`"}
{/if}
{if !empty($sDisplay) && $sDisplay == 'export'}
<script type="text/javascript">
	{literal}
	var oFeedSettingsCallBack = [{
		'name' : 'displayFeedList',
		'url' : '{/literal}{$sURI}{literal}',
		'params' : '{/literal}{$sCtrlParamName|escape:'htmlall':'UTF-8'}{literal}={/literal}{$sController|escape:'htmlall':'UTF-8'}{literal}&sAction={/literal}{$aQueryParams.feedList.action|escape:'htmlall':'UTF-8'}{literal}&sType={/literal}{$aQueryParams.feedList.type|escape:'htmlall':'UTF-8'}{literal}',
		'toShow' : 'bt_feed-list-settings',
		'toHide' : 'bt_feed-list-settings',
		'bFancybox' : false,
		'bFancyboxActivity' : false,
		'sLoadbar' : null,
		'sScrollTo' : null,
		'oCallBack' : {}
	}];
	{/literal}
</script>
{/if}

<div class="bootstrap">
	<form class="form-horizontal col-lg-{if empty($bCompare15)}12{else}10{/if}" action="{$sURI|escape:'htmlall':'UTF-8'}" method="post" id="bt_feed-{$sDisplay|escape:'htmlall':'UTF-8'}-form" name="bt_feed-{$sDisplay|escape:'htmlall':'UTF-8'}-form" {if $smarty.const._GMC_USE_JS == true}onsubmit="javascript: oGmc.form('bt_feed-{$sDisplay|escape:'htmlall':'UTF-8'}-form', '{$sURI|escape:'htmlall':'UTF-8'}', null, 'bt_feed-settings-{$sDisplay|escape:'htmlall':'UTF-8'}', 'bt_feed-settings-{$sDisplay|escape:'htmlall':'UTF-8'}', false, false, {if empty($sDisplay) || (!empty($sDisplay) && $sDisplay == 'export')}oFeedSettingsCallBack{else}null{/if}, 'Feed{$sDisplay|escape:'htmlall':'UTF-8'}', 'loadingFeedDiv');return false;"{/if}>
		<input type="hidden" name="sAction" value="{$aQueryParams.feed.action|escape:'htmlall':'UTF-8'}" />
		<input type="hidden" name="sType" value="{$aQueryParams.feed.type|escape:'htmlall':'UTF-8'}" />
		<input type="hidden" name="sDisplay" id="sDisplay" value="{if !empty($sDisplay)}{$sDisplay|escape:'htmlall':'UTF-8'}{else}export{/if}" />

		<div class="clr_20"></div>

		{* USE CASE - Export *}
		{if !empty($sDisplay) && $sDisplay == 'export'}
			<h3>{l s='Export method' mod='gmerchantcenter'}</h3>
			<div class="clr_hr"></div>
			<div class="clr_20"></div>

			<div class="alert alert-info" id="info_export">
				{l s='Select one export method by categories OR by brands' mod='gmerchantcenter'}
			</div>
			<div {if !empty($bExportMode)}style="display: none;"{/if}>
				{if $iMaxPostVars != false && $iShopCatCount > $iMaxPostVars}
					<div class="alert alert-warning">
						{l s='IMPORTANT NOTE: Be careful, apparently your maximum post variables is limited by your server and your number of categories is higher than your max post variables' mod='gmerchantcenter'} :<br/>
						<strong>{$iShopCatCount|intval}</strong>&nbsp;{l s='categories' mod='gmerchantcenter'}</strong>&nbsp;{l s='on' mod='gmerchantcenter'}&nbsp;<strong>{$iMaxPostVars|intval}</strong>&nbsp;{l s='max post variables possible (PHP directive => max_input_vars)' mod='gmerchantcenter'}<br/><br/>
						<strong>{l s='IT IS POSSIBLE YOU CANNOT REGISTER PROPERLY YOUR ALL CATEGORIES, PLEASE VISIT OUR FAQ ON THIS TOPIC' mod='gmerchantcenter'}</strong>: <a target="_blank" href="{$smarty.const._GMC_BT_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}faq.php?lg={$sCurrentIso|escape:'htmlall':'UTF-8'}&id=59">{$smarty.const._GMC_BT_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}</a>
					</div>
				{/if}
			</div>

			<div class="form-group" id="optionplus">
				<label class="control-label col-xs-2 col-md-3 col-lg-3">
					<span class="label-tooltip" title="{l s='Select your export method' mod='gmerchantcenter'}"><b>{l s='Select your export method' mod='gmerchantcenter'}</b></span> :
				</label>
				<div class="col-xs-3 col-md-3 col-lg-2">
					<select name="bt_export" id="bt_export">
						<option value="0" {if empty($bExportMode)}selected="selected"{/if}>{l s='Export by categories' mod='gmerchantcenter'}</option>
						<option value="1" {if !empty($bExportMode)}selected="selected"{/if}>{l s='Export by brands' mod='gmerchantcenter'}</option>
					</select>
				</div>
				<span class="icon-question-sign label-tooltip" title="{l s='Select your export method' mod='gmerchantcenter'}"></span>
			</div>
			{* categories tree *}
			<div id="bt_categories" {if !empty($bExportMode)}style="display: none;"{/if}>
				<div class="form-group">
					<label class="control-label col-xs-2 col-md-3 col-lg-3"></label>
					<div class="col-xs-5 col-md-5 col-lg-4">
						<div class="btn-actions">
							<div class="btn btn-default btn-mini" id="categoryCheck" onclick="return oGmc.selectAll('input.categoryBox', 'check');"><span class="icon-plus-square"></span>&nbsp;{l s='Check All' mod='gmerchantcenter'}</div> - <div class="btn btn-default btn-mini" id="categoryUnCheck" onclick="return oGmc.selectAll('input.categoryBox', 'uncheck');"><span class="icon-minus-square"></span>&nbsp;{l s='Uncheck All' mod='gmerchantcenter'}</div>
							<div class="clr_10"></div>
						</div>
						<table cellspacing="0" cellpadding="0" class="table  table-bordered table-striped" style="width: 100%;">
							{foreach from=$aFormatCat name=category key=iKey item=aCat}
								<tr class="alt_row">
									<td>
										{$aCat.id_category|intval}
									</td>
									<td>
										<input type="checkbox" name="bt_category-box[]" class="categoryBox" id="bt_category-box_{$aCat.iNewLevel|intval}" value="{$aCat.id_category|intval}" {if !empty($aCat.bCurrent)}checked="checked"{/if} />
									</td>
									<td>
										<span class="icon icon-folder{if !empty($aCat.bCurrent)}-open{/if}" style="margin-left: {$aCat.iNewLevel|intval}5px;"></span>&nbsp;&nbsp;<span style="font-size:12px;">{$aCat.name|escape:'htmlall':'UTF-8'}</span>
									</td>
								</tr>
							{/foreach}
						</table><br /><br />
					</div>
				</div>
			</div>

			{* brands tree *}
			<div id="bt_brands" {if empty($bExportMode)}style="display: none;"{/if}>
				<div class="form-group">
					<label class="control-label col-xs-2 col-md-3 col-lg-3">
						<span class="label-tooltip" title="{l s='Use this option to export your catalog by brands' mod='gmerchantcenter'}"><b>{l s='Brands' mod='gmerchantcenter'}</b></span> :
					</label>
					<div class="col-xs-5 col-md-5 col-lg-4">
						<div class="btn-actions">
							<div class="btn btn-default btn-mini" id="brandCheck" onclick="return oGmc.selectAll('input.brandBox', 'check');"><i class="icon-plus-square"></i>&nbsp;{l s='Check All' mod='gmerchantcenter'}</div> - <div class="btn btn-default btn-mini" id="brandUnCheck" onclick="return oGmc.selectAll('input.brandBox', 'uncheck');"><i class="icon-minus-square"></i>&nbsp;{l s='Uncheck All' mod='gmerchantcenter'}</div>
							<div class="clr_10"></div>
						</div>
						<table cellspacing="0" cellpadding="0" class="table  table-bordered table-striped" style="width: 100%;">
							{foreach from=$aFormatBrands name=brand key=iKey item=aBrand}
								<tr class="alt_row">
									<td>
										{$aBrand.id|intval}
									</td>
									<td>
										<input type="checkbox" name="bt_brand-box[]" class="brandBox" id="bt_brand-box_{$aBrand.id|intval}" value="{$aBrand.id|intval}" {if !empty($aBrand.checked)}checked="checked"{/if} />
									</td>
									<td>
										<i class="icon icon-folder{if !empty($aBrand.checked)}-open{/if}">&nbsp;&nbsp;<span style="font-size:12px;"></i><span>{$aBrand.name|escape:'htmlall':'UTF-8'}</span>
									</td>
								</tr>
							{/foreach}
						</table><br /><br />
					</div>
				</div>
			</div>
		{/if}
		{* END - Export *}

		{* USE CASE - Exclusion *}
		{if !empty($sDisplay) && $sDisplay == 'exclusion'}
			<h3>{l s='Product exclusion rules' mod='gmerchantcenter'}</h3>

			<div class="clr_hr"></div>
			<div class="clr_20"></div>

			<div class="form-group">
				<label class="control-label col-xs-2 col-md-3 col-lg-3"><span class="label-tooltip" data-toggle="tooltip" title data-original-title="{l s='If you select yes, all your active products will be exported. If you select no, only products that you have in stock will be exported.' mod='gmerchantcenter'}"><b>{l s=' Export out of stock products ?' mod='gmerchantcenter'}</b></span> :</label>
				<div class="col-xs-5 col-md-5 col-lg-6">
					<span class="switch prestashop-switch fixed-width-lg">
						<input type="radio" name="bt_export-oos" id="bt_export-oos_on" value="1" {if !empty($bExportOOS)}checked="checked"{/if} />
						<label for="bt_export-oos_on" class="radioCheck">
							{l s='Yes' mod='gmerchantcenter'}
						</label>
						<input type="radio" name="bt_export-oos" id="bt_export-oos_off" value="0" {if empty($bExportOOS)}checked="checked"{/if} />
						<label for="bt_export-oos_off" class="radioCheck">
							{l s='No' mod='gmerchantcenter'}
						</label>
						<a class="slide-button btn"></a>
					</span>
					<span class="label-tooltip" data-toggle="tooltip" title data-original-title="{l s='If you select yes, all your active products will be exported. If you select no, only products that you have in stock will be exported.' mod='gmerchantcenter'}">&nbsp;<span class="icon-question-sign"></span></span>
					<a class="badge badge-info" href="{$smarty.const._GMC_BT_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}faq.php?id=22&lg={$sCurrentLang|escape:'htmlall':'UTF-8'}" target="_blank"><i class="icon icon-link"></i>&nbsp;{l s='FAQ' mod='gmerchantcenter'}</a>
				</div>
			</div>

			<div class="clr_10"></div>

			<div class="form-group">
				<label class="control-label col-xs-2 col-md-3 col-lg-3"><span class="label-tooltip" data-toggle="tooltip" title data-original-title="{l s='If Google is giving you many errors to missing EAN or UPC codes, you may activate this option and none of the products without EAN13 OR UPC will be exported. This will get rid of the Google errors until you are able to get all your product codes from suppliers.' mod='gmerchantcenter'}"><b>{l s='Do not export products without EAN13 or UPC ?' mod='gmerchantcenter'}</b></span> :</label>
				<div class="col-xs-5 col-md-5 col-lg-6">
					<span class="switch prestashop-switch fixed-width-lg">
						<input type="radio" name="bt_excl-no-ean" id="bt_excl-no-ean_on" value="1" {if !empty($bExcludeNoEan)}checked="checked"{/if} />
						<label for="bt_excl-no-ean_on" class="radioCheck">
							{l s='Yes' mod='gmerchantcenter'}
						</label>
						<input type="radio" name="bt_excl-no-ean" id="bt_excl-no-ean_off" value="0" {if empty($bExcludeNoEan)}checked="checked"{/if} />
						<label for="bt_excl-no-ean_off" class="radioCheck">
							{l s='No' mod='gmerchantcenter'}
						</label>
						<a class="slide-button btn"></a>
					</span>
					<span class="label-tooltip" data-toggle="tooltip" title data-original-title="{l s='If Google is giving you many errors to missing EAN or UPC codes, you may activate this option and none of the products without EAN13 OR UPC will be exported. This will get rid of the Google errors until you are able to get all your product codes from suppliers.' mod='gmerchantcenter'}">&nbsp;<span class="icon-question-sign"></span></span>
					<a class="badge badge-info" href="{$smarty.const._GMC_BT_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}faq.php?id=22&lg={$sCurrentLang|escape:'htmlall':'UTF-8'}" target="_blank"><i class="icon icon-link"></i>&nbsp;{l s='FAQ' mod='gmerchantcenter'}</a>
				</div>
			</div>

			<div class="clr_5"></div>

			<div class="form-group">
				<label class="control-label col-xs-2 col-md-3 col-lg-3"><span class="label-tooltip" data-toggle="tooltip" title data-original-title="{l s='If Google is giving you many errors to missing MPN codes, you may activate this option and none of the products without a manufacturer reference will be exported. This will get rid of the Google errors until you are able to get all your product codes from suppliers.' mod='gmerchantcenter'}"><b>{l s='Do not export products without a manufacturer reference ?' mod='gmerchantcenter'}</b></span> :</label>
				<div class="col-xs-5 col-md-5 col-lg-6">
					<span class="switch prestashop-switch fixed-width-lg">
						<input type="radio" name="bt_excl-no-mref" id="bt_excl-no-mref_on" value="1" {if !empty($bExcludeNoMref)}checked="checked"{/if} />
						<label for="bt_excl-no-mref_on" class="radioCheck">
							{l s='Yes' mod='gmerchantcenter'}
						</label>
						<input type="radio" name="bt_excl-no-mref" id="bt_excl-no-mref_off" value="0" {if empty($bExcludeNoMref)}checked="checked"{/if} />
						<label for="bt_excl-no-mref_off" class="radioCheck">
							{l s='No' mod='gmerchantcenter'}
						</label>
						<a class="slide-button btn"></a>
					</span>
					<span class="label-tooltip" data-toggle="tooltip" title data-original-title="{l s='If Google is giving you many errors to missing MPN codes, you may activate this option and none of the products without a manufacturer reference will be exported. This will get rid of the Google errors until you are able to get all your product codes from suppliers.' mod='gmerchantcenter'}">&nbsp;<span class="icon-question-sign"></span></span>
					<a class="badge badge-info" href="{$smarty.const._GMC_BT_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}faq.php?id=22&lg={$sCurrentLang|escape:'htmlall':'UTF-8'}" target="_blank"><i class="icon icon-link"></i>&nbsp;{l s='FAQ' mod='gmerchantcenter'}</a>
				</div>
			</div>

			<div class="form-group">
				<label class="control-label col-xs-2 col-md-3 col-lg-3">
					<span class="label-tooltip" title="{l s='Leave this value at 0 to disable this feature. Otherwise, any product whose CURRENT PRICE (taking specific prices / promotions into account) is lower than this value will be excluded from the feed. Cart rules on version 1.5 are not taken into account, only specific prices. This allows you to exclude low margin products and not pay for clicks on them, making your Product Listing Ad campaigns on Google more efficient and profitable.' mod='gmerchantcenter'}"><b>{l s='Do not export products with a price lower than' mod='gmerchantcenter'}</b></span> :
				</label>
				<div class="col-xs-1 col-md-1 col-lg-1">
					<input type="text" size="5" name="bt_min-price" value="{if !empty($iMinPrice)}{$iMinPrice|floatval}{/if}" />
				</div>
				{$sDefaultCurrency|escape:'htmlall':'UTF-8'} {l s='Tax excluded' mod='gmerchantcenter'}
				&nbsp;&nbsp;
				<span class="icon-question-sign label-tooltip" title="{l s='Leave this value at 0 to disable this feature. Otherwise, any product whose CURRENT PRICE (taking specific prices / promotions into account) is lower than this value will be excluded from the feed. Cart rules on version 1.5 are not taken into account, only specific prices. This allows you to exclude low margin products and not pay for clicks on them, making your Product Listing Ad campaigns on Google more efficient and profitable.' mod='gmerchantcenter'}">&nbsp;</span>
			</div>

			<div class="clr_5"></div>

			<div class="form-group">
				<label class="control-label col-xs-2 col-md-3 col-lg-3">
					<span class="label-tooltip" title="{l s='Start to type a product name and get an autocomplete list of products' mod='gmerchantcenter'}"><b>{l s='Define your excluded products list' mod='gmerchantcenter'}</b></span> :
				</label>
				<div class="col-xs-3 col-md-3 col-lg-2">
					<input type="text" size="5" id="bt_search-p" name="bt_search-p" value="" />
				</div>
				&nbsp;&nbsp;
				<span class="icon-question-sign label-tooltip" title="{l s='Start to type a product name and get an autocomplete list of products' mod='gmerchantcenter'}">&nbsp;</span>
			</div>

			<input type="hidden" value="{if !empty($sProductIds)}{$sProductIds|escape:'htmlall':'UTF-8'}{else}{/if}" id="hiddenProductIds" name="hiddenProductIds" />
			<input type="hidden" value="{if !empty($sProductNames)}{$sProductNames|escape:'UTF-8'}{/if}" id="hiddenProductNames" name="hiddenProductNames" />


			<h3>{l s='Your excluded products list' mod='gmerchantcenter'}:</h3>

			<div class="clr_hr"></div>
			<div class="clr_10"></div>

			<div class="col-xs-6 col-md-5 col-lg-4">
				<table id="bt_product-list" border="0" cellpadding="2" cellspacing="2" class="table table-striped">
					<thead>
					<tr>
						<th>{l s='Products' mod='gmerchantcenter'}</th>
						<th>{l s='Actions' mod='gmerchantcenter'}</th>
					</tr>
					</thead>
					<tbody id="bt_excluded-products">
					{if !empty($aProducts)}
						{foreach name=product key=key item=aProduct from=$aProducts}
							<tr>
								<td>{$aProduct.id|intval}{if isset($aProduct.attrId) && $aProduct.attrId != 0} (attr: {$aProduct.attrId|intval}){/if} - {$aProduct.name|escape:'htmlall':'UTF-8'}</td>
								<td><span class="icon-trash" style="cursor:pointer;" onclick="javascript: oGmc.deleteProduct('{$aProduct.stringIds|escape:'htmlall':'UTF-8'}');"></span></td>
							</tr>
						{/foreach}
					{else}
						<tr id="bt_exclude-no-products">
							<td colspan="2">{l s='No products' mod='gmerchantcenter'}</td>
						</tr>
					{/if}
					</tbody>
				</table>
			</div>
		{/if}
		{* END - Exclusion *}

		{* BEGIN - Feed data option *}
		{if !empty($sDisplay) && $sDisplay == 'data'}
			<h3>{l s='Feed data option' mod='gmerchantcenter'}</h3>

			<div class="clr_hr"></div>
			<div class="clr_20"></div>

			<div class="alert alert-info">
				{l s='The more detailed information your provide to Google, the better your products will rank. Try to include as much information as possible. Please note that some fields are not appropriate for all products. See http://www.google.com/support/merchants/bin/answer.py?answer=171375 for specifications by country and details.' mod='gmerchantcenter'}
			</div>
			<div class="form-group">
				<label class="control-label col-xs-2 col-md-3 col-lg-3">
					<b>{l s='Products with combinations / attributes' mod='gmerchantcenter'}</b> :
				</label>
				<div class="col-xs-3 col-md-3 col-lg-3">
					<select name="bt_prod-combos">
						<option value="0" {if empty($bProductCombos)}selected="selected"{/if}>{l s='Export only one product' mod='gmerchantcenter'}</option>
						<option value="1" {if !empty($bProductCombos)}selected="selected"{/if}>{l s='Export several products: one product per attribute combination' mod='gmerchantcenter'}</option>
					</select>
				</div>
			</div>

			<div class="form-group">
				<label class="control-label col-xs-2 col-md-3 col-lg-3">
					<b>{l s='Product description' mod='gmerchantcenter'}</b> :
				</label>
				<div class="col-xs-3 col-md-3 col-lg-3">
					<select name="bt_prod-desc-type">
						{foreach from=$aDescriptionType name=desc key=iKey item=sType}
							<option value="{$iKey|intval}" {if $iKey == $iDescType}selected="selected"{/if}>{$sType|escape:'htmlall':'UTF-8'}</option>
						{/foreach}
					</select>
					<div class="alert-tag">Google: [description]</div>
				</div>
				<a class="badge badge-info" href="{$smarty.const._GMC_BT_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}faq.php?id=100&lg={$sCurrentLang|escape:'htmlall':'UTF-8'}#prod_description" target="_blank"><i class="icon icon-link"></i>&nbsp;{l s='FAQ' mod='gmerchantcenter'}</a>
			</div>

			<div class="form-group">
				<label class="control-label col-xs-2 col-md-3 col-lg-3">
					<b>{l s='Product availability (mandatory on Google)' mod='gmerchantcenter'}</b> :
				</label>
				<div class="col-xs-3 col-md-3 col-lg-3">
					<select name="bt_incl-stock">
						<option value="1" {if $iIncludeStock == 1}selected="selected"{/if}>{l s='Only indicate product as available if it is in stock' mod='gmerchantcenter'}</option>
						<option value="2" {if $iIncludeStock == 2}selected="selected"{/if}>{l s='Always indicate products as available even when product quantity is 0' mod='gmerchantcenter'}</option>
					</select>
					<div class="alert-tag">Google: [g:availability]</div>
				</div>
				<a class="badge badge-info" href="{$smarty.const._GMC_BT_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}faq.php?id=100&lg={$sCurrentLang|escape:'htmlall':'UTF-8'}#prod_availability" target="_blank"><i class="icon icon-link"></i>&nbsp;{l s='FAQ' mod='gmerchantcenter'}</a>
			</div>

			<div class="form-group">
				<label class="control-label col-xs-2 col-md-3 col-lg-3">
					<span class="label-tooltip" data-toggle="tooltip" title data-original-title="{l s='When using EAN-13 / JAN or UPC to determine the final GTIN code that will be sent to Google, you can decide to let the module check the EAN or the UPC first. For example, if your shop uses mostly EAN-13, but also have UPC for some products, then you\'ll want to set it to EAN (and this way we will check EAN first and use that if available, and if empty then check and use UPC if available).' mod='gmerchantcenter'}"><b>{l s='GTIN determination (EAN/JAN or UPC) priority' mod='gmerchantcenter'}</b></span> :
				</label>
				<div class="col-xs-3 col-md-3 col-lg-3">
					<select name="bt_gtin-pref">
						<option value="ean" {if $sGtinPreference == 'ean'}selected="selected"{/if}>{l s='Check EAN-13/JAN code first' mod='gmerchantcenter'}</option>
						<option value="upc" {if $sGtinPreference == 'upc'}selected="selected"{/if}>{l s='Check UPC code first' mod='gmerchantcenter'}</option>
					</select>
				</div>
				<div>
					<span class="label-tooltip" data-toggle="tooltip" title data-original-title="{l s='When using EAN-13 / JAN or UPC to determine the final GTIN code that will be sent to Google, you can decide to let the module check the EAN or the UPC first. For example, if your shop uses mostly EAN-13, but also have UPC for some products, then you\'ll want to set it to EAN (and this way we will check EAN first and use that if available, and if empty then check and use UPC if available).' mod='gmerchantcenter'}">&nbsp;<span class="icon-question-sign"></span></span>
					<a class="badge badge-info" href="{$smarty.const._GMC_BT_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}faq.php?id=100&lg={$sCurrentLang|escape:'htmlall':'UTF-8'}#prod_gtin" target="_blank"><i class="icon icon-link"></i>&nbsp;{l s='FAQ' mod='gmerchantcenter'}</a>
				</div>
			</div>

			<div class="form-group">
				<label class="control-label col-xs-2 col-md-3 col-lg-3"><span class="label-tooltip" data-toggle="tooltip" title data-original-title="{l s='Use this tag for adult product example : tabacco' mod='gmerchantcenter'}"><b>{l s='Include tag adult ?' mod='gmerchantcenter'}</b></span> :</label>
				<div class="col-xs-5 col-md-5 col-lg-6">
					<span class="switch prestashop-switch fixed-width-lg">
						<input type="radio" name="bt_incl-tag-adult" id="bt_incl-tag-adult_on" value="1" {if !empty($bIncludeTagAdult)}checked="checked"{/if} onclick="javascript: oGmc.changeSelect('tag_adult_link', 'tag_adult_link', null, null, true, true);" />
						<label for="bt_incl-tag-adult_on" class="radioCheck">
							{l s='Yes' mod='gmerchantcenter'}
						</label>
						<input type="radio" name="bt_incl-tag-adult" id="bt_incl-tag-adult_off" value="0" {if empty($bIncludeTagAdult)}checked="checked"{/if} onclick="javascript: oGmc.changeSelect('tag_adult_link', 'tag_adult_link', null, null, true, false);" />
						<label for="bt_incl-tag-adult_off" class="radioCheck">
							{l s='No' mod='gmerchantcenter'}
						</label>
						<a class="slide-button btn"></a>
					</span>
					<span class="label-tooltip" data-toggle="tooltip" title data-original-title="{l s='Use this tag for adult product example : tabacco' mod='gmerchantcenter'}">&nbsp;<span class="icon-question-sign"></span></span>
					<a class="badge badge-info" href="{$smarty.const._GMC_BT_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}faq.php?id=100&lg={$sCurrentLang|escape:'htmlall':'UTF-8'}#age_group" target="_blank"><i class="icon icon-link"></i>&nbsp;{l s='FAQ' mod='gmerchantcenter'}</a>
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-xs-2 col-md-3 col-lg-3"></label>
				<div class="col-xs-5 col-md-4 col-lg-3">
					<div class="alert-tag">Google: [g:adult]</div>
				</div>
			</div>


			<div class="form-group" id="tag_adult_link" {if empty($bIncludeTagAdult)}style="display: none;"{/if}>
				<label class="control-label col-xs-2 col-md-3 col-lg-3"></label>
				<div class="col-xs-5 col-md-4 col-lg-3">
					<div class="clr_15"></div>
					{if !empty($bIncludeTagAdult)}
						<span class="alert alert-success">
							<a id="handleTagAdult" class="fancybox.ajax" href="{$sURI|escape:'htmlall':'UTF-8'}&{$sCtrlParamName|escape:'htmlall':'UTF-8'}={$sController|escape:'htmlall':'UTF-8'}&sAction={$aQueryParams.tag.action|escape:'htmlall':'UTF-8'}&sType={$aQueryParams.tag.type|escape:'htmlall':'UTF-8'}&sUseTag=adult">{l s='Click here to configure the Tag for each category' mod='gmerchantcenter'}</a>
						</span>
					{else}
						<span class="alert alert-danger">{l s='Save your configuration before configure the TAG' mod='gmerchantcenter'}</span>
					{/if}
					<div class="clr_15"></div>
				</div>
			</div>

			<div class="form-group">
				<label class="control-label col-xs-2 col-md-3 col-lg-3"><span class="label-tooltip" data-toggle="tooltip" title data-original-title="{l s='This is recommended for clothing. If applicable, select one or more attribute group(s).' mod='gmerchantcenter'} {l s='Note: a maximum of 30 items will be sent to Google, as Google does not accept more.' mod='gmerchantcenter'}"><b>{l s='Include available product sizes?' mod='gmerchantcenter'}</b></span> :</label>
				<div class="col-xs-5 col-md-5 col-lg-6">
					<span class="switch prestashop-switch fixed-width-lg">
						<input type="radio" name="bt_incl-size" id="bt_incl-size_on" value="1" {if !empty($bIncludeSize)}checked="checked"{/if} onclick="javascript: oGmc.changeSelect('size_opt', 'size_opt', null, null, true, true);" />
						<label for="bt_incl-size_on" class="radioCheck">
							{l s='Yes' mod='gmerchantcenter'}
						</label>
						<input type="radio" name="bt_incl-size" id="bt_incl-size_off" value="0" {if empty($bIncludeSize)}checked="checked"{/if} onclick="javascript: oGmc.changeSelect('size_opt', 'size_opt', null, null, true, false);" />
						<label for="bt_incl-size_off" class="radioCheck">
							{l s='No' mod='gmerchantcenter'}
						</label>
						<a class="slide-button btn"></a>
					</span>
					<span class="label-tooltip" data-toggle="tooltip" title data-original-title="{l s='This is recommended for clothing. If applicable, select one or more attribute group(s).' mod='gmerchantcenter'} {l s='Note: a maximum of 30 items will be sent to Google, as Google does not accept more.' mod='gmerchantcenter'}">&nbsp;<span class="icon-question-sign"></span></span>
					<a class="badge badge-info" href="{$smarty.const._GMC_BT_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}faq.php?id=100&lg={$sCurrentLang|escape:'htmlall':'UTF-8'}#size_color" target="_blank"><i class="icon icon-link"></i>&nbsp;{l s='FAQ' mod='gmerchantcenter'}</a>
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-xs-2 col-md-3 col-lg-3"></label>
				<div class="col-xs-5 col-md-4 col-lg-3">
					<div class="alert-tag">Google: [g:size]</div>
				</div>
			</div>

			<div class="form-group" id="size_opt" {if empty($bIncludeSize)}style="display: none;"{/if}>
				<label class="control-label col-xs-2 col-md-3 col-lg-3"></label>
				<div class="col-xs-5 col-md-4 col-lg-3">
					<select name="bt_size-opt[]" multiple="multiple" size="8">
						<option value="0">--</option>
						{foreach from=$aAttributeGroups name=attribute key=iKey item=aGroup}
							<option value="{$aGroup.id_attribute_group|intval}" {if !empty($aSizeOptions) && in_array($aGroup.id_attribute_group, $aSizeOptions)}selected="selected"{/if}>{$aGroup.name|escape:'htmlall':'UTF-8'}</option>
						{/foreach}
					</select>
				</div>
			</div>

			<div class="form-group">
				<label class="control-label col-xs-2 col-md-3 col-lg-3">
					<span class="label-tooltip" title="{l s='This is recommended for clothing. If applicable, select one or more attribute group(s).' mod='gmerchantcenter'} {l s='Note: a maximum of 30 items will be sent to Google, as Google does not accept more.' mod='gmerchantcenter'}"><b>{l s='Include available product colors?' mod='gmerchantcenter'}</b></span> :
				</label>
				<div class="col-xs-5 col-md-4 col-lg-3">
					<select name="bt_incl-color" id="inc_color">
						<option value="" {if $sIncludeColor == ''}selected="selected"{/if}>--</option>
						<option value="attribute" {if $sIncludeColor == 'attribute'}selected="selected"{/if}>{l s='Use attributes' mod='gmerchantcenter'}</option>
						<option value="feature" {if $sIncludeColor == 'feature'}selected="selected"{/if}>{l s='Use features' mod='gmerchantcenter'}</option>
						<option value="both" {if $sIncludeColor == 'both'}selected="selected"{/if}>{l s='Use both' mod='gmerchantcenter'}</option>
					</select>
				</div>
				<div>
					<span class="icon-question-sign label-tooltip" title="{l s='This is recommended for clothing. If applicable, select one or more attribute group(s).' mod='gmerchantcenter'} {l s='Note: a maximum of 30 items will be sent to Google, as Google does not accept more.' mod='gmerchantcenter'}"></span>
					<a class="badge badge-info" href="{$smarty.const._GMC_BT_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}faq.php?id=100&lg={$sCurrentLang|escape:'htmlall':'UTF-8'}#size_color" target="_blank"><i class="icon icon-link"></i>&nbsp;{l s='FAQ' mod='gmerchantcenter'}</a>
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-xs-2 col-md-3 col-lg-3"></label>
				<div class="col-xs-5 col-md-4 col-lg-3">
					<div class="alert-tag">Google: [g:color]</div>
				</div>
			</div>

			<div class="form-group" id="div_color_opt_attr" style="display: none;">
				<label class="control-label col-xs-2 col-md-3 col-lg-3"></label>
				<div class="col-xs-5 col-md-4 col-lg-3">
					<select name="bt_color-opt[attribute][]" multiple="multiple" size="8" id="color_opt_attr">
						<option value="" disabled="disabled" style="color: #aaa;font-weight: bold;">{l s='Attributes (multiple choice)' mod='gmerchantcenter'}</option>
						{foreach from=$aAttributeGroups name=attribute key=iKey item=aGroup}
							<option value="{$aGroup.id_attribute_group|intval}" {if !empty($aColorOptions.attribute) && is_array($aColorOptions.attribute) && in_array($aGroup.id_attribute_group, $aColorOptions.attribute)}selected="selected"{/if} style="padding-left: 10px;font-weight: bold;">{$aGroup.name|escape:'htmlall':'UTF-8'}</option>
						{/foreach}
					</select>
				</div>
			</div>

			<div class="form-group" id="div_color_opt_feat" style="display: none;">
				<label class="control-label col-xs-2 col-md-3 col-lg-3"></label>
				<div class="col-xs-5 col-md-4 col-lg-3">
					<select name="bt_color-opt[feature][]" size="8" id="color_opt_feat">
						<option value="" disabled="disabled" style="color: #aaa;font-weight: bold;">{l s='Features (one choice)' mod='gmerchantcenter'}</option>
						{foreach from=$aFeatures name=feature key=iKey item=aFeature}
							<option value="{$aFeature.id_feature|intval}" {if !empty($aColorOptions.feature) && is_array($aColorOptions.feature) && in_array($aFeature.id_feature, $aColorOptions.feature)}selected="selected"{/if} style="padding-left: 10px;font-weight: bold;">{$aFeature.name|escape:'htmlall':'UTF-8'}</option>
						{/foreach}
					</select>
				</div>
			</div>

		{/if}
		{* END - Feed data option *}

		{* BEGIN - Apparel *}
		{if !empty($sDisplay) && $sDisplay == 'apparel'}
			<h3>{l s='Apparel feed options' mod='gmerchantcenter'}</h3>

			<div class="clr_hr"></div>
			<div class="clr_20"></div>

			<div class="alert alert-info">
				<p>{l s='If available, clothing and apparel stores should try to include these options.' mod='gmerchantcenter'}</p>
				<p>{l s='For material and pattern you can set these value in "Feature" management from Prestashop ' mod='gmerchantcenter'}</p>
				<p>{l s='For Gender and Age Group you can select the value directly on the dropdown menu' mod='gmerchantcenter'}</p>
			</div>

			<div class="form-group">
				<label class="control-label col-xs-2 col-md-3 col-lg-3"><span class="label-tooltip" data-toggle="tooltip" title data-original-title="{l s='If available, you should indicate this for all apparel products' mod='gmerchantcenter'}"><b>{l s='Include product material?' mod='gmerchantcenter'}</b></span> :</label>
				<div class="col-xs-5 col-md-5 col-lg-6">
					<span class="switch prestashop-switch fixed-width-lg">
						<input type="radio" name="bt_incl-material" id="bt_incl-material_on" value="1" {if !empty($bIncludeMaterial)}checked="checked"{/if} onclick="javascript: oGmc.changeSelect('material_link', 'material_link', null, null, true, true);"/>
						<label for="bt_incl-material_on" class="radioCheck">
							{l s='Yes' mod='gmerchantcenter'}
						</label>
						<input type="radio" name="bt_incl-material" id="bt_incl-material_off" value="0" {if empty($bIncludeMaterial)}checked="checked"{/if} onclick="javascript: oGmc.changeSelect('material_link', 'material_link', null, null, true, false);" />
						<label for="bt_incl-material_off" class="radioCheck">
							{l s='No' mod='gmerchantcenter'}
						</label>
						<a class="slide-button btn"></a>
					</span>
					<span class="label-tooltip" data-toggle="tooltip" title data-original-title="{l s='If available, you should indicate this for all apparel products' mod='gmerchantcenter'}">&nbsp;<span class="icon-question-sign"></span></span>
					<a class="badge badge-info" href="{$smarty.const._GMC_BT_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}faq.php?id=100&lg={$sCurrentLang|escape:'htmlall':'UTF-8'}#material" target="_blank"><i class="icon icon-link"></i>&nbsp;{l s='FAQ' mod='gmerchantcenter'}</a>
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-xs-2 col-md-3 col-lg-3"></label>
				<div class="col-xs-5 col-md-4 col-lg-3">
					<div class="alert-tag">Google: [g:material]</div>
				</div>
			</div>

			<div class="form-group" id="material_link" {if empty($bIncludeMaterial)}style="display: none;"{/if}>
				<label class="control-label col-xs-2 col-md-3 col-lg-3"></label>
				<div class="col-xs-6 col-md-5 col-lg-4">
					<div class="clr_15"></div>
					{if !empty($bIncludeMaterial)}
						<span class="alert alert-success">
							<a id="handleTagMaterial" class="fancybox.ajax" href="{$sURI|escape:'htmlall':'UTF-8'}&{$sCtrlParamName|escape:'htmlall':'UTF-8'}={$sController|escape:'htmlall':'UTF-8'}&sAction={$aQueryParams.tag.action|escape:'htmlall':'UTF-8'}&sType={$aQueryParams.tag.type|escape:'htmlall':'UTF-8'}&sUseTag=material">{l s='Click here to configure the Tag for each category' mod='gmerchantcenter'}</a>
						</span>
					{else}
						<span class="alert alert-danger" id="save_require">{l s='Save your configuration before configure the TAG' mod='gmerchantcenter'}</span>
					{/if}
					<div class="clr_15"></div>
				</div>
			</div>

			<div class="form-group">
				<label class="control-label col-xs-2 col-md-3 col-lg-3"><span class="label-tooltip" data-toggle="tooltip" title data-original-title="{l s='If available, you should indicate this for all apparel products' mod='gmerchantcenter'}"><b>{l s='Include product pattern?' mod='gmerchantcenter'}</b></span> :</label>
				<div class="col-xs-5 col-md-5 col-lg-6">
					<span class="switch prestashop-switch fixed-width-lg">
						<input type="radio" name="bt_incl-pattern" id="bt_incl-pattern_on" value="1" {if !empty($bIncludePattern)}checked="checked"{/if} onclick="javascript: oGmc.changeSelect('pattern_link', 'pattern_link', null, null, true, true);"/>
						<label for="bt_incl-pattern_on" class="radioCheck">
							{l s='Yes' mod='gmerchantcenter'}
						</label>
						<input type="radio" name="bt_incl-pattern" id="bt_incl-pattern_off" value="0" {if empty($bIncludePattern)}checked="checked"{/if} onclick="javascript: oGmc.changeSelect('pattern_link', 'pattern_link', null, null, true, false);" />
						<label for="bt_incl-pattern_off" class="radioCheck">
							{l s='No' mod='gmerchantcenter'}
						</label>
						<a class="slide-button btn"></a>
					</span>
					<span class="label-tooltip" data-toggle="tooltip" title data-original-title="{l s='If available, you should indicate this for all apparel products' mod='gmerchantcenter'}">&nbsp;<span class="icon-question-sign"></span></span>
					<a class="badge badge-info" href="{$smarty.const._GMC_BT_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}faq.php?id=100&lg={$sCurrentLang|escape:'htmlall':'UTF-8'}#pattern" target="_blank"><i class="icon icon-link"></i>&nbsp;{l s='FAQ' mod='gmerchantcenter'}</a>
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-xs-2 col-md-3 col-lg-3"></label>
				<div class="col-xs-5 col-md-4 col-lg-3">
					<div class="alert-tag">Google: [g:pattern]</div>
				</div>
			</div>

			<div class="form-group" id="pattern_link" {if empty($bIncludePattern)}style="display: none;"{/if}>
				<label class="control-label col-xs-2 col-md-3 col-lg-3"></label>
				<div class="col-xs-6 col-md-5 col-lg-4">
					<div class="clr_15"></div>
					{if !empty($bIncludePattern)}
						<span class="alert alert-success">
								<a id="handleTagPattern" class="fancybox.ajax" href="{$sURI|escape:'htmlall':'UTF-8'}&{$sCtrlParamName|escape:'htmlall':'UTF-8'}={$sController|escape:'htmlall':'UTF-8'}&sAction={$aQueryParams.tag.action|escape:'htmlall':'UTF-8'}&sType={$aQueryParams.tag.type|escape:'htmlall':'UTF-8'}&sUseTag=pattern">{l s='Click here to configure the Tag for each category' mod='gmerchantcenter'}</a>
							</span>
					{else}
						<span class="alert alert-danger" id="save_require">{l s='Save your configuration before configure the TAG' mod='gmerchantcenter'}</span>
					{/if}
					<div class="clr_15"></div>
				</div>
			</div>

			<div class="form-group">
				<label class="control-label col-xs-2 col-md-3 col-lg-3"><span class="label-tooltip" data-toggle="tooltip" title data-original-title="{l s='If available, you should indicate this for all apparel products' mod='gmerchantcenter'}"><b>{l s='Include product gender?' mod='gmerchantcenter'}</b></span> :</label>
				<div class="col-xs-5 col-md-5 col-lg-6">
					<span class="switch prestashop-switch fixed-width-lg">
						<input type="radio" name="bt_incl-gender" id="bt_incl-gender_on" value="1" {if !empty($bIncludeGender)}checked="checked"{/if} onclick="javascript: oGmc.changeSelect('gender_link', 'gender_link', null, null, true, true);"/>
						<label for="bt_incl-gender_on" class="radioCheck">
							{l s='Yes' mod='gmerchantcenter'}
						</label>
						<input type="radio" name="bt_incl-gender" id="bt_incl-gender_off" value="0" {if empty($bIncludeGender)}checked="checked"{/if} onclick="javascript: oGmc.changeSelect('gender_link', 'gender_link', null, null, true, false);" />
						<label for="bt_incl-gender_off" class="radioCheck">
							{l s='No' mod='gmerchantcenter'}
						</label>
						<a class="slide-button btn"></a>
					</span>
					<span class="label-tooltip" data-toggle="tooltip" title data-original-title="{l s='If available, you should indicate this for all apparel products' mod='gmerchantcenter'}">&nbsp;<span class="icon-question-sign"></span></span>
					<a class="badge badge-info" href="{$smarty.const._GMC_BT_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}faq.php?id=100&lg={$sCurrentLang|escape:'htmlall':'UTF-8'}#gender" target="_blank"><i class="icon icon-link"></i>&nbsp;{l s='FAQ' mod='gmerchantcenter'}</a>
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-xs-2 col-md-3 col-lg-3"></label>
				<div class="col-xs-5 col-md-4 col-lg-3">
					<div class="alert-tag">Google: [g:gender]</div>
				</div>
			</div>

			<div class="form-group" id="gender_link" {if empty($bIncludeGender)}style="display: none;"{/if}>
				<label class="control-label col-xs-2 col-md-3 col-lg-3"></label>
				<div class="col-xs-6 col-md-5 col-lg-4">
					<div class="clr_15"></div>
					{if !empty($bIncludeGender)}
						<span class="alert alert-success">
							<a id="handleTagGender" class="fancybox.ajax" href="{$sURI|escape:'htmlall':'UTF-8'}&{$sCtrlParamName|escape:'htmlall':'UTF-8'}={$sController|escape:'htmlall':'UTF-8'}&sAction={$aQueryParams.tag.action|escape:'htmlall':'UTF-8'}&sType={$aQueryParams.tag.type|escape:'htmlall':'UTF-8'}&sUseTag=gender">{l s='Click here to configure the Tag for each category' mod='gmerchantcenter'}</a>
						</span>
					{else}
						<span class="alert alert-danger" id="save_require">{l s='Save your configuration before configure the TAG' mod='gmerchantcenter'}</span>
					{/if}
					<div class="clr_15"></div>
				</div>
			</div>


			<div class="form-group">
				<label class="control-label col-xs-2 col-md-3 col-lg-3"><span class="label-tooltip" data-toggle="tooltip" title data-original-title="{l s='If available, you should indicate this for all apparel products' mod='gmerchantcenter'}"><b>{l s='Include product age group?' mod='gmerchantcenter'}</b></span> :</label>
				<div class="col-xs-5 col-md-5 col-lg-6">
					<span class="switch prestashop-switch fixed-width-lg">
						<input type="radio" name="bt_incl-age" id="bt_incl-age_on" value="1" {if !empty($bIncludeAge)}checked="checked"{/if} onclick="javascript: oGmc.changeSelect('age_group_link', 'age_group_link', null, null, true, true);"/>
						<label for="bt_incl-age_on" class="radioCheck">
							{l s='Yes' mod='gmerchantcenter'}
						</label>
						<input type="radio" name="bt_incl-age" id="bt_incl-age_off" value="0" {if empty($bIncludeAge)}checked="checked"{/if} onclick="javascript: oGmc.changeSelect('age_group_link', 'age_group_link', null, null, true, false);" />
						<label for="bt_incl-age_off" class="radioCheck">
							{l s='No' mod='gmerchantcenter'}
						</label>
						<a class="slide-button btn"></a>
					</span>
					<span class="label-tooltip" data-toggle="tooltip" title data-original-title="{l s='If available, you should indicate this for all apparel products' mod='gmerchantcenter'}">&nbsp;<span class="icon-question-sign"></span></span>
					<a class="badge badge-info" href="{$smarty.const._GMC_BT_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}faq.php?id=100&lg={$sCurrentLang|escape:'htmlall':'UTF-8'}#age_group" target="_blank"><i class="icon icon-link"></i>&nbsp;{l s='FAQ' mod='gmerchantcenter'}</a>
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-xs-2 col-md-3 col-lg-3"></label>
				<div class="col-xs-5 col-md-4 col-lg-3">
					<div class="alert-tag">Google: [g:age_group]</div>
				</div>
			</div>

			<div class="form-group" id="age_group_link" {if empty($bIncludeAge)}style="display: none;"{/if}>
				<label class="control-label col-xs-2 col-md-3 col-lg-3"></label>
				<div class="col-xs-6 col-md-5 col-lg-4">
					<div class="clr_15"></div>
					{if !empty($bIncludeAge)}
						<span class="alert alert-success">
							<a id="handleTagAge" class="fancybox.ajax" href="{$sURI|escape:'htmlall':'UTF-8'}&{$sCtrlParamName|escape:'htmlall':'UTF-8'}={$sController|escape:'htmlall':'UTF-8'}&sAction={$aQueryParams.tag.action|escape:'htmlall':'UTF-8'}&sType={$aQueryParams.tag.type|escape:'htmlall':'UTF-8'}&sUseTag=agegroup">{l s='Click here to configure the Tag for each category' mod='gmerchantcenter'}</a>
						</span>
					{else}
						<span class="alert alert-danger" id="save_require">{l s='Save your configuration before configure the TAG' mod='gmerchantcenter'}</span>
					{/if}
					<div class="clr_15"></div>
				</div>
			</div>
		{/if}
		{* END - Apparel *}

		{* BEGIN - Tax and shipping fees *}
		{if !empty($sDisplay) && $sDisplay == 'tax'}
			<h3>{l s='Tax and shipping fees' mod='gmerchantcenter'}</h3>

			<div class="clr_hr"></div>
			<div class="clr_20"></div>

			<div class="alert alert-info">
				{l s='TAXES: Detailed tax information is only required for feeds targeting the United States. If you do not sell in the US, products will automatically have VAT applied to them in the main product price, as required by Google. If you sell in the US and need to specify taxes by state or ZIP code, please simply define them on your Google Merchant Center account, in the "Settings" -> "Tax and shipping" tab.' mod='gmerchantcenter'}
				{l s='SHIPPING: Please select the appropriate default carrier for each country below' mod='gmerchantcenter'}:
			</div>

			<div class="clr_15"></div>

			<div class="form-group">
				<label class="control-label col-xs-2 col-md-3 col-lg-3"><b>{l s='Manage the shipping cost with the module ?' mod='gmerchantcenter'}</b> :</label>
				<div class="col-xs-5 col-md-5 col-lg-6">
					<span class="switch prestashop-switch fixed-width-lg">
						<input type="radio" name="bt_manage-shipping" id="bt_manage-shipping_on" value="1" {if !empty($bShippingUse)}checked="checked"{/if} onclick="javascript: oGmc.changeSelect('bt_conf-shipping', 'bt_conf-shipping', null, null, true, true);"/>
						<label for="bt_manage-shipping_on" class="radioCheck">
							{l s='Yes' mod='gmerchantcenter'}
						</label>
						<input type="radio" name="bt_manage-shipping" id="bt_manage-shipping_off" value="0" {if empty($bShippingUse)}checked="checked"{/if} onclick="javascript: oGmc.changeSelect('bt_conf-shipping', 'bt_conf-shipping', null, null, true, false);" />
						<label for="bt_manage-shipping_off" class="radioCheck">
							{l s='No' mod='gmerchantcenter'}
						</label>
						<a class="slide-button btn"></a>
					</span>
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-xs-2 col-md-3 col-lg-3"></label>
				<div class="col-xs-5 col-md-4 col-lg-3">
					<div class="alert-tag">Google: [g:shipping]</div>
				</div>
			</div>

			<div id="bt_conf-shipping" {if empty($bShippingUse)}style="display: none;"{/if}>
				{if !empty($aShippingCarriers)}
					{foreach from=$aShippingCarriers name=shipping key=sCountry item=aShipping}
					<div class="form-group">
						<label class="control-label col-xs-2 col-md-3 col-lg-3">
							<span title=""><b>{$sCountry|escape:'htmlall':'UTF-8'}</b></span>
						</label>
						<div class="col-xs-3 col-md-3 col-lg-2">
							<select name="bt_ship-carriers[{$sCountry|escape:'htmlall':'UTF-8'}]">
								{foreach from=$aShipping.carriers name=carrier key=iKey item=aCarrier}
									<option {if $aCarrier.id_carrier == $aShipping.shippingCarrierId}selected=selected{/if} value="{$aCarrier.id_carrier|intval}">{$aCarrier.name|escape:'htmlall':'UTF-8'}</option>
								{/foreach}
							</select>
						</div>
					</div>
					<div class="clr_15"></div>
					{/foreach}
				{else}
					<div class="alert alert-warning">
						{l s='There isn\'t any carrier available' mod='gmerchantcenter'}
					</div>
					<div class="clr_15"></div>
				{/if}
			</div>
		{/if}
		{* END - Tax and shipping fees *}

		<div class="clr_20"></div>

		<div id="{$sModuleName|escape:'htmlall':'UTF-8'}Feed{$sDisplay|escape:'htmlall':'UTF-8'}Error"></div>

		<div class="clr_5"></div>

		<div class="center">
			<button name="bt_feed-button" class="btn btn-success btn-lg" onclick="oGmc.form('bt_feed-{$sDisplay|escape:'htmlall':'UTF-8'}-form', '{$sURI|escape:'htmlall':'UTF-8'}', null, 'bt_feed-settings-{$sDisplay|escape:'htmlall':'UTF-8'}', 'bt_feed-settings-{$sDisplay|escape:'htmlall':'UTF-8'}', false, false, {if empty($sDisplay) || (!empty($sDisplay) && $sDisplay == 'export')}oFeedSettingsCallBack{else}null{/if}, 'Feed{$sDisplay|escape:'htmlall':'UTF-8'}', 'loadingFeedDiv');return false;">{l s='Update' mod='gmerchantcenter'}</button>
		</div>
	</form>
</div>
{literal}
<script type="text/javascript">
	$(document).ready(function() {
		{/literal}{if !empty($sDisplay) && $sDisplay == 'exclusion'}{literal}
		// autocomplete
		oGmc.autocomplete('{/literal}{$sURI|escape:'UTF-8'}&sAction={$aQueryParams.searchProduct.action|escape:'htmlall':'UTF-8'}&sType={$aQueryParams.searchProduct.type|escape:'htmlall':'UTF-8'}{literal}', '#bt_search-p');
		{/literal}{/if}{literal}
	});
	//bootstrap components init
	{/literal}{if !empty($bAjaxMode)}{literal}
	$('.label-tooltip, .help-tooltip').tooltip();
	oGmc.runMainFeed();
	{/literal}{/if}{literal}
</script>
{/literal}