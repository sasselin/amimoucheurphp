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
	var oBasicCallBack = [{
		'name' : 'displayFeedList',
		'url' : '{/literal}{$sURI|escape:'UTF-8'}{literal}',
		'params' : '{/literal}{$sCtrlParamName|escape:'htmlall':'UTF-8'}{literal}={/literal}{$sController|escape:'htmlall':'UTF-8'}{literal}&sAction={/literal}{$aQueryParams.feedList.action|escape:'htmlall':'UTF-8'}{literal}&sType={/literal}{$aQueryParams.feedList.type|escape:'htmlall':'UTF-8'}{literal}',
		'toShow' : 'bt_feed-list-settings',
		'toHide' : 'bt_feed-list-settings',
		'bFancybox' : false,
		'bFancyboxActivity' : false,
		'sLoadbar' : null,
		'sScrollTo' : null,
		'oCallBack' : {}
	},
	{
		'name' : 'displayFeed',
		'url' : '{/literal}{$sURI|escape:'UTF-8'}{literal}',
		'params' : '{/literal}{$sCtrlParamName|escape:'htmlall':'UTF-8'}{literal}={/literal}{$sController|escape:'htmlall':'UTF-8'}{literal}&sAction={/literal}{$aQueryParams.feedDisplay.action|escape:'htmlall':'UTF-8'}{literal}&sType={/literal}{$aQueryParams.feedDisplay.type|escape:'htmlall':'UTF-8'}{literal}&sDisplay=export',
		'toShow' : 'bt_feed-settings-export',
		'toHide' : 'bt_feed-settings-export',
		'bFancybox' : false,
		'bFancyboxActivity' : false,
		'sLoadbar' : null,
		'sScrollTo' : null,
		'oCallBack' : {}
	},
	{
		'name' : 'displayFeed',
		'url' : '{/literal}{$sURI|escape:'UTF-8'}{literal}',
		'params' : '{/literal}{$sCtrlParamName|escape:'htmlall':'UTF-8'}{literal}={/literal}{$sController|escape:'htmlall':'UTF-8'}{literal}&sAction={/literal}{$aQueryParams.feedDisplay.action|escape:'htmlall':'UTF-8'}{literal}&sType={/literal}{$aQueryParams.feedDisplay.type|escape:'htmlall':'UTF-8'}{literal}&sDisplay=exclusion',
		'toShow' : 'bt_feed-settings-exclusion',
		'toHide' : 'bt_feed-settings-exclusion',
		'bFancybox' : false,
		'bFancyboxActivity' : false,
		'sLoadbar' : null,
		'sScrollTo' : null,
		'oCallBack' : {}
	},
	{
		'name' : 'displayFeed',
		'url' : '{/literal}{$sURI|escape:'UTF-8'}{literal}',
		'params' : '{/literal}{$sCtrlParamName|escape:'htmlall':'UTF-8'}{literal}={/literal}{$sController|escape:'htmlall':'UTF-8'}{literal}&sAction={/literal}{$aQueryParams.feedDisplay.action|escape:'htmlall':'UTF-8'}{literal}&sType={/literal}{$aQueryParams.feedDisplay.type|escape:'htmlall':'UTF-8'}{literal}&sDisplay=data',
		'toShow' : 'bt_feed-settings-data',
		'toHide' : 'bt_feed-settings-data',
		'bFancybox' : false,
		'bFancyboxActivity' : false,
		'sLoadbar' : null,
		'sScrollTo' : null,
		'oCallBack' : {}
	},
	{
		'name' : 'displayFeed',
		'url' : '{/literal}{$sURI|escape:'UTF-8'}{literal}',
		'params' : '{/literal}{$sCtrlParamName|escape:'htmlall':'UTF-8'}{literal}={/literal}{$sController|escape:'htmlall':'UTF-8'}{literal}&sAction={/literal}{$aQueryParams.feedDisplay.action|escape:'htmlall':'UTF-8'}{literal}&sType={/literal}{$aQueryParams.feedDisplay.type|escape:'htmlall':'UTF-8'}{literal}&sDisplay=apparel',
		'toShow' : 'bt_feed-settings-apparel',
		'toHide' : 'bt_feed-settings-apparel',
		'bFancybox' : false,
		'bFancyboxActivity' : false,
		'sLoadbar' : null,
		'sScrollTo' : null,
		'oCallBack' : {}
	},
	{
		'name' : 'displayFeed',
		'url' : '{/literal}{$sURI|escape:'UTF-8'}{literal}',
		'params' : '{/literal}{$sCtrlParamName|escape:'htmlall':'UTF-8'}{literal}={/literal}{$sController|escape:'htmlall':'UTF-8'}{literal}&sAction={/literal}{$aQueryParams.feedDisplay.action|escape:'htmlall':'UTF-8'}{literal}&sType={/literal}{$aQueryParams.feedDisplay.type|escape:'htmlall':'UTF-8'}{literal}&sDisplay=tax',
		'toShow' : 'bt_feed-settings-tax',
		'toHide' : 'bt_feed-settings-tax',
		'bFancybox' : false,
		'bFancyboxActivity' : false,
		'sLoadbar' : null,
		'sScrollTo' : null,
		'oCallBack' : {}
	}
	];
	{/literal}
</script>

<div class="bootstrap">
	<form class="form-horizontal col-lg-{if empty($bCompare15)}12{else}10{/if}" action="{$sURI|escape:'htmlall':'UTF-8'}" method="post" id="bt_basics-form" name="bt_basics-form" {if $smarty.const._GMC_USE_JS == true}onsubmit="javascript: oGmc.form('bt_basics-form', '{$sURI|escape:'htmlall':'UTF-8'}', null, 'bt_basics-settings', 'bt_basics-settings', false, false, oBasicCallBack, 'Basics', 'loadingBasicsDiv');return false;"{/if}>
		<input type="hidden" name="sAction" value="{$aQueryParams.basic.action|escape:'htmlall':'UTF-8'}" />
		<input type="hidden" name="sType" value="{$aQueryParams.basic.type|escape:'htmlall':'UTF-8'}" />

		<div class="clr_20"></div>

		<h3>{l s='Basic settings' mod='gmerchantcenter'}</h3>
		<div class="clr_hr"></div>
		<div class="clr_20"></div>

		<div class="form-group">
			<label class="control-label col-xs-2 col-md-3 col-lg-3">
				<span class="label-tooltip" title="{l s='Example: http://www.myshop.com - Even if your shop is located in a sub-directory (e.g. http://www.myshop.com/shop), you should still only enter the fully qualified domain name http://www.myshop.com - DO NOT include a trailing slash (/) at the end' mod='gmerchantcenter'}"><b>{l s='Your Prestashop shop\'s URL' mod='gmerchantcenter'}</b></span> :
			</label>
			<div class="col-xs-4 col-md-4 col-lg-2">
				<input type="text" name="bt_link" value="{$sLink|escape:'htmlall':'UTF-8'}" />
			</div>
			<span class="icon-question-sign label-tooltip" title="{l s='Example: http://www.myshop.com - Even if your shop is located in a sub-directory (e.g. http://www.myshop.com/shop), you should still only enter the fully qualified domain name http://www.myshop.com - DO NOT include a trailing slash (/) at the end' mod='gmerchantcenter'}">&nbsp;</span>
		</div>

		<div class="form-group">
			<label class="control-label col-xs-2 col-md-3 col-lg-3">
				<span class="label-tooltip" title="{l s='Enter a short prefix for your shop. For example, if your shop is called "Janes\'s Flowers", enter jf. This is mandatory if you manage several shop feeds from your Google Merchant Center account' mod='gmerchantcenter'}. {l s='Please enter lowercase letters only.' mod='gmerchantcenter'}"><b>{l s='Product ID prefix for your shop' mod='gmerchantcenter'}</b></span> :
			</label>
			<div class="col-xs-3 col-md-3 col-lg-2">
				<input type="text" name="bt_prefix-id" value="{$sPrefixId|escape:'htmlall':'UTF-8'}" />
			</div>
			<span class="icon-question-sign label-tooltip" title="{l s='Enter a short prefix for your shop. For example, if your shop is called "Janes\'s Flowers", enter jf. This is mandatory if you manage several shop feeds from your Google Merchant Center account' mod='gmerchantcenter'}. {l s='Please enter lowercase letters only.' mod='gmerchantcenter'}">&nbsp;</span>
		</div>

		<div class="form-group">
			<label class="control-label col-xs-2 col-md-3 col-lg-3">
			</label>
			<div class="col-xs-2 col-md-2 col-lg-2">
				<div class="alert-tag">Google: [g:type]</div>
			</div>
		</div>

		<div class="form-group">
			<label class="control-label col-xs-2 col-md-3 col-lg-3">
				<span class="label-tooltip" title="{l s='This determines how many products are processed per AJAX / CRON cycle. Default is 200. Only increase this value if you have a large shop and run into problems with server limits. Otherwise, leave it at its default 200 value. It should not be higher than 1000 in any case.' mod='gmerchantcenter'}"><b>{l s='Number of products per cycle' mod='gmerchantcenter'}</b></span> :
			</label>
			<div class="col-xs-3 col-md-3 col-lg-2">
				<input type="text" name="bt_ajax-cycle" value="{$iProductPerCycle|intval}" />
			</div>
			<span class="icon-question-sign label-tooltip" title="{l s='This determines how many products are processed per AJAX / CRON cycle. Default is 200. Only increase this value if you have a large shop and run into problems with server limits. Otherwise, leave it at its default 200 value. It should not be higher than 1000 in any case.' mod='gmerchantcenter'}">&nbsp;</span>
		</div>

		<div class="clr_20"></div>

		<div class="form-group">
			<label class="control-label col-xs-2 col-md-3 col-lg-3">
				<span class="label-tooltip" title="{l s='Choose the largest image size available (such as thickbox). Google requires at least 250x250 and recommends at least 400x400 pixels.' mod='gmerchantcenter'}"><b>{l s='Image size for product photos' mod='gmerchantcenter'}</b></span> :
			</label>
			<div class="col-xs-3 col-md-3 col-lg-2">
				<select name="bt_image-size">';
					{foreach from=$aImageTypes item=aImgType}
					<option value="{$aImgType.name|escape:'htmlall':'UTF-8'}" {if $aImgType.name == $sImgSize}selected="selected"{/if}>{$aImgType.name|escape:'htmlall':'UTF-8'}</option>
					{/foreach}
				</select>
				<div class="alert-tag">Google: [g:image_link]</div>
			</div>
			<div>
				<span class="icon-question-sign label-tooltip" title="{l s='Choose the largest image size available (such as thickbox). Google requires at least 250x250 and recommends at least 400x400 pixels.' mod='gmerchantcenter'}">&nbsp;</span>
				<a class="badge badge-info" href="{$smarty.const._GMC_BT_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}faq.php?id=100&lg={$sCurrentLang|escape:'htmlall':'UTF-8'}#image_link" target="_blank"><i class="icon icon-link"></i>&nbsp;{l s='FAQ' mod='gmerchantcenter'}</a>
			</div>
		</div>

		<div class="clr_20"></div>

		<div class="form-group">
			<label class="control-label col-xs-2 col-md-3 col-lg-3">
				<span class="label-tooltip" title="{l s='On version 1.5, the Home category may have an ID of 1 or 2, depending on whether it was a fresh install or an upgrade from an older PrestaShop version, so we are unable to determine this automatically but need it for other functions in the module. Please select below the category that is labeled "Home"' mod='gmerchantcenter'}"><b>{l s='Please select your Home category' mod='gmerchantcenter'}</b></span> :
			</label>
			<div class="col-xs-3 col-md-3 col-lg-2">
				<select name="bt_home-cat-id">';
					{foreach from=$aHomeCat item=aCat}
						<option value="{$aCat.id_category|intval}" {if $aCat.id_category == $iHomeCatId}selected="selected"{/if}>{$aCat.name|escape:'htmlall':'UTF-8'}</option>
					{/foreach}
				</select>
			</div>
			<span class="icon-question-sign label-tooltip" title="{l s='On version 1.5, the Home category may have an ID of 1 or 2, depending on whether it was a fresh install or an upgrade from an older PrestaShop version, so we are unable to determine this automatically but need it for other functions in the module. Please select below the category that is labeled "Home"' mod='gmerchantcenter'}">&nbsp;</span>
		</div>

		<div class="form-group">
			<label class="control-label col-xs-2 col-md-3 col-lg-3">
				<span class="label-tooltip" title="{l s='Example: Clothing' mod='gmerchantcenter'}"><b>{l s='Name for home category' mod='gmerchantcenter'}</b></span> :
			</label>
			<div id="homecat" class="col-xs-3 col-md-3 col-lg-3" >
				{foreach from=$aLangs item=aLang}
					<div id="bt_home-cat-name_{$aLang.id_lang|intval}" class="translatable-field row lang-{$aLang.id_lang|intval}" {if $aLang.id_lang != $iCurrentLang}style="display:none"{/if}>
						<div class="col-xs-9 col-md-9 col-lg-9">
							<input type="text" id="bt_home-cat-name_{$aLang.id_lang|intval}" name="bt_home-cat-name_{$aLang.id_lang|intval}" {if !empty($aHomeCatLanguages)}{foreach from=$aHomeCatLanguages key=idLang item=sLangTitle}{if $idLang == $aLang.id_lang} value="{$sLangTitle|escape:'htmlall':'UTF-8'}"{/if}{/foreach}{/if} />
						</div>
						<div class="col-xs-3 col-md-3 col-lg-3">
							<button type="button" class="btn btn-default dropdown-toggle" tabindex="-1" data-toggle="dropdown">{$aLang.iso_code|escape:'htmlall':'UTF-8'}&nbsp;<i class="icon-caret-down"></i></button>
							<ul class="dropdown-menu">
								{foreach from=$aLangs item=aLang}
									<li><a href="javascript:hideOtherLanguage({$aLang.id_lang|intval});" tabindex="-1">{$aLang.name|escape:'htmlall':'UTF-8'}</a></li>
								{/foreach}
							</ul>
						</div>
					</div>
				{/foreach}
			</div>
			<div>
				<span class="icon-question-sign label-tooltip" title="{l s='Example: Clothing' mod='gmerchantcenter'}">&nbsp;</span>
				<a class="badge badge-info" href="{$smarty.const._GMC_BT_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}faq.php?id=100&lg={$sCurrentLang|escape:'htmlall':'UTF-8'}#prod_type" target="_blank"><i class="icon icon-link"></i>&nbsp;{l s='FAQ' mod='gmerchantcenter'}</a>
			</div>
		</div>

		<div class="clr_20"></div>

		<div class="form-group">
			<label class="control-label col-xs-2 col-md-3 col-lg-3"><span class="label-tooltip" data-toggle="tooltip" title data-original-title="{l s='If you are submitting your feeds to several countries in the same language but different currencies, for example English in the US ($) and the UK (£), then you need to set this value to Yes (otherwise, set to No), and, if set to Yes, you also need to override the Tools.php class.' mod='gmerchantcenter'}"><b>{l s='Add id_currency in product link ?' mod='gmerchantcenter'}</b></span> :</label>
			<div class="col-xs-5 col-md-5 col-lg-6">
				<span class="switch prestashop-switch fixed-width-lg">
					<input type="radio" name="bt_add-currency" id="bt_add-currency_on" value="true" {if !empty($bAddCurrency)}checked="checked"{/if} />
					<label for="bt_add-currency_on" class="radioCheck">
						{l s='Yes' mod='gmerchantcenter'}
					</label>
					<input type="radio" name="bt_add-currency" id="bt_add-currency_off" value="false" {if empty($bAddCurrency)}checked="checked"{/if} />
					<label for="bt_add-currency_off" class="radioCheck">
						{l s='No' mod='gmerchantcenter'}
					</label>
					<a class="slide-button btn"></a>
				</span>
				<span class="label-tooltip" data-toggle="tooltip" title data-original-title="{l s='If you are submitting your feeds to several countries in the same language but different currencies, for example English in the US ($) and the UK (£), then you need to set this value to Yes (otherwise, set to No), and, if set to Yes, you also need to override the Tools.php class.' mod='gmerchantcenter'}">&nbsp;<span class="icon-question-sign"></span></span>
				<a class="badge badge-info" href="{$smarty.const._GMC_BT_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}faq.php?id=34&lg={$sCurrentLang|escape:'htmlall':'UTF-8'}#image_link" target="_blank"><i class="icon icon-link"></i>&nbsp;{l s='FAQ' mod='gmerchantcenter'}: {l s='How do I override the Tools.php class' mod='gmerchantcenter'}</a>
			</div>
		</div>

		<div class="clr_20"></div>

		<div class="form-group">
			<label class="control-label col-xs-2 col-md-3 col-lg-3">
				<span class="label-tooltip" title="{l s='Note: For Prestashop over v1.4 users, this will be automatically replaced by the product\'s condition for each specific product.' mod='gmerchantcenter'}"><b>{l s='Condition of your products' mod='gmerchantcenter'}</b></span> :
			</label>
			<div class="col-xs-3 col-md-3 col-lg-2">
				<select name="bt_product-condition">
					<option value="0" {if empty($sCondition)}selected="selected"{/if}>--</option>
					{foreach from=$aAvailableCondition item=aCondition key=sCondName}
						<option value="{$sCondName|escape:'htmlall':'UTF-8'}" {if $sCondition == $sCondName}selected="selected"{/if}>{$sCondName|escape:'htmlall':'UTF-8'}</option>
					{/foreach}
				</select>
				<div class="alert-tag">Google: [g:condition]</div>
			</div>
			<div>
				<span class="icon-question-sign label-tooltip" title="{l s='Note: For Prestashop v1.4.x users, this will be automatically replaced by the product\'s condition for each specific product.' mod='gmerchantcenter'}">&nbsp;</span>
				<a class="badge badge-info" href="{$smarty.const._GMC_BT_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}faq.php?id=100&lg={$sCurrentLang|escape:'htmlall':'UTF-8'}#prod_condition" target="_blank"><i class="icon icon-link"></i>&nbsp;{l s='FAQ' mod='gmerchantcenter'}</a>
			</div>
		</div>

		<div class="clr_20"></div>

		<div class="form-group">
			<label class="control-label col-xs-2 col-md-3 col-lg-3">
				<span class="label-tooltip" title="{l s='This option set each product\'s title in your data feed like this : category name + product name'  mod='gmerchantcenter'}"><b>{l s='Advanced product name' mod='gmerchantcenter'}</b></span> :
			</label>
			<div class="col-xs-3 col-md-3 col-lg-3">
				<select name="bt_advanced-prod-name" id="bt_advanced-prod-name">
					<option value="0" {if $iAdvancedProductName == 0}selected="selected"{/if} >{l s='Just the normal product name' mod='gmerchantcenter'}</option>
					<option value="1" {if $iAdvancedProductName == 1}selected="selected"{/if} >{l s='Current category name + Product name' mod='gmerchantcenter'}</option>
					<option value="2" {if $iAdvancedProductName == 2}selected="selected"{/if} >{l s='Product name + Current category name' mod='gmerchantcenter'}</option>
					<option value="3" {if $iAdvancedProductName == 3}selected="selected"{/if} >{l s='Brand name + Product name' mod='gmerchantcenter'}</option>
					<option value="4" {if $iAdvancedProductName == 4}selected="selected"{/if} >{l s='Product name + Brand name' mod='gmerchantcenter'}</option>
				</select>
				<br/>
				<div class="alert alert-info">
					{l s='Google recommends you include characteristics such as brand in the title which differentiates the item from other products.' mod='gmerchantcenter'}
				</div>
				<div class="alert alert-warning" id="bt_info-title-category">
					{l s='Be careful : this option will set your product\'s title with the current category name. However, Google will still require your product titles to be no more than 150 characters long' mod='gmerchantcenter'}
				</div>
				<div class="alert alert-warning" id="bt_info-title-brand">
					{l s='Be careful : this option will set your product\'s title with the brand name. However, Google will still require your product titles to be no more than 150 characters long' mod='gmerchantcenter'}
				</div>
			</div>
			<span class="icon-question-sign label-tooltip" title="{l s='This option set each product\'s title in your data feed like this : category name + product name' mod='gmerchantcenter'}">&nbsp;</span>
		</div>

		<div class="clr_20"></div>

		<div class="form-group">
			<label class="control-label col-xs-2 col-md-3 col-lg-3">
				<span class="label-tooltip" title="{l s='Google will refuse your product feed if your product titles have too many UPPERCASE letters. Using this feature will capitalize only the first letter of your product title. If you have included the brand name in the product title, it will attempt to capitalize its first letter(s) as well. Ex: "Leather Bag by LOUIS VUITTON" will become "Leather bag by Louis Vuitton", provided your product has been assigned the corresponding manufacturer and they are spelled the same way. NOTE: this will only work with European languages, not languages such as Chinese. If you are experiencing problems with the product title in your feed, please turn this feature off'  mod='gmerchantcenter'}"><b>{l s='Remove excessive UPPERCASE letters from product titles?' mod='gmerchantcenter'}</b></span> :
			</label>
			<div class="col-xs-3 col-md-3 col-lg-3">
				<select name="bt_advanced-prod-title" id="bt_advanced-prod-title">
					<option value="0" {if $iAdvancedProductTitle == 0}selected="selected"{/if} >---</option>
					<option value="1" {if $iAdvancedProductTitle == 1}selected="selected"{/if} >{l s='Uppercase the first character of each word' mod='gmerchantcenter'}</option>
					<option value="2" {if $iAdvancedProductTitle == 2}selected="selected"{/if} >{l s='Uppercase the first character only' mod='gmerchantcenter'}</option>
				</select>
			</div>
			<span class="icon-question-sign label-tooltip" title="{l s='Google will refuse your product feed if your product titles have too many UPPERCASE letters. Using this feature will capitalize only the first letter of your product title. If you have included the brand name in the product title, it will attempt to capitalize its first letter(s) as well. Ex: "Leather Bag by LOUIS VUITTON" will become "Leather bag by Louis Vuitton", provided your product has been assigned the corresponding manufacturer and they are spelled the same way. NOTE: this will only work with European languages, not languages such as Chinese. If you are experiencing problems with the product title in your feed, please turn this feature off' mod='gmerchantcenter'}">&nbsp;</span>
		</div>

		<div class="clr_20"></div>

		<div class="form-group">
			<label class="control-label col-xs-2 col-md-3 col-lg-3"><span class="label-tooltip" data-toggle="tooltip" title data-original-title="{l s='This is a security measure so people from the outside cannot call the feed URL and be able to view your feed.' mod='gmerchantcenter'}"><b>{l s='Advanced file security' mod='gmerchantcenter'}</b></span> :</label>
			<div class="col-xs-4 col-md-5 col-lg-3">
				<span class="switch prestashop-switch fixed-width-lg">
					<input type="radio" name="bt_protection-mode" id="bt_protection-mode_on" value="1" {if !empty($bFeedProtection)}checked="checked"{/if} onclick="javascript: oGmc.changeSelect('bt_configure-protection', 'bt_configure-protection', null, null, true, true);" />
					<label for="bt_protection-mode_on" class="radioCheck">
						{l s='Yes' mod='gmerchantcenter'}
					</label>
					<input type="radio" name="bt_protection-mode" id="bt_protection-mode_off" value="0" {if empty($bFeedProtection)}checked="checked"{/if} onclick="javascript: oGmc.changeSelect('bt_configure-protection', 'bt_configure-protection', null, null, true, false);" />
					<label for="bt_protection-mode_off" class="radioCheck">
						{l s='No' mod='gmerchantcenter'}
					</label>
					<a class="slide-button btn"></a>
				</span>
				<span class="label-tooltip" data-toggle="tooltip" title data-original-title="{l s='This is a security measure so people from the outside cannot call the feed URL and be able to view your feed.' mod='gmerchantcenter'}">&nbsp;<span class="icon-question-sign"></span></span>
			</div>
		</div>
		<div class="form-group">
			<label class="control-label col-xs-2 col-md-3 col-lg-3"></label>
			<div class="col-xs-4 col-md-5 col-lg-3">
				<div class="alert alert-warning" id="protection_off" style="display: {if empty($bFeedProtection)}block{else}none{/if};">
					{l s='You have to generate the feed' mod='gmerchantcenter'}
				</div>
			</div>
		</div>

		<div id="bt_configure-protection" style="display: {if !empty($bFeedProtection)}block{else}none{/if};">
			<h3>{l s='Advanced file security' mod='gmerchantcenter'}</h3>
			<div class="clr_hr"></div>
			<div class="clr_20"></div>

			<div class="alert alert-info">
				<p>{l s='In order to secure your file PHP URL\'s for on-the-fly output, you should copy the file "gmerchantcenter.xml.php" at website root ' mod='gmerchantcenter'}</p>
				<p>{l s='This option sets a unique identifier in your xml file name' mod='gmerchantcenter'}</p>
			</div>

			<div class="form-group">
				<label class="control-label col-xs-2 col-md-3 col-lg-3">
					<span class="label-tooltip" title="{l s='This is a security measure so people from the outside cannot call the feed URL and be able to view your information. We have automatically generated one on install for your convenience.' mod='gmerchantcenter'}"><b>{l s='Your secure token' mod='gmerchantcenter'}</b></span> :
				</label>
				<div class="col-xs-3 col-md-3 col-lg-3">
					<input type="text" maxlength="32" name="bt_feed-token" id="bt_feed-token" value="{$sFeedToken|escape:'htmlall':'UTF-8'}" />
				</div>
				<span class="icon-question-sign label-tooltip" title="{l s='This is a security measure so people from the outside cannot call the feed URL and be able to view your information. We have automatically generated one on install for your convenience.' mod='gmerchantcenter'}">&nbsp;</span>
			</div>
		</div>

		<div class="clr_20"></div>

		<div id="{$sModuleName|escape:'htmlall':'UTF-8'}BasicsError"></div>

		<div class="center">
			<button class="btn btn-success btn-lg" onclick="oGmc.form('bt_basics-form', '{$sURI|escape:'htmlall':'UTF-8'}', null, 'bt_basics-settings', 'bt_basics-settings', false, false, oBasicCallBack, 'Basics', 'loadingBasicsDiv');return false;">{l s='Update' mod='gmerchantcenter'}</button>
		</div>
	</form>
</div>

<div class="clr_20"></div>

{literal}
<script type="text/javascript">
	//bootstrap components init
	// manage change value for advance protection
	//$("#bt_protection-mode").change(function() {
	$("input [name='bt_protection-mode']").bind($.browser.msie ? 'click' : 'change', function (event){
		if ($(this).val() == "0") {
			$("#protection_off").show();
		}
		else {
			$("#protection_off").hide();
		}
	});

	//manage information for info title
	if ($("#bt_advanced-prod-name").val() == "0") {
		$("#bt_info-title-category").hide();
		$("#bt_info-title-brand").hide();
	}
	if ($("#bt_advanced-prod-name").val() == "1"
		|| $("#bt_advanced-prod-name").val() == "2"
	) {
		$("#bt_info-title-category").show();
		$("#bt_info-title-brand").hide();
	}
	if ($("#bt_advanced-prod-name").val() == "3"
		|| $("#bt_advanced-prod-name").val() == "4"
	) {
		$("#bt_info-title-category").hide();
		$("#bt_info-title-brand").show();
	}
	$("#bt_advanced-prod-name").change(function() {
		if ($(this).val() == "0" ) {
			$("#bt_info-title-category").hide();
			$("#bt_info-title-brand").hide();
		}
		if ($(this).val() == "1"
			|| $(this).val() == "2"
		) {
			$("#bt_info-title-category").show();
			$("#bt_info-title-brand").hide();
		}
		if ($(this).val() == "3"
			|| $(this).val() == "4"
		) {
			$("#bt_info-title-category").hide();
			$("#bt_info-title-brand").show();
		}
	});
	{/literal}{if !empty($bAjaxMode)}{literal}
	$('.label-tooltip, .help-tooltip').tooltip();
	{/literal}{/if}{literal}
</script>
{/literal}