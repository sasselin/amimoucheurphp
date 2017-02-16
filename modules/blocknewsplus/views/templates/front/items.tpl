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

{capture name=path}
{$meta_title|escape:'htmlall':'UTF-8'}
{/capture}



{if $blocknewsplusis16 == 0}
{include file="$tpl_dir./breadcrumb.tpl"}

<h2>{$meta_title|escape:'htmlall':'UTF-8'}</h2>
{else}
<h1 class="page-heading">{$meta_title|escape:'htmlall':'UTF-8'}</h1>
{/if}


<div style="margin-top:10px;border:1px solid #CCCCCC;padding:5px">
{if $count_all > 0}

<div class="toolbar-top">
			
	<div class="sortTools {if $blocknewsplusis16==1}sortTools16{/if}" id="show" style="margin-bottom: 10px;">
		<ul class="actions">
			<li class="frst">
					<strong>{l s='Items' mod='blocknewsplus'}  ( <span id="count_items_top" style="color: #333;">{$count_all|escape:'htmlall':'UTF-8'}</span> )</strong>
			</li>
		</ul>
	</div>

</div>


<div id="list_reviews" class="productsBox1">
{foreach from=$posts item=post name=myLoop}
	<table cellspacing="0" cellpadding="0" border="0" width="100%" class="productsTable compareTableNew {if $blocknewsplusis16==1}float-left-table16{/if}">
		<tbody>
			<tr class="line1">
			<td class="info">
			
			<table width="100%">
				<tr>
				{if strlen($post.img)>0}
					<td style="background:none;border-bottom:none;text-align:center">
					{if $blocknewsplusis_urlrewrite == 1}
						<a href="{$base_dir_ssl|escape:'htmlall':'UTF-8'}{$blocknewsplusiso_lng|escape:'htmlall':'UTF-8'}news/{$post.seo_url|escape:'htmlall':'UTF-8'}" 
					   title="{$post.title|escape:'htmlall':'UTF-8'}">
					{else}
						<a href="{$base_dir_ssl|escape:'htmlall':'UTF-8'}modules/blocknewsplus/item.php?item_id={$post.id|escape:'htmlall':'UTF-8'}" 
					   title="{$post.title|escape:'htmlall':'UTF-8'}">
					{/if}
							<img src="{$base_dir_ssl|escape:'htmlall':'UTF-8'}{$blocknewspluspic|escape:'htmlall':'UTF-8'}{$post.img|escape:'htmlall':'UTF-8'}"
							     title="{$post.title|escape:'htmlall':'UTF-8'}" 
							    />
						</a>
					</td>
				{/if}
					<td style="background:none;border-bottom:none;{if strlen($post.img)==0} width:100%{else} width:80%{/if}">
						<h3>
								{if $blocknewsplusis_urlrewrite == 1}
									<a href="{$base_dir_ssl|escape:'htmlall':'UTF-8'}{$blocknewsplusiso_lng|escape:'htmlall':'UTF-8'}news/{$post.seo_url|escape:'htmlall':'UTF-8'}" 
								   title="{$post.title|escape:'htmlall':'UTF-8'}">
								{else}
									<a href="{$base_dir_ssl|escape:'htmlall':'UTF-8'}modules/blocknewsplus/item.php?item_id={$post.id|escape:'htmlall':'UTF-8'}" 
								   title="{$post.title|escape:'htmlall':'UTF-8'}">
								{/if}
									{$post.title|escape:'htmlall':'UTF-8'}
								</a>
							</h3>
					</td>
				</tr>
			</table>
			
			<p class="commentbody_center">
				{$post.content|substr:0:140|escape:'htmlall':'UTF-8'}
				{if strlen($post.content)>140}...{/if}
				
				{if $blocknewsplusis_urlrewrite == 1}
				<a href="{$base_dir_ssl|escape:'htmlall':'UTF-8'}{$blocknewsplusiso_lng|escape:'htmlall':'UTF-8'}news/{$post.seo_url|escape:'htmlall':'UTF-8'}"
				   title="{$post.title|escape:'htmlall':'UTF-8'}">
				{else}
				<a href="{$base_dir_ssl|escape:'htmlall':'UTF-8'}modules/blocknewsplus/item.php?item_id={$post.id|escape:'htmlall':'UTF-8'}" 
				   title="{$post.title|escape:'htmlall':'UTF-8'}">
				{/if}
						{l s='more' mod='blocknewsplus'}
				</a>
				
				<br><br>
				
				<span class="foot_center">{$post.time_add|date_format|escape:'htmlall':'UTF-8'}</span>
				<br>
				</p>
			</td>
			</tr>
		</tbody>
	</table>
{/foreach}
{if $blocknewsplusis16==1}<div class="clear"></div>{/if}
</div>


<div class="toolbar-bottom">
			
	<div class="sortTools {if $blocknewsplusis16==1}sortTools16{/if}" id="show">
		
		<ul style="margin-left: 38%;">
			<li style="border: medium none; padding: 0pt;">	
			
			<table class="toolbar">
			<tbody>
			<tr class="pager">
				<td id="page_nav" class="pages">
					{$paging}
				</td>
			</tr>
			</tbody>
	</table>
</li>
		</ul>
		
			</div>

		</div>
{else}
	<div style="padding:10px;text-align:center;font-weight:bold">
	{l s='There are not news yet' mod='blocknewsplus'}
	</div>
{/if}

</div>
