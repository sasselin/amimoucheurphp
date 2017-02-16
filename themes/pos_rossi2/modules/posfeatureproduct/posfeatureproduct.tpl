{if count($products)>1}
	<div class="pos-feature-product home-products col-xs-12 col-sm-8 col-md-6 col-lg-6 col-xs-offset-0 col-sm-offset-2 col-md-offset-0 col-lg-offset-0">
		<div class="title">
			<h2>{l s='featured products' mod='posfeatureproduct'}</h2>
		</div>
		<div class="row">
			<div class="home-product-control">
				<span class="home-product-prev pos-featured-product-home-prev"></span>
				<span class="home-product-next pos-featured-product-home-next"></span>
			</div>
		<ul id ="posfeatureproduct" class="posfeatureproduct slider-row">			
			{foreach from=$products item=product name=myLoop}
				{if $smarty.foreach.myLoop.index % 2 == 0 || $smarty.foreach.myLoop.first }
						<li class="col-xs-12">
					{/if}
						<div class="item">
							<div class="pos-home-product-img">
								<a class="product_img_link"	href="{$product.link|escape:'html':'UTF-8'}" title="{$product.name|escape:'html':'UTF-8'}" itemprop="url">
									<img src="{$link->getImageLink($product.link_rewrite, $product.id_image, 'small_default')|escape:'html'}"
											 alt="{$product.legend|escape:'html':'UTF-8'}"
											 class="img-responsive"/>
								</a>
							</div>
							<div class="pos-home-product-info">
								<h2 class="product-name"><a href="{$product.link|escape:'html'}" title="{$product.name|truncate:50:'...'|escape:'htmlall':'UTF-8'}">{$product.name|truncate:35:'...'|escape:'htmlall':'UTF-8'}</a></h2>
								<div class="price-box">
									{if isset($product.specific_prices) && $product.specific_prices && isset($product.specific_prices.reduction) && $product.specific_prices.reduction > 0}
										<span class="old-price product-price">
											{displayWtPrice p=$product.price_without_reduction}
										</span>
									{/if}
									<span class="price">{if !$priceDisplay}{convertPrice price=$product.price}{else}{convertPrice price=$product.price_tax_exc}{/if}</span>
									<meta itemprop="priceCurrency" content="{$priceDisplay}" />
								</div>
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
					{if $smarty.foreach.myLoop.iteration % 2 == 0 || $smarty.foreach.myLoop.last  }
						</li>
					{/if}
			{/foreach}
		</ul>
		</div>
	</div>
	<script>
		$(document).ready(function() {
			var pos123 = $("#posfeatureproduct");
			pos123.owlCarousel({
			autoPlay : false,
			items : 2,
				itemsDesktop : [1199,1],
				itemsDesktopSmall : [980,1],
				itemsTablet: [599,1],
				itemsMobile : [480,1]
			});
				$(".pos-featured-product-home-next").click(function(){
					pos123.trigger('owl.next');
				});
				$(".pos-featured-product-home-prev").click(function(){
					pos123.trigger('owl.prev');
				});
		});
	</script>
{/if}		 
