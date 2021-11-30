<?php

	class WS_Form_Template extends WS_Form_Core {

		public $id = false;
		public $label = '';
		public $file_json = false;
		public $file_config = false;
		public $index = false;
		public $category_index = false;
		public $form_json = '';
		public $form_object = false;
		public $svg = '';

		public $pro_required = false;

		public $action_id = false;

		public $type = 'form';

		public $config_full = array();

		public function __construct() {

			add_filter('wsf_template_section_config_files', array($this, 'user_section_template_config_file'));

			global $wpdb;
		}

		// Read template
		public function read($include_file_paths = true, $get_svg = false, $svg_width = false, $svg_height = false, $get_form_object = true) {

			self::db_check_id();

			$templates = self::read_all(false, $include_file_paths, $get_svg, $svg_width, $svg_height, $get_form_object, $this->id);
	
			if(isset($templates[$this->id])) {

				$template = $templates[$this->id];

				// Set class variables
				$this->id = $template->id;
				$this->label = $template->label;
				$this->index = $template->index;
				$this->category_index = $template->category_index;

				// Include file paths
				if($include_file_paths) {

					$this->file_json = $template->file_json;
					$this->file_config = $template->file_config;
				}

				// Get SVG
				if($get_svg) {

					$this->svg = $template->svg;
				}

				// Get form object
				if($get_form_object) {

					$this->form_object = $template->form_object;
					$this->form_json = $template->form_json;
				}

				return $this;
			}

			self::db_throw_error(sprintf(__('Template not found: %s', 'ws-form'), $this->id));
		}

		// Get templates (read_config flattened)
		public function read_all($config_files = false, $include_file_paths = true, $get_svg = true, $svg_width = false, $svg_height = false, $get_form_object = false, $template_id_filter = false) {

			$config = self::read_config($config_files, $include_file_paths, $get_svg, $svg_width, $svg_height, $get_form_object, $template_id_filter);

			$templates = array();

			foreach($config as $template_category) {

				foreach($template_category->templates as $template) {

					$templates[$template->id] = $template;
				}
			}

			return $templates;
		}

		// Read config
		public function read_config($config_files = false, $include_file_paths = true, $get_svg = true, $svg_width = false, $svg_height = false, $get_form_object = false, $template_id_filter = false) {

			if($svg_width === false) { $svg_width = self::get_default_svg_width(); }
			if($svg_height === false) { $svg_height = self::get_default_svg_height(); }

			// Check type
			self::db_check_type();

			// Reset full config
			$this->config_full = array();

			// Run filter (to allow appending of additional config files)
			if($config_files === false) {

				// Core
				$config_files = array(sprintf('%sincludes/templates/%s/config.json', WS_FORM_PLUGIN_DIR_PATH, $this->type));

				// Legacy
				if($this->type == 'form') {

					$config_files = apply_filters('wsf_wizard_config_files', $config_files);	// Legacy
				}

				$config_files = apply_filters(sprintf('wsf_template_%s_config_files', $this->type), $config_files);
			}

			$config = array();

			$popular_templates = array();

			foreach($config_files as $config_file) {

				// Check integrity of config file
				if(!self::config_check($config_file)) { continue; }

				// Read config file
				$config_file_string = file_get_contents($config_file);
				if($config_file_string === false) { self::db_throw_error(sprintf(__('Unable to read template config file: %s', 'ws-form'), $config_file)); }

				// JSON decode
				$config_object = json_decode($config_file_string);
				if(is_null($config_object)) { self::db_throw_error(sprintf(__('Unable to JSON decode template config file: %s', 'ws-form'), $config_file)); }

				// Legacy
				if(isset($config_object->wizard_categories)) {

					$config_object->template_categories = $config_object->wizard_categories;
					unset($config_object->wizard_categories);
				}

				foreach($config_object->template_categories as $template_category_index => $template_category) {

					// Legacy
					if(isset($config_object->template_categories[$template_category_index]->wizards)) {

						$config_object->template_categories[$template_category_index]->templates = $config_object->template_categories[$template_category_index]->wizards;
						unset($config_object->template_categories[$template_category_index]->wizards);
					}

					// Legacy
					if(isset($template_category->wizards)) {

						$template_category->templates = $template_category->wizards;
						unset($template_category->wizards);
					}

					$file_path = $config_object->template_categories[$template_category_index]->file_path;

					if(!$include_file_paths) {

						unset($config_object->template_categories[$template_category_index]->file_path);
					}

					// Sort templates
					usort($template_category->templates, function($a, $b) {

						return ($a->label === $b->label) ? 0 : (($a->label > $b->label) ? 1 : 0);
					});

					foreach($template_category->templates as $template_index => $template) {

						if(
							($template_id_filter !== false) &&
							($template->id !== $template_id_filter)
						) {

							continue;
						}

						// Pro required
						$pro_required = (isset($template->pro_required) ? $template->pro_required : false);
						if($pro_required) {

							unset($config_object->template_categories[$template_category_index]->templates[$template_index]);
							continue;
						}
						// Indexes (used by delete)
						$config_object->template_categories[$template_category_index]->templates[$template_index]->category_index = $template_category_index;
						$config_object->template_categories[$template_category_index]->templates[$template_index]->index = $template_index;

						// Build JSON file path
						$file_json = sprintf('%s/%s%s', dirname($config_file), $file_path, $config_object->template_categories[$template_category_index]->templates[$template_index]->file_json);

						// File json
						if($include_file_paths) {

							// Config file
							$config_object->template_categories[$template_category_index]->templates[$template_index]->file_config = $config_file;

							// Template file
							$config_object->template_categories[$template_category_index]->templates[$template_index]->file_json = $file_json;

						} else {

							unset($config_object->template_categories[$template_category_index]->templates[$template_index]->file_json);
						}

						// Form objet
						if($get_svg || $get_form_object) {

							if(!file_exists($file_json)) { self::db_throw_error(sprintf(__('Unable to read template JSON file: %s', 'ws-form'), $file_json)); }

							$form_json = file_get_contents($file_json);

							$config_object->template_categories[$template_category_index]->templates[$template_index]->form_json = $form_json;

							$form_object = WS_Form_Common::get_form_object_from_json($form_json);

							$config_object->template_categories[$template_category_index]->templates[$template_index]->form_object = $form_object;

							// Checksum repair
							if(
								WS_FORM_TEMPLATE_CHECKSUM_REPAIR &&
								!WS_Form_Common::form_object_checksum_check($form_object)
							) {

								unset($form_object->checksum);
								$checksum = md5(json_encode($form_object));
								$form_object->checksum = $checksum;
								file_put_contents($file_json, json_encode($form_object));
							}
						}

						// SVG
						if($get_svg) {

							$ws_form_form = new WS_Form_Form();
							$svg = $ws_form_form->get_svg_from_form_object($form_object, false, $svg_width, $svg_height);
							$svg = str_replace('#label', htmlentities($template->label), $svg);
							$config_object->template_categories[$template_category_index]->templates[$template_index]->svg = $svg;

							// Release memory
							$svg = $ws_form_form = null;

						} else {

							$config_object->template_categories[$template_category_index]->templates[$template_index]->svg = '';
						}

						$form_object = null;

						// Pro required
						$config_object->template_categories[$template_category_index]->templates[$template_index]->pro_required = !WS_Form_Common::is_edition($pro_required ? 'pro' : 'basic');

						// Preview URL
						$preview_url = isset($template->preview_url) ? $template->preview_url : false;
						if($preview_url === true) {

							$preview_url = WS_Form_Common::get_plugin_website_url(sprintf('/template/%s/', sanitize_title($template->label)), 'add_form');
						}
						$config_object->template_categories[$template_category_index]->templates[$template_index]->preview_url = $preview_url;

						// Popular?
						$popular = isset($template->popular) ? $template->popular : false;
						if($popular) {

							$popular_templates[] = clone $template;
						}
					}
				}

				$config = array_merge($config, $config_object->template_categories);
			}

			// Build popular category
			if(count($popular_templates) > 0) {

				// Sort templates
				usort($popular_templates, function($a, $b) {

					return ($a->label === $b->label) ? 0 : (($a->label > $b->label) ? 1 : 0);
				});

				// Insert at beginning of config
				$config[] = (object) array(

					'id'			=>	'popular',
					'label' 		=> 	'Popular',
					'file_path'		=>	'',
					'templates'		=>	$popular_templates,
					'priority'		=>	190
				);
			}

			return $config;
		}

		// Create from form object
		public function create_from_form_object($form_object) {

			// Get config file name and path
			$user_template_config_file_return = self::user_template_config_file($this->type);
			$file_config = $user_template_config_file_return['file_config'];
			$file_path = $user_template_config_file_return['file_path'];

			// Load config file
			if(!file_exists($file_config)) {

				parent::db_throw_error(sprintf(__('Unable to open config.json file: %s', 'ws-form'), $file_config));
			}
			$config_file_json = file_get_contents($file_config);

			// JSON decode config file
			$config_object = json_decode($config_file_json);
			if(is_null($config_object)) {

				parent::db_throw_error(sprintf(__('Unable to decode config.json file: %s', 'ws-form'), $file_config));
			}

			// Build template file path
			$template_file_path = sprintf('%s/%s', $file_path, $config_object->template_categories[0]->file_path);

			// Build template ID
			$template_id = $template_id_base = strtolower(sanitize_file_name($form_object->label));

			// Check for duplicate template ID
			$templates = self::read_all();

			$duplicate_index = 1;

			do {

				$duplicate_found = false;

				if(isset($templates[$template_id])) {

					$template_id = sprintf('%s-%u', $template_id_base, $duplicate_index);
					$duplicate_index ++;
					$duplicate_found = true;
				}

			} while($duplicate_found);

			// Build template file name
			$template_file_name = sprintf('wsf-%s-%s.json', $this->type, $template_id);

			// Create new template
			$template = array(

				'id'			=> $template_id,
				'label' 		=> $form_object->label,
				'file_json'		=> $template_file_name,
				'preview_url'	=> false
			);

			// Add template
			$config_object->template_categories[0]->templates[] = $template;

			// Write config file
			if(file_put_contents($file_config, wp_json_encode($config_object)) === false) {

				parent::db_throw_error(sprintf(__('Unable to write config.json file: %s', 'ws-form'), $file_config));
			}

			// Write template file
			$template_file_name = sprintf('%s%s', $template_file_path, $template_file_name);

			if(file_put_contents($template_file_name, wp_json_encode($form_object)) === false) {

				parent::db_throw_error(sprintf(__('Unable to write template file: %s', 'ws-form'), $template_file_name));
			}
		}

		// User section templates
		public function user_section_template_config_file($config_files) {

			$user_template_config_file_return = self::user_template_config_file('section');

			$config_files[] = $user_template_config_file_return['file_config'];

			return $config_files;
		}

		public function user_template_config_file($type) {

			// Get / create template path hash
			$hash = WS_Form_Common::option_get('template_path_hash_user', md5(wp_generate_password()), true);

			// Build path
			$config_file_path = sprintf('templates/%s-%s', $type, $hash);

			// Get / create upload directory
			$upload_dir_create_return = WS_Form_Common::upload_dir_create($config_file_path);

			// Get full path
			$config_file_path = $upload_dir_create_return['dir'];

			// Build config file name
			$config_file_name = sprintf('%s/config.json', $config_file_path, $config_file_path);

			// Check if config.json file exists
			if(!file_exists($config_file_name)) {

				// Build default config file content
				$config_file_array = array(

					'template_categories' => array(

						array(

							'id'				=> 'wsfuser',
							'label' 			=> __('My Sections', 'ws-form'),
							'file_path'			=> 'user/',
							'templates'			=> [],
							'upload'			=> true,
							'download'			=> true,
							'delete'			=> true,
							'priority'			=> 200
						)
					)
				);

				// JSON encode config
				$config_file_json = wp_json_encode($config_file_array);

				// Create config.json file
				file_put_contents($config_file_name, $config_file_json);
			}

			// Check if config.json file path exists
			$template_category_file_path = sprintf('%s/user', $config_file_path);
			if(!file_exists($template_category_file_path)) {

				wp_mkdir_p($template_category_file_path);
			}

			return array('file_path' => $config_file_path, 'file_config' => $config_file_name);
		}

		// Build SVG from form
		public function get_svg($svg_width = false, $svg_height = false) {

			self::db_check_id();
			self::read(false, true, $svg_width, $svg_height);

			return $this->svg;
		}

		// Get templates for each action installed
		public function db_get_actions() {

			$return_array = array();

			if(!isset(WS_Form_Action::$actions)) { parent::db_throw_error(__('No actions installed', 'ws-form')); }

			// Capabilities required of each action
			$capabilities_required = array('get_lists', 'get_list', 'get_list_fields');

			// Get actions that have above capabilities
			$actions = WS_Form_Action::get_actions_with_capabilities($capabilities_required);

			// Run through each action
			foreach($actions as $action) {

				// Add to return array
				$return_array[] = (object) array(

					'id'					=>	$action->id,
					'label'					=>	$action->label,
					'reload'				=>	isset($action->add_new_reload) ? $action->add_new_reload : true,
					'list_sub_modal_label'	=>	isset($action->list_sub_modal_label) ? $action->list_sub_modal_label : false
				);
			}

			return $return_array;
		}

		// Get templates for each action installed
		public function db_get_action_templates() {

			$return_array = array();

			if(!isset(WS_Form_Action::$actions)) { parent::db_throw_error(__('No actions installed', 'ws-form')); }

			// Check action ID
			self::db_check_action_id();

			// Capabilities required of each action
			$capabilities_required = array('get_lists', 'get_list', 'get_list_fields');

			// Get actions that have above capabilities
			$actions = WS_Form_Action::get_actions_with_capabilities($capabilities_required);

			if(!isset($actions[$this->action_id])) { parent::db_throw_error(__('Action not compatible with this function', 'ws-form')); }

			$action = $actions[$this->action_id];

			// Labels
			$field_label = isset($action->field_label) ? $action->field_label : false;
			$record_label = isset($action->record_label) ? $action->record_label : false;

			// Get lists
			$lists = $action->get_lists();

			foreach($lists as $list) {

				// Add to return array
				$return_array[] = array(

					'id'			=>	$list['id'],
					'label'			=>	$list['label'],
					'field_count'	=>	$list['field_count'],
					'record_count'	=>	$list['record_count'],
					'list_sub'		=>	isset($list['list_sub']) ? $list['list_sub'] : false,
					'svg'			=>	WS_Form_Action::get_svg($this->action_id, $list['id'], $list['label'], $list['field_count'], $list['record_count'], $field_label, $record_label)
				);
			}

			return $return_array;
		}

		// Render template category
		public function template_category_render($template_category, $button_class = 'wsf-button wsf-button-primary wsf-button-full', $action_id = 'template') {

			// SVG defaults
			$svg_width = WS_FORM_TEMPLATE_SVG_WIDTH_FORM;
			$svg_height = WS_FORM_TEMPLATE_SVG_HEIGHT_FORM;

			// Colors
			$color_form_background = WS_Form_Common::option_get('skin_color_form_background');
			if($color_form_background == '') { $color_form_background = '#ffffff'; }

			$color_default = WS_Form_Common::option_get('skin_color_default');
			$color_default_inverted = WS_Form_Common::option_get('skin_color_default_inverted');
			$color_default_lighter = WS_Form_Common::option_get('skin_color_default_lighter');
?>
<!-- Blank -->
<li>
<div class="wsf-template" data-id="blank">
	<svg class="wsf-responsive" viewBox="0 0 <?php echo esc_attr($svg_width); ?> <?php echo esc_attr($svg_height); ?>"><rect height="100%" width="100%" fill="<?php echo esc_attr($color_form_background); ?>"/><text fill="<?php echo esc_attr($color_default) ?>" class="wsf-template-title"><tspan x="<?php echo is_rtl() ? esc_attr($svg_width - 5) : 5; ?>" y="16"><?php esc_html_e('Blank', 'ws-form'); ?></tspan></text></svg>
	<div class="wsf-template-actions">
		<button class="wsf-button wsf-button-primary wsf-button-full" data-action="wsf-add-blank"><?php esc_html_e('Use Template', 'ws-form'); ?></button>
	</div>
</div>
</li>
<!-- /Blank -->
<?php
			if(isset($template_category->templates)) {

				// Loop through templates
				foreach ($template_category->templates as $template)  {

?><li<?php if($template->pro_required) { ?> class="wsf-pro-required"<?php } ?>>
<div class="wsf-template" title="<?php echo esc_html($template->label); ?>">
<?php
					// Parse SVG
					echo $template->svg;	 // phpcs:ignore
?>
<div class="wsf-template-actions">
<?php
					if($template->pro_required) {
?>
	<a class="wsf-button wsf-button-primary wsf-button-full" href="<?php echo esc_attr(WS_Form_Common::get_plugin_website_url('', 'add_form')); ?>" target="_blank"><?php esc_html_e('Upgrade to PRO', 'ws-form'); ?></a>
<?php
					} else {
?>
	<button class="wsf-button wsf-button-primary wsf-button-full" data-action="wsf-add-<?php echo esc_attr($action_id); ?>" data-id="<?php echo esc_attr($template->id); ?>"><?php esc_html_e('Use Template', 'ws-form'); ?></button>
<?php
					}

					if($template->preview_url !== false) {
?>
	<a class="wsf-preview" href="<?php echo esc_attr($template->preview_url); ?>" target="_blank"><?php WS_Form_Common::render_icon_16_svg('visible'); ?> <?php esc_html_e('Preview Template', 'ws-form'); ?></a>
<?php
					}
?>
	</div>
</div>
</li>
<?php
				}
			}
		}

		// Legacy
		public function wizard_category_render($template_category, $button_class = 'wsf-button wsf-button-primary wsf-button-full') {

			return self::template_category_render($template_category, $button_class, 'wizard');
		}

		// Get default SVG width
		public function get_default_svg_width() {

			switch($this->type) {

				case 'section' :

					return WS_FORM_TEMPLATE_SVG_WIDTH_SECTION;
					break;

				default :

					return WS_FORM_TEMPLATE_SVG_WIDTH_FORM;
			}
		}

		// Get default SVG height
		public function get_default_svg_height() {

			switch($this->type) {

				case 'section' :

					return WS_FORM_TEMPLATE_SVG_HEIGHT_SECTION;
					break;

				default :

					return WS_FORM_TEMPLATE_SVG_HEIGHT_FORM;
			}
		}

		// Get settings
		public function get_settings() {

			$config = self::read_config(false, false, true);

			// Order template categories by priority, then label
			uasort($config, function($a, $b) {

				$pa = isset($a->priority) ? $a->priority : 0;
				$pb = isset($b->priority) ? $b->priority : 0;

				if($pa === $pb) {

					return ($a->label === $b->label) ? 0 : (($a->label > $b->label) ? 1 : -1);

				} else {

					return ($pa < $pb) ? 1 : -1;
				}
			});

			$config = array_values($config);

			return $config;
		}

		// Config file integrity check
		public function config_check($config_file) {

			$config_file_rewrite = false;

			// Load config file
			if(!file_exists($config_file)) { return false; }
			$config_file_json = file_get_contents($config_file);

			// JSON decode config file
			$config_object = json_decode($config_file_json);
			if(is_null($config_object)) { return false; }

			// Get path
			$config_file_pathinfo = pathinfo($config_file);
			$config_file_path = $config_file_pathinfo['dirname'];

			// Legacy
			if(isset($config_object->wizard_categories)) {

				$config_object->template_categories = $config_object->wizard_categories;
				unset($config_object->wizard_categories);
			}

			// Check template_categories exist
			if(
				!is_object($config_object) ||
				!isset($config_object->template_categories) ||
				!is_array($config_object->template_categories)

			) {
				return false;
			}

			// Check template categories
			foreach($config_object->template_categories as $template_category_index => $template_category) {

				// Legacy
				if(isset($template_category->wizards)) {

					$template_category->templates = $template_category->wizards;
					unset($template_category->wizards);
				}

				// Check integrity of template category
				if(
					!is_object($template_category) ||
					!isset($template_category->id) ||
					!isset($template_category->label) ||
					!isset($template_category->file_path) ||
					!isset($template_category->templates) ||
					!is_array($template_category->templates)
				) {

					return false;
				}

				// Check templates
				$templates_new = array();
				$templates_new_set = false;
				foreach($template_category->templates as $template_index => $template) {

					// Check integrity of template
					if(
						!is_object($template) ||
						!isset($template->id) ||
						!isset($template->label) ||
						!isset($template->file_json)
					) {

						return false;
					}

					// Get full path of template file
					$template_file = sprintf('%s/%s%s', $config_file_path, $template_category->file_path, $template->file_json);

					// Check to see if it exists
					if(!file_exists($template_file)) {

						$config_file_rewrite = true;
						$templates_new_set = true;

					} else {

						$templates_new[] = $template;
					}
				}

				if($templates_new_set) {

					$config_object->template_categories[$template_category_index]->templates = $templates_new;
				}
			}

			if($config_file_rewrite) {

				$config_file_json = json_encode($config_object);
				if($config_file_json === false) { return false; }

				file_put_contents($config_file, $config_file_json);
			}

			return true;
		}

		// Check id
		public function db_check_id() {

			if(empty($this->id)) { parent::db_throw_error(__('Invalid ID', 'ws-form')); }
			return true;
		}

		// Check type
		public function db_check_type() {

			if(!in_array($this->type, array('form', 'section'))) { parent::db_throw_error(__('Invalid template type', 'ws-form')); }
			return true;
		}

		// Check action_id
		public function db_check_action_id() {

			if($this->action_id === false) { parent::db_throw_error(__('Invalid action ID', 'ws-form')); }
			return true;
		}
	}

	class_alias('WS_Form_Template', 'WS_Form_Wizard');	// Legacy
