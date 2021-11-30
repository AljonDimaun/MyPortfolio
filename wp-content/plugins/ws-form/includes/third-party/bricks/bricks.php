<?php 

	add_action('init', function () {

		if(class_exists('\Bricks\Elements')) {

			try {

				// i18n category title
				add_filter('bricks/builder/i18n', function($i18n) {

					$i18n['ws-form'] = WS_FORM_NAME_PRESENTABLE;
					return $i18n;
				});

				// Bricks iframe
				$bricks_iframe = (

					(function_exists('bricks_is_builder_preview') && bricks_is_builder_preview()) ||
					(function_exists('bricks_is_builder_iframe') && bricks_is_builder_iframe())
				);

				// Builder preview enqueues
				if($bricks_iframe) {

					// Create public instance
					$ws_form_public = new WS_Form_Public();

					// Set visual builder scripts to enqueue
					do_action('wsf_enqueue_visual_builder');

					// Enqueue scripts
					$ws_form_public->enqueue();

					// Add public footer to speed up loading of config
					$ws_form_public->wsf_form_json[0] = true;
					add_action('admin_footer', array($ws_form_public, 'wp_footer'));
				}

				// Register element
				\Bricks\Elements::register_element(__DIR__ . '/elements/class-bricks-ws-form-form.php');

			} catch (Exception $e) {}
		}
	}, 11);
