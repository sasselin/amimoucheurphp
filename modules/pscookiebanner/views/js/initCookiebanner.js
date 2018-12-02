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
*
* Don't forget to prefix your containers with your own identifier
* to avoid any conflicts with others containers.
*/

$(document).ready(function() {
    // Positions :
    // top
    // fixedtop
    // bottom
    // bottomleft
    // bottomright
    // block
    $(document).euCookieLawPopup().init({
        cookiePolicyUrl : cb_cms_url,
        popupPosition : cb_position,
        colorStyle : 'default',
        compactStyle : false,
        popupTitle : '',
        popupText : cb_text,
        buttonContinueTitle : cd_button_text,
        buttonLearnmoreTitle : cb_link_text,
        buttonLearnmoreOpenInNewWindow : true,
        agreementExpiresInDays : 30,
        autoAcceptCookiePolicy : false,
        htmlMarkup : null
    });

    $( ".eupopup-button_1" ).mouseenter(function() {
        $(this).css('background-color', cb_settings.cb_button_bg_color_hover);
        $(this).css('border', '1px solid'+cb_settings.cb_button_bg_color_hover);
    });

    $( ".eupopup-button_1" ).mouseleave(function() {
        $(this).css('background-color', cb_settings.cb_button_bg_color);
        $(this).css('border', '1px solid'+cb_settings.cb_button_bg_color);
    });

});
