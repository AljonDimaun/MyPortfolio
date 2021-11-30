<?php

	class WS_Form_Data_Source_Term extends WS_Form_Data_Source {

		public $id = 'term';
		public $pro_required = false;
		public $label;
		public $label_retrieving;
		public $records_per_page = 0;

		// ACF
		public $acf_activated;

		public function __construct() {

			// Set label
			$this->label = __('Terms', 'ws-form');

			// Set label retrieving
			$this->label_retrieving = __('Retrieving Terms...', 'ws-form');

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

			// Build taxonomies
			$taxonomies = array();
			if(is_array($this->data_source_term_filter_taxonomies) && (count($this->data_source_term_filter_taxonomies) > 0)) {

				foreach($this->data_source_term_filter_taxonomies as $filter_taxonomy) {

					if(
						!isset($filter_taxonomy->{'data_source_' . $this->id . '_taxonomies'}) ||
						empty($filter_taxonomy->{'data_source_' . $this->id . '_taxonomies'})

					) { continue; }

					$taxonomies[] = $filter_taxonomy->{'data_source_' . $this->id . '_taxonomies'};
				}
			}

			// If no taxonomies are specified, set taxonomies to default list
			if(count($taxonomies) == 0) {

				$taxonomies_array = get_taxonomies(array(), 'objects');

				// Sort taxonomies
				usort($taxonomies_array, function ($taxonomy_1, $taxonomy_2) {

					return $taxonomy_1->labels->singular_name < $taxonomy_2->labels->singular_name ? -1 : 1;
				});

				foreach($taxonomies_array as $taxonomy) {

					$taxonomies[] = $taxonomy->name;
				}
			}

			// Groups
			$data_source_term_groups = ($this->data_source_term_groups == 'on');

			// Hide empty terms
			$data_source_term_terms_hide_empty = ($this->data_source_term_terms_hide_empty == 'on');

			// Hide empty terms
			$data_source_term_terms_hide_children = ($this->data_source_term_terms_hide_children == 'on');

			// Check order
			if(!in_array($this->data_source_term_order, array(

				'ASC',
				'DESC'

			))) { return self::error(__('Invalid order method', 'ws-form'), $field_id, $this, $api_request); }

			// Check order by
			if(!in_array($this->data_source_term_order_by, array(

				'none',
				'term_id',
				'name',
				'slug',
				'menu_order',

			))) { return self::error(__('Invalid order by method'), $field_id, $this, $api_request); }

			// Columns
			$meta_value->columns = array(

				(object) array('id' => 0, 'label' => __('ID', 'ws-form')),
				(object) array('id' => 1, 'label' => __('Name', 'ws-form')),
				(object) array('id' => 2, 'label' => __('Slug', 'ws-form'))
			);

			// Columns
			$columns = array();
			$column_index = 0;
			$meta_value->columns = array();

			if(!is_array($this->data_source_term_columns)) {

				return self::error(__('Invalid column data', 'ws-form'), $field_id, $this, $api_request);
			}

			foreach($this->data_source_term_columns as $column) {

				if(
					!isset($column->{'data_source_' . $this->id . '_column'}) ||
					empty($column->{'data_source_' . $this->id . '_column'})

				) { continue; }

				$column = $column->{'data_source_' . $this->id . '_column'};

				$columns[] = $column;

				switch($column) {

					case 'id' : $label = __('ID', 'ws-form'); break;
					case 'name' : $label = __('Name', 'ws-form'); break;
					case 'slug' : $label = __('Slug', 'ws-form'); break;
					case 'parent' : $label = __('Parent ID', 'ws-form'); break;
					case 'parent_name' : $label = __('Parent Name', 'ws-form'); break;
					case 'parent_slug' : $label = __('Parent Slug', 'ws-form'); break;
					case 'count' : $label = __('Count', 'ws-form'); break;
					case 'link' : $label = __('Permalink', 'ws-form'); break;
					default : $label = __('Unknown', 'ws-form');
				}

				$meta_value->columns[] = (object) array('id' => $column_index++, 'label' => $label);
			}

			// Build meta_keys
			$meta_keys = array();

			if(is_array($this->data_source_term_meta_keys) && (count($this->data_source_term_meta_keys) > 0)) {

				foreach($this->data_source_term_meta_keys as $meta_key) {

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

				if(is_array($this->data_source_term_acf_fields) && (count($this->data_source_term_acf_fields) > 0)) {

					foreach($this->data_source_term_acf_fields as $acf_field_key) {

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

			// Filter by post?
			$filter_by_options = false;
			$filter_by_post = $this->{'data_source_' . $this->id . '_filter_by_post'};
			if($filter_by_post) {

				// Get post ID
				$filter_by_post_id = $this->{'data_source_' . $this->id . '_filter_by_post_id'};
				if($filter_by_post_id == '') { $filter_by_post_id = '#post_id'; }
				$filter_by_post_id = intval(WS_Form_Common::parse_variables_process($filter_by_post_id, $form_object, false, 'text/plain'));

				if($filter_by_post_id > 0) {

					$filter_by_options = wp_get_post_terms($filter_by_post_id, $taxonomies, array( 'fields' => 'ids' ) );
				}
			}

			// Base meta
			$group = clone($meta_value->groups[0]);
			$max_num_pages = 0;

			// Form parse?
			if($no_paging) { $this->records_per_page = 0; }

			// Run through taxonomies
			$group_index = 0;
			$row_index = 1;
			foreach(($data_source_term_groups ? $taxonomies : array(false)) as $taxonomy) {

				// Calculate offset
				if($no_paging === false) {

					// API request
					$offset = (($page - 1) * $this->records_per_page);

				} else {

					// Form parse
					$offset = 0;
				}

				// get_terms args
				$args = array(

					'taxonomy' => ($this->data_source_term_groups == 'on') ? $taxonomy : $taxonomies,
					'number' => $this->records_per_page,
					'offset' => $offset,
					'order' => $this->data_source_term_order,
					'orderby' => $this->data_source_term_order_by,
					'hide_empty' => $data_source_term_terms_hide_empty
				);

				// Hide chilren
				if($data_source_term_terms_hide_children) {

					$args['parent'] = 0;
				}

				if($this->data_source_term_hierarchy && !$data_source_term_terms_hide_children) {

					// Sort terms according hierarchy structure
					$terms = self::get_terms_hierarchy($args);

				} else {

					// Run WP_Term_Query
					$wp_query = new WP_Term_Query($args);

					// Check for terms
					$terms = !empty($wp_query->terms) ? $wp_query->terms : array();
				}

				// Skip if no records
				if(count($terms) === 0) { continue; }

				// Rows
				$rows = array();
				foreach($terms as $term_index => $term) {

					$term_id = $term->term_id;

					// Filter by post?
					if($filter_by_options !== false) {

						if(!in_array($term_id, $filter_by_options)) { continue; }
					}

					// Build row data
					$row_data = array();
					foreach($columns as $column) {

						$column_value = '';
						switch($column) {

							case 'id' : $column_value = $term_id; break;
							case 'name' : $column_value = $term->name; break;
							case 'slug' : $column_value = $term->slug; break;
							case 'parent' : $column_value = $term->parent; break;
							case 'parent_name' :
							case 'parent_slug' :

								$parent_id = $term->parent;
								if($parent_id > 0) {

									$parent_term = get_term($parent_id);

									switch($column) {

										case 'parent_name' : $column_value = $parent_term->name; break;
										case 'parent_slug' : $column_value = $parent_term->slug; break;
									}

								} else {

									$column_value = '';
								}

								break;

							case 'count' : $column_value = $term->count; break;
							case 'link' : $column_value = get_term_link($term_id, $term->taxonomy); break;
						}

						$row_data[] = $column_value;
					}

					// Base columns
					$row = (object) array(

						'id'		=> $offset + $row_index++,
						'default'	=> '',
						'required'	=> '',
						'disabled'	=> '',
						'hidden'	=> '',
						'data'		=> $row_data,
						'hierarchy' => $term->hierarchy
					);

					// Add meta key columns
					if($has_meta_keys) {

						foreach($meta_keys as $meta_key) {

							$row->data[] = get_term_meta($term_id, $meta_key, true);
						}
					}

					// Add ACF fields
					if($has_acf_fields) {

						foreach($acf_field_keys as $acf_field_key) {

							$acf_field = get_field($acf_field_key, $term->taxonomy . '_' . $term_id, true);
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

											if($this->data_source_term_image_tag) {

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

										$row_data = get_field($acf_field_key, $term_id, false);
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

				// Term label
				if($data_source_term_groups) {

					$taxonomy_object = get_taxonomy($taxonomy);
					$meta_value->groups[$group_index]->label = $taxonomy_object->labels->singular_name;

				} else {

					$meta_value->groups[$group_index]->label = $this->label;
				}

				// Rows
				$meta_value->groups[$group_index]->rows = $rows;

				// Enable optgroups
				if(count($taxonomies) > 1) {

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

		// Sort terms according to hierarchy
		public function get_terms_hierarchy($args, $parent = 0, $hierarchy = 0, $terms = array()) {

			// Add parent to args
			$args['parent'] = $parent;

			// Run WP_Term_Query
			$wp_query = new WP_Term_Query($args);

			// Check for terms
			$wp_query_terms = !empty($wp_query->terms) ? $wp_query->terms : array();

			// Check for children
			foreach($wp_query_terms as $term) {

				// Add hierarchy parameter to term
				$term->hierarchy = $hierarchy;

				// Add term to terms array
				$terms[] = $term;

				// Look for child terms
				$terms = self::get_terms_hierarchy($args, $term->term_id, $hierarchy + 1, $terms);
			}

			return $terms;
		}

		// Get meta keys
		public function get_data_source_meta_keys() {

			$meta_keys = array_merge(

				array('data_source_' . $this->id . '_filter_taxonomies'),
				$this->acf_activated ? array('data_source_'. $this->id . '_acf_fields') : array(),
				array('data_source_' . $this->id . '_meta_keys',
				'data_source_' . $this->id . '_order_by',
				'data_source_' . $this->id . '_order',
				'data_source_' . $this->id . '_groups',
				'data_source_' . $this->id . '_hierarchy',
				'data_source_' . $this->id . '_terms_hide_children',
				'data_source_' . $this->id . '_terms_hide_empty',
				'data_source_' . $this->id . '_image_tag',
				'data_source_' . $this->id . '_filter_by_post',
				'data_source_' . $this->id . '_filter_by_post_id',
				'data_source_' . $this->id . '_columns')
//				['data_source_recurrence']
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

				// Filter - Taxonomy
				'data_source_' . $this->id . '_filter_taxonomies' => array(

					'label'						=>	__('Filter by Taxonomy', 'ws-form'),
					'type'						=>	'repeater',
					'help'						=>	__('Select which taxonomies to include.', 'ws-form'),
					'meta_keys'					=>	array(

						'data_source_' . $this->id . '_taxonomies'
					),
					'meta_keys_unique'			=>	array(

						'data_source_' . $this->id . '_taxonomies'
					)
				),

				// Taxonomies
				'data_source_' . $this->id . '_taxonomies' => array(

					'label'						=>	__('Taxonomy', 'ws-form'),
					'type'						=>	'select',
					'options'					=>	array(),
					'options_blank'				=>	__('Select...', 'ws-form')
				),

				// Filter by Post
				'data_source_' . $this->id . '_filter_by_post' => array(

					'label'						=>	__('Filter by Post', 'ws-form'),
					'type'						=>	'checkbox',
					'default'					=>	'',
					'help'						=>	__('Filter the terms by those selected in a post.'),
					'condition'					=>	array(

						array(

							'logic'				=>	'!=',
							'meta_key'			=>	'data_source_' . $this->id . '_field_key',
							'meta_value'		=>	''
						)
					)
				),

				// Filter by Post ID
				'data_source_' . $this->id . '_filter_by_post_id' => array(

					'label'						=>	__('Post ID', 'ws-form'),
					'type'						=>	'text',
					'default'					=>	'',
					'placeholder'				=>	'#post_id',
					'help'						=>	__('Choose the post ID to filter by. This can be a number or WS Form variable. If blank, the ID of the post the form is shown on will be used.'),
					'condition'					=>	array(

						array(

							'logic_previous'	=>	'&&',
							'logic'				=>	'==',
							'meta_key'			=>	'data_source_' . $this->id . '_filter_by_post'
						)
					)
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
					'default'					=>	'name',
					'options'					=>	array(

						array('value' => 'none', 'text' => 'None'),
						array('value' => 'term_id', 'text' => 'ID'),
						array('value' => 'name', 'text' => 'Name'),
						array('value' => 'slug', 'text' => 'Slug'),
						array('value' => 'menu_order', 'text' => 'Menu Order')
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

					'label'						=>	__('Group by Taxonomy', 'ws-form'),
					'type'						=>	'checkbox',
					'default'					=>	'on',
					'show_if_groups_group'		=>	true
				),

				// Hierachy
				'data_source_' . $this->id . '_hierarchy' => array(

					'label'						=>	__('Display as Hierarchy', 'ws-form'),
					'type'						=>	'checkbox',
					'default'					=>	'on',
					'condition'					=>	array(

						array(

							'logic'				=>	'!=',
							'meta_key'			=>	'data_grid_rows_randomize',
							'meta_value'		=>	'on'
						),

						array(

							'logic'				=>	'==',
							'meta_key'			=>	'orientation',
							'meta_value'		=>	''
						)
					)
				),

				// Terms - Hide Children
				'data_source_' . $this->id . '_terms_hide_children' => array(

					'label'						=>	__('Hide Child Terms', 'ws-form'),
					'type'						=>	'checkbox',
					'default'					=>	'',
					'help'						=>	__('Whether to hide any child terms in a hierarchy.', 'ws-form')
				),

				// Terms - Hide Empty
				'data_source_' . $this->id . '_terms_hide_empty' => array(

					'label'						=>	__('Hide Empty Terms', 'ws-form'),
					'type'						=>	'checkbox',
					'default'					=>	'',
					'help'						=>	__('Whether to hide terms not assigned to any posts.', 'ws-form')
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
						(object) array('data_source_' . $this->id . '_column' => 'name'),
						(object) array('data_source_' . $this->id . '_column' => 'slug')
					)
				),

				// Column
				'data_source_' . $this->id . '_column' => array(

					'label'						=>	__('Column', 'ws-form'),
					'type'						=>	'select',
					'options'					=>	array(

						array('value' => 'id', 'text' => __('ID', 'ws-form')),
						array('value' => 'name', 'text' => __('Name', 'ws-form')),
						array('value' => 'slug', 'text' => __('Slug', 'ws-form')),
						array('value' => 'parent', 'text' => __('Parent ID', 'ws-form')),
						array('value' => 'parent_name', 'text' => __('Parent Name', 'ws-form')),
						array('value' => 'parent_slug', 'text' => __('Parent Slug', 'ws-form')),
						array('value' => 'count', 'text' => __('Count', 'ws-form')),
						array('value' => 'link', 'text' => __('Permalink', 'ws-form'))
					),
					'options_blank'				=>	__('Select...', 'ws-form')
				)
			);

			// Add taxonomies
			$taxonomies = get_taxonomies(array(), 'objects');

			// Sort taxonomies
			usort($taxonomies, function ($taxonomy_1, $taxonomy_2) {

				return $taxonomy_1->labels->singular_name < $taxonomy_2->labels->singular_name ? -1 : 1;
			});

			foreach($taxonomies as $taxonomy) {

				if($taxonomy->_builtin && !$taxonomy->public) continue;

				$text = $taxonomy->labels->singular_name . ' (' . $taxonomy->name . ')';

				$config_meta_keys['data_source_' . $this->id . '_taxonomies']['options'][] = array('value' => $taxonomy->name, 'text' => $text);
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

	new WS_Form_Data_Source_Term();
