{if !empty($options.tips)}
    {if !empty($options.key_active)}
        {assign var='tips_key' value=$options.key_active}
    {else}
        {assign var='tips_key' value=$options.key}
    {/if}
    <img title="{$options.tips|atw_nofilter}" id="{$tips_key|escape:'htmlall':'UTF-8'}-tips" class="pm_tips" src="{$module_path|atw_nofilter}views/img/question.png" width="16px" height="16px" />
    <script type="text/javascript">initTips("#{$tips_key|escape:'htmlall':'UTF-8'}")</script>
{/if}