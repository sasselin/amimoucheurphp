	{if isset($products) AND $products}
<div id="special_products" class="pos_special_products">
	<div class="title_block">
		<h2>{l s='Hot Sale' mod='posspecialsproductsslide'}</h2>
	</div>
		<div class="block_content">
			<div id="pos-special-products" class="special_product_list">
			{foreach from=$products item=product name=myLoop}
				<div class="item">
					<div class="img-container">
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
					<div class="countdown" >
						{if isset($product.specific_prices)  && $product.specific_prices.to|date_format:"%Y" !=0 }
							{hook h='timecountdown' product=$product } 
							<span 	id="future_date_{$product.id_category_default}_{$product.id_product}" 
									class="id_countdown"></span>
						{/if}
					</div>
                </div>
			{/foreach}
			</div>
		</div>
</div>
<script type="text/javascript"> 
    $(document).ready(function() {
		var owl = $(".special_product_list");
		owl.owlCarousel({
		items : 1,
		itemsDesktop : [1200,1],
		itemsDesktopSmall : [992,1], 
		itemsTablet: [600,1], 
		itemsMobile : [360,1],
		});   
    });

</script>
	{/if}