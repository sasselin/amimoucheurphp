{atw_startForm id="formConfigTotal"}

    <h2>{l s='Configuration for Total:' mod='pm_advancedtrackingwizard'}</h2>

    {atw_inputActive
        obj=''
        key_active='taxe'
        key_db='taxes'
        label={l s='Include Taxes:' mod='pm_advancedtrackingwizard'}
    }

    {atw_inputActive
        obj=''
        key_active='discount'
        key_db='discounts'
        label={l s='Include Discount:' mod='pm_advancedtrackingwizard'}
    }

    {atw_inputActive
        obj=''
        key_active='shipping'
        key_db='shippings'
        label={l s='Include Shipping:' mod='pm_advancedtrackingwizard'}
    }

    {module->_displaySubmit text="{l s='Insert' mod='pm_advancedtrackingwizard'}" name='submit_total'}

{atw_endForm id="formConfigTotal"}
