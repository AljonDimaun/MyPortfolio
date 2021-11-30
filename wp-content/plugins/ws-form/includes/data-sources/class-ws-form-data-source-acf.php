<?php

	class WS_Form_Data_Source_ACF extends WS_Form_Data_Source {

		public $id = 'acf';
		public $pro_required = false;
		public $label;
		public $label_retrieving;
		public $records_per_page = 0;

		public function __construct() {

			// Set label
			$this->label = __('ACF Field Options', 'ws-form');

			// Set label retrieving
			$this->label_retrieving = __('Retrieving ACF field options...', 'ws-form');

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

			// Columns
			$meta_value->columns = array(

				(object) array('id' => 0, 'label' => __('Value', 'ws-form')),
				(object) array('id' => 1, 'label' => __('Label', 'ws-form'))
			);

			// Base meta
			$group = clone($meta_value->groups[0]);

			// Get ACF field key
			$acf_field_key = $this->{'data_source_' . $this->id . '_field_key'};

			// Get ACF field object
			$acf_field_object = get_field_object($acf_field_key);

			// Filter by post?
			$filter_by_options = false;
			$filter_by_post = $this->{'data_source_' . $this->id . '_filter_by_post'};
			if($filter_by_post) {

				// Get post ID
				$filter_by_post_id = $this->{'data_source_' . $this->id . '_filter_by_post_id'};
				if($filter_by_post_id == '') { $filter_by_post_id = '#post_id'; }
				$filter_by_post_id = intval(WS_Form_Common::parse_variables_process($filter_by_post_id, $form_object, false, 'text/plain'));

				if($filter_by_post_id > 0) {

					// Get parent data to see if this field is in a repeater or group
					$acf_parent_data = WS_Form_ACF::acf_get_parent_data($acf_field_key);
					$acf_parent_field_type = isset($acf_parent_data['type']) ? $acf_parent_data['type'] : false;

					switch($acf_parent_field_type) {

						case 'repeater' :
						case 'group' :

							$filter_by_options = array();

							// Get ACF field name (We'll use this to look up values)
							$acf_field_name = $acf_field_object['name'];

							// Get parent field object (contains an array of child field values)
							$acf_field_key_parent = $acf_parent_data['acf_key'];
							$acf_field_object_parent = get_field_object($acf_field_key_parent);

							// Get values
							$acf_field_object_parent_values = isset($acf_field_object_parent['value']) ? $acf_field_object_parent['value'] : false;

							// Process values
							if(is_array($acf_field_object_parent_values)) {

								if($acf_parent_field_type == 'repeater') {

									foreach($acf_field_object_parent_values as $row_index => $row) {

										foreach($row as $name => $value) {

											if($name == $acf_field_name) {

											if(empty($value)) { $value = array(); }
											if(is_string($value)) { $value = array($value); }
												$filter_by_options = array_merge($filter_by_options, $value);
											}
										}
									}
								}

								if($acf_parent_field_type == 'group') {

									foreach($acf_field_object_parent_values as $name => $value) {

										if($name == $acf_field_name) {

											if(empty($value)) { $value = array(); }
											if(is_string($value)) { $value = array($value); }
											$filter_by_options = array_merge($filter_by_options, $value);
										}
									}
								}
							}

							break;

						default :

							// Field that is not in a group or repeater
							$filter_by_options = get_field($acf_field_key, $filter_by_post_id, false);
							if(empty($filter_by_options)) { $filter_by_options = array(); }
							if(is_string($filter_by_options)) { $filter_by_options = array($filter_by_options); }
					}
				}
			}

			// Get ACF field label
			$label = isset($acf_field_object['label']) ? $acf_field_object['label'] : $this->label;

			// Get ACF field choices
			$choices = isset($acf_field_object['choices']) ? $acf_field_object['choices'] : array();

			// Run through choices
			$rows = array();
			$row_index = 1;
			foreach($choices as $value => $text) {

				// Filter by post?
				if($filter_by_options !== false) {

					if(!in_array($value, $filter_by_options)) { continue; }
				}

				$rows[] = (object) array(

					'id'		=> $row_index++,
					'default'	=> '',
					'required'	=> '',
					'disabled'	=> '',
					'hidden'	=> '',
					'data'		=> array(

						$value,
						$text
					)
				);
			}

			// Build new group if one does not exist
			if(!isset($meta_value->groups[0])) {

				$meta_value->groups[0] = $group;
			}

			$meta_value->groups[0]->label = $label;

			// Rows
			$meta_value->groups[0]->rows = $rows;

			// Delete any old groups
			$group_index = 1;
			while(isset($meta_value->groups[$group_index])) {

				unset($meta_value->groups[$group_index++]);
			}

			// Column mapping
			$meta_keys = parent::get_column_mapping(array(), $meta_value, $meta_key_config);

			// Return data
			return array('error' => false, 'error_message' => '', 'meta_value' => $meta_value, 'max_num_pages' => 0, 'meta_keys' => $meta_keys);
		}

		// Get meta keys
		public function get_data_source_meta_keys() {

			return array(

				'data_source_' . $this->id . '_field_key',
				'data_source_' . $this->id . '_filter_by_post',
				'data_source_' . $this->id . '_filter_by_post_id'
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

		// Meta keys for this action
		public function config_meta_keys($meta_keys = array(), $form_id = 0) {

			// Build config_meta_keys
			$config_meta_keys = array(

				// ACF Field
				'data_source_' . $this->id . '_field_key' => array(

					'label'						=>	__('ACF Field', 'ws-form'),
					'type'						=>	'select',
					'options'					=>	WS_Form_ACF::acf_get_fields_all(false, true, false, true),
					'options_blank'				=>	__('Select...', 'ws-form')
				),

				// ACF Filter by Post
				'data_source_' . $this->id . '_filter_by_post' => array(

					'label'						=>	__('Filter by Post', 'ws-form'),
					'type'						=>	'checkbox',
					'default'					=>	'',
					'help'						=>	__('Filter the options by those selected in a post.'),
					'condition'					=>	array(

						array(

							'logic'				=>	'!=',
							'meta_key'			=>	'data_source_' . $this->id . '_field_key',
							'meta_value'		=>	''
						)
					)
				),

				// ACF Filter by Post ID
				'data_source_' . $this->id . '_filter_by_post_id' => array(

					'label'						=>	__('Post ID', 'ws-form'),
					'type'						=>	'text',
					'default'					=>	'',
					'placeholder'				=>	'#post_id',
					'help'						=>	__('Choose the post ID to filter by. This can be a number or WS Form variable. If blank, the ID of the post the form is shown on will be used.'),
					'condition'					=>	array(

						array(

							'logic'				=>	'!=',
							'meta_key'			=>	'data_source_' . $this->id . '_field_key',
							'meta_value'		=>	''
						),

						array(

							'logic_previous'	=>	'&&',
							'logic'				=>	'==',
							'meta_key'			=>	'data_source_' . $this->id . '_filter_by_post'
						)
					)
				),

				// Get Data
				'data_source_' . $this->id . '_get' => array(

					'label'						=>	__('Get Data', 'ws-form'),
					'type'						=>	'button',
					'condition'					=>	array(

						array(

							'logic'				=>	'!=',
							'meta_key'			=>	'data_source_' . $this->id . '_field_key',
							'meta_value'		=>	''
						)
					),
					'key'						=>	'data_source_get'
				)
			);

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

	new WS_Form_Data_Source_ACF();
