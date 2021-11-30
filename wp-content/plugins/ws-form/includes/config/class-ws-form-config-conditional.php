<?php

	class WS_Form_Config_Conditional {

		// Configuration - Conditional
		public static function get_settings_conditional() {

			// Conditional
			$conditional =  array(

				// Objects
				'objects' => array(

					// Form
					'form' => array(

						'text' 		=> __('Form', 'ws-form'),
						'logic' => array(

							// Validation
							'validate'		=> array('text' => __('Validated', 'ws-form'), 'values' => false, 'event' => 'wsf-validate', 'group' => 'validate'),
							'validate_not'	=> array('text' => __('Not validated', 'ws-form'), 'values' => false, 'event' => 'wsf-validate', 'group' => 'validate'),

							// Events
							'wsf-rendered'	=> array('text' => __('Rendered', 'ws-form'), 'values' => false, 'event' => 'wsf-rendered', 'group' => 'event'),

							'wsf-submit'	=> array('text' => __('Submit', 'ws-form'), 'values' => false, 'event' => 'wsf-submit', 'group' => 'event'),
							'wsf-save'	=> array('text' => __('Save', 'ws-form'), 'values' => false, 'event' => 'wsf-save', 'group' => 'event'),
							'wsf-submit-save'	=> array('text' => __('Submit or save', 'ws-form'), 'values' => false, 'event' => 'wsf-submit wsf-save', 'group' => 'event'),

							'wsf-submit-complete'	=> array('text' => __('Submit complete', 'ws-form'), 'values' => false, 'event' => 'wsf-submit-complete', 'group' => 'event'),
							'wsf-save-complete'	=> array('text' => __('Save complete', 'ws-form'), 'values' => false, 'event' => 'wsf-save-complete', 'group' => 'event'),
							'wsf-complete'	=> array('text' => __('Submit or save complete', 'ws-form'), 'values' => false, 'event' => 'wsf-complete', 'group' => 'event'),

							'wsf-submit-error'	=> array('text' => __('Submit error', 'ws-form'), 'values' => false, 'event' => 'wsf-submit-error', 'group' => 'event'),
							'wsf-save-error'	=> array('text' => __('Save error', 'ws-form'), 'values' => false, 'event' => 'wsf-save-error', 'group' => 'event'),
							'wsf-error'	=> array('text' => __('Submit or save error', 'ws-form'), 'values' => false, 'event' => 'wsf-error', 'group' => 'event'),

							'wsf-submit-success'	=> array('text' => __('Submit success', 'ws-form'), 'values' => false, 'event' => 'wsf-submit-success', 'group' => 'event'),
							'wsf-save-success'	=> array('text' => __('Save success', 'ws-form'), 'values' => false, 'event' => 'wsf-save-success', 'group' => 'event'),
							'wsf-success'	=> array('text' => __('Submit or save success', 'ws-form'), 'values' => false, 'event' => 'wsf-error', 'group' => 'event'),

							'click'			=> array('text' => __('Clicked', 'ws-form'), 'values' => false, 'event' => 'click', 'group' => 'event'),

							'mousedown'		=> array('text' => __('Mouse down', 'ws-form'), 'values' => false, 'event' => 'mousedown', 'group' => 'event'),
							'mouseup'		=> array('text' => __('Mouse up', 'ws-form'), 'values' => false, 'event' => 'mouseup', 'group' => 'event'),
							'mouseover'		=> array('text' => __('Mouse over', 'ws-form'), 'values' => false, 'event' => 'mouseover', 'group' => 'event'),
							'mouseout'		=> array('text' => __('Mouse out', 'ws-form'), 'values' => false, 'event' => 'mouseout', 'group' => 'event'),

							'touchstart'		=> array('text' => __('Touch start', 'ws-form'), 'values' => false, 'event' => 'touchstart', 'group' => 'event'),
							'touchend'		=> array('text' => __('Touch end', 'ws-form'), 'values' => false, 'event' => 'touchend', 'group' => 'event'),
							'touchmove'		=> array('text' => __('Touch move', 'ws-form'), 'values' => false, 'event' => 'touchmove', 'group' => 'event'),
							'touchcancel'		=> array('text' => __('Touch cancel', 'ws-form'), 'values' => false, 'event' => 'touchcancel', 'case_sensitive' => false, 'group' => 'event'),
						),
						'action' => array(

							'form_submit'			=> array('text' => __('Submit', 'ws-form'), 'values' => false),
							'form_save_validate'	=> array('text' => __('Save if validated', 'ws-form'), 'values' => false),
							'form_save'				=> array('text' => __('Save', 'ws-form'), 'values' => false),
							'form_clear'			=> array('text' => __('Clear', 'ws-form'), 'values' => false),
							'form_reset'			=> array('text' => __('Reset', 'ws-form'), 'values' => false),
							'class_add_wrapper'		=> array('text' => __('Add wrapper class', 'ws-form'), 'values' => true, 'auto_else' => 'class_remove_wrapper'),
							'class_remove_wrapper'	=> array('text' => __('Remove wrapper class', 'ws-form'), 'values' => true, 'auto_else' => 'class_add_wrapper'),
							'javascript'			=> array('text' => __('Run JavaScript', 'ws-form'), 'type' => 'html_editor'),
							'validate_show'			=> array('text' => __('Show validation', 'ws-form'), 'values' => false),
							'validate_hide'			=> array('text' => __('Hide validation', 'ws-form'), 'values' => false),
						)
					),

					// Groups
					'group' => array(

						'text' 		=> __('Tab', 'ws-form'),
						'logic' => array(

							// Validation
							'validate'		=> array('text' => __('Validated', 'ws-form'), 'values' => false, 'event' => 'wsf-validate-silent', 'group' => 'validate'),
							'validate_not'	=> array('text' => __('Not validated', 'ws-form'), 'values' => false, 'event' => 'wsf-validate-silent', 'group' => 'validate'),

							// Events
							'click'			=> array('text' => __('Clicked', 'ws-form'), 'values' => false, 'event' => 'click wsf-click', 'group' => 'event'),
							'mousedown'		=> array('text' => __('Mouse down', 'ws-form'), 'values' => false, 'event' => 'mousedown', 'group' => 'event'),
							'mouseup'		=> array('text' => __('Mouse up', 'ws-form'), 'values' => false, 'event' => 'mouseup', 'group' => 'event'),
							'mouseover'		=> array('text' => __('Mouse over', 'ws-form'), 'values' => false, 'event' => 'mouseover', 'group' => 'event'),
							'mouseout'		=> array('text' => __('Mouse out', 'ws-form'), 'values' => false, 'event' => 'mouseout', 'group' => 'event'),
							'touchstart'		=> array('text' => __('Touch start', 'ws-form'), 'values' => false, 'event' => 'touchstart', 'group' => 'event'),
							'touchend'		=> array('text' => __('Touch end', 'ws-form'), 'values' => false, 'event' => 'touchend', 'group' => 'event'),
							'touchmove'		=> array('text' => __('Touch move', 'ws-form'), 'values' => false, 'event' => 'touchmove', 'group' => 'event'),
							'touchcancel'		=> array('text' => __('Touch cancel', 'ws-form'), 'values' => false, 'event' => 'touchcancel', 'group' => 'event'),
						),
						'action' => array(

							'visibility'			=> array('text' => __('Set visibility', 'ws-form'), 'values' => array(

								array('text' => __('Visible', 'ws-form'), 'value' => '', 'auto_else' => 'off'),
								array('text' => __('Hidden', 'ws-form'), 'value' => 'off', 'auto_else' => ''),

							), 'auto_else' => 'visibility'),

							'click'			=> array('text' => __('Click', 'ws-form'), 'values' => false),

							// Reset / Clear
							'reset'					=> array('text' => __('Reset', 'ws-form'), 'values' => false),
							'clear'					=> array('text' => __('Clear', 'ws-form'), 'values' => false)
						)
					),

					// Section
					'section' => array(

						'text' 		=> __('Section', 'ws-form'),
						'logic' => array(

							// Validation
							'validate'		=> array('text' => __('Validated', 'ws-form'), 'values' => false, 'event' => 'wsf-validate-silent', 'group' => 'validate'),
							'validate_not'	=> array('text' => __('Not validated', 'ws-form'), 'values' => false, 'event' => 'wsf-validate-silent', 'group' => 'validate'),

							// Section repeatable count
							'r=='					=> array('text' => __('Row count equals', 'ws-form'), 'type' => 'number', 'event' => 'wsf-section-repeatable', 'case_sensitive' => false, 'group' => 'rs'),
							'r!='					=> array('text' => __('Row count does not equal', 'ws-form'), 'type' => 'number', 'event' => 'wsf-section-repeatable', 'case_sensitive' => false, 'group' => 'rs'),
							'r>'					=> array('text' => __('Row count greater than', 'ws-form'), 'type' => 'number', 'event' => 'wsf-section-repeatable', 'case_sensitive' => false, 'group' => 'rs'),
							'r<'					=> array('text' => __('Row count less than', 'ws-form'), 'type' => 'number', 'event' => 'wsf-section-repeatable', 'case_sensitive' => false, 'group' => 'rs'),
							'r>='					=> array('text' => __('Row count greater than or equal to', 'ws-form'), 'type' => 'number', 'event' => 'wsf-section-repeatable', 'case_sensitive' => false, 'group' => 'rs'),
							'r<='					=> array('text' => __('Row count less than or equal to', 'ws-form'), 'type' => 'number', 'event' => 'wsf-section-repeatable', 'case_sensitive' => false, 'group' => 'rs'),
							'section_repeatable'	=> array('text' => __('Row count changes', 'ws-form'), 'values' => false, 'event' => 'wsf-section-repeatable', 'group' => 'rs'),
						),
						'action' => array(

							'visibility'			=> array('text' => __('Set visibility', 'ws-form'), 'values' => array(

								array('text' => __('Visible', 'ws-form'), 'value' => '', 'auto_else' => 'off'),
								array('text' => __('Hidden', 'ws-form'), 'value' => 'off', 'auto_else' => ''),

							), 'auto_else' => 'visibility'),

							'disabled'				=> array('text' => __('Set disabled', 'ws-form'), 'values' => array(

								array('text' => __('Not disabled', 'ws-form'), 'value' => '', 'auto_else' => 'on'),
								array('text' => __('Disabled', 'ws-form'), 'value' => 'on', 'auto_else' => '')

							), 'auto_else' => 'disabled'),

							'class_add_wrapper'		=> array('text' => __('Add wrapper class', 'ws-form'), 		'values' => true, 'auto_else' => 'class_remove_wrapper'),
							'class_remove_wrapper'	=> array('text' => __('Remove wrapper class', 'ws-form'), 	'values' => true, 'auto_else' => 'class_add_wrapper'),
							'set_row_count'			=> array('text' => __('Set row count', 'ws-form'), 	'values' => true),

							// Reset / Clear
							'reset'					=> array('text' => __('Reset', 'ws-form'), 'values' => false),
							'clear'					=> array('text' => __('Clear', 'ws-form'), 'values' => false)
						)
					),

					// Field
					'field' => array(

						'text'		=> __('Field', 'ws-form'),
						'logic' => array(

							// Numeric
							'=='					=> array('text' => __('Equals', 'ws-form'), 'type' => 'number', 'case_sensitive' => false, 'group' => 'value'),
							'!='					=> array('text' => __('Does not equal', 'ws-form'), 'type' => 'number', 'case_sensitive' => false, 'group' => 'value'),
							'>'						=> array('text' => __('Greater than', 'ws-form'), 'type' => 'number', 'case_sensitive' => false, 'group' => 'value'),
							'<'						=> array('text' => __('Less than', 'ws-form'), 'type' => 'number', 'case_sensitive' => false, 'group' => 'value'),
							'>='					=> array('text' => __('Greater than or equal to', 'ws-form'), 'type' => 'number', 'case_sensitive' => false, 'group' => 'value'),
							'<='					=> array('text' => __('Less than or equal to', 'ws-form'), 'type' => 'number', 'case_sensitive' => false, 'group' => 'value'),

							// Strings
							'equals'				=> array('text' => __('Equals', 'ws-form'), 'group' => 'value'),
							'equals_not' 			=> array('text' => __('Does not equal', 'ws-form'), 'group' => 'value'),
							'contains'				=> array('text' => __('Contains', 'ws-form'), 'group' => 'value'),
							'contains_not'			=> array('text' => __('Does not contain', 'ws-form'), 'group' => 'value'),
							'starts'				=> array('text' => __('Starts with', 'ws-form'), 'group' => 'value'),
							'starts_not'			=> array('text' => __('Does not start with', 'ws-form'), 'group' => 'value'),
							'ends'					=> array('text' => __('Ends with', 'ws-form'), 'group' => 'value'),
							'ends_not'				=> array('text' => __('Does not end with', 'ws-form'), 'group' => 'value'),
							'blank'					=> array('text' => __('Is blank', 'ws-form'), 'values' => false, 'case_sensitive' => false, 'group' => 'value'),
							'blank_not'				=> array('text' => __('Is not blank', 'ws-form'), 'values' => false, 'case_sensitive' => false, 'group' => 'value'),
							'regex'					=> array('text' => __('Matches JS regex', 'ws-form'), 'case_sensitive' => false, 'group' => 'value'),
							'regex_not'				=> array('text' => __('Does not match JS regex', 'ws-form'), 'case_sensitive' => false, 'group' => 'value'),

							// Select
							'selected'				=> array('text' => __('Row(s) selected', 'ws-form'), 'values' => false, 'rows' => true, 'case_sensitive' => false, 'data_source_exclude' => true, 'group' => 'value', 'multiple' => true),
							'selected_not'			=> array('text' => __('Row(s) not selected', 'ws-form'), 'values' => false, 'rows' => true, 'case_sensitive' => false, 'data_source_exclude' => true, 'group' => 'value', 'multiple' => true),
							'selected_any'			=> array('text' => __('Any row selected', 'ws-form'), 'values' => false, 'group' => 'value'),
							'selected_any_not'			=> array('text' => __('No row selected', 'ws-form'), 'values' => false, 'group' => 'value'),
							'selected_value_equals'	=> array('text' => __('Selected value equals', 'ws-form'), 'group' => 'value'),
							'selected_value_equals_not'	=> array('text' => __('Select value does not equal', 'ws-form'), 'group' => 'value'),
							'rs=='					=> array('text' => __('Selected count equals', 'ws-form'), 'type' => 'number', 'case_sensitive' => false, 'group' => 'value'),
							'rs!='					=> array('text' => __('Selected count does not equal', 'ws-form'), 'type' => 'number', 'case_sensitive' => false, 'group' => 'value'),
							'rs>'					=> array('text' => __('Selected count greater than', 'ws-form'), 'type' => 'number', 'case_sensitive' => false, 'group' => 'value'),
							'rs<'					=> array('text' => __('Selected count less than', 'ws-form'), 'type' => 'number', 'case_sensitive' => false, 'group' => 'value'),

							// Checkbox / Radio
							'checked'				=> array('text' => __('Row(s) checked', 'ws-form'), 'values' => false, 'rows' => true, 'case_sensitive' => false, 'data_source_exclude' => true, 'group' => 'value', 'multiple' => true),
							'checked_not'			=> array('text' => __('Row(s) not checked', 'ws-form'), 'values' => false, 'rows' => true, 'case_sensitive' => false, 'data_source_exclude' => true, 'group' => 'value', 'multiple' => true),
							'checked_any'			=> array('text' => __('Any row checked', 'ws-form'), 'values' => false, 'group' => 'value'),
							'checked_any_not'		=> array('text' => __('No row checked', 'ws-form'), 'values' => false, 'group' => 'value'),
							'rc=='					=> array('text' => __('Checked count equals', 'ws-form'), 'type' => 'number', 'case_sensitive' => false, 'group' => 'value'),
							'rc!='					=> array('text' => __('Checked count does not equal', 'ws-form'), 'type' => 'number', 'case_sensitive' => false, 'group' => 'value'),
							'rc>'					=> array('text' => __('Checked count greater than', 'ws-form'), 'type' => 'number', 'case_sensitive' => false, 'group' => 'value'),
							'rc<'					=> array('text' => __('Checked count less than', 'ws-form'), 'type' => 'number', 'case_sensitive' => false, 'group' => 'value'),
							'checked_value_equals'	=> array('text' => __('Checked value equals', 'ws-form'), 'group' => 'value'),
							'checked_value_equals_not'	=> array('text' => __('Checked value does not equal', 'ws-form'), 'group' => 'value'),

							// Email
							'regex_email'			=> array('text' => __('Is valid email address', 'ws-form'), 'values' => false, 'case_sensitive' => false, 'group' => 'value'),
							'regex_email_not'		=> array('text' => __('Is not a valid email address', 'ws-form'), 'values' => false, 'case_sensitive' => false, 'group' => 'value'),

							// URL
							'regex_url'				=> array('text' => __('Is valid URL', 'ws-form'), 'values' => false, 'case_sensitive' => false, 'group' => 'value'),
							'regex_url_not'			=> array('text' => __('Is not a valid URL', 'ws-form'), 'values' => false, 'case_sensitive' => false, 'group' => 'value'),

							// Match field
							'field_match' 			=> array('text' => __('Matches field', 'ws-form'), 'values' => 'fields', 'group' => 'value'),
							'field_match_not'		=> array('text' => __('Does not match field', 'ws-form'), 'values' => 'fields', 'group' => 'value'),

							// Date/Time
							'd=='					=> array('text' => __('Equals', 'ws-form'), 'type' => 'datetime', 'case_sensitive' => false, 'group' => 'value'),
							'd!='					=> array('text' => __('Does not equal', 'ws-form'), 'type' => 'datetime', 'case_sensitive' => false, 'group' => 'value'),
							'd>'					=> array('text' => __('Greater than', 'ws-form'), 'type' => 'datetime', 'case_sensitive' => false, 'group' => 'value'),
							'd<'					=> array('text' => __('Less than', 'ws-form'), 'type' => 'datetime', 'case_sensitive' => false, 'group' => 'value'),
							'd>='					=> array('text' => __('Greater than or equal to', 'ws-form'), 'type' => 'datetime', 'case_sensitive' => false, 'group' => 'value'),
							'd<='					=> array('text' => __('Less than or equal to', 'ws-form'), 'type' => 'datetime', 'case_sensitive' => false, 'group' => 'value'),

							// Color
							'c==' 					=> array('text' => __('Equals (#RRGGBB)', 'ws-form'), 'type' => 'text', 'case_sensitive' => false, 'group' => 'value'),
							'c!=' 					=> array('text' => __('Does not equal (#RRGGBB)', 'ws-form'), 'type' => 'text', 'case_sensitive' => false, 'group' => 'value'),
							'ch>'					=> array('text' => __('Hue greater than', 'ws-form'), 'type' =>	'number', 'min' => 0, 'max' => 360, 'unit' => '&#176;', 'case_sensitive' => false, 'group' => 'value'),
							'ch<' 					=> array('text' => __('Hue less than', 'ws-form'), 'type' => 'number', 'min' => 0, 'max' => 360, 'unit' => '&#176;', 'case_sensitive' => false, 'group' => 'value'),
							'cs>'					=> array('text' => __('Saturation greater than', 'ws-form'), 'type' => 'number', 'min' => 0, 'max' => 100, 'unit' => '%', 'case_sensitive' => false, 'group' => 'value'),
							'cs<'					=> array('text' => __('Saturation less than', 'ws-form'), 'type' => 'number', 'min' => 0, 'max' => 100, 'unit' => '%', 'case_sensitive' => false, 'group' => 'value'),
							'cl>'					=> array('text' => __('Lightness greater than', 'ws-form'), 'type' => 'number', 'min' => 0, 'max' => 100, 'unit' => '%', 'case_sensitive' => false, 'group' => 'value'),
							'cl<'					=> array('text' => __('Lightness less than', 'ws-form'), 'type' => 'number', 'min' => 0, 'max' => 100, 'unit' => '%', 'case_sensitive' => false, 'group' => 'value'),

							// File
							'file' 					=> array('text' => __('File selected', 'ws-form'), 'values' => false, 'case_sensitive' => false, 'group' => 'value'),
							'file_not'				=> array('text' => __('No file selected', 'ws-form'), 'values' => false, 'case_sensitive' => false, 'group' => 'value'),
							'f==' 					=> array('text' => __('File count equals', 'ws-form'), 'type' => 'number', 'case_sensitive' => false, 'group' => 'value'),
							'f!=' 					=> array('text' => __('File count does not equal', 'ws-form'), 'type' => 'number', 'case_sensitive' => false, 'group' => 'value'),
							'f>' 					=> array('text' => __('File count greater than', 'ws-form'), 'type' => 'number', 'case_sensitive' => false, 'group' => 'value'),
							'f<' 					=> array('text' => __('File count less than', 'ws-form'), 'type' =>	'number', 'case_sensitive' => false, 'group' => 'value'),

							// Character and word count
							'cc==' 					=> array('text' => __('Character count equals', 'ws-form'), 'type' => 'number', 'case_sensitive' => false, 'group' => 'value'),
							'cc!=' 					=> array('text' => __('Character count does not equal', 'ws-form'), 'type' => 'number', 'case_sensitive' => false, 'group' => 'value'),
							'cc>' 					=> array('text' => __('Character count greater than', 'ws-form'), 'type' => 'number', 'case_sensitive' => false, 'group' => 'value'),
							'cc<' 					=> array('text' => __('Character count less than', 'ws-form'), 'type' => 'number', 'case_sensitive' => false, 'group' => 'value'),
							'cw==' 					=> array('text' => __('Word count equals', 'ws-form'), 'type' => 'number', 'case_sensitive' => false, 'group' => 'value'),
							'cw!=' 					=> array('text' => __('Word count does not equal', 'ws-form'), 'type' => 'number', 'case_sensitive' => false, 'group' => 'value'),
							'cw>' 					=> array('text' => __('Word count greater than', 'ws-form'), 'type' => 'number', 'case_sensitive' => false, 'group' => 'value'),
							'cw<' 					=> array('text' => __('Word count less than', 'ws-form'), 'type' => 'number', 'case_sensitive' => false, 'group' => 'value'),

							// Validation
							'validate'				=> array('text' => __('Is validated', 'ws-form'), 'values' => false, 'event' => 'wsf-validate-silent', 'group' => 'validate'),
							'validate_not'			=> array('text' => __('Is not validated', 'ws-form'), 'values' => false, 'event' => 'wsf-validate-silent', 'group' => 'validate'),

							// reCAPTCHA
							'recaptcha' 			=> array('text' => __('reCAPTCHA valid', 'ws-form'), 'values' => false, 'group' => 'validate'),
							'recaptcha_not' 	=> array('text' => __('reCAPTCHA invalid', 'ws-form'), 'values' => false, 'group' => 'validate'),

							// hCaptcha
							'hcaptcha' 			=> array('text' => __('hCaptcha valid', 'ws-form'), 'values' => false, 'group' => 'validate'),
							'hcaptcha_not' 	=> array('text' => __('hCaptcha invalid', 'ws-form'), 'values' => false, 'group' => 'validate'),

							// Signature
							'signature' 			=> array('text' => __('Signed', 'ws-form'), 'values' => false, 'group' => 'validate'),
							'signature_not' 		=> array('text' => __('Unsigned', 'ws-form'), 'values' => false, 'group' => 'validate'),

							// Events
							'click'			=> array('text' => __('Clicked', 'ws-form'), 'values' => false, 'event' => 'click', 'group' => 'event'),
							'mousedown'		=> array('text' => __('Mouse down', 'ws-form'), 'values' => false, 'event' => 'mousedown', 'group' => 'event'),
							'mouseup'		=> array('text' => __('Mouse up', 'ws-form'), 'values' => false, 'event' => 'mouseup', 'group' => 'event'),
							'mouseover'		=> array('text' => __('Mouse over', 'ws-form'), 'values' => false, 'event' => 'mouseover', 'group' => 'event'),
							'mouseout'		=> array('text' => __('Mouse out', 'ws-form'), 'values' => false, 'event' => 'mouseout', 'group' => 'event'),
							'touchstart'		=> array('text' => __('Touch start', 'ws-form'), 'values' => false, 'event' => 'touchstart', 'group' => 'event'),
							'touchend'		=> array('text' => __('Touch end', 'ws-form'), 'values' => false, 'event' => 'touchend', 'group' => 'event'),
							'touchmove'		=> array('text' => __('Touch move', 'ws-form'), 'values' => false, 'event' => 'touchmove', 'group' => 'event'),
							'touchcancel'		=> array('text' => __('Touch cancel', 'ws-form'), 'values' => false, 'event' => 'touchcancel', 'group' => 'event'),

							'focus'					=> array('text' => __('On focus', 'ws-form'), 'values' => false, 'event' => 'focus', 'group' => 'event'),
							'blur'					=> array('text' => __('On blur', 'ws-form'), 'values' => false, 'event' => 'blur', 'group' => 'event'),
							'change'				=> array('text' => __('On change', 'ws-form'), 'values' => false, 'event' => 'change', 'group' => 'event'),
							'input'					=> array('text' => __('On input', 'ws-form'), 'values' => false, 'event' => 'input', 'group' => 'event'),
							'change_input'			=> array('text' => __('On change or input', 'ws-form'), 'values' => false, 'event' => 'change input', 'group' => 'event'),
							'keyup'					=> array('text' => __('On key up', 'ws-form'), 'values' => false, 'event' => 'keyup', 'group' => 'event'),
							'keydown'				=> array('text' => __('On key down', 'ws-form'), 'values' => false, 'event' => 'keydown', 'group' => 'event'),

						),
						'action' => array(

							// General
							'visibility'		=> array('text' => __('Set visibility', 'ws-form'), 'values' => array(

								array('text' => __('Visible', 'ws-form'), 'value' => '', 'auto_else' => 'off'),
								array('text' => __('Hidden', 'ws-form'), 'value' => 'off', 'auto_else' => '')

							), 'auto_else' => 'visibility'),

							'required'			=> array('text' => __('Set required', 'ws-form'), 'values' => array(

								array('text' => __('Not required', 'ws-form'), 'value' => '', 'auto_else' => 'on'),
								array('text' => __('Required', 'ws-form'), 'value' => 'on', 'auto_else' => '')

							), 'auto_else' => 'required'),

							'focus'				=> array('text' => __('Focus', 'ws-form'), 'values' => false),
							'blur'				=> array('text' => __('Blur', 'ws-form'), 'values' => false),
							'value'				=> array('text' => __('Set value', 'ws-form'), 'values' => true, 'auto_else' => 'value'),

							'disabled'			=> array('text' => __('Set disabled', 'ws-form'), 'values' => array(

								array('text' => __('Not disabled', 'ws-form'), 'value' => '', 'auto_else' => 'on'),
								array('text' => __('Disabled', 'ws-form'), 'value' => 'on', 'auto_else' => '')

							), 'auto_else' => 'disabled'),

							'readonly'			=> array('text' => __('Set read only', 'ws-form'), 'values' => array(

								array('text' => __('Not read only', 'ws-form'), 'value' => '', 'auto_else' => 'on'),
								array('text' => __('Read only', 'ws-form'), 'value' => 'on', 'auto_else' => '')

							), 'auto_else' => 'readonly'),

							// Values by field type
							'value_datetime'	=> array('text' => __('Set value', 'ws-form'), 'values' => true, 'auto_else' => 'value_datetime', 'type' => 'datetime'),
							'value_number'		=> array('text' => __('Set value', 'ws-form'), 'values' => true, 'auto_else' => 'value_number', 'type' => 'number'),
							'value_range'		=> array('text' => __('Set value', 'ws-form'), 'values' => true, 'auto_else' => 'value_range', 'type' => 'range'),
							'value_rating'		=> array('text' => __('Set value', 'ws-form'), 'values' => true, 'auto_else' => 'value_rating', 'type' => 'rating'),
							'value_color'		=> array('text' => __('Set color', 'ws-form'), 'values' => true, 'auto_else' => 'value_color', 'type' => 'color'),
							'value_email'		=> array('text' => __('Set value', 'ws-form'), 'values' => true, 'auto_else' => 'value_email', 'type' => 'email'),
							'value_tel'			=> array('text' => __('Set value', 'ws-form'), 'values' => true, 'auto_else' => 'value_tel', 'type' => 'tel'),
							'value_url'			=> array('text' => __('Set value', 'ws-form'), 'values' => true, 'auto_else' => 'value_url', 'type' => 'url'),
							'value_textarea'	=> array('text' => __('Set value', 'ws-form'), 'values' => true, 'auto_else' => 'value_textarea', 'type' => 'textarea'),

							// Validation
							'set_custom_validity'	=> array('text' => __('Set custom validity', 'ws-form'), 'values' => true, 'auto_else' => 'set_custom_validity'),

							// Data grid rows
							'value_row_select'			=> array('text' => __('Select row', 'ws-form'), 'values' => false, 'value_row_ids' => true, 'auto_else' => 'value_row_deselect', 'data_source_exclude' => true),
							'value_row_deselect'		=> array('text' => __('Deselect row', 'ws-form'), 'values' => false, 'value_row_ids' => true, 'auto_else' => 'value_row_select', 'data_source_exclude' => true),

							'value_row_select_value'	=> array('text' => __('Select row with value', 'ws-form'), 'auto_else' => 'value_row_deselect_value', 'auto_else_copy' => true),
							'value_row_deselect_value'	=> array('text' => __('Deselect row with value', 'ws-form'), 'auto_else' => 'value_row_select_value', 'auto_else_copy' => true),

							'value_row_reset'			=> array('text' => __('Clear', 'ws-form'), 'values' => false),

							'value_row_check'			=> array('text' => __('Check row', 'ws-form'), 'values' => false, 'value_row_ids' => true, 'auto_else' => 'value_row_uncheck', 'data_source_exclude' => true),
							'value_row_uncheck'			=> array('text' => __('Uncheck row', 'ws-form'), 'values' => false, 'value_row_ids' => true, 'auto_else' => 'value_row_check', 'data_source_exclude' => true),

							'value_row_check_value'		=> array('text' => __('Check row with value', 'ws-form'), 'auto_else' => 'value_row_uncheck_value', 'auto_else_copy' => true),
							'value_row_uncheck_value'	=> array('text' => __('Uncheck row with value', 'ws-form'), 'auto_else' => 'value_row_check_value', 'auto_else_copy' => true),

							'value_row_required'		=> array('text' => __('Set row required', 'ws-form'), 'values' => false, 'value_row_ids' => true, 'auto_else' => 'value_row_not_required', 'data_source_exclude' => true),
							'value_row_not_required'	=> array('text' => __('Set row not required', 'ws-form'), 'values' => false, 'value_row_ids' => true, 'auto_else' => 'value_row_required', 'data_source_exclude' => true),

							'value_row_disabled'		=> array('text' => __('Set row disabled', 'ws-form'), 'values' => false, 'value_row_ids' => true, 'auto_else' => 'value_row_not_disabled', 'data_source_exclude' => true),
							'value_row_not_disabled'	=> array('text' => __('Set row not disabled', 'ws-form'), 'values' => false, 'value_row_ids' => true, 'auto_else' => 'value_row_disabled', 'data_source_exclude' => true),

							'value_row_class_add'		=> array('text' => __('Add row class', 'ws-form'), 'values' => true, 'value_row_ids' => true, 'auto_else' => 'value_row_class_remove', 'data_source_exclude' => true),
							'value_row_class_remove'	=> array('text' => __('Remove row class', 'ws-form'), 'values' => true, 'value_row_ids' => true, 'auto_else' => 'value_row_class_add', 'data_source_exclude' => true),

							'value_row_visible'			=> array('text' => __('Set row visible', 'ws-form'), 'values' => false, 'value_row_ids' => true, 'auto_else' => 'value_row_not_visible', 'data_source_exclude' => true),
							'value_row_not_visible'		=> array('text' => __('Set row not visible', 'ws-form'), 'values' => false, 'value_row_ids' => true, 'auto_else' => 'value_row_visible', 'data_source_exclude' => true),

							'min'		=> array('text' => __('Set minimum', 'ws-form'), 'values' => true),
							'max'		=> array('text' => __('Set maximum', 'ws-form'), 'values' => true),
							'step'		=> array('text' => __('Set step', 'ws-form'), 'values' => true),

							'low'		=> array('text' => __('Set low', 'ws-form'), 'values' => true),
							'high'		=> array('text' => __('Set high', 'ws-form'), 'values' => true),
							'optimum'	=> array('text' => __('Set optimum', 'ws-form'), 'values' => true),

							'min_int'	=> array('text' => __('Set minimum', 'ws-form'), 'values' => true),
							'max_int'	=> array('text' => __('Set maximum', 'ws-form'), 'values' => true),
							'step_int'	=> array('text' => __('Set step', 'ws-form'), 'values' => true),

							'ecommerce_price_min'	=> array('text' => __('Set minimum', 'ws-form'), 'values' => true),
							'ecommerce_price_max'	=> array('text' => __('Set maximum', 'ws-form'), 'values' => true),

							'select_min'		=> array('text' => __('Set minimum selected', 'ws-form'), 'values' => true),
							'select_max'		=> array('text' => __('Set maximum selected', 'ws-form'), 'values' => true),

							'checkbox_min'		=> array('text' => __('Set minimum checked', 'ws-form'), 'values' => true),
							'checkbox_max'		=> array('text' => __('Set maximum checked', 'ws-form'), 'values' => true),

							'value_row_focus'			=> array('text' => __('Focus row', 'ws-form'), 'values' => false, 'value_row_ids' => true, 'data_source_exclude' => true),

							'value_row_set_custom_validity'		=> array('text' => __('Set row custom validity', 'ws-form'), 'values' => true, 'value_row_ids' => true, 'auto_else' => 'value_row_set_custom_validity', 'data_source_exclude' => true),

							'html'					=> array('text' => __('Set HTML', 'ws-form'), 'type' => 'html_editor'),
							'text_editor'			=> array('text' => __('Set content', 'ws-form'), 'type' => 'text_editor'),

							// Reset / Clear
							'reset'					=> array('text' => __('Reset', 'ws-form'), 'values' => false),
							'clear'					=> array('text' => __('Clear', 'ws-form'), 'values' => false),

							// Buttons
							'button_html'			=> array('text' => __('Set label', 'ws-form')),
							'click'					=> array('text' => __('Click', 'ws-form'), 'values' => false),

							// Classes
							'class_add_wrapper'		=> array('text' => __('Add wrapper class', 'ws-form'), 'values' => true, 'auto_else' => 'class_remove_wrapper', 'auto_else_copy' => true),
							'class_remove_wrapper'	=> array('text' => __('Remove wrapper class', 'ws-form'), 'values' => true, 'auto_else' => 'class_add_wrapper', 'auto_else_copy' => true),
							'class_add_field'		=> array('text' => __('Add field class', 'ws-form'), 'values' => true, 'auto_else' => 'class_remove_field', 'auto_else_copy' => true),
							'class_remove_field'	=> array('text' => __('Remove field class', 'ws-form'), 'values' => true, 'auto_else' => 'class_add_field', 'auto_else_copy' => true),

							// File
							'reset_file'			=> array('text' => __('Reset', 'ws-form'), 	'values' => false),

							// Signature
							'reset_signature'		=> array('text' => __('Reset', 'ws-form'), 	'values' => false),
							'required_signature'	=> array('text' => __('Set required', 'ws-form'), 'values' => array(

								array('text' => __('Not required', 'ws-form'), 'value' => '', 'auto_else' => 'on'),
								array('text' => __('Required', 'ws-form'), 'value' => 'on', 'auto_else' => '')

							), 'auto_else' => 'required_signature')
						)
					),

					// Action
					'action' => array(

						'text'		=> __('Action', 'ws-form'),
						'logic' 	=> array(),
						'action'	=> array(

							'action_run' 					=> array('text' => __('Run immediately', 'ws-form'), 'values' => false),
							'action_run_on_submit' 			=> array('text' => __('Run when form submitted', 'ws-form'), 'values' => false, 'auto_else' => 'action_do_not_run_on_submit'),
							'action_do_not_run_on_submit' 	=> array('text' => __('Do not run when form submitted', 'ws-form'), 'values' => false, 'auto_else' => 'action_run_submit'),
							'action_run_on_save' 			=> array('text' => __('Run when form saved', 'ws-form'), 'values' => false, 'auto_else' => 'action_do_not_run_on_save'),
							'action_do_not_run_on_save' 	=> array('text' => __('Do not run when form saved', 'ws-form'), 'values' => false, 'auto_else' => 'action_run_save')
						)
					),

					// Submission
					'submit' => array(

						'text' 		=> __('Submission', 'ws-form'),
						'logic' => array(

							// Validation
							'token_validated'		=> array('text' => __('Email Validated', 'ws-form'), 'values' => false, 'event' => 'wsf-rendered', 'group' => 'validate'),
							'token_validated_not'	=> array('text' => __('Email Not validated', 'ws-form'), 'values' => false, 'event' => 'wsf-rendered', 'group' => 'validate')
						),
						'action' => array()
					),

					// User
					'user' => array(

						'text' 		=> __('User', 'ws-form'),
						'logic' => array(

							'user_logged_in'		=> array('text' => __('Logged In', 'ws-form'), 'values' => false, 'event' => 'wsf-rendered', 'group' => 'status'),
							'user_logged_in_not'	=> array('text' => __('Logged Out', 'ws-form'), 'values' => false, 'event' => 'wsf-rendered', 'group' => 'status'),
							'user_role'	=> array('text' => __('Has Role', 'ws-form'), 'values' => array(

								array('value' => '', 'text' => __('Select...', 'ws-form')),

							), 'event' => 'wsf-rendered', 'group' => 'status'),
							'user_role_not'	=> array('text' => __('Does Not Have Role', 'ws-form'), 'values' => array(

								array('value' => '', 'text' => __('Select...', 'ws-form')),

							), 'event' => 'wsf-rendered', 'group' => 'status'),
							'user_capability'	=> array('text' => __('Has Capability', 'ws-form'), 'values' => array(

								array('value' => '', 'text' => __('Select...', 'ws-form')),

							), 'event' => 'wsf-rendered', 'group' => 'status'),
							'user_capability_not'	=> array('text' => __('Does Not Have Capability', 'ws-form'), 'values' => array(

								array('value' => '', 'text' => __('Select...', 'ws-form')),

							), 'event' => 'wsf-rendered', 'group' => 'status'),
						),
						'action' => array()
					)
				),

				// Logic previous
				'logic_previous' => array(

					'||' => array('text' => __('OR', 'ws-form')),
					'&&' => array('text' => __('AND', 'ws-form')),
				),

				// Logic groups
				'logic_group' => array(

					'event' 	=> array('label' => __('Event', 'ws-form'), 'auto_else_disabled' => true),
					'rs' 		=> array('label' => __('Repeatable Section', 'ws-form')),
					'value' 	=> array('label' => __('Value', 'ws-form')),
					'validate' 	=> array('label' => __('Validation', 'ws-form')),
					'status' 	=> array('label' => __('Status', 'ws-form')),
				)
			);

			// User roles
			$capabilities = array();
			if (!function_exists('get_editable_roles')) {

				require_once(ABSPATH . '/wp-admin/includes/user.php');
			}
			$roles = get_editable_roles();
			uasort($roles, function($role_a, $role_b) {

				return ($role_a['name'] == $role_b['name']) ? 0 : (($role_a['name'] < $role_b['name']) ? -1 : 1);
			});
			foreach ($roles as $role => $role_config) {

				$conditional['objects']['user']['logic']['user_role']['values'][] = array('value' => esc_attr($role), 'text' => esc_html(translate_user_role($role_config['name'])));
				$conditional['objects']['user']['logic']['user_role_not']['values'][] = array('value' => esc_attr($role), 'text' => esc_html(translate_user_role($role_config['name'])));

				$capabilities = array_merge($capabilities, array_keys($role_config['capabilities']));
			}

			// User capabilities
			$capabilities = array_unique($capabilities);
			sort($capabilities);
			foreach ($capabilities as $capability) {

				$conditional['objects']['user']['logic']['user_capability']['values'][] = array('value' => esc_attr($capability), 'text' => esc_html($capability));
				$conditional['objects']['user']['logic']['user_capability_not']['values'][] = array('value' => esc_attr($capability), 'text' => esc_html($capability));
			}

			return $conditional;
		}
	}
