<?php
/**
 * @package Unlimited Elements
 * @author unlimited-elements.com
 * @copyright (C) 2021 Unlimited Elements, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
defined('UNLIMITED_ELEMENTS_INC') or die('Restricted access');

class UniteCreatorUniteGallery{

		private $isTilesType = false;
		private $arrParams;
		private $arrOriginalParams;		//params as they came originally from the database
		private $skipJsOptions = array();
		private $arrJsParamsAssoc = array();
		
		
		const TYPE_NUMBER = "number";
		const TYPE_BOOLEAN = "boolean";
		const TYPE_OBJECT = "object";
		const TYPE_SIZE = "size";
		
		const VALIDATE_EXISTS = "validate";
		const VALIDATE_NUMERIC = "numeric";
		const VALIDATE_SIZE = "size";
		const FORCE_NUMERIC = "force_numeric";
		const FORCE_BOOLEAN = "force_boolean";
		const FORCE_SIZE = "force_size";
		const TRIM = "trim";

		
		const THEME_DEFAULT = "default";
		const THEME_COMPACT = "compact";
		const THEME_SLIDER = "slider";
		const THEME_GRID = "grid";
		const THEME_VIDEO = "video";
		const THEME_TILES = "tiles";
		const THEME_TILESGRID = "tilesgrid";
		const THEME_CAROUSEL = "carousel";
		
		
		/**
		 * get must fields that will be thrown from the settings anyway
		 */
		protected function getArrMustFields(){
			$arrMustKeys = array(
					"category",					
					"gallery_theme",
					"full_width",
					"gallery_width",
					"gallery_height",
					"position",
					"margin_top",
					"margin_bottom",
					"margin_left",
					"margin_right"
			);

			return($arrMustKeys);
		}
		
		
		/**
		 * build javascript param
		 */
		protected function buildJsParam($paramName, $validate = null, $type = null){
			
			if(array_key_exists($paramName, $this->arrJsParamsAssoc))
				UniteFunctionsUG::throwError("Unable to biuld js param: <b>$paramName</b> already exists");
			
			$output = array("name"=>$paramName, "validate"=>$validate, "type"=>$type);
			
			$this->arrJsParamsAssoc[$paramName] = true;
			
			return($output);
		}
		
	
		/**
		 * get params array defenitions that shouls be put as is from the settings
		 */
		protected function getArrJsOptions(){
			
			$arr = array();
			$arr[] = $this->buildJsParam("gallery_theme");
			$arr[] = $this->buildJsParam("gallery_width", self::VALIDATE_SIZE, self::TYPE_SIZE);
			$arr[] = $this->buildJsParam("gallery_height", self::VALIDATE_SIZE, self::TYPE_SIZE);
			$arr[] = $this->buildJsParam("gallery_min_width", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
			$arr[] = $this->buildJsParam("gallery_min_height", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
			$arr[] = $this->buildJsParam("gallery_skin");
			$arr[] = $this->buildJsParam("gallery_images_preload_type");
			$arr[] = $this->buildJsParam("gallery_autoplay", null, self::TYPE_BOOLEAN);
			$arr[] = $this->buildJsParam("gallery_play_interval", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
			$arr[] = $this->buildJsParam("gallery_pause_on_mouseover", null, self::TYPE_BOOLEAN);
			$arr[] = $this->buildJsParam("gallery_mousewheel_role");
			$arr[] = $this->buildJsParam("gallery_control_keyboard", null, self::TYPE_BOOLEAN);
			$arr[] = $this->buildJsParam("gallery_preserve_ratio", null, self::TYPE_BOOLEAN);
			$arr[] = $this->buildJsParam("gallery_shuffle", null, self::TYPE_BOOLEAN);
			$arr[] = $this->buildJsParam("gallery_debug_errors", null, self::TYPE_BOOLEAN);
			$arr[] = $this->buildJsParam("slider_background_color");
			$arr[] = $this->buildJsParam("slider_background_opacity", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
			
			$arr[] = $this->buildJsParam("slider_scale_mode");
			$arr[] = $this->buildJsParam("slider_scale_mode_media");
			$arr[] = $this->buildJsParam("slider_scale_mode_fullscreen");
			
			$arr[] = $this->buildJsParam("slider_transition");
			$arr[] = $this->buildJsParam("slider_transition_speed", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
			$arr[] = $this->buildJsParam("slider_transition_easing");
			$arr[] = $this->buildJsParam("slider_control_swipe", null, self::TYPE_BOOLEAN);
			$arr[] = $this->buildJsParam("slider_control_zoom", null, self::TYPE_BOOLEAN);
			$arr[] = $this->buildJsParam("slider_zoom_max_ratio", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
			$arr[] = $this->buildJsParam("slider_enable_links", null, self::TYPE_BOOLEAN);
			$arr[] = $this->buildJsParam("slider_links_newpage", null, self::TYPE_BOOLEAN);
			
			$arr[] = $this->buildJsParam("slider_video_enable_closebutton", null, self::TYPE_BOOLEAN);
			
			$arr[] = $this->buildJsParam("slider_controls_always_on", null, self::TYPE_BOOLEAN);
			$arr[] = $this->buildJsParam("slider_controls_appear_ontap", null, self::TYPE_BOOLEAN);
			$arr[] = $this->buildJsParam("slider_controls_appear_duration", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
			
			$arr[] = $this->buildJsParam("slider_loader_type", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
			$arr[] = $this->buildJsParam("slider_loader_color");
			
			$arr[] = $this->buildJsParam("slider_enable_bullets", null, self::TYPE_BOOLEAN);
			$arr[] = $this->buildJsParam("slider_bullets_skin");
			$arr[] = $this->buildJsParam("slider_bullets_space_between", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
			$arr[] = $this->buildJsParam("slider_bullets_align_hor");
			$arr[] = $this->buildJsParam("slider_bullets_align_vert");
			$arr[] = $this->buildJsParam("slider_bullets_offset_hor", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
			$arr[] = $this->buildJsParam("slider_bullets_offset_vert", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);

			$arr[] = $this->buildJsParam("slider_enable_arrows", null, self::TYPE_BOOLEAN);
			$arr[] = $this->buildJsParam("slider_arrows_skin");
			$arr[] = $this->buildJsParam("slider_arrow_left_align_hor");
			$arr[] = $this->buildJsParam("slider_arrow_left_align_vert");
			$arr[] = $this->buildJsParam("slider_arrow_left_offset_hor", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
			$arr[] = $this->buildJsParam("slider_arrow_left_offset_vert", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
			$arr[] = $this->buildJsParam("slider_arrow_right_align_hor");
			$arr[] = $this->buildJsParam("slider_arrow_right_align_vert");
			$arr[] = $this->buildJsParam("slider_arrow_right_offset_hor", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
			$arr[] = $this->buildJsParam("slider_arrow_right_offset_vert", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);

			$arr[] = $this->buildJsParam("slider_enable_progress_indicator", null, self::TYPE_BOOLEAN);
			$arr[] = $this->buildJsParam("slider_progress_indicator_type");
			$arr[] = $this->buildJsParam("slider_progress_indicator_align_hor");
			$arr[] = $this->buildJsParam("slider_progress_indicator_align_vert");
			$arr[] = $this->buildJsParam("slider_progress_indicator_offset_hor", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
			$arr[] = $this->buildJsParam("slider_progress_indicator_offset_vert", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
			
			$arr[] = $this->buildJsParam("slider_progressbar_color");
			$arr[] = $this->buildJsParam("slider_progressbar_opacity", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
			$arr[] = $this->buildJsParam("slider_progressbar_line_width", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
			
			$arr[] = $this->buildJsParam("slider_progresspie_color1");
			$arr[] = $this->buildJsParam("slider_progresspie_color2");
			$arr[] = $this->buildJsParam("slider_progresspie_stroke_width", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
			$arr[] = $this->buildJsParam("slider_progresspie_width", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
			$arr[] = $this->buildJsParam("slider_progresspie_height", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
			
			$arr[] = $this->buildJsParam("slider_enable_play_button", null, self::TYPE_BOOLEAN);
			$arr[] = $this->buildJsParam("slider_play_button_skin");
			$arr[] = $this->buildJsParam("slider_play_button_align_hor");
			$arr[] = $this->buildJsParam("slider_play_button_align_vert");
			$arr[] = $this->buildJsParam("slider_play_button_offset_hor", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
			$arr[] = $this->buildJsParam("slider_play_button_offset_vert", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);

			$arr[] = $this->buildJsParam("slider_enable_fullscreen_button", null, self::TYPE_BOOLEAN);
			$arr[] = $this->buildJsParam("slider_fullscreen_button_skin");
			$arr[] = $this->buildJsParam("slider_fullscreen_button_align_hor");
			$arr[] = $this->buildJsParam("slider_fullscreen_button_align_vert");
			$arr[] = $this->buildJsParam("slider_fullscreen_button_offset_hor", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
			$arr[] = $this->buildJsParam("slider_fullscreen_button_offset_vert", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);

			$arr[] = $this->buildJsParam("slider_enable_zoom_panel", null, self::TYPE_BOOLEAN);
			$arr[] = $this->buildJsParam("slider_zoompanel_skin");
			$arr[] = $this->buildJsParam("slider_zoompanel_align_hor");
			$arr[] = $this->buildJsParam("slider_zoompanel_align_vert");
			$arr[] = $this->buildJsParam("slider_zoompanel_offset_hor", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
			$arr[] = $this->buildJsParam("slider_zoompanel_offset_vert", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
			
			$arr[] = $this->buildJsParam("slider_enable_text_panel", null, self::TYPE_BOOLEAN);
			$arr[] = $this->buildJsParam("slider_textpanel_always_on", null, self::TYPE_BOOLEAN);
			$arr[] = $this->buildJsParam("slider_textpanel_align");
			$arr[] = $this->buildJsParam("slider_textpanel_margin", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
			$arr[] = $this->buildJsParam("slider_textpanel_text_valign");
			$arr[] = $this->buildJsParam("slider_textpanel_padding_top", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
			$arr[] = $this->buildJsParam("slider_textpanel_padding_bottom", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
			$arr[] = $this->buildJsParam("slider_textpanel_height", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
			$arr[] = $this->buildJsParam("slider_textpanel_padding_title_description", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
			$arr[] = $this->buildJsParam("slider_textpanel_padding_right", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
			$arr[] = $this->buildJsParam("slider_textpanel_padding_left", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
			$arr[] = $this->buildJsParam("slider_textpanel_fade_duration", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
			$arr[] = $this->buildJsParam("slider_textpanel_enable_title", null, self::TYPE_BOOLEAN);
			$arr[] = $this->buildJsParam("slider_textpanel_title_as_link", null, self::TYPE_BOOLEAN);
			$arr[] = $this->buildJsParam("slider_textpanel_enable_description", null, self::TYPE_BOOLEAN);
			$arr[] = $this->buildJsParam("slider_textpanel_enable_bg", null, self::TYPE_BOOLEAN);
			$arr[] = $this->buildJsParam("slider_textpanel_bg_color");
			$arr[] = $this->buildJsParam("slider_textpanel_bg_opacity", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
			
			$arr[] = $this->buildJsParam("thumb_width", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
			$arr[] = $this->buildJsParam("thumb_height", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
			$arr[] = $this->buildJsParam("thumb_fixed_size", null, self::TYPE_BOOLEAN);
			$arr[] = $this->buildJsParam("thumb_border_effect", null, self::TYPE_BOOLEAN);
			$arr[] = $this->buildJsParam("thumb_border_width", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
			$arr[] = $this->buildJsParam("thumb_border_color");
			$arr[] = $this->buildJsParam("thumb_over_border_width", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
			$arr[] = $this->buildJsParam("thumb_over_border_color");
			$arr[] = $this->buildJsParam("thumb_selected_border_width", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
			$arr[] = $this->buildJsParam("thumb_selected_border_color");
			$arr[] = $this->buildJsParam("thumb_round_corners_radius", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
			$arr[] = $this->buildJsParam("thumb_color_overlay_effect", null, self::TYPE_BOOLEAN);
			$arr[] = $this->buildJsParam("thumb_overlay_color");
			$arr[] = $this->buildJsParam("thumb_overlay_opacity", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
			$arr[] = $this->buildJsParam("thumb_overlay_reverse", null, self::TYPE_BOOLEAN);
			$arr[] = $this->buildJsParam("thumb_image_overlay_effect", null, self::TYPE_BOOLEAN);
			$arr[] = $this->buildJsParam("thumb_image_overlay_type");
			$arr[] = $this->buildJsParam("thumb_transition_duration", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
			$arr[] = $this->buildJsParam("thumb_transition_easing");
			$arr[] = $this->buildJsParam("thumb_show_loader", null, self::TYPE_BOOLEAN);
			$arr[] = $this->buildJsParam("thumb_loader_type");
			
			$arr[] = $this->buildJsParam("strippanel_padding_top", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
			$arr[] = $this->buildJsParam("strippanel_padding_bottom", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
			$arr[] = $this->buildJsParam("strippanel_padding_left", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
			$arr[] = $this->buildJsParam("strippanel_padding_right", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
			$arr[] = $this->buildJsParam("strippanel_enable_buttons", null, self::TYPE_BOOLEAN);
			$arr[] = $this->buildJsParam("strippanel_buttons_skin");
			$arr[] = $this->buildJsParam("strippanel_padding_buttons", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
			$arr[] = $this->buildJsParam("strippanel_buttons_role");
			$arr[] = $this->buildJsParam("strippanel_enable_handle", null, self::TYPE_BOOLEAN);
			$arr[] = $this->buildJsParam("strippanel_handle_align");
			$arr[] = $this->buildJsParam("strippanel_handle_offset", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
			$arr[] = $this->buildJsParam("strippanel_handle_skin");
			$arr[] = $this->buildJsParam("strippanel_background_color");
			$arr[] = $this->buildJsParam("strip_thumbs_align");
			$arr[] = $this->buildJsParam("strip_space_between_thumbs", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
			$arr[] = $this->buildJsParam("strip_thumb_touch_sensetivity", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
			$arr[] = $this->buildJsParam("strip_scroll_to_thumb_duration", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
			$arr[] = $this->buildJsParam("strip_scroll_to_thumb_easing");
			$arr[] = $this->buildJsParam("strip_control_avia", null, self::TYPE_BOOLEAN);
			$arr[] = $this->buildJsParam("strip_control_touch", null, self::TYPE_BOOLEAN);
			
			$arr[] = $this->buildJsParam("gridpanel_vertical_scroll", null, self::TYPE_BOOLEAN);
			$arr[] = $this->buildJsParam("gridpanel_grid_align");
			$arr[] = $this->buildJsParam("gridpanel_padding_border_top", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
			$arr[] = $this->buildJsParam("gridpanel_padding_border_bottom", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
			$arr[] = $this->buildJsParam("gridpanel_padding_border_left", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
			$arr[] = $this->buildJsParam("gridpanel_padding_border_right", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
			$arr[] = $this->buildJsParam("gridpanel_arrows_skin");
			$arr[] = $this->buildJsParam("gridpanel_arrows_align_vert");
			$arr[] = $this->buildJsParam("gridpanel_arrows_padding_vert", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
			$arr[] = $this->buildJsParam("gridpanel_arrows_align_hor");
			$arr[] = $this->buildJsParam("gridpanel_arrows_padding_hor", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
			$arr[] = $this->buildJsParam("gridpanel_space_between_arrows", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
			$arr[] = $this->buildJsParam("gridpanel_arrows_always_on", null, self::TYPE_BOOLEAN);
			$arr[] = $this->buildJsParam("gridpanel_enable_handle", null, self::TYPE_BOOLEAN);
			$arr[] = $this->buildJsParam("gridpanel_handle_align");
			$arr[] = $this->buildJsParam("gridpanel_handle_offset", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
			$arr[] = $this->buildJsParam("gridpanel_handle_skin");
			$arr[] = $this->buildJsParam("gridpanel_background_color");
			
			$arr[] = $this->buildJsParam("grid_panes_direction");
			$arr[] = $this->buildJsParam("grid_num_cols", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
			$arr[] = $this->buildJsParam("grid_space_between_cols", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
			$arr[] = $this->buildJsParam("grid_space_between_rows", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
			$arr[] = $this->buildJsParam("grid_transition_duration", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
			$arr[] = $this->buildJsParam("grid_transition_easing");
			$arr[] = $this->buildJsParam("grid_carousel", null, self::TYPE_BOOLEAN);

			//category tabs related
			$arr[] = $this->buildJsParam("gallery_urlajax");
			$arr[] = $this->buildJsParam("gallery_enable_tabs", null, self::TYPE_BOOLEAN);
			$arr[] = $this->buildJsParam("tabs_type");
			$arr[] = $this->buildJsParam("tabs_container");
			$arr[] = $this->buildJsParam("gallery_initial_catid");
			$arr[] = $this->buildJsParam("load_api_externally", null, self::TYPE_BOOLEAN);
			
			$arr[] = $this->buildJsParam("gallery_enable_loadmore", null, self::TYPE_BOOLEAN);
			$arr[] = $this->buildJsParam("loadmore_container");
			
			//tiles type
			if($this->isTilesType == true){
				
				$arr[] = $this->buildJsParam("tile_width", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
				$arr[] = $this->buildJsParam("tile_height", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
				$arr[] = $this->buildJsParam("tile_enable_background", null, self::TYPE_BOOLEAN);
				$arr[] = $this->buildJsParam("tile_background_color");
				$arr[] = $this->buildJsParam("tile_enable_border", null, self::TYPE_BOOLEAN);
				$arr[] = $this->buildJsParam("tile_border_width", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
				$arr[] = $this->buildJsParam("tile_border_color");
				$arr[] = $this->buildJsParam("tile_border_radius", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
				$arr[] = $this->buildJsParam("tile_enable_outline", null, self::TYPE_BOOLEAN);
				$arr[] = $this->buildJsParam("tile_outline_color");
				$arr[] = $this->buildJsParam("tile_enable_shadow", null, self::TYPE_BOOLEAN);
				$arr[] = $this->buildJsParam("tile_shadow_h", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
				$arr[] = $this->buildJsParam("tile_shadow_v", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
				$arr[] = $this->buildJsParam("tile_shadow_blur", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
				$arr[] = $this->buildJsParam("tile_shadow_spread", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
				$arr[] = $this->buildJsParam("tile_shadow_color");
				$arr[] = $this->buildJsParam("tile_enable_action");
				$arr[] = $this->buildJsParam("tile_as_link", null, self::TYPE_BOOLEAN);
				$arr[] = $this->buildJsParam("tile_link_newpage", null, self::TYPE_BOOLEAN);
				$arr[] = $this->buildJsParam("tile_enable_overlay", null, self::TYPE_BOOLEAN);
				$arr[] = $this->buildJsParam("tile_overlay_opacity", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
				$arr[] = $this->buildJsParam("tile_overlay_color");
				$arr[] = $this->buildJsParam("tile_enable_icons", null, self::TYPE_BOOLEAN);
				$arr[] = $this->buildJsParam("tile_show_link_icon", null, self::TYPE_BOOLEAN);
				$arr[] = $this->buildJsParam("tile_space_between_icons");
				$arr[] = $this->buildJsParam("tile_videoplay_icon_always_on");
				$arr[] = $this->buildJsParam("tile_enable_image_effect", null, self::TYPE_BOOLEAN);
				$arr[] = $this->buildJsParam("tile_image_effect_type");
				$arr[] = $this->buildJsParam("tile_image_effect_reverse", null, self::TYPE_BOOLEAN);
				$arr[] = $this->buildJsParam("tile_enable_textpanel", null, self::TYPE_BOOLEAN);
				$arr[] = $this->buildJsParam("tile_textpanel_source");
				$arr[] = $this->buildJsParam("tile_textpanel_position");
				
				$arr[] = $this->buildJsParam("tile_textpanel_always_on", null, self::TYPE_BOOLEAN);
				$arr[] = $this->buildJsParam("tile_textpanel_appear_type");
				$arr[] = $this->buildJsParam("tile_textpanel_offset", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
				$arr[] = $this->buildJsParam("tile_textpanel_padding_top", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
				$arr[] = $this->buildJsParam("tile_textpanel_padding_bottom", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
				$arr[] = $this->buildJsParam("tile_textpanel_padding_left", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
				$arr[] = $this->buildJsParam("tile_textpanel_padding_right", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
				$arr[] = $this->buildJsParam("tile_textpanel_bg_color");
				$arr[] = $this->buildJsParam("tile_textpanel_bg_opacity", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
				$arr[] = $this->buildJsParam("tile_textpanel_title_color");
				$arr[] = $this->buildJsParam("tile_textpanel_title_text_align");
				$arr[] = $this->buildJsParam("tile_textpanel_title_font_size", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
				$arr[] = $this->buildJsParam("tile_textpanel_title_bold", null, self::TYPE_BOOLEAN);
				$arr[] = $this->buildJsParam("tile_textpanel_desc_bold", null, self::TYPE_BOOLEAN);

				$arr[] = $this->buildJsParam("lightbox_hide_arrows_onvideoplay", null, self::TYPE_BOOLEAN);
				$arr[] = $this->buildJsParam("lightbox_slider_control_swipe", null, self::TYPE_BOOLEAN);
				$arr[] = $this->buildJsParam("lightbox_slider_control_zoom", null, self::TYPE_BOOLEAN);
				$arr[] = $this->buildJsParam("lightbox_close_on_emptyspace", null, self::TYPE_BOOLEAN);
				
				$arr[] = $this->buildJsParam("lightbox_slider_zoom_max_ratio", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
				$arr[] = $this->buildJsParam("lightbox_slider_transition");
				$arr[] = $this->buildJsParam("lightbox_overlay_opacity", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
				$arr[] = $this->buildJsParam("lightbox_overlay_color");
				
				$arr[] = $this->buildJsParam("lightbox_top_panel_opacity", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
				$arr[] = $this->buildJsParam("lightbox_show_numbers", null, self::TYPE_BOOLEAN);
				$arr[] = $this->buildJsParam("lightbox_numbers_size", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
				$arr[] = $this->buildJsParam("lightbox_numbers_color");
				$arr[] = $this->buildJsParam("lightbox_show_textpanel", null, self::TYPE_BOOLEAN);
				
				$arr[] = $this->buildJsParam("lightbox_textpanel_width", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
				$arr[] = $this->buildJsParam("lightbox_textpanel_enable_title", null, self::TYPE_BOOLEAN);
				$arr[] = $this->buildJsParam("lightbox_textpanel_enable_description", null, self::TYPE_BOOLEAN);
				
				$arr[] = $this->buildJsParam("lightbox_textpanel_title_color");
				$arr[] = $this->buildJsParam("lightbox_textpanel_title_text_align");
				$arr[] = $this->buildJsParam("lightbox_textpanel_title_font_size", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
				$arr[] = $this->buildJsParam("lightbox_textpanel_title_bold", null, self::TYPE_BOOLEAN);
				
				$arr[] = $this->buildJsParam("lightbox_textpanel_desc_color");
				$arr[] = $this->buildJsParam("lightbox_textpanel_desc_text_align");
				$arr[] = $this->buildJsParam("lightbox_textpanel_desc_font_size", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
				$arr[] = $this->buildJsParam("lightbox_textpanel_desc_bold", null, self::TYPE_BOOLEAN);
				
				//lightbox compact related styles
				$arr[] = $this->buildJsParam("lightbox_type");
				$arr[] = $this->buildJsParam("lightbox_arrows_position");
				$arr[] = $this->buildJsParam("lightbox_arrows_inside_alwayson", null, self::TYPE_BOOLEAN);
				$arr[] = $this->buildJsParam("lightbox_numbers_padding_top", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
				$arr[] = $this->buildJsParam("lightbox_numbers_padding_right", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
				$arr[] = $this->buildJsParam("lightbox_textpanel_padding_left", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
				$arr[] = $this->buildJsParam("lightbox_textpanel_padding_right", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
				$arr[] = $this->buildJsParam("lightbox_textpanel_padding_top", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
				$arr[] = $this->buildJsParam("lightbox_slider_image_border", null, self::TYPE_BOOLEAN);
				$arr[] = $this->buildJsParam("lightbox_slider_image_border_width", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
				$arr[] = $this->buildJsParam("lightbox_slider_image_border_color");
				$arr[] = $this->buildJsParam("lightbox_slider_image_border_radius", self::VALIDATE_NUMERIC, self::TYPE_NUMBER);
				$arr[] = $this->buildJsParam("lightbox_slider_image_shadow", null, self::TYPE_BOOLEAN);
				
			}	//tiles type end
			
			
			return($arr);
		}
	
	
	
	/**
	 * get gallery js settings from data
	 */
	public function getUniteGalleryJsSettings($data,UniteCreatorAddon $addon){
		
			$arrJsParams = $this->getArrJsOptions();
			$jsOutput = "";
			$counter = 0;
			$tabs = "								";
			
			foreach($arrJsParams as $arrParam){
				
				$name = $arrParam["name"];
				$validate = $arrParam["validate"];
				$type = $arrParam["type"];
				
				if(array_key_exists($name, $this->skipJsOptions) == true)
					continue;
				
				if($this->isParamExists($name)){
					$value = $this->getParam($name, $validate);
					
					$putInBrackets = false;
					switch($type){
						case self::TYPE_NUMBER:
						case self::TYPE_BOOLEAN:
						case self::TYPE_OBJECT:
						break;
						case self::TYPE_SIZE:
							if(strpos($value, "%") !== 0)
								$putInBrackets = true;
						break;
						default:	//string
							$putInBrackets = true;						
						break;
					}
		
					if($putInBrackets == true){
						$value = str_replace('"','\\"', $value);
						$value = '"'.$value.'"';
					}
					
					if($counter > 0)
						$jsOutput .= ",\n".$tabs;
					$jsOutput .= "{$name}:{$value}";
		
					$counter++;
				}
			}
		
			$jsOutput .= "\n";
		
			return($jsOutput);
	}
	
}