{*
* 2003-2016 Business Tech
*
* @author Business Tech SARL <http://www.businesstech.fr/en/contact-us>
* @copyright  2003-2016 Business Tech SARL
*}
{if !empty($aErrors)}
{include file="`$sErrorInclude`"}
{* USE CASE - edition review mode *}
{else}
<div id="{$sModuleName|escape:'htmlall':'UTF-8'}" class="bootstrap" {if empty($bCompare15)}style="text-align: left !important;"{/if}>
	<div id="bt_google-category" class="col-xs-12">
		<h3>{l s='Google product categories for' mod='gmerchantcenter'}: {$sLangIso|escape:'htmlall':'UTF-8'}</h3>

		<div class="clr_hr"></div>
		<div class="clr_20"></div>

		<div class="alert alert-success">
			{l s='INSTRUCTIONS: for each category, you can do keyword search that represents the category, using as many words as you wish. Simply separate each word by a space. The field will autocomplete with possible matches that contain all the words you entered. Then simply select the best match from the list.' mod='gmerchantcenter'}
		</div>

		{if $iMaxPostVars != false && $iShopCatCount > $iMaxPostVars}
		<div class="alert alert-warning">
			{l s='IMPORTANT NOTE: Be careful, apparently your maximum post variables is limited by your server and your number of categories is higher than your max post variables' mod='gmerchantcenter'} :<br/>
			<strong>{$iShopCatCount|intval}</strong>&nbsp;{l s='categories' mod='gmerchantcenter'}</strong>&nbsp;{l s='on' mod='gmerchantcenter'}&nbsp;<strong>{$iMaxPostVars|intval}</strong>&nbsp;{l s='max post variables possible (PHP directive => max_input_vars)' mod='gmerchantcenter'}<br/><br/>
			<strong>{l s='IT IS POSSIBLE YOU CANNOT REGISTER PROPERLY YOUR ALL CATEGORIES, PLEASE VISIT OUR FAQ ON THIS TOPIC' mod='gmerchantcenter'}</strong>: <a target="_blank" href="{$smarty.const._GMC_BT_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}faq.php?lg={$sCurrentIso|escape:'htmlall':'UTF-8'}&id=59">{$smarty.const._GMC_BT_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}</a>
		</div>
		{/if}

		<div class="clr_20"></div>

		<form class="form-horizontal" method="post" id="bt_form-google-cat" name="bt_form-google-cat" {if $smarty.const._GSR_USE_JS == true}onsubmit="oGmc.form('bt_form-google-cat', '{$sURI|escape:'htmlall':'UTF-8'}', null, 'bt_google-category', 'bt_google-category', false, true, null, 'GoogleCat', 'loadingGoogleCatDiv');return false;"{/if}>
			<input type="hidden" name="{$sCtrlParamName|escape:'htmlall':'UTF-8'}" value="{$sController|escape:'htmlall':'UTF-8'}" />
			<input type="hidden" name="sAction" value="{$aQueryParams.googleCatUpdate.action|escape:'htmlall':'UTF-8'}" />
			<input type="hidden" name="sType" value="{$aQueryParams.googleCatUpdate.type|escape:'htmlall':'UTF-8'}" />
			<input type="hidden" name="sLangIso" value="{$sLangIso|escape:'htmlall':'UTF-8'}" />
			<input type="hidden" name="iLangId" value="{$iLangId|intval}" />

			{foreach from=$aShopCategories name=category item=aCategory}
			<table class="table table-bordered table-responsive">
				<tr>
					<td class="label_tag_categories">{l s='Shop category' mod='gmerchantcenter'} : {$aCategory.path|escape:'UTF-8'}</td>
				</tr>
				<tr>
					<td>
						{l s='Google category' mod='gmerchantcenter'}&nbsp;:&nbsp;<input class="autocmp" style="font-size: 11px; width: 800px;" type="text" name="bt_google-cat[{$aCategory.id_category|escape:'intval'}]" id="bt_google-cat{$aCategory.id_category|intval}" value="{$aCategory.google_category_name|escape:'UTF-8'}" />
						<p class="duplicate_category">
						{if $smarty.foreach.category.first}
							<br /><a href="#" onclick="return oGmc.duplicateFirstValue('input.autocmp', $('#bt_google-cat{$aCategory.id_category|intval}').val());">{l s='Duplicate this value on all categories below' mod='gmerchantcenter'}</a>
						{/if}
						</p>
					</td>
				</tr>
			</table>
			{/foreach}

			<div class="clr_20"></div>

			<p style="text-align: center !important;">
				{if $smarty.const._GMC_USE_JS == true}
					<script type="text/javascript">
						{literal}
						var oGoogleCatCallback = [{
							//'name' : 'moderationList',
							//'url' : '',
							//'params' : '',
							//'toShow' : '',
							//'toHide' : '',
							//'bFancybox' : false,
							//'bFancyboxActivity' : false,
							//'sLoadbar' : null,
							//'sScrollTo' : null,
							//'oCallBack' : {}
						}];
						{/literal}
					</script>
					<input type="button" name="{$sModuleName|escape:'htmlall':'UTF-8'}CommentButton" class="btn btn-success btn-lg" value="{l s='Modify' mod='gmerchantcenter'}" onclick="oGmc.form('bt_form-google-cat', '{$sURI|escape:'htmlall':'UTF-8'}', null, 'bt_google-category', 'bt_google-category', false, true, null, 'GoogleCat', 'loadingGoogleCatDiv');return false;" />
				{else}
					<input type="submit" name="{$sModuleName|escape:'htmlall':'UTF-8'}CommentButton" class="btn btn-success btn-lg" value="{l s='Modify' mod='gmerchantcenter'}" />
				{/if}
				{if !empty($bCompare149)}
					<button class="btn btn-danger btn-lg" value="{l s='Cancel' mod='gmerchantcenter'}"  onclick="$.fancybox.close();return false;">{l s='Cancel' mod='gmerchantcenter'}</button>
				{/if}
			</p>
		</form>
		{literal}
		<script type="text/javascript">
			$('input.autocmp').each(function(index, element) {
				var query = $(element).attr("id");
				$(element).autocomplete('{/literal}{$sURI|escape:'UTF-8'}&{$sCtrlParamName|escape:'htmlall':'UTF-8'}={$sController|escape:'htmlall':'UTF-8'}&sAction={$aQueryParams.autocomplete.action|escape:'htmlall':'UTF-8'}&sType={$aQueryParams.autocomplete.type|escape:'htmlall':'UTF-8'}&sLangIso={$sLangIso|escape:'htmlall':'UTF-8'}&query='+query{literal}, {
					minChars: 3,
					autoFill: false,
					max:50,
					matchContains: true,
					mustMatch:false,
					scroll:true,
					cacheLength:0,
					formatItem: function(item) {
						return item[0];
					}
				});
			});
		</script>
		{/literal}
	</div>
</div>
<div id="loadingGoogleCatDiv" style="display: none;">
	<div class="alert alert-info">
		<p style="text-align: center !important;"><img src="{$sLoadingImg|escape:'htmlall':'UTF-8'}" alt="Loading" /></p><div class="clr_20"></div>
		<p style="text-align: center !important;">{l s='Your update configuration is in progress' mod='gmerchantcenter'}</p>
	</div>
</div>
{/if}