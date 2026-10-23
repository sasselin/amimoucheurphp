{*
* 2007-2017 PrestaShop
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
* @author    PrestaShop SA <contact@prestashop.com>
* @copyright 2007-2017 PrestaShop SA
* @license   http://addons.prestashop.com/en/content/12-terms-and-conditions-of-use
* International Registered Trademark & Property of PrestaShop SA
*}
<div id="faq" class="panel col-lg-10 right-panel">
    <div class="panel-heading"><i class="icon-question"></i> {l s='FAQ' mod='pscookiebanner'}</div>
    {foreach from=$apifaq item=categorie name='faq'}
        <span class="faq-h1">{$categorie->title|escape:'htmlall':'UTF-8'}</span>
        <ul>
            {foreach from=$categorie->blocks item=QandA}
                {if !empty($QandA->question)}
                    <li>
                        <span class="faq-h2"><i class="icon-info-circle"></i> {$QandA->question|escape:'htmlall':'UTF-8'}</span>
                        <p class="faq-text hide">
                            {$QandA->answer|escape:'htmlall':'UTF-8'|replace:"\n":"<br />"}
                        </p>
                    </li>
                {/if}
            {/foreach}
        </ul>
        {if !$smarty.foreach.faq.last}<hr/>{/if}
    {/foreach}
    <span class="faq-h1">{l s='Need some help ?' mod='pscookiebanner'}</span>
    {l s='You can find the documentation of this module here ' mod='pscookiebanner'}
    <a class="btn btn-default" href="{$doc|escape:'htmlall':'UTF-8'}" target="_blank"><i class="icon-book" aria-hidden="true"></i>{l s='Documentation' mod='pscookiebanner'}</a>
    <p>{l s='You can also contact us from ' mod='pscookiebanner'}<a href="http://addons.prestashop.com/contact-form.php" target="_blank">{l s='PrestaShop Addons' mod='pscookiebanner'}</a></p>
    </div>
</div>
