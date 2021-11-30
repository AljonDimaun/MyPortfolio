<?php

	class WS_Form_Action_Human_Presence extends WS_Form_Action {

		public $id = 'humanpresence';
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
			$this->label = __('Human Presence', 'ws-form');

			// Set label for actions pull down10
			$this->label_action = __('Spam Check with Human Presence', 'ws-form');

			// Events
			$this->events = array('save', 'submit');

			// Add to spam tab in form settings sidebar
			add_filter('wsf_config_settings_form_admin', array($this, 'config_settings_form_admin'), 20, 1);

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
				!self::form_enabled($form->id)
			) {

				return $actions;
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

		public function plugin_installed() {

			return class_exists('HumanPresence');
		}

		public function form_enabled($form_id = false) {

			if($form_id === false) {

				// Get ID of form (0 = New)
				$form_id = intval(WS_Form_Common::get_query_var('id', 0));
				if($form_id === 0) { return false; }
			}

			// Build Human Presence form ID
			$human_presence_form_id = sprintf('wsf-%u', $form_id);

			// Check for options
			$options = get_option('wp-human-presence');
			if(
				($options === false) ||
				!isset($options['hp_forms']) ||
				!isset($options['hp_forms'][$human_presence_form_id]) ||
				!isset($options['hp_forms'][$human_presence_form_id]['form_enabled'])
			) {
				return false;
			}

			$hp_form_enabled = $options['hp_forms'][$human_presence_form_id]['form_enabled'];

			return(($hp_form_enabled != null) && ($hp_form_enabled != 0));
		}

		public function post($form, &$submit, $config) {

			// Do not run if administator
			$admin_no_run = WS_Form_Common::get_object_meta_value($form, 'action_' . $this->id . '_admin_no_run', 'on');
			if($admin_no_run && WS_Form_Common::can_user('manage_options_wsform')) { return true; }

			// Apply Human Presence filter
			try {

				$wsf_action_humanpresence_check = apply_filters('wsf_action_humanpresence_check', $form->id);

			} catch (Exception $e) {

				parent::error(sprintf(__('Error submitting to Human Presence: %s', 'ws-form'), $e->getMessage()));
			}

			// Read validation failed
			$validation_failed = isset($wsf_action_humanpresence_check['validation_failed']) ? $wsf_action_humanpresence_check['validation_failed'] : false;

			// Read confidence level
			$confidence = isset($wsf_action_humanpresence_check['confidence']) ? floatVal($wsf_action_humanpresence_check['confidence']) : 100;

			// Set spam level
			$spam_level = (100 - $confidence);

			// API message
			if(!$validation_failed) {

				parent::success(__('Submission analyzed by Human Presence (Validation passed)', 'ws-form'));
			}

			// Set spam level on submit record
			if(is_null(parent::$spam_level) || (parent::$spam_level < $spam_level)) { parent::$spam_level = $spam_level; }

			// Check spam level (Return halt if submission should be rejected)
			$spam_level_reject = intval(WS_Form_Common::get_object_meta_value($form, 'action_' . $this->id . '_spam_level_reject', '25'));
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

					'meta_keys'	=> array('action_' . $this->id . '_intro', 'action_' . $this->id . '_spam_level_reject', 'action_' . $this->id . '_admin_no_run')
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

				$instructions_array[] = '<li>' . sprintf(__('Install and activate the <a href="%s" target="_blank">Human Presence plugin</a>', 'ws-form'), 'https://www.humanpresence.io/anti-spam-wordpress-plugin/') . '</li>';

			} else {

				$instructions_array[] = '<li class="wsf-disabled">' . __('Install and activate the Human Presence plugin', 'ws-form') . '</li>';
			}

			if(!self::form_enabled()) {

				if(!self::plugin_installed()) {

					$instructions_array[] = '<li>' . __('Enable protection on this form', 'ws-form') . '</li>';

				} else {

					$instructions_array[] = '<li>' . sprintf(__('<a href="%s">Enable protection on this form</a>', 'ws-form'), get_admin_url(null, 'admin.php?page=wp-human-presence')) . '</li>';
				}

			} else {

				$instructions_array[] = '<li class="wsf-disabled">' . __('Enable protection on this form', 'ws-form') . '</li>';
			}

			$instructions = sprintf('<p>%s</p><ol>%s</ol>', __('To enable Human Presence on this form:', 'ws-form'), implode('', $instructions_array));

			// Build config_meta_keys
			$config_meta_keys = array(

				// Intro HTML block
				'action_' . $this->id . '_intro'		=> array(

					'type'						=>	'html',
					'html'						=>	sprintf('<a href="https://www.humanpresence.io/anti-spam-wordpress-plugin/?utm_source=ws_form" target="_blank"><img src="%s/includes/third-party/human-presence/images/logo.gif" width="150" height="41" alt="Human Presence" title="Human Presence" /></a><div class="wsf-helper">%s</div>', WS_FORM_PLUGIN_DIR_URL, sprintf(__('Human Presence utilizes anonymized behavior analysis and proprietary algorithms to invisibly detect and eliminate malicious bot activity without complicated configuration. <a href="%s" target="_blank">Learn more</a>', 'ws-form'), WS_Form_Common::get_plugin_website_url('/knowledgebase/spam-check-with-human-presence/', 'ws-form')))
				),
				
				// Not enable HTML block
				'action_' . $this->id . '_not_enabled' => array(

					'type'						=>	'html',
					'html'						=>	$instructions
				),

				// How to handle spam
				'action_' . $this->id . '_spam_level_reject'	=> array(

					'label'						=>	__('Settings', 'ws-form'),
					'type'						=>	'select',
					'help'						=>	__('Reject all submissions that are considered non-human or use the spam threshold setting below to determine if a submission should be moved to the submissions spam folder.', 'ws-form'),
					'options'					=>	array(

						array('value' => '25', 'text' => __('Reject Non-Human', 'ws-form')),
						array('value' => '', 'text' => __('Use Spam Threshold', 'ws-form'))
					),
					'default'					=>	'25'
				),

				// Administrator
				'action_' . $this->id . '_admin_no_run'	=> array(

					'label'						=>	__('Bypass If Administrator', 'ws-form'),
					'type'						=>	'checkbox',
					'help'						=>	__('If checked, Human Presence form protection will not run if you are logged in as an administrator.', 'ws-form'),
					'default'					=>	'on'
				)
			);

			// Merge
			$meta_keys = array_merge($meta_keys, $config_meta_keys);

			return $meta_keys;
		}
	}

	new WS_Form_Action_Human_Presence();
