<?php

	class OxyEl_WS_Form_Form extends OxyEl {

		public $slug = 'wsform_form';

		public $js_added = false;

		public $is_oxygen_iframe = false;
		public $is_oxygen_element = false;

		public function custom_init() {

			$this->is_oxygen_iframe = (

				isset($_REQUEST) &&					// phpcs:ignore
				isset($_REQUEST['oxygen_iframe'])	// phpcs:ignore
			);

			$this->is_oxygen_element = (

				isset($_REQUEST) &&											// phpcs:ignore
				isset($_REQUEST['action']) &&								// phpcs:ignore
				($_REQUEST['action'] === 'oxy_render_oxy-' . $this->slug)	// phpcs:ignore
			);
		}

		public function name() {

			return __('Form', 'ws-form');
		}

		public function slug() {

			return $this->slug;
		}

		public function icon() {

			return WS_FORM_PLUGIN_DIR_URL . 'includes/third-party/oxygen/icons/form.svg';
		}

		public function button_place() {

			return 'wsform_section::other';
		}

		public function init() {

			if($this->is_oxygen_iframe) {

				// Enqueue scripts
				add_action('wp_enqueue_scripts', function() {

					// Create public instance
					$ws_form_public = new WS_Form_Public();

					// Set visual builder scripts to enqueue
					do_action('wsf_enqueue_visual_builder');

					// Enqueue scripts
					$ws_form_public->enqueue();

					// Add public footer to speed up loading of config
					$ws_form_public->wsf_form_json[0] = true;
					add_action('admin_footer', array($ws_form_public, 'wp_footer'));

				}, 1000000);
			}
		}

		public function controls() {

			// Form selector	        
			$this->addOptionControl([

				'type' => 'dropdown',  
				'name' => __('Form', 'ws-form'),
				'slug' => 'wsf_form_id',
				'value' => WS_Form_Common::get_forms_array()
			]);

			// Custom ID
			$this->addOptionControl([

				'type' => 'textfield',  
				'name' => __('ID (Optional)', 'ws-form'),
				'slug' => 'wsf_form_element_id'
			]);
		}

		public function render($options, $defaults, $content) {

			// Read options
			$form_id = intval(isset($options['wsf_form_id']) ? $options['wsf_form_id'] : 0);
			$form_element_id = isset($options['wsf_form_element_id']) ? $options['wsf_form_element_id'] : '';

			if($form_id === 0) {

				if($this->is_oxygen_element) {
?>
<div class="wsf_oxygen_no_form_id">
<?php echo WS_Form_Config::get_logo_svg(); ?>
<p>Please select a form, then click 'Apply Params'.</p>
</div>
<style>

	.wsf_oxygen_no_form_id {
		text-align: center;
	}

	.wsf_oxygen_no_form_id svg {
		width: 340px;
	}

	.wsf_oxygen_no_form_id p {
		margin: 0;
	}

</style>
<?php
				}

			} else {

				if($this->is_oxygen_element) {

					// Disable debug
					add_filter('wsf_debug_enabled', function($debug_render) { return false; }, 10, 1);

					add_filter('wsf_public_enqueue', function($enqueue) {

						if(
							isset($_REQUEST) &&										// phpcs:ignore
							isset($_REQUEST['action']) &&							// phpcs:ignore
							($_REQUEST['action'] === 'oxy_render_oxy-wsform_form')	// phpcs:ignore

						) { return false; } else { return $enqueue; }
					});
				}

				$shortcode = sprintf('[ws_form id="%u"%s%s]', $form_id, ($form_element_id != '') ? sprintf(' element_id="%s"', esc_attr($form_element_id)) : '', ($this->is_oxygen_element ? ' visual_builder="true"' : ''));

				echo do_shortcode($shortcode);

				if($this->is_oxygen_element) {
?>
<script>

	wsf_form_init();

</script>
<?php
				}
			}
		}
	}

	new OxyEl_WS_Form_Form();