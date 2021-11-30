<?php

	class WS_Form_API_Template extends WS_Form_API {

		public function __construct() {

			// Call parent on WS_Form_API
			parent::__construct();
		}

		// API - GET actions that can be used for templates
		public function api_get_actions($parameters) {

			$ws_form_template = new WS_Form_Template();

			try {

				$actions = $ws_form_template->db_get_actions();

			} catch(Exception $e) {

				// Throw JSON error
				parent::api_throw_error($e->getMessage());
			}

			// Send JSON response
			parent::api_json_response($actions);
		}

		// API - GET action templates
		public function api_get_action_templates($parameters) {

			$ws_form_template = new WS_Form_Template();
			$ws_form_template->action_id = self::api_get_action_id($parameters);

			try {

				$templates = $ws_form_template->db_get_action_templates();

			} catch(Exception $e) {

				// Throw JSON error
				parent::api_throw_error($e->getMessage());
			}

			// Send JSON response
			parent::api_json_response($templates);
		}

		// Get action ID
		public function api_get_action_id($parameters) {

			return WS_Form_Common::get_query_var_nonce('action_id', 0, $parameters);
		}

		// API - POST - Template - Upload - JSON
		public function api_post_upload_json($parameters) {

			// Get template type
			$type = self::api_get_type($parameters);

			// Get form object from post $_FILE
			$form_object = WS_Form_Common::get_form_object_from_post_file();

			$ws_form_template = new WS_Form_Template();
			$ws_form_template->type = $type;

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

			// Get template type
			$type = self::api_get_type($parameters);

			// Get template category ID
			$id = self::api_get_id($parameters);

			// Check template is valid
			$ws_form_template = new WS_Form_Template();
			$ws_form_template->type = $type;
			$ws_form_template->id = $id;

			try {

				$ws_form_template->read(false);

			} catch(Exception $e) {

				// Throw error
				parent::api_throw_error($e->getMessage());
			}

			// Build filename
			$filename = sprintf('wsf-%s-%s', $type, strtolower($ws_form_template->form_object->label) . '.json');

			// HTTP headers
			WS_Form_Common::file_download_headers($filename, 'application/json');

			// Output form as JSON
			echo $ws_form_template->form_json;
			exit;
		}

		// API - DELETE - Template
		public function api_delete($parameters) {

			// Get template type
			$type = self::api_get_type($parameters);

			// Get template category ID
			$id = self::api_get_id($parameters);

			// Check template is valid
			$ws_form_template = new WS_Form_Template();
			$ws_form_template->type = $type;
			$ws_form_template->id = $id;

			try {

				$ws_form_template->read(true);

			} catch(Exception $e) {

				// Throw error
				parent::api_throw_error($e->getMessage());
			}

			// Check config data
			if(
				!isset($ws_form_template->file_config) ||
				!isset($ws_form_template->category_index) ||
				!isset($ws_form_template->index)
			) {

				parent::api_throw_error(__('Template config data not found', 'ws-form'));
			}

			// Read config data
			$file_config = $ws_form_template->file_config;
			$category_index = $ws_form_template->category_index;
			$index = $ws_form_template->index;

			// Load config file
			if(!file_exists($file_config)) {

				parent::api_throw_error(sprintf(__('Unable to open config.json file: %s', 'ws-form'), $file_config));
			}
			$config_file_json = file_get_contents($file_config);

			// JSON decode config file
			$config_file_object = json_decode($config_file_json);
			if(is_null($config_file_object)) {

				parent::api_throw_error(sprintf(__('Unable to decode config.json file: %s', 'ws-form'), $file_config));
			}

			// Delete template
			unset($config_file_object->template_categories[$category_index]->templates[$index]);
			$config_file_object->template_categories[$category_index]->templates = array_values($config_file_object->template_categories[$category_index]->templates);

			// Write config file
			if(file_put_contents($file_config, wp_json_encode($config_file_object)) === false) {

				parent::api_throw_error(sprintf(__('Unable to write config.json file: %s', 'ws-form'), $file_config));
			}

			// Delete template
			if(
				isset($ws_form_template->file_json)
			) {

				$file_json = $ws_form_template->file_json;

				if(
					!file_exists($file_json) ||
					!unlink($file_json)
				) {

					// Throw error
					parent::api_throw_error(sprintf(__('Template file not found: %s', 'ws-form'), $file_json));
				}
			}

			// Send JSON response
			parent::api_json_response($ws_form_template->get_settings());
		}

		// Get template type
		public function api_get_type($parameters) {

			$type = WS_Form_Common::get_query_var_nonce('type', 'section', $parameters);

			if(!in_array($type, array('form', 'section'))) {

				parent::api_throw_error(__('Invalid template type'));
			}

			return $type;
		}

		// Get template template ID
		public function api_get_id($parameters) {

			return WS_Form_Common::get_query_var_nonce('template_id', 'user', $parameters);
		}
	}
