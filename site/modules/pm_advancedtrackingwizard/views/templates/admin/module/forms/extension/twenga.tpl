{atw_startForm id="Twenga"}

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
        key='twenga_hashkey'
        label={l s='Hash key:' mod='pm_advancedtrackingwizard'}
        defaultvalue=$vars.twenga_hashkey
    }

    {atw_inputText
        obj=''
        key='twenga_tld'
        label={l s='TLD (enter fr if twenga.fr or com if twenga.com)' mod='pm_advancedtrackingwizard'}
        defaultvalue=$vars.twenga_tld
    }

    {atw_inputActive
        obj=''
        key_active='active'
        key_db='active'
        label={l s='Activate tracker:' mod='pm_advancedtrackingwizard'}
        defaultvalue=$vars.active
    }

    {module->_displaySubmit text="{l s='Save' mod='pm_advancedtrackingwizard'}" name='submit'}

{atw_endForm id="Twenga"}