{*
* 2007-2014 PrestaShop
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

<div class="page-head">
	<h2 class="page-title">
		{l s='Configure your SEO' mod='gshopping'}
	</h2>
	<ul class="breadcrumb page-breadcrumb">
		<li>
			<i class="icon-puzzle-piece"></i>Modules
		</li>
		<li>seohelping</li>
		<li>
			<i class="icon-wrench"></i>
			{l s='Configure' mod='gshopping'}
		</li>
	</ul>
	<div class="page-bar toolbarBox">
		<div class="btn-toolbar">
			<ul class="cc_button nav nav-pills pull-right">
				{if $module_active == '1'}
				<li>
					<a id="desc-module-hook" class="toolbar_btn" href="{$module_form|escape:'htmlall':'UTF-8'}&amp;enable=0" title="{l s='Disable' mod='gshopping'}">
						<i class="process-icon-off"></i>
						<div>{l s='Disable' mod='gshopping'}</div>
					</a>
				</li>
				{else}
				<li>
					<a id="desc-module-hook" class="toolbar_btn" href="{$module_form|escape:'htmlall':'UTF-8'}&amp;enable=1" title="{l s='Enable' mod='gshopping'}">
						<i class="process-icon-off"></i>
						<div>{l s='Enable' mod='gshopping'}</div>
					</a>
				</li>
				{/if}
				<li>
					<a id="desc-module-hook" class="toolbar_btn" href="{$module_form|escape:'htmlall':'UTF-8'}&amp;uninstall={$module_name|escape:'htmlall':'UTF-8'}" title="{l s='Uninstall' mod='gshopping'}">
						<i class="process-icon-uninstall"></i>
						<div>{l s='Uninstall' mod='gshopping'}</div>
					</a>
				</li>
				<li>
					<a id="desc-module-hook" class="toolbar_btn" href="{$module_reset|escape:'htmlall':'UTF-8'}" title="{l s='Reset' mod='gshopping'}">
						<i class="process-icon-reset"></i>
						<div>{l s='Reset' mod='gshopping'}</div>
					</a>
				</li>
				<li>
					<a id="desc-module-hook" class="toolbar_btn" href="{$module_hook|escape:'htmlall':'UTF-8'}" title="{l s='Manage hooks' mod='gshopping'}">
						<i class="process-icon-anchor"></i>
						<div>{l s='Manage hooks' mod='gshopping'}</div>
					</a>
				</li>
				<li>
					<a id="desc-module-back" class="toolbar_btn" href="{$module_back|escape:'htmlall':'UTF-8'}" title="{l s='Back' mod='gshopping'}">
						<i class="process-icon-back"></i>
						<div>{l s='Back' mod='gshopping'}</div>
					</a>
				</li>
			</ul>
		</div>
	</div>
</div>