<?php

	class WS_Form_Oxygen {

		public $section_slug = "wsform_section";

		public function __construct() {

			// Adds section to +Add sidebar
			add_action('oxygen_add_plus_sections', [$this, 'add_plus_sections']);

			// Content for the +Add sidebar section	        
			add_action("oxygen_add_plus_" . $this->section_slug . "_section_content", [$this, 'add_plus_section_content']);
		}

		public function add_plus_sections() {

			// Add new accordion section for WS Form elements
			CT_Toolbar::oxygen_add_plus_accordion_section($this->section_slug, WS_FORM_NAME_PRESENTABLE);
		}

		public function add_plus_section_content() {

			// Add subsection content
			do_action("oxygen_add_plus_" . $this->section_slug . "_other");
		}
	}
	new WS_Form_Oxygen();
