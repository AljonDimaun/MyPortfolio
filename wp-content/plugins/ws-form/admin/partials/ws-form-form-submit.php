<?php

	// Form - Submnissions - Admin Page
	$form_id = $this->ws_form_wp_list_table_submit_obj->form_id;

	// Loader
	WS_Form_Common::loader();
?>
<div id="poststuff">
<div id="wsf-wrapper" class="<?php WS_Form_Common::wrapper_classes(); ?> wsf-sidebar-closed">

<!-- Header -->

<div class="wsf-header">
<h1><?php esc_html_e('Submissions', 'ws-form'); ?></h1>
<?php

	if($form_id > 0) {

		// User capability check
		if(WS_Form_Common::can_user('edit_form')) {
?>
<a class="wsf-button wsf-button-small wsf-button-information" href="<?php echo esc_attr(admin_url('admin.php?page=ws-form-edit&id=' . $form_id)); ?>"><?php WS_Form_Common::render_icon_16_svg('edit'); ?> <?php esc_html_e('Edit', 'ws-form'); ?></a>
<?php
		}
?>
<a class="wsf-button wsf-button-small" href="<?php echo esc_attr(WS_Form_Common::get_preview_url($form_id)); ?>" target="_blank"><?php WS_Form_Common::render_icon_16_svg('visible'); ?> <?php esc_html_e('Preview', 'ws-form'); ?></a>
<?php

		if($this->ws_form_wp_list_table_submit_obj->record_count() > 0) {

			// User capability check
			if(WS_Form_Common::can_user('export_submission')) {
?>
<button data-action="wsf-export-all" class="wsf-button wsf-button-small"><?php WS_Form_Common::render_icon_16_svg('download'); ?> <?php esc_html_e('Export CSV', 'ws-form'); ?></button>
<?php
			}
		}
	}

	// Hook for additional buttons
	do_action('wsf_form_submit_nav_left', $form_id);
?>
</div>
<hr class="wp-header-end">
<!-- /Header -->
<?php

	// Review nag
	WS_Form_Common::review();

	// Prepare
	$this->ws_form_wp_list_table_submit_obj->prepare_items();

	// Views
	$this->ws_form_wp_list_table_submit_obj->views();

	// Ordering
	$order_query_var = WS_Form_Common::get_query_var('order', '');
	$order_by_query_var = WS_Form_Common::get_query_var('orderby', '');
?>
<!-- Submissions Table -->
<div id="wsf-submissions">
<form method="get">
<input type="hidden" name="_wpnonce" value="<?php echo esc_attr(wp_create_nonce('wp_rest')); ?>">
<input type="hidden" name="orderby" value="<?php echo esc_attr($order_by_query_var); ?>">
<input type="hidden" name="order" value="<?php echo esc_attr($order_query_var); ?>">
<input type="hidden" name="page" value="ws-form-submit">
<?php
	
	// Nonce
	wp_nonce_field(WS_FORM_POST_NONCE_ACTION_NAME, WS_FORM_POST_NONCE_FIELD_NAME);

	// Display
	$this->ws_form_wp_list_table_submit_obj->display();
?>
</form>
</div>
<!-- /Submissions Table -->

<!-- View / Edit Sidebar -->
<div id="wsf-sidebars">

	<div id="wsf-sidebar-submit" class="wsf-sidebar wsf-sidebar-closed">

		<!-- Header -->
		<div class="wsf-sidebar-header">

			<h2>
				
				<?php

					WS_Form_Common::render_icon_16_svg('table');
					esc_html_e('Submission', 'ws-form');
				?>

				<!-- Submit ID -->
				<code></code>

			</h2>

		</div>
		<!-- /Header -->

	</div>
	
</div>
<!-- /View / Edit Sidebar -->

<!-- Submissions Actions -->
<form action="<?php echo esc_attr(WS_Form_Common::get_admin_url()); ?>" id="ws-form-action-do" method="post">
<input type="hidden" name="_wpnonce" value="<?php echo esc_attr(wp_create_nonce('wp_rest')); ?>">
<?php wp_nonce_field(WS_FORM_POST_NONCE_ACTION_NAME, WS_FORM_POST_NONCE_FIELD_NAME); ?>
<input type="hidden" name="page" value="ws-form-submit">
<input type="hidden" id="ws-form-action" name="action" value="">
<input type="hidden" id="ws-form-id" name="id" value="<?php echo esc_attr($form_id); ?>">
<input type="hidden" id="ws-form-submit-id" name="submit_id" value="">
<input type="hidden" id="ws-form-date-from" name="date_from" value="<?php echo esc_attr(WS_Form_Common::get_query_var_nonce('date_from', '', false, false, true, 'POST')); ?>">
<input type="hidden" id="ws-form-date-to" name="date_to" value="<?php echo esc_attr(WS_Form_Common::get_query_var_nonce('date_to', '', false, false, true, 'POST')); ?>">
<input type="hidden" id="ws-form-paged" name="paged" value="<?php echo esc_attr(WS_Form_Common::get_query_var_nonce('paged', '', false, false, true, 'POST')); ?>">
<input type="hidden" id="ws-form-status" name="ws-form-status" value="<?php echo esc_attr(WS_Form_Common::get_query_var_nonce('ws-form-status', '', false, false, true, 'POST')); ?>">
</form>
<!-- /Submissions Actions -->

<!-- Popover -->
<div id="wsf-popover" class="wsf-ui-cancel"></div>
<!-- /Popover -->

<script>

	(function($) {

		'use strict';

		// On load
		$(function() {

			// Manually inject language strings (Avoids having to call the full config)
			$.WS_Form.settings_form = [];
			$.WS_Form.settings_form.language = [];
			$.WS_Form.settings_form.language['starred_on'] = '<?php esc_html_e('Starred', 'ws-form'); ?>';
			$.WS_Form.settings_form.language['starred_off'] = '<?php esc_html_e('Not Starred', 'ws-form'); ?>';
			$.WS_Form.settings_form.language['viewed_on'] = '<?php esc_html_e('Mark as Unread', 'ws-form'); ?>';
			$.WS_Form.settings_form.language['viewed_off'] = '<?php esc_html_e('Mark as Read', 'ws-form'); ?>';
			$.WS_Form.settings_form.language['error_server'] = '<?php esc_html_e('500 Server error response from server.', 'ws-form'); ?>';
			$.WS_Form.settings_form.language['error_bad_request_message'] = '<?php esc_html_e('400 Bad request response from server: %s', 'ws-form'); ?>';
			$.WS_Form.settings_form.language['dismiss'] = '<?php esc_html_e('Dismiss', 'ws-form'); ?>';

			// Initialize WS Form
			var wsf_obj = new $.WS_Form();

			wsf_obj.wp_list_table_submit(<?php echo esc_html($form_id); ?>);
		});

	})(jQuery);

</script>

</div>
</div>

<!-- Submit export process -->
<div id="wsf-form-submit-export-popup" class="wsf-form-popup-progress">
	<div class="wsf-form-popup-progress-backdrop"></div>
	<div class="wsf-form-popup-progress-inner"><img src="<?php echo esc_attr(WS_FORM_PLUGIN_DIR_URL) . 'admin/images/loader.gif'; ?>" class="wsf-responsive" width="256" height="256" alt="<?php esc_html_e('Your export is being created...', 'ws-form'); ?>" /><p><?php esc_html_e('Your export is being created...', 'ws-form'); ?></p>
		<div class="wsf-form-popup-progress-bar"><progress class="wsf-progress" max="100" value="0"></progress></div>
	</div>
</div>
<!-- /Submit export process -->

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

</script>
