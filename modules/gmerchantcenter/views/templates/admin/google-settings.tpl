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
<script type="text/javascript">
	{literal}
	var oGoogleSettingsCallBack = [{
		//'name' : 'updateDesc',
		//'url' : '{*/literal}{$sURI}{literal*}',
		//'params' : '{*/literal}{$sCtrlParamName|escape:'htmlall':'UTF-8'}{literal*}={*/literal}{$sController|escape:'htmlall':'UTF-8'}{literal*}&sAction=display&sType=moderation',
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

<div class="bootstrap">
	<form class="form-horizontal col-lg-{if empty($bCompare15)}12{else}10{/if}" action="{$sURI|escape:'htmlall':'UTF-8'}" method="post" id="bt_google-{$sDisplay|escape:'htmlall':'UTF-8'}-form" name="bt_google-{$sDisplay|escape:'htmlall':'UTF-8'}-form" {if $smarty.const._GMC_USE_JS == true}onsubmit="javascript: oGmc.form('bt_google-{$sDisplay|escape:'htmlall':'UTF-8'}-form', '{$sURI|escape:'htmlall':'UTF-8'}', null, 'bt_google-settings-{$sDisplay|escape:'htmlall':'UTF-8'}', 'bt_google-settings-{$sDisplay|escape:'htmlall':'UTF-8'}', false, false, oGoogleSettingsCallBack, 'Google', 'loadingGoogleDiv');return false;"{/if}>
		<input type="hidden" name="sAction" value="{$aQueryParams.google.action|escape:'htmlall':'UTF-8'}" />
		<input type="hidden" name="sType" value="{$aQueryParams.google.type|escape:'htmlall':'UTF-8'}" />
		<input type="hidden" name="sDisplay" id="sGsDisplay" value="{if !empty($sDisplay)}{$sDisplay|escape:'htmlall':'UTF-8'}{else}categories{/if}" />

		<div class="clr_20"></div>

		{* USE CASE - Google categories *}
		{if !empty($sDisplay) && $sDisplay == 'categories'}
			<h3>{l s='Google Categories' mod='gmerchantcenter'}</h3>
			<div class="clr_hr"></div>
			<div class="clr_20"></div>

			<div class="alert alert-info" id="info_export">
				{l s='Each Google country has its own taxonomy (some countries share the same one though). Therefore, you must match your categories for each country you wish to submit your feed for separately. So if you wish to submit for US and UK / GB, you must match the categories for each of those two, but can leave out any other countries where you do not want to submit your feed. Note that not all product types require a Google product category. Please visit http://www.google.com/support/merchants/bin/answer.py?answer=160081 for more information' mod='gmerchantcenter'}.
				{l s='Use the tool below to manage your Google categories. The pencil icon' mod='gmerchantcenter'}&nbsp;<span class="icon-pencil"></span>&nbsp;{l s='allows you to match your PrestaShop categories to the Google taxonomy categories. The reload icon' mod='gmerchantcenter'}&nbsp;<span class="icon-refresh">&nbsp;</span>{l s='allows you to do a real-time update of the module\'s database of Google categories. This should not be needed as Google does not update this very often, but if you notice that some categories are missing in the choices you get offered, compared to what can be found on http://www.google.com/support/merchants/bin/answer.py?answer=160081, you can go ahead and update your database' mod='gmerchantcenter'}.
			</div>

			<div class="clr_20"></div>

			<div id="bt_google-cat-list">
				{include file="`$sGoogleCatListInclude`"}
			</div>

			<div class="form-group">
				<div class="col-xs-5 col-md-4 col-lg-3">
					<div class="alert-tag">Google: [g:google_product_category]</div>
				</div>
			</div>

			<div class="clr_20"></div>
			<div id="loadingGoogleCatListDiv" style="display: none;">
				<div class="alert alert-info">
					<p style="text-align: center !important;"><img src="{$sBigLoadingImg|escape:'htmlall':'UTF-8'}" alt="Loading" /></p><div class="clr_20"></div>
					<p style="text-align: center !important;">{l s='Your update google categories is in progress' mod='gmerchantcenter'}</p>
				</div>
			</div>
		{/if}
		{* END - Google categories *}

		{* USE CASE - Google analytics *}
		{if !empty($sDisplay) && $sDisplay == 'analytics'}
			<h3>{l s='Google Analytics integration' mod='gmerchantcenter'}</h3>

			<div class="clr_hr"></div>
			<div class="clr_20"></div>

			<div class="alert alert-info" id="info_export">
				{l s='This section allow you to add some parameters in your product links (utm_campaign, utm_source and utm_medium) so that you can better track clicks and sales from your Google Adwords Product Listing Ad campaigns in in your Google Analytics account. If a parameter is left empty below, it will not be added. Please add alphanumerical characters only, without spaces. You can use - or _ signs however' mod='gmerchantcenter'}.
				{l s='Note: if you use this feature, you will want to make sure that the utm_campaign, utm_source and utm_medium parameters are not disallowed in your robots.txt file' mod='gmerchantcenter'}
			</div>

			<div class="clr_20"></div>

			<div class="form-group ">
				<label class="control-label col-lg-3">
					<span><b>{l s='Value of parameter for utm_campaign' mod='gmerchantcenter'}</b></span> :
				</label>
				<div class="col-xs-3">
					<input type="text" size="30" name="bt_utm-campaign" value="{$sUtmCampaign|escape:'htmlall':'UTF-8'}" />
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-lg-3">
					<span><b>{l s='Value of parameter for utm_source' mod='gmerchantcenter'}</b></span> :
				</label>
				<div class="col-xs-3">
					<input type="text" size="30" name="bt_utm-source" value="{$sUtmSource|escape:'htmlall':'UTF-8'}" />
				</div>
			</div>
			<div class="form-group ">
				<label class="control-label col-lg-3">
					<span><b>{l s='Value of parameter for utm_medium' mod='gmerchantcenter'}</b></span> :
				</label>
				<div class="col-xs-3">
					<input type="text" size="30" name="bt_utm-medium" value="{$sUtmMedium|escape:'htmlall':'UTF-8'}" />
				</div>
			</div>
		{/if}
		{* END - Google analytics *}

		{* USE CASE - Google custom label *}
		{if !empty($sDisplay) && $sDisplay == 'adwords'}
			<h3>{l s='Google Adwords / Custom label integration' mod='gmerchantcenter'}</h3>
			<div class="alert-tag">
				[custom_label]
			</div>
			<div class="clr_hr"></div>
			<div class="clr_20"></div>

			<div class="alert alert-info" id="info_export">
				{l s='This section allow you to associate custom labels based on categories, brands and suppliers. This is a pretty advanced feature, so we are assuming you are familiar with how this works on the Google side and we are not going to explain it here. If you have no idea what this is, you may simply ignore this section. For more information, please visit' mod='gmerchantcenter'} <a href="http://support.google.com/merchants/bin/answer.py?answer=188479" target="_blank" style="text-decoration: underline;">http://support.google.com/merchants/bin/answer.py?answer=188479</a>
			</div>

			<div class="clr_20"></div>

			<div class="add_adwords">
				<a id="handleGoogleAdwords" class="fancybox.ajax" href="{$sURI|escape:'htmlall':'UTF-8'}&{$sCtrlParamName|escape:'htmlall':'UTF-8'}={$sController|escape:'htmlall':'UTF-8':'UTF-8'}&sAction={$aQueryParams.custom.action|escape:'htmlall':'UTF-8'}&sType={$aQueryParams.custom.type|escape:'htmlall':'UTF-8':'UTF-8'}"><span class="icon-plus-sign"></span>&nbsp;{l s='Add a custom label' mod='gmerchantcenter'}</a>
			</div>

			{if !empty($aTags)}
			<div class="clr_15"></div>
			<div class="col-xs-3">
				<table id="tags" class="table table-striped">
					<tr>
						<th>{l s='Tag' mod='gmerchantcenter'}</th>
						<th>{l s='Edit' mod='gmerchantcenter'}</th>
						<th>{l s='Delete' mod='gmerchantcenter'}</th>
					</tr>
					{foreach from=$aTags name=label key=iKey item=aTag}
					<tr>
						<td>{$aTag.name|escape:'htmlall':'UTF-8'}</td>
						<td><a id="handleGoogleAdwordsEdit" class="fancybox.ajax" href="{$sURI|escape:'htmlall':'UTF-8'}&{$sCtrlParamName|escape:'htmlall':'UTF-8'}={$sController|escape:'htmlall':'UTF-8'}&sAction={$aQueryParams.custom.action|escape:'htmlall':'UTF-8'}&sType={$aQueryParams.custom.type|escape:'htmlall':'UTF-8'}&iTagId={$aTag.id_tag|intval}&sDisplay=adwords"><span class="icon-pencil"></span></a></td>
						<td><a href="#"><i class="icon-trash" title="{l s='delete' mod='gmerchantcenter'}" onclick="check = confirm('{l s='Are you sure to want to delete this custom label' mod='gmerchantcenter'} ? {l s='It will be definitely removed from your database' mod='gmerchantcenter'}');if(!check)return false;$('#loadingGoogleDiv').show();oGmc.hide('bt_google-settings');oGmc.ajax('{$sURI|escape:'htmlall':'UTF-8'}', '{$sCtrlParamName|escape:'htmlall':'UTF-8'}={$sController|escape:'htmlall':'UTF-8'}&sAction={$aQueryParams.customDelete.action|escape:'htmlall':'UTF-8'}&sType={$aQueryParams.customDelete.type|escape:'htmlall':'UTF-8'}&iTagId={$aTag.id_tag|intval}&sDisplay=adwords', 'bt_google-settings-{$sDisplay|escape:'htmlall':'UTF-8'}', 'bt_google-settings-{$sDisplay|escape:'htmlall':'UTF-8'}', null, null, 'loadingGoogleDiv');" ></i></a></td>
					</tr>
					{/foreach}
				</table>
			</div>
			{/if}
		{/if}
		{* END - Google custom label *}

		<div class="clr_20"></div>

		<div id="{$sModuleName|escape:'htmlall':'UTF-8'}GoogleError"></div>

		{if !empty($sDisplay) && $sDisplay == 'analytics'}
		<div class="center">
			<button type="button" name="bt_google-button" class="btn btn-success btn-lg" onclick="oGmc.form('bt_google-{$sDisplay|escape:'htmlall':'UTF-8'}-form', '{$sURI|escape:'htmlall':'UTF-8'}', null, 'bt_google-settings-{$sDisplay|escape:'htmlall':'UTF-8'}', 'bt_google-settings-{$sDisplay|escape:'htmlall':'UTF-8'}', false, false, oGoogleSettingsCallBack, 'Google', 'loadingGoogleDiv');return false;">{l s='Update' mod='gmerchantcenter'}</button>
		</div>
		{/if}
	</form>
</div>
{literal}
<script type="text/javascript">
	//bootstrap components init
	{/literal}{if !empty($bAjaxMode)}{literal}
	$('.label-tooltip, .help-tooltip').tooltip();
	oGmc.runMainGoogle();
	{/literal}{/if}{literal}
</script>
{/literal}