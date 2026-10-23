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
{if count($categoryProducts) > 0 && $categoryProducts !== false}
<section class="page-product-box blockproductscategory block ">
	<div class="title_block">
		<h4>
			{l s='Related Products' mod='productscategory'}
		</h4>
	</div>
	<div id="productscategory_list" class="clearfix">
		<div id="productscategory" class="clearfix">
		 {foreach from=$categoryProducts item='categoryProduct' name=categoryProduct}
			<div class="item">
				<div class="item_inner">
					<div class="img-container">
					<a href="{$link->getProductLink($categoryProduct.id_product, $categoryProduct.link_rewrite, $categoryProduct.category, $categoryProduct.ean13)}" class="lnk_img product-image" title="{$categoryProduct.name|htmlspecialchars}"><img class="img-responsive" src="{$link->getImageLink($categoryProduct.link_rewrite, $categoryProduct.id_image, 'home_default')|escape:'html':'UTF-8'}" alt="{$categoryProduct.name|htmlspecialchars}" /></a> 
					</div>
					<div class="item_bottom">
						<h5 class="product-name">
							<a href="{$link->getProductLink($categoryProduct.id_product, $categoryProduct.link_rewrite, $categoryProduct.category, $categoryProduct.ean13)|escape:'html':'UTF-8'}" title="{$categoryProduct.name|htmlspecialchars}">{$categoryProduct.name|escape:'html':'UTF-8'}</a>
						</h5>
						{if $ProdDisplayPrice AND $categoryProduct.show_price == 1 AND !isset($restricted_country_mode) AND !$PS_CATALOG_MODE}
						<p class="price_display">
							{if isset($categoryProduct.specific_prices) && $categoryProduct.specific_prices}<span class="old-price">{displayWtPrice p=$categoryProduct.price_without_reduction}</span>{/if}
							<span class="price{if isset($categoryProduct.specific_prices) && $categoryProduct.specific_prices} special-price{/if}">{convertPrice price=$categoryProduct.displayed_price}</span>
						</p>
						{/if}
					</div>
				</div>
			</div>
		{/foreach}
		</div>
		<a class="prevpc"><i class="icon-angle-left"></i></a>
		<a class="nextpc"><i class="icon-angle-right"></i></a>
	</div>
</section>
<script>


    $(document).ready(function() {
     
    var owl = $("#productscategory");
     
    owl.owlCarousel({
	autoPlay : false,
    items :4, //10 items above 1000px browser width
    itemsDesktop : [1000,3], //5 items between 1000px and 901px
    itemsDesktopSmall : [900,3], // betweem 900px and 601px
    itemsTablet: [600,2], //2 items between 600 and 0
    itemsMobile : [480,2] // itemsMobile disabled - inherit from itemsTablet option
    });
    // Custom Navigation Events
    $(".nextpc").click(function(){
    owl.trigger('owl.next');
    })
    $(".prevpc").click(function(){
    owl.trigger('owl.prev');
    })
     
    });
</script>
{/if}