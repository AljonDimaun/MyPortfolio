(function($) {

	'use strict';

	// Set is_admin
	$.WS_Form.prototype.set_is_admin = function() { return false; }

	// One time init for admin page
	$.WS_Form.prototype.init = function() {

		// Build data cache
		this.data_cache_build();

		// Set global variables once for performance
		this.set_globals();
	}

	// Continue initialization after submit data retrieved
	$.WS_Form.prototype.init_after_get_submit = function(submit_retrieved) {


		// Build form
		this.form_build();
	}

	// Set global variables once for performance
	$.WS_Form.prototype.set_globals = function() {

		// Get framework ID
		this.framework_id = $.WS_Form.settings_plugin.framework;

		// Get framework settings
		this.framework = $.WS_Form.frameworks.types[this.framework_id];

		// Get current framework
		this.framework_fields = this.framework['fields']['public'];

		// Get invalid_feedback placeholder mask
		this.invalid_feedback_mask_placeholder = '';
		if(typeof($.WS_Form.meta_keys['invalid_feedback']) !== 'undefined') {

			if(typeof($.WS_Form.meta_keys['invalid_feedback']['p']) !== 'undefined') {

				this.invalid_feedback_mask_placeholder = $.WS_Form.meta_keys['invalid_feedback']['p'];
			}
		}

		// Custom action URL
		this.form_action_custom = (this.form_obj.attr('action') != (ws_form_settings.url_ajax + 'submit'));

		// Get validated class
		var class_validated_array = (typeof(this.framework.fields.public.class_form_validated) !== 'undefined') ? this.framework.fields.public.class_form_validated : [];
		this.class_validated = class_validated_array.join(' ');


		// Hash
		if(
			ws_form_settings.wsf_hash &&
			(typeof(ws_form_settings.wsf_hash) === 'object')
		) {

			// Set hash from query string
			for(var hash_index in ws_form_settings.wsf_hash) {

				if(!ws_form_settings.wsf_hash.hasOwnProperty(hash_index)) { continue; }

				var wsf_hash = ws_form_settings.wsf_hash[hash_index];

				if(
					(typeof(wsf_hash.id) !== 'undefined') &&
					(typeof(wsf_hash.hash) !== 'undefined') &&
					(typeof(wsf_hash.token) !== 'undefined') &&
					(wsf_hash.id == this.form_id)
				) {

					this.hash_set(wsf_hash.hash, wsf_hash.token, true);
				}
			}

		} else {

			// Set hash from cookie
			this.hash_set(this.cookie_get('hash', ''), false, true);
		}

		// Visual editor?
		this.visual_editor = (typeof(this.form_canvas_obj.attr('data-visual-builder')) !== 'undefined');

		// Read submission data if hash is defined
		var ws_this = this;
		if(this.hash) {

			var url = 'submit/hash/' + this.hash + '/';
			if(this.token) { url += this.token + '/'; }

			// Call AJAX request
			$.WS_Form.this.api_call(url, 'GET', false, function(response) {

				if(typeof(response.data) !== 'undefined') {

					// Save the submissions data
					ws_this.submit = response.data;
				}

				// Initialize after getting submit
				ws_this.init_after_get_submit(true);

				// Finished with submit data
				ws_this.submit = false;

			}, function(response) {

				// Read auto populate data instead
				ws_this.read_json_populate();

				// Initialize after getting submit
				ws_this.init_after_get_submit(false);
			});

		} else {

			// Read auto populate data
			this.read_json_populate();

			// Initialize after getting submit
			this.init_after_get_submit(false);
		}
	}

	// Read auto populate data
	$.WS_Form.prototype.read_json_populate = function() {

		if(typeof(wsf_form_json_populate) !== 'undefined') {

			if(typeof(wsf_form_json_populate[this.form_id]) !== 'undefined') {

				this.submit_auto_populate = wsf_form_json_populate[this.form_id];
			}
		}
	}


	// Render an error message
	$.WS_Form.prototype.error = function(language_id, variable, error_class) {

		if(typeof(variable) == 'undefined') { variable = ''; }
		if(typeof(error_class) == 'undefined') { error_class = ''; }

		// Build error message
		var error_message = this.language(language_id, variable, false).replace(/%s/g, variable);

		// Show error message
		if(!this.visual_editor && this.get_object_meta_value(this.form, 'submit_show_errors', true)) {

			this.action_message(error_message);
		}

		if(window.console && window.console.error) { console.error(error_message); }
	}

	// Render any interface elements that rely on the form object
	$.WS_Form.prototype.form_render = function() {

		// Timer
		this.form_timer();


		// Initialize framework
		this.form_framework();

		// Form preview
		this.form_preview();

		// Groups - Tabs - Initialize
		this.form_tabs();


		// Navigation
		this.form_navigation();


		// Client side form validation
		this.form_validation();

		// Select all
		this.form_select_all();

		// Select min max
		this.form_select_min_max();


		// Checkbox min max
		this.form_checkbox_min_max();

		// Text input and textarea character and word count
		this.form_character_word_count();

		// Tel inputs
		this.form_tel();


		// Required
		this.form_required();

		// Input masks
		this.form_inputmask();

		// Spam protection
		this.form_spam_protection();

		// Bypass
		this.form_bypass_enabled = true;
		this.form_bypass(false);
		// Form stats
		this.form_stat();

		// Form validation - Real time
		this.form_validate_real_time();


		// Trigger rendered event
		this.trigger('rendered');


		// Set data-wsf-rendered attribute
		this.form_obj.attr('data-wsf-rendered', '');
	}

	// Duration tracking timer
	$.WS_Form.prototype.form_timer = function() {

		// Check for date start cookie
		this.date_start = this.cookie_get('date_start', false);

		if((this.date_start === false) || isNaN(this.date_start) || (this.date_start == '')) {

			this.date_start = new Date().getTime();

			// Set cookie if duration track enabled
			if(this.get_object_meta_value(this.form, 'tracking_duration', 'on') == 'on') {

				this.cookie_set('date_start', this.date_start, false);
			}
		}
	}

	// Trigger events
	$.WS_Form.prototype.trigger = function(slug) {

		// New method
		var action_type = 'wsf-' + slug;
		$(document).trigger(action_type, [this.form, this.form_id, this.form_instance_id, this.form_obj, this.form_canvas_obj]);

		// Legacy method - Instance
		var trigger_instance = 'wsf-' + slug + '-instance-' + this.form_instance_id;
		$(window).trigger(trigger_instance);

		// Legacy method - Form
		var trigger_form = 'wsf-' + slug + '-form-' + this.form_id;
		$(window).trigger(trigger_form);
	}

	// Initialize JS
	$.WS_Form.prototype.form_framework = function() {

		// Add framework form attributes
		if(
			(typeof(this.framework.form.public) !== 'undefined') &&
			(typeof(this.framework.form.public.attributes) === 'object')
		) {

			for(var attribute in this.framework.form.public.attributes) {

				var attribute_value = this.framework.form.public.attributes[attribute];

				this.form_obj.attr(attribute, attribute_value);
			}
		}

		// Check framework init_js
		if(typeof(this.framework.init_js) !== 'undefined') {

			// Framework init JS values
			var framework_init_js_values = {'form_canvas_selector': '#' + this.form_obj_id};
			var framework_init_js = this.mask_parse(this.framework.init_js, framework_init_js_values);

			try {

				$.globalEval('(function($) { ' + framework_init_js + ' })(jQuery);');

			} catch(e) {

				this.error('error_js', action_javascript);
			}
		}
	}

	// Form - Reset
	$.WS_Form.prototype.form_reset = function(e) {

		var ws_this = this;

		// Trigger
		this.trigger('reset-before');

		// Unmark as validated
		this.form_obj.removeClass(this.class_validated);

		// HTML form reset
		this.form_obj[0].reset();
		// Trigger
		this.trigger('reset-complete');
	}

	// Form - Clear
	$.WS_Form.prototype.form_clear = function() {

		var ws_this = this;

		// Trigger
		this.trigger('clear-before');

		// Unmark as validated
		this.form_obj.removeClass(this.class_validated);

		// Clear fields
		for(var key in this.field_data_cache) {

			if(!this.field_data_cache.hasOwnProperty(key)) { continue; }

			var field = this.field_data_cache[key];

			var field_id = field.id;
			var field_name = this.field_name_prefix + field_id;

			var field_type_config = $.WS_Form.field_type_cache[field.type];
			var trigger = (typeof(field_type_config.trigger) !== 'undefined') ? field_type_config.trigger : 'change';

			// Clear value
			switch(field.type) {

				case 'checkbox' :
				case 'price_checkbox' :
				case 'radio' :
				case 'price_radio' :

					$('[name="' + field_name + '"], [name^="' + field_name + '["]', this.form_canvas_obj).each(function() {

						if($(this).is(':checked')) {
	
							$(this).prop('checked', false).trigger(trigger);
						}
					});

					break;

				case 'select' :
				case 'price_select' :

					$('[name="' + field_name + '"], [name^="' + field_name + '["] option', this.form_canvas_obj).each(function() {

						if($(this).is(':selected')) {
	
							$(this).prop('selected', false);
							$(this).closest('select').trigger(trigger);
						}
					});

					break;

				case 'textarea' :

					$('[name="' + field_name + '"], [name^="' + field_name + '["]', this.form_canvas_obj).each(function() {

						if($(this).val() != '') {

							$(this).val('').trigger(trigger);
							ws_this.textarea_set_value($(this), '');
						}
					});

					break;

				case 'color' :

					$('[name="' + field_name + '"], [name^="' + field_name + '["]', this.form_canvas_obj).each(function() {

						if($(this).val() != '') {

							$(this).val('').trigger(trigger);

							if($(this).hasClass('minicolors-input')) {

								$(this).minicolors('value', '');
							}
						}
					});

					break;

				default:

					$('[name="' + field_name + '"], [name^="' + field_name + '["]', this.form_canvas_obj).each(function() {

						if($(this).val() != '') {

							$(this).val('').trigger(trigger);
						}
					});
			}
		}

		// Trigger
		this.trigger('clear-complete');
	}

	// Form reload
	$.WS_Form.prototype.form_reload = function() {

		// Read submission data if hash is defined
		var ws_this = this;
		if(this.hash != '') {

			// Call AJAX request
			$.WS_Form.this.api_call('submit/hash/' + this.hash, 'GET', false, function(response) {

				// Save the submissions data
				ws_this.submit = response.data;

				ws_this.form_reload_after_get_submit(true);

				// Finished with submit data
				ws_this.submit = false;

			}, function(response) {

				ws_this.form_reload_after_get_submit(false);
			});

		} else {

			// Reset submit
			this.submit = false;

			this.form_reload_after_get_submit(false);
		}
	}

	// Form reload - After get submit
	$.WS_Form.prototype.form_reload_after_get_submit = function(submit_retrieved) {

		// Clear any messages
		$('[data-wsf-message][data-wsf-instance-id="' + this.form_instance_id + '"]').remove();

		// Show the form
		this.form_canvas_obj.show();

		// Reset form tag
		this.form_canvas_obj.removeClass(this.class_validated)

		// Clear ecommerce real time validation hooks
		this.form_validation_real_time_hooks = [];

		// Empty form object
		this.form_canvas_obj.empty();

		// Build form
		this.form_build();
	}

	// Form - Hash reset
	$.WS_Form.prototype.form_hash_clear = function() {

		// Clear hash variable
		this.hash = '';

		// Clear hash cookie
		this.cookie_clear('hash')

	}


	// Form navigation
	$.WS_Form.prototype.form_navigation = function() {

		var ws_this = this;

		var group_count = $('.wsf-group-tabs', this.form_canvas_obj).children(':not([data-wsf-group-hidden])').length;

		// Buttons - Next
		$('[data-action="wsf-tab_next"]', this.form_canvas_obj).each(function() {

			// Remove existing click event
			$(this).off('click');

			// Get next group
			var group_next = $(this).closest('[data-group-index]').nextAll(':not([data-wsf-group-hidden])').first();

			// If there are no tabs, or no next tab, disable the next button
			if(
				(group_count <= 1) ||
				(!group_next.length)
			) {
				$(this).attr('disabled', '').attr('data-wsf-disabled', '');

			} else {

				if(typeof($(this).attr('data-wsf-disabled')) !== 'undefined') { $(this).removeAttr('disabled').removeAttr('data-wsf-disabled'); }
			}

			// If button is disabled, then don't initialize
			if(typeof($(this).attr('disabled')) !== 'undefined') { return; }

			// Add click event
			$(this).on('click', function() {

				ws_this.group_index_new($(this), group_next.attr('data-group-index'));
			});
		});

		// Buttons - Previous
		$('[data-action="wsf-tab_previous"]', this.form_canvas_obj).each(function() {

			// Remove existing click event
			$(this).off('click');

			// Get previous group
			var group_previous = $(this).closest('[data-group-index]').prevAll(':not([data-wsf-group-hidden])').first();

			// If there are no tabs, or no previous tab, disable the previous button
			if(
				(group_count <= 1) ||
				(!group_previous.length)
			) {
				$(this).attr('disabled', '').attr('data-wsf-disabled', '');

			} else {

				if(typeof($(this).attr('data-wsf-disabled')) !== 'undefined') { $(this).removeAttr('disabled').removeAttr('data-wsf-disabled'); }
			}

			// If button is disabled, then don't initialize
			if(typeof($(this).attr('disabled')) !== 'undefined') { return; }

			// Add click event
			$(this).on('click', function() {

				ws_this.group_index_new($(this), group_previous.attr('data-group-index'));
			});
		});

		// Buttons - Save
		this.form_canvas_obj.off('click', '[data-action="wsf-save"]').on('click', '[data-action="wsf-save"]', function() {

			// Get field
			var field = ws_this.get_field($(this));

			if(typeof(field) !== 'undefined') {

				var validate_form = ws_this.get_object_meta_value(field, 'validate_form', '');

				if(validate_form) {

					ws_this.form_post_if_validated('save');

				} else {

					ws_this.form_post('save');
				}
			}
		});

		// Buttons - Reset
		this.form_canvas_obj.off('click', '[data-action="wsf-reset"]').on('click', '[data-action="wsf-reset"]', function(e) {

			// Prevent default
			e.preventDefault();

			ws_this.form_reset();
		});

		// Buttons - Clear
		this.form_canvas_obj.off('click', '[data-action="wsf-clear"]').on('click', '[data-action="wsf-clear"]', function() {

			ws_this.form_clear();
		});
	}

	// Tab - Activate by offset amount
	$.WS_Form.prototype.group_index_new = function(obj, group_index_new) {

		// Activate tab
		this.group_index_set(group_index_new);

		// Get field ID
		var field_id = obj.closest('[data-id]').attr('data-id');
		var field = this.field_data_cache[field_id];
		var scroll_to_top = this.get_object_meta_value(field, 'scroll_to_top', '');
		var scroll_to_top_offset = this.get_object_meta_value(field, 'scroll_to_top_offset', '0');
		scroll_to_top_offset = (scroll_to_top_offset == '') ? 0 : parseInt(scroll_to_top_offset, 10);
		var scroll_position = this.form_canvas_obj.offset().top - scroll_to_top_offset;

		switch(scroll_to_top) {

			// Instant
			case 'instant' :

				$('html,body').scrollTop(scroll_position);

				break;

			// Smooth
			case 'smooth' :

				var scroll_to_top_duration = this.get_object_meta_value(field, 'scroll_to_top_duration', '0');
				scroll_to_top_duration = (scroll_to_top_duration == '') ? 0 : parseInt(scroll_to_top_duration, 10);

				$('html,body').animate({

					scrollTop: scroll_position

				}, scroll_to_top_duration);

				break;
		}
	}

	// Tab - Set
	$.WS_Form.prototype.group_index_set = function(group_index) {

		if(this.form.groups.length <= 1) { return false; }

		var framework_tabs = this.framework['tabs']['public'];

		if(typeof(framework_tabs.activate_js) !== 'undefined') {

			var activate_js = framework_tabs.activate_js;	

			if(activate_js != '') {

				// Parse activate_js
				var mask_values = {'form': '#' + this.form_obj_id, 'index': group_index};
				var activate_js_parsed = this.mask_parse(activate_js, mask_values);

				// Execute activate tab javascript
				$.globalEval('(function($) { $(function() {' + activate_js_parsed + '}); })(jQuery);');

				// Set cookie
				this.cookie_set('tab_index', group_index);
			}
		}

	}

	// Get tab index object resides in
	$.WS_Form.prototype.get_group_index = function(obj) {

		var group_count = $('.wsf-tabs', this.form_canvas_obj).children(':visible').length;
		if(group_count <= 1) { return false; }

		// Get group
		var group_single = obj.closest('[data-group-index]');
		if(group_single.length == 0) { return false; }

		// Get group index
		var group_index = group_single.first().attr('data-group-index');
		if(group_index == undefined) { return false; }

		return parseInt(group_index, 10);
	}

	// Get section id from object
	$.WS_Form.prototype.get_section_id = function(obj) {

		var section_id = obj.closest('[id^="' + this.form_id_prefix + 'section-"]').attr('data-id');

		return (typeof(section_id) !== 'undefined') ? parseInt(section_id, 10) : false;
	}

	// Get section repeatable index from object
	$.WS_Form.prototype.get_section_repeatable_index = function(obj) {

		var section_repeatable_index = obj.closest('[id^="' + this.form_id_prefix + 'section-"]').attr('data-repeatable-index');

		return (section_repeatable_index > 0) ? parseInt(section_repeatable_index, 10) : 0;
	}

	// Get section repeatable suffix from object
	$.WS_Form.prototype.get_section_repeatable_suffix = function(obj) {

		var section_repeatable_index = this.get_section_repeatable_index(obj);

		return section_repeatable_index ? '-repeat-' + section_repeatable_index : '';
	}

	// Get field from obj
	$.WS_Form.prototype.get_field = function(obj) {

		var field_id = this.get_field_id(obj);

		return field_id ? this.field_data_cache[field_id] : false;
	}

	// Get field wrapper from object
	$.WS_Form.prototype.get_field_wrapper = function(obj) {

		return obj.closest('[data-id]')
	}

	// Get field id from object
	$.WS_Form.prototype.get_field_id = function(obj) {

		var field_id = obj.closest('[data-type]').attr('data-id');

		return (typeof(field_id) !== 'undefined') ? parseInt(field_id, 10) : false;
	}

	// Get field type from object
	$.WS_Form.prototype.get_field_type = function(obj) {

		var field_type = obj.closest('[data-type]').attr('data-type');

		return (typeof(field_type) !== 'undefined') ? field_type : false;
	}

	// Get help from object
	$.WS_Form.prototype.get_help_obj = function(obj) {

		var field_id = this.get_field_id(obj);
		var section_repeatable_suffix = this.get_section_repeatable_suffix(obj);

		return $('#' + this.form_id_prefix + 'help-' + field_id + section_repeatable_suffix, this.form_canvas_obj);
	}

	// Get invalid feedback from object
	$.WS_Form.prototype.get_invalid_feedback_obj = function(obj) {

		var field_id = this.get_field_id(obj);
		var section_repeatable_suffix = this.get_section_repeatable_suffix(obj);

		return $('#' + this.form_id_prefix + 'invalid-feedback-' + field_id + section_repeatable_suffix, this.form_canvas_obj);
	}

	// Set invalid feedback on object
	$.WS_Form.prototype.set_invalid_feedback = function(obj, message, object_row_id) {

		// Check for object row
		if(typeof(object_row_id) === 'undefined') { object_row_id = 0; }

		// Get invalid feedback obj
		var invalid_feedback_obj = this.get_invalid_feedback_obj(obj);

		// Get section ID
		var section_id = this.get_section_id(obj);

		// Get section repeatable index
		var section_repeatable_index = this.get_section_repeatable_index(obj);

		// Get field ID
		var field_id = this.get_field_id(obj);

		// Check for false message
		if(message === false) { message = invalid_feedback_obj.html(); }

		var message_invalid_feedback = message;

		// HTML 5 custom validity
		if(obj.length && obj[0].willValidate) {

			if(message !== '') {

				// Store message
				if(typeof(this.validation_message_cache[section_id]) === 'undefined') { this.validation_message_cache[section_id] = []; }
				if(typeof(this.validation_message_cache[section_id][section_repeatable_index]) === 'undefined') { this.validation_message_cache[section_id][section_repeatable_index] = []; }
				if(typeof(this.validation_message_cache[section_id][section_repeatable_index][field_id]) === 'undefined') { this.validation_message_cache[section_id][section_repeatable_index][field_id] = []; }

				this.validation_message_cache[section_id][section_repeatable_index][field_id][object_row_id] = message;

			} else {

				// Recall message
				if(
					(typeof(this.validation_message_cache[section_id]) !== 'undefined') &&
					(typeof(this.validation_message_cache[section_id][section_repeatable_index]) !== 'undefined') &&
					(typeof(this.validation_message_cache[section_id][section_repeatable_index][field_id]) !== 'undefined') &&
					(typeof(this.validation_message_cache[section_id][section_repeatable_index][field_id][object_row_id]) !== 'undefined')
				) {

					delete this.validation_message_cache[section_id][section_repeatable_index][field_id][object_row_id];
				}
			}

			// Set custom validity
			obj[0].setCustomValidity(message);
		}

		// Invalid feedback text
		if(invalid_feedback_obj.length) {

			if(message !== '') {

				// Store invalid feedback
				if(typeof(this.invalid_feedback_cache[section_id]) === 'undefined') { this.invalid_feedback_cache[section_id] = []; }
				if(typeof(this.invalid_feedback_cache[section_id][section_repeatable_index]) === 'undefined') { this.invalid_feedback_cache[section_id][section_repeatable_index] = []; }
				if(typeof(this.invalid_feedback_cache[section_id][section_repeatable_index][field_id]) === 'undefined') { this.invalid_feedback_cache[section_id][section_repeatable_index][field_id] = []; }

				if(typeof(this.invalid_feedback_cache[section_id][section_repeatable_index][field_id][object_row_id]) === 'undefined') {

					this.invalid_feedback_cache[section_id][section_repeatable_index][field_id][object_row_id] = invalid_feedback_obj.html();
				}

				// Set invalid feedback
				invalid_feedback_obj.html(message);

			} else {

				// Recall invalid feedback
				if(
					(typeof(this.invalid_feedback_cache[section_id]) !== 'undefined') &&
					(typeof(this.invalid_feedback_cache[section_id][section_repeatable_index]) !== 'undefined') &&
					(typeof(this.invalid_feedback_cache[section_id][section_repeatable_index][field_id]) !== 'undefined') &&
					(typeof(this.invalid_feedback_cache[section_id][section_repeatable_index][field_id][object_row_id]) !== 'undefined')
				) {

					invalid_feedback_obj.html(this.invalid_feedback_cache[section_id][section_repeatable_index][field_id][object_row_id]);

					delete this.invalid_feedback_cache[section_id][section_repeatable_index][field_id][object_row_id];
				}
			}
		}
	}

	// Form preview
	$.WS_Form.prototype.form_preview = function() {

		if(this.form_canvas_obj[0].hasAttribute('data-preview')) {

			this.form_add_hidden_input('wsf_preview', 'true');
		}
	}

	// Form spam protection
	$.WS_Form.prototype.form_spam_protection = function() {

		// Honeypot
		var honeypot = this.get_object_meta_value(this.form, 'honeypot', false);

		if(honeypot) {

			// Add honeypot field
			var honeypot_hash = (this.form.published_checksum != '') ? this.form.published_checksum : ('honeypot_unpublished_' + this.form_id);

			// Build honeypot input
			var framework_type = $.WS_Form.settings_plugin.framework;
			var framework = $.WS_Form.frameworks.types[framework_type];
			var fields = this.framework['fields']['public'];
			var honeypot_attributes = (typeof(fields.honeypot_attributes) !== 'undefined') ? ' ' + fields.honeypot_attributes.join(' ') : '';

			// Add to form
			this.form_add_hidden_input('field_' + honeypot_hash, '', false, 'autocomplete="off"' + honeypot_attributes);

			// Hide it
			var honeypot_obj = $('[name="field_' + honeypot_hash + '"]', this.form_canvas_obj);
			honeypot_obj.css({'position': 'absolute', 'left': '-9999em'});

		}
	}

	// Adds international telephone input elements
	$.WS_Form.prototype.form_tel = function() {

		var ws_this = this;

		// Get tel objects
		var tel_objects = $('[data-intl-tel-input]:not([data-init-intl-tel-input])', this.form_canvas_obj);
		if(!tel_objects.length) { return false;}

		// Process each tel object
		tel_objects.each(function() {

			// Flag so it only initializes once
			$(this).attr('data-init-intl-tel-input', '');

			// Stylesheet
			if(!$('#wsf-intl-tel-input').length) {

				var image_path = (ws_form_settings.url_cdn == 'cdn') ? 'https://cdn.jsdelivr.net/npm/intl-tel-input@17.0.13/build/img/' : ws_form_settings.url_plugin + 'public/images/external/';

				$('body').append("<style id=\"wsf-intl-tel-input\">\n	.iti { width: 100%; }\n	.iti__flag { background-image: url(\"" + image_path + "flags.png\");}\n	.iti--allow-dropdown input, .iti--allow-dropdown input[type=tel], .iti--allow-dropdown input[type=text], .iti--separate-dial-code input, .iti--separate-dial-code input[type=tel], .iti--separate-dial-code input[type=text] {\n		padding-right: 6px;\n		padding-left: 52px;\n		margin-left: 0;\n	}\n	@media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {\n		.iti__flag { background-image: url(\"" + image_path + "flags@2x.png\"); }\n	}\n\n");
			}

			// Build config
			var config = {

				utilsScript: ((ws_form_settings.url_cdn == 'cdn') ? 'https://cdn.jsdelivr.net/npm/intl-tel-input@17.0.13/build/js/utils.js' : ws_form_settings.url_plugin + 'public/js/external/utils.js?ver=17.0.13')
			}

			// Get field wrapper
			var field_wrapper_obj = ws_this.get_field_wrapper($(this));

			// Get field ID
			var field_id = ws_this.get_field_id($(this));

			// Get field
			var field = ws_this.field_data_cache[field_id];

			// Config - Allow dropdown
			config.allowDropdown = (ws_this.get_object_meta_value(field, 'intl_tel_input_allow_dropdown', 'on') == 'on');

			// Config - Auto placeholder
			config.autoPlaceholder = (ws_this.get_object_meta_value(field, 'intl_tel_input_auto_placeholder', 'on') == 'on') ? 'polite' : 'off';

			// Config - National mode
			config.nationalMode = (ws_this.get_object_meta_value(field, 'intl_tel_input_national_mode', 'on') == 'on');

			// Config - Separate dial code
			config.separateDialCode = (ws_this.get_object_meta_value(field, 'intl_tel_input_separate_dial_code', '') == 'on');

			// Config - Initial country
			config.initialCountry = ws_this.get_object_meta_value(field, 'intl_tel_input_initial_country', '');

			// Config - Geolookup
			if(config.initialCountry == 'auto') {

				config.geoIpLookup = function(callback) {

					$.get('https://ipinfo.io', function() {}, 'jsonp').always(function(resp) {

						var country_code = (resp && resp.country) ? resp.country : 'us';

						callback(country_code);
					});
				};
			}

			// Config - Only countries
			var only_countries = ws_this.get_object_meta_value(field, 'intl_tel_input_only_countries', []);

			if(
				(typeof(only_countries) === 'object') &&
				(only_countries.length > 0)
			) {

				config.onlyCountries = only_countries.map(function(row) { return row.country_alpha_2; });
			}

			// Config - Preferred countries
			var preferred_countries = ws_this.get_object_meta_value(field, 'intl_tel_input_preferred_countries', []);

			if(
				(typeof(preferred_countries) === 'object') &&
				(preferred_countries.length > 0)
			) {

				config.preferredCountries = preferred_countries.map(function(row) { return row.country_alpha_2; });
			}

			// Initialize intlTelInput
			var iti = window.intlTelInput($(this)[0], config);

			// Set flag container height (so invalid feedback does not break the styling)
			$('.iti__flag-container', field_wrapper_obj).css({height:$('.iti', field_wrapper_obj).height()});

			// Custom invalid feedback text
			var invalid_feedback_obj = ws_this.get_invalid_feedback_obj($(this));

			// Move invalid feedback div
			invalid_feedback_obj.insertAfter($(this));

			// Validation
			$(this).on('keyup change input', function() {

				// Get iti instance
				var iti = window.intlTelInputGlobals.getInstance($(this)[0]);

				// Check if valid
				if(
					($(this).val() == '') ||
					iti.isValidNumber()
				) {

					// Reset feedback
					ws_this.set_invalid_feedback($(this), '');

				} else {

					// Get field ID
					var field_id = ws_this.get_field_id($(this));

					// Get field
					var field = ws_this.field_data_cache[field_id];

					// Config - Allow dropdown
					var intl_tel_input_errors = [

						ws_this.get_object_meta_value(field, 'intl_tel_input_label_number', ws_this.language('iti_number')),
						ws_this.get_object_meta_value(field, 'intl_tel_input_label_country_code', ws_this.language('iti_country_code')),
						ws_this.get_object_meta_value(field, 'intl_tel_input_label_short', ws_this.language('iti_short')),
						ws_this.get_object_meta_value(field, 'intl_tel_input_label_long', ws_this.language('iti_long')),
						ws_this.get_object_meta_value(field, 'intl_tel_input_label_number', ws_this.language('iti_number'))
					];

					// Get error number
					var error_code = iti.getValidationError();

					// Get invalid feedback
					var invalid_feedback = (typeof(intl_tel_input_errors[error_code]) !== 'undefined') ? intl_tel_input_errors[error_code] : '';

					// Invalid feedback
					ws_this.set_invalid_feedback($(this), invalid_feedback);
				}
			});

			// Fire real time form validation
			ws_this.form_validate_real_time_process(false);
		});
	}

	// Adds required string (if found in framework config) to all labels
	$.WS_Form.prototype.form_required = function() {

		var ws_this = this;

		// Get required label HTML
		var label_required = this.get_object_meta_value(this.form, 'label_required', false);
		if(!label_required) { return false; }

		var label_mask_required = this.get_object_meta_value(this.form, 'label_mask_required', '', true, true);
		if(label_mask_required == '') {

			// Use framework mask_required_label
			var framework_type = $.WS_Form.settings_plugin.framework;
			var framework = $.WS_Form.frameworks.types[framework_type];
			var fields = this.framework['fields']['public'];

			if(typeof(fields.mask_required_label) === 'undefined') { return false; }
			var label_mask_required = fields.mask_required_label;
			if(label_mask_required == '') { return false; }
		}

		// Get all labels in this form
		$('label', this.form_canvas_obj).each(function() {

			// Get 'for' attribute of label
			var label_for = $(this).attr('for');
			if(label_for === undefined) { return; }

			// Get field related to 'for'
			var field_obj = $('[id="' + label_for + '"]', ws_this.form_canvas_obj);
			if(!field_obj.length) { return; }

			// Check if field should be processed
			if(typeof(field_obj.attr('data-init-required')) !== 'undefined') { return; }

			// Check if field is required
			var field_required = (typeof(field_obj.attr('data-required')) !== 'undefined');

			// Check if the require string should be added to the parent label (e.g. for radios)
			var label_required_id = $(this).attr('data-label-required-id');
			if((typeof(label_required_id) !== 'undefined') && (label_required_id !== false)) {

				var label_obj = $('#' + label_required_id, ws_this.form_canvas_obj);

			} else {

				var label_obj = $(this);
			}

			// Check if wsf-required-wrapper span exists, if not, create it (You can manually insert it in config using #required)
			var required_wrapper = $('.wsf-required-wrapper', label_obj);
			if(!required_wrapper.length && field_required) {

				var required_wrapper_html = '<span class="wsf-required-wrapper"></span>';

				// If field is wrapped in label, find the first the first element to inject the required wrapper before
				var first_child = label_obj.children('div,[name]').first();

				// Add at appropriate place
				if(first_child.length) {

					first_child.before(required_wrapper_html);

				} else {

					label_obj.append(required_wrapper_html);
				}

				required_wrapper = $('.wsf-required-wrapper', label_obj);
			}

			if(field_required) {

				// Add it
				required_wrapper.html(label_mask_required);
				field_obj.attr('data-init-required', '');

			} else {

				// Remove it
				required_wrapper.html('');
				field_obj.removeAttr('data-init-required');
			}
		});
	}

	// Field required bypass
	$.WS_Form.prototype.form_bypass = function(conditional_initiated) {

		if(!this.form_bypass_enabled) { return; }

		var ws_this = this;

		// Look for number fields and add step="1" if none present
		// This ensures that if a number field is hidden that the data-step-bypass is added with step="any" so that the form submits
		$('input[type="number"]:not([step]):not([data-step-bypass])', this.form_canvas_obj).attr('step', 1);

		// Process attributes that should be bypassed if a field is hidden
		var attributes = {

			'required':						{'bypass': 'data-required-bypass', 'not': '[type="hidden"]'},
			'aria-required':				{'bypass': 'data-aria-required-bypass', 'not': '[type="hidden"]'},
			'min':							{'bypass': 'data-min-bypass', 'not': '[type="hidden"],[type="range"]'},
			'max':							{'bypass': 'data-max-bypass', 'not': '[type="hidden"],[type="range"]'},
			'minlength':					{'bypass': 'data-minlength-bypass', 'not': '[type="hidden"]'},
			'maxlength':					{'bypass': 'data-maxlength-bypass', 'not': '[type="hidden"]'},
			'pattern':						{'bypass': 'data-pattern-bypass', 'not': '[type="hidden"]'},
			'step':							{'bypass': 'data-step-bypass', 'not': '[type="hidden"],[type="range"]', 'replace': 'any'},
		};

		for(var attribute_source in attributes) {

			if(!attributes.hasOwnProperty(attribute_source)) { continue; }

			var attribute_config = attributes[attribute_source];

			var attribute_bypass = attribute_config.bypass;
			var attribute_not = attribute_config.not;
			var attribute_replace = (typeof(attribute_config.replace) !== 'undefined') ? attribute_config.replace : false;

			// If a group is visible, and contains fields that have a data bypass attribute, reset that attribute
			if($('[' + attribute_bypass + '-group]', this.form_canvas_obj).length) {

				$('[id^="' + this.form_id_prefix + 'group-"]:not([data-wsf-group-hidden]) [' + attribute_bypass + '-group]:not(' + attribute_not + ')', this.form_canvas_obj).attr(attribute_source, function() { return $(this).attr(attribute_bypass + '-group'); }).removeAttr(attribute_bypass + '-group');
			}

			// If a group is not visible, and contains validation attributes, add bypass attributes
			if($('[' + attribute_source + ']', this.form_canvas_obj).length) {

				$('[id^="' + this.form_id_prefix + 'group-"][data-wsf-group-hidden] [' + attribute_source + ']:not(' + attribute_not + '), [id^="' + this.form_id_prefix + 'group-"][data-wsf-group-hidden] [' + attribute_source + ']:not(' + attribute_not + ')').attr(attribute_bypass + '-group', function() { return ws_this.form_bypass_hidden($(this), attribute_source, attribute_replace); });
			}

			// If a hidden field is in a hidden group, convert bypass address to group level
			if($('[' + attribute_bypass + ']', this.form_canvas_obj).length) {

				$('[id^="' + this.form_id_prefix + 'group-"][data-wsf-group-hidden] [' + attribute_bypass + ']:not(' + attribute_not + '), [id^="' + this.form_id_prefix + 'group-"][data-wsf-group-hidden] [' + attribute_bypass + ']:not(' + attribute_not + ')').attr(attribute_bypass + '-group', function() { return ws_this.form_bypass_visible($(this), attribute_bypass); }).removeAttr(attribute_bypass);
			}


			// If a section is visible, and contains fields that have a data bypass attribute, reset that attribute
			if($('[' + attribute_bypass + '-section]', this.form_canvas_obj).length) {

				$('[id^="' + this.form_id_prefix + 'section-"][style!="display:none;"][style!="display: none;"] [' + attribute_bypass + '-section]:not(' + attribute_not + ')', this.form_canvas_obj).attr(attribute_source, function() { return $(this).attr(attribute_bypass + '-section'); }).removeAttr(attribute_bypass + '-section');
			}

			// If a section is not visible, and contains validation attributes, add bypass attributes
			if($('[' + attribute_source + ']', this.form_canvas_obj).length) {

				$('[id^="' + this.form_id_prefix + 'section-"][style="display:none;"] [' + attribute_source + ']:not(' + attribute_not + '), [id^="' + this.form_id_prefix + 'section-"][style="display: none;"] [' + attribute_source + ']:not(' + attribute_not + ')').attr(attribute_bypass + '-section', function() { return ws_this.form_bypass_hidden($(this), attribute_source, attribute_replace); });
			}

			// If a hidden field is in a hidden section, convert bypass address to section level
			if($('[' + attribute_bypass + ']', this.form_canvas_obj).length) {

				$('[id^="' + this.form_id_prefix + 'section-"][style="display:none;"] [' + attribute_bypass + ']:not(' + attribute_not + '), [id^="' + this.form_id_prefix + 'section-"][style="display: none;"] [' + attribute_bypass + ']:not(' + attribute_not + ')').attr(attribute_bypass + '-section', function() { return ws_this.form_bypass_visible($(this), attribute_bypass); }).removeAttr(attribute_bypass);
			}


			// If field is visible, add validation attributes back that have a bypass data tag
			if($('[' + attribute_bypass + ']', this.form_canvas_obj).length) {

				$('[id^="' + this.form_id_prefix + 'field-wrapper-"][style!="display:none;"][style!="display: none;"] [' + attribute_bypass + ']:not(' + attribute_not + ')', this.form_canvas_obj).attr(attribute_source, function() { return ws_this.form_bypass_visible($(this), attribute_bypass); }).removeAttr(attribute_bypass);
			}

			// If field is not visible, add contain validation attributes, add bypass attributes
			if($('[' + attribute_source + ']', this.form_canvas_obj).length) {

				$('[id^="' + this.form_id_prefix + 'field-wrapper-"][style="display:none;"] [' + attribute_source + ']:not(' + attribute_not + '), [id^="' + this.form_id_prefix + 'field-wrapper-"][style="display: none;"] [' + attribute_source + ']:not(' + attribute_not + ')', this.form_canvas_obj).attr(attribute_bypass, function() { return ws_this.form_bypass_hidden($(this), attribute_source, attribute_replace); });
			}
		}

		// Process custom validity messages - Groups
		$('[id^="' + this.form_id_prefix + 'group-"]:not([data-wsf-group-hidden])', this.form_canvas_obj).find('[name]:not([type="hidden"]),[data-static],[data-recaptcha-type],[data-hcaptcha-type]').each(function() {

			ws_this.form_bypass_process($(this), '-group', false);
		});

		$('[id^="' + this.form_id_prefix + 'group-"][data-wsf-group-hidden]').find('[name]:not([type="hidden"]),[data-static],[data-recaptcha-type],[data-hcaptcha-type]').each(function() {

			ws_this.form_bypass_process($(this), '-group', true);
		});

		// Process custom validity messages - Sections
		$('[id^="' + this.form_id_prefix + 'section-"][style!="display:none;"][style!="display: none;"]', this.form_canvas_obj).find('[name]:not([type="hidden"],[data-hidden-group]),[data-static],[data-recaptcha-type],[data-hcaptcha-type]').each(function() {

			ws_this.form_bypass_process($(this), '-section', false);
		});

		$('[id^="' + this.form_id_prefix + 'section-"][style="display:none;"], [id^="' + this.form_id_prefix + 'section-"][style="display: none;"]').find('[name]:not([type="hidden"]),[data-static],[data-recaptcha-type],[data-hcaptcha-type]').each(function() {

			ws_this.form_bypass_process($(this), '-section', true);
		});

		// Process custom validity messages - Fields
		$('[id^="' + this.form_id_prefix + 'field-wrapper-"][style!="display:none;"][style!="display: none;"]', this.form_canvas_obj).find('[name]:not([type="hidden"],[data-hidden-section],[data-hidden-group]),[data-static],[data-recaptcha-type],[data-hcaptcha-type]').each(function() {

			ws_this.form_bypass_process($(this), '', false);
		});

		$('[id^="' + this.form_id_prefix + 'field-wrapper-"][style="display:none;"], [id^="' + this.form_id_prefix + 'field-wrapper-"][style="display: none;"]', this.form_canvas_obj).find('[name]:not([type="hidden"]),[data-static],[data-recaptcha-type],[data-hcaptcha-type]').each(function() {

			ws_this.form_bypass_process($(this), '', true);
		});

	}

	// Form bypass - Hidden
	$.WS_Form.prototype.form_bypass_hidden = function(obj, attribute_source, attribute_replace) {

		var attribute_source_value = obj.attr(attribute_source);

		if(attribute_replace) {

			obj.attr(attribute_source, attribute_replace);

		} else {

			obj.removeAttr(attribute_source);
		}

		return attribute_source_value;
	}

	// Form bypass - Visible
	$.WS_Form.prototype.form_bypass_visible = function(obj, attribute_bypass) {

		return obj.attr(attribute_bypass);
	}

	// Form bypass process
	$.WS_Form.prototype.form_bypass_process = function(obj, attr_suffix, set) {

		if(!obj[0].willValidate) { return; }

		var section_id = this.get_section_id(obj);
		var section_repeatable_index = this.get_section_repeatable_index(obj);
		var field_id = this.get_field_id(obj);
	
		if(set) {

			var validation_message = obj[0].validationMessage;

			if(validation_message !== '') {

				if(typeof(this.validation_message_cache[section_id]) === 'undefined') { this.validation_message_cache[section_id] = []; }
				if(typeof(this.validation_message_cache[section_id][section_repeatable_index]) === 'undefined') { this.validation_message_cache[section_id][section_repeatable_index] = []; }
				if(typeof(this.validation_message_cache[section_id][section_repeatable_index][0]) === 'undefined') { this.validation_message_cache[section_id][section_repeatable_index][0] = []; }

				this.validation_message_cache[section_id][section_repeatable_index][field_id][0] = validation_message;

				// Set custom validation message to blank
				obj[0].setCustomValidity('');
			}

			// Add data-hidden attribute
			if(typeof(obj.attr('data-hidden-bypass')) === 'undefined') {

				obj.attr('data-hidden' + attr_suffix, '');
			}

		} else {

			if(
				(typeof(this.validation_message_cache[section_id]) !== 'undefined') &&
				(typeof(this.validation_message_cache[section_id][section_repeatable_index]) !== 'undefined') &&
				(typeof(this.validation_message_cache[section_id][section_repeatable_index][field_id]) !== 'undefined') &&
				(typeof(this.validation_message_cache[section_id][section_repeatable_index][field_id][0]) !== 'undefined')
			) {

				// Recall custom validation message
				obj[0].setCustomValidity(this.validation_message_cache[section_id][section_repeatable_index][field_id][0]);
			}

			// Remove data-hidden attribute
			if(typeof(obj.attr('data-hidden-bypass')) === 'undefined') {

				obj.removeAttr('data-hidden' + attr_suffix);
			}
		}
	}

	// Select all
	$.WS_Form.prototype.form_select_all = function() {

		var ws_this = this;

		$('[data-wsf-select-all]:not([data-init-select-all])', this.form_canvas_obj).each(function() {

			// Flag so it only initializes once
			$(this).attr('data-init-select-all', '');

			// Get select all name
			var select_all_name = $(this).attr('name');
			$(this).removeAttr('name').removeAttr('value').attr('data-wsf-select-all', select_all_name);

			// Click event
			$(this).on('click', function() {

				var select_all = $(this).is(':checked');
				var select_all_name = $(this).attr('data-wsf-select-all');

				// Get field wraper
				var field_wrapper_obj = $(this).closest('[data-id]');

				// Is select all within a field set
				var fieldset_obj = $(this).closest('fieldset', field_wrapper_obj);

				// Determine context
				var context = fieldset_obj.length ? fieldset_obj : ws_this.form_canvas_obj;

				// We use 'each' here to ensure they are checked in ascending order
				$('[name="' + select_all_name + '"]:enabled', context).each(function() {

					$(this).prop('checked', select_all).trigger('change');
				});
			})
		});
	}

	// Form - Input Mask
	$.WS_Form.prototype.form_inputmask = function() {

		$('[data-inputmask]', this.form_canvas_obj).each(function () {

			if(typeof($(this).inputmask) !== 'undefined') {

				$(this).inputmask().off('invalid');
			}
		});
	}

	// Form - Checkbox Min / Max
	$.WS_Form.prototype.form_checkbox_min_max = function() {

		var ws_this = this;

		$('[data-checkbox-min]:not([data-checkbox-min-max-init]),[data-checkbox-max]:not([data-checkbox-min-max-init])', this.form_canvas_obj).each(function () {

			var checkbox_min = $(this).attr('data-checkbox-min');
			var checkbox_max = $(this).attr('data-checkbox-max');

			// If neither attribute present, disregard this feature
			if(
				(typeof(checkbox_min) === 'undefined') &&
				(typeof(checkbox_max) === 'undefined')
			) {

				return;
			}

			// Get field ID
			var field_id = $(this).attr('data-id');

			// Get repeatable suffix
			var section_repeatable_suffix = ws_this.get_section_repeatable_suffix($(this));

			// Build number input
			var checkbox_min_max = $('<input type="number" id="' + ws_this.form_id_prefix + 'checkbox-min-max-' + field_id + section_repeatable_suffix + '" data-checkbox-min-max data-progress-include="change" style="display:none !important;" />', ws_this.form_canvas_obj);

			// Add min attribute
			if(typeof(checkbox_min) !== 'undefined') { checkbox_min_max.attr('min', checkbox_min); }

			// Add max attribute
			if(typeof(checkbox_max) !== 'undefined') { checkbox_min_max.attr('max', checkbox_max); }
			checkbox_max = parseInt(checkbox_max, 10);

			// Add value attribute
			var checked_count = $('input[type="checkbox"]:not([data-wsf-select-all]):checked', $(this)).length;
			checkbox_min_max.attr('value', checked_count);

			// Add before invalid feedback
			var invalid_feedback_obj = ws_this.get_invalid_feedback_obj($(this));
			invalid_feedback_obj.before(checkbox_min_max);

			// Add event on all checkboxes
			$('input[type="checkbox"]:not([data-wsf-select-all])', $(this)).on('change', function(e) {

				// Get field wrapper
				var field_wrapper = ws_this.get_field_wrapper($(this));

				// Get field ID
				var field_id = ws_this.get_field_id($(this));

				// Get repeatable suffix
				var section_repeatable_suffix = ws_this.get_section_repeatable_suffix($(this));

				// Custom invalid feedback text
				var checkbox_min_max_obj = $('#' + ws_this.form_id_prefix + 'checkbox-min-max-' + field_id + section_repeatable_suffix, ws_this.form_canvas_obj);

				// Set value
				var checked_count = $('input[type="checkbox"]:not([data-wsf-select-all]):checked', field_wrapper).length;

				// Max check
				var obj_wrapper = $(this).closest('[data-type]');
				var input_number = $('input[type="number"]', obj_wrapper);
				var checkbox_max = ws_this.get_number(input_number.attr('max'), 0, false);
				if(
					(checkbox_max > 0) &&
					(checked_count > checkbox_max)
				) {

					$(this).prop('checked', false);
					checked_count--;
				}

				checkbox_min_max_obj.val(checked_count).trigger('change');
			});

			// Flag so it only initializes once
			$(this).attr('data-checkbox-min-max-init', '');
		});
	}

	// Form - Select Min / Max
	$.WS_Form.prototype.form_select_min_max = function() {

		var ws_this = this;

		$('[data-select-min]:not([data-select-min-max-init]),[data-select-max]:not([data-select-min-max-init])', this.form_canvas_obj).each(function () {

			var select_min = $(this).attr('data-select-min');
			var select_max = $(this).attr('data-select-max');

			// If neither attribute present, disregard this feature
			if(
				(typeof(select_min) === 'undefined') &&
				(typeof(select_max) === 'undefined')
			) {

				return;
			}

			// Get field ID
			var field_id = $(this).attr('data-id');

			// Get repeatable suffix
			var section_repeatable_suffix = ws_this.get_section_repeatable_suffix($(this));

			// Build number input
			var select_min_max = $('<input type="number" id="' + ws_this.form_id_prefix + 'select-min-max-' + field_id + section_repeatable_suffix + '" data-select-min-max data-progress-include="change" style="display:none !important;" />', ws_this.form_canvas_obj);

			// Add min attribute
			if(typeof(select_min) !== 'undefined') { select_min_max.attr('min', select_min); }

			// Add max attribute
			if(typeof(select_max) !== 'undefined') { select_min_max.attr('max', select_max); }
			select_max = parseInt(select_max, 10);

			// Add value attribute
			var selected_count = $('select option:selected', $(this)).length;
			select_min_max.attr('value', selected_count);

			// Add before invalid feedback
			var invalid_feedback_obj = ws_this.get_invalid_feedback_obj($(this));
			invalid_feedback_obj.before(select_min_max);

			// Add event on all selects
			$('select', $(this)).on('change', function() {

				var field_wrapper = ws_this.get_field_wrapper($(this));

				// Get field ID
				var field_id = ws_this.get_field_id($(this));

				// Get repeatable suffix
				var section_repeatable_suffix = ws_this.get_section_repeatable_suffix($(this));

				// Custom invalid feedback text
				var select_min_max_obj = $('#' + ws_this.form_id_prefix + 'select-min-max-' + field_id + section_repeatable_suffix, ws_this.form_canvas_obj);

				// Get count
				var selected_count = $('select option:selected', field_wrapper).length;

				// Max check
				if(
					(select_max > 0) &&
					(selected_count > select_max)
				) {

					$(this).prop('selected', false);
					selected_count--;
				}

				// Set value
				select_min_max_obj.val(selected_count).trigger('change');
			});

			// Flag so it only initializes once
			$(this).attr('data-select-min-max-init', '');
		});
	}

	// Form - Client side validation
	$.WS_Form.prototype.form_validation = function() {

		// WS Form forms are set with novalidate attribute so we can manage that ourselves
		var ws_this = this;

		// Disable submit on enter
		if(!this.get_object_meta_value(this.form, 'submit_on_enter', false)) {

			this.form_obj.on('keydown', ':input:not(textarea)', function(e) {

				if(e.keyCode == 13) {

					e.preventDefault();
					return false;
				}
			});
		}

		// On submit
		this.form_obj.on('submit', function(e) {

			e.preventDefault();
			e.stopPropagation();

			// Post if form validates
			ws_this.form_post_if_validated('submit');
		});
	}

	// Form - Post if validated
	$.WS_Form.prototype.form_post_if_validated = function(post_mode) {

		// Trigger
		this.trigger(post_mode + '-before');

		// If form post is locked, return
		if(this.form_post_locked) { return; }

		// Recalculate e-commerce
		if(this.has_ecommerce) { this.form_ecommerce_calculate(); }

		// Mark as validated
		this.form_obj.addClass(this.class_validated);

		// Check validity of form
		if(this.form_validate(this.form_obj)) {

			// Trigger
			this.trigger(post_mode + '-validate-success');

				// Submit form
				this.form_post(post_mode);
		} else {

			// Trigger
			this.trigger(post_mode + '-validate-fail');
		}
	}

	// Form - Validate (WS Form validation functions)
	$.WS_Form.prototype.form_validate = function(form) {

		if(typeof(form) === 'undefined') { form = this.form_obj; }

		// Trigger rendered event
		this.trigger('validate-before');

		// Tab focussing
		var group_index_focus_enabled = (this.form.groups.length > 0);
		var group_index_focus = false;
		var object_focus = false;

		// Get form as element
		var form_el = form[0];

		// Execute browser validation
		var form_validated = form_el.checkValidity();

		if(!form_validated) {

			// Get all invalid fields
			var fields_invalid = $(':invalid', form).not('fieldset');

			if(fields_invalid) {

				// Get first invalid field
				object_focus = fields_invalid.first();

				// Get group index
				group_index_focus = this.get_group_index(object_focus);
			}
		}

		// Focus
		if(!form_validated) {

			if(object_focus !== false) {

				// Focus object
				if(this.get_object_meta_value(this.form, 'invalid_field_focus', true)) {

					if(group_index_focus !== false) { 

						this.object_focus = object_focus;

					} else {

						object_focus.trigger('focus');
					}
				}
			}

			// Focus tab
			if(group_index_focus !== false) { this.group_index_set(group_index_focus); }
		}

		// Trigger rendered event
		this.trigger('validate-after');

		return form_validated;
	}

	// Form - Validate - Real time
	$.WS_Form.prototype.form_validate_real_time = function(form) {

		var ws_this = this;

		// Set up form validation events
		for(var field_index in this.field_data_cache) {

			if(!this.field_data_cache.hasOwnProperty(field_index)) { continue; }

			var field_type = this.field_data_cache[field_index].type;
			var field_type_config = $.WS_Form.field_type_cache[field_type];

			// Get events
			if(typeof(field_type_config.events) === 'undefined') { continue; }
			var form_validate_event = field_type_config.events.event;

			// Get field ID
			var field_id = this.field_data_cache[field_index].id;

			// Check to see if this field is submitted as an array
			var submit_array = (typeof(field_type_config.submit_array) !== 'undefined') ? field_type_config.submit_array : false;

			// Check to see if field is in a repeatable section
			var field_wrapper = $('[data-type][data-id="' + field_id + '"],input[type="hidden"][data-id-hidden="' + field_id + '"]', this.form_canvas_obj);

			// Run through each wrapper found (there might be repeatables)
			field_wrapper.each(function() {

				var section_repeatable_index = $(this).attr('data-repeatable-index');
				var section_repeatable_suffix = (section_repeatable_index > 0) ? '[' + section_repeatable_index + ']' : '';

				if(submit_array) {

					var field_obj = $('[name="' + ws_form_settings.field_prefix + field_id + section_repeatable_suffix + '[]"]:not([data-init-validate-real-time]), [name="' + ws_form_settings.field_prefix + field_id + section_repeatable_suffix + '[]"]:not([data-init-validate-real-time])', ws_this.form_canvas_obj);

				} else {

					var field_obj = $('[name="' + ws_form_settings.field_prefix + field_id + section_repeatable_suffix + '"]:not([data-init-validate-real-time]), [name="' + ws_form_settings.field_prefix + field_id + section_repeatable_suffix + '"]:not([data-init-validate-real-time])', ws_this.form_canvas_obj);
				}

				if(field_obj.length) {

					// Flag so it only initializes once
					field_obj.attr('data-init-validate-real-time', '');

					// Check if field should be bypassed
					var event_validate_bypass = (typeof(field_type_config.event_validate_bypass) !== 'undefined') ? field_type_config.event_validate_bypass : false;

					// Create event (Also run on blur, this prevents the mask component from causing false validation results)
					field_obj.on(form_validate_event + ' blur', function(e) {

						// Form validation
						if(!event_validate_bypass) {

							// Run validate real time processing
							ws_this.form_validate_real_time_process(false);
						}

					});
				}
			});
		}

		// Initial validation fire
		this.form_validate_real_time_process(false);
	}

	$.WS_Form.prototype.form_validate_real_time_process = function(conditional_initiated) {

		// Validate
		this.form_valid = this.form_validate_silent(this.form_obj);

		// Run conditional logic
		if(!conditional_initiated) { this.form_canvas_obj.trigger('wsf-validate-silent'); }

		// Check for form validation changes
		if(
			(this.form_valid_old === null) ||
			(this.form_valid_old != this.form_valid)
		) {

			// Run conditional logic
			if(!conditional_initiated) { this.form_canvas_obj.trigger('wsf-validate'); }
		}

		this.form_valid_old = this.form_valid;

		// Execute hooks and pass form_valid to them
		for(var hook_index in this.form_validation_real_time_hooks) {

			if(!this.form_validation_real_time_hooks.hasOwnProperty(hook_index)) { continue; }

			var hook = this.form_validation_real_time_hooks[hook_index];

			if(typeof(hook) === 'undefined') {

				delete(this.form_validation_real_time_hooks[hook_index]);

			} else {

				hook(this.form_valid, this.form, this.form_id, this.form_instance_id, this.form_obj, this.form_canvas_obj);
			}
		}

		return this.form_valid;
	}

	$.WS_Form.prototype.form_validate_real_time_register_hook = function(hook) {

		this.form_validation_real_time_hooks.push(hook);
	}

	// Form - Validate - Silent
	$.WS_Form.prototype.form_validate_silent = function(form) {

		// Get form as element
		var form_el = form[0];

		// aria-invalid="true"
		$(':valid[aria-invalid="true"]:not(fieldset)', form).removeAttr('aria-invalid');
		$(':invalid:not([aria-invalid="true"]):not(fieldset)', form).attr('aria-invalid', 'true');

		// Execute browser validation
		var form_validated = form_el.checkValidity();
		if(!form_validated) { return false; }


		return true;
	}

	// Validate any form object
	$.WS_Form.prototype.object_validate = function(obj) {

		var radio_field_processed = [];		// This ensures correct progress numbers of radios

		if(typeof(obj) === 'undefined') { return false; }

		var ws_this = this;

		var valid = true;

		// Get fields
		$('input,select,textarea', obj).filter(':not([data-hidden],[data-hidden-section],[data-hidden-group],[disabled],[type="hidden"])').each(function() {

			// Get field
			var field = ws_this.get_field($(this));
			var field_type = field.type;

			// Get repeatable suffix
			var section_repeatable_index = ws_this.get_section_repeatable_index($(this));
			var section_repeatable_suffix = (section_repeatable_index > 0) ? '[' + section_repeatable_index + ']' : '';

			// Build field name
			var field_name = ws_form_settings.field_prefix + ws_this.get_field_id($(this)) + section_repeatable_suffix;

			// Determine field validity based on field type
			var validity = false;
			switch(field_type) {

				case 'radio' :
				case 'price_radio' :

					if(typeof(radio_field_processed[field_name]) === 'undefined') { 

						validity = $(this)[0].checkValidity();

					} else {

						return;
					}
					break;

				case 'email' :

					if(typeof($(this).attr('required')) !== 'undefined') {

						var email_regex = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
						validity = email_regex.test($(this).val());

					} else {

						validity = true;
					}

					break;

				default :

					validity = $(this)[0].checkValidity();
			}

			radio_field_processed[field_name] = true;

			if(!validity) { valid = false; return false; }
		});

		return valid;
	}

	// Convert hex color to RGB values
	$.WS_Form.prototype.hex_to_hsl = function(color) {

		// Get RGB of hex color
		var rgb = this.hex_to_rgb(color);
		if(rgb === false) { return false; }

		// Get HSL of RGB
		var hsl = this.rgb_to_hsl(rgb);

		return hsl;
	}

	// Convert hex color to RGB values
	$.WS_Form.prototype.hex_to_rgb = function(color) {

		// If empty, return false
		if(color == '') { return false; }

		// Does color have a hash?
		var color_has_hash = (color[0] == '#');

		// Check
		if(color_has_hash && (color.length != 7)) { return false; }
		if(!color_has_hash && (color.length != 6)) { return false; }

		// Strip hash
		var color = color_has_hash ? color.substr(1) : color;

		// Get RGB values
		var r = parseInt(color.substr(0,2), 16);
		var g = parseInt(color.substr(2,2), 16);
		var b = parseInt(color.substr(4,2), 16);

		return {'r': r, 'g': g, 'b': b};
	}

	// Convert RGB to HSL
	$.WS_Form.prototype.rgb_to_hsl = function(rgb) {

		if(typeof(rgb.r) === 'undefined') { return false; }
		if(typeof(rgb.g) === 'undefined') { return false; }
		if(typeof(rgb.b) === 'undefined') { return false; }

		var r = rgb.r;
		var g = rgb.g;
		var b = rgb.b;

		r /= 255, g /= 255, b /= 255;

		var max = Math.max(r, g, b), min = Math.min(r, g, b);
		var h, s, l = (max + min) / 2;

		if(max == min){
	
			h = s = 0;
	
		} else {
	
			var d = max - min;
			s = l > 0.5 ? d / (2 - max - min) : d / (max + min);

			switch(max){
				case r: h = (g - b) / d + (g < b ? 6 : 0); break;
				case g: h = (b - r) / d + 2; break;
				case b: h = (r - g) / d + 4; break;
			}

			h /= 6;
		}

		return {'h': h, 's': s, 'l': l};
	}

	// Set object attribute (if false, remove the attribute)
	$.WS_Form.prototype.obj_set_attribute = function(obj, attribute, value) {

		if(typeof(obj.attr('data-' + attribute + '-bypass')) !== 'undefined') {

			if(value !== false) {

				obj.attr('data-' + attribute + '-bypass', value).trigger('change');

			} else {

				obj.removeAttr('data-' + attribute + '-bypass').trigger('change');
			}

		} else {

			if(value !== false) {

				obj.attr(attribute, value).trigger('change');

			} else {

				obj.removeAttr(attribute).trigger('change');
			}
		}
	}

	$.WS_Form.prototype.group_fields_reset = function(group_id, field_clear) {

		if(typeof(this.group_data_cache[group_id]) === 'undefined') { return false; }

		// Get group
		var group = this.group_data_cache[group_id];
		if(typeof(group.sections) === 'undefined') { return false; }

		// Get all fields in group
		var sections = group.sections;

		for(var section_index in sections) {

			if(!sections.hasOwnProperty(section_index)) { continue; }

			var section = sections[section_index];

			this.section_fields_reset(section.id, field_clear, false);
		}
	}

	$.WS_Form.prototype.section_fields_reset = function(section_id, field_clear, section_repeatable_index) {

		if(typeof(this.section_data_cache[section_id]) === 'undefined') { return false; }

		// Get section
		var section = this.section_data_cache[section_id];
		if(typeof(section.fields) === 'undefined') { return false; }

		// Get all fields in section
		var fields = section.fields;

		for(var field_index in fields) {

			if(!fields.hasOwnProperty(field_index)) { continue; }

			var field = fields[field_index];
			var field_id = field.id;

			if(section_repeatable_index === false) {

				var object_selector_wrapper = '[id^="' + this.form_id_prefix + 'field-wrapper-' + field_id + '"][data-id="' + field.id + '"]';

			} else {

				var object_selector_wrapper = '#' + this.form_id_prefix + 'field-wrapper-' + field_id + '-repeat-' + section_repeatable_index;
			}

			var obj_wrapper = $(object_selector_wrapper, this.form_canvas_obj);

			this.field_reset(field_id, field_clear, obj_wrapper);
		}
	}

	$.WS_Form.prototype.field_reset = function(field_id, field_clear, obj_wrapper) {

		var ws_this = this;

		if(typeof(obj_wrapper) === 'undefined') { obj_wrapper = false; }

		if(typeof(this.field_data_cache[field_id]) === 'undefined') { return; }

		var field = this.field_data_cache[field_id];

		var field_type_config = $.WS_Form.field_type_cache[field.type];
		var trigger_action = (typeof(field_type_config.trigger) !== 'undefined') ? field_type_config.trigger : 'change';

		switch(field.type) {

			case 'select' :
			case 'price_select' :

				$('option', obj_wrapper).each(function() {

					var selected_new = field_clear ? false : $(this).prop('defaultSelected');
					var trigger = $(this).prop('selected') !== selected_new;
					$(this).prop('selected', selected_new);
					if(trigger) { $(this).trigger(trigger_action); }
				});
				break;

			case 'checkbox' :
			case 'price_checkbox' :

				$('input[type="checkbox"]', obj_wrapper).each(function() {

					var checked_new = field_clear ? false : $(this).prop('defaultChecked');
					var trigger = $(this).prop('checked') !== checked_new;
					$(this).prop('checked', checked_new);
					if(trigger) { $(this).trigger(trigger_action); }
				});
				break;

			case 'radio' :
			case 'price_radio' :

				$('input[type="radio"]', obj_wrapper).each(function() {

					var checked_new = field_clear ? false : $(this).prop('defaultChecked');
					var trigger = $(this).prop('checked') !== checked_new;
					$(this).prop('checked', checked_new);
					if(trigger) { $(this).trigger(trigger_action); }
				});
				break;

			case 'textarea' :

				$('textarea', obj_wrapper).each(function() {

					var val_new = field_clear ? '' : $(this).prop('defaultValue');
					var trigger = $(this).val() !== val_new;
					$(this).val(val_new);
					ws_this.textarea_set_value($(this), val_new);
					if(trigger) { $(this).trigger('change'); }
				});
				break;

			case 'color' :

				$('input', obj_wrapper).each(function() {

					var val_new = field_clear ? '' : $(this).prop('defaultValue');
					var trigger = $(this).val() !== val_new;
					$(this).val(val_new);
					if($(this).hasClass('minicolors-input')) {
						$(this).minicolors('value', {color: val_new});
					}
					if(trigger) { $(this).trigger('change'); }
				});
				break;

			case 'hidden' :

				// Hidden fields don't have a wrapper so the obj_wrapper is the field. You cannot use the defaultValue property on hidden fields as it gets update when val() is used, so we use data-default-value attribute instead.
				var val_new = field_clear ? '' : obj_wrapper.attr('data-default-value');
				var trigger = obj_wrapper.val() !== val_new;
				obj_wrapper.val(val_new);
				if(trigger) { obj_wrapper.trigger(trigger_action); }
				break;

			case 'googlemap' :

				$('input', obj_wrapper).each(function() {

					var val_new = field_clear ? '' : $(this).attr('data-default-value');
					var trigger = $(this).val() !== val_new;
					$(this).val(val_new);
					if(trigger) { $(this).trigger(trigger_action); }
				});
				break;

			default :

				$('input', obj_wrapper).each(function() {

					var val_new = field_clear ? '' : $(this).prop('defaultValue');
					var trigger = $(this).val() !== val_new;
					$(this).val(val_new);
					if(trigger) { $(this).trigger(trigger_action); }
				});
		}
	}

	$.WS_Form.prototype.conditional_logic_previous = function(accumulator, value, logic_previous) {

		switch(logic_previous) {

			// OR
			case '||' :

				accumulator |= value;
				break;

			// AND
			case '&&' :

				accumulator &= value;
				break;
		}

		return accumulator;
	}

	// Check integrity of a condition
	$.WS_Form.prototype.conditional_condition_check = function(condition) {

		return !(

			(condition === null) ||
			(typeof(condition) !== 'object') ||
			(typeof(condition.id) === 'undefined') ||
			(typeof(condition.object) === 'undefined') ||
			(typeof(condition.object_id) === 'undefined') ||
			(typeof(condition.object_row_id) === 'undefined') ||
			(typeof(condition.logic) === 'undefined') ||
			(typeof(condition.value) === 'undefined') ||
			(typeof(condition.case_sensitive) === 'undefined') ||
			(typeof(condition.logic_previous) === 'undefined') ||
			(condition.id == '') ||
			(condition.id == 0) ||
			(condition.object == '') ||
			(condition.object_id == '') ||
			(condition.logic == '')
		);
	}

	// Check integrity of an action
	$.WS_Form.prototype.conditional_action_check = function(action) {

		return !(

			(action === null) ||
			(typeof(action) !== 'object') ||
			(typeof(action.object) === 'undefined') ||
			(typeof(action.object_id) === 'undefined') ||
			(typeof(action.action) === 'undefined') ||
			(action.object == '') ||
			(action.object_id == '') ||
			(action.action == '')
		);
	}

	// Group - Tabs - Init
	$.WS_Form.prototype.form_tabs = function() {

		if(this.form.groups.length <= 1) { return false; }

		var ws_this = this;

		// Get selector href
		var selector_href = (typeof(this.framework.tabs.public.selector_href) !== 'undefined') ? this.framework.tabs.public.selector_href : 'href';

		// Get tab index cookie if settings require it
		var index = parseInt((this.get_object_meta_value(this.form, 'cookie_tab_index')) ? this.cookie_get('tab_index', 0) : 0);

		// Check index is valid
		var tabs_obj = $('.wsf-group-tabs', this.form_canvas_obj);
		var li_obj = tabs_obj.children();
		if(
			(typeof(li_obj[index]) === 'undefined') ||
			(typeof($(li_obj[index]).attr('data-wsf-group-hidden')) !== 'undefined')
		) {

			index = 0;

			var li_obj_visible = $(':not([data-wsf-group-hidden])', li_obj);

			if(li_obj_visible.length) {

				index = li_obj_visible.first().index();
			}

			// Save current tab index to cookie
			if(ws_this.get_object_meta_value(ws_this.form, 'cookie_tab_index')) {

				ws_this.cookie_set('tab_index', index);
			}
		}

		// If we are using the WS Form framework, then we need to run our own tabs script
		if($.WS_Form.settings_plugin.framework === 'ws-form') {

			// Destroy tabs (Ensures subsequent calls work)
			if(tabs_obj.hasClass('wsf-tabs')) { this.tabs_destroy(); }

			// Init tabs
			this.tabs(tabs_obj, { active: index });

		} else {

			// Set active tab
			this.group_index_set(index);
		}

		var framework_tabs = this.framework['tabs']['public'];

		if(typeof(framework_tabs.event_js) !== 'undefined') {

			var event_js = framework_tabs.event_js;
			var event_type_js = (typeof(framework_tabs.event_type_js) !== 'undefined') ? framework_tabs.event_type_js : false;
			var event_selector_wrapper_js = (typeof(framework_tabs.event_selector_wrapper_js) !== 'undefined') ? framework_tabs.event_selector_wrapper_js : false;
			var event_selector_active_js = (typeof(framework_tabs.event_selector_active_js) !== 'undefined') ? framework_tabs.event_selector_active_js : false;

			switch(event_type_js) {

				case 'wrapper' :

					var event_selector = $(event_selector_wrapper_js, this.form_canvas_obj);
					break;

				default :

					var event_selector = $('[' + selector_href + '^="#' + this.form_id_prefix + 'group-"]', this.form_canvas_obj);
			}

			// Set up on click event for each tab
			event_selector.on(event_js, function (event, ui) {

				switch(event_type_js) {

					case 'wrapper' :

						var event_active_selector = $(event_selector_active_js, event_selector);
						var tab_index = event_active_selector.index();
						break;

					default :

						var tab_index = $(this).parent().index();
				}

				// Save current tab index to cookie
				if(ws_this.get_object_meta_value(ws_this.form, 'cookie_tab_index')) {

					ws_this.cookie_set('tab_index', tab_index);
				}

				// Object focus
				if(ws_this.object_focus !== false) {

					ws_this.object_focus.trigger('focus');
					ws_this.object_focus = false;
				}
			});
		}
	}

	// Tab validation
	$.WS_Form.prototype.form_tab_validation = function() {

		var ws_this = this;

		var tab_validation = this.get_object_meta_value(this.form, 'tab_validation');
		if(tab_validation) {

			this.form_canvas_obj.on('wsf-validate-silent', function() {

				ws_this.form_tab_validation_process();
			});

			this.form_tab_validation_process();
		}
	}

	// Tab validation
	$.WS_Form.prototype.form_tab_validation_process = function() {

		var tab_validation = this.get_object_meta_value(this.form, 'tab_validation');
		if(!tab_validation) { return; }

		var ws_this = this;

		var tab_validated_previous = true;

		// Get selector href
		var selector_href = (typeof(this.framework.tabs.public.selector_href) !== 'undefined') ? this.framework.tabs.public.selector_href : 'href';

		// Get tabs
		var tabs = $('.wsf-group-tabs > :not([data-wsf-group-hidden]) > [' + selector_href + ']', this.form_canvas_obj);

		// Get tab count
		var tab_count = tabs.length;

		// Get tab_index_current
		var tab_index_current = 0;
		tabs.each(function(tab_index) {

			var tab_visible = $($(this).attr(selector_href)).is(':visible');
			if(tab_visible) {

				tab_index_current = tab_index;
				return false;
			}
		});

		tabs.each(function(tab_index) {

			// Render validation for previous tab
			ws_this.tab_validation_previous($(this), tab_validated_previous);

			// Validate tab
			if(tab_index < (tab_count - 1)) {

				if(tab_validated_previous === true) {

					var tab_validated_current = ws_this.object_validate($($(this).attr(selector_href)));

				} else {

					var tab_validated_current = false;
				}

				// Render validation for current tab
				ws_this.tab_validation_current($(this), tab_validated_current);

				tab_validated_previous = tab_validated_current;
			}

			// If we are on a tab that is beyond the current invalidated tab, change tab to first invalidated tab
			if( !tab_validated_current &&
				(tab_index_current > tab_index)
			) {

				// Activate tab
				ws_this.group_index_set(tab_index);
			}
		});

		// Form navigation
		this.form_navigation();
	}

	// Tab validation - Current
	$.WS_Form.prototype.tab_validation_current = function(obj, tab_validated) {

		// Get selector href
		var selector_href = (typeof(this.framework.tabs.public.selector_href) !== 'undefined') ? this.framework.tabs.public.selector_href : 'href';

		var tab_id = obj.attr(selector_href);
		var tab_content_obj = $(tab_id, this.form_canvas_obj);
		var button_next_obj = $('button[data-action="wsf-tab_next"]', tab_content_obj);

		if(tab_validated) {

			button_next_obj.removeAttr('disabled');

		} else {

			button_next_obj.attr('disabled', '');
		}
	}

	// Tab validation - Previous
	$.WS_Form.prototype.tab_validation_previous = function(obj, tab_validated) {

		var framework_tabs = this.framework.tabs.public;

		if(typeof(framework_tabs.class_disabled) !== 'undefined') {

			if(tab_validated) {

				obj.removeClass(framework_tabs.class_disabled).removeAttr('data-wsf-tab-disabled').removeAttr('tabindex');

			} else {

				obj.addClass(framework_tabs.class_disabled).attr('data-wsf-tab-disabled', '').attr('tabindex', '-1');
			}
		}

		if(typeof(framework_tabs.class_parent_disabled) !== 'undefined') {

			if(tab_validated) {

				obj.parent().removeClass(framework_tabs.class_parent_disabled);

			} else {

				obj.parent().addClass(framework_tabs.class_parent_disabled);
			}
		}
	}

	// Form - Post
	$.WS_Form.prototype.form_post = function(post_mode, action_id) {

		if(typeof(post_mode) == 'undefined') { post_mode = 'save'; }
		if(typeof(action_id) == 'undefined') { action_id = 0; }

		// Determine if this is a submit
		var submit = (post_mode == 'submit');

		// Trigger post mode event
		this.trigger(post_mode);

		var ws_this = this;

		// Lock form
		this.form_post_lock();

		// Build form data
		this.form_add_hidden_input('wsf_form_id', this.form_id);
		this.form_add_hidden_input('wsf_hash', this.hash);
		this.form_add_hidden_input(ws_form_settings.wsf_nonce_field_name, ws_form_settings.wsf_nonce);

		// Tracking - Duration (If disabled, cookie and hidden field are not set to respect privacy settings)
		var duration_tracking = (this.get_object_meta_value(this.form, 'tracking_duration', '') == 'on');
		if(duration_tracking) {

			this.form_add_hidden_input('wsf_duration', Math.round((new Date().getTime() - this.date_start) / 1000));
		}


		// Reset date start
		if(post_mode == 'submit') {

			this.date_start = false;

			if(duration_tracking) {

				this.cookie_set('date_start', false, false);
			}

			this.form_timer();
		}

		if((typeof(ws_form_settings.post_id) !== 'undefined') && (ws_form_settings.post_id > 0)) {

			this.form_add_hidden_input('wsf_post_id', ws_form_settings.post_id);
		}

		// Post mode
		this.form_add_hidden_input('wsf_post_mode', post_mode);

		// Work out which fields are hidden
		var hidden_array = $('[data-hidden],[data-hidden-section],[data-hidden-group]', ws_this.form_canvas_obj).map(function() {

			// Get name
			var name = $(this).attr('name');
			if(typeof(name) === 'undefined') {

				var name = $(this).attr('data-name');
				if(typeof(name) === 'undefined') {

					return '';
				}
			}

			// Strip brackets (For select, radio and checkboxes)
			name = name.replace('[]', '');

			return name;

		}).get();
		hidden_array = hidden_array.filter(function(value, index, self) { 

			return self.indexOf(value) === index;
		});
		var hidden = hidden_array.join();
		this.form_add_hidden_input('wsf_hidden', hidden);

		// Work out which required fields to bypass (because they are hidden) or no longer required because of conditional logic
		var bypass_required_array = $('[data-required-bypass],[data-required-bypass-section],[data-required-bypass-group],[data-conditional-logic-bypass]', this.form_canvas_obj).map(function() {

			// Get name
			var name = $(this).attr('name');

			// Strip brackets (For select, radio and checkboxes)
			name = name.replace('[]', '');

			return name;

		}).get();
		bypass_required_array = bypass_required_array.filter(function(value, index, self) { 

			return self.indexOf(value) === index;
		});
		var bypass_required = bypass_required_array.join();
		this.form_add_hidden_input('wsf_bypass_required', bypass_required);


		// Do not run AJAX
		if(
			(action_id == 0) &&
			(this.form_ajax === false)
		) {

			// We're done!
			ws_this.trigger(post_mode + '-complete');
			ws_this.trigger('complete');
			return;
		}

		// Trigger
		ws_this.trigger(post_mode + '-before-ajax');

		// Build form data
		var form_data = new FormData(this.form_obj[0]);

		// Action ID (Inject into form_data so that it doesn't stay on the form)
		if(action_id > 0) {

			form_data.append('wsf_action_id', action_id);
		}

		// Process international telephone inputs
		$('[data-intl-tel-input]').each(function() {

			// Get iti instance
			var iti = window.intlTelInputGlobals.getInstance($(this)[0]);

			// Get field ID
			var field_id = ws_this.get_field_id($(this));

			// Get field
			var field = ws_this.field_data_cache[field_id];

			// Get return format
			var return_format = ws_this.get_object_meta_value(field, 'intl_tel_input_format', '');

			// Get number
			switch(return_format) {

				case 'INTERNATIONAL' :
				case 'NATIONAL' :
				case 'E164' :
				case 'RFC3966' :

					var field_value = iti.getNumber(intlTelInputUtils.numberFormat[return_format]);
					break;

				default :

					return;
			}

			// Get field name
			var field_name = $(this).attr('name');

			// Override form data
			form_data.set(field_name, field_value);
		});

		// Call API
		this.api_call('submit', 'POST', form_data, function(response) {

			// Success

			// Check for validation errors
			var error_validation = (typeof(response.error_validation) !== 'undefined') && response.error_validation;

			// Check for errors
			var errors = (

				(typeof(response.data) !== 'undefined') &&
				(typeof(response.data.errors) !== 'undefined') &&
				response.data.errors.length
			);

			// If response is invalid or form is being saved, force unlock it
			var form_post_unlock_force = (

				(typeof(response.data) === 'undefined') ||
				(post_mode == 'save') ||
				error_validation ||
				errors
			);

			// Unlock form
			ws_this.form_post_unlock('progress', !form_post_unlock_force, form_post_unlock_force);

			// Trigger error event
			if(errors || error_validation) {

				// Trigger error
				ws_this.trigger(post_mode + '-error');
				ws_this.trigger('error');

			} else {

				// Trigger success
				ws_this.trigger(post_mode + '-success');
				ws_this.trigger('success');
			}

			// Check for form reload on submit
			if(
				(submit && !error_validation && !errors)
			) {

				// Clear hash
				ws_this.form_hash_clear();

				if(ws_this.get_object_meta_value(ws_this.form, 'submit_reload', true)) {

					// Reload
					ws_this.form_reload();
				}
			}

			// Show error messages
			if(errors && ws_this.get_object_meta_value(ws_this.form, 'submit_show_errors', true)) {

				for(var error_index in response.data.errors) {

					if(!response.data.errors.hasOwnProperty(error_index)) { continue; }

					var error_message = response.data.errors[error_index];
					ws_this.action_message(error_message);
				}
			}

			ws_this.trigger(post_mode + '-complete');
			ws_this.trigger('complete');

			return !errors;

		}, function(response) {

			// Error
			ws_this.form_post_unlock('progress', true, true);


			// Show error message
			if(typeof(response.error_message) !== 'undefined') {

				ws_this.action_message(response.error_message);
			}

			// Trigger post most complete event
			ws_this.trigger(post_mode + '-error');
			ws_this.trigger('error');

		}, (action_id > 0));
	}

	// Form lock
	$.WS_Form.prototype.form_post_lock = function(cursor, force, ecommerce_calculate_disable) {

		if(typeof(cursor) === 'undefined') { cursor = 'progress'; }
		if(typeof(force) === 'undefined') { force = false; }
		if(typeof(ecommerce_calculate_disable) === 'undefined') { ecommerce_calculate_disable = false; }

		if(this.form_obj.hasClass('wsf-form-post-lock')) { return; }

		if(force || this.get_object_meta_value(this.form, 'submit_lock', false)) {

			// Stop further calculations
			if(ecommerce_calculate_disable) {

				this.form_ecommerce_calculate_enabled = false;
			}

			// Add locked class to form
			this.form_obj.addClass('wsf-form-post-lock' + (cursor ? ' wsf-form-post-lock-' + cursor : ''));

			// Disable submit buttons
			$('button[type="submit"].wsf-button, input[type="submit"].wsf-button, button[data-action="wsf-save"].wsf-button, button[data-ecommerce-payment].wsf-button, [data-post-lock]', this.form_canvas_obj).attr('disabled', '');
			this.form_post_locked = true;

			// Trigger lock event
			this.trigger('lock');

		}
	}

	// Form unlock
	$.WS_Form.prototype.form_post_unlock = function(cursor, timeout, force) {

		if(typeof(cursor) === 'undefined') { cursor = 'progress'; }
		if(typeof(timeout) === 'undefined') { timeout = true; }
		if(typeof(force) === 'undefined') { force = false; }

		if(!this.form_obj.hasClass('wsf-form-post-lock')) { return; }

		var ws_this = this;

		var unlock_fn = function() {

			// Re-enable cart calculations
			ws_this.form_ecommerce_calculate_enabled = true;

			// Remove locked class from form
			ws_this.form_obj.removeClass('wsf-form-post-lock' + (cursor ? ' wsf-form-post-lock-' + cursor : ''));

			// Enable submit buttons
			$('button[type="submit"].wsf-button, input[type="submit"].wsf-button, button[data-action="wsf-save"].wsf-button, button[data-ecommerce-payment].wsf-button, [data-post-lock]', ws_this.form_canvas_obj).removeAttr('disabled');
			ws_this.form_post_locked = false;

			// Reset post upload progress indicators
			ws_this.api_call_progress_reset();

			// Trigger unlock event
			ws_this.trigger('unlock');

		}

		if(force || this.get_object_meta_value(this.form, 'submit_unlock', false)) {

			// Enable post buttons
			timeout ? setTimeout(function() { unlock_fn(); }, 1000) : unlock_fn();
		}
	}

	// API Call
	$.WS_Form.prototype.api_call = function(ajax_path, method, params, success_callback, error_callback, force_ajax_path) {

		// Defaults
		if(typeof(method) === 'undefined') { method = 'POST'; }
		if(!params) { params = new FormData(); }
		if(typeof(force_ajax_path) === 'undefined') { force_ajax_path = false; }

		var ws_this = this;


		// Make AJAX request
		var url = force_ajax_path ? (ws_form_settings.url_ajax + ajax_path) : ((ajax_path == 'submit') ? this.form_obj.attr('action') : (ws_form_settings.url_ajax + ajax_path));

		// Check for custom action URL
		if(
			!force_ajax_path &&
			this.form_action_custom &&
			((ajax_path == 'submit') || (ajax_path == 'save'))
		) {

			// Custom action submit
			this.form_obj.off('submit');
			this.form_obj.submit();
			return true;
		}

		// NONCE
		if(
			(typeof(params.get) === 'undefined') || // Do it anyway for IE 11
			(params.get(ws_form_settings.wsf_nonce_field_name) === null)
		) {

			params.append(ws_form_settings.wsf_nonce_field_name, ws_form_settings.wsf_nonce);
		}

		// Convert FormData to object if making GET request (IE11 friendly code so not that elegant)
		if(method === 'GET') {

			var params_object = {};

			var form_data_entries = params.entries();
			var form_data_entry = form_data_entries.next();

			while (!form_data_entry.done) {

				var pair = form_data_entry.value;
				params_object[pair[0]] = pair[1];
				form_data_entry = form_data_entries.next();
			}

			params = params_object;
		}

		// Process validation focus
		this.action_js_process_validation_focus = true;

		// Call AJAX
		var ajax_request = {

			method: method,
			url: url,
			beforeSend: function(xhr) {

				// Nonce (X-WP-Nonce)
				xhr.setRequestHeader('X-WP-Nonce', ws_form_settings.x_wp_nonce);
			},
			contentType: false,
			processData: (method === 'GET'),
 			statusCode: {

				// Success
				200: function(response) {

					// Handle hash response
					var hash_ok = ws_this.api_call_hash(response);

					// Check for new nonce values
					if(typeof(response.data) !== 'undefined') {

						if(typeof(response.data.x_wp_nonce) !== 'undefined') { ws_form_settings.x_wp_nonce = response.data.x_wp_nonce; }
						if(typeof(response.data.wsf_nonce) !== 'undefined') { ws_form_settings.wsf_nonce = response.data.wsf_nonce; }
					}

					// Call success function
					var success_callback_result = (typeof(success_callback) === 'function') ? success_callback(response) : true;

					// Check for data to process
					if(
						(typeof(response.data) !== 'undefined') &&
						success_callback_result
					) {

						// Check for action_js (These are returned from the action system to tell the browser to do something)
						if(typeof(response.data.js) === 'object') { ws_this.action_js_init(response.data.js); }
					}
				},

				// Bad request
				400: function(response) {

					// Process error
					ws_this.api_call_error_handler(response, 400, url, error_callback);
				},

				// Unauthorized
				401: function(response) {

					// Process error
					ws_this.api_call_error_handler(response, 401, url, error_callback);
				},

				// Forbidden
				403: function(response) {

					// Process error
					ws_this.api_call_error_handler(response, 403, url, error_callback);
				},

				// Not found
				404: function(response) {

					// Process error
					ws_this.api_call_error_handler(response, 404, url, error_callback);
				},

				// Server error
				500: function(response) {

					// Process error
					ws_this.api_call_error_handler(response, 500, url, error_callback);
				}
			},

			complete: function() {

				this.api_call_handle = false;
			}
		};

		// Data
		if(params !== false) { ajax_request.data = params; }

		// Progress
		var progress_objs = $('[data-source="post_progress"]', this.form_canvas_obj);
		if(progress_objs.length) {

			ajax_request.xhr = function() {

				var xhr = new window.XMLHttpRequest();
				xhr.upload.addEventListener("progress", function(e) { ws_this.api_call_progress(progress_objs, e); }, false);
				xhr.addEventListener("progress", function(e) { ws_this.api_call_progress(progress_objs, e); }, false);
				return xhr;
			};
		}

		return $.ajax(ajax_request);
	};

	// API call - Process error
	$.WS_Form.prototype.api_call_error_handler = function(response, status, url, error_callback) {

		// Get response data
		var data = (typeof(response.responseJSON) !== 'undefined') ? response.responseJSON : false;

		// Process WS Form API error message
		if(data && data.error) {

			if(data.error_message) {

				this.error('error_api_call_' + status, data.error_message);

			} else {

				this.error('error_api_call_' + status, url);
			}

		} else {

			// Fallback
			this.error('error_api_call_' + status, url);
		}

		// Call error call back
		if(typeof(error_callback) === 'function') {

			// Run error callback
			error_callback(data);

		}
	}

	// API Call - Progress
	$.WS_Form.prototype.api_call_progress = function(progress_objs, e) {

		if(!e.lengthComputable) { return; }

		var ws_this = this;

		progress_objs.each(function() {

			// Get progress value
			var progress_percentage = (e.loaded / e.total) * 100;

			// Set progress fields
			ws_this.form_progress_set_value($(this), Math.round(progress_percentage));
		});
	}

	// API Call - Progress
	$.WS_Form.prototype.api_call_progress_reset = function() {

		var ws_this = this;

		var progress_obj = $('[data-progress-bar][data-source="post_progress"]', this.form_canvas_obj);
		progress_obj.each(function() {

			ws_this.form_progress_set_value($(this), 0);
		});
	}

	// JS Actions - Init
	$.WS_Form.prototype.action_js_init = function(action_js) {

		// Trigger actions start event
		this.trigger('actions-start');

		this.action_js = action_js;

		this.action_js_process_next();
	};

	$.WS_Form.prototype.action_js_process_next = function() {

		if(this.action_js.length == 0) {

			// Trigger actions finish event
			this.trigger('actions-finish');

			return false;
		}

		var js_action = this.action_js.shift();

		var action = this.js_action_get_parameter(js_action, 'action');

		switch(action) {

			// Redirect
			case 'redirect' :

				var url = this.js_action_get_parameter(js_action, 'url');
				if(url !== false) { location.href = js_action['url']; }

				// Actions end at this point because of the redirect
				return true;

				break;

			// Message
			case 'message' :

				var message = this.js_action_get_parameter(js_action, 'message');
				var type = this.js_action_get_parameter(js_action, 'type');
				var method = this.js_action_get_parameter(js_action, 'method');
				var duration = this.js_action_get_parameter(js_action, 'duration');
				var form_hide = this.js_action_get_parameter(js_action, 'form_hide');
				var clear = this.js_action_get_parameter(js_action, 'clear');
				var scroll_top = this.js_action_get_parameter(js_action, 'scroll_top');
				var scroll_top_offset = this.js_action_get_parameter(js_action, 'scroll_top_offset');
				var scroll_top_duration = this.js_action_get_parameter(js_action, 'scroll_top_duration');
				var form_show = this.js_action_get_parameter(js_action, 'form_show');
				var message_hide = this.js_action_get_parameter(js_action, 'message_hide');

				this.action_message(message, type, method, duration, form_hide, clear, scroll_top, scroll_top_offset, scroll_top_duration, form_show, message_hide);

				break;
			// Field invalid feedback
			case 'field_invalid_feedback' :

				var field_id = parseInt(this.js_action_get_parameter(js_action, 'field_id'));
				var section_repeatable_index = parseInt(this.js_action_get_parameter(js_action, 'section_repeatable_index'));
				var section_repeatable_suffix = section_repeatable_index ? '-repeat-' + section_repeatable_index : '';
				var message = this.js_action_get_parameter(js_action, 'message');

				// Field object
				var field_obj = $('#' + this.form_id_prefix + 'field-' + field_id + section_repeatable_suffix, this.form_canvas_obj);

				// Set invalid feedback
				this.set_invalid_feedback(field_obj, message);

				// Log event
				this.log('error_invalid_feedback', field_id + ' (' + this.html_encode(message) + ')');

				var ws_this = this;

				// Reset if field modified
				field_obj.one('change input keyup paste', function() {

					// Reset invalid feedback
					ws_this.set_invalid_feedback($(this), '');
				});

				// Process focus?
				if(
					this.get_object_meta_value(this.form, 'invalid_field_focus', true) &&
					this.action_js_process_validation_focus
				) {

					// Get group index
					var group_index_focus = this.get_group_index(field_obj);

					// Focus object
					if(group_index_focus !== false) { 

						this.object_focus = field_obj;

					} else {

						field_obj.trigger('focus');
					}

					// Focus tab
					if(group_index_focus !== false) { this.group_index_set(group_index_focus); }

					// Prevent further focus
					this.action_js_process_validation_focus = false;
				}

				this.action_js_process_next();

				break;

			case 'trigger' :

				var event = this.js_action_get_parameter(js_action, 'event');
				var params = this.js_action_get_parameter(js_action, 'params');

				$(document).trigger(event, params);

				this.action_js_process_next();

				break;
		}
	}

	// JS Actions - Get js_action config parameter from AJAX return
	$.WS_Form.prototype.js_action_get_parameter = function(js_action_parameters, meta_key) {

		return typeof(js_action_parameters[meta_key]) !== 'undefined' ? js_action_parameters[meta_key] : false;
	}

	// JS Actions - Get framework config value
	$.WS_Form.prototype.get_framework_config_value = function(object, meta_key) {

		if(typeof(this.framework[object]) === 'undefined') {
			return false;
		}
		if(typeof(this.framework[object]['public']) === 'undefined') {
			return false;
		}
		if(typeof(this.framework[object]['public'][meta_key]) === 'undefined') { return false; }

		return this.framework[object]['public'][meta_key];
	}

	// JS Action - Message
	$.WS_Form.prototype.action_message = function(message, type, method, duration, form_hide, clear, scroll_top, scroll_top_offset, scroll_top_duration, form_show, message_hide) {

		// Error message setting defaults
		if(typeof(type) === 'undefined') { type = this.get_object_meta_value(this.form, 'error_type', 'danger'); }
		if(typeof(method) === 'undefined') { method = this.get_object_meta_value(this.form, 'error_method', 'after'); }
		if(typeof(duration) === 'undefined') { duration = parseInt(this.get_object_meta_value(this.form, 'error_duration', '4000')); }
		if(typeof(form_hide) === 'undefined') { form_hide = (this.get_object_meta_value(this.form, 'error_form_hide', '') == 'on'); }
		if(typeof(clear) === 'undefined') { clear = (this.get_object_meta_value(this.form, 'error_clear', '') == 'on'); }
		if(typeof(scroll_top) === 'undefined') { scroll_top = (this.get_object_meta_value(this.form, 'error_scroll_top', '') == 'on'); }
		if(typeof(scroll_top_offset) === 'undefined') { scroll_top_offset = parseInt(this.get_object_meta_value(this.form, 'error_scroll_top_offset', '0')); }
		scroll_top_offset = (scroll_top_offset == '') ? 0 : parseInt(scroll_top_offset, 10);
		if(typeof(scroll_top_duration) === 'undefined') { scroll_top_duration = parseInt(this.get_object_meta_value(this.form, 'error_scroll_top_duration', '400')); }
		if(typeof(form_show) === 'undefined') { form_show = (this.get_object_meta_value(this.form, 'error_form_show', '') == 'on'); }
		if(typeof(message_hide) === 'undefined') { message_hide = (this.get_object_meta_value(this.form, 'error_message_hide', 'on') == 'on'); }

		var scroll_position = this.form_canvas_obj.offset().top - scroll_top_offset;

		// Parse duration
		duration = parseInt(duration, 10);
		if(duration < 0) { duration = 0; }

		// Get config
		var mask_wrapper = this.get_framework_config_value('message', 'mask_wrapper');
		var types = this.get_framework_config_value('message', 'types');

		var type = (typeof(types[type]) !== 'undefined') ? types[type] : false;
		var mask_wrapper_class = (typeof(type['mask_wrapper_class']) !== 'undefined') ? type['mask_wrapper_class'] : '';

		// Clear other messages
		if(clear) {

			$('[data-wsf-message][data-wsf-instance-id="' + this.form_instance_id + '"]').remove();
		}

		// Scroll top
		switch(scroll_top) {

			case 'instant' :
			case 'on' :			// Legacy

				$('html,body').scrollTop(scroll_position);

				break;

			// Smooth
			case 'smooth' :

				scroll_top_duration = (scroll_top_duration == '') ? 0 : parseInt(scroll_top_duration, 10);

				$('html,body').animate({

					scrollTop: scroll_position

				}, scroll_top_duration);

				break;
		}

		var mask_wrapper_values = {

			'message':				message,
			'mask_wrapper_class':	mask_wrapper_class 
		};

		var message_div = $('<div/>', { html: this.mask_parse(mask_wrapper, mask_wrapper_values) });
		message_div.attr('role', 'alert');
		message_div.attr('data-wsf-message', '');
		message_div.attr('data-wsf-instance-id', this.form_instance_id);

		// Hide form?
		if(form_hide) { this.form_obj.hide(); }

		// Render message
		switch(method) {

			// Before
			case 'before' :

				message_div.insertBefore(this.form_obj);
				break;

			// After
			case 'after' :

				message_div.insertAfter(this.form_obj);
				break;
		}

		// Process next action
		var ws_this = this;

		duration = parseInt(duration, 10);

		if(duration > 0) {

			setTimeout(function() {

				// Should this message be removed?
				if(message_hide) { message_div.remove(); }

				// Should the form be shown?
				if(form_show) { ws_this.form_canvas_obj.show(); }

				// Process next js_action
				ws_this.action_js_process_next();

			}, duration);

		} else {

			// Process next js_action
			ws_this.action_js_process_next();
		}
	}
	// Text input and textarea character and word count
	$.WS_Form.prototype.form_character_word_count = function(obj) {

		var ws_this = this;
		if(typeof(obj) === 'undefined') { obj = this.form_canvas_obj; }

		// Run through each input that accepts text
		for(var field_id in this.field_data_cache) {

			if(!this.field_data_cache.hasOwnProperty(field_id)) { continue; }

			var field = this.field_data_cache[field_id];

			// Process help?
			var help = this.get_object_meta_value(field, 'help', '', false, true);
			var process_help = (

				(help.indexOf('#character_') !== -1) ||
				(help.indexOf('#word_') !== -1)
			);

			// Process min or max?
			var process_min_max = (

				this.has_object_meta_key(field, 'min_length') ||
				this.has_object_meta_key(field, 'max_length') ||
				this.has_object_meta_key(field, 'min_length_words') ||
				this.has_object_meta_key(field, 'max_length_words')
			);

			if(process_min_max || process_help) {

				// Process count functionality on field
				var field_obj = $('#' + this.form_id_prefix + 'field-' + field_id, obj);
				if(!field_obj.length) { field_obj = $('[id^="' + this.form_id_prefix + 'field-' + field_id + '-"]:not([data-init-char-word-count]):not(iframe)', obj); }

				field_obj.each(function() {

					// Flag so it only initializes once
					$(this).attr('data-init-char-word-count', '');

					if(ws_this.form_character_word_count_process($(this))) {

						$(this).on('keyup change paste', function() { ws_this.form_character_word_count_process($(this)); });
					}
				});
			}
		}
	}

	// Text input and textarea character and word count - Process
	$.WS_Form.prototype.form_character_word_count_process = function(obj) {

		// Get minimum and maximum character count
		var field = this.get_field(obj);

		var min_length = this.get_object_meta_value(field, 'min_length', '');
		min_length = (parseInt(min_length, 10) > 0) ? parseInt(min_length, 10) : false;

		var max_length = this.get_object_meta_value(field, 'max_length', '');
		max_length = (parseInt(max_length, 10) > 0) ? parseInt(max_length, 10) : false;

		// Get minimum and maximum word length
		var min_length_words = this.get_object_meta_value(field, 'min_length_words', '');
		min_length_words = (parseInt(min_length_words, 10) > 0) ? parseInt(min_length_words, 10) : false;

		var max_length_words = this.get_object_meta_value(field, 'max_length_words', '');
		max_length_words = (parseInt(max_length_words, 10) > 0) ? parseInt(max_length_words, 10) : false;

		// Calculate sizes
		var val = obj.val();

		// Check value is a string
		if(typeof(val) !== 'string') { return; }

		var character_count = val.length;
		var character_remaining = (max_length !== false) ? max_length - character_count : false;
		if(character_remaining < 0) { character_remaining = 0; }

		var word_count = this.get_word_count(val);
		var word_remaining = (max_length_words !== false) ? max_length_words - word_count : false;
		if(word_remaining < 0) { word_remaining = 0; }

		// Check minimum and maximums counts
		var count_invalid = false;
		var count_invalid_message_array = [];

		if((min_length !== false) && (character_count < min_length)) {

			count_invalid_message_array.push(this.language('error_min_length', min_length));
			count_invalid = true;
		}
		if((max_length !== false) && (character_count > max_length)) {

			count_invalid_message_array.push(this.language('error_max_length', max_length));
			count_invalid = true;
		}
		if((min_length_words !== false) && (word_count < min_length_words)) {

			count_invalid_message_array.push(this.language('error_min_length_words', min_length_words));
			count_invalid = true;
		}
		if((max_length_words !== false) && (word_count > max_length_words)) {

			count_invalid_message_array.push(this.language('error_max_length_words', max_length_words));
			count_invalid = true;
		}

		// Check if required
		if(
			(typeof(obj.attr('required')) !== 'undefined') ||
			(val.length > 0)
		) {

			// Check if count_invalid
			if(count_invalid) {

				// Set invalid feedback
				this.set_invalid_feedback(obj, count_invalid_message_array.join(' / '));

			} else {

				// Reset invalid feedback
				this.set_invalid_feedback(obj, '');
			}

		} else {

			// Reset invalid feedback
			this.set_invalid_feedback(obj, '');
		}

		// Process help
		var help = this.get_object_meta_value(field, 'help', '', false, true);

		// If #character_ and #word_ not present, don't bother processing
		if(
			(help.indexOf('#character_') === -1) &&
			(help.indexOf('#word_') === -1)
		) {
			return true;
		}

		// Get language
		var character_singular = this.language('character_singular');
		var character_plural = this.language('character_plural');
		var word_singular = this.language('word_singular');
		var word_plural = this.language('word_plural');

		// Set mask values
		var mask_values_help = {

			// Characters
			'character_count':				character_count,
			'character_count_label':		(character_count == 1 ? character_singular : character_plural),
			'character_remaining':			(character_remaining !== false) ? character_remaining : '',
			'character_remaining_label':	(character_remaining == 1 ? character_singular : character_plural),
			'character_min':				(min_length !== false) ? min_length : '',
			'character_min_label':			(min_length !== false) ? (min_length == 1 ? character_singular : character_plural) : '',
			'character_max':				(max_length !== false) ? max_length : '',
			'character_max_label':			(max_length !== false) ? (max_length == 1 ? character_singular : character_plural) : '',

			// Words
			'word_count':			word_count,
			'word_count_label':		(word_count == 1 ? word_singular : word_plural),
			'word_remaining':		(word_remaining !== false) ? word_remaining : '',
			'word_remaining_label': (word_remaining == 1 ? word_singular : word_plural),
			'word_min':				(min_length_words !== false) ? min_length_words : '',
			'word_min_label':		(min_length_words !== false) ? (min_length_words == 1 ? word_singular : word_plural) : '',
			'word_max':				(max_length_words !== false) ? max_length_words : '',
			'word_max_label':		(max_length_words !== false) ? (max_length_words == 1 ? word_singular : word_plural) : ''
		};

		// Parse help mask
		var help_parsed = this.mask_parse(help, mask_values_help);

		// Update help HTML
		var help_obj = this.get_help_obj(obj);
		help_obj.html(help_parsed);

		return true;
	}

	// Get word count of a string
	$.WS_Form.prototype.get_word_count = function(input_string) {

		// Trim input string
		input_string = input_string.trim();

		// If string is empty, return 0
		if(input_string.length == 0) { return 0; }

		// Return word count
		return input_string.trim().replace(/\s+/gi, ' ').split(' ').length;
	}

	// API Call
	$.WS_Form.prototype.api_call_hash = function(response) {

		var hash_ok = true;
		if(typeof(response.hash) === 'undefined') { hash_ok = false; }
		if(hash_ok && (response.hash.length != 32)) { hash_ok = false; }
		if(hash_ok) {

			// Set hash
			this.hash_set(response.hash)
		}

		return hash_ok;
	}

	// Hash - Set
	$.WS_Form.prototype.hash_set = function(hash, token, cookie_set) {

		if(typeof(token) === 'undefined') { token = false; }
		if(typeof(cookie_set) === 'undefined') { cookie_set = false; }

		if(hash != this.hash) {

			// Set hash
			this.hash = hash;

			// Set hash cookie
			cookie_set = true;

		}

		if(token) {

			// Set token
			this.token = token;

		}

		if(cookie_set) {

			var cookie_hash = this.get_object_value($.WS_Form.settings_plugin, 'cookie_hash');

			if(cookie_hash) {

				this.cookie_set('hash', this.hash);
			}
		}
	}

	// Generate password
	$.WS_Form.prototype.generate_password = function(length) {

		var password = '';
		var characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+~`|}{[]\\:;?><,./-=';
		
		for(var i = 0; i < length; ++i) { password += characters.charAt(Math.floor(Math.random() * characters.length)); }

		return password;
	}

	// Form - Statistics
	$.WS_Form.prototype.form_stat = function() {

		// Add view
		if(ws_form_settings.stat) { this.form_stat_add_view(); }
	}

	// Add view statistic
	$.WS_Form.prototype.form_stat_add_view = function() {

		// Call AJAX
		$.ajax({ method: 'POST', url: ws_form_settings.add_view_url, data: { wsffid: this.form_id } });
	}

	// Initialize forms function
	window.wsf_form_instances = [];

	window.wsf_form_init = function(force_reload, reset_events, container) {

		if(typeof(force_reload) === 'undefined') { force_reload = false; }
		if(typeof(reset_events) === 'undefined') { reset_events = false; }
		if(typeof(container) === 'undefined') {

			var forms = $('.wsf-form');

		} else {

			var forms = $('.wsf-form', container);
		}

		if(!$('.wsf-form').length) { return; }

		// Get highest instance ID
		var set_instance_id = 0;
		var instance_id_array = [];

		$('.wsf-form').each(function() {

			if(typeof($(this).attr('data-instance-id')) === 'undefined') { return; }

			// Get instance ID
			var instance_id_single = parseInt($(this).attr('data-instance-id'));

			// Check for duplicate instance ID
			if(instance_id_array.indexOf(instance_id_single) !== -1) {

				// If duplicate, remove the data-instance-id so it is reset
				$(this).removeAttr('data-instance-id');

			} else {

				// Check if this is the highest instance ID
				if(instance_id_single > set_instance_id) { set_instance_id = instance_id_single; }
			}

			instance_id_array.push(instance_id_single);
		});

		// Increment to next instance ID
		set_instance_id++;

		// Render each form
		forms.each(function() {

			// Skip forms already initialized
			if(!force_reload && (typeof($(this).attr('data-wsf-rendered')) !== 'undefined')) { return; }

			// Reset events
			if(reset_events) { $(this).off(); }

			// Set instance ID
			if(typeof($(this).attr('data-instance-id')) === 'undefined') {

				// Set ID (Only if custom ID not set)
				if(typeof($(this).attr('data-wsf-custom-id')) === 'undefined') {

					$(this).attr('id', 'ws-form-' + set_instance_id);
				}

				// Set instance ID
				$(this).attr('data-instance-id', set_instance_id);

				set_instance_id++;
			}

			// Get attributes
			var id = $(this).attr('id');
			var form_id = $(this).attr('data-id');
			var instance_id = $(this).attr('data-instance-id');

			if(id && form_id && instance_id) {

				// Initiate new WS Form object
				var ws_form = new $.WS_Form();

				// Save to wsf_form_instances array
				window.wsf_form_instances[instance_id] = ws_form;

				// Render
				ws_form.render({

					'obj' :			'#' + id,
					'form_id':		form_id
				});
			}
		});
	}

	// On load
	$(function() { wsf_form_init(); });

})(jQuery);
