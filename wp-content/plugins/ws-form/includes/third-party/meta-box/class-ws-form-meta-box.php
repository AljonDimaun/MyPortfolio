<?php

	class WS_Form_Meta_Box {

		public static $meta_box_fields = false;

		// Get fields all
		public static function meta_box_get_fields_all($object_type = false, $post_types = false, $choices_filter = false, $raw = false, $traverse = false, $has_fields = false) {

			// Meta Box fields
			$fields = array();

			// Field found flag
			$fields_found = false;

			// Get meta boxes for current post type
			$meta_box_registry = rwmb_get_registry('meta_box');

			// Check if object type specified
			if($object_type === false) {

				$meta_boxes = $meta_box_registry->all();

			} else {

				$get_by_args = array(

					'object_type' => $object_type
				);

				$meta_boxes = $meta_box_registry->get_by($get_by_args);
			}

			// Process each meta box
			foreach($meta_boxes as $meta_box_id => $meta_box) {

				// Get meta_box from object
				$meta_box = $meta_box->meta_box;

				// Filter by post type if specified
				if(
					($post_types !== false) &&
					!in_array($post_types[0], $meta_box['post_types'])
				) {
					continue;
				}

				// Get meta box title
				$meta_box_field_group_name = $meta_box['title'];

				// Get meta box fields
				$meta_box_fields = $meta_box['fields'];

				// If only looking to see if fields exist ...
				if($has_fields && (count($meta_box_fields) > 0)) { $fields_found = true; break; }

				// Read column, tab and validation data
				$meta_box_columns = isset($meta_box['columns']) ? $meta_box['columns'] : array();
				$meta_box_tabs = isset($meta_box['tabs']) ? $meta_box['tabs'] : array();
				$meta_box_validation_rules = (isset($meta_box['validation']) && isset($meta_box['validation']['rules'])) ? $meta_box['validation']['rules'] : array();

				// Process fields
				self::meta_box_get_fields_process($fields, $meta_box_field_group_name, $meta_box_fields, $meta_box_columns, $meta_box_tabs, $meta_box_validation_rules, $choices_filter, $raw, $traverse);
			}

			return $has_fields ? $fields_found : $fields;
		}

		// Get fields
		public static function meta_box_get_fields_process(&$fields, $meta_box_field_group_name, $meta_box_fields, $meta_box_columns, $meta_box_tabs, $meta_box_validation_rules, $choices_filter, $raw, $traverse, $prefix = '', $parent_field_id = '', $parent_field_type = '', $parent_field_clone = false) {

			foreach($meta_box_fields as $meta_box_field_id => $meta_box_field) {

				// Get field type
				$meta_box_field_type = $meta_box_field['type'];

				// Field data checks
				if(!isset($meta_box_field['id']) || ($meta_box_field['id'] === '')) { $meta_box_field['id'] = sprintf('divider_%s', uniqid()); }
				if(!isset($meta_box_field['name']) || ($meta_box_field['name'] == '')) { $meta_box_field['name'] = ucfirst($meta_box_field_type); }

				// Get field ID
				$meta_box_field_id = $meta_box_field['id'];
				if($meta_box_field_id === 'post_name') { continue; }

				// Get field name
				$meta_box_field_name = $meta_box_field['name'];

				// Store meta box name
				$meta_box_field['wsf_meta_box_name'] = $meta_box_field_group_name;

				// Get field clone
				$meta_box_field_clone = isset($meta_box_field['clone']) ? $meta_box_field['clone'] : false;

				switch($meta_box_field_type) {

					// Key value
					case 'key_value' :

						// Placeholder
						$placeholder = isset($meta_box_field['placeholder']) ? $meta_box_field['placeholder'] : array();

						// Create two fields
						$meta_box_field_key = $meta_box_field;
						$meta_box_field_value = $meta_box_field;

						// Key
						$meta_box_field_key['id'] = $meta_box_field['id'] . '_key';
						$meta_box_field_key['name'] = __('Key', 'ws-form');
						$meta_box_field_key['type'] = 'text';
						$meta_box_field_key['placeholder'] = isset($placeholder['key']) ? $placeholder['key'] : '';
						$meta_box_field_key['clone'] = 0;
						$meta_box_field_key['columns'] = 6;

						// Value
						$meta_box_field_value['id'] = $meta_box_field['id'] . '_value';
						$meta_box_field_value['name'] = __('Value', 'ws-form');
						$meta_box_field_value['type'] = 'text';
						$meta_box_field_value['placeholder'] = isset($placeholder['value']) ? $placeholder['value'] : '';
						$meta_box_field_value['clone'] = 0;
						$meta_box_field_value['columns'] = 6;

						$meta_box_field['fields'] = array(

							$meta_box_field_key,
							$meta_box_field_value
						);

						break;
				}

				// Column parsing
				if(isset($meta_box_field['column'])) {

					$column = $meta_box_field['column'];

					if(
						is_string($column) &&
						isset($meta_box_columns[$column])
					) {

						$meta_box_field['columns'] = $meta_box_columns[$column];
					}
				}

				// Tab parsing
				if(isset($meta_box_field['tab'])) {

					$tab = $meta_box_field['tab'];

					if(
						is_string($tab) &&
						isset($meta_box_tabs[$tab])
					) {

						$meta_box_field['wsf_tab_name'] = $meta_box_tabs[$tab];
					}
				}

				// Validation rule parsing
				if(isset($meta_box_validation_rules[$meta_box_field_id])) {

					$meta_box_field['wsf_validation_rules'] = $meta_box_validation_rules[$meta_box_field_id];
				}

				// Check for sub fields
				if(
					!$traverse &&
					isset($meta_box_field['fields']) &&
					is_array($meta_box_field['fields']) &&
					(count($meta_box_field['fields']) == 0)
				) {

					foreach($meta_box_field['fields'] as $meta_box_sub_field_index => $meta_box_sub_field) {

						if(!isset($meta_box_sub_field['column'])) { continue; }

						$column = $meta_box_sub_field['column'];

						if(
							is_string($column) &&
							isset($meta_box_columns[$column])
						) {

							$meta_box_field['fields'][$meta_box_sub_field_index]['columns'] = $meta_box_columns[$column];
						}		
					}
				}

				// Only return fields that have choices
				$process_field = true;
				if(
					$choices_filter &&
					(
						!isset($meta_box_field['options']) ||
						!is_array($meta_box_field['options']) ||
						(count($meta_box_field['options']) == 0) ||
						($meta_box_field_type === 'wysiwyg')			// This field stores visual editor options in 'options'
					)
				) {

					$process_field = false;
				}

				if($process_field) {

					if($raw) {

						if($parent_field_id !== '') {

							$meta_box_field['parent_field_id'] = $parent_field_id;
							$meta_box_field['parent_field_type'] = $parent_field_type;
							$meta_box_field['parent_field_clone'] = $parent_field_clone;
						}

						$fields[$meta_box_field_id] = $meta_box_field;

					} else {

						// Check if mappable
						if(self::meta_box_field_mappable($meta_box_field_type)) {

							$fields[$meta_box_field_id] = array('value' => $meta_box_field_id, 'text' => sprintf('%s%s - %s', $meta_box_field_group_name, $prefix, $meta_box_field_name));
						}
					}
				}

				// Check for sub fields
				if(
					$traverse &&
					isset($meta_box_field['fields']) &&
					is_array($meta_box_field['fields']) &&
					(count($meta_box_field['fields']) > 0)
				) {

					self::meta_box_get_fields_process($fields, $meta_box_field_group_name, $meta_box_field['fields'], $meta_box_columns, $meta_box_tabs, $meta_box_validation_rules, $choices_filter, $raw, $traverse, $prefix . ' - ' . $meta_box_field['name'], $meta_box_field_id, $meta_box_field_type, $meta_box_field_clone);
				}
			}
		}

		// Get field
		public static function meta_box_get_field_settings($meta_box_field_id) {

			// Get Meta Box fields
			if(self::$meta_box_fields === false) {

				// Retrieve fields
				self::$meta_box_fields = WS_Form_Meta_Box::meta_box_get_fields_all(false, false, false, true, true);
			}

			// Check if field ID exists
			if(!isset(self::$meta_box_fields[$meta_box_field_id])) { return false; }

			return self::$meta_box_fields[$meta_box_field_id];
		}

		// Get field data
		public static function meta_box_get_field_data($object_type, $post_types, $object_id) {

			$field_objects = self::meta_box_get_fields_all($object_type, $post_types, false, true, false);
			if($field_objects === false) { return array(); }

			$return_array = array();
			foreach($field_objects as $field_object) {

				// Get field ID
				$meta_box_field_id = $field_object['id'];

				// Get field type
				$meta_box_field_type = $field_object['type'];

				// Get field value
				$field_value = rwmb_get_value($meta_box_field_id, ['object_type' => $object_type], $object_id);

				switch($meta_box_field_type) {

					case 'key_value' :

						// Check field value
						if(!is_array($field_value)) { break; }

						$field_values = array();

						// Split out sub fields
						foreach($field_value as $row_index => $row) {

							foreach($row as $column_index => $meta_box_field_value) {

								$meta_box_field_id_column = ($column_index === 0) ? $meta_box_field_id . '_key' : $meta_box_field_id . '_value';

								if(!isset($field_values[$meta_box_field_id_column])) { $field_values[$meta_box_field_id_column] = array(); }

								$field_values[$meta_box_field_id_column][] = $meta_box_field_value;
							}
						}

						foreach($field_values as $meta_box_field_id => $field_value) {

							// Add to return array
							$return_array[$meta_box_field_id] = array('repeater' => true, 'values' => $field_value);
						}

						break;

					case 'group' :

						// Check field value
						if(!is_array($field_value)) { break; }
						if(count($field_value) === 0) { break; }

						$field_values = array();

						// Check if cloneable or not
						$meta_box_field_settings = self::meta_box_get_field_settings($meta_box_field_id);

						// Clone
						$clone = isset($meta_box_field_settings['clone']) ? $meta_box_field_settings['clone'] : false;

						// Split out sub fields
						if($clone) {

							foreach($field_value as $row_index => $row) {

								foreach($row as $meta_box_field_id => $meta_box_field_value) {

									if(!isset($field_values[$meta_box_field_id])) { $field_values[$meta_box_field_id] = array(); }

									$field_values[$meta_box_field_id][$row_index] = $meta_box_field_value;
								}
							}

						} else {

							foreach($field_value as $meta_box_field_id => $meta_box_field_value) {

								$field_values[$meta_box_field_id] = $meta_box_field_value;
							}
						}

						foreach($field_values as $meta_box_field_id => $field_value) {

							// Add to return array
							$return_array[$meta_box_field_id] = array('repeater' => $clone, 'values' => $field_value);
						}

						break;

					default :

						// Check field value
						if($field_value === false) { $field_value = ''; }

						// Add to return array
						$return_array[$meta_box_field_id] = array('repeater' => false, 'values' => $field_value);
				}
			}

			return $return_array;
		}

		// Process Meta Box fields
		public static function meta_box_fields_to_list_fields($fields, $group_index = 0, $section_index = 1, $field_index = 1, $depth = 0) {

			$list_fields = array();

			$tab_last = false;
			$wsf_meta_box_name_last = false;

			foreach($fields as $field) {

				// Get field type
				$action_type = $field['type'];
				$type = self::meta_box_action_field_type_to_ws_form_field_type($field);

				if($type === false) { continue; }

				// Get meta
				$meta = self::meta_box_action_field_to_ws_form_meta_keys($field);

				// Adjust name if blank
				if($field['name'] == '') {

					$field['name'] = __('(No label)', 'meta-box-builder');
					$meta['label_render'] = '';
				}

				// Tabs
				$tab = isset($field['tab']) ? $field['tab'] : false;

				if($tab !== $tab_last) {

					$group_index++;
					$section_index = 0;
					$field_index = 1;

					$tab_last = $tab;
				}

				// Meta box name
				$wsf_meta_box_name = isset($field['wsf_meta_box_name']) ? $field['wsf_meta_box_name'] : false;

				if(
					($depth === 0) &&
					($wsf_meta_box_name !== false) &&
					($wsf_meta_box_name !== $wsf_meta_box_name_last)
				) {

					if(
						!(
							($section_index === 0) &&
							($field_index === 1) 
						)
					) {

						$section_index++;
					}

					$wsf_meta_box_name_last = $wsf_meta_box_name;
				}

				// Repeaters & Groups
				switch($action_type) {

					case 'heading' :

						if($field_index > 1) {

							$section_index++;
							$field_index = 1;
						}

						// Add heading description
						$desc = isset($field['desc']) ? $field['desc'] : '';
						if(!empty($desc)) {

							$list_fields[] = array(

								'id' => 				$field['id'],
								'label' => 				$field['name'], 
								'label_field' => 		$field['name'], 
								'type' => 				'texteditor',
								'action_type' =>		$field['type'],
								'required' => 			'',
								'default_value' => 		'',
								'pattern' => 			'',
								'placeholder' => 		'',
								'help' =>				'',
								'group_index' =>		$group_index,
								'section_index' => 		$section_index,
								'sort_index' => 		$field_index++,
								'visible' =>			true,
								'meta' => 				array(

									'text_editor' => $desc
								),
								'no_map' =>				true
							);
						}

						continue 2;

					case 'group' :
					case 'key_value' :

						if(isset($field['fields'])) {

							$meta_box_fields_to_list_fields_return = self::meta_box_fields_to_list_fields($field['fields'], $group_index, $section_index + 1, 1, $depth + 1);
							if(count($meta_box_fields_to_list_fields_return['list_fields']) > 0) {

								$section_index++;
								$field_index = 1;

								$list_fields = array_merge($list_fields, $meta_box_fields_to_list_fields_return['list_fields']);

								$section_index++;
								$field_index = 1;
							}
						}

						continue 2;

					case 'map' :
					case 'custom_html' :

						$default_value = '';
						break;

					default :

						$default_value = (isset($field['std']) && !is_array($field['std']) ? $field['std'] : '');
				}

				// Required
				$required = isset($field['required']) ? ($field['required'] == 1) : false;
				if(!WS_Form_Meta_Box::meta_box_field_type_has_required($action_type)) {

					$required = false;
				}

				// Help
				$help = isset($field['desc']) ? $field['desc'] : '';
				if(
					($help === '') &&
					($type === 'range')
				) { 

					$help = '#value';
				}

				$list_fields_single = array(

					'id' => 				$field['id'],
					'label' => 				$field['name'], 
					'label_field' => 		$field['name'], 
					'type' => 				$type,
					'action_type' =>		$action_type,
					'required' => 			$required,
					'default_value' => 		$default_value,
					'pattern' => 			isset($field['pattern']) ? $field['pattern'] : false,
					'placeholder' => 		isset($field['placeholder']) ? $field['placeholder'] : '',
					'help' =>				$help,
					'group_index' =>		$group_index,
					'section_index' => 		$section_index,
					'sort_index' => 		$field_index++,
					'visible' =>			true,
					'meta' => 				$meta,
					'no_map' =>				true
				);

				// Width
				if(
					isset($field['columns'])
				) {

					$columns = $field['columns'];

					if(is_array($columns)) {

						$size = isset($columns['size']) ? $columns['size'] : 12;
						$class = isset($columns['class']) ? $columns['class'] : '';

					} else {

						$size = $field['columns'];
					}

					$size = absint($size);

					if(
						($size > 0) &&
						($size <= 12)
					) {

						$list_fields_single['width_factor'] = ($size / 12);
					}

					if(!empty($class)) {

						if(!isset($list_fields_single['meta']['class_field_wrapper'])) {

							$list_fields_single['meta']['class_field_wrapper'] = $class;

						} else {

							$list_fields_single['meta']['class_field_wrapper'] .= ' ' . $class;
						}
					}
				}

				$list_fields[] = $list_fields_single;
			}

			return array('list_fields' => $list_fields, 'group_index' => $group_index, 'section_index' => $section_index);
		}

		// Convert action field to WS Form meta key
		public static function meta_box_action_field_to_ws_form_meta_keys($field) {

			$meta_return = array();

			$type = $type_original = $field['type'];

			// Multiple
			$multiple = isset($field['multiple']) ? $field['multiple'] : false;

			// Required
			$required = isset($field['required']) ? $field['required'] : false;
			$row_required = false;

			// Check for data source
			$data_source = self::meta_box_get_field_data_source($field);

			// Check for sub type
			$field_type = isset($field['field_type']) ? $field['field_type'] : false;
			if($field_type !== false) { $type = $field_type; }

			// Meta mappings
			$meta_mappings = array(

				'cols' => 'cols',
				'rows' => 'rows',
				'maxlength' => 'max_length',
				'readonly' => 'readonly',
				'multiple' => 'multiple',
				'class' => 'class_field_wrapper',
				'disabled' => 'disabled',
				'prepend' => 'prepend',
				'append' => 'append',
				'min' => 'min',
				'max' => 'max',
				'step' => 'step'
			);

			foreach($meta_mappings as $meta_box_meta_key => $ws_form_meta_key) {

				if(
					isset($field[$meta_box_meta_key]) &&
					($field[$meta_box_meta_key] != '')
				) {

					if($field[$meta_box_meta_key] === true) {

						// True
						$ws_form_meta_value = 'on';

					} elseif($field[$meta_box_meta_key] === false) {

						// False
						$ws_form_meta_value = '';

					} else {

						// Value
						$ws_form_meta_value = $field[$meta_box_meta_key];
					}

					$meta_return[$ws_form_meta_key] = $ws_form_meta_value;
				}				
			}

			// Validation rules
			if(isset($field['wsf_validation_rules'])) {

				foreach($field['wsf_validation_rules'] as $key => $value) {

					switch($key) {

						case 'required' :
						case 'minlength' :
						case 'maxlength' :
						case 'min' :
						case 'max' :
						case 'accept' :

							$meta_return[$key] = $value;

							break;

						case 'extension' :

							$meta_return['accept'] = $value;

							break;

						case 'email' :

							$meta_return['pattern'] = '[a-zA-Z0-9.!#$%&’*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$';

							break;

						case 'url' :

							$meta_return['pattern'] = 'https?://.+';

							break;

						case 'number' :

							$meta_return['pattern'] = '^[0-9]+$';

							break;

						case 'phoneUS' :

							$meta_return['input_mask'] = '(999) 999-9999';

							break;

						case 'accept' :

							$meta_return['accept'] = '(999) 999-9999';

							break;
					}
				}
			}

			// Attributes
			if(isset($field['attributes']) && is_array($field['attributes'])) {

				$custom_attributes = array();

				foreach($field['attributes'] as $name => $value) {

					if(empty($name)) { continue; }
					if($name === 'class') { continue; }

					$custom_attributes[] = array(

						'custom_attribute_name' => $name,
						'custom_attribute_value' => $value
					);
				}

				if(count($custom_attributes) > 0) {

					$meta_return['custom_attributes'] = $custom_attributes;
				}
			}

			// Button group
			if($type === 'button_group') {

				$type = $multiple ? 'checkbox' : 'radio';
				$meta_return['class_field'] = 'wsf-button';
				$meta_return['orientation'] = 'horizontal';

				if($type === 'checkbox') {

					$meta_return['label_render'] = 'on';
				}
			}

			// Image select
			if($type === 'image_select') {

				$type = $multiple ? 'checkbox' : 'radio';
				$meta_return['class_field'] = 'wsf-image';
				$meta_return['orientation'] = 'horizontal';

				if($type === 'checkbox') {

					$meta_return['label_render'] = 'on';
				}
			}

			// Data grids
			$meta_key = false;
			$choices = array();
			$data_grid_column_count = 1;

			// Check for datalist
			if(
				isset($field['datalist']) &&
				isset($field['datalist']['options']) &&
				is_array($field['datalist']['options']) &&
				(count($field['datalist']['options']) > 0)
			) {

				$meta_key = 'data_grid_datalist';
				$choices = $field['datalist']['options'];
			}

			// Process by Meta Box field type
			switch($type) {

				// Map (Google Map)
				case 'map' :

					// Attempt to split default location into lat / lng
					$default_location = (isset($field['std']) && !is_array($field['std']) ? $field['std'] : '');

					if(!empty($default_location)) {

						$default_location_array = explode(',', $default_location);

						if(count($default_location_array) === 2) {

							$meta_return['google_map_lat'] = floatval(trim($default_location_array[0]));
							$meta_return['google_map_lng'] = floatval(trim($default_location_array[1]));
						}
					}

					// Get address field
					$address_field = (isset($field['address_field']) && !is_array($field['address_field']) ? $field['address_field'] : '');

					if(!empty($address_field)) {

						$meta_return['address_field'] = sprintf('#%s', $address_field);
					}

					break;

				// Custom HTML
				case 'custom_html' :

					$html_editor = (isset($field['std']) && !is_array($field['std']) ? $field['std'] : '');
					$meta_return['html_editor'] = $html_editor;

					break;

				// WYSIWYG
				case 'wysiwyg' :

					$meta_return['input_type_textarea'] = 'tinymce';

					break;

				// Build data grids for checkbox, radio and select
				case 'select' :
				case 'select_advanced' :
				case 'select_tree' :
				case 'autocomplete' :
				case 'checkbox' :
				case 'checkbox_list' :
				case 'checkbox_tree' :
				case 'switch' :
				case 'radio' :

					switch($type) {

						case 'select' :
						case 'select_advanced' :
						case 'select_tree' :
						case 'autocomplete' :

							$meta_key = 'data_grid_select';
							$meta_return['select_field_label'] = 1;
							$data_grid_column_count = 2;

							// Multiple
							if($multiple) {

								$meta_return['placeholder_row'] = '';
							}

							// Advanced
							if($type === 'select_advanced') {

								$meta_return['select2'] = 'on';
							}

							// Autocomplete
							if($type === 'autocomplete') {

								$meta_return['select2'] = 'on';
								$meta_return['multiple'] = 'on';
								$meta_return['select2_tags'] = 'on';
							}

							$choices = isset($field['options']) ? $field['options'] : array();
							break;

						case 'checkbox_list' :
						case 'checkbox_tree' :
						case 'checkbox' :
						case 'switch' :

							$meta_key = 'data_grid_checkbox';

							switch($type) {

								case 'checkbox_list' :
								case 'checkbox_tree' :

									$choices = isset($field['options']) ? $field['options'] : array($field['name']);

									$data_grid_column_count = 2;

									$meta_return['checkbox_field_label'] = 1;
									$meta_return['label_render'] = 'on';

									if($required) {

										$meta_return['checkbox_min'] = 1;
									}

									break;

								case 'checkbox' :
								case 'switch' :

									$choices = array('1' => $field['name']);

									$data_grid_column_count = 2;

									$meta_return['checkbox_field_label'] = 1;

									if($type === 'switch') { $meta_return['class_field'] = 'wsf-switch'; }

									if($required) {

										$row_required = true;
									}

									break;
							}

							// Toggle (Select All)
							if(
								isset($field['select_all_none']) &&
								($field['select_all_none'] == 1)
							) {

								$meta_return['select_all'] = 'on';
							}

							break;

						case 'radio' :

							$meta_key = 'data_grid_radio';
							$meta_return['radio_field_label'] = 1;
							$data_grid_column_count = 2;

							$choices = isset($field['options']) ? $field['options'] : array();

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

							case 'metabox' :

								$meta_return['data_source_metabox_field_id'] = $field['id'];

								if($type_original === 'image_select') {

									$meta_return['data_source_metabox_image_tag'] = 'on';
								}

								break;
						}

						// Interpret Meta Box JS options
						if(isset($field['query_args'])) {

							// Post type
							if(isset($field['query_args']['post_type'])) {

								$meta_return['data_source_post_filter_post_types'] = array();

								$post_types = $field['query_args']['post_type'];

								foreach($post_types as $post_type) {

									$meta_return['data_source_post_filter_post_types'][] = array(

										'data_source_post_post_types' => $post_type
									);
								}
							}

							// Post status
							if(isset($field['query_args']['post_status'])) {

								$meta_return['data_source_post_filter_post_statuses'] = array(

									array(

										'data_source_post_post_statuses' => $field['query_args']['post_status']
									)
								);
							}

							// Taxonomy
							if(isset($field['query_args']['taxonomy'])) {

								$meta_return['data_source_term_filter_taxonomies'] = array();

								$taxonomies = $field['query_args']['taxonomy'];

								foreach($taxonomies as $taxonomy) {

									$meta_return['data_source_term_filter_taxonomies'][] = array(

										'data_source_term_taxonomies' => $taxonomy
									);
								}
							}
						}
					}

					break;

				// File
				case 'file' :
				case 'file_advanced' :
				case 'file_upload' :
				case 'image' :
				case 'image_advanced' :
				case 'image_upload' :
				case 'single_image' :
				case 'video' :

					switch($type) {

						case 'file_upload' :
						case 'image_upload' :

							$meta_return['sub_type'] = 'dropzonejs';
							$meta_return['multiple'] = 'on';
							break;
					}

					switch($type) {

//						case 'image' :				// Meta Box adds a custom attribute for 'image/* already'
						case 'image_advanced' :
						case 'image_upload' :
						case 'single_image' :

							$meta_return['accept'] = 'image/*';
							break;

						case 'video' :

							$meta_return['accept'] = 'video/*';
							break;
					}

					if($multiple) {

						$meta_return['multiple_file'] = 'on';
						unset($meta_return['multiple']);
					}

					$meta_return['file_handler'] = 'attachment';

					break;

				// Date
				case 'datetime-local' :
				case 'datetime' :

					$meta_return['input_type_datetime'] = 'datetime-local';
					break;

				case 'date' :

					$meta_return['input_type_datetime'] = 'date';
					break;

				case 'month' :

					$meta_return['input_type_datetime'] = 'month';
					break;

				case 'time' :

					$meta_return['input_type_datetime'] = 'time';
					break;

				case 'week' :

					$meta_return['input_type_datetime'] = 'week';
					break;
			}

			// Check for data grid
			if(
				($meta_key !== false) &&
				(count($choices) > 0)
			) {

				// Get base meta
				$meta_keys = WS_Form_Config::get_meta_keys();

				// Get default meta data
				if(!isset($meta_keys[$meta_key])) { return false; }
				if(!isset($meta_keys[$meta_key]['default'])) { return false; }

				$meta = $meta_keys[$meta_key]['default'];

				// Configure columns
				if($data_grid_column_count === 2) {

					$meta['columns'] = array(

						array('id' => 0, 'label' => __('Value', 'ws-form')),
						array('id' => 1, 'label' => __('Label', 'ws-form'))
					);

				} else {

					$meta['columns'] = array(

						array('id' => 0, 'label' => __('Label', 'ws-form'))
					);
				}

				// Build new rows
				$rows = array();
				$id = 1;
				$default_value = array();

				if(isset($field['std'])) {

					$std = $field['std'];

					if(is_array($std)) {

						$default_value = $std;
					}

					if($std === 1) {

						$default_value = array($field['name']);
					}
				}

				foreach($choices as $value => $text) {

					if($type_original === 'image_select') {

						$text = sprintf('<img src="%s" alt="%s" />', esc_attr($text), esc_attr($value));
					}

					if($data_grid_column_count === 2) {

						$data = array($value, $text);

					} else {

						$data = array($text);
					}

					$rows[] = array(

						'id'		=> $id,
						'default'	=> (in_array($value, $default_value) ? 'on' : ''),
						'required'	=> $row_required,
						'disabled'	=> '',
						'hidden'	=> '',
						'data'		=> $data
					);

					$id++;
				}

				// Modify meta
				$meta['groups'][0]['rows'] = $rows;

				$meta_return[$meta_key] = $meta;
			}

			return $meta_return;
		}

		// Process Meta Box fields
		public static function meta_box_fields_to_meta_data($fields, $group_index = 0, $section_index = 1, $field_index = 1, $depth = 0) {

			$list_fields = array();
			$group_meta_data = array();
			$section_meta_data = array();

			$tab_last = false;
			$wsf_meta_box_name_last = false;

			foreach($fields as $field) {

				// Get Meta Box field type
				$action_type = $field['type'];

				// Get WS Form field type
				$type = self::meta_box_action_field_type_to_ws_form_field_type($field);
				if($type === false) { continue; }

				// Section names
				$wsf_meta_box_name = isset($field['wsf_meta_box_name']) ? $field['wsf_meta_box_name'] : false;

				// Tabs
				$tab = isset($field['tab']) ? $field['tab'] : false;

				if($tab !== $tab_last) {

					$group_index++;
					$section_index = 0;
					$field_index = 1;

					// Read tab name
					$wsf_tab_name = (isset($field['wsf_tab_name']) && isset($field['wsf_tab_name']['label'])) ? $field['wsf_tab_name']['label'] : false;
					if(!empty($wsf_tab_name)) {

						if(!isset($group_meta_data['group_' . $group_index])) { $group_meta_data['group_' . $group_index] = array(); }
						$group_meta_data['group_' . $group_index]['label'] = $wsf_tab_name;
					}

					if(!isset($section_meta_data['group_' . $group_index]['section_' . $section_index])) { $section_meta_data['group_' . $group_index]['section_' . $section_index] = array(); }
					$section_meta_data['group_' . $group_index]['section_' . $section_index]['label'] = $wsf_meta_box_name;

					$tab_last = $tab;
				}

				// Meta box name
				if(
					($depth === 0) &&
					($wsf_meta_box_name !== false) &&
					($wsf_meta_box_name !== $wsf_meta_box_name_last)
				) {

					if(empty($wsf_meta_box_name)) { $wsf_meta_box_name = WS_FORM_DEFAULT_GROUP_NAME; }

					if(
						!(
							($section_index === 0) &&
							($field_index === 1) 
						)
					) {

						$section_index++;
					}

					if(!isset($section_meta_data['group_' . $group_index]['section_' . $section_index])) { $section_meta_data['group_' . $group_index]['section_' . $section_index] = array(); }
					$section_meta_data['group_' . $group_index]['section_' . $section_index]['label'] = $wsf_meta_box_name;

					$wsf_meta_box_name_last = $wsf_meta_box_name;
				}

				// Repeaters & Groups
				switch($action_type) {

					case 'heading' :

						if($field_index > 1) {

							$section_index++;
							$field_index = 1;
						}

						if(!isset($section_meta_data['group_' . $group_index])) { $section_meta_data['group_' . $group_index] = array(); }
						if(!isset($section_meta_data['group_' . $group_index]['section_' . $section_index])) { $section_meta_data['group_' . $group_index]['section_' . $section_index] = array(); }

						// Section label
						$section_meta_data['group_' . $group_index]['section_' . $section_index]['label'] = $field['name'];
						$section_meta_data['group_' . $group_index]['section_' . $section_index]['label_render'] = 'on';

						// Section class
						$section_class = isset($field['class']) ? $field['class'] : '';
						if(!empty($section_class)) {

							$section_meta_data['group_' . $group_index]['section_' . $section_index]['class_section_wrapper'] = $section_class;
						}

						$desc = isset($field['desc']) ? $field['desc'] : '';
						if(!empty($desc)) {

							$field_index++;
						}

						continue 2;

					case 'group' :
					case 'key_value' :

						if(isset($field['fields'])) {

							$meta_box_fields_to_meta_data_return = self::meta_box_fields_to_meta_data($field['fields'], $group_index, $section_index + 1, 1, $depth + 1);
							if(count($meta_box_fields_to_meta_data_return['list_fields']) > 0) {

								$section_index++;
								$field_index = 1;

								// Label section
								if(!isset($section_meta_data['group_' . $group_index])) { $section_meta_data['group_' . $group_index] = array(); }
								if(!isset($section_meta_data['group_' . $group_index]['section_' . $section_index])) { $section_meta_data['group_' . $group_index]['section_' . $section_index] = array(); }

								// Section label
								$section_meta_data['group_' . $group_index]['section_' . $section_index]['label'] = $field['name'];
								$section_meta_data['group_' . $group_index]['section_' . $section_index]['label_render'] = 'on';

								// Section class
								$section_class = isset($field['class']) ? $field['class'] : '';
								if(!empty($section_class)) {

									$section_meta_data['group_' . $group_index]['section_' . $section_index]['class_section_wrapper'] = $section_class;
								}

								$group_meta_data = array_merge($group_meta_data, $meta_box_fields_to_meta_data_return['group_meta_data']);
								$section_meta_data = array_merge($section_meta_data, $meta_box_fields_to_meta_data_return['section_meta_data']);

								// Width
								if(
									isset($field['columns'])
								) {

									$columns = $field['columns'];

									if(is_array($columns)) {

										$size = isset($columns['size']) ? $columns['size'] : 12;
										$class = isset($columns['class']) ? $columns['class'] : '';

									} else {

										$size = $field['columns'];
									}

									$size = absint($size);

									if(
										($size > 0) &&
										($size <= 12)
									) {

										$section_meta_data['group_' . $group_index]['section_' . $section_index]['width_factor'] = ($size / 12);
									}

									if(!empty($class)) {

										if(!isset($section_meta_data['group_' . $group_index]['section_' . $section_index]['class_section_wrapper'])) {

											$section_meta_data['group_' . $group_index]['section_' . $section_index]['class_section_wrapper'] = $class;

										} else {

											$section_meta_data['group_' . $group_index]['section_' . $section_index]['class_section_wrapper'] .= ' ' . $class;
										}
									}
								}

								// Is repeatable?
								$clone = isset($field['clone']) ? $field['clone'] : false;
								if($clone) {

									$section_meta_data['group_' . $group_index]['section_' . $section_index]['section_repeatable'] = 'on';

									$max_clone = isset($field['max_clone']) ? $field['max_clone'] : '';
									$max_clone = absint($max_clone);
									if($max_clone > 0) {

										$section_meta_data['group_' . $group_index]['section_' . $section_index]['section_repeat_max'] = $max_clone;
									}
								}

								$section_index++;
								$field_index = 1;

								if(!isset($section_meta_data['group_' . $group_index]['section_' . $section_index])) { $section_meta_data['group_' . $group_index]['section_' . $section_index] = array(); }
								$section_meta_data['group_' . $group_index]['section_' . $section_index]['label'] = $wsf_meta_box_name;
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

		// Get parent key data for repeatables. We need this to be able to add the repeatable field meta data.
		public static function meta_box_get_parent_data($meta_box_field_id, $repeater_index = 0) {

			$field_settings = self::meta_box_get_field_settings($meta_box_field_id);

			if($field_settings === false) { return false; }

			if(!empty($field_settings['parent_field_id'])) {

				return array(

					'field_id' => $field_settings['parent_field_id'],
					'type' => $field_settings['parent_field_type'],
					'repeater' => $field_settings['parent_field_clone']
				);
			}

			return false;
		}

		// Process meta_box_field_values as taxonomy
		public static function meta_box_field_values_taxonomy($meta_box_field_values) {

			return (is_object($meta_box_field_values) && isset($meta_box_field_values->term_id)) ? $meta_box_field_values->term_id : $meta_box_field_values;
		}

		// Process meta_box_field_values as map
		public static function meta_box_field_values_google_map($meta_box_field_values) {

			if(is_array($meta_box_field_values)) {

				$lat = isset($meta_box_field_values['latitude']) ? $meta_box_field_values['latitude'] : '';
				$lng = isset($meta_box_field_values['longitude']) ? $meta_box_field_values['longitude'] : '';
				$zoom = isset($meta_box_field_values['zoom']) ? $meta_box_field_values['zoom'] : '14';

				return array(

					'lat' => floatval($lat),
					'lng' => floatval($lng),
					'zoom' => absint($zoom)
				);

			} else {

				$meta_box_field_values_array = explode(',', $meta_box_field_values);
				
				return array(

					'lat' => floatval($meta_box_field_values_array[0]),
					'lng' => floatval($meta_box_field_values_array[1]),
					'zoom' => absint($meta_box_field_values_array[2])
				);
			}
		}

		// Process meta_box_field_values as file
		public static function meta_box_field_values_file($meta_box_field_values) {

			$return_array = array();

			// Process attachment IDs
			if(!is_array($meta_box_field_values)) { $meta_box_field_values = array($meta_box_field_values); }

			foreach($meta_box_field_values as $attachment_id => $meta_box_field_value_single) {

				// File fields nested within a repeater, only return the attachment ID
				if(is_numeric($meta_box_field_value_single)) {

					$attachment_id = $meta_box_field_value_single;
				}

				$file_object = WS_Form_File_Handler::get_file_object_from_attachment_id($attachment_id);
				if($file_object === false) { continue; }

				$return_array[] = $file_object;
			}

			return (count($return_array) > 0) ? $return_array : false;
		}

		// Process meta_box_field_values as boolean
		public static function meta_box_field_values_boolean($meta_box_field_values, $field_id, $fields, $field_types) {

			// Get meta value array (Array containing values of data grid)
			$meta_value_array = WS_Form_Common::get_meta_value_array($field_id, $fields, $field_types);
			$true_array = array('1', 'on', 'yes', 'true');

			// Get first element if array
			if(is_array($meta_box_field_values)) { $meta_box_field_values = isset($meta_box_field_values[0]) ? $meta_box_field_values[0] : ''; }

			$meta_box_field_values = strtolower($meta_box_field_values);

			return in_array($meta_box_field_values, $true_array) ? $meta_value_array[0] : false;
		}

		// Process meta_box_field_values as date
		public static function meta_box_field_values_date_time($meta_box_field_values, $field_id, $meta_box_field_type) {

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

			switch($meta_box_field_type) {

				case 'date' :

					return date($format_date, strtotime($meta_box_field_values));

				case 'datetime' :
				case 'datetime-local' :

					return date($format_date . ' ' . $format_time, strtotime($meta_box_field_values));

				case 'month' :
				case 'week' :

					return date('Y-m-d', strtotime($meta_box_field_values));

				case 'time' :

					return date($format_time, strtotime($meta_box_field_values));
			}

			return '';
		}

		// Get field type
		public static function meta_box_get_field_type($meta_box_field_id) {

			$field_settings = self::meta_box_get_field_settings($meta_box_field_id);
			if($field_settings === false) { return false; }

			return $field_settings['type'];
		}

		// Get file field types
		public static function meta_box_get_field_types_file() {

			return array(

				'file',
				'file_advanced',
				'file_upload',
				'image',
				'image_advanced',
				'image_upload',
				'single_image',
				'video'
			);
		}

		// Get check if field type supports required
		public static function meta_box_field_type_has_required($action_field_type) {

			switch($action_field_type) {

				case 'checkbox' :
				case 'checkbox_list' :
				case 'checkbox_tree' :

					return false;
			}

			return true;
		}

		// Convert Meta Box meta value to WS Form field
		public static function meta_box_meta_box_meta_value_to_ws_form_field_value($meta_box_field_values, $meta_box_field_type, $meta_box_field_repeater, $field_id, $fields, $field_types) {

			switch($meta_box_field_type) {

				case 'taxonomy' :
				case 'taxonomy_advanced' :

					if($meta_box_field_repeater) {

						// Process repeated attachment IDs
						foreach($meta_box_field_values as $meta_box_field_values_index => $meta_box_field_value) {

							$meta_box_field_values[$meta_box_field_values_index] = self::meta_box_field_values_taxonomy($meta_box_field_value);
						}

					} else {

						// Process regular attachment IDs
						$meta_box_field_values = self::meta_box_field_values_taxonomy($meta_box_field_values);
					}

					break;

				case 'map' :

					if($meta_box_field_repeater) {

						// Process repeated attachment IDs
						foreach($meta_box_field_values as $meta_box_field_values_index => $meta_box_field_value) {

							$meta_box_field_values[$meta_box_field_values_index] = self::meta_box_field_values_google_map($meta_box_field_value);
						}

					} else {

						// Process regular attachment IDs
						$meta_box_field_values = self::meta_box_field_values_google_map($meta_box_field_values);
					}

					break;

				case 'file' :
				case 'file_advanced' :
				case 'image' :
				case 'image_advanced' :
				case 'single_image' :
				case 'video' :

					// Write only field fields
					$meta_box_field_values = '';

					break;

				case 'file_upload' :
				case 'image_upload' :

					if($meta_box_field_repeater) {

						// Process repeated attachment IDs
						foreach($meta_box_field_values as $meta_box_field_values_index => $meta_box_field_value) {

							$meta_box_field_values[$meta_box_field_values_index] = self::meta_box_field_values_file($meta_box_field_value);
						}

					} else {

						// Process regular attachment IDs
						$meta_box_field_values = self::meta_box_field_values_file($meta_box_field_values);
					}

					break;

				case 'date' :
				case 'datetime' :
				case 'datetime-local' :
				case 'time' :
				case 'week' :
				case 'month' :

					if($meta_box_field_repeater) {

						// Process repeated date
						foreach($meta_box_field_values as $meta_box_field_values_index => $meta_box_field_value) {

							$meta_box_field_values[$meta_box_field_values_index] = self::meta_box_field_values_date_time($meta_box_field_value, $field_id, $meta_box_field_type);
						}

					} else {

						// Process regular date
						$meta_box_field_values = self::meta_box_field_values_date_time($meta_box_field_values, $field_id, $meta_box_field_type);
					}

					break;
			}

			return $meta_box_field_values;
		}

		// Convert WS Form field value to Meta Box meta value
		public static function meta_box_ws_form_field_value_to_meta_box_meta_value($meta_value, $meta_box_field_type, $meta_box_field_id) {

			if($meta_value == '') { return ''; }

			switch($meta_box_field_type) {

/*				case 'taxonomy' :

					// For some reason, taxonomy needs to be string if not multiple, whereas all other fields don't
					$meta_box_field_settings = self::meta_box_get_field_settings($meta_box_field_id);
					if($meta_box_field_settings === false) { return $meta_value; }

					// Get multiple
					$meta_box_field_multiple = isset($meta_box_field_settings['multiple']) ? $meta_box_field_settings['multiple'] : false;

					// If not multiple, convert to string
					if(!$meta_box_field_multiple) { return $meta_value[0]; }

					break;
*/
				case 'map' :

					$lat = floatval(isset($meta_value['lat']) ? $meta_value['lat'] : '');
					$lng = floatval(isset($meta_value['lng']) ? $meta_value['lng'] : '');
					$zoom = floatval(isset($meta_value['zoom']) ? $meta_value['zoom'] : '5');
					return sprintf('%f,%f,%u', $lat, $lng, $zoom);

				case 'datetime-local' :

					return date('Y-m-d', strtotime($meta_value)) . 'T' . date('H:i', strtotime($meta_value));

				case 'datetime' :

					return date('Y-m-d', strtotime($meta_value)) . ' ' . date('H:i', strtotime($meta_value));

				case 'week' :

					return date('Y', strtotime($meta_value)) . '-W' . date('W', strtotime($meta_value));

				case 'month' :

					return date('Y-m', strtotime($meta_value));

				case 'time' :

					return date('H:i', strtotime($meta_value));
			}

			return $meta_value;
		}

		// Get field sub type
		public static function meta_box_get_field_sub_type($field) {

			$sub_type = false;

			$field_type = isset($field['field_type']) ? $field['field_type'] : false;
			
			if($field_type !== false) {

				switch($field_type) {

					case 'select' : $sub_type = 'select'; break;
					case 'select_advanced' : $sub_type = 'select'; break;
					case 'select_tree' : $sub_type = 'select'; break;
					case 'checkbox' : $sub_type = 'checkbox'; break;
					case 'checkbox_list' : $sub_type = 'checkbox'; break;
					case 'checkbox_tree' : $sub_type = 'checkbox'; break;
					case 'radio' : $sub_type = 'radio'; break;
					case 'radio_list' : $sub_type = 'radio'; break;
				}
			}

			return $sub_type;
		}

		// Get field data source
		public static function meta_box_get_field_data_source($field) {

			$data_source = false;

			$field_type = isset($field['type']) ? $field['type'] : false;
			
			if($field_type !== false) {

				switch($field_type) {

					case 'select' : $data_source = 'metabox'; break;
					case 'select_advanced' : $data_source = 'metabox'; break;
					case 'select_tree' : $data_source = 'metabox'; break;
					case 'checkbox_list' : $data_source = 'metabox'; break;
					case 'checkbox_tree' : $data_source = 'metabox'; break;
					case 'radio' : $data_source = 'metabox'; break;
					case 'radio_list' : $data_source = 'metabox'; break;
					case 'image_select' : $data_source = 'metabox'; break;
					case 'post' : $data_source = 'post'; break;
//					case 'sidebar' : $data_source = 'sidebar'; break;
					case 'taxonomy' : $data_source = 'term'; break;
					case 'taxonomy_advanced' : $data_source = 'term'; break;
					case 'user' : $data_source = 'user'; break;
				}
			}

			return $data_source;
		}

		// Convert action field type to WS Form field type
		public static function meta_box_action_field_type_to_ws_form_field_type($field) {

			$type = $field['type'];

			$sub_type = self::meta_box_get_field_sub_type($field);

			switch($type) {

				// Basic
				case 'button' : return 'button';
				case 'button_group' : return $field['multiple'] ? 'checkbox' : 'radio';
				case 'checkbox' : return 'checkbox';
				case 'checkbox_list' : return 'checkbox';
				case 'hidden' : return 'hidden';
				case 'password' : return 'password';
				case 'radio' : return 'radio';
				case 'select' : return 'select';
				case 'select_advanced' : return 'select';
				case 'text' : return 'text';
				case 'textarea' : return 'textarea';
				case 'url' : return 'url';

				// Advanced
				case 'autocomplete' : return 'select';
				case 'color' : return 'color';
				case 'custom_html' : return 'html';
				case 'date' : return 'datetime';
				case 'datetime' : return 'datetime';
				case 'map' : return 'googlemap';
				case 'image_select' : return $field['multiple'] ? 'checkbox' : 'radio';
				case 'key_value' : return 'key_value';
				case 'oembed' : return 'url';
				case 'slider' : return 'range';
				case 'switch' : return 'checkbox';
				case 'time' : return 'datetime';
				case 'wysiwyg' : return 'textarea';

				// HTML5
				case 'datetime-local' : return 'datetime';
				case 'email' : return 'email';
				case 'month' : return 'datetime';
				case 'number' : return 'number';
				case 'range' : return 'range';
				case 'tel' : return 'tel';
				case 'week' : return 'datetime';

				// WordPress
				case 'post' : return $sub_type;
				case 'taxonomy' : return $sub_type;
				case 'taxonomy_advanced' : return $sub_type;
				case 'user' : return $sub_type;

				// Upload
				case 'file' : return 'file';
				case 'file_advanced' : return 'file';
				case 'file_input' : return 'url';
				case 'file_upload' : return 'file';
				case 'image' : return 'file';
				case 'image_advanced' : return 'file';
				case 'image_upload' : return 'file';
				case 'single_image' : return 'file';
				case 'video' : return 'file';

				// Layout
				case 'divider' : return 'divider';
				case 'heading' : return 'heading';
				case 'group' : return 'group';
				case 'tab' : return false;

				// Unsupported
				case 'background' : return false;
				case 'sidebar' : return false;
				case 'osm' : return false;	// Open Street Map
				case 'text_list' : return false;
				case 'fieldset_text' : return false;
			}

			return false;
		}

		// Fields that we can push data to
		public static function meta_box_field_mappable($meta_box_field_type) {

			switch($meta_box_field_type) {

				// Basic
				case 'button_group' :
				case 'checkbox' :
				case 'checkbox_list' :
				case 'hidden' :
				case 'password' :
				case 'radio' :
				case 'select' :
				case 'select_advanced' :
				case 'text' :
				case 'textarea' :
				case 'url' :

				// Advanced
				case 'autocomplete' :
				case 'color' :
				case 'date' :
				case 'datetime' :
				case 'map' :
				case 'image_select' :
				case 'oembed' :
				case 'slider' :
				case 'switch' :
				case 'time' :
				case 'wysiwyg' :

				// HTML5
				case 'datetime-local' :
				case 'email' :
				case 'month' :
				case 'number' :
				case 'range' :
				case 'tel' :
				case 'week' :

				// WordPress
				case 'post' :
				case 'taxonomy' :
				case 'taxonomy_advanced' :
				case 'user' :

				// Upload
				case 'file' :
				case 'file_advanced' :
				case 'file_input' :
				case 'file_upload' :
				case 'image' :
				case 'image_advanced' :
				case 'image_upload' :
				case 'single_image' :
				case 'video' :

					return true;

				default :

					return false;
			}
		}
	}