(function($) {

	'use strict';

	$(window).on('elementor/popup/show', function(event, id, instance) {

		if(typeof(wsf_form_init) === 'function') {

			wsf_form_init(true, true, $('[data-elementor-id="' + id + '"]'));
		}
	});

})(jQuery);