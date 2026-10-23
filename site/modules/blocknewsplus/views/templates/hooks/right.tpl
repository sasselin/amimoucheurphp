{*
/**
 * StorePrestaModules SPM LLC.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 *
 /*
 * 
 * @author    StorePrestaModules SPM
 * @category content_management
 * @package blocknewsplus
 * @copyright Copyright StorePrestaModules SPM
 * @license   StorePrestaModules SPM
 */
*}

{if $blocknewsplusnews_right == 1}

	<div id="blocknewsplus_block_right" class="block {if $blocknewsplusis16 == 1}blockmanufacturer16{else}blockmanufacturer{/if}" id="manufacturers_block_left">
		<h4 align="center" class="title_block">
		{if $blocknewsplusis_urlrewrite == 1}
		<a href="{$base_dir_ssl|escape:'htmlall':'UTF-8'}{$blocknewsplusiso_lng|escape:'htmlall':'UTF-8'}news">
		{else}
		<a href="{$base_dir_ssl|escape:'htmlall':'UTF-8'}modules/blocknewsplus/items.php">
		{/if}
			{l s='Last News' mod='blocknewsplus'}
		</a>
		</h4>
		<div class="block_content block-items-data">
		{if count($blocknewsplusitemsblock) > 0}
	    <ul>
	    {foreach from=$blocknewsplusitemsblock item=items name=myLoop1}
	    	{foreach from=$items.data item=item name=myLoop}
	    	<li class="{if $smarty.foreach.myLoop.first}first_item{elseif $smarty.foreach.myLoop.last}last_item{else}item{/if}">
	    	
		    	<table width="100%">
		    				<tr>
		    				{if strlen($item.img)>0}
		    					<td align="center" width="40%">
		    						{if $blocknewsplusis_urlrewrite == 1}
		    						<a href="{$base_dir_ssl|escape:'htmlall':'UTF-8'}{$blocknewsplusiso_lng|escape:'htmlall':'UTF-8'}news/{$item.seo_url|escape:'htmlall':'UTF-8'}" 
		    		  				title="{$item.title|escape:'htmlall':'UTF-8'}">
		    		  				{else}
		    						<a href="{$base_dir_ssl|escape:'htmlall':'UTF-8'}modules/blocknewsplus/item.php?item_id={$item.id|escape:'htmlall':'UTF-8'}" 
		    		  				title="{$item.title|escape:'htmlall':'UTF-8'}">
		    		  				{/if}
		    						<img src="{$base_dir_ssl|escape:'htmlall':'UTF-8'}{$blocknewspluspic|escape:'htmlall':'UTF-8'}{$item.img|escape:'htmlall':'UTF-8'}" title="{$item.title|escape:'htmlall':'UTF-8'}" />
		    						</a>
		    					</td>
		    				{/if}
		    					<td>
		    						<table width="100%">
		    							<tr>
		    								<td align="left">
		    									{if $blocknewsplusis_urlrewrite == 1}
					    						<a href="{$base_dir_ssl|escape:'htmlall':'UTF-8'}{$blocknewsplusiso_lng|escape:'htmlall':'UTF-8'}news/{$item.seo_url|escape:'htmlall':'UTF-8'}" 
					    		  				title="{$item.title|escape:'htmlall':'UTF-8'}">
					    		  				{else}
					    						<a href="{$base_dir_ssl|escape:'htmlall':'UTF-8'}modules/blocknewsplus/item.php?item_id={$item.id|escape:'htmlall':'UTF-8'}" 
					    		  				title="{$item.title|escape:'htmlall':'UTF-8'}">
					    		  				{/if}
		    									   <b>{$item.title|escape:'htmlall':'UTF-8'}</b>
			    		  						</a>
		    								</td>
		    							</tr>
		    							<tr>
		    								<td align="left">{$item.time_add|date_format|escape:'htmlall':'UTF-8'}</td>
		    							</tr>
		    						</table>
			    					
		    					</td>
		    				</tr>
		    		</table>
	    	</li>
	    		{/foreach}
	    	{/foreach}
	    </ul>
	    
	    <p style="text-align:center;margin-top:5px">
			{if $blocknewsplusis_urlrewrite == 1}
			<a class="button_large" title="{l s='View all news' mod='blocknewsplus'}" 
			href="{$base_dir_ssl|escape:'htmlall':'UTF-8'}{$blocknewsplusiso_lng|escape:'htmlall':'UTF-8'}news">
			{else}
			<a class="button_large" title="{l s='View all news' mod='blocknewsplus'}" 
			href="{$base_dir_ssl|escape:'htmlall':'UTF-8'}modules/blocknewsplus/items.php">
			{/if}
			{l s='View all news' mod='blocknewsplus'}
			</a>
		</p>
	    
	    {else}
		<div style="padding:10px">
			{l s='News not found.' mod='blocknewsplus'}
		</div>
		{/if}
		</div>
	</div>
{/if}