{*
* 2007-2014 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2014 PrestaShop SA

*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<!-- MODULE Block best sellers -->
<div id="best-sellers_block_right" class="products_block">
    <div class="title_block">
        <h2>{l s='Bestseller' mod='posbestsellers'}</h2>
    </div>

    <div class="block_content">
        {if $best_sellers && $best_sellers|@count > 0}
            <div class="product_list posbestseller">
                {foreach from=$best_sellers item=product name=myLoop}
                    <div class="item-outer">
						<div class="item">
							<div class="col_img col-xs-4">
								<a href="{$product.link|escape:'html'}" title="{$product.legend|escape:'html':'UTF-8'}" class="content_img clearfix">
									<img src="{$link->getImageLink($product.link_rewrite, $product.id_image, 'small_default')|escape:'html'}"
										 alt="{$product.legend|escape:'html':'UTF-8'}"
										 class="img-responsive"/>

								</a>
							</div>
							<div class="col_info col-xs-8">
								{if !$PS_CATALOG_MODE}
									<h2 class="product-name">
										<a href="{$product.link|escape:'html'}" title="{$product.legend|escape:'html':'UTF-8'}">
										{$product.name|strip_tags:'UTF-8'|escape:'html':'UTF-8'}</a>
									</h2>
									{if isset($product.show_price) && $product.show_price && !isset($restricted_country_mode)}
									<div class="price-box">
										{if isset($product.specific_prices) && $product.specific_prices && isset($product.specific_prices.reduction) && $product.specific_prices.reduction > 0}
											<span class="old-price product-price">
												{displayWtPrice p=$product.price_without_reduction}
											</span>
										{/if}
										<span itemprop="price" class="price product-price">
											{if !$priceDisplay}{convertPrice price=$product.price}{else}{convertPrice price=$product.price_tax_exc}{/if}
										</span>
									</div>
									{/if}	
								{/if}
							</div>
						</div>
                    </div>
                {/foreach}
            </div>
        {else}
            <p>{l s='No best sellers at this time' mod='posbestsellers'}</p>
        {/if}
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function() {
     $('.posbestseller').bxSlider({
	  mode: 'vertical',
	  useCSS: false,
	  pager: false,
	  controls: false,
	  auto: true,
	  minSlides: 2,
	  maxSlides: 2,
	  pause: 2500,
	  speed: 1000,
	});
    });
</script>
<!-- /MODULE Block best sellers -->
