<?php

	class WS_Form_Pods {

		// Get fields all
		public static function pods_get_fields_all($type = false, $post_types = false, $choices_filter = false, $raw = false, $traverse = false, $has_fields = false) {

			// Pods fields
			$fields = array();

			$fields_found = false;

			// Ensure post types is an array if specified
			if(
				($post_types !== false) &&
				!is_array($post_types)
			) {

				$post_types = array($post_types);
			}

			// Initialize Pods API
			$pods_api = pods_api();

			// Build args
			$args = array(

				'fields' => true
			);

			if($type !== false) {

				$args['type'] = $type;
			}

			// Check if object type specified
			$pods = $pods_api->load_pods($args);

			// Process each Pod
			foreach($pods as $pods_id => $pod) {

				if(
					($post_types !== false) &&
					!in_array($pod['name'], $post_types)
				) {
					continue;
				}

				// Check for get_groups method in version 2.8+ of Pods
				if(
					is_object($pod) &&
					method_exists($pod, 'get_groups')
				) {

					// Get groups
					$pod_groups = $pod->get_groups();

					// Process each group
					foreach($pod_groups as $pod_group) {

						// Get fields for group
						$pod_fields = $pod_group->get_fields();

						// Has fields?
						if($has_fields && (count($pod_fields) > 0)) { $fields_found = true; break 2; }

						// Get group label
						$pod_group_label = $pod_group->get_arg('label');

						// Process fields
						self::pods_get_fields_process($fields, $pod_group_label, $pod_group, $pod_fields, $choices_filter, $raw, $traverse);
					}

				} else {

					// Get pod group label (Use label in older versions of Pod)
					$pod_group_label = $pod['label'];

					// Get pod fields
					$pod_fields = $pod['fields'];

					// Has fields?
					if($has_fields && (count($pod_fields) > 0)) { $fields_found = true; break; }

					// Process fields
					self::pods_get_fields_process($fields, $pod_group_label, false, $pod_fields, $choices_filter, $raw, $traverse);
				}
			}

			return $has_fields ? $fields_found : $fields;
		}

		// Get fields
		public static function pods_get_fields_process(&$fields, $pod_group_label, $pod_group, $pod_fields, $choices_filter, $raw, $traverse, $prefix = '') {

			foreach($pod_fields as $pod_field) {

				// Get field ID
				$pod_field_id = $pod_field['id'];

				// Store group label
				$pod_field['wsf_pod_label'] = $pod_group_label;

				// Store group
				$pod_field['wsf_pod_group'] = $pod_group;

				// Only return fields that have choices
				$process_field = true;
				if(
					$choices_filter &&
					(
						!isset($pod_field['options']['pick_custom']) ||
						($pod_field['options']['pick_custom'] == '')
					)
				) {

					$process_field = false;
				}

				if($process_field) {

					if($raw) {

						$fields[$pod_field_id] = $pod_field;

					} else {

						// Get field label
						$pod_field_label = $pod_field['label'];

						// Get field type
						$pod_field_type = $pod_field['type'];

						// Check if mappable
						if(self::pods_field_mappable($pod_field_type)) {

							$fields[$pod_field_id] = array('value' => $pod_field_id, 'text' => sprintf('%s%s - %s', $pod_group_label, $prefix, $pod_field_label));
						}
					}
				}
			}
		}

		// Get field id to name lookup
		public static function pods_get_id_to_name_lookup($type = false, $post_types = false) {

			$id_to_name_lookup = array();

			// Build pods ID to name lookup
			$fields = WS_Form_Pods::pods_get_fields_all($type, $post_types, false, true, false);

			foreach($fields as $field) {

				$id_to_name_lookup[$field['id']] = $field['name'];
			}

			return $id_to_name_lookup;
		}

		// Get field
		public static function pods_get_field_settings($pods_field_id) {

			$pods_api = pods_api();

			$args = array(

				'id' => $pods_field_id
			);

			return $pods_api->load_field($args);
		}

		// Get field data
		public static function pods_get_field_data($type, $post_types, $object_id) {

			$field_objects = self::pods_get_fields_all($type, $post_types, false, true, false);
			if($field_objects === false) { return array(); }

			// Get post type
			switch($type) {

				case 'user' :

					$pod = 'user';
					break;

				default :

					$pod = is_array($post_types) ? $post_types[0] : $post_types;
					break;
			}

			$return_array = array();

			// Get pod
			$pod = pods($pod, $object_id);

			foreach($field_objects as $field_object) {

				// Get field ID
				$pods_field_id = $field_object['id'];

				// Get field name
				$pods_field_name = $field_object['name'];

				// Get field value
				$field_value = $pod->raw($pods_field_name, true);

				// Check field value
				if($field_value === false) { $field_value = ''; }

				// Add to return array
				$return_array[$pods_field_id] = array('values' => $field_value);
			}

			return $return_array;
		}

		// Process Pods fields
		public static function pods_fields_to_list_fields($fields, $group_index = 0, $section_index = 1, $field_index = 1, $depth = 0) {

			$list_fields = array();

			$tab_last = false;
			$wsf_pod_label_last = false;

			foreach($fields as $field) {

				// Get field type
				$action_type = $field['type'];
				$type = self::pods_action_field_type_to_ws_form_field_type($field);

				if($type === false) { continue; }

				// Get meta
				$meta = self::pods_action_field_to_ws_form_meta_keys($field);

				// Pods label
				$wsf_pod_label = isset($field['wsf_pod_label']) ? $field['wsf_pod_label'] : false;

				if(
					($depth === 0) &&
					($wsf_pod_label !== false) &&
					($wsf_pod_label !== $wsf_pod_label_last)
				) {

					if(
						!(
							($section_index === 0) &&
							($field_index === 1) 
						)
					) {

						$section_index++;
					}

					$wsf_pod_label_last = $wsf_pod_label;
				}

				$list_fields_single = array(

					'id' => 				$field['id'],
					'label' => 				$field['label'], 
					'label_field' => 		$field['label'], 
					'type' => 				$type,
					'action_type' =>		$action_type,
					'required' => 			self::pods_get_field_option($field, 'required', false),
					'default_value' => 		self::pods_get_field_option($field, 'default_value', false),
					'pattern' => 			'',
					'placeholder' => 		self::pods_get_field_option($field, 'text_placeholder', false),
					'help' =>				isset($field['description']) ? $field['description'] : '',
					'group_index' =>		$group_index,
					'section_index' => 		$section_index,
					'sort_index' => 		$field_index++,
					'visible' =>			true,
					'meta' => 				$meta,
					'no_map' =>				true
				);

				$list_fields[] = $list_fields_single;
			}

			return array('list_fields' => $list_fields, 'group_index' => $group_index, 'section_index' => $section_index);
		}

		// Convert action field to WS Form meta key
		public static function pods_action_field_to_ws_form_meta_keys($field) {

			$meta_return = array();

			$label = $field['label'];
			$type = $field['type'];

			// Meta mappings
			$meta_mappings = array(

				'class' => 'class_field',
				'hidden' => 'hidden',
				'read_only' => 'read_only'
			);

			foreach($meta_mappings as $pods_meta_key => $ws_form_meta_key) {

				$ws_form_meta_value = self::pods_get_field_option($field, $pods_meta_key, false);

				if($ws_form_meta_value !== false) {

					if($ws_form_meta_value === '1') {

						// True
						$ws_form_meta_value = 'on';

					} elseif($ws_form_meta_value === '0') {

						// False
						$ws_form_meta_value = '';
					}

					$meta_return[$ws_form_meta_key] = $ws_form_meta_value;
				}				
			}

			// Max length
			switch($type) {

				case 'text' :
				case 'website' :
				case 'phone' :
				case 'email' :
				case 'password' :
				case 'paragraph' :
				case 'code' :

					$max_length = intval(self::pods_get_field_option($field, 'text_max_length', -1));
					if($max_length > 0) {

						$meta_return['max_length'] = $max_length;
					}

					break;
			}

			// Role and capability checks
			$field_user_roles = array();

			$admin_only = self::pods_get_field_option($field, 'admin_only', false) === '1';
			if($admin_only) {

				$field_user_roles[] = 'administrator';
			}

			$restrict_role = self::pods_get_field_option($field, 'restrict_role', false) === '1';
			if($restrict_role) {

				$roles_allowed = self::pods_get_field_option($field, 'roles_allowed');
				if(is_array($roles_allowed)) {

					$field_user_roles = array_merge($field_user_roles, $roles_allowed);
				}
			}

			// Field capabilities
			$field_user_capabilities = array();

			$restrict_capability = self::pods_get_field_option($field, 'restrict_capability', false) === '1';

			if($restrict_capability) {

				$capability_allowed = self::pods_get_field_option($field, 'capability_allowed');
				if($capability_allowed != '') {

					$capability_allowed = explode(',', $capability_allowed);

					$field_user_capabilities = array_merge($field_user_capabilities, $capability_allowed);
				}
			}

			$field_user_status = ((count($field_user_roles) > 0) || (count($field_user_capabilities) > 0)) ? 'role_capability' : false;

			if($field_user_status === 'role_capability') {

				$meta_return['field_user_status'] = $field_user_status;

				if(count($field_user_roles) > 0) {

					$field_user_roles = array_values(array_unique($field_user_roles));

					$meta_return['field_user_roles'] = $field_user_roles;
				}

				if(count($field_user_capabilities) > 0) {

					$field_user_capabilities = array_values(array_unique($field_user_capabilities));

					$meta_return['field_user_capabilities'] = $field_user_capabilities;
				}

			} else {

				$logged_in_only = self::pods_get_field_option($field, 'logged_in_only', false) === '1';

				if($logged_in_only) {

					$meta_return['field_user_status'] = 'on';
				}
			}

			// Check for data source
			$data_source = self::pods_get_field_data_source($field);

			// Data grids
			$meta_key = false;
			$multiple = false;
			$choices = false;
			$default = false;
			$auto_complete = false;

			// Process by Meta Box field type
			switch($type) {

				// Heading
				case 'heading' :

					$heading_tag = self::pods_get_field_option($field, 'heading_tag', 'h2');

					$meta_return['html_editor'] = sprintf('<%1$s>%2$s</%1$s>', $heading_tag, $label);

					break;

				// HTML
				case 'html' :

					$html_content = self::pods_get_field_option($field, 'html_content', '');

					$meta_return['html_editor'] = $html_content;

					break;

				// Number / Currency
				case 'number' :

					// Get decimals
					$number_decimals = intval(self::pods_get_field_option($field, 'number_decimals', 0));
					if($number_decimals > 0) {

						$meta_return['step'] = pow(10, -$number_decimals);
					}

					break;

				// WYSIWYG
				case 'wysiwyg' :

					$meta_return['input_type_textarea'] = 'tinymce';

					break;

				// Code
				case 'code' :

					$meta_return['input_type_textarea'] = 'html';

					break;

				// Build data grids for checkbox, radio and select
				case 'pick' :

					$meta_key = 'data_grid_select';
					$auto_complete = false;

					$pick_format_type = self::pods_get_field_option($field, 'pick_format_type');

					switch($pick_format_type) {

						case 'single' :

							$pick_format_single = self::pods_get_field_option($field, 'pick_format_single');

							switch($pick_format_single) {

								case 'radio' : $meta_key = 'data_grid_radio'; break;
								case 'autocomplete' : $auto_complete = true; break;
								case 'list' : $auto_complete = true; break;
							}

							break;

						case 'multi' :

							$pick_format_multi = self::pods_get_field_option($field, 'pick_format_multi');

							switch($pick_format_multi) {

								case 'checkbox' : $meta_key = 'data_grid_checkbox'; break;
								case 'autocomplete' : $auto_complete = true; break;
								case 'list' : $auto_complete = true; break;
							}

							$multiple = true;

							break;
					}

					break;

				case 'boolean' :

					$boolean_format_type = self::pods_get_field_option($field, 'boolean_format_type');
					$boolean_yes_label = self::pods_get_field_option($field, 'boolean_yes_label', __('Yes', 'ws-form'));
					$boolean_no_label = self::pods_get_field_option($field, 'boolean_no_label', __('No', 'ws-form'));

					$default = $boolean_no_label;

					switch($boolean_format_type) {

						case 'checkbox' : 

							$meta_key = 'data_grid_checkbox';

							$choices = array(

								$boolean_yes_label => 1
							);

							break;

						case 'radio' : 

							$meta_key = 'data_grid_radio';

							$choices = array(

								$boolean_yes_label => 1,
								$boolean_no_label => 0
							);

							break;

						case 'dropdown' :

							$meta_key = 'data_grid_select';

							$choices = array(

								$boolean_yes_label => 1,
								$boolean_no_label => 0
							);

							$meta_return['placeholder_row'] = '';

							break;
					}

					break;

				// File
				case 'file' :

					$meta_return['sub_type'] = 'dropzonejs';

					// Multiple
					$file_format_type = self::pods_get_field_option($field, 'file_format_type');
					$meta_return['multiple_file'] = ($file_format_type == 'multi') ? 'on' : '';

					// Max files
					$file_limit = intval(self::pods_get_field_option($field, 'file_limit', ''));
					$meta_return['file_max'] = ($file_limit > 0) ? $file_limit : '';

					// Max files
					$file_type = self::pods_get_field_option($field, 'file_type', '');

					switch($file_type) {

						case 'images' :

							$meta_return['accept'] = 'image/*';
							break;

						case 'video' :
						case 'audio' :
						case 'text' :

							$meta_return['accept'] = sprintf('%s/*', $file_type);
							break;

						case 'other' :

							$meta_return['accept'] = self::pods_get_field_option($field, 'file_allowed_extensions', '');
							break;
					}

					break;

				// Date
				case 'datetime' :

					$meta_return['input_type_datetime'] = 'datetime-local';
					break;

				case 'date' :

					$meta_return['input_type_datetime'] = 'date';
					break;

				case 'time' :

					$meta_return['input_type_datetime'] = 'time';
					break;
			}

			// Process 
			switch($meta_key) {

				case 'data_grid_select' :

					$meta_return['select_field_value'] = 1;

					// Multiple
					if($multiple) {

						$meta_return['multiple'] = 'on';
						$meta_return['placeholder_row'] = '';
					}

					// Auto complete
					if($auto_complete) {

						$meta_return['select2'] = 'on';
					}

					break;

				case 'data_grid_checkbox' :

					$meta_return['checkbox_field_value'] = 1;

					break;

				case 'data_grid_radio' :

					$meta_return['radio_field_value'] = 1;

					break;
			}

			// Data source
			if($data_source !== false) {

				// Data source set-up
				$meta_return = WS_Form_Data_Source::get_data_source_meta($data_source, $meta_return);

				// Set up data source
				$meta_return['data_source_id'] = $data_source;

				// Data source meta data
				switch($data_source) {

					case 'pods' :

						$meta_return['data_source_pods_field_id'] = $field['id'];

						break;

					case 'post' :

						if(isset($field['pick_val']) && ($field['pick_val'] != '')) {

							$meta_return['data_source_post_filter_post_types'] = array(

								array('data_source_post_post_types' => $field['pick_val'])
							);
						}

						// Post status
						if(isset($field['options']['pick_post_status'])) {

							$meta_return['data_source_post_filter_post_statuses'] = array();

							foreach($field['options']['pick_post_status'] as $pick_post_status) {

								$meta_return['data_source_post_filter_post_statuses'][] = array(

									'data_source_post_post_statuses' => $pick_post_status
								);
							}
						}

						break;

					case 'term' :

						if(isset($field['pick_val']) && ($field['pick_val'] != '')) {

							$meta_return['data_source_term_filter_taxonomies'] = array(

								array('data_source_term_taxonomies' => $field['pick_val'])
							);
						}

						break;

					case 'user' :

						// Post status
						if(isset($field['options']['pick_user_role'])) {

							$meta_return['data_source_user_filter_roles'] = array();

							foreach(['options']['pick_user_role'] as $pick_user_role) {

								$meta_return['data_source_user_filter_roles'][] = array(

									'data_source_user_roles' => $pick_user_role
								);
							}
						}

						break;
				}
			}

			// Check for data grid
			if($meta_key !== false) {

				// Get base meta
				$meta_keys = WS_Form_Config::get_meta_keys();

				// Get default meta data
				if(!isset($meta_keys[$meta_key])) { return false; }
				if(!isset($meta_keys[$meta_key]['default'])) { return false; }

				$meta = $meta_keys[$meta_key]['default'];

				// Configure columns
				$meta['columns'] = array(

					array('id' => 0, 'label' => __('Label', 'ws-form')),
					array('id' => 1, 'label' => __('Value', 'ws-form'))
				);

				if(
					($data_source === false) &&
					($choices === false)
				) {

					// Get choices
					$pick_custom = isset($field['options']['pick_custom']) ? $field['options']['pick_custom'] : '';

					// Get choices
					$choices = array();

					$rows = explode("\n", $pick_custom);

					foreach($rows as $row) {

						$row = trim($row);

						if($row == '') { continue; }

						$columns = explode('|', $row);

						if(count($columns) === 1) {

							$choices[$columns[0]] = $columns[0];
						}

						if(count($columns) === 2) {

							$choices[$columns[1]] = $columns[0];
						}
					}
				}

				// Build new rows
				$rows = array();
				$id = 1;

				if(is_array($choices)) {

					foreach($choices as $value => $text) {

						$data = array($value, $text);

						$rows[] = array(

							'id'		=> $id,
							'default'	=> ($value === $default) ? 'on' : '',
							'required'	=> '',
							'disabled'	=> '',
							'hidden'	=> '',
							'data'		=> $data
						);

						$id++;
					}
				}

				// Modify meta
				$meta['groups'][0]['rows'] = $rows;

				$meta_return[$meta_key] = $meta;
			}

			return $meta_return;
		}

		// Get group option
		public static function pods_get_group_option($group, $meta_key, $default_value = false) {

			if(!isset($group['options'])) { return $default_value; }

			return isset($group['options'][$meta_key]) ? $group['options'][$meta_key] : $default_value;
		}

		// Get field option
		public static function pods_get_field_option($field, $meta_key, $default_value = false) {

			if(!isset($field['options'])) { return $default_value; }

			return isset($field['options'][$meta_key]) ? $field['options'][$meta_key] : $default_value;
		}

		// Process Pods fields
		public static function pods_fields_to_meta_data($fields, $group_index = 0, $section_index = 1, $field_index = 1, $depth = 0) {

			$list_fields = array();
			$group_meta_data = array();
			$section_meta_data = array();

			$tab_last = false;
			$wsf_pod_label_last = false;

			foreach($fields as $field) {

				// Get Pods field type
				$action_type = $field['type'];

				// Get WS Form field type
				$type = self::pods_action_field_type_to_ws_form_field_type($field);
				if($type === false) { continue; }

				// Section names
				$wsf_pod_label = isset($field['wsf_pod_label']) ? $field['wsf_pod_label'] : false;

				// Pods label
				if(
					($depth === 0) &&
					($wsf_pod_label !== false) &&
					($wsf_pod_label !== $wsf_pod_label_last)
				) {

					if(empty($wsf_pod_label)) { $wsf_pod_label = WS_FORM_DEFAULT_GROUP_NAME; }

					if(
						!(
							($section_index === 0) &&
							($field_index === 1) 
						)
					) {

						$section_index++;
					}

					if(!isset($section_meta_data['group_' . $group_index]['section_' . $section_index])) { $section_meta_data['group_' . $group_index]['section_' . $section_index] = array(); }
					$section_meta_data['group_' . $group_index]['section_' . $section_index]['label'] = $wsf_pod_label;

					// Section group
					$group = isset($field['wsf_pod_group']) ? $field['wsf_pod_group'] : false;

					if($group !== false) {

						// Group label
						$section_meta_data['group_' . $group_index]['section_' . $section_index]['label_render'] = 'on';

						// Role and capability checks
						$section_user_roles = array();

						$admin_only = self::pods_get_group_option($group, 'admin_only', false) === '1';
						if($admin_only) {

							$section_user_roles[] = 'administrator';
						}

						$restrict_role = self::pods_get_group_option($group, 'restrict_role', false) === '1';
						if($restrict_role) {

							$roles_allowed = self::pods_get_group_option($group, 'roles_allowed');
							if(is_array($roles_allowed)) {

								$section_user_roles = array_merge($section_user_roles, $roles_allowed);
							}
						}

						// Field capabilities
						$section_user_capabilities = array();

						$restrict_capability = self::pods_get_group_option($group, 'restrict_capability', false) === '1';

						if($restrict_capability) {

							$capability_allowed = self::pods_get_group_option($group, 'capability_allowed');
							if($capability_allowed != '') {

								$capability_allowed = explode(',', $capability_allowed);

								$section_user_capabilities = array_merge($section_user_capabilities, $capability_allowed);
							}
						}

						$section_user_status = ((count($section_user_roles) > 0) || (count($section_user_capabilities) > 0)) ? 'role_capability' : false;

						if($section_user_status === 'role_capability') {

							$section_meta_data['group_' . $group_index]['section_' . $section_index]['section_user_status'] = $section_user_status;

							if(count($section_user_roles) > 0) {

								$section_user_roles = array_values(array_unique($section_user_roles));

								$section_meta_data['group_' . $group_index]['section_' . $section_index]['section_user_roles'] = $section_user_roles;
							}

							if(count($section_user_capabilities) > 0) {

								$section_user_capabilities = array_values(array_unique($section_user_capabilities));

								$section_meta_data['group_' . $group_index]['section_' . $section_index]['section_user_capabilities'] = $section_user_capabilities;
							}

						} else {

							$logged_in = self::pods_get_group_option($group, 'logged_in', false) === '1';

							if($logged_in) {

								$section_meta_data['group_' . $group_index]['section_' . $section_index]['section_user_status'] = 'on';
							}
						}
					}

					$wsf_pod_label_last = $wsf_pod_label;
				}

				// Dummy entry
				$list_fields[] = array();

				$field_index++;
			}

			return array('list_fields' => $list_fields, 'group_meta_data' => $group_meta_data, 'section_meta_data' => $section_meta_data, 'group_index' => $group_index, 'section_index' => $section_index);
		}

		// Process pods_field_values as taxonomy
		public static function pods_field_values_taxonomy($pods_field_values) {

			return (is_object($pods_field_values) && isset($pods_field_values->term_id)) ? $pods_field_values->term_id : $pods_field_values;
		}

		// Process pods_field_values as file
		public static function pods_field_values_file($pods_field_values) {

			if(!is_array($pods_field_values)) { return false; }

			// Check for multiple files
			if(!isset($pods_field_values[0])) { $pods_field_values = array($pods_field_values); }

			$return_array = array();

			foreach($pods_field_values as $pods_field_value) {

				$attachment_id = $pods_field_value['ID'];

				$file_object = WS_Form_File_Handler::get_file_object_from_attachment_id($attachment_id);
				if($file_object === false) { continue; }

				$return_array[] = $file_object;
			}

			return (count($return_array) > 0) ? $return_array : false;
		}

		// Process pods_field_values as boolean
		public static function pods_field_values_boolean($pods_field_values, $field_id, $fields, $field_types) {

			// Get meta value array (Array containing values of data grid)
			$meta_value_array = WS_Form_Common::get_meta_value_array($field_id, $fields, $field_types);
			$true_array = array('1', 'on', 'yes', 'true');

			// Get first element if array
			if(is_array($pods_field_values)) { $pods_field_values = isset($pods_field_values[0]) ? $pods_field_values[0] : ''; }

			$pods_field_values = strtolower($pods_field_values);

			return in_array($pods_field_values, $true_array) ? $meta_value_array[0] : false;
		}

		// Process pods_field_values as date
		public static function pods_field_values_date_time($pods_field_values, $field_id, $pods_field_type) {

			if(intval($field_id) === 0) { return ''; }

			try {

				$ws_form_field = new WS_Form_Field();
				$ws_form_field->id = $field_id;
				$field_object = $ws_form_field->db_read(true, true);

			} catch (Exception $e) {

				return '';
			}

			$format_date = WS_Form_Common::get_object_meta_value($field_object, 'format_date', get_option('date_format'));
			if(empty($format_date)) { $format_date = get_option('date_format'); }
			$format_time = WS_Form_Common::get_object_meta_value($field_object, 'format_time', get_option('time_format'));
			if(empty($format_time)) { $format_time = get_option('time_format'); }

			switch($pods_field_type) {

				case 'datetime' :

					return date($format_date . ' ' . $format_time, strtotime($pods_field_values));

				case 'date' :

					return date($format_date, strtotime($pods_field_values));

				case 'time' :

					return date($format_time, strtotime($pods_field_values));
			}

			return '';
		}

		// Get field type
		public static function pods_get_field_type($pods_field_id) {

			$field_settings = self::pods_get_field_settings($pods_field_id);
			if($field_settings === false) { return false; }

			return $field_settings['type'];
		}

		// Get file field types
		public static function pods_get_field_types_file() {

			return array(

				'file'
			);
		}

		// Convert Pods meta value to WS Form field
		public static function pods_pods_meta_value_to_ws_form_field_value($pods_field_values, $pods_field_type, $field_id, $fields, $field_types) {

			switch($pods_field_type) {

				case 'file' :

					// Process regular attachment IDs
					$pods_field_values = self::pods_field_values_file($pods_field_values);

					break;

				case 'datetime' :
				case 'date' :
				case 'time' :

					// Process regular date
					$pods_field_values = self::pods_field_values_date_time($pods_field_values, $field_id, $pods_field_type);

					break;
			}

			return $pods_field_values;
		}

		// Convert WS Form field value to Pods meta value
		public static function pods_ws_form_field_value_to_pods_meta_value($meta_value, $pods_field_type, $pods_field_id) {

			if($meta_value == '') { return ''; }

			switch($pods_field_type) {

				case 'boolean' :

					if(!is_array($meta_value)) { $meta_value = array($meta_value); }

					return in_array('1', $meta_value) ? '1' : '0';

				case 'datetime' :

					return date('Y-m-d', strtotime($meta_value)) . ' ' . date('H:i', strtotime($meta_value));

				case 'date' :

					return date('Y-m-d', strtotime($meta_value));

				case 'time' :

					return date('H:i', strtotime($meta_value));
			}

			return $meta_value;
		}

		// Get field data source
		public static function pods_get_field_data_source($field) {

			$pick_object = isset($field['pick_object']) ? $field['pick_object'] : false;
			
			if($pick_object !== false) {

				switch($pick_object) {

					case 'custom-simple' : return 'pods';
					case 'post_type' : return 'post';
					case 'taxonomy' : return 'term';
					case 'user' : return 'user';
				}
			}

			return false;
		}

		// Convert action field type to WS Form field type
		public static function pods_action_field_type_to_ws_form_field_type($field) {

			$type = $field['type'];

			switch($type) {

				case 'text' : return 'text';

				case 'pick' : 

					$pick_format_type = self::pods_get_field_option($field, 'pick_format_type');

					switch($pick_format_type) {

						case 'single' :

							$pick_format_single = self::pods_get_field_option($field, 'pick_format_single');

							switch($pick_format_single) {

								case 'radio' : return 'radio';

								default : return 'select';
							}

							break;

						case 'multi' :

							$pick_format_multi = self::pods_get_field_option($field, 'pick_format_multi');

							switch($pick_format_multi) {

								case 'checkbox' : return 'checkbox';

								default : return 'select';
							}

							break;
					}

					break;

				case 'boolean' : 

					$boolean_format_type = self::pods_get_field_option($field, 'boolean_format_type');

					switch($boolean_format_type) {

						case 'checkbox' : return 'checkbox';
						case 'radio' : return 'radio';
						case 'dropdown' : return 'select';
					}

					break;

				case 'website' : return 'url';
				case 'phone' : return 'tel';
				case 'email' : return 'email';
				case 'password' : return 'password';
				case 'paragraph' : return 'textarea';
				case 'wysiwyg' : return 'textarea';
				case 'code' : return 'textarea';
				case 'datetime' : return 'datetime';
				case 'date' : return 'datetime';
				case 'time' : return 'datetime';
				case 'number' : return 'number';
				case 'currency' : return 'price';
				case 'file' : return 'file';
				case 'oembed' : return 'url';
				case 'color' : return 'color';
				case 'heading' : return 'html';
				case 'html' : return 'html';
			}

			return false;
		}

		// Fields that we can push data to
		public static function pods_field_mappable($pods_field_type) {

			switch($pods_field_type) {

				case 'text' :
				case 'pick' :
				case 'website' :
				case 'phone' :
				case 'email' :
				case 'password' :
				case 'paragraph' :
				case 'wysiwyg' :
				case 'code' :
				case 'datetime' :
				case 'date' :
				case 'time' :
				case 'number' :
				case 'currency' :
				case 'file' :
				case 'oembed' :
				case 'boolean' :
				case 'color' :

					return true;

				default :

					return false;
			}
		}
	}