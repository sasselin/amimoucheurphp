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

<h3>
  {l s='Reminder of the feed caracterisitics : '  mod='gshopping'}
</h3>
<br/>
<label>
  {l s='Number of categories selected for the feed'  mod='gshopping'}
</label>
<span class="badge">{$category_quantity|escape:'htmlall':'UTF-8'}</span>
<hr/>
<label>
  {l s='Number of products contain in the feed'  mod='gshopping'}
</label>
<span class="badge">{$product_quantity|escape:'htmlall':'UTF-8'}</span>
