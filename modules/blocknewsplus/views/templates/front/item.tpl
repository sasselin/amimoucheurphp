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
{if $blocknewsplusis_urlrewrite == 1}
<a href="{$base_dir_ssl|escape:'htmlall':'UTF-8'}{$blocknewsplusiso_lng|escape:'htmlall':'UTF-8'}news">
{else}
<a href="{$base_dir_ssl|escape:'htmlall':'UTF-8'}modules/blocknewsplus/items.php">
{/if}
{l s='News' mod='blocknewsplus'}</a>
	<span class="navigation-pipe">></span>
{$meta_title|escape:'htmlall':'UTF-8'}
{/capture}


{if $blocknewsplusis16 == 0}
{include file="$tpl_dir./breadcrumb.tpl"}

<h2>{$meta_title|escape:'htmlall':'UTF-8'}</h2>
{else}
<h1 class="page-heading">{$meta_title|escape:'htmlall':'UTF-8'}</h1>
{/if}

<div class="blog-post-item">

<div id="list_reviews" class="productsBox1">
{foreach from=$posts item=post name=myLoop}
	<table cellspacing="0" cellpadding="0" border="0" width="100%" class="productsTable compareTableNew {if $blocknewsplusis16==1}float-left-table16{/if}">
		<tbody>
			<tr class="line1">
			<td class="info">
			
			<table width="100%">
				<tr>
				{if strlen($post.img)>0}
					<td style="background:none;border-bottom:none">
					
							<img src="{$base_dir_ssl|escape:'htmlall':'UTF-8'}{$blocknewspluspic|escape:'htmlall':'UTF-8'}{$post.img|escape:'htmlall':'UTF-8'}"
							     title="{$post.title|escape:'htmlall':'UTF-8'}" 
							    />
					</td>
				{/if}
					<td style="background:none;border-bottom:none;{if strlen($post.img)==0} width:100%{else} width:80%{/if}">
						<h3>
								{$post.title|escape:'htmlall':'UTF-8'}
						</h3>
					</td>
				</tr>
			</table>
			
			
				<p class="commentbody_center">
				{$post.content}
				<br><br>
				
				<div class="share-buttons-item">

				<!-- Place this tag where you want the +1 button to render -->
				<g:plusone size="small" href="http://{$smarty.server.HTTP_HOST|escape:'htmlall':'UTF-8'}{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}" count="false"></g:plusone>
				
				<!-- Place this tag after the last plusone tag -->
				{literal}
				<script type="text/javascript">
				  (function() {
				    var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
				    po.src = 'https://apis.google.com/js/plusone.js';
				    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
				  })();
				</script>
				{/literal}
				
					<a href="http://twitter.com/?status={$meta_title|escape:'htmlall':'UTF-8'} : http://{$smarty.server.HTTP_HOST|escape:'htmlall':'UTF-8'}{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}" rel="nofollow" target="_blank" title="Twitter">
					       <img src="{$base_dir_ssl|escape:'htmlall':'UTF-8'}modules/blocknewsplus/views/img/share/1292323517.png" alt="Twitter">
					</a>
					<a href="http://www.facebook.com/share.php?u=http://{$smarty.server.HTTP_HOST|escape:'htmlall':'UTF-8'}{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}" rel="nofollow" target="_blank" title="Facebook">
					      <img src="{$base_dir_ssl|escape:'htmlall':'UTF-8'}modules/blocknewsplus/views/img/share/1292323398.png" alt="Facebook">
					</a>
					<a href="https://www.google.com/bookmarks/mark?op=add&amp;bkmk=http://{$smarty.server.HTTP_HOST|escape:'htmlall':'UTF-8'}{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}&amp;title={$meta_title|escape:'htmlall':'UTF-8'}" rel="nofollow" target="_blank" title="Google Bookmarks">
					      <img src="{$base_dir_ssl|escape:'htmlall':'UTF-8'}modules/blocknewsplus/views/img/share/1293027456.png" alt="Google Bookmarks">
					</a>
					<a href="http://bookmarks.yahoo.com/toolbar/savebm?opener=tb&amp;u=http://{$smarty.server.HTTP_HOST|escape:'htmlall':'UTF-8'}{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}&amp;t={$meta_title|escape:'htmlall':'UTF-8'}" rel="nofollow" target="_blank" title="Yahoo! Bookmarks">
					       <img src="{$base_dir_ssl|escape:'htmlall':'UTF-8'}modules/blocknewsplus/views/img/share/1293094139.png" alt="Yahoo! Bookmarks">
					</a>
					<a href="http://www.linkedin.com/shareArticle?mini=true&amp;url=http://{$smarty.server.HTTP_HOST|escape:'htmlall':'UTF-8'}{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}&amp;title={$meta_title|escape:'htmlall':'UTF-8'}" rel="nofollow" target="_blank" title="LinkedIn">
					       <img src="{$base_dir_ssl|escape:'htmlall':'UTF-8'}modules/blocknewsplus/views/img/share/1292323420.png" alt="LinkedIn">
					</a>
					<a href="http://reddit.com/submit?url=http://{$smarty.server.HTTP_HOST|escape:'htmlall':'UTF-8'}{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}&amp;title={$meta_title|escape:'htmlall':'UTF-8'}" rel="nofollow" target="_blank" title="Reddit">
					      <img src="{$base_dir_ssl|escape:'htmlall':'UTF-8'}modules/blocknewsplus/views/img/share/1292323468.png" alt="Reddit">
					</a>
					<a href="http://del.icio.us/post?url=http://{$smarty.server.HTTP_HOST|escape:'htmlall':'UTF-8'}{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}" rel="nofollow" target="_blank" title="Del.icio.us">
					       <img src="{$base_dir_ssl|escape:'htmlall':'UTF-8'}modules/blocknewsplus/views/img/share/1292323295.png" alt="Del.icio.us">
					</a>
				</div>	
			
			
				
				<span class="foot_center" style="float:left;margin-left:10px" >{$post.time_add|date_format|escape:'htmlall':'UTF-8'}</span>
				<div style="clear:both"></div>
				<br>
				</p>
			</td>
			</tr>
		</tbody>
	</table>
{/foreach}
{if $blocknewsplusis16==1}<div class="clear"></div>{/if}
</div>



{if count($related_posts)>0}

<div class="rel-posts-block">
<div class="related-posts-title">{l s='Related News' mod='blocknewsplus'}</div>

<div class="realated-posts-items">
	{foreach from=$related_posts item=relpost name=myLoop}
	<a title="{$relpost.title|escape:'htmlall':'UTF-8'}"
	{if $blocknewsplusis_urlrewrite == 1}
	    href="{$base_dir_ssl|escape:'htmlall':'UTF-8'}{$blocknewsplusiso_lng|escape:'htmlall':'UTF-8'}news/{$relpost.url|escape:'htmlall':'UTF-8'}" 
	{else}
	 	href="{$base_dir_ssl|escape:'htmlall':'UTF-8'}modules/blocknewsplus/item.php?item_id={$relpost.id|escape:'htmlall':'UTF-8'}" 
	{/if}
	>
		{$relpost.title|escape:'htmlall':'UTF-8'}
	</a>
	<br/>
	{/foreach}
</div>		


</div>
{/if}


{if $blocknewsplusis16==1}<div class="clear"></div>{/if}


</div>