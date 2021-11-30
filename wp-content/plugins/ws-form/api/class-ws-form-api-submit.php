<?php

	class WS_Form_API_Submit extends WS_Form_API {

		public $ws_form_submit;

		private $duration_server_start;
		private $spam_level;

		public function __construct() {

			// Initialize
			$this->ws_form_submit = New WS_Form_Submit();
			$this->duration_server_start = microtime(true);
			$this->spam_level = null;

			// Call parent on WS_Form_API
			parent::__construct();
		}

		// API - GET
		public function api_get($parameters) {

			$this->ws_form_submit->id = self::api_get_id($parameters);
			$this->ws_form_submit->form_id = self::api_get_form_id($parameters);

			try {

				// Mark as viewed
				$this->ws_form_submit->db_set_viewed();

				// Get submit object
				$this->ws_form_submit->db_read(true, true);

				// Clear hidden fields
				$clear_hidden_fields = (get_user_meta(get_current_user_id(), 'ws_form_submissions_clear_hidden_fields', true) === 'on');
				if($clear_hidden_fields) {

					$this->ws_form_submit->clear_hidden_meta_values();
				}

				// Compact
				$this->ws_form_submit->db_compact();

				// Protect
				$this->ws_form_submit->db_remove_meta_protected();

				// Send JSON response
				self::api_json_response($this->ws_form_submit);

			} catch(Exception $e) {

				self::api_throw_error($e->getMessage());
			}
		}

		// API - GET - By hash
		public function api_get_by_hash($parameters) {

			// No capabilities required, this is a public method

			// Get form hash
			$this->ws_form_submit->hash = self::api_get_hash($parameters);

			// Get form token
			$this->ws_form_submit->token = self::api_get_token($parameters);
			if(empty($this->ws_form_submit->token)) { $this->ws_form_submit->token = false; }

			try {

				// Send JSON response
				self::api_json_response($this->ws_form_submit->db_read_by_hash(true, true, false, true));

			} catch(Exception $e) {

				self::api_throw_error($e->getMessage());
			}
		}

		// API - POST
		public function api_post($parameters) {

			// No capabilities required, this is a public method
			try {

				// Set up submit from post
				$this->ws_form_submit->setup_from_post();

				// Process WS Form form validation errors prior to actions running
				$action_complete_array = self::api_validation_error_process();
				if(count($action_complete_array) > 0) { self::api_post_complete($action_complete_array); }

				// Get form object (This was set up as a result of setup_from_post running)
				$form_object = $this->ws_form_submit->form_object;

				// Set up action
				add_action('wsf_actions_post_complete', array($this, 'api_post_complete'), 10, 2);

				// Get action_id
				$action_id = intval(WS_Form_Common::get_query_var_nonce('wsf_action_id'));

				// Process all actions
				do_action('wsf_actions_post', $form_object, $this->ws_form_submit, 'wsf_actions_post_complete', $action_id);

			} catch(Exception $e) {

				self::api_throw_error_submit($e->getMessage());
			}
		}

		// API - POST - Complete
		public function api_post_complete($action_complete_array) {

			// No capabilities required, this is a public method

			// Process action form validation errors
			$action_complete_array = self::api_validation_error_process($action_complete_array);

			// Get processing time in milliseconds
			$submit_duration_server = round((microtime(true) - $this->duration_server_start) * 1000);

			// Create response
			$json_response = ['count' => $this->ws_form_submit->count_submit, 'submit_duration_server' => $submit_duration_server, 'submit_duration_user' => $this->ws_form_submit->duration, 'post_mode' => $this->ws_form_submit->post_mode];

			// Add js to response
			if(isset($action_complete_array['js']) && is_array($action_complete_array['js']) && count($action_complete_array['js']) > 0) { $json_response['js'] = $action_complete_array['js']; }

			// Check if debug is enabled
			$debug = WS_Form_Common::debug_enabled();
			if($debug) {

				// Add logs to response
				if(isset($action_complete_array['logs']) && is_array($action_complete_array['logs']) && count($action_complete_array['logs']) > 0) { $json_response['logs'] = $action_complete_array['logs']; }
			}

			// Add errors to response
			if(isset($action_complete_array['errors']) && is_array($action_complete_array['errors']) && count($action_complete_array['errors']) > 0) { $json_response['errors'] = $action_complete_array['errors']; }

			// Log save or submit
			$ws_form_form_stat = new WS_Form_Form_Stat();
			$ws_form_form_stat->form_id = $this->ws_form_submit->form_id;

			switch($this->ws_form_submit->post_mode) {

				case 'save' :

					try {

						$ws_form_form_stat->db_add_save();

					} catch (Exception $e) {

						parent::api_throw_error($e->getMessage());
					}

					break;

				case 'submit' :

					try {

						$ws_form_form_stat->db_add_submit();

					} catch (Exception $e) {

						parent::api_throw_error($e->getMessage());
					}

					break;
			}

			// Do action
			do_action('wsf_submit_post_complete', $this->ws_form_submit);

			// Send response
			self::api_json_response_submit($json_response);
		}

		// API - REPOST (This is called to repost an action)
		public function api_repost($parameters) {

			try {

				$this->ws_form_submit->id = self::api_get_id($parameters);
				$action_index = self::api_get_action_index($parameters);

				// Read submit
				$this->ws_form_submit->db_read(true, false);

				// Read form_object
				$this->ws_form_submit->db_form_object_read();

				// Get submit actions
				$actions = is_serialized($this->ws_form_submit->actions) ? unserialize($this->ws_form_submit->actions) : false;
				if($actions === false) { self::api_throw_error(__('No actions found', 'ws-form')); }

				// Get action
				if(!isset($actions[$action_index])) { self::api_throw_error(__('Action index not found', 'ws-form')); }
				$action = $actions[$action_index];

				// Set up action for 
				add_action('wsf_action_repost_complete', array($this, 'api_repost_complete'), 10, 1);

				do_action('wsf_action_repost', $this->ws_form_submit->form_object, $this->ws_form_submit, $action, 'wsf_action_repost_complete');

			} catch(Exception $e) {

				self::api_throw_error($e->getMessage());
			}
		}

		// API - POST - Complete
		public function api_repost_complete($return_array) {

			// Send response
			parent::api_json_response($return_array, false, false);
		}

		// API - PUT
		public function api_put($parameters) {

			$ws_form_submit = new WS_Form_Submit();
			$ws_form_submit->form_id = self::api_get_form_id($parameters);
			$ws_form_submit->id = self::api_get_id($parameters);

			// Get field data
			$submit_object = WS_Form_Common::get_query_var_nonce('submit', false, $parameters);
			if(!$submit_object) { return false; }

			// Serialize actions (We need to do this because the actions are sent to us as an array)
			if(isset($submit_object->actions) && is_array($submit_object->actions)) {

				// Convert objects to arrays to match format used throughout WS Form
				$submit_object->actions = json_decode(json_encode($submit_object->actions), true);

				// Serialize
				$submit_object->actions = serialize($submit_object->actions);
			}

			// Serialize section_repeatable (We need to do this because section_repeatable is sent to us as an object)
			if(isset($submit_object->section_repeatable) && is_object($submit_object->section_repeatable)) {

				// Convert to array
				$section_repeatable = json_decode(json_encode($submit_object->section_repeatable), true);
				$submit_object->section_repeatable = serialize($section_repeatable);
				$section_ids = array_keys($section_repeatable);

				// Remove repeatable fallbacks
				if(isset($submit_object->meta)) {

					foreach((array) $submit_object->meta as $key => $meta) {

						$section_id = isset($meta->section_id) ? $meta->section_id : false;
						$repeatable_index = isset($meta->repeatable_index) ? $meta->repeatable_index : false;

						if(
							in_array('section_' . $section_id, $section_ids) &&
							($repeatable_index === false)
						) {

							unset($submit_object->meta->{$key});
						}
					}
				}
			}

			// Meta data to array
			$submit_object->meta = json_decode(json_encode($submit_object->meta), true);

			try {

				// Put field
				$ws_form_submit->db_update_from_object($submit_object);

			} catch (Exception $e) {

				parent::api_throw_error($e->getMessage());
			}

			// Send JSON response
			parent::api_json_response([], false, false);
		}

		// Handle JSON response
		public function api_json_response_submit($data = false) {

			$json_array = [];

			// Normal response
			if(!$this->ws_form_submit->error) {

				// Add hash
				if($this->ws_form_submit->post_mode != 'action') {

					$json_array['hash'] = $this->ws_form_submit->hash;
				}

			} else {

				if(isset($this->ws_form_submit->error_message)) {

					$json_array['error_message'] = $this->ws_form_submit->error_message;
				}
			}

			// Set nonce
			$json_array['x_wp_nonce'] = wp_create_nonce('wp_rest');
			$json_array['wsf_nonce'] = wp_create_nonce(WS_FORM_POST_NONCE_ACTION_NAME);

			// Set error
			$json_array['error'] = $this->ws_form_submit->error;
			$json_array['error_validation'] = (count($this->ws_form_submit->error_validation_actions) > 0);

			// New data
			if($data !== false) { $json_array['data'] = $data; }

			// Return data filter
			$json_array = apply_filters('wsf_api_submit_response_data', $json_array, $this->ws_form_submit);

			// JSON encode
			$json_return = wp_json_encode($json_array);

			// Check for JSON encoding error
			if(json_last_error() !== 0) {

				// Set response code
				header('HTTP/1.1 400 Bad Request', true, 400);

				// Build error JSON
				$json_array = array(

					'error' => 			true,
					'error_message' =>	'JSON encoding error: ' . json_last_error_msg() . ' (' . json_last_error() . ')'
				);

				echo wp_json_encode($json_array);
				exit;
			}

			// API error
			if($this->ws_form_submit->error) {

				// Set response code
				switch($this->ws_form_submit->error_code) {

					case '403' :

						header('HTTP/1.1 403 Forbidden', true, 403);
						break;

					case '404' :

						header('HTTP/1.1 404 Not Found', true, 403);
						break;

					default :

						header('HTTP/1.1 400 Bad Request', true, 400);
				}

				// Set error message
				$json_array['error_message'] = $this->ws_form_submit->error_message;

				echo wp_json_encode($json_array);
				exit;
			}

			// Set HTTP content type head
			header('Content-Type: application/json');

			// Output JSON response
			echo $json_return; // phpcs:ignore

			// Stop execution
			exit;
		}

		// API - PUT - Starred - On
		public function api_put_starred_on($parameters) {

			$this->ws_form_submit->id = self::api_get_id($parameters);

			try {

				// Publish
				$this->ws_form_submit->db_set_starred(true);

			} catch (Exception $e) {

				parent::api_throw_error($e->getMessage());
			}

			// Send JSON response
			parent::api_json_response([], false, false, false);
		}

		// API - PUT - Starred - Off
		public function api_put_starred_off($parameters) {

			$this->ws_form_submit->id = self::api_get_id($parameters);

			try {

				// Publish
				$this->ws_form_submit->db_set_starred(false);

			} catch (Exception $e) {

				parent::api_throw_error($e->getMessage());
			}

			// Send JSON response
			parent::api_json_response([], false, false, false);
		}

		// API - PUT - Viewed - On
		public function api_put_viewed_on($parameters) {

			$this->ws_form_submit->id = self::api_get_id($parameters);
			$this->ws_form_submit->form_id = self::api_get_form_id($parameters);

			try {

				// Publish
				$this->ws_form_submit->db_set_viewed(true);

			} catch (Exception $e) {

				parent::api_throw_error($e->getMessage());
			}

			// Send JSON response
			parent::api_json_response([], false, false, false);
		}

		// API - PUT - Viewed - Off
		public function api_put_viewed_off($parameters) {

			$this->ws_form_submit->id = self::api_get_id($parameters);
			$this->ws_form_submit->form_id = self::api_get_form_id($parameters);

			try {

				// Publish
				$this->ws_form_submit->db_set_viewed(false);

			} catch (Exception $e) {

				parent::api_throw_error($e->getMessage());
			}

			// Send JSON response
			parent::api_json_response([], false, false, false);
		}

		// API - Export
		public function api_export() {

			// Filters
			$form_id = intval(WS_Form_Common::get_query_var_nonce('id'));
			$status = WS_Form_Common::get_query_var_nonce('status');
			$date_from = WS_Form_Common::get_query_var_nonce('date_from');
			$date_to = WS_Form_Common::get_query_var_nonce('date_to');
			$hash = WS_Form_Common::get_query_var_nonce('hash');
			$page = intval(WS_Form_Common::get_query_var_nonce('page'));
			$submit_ids = array();

			// Check hash
			if(empty($hash)) {

				$hash = wp_hash($form_id . '_' . time() . '_' . wp_rand());
			}
			if(!WS_Form_Common::check_submit_hash($hash)) { exit; }

			// Get submit export directory
			$submit_export_dir = WS_Form_Common::upload_dir_create(WS_FORM_SUBMIT_EXPORT_TMP_DIR);
			if($submit_export_dir['error']) {

				parent::db_throw_error($submit_export_dir['error']);
			}
			$submit_export_dir = $submit_export_dir['dir'];

			// Get CSV file name
			$csv_file_name = sprintf('%s/%s.csv', $submit_export_dir, $hash);

			// First page
			if($page === 0) {

				$csv_file_pointer = fopen($csv_file_name, 'w');

				if($csv_file_pointer === false) {

					return self::api_export_error(sprintf(__('Unable to create temporary file %s', 'ws-form'), $csv_file_name));
				}

			} else {

				if(!file_exists($csv_file_name)) {

					return self::api_export_error(sprintf(__('Unable to open temporary file %s', 'ws-form'), $csv_file_name));
				}

				$csv_file_pointer = fopen($csv_file_name, 'a');
			}

			// Build page
			$this->ws_form_submit->form_id = $form_id;
			$db_export_csv_page_return = $this->ws_form_submit->db_export_csv_page($csv_file_pointer, $submit_ids, $status, $date_from, $date_to, $page);

			$records_processed = $db_export_csv_page_return['records_processed'];
			$records_total = $db_export_csv_page_return['records_total'];

			if(
				($records_processed === 0) ||
				($records_processed < apply_filters('wsf_submit_export_page_size', WS_FORM_SUBMIT_EXPORT_PAGE_SIZE))
			) {

				// Set progress to 100%
				$progress = 100;

				// No more records to process
				$complete = true;

			} else {

				// More records to process
				$complete = false;
			}

			return array('error' => false, 'complete' => $complete, 'hash' => $hash, 'records_processed' => $records_processed, 'records_total' => $records_total);
		}

		// API export - Error
		public function api_export_error($error_message) {

			return array('error' => true, 'error_message' => $error_message);
		}

		// API export - Get
		public function api_export_get($parameters) {

			// Get hash
			$hash = WS_Form_Common::get_query_var('wsf_hash', false, $parameters);

			// Check hash
			if(!WS_Form_Common::check_submit_hash($hash)) { exit; }

			// Get submit export directory
			$submit_export_dir = WS_Form_Common::upload_dir_create(WS_FORM_SUBMIT_EXPORT_TMP_DIR);
			if($submit_export_dir['error']) {

				parent::db_throw_error($submit_export_dir['error']);
			}
			$submit_export_dir = $submit_export_dir['dir'];

			// Get CSV file name
			$csv_file_name = sprintf('%s/%s.csv', $submit_export_dir, $hash);

			// Check file name
			if(!file_exists($csv_file_name)) { exit; }

			// Check file size
			$csv_file_size = filesize($csv_file_name);

			// If file size large, zip the file
			if($csv_file_size > apply_filters('wsf_submit_export_file_size_zip', WS_FORM_SUBMIT_EXPORT_FILE_SIZE_ZIP)) {

				// Create zip archive
				$zip = new ZipArchive();

				// Build file names
				$zip_file_name = sprintf('%s%s', get_temp_dir(), WS_Form_Common::filename_datestamp('ws-form-submit', 'zip'));
				$csv_file_name_base = WS_Form_Common::filename_datestamp('ws-form-submit', 'csv');

				// Open zip file
				if($zip->open($zip_file_name, ZipArchive::CREATE) !== true) { exit; }

				// Add CSV file to zip
				$zip->addFile($csv_file_name, $csv_file_name_base);

				// Close zip file
				$zip->close();

				// Delete old CSV file
				unlink($csv_file_name);

				// Set CSV file name to the ZIP file
				$csv_file_name = $zip_file_name;

				// Build file name
				$http_file_name = WS_Form_Common::filename_datestamp('ws-form-submit', 'zip');

				// HTTP headers
				WS_Form_Common::file_download_headers($http_file_name, 'application/zip');

			} else {

				// Build file name
				$http_file_name = WS_Form_Common::filename_datestamp('ws-form-submit', 'csv');

				// HTTP headers
				WS_Form_Common::file_download_headers($http_file_name, 'text/csv');
			}

			// Read and output the CSV file
			readfile($csv_file_name);

			// Delete temporary file
			unlink($csv_file_name);

			exit;
		}

		// API - Throw error
		public function api_throw_error_submit($message, $error_code = 400) {

			$this->ws_form_submit->error = true;
			$this->ws_form_submit->error_message = $message;
			$this->ws_form_submit->error_code = $error_code;

			self::api_json_response_submit();
		}

		// API - Validation error
		public function api_validation_error_process($action_complete_array = array()) {

			if(count($this->ws_form_submit->error_validation_actions) == 0) { return $action_complete_array; }

			$action_complete_array_new = array();
			$action_complete_array_new['js'] = array();
			$action_complete_array_new['errors'] = array();

			// Error clear
			$error_clear = (WS_Form_Common::get_object_meta_value($this->ws_form_submit->form_object, 'error_clear', '') == 'on');

			// Add errors to response
			foreach($this->ws_form_submit->error_validation_actions as $action) {

				if(!isset($action['action'])) { continue; }

				switch($action['action']) {

					case 'message' :

						// If no message set, set default error
						if(!isset($action['message'])) { $action['message'] = __('An unknown error occurred', 'ws-form'); }

						// Set default error 
						if(!isset($action['type'])) { $action['type'] = WS_Form_Common::get_object_meta_value($this->ws_form_submit->form_object, 'error_type', 'danger'); }
						if(!isset($action['method'])) { $action['method'] = WS_Form_Common::get_object_meta_value($this->ws_form_submit->form_object, 'error_method', 'after'); }
						if(!isset($action['duration'])) { intval(WS_Form_Common::get_object_meta_value($this->ws_form_submit->form_object, 'error_duration', '')); }
						if(!isset($action['form_hide'])) { $action['form_hide'] = (WS_Form_Common::get_object_meta_value($this->ws_form_submit->form_object, 'error_form_hide', '') == 'on'); }
						if(!isset($action['clear'])) { $action['clear'] = $error_clear; }
						if(!isset($action['scroll_top'])) { $action['scroll_top'] = (WS_Form_Common::get_object_meta_value($this->ws_form_submit->form_object, 'error_scroll_top', '') == 'on'); }
						if(!isset($action['scroll_top_offset'])) { $action['scroll_top_offset'] = intval(WS_Form_Common::get_object_meta_value($this->ws_form_submit->form_object, 'error_scroll_top_offset', '0')); }
						if(!isset($action['scroll_top_duration'])) { $action['scroll_top_duration'] = intval(WS_Form_Common::get_object_meta_value($this->ws_form_submit->form_object, 'error_scroll_top_duration', '400')); }
						if(!isset($action['form_show'])) { $action['form_show'] = (WS_Form_Common::get_object_meta_value($this->ws_form_submit->form_object, 'error_form_show', '') == 'on'); }
						if(!isset($action['message_hide'])) { $action['message_hide'] = (WS_Form_Common::get_object_meta_value($this->ws_form_submit->form_object, 'error_message_hide', 'on') == 'on'); }

						// Ensure all messages show
						$error_clear = false;

						break;
				}

				$action_complete_array_new['js'][] = $action;
			}

			// No actions should be run, so just return the submit JSON response
			return (count($action_complete_array_new['js']) > 0) ? $action_complete_array_new : $action_complete_array;
		}

		// Get form ID
		public function api_get_form_id($parameters) {

			// Public
			$form_id = WS_Form_Common::get_query_var_nonce('wsf_form_id', false, $parameters);

			// Admin
			if($form_id === false) {

				$form_id = WS_Form_Common::get_query_var_nonce('id', false, $parameters);
			}

			return intval($form_id);
		}

		// Get hash
		public function api_get_hash($parameters) {

			return WS_Form_Common::get_query_var_nonce('wsf_hash', '', $parameters, true);
		}

		// Get token
		public function api_get_token($parameters) {

			return WS_Form_Common::get_query_var_nonce('wsf_token', '', $parameters, true);
		}

		// Get action index
		public function api_get_action_index($parameters) {

			return WS_Form_Common::get_query_var_nonce('action_index', 0, $parameters);
		}

		// Get submit ID
		public function api_get_id($parameters) {

			return intval(WS_Form_Common::get_query_var_nonce('submit_id', 0, $parameters));
		}
	}