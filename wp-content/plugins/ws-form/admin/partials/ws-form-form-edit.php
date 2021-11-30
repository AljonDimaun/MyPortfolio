<?php

	// Get ID of form (0 = New)
	$form_id = intval(WS_Form_Common::get_query_var('id', 0));

	// Loader icon
	WS_Form_Common::loader();
?>
<!-- Layout Editor -->
<div id="wsf-layout-editor">

<!-- Header -->
<div class="wsf-loading-hidden">
<div id="wsf-header">
<h1><?php esc_html_e('Edit Form', 'ws-form') ?></h1>

<!-- Form actions -->
<?php

	// Publish
	if(WS_Form_Common::can_user('publish_form')) {
?>
<button data-action="wsf-publish" class="wsf-button wsf-button-small wsf-button-information" disabled><?php WS_Form_Common::render_icon_16_svg('publish'); ?> <?php esc_html_e('Publish', 'ws-form'); ?></button>
<?php
	}

	// Preview
?>
<a data-action="wsf-preview" class="wsf-button wsf-button-small" href="<?php echo esc_attr(WS_Form_Common::get_preview_url($form_id)); ?>" target="wsf-preview-<?php echo esc_attr($form_id); ?>"><?php WS_Form_Common::render_icon_16_svg('visible'); ?> <?php esc_html_e('Preview', 'ws-form'); ?></a>
<?php

	// Submissions
	if(WS_Form_Common::can_user('read_submission')) {
?>
<a data-action="wsf-submission" class="wsf-button wsf-button-small" href="<?php echo esc_attr(admin_url('admin.php?page=ws-form-submit&id=' . $form_id)); ?>"><?php WS_Form_Common::render_icon_16_svg('table'); ?> <?php esc_html_e('Submissions', 'ws-form'); ?></a>
<?php
	}

	// Hook for additional buttons
	do_action('wsf_form_edit_nav_left', $form_id);
?>
<ul class="wsf-settings wsf-settings-form">
<?php
	// Download
	if(WS_Form_Common::can_user('export_form')) {
?>
<li data-action="wsf-form-download"<?php echo WS_Form_Common::tooltip(__('Export Form', 'ws-form'), 'bottom-center'); ?>><?php WS_Form_Common::render_icon_16_svg('download'); ?></li>
<?php
	}
	
	// Upload
	if(WS_Form_Common::can_user('import_form')) {
?>
<li data-action="wsf-form-upload"<?php echo WS_Form_Common::tooltip(__('Import Form', 'ws-form'), 'bottom-center'); ?>><?php WS_Form_Common::render_icon_16_svg('upload'); ?></li>
<?php
	}
?>
<li data-action="wsf-redo"<?php echo WS_Form_Common::tooltip(__('Redo', 'ws-form'), 'bottom-center'); ?> class="wsf-redo-inactive"><?php WS_Form_Common::render_icon_16_svg('redo'); ?></li>
<li data-action="wsf-undo"<?php echo WS_Form_Common::tooltip(__('Undo', 'ws-form'), 'bottom-center'); ?> class="wsf-undo-inactive"><?php WS_Form_Common::render_icon_16_svg('undo'); ?></li>
</ul>
<?php

	// Upload
	if(WS_Form_Common::can_user('import_form')) {
?>
<input type="file" id="wsf-object-upload-file" class="wsf-file-upload" accept=".json"/>
<?php
	}
?>
</div>
</div>
<!-- /Header -->
<?php

	// Review nag
	WS_Form_Common::review();
?>
<!-- Wrapper -->
<div id="poststuff" class="wsf-loading-hidden">

<hr class="wp-header-end">

<!-- Title -->
<div id="titlediv">
<div id="titlewrap">

<label class="screen-reader-text" id="title-prompt-text" for="title"><?php esc_html_e('Form Name', 'ws-form'); ?></label>
<input type="text" id="title" class="wsf-field" placeholder="<?php esc_attr_e('Form Title', 'ws-form'); ?>" data-action="wsf-form-label" name="form_label" size="30" value="" spellcheck="true" autocomplete="off" />

</div>
</div>
<!-- /Title -->

<!-- Form -->
<div id="wsf-form" class="wsf-form wsf-form-canvas"></div>
<!-- /Form -->

</div>
<!-- /Wrapper -->

<!-- Breakpoints -->
<div id="wsf-breakpoints"></div>
<!-- /Breakpoints -->

</div>
<!-- /Layout Editor -->

<!-- Popover -->
<div id="wsf-popover" class="wsf-ui-cancel"></div>
<!-- /Popover -->

<!-- Field Draggable Container (Fixes Chrome bug) -->
<div class="wsf-field-selector"><div id="wsf-field-draggable"><ul></ul></div></div>
<!-- /Field Draggable Container (Fixes Chrome bug) -->

<!-- Section Draggable Container (Fixes Chrome bug) -->
<div class="wsf-section-selector"><div id="wsf-section-draggable"><ul></ul></div></div>
<!-- /Section Draggable Container (Fixes Chrome bug) -->

<!-- Sidebars -->
<div id="wsf-sidebars"></div>
<!-- /Sidebars -->

<script>

<?php

	// Get config
	$json_config = WS_Form_Config::get_config(false, array(), true);
?>
	// Embed config
	var wsf_form_json_config = {};
<?php

	// Split up config (Fixes HTTP2 error on certain hosting providers that can't handle the full JSON string)
	foreach($json_config as $key => $config) {

?>	wsf_form_json_config.<?php echo $key; ?> = <?php echo wp_json_encode($config); ?>;
<?php
	}

	$json_config = null;

	// Get form data
	try {

		$ws_form_form = New WS_Form_Form();
		$ws_form_form->id = $form_id;
		$form_object = $ws_form_form->db_read(true, true);
		$json_form = wp_json_encode($form_object);

	} catch(Exception $e) {

		$json_form = false;
	}
?>

	// Embed form data
	var wsf_form_json = { <?php

	echo $form_id;		// phpcs:ignore

?>: <?php

	echo $json_form;	// phpcs:ignore
	$json_form = null;

?> };

	var wsf_obj = null;

	(function($) {

		'use strict';

		// On load
		$(function() {

			// Manually inject language strings (Avoids having to call the full config)
			$.WS_Form.settings_form = [];
			$.WS_Form.settings_form.language = [];
			$.WS_Form.settings_form.language['error_server'] = '<?php esc_html_e('500 Server error response from server.', 'ws-form'); ?>';

			// Initialize WS Form
			var wsf_obj = new $.WS_Form();
			wsf_obj.render({

				'obj' : 	'#wsf-form',
				'form_id':	<?php echo esc_attr($form_id); ?>
			});
			wsf_obj.menu_highlight();
		});

	})(jQuery);

</script>
