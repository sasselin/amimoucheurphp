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
	<form class="form-horizontal col-lg-10" action="{$sURI|escape:'htmlall':'UTF-8'}" method="post" id="bt_reporting-form" name="bt_reporting-form" {if $smarty.const._GMC_USE_JS == true}onsubmit="javascript: oGmc.form('bt_feed-form', '{$sURI|escape:'htmlall':'UTF-8'}', null, 'bt_reporting-settings', 'bt_reporting-settings', false, false, null, 'Reporting', 'loadingReportingDiv');return false;"{/if}>
		<input type="hidden" name="sAction" value="{$aQueryParams.reporting.action|escape:'htmlall':'UTF-8'}" />
		<input type="hidden" name="sType" value="{$aQueryParams.reporting.type|escape:'htmlall':'UTF-8'}" />

		<div class="clr_20"></div>

		<h3>{l s='Reporting' mod='gmerchantcenter'}</h3>
		<div class="clr_hr"></div>
		<div class="clr_20"></div>

		<div class="alert alert-info" id="info_export">
			{l s='This section allows you to get diagnostic information about the quality of your feed and catch any problems with your data prior to submitting it to Google. For each type of warning, we will provide you with the ability to see which products are affected as well as how to fix the issue.' mod='gmerchantcenter'}<br />
			<span style="font-weight: bold; color: red!important;">{l s='Please understand that these warnings come from 1) either an incorrect configuration of the module or 2) data missing in your products. WE WILL NOT BE ABLE TO DO ANYTHING ABOUT IT. Either way, please simply refer to the recommendations in the diagnostic tool and you will have the information needed to be able to get rid of all issues.' mod='gmerchantcenter'}</span>
		</div>

		<div class="clr_20"></div>

		<div class="form-group" id="optionplus">
			<label class="control-label col-xs-2 col-md-3 col-lg-3"><span class="label-tooltip" data-toggle="tooltip" title data-original-title="{l s='Generate and Show reporting window automatically after regenerating a feed. If you\'ve got an important bulk of products (many thousands), you should leave this option deactivated in order to improve speed and performance of data feed generating. You still can re-activate it the next time if you need information about your missing attributes of products' mod='gmerchantcenter'}"><b>{l s='Activate flux reporting' mod='gmerchantcenter'}</b></span> :</label>
			<div class="col-xs-5 col-md-5 col-lg-6">
					<span class="switch prestashop-switch fixed-width-lg">
						<input type="radio" name="bt_reporting" id="bt_reporting_on" value="1" {if !empty($bReporting)}checked="checked"{/if} onclick="javascript: oGmc.changeSelect('display_reporting', 'display_reporting', null, null, true, true);"/>
						<label for="bt_reporting_on" class="radioCheck">
							{l s='Yes' mod='gmerchantcenter'}
						</label>
						<input type="radio" name="bt_reporting" id="bt_reporting_off" value="0" {if empty($bReporting)}checked="checked"{/if} onclick="javascript: oGmc.changeSelect('display_reporting', 'display_reporting', null, null, true, false);" />
						<label for="bt_reporting_off" class="radioCheck">
							{l s='No' mod='gmerchantcenter'}
						</label>
						<a class="slide-button btn"></a>
					</span>
				<span class="label-tooltip" data-toggle="tooltip" title data-original-title="{l s='Generate and Show reporting window automatically after regenerating a feed. If you\'ve got an important bulk of products (many thousands), you should leave this option deactivated in order to improve speed and performance of data feed generating. You still can re-activate it the next time if you need information about your missing attributes of products' mod='gmerchantcenter'}">&nbsp;<span class="icon-question-sign"></span></span>
			</div>
		</div>

		<div class="form-group" id="display_reporting" {if empty($bReporting)}style="display: none;"{/if}>
			{if !empty($aLangCurrencies)}
				<label class="control-label col-xs-2 col-md-3 col-lg-3">
					<span class="label-tooltip" title="{l s='Select language to get an explicit reporting about products errors and warnings' mod='gmerchantcenter'}"><b>{l s='Select your reporting flux' mod='gmerchantcenter'}</b></span>
				</label>
				<div class="col-xs-3 col-md-3 col-lg-2">
					<select name="bt_select-reporting" id="select_reporting">
						<option value="">...</option>
						{foreach from=$aLangCurrencies item=sISO key=currency}
							<option value="{$sISO|escape:'htmlall':'UTF-8'}">{$sISO|escape:'htmlall':'UTF-8'}</option>
						{/foreach}
					</select>
					<a style="display: none;" id="handleReportingBox" class="fancybox.ajax" href="{$sURI|escape:'htmlall':'UTF-8'}&{$sCtrlParamName|escape:'htmlall':'UTF-8'}={$sController|escape:'htmlall':'UTF-8'}&sAction={$aQueryParams.reportingBox.action|escape:'htmlall':'UTF-8'}&sType={$aQueryParams.reportingBox.type|escape:'htmlall':'UTF-8'}"></a>
				</div>
				<span class="icon-question-sign" title="{l s='Select language to get an explicit reporting about products errors and warnings' mod='gmerchantcenter'}"></span>
			{else}
				<label class="control-label col-xs-2 col-md-3 col-lg-3"></label>
				<div class="col-xs-5 col-md-4 col-lg-3">
					<div class="alert alert-warning">{l s='There are currently no reports available. Please make sure that the "reporting" folder inside the "gmerchantcenter" module folder has correct write permissions.' mod='gmerchantcenter'}</div>
				</div>
			{/if}
		</div>

		<div class="clr_20"></div>

		<div id="{$sModuleName|escape:'htmlall':'UTF-8'}ReportingError"></div>

		<div class="center">
			{if $smarty.const._GMC_USE_JS == true}
				<input type="button" name="bt_feed-button" value="{l s='Update' mod='gmerchantcenter'}" class="btn btn-success btn-lg" onclick="oGmc.form('bt_reporting-form', '{$sURI|escape:'htmlall':'UTF-8'}', null, 'bt_reporting-settings', 'bt_reporting-settings', false, false, null, 'Reporting', 'loadingReportingDiv');return false;" />
			{else}
				<input type="submit" name="bt_feed-button" value="{l s='Update' mod='gmerchantcenter'}" class="btn btn-success btn-lg" />
			{/if}
		</div>
	</form>
</div>
{literal}
<script type="text/javascript">
	$(document).ready(function() {
		// manage change value for reporting
		$("#bt_reporting").change(function() {
			if ($(this).val() == "1") {
				$("#display_reporting").show();
			}
			else {
				$("#display_reporting").hide();
			}
		});

		// fancy box
		$("a#handleReportingBox").fancybox({
			'hideOnContentClick' : false
		});

		// handle reporting files
		$("#select_reporting").bind('change', function (event) {
			$("#select_reporting option:selected").each(function () {
				if ($(this).val() != "") {
					$('a#handleReportingBox').attr('href', $('#handleReportingBox').attr('href') + '&lang=' + $(this).val());

					$('a#handleReportingBox').click();
				}
			});
		});

		//bootstrap components init
		{/literal}{if !empty($bAjaxMode)}{literal}
		$('.label-tooltip, .help-tooltip').tooltip();
		{/literal}{/if}{literal}
	});
</script>
{/literal}