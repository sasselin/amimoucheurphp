{*
* 2003-2016 Business Tech
*
* @author Business Tech SARL <http://www.businesstech.fr/en/contact-us>
* @copyright  2003-2016 Business Tech SARL
*}
<link rel="stylesheet" type="text/css" href="{$smarty.const._GMC_URL_CSS|escape:'htmlall':'UTF-8'}admin.css">
<link rel="stylesheet" type="text/css" href="{$smarty.const._GMC_BT_API_MAIN_URL|escape:'htmlall':'UTF-8'}css/styles.css?ts={$sTs|escape:'htmlall':'UTF-8'}">

{* USE CASE - load CSS and JS when PS version is under 1.6 *}
{if empty($bCompare16)}
	<link rel="stylesheet" type="text/css" href="{$smarty.const._GMC_URL_CSS|escape:'htmlall':'UTF-8'}admin-theme.css">
	<link rel="stylesheet" type="text/css" href="{$smarty.const._GMC_URL_CSS|escape:'htmlall':'UTF-8'}admin-15.css">
	<link rel="stylesheet" type="text/css" href="{$smarty.const._GMC_URL_CSS|escape:'htmlall':'UTF-8'}bootstrap-theme.min.css">
	{* USE CASE - load CSS and JS for  matching JQuery and Fancy Box version *}
	{if empty($bCompare15)}
	<script type="text/javascript" src="{$smarty.const._GMC_URL_JS|escape:'htmlall':'UTF-8'}jquery-1.8.0.min.js"></script>
	<script type="text/javascript" src="{$smarty.const._GMC_URL_JS|escape:'htmlall':'UTF-8'}jquery.fancybox-1.3.4.min.js"></script>
	<link rel="stylesheet" type="text/css" href="{$smarty.const._GMC_URL_CSS|escape:'htmlall':'UTF-8'}jquery.fancybox-1.3.4.css">
	{/if}
	<script type="text/javascript" src="{$smarty.const._GMC_URL_JS|escape:'htmlall':'UTF-8'}bootstrap.min.js"></script>
{/if}
<script type="text/javascript" src="{$autocmp_js|escape:'htmlall':'UTF-8'}"></script>
<link rel="stylesheet" type="text/css" href="{$autocmp_css|escape:'htmlall':'UTF-8'}" />
<script type="text/javascript" src="{$smarty.const._GMC_URL_JS|escape:'htmlall':'UTF-8'}module.js"></script>
<script type="text/javascript">
	// instantiate object
	var oGmc = oGmc || new Gmc('{$sModuleName|escape:'htmlall':'UTF-8'}');

	// get errors translation
	oGmc.msgs = {$oJsTranslatedMsg};

	// set URL of admin img
	oGmc.sImgUrl = '{$smarty.const._GMC_URL_IMG|escape:'htmlall':'UTF-8'}';

	{if !empty($sModuleURI)}
	// set URL of module's web service
	oGmc.sWebService = '{$sModuleURI|escape:'htmlall':'UTF-8'}';
	{/if}
</script>


