{*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2018 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<form class="defaultForm form-horizontal">
    <fieldset style="width: 700px; margin: 0 auto;">
        <legend>{l s='Installation' mod='recaptcha'}</legend>
        <div class="panel" id="fieldset_0">
            <div class="form-wrapper">
				{l s='Your version of prestashop requires adding the tag {$HOOK_CONTACT_FORM_BOTTOM} on the contact form: your_theme / contact-form.tpl' mod='recaptcha'}
            </div>
        </div>
    </fieldset>
</form>

<br/>

<form id="module_form" class="defaultForm form-horizontal" action="{$current_url|escape:'html':'UTF-8'}" method="post" enctype="multipart/form-data" novalidate="">
    <fieldset style="width: 700px; margin: 0 auto;">
        <legend>{l s='Configuration' mod='recaptcha'}</legend>
        <div class="panel" id="fieldset_0">
            <div class="form-wrapper">
                <div class="form-group">
                    <label class="control-label col-lg-3">{l s='Site key' mod='recaptcha'}</label>
                    <div class="col-lg-9 ">
                        <input type="text" name="captcha_public_key" id="captcha_public_key" value="{if isset($smarty.post.captcha_public_key)}{$smarty.post.captcha_public_key|escape:'html':'UTF-8'}{else}{$captcha_public_key|escape:'html':'UTF-8'}{/if}">
                    </div>
                </div>
                <br/>
                <div class="form-group">
                    <label class="control-label col-lg-3">{l s='Secret key' mod='recaptcha'}</label>
                    <div class="col-lg-9 ">
                        <input type="text" name="captcha_private_key" id="captcha_private_key" value="{if isset($smarty.post.captcha_private_key)}{$smarty.post.captcha_private_key|escape:'html':'UTF-8'}{else}{$captcha_private_key|escape:'html':'UTF-8'}{/if}">

                        <p class="help-block">{l s='To get your own key pair please click on the following link' mod='recaptcha'}<br>
                        <a href="https://www.google.com/recaptcha/admin#whyrecaptcha" target="_blank">https://www.google.com/recaptcha/admin#whyrecaptcha</a></p>
                    </div>
                </div>
                <br/>
                <div class="form-group">
                    <label class="control-label col-lg-3">{l s='Enable Captcha for account creation' mod='recaptcha'}</label>
                    <div class="col-lg-9">
                        <div class="radio">
                            <label><input type="radio" name="captcha_enable_account" id="active_on" value="1"{if (isset($smarty.post.captcha_enable_account) && $smarty.post.captcha_enable_account) || $captcha_enable_account} checked="checked"{/if}/>{l s='Yes' mod='recaptcha'}</label>
                        </div>
                        <div class="radio">
                            <label><input type="radio" name="captcha_enable_account" id="active_off" value="0"{if (isset($smarty.post.captcha_enable_account) && !$smarty.post.captcha_enable_account) || !$captcha_enable_account} checked="checked"{/if}/>{l s='No' mod='recaptcha'}</label>
                        </div>
                    </div>
                </div>
                <br/><br/><br/>
                <div class="form-group">
                    <label class="control-label col-lg-3">{l s='Enable Captcha on contact form' mod='recaptcha'}</label>
                    <div class="col-lg-9 ">
                        <div class="radio ">
                            <label><input type="radio" name="captcha_enable_contact" id="active_on" value="1"{if (isset($smarty.post.captcha_enable_contact) && $smarty.post.captcha_enable_contact) || $captcha_enable_contact} checked="checked"{/if}/>{l s='Yes' mod='recaptcha'}</label>
                        </div>
                        <div class="radio ">
                            <label><input type="radio" name="captcha_enable_contact" id="active_off" value="0"{if (isset($smarty.post.captcha_enable_contact) && !$smarty.post.captcha_enable_contact) || !$captcha_enable_contact} checked="checked"{/if}/>{l s='No' mod='recaptcha'}</label>
                        </div>
                    </div>
                </div>
            </div>
            <br/><br/><br/>
            <div class="panel-footer">
                <button type="submit" value="1" id="module_form_submit_btn" name="SubmitCaptchaConfiguration" class="btn btn-default pull-right">{l s='Save' mod='recaptcha'}</button>
            </div>
        </div>
    </fieldset>
</form>