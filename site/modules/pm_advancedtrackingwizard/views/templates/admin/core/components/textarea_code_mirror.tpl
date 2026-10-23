{if isset($options.label) && $options.label|strlen > 0}
    <label>{$options.label|escape:'htmlall':'UTF-8'}</label>
{/if}

<div class="margin-form">
    <div class="dynamicTextarea" style="width:{$options.size|escape:'htmlall':'UTF-8'};">
        <textarea style="width:{$options.size|escape:'htmlall':'UTF-8'};height:150px;" rows="5" name="{$options.key|escape:'htmlall':'UTF-8'}" id="{$options.key|escape:'htmlall':'UTF-8'}">{$current_value|escape:'htmlall':'UTF-8'}</textarea>
    </div>
    {include file='./tips.tpl' options=$options}
    <br/>
</div>
{*
<script type="text/javascript">
    {literal}
    var editor{/literal}{$options.key|escape:'htmlall':'UTF-8'}{literal} = CodeMirror.fromTextArea(document.getElementById("{/literal}{$options.key|escape:'htmlall':'UTF-8'}{literal}"), {mode: "{/literal}{$options.mode|escape:'htmlall':'UTF-8'}{literal}"});
    {/literal}
</script>
*}
{include file='../clear.tpl'}