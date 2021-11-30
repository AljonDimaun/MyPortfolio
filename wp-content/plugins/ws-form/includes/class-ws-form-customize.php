<?php

	/**
	 * Manages plugin customization
	 */

	class WS_Form_Customize {

		public function __construct($wp_customize) {

			// Get skins
			$skins = WS_Form_Config::get_skins();

			foreach($skins as $skin_id => $skin) {

				// Get skin label
				$skin_label = $skin['label'];

				// Add WS Form panel
				self::add_panel($wp_customize, $skin_id, $skin_label);

				// Add sections, settings and controls
				self::add_sections($wp_customize, $skin_id, $skin);
			}

			// Add scripts
			wp_add_inline_script('customize-controls', self::customize_controls_after());
		}

		public function add_panel($wp_customize, $skin_id, $skin_label) {

			$wp_customize->add_panel($skin_id . '_panel', array(

				'priority'       	=> 200,
				'theme_supports'	=> '',
				'title'          	=> $skin_label
			));
		}

		public function add_sections($wp_customize, $skin_id, $skin) {

			// Get setting suffix
			$skin_setting_id_prefix = ($skin['setting_id_prefix'] != '') ? '_' . $skin['setting_id_prefix'] : '';

			// Get customize
			$customize_sections = WS_Form_Config::get_customize();

			// Run through each group
			foreach($customize_sections as $customize_section_id => $customize_section) {

				// Check this section applies to this skin
				if(
					isset($customize_section['skin_ids']) &&
					is_array($customize_section['skin_ids']) &&
					!in_array($skin_id, $customize_section['skin_ids'])
				) {

					continue;
				}

				// Build customize section ID
				$customize_section_id = $skin_id . '_section_' . $customize_section_id;

				// Add section
				$wp_customize->add_section(

					$customize_section_id,

					array(
						'title'    => $customize_section['heading'],
						'priority' => 10,
						'panel'    => $skin_id . '_panel',
					)
				);

				$customize_fields = $customize_section['fields'];

				foreach($customize_fields as $customize_field_id => $customize_field) {

					$setting_id = WS_FORM_IDENTIFIER . '[skin' . $skin_setting_id_prefix . '_' . $customize_field_id . ']';
					$control_id = $skin_id . '_control_' . $customize_field_id;

					$default = isset($skin['defaults'][$customize_field_id]) ? $skin['defaults'][$customize_field_id] : '';

					switch($customize_field['type']) {

						case 'checkbox' :

							$wp_customize->add_setting(

								$setting_id,

								array(
									'default'           => $default,
									'type'              => 'option'
								)
							);

						default :

							$wp_customize->add_setting(

								$setting_id,

								array(
									'default'           => $default,
									'type'              => 'option'
								)
							);
					}

					switch($customize_field['type']) {

						case 'select' :

							$wp_customize->add_control(

								$control_id,

								array(
									'label'			=> $customize_field['label'],
									'description'	=> isset($customize_field['description']) ? $customize_field['description'] : '',
									'section'		=> $customize_section_id,
									'settings'		=> $setting_id,
									'type'			=> 'select',
									'choices'		=> $customize_field['choices']
								)
							);

							break;

						case 'color' :

							$wp_customize->add_control(

								new WP_Customize_Color_Control( 

									$wp_customize, 
									$control_id,

									array(
										'label'			=> $customize_field['label'],
										'description'	=> isset($customize_field['description']) ? $customize_field['description'] : '',
										'section'		=> $customize_section_id,
										'settings'		=> $setting_id,
									)
								)
							);

							break;

						default :

							$wp_customize->add_control(

								$control_id,

								array(
									'label'       => $customize_field['label'],
									'description' => isset($customize_field['description']) ? $customize_field['description'] : '',
									'section'     => $customize_section_id,
									'settings'    => $setting_id,
									'type'        => $customize_field['type'],
									'input_attrs' => array(

										'placeholder' => $default
									)
								)
							);
					}
				}
			}
		}

		public function customize_controls_after() {

			// Work out which form to use for the preview
			$form_id = intval(WS_Form_Common::get_query_var('wsf_preview_form_id'));
			if($form_id === 0) {

				// Find a default form to use
				$ws_form_form = new WS_Form_Form();
				$form_id = $ws_form_form->db_get_preview_form_id();
			}

			if($form_id === 0) { return; }

			// Determine if a panel should open
			$wsf_panel_open = WS_Form_Common::get_query_var('wsf_panel_open');

			// Get skins
			$skins = WS_Form_Config::get_skins();

			// Build form preview URL array
			$form_preview_url_array = array();
			foreach($skins as $skin_id => $skin) {

				// Is this a conversational skin?
				$conversational = isset($skin['conversational']) ? $skin['conversational'] : false;

				// Get form preview URL
				$form_preview_url_array[$skin_id] = WS_Form_Common::get_preview_url($form_id, $skin_id, $conversational);
			}

			// Start script
			$return_script = "	wp.customize.bind('ready', function() {\n";

			// is_expanded bindings
			foreach($skins as $skin_id => $skin) {

				// Open if WS Form panel is opened
				$return_script .= sprintf("		wp.customize.panel('%s_panel', function(panel) {\n", esc_js($skin_id));
				$return_script .= "			panel.expanded.bind(function(is_expanded) {\n";
				$return_script .= "				if(is_expanded) {\n";
				$return_script .= sprintf("					wp.customize.previewer.previewUrl('%s');\n", $form_preview_url_array[$skin_id]);
				$return_script .= "				}\n";
				$return_script .= "			});\n";
				$return_script .= "		});\n";
			}

			// Determine if we should automatically open the WS Form panel
			if($wsf_panel_open) {

				// Get all skin IDs
				$skin_ids = array_keys($skins);

				// Check wsf_panel_open is valid
				if(!in_array($wsf_panel_open, $skin_ids)) { $wsf_panel_open = WS_FORM_CSS_SKIN_DEFAULT; }

				// Open immediately
				$return_script .= sprintf("		wp.customize.previewer.previewUrl('%s');\n", $form_preview_url_array[$wsf_panel_open]);
				$return_script .= sprintf("		wp.customize.panel('%s_panel').expand();\n", esc_js($wsf_panel_open));
			}

			$return_script .= '	});';

			return $return_script;
		}
	}
