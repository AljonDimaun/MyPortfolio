<?php
	/**
	 * WS Form LITE
	 *
	 * @link              https://wsform.com/
	 * @since             1.0.0
	 * @package           WS_Form
	 *
	 * @wordpress-plugin
	 * Plugin Name:       WS Form LITE
	 * Plugin URI:        https://wsform.com/
	 * Description:       Build Better WordPress Forms
	 * Version:           1.8.147
	 * Author:            WS Form
	 * Author URI:        https://wsform.com/
	 * License:           GPL-2.0+
	 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
	 * Text Domain:       ws-form
	 * Domain Path:       /languages
	 */

	// If this file is called directly, abort.
	if( !defined('WPINC') ) {
		die;
	}

	// Load plugin.php
	if( !function_exists('is_plugin_active') ) {

		include_once(ABSPATH . 'wp-admin/includes/plugin.php');
	}

	if(!is_plugin_active('ws-form-pro/ws-form.php')) {

		// Constants
		define('WS_FORM_NAME', 'ws-form');
		define('WS_FORM_VERSION', '1.8.147');
		define('WS_FORM_NAME_PRESENTABLE', 'WS Form LITE');
		define('WS_FORM_EDITION', 'basic');
		define('WS_FORM_PLUGIN_BASENAME_COUNTERPART', 'ws-form-pro/ws-form.php');
		define('WS_FORM_POST_NONCE_FIELD_NAME', 'wsf_nonce');
		define('WS_FORM_POST_NONCE_ACTION_NAME', 'wsf_post');
		define('WS_FORM_UPLOAD_DIR', 'ws-form');
		define('WS_FORM_IDENTIFIER', 'ws_form');
		define('WS_FORM_DB_TABLE_PREFIX', 'wsf_');
		define('WS_FORM_SHORTCODE', 'ws_form');
		define('WS_FORM_WIDGET', 'ws_form_widget');
		define('WS_FORM_CAPABILITY_PREFIX', 'wsf_');
		define('WS_FORM_USER_REQUEST_IDENTIFIER', 'ws-form');
		define('WS_FORM_AUTHOR', 'Westguard Solutions');

		define('WS_FORM_DEFAULT_FORM_NAME', __('New Form', 'ws-form'));
		define('WS_FORM_DEFAULT_GROUP_NAME', __('Tab', 'ws-form'));
		define('WS_FORM_DEFAULT_SECTION_NAME', __('Section', 'ws-form'));
		define('WS_FORM_DEFAULT_FIELD_NAME', __('Field', 'ws-form'));
		define('WS_FORM_DEFAULT_FRAMEWORK', 'ws-form');
		define('WS_FORM_DEFAULT_MODE', 'basic');

		define('WS_FORM_RESTFUL_NAMESPACE', 'ws-form/v1');

		define('WS_FORM_STATUS_FORM', 'draft,publish,trash');
		define('WS_FORM_STATUS_SUBMIT', 'draft,publish,error,spam,trash');

		define('WS_FORM_COMPATIBILITY_NAME', 'caniuse.com');
		define('WS_FORM_COMPATIBILITY_URL', 'https://caniuse.com');
		define('WS_FORM_COMPATIBILITY_MASK', 'https://caniuse.com/#feat=#compatibility_id');

		define('WS_FORM_MODES', 'basic,advanced');

		define('WS_FORM_SPAM_LEVEL_MAX', 100);		// 0 = Not spam, 100 = Spam

		define('WS_FORM_FORM_LABEL_MAX_LENGTH', 1024);

		define('WS_FORM_GROUP_LABEL_MAX_LENGTH', 1024);

		define('WS_FORM_SECTION_REPEATABLE_DELIMITER_SECTION', ',');
		define('WS_FORM_SECTION_REPEATABLE_DELIMITER_ROW', ';');
		define('WS_FORM_SECTION_REPEATABLE_DELIMITER_SUBMIT', '<br />');
		define('WS_FORM_SECTION_LABEL_MAX_LENGTH', 1024);

		define('WS_FORM_FIELD_PREFIX', 'field_');
		define('WS_FORM_FIELD_PREFIX_PUBLIC_', 'wsf_field_');
		define('WS_FORM_FIELD_INVALID_FEEDBACK', __('Please provide a valid #label_lowercase.', 'ws-form'));
		define('WS_FORM_FIELD_LABEL_MAX_LENGTH', 1024);

		define('WS_FORM_PLUGIN_ROOT_FILE', __FILE__);
		define('WS_FORM_PLUGIN_DIR_PATH', plugin_dir_path(__FILE__));
		define('WS_FORM_PLUGIN_DIR_URL', plugin_dir_url(__FILE__));
		define('WS_FORM_PLUGIN_BASENAME', plugin_basename(__FILE__));

		define('WS_FORM_CSS_CACHE_DURATION_DEFAULT', 31536000);
		define('WS_FORM_CSS_FILE_PATH', 'css/public');
		define('WS_FORM_CSS_SKIN_DEFAULT', 'ws_form');

		define('WS_FORM_SETTINGS_IMAGE_PREVIEW_SIZE', 'full');

		define('WS_FORM_MIN_VERSION_WORDPRESS', '5.3.0');
		define('WS_FORM_MIN_VERSION_PHP', '7.2');
		define('WS_FORM_MIN_VERSION_MYSQL', '5.6');
		define('WS_FORM_MIN_INPUT_VARS', 100);
		define('WS_FORM_MIN_MYSQL_MAX_ALLOWED_PACKET', 4194304);

		define('WS_FORM_API_CALL_TIMEOUT', 10);
		define('WS_FORM_API_CALL_VERIFY_SSL', true);

		define('WS_FORM_REVIEW_NAG_DURATION', 14);

		define('WS_FORM_DATA_SOURCE_SCHEDULE_ID_PREFIX', 'wsf_');
		define('WS_FORM_DATA_SOURCE_SCHEDULE_HOOK', 'ws_form_wp_cron_data_source');

		define('WS_FORM_UTF32_BIG_ENDIAN_BOM'   , chr(0x00) . chr(0x00) . chr(0xFE) . chr(0xFF));
		define('WS_FORM_UTF32_LITTLE_ENDIAN_BOM', chr(0xFF) . chr(0xFE) . chr(0x00) . chr(0x00));
		define('WS_FORM_UTF16_BIG_ENDIAN_BOM'   , chr(0xFE) . chr(0xFF));
		define('WS_FORM_UTF16_LITTLE_ENDIAN_BOM', chr(0xFF) . chr(0xFE));
		define('WS_FORM_UTF8_BOM'               , chr(0xEF) . chr(0xBB) . chr(0xBF));

		define('WS_FORM_TEMPLATE_SVG_WIDTH_FORM', 140);
		define('WS_FORM_TEMPLATE_SVG_HEIGHT_FORM', 180);
		define('WS_FORM_TEMPLATE_SVG_WIDTH_SECTION', 140);
		define('WS_FORM_TEMPLATE_SVG_HEIGHT_SECTION', 85);
		define('WS_FORM_TEMPLATE_CHECKSUM_REPAIR', false);

		define('WS_FORM_SUBMIT_EXPORT_PAGE_SIZE', 500);
		define('WS_FORM_SUBMIT_EXPORT_FILE_SIZE_ZIP', 524288);
		define('WS_FORM_SUBMIT_EXPORT_TMP_DIR', 'submit/export/tmp');

		define('WS_FORM_DROPZONEJS_IMAGE_SIZE', 'thumbnail');
	}

	function activate_ws_form() {

		if(is_plugin_active('ws-form-pro/ws-form.php')) {

			deactivate_plugins('ws-form-pro/ws-form.php');
		}

		require_once WS_FORM_PLUGIN_DIR_PATH . 'includes/class-ws-form-activator.php';
		WS_Form_Activator::activate();
	}

	// Deactivate
	function deactivate_ws_form() {

		require_once WS_FORM_PLUGIN_DIR_PATH . 'includes/class-ws-form-deactivator.php';
		WS_Form_Deactivator::deactivate();
	}

	// Uninstall
	function uninstall_ws_form() {

		require_once WS_FORM_PLUGIN_DIR_PATH . 'includes/class-ws-form-uninstaller.php';
		WS_Form_Uninstaller::uninstall();
	}

	// Register hooks for plugin activation, deactivation and uninstall
	register_activation_hook(__FILE__, 'activate_ws_form');
	register_deactivation_hook(__FILE__, 'deactivate_ws_form');
	register_uninstall_hook(__FILE__, 'uninstall_ws_form');

	if(!is_plugin_active('ws-form-pro/ws-form.php')) {

		require WS_FORM_PLUGIN_DIR_PATH. 'includes/class-ws-form.php';

		function run_ws_form() {

			$plugin = new WS_Form();
			$plugin->run();
		}
		run_ws_form();
	}
