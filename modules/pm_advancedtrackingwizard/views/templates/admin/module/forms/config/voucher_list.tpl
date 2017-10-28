{atw_startForm id="formConfigVoucherList"}

    <h2>{l s='Configuration for vouchers on Cart:' mod='pm_advancedtrackingwizard'}</h2>

    {atw_inputText
        obj=''
        key='spacer'
        label={l s='Spacer:' mod='pm_advancedtrackingwizard'}
        size='10px'
    }

    {module->_displaySubmit text="{l s='Insert' mod='pm_advancedtrackingwizard'}" name='submit_voucher_list'}

{atw_endForm id="formConfigVoucherList"}
