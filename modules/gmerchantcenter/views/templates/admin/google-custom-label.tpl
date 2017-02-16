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
	<div id="bt_custom-tag" class="col-xs-12 adwords">
		{if !empty($aTag)}
		<h3>{l s='Update a custom_label' mod='gmerchantcenter'}</h3>
		{else}
		<h3>{l s='Create a custom_label' mod='gmerchantcenter'}</h3>
		{/if}
		<div class="clr_hr"></div>
		<div class="clr_20"></div>

		<script type="text/javascript">
			{literal}
			var oCustomCallBack = [{
				'name' : 'displayGoogleList',
				'url' : '{/literal}{$sURI|escape:'UTF-8'}{literal}',
				'params' : '{/literal}{$sCtrlParamName|escape:'htmlall':'UTF-8'}{literal}={/literal}{$sController|escape:'htmlall':'UTF-8'}{literal}&sAction=display&sType={/literal}{$aQueryParams.google.type|escape:'htmlall':'UTF-8'}{literal}&sDisplay=button3',
				'toShow' : 'bt_google-settings',
				'toHide' : 'bt_google-settings',
				'bFancybox' : false,
				'bFancyboxActivity' : false,
				'sLoadbar' : null,
				'sScrollTo' : null,
				'oCallBack' : {}
			}];
			{/literal}
		</script>

		<form class="form-horizontal" method="post" id="bt_form-custom-tag" name="bt_form-custom-tag" {if $smarty.const._GSR_USE_JS == true}onsubmit="oGmc.form('bt_form-custom-tag', '{$sURI|escape:'htmlall':'UTF-8'}', null, 'bt_custom-tag', 'bt_custom-tag', false, true, oCustomCallBack, 'CustomTag', 'loadingCustomTagDiv');return false;"{/if}>
			<input type="hidden" name="{$sCtrlParamName|escape:'htmlall':'UTF-8'}" value="{$sController|escape:'htmlall':'UTF-8'}" />
			<input type="hidden" name="sAction" value="{$aQueryParams.customUpdate.action|escape:'htmlall':'UTF-8'}" />
			<input type="hidden" name="sType" value="{$aQueryParams.customUpdate.type|escape:'htmlall':'UTF-8'}" />
			{if !empty($aTag)}
			<input type="hidden" name="bt_tag-id" value="{$aTag.id_tag|intval}" id="tag_id" />
			{/if}

			<div class="form-group" id="optionplus">
				<label class="control-label col-lg-2">
					<b>{l s='Name of your custom label' mod='gmerchantcenter'}</b>
				</label>
				<div class="col-xs-3">
					<input type="text" name="bt_label-name" value="{if !empty($aTag)}{$aTag.name|escape:'htmlall':'UTF-8'}{/if}" />
				</div>
			</div>

			<div class="clr_15"></div>

			<div class="form-group">
				<label class="control-label col-lg-2">
					<b>{l s='Please set a value' mod='gmerchantcenter'}</b>
				</label>
				<div class="col-xs-3">
					<select name="bt_custom-type" id="custom">
						<option value="custom_label">custom_label</option>
					</select>
				</div>
			</div>

			<div class="clr_15"></div>

			<div class="alert alert-info">
				<strong>{l s='IMPORTANT NOTE:' mod='gmerchantcenter'}</strong><br/>
				{l s='With Shopping campaigns, you use custom labels when you want to subdivide the products in your campaign using values of your choosing. For example, you can use custom labels to indicate that products are seasonal, on clearance, best sellers, etc. These values can then be selected to use for monitoring, reporting and bidding in your Shopping campaign.' mod='gmerchantcenter'}<br/>
				{l s='You create custom labels as attributes in your data feed. You can have up to five custom labels, numbered 0 to 4.' mod='gmerchantcenter'}
			</div>

			<div class="clr_15"></div>

			<h3>{l s='Manage categories / brands / suppliers' mod='gmerchantcenter'}</h3>

			<div class="clr_hr"></div>
			<div class="clr_20"></div>

			<div class="row">
				<div class="col-xs-4">
					<div class="btn-actions">
						<div class="btn btn-default btn-mini" id="categoryCheck" onclick="return oGmc.selectAll('input.categoryBox', 'check');"><i class="icon-plus-square"></i>&nbsp;{l s='Check All' mod='gmerchantcenter'}</div> - <div class="btn btn-default btn-mini" id="categoryUnCheck" onclick="return oGmc.selectAll('input.categoryBox', 'uncheck');"><i class="icon-minus-square"></i>&nbsp;{l s='Uncheck All' mod='gmerchantcenter'}</div>
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
									<i class="icon icon-folder{if !empty($aCat.bCurrent)}-open{/if}" style="margin-left: {$aCat.iNewLevel|intval}5px;">&nbsp;&nbsp;</i><span style="font-size:12px;">{$aCat.name|escape:'htmlall':'UTF-8'}</span>
								</td>
							</tr>
						{/foreach}
					</table>
				</div>
				<div class="col-xs-4">
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
									<i class="icon icon-folder{if !empty($aBrand.checked)}-open{/if}">&nbsp;&nbsp;</i><span style="font-size:12px;">{$aBrand.name|escape:'htmlall':'UTF-8'}</span>
								</td>
							</tr>
						{/foreach}
					</table>
				</div>
				<div class="col-xs-4">
					<div class="btn-actions">
						<div class="btn btn-default btn-mini" id="supplierCheck" onclick="return oGmc.selectAll('input.supplierBox', 'check');"><i class="icon-plus-square"></i>&nbsp;{l s='Check All' mod='gmerchantcenter'}</div> - <div class="btn btn-default btn-mini" id="supplierUnCheck" onclick="return oGmc.selectAll('input.supplierBox', 'uncheck');"><i class="icon-minus-square"></i>&nbsp;{l s='Uncheck All' mod='gmerchantcenter'}</div>
						<div class="clr_10"></div>
					</div>
					<table cellspacing="0" cellpadding="0" class="table  table-bordered table-striped" style="width: 100%;">
						{foreach from=$aFormatSuppliers name=supplier key=iKey item=aSupplier}
							<tr class="alt_row">
								<td>
									{$aSupplier.id|intval}
								</td>
								<td>
									<input type="checkbox" name="bt_supplier-box[]" class="supplierBox" id="bt_supplier-box_{$aSupplier.id|intval}" value="{$aSupplier.id|intval}" {if !empty($aSupplier.checked)}checked="checked"{/if} />
								</td>
								<td>
									<i class="icon icon-folder{if !empty($aSupplier.checked)}-open{/if}">&nbsp;&nbsp;</i><span style="font-size:12px;">{$aSupplier.name|escape:'htmlall':'UTF-8'}</span>
								</td>
							</tr>
						{/foreach}
					</table>
				</div>
			</div>

			<div class="clr_20"></div>

			<div id="{$sModuleName|escape:'htmlall':'UTF-8'}CustomTagError"></div>

			<div class="clr_20"></div>

			<p style="text-align: center !important;">
				{if $smarty.const._GMC_USE_JS == true}
					<input type="button" name="{$sModuleName|escape:'htmlall':'UTF-8'}CommentButton" class="btn btn-success btn-lg" value="{l s='Modify' mod='gmerchantcenter'}" onclick="oGmc.form('bt_form-custom-tag', '{$sURI|escape:'htmlall':'UTF-8'}', null, 'bt_custom-tag', 'bt_custom-tag', false, true, oCustomCallBack, 'CustomTag', 'loadingCustomTagDiv');return false;" />
				{else}
					<input type="submit" name="{$sModuleName|escape:'htmlall':'UTF-8'}CommentButton" class="btn btn-success btn-lg" value="{l s='Modify' mod='gmerchantcenter'}" />
				{/if}
				{if !empty($bCompare149)}
					<button class="btn btn-danger btn-lg" value="{l s='Cancel' mod='gmerchantcenter'}"  onclick="$.fancybox.close();return false;">{l s='Cancel' mod='gmerchantcenter'}</button>
				{/if}
			</p>
		</form>
	</div>
</div>
<div id="loadingCustomTagDiv" style="display: none;">
	<div class="alert alert-info">
		<p style="text-align: center !important;"><img src="{$sLoadingImg|escape:'htmlall':'UTF-8'}" alt="Loading" /></p><div class="clr_20"></div>
		<p style="text-align: center !important;">{l s='Your update configuration is in progress' mod='gmerchantcenter'}</p>
	</div>
</div>
{/if}