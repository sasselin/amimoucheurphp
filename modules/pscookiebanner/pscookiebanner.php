<?php
/**
* 2007-2017 PrestaShop
*
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
*  @copyright 2007-2017 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class Pscookiebanner extends Module
{
    protected $config_form = false;
    protected $front_controller = null;

    public $fontsCss = array(
        'https://fonts.googleapis.com/css?family=Roboto', // font-family: 'Roboto', sans-serif;
        'https://fonts.googleapis.com/css?family=Hind', // font-family: 'Hind', sans-serif;
        'https://fonts.googleapis.com/css?family=Maven+Pro', // font-family: 'Maven Pro', sans-serif;
        'https://fonts.googleapis.com/css?family=Noto+Serif', // font-family: 'Noto Serif', serif;
        'https://fonts.googleapis.com/css?family=Bitter', // font-family: 'Bitter', serif;
        'https://fonts.googleapis.com/css?family=Forum', // font-family: 'Forum', serif;
    );
    public $fonts = array(1 => 'Roboto', 2 => 'Hind', 3 => 'Maven Pro', 4 => 'Noto Serif', 5 => 'Bitter', 6 => 'Forum');

    public function __construct()
    {
        $this->name = 'pscookiebanner';
        $this->tab = 'administration';
        $this->version = '1.0.8';
        $this->author = 'PrestaShop';
        $this->need_instance = 0;

        $this->module_key = '7c707e5791af499b0fb5983803599bb3';
        $this->author_address = '0x64aa3c1e4034d07015f639b0e171b0d7b27d01aa';

        $link = new Link();
        $this->front_controller = $link->getModuleLink($this->name, 'FrontAjaxCookiebanner', array(), true);
        $this->bootstrap = true;

        parent::__construct();

        $this->output = '';

        $this->displayName = $this->l('Cookie Banner');
        $this->description = $this->l('This module allows you to display a cookie banner on your website');

        $this->js_path = $this->_path.'views/js/';
        $this->css_path = $this->_path.'views/css/';
        $this->img_path = $this->_path.'views/img/';
        $this->logo_path = $this->_path.'logo.png';
        $this->docs_path = $this->_path.'docs/';
        $this->module_path = $this->_path;

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall this module?');
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        $languages = Language::getLanguages(false);

        Configuration::updateValue('CB-POSITION', 'bottom');
        Configuration::updateValue('CB-BG-COLOR', '#17191c');
        Configuration::updateValue('CB-BG-OPACITY', '0.85');
        Configuration::updateValue('CB-FONT-STYLE', '4');
        Configuration::updateValue('CB-TEXT-COLOR', '#efefef');
        Configuration::updateValue('CB-FONT-SIZE', '12');
        Configuration::updateValue('CB-FONT-LOOP', '1');
        Configuration::updateValue('CB-CMS', '2');
        Configuration::updateValue('CB-BUTTON-BG-COLOR', '#25B9D7');
        Configuration::updateValue('CB-BUTTON-BG-COLOR-HOVER', '#1e94ab');
        Configuration::updateValue('CB-BUTTON-TEXT-COLOR', '#ffffff');
        Configuration::updateValue('CB-MORE-INF-LINK-COLOR', '#25B9D7');

        $values = array();
        foreach ($languages as $lang) {
            switch ($lang['iso_code']) {
                case 'en':
                    $values['CB-TEXT'][$lang['id_lang']] = 'To give you the best possible experience, this site uses cookies. Using your site means your agree to our use of cookies. We have published a new cookies policy, which you should need to find out more about the cookies we use.';
                    $values['CB-LINK-TEXT'][$lang['id_lang']] = 'View cookies policy.';
                    $values['CB-BUTTON-TEXT'][$lang['id_lang']] = 'Accept';
                    break;
                case 'gb':
                    $values['CB-TEXT'][$lang['id_lang']] = 'To give you the best possible experience, this site uses cookies. Using your site means your agree to our use of cookies. We have published a new cookies policy, which you should need to find out more about the cookies we use.';
                    $values['CB-LINK-TEXT'][$lang['id_lang']] = 'View cookies policy.';
                    $values['CB-BUTTON-TEXT'][$lang['id_lang']] = 'Accept';
                    break;
                case 'fr':
                    $values['CB-TEXT'][$lang['id_lang']] = 'En poursuivant votre navigation sur ce site, vous acceptez l\'utilisation de Cookies pour vous proposer des publicités ciblées adaptées à vos centres d\'intérêts et réaliser des statistiques de visites.';
                    $values['CB-LINK-TEXT'][$lang['id_lang']] = 'En savoir plus.';
                    $values['CB-BUTTON-TEXT'][$lang['id_lang']] = 'Accepter';
                    break;
                case 'qc':
                    $values['CB-TEXT'][$lang['id_lang']] = 'En poursuivant votre navigation sur ce site, vous acceptez l\'utilisation de Cookies pour vous proposer des publicités ciblées adaptées à vos centres d\'intérêts et réaliser des statistiques de visites.';
                    $values['CB-LINK-TEXT'][$lang['id_lang']] = 'En savoir plus.';
                    $values['CB-BUTTON-TEXT'][$lang['id_lang']] = 'Accepter';
                    break;
                case 'es':
                    $values['CB-TEXT'][$lang['id_lang']] = 'Para mejorar al máximo tu experiencia, esta web utiliza cookies. Si utilizas la web significa que estás de acuerdo con que usemos cookies. Hemos publicado una nueva política de cookies, que deberás leer para entender mejor cuáles son las cookies que utilizamos.';
                    $values['CB-LINK-TEXT'][$lang['id_lang']] = 'Ver la política de cookies.';
                    $values['CB-BUTTON-TEXT'][$lang['id_lang']] = 'Ok';
                    break;
                case 'mx':
                    $values['CB-TEXT'][$lang['id_lang']] = 'Para mejorar al máximo tu experiencia, esta web utiliza cookies. Si utilizas la web significa que estás de acuerdo con que usemos cookies. Hemos publicado una nueva política de cookies, que deberás leer para entender mejor cuáles son las cookies que utilizamos.';
                    $values['CB-LINK-TEXT'][$lang['id_lang']] = 'Ver la política de cookies.';
                    $values['CB-BUTTON-TEXT'][$lang['id_lang']] = 'Ok';
                    break;
                case 'ag':
                    $values['CB-TEXT'][$lang['id_lang']] = 'Para mejorar al máximo tu experiencia, esta web utiliza cookies. Si utilizas la web significa que estás de acuerdo con que usemos cookies. Hemos publicado una nueva política de cookies, que deberás leer para entender mejor cuáles son las cookies que utilizamos.';
                    $values['CB-LINK-TEXT'][$lang['id_lang']] = 'Ver la política de cookies.';
                    $values['CB-BUTTON-TEXT'][$lang['id_lang']] = 'Ok';
                    break;
                case 'cb':
                    $values['CB-TEXT'][$lang['id_lang']] = 'Para mejorar al máximo tu experiencia, esta web utiliza cookies. Si utilizas la web significa que estás de acuerdo con que usemos cookies. Hemos publicado una nueva política de cookies, que deberás leer para entender mejor cuáles son las cookies que utilizamos.';
                    $values['CB-LINK-TEXT'][$lang['id_lang']] = 'Ver la política de cookies.';
                    $values['CB-BUTTON-TEXT'][$lang['id_lang']] = 'Ok';
                    break;
                case 'it':
                    $values['CB-TEXT'][$lang['id_lang']] = 'Per offrirti la miglior esperienza possibile, questo sito utilizza i cookie. Utilizzando il sito acconsenti all\'uso dei cookie. Abbiamo pubblicato una nuova informativa sui cookie, dove puoi trovare maggiori informazioni sui cookie che utilizziamo.';
                    $values['CB-LINK-TEXT'][$lang['id_lang']] = 'Vedi l\'informativa sui cookie.';
                    $values['CB-BUTTON-TEXT'][$lang['id_lang']] = 'Ok';
                    break;
                case 'de':
                    $values['CB-TEXT'][$lang['id_lang']] = 'Um Ihnen das bestmögliche Kundenerlebnis zu bieten, nutzt diese Seite Cookies. Indem Sie Ihre Seite nutzen, erklären Sie sich mit der Verwendung von Cookies einverstanden. Wir haben neue Cookies-Richtlinien veröffentlicht, in denen Sie mehr über die Cookies, die wir nutzen, erfahren können.';
                    $values['CB-LINK-TEXT'][$lang['id_lang']] = 'Cookies-Richtlinien lesen.';
                    $values['CB-BUTTON-TEXT'][$lang['id_lang']] = 'Ok';
                    break;
                case 'nl':
                    $values['CB-TEXT'][$lang['id_lang']] = 'Deze site gebruikt cookies voor een optimale gebruikerservaring. Als u onze website gebruikt gaan we ervan uit dat u akkoord gaat met ons gebruik van cookies. We hebben een nieuw cookiebeleid gepubliceerd dat u kunt raadplegen voor meer informatie over de gebruikte cookies.';
                    $values['CB-LINK-TEXT'][$lang['id_lang']] = 'Cookiebeleid nalezen.';
                    $values['CB-BUTTON-TEXT'][$lang['id_lang']] = 'Ok';
                    break;
                case 'pl':
                    $values['CB-TEXT'][$lang['id_lang']] = 'Niniejsza witryna korzysta z plików cookies, by umożliwić Ci jak największą wygodę korzystania. Dalsze korzystanie z niej oznacza, że wyrażasz zgodę na używanie tych plików. Opublikowaliśmy nowe zasady dotyczące plików cookies, w których dowiesz się więcej na ich temat.';
                    $values['CB-LINK-TEXT'][$lang['id_lang']] = 'Przeczytaj zasady dotyczące plików cookies.';
                    $values['CB-BUTTON-TEXT'][$lang['id_lang']] = 'Ok';
                    break;
                case 'pt':
                    $values['CB-TEXT'][$lang['id_lang']] = 'Para que você tenha a melhor experiência possível, este site usa cookies. O uso deste site significa que você concorda com a nossa utilização de cookies. Publicamos uma nova política de cookies, onde você pode saber mais sobre os cookies que utilizamos.';
                    $values['CB-LINK-TEXT'][$lang['id_lang']] = 'Veja a política de cookies.';
                    $values['CB-BUTTON-TEXT'][$lang['id_lang']] = 'Ok';
                    break;
                case 'br':
                    $values['CB-TEXT'][$lang['id_lang']] = 'Para que você tenha a melhor experiência possível, este site usa cookies. O uso deste site significa que você concorda com a nossa utilização de cookies. Publicamos uma nova política de cookies, onde você pode saber mais sobre os cookies que utilizamos.';
                    $values['CB-LINK-TEXT'][$lang['id_lang']] = 'Veja a política de cookies.';
                    $values['CB-BUTTON-TEXT'][$lang['id_lang']] = 'Ok';
                    break;
                default:
                    $values['CB-TEXT'][$lang['id_lang']] = 'To give you the best possible experience, this site uses cookies. Using your site means your agree to our use of cookies. We have published a new cookies policy, which you should need to find out more about the cookies we use.';
                    $values['CB-LINK-TEXT'][$lang['id_lang']] = 'View cookies policy.';
                    $values['CB-BUTTON-TEXT'][$lang['id_lang']] = 'Ok';
                    break;
            }
        }

        Configuration::updateValue('CB-TEXT', $values['CB-TEXT']);
        Configuration::updateValue('CB-LINK-TEXT', $values['CB-LINK-TEXT']);
        Configuration::updateValue('CB-BUTTON-TEXT', $values['CB-BUTTON-TEXT']);

        if (parent::install() && $this->registerHook('header')) {
            return true;
        } else {
            $this->_errors[] = $this->l('There was an error during the installation.
                Please contact us through Addons website');
            return false;
        }
    }

    public function uninstall()
    {
        Configuration::deleteByName('CB-POSITION');
        Configuration::deleteByName('CB-BG-COLOR');
        Configuration::deleteByName('CB-BG-OPACITY');
        Configuration::deleteByName('CB-FONT-STYLE');
        Configuration::deleteByName('CB-TEXT-COLOR');
        Configuration::deleteByName('CB-FONT-SIZE');
        Configuration::deleteByName('CB-FONT-LOOP');
        Configuration::deleteByName('CB-CMS');
        Configuration::deleteByName('CB-BUTTON-BG-COLOR');
        Configuration::deleteByName('CB-BUTTON-BG-COLOR-HOVER');
        Configuration::deleteByName('CB-BUTTON-TEXT-COLOR');
        Configuration::deleteByName('CB-MORE-INF-LINK-COLOR');
        Configuration::deleteByName('CB-TEXT');
        Configuration::deleteByName('CB-LINK-TEXT');
        Configuration::deleteByName('CB-BUTTON-TEXT');

        return parent::uninstall();
    }

    public function loadAsset()
    {
        $_controller = Context::getContext()->controller;
        // Load CSS
        $css = array(
            $this->css_path.'font-awesome.min.css',
            $this->css_path.'faq.css',
            $this->css_path.'front.css',
            $this->css_path.'bootstrap-slider.css',
            $this->css_path.'back.css',
            $this->css_path.'jquery-eu-cookie-law-popup.css',
        );

        $_controller->addCSS($this->fontsCss);

        $this->context->controller->addCSS($css, 'all');

        // Load JS
        $jss = array(
            $this->js_path.'faq.js',
            $this->js_path.'menu.js',
            $this->js_path.'front.js',
            $this->js_path.'bootstrap-slider.js',
            $this->js_path.'back.js',
        );

        $_controller->addJqueryPlugin('colorpicker');

        $this->context->controller->addJS($jss);

        // Clean memory
        unset($jss, $css);
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        $this->loadAsset();
        $this->postProcess();

        $id_lang = $this->context->language->id;
        $id_shop = $this->context->shop->id;

        $cms_link_page = Context::getContext()->link->getAdminLink('AdminCmsContent');

        // API FAQ Update
        include_once('classes/APIFAQClass.php');
        $api = new APIFAQ();
        $faq = $api->getData($this->module_key, $this->version);

        $languages = Language::getLanguages(false);

        $CMS = CMS::getCMSPages($id_lang, null, true, $id_shop);

        $values = array();
        foreach ($languages as $lang) {
            $values['CB-TEXT'][$lang['id_lang']] = Configuration::get('CB-TEXT', $lang['id_lang']);
            $values['CB-LINK-TEXT'][$lang['id_lang']] = Configuration::get('CB-LINK-TEXT', $lang['id_lang']);
            $values['CB-BUTTON-TEXT'][$lang['id_lang']] = Configuration::get('CB-BUTTON-TEXT', $lang['id_lang']);
        }

        //get readme
        $iso_lang = Language::getIsoById($id_lang);
        $readme = $this->docs_path.'readme_'.$iso_lang.'.pdf';

        //get readme
        $iso_lang = Language::getIsoById($id_lang);
        $doc = $this->docs_path.'doc_'.$iso_lang.'.pdf';

        $baseUrl = _PS_BASE_URL_;

        $this->context->smarty->assign(array(
            'baseUrl' => $baseUrl,
            'id_lang' => $id_lang,
            'module_name' => $this->name,
            'module_version' => $this->version,
            'apifaq' => $faq,
            'readme' => $readme,
            'doc' => $doc,
            'module_display' => $this->displayName,
            'module_path' => $this->module_path,
            'logo_path' => $this->logo_path,
            'img_path' => $this->img_path,
            'CMS_LINK' => $cms_link_page,
            'languages' => $this->context->controller->getLanguages(),
            'defaultFormLanguage' => (int) $this->context->employee->id_lang,
            'fonts' => $this->fonts,
            'cms' => $CMS,
            'img' => $this->img_path,
            'CB_POSITION' => Configuration::get('CB-POSITION'),
            'CB_BG_COLOR' => Configuration::get('CB-BG-COLOR'),
            'CB_BG_OPACITY' => Configuration::get('CB-BG-OPACITY')*100,
            'CB_FONT_STYLE' => Configuration::get('CB-FONT-STYLE'),
            'CB_TEXT_COLOR' => Configuration::get('CB-TEXT-COLOR'),
            'CB_FONT_SIZE' => Configuration::get('CB-FONT-SIZE'),
            'CB_FONT_LOOP' => Configuration::get('CB-FONT-LOOP'),
            'CB_CMS' => Configuration::get('CB-CMS'),
            'CB_BUTTON_BG_COLOR' => Configuration::get('CB-BUTTON-BG-COLOR'),
            'CB_BUTTON_BG_COLOR_HOVER' => Configuration::get('CB-BUTTON-BG-COLOR-HOVER'),
            'CB_BUTTON_TEXT_COLOR' => Configuration::get('CB-BUTTON-TEXT-COLOR'),
            'CB_MORE_INF_LINK_COLOR' => Configuration::get('CB-MORE-INF-LINK-COLOR'),
            'CB_TEXT' => $values['CB-TEXT'],
            'CB_LINK_TEXT' => $values['CB-LINK-TEXT'],
            'CB_BUTTON_TEXT' => $values['CB-BUTTON-TEXT'],
            'ps_version' => (bool)version_compare(_PS_VERSION_, '1.6', '>'),
        ));

        $this->output .= $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

        return $this->output;
    }

    public function postProcess()
    {
        if (Tools::isSubmit('submitCookieBannerModule')) {
            $languages = Language::getLanguages(false);
            $values = array();

            $CB_POSITION = pSQL(Tools::getValue('CB-POSITION'));
            Configuration::updateValue('CB-POSITION', $CB_POSITION);

            $CB_BG_COLOR = pSQL(Tools::getValue('CB-BG-COLOR'));
            Configuration::updateValue('CB-BG-COLOR', $CB_BG_COLOR);

            $CB_BG_OPACITY = (int)Tools::getValue('CB-BG-OPACITY') / 100;
            Configuration::updateValue('CB-BG-OPACITY', $CB_BG_OPACITY);

            $CB_FONT_STYLE= pSQL(str_replace(' ', '+', Tools::getValue('CB-FONT-STYLE')));
            Configuration::updateValue('CB-FONT-STYLE', $CB_FONT_STYLE);

            $CB_TEXT_COLOR = pSQL(Tools::getValue('CB-TEXT-COLOR'));
            Configuration::updateValue('CB-TEXT-COLOR', $CB_TEXT_COLOR);

            $CB_FONT_SIZE = (int)Tools::getValue('CB-FONT-SIZE');
            Configuration::updateValue('CB-FONT-SIZE', $CB_FONT_SIZE);

            $CB_FONT_LOOP = (int)Tools::getValue('CB-FONT-LOOP');
            Configuration::updateValue('CB-FONT-LOOP', $CB_FONT_LOOP);

            $CB_CMS = (int)Tools::getValue('CB-CMS');
            Configuration::updateValue('CB-CMS', $CB_CMS);

            $CB_BUTTON_BG_COLOR = pSQL(Tools::getValue('CB-BUTTON-BG-COLOR'));
            Configuration::updateValue('CB-BUTTON-BG-COLOR', $CB_BUTTON_BG_COLOR);

            $CB_BUTTON_BG_COLOR_HOVER = pSQL(Tools::getValue('CB-BUTTON-BG-COLOR-HOVER'));
            Configuration::updateValue('CB-BUTTON-BG-COLOR-HOVER', $CB_BUTTON_BG_COLOR_HOVER);

            $CB_BUTTON_TEXT_COLOR = pSQL(Tools::getValue('CB-BUTTON-TEXT-COLOR'));
            Configuration::updateValue('CB-BUTTON-TEXT-COLOR', $CB_BUTTON_TEXT_COLOR);

            $CB_MORE_INF_LINK_COLOR = pSQL(Tools::getValue('CB-MORE-INF-LINK-COLOR'));
            Configuration::updateValue('CB-MORE-INF-LINK-COLOR', $CB_MORE_INF_LINK_COLOR);

            foreach ($languages as $lang) {
                $values['CB_TEXT'][$lang['id_lang']] = pSQL(Tools::getValue('CB-TEXT-'.$lang['id_lang'].''));
                $values['CB_LINK_TEXT'][$lang['id_lang']] = pSQL(Tools::getValue('CB-LINK-TEXT-'.$lang['id_lang'].''));
                $values['CB_BUTTON_TEXT'][$lang['id_lang']] = pSQL(Tools::getValue('CB-BUTTON-TEXT-'.$lang['id_lang'].''));
            }

            Configuration::updateValue('CB-TEXT', $values['CB_TEXT']);
            Configuration::updateValue('CB-LINK-TEXT', $values['CB_LINK_TEXT']);
            Configuration::updateValue('CB-BUTTON-TEXT', $values['CB_BUTTON_TEXT']);

            $this->output .= $this->displayConfirmation($this->l('Successful update !'));
        }

    }

    public function hookHeader($params)
    {
        $controller = Context::getContext();
        // Load CSS
        $css = array(
            $this->css_path.'jquery-eu-cookie-law-popup.css',
            $this->css_path.'font-awesome.min.css',
        );
        $controller->controller->addCSS($css, 'all');
        // $controller->controller->addCSS($this->fontsCss);

        // Load JS
        $jss = array(
            $this->js_path.'jquery-eu-cookie-law-popup.js',
            $this->js_path.'initCookiebanner.js',
        );

        $controller->controller->addJS($jss);

        $current_language = Context::getContext()->language->id;
        $languages = Language::getLanguages(true);

        $values = array();
        foreach ($languages as $lang) {
            $values['CB-TEXT'][$lang['id_lang']] = Configuration::get('CB-TEXT', $lang['id_lang']);
            $values['CB-LINK-TEXT'][$lang['id_lang']] = Configuration::get('CB-LINK-TEXT', $lang['id_lang']);
            $values['CB-BUTTON-TEXT'][$lang['id_lang']] = Configuration::get('CB-BUTTON-TEXT', $lang['id_lang']);
        }

        $CMS_ID = Configuration::get('CB-CMS');
        $CMS_URL = $this->context->link->getCMSLink($CMS_ID);

        $background_color = $this->hexToRgb(Configuration::get('CB-BG-COLOR'), Configuration::get('CB-BG-OPACITY'));

        $this->context->smarty->assign(array(
            'current_language' => $current_language,
            'languages' => $languages,
            'CB_POSITION' => Configuration::get('CB-POSITION'),
            // 'CB_SETTINGS' => $CB_SETTINGS,
            'CB_CMS_URL' => $CMS_URL,
            'CB_TEXT' => $values['CB-TEXT'],
            'CB_LINK_TEXT' => $values['CB-LINK-TEXT'],
            'CB_BUTTON_TEXT' => $values['CB-BUTTON-TEXT'],
            'CB_BG_COLOR' => $background_color,
            'CB_FONT_STYLE' => Configuration::get('CB-FONT-STYLE'),
            'CB_TEXT_COLOR' => Configuration::get('CB-TEXT-COLOR'),
            'CB_FONT_SIZE' => Configuration::get('CB-FONT-SIZE'),
            'CB_FONT_LOOP' => Configuration::get('CB-FONT-LOOP'),
            'CB_BUTTON_BG_COLOR' => Configuration::get('CB-BUTTON-BG-COLOR'),
            'CB_BUTTON_BG_COLOR_HOVER' => Configuration::get('CB-BUTTON-BG-COLOR-HOVER'),
            'CB_BUTTON_TEXT_COLOR' => Configuration::get('CB-BUTTON-TEXT-COLOR'),
            'CB_MORE_INF_LINK_COLOR' => Configuration::get('CB-MORE-INF-LINK-COLOR'),

        ));

        return $this->display(__FILE__, 'views/templates/hook/header.tpl');
    }

    public function hexToRgb($hex, $alpha = false)
    {
        $rgb = array();
        $hex      = str_replace('#', '', $hex);
        $length   = Tools::strlen($hex);
        $rgb['r'] = hexdec($length == 6 ? Tools::substr($hex, 0, 2) : ($length == 3 ? str_repeat(Tools::substr($hex, 0, 1), 2) : 0));
        $rgb['g'] = hexdec($length == 6 ? Tools::substr($hex, 2, 2) : ($length == 3 ? str_repeat(Tools::substr($hex, 1, 1), 2) : 0));
        $rgb['b'] = hexdec($length == 6 ? Tools::substr($hex, 4, 2) : ($length == 3 ? str_repeat(Tools::substr($hex, 2, 1), 2) : 0));
        if ($alpha) {
            $rgb['a'] = $alpha;
        }
        return implode(array_keys($rgb)) . '(' . implode(', ', $rgb) . ')';
    }
}
