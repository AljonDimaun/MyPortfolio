<?php

	add_action('init', function() {

		// Litespeed NONCE registration
		do_action('litespeed_nonce', WS_FORM_POST_NONCE_ACTION_NAME);
	});
