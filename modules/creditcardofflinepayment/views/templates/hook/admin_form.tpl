{**
* Credit Card Offline Payment Module.
*
* Allows currently running stores to accept card information through their online store.
* Card number is verified and the information is stored masked at database.
* It can then be decrypted, together with the information received by email,
* at a later time for charges through an existing gateway (ie. a creditcard machine).
*
* NOTICE OF LICENSE
*
* This product is licensed for one customer to use on one installation (test stores and multishop included).
* Site developer has the right to modify this module to suit their needs, but can not redistribute the module in
* whole or in part. Any other use of this module constitues a violation of the user agreement.
*
* DISCLAIMER
*
* NO WARRANTIES OF DATA SAFETY OR MODULE SECURITY
* ARE EXPRESSED OR IMPLIED. USE THIS MODULE IN ACCORDANCE
* WITH YOUR MERCHANT AGREEMENT, KNOWING THAT VIOLATIONS OF
* PCI COMPLIANCY OR A DATA BREACH CAN COST THOUSANDS OF DOLLARS
* IN FINES AND DAMAGE A STORES REPUTATION. USE AT YOUR OWN RISK.
*
*  @author    idnovate.com <info@idnovate.com>
*  @copyright 2016 idnovate.com
*  @license   See above
*}

<style type="text/css">
	.nobootstrap {
		min-width: 0 !important;
		padding: 100px 30px 0 !important;
	}
	.nobootstrap .margin-form {
		font-size: 0.9em !important;
	}

	.company {
		border: 1px solid black;
		background-color: #2A2A2A;
		color: white;
		overflow: hidden;
		padding: 20px;
		margin: 15px 0;
	}
	.company a{
		color: white;
		font-weight: bold;
	}
	.company ul {
		margin: 6px 0 12px;
		padding-left: 40px;
		list-style-type: disc;
	}
	.company ul li {
		color: #FFF;
	}
	.company .logo {
		padding-bottom: 10px;
	}
	#creditcardofflinepayment select {
		height: 23px;
		vertical-align: middle;
	}
	#creditcardofflinepayment label {
		clear: both;
	}
</style>

<script type="text/javascript">
{literal}
function showWM(value) {
	if (value == '1')
	{
		document.getElementById('bymail').style.display = '';
		document.getElementById('mail').style.display = '';
		document.getElementById('maillabel').style.display = '';
		document.getElementById('indatabase').style.display = 'none';
	}
	else
	{
		document.getElementById('bymail').style.display = 'none';
		document.getElementById('mail').style.display = 'none';
		document.getElementById('maillabel').style.display = 'none';
		document.getElementById('indatabase').style.display = '';
	}
}
{/literal}
</script>
<h2>{$displayName|escape:'htmlall':'UTF-8'} - {l s='Configuration' mod='creditcardofflinepayment'}</h2>

<div class="company">
	<div class="logo">
		<img src="{$cc_path|escape:'htmlall':'UTF-8'}views/img/logo_idnovate.png" title="idnovate.com" alt="idnovate.com" />
	</div>
	<div class="content">
		{l s='We offer you free assistance to install and set up the module. If you have any problem you can contact us at' mod='creditcardofflinepayment'} <a href="http://addons.prestashop.com/contact-community.php?id_product=4647" target="_blank" title="{l s='Contact' mod='creditcardofflinepayment'}">http://addons.prestashop.com/contact-community.php?id_product=4647</a>
	</div>
</div>

{if isset($errors) && $errors|@count > 0}
<div class="bootstrap">
	<div class="module_confirmation alert error alert-warning">
		<strong>{l s='There are errors:' mod='creditcardofflinepayment'}</strong>
		<ol>
			{foreach from=$errors item=error}
				<li>{$error|escape:'htmlall':'UTF-8'}</li>
			{/foreach}
		</ol>
	</div>
</div>
{/if}
{if isset($success) && $success}
	<div class="bootstrap">
		<div class="module_confirmation conf confirm alert alert-success">{l s='Settings updated' mod='creditcardofflinepayment'}</div>
	</div>
{/if}

<div style="clear: both"></div>

