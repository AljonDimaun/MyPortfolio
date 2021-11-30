<?php

	class WS_Form_CSS_Admin extends WS_Form_CSS {

		// Get
		public function get_admin() {

			// Get form column count
			$columns = intval(WS_Form_Common::option_get('framework_column_count', 0));
			if($columns == 0) { self::db_throw_error(__('Invalid framework column count', 'ws-form')); }

			// Read frameworks
			$frameworks = WS_Form_Config::get_frameworks();

			// Get framework ID
			$framework_id = WS_Form_Common::option_get('framework', 'ws-form');

			// Get framework
			$framework = $frameworks['types'][$framework_id];

			// Get column class mask
			$column_class = $framework['columns']['column_css_selector'];

			// Get current framework breakpoints
			$breakpoints_outer = $framework['breakpoints'];
			$breakpoints_inner = $framework['breakpoints'];

			// Build CSS
			$css_return = ".wsf-group:before {\n\tbackground-image: repeating-linear-gradient(to right, #E5E5E5, #E5E5E5 calc((100% / $columns) - 12px), transparent calc((100% / $columns) - 12px), transparent calc(100% / $columns));\n\tbackground-size: calc(100% + 12px) 100%;\n}\n\n";
			$css_return .= ".wsf-section > .wsf-section-inner:before {\n\tbackground-image: repeating-linear-gradient(to right, #E5E5E5, #E5E5E5 calc((100% / $columns) - 12px), transparent calc((100% / $columns) - 12px), transparent calc(100% / $columns));\n\tbackground-size: calc(100% - 12px) 100%;\n\tbackground-position-x: 12px;\n}\n\n";

			// Grid
			$css_return .= ".wsf-sections, .wsf-fields {\n";

			$css_return .= "\tdisplay: -webkit-box;\n";
			$css_return .= "\tdisplay: -ms-flexbox;\n";
			$css_return .= "\tdisplay: flex;\n";
			$css_return .= "\t-ms-flex-wrap: wrap;\n";
			$css_return .= "\tflex-wrap: wrap;\n";

			$css_return .= "}\n\n";

			$breakpoint_outer_index = 0;
			foreach($breakpoints_outer as $key_outer => $breakpoint_outer) {

				// Get outer breakpoint ID and name
				$breakpoint_outer_id = $breakpoint_outer['id'];
				$breakpoint_outer_name = $breakpoint_outer['name'];

				// Output comment
				$css_return .= WS_Form_Common::comment_css($breakpoint_outer_name);

				// Add classes for breakpoint widths to resize admin
				if(WS_Form_Common::option_get('helper_breakpoint_width', false)) {

					// Output max-width statements
					if($breakpoint_outer_index != (count($breakpoints_outer) - 1)) {

						if(!isset($breakpoint_outer['admin_max_width'])) {

							self::db_throw_error(__('Admin max width not defined: ' . $breakpoint_outer_id, 'ws-form'));

						} else {

							$breakpoint_outer_max_width = $breakpoint_outer['admin_max_width'];
						}

						$css_return .= "#wsf-form[data-breakpoint=\"" . $breakpoint_outer_id . "\"] { max-width: " . $breakpoint_outer_max_width . "px; }\n\n";
					}
				}

				// Check for breakpoint specific CSS selector
				if(isset($breakpoint_outer['column_css_selector'])) {

					$column_class_single = $breakpoint_outer['column_css_selector'];

				} else {

					$column_class_single = $column_class;
				}

				// Columns - Run through each column
				for($column_index = 1; $column_index <= $columns; $column_index++) {

					// Create CSS for each column and each breakpoint
					$breakpoint_inner_index = 1;
					foreach($breakpoints_inner as $key_inner => $breakpoint_inner) {

						// Get inner breakpoint ID
						$breakpoint_inner_id = $breakpoint_inner['id'];

						// Build mask values for parser
						$mask_values = ['id' => $breakpoint_outer_id, 'size' => $column_index];

						// COLUMN

						// Get single class
						$class_single = WS_Form_Common::mask_parse($column_class_single, $mask_values);

						// Build CSS selectors
						$css_return .= "#wsf-form[data-breakpoint=\"" . $breakpoint_inner_id . '"] ' . $class_single;

						// Get key of top breakpoint (we'll remove this for the next run)
						if($breakpoint_inner_index == 1) { $breakpoint_inner_key_to_delete = $key_inner; }

						if($breakpoint_inner_index == count($breakpoints_inner)) {

							$column_width_percentage = ($column_index / $columns) * 100;

							$css_return .= " {";

							$css_return .= "\n\t-webkit-box-flex: 0;";
							$css_return .= "\n\t-ms-flex: 0 0 " . $column_width_percentage . "%;";
							$css_return .= "\n\tflex: 0 0 " . $column_width_percentage . "%;";
							$css_return .= "\n\tmax-width: " . $column_width_percentage . "%;";

							$css_return .= "\n}\n\n";

						} else {

							// Add comma (not at last inner breakpoint yet)
							$css_return .= ",\n";
						}

						$breakpoint_inner_index++;
					}
				}

				// Take top key off the inner breakpoints
				unset($breakpoints_inner[$breakpoint_inner_key_to_delete]);

				$breakpoint_outer_index++;
			}

			// Offsets - Run through each column
			$offset_class = $framework['columns']['offset_css_selector'];

			// Get current framework breakpoints
			$breakpoints_outer = $framework['breakpoints'];
			$breakpoints_inner = $framework['breakpoints'];

			foreach($breakpoints_outer as $key_outer => $breakpoint_outer) {

				// Get outer breakpoint ID and name
				$breakpoint_outer_id = $breakpoint_outer['id'];
				$breakpoint_outer_name = $breakpoint_outer['name'];

				// Check for breakpoint specific CSS selector
				if(isset($breakpoint_outer['offset_css_selector'])) {

					$offset_class_single = $breakpoint_outer['offset_css_selector'];

				} else {

					$offset_class_single = $offset_class;
				}

				// Output comment
				$css_return .= WS_Form_Common::comment_css($breakpoint_outer_name . ' - Offsets');

				for($column_index = 0; $column_index < $columns; $column_index++) {

					// Create CSS for each column and each breakpoint
					$breakpoint_inner_index = 1;
					foreach($breakpoints_inner as $key_inner => $breakpoint_inner) {

						// Get inner breakpoint ID
						$breakpoint_inner_id = $breakpoint_inner['id'];

						// Build mask values for parser
						$mask_values = ['id' => $breakpoint_outer_id, 'offset' => $column_index];

						// Get single offset
						$offset_single = WS_Form_Common::mask_parse($offset_class_single, $mask_values);

						// Get key of top breakpoint (we'll remove this for the next run)
						if($breakpoint_inner_index == 1) { $breakpoint_inner_key_to_delete = $key_inner; }

						// Build CSS selectors
						$css_return .= "#wsf-form[data-breakpoint=\"" . $breakpoint_inner_id . '"] ' . $offset_single;

						// Get key of top breakpoint (we'll remove this for the next run)
						if($breakpoint_inner_index == 1) { $breakpoint_inner_key_to_delete = $key_inner; }

						if($breakpoint_inner_index == count($breakpoints_inner)) {

							$column_width_percentage = ($column_index / $columns) * 100;

							// Build offset CSS
							$css_return .= " {";

							$css_return .= "\n\tbackground-size: " . $column_width_percentage . "%;";
							$css_return .= "\n\tmargin-" . (is_rtl() ? 'right' : 'left') . ": " . $column_width_percentage . "%;";

							$css_return .= "\n}\n\n";

						} else {

							// Add comma (not at last inner breakpoint yet)
							$css_return .= ",\n";
						}

						$breakpoint_inner_index++;
					}
				}

				// Take top key off the inner breakpoints
				unset($breakpoints_inner[$breakpoint_inner_key_to_delete]);
			}

			// Apply filters
			$css_return = apply_filters('wsf_get_admin', $css_return);

			// Minify
			$css_minify = !SCRIPT_DEBUG;

			return $css_minify ? self::minify($css_return) : $css_return;
		}
	}
