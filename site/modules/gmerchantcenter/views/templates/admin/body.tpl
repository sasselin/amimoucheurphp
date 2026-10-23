{*
* 2003-2016 Business Tech
*
* @author Business Tech SARL <http://www.businesstech.fr/en/contact-us>
* @copyright  2003-2016 Business Tech SARL
*}

<div id='{$sModuleName|escape:'htmlall':'UTF-8'}' class="bootstrap form">
	{* HEADER *}
	{include file="`$sHeaderInclude`"  bContentToDisplay=true}
	{* /HEADER *}

	<div class="clr_20"></div>

	<div>
		<img src="{$smarty.const._GMC_URL_IMG|escape:'htmlall':'UTF-8'}admin/gmc.png" width="350" height="60" alt="" />
		<h3 class="float-right">{l s='Module Version' mod='gmerchantcenter'}: {$sModuleVersion|escape:'htmlall':'UTF-8'}</h3>
	</div>

	<div class="clr_10"></div>

	{* USE CASE - module update not ok  *}
	{if !empty($aUpdateErrors)}
		{include file="`$sErrorInclude`" aErrors=$aUpdateErrors bDebug=true}
		{* USE CASE - display configuration ok *}
	{else}
		{literal}
		<script type="text/javascript">
			var id_language = Number({/literal}{$iCurrentLang|intval}{literal});

			{/literal}
			{* USE CASE - use the new language flags system from PS 1.6 *}
			{if empty($bCompare16)}
			{literal}
			function hideOtherLanguage(id) {
				$('.translatable-field').hide();
				$('.lang-' + id).show();

				var id_old_language = id_language;
				id_language = id;
			}
			{/literal}
			{/if}
			{literal}
		</script>
		{/literal}

		<div class="clr_20"></div>

		<div id="{$sModuleName|escape:'htmlall':'UTF-8'}BlockTab">

			<div class="alert alert-info">
				<h3><i class="icon icon-tag"></i>&nbsp;{l s='Documentation & FAQs' mod='gmerchantcenter'}</h3>
				<p>
					{l s='You can see our documentation and our FAQs website by clicking on the "help / FAQ " tab below' mod='gmerchantcenter'}
				</p>
			</div>

			<ul class="nav nav-tabs" id="workTabs">
				<li class="active"><a data-toggle="tab" href="#tab-0"><span class="icon-home"></span>&nbsp;{l s='Welcome' mod='gmerchantcenter'}</a></li>
				<li><a data-toggle="tab" href="#tab-1"><span class="icon-globe"></span>&nbsp;{l s='Prerequisite check' mod='gmerchantcenter'}</a></li>
				<li><a data-toggle="tab" href="#tab-2"><span class="icon-heart"></span>&nbsp;{l s='Basic settings' mod='gmerchantcenter'}</a></li>
				<li class="dropdown">
					<a data-toggle="dropdown" class="dropdown-toggle" href="#"><span class="icon-edit"></span>&nbsp;{l s='Feed management' mod='gmerchantcenter'}<b class="caret"></b></a>
					<ul class="dropdown-menu">
						<li><a data-toggle="tab" href="#feed-management-dropdown1">{l s='Export method' mod='gmerchantcenter'}</a></li>
						<li><a data-toggle="tab" href="#feed-management-dropdown2">{l s='Product exclusion rules' mod='gmerchantcenter'}</a></li>
						<li><a data-toggle="tab" href="#feed-management-dropdown3">{l s='Feed data options' mod='gmerchantcenter'}</a></li>
						<li><a data-toggle="tab" href="#feed-management-dropdown4">{l s='Apparel feed options' mod='gmerchantcenter'}</a></li>
						<li><a data-toggle="tab" href="#feed-management-dropdown5">{l s='Tax and shipping fees' mod='gmerchantcenter'}</a></li>
					</ul>
				</li>
				<li class="dropdown">
					<a data-toggle="dropdown" class="dropdown-toggle" href="#"><span class="icon-briefcase"></span>&nbsp;{l s='Google management' mod='gmerchantcenter'}<b class="caret"></b></a>
					<ul class="dropdown-menu">
						<li><a data-toggle="tab" href="#google-management-dropdown1">{l s='Google Categories' mod='gmerchantcenter'}</a></li>
						<li><a data-toggle="tab" href="#google-management-dropdown2">{l s='Google Analytics integration' mod='gmerchantcenter'}</a></li>
						<li><a data-toggle="tab" href="#google-management-dropdown3">{l s='Google Adwords / Custom label integration' mod='gmerchantcenter'}</a></li>
					</ul>
				</li>
				<li><a data-toggle="tab" href="#tab-5"><span class="icon-align-justify"></span>&nbsp;{l s='My feeds' mod='gmerchantcenter'}</a></li>
				<li><a data-toggle="tab" href="#tab-6"><span class="icon-play"></span>&nbsp;{l s='Reporting' mod='gmerchantcenter'}</a></li>
				<li><a data-toggle="tab" href="#tab-7"><span class="icon-question-sign"></span>&nbsp;{l s='Help / FAQ' mod='gmerchantcenter'}</a></li>
			</ul>

			{if empty($bHideConfiguration)}
			<div class="tab-content">
				<div id="tab-0" class="tab-pane fade in active information">
					<h3 class="subtitle">{l s='IMPORTANT INFORMATION: PLEASE READ...' mod='gmerchantcenter'}</h3>
					<div class="clr_hr"></div>
					<div class="clr_20"></div>

					<div class="alert alert-info">
						<p>{l s='Register first at http://www.google.com/merchants/' mod='gmerchantcenter'}</p>
						<p>{l s='Please be sure to consult the Help resources section (last tab on the top menu)' mod='gmerchantcenter'}</p>
						<p>{l s='Please be sure to read the documentation carefully and use the help resources available (last tab on the left menu). Please also understand that any errors reported by Google that are due to missing data in your catalog (such as missing EAN or UPC codes) cannot be fixed be us. Only you can fix these problems by making sure your catalog data is complete and accurate.' mod='gmerchantcenter'}</p>
					</div>
					<div class="clr_hr"></div>
					<div class="clr_20"></div>

					<iframe class="btxsellingiframe" src="{$smarty.const._GMC_BT_API_MAIN_URL|escape:'htmlall':'UTF-8'}?ts={$sTs|escape:'htmlall':'UTF-8'}&sName={$smarty.const._GMC_MODULE_SET_NAME|escape:'htmlall':'UTF-8'}&sLang={$sCurrentIso|escape:'htmlall':'UTF-8'}"></iframe>
				</div>

				{* PREREQUISITES SETTINGS *}
				<div id="tab-1" class="tab-pane fade">
					<div id="bt_prerequisites-settings">
						{include file="`$sPrerequisitesInclude`"}
					</div>
				</div>
				{* /PREREQUISITES SETTINGS *}

				{* BASICS SETTINGS *}
				<div id="tab-2" class="tab-pane fade">
					<div id="bt_basics-settings">
						{include file="`$sBasicsInclude`"}
					</div>
					<div class="clr_20"></div>
					<div id="loadingBasicsDiv" style="display: none;">
						<div class="alert alert-info">
							<p style="text-align: center !important;"><img src="{$sBigLoadingImg|escape:'htmlall':'UTF-8'}" alt="Loading" /></p><div class="clr_20"></div>
							<p style="text-align: center !important;">{l s='Your update configuration is in progress' mod='gmerchantcenter'}</p>
						</div>
					</div>
				</div>
				{* /BASICS SETTINGS *}

				{* FEED MANAGEMENT SETTINGS *}
				<div id="feed-management-dropdown1" class="tab-pane fade">
					<div id="bt_feed-settings-export">
						{include file="`$sFeedInclude`" sDisplay="export"}
					</div>
					<div class="clr_20"></div>
				</div>

				<div id="feed-management-dropdown2" class="tab-pane fade">
					<div id="bt_feed-settings-exclusion">
						{include file="`$sFeedInclude`" sDisplay="exclusion"}
					</div>
					<div class="clr_20"></div>
				</div>

				<div id="feed-management-dropdown3" class="tab-pane fade">
					<div id="bt_feed-settings-data">
						{include file="`$sFeedInclude`" sDisplay="data"}
					</div>
					<div class="clr_20"></div>
				</div>

				<div id="feed-management-dropdown4" class="tab-pane fade">
					<div id="bt_feed-settings-apparel">
						{include file="`$sFeedInclude`" sDisplay="apparel"}
					</div>
					<div class="clr_20"></div>
				</div>

				<div id="feed-management-dropdown5" class="tab-pane fade">
					<div id="bt_feed-settings-tax">
						{include file="`$sFeedInclude`" sDisplay="tax"}
					</div>
					<div class="clr_20"></div>
				</div>

				<div id="loadingFeedDiv" style="display: none;">
					<div class="alert alert-info">
						<p style="text-align: center !important;"><img src="{$sBigLoadingImg|escape:'htmlall':'UTF-8'}" alt="Loading" /></p><div class="clr_20"></div>
						<p style="text-align: center !important;">{l s='Your update configuration is in progress' mod='gmerchantcenter'}</p>
					</div>
				</div>

				{literal}
				<script type="text/javascript">
					// run main feed JS
					oGmc.runMainFeed();
				</script>
				{/literal}
				{* /FEED MANAGEMENT SETTINGS *}

				{* GOOGLE MANAGEMENT SETTINGS *}
				<div id="google-management-dropdown1" class="tab-pane fade">
					<div id="bt_google-settings-categories">
						{include file="`$sGoogleInclude`" sDisplay="categories"}
					</div>
					<div class="clr_20"></div>
				</div>

				<div id="google-management-dropdown2" class="tab-pane fade">
					<div id="bt_google-settings-analytics">
						{include file="`$sGoogleInclude`" sDisplay="analytics"}
					</div>
					<div class="clr_20"></div>
				</div>

				<div id="google-management-dropdown3" class="tab-pane fade">
					<div id="bt_google-settings-adwords">
						{include file="`$sGoogleInclude`" sDisplay="adwords"}
					</div>
					<div class="clr_20"></div>
				</div>

				<div id="loadingGoogleDiv" style="display: none;">
					<div class="alert alert-info">
						<p style="text-align: center !important;"><img src="{$sBigLoadingImg|escape:'htmlall':'UTF-8'}" alt="Loading" /></p><div class="clr_20"></div>
						<p style="text-align: center !important;">{l s='Your update configuration is in progress' mod='gmerchantcenter'}</p>
					</div>
				</div>

				{literal}
				<script type="text/javascript">
					// run main Google JS
					oGmc.runMainGoogle();
				</script>
				{/literal}
				{* /GOOGLE MANAGEMENT SETTINGS *}

				{* MY FEEDS SETTINGS *}
				<div id="tab-5" class="tab-pane fade">
					<div id="bt_feed-list-settings">
						{include file="`$sFeedListInclude`"}
					</div>
					<div class="clr_20"></div>
					<div id="loadingFeedListDiv" style="display: none;">
						<div class="alert alert-info">
							<p style="text-align: center !important;"><img src="{$sBigLoadingImg|escape:'htmlall':'UTF-8'}" alt="Loading" /></p><div class="clr_20"></div>
							<p style="text-align: center !important;">{l s='Your update configuration is in progress' mod='gmerchantcenter'}</p>
						</div>
					</div>
				</div>
				{* /MY FEEDS SETTINGS *}

				{* REPORTING SETTINGS *}
				<div id="tab-6" class="tab-pane fade">
					<div id="bt_reporting-settings">
						{include file="`$sReportingInclude`"}
					</div>
					<div class="clr_20"></div>
					<div id="loadingReportingDiv" style="display: none;">
						<div class="alert alert-info">
							<p style="text-align: center !important;"><img src="{$sBigLoadingImg|escape:'htmlall':'UTF-8'}" alt="Loading" /></p><div class="clr_20"></div>
							<p style="text-align: center !important;">{l s='Your update configuration is in progress' mod='gmerchantcenter'}</p>
						</div>
					</div>
				</div>
				{* /REPORTING SETTINGS *}

				{* HELD AND FAQ TAB *}
				<div id="tab-7" class="tab-pane fade">
					<h3>{l s='Help / FAQ' mod='gmerchantcenter'}</h3>
					<div class="clr_hr"></div>
					<div class="clr_20"></div>

					<p><strong style="font-weight: bold;">{l s='MODULE PDF DOCUMENTATION' mod='gmerchantcenter'} :</strong> <a target="_blank" href="{$sDocUri|escape:'htmlall':'UTF-8'}{$sDocName|escape:'htmlall':'UTF-8'}">{$sDocName|escape:'htmlall':'UTF-8'}</a></p>
					<p><strong style="font-weight: bold;">{l s='ONLINE FAQ' mod='gmerchantcenter'} :</strong> <a target="_blank" href="{$smarty.const._GMC_BT_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}?lg={$sCurrentIso|escape:'htmlall':'UTF-8'}">{$smarty.const._GMC_BT_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}</a></p>
					<p><strong style="font-weight: bold;">{l s='GOOGLE DATA REQUIREMENTS HELP: ' mod='gmerchantcenter'}</strong> <a target="_blank" href="http://www.google.com/support/merchants/bin/answer/188494">http://www.google.com/support/merchants/bin/answer/188494</a></p>
					<p><strong style="font-weight: bold;">{l s='GOOGLE MANDATORY ATTRIBUTES BY INDUSTRY: ' mod='gmerchantcenter'}</strong> <a target="_blank" href="https://support.google.com/merchants/answer/1344057">https://support.google.com/merchants/answer/1344057</a></p>
					<p><strong style="font-weight: bold;">{l s='GOOGLE PRODUCT CATEGORIES HELP: ' mod='gmerchantcenter'}</strong> <a target="_blank" href="http://www.google.com/support/merchants/bin/answer/160081">http://www.google.com/support/merchants/bin/answer/160081</a></p>
					<p><strong style="font-weight: bold;">{l s='CONTACT' mod='gmerchantcenter'} :</strong> <a target="_blank" href="{$sContactUs|escape:'htmlall':'UTF-8'}">{$sContactUs|escape:'htmlall':'UTF-8'}</a></p>
				</div>
				{* /HELD AND FAQ TAB *}

			</div>
			{else}
			<div class="clr_20"></div>
			{if !empty($bFileStopExec)}
			<div class="alert alert-danger">
				{l s='Please copy the gmerchantcenter.xml.php file from the gmerchantcenter module\'s directory to your shop\'s root directory' mod='gmerchantcenter'}.
			</div>
			{/if}

			{if !empty($bCurlAndContentStopExec)}
			<div class="alert alert-danger">
				{l s='You need to have either file_get_contents() with the allow_url_fopen directive enabled in the php.ini file, or have the PHP CURL extension enabled in order to retrieve the Google category definition files from Google\'s website. Please contact your web host. If neither of these options are available to you on your server (but at least one should be in most cases), you will not be able to use this module' mod='gmerchantcenter'}.
			</div>
			{/if}

			{if !empty($bMultishopGroupStopExec)}
				<div class="alert alert-danger">
					{l s='For performance reasons, this module cannot be configured within a shop group context. You must configure it one shop at a time' mod='gmerchantcenter'}.
				</div>
			{/if}

			{if !empty($bWeightUnitStopExec)}
				<div class="alert alert-danger">
					{l s='You need to have your weight units correctly set in your back-office. Go to Localization => Localization on PrestaShop 1.5 to 1.6 OR Preferences => Localization on PrestaShop 1.2 to 1.4. Google only accepts the following weight units: kg, g, lb, oz so you must set it to one of these 4 values, exactly and all in lowercase' mod='gmerchantcenter'}.
				</div>
			{/if}

			<div class="clr_20"></div>
			{/if}
		</div>
		{literal}
		<script type="text/javascript">
			$('#workTabs a').click(function (e) {
				e.preventDefault()
				$(this).tab('show')
			});

			$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
				e.target // activated tab
				e.relatedTarget // previous tab
			});

			var sHash = $(location).attr('hash');
			if (sHash != null && sHash != '') {
				$('#workTabs a[href="' + sHash + '"]').tab('show');
			}

			{/literal}{if empty($bCompare16)}{literal}
			$(document).ready(function() {
				$('.label-tooltip, .help-tooltip').tooltip();
				$('.dropdown-toggle').dropdown();
			});

			{/literal}{/if}{literal}
		</script>
		{/literal}
	{/if}
</div>