<form action="{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}" method="post" class="half_form" id="creditcardofflinepayment">

	<fieldset>
		<legend>
			<img src="../img/admin/edit.gif" />
			{l s='Module configuration' mod='creditcardofflinepayment'}
		</legend>

		<label>{l s='Minimum amount to enable this payment method' mod='creditcardofflinepayment'}:</label>
		<div id="minimum_amount" class="margin-form">
			<input type="text" name="minimum_amount" value="{$minimum_amount|escape:'htmlall':'UTF-8'}" />
			<select name="moneda">
				<option value=""></option>
				{foreach $currencies as $currency}
				<option value="{$currency.iso_code_num|escape:'htmlall':'UTF-8'}" {if isset($moneda) and $moneda==$currency.iso_code_num}selected{/if}>{$currency.name|escape:'htmlall':'UTF-8'}</option>
				{/foreach}
			</select>
			<br />
			<span>{l s='Minimum amount includes taxes, discounts and shipping cost' mod='creditcardofflinepayment'}</span><br />
			<span>{l s='This payment method will be only shown is order amount is at least as amount introduced.' mod='creditcardofflinepayment'}</span><br />
			<span>{l s='If you set 0, it will be shown always' mod='creditcardofflinepayment'}</span>
		</div>

		<label>{l s='Working mode' mod='creditcardofflinepayment'}:</label>
		<div class="margin-form">
			<input type="radio" onchange="javascript:showWM('1')" name="working_mode" value="1" {if $working_mode==1}checked="checked"{/if} />
			<label class="t">
				{l s='Send info by email' mod='creditcardofflinepayment'}
			</label>
			<input type="radio" onchange="javascript:showWM('2')" name="working_mode" value="2" {if $working_mode==2}checked="checked"{/if} />
			<label class="t">
				{l s='Store in database' mod='creditcardofflinepayment'}
			</label>
			<br />
			<span>{l s='- If you set "Send info by email", part of the card number will be stored in database and the other half sent by email. You will have to match information received from email with the one from Backoffice.' mod='creditcardofflinepayment'}</span>
			<br />
			<br />
			<span>{l s='- If you set "Store in database", whole card number will be stored in database and you will be able to check it from BackOffice. You can delete the card info afterwards.' mod='creditcardofflinepayment'}</span>
			<br />
			<br />
			<div class="bootstrap"><div class="alert alert-warning">{l s='CAUTION! The information will be stored at database. To accomplish with PCI/DSS your server must be secure. Please ask your hosting provider. You are responsible of any act.' mod='creditcardofflinepayment'}<br /><strong>{l s='Module will work correctly anyway.' mod='creditcardofflinepayment'}</strong></div></div>
			<br />
			<span><strong>{l s='How you will see the card info:' mod='creditcardofflinepayment'}</strong></span>
			<br /><br />
			<div id="bymail"><img src="{$cc_path|escape:'htmlall':'UTF-8'}views/img/bymail.png" /></div>
			<div id="indatabase"><img src="{$cc_path|escape:'htmlall':'UTF-8'}views/img/indatabase.png" /></div>
		</div>

		<label id="maillabel">{l s='Mail address' mod='creditcardofflinepayment'}:</label>
		<div id="mail" class="margin-form">
			<input type="text" name="admin_mail" value="{$admin_mail|escape:'htmlall':'UTF-8'}" size="35"/>
			<br />
			<span>{l s='Mail address where card information will be sent if you select "Send info by email" working mode' mod='creditcardofflinepayment'}</span>
		</div>

		<label>{l s='Order initial status' mod='creditcardofflinepayment'}:</label>
		<div class="margin-form">
			<table cellpadding="0" cellspacing="0" class="table">
				<thead>
					<tr>
						<th>{l s='Status name' mod='creditcardofflinepayment'}</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					{foreach from=$states item=state}
					<tr style="background-color: {$state.color|escape:'htmlall':'UTF-8'};">
						<td style="text-align:center"><input type="radio" name="id_os_initial" {if $state.id_order_state == $id_os_initial}checked{/if} value="{$state.id_order_state|escape:'htmlall':'UTF-8'}"/></td>
						<td style="color:{if Tools::getBrightness($state.color|escape:'htmlall':'UTF-8') < 128}white{else}#383838{/if}">{$state.name|escape:'htmlall':'UTF-8'}</td>
					</tr>
					{/foreach}
				</tbody>
			</table>
			<span>{l s='When a customer choose this payment method, the order will be left in this status.' mod='creditcardofflinepayment'}</span>
		</div>

		<label>{l s='Delete card info when the status change' mod='creditcardofflinepayment'}:</label>
		<div class="margin-form">
			<table cellpadding="0" cellspacing="0" class="table">
				<thead>
					<tr>
						<th>{l s='Status name' mod='creditcardofflinepayment'}</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					{foreach from=$states item=state}
					<tr style="background-color: {$state.color|escape:'htmlall':'UTF-8'};">
						<td style="text-align:center"><input type="checkbox" name="id_os_remove[]" {if in_array($state.id_order_state, $id_os_remove)}checked{/if} value="{$state.id_order_state|escape:'htmlall':'UTF-8'}"/></td>
						<td style="color:{if Tools::getBrightness($state.color|escape:'htmlall':'UTF-8') < 128}white{else}#383838{/if}">{$state.name|escape:'htmlall':'UTF-8'}</td>
					</tr>
					{/foreach}
				</tbody>
			</table>
			<span>{l s='Don\'t select any if you don\'t want to delete it automatically.' mod='creditcardofflinepayment'}</span>
		</div>

		<label>{l s='Debug mode' mod='creditcardofflinepayment'}:</label>
		<div id="mail" class="margin-form">
			<input type="radio" name="debugMode" value="1" {if isset($debugMode) && ($debugMode)}checked="checked"{/if} />
			<label class="t">
				<img title="{l s='Enabled' mod='creditcardofflinepayment'}" alt="{l s='Enabled' mod='creditcardofflinepayment'}" src="../img/admin/enabled.gif"/>
			</label>
			<input type="radio" name="debugMode" value="0" {if !isset($debugMode) || !$debugMode}checked="checked"{/if} />
			<label class="t">
				<img title="{l s='Disabled' mod='creditcardofflinepayment'}" alt="{l s='Disabled' mod='creditcardofflinepayment'}" src="../img/admin/disabled.gif"/>
			</label>
		</div>
	</fieldset>

	<div style="clear: both"></div>

	<br/>

	<fieldset>
		<legend>
			<img src="../img/admin/cog.gif" />
			{l s='Form configuration' mod='creditcardofflinepayment'}
		</legend>

		<label>{l s='Display left column?' mod='creditcardofflinepayment'}:</label>
		<div class="margin-form">
			<input type="radio" name="leftColumn" value="1" {if isset($leftColumn) && ($leftColumn)}checked="checked"{/if} />
			<label class="t">
				<img title="{l s='Enabled' mod='creditcardofflinepayment'}" alt="{l s='Enabled' mod='creditcardofflinepayment'}" src="../img/admin/enabled.gif"/>
			</label>
			<input type="radio" name="leftColumn" value="0" {if !isset($leftColumn) || !$leftColumn}checked="checked"{/if} />
			<label class="t">
				<img title="{l s='Disabled' mod='creditcardofflinepayment'}" alt="{l s='Disabled' mod='creditcardofflinepayment'}" src="../img/admin/disabled.gif"/>
			</label>
			<br />
			<span>{l s='Left column will not be displayed at payment form.' mod='creditcardofflinepayment'}</span>
		</div>

		<label>{l s='Display right column?' mod='creditcardofflinepayment'}:</label>
		<div class="margin-form">
			<input type="radio" name="rightColumn" value="1" {if isset($rightColumn) && ($rightColumn)}checked="checked"{/if} />
			<label class="t">
				<img title="{l s='Enabled' mod='creditcardofflinepayment'}" alt="{l s='Enabled' mod='creditcardofflinepayment'}" src="../img/admin/enabled.gif"/>
			</label>
			<input type="radio" name="rightColumn" value="0" {if !isset($rightColumn) || !$rightColumn}checked="checked"{/if} />
			<label class="t">
				<img title="{l s='Disabled' mod='creditcardofflinepayment'}" alt="{l s='Disabled' mod='creditcardofflinepayment'}" src="../img/admin/disabled.gif"/>
			</label>
			<br />
			<span>{l s='Right column will not be displayed at payment form.' mod='creditcardofflinepayment'}</span>
		</div>

		<label>{l s='Show card form at payments method' mod='creditcardofflinepayment'}:</label>
		<div class="margin-form">
			<input type="radio" name="show_form" value="1" {if $show_form}checked="checked"{/if} />
			<label class="t">
				<img title="{l s='Enabled' mod='creditcardofflinepayment'}" alt="{l s='Enabled' mod='creditcardofflinepayment'}" src="../img/admin/enabled.gif"/>
			</label>
			<input type="radio" name="show_form" value="0" {if !$show_form}checked="checked"{/if} />
			<label class="t">
				<img title="{l s='Disabled' mod='creditcardofflinepayment'}" alt="{l s='Disabled' mod='creditcardofflinepayment'}" src="../img/admin/disabled.gif"/>
			</label>
			<br />
			<span>{l s='Customers will have to enter the card information when they select payment method' mod='creditcardofflinepayment'}<br />{l s='Not compatible with module "Advanced EU Compliance"' mod='creditcardofflinepayment'}</span>
		</div>

		<label>{l s='Numbers of expiring years' mod='creditcardofflinepayment'}:</label>
		<div class="margin-form">
			<select name="years">
				{for $year=1 to 10}
					<option value="{$year|escape:'htmlall':'UTF-8'}" {if isset($years) && $years == $year}selected=selected{/if}>{$year|escape:'htmlall':'UTF-8'}</option>
				{/for}
			</select>
			<br />
			<span>{l s='Number of following years to display at expiry dropdown' mod='creditcardofflinepayment'}</span>
		</div>
	</fieldset>

	<br />

	<fieldset>
		<legend>
			<img src="../img/admin/prefs.gif" />
			{l s='Card configuration' mod='creditcardofflinepayment'}
		</legend>
		<label>{l s='Request for Card Holder Name?' mod='creditcardofflinepayment'}:</label>
		<div class="margin-form">
			<input type="radio" name="requireIssuerName" value="1" {if $requireIssuerName}checked="checked"{/if} />
			<label class="t">
				<img title="{l s='Enabled' mod='creditcardofflinepayment'}" alt="{l s='Enabled' mod='creditcardofflinepayment'}" src="../img/admin/enabled.gif"/>
			</label>
			<input type="radio" name="requireIssuerName" value="0" {if !$requireIssuerName}checked="checked"{/if} />
			<label class="t">
				<img title="{l s='Disabled' mod='creditcardofflinepayment'}" alt="{l s='Disabled' mod='creditcardofflinepayment'}" src="../img/admin/disabled.gif"/>
			</label>
			&nbsp;&nbsp;&nbsp;&nbsp;
			<input type="checkbox" name="requiredIssuerName" {if $requiredIssuerName}checked="checked"{/if} />
			<label class="t">
				{l s='Required' mod='creditcardofflinepayment'}
			</label>
			<br />
			<span>{l s='Customers must enter their name.' mod='creditcardofflinepayment'}</span>
		</div>

		<label>{l s='Request for ID Card/Passport?' mod='creditcardofflinepayment'}:</label>
		<div class="margin-form">
			<input type="radio" name="requireCedule" value="1" {if $requireCedule}checked="checked"{/if} />
			<label class="t">
				<img title="{l s='Enabled' mod='creditcardofflinepayment'}" alt="{l s='Enabled' mod='creditcardofflinepayment'}" src="../img/admin/enabled.gif"/>
			</label>
			<input type="radio" name="requireCedule" value="0" {if !$requireCedule}checked="checked"{/if} />
			<label class="t">
				<img title="{l s='Disabled' mod='creditcardofflinepayment'}" alt="{l s='Disabled' mod='creditcardofflinepayment'}" src="../img/admin/disabled.gif"/>
			</label>
			&nbsp;&nbsp;&nbsp;&nbsp;
			<input type="checkbox" name="requiredCedule" {if $requiredCedule}checked="checked"{/if} />
			<label class="t">
				{l s='Required' mod='creditcardofflinepayment'}
			</label>
			<br />
			<span>{l s='Customers must enter their identifity card.' mod='creditcardofflinepayment'}</span>
		</div>

		<label>{l s='Request for address?' mod='creditcardofflinepayment'}:</label>
		<div class="margin-form">
			<input type="radio" name="requireAddress" value="1" {if $requireAddress}checked="checked"{/if} />
			<label class="t">
				<img title="{l s='Enabled' mod='creditcardofflinepayment'}" alt="{l s='Enabled' mod='creditcardofflinepayment'}" src="../img/admin/enabled.gif"/>
			</label>
			<input type="radio" name="requireAddress" value="0" {if !$requireAddress}checked="checked"{/if} />
			<label class="t">
				<img title="{l s='Disabled' mod='creditcardofflinepayment'}" alt="{l s='Disabled' mod='creditcardofflinepayment'}" src="../img/admin/disabled.gif"/>
			</label>
			&nbsp;&nbsp;&nbsp;&nbsp;
			<input type="checkbox" name="requiredAddress" {if $requiredAddress}checked="checked"{/if} />
			<label class="t">
				{l s='Required' mod='creditcardofflinepayment'}
			</label>
			<br />
			<span>{l s='Customers should enter an address.' mod='creditcardofflinepayment'}</span>
		</div>

		<label>{l s='Request for zip code?' mod='creditcardofflinepayment'}:</label>
		<div class="margin-form">
			<input type="radio" name="requireZipCode" value="1" {if $requireZipCode}checked="checked"{/if} />
			<label class="t">
				<img title="{l s='Enabled' mod='creditcardofflinepayment'}" alt="{l s='Enabled' mod='creditcardofflinepayment'}" src="../img/admin/enabled.gif"/>
			</label>
			<input type="radio" name="requireZipCode" value="0" {if !$requireZipCode}checked="checked"{/if} />
			<label class="t">
				<img title="{l s='Disabled' mod='creditcardofflinepayment'}" alt="{l s='Disabled' mod='creditcardofflinepayment'}" src="../img/admin/disabled.gif"/>
			</label>
			&nbsp;&nbsp;&nbsp;&nbsp;
			<input type="checkbox" name="requiredZipCode" {if $requiredZipCode}checked="checked"{/if} />
			<label class="t">
				{l s='Required' mod='creditcardofflinepayment'}
			</label>
			<br />
			<span>{l s='Customer should enter a zip code.' mod='creditcardofflinepayment'}</span>
		</div>

		<label>{l s='Request for city?' mod='creditcardofflinepayment'}:</label>
		<div class="margin-form">
			<input type="radio" name="requireCity" value="1" {if $requireCity}checked="checked"{/if} />
			<label class="t">
				<img title="{l s='Enabled' mod='creditcardofflinepayment'}" alt="{l s='Enabled' mod='creditcardofflinepayment'}" src="../img/admin/enabled.gif"/>
			</label>
			<input type="radio" name="requireCity" value="0" {if !$requireCity}checked="checked"{/if} />
			<label class="t">
				<img title="{l s='Disabled' mod='creditcardofflinepayment'}" alt="{l s='Disabled' mod='creditcardofflinepayment'}" src="../img/admin/disabled.gif"/>
			</label>
			&nbsp;&nbsp;&nbsp;&nbsp;
			<input type="checkbox" name="requiredCity" {if $requiredCity}checked="checked"{/if} />
			<label class="t">
				{l s='Required' mod='creditcardofflinepayment'}
			</label>
			<br />
			<span>{l s='Customer should enter a city.' mod='creditcardofflinepayment'}</span>
		</div>

		<label>{l s='Request for state?' mod='creditcardofflinepayment'}:</label>
		<div class="margin-form">
			<input type="radio" name="requireState" value="1" {if $requireState}checked="checked"{/if} />
			<label class="t">
				<img title="{l s='Enabled' mod='creditcardofflinepayment'}" alt="{l s='Enabled' mod='creditcardofflinepayment'}" src="../img/admin/enabled.gif"/>
			</label>
			<input type="radio" name="requireState" value="0" {if !$requireState}checked="checked"{/if} />
			<label class="t">
				<img title="{l s='Disabled' mod='creditcardofflinepayment'}" alt="{l s='Disabled' mod='creditcardofflinepayment'}" src="../img/admin/disabled.gif"/>
			</label>
			&nbsp;&nbsp;&nbsp;&nbsp;
			<input type="checkbox" name="requiredState" {if $requiredState}checked="checked"{/if} />
			<label class="t">
				{l s='Required' mod='creditcardofflinepayment'}
			</label>
			<br />
			<span>{l s='Customer should enter a state.' mod='creditcardofflinepayment'}</span>
		</div>

		<label>{l s='Request for country?' mod='creditcardofflinepayment'}:</label>
		<div class="margin-form">
			<input type="radio" name="requireCountry" value="1" {if $requireCountry}checked="checked"{/if} />
			<label class="t">
				<img title="{l s='Enabled' mod='creditcardofflinepayment'}" alt="{l s='Enabled' mod='creditcardofflinepayment'}" src="../img/admin/enabled.gif"/>
			</label>
			<input type="radio" name="requireCountry" value="0" {if !$requireCountry}checked="checked"{/if} />
			<label class="t">
				<img title="{l s='Disabled' mod='creditcardofflinepayment'}" alt="{l s='Disabled' mod='creditcardofflinepayment'}" src="../img/admin/disabled.gif"/>
			</label>
			&nbsp;&nbsp;&nbsp;&nbsp;
			<input type="checkbox" name="requiredCountry" {if $requiredCountry}checked="checked"{/if} />
			<label class="t">
				{l s='Required' mod='creditcardofflinepayment'}
			</label>
			<br />
			<span>{l s='Customer should enter a country.' mod='creditcardofflinepayment'}</span>
		</div>

		<label>{l s='Request for card number?' mod='creditcardofflinepayment'}:</label>
		<div class="margin-form">
			<input type="radio" name="requireCardNumber" value="1" {if $requireCardNumber}checked="checked"{/if} />
			<label class="t">
				<img title="{l s='Enabled' mod='creditcardofflinepayment'}" alt="{l s='Enabled' mod='creditcardofflinepayment'}" src="../img/admin/enabled.gif"/>
			</label>
			<input type="radio" name="requireCardNumber" value="0" {if !$requireCardNumber}checked="checked"{/if} />
			<label class="t">
				<img title="{l s='Disabled' mod='creditcardofflinepayment'}" alt="{l s='Disabled' mod='creditcardofflinepayment'}" src="../img/admin/disabled.gif"/>
			</label>
			&nbsp;&nbsp;&nbsp;&nbsp;
			<input type="checkbox" name="requiredCardNumber" {if $requiredCardNumber}checked="checked"{/if} />
			<label class="t">
				{l s='Required' mod='creditcardofflinepayment'}
			</label>
			<br />
			<span>{l s='Customers should enter their card number.' mod='creditcardofflinepayment'}</span>
		</div>

		<label>{l s='Request CVC number?' mod='creditcardofflinepayment'}:</label>
		<div class="margin-form">
			<input type="radio" name="requireCVC" value="1" {if $requireCVC}checked="checked"{/if} />
			<label class="t">
				<img title="{l s='Enabled' mod='creditcardofflinepayment'}" alt="{l s='Enabled' mod='creditcardofflinepayment'}" src="../img/admin/enabled.gif"/>
			</label>
			<input type="radio" name="requireCVC" value="0" {if !$requireCVC}checked="checked"{/if} />
			<label class="t">
				<img title="{l s='Disabled' mod='creditcardofflinepayment'}" alt="{l s='Disabled' mod='creditcardofflinepayment'}" src="../img/admin/disabled.gif"/>
			</label>
			&nbsp;&nbsp;&nbsp;&nbsp;
			<input type="checkbox" name="requiredCVC" {if $requiredCVC}checked="checked"{/if} />
			<label class="t">
				{l s='Required' mod='creditcardofflinepayment'}
			</label>
			<br />
			<span>{l s='Customers should enter the card CVC number.' mod='creditcardofflinepayment'}</span>
		</div>

		<label>{l s='Request for card expiration date?' mod='creditcardofflinepayment'}:</label>
		<div class="margin-form">
			<input type="radio" name="requireExpiration" value="1" {if $requireExpiration}checked="checked"{/if} />
			<label class="t">
				<img title="{l s='Enabled' mod='creditcardofflinepayment'}" alt="{l s='Enabled' mod='creditcardofflinepayment'}" src="../img/admin/enabled.gif"/>
			</label>
			<input type="radio" name="requireExpiration" value="0" {if !$requireExpiration}checked="checked"{/if} />
			<label class="t">
				<img title="{l s='Disabled' mod='creditcardofflinepayment'}" alt="{l s='Disabled' mod='creditcardofflinepayment'}" src="../img/admin/disabled.gif"/>
			</label>
			&nbsp;&nbsp;&nbsp;&nbsp;
			<input type="checkbox" name="requiredExpiration" {if $requiredExpiration}checked="checked"{/if} />
			<label class="t">
				{l s='Required' mod='creditcardofflinepayment'}
			</label>
			<br />
			<span>{l s='Customers should enter month and year card expiration.' mod='creditcardofflinepayment'}</span>
		</div>

		<label>{l s='Request card issuer?' mod='creditcardofflinepayment'}:</label>
		<div class="margin-form">
			<input type="radio" name="requireIssuer" value="1" {if $requireIssuer}checked="checked"{/if} />
			<label class="t">
				<img title="{l s='Enabled' mod='creditcardofflinepayment'}" alt="{l s='Enabled' mod='creditcardofflinepayment'}" src="../img/admin/enabled.gif"/>
			</label>
			<input type="radio" name="requireIssuer" value="0" {if !$requireIssuer}checked="checked"{/if} />
			<label class="t">
				<img title="{l s='Disabled' mod='creditcardofflinepayment'}" alt="{l s='Disabled' mod='creditcardofflinepayment'}" src="../img/admin/disabled.gif"/>
			</label>
			&nbsp;&nbsp;&nbsp;&nbsp;
			<input type="checkbox" name="requiredIssuer" {if $requiredIssuer}checked="checked"{/if} />
			<label class="t">
				{l s='Required' mod='creditcardofflinepayment'}
			</label>
			<br />
			<span>{l s='Customers should introduce the card issuer.' mod='creditcardofflinepayment'}</span>
		</div>

		<label>{l s='Request card pin code?' mod='creditcardofflinepayment'}:</label>
		<div class="margin-form">
			<input type="radio" name="requirePin" value="1" {if $requirePin}checked="checked"{/if} />
			<label class="t">
				<img title="{l s='Enabled' mod='creditcardofflinepayment'}" alt="{l s='Enabled' mod='creditcardofflinepayment'}" src="../img/admin/enabled.gif"/>
			</label>
			<input type="radio" name="requirePin" value="0" {if !$requirePin}checked="checked"{/if} />
			<label class="t">
				<img title="{l s='Disabled' mod='creditcardofflinepayment'}" alt="{l s='Disabled' mod='creditcardofflinepayment'}" src="../img/admin/disabled.gif"/>
			</label>
			&nbsp;&nbsp;&nbsp;&nbsp;
			<input type="checkbox" name="requiredPin" {if $requiredPin}checked="checked"{/if} />
			<label class="t">
				{l s='Required' mod='creditcardofflinepayment'}
			</label>
			<br />
			<span>{l s='Customers should introduce the card pin code.' mod='creditcardofflinepayment'}</span>
		</div>
		<label>{l s='Card issuers' mod='creditcardofflinepayment'}:</label>


		<div class="margin-form">
			<table cellpadding="0" cellspacing="0" class="table">
				<thead>
					<tr>
						<th style="width: 200px;font-weight: bold;">{l s='Card issuer' mod='creditcardofflinepayment'}</th>
						<th>{l s='Enabled?' mod='creditcardofflinepayment'}</th>
					</tr>
				</thead>
				<tbody>
					{foreach from=$issuers item=issuer}
					<tr>
						<td>{$issuer.name|escape:'htmlall':'UTF-8'}</td>
						<td style="text-align: center;"><input type="checkbox" name="issuers[]" value="{$issuer.id|escape:'htmlall':'UTF-8'}" {if $issuer.authorized}checked="checked"{/if} /></td>
					</tr>
					{/foreach}
				</tbody>
			</table>
			<span>{l s='Card issuers enabled to choose when making payment.' mod='creditcardofflinepayment'}</span>
		</div>


	</fieldset>

	<div style="clear: both;"></div>
	<br />

	<center>
		<input type="submit" name="btnSubmit" value="{l s='Update settings' mod='creditcardofflinepayment'}" class="button" />
	</center>
	<hr />
</form>

<script type="text/javascript">
	javascript:showWM("{$working_mode|escape:'htmlall':'UTF-8'}");
</script>