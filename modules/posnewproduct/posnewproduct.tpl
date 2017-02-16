<div class="v-slider pos-new-product col-xs-12 col-sm6 col-md-6 col-lg-4">
	<div class="v-slider-title">
		<h2>{l s='New products' mod='posnewproduct'}</h2>
	</div>
	{if count($products)>1}
		<ul class="posnewproduct">			
			{foreach from=$products item=product name=posNewProducts}
				<li>
					<div class="item">
						<div class="pos-new-product-img">
							<a href="{$product.link|escape:'html'}" title="{$product.legend|escape:'html':'UTF-8'}" class="content_img clearfix">
									<img src="{$link->getImageLink($product.link_rewrite, $product.id_image, 'small_default')|escape:'html'}"
										 alt="{$product.legend|escape:'html':'UTF-8'}"
										 class="img-responsive"/>
							</a>
						</div>
						<div class="pos-new-product-info">
						<h5 class="s_title_block"><a href="{$product.link|escape:'html'}" title="{$product.name|truncate:50:'...'|escape:'htmlall':'UTF-8'}">{$product.name|truncate:35:'...'|escape:'htmlall':'UTF-8'}</a></h5>
						<div>
						<div class="price-box">
						{if isset($product.specific_prices) && $product.specific_prices && isset($product.specific_prices.reduction) && $product.specific_prices.reduction > 0}
						<span class="old-price product-price">
						{displayWtPrice p=$product.price_without_reduction}
						</span>
						{/if}
						<span class="price">{if !$priceDisplay}{convertPrice price=$product.price}{else}{convertPrice price=$product.price_tax_exc}{/if}</span>
						<meta itemprop="priceCurrency" content="{$priceDisplay}" />
						</div>
						<div class="pos-tab-content-bottom">
						{if ($product.id_product_attribute == 0 || (isset($add_prod_display) && ($add_prod_display == 1))) && $product.available_for_order && !isset($restricted_country_mode) && $product.minimal_quantity <= 1 && $product.customizable != 2 && !$PS_CATALOG_MODE}
						{if ($product.allow_oosp || $product.quantity > 0)}
						{if isset($static_token)}
						<a class="exclusive ajax_add_to_cart_button btn btn-default" href="{$link->getPageLink('cart',false, NULL, "add=1&amp;id_product={$product.id_product|intval}&amp;token={$static_token}", false)|escape:'html':'UTF-8'}" rel="nofollow" title="{l s='Add to cart' mod='posnewproduct'}" data-id-product="{$product.id_product|intval}">
						{l s='Add to cart' mod='posnewproduct'}
						</a>
						{else}
						<a class="exclusive ajax_add_to_cart_button btn btn-default" href="{$link->getPageLink('cart',false, NULL, 'add=1&amp;id_product={$product.id_product|intval}', false)|escape:'html':'UTF-8'}" rel="nofollow" title="{l s='Add to cart' mod='posnewproduct'}" data-id-product="{$product.id_product|intval}">
						{l s='Add to cart' mod='posnewproduct'}
						</a>
						{/if}						
						{else}
						<span class="exclusive ajax_add_to_cart_button btn btn-default disabled">
						{l s='Add to cart' mod='posnewproduct'}
						</span>
						{/if}
						{/if}
						</div>
						</div>
						</div>
					</div>
				</li>
			{/foreach}
		</ul>
		<script type="text/javascript">
			$(document).ready(function() {
			 $('.posnewproduct').bxSlider({
			  mode: 'vertical',
			  useCSS: false,
			  pager: false,
			  controls: true,
			  auto: false,
			  minSlides: 4,
			  maxSlides: 4,
			  pause: 2500,
			  speed: 1000,
			});
			});
		</script>
	{else}
	<p class="warning">{l s='No products for this new products.' mod='posnewproduct'}</p>
	{/if}		 
</div>
