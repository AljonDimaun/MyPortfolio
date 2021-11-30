<?php

	class WS_Form_API_Section extends WS_Form_API {
	
		public function __construct() {

			// Call parent on WS_Form_API
			parent::__construct();
		}

		// API - POST
		public function api_post($parameters) {

			$ws_form_section = new WS_Form_Section();
			$ws_form_section->form_id = self::api_get_form_id($parameters);
			$ws_form_section->group_id = self::api_get_group_id($parameters);

			// Get next sibling ID
			$next_sibling_id = intval(WS_Form_Common::get_query_var_nonce('next_sibling_id', 0, $parameters));

			try {

				// Set breakpoint meta
				$ws_form_section->db_set_breakpoint_size_meta();

				// Create section
				$ws_form_section->db_create($next_sibling_id);

				// Build api_json_response
				$api_json_response = $ws_form_section->db_read();

				// Add empty fields element
				$api_json_response->fields = [];

				// Describe transaction for history
				$history = array(

					'object'		=>	'section',
					'method'		=>	'post',
					'label'			=>	WS_FORM_DEFAULT_SECTION_NAME,
					'group_id'		=>	$ws_form_section->group_id,
					'id'			=>	$ws_form_section->id
				);

				// Update checksum
				$ws_form_section->db_checksum();

			} catch (Exception $e) {

				parent::api_throw_error($e->getMessage());
			}

			parent::api_json_response($api_json_response, $ws_form_section->form_id, $history);
		}

		// API - POST - Template
		public function api_post_template($parameters) {

			$ws_form_section = new WS_Form_Section();
			$ws_form_section->form_id = self::api_get_form_id($parameters);
			$ws_form_section->group_id = self::api_get_group_id($parameters);

			// Get next sibling ID
			$next_sibling_id = intval(WS_Form_Common::get_query_var_nonce('next_sibling_id', 0, $parameters));

			// Get template ID
			$template_id = WS_Form_Common::get_query_var_nonce('template_id', '', $parameters);
			if($template_id == '') { parent::api_throw_error(__('Invalid template ID', 'ws-form')); }

			try {

				// Load template form data
				$ws_form_template = New WS_Form_Template();
				$ws_form_template->type = 'section';
				$ws_form_template->id = $template_id;
				$ws_form_template->read();

				// Create sections from template
				$label = $ws_form_section->db_create_from_form_object($ws_form_template->form_object, $next_sibling_id);

				// Describe transaction for history
				$history = array(

					'object'		=>	'template',
					'method'		=>	'post',
					'label'			=>	$label,
					'group_id'		=>	$ws_form_section->group_id,
					'id'			=>	$ws_form_section->id
				);

			} catch (Exception $e) {

				parent::api_throw_error($e->getMessage());
			}

			parent::api_json_response([], $ws_form_section->form_id, $history);
		}

		// API - POST - Template - Add
		public function api_post_template_add($parameters) {

			$ws_form_section = new WS_Form_Section();
			$ws_form_section->form_id = self::api_get_form_id($parameters);
			$ws_form_section->id = self::api_get_id($parameters);

			try {

				$form_object = $ws_form_section->db_get_form_object();

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

			$ws_form_section = new WS_Form_Section();
			$ws_form_section->form_id = self::api_get_form_id($parameters);
			$ws_form_section->id = self::api_get_id($parameters);

			try {

				$form_object = $ws_form_section->db_get_form_object();

			} catch (Exception $e) {

				parent::api_throw_error($e->getMessage());
			}

			// Build filename
			$filename = 'wsf-section-' . strtolower($form_object->label) . '.json';

			// HTTP headers
			WS_Form_Common::file_download_headers($filename, 'application/json');

			// Output JSON
			echo wp_json_encode($form_object);
			
			exit;
		}

		// API - POST - Upload - JSON
		public function api_post_upload_json($parameters) {

			$ws_form_section = new WS_Form_Section();
			$ws_form_section->form_id = self::api_get_form_id($parameters);
			$ws_form_section->group_id = self::api_get_group_id($parameters);
			$ws_form_section->id = self::api_get_id($parameters);

			// Get next sibling ID
			$next_sibling_id = intval(WS_Form_Common::get_query_var_nonce('next_sibling_id', 0, $parameters));

			try {

				// Get form object from file
				$form_object = WS_Form_Common::get_form_object_from_post_file();

				// Create sections from template
				$label = $ws_form_section->db_create_from_form_object($form_object, $next_sibling_id);

				// Describe transaction for history
				$history = array(

					'object'		=>	'template',
					'method'		=>	'post',
					'label'			=>	$label,
					'group_id'		=>	$ws_form_section->group_id,
					'id'			=>	$ws_form_section->id
				);

			} catch (Exception $e) {

				parent::api_throw_error($e->getMessage());
			}

			// Send JSON response (By passing form ID, it will get returned in default JSON response)
			parent::api_json_response([], $ws_form_section->form_id, $history, true);
		}

		// API - PUT
		public function api_put($parameters) {

			$ws_form_section = new WS_Form_Section();
			$ws_form_section->id = self::api_get_id($parameters);
			$ws_form_section->form_id = self::api_get_form_id($parameters);

			// Get section data
			$section_object = WS_Form_Common::get_query_var_nonce('section', false, $parameters);
			if(!$section_object) { return false; }

			try {

				// Put section
				$ws_form_section->db_update_from_object($section_object, false);

				// Describe transaction for history
				$history = array(

					'object'		=>	'section',
					'method'		=>	WS_Form_Common::get_query_var_nonce('history_method', 'put'),
					'label'			=>	$ws_form_section->db_get_label($ws_form_section->table_name, $ws_form_section->id),
					'group_id'		=>	$ws_form_section->group_id,
					'id'			=>	$ws_form_section->id
				);

				// Update checksum
				$ws_form_section->db_checksum();

			} catch (Exception $e) {

				parent::api_throw_error($e->getMessage());
			}

			// Send JSON response
			parent::api_json_response([], $ws_form_section->form_id, isset($section_object->history_suppress) ? false : $history);
		}

		// API - PUT - SORT INDEX
		public function api_put_sort_index($parameters) {

			$ws_form_section = new WS_Form_Section();
			$ws_form_section->id = self::api_get_id($parameters);
			$ws_form_section->form_id = self::api_get_form_id($parameters);
			$ws_form_section->group_id = self::api_get_group_id($parameters);

			// Get next sibling ID
			$next_sibling_id = intval(WS_Form_Common::get_query_var_nonce('next_sibling_id', 0, $parameters));

			try {

				// Process sort index
				$ws_form_section->db_object_sort_index($ws_form_section->table_name, 'group_id', $ws_form_section->group_id, $next_sibling_id, $ws_form_section->id);

				// Describe transaction for history
				$history = array(

					'object'		=>	'section',
					'method'		=>	'put_sort_index',
					'label'			=>	$ws_form_section->db_get_label($ws_form_section->table_name, $ws_form_section->id),
					'group_id'		=>	$ws_form_section->group_id,
					'id'			=>	$ws_form_section->id
				);

				// Update checksum
				$ws_form_section->db_checksum();

			} catch (Exception $e) {

				parent::api_throw_error($e->getMessage());
			}

			// Send JSON response
			parent::api_json_response([], $ws_form_section->form_id, $history);
		}

		// API - PUT - CLONE
		public function api_put_clone($parameters) {

			$ws_form_section = new WS_Form_Section();
			$ws_form_section->id = self::api_get_id($parameters);
			$ws_form_section->form_id = self::api_get_form_id($parameters);

			try {

				// Read
				$ws_form_section->db_read();

				// Get group ID
				$ws_form_section->group_id = $ws_form_section->db_get_group_id();

				// Get next sibling ID
				$next_sibling_id = intval(WS_Form_Common::get_query_var_nonce('next_sibling_id', 0, $parameters));

				// Get sort_index
				$ws_form_section->sort_index = $ws_form_section->db_object_sort_index_get($ws_form_section->table_name, 'group_id', $ws_form_section->group_id, $next_sibling_id);

				// Rename
				$ws_form_section->label = sprintf(__('%s (Copy)', 'ws-form'), $ws_form_section->label);

				// Clone
				$ws_form_section->id = $ws_form_section->db_clone();

				// Remember label before change
				$label = $ws_form_section->label;

				// Build api_json_response
				$api_json_response = $ws_form_section->db_read(true, true);

				// Describe transaction for history
				$history = array(

					'object'		=>	'section',
					'method'		=>	'put_clone',
					'label'			=>	$label,
					'group_id'		=>	$ws_form_section->group_id,
					'id'			=>	$ws_form_section->id
				);

				// Update checksum
				$ws_form_section->db_checksum();

			} catch (Exception $e) {

				parent::api_throw_error($e->getMessage());
			}

			parent::api_json_response($api_json_response, $ws_form_section->form_id, $history);
		}

		// API - DELETE
		public function api_delete($parameters) {

			$ws_form_section = new WS_Form_Section();
			$ws_form_section->id = self::api_get_id($parameters);
			$ws_form_section->form_id = self::api_get_form_id($parameters);

			try {

				// Get label (We do this because once its deleted, we can't reference it)
				$label = $ws_form_section->db_get_label($ws_form_section->table_name, $ws_form_section->id);

				// Delete section
				$ws_form_section->db_delete();

				// Describe transaction for history
				$history = array(

					'object'		=>	'section',
					'method'		=>	'delete',
					'label'			=>	$label,
					'group_id'		=>	$ws_form_section->group_id,
					'id'			=>	$ws_form_section->id
				);

				// Update checksum
				$ws_form_section->db_checksum();

			} catch (Exception $e) {

				parent::api_throw_error($e->getMessage());
			}

			// Send JSON response
			parent::api_json_response([], $ws_form_section->form_id, $history);
		}

		// Get form ID
		public function api_get_form_id($parameters) {

			return intval(WS_Form_Common::get_query_var_nonce('id', 0, $parameters));
		}

		// Get group ID
		public function api_get_group_id($parameters) {

			return intval(WS_Form_Common::get_query_var_nonce('group_id', 0, $parameters));
		}

		// Get section ID
		public function api_get_id($parameters) {

			return intval(WS_Form_Common::get_query_var_nonce('section_id', 0, $parameters));
		}
	}