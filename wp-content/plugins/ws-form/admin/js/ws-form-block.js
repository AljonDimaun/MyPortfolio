(function($, blocks, editor, element, components) {
 
 	const el = element.createElement;
 
	const { registerBlockType } = blocks;
 
	const { InspectorControls } = editor;
	const { Fragment, RawHTML } = element;
	const { Button, Panel, PanelBody, PanelRow, Placeholder, SelectControl, TextControl } = components;

	// Build icon
 	const icon = el(

		'svg',
		{ width: 20, height: 20 },
		el(
			'path',
			{ fill: '#002E5D', d: 'M0 0v20h20V0zm8.785 13.555h-.829l-1.11-4.966c-.01-.036-.018-.075-.026-.115l-.233-1.297h-.014l-.104.574-.17.838-1.147 4.966h-.836L2.57 6.224h.703l.999 4.27.466 2.23h.044q.133-.966.43-2.243l.998-4.257h.74l1.006 4.27q.119.48.422 2.23h.044q.022-.223.219-1.121t1.254-5.379h.695zm5.645-.389a2.105 2.105 0 0 1-1.54.524 3.26 3.26 0 0 1-.961-.129 2.463 2.463 0 0 1-.644-.283l.309-.534a1.274 1.274 0 0 0 .416.186 2.78 2.78 0 0 0 .925.152 1.287 1.287 0 0 0 .977-.372 1.377 1.377 0 0 0 .355-.993 1.313 1.313 0 0 0-.255-.821 3.509 3.509 0 0 0-.973-.76 6.51 6.51 0 0 1-1.121-.757 2.121 2.121 0 0 1-.466-.635 1.94 1.94 0 0 1-.167-.838A1.67 1.67 0 0 1 11.87 6.6a2.161 2.161 0 0 1 1.487-.517 2.76 2.76 0 0 1 1.567.446l-.31.534a2.425 2.425 0 0 0-1.287-.372 1.422 1.422 0 0 0-.991.334 1.132 1.132 0 0 0-.37.882 1.298 1.298 0 0 0 .252.814 3.792 3.792 0 0 0 1.065.794 6.594 6.594 0 0 1 1.095.767 1.896 1.896 0 0 1 .44.635 2.076 2.076 0 0 1 .144.8 1.94 1.94 0 0 1-.532 1.45zm2.375.598a.671.671 0 1 1 .672-.671.671.671 0 0 1-.672.67zm0-3.242a.671.671 0 1 1 .672-.671.671.671 0 0 1-.672.67zm0-3.284a.671.671 0 1 1 .672-.672.671.671 0 0 1-.672.672z' }
		)
	);

	registerBlockType('wsf-block/form-add', {

		title: wsf_settings_block.form_add.label,

		icon: icon,

		category: wsf_settings_block.form_add.category,

		keywords: wsf_settings_block.form_add.keywords,

		description: wsf_settings_block.form_add.description,

		supports: {

			html: false,
		},

		attributes: {

			form_id: {

				type: 'string'
			},

			form_element_id: {

				type: 'string'
			},

			preview: {

				type: 'boolean'
			}
		},

		example: {

			attributes: {

				'preview' : true,
			}
		},

		edit: function (props) {

			// Show preview SVG
			var preview = props.attributes.preview;
			if(preview) {

				return(

					el('div', { className: 'wsf-block-form-add-preview' }, 

						el(RawHTML, null, wsf_settings_block.form_add.preview)
					)
				);
			}

			// Get attribute values
			var form_id = props.attributes.form_id;
			var form_element_id = props.attributes.form_element_id ? props.attributes.form_element_id : '';

			// Create form selector options
			var options = [];

			// Select... option
			options.push({

				value: 0,
				label: wsf_settings_block.form_add.form_id_options_select
			});

			// Add forms to options
			var form_id_found = false;
			var form_count = 0;
			for(var form_index in wsf_settings_block.forms) {

				if(!wsf_settings_block.forms.hasOwnProperty(form_index)) { continue; }

				var form = wsf_settings_block.forms[form_index];

				if(form.id == form_id) { form_id_found = true; }

				options.push({

					value: form.id,
					label: form.label + ' (' + wsf_settings_block.form_add.id + ': ' + form.id + ')'
				});

				form_count++;
			}
			if(!form_id_found) {

				form_id = 0;
				form_element_id = '';
				props.setAttributes({form_id: form_id, form_element_id: form_element_id});
			}

			function fragment_rendered(props) {

				var block_wrapper_obj = $('#block-' + props.clientId);

				if(
					!block_wrapper_obj.length ||
					!$('form.wsf-form', block_wrapper_obj).length
				) {

					setTimeout(function() {

						fragment_rendered(props);

					}, 50, props);

				} else {

					// Remove messages
					$('[data-wsf-message]', block_wrapper_obj).remove();

					// Read props
					var form_id = props.attributes.form_id;
					var form_element_id = props.attributes.form_element_id;

					// Get block wrapper
					var block_wrapper_obj = $('#block-' + props.clientId);

					// Get form object
					var form_obj = $('form.wsf-form', block_wrapper_obj);

					// Set up this form
					form_obj.off().html('').attr('data-id', form_id).removeAttr('data-instance-id');

					// Custom element ID
					if(form_element_id) {

						form_obj.attr('id', form_element_id).attr('data-wsf-custom-id', '');

					} else {

						form_obj.removeAttr('data-wsf-custom-id');
					}

					// Init forms
					wsf_form_init(true, false, form_obj.parent());
				}
			}

			return (

				el(Fragment, {},

					// Sidebar
					el(InspectorControls, {},

						el(Panel, {

							title: 'test'
						}, 

							el(PanelBody, {

								title: wsf_settings_block.form_add.label,

								initialOpen: true
							},

								// Form selector
								el(SelectControl, {

									label: wsf_settings_block.form_add.form_id_options_label,

									value: form_id,

									options: options,

									onChange: (value) => { props.setAttributes({form_id: value}); }
								}),

								// Form element ID
								el(TextControl, {

									label: wsf_settings_block.form_add.form_element_id_label,

									value: form_element_id,

									placeholder: 'e.g. my-form',

									onChange: (value) => { props.setAttributes({form_element_id: value}); }
								}),

								// Add new form button
								el(Button, {

									isSecondary: true,
									href: wsf_settings_block.form_add.url_add

								}, wsf_settings_block.form_add.add)
							)
						)
					),

					// Block

					(form_count == 0) ? el(Placeholder, {

						// Render no form placeholder
						icon: icon,

						label: wsf_settings_block.form_add.label,

						instructions: wsf_settings_block.form_add.no_forms

					},

						// Add new form button
						el(Button, {

							isSecondary: true,
							href: wsf_settings_block.form_add.url_add

						}, wsf_settings_block.form_add.add)

					) : parseInt(form_id, 10) ? el('div', null, 

						// Render WS Form
						el('form', {

							action: wsf_settings_block.form_add.form_action,
							className: 'wsf-form wsf-form-canvas',
							method: 'POST'

						}, fragment_rendered(props))

					// If form ID not set
					) : el(Placeholder, {

						// Render no form selected
						icon: icon,

						label: wsf_settings_block.form_add.label,

						instructions: wsf_settings_block.form_add.form_not_selected
					})
				)
			);
		},

		save: function () { null; }
	});
})(
	jQuery,
	window.wp.blocks,
	window.wp.blockEditor,
	window.wp.element,
	window.wp.components
);

