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

<div class="bootstrap">
	{if !empty($sGmcLink)}
		{if !empty($iTotalProductToExport)}
			{literal}
			<script type="text/javascript">
				var aDataFeedGenOptions = {
					'sURI' : '{/literal}{$sURI|escape:'UTF-8'}{literal}',
					'sParams' : '{/literal}{$sCtrlParamName|escape:'htmlall':'UTF-8'}={$sController|escape:'htmlall':'UTF-8'}&sAction={$aQueryParams.dataFeed.action|escape:'htmlall':'UTF-8'}&sType={$aQueryParams.dataFeed.type|escape:'htmlall':'UTF-8'}{literal}',
					'iShopId' : {/literal}{$iShopId|intval}{literal},
					'sFilename' : '',
					'iLangId' : 0,
					'sLangIso' : '',
					'sCountryIso' : '',
					'iStep' : 0,
					'iTotal' : {/literal}{$iTotalProductToExport|intval}{literal},
					'iProcess' : 0,
					'sDisplayedCounter' : '#regen_counter',
					'sDisplayedBlock' : '#syncCounterDiv',
					'sDisplaySuccess' : '#regen_xml',
					'sDisplayTotal' : '#total_product_processed',
					'sLoaderBar' : '#loaderbarImg',
					'sErrorContainer' : 'AjaxFeed',
					//'bReporting' : {/literal}{$bReporting|escape:'htmlall':'UTF-8'}{literal},
					'bReporting' : 1,
					'sDisplayReporting' : '#handleGenerateReportingBox',
					'sResultText' : '{/literal}{l s='product(s) exported' mod='gmerchantcenter'}{literal}'
				};
			</script>
			{/literal}
			<div class="clr_15"></div>

			<div class="alert alert-success">
				{l s='Your "Export method" is configured' mod='gmerchantcenter'}
			</div>

			<h3>{l s='Your XML files' mod='gmerchantcenter'}</h3>
			<div class="clr_hr"></div>
			<div class="clr_20"></div>

			{* USE CASE - AVAILABLE FEED FILE LIST *}
			{if !empty($aFeedFileList)}
				<div id="syncCounterDiv" style="display: none;" class="alert alert-info">
					<button type="button" class="close" onclick="$('#syncCounterDiv').hide();">×</button>
					<h4>{l s='Update data feed' mod='gmerchantcenter'}</h4>
					<table>
						<tr>
							<td><b>{l s='Number of products generated:' mod='gmerchantcenter'}</b>&nbsp;</td>
							<td>&nbsp;</td>
							<td><input type="text" size="5" name="bt_regen-counter" id="regen_counter" value="0" />&nbsp;</td>
							<td>&nbsp;</td>
							<td>/</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>{$iTotalProductToExport|intval}</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>{l s='on' mod='gmerchantcenter'}&nbsp;{$iTotalProduct|intval} ({l s='total of products on the shop' mod='gmerchantcenter'})</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td><div class="loaderbar"><img id="loaderbarImg" src="{$smarty.const._GMC_URL_IMG|escape:'htmlall':'UTF-8'}admin/loadbar.png" width="1" height="16" /></div></td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td><div class="reloader"><img src="{$sLoadingImg|escape:'htmlall':'UTF-8'}" alt="Loading" /></div></td>
						</tr>
						<tr>
							<div id="{$sModuleName|escape:'htmlall':'UTF-8'}AjaxFeedError"></div>
						</tr>
					</table>
					<div class="clr_20"></div>
				</div>


				<form class="form-horizontal col-lg-{if empty($bCompare15)}12{else}10{/if}" action="{$sURI|escape:'htmlall':'UTF-8'}" method="post" id="bt_feedlist-form" name="bt_feedlist-form" {if $smarty.const._GMC_USE_JS == true}onsubmit="javascript: oGmc.form('bt_feedlist-form', '{$sURI|escape:'htmlall':'UTF-8'}', null, 'bt_feed-list-settings', 'bt_feed-list-settings', false, false, null, 'FeedList', 'loadingFeedListDiv');return false;"{/if}>
					<input type="hidden" name="sAction" value="{$aQueryParams.feedListUpdate.action|escape:'htmlall':'UTF-8'}" />
					<input type="hidden" name="sType" value="{$aQueryParams.feedListUpdate.type|escape:'htmlall':'UTF-8'}" />

					<table border="0" cellpadding="2" cellspacing="2" class="table table-striped">
						<tr>
							<th>{l s='Regenerate during CRON' mod='gmerchantcenter'}</th>
							<th>{l s='Language / country' mod='gmerchantcenter'}</th>
							<th>{l s='File (right-click and "Save As" to download)' mod='gmerchantcenter'}</th>
							<th>{l s='Last update' mod='gmerchantcenter'}</th>
							<th align="center">{l s='Update / regenerate' mod='gmerchantcenter'}</th>
						</tr>
						{foreach from=$aFeedFileList name=feed key=iKey item=aFeed}
							<tr id="regen_xml_{$aFeed.lang|lower|escape:'htmlall':'UTF-8'}_{$aFeed.country|lower|escape:'htmlall':'UTF-8'}">
								<td><input type="checkbox" name="bt_cron-export[]" value="{$aFeed.lang|lower|escape:'htmlall':'UTF-8'}_{$aFeed.country|escape:'htmlall':'UTF-8'}" {if !empty($aFeed.checked)}checked="checked"{/if} /></td>
								<td>{$aFeed.lang|escape:'htmlall':'UTF-8'}-{$aFeed.country|escape:'htmlall':'UTF-8'}</td>
								<td><a target="_blank" href="{$aFeed.link|escape:'htmlall':'UTF-8'}">{$aFeed.link|escape:'htmlall':'UTF-8'}</a></td>
								<td>{$aFeed.filemtime|escape:'htmlall':'UTF-8'}</td>
								<td class="center">
									<div class="row">
										<a href="javascript:void(0);" class="regenXML" onclick="if (oGmc.bGenerateXmlFlag){literal}{{/literal}alert('{l s='One data feed is currently in progress' mod='gmerchantcenter'}'); return false;{literal}}{/literal}aDataFeedGenOptions.sLangIso='{$aFeed.lang|lower|escape:'htmlall':'UTF-8'}';aDataFeedGenOptions.sCountryIso='{$aFeed.country|lower|escape:'htmlall':'UTF-8'}';aDataFeedGenOptions.iLangId='{$aFeed.langId|intval}';aDataFeedGenOptions.sFilename='{$aFeed.filename|escape:'htmlall':'UTF-8'}';$('#syncCounterDiv').show();oGmc.generateDataFeed(aDataFeedGenOptions);"><span class="icon-refresh"></span></a>&nbsp;<div id="total_product_processed_{$aFeed.lang|lower|escape:'htmlall':'UTF-8'}_{$aFeed.country|lower|escape:'htmlall':'UTF-8'}" style="font-style: bold; display: none; margin-left:20px; vertical-align:text-top;"></div>
									</div>
								</td>
							</tr>
						{/foreach}
					</table>
					<a style="display: none;" id="handleGenerateReportingBox" class="fancybox.ajax" href="{$sURI|escape:'htmlall':'UTF-8'}&{$sCtrlParamName|escape:'htmlall':'UTF-8'}={$sController|escape:'htmlall':'UTF-8'}&sAction={$aQueryParams.reportingBox.action|escape:'htmlall':'UTF-8'}&sType={$aQueryParams.reportingBox.type|escape:'htmlall':'UTF-8'}"></a>

					<div class="clr_20"></div>

					<p style="text-align: center;">
						{if $smarty.const._GMC_USE_JS == true}
							<input type="button" name="bt_feed-button" value="{l s='Update' mod='gmerchantcenter'}" class="btn btn-success btn-lg" onclick="oGmc.form('bt_feedlist-form', '{$sURI|escape:'htmlall':'UTF-8'}', null, 'bt_feed-list-settings', 'bt_feed-list-settings', false, false, null, 'FeedList', 'loadingFeedListDiv');return false;" />
						{else}
							<input type="submit" name="bt_feed-button" value="{l s='Update' mod='gmerchantcenter'}" class="btn btn-success btn-lg" />
						{/if}
					</p>
				</form>
			{* USE CASE - NO AVAILABLE LANGUAGE : CURRENCY : COUNTRY *}
			{else}
			<div class="clr_15"></div>
			<div class="alert alert-warning">
				{l s='Either you just updated your configuration by deactivating the advanced file security feature (in which case, please please reload the page), or there are no files because no valid languages / currencies / countries are available according to the Google\'s requirements' mod='gmerchantcenter'}.
			</div>
			{/if}

			<div class="clr_20"></div>

			<h3>{l s='Your CRON URL' mod='gmerchantcenter'}</h3>
			<div class="clr_hr"></div>
			<div class="clr_20"></div>

			{* USE CASE - AT LEAST ONE DATA FEED IS ACTIVATED IN THE CRON OPTION *}
			{if !empty($aCronLang)}
				<div class="alert alert-info">
					{l s='You should schedule a CRON task to execute this URL, so that your feeds will be updated automatically in time for the scheduled update you set on your Google Merchant Center account' mod='gmerchantcenter'}.
					<div class="clr_5"></div>
					<strong>{l s='Please follow our FAQ link on how to create a CRON task' mod='gmerchantcenter'} <a target="_blank" href="{$smarty.const._GMC_BT_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}faq.php?id=30&lg={$sCurrentIso|escape:'htmlall':'UTF-8'}#cron">{$smarty.const._GMC_BT_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}</a></strong>
				</div>

				<div class="clr_15"></div>

				<table border="0" cellpadding="2" cellspacing="2" class="table table-striped">
					<tr>
						<th>{l s='URL' mod='gmerchantcenter'}</th>
					</tr>
					<tr>
						<td><a target="_blank" href="{$sCronUrl|escape:'htmlall':'UTF-8'}">{$sCronUrl|escape:'htmlall':'UTF-8'}</a></td>
					</tr>
				</table>
			{else}
				<div class="clr_15"></div>
				<div class="alert alert-warning">
					{l s='In the "your XML files" block, you should select one data feed at least, so your feeds will be updated automatically via the CRON task' mod='gmerchantcenter'}.
				</div>
			{/if}

			<div class="clr_20"></div>

			<h3>{l s='Your PHP URL\'s for on-the-fly output' mod='gmerchantcenter'}</h3>
			<div class="clr_hr"></div>
			<div class="clr_20"></div>

			{* USE CASE - THE OUTPUT PHP FILE HAS BEEN WELL COPIED *}
			{if !empty($bCheckOutputFile)}
				{* USE CASE - AVAILABLE FEED FILE LIST *}
				{if !empty($aFlyFileList)}

				<div class="alert alert-info">
					{l s='You can use the "on-the-fly output" URL if your catalog is relativealy small (1000 products maximum). If you are on a dedicated server, you may also be able to process larger catalogs by increasing PHP time-out and memory usage limits' mod='gmerchantcenter'}.
					<div class="clr_5"></div>
					<strong>{l s='Please follow our FAQ link on how to manage the on-the-fly output URL in the Google inetrface' mod='gmerchantcenter'} <a target="_blank" href="{$smarty.const._GMC_BT_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}faq.php?id=30&lg={$sCurrentIso|escape:'htmlall':'UTF-8'}#fly">{$smarty.const._GMC_BT_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}</a></strong>
				</div>

				<table border="0" cellpadding="2" cellspacing="2" class="table table-striped">
					<tr>
						<th>{l s='Language / country' mod='gmerchantcenter'}</th>
						<th>{l s='URL (copy this URL into your Google Merchant Center interface / planning)' mod='gmerchantcenter'}</th>
					</tr>
					{foreach from=$aFlyFileList name=feed key=iKey item=aFlyFeed}
					<tr>
						<td>{$aFlyFeed.iso_code|escape:'htmlall':'UTF-8'}-{$aFlyFeed.country|escape:'htmlall':'UTF-8'}</td>
						<td><a target="_blank" href="{$aFlyFeed.link|escape:'htmlall':'UTF-8'}">{$aFlyFeed.link|escape:'htmlall':'UTF-8'}</a></td>
					</tr>
					{/foreach}
				</table>
				{* USE CASE - NO AVAILABLE LANGUAGE : CURRENCY : COUNTRY *}
				{else}
				<div class="clr_10"></div>
				<div class="alert alert-warning">
					{l s='There are no files because no valid languages / currencies / countries are available according to the Google\'s requirements' mod='gmerchantcenter'}.
				</div>
				{/if}
			{* USE CASE - THE OUTPUT PHP FILE HASN'T BEEN COPIED *}
			{else}
				<div class="clr_10"></div>
				<div class="alert alert-warning">
					{l s='To use this feature, please copy the gmerchantcenter.xml.php file from the gmerchantcenter module\'s directory to your shop\'s root directory' mod='gmerchantcenter'}.
				</div>
			{/if}

			<div id="{$sModuleName|escape:'htmlall':'UTF-8'}FeedListError"></div>
		{* USE CASE - NO CATEGORY OR BRAND HAVE BEEN SELECTED *}
		{else}
			<div class="clr_15"></div>

			<div class="alert alert-warning">
				{l s='You have to select an export method, and check either the category or the brand checkboxes (NOTE: you also need to check if there is at least one product in the selected categories or brands). Please click on the "Feed management > export method" tab' mod='gmerchantcenter'}.
			</div>
		{/if}
	{* USE CASE - NO GOOGLE LINK HAS BEEN FILLED OUT *}
	{else}
		<div class="clr_15"></div>

		<div class="alert alert-warning">
			{l s='You must first update the module\'s configuration options before the files can be accessed' mod='gmerchantcenter'}.
		</div>
	{/if}
</div>
{literal}
<script type="text/javascript">
	// fancy box
	$("a#handleGenerateReportingBox").fancybox({
		'hideOnContentClick' : false
	});

	//bootstrap components init
	{/literal}{if !empty($bAjaxMode)}{literal}
	$('.label-tooltip, .help-tooltip').tooltip();
	{/literal}{/if}{literal}
</script>
{/literal}