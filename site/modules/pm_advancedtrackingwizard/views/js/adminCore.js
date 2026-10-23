/**
 *
 * PM_JsCoreFunctions
 *
 * @author    Presta-Module.com <support@presta-module.com>
 * @copyright Presta-Module 2011
 * @version   0.0.1
 *
 *************************************
 ** JS Core Functions For PM Addons  *
 **   http://www.presta-module.com   *
 **             V 0.0.1              *
 *************************************
 * + Description: Js Core Functions For PM Addons
 * + Languages: EN, FR
 **/
(function(){
	var old = $.ui.dialog.prototype._create;
	$.ui.dialog.prototype._create = function(d){
		old.call(this, d);

		var self = this;
		var options = self.options,
		oldHeight = options.height,
		oldWidth = options.width;
		fixDialogSize(self,options,oldHeight,oldWidth);
		$(window).unbind('resize.uidialog').bind('resize.uidialog', function() {
			fixDialogSize(self,options,oldHeight,oldWidth);
		});
	};
})();

function fixDialogSize(self,options,oldHeight,oldWidth) {

	var fitHeight = options.fitHeight,
	screenHeight 	= $(window).height(),
	screenWidth 	= $(window).width(),
	dialogHeight	= options.height,
	dialogWidth		= options.width;

	if(!fitHeight && (screenHeight < oldHeight)) {
		fitHeight = true;
	}else if(!fitHeight && (dialogHeight < oldHeight) && (screenHeight < oldHeight)) {
		$(self).dialog( "option", "height",  screenHeight);
	}

	uiDialogTitlebarFull = $('<a href="#"><span class="ui-icon ui-icon-newwin"></span></a>')
		.addClass(
			'ui-dialog-titlebar-full ' +
			'ui-corner-all'
		)
		.attr('role', 'button')
		.hover(
			function() {
				uiDialogTitlebarFull.addClass('ui-state-hover');
			},
			function() {
				uiDialogTitlebarFull.removeClass('ui-state-hover');
			}
		)
		.toggle(
			function() {
				self._setOptions({
					height : window.innerHeight - 10,
					width : oldWidth
				});
				self._position('center');
				return false;
			},
			function() {
				self._setOptions({
					height : oldHeight,
					width : oldWidth
				});
				self._position('center');
				return false;
			}
		)
		.focus(function() {
			uiDialogTitlebarFull.addClass('ui-state-focus');
		})
		.blur(function() {
			uiDialogTitlebarFull.removeClass('ui-state-focus');
		})
		.appendTo(self.uiDialogTitlebar),

		uiDialogTitlebarFullText = $('<span></span>')
			.addClass(
				'ui-icon ' +
				'ui-icon-newwin'
			)
			.text(options.fullText);
	if(fitHeight) {
		self._setOptions({
			height : window.innerHeight - 10,
			width : oldWidth
		});
		self._position('center');
	}
	self._position('center');
}

function hideNextIfTrue(e) {
	var val = parseInt($(e).val());
	if (val) {
		$(e).parent('.margin-form').next('div').slideUp('fast');
	} else {
		$(e).parent('.margin-form').next('div').slideDown('fast');
	}
}
function showNextIfTrue(e) {
	var val = parseInt($(e).val());
	if (val) {
		showNext(e);
	} else {
		hideNext(e);
	}
}
function showNext(e) {
	$(e).parent('.margin-form').next('div').slideDown('fast');
}
function hideNext(e) {
	$(e).parent('.margin-form').next('div').slideUp('fast');
}
function showSpanIfChecked(e, idToShow) {
	var val = $(e).attr('checked');
	if (val) {
		$(idToShow).css('display', 'inline');
	} else {
		$(idToShow).hide();
	}
}

var dialogIframe;
function openDialogIframe(url,dialogWidth,dialogHeight,fitScreenHeight) {
	$('body').css('overflow','hidden');
	dialogIframe = $('<iframe class="dialogIFrame" frameborder="0" marginheight="0" marginwidth="0" src="'+url+'"></iframe>').dialog({
		bgiframe: true,
		modal: true,
		width:dialogWidth,
		height:dialogHeight,
		fitHeight:(typeof(fitScreenHeight)!='undefined' && fitScreenHeight ? true:false),
		close: function(event, ui) {$('body').css('overflow','auto'); },
		open: function (event,ui) {$(this).css('width','97%');}
	});
}
function closeDialogIframe() {
	$(dialogIframe).dialog("close");
}

var dialogInline;
function openDialogInline(contentId,dialogWidth,dialogHeight,fitScreenHeight) {
	$('body').css('overflow','hidden');
	dialogInline = $(contentId).dialog({
		modal: true,
		width:dialogWidth,
		height:dialogHeight,
		fitHeight:(typeof(fitScreenHeight)!='undefined' && fitScreenHeight ? true:false),
		close: function(event, ui) {$('body').css('overflow','auto'); },
		open: function (event,ui) {$(this).css('width','97%');}
	});
}
function reloadPanel(idPanel) {
	var url = $('#'+idPanel).attr('rel');
	if(!url) show_info('noRelPanel','Attribute rel is not set for panel '+idPanel);
	$('#'+idPanel).load(url, function(response, status, xhr) {
		if (status == "error") {
		    return;
		}
	});
}
function loadPanel(idPanel,url) {
	$('#'+idPanel).attr('rel',url);
	reloadPanel(idPanel);
}

