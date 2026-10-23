{*
* 2007-2015 PrestaShop
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
* @author    PrestaShop SA <contact@prestashop.com>
* @copyright 2007-2015 PrestaShop SA
* @license   http://addons.prestashop.com/en/content/12-terms-and-conditions-of-use
* International Registered Trademark & Property of PrestaShop SA
*}
<div class="onboarding-intro">
	<h3 class="text-center">
		{l s='Some advices to get started' mod='gshopping'}
	</h3>
</div>
<div class="steps-list-container">
	<ul class="steps-list">
		<li class="active">
			<span class="title">{l s='Create a Google account' mod='gshopping'}</span>
				<p class="desc">
					{l s='To be able to use this module fully, you need to have a Google Merchant account. ' mod='gshopping'}<br><br>
					<a class="btn btn-default continue_editing" target="_blank" href="https://www.google.com/merchants/Signup">
					{l s="See further information" mod='gshopping'}
					<i class="icon-chevron-right icon-sm"></i>
					</a>
				</p>
		</li>
		<li class="active">
			<span class="title">{l s='Prepare your catalogue with attributes required by Google' mod='gshopping'}</span>
			<p class="desc">
				{l s='Check that you filled in all the required information by Google, especially: Description, references (GTIN Code -EAN 13 MPN, UPC, Jan- brand) and product attributes.' mod='gshopping'}<br><br>
				<a class="btn btn-default continue_editing" target="_blank" href="https://support.google.com/merchants/answer/1344057?hl=en">
				{l s="See required attributes" mod='gshopping'}
				<i class="icon-chevron-right icon-sm"></i>
				</a>
			</p>
		</li>
		<li class="active">
			<span class="title">{l s='Test your feed on google merchant' mod='gshopping'}</span>
			<p class="desc">
				{l s='Read the section 5 of the user documentation to export your catalogue to Google Shopping and see the FAQ on the left menu.' mod='gshopping'}<br><br>
				<a class="btn btn-default continue_editing" target="_blank" href="{$module_dir|escape:'htmlall':'UTF-8'}{$guide_link|escape:'htmlall':'UTF-8'}">
				{l s="Read the documentation" mod='gshopping'}
				<i class="icon-chevron-right icon-sm"></i>
				</a>
			</p>
		</li>
	</ul>
</div>
<div class="clearfix">
</div>