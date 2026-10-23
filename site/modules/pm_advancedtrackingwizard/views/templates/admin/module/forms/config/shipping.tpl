{atw_startForm id="formConfigShipping"}

    <h2>{l s='Configuration for Shipping:' mod='pm_advancedtrackingwizard'}</h2>

    {atw_inputActive
        obj=false
        key_active='taxe'
        key_db='taxes'
        label={l s='Include Taxes:' mod='pm_advancedtrackingwizard'}
    }

    <div class="clear"></div>

    {module->_displaySubmit text="{l s='Insert' mod='pm_advancedtrackingwizard'}" name='submit_shipping'}

{atw_endForm id="formConfigShipping"}
