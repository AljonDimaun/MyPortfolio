<?php

	add_action('plugins_loaded', function() {

		if(
			isset($_GET) && isset($_GET['elementor-preview'])	// phpcs:ignore
		) {

			// Disable debug
			add_filter('wsf_debug_enabled', function($debug_render) { return false; }, 10, 1);

			// Enqueue all WS Form scripts
			add_action('wp_enqueue_scripts', function() { do_action('wsf_enqueue_core'); });
		}
	});

	add_action('elementor/widgets/widgets_registered', function($widgets_manager) {

		// Unregister normal WordPress widget
		$widgets_manager->unregister_widget_type('wp-widget-ws_form_widget');

		// Include WS Form widget class
		include 'class-elementor-ws-form-widget.php';

		// Initiate WS Form widget
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type(new \Elementor_WS_Form_Widget());
	});
