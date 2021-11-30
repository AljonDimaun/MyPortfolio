<?php

	class WS_Form_WooCommerce {

		// Get fields all
		public static function woocommerce_get_fields_all($type = 'user', $post_type = false, $choices_filter = false, $raw = false, $traverse = false, $has_fields = false) {

			// Get field groups
			$woocommerce_field_groups = self::woocommerce_get_field_groups($type, $post_type);

			// WooCommerce fields
			$options_woocommerce = array();

			// Check if fields were found
			$fields_found = false;

			// Process each WooCommerce field group
			foreach($woocommerce_field_groups as $woocommerce_field_group) {

				// Get fields
				$woocommerce_fields = $woocommerce_field_group['fields'];

				// Has fields?
				if($has_fields && (count($woocommerce_fields) > 0)) { $fields_found = true; break; }

				// Get group name
				$woocommerce_field_group_name = $woocommerce_field_group['label'];

				// Process fields
				WS_Form_WooCommerce::woocommerce_get_fields_process($options_woocommerce, $woocommerce_field_group_name, $woocommerce_fields, $choices_filter, $raw, $traverse);
			}

			return $has_fields ? $fields_found : $options_woocommerce;
		}

		// Get field groups
		public static function woocommerce_get_field_groups($type = 'user', $post_type = '') {

			switch($type) {

				case 'post' :

					if(
						($post_type == 'product') ||
						($post_type === false)
					) {

						return array(

							array(

								'label' => __('Product Data', 'ws-form'),

								'fields' => array(

									'_regular_price'  => array('label' => __('Regular Price', 'woocommerce'), 'type' => 'price', 'no_map' => true),
									'_sale_price'   => array('label' => __('Sale Price', 'woocommerce'), 'type' => 'price', 'no_map' => true),
									'_manage_stock' => array('label' => __('Manage Stock', 'woocommerce'), 'type' => 'checkbox', 'no_map' => true),
									'_stock' => array('label' => __('Stock', 'woocommerce'), 'type' => 'number', 'no_map' => true),
									'_sku'  => array('label' => __('SKU', 'woocommerce'), 'type' => 'text', 'no_map' => true),
									'_weight'  => array('label' => __('Weight', 'woocommerce'), 'type' => 'number', 'no_map' => true),
									'_length'  => array('label' => __('Length', 'woocommerce'), 'type' => 'number', 'no_map' => true),
									'_width'  => array('label' => __('Width', 'woocommerce'), 'type' => 'number', 'no_map' => true),
									'_height'  => array('label' => __('Height', 'woocommerce'), 'type' => 'number', 'no_map' => true),
									'_virtual' => array('label' => __('Virtual', 'woocommerce'), 'type' => 'checkbox', 'no_map' => true)
//									'_downloadable' => array('label' => __('Downloadable', 'woocommerce'), 'type' => 'checkbox', 'no_map' => true),
								)
							)
						);
					}

					break;

				case 'user' :

					return array(

						array(

							'label' => __('Billing', 'ws-form'),

							'fields' => array(

								'billing_first_name'  => array('label' => __('Billing First Name', 'woocommerce'), 'type' => 'text'),
								'billing_last_name'   => array('label' => __('Billing Last Name', 'woocommerce'), 'type' => 'text'),
								'billing_company'     => array('label' => __('Billing Company', 'woocommerce'), 'type' => 'text'),
								'billing_address_1'   => array('label' => __('Billing Address 1', 'woocommerce'), 'type' => 'text'),
								'billing_address_2'   => array('label' => __('Billing Address 2', 'woocommerce'), 'type' => 'text'),
								'billing_city'        => array('label' => __('Billing City', 'woocommerce'), 'type' => 'text'),
								'billing_state'       => array('label' => __('Billing State', 'woocommerce'), 'type' => 'text'),
								'billing_postcode'    => array('label' => __('Billing Postal/Zip Code', 'woocommerce'), 'type' => 'text'),
								'billing_country'     => array('label' => __('Billing Country / Region', 'woocommerce'), 'type' => 'select'),
								'billing_phone'       => array('label' => __('Billing Phone Number', 'woocommerce'), 'type' => 'text'),
								'billing_email'       => array('label' => __('Billing Email Address', 'woocommerce'), 'type' => 'text')
							)
						),

						array(

							'label' => __('Shipping', 'ws-form'),

							'fields' => array(

								'shipping_first_name' => array('label' => __('Shipping First Name', 'woocommerce'), 'type' => 'text'),
								'shipping_last_name'  => array('label' => __('Shipping Last Name', 'woocommerce'), 'type' => 'text'),
								'shipping_company'    => array('label' => __('Shipping Company', 'woocommerce'), 'type' => 'text'),
								'shipping_address_1'  => array('label' => __('Shipping Address 1', 'woocommerce'), 'type' => 'text'),
								'shipping_address_2'  => array('label' => __('Shipping Address 2', 'woocommerce'), 'type' => 'text'),
								'shipping_city'       => array('label' => __('Shipping City', 'woocommerce'), 'type' => 'text'),
								'shipping_state'      => array('label' => __('Shipping State', 'woocommerce'), 'type' => 'text'),
								'shipping_postcode'   => array('label' => __('Shipping Postal/Zip Code', 'woocommerce'), 'type' => 'text'),
								'shipping_country'    => array('label' => __('Shipping Country / Region', 'woocommerce'), 'type' => 'select')
							)
						)
					);

					break;
			}

			return array();
		}

		// Get fields
		public static function woocommerce_get_fields_process(&$options_woocommerce, $woocommerce_field_group_name, $woocommerce_fields, $choices_filter, $raw, $traverse, $prefix = '') {

			foreach($woocommerce_fields as $meta_key => $woocommerce_field) {

				// Get field type
				$woocommerce_field_type = $woocommerce_field['type'];

				// Store meta box name
				$woocommerce_field['wsf_group_name'] = $woocommerce_field_group_name;

				// Only return fields that have choices
				$process_field = true;
				if(
					$choices_filter &&
					!isset($woocommerce_field['options'])
				) {
					$process_field = false;
				}

				if($process_field) {

					if($raw) {

						$options_woocommerce[$meta_key] = $woocommerce_field;

					} else {

						$options_woocommerce[$meta_key] = array('value' => $meta_key, 'text' => sprintf('%s%s - %s', $woocommerce_field_group_name, $prefix, $woocommerce_field['label']));
					}
				}
			}
		}

		// Process WooCommerce fields
		public static function woocommerce_fields_to_list_fields($fields, $group_index = 0, $section_index = 1, $field_index = 1, $depth = 0) {

			$list_fields = array();

			$wsf_group_name_last = false;

			$sort_index = $field_index;

			foreach($fields as $meta_key => $field) {

				// Get field type
				$field_type = $field['type'];

				// Get meta
				$meta = self::woocommerce_action_field_to_ws_form_meta_keys($meta_key, $field);

				// Section names
				$wsf_group_name = isset($field['wsf_group_name']) ? $field['wsf_group_name'] : false;

				if(
					($depth === 0) &&
					($wsf_group_name !== false) &&
					($wsf_group_name !== $wsf_group_name_last)
				) {

					if(
						!(
							($section_index === 0) &&
							($field_index === 1) 
						)
					) {

						$section_index++;
					}

					$wsf_group_name_last = $wsf_group_name;
				}

				$list_fields_single = array(

					'id' => 				$meta_key,
					'label' => 				$field['label'], 
					'label_field' => 		$field['label'], 
					'type' => 				$field_type,
					'action_type' =>		$field_type,
					'required' => 			isset($field['required']),
					'default_value' => 		'',
					'pattern' => 			'',
					'placeholder' => 		'',
					'group_index' =>		$group_index,
					'section_index' => 		$section_index,
					'sort_index' => 		$sort_index++,
					'visible' =>			true,
					'help' =>				'',
					'meta' => 				$meta,
					'no_map' =>				isset($field['no_map']) ? $field['no_map'] : false
				);

				$list_fields[] = $list_fields_single;
			}

			return array('list_fields' => $list_fields, 'group_index' => $group_index, 'section_index' => $section_index);
		}

		// Convert action field to WS Form meta key
		public static function woocommerce_action_field_to_ws_form_meta_keys($meta_key, $field) {

			$meta_return = array();

			switch($meta_key) {

				case 'billing_country' :
				case 'shipping_country' :

					$meta_return['data_grid_select'] = WS_Form_Common::build_data_grid_meta('data_grid_select', false, array(

							array('id' => 0, 'label' => __('Label', 'ws-form')),
							array('id' => 1, 'label' => __('Value', 'ws-form'))

						), array());

					$meta_return['select_field_value'] = 1;

					// Data source set-up
					$meta_return = WS_Form_Data_Source::get_data_source_meta('woocommerce', $meta_return);

					// Set up data source
					$meta_return['data_source_id'] = 'woocommerce';

					// Data source type
					$meta_return['data_source_woocommerce_type'] = $meta_key;

					break;

				case '_manage_stock' :
				case '_virtual' :
				case '_downloadable' :

					// Choices
					switch($meta_key) {

						case '_manage_stock' : 	$choices = array('yes' => __('Manage Stock', 'ws-form')); break;
						case '_virtual' : 		$choices = array('yes' => __('Virtual', 'ws-form')); break;
						case '_downloadable' :	$choices = array('yes' => __('Downloadable', 'ws-form')); break;
					}

					$meta_key = 'checkbox';

					// Default choice
					$default = '';

					// Remove Select...
					$meta_return['placeholder_row'] = '';

					break;

				case '_regular_price' :
				case '_sale_price' :
				case '_weight' :
				case '_length' :
				case '_width' :
				case '_height' :

					$meta_return['step'] = 'any';

					break;
			}

			if(isset($choices)) {

				// Get base meta
				$meta_return['data_grid_' . $meta_key] = WS_Form_Common::build_data_grid_meta('data_grid_' . $meta_key, false, array(

					array('id' => 0, 'label' => __('Value', 'ws-form')),
					array('id' => 1, 'label' => __('Label', 'ws-form')),

				), array());

				// Build new rows
				$rows = array();
				$id = 1;

				if(is_array($choices)) {

					foreach($choices as $value => $text) {

						$rows[] = array(

							'id'		=> $id,
							'default'	=> ($value === $default) ? 'on' : '',
							'required'	=> '',
							'disabled'	=> '',
							'hidden'	=> '',
							'data'		=> array($value, $text)
						);

						$id++;
					}
				}

				$meta_return[$meta_key . '_field_label'] = 1;

				// Modify meta
				$meta_return['data_grid_' . $meta_key]['groups'][0]['rows'] = $rows;
			}

			return $meta_return;
		}

		// Process WooCommerce fields
		public static function woocommerce_fields_to_meta_data($fields, $group_index = 0, $section_index = 1, $field_index = 1, $depth = 0) {

			$list_fields = array();
			$group_meta_data = array();
			$section_meta_data = array();

			$wsf_group_name_last = false;

			foreach($fields as $field) {

				// Section names
				$wsf_group_name = isset($field['wsf_group_name']) ? $field['wsf_group_name'] : false;

				// Group name
				if(
					($depth === 0) &&
					($wsf_group_name !== false) &&
					($wsf_group_name !== $wsf_group_name_last)
				) {

					if(empty($wsf_group_name)) { $wsf_group_name = WS_FORM_DEFAULT_GROUP_NAME; }

					if(
						!(
							($section_index === 0) &&
							($field_index === 1) 
						)
					) {

						$section_index++;
					}

					if(!isset($section_meta_data['group_' . $group_index]['section_' . $section_index])) { $section_meta_data['group_' . $group_index]['section_' . $section_index] = array(); }
					$section_meta_data['group_' . $group_index]['section_' . $section_index]['label'] = $wsf_group_name;

					$wsf_group_name_last = $wsf_group_name;
				}

				// Dummy entry
				$list_fields[] = array();

				$field_index++;
			}

			return array('list_fields' => $list_fields, 'group_meta_data' => $group_meta_data, 'section_meta_data' => $section_meta_data, 'group_index' => $group_index, 'section_index' => $section_index);
		}

		public static function woocommerce_ws_form_field_value_to_woocommerce_meta_value($meta_key, $meta_value) {

			switch($meta_key) {

				case '_manage_stock' :
				case '_virtual' :
				case '_downloadable' :

					return (empty($meta_value) || ($meta_value == 'no')) ? 'no' : 'yes';

				case '_regular_price' :
				case '_sale_price' :

					return WS_Form_Common::get_number($meta_value, 0, true);
			}

			return $meta_value;
		}

		// WooCommerce - Check post type
		public static function is_woocommerce_post_type($post_type) {

			return in_array($post_type, array('product', 'shop_order', 'shop_coupon'));
		}
	}