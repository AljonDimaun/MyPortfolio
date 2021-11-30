<?php

	//	Framework config: Bootstrap 5

	class WS_Form_Config_Framework_Bootstrap_5 {

		// Configuration - Frameworks
		public static function get_framework_config() {

			return array(

				'name'							=>	__('Bootstrap 5+', 'ws-form'),

				'default'						=>	false,

				'css_file'						=>	'bootstrap5.css',

				'label_positions'				=>	array('default', 'top', 'left', 'right', 'bottom', 'inside'),

				'minicolors_args'				=>	array(

					'changeDelay' 	=> 200,
					'letterCase' 	=> 'uppercase',
					'theme' 		=> 'bootstrap'
				),

				'columns'					=>	array(

					'column_class'			=>	'col-#id-#size',
					'column_css_selector'	=>	'.col-#id-#size',
					'offset_class'			=>	'offset-#id-#offset',
					'offset_css_selector'	=>	'.offset-#id-#offset'
				),

				'breakpoints'				=>	array(

					// Up to 575px
					25	=>	array(
						'id'					=>	'xs',
						'name'					=>	__('Extra Small', 'ws-form'),
						'column_class'			=>	'col-#size',
						'column_css_selector'	=>	'.col-#size',
						'offset_class'			=>	'offset-#offset',
						'offset_css_selector'	=>	'.offset-#offset',
						'admin_max_width'		=>	575,
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

						'mask_wrapper'		=>	'<ul class="nav nav-tabs mb-3 #class" role="tablist"#attributes>#tabs</ul>',
						'mask_single'		=>	'<li class="nav-item" role="presentation"><button type="button" class="nav-link" #attributes data-bs-target="#href" data-bs-toggle="tab" role="tab">#label</button></li>',
						'activate_js'		=>	"var wsf_bs_tab_el = document.querySelector('#form ul.nav-tabs').getElementsByTagName('li')[#index].getElementsByTagName('button')[0]; var wsf_bs_tab = new bootstrap.Tab(wsf_bs_tab_el); wsf_bs_tab.show();",
						'event_js'			=>	'shown.bs.tab',
						'event_type_js'		=>	'tab',
						'class_disabled'	=>	'disabled',
						'class_active'		=>	'active',
						'selector_href'		=>	'data-bs-target'
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

				'action_js' => array(

					'message'	=>	array(

						'mask_wrapper'		=>	'<div class="alert #mask_wrapper_class">#message</div>',

						'types'	=>	array(

							'success'		=>	array('mask_wrapper_class' => 'alert-success'),
							'information'	=>	array('mask_wrapper_class' => 'alert-info'),
							'warning'		=>	array('mask_wrapper_class' => 'alert-warning'),
							'danger'		=>	array('mask_wrapper_class' => 'alert-danger')
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
					)
				),

				'fields' => array(

					'public' => array(

						// Label position - Left
						'left' => array(

							'mask'							=>	'<div class="row">#field</div>',
							'mask_field_label_wrapper'		=>	'<div class="col-#column_width_label col-form-label text-right">#label</div>',
							'mask_field_wrapper'			=>	'<div class="col-#column_width_field">#field</div>',
						),

						// Label position - Right
						'right' => array(

							'mask'							=>	'<div class="row">#field</div>',
							'mask_field_label_wrapper'		=>	'<div class="col-#column_width_label col-form-label">#label</div>',
							'mask_field_wrapper'			=>	'<div class="col-#column_width_field">#field</div>',
						),

						// Label position - Inside
						'inside' => array(

							'mask_field_wrapper'			=>	'<div class="form-floating">#field</div>',
						),

						// Masks
						'mask_wrapper' 			=> '#label<div class="row" id="#id" data-id="#data_id">#fields</div>',
						'mask_wrapper_label'	=> '<legend>#label</legend>',
						'mask_single' 			=> '<div class="#class" id="#id" data-id="#data_id" data-type="#type"#attributes>#field</div>',

						// Input group
						'mask_field_input_group'			=>	'#pre_label<div class="input-group#css_input_group">#field#post_label#invalid_feedback</div>#help',
						'mask_field_input_group_prepend'	=>	'<span class="input-group-text">#prepend</span>',
						'mask_field_input_group_append'		=>	'<span class="input-group-text wsf-input-group-append">#append</span>',

						// Required
						'mask_required_label'	=> ' <strong class="text-danger">*</strong>',

						// Help
						'mask_help'				=>	'<div id="#help_id" class="#help_class"#attributes>#help#help_append</div>',

						// Invalid feedback
						'mask_invalid_feedback'	=>	'<div id="#invalid_feedback_id" class="#invalid_feedback_class"#attributes>#invalid_feedback</div>',

						// Classes - Default
						'class_single'					=> array('mb-3'),
		//								'class_single_required'			=> array('required'),
						'class_field'					=> array('form-control'),
						'class_field_label'				=> array('form-label'),
						'class_help'					=> array('form-text'),
						'class_invalid_feedback'		=> array('invalid-feedback'),
						'class_inline' 					=> array('form-check-inline'),
						'class_form_validated'			=> array('was-validated'),
						'class_orientation_wrapper'		=> array('row'),
						'class_orientation_row'			=> array(),
						'class_single_vertical_align'	=> array(

							'middle'	=>	'align-self-center',
							'bottom'	=>	'align-self-end'
						),
						'class_field_button_type'	=> array(

							'default'		=>	'btn-secondary',
							'primary'		=>	'btn-primary',
							'secondary'		=>	'btn-secondary',
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

							'select' 	=> array(
								'class_field'			=> array('form-select')
							),

							'checkbox' 	=> array(

								'class_field'			=> array(),
								'class_row'				=> array(),
								'class_row_disabled'	=> array('disabled'),
								'class_row_field'		=> array('form-check-input'),
								'class_row_field_label'	=> array('form-check-label'),
								'mask_field'			=> '#pre_label<div#attributes>#datalist</div>#post_label#invalid_feedback#help',
								'mask_group'			=> '<fieldset#disabled>#group_label#group</fieldset>',
								'mask_row_label'		=> '<div class="form-check">#row_field<label id="#label_row_id" for="#row_id"#attributes>#checkbox_field_label</label>#invalid_feedback</div>',
							),

							'radio' 	=> array(

								'class_field'			=> array(),
								'class_row'				=> array(),
								'class_row_disabled'	=> array('disabled'),
								'class_row_field'		=> array('form-check-input'),
								'class_row_field_label'	=> array('form-check-label'),
								'mask_group'			=> '<fieldset#disabled>#group_label#group</fieldset>',
								'mask_row_label'		=> '<div class="form-check">#row_field<label id="#label_row_id" for="#row_id" data-label-required-id="#label_id"#attributes>#radio_field_label</label>#invalid_feedback</div>'
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