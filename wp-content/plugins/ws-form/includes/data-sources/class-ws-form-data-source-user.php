<?php

	class WS_Form_Data_Source_User extends WS_Form_Data_Source {

		public $id = 'user';
		public $pro_required = false;
		public $label;
		public $label_retrieving;
		public $records_per_page = 0;

		// ACF
		public $acf_activated;

		public function __construct() {

			// Set label
			$this->label = __('Users', 'ws-form');

			// Set label retrieving
			$this->label_retrieving = __('Retrieving Users...', 'ws-form');

			// ACF
			$this->acf_activated = class_exists('ACF');

			// Register action
			parent::register($this);

			// Register config filters
			add_filter('wsf_config_meta_keys', array($this, 'config_meta_keys'), 10, 2);

			// Register API endpoint
			add_action('rest_api_init', array($this, 'rest_api_init'), 10, 0);

			// Records per page
			$this->records_per_page = apply_filters('wsf_data_source_' . $this->id . '_records_per_age', $this->records_per_page);
		}

		// Get
		public function get($form_object, $field_id, $page, $meta_key, $meta_value, $no_paging = false, $api_request = false) {

			global $wp_roles;

			// Check meta key
			if(empty($meta_key)) { return self::error(__('No meta key specified', 'ws-form'), $field_id, $this, $api_request); }

			// Get meta key config
			$meta_keys = WS_Form_Config::get_meta_keys();
			if(!isset($meta_keys[$meta_key])) { return self::error(__('Unknown meta key', 'ws-form'), $field_id, $this, $api_request); }
			$meta_key_config = $meta_keys[$meta_key];

			// Check meta value
			if(
				!is_object($meta_value) ||
				!isset($meta_value->columns) ||
				!isset($meta_value->groups) ||
				!isset($meta_value->groups[0])
			) {

				if(!isset($meta_key_config['default'])) { return self::error(__('No default value', 'ws-form'), $field_id, $this, $api_request); }

				// If meta_value is invalid, create one from default
				$meta_value = json_decode(json_encode($meta_key_config['default']));
			}

			// Build roles
			$roles = array();
			if(is_array($this->data_source_user_filter_roles)) {

				foreach($this->data_source_user_filter_roles as $filter_role) {

					if(
						!isset($filter_role->{'data_source_' . $this->id . '_roles'}) ||
						empty($filter_role->{'data_source_' . $this->id . '_roles'})

					) { continue; }

					$roles[] = $filter_role->{'data_source_' . $this->id . '_roles'};
				}
			}
			if(count($roles) == 0) {

				$roles = array_keys($wp_roles->roles);
			}

			// Groups
			$data_source_user_groups = ($this->data_source_user_groups == 'on');

			// Check order
			if(!in_array($this->data_source_user_order, array(

				'ASC',
				'DESC'

			))) { return self::error(__('Invalid order method', 'ws-form'), $field_id, $this, $api_request); }

			// Check order by
			if(!in_array($this->data_source_user_order_by, array(

				'ID',
				'display_name',
				'user_name',
				'login',
				'nicename',
				'email',
				'url',
				'post_count'

			))) { return self::error(__('Invalid order by method'), $field_id, $this, $api_request); }

			// Columns
			$columns = array();
			$column_index = 0;
			$meta_value->columns = array();

			if(!is_array($this->data_source_user_columns)) {

				return self::error(__('Invalid column data', 'ws-form'), $field_id, $this, $api_request);
			}

			foreach($this->data_source_user_columns as $column) {

				if(
					!isset($column->{'data_source_' . $this->id . '_column'}) ||
					empty($column->{'data_source_' . $this->id . '_column'})

				) { continue; }

				$column = $column->{'data_source_' . $this->id . '_column'};

				$columns[] = $column;

				switch($column) {

					case 'id' : $label = __('ID', 'ws-form'); break;
					case 'display_name' : $label = __('Display Name', 'ws-form'); break;
					case 'nicename' : $label = __('Nicename', 'ws-form'); break;
					case 'login' : $label = __('Login', 'ws-form'); break;
					case 'email' : $label = __('Email', 'ws-form'); break;
					case 'url' : $label = __('Website', 'ws-form'); break;
					case 'avatar' : $label = __('Avatar', 'ws-form'); break;
					default : $label = __('Unknown', 'ws-form');
				}

				$meta_value->columns[] = (object) array('id' => $column_index++, 'label' => $label);
			}

			// Build meta_keys
			$meta_keys = array();

			if(is_array($this->data_source_user_meta_keys) && (count($this->data_source_user_meta_keys) > 0)) {

				foreach($this->data_source_user_meta_keys as $meta_key) {

					if($meta_key != '') { 

						$meta_keys[] = $meta_key;
					}
				}
			}

			$has_meta_keys = (count($meta_keys) > 0);

			if($has_meta_keys) {

				$meta_keys = array_unique($meta_keys);

				foreach($meta_keys as $meta_key) {

					$meta_value->columns[] = (object) array('id' => $column_index++, 'label' => $meta_key);
				}
			}

			$acf_field_keys = array();

			if($this->acf_activated) {

				if(is_array($this->data_source_user_acf_fields) && (count($this->data_source_user_acf_fields) > 0)) {

					foreach($this->data_source_user_acf_fields as $acf_field_key) {

						if(
							!isset($acf_field_key->{'data_source_' . $this->id . '_acf_field_key'}) ||
							empty($acf_field_key->{'data_source_' . $this->id . '_acf_field_key'})

						) { continue; }

						$acf_field_key = $acf_field_key->{'data_source_' . $this->id . '_acf_field_key'};

						if($acf_field_key != '') { 

							$acf_field_keys[] = $acf_field_key;
						}
					}
				}
			}

			$has_acf_fields = (count($acf_field_keys) > 0);

			if($this->acf_activated) {

				if($has_acf_fields) {

					$acf_field_keys = array_unique($acf_field_keys);

					foreach($acf_field_keys as $acf_field_key) {

						$acf_field = get_field_object($acf_field_key);
						if($acf_field === false) { continue; }

						$meta_value->columns[] = (object) array('id' => $column_index++, 'label' => $acf_field['label']);
					}
				}
			}

			// Base meta
			$group = clone($meta_value->groups[0]);
			$max_num_pages = 0;

			// Form parse?
			if($no_paging) { $this->records_per_page = 0; }

			// Run through roles
			$group_index = 0;
			$row_index = 1;
			foreach(($data_source_user_groups ? $roles : array(false)) as $role) {

				// Calculate offset
				if($no_paging === false) {

					// API request
					$offset = (($page - 1) * $this->records_per_page);

				} else {

					// Form parse
					$offset = 0;
				}
				// get_users args
				$args = array(

					'role__in' => ($data_source_user_groups == 'on') ? $role : $roles,
					'number' => $this->records_per_page,
					'offset' => $offset,
					'fields' => 'ids',
					'order' => $this->data_source_user_order,
					'orderby' => $this->data_source_user_order_by
				);

				// get_users
				$wp_query = new WP_User_Query($args);

					// max_num_pages
//				if($wp_query->max_num_pages > $max_num_pages) { $max_num_pages = $wp_query->max_num_pages; }

				$user_ids = $wp_query->get_results();

				// Skip if no records
				if(count($user_ids) === 0) { continue; }

				// Rows
				$rows = array();
				foreach($user_ids as $user_index => $user_id) {

					$user = get_user_by('ID', $user_id);

					// Build row data
					$row_data = array();
					foreach($columns as $column) {

						$column_value = '';
						switch($column) {

							case 'id' : $column_value = $user_id; break;
							case 'display_name' : $column_value = $user->display_name; break;
							case 'nicename' : $column_value = $user->user_nicename; break;
							case 'login' : $column_value = $user->user_login; break;
							case 'email' : $column_value = $user->user_email; break;
							case 'url' : $column_value = $user->user_url; break;

							case 'avatar' :

								// Get featured image URL
								$column_value = '';
								$avatar_url = get_avatar_url($user_id);
								if($avatar_url !== false) {

									$column_value = $avatar_url;

									if($this->data_source_user_image_tag) {

										$alt = $user->display_name;
										$width = $height = '96';

										$column_value = sprintf('<img src="%s"%s%s%s />', 

											$column_value,
											$width ? sprintf(' width="%u"', esc_attr($width)) : '',
											$height ? sprintf(' height="%u"', esc_attr($height)) : '',
											$alt ? sprintf(' alt="%s"', esc_attr($alt)) : ''
										);
									}
								}

								break;
						}

						$row_data[] = $column_value;
					}

					$row = (object) array(

						'id'		=> $offset + $row_index++,
						'default'	=> '',
						'required'	=> '',
						'disabled'	=> '',
						'hidden'	=> '',
						'data'		=> $row_data
					);

					// Add meta key columns
					if($has_meta_keys) {

						foreach($meta_keys as $meta_key) {

							$row->data[] = get_user_meta($user_id, $meta_key, true);
						}
					}

					// Add ACF fields
					if($has_acf_fields) {

						foreach($acf_field_keys as $acf_field_key) {

							$acf_field = get_field($acf_field_key, 'user_' . $user_id, true);
							$row_data = false;

							// Process ACF field types
							if(is_array($acf_field)) {

								$acf_field_type = isset($acf_field['type']) ? $acf_field['type'] : 'text';

								switch($acf_field_type) {

									case 'file' :

										$row_data = isset($acf_field['url']) ? $acf_field['url'] : false;
										break;

									case 'image' :

										$row_data = isset($acf_field['url']) ? $acf_field['url'] : false;

										if($row_data !== false) {

											if($this->data_source_user_image_tag) {

												$width = isset($acf_field['width']) ? $acf_field['width'] : false;
												$height = isset($acf_field['height']) ? $acf_field['height'] : false;
												$alt = isset($acf_field['alt']) ? $acf_field['alt'] : false;

												$row_data = sprintf('<img src="%s"%s%s%s />', 

													$row_data,
													$width ? sprintf(' width="%u"', esc_attr($width)) : '',
													$height ? sprintf(' height="%u"', esc_attr($height)) : '',
													$alt ? sprintf(' alt="%s"', esec_attr($alt)) : ''
												);
											}
										}

										break;

									default :

										$row_data = get_field($acf_field_key, 'user_' . $user_id, false);
								}
							}

							if($row_data === false) {

								$row_data = is_string($acf_field) ? $acf_field : '';
							}

							$row->data[] = $row_data;
						}
					}

					$rows[] = $row;
				}

				// Build new group if one does not exist
				if(!isset($meta_value->groups[$group_index])) {

					$meta_value->groups[$group_index] = clone($group);
				}

				// User label
				if($data_source_user_groups) {

					$roles_for_group_label = $wp_roles->roles;
					$meta_value->groups[$group_index]->label = (isset($roles_for_group_label[$role]) ? $roles_for_group_label[$role]['name'] : __('Unknown', 'ws-form'));

				} else {

					$meta_value->groups[$group_index]->label = $this->label;
				}

				// Rows
				$meta_value->groups[$group_index]->rows = $rows;

				// Enable optgroups
				if(count($roles) > 1) {

					$meta_value->groups[$group_index]->mask_group = 'on';
					$meta_value->groups[$group_index]->label_render = 'on';
				}

				$group_index++;
			}

			// Delete any old groups
			while(isset($meta_value->groups[$group_index])) {

				unset($meta_value->groups[$group_index++]);
			}

			// Column mapping
			$meta_keys = parent::get_column_mapping(array(), $meta_value, $meta_key_config);

			// Return data
			return array('error' => false, 'error_message' => '', 'meta_value' => $meta_value, 'max_num_pages' => $max_num_pages, 'meta_keys' => $meta_keys);
		}

		// Get meta keys
		public function get_data_source_meta_keys() {

			$meta_keys = array_merge(

				array('data_source_' . $this->id . '_filter_roles'),
				$this->acf_activated ? array('data_source_'. $this->id . '_acf_fields') : array(),
				array('data_source_' . $this->id . '_meta_keys',
				'data_source_' . $this->id . '_order_by',
				'data_source_' . $this->id . '_order',
				'data_source_' . $this->id . '_groups',
				'data_source_' . $this->id . '_image_tag',
				'data_source_' . $this->id . '_columns')
//				'data_source_recurrence'
			);

			return $meta_keys;
		}

		// Get settings
		public function get_data_source_settings() {

			// Build settings
			$settings = array(

				'meta_keys' => self::get_data_source_meta_keys()
			);

			// Add retrieve button
			$settings['meta_keys'][] = 'data_source_get';

			// Wrap settings so they will work with sidebar_html function in admin.js
			$settings = parent::get_settings_wrapper($settings);

			// Add label
			$settings->label = $this->label;

			// Add label retrieving
			$settings->label_retrieving = $this->label_retrieving;

			// Add API GET endpoint
			$settings->endpoint_get = 'data-source/' . $this->id . '/';

			// Apply filter
			$settings = apply_filters('wsf_data_source_' . $this->id . '_settings', $settings);

			return $settings;
		}

		// Meta keys for this action
		public function config_meta_keys($meta_keys = array(), $form_id = 0) {

			// Build config_meta_keys
			$config_meta_keys = array(

				// Filter - Role
				'data_source_' . $this->id . '_filter_roles' => array(

					'label'						=>	__('Filter by Role', 'ws-form'),
					'type'						=>	'repeater',
					'help'						=>	__('Select which roles to include.', 'ws-form'),
					'meta_keys'					=>	array(

						'data_source_' . $this->id . '_roles'
					),
					'meta_keys_unique'			=>	array(

						'data_source_' . $this->id . '_roles'
					)
				),

				// Taxonomies
				'data_source_' . $this->id . '_roles' => array(

					'label'						=>	__('Role', 'ws-form'),
					'type'						=>	'select',
					'options'					=>	array(),
					'options_blank'				=>	__('Select...', 'ws-form')
				),

				// Meta data
				'data_source_' . $this->id . '_meta_keys' => array(

					'label'						=>	__('Includes Meta Keys', 'ws-form'),
					'type'						=>	'select',
					'select2'					=>	true,
					'select2_tags'				=>	true,
					'multiple'					=>	true,
					'placeholder' 				=>	__('Enter meta key(s)...', 'ws-form'),
					'help'						=>	__('Enter meta keys to include in the returned data. Type return after each meta key.', 'ws-form')
				),

				// Order By
				'data_source_' . $this->id . '_order_by' => array(

					'label'						=>	__('Order By', 'ws-form'),
					'type'						=>	'select',
					'default'					=>	'display_name',
					'options'					=>	array(

						array('value' => 'ID', 'text' => 'ID'),
						array('value' => 'display_name', 'text' => 'Display Name'),
						array('value' => 'user_name', 'text' => 'User Name'),
						array('value' => 'login', 'text' => 'Login'),
						array('value' => 'nicename', 'text' => 'Nicename'),
						array('value' => 'email', 'text' => 'Email'),
						array('value' => 'post_count', 'text' => 'Post Count'),
					)
				),

				// Order
				'data_source_' . $this->id . '_order' => array(

					'label'						=>	__('Order', 'ws-form'),
					'type'						=>	'select',
					'default'					=>	'ASC',
					'options'					=>	array(

						array('value' => 'ASC', 'text' => 'Ascending'),
						array('value' => 'DESC', 'text' => 'Descending')
					)
				),

				// Groups
				'data_source_' . $this->id . '_groups' => array(

					'label'						=>	__('Group by Role', 'ws-form'),
					'type'						=>	'checkbox',
					'default'					=>	'on',
					'show_if_groups_group'		=>	true
				),

				// Images - As Tags
				'data_source_' . $this->id . '_image_tag' => array(

					'label'						=>	__('Image URLs to &lt;img&gt; Tags', 'ws-form'),
					'type'						=>	'checkbox',
					'default'					=>	'on'
				),

				// Columns
				'data_source_' . $this->id . '_columns' => array(

					'label'						=>	__('Columns', 'ws-form'),
					'type'						=>	'repeater',
					'help'						=>	__('Select columns to return.', 'ws-form'),
					'meta_keys'					=>	array(

						'data_source_' . $this->id . '_column'
					),
					'meta_keys_unique'			=>	array(

						'data_source_' . $this->id . '_column'
					),
					'default'					=>	array(

						(object) array('data_source_' . $this->id . '_column' => 'id'),
						(object) array('data_source_' . $this->id . '_column' => 'display_name'),
						(object) array('data_source_' . $this->id . '_column' => 'nicename'),
						(object) array('data_source_' . $this->id . '_column' => 'login'),
						(object) array('data_source_' . $this->id . '_column' => 'email')
					)
				),

				// Column
				'data_source_' . $this->id . '_column' => array(

					'label'						=>	__('Column', 'ws-form'),
					'type'						=>	'select',
					'options'					=>	array(

						array('value' => 'id', 'text' => __('ID', 'ws-form')),
						array('value' => 'display_name', 'text' => __('Display Name', 'ws-form')),
						array('value' => 'nicename', 'text' => __('Nicename', 'ws-form')),
						array('value' => 'login', 'text' => __('Login', 'ws-form')),
						array('value' => 'email', 'text' => __('Email', 'ws-form')),
						array('value' => 'url', 'text' => __('Website', 'ws-form')),
						array('value' => 'avatar', 'text' => __('Avatar', 'ws-form'))
					),
					'options_blank'				=>	__('Select...', 'ws-form')
				)
			);

			// Add roles
			global $wp_roles;
			$roles = $wp_roles->roles;

			// Sort roles
			uasort($roles, function ($role_1, $role_2) {

				return $role_1['name'] < $role_2['name'] ? -1 : 1;
			});

			foreach($roles as $role_id => $role) {

				$config_meta_keys['data_source_' . $this->id . '_roles']['options'][] = array('value' => $role_id, 'text' => $role['name']);
			}


			// Add ACF
			if($this->acf_activated) {

				$options_acf = array();

				$acf_field_groups = acf_get_field_groups();

				foreach($acf_field_groups as $acf_field_group) {

					$acf_fields = acf_get_fields($acf_field_group);

					$acf_field_group_name = $acf_field_group['title'];

					WS_Form_ACF::acf_get_fields($options_acf, $acf_field_group_name, $acf_fields);
				}

				// ACF - Fields
				$config_meta_keys['data_source_' . $this->id . '_acf_fields'] = array(

					'label'						=>	__('Include ACF Fields', 'ws-form'),
					'type'						=>	'repeater',
					'help'						=>	__('Select which ACF fields to include in the returned data.', 'ws-form'),
					'meta_keys'					=>	array(

						'data_source_' . $this->id . '_acf_field_key'
					),
					'meta_keys_unique'			=>	array(

						'data_source_' . $this->id . '_acf_field_key'
					)
				);

				// ACF - Field
				$config_meta_keys['data_source_' . $this->id . '_acf_field_key'] = array(

					'label'						=>	__('ACF Field', 'ws-form'),
					'type'						=>	'select',
					'options'					=>	$options_acf,
					'options_blank'				=>	__('Select...', 'ws-form-post')
				);
			}

			// Merge
			$meta_keys = array_merge($meta_keys, $config_meta_keys);

			return $meta_keys;
		}

		// Build REST API endpoints
		public function rest_api_init() {

			// Get data source
			register_rest_route(WS_FORM_RESTFUL_NAMESPACE, '/data-source/' . $this->id . '/', array('methods' => 'POST', 'callback' => array($this, 'api_post'), 'permission_callback' => function () { return WS_Form_Common::can_user('edit_form'); }));
		}

		// api_post
		public function api_post() {

			// Get meta keys
			$meta_keys = self::get_data_source_meta_keys();

			// Read settings
			foreach($meta_keys as $meta_key) {

				$this->{$meta_key} = WS_Form_Common::get_query_var($meta_key, false);
				if(
					is_object($this->{$meta_key}) ||
					is_array($this->{$meta_key})
				) {

					$this->{$meta_key} = json_decode(json_encode($this->{$meta_key}));
				}
			}

			// Get field ID
			$field_id = WS_Form_Common::get_query_var('field_id', 0);

			// Get page
			$page = intval(WS_Form_Common::get_query_var('page', 1));

			// Get meta key
			$meta_key = WS_Form_Common::get_query_var('meta_key', 0);

			// Get meta value
			$meta_value = WS_Form_Common::get_query_var('meta_value', 0);

			// Get return data
			$get_return = self::get(false, $field_id, $page, $meta_key, $meta_value, false, true);

			// Error checking
			if($get_return['error']) {

				// Error
				return self::api_error($get_return);

			} else {

				// Success
				return $get_return;
			}
		}
	}

	new WS_Form_Data_Source_User();
