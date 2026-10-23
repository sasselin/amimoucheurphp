{*
* 2007-2017 PrestaShop
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
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2017 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<!-- Module content -->
<div id="modulecontent" class="clearfix">
    <!-- Nav tabs -->
    <div class="col-lg-2">
        <div class="list-group">
            <a href="#configuration" class="list-group-item active" data-target="configuration" data-toggle="tab"><i class="fa fa-cogs"></i> {l s='Configuration' mod='pscookiebanner'}</a>
            {* <a href="#customization" class="list-group-item" data-target="customization" data-toggle="tab"><i class="fa fa-css3"></i> {l s='Customization' mod='pscookiebanner'}</a> *}
             {if ($apifaq != '')}<a href="#help" class="list-group-item" data-target="help" data-toggle="tab"><i class="fa fa-question-circle"></i> {l s='Help' mod='pscookiebanner'}</a>{/if}
        </div>
        <div class="list-group">
            <a id="cb-preview" class="cb-preview list-group-item"><i class="icon-eye"></i> {l s='Enable Preview mode' mod='pscookiebanner'}</a>
            <a class="list-group-item"><i class="icon-info"></i> {l s='Version' mod='pscookiebanner'} {$module_version|escape:'htmlall':'UTF-8'}</a>
        </div>
    </div>
    <div class="" id="configuration">
        {include file="./tabs/configuration.tpl"}
    </div>
    {* <div class="hidden" id="customization">
        {include file="./tabs/customization.tpl"}
    </div> *}
    <div class="hidden" id="help">
        {if ($apifaq != '')}
        {include file="./tabs/help.tpl"}
        {/if}
    </div>
</div>

{literal}
<script type="text/javascript">
    var id_lang = "{/literal}{$id_lang|escape:'htmlall':'UTF-8'}{literal}";
    var baseUrl = "{/literal}{$baseUrl|escape:'htmlall':'UTF-8'}{literal}";
</script>
{/literal}

