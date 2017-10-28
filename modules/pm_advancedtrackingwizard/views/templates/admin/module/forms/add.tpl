{atw_startForm id="formAddTracker"}

    {if $mode eq 'add'}
        {module->_displayTitle text="{l s='Add a new Tracker' mod='pm_advancedtrackingwizard'}"}
    {else}
        {module->_displayTitle text="{l s='Update Tracker' mod='pm_advancedtrackingwizard'}"}
    {/if}

    {if $mode eq 'update'}
        <input type="hidden" name="id_tracking" id="id_tracking" value="{$obj->id|intval}" />
    {/if}

    {atw_inputText
        obj=$obj
        key='internal_name'
        label={l s='Internal name' mod='pm_advancedtrackingwizard'}
        tips={l s='Internal Name for your tracker' mod='pm_advancedtrackingwizard'}
        size='150px'
    }

    <div class="clear"></div>
    <br />

    <div class="custom">
        {atw_select
            obj=$obj
            options=$option
            key='page'
            label={l s='Page' mod='pm_advancedtrackingwizard'}
            tips={l s='Select your page' mod='pm_advancedtrackingwizard'}
            onchange='showPageRelatedItems($(this));'
            size='250px'
        }
        <div class="category_picker">
            {atw_ajaxSelectMultiple
                selectedoptions=$categories
                key='categories'
                label={l s='Categories (Leave empty to include All Categories.)' mod='pm_advancedtrackingwizard'}
                remoteurl="{$base_config_url|atw_nofilter}&getCategory=1"
                tips={l s='Restriction on selected categories. Leave empty to include them all.' mod='pm_advancedtrackingwizard'}
            }
        </div>

        <div class="product_picker">
            {atw_ajaxSelectMultiple
                selectedoptions=$products
                key='products'
                label={l s='Products (Leave empty to include All products.)' mod='pm_advancedtrackingwizard'}
                remoteurl="{$base_config_url|atw_nofilter}&getProduct=1t"
                tips={l s='Restriction on selected products. Leave empty to include them all.' mod='pm_advancedtrackingwizard'}
            }
        </div>

        <script type="text/javascript">
            $(document).ready($(".category_picker").hide("fast"));
            $(document).ready($(".product_picker").hide("fast"));
        </script>

        {if $mode eq 'update'}
            <script type="text/javascript">showPageRelatedItems($("#page"));</script>
        {/if}

        {module->_showInfo text="{l s='How To: You must set your mouse cursor at the exact position where you want your variable to be placed. Once your pointer is set, click one of the variable buttons on the right side. To remove a button, place your cursor after it and hit delete on your keyboard. You can use "Ctrl Z" to go back through your modifications.' mod='pm_advancedtrackingwizard'}"}

        <label style="width:90%">{l s='Enter your tracking code below.' mod='pm_advancedtrackingwizard'}</label>
        <br />
        <div class="dynamicTextarea">
            <textarea name="tracking_code" id="tracking_code" cols="100" rows="30">{if $mode eq 'update'}{$obj->tracking_code}{/if}</textarea>
            <br />
            <script type="text/javascript">
                {literal}
                var currentCodeMirror = CodeMirror.fromTextArea(document.getElementById("tracking_code"), {mode: "text/html", tabMode: "indent"});
                {/literal}
            </script>
        </div>
        <div id="codeEditButton" class="codeEditButton">
            {foreach from=$tracking_group_vars_label key=keys item=value}
                <div class="button_group button_group_{$keys}">
                    <div class="clear"></div>
                    <p><b>{$value}:</b></p>
                    {foreach from=$tracking_vars_label[$keys] key=key item=button}
                        <span class="button_to_add button_{$key}">
                        {if $key|in_array:$var_to_config}
                                {atw_button
                                    text=$button
                                    href="javascript:void(0);"
                                    onclick="openDialogIframe('{$base_config_url|atw_nofilter}&pm_load_function=displayFormConfig&var={$key}', 300, 280);"
                                    icon_class='ui-icon ui-icon-gear'
                                    class="tracker_add_button_conf tracker_add_button_{$key}"
                                 }
                            {else}
                                {atw_button
                                    text=$button
                                    href="javascript:void(0);"
                                    onclick="addVarToTracker('{$key}', '{$button}');"
                                    class="tracker_add_button tracker_add_button_{$key}"
                                 }
                            {/if}
                        </span>
                    {/foreach}
                </div>
            {/foreach}
        </div>
        <div class="clear"></div>
        <br />
    </div>

    {atw_inputActive
        obj=$obj
        key_active='active_tracking'
        key_db='active'
        label={l s='Activate Tracker' mod='pm_advancedtrackingwizard'}
    }

    {module->_displaySubmit text="{l s='Save' mod='pm_advancedtrackingwizard'}" name='submit_tracker'}

{atw_endForm id="formAddTracker"}