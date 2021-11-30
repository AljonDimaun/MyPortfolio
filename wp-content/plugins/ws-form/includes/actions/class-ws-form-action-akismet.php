<?php

	class WS_Form_Action_Akismet_V1 extends WS_Form_Action {

		public $id = 'akismetv1';
		public $pro_required = false;
		public $label;
		public $label_action;
		public $events;
		public $multiple = false;
		public $configured = true;
		public $priority = 20;
		public $can_repost = false;
		public $form_add = false;

		public function __construct() {

			// Set label
			$this->label = __('Akismet', 'ws-form');

			// Set label for actions pull down
			$this->label_action = __('Spam Check with Akismet', 'ws-form');

			// Events
			$this->events = array('save', 'submit');

			// Add to spam tab in form settings sidebar
			add_filter('wsf_config_settings_form_admin', array($this, 'config_settings_form_admin'), 10, 1);

			// Register config filters
			add_filter('wsf_config_meta_keys', array($this, 'config_meta_keys'), 10, 2);

			// Register as action
			add_filter('wsf_actions_post_save', array($this, 'actions_post_add'), 10, 3);
			add_filter('wsf_actions_post_submit', array($this, 'actions_post_add'), 10, 3);

			// Register action
			parent::register($this);
		}

		public function actions_post_add($actions, $form, $submit) {

			if(
				!self::plugin_installed() ||
				!self::form_enabled($form->id) ||
				!self::enabled($form)
			) {

				return $actions;
			}

			// Remove existing Akismet actions (backward compatibility with new spam system)
			foreach($actions as $key => $action) {

				if($action['id'] === $this->id) {

					unset($actions[$key]);
				}
			}

			// Prepend this action so it runs first
			$actions[] = array(

				'id' => $this->id,
				'meta' => array(),
				'events' => array(
					'0' => 'save',
					'1' => 'submit'
				),
				'label' => $this->label_action,
				'priority' => $this->priority,
				'row_index' => 0
			);

			return $actions;
		}

		public function enabled($form) {

			$enabled = WS_Form_Common::get_object_meta_value($form, 'action_' . $this->id . '_enabled');

			return ($enabled === 'on');
		}

		public function plugin_installed() {

			return class_exists('Akismet');
		}

		public function form_enabled() {

			$key = self::get_key();

			return ($key !== false) && (strlen($key) === 12);
		}

		public function get_key() {

			return is_callable(array('Akismet', 'get_api_key')) ? Akismet::get_api_key() : ((function_exists('akismet_get_key')) ? akismet_get_key() : false);
		}

		public function post($form, &$submit, $config) {

			// Get configuration
			$api_key = 				self::get_key();
			$enabled =				WS_Form_Common::get_object_meta_value($form, 'action_' . $this->id . '_enabled', '');
			$field_email =			WS_Form_Common::get_object_meta_value($form, 'action_' . $this->id . '_field_email');
			$field_mapping =		WS_Form_Common::get_object_meta_value($form, 'action_' . $this->id . '_field_mapping');
			$spam_level_reject =	WS_Form_Common::get_object_meta_value($form, 'action_' . $this->id . '_spam_level_reject', '');
			$admin_no_run =			WS_Form_Common::get_object_meta_value($form, 'action_' . $this->id . '_admin_no_run', 'on');
			$test =					WS_Form_Common::get_object_meta_value($form, 'action_' . $this->id . '_test');

			// Checks
			if(!self::enabled($form)) { return true; }
			if(($api_key === false) || (strlen($api_key) !== 12)) { return true; }
			if($admin_no_run && WS_Form_Common::can_user('manage_options_wsform')) { return true; }

			// Build API endpoint URL
			$api_endpoint = 'https://' . $api_key . '.rest.akismet.com/1.1/';

			// Reset spam level
			$spam_level = 0;

			// Build post request
			$data = array(

				'blog'			=>	get_option('home'),
				'blog_lang'		=>	get_locale(),
				'blog_charset'	=>	get_locale(),
				'user_ip'		=>	WS_Form_Common::get_http_env(array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR')),
				'user_agent'	=>	WS_Form_Common::get_http_env(array('HTTP_USER_AGENT')),
				'referrer'		=>	WS_Form_Common::get_http_env(array('HTTP_REFERER')),
				'comment_type'	=>	'contact-form',
			);

			// Build comment_email
			if(
				($field_email != '') &&
				isset($submit->meta) &&
				isset($submit->meta[WS_FORM_FIELD_PREFIX . $field_email]) &&
				isset($submit->meta[WS_FORM_FIELD_PREFIX . $field_email]['value'])
			) {

				$email_address = $submit->meta[WS_FORM_FIELD_PREFIX . $field_email]['value'];
				if(filter_var($email_address, FILTER_VALIDATE_EMAIL)) { $data['comment_author_email'] = $email_address; }
			}

			// Build comment_content
			$comment_content_array = array();
			foreach($field_mapping as $field_map) {

				$field_id = $field_map->ws_form_field;
				$submit_value = parent::get_submit_value($submit, WS_FORM_FIELD_PREFIX . $field_id, false);
				if($submit_value !== false) {

					$comment_content_array[] = $submit_value;
				}
			}
			if(count($comment_content_array) > 0) {

				$data['commment_content'] = implode("\n", $comment_content_array);
			}

			// Test
			if($test) { $data['is_test'] = true; }

			// Add permalink if available
			if($permalink = get_permalink()) { $data['permalink'] = $permalink; }

			// Build query string
			$query_string = http_build_query($data);

			// POST
			$api_response = parent::api_call($api_endpoint, 'comment-check', 'POST', $query_string, false, false, false, false, 'text/plain', 'application/x-www-form-urlencoded');

			// Check for X-akismet-pro-tip header
			if(($pro_tip = parent::api_get_header($api_response, 'X-akismet-pro-tip')) !== false) {

				switch($pro_tip) {

					case 'discard' :

						$spam_level = WS_FORM_SPAM_LEVEL_MAX;
						break;
				}
			}

			// Process response
			if($spam_level == 0) {

				switch($api_response['http_code']) {

					case 200 :

						// Get response string
						$response = trim($api_response['response']);
 						switch($response) {

							// Not spam
							case 'false' :

								parent::success(__('Submitted form content to Akismet (Not spam)', 'ws-form'));
								break;

							// Spam
							case 'true' :

								$spam_level = (WS_FORM_SPAM_LEVEL_MAX * 0.75);		// 0.75 Shows up as orange in submit table
								break;

							case '' :

								parent::error(__('An error occurred when submitting the form content to Akismet', 'ws-form'));
								break;
						}
						break;

					default :

						parent::error(__('An error occurred when submitting the form content to Akismet', 'ws-form'));
				}
			}	

			// Set spam level on submit record
			if(is_null(parent::$spam_level) || (parent::$spam_level < $spam_level)) { parent::$spam_level = $spam_level; }

			// Check spam level (Return halt if submission should be rejected)
			$spam_level_reject = intval($spam_level_reject);
			if($spam_level_reject > 0) {

				if($spam_level >= $spam_level_reject) {

					parent::error(__('Spam detected', 'ws-form'));

					return 'halt';
				}
			}

			return $spam_level;
		}

		// Add meta keys to spam tab in form settings
		public function config_settings_form_admin($config_settings_form_admin) {

			if(self::plugin_installed() && self::form_enabled()) {

				$fieldset = array(

					'meta_keys'	=> array('action_' . $this->id . '_intro', 'action_' . $this->id . '_enabled', 'action_' . $this->id . '_field_email', 'action_' . $this->id . '_field_mapping', 'action_' . $this->id . '_spam_level_reject', 'action_' . $this->id . '_test', 'action_' . $this->id . '_admin_no_run')
				);

			} else {

				$fieldset = array(

					'meta_keys'	=> array('action_' . $this->id . '_intro', 'action_' . $this->id . '_not_enabled')
				);
			}

			array_unshift($config_settings_form_admin['sidebars']['form']['meta']['fieldsets']['spam']['fieldsets'], $fieldset);

			return $config_settings_form_admin;
		}

		// Meta keys for this action
		public function config_meta_keys($meta_keys = array(), $form_id = 0) {

			// Build instructions
			$instructions_array = array();

			if(!self::plugin_installed()) {

				$instructions_array[] = '<li>' . sprintf(__('Install and activate the <a href="%s" target="_blank">Akismet plugin</a>', 'ws-form'), 'https://akismet.com/?utm_source=ws_form') . '</li>';

			} else {

				$instructions_array[] = '<li class="wsf-disabled">' . __('Install and activate the Akismet plugin', 'ws-form') . '</li>';
			}

			if(!self::form_enabled()) {

				if(!self::plugin_installed()) {

					$instructions_array[] = '<li>' . __('Enter your Akismet key', 'ws-form') . '</li>';

				} else {

					$instructions_array[] = '<li>' . sprintf(__('<a href="%s">Enter your Akismet key</a>', 'ws-form'), get_admin_url(null, 'options-general.php?page=akismet-key-config')) . '</li>';
				}

			} else {

				$instructions_array[] = '<li class="wsf-disabled">' . __('Enable protection on this form', 'ws-form') . '</li>';
			}

			$instructions = sprintf('<p>%s</p><ol>%s</ol>', __('To enable Akismet on this form:', 'ws-form'), implode('', $instructions_array));

			// Build config_meta_keys
			$config_meta_keys = array(

				// Intro HTML block
				'action_' . $this->id . '_intro'		=> array(

					'type'						=>	'html',
					'html'						=>	sprintf('<a href="https://akismet.com/" target="_blank"><img src="%s/includes/third-party/akismet/images/logo.gif" width="100" height="18" alt="Akismet" title="Akismet" /></a><div class="wsf-helper">%s</div>', WS_FORM_PLUGIN_DIR_URL, sprintf(__('Use Akismet to filter out form submissions that contain spam. <a href="%s" target="_blank">Learn more</a>', 'ws-form'), WS_Form_Common::get_plugin_website_url('/knowledgebase/spam-check-with-akismet/', 'ws-form')))
				),
				
				// Not enable HTML block
				'action_' . $this->id . '_not_enabled' => array(

					'type'						=>	'html',
					'html'						=>	$instructions
				),

				// Enabled
				'action_' . $this->id . '_enabled'	=> array(

					'label'						=>	__('Enabled', 'ws-form'),
					'type'						=>	'checkbox',
					'default'					=>	''
				),

				// Email field
				'action_' . $this->id . '_field_email'	=> array(

					'label'							=>	__('Email Field', 'ws-form'),
					'type'							=>	'select',
					'options'						=>	'fields',
					'options_blank'					=>	__('Select...', 'ws-form'),
					'fields_filter_type'			=>	array('email'),
					'help'							=>	__('Select which field contains the email address of the person submitting the form.', 'ws-form'),
					'condition'					=>	array(

						array(

							'logic'				=>	'==',
							'meta_key'			=>	'action_' . $this->id . '_enabled',
							'meta_value'		=>	'on'
						)
					)
				),

				// Field mapping
				'action_' . $this->id . '_field_mapping'	=> array(

					'label'						=>	__('Fields To Check For Spam', 'ws-form'),
					'type'						=>	'repeater',
					'help'						=>	__('Select which WS Form fields Akismet should check for spam.', 'ws-form'),
					'meta_keys'					=>	array(

						'ws_form_field_edit'
					),
					'meta_keys_unique'			=>	array(

						'ws_form_field_edit'
					),
					'condition'					=>	array(

						array(

							'logic'				=>	'==',
							'meta_key'			=>	'action_' . $this->id . '_enabled',
							'meta_value'		=>	'on'
						)
					)
				),

				// List ID
				'action_' . $this->id . '_spam_level_reject'	=> array(

					'label'						=>	__('Settings', 'ws-form'),
					'type'						=>	'select',
					'help'						=>	__('Reject submission if spam level meets this criteria.', 'ws-form'),
					'options'					=>	array(

						array('value' => '', 'text' => __('Use Spam Threshold', 'ws-form')),
						array('value' => '75', 'text' => __('Reject Suspected Spam', 'ws-form')),
						array('value' => '100', 'text' => __('Reject Blatant Spam', 'ws-form')),
					),
					'condition'					=>	array(

						array(

							'logic'				=>	'==',
							'meta_key'			=>	'action_' . $this->id . '_enabled',
							'meta_value'		=>	'on'
						)
					)
				),

				// Administrator
				'action_' . $this->id . '_admin_no_run'	=> array(

					'label'						=>	__('Bypass If Administrator', 'ws-form'),
					'type'						=>	'checkbox',
					'help'						=>	__('If checked, this action will not run if you are signed in as an administrator.', 'ws-form'),
					'default'					=>	'on',
					'condition'					=>	array(

						array(

							'logic'				=>	'==',
							'meta_key'			=>	'action_' . $this->id . '_enabled',
							'meta_value'		=>	'on'
						)
					)
				),

				// Test
				'action_' . $this->id . '_test'	=> array(

					'label'						=>	__('Test Mode', 'ws-form'),
					'type'						=>	'checkbox',
					'help'						=>	__('If checked, Akismet will run in test mode.', 'ws-form'),
					'default'					=>	'',
					'condition'					=>	array(

						array(

							'logic'				=>	'==',
							'meta_key'			=>	'action_' . $this->id . '_enabled',
							'meta_value'		=>	'on'
						)
					)
				)
			);

			// Merge
			$meta_keys = array_merge($meta_keys, $config_meta_keys);

			return $meta_keys;
		}
	}

	new WS_Form_Action_Akismet_V1();
