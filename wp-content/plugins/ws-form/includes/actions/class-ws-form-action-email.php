<?php

	class WS_Form_Action_Email extends WS_Form_Action {

		public $id = 'email';
		public $pro_required = false;
		public $label;
		public $label_action;
		public $events;
		public $multiple = true;
		public $configured = true;
		public $priority = 175;
		public $can_repost = true;
		public $form_add = true;

		// Config
		public $from_email;
		public $from_name;
		public $tos;
		public $ccs;
		public $bccs;
		public $reply_to_email;
		public $subject;
		public $message_editor;
		public $message_wrapper;
		public $message_textarea;
		public $message_text_editor;
		public $message_html_editor;
		public $clear_hidden_meta_values;
		public $content_type;
		public $charset;
		public $attachments_media;
		public $headers;

		public function __construct() {

			// Set label
			$this->label = __('Email', 'ws-form');

			// Set label for actions pull down
			$this->label_action = __('Send Email', 'ws-form');

			// Events
			$this->events = array('submit');

			// Register config filters
			add_filter('wsf_config_options', array($this, 'config_options'), 10, 1);
			add_filter('wsf_config_meta_keys', array($this, 'config_meta_keys'), 10, 2);

			// Register action
			parent::register($this);
		}

		public function post($form, $submit, $config) {

			// Load config
			self::load_config($config);

			// Clear hidden meta values?
			$submit_parse = clone $submit;
			if($this->clear_hidden_meta_values) { $submit_parse->clear_hidden_meta_values(); }

			// Ensure minimal config is set
			if($this->from_email == '') { self::error(__('No sender email address specified', 'ws-form')); }
			if(count($this->tos) == 0) { self::error(__('No recipients specified', 'ws-form')); }
			if(($this->message_textarea == '') && ($this->message_text_editor == '') && ($this->message_html_editor == '')) { self::error(__('No message specified', 'ws-form')); }

			// Get content type
			if(($this->content_type === false) || ($this->content_type == '')) { $this->content_type = 'text/plain'; }
			$email_content_type = WS_Form_Common::parse_variables_process(trim($this->content_type), $form, $submit_parse, $this->content_type);

			// Get character set
			if(($this->charset === false) || ($this->charset == '')) { $this->charset = '#blog_charset'; }
			$email_charset = WS_Form_Common::parse_variables_process(trim($this->charset), $form, $submit_parse, 'text/plain');

			// Round robin
			if($this->tos_rr) {

				$this->tos = self::round_robin_tos($this->tos, $form->id, $config['row_index']);

				// Save round robin recipient to action config
				if(
					isset($this->tos[0]) &&
					isset($this->tos[0]['action_' . $this->id . '_email'])
				) {

					self::success(sprintf(__('Round robin recipient: %s', 'ws-form'), $this->tos[0]['action_' . $this->id . '_email']));
				}
			}

			// Build to address
			$email_to = self::process_email_rows($form, $submit_parse, $this->tos);

			if(count($email_to) == 0) {

				self::error(__("No 'To' email address specified", 'ws-form'));

				return 'halt';
			}

			// Build subject
			$email_subject = WS_Form_Common::parse_variables_process(trim($this->subject), $form, $submit_parse, 'text/plain');

			// Build headers
			$email_headers = array();

			// Build headers - From
			$email_from = self::email_validate($form, $submit_parse, $this->from_email, $this->from_name);

			// Validate email address
			if($email_from !== false) {

				$email_headers[] = 'From: ' . $email_from;

			} else {

				return 'halt';
			}

			// Build headers - Reply-To
			$email_reply_to = self::email_validate($form, $submit_parse, $this->reply_to_email, '', true);

			if($email_reply_to !== false) {

				$email_headers[] = 'Reply-To: ' . $email_reply_to;
			}

			// Build header - CC's
			if(!empty($this->ccs)) {

				$cc_emails = self::process_email_rows($form, $submit_parse, $this->ccs);

				foreach($cc_emails as $cc_email) {

					$email_headers[] = 'Cc: ' . $cc_email;
				}
			}

			// Build header - BCC's
			if(!empty($this->bccs)) {

				$bcc_emails = self::process_email_rows($form, $submit_parse, $this->bccs);

				foreach($bcc_emails as $bcc_email) {

					$email_headers[] = 'Bcc: ' . $bcc_email;
				}
			}

			// Builder header - Content Type
			$email_headers[] = 'Content-Type: ' . $email_content_type . ';' . (($email_charset !== false) ? ' charset=' . $email_charset : '');

			// Build attachments - Field
			$email_attachments = array();

			$temp_path = get_temp_dir() . 'ws-form-' . $submit_parse->hash;

			// Build attachments - Media
			if(is_array($this->attachments_media)) {

				foreach($this->attachments_media as $attachment) {

					// Get field_id
					if(!isset($attachment['action_' . $this->id . '_attachment']) || empty($attachment['action_' . $this->id . '_attachment'])) { continue; }
					$attachment = $attachment['action_' . $this->id . '_attachment'];

					// Decode
					$attachment_object = json_decode($attachment);
					if(
						is_null($attachment_object) ||
						!isset($attachment_object->id)

					) { continue; }

					// Get attachment ID
					$attachment_id = intval($attachment_object->id);
					if(!$attachment_id) { continue; }

					// Get file path
					$file_path = get_attached_file($attachment_id); 
					if($file_path === false) { continue; }

					// Check file exists
					if(!file_exists($file_path)) { continue; }

					// Add file to email_attachments
					$email_attachments[] = array(

						'path' 				=> $file_path,
						'unlink_after_use' 	=> false 		// Do not delete media attachments
					);
				}
			}

			// Attachments filter
			$email_attachments = apply_filters('wsf_action_email_email_attachments', $email_attachments, $form, $submit_parse, $config, $temp_path);

			// Build headers
			if(is_array($this->headers)) {

				foreach($this->headers as $header) {

					// Get header key
					$header_key = $header['action_' . $this->id . '_header_key'];
					if($header_key == '') { continue; }

					// Get header value
					$header_value = $header['action_' . $this->id . '_header_value'];
					if($header_value == '') { continue; }

					// Parse values
					$header_key = WS_Form_Common::parse_variables_process($header_key, $form, $submit_parse, 'text/plain');
					$header_value = WS_Form_Common::parse_variables_process($header_value, $form, $submit_parse, 'text/plain');

					// Add to email headers
					$email_headers[] = sprintf('%s: %s', $header_key, $header_value);
				}
			}

			// Email template
			if($this->message_wrapper) {

				$template_filename = 'templates/' . (($email_content_type == 'text/html') ? 'html/standard.html' : 'plain/standard.txt');

				$email_template = file_get_contents(dirname(__FILE__) . '/' . $template_filename);

			} else {

				$email_template = '#email_message';
			}
			$email_template = apply_filters('wsf_action_email_template', $email_template);

			// Build message
			$variables = array();
			switch($email_content_type) {

				case 'text/plain' :

					$variables['email_message'] = $this->message_textarea;
					break;

				case 'text/html' :

					switch($this->message_editor) {

						case 'text_editor' :

							$variables['email_message'] = wpautop($this->message_text_editor);
							break;

						case 'html_editor' :

							$variables['email_message'] = $this->message_html_editor;
							break;
					}
			}

			// Build message - Add template
			$email_message = WS_Form_Common::mask_parse($email_template, $variables);

			// Build message - Parse email variables
			$variables = array(

				'email_subject' 		=> $email_subject,
				'email_content_type' 	=> $email_content_type,
				'email_charset' 		=> $email_charset
			);
			$email_message = WS_Form_Common::mask_parse($email_message, $variables);

			// Build message - Parse other variables
			$email_message = WS_Form_Common::parse_variables_process($email_message, $form, $submit_parse, $email_content_type, false, false, 1, false, $config);

			// Final clean up (This removes double p tags added by WPAutoP)
			$email_message = str_replace('<p><p>', '<p>', $email_message);
			$email_message = str_replace("<p>\n<p>", '<p>', $email_message);
			$email_message = str_replace('</p></p>', '</p>', $email_message);
			$email_message = str_replace("</p>\n</p>", '</p>', $email_message);

			// Filters
			$email_to = apply_filters('wsf_action_email_to', $email_to);
			$email_subject = apply_filters('wsf_action_email_subject', $email_subject);
			$email_message = apply_filters('wsf_action_email_message', $email_message);
			$email_headers = apply_filters('wsf_action_email_headers', $email_headers);
			$email_attachments = apply_filters('wsf_action_email_attachments', $email_attachments);

			// If there are any errors, bail
			if(parent::error_count() == 0) {

				$email_attachment_paths = array();
				foreach($email_attachments as $email_attachment) {

					$email_attachment_paths[] = $email_attachment['path'];
				}

				// Run wp_mail
				$email_return = wp_mail($email_to, $email_subject, $email_message, $email_headers, $email_attachment_paths);

			} else {

				$email_return = false;
			}

			// Tidy up attachments
			if(count($email_attachments) > 0) {

				foreach($email_attachments as $email_attachment) {

					if(!$email_attachment['unlink_after_use']) { continue; }

					$path = $email_attachment['path'];

					// Delete each file
					if(file_exists($path)) {

						unlink($path);
					}
				}

				// Remove temporary path
				if(file_exists($temp_path)) {

					rmdir($temp_path);
				}
			}

			// Check response
			if($email_return) {

				self::success(__('Email successfully sent', 'ws-form'));

			} else {

				self::error(__('Error sending email', 'ws-form'));
			}
		}

		public function email_validate($form, $submit_parse, $email, $name = '') {

			// Parse email address
			$email = WS_Form_Common::parse_variables_process($email, $form, $submit_parse, 'text/plain');

			// Parse name
			if($name !== '') {

				$name = WS_Form_Common::parse_variables_process($name, $form, $submit_parse, 'text/plain');
			}

			// Sanitize email address
			$email = sanitize_email($email);

			// Check if email is blank
			if($email === '') { return false; }

			// Check email address is valid
			$email_validate = (filter_var($email, FILTER_VALIDATE_EMAIL) !== false);

			// Apply email validation filters
			if($email_validate !== false) {

				$email_validate = apply_filters('wsf_action_email_email_validate', $email_validate, $email);
			}

			// If email validates, return the email address
			if($email_validate === true) {

				// Get full email address
				$email_full = WS_Form_Common::get_email_address($email, $name);

				// Check full email address
				if($email_full === false) {

					self::error(__('Invalid email address or display name too long', 'ws-form'));

					return false;
				}

				return $email_full;
			}

			if(is_string($email_validate)) {

				self::error($email_validate);

				return false;
			}

			self::error(__(sprintf('Invalid email address: %s', $email), 'ws-form'));

			return false;
		}

		public function process_email_rows($form, $submit_parse, $rows) {

			$email_addresses = array();

			foreach($rows as $row) {

				// Get email address
				$email = $row['action_' . $this->id . '_email'];

				// Parse email address
				$email = WS_Form_Common::parse_variables_process($email, $form, $submit_parse, 'text/plain');

				// Get name
				$name = isset($row['action_' . $this->id . '_name']) ? $row['action_' . $this->id . '_name'] : '';

				// Replace new lines to commas (We get new lines between values from #field if there are multiple values)
				$email = str_replace("\n", ',', $email);

				// Explode in case the email addresses are comma separated
				$email_array = explode(',', $email);

				// Process each email
				foreach($email_array as $email) {

					// Sanitize email address
					$email = self::email_validate($form, $submit_parse, $email, $name);

					if($email !== false) {

						$email_addresses[] = $email;
					}
				}
			}

			return $email_addresses;
		}

		public function round_robin_tos($tos, $form_id, $action_row_index) {

			// Return array
			$tos_return = array();

			// Work out hash
			$tos_hash = md5(json_encode($tos));

			// Set any blank percentages automatically
			$rr_percentage_total = 0;
			$rr_percentage_blank_count = 0;
			foreach($tos as $to) {

				if(trim($to['action_' . $this->id . '_email']) == '') { continue; }

				$rr_percentage = $to['action_' . $this->id . '_rr_percentage'];
				if($rr_percentage == '') {

					$rr_percentage_blank_count++;

				} else {

					$rr_percentage = floatval($rr_percentage);
					$rr_percentage_total += $rr_percentage;
				}
			}

			if($rr_percentage_blank_count > 0) {

				$rr_percentage_difference_for_zeroes = (100 - $rr_percentage_total) / $rr_percentage_blank_count;

				foreach($tos as $to_index => $to) {

					if(trim($to['action_' . $this->id . '_email']) == '') { continue; }

					$rr_percentage = $to['action_' . $this->id . '_rr_percentage'];
					if($rr_percentage == '') {

						$tos[$to_index]['action_' . $this->id . '_rr_percentage'] = $rr_percentage_difference_for_zeroes;
					}
				}
			}

			// Read round robin data
			$email_rr = WS_Form_Common::option_get(sprintf('email_rr_%u_%u', $form_id, $action_row_index));

			// Get stats
			if(
				($email_rr === false) ||
				!is_array($email_rr) ||
				!isset($email_rr['hash']) ||
				($email_rr['hash'] !== $tos_hash)
			) {

				// Reset array
				$email_rr = array(

					'to' => array(),
					'send_count' => 0,
					'hash' => $tos_hash
				);
			}

			// Get total send count to date
			$send_count = $email_rr['send_count'];

			// Find best send candidate
			$percentage_difference_max = false;
			$email_send_count_recipient = 0;
			foreach($tos as $to_index => $to) {

				$email = $to['action_' . $this->id . '_email'];

				$rr_percentage = isset($to['action_' . $this->id . '_rr_percentage']) ? floatval($to['action_' . $this->id . '_rr_percentage']) : 100;

				// Get number of times recipient has received email
				$email_send_count = isset($email_rr['to'][$email]) ? $email_rr['to'][$email] : 0;

				$email_percentage = ($send_count > 0) ? (($email_send_count / $send_count) * 100) : 0;

				// Calculate percentage difference
				$percentage_difference = $rr_percentage - $email_percentage;

				if(($percentage_difference_max === false) || ($percentage_difference > $percentage_difference_max)) {

					$percentage_difference_max = $percentage_difference;
					$email_send_count_recipient = $email_send_count;
					$tos_return = array($to);
				}
			}

			// Log best candidates
			foreach($tos_return as $to) {

				$email = $to['action_' . $this->id . '_email'];
				$email_rr['to'][$email] = $email_send_count_recipient + 1;
			}

			// Write back round robin data
			$email_rr['send_count']++;
			WS_Form_Common::option_set(sprintf('email_rr_%u_%u', $form_id, $action_row_index), $email_rr);

			return $tos_return;
		}

		public function load_config($config) {

			// Get configuration
			$this->from_email = 				parent::get_config($config, 'action_' . $this->id . '_from_email');
			$this->from_name = 					parent::get_config($config, 'action_' . $this->id . '_from_name');
			$this->tos = 						parent::get_config($config, 'action_' . $this->id . '_to');
			if(!is_array($this->tos)) { $this->tos = array(); }
			$this->tos_rr = 					parent::get_config($config, 'action_' . $this->id . '_to_rr');
			$this->ccs = 						parent::get_config($config, 'action_' . $this->id . '_cc');
			if(!is_array($this->ccs)) { $this->ccs = array(); }
			$this->bccs = 						parent::get_config($config, 'action_' . $this->id . '_bcc');
			if(!is_array($this->bccs)) { $this->bccs = array(); }
			$this->reply_to_email = 			parent::get_config($config, 'action_' . $this->id . '_reply_to_email');
			$this->subject = 					parent::get_config($config, 'action_' . $this->id . '_subject');
			$this->attachments_media = 			parent::get_config($config, 'action_' . $this->id . '_attachments_media');
			if(!is_array($this->attachments_media)) { $this->attachments_media = array(); }
			$this->message_editor = 			parent::get_config($config, 'action_' . $this->id . '_message_editor');
			$this->message_wrapper = 			parent::get_config($config, 'action_' . $this->id . '_message_wrapper');
			$this->message_textarea = 			parent::get_config($config, 'action_' . $this->id . '_message_textarea');
			$this->message_text_editor = 		parent::get_config($config, 'action_' . $this->id . '_message_text_editor');
			$this->message_html_editor = 		parent::get_config($config, 'action_' . $this->id . '_message_html_editor');
			$this->clear_hidden_meta_values = 	parent::get_config($config, 'action_' . $this->id . '_clear_hidden_meta_values', 'on');
			$this->content_type =				parent::get_config($config, 'action_' . $this->id . '_content_type');
			$this->headers = 					parent::get_config($config, 'action_' . $this->id . '_headers');
			if(!is_array($this->headers)) { $this->headers = array(); }
			$this->charset = 					parent::get_config($config, 'action_' . $this->id . '_charset');
		}

		// Get settings
		public function get_action_settings() {

			$settings = array(

				'meta_keys'		=> array(

					'action_' . $this->id . '_from_email',
					'action_' . $this->id . '_from_name',
					'action_' . $this->id . '_to',
					'action_' . $this->id . '_to_rr',
					'action_' . $this->id . '_cc',
					'action_' . $this->id . '_bcc',
					'action_' . $this->id . '_reply_to_email',
					'action_' . $this->id . '_subject',
					'action_' . $this->id . '_message_textarea',
					'action_' . $this->id . '_message_text_editor',
					'action_' . $this->id . '_message_html_editor',
					'action_' . $this->id . '_message_editor',
					'action_' . $this->id . '_attachments_media',
					'action_' . $this->id . '_message_wrapper',
					'action_' . $this->id . '_clear_hidden_meta_values',
					'action_' . $this->id . '_content_type',
					'action_' . $this->id . '_headers',
					'action_' . $this->id . '_charset',
				)
			);

			// Wrap settings so they will work with sidebar_html function in admin.js
			$settings = parent::get_settings_wrapper($settings);

			// Add labels
			$settings->label = $this->label;
			$settings->label_action = $this->label_action;

			// Add multiple
			$settings->multiple = $this->multiple;

			// Add events
			$settings->events = $this->events;

			// Add can_repost
			$settings->can_repost = $this->can_repost;

			// Apply filter
			$settings = apply_filters('wsf_action_' . $this->id . '_settings', $settings);

			return $settings;
		}

		// Meta keys for this action
		public function config_meta_keys($meta_keys = array(), $form_id = 0) {

			// Build config_meta_keys
			$config_meta_keys = array(

				// Content type
				'action_' . $this->id . '_content_type'	=> array(

					'label'						=>	__('Content Type', 'ws-form'),
					'type'						=>	'select',
					'options'					=>	array(

						array('value' => 'text/plain', 'text' => __('Plain text', 'ws-form')),
						array('value' => 'text/html', 'text' => __('HTML', 'ws-form')),
					),
					'help'						=>	__('Email content MIME type.', 'ws-form'),
					'default'					=>	'text/html'
				),

				// From - Email
				'action_' . $this->id . '_from_email'	=> array(

					'label'						=>	__('From Email Address', 'ws-form'),
					'type'						=>	'text',
					'help'						=>	__('Email address email sent from.', 'ws-form'),
					'default'					=>	'#blog_admin_email',
					'select_list'				=>	true
				),

				// From - Display Name
				'action_' . $this->id . '_from_name'	=> array(

					'label'						=>	__('From Display Name (Optional)', 'ws-form'),
					'type'						=>	'text',
					'help'						=>	__('Display name email sent from.', 'ws-form'),
					'default'					=>	'#blog_name',
					'select_list'				=>	true
				),

				// To
				'action_' . $this->id . '_to'	=> array(

					'label'						=>	__('To', 'ws-form'),
					'type'						=>	'repeater',
					'meta_keys'					=>	array(

						'action_' . $this->id . '_email',
						'action_' . $this->id . '_name',
						'action_' . $this->id . '_rr_percentage',
					),
					'help'						=>	__('Email address(es) to send email to.', 'ws-form'),
					'default'					=>	array(

						(object) array(

							'action_' . $this->id . '_email' 	=> '#blog_admin_email',
							'action_' . $this->id . '_name' 	=> '#blog_name'
						)
					)
				),

				// To - Round Robin
				'action_' . $this->id . '_to_rr'	=> array(

					'label'						=>	__('Round Robin', 'ws-form'),
					'type'						=>	'checkbox',
					'help'						=>	__("Send to a single 'To' recipient using round robin rules.", 'ws-form'),
					'default'					=>	'',
					'column_toggle_meta_key'	=>	'action_' . $this->id . '_to',
					'column_toggle_column_id'	=>	'rr',
				),

				// CC
				'action_' . $this->id . '_cc'	=> array(

					'label'						=>	__('CC', 'ws-form'),
					'type'						=>	'repeater',
					'meta_keys'					=>	array(

						'action_' . $this->id . '_email',
						'action_' . $this->id . '_name'
					),
					'help'						=>	__('Email address(es) to carbon copy email to.', 'ws-form')
				),

				// BCC
				'action_' . $this->id . '_bcc'	=> array(

					'label'						=>	__('BCC', 'ws-form'),
					'type'						=>	'repeater',
					'meta_keys'					=>	array(

						'action_' . $this->id . '_email',
						'action_' . $this->id . '_name'
					),
					'help'						=>	__('Email address(es) to blind carbon copy email to.', 'ws-form')
				),

				// Reply-To - Email
				'action_' . $this->id . '_reply_to_email'	=> array(

					'label'						=>	__('Reply To Email Address', 'ws-form'),
					'type'						=>	'text',
					'help'						=>	__('Email address replies will be sent to.', 'ws-form'),
					'default'					=>	'',
					'select_list'				=>	true
				),

				// Subject
				'action_' . $this->id . '_subject'	=> array(

					'label'						=>	__('Subject', 'ws-form'),
					'type'						=>	'text',
					'help'						=>	__('Email subject.', 'ws-form'),
					'default'					=>	'#form_label',
					'select_list'				=>	true
				),

				// Message - Format
				'action_' . $this->id . '_message_editor'	=> array(

					'label'						=>	__('Message Editor', 'ws-form'),
					'type'						=>	'select',
					'options'					=>	array(

						array('value' => 'text_editor', 'text' => __('Visual / Text', 'ws-form')),
						array('value' => 'html_editor', 'text' => __('HTML', 'ws-form'))
					),
					'default'					=>	'text_editor',
					'condition'					=>	array(

						array(

							'logic'			=>	'==',
							'meta_key'		=>	'action_' . $this->id . '_content_type',
							'meta_value'	=>	'text/html'
						)
					)
				),

				// Message - Wrapper
				'action_' . $this->id . '_message_wrapper'	=> array(

					'label'						=>	__('Wrap Message in Header and Footer?', 'ws-form'),
					'type'						=>	'checkbox',
					'help'						=>	__('Enabling this will wrap your message in a standard header and footer for convenience.', 'ws-form'),
					'default'					=>	'on'
				),

				// Message - Text Area
				'action_' . $this->id . '_message_textarea'	=> array(

					'label'						=>	__('Message', 'ws-form'),
					'type'						=>	'textarea',
					'help'						=>	__('Email message.', 'ws-form'),
					'default'					=>	'#email_submission',
					'condition'					=>	array(

						array(

							'logic'			=>	'==',
							'meta_key'		=>	'action_' . $this->id . '_content_type',
							'meta_value'	=>	'text/plain'
						),
					),
					'select_list'				=>	true
				),

				// Message - WordPress Editor
				'action_' . $this->id . '_message_text_editor'	=> array(

					'label'						=>	__('Message', 'ws-form'),
					'type'						=>	'text_editor',
					'help'						=>	__('Email message.', 'ws-form'),
					'default'					=>	"<h3>#email_subject</h3>\n\n#email_submission",
					'css'						=>	'css-email',
					'condition'					=>	array(

						array(

							'logic'				=>	'==',
							'meta_key'			=>	'action_' . $this->id . '_content_type',
							'meta_value'		=>	'text/html'
						),

						array(

							'logic'				=>	'==',
							'meta_key'			=>	'action_' . $this->id . '_message_editor',
							'meta_value'		=>	'text_editor',
							'logic_previous'	=>	'&&'
						)
					),
					'select_list'				=>	true
				),

				// Message - HTML Editor
				'action_' . $this->id . '_message_html_editor'	=> array(

					'label'						=>	__('Message', 'ws-form'),
					'type'						=>	'html_editor',
					'help'						=>	__('Email message.', 'ws-form'),
					'default'					=>	"<h1>#email_subject</h1>\n\n#email_submission",
					'condition'					=>	array(

						array(

							'logic'				=>	'==',
							'meta_key'			=>	'action_' . $this->id . '_content_type',
							'meta_value'		=>	'text/html'
						),

						array(

							'logic'				=>	'==',
							'meta_key'			=>	'action_' . $this->id . '_message_editor',
							'meta_value'		=>	'html_editor',
							'logic_previous'	=>	'&&'
						)
					),
					'select_list'				=>	true
				),

				// Clear hidden meta values
				'action_' . $this->id . '_clear_hidden_meta_values'	=> array(

					'label'						=>	__('Clear Hidden Fields', 'ws-form'),
					'type'						=>	'checkbox',
					'help'						=>	__('Enabling this will clear fields that were hidden when the form was submitted.', 'ws-form'),
					'default'					=>	'on'
				),

				// Character set
				'action_' . $this->id . '_charset'	=> array(

					'label'						=>	__('Character Set', 'ws-form'),
					'type'						=>	'text',
					'help'						=>	__('Email character set', 'ws-form'),
					'default'					=>	'#blog_charset',
					'select_list'				=>	true
				),

				// Attachments - Media
				'action_' . $this->id . '_attachments_media'	=> array(

					'label'						=>	__('Media Attachments', 'ws-form'),
					'type'						=>	'repeater',
					'meta_keys'					=>	array(

						'action_' . $this->id . '_attachment'
					),
					'help'						=>	__('Add media files as email attachments.', 'ws-form')
				),

				// Attachment URL
				'action_' . $this->id . '_attachment'	=> array(

					'label'						=>	__('Media Attachment', 'ws-form'),
					'type'						=>	'media'
				),

				// Email address
				'action_' . $this->id . '_email'	=> array(

					'label'						=>	__('Email Address', 'ws-form'),
					'type'						=>	'text'
				),

				// Name
				'action_' . $this->id . '_name'	=> array(

					'label'						=>	__('Display Name', 'ws-form'),
					'type'						=>	'text'
				),

				// Percentage
				'action_' . $this->id . '_rr_percentage'	=> array(

					'label'						=>	__('Round Robin %', 'ws-form'),
					'type'						=>	'number',
					'placeholder'				=>	__('Auto', 'ws-form'),
					'column_id'					=>	'rr'
				),

				// Headers
				'action_' . $this->id . '_headers'	=> array(

					'label'						=>	__('Headers', 'ws-form'),
					'type'						=>	'repeater',
					'meta_keys'					=>	array(

						'action_' . $this->id . '_header_key',
						'action_' . $this->id . '_header_value'
					),
					'help'						=>	__('Additional email headers.', 'ws-form')
				),

				// Header key
				'action_' . $this->id . '_header_key'	=> array(

					'label'						=>	__('Header Key', 'ws-form'),
					'type'						=>	'text'
				),

				// Header value
				'action_' . $this->id . '_header_value'	=> array(

					'label'						=>	__('Header Value', 'ws-form'),
					'type'						=>	'text'
				)
			);

			// Merge
			$meta_keys = array_merge($meta_keys, $config_meta_keys);

			return $meta_keys;
		}

		// Plug-in options for this action
		public function config_options($options) {

			$options['action_' . $this->id] = array(

				'label'		=>	$this->label,

				'groups'	=>	array(

					'logo'	=>	array(

						'heading'		=>	__('Logo', 'ws-form'),

						'fields'	=>	array(

							'action_' . $this->id . '_logo'	=>	array(

								'label'		=>	__('Image', 'ws-form'),
								'type'		=>	'image',
								'button'	=>	'wsf-image',
								'help'		=>	__('Use #email_logo in your email template to add this logo.', 'ws-form')
							),

							'action_' . $this->id . '_logo_size'	=>	array(

								'label'		=>	__('Size', 'ws-form'),
								'type'		=>	'image_size',
								'default'	=>	'full',
								'help'		=>	__('Recommended max dimensions: 400 x 200 pixels.')
							)
						)
					),

					'email_submission'	=>	array(

						'heading'		=>	'#email_submission',

						'fields'	=>	array(

							'action_' . $this->id . '_group_labels'	=> array(

								'label'		=>	__('Tab Labels', 'ws-form'),
								'type'		=>	'select',
								'default'	=>	'auto',
								'options'	=>	array(

									'auto'				=>	array('text' => __('Auto', 'ws-form')),
									'true'				=>	array('text' => __('Yes', 'ws-form')),
									'false'				=>	array('text' => __('No', 'ws-form'))
								),
								'help'		=>	__("Auto - Only shown if any fields are not empty and the 'Show Label' setting is enabled.<br />Yes - Only shown if the 'Show Label' setting is enabled for that tab.<br />No - Never shown.", 'ws-form')
							),

							'action_' . $this->id . '_section_labels'	=> array(

								'label'		=>	__('Section Labels', 'ws-form'),
								'type'		=>	'select',
								'default'	=>	'auto',
								'options'	=>	array(

									'auto'				=>	array('text' => __('Auto', 'ws-form')),
									'true'				=>	array('text' => __('Yes', 'ws-form')),
									'false'				=>	array('text' => __('No', 'ws-form'))
								),
								'help'		=>	__("Auto - Only shown if any fields are not empty and the 'Show Label' setting is enabled.<br />Yes - Only shown if the 'Show Label' setting is enabled.<br />No - Never shown.", 'ws-form')
							),

							'action_' . $this->id . '_field_labels'	=> array(

								'label'		=>	__('Field Labels', 'ws-form'),
								'type'		=>	'select',
								'default'	=>	'auto',
								'options'	=>	array(

									'auto'				=>	array('text' => __("Auto", 'ws-form')),
									'true'				=>	array('text' => __('Yes', 'ws-form')),
									'false'				=>	array('text' => __('No', 'ws-form'))
								),
								'help'		=>	__("Auto - Only shown if the field is not empty.<br />Yes - Only shown if the 'Show Label' setting is enabled.<br />No - Never shown.", 'ws-form')
							),

							'action_' . $this->id . '_static_fields'	=>	array(

								'label'		=>	__('Static Fields', 'ws-form'),
								'type'		=>	'checkbox',
								'default'	=>	true,
								'help'		=>	__('Show static fields such as text and HTML, if not excluded at a field level.')
							),

							'action_' . $this->id . '_exclude_empty'	=>	array(

								'label'		=>	__('Exclude Empty Fields', 'ws-form'),
								'type'		=>	'checkbox',
								'default'	=>	true,
								'help'		=>	__('Exclude empty fields in email templates.')
							),

							'action_' . $this->id . '_embed_images'	=>	array(

								'label'		=>	__('Embed Images', 'ws-form'),
								'type'		=>	'checkbox',
								'default'	=>	false,
								'help'		=>	__('If checked, file and signature images will be embedded in the email template.')
							)
						)
					)
				)
			);

			return $options;
		}
	}

	new WS_Form_Action_Email();
