<?php 

	add_action('plugins_loaded', function () {

		if(class_exists('OxyEl')) {

			try {

				include_once 'class-ws-form-oxygen.php';
				include_once 'elements/class-oxyel-ws-form-form.php';

			} catch (Exception $e) {}
		}
	});
