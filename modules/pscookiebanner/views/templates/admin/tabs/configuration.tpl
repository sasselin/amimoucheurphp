{*
* 2007-2017 PrestaShop
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
* @author    PrestaShop SA <contact@prestashop.com>
* @copyright 2007-2017 PrestaShop SA
* @license   http://addons.prestashop.com/en/content/12-terms-and-conditions-of-use
* International Registered Trademark & Property of PrestaShop SA
*}
<div class="panel col-lg-10 right-panel">
    <h3>
        <i class="a fa-css3"></i> {l s='Customize your Buylater block' mod='pscookiebanner'} <small>{$module_display|escape:'htmlall':'UTF-8'}</small>
    </h3>
    <form id="buylaterConf" method="post" action="#" class="form-horizontal">
        <div class="buylaterContent">
            <h4>1 - {l s='Create a new cookies policy page' mod='pscookiebanner'}</h4>
            <div class="alert alert-info" style="display:block;">
                <p>
                {l s='Before configuring your cookie banner, please make sure that you have created your cookies policy page.' mod='pscookiebanner'}<br />
                {l s='To create a new cookies policy page on your website and customize its content, please' mod='pscookiebanner'} <a href="{$CMS_LINK|escape:'htmlall':'UTF-8'}" target="_blank">{l s='click here' mod='pscookiebanner'}.</a><br><br>
                {l s='Concerning the content of this page, you must be in compliance with applicable regulations related to ecommerce, including the legislation related to personal data and cookies. Please click here to visualize some useful links on how to manage cookies from different platforms.' mod='pscookiebanner'} <a href="{$readme|escape:'htmlall':'UTF-8'}" target="_blank">{l s='Click here' mod='pscookiebanner'}.</a>
                </p>
            </div>
            <h4>2 - {l s='Create a cookie banner' mod='pscookiebanner'}</h4>

            {* START COOKIE BANNER POSITION *}
            <div class="form-group">
                <div class="col-xs-12 col-sm-12 col-md-5 col-lg-3">
                    <div class="text-right">
                        <label class="boldtext control-label">{l s='Choose the position of your banner' mod='pscookiebanner'}</label>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 cookiebanner-center">
                    <div class="radio-banner-position">
                        <ul>
                            <li>
                                <label class="select">
                                    <input type="radio" name="CB-POSITION" value="fixedtop" {if $CB_POSITION eq 'fixedtop'}checked{/if}/>
                                    <img src="{$img|escape:'htmlall':'UTF-8'}top.png" width="250" height="150">
                                </label>
                                <label class="info-position">{l s='Top' mod='pscookiebanner'}</label>
                            </li>
                            <li>
                                <label class="select">
                                    <input type="radio" name="CB-POSITION" value="bottom" {if $CB_POSITION eq 'bottom'}checked{/if}/>
                                    <img src="{$img|escape:'htmlall':'UTF-8'}bottom.png" width="250" height="150">
                                </label>
                                <label class="info-position">{l s='Bottom' mod='pscookiebanner'}</label>
                            </li>
                            <li>
                                <label class="select">
                                    <input type="radio" name="CB-POSITION" value="bottomright" {if $CB_POSITION eq 'bottomright'}checked{/if}/>
                                    <img src="{$img|escape:'htmlall':'UTF-8'}bottomright.png" width="250" height="150">
                                </label>
                                <label class="info-position">{l s='Bottom right' mod='pscookiebanner'}</label>
                            </li>
                            <li>
                                <label class="select">
                                    <input type="radio" name="CB-POSITION" value="bottomleft" {if $CB_POSITION eq 'bottomleft'}checked{/if}/>
                                    <img src="{$img|escape:'htmlall':'UTF-8'}bottomleft.png" width="250" height="150">
                                </label>
                                <label class="info-position">{l s='Bottom left' mod='pscookiebanner'}</label>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            {* END COOKIE BANNER POSITION *}

            {* START COOKIE BANNER BACKGROUND COLOR *}
            <div class="form-group">
                <div class="col-xs-12 col-sm-12 col-md-5 col-lg-3">
                    <div class="text-right">
                        <label class="control-label">
                            <span class="label-tooltip" data-toggle="tooltip" data-html="true" data-original-title="{l s='Choose the background color of your cookie banner' mod='pscookiebanner'}">
                                {l s='Background color' mod='pscookiebanner'}
                            </span>
                        </label>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-12 col-md-7 col-lg-2">
                    <div class="input-group">
                        <input type="text" data-hex="true" class="color mColorPickerInput mColorPicker" name="CB-BG-COLOR" value="{$CB_BG_COLOR|escape:'htmlall':'UTF-8'}" id="color_0" style="background-color:{$CB_BG_COLOR|escape:'htmlall':'UTF-8'}; color: white;"><span style="cursor:pointer;" id="icp_color_0" class="mColorPickerTrigger input-group-addon" data-mcolorpicker="true"><img src="../img/admin/color.png" style="border:0;margin:0 0 0 3px" align="absmiddle"></span>
                    </div>
                </div>
            </div>
            {* END COOKIE BANNER BACKGROUND COLOR *}

            {* START COOKIE BANNER BACKGROUND OPACITY *}
            <div class="form-group">
                <div class="col-xs-12 col-sm-12 col-md-5 col-lg-3">
                    <div class="text-right">
                        <label class="control-label">
                            {l s='Opacity' mod='pscookiebanner'}
                        </label>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-12 col-md-7 col-lg-3">
                    <div class="input-group">
                        <input id="CB-OPACITY" name="CB-BG-OPACITY-SLIDER" data-slider-id='ex1Slider' type="text" data-slider-min="0" data-slider-max="100" data-slider-step="1" data-slider-value="{$CB_BG_OPACITY|escape:'htmlall':'UTF-8'}"/>
                    </div>
                    <div class="help-block">
                        {l s='Set the opacity of your banner' mod='pscookiebanner'}
                    </div>
                </div>
                <div class="col-xs-12 col-sm-12 col-md-5 col-lg-3">
                    <div class="input-group">
                        <input class="cookiebanner-number" required="required" value="{$CB_BG_OPACITY|escape:'htmlall':'UTF-8'}" type="number" name="CB-BG-OPACITY" min="0" max="100">
                    </div>
                </div>
            </div>
            {* END COOKIE BANNER BACKGROUND OPACITY *}

            {* START COOKIE BANNER TEXT *}
            {foreach from=$languages item=language}
                {if $languages|count > 1}
                    <div class="translatable-field lang-{$language.id_lang|escape:'htmlall':'UTF-8'}" {if $language.id_lang != $defaultFormLanguage}style="display:none"{/if}>
                {/if}
                <div class="form-group">
                    <div class="col-xs-12 col-sm-12 col-md-5 col-lg-3">
                        <div class="text-right">
                            <label for="buylater_title_text" class="control-label">
                                    {l s='Add your text' mod='pscookiebanner'}
                            </label>
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-7 col-lg-4">
                        <textarea class="CB-TEXT" required="required" name="CB-TEXT-{$language.id_lang|escape:'htmlall':'UTF-8'}" text="{$CB_TEXT[$language.id_lang]|escape:'htmlall':'UTF-8'}" rows="8" cols="80">{$CB_TEXT[$language.id_lang]|escape:'htmlall':'UTF-8'}</textarea>
                        <div class="help-block">
                            {l s='Maximum character recommendes in total : 300 for the best quality on desktop and smartphones' mod='pscookiebanner'}
                        </div>
                    </div>
                    {if $languages|count > 1}
                        <div class="col-xs-12 col-sm-12 col-md-7 col-lg-3">
                            <button type="button" class="btn btn-default dropdown-toggle" tabindex="-1" data-toggle="dropdown">
                                {$language.iso_code|escape:'htmlall':'UTF-8'}
                                <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu">
                                {foreach from=$languages item=lang}
                                <li><a class="currentLang" data-id="{$lang.id_lang|escape:'htmlall':'UTF-8'}" href="javascript:hideOtherLanguage({$lang.id_lang|escape:'htmlall':'UTF-8'});" tabindex="-1">{$lang.name|escape:'htmlall':'UTF-8'}</a></li>
                                {/foreach}
                            </ul>
                        </div>
                    {/if}
                </div>
                {if $languages|count > 1}
                    </div>
                {/if}
            {/foreach}
            {* END COOKIE BANNER TEXT *}

            {* START COOKIE BANNER FONT TEXT *}
            <div class="form-group">
                <div class="col-xs-12 col-sm-12 col-md-5 col-lg-3">
                    <div class="text-right">
                        <label class="boldtext control-label">{l s='Font text' mod='pscookiebanner'}</label>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-12 col-md-7 col-lg-2">
                    <select class="" name="CB-FONT-STYLE" id="CB-FONT-STYLE">
                        {foreach from=$fonts key='key' item='font'}
                            <option style="font-family: '{$font|escape:'htmlall':'UTF-8'}'" value="{$font|escape:'htmlall':'UTF-8'}" {if $CB_FONT_STYLE eq $font}selected{/if}>{$font|escape:'htmlall':'UTF-8'}</option>
                        {/foreach}
                    </select>
                </div>
            </div>
            {* END COOKIE BANNER FONT TEXT *}

            {* START COOKIE BANNER TEXT COLOR *}
            <div class="form-group">
                <div class="col-xs-12 col-sm-12 col-md-5 col-lg-3">
                    <div class="text-right">
                        <label class="boldtext control-label">{l s='Text color' mod='pscookiebanner'}</label>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-12 col-md-7 col-lg-2">
                    <div class="input-group">
                        <input type="text" data-hex="true" class="color mColorPickerInput mColorPicker" name="CB-TEXT-COLOR" value="{$CB_TEXT_COLOR|escape:'htmlall':'UTF-8'}" id="color_3" style="background-color:{$CB_TEXT_COLOR|escape:'htmlall':'UTF-8'}; color: black;"><span style="cursor:pointer;" id="icp_color_3" class="mColorPickerTrigger input-group-addon" data-mcolorpicker="true"><img src="../img/admin/color.png" style="border:0;margin:0 0 0 3px" align="absmiddle"></span>
                    </div>
                </div>
            </div>
            {* END COOKIE BANNER TEXT COLOR *}

            {* START COOKIE BANNER FONT SIZE *}
            <div class="form-group">
                <div class="col-xs-12 col-sm-12 col-md-5 col-lg-3">
                    <div class="text-right">
                        <label for="buylater_title_text" class="control-label">
                                {l s='Font size' mod='pscookiebanner'}
                        </label>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-12 col-md-7 col-lg-1">
                    <input class="cookiebanner-font-size" required="required" value="{$CB_FONT_SIZE|escape:'htmlall':'UTF-8'}" type="number" name="CB-FONT-SIZE" min="0" max="50">
                </div>
            </div>
            {* END COOKIE BANNER FONT SIZE *}

            {* START COOKIE BANNER MORE INFORMATION LINK PAGE *}
            {foreach from=$languages item=language}
                {if $languages|count > 1}
                    <div class="translatable-field lang-{$language.id_lang|escape:'htmlall':'UTF-8'}" {if $language.id_lang != $defaultFormLanguage}style="display:none"{/if}>
                {/if}
                <div class="form-group">
                    <div class="col-xs-12 col-sm-12 col-md-5 col-lg-3">
                        <div class="text-right">
                            <label for="buylater_title_text" class="control-label">
                                    {l s='More information link text' mod='pscookiebanner'}
                            </label>
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-7 col-lg-3">
                        <input class="CB-LINK-TEXT" required="required" type="text" value="{$CB_LINK_TEXT[$language.id_lang]|escape:'htmlall':'UTF-8'}" name="CB-LINK-TEXT-{$language.id_lang|escape:'htmlall':'UTF-8'}" placeholder="{l s='More information here' mod='pscookiebanner'}">
                    </div>
                    {if $languages|count > 1}
                        <div class="col-xs-12 col-sm-12 col-md-7 col-lg-3">
                            <button type="button" class="btn btn-default dropdown-toggle" tabindex="-1" data-toggle="dropdown">
                                {$language.iso_code|escape:'htmlall':'UTF-8'}
                                <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu">
                                {foreach from=$languages item=lang}
                                <li><a class="currentLang" data-id="{$lang.id_lang|escape:'htmlall':'UTF-8'}" href="javascript:hideOtherLanguage({$lang.id_lang|escape:'htmlall':'UTF-8'});" tabindex="-1">{$lang.name|escape:'htmlall':'UTF-8'}</a></li>
                                {/foreach}
                            </ul>
                        </div>
                    {/if}
                </div>
                {if $languages|count > 1}
                    </div>
                {/if}
            {/foreach}
            {* END COOKIE BANNER MORE INFORMATION LINK PAGE *}

            {* START COOKIE BANNER MORE INFORMATION LINK COLOR *}
            <div class="form-group">
                <div class="col-xs-12 col-sm-12 col-md-5 col-lg-3">
                    <div class="text-right">
                        <label class="boldtext control-label">{l s='More information link color' mod='pscookiebanner'}</label>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-12 col-md-7 col-lg-2">
                    <div class="input-group">
                        <input type="text" data-hex="true" class="color mColorPickerInput mColorPicker" name="CB-MORE-INF-LINK-COLOR" value="{$CB_MORE_INF_LINK_COLOR|escape:'htmlall':'UTF-8'}" id="color_6" style="background-color:{$CB_MORE_INF_LINK_COLOR|escape:'htmlall':'UTF-8'}; color: black;"><span style="cursor:pointer;" id="icp_color_6" class="mColorPickerTrigger input-group-addon" data-mcolorpicker="true"><img src="../img/admin/color.png" style="border:0;margin:0 0 0 3px" align="absmiddle"></span>
                    </div>
                </div>
            </div>
            {* END COOKIE BANNER MORE INFORMATION LINK COLOR *}

            {* START COOKIE BANNER CMS *}
            <div class="form-group">
                <div class="col-xs-12 col-sm-12 col-md-5 col-lg-3">
                    <div class="text-right">
                        <label class="boldtext control-label">{l s='More information CMS page' mod='pscookiebanner'}</label>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-12 col-md-7 col-lg-2">
                    <select class="" name="CB-CMS" id="CB-CMS">
                        {foreach from=$cms key='key' item='page'}
                            <option value="{$cms.$key.id_cms|escape:'htmlall':'UTF-8'}" {if $CB_CMS eq $cms.$key.id_cms}selected{/if}>{$cms.$key.meta_title|escape:'htmlall':'UTF-8'}</option>
                        {/foreach}
                    </select>
                </div>
            </div>
            {* END COOKIE BANNER CMS *}

            {* START COOKIE BANNER ACCEPT BUTTON TEXT *}
            {foreach from=$languages item=language}
                {if $languages|count > 1}
                    <div class="translatable-field lang-{$language.id_lang|escape:'htmlall':'UTF-8'}" {if $language.id_lang != $defaultFormLanguage}style="display:none"{/if}>
                {/if}
                <div class="form-group">
                    <div class="col-xs-12 col-sm-12 col-md-5 col-lg-3">
                        <div class="text-right">
                            <label for="buylater_button_title_text" class="control-label">
                                    {l s='Accept button text' mod='pscookiebanner'}
                            </label>
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-7 col-lg-3">
                        <input class="CB-BUTTON-TEXT" required="required" type="text" value="{$CB_BUTTON_TEXT[$language.id_lang]|escape:'htmlall':'UTF-8'}" name="CB-BUTTON-TEXT-{$language.id_lang|escape:'htmlall':'UTF-8'}" placeholder="{l s='I accept' mod='pscookiebanner'}">
                    </div>
                    {if $languages|count > 1}
                        <div class="col-xs-12 col-sm-12 col-md-7 col-lg-3">
                            <button type="button" class="btn btn-default dropdown-toggle" tabindex="-1" data-toggle="dropdown">
                                {$language.iso_code|escape:'htmlall':'UTF-8'}
                                <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu">
                                {foreach from=$languages item=lang}
                                <li><a class="currentLang" data-id="{$lang.id_lang|escape:'htmlall':'UTF-8'}" href="javascript:hideOtherLanguage({$lang.id_lang|escape:'htmlall':'UTF-8'});" tabindex="-1">{$lang.name|escape:'htmlall':'UTF-8'}</a></li>
                                {/foreach}
                            </ul>
                        </div>
                    {/if}
                </div>
                {if $languages|count > 1}
                    </div>
                {/if}
            {/foreach}
            {* END COOKIE BANNER ACCEPT BUTTON TEXT *}

            {* START COOKIE BANNER BUTTON BACKGROUND COLOR *}
            <div class="form-group">
                <div class="col-xs-12 col-sm-12 col-md-5 col-lg-3">
                    <div class="text-right">
                        <label class="boldtext control-label">{l s='Button background color' mod='pscookiebanner'}</label>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-12 col-md-7 col-lg-2">
                    <div class="input-group">
                        <input type="text" data-hex="true" class="color mColorPickerInput mColorPicker" name="CB-BUTTON-BG-COLOR" value="{$CB_BUTTON_BG_COLOR|escape:'htmlall':'UTF-8'}" id="color_2" style="background-color:{$CB_BUTTON_BG_COLOR|escape:'htmlall':'UTF-8'}; color: black;"><span style="cursor:pointer;" id="icp_color_2" class="mColorPickerTrigger input-group-addon" data-mcolorpicker="true"><img src="../img/admin/color.png" style="border:0;margin:0 0 0 3px" align="absmiddle"></span>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-12 col-md-5 col-lg-2">
                    <div class="text-right">
                        <label class="control-label">
                            <span class="label-tooltip" data-toggle="tooltip" data-html="true" data-original-title="{l s='This color will be displayed on the mouse over' mod='pscookiebanner'}">
                                {l s='on mouse over' mod='pscookiebanner'}
                            </span>
                        </label>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-12 col-md-7 col-lg-2">
                    <div class="input-group">
                        <input type="text" data-hex="true" class="color mColorPickerInput mColorPicker" name="CB-BUTTON-BG-COLOR-HOVER" value="{$CB_BUTTON_BG_COLOR_HOVER|escape:'htmlall':'UTF-8'}" id="color_4" style="background-color:{$CB_BUTTON_BG_COLOR_HOVER|escape:'htmlall':'UTF-8'}; color: black;"><span style="cursor:pointer;" id="icp_color_4" class="mColorPickerTrigger input-group-addon" data-mcolorpicker="true"><img src="../img/admin/color.png" style="border:0;margin:0 0 0 3px" align="absmiddle"></span>
                    </div>
                    {* <div class="help-block">
                        {l s='Color will be display on mouse over' mod='pscookiebanner'}
                    </div> *}
                </div>
            </div>
            {* END COOKIE BANNER BUTTON BACKGROUND COLOR *}

            {* START COOKIE BANNER BUTTON TEXT COLOR *}
            <div class="form-group">
                <div class="col-xs-12 col-sm-12 col-md-5 col-lg-3">
                    <div class="text-right">
                        <label class="boldtext control-label">{l s='Button text color' mod='pscookiebanner'}</label>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-12 col-md-7 col-lg-2">
                    <div class="input-group">
                        <input type="text" data-hex="true" class="color mColorPickerInput mColorPicker" name="CB-BUTTON-TEXT-COLOR" value="{$CB_BUTTON_TEXT_COLOR|escape:'htmlall':'UTF-8'}" id="color_5" style="background-color:{$CB_BUTTON_TEXT_COLOR|escape:'htmlall':'UTF-8'}; color: black;"><span style="cursor:pointer;" id="icp_color_5" class="mColorPickerTrigger input-group-addon" data-mcolorpicker="true"><img src="../img/admin/color.png" style="border:0;margin:0 0 0 3px" align="absmiddle"></span>
                    </div>
                </div>
            </div>
            {* END COOKIE BANNER BUTTON TEXT COLOR *}

            {* START COOKIE BANNER LOOP *}
            <div class="form-group">
                <div class="col-xs-12 col-sm-12 col-md-5 col-lg-3">
                    <div class="text-right">
                        <label class="control-label">
                                {l s='Redisplay cookie banner every ' mod='pscookiebanner'}
                        </label>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-12 col-md-7 col-lg-1">
                    <input class="cookiebanner-font-size" required="required" value="{$CB_FONT_LOOP|escape:'htmlall':'UTF-8'}" type="number" name="CB-FONT-LOOP" min="0" max="13">
                </div>
                <div class="col-xs-12 col-sm-12 col-md-5 col-lg-3">
                    <div class="cb-month-left">
                        <label class="control-label">
                                {l s='months' mod='pscookiebanner'}
                        </label>
                    </div>
                </div>
            </div>
            {* END COOKIE BANNER LOOP *}
        </div>
        <div class="panel-footer">
            <button type="submit" value="1" id="submitCookieBannerModule" name="submitCookieBannerModule" class="btn btn-default pull-right">
                <i class="process-icon-save"></i> {l s='Save' mod='pscookiebanner'}
            </button>
        </div>
    </form>
</div>

<div id="cookiebanner-container" class="eupopup-container">
    <div class="eupopup-head"></div>
        <div class="eupopup-body">
            <div class="eupopup-body-text" style="display: inline;">
            </div>
            <div class="eupopup-body-link" style="display: inline;">
                <a id="learn_more" href="" target="_blank" class="eupopup-learn-more">test</a>
            </div>
            <div class="eupopup-buttons">
                <button type="button" class="eupopup-button eupopup-button_1"></button>
                <div class="clearfix"></div>
            </div>
        </div>
    <a href="#" class="eupopup-closebutton"><i class="fa fa-close"></i></a>
</div>
