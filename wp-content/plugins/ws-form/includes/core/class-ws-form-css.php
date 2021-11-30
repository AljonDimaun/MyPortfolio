<?php

	class WS_Form_CSS {

		public $skins = array();
		public $skin_id = WS_FORM_CSS_SKIN_DEFAULT;
		public $skin = false;

		public function __construct() {

			// Get skins
			$this->skins = WS_Form_Config::get_skins();

			// Skin override (Used by customize feature)
			$skin_id_override = WS_Form_Common::get_query_var('wsf_skin_id', '', false, false, true, 'GET');
			if($skin_id_override) {

				// Get all skin IDs
				$skin_ids = array_keys($this->skins);

				// Check skin ID override is valid
				if(!in_array($skin_id_override, $skin_ids)) { $skin_id_override = WS_FORM_CSS_SKIN_DEFAULT; }

				$this->skin_id = $skin_id_override;
			}
		}

		// Init
		public function init() {

			// Initial build
			$css_compile = WS_Form_Common::option_get('css_compile', false);
			$css_public_layout = WS_Form_Common::option_get('css_public_layout', '');
			if($css_compile && empty($css_public_layout)) {

				self::build_public_css();
			}

			// Actions that recompile CSS
			add_action('wsf_activate', array($this, 'build_public_css'));
			add_action('wsf_activate_add_on', array($this, 'build_public_css'));
			add_action('wsf_settings_update', array($this, 'build_public_css'));
			add_filter('customize_save_response', function($response) {

				self::build_public_css();

				return $response;
			});
		}

		// Load skin
		public function skin_load() {

			// Get skin
			$this->skin_id = apply_filters('wsf_skin_id', $this->skin_id);

			// Check skin config
			if(!isset($this->skins[$this->skin_id])) { throw new ErrorException(__('Invalid skin ID', 'ws-form')); }			

			// Load config
			$this->skin_config = $this->skins[$this->skin_id];

			// Label
			$this->skin_label = $this->skin_config['label'];

			// Setting ID prefix
			$this->skin_setting_id_prefix = $this->skin_config['setting_id_prefix'];

			// Setting defaults
			$this->skin_defaults = $this->skin_config['defaults'];

			// Set skin option
			$this->skin_option = ($this->skin_setting_id_prefix != '') ? '_' . $this->skin_setting_id_prefix : '';

			// Set skin file
			$this->skin_file = ($this->skin_setting_id_prefix != '') ? '.' . $this->skin_setting_id_prefix : '';
		}

		// Set skin variables
		public function skin_variables() {

			// Set variables
			$enable_cache = !(WS_Form_Common::get_query_var('customize_theme') !== '');

			// Get customize groups
			$customize_groups = WS_Form_Config::get_customize();

			foreach($customize_groups as $customize_group) {

				foreach($customize_group['fields'] as $meta_key => $config) {

					$this->{$meta_key} = WS_Form_Common::option_get('skin' . $this->skin_option . '_' . $meta_key, null, false, $enable_cache, true);
					if(is_null($this->{$meta_key})) { $this->{$meta_key} = isset($this->skin_defaults[$meta_key]) ? $this->skin_defaults[$meta_key] : ''; }
				}
			}
		}

		// Set option defaults
		public function option_set_defaults() {

			foreach($this->skins as $skin_id => $skin) {

				$this->skin_id = $skin_id;
				$this->skin_load();

				// Set up customizer options with default values
				foreach($this->skin_defaults as $meta_key => $meta_value) {

					WS_Form_Common::option_set('skin' . $this->skin_option . '_' . $meta_key, $meta_value, false);
				}
			}
		}

		// Set color shades
		public function skin_color_shades() {

			// Default
			$this->color_default_lightest_dark_10 = WS_Form_Common::hex_darken_percentage($this->color_default_lightest, 10);
			$this->color_default_lightest_dark_20 = WS_Form_Common::hex_darken_percentage($this->color_default_lightest, 20);
			$this->color_default_lighter_dark_10 = WS_Form_Common::hex_darken_percentage($this->color_default_lighter, 10);
			$this->color_default_lighter_dark_20 = WS_Form_Common::hex_darken_percentage($this->color_default_lighter, 20);

			// Primary
			$this->color_primary_dark_10 = WS_Form_Common::hex_darken_percentage($this->color_primary, 10);
			$this->color_primary_dark_20 = WS_Form_Common::hex_darken_percentage($this->color_primary, 20);

			// Secondary
			$this->color_secondary_dark_10 = WS_Form_Common::hex_darken_percentage($this->color_secondary, 10);
			$this->color_secondary_dark_20 = WS_Form_Common::hex_darken_percentage($this->color_secondary, 20);

			// Success
			$this->color_success_light_40 = WS_Form_Common::hex_lighten_percentage($this->color_success, 40);
			$this->color_success_light_85 = WS_Form_Common::hex_lighten_percentage($this->color_success, 85);
			$this->color_success_dark_10 = WS_Form_Common::hex_darken_percentage($this->color_success, 10);
			$this->color_success_dark_20 = WS_Form_Common::hex_darken_percentage($this->color_success, 20);
			$this->color_success_dark_40 = WS_Form_Common::hex_darken_percentage($this->color_success, 40);
			$this->color_success_dark_60 = WS_Form_Common::hex_darken_percentage($this->color_success, 60);

			// Information
			$this->color_information_light_40 = WS_Form_Common::hex_lighten_percentage($this->color_information, 40);
			$this->color_information_light_85 = WS_Form_Common::hex_lighten_percentage($this->color_information, 85);
			$this->color_information_dark_10 = WS_Form_Common::hex_darken_percentage($this->color_information, 10);
			$this->color_information_dark_20 = WS_Form_Common::hex_darken_percentage($this->color_information, 20);
			$this->color_information_dark_40 = WS_Form_Common::hex_darken_percentage($this->color_information, 40);
			$this->color_information_dark_60 = WS_Form_Common::hex_darken_percentage($this->color_information, 60);

			// Warning
			$this->color_warning_light_40 = WS_Form_Common::hex_lighten_percentage($this->color_warning, 40);
			$this->color_warning_light_85 = WS_Form_Common::hex_lighten_percentage($this->color_warning, 85);
			$this->color_warning_dark_10 = WS_Form_Common::hex_darken_percentage($this->color_warning, 10);
			$this->color_warning_dark_20 = WS_Form_Common::hex_darken_percentage($this->color_warning, 20);
			$this->color_warning_dark_40 = WS_Form_Common::hex_darken_percentage($this->color_warning, 40);
			$this->color_warning_dark_60 = WS_Form_Common::hex_darken_percentage($this->color_warning, 60);

			// Dveranger
			$this->color_danger_light_40 = WS_Form_Common::hex_lighten_percentage($this->color_danger, 40);
			$this->color_danger_light_85 = WS_Form_Common::hex_lighten_percentage($this->color_danger, 85);
			$this->color_danger_dark_10 = WS_Form_Common::hex_darken_percentage($this->color_danger, 10);
			$this->color_danger_dark_20 = WS_Form_Common::hex_darken_percentage($this->color_danger, 20);
			$this->color_danger_dark_40 = WS_Form_Common::hex_darken_percentage($this->color_danger, 40);
			$this->color_danger_dark_60 = WS_Form_Common::hex_darken_percentage($this->color_danger, 60);
		}

		// Admin
		public function get_admin() {

			include_once 'css/class-ws-form-css-admin.php';
			$ws_form_css_admin = new WS_Form_CSS_Admin();
			return $ws_form_css_admin->get_admin();
		}

		// Conversational
		public function get_conversational($css_minify = null, $force_build = false, $rtl = false) {

			// Build CSS
			$css_return = '';

			return $css_return;
		}

		// Layout
		public function get_layout($css_minify = null, $force_build = false, $rtl = false) {

			// Build CSS
			$css_return = '';

			// Minify
			if(is_null($css_minify)) {

				$css_minify = !SCRIPT_DEBUG;
			}

			// Initial build of compiled files
			$css_compile = WS_Form_Common::option_get('css_compile', false);
			if($css_compile && !$force_build) {

				if($css_minify) {

					$css_return = WS_Form_Common::option_get(sprintf('css_public_layout%s_min', $rtl));

				} else {

					$css_return = WS_Form_Common::option_get(sprintf('css_public_layout%s', $rtl));
				}

			} else {

				include_once 'css/class-ws-form-css-layout.php';

				$ws_form_css_layout = new WS_Form_CSS_Layout();

				ob_start();

				// Render layout
				$ws_form_css_layout->render_layout();

				$css_return = ob_get_contents();

				ob_end_clean();

				// Apply filters
				$css_return = apply_filters('wsf_get_layout', $css_return);

				// Minify?
				$css_return = $css_minify ? self::minify($css_return) : $css_return;
			}

			return $css_return;
		}

		// Skin
		public function get_skin($css_minify = null, $force_build = false, $rtl = false) {

			// Build CSS
			$css_return = '';

			// Minify
			if(is_null($css_minify)) {

				$css_minify = !SCRIPT_DEBUG;
			}

			// Initial build of compiled files
			$css_compile = WS_Form_Common::option_get('css_compile', false);
			if($css_compile && !$force_build) {

				// Load skin
				self::skin_load();

				if($css_minify) {

					$css_return = WS_Form_Common::option_get(sprintf('css_public_skin%s%s_min', $this->skin_option, $rtl));

				} else {

					$css_return = WS_Form_Common::option_get(sprintf('css_public_skin%s%s', $this->skin_option, $rtl));
				}

			} else {

				include_once 'css/class-ws-form-css-skin.php';

				$ws_form_css_skin = new WS_Form_CSS_Skin();
				$ws_form_css_skin->skin_id = $this->skin_id;

				ob_start();

				// Render skin
				$ws_form_css_skin->render_skin();

				if($rtl) {

					// Render RTL skin
					$ws_form_css_skin->render_skin_rtl();
				}

				$css_return = ob_get_contents();

				ob_end_clean();

				// Apply filters
				$css_return = apply_filters('wsf_get_skin', $css_return);

				// Minify?
				$css_return = $css_minify ? self::minify($css_return) : $css_return;
			}

			return $css_return;
		}

		// Email
		public function get_email() {

			include_once 'css/class-ws-form-css-email.php';
			$ws_form_css_email = new WS_Form_CSS_Email();
			return $ws_form_css_email->get_email();
		}

		// Build public CSS files
		public function build_public_css() {

			$css_compile = WS_Form_Common::option_get('css_compile', false);
			$css_inline = WS_Form_Common::option_get('css_inline', false);

			if($css_compile) {

				// Build file upload directory
				if(!$css_inline) {

					$upload_dir = WS_Form_Common::upload_dir_create(WS_FORM_CSS_FILE_PATH);
					if($upload_dir['error']) { self::db_throw_error($upload_dir['error']); }
					$file_upload_dir = $upload_dir['dir'];
				}

				// Build CSS (Layout)
				$css_layout = self::get_layout(false, true, false);
				if(!$css_inline) {

					file_put_contents($file_upload_dir . '/public.layout.css', $css_layout);
				}
				WS_Form_Common::option_set('css_public_layout', $css_layout);

				// Build CSS (Layout - Minimized)
				$css_layout_minimized = self::minify($css_layout);
				$css_layout = null;
				if(!$css_inline) {

					file_put_contents($file_upload_dir . '/public.layout.min.css', $css_layout_minimized);
				}
				WS_Form_Common::option_set('css_public_layout_min', $css_layout_minimized);
				$css_layout_minimized = null;

				// Build CSS (Layout - RTL)
				$css_layout = self::get_layout(false, true, true);
				if(!$css_inline) {

					file_put_contents($file_upload_dir . '/public.layout.rtl.css', $css_layout);
				}
				WS_Form_Common::option_set('css_public_layout_rtl', $css_layout);

				// Build CSS (Layout - RTL - Minimized)
				$css_layout_minimized = self::minify($css_layout);
				$css_layout = null;
				if(!$css_inline) {

					file_put_contents($file_upload_dir . '/public.layout.rtl.min.css', $css_layout_minimized);
				}
				WS_Form_Common::option_set('css_public_layout_rtl_min', $css_layout_minimized);
				$css_layout_minimized = null;

				// Build skins
				foreach($this->skins as $skin_id => $skin) {

					$this->skin_id = $skin_id;

					// Load skin
					self::skin_load();

					// Build CSS (Skin)
					$css_skin = self::get_skin(false, true, false);
					if(!$css_inline) {

						file_put_contents(sprintf('%s/public.skin%s.css', $file_upload_dir, $this->skin_file), $css_skin);
					}
					WS_Form_Common::option_set(sprintf('css_public_skin%s', $this->skin_option), $css_skin);

					// Build CSS (Skin - Minimized)
					$css_skin_minimized = self::minify($css_skin);
					$css_skin = null;
					if(!$css_inline) {

						file_put_contents(sprintf('%s/public.skin%s.min.css', $file_upload_dir, $this->skin_file), $css_skin_minimized);
					}
					WS_Form_Common::option_set(sprintf('css_public_skin%s_min', $this->skin_option), $css_skin_minimized);
					$css_skin_minimized = null;

					// Build CSS (Skin - RTL)
					$css_skin = self::get_skin(false, true, true);
					if(!$css_inline) {

						file_put_contents(sprintf('%s/public.skin%s.rtl.css', $file_upload_dir, $this->skin_file), $css_skin);
					}
					WS_Form_Common::option_set(sprintf('css_public_skin%s_rtl', $this->skin_option), $css_skin);

					// Build CSS (Skin - RTL - Minimized)
					$css_skin_minimized = self::minify($css_skin);
					$css_skin = null;
					if(!$css_inline) {

						file_put_contents(sprintf('%s/public.skin%s.rtl.min.css', $file_upload_dir, $this->skin_file), $css_skin_minimized);
					}
					WS_Form_Common::option_set(sprintf('css_public_skin%s_rtl_min', $this->skin_option), $css_skin_minimized);
					$css_skin_minimized = null;
				}

				// Build CSS (Conversational)
				$css_conversational = self::get_conversational(false, true, false);
				if(!$css_inline) {

					file_put_contents($file_upload_dir . '/public.conversational.css', $css_conversational);
				}
				WS_Form_Common::option_set('css_public_conversational', $css_conversational);

				// Build CSS (Conversational - Minimized)
				$css_conversational_minimized = self::minify($css_conversational);
				$css_conversational = null;
				if(!$css_inline) {

					file_put_contents($file_upload_dir . '/public.conversational.min.css', $css_conversational_minimized);
				}
				WS_Form_Common::option_set('css_public_conversational_min', $css_conversational_minimized);
				$css_conversational_minimized = null;

				// Build CSS (Conversational - RTL)
				$css_conversational = self::get_conversational(false, true, true);
				if(!$css_inline) {

					file_put_contents($file_upload_dir . '/public.conversational.rtl.css', $css_conversational);
				}
				WS_Form_Common::option_set('css_public_conversational_rtl', $css_conversational);

				// Build CSS (Conversational - RTL - Minimized)
				$css_conversational_minimized = self::minify($css_conversational);
				$css_conversational = null;
				if(!$css_inline) {

					file_put_contents($file_upload_dir . '/public.conversational.rtl.min.css', $css_conversational_minimized);
				}
				WS_Form_Common::option_set('css_public_conversational_rtl_min', $css_conversational_minimized);
				$css_conversational_minimized = null;
			}
		}

		public function inline($css) {

			// Output CSS
			return sprintf('<style>%s</style>', $css);
		}

		public function minify($css) {

			// Basic minify
			$css = preg_replace('/\/\*((?!\*\/).)*\*\//', '', $css);
			$css = preg_replace('/\s{2,}/', ' ', $css);
			$css = preg_replace('/\s*([:;{}])\s*/', '$1', $css);
			$css = preg_replace('/;}/', '}', $css);
			$css = str_replace(array("\r\n","\r","\n","\t",'  ','    ','    '),"",$css);

			return $css;
		}

		// Escape CSS values
		public function e($css_value) {

			$css_value = wp_strip_all_tags($css_value);
			$css_value = str_replace(';', '', $css_value);
			echo $css_value;
		}
	}
