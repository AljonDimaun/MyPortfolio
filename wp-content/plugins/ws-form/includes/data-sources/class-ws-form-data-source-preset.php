<?php

	class WS_Form_Data_Source_Preset extends WS_Form_Data_Source {

		public $id = 'preset';
		public $pro_required = false;
		public $label;
		public $label_retrieving;
		public $records_per_page = 1000;

		public function __construct() {

			// Set label
			$this->label = __('Preset', 'ws-form');

			// Set label retrieving
			$this->label_retrieving = __('Retrieving Preset...', 'ws-form');

			// Register action
			parent::register($this);

			// Register config filters
			add_filter('wsf_config_meta_keys', array($this, 'config_meta_keys'), 10, 2);

			// Register API endpoint
			add_action('rest_api_init', array($this, 'rest_api_init'), 10, 0);

			// Records per page
			$this->records_per_page = apply_filters('wsf_data_source_' . $this->id . '_records_per_age', $this->records_per_page);
		}

		// Get
		public function get($form_object, $field_id, $page, $meta_key, $meta_value, $no_paging = false, $api_request = false) {

			// If this is not an API request, return data to avoid unnecessary calls to WS Form CDN
			if(!$api_request) { 

				// Return data
				return array('error' => false, 'error_message' => '', 'meta_value' => $meta_value);
			}

			// Check meta key
			if(empty($meta_key)) { return self::error(__('No meta key specified', 'ws-form'), $field_id, $this, $api_request); }

			// Get meta key config
			$meta_keys = WS_Form_Config::get_meta_keys();
			if(!isset($meta_keys[$meta_key])) { return self::error(__('Unknown meta key', 'ws-form'), $field_id, $this, $api_request); }
			$meta_key_config = $meta_keys[$meta_key];

			// Check meta value
			if(
				!is_object($meta_value) ||
				!isset($meta_value->columns) ||
				!isset($meta_value->groups) ||
				!isset($meta_value->groups[0])
			) {

				if(!isset($meta_key_config['default'])) { return self::error(__('No default value', 'ws-form'), $field_id, $this, $api_request); }

				// If meta_value is invalid, create one from default
				$meta_value = json_decode(json_encode($meta_key_config['default']));
			}

			// Get preset
			$preset_id = $this->data_source_preset_preset_id;
			if($preset_id === '') {

				// Return data
				return array('error' => false, 'error_message' => '', 'meta_value' => $meta_value, 'max_num_pages' => 0, 'meta_keys' => array());
			}

			// Retrieve presets
			$presets = self::get_presets();
			if($presets === false) {

				return self::error(__('Unable to obtain presets', 'ws-form'), $field_id, $this, $api_request);
			}

			if(!isset($presets[$preset_id])) {

				return self::error(__('Invalid preset', 'ws-form'), $field_id, $this, $api_request);
			}

			// Get preset
			$preset = $presets[$preset_id];

			// Get URL
			$url = $preset->url;

			// Get CSV
			try {

				$csv = @file_get_contents($url);
				if($csv === false) {

					return self::error(sprintf(__('Error retrieving CSV file: %s', 'ws-form'), $url), $field_id, $this, $api_request);
				}

			} catch (Exception $e) {

				return self::error($e->getMessage(), $field_id, $this, $api_request);
			}

			// Build file (as if it were a download)
			$file = array(

				'name' => basename($url),
				'type' => 'text/csv',
				'tmp_name' => '',
				'error' => 0,
				'size' => 0,
				'string' => $csv,
				'group_label' => $preset->label
			);

			// Convert CSV string to meta_value
			try {

				$meta_value = WS_Form_Common::csv_file_to_data_grid_meta_value($file, $meta_key, $meta_value);

			} catch (Exception $e) {

				parent::api_throw_error($e->getMessage());
			}

			// Return data
			return array('error' => false, 'error_message' => '', 'meta_value' => $meta_value, 'max_num_pages' => 0, 'meta_keys' => array(), 'deselect_data_source_id' => true);
		}

		// Get meta keys
		public function get_data_source_meta_keys() {

			return array(

				'data_source_' . $this->id . '_preset_id'
//				'data_source_recurrence'
			);
		}

		// Get settings
		public function get_data_source_settings() {

			// Build settings
			$settings = array(

				'meta_keys' => self::get_data_source_meta_keys()
			);

 			// Add retrieve button
			$settings['meta_keys'][] = 'data_source_' . $this->id . '_get';

			// Wrap settings so they will work with sidebar_html function in admin.js
			$settings = parent::get_settings_wrapper($settings);

			// Add label
			$settings->label = $this->label;

			// Add label retrieving
			$settings->label_retrieving = $this->label_retrieving;

			// Add API GET endpoint
			$settings->endpoint_get = 'data-source/' . $this->id . '/';

			// Apply filter
			$settings = apply_filters('wsf_data_source_' . $this->id . '_settings', $settings);

			return $settings;
		}

		// Get presets
		public function get_presets() {

			$transient_id = 'wsf_data_source_' . $this->id . '_preset';
			$transient_expiry = 86400;	// 1 day expiration

			// Check for presets transient
			$presets = get_transient($transient_id);
			if($presets === false) {

				// Get presets
				try {

					$preset_content = @file_get_contents('https://cdn.wsform.com/plugin-support/preset.json?version=' . urlencode(WS_FORM_VERSION));

					if($preset_content === false) { return false; }

				} catch (Exception $e) {

					return false;
				}

				$preset_content_decoded = json_decode($preset_content);

				if(is_null($preset_content_decoded)) { return false; }
				if(!isset($preset_content_decoded->presets)) { return false; }

				$presets = (array) $preset_content_decoded->presets;

				set_transient($transient_id, $presets, $transient_expiry);
			}

			return $presets;
		}

		// Meta keys for this action
		public function config_meta_keys($meta_keys = array(), $form_id = 0) {

			// Build config_meta_keys
			$config_meta_keys = array(

				// Preset
				'data_source_' . $this->id . '_preset_id' => array(

					'label'						=>	__('Preset', 'ws-form'),
					'type'						=>	'select',
					'options'					=>	array(),
					'options_blank'				=>	__('Select...', 'ws-form'),
					'help'						=>	__('Select which preset to use.', 'ws-form')
				),

				// Get Data
				'data_source_' . $this->id . '_get' => array(

					'label'						=>	__('Get Data', 'ws-form'),
					'type'						=>	'button',
					'condition'					=>	array(

						array(

							'logic'				=>	'!=',
							'meta_key'			=>	'data_source_' . $this->id . '_preset_id',
							'meta_value'		=>	''
						)
					),
					'key'						=>	'data_source_get'
				)
			);

			// Add presets
			$presets = self::get_presets();

			if($presets !== false) {

				// Sort presets
				uasort($presets, function ($preset_1, $preset_2) {

					if($preset_1->optgroup == $preset_2->optgroup) {

						return $preset_1->label < $preset_2->label ? -1 : 1;
					}

					return $preset_1->optgroup < $preset_2->optgroup ? -1 : 1;
				});

				foreach($presets as $preset_id => $preset) {

					$config_meta_keys['data_source_' . $this->id . '_preset_id']['options'][] = array('value' => $preset_id, 'text' => $preset->label, 'optgroup' => $preset->optgroup);
				}
			}

			// Merge
			$meta_keys = array_merge($meta_keys, $config_meta_keys);

			return $meta_keys;
		}

		// Build REST API endpoints
		public function rest_api_init() {

			// Get data source
			register_rest_route(WS_FORM_RESTFUL_NAMESPACE, '/data-source/' . $this->id . '/', array('methods' => 'POST', 'callback' => array($this, 'api_post'), 'permission_callback' => function () { return WS_Form_Common::can_user('edit_form'); }));
		}

		// api_post
		public function api_post() {

			// Get meta keys
			$meta_keys = self::get_data_source_meta_keys();

			// Read settings
			foreach($meta_keys as $meta_key) {

				$this->{$meta_key} = WS_Form_Common::get_query_var($meta_key, false);
				if(
					is_object($this->{$meta_key}) ||
					is_array($this->{$meta_key})
				) {

					$this->{$meta_key} = json_decode(json_encode($this->{$meta_key}));
				}
			}

			// Get field ID
			$field_id = WS_Form_Common::get_query_var('field_id', 0);

			// Get page
			$page = intval(WS_Form_Common::get_query_var('page', 1));

			// Get meta key
			$meta_key = WS_Form_Common::get_query_var('meta_key', 0);

			// Get meta value
			$meta_value = WS_Form_Common::get_query_var('meta_value', 0);

			// Get return data
			$get_return = self::get(false, $field_id, $page, $meta_key, $meta_value, false, true);

			// Error checking
			if($get_return['error']) {

				// Error
				return self::api_error($get_return);

			} else {

				// Success
				return $get_return;
			}
		}
	}

	new WS_Form_Data_Source_Preset();
