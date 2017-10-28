{atw_startForm id="formConfigProductCart"}

    <h2>{l s='Configuration for price of products on Cart:' mod='pm_advancedtrackingwizard'}</h2>

    {atw_inputText
        obj=''
        key='spacer'
        label={l s='Spacer:' mod='pm_advancedtrackingwizard'}
        size='10px'
    }

    {atw_inputActive
        obj=false
        key_active='taxes'
        key_db='taxes'
        label={l s='Include Taxes:' mod='pm_advancedtrackingwizard'}
    }

    {module->_displaySubmit text="{l s='Insert' mod='pm_advancedtrackingwizard'}" name='submit_price_product_cart'}

{atw_endForm id="formConfigProductCart"}
