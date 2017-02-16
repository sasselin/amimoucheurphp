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

{if $blocknewsplusis_news != 0}
       <meta property="og:title" content="{$blocknewsplusname|escape:'htmlall':'UTF-8'}"/>
	   <meta property="og:image" content="{$base_dir_ssl|escape:'htmlall':'UTF-8'}upload/blocknewsplus/{$blocknewsplusimg|escape:'htmlall':'UTF-8'}"/>
	   <meta property="og:type" content="product"/>
{/if}

{if $blocknewsplusis15 == 0}
<link href="{$base_dir_ssl|escape:'htmlall':'UTF-8'}modules/blocknewsplus/views/css/blocknewsplus.css" rel="stylesheet" type="text/css" media="all" />
	
{literal}
<script type="text/javascript" src="{/literal}{$base_dir_ssl|escape:'htmlall':'UTF-8'}{literal}modules/blocknewsplus/views/js/blocknewsplus.js"></script>
{/literal}
{/if}