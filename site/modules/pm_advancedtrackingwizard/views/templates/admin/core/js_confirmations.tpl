{if isset($js_confirmations) && $js_confirmations|is_array && $js_confirmations|sizeof}
	{if $include_script_tag}
		<script type="text/javascript">
	{/if}
    {foreach from=$js_confirmations item=js_confirmation}
	    parent.parent.show_info({$js_confirmation|json_encode});
    {/foreach}
	{if $include_script_tag}
		</script>
	{/if}
{/if}