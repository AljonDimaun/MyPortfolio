<?php

	class WS_Form_CSS_Email extends WS_Form_CSS {

		// Get
		public function get_email() {

			$css_return = '	svg { max-width: 100%; }

	h1, h2, h3, h4 {

		font-family: sans-serif;
		font-weight: bold;
		margin: 0;
		margin-bottom: 10px;"
	}
	h1 {
		font-size: 24px !important;
	}
	h2 {
		font-size: 22px !important;
	}
	h3 {
		font-size: 20px !important;
	}
	h4 {
		font-size: 18px !important;
	}
	p,li,td,span,a {

		font-family: sans-serif;
		font-size: 14px;
		font-weight: normal;
		margin: 0;
		margin-bottom: 10px;"
 	}

	@media only screen and (max-width: 620px) {

		p,li,td,span,a {
			font-size: 16px;
	 	}
		.wrapper {
			padding: 10px !important;
		}
		.content {
			padding: 0 !important;
		}
		.container {
			padding: 0 !important;
			width: 100% !important;
		}
		.main {
			border-left-width: 0 !important;
			border-radius: 0 !important;
			border-right-width: 0 !important;
		}
	}
			';

			// Apply filters
			$css_return = apply_filters('wsf_get_email', $css_return);

			// Minify
			$css_minify = !SCRIPT_DEBUG;

			return $css_minify ? self::minify($css_return) : $css_return;
		}
	}
