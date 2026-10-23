{atw_startForm id="Zanox"}

    <img src="{$module_path}views/img/extensions/{$internal_name}/logo.png">
    <div class="clear"></div>
    <br/>
    <input type="hidden" name="internal_name" value="{$internal_name}">
    <input type="hidden" name="postExtensionConfigurationForm" id="postWidgetConfigurationForm" value="{$internal_name}">
    {if $id_tracking}
        <input type="hidden" name="id_tracking" id="id_tracking" value="{$id_tracking|intval}" />
    {/if}

    <p>{l s='Please enter your Zanox ID (ex: 123456789A123456B1AB) for each type of page :' mod='pm_advancedtrackingwizard'}</p>

    {atw_inputText
        obj=''
        key='zanox_id_generic'
        label={l s='Generic :' mod='pm_advancedtrackingwizard'}
        defaultvalue=$vars.zanox_id_generic
    }

    {atw_inputText
        obj=''
        key='zanox_id_home'
        label={l s='Home :' mod='pm_advancedtrackingwizard'}
        defaultvalue=$vars.zanox_id_home
    }

    {atw_inputText
        obj=''
        key='zanox_id_category'
        label={l s='Category :' mod='pm_advancedtrackingwizard'}
        defaultvalue=$vars.zanox_id_category
    }

    {atw_inputText
        obj=''
        key='zanox_id_product'
        label={l s='Product :' mod='pm_advancedtrackingwizard'}
        defaultvalue=$vars.zanox_id_product
    }

    {atw_inputText
        obj=''
        key='zanox_id_basket'
        label={l s='Cart/Basket :' mod='pm_advancedtrackingwizard'}
        defaultvalue=$vars.zanox_id_basket
    }

    {atw_inputText
        obj=''
        key='zanox_id_checkout'
        label={l s='Checkout :' mod='pm_advancedtrackingwizard'}
        defaultvalue=$vars.zanox_id_checkout
    }

    {atw_inputActive
        obj=''
        key_active='active'
        key_db='active'
        label={l s='Activate tracker:' mod='pm_advancedtrackingwizard'}
        defaultvalue=$vars.active
    }

    {module->_displaySubmit text="{l s='Save' mod='pm_advancedtrackingwizard'}" name='submit'}

{atw_endForm id="Zanox"}