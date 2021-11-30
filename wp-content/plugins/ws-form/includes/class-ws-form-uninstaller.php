<?php

	// Fired during plugin uninstall
	class WS_Form_Uninstaller {

		public static function uninstall() {

			// Delete options
			$uninstall_options = WS_Form_Common::option_get('uninstall_options', false);
			if($uninstall_options) {

				delete_option(WS_FORM_IDENTIFIER);
				delete_site_option(WS_FORM_IDENTIFIER);

				// Delete submission hidden column meta
				$ws_form_form = New WS_Form_Form();
				$forms = $ws_form_form->db_read_all('', '', '', '', '', false);
				foreach($forms as $form) {

					delete_user_option(get_current_user_id(), sprintf('managews-form_page_ws-form-submitcolumnshidden-%u', $form['id']), !is_multisite());
				}
			}

			// Delete database tables
			$uninstall_database = WS_Form_Common::option_get('uninstall_database', false);
			if($uninstall_database) {

				// Drop WS Form tables
				global $wpdb;

				// Get table prefix
				$table_prefix = $wpdb->prefix . WS_FORM_DB_TABLE_PREFIX;

				// Tables to delete
				$tables = array('form', 'form_meta', 'form_stat', 'group', 'group_meta', 'section', 'section_meta', 'field', 'field_meta', 'submit', 'submit_meta');

				// Run through each table and delete
				foreach($tables as $table_name) {

					$sql = sprintf("DROP TABLE IF EXISTS %s%s;", $table_prefix, $table_name);
					$wpdb->query($sql);
				}
			}
		}
	}
