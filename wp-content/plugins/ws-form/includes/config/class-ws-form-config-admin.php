<?php

	class WS_Form_Config_Admin extends WS_Form_Config {

		// Caches
		public static $calc = false;
		public static $settings_form_admin = false;
		public static $parse_variable_help = array();

		// Configuration - Settings - Admin
		public static function get_settings_form_admin() {

			// Conditional
			require_once 'class-ws-form-config-conditional.php';
			$ws_form_config_conditional = new WS_Form_Config_Conditional();

			// Check cache
			if(self::$settings_form_admin !== false) { return self::$settings_form_admin; }

			$settings_form_admin = array(

				'sidebars'	=> array(

					// Toolbox
					'toolbox'	=> array(

						'label'		=>	__('Toolbox', 'ws-form'),
						'icon'		=>	'tools',
						'buttons'	=>	array(

							array(

								'label' 	=> __('Close', 'ws-form'),
								'action' 	=> 'wsf-sidebar-cancel'
							)
						),
						'static'	=>	true,
						'nav'		=>	true,
						'expand'	=>	false,
						'logo'		=>	sprintf('<a href="https://wsform.com/?utm_source=ws_form%s&utm_medium=sidebar" target="_blank" class="wsf-sidebar-logo">%s</a>', ((WS_FORM_EDITION == 'pro') ? '_pro' : ''), WS_Form_Config::get_logo_svg('#fff', '#fff')),

						'meta'		=>	array(

							'fieldsets'	=>	array(

								'field-selector'	=>	array(

									'label'		=> __('Fields', 'ws-form'),
									'meta_keys'	=>	array('field_select')
								),

								'section-selector'	=>	array(

									'label'		=> __('Sections', 'ws-form'),
									'meta_keys'	=>	array('section_select')
								),

								'form-history'	=>	array(

									'label'		=>	__('Undo', 'ws-form'),
									'meta_keys'	=>	array('form_history')
								)
							)
						)
					),

					// Conditional
					'conditional'	=> array(

						'label'		=>	__('Conditional Logic', 'ws-form'),
						'icon'		=>	'conditional',
						'nav'		=>	true,
						'url'		=>	'/knowledgebase/conditional-logic/',
					),

					// Actions
					'action'	=> array(

						'label'		=>	__('Actions', 'ws-form'),
						'icon'		=>	'actions',
						'buttons'	=>	true,
						'static'	=>	false,
						'nav'		=>	true,
						'expand'	=>	true,
						'kb_url'	=>	'/knowledgebase_category/actions/',

						// When an action is fired...
						'events'	=>	array(

							'submit'	=>	array('label' => __('Form Submitted', 'ws-form'))
						),

						'meta'		=>	array(

							'fieldsets'	=>	array(

								'action'	=>	array(

									'meta_keys'	=>	array('action')
								)
							)
						),

						'actions_pro' => array(

							__('Conversion Tracking (PRO)', 'ws-form'),
							__('Run WordPress Hook (PRO)', 'ws-form'),
							__('Run JavaScript (PRO)', 'ws-form'),
							__('Push to Custom API (PRO)', 'ws-form'),
						)
					),

					// Support
					'support'	=> array(

						'label'		=>	__('Support', 'ws-form'),
						'icon'		=>	'support',
						'buttons'	=>	array(

							array(

								'label' => __('Close', 'ws-form'),
								'action' => 'wsf-sidebar-cancel'
							)
						),
						'static'	=>	true,
						'nav'		=>	true,
						'expand'	=>	true,

						'meta'		=>	array(

							'fieldsets'	=>	array(

								'knowledgebase'	=>	array(

									'label'		=> __('Knowledge Base', 'ws-form'),
									'meta_keys'	=>	array('knowledgebase')
								),

								'contact'		=>	array(

									'label'		=>	__('Contact', 'ws-form'),
									'meta_keys'	=>	array('contact_first_name', 'contact_last_name', 'contact_email', 'contact_inquiry', 'contact_support_search_results', 'contact_push_form', 'contact_push_system', 'contact_gdpr', 'contact_submit')
								)
							)
						)
					),

					// Form
					'form' => array (

						'label'		=>	__('Form Settings', 'ws-form'),
						'icon'		=>	'settings',
						'buttons'	=>	true,
						'static'	=>	false,
						'nav'		=>	true,
						'expand'	=>	true,

						'meta' => array (

							'fieldsets'			=> array(

								// Tab: Basic
								'basic'	=> array(

									'label'		=>	__('Basic', 'ws-form'),

									'meta_keys'	=>	array('label_render_off'),

									'fieldsets'	=>	array(

										array(
											'label'		=>	__('Form', 'ws-form'),
											'meta_keys'	=> array('submit_on_enter', 'submit_lock', 'submit_unlock', 'submit_reload', 'form_action')
										),

										array(
											'label'		=>	__('Tabs', 'ws-form'),
											'meta_keys'	=> array('cookie_tab_index', 'tab_validation', 'tabs_hide')
										),

										array(
											'label'		=>	__('Errors', 'ws-form'),
											'meta_keys'	=> array('submit_show_errors', 'invalid_field_focus', 'error_type', 'error_method', 'error_clear', 'error_scroll_top', 'error_scroll_top_offset', 'error_scroll_top_duration', 'error_form_hide', 'error_duration', 'error_message_hide', 'error_form_show')
										)
									)
								),

								// Tab: Advanced
								'styling'	=> array(

									'label'			=>	__('Styling', 'ws-form'),

									'fieldsets'	=>	array(

										array(
											'label'		=>	__('Fields', 'ws-form'),
											'meta_keys'	=> array('label_position_form', 'label_column_width_form')
										),

										array(
											'label'		=>	__('Required Fields', 'ws-form'),
											'meta_keys'	=> array('label_required', 'label_mask_required')
										),

										array(
											'label'		=>	__('Classes', 'ws-form'),
											'meta_keys'	=> array('class_form_wrapper', 'class_tabs_wrapper', 'class_group_wrapper', 'class_section_wrapper', 'class_field_wrapper', 'class_field')
										),

										array(
											'label'		=>	__('Heading HTML Masks', 'ws-form'),
											'meta_keys'	=> array('label_mask_form', 'label_mask_group', 'label_mask_section')
										)
									)
								),

								// Tab: Spam
								'spam'	=> array(

									'label'		=>	__('Spam', 'ws-form'),

									'fieldsets'	=>	array(

										array(
											'label'			=>	__('Honeypot', 'ws-form'),
											'meta_keys'	=> array('honeypot')
										),

										array(
											'label'			=>	__('Settings', 'ws-form'),
											'meta_keys'	=> array('spam_threshold')
										)
									)
								),
							),

							// Hidden meta data used to render admin interface
							'hidden'	=> array(

								'meta_keys'	=>	array('breakpoint', 'tab_index', 'action')
							)
						)
					),

					// Groups
					'group' => array(

						'label'		=>	__('Group', 'ws-form'),
						'icon'		=>	'group',
						'buttons'	=>	true,
						'static'	=>	false,
						'nav'			=>	false,
						'expand'	=>	true,

						'meta' => array (

							'fieldsets'			=> array(

								// Tab: Basic
								'basic' 		=> array(

									'label'		=>	__('Basic', 'ws-form'),
									'meta_keys'	=>	array('label_render_off', 'hidden')
								),

								// Tab: Advanced
								'advanced'		=> array(

									'label'		=>	__('Advanced', 'ws-form'),

									'fieldsets'	=>	array(

										array(
											'label'		=>	__('Classes', 'ws-form'),
											'meta_keys'	=>	array('class_group_wrapper')
										),

										array(
											'label'		=>	__('Restrictions', 'ws-form'),
											'meta_keys'	=>	array('group_user_status', 'group_user_roles', 'group_user_capabilities')
										)
									)
								)
							)
						)
					),

					// Sections
					'section' => array(

						'label'		=>	__('Section', 'ws-form'),
						'icon'		=>	'section',
						'buttons'	=>	true,
						'static'	=>	false,
						'nav'		=>	false,
						'expand'	=>	true,

						'meta' => array (

							'fieldsets'			=> array(

								// Tab: Basic
								'basic' 		=> array(

									'label'			=>	__('Basic', 'ws-form'),
									'meta_keys'	=>	array('label_render_off', 'hidden_section'),
								),

								// Tab: Advanced
								'advanced'		=> array(

									'label'			=>	__('Advanced', 'ws-form'),
									'fieldsets'	=>	array(

										array(
											'label'		=>	__('Style', 'ws-form'),
											'meta_keys'	=>	array('class_single_vertical_align')
										),
										array(
											'label'			=>	__('Classes', 'ws-form'),
											'meta_keys'	=> array('class_section_wrapper')
										),

										array(
											'label'			=>	__('Restrictions', 'ws-form'),
											'meta_keys'	=> array('disabled_section', 'section_user_status', 'section_user_roles', 'section_user_capabilities')
										),
										array(
											'label'		=>	__('Breakpoints', 'ws-form'),
											'meta_keys'	=> array('breakpoint_sizes'),
											'class'		=>	array('wsf-fieldset-panel')
										)
									)
								)
							)
						)
					),

					// Fields
					'field' => array(

						'buttons'	=>	true,
						'static'	=>	false,
						'nav'			=>	false,
						'expand'	=>	true,
					)
				),

				'group' => array(

					'buttons' =>	array(

						array('name' => __('Tab Settings', 'ws-form'), 'method' => 'edit'),
						array('name' => __('Delete Tab', 'ws-form'), 'method' => 'delete'),
						array('name' => __('Clone Tab', 'ws-form'), 'method' => 'clone'),
						array('name' => __('Export Tab', 'ws-form'), 'method' => 'download'),
						array('name' => __('Import Tab', 'ws-form'), 'method' => 'upload'),
						array('name' => __('Add to My Sections', 'ws-form'), 'method' => 'template_add')
					),
				),

				'section' => array(

					'buttons' =>	array(

						array('name' => __('Section Settings', 'ws-form'), 'method' => 'edit'),
						array('name' => __('Delete Section', 'ws-form'), 'method' => 'delete'),
						array('name' => __('Clone Section', 'ws-form'), 'method' => 'clone'),
						array('name' => __('Export Section', 'ws-form'), 'method' => 'download'),
						array('name' => __('Import Section', 'ws-form'), 'method' => 'upload'),
						array('name' => __('Add to My Sections', 'ws-form'), 'method' => 'template_add')
					),
				),

				'field' => array(

					'buttons' =>	array(

						array('name' => __('Field Settings', 'ws-form'), 'method' => 'edit'),
						array('name' => __('Delete Field', 'ws-form'), 'method' => 'delete'),
						array('name' => __('Clone Field', 'ws-form'), 'method' => 'clone')
					),
				),

				// Data grid
				'data_grid' => array(

					'rows_per_page_options' => array(

						5	=>	'5',
						10	=>	'10',
						25	=>	'25',
						50	=>	'50',
						100	=>	'100',
						150	=>	'150',
						200	=>	'200',
						250	=>	'250',
						500	=>	'500'
					)
				),

				// History
				'history'	=> array(

					'initial'	=> __('Initial form', 'ws-form'),

					'method' 	=> array(

						// All past tense
						'get'				=> __('Read', 'ws-form'),
						'put'				=> __('Updated', 'ws-form'),
						'put_clone'			=> __('Cloned', 'ws-form'),
						'put_resize'		=> __('Resized', 'ws-form'),
						'put_offset'		=> __('Offset', 'ws-form'),
						'put_sort_index'	=> __('Moved', 'ws-form'),
						'put_reset'			=> __('Reset', 'ws-form'),
						'post'				=> __('Added', 'ws-form'),
						'post_upload_json'	=> __('Uploaded', 'ws-form'),
						'delete'			=> __('Deleted', 'ws-form'),
					),

					'object'	=> array(

						'form'		=> __('form', 'ws-form'),
						'group'		=> __('group', 'ws-form'),
						'section'	=> __('section', 'ws-form'),
						'field'		=> __('field', 'ws-form')
					)
				),

				// Icons
				'icons'		=> array(

					'actions'			=> self::get_icon_16_svg('actions'),
					'asterisk'			=> self::get_icon_16_svg('asterisk'),
					'calc'				=> self::get_icon_16_svg('calc'),
					'check'				=> self::get_icon_16_svg('check'),
					'clone'				=> self::get_icon_16_svg('clone'),
					'conditional'		=> self::get_icon_16_svg('conditional'),
					'contract'			=> self::get_icon_16_svg('contract'),
					'default'			=> self::get_icon_16_svg(),
					'delete'			=> self::get_icon_16_svg('delete'),
					'delete-circle'		=> self::get_icon_16_svg('delete-circle'),
					'disabled'			=> self::get_icon_16_svg('disabled'),
					'download'			=> self::get_icon_16_svg('download'),
					'edit'				=> self::get_icon_16_svg('edit'),
					'expand'			=> self::get_icon_16_svg('expand'),
					'file-picture'		=> self::get_icon_16_svg('file-picture'),

					'hidden'			=> self::get_icon_16_svg('hidden'),
					'info-circle'		=> self::get_icon_16_svg('info-circle'),
					'first'				=> self::get_icon_16_svg('first'),
					'form'				=> self::get_icon_16_svg('settings'),
					'group'				=> self::get_icon_16_svg('group'),
					'last'				=> self::get_icon_16_svg('last'),
					'markup-circle'		=> self::get_icon_16_svg('markup-circle'),
					'menu'				=> self::get_icon_16_svg('menu'),
					'next'				=> self::get_icon_16_svg('next'),
					'number'			=> self::get_icon_16_svg('number'),
					'picture'			=> self::get_icon_16_svg('picture'),
					'plus'				=> self::get_icon_16_svg('plus'),
					'plus-circle'		=> self::get_icon_16_svg('plus-circle'),
					'previous'			=> self::get_icon_16_svg('previous'),
					'question-circle'	=> self::get_icon_16_svg('question-circle'),
					'readonly'			=> self::get_icon_16_svg('readonly'),
					'redo'				=> self::get_icon_16_svg('redo'),
					'section'			=> self::get_icon_16_svg('section'),
					'settings'			=> self::get_icon_16_svg('settings'),
					'sort'				=> self::get_icon_16_svg('sort'),
					'table'				=> self::get_icon_16_svg('table'),
					'tools'				=> self::get_icon_16_svg('tools'),
					'undo'				=> self::get_icon_16_svg('undo'),
					'upload'			=> self::get_icon_16_svg('upload'),
					'visible'			=> self::get_icon_16_svg('visible'),
					'warning'			=> self::get_icon_16_svg('warning'),
				),

				// Language
				'language'	=> array(

					// Custom
					'custom'		=>	'%s',

					// Objects
					'form'				=>	__('Form', 'ws-form'),
					'forms'				=>	__('Forms', 'ws-form'),
					'group'				=>	__('Tab', 'ws-form'),
					'groups'			=>	__('Tabs', 'ws-form'),
					'section'			=>	__('Section', 'ws-form'),
					'sections'			=>	__('Sections', 'ws-form'),
					'field'				=>	__('Field', 'ws-form'),
					'fields'			=>	__('Fields', 'ws-form'),
					'action'			=>	__('Action', 'ws-form'),
					'actions'			=>	__('Actions', 'ws-form'),
					'submission'		=>	__('Submission', 'ws-form'),
					'user'				=>	__('User', 'ws-form'),
					'conditional'		=>	__('Conditional Logic', 'ws-form'),
					'id'				=>	__('ID', 'ws-form'),
					'unknown'			=>	__('Unknown', 'ws-form'),

					// Buttons
					'add_group'			=>	__('Add Tab', 'ws-form'),
					'add_section'		=>	__('Add Section', 'ws-form'),
					'save'				=>	__('Save', 'ws-form'),
					'save_and_close'	=>	__('Save & Close', 'ws-form'),
					'delete'			=>	__('Delete', 'ws-form'),
					'trash'				=>	__('Trash', 'ws-form'),
					'clone'				=>	__('Clone', 'ws-form'),
					'cancel'			=>	__('Cancel', 'ws-form'),
					'print'				=>	__('Print', 'ws-form'),
					'edit'				=>	__('Edit', 'ws-form'),
					'previous'			=>	__('Previous', 'ws-form'),
					'next'				=>	__('Next', 'ws-form'),
					'repost'			=>	__('Re-Run', 'ws-form'),
					'default'			=>	__('Default', 'ws-form'),
					'variables'			=>	__('Variables', 'ws-form'),
					'select_list'		=>	__('Insert', 'ws-form'),
					'calc'				=>	__('Calculate', 'ws-form'),
					'reset'				=>	__('Reset', 'ws-form'),
					'close'				=>	__('Close', 'ws-form'),
					'required'			=>	__('Required', 'ws-form'),
					'required_setting'	=>	__('Required Setting', 'ws-form'),
					'hidden'			=>	__('Hidden', 'ws-form'),
					'disabled'			=>	__('Disabled', 'ws-form'),
					'readonly'			=>	__('Read Only', 'ws-form'),
					'saving'			=>	__('Saving', 'ws-form'),
					'clipboard'			=>	__('Click to Copy', 'ws-form'),

					// Tutorial
					'intro_learn_more'	=>	__('Learn More', 'ws-form'),
					'intro_skip'		=>	__('Skip Tutorial', 'ws-form'),

					// Form statuses
					'draft'				=>	__('Draft', 'ws-form'),
					'publish'			=>	__('Published', 'ws-form'),

					// Uses constants because these are used by the API also
					'default_label_form'		=>	__(WS_FORM_DEFAULT_FORM_NAME, 'ws-form'),
					'default_label_group'		=>	__(WS_FORM_DEFAULT_GROUP_NAME, 'ws-form'),
					'default_label_section'		=>	__(WS_FORM_DEFAULT_SECTION_NAME, 'ws-form'),
					'default_label_field'		=>	__(WS_FORM_DEFAULT_FIELD_NAME, 'ws-form'),

					// Error messages
					'error_field_type_unknown'			=>	__('Unknown field type', 'ws-form'),
					'error_admin_max_width'				=>	__('admin_max_width not defined for breakpoint: %s.', 'ws-form'),
					'error_object'						=>	__('Unable to find object', 'ws-form'),
					'error_object_data'					=>	__('Unable to retrieve object data', 'ws-form'),
					'error_object_meta_value'			=>	__('Unable to retrieve object meta', 'ws-form'),
					'error_object_type'					=>	__('Unable to determine object type', 'ws-form'),
					'error_meta_key'					=>	__('Unknown meta_key: %s', 'ws-form'),
					'error_data_grid'					=>	__('Data grid not specified', 'ws-form'),
					'error_data_grid_groups'			=>	__('Data grid has no groups', 'ws-form'),
					'error_data_grid_default_group'		=>	__('Default group missing in meta type', 'ws-form'),
					'error_data_grid_columns'			=>	__('Data grid has no columns', 'ws-form'),
					'error_data_grid_rows_per_page'		=>	__('Data grid has no rows per page value', 'ws-form'),
					'error_data_grid_csv_no_data'		=>	__('No data to export', 'ws-form'),
					'error_data_grid_row_id'			=>	__('Data grid row has no ID', 'ws-form'),
					'error_timeout_codemirror'			=>	__('Timeout waiting for CodeMirror to load', 'ws-form'),
					'error_submit_export'				=>	__('Export error: %s', 'ws-form'),

					// Popover
					'confirm_group_delete'				=>	__('Are you sure you want to delete this tab?', 'ws-form'),
					'confirm_section_delete'			=>	__('Are you sure you want to delete this section?', 'ws-form'),
					'confirm_field_delete'				=>	__('Are you sure you want to delete this field?', 'ws-form'),
					'confirm_conditional_delete'		=>	__('Are you sure you want to delete this condition?', 'ws-form'),
					'confirm_action_delete'				=>	__('Are you sure you want to delete this action?', 'ws-form'),
					'confirm_action_repost'				=>	__('Are you sure you want to re-run this action?', 'ws-form'),
					'confirm_breakpoint_reset'			=>	__('Are you sure you want to reset the widths and offsets?', 'ws-form'),
					'confirm_orientation_breakpoint_reset'	=>	__('Are you sure you want to reset the widths?', 'ws-form'),
					'confirm_submit_delete'				=>	__('Are you sure you want to trash this submission', 'ws-form'),
					'confirm_data_grid_group_delete'	=>	__('Are you sure you want to delete this group?', 'ws-form'),
					'confirm_data_grid_column_delete'	=>	__('Are you sure you want to delete this column?', 'ws-form'),
					'confirm_section_template_delete'	=>	__('Are you sure you want to delete this section?', 'ws-form'),

					// Blanks
					'blank_section'						=>	__('Drag a section here', 'ws-form'),
					'blank_field'						=>	__('Drag a field here', 'ws-form'),

					// Compatibility
					'attribute_compatibility'			=>	__('Compatibility', 'ws-form'),
					'field_compatibility'				=>	__('Compatibility', 'ws-form'),
					'field_kb_url'						=>	__('Support', 'ws-form'),

					// Drop zones
					'drop_zone_form'					=>	__('Drop file to import', 'ws-form'),
					'drop_zone_section'					=>	__('Drop file to import', 'ws-form'),
					'drop_zone_data_grid'				=>	__('Drop file to import', 'ws-form'),

					// Section templates
					'section_selector_import'			=>	__('Import Section', 'ws-form'),
					'section_selector_drop_zone'		=>	sprintf(__('Drag a form JSON file here<br /><a href="%s" target="_blank">Learn more</a>', 'ws-form'), WS_Form_Common::get_plugin_website_url('/knowledgebase/section-library/', 'siderbar_toolbox')),
					'section_download'					=>	__('Export Section', 'ws-form'),
					'section_delete'					=>	__('Delete Section', 'ws-form'),

					// Data grids - Data sources
					'data_grid_data_source_error'			=>	__('Error retrieving data source', 'ws-form'),
					'data_grid_data_source_error_s'			=>	__('Error retrieving data source: %s', 'ws-form'),
					'data_grid_data_source_error_last'			=>	__('Error retrieving data source<br />%s', 'ws-form'),
					'data_grid_data_source_error_last_field'	=>	__('Field: %s', 'ws-form'),
					'data_grid_data_source_error_last_field_id'	=>	__('ID: %s', 'ws-form'),
					'data_grid_data_source_error_last_source'	=>	__('Data source: %s', 'ws-form'),
					'data_grid_data_source_error_last_date'		=>	__('Last attempt: %s', 'ws-form'),
					'data_grid_data_source_error_last_error'	=>	__('Error: %s', 'ws-form'),

					// Data grids - Groups
					'data_grid_settings'				=>	__('Settings', 'ws-form'),
					'data_grid_groups_label'			=>	__('Label', 'ws-form'),
					'data_grid_groups_label_render'		=>	__('Show Label', 'ws-form'),
					'data_grid_group_add'				=>	__('Add Group', 'ws-form'),
					'data_grid_group_label_default'		=>	__('Group', 'ws-form'),
					'data_grid_group_auto_group'		=>	__('Auto Group By', 'ws-form'),
					'data_grid_group_auto_group_select'	=>	__('Select...', 'ws-form'),
					'data_grid_group_disabled'			=>	__('Disabled', 'ws-form'),
					'data_grid_groups_group'			=>	__('Group These Values', 'ws-form'),
					'data_grid_group_delete'			=>	__('Delete Group', 'ws-form'),

					// Data grids - Columns
					'data_grid_column_add'				=>	__('Add Column', 'ws-form'),
					'data_grid_column_label_default'	=>	__('Column', 'ws-form'),
					'data_grid_column_delete'			=>	__('Delete Column', 'ws-form'),

					// Data grids - Rows
					'data_grid_row_add'					=>	__('Add Row', 'ws-form'),
					'data_grid_row_sort'				=>	__('Sort Row', 'ws-form'),
					'data_grid_row_delete'				=>	__('Delete Row', 'ws-form'),
					'data_grid_row_delete_confirm'		=>	__('Are you sure you want to delete this row?', 'ws-form'),
					'data_grid_row_bulk_actions'		=>	__('Bulk Actions', 'ws-form'),
					'data_grid_row_default'				=>	__('Selected', 'ws-form'),
					'data_grid_row_required'			=>	__('Required', 'ws-form'),
					'data_grid_row_disabled'			=>	__('Disabled', 'ws-form'),
					'data_grid_row_hidden'				=>	__('Hidden', 'ws-form'),

					// Data grids - Bulk actions
					'data_grid_row_bulk_actions_select'			=>	__('Select...', 'ws-form'),
					'data_grid_row_bulk_actions_delete'			=>	__('Delete', 'ws-form'),
					'data_grid_row_bulk_actions_default'		=>	__('Set Default', 'ws-form'),
					'data_grid_row_bulk_actions_default_off'	=>	__('Set Not Default', 'ws-form'),
					'data_grid_row_bulk_actions_required'		=>	__('Set Required', 'ws-form'),
					'data_grid_row_bulk_actions_required_off'	=>	__('Set Not Required', 'ws-form'),
					'data_grid_row_bulk_actions_disabled'		=>	__('Set Disabled', 'ws-form'),
					'data_grid_row_bulk_actions_disabled_off'	=>	__('Set Not Disabled', 'ws-form'),
					'data_grid_row_bulk_actions_hidden'			=>	__('Set Hidden', 'ws-form'),
					'data_grid_row_bulk_actions_hidden_off'		=>	__('Set Not Hidden', 'ws-form'),
					'data_grid_row_bulk_actions_apply'			=>	__('Apply', 'ws-form'),

					// Data grids - Rows per page
					'data_grid_rows_per_page'				=>	__('Rows Per Page', 'ws-form'),
					'data_grid_rows_per_page_0'				=>	__('Show All', 'ws-form'),
					'data_grid_rows_per_page_apply'			=>	__('Apply', 'ws-form'),

					// Data grids - Upload
					'data_grid_group_upload_csv'			=>	__('Import CSV', 'ws-form'),

					// Data grids - Download
					'data_grid_group_download_csv'			=>	__('Export CSV', 'ws-form'),

					// Data grids - Actions
					'data_grid_action_edit'					=>	__('Edit', 'ws-form'),
					'data_grid_action_action'				=>	__('Action', 'ws-form'),
					'data_grid_action_event'				=>	__('When should this action run?', 'ws-form'),

					// Data grids - Actions
					'data_grid_action_edit'					=>	__('Edit', 'ws-form'),
					'data_grid_action_clone'				=>	__('Clone', 'ws-form'),

					// Data grids - Insert image
					'data_grid_insert_image'				=>	__('Insert Image', 'ws-form'),

					// Repeaters
					'repeater_row_sort'						=>	__('Sort Row', 'ws-form'),
					'repeater_row_add'						=>	__('Add Row', 'ws-form'),
					'repeater_row_delete'					=>	__('Delete Row', 'ws-form'),

					// Breakpoint size
					'breakpoint_reset'						=>	__('Reset', 'ws-form'),

					// Sidebar titles
					'sidebar_title_form'					=>	__('Form', 'ws-form'),
					'sidebar_title_group'					=>	__('Tab', 'ws-form'),
					'sidebar_title_section'					=>	__('Section', 'ws-form'),
					'sidebar_title_history'					=>	__('History', 'ws-form'),
					'sidebar_button_image'					=>	__('Select', 'ws-form'),
					'sidebar_button_media'					=>	__('Select', 'ws-form'),

					// Search
					'field_search'							=>	__('Field search...', 'ws-form'),
					'section_search'						=>	__('Section search...', 'ws-form'),

					'field_selector_upgrade'	=>	sprintf(__('<a href="%s" target="_blank">Upgrade to PRO</a> for <a href="%s" target="_blank">55+ field types</a>, <a href="%s" target="_blank">conditional logic</a>, <a href="%s" target="_blank">calculated fields</a> and more!', 'ws-form'), WS_Form_Common::get_plugin_website_url('', 'siderbar_toolbox'), WS_Form_Common::get_plugin_website_url('/knowledgebase_category/field-types/', 'siderbar_toolbox'), WS_Form_Common::get_plugin_website_url('/knowledgebase/conditional-logic/', 'siderbar_toolbox'), WS_Form_Common::get_plugin_website_url('/knowledgebase/calculated-fields/', 'siderbar_toolbox')),

					'section_selector_upgrade'	=>	sprintf(__('<a href="%s" target="_blank">Upgrade to PRO</a> for <a href="%s" target="_blank">more sections</a>, <a href="%s" target="_blank">conditional logic</a>, <a href="%s" target="_blank">calculated fields</a> and more!', 'ws-form'), WS_Form_Common::get_plugin_website_url('', 'siderbar_toolbox'), WS_Form_Common::get_plugin_website_url('/knowledgebase/section-library/', 'siderbar_toolbox'), WS_Form_Common::get_plugin_website_url('/knowledgebase/conditional-logic/', 'siderbar_toolbox'), WS_Form_Common::get_plugin_website_url('/knowledgebase/calculated-fields/', 'siderbar_toolbox')),

					'action_upgrade'			=>	sprintf(__('<a href="%s" target="_blank">Upgrade to PRO</a> for <a href="%s" target="_blank">more actions</a> and the ability to run actions using <a href="%s" target="_blank">conditional logic</a>.', 'ws-form'), WS_Form_Common::get_plugin_website_url('', 'siderbar_action'), WS_Form_Common::get_plugin_website_url('/knowledgebase_category/actions/', 'siderbar_toolbox'), WS_Form_Common::get_plugin_website_url('/knowledgebase/conditional-logic/', 'siderbar_toolbox')),
					// Sidebar - Expand / Contract
					'data_sidebar_expand'					=>	__('Expand', 'ws-form'),
					'data_sidebar_contract'					=>	__('Contract', 'ws-form'),

					// Actions
					'action_label_default'					=>	__('New Action', 'ws-form'),
					// Breakpoint options
					'breakpoint_offset_column_width'			=>	__('Width - Columns', 'ws-form'),
					'breakpoint_offset_column_offset'			=>	__('Offset - Columns', 'ws-form'),
					'breakpoint_option_default'					=>	__('Default ', 'ws-form'),
					'breakpoint_option_inherit'					=>	__('Inherit', 'ws-form'),
					'breakpoint_option_column_default_singular'	=>	'%s',
					'breakpoint_option_column_default_plural'	=>	'%s',
					'breakpoint_option_offset_default_singular'	=>	'%s',
					'breakpoint_option_offset_default_plural'	=>	'%s',
					'breakpoint_option_column_singular'			=>	'%s',
					'breakpoint_option_column_plural'			=>	'%s',
					'breakpoint_option_offset_singular'			=>	'%s',
					'breakpoint_option_offset_plural'			=>	'%s',

					// Orientation Breakpoint options
					'orientation_breakpoint_label_width'					=>	__('%s Width', 'ws-form'),
					'orientation_breakpoint_width'							=>	__(' = %s width', 'ws-form'),
					'orientation_breakpoint_width_full'						=>	__(' = Full width', 'ws-form'),
					'orientation_breakpoint_option_default'					=>	__('Default ', 'ws-form'),
					'orientation_breakpoint_option_inherit'					=>	__('Inherit', 'ws-form'),
					'orientation_breakpoint_option_column_default_singular'	=>	'%s column',
					'orientation_breakpoint_option_column_default_plural'	=>	'%s columns',
					'orientation_breakpoint_option_column_singular'			=>	'%s column',
					'orientation_breakpoint_option_column_plural'			=>	'%s columns',

					'column_size_change'						=>	__('Change column size', 'ws-form'),
					'offset_change'								=>	__('Change offset', 'ws-form'),

					// Submit
					'submit_status'								=>	__('Status', 'ws-form'),
					'submit_preview'							=>	__('Preview', 'ws-form'),
					'submit_date_added'							=>	__('Added', 'ws-form'),
					'submit_date_updated'						=>	__('Updated', 'ws-form'),
					'submit_user'								=>	__('User', 'ws-form'),
					'submit_status'								=>	__('Status', 'ws-form'),
					'submit_duration'							=>	__('Duration', 'ws-form'),
					'submit_duration_days'						=>	__('Days', 'ws-form'),
					'submit_duration_hours'						=>	__('Hours', 'ws-form'),
					'submit_duration_minutes'					=>	__('Minutes', 'ws-form'),
					'submit_duration_seconds'					=>	__('Seconds', 'ws-form'),
					'submit_tracking'							=>	__('Tracking', 'ws-form'),
					'submit_tracking_geo_location_permission_denied'	=>	__('User denied the request for geo location.', 'ws-form'),
					'submit_tracking_geo_location_position_unavailable'	=>	__('Geo location information was unavailable.', 'ws-form'),
					'submit_tracking_geo_location_timeout'				=>	__('The request to get user geo location timed out.', 'ws-form'),
					'submit_tracking_geo_location_default'				=>	__('An unknown error occurred whilst retrieving geo location.', 'ws-form'),
					'submit_actions'							=>	__('Actions', 'ws-form'),
					'submit_actions_column_index'				=>	'#',
					'submit_actions_column_action'				=>	__('Action', 'ws-form'),
					'submit_actions_column_meta_label'			=>	__('Setting', 'ws-form'),
					'submit_actions_column_meta_value'			=>	__('Value', 'ws-form'),
					'submit_actions_column_logs'				=>	__('Log', 'ws-form'),
					'submit_actions_column_errors'				=>	__('Error', 'ws-form'),
					'submit_actions_repost'						=>	__('Run Again', 'ws-form'),
					'submit_actions_meta'						=>	__('Settings', 'ws-form'),
					'submit_actions_logs'						=>	__('Logs', 'ws-form'),
					'submit_actions_errors'						=>	__('Errors', 'ws-form'),
					'submit_action_logs'						=>	__('Action Logs', 'ws-form'),
					'submit_action_errors'						=>	__('Action Errors', 'ws-form'),
					'submit_ecommerce'							=>	__('E-Commerce', 'ws-form'),
					'submit_encrypted'							=>	__('Encrypted', 'ws-form'),

					// Add form
					'form_add_create'		=>	__('Create', 'ws-form'),
					'form_import_confirm'	=>	__("Are you sure you want to import this file?\n\nImporting a form file will overwrite the existing form and create new field ID's.\n\nIt is not recommended that you use this feature for forms that are in use on your website.", 'ws-form'),

					// Sidebar - Expand / Contract
					'sidebar_expand'	=>	__('Expand', 'ws-form'),
					'sidebar_contract'	=>	__('Contract', 'ws-form'),

					// Knowledge Base
					'knowledgebase_search_label'		=>	__('Enter keyword(s) to search', 'ws-form'),
					'knowledgebase_search_button'		=>	__('Search', 'ws-form'),
					'knowledgebase_search_placeholder'	=>	__('Keyword(s)', 'ws-form'),
					'knowledgebase_popular'				=>	__('Popular Articles', 'ws-form'),
					'knowledgebase_view_all'			=>	__('View Full Knowledge Base', 'ws-form'),

					// Contact
					'support_contact_thank_you'			=>	__('Thank you for your support request.', 'ws-form'),
					'support_contact_error'				=>	__('An error occurred when submitting your support request. Please email support@wsform.com (%s)', 'ws-form'),

					// Starred
					'starred_on'						=>	__('Starred', 'ws-form'),
					'starred_off'						=>	__('Not Starred', 'ws-form'),

					// Viewed
					'viewed_on'							=>	__('Mark as Unread', 'ws-form'),
					'viewed_off'						=>	__('Mark as Read', 'ws-form'),

					// Form location
					'form_location_not_found'			=>	__('Form not found in content', 'ws-form'),

					// Shortcode copy
					'shortcode_copied'					=>	__('Shortcode copied', 'ws-form'),

					// API - List subs
					'list_subs_call'		=>	__('Retrieving...', 'ws-form'),
					'list_subs_select'		=>	__('Select...', 'ws-form'),

					// Options
					'options_select'		=>	__('Select...', 'ws-form')
				)
			);

			// Set icons
			foreach($settings_form_admin['group']['buttons'] as $key => $buttons) {

				$method = $buttons['method'];
				$settings_form_admin['group']['buttons'][$key]['icon'] = self::get_icon_16_svg($method);
			}
			foreach($settings_form_admin['section']['buttons'] as $key => $buttons) {

				$method = $buttons['method'];
				$settings_form_admin['section']['buttons'][$key]['icon'] = self::get_icon_16_svg($method);
			}
			foreach($settings_form_admin['field']['buttons'] as $key => $buttons) {

				$method = $buttons['method'];
				$settings_form_admin['field']['buttons'][$key]['icon'] = self::get_icon_16_svg($method);
			}

			// Apply filter
			$settings_form_admin = apply_filters('wsf_config_settings_form_admin', $settings_form_admin);

			// Cache
			self::$settings_form_admin = $settings_form_admin;

			return $settings_form_admin;
		}

		// Calc
		public static function get_calc() {

			// Check cache
			if(self::$calc !== false) { return self::$calc; }

			$calc = array(

				// Row 1
				array(

					array('type' => 'select', 'source' => 'field', 'colspan' => 2, 'label' => __('Insert Field', 'ws-form'), 'action' => 'insert-select'),
					array('type' => 'button', 'label' => __('del', 'ws-form'), 'class' => 'wsf-button-danger', 'title' => __('Delete', 'ws-form'), 'action' => 'delete'),
					array('type' => 'button', 'label' => __('AC', 'ws-form'), 'class' => 'wsf-button-danger', 'title' => __('All Clear', 'ws-form'), 'action' => 'clear'),
				),

				// Row 2
				array(

					array('type' => 'button', 'label' => '(', 'title' => __('Opening Parentheses', 'ws-form'), 'action' => 'insert', 'insert' => '('),
					array('type' => 'button', 'label' => ')', 'title' => __('Closing Parentheses', 'ws-form'), 'action' => 'insert', 'insert' => ')'),
					array('type' => 'button', 'label' => ',', 'title' => __('Percentage', 'ws-form'), 'action' => 'insert', 'insert' => ','),
					array('type' => 'select', 'source' => 'variables', 'label' => 'f', 'class' => 'wsf-button-primary', 'title' => __('Variables', 'ws-form'), 'action' => 'insert-select-highlight-parameters', 'variables_group_id' => 'math'),
				),

				// Row 3
				array(

					array('type' => 'button', 'label' => '7', 'action' => 'insert', 'insert' => '7'),
					array('type' => 'button', 'label' => '8', 'action' => 'insert', 'insert' => '8'),
					array('type' => 'button', 'label' => '9', 'action' => 'insert', 'insert' => '9'),
					array('type' => 'button', 'label' => '/', 'class' => 'wsf-button-primary', 'title' => __('Divide', 'ws-form'), 'action' => 'insert', 'insert' => '/'),
				),

				// Row 4
				array(

					array('type' => 'button', 'label' => '4', 'action' => 'insert', 'insert' => '4'),
					array('type' => 'button', 'label' => '5', 'action' => 'insert', 'insert' => '5'),
					array('type' => 'button', 'label' => '6', 'action' => 'insert', 'insert' => '6'),
					array('type' => 'button', 'label' => '*', 'class' => 'wsf-button-primary', 'title' => __('Multiply', 'ws-form'), 'action' => 'insert', 'insert' => '*'),
				),

				// Row 5
				array(

					array('type' => 'button', 'label' => '1', 'action' => 'insert', 'insert' => '1'),
					array('type' => 'button', 'label' => '2', 'action' => 'insert', 'insert' => '2'),
					array('type' => 'button', 'label' => '3', 'action' => 'insert', 'insert' => '3'),
					array('type' => 'button', 'label' => '-', 'class' => 'wsf-button-primary', 'title' => __('Subtract', 'ws-form'), 'action' => 'insert', 'insert' => '-'),
				),

				// Row 6
				array(

					array('type' => 'button', 'label' => '0', 'colspan' => 2, 'action' => 'insert', 'insert' => '0'),
					array('type' => 'button', 'label' => '.', 'title' => __('Decimal', 'ws-form'), 'action' => 'insert', 'insert' => '.'),
					array('type' => 'button', 'label' => '+', 'class' => 'wsf-button-primary', 'title' => __('Add', 'ws-form'), 'action' => 'insert', 'insert' => '+'),
				)
			);

			// Apply filter
			$calc = apply_filters('wsf_config_calc', $calc);

			// Cache
			self::$calc = $calc;

			return $calc;
		}

		// Parse variable
		public static function get_parse_variable_help($form_id = 0, $public = true, $group = false, $group_first = false) {

			// Check cache
			if(
				isset(self::$parse_variable_help[$public]) &&
				isset(self::$parse_variable_help[$public][$group])

			) { return self::$parse_variable_help[$public][$group]; }

			$parse_variable_help = array();

			// Get admin variables
			$parse_variables_config = self::get_parse_variables($public);

			// Get all parse variables
			$parse_variables = [];

			foreach($parse_variables_config as $parse_variable_group_id => $parse_variable_group) {

				if(!isset($parse_variable_group['label'])) { continue; }

				if(($group !== false) && (strpos($group, $parse_variable_group_id) === false)) { continue; }

				$group_label = $parse_variable_group['label'];

				foreach($parse_variable_group['variables'] as $parse_variable_key => $parse_variables_single) {

					$parse_variables_single['group_id'] = $parse_variable_group_id;
					$parse_variables_single['group_label'] = $group_label;
					$parse_variables_single['key'] = $parse_variable_key;
					$parse_variables[] = $parse_variables_single;
				}
			}

			// Sort parse variables
			uasort($parse_variables, function ($parse_variable_1, $parse_variable_2) use ($group_first) {

				if($parse_variable_1['group_label'] == $parse_variable_2['group_label']) {

					if($parse_variable_1['label'] == $parse_variable_2['label']) return 0;
					return $parse_variable_1['label'] < $parse_variable_2['label'] ? -1 : 1;
				}

				if($group_first !== false) {

					if($parse_variable_1['group_label'] == $group_first) { return -1; }
					if($parse_variable_2['group_label'] == $group_first) { return 1; }
				}

				return $parse_variable_1['group_label'] < $parse_variable_2['group_label'] ? -1 : 1;
			});

			// Process variables
			foreach($parse_variables as $parse_variable) {

				if(!isset($parse_variable['label'])) { continue; }

				$parse_variable_key = $parse_variable['key'];

				// Has attributes?
				if(isset($parse_variable['attributes'])) {

					// Functions
					$attributes_text = [];
					$attributes_value = [];
					foreach($parse_variable['attributes'] as $parse_variable_attribute) {

						$parse_variable_attribute_id = $parse_variable_attribute['id'];
						$parse_variable_attribute_required = isset($parse_variable_attribute['required']) ? $parse_variable_attribute['required'] : false;
						$parse_variable_attribute_default = isset($parse_variable_attribute['default']) ? $parse_variable_attribute['default'] : false;

						$attributes_text[] = $parse_variable_attribute_id . ($parse_variable_attribute_required ? '*' : '');

						$attributes_value[] = $parse_variable_attribute_id;
					}

					$value = $parse_variable_key . '(' . implode(', ', $attributes_value) . ')';
					$parse_variable_help_single = array('text' => $parse_variable['label'], 'value' => '#' . $value, 'group_id' => $parse_variable['group_id'], 'group_label' => $parse_variable['group_label'], 'description' => isset($parse_variable['description']) ? $parse_variable['description'] : '');

					if(isset($parse_variable['kb_slug'])) { $parse_variable_help_single['kb_slug'] = $parse_variable['kb_slug']; }

					if(isset($parse_variable['limit'])) { $parse_variable_help_single['limit'] = $parse_variable['limit']; }

					self::parse_variable_help_add($parse_variable_help, $parse_variable_help_single);

				} else {

					// No attributes
					$value = $parse_variable_key;
					$parse_variable_help_single = array('text' => $parse_variable['label'], 'value' => '#' . $value, 'group_id' => $parse_variable['group_id'], 'group_label' => $parse_variable['group_label'], 'description' => isset($parse_variable['description']) ? $parse_variable['description'] : '');

					if(isset($parse_variable['kb_slug'])) { $parse_variable_help_single['kb_slug'] = $parse_variable['kb_slug']; }

					if(isset($parse_variable['limit'])) { $parse_variable_help_single['limit'] = $parse_variable['limit']; }

					self::parse_variable_help_add($parse_variable_help, $parse_variable_help_single);
				}
			}

			// Apply filter
			$parse_variable_help = apply_filters('wsf_config_parse_variable_help', $parse_variable_help);

			// Cache
			self::$parse_variable_help[$public][$group] = $parse_variable_help;

			return $parse_variable_help;
		}

		// Parse variables help add
		public static function parse_variable_help_add(&$parse_variable_help, $parse_variable_help_single) {

			$passthrough_attributes = array('description', 'limit', 'kb_slug');

			// Passthrough attributes
			foreach($passthrough_attributes as $passthrough_attribute) {

				if(isset($parse_variable[$passthrough_attribute])) { $parse_variable_help_single[$passthrough_attribute] = $parse_variable[$passthrough_attribute]; }

			}

			$parse_variable_help[] = $parse_variable_help_single;
		}

		// System report
		public static function get_system() {

			global $wpdb, $required_mysql_version;

			// Get MySQL max_allowed_packet
			$mysql_max_allowed_packet = $wpdb->get_var('SELECT @@global.max_allowed_packet;');
			if(is_null($mysql_max_allowed_packet)) { $mysql_max_allowed_packet = 0; }

			$system = array(

				// WS Form
				'ws_form' => array(

					'label'		=> WS_FORM_NAME_PRESENTABLE,
					'variables'	=> array(

						'version'		=> array('label' => __('Version', 'ws-form'), 'value' => WS_FORM_VERSION),
						'edition'		=> array('label' => __('Edition', 'ws-form'), 'value' => WS_FORM_EDITION, 'type' => 'edition'),
						'framework'		=> array('label' => __('Framework', 'ws-form'), 'value' => WS_Form_Common::option_get('framework')),
					)
				),

				// WordPress
				'wordpress' => array(

					'label'		=> __('WordPress', 'ws-form'),
					'variables'	=> array(

						'version' 			=> array('label' => __('Version', 'ws-form'), 'value' => get_bloginfo('version'), 'valid' => (WS_Form_Common::version_compare(get_bloginfo('version'), WS_FORM_MIN_VERSION_WORDPRESS) >= 0), 'min' => WS_FORM_MIN_VERSION_WORDPRESS),
						'multisite'			=> array('label' => __('Multisite Enabled', 'ws-form'), 'value' => is_multisite(), 'type' => 'boolean'),
						'home_url' 			=> array('label' => __('Home URL', 'ws-form'), 'value' => get_home_url(), 'type' => 'url'),
						'site_url' 			=> array('label' => __('Site URL', 'ws-form'), 'value' => get_site_url(), 'type' => 'url'),
						'theme_active' 		=> array('label' => __('Theme', 'ws-form'), 'value' => wp_get_theme(), 'type' => 'theme'),
						'plugins_active' 	=> array('label' => __('Plugins', 'ws-form'), 'value' => get_option('active_plugins', array()), 'type' => 'plugins'),
						'locale'			=> array('label' => __('Locale', 'ws-form'), 'value' => get_locale()),
						'max_upload_size'	=> array('label' => __('Max Upload Size', 'ws-form'), 'value' => wp_max_upload_size(), 'type' => 'size'),
						'memory_limit'		=> array('label' => __('Memory Limit', 'ws-form'), 'value' => (defined('WP_MEMORY_LIMIT') ? WP_MEMORY_LIMIT : 0)),
					)
				),

				// PHP
				'php' => array(

					'label'		=>	__('PHP', 'ws-form'),
					'variables'	=> array(

						'version'				=> array('label' => __('Version', 'ws-form'), 'value' => phpversion(), 'valid' => (version_compare(phpversion(), WS_FORM_MIN_VERSION_PHP) >= 0), 'min' => WS_FORM_MIN_VERSION_PHP),
						'curl'					=> array('label' => __('CURL Installed', 'ws-form'), 'value' => (function_exists('curl_init') && function_exists('curl_setopt')), 'type' => 'boolean', 'valid' => true),
						'suhosin'				=> array('label' => __('SUHOSIN Extension Loaded', 'ws-form'), 'value' => extension_loaded('suhosin'), 'type' => 'boolean'),
						'date_default_timezone'	=> array('label' => __('Default Timezone', 'ws-form'), 'value' => date_default_timezone_get()),
						'memory_limit'			=> array('label' => __('Memory Limit', 'ws-form'), 'value' => (ini_get('memory_limit') ? ini_get('memory_limit') : 0)),
						'post_max_size'	=> array('label' => __('Max Upload Size', 'ws-form'), 'value' => ini_get('post_max_size')),
						'max_input_vars'	=> array('label' => __('Max Input Variables', 'ws-form'), 'value' => ini_get('max_input_vars'), 'valid' => (ini_get('max_input_vars') >= WS_FORM_MIN_INPUT_VARS), 'min' => WS_FORM_MIN_INPUT_VARS),
						'max_execution_time'	=> array('label' => __('Max Execution Time', 'ws-form'), 'value' => ini_get('max_execution_time'), 'suffix' => __(' seconds', 'ws-form')),
						'smtp'				=> array('label' => __('SMTP Hostname', 'ws-form'), 'value' => ini_get('SMTP')),
						'smtp_port'			=> array('label' => __('SMTP Port', 'ws-form'), 'value' => ini_get('smtp_port')),
					)
				),

				// Web Server
				'web_server' => array(

					'label'		=>	__('Web Server', 'ws-form'),
					'variables'	=> array(

						'name'				=> array('label' => __('Name', 'ws-form'), 'value' => WS_Form_Common::get_http_env('SERVER_SOFTWARE')),
						'ip'				=> array('label' => __('IP', 'ws-form'), 'value' => WS_Form_Common::get_http_env(array('SERVER_ADDR', 'LOCAL_ADDR'))),
					)
				),

				// MySQL
				'mysql' => array(

					'label'		=>	__('MySQL', 'ws-form'),
					'variables'	=> array(

						'version'	=> array('label' => __('Version', 'ws-form'), 'value' => $wpdb->db_version(), 'valid' => version_compare($wpdb->db_version(), $required_mysql_version, '>'), 'min' => $required_mysql_version),
						'max_allowed_packet' => array('label' => __('Max Allowed Packet', 'ws-form'), 'value' => $mysql_max_allowed_packet, 'type' => 'size', 'valid' => ($mysql_max_allowed_packet >= WS_FORM_MIN_MYSQL_MAX_ALLOWED_PACKET), 'min' => '4 MB')
					)
				)
			);


			// Apply filter
			$system = apply_filters('wsf_config_system', $system);

			return $system;
		}

	}