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

{if $blocknewsplusnews_home == 1}
<div {if $blocknewsplusis15 != 0}id="left_column"{/if}>
	<div id="blocknewsplus_block_home" class="block {if $blocknewsplusis16 == 1}blockmanufacturer16{else}blockmanufacturer{/if}" id="manufacturers_block_left">
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
	    	
	    	
	    	{if $blocknewsplusnews_h == 1}
	    	<li class="{if $smarty.foreach.myLoop.first}first_item{elseif $smarty.foreach.myLoop.last}last_item{else}item{/if}">
	    		
		    	<table width="100%">
		    				<tr>
		    				{if strlen($item.img)>0}
		    					<td align="center" width="{if $blocknewsplusis16 == 1}10{else}20{/if}%" style="text-align:center">
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
					    		  				title="{$item.title|escape:'htmlall':'UTF-8'}" style="padding:5px">
					    		  				{else}
					    						<a href="{$base_dir_ssl|escape:'htmlall':'UTF-8'}modules/blocknewsplus/item.php?item_id={$item.id|escape:'htmlall':'UTF-8'}" 
					    		  				title="{$item.title|escape:'htmlall':'UTF-8'}" style="padding:5px">
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
		   {elseif $blocknewsplusnews_h == 2}
		   
		   <li style="float:left;width:20%" class="{if $smarty.foreach.myLoop.first}first_item{elseif $smarty.foreach.myLoop.last}last_item{else}item{/if}">
	    		
		    	<table width="100%">
		    				
		    				{if strlen($item.img)>0}
		    				<tr>
		    					<td align="center" style="text-align:center">
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
		    					</tr>
		    				{/if}
		    				<tr>
		    					<td style="padding:5px 0;text-align:center">
		    						
		    									{if $blocknewsplusis_urlrewrite == 1}
					    						<a href="{$base_dir_ssl|escape:'htmlall':'UTF-8'}{$blocknewsplusiso_lng|escape:'htmlall':'UTF-8'}news/{$item.seo_url|escape:'htmlall':'UTF-8'}" 
					    		  				title="{$item.title|escape:'htmlall':'UTF-8'}" style="padding:5px">
					    		  				{else}
					    						<a href="{$base_dir_ssl|escape:'htmlall':'UTF-8'}modules/blocknewsplus/item.php?item_id={$item.id|escape:'htmlall':'UTF-8'}" 
					    		  				title="{$item.title|escape:'htmlall':'UTF-8'}" style="padding:5px">
					    		  				{/if}
		    									   <b>{$item.title|substr:0:43|escape:'htmlall':'UTF-8'}
		    									   {if strlen($item.title)>43}...{/if}</b>
			    		  						</a>
		    						
		    					</td>
		    				</tr>
		    		</table>
		   </li>
		   {/if}
		   
		   
	    		{/foreach}
	    	{/foreach}
	    	<div style="clear:both"></div>
	    </ul>
	    
	    
	    {else}
		<div style="padding:10px">
			{l s='News not found.' mod='blocknewsplus'}
		</div>
		{/if}
		</div>
	</div>
</div>
{/if}