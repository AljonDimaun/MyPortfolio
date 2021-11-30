<?php

	class WS_Form_API {

		public $error;
		public $error_message;
		public $error_code;

		protected $loader;

		public function __construct() {

			$this->error = false;
			$this->error_message = '';
			$this->error_code = 0;
		}

		// Throw API error
		public function api_throw_error($message, $error_code = 400) {

			$this->error = true;
			$this->error_message = $message;
			$this->error_code = $error_code;

			$this->api_json_response();
		}

		// Handle JSON response
		public function api_json_response($data = false, $form_id = 0, $history = false, $form_full = true, $form_published = false, $form_parse = false, $form_public = false) {

			$response_array = [];

			// Set error
			$response_array['error'] = $this->error;

			// New data
			if($data !== false) { $response_array['data'] = $data; }

			// Set HTTP content type head
			header('Content-Type: application/json');

			// API error
			if($this->error) {

				// Set error message
				$response_array['error_message'] = $this->error_message;

				// Set response code
				switch($this->error_code) {

					case '403' :

						header('HTTP/1.1 403 Forbidden', true, 403);
						break;

					case '404' :

						header('HTTP/1.1 404 Not Found', true, 403);
						break;

					default :

						header('HTTP/1.1 400 Bad Request', true, 400);
				}
			}

			if($form_id > 0) {

				// Build form array
				$ws_form_form = New WS_Form_Form();
				$ws_form_form->id = $form_id;

				try {

					if($form_published) {

						// Published
						$form_object = $ws_form_form->db_read_published($form_parse);

					} else {

						// Draft
						$form_object = $ws_form_form->db_read($form_full, $form_full, false, $form_parse);
					}

				} catch (Exception $e) {

					self::api_throw_error($e->getMessage());
				}

				if($form_public) {

					// Convert for public
					$ws_form_form->form_public($form_object);
				}

				$response_array['form'] = $form_object;

				$response_array['form_full'] = $form_full || $form_published;

				if(is_array($history)) {

					// History data array (Describes what changed), or false for do not store in history
					$history['date'] = current_datetime()->format(get_option('date_format'));
					$history['time'] = current_datetime()->format(get_option('time_format'));
					$response_array['history'] = $history;
				}
			}

			// Strip any 'Location' headers that might have been added by third party hooks and would break our response
			header_remove('Location');

			// Push JSON response
			$response_json = wp_json_encode(apply_filters('wsf_api_response_array', $response_array));
			if(json_last_error() !== 0) {

				// Set response code
				header('HTTP/1.1 400 Bad Request', true, 400);

				// Build error JSON
				$response_array = array(

					'error' => 			true,
					'error_message' =>	'JSON encoding error: ' . json_last_error_msg() . ' (' . json_last_error() . ')'
				);

				// JSON encode array
				$response_json = wp_json_encode($response_array);
			}

			// Run wsf_api_response action
			do_action('wsf_api_response');

			// Output JSON return
			echo apply_filters('wsf_api_response_json', $response_json); // phpcs:ignore

			// Stop execution
			exit;
		}

		// Set up RESTful API
		public function api_rest_api_init() {

			/* API - Config */
			require_once WS_FORM_PLUGIN_DIR_PATH . 'api/class-ws-form-api-config.php';
			$plugin_api_config = new WS_Form_API_Config();

			register_rest_route(WS_FORM_RESTFUL_NAMESPACE, '/config/', array('methods' => 'GET', 'callback' => array($plugin_api_config, 'api_get'), 'permission_callback' => function () { return true; }));

			/* API - Helper */
			require_once WS_FORM_PLUGIN_DIR_PATH . 'api/class-ws-form-api-helper.php';
			$plugin_api_helper = new WS_Form_API_Helper();

			register_rest_route(WS_FORM_RESTFUL_NAMESPACE, '/helper/test/', array('methods' => 'GET', 'callback' => array($plugin_api_helper, 'api_test'), 'permission_callback' => function () { return true; }));

			register_rest_route(WS_FORM_RESTFUL_NAMESPACE, '/helper/system/', array('methods' => 'GET', 'callback' => array($plugin_api_helper, 'api_system'), 'permission_callback' => function () { return WS_Form_Common::can_user('manage_options_wsform'); }));

			register_rest_route(WS_FORM_RESTFUL_NAMESPACE, '/helper/intro/', array('methods' => 'GET', 'callback' => array($plugin_api_helper, 'api_intro'), 'permission_callback' => function () { return WS_Form_Common::can_user('edit_form'); }));

			register_rest_route(WS_FORM_RESTFUL_NAMESPACE, '/helper/framework-detect/', array('methods' => 'POST', 'callback' => array($plugin_api_helper, 'api_framework_detect'), 'permission_callback' => function () { return WS_Form_Common::can_user('manage_options_wsform'); }));

			register_rest_route(WS_FORM_RESTFUL_NAMESPACE, '/helper/setup-push/', array('methods' => 'POST', 'callback' => array($plugin_api_helper, 'api_setup_push'), 'permission_callback' => function () { return WS_Form_Common::can_user('manage_options_wsform'); }));

			register_rest_route(WS_FORM_RESTFUL_NAMESPACE, '/helper/ws-form-css/', array('methods' => 'GET', 'callback' => array($plugin_api_helper, 'api_ws_form_css'), 'permission_callback' => function () { return true; }));

			register_rest_route(WS_FORM_RESTFUL_NAMESPACE, '/helper/ws-form-css-skin/', array('methods' => 'GET', 'callback' => array($plugin_api_helper, 'api_ws_form_css_skin'), 'permission_callback' => function () { return true; }));

			register_rest_route(WS_FORM_RESTFUL_NAMESPACE, '/helper/ws-form-css-conversational/', array('methods' => 'GET', 'callback' => array($plugin_api_helper, 'api_ws_form_css_conversational'), 'permission_callback' => function () { return true; }));

			register_rest_route(WS_FORM_RESTFUL_NAMESPACE, '/helper/ws-form-css-admin/', array('methods' => 'GET', 'callback' => array($plugin_api_helper, 'api_ws_form_css_admin'), 'permission_callback' => function () { return WS_Form_Common::can_user('edit_form'); }));

			register_rest_route(WS_FORM_RESTFUL_NAMESPACE, '/helper/css-email/', array('methods' => 'GET', 'callback' => array($plugin_api_helper, 'api_css_email'), 'permission_callback' => function () { return true; }));

			register_rest_route(WS_FORM_RESTFUL_NAMESPACE, '/helper/file_download/', array('methods' => 'GET', 'callback' => array($plugin_api_helper, 'api_file_download'), 'permission_callback' => function () { return WS_Form_Common::can_user('read_submission'); }));

			register_rest_route(WS_FORM_RESTFUL_NAMESPACE, '/helper/user_meta_hidden_column/', array('methods' => 'POST', 'callback' => array($plugin_api_helper, 'api_user_meta_hidden_columns'), 'permission_callback' => function () { return WS_Form_Common::can_user('read_submission'); }));

			register_rest_route(WS_FORM_RESTFUL_NAMESPACE, '/helper/support-contact-submit/', array('methods' => 'POST', 'callback' => array($plugin_api_helper, 'api_support_contact_submit'), 'permission_callback' => function () { return WS_Form_Common::can_user('manage_options_wsform'); }));

			register_rest_route(WS_FORM_RESTFUL_NAMESPACE, '/helper/deactivate-feedback-submit/', array('methods' => 'POST', 'callback' => array($plugin_api_helper, 'api_deactivate_feedback_submit'), 'permission_callback' => function () { return WS_Form_Common::can_user('manage_options_wsform'); }));

			register_rest_route(WS_FORM_RESTFUL_NAMESPACE, '/helper/count-submit-unread/', array('methods' => 'GET', 'callback' => array($plugin_api_helper, 'api_count_submit_unread'), 'permission_callback' => function () { return WS_Form_Common::can_user('read_submission'); }));

			register_rest_route(WS_FORM_RESTFUL_NAMESPACE, '/helper/review-nag/dismiss/', array('methods' => 'POST', 'callback' => array($plugin_api_helper, 'api_review_nag_dismiss'), 'permission_callback' => function () { return WS_Form_Common::can_user('manage_options_wsform'); }));

			register_rest_route(WS_FORM_RESTFUL_NAMESPACE, '/helper/shortcode/', array('methods' => 'POST', 'callback' => array($plugin_api_helper, 'api_review_nag_dismiss'), 'permission_callback' => function () { return WS_Form_Common::can_user('manage_options_wsform'); }));

			/* API - Form */
			require_once WS_FORM_PLUGIN_DIR_PATH . 'api/class-ws-form-api-form.php';
			$plugin_api_form = new WS_Form_API_Form();

			register_rest_route(WS_FORM_RESTFUL_NAMESPACE, '/form/(?P<form_id>[\d]+)/download/json/', array('methods' => 'POST', 'callback' => array($plugin_api_form, 'api_post_download_json'), 'permission_callback' => function () { return WS_Form_Common::can_user('export_form'); }));

			register_rest_route(WS_FORM_RESTFUL_NAMESPACE, '/form/(?P<form_id>[\d]+)/upload/json/', array('methods' => 'POST', 'callback' => array($plugin_api_form, 'api_post_upload_json'), 'permission_callback' => function () { return WS_Form_Common::can_user('import_form'); }));

			register_rest_route(WS_FORM_RESTFUL_NAMESPACE, '/form/(?P<form_id>[\d]+)/full/', array('methods' => 'GET', 'callback' => array($plugin_api_form, 'api_get_full'), 'permission_callback' => function () { return WS_Form_Common::can_user('read_form'); }));

			register_rest_route(WS_FORM_RESTFUL_NAMESPACE, '/form/(?P<form_id>[\d]+)/published/', array('methods' => 'GET', 'callback' => array($plugin_api_form, 'api_get_published'), 'permission_callback' => function () { return true; }));

			register_rest_route(WS_FORM_RESTFUL_NAMESPACE, '/form/(?P<form_id>[\d]+)/checksum/', array('methods' => 'GET', 'callback' => array($plugin_api_form, 'api_get_checksum'), 'permission_callback' => function () { return WS_Form_Common::can_user('read_form'); }));

			register_rest_route(WS_FORM_RESTFUL_NAMESPACE, '/form/(?P<form_id>[\d]+)/full/put/', array('methods' => 'POST', 'callback' => array($plugin_api_form, 'api_put_full'), 'permission_callback' => function () { return WS_Form_Common::can_user('edit_form'); }));

			register_rest_route(WS_FORM_RESTFUL_NAMESPACE, '/form/(?P<form_id>[\d]+)/put/', array('methods' => 'POST', 'callback' => array($plugin_api_form, 'api_put'), 'permission_callback' => function () { return WS_Form_Common::can_user('edit_form'); }));

			register_rest_route(WS_FORM_RESTFUL_NAMESPACE, '/form/(?P<form_id>[\d]+)/publish/', array('methods' => 'POST', 'callback' => array($plugin_api_form, 'api_put_publish'), 'permission_callback' => function () { return WS_Form_Common::can_user('publish_form'); }));

			register_rest_route(WS_FORM_RESTFUL_NAMESPACE, '/form/(?P<form_id>[\d]+)/draft/', array('methods' => 'POST', 'callback' => array($plugin_api_form, 'api_put_draft'), 'permission_callback' => function () { return WS_Form_Common::can_user('publish_form'); }));

			register_rest_route(WS_FORM_RESTFUL_NAMESPACE, '/form/(?P<form_id>[\d]+)/locations/', array('methods' => 'GET', 'callback' => array($plugin_api_form, 'api_get_locations'), 'permission_callback' => function () { return WS_Form_Common::can_user('read_form'); }));

			register_rest_route(WS_FORM_RESTFUL_NAMESPACE, '/form/(?P<form_id>[\d]+)/svg/draft/', array('methods' => 'GET', 'callback' => array($plugin_api_form, 'api_get_svg_draft'), 'permission_callback' => function () { return WS_Form_Common::can_user('read_form'); }));

			register_rest_route(WS_FORM_RESTFUL_NAMESPACE, '/form/(?P<form_id>[\d]+)/svg/published/', array('methods' => 'GET', 'callback' => array($plugin_api_form, 'api_get_svg_published'), 'permission_callback' => function () { return WS_Form_Common::can_user('read_form'); }));


			/* API - Group */
			require_once WS_FORM_PLUGIN_DIR_PATH . 'api/class-ws-form-api-group.php';
			$plugin_api_group = new WS_Form_API_Group();

			register_rest_route(WS_FORM_RESTFUL_NAMESPACE, '/group/(?P<group_id>[\d]+)/download/json/', array('methods' => 'POST', 'callback' => array($plugin_api_group, 'api_post_download_json'), 'permission_callback' => function () { return WS_Form_Common::can_user('export_form'); }));

			register_rest_route(WS_FORM_RESTFUL_NAMESPACE, '/group/(?P<group_id>[\d]+)/upload/json/', array('methods' => 'POST', 'callback' => array($plugin_api_group, 'api_post_upload_json'), 'permission_callback' => function () { return WS_Form_Common::can_user('import_form'); }));

			register_rest_route(WS_FORM_RESTFUL_NAMESPACE, '/group/(?P<group_id>[\d]+)/template/add/', array('methods' => 'POST', 'callback' => array($plugin_api_group, 'api_post_template_add'), 'permission_callback' => function () { return WS_Form_Common::can_user('manage_options_wsform'); }));

			register_rest_route(WS_FORM_RESTFUL_NAMESPACE, '/group/', array('methods' => 'POST', 'callback' => array($plugin_api_group, 'api_post'), 'permission_callback' => function () { return WS_Form_Common::can_user('edit_form'); }));

			register_rest_route(WS_FORM_RESTFUL_NAMESPACE, '/group/(?P<group_id>[\d]+)/put/', array('methods' => 'POST', 'callback' => array($plugin_api_group, 'api_put'), 'permission_callback' => function () { return WS_Form_Common::can_user('edit_form'); }));

			register_rest_route(WS_FORM_RESTFUL_NAMESPACE, '/group/(?P<group_id>[\d]+)/sort-index/', array('methods' => 'POST', 'callback' => array($plugin_api_group, 'api_put_sort_index'), 'permission_callback' => function () { return WS_Form_Common::can_user('edit_form'); }));

			register_rest_route(WS_FORM_RESTFUL_NAMESPACE, '/group/(?P<group_id>[\d]+)/clone/', array('methods' => 'POST', 'callback' => array($plugin_api_group, 'api_put_clone'), 'permission_callback' => function () { return WS_Form_Common::can_user('edit_form'); }));

			register_rest_route(WS_FORM_RESTFUL_NAMESPACE, '/group/(?P<group_id>[\d]+)/delete/', array('methods' => 'POST', 'callback' => array($plugin_api_group, 'api_delete'), 'permission_callback' => function () { return WS_Form_Common::can_user('edit_form'); }));

			/* API - Section */
			require_once WS_FORM_PLUGIN_DIR_PATH . 'api/class-ws-form-api-section.php';
			$plugin_api_section = new WS_Form_API_Section();

			register_rest_route(WS_FORM_RESTFUL_NAMESPACE, '/section/(?P<section_id>[\d]+)/download/json/', array('methods' => 'POST', 'callback' => array($plugin_api_section, 'api_post_download_json'), 'permission_callback' => function () { return WS_Form_Common::can_user('export_form'); }));

			register_rest_route(WS_FORM_RESTFUL_NAMESPACE, '/section/(?P<section_id>[\d]+)/upload/json/', array('methods' => 'POST', 'callback' => array($plugin_api_section, 'api_post_upload_json'), 'permission_callback' => function () { return WS_Form_Common::can_user('import_form'); }));

			register_rest_route(WS_FORM_RESTFUL_NAMESPACE, '/section/(?P<section_id>[\d]+)/template/add/', array('methods' => 'POST', 'callback' => array($plugin_api_section, 'api_post_template_add'), 'permission_callback' => function () { return WS_Form_Common::can_user('manage_options_wsform'); }));

			register_rest_route(WS_FORM_RESTFUL_NAMESPACE, '/section/', array('methods' => 'POST', 'callback' => array($plugin_api_section, 'api_post'), 'permission_callback' => function () { return WS_Form_Common::can_user('edit_form'); }));

			register_rest_route(WS_FORM_RESTFUL_NAMESPACE, '/section/template/', array('methods' => 'POST', 'callback' => array($plugin_api_section, 'api_post_template'), 'permission_callback' => function () { return WS_Form_Common::can_user('edit_form'); }));

			register_rest_route(WS_FORM_RESTFUL_NAMESPACE, '/section/(?P<section_id>[\d]+)/put/', array('methods' => 'POST', 'callback' => array($plugin_api_section, 'api_put'), 'permission_callback' => function () { return WS_Form_Common::can_user('edit_form'); }));

			register_rest_route(WS_FORM_RESTFUL_NAMESPACE, '/section/(?P<section_id>[\d]+)/sort-index/', array('methods' => 'POST', 'callback' => array($plugin_api_section, 'api_put_sort_index'), 'permission_callback' => function () { return WS_Form_Common::can_user('edit_form'); }));

			register_rest_route(WS_FORM_RESTFUL_NAMESPACE, '/section/(?P<section_id>[\d]+)/clone/', array('methods' => 'POST', 'callback' => array($plugin_api_section, 'api_put_clone'), 'permission_callback' => function () { return WS_Form_Common::can_user('edit_form'); }));

			register_rest_route(WS_FORM_RESTFUL_NAMESPACE, '/section/(?P<section_id>[\d]+)/delete/', array('methods' => 'POST', 'callback' => array($plugin_api_section, 'api_delete'), 'permission_callback' => function () { return WS_Form_Common::can_user('edit_form'); }));

			/* API - Field */
			require_once WS_FORM_PLUGIN_DIR_PATH . 'api/class-ws-form-api-field.php';
			$plugin_api_field = new WS_Form_API_Field();

			register_rest_route(WS_FORM_RESTFUL_NAMESPACE, '/field/(?P<field_id>[\d]+)/', array('methods' => 'GET', 'callback' => array($plugin_api_field, 'api_get'), 'permission_callback' => function () { return WS_Form_Common::can_user('read_form'); }));

			register_rest_route(WS_FORM_RESTFUL_NAMESPACE, '/field/', array('methods' => 'POST', 'callback' => array($plugin_api_field, 'api_post'), 'permission_callback' => function () { return WS_Form_Common::can_user('edit_form'); }));

			register_rest_route(WS_FORM_RESTFUL_NAMESPACE, '/field/(?P<field_id>[\d]+)/upload/csv/', array('methods' => 'POST', 'callback' => array($plugin_api_field, 'api_post_upload_csv'), 'permission_callback' => function () { return WS_Form_Common::can_user('import_form'); }));

			register_rest_route(WS_FORM_RESTFUL_NAMESPACE, '/field/(?P<field_id>[\d]+)/download/csv/', array('methods' => 'POST', 'callback' => array($plugin_api_field, 'api_post_download_csv'), 'permission_callback' => function () { return WS_Form_Common::can_user('export_form'); }));

			register_rest_route(WS_FORM_RESTFUL_NAMESPACE, '/field/(?P<field_id>[\d]+)/put/', array('methods' => 'POST', 'callback' => array($plugin_api_field, 'api_put'), 'permission_callback' => function () { return WS_Form_Common::can_user('edit_form'); }));

			register_rest_route(WS_FORM_RESTFUL_NAMESPACE, '/field/(?P<field_id>[\d]+)/sort-index/', array('methods' => 'POST', 'callback' => array($plugin_api_field, 'api_put_sort_index'), 'permission_callback' => function () { return WS_Form_Common::can_user('edit_form'); }));

			register_rest_route(WS_FORM_RESTFUL_NAMESPACE, '/field/(?P<field_id>[\d]+)/clone/', array('methods' => 'POST', 'callback' => array($plugin_api_field, 'api_put_clone'), 'permission_callback' => function () { return WS_Form_Common::can_user('edit_form'); }));

			register_rest_route(WS_FORM_RESTFUL_NAMESPACE, '/field/(?P<field_id>[\d]+)/last-api-error/clear/', array('methods' => 'POST', 'callback' => array($plugin_api_field, 'api_put_last_api_error_clear'), 'permission_callback' => function () { return WS_Form_Common::can_user('edit_form'); }));

			register_rest_route(WS_FORM_RESTFUL_NAMESPACE, '/field/(?P<field_id>[\d]+)/delete/', array('methods' => 'POST', 'callback' => array($plugin_api_field, 'api_delete'), 'permission_callback' => function () { return WS_Form_Common::can_user('edit_form'); }));

			register_rest_route(WS_FORM_RESTFUL_NAMESPACE, '/field/(?P<field_id>[\d]+)/cascade/', array('methods' => 'GET', 'callback' => array($plugin_api_field, 'api_cascade'), 'permission_callback' => function () { return true; }));

			register_rest_route(WS_FORM_RESTFUL_NAMESPACE, '/field/(?P<field_id>[\d]+)/select-ajax/', array('methods' => 'GET', 'callback' => array($plugin_api_field, 'api_select_ajax'), 'permission_callback' => function () { return true; }));
			/* API - Submit */
			require_once WS_FORM_PLUGIN_DIR_PATH . 'api/class-ws-form-api-submit.php';
			$plugin_api_submit = new WS_Form_API_Submit();

			register_rest_route(WS_FORM_RESTFUL_NAMESPACE, '/submit/(?P<submit_id>[\d]+)/', array('methods' => 'GET', 'callback' => array($plugin_api_submit, 'api_get'), 'permission_callback' => function () { return WS_Form_Common::can_user('read_submission'); }));

			register_rest_route(WS_FORM_RESTFUL_NAMESPACE, '/submit/', array('methods' => 'POST', 'callback' => array($plugin_api_submit, 'api_post'), 'permission_callback' => function () { return true; }));

			register_rest_route(WS_FORM_RESTFUL_NAMESPACE, '/submit/(?P<submit_id>[\d]+)/action/', array('methods' => 'POST', 'callback' => array($plugin_api_submit, 'api_repost'), 'permission_callback' => function () { return WS_Form_Common::can_user('edit_submission'); }));

			register_rest_route(WS_FORM_RESTFUL_NAMESPACE, '/submit/(?P<submit_id>[\d]+)/put/', array('methods' => 'POST', 'callback' => array($plugin_api_submit, 'api_put'), 'permission_callback' => function () { return WS_Form_Common::can_user('edit_submission'); }));

			register_rest_route(WS_FORM_RESTFUL_NAMESPACE, '/submit/hash/(?P<wsf_hash>[a-zA-Z0-9]+)/', array('methods' => 'GET', 'callback' => array($plugin_api_submit, 'api_get_by_hash'), 'permission_callback' => function () { return true; }));

			register_rest_route(WS_FORM_RESTFUL_NAMESPACE, '/submit/hash/(?P<wsf_hash>[a-zA-Z0-9]+)/(?P<wsf_token>[a-zA-Z0-9]+)/', array('methods' => 'GET', 'callback' => array($plugin_api_submit, 'api_get_by_hash'), 'permission_callback' => function () { return true; }));

			register_rest_route(WS_FORM_RESTFUL_NAMESPACE, '/submit/(?P<submit_id>[\d]+)/starred/on/', array('methods' => 'POST', 'callback' => array($plugin_api_submit, 'api_put_starred_on'), 'permission_callback' => function () { return WS_Form_Common::can_user('edit_submission'); }));

			register_rest_route(WS_FORM_RESTFUL_NAMESPACE, '/submit/(?P<submit_id>[\d]+)/starred/off/', array('methods' => 'POST', 'callback' => array($plugin_api_submit, 'api_put_starred_off'), 'permission_callback' => function () { return WS_Form_Common::can_user('edit_submission'); }));

			register_rest_route(WS_FORM_RESTFUL_NAMESPACE, '/submit/(?P<submit_id>[\d]+)/viewed/on/', array('methods' => 'POST', 'callback' => array($plugin_api_submit, 'api_put_viewed_on'), 'permission_callback' => function () { return WS_Form_Common::can_user('edit_submission'); }));

			register_rest_route(WS_FORM_RESTFUL_NAMESPACE, '/submit/(?P<submit_id>[\d]+)/viewed/off/', array('methods' => 'POST', 'callback' => array($plugin_api_submit, 'api_put_viewed_off'), 'permission_callback' => function () { return WS_Form_Common::can_user('edit_submission'); }));

			register_rest_route(WS_FORM_RESTFUL_NAMESPACE, '/submit/export/', array('methods' => 'POST', 'callback' => array($plugin_api_submit, 'api_export'), 'permission_callback' => function () { return WS_Form_Common::can_user('export_submission'); }));

			register_rest_route(WS_FORM_RESTFUL_NAMESPACE, '/submit/export/(?P<wsf_hash>[a-zA-Z0-9]+)/', array('methods' => 'GET', 'callback' => array($plugin_api_submit, 'api_export_get'), 'permission_callback' => function () { return WS_Form_Common::can_user('export_submission'); }));

			/* API - Template */
			require_once WS_FORM_PLUGIN_DIR_PATH . 'api/class-ws-form-api-template.php';
			$plugin_api_template = new WS_Form_API_Template();

			register_rest_route(WS_FORM_RESTFUL_NAMESPACE, '/template/upload/json/', array('methods' => 'POST', 'callback' => array($plugin_api_template, 'api_post_upload_json'), 'permission_callback' => function () { return WS_Form_Common::can_user('manage_options_wsform'); }));

			register_rest_route(WS_FORM_RESTFUL_NAMESPACE, '/template/download/json/', array('methods' => 'POST', 'callback' => array($plugin_api_template, 'api_post_download_json'), 'permission_callback' => function () { return WS_Form_Common::can_user('manage_options_wsform'); }));

			register_rest_route(WS_FORM_RESTFUL_NAMESPACE, '/template/action/', array('methods' => 'GET', 'callback' => array($plugin_api_template, 'api_get_actions'), 'permission_callback' => function () { return WS_Form_Common::can_user('create_form'); }));

			register_rest_route(WS_FORM_RESTFUL_NAMESPACE, '/template/action/(?P<action_id>[a-zA-Z0-9]+)/', array('methods' => 'GET', 'callback' => array($plugin_api_template, 'api_get_action_templates'), 'permission_callback' => function () { return WS_Form_Common::can_user('create_form'); }));

			register_rest_route(WS_FORM_RESTFUL_NAMESPACE, '/template/delete/', array('methods' => 'POST', 'callback' => array($plugin_api_template, 'api_delete'), 'permission_callback' => function () { return WS_Form_Common::can_user('manage_options_wsform'); }));

		}

		// Access denied
		public function api_access_denied() {

			echo wp_json_encode(array('edition' => WS_FORM_EDITION, 'error' => true, 'error_message' => __('Access denied', 'ws-form')));
			exit;
		}
	}
