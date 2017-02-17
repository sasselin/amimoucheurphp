{*
* 2007-2014 PrestaShop
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
* @author    PrestaShop SA <contact@prestashop.com>
* @copyright 2007-2015 PrestaShop SA
* @license   http://addons.prestashop.com/en/content/12-terms-and-conditions-of-use
* International Registered Trademark & Property of PrestaShop SA
*}

{literal}
<script>
	gshopping_module_school = '{/literal}{$gshopping_module_school|intval}{literal}';
	ps_version = '{/literal}{$ps_version|intval}{literal}';
	multishop = '{/literal}{$multishop|intval}{literal}';
	debug_mode = '{/literal}{$debug_mode|intval}{literal}';
	current_id_tab = '{/literal}{$current_id_tab|intval}{literal}';
	admin_module_ajax_url = '{/literal}{$controller_url}{literal}';
	admin_module_controller = "{/literal}{$controller_name|escape:'htmlall':'UTF-8'}{literal}";
{/literal}
	ready_message = '{l s=' Now you are ready to GO' mod='gshopping' js=1}';
	next_message = '{l s=' Next' mod='gshopping' js=1}';
	prev_message = '{l s=' Back' mod='gshopping' js=1}';
	skip_message = '{l s=' Skip' mod='gshopping' js=1}';
	save_message = '{l s=' Save' mod='gshopping' js=1}';
	close_message = '{l s='Close' mod='gshopping' js=1}';
	active_message = '{l s='Active' mod='gshopping' js=1}';
	later_message = '{l s='Later' mod='gshopping' js=1}';
	delete_message = '{l s='Delete' mod='gshopping' js=1}';
	delete_rule_message = '{l s='Are you sure you want to delete this rule?' mod='gshopping' js=1}';

	records_msg = '{l s='Show' mod='gshopping' js=1}';
	zero_records_msg = '{l s='Nothing found' mod='gshopping' js=1}';
</script>

{if $ps_version == 0}
<div class="bootstrap">
	<!-- Beautiful header -->
	{include file="./header.tpl"}
{/if}
	<input type="hidden" value="{$country_default|intval}" id="id_country">
	<!-- Module content -->
	<div id="modulecontent" class="clearfix">
		<!-- Nav tabs -->
		<div class="col-lg-2">
			<div class="list-group">
				<a href="#documentation" class="list-group-item active" data-toggle="tab"><i class="icon-book"></i> {l s='Documentation' mod='gshopping'}</a>
				<a href="#conf" class="list-group-item" data-toggle="tab"><i class="icon-cogs" data-target="table-metas-1" ></i> {l s='Configuration' mod='gshopping'}</a>
				<a href="#export" class="list-group-item" data-toggle="tab"><i class="icon-upload" data-target="export"></i> {l s='Export' mod='gshopping'}</a>
				<a href="#faq" class="list-group-item" data-toggle="tab"><i class="icon-info-sign" data-target="table-metas-3"></i> {l s='FAQ' mod='gshopping'}</a>
				<a href="#contacts" class="contacts list-group-item" data-toggle="tab"><i class="icon-envelope"></i> {l s='Contact' mod='gshopping'}</a>
			</div>
			<div class="list-group">
				<a class="list-group-item"><i class="icon-info"></i> {l s='Version' mod='gshopping'} {$module_version|escape:'htmlall':'UTF-8'}</a>
			</div>
		</div>
		<!-- Tab panes -->
		<div class="tab-content col-lg-10">
			<div class="tab-pane active panel" id="documentation">
				{include file="./tabs/documentation.tpl"}
			</div>
			<div class="tab-pane panel" id="conf">
				{include file="./tabs/conf.tpl"}
			</div>
			<div class="tab-pane panel" id="export">
				{include file="./tabs/export.tpl"}
			</div>
			<div class="tab-pane panel" id="faq">
				{include file="./tabs/faq.tpl"}
			</div>
			<div class="tab-pane panel" id="cron">

			</div>
			{include file="./tabs/contact.tpl"}
		</div>
	</div>
{if $ps_version == 0}
	<!-- Manage translations -->
	{include file="./translations.tpl"}
</div>
{/if}
