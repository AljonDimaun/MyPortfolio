<?php

	class WS_Form_ACF {

		// Get fields all
		public static function acf_get_fields_all($acf_get_field_groups_filter = array(), $choices_filter = false, $raw = false, $traverse = false, $has_fields = false) {

			if($acf_get_field_groups_filter === false) { $acf_get_field_groups_filter = array(); }

			// ACF fields
			$options_acf = array();

			$fields_found = false;

			// Get ACF field groups
			$acf_field_groups = acf_get_field_groups($acf_get_field_groups_filter);

			// Process each ACF field group
			foreach($acf_field_groups as $acf_field_group) {

				// Get fields
				$acf_fields = acf_get_fields($acf_field_group);

				// Has fields?
				if($has_fields && (count($acf_fields) > 0)) { $fields_found = true; break; }

				// Get group name
				$acf_field_group_name = $acf_field_group['title'];

				// Process fields
				WS_Form_ACF::acf_get_fields_process($options_acf, $acf_field_group_name, $acf_fields, $choices_filter, $raw, $traverse);
			}

			return $has_fields ? $fields_found : $options_acf;
		}

		// Get fields
		public static function acf_get_fields_process(&$options_acf, $acf_field_group_name, $acf_fields, $choices_filter, $raw, $traverse, $prefix = '') {

			foreach($acf_fields as $acf_field) {

				// Get field type
				$acf_field_type = $acf_field['type'];

				// Adjust label if blank
				if($acf_field['label'] == '') {

					$acf_field['label'] = $acf_field['key'];
				}

				// Store meta box name
				$acf_field['wsf_group_name'] = $acf_field_group_name;

				// Only return fields that have choices
				$process_field = true;
				if(
					$choices_filter &&
					(
						!isset($acf_field['choices']) ||
						!is_array($acf_field['choices']) ||
						(count($acf_field['choices']) == 0)
					)
				) {
					$process_field = false;
				}

				if($process_field) {

					if($raw) {

						$options_acf[$acf_field['key']] = $acf_field;

					} else {

						// Check if mappable
						if(self::acf_field_mappable($acf_field_type)) {

							$options_acf[] = array('value' => $acf_field['key'], 'text' => sprintf('%s%s - %s', $acf_field_group_name, $prefix, $acf_field['label']));
						}
					}
				}

				// Check for sub fields
				if($traverse) {

					if(
						isset($acf_field['sub_fields']) &&
						is_array($acf_field['sub_fields']) &&
						(count($acf_field['sub_fields']) > 0)
					) {
						self::acf_get_fields_process($options_acf, $acf_field_group_name, $acf_field['sub_fields'], $choices_filter, $raw, $traverse, $prefix . ' - ' . $acf_field['label']);
					}
				}
			}
		}

		// Get field data
		public static function acf_get_field_data($selector, $field_objects = false, $parent_value = false, $parent_type = false) {

			if($field_objects === false) {

				$field_objects = get_field_objects($selector, false);
				if($field_objects === false) { return array(); }
			}

			$return_array = array();

			foreach($field_objects as $field_object) {

				$field_name = $field_object['name'];
				$field_key = $field_object['key'];
				$field_value = $field_object['value'];
				$field_type = $field_object['type'];

				if(isset($field_object['sub_fields'])) {

					$return_array = array_merge($return_array, self::acf_get_field_data($selector, $field_object['sub_fields'], $field_value, $field_type));

				} else {

					switch($parent_type) {

						case 'repeater' :

							$return_array[$field_key] = array(

								'repeater' => true,
								'values' => array()
							);

							if(is_array($parent_value)) {

								foreach($parent_value as $repeater_index => $repeater_values) {

									$field_value = (isset($repeater_values[$field_key])) ? $repeater_values[$field_key] : '';
									$return_array[$field_key]['values'][$repeater_index] = $field_value;
								}
							}

							break;

						case 'group' :

							$field_value = (isset($parent_value[$field_key])) ? $parent_value[$field_key] : '';
							$return_array[$field_key] = array('repeater' => false, 'values' => $field_value);
							break;

						default :

							$return_array[$field_key] = array('repeater' => false, 'values' => $field_value);
					}
				}
			}

			return $return_array;
		}

		// Process ACF fields
		public static function acf_fields_to_list_fields($fields, $group_index = 0, $section_index = 1, $field_index = 1, $depth = 0) {

			$list_fields = array();

			$wsf_group_name_last = false;

			foreach($fields as $field) {

				// Skip ACF extended internal fields
				if(strpos($field['key'], 'acfe_') === 0) { continue; }

				// Get field type
				$action_type = $field['type'];
				$type = self::acf_action_field_type_to_ws_form_field_type($field);
				if($type === false) { continue; }

				// Get meta
				$meta = self::acf_action_field_to_ws_form_meta_keys($field);

				// Get sort index
				$sort_index = $field_index + intval($field['menu_order']);

				// Adjust label if blank
				if($field['label'] == '') {

					$field['label'] = __('(no label)', 'acf');
					$meta['label_render'] = '';
				}

				// Tabs
				switch($action_type) {

					case 'tab' :

						$group_index++;
						$section_index = 0;
						$field_index = 1;

						continue 2;
				}

				// Section names
				$wsf_group_name = isset($field['wsf_group_name']) ? $field['wsf_group_name'] : false;

				if(
					($depth === 0) &&
					($wsf_group_name !== false) &&
					($wsf_group_name !== $wsf_group_name_last)
				) {

					if(
						!(
							($section_index === 0) &&
							($field_index === 1) 
						)
					) {

						$section_index++;
					}

					$wsf_group_name_last = $wsf_group_name;
				}

				// Groups
				switch($action_type) {

					case 'repeater' :
					case 'group' :

						if(isset($field['sub_fields'])) {

							$acf_fields_to_list_fields_return = self::acf_fields_to_list_fields($field['sub_fields'], $group_index, $section_index + 1, 1, $depth + 1);
							if(count($acf_fields_to_list_fields_return['list_fields']) > 0) {

								$section_index++;
								$field_index = 1;

								$list_fields = array_merge($list_fields, $acf_fields_to_list_fields_return['list_fields']);

								$section_index++;
								$field_index = 1;
							}
						}

						continue 2;
				}

				$list_fields_single = array(

					'id' => 				$field['key'],
					'label' => 				$field['label'], 
					'label_field' => 		$field['label'], 
					'type' => 				$type,
					'action_type' =>		$action_type,
					'required' => 			(isset($field['required']) ? ($field['required'] == 1) : false),
					'default_value' => 		(isset($field['default_value']) ? $field['default_value'] : ''),
					'pattern' => 			(isset($field['pattern']) ? $field['pattern'] : ''),
					'placeholder' => 		(isset($field['placeholder']) ? $field['placeholder'] : ''),
					'group_index' =>		$group_index,
					'section_index' => 		$section_index,
					'sort_index' => 		$sort_index,
					'visible' =>			true,
					'meta' => 				$meta,
					'no_map' =>				true
				);

				// Help
				if(
					isset($field['instructions']) &&
					!empty($field['instructions'])
				) {

					$list_fields_single['help'] = $field['instructions'];
				}

				// Width
				if(
					isset($field['wrapper']) &&
					isset($field['wrapper']['width'])
				) {

					$wrapper_width = floatval($field['wrapper']['width']);

					if(
						($wrapper_width > 0) &&
						($wrapper_width <= 100)
					) {

						$list_fields_single['width_factor'] = ($wrapper_width / 100);
					}
				}

				$list_fields[] = $list_fields_single;
			}

			return array('list_fields' => $list_fields, 'group_index' => $group_index, 'section_index' => $section_index);
		}

		// Convert action field to WS Form meta key
		public static function acf_action_field_to_ws_form_meta_keys($field) {

			$meta_return = array();

			$type = $field['type'];
			$default_value = isset($field['default_value']) ? $field['default_value'] : '';

			// Max length
			if(
				isset($field['maxlength']) &&
				($field['maxlength'] != '')
			) {

				$meta_return['max_length'] = intval($field['maxlength']);
			}

			// Layout
			if(isset($field['layout'])) {

				switch($field['layout']) {

					case 'vertical' :

						$meta_return['orientation'] = 'vertical';
						break;

					default :

						$meta_return['orientation'] = 'horizontal';
						break;
				}
			}

			// Get WS Form meta configurations for action field types
			switch($type) {

				// Build data grids for radio and select
				case 'select' :
				case 'checkbox' :
				case 'radio' :
				case 'true_false' :
				case 'button_group' :
				case 'post_object' :
				case 'page_link' :
				case 'relationship' :
				case 'taxonomy' :
				case 'user' :

					switch($type) {

						case 'post_object' :
						case 'page_link' :
						case 'relationship' :

							$meta_key = 'data_grid_select';
							$meta_return['select_field_label'] = 1;

							// Data source set-up
							$meta_return = WS_Form_Data_Source::get_data_source_meta('post', $meta_return);

							// Set up data source
							$meta_return['data_source_id'] = 'post';

							// Multiple
							if(
								(isset($field['multiple']) && ($field['multiple'] == 1)) ||
								($type == 'relationship')
							) {

								$meta_return['multiple'] = 'on';
								$meta_return['placeholder_row'] = '';

							} else {

								// Allow Null
								if(
									isset($field['allow_null']) &&
									($field['allow_null'] == 0)
								) {

									$meta_return['placeholder_row'] = '';
								}
							}

							// Post types
							$post_types = isset($field['post_type']) ? $field['post_type'] : array();
							if(!is_array($post_types)) { $post_types = array(); }
							$meta_return['data_source_post_filter_post_types'] = array();
							foreach($post_types as $post_type) {

								$meta_return['data_source_post_filter_post_types'][] = array(

									'data_source_post_post_types' => $post_type
								);
							}

							// Terms
							$terms = isset($field['taxonomy']) ? $field['taxonomy'] : array();
							if(!is_array($terms)) { $terms = array(); }
							$meta_return['data_source_post_filter_terms'] = array();
							foreach($terms as $term) {

								// Look up term ID
								$acf_taxonomy_term_array = explode(':', $term);
								if(count($acf_taxonomy_term_array) !== 2) { continue; }

								$taxonomy_name = $acf_taxonomy_term_array[0];
								$term_name = $acf_taxonomy_term_array[1];

								$term = get_term_by('slug', $term_name, $taxonomy_name);
								if($term === false) { continue; }

								$meta_return['data_source_post_filter_terms'][] = array(

									'data_source_post_terms' => $term->term_id
								);
							}

							$choices = array();
							break;

						case 'taxonomy' :

							switch($field['field_type']) {

								case 'checkbox' :

									$meta_key = 'data_grid_checkbox';
									$meta_return['checkbox_field_label'] = 1;
									break;

								case 'multi_select' :

									$meta_key = 'data_grid_select';
									$meta_return['multiple'] = 'on';
									$meta_return['select_field_label'] = 1;
									break;

								case 'radio' :

									$meta_key = 'data_grid_radio';
									$meta_return['radio_field_label'] = 1;
									break;

								case 'select' :

									$meta_key = 'data_grid_select';
									$meta_return['select_field_label'] = 1;
									break;
							}

							// Data source set-up
							$meta_return = WS_Form_Data_Source::get_data_source_meta('term', $meta_return);

							// Set up data source
							$meta_return['data_source_id'] = 'term';

							// Allow Null
							if(
								isset($field['allow_null']) &&
								($field['allow_null'] == 0)
							) {

								$meta_return['placeholder_row'] = '';
							}

							// Taxonomy
							$taxonomy = isset($field['taxonomy']) ? $field['taxonomy'] : false;
							if($taxonomy !== false) {

								$meta_return['data_source_term_filter_taxonomies'] = array(

									array(

										'data_source_term_taxonomies' => $taxonomy
									)
								);
							}

							$choices = array();
							break;

						case 'user' :

							$meta_key = 'data_grid_select';
							$meta_return['select_field_label'] = 1;

							// Data source set-up
							$meta_return = WS_Form_Data_Source::get_data_source_meta('user', $meta_return);

							// Set up data source
							$meta_return['data_source_id'] = 'user';

							// Multiple
							if(
								isset($field['multiple']) &&
								($field['multiple'] == 1)
							) {

								$meta_return['multiple'] = 'on';
								$meta_return['placeholder_row'] = '';

							} else {

								// Allow Null
								if(
									isset($field['allow_null']) &&
									($field['allow_null'] == 0)
								) {

									$meta_return['placeholder_row'] = '';
								}
							}

							// Roles
							$roles = isset($field['role']) ? $field['role'] : array();
							if(!is_array($roles)) { $roles = array(); }
							$meta_return['data_source_user_filter_roles'] = array();
							foreach($roles as $role) {

								$meta_return['data_source_user_filter_roles'][] = array(

									'data_source_user_roles' => $role
								);
							}

							$choices = array();
							break;

						case 'select' :

							$meta_key = 'data_grid_select';
							$meta_return['select_field_label'] = 1;

							// Data source set-up
							$meta_return = WS_Form_Data_Source::get_data_source_meta('acf', $meta_return);

							// Set up data source
							$meta_return['data_source_id'] = 'acf';
							$meta_return['data_source_acf_field_key'] = $field['key'];

							// Multiple
							if(
								isset($field['multiple']) &&
								($field['multiple'] == 1)
							) {

								$meta_return['multiple'] = 'on';
								$meta_return['placeholder_row'] = '';

							} else {

								// Allow Null
								if(
									isset($field['allow_null']) &&
									($field['allow_null'] == 0)
								) {

									$meta_return['placeholder_row'] = '';
								}
							}

							$choices = isset($field['choices']) ? $field['choices'] : array();
							break;

						case 'checkbox' :

							$meta_key = 'data_grid_checkbox';
							$meta_return['checkbox_field_label'] = 1;

							// Data source set-up
							$meta_return = WS_Form_Data_Source::get_data_source_meta('acf', $meta_return);

							// Set up data source
							$meta_return['data_source_id'] = 'acf';
							$meta_return['data_source_acf_field_key'] = $field['key'];

							// Toggle (Select All)
							if(
								isset($field['toggle']) &&
								($field['toggle'] == 1)
							) {

								$meta_return['select_all'] = 'on';
							}

							$choices = isset($field['choices']) ? $field['choices'] : array();
							break;

						case 'radio' :

							$meta_key = 'data_grid_radio';
							$meta_return['radio_field_label'] = 1;

							// Data source set-up
							$meta_return = WS_Form_Data_Source::get_data_source_meta('acf', $meta_return);

							// Set up data source
							$meta_return['data_source_id'] = 'acf';
							$meta_return['data_source_acf_field_key'] = $field['key'];

							$choices = isset($field['choices']) ? $field['choices'] : array();
							break;

						case 'true_false' :

							$meta_key = 'data_grid_checkbox';
							$meta_return['checkbox_field_label'] = 1;

							// Stylized UI
							if(
								isset($field['ui']) &&
								($field['ui'] == 1)
							) {

								$meta_return['class_field'] = 'wsf-switch';
							}

							$choices = array('on' => $field['label']);
							break;

						case 'button_group' :

							$meta_key = 'data_grid_radio';
							$meta_return['radio_field_label'] = 1;
							$meta_return['class_field'] = 'wsf-button';

							// Data source set-up
							$meta_return = WS_Form_Data_Source::get_data_source_meta('acf', $meta_return);

							// Set up data source
							$meta_return['data_source_id'] = 'acf';
							$meta_return['data_source_acf_field_key'] = $field['key'];

							$choices = isset($field['choices']) ? $field['choices'] : array();
							break;
					}

					// Get options
					if(!is_array($choices)) { return false; }

					// Get base meta
					$meta_keys = WS_Form_Config::get_meta_keys();
					if(!isset($meta_keys[$meta_key])) { return false; }
					if(!isset($meta_keys[$meta_key]['default'])) { return false; }

					$meta = $meta_keys[$meta_key]['default'];

					// Configure columns
					$meta['columns'] = array(

						array('id' => 0, 'label' => __('Value', 'ws-form')),
						array('id' => 1, 'label' => __('Label', 'ws-form'))
					);

					// Build new rows
					$rows = array();
					$id = 1;
					$default_value = isset($field['default_value']) ? $field['default_value'] : '';
					if($type == 'true_false') {

						if($default_value === 0) { $default_value = ''; }
						if($default_value === 1) { $default_value = 'on'; }
					}
					if(!is_array($default_value)) { $default_value = array($default_value); }

					foreach($choices as $value => $text) {

						$rows[] = array(

							'id'		=> $id,
							'default'	=> (in_array($value, $default_value) ? 'on' : ''),
							'required'	=> '',
							'disabled'	=> '',
							'hidden'	=> '',
							'data'		=> array($value, $text)
						);

						$id++;
					}

					// Modify meta
					$meta['groups'][0]['rows'] = $rows;

					$meta_return[$meta_key] = $meta;

					return $meta_return;

				case 'range' :
				case 'number' :

					if(
						isset($field['min']) &&
						($field['min'] != '')
					) {

						$meta_return['min'] = intval($field['min']);
					}

					if(
						isset($field['max']) &&
						($field['max'] != '')
					) {

						$meta_return['max'] = intval($field['max']);
					}

					if(
						isset($field['step']) &&
						($field['step'] != '')
					) {

						$meta_return['step'] = intval($field['step']);
					}

					return $meta_return;

				case 'google_map' :

					$meta_return['google_map_lat'] = isset($field['center_lat']) ? $field['center_lat'] : '';
					$meta_return['google_map_lng'] = isset($field['center_lng']) ? $field['center_lng'] : '';
					$height = isset($field['height']) ? intval($field['height']) : 0;
					if($height > 0) { $meta_return['google_map_height'] = $height . 'px'; }
					$meta_return['google_map_zoom'] = isset($field['zoom']) ? $field['zoom'] : '';

					return $meta_return;

				case 'date_picker' :

					$meta_return['input_type_datetime'] = 'date';
					return $meta_return;

				case 'date_time_picker' :

					$meta_return['input_type_datetime'] = 'datetime-local';
					return $meta_return;

				case 'time_picker' :

					$meta_return['input_type_datetime'] = 'time';
					return $meta_return;

				case 'wysiwyg' :

					global $wp_version;
					if(WS_Form_Common::version_compare($wp_version, '4.8') >= 0) {
						$meta_return['input_type_textarea'] = 'tinymce';
					}
					return $meta_return;

				case 'textarea' :

					if(
						isset($field['rows']) &&
						($field['rows'] != '')
					) {

						$meta_return['rows'] = intval($field['rows']);
					}
					return $meta_return;

				case 'message' :

					if(
						isset($field['message']) &&
						($field['message'] != '')
					) {

						$meta_return['text_editor'] = $field['message'];
					}
					return $meta_return;

				case 'file' :
				case 'image' :
				case 'gallery' :

					// Convert ACF file extensions to MIME types
					if(
						isset($field['mime_types']) &&
						($field['mime_types'] != '')
					) {

						$mime_types = get_allowed_mime_types();
						$mime_type_lookup = array();

						foreach($mime_types as $extensions => $mime_type) {

							$extension_array = explode('|', $extensions);

							foreach($extension_array as $extension) {

								$mime_type_lookup[$extension] = $mime_type;
							}
						}

						$acf_file_types = $field['mime_types'];

						$file_type_array = explode(',', $acf_file_types);

						foreach($file_type_array as $index => $extension) {

							$extension = strtolower(trim(str_replace('.', '', $extension)));

							if(isset($mime_type_lookup[$extension])) {

								$file_type_array[$index] = $mime_type_lookup[$extension];

							} else {

								$file_type_array[$index] = $extension;
							}
						}

						$file_type_array = array_unique($file_type_array);

						$accept = implode(',', $file_type_array);

						$meta_return['accept'] = $accept;
					}

					// File handler
					$meta_return['file_handler'] = 'attachment';

					if(
						isset($field['min']) &&
						($field['min'] != '')
					) {

						$meta_return['file_min'] = intval($field['min']);
					}

					if(
						isset($field['max']) &&
						($field['max'] != '')
					) {

						$meta_return['file_max'] = intval($field['max']);
					}

					if(
						isset($field['min_size']) &&
						($field['min_size'] != '')
					) {

						$meta_return['file_min_size'] = floatval($field['min_size']);
					}

					if(
						isset($field['max_size']) &&
						($field['max_size'] != '')
					) {

						$meta_return['file_max_size'] = floatval($field['max_size']);
					}

					if(
						isset($field['max_width']) &&
						($field['max_width'] != '')
					) {

						$meta_return['file_image_max_width'] = intval($field['max_width']);
					}

					if(
						isset($field['max_height']) &&
						($field['max_height'] != '')
					) {

						$meta_return['file_image_max_height'] = intval($field['max_height']);
					}

					switch($type) {

						case 'file' :
						case 'image' :

							$meta_return['sub_type'] = 'dropzonejs';
							break;

						case 'gallery' :

							$meta_return['sub_type'] = 'dropzonejs';
							$meta_return['multiple_file'] = 'on';
							break;
					}

					return $meta_return;

				default :

					return false;
			}
		}

		// Process ACF fields
		public static function acf_fields_to_meta_data($fields, $group_index = 0, $section_index = 1, $field_index = 1, $depth = 0) {

			$list_fields = array();
			$group_meta_data = array();
			$section_meta_data = array();

			$wsf_group_name_last = false;

			foreach($fields as $field) {

				$action_type = $field['type'];
				$type = self::acf_action_field_type_to_ws_form_field_type($field);

				// Skip unsupported field types
				if($type === false) { continue; }

				// Section names
				$wsf_group_name = isset($field['wsf_group_name']) ? $field['wsf_group_name'] : false;

				// Tabs
				switch($action_type) {

					case 'tab' :

						$group_index++;
						$section_index = 0;
						$field_index = 1;

						if(!isset($group_meta_data['group_' . $group_index])) { $group_meta_data['group_' . $group_index] = array(); }
						$group_meta_data['group_' . $group_index]['label'] = $field['label'];

						if(!isset($section_meta_data['group_' . $group_index]['section_' . $section_index])) { $section_meta_data['group_' . $group_index]['section_' . $section_index] = array(); }
						$section_meta_data['group_' . $group_index]['section_' . $section_index]['label'] = $wsf_group_name;

						continue 2;
				}

				// Group name
				if(
					($depth === 0) &&
					($wsf_group_name !== false) &&
					($wsf_group_name !== $wsf_group_name_last)
				) {

					if(empty($wsf_group_name)) { $wsf_group_name = WS_FORM_DEFAULT_GROUP_NAME; }

					if(
						!(
							($section_index === 0) &&
							($field_index === 1) 
						)
					) {

						$section_index++;
					}

					if(!isset($section_meta_data['group_' . $group_index]['section_' . $section_index])) { $section_meta_data['group_' . $group_index]['section_' . $section_index] = array(); }
					$section_meta_data['group_' . $group_index]['section_' . $section_index]['label'] = $wsf_group_name;

					$wsf_group_name_last = $wsf_group_name;
				}

				// Repeaters & Groups
				switch($action_type) {

					case 'repeater' :
					case 'group' :

						if(isset($field['sub_fields'])) {

							$acf_fields_to_meta_data_return = self::acf_fields_to_meta_data($field['sub_fields'], $group_index, $section_index + 1, 1, $depth + 1);
							if(count($acf_fields_to_meta_data_return['list_fields']) > 0) {

								$section_index++;
								$field_index = 1;

								if(!isset($section_meta_data['group_' . $group_index])) { $section_meta_data['group_' . $group_index] = array(); }
								if(!isset($section_meta_data['group_' . $group_index]['section_' . $section_index])) { $section_meta_data['group_' . $group_index]['section_' . $section_index] = array(); }

								if(isset($field['wrapper'])) {

									if(isset($field['wrapper']['width'])) {

										$wrapper_width = floatval($field['wrapper']['width']);

										if(
											($wrapper_width > 0) &&
											($wrapper_width <= 100)
										) {

											$section_meta_data['group_' . $group_index]['section_' . $section_index]['width_factor'] = ($wrapper_width / 100);
										}		
									}

									if(isset($field['wrapper']['class'])) {

										$wrapper_class = $field['wrapper']['class'];

										if($wrapper_class != '') {

											$section_meta_data['group_' . $group_index]['section_' . $section_index]['class_section_wrapper'] = $wrapper_class;
										}
									}
								}

								$group_meta_data = array_merge($group_meta_data, $acf_fields_to_meta_data_return['group_meta_data']);
								$section_meta_data = array_merge($section_meta_data, $acf_fields_to_meta_data_return['section_meta_data']);

								$section_meta_data['group_' . $group_index]['section_' . $section_index]['label'] = $field['label'];
								$section_meta_data['group_' . $group_index]['section_' . $section_index]['label_render'] = 'on';

								if($action_type === 'repeater') {

									$section_meta_data['group_' . $group_index]['section_' . $section_index]['section_repeatable'] = 'on';

									if(
										isset($field['min']) &&
										($field['min'] != '')
									) {

										$section_meta_data['group_' . $group_index]['section_' . $section_index]['section_repeatable_min'] = intval($field['min']);
									}

									if(
										isset($field['max']) &&
										($field['max'] != '')
									) {

										$section_meta_data['group_' . $group_index]['section_' . $section_index]['section_repeatable_max'] = intval($field['max']);
									}
								}

								$section_index++;
								$field_index = 1;

								if(!isset($section_meta_data['group_' . $group_index]['section_' . $section_index])) { $section_meta_data['group_' . $group_index]['section_' . $section_index] = array(); }
								$section_meta_data['group_' . $group_index]['section_' . $section_index]['label'] = $wsf_group_name;
							}
						}

						continue 2;
				}

				// Dummy entry
				$list_fields[] = array();

				$field_index++;
			}

			return array('list_fields' => $list_fields, 'group_meta_data' => $group_meta_data, 'section_meta_data' => $section_meta_data, 'group_index' => $group_index, 'section_index' => $section_index);
		}

		// Parent field crawler
		public static function acf_repeater_field_walker($parent_field_array, $sub_field, $post) {

			$return_value = '';

			$parent_field = array_shift($parent_field_array);

			if(have_rows($parent_field, $post->ID)) {

				while(have_rows($parent_field, $post->ID)) {

					the_row();

					$row = get_row();

					if(count($parent_field_array) == 0) {

						$sub_field_value = get_sub_field($sub_field);

						if($sub_field_value !== false) { return $sub_field_value; }

					} else {

						$return_value = self::acf_repeater_field_walker($parent_field_array, $sub_field);
					}
				}
			}

			return $return_value;
		}

		// Get parent key data for repeatables. We need this to be able to add the repeatable field meta data.
		public static function acf_get_parent_data($acf_key, $repeater_index = 0) {

			$field_object = get_field_object($acf_key);
			if($field_object === false) { return false; }

			$meta_key = $field_object['name'];

			$parent = isset($field_object['parent']) ? $field_object['parent'] : 0;

			if(is_numeric($parent)) {

				if($parent > 0) {

					$post = get_post($parent);

					if($post && ($post->post_type == 'acf-field')) {

						$acf_key = $post->post_name;

					} else {
						
						$acf_key = false;
					}

				} else {

					$acf_key = false;
				}

			} else {
				
				$acf_key = $parent;
			}

			if(!empty($acf_key)) {
				
				$field_object_parent = get_field_object($acf_key);
				if($field_object_parent === false) { return false; }

				return array(

					'meta_key' => $field_object_parent['name'],
					'acf_key' => $field_object_parent['key'],
					'type' => $field_object_parent['type']
				);
			}

			return false;
		}

		// Process acf_field_values as file
		public static function acf_field_values_file($acf_field_values) {

			$return_array = array();

			// Process attachment IDs
			if(!is_array($acf_field_values)) { $acf_field_values = array($acf_field_values); }

			foreach($acf_field_values as $acf_field_value_single) {

				$attachment_id = intval($acf_field_value_single);
				if(!$attachment_id) { continue; }

				$file_object = WS_Form_File_Handler::get_file_object_from_attachment_id($attachment_id);
				if($file_object === false) { continue; }

				$return_array[] = $file_object;
			}

			return (count($return_array) > 0) ? $return_array : false;
		}

		// Process acf_field_values as boolean
		public static function acf_field_values_boolean($acf_field_values, $field_id, $fields, $field_types) {

			// Get meta value array (Array containing values of data grid)
			$meta_value_array = WS_Form_Common::get_meta_value_array($field_id, $fields, $field_types);
			$true_array = array('1', 'on', 'yes', 'true');

			// Get first element if array
			if(is_array($acf_field_values)) { $acf_field_values = isset($acf_field_values[0]) ? $acf_field_values[0] : ''; }

			$acf_field_values = strtolower($acf_field_values);

			return in_array($acf_field_values, $true_array) ? $meta_value_array[0] : false;
		}

		// Process acf_field_values as date
		public static function acf_field_values_date_time($acf_field_values, $field_id, $acf_field_type) {

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

			switch($acf_field_type) {

				case 'date_picker' :

					return date($format_date, strtotime($acf_field_values));

				case 'date_time_picker' :

					return date($format_date . ' ' . $format_time, strtotime($acf_field_values));

				case 'time_picker' :

					return date($format_time, strtotime($acf_field_values));
			}

			return '';
		}

		// Get field type
		public static function acf_get_field_type($acf_key) {

			$field_object = get_field_object($acf_key);
			if($field_object === false) { return false; }

			return $field_object['type'];
		}

		// Get file field types
		public static function acf_get_field_types_file() {

			return array(

				'image',
				'file',
				'gallery'
			);
		}

		// Convert ACF meta value to WS Form field
		public static function acf_acf_meta_value_to_ws_form_field_value($acf_field_values, $acf_field_type, $acf_field_repeater, $field_id, $fields, $field_types) {

			switch($acf_field_type) {

				case 'file' :
				case 'image' :
				case 'gallery' :

					if($acf_field_repeater) {

						// Process repeated attachment IDs
						foreach($acf_field_values as $acf_field_values_index => $acf_field_value) {

							$acf_field_values[$acf_field_values_index] = self::acf_field_values_file($acf_field_value);
						}

					} else {

						// Process regular attachment IDs
						$acf_field_values = self::acf_field_values_file($acf_field_values);
					}

					break;

				case 'true_false' :

					if($acf_field_repeater) {

						// Process repeated true false values
						foreach($acf_field_values as $acf_field_values_index => $acf_field_value) {

							$acf_field_values[$acf_field_values_index] = self::acf_field_values_boolean($acf_field_value, $field_id, $fields, $field_types);
						}

					} else {

						// Process regular true false value
						$acf_field_values = self::acf_field_values_boolean($acf_field_values, $field_id, $fields, $field_types);
					}

					break;

				case 'date_picker' :
				case 'date_time_picker' :
				case 'time_picker' :

					if($acf_field_repeater) {

						// Process repeated date
						foreach($acf_field_values as $acf_field_values_index => $acf_field_value) {

							$acf_field_values[$acf_field_values_index] = self::acf_field_values_date_time($acf_field_value, $field_id, $acf_field_type);
						}

					} else {

						// Process regular date
						$acf_field_values = self::acf_field_values_date_time($acf_field_values, $field_id, $acf_field_type);
					}

					break;
			}

			return $acf_field_values;
		}

		// Convert WS Form field value to ACF meta value
		public static function acf_ws_form_field_value_to_acf_meta_value($meta_value, $acf_field_type, $field_id) {

			if($meta_value == '') { return ''; }

			switch($acf_field_type) {

				case 'date_picker' :
				case 'date_time_picker' :
				case 'time_picker' :

					try {

						$ws_form_field = new WS_Form_Field();
						$ws_form_field->id = $field_id;
						$field_object = $ws_form_field->db_read(true, true);

					} catch (Exception $e) {

						return '';
					}
			}

			switch($acf_field_type) {

				case 'date_picker' :

					$meta_value = WS_Form_Common::get_date_by_type($meta_value, $field_object, 'Ymd');
					break;

				case 'date_time_picker' :

					$meta_value = WS_Form_Common::get_date_by_type($meta_value, $field_object, 'Y-m-d H:i:s');
					break;

				case 'time_picker' :

					$meta_value = WS_Form_Common::get_date_by_type($meta_value, $field_object, 'H:i:s');
					break;
			}

			return $meta_value;
		}

		// Convert action field type to WS Form field type
		public static function acf_action_field_type_to_ws_form_field_type($field) {

			$type = $field['type'];

			switch($type) {

				// Basic
				case 'text' : return 'text';
				case 'textarea' : return 'textarea';
				case 'number' : return 'number';
				case 'range' : return 'range';
				case 'email' : return 'email';
				case 'url' : return 'url';
				case 'password' : return 'password';

				// Content
				case 'image' : return 'file';
				case 'file' : return 'file';
				case 'wysiwyg' : return 'textarea';
				case 'gallery' : return 'file';
				case 'oembed' : return 'url';

				// Choice
				case 'select' : return 'select';
				case 'checkbox' : return 'checkbox';
				case 'radio' : return 'radio';
				case 'button_group' : return 'radio';
				case 'true_false' : return 'checkbox';

				// jQuery
				case 'google_map' : return 'googlemap';
				case 'date_picker' : return 'datetime';
				case 'date_time_picker' : return 'datetime';
				case 'time_picker' : return 'datetime';
				case 'color_picker' : return 'color';

				// Layout
				case 'message' : return 'texteditor';
				case 'tab' : return 'tab';
				case 'group' : return 'group';
				case 'repeater' : return 'repeater';

				// Relational
				case 'post_object' : return 'select';
				case 'page_link' : return 'select';
				case 'relationship' : return 'select';
				case 'user' : return 'select';

				case 'taxonomy' :

					switch($field['field_type']) {

						case 'checkbox' : return 'checkbox';
						case 'multi_select' : return 'select';
						case 'radio' : return 'radio';
						case 'select' : return 'select';
					}
			}

			return false;
		}

		// Fields that we can push data to
		public static function acf_field_mappable($acf_field_type) {

			switch($acf_field_type) {

				// Basic
				case 'text' :
				case 'textarea' :
				case 'number' :
				case 'range' :
				case 'email' :
				case 'url' :
				case 'password' :

				// Content
				case 'image' :
				case 'file' :
				case 'wysiwyg' :
				case 'gallery' :
				case 'oembed' :

				// Choice
				case 'select' :
				case 'checkbox' :
				case 'radio' :
				case 'button_group' :
				case 'true_false' :

				// jQuery
				case 'google_map' :
				case 'date_picker' :
				case 'date_time_picker' :
				case 'time_picker' :
				case 'color_picker' :

				// Relational
				case 'post_object' :
				case 'page_link' :
				case 'relationship' :
				case 'user' :
				case 'taxonomy' :

					return true;

				default :

					return false;
			}
		}

		// Field validation
		public static function acf_validate_value(&$submit, $field_id, $section_repeatable_index, $value, $acf_key, $input) {

			$field = get_field_object($acf_key);
			$valid = true;

			// Check if field is required
			if($field['required']) {

				// Valid is set to false if the value is empty, but allow 0 as a valid value
				if(empty($value) && !is_numeric($value)) {
					
					$valid = false;	
				}
			}
			
			// Apply filters
			$valid = apply_filters( "acf/validate_value/type={$field['type']}",		$valid, $value, $field, $input );
			$valid = apply_filters( "acf/validate_value/name={$field['_name']}", 	$valid, $value, $field, $input );
			$valid = apply_filters( "acf/validate_value/key={$field['key']}", 		$valid, $value, $field, $input );
			$valid = apply_filters( "acf/validate_value", 							$valid, $value, $field, $input );

			// Check valid variable
			if(is_string($valid)) {

				$submit->error_validation_actions[] = array(

					'action' 					=> 'field_invalid_feedback',
					'field_id' 					=> $field_id,
					'section_repeatable_index' 	=> $section_repeatable_index,
					'message' 					=> $valid
				);
			}

			if($valid === false) {

				$submit->error_validation_actions[] = array(

					'action' 					=> 'field_invalid_feedback',
					'field_id' 					=> $field_id,
					'section_repeatable_index' 	=> $section_repeatable_index,
					'message' 					=> sprintf(__('%s value is required', 'acf'), $field['label'])
				);
			}

			return $valid;
		}

		// Legacy - TO DO: Remove in future

		// Get fields
		public static function acf_get_fields(&$options_acf, $acf_field_group_name, $acf_fields, $choices_filter = false, $prefix = '') {

			foreach($acf_fields as $acf_field) {

				// Check for sub fields
				if(isset($acf_field['sub_fields'])) {

					$acf_fields = $acf_field['sub_fields'];

					self::acf_get_fields($options_acf, $acf_field_group_name, $acf_fields, $choices_filter, $prefix . ' - ' . $acf_field['label']);

				} else {

					// Only return fields that have choices
					if(
						$choices_filter &&
						(
							!isset($acf_field['choices']) ||
							(count($acf_field['choices']) == 0)
						)
					) {
						continue;
					}

					$options_acf[] = array('value' => $acf_field['key'], 'text' => sprintf('%s%s - %s', $acf_field_group_name, $prefix, $acf_field['label']));
				}
			}
		}

		// Get field meta key
		public static function acf_get_field_meta_key($acf_key, $repeater_index = 0) {

			$field_object = get_field_object($acf_key);
			if($field_object === false) { return false; }

			$meta_key = $field_object['name'];

			$parent = isset($field_object['parent']) ? $field_object['parent'] : 0;

			if(is_numeric($parent)) {

				$post = get_post($parent);

				if($post && ($post->post_type == 'acf-field')) {

					$acf_key = $post->post_name;

				} else {
					
					$acf_key = false;
				}

			} else {
				
				$acf_key = $parent;
			}
			   
			if(!empty($acf_key)) {
				
				$field_object_parent = get_field_object($acf_key);

				if($field_object_parent !== false) {

					$repeater_index_string = '';
					if($field_object_parent['type'] == 'repeater') {

						$repeater_index_string = $repeater_index . '_';
					}

					$meta_key = self::acf_get_field_meta_key($acf_key, $repeater_index) . '_' . $repeater_index_string . $meta_key;
				}
			}

			return $meta_key;
		}
	}