var buttonsCoords = {};
function addVarToTracker(var_name,var_labbel) {
  var start = currentCodeMirror.getCursor(true);
  var end = currentCodeMirror.getCursor(false);
  buttonsCoords[var_name] = end;
  currentCodeMirror.replaceSelection('{%span class="CodeMirror-atw button" rel="'+var_name+'"><img src="'+_modulePath+'views/img/blank.gif" /><em>{'+var_name+'}</em>%}');
  var selectEnd = currentCodeMirror.getCursor(false);
  currentCodeMirror.setCursor(selectEnd);
  currentCodeMirror.focus();
}

function showPageRelatedItems(e) {
	var itemName = $(e).attr("name");
	var itemVal = $(e).val();
	if(itemVal != 0)
		switch(itemName){
			case 'page':
					$.ajax( {
						type : "GET",
						url  : _base_config_url+'&getPageRelatedItems=1&pageName='+itemVal,
						dataType : "script",
						error: function(XMLHttpRequest, textStatus, errorThrown) {
						}
					});
			break;
		}

}

function preg_quote (str, delimiter) {
    // Quote regular expression characters plus an optional character
    //
    // version: 1109.2015
    // discuss at: http://phpjs.org/functions/preg_quote
	// +   original by: booeyOH
    // +   improved by: Ates Goral (http://magnetiq.com)
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   bugfixed by: Onno Marsman
    // +   improved by: Brett Zamir (http://brett-zamir.me)
	// *     example 1: preg_quote("$40");
    // *     returns 1: '\$40'
    // *     example 2: preg_quote("*RRRING* Hello?");
    // *     returns 2: '\*RRRING\* Hello\?'
    // *     example 3: preg_quote("\\.+*?[^]$(){}=!<>|:");
	// *     returns 3: '\\\.\+\*\?\[\^\]\$\(\)\{\}\=\!\<\>\|\:'
    return (str + '').replace(new RegExp('[.\\\\+*?\\[\\^\\]$(){}=!<>|:\\' + (delimiter || '') + '-]', 'g'), '\\$&');
}
var replaceCursor = false;

$(document).ready(function(event){
	$('.dynamicTextarea').bind('keydown', function(e) {
		var code = (e.keyCode ? e.keyCode : e.which);
		//Check if backspace or delete key ara press
		 if(code == 8 || code == 46) {
			 var hitTag = $(".CodeMirror-cursor").collision( ".CodeMirror-atw" );
			//Check if cursor hit tag
			 if($(hitTag).html() != null) {
				//get tag id
				 var paternReplace = $(hitTag).children('em').html();
				 var originalTag = "{%span class=\"CodeMirror-atw button\" rel=\""+$(hitTag).attr('rel')+"\"><img src=\""+_modulePath+"views/img/blank.gif\" /><em>"+paternReplace+"</em>%}";
				//get tag strlen for removing from cursor pos
				 var removeCharFromCursorPos = originalTag.length;
				//escape for regexp
				 /*var regPaternReplace1=new RegExp("{", "g");
				 paternReplace = paternReplace.replace(regPaternReplace1,'\\\{');
				 var regPaternReplace2=new RegExp("}", "g");
				 paternReplace = paternReplace.replace(regPaternReplace2,'\\\}');*/
				 var regReplace=new RegExp("\\\{%span class=\"CodeMirror-atw button\" rel=\""+preg_quote($(hitTag).attr('rel'))+"\"><img src=\""+_modulePath+"views/img/blank.gif\" /><em>"+preg_quote(paternReplace)+"</em>%\\\}", "g");
				//Update codemirror textarea
				 var textareaVal = $('.CodeMirror textarea').val();
				 textareaVal = textareaVal.replace(regReplace,'');

				 $('.CodeMirror textarea').val(textareaVal);
				 $('#tracking_code').val(textareaVal);

				//Remove tag from code mirror
				 hitTag.remove();
				//Save and refresh code mirror
				 currentCodeMirror.save();
				 currentCodeMirror.refresh();
				//Get cursor pos
				 replaceCursor = currentCodeMirror.getCursor(true);
				 replaceCursor.ch = replaceCursor.ch-removeCharFromCursorPos;
				 return false;

			 }
		 }
		 //Detect left/top/bottom arrow and replace code mirror cursor
		 else if(code == 37 || code == 40 || code == 38) {
			 var hitTag = $(".CodeMirror-cursor").collision( ".CodeMirror-atw" );
			//Check if cursor hit tag
			 if($(hitTag).html() != null) {
				//get tag id
				 var paternReplace = $(hitTag).children('em').html();
				 var originalTag = "{%span class=\"CodeMirror-atw button\" rel=\""+$(hitTag).attr('rel')+"\"><img src=\""+_modulePath+"views/img/blank.gif\" /><em>"+paternReplace+"</em>%}";
				//get tag strlen for removing from cursor pos
				 var removeCharFromCursorPos = originalTag.length;

				//Get cursor pos
				 replaceCursor = currentCodeMirror.getCursor(true);
				 replaceCursor.ch = replaceCursor.ch-removeCharFromCursorPos+1;
				 replaceCodeMirrorCursor();

			 }
		 }
		//Detect right arrow and replace code mirror cursor
		 else if(code == 39) {
			 var hitTag = $(".CodeMirror-cursor").collision( ".CodeMirror-atw" );
				//Check if cursor hit tag
				 if($(hitTag).html() != null) {
					//get tag id
					 var paternReplace = $(hitTag).children('em').html();
					 var originalTag = "{%span class=\"CodeMirror-atw button\" rel=\""+$(hitTag).attr('rel')+"\"><img src=\""+_modulePath+"views/img/blank.gif\" /><em>"+paternReplace+"</em>%}";
					//get tag strlen for removing from cursor pos
					 var addingCharFromCursorPos = originalTag.length;
					//Get cursor pos
					 replaceCursor = currentCodeMirror.getCursor(true);
					 replaceCursor.ch = replaceCursor.ch+addingCharFromCursorPos-1;
					 replaceCodeMirrorCursor();
				 }
			 }
	});
	$('.dynamicTextarea').bind('keyup', function(e) {
		var code = (e.keyCode ? e.keyCode : e.which);
		if(code == 40 || code == 38) {
			 var hitTag = $(".CodeMirror-cursor").collision( ".CodeMirror-atw" );
			//Check if cursor hit tag
			 if($(hitTag).html() != null) {
				//get tag id
				 var paternReplace = $(hitTag).children('em').html();
				 var originalTag = "{%span class=\"CodeMirror-atw button\" rel=\""+$(hitTag).attr('rel')+"\"><img src=\""+_modulePath+"views/img/blank.gif\" /><em>"+paternReplace+"</em>%}";
				//get tag strlen for removing from cursor pos
				 var removeCharFromCursorPos = originalTag.length;
				//Get cursor pos
				 replaceCursor = currentCodeMirror.getCursor(true);
				 replaceCursor.ch = replaceCursor.ch-removeCharFromCursorPos+1;
				 replaceCodeMirrorCursor();

			 }
		 }
		replaceCodeMirrorCursor();
	});
});
function replaceCodeMirrorCursor() {
	if(replaceCursor) {
		//Replace cursor to correct POS
		replaceCursor.ch = (replaceCursor.ch);
		currentCodeMirror.setCursor(replaceCursor);
		currentCodeMirror.focus();
		replaceCursor = false;
	}
}
//Prevent tag edit
$(document).off('click', '.CodeMirror-atw').on('click', '.CodeMirror-atw', function(e) {
	currentCodeMirror.setCursor({'line':0,'ch':0});
});