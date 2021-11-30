<?php

	// Add form view
	// This script is only used if we can reliably load wp-load.php in SHORTINIT mode as determine by scripts in class-ws-form-public.php
	// It executes the add view method without loading all of WordPress to improve performance

	// Check super globals
	if(
		!isset($_SERVER) ||
		!isset($_SERVER['SCRIPT_FILENAME']) ||
		!isset($_SERVER['HTTP_REFERER']) ||
		!isset($_SERVER['HTTP_HOST']) ||
		!isset($_SERVER['REQUEST_METHOD']) ||
		(strtolower($_SERVER['REQUEST_METHOD']) !== 'post') ||
		!isset($_POST) ||
		!isset($_POST['wsffid'])
	) {
		exit;
	}

	// Check referrer
	if(strtolower(parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST)) != strtolower($_SERVER['HTTP_HOST'])) { exit; }

	// Read form ID
	$form_id =  intval($_POST['wsffid']);
	if($form_id <= 0) { exit; }

	// Load WordPress
	define('SHORTINIT', true);
	if(!defined('WS_FORM_AV_WORDPRESS_ROOT')) { define('WS_FORM_AV_WORDPRESS_ROOT', dirname($_SERVER['SCRIPT_FILENAME'], 5)); }

	$wp_load_paths = array(

		WS_FORM_AV_WORDPRESS_ROOT . '/wp-load.php',
		WS_FORM_AV_WORDPRESS_ROOT . '/.wordpress/wp-load.php',	// e.g. FlyWheel
	);

	$wp_load_success = false;

	foreach($wp_load_paths as $wp_load_path) {

		if(file_exists($wp_load_path)) {

			require_once $wp_load_path;
			$wp_load_success = true;
			break;
		}
	}

	if(!$wp_load_success) {

		error('Unable to find wp-load.php');
	}

	// Required definitions
	define('WS_FORM_IDENTIFIER', 'ws_form');
	define('WS_FORM_DB_TABLE_PREFIX', 'wsf_');

	// Dummy function
	function __($m) { return $m; }

	// Load stats class
	if(!defined('WS_FORM_AV_PLUGIN_ROOT')) { define('WS_FORM_AV_PLUGIN_ROOT', dirname($_SERVER['SCRIPT_FILENAME'], 2)); }
	if(!file_exists(WS_FORM_AV_PLUGIN_ROOT . '/includes/class-ws-form-common.php')) { exit; }
	require_once WS_FORM_AV_PLUGIN_ROOT . '/includes/class-ws-form-common.php';
	if(!file_exists(WS_FORM_AV_PLUGIN_ROOT . '/includes/core/class-ws-form-core.php')) { exit; }
	require_once WS_FORM_AV_PLUGIN_ROOT . '/includes/core/class-ws-form-core.php';
	if(!file_exists(WS_FORM_AV_PLUGIN_ROOT . '/includes/core/class-ws-form-form-stat.php')) { exit; }
	require_once WS_FORM_AV_PLUGIN_ROOT . '/includes/core/class-ws-form-form-stat.php';

	// Log view
	$ws_form_form_stat = new WS_Form_Form_Stat();
	$ws_form_form_stat->form_id = $form_id;
	$ws_form_form_stat->db_add_view();

	// X-Robots-Tag header
	header('X-Robots-Tag: noindex, nofollow');

	// JSON response
	header('Content-Type: application/json');
	echo json_encode(array('error' => false));
	exit;

	function error($error_message) {

		// JSON response
		header('Content-Type: application/json');
		echo json_encode(array('error' => true, 'error_message' => $error_message));
		exit;
	}
