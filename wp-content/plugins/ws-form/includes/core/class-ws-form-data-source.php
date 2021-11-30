<?php

	abstract class WS_Form_Data_Source {

		// Variables global to this abstract class
		public static $data_sources = array();
		private static $return_array = array();

		// Register data source
		public function register($object) {

			// Check if pro required for data source
			if(!WS_Form_Common::is_edition($this->pro_required ? 'pro' : 'basic')) { return false; }

			// Get data source ID
			$data_source_id = $this->id;

			// Add action to actions array
			self::$data_sources[$data_source_id] = $object;
		}

		// Get settings wrapper
		public function get_settings_wrapper($settings) {

			$settings_wrapper = new stdClass();

			$settings_wrapper->fieldsets = array(

				$this->id => $settings
			);

			return $settings_wrapper;
		}

		// Get data source settings
		public static function get_settings() {

			$return_settings = array();

			// Add 'Off'
			$return_settings[''] = new stdClass();
			$return_settings['']->{'label'} = __('Off', 'ws-form');
			$return_settings['']->{'fieldsets'} = array('' => array(

				'meta_keys' => array('data_source_id')
			));

			// Build action settings
			foreach(self::$data_sources as $id => $action) {

				if(method_exists($action, 'get_data_source_settings')) {

					$return_settings[$id] = $action->get_data_source_settings();
					array_unshift($return_settings[$id]->{'fieldsets'}[$id]['meta_keys'], 'data_source_id');
				}
			}

			return $return_settings;
		}

		// Add default meta data to meta_return
		public static function get_data_source_meta($data_source_id, $meta = array()) {

			// Get data source
			$data_source = self::$data_sources[$data_source_id];

			// Get meta keys
			$meta_keys = $data_source->config_meta_keys();

			// Get data source meta keys
			$data_source_meta_keys = $data_source->get_data_source_meta_keys();

			foreach($data_source_meta_keys as $data_source_meta_key) {

				$meta_value_default = isset($meta_keys[$data_source_meta_key]['default']) ? $meta_keys[$data_source_meta_key]['default'] : false;

				$meta[$data_source_meta_key] = $meta_value_default;
			}

			return $meta;
		}

		// Get configuration
		public function get_config($config, $meta_key, $default_value = false, $throw_error = false) {

			if(!isset($config['meta']) || !isset($config['meta'][$meta_key])) {

				return $throw_error ? self::get_config_error($config, $meta_key, $default_value) : $default_value;
			}

			return $config['meta'][$meta_key];
		}

		// Get configuration error
		public function get_config_error($config, $meta_key, $default_value = false) {

			if($throw_error) { self::error('Cannot find configuration meta_key: ' + $meta_key, false, false); }

			return $default_value;
		}

		// Schedule - Error
		public function error($error_message, $field_id, $data_source = false, $api_request = false) {

			// Build last API error
			$last_api_error = array(

				'error' => true,
				'error_message' => $error_message,
				'date' => date_i18n(get_option('date_format') . ' ' . get_option('time_format'))
			);

			// Add data source prefix
			if($data_source !== false) {

				$last_api_error['data_source_id'] = $data_source->id;
				$last_api_error['data_source_label'] = $data_source->label;

			} else {

				$last_api_error['data_source_id'] = '';
				$last_api_error['data_source_label'] = __('Unknown', 'ws-form');
			}

			// Save API error
			if(!$api_request) {

				try{

					// Build new meta array
					$meta_array = array('data_source_last_api_error' => $last_api_error);

					// Save new meta value
					$ws_form_meta = new WS_Form_Meta();
					$ws_form_meta->parent_id = $field_id;
					$ws_form_meta->object = 'field';
					$ws_form_meta->db_update_from_array($meta_array, false, true);

				} catch (Exception $e) {

					// Error
					echo sprintf(__('WS Form Data Source CRON Error: %s', 'ws-form'), esc_html($error_message));
					exit;
				}
			}

			return $last_api_error;
		}

		// Get column mapping
		public function get_column_mapping($meta_keys, $meta_value, $meta_key_config) {

			$meta_key_value = isset($meta_key_config['meta_key_value']) ? $meta_key_config['meta_key_value'] : false;
			$meta_key_label = isset($meta_key_config['meta_key_label']) ? $meta_key_config['meta_key_label'] : false;
			$meta_key_parse_variable = isset($meta_key_config['meta_key_parse_variable']) ? $meta_key_config['meta_key_parse_variable'] : false;

			foreach($meta_value->columns as $column) {

				if(!isset($column->id) || !isset($column->label)) { continue; }

				$column_id = $column->id;
				$column_label = strtolower($column->label);

				switch($column_label) {

					case 'value' :
					case 'id' :

						if(
							($meta_key_value !== false) &&
							!isset($meta_keys[$meta_key_value])
						) {
							$meta_keys[$meta_key_value] = $column_id;
						}

						break;

					case 'label' :
					case 'name' :
					case 'title' :
					case 'display name' :
					case 'nicename' :
					case 'login' :
					case 'email' :

						if(
							($meta_key_label !== false) &&
							!isset($meta_keys[$meta_key_label])
						) {
							$meta_keys[$meta_key_label] = $column_id;
						}

						if(
							($meta_key_parse_variable !== false) &&
							!isset($meta_keys[$meta_key_parse_variable])
						) {
							$meta_keys[$meta_key_parse_variable] = $column_id;
						}

						break;
				}
			}

			// Apply filter
			$meta_keys = apply_filters('wsf_data_source_get_column_mapping', $meta_keys, $meta_value, $meta_key_config);

			return $meta_keys;
		}

		// Action API call response
		public function api_error($last_api_error) {

			// Set HTTP content type head
			header('Content-Type: application/json');

			// Set header
			header('HTTP/1.1 400 Bad Request', true, 400);

			// API response
			echo wp_json_encode($last_api_error);

			exit;
		}
	}
