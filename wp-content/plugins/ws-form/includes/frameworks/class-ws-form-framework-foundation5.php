<?php

	//	Framework config: Foundation 5

	class WS_Form_Config_Framework_Foundation_5 {

		// Configuration - Frameworks
		public static function get_framework_config() {

			return array(

				'name'						=>	__('Foundation 5.x', 'ws-form'),

				'default'					=>	false,

				'css_file'					=>	'foundation5.css',

				'label_positions'			=>	array('default', 'top', 'left', 'right'),

				'init_js'					=>	"if(typeof($(document).foundation) === 'function') { $(document).foundation('tab', 'reflow'); }",

				'minicolors_args'			=>	array(

					'theme' 					=> 'foundation'
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
						'id'				=>	'small',
						'name'				=>	__('Small', 'ws-form'),
						'column_class'			=>	'#id-#size',
						'column_css_selector'	=>	'.#id-#size',
						'admin_max_width'	=>	640,
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

						'mask_wrapper'				=>	'<dl class="tabs #class" data-tab id="#id"#attributes>#tabs</dl>',
						'mask_single'				=>	'<dd class="tab-title#active"#attributes><a href="#href">#label</a></dd>',
						'active'					=>	' active',
						'activate_js'				=>	"$('#form .tabs .tab-title:eq(#index) a').click();",
						'event_js'					=>	'toggled',
						'event_type_js'				=>	'wrapper',
						'event_selector_wrapper_js'	=>	'dl[data-tab]',
						'event_selector_active_js'	=>	'dd.active',
						'class_parent_disabled'		=>	'wsf-tab-disabled'
					),
				),

				'message' => array(

					'public'	=>	array(

						'mask_wrapper'		=>	'<div class="alert-box #mask_wrapper_class">#message</div>',

						'types'	=>	array(

							'success'		=>	array('mask_wrapper_class' => 'success'),
							'information'	=>	array('mask_wrapper_class' => 'info'),
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
						'class'			=> 'content',
						'class_active'	=> 'active',
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

							'mask'									=>	'<div class="row">#field</div>',
							'mask_field_label_wrapper'				=>	'<div class="small-#column_width_label columns">#label</div>',
							'mask_field_label'						=>	'<label id="#label_id" for="#id"#attributes>#label</label>',
							'mask_field_wrapper'					=>	'<div class="small-#column_width_field columns">#field</div>',
							'class_field_label'						=>	array('text-right', 'middle'),
						),

						// Label position - Right
						'right' => array(

							'mask'									=>	'<div class="row">#field</div>',
							'mask_field_label_wrapper'				=>	'<div class="small-#column_width_label columns">#label</div>',
							'mask_field_label'						=>	'<label id="#label_id" for="#id"#attributes>#label</label>',
							'mask_field_wrapper'					=>	'<div class="small-#column_width_field columns">#field</div>',
							'class_field_label'						=>	array('middle'),
						),

						// Masks - Section
						'mask_wrapper' 			=> '#label<div class="row" id="#id" data-id="#data_id">#fields</div>',
						'mask_wrapper_label'	=> '<legend>#label</legend>',

						// Masks - Field
						'mask_single' 			=> '<div class="#class" id="#id" data-id="#data_id" data-type="#type"#attributes>#field</div>',
						'mask_field_label'		=> '<label id="#label_id" for="#id"#attributes>#field</label>',

						// Input group
						'mask_field_input_group'			=>	'#pre_label<div class="row collapse">#field</div>#post_label#help',
						'mask_field_input_group_prepend'	=>	'<div class="small-#col_small_prepend columns"><span class="prefix">#prepend</span></div>',
						'mask_field_input_group_field'		=>	'<div class="small-#col_small_field columns">#field#invalid_feedback</div>',
						'mask_field_input_group_append'		=>	'<div class="small-#col_small_append columns"><span class="postfix">#append</span></div>',
						'col_small_prepend_factor'			=>	0.17,	// 2 columns with a 12 column grid
						'col_small_append_factor'			=>	0.17,	// 2 columns with a 12 column grid

						// Required
						'mask_required_label'	=> ' <small>Required</small>',

						// Help
						'mask_help'				=>	'<p id="#help_id"#attributes>#help#help_append</p>',

						// Invalid feedback
						'mask_invalid_feedback'	=>	'<small id="#invalid_feedback_id" data-form-error-for="#id" class="#invalid_feedback_class"#attributes>#invalid_feedback</small>',

						// Classes - Default
						'class_single'				=> array('columns'),
						'class_field'				=> array(),
						'class_field_label'			=> array(),
						'class_help'				=> array(),
						'class_invalid_feedback'	=> array('error'),
						'class_inline' 				=> array('form-inline'),
						'class_form_validated'		=> array('was-validated'),
						'class_orientation_wrapper'	=> array('row'),
						'class_orientation_row'		=> array('columns'),
						'class_field_button_type'	=> array(

							'secondary'		=>	'secondary',
							'success'		=>	'success',
							'information'	=>	'info',
							'danger'		=>	'alert'
						),
						'class_field_message_type'	=> array(

							'success'		=>	'success',
							'information'	=>	'info',
							'warning'		=>	'warning',
							'danger'		=>	'alert'
						),

						// Attributes
						'attribute_field_match'		=> array('data-equalto' => '#field_match_id'),

						// Classes - Custom by field type
						'field_types'		=> array(

							'checkbox' 	=> array(

								'class_inline' 			=> array(),
								'mask_field'			=> '#pre_label<div#attributes>#datalist</div>#post_label#invalid_feedback#help',
								'mask_group'			=> '<fieldset#disabled>#group_label#group</fieldset>',
								'mask_row_label'		=> '#row_field<label id="#label_row_id" for="#row_id"#attributes>#checkbox_field_label</label>#invalid_feedback',
								'mask_field_label'		=> '<label id="#label_id" for="#id"#attributes>#label</label>'
							),

							'radio' 	=> array(

								'class_inline' 			=> array(),
								'mask_field'			=> '#pre_label<div#attributes>#datalist</div>#post_label#help',
								'mask_group'			=> '<fieldset#disabled>#group_label#group</fieldset>',
								'mask_row_label'		=> '#row_field<label id="#label_row_id" for="#row_id" data-label-required-id="#label_id"#attributes>#radio_field_label</label>#invalid_feedback',
								'mask_field_label'		=> '<label id="#label_id" for="#id"#attributes>#label</label>'
							),

							'spacer'	=> array(

								'mask_field_label'		=>	'',
							),

							'texteditor'	=> array(

								'mask_field_label'		=>	'',
							),


							'submit' 	=> array(

								'mask_field_label'					=> '#label',
								'class_field'						=> array('button'),
								'class_field_full_button'			=> 	array('expand'),
								'class_field_button_type_fallback'	=> 'secondary'
							),

						)
					)
				)
			);
		}
	}