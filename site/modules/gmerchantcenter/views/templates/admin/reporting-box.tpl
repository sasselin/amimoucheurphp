{*
* 2003-2016 Business Tech
*
* @author Business Tech SARL <http://www.businesstech.fr/en/contact-us>
* @copyright  2003-2016 Business Tech SARL
*}
<div id="{$sModuleName|escape:'htmlall':'UTF-8'}" class="bootstrap" {if empty($bCompare15)}style="text-align: left !important;"{/if}>
	{if !empty($aErrors)}
	{include file="`$sErrorInclude`"}
	{* USE CASE - edition review mode *}
	{else}
	<div id="bt_google-reporting" class="col-xs-12">
		<h3>{l s='Google Merchant Center data diagnostic tool' mod='gmerchantcenter'}</h3>

		<div class="clr_hr"></div>
		<div class="clr_10"></div>

		<h4>{l s='language' mod='gmerchantcenter'} "<strong>{$sLangName|escape:'htmlall':'UTF-8'}</strong>" / {l s='country' mod='gmerchantcenter'} "<strong>{$sCountryName|escape:'htmlall':'UTF-8'}</strong>"</h4>

		<div class="clr_20"></div>

		<div class="alert alert-success">
			{l s='Your feed was successfully generated' mod='gmerchantcenter'} !
			<br />
			{if isset($iProductCount)}
			{l s='Total of products exported:' mod='gmerchantcenter'} <strong>{$iProductCount|intval}</strong>
			{/if}
		</div>

		<div class="clr_20"></div>

		<div class="alert alert-info">
			{l s='This tool allows you to get diagnostic information about the quality of your feed and catch any problems with your data prior to submitting it to Google. For each type of warning, we will provide you with the ability to see which products are affected as well as how to fix the issue.' mod='gmerchantcenter'} :<br/>
			{l s='Please understand that these warnings come from 1) either an incorrect configuration of the module or 2) data missing in your products. WE WILL NOT BE ABLE TO DO ANYTHING ABOUT IT... Either way, please simply refer to the recommendations in the diagnostic tool and you will have the information needed to be able to get rid of all issues.' mod='gmerchantcenter'} :<br/>
		</div>

		<div class="clr_20"></div>

		{if !empty($aReport) && !empty($aReport.notice)}
			{foreach from=$aReport.notice item=aTag key=tagName name=report}
				<div class="alert alert-info">
					<span style="font-size: 13px;"><strong>{$aTag.label|escape:'htmlall':'UTF-8'}</strong>&nbsp;:&nbsp;<strong style="color: #00529B;">{$aTag.count|intval}</strong>&nbsp;{l s='notice' mod='gmerchantcenter'}{if $aTag.count > 1}s{/if}</span><br/>
					<span>{$aTag.msg|escape:'UTF-8'}</span><p style="height: 10px; margin: 0; padding: 0;"> </p>
					<span class="icon-eye-open"></span>&nbsp;<a href="#" onclick="$('#tagReport{$tagName|escape:'htmlall':'UTF-8'}').toggle(); return false;">{l s='View affected products' mod='gmerchantcenter'}</a>
					{if $aTag.faq_id != 0}
						&nbsp;<span class="icon-question-sign"></span>&nbsp;<a href="{$sFaqURL|escape:'htmlall':'UTF-8'}{$aTag.faq_id|intval}&lg={$sFaqLang|escape:'htmlall':'UTF-8'}{if !empty($aTag.anchor)}#{$aTag.anchor|escape:'htmlall':'UTF-8'}{/if}" target="_blank">{l s='Learn how to fix this problem' mod='gmerchantcenter'}</a>
					{/if}
					<div id="tagReport{$tagName|escape:'htmlall':'UTF-8'}" style="display: none;">
						<ul style="padding: 10px; background: #fff; border: 1px solid #ccc;">
							{foreach from=$aTag.data item=aProduct key=key}
								<li style="margin-left: 15px;"><a href="{$aProduct.productUrl|escape:'htmlall':'UTF-8'}" target="_blank">{l s='Product Id' mod='gmerchantcenter'}: {$aProduct.productId|intval} - {l s='Product name' mod='gmerchantcenter'}: {$aProduct.productName|escape:'htmlall':'UTF-8'}</a></li>
							{/foreach}
						</ul>
					</div>
				</div>

			{/foreach}
		{/if}

		{if !empty($aReport) && !empty($aReport.error)}
			{foreach from=$aReport.error item=aTag key=tagName name=report}
				<div class="alert alert-danger">
					<span style="font-size: 13px;"><strong>{$aTag.label|escape:'htmlall':'UTF-8'}</strong>&nbsp;:&nbsp;<strong style="color: orange;">{$aTag.count|intval}</strong>&nbsp;{if $aTag.mandatory == true}{l s='error' mod='gmerchantcenter'}{else}{l s='warning' mod='gmerchantcenter'}{/if}{if $aTag.count > 1}s{/if}</span><br/>
					<span>{$aTag.msg|escape:'UTF-8'}</span><p style="height: 10px; margin: 0; padding: 0;"> </p>
					<span class="icon-eye-open"></span>&nbsp;<a href="#" onclick="$('#tagReport{$tagName|escape:'htmlall':'UTF-8'}').toggle(); return false;">{l s='View affected products' mod='gmerchantcenter'}</a>
					{if $aTag.faq_id != 0}
						&nbsp;<span class="icon-question-sign"></span>&nbsp;<a href="{$sFaqURL|escape:'htmlall':'UTF-8'}{$aTag.faq_id|intval}&lg={$sFaqLang|escape:'htmlall':'UTF-8'}{if !empty($aTag.anchor)}#{$aTag.anchor|escape:'htmlall':'UTF-8'}{/if}" target="_blank">{l s='Learn how to fix this problem' mod='gmerchantcenter'}</a>
					{/if}
					<div id="tagReport{$tagName|escape:'htmlall':'UTF-8'}" style="display: none;">
						<ul style="padding: 10px; background: #fff; border: 1px solid #ccc;">
							{foreach from=$aTag.data item=aProduct key=key}
								<li style="margin-left: 15px;"><a href="{$aProduct.productUrl|escape:'htmlall':'UTF-8'}" target="_blank">{l s='Product Id' mod='gmerchantcenter'}: {$aProduct.productId|intval} - {l s='Product name' mod='gmerchantcenter'}: {$aProduct.productName|escape:'htmlall':'UTF-8'}</a></li>
							{/foreach}
						</ul>
					</div>
				</div>
			{/foreach}
		{/if}

		{if !empty($aReport) && !empty($aReport.warning)}
			{foreach from=$aReport.warning item=aTag key=tagName name=report}
				<div class="alert alert-warning">
					<span style="font-size: 13px;"><strong>{$aTag.label|escape:'htmlall':'UTF-8'}</strong>&nbsp;:&nbsp;<strong style="color: orange;">{$aTag.count|intval}</strong>&nbsp;{if $aTag.mandatory == true}{l s='error' mod='gmerchantcenter'}{else}{l s='warning' mod='gmerchantcenter'}{/if}{if $aTag.count > 1}s{/if}</span><br/>
					<span>{$aTag.msg|escape:'UTF-8'}</span><p style="height: 10px; margin: 0; padding: 0;"> </p>
					<span class="icon-eye-open"></span>&nbsp;<a href="#" onclick="$('#tagReport{$tagName|escape:'htmlall':'UTF-8'}').toggle(); return false;">{l s='View affected products' mod='gmerchantcenter'}</a>
					{if $aTag.faq_id != 0}
					&nbsp;<span class="icon-question-sign"></span>&nbsp;<a href="{$sFaqURL|escape:'htmlall':'UTF-8'}{$aTag.faq_id|intval}&lg={$sFaqLang|escape:'htmlall':'UTF-8'}{if !empty($aTag.anchor)}#{$aTag.anchor|escape:'htmlall':'UTF-8'}{/if}" target="_blank">{l s='Learn how to fix this problem' mod='gmerchantcenter'}</a>
					{/if}
					<div id="tagReport{$tagName|escape:'htmlall':'UTF-8'}" style="display: none;">
						<ul style="padding: 10px; background: #fff; border: 1px solid #ccc;">
							{foreach from=$aTag.data item=aProduct key=key}
								<li style="margin-left: 15px;"><a href="{$aProduct.productUrl|escape:'htmlall':'UTF-8'}" target="_blank">{l s='Product Id' mod='gmerchantcenter'}: {$aProduct.productId|intval} - {l s='Product name' mod='gmerchantcenter'}: {$aProduct.productName|escape:'htmlall':'UTF-8'}</a></li>
							{/foreach}
						</ul>
					</div>
				</div>
			{/foreach}
		{/if}

		<div class="clr_20"></div>

		{if !empty($bCompare149)}
		<p style="text-align: center !important;">
			<button class="btn btn-danger btn-lg" value="{l s='Close' mod='gmerchantcenter'}"  onclick="$.fancybox.close();return false;">{l s='Close' mod='gmerchantcenter'}</button>
		</p>
		{/if}
	</div>
	{/if}
</div>
