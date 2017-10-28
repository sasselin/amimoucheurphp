{atw_startForm id="formConfigProductPrice"}

    <h2>{l s='Configuration for price of product:' mod='pm_advancedtrackingwizard'}</h2>

    {atw_inputActive
        obj=false
        key_active='taxes'
        key_db='taxes'
        label={l s='Include Taxes:' mod='pm_advancedtrackingwizard'}
    }

    {atw_inputActive
        obj=false
        key_active='eco_taxes'
        key_db='eco_taxes'
        label={l s='Include Eco-Taxes:' mod='pm_advancedtrackingwizard'}
    }

    {module->_displaySubmit text="{l s='Insert' mod='pm_advancedtrackingwizard'}" name='submit_product_price'}

{atw_endForm id="formConfigProductPrice"}
