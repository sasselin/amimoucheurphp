{if !$returnAsScript}
    <script type="text/javascript">
        var oTable{$idDataTable|escape:'htmlall':'UTF-8'} = undefined;
        {literal}
        $(document).ready(function() {
        {/literal}
{/if}
            oTable{$idDataTable|escape:'htmlall':'UTF-8'} = $('#{$idDataTable|escape:'htmlall':'UTF-8'}').dataTable({
                "sDom": 'R<"H"lfr>t<"F"ip<',
                "bJQueryUI": true,
                "bStateSave": true,
                "sPaginationType": "full_numbers",
                "bDestory": true,
                "oLanguage": {
                    "sLengthMenu": "{l s='Display' mod='pm_advancedtrackingwizard'} _MENU_ {l s='records per page' mod='pm_advancedtrackingwizard'}",
                    "sZeroRecords": "{l s='Nothing found - sorry' mod='pm_advancedtrackingwizard'}",
                    "sInfo": "{l s='Showing' mod='pm_advancedtrackingwizard'} _START_ {l s='to' mod='pm_advancedtrackingwizard'} _END_ {l s='of' mod='pm_advancedtrackingwizard'} _TOTAL_ {l s='records' mod='pm_advancedtrackingwizard'}",
                    "sInfoEmpty": "{l s='Showing' mod='pm_advancedtrackingwizard'} 0 {l s='to' mod='pm_advancedtrackingwizard'} 0 {l s='of' mod='pm_advancedtrackingwizard'} 0 {l s='records' mod='pm_advancedtrackingwizard'}",
                    "sInfoFiltered": "({l s='filtered from' mod='pm_advancedtrackingwizard'} _MAX_ {l s='total records' mod='pm_advancedtrackingwizard'})",
                    "sPageNext": "{l s='Next' mod='pm_advancedtrackingwizard'}",
                    "sPagePrevious": "{l s='Previous' mod='pm_advancedtrackingwizard'}",
                    "sPageLast": "{l s='Last' mod='pm_advancedtrackingwizard'}",
                    "sPageFirst": "{l s='First' mod='pm_advancedtrackingwizard'}",
                    "sSearch": "{l s='Search' mod='pm_advancedtrackingwizard'}",
                    oPaginate: {
                        "sFirst":"{l s='First' mod='pm_advancedtrackingwizard'}",
                        "sPrevious": "{l s='Previous' mod='pm_advancedtrackingwizard'}",
                        "sNext": "{l s='Next' mod='pm_advancedtrackingwizard'}",
                        "sLast": "{l s='Last' mod='pm_advancedtrackingwizard'}"
{literal}
                    }
                }
            });
{/literal}
{if !$returnAsScript}
        {literal}
        });
        {/literal}
    </script>
{/if}