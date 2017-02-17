/*!
* @summary     DataTables
* @description Paginate, search and sort HTML tables
* @file        jquery.dataTables.js
* @author      Allan Jardine (www.sprymedia.co.uk)
* @contact     www.sprymedia.co.uk/contact
*
* @copyright Copyright 2008-2014 Allan Jardine, all rights reserved.
*/
var tmp = $;
$ = $j210;

var $oTable;

function getTypeDetails(id) {
	if (typeof(id) !== 'object') {
		is_sharp = id.charAt(0);
		if (is_sharp !== '#') {
			id = '#'+id;
		}
	}
	$obj = $(id);
	var reg_m = new RegExp("^metas-[0-9]$","g");
	var reg_u = new RegExp("^urls-[0-9]$","g");
	var parentEls = $obj.parents().map(function() {
		var id = $.trim(this.id);
		if(reg_m.test(id)) {
			return this.id;
		}
		else if(reg_u.test(id)) {
			return this.id;
		}
	}).get().join('');
	return ($('#configuration-'+parentEls).attr('data-type'));
}

function getType(id) {
	if (typeof(id) !== 'object') {
		is_sharp = id.charAt(0);
		if (is_sharp !== '#') {
			id = '#'+id;
		}
	}
	return ($(id).attr('data-type'));
}

function reloadTable(id) {
	if (typeof(id) !== 'object') {
		is_sharp = id.charAt(0);
		if (is_sharp !== '#') {
			id = '#'+id;
		}
	}
	conf = id.replace('table', 'configuration');

	type = getType(conf);
	$(id).dataTable().fnDestroy();
	$(id).dataTable({
		"bDestroy":true,
		"bRetrieve": true,
		"bProcessing": true,
		"bServerSide": true,
		"sAjaxSource": admin_module_ajax_url,
		"bAutoWidth": false,
		"fnRowCallback": function(nRow, aData, iDisplayIndex) {
			var $tr = $oTable.$('tr:eq('+cleanInt(iDisplayIndex)+')' );
			active = $(aData[cols_status]).hasClass('action-enabled');
			if (active === true)
				$tr.attr('data-active', 1);
			else
				$tr.attr('data-active', 0);
		},
		"fnServerData": function (sSource, aoData, fnCallback) {
			aoData = setData(aoData);
			$.ajax({
				"dataType": 'json',
				"type": "POST",
				"url": sSource,
				"data": aoData,
				"success": fnCallback
			});
		},
		"oLanguage": setLang(),
		"aoColumnDefs": setColumnDefs(),
		"aaSorting": [
			[1, 'asc']
		]
	});
}

function loadTable(id) {
	if (typeof(id) !== 'object') {
		is_sharp = id.charAt(0);
		if (is_sharp !== '#') {
			id = '#'+id;
		}
	}
	conf = id.replace('table', 'configuration');
	$oTable = $(id).dataTable({
		"bRetrieve": true,
		"bProcessing": true,
		"bServerSide": true,
		"sAjaxSource": admin_module_ajax_url,
		"bAutoWidth": false,
		"fnRowCallback": function(nRow, aData, iDisplayIndex) {
			var $tr = $oTable.$('tr:eq('+cleanInt(iDisplayIndex)+')' );
			active = $(aData[cols_status]).hasClass('action-enabled');
			if (active === true)
				$tr.attr('data-active', 1);
			else
				$tr.attr('data-active', 0);
		},
		"fnServerData": function (sSource, aoData, fnCallback) {
			aoData = setData(aoData);
			$.ajax({
				"dataType": 'json',
				"type": "POST",
				"url": sSource,
				"data": aoData,
				"success": fnCallback
			});
		},
		"oLanguage": setLang(),
		"aoColumnDefs": setColumnDefs(),
		"aaSorting": [
			[1, 'asc']
		]
	});
}

function setLang() {
	return {
		"sLengthMenu": records_msg+" _MENU_",
		"sZeroRecords": zero_records_msg,
		"sInfo": "_START_/_END_ of _TOTAL_ records",
		"sInfoEmpty": "",
		"sInfoFiltered": "(filtered from _MAX_ total records)"
	};
}

function setData(aoData) {
	aoData.push({
		"name": "controller", "value": admin_module_controller
	});
	aoData.push({
		"name": "action", "value": 'ReloadData'
	});
	aoData.push({
		"name": "ajax", "value": true
	});
	aoData.push({
		"name": "id_tab", "value": current_id_tab
	});
	aoData.push({
		"name" : "id_country", "value": $('#id_country').val()
	})
	return aoData;
}

function setColumnDefs() {
	cols_status = 4;
	cols_after = 5;
	last_cols = 5;

	return [{
			"bSearchable": false,
			"aTargets": [ 0, 2, 4, cols_after, last_cols]
		}, {
			"bSortable": false,
			"sClass": "fixed-width-sm text-center hidden-table-info",
			"aTargets": [0]
		}, {
			"sClass": "fixed-width-sm text-center",
			"aTargets": [1, 2, 3, cols_after, last_cols]
		}, {
			"sClass": "fixed-width-sm text-center number",
			"aTargets": [1]
		}, {
			"bSortable": false,
			"aTargets": [2,last_cols]
		}, {
			"sClass": "pointer fixed-width-sm text-center",
			"aTargets": [cols_status]
		}, {
			"bSortable": false,
			"aTargets": [0, 1, 2, 3, 4, cols_after, last_cols]
		}, {
			"bVisible": false,
			"aTargets": [0, 2]
		},
	];
}

$(window).load(function() {

	$(document).on('hover', '.dropdown-toggle', function (e) {
		$(this).dropdown();
	});

	$(document).on('click', '.dataTable thead th', function (e) {
		e.preventDefault();
		if (!$(this).hasClass('sorting_disabled')) {
			$(this).parents('thead').each(function () {
				$(this).find('i').removeClass('icon-sort-alpha-asc icon-sort-amount-asc').addClass('icon-sort');
				$(this).find('i').removeClass('icon-sort-alpha-desc icon-sort-amount-desc').addClass('icon-sort');
			});
			$(this).find('i').toggleClass(function() {
				if ($(this).parent().is(".number")) {
					asc_icon = 'icon-sort-amount-asc';
					desc_icon = 'icon-sort-amount-desc';
				} else {
					asc_icon = 'icon-sort-alpha-asc';
					desc_icon = 'icon-sort-alpha-desc';
				}

				if ($(this).parent().is(".sorting_asc")) {
					$(this).removeClass(desc_icon);
					return asc_icon;
				} else {
					$(this).removeClass(asc_icon);
					return desc_icon;
				}
			});
		}
	});
});

$ = tmp;
