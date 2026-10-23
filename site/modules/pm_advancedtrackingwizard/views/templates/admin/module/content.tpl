{$css_js_assets|atw_nofilter}

<div id="pm_backoffice_wrapper" class="pm_bo_ps_{$ps_major_version|escape:'htmlall':'UTF-8'}">
    {module->_displayTitle text="{$module_display_name}"}

    {if $module_is_up_to_date}
        {if $context_is_shop}
            {$rating_invite|atw_nofilter}

            {$parent_content|atw_nofilter}

            {atw_button
                text={l s='Create a Tracker' mod='pm_advancedtrackingwizard'}
                href="{$base_config_url|atw_nofilter}&pm_load_function=displayFormAddTracker"
                icon_class='ui-icon ui-icon-circle-plus'
                class='open_on_dialog_iframe'
                rel='980_850_1'}

            {atw_button
                text={l s='Load a Tracker' mod='pm_advancedtrackingwizard'}
                href="{$base_config_url|atw_nofilter}&pm_load_function=displayFormCreateTracker"
                icon_class='ui-icon ui-icon-circle-plus'
                class='open_on_dialog_iframe'
                rel='600_500'}

            {$tracking_table|atw_nofilter}
        {else}
            {module->_showWarning text="{l s='You must select a specific shop in order to continue' mod='pm_advancedtrackingwizard'}"}
        {/if}
    {else}
        {module->_showWarning text="
            <p>{l s='We have detected that you installed a new version of the module on your shop' mod='pm_advancedtrackingwizard'}</p>
            <p style=\"text-align: center\">
                <a href=\"{$base_config_url|atw_nofilter}&makeUpdate=1\" class=\"button\">
                    {l s='Please click here in order to finish the installation process' mod='pm_advancedtrackingwizard'}
                </a>
            </p>
        "}
    {/if}

    {module->_displaySupport}
</div>