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
$(window).load(function() {
    // Tab panel active
    $(".list-group-item").on('click', function() {
        var id_tab = $(this).attr('data-target');

        switch(id_tab) {
            case 'configuration':
                $("#configuration").removeClass('hidden');
                $("#customization").addClass('hidden');
                $("#help").addClass('hidden');
                break;
            case 'customization':
                $("#configuration").addClass('hidden');
                $("#customization").removeClass('hidden');
                $("#help").addClass('hidden');
                break;
            case 'help':
                $("#configuration").addClass('hidden');
                $("#customization").addClass('hidden');
                $("#help").removeClass('hidden');
                break;
        }

        var $el = $(this).parent().closest(".list-group").children(".active");
        var tab_id = $(this).attr('id');
        if (tab_id === 'drop') {
            $(this).removeClass("active");
            cleanUp();
        } else {
            if ($el.hasClass("active")) {
                target = $(this).find('i').attr('data-target');
                if (target !== undefined) {
                    loadTable('#'+target);
                }
                $el.removeClass("active");
                $(this).addClass("active");
            }
        }
    });
});
