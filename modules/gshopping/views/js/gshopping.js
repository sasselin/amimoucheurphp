/**
* 2007-2015 PrestaShop
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
* @author    PrestaShop SA <contact@prestashop.com>
* @copyright 2007-2015 PrestaShop SA
* @license   http://addons.prestashop.com/en/content/12-terms-and-conditions-of-use
* International Registered Trademark & Property of PrestaShop SA
*/

var cleanInt = function (x) {
	x = Number(x);
	return x >= 0 ? Math.floor(x) : Math.ceil(x);
};

var p = function () {
	if (debug_mode) {
		var i = 0,
		arg_lenght = arguments.length;
		if (arg_lenght > 0) {
			for (i; i<arg_lenght; i++) {
				if (arguments[i] instanceof Array) {
					console.log(arguments[i]);
				}
				else if (typeof(arguments[i]) === 'object') {
					console.table(arguments[i]);
				} else {
					// console.log(arguments.callee.caller.toString());
					console.log(arguments[i]);
				}
			}
		}
	}
};

jQuery.fn.listAttributes = function(prefix) {
	var list = [], key;
	$(this).each(function() {
		console.info(this);
		var attributes = [];
		for (key in this.attributes) {
			if(!isNaN(key)) {
				if(!prefix || this.attributes[key].name.substr(0,prefix.length) === prefix) {
					attributes.push(this.attributes[key].name);
				}
			}
		}
		list.push(attributes);
	});
	return (list.length > 1 ? list : list[0]);
};

 // Allow to serialize any element
(function($) {
		$.fn.serializeAnything = function() {
				var toReturn = [];
				var els = $(this).find(':input').get();
				$.each(els, function() {
					if (this.name && !this.disabled && (this.checked || /select|textarea/i.test(this.nodeName) || /text|hidden|password/i.test(this.type))) {
						var val = $(this).val();
						toReturn.push( encodeURIComponent(this.name) + "=" + encodeURIComponent( val ) );
					}
				});
			return toReturn.join("&").replace(/%20/g, "+");
		};
})(jQuery);

var tmp = $;
$ = $j210;

