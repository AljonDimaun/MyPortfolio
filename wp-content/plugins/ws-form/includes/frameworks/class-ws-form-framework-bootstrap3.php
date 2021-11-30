<?php

	//	Framework config: Bootstrap 3

	class WS_Form_Config_Framework_Bootstrap_3 {

		// Configuration - Frameworks
		public static function get_framework_config() {

			return array(

				'name'						=>	__('Bootstrap 3.x', 'ws-form'),

				'default'					=>	false,

				'css_file'					=>	'bootstrap3.css',

				'label_positions'			=>	array('default', 'top', 'left', 'right', 'bottom'),

				'minicolors_args'			=>	array(

					'changeDelay' 	=> 200,
					'letterCase' 	=> 'uppercase',
					'theme' 		=> 'bootstrap'
				),

				'columns'					=>	array(

					'column_class'				=>	'col-#id-#size',
					'column_css_selector'		=>	'.col-#id-#size',
					'offset_class'				=>	'col-#id-offset-#offset',
					'offset_css_selector'		=>	'.col-#id-offset-#offset'
				),

				'breakpoints'				=>	array(

					// Up to 767px
					25	=>	array(
						'id'				=>	'xs',
						'name'				=>	__('Extra Small', 'ws-form'),
						'admin_max_width'	=>	767,
						'column_size_default'	=>	'column_count'	// Set to column count if XS framework breakpoint size is not set in object meta
					),
				),

				'form' => array(

					'admin' => array('mask_single' => '#form'),
					'public' => array(

						'mask_single' 	=> '#label#form',
						'mask_label'	=> '<h2>#label</h2>',
					),
				),

				'tabs' => array(

					'public' => array(

						'mask_wrapper'		=>	'<ul class="nav nav-tabs #class" role="tablist"#attributes>#tabs</ul>',
						'mask_single'		=>	'<li#attributes><a class="nav-link" href="#href" data-toggle="tab" role="tab">#label</a></li>',
						'activate_js'		=>	"$('#form ul.nav-tabs li:eq(#index) a').tab('show');",
						'event_js'			=>	'shown.bs.tab',
						'event_type_js'		=>	'tab',
						'class_disabled'	=>	'disabled'
					),
				),

				'message' => array(

					'public'	=>	array(

						'mask_wrapper'		=>	'<div class="alert #mask_wrapper_class">#message</div>',

						'types'	=>	array(

							'success'		=>	array('mask_wrapper_class' => 'alert-success', 'text_class' => 'text-success'),
							'information'	=>	array('mask_wrapper_class' => 'alert-info', 'text_class' => 'text-info'),
							'warning'		=>	array('mask_wrapper_class' => 'alert-warning', 'text_class' => 'text-warning'),
							'danger'		=>	array('mask_wrapper_class' => 'alert-danger', 'text_class' => 'text-danger')
						)
					)
				),

				'groups' => array(

					'public' => array(

						'mask_wrapper'	=>	'<div class="tab-content">#groups</div>',
						'mask_single' 	=> '<div class="#class" id="#id" data-id="#data_id" data-group-index="#data_group_index" role="tabpanel"#attributes>#label#group</div>',
						'mask_label' 	=> '<h3>#label</h3>',
						'class'			=> 'tab-pane',
						'class_active'	=> 'active',
					)
				),

				'sections' => array(

					'public' => array(

						'mask_wrapper'	=> '<div class="row" id="#id" data-id="#data_id">#sections</div>',
						'mask_single' 	=> '<fieldset#attributes class="#class" id="#id" data-id="#data_id">#section</fieldset>',
						'class_single'	=> array('col')
					)
				),

				'fields' => array(

					'public' => array(

						// Label position - Left
						'left' => array(

							'mask'							=>	'<div class="row">#field</div>',
							'mask_field_label_wrapper'		=>	'<div class="col-xs-#column_width_label control-label text-right">#label</div>',
							'mask_field_wrapper'			=>	'<div class="col-xs-#column_width_field">#field</div>',
						),

						// Label position - Right
						'right' => array(

							'mask'							=>	'<div class="row">#field</div>',
							'mask_field_label_wrapper'		=>	'<div class="col-xs-#column_width_label control-label">#label</div>',
							'mask_field_wrapper'			=>	'<div class="col-xs-#column_width_field">#field</div>',
						),

						// Masks
						'mask_wrapper' 		=> '#label<div class="row" id="#id" data-id="#data_id">#fields</div>',
						'mask_wrapper_label'	=> '<legend>#label</legend>',
						'mask_single' 		=> '<div class="#class" id="#id" data-id="#data_id" data-type="#type"#attributes>#field</div>',

						// Input group
						'mask_field_input_group'			=>	'#pre_label<div class="input-group#css_input_group">#field#post_label#invalid_feedback</div>#help',
						'mask_field_input_group_prepend'	=>	'<div class="input-group-addon wsf-input-group-prepend">#prepend</div>',
						'mask_field_input_group_append'		=>	'<div class="input-group-addon wsf-input-group-append">#append</div>',

						// Required
						'mask_required_label'	=> ' <strong class="text-danger">*</strong>',

						// Help
						'mask_help'			=>	'<span id="#help_id" class="#help_class"#attributes>#help#help_append</span>',

						// Invalid feedback
						'mask_invalid_feedback'	=>	'<div id="#invalid_feedback_id" class="#invalid_feedback_class"#attributes>#invalid_feedback</div>',

						// Classes - Default
						'class_single'				=> array('form-group'),
						'class_field'				=> array('form-control'),
						'class_field_label'			=> array(),
						'class_help'				=> array('help-block'),
						'class_invalid_feedback'	=> array('help-block', 'wsf-invalid-feedback'),
						'class_inline' 				=> array('form-inline'),
						'class_form_validated'		=> array('wsf-validated'),
						'class_orientation_wrapper'	=> array('row'),
						'class_orientation_row'		=> array(),
						'class_field_button_type'	=> array(

							'default'		=>	'btn-default',
							'primary'		=>	'btn-primary',
							'success'		=>	'btn-success',
							'information'	=>	'btn-info',
							'warning'		=>	'btn-warning',
							'danger'		=>	'btn-danger'
						),
						'class_field_message_type'	=> array(

							'success'		=>	'alert-success',
							'information'	=>	'alert-info',
							'warning'		=>	'alert-warning',
							'danger'		=>	'alert-danger'
						),

						// Classes - Custom by field type
						'field_types'		=> array(

							'checkbox' 	=> array(

								'class_field'			=> array(),
								'mask_group'			=> '<fieldset#disabled>#group_label#group</fieldset>',
								'mask_row_label'		=> '<label id="#label_row_id" for="#row_id"#attributes>#row_field#checkbox_field_label#required#invalid_feedback</label>',
								'class_row'				=> array('checkbox'),
								'class_row_disabled'	=> array('disabled'),
								'class_inline' 			=> array('checkbox-inline'),
							),

							'radio' 	=> array(

								'class_field'			=> array(),
								'mask_group'			=> '<fieldset#disabled>#group_label#group</fieldset>',
								'mask_row_label'		=> '<label id="#label_row_id" for="#row_id" data-label-required-id="#label_id"#attributes>#row_field#radio_field_label#required#invalid_feedback</label>',
								'class_row'				=> array('radio'),
								'class_row_disabled'	=> array('disabled'),
								'class_inline' 			=> array('radio-inline'),
							),

							'spacer' 	=> array(
								'class_single'			=> array(),
							),

							'submit' 	=> array(
								'class_field'						=> array('btn'),
								'class_field_full_button'			=> array('btn-block'),
								'class_field_button_type_fallback'	=> 'primary'
							),

						)
					)
				)
			);
		}
	}