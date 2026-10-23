{*
* 2003-2016 Business Tech
*
* @author Business Tech SARL <http://www.businesstech.fr/en/contact-us>
* @copyright  2003-2016 Business Tech SARL
*}
{if !empty($aErrors)}
	{include file="`$sErrorInclude`"}
{/if}
<table border="0" cellpadding="2" cellspacing="2" width="400px" class="table table-striped">
	<tr>
		<th>{l s='Google ISO code' mod='gmerchantcenter'}</th>
		<th>{l s='Concerned countries' mod='gmerchantcenter'}</th>
		<th align="center">{l s='Update my categories' mod='gmerchantcenter'}</th>
		<th align="center">{l s='Synch from Google' mod='gmerchantcenter'}</th>
	</tr>
	{foreach from=$aCountryTaxonomies name=taxonomy key=sCode item=aTaxonomy}
		<tr>
			<td>{$sCode|escape:'htmlall':'UTF-8'}</td>
			<td>{$aTaxonomy.countryList|escape:'htmlall':'UTF-8'}</td>
			{if !empty($aTaxonomy.updated)}
				<td id="gcupd_{$sCode|escape:'htmlall':'UTF-8'}">
					<a id="handleGoogle" class="fancybox.ajax" href="{$sURI|escape:'htmlall':'UTF-8'}&{$sCtrlParamName|escape:'htmlall':'UTF-8'}={$sController|escape:'htmlall':'UTF-8'}&sAction={$aQueryParams.googleCat.action|escape:'htmlall':'UTF-8'}&sType={$aQueryParams.googleCat.type|escape:'htmlall':'UTF-8'}&iLangId={$aTaxonomy.id_lang|intval}&sLangIso={$sCode|escape:'htmlall':'UTF-8'}"><span class="icon-pencil"></span></a>
				</td>
			{else}
				<td id="gcupd_{$sCode|escape:'htmlall':'UTF-8'}">{l s='Please synch first, click there -->' mod='gmerchantcenter'}</td>
			{/if}
			<td>
				<a id="updateGoogleCategories" href="#" onclick="$('#loadingGoogleCatListDiv').show();oGmc.hide('bt_google-cat-list');oGmc.ajax('{$sURI|escape:'htmlall':'UTF-8'}', '{$sCtrlParamName|escape:'htmlall':'UTF-8'}={$sController|escape:'htmlall':'UTF-8'}&sAction={$aQueryParams.googleCatSync.action|escape:'htmlall':'UTF-8'}&sType={$aQueryParams.googleCatSync.type|escape:'htmlall':'UTF-8'}&iLangId={$aTaxonomy.id_lang|intval}&sLangIso={$sCode|escape:'htmlall':'UTF-8'}', 'bt_google-cat-list', 'bt_google-cat-list', null, null, 'loadingGoogleCatListDiv');"><span class="icon-refresh"></span></a>
				{if !empty($aTaxonomy.currentUpdated)}<span class="icon-ok-sign"></span>{/if}
			</td>
		</tr>
	{/foreach}
</table>
{literal}
<script type="text/javascript">
	$("a#handleGoogle").fancybox({
		'hideOnContentClick' : false
	});
</script>
{/literal}