<label>{$options.label|escape:'htmlall':'UTF-8'}</label>
<div class="margin-form">
    {assign var=custom_label value=false}
    {assign var=on_label value={l s='Yes' mod='pm_advancedtrackingwizard'}}
    {assign var=off_label value={l s='No' mod='pm_advancedtrackingwizard'}}
    {if !empty($options.on_label)}
        {assign var=custom_label value=true}
        {assign var=on_label value=$options.on_label}
    {/if}
    {if !empty($options.off_label)}
        {assign var=custom_label value=true}
        {assign var=off_label value=$options.off_label}
    {/if}
    {if !$custom_label}
        <label class="t" for="{$options.key_active|escape:'htmlall':'UTF-8'}_on" style="float:left;">
            <img src="../img/admin/enabled.gif" alt="{$on_label|escape:'htmlall':'UTF-8'}" title="{$on_label|escape:'htmlall':'UTF-8'}" />
        </label>
    {else}
        <label class="t" for="{$options.key_active|escape:'htmlall':'UTF-8'}_on" style="float:left;"></label>
    {/if}
    <input type="radio" name="{$options.key_active|escape:'htmlall':'UTF-8'}" id="{$options.key_active|escape:'htmlall':'UTF-8'}_on" {if !empty($options.onclick)} onclick="{$options.onclick|atw_nofilter}" {/if} value="1" {$selected_on|escape:'htmlall':'UTF-8'} style="float:left;" />
    <label class="t" for="{$options.key_active|escape:'htmlall':'UTF-8'}_on" style="float:left;">{$on_label|escape:'htmlall':'UTF-8'}</label>

    {if !$custom_label}
        <label class="t" for="{$options.key_active|escape:'htmlall':'UTF-8'}_off" style="float:left;">
            <img src="../img/admin/disabled.gif" alt="{$off_label|escape:'htmlall':'UTF-8'}" title="{$off_label|escape:'htmlall':'UTF-8'}" style="margin-left: 10px;" />
        </label>
    {else}
        <label class="t" for="{$options.key_active|escape:'htmlall':'UTF-8'}_off" style="float:left;"></label>
    {/if}
    <input type="radio" name="{$options.key_active|escape:'htmlall':'UTF-8'}" id="{$options.key_active|escape:'htmlall':'UTF-8'}_off" {if !empty($options.onclick)} onclick="{$options.onclick|atw_nofilter}" {/if} value="0" {$selected_off|escape:'htmlall':'UTF-8'} style="float:left;" />
    <label class="t" for="{$options.key_active|escape:'htmlall':'UTF-8'}_off" style="float:left;"> {$off_label|escape:'htmlall':'UTF-8'}</label>

    {include file='./tips.tpl' options=$options}
    {include file='../clear.tpl'}
</div>