function show_info(content) {
	$.jGrowl(content,{ themeState: 'ui-state-highlight' });
}
function show_error(content) {
	$.jGrowl(content,{ sticky: true, themeState: 'ui-state-error' });
}
function objectToarray (o,e) {
	a = new Array;
	for (var i=1; i<o.length; i++) {
		a.push(parseInt(o[i][e]));
	}
	return a;
}

$.fn.extend({
	pm_ajaxScriptLoad: function(event) {
		if($(this).hasClass('pm_confirm') && !confirm($("<textarea />").html($(this).attr('title')).val())) {
			event.preventDefault();
			return false;
		}
		if($(this).next('.progressbar_wrapper').length) {
			var curLink = $(this);
			$(curLink).hide();
			var progressbar = $(this).next('.progressbar_wrapper').children('.progressbar');
			$(progressbar).progressbar({
				value: 0,
				complete: function(event, ui) { clearInterval(progressbarInterval);$(progressbar).text("");$(this).progressbar( "destroy" );$(curLink).show();}
			});
			var progressbarInterval = setInterval(function() {
				if($(progressbar).progressbar('value') == 99) $(progressbar).progressbar('value',0);
				$(progressbar).progressbar('value',$(progressbar).progressbar('value') + 9);
			}, 100);
		}
		var rel = $(this).attr('rel');
		if(rel) {
			var relSplit = rel.split(/_/g);
			if(relSplit[0] == 'tab') {
				var tabId = '#'+relSplit[1];
				var tabIndex = parseInt(relSplit[2]);
				$(tabId).unbind( "tabsload").bind( "tabsload", function(event, ui) {
					$(window).scrollTo("#wrapConfigTab",1000);
				});
				$(tabId).tabs( "url" , tabIndex , $(this).attr('href') );
				$(tabId).tabs( "load" , tabIndex );
				event.preventDefault();
				return false;
			}
		}
		$.ajax( {
			type : "GET",
			url : $(this).attr('href'),
			dataType : "script",
			error: function(XMLHttpRequest, textStatus, errorThrown) {
				//alert(msgAjaxError);
			}
		});
		event.preventDefault();
		return false;
	},
	pm_openOnDialogIframe: function(event) {
		var rel = $(this).attr('rel');
		var dialogWidth = 900;
		var dialogHeight = 600;
		var dialogFixHeight = false;
		if(rel) {
			var relSplit = rel.split(/_/g);
			if(typeof(relSplit[0]) != 'undefined' && typeof(relSplit[1]) != 'undefined') {
				dialogWidth = relSplit[0];
				dialogHeight = relSplit[1];
				dialogFixHeight = (typeof(relSplit[2])!='undefined' && relSplit[2] ? true:false);
			}
		}
		openDialogIframe($(this).attr('href'),dialogWidth,dialogHeight,dialogFixHeight);
		return false;
	},
	pm_ajaxLoadOnBlc: function(event) {
		var rel = $(this).attr('rel');
		if (rel) {
			loadPanel(rel,$(this).attr('href'));
		}
		return false;
	},
	pm_hideClassShowId: function(event) {
		var rel = $(this).attr('rel');
		if (typeof(rel) != 'undefined' && rel.length > 0) {
			var relSplit = rel.split(/@/g);
			if (typeof(relSplit[0]) != 'undefined' && typeof(relSplit[1]) != 'undefined') {
				class_to_hide = relSplit[0];
				id_to_show = relSplit[1];
			}
			// Hide other block
			$('.'+class_to_hide).slideUp('fast', function() {
				// Show selected block
				$('#'+id_to_show).slideDown('fast');
			});
		}
		return false;
	}
});

function loadAjaxLink() {
	$(document).off('click', '.ajax_script_load').on('click', '.ajax_script_load', function(e) {
		return $(this).pm_ajaxScriptLoad(e);
	});
	$(document).off('click', '.open_on_dialog_iframe').on('click', '.open_on_dialog_iframe', function(e) {
		return $(this).pm_openOnDialogIframe(e);
	});
	$(document).off('click', '.ajax_load_on_blc').on('click', '.ajax_load_on_blc', function(e) {
		return $(this).pm_ajaxLoadOnBlc(e);
	});
	$(document).off('click', '.hide_class_show_id').on('click', '.hide_class_show_id', function(e) {
		return $(this).pm_hideClassShowId(e);
	});
}

function bindFillNextSize() {
	$('.fill_next_size').unbind('click').click(function() {
		$(this).nextAll('input[type=text]').val($(this).prev('input[type=text]').val());
	})
}
function initMakeGradient() {
	$('.makeGradient').unbind('click').click(function() {
		var e = $(this).parent('span').prev('span');
		if($(e).css('display') == 'inline')
			$(this).parent('span').prev('span').hide();
		else
			$(this).parent('span').prev('span').show();
	});
}
function checkChildrenCheckbox(e) {
	if($(e).children('input[type=checkbox]:checked').length)
		$(e).children('input[type=checkbox]').removeAttr('checked');
	else
		$(e).children('input[type=checkbox]').attr('checked','checked');
}
function unCheckAllChildrenCheckbox(e) {
	$(e).find('input[type=checkbox]').removeAttr('checked');
}
function display(message) {
	$.jGrowl(message, { sticky: true });
}
function hide(message) {
	$.jGrowl('close');
}
function initTips(e) {
	$(document).ready(function() {
		$(e+"-tips").tipTip();
	});
}
$(document).ready(function() {
	loadAjaxLink();
	$(".pm_tips").tipTip();
	$('div#addons-rating-container p.dismiss a').click(function() {
		$('div#addons-rating-container').hide(500);
		$.ajax({type : "GET", url : window.location+'&dismissRating=1' });
		return false;
	});
});