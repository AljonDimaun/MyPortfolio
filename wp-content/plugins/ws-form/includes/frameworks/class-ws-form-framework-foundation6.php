<?php

	//	Framework config: Foundation 6

	class WS_Form_Config_Framework_Foundation_6 {

		// Configuration - Frameworks
		public static function get_framework_config() {

			return array(

				'name'						=>	__('Foundation 6.0-6.3.1', 'ws-form'),

				'default'					=>	false,

				'css_file'					=>	'foundation6.css',

				'label_positions'			=>	array('default', 'top', 'left', 'right'),

				'init_js'					=>	"if(typeof(Foundation) === 'object') {

					// Abide
					var abide_initialized = true;
					if(typeof(Foundation.Abide) === 'function') {

						var form_obj = $('#form_canvas_selector').closest('form[data-abide]');
						if(form_obj.length) {

							if(!new Foundation.Abide(form_obj)) {

								abide_initialized = true;
							}
						}
					}

					// Tabs
					var tabs_initialized = true;
					if(typeof(Foundation.Tabs) === 'function') {

						var tabs_obj = $('#form_canvas_selector [data-tabs]');
						if(tabs_obj.length) {

							if(!new Foundation.Tabs(tabs_obj)) {

								tabs_initialized = false;
							}
						}
					}

					// Error handling
					if(typeof($('#form_canvas_selector')[0].ws_form_log_error) === 'function') {

						if(!abide_initialized) {

							$('#form_canvas_selector')[0].ws_form_log_error('error_framework_plugin', 'Abide', 'framework');
						}

						if(!tabs_initialized) {

							$('#form_canvas_selector')[0].ws_form_log_error('error_framework_plugin', 'Tabs', 'framework');
						}
					}
				}",

				'minicolors_args'			=>	array(

					'theme' 				=> 'foundation'
				),

				'columns'					=>	array(

					'column_class'				=>	'#id-#size',
					'column_css_selector'		=>	'.#id-#size',
					'offset_class'				=>	'#id-offset-#offset',
					'offset_css_selector'		=>	'.#id-offset-#offset'
				),

				'breakpoints'				=>	array(

					// Up to 639px
					25	=>	array(
						'id'					=>	'small',
						'name'					=>	__('Small', 'ws-form'),
						'column_class'			=>	'#id-#size',
						'column_css_selector'	=>	'.#id-#size',
						'admin_max_width'		=>	639,
						'column_size_default'	=>	'column_count'	// Set to column count if XS framework breakpoint size is not set in object meta
					),
				),

				'form' => array(

					'admin' => array('mask_single' => '#form'),
					'public' => array(

						'mask_single' 	=> '#label#form',
						'mask_label'	=> '<h2>#label</h2>',
						'attributes' => array('data-abide' => '')
					),
				),

				'tabs' => array(

					'public' => array(

						'mask_wrapper'				=>	'<ul class="tabs #class" data-tabs id="#id"#attributes>#tabs</ul>',
						'mask_single'				=>	'<li class="tabs-title#active"#attributes><a href="#href">#label</a></li>',
						'active'					=>	' is-active',
						'activate_js'				=>	"$('#form .tabs .tabs-title:eq(#index) a').click();",
						'event_js'					=>	'change.zf.tabs',
						'event_type_js'				=>	'wrapper',
						'event_selector_wrapper_js'	=>	'ul[data-tabs]',
						'event_selector_active_js'	=>	'li.is-active',
						'class_parent_disabled'		=>	'wsf-tab-disabled'
					),
				),

				'message' => array(

					'public'	=>	array(

						'mask_wrapper'		=>	'<div class="callout #mask_wrapper_class">#message</div>',

						'types'	=>	array(

							'success'		=>	array('mask_wrapper_class' => 'success'),
							'information'	=>	array('mask_wrapper_class' => 'primary'),
							'warning'		=>	array('mask_wrapper_class' => 'warning'),
							'danger'		=>	array('mask_wrapper_class' => 'alert')
						)
					)
				),

				'groups' => array(

					'public' => array(

						'mask_wrapper'	=>	'<div class="tabs-content" data-tabs-content="#id">#groups</div>',
						'mask_single' 	=> '<div class="#class" id="#id" data-id="#data_id" data-group-index="#data_group_index"#attributes>#label#group</div>',
						'mask_label' 	=> '<h3>#label</h3>',
						'class'			=> 'tabs-panel',
						'class_active'	=> 'is-active',
					)
				),

				'sections' => array(

					'public' => array(

						'mask_wrapper'	=> '<div class="row" id="#id" data-id="#data_id">#sections</div>',
						'mask_single' 	=> '<fieldset#attributes class="#class" id="#id" data-id="#data_id">#section</fieldset>',
						'class_single'	=> array('columns')
					)
				),

				'fields' => array(

					'public' => array(

						// Honeypot attributes
						'honeypot_attributes' => array('data-abide-ignore'),

						// Label position - Left
						'left' => array(

							'mask'							=>	'<div class="row">#field</div>',
							'mask_field_label_wrapper'		=>	'<div class="small-#column_width_label columns">#label</div>',
							'mask_field_label'				=>	'<label id="#label_id" for="#id"#attributes>#label</label>',
							'mask_field_wrapper'			=>	'<div class="small-#column_width_field columns">#field</div>',
							'class_field_label'				=>	array('text-right', 'middle'),
						),

						// Label position - Right
						'right' => array(

							'mask'							=>	'<div class="row">#field</div>',
							'mask_field_label_wrapper'		=>	'<div class="small-#column_width_label columns">#label</div>',
							'mask_field_label'				=>	'<label id="#label_id" for="#id"#attributes>#label</label>',
							'mask_field_wrapper'			=>	'<div class="small-#column_width_field columns">#field</div>',
							'class_field_label'				=>	array('middle'),
						),

						// Masks - Section
						'mask_wrapper' 			=> '#label<div class="row" id="#id" data-id="#data_id">#fields</div>',
						'mask_wrapper_label'	=> '<legend>#label</legend>',

						// Masks - Field
						'mask_single' 			=> '<div class="#class" id="#id" data-id="#data_id" data-type="#type"#attributes>#field</div>',
						'mask_field_label'		=> '<label id="#label_id" for="#id"#attributes>#field</label>',

						// Input group
						'mask_field_input_group'			=>	'#pre_label<div class="input-group">#field</div>#post_label#invalid_feedback#help',
						'mask_field_input_group_prepend'	=>	'<span class="input-group-label">#prepend</span>',
						'mask_field_input_group_append'		=>	'<span class="input-group-label">#append</span>',
						'class_field_input_group'			=>	'input-group-field',

						// Required
						'mask_required_label'	=> ' <small>Required</small>',

						// Help
						'mask_help'				=>	'<p id="#help_id" class="#help_class"#attributes>#help#help_append</p>',

						// Invalid feedback
						'mask_invalid_feedback'	=>	'<span id="#invalid_feedback_id" data-form-error-for="#id" class="#invalid_feedback_class"#attributes>#invalid_feedback</span>',

						// Classes - Default
						'class_single'					=> array('columns'),
						'class_field'					=> array(),
						'class_field_label'				=> array(),
						'class_help'					=> array('help-text'),
						'class_invalid_feedback'		=> array('form-error'),
						'class_inline' 					=> array('form-inline'),
						'class_form_validated'			=> array('was-validated'),
						'class_orientation_wrapper'		=> array('row'),
						'class_orientation_row'			=> array('columns'),
						'class_single_vertical_align'	=> array(

							'middle'	=>	'align-self-middle',
							'bottom'	=>	'align-self-bottom'
						),
						'class_field_button_type'	=> array(

							'primary'		=>	'primary',
							'secondary'		=>	'secondary',
							'success'		=>	'success',
							'warning'		=>	'warning',
							'danger'		=>	'alert'
						),
						'class_field_message_type'	=> array(

							'success'		=>	'success',
							'information'	=>	'primary',
							'warning'		=>	'warning',
							'danger'		=>	'alert'
						),

						// Attributes
						'attribute_field_match'		=> array('data-equalto' => '#field_match_id'),

						// Classes - Custom by field type
						'field_types'				=> array(

							'checkbox' 	=> array(

								'class_inline' 			=> array(),
								'mask_field'			=> '<div#attributes>#datalist</div>#invalid_feedback#help',
								'mask_group'			=> '<fieldset#disabled>#group_label#group</fieldset>',
								'mask_row_label'		=> '#row_field<label id="#label_row_id" for="#row_id"#attributes>#checkbox_field_label</label>#invalid_feedback',
								'mask_single' 			=> '<fieldset class="#class" id="#id" data-id="#data_id" data-type="#type"#attributes>#field</fieldset>',
								'mask_field_label'		=> '<legend id="#label_id" for="#id"#attributes>#label</legend>#field',
							),

							'radio' 				=> array(

								'class_inline' 			=> array(),
								'mask_field'			=> '<div#attributes>#datalist</div>#help',
								'mask_group'			=> '<fieldset#disabled>#group_label#group</fieldset>',
								'mask_row_label'		=> '#row_field<label id="#label_row_id" for="#row_id" data-label-required-id="#label_id"#attributes>#radio_field_label</label>#invalid_feedback',
								'mask_single' 			=> '<fieldset class="#class" id="#id" data-id="#data_id" data-type="#type"#attributes>#field</fieldset>',
								'mask_field_label'		=> '<legend id="#label_id" for="#id"#attributes>#label</legend>#field',
							),

							'spacer'	=> array(

								'mask_field_label'		=>	'',
							),

							'texteditor'	=> array(

								'mask_field_label'		=>	'',
							),

							'submit' 	=> array(

								'mask_field_label'					=>	'#label',
								'class_field'						=>	array('button'),
								'class_field_full_button'			=> array('expanded'),
								'class_field_button_type_fallback'	=> 'primary'
							),
						)
					)
				)
			);
		}
	}