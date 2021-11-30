<?php

	class WS_Form_API_Group extends WS_Form_API {

		public function __construct() {

			// Call parent on WS_Form_API
			parent::__construct();
		}

		// API - POST
		public function api_post($parameters) {

			$ws_form_group = new WS_Form_Group();
			$ws_form_group->form_id = self::api_get_form_id($parameters);

			$api_json_response = [];

			try {

				// Save tab index
				$ws_form_group->db_tab_index_save($parameters);

				// Get next sibling ID
				$next_sibling_id = intval(WS_Form_Common::get_query_var_nonce('next_sibling_id', 0, $parameters));

				// Create group
				$ws_form_group->db_create($next_sibling_id);

				// Build api_json_response
				$api_json_response = $ws_form_group->db_read(true, true);	// True on get groups because we create a default first group in db_create

				// Describe transaction for history
				$history = array(

					'object'		=>	'group',
					'method'		=>	'post',
					'label'			=>	WS_FORM_DEFAULT_GROUP_NAME,
					'form_id'		=>	$ws_form_group->form_id,
					'id'			=>	$ws_form_group->id
				);

				// Update checksum
				$ws_form_group->db_checksum();

			} catch (Exception $e) {

				parent::api_throw_error($e->getMessage());
			}

			// Send JSON response
			parent::api_json_response($api_json_response, $ws_form_group->form_id, $history);
		}

		// API - POST - Template - Add
		public function api_post_template_add($parameters) {

			$ws_form_group = new WS_Form_Group();
			$ws_form_group->form_id = self::api_get_form_id($parameters);
			$ws_form_group->id = self::api_get_id($parameters);

			try {

				$form_object = $ws_form_group->db_get_form_object();

			} catch (Exception $e) {

				parent::api_throw_error($e->getMessage());
			}

			$ws_form_template = new WS_Form_Template();
			$ws_form_template->type = 'section';

			try {

				$ws_form_template->create_from_form_object($form_object);

			} catch(Exception $e) {

				// Throw JSON error
				parent::api_throw_error($e->getMessage());
			}

			// Send JSON response
			parent::api_json_response($ws_form_template->get_settings());
		}

		// API - POST - Download - JSON
		public function api_post_download_json($parameters) {

			$ws_form_group = new WS_Form_Group();
			$ws_form_group->form_id = self::api_get_form_id($parameters);
			$ws_form_group->id = self::api_get_id($parameters);

			try {

				$form_object = $ws_form_group->db_get_form_object();

			} catch (Exception $e) {

				parent::api_throw_error($e->getMessage());
			}

			// Build filename
			$filename = 'wsf-group-' . strtolower($form_object->label) . '.json';

			// HTTP headers
			WS_Form_Common::file_download_headers($filename, 'application/json');

			// Output JSON
			echo wp_json_encode($form_object);
			
			exit;
		}

		// API - POST - Upload - JSON
		public function api_post_upload_json($parameters) {

			$ws_form_group = new WS_Form_Group();
			$ws_form_group->form_id = self::api_get_form_id($parameters);
			$ws_form_group->id = self::api_get_id($parameters);

			try {

				// Get form object from file
				$form_object = WS_Form_Common::get_form_object_from_post_file();

				// Update group
				$label = $ws_form_group->db_create_from_form_object($form_object);

				// Describe transaction for history
				$history = array(

					'object'		=>	'group',
					'method'		=>	'post',
					'label'			=>	$label,
					'id'			=>	$ws_form_group->id
				);

			} catch (Exception $e) {

				parent::api_throw_error($e->getMessage());
			}

			// Send JSON response (By passing form ID, it will get returned in default JSON response)
			parent::api_json_response([], $ws_form_group->form_id, $history, true);
		}

		// API - PUT
		public function api_put($parameters) {

			$ws_form_group = new WS_Form_Group();
			$ws_form_group->id = self::api_get_id($parameters);
			$ws_form_group->form_id = self::api_get_form_id($parameters);

			// Get group data
			$group_object = WS_Form_Common::get_query_var_nonce('group', false, $parameters);
			if(!$group_object) { return false; }

			try {

				// Put group
				$ws_form_group->db_update_from_object($group_object, false);

				// Describe transaction for history
				$history = array(

					'object'		=>	'group',
					'method'		=>	WS_Form_Common::get_query_var_nonce('history_method', 'put'),
					'label'			=>	$ws_form_group->db_get_label($ws_form_group->table_name, $ws_form_group->id),
					'form_id'		=>	$ws_form_group->form_id,
					'id'			=>	$ws_form_group->id
				);

				// Update checksum
				$ws_form_group->db_checksum();

			} catch (Exception $e) {

				parent::api_throw_error($e->getMessage());
			}

			// Send JSON response
			parent::api_json_response([], $ws_form_group->form_id, isset($group_object->history_suppress) ? false : $history);
		}

		// API - PUT - SORT INDEX
		public function api_put_sort_index($parameters) {

			$ws_form_group = new WS_Form_Group();
			$ws_form_group->id = self::api_get_id($parameters);
			$ws_form_group->form_id = self::api_get_form_id($parameters);

			try {

				// Store tab index
				$ws_form_group->db_tab_index_save($parameters);

				// Get next sibling ID
				$next_sibling_id = intval(WS_Form_Common::get_query_var_nonce('next_sibling_id', 0, $parameters));

				// Process sort index
				$ws_form_group->db_object_sort_index($ws_form_group->table_name, 'form_id', $ws_form_group->form_id, $next_sibling_id, $ws_form_group->id);

				// Describe transaction for history
				$history = array(

					'object'		=>	'group',
					'method'		=>	'put_sort_index',
					'label'			=>	$ws_form_group->db_get_label($ws_form_group->table_name, $ws_form_group->id),
					'form_id'		=>	$ws_form_group->form_id,
					'id'			=>	$ws_form_group->id
				);

				// Update checksum
				$ws_form_group->db_checksum();

			} catch (Exception $e) {

				parent::api_throw_error($e->getMessage());
			}

			// Send JSON response
			parent::api_json_response([], $ws_form_group->form_id, $history);
		}

		// API - PUT - CLONE
		public function api_put_clone($parameters) {

			$ws_form_group = new WS_Form_Group();
			$ws_form_group->id = self::api_get_id($parameters);
			$ws_form_group->form_id = self::api_get_form_id($parameters);

			try {

				// Save tab index
				$ws_form_group->db_tab_index_save($parameters);

				// Read
				$ws_form_group->db_read();

				// Get next sibling ID
				$next_sibling_id = intval(WS_Form_Common::get_query_var_nonce('next_sibling_id', 0, $parameters));

				// Get sort_index
				$ws_form_group->sort_index = $ws_form_group->db_object_sort_index_get($ws_form_group->table_name, 'form_id', $ws_form_group->form_id, $next_sibling_id);

				// Rename
				$ws_form_group->label = sprintf(__('%s (Copy)', 'ws-form'), $ws_form_group->label);

				// Clone
				$ws_form_group->id = $ws_form_group->db_clone();

				// Remember label before change
				$label = $ws_form_group->label;

				// Build api_json_response
				$api_json_response = $ws_form_group->db_read(true, true);

				// Describe transaction for history
				$history = array(

					'object'		=>	'group',
					'method'		=>	'put_clone',
					'label'			=>	$label,
					'form_id'		=>	$ws_form_group->form_id,
					'id'			=>	$ws_form_group->id
				);

				// Update checksum
				$ws_form_group->db_checksum();

			} catch (Exception $e) {

				parent::api_throw_error($e->getMessage());
			}

			parent::api_json_response($api_json_response, $ws_form_group->form_id, $history);
		}

		// API - DELETE
		public function api_delete($parameters) {

			$ws_form_group = new WS_Form_Group();
			$ws_form_group->id = self::api_get_id($parameters);
			$ws_form_group->form_id = self::api_get_form_id($parameters);

			try {

				// Save tab index
				$ws_form_group->db_tab_index_save($parameters);

				// Get label (We do this because once its deleted, we can't reference it)
				$label = $ws_form_group->db_get_label($ws_form_group->table_name, $ws_form_group->id);

				// Delete group
				$ws_form_group->db_delete();

				// Describe transaction for history
				$history = array(

					'object'		=>	'group',
					'method'		=>	'delete',
					'label'			=>	$label,
					'form_id'		=>	$ws_form_group->form_id,
					'id'			=>	$ws_form_group->id
				);

				// Update checksum
				$ws_form_group->db_checksum();

			} catch (Exception $e) {

				parent::api_throw_error($e->getMessage());
			}

			// Send JSON response
			parent::api_json_response([], $ws_form_group->form_id, $history);
		}

		// Get form ID
		public function api_get_form_id($parameters) {

			return intval(WS_Form_Common::get_query_var_nonce('id', 0, $parameters));
		}

		// Get group ID
		public function api_get_id($parameters) {

			return intval(WS_Form_Common::get_query_var_nonce('group_id', 0, $parameters));
		}

	}