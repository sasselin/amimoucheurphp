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

<br />
{if $success == true}
	<p>{l s='Your order in' mod='creditcardofflinepayment'} <span class="bold">{$shop_name|escape:'htmlall':'UTF-8'}</span> {l s='is completed.' mod='creditcardofflinepayment'}
		<br /><br />
		{l s='When your order is verified and the payment accepted, your order will be sent.' mod='creditcardofflinepayment'}
		<br /><br />- {l s='Order total amount:' mod='creditcardofflinepayment'} <span class="price">{$total_to_pay|escape:'htmlall':'UTF-8'}</span>
		<br /><br />{l s='If you have any issue, please contact our' mod='creditcardofflinepayment'} <a href="{$link->getPageLink('contact', true)|escape:'htmlall':'UTF-8'}">{l s='Customer Service' mod='creditcardofflinepayment'}</a>.
	</p>
{else}
	<p class="warning">
		{l s='There has been a problem with your order. Please contact our' mod='creditcardofflinepayment'} <a href="{$link->getPageLink('contact', true)|escape:'htmlall':'UTF-8'}">{l s='Customer Service' mod='creditcardofflinepayment'}</a>.
	</p>
{/if}