{atw_startForm id="kelkoo"}

    <img src="{$module_path}views/img/extensions/{$internal_name}/logo.png">
    <div class="clear"></div>
    <br/>
    <input type="hidden" name="internal_name" value="{$internal_name}">
    <input type="hidden" name="postExtensionConfigurationForm" id="postWidgetConfigurationForm" value="{$internal_name}">
    {if $id_tracking}
        <input type="hidden" name="id_tracking" id="id_tracking" value="{$id_tracking|intval}" />
    {/if}

    {atw_inputText
        obj=''
        key='klk_org_id'
        label={l s='Your personnal ID:' mod='pm_advancedtrackingwizard'}
        defaultvalue=$vars.org_id
    }

    {atw_inputText
        obj=''
        key='klk_event'
        label={l s='Event:' mod='pm_advancedtrackingwizard'}
        defaultvalue=$vars.event
    }
    {atw_inputActive
        obj=''
        key_active='active'
        key_db='active'
        label={l s='Activate tracker:' mod='pm_advancedtrackingwizard'}
        defaultvalue=$vars.active
    }

    {module->_displaySubmit text="{l s='Save' mod='pm_advancedtrackingwizard'}" name='submit'}

{atw_endForm id="kelkoo"}