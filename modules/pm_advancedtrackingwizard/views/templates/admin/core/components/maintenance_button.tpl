{strip}
<a href="{$base_config_url|atw_nofilter}&activeMaintenance=1" title="Maintenance" class="ajax_script_load ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only{if $maintenance_enabled} maintenance-enabled{/if}" id="buttonMaintenance" style="padding-right:5px;">
	<span id="pmImgMaintenance" class="ui-icon ui-icon-{if $maintenance_enabled}locked{else}unlocked{/if}" style="float: left; margin-right: .3em;"></span>
	{if $maintenance_enabled}
		{l s='Disable maintenance mode' mod='pm_advancedtrackingwizard'}
	{else}
		{l s='Enable maintenance mode' mod='pm_advancedtrackingwizard'}
	{/if}
</a>
{/strip}