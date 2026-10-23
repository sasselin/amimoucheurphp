{**
* Credit card offline payment
*
* NOTICE OF LICENSE
*
* This product is licensed for one customer to use on one installation (test stores and multishop included).
* Site developer has the right to modify this module to suit their needs, but can not redistribute the module in
* whole or in part. Any other use of this module constitues a violation of the user agreement.
*
* DISCLAIMER
*
* NO WARRANTIES OF DATA SAFETY OR MODULE SECURITY
* ARE EXPRESSED OR IMPLIED. USE THIS MODULE IN ACCORDANCE
* WITH YOUR MERCHANT AGREEMENT, KNOWING THAT VIOLATIONS OF
* PCI COMPLIANCY OR A DATA BREACH CAN COST THOUSANDS OF DOLLARS
* IN FINES AND DAMAGE A STORES REPUTATION. USE AT YOUR OWN RISK.
*
*  @author    idnovate.com <info@idnovate.com>
*  @copyright 2016 idnovate.com
*  @license   See above
*}

{if $currency_authorized}
<p class="payment_module">
	<a href="{if version_compare($smarty.const._PS_VERSION_,'1.5','<')}{$cc_path|escape:'htmlall':'UTF-8'}payment.php{else}{$link->getModuleLink('creditcardofflinepayment', 'payment', [], true)|escape:'htmlall':'UTF-8'}{/if}" title="{l s='Pay with credit card' mod='creditcardofflinepayment'}">
		<img src="{$cc_path|escape:'htmlall':'UTF-8'}views/img/creditcardofflinepayment.jpg" alt="{l s='Pay with credit card' mod='creditcardofflinepayment'}" width="86px" height="57px"/>
		{l s='Pay with credit card' mod='creditcardofflinepayment'}
	</a>
</p>
{else}
<p class="payment_module">
	<a href="#" style="cursor:not-allowed;" title="{l s='Pay with credit card' mod='creditcardofflinepayment'}">
		<img src="{$cc_path|escape:'htmlall':'UTF-8'}views/img/creditcardofflinepayment.jpg" alt="{l s='Pay with credit card' mod='creditcardofflinepayment'}" />
		{l s='Payment with credit card is not enabled with this currency' mod='creditcardofflinepayment'}
	</a>
</p>
{/if}