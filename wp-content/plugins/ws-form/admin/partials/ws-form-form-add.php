<?php

	global $wpdb;

	// Get core template data
	$ws_form_template = new WS_Form_Template;
	$template_categories = $ws_form_template->read_config();

	// Add action categories
	$actions = $ws_form_template->db_get_actions();
	if($actions !== false) {

		foreach($actions as $action) {

			$action->action_id = $action->id;
			$action->action_list_sub_modal_label = isset($action->list_sub_modal_label) ? $action->list_sub_modal_label : false;
			$template_categories[] = $action;
		}
	}

	// Order template categories by priority, then label
	uasort($template_categories, function($a, $b) {

		$pa = isset($a->priority) ? $a->priority : 0;
		$pb = isset($b->priority) ? $b->priority : 0;

		if($pa === $pb) {

			return ($a->label === $b->label) ? 0 : (($a->label > $b->label) ? 1 : -1);

		} else {

			return ($pa < $pb) ? 1 : -1;
		}
	});

	// Loader icon
	WS_Form_Common::loader();
?>
<script>

	// Localize
	var ws_form_settings_language_form_add_create = '<?php esc_html_e('Use Template', 'ws-form'); ?>';

</script>

<div id="wsf-wrapper" class="<?php WS_Form_Common::wrapper_classes(); ?>">

<!-- Header -->
<div class="wsf-header">
<h1><?php esc_html_e('Add New', 'ws-form'); ?></h1>
</div>
<hr class="wp-header-end">
<!-- /Header -->
<?php

	// Review nag
	WS_Form_Common::review();
?>
<!-- Template -->
<div id="wsf-form-add">

<p><?php echo __('Choose a template or start with a <a href="#" data-action="wsf-add-blank">blank form</a>.', 'ws-form'); ?></p>

<!-- Tabs - Categories -->
<ul id="wsf-form-add-tabs">
<?php

	// Loop through templates
	foreach ($template_categories as $template_category)  {

		if(isset($template_category->templates) && (count($template_category->templates) == 0)) { continue; }

		$action_id = isset($template_category->action_id) ? $template_category->action_id : false;

?><li><a href="<?php echo esc_attr(sprintf('#wsf_template_category_%s', $template_category->id)); ?>"><?php echo esc_html($template_category->label); ?><?php

		if(($action_id !== false) && ($template_category->reload)) {

?><span data-action="wsf-api-reload" data-action-id="<?php echo esc_html($action_id); ?>" data-method="lists_fetch"<?php echo WS_Form_Common::tooltip(__('Update', 'ws-form'), 'top-center'); ?>><?php WS_Form_Common::render_icon_16_svg('reload'); ?></span><?php

		}

?></a></li>
<?php

	}
?>
</ul>
<!-- Tabs - Categories -->
<?php

	// Loop through templates
	foreach ($template_categories as $template_category)  {

		if(isset($template_category->templates) && (count($template_category->templates) == 0)) { continue; }
?>
<!-- Tab Content: <?php echo esc_html($template_category->label); ?> -->
<div id="<?php echo esc_attr(sprintf('wsf_template_category_%s', $template_category->id)); ?>"<?php if(isset($template_category->action_id)) { ?> data-action-id="<?php echo esc_attr($template_category->action_id); ?>"<?php } ?><?php if(isset($template_category->action_list_sub_modal_label)) { ?> data-action-list-sub-modal-label="<?php echo esc_html($template_category->action_list_sub_modal_label); ?>"<?php } ?> style="display: none;">
<ul class="wsf-templates">
<?php
		$ws_form_template->template_category_render($template_category);
?>
</ul>

</div>
<!-- /Tab Content: <?php echo esc_html($template_category->label); ?> -->
<?php

	}
?>

</div>
<!-- /Template -->

<!-- Loading -->
<div id="wsf-form-add-loading" class="wsf-form-popup-progress">
	<div class="wsf-form-popup-progress-backdrop"></div>
	<div class="wsf-form-popup-progress-inner"><img src="<?php echo esc_attr(WS_FORM_PLUGIN_DIR_URL) . 'admin/images/loader.gif'; ?>" class="wsf-responsive" width="256" height="256" alt="<?php esc_html_e('Your form is being created...', 'ws-form'); ?>" /><p><?php esc_html_e('Your form is being created...', 'ws-form'); ?></p></div>
</div>
<!-- /Loading -->

<!-- WS Form - Modal - List Sub -->
<div id="wsf-list-sub-modal-backdrop" class="wsf-modal-backdrop" style="display:none;"></div>

<div id="wsf-list-sub-modal" class="wsf-modal" style="display:none; margin-left:-200px; margin-top:-100px; width: 400px;">

<div id="wsf-list-sub">

<!-- WS Form - Modal - List Sub - Header -->
<div class="wsf-modal-title"><?php

	echo WS_Form_Common::get_admin_icon('#002e5f', false); // phpcs:ignore

?><span></span></div>
<div class="wsf-modal-close" data-action="wsf-close" title="<?php esc_attr_e('Close', 'ws-form'); ?>"></div>
<!-- /WS Form - Modal - List Sub - Header -->

<!-- WS Form - Modal - List Sub - Content -->
<div class="wsf-modal-content">

<form>

<select id="wsf-list-sub-id"></select>

</form>

</div>
<!-- /WS Form - Modal - List Sub - Content -->

<!-- WS Form - Modal - List Sub - Buttons -->
<div class="wsf-modal-buttons">

<div id="wsf-modal-buttons-cancel">
<a data-action="wsf-close"><?php esc_html_e('Cancel', 'ws-form'); ?></a>
</div>

<div id="wsf-modal-buttons-list-sub">
<button class="button button-primary" data-action="wsf-add-template-action-modal"><?php esc_html_e('Create', 'ws-form'); ?></button>
</div>

</div>
<!-- /WS Form - Modal - List Sub - Buttons -->

</div>

</div>
<!-- /WS Form - Modal - List Sub -->

<!-- Form Actions -->
<form action="<?php echo esc_attr(WS_Form_Common::get_admin_url()); ?>" id="ws-form-action-do" method="post">
<input type="hidden" name="_wpnonce" value="<?php echo esc_attr(wp_create_nonce('wp_rest')); ?>">
<?php wp_nonce_field(WS_FORM_POST_NONCE_ACTION_NAME, WS_FORM_POST_NONCE_FIELD_NAME); ?>
<input type="hidden" name="page" value="ws-form">
<input type="hidden" id="ws-form-action" name="action" value="">
<input type="hidden" id="ws-form-id" name="id" value="">
<input type="hidden" id="ws-form-action-id" name="action_id" value="">
<input type="hidden" id="ws-form-list-id" name="list_id" value="">
<input type="hidden" id="ws-form-list-sub-id" name="list_sub_id" value="">
<?php

	do_action('wsf_form_add_hidden');
?>
</form>
<!-- /Form Actions -->

<script>

	(function($) {

		'use strict';

		// On load
		$(function() {

			// Init template functionality
			var wsf_obj = new $.WS_Form();

			wsf_obj.tooltips();
			wsf_obj.template();
		});

	})(jQuery);

</script>

</div>
