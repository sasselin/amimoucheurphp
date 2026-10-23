<div id="displayTrackingTable" rel="{$base_config_url|atw_nofilter}&pm_load_function=displayTrackingTable">
  <table id="trackingTable" cellspacing="0" cellpadding="0" class="display"  style="width:100%;">
      <thead>
          <tr>
            <th width="50">{l s='ID' mod='pm_advancedtrackingwizard'}</th>
            <th style="width:auto">{l s='Tracker' mod='pm_advancedtrackingwizard'}</th>
            <th style="text-align:center;">{l s='Page' mod='pm_advancedtrackingwizard'}</th>
            <th width="50" style="text-align:center;">{l s='Active' mod='pm_advancedtrackingwizard'}</th>
            <th width="60" style="text-align:center;">{l s='Edit' mod='pm_advancedtrackingwizard'}</th>
            <th width="60" style="text-align:center;">{l s='Delete' mod='pm_advancedtrackingwizard'}</th>
          </tr>
      </thead>
      <tbody>
            {if $trackers|is_array && $trackers|count}
                {foreach from=$trackers item=tracker}
                  <tr>
                    <td width="50">
                      {$tracker.id_tracking|intval}
                    </td>
                    <td style="width:auto">
                      {if isset($tracker.object->displayName) && !empty($tracker.object->displayName)}
                        {$tracker.object->displayName|atw_nofilter}
                      {else}
                        {$tracker.internal_name|atw_nofilter}
                      {/if}
                    </td>
                    <td style="text-align:center;">
                      {$tracker.page|atw_nofilter}
                    </td>
                    <td style="text-align:center;">
                      <span id="spanActiveTracking{$tracker.id_tracking|intval}" style="display:none">{$tracker.active}</span>
                      <a href="{$base_config_url|atw_nofilter}&activeTracking=1&id_tracking={$tracker.id_tracking|intval}'" class="ajax_script_load">
                        <img  title="{if $tracker.active}{l s='Active' mod='pm_advancedtrackingwizard'}{else}{l s='Inactive' mod='pm_advancedtrackingwizard'}{/if}"
                              alt="{if $tracker.active}{l s='Active' mod='pm_advancedtrackingwizard'}{else}{l s='Inactive' mod='pm_advancedtrackingwizard'}{/if}"
                              src="{$module_path|atw_nofilter}views/img/module_{if $tracker.active}install{else}disabled{/if}.png"
                              id="imgActiveTracking{$tracker.id_tracking|intval}"/>
                      </a>
                    </td>
                    <td style="text-align:center;">
                      {atw_button
                        href="{$base_config_url|atw_nofilter}&pm_load_function=displayFormAddTracker&alter=1&id_tracking={$tracker.id_tracking|intval}&is_ext={$tracker.is_extension|intval}"
                        icon_class='ui-icon ui-icon-pencil'
                        class='open_on_dialog_iframe'
                        rel="{if $tracker.is_extension|intval}600_500{else}980_735_1{/if}"
                      }
                    </td>
                    <td style="text-align:center;">

                      {atw_button
                        href="{$base_config_url|atw_nofilter}&deleteTracking=1&id_tracking={$tracker.id_tracking|intval}"
                        icon_class='ui-icon ui-icon-trash'
                        class='ajax_script_load pm_confirm'
                        title="{l s='Do you really want to delete this item ?' js=1 mod='pm_advancedtrackingwizard'}"
                      }
                    </td>
                  </tr>
                {/foreach}
            {/if}
      </tbody>
  </table>
</div>

{atw_initDataTable id_table='trackingTable'}
