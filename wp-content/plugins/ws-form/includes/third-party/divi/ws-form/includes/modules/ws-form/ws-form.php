<?php

	class ET_Builder_Module_WS_Form extends ET_Builder_Module {

		public $slug       = 'ws_form_divi';
		public $vb_support = 'on';

		public function init() {

			// Set name of module
			$this->name = __('WS Form', 'ws-form');

			// Use raw content, do not wpautop it
			$this->use_raw_content = true;

			// Create Form selector
			$this->settings_modal_toggles = array(

				'general'  => array(

					'toggles' => array(

						'ws_form_divi_form_id' => __('Form', 'ws-form')
					)
				)
			);

			// Set icon
			$this->icon_path = plugin_dir_path(__FILE__) . 'icon.svg';
		}

		public function get_advanced_fields_config() {

			// Remove link options
			return array(

				'link_options' => false,
			);
		}

		public function get_fields() {

			// Build form list
			$ws_form_form = New WS_Form_Form();
			$forms = $ws_form_form->db_read_all('', "NOT (status = 'trash')", 'label ASC', '', '', false);
			$form_array = array('0' => __('Select form...', 'ws-form'));

			if($forms) {

				foreach($forms as $form) {

					// Get form ID
					$form_id = $form['id'];

					// Divi bug fix
					if($form_id === '1') { $form_id = '01'; }

					// Add to form array
					$form_array[$form_id] = $form['label'] . ' (ID: ' . $form['id'] . ')';
				}
			}

			// Return field configuration
			return array(

				'form_id'     => array(

					'label'				=> __('Form', 'ws-form'),
					'type'				=> 'select',
					'options'			=> $form_array,
					'option_category'	=> 'basic_option',
					'description'		=> __('Select the form that you would like to use for this Divi module.', 'ws-form'),
					'toggle_slug'		=> 'ws_form_divi_form_id'
				)
			);
		}

		public function render($unprocessed_props, $content = null, $render_slug = null) {

			$form_id = isset($this->props['form_id']) ? intval($this->props['form_id']) : 0;

			if($form_id > 0) {

				if(
					isset($_GET) && isset($_GET['et_fb'])	// phpcs:ignore
				) {

					// Render shortcode (Editor)
					return sprintf('<div style="min-height:42px">%s</div>', do_shortcode(sprintf('[%s id="%u" visual_builder="true"]', WS_FORM_SHORTCODE, $form_id)));

				} else {

					// Render shortcode (Frontend)
					return do_shortcode(sprintf('[%s id="%u"]', WS_FORM_SHORTCODE, $form_id));
				}

			} else {

				// Render placeholder
				return '<div class="ws-form-divi-no-form-id"><h2>WS Form</h2><p>Select the form that you would like to use for this Divi module.</p></div>';
			}
		}
	}

	new ET_Builder_Module_WS_Form;
