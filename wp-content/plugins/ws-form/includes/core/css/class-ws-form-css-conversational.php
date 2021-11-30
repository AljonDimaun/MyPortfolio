<?php

	class WS_Form_CSS_Conversational extends WS_Form_CSS {

		// Render
		public function render_conversational() {

			// Load skin
			$this->skin_id = 'ws_form_conv';
			self::skin_load();

			// Load variables
			self::skin_variables();

			// Skin color shades
			self::skin_color_shades();

			// Forms
			$uom = 'px';
			$this->spacing_vertical = 8.5;
			$input_height = round(($this->font_size * $this->line_height) + ($this->spacing_vertical * 2) + ($this->border_width * 2));
?>
/* Skin ID: <?php echo $this->skin_label; ?> (<?php echo $this->skin_id; ?>) */

/* Global */
html {
	-webkit-box-sizing: border-box;
	box-sizing: border-box;
	-webkit-tap-highlight-color: transparent;
	-webkit-text-size-adjust: 100%;
	-moz-text-size-adjust: 100%;
	-ms-text-size-adjust: 100%;
	text-size-adjust: 100%;
}

*, *:before, *:after {
	-webkit-box-sizing: inherit;
	box-sizing: inherit;
}

body {
<?php if ($this->conversational_color_background) { ?>
	background-color: <?php self::e($this->conversational_color_background); ?>;
<?php } ?>
	height: 100vh;
	margin: 0;
}

article, aside, details, figcaption, figure, footer, header, hgroup, main, menu, nav, section, summary {
	display: block;
}

audio, canvas, video {
	display: inline-block;
	vertical-align: baseline;
}

audio:not([controls]) {
	display: none;
	height: 0;
}

::-moz-selection {
	color: <?php self::e($this->color_default_inverted); ?>;
	background-color: <?php self::e($this->color_primary); ?>;
	text-shadow: none;
}

::selection {
	color: <?php self::e($this->color_default_inverted); ?>;
	background-color: <?php self::e($this->color_primary); ?>;
	text-shadow: none;
}

a {
	background-color: transparent;
	color: <?php self::e($this->color_primary); ?>;
	font-weight: bold;
	text-decoration: none;
	-ms-touch-action: manipulation;
	touch-action: manipulation;
	-webkit-transition: color 200ms ease-in-out;
	transition: color 200ms ease-in-out;
}

a:active, a:hover {
	color: <?php self::e($this->color_primary); ?>;
	outline: 0;
}

area {
	-ms-touch-action: manipulation;
	touch-action: manipulation;
}

h1, h2, h3, h4, h5, h6 {
	font-weight: <?php self::e($this->font_weight); ?>;
	line-height: 1.2;
	margin-bottom: 10px;
	margin-top: 0;
}

h1 {
	font-size: 50px;
}

h2 {
	font-size: 40px;
}

h3 {
	font-size: 35px;
}

h4 {
	font-size: 30px;
}

h5 {
	font-size: 25px;
}

h6 {
	font-size: 20px;
}

p {
	font-size: 20px;
	margin-bottom: 20px;
	margin-top: 0;
}

ul, ol {
	font-size: 20px;
}

@media (max-width: 575px) {
	h1 {
		font-size: 40px;
	}

	h2 {
		font-size: 35px;
	}

	h3 {
		font-size: 30px;
	}

	h4 {
		font-size: 25px;
	}

	h5 {
		font-size: 20px;
	}

	h6 {
		font-size: 16px;
	}

	p {
		font-size: 16px;
	}

	ul, ol {
		font-size: 16px;
	}
}

hr {
	border-bottom: <?php self::e($this->border_width . $uom . ' ' . $this->border_style . ' ' . $this->color_default_lighter); ?>;
	border-top: none;
	-webkit-box-sizing: content-box;
	box-sizing: content-box;
	height: 0;
	margin-bottom: 20px;
	margin-top: 20px;
}

abbr[title] {
	border-bottom: <?php self::e($this->border_width . $uom . ' ' . $this->border_style . ' ' . $this->color_default_lighter); ?>;
}

b, strong {
	font-weight: bold;
}

dfn {
	font-style: italic;
}

mark {
	color: #373A3C;
}

small {
	font-size: <?php self::e($this->font_size_small); ?>;
}

sub, sup {
	font-size: <?php self::e($this->font_size_small); ?>;
	line-height: 0;
	position: relative;
	vertical-align: baseline;
}

sup {
	top: -0.5em;
}

sub {
	bottom: -0.25em;
}

ul, ol, dl {
	margin: 0 0 20px;
	padding-left: 20px;
}

ol ol, ul ul, ol ul, ul ol {
	margin-bottom: 0;
}

pre {
	margin: 0 0 20px;
	overflow: auto;
}

code, kbd, pre, samp {
	font-family: monospace, monospace;
	font-size: 1em;
}

blockquote {
	margin: 0 0 20px;
}

blockquote cite {
	color: #8E8E93;
}

svg:not(:root) {
	overflow: hidden;
}

figure {
	margin: 0 0 20px;
}

img {
	border: 0;
}

/* WordPress */
.alignnone {
	height: auto;
	margin-bottom: 20px;
	max-width: 100%;
}

.aligncenter,
div.aligncenter {
	display: block;
	height: auto;
	margin: 0 auto 20px;
	max-width: 100%;
}

.alignright {
	float: right;
	height: auto;
	-webkit-margin-start: 20px;
	margin-inline-start: 20px;
	margin-bottom: 20px;
	max-width: 100%;
}

.alignleft {
	float: left;
	height: auto;
	-webkit-margin-end: 20px;
	margin-inline-end: 20px;
	margin-bottom: 20px;
	max-width: 100%;
}

a img.alignright {
	float: right;
	-webkit-margin-start: 20px;
	margin-inline-start: 20px;
	margin-bottom: 20px;
}

a img.alignnone {
	margin-bottom: 20px;
}

a img.alignleft {
	float: left;
	-webkit-margin-end: 20px;
	margin-inline-end: 20px;
	margin-bottom: 20px;
}

a img.aligncenter {
	display: block;
	margin: 0 auto 20px;
}

.wp-caption {
	text-align: center;
}

.wp-caption.alignnone {
	margin-bottom: 20px;
}

.wp-caption.alignleft {
	-webkit-margin-end: 20px;
	margin-inline-end: 20px;
	margin-bottom: 20px;
}

.wp-caption.alignright {
	-webkit-margin-start: 20px;
	margin-inline-start: 20px;
	margin-bottom: 20px;
}

.wp-caption img {
	border: 0 none;
	height: auto;
	margin: 0;
	padding: 0;
	width: auto;
}

.wp-caption .wp-caption-text {
	font-size: <?php self::e($this->font_size_small . $uom); ?>;
}

/* WS Form */
.wsf-form {
<?php if ($this->conversational_max_width) { ?>
	margin: 0 auto;
	max-width: <?php self::e($this->conversational_max_width); ?>;
<?php } ?>
	padding: 0 <?php self::e($this->grid_gutter . $uom); ?>;
}

.wsf-section {
<?php if ($this->conversational_opacity_section_inactive != '') { ?>
	opacity: <?php self::e($this->conversational_opacity_section_inactive); ?>;
<?php } ?>
<?php if ($this->transition) { ?>
	transition: opacity <?php self::e($this->transition_speed . 'ms ' . $this->transition_timing_function); ?>;
<?php } ?>
}

.wsf-section.wsf-form-conversational-active {
	opacity: 1;
}

.wsf-section.wsf-form-conversational-section-full-height {
	align-items: center;
	display: flex;
	margin: 0;
	padding-bottom: 0;
	padding-top: <?php self::e($this->grid_gutter . $uom); ?>;
}

.wsf-section.wsf-form-conversational-section-full-height > .wsf-grid {
	width: calc(100% + <?php self::e($this->grid_gutter . $uom); ?>);
}

input[type=number]::-webkit-outer-spin-button,
input[type=number]::-webkit-inner-spin-button {
	-webkit-appearance: none;
	margin: 0;
}

input[type=number] {
	-moz-appearance: textfield;
}

.wsf-form-conversational-inactive select {
	pointer-events: none;
}

.xdsoft_datetimepicker {
	left: auto !important;
	top: auto !important;
}

.wsf-form-conversational-nav {
<?php if ($this->conversational_color_background_nav) { ?>
	background-color: <?php self::e($this->conversational_color_background_nav); ?>;
<?php } ?>
	bottom: 0;
	left: 0;
	padding: 20px <?php self::e($this->grid_gutter . $uom); ?>;
	position: fixed;
	width: 100%;
}

.wsf-form-conversational-nav > div {
<?php if ($this->conversational_max_width) { ?>
	margin: 0 auto;
	max-width: <?php self::e($this->conversational_max_width); ?>;
<?php } ?>
}

.wsf-form-conversational-nav ul {
	display: flex;
	list-style: none;
	margin: 0 -10px;
	padding: 0;
}

.wsf-form-conversational-nav ul li {
	padding: 0 10px;
}

.wsf-form-conversational-nav ul li:first-child {
	align-self: center;
	flex: 1;
	-webkit-padding-end: 30px;
	padding-inline-end: 30px;
}

.wsf-form-conversational-nav-progress-help {
	color: <?php self::e($this->conversational_color_foreground_nav); ?>;
}

.wsf-form-conversational-nav-move-up svg,
.wsf-form-conversational-nav-move-down svg {
	cursor: pointer;
	display: block;
	height: <?php self::e($input_height . $uom); ?>;
	width: <?php self::e($input_height . $uom); ?>;
}

.wsf-form-conversational-nav-move-up svg path,
.wsf-form-conversational-nav-move-down svg path {
	fill: <?php self::e($this->conversational_color_foreground_nav); ?>;
}

[data-wsf-message] {
<?php if ($this->conversational_max_width) { ?>
	margin: 0 auto;
	max-width: <?php self::e($this->conversational_max_width); ?>;
<?php } ?>
   	padding: <?php self::e($this->grid_gutter . $uom); ?> <?php self::e($this->grid_gutter . $uom); ?> 0;
}

.wsf-alert {
	width: 100%;
}

<?php
		}

		// Skin - RTL
		public function render_conversational_rtl() {
?>
/* Global */
ul, ol, dl {
	padding-left: 0;
	padding-right: 20px;
}

/* WordPress */
.alignright {
	float: left;
}

.alignleft {
	float: right;
}

a img.alignright {
	float: left;
}

a img.alignleft {
	float: right;
}

<?php
		}
	}
