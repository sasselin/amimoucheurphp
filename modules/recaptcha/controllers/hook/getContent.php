<?php
/**
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
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2018 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

class RecaptchaGetContentController
{
    public function __construct($module, $file, $path)
    {
        $this->file = $file;
        $this->module = $module;
        $this->context = Context::getContext();
        $this->_path = $path;
    }

    //Save the form result
    public function postProcess()
    {
        if (Tools::isSubmit('SubmitCaptchaConfiguration')) {
            Configuration::updateValue('CAPTCHA_PUBLIC_KEY', pSQL(Tools::getValue('captcha_public_key')));
            Configuration::updateValue('CAPTCHA_PRIVATE_KEY', pSQL(Tools::getValue('captcha_private_key')));
            Configuration::updateValue('CAPTCHA_ENABLE_ACCOUNT', (int)Tools::getValue('captcha_enable_account'));
            Configuration::updateValue('CAPTCHA_ENABLE_CONTACT', (int)Tools::getValue('captcha_enable_contact'));
            Configuration::updateValue('CAPTCHA_VERSION', pSQL(Tools::getValue('captcha_version')));
            Configuration::updateValue('CAPTCHA_OVERLOAD', pSQL(Tools::getValue('captcha_overload')));
            Configuration::updateValue('CAPTCHA_THEME', pSQL(Tools::getValue('captcha_theme')));

           // $this->module->clearSmartyCache();

            Tools::redirectAdmin(
                'index.php?tab=AdminModules&configure='.$this->module->name.
                '&tab_module='.$this->module->tab.
                '&module_name='.$this->module->name.
                '&token='.Tools::getAdminTokenLite('AdminModules').
                '&conf=4'
            );
        }
    }


    public function getConfigFieldsValues()
    {
        return array(
            'captcha_private_key' => Tools::getValue('captcha_private_key', Configuration::get('CAPTCHA_PRIVATE_KEY')),
            'captcha_public_key' => Tools::getValue('captcha_public_key', Configuration::get('CAPTCHA_PUBLIC_KEY')),
            'captcha_enable_account' => Tools::getValue('captcha_enable_account', Configuration::get('CAPTCHA_ENABLE_ACCOUNT')),
            'captcha_enable_contact' => Tools::getValue('captcha_enable_contact', Configuration::get('CAPTCHA_ENABLE_CONTACT')),
            'captcha_version' => Tools::getValue('captcha_version', Configuration::get('CAPTCHA_VERSION')),
            'captcha_overload' => Tools::getValue('captcha_overload', Configuration::get('CAPTCHA_OVERLOAD')),
            'captcha_theme' => Tools::getValue('captcha_theme', Configuration::get('CAPTCHA_THEME'))
        );
    }


    //Form configuration backoffice
    public function getContent()
    {
        $this->postProcess();

        //1.4 : No overload possible, no formhelper
        if (version_compare(_PS_VERSION_, '1.5', '<')) {
            $this->context->smarty->assign(array(
                'current_url' => 'index.php?tab=AdminModules&configure='.$this->module->name.
                    '&tab_module='.$this->module->tab.
                    '&module_name='.$this->module->name.
                    '&token='.Tools::getAdminTokenLite('AdminModules'),
                'captcha_public_key' => Configuration::get('CAPTCHA_PUBLIC_KEY'),
                'captcha_private_key' => Configuration::get('CAPTCHA_PRIVATE_KEY'),
                'captcha_enable_account' => Configuration::get('CAPTCHA_ENABLE_ACCOUNT'),
                'captcha_enable_contact' => Configuration::get('CAPTCHA_ENABLE_CONTACT'),
                'captcha_version' => Configuration::get('CAPTCHA_VERSION'),
                'captcha_theme' => Configuration::get('CAPTCHA_THEME')
            ));

            $urlGab = '/views/templates/admin/configure_ps14.tpl';
            $urlModule = _PS_MODULE_DIR_.$this->module->name;

            return $this->module->display($urlModule, $urlGab);
        } else {
            //1.5 : Switch no exist
            if (version_compare(_PS_VERSION_, '1.6', '<')) {
                $fields_form = array(
                'form' => array(
                    'legend' => array(
                        'title' => $this->module->l('Captcha Configuration'),
                        'icon' => 'icon-cogs'
                    ),
                    'input' => array(
                        array(
                            'type' => 'text',
                            'label' => $this->module->l('Site key'),
                            'name' => 'captcha_public_key'
                        ),
                        array(
                            'type' => 'text',
                            'label' => $this->module->l('Secret key'),
                            'name' => 'captcha_private_key',
                            'desc' => $this->module->l('To get your own key pair please click on the following link').
                                '<br /><a href="https://www.google.com/recaptcha/admin#whyrecaptcha" target="_blank">https://www.google.com/recaptcha/admin#whyrecaptcha</a>',
                        ),
                        array(
                            'type'      => 'radio',
                            'label'     => $this->module->l('Enable Captcha for account creation'),
                            'name'      => 'captcha_enable_account',
                            'is_bool'   => true,
                            'class'     => 't',
                            'values'    => array(
                                array(
                                    'id'    => 'active_on',
                                    'value' => 1,
                                    'label' => $this->module->l('Yes')
                                ),
                                array(
                                    'id'    => 'active_off',
                                    'value' => 0,
                                    'label' => $this->module->l('No')
                                )
                            )
                        ),
                        array(
                            'type'      => 'radio',
                            'label'     => $this->module->l('Enable Captcha on contact form'),
                            'name'      => 'captcha_enable_contact',
                            'is_bool'   => true,
                            'class'     => 't',
                            'values'    => array(
                                array(
                                    'id'    => 'active_on',
                                    'value' => 1,
                                    'label' => $this->module->l('Yes')
                                ),
                                array(
                                    'id'    => 'active_off',
                                    'value' => 0,
                                    'label' => $this->module->l('No')
                                )
                            )
                        ),
                        array(
                          'type'    => 'checkbox',
                          'label'   => '',
                          'desc'    => $this->module->l('If checked, replaces your contact form. If you uncheck this box, you must place the tag with the tag {$HOOK_CONTACT_FORM_BOTTOM} within the HTML tags of the form of the contact-form.tpl file of the active theme.'),
                          'name'    => 'captcha',
                          'values'  => array(
                            'query' => array(array('id_option'=>'overload', 'name' => $this->module->l('Overload the contact-form template'), 'val' => '1')),
                            'id'    => 'id_option',
                            'name'  => 'name'
                          ),
                        ),
                        array(
                            'type' => 'select',
                            'label' => $this->module->l('Type'),
                            'name' => 'captcha_version',
                            'options' => array(
                                'query' => array(
                                    array(
                                        'id' => 'v2',
                                        'name' => $this->module->l('Recaptcha V2')
                                    )/*
,
                                    array(
                                        'id' => 'invisible',
                                        'name' => $this->module->l('Recaptcha Invisible')
                                    )
*/
                                ),
                                'id' => 'id',
                                'name' => 'name',
                            )
                        ),
                        array(
                            'type' => 'select',
                            'label' => $this->module->l('Theme'),
                            'name' => 'captcha_theme',
                            'options' => array(
                                'query' => array(
                                    array(
                                        'id' => 'dark',
                                        'name' => $this->module->l('Dark')
                                    ),
                                    array(
                                        'id' => 'light',
                                        'name' => $this->module->l('Light')
                                    )

                                ),
                                'id' => 'id',
                                'name' => 'name',
                            )
                        ),
                    ),
                    'submit' => array(
                        'title' => $this->module->l('Save'),
                        'name' => 'SubmitCaptchaConfiguration'
                    )
                ),
                );
            } //1.6
            elseif (version_compare(_PS_VERSION_, '1.7', '<')) {
                $fields_form = array(
                'form' => array(
                    'legend' => array(
                        'title' => $this->module->l('Captcha Configuration'),
                        'icon' => 'icon-cogs'
                    ),
                    'input' => array(
                        array(
                            'type' => 'text',
                            'label' => $this->module->l('Site key'),
                            'name' => 'captcha_public_key'
                        ),
                        array(
                            'type' => 'text',
                            'label' => $this->module->l('Secret key'),
                            'name' => 'captcha_private_key',
                            'desc' => $this->module->l('To get your own key pair please click on the following link').
                                '<br /><a href="https://www.google.com/recaptcha/admin#whyrecaptcha" target="_blank">https://www.google.com/recaptcha/admin#whyrecaptcha</a>',
                        ),
                        array(
                            'type'      => 'switch',
                            'label'     => $this->module->l('Enable Captcha for account creation'),
                            'name'      => 'captcha_enable_account',
                            'is_bool'   => true,
                            'class'     => 't',
                            'values'    => array(
                                array(
                                    'id'    => 'active_on',
                                    'value' => 1,
                                    'label' => $this->module->l('Yes')
                                ),
                                array(
                                    'id'    => 'active_off',
                                    'value' => 0,
                                    'label' => $this->module->l('No')
                                )
                            )
                        ),
                        array(
                            'type'      => 'switch',
                            'label'     => $this->module->l('Enable Captcha on contact form'),
                            'name'      => 'captcha_enable_contact',
                            'is_bool'   => true,
                            'class'     => 't',
                            'values'    => array(
                                array(
                                    'id'    => 'active_on',
                                    'value' => 1,
                                    'label' => $this->module->l('Yes')
                                ),
                                array(
                                    'id'    => 'active_off',
                                    'value' => 0,
                                    'label' => $this->module->l('No')
                                )
                            )
                        ),
                        array(
                          'type'    => 'checkbox',
                          'label'   => '',
                          'desc'    => $this->module->l('If checked, replaces your contact form. If you uncheck this box, you must place the tag with the tag {$ HOOK_CONTACT_FORM_BOTTOM} within the HTML tags of the form of the contact-form.tpl file of the active theme.'),
                          'name'    => 'captcha',
                          'values'  => array(
                            'query' => array(array('id_option'=>'overload', 'name' => $this->module->l('Overload the contact-form template'), 'val' => '1')),
                            'id'    => 'id_option',
                            'name'  => 'name'
                          ),
                        ),
                        array(
                            'type' => 'select',
                            'label' => $this->module->l('Type'),
                            'name' => 'captcha_version',
                            'options' => array(
                                'query' => array(
                                    array(
                                        'id' => 'v2',
                                        'name' => $this->module->l('Recaptcha V2')
                                    )/*
,
                                    array(
                                        'id' => 'invisible',
                                        'name' => $this->module->l('Recaptcha Invisible')
                                    )
*/
                                ),
                                'id' => 'id',
                                'name' => 'name',
                            )
                        ),
                        array(
                            'type' => 'select',
                            'label' => $this->module->l('Theme'),
                            'name' => 'captcha_theme',
                            'options' => array(
                                'query' => array(
                                    array(
                                        'id' => 'dark',
                                        'name' => $this->module->l('Dark')
                                    ),
                                    array(
                                        'id' => 'light',
                                        'name' => $this->module->l('Light')
                                    )

                                ),
                                'id' => 'id',
                                'name' => 'name',
                            )
                        ),
                    ),
                    'submit' => array(
                        'title' => $this->module->l('Save'),
                        'name' => 'SubmitCaptchaConfiguration'
                    )
                ),
                );
            } //1.7 : Overload of contact-form no required
            elseif (version_compare(_PS_VERSION_, '1.7.0', '>=')) {
                $fields_form = array(
                'form' => array(
                    'legend' => array(
                        'title' => $this->module->l('Captcha Configuration'),
                        'icon' => 'icon-cogs'
                    ),
                    'input' => array(
                        array(
                            'type' => 'text',
                            'label' => $this->module->l('Site key'),
                            'name' => 'captcha_public_key'
                        ),
                        array(
                            'type' => 'text',
                            'label' => $this->module->l('Secret key'),
                            'name' => 'captcha_private_key',
                            'desc' => $this->module->l('To get your own key pair please click on the following link').
                                '<br /><a href="https://www.google.com/recaptcha/admin#whyrecaptcha" target="_blank">https://www.google.com/recaptcha/admin#whyrecaptcha</a>',
                        ),
                        array(
                            'type'      => 'switch',
                            'label'     => $this->module->l('Enable Captcha for account creation'),
                            'name'      => 'captcha_enable_account',
                            'is_bool'   => true,
                            'class'     => 't',
                            'values'    => array(
                                array(
                                    'id'    => 'active_on',
                                    'value' => 1,
                                    'label' => $this->module->l('Yes')
                                ),
                                array(
                                    'id'    => 'active_off',
                                    'value' => 0,
                                    'label' => $this->module->l('No')
                                )
                            )
                        ),
                        array(
                            'type'      => 'switch',
                            'label'     => $this->module->l('Enable Captcha on contact form'),
                            'name'      => 'captcha_enable_contact',
                            'is_bool'   => true,
                            'class'     => 't',
                            'values'    => array(
                                array(
                                    'id'    => 'active_on',
                                    'value' => 1,
                                    'label' => $this->module->l('Yes')
                                ),
                                array(
                                    'id'    => 'active_off',
                                    'value' => 0,
                                    'label' => $this->module->l('No')
                                )
                            )
                        ),
                        array(
                            'type' => 'select',
                            'label' => $this->module->l('Type'),
                            'name' => 'captcha_version',
                            'options' => array(
                                'query' => array(
                                    array(
                                        'id' => 'v2',
                                        'name' => $this->module->l('Recaptcha V2')
                                    )/*
,
                                    array(
                                        'id' => 'invisible',
                                        'name' => $this->module->l('Recaptcha Invisible')
                                    )
*/
                                ),
                                'id' => 'id',
                                'name' => 'name',
                            )
                        ),
                        array(
                            'type' => 'select',
                            'label' => $this->module->l('Theme'),
                            'name' => 'captcha_theme',
                            'options' => array(
                                'query' => array(
                                    array(
                                        'id' => 'dark',
                                        'name' => $this->module->l('Dark')
                                    ),
                                    array(
                                        'id' => 'light',
                                        'name' => $this->module->l('Light')
                                    )

                                ),
                                'id' => 'id',
                                'name' => 'name',
                            )
                        ),
                    ),
                    'submit' => array(
                        'title' => $this->module->l('Save'),
                        'name' => 'SubmitCaptchaConfiguration'
                    )
                ),
                );
            }

            $helper = new HelperForm();
            $helper->show_toolbar = false;
            $helper->table = 'recaptcha';
            $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
            $helper->default_form_language = $lang->id;
           // $helper->module = $this;
            $helper->allow_employee_form_lang = (int)Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG');
            //$helper->identifier = $this->module->identifier;
            $helper->submit_action = 'submit_recaptcha_form';
            $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).
                '&configure='.$this->module->name.'&tab_module='.$this->module->tab.'&module_name='.$this->module->name;
            $helper->token = Tools::getAdminTokenLite('AdminModules');
            $helper->tpl_vars = array(
                'uri' => $this->module->getPathUri(),
                'fields_value' => $this->getConfigFieldsValues(),
                'languages' => $this->context->controller->getLanguages(),
                'id_language' => $this->context->language->id
            );
            return $helper->generateForm(array($fields_form));
        }
    }


    public function run()
    {
        $this->postProcess();
        $html_form = $this->getContent();
        return $html_form;
    }
}
