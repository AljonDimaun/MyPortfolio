<?php

	class WS_Form_CSS_Layout extends WS_Form_CSS {

		// Render
		public function render_layout() {

			// Read frameworks
			$frameworks = WS_Form_Config::get_frameworks();

			// Get framework ID
			$framework_id = WS_Form_Common::option_get('framework', 'ws-form');

			// Get framework
			$framework = $frameworks['types'][$framework_id];

			// Get column class mask
			$column_class = $framework['columns']['column_css_selector'];

			// Get form column count
			$columns = intval(WS_Form_Common::option_get('framework_column_count', 0));
			if($columns == 0) { self::db_throw_error(__('Invalid framework column count', 'ws-form')); }

			$grid_spacing = 0;
			$grid_spacing_unit = 'px';

			// Invalid Feedback
			$css_return = ".wsf-invalid-feedback,\n";
			$css_return .= "[data-select-min-max], \n";
			$css_return .= "[data-checkbox-min-max] {\n";
			$css_return .= "\tdisplay: none;\n";
			$css_return .= "}\n\n";

			$css_return .= ".wsf-validated .wsf-field:invalid ~ .wsf-invalid-feedback,\n";
			$css_return .= ".wsf-validated .wsf-field.wsf-invalid ~ .wsf-invalid-feedback,\n";
			$css_return .= ".wsf-validated [data-select-min-max]:invalid ~ .wsf-invalid-feedback,\n";
			$css_return .= ".wsf-validated [data-checkbox-min-max]:invalid ~ .wsf-invalid-feedback {\n";
			$css_return .= "\tdisplay: block;\n";
			$css_return .= "}\n\n";

			// Grid
			$css_return .= ".wsf-grid {\n";

			$css_return .= "\tdisplay: -webkit-box;\n";
			$css_return .= "\tdisplay: -ms-flexbox;\n";
			$css_return .= "\tdisplay: flex;\n";
			$css_return .= "\t-ms-flex-wrap: wrap;\n";
			$css_return .= "\tflex-wrap: wrap;\n";

			if($grid_spacing > 0) {

				$css_return .= "\tmargin-left: " . (($grid_spacing / 2) * -1) . $grid_spacing_unit . " !important;\n";
				$css_return .= "\tmargin-right: " . (($grid_spacing / 2) * -1) . $grid_spacing_unit . " !important;\n";
			}

			$css_return .= "}\n\n";

			// Tile
			$css_return .= ".wsf-tile {\n";
			$css_return .= "\tposition: relative;\n";
			$css_return .= "\twidth: 100%;\n";
			$css_return .= "\tbox-sizing: border-box;\n";

			if($grid_spacing > 0) {
				$css_return .= "\tpadding-left: " . ($grid_spacing / 2) . $grid_spacing_unit . " !important;\n";
				$css_return .= "\tpadding-right: " . ($grid_spacing / 2) . $grid_spacing_unit . " !important;\n";
			}
			$css_return .= "}\n\n";

			// Breakpoint CSS
			foreach($framework['breakpoints'] as $key => $breakpoint) {

				// Get outer breakpoint ID and name
				$breakpoint_id = $breakpoint['id'];
				$breakpoint_name = $breakpoint['name'];
				if(isset($breakpoint['min_width'])) {
					$breakpoint_min_width = $breakpoint['min_width'];
				} else {
					$breakpoint_min_width = 0;
				}

				// Output comment
				$css_return .= WS_Form_Common::comment_css($breakpoint_name);

				// Output media query
				$css_indent = '';
				if($breakpoint_min_width > 0) {

					$css_return .= "@media (min-width: " . $breakpoint_min_width . "px) {\n\n";
					$css_indent = "\t";
				}

				// Check for breakpoint specific CSS selector
				if(isset($breakpoint['column_css_selector'])) {

					$column_class_single = $breakpoint['column_css_selector'];

				} else {

					$column_class_single = $column_class;
				}

				// Run through each column
				for($column_index = 1; $column_index <= $columns; $column_index++) {

					// Build mask values for parser
					$mask_values = ['id' => $breakpoint_id, 'size' => $column_index];

					// Get single class
					$class_single = WS_Form_Common::mask_parse($column_class_single, $mask_values);

					// Build CSS selectors
					$css_return .= $css_indent . $class_single;

					$column_width_percentage = round(($column_index / $columns) * 100, 6);

					$css_return .= " {";

					$css_return .= "\n" . $css_indent . "\t-webkit-box-flex: 0 !important;";
					$css_return .= "\n" . $css_indent . "\t-ms-flex: 0 0 " . $column_width_percentage . "% !important;";
					$css_return .= "\n" . $css_indent . "\tflex: 0 0 " . $column_width_percentage . "% !important;";
					$css_return .= "\n" . $css_indent . "\tmax-width: " . $column_width_percentage . "% !important;";

					$css_return .= "\n" . $css_indent . "}\n\n";
				}

				// Close media query
				if($breakpoint_min_width > 0) {

					$css_return .= "}\n\n";
				}
			}

			// Offsets - Run through each column
			$offset_class = $framework['columns']['offset_css_selector'];

			// Breakpoint CSS
			foreach($framework['breakpoints'] as $key => $breakpoint) {

				// Get outer breakpoint ID and name
				$breakpoint_id = $breakpoint['id'];
				$breakpoint_name = $breakpoint['name'];
				if(isset($breakpoint['min_width'])) {
					$breakpoint_min_width = $breakpoint['min_width'];
				} else {
					$breakpoint_min_width = 0;
				}

				// Output comment
				$css_return .= WS_Form_Common::comment_css($breakpoint_name . ' - Offsets');

				// Output media query
				$css_indent = '';
				if($breakpoint_min_width > 0) {

					$css_return .= "@media (min-width: " . $breakpoint_min_width . "px) {\n\n";
					$css_indent = "\t";
				}

				// Check for breakpoint specific CSS selector
				if(isset($breakpoint['offset_css_selector'])) {

					$offset_class_single = $breakpoint['offset_css_selector'];

				} else {

					$offset_class_single = $offset_class;
				}

				for($column_index = 0; $column_index <= $columns; $column_index++) {

					// Build mask values for parser
					$mask_values = ['id' => $breakpoint_id, 'offset' => $column_index];

					// Get single offset
					$offset_single = WS_Form_Common::mask_parse($offset_class_single, $mask_values);

					$column_width_percentage = ($column_index / $columns) * 100;

					// Build CSS selectors
					$css_return .= $css_indent . $offset_single . " {\n";

					// Build offset CSS
					$css_return .= $css_indent . "\t-webkit-margin-start: " . $column_width_percentage . "% !important;\n";
					$css_return .= $css_indent . "\tmargin-inline-start: " . $column_width_percentage . "% !important;\n";

					$css_return .= $css_indent . "}\n\n";
				}

				// Close media query
				if($breakpoint_min_width > 0) {

					$css_return .= "}\n\n";
				}
			}

			$css_return .= ".wsf-bottom {\n";
			$css_return .= "\talign-self: flex-end !important;\n";
			$css_return .= "}\n\n";

			$css_return .= ".wsf-top {\n";
			$css_return .= "\talign-self: flex-start !important;\n";
			$css_return .= "}\n\n";

			$css_return .= ".wsf-middle {\n";
			$css_return .= "\talign-self: center !important;\n";
			$css_return .= "}\n\n";

			// Apply filters
			$css_return = apply_filters('wsf_get_layout', $css_return);

			echo $css_return;
		}
	}
