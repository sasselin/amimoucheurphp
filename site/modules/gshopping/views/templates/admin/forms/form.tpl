{*
* 2007-2015 PrestaShop
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

<form id="form_add" name="form">
	<div id="wizard" class="swMain">
		<ul class="anchor">
			<li>
				<a href="#step-1"  class="selected" >
					<div class="stepNumber">1</div>
					<span class="stepDesc">{l s='Lang of export' mod='gshopping'}</span>
				</a>
			</li>
			<li>
				<a href="#step-2">
					<div class="stepNumber">2</div>
					<span class="stepDesc">{l s='Category matching' mod='gshopping'}</span>
				</a>
			</li>
			<li>
				<a href="#step-3">
					<div class="stepNumber">3</div>
					<span class="stepDesc">{l s='Parameters' mod='gshopping'}</span>
				</a>
			</li>
		</ul>

		<hr class="clear"/>
		<div class="clearfix"></div>
		<div id="step-1">
			<div class="alert alert-info">
				<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
				{l s='Choose the language for the country that you configure the feed. The chosen language should be the language of the country or English by default. Only one language is accepted by country.' mod='gshopping'} <a target="_blank" href="{$module_dir|escape:'htmlall':'UTF-8'}{$guide_link|escape:'htmlall':'UTF-8'}">{l s='(C.f. documentation).' mod='gshopping'}</a>
			</div>
			<div class="form-group clear">
				<label for="form-field-1" class="col-sm-4 control-label required">
					{l s='Select the lang for exportation' mod='gshopping'}
				</label>
				<div class="col-sm-6">
					<select id="select_lang" name="select_lang" class="selectpicker show-menu-arrow" data-show-subtext="true">
						<option value="">{l s='Select your language' mod='gshopping'}</option>
						{foreach $lang_select as $lang}
							<option value="{$lang.id|intval}" {if !empty($lang.subtitle)}data-subtext="{$lang.subtitle|escape:'htmlall':'UTF-8'}"{/if} data-icon="icon-flag">{$lang.title}</option>
						{/foreach}
					</select>
					<div class="clear">&nbsp;</div>
				</div>
			</div>
			<center><div id="google_loader" class="google_loader hidden"><img src="../modules/gshopping/views/img/loader.gif"></div></center>
		</div>
		<div id="step-2">
			<div class="alert alert-info">
				<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
				{l s='Match the categories of your shop to the Google Shopping categories. See this' mod='gshopping'}
				<a target="_blank" href='https://support.google.com/merchants/answer/160081?hl=en'>{l s=' link.' mod='gshopping'}</a>
				{l s='for further information about Google product taxonomy.' mod='gshopping'}
				<br/>
				{l s='You can increase the chances that your products will be included in relevant categories by matching the Google categories. ' mod='gshopping'} <a target="_blank" href="{$module_dir|escape:'htmlall':'UTF-8'}{$guide_link|escape:'htmlall':'UTF-8'}">{l s='(C.f. documentation).' mod='gshopping'}</a>
			</div>
			<div class="form-group clear">
				<label for="form-field-1" class="col-sm-4 control-label">
					{l s='Shop Category' mod='gshopping'}
				</label>
				<div class="col-sm-6">
					<div id="categoryname" class="well well-sm">&nsbp;</div>
					<div class="clear">&nbsp;</div>
				</div>
			</div>
			<div class="form-group clear">
				<label for="form-field-1" class="col-sm-4 control-label required">
					{l s='Select Google category' mod='gshopping'}
				</label>
				<div class="col-sm-6">
					<div class="tooltips" data-toggle="tooltip" data-placement="top" data-original-title="{l s='Google root category (obligatory field)' mod='gshopping'}">
						<select id="select_google_category" name="select_google_category" class="selectpicker show-menu-arrow show-tick span5 " data-show-subtext="true" data-live-search="true" data-size="auto">
						</select>
					</div>
					<div class="tooltips" data-toggle="tooltip" data-placement="bottom" data-original-title="{l s='Google sub-category (recommended fill)' mod='gshopping'}">
						<select id="sub_category" name="sub_category" class="selectpicker show-menu-arrow show-tick auto span5" data-show-subtext="true" data-live-search="true">
						</select>
					</div>
					<div class="clear">&nbsp;</div>
				</div>
			</div>
		</div>
		<div id="step-3">
			<center>
				<div class="google_loader hidden"><img src="../modules/gshopping/views/img/loader.gif">
				<br>
				<br>
				</div>
			</center>
			<div id="parameterstrue">
			<div class="alert alert-info">
				<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
				{l s='If you have chosen the “Apparel & Accessories” category, you need to specify the “product type” attribute. Check the obligatory attributes for each country' mod='gshopping'}
				<a target="_blank" href='https://support.google.com/merchants/answer/1344057?hl=en'>{l s=' here.' mod='gshopping'}</a>
				<br/>
				{l s='Match Google attributes to your shop attributes or features. You can create new attributes or features on the Catalog tab.' mod='gshopping'}
				<br/>
				{l s='We recommend you to create attributes and features with the same format that Google Shopping' mod='gshopping'}
				<a target="_blank" href="{$module_dir|escape:'htmlall':'UTF-8'}{$guide_link|escape:'htmlall':'UTF-8'}">{l s='(C.f. documentation).' mod='gshopping'}</a>
			</div>
			<div class="form-group clear">
				<label for="form-field-1" class="col-sm-4 control-label required">
					{l s='Gender' mod='gshopping'}
				</label>
				<div class="col-sm-6">
						<select id="select_gender" name="select_gender" class="selectattributes selectpicker show-menu-arrow show-tick" data-type="gender" data-show-subtext="true" data-live-search="true" data-size="auto">
							{*Will be add with javascript*}
						</select>
						<div id="gender_values" style="display:none" class="selectdetailscontainer">
							<label for="form-field-1" class="col-sm-4 control-label required">
								{l s='Male' mod='gshopping'}
							</label>
							<select id="gender_male" name="gender_male" class="selectdetails selectpicker show-menu-arrow show-tick auto span2" data-show-subtext="true" data-live-search="true">
							</select><label for="form-field-1" class="col-sm-4 control-label required">
								{l s='Female' mod='gshopping'}
							</label>
							<select id="gender_female" name="gender_female" class="selectdetails selectpicker show-menu-arrow show-tick auto span2" data-show-subtext="true" data-live-search="true">
							</select><label for="form-field-1" class="col-sm-4 control-label required">
								{l s='Unisex' mod='gshopping'}
							</label>
							<select id="gender_unisex" name="gender_unisex" class="selectdetails selectpicker show-menu-arrow show-tick auto span2" data-show-subtext="true" data-live-search="true">
							</select>
						</div>
					<div class="clear">&nbsp;</div>
				</div>
			</div>
			<div class="form-group clear">
				<label for="form-field-1" class="col-sm-4 control-label required">
					{l s='Age Group' mod='gshopping'}
				</label>
				<div class="col-sm-6">
					<select id="select_age_group" name="select_age_group" class="selectattributes selectpicker show-menu-arrow show-tick" data-type="age_group" data-show-subtext="true" data-live-search="true" data-size="auto">
					</select>
					<div id="age_group_values" style="display:none" class="selectdetailscontainer">
						<label for="form-field-1" class="col-sm-4 control-label required">
							{l s='New born' mod='gshopping'}
						</label>
						<select id="age_group_newborn" name="age_group_newborn" class="selectdetails selectpicker show-menu-arrow show-tick auto span2" data-show-subtext="true" data-live-search="true">
						</select>
						<label for="form-field-1" class="col-sm-4 control-label required">
							{l s='Infant' mod='gshopping'}
						</label>
						<select id="age_group_infant" name="age_group_infant" class="selectdetails selectpicker show-menu-arrow show-tick auto span2" data-show-subtext="true" data-live-search="true">
						</select>
						<label for="form-field-1" class="col-sm-4 control-label required">
							{l s='Toddler' mod='gshopping'}
						</label>
						<select id="age_group_toddler" name="age_group_toddler" class="selectdetails selectpicker show-menu-arrow show-tick auto span2" data-show-subtext="true" data-live-search="true">
						</select>
						<label for="form-field-1" class="col-sm-4 control-label required">
							{l s='Kids' mod='gshopping'}
						</label>
						<select id="age_group_kids" name="age_group_kids" class="selectdetails selectpicker show-menu-arrow show-tick auto span2" data-show-subtext="true" data-live-search="true">
						</select>
						<label for="form-field-1" class="col-sm-4 control-label required">
							{l s='Adult' mod='gshopping'}
						</label>
						<select id="age_group_adult" name="age_group_adult" class="selectdetails selectpicker show-menu-arrow show-tick auto span2" data-show-subtext="true" data-live-search="true">
						</select>
					</div>
					<div class="clear">&nbsp;</div>
				</div>
			</div>
			<div class="form-group clear">
				<label for="form-field-1" class="col-sm-4 control-label required">
					{l s='Color' mod='gshopping'}
				</label>
				<div class="col-sm-6">
						<select id="select_color" name="select_color" class="selectattributes selectpicker show-menu-arrow show-tick" data-type="color" data-show-subtext="true" data-live-search="true" data-size="auto">
						</select>
					<div class="clear">&nbsp;</div>
				</div>
			</div>
			<div class="form-group clear">
				<label for="form-field-1" class="col-sm-4 control-label required">
					{l s='Size' mod='gshopping'}
				</label>
				<div class="col-sm-6">
						<select id="select_size" name="select_size" class="selectattributes selectpicker show-menu-arrow show-tick" data-type="size" data-show-subtext="true" data-live-search="true" data-size="auto">
						</select>
					<div class="clear">&nbsp;</div>
				</div>
			</div>
			<div class="form-group clear">
				<label for="form-field-1" class="col-sm-4 control-label required">
					{l s='Material' mod='gshopping'}
				</label>
				<div class="col-sm-6">
						<select id="select_material" name="select_material" class="selectattributes selectpicker show-menu-arrow show-tick" data-type="material" data-show-subtext="true" data-live-search="true" data-size="auto">
						</select>
					<div class="clear">&nbsp;</div>
				</div>
			</div>
			<div class="form-group clear">
				<label for="form-field-1" class="col-sm-4 control-label required">
					{l s='Pattern' mod='gshopping'}
				</label>
				<div class="col-sm-6">
						<select id="select_pattern" name="select_pattern" class="selectattributes selectpicker show-menu-arrow show-tick" data-type="pattern" data-show-subtext="true" data-live-search="true" data-size="auto">
						</select>
					<div class="clear">&nbsp;</div>
				</div>
			</div>
			<br>
			<br>
			<div class="alert alert-warning">
				<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
				<b>{l s='Note: ' mod='gshopping'}</b> {l s='The attribute names in the feed can be either English or the language of the target country for that feed. However, you must provide all the attribute names in one language.' mod='gshopping'}
			</div>
		</div>
		<div id="parametersfalse">
			<div class="alert alert-success">
				<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
				{l s='Your category matching has been succesfully added, you can now save the configuration.' mod='gshopping'}
			</div>
		</div>
	</div>
	</form>
