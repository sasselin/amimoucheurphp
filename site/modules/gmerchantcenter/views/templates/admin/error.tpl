{*
* 2003-2016 Business Tech
*
* @author Business Tech SARL <http://www.businesstech.fr/en/contact-us>
* @copyright  2003-2016 Business Tech SARL
*}
<div class="clr_20"></div>

<div class="alert alert-danger">
	<button type="button" class="close" data-dismiss="alert">×</button>
	{foreach from=$aErrors name=condition key=nKey item=aError}
	<strong>{$aError.msg|escape:'htmlall':'UTF-8'}</strong><br/>
	{if $bDebug == true}
	<ol>
		{if !empty($aError.code)}<li>{l s='Error code' mod='gmerchantcenter'} : {$aError.code|intval}</li>{/if}
		{if !empty($aError.file)}<li>{l s='Error file' mod='gmerchantcenter'} : {$aError.file|escape:'htmlall':'UTF-8'}</li>{/if}
		{if !empty($aError.line)}<li>{l s='Error line' mod='gmerchantcenter'} : {$aError.line|intval}</li>{/if}
		{if !empty($aError.context)}<li>{l s='Error context' mod='gmerchantcenter'} : {$aError.context|escape:'htmlall':'UTF-8'}</li>{/if}
	</ol>
		{if !empty($aError.howTo)}
			<strong>{$aError.howTo|escape:'UTF-8'}</strong><br/><br/>
			<div class="clr_hr_danger"></div>
			<div class="clr_10"></div>
		{/if}
	{/if}
	{/foreach}
</div>