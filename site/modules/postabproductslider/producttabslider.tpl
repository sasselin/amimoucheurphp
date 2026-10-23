<script type="text/javascript">
$(document).ready(function() {
	$(".tab_content").hide();
	$(".tab_content:first").show(); 
	$("ul.tabs li").click(function() {
	$("ul.tabs li").removeClass("active");
	$(this).addClass("active");
	$(".tab_content").hide();
	$(".tab_content").removeClass("animate1 {$tab_effect}");
	var activeTab = $(this).attr("rel"); 
	$("#"+activeTab) .addClass("animate1 {$tab_effect}");
	$("#"+activeTab).fadeIn(); 
	});
	
	var tabs = $(".productTabContent");
		tabs.owlCarousel({
		items :4, 
		itemsDesktop : [1199,3], 
		itemsDesktopSmall : [992,2], 
		itemsTablet: [768,2], 
		itemsMobile : [480,1],
		autoPlay :  false,
		});
		 
		// Custom Navigation Events
		$(".productTabSlider_next").click(function(){
		tabs.trigger('owl.next');
		})
		$(".productTabSlider_prev").click(function(){
		tabs.trigger('owl.prev');
		})   
});
</script>

{if $page_name == 'index'}	
	<div class="product-tabs-slider list-products pos_animated">
		<ul class="tabs fx-fadeInDown"> 
			{$count=0}
			{foreach from=$productTabslider item=productTab name=posTabProduct}
				<li class="{if $smarty.foreach.posTabProduct.first}first_item{elseif $smarty.foreach.posTabProduct.last}last_item{else}{/if} {if $count==0} active {/if}" rel="tab_{$productTab.id}"  >
					{$productTab.name}
				</li>
			{$count= $count+1}
			{/foreach}	
		</ul>
		<div class="productTabSlider_control">
			<span class="productTabSlider_prev"></span>
			<span class="productTabSlider_next"></span>
		</div>
		<div class="tab_container fx-fadeInUp"> 
			{foreach from=$productTabslider item=productTab name=posTabProduct}
			<div id="tab_{$productTab.id}" class="tab_content">
			<div class="productTabContent product_list productContent">
				{foreach from=$productTab.productInfo item=product name=posFeatureProducts}
					<div class="cate_item">
					<div class="item-inner">
						<div class="box-img">
							<div class="img_btn">
								<a 	class="quick-view" href="{$product.link|escape:'html':'UTF-8'}" 
									rel="{$product.link|escape:'html':'UTF-8'}" 
									title="{l s='Quick view' mod='postabcateslider1'}"></a>
								<a 	onclick="WishlistCart('wishlist_block_list', 'add', '{$product.id_product|intval}', $('#idCombination').val(), 1,'tabcategory'); return false;" 
									class="add-wishlist wishlist_button" 
									title="{l s='Add to Wishlist' mod='postabcateslider1'}" ></a>
							</div>
							<div class="img_main">
								<a href="{$product.link|escape:'html'}" title="{$product.legend|escape:'html':'UTF-8'}" class="content_img clearfix">
									{if Hook::exec('rotatorImg')}
										{hook h='rotatorImg' product=$product}
									{else}
										<img src="{$link->getImageLink($product.link_rewrite, $product.id_image, 'home_default')|escape:'html'}"
										alt="{$product.legend|escape:'html':'UTF-8'}"
										class="img-responsive"/>
									{/if}
									{if $product.on_sale}
										<span class="sale-box no-print">
											<span class="sale-label">{l s='Sale'}</span>
										</span>
									{/if}
								</a>
							</div>
						</div>
						<h5 class="product-name">
							<a href="{$product.link|escape:'html'}" title="{$product.name|truncate:50:'...'|escape:'htmlall':'UTF-8'}">
								{$product.name|truncate:25:'...'|escape:'htmlall':'UTF-8'}
							</a>
						</h5>
						{if isset($product.show_price) && $product.show_price && !isset($restricted_country_mode)}
							<div class="price-box">
								{if isset($product.specific_prices) && $product.specific_prices && isset($product.specific_prices.reduction) && $product.specific_prices.reduction > 0}
									<span class="old-price product-price">
										{displayWtPrice p=$product.price_without_reduction}
									</span>
								{/if}
								<span itemprop="price" class="price product-price">
									{if !$priceDisplay}
										{convertPrice price=$product.price}
									{else}
										{convertPrice price=$product.price_tax_exc}
									{/if}
								</span>
							</div>
						{/if}
						{if ($product.id_product_attribute == 0 || (isset($add_prod_display) && ($add_prod_display == 1))) && $product.available_for_order && !isset($restricted_country_mode) && $product.minimal_quantity <= 1 && $product.customizable != 2 && !$PS_CATALOG_MODE}
						{if ($product.allow_oosp || $product.quantity > 0)}
						{if isset($static_token)}
							<a class="cate_buy exclusive ajax_add_to_cart_button btn btn-default" href="{$link->getPageLink('cart',false, NULL, "add=1&amp;id_product={$product.id_product|intval}&amp;token={$static_token}", false)|escape:'html':'UTF-8'}" rel="nofollow" title="{l s='Add to cart' mod='postabcateslider1'}" data-id-product="{$product.id_product|intval}">
								{l s='Add to cart' mod='postabcateslider1'}
							</a>
						{else}
							<a class="cate_buy exclusive ajax_add_to_cart_button btn btn-default" href="{$link->getPageLink('cart',false, NULL, 'add=1&amp;id_product={$product.id_product|intval}', false)|escape:'html':'UTF-8'}" rel="nofollow" title="{l s='Add to cart' mod='postabcateslider1'}" data-id-product="{$product.id_product|intval}">
								{l s='Add to cart' mod='postabcateslider1'}
							</a>
						{/if}						
						{else}
							<span class="cate_buy exclusive ajax_add_to_cart_button btn btn-default disabled">
								{l s='Add to cart' mod='postabcateslider1'}
							</span>
						{/if}
						{/if}
					</div>
					</div>
				{/foreach}
			</div>
			</div>
			{/foreach}	
			
		</div> <!-- .tab_container -->
	</div>
{/if}