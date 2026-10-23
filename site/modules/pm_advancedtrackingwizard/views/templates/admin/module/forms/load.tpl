{atw_startForm id="formCreateTracker" target=''}

    {module->_displayTitle text="{l s='Load a tracker:' mod='pm_advancedtrackingwizard'}"}

    {atw_select
        obj=false
        label={l s='Tracking:' mod='pm_advancedtrackingwizard'}
        key='load'
        options=$load
        size='250px'
        onchange='showPageRelatedItems($(this));'
    }

    {module->_displaySubmit text="{l s='Next' mod='pm_advancedtrackingwizard'}" name='load_tracker'}

{atw_endForm id="formCreateTracker"}