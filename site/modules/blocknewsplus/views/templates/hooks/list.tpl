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

{foreach from=$posts item=post name=myLoop}
	<table cellspacing="0" cellpadding="0" border="0" width="100%" class="productsTable compareTableNew">
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
				
				<br/><br/>
				
				<span class="foot_center">{$post.time_add|date_format|escape:'htmlall':'UTF-8'}</span>
				<br>
				</p>
			</td>
			</tr>
		</tbody>
	</table>
{/foreach}