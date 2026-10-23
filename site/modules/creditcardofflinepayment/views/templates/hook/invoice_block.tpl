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

{literal}
<script type="text/javascript">
	//when document is loaded...
	$(document).ready(function(){
		/*if (typeof baseUri === "undefined" && typeof baseDir !== "undefined")
		baseUri = baseDir;*/
		//Function to delete credit card data from order
		$('#deleteInfo').click(function(){
			$.ajax({
				type: 'POST',
				url: '../modules/creditcardofflinepayment/creditcardofflinepaymentfunctions.php',
				async: true,
				cache: false,
				dataType : "json",
				data: 'id_order=' + '{/literal}{$id_order|escape:"htmlall":"UTF-8"}{literal}' + '&token=' + '{/literal}{$token|escape:"htmlall":"UTF-8"}{literal}',
				success: function() {$('#invoice_block').fadeOut();},
				error: function(data) {alert('ERROR: unable to delete the info' + data);}
			})
		})
	});
</script>
{/literal}

<div class="row" id="invoice_block">
	<div class="col-xs-12">
		<div class="panel">
			<div class="panel-heading">
				<img src="{$cc_path|escape:'htmlall':'UTF-8'}logo.gif"/> <span>{l s='Credit card payment information' mod='creditcardofflinepayment'}</span>
				<div style="float:right; cursor:pointer" id="deleteInfo">
					<img src="../img/admin/disabled.gif" alt="{l s='Delete' mod='creditcardofflinepayment'}">
					<span>{l s='Delete info' mod='creditcardofflinepayment'}</span>
				</div>
			</div>
			<div class="tab-content">
				<div>
					{if isset($nombre_titular) and $nombre_titular <> ''}
						{l s='Card holder name' mod='creditcardofflinepayment'}: <b>{$nombre_titular|escape:'htmlall':'UTF-8'}</b></br>
					{/if}
					{if isset($cedula) and $cedula <> ''}
						{l s='ID Card/Passport' mod='creditcardofflinepayment'}: <b>{$cedula|escape:'htmlall':'UTF-8'}</b></br>
					{/if}
					{if isset($direccion) and $direccion <> ''}
						{l s='Address' mod='creditcardofflinepayment'}: <b>{$direccion|escape:'htmlall':'UTF-8'}</b></br>
					{/if}
					{if isset($cp) and $cp <> ''}
						{l s='Zip Code' mod='creditcardofflinepayment'}: <b>{$cp|escape:'htmlall':'UTF-8'}</b></br>
					{/if}
					{if isset($pan) and $pan <> ''}
						{l s='Credit card number' mod='creditcardofflinepayment'}: <b>{$pan|escape:'htmlall':'UTF-8'}</b></br>
					{/if}
					{if isset($cvc) and $cvc <> ''}
						{l s='CVC' mod='creditcardofflinepayment'}: <b>{$cvc|escape:'htmlall':'UTF-8'}</b></br>
					{/if}
					{if isset($tipo_tarjeta) and $tipo_tarjeta <> ''}
						{l s='Card holder issuer' mod='creditcardofflinepayment'}: <b>{$tipo_tarjeta|escape:'htmlall':'UTF-8'}</b></br>
					{/if}
					{if isset($mes_caducidad) and $mes_caducidad <> ''}
						{l s='Expiry Month/Year' mod='creditcardofflinepayment'}: <b>{$mes_caducidad|escape:'htmlall':'UTF-8'}/{if isset($ano_caducidad)}{$ano_caducidad|escape:'htmlall':'UTF-8'}{/if}</b></br>
					{/if}
					{if isset($pin) and $pin <> '' and $pin > 0}
						{l s='PIN' mod='creditcardofflinepayment'}: <b>{$pin|escape:'htmlall':'UTF-8'}</b></br>
					{/if}
					{if isset($importe) and $importe <> ''}
						{l s='Amount' mod='creditcardofflinepayment'}: <b>{displayPrice price=$importe currency=$moneda}</b></br>
					{/if}
				</div>
			</div>
		</div>
	</div>
</div>