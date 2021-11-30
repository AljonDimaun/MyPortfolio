<?php

	class WS_Form_Toolset {

		// Get fields all
		public static function toolset_get_fields_all($toolset_get_field_groups_filter = array(), $choices_filter = false, $raw = false, $traverse = false, $has_fields = false) {

			// Version check
 			if(!self::toolset_version_check()) {

				return $has_fields ? false : array();
			}

			// Build toolset_get_field_groups args
			$toolset_get_field_groups_filter['active'] = true;

			// Get field
			$toolset_field_groups = toolset_get_field_groups($toolset_get_field_groups_filter);

			// Toolset fields
			$options_toolset = array();

			// Check if fields were found
			$fields_found = false;

			// Process each Toolset field group
			foreach($toolset_field_groups as $toolset_field_group) {

				// Get fields
				$toolset_fields = $toolset_field_group->get_field_definitions();

				// Has fields?
				if($has_fields && (count($toolset_fields) > 0)) { $fields_found = true; break; }

				// Get group name
				$toolset_field_group_name = $toolset_field_group->get_name();

				// Process fields
				WS_Form_Toolset::toolset_get_fields_process($options_toolset, $toolset_field_group_name, $toolset_fields, $choices_filter, $raw, $traverse);
			}

			return $has_fields ? $fields_found : $options_toolset;
		}

		// Get fields
		public static function toolset_get_fields_process(&$options_toolset, $toolset_field_group_name, $toolset_fields, $choices_filter, $raw, $traverse, $prefix = '') {

			foreach($toolset_fields as $toolset_field) {

				// Get field type
				$toolset_field_type = $toolset_field->get_type()->get_slug();

				// Store meta box name
				$toolset_field->wsf_group_name = $toolset_field_group_name;

				// Only return fields that have choices
				$process_field = true;
				if(
					$choices_filter &&

					(
						!in_array(

							$toolset_field_type,

							array(

								Toolset_Field_Type_Definition_Factory::CHECKBOXES,
								Toolset_Field_Type_Definition_Factory::RADIO,
								Toolset_Field_Type_Definition_Factory::SELECT
							)
						)

						||

						(count($toolset_field->get_field_options()) === 0)
					)
				) {
					$process_field = false;
				}

				if($process_field) {

					if($raw) {

						$options_toolset[$toolset_field->get_slug()] = $toolset_field;

					} else {

						// Check if mappable
						if(self::toolset_field_mappable($toolset_field_type)) {

							$options_toolset[] = array('value' => $toolset_field->get_slug(), 'text' => sprintf('%s%s - %s', $toolset_field_group_name, $prefix, $toolset_field->get_name()));
						}
					}
				}
			}
		}

		// Version check
		public static function toolset_version_check() {

			return defined('TYPES_VERSION') && (WS_Form_Common::version_compare(TYPES_VERSION, '3.4.0') >= 0);
		}

		// Get field data
		public static function toolset_get_field_data($toolset_get_field_groups_filter, $object_id) {

			// Get field objects
			$field_objects = self::toolset_get_fields_all($toolset_get_field_groups_filter, false, true, false, false);
			if($field_objects === false) { return array(); }

			// Get post ID
			$domain = $toolset_get_field_groups_filter['domain'];

			$return_array = array();

			foreach($field_objects as $field_object) {

				// Get field ID
				$toolset_field_slug = $field_object->get_slug();

				// Get value
				switch($domain) {

					case Toolset_Element_Domain::POSTS :

						$field_value = get_post_meta($object_id, sprintf('wpcf-%s', $toolset_field_slug), true);
						break;

					case Toolset_Element_Domain::USERS :

						$field_value = get_user_meta($object_id, sprintf('wpcf-%s', $toolset_field_slug), true);
						break;
				}

				// Check field value
				if($field_value === false) { $field_value = ''; }

				// Add to return array
				$return_array[$toolset_field_slug] = array('values' => $field_value);
			}

			return $return_array;
		}

		// Process Toolset fields
		public static function toolset_fields_to_list_fields($fields, $group_index = 0, $section_index = 1, $field_index = 1, $depth = 0) {

			$list_fields = array();

			$wsf_group_name_last = false;

			$sort_index = $field_index;

			foreach($fields as $field) {

				// Get field type
				$action_type = $field->get_type()->get_slug();
				$type = self::toolset_action_field_type_to_ws_form_field_type($field);
				if($type === false) { continue; }

				// Get meta
				$meta = self::toolset_action_field_to_ws_form_meta_keys($field);

				// Section names
				$wsf_group_name = isset($field->wsf_group_name) ? $field->wsf_group_name : false;

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

				$definition_array = $field->get_definition_array();

				$list_fields_single = array(

					'id' => 				$field->get_slug(),
					'label' => 				$field->get_name(), 
					'label_field' => 		$field->get_name(), 
					'type' => 				$type,
					'action_type' =>		$action_type,
					'required' => 			false,
					'default_value' => 		toolset_getnest($definition_array, array('data', 'user_default_value'), ''),
					'pattern' => 			'',
					'placeholder' => 		toolset_getnest($definition_array, array('data', 'placeholder'), ''),
					'group_index' =>		$group_index,
					'section_index' => 		$section_index,
					'sort_index' => 		$sort_index++,
					'visible' =>			true,
					'help' =>				$field->get_description(),
					'meta' => 				$meta,
					'no_map' =>				true
				);

				$list_fields[] = $list_fields_single;
			}

			return array('list_fields' => $list_fields, 'group_index' => $group_index, 'section_index' => $section_index);
		}

		// Convert action field to WS Form meta key
		public static function toolset_action_field_to_ws_form_meta_keys($field) {

			$meta_return = array();

			$type = $field->get_type()->get_slug();

			$definition_array = $field->get_definition_array();

			// Invalid feedback
			$meta_return['invalid_feedback'] = toolset_getnest($definition_array, array('data', 'validate', 'required', 'message'), '');

			// Required
			$meta_return['required'] = $field->get_is_required();

			// Get WS Form meta configurations for action field types
			switch($type) {

				// Build data grids for radio and select
				case 'checkbox' :
				case 'checkboxes' :
				case 'post' :
				case 'radio' :
				case 'select' :

					switch($type) {

						case 'post' :

							$meta_key = 'data_grid_select';
							$meta_return['select_field_label'] = 1;

							// Data source set-up
							$meta_return = WS_Form_Data_Source::get_data_source_meta('post', $meta_return);

							// Set up data source
							$meta_return['data_source_id'] = 'post';

							// Post types
							$post_types = toolset_getnest($definition_array, array('data', 'post_reference_type'), array());
							if(!is_array($post_types)) { $post_types = array($post_types); }
							$meta_return['data_source_post_filter_post_types'] = array();
							foreach($post_types as $post_type) {

								$meta_return['data_source_post_filter_post_types'][] = array(

									'data_source_post_post_types' => $post_type
								);
							}

							$choices = array();
							break;

						case 'select' :

							$meta_key = 'data_grid_select';
							$meta_return['select_field_label'] = 1;

							// Data source set-up
							$meta_return = WS_Form_Data_Source::get_data_source_meta('toolset', $meta_return);

							// Set up data source
							$meta_return['data_source_id'] = 'toolset';
							$meta_return['data_source_toolset_field_slug'] = $field->get_slug();

							$choices = array();
							break;

						case 'checkbox' :

							$meta_key = 'data_grid_checkbox';
							$meta_return['checkbox_field_label'] = 1;

							$value = $field->get_forced_value();
							$label = $field->get_name();

							$checked = toolset_getnest($definition_array, array('data', 'checked'), false);

							$choices = array($value => $label);

							break;

						case 'checkboxes' :

							$meta_key = 'data_grid_checkbox';
							$meta_return['checkbox_field_label'] = 1;

							// Data source set-up
							$meta_return = WS_Form_Data_Source::get_data_source_meta('toolset', $meta_return);

							// Set up data source
							$meta_return['data_source_id'] = 'toolset';
							$meta_return['data_source_toolset_field_slug'] = $field->get_slug();

							$choices = array();
							break;

						case 'radio' :

							$meta_key = 'data_grid_radio';
							$meta_return['radio_field_label'] = 1;

							// Data source set-up
							$meta_return = WS_Form_Data_Source::get_data_source_meta('toolset', $meta_return);

							// Set up data source
							$meta_return['data_source_id'] = 'toolset';
							$meta_return['data_source_toolset_field_slug'] = $field->get_slug();

							$choices = array();
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

					$definition_array = $field->get_definition_array();

					foreach($choices as $value => $text) {

						$rows[] = array(

							'id'		=> $id,
							'default'	=> '',
							'required'	=> $checked,
							'disabled'	=> '',
							'hidden'	=> '',
							'data'		=> array($value, $text)
						);

						$id++;
					}

					// Modify meta
					$meta['groups'][0]['rows'] = $rows;

					$meta_return[$meta_key] = $meta;

					$meta_return['required'] = false;

					return $meta_return;

				case 'audio' :
				case 'file' :
				case 'image' :
				case 'video' :

					// Type
					$meta_return['sub_type'] = 'dropzonejs';

					// File handler
					$meta_return['file_handler'] = 'attachment';

					switch($type) {

						case 'audio' :

							// Accept
							$meta_return['accept'] = 'audio/*';

							break;

						case 'image' :

							// Accept
							$meta_return['accept'] = 'image/*';

							break;

						case 'video' :

							// Accept
							$meta_return['accept'] = 'video/*';

							break;
					}

					return $meta_return;

				case 'date' :

					// Get type of date
					$date_and_time = toolset_getnest($definition_array, array('data', 'date_and_time'), 'date');

					switch($date_and_time) {

						case 'and_time' :

							$meta_return['input_type_datetime'] = 'datetime-local';

							break;

						default :

							$meta_return['input_type_datetime'] = 'date';
					}

					return $meta_return;

				case 'wysiwyg' :

					global $wp_version;
					if(WS_Form_Common::version_compare($wp_version, '4.8') >= 0) {
						$meta_return['input_type_textarea'] = 'tinymce';
					}
					return $meta_return;

				default :

					return false;
			}
		}

		// Process Toolset fields
		public static function toolset_fields_to_meta_data($fields, $group_index = 0, $section_index = 1, $field_index = 1, $depth = 0) {

			$list_fields = array();
			$group_meta_data = array();
			$section_meta_data = array();

			$wsf_group_name_last = false;

			foreach($fields as $field) {

				$action_type = $field->get_type()->get_slug();
				$type = self::toolset_action_field_type_to_ws_form_field_type($field);

				// Skip unsupported field types
				if($type === false) { continue; }

				// Section names
				$wsf_group_name = isset($field->wsf_group_name) ? $field->wsf_group_name : false;

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

				// Dummy entry
				$list_fields[] = array();

				$field_index++;
			}

			return array('list_fields' => $list_fields, 'group_meta_data' => $group_meta_data, 'section_meta_data' => $section_meta_data, 'group_index' => $group_index, 'section_index' => $section_index);
		}

		// Process toolset_field_values as file
		public static function toolset_field_values_file($toolset_field_values) {

			$return_array = array();

			// Process attachment IDs
			if(!is_array($toolset_field_values)) { $toolset_field_values = array($toolset_field_values); }

			foreach($toolset_field_values as $toolset_field_value_single) {

				if(empty($toolset_field_value_single)) { continue; }

				$file_object = WS_Form_File_Handler::get_file_object_from_url($toolset_field_value_single);
				if($file_object === false) { continue; }

				$return_array[] = $file_object;
			}

			return (count($return_array) > 0) ? $return_array : false;
		}

		// Process toolset_field_values as date
		public static function toolset_field_values_date_time($toolset_field_values, $field_id) {

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

			return date($format_date . ' ' . $format_time, intval($toolset_field_values));
		}

		// Process toolset_field_values as checkboxes
		public static function toolset_field_values_checkboxes($toolset_field_values) {

			$return_array = array();

			if(!is_array($toolset_field_values)) { $toolset_field_values = array($toolset_field_values); }

			foreach($toolset_field_values as $toolset_field_value_single) {

				if(is_array($toolset_field_value_single)) {

					$return_array[] = $toolset_field_value_single[0];
				}
			}

			return (count($return_array) > 0) ? $return_array : false;
		}

		// Process toolset_field_values as post
		public static function toolset_field_values_post($object_id, $toolset_field_slug) {

			$related_posts = toolset_get_related_post($object_id, $toolset_field_slug, 'parent');

			return array($related_posts);
		}

		// Get field type
		public static function toolset_get_field_type($toolset_field_slug, $meta_name = 'wpcf-fields') {

			$field_array = wpcf_fields_get_field_by_slug($toolset_field_slug, $meta_name);
			if(!isset($field_array['type'])) { return false; }

			return $field_array['type'];
		}

		// Get file field types
		public static function toolset_get_field_types_file() {

			return array(

				'audio',
				'file',
				'image',
				'video'
			);
		}

		// Update Toolset meta
		public static function toolset_update_meta($object_id, $toolset_update_fields, $toolset_field_type_lookup, $meta_name = 'wpcf-fields') {

			global $wpcf;

			// Update fields
			foreach($toolset_update_fields as $toolset_field_slug => $meta_value) {

				if(!isset($toolset_field_type_lookup[$toolset_field_slug])) { continue; }

				$toolset_field_type = $toolset_field_type_lookup[$toolset_field_slug];

				switch($toolset_field_type) {

					case 'post' :

						if(is_array($meta_value)) { $meta_value = $meta_value[0]; }

						// Initialize field
						$field = wpcf_fields_get_field_by_slug($toolset_field_slug, $meta_name);
						$wpcf->field->set($object_id, $field);

						// The following code is an extract from Toolset Types plugin: /application/controllers/page/extension/edit_post.php
						$new_parent_id = (int) $meta_value;
						$child_id = $wpcf->field->post->ID;
						$relationship_slug = $field['data']['relationship_slug'];

						// delete previous association
						$repository = Toolset_Relationship_Definition_Repository::get_instance();

						if ( ! $definition = $repository->get_definition( $relationship_slug ) ) {
							// definition could not be found, should not happen...
							break;
						}

						// get association
						$query = new Toolset_Association_Query_V2();
						$associations = $query->add( $query->relationship( $definition ) )
							->add( $query->child_id( $child_id ) )
							// This is important, we don't care about the status at this point.
							->add( $query->element_status( 'any' ) )
							->limit( 1 )
							->return_association_instances()
							->get_results();

						$association = array_shift( $associations );

						// if no assocation stored so far...
						if ( empty( $association ) ) {
							if ( ! empty( $new_parent_id ) ) {
								// user has set a new parent, store it
								$definition->create_association(
									get_post( $new_parent_id ),
									$wpcf->field->post
								);
							}

							break;
						}

						// ...there is a stored association
						$is_current_association_different_to_stored =
							$association->get_element( new Toolset_Relationship_Role_Parent() )->get_id() !== $new_parent_id;

						if ( $is_current_association_different_to_stored ) {
							// associated post has changed, delete previous
							$association->get_definition()->delete_association( $association );

							if ( ! empty( $new_parent_id ) ) {
								// a new post was selected
								$definition->create_association(
									get_post( $new_parent_id ),
									$wpcf->field->post
								);
							}
						}

						break;

					default :

						switch($meta_name) {

							case 'wpcf-fields' :

								update_post_meta($object_id, sprintf('wpcf-%s', $toolset_field_slug), $meta_value);
								break;

							case 'wpcf-usermeta' :

								update_user_meta($object_id, sprintf('wpcf-%s', $toolset_field_slug), $meta_value);
								break;
						}
				}
			}
		}

		// Convert Toolset meta value to WS Form field
		public static function toolset_toolset_meta_value_to_ws_form_field_value($toolset_field_values, $toolset_field_type, $field_id, $fields, $field_types, $object_id, $toolset_field_slug) {

			switch($toolset_field_type) {

				case 'audio' :
				case 'file' :
				case 'image' :
				case 'video' :

					// Process files
					$toolset_field_values = self::toolset_field_values_file($toolset_field_values, $field_id, $fields, $field_types);

					break;

				case 'date' :

					// Process dates
					$toolset_field_values = self::toolset_field_values_date_time($toolset_field_values, $field_id);

					break;

				case 'checkboxes' :

					// Process checkboxes
					$toolset_field_values = self::toolset_field_values_checkboxes($toolset_field_values);

					break;

				case 'post' :

					// Process posts
					$toolset_field_values = self::toolset_field_values_post($object_id, $toolset_field_slug);

					break;
			}

			return $toolset_field_values;
		}

		// Convert WS Form field value to Toolset meta value
		public static function toolset_ws_form_field_value_to_toolset_meta_value($meta_value, $toolset_field_type, $toolset_field_slug, $meta_name = 'wpcf-fields') {

			if($meta_value == '') { return ''; }

			switch($toolset_field_type) {

				case 'checkboxes' :

					$meta_value_new = array();

					$field_array = wpcf_fields_get_field_by_slug($toolset_field_slug, $meta_name);
					if(!isset($field_array['data'])) { return ''; }
					if(!isset($field_array['data']['options'])) { return ''; }
					if(!is_array($meta_value)) { $meta_value = array($meta_value); }

					$options = $field_array['data']['options'];

					foreach($options as $option_id => $option) {

						if(isset($option['set_value'])) {

							$option_value = $option['set_value'];

							if(in_array($option_value, $meta_value)) {

								$meta_value_new[$option_id] = array($option_value);

							}
						}						
					}

					$meta_value = $meta_value_new;

					break;

				case 'checkbox' :
				case 'radio' :
				case 'select' :

					if(is_array($meta_value)) {

						$meta_value = $meta_value[0];
					}

					break;

				case 'date' :

					$meta_value = strtotime($meta_value);
					break;
			}

			return $meta_value;
		}

		// Convert action field type to WS Form field type
		public static function toolset_action_field_type_to_ws_form_field_type($field) {

			$type = $field->get_type()->get_slug();

			switch($type) {

				case 'audio' : return 'file';
				case 'checkbox' : return 'checkbox';
				case 'checkboxes' : return 'checkbox';
				case 'colorpicker' : return 'color';
				case 'date' : return 'datetime';
				case 'email' : return 'email';
				case 'embed' : return 'url';
				case 'file' : return 'file';
				case 'image' : return 'file';
				case 'numeric' : return 'number';
				case 'phone' : return 'tel';
				case 'post' : return 'select';
				case 'radio' : return 'radio';
				case 'select' : return 'select';
				case 'skype' : return 'text';
				case 'textarea' : return 'textarea';
				case 'textfield' : return 'text';
				case 'url' : return 'url';
				case 'video' : return 'file';
				case 'wysiwyg' : return 'textarea';
			}

			return false;
		}

		// Fields that we can push data to
		public static function toolset_field_mappable($toolset_field_type) {

			switch($toolset_field_type) {

				case 'audio' :
				case 'checkbox' :
				case 'checkboxes' :
				case 'colorpicker' :
				case 'date' :
				case 'email' :
				case 'embed' :
				case 'file' :
				case 'image' :
				case 'numeric' :
				case 'phone' :
				case 'post' :
				case 'radio' :
				case 'select' :
				case 'skype' :
				case 'textarea' :
				case 'textfield' :
				case 'url' :
				case 'video' :
				case 'wysiwyg' :

					return true;

				default :

					return false;
			}
		}
	}