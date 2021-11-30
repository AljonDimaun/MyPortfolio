<?php

	class Bricks_WS_Form_Form extends \Bricks\Element {

		// Element properties
		public $category     = 'ws-form';
		public $name         = 'ws-form-form';
		public $icon         = 'ti-view-list';
		public $css_selector = '';

		public function __construct($element = null) {

			if(bricks_is_builder()) {

				$this->scripts = ['wsf_form_init'];
			}

			parent::__construct($element);
		}

		public function get_label() {

			return __('Form', 'ws-form');
		}

		public function set_controls() {

			// Form ID
			$this->controls['form-id'] = [

				'tab' => 'content',
				'label' => __( 'Form', 'ws-form' ),
				'type' => 'select', 
				'options' => WS_Form_Common::get_forms_array(),
				'placeholder' => __( 'Select form', 'ws-form' ),
			];

			// Form element ID
			$this->controls['form-element-id'] = [

				'tab' => 'content',
				'label' => __( 'ID (Optional)', 'ws-form' ),
				'type' => 'text'
			];
		}

		public function render() {

			// Get form ID
			$form_id = intval(isset($this->settings['form-id']) ? $this->settings['form-id'] : '');

			// Get form element ID
			$form_element_id = isset($this->settings['form-element-id']) ? $this->settings['form-element-id'] : '';

			if($form_id > 0) {

				// Bricks iframe
				$bricks_iframe = (

					(function_exists('bricks_is_builder_preview') && bricks_is_builder_preview()) ||
					(function_exists('bricks_is_builder_iframe') && bricks_is_builder_iframe())
				);

				// Show shortcode
				$shortcode = sprintf('[ws_form id="%u"%s%s]', $form_id, ($form_element_id != '') ? sprintf(' element_id="%s"', esc_attr($form_element_id)) : '', (($bricks_iframe || bricks_is_ajax_call()) ? ' visual_builder="true"' : ''));
				echo do_shortcode($shortcode);

			} else {

				// Show placeholder
				return $this->render_element_placeholder([
					'icon-class' => $this->icon,
					'text'       => esc_html__( 'No form selected.', 'ws-form' ),
				]);
			}
		}
	}
