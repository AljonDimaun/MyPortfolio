<?php

	abstract class WS_Form_File_Handler extends WS_Form_Core {

		// Variables global to this abstract class
		public static $file_handlers = array();
		private static $return_array = array();

		// Register data source
		public function register($object) {

			// Check if pro required for data source
			if(!WS_Form_Common::is_edition($this->pro_required ? 'pro' : 'basic')) { return false; }

			// Get data source ID
			$file_handler_id = $this->id;

			// Add action to actions array
			self::$file_handlers[$file_handler_id] = $object;

			// Add meta keys to file field type
			if(method_exists($object, 'get_file_handler_settings')) {

				$settings = $object->get_file_handler_settings();

				if(isset($settings['meta_keys'])) {

					add_filter('wsf_config_field_types', function($field_types) {

						$object = self::$file_handlers[$this->id];
						$settings = $object->get_file_handler_settings();
						$meta_keys = $settings['meta_keys'];

						// Locate file handler index
						$file_handler_index = false;
						foreach($field_types['advanced']['types']['file']['fieldsets']['basic']['fieldsets'] as $index => $fieldset) {

							if(in_array('file_handler', $fieldset['meta_keys'])) {

								$file_handler_index = $index;
								break;
							}
						}
						if($file_handler_index === false) { return $field_types; }

						foreach($meta_keys as $meta_key) {

							$field_types['advanced']['types']['file']['fieldsets']['basic']['fieldsets'][$file_handler_index]['meta_keys'][] = $meta_key;
						}

						return $field_types;
					});
				}
			}
		}

		// Touch with file handler ID
		public function touch(&$file_objects) {

			foreach($file_objects as $file_object_key => $file_object) {

				$file_objects[$file_object_key]['handler'] = $this->id;
			}
		}

		// Get file object from URL
		public static function get_file_object_from_url($url) {

			$attachment_id = attachment_url_to_postid($url);
			if($attachment_id === 0) { return false; }

			return self::get_file_object_from_attachment_id($attachment_id);
		}

		// Get file object from attachment ID
		public static function get_file_object_from_attachment_id($attachment_id) {

			if(!$attachment_id) { return false; }

			// Get file path full
			$file_path_full = get_attached_file($attachment_id);
			if($file_path_full === false) { return false; }

			// Get file name
			$file_name = basename($file_path_full);			

			// Get file path
			$file_path = str_replace(wp_upload_dir()['basedir'] . '/', '', $file_path_full);

			// Get file size
			$file_size = 0;
			if(file_exists($file_path_full)) {

				$file_size = filesize($file_path_full);

			} else {

				return false;
			}

			// Get mime type
			$file_type = get_post_mime_type($attachment_id);

			// Get UUID
			$file_uuid = md5(get_the_guid($attachment_id));

			// Get file URL
			$file_url = wp_get_attachment_image_src($attachment_id, WS_FORM_DROPZONEJS_IMAGE_SIZE, true);
			if($file_url) {

				$file_url = $file_url[0];

			} else {

				$file_url = wp_get_attachment_thumb_url($attachment_id);

				if(!$file_url) { $file_url = ''; }
			}

			// Build file object
			$file_object = array(

				'name' => $file_name,
				'path' => $file_path,
				'url' => $file_url,
				'size' => $file_size,
				'type' => $file_type,
				'uuid' => $file_uuid,		// Used by DropzoneJS to provide a unique ID
				'attachment_id' => $attachment_id
			);

			return $file_object;
		}

		public function api_call($endpoint, $path = '', $method = 'GET', $body = null, $headers = array(), $authentication = 'basic', $username = false, $password = false, $accept = 'application/json', $content_type = 'application/json') {
			
			// Build query string
			$query_string = (($body !== null) && ($method == 'GET')) ? '?' . http_build_query($body) : '';

			// Filters
			$timeout = apply_filters('wsf_api_call_timeout', WS_FORM_API_CALL_TIMEOUT);
			$sslverify = apply_filters('wsf_api_call_verify_ssl',WS_FORM_API_CALL_VERIFY_SSL);

			// Headers
			if($accept !== false) { $headers['Accept'] = $accept; }
			if($content_type !== false) { $headers['Content-Type'] = $content_type; }
			if($username !== false) {

				switch($authentication) {

					case 'basic' :

						$headers['Authorization']  = 'Basic ' . base64_encode($username . ':' . $password);
						break;
				}
			}

			// Build args
			$args = array(

				'method'		=>	$method,
				'headers'		=>	$headers,
				'user-agent'	=>	'WSForm/' . WS_FORM_VERSION . ' (wsform.com)',
				'timeout'		=>	$timeout,
				'sslverify'		=>	$sslverify
			);

			// Add body
			if($method != 'GET') { $args['body'] = $body; }

			// URL
			$url = $endpoint . $path . $query_string;

			// Call using Wordpress wp_remote_get
			$response = wp_remote_request($url, $args);

			// Check for error
			if($api_response_error = is_wp_error($response)) {

				// Handle error
				$api_response_error_message = $response->get_error_message();
				$api_response_headers = array();
				$api_response_body = '';
				$api_response_http_code = 0;

			} else {

				// Handle response
				$api_response_error_message = '';
				$api_response_headers = wp_remote_retrieve_headers($response);
				$api_response_body = wp_remote_retrieve_body($response);
				$api_response_http_code = wp_remote_retrieve_response_code($response);
			}

			// Return response
			return array('error' => $api_response_error, 'error_message' => $api_response_error_message, 'response' => $api_response_body, 'http_code' => $api_response_http_code, 'headers' => $api_response_headers);
		}

		// Get value of an object, otherwise return false if not set
		public function get_object_value($field, $key) {

			return isset($field->{$key}) ? $field->{$key} : false;
		}
	}
