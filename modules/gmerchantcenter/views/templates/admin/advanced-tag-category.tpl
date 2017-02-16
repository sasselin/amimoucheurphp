{*
* 2003-2016 Business Tech
*
* @author Business Tech SARL <http://www.businesstech.fr/en/contact-us>
* @copyright  2003-2016 Business Tech SARL
*}
{if !empty($aErrors)}
{include file="`$sErrorInclude`"}
{* USE CASE - edition review mode *}
{else}
<div id="{$sModuleName|escape:'htmlall':'UTF-8'}" class="bootstrap" {if empty($bCompare15)}style="text-align: left !important;"{/if}>
	<div id="bt_advanced-tag" class="col-xs-12 adwords">
		<h3>{l s='Define your tag for each category' mod='gmerchantcenter'}</h3>

		<div class="clr_hr"></div>
		<div class="clr_20"></div>

		<div class="alert alert-warning">
			{l s='WARNING : For this tool by category to work correctly, you need to make sure that your products are correctly assigned to the right categories. It is the MAIN default category that counts (the one in the pull-down menu on your product sheet in the back-office)' mod='gmerchantcenter'}
		</div>
		<div class="alert alert-info">
			<div class="form-group">
				<label class="col-xs-3"><b>{l s='Select your type of TAG to configure:' mod='gmerchantcenter'}</b></label>
				{*<b>{l s='Select your type of TAG to configure:' mod='gmerchantcenter'}</b>*}
				<div class="col-xs-4">
					<select class="set_tag" name="set_tag" id="set_tag">
						<option value="0">---</option>
						{if !empty($bMaterial)}
							<option value="material">{l s='Set product material' mod='gmerchantcenter'}</option>
						{/if}
						{if !empty($bPattern)}
							<option value="pattern">{l s='Set product pattern' mod='gmerchantcenter'}</option>
						{/if}
						{if !empty($bGender)}
							<option value="gender">{l s='Set product gender' mod='gmerchantcenter'}</option>
						{/if}
						{if !empty($bAgeGroup)}
							<option value="agegroup">{l s='Set product age group' mod='gmerchantcenter'}</option>
						{/if}
						{if !empty($bTagAdult)}
							<option value="adult">{l s='Set product tag adult' mod='gmerchantcenter'}</option>
						{/if}
					</select>
				</div>
			</div>
		</div>
		<div class="bulk-actions">
			<table class="table">
				<tr id="bulk_action_material">
					<td class="label_tag_categories">{l s='Manage your MATERIAL product for all categories' mod='gmerchantcenter'}</td>
					<td>
						<select name="set_material_bulk_action" class="set_material_bulk_action">
							{foreach from=$aFeatures item=feature}
								<option value="{$feature.id_feature|intval}">{$feature.name|escape:'htmlall':'UTF-8'}</option>
							{/foreach}
						</select>
					</td>
					<td><span class="btn btn-default" onclick="oGmc.doSet('.material', $('.set_material_bulk_action').val());">{l s='Set for all categories' mod='gmerchantcenter'}</span> - <span class="btn btn-default" onclick="oGmc.doSet('.material', 0);">{l s='Reset' mod='gmerchantcenter'}</td>
				</tr>
				<tr id="bulk_action_pattern">
					<td class="label_tag_categories">{l s='Manage your PATTERN product for all categories' mod='gmerchantcenter'}</td>
					<td>
						<select name="set_pattern_bulk_action" class="set_pattern_bulk_action">
							{foreach from=$aFeatures item=feature}
								<option value="{$feature.id_feature|intval}">{$feature.name|escape:'html'}</option>
							{/foreach}
						</select>
					</td>
					<td><span class="btn btn-default" onclick="oGmc.doSet('.pattern', $('.set_pattern_bulk_action').val());">{l s='Set for all categories' mod='gmerchantcenter'}</span> - <span class="btn btn-default" onclick="oGmc.doSet('.pattern', 0);">{l s='Reset' mod='gmerchantcenter'}</span></td>
				</tr>
				<tr id="bulk_action_adult">
					<td class="label_tag_categories">{l s='Manage your AGE product for all cateogories' mod='gmerchantcenter'}</td>
					<td><span class="btn btn-default" onclick="oGmc.doSet('.agegroup', 'adult');">{l s='adult' mod='gmerchantcenter'} </span> - <span class="btn btn-default" onclick="oGmc.doSet('.agegroup', 'kids');">{l s='Kid' mod='gmerchantcenter'} </span> - <span class="btn btn-default" onclick="oGmc.doSet('.agegroup', 0);">{l s='Reset' mod='gmerchantcenter'}</span></td>
				</tr>
				<tr id="bulk_action_gender">
					<td class="label_tag_categories"> {l s='Manage your GENDER product for all categories' mod='gmerchantcenter'}</td>
					<td><span class="btn btn-default" onclick="oGmc.doSet('.gender', 'male');">{l s='male' mod='gmerchantcenter'} </span> - <span class="btn btn-default" onclick="oGmc.doSet('.gender', 'female');">{l s='Female' mod='gmerchantcenter'} </span> - <span class="btn btn-default" onclick="oGmc.doSet('.gender', 'unisex');">{l s='Unisex' mod='gmerchantcenter'} </span> - <span class="btn btn-default" onclick="oGmc.doSet('.gender', 0);">{l s='Reset' mod='gmerchantcenter'}</span></td>
				</tr>
				<tr id="bulk_action_tagadult">
					<td class="label_tag_categories" >{l s='Manage your ADULT product for all categories' mod='gmerchantcenter'}</td>
					<td><span class="btn btn-default" onclick="oGmc.doSet('.adult', 'true');">{l s='Do all' mod='gmerchantcenter'} </span> - <span class="btn btn-default" onclick="oGmc.doSet('.adult', 0);">{l s='Reset' mod='gmerchantcenter'}</span></td>
				</tr>
			</table>
		</div>
		<form class="form-horizontal" method="post" id="bt_form-advanced-tag" name="bt_form-advanced-tag" {if $smarty.const._GSR_USE_JS == true}onsubmit="oGmc.form('bt_form-advanced-tag', '{$sURI|escape:'htmlall':'UTF-8'}', null, 'bt_advanced-tag', 'bt_advanced-tag', false, true, null, 'AdvancedTag', 'loadingAdvancedTagDiv');return false;"{/if}>
			<input type="hidden" name="{$sCtrlParamName}" value="{$sController|escape:'htmlall':'UTF-8'}" />
			<input type="hidden" name="sAction" value="{$aQueryParams.tagUpdate.action|escape:'htmlall':'UTF-8'}" />
			<input type="hidden" name="sType" value="{$aQueryParams.tagUpdate.type|escape:'htmlall':'UTF-8'}" />
			<input type="hidden" name="sUseTag" value="{$sUseTag|escape:'htmlall':'UTF-8'}" id="default_tag" />
			{foreach from=$aShopCategories item=cat}
				<table class="table table-responsive">
					<tr>
						<td class="label_tag_categories">{l s='Shop category' mod='gmerchantcenter'} : {$cat.path|escape:'UTF-8'}</td>
						<td>
							<div class="value_material">
								{l s='Material:' mod='gmerchantcenter'}
								<select name="material[{$cat.id_category|intval}]" class="material" >
									<option value="0">-----</option>
									{foreach from=$aFeatures item=feature}
										<option value="{$feature.id_feature|intval}" {if $cat.material == $feature.id_feature} selected {/if}>{$feature.name|escape:'html'}</option>
									{/foreach}
								</select>
							</div>
							<div class="value_pattern">
								{l s='Pattern:' mod='gmerchantcenter'}
								<select name="pattern[{$cat.id_category|intval}]" class="pattern" >
									<option value="0">-----</option>
									{foreach from=$aFeatures item=feature}
										<option value="{$feature.id_feature|intval}" {if $cat.pattern == $feature.id_feature} selected {/if}>{$feature.name|escape:'html'}</option>
									{/foreach}
								</select>
							</div>
							<div class="value_agegroup">
								{l s='Age group:' mod='gmerchantcenter'}
								<select class="agegroup" name="agegroup[{$cat.id_category|intval}]" id="agegroup{$cat.id_category|intval}">
									<option value="0"{if $cat.agegroup=="0"} selected{/if}>--</option>
									<option value="adult"{if $cat.agegroup=="adult"} selected{/if}>adult</option>
									<option value="kids"{if $cat.agegroup=="kids"} selected{/if}>kids</option>
								</select>
							</div>
							<div class="value_gender">
								{l s='Gender:' mod='gmerchantcenter'}
								<select class="gender" name="gender[{$cat.id_category|intval}]" id="gender{$cat.id_category|intval}">
									<option value="0"{if $cat.gender=="0"} selected{/if}>--</option>
									<option value="male"{if $cat.gender=="male"} selected{/if}>male</option>
									<option value="female"{if $cat.gender=="female"} selected{/if}>female</option>
									<option value="unisex"{if $cat.gender=="unisex"} selected{/if}>unisex</option>
								</select>
							</div>
							<div class="value_tagadult">
								{l s='Tag adult:' mod='gmerchantcenter'}
								<select class="adult" name="adult[{$cat.id_category|intval}]" id="adult{$cat.id_category|intval}">
									<option value="0"{if $cat.adult=="0"} selected{/if}>--</option>
									<option value="true"{if $cat.adult=="true"} selected{/if}>true</option>
								</select>
							</div>
						</td>
					</tr>
				</table>
			{/foreach}

			<p style="text-align: center !important;">
				{if $smarty.const._GMC_USE_JS == true}
					<script type="text/javascript">
						{literal}
						var oAdvancedCallback = [{
							//'name' : 'moderationList',
							//'url' : '',
							//'params' : '',
							//'toShow' : '',
							//'toHide' : '',
							//'bFancybox' : false,
							//'bFancyboxActivity' : false,
							//'sLoadbar' : null,
							//'sScrollTo' : null,
							//'oCallBack' : {}
						}];
						{/literal}
					</script>
					<input type="button" name="{$sModuleName|escape:'htmlall':'UTF-8'}CommentButton" class="btn btn-success btn-lg" value="{l s='Modify' mod='gmerchantcenter'}" onclick="oGmc.form('bt_form-advanced-tag', '{$sURI|escape:'htmlall':'UTF-8'}', null, 'bt_advanced-tag', 'bt_advanced-tag', false, true, oAdvancedCallback, 'AdvancedTag', 'loadingAdvancedTagDiv');return false;" />
				{else}
					<input type="submit" name="{$sModuleName|escape:'htmlall':'UTF-8'}CommentButton" class="btn btn-success btn-lg" value="{l s='Modify' mod='gmerchantcenter'}" />
				{/if}
				{if !empty($bCompare149)}
					<button class="btn btn-danger btn-lg" value="{l s='Cancel' mod='gmerchantcenter'}"  onclick="$.fancybox.close();return false;">{l s='Cancel' mod='gmerchantcenter'}</button>
				{/if}
			</p>
		</form>
		{literal}
		<script type="text/javascript">
			function handleOptionToDisplay(sTagType) {
				// initialize the list of elt to show and hide
				var aShow = [];
				var aHide = [];

				switch (sTagType) {
					case 'material':
						oGmc.doSet('#set_tag', 'material');
						aShow = ['#bulk_action_material', '.value_material'];
						aHide = ['#bulk_action_pattern', '#bulk_action_adult', '#bulk_action_gender', '#bulk_action_tagadult', '.value_pattern', '.value_agegroup', '.value_gender', '.value_tagadult'];
						break;
					case 'pattern':
						oGmc.doSet('#set_tag', 'pattern');
						aShow = ['#bulk_action_pattern', '.value_pattern'];
						aHide = ['#bulk_action_material', '#bulk_action_adult', '#bulk_action_gender', '#bulk_action_tagadult', '.value_material', '.value_agegroup', '.value_gender', '.value_tagadult'];
						break;
					case 'agegroup':
						oGmc.doSet('#set_tag', 'agegroup');
						aShow = ['#bulk_action_adult', '.value_agegroup'];
						aHide = ['#bulk_action_material', '#bulk_action_pattern', '#bulk_action_gender', '#bulk_action_tagadult', '.value_material', '.value_pattern', '.value_gender', '.value_tagadult'];
						break;
					case 'gender':
						oGmc.doSet('#set_tag', 'gender');
						aShow = ['#bulk_action_gender', '.value_gender'];
						aHide = ['#bulk_action_material', '#bulk_action_pattern', '#bulk_action_adult', '#bulk_action_tagadult', '.value_material', '.value_pattern', '.value_agegroup', '.value_tagadult'];
						break;
					case 'adult':
						oGmc.doSet('#set_tag', 'adult');
						aShow = ['#bulk_action_tagadult', '.value_tagadult'];
						aHide = ['#bulk_action_material', '#bulk_action_pattern', '#bulk_action_adult', '#bulk_action_gender', '.value_material', '.value_pattern', '.value_agegroup', '.value_gender'];
						break;
					case '0':
						aHide = ['#bulk_action_material', '#bulk_action_pattern', '#bulk_action_adult', '#bulk_action_gender', '#bulk_action_tagadult', '.value_material', '.value_pattern', '.value_agegroup', '.value_gender', '.value_tagadult'];
						break;
					default:
						break;
				}
				oGmc.initHide(aHide);
				oGmc.initShow(aShow);
			}

			// execute management of options
			handleOptionToDisplay($("#default_tag").val());

			$("#set_tag").change(function () {
				handleOptionToDisplay($(this).val());
			});
		</script>
		{/literal}
	</div>
</div>
<div id="loadingAdvancedTagDiv" style="display: none;">
	<div class="alert alert-info">
		<p style="text-align: center !important;"><img src="{$sLoadingImg|escape:'htmlall':'UTF-8'}" alt="Loading" /></p><div class="clr_20"></div>
		<p style="text-align: center !important;">{l s='Your update configuration is in progress' mod='gmerchantcenter'}</p>
	</div>
</div>
{/if}