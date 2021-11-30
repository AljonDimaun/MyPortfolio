<?php

	class WS_Form_Submit extends WS_Form_Core {

		public $id = 0;
		public $form_id;
		public $date_added;
		public $date_updated;
		public $date_expire;
		public $user_id;
		public $hash;
		public $token;
		public $token_validated;
		public $duration;
		public $count_submit;
		public $status;
		public $actions;
		public $section_repeatable;
		public $preview;
		public $spam_level;
		public $starred;
		public $viewed;

		public $meta;
		public $meta_protected;

		public $post_mode;

		public $form_object;

		public $error;
		public $error_message;
		public $error_code;

		public $error_validation_actions;

		public $table_name;
		public $table_name_meta;

		public $bypass_required_array;
		public $hidden_array;

		public $field_types;

		public $file_objects = array();

		const DB_INSERT = 'form_id,date_added,date_updated,date_expire,user_id,hash,token,token_validated,duration,count_submit,status,actions,section_repeatable,preview,spam_level,starred,viewed,encrypted';
		const DB_UPDATE = 'form_id,date_added,date_updated,date_expire,user_id,hash,token,token_validated,duration,count_submit,status,actions,section_repeatable,preview,spam_level,starred,viewed,encrypted';
		const DB_SELECT = 'form_id,date_added,date_updated,date_expire,user_id,hash,token,token_validated,duration,count_submit,status,actions,section_repeatable,preview,spam_level,starred,viewed,encrypted,id';

		public function __construct() {

			global $wpdb;

			$this->id = 0;
			$this->form_id = 0;
			$this->user_id = WS_Form_Common::get_user_id(false);
			$this->table_name = $wpdb->prefix . WS_FORM_DB_TABLE_PREFIX . 'submit';
			$this->table_name_meta = $this->table_name . '_meta';
			$this->hash = '';
			$this->token = false;
			$this->token_validated = false;
			$this->status = 'draft';
			$this->duration = 0;
			$this->count_submit = 0;
			$this->meta = array();
			$this->meta_protected = array();
			$this->actions = '';
			$this->section_repeatable = '';
			$this->preview = false;
			$this->date_added = WS_Form_Common::get_mysql_date();
			$this->date_updated = WS_Form_Common::get_mysql_date();
			$this->date_expire = null;
			$this->spam_level = null;
			$this->starred = false;
			$this->viewed = false;

			$this->post_mode = false;

			$this->error = false;
			$this->error_message = '';
			$this->error_code = 200;

			$this->error_validation_actions = array();
			$this->error_validation_action_field = array();

			$this->encrypted = false;
			// Get field types in single dimension array
			$this->field_types = false;
		}

		// Create
		public function db_create($update_count_submit_unread = true) {

			// No capabilities required, this is a public method

			// Check form ID
			self::db_check_form_id();

			global $wpdb;

			// get_user_id(false) = Does not exit on zero
			$sql = sprintf("INSERT INTO %s (%s) VALUES (%u, '%s', '%s', %s, %u, '', '', 0, %u, %u, '%s', '%s', '%s', %u, %s, %u, %u, %u);", $this->table_name, self::DB_INSERT, $this->form_id, $this->date_added, $this->date_updated, (is_null($this->date_expire) ? 'NULL' : "'" . $this->date_expire . "'"), $this->user_id, $this->duration, $this->count_submit, esc_sql($this->status), esc_sql($this->actions), esc_sql($this->section_repeatable), ($this->preview ? 1 : 0), (is_null($this->spam_level) ? 'NULL' : $this->spam_level), ($this->starred ? 1 : 0), ($this->viewed ? 1 : 0), ($this->encrypted ? 1 : 0));
			if($wpdb->query($sql) === false) { parent::db_wpdb_handle_error(__('Error adding submit', 'ws-form')); }

			// Get inserted ID
			$this->id = $wpdb->insert_id;

			// Create hash
			self::db_create_hash();

			// Create token
			self::db_create_token();

			// Update hash
			$sql = sprintf("UPDATE %s SET hash = '%s', token = '%s' WHERE id = %u LIMIT 1", $this->table_name, esc_sql($this->hash), esc_sql($this->token), $this->id);
			if($wpdb->query($sql) === false) { parent::db_wpdb_handle_error(__('Error updating submit', 'ws-form')); }

			// Update form submit unread count statistic
			if($update_count_submit_unread) {

				$ws_form_form = new WS_Form_Form();
				$ws_form_form->id = $this->form_id;
				$ws_form_form->db_update_count_submit_unread();
			}
		}

		// Read record to array
		public function db_read($get_meta = true, $get_expanded = true, $bypass_user_capability_check = false) {

			// User capability check
			if(!$bypass_user_capability_check && !WS_Form_Common::can_user('read_submission')) { return false; }

			self::db_check_id();

			global $wpdb;

			// Add fields
			$sql = sprintf("SELECT %s FROM %s WHERE id = %u LIMIT 1;", self::DB_SELECT, $this->table_name, $this->id);
			$submit_array = $wpdb->get_row($sql, 'ARRAY_A');
			if(is_null($submit_array)) { parent::db_wpdb_handle_error(__('Unable to read submission', 'ws-form')); }

			// Set class variables
			foreach($submit_array as $key => $value) {

				$this->{$key} = $value;
			}

			// Convert into object
			$submit_object = json_decode(json_encode($submit_array));

			// Process meta data
			if($get_meta) {

				$this->meta = $submit_object->meta = self::db_get_submit_meta($submit_object, false, $bypass_user_capability_check);
			}

			// Get user data
			if($get_expanded) {

				self::db_read_expanded($submit_object, true, true, true, true, true, true, true, $bypass_user_capability_check);
			}

			// Preview to boolean
			if(isset($this->preview)) { $this->preview = $submit_object->preview = (bool) $this->preview; }

			// Encrypted to boolean
			if(isset($this->encrypted)) { $this->encrypted = $submit_object->encrypted = (bool) $this->encrypted; }

			// Return array
			return $submit_object;
		}

		// Read expanded data for a record
		public function db_read_expanded(&$submit_object, $expand_user = true, $expand_date_added = true, $expand_date_updated = true, $expand_status = true, $expand_actions = true, $expand_section_repeatable = true, $expand_file_objects = true, $bypass_user_capability_check = false) {

			// User capability check
			if(!$bypass_user_capability_check && !WS_Form_Common::can_user('read_submission')) { return false; }

			if($expand_user && isset($submit_object->user_id) && ($submit_object->user_id > 0)) {

				$user = get_user_by('ID', $submit_object->user_id);
				if($user !== false) {

					$this->user = $submit_object->user = (object) array(

						'first_name' 	=>	$user->first_name,
						'last_name' 	=>	$user->last_name,
						'display_name'	=> $user->display_name
					);
				}
			}

			// Date added
			if($expand_date_added && isset($submit_object->date_added)) {

				$this->date_added_wp = $submit_object->date_added_wp = date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime(get_date_from_gmt($submit_object->date_added)));
			}

			// Date updated
			if($expand_date_updated && isset($submit_object->date_updated)) {

				$this->date_updated_wp = $submit_object->date_updated_wp = date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime(get_date_from_gmt($submit_object->date_updated)));
			}

			// Status
			if($expand_status && isset($submit_object->status)) {

				$this->status_full = $submit_object->status_full = self::db_get_status_name($submit_object->status);
			}

			// Unserialize actions
			if($expand_actions && isset($submit_object->actions)) {

				$this->actions = $submit_object->actions = is_serialized($submit_object->actions) ? unserialize($submit_object->actions) : false;
			}

			// Unserialize section_repeatable
			if($expand_section_repeatable && isset($submit_object->section_repeatable)) {

				$this->section_repeatable = $submit_object->section_repeatable = is_serialized($submit_object->section_repeatable) ? unserialize($submit_object->section_repeatable) : false;
			}

			// File objects
			if($expand_file_objects && isset($submit_object->meta)) {

				$metas = (array) $submit_object->meta;

				foreach($metas as $meta_key => $meta) {

					$meta = (array) $meta;

					// Add URLs to file objects all objects
					if(
						isset($meta['type']) &&
						(($meta['type'] == 'file') || ($meta['type'] == 'signature')) &&
						isset($meta['value']) &&
						is_array($meta['value']) &&
						(count($meta['value']) > 0) &&
						is_array($meta['value'][0]) &&
						isset($meta['id'])
					) {

						foreach($meta['value'] as $file_object_index => $file_object) {

							if(
								isset($file_object['url']) ||
								!isset($file_object['name']) ||
								!isset($file_object['size']) ||
								!isset($file_object['type']) ||
								!isset($file_object['path'])

							) { continue; }

							// Get handler
							$handler = isset($file_object['handler']) ? $file_object['handler'] : 'wsform';

							// Get URL
							if(isset(WS_Form_File_Handler_WS_Form::$file_handlers[$handler])) {

								$url = WS_Form_File_Handler_WS_Form::$file_handlers[$handler]->get_url($file_object, $meta['id'], $file_object_index, $submit_object->hash);

							} else {

								$url = '#';
							}

							$this->meta[$meta_key]['value'][$file_object_index]['url'] = $submit_object->meta[$meta_key]['value'][$file_object_index]['url'] = $url;
						}
					}
				}
			}
		}

		// Read - All
		public function db_read_all($join = '', $where = '', $order_by = '', $limit = '', $offset = '', $get_meta = true, $get_expanded = true, $bypass_user_capability_check = false, $clear_hidden_fields = false) {

			// User capability check
			if(!$bypass_user_capability_check && !WS_Form_Common::can_user('read_submission')) { return false; }

			global $wpdb;

			// Get form data
			$select = self::DB_SELECT;
			if($join != '') {

				$select_array = explode(',', $select);
				foreach($select_array as $key => $select) {

					$select_array[$key] = $this->table_name . '.' . $select;
				}
				$select = implode(',', $select_array);
			}

			$sql = sprintf("SELECT %s FROM %s", $select, $this->table_name);

			if($join != '') { $sql .= sprintf(" %s", $join); }
			if($where != '') { $sql .= sprintf(" WHERE %s", $where); }
			if($order_by != '') { $sql .= sprintf(" ORDER BY %s", $order_by); }
			if($limit != '') { $sql .= sprintf(" LIMIT %s", $limit); }
			if($offset != '') { $sql .= sprintf(" OFFSET %s", $offset); }

			$return_array = $wpdb->get_results($sql);
			if(is_null($return_array)) { return; }

			foreach($return_array as $key => $submit_object) {

				// Check form ID
				if(intval($submit_object->form_id) === 0) {

					// Delete this orphaned submit record
					$this->id = $submit_object->id;
					self::db_delete(true);

					// Remove from return array
					unset($return_array[$key]);

					continue;
				}

				// Process meta data
				if($get_meta) {

					// Get meta data
					$submit_object->meta = self::db_get_submit_meta($submit_object, false, $bypass_user_capability_check);

					// Clear hidden fields
					if($clear_hidden_fields) {

						$submit_object = self::clear_hidden_meta_values($submit_object);
					}
				}
	
				// Process expanded data
				if($get_expanded) {

					self::db_read_expanded($submit_object, true, true, true, true, true, true, true, $bypass_user_capability_check);
				}

				$return_array[$key] = $submit_object;
			}

			return $return_array;
		}

		// Read - Count
		public function db_read_count($join = '', $where = '', $bypass_user_capability_check = false) {

			// User capability check
			if(!$bypass_user_capability_check && !WS_Form_Common::can_user('read_submission')) { return false; }

			global $wpdb;

			// Get form data
			$select = self::DB_SELECT;
			if($join != '') {

				$select_array = explode(',', $select);
				foreach($select_array as $key => $select) {

					$select_array[$key] = $this->table_name . '.' . $select;
				}
				$select = implode(',', $select_array);
			}

			$sql = sprintf("SELECT COUNT(id) FROM %s", $this->table_name);

			if($join != '') { $sql .= sprintf(" %s", $join); }
			if($where != '') { $sql .= sprintf(" WHERE %s", $where); }

			$read_count = $wpdb->get_var($sql);
			if(is_null($read_count)) { return 0; }

			return $read_count;
		}

		// Read by hash
		public function db_read_by_hash($get_meta = true, $get_expanded = true, $form_id_check = true, $bypass_user_capability_check = false) {

			// User capability check
			if(!$bypass_user_capability_check && !WS_Form_Common::can_user('read_submission')) { return false; }

			// Check form ID
			if($form_id_check) { self::db_check_form_id(); }

			// Check hash
			if(!WS_Form_Common::check_submit_hash($this->hash)) {

				$this->hash = '';
				return false;
			}

			// Check token
			if($this->token !== false) {

				if(!WS_Form_Common::check_submit_hash($this->token)) {

					$this->token = '';
					return false;
				}

				$token_check = $this->token;

			} else {

				$token_check = false;
			}

			global $wpdb;

			// Get form submission
			if($form_id_check) {

				$sql = sprintf("SELECT %s FROM %s WHERE form_id = %u AND hash = '%s' LIMIT 1;", self::DB_SELECT, $this->table_name, $this->form_id, $this->hash);
			} else {

				$sql = sprintf("SELECT %s FROM %s WHERE hash = '%s' LIMIT 1;", self::DB_SELECT, $this->table_name, $this->hash);				
			}
			$submit_array = $wpdb->get_row($sql, 'ARRAY_A');
			if(is_null($submit_array)) { $this->hash = ''; return false; }

			// Set class variables
			foreach($submit_array as $key => $value) {

				$this->{$key} = $value;
			}

			// Convert into object
			$submit_object = json_decode(json_encode($submit_array));

			// Process meta data
			if($get_meta) {

				$this->meta = $submit_object->meta = self::db_get_submit_meta($submit_object, false, $bypass_user_capability_check);
			}

			// Get user data
			if($get_expanded) {

				self::db_read_expanded($submit_object, true, true, true, true, true, true, true, $bypass_user_capability_check);
			}

			// Perform token validation
			if(!$this->token_validated && ($token_check !== false)) {

				if($this->token === $token_check) {

					$this->token_validated = $submit_object->token_validated = true;

					// Update hash
					$sql = sprintf("UPDATE %s SET token_validated = 1, spam_level = 0 WHERE id = %u LIMIT 1", $this->table_name, $this->id);
					if($wpdb->query($sql) === false) { parent::db_wpdb_handle_error(__('Error updating submit', 'ws-form')); }
				}
			}

			// Return array
			return $submit_object;
		}

		// Update current submit
		public function db_update() {

			// No capabilities required, this is a public method

			// Check ID
			self::db_check_id();

			// Update / Insert
			$this->id = parent::db_update_insert($this->table_name, self::DB_UPDATE, self::DB_INSERT, $this, 'submit', $this->id);

			// Update meta
			if(isset($this->meta)) {

				$ws_form_submit_meta = New WS_Form_Submit_Meta();
				$ws_form_submit_meta->parent_id = $this->id;
				$ws_form_submit_meta->db_update_from_object($this->meta, $this->encrypted);
			}
		}

		// Push submit from array
		public function db_update_from_object($submit_object) {

			// No capabilities required, this is a public method

			// Check for submit ID in $submit
			if(isset($submit_object->id)) { $this->id = intval($submit_object->id); } else { return false; }

			// Encryption
			$submit_encrypted = isset($submit_object->encrypted) ? $submit_object->encrypted : false;

			// Update / Insert
			$this->id = parent::db_update_insert($this->table_name, self::DB_UPDATE, self::DB_INSERT, $submit_object, 'submit', $this->id);

			// Update meta
			if(isset($submit_object->meta)) {

				$ws_form_submit_meta = New WS_Form_Submit_Meta();
				$ws_form_submit_meta->parent_id = $this->id;
				$ws_form_submit_meta->db_update_from_object($submit_object->meta, $submit_encrypted);
			}
		}

		// Stamp submit with date updated, increase submit count and add duration (if available)
		public function db_stamp() {

			// No capabilities required, this is a public method

			// Check ID
			self::db_check_id();

			// Get duration
			$this->duration = intval(WS_Form_Common::get_query_var_nonce('wsf_duration', 0));

			global $wpdb;

			// Date updated, count submit + 1
			$sql = sprintf("UPDATE %s SET date_updated = '%s', count_submit = count_submit + 1, duration = %u WHERE id = %u LIMIT 1", $this->table_name, WS_Form_Common::get_mysql_date(), $this->duration, $this->id);
			if($wpdb->query($sql) === false) { parent::db_wpdb_handle_error(__('Error updating submit date updated', 'ws-form')); }
			$this->count_submit++;

			// User ID
			$sql = sprintf("UPDATE %s SET user_id = %u WHERE id = %u AND (user_id = 0 OR user_id IS NULL) LIMIT 1", $this->table_name, WS_Form_Common::get_user_id(false), $this->id);
			if($wpdb->query($sql) === false) { parent::db_wpdb_handle_error(__('Error updating submit user ID', 'ws-form')); }
		}

		// Delete
		public function db_delete($permanent_delete = false, $count_update = true) {

			// User capability check
			if(!WS_Form_Common::can_user('delete_submission')) { return false; }

			self::db_check_id();

			// Read the submit status
			self::db_read(false, false);

			if(in_array($this->status, array('spam', 'trash'))) { $permanent_delete = true; }

			// If status is trashed, do a permanent delete of the data
			if($permanent_delete) {

				global $wpdb;

				// Delete submit
				$sql = sprintf("DELETE FROM %s WHERE id = %u;", $this->table_name, $this->id);
				if($wpdb->query($sql) === false) { parent::db_wpdb_handle_error(__('Error deleting submit', 'ws-form')); }

				// Delete meta
				$ws_form_meta = New WS_Form_Submit_Meta();
				$ws_form_meta->parent_id = $this->id;
				$ws_form_meta->db_delete_by_submit();

			} else {

				// Set status to 'trash'
				self::db_set_status('trash', $count_update);
			}

			return true;
		}

		// Delete trashed submits
		public function db_trash_delete() {

			// User capability check
			if(!WS_Form_Common::can_user('delete_submission')) { return false; }

			self::db_check_form_id();

			// Get all trashed forms
			$submits = self::db_read_all('', "status='trash' AND form_id=" . $this->form_id, '', '', '', false, false);

       		foreach($submits as $submit_object) {

				$this->id = $submit_object->id;
				self::db_delete();
			}

			return true;
		}

		// Export by email
		public function db_exporter($email_address) {

			global $wpdb;

			// User capability check
			if(!WS_Form_Common::can_user('read_submission')) { return false; }

			// Check email address
			if(!filter_var($email_address, FILTER_VALIDATE_EMAIL)) { return false; }

			$data_to_export = array();

			// Get submit records
			$sql = sprintf('SELECT %1$s.id FROM %2$s LEFT OUTER JOIN %1$s ON %1$s.id = %2$s.parent_id WHERE (LOWER(%2$s.meta_value) = \'%3$s\') AND NOT (%1$s.id IS NULL);', $this->table_name, $this->table_name_meta, esc_sql(strtolower($email_address)));

			$submissions = $wpdb->get_results($sql);

			// Process results
			if($submissions) {

				foreach($submissions as $submission) {

					// Reset submit data
					$submit_data = array();

					// Get submit ID
					$submit_id = $submission->id;

					// Get submit record
					$this->id = $submit_id;
					$submit_object = self::db_read();

					// Remove some data that will not be shared for security reasons or internal only
					unset($submit_object->form_id);
					unset($submit_object->user_id);
					unset($submit_object->id);
					unset($submit_object->actions);
					unset($submit_object->preview);
					unset($submit_object->status);


					// Push all submit data
					foreach($submit_object as $key => $value) {

						// Convert objects to array (e.g. user data)
						if(is_object($value)) {

							$value = (array) $value;
						}

						if(is_array($value)) {
							
							foreach($value as $meta_key => $meta_value) {

								if(is_object($meta_value)) {

									$meta_value = (array) $meta_value;
								}

								if(is_array($meta_value)) {

									$value = $meta_value['value'];

									if(is_object($value)) {

										$value = (array) $value;
									}

									if(is_array($value)) {

										$value = implode(',', $value);
									}

								} else {

									$value = $meta_value;											
								}

								$submit_data[] = array('name' => $meta_key, 'value' => $value);
							}

						} else {
							
							$submit_data[] = array('name' => $key, 'value' => $value);
						}
					}

					$data_to_export[] = array(
						'group_id'    => WS_FORM_USER_REQUEST_IDENTIFIER,
						'group_label' => __('Form Submissions', 'ws-form'),
						'item_id'     => WS_FORM_USER_REQUEST_IDENTIFIER . '-' . $submit_object->hash,
						'data'        => $submit_data
					);
				}
			}

			// Return
			return array(

				'data' => $data_to_export,
				'done' => true,
			);
		}

		// Erase by email
		public function db_eraser($email_address) {

			global $wpdb;

			// User capability check
			if(!WS_Form_Common::can_user('delete_submission')) { return false; }

			// Check email address
			if(!filter_var($email_address, FILTER_VALIDATE_EMAIL)) { return false; }

			// Return array
			$items_removed_count = 0;
			$items_retained_count = 0;

			// Get submit records to be deleted
			$sql = sprintf('SELECT %1$s.id FROM %2$s LEFT OUTER JOIN %1$s ON %1$s.id = %2$s.parent_id WHERE (LOWER(%2$s.meta_value) = \'%3$s\') AND NOT (%1$s.id IS NULL);', $this->table_name, $this->table_name_meta, esc_sql(strtolower($email_address)));

			$submissions = $wpdb->get_results($sql);

			// Process results
			if($submissions) {

				$items_retained_count = count($submissions);

				if($items_retained_count > 0) {

					// Get first record (Delete one record each time eraser is requested to avoid timeouts)
					if(isset($submissions[0]->id)) {

						// Delete submit record with permanent delete
						$this->id = $submissions[0]->id;
						self::db_delete(true);

						$items_retained_count--;
						$items_removed_count++;
					}
				}
			}

			// Build return values
			$items_removed = ($items_removed_count > 0);
			$items_retained = ($items_retained_count > 0);
			$done = ($items_retained <= 0);
			$messages = (($items_removed > 0) && ($items_retained <= 0)) ? array(__('WS Form submissions successfully deleted.', 'ws-form')) : array();

			// Return
			return array(

				'items_removed' => $items_removed,
				'items_retained' => $items_retained,
				'messages' => $messages,
				'done' => $done,
			);
		}

		// Delete expired
		public function db_delete_expired($count_update_all = true) {

			global $wpdb;

			$sql = sprintf("UPDATE %s SET status = 'trash' WHERE (NOT date_expire IS NULL) AND (NOT date_expire = '0000-00-00 00:00:00') AND (NOT status = 'trash') AND (date_expire < '%s')", $this->table_name, WS_Form_Common::get_mysql_date());
			$rows_affected = $wpdb->query($sql);

			// Update form submit unread count statistic
			if($count_update_all) {

				$ws_form_form = new WS_Form_Form();
				$ws_form_form->db_count_update_all();
			}

			return $rows_affected;
		}

		// Get submission count by status
		public function db_get_count_by_status($form_id = 0, $status = '') {

			// User capability check
			if(!WS_Form_Common::can_user('read_submission')) { return false; }

			if(!WS_Form_Common::check_submit_status($status, false)) { $status = ''; }
			if($form_id == 0) { return 0; }

			global $wpdb;

			$sql = sprintf("SELECT COUNT(id) FROM %s WHERE", $this->table_name);
			if($status == '') { $sql .= " NOT(status = 'trash' OR status = 'spam')"; } else { $sql .= " status = '$status'"; }
			$sql .= " AND form_id = $form_id;";

			$form_count = $wpdb->get_var($sql);
			if(is_null($form_count)) { $form_count = 0; }

			return $form_count; 
		}

		// Get submit meta
		public function db_get_submit_meta($submit_object, $meta_array = false, $bypass_user_capability_check = false) {

			// No capabilities required, this is a public method
			$submit_meta = array();

			// Get submit record ID
			$submit_id = $submit_object->id;
			$submit_encrypted = isset($submit_object->encrypted) ? $submit_object->encrypted : false;

			// Read meta
			if(!is_array($meta_array)) {

				$ws_form_submit_meta = New WS_Form_Submit_Meta();
				$ws_form_submit_meta->parent_id = $submit_id;
				$meta_array = $ws_form_submit_meta->db_read_all($bypass_user_capability_check, $submit_encrypted);
			}

			$field_cache = array();

			// Process meta data
			foreach($meta_array as $index => $meta) {

				// Get field value
				$value = is_serialized($meta['meta_value']) ? unserialize($meta['meta_value']) : $meta['meta_value'];

				// Get field ID
				$field_id = intval($meta['field_id']);

				// If field ID found, process and return as array including type
				if($field_id > 0) {

					// Load field data to cache
					if(isset($field_cache[$field_id])) {

						// Use cached version
						$field_object = $field_cache[$field_id];

					} else {

						// Read field data and get type
						$ws_form_field = New WS_Form_Field();
						$ws_form_field->id = $field_id;
						$field_object = $ws_form_field->db_read(true, $bypass_user_capability_check);
						$field_cache[$field_id] = $field_object;
					}

					// If field no longer exists, just return the value
					if($field_object === false) {

						$submit_meta[$meta['meta_key']] = $value;
						continue;
					}

					// Get field type
					$field_type = $field_object->type;

					// If field type not known, skip
					if($this->field_types === false) { $this->field_types = WS_Form_Config::get_field_types_flat(); }
					if(!isset($this->field_types[$field_type])) { continue; };
					$field_type_config = $this->field_types[$field_type];

					// Legacy date format support
					if(
						($field_type === 'datetime') &&
						is_array($value) &&
						isset($value['mysql'])
					) {
						$value = $value['mysql'];
					}

					// Submit array
					$field_submit_array = (isset($field_type_config['submit_array'])) ? $field_type_config['submit_array'] : false; 

					// Build meta key
					$meta_key = is_null($meta['meta_key']) ? (WS_FORM_FIELD_PREFIX . $field_id) : $meta['meta_key'];

					// Check for repeater
					$repeatable_index = (
						isset($meta['repeatable_index']) &&
						(intval($meta['repeatable_index']) > 0)
					) ? intval($meta['repeatable_index']) : false;

					// Check for section_id
					$section_id = (
						isset($meta['section_id']) &&
						(intval($meta['section_id']) > 0)
					) ? intval($meta['section_id']) : false;

					// Check for repeatable_delimiter_section
					$section_repeatable_section_string = 'section_' . $section_id;
					$section_repeatable_delimiter_section = (
						isset($this->section_repeatable[$section_repeatable_section_string]) &&
						isset($this->section_repeatable[$section_repeatable_section_string]['delimiter_section'])
					) ? $this->section_repeatable[$section_repeatable_section_string]['delimiter_section'] : WS_FORM_SECTION_REPEATABLE_DELIMITER_SECTION;

					// Check for repeatable_delimiter_row
					$section_repeatable_delimiter_row = (
						isset($this->section_repeatable[$section_repeatable_section_string]) &&
						isset($this->section_repeatable[$section_repeatable_section_string]['delimiter_row'])
					) ? $this->section_repeatable[$section_repeatable_section_string]['delimiter_row'] : WS_FORM_SECTION_REPEATABLE_DELIMITER_ROW;

					// Build meta data
					$meta_data = array('id' => $field_id, 'value' => $value, 'type' => $field_type, 'section_id' => $section_id, 'repeatable_index' => $repeatable_index);

					// Add to submit meta
					$submit_meta[$meta_key] = $meta_data;

					// Build fallback value
					if($repeatable_index !== false) {

						$meta_key_base = WS_FORM_FIELD_PREFIX . $field_id;

						$submit_meta_not_set = !isset($submit_meta[$meta_key_base]);

						if($submit_meta_not_set) {

							$submit_meta[$meta_key_base] = $meta_data;
							$submit_meta[$meta_key_base]['db_ignore'] = true;
							$submit_meta[$meta_key_base]['repeatable_index'] = false;
						}

						switch($field_type) {

							// Arrays
							case 'file' :
							case 'signature' :
							case 'googlemap' :

								if(!is_array($value)) { $value = array(); }

								if($submit_meta_not_set) {

									$submit_meta[$meta_key_base]['value'] = $value;

								} else {

									foreach($value as $file) {

										$submit_meta[$meta_key_base]['value'][] = $file;
									}
								}
								break;

							// Strings
							default :

								if($submit_meta_not_set) {

									$submit_meta[$meta_key_base]['value'] = self::field_value_stringify($field_object, $submit_meta[$meta_key_base]['value'], $field_submit_array, $section_repeatable_delimiter_row);

								} else {

									$submit_meta[$meta_key_base]['value'] .= $section_repeatable_delimiter_section . self::field_value_stringify($field_object, $value, $field_submit_array, $section_repeatable_delimiter_row);
								}
						}

						// Store raw array values
						$submit_array = isset($field_type_config['submit_array']) ? $field_type_config['submit_array'] : false;
						if($submit_array) {

							if(!is_array($value)) { $value = array($value); }

							if($submit_meta_not_set) {

								$submit_meta[$meta_key_base]['value_array'] = $value;

							} else {

								$submit_meta[$meta_key_base]['value_array'] = array_merge($submit_meta[$meta_key_base]['value_array'], $value);
							}
						}
					}

				} else {

					// Return as string
					$submit_meta[$meta['meta_key']] = $value;
				}
			}

			return $submit_meta;
		}

		// Get number for form submissions
		public function db_get_count_submit() {

			// User capability check
			if(!WS_Form_Common::can_user('read_submission')) { return false; }

			// Check form ID
			self::db_check_form_id();

			global $wpdb;

			// Get total number for form submissions
			$sql = sprintf("SELECT COUNT(id) AS count_submit FROM %s WHERE form_id = %u AND NOT (status = 'trash');", $this->table_name, $this->form_id);
			$count_submit = $wpdb->get_var($sql);
			if(!is_null($count_submit)) { return intval($count_submit); } else { return 0; }
		}

		// Get number for form submissions unread
		public function db_get_count_submit_unread($bypass_user_capability_check = false) {

			// User capability check
			if(!$bypass_user_capability_check && !WS_Form_Common::can_user('read_submission')) { return false; }

			// Check form ID
			self::db_check_form_id();

			global $wpdb;

			// Get total number for form submissions that are unread
			$sql = sprintf("SELECT COUNT(id) AS count_submit_unread FROM %s WHERE form_id = %u AND viewed = 0 AND status IN ('publish', 'draft');", $this->table_name, $this->form_id);
			$count_submit_unread = $wpdb->get_var($sql);
			if(!is_null($count_submit_unread)) { return intval($count_submit_unread); } else { return 0; }
		}

		// Restore
		public function db_restore($count_update = true) {

			// User capability check
			if(!WS_Form_Common::can_user('delete_submission')) { return false; }

			self::db_set_status('draft', $count_update);
		}

		// Set starred on / off
		public function db_set_starred($starred = true) {

			// User capability check
			if(!WS_Form_Common::can_user('edit_submission')) { parent::db_access_denied(); }

			self::db_check_id();

			global $wpdb;

			// Build SQL
			$sql = sprintf("UPDATE %s SET starred = %u WHERE id = %u LIMIT 1;", $this->table_name, ($starred ? 1 : 0), $this->id);

			// Update submit record
			if($wpdb->query($sql) === false) { parent::db_wpdb_handle_error(__('Error setting starred status', 'ws-form')); }
		}

		// Set a submit record as viewed
		public function db_set_viewed($viewed = true, $update_count_submit_unread = true) {

			// User capability check
			if(!WS_Form_Common::can_user('read_submission')) { return false; }

			// Check ID
			self::db_check_id();

			global $wpdb;

			// Set viewed true
			$sql = sprintf("UPDATE %s SET viewed = %u WHERE id = %u LIMIT 1", $this->table_name, ($viewed ? 1 : 0), $this->id);
			if($wpdb->query($sql) === false) { parent::db_wpdb_handle_error(__('Error updating viewed status', 'ws-form')); }

			// Update form submit unread count statistic
			if($update_count_submit_unread) {

				$ws_form_form = new WS_Form_Form();
				$ws_form_form->id = $this->form_id;
				$ws_form_form->db_update_count_submit_unread();
			}
		}

		// Set status of submit
		public function db_set_status($status, $count_update = true) {

			// No capabilities required, this is a public method

			self::db_check_id();

			// Mark As Spam
			switch($status) {

				case 'spam' :

					$sql = sprintf("UPDATE %s SET status = '%s', spam_level = 100 WHERE id = %u LIMIT 1;", $this->table_name, esc_sql($status), $this->id);
					break;

				case 'not_spam' :

					$status = 'publish';
					$sql = sprintf("UPDATE %s SET status = '%s', spam_level = 0 WHERE id = %u LIMIT 1;", $this->table_name, esc_sql($status), $this->id);
					break;

				default :

					$sql = sprintf("UPDATE %s SET status = '%s' WHERE id = %u LIMIT 1;", $this->table_name, esc_sql($status), $this->id);
			}

			// Ensure provided submit status is valid
			self::db_check_status($status);

			global $wpdb;

			// Update submit record
			if($wpdb->query($sql) === false) { parent::db_wpdb_handle_error(__('Error setting submit status', 'ws-form')); }

			// Update form submit unread count statistic
			if($count_update) {

				self::db_check_form_id();

				$ws_form_form = new WS_Form_Form();
				$ws_form_form->id = $this->form_id;
				$ws_form_form->db_count_update();
			}

			return true;
		}

		// Check submit status
		public function db_check_status($status) {

			// Check status is valid
			$valid_statuses = explode(',', WS_FORM_STATUS_SUBMIT);
			if(!in_array($status, $valid_statuses)) { parent::db_throw_error(__('Invalid submit status: ' . $status, 'ws-form')); }

			return true;
		}

		// Get submit status name
		public static function db_get_status_name($status) {

			switch($status) {

				case 'draft' : 		return __('In Progress', 'ws-form'); break;
				case 'publish' : 	return __('Submitted', 'ws-form'); break;
				case 'error' : 		return __('Error', 'ws-form'); break;
				case 'spam' : 		return __('Spam', 'ws-form'); break;
				case 'trash' : 		return __('Trash', 'ws-form'); break;
				default :			return $status;
			}
		}

		// Get submit columns
		public function db_get_submit_fields() {

			// User capability check
			if(!WS_Form_Common::can_user('read_submission')) { return false; }

			self::db_check_form_id();

			$visible_count = 0;
			$visible_count_max = 5;

			$submit_fields = array();

			// Get form object
			$this->preview = true;
			self::db_form_object_read();

			// Get fields in single dimension array
			$fields = WS_Form_Common::get_fields_from_form($this->form_object);

			// Excluded field types
			$field_types_excluded = array('textarea');

			foreach($fields as $field) {

				if($this->field_types === false) { $this->field_types = WS_Form_Config::get_field_types_flat(); }
				if(!isset($this->field_types[$field->type])) { continue; }

				// Get field type
				$field_type_config = $this->field_types[$field->type];

				// Skip unlicensed fields
				if(
					isset($field_type_config['pro_required']) &&
					$field_type_config['pro_required']

				) { continue; }

				// Skip fields that are not saved to meta data
				if(!$field_type_config['submit_save']) { continue; }

				// Skip fields containing the word 'gdpr'
				if(strpos(strtolower($field->label), 'gdpr') !== false) { continue; }

				// Determine if field is required
				$required = WS_Form_Common::get_object_meta_value($field, 'required', false);

				// Determine excluded fields
				$excluded = in_array($field->type, $field_types_excluded);

				// Push to submit_fields array
				$submit_fields[$field->id] = array(

					'label' 	=> $field->label,
					'required' 	=> $required,
					'excluded'	=> $excluded,
					'hidden'	=> true,
				);
			}

			// Go through each submit field and if it is required, mark it as not hidden
			foreach($submit_fields as $id => $field) {

				if($visible_count < $visible_count_max) {

					if($field['required'] && !$field['excluded']) {

						$submit_fields[$id]['hidden'] = false;
						$visible_count++;
					}

					if($visible_count == $visible_count_max) { break; }
				}
			}

			if($visible_count < $visible_count_max) {

				// Go through each submit field and if it is not required, mark it as not hidden
				foreach($submit_fields as $id => $field) {

					if($visible_count < $visible_count_max) {

						if(!$field['required'] && !$field['excluded']) {

							$submit_fields[$id]['hidden'] = false;
							$visible_count++;
						}

						if($visible_count == $visible_count_max) { break; }
					}
				}
			}

			return $submit_fields;
		}

		// Create CSV file by page
		public function db_export_csv_page(&$file, $submit_ids = array(), $status = '', $date_from = '', $date_to = '', $page = 0) {

			// User capability check
			if(!WS_Form_Common::can_user('export_submission')) { return false; }

			// Clear hidden fields?
			$clear_hidden_fields = (get_user_meta(get_current_user_id(), 'ws_form_submissions_clear_hidden_fields', true) === 'on');

			// Get field data
			$submit_fields = $this->db_get_submit_fields();


			// Build CSV column headings
			$csv_header = array();

			// Fixed fields
			$csv_header_fields = array('id' => 'Submission ID', 'status_full' => 'Status', 'date_added' => 'Date Added', 'date_updated' => 'Date Updated', 'user_id' => 'User ID', 'user_first_name' => 'User First Name', 'user_last_name' => 'User Last Name', 'duration' => 'Duration (Seconds)');
			foreach(array_slice($csv_header_fields, 1) as $key => $value) { $csv_header[] = $value; } // Ignore first index

			// Form fields
			foreach($submit_fields as $submit_field) { $csv_header[] = $submit_field['label']; }

			if($page === 0) {

				// Output header row
				fwrite($file, '"ID",');	// To overcome issue with Excel thinking 'ID,' is an SYLK file
				fputcsv($file, $csv_header);
			}

			// Get id's to download
			if(!is_array($submit_ids)) { $submit_ids = (empty($submit_ids) ? array() : array($submit_ids)); }

			if(count($submit_ids) > 0) {

				// Check integrity of array
				foreach($submit_ids as $key => $submit_id) {

					if(!is_numeric($submit_id)) { unset($submit_ids[$key]); }
				}

				// Throw error if no valid submit ID's
				if(count($submit_ids) == 0) { self::db_throw_error(__('Invalid submit ID')); }

				// Build WHERE sql
				$where = 'id IN (' . implode(',', $submit_ids) . ') AND ';

			} else {

				$where = '';
			}

			$where .= sprintf("(NOT status='trash') AND form_id = %u", $this->form_id);

			// Build WHERE - status
			if($status == '') { $status == 'all'; }
			if(!WS_Form_Common::check_submit_status($status, false)) { $status = 'all'; }
			if($status != 'all') {
	
				// Filter by status
				$where .= ' AND status = "' . $status . '"';

			} else {

				// Show everything but trash (All)
				$where .= " AND NOT(status = 'trash' OR status = 'spam')";
			}

			// Date from
			if($date_from != '') {

				$date_from = WS_Form_Common::get_mysql_date(get_gmt_from_date($date_from . ' 00:00:00'));
				if($date_from !== false) { $where .= " AND date_added >= '$date_from'"; }
			}

			// Date to
			if($date_to != '') {

				$date_to = WS_Form_Common::get_mysql_date(get_gmt_from_date($date_to . ' 23:59:59'));
				if($date_to !== false) { $where .= " AND date_added <= '$date_to'"; }
			}

			global $wpdb;

			if($page === 0) {

				// Get form data
				$sql = sprintf("SELECT COUNT(id) FROM %s WHERE %s", $this->table_name, $where);
				$records_total = intval($wpdb->get_var($sql));

			} else {

				$records_total = false;
			}

			// Calculate limit and offset
			$limit = apply_filters('wsf_submit_export_page_size', WS_FORM_SUBMIT_EXPORT_PAGE_SIZE);
			$offset = ($page * $limit);

			// Get submit records
			$sql = sprintf("SELECT %s FROM %s WHERE %s ORDER BY date_added LIMIT %u OFFSET %u", self::DB_SELECT, $this->table_name, $where, $limit, $offset);
			$submits = $wpdb->get_results($sql);

			// Get total number of records to process
			$records_processed = is_null($submits) ? 0 : count($submits);
			if($records_processed === 0) { return array('records_processed' => 0, 'records_total' => $records_total); }

			// Process meta data
			foreach($submits as $key => $submit_object) {

				// Read expanded
				self::db_read_expanded($submit_object);

				// Get meta data
				$submit_object->meta = self::db_get_submit_meta($submit_object);

				// Clear hidden fields
				if($clear_hidden_fields) {

					$submit_object = self::clear_hidden_meta_values($submit_object);
				}

				$row_array = array();

				// Fixed fields
				foreach($csv_header_fields as $key => $value) {

					switch($key) {

						case 'date_added' :

							$row_array[] = date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime(get_date_from_gmt($submit_object->date_added)));
							break;

						case 'date_updated' :

							$row_array[] = date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime(get_date_from_gmt($submit_object->date_updated)));
							break;

						case 'user_first_name' :

							$row_array[] = isset($submit_object->user) ? $submit_object->user->first_name : '';
							break;

						case 'user_last_name' :

							$row_array[] = isset($submit_object->user) ? $submit_object->user->last_name : '';
							break;

						case 'id' :
						case 'status_full' :
						case 'user_id' :
						case 'duration' :

							$row_array[] = isset($submit_object->{$key}) ? $submit_object->{$key} : '';
							break;

						default :

							$row_array[] = isset($submit_object->meta[$key]) ? $submit_object->meta[$key] : '';
					}
				}

				// Form fields
				foreach($submit_fields as $id => $field) {

					$field_name = WS_FORM_FIELD_PREFIX . $id;

					// Get type
					$type = isset($submit_object->meta[$field_name]) ? (isset($submit_object->meta[$field_name]['type']) ? $submit_object->meta[$field_name]['type'] : '') : '';

					// Get value
					$value = isset($submit_object->meta[$field_name]) ? (isset($submit_object->meta[$field_name]['value']) ? $submit_object->meta[$field_name]['value'] : '') : '';

					// Apply filter
					$value = apply_filters('wsf_submit_field_type_csv', $value, $id, $type);

					// Process by type
					switch($type) {

						case 'signature' :
						case 'file' :

							if(!is_array($value)) { break; }

							$value_array = array();

							foreach($value as $file_object_index => $file_object) {

		 						// Get file handler
								$file_handler = isset($file_object['handler']) ? $file_object['handler'] : '';
		 						if($file_handler == '') { $file_handler = 'wsform'; }
		 						if(!isset(WS_Form_File_Handler::$file_handlers[$file_handler])) { continue; }
		 						$file_handler = WS_Form_File_Handler::$file_handlers[$file_handler];

								// Get value array
		 						$value_array[] = $file_handler->get_url($file_object, $id, $file_object_index, $submit_object->hash);
							}

							$value = implode(',', $value_array);

							break;

						case 'datetime' :

							if(
								is_array($value) &&
								isset($value['mysql'])
							) {
								$value = $value['mysql'];
							}
							break;

						case 'googlemap' :

							if(
								is_array($value) &&
								isset($value['lat']) &&
								isset($value['lng'])
							) {
								$value = sprintf('%.7f,%.7f', $value['lat'], $value['lng']);
							}
							break;
					}

					// Process array values (e.g. Select, Checkbox, Radio field types)
					if(is_array($value)) { $value = implode(',', $value); }

					// Add column
					$row_array[] = $value;
				}

				// Output data
				fputcsv($file, $row_array);
			}

			return array('records_processed' => ($page * $limit) + $records_processed, 'records_total' => $records_total);
		}

		// Setup from post
		public function setup_from_post() {

			// No capabilities required, this is a public method

			// Get form_id
			$this->form_id = intval(WS_Form_Common::get_query_var_nonce('wsf_form_id', 0));
			self::db_check_form_id();

			// Get hash
			$this->hash = WS_Form_Common::get_query_var_nonce('wsf_hash', '');

			// If hash found, look for form submission
			if($this->hash != '') {

				// Check hash
				if(!WS_Form_Common::check_submit_hash($this->hash)) {

					parent::db_throw_error(__('Invalid hash ID (setup_from_post)', 'ws-form'));
					die();
				}

				// Read submit by hash
				$this->db_read_by_hash(true, true, true, true);

				// Clear meta data
				$submit_clear_meta_filter_keys = apply_filters('wsf_submit_clear_meta_filter_keys', array());
				foreach($this->meta as $key => $value) {

					if(!in_array($key, $submit_clear_meta_filter_keys)) {

						unset($this->meta[$key]);
					}
				}
				$this->meta_protected = array();
			}

			if($this->hash == '') {

				// Create fresh hash for this submission
				$this->db_create_hash();
			}

			// Preview submit?
			$this->preview = (WS_Form_Common::get_query_var_nonce('wsf_preview', false) !== false);

			// Read form
			self::db_form_object_read();

			// Do not validate fields that are required bypassed
			$bypass_required = WS_Form_Common::get_query_var_nonce('wsf_bypass_required', '');
			$this->bypass_required_array = explode(',', $bypass_required);

			// Process hidden fields
			$hidden = WS_Form_Common::get_query_var_nonce('wsf_hidden', '');
			$this->hidden_array = explode(',', $hidden);
			if(count($this->hidden_array) > 0) {

				$this->meta['wsf_meta_key_hidden'] = array();
			}

			// Spam protection - Honeypot
			$honeypot_hash = ($this->form_object->published_checksum != '') ? $this->form_object->published_checksum : 'honeypot_unpublished_' . $this->form_id;
			$honeypot_value = WS_Form_Common::get_query_var_nonce("field_$honeypot_hash");
			if($honeypot_value != '') { parent::db_throw_error(__('Spam protection error', 'ws-form')); }

			// Get sections array
			$sections = WS_Form_Common::get_sections_from_form($this->form_object);

			// Are we submitting the form or just saving it?
			$this->post_mode = WS_Form_Common::get_query_var_nonce('wsf_post_mode', false);
			$form_submit = ($this->post_mode == 'submit');

			// Ensure post mode is valid
			if(!in_array($this->post_mode, array('submit', 'save', 'action'))) { parent::db_throw_error(__('Invalid post mode', 'ws-form')); }

			// Build section_repeatable
			$section_repeatable = array();
			$wsf_form_section_repeatable_index_json = WS_Form_Common::get_query_var_nonce('wsf_form_section_repeatable_index', false);
			if(!empty($wsf_form_section_repeatable_index_json)) {

				if(is_null($wsf_form_section_repeatable_index = (array) json_decode($wsf_form_section_repeatable_index_json))) {

					parent::db_throw_error(__('Malformed wsf_form_section_repeatable_index JSON value.', 'ws-form'));
				}

				// Save wsf_form_section_repeatable_index to section_repeatable and parse it to ensure the data is valid
				foreach($wsf_form_section_repeatable_index as $section_id_string => $indexes) {

					$section_repeatable[$section_id_string] = array('index' => array());

					foreach($indexes as $index) {

						if(intval($index) <= 0) { continue; }

						$section_repeatable[$section_id_string]['index'][] = intval($index);
					}
				}
			}

			// Process each section
			foreach($sections as $section_id => $section) {

				if($section->repeatable) {

					$section_id_string = 'section_' . $section_id;

					// Get repeatable indexes for that section
					if(
						!isset($section_repeatable[$section_id_string]) ||
						!isset($section_repeatable[$section_id_string]['index'])
					) {

						parent::db_throw_error(__('Repeatable data error. Section ID not found in wsf_form_section_repeatable_index.', 'ws-form'));
					}

					$section_repeatable_indexes = $section_repeatable[$section_id_string]['index'];

					foreach($section_repeatable_indexes as $section_repeatable_index) {

						self::setup_from_post_section($section, $form_submit, $section_id, $section_repeatable_index, $section_repeatable);
					}

				} else {

					self::setup_from_post_section($section, $form_submit);
				}
			}

			if(!empty($section_repeatable)) {

				$this->section_repeatable = serialize($section_repeatable);
			}
		}

		function setup_from_post_section($section, $form_submit, $section_id = false, $section_repeatable_index = false, &$section_repeatable = array()) {

			// Delimiters
			if($section_repeatable_index !== false) {

				// Get delimiters
				$section_repeatable_delimiter_section = WS_Form_Common::get_object_meta_value($section, 'section_repeatable_delimiter_section', WS_FORM_SECTION_REPEATABLE_DELIMITER_SECTION);
				if($section_repeatable_delimiter_section == '') { $section_repeatable_delimiter_section = WS_FORM_SECTION_REPEATABLE_DELIMITER_SECTION; }
				$section_repeatable_delimiter_row = WS_Form_Common::get_object_meta_value($section, 'section_repeatable_delimiter_row', WS_FORM_SECTION_REPEATABLE_DELIMITER_ROW);
				if($section_repeatable_delimiter_row == '') { $section_repeatable_delimiter_row = WS_FORM_SECTION_REPEATABLE_DELIMITER_ROW; }

				// Add delimiters to section_repeatable
				if(!isset($section_repeatable['section_' . $section_id])) { $section_repeatable['section_' . $section_id] = array(); }
				$section_repeatable['section_' . $section_id]['delimiter_section'] = $section_repeatable_delimiter_section;
				$section_repeatable['section_' . $section_id]['delimiter_row'] = $section_repeatable_delimiter_row;
			}

			// Process each field
			$section_fields = $section->fields;
			foreach($section_fields as $field) {

				// Validation
				$this->error_validation_action_field = array();

				// If field type not specified, skip
				if(!isset($field->type)) { continue; };
				$field_type = $field->type;

				// If field type not known, skip
				if($this->field_types === false) { $this->field_types = WS_Form_Config::get_field_types_flat(); }
				if(!isset($this->field_types[$field_type])) { continue; };
				$field_type_config = $this->field_types[$field_type];

				// Submit array
				$submit_array = isset($field_type_config['submit_array']) ? $field_type_config['submit_array'] : false;

				// If field is not licensed, skip
				if(
					isset($field_type_config['pro_required']) &&
					$field_type_config['pro_required']

				) { continue; }

				// Submit array
				$field_submit_array = (isset($field_type_config['submit_array'])) ? $field_type_config['submit_array'] : false; 

				// Is field in a repeatable section?
				$field_section_repeatable = isset($field->section_repeatable) && $field->section_repeatable;

				// Save meta data
				if(!isset($field->id)) { continue; }
				$field_id = intval($field->id);

				// Build field name
				$field_name = $field_name_post = $meta_key_hidden = WS_FORM_FIELD_PREFIX . $field_id;

				// Field value
				$field_value = WS_Form_Common::get_query_var_nonce($field_name);

				if($section_repeatable_index !== false) {

					$field_value = isset($field_value[$section_repeatable_index]) ? $field_value[$section_repeatable_index] : '';
					$field_name_post = sprintf('%s[%u]', $field_name, $section_repeatable_index);
					$meta_key_hidden = sprintf('%s_%u', $field_name, $section_repeatable_index);
				}

				// Field bypassed
				$field_bypassed = in_array($field_name_post, $this->bypass_required_array);

				// Field required
				$field_required = WS_Form_Common::get_object_meta_value($field, 'required', false) && !$field_bypassed;
				// Handle required fields
				if($form_submit && $field_required && ($field_value == '')) {

					self::db_throw_error_field_invalid_feedback($field_id, $section_repeatable_index, sprintf(__('Required field missing: %s', 'ws-form'), $field->label));
				}

				// Handle hidden fields
				if(in_array($field_name_post, $this->hidden_array)) {

					$this->meta['wsf_meta_key_hidden'][] = $meta_key_hidden;
				}

				// Deduplication
				if($field_value != '') {

					$field_dedupe = WS_Form_Common::get_object_meta_value($field, 'dedupe', false);
					if($field_dedupe) {

						// Get dedupe period
						$field_dedupe_period = WS_Form_Common::get_object_meta_value($field, 'dedupe_period', false);

						// Check for a dupe
						$ws_form_submit_meta = new WS_Form_Submit_Meta();
						if($ws_form_submit_meta->db_dupe_check($this->form_id, $field_id, $field_value, $field_dedupe_period)) {

							$field_dedupe_message = WS_Form_Common::get_object_meta_value($field, 'dedupe_message', '');
							if($field_dedupe_message == '') {

								$field_dedupe_message = __('The value entered for #label_lowercase has already been used.', 'ws-form');
							}

							$field_dedupe_message_lookups = array(

								'label_lowercase' 	=> strtolower($field->label),
								'label' 			=> $field->label
							);

							$field_dedupe_message = WS_Form_Common::mask_parse($field_dedupe_message, $field_dedupe_message_lookups);

							self::db_throw_error_field_invalid_feedback($field_id, $section_repeatable_index, $field_dedupe_message);
						}
					}
				}

				// Allow / Deny
				$field_allow_deny = WS_Form_Common::get_object_meta_value($field, 'allow_deny', '');
				if(
					($field_allow_deny !== '') &&
					in_array($field_allow_deny, array('allow', 'deny'))
				) {

					$field_value_allowed = ($field_allow_deny === 'deny');

					$field_allow_deny_values = WS_Form_Common::get_object_meta_value($field, 'allow_deny_values', array());

					if(

						is_array($field_allow_deny_values) &&
						(count($field_allow_deny_values) > 0)
					) {

						foreach($field_allow_deny_values as $field_allow_deny_row) {

							$field_allow_deny_value = $field_allow_deny_row->allow_deny_value;

							$field_allow_deny_pattern = str_replace('*', '.*', $field_allow_deny_value);

							$field_allow_deny_result = preg_match(sprintf('/%s/', $field_allow_deny_pattern), $field_value);

							if($field_allow_deny_result) {

								$field_value_allowed = ($field_allow_deny === 'allow');
								break;
							}
						}

						if(!$field_value_allowed) {

							$field_allow_deny_message = WS_Form_Common::get_object_meta_value($field, 'allow_deny_message', '');
							if($field_allow_deny_message == '') {

								$field_allow_deny_message = __('The email address entered is not allowed.', 'ws-form');
							}

							self::db_throw_error_field_invalid_feedback($field_id, $section_repeatable_index, $field_allow_deny_message);
						}
					}
				}

				// If field type should not be saved, skip
				$submit_save = isset($field_type_config['submit_save']) ? $field_type_config['submit_save'] : false;

				// Build meta_data
				$meta_data = array('id' => $field_id, 'value' => $field_value, 'type' => $field_type, 'section_id' => $section_id, 'repeatable_index' => $section_repeatable_index);
				$meta_key_suffix = (($section_repeatable_index !== false) ? ('_' . $section_repeatable_index) : '');
				if($submit_save !== false) {

					$meta_field = 'meta';

				} else {

					$meta_field = 'meta_protected';
				}

				// Add to submit meta protected
				$this->{$meta_field}[WS_FORM_FIELD_PREFIX . $field_id . $meta_key_suffix] = $meta_data;

				// Build fallback value
				if($section_repeatable_index !== false) {

					$meta_not_set = !isset($this->{$meta_field}[WS_FORM_FIELD_PREFIX . $field_id]);

					if($meta_not_set) {

						$this->{$meta_field}[WS_FORM_FIELD_PREFIX . $field_id] = $meta_data;

						// We don't store the fallback data to the database, it is just made available to any actions that need it
						$this->{$meta_field}[WS_FORM_FIELD_PREFIX . $field_id]['db_ignore'] = true;

						// Set repeatable index to false
						$this->{$meta_field}[WS_FORM_FIELD_PREFIX . $field_id]['repeatable_index'] = false;
					}

					switch($field_type) {

						// Merge
						case 'file' :
						case 'signature' :
						case 'googlemap' :

							if($meta_not_set) {

								$this->{$meta_field}[WS_FORM_FIELD_PREFIX . $field_id]['value'] = $field_value;

							} else {

								if(is_array($field_value)) {

									$meta_value = $this->{$meta_field}[WS_FORM_FIELD_PREFIX . $field_id]['value'];

									if(!is_array($meta_value)) {

										// Currently a blank string
										$this->{$meta_field}[WS_FORM_FIELD_PREFIX . $field_id]['value'] = $field_value;

									} else {

										// Currently an array
										$this->{$meta_field}[WS_FORM_FIELD_PREFIX . $field_id]['value'] = array_merge($field_value, $meta_value);
									}
								}
							}

							break;

						// Other fields
						default :

							if($meta_not_set) {

								$this->{$meta_field}[WS_FORM_FIELD_PREFIX . $field_id]['value'] = self::field_value_stringify($field, $this->{$meta_field}[WS_FORM_FIELD_PREFIX . $field_id]['value'], $field_submit_array, $section_repeatable_delimiter_row);

							} else {

								$this->{$meta_field}[WS_FORM_FIELD_PREFIX . $field_id]['value'] .= $section_repeatable_delimiter_section . self::field_value_stringify($field, $field_value, $field_submit_array, $section_repeatable_delimiter_row);
							}
					}

					// Store raw array values
					if($submit_array) {

						if(!is_array($field_value)) { $field_value = array($field_value); }

						if($meta_not_set) {

							$this->{$meta_field}[WS_FORM_FIELD_PREFIX . $field_id]['value_array'] = $field_value;

						} else {

							$this->{$meta_field}[WS_FORM_FIELD_PREFIX . $field_id]['value_array'] = array_merge($this->{$meta_field}[WS_FORM_FIELD_PREFIX . $field_id]['value_array'], $field_value);
						}
					}
				}

				// Merge errors
				if(count($this->error_validation_action_field) > 0) {

					$this->error_validation_actions = array_merge($this->error_validation_actions, $this->error_validation_action_field);
				}
			}

		}

		// Get field invalid feedback
		public function field_invalid_feedback($field, $field_type_config) {

			$invalid_feedback = WS_Form_Common::get_object_meta_value($field, 'invalid_feedback', '');
			$invalid_feedback_mask_placeholder = (isset($field_type_config['invalid_feedback'])) ? $field_type_config['invalid_feedback'] : WS_FORM_FIELD_INVALID_FEEDBACK;

			if(($invalid_feedback == '') && ($invalid_feedback_mask_placeholder != '')) {

 				$invalid_feedback_label = $field->label;

				// Parse invalid_feedback_mask_placeholder
				$invalid_feedback = str_replace('#label_lowercase', strtolower($invalid_feedback_label), $invalid_feedback_mask_placeholder);
				$invalid_feedback = str_replace('#label', $invalid_feedback_label, $invalid_feedback);
			}

			return $invalid_feedback;
		}

		// Meta value stringify
		public function field_value_stringify($field_object, $field_value, $field_submit_array, $section_repeatable_delimiter_row) {

			$field_type = $field_object->type;

			if($field_submit_array) {

				if(!is_array($field_value)) { $field_value = array($field_value); }

				switch($field_type) {

					case 'file' :
					case 'signature' :

						$field_value = $field_value['name'];
						break;

					case 'googlemap' :

						if(
							is_array($field_value) &&
							isset($field_value['lat']) &&
							isset($field_value['lng'])
						) {

							$field_value = sprintf('%.7f,%.7f', $field_value['lat'], $field_value['lng']);

						} else {

							$field_value = '';
						}
						break;

					default :

						$field_value = implode($section_repeatable_delimiter_row, $field_value);
				}

			} else {

				switch($field_type) {

					case 'datetime' :

						$field_value = WS_Form_Common::get_date_by_type($field_value, $field_object);;
						break;
				}
			}

			return $field_value;
		}

		// Read form object
		public function db_form_object_read() {

			// Check form ID
			self::db_check_form_id();

			// Read form data
			$ws_form_form = New WS_Form_Form();
			$ws_form_form->id = $this->form_id;

			if($this->preview) {

				// Draft
				$form_object = $ws_form_form->db_read(true, true);

				// Form cannot be read
				if($form_object === false) { parent::db_throw_error(__('Unable to read form data (Still logged in?)', 'ws-form')); }

			} else {

				// Published
				$form_object = $ws_form_form->db_read_published();

				// Form not yet published
				if($form_object === false) { parent::db_throw_error(__('No published form data', 'ws-form')); }
			}

			// Filter
			$form_object = apply_filters('wsf_pre_render_' . $this->form_id, $form_object, $this->preview);
			$form_object = apply_filters('wsf_pre_render', $form_object, $this->preview);

			// Convert to object
			$this->form_object = $form_object;
		}


		// Clear hidden meta values
		public function clear_hidden_meta_values($submit_object = false, $bypass_user_capability_check = true) {

			if($submit_object === false) {

				$submit_object = $this;
			}

			if(!isset($submit_object->meta)) { return $submit_object; }
			if(!isset($submit_object->meta['wsf_meta_key_hidden'])) { return $submit_object; }

			// Get section repeatable data (Unserialize if necesary)
			$section_repeatable_serialized = false;
			if(isset($submit_object->section_repeatable)) {

				$section_repeatable_serialized = is_serialized($submit_object->section_repeatable);

				$section_repeatable_array = $section_repeatable_serialized ? unserialize($submit_object->section_repeatable) : $submit_object->section_repeatable;

				if(!is_array($section_repeatable_array)) { $section_repeatable_array = array(); }

			} else {

				$section_repeatable_array = array();
			}
			$section_repeatable_original_array = $section_repeatable_array;
			$section_repeatable_edited = false;

			// Get hidden field names
			$meta_key_hidden_array = $submit_object->meta['wsf_meta_key_hidden'];

			// Clear each hidden array
			$field_ids_hidden = array();
			$field_ids_need_new_fallback = array();

			foreach($meta_key_hidden_array as $meta_key_hidden) {

				if(
					!isset($submit_object->meta[$meta_key_hidden]) ||
					!isset($submit_object->meta[$meta_key_hidden]['id'])
				) {
					continue;
				}

				// Get field ID
				$field_id = intval($submit_object->meta[$meta_key_hidden]['id']);

				// Get section ID (Only set on repeatable sections)
				$section_id = isset($submit_object->meta[$meta_key_hidden]['section_id']) ? intval($submit_object->meta[$meta_key_hidden]['section_id']) : 0;
				if($section_id > 0) {

					if(!isset($field_ids_hidden[$section_id])) { $field_ids_hidden[$section_id] = array(); }

					// Add to fields touched
					if(!isset($field_ids_hidden[$section_id][$field_id])) { $field_ids_hidden[$section_id][$field_id] = 0; }
					$field_ids_hidden[$section_id][$field_id]++;
				}

				// Unset field
				unset($submit_object->meta[$meta_key_hidden]);

				// Unset fallback field
				unset($submit_object->meta[WS_FORM_FIELD_PREFIX . $field_id]);

				$field_ids_need_new_fallback[] = $field_id;
			}

			$field_ids_need_new_fallback = array_unique($field_ids_need_new_fallback);

			if(count($field_ids_need_new_fallback) > 0) {

				// Run through each section and clean section repeatable array
				foreach($field_ids_hidden as $section_id => $fields) {

					// Get section name
					$section_name = sprintf('section_%u', $section_id);

					// Check this exists in the index
					if(!isset($section_repeatable_array[$section_name])) { continue; }

					// Run through each index
					foreach($section_repeatable_array[$section_name]['index'] as $section_repeatable_index => $section_repeatable_id) {

						// Find out how many fields remain for this section
						$section_row_fields_found = false;
						foreach($submit_object->meta as $meta) {

							if(
								!isset($meta['section_id']) ||
								!isset($meta['repeatable_index'])
							) {
								continue;
							}

							// Get section ID (Only set on repeatable sections)
							$meta_section_id = intval($meta['section_id']);
							$meta_repeatable_index = intval($meta['repeatable_index']);
							if(
								($meta_section_id === $section_id) &&
								($meta_repeatable_index === $section_repeatable_id)
							) {

								$section_row_fields_found = true;
								break;
							}
						}

						// If no fields found in this row, then remove it from the section repeatable array
						if(!$section_row_fields_found) {

							// Remove this row from the index
							foreach($section_repeatable_array[$section_name]['index'] as $section_repeatable_index_delete => $section_repeatable_id_delete) {

								if($section_repeatable_id_delete === $section_repeatable_id) {

									unset($section_repeatable_array[$section_name]['index'][$section_repeatable_index_delete]);
									$section_repeatable_array[$section_name]['index'] = array_values($section_repeatable_array[$section_name]['index']);
									$section_repeatable_edited = true;
								}
							}
						}
					}
				}

				// Rebuild meta data
				$meta_array = array();
				foreach($submit_object->meta as $meta_key => $meta) {

					if(
						!isset($meta['value']) ||
						!isset($meta['section_id']) ||
						!isset($meta['id']) ||
						!isset($meta['repeatable_index'])
					) {

						continue;
					}

					// Strip db_ignore
					if(isset($meta['db_ignore']) && $meta['db_ignore']) { continue; }

					// Build meta data
					$meta_array[] = array(

						'meta_key' => $meta_key,
						'meta_value' => $meta['value'],
						'section_id' => $meta['section_id'],
						'field_id' => $meta['id'],
						'repeatable_index' => $meta['repeatable_index']
					);
				}

				// Get new fallback values
				$meta_new = self::db_get_submit_meta($this, $meta_array, $bypass_user_capability_check);

				// Run through field that needs a new fallback
				foreach($field_ids_need_new_fallback as $field_id) {

					// Field name
					$field_name = WS_FORM_FIELD_PREFIX . $field_id;
					if(isset($meta_new[$field_name])) {

						// We don't store the fallback data to the database, it is just made available to any actions that need it
						$meta_new[$field_name]['db_ignore'] = true;

						// Set repeatable index to false
						$meta_new[$field_name]['repeatable_index'] = false;

						// Replace						
						$this->meta[$field_name] = $submit_object->meta[$field_name] = $meta_new[$field_name];
					}
				}

				// Rebuild section_repeatable
				if($section_repeatable_edited) {

					$this->section_repeatable = $submit_object->section_repeatable = ($section_repeatable_serialized ? serialize($section_repeatable_array) : $section_repeatable_array);
				}
			}

			return $submit_object;
		}

		// Handle server side error - Invalid feedback
		public function db_throw_error_field_invalid_feedback($field_id, $section_repeatable_index, $message) {

			$this->error_validation_action_field[] = array(

				'action' 					=> 'field_invalid_feedback',
				'field_id' 					=> $field_id,
				'section_repeatable_index' 	=> $section_repeatable_index,
				'message' 					=> $message
			);
		}

		// Remove protected meta data
		public function db_remove_meta_protected() {

			$this->meta_protected = array();
		}

		// Compact
		public function db_compact() {

			// Remove form_object
			if(isset($this->form_object)) { unset($this->form_object); }
			if(isset($this->field_types)) { unset($this->field_types); }
		}

		// Create hash
		public function db_create_hash() {

			if($this->hash == '') { $this->hash = esc_sql(wp_hash($this->id . '_' . $this->form_id . '_' . time() . '_' . wp_rand())); }

			// Check hash
			if(!WS_Form_Common::check_submit_hash($this->hash)) {

				parent::db_throw_error(__('Invalid hash (db_create_hash)', 'ws-form'));
				die();
			}

			return $this->hash;
		}

		// Create token
		public function db_create_token() {

			if(!WS_Form_Common::check_submit_hash($this->hash)) {

				parent::db_throw_error(__('Invalid hash (db_create_token)', 'ws-form'));
				die();
			}

			if($this->token == '') { $this->token = esc_sql(wp_hash($this->id . '_' . $this->form_id . '_' . $this->token . '_' . time() . '_' . wp_rand())); }

			// Check hash
			if(!WS_Form_Common::check_submit_hash($this->token)) {

				parent::db_throw_error(__('Invalid token (db_create_token)', 'ws-form'));
				die();
			}

			return $this->token;
		}

		// Check form id
		public function db_check_form_id() {

			if(intval($this->form_id) <= 0) { parent::db_throw_error(__('Invalid form ID', 'ws-form')); }
			return true;
		}

		// Check id
		public function db_check_id() {

			if(intval($this->id) <= 0) { parent::db_throw_error(__('Invalid submit ID', 'ws-form')); }
			return true;
		}
	}