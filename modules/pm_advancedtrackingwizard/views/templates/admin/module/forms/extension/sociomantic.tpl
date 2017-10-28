{atw_startForm id="Sociomantic"}

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
        key='sm_adpan_id'
        label={l s='adpanID:' mod='pm_advancedtrackingwizard'}
        defaultvalue=$vars.sm_adpan_id
    }

    {atw_inputActive
        obj=''
        key_active='active'
        key_db='active'
        label={l s='Activate tracker:' mod='pm_advancedtrackingwizard'}
        defaultvalue=$vars.active
    }

    {module->_displaySubmit text="{l s='Save' mod='pm_advancedtrackingwizard'}" name='submit'}

{atw_endForm id="Sociomantic"}