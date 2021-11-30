<?php

	//	Framework config: WS-Form

	class WS_Form_Config_Framework_WS_Form extends WS_Form_Config {

		// Configuration - Frameworks
		public static function get_framework_config() {

			return array(

				'name'							=>	__('WS Form', 'ws-form'),

				'label_positions'				=>	array('default', 'top', 'left', 'right', 'bottom', 'inside'),

				'minicolors_args' 				=>	array(

					'theme' 					=> 'ws-form'
				),

				'columns'					=>	array(

					'column_class'				=>	'wsf-#id-#size',
					'column_css_selector'		=>	'.wsf-#id-#size',
					'offset_class'				=>	'wsf-offset-#id-#offset',
					'offset_css_selector'		=>	'.wsf-offset-#id-#offset'
				),

				'breakpoints'				=>	array(

					// Up to 575px
					25	=>	array(
						'id'					=>	'extra-small',
						'name'					=>	__('Extra Small', 'ws-form'),
						'admin_max_width'		=>	575,
						'column_size_default'	=>	'column_count'
					),

					// Up to 767px
					50	=>	array(
						'id'				=>	'small',
						'name'				=>	__('Small', 'ws-form'),
						'min_width'			=>	576,
						'admin_max_width'	=>	767
					),

					// Up to 991px
					75	=>	array(
						'id'				=>	'medium',
						'name'				=>	__('Medium', 'ws-form'),
						'min_width'			=>	768,
						'admin_max_width'	=>	991
					),

					// Up to 1199px
					100	=>	array(
						'id'				=>	'large',
						'name'				=>	__('Large', 'ws-form'),
						'min_width'			=>	992,
						'admin_max_width'	=>	1199
					),

					// 1200px+
					150	=>	array(
						'id'				=>	'extra-large',
						'name'				=>	__('Extra Large', 'ws-form'),
						'min_width'			=>	1200
					)
				),

				'form' => array(

					'admin' => array('mask_single' => '#form'),
					'public' => array(

						'mask_single' 	=> '#label#form',
						'mask_label'	=> '<h2>#label</h2>',
					),
				),

				'tabs' => array(

					'admin' => array(

						'mask_wrapper'		=>	'<ul class="wsf-group-tabs">#tabs</ul>',
						'mask_single'		=>	'<li class="wsf-group-tab" data-id="#data_id" title="#label"><a href="#href"><div class="wsf-group-label"><span class="wsf-group-hidden" title="' . __('Hidden', 'ws-form') . '">' . self::get_icon_16_svg('hidden') . '</span><input type="text" value="#label" data-label="#data_id" readonly></div></a></li>'
					),

					'public' => array(

						'mask_wrapper'		=>	'<ul class="#class" role="tablist"#attributes>#tabs</ul>',
						'mask_single'		=>	'<li class="wsf-group-tab" data-id="#data_id"#attributes><a href="#href" role="tab">#label</a></li>',
						'activate_js'		=>	"$('#form .wsf-group-tabs .wsf-group-tab:eq(#index) a').trigger('click');",
						'event_js'			=>	'tab_show',
						'event_type_js'		=>	'tab',
						'class_disabled'	=>	'wsf-tab-disabled'
					),
				),

				'message' => array(

					'public'	=>	array(

						'mask_wrapper'		=>	'<div class="wsf-alert#mask_wrapper_class">#message</div>',

						'types'	=>	array(

							'success'		=>	array('mask_wrapper_class' => ' wsf-alert-success', 'text_class' => 'wsf-text-success'),
							'information'	=>	array('mask_wrapper_class' => ' wsf-alert-information', 'text_class' => 'wsf-text-information'),
							'warning'		=>	array('mask_wrapper_class' => ' wsf-alert-warning', 'text_class' => 'wsf-text-warning'),
							'danger'		=>	array('mask_wrapper_class' => ' wsf-alert-danger', 'text_class' => 'wsf-text-danger')
						)
					)
				),

				'groups' => array(

					'admin' => array(

						// mask_wrapper is placed around all of the groups
						'mask_wrapper'	=>	'<div class="wsf-groups">#groups</div>',

						// mask_single is placed around each individual group
						'mask_single'	=>	'<div class="wsf-group" id="#id" data-id="#data_id" data-group-index="#data_group_index">#group</div>',
					),

					'public' => array(

						'mask_wrapper'	=>	'<div class="wsf-groups">#groups</div>',
						'mask_single' 	=> '<div class="#class" id="#id" data-id="#data_id" data-group-index="#data_group_index" role="tabpanel"#attributes>#label#group</div>',
						'mask_label' 	=> '<h3>#label</h3>',
						'class'			=> 'wsf-group'
					)
				),

				'sections' => array(

					'admin' => array(

						'mask_wrapper' 	=> '<ul class="wsf-sections" id="#id" data-id="#data_id">#sections</ul>',
						'mask_single' 	=> sprintf('<li class="#class" id="#id" data-id="#data_id"><div class="wsf-section-inner">#label<div class="wsf-section-type">%s#section_id</div>#section</div></li>', __('Section', 'ws-form')),
						'mask_label' 	=> '<div class="wsf-section-label"><span class="wsf-section-repeatable">' . self::get_icon_16_svg('redo') . '</span><span class="wsf-section-hidden" title="' . __('Hidden', 'ws-form') . '">' . self::get_icon_16_svg('hidden') . '</span><span class="wsf-section-disabled" title="' . __('Disabled', 'ws-form') . '">' . self::get_icon_16_svg('disabled') . '</span><input type="text" value="#label" data-label="#data_id" readonly></div>',
						'class_single'	=> array('wsf-section')
					),

					'public' => array(

						'mask_wrapper'	=> '<div class="wsf-grid wsf-sections" id="#id" data-id="#data_id">#sections</div>',
						'mask_single' 	=> '<fieldset#attributes class="#class" id="#id" data-id="#data_id">#section</fieldset>',
						'class_single'	=> array('wsf-tile', 'wsf-section')
					)
				),

				'fields' => array(

					'admin' => array(

						'mask_wrapper' 	=> '<ul class="wsf-fields" id="#id" data-id="#data_id">#fields</ul>',
						'mask_single' 	=> '<li class="#class" id="#id" data-id="#data_id" data-type="#type"></li>',
						'mask_label' 	=> '<h4>#label</h4>',
						'class_single'	=> array('wsf-field-wrapper')
					),

					'public' => array(

						// Label position - Left
						'left' => array(

							'mask'							=>	'<div class="wsf-grid wsf-fields">#field</div>',
							'mask_field_label_wrapper'		=>	'<div class="wsf-extra-small-#column_width_label wsf-tile wsf-label-wrapper">#label</div>',
							'mask_field_wrapper'			=>	'<div class="wsf-extra-small-#column_width_field wsf-tile">#field</div>',
						),

						// Label position - Right
						'right' => array(

							'mask'							=>	'<div class="wsf-grid wsf-fields">#field</div>',
							'mask_field_label_wrapper'		=>	'<div class="wsf-extra-small-#column_width_label wsf-tile wsf-label-wrapper">#label</div>',
							'mask_field_wrapper'			=>	'<div class="wsf-extra-small-#column_width_field wsf-tile">#field</div>',
						),

						// Label position - Inside
						'inside' => array(

							'mask_field_wrapper'			=>	'<div class="wsf-label-position-inside">#field</div>',
						),

						// Masks - Section
						'mask_wrapper' 			=> '#label<div class="wsf-grid wsf-fields" id="#id" data-id="#data_id">#fields</div>',
						'mask_wrapper_label'	=> '<legend>#label</legend>',

						// Masks - Field
						'mask_single' 			=> '<div class="#class" id="#id" data-id="#data_id" data-type="#type"#attributes>#field</div>',

						// Input group
						'mask_field_input_group'			=>	'#pre_label<div class="wsf-input-group#css_input_group">#field#post_label#invalid_feedback</div>#help',
						'mask_field_input_group_prepend'	=>	'<span class="wsf-input-group-prepend">#prepend</span>',
						'mask_field_input_group_append'		=>	'<span class="wsf-input-group-append">#append</span>',

						// Required
						'mask_required_label'	=> ' <strong class="wsf-text-danger">*</strong>',

						// Help
						'mask_help'				=>	'<small id="#help_id" class="#help_class"#attributes>#help#help_append</small>',

						// Invalid feedback
						'mask_invalid_feedback'	=>	'<div id="#invalid_feedback_id" class="#invalid_feedback_class"#attributes>#invalid_feedback</div>',

						// Classes - Default
						'class_single'					=> array('wsf-tile', 'wsf-field-wrapper'),
						'class_field'					=> array('wsf-field'),
						'class_field_label'				=> array('wsf-label'),
						'class_help'					=> array('wsf-help'),
						'class_invalid_feedback'		=> array('wsf-invalid-feedback'),
						'class_inline' 					=> array('wsf-inline'),
						'class_form_validated'			=> array('wsf-validated'),
						'class_orientation_wrapper'		=> array('wsf-grid'),
						'class_orientation_row'			=> array('wsf-tile'),
						'class_single_vertical_align'	=> array(

							'middle'	=>	'wsf-middle',
							'bottom'	=>	'wsf-bottom'
						),
						'class_field_button_type'	=> array(

							'primary'		=>	'wsf-button-primary',
							'secondary'		=>	'wsf-button-secondary',
							'success'		=>	'wsf-button-success',
							'information'	=>	'wsf-button-information',
							'warning'		=>	'wsf-button-warning',
							'danger'		=>	'wsf-button-danger'
						),
						'class_field_message_type'	=> array(

							'success'		=>	'wsf-alert-success',
							'information'	=>	'wsf-alert-information',
							'warning'		=>	'wsf-alert-warning',
							'danger'		=>	'wsf-alert-danger'
						),

						// Custom settings by field type
						'field_types'		=> array(

							'checkbox' 	=> array(

								'class_field'			=> array(),
								'class_row_field'		=> array('wsf-field'),
								'class_row_field_label'	=> array('wsf-label'),
								'mask_group'			=> '<fieldset class="wsf-fieldset"#disabled>#group_label#group</fieldset>',
								'mask_row_label'		=> '#row_field<label id="#label_row_id" for="#row_id"#attributes>#checkbox_field_label</label>#invalid_feedback',
							),

							'radio' 	=> array(

								'class_field'			=> array(),
								'class_row_field'		=> array('wsf-field'),
								'class_row_field_label'	=> array('wsf-label'),
								'mask_group'			=> '<fieldset class="wsf-fieldset"#disabled>#group_label#group</fieldset>',
								'mask_row_label'		=> '#row_field<label id="#label_row_id" data-label-required-id="#label_id" for="#row_id"#attributes>#radio_field_label</label>#invalid_feedback',
							),

							'spacer' 	=> array(
								'class_single'			=> array('wsf-tile'),
							),


							'submit' 	=> array(
								'class_field'						=> array('wsf-button'),
								'class_field_full_button'			=> array('wsf-button-full'),
								'class_field_button_type_fallback'	=> 'primary',
							),

						)
					)
				)
			);
		}
	}