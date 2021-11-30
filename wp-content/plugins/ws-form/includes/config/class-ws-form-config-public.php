<?php

	class WS_Form_Config_Public extends WS_Form_Config {

		// Configuration - Settings - Public
		public static function get_settings_form_public() {

			$settings_form_public = array();

			// Additional language strings for the public
			$language_extra = array(

				'error_min_length'						=>	__('Minimum character count: %s', 'ws-form'),
				'error_max_length'						=>	__('Maximum character count: %s', 'ws-form'),
				'error_min_length_words'				=>	__('Minimum word count: %s', 'ws-form'),
				'error_max_length_words'				=>	__('Maximum word count: %s', 'ws-form'),
				'error_data_grid_source_type'			=>	__('Data grid source type not specified', 'ws-form'),
				'error_data_grid_source_id'				=>	__('Data grid source ID not specified', 'ws-form'),
				'error_data_source_data'				=>	__('Data source data not found', 'ws-form'),
				'error_data_source_columns'				=>	__('Data source columns not found', 'ws-form'),
				'error_data_source_groups'				=>	__('Data source groups not found', 'ws-form'),
				'error_data_source_group_label'			=>	__('Data source group label not found', 'ws-form'),
				'error_data_group_rows'					=>	__('Data source group rows not found', 'ws-form'),
				'error_data_group_label'				=>	__('Data source group label not found', 'ws-form'),
				'error_mask_help'						=>	__('No help mask defined: %s', 'ws-form'),
				'error_mask_invalid_feedback'			=>	__('No invalid feedback mask defined', 'ws-form'),
				'error_api_call_hash'					=>	__('Hash not returned in API call', 'ws-form'),
				'error_api_call_hash_invalid'			=>	__('Invalid hash returned in API call', 'ws-form'),
				'error_api_call_framework_invalid'		=>	__('Framework config not found', 'ws-form'),
				'error_recaptcha_v2_invisible'			=>	__('reCAPTCHA V2 invisible error', 'ws-form'),
				'error_hcaptcha_invisible'				=>	__('hCaptcha invisible error', 'ws-form'),
				'error_timeout_recaptcha'				=>	__('Timeout waiting for reCAPTCHA to load', 'ws-form'),
				'error_timeout_hcaptcha'				=>	__('Timeout waiting for hCaptcha to load', 'ws-form'),
				'error_timeout_analytics_google'		=>	__('Timeout waiting for Google Analytics to load', 'ws-form'),
				'error_timeout_google_maps'				=>	__('Timeout waiting for Google Maps to load', 'ws-form'),
				'error_datetime_default_value'			=>	__('Default date/time value invalid (%s)', 'ws-form'),
				'error_framework_tabs_activate_js'		=>	__('Framework tab activate JS not defined', 'ws-form'),
				'error_form_draft'						=>	__('Form is in draft', 'ws-form'),
				'error_form_future'						=>	__('Form is scheduled', 'ws-form'),
				'error_form_trash'						=>	__('Form is trashed', 'ws-form'),
				'error_calc'							=>	__('Calculation error: %s'),
				'error_framework_plugin'				=>	__('Framework plugin error: %s', 'ws-form'),
				'error_tracking_geo_location'			=>	__('Tracking - Geo location error: %s', 'ws-form'),
				'error_action'							=>	__('Actions - %s', 'ws-form'),
				'error_payment'							=>	__('Payments - %s', 'ws-form'),
				'error_termageddon'						=>	__('Error retrieving Termageddon content', 'ws-form'),
				'error_termageddon_404'					=>	__('Invalid Termageddon key', 'ws-form'),
				'error_js'								=>	__('Syntax error in JavaScript: %s', 'ws-form'),
				'error_section_button_no_section'		=>	__('No section assigned to this button', 'ws-form'),
				'error_section_icon_no_section'			=>	__('No section assigned to these icons', 'ws-form'),
				'error_section_icon_not_in_own_section'	=>	__('Icon %s must be in its own assigned section', 'ws-form'),
				'error_not_supported_video'				=>	__("Sorry, your browser doesn't support embedded videos.", 'ws-form'),
				'error_not_supported_audio'				=>	__("Sorry, your browser doesn't support embedded audio.", 'ws-form'),
				'error_google_map_style_js'				=>	__('Invalid Google Map embedded JSON style declaration', 'ws-form'),
				'error_file_upload'						=>	__('Error uploading file', 'ws-form'),
				'error_submit_hash'						=>	__('Invalid submission hash', 'ws-form'),
				'error_invalid_feedback'				=>	__('Invalid feedback set on field ID: %s', 'ws-form'),

				// Analytics
				'analytics_category'				=> __('Form - %s', 'ws-form'),

				// International telephone input errors
				'iti_number'						=> __('Invalid number', 'ws-form'),
				'iti_country_code'					=> __('Invalid country code', 'ws-form'),
				'iti_short'							=> __('Too short', 'ws-form'),
				'iti_long'							=> __('Too long', 'ws-form'),
			);

			// Add to language array
			$settings_form_public['language'] = array();
			foreach($language_extra as $key => $value) {

				$settings_form_public['language'][$key] = $value;
			}


			// Apply filter
			$settings_form_public = apply_filters('wsf_config_settings_form_public', $settings_form_public);

			return $settings_form_public;
		}

		// Configuration - Get field types public
		public static function get_field_types_public($field_types_filter) {

			$field_types = self::get_field_types_flat(true);

			// Filter by fields found in forms
			if(count($field_types_filter) > 0) {

				$field_types_old = $field_types;
				$field_types = array();

				foreach($field_types_filter as $field_type) {

					if(isset($field_types_old[$field_type])) { $field_types[$field_type] = $field_types_old[$field_type]; }
				}
			}

			// Strip attributes
			$public_attributes_strip = array('label' => false, 'label_default' => false, 'submit_edit' => false, 'conditional' => array('logics_enabled', 'actions_enabled'), 'compatibility_id' => false, 'kb_url' => false, 'fieldsets' => false, 'pro_required' => false);

			foreach($field_types as $key => $field) {

				foreach($public_attributes_strip as $attribute_strip => $attributes_strip_sub) {

					if(isset($field_types[$key][$attribute_strip])) {

						if(is_array($attributes_strip_sub)) {

							foreach($attributes_strip_sub as $attribute_strip_sub) {

								if(isset($field_types[$key][$attribute_strip][$attribute_strip_sub])) {

									unset($field_types[$key][$attribute_strip][$attribute_strip_sub]);
								}
							}

						} else {

							unset($field_types[$key][$attribute_strip]);
						}
					}
				}
			}

			return $field_types;
		}
	}