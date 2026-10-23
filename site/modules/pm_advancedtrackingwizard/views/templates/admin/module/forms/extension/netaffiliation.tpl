{atw_startForm id="Net Affiliation"}

    <img src="{$module_path}views/img/extensions/{$internal_name}/logo.png">
    <div class="clear"></div>
    <br/>
    <input type="hidden" name="internal_name" value="{$internal_name}">
    <input type="hidden" name="postExtensionConfigurationForm" id="postWidgetConfigurationForm" value="{$internal_name}">
    {if $id_tracking}
        <input type="hidden" name="id_tracking" id="id_tracking" value="{$id_tracking|intval}" />
    {/if}

    {module->_displaySubTitle text="{l s='Orders tracking configuration (leave empty to disable)' mod='pm_advancedtrackingwizard'}"}

    {atw_inputText
        obj=''
        key='netaffiliation_mclic'
        label={l s='Tracking ID (A1BC234567):' mod='pm_advancedtrackingwizard'}
        defaultvalue=$vars.netaffiliation_mclic
    }

    {module->_displaySubTitle text="{l s='Orders tracking configuration (leave empty to disable)' mod='pm_advancedtrackingwizard'}"}

    {atw_inputText
        obj=''
        key='netaffiliation_mclic'
        label={l s='Tracking ID (A1BC234567):' mod='pm_advancedtrackingwizard'}
        defaultvalue=$vars.netaffiliation_mclic
    }

    {module->_displaySubTitle text="{l s='PartnerTag Lead configuration (leave empty to disable)' mod='pm_advancedtrackingwizard'}"}

    {atw_inputText
        obj=''
        key='netaffiliation_lead_projectid'
        label={l s='Program ID (12345):' mod='pm_advancedtrackingwizard'}
        defaultvalue=$vars.netaffiliation_lead_projectid
    }

    {module->_displaySubTitle text="{l s='PartnerTag configuration (leave empty to disable)' mod='pm_advancedtrackingwizard'}"}

    {atw_inputText
        obj=''
        key='netaffiliation_projectid'
        label={l s='Program ID (12345):' mod='pm_advancedtrackingwizard'}
        defaultvalue=$vars.netaffiliation_projectid
    }

    <br />

    {atw_inputActive
        obj=''
        key_active='active'
        key_db='active'
        label={l s='Activate tracker:' mod='pm_advancedtrackingwizard'}
        defaultvalue=$vars.active
    }

    {module->_displaySubmit text="{l s='Save' mod='pm_advancedtrackingwizard'}" name='submit'}

{atw_endForm id="Net Affiliation"}