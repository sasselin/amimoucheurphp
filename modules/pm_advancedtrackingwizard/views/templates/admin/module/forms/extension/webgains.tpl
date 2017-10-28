{atw_startForm id="Webgains"}

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
        key='wg_pin'
        label={l s='Webgains PIN:' mod='pm_advancedtrackingwizard'}
        defaultvalue=$vars.wg_pin
    }

    {atw_inputText
        obj=''
        key='wg_prog_id'
        label={l s='Program ID:' mod='pm_advancedtrackingwizard'}
        defaultvalue=$vars.wg_prog_id
    }

    {atw_inputText
        obj=''
        key='wg_event_id'
        label={l s='Event ID:' mod='pm_advancedtrackingwizard'}
        defaultvalue=$vars.wg_event_id
    }

    {atw_select
        obj=''
        options=$vars.options.wg_lang
        key='wg_lang'
        label={l s='Transaction Lang' mod='pm_advancedtrackingwizard'}
        size='160px'
        defaultvalue=$vars.wg_lang
    }

    {atw_select
        obj=''
        options=$vars.options.wg_protocol
        key='wg_protocol'
        label={l s='Protocol' mod='pm_advancedtrackingwizard'}
        size='160px'
        defaultvalue=$vars.wg_protocol
    }

    {atw_inputActive
        obj=''
        key_active='active'
        key_db='active'
        label={l s='Activate tracker:' mod='pm_advancedtrackingwizard'}
        defaultvalue=$vars.active
    }

    {module->_displaySubmit text="{l s='Save' mod='pm_advancedtrackingwizard'}" name='submit'}

{atw_endForm id="Webgains"}