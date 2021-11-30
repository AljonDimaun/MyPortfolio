<?php

	class WS_Form_Group extends WS_Form_Core {

		public $id;
		public $form_id;
		public $new_lookup;
		public $label;
		public $meta;

		public $table_name;

		const DB_INSERT = 'label,user_id,date_added,date_updated,sort_index,form_id';
		const DB_UPDATE = 'label,user_id,date_updated';
		const DB_SELECT = 'label,sort_index,id';

		public function __construct() {

			global $wpdb;

			$this->id = 0;
			$this->form_id = 0;
			$this->table_name = $wpdb->prefix . WS_FORM_DB_TABLE_PREFIX . 'group';
			$this->new_lookup = array();
			$this->new_lookup['group'] = array();
			$this->new_lookup['section'] = array();
			$this->new_lookup['field'] = array();
			$this->label = WS_FORM_DEFAULT_GROUP_NAME;
			$this->meta = array();
		}

		// Create group
		public function db_create($next_sibling_id = 0, $create_section = true) {

			// User capability check
			if(!WS_Form_Common::can_user('edit_form')) { return false; }

			self::db_check_form_id();

			// Process sort index
			$sort_index = self::db_object_sort_index_get($this->table_name, 'form_id', $this->form_id, $next_sibling_id);

			global $wpdb;

			// Truncate label
			if(strlen($this->label) > WS_FORM_GROUP_LABEL_MAX_LENGTH) {

				$this->label = substr($this->label, 0, WS_FORM_GROUP_LABEL_MAX_LENGTH);
			}

			// Add group
			$sql = sprintf("INSERT INTO %s (%s) VALUES ('%s', %u, '%s', '%s', %u, %u);", $this->table_name, self::DB_INSERT, esc_sql($this->label), WS_Form_Common::get_user_id(), WS_Form_Common::get_mysql_date(), WS_Form_Common::get_mysql_date(), $sort_index, $this->form_id);
			if($wpdb->query($sql) === false) { parent::db_wpdb_handle_error(__('Error adding group', 'ws-form')); }

			// Get inserted ID
			$this->id = $wpdb->insert_id;

			// Build meta data array
			$settings_form_admin = WS_Form_Config::get_settings_form_admin();
			$meta_data = $settings_form_admin['sidebars']['group']['meta'];
			$meta_keys = WS_Form_Config::get_meta_keys();
			$meta_data = self::build_meta_data($meta_data, $meta_keys);
			$meta_data = (object) array_merge($meta_data, (array) $this->meta);

			// Build meta data
			$ws_form_meta = New WS_Form_Meta();
			$ws_form_meta->object = 'group';
			$ws_form_meta->parent_id = $this->id;
			$ws_form_meta->db_update_from_object($meta_data);

			// Build first section
			if($create_section) {

				$ws_form_section = New WS_Form_Section();
				$ws_form_section->form_id = $this->form_id;
				$ws_form_section->group_id = $this->id;
				$ws_form_section->db_create();
			}

			return $this->id;
		}

		// Read record to array
		public function db_read($get_meta = true, $get_sections = false, $bypass_user_capability_check = false) {

			// User capability check
			if(!$bypass_user_capability_check && !WS_Form_Common::can_user('read_form')) { return false; }

			global $wpdb;

			// Add fields
			$sql = sprintf("SELECT %s FROM %s WHERE id = %u LIMIT 1;", self::DB_SELECT, $this->table_name, $this->id);
			$group_array = $wpdb->get_row($sql, 'ARRAY_A');
			if(is_null($group_array)) { parent::db_wpdb_handle_error(__('Unable to read group', 'ws-form')); }

			foreach($group_array as $key => $value) {

				$this->{$key} = $value;
			}

			if($get_meta) {

				// Read meta
				$section_meta = New WS_Form_Meta();
				$section_meta->object = 'group';
				$section_meta->parent_id = $this->id;
				$metas = $section_meta->db_read_all($bypass_user_capability_check);
				$this->meta = $group_array['meta'] = $metas;
			}

			if($get_sections) {

				// Read sections
				$ws_form_section = New WS_Form_Section();
				$ws_form_section->group_id = $this->id;
				$sections = $ws_form_section->db_read_all($get_meta, false, $bypass_user_capability_check);
				$this->sections = $group_array['sections'] = $sections;
			}

			// Convert into object
			$group_object = json_decode(json_encode($group_array));

			// Return array
			return $group_object;
		}

		// Check if record exists
		public function db_check() {

			// User capability check
			if(!WS_Form_Common::can_user('read_form')) { return false; }

			global $wpdb;

			$sql = sprintf("SELECT id FROM %s WHERE id = %u LIMIT 1;", $this->table_name, $this->id);
			$return_array = $wpdb->get_row($sql, 'ARRAY_A');

			return !is_null($return_array);
		}

		// Read all group data
		public function db_read_all($get_meta = true, $checksum = false, $bypass_user_capability_check = false) {

			// User capability check
			if(!$bypass_user_capability_check && !WS_Form_Common::can_user('read_form')) { return false; }

			self::db_check_form_id();

			global $wpdb;

			$fields_array = array();

			$sql = sprintf("SELECT %s FROM %s WHERE form_id = %u ORDER BY sort_index", self::DB_SELECT, $this->table_name, $this->form_id);
			$groups = $wpdb->get_results($sql, 'ARRAY_A');

			if($groups) {

				foreach($groups as $key => $group) {

					// Get sections
					$ws_form_section = New WS_Form_Section();
					$ws_form_section->group_id = $group['id'];
					$ws_form_section_return = $ws_form_section->db_read_all($get_meta, $checksum, $bypass_user_capability_check);
					$groups[$key]['sections'] = $ws_form_section_return;

					// Checksum
					if($checksum && isset($groups[$key]['date_updated'])) {

						unset($groups[$key]['date_updated']);
					}

					if($get_meta) {

						// Get meta data for each group
						$group_meta = New WS_Form_Meta();
						$group_meta->object = 'group';
						$group_meta->parent_id = $group['id'];
						$metas = $group_meta->db_read_all($bypass_user_capability_check);
						$groups[$key]['meta'] = $metas;
					}
				}

				return $groups;

			} else {

				return [];
			}
		}

		public function db_create_from_form_object($form_object, $next_sibling_id = 0) {

			// Check group ID
			self::db_check_form_id();

			// See if group ID set (if so we're going to delete it later as this will replace it)
			$group_id_old = $this->id ? $this->id : false;

			// Add form ID to lookups
			$this->new_lookup['form'][$form_object->id] = $this->form_id;

			// Check template has at least one group
			if(
				!isset($form_object->groups) ||
				!isset($form_object->groups[0]) ||
				!isset($form_object->groups[0]->id)
			) {
				parent::db_throw_error(__('No group found in form object', 'ws-form'));
			}

			// Get groups
			$groups = $form_object->groups;

			// Build array of touched groups
			$filter_group_ids = array();

			// Run through all first groups in existing template
			foreach($groups as $group) {

				// Process sort index
				$sort_index = self::db_object_sort_index_get($this->table_name, 'form_id', $this->form_id, $next_sibling_id);

				// Set sort index
				$group->sort_index = $sort_index;

				// Create new object and add new group ID to filter
				$filter_group_ids[] = self::db_update_from_object($group, true, true);
			}

			// Check if group ID set, if so this is a request to replace the existing group
			if($group_id_old) {

				$this->id = $group_id_old;
				$this->db_delete();
			}

			// Build array of touched conditional logic
			$filter_conditional_row_indexes = array();

			// Get conditional in form object
			$form_object_conditional = WS_Form_Common::get_object_meta_value($form_object, 'conditional', false);

			// Run through each condition in form object
			if(
				isset($form_object_conditional->groups) &&
				isset($form_object_conditional->groups[0]) &&
				isset($form_object_conditional->groups[0]->rows) &&
				is_array($form_object_conditional->groups[0]->rows) &&
				(count($form_object_conditional->groups[0]->rows) > 0)
			) {

				$rows = $form_object_conditional->groups[0]->rows;

				// Read conditional in existing form
				$ws_form_meta = New WS_Form_Meta();
				$ws_form_meta->object = 'form';
				$ws_form_meta->parent_id = $this->form_id;
				$conditional = $ws_form_meta->db_get_object_meta('conditional');

				// Create conditional data grid if it doesn't exist
				if(
					!isset($conditional->groups) ||
					!isset($conditional->groups[0]) ||
					!isset($conditional->groups[0]->rows) ||
					!is_array($conditional->groups[0]->rows)
				) {

					$meta_keys = WS_Form_Config::get_meta_keys();
					$conditional = json_decode(json_encode($meta_keys['conditional']['default']));
				}

				// Get next column row index
				$row_index_base = count($conditional->groups[0]->rows);

				foreach($rows as $row_index => $row) {

					// Add new conditional row
					$conditional->groups[0]->rows[] = $row;

					// Add row index to filter
					$filter_conditional_row_indexes[] = $row_index_base + $row_index;
				}

				$ws_form_meta->db_update_from_array(array('conditional' => $conditional));
			}

			// Create instance to run repair routines
			$ws_form_form = new WS_Form_Form();
			$ws_form_form->id = $this->form_id;

			// Set new lookups
			$ws_form_form->new_lookup = $this->new_lookup;

			// Fix data - Meta (Reads the form)
			$ws_form_form->db_meta_repair($filter_group_ids);

			// Fix data - Conditionals
			$ws_form_form->db_conditional_repair($filter_conditional_row_indexes);

			// Update checksum
			$ws_form_form->db_checksum();

			return $form_object->label;
		}

		// Delete
		public function db_delete($repair = true) {

			// User capability check
			if(!WS_Form_Common::can_user('edit_form')) { return false; }

			self::db_check_form_id();

			self::db_check_id();

			global $wpdb;

			// Delete group
			$sql = sprintf("DELETE FROM %s WHERE id = %u;", $this->table_name, $this->id);
			if($wpdb->query($sql) === false) { parent::db_wpdb_handle_error(__('Error deleting group', 'ws-form')); }

			// Delete meta
			$ws_form_meta = New WS_Form_Meta();
			$ws_form_meta->object = 'group';
			$ws_form_meta->parent_id = $this->id;
			$ws_form_meta->db_delete_by_object();

			// Delete groups sections
			$ws_form_section = New WS_Form_Section();
			$ws_form_section->form_id = $this->form_id;
			$ws_form_section->group_id = $this->id;
			$ws_form_section->db_delete_by_group(false);

			// Repair conditional, actions and meta data to remove references to this deleted field
 			if($repair) {

				$ws_form_form = New WS_Form_Form();
				$ws_form_form->id = $this->form_id;
				$ws_form_form->new_lookup['group'][$this->id] = '';
				$ws_form_form->db_conditional_repair();
				$ws_form_form->db_action_repair();
				$ws_form_form->db_meta_repair();
			}

			return true;
		}

		// Delete all groups in form
		public function db_delete_by_form($repair = true) {

			// User capability check
			if(!WS_Form_Common::can_user('edit_form')) { return false; }

			self::db_check_form_id();

			global $wpdb;

			if($repair) {

				$ws_form_form = New WS_Form_Form();
				$ws_form_form->id = $this->form_id;
			}

			$sql = sprintf("SELECT %s FROM %s WHERE form_id = %u", self::DB_SELECT, $this->table_name, $this->form_id);
			$groups = $wpdb->get_results($sql, 'ARRAY_A');

			if($groups) {

				foreach($groups as $key => $group) {

					// Delete group
					$this->id = $group['id'];
					self::db_delete(false);

					if($repair) {

						$ws_form_form->new_lookup['group'][$this->id] = '';
					}
				}
			}

			// Repair conditional, actions and meta data to remove references to these deleted groups
			if($repair) {

				$ws_form_form->db_conditional_repair();
				$ws_form_form->db_action_repair();
				$ws_form_form->db_meta_repair();
			}

			return true;
		}

		// Clone - All
		public function db_clone_all($form_id_copy_to) {

			// User capability check
			if(!WS_Form_Common::can_user('edit_form')) { return false; }

			global $wpdb;

			$sql = sprintf("SELECT %s FROM %s WHERE form_id = %u ORDER BY sort_index", self::DB_SELECT, $this->table_name, $this->form_id);
			$groups = $wpdb->get_results($sql, 'ARRAY_A');

			if($groups) {

				foreach($groups as $key => $group) {

					// Read data required for copying
					$this->id = $group['id'];
					$this->label = $group['label'];
					$this->sort_index = $group['sort_index'];
					$this->form_id = $form_id_copy_to;

					self::db_clone();
				}
			}
		}

		// Clone
		public function db_clone() {

			// User capability check
			if(!WS_Form_Common::can_user('edit_form')) { return false; }

			global $wpdb;

			// Clone group
			$sql = sprintf("INSERT INTO %s (%s) VALUES ('%s', %u, '%s', '%s', %u, %u);", $this->table_name, self::DB_INSERT, esc_sql($this->label), WS_Form_Common::get_user_id(), WS_Form_Common::get_mysql_date(), WS_Form_Common::get_mysql_date(), $this->sort_index, $this->form_id);
			if($wpdb->query($sql) === false) { parent::db_wpdb_handle_error(__('Error cloning group', 'ws-form')); }

			// Get new group ID
			$group_id_new = $wpdb->insert_id;

			// Clone meta data
			$ws_form_meta = New WS_Form_Meta();
			$ws_form_meta->object = 'group';
			$ws_form_meta->parent_id = $this->id;
			$ws_form_meta->db_clone_all($group_id_new);

			// Clone groups
			$ws_form_section = New WS_Form_Section();
			$ws_form_section->group_id = $this->id;
			$ws_form_section->db_clone_all($group_id_new);

			return $group_id_new;
		}

		// Get checksum of current form and store it to database
		public function db_checksum() {

			// Check form ID
			self::db_check_form_id();

			// Calculate new form checksum
			$form = New WS_Form_Form();
			$form->id = $this->form_id;
			$checksum = $form->db_checksum();

			return $checksum;
		}

		// API - POST - Download - JSON
		public function db_get_form_object($published = false) {

			// User capability check
			if(!$published && !WS_Form_Common::can_user('export_form')) { parent::api_access_denied(); }

			// Check form ID
			self::db_check_form_id();

			// Check section ID
			self::db_check_id();

			// Read section
			$group_object = self::db_read(true, true);

			// Read form
			$ws_form_form = new WS_Form_Form();
			$ws_form_form->id = $this->form_id;

			// Get form
			if($published) {

				$form_object = $ws_form_form->db_read_published();

			} else {

				$form_object = $ws_form_form->db_read(true, true);
			}

			// Clean form
			unset($form_object->checksum);
			unset($form_object->published_checksum);

			// Stamp form data
			$form_object->identifier = WS_FORM_IDENTIFIER;
			$form_object->version = WS_FORM_VERSION;
			$form_object->time = time();
			$form_object->status = 'draft';
			$form_object->count_submit = 0;
			$form_object->meta->tab_index = 0;
			$form_object->meta->export_object = 'section';

			// Set form label to section label
			$form_object->label = $group_object->label;

			// Remove groups
			while(isset($form_object->groups[1])) {

				unset($form_object->groups[1]);
				$form_object->groups = array_values($form_object->groups);
			}

			// Set group label to section label
			$form_object->groups[0] = $group_object;

			// Filter conditions
			$form_object = self::db_conditional_filter($form_object, $group_object);

			// Add checksum
			$form_object->checksum = md5(json_encode($form_object));

			return $form_object;
		}

		// Filter conditional logic
		public function db_conditional_filter($form_object, $group_object) {

			// Get section IDs
			foreach($group_object->sections as $section_object) {

				$this->new_lookup['section'][$section_object->id] = $section_object->id;

				// Get field IDs
				foreach($section_object->fields as $field_obj) {

					$this->new_lookup['field'][$field_obj->id] = $field_obj->id;
				}
			}

			// Get conditional configuration
			$settings_conditional = WS_Form_Config::get_settings_conditional();

			// Get parse variables repairable
			$parse_variables_repairable = WS_Form_Config::get_parse_variables_repairable();

			// Read conditional
			$conditional = WS_Form_Common::get_object_meta_value($form_object, 'conditional');

			// Data integrity check
			if(!isset($conditional->groups)) { return $form_object; }
			if(!isset($conditional->groups[0])) { return $form_object; }
			if(!isset($conditional->groups[0]->rows)) { return $form_object; }

			// Run through each conditional (data grid rows)
			$rows = $conditional->groups[0]->rows;

			foreach($rows as $row_index => $row) {

				// Data integrity check
				if(!isset($row->data)) { continue; }
				if(!isset($row->data[1])) { continue; }

				// Keep row
				$keep_row = false;

				$data = $row->data[1];

				// Data integrity check
				if(gettype($data) !== 'string') { continue; }
				if($data == '') { continue; }

				// Converts conditional JSON string to object
				$conditional_json_decode = json_decode($data);
				if(is_null($conditional_json_decode)) { continue; }

				// Process IF conditions
				$if = $conditional_json_decode->if;

				// Run through each group in $if
				foreach($if as $key_if => $group) {

					$conditions = $group->conditions;

					// Run through each condition
					foreach($conditions as $key_condition => $condition) {

						if(
							isset($condition->object) &&
							isset($this->new_lookup[$condition->object])
						) {

							// Section or field found
							if(isset($this->new_lookup[$condition->object][$condition->object_id])) {

								// Check to see if this logic is for a field (e.g. the logic has a field selector)
								if(
									isset($settings_conditional['objects'][$condition->object]) &&
									isset($settings_conditional['objects'][$condition->object]['logic']) &&
									isset($settings_conditional['objects'][$condition->object]['logic'][$condition->logic]) &&
									isset($settings_conditional['objects'][$condition->object]['logic'][$condition->logic]['values']) &&
									($settings_conditional['objects'][$condition->object]['logic'][$condition->logic]['values'] === 'fields')
								) {

									$keep_row = true;
								}
							}
						}

						// String replace - Field
						foreach($this->new_lookup['field'] as $field_id_old => $field_id_new) {

							if(isset($condition->value)) {

								foreach($parse_variables_repairable['field'] as $parse_variable) {

									if(
										(strpos($condition->value, '#' . $parse_variable . '(' . $field_id_old . ')') !== false) ||
										(strpos($condition->value, '#' . $parse_variable . '(' . $field_id_old . ',') !== false)
									) {

										$keep_row = true;
									}
								}
							}
						}

						// String replace - Section
						foreach($this->new_lookup['section'] as $section_id_old => $section_id_new) {

							if(isset($condition->value)) {

								foreach($parse_variables_repairable['section'] as $parse_variable) {

									if(
										(strpos($condition->value, '#' . $parse_variable . '(' . $section_id_old . ')') !== false)
									) {

										$keep_row = true;
									}
								}
							}
						}
					}
				}

				// Process THEN actions
				$then = $conditional_json_decode->then;

				// Run through each group in $then
				foreach($then as $key_then => $then_single) {

					if(isset($then_single->object) && isset($this->new_lookup[$then_single->object]) && isset($this->new_lookup[$then_single->object][$then_single->object_id])) {

						$keep_row = true;
					}

					// String replace - Field
					foreach($this->new_lookup['field'] as $field_id_old => $field_id_new) {

						if(isset($then_single->value)) {

							foreach($parse_variables_repairable['field'] as $parse_variable) {

								if(
									(strpos($then_single->value, '#' . $parse_variable . '(' . $field_id_old . ')') !== false) ||
									(strpos($then_single->value, '#' . $parse_variable . '(' . $field_id_old . ',') !== false)
								) {

									$keep_row = true;
								}
							}
						}
					}

					// String replace - Section
					foreach($this->new_lookup['section'] as $section_id_old => $section_id_new) {

						if(isset($then_single->value)) {

							foreach($parse_variables_repairable['section'] as $parse_variable) {

								if(
									(strpos($then_single->value, '#' . $parse_variable . '(' . $section_id_old . ')') !== false)
								) {

									$keep_row = true;
								}
							}
						}
					}
				}

				// Process ELSE actions
				$else = $conditional_json_decode->else;

				// Run through each group in $else
				foreach($else as $key_else => $else_single) {

					if(isset($else_single->object) && isset($this->new_lookup[$else_single->object]) && isset($this->new_lookup[$else_single->object][$else_single->object_id])) {

						$keep_row = true;
					}

					// String replace - Field
					foreach($this->new_lookup['field'] as $field_id_old => $field_id_new) {

						if(isset($else_single->value)) {

							foreach($parse_variables_repairable['field'] as $parse_variable) {

								if(
									(strpos($else_single->value, '#' . $parse_variable . '(' . $field_id_old . ')') !== false) ||
									(strpos($else_single->value, '#' . $parse_variable . '(' . $field_id_old . ',') !== false)
								) {

									$keep_row = true;
								}
							}
						}
					}

					// String replace - Section
					foreach($this->new_lookup['section'] as $section_id_old => $section_id_new) {

						if(isset($else_single->value)) {

							foreach($parse_variables_repairable['section'] as $parse_variable) {

								if(
									(strpos($else_single->value, '#' . $parse_variable . '(' . $section_id_old . ')') !== false)
								) {

									$keep_row = true;
								}
							}
						}
					}
				}

				if(!$keep_row) {

					unset($form_object->meta->conditional->groups[0]->rows[$row_index]);
				}
			}

			// Reset row indexes
			$form_object->meta->conditional->groups[0]->rows = array_values($form_object->meta->conditional->groups[0]->rows);

			return $form_object;
		}

		// Push group from array
		public function db_update_from_object($group_object, $full = true, $new = false) {

			// User capability check
			if(!WS_Form_Common::can_user('edit_form')) { return false; }

			// Check for group ID in $group
			if(isset($group_object->id) && !$new) { $this->id = intval($group_object->id); }
			if($new) {

				$this->id = 0;
				$group_object_id_old = (isset($group_object->id)) ? intval($group_object->id) : 0;
				if(isset($group_object->id)) { unset($group_object->id); }
			}

			// Update / Insert
			$this->id = parent::db_update_insert($this->table_name, self::DB_UPDATE, self::DB_INSERT, $group_object, 'group', $this->id);
			if($new && $group_object_id_old) { $this->new_lookup['group'][$group_object_id_old] = $this->id; }

			// Base meta for new records
			if(!isset($group_object->meta) || !is_object($group_object->meta)) { $group_object->meta = new stdClass(); }
			if($new) {

				$settings_form_admin = WS_Form_Config::get_settings_form_admin();
				$meta_data = $settings_form_admin['sidebars']['group']['meta'];
				$meta_keys = WS_Form_Config::get_meta_keys();
				$meta_data_array = self::build_meta_data($meta_data, $meta_keys);
				$group_object->meta = (object) array_merge($meta_data_array, (array) $group_object->meta);
			}

			// Update meta
			if(isset($group_object->meta)) {

				$ws_form_meta = New WS_Form_Meta();
				$ws_form_meta->object = 'group';
				$ws_form_meta->parent_id = $this->id;
				$ws_form_meta->db_update_from_object($group_object->meta);
			}

			if($full) {

				// Update sections
				if(isset($group_object->sections)) {

					$ws_form_section = New WS_Form_Section();
					$ws_form_section->group_id = $this->id;
					$ws_form_section->db_update_from_array($group_object->sections, $new);

					if($new) {
						$this->new_lookup['section'] = $this->new_lookup['section'] + $ws_form_section->new_lookup['section'];
						$this->new_lookup['field'] = $this->new_lookup['field'] + $ws_form_section->new_lookup['field'];
					}
				}
			}

			return $this->id;
		}

		// Push all groups from array (including all sections, fields)
		public function db_update_from_array($groups, $new = false) {

			// User capability check
			if(!WS_Form_Common::can_user('edit_form')) { return false; }

			self::db_check_form_id();

			global $wpdb;

			// Change date_updated to null for all records
			$wpdb->update($this->table_name, array('date_updated' => null), array('form_id' => $this->form_id));

			foreach($groups as $group) {

				self::db_update_from_object($group, true, $new);
			}

			// Delete any groups that were not updated
			$wpdb->delete($this->table_name, array('date_updated' => null, 'form_id' => $this->form_id));

			return true;
		}

		// Check form_id
		public function db_check_form_id() {

			if(intval($this->form_id) <= 0) { parent::db_throw_error(__('Invalid form ID', 'ws-form')); }
			return true;
		}

		// Check id
		public function db_check_id() {

			if(intval($this->id) <= 0) { parent::db_throw_error(__('Invalid group ID', 'ws-form')); }
			return true;
		}

		// Get group label
		public function db_get_label() {

			// User capability check
			if(!WS_Form_Common::can_user('read_form')) { return false; }

			return parent::db_object_get_label($this->table_name, $this->id);
		}

		// Save tab index
		public function db_tab_index_save($parameters) {

			// User capability check
			if(!WS_Form_Common::can_user('edit_form')) { return false; }

			// Store tab index to form meta
			$form_tab_index = intval(WS_Form_Common::get_query_var('wsf_fti', false, $parameters));
			if($form_tab_index !== false) {

				$group_meta = New WS_Form_Meta();
				$group_meta->object = 'form';
				$group_meta->parent_id = $this->form_id;
				$group_meta->db_update_from_object((object) array('tab_index' => $form_tab_index));
			}

			return $form_tab_index;
		}
	}