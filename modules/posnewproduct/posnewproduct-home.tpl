{if count($products)>1}
<div class="pos-new-product-home">
	<div class="pos-new-product-home-title">
		<h2>{l s='Latest products' mod='posnewproduct'}</h2>
	</div>
	<div class="row">
	<ul>			
		{foreach from=$products item=product name=posNewProducts}
			 {if $smarty.foreach.posNewProducts.index == 6}
				{break}
			  {/if}
			<li class="col-xs-6 col-sm-5 col-md-3 col-lg-2">
				<div class="item">
					<div class="pos-new-product-home-img">
						<a class="product_img_link"	href="{$product.link|escape:'html':'UTF-8'}" title="{$product.name|escape:'html':'UTF-8'}" itemprop="url">
							{if Hook::exec('rotatorImg')}
								{hook h='rotatorImg' product=$product}
							{else}
								<img src="{$link->getImageLink($product.link_rewrite, $product.id_image, 'home_default')|escape:'html'}"
									 alt="{$product.legend|escape:'html':'UTF-8'}"
									 class="img-responsive"/>
							{/if}
						</a>
					</div>
					<div class="pos-new-product-home-info">
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
					</div>
					<div class="pos-new-product-home-info-hover">
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
						<a class="quick-view" href="{$product.link|escape:'html':'UTF-8'}" rel="{$product.link|escape:'html':'UTF-8'}" title="{l s='Quick view' mod='postabcateslider1'}">
							{l s='quick view' mod='posnewproduct'}
						</a>
						<a 	onclick="WishlistCart('wishlist_block_list', 'add', '{$product.id_product|intval}', $('#idCombination').val(), 1,'tabcategory'); return false;" 
							class="add-wishlist wishlist_button" 
							title="{l s='Add to Wishlist' mod='postabcateslider1'}" 
							href="#">
							{l s='wishlist' mod='posnewproduct'}
						</a>
					</div>
				</div>
			</li>
		{/foreach}
	</ul>
	</div>
</div>
{/if}		 
