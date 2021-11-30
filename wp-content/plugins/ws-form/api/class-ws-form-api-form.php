<?php

	class WS_Form_API_Form extends WS_Form_API {

		public function __construct() {

			// Call parent on WS_Form_Form
			parent::__construct();
		}

		// API - GET - ALL
		public function api_get_full($parameters) {

			$ws_form_form = new WS_Form_Form();
			$ws_form_form->id = self::api_get_id($parameters);

			// Check if this is coming from the admin
			$is_admin = (WS_Form_Common::get_query_var_nonce('wsf_fia', 'false', $parameters) == 'true');

			try {

				// Get label
				$label = $ws_form_form->db_get_label();

			} catch (Exception $e) {

				parent::api_throw_error($e->getMessage());
			}

			if($is_admin == 'true') {

				// Describe transaction for history
				$history = array(

					'object'		=>	'form',
					'method'		=>	'get',
					'label'			=>	$label,
					'id'			=>	$ws_form_form->id
				);

			} else {

				$history = false;
			}

			// Send JSON response (By passing form ID, it will get returned in default JSON response)
			parent::api_json_response([], $ws_form_form->id, $history, true, false, !$is_admin, true);
		}

		// API - GET - Published
		public function api_get_published($parameters) {

			// Send JSON response (By passing form ID, it will get returned in default JSON response)
			parent::api_json_response([], self::api_get_id($parameters), false, true, true);
		}

		// API - GET - Checksum
		public function api_get_checksum($parameters) {

			$ws_form_form = new WS_Form_Form();
			$ws_form_form->id = self::api_get_id($parameters);

			try {

				$checksum = $ws_form_form->db_get_checksum();

				// If checksum not yet calculated, calculate it
				if((is_null($checksum)) || ($checksum == '')) {

					$checksum = $ws_form_form->db_checksum();
				}

			} catch (Exception $e) {

				parent::api_throw_error($e->getMessage());
			}

			// Send JSON response
			parent::api_json_response([], $ws_form_form->id, false, false);
		}

		// API - POST
		public function api_post($parameters) {

			// User capability check
			if(!WS_Form_Common::can_user('create_form')) { parent::api_access_denied(); }

			$api_json_response = [];

			$ws_form_form = new WS_Form_Form();

			try {

				// Create form
				$ws_form_form->db_create();

				// Build api_json_response
				$api_json_response = $ws_form_form->db_read();

				// Add default form groups, sections, fields
				$api_json_response->groups = [];

				// Update checksum
				$ws_form_form->db_checksum();

			} catch (Exception $e) {

				parent::api_throw_error($e->getMessage());
			}

			// Send JSON response
			parent::api_json_response($api_json_response, $ws_form_form->id, false);
		}

		// API - POST - Upload - JSON
		public function api_post_upload_json($parameters) {

			$form_id = self::api_get_id($parameters);

			$ws_form_form = new WS_Form_Form();

			if($form_id == 0) {

				try {

					$ws_form_form->db_create();				

				} catch (Exception $e) {

					parent::api_throw_error($e->getMessage());
				}

			} else {

				$ws_form_form->id = $form_id;
			}

			try {

				// Get form object from file
				$form_object = WS_Form_Common::get_form_object_from_post_file();

				// Reset form
				$ws_form_form->db_import_reset();

				// Build form
				$ws_form_form->db_update_from_object($form_object, true, true);

				// Fix data - Conditional ID's
				$ws_form_form->db_conditional_repair();

				// Fix data - Action ID's
				$ws_form_form->db_action_repair();

				// Fix data - Meta ID's
				$ws_form_form->db_meta_repair();

				// Update checksum
				$ws_form_form->db_checksum();

				// Describe transaction for history
				$history = ($form_id > 0) ? array(

					'object'		=>	'form',
					'method'		=>	WS_Form_Common::get_query_var_nonce('history_method', 'post_upload_json'),
					'label'			=>	$ws_form_form->db_get_label(),
					'id'			=>	$ws_form_form->id
				) : false;

			} catch (Exception $e) {

				parent::api_throw_error($e->getMessage());
			}

			// Send JSON response (By passing form ID, it will get returned in default JSON response)
			parent::api_json_response([], $form_id, $history, true);
		}

		// API - POST - Download - JSON
		public function api_post_download_json($parameters) {

			$ws_form_form = new WS_Form_Form();
			$ws_form_form->id = self::api_get_id($parameters);

			try {

				$ws_form_form->db_download_json();

			} catch (Exception $e) {

				parent::api_throw_error($e->getMessage());
			}
		}

		// API - PUT
		public function api_put($parameters) {

			$ws_form_form = new WS_Form_Form();
			$ws_form_form->id = self::api_get_id($parameters);

			// Get form data
			$form_object = WS_Form_Common::get_query_var_nonce('form', false, $parameters);
			if(!$form_object) { return false; }

			try {

				// Put form as array
				$ws_form_form->db_update_from_object($form_object, false);

				// Describe transaction for history
				$history = array(

					'object'		=>	'form',
					'method'		=>	WS_Form_Common::get_query_var_nonce('history_method', 'put'),
					'label'			=>	$ws_form_form->db_get_label(),
					'id'			=>	$ws_form_form->id
				);

				// Update checksum
				$ws_form_form->db_checksum();

			} catch (Exception $e) {

				parent::api_throw_error($e->getMessage());
			}

			// Send JSON response
			parent::api_json_response([], $ws_form_form->id, isset($form_object->history_suppress) ? false : $history);
		}

		// API - PUT - ALL
		public function api_put_full($parameters) {

			$ws_form_form = new WS_Form_Form();
			$ws_form_form->id = self::api_get_id($parameters);

			// Get form data
			$form_object = WS_Form_Common::get_query_var_nonce('form', false, $parameters);
			if(!$form_object) { return false; }

			try {

				// Put form as array
				$ws_form_form->db_update_from_object($form_object);

				// Update checksum
				$ws_form_form->db_checksum();

				// Describe transaction for history
				$history = array(

					'object'		=>	'form',
					'method'		=>	WS_Form_Common::get_query_var_nonce('history_method', 'put_full'),
					'label'			=>	$ws_form_form->db_get_label(),
					'id'			=>	$ws_form_form->id
				);

			} catch (Exception $e) {

				parent::api_throw_error($e->getMessage());
			}

			// Send JSON response
			parent::api_json_response([], $ws_form_form->id, isset($form_object->history_suppress) ? false : $history);
		}

		// API - PUT - Publish
		public function api_put_publish($parameters) {

			$ws_form_form = new WS_Form_Form();
			$ws_form_form->id = self::api_get_id($parameters);

			try {

				// Publish
				$ws_form_form->db_publish();

			} catch (Exception $e) {

				parent::api_throw_error($e->getMessage());
			}

			// Send JSON response
			parent::api_json_response([], $ws_form_form->id, false, false);
		}

		// API - PUT - Draft
		public function api_put_draft($parameters) {

			$ws_form_form = new WS_Form_Form();
			$ws_form_form->id = self::api_get_id($parameters);

			try {

				// Draft
				$ws_form_form->db_draft();

			} catch (Exception $e) {

				parent::api_throw_error($e->getMessage());
			}

			// Send JSON response
			parent::api_json_response([], false, false, false);
		}

		// API - DELETE
		public function api_delete($parameters) {

			// User capability check
			if(!WS_Form_Common::can_user('delete_form')) { parent::api_access_denied(); }

			$ws_form_form = new WS_Form_Form();
			$ws_form_form->id = self::api_get_id($parameters);

			try {

				// Get label (We do this because once its deleted, we can't reference it)
				$label = $ws_form_form->db_get_label();

				// Delete form
				$ws_form_form->db_delete();

			} catch (Exception $e) {

				parent::api_throw_error($e->getMessage());
			}

			// Describe transaction for history
			$history = array(

				'object'		=>	'form',
				'method'		=>	'delete',
				'label'			=>	$label,
				'id'			=>	$ws_form_form->id
			);

			// Update checksum
			$ws_form_form->db_checksum();

			// Send JSON response
			parent::api_json_response([], $ws_form_form->id, $history, false);
		}

		// API - GET - Locations
		public function api_get_locations($parameters) {

			// Get locations
			$ws_form_form = new WS_Form_Form();
			$ws_form_form->id = self::api_get_id($parameters);

			try {

				$return_array = $ws_form_form->db_get_locations();

			} catch (Exception $e) {

				parent::api_throw_error($e->getMessage());
			}

			return $return_array;
		}

		// API - GET - SVG - Draft
		public function api_get_svg_draft($parameters) {

			self::api_get_svg($parameters, false);
		}

		// API - GET - SVG - Published
		public function api_get_svg_published($parameters) {

			self::api_get_svg($parameters, true);
		}

		// API - GET - SVG - Draft
		public function api_get_svg($parameters, $published) {

			// Content type
			header('Content-type: text/html');

			// Get form ID
			$form_id = intval(self::api_get_id($parameters));
			if($form_id == 0) { exit; }

			// Return SVG
			$ws_form_form = new WS_Form_Form();
			$ws_form_form->id = $form_id;
			echo $ws_form_form->get_svg($published);	// phpcs:ignore
			exit;
		}

		// Get form ID
		public function api_get_id($parameters) {

			return intval(WS_Form_Common::get_query_var_nonce('form_id', 0, $parameters));
		}
	}