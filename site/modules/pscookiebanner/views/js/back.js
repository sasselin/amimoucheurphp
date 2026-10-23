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
$(window).ready(function() {
    $('#CB-OPACITY').bootstrapSlider();
    $("#CB-OPACITY").on("slide", function(slideEvt) {
        $(".cookiebanner-number").val(slideEvt.value);
    });

    $('.cookiebanner-number').bind('input', function() {
        $('#CB-OPACITY').bootstrapSlider('setValue', $(this).val());
    });

    //hide preview banner
    $('#cookiebanner-container').css('display', 'none');

    var html = '<i class="icon-eye"></i>';
    $('#cb-preview').click(function() {
        $(this).toggleClass("cb-preview-active");
        if ($(this).hasClass("cb-preview-active")) {
            $(this).html(html+" Disable preview mode");
            $('#cookiebanner-container').css('display', 'block');
        } else {
            $(this).html(html+" Enable preview mode");
            $('#cookiebanner-container').css('display', 'none');
        }
    });

    $(document).on('click', '.currentLang', function (e) {
        id_lang = $(this).attr('data-id');
        refreshPreview();
    });

    refreshPreview();
    $( "#buylaterConf" ).change(function() {
        refreshPreview();
    });

    function refreshPreview() {
        console.log(id_lang);
        // REFRESH POSITION
        var position = $("input[name=CB-POSITION]:checked").val();
        $('#cookiebanner-container').removeClass();
        $('#cookiebanner-container').addClass('eupopup-container');
        $('#cookiebanner-container').addClass('eupopup-container-'+position);

        if (position === 'fixedtop' || position === 'bottom') {
            $('.eupopup-body').css('display', 'inline-block');
            $('.eupopup-buttons').css('display', 'inline-block');
            $('.eupopup-buttons').css('margin-left', '10px');
            $('.eupopup-body').css('max-width', '1200px');
        } else {
            $('.eupopup-body').css('display', 'block');
            $('.eupopup-buttons').css('display', 'block');
            $('.eupopup-buttons').css('margin-left', '0px');
            $('.eupopup-body').css('max-width', '1200px');
        }

        // REFRESH BACKGROUND COLOR
        var background_color = hexToRgb($("input[name=CB-BG-COLOR]").val(), $("input[name=CB-BG-OPACITY]").val()/100);
        $('#cookiebanner-container').css('background-color', background_color);

        // REFRESH TEXT
        var text_input = 'CB-TEXT-'+id_lang;
        var text = $("textarea[name="+text_input+"]").val();
        $( ".eupopup-body-text" ).text(text);

        $( ".CB-TEXT").keyup(function() {
            var text_input = 'CB-TEXT-'+id_lang;
            var text = $("textarea[name="+text_input+"]").val();
            $(".eupopup-body-text").text(text);
        });

        // REFRESH FONT
        var font = $("#CB-FONT-STYLE option:selected").text();
        $('.eupopup-body-text').css('font-family', font);
        $('.eupopup-body-link').css('font-family', font);
        $('.eupopup-button').css('font-family', font);

        // TEXT COLOR
        var textColor = $("input[name=CB-TEXT-COLOR]").val();
        $('.eupopup-body').css('color', textColor);
        var infColor = $("input[name=CB-MORE-INF-LINK-COLOR]").val();
        $('#learn_more').css('color', infColor );

        // FONT SIZE
        var fontSize = $("input[name=CB-FONT-SIZE]").val();
        $('.eupopup-body-text').css('font-size', fontSize+'px');
        $('.eupopup-body-link').css('font-size', fontSize+'px');
        $('.eupopup-button').css('font-size', fontSize+'px');

        // LINK TEXT
        var learnMore = 'CB-LINK-TEXT-'+id_lang;
        var learnMore = $("input[name="+learnMore+"]").val();
        $("#learn_more").text(learnMore);

        $(".CB-LINK-TEXT").keyup(function() {
            var learnMore = 'CB-LINK-TEXT-'+id_lang;
            var learnMore = $("input[name="+learnMore+"]").val();
            $("#learn_more").text(learnMore);
        });

        // CMS PAGE
        var idCms = $("#CB-CMS option:selected").val();
        $('#learn_more').attr("href", baseUrl+"/index.php?id_cms="+idCms+"&controller=cms&id_lang="+id_lang);

        // BUTTON TEXT
        var buttonText = 'CB-BUTTON-TEXT-'+id_lang;
        var buttonText = $("input[name="+buttonText+"]").val();
        $(".eupopup-button").text(buttonText);

        $(".CB-BUTTON-TEXT").keyup(function() {
            var buttonText = 'CB-BUTTON-TEXT-'+id_lang;
            var buttonText = $("input[name="+buttonText+"]").val();
            $(".eupopup-button").text(buttonText);
        });

        // BUTTON BACKGROUND COLOR
        var buttonBgColor = $("input[name=CB-BUTTON-BG-COLOR]").val();
        $('.eupopup-button').css('background-color', buttonBgColorHover);
        $('.eupopup-button').css('border', '1px solid '+buttonBgColorHover);

        var buttonBgColorHover = $("input[name=CB-BUTTON-BG-COLOR-HOVER]").val();
        $('.eupopup-button').css('background-color', buttonBgColor);
        $('.eupopup-button').css('border', '1px solid '+buttonBgColor);

        $( ".eupopup-button" ).mouseenter(function() {
            $('.eupopup-button').css('background-color', buttonBgColorHover);
            $('.eupopup-button').css('border', '1px solid '+buttonBgColorHover);
        });

        $( ".eupopup-button" ).mouseleave(function() {
            $('.eupopup-button').css('background-color', buttonBgColor);
            $('.eupopup-button').css('border', '1px solid '+buttonBgColor);
        });

        // BUTTON TEXT COLOR
        var buttonTextColor = $("input[name=CB-BUTTON-TEXT-COLOR]").val();
        $('.eupopup-button').css('color', buttonTextColor);
    }

    function hexToRgb(hex, opacity) {
        var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);

        var rgba = 'rgba('+parseInt(result[1], 16)+', '+parseInt(result[2], 16)+', '+parseInt(result[3], 16)+', '+opacity+')';
        return rgba;
    }
});
