<div class="products-rotate">
	<div class="product_img_link">
		<div class="product-image">
			<img class="replace-2x img-responsive" src="{$link->getImageLink($product.link_rewrite, $product.id_image, 'home_default')|escape:'html':'UTF-8'}" alt="{if !empty($product.legend)}{$product.legend|escape:'html':'UTF-8'}{else}{$product.name|escape:'html':'UTF-8'}{/if}" title="{if !empty($product.legend)}{$product.legend|escape:'html':'UTF-8'}{else}{$product.name|escape:'html':'UTF-8'}{/if}" />
		</div>
		<div class="product-image">
				{if isset($rotator_img)}
                    {foreach from=$rotator_img item=image name=thumbnails}
						  {assign var=imageIds value="`$product.id_product`-`$image.id_image`"}
                          <img class="img-responsive thumb_{$image.id_image}" src="{$link->getImageLink($product.link_rewrite, $imageIds, 'home_default')|escape:'html':'UTF-8'}" alt="{$imageTitle}" />
                    {/foreach}
                {/if}		
		</div>
	</div>
</div>