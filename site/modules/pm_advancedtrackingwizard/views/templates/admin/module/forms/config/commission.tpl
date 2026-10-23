{atw_startForm id="formConfigCommission"}

    <h2>{l s='Configuration for Commission:' mod='pm_advancedtrackingwizard'}</h2>

    {atw_inputText
        obj=''
        key='commission_amount'
        label={l s='Commission Amount (in %)' mod='pm_advancedtrackingwizard'}
    }

    {module->_displaySubmit text="{l s='Insert' mod='pm_advancedtrackingwizard'}" name='submit_commission'}

{atw_endForm id="formConfigCommission"}
