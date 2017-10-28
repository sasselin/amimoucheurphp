{atw_startForm id="formConfigCurrency"}

    <h2>{l s='Configuration for Currency:' mod='pm_advancedtrackingwizard'}</h2>

    {atw_select
        obj=''
        options=$options
        key='currency'
    }

    {module->_displaySubmit text="{l s='Insert' mod='pm_advancedtrackingwizard'}" name='submit_currency'}

{atw_endForm id="formConfigCurrency"}
