{**
* Credit card offline payment
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

{capture name=path}{l s='Credit card payment' mod='creditcardofflinepayment'}{/capture}
{if version_compare($smarty.const._PS_VERSION_,'1.6','<')}
{include file="$tpl_dir./breadcrumb.tpl"}
{/if}

<h2>{l s='Order summary' mod='creditcardofflinepayment'}</h2>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

<div id="payment_form">
	{if $nbProducts <= 0}
		<p class="warning">{l s='Your shopping cart is empty.' mod='creditcardofflinepayment'}</p>
	{else}
		<h3 class="page-subheading">{l s='Credit card payment' mod='creditcardofflinepayment'}</h3>
		<form action="{if version_compare($smarty.const._PS_VERSION_,'1.5','<')}{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}{else}{$link->getModuleLink('creditcardofflinepayment', 'validation', [], true)|escape:'htmlall':'UTF-8'}{/if}" method="post" id="creditForm" name="creditForm" class="std box">

			<input type="hidden" name="id_currency" value="{$id_currency|escape:'htmlall':'UTF-8'}"/>
			<input type="hidden" name="skip_validation" value="{if isset($skip_validation)}{$skip_validation|escape:'htmlall':'UTF-8'}{/if}"/>

			<div class="payment_form_container">
				{if isset($errores) && $errores|@count > 0}
				<div class="alert error alert-danger" id="errorDiv">
					{l s='There are errors' mod='creditcardofflinepayment'}:
					<ol>
						{foreach from=$errores item=error}
						<li>{$error|escape:'htmlall':'UTF-8'}</li>
						{/foreach}
					</ol>
				</div>
				{/if}

				<p class="info-title">
					{l s='You can use the following card issuers:' mod='creditcardofflinepayment'}
					<br />
					{foreach from=$issuers item=issuer}
						{if $issuer.authorized}
							<img src="{$cc_path|escape:'htmlall':'UTF-8'}views/img/{$issuer.imgName|escape:'htmlall':'UTF-8'}" />
						{/if}
					{/foreach}
				</p>

				{if $requireIssuerName}
				<div class="{if $requiredIssuerName}required{/if} text form-group">
					<label for="card[name]" {if $requiredIssuerName}class="required"{/if}>
						{l s='Card holder name' mod='creditcardofflinepayment'}
					</label>
					<input type="text" class="{if $requiredIssuerName}is_required{/if} validate form-control" data-validate="isName" maxlength="150" name="card[name]" id="cardName" value="{if isset($card.name)}{$card.name|escape:'htmlall':'UTF-8'}{else}{$cookie->customer_firstname|escape:'htmlall':'UTF-8'} {$cookie->customer_lastname|escape:'htmlall':'UTF-8'}{/if}" autocomplete="name"/>
				</div>
				{/if}

				{if $requireCedule}
				<div class="{if $requiredCedule}required{/if} text form-group">
					<label for="card[cedula]" {if $requiredCedule}class="required"{/if}>
						{l s='ID Card/Passport' mod='creditcardofflinepayment'}
					</label>
					<input type="text" class="{if $requiredCedule}is_required{/if} validate form-control" data-validate="isDniLite" name="card[cedula]" id="cardCedule" value="{if isset($card.cedula)}{$card.cedula|escape:'htmlall':'UTF-8'}{/if}" maxlength="20"/>
				</div>
				{/if}

				{if $requireAddress}
				<div class="{if $requiredAddress}required{/if} text form-group">
					<label for="card[address]" {if $requiredAddress}class="required"{/if}>
						{l s='Address' mod='creditcardofflinepayment'}
					</label>
					<input type="text" class="{if $requiredAddress}is_required{/if} validate form-control" data-validate="isAddress" name="card[address]" id="cardAddress" value="{if isset($card.address)}{$card.address|escape:'htmlall':'UTF-8'}{/if}" maxlength="255" autocomplete="street-address"/>
				</div>
				{/if}

				{if $requireZipCode}
				<div class="{if $requiredZipCode}required{/if} text form-group">
					<label for="card[zipcode]" {if $requiredZipCode}class="required"{/if}>
						{l s='Zip code' mod='creditcardofflinepayment'}
					</label>
					<input type="text" class="{if $requiredZipCode}is_required{/if} validate form-control" data-validate="isPostCode" name="card[zipcode]" id="cardZipCode" value="{if isset($card.zipcode)}{$card.zipcode|escape:'htmlall':'UTF-8'}{/if}" autocomplete="postal-code" />
				</div>
				{/if}

				{if $requireCity}
				<div class="{if $requiredCity}required{/if} text form-group">
					<label for="card[city]" {if $requiredCity}class="required"{/if}>
						{l s='City' mod='creditcardofflinepayment'}
					</label>
					<input type="text" class="{if $requiredCity}is_required{/if} validate form-control" data-validate="isAddress" name="card[city]" id="cardCity" value="{if isset($card.city)}{$card.city|escape:'htmlall':'UTF-8'}{/if}" autocomplete="city" />
				</div>
				{/if}

				{if $requireState}
				<div class="{if $requiredState}required{/if} text form-group">
					<label for="card[state]" {if $requiredState}class="required"{/if}>
						{l s='State' mod='creditcardofflinepayment'}
					</label>
					<input type="text" class="{if $requiredState}is_required{/if} validate form-control" data-validate="isAddress" name="card[state]" id="cardState" value="{if isset($card.state)}{$card.state|escape:'htmlall':'UTF-8'}{/if}" autocomplete="state" />
				</div>
				{/if}

				{if $requireCountry}
				<div class="{if $requiredCountry}required{/if} text form-group">
					<label for="card[country]" {if $requiredCountry}class="required"{/if}>
						{l s='Country' mod='creditcardofflinepayment'}
					</label>
					<input type="text" class="{if $requiredCountry}is_required{/if} validate form-control" data-validate="isAddress" name="card[country]" id="cardCountry" value="{if isset($card.country)}{$card.country|escape:'htmlall':'UTF-8'}{/if}" autocomplete="country" />
				</div>
				{/if}

				{if $requireCardNumber}
				<div class="{if $requiredCardNumber}required{/if} text form-group">
					<label for="card[number]" {if $requiredCardNumber}class="required"{/if}>
						{l s='Credit card number' mod='creditcardofflinepayment'}
					</label>
					<input type="text" class="{if $requiredCardNumber}is_required{/if} validate form-control" data-validate="isNumber" name="card[number]" id="cardNumber" value="{if isset($card.number)}{$card.number|escape:'htmlall':'UTF-8'}{/if}" maxlength="16" autocomplete="cc-number" />
				</div>
				<script type="javascript">
				var card_number = document.getElementById('cardNumber'),
				    cleanCardNumber;

				cleanCardNumber= function(e) {
				    e.preventDefault();
				    var pastedText = '';
				    if (window.clipboardData && window.clipboardData.getData) { // IE
				        pastedText = window.clipboardData.getData('Text');
				      } else if (e.clipboardData && e.clipboardData.getData) {
				        pastedText = e.clipboardData.getData('text/plain');
				      }
				    this.value = pastedText.replace(/\D/g, '');
				};

				card_number.onpaste = cleanCardNumber;
				</script>
				{/if}

				{if $requireCVC}
				<div class="{if $requiredCVC}required{/if} text form-group">
					<label for="card[cvc]" {if $requiredCVC}class="required"{/if}>
						{l s='CVC (card security code)' mod='creditcardofflinepayment'}
					</label>
					<input type="text" class="{if $requiredCVC}is_required{/if} validate form-control" data-validate="isNumber" name="card[cvc]" id="cardCVC" value="{if isset($card.cvc)}{$card.cvc|escape:'htmlall':'UTF-8'}{/if}" maxlength="4" autocomplete="cc-csc" />
					<span class="hover-tipso-tooltip" data-tipso="<img src='{$cc_path|escape:'htmlall':'UTF-8'}views/img/cvc-cards.png' />"><img src="{$cc_path|escape:'htmlall':'UTF-8'}views/img/cvc-icon.png" width="39" height="27" alt="{l s='Where is the CVC number?' mod='creditcardofflinepayment'}" /></span>
				</div>
				{/if}

				{if $requireIssuer}
				<div class="{if $requiredIssuer}required{/if} form-group">
					<label for="card[issuer]" {if $requiredIssuer}class="required"{/if}>
						{l s='Card issuer' mod='creditcardofflinepayment'}
					</label>
					<select name="card[issuer]" class="form-control">
						<option value="" {if isset($card.issuer)}{if $card.issuer == ''}selected="selected"{/if}{/if}></option>
						{foreach from=$issuers item=issuer}
						{if $issuer.authorized}
						<option value="{$issuer.name|escape:'htmlall':'UTF-8'}" {if isset($card.issuer)}{if ($card.issuer == $issuer.name)}selected="selected"{/if}{/if}>{$issuer.name|escape:'htmlall':'UTF-8'}</option>
						{/if}
						{/foreach}
					</select>
				</div>
				{/if}

				{if $requirePin}
				<div class="{if $requiredPin}required{/if} text form-group">
					<label for="card[pin]" {if $requiredPin}class="required"{/if}>
						{l s='PIN card code' mod='creditcardofflinepayment'}
					</label>
					<input type="password" class="{if $requiredPin}is_required{/if} validate form-control" data-validate="isNumber" name="card[pin]" id="cardPIN" value="{if isset($card.pin)}{$card.pin|escape:'htmlall':'UTF-8'}{/if}" maxlength="4"/>
				</div>
				{/if}

				{if $requireExpiration}
				<div class="{if $requiredExpiration}required{/if} select form-group expiry-date">
					<label for="card[mes_caducidad]" {if $requiredExpiration}class="required"{/if}>
						{l s='Card expiry date' mod='creditcardofflinepayment'}
					</label>
					<div class="row">
						<div class="col-xs-4">
							<select name="card[mes_caducidad]" class="form-control" autocomplete="cc-exp-month">
								<option value="-" {if isset($card.mes_caducidad)}{if $card.mes_caducidad == '' || $card.mes_caducidad == '-'}selected="selected"{/if}{/if}>-</option>
								<option value="01" {if isset($card.mes_caducidad)}{if $card.mes_caducidad == '01'}selected="selected"{/if}{/if}>01</option>
								<option value="02" {if isset($card.mes_caducidad)}{if $card.mes_caducidad == '02'}selected="selected"{/if}{/if}>02</option>
								<option value="03" {if isset($card.mes_caducidad)}{if $card.mes_caducidad == '03'}selected="selected"{/if}{/if}>03</option>
								<option value="04" {if isset($card.mes_caducidad)}{if $card.mes_caducidad == '04'}selected="selected"{/if}{/if}>04</option>
								<option value="05" {if isset($card.mes_caducidad)}{if $card.mes_caducidad == '05'}selected="selected"{/if}{/if}>05</option>
								<option value="06" {if isset($card.mes_caducidad)}{if $card.mes_caducidad == '06'}selected="selected"{/if}{/if}>06</option>
								<option value="07" {if isset($card.mes_caducidad)}{if $card.mes_caducidad == '07'}selected="selected"{/if}{/if}>07</option>
								<option value="08" {if isset($card.mes_caducidad)}{if $card.mes_caducidad == '08'}selected="selected"{/if}{/if}>08</option>
								<option value="09" {if isset($card.mes_caducidad)}{if $card.mes_caducidad == '09'}selected="selected"{/if}{/if}>09</option>
								<option value="10" {if isset($card.mes_caducidad)}{if $card.mes_caducidad == '10'}selected="selected"{/if}{/if}>10</option>
								<option value="11" {if isset($card.mes_caducidad)}{if $card.mes_caducidad == '11'}selected="selected"{/if}{/if}>11</option>
								<option value="12" {if isset($card.mes_caducidad)}{if $card.mes_caducidad == '12'}selected="selected"{/if}{/if}>12</option>
							</select>
						</div>
						<div class="col-xs-4">
							<select name="card[ano_caducidad]" class="form-control" autocomplete="cc-exp-year">
								<option value="-" {if isset($card.ano_caducidad)}{if $card.ano_caducidad == "" || $card.ano_caducidad == "-"} selected="selected"{/if}{/if}>-</option>
								{*Function to get a select with the current year and 6 more *}
								{for $i=$smarty.now|date_format:"%Y" to ($smarty.now|date_format:"%Y")+($years -1)}
								<option value="{$i|escape:'htmlall':'UTF-8'}" {if isset($card.ano_caducidad)}{if $card.ano_caducidad == $i}selected="selected"{/if}{/if}>{$i|escape:'htmlall':'UTF-8'}&nbsp;&nbsp;</option>
								{/for}
							</select>
						</div>
					</div>
				</div>
				{/if}

				<p class="info-title">
					<span><strong>{l s='Confirm your order of' mod='creditcardofflinepayment'} <span class="price">{convertPriceWithCurrency price=$total currency=$currency}</span> {l s='by clicking the button "Confirm my order":' mod='creditcardofflinepayment'}</strong></span>
				</p>
			</div>

			<input type="submit" class="hidden" name="paymentSubmit"></input>
		</form>
	{/if}
</div>
<p class="cart_navigation clearfix">
	<a href="{if version_compare($smarty.const._PS_VERSION_,'1.5','<')}{$link->getPageLink('order.php', true)|escape:'htmlall':'UTF-8'}?step=3{else}{$link->getPageLink('order.php', true, NULL, "step=3")|escape:'htmlall':'UTF-8'}{/if}" class="btn btn-default button button-small button_large hideOnSubmit"><span><i class="icon-chevron-left"></i>{l s='Other payment methods' mod='creditcardofflinepayment'}</span></a>
	<button type="button" class="btn btn-default button button-medium hideOnSubmit pay-button" onclick="procesando();"><span>{l s='Confirm my order' mod='creditcardofflinepayment'}</span></button>
	<a type="button" class="btn btn-default button button-medium hideOnSubmit exclusive pay-button-14" onclick="procesando();"><span>{l s='Confirm my order' mod='creditcardofflinepayment'}</span></a>
</p>
<p id="procesando"><i>{l s='Processing...' mod='creditcardofflinepayment'}</i>&nbsp;&nbsp;&nbsp;<img src="{$cc_path|escape:'htmlall':'UTF-8'}views/img/loadingAnimation.gif" width="208" height="13" vertical-align="middle" />
</p>

<script type="text/javascript">
//< ![CDATA[
	function validate_isNumber(s)
	{
		var reg = /^[0-9]+$/;
		return reg.test(s);
	}

	function procesando()
	{
		$('*').css('cursor', 'wait');
		$('.cart_navigation').hide();
		$('#procesando').show();
		$("input[name='paymentSubmit']").click();
	}

	$('.hover-tipso-tooltip').tipso({
		tooltipHover: true,
		position: 'right',
		width: 247,
	});

	//preload image for tooltip
	$("<img />").attr("src", "{$cc_path|escape:'htmlall':'UTF-8'}views/img/cvc-cards.png");
//]]>
</script>