// Main Function
var Main = function () {

	var addExports = function(id_country) {
		$.ajax({
			tpye: 'POST',
			url:admin_module_ajax_url,
			dataType:'html',
			data: {
				controller : admin_module_controller,
				action : 'LoadExportInfo',
				ajax : true,
				id_country: id_country
			},
			success : function(data)	{
				$("#export_info").html(data);
			}
		});

		$.ajax({
			tpye: 'POST',
			url:admin_module_ajax_url,
			dataType:'html',
			data: {
				controller : admin_module_controller,
				action : 'LoadExportLink',
				ajax : true,
				id_country: id_country,
			},
			success : function(data)	{
				$("#export_link").html(data);
			}
		});
	};

	var addTaxonomyCache = function(id_country) {
		$.ajax({
			tpye: 'POST',
			url:admin_module_ajax_url,
			data: {
				controller : admin_module_controller,
				action : 'GenTaxonomyCache',
				ajax : true,
				id_country: id_country
			},
		});
	};

	/**
	** Check if inputs are not empty
	*/
	var checkInputs = function(obj, btn) {
		obj.data('val', obj.val());
		obj.change(function() {
			if (obj.val().length > 0) {
				btn.enable();
			} else {
				btn.disable();
			}
		});

		obj.keyup(function() {
			if(obj.val() !== obj.data('val')) {
				obj.data('val', obj.val());
				$(this).change();
			}
		});

		if (obj.val().length > 0) {
			btn.enable();
		} else {
			btn.disable();
		}
	};

	/**
	** Check if inputs are not empty
	*/
	var checkSave = function(obj) {
		var set = 0;
		obj.data('val', obj.val());
		obj.change(function() {
			if (obj.val().length > 0) {
				set = 0;
			} else {
				set = 1}
		});

		obj.keyup(function() {
			if(obj.val() !== obj.data('val')) {
				obj.data('val', obj.val());
				$(this).change();
			}
		});

	if (obj.val().length > 0) {
			set = 0;
		} else {
			set = 1
		}

		return set;
	};



	/**
	** Disable some buttons
	*/
	var disableButtons = function (header, button) {
		// Disable & hide save button
		button.disable().hide();
		// Allow to close from header
		header.find('.bootstrap-dialog-close-button').attr('style', '');
	};
	var loadModuleSchool = function () {
		$.ajax({
			type: 'POST',
			url: admin_module_ajax_url,
			dataType: 'html',
			async: true,
			cache: false,
			data: {
				controller : admin_module_controller,
				action : 'loadModuleSchool',
				ajax : true,
			},
			success : function(jsonData) {
				var response_form = $('<div id="response_form"></div>');
				response_form.html(jsonData);
				BootstrapDialog.show({
					//title: module_school_title,
					sizeModal: 'SIZE_XLARGE',
					onshow: function(dialogRef) {
						var $header = dialogRef.getModalHeader();
						$header.hide();
						var $body = dialogRef.getModalBody();
						var $footer = dialogRef.getModalFooter();
					},
					message: response_form,
					buttons: [
						// Close
						{
							label: ready_message,
							cssClass: 'btn-primary pull-right',
							action: function(dialogRef){
								dialogRef.close();
							}
						},
					]
				});
			}
		});
	}
	/**
	** Load Modal for Add or Edit SEO Rule
	*/
	var loadFormModal = function (id, category_name, id_category, table, id_country) {
		id_object = (typeof(id) !== 'undefined') ? id : '';

		$.ajax({
			type: 'POST',
			url: admin_module_ajax_url,
			dataType: 'html',
			data: {
				controller : admin_module_controller,
				action : 'LoadForm',
				ajax : true,
				id_tab : current_id_tab,
				id_category: id_category,
				category_name: category_name,
				id_object : id_object,
				id_country : id_country
			},
			success: function(jsonData) {
				// Filled the fields with pattern tags
				var response_form = $('<div id="response_form"></div>');
				response_form.html(jsonData);
				// Show Modal
				BootstrapDialog.show({
					//title: '',
					sizeModal: 'SIZE_XLARGE',
					onshow: function(dialogRef) {
						dialogRef.setClosable(false);

						var $header = dialogRef.getModalHeader();
						var $body = dialogRef.getModalBody();
						var $footer = dialogRef.getModalFooter();
						var $button = dialogRef.getButton('btn-save');
						var $next_button = dialogRef.getButton('next-step');
						var $back_button = dialogRef.getButton('back-step');
						var $later_button = dialogRef.getButton('later');
						var $active_button = dialogRef.getButton('active');

						// Disable & hide save button
						disableButtons($header, $button);

						// Check if this rule is a default one
						id_lang = $body.find("#select_lang").val();

						// Set category name
						$body.find("#categoryname").html(category_name);


						// Verification between steps
						var checkStep = function (obj, context) {
							var $wizardContent = $body.find('#wizard');
							$lang = $wizardContent.find('#select_lang');
							current_step = cleanInt(context.fromStep);
							next_step = cleanInt(context.toStep);
							$next_button.disable();
							$later_button.hide();
							$active_button.hide();

							if(next_step === 1) {
								$footer.find("#back-step").addClass('hide');
								checkInputs($body.find('#select_lang'), $next_button);
								$body.find('#step-1 input').first().focus();
							}

							if(next_step === 2) {
								$footer.find("#back-step").addClass('hide');
								checkInputs($body.find('#select_google_category'), $next_button);
								$body.find('#step-2 input').first().focus();
							}

							if (next_step === 3) {
								$footer.find("#back-step").addClass('hide');
								$footer.find("#next-step").addClass('hide');
								$button.show();
								$body.find('#step-3 input').first().focus();
							}

							// Navigation buttons (Back/Next/Save)
							$body.find("#next-step").unbind("click").on("click", function (e) {
								e.preventDefault();
								$wizardContent.smartWizard("goForward");
							});
							$body.find("#back-step").unbind("click").on("click", function(e) {
								e.preventDefault();
								$wizardContent.smartWizard("goBackward");
							});
							$body.find(".finish-step").unbind("click").on("click", function(e) {
								e.preventDefault();
								onFinishForm();
							});
						};

						// Show tooltip for helping merchant
						$body.find('.tooltips').tooltip({animation: false});

						$body.find('.tags_select').hide();

						$body.find('.showlist').focusin(function() {
							if (typeof(timeout) !== 'undefined') {
								clearTimeout(timeout);
							}
							$body.find('.tags_select').show();
						}).focusout(function() {
							timeout = setTimeout(function() {
								$body.find('.tags_select').hide();
							}, 200);
						});

						// Start the wizard
						$body.find('#wizard').smartWizard({
							selected: 0,
							keyNavigation: false,
							enableAllSteps: false,//(typeof(id_object) === 'string'),
							onShowStep: checkStep
						});

						// Select picker
						$body.find('select.selectpicker').selectpicker();
						$body.find('button.selectpicker').each(function() {
							var select = $(this);
							select.on('click', function() {
								select.find('.bootstrap-select').addClass('open');
							});
						});

						// Select lang
						$body.find('#select_lang').on('change', function() {
							$body.find('.form-group').hide();
							$body.find('#google_loader').removeClass('hidden');
							$.ajax({
								type: 'POST',
								url: admin_module_ajax_url,
								dataType: 'html',
								async: true,
								cache: false,
								data: {
									controller : admin_module_controller,
									action : 'LoadTaxonomy',
									ajax : true,
									id_tab : current_id_tab,
									id_lang : $(this).val()
								},
								success : function(data) {
									$body.find('#google_loader').addClass('hidden');
									$body.find('.form-group').show();
									$body.find('#select_google_category').html(data);
									$body.find('#select_google_category').selectpicker('refresh');
									$body.find('#sub_category').selectpicker('hide');
								}
							});
						});

						$body.find('#select_google_category').on('change', function() {
							// Get Sub cat and attributes require
							$.ajax({
								type: 'POST',
								url: admin_module_ajax_url,
								async: true,
								cache: false,
								data: {
									controller : admin_module_controller,
									action : 'modalAction',
									ajax : true,
									id_tab : current_id_tab,
									id_lang : $lang.val(),
									category : $(this).val()
								},
								success : function(data) {
									var action = jQuery.parseJSON(data);

									// Get taxonomy
									if (action.taxonomy !== null)	{
										$body.find('#sub_category').html(action.taxonomy);
										$body.find('#sub_category').selectpicker('refresh');
										$body.find('#sub_category').selectpicker('show');
									}

									// Get attributes
									if (action.attribute !== null)	{
										for(var x = 0 ; x < action.attribute.length; x++)
										{
											$body.find('select.selectattributes').append($('<option>', {
									        value: action.attribute[x].id,
									        text : action.attribute[x].public_name
									    }));
										}

										// Show the right step
										$body.find('#parametersfalse').hide();
										$body.find('#parameterstrue').show();
										// Get attributes names
										$body.find('.selectattributes').selectpicker('refresh');
										$body.find('.selectattributes').selectpicker('show');
									}
									else {
										$body.find('#parameterstrue').hide();
										$body.find('#parametersfalse').show();
										$button.enable();
									}
								}
							});
						});


						// Get attributes details
						$body.find('.selectattributes').on('change', function() {
							myid = $(this).attr('id');

							//Test if all the values is set. If the value was not save button was not enable.
							var set_size = checkSave($body.find('#select_size'));
							var set_gender = checkSave($body.find('#select_gender'));
							var set_age_group = checkSave($body.find('#select_age_group'));
							var set_color = checkSave($body.find('#select_color'));
							setall = set_size + set_gender + set_age_group + set_color;
							if (setall !== 0)
								$button.disable();
							else
								$button.enable();
							$.ajax({
								type: 'POST',
								url: admin_module_ajax_url,
								dataType: 'html',
								async: true,
								cache: false,
								data: {
									controller : admin_module_controller,
									action : 'LoadAttributesDetails',
									ajax : true,
									id_tab : current_id_tab,
									id_lang : $lang.val(),
									id_attribute : $(this).val(),
									type : $(this).attr('data-type'),
								},
								success : function(data) {
									var action = jQuery.parseJSON(data);

									if (action['type'] === 'gender') {
										$body.find('#gender_values').show('fast');
										$body.find('#age_group_values').hide('fast');
										myid = "#gender_values";
									}
									else if (action['type'] === 'age_group') {
										$body.find('#age_group_values').show('fast');
										$body.find('#gender_values').hide('fast');
										myid = "#age_group_values";
									}
									else {
										myid = "#"+myid;
										$body.find('.selectdetailscontainer').hide('fast');
									}
									$body.find(myid+' select.selectdetails').html('');

									var len = action['attr'].length;
									if (len > 0) {
										for(var x = 0 ; x < len; x++)
										{
											$body.find(myid+' select.selectdetails').append($('<option>', {
													value: action['attr'][x].id_attribute,
													text : action['attr'][x].name
											}));
										}
									}

									$body.find(myid+' .selectdetails').selectpicker('refresh');
								}
							});

						});
					},
					message: response_form,
					buttons: [
						// Next
						{
							id: 'next-step',
							label: next_message,
							cssClass: 'btn-default pull-right',
							action: function(dialogRef){
								var $body = dialogRef.getModalBody();
								var $wizardContent = $body.find('#wizard');
								$wizardContent.smartWizard("goForward");
							}
						},
						// Prev
						{
							id: 'back-step',
							label: prev_message,
							cssClass: 'btn-default pull-right',
							action: function(dialogRef){
								var $body = dialogRef.getModalBody();
								var $wizardContent = $body.find('#wizard');
								$wizardContent.smartWizard("goBackward");
							}
						},
						// Close
						{
							label: close_message,
							cssClass: 'btn-default pull-left',
							action: function(dialogRef){
								dialogRef.close();
							}
						},
						//Later
						{
							id: 'later',
							label: later_message,
							cssClass: 'btn-default pull-right',
							action: function(dialogRef){
								dialogRef.close();
							}
						},
						//Active
						{
							id:'active',
							label: active_message,
							cssClass: 'btn-default pull-right',
							action: function(dialogRef){
								dialogRef.close();
								$.ajax({
									type: 'POST',
									url: admin_module_ajax_url,
									dataType: 'html',
									data: {
										controller : admin_module_controller,
										action : 'SwitchAction',
										ajax : true,
										id_tab : current_id_tab,
										id_object : id,
										id_country: id_country,
										active : 1
									},
									success : function(data) {
										if(data === '1') {
											reloadTable(table);
										}
										else	{
											obj.parent().next('td').find('a').click();
										}
									}
								});
							}
						},
						// Save
						{
							id: 'btn-save',
							label: save_message,
							cssClass: 'btn-primary pull-left',
							autospin: true,
							action: function(dialogRef) {
								dialogRef.enableButtons(false);
								dialogRef.setClosable(false);
								form_value = $("#form_add").serializeAnything();
								var $body = dialogRef.getModalBody();
								$body.find('#parameterstrue').hide();
								$body.find('#parametersfalse').hide();
								$body.find('.google_loader').removeClass('hidden');
								dialogRef.getModalFooter().hide();
								$.ajax({
									type: 'POST',
									url: admin_module_ajax_url,
									data: {
										controller : admin_module_controller,
										action : 'SaveCategory',
										ajax : true,
										id_tab : current_id_tab,
										id_category : id_category,
										category_name : category_name,
										id_object : id_object,
										id_country : id_country,
										apply: 0,
										params: form_value
									},
									success: function(jsonData) {
										var $body = dialogRef.getModalBody();
										var $footer = dialogRef.getModalFooter();
										var $later = dialogRef.getButton('later');
										var $active = dialogRef.getButton('active');
										var $button = dialogRef.getButton('btn-save');

										$footer.show();
										$later.show();
										$active.show();
										$button.hide();
										dialogRef.enableButtons(true);

										ps_version = cleanInt(ps_version);
										if (ps_version === 1) {
											error_exist = $(jsonData).find('.module_error').length;
											test_error = (error_exist === 0);
										} else {
											error_exist = $(jsonData).attr('class');
											test_error = (error_exist !== 'module_error alert error');
										}

										$body.show();
										if (test_error) {
											dialogRef.setClosable(true);
											$body.html(jsonData);
											reloadTable(table);
										} else {
											dialogRef.enableButtons(true);
											dialogRef.getModalFooter().show();
											error_already_exist = $body.find('.module_error').length;
											error_already_exist = cleanInt(error_already_exist);
											if (error_already_exist === 0) {
												$(jsonData).insertBefore($body.find('#response_form'));
											}
										}

										if(debug_mode === 0) {
											setTimeout(function(){
												dialogRef.close();
											}, 1000);
										}
									}
								});
							}
						}
					]
				});
			}
		});
	};

	/**
	** Click Event
	*/
	var runEvent = function () {

		if (cleanInt(gshopping_module_school) === 0)
		{
			loadModuleSchool();
		}
		// Click on Edit button
		$('.edit').live('click', function (e) {
			e.preventDefault();
			var id = $(this).attr('data-idobject');
			var category_name = $(this).attr('data-name');
			var id_category = $(this).attr('data-idcategory');
			var id_country = $(this).attr('data-idcountry');
			var table = $(this).closest("table").attr('id');
			loadFormModal(id, category_name, id_category, table, id_country);
		});
		// Click on Delete button
		$('.delete').live('click', function (e) {
			e.preventDefault();
			var id = $(this).attr('role-id');
			var table = $(this).closest("table").attr('id');
			loadDeleteModal(id, table);
		});

		// Click on Generate button
		$('.generate').live('click', function (e) {
			e.preventDefault();
			var id = $(this).attr('role-id');
			var table_id = $(this).parents("table").attr('id');
			loadGenerateModal(id, table_id);
		});

		// Click on State button
		$('.action-enabled, .action-disabled').live('click', function (e) {
			e.preventDefault();
			var table_id = $(this).parents("table").attr('id');
			var cat_id = $(this).parents("tr").attr('id');
			var id_country = $(this).data('idcountry');

			id = cat_id.replace('cat_', '');
			var obj = $(this);
			$.ajax({
				type: 'POST',
				url: admin_module_ajax_url,
				dataType: 'html',
				data: {
					controller : admin_module_controller,
					action : 'SwitchAction',
					ajax : true,
					id_tab : current_id_tab,
					id_object : id,
					id_country: id_country
				},
				success : function(data) {
					if(data === '1') {
						reloadTable(table_id);
					}
					else	{
						obj.parent().next('td').find('a').click();
					}
				}
			});
		});

		// Click on Panel
		$('#modulecontent .tab-content h3 a').live('click', function (e) {
			e.preventDefault();
			var collapse = $(this).attr('data-toggle');
			if (typeof(collapse) !== "undefined" && collapse === 'collapse') {
				var id = $(this).attr('href');
				id = id.replace('#', '');
				id = id.replace('metas', 'meta');
				var is_collapse = false;
				var table_id = '#table-'+id;

				$(this.attributes).each(function() {
					if (this.nodeName === 'class') {
						if(this.nodeValue === '') {
							is_collapse = true;
						}
					}
				});

				if ($(this).attr('class') === undefined) {
					is_collapse = true;
				}

				if(is_collapse) {
					reloadTable(table_id);
				}
			}
		});

		$(".contactus").on('click', function() {
			$href = $.trim($(this).attr('href'));
			$(".list-group a.active").each(function() {
				$(this).removeClass("active");
			});

			$(".list-group a.contacts").addClass("active");
		});

		// Tab panel active
		$(".list-group-item").on('click', function() {
			var $el = $(this).parent().closest(".list-group").children(".active");
			var tab_id = $(this).attr('id');
			if (tab_id === 'drop') {
				$(this).removeClass("active");
				cleanUp();
			}
			else
			{
				if ($el.hasClass("active")) {
					target = $(this).find('i').attr('data-target');
					if (target !== undefined) {
						loadTable('#table-metas-1');
					}
					if (target === "export")	{
						addExports($(this).attr('id_country'));
					}

					$el.removeClass("active");
					$(this).addClass("active");
				}
			}
		});
	};

	/**
	** Custom Elements
	*/
	var runCustomElement = function () {
		// Hide ugly toolbar
		$('table[class="table"]').each(function() {
			$(this).hide();
			$(this).next('div.clear').hide();
		});

		// Custom Select
		$('.selectpicker').selectpicker();

		// Fix bug form builder + bootstrap select
		$('.selectpicker').each(function(){
			var select = $(this);
			select.on('click', function() {
				$(this).parents('.bootstrap-select').addClass('open');
				$(this).parents('.bootstrap-select').toggleClass('open');
			});
		});

		// Show tooltip for helping merchant
		$('a').tooltip();

		// Custom Textarea
		$('.textarea-animated').autosize({append: "\n"});
	};

		//Custom click

		var clickButtonCountry = function() {
				// Show table by country
			$('#select_country').change(function() {
				var id_country = $('#select_country option:selected').attr('id_country');

				$('#id_country').val($('#select_country option:selected').val());
				reloadTable('#table-metas-1');
				addTaxonomyCache(id_country);
			});
			$('#select_country_export').change(function() {
				var id_country = $('#select_country_export option:selected').attr('id_country');

				addExports(id_country);
			});
		};

	return {
		init: function () {
			runEvent();
			runCustomElement();
			clickButtonCountry();
		}
	};
}();

// Load functions
$(window).load(function() {
	Main.init();
});

$ = tmp;