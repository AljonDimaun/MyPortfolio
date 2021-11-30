<?php

	class WS_Form_Data_Source_Cron {

		public function __construct() {

			// Register additional schedules
			self::cron_schedules();

			// Data source cron processes
			add_action(WS_FORM_DATA_SOURCE_SCHEDULE_HOOK, array($this, 'schedule_run'), 10, 2);
		}

		// Data source cron processing
		public function schedule_run($form_id, $field_id) {

			// Field types
			$field_types = WS_Form_Config::get_field_types_flat();

			// Get field data
			try{

				$ws_form_field = new WS_Form_Field();
				$ws_form_field->id = $field_id;
				$field = $ws_form_field->db_read(true, true);	// read meta, bypass security checks

			} catch (Exception $e) {

				self::error($e->getMessage(), $field_id);
				exit;
			}

			// Check to see if field type exists
			if(!isset($field_types[$field->type])) {

				self::error(__('Invalid field type', 'ws-form'), $field_id);
				exit;
			}

			// Get field config
			$field_config = $field_types[$field->type];

			// Get field type data source
			$data_source = isset($field_config['data_source']) ? $field_config['data_source'] : false;
			if(
				($data_source === false) ||
				!isset($data_source['id'])
			) {

				exit;
			}

			// Get meta key
			$meta_key = $data_source['id'];

			// Get meta keys
			$meta_keys = WS_Form_Config::get_meta_keys();

			// Check meta key exists
			if(!isset($meta_keys[$meta_key])) {

				self::error(__('Invalid meta key', 'ws-form'), $field_id);
				exit;
			}

			$meta_key_config = $meta_keys[$meta_key];

			// Check if data source enabled
			$data_source_enabled = isset($meta_key_config['data_source']) ? $meta_key_config['data_source'] : false;
			if(!$data_source_enabled) { exit; }

			// Check if data source ID is set
			$data_source_id = WS_Form_Common::get_object_meta_value($field, 'data_source_id', '');

			if(
				($data_source_id === '') ||
				!isset(WS_Form_Data_Source::$data_sources[$data_source_id]) ||
				!method_exists(WS_Form_Data_Source::$data_sources[$data_source_id], 'get_data_source_meta_keys')
			) {

				self::error(__('Invalid data source', 'ws-form'), $field_id);
				exit;
			}

			$data_source = WS_Form_Data_Source::$data_sources[$data_source_id];

			// Get meta keys
			$meta_keys = $data_source->config_meta_keys();

			// Get data source settings
			$data_source_meta_keys = $data_source->get_data_source_meta_keys();

			// Configure
			foreach($data_source_meta_keys as $data_source_meta_key) {

				$meta_value_default = isset($meta_keys[$data_source_meta_key]['default']) ? $meta_keys[$data_source_meta_key]['default'] : false;

				$data_source->{$data_source_meta_key} = WS_Form_Common::get_object_meta_value($field, $data_source_meta_key, $meta_value_default);
			}

			// Get existing meta_value
			$meta_value = WS_Form_Common::get_object_meta_value($field, $meta_key, false);
			$checksum_old = md5(serialize($meta_value));

			// Get replacement meta_value
			$get_return = $data_source->get(false, $field_id, 1, $meta_key, $meta_value, true);	// true = No paging

			// Error checking
			if(isset($get_return['error']) && $get_return['error']) {

				exit;

			} else {

				// Check if data source ID is set
				$data_source_last_api_error = WS_Form_Common::get_object_meta_value($field, 'data_source_last_api_error', '');

				// Clear last_api_error
				if($data_source_last_api_error !== '') {

					$ws_form_field = new WS_Form_Field();
					$ws_form_field->id = $field_id;
					$ws_form_field->db_last_api_error_clear();
				}
			}

			// Get new meta_value
			$meta_value = $get_return['meta_value'];
			$checksum_new = md5(serialize($meta_value));

			// Update if data has changed
			if($checksum_old != $checksum_new) {

				// Build new meta array
				$meta_array = array(

					'data_source_last_api_error' => '',	// Clear last API error
					$meta_key => $meta_value
				);

				try{

					// Save new meta value
					$ws_form_meta = new WS_Form_Meta();
					$ws_form_meta->parent_id = $field_id;
					$ws_form_meta->object = 'field';
					$ws_form_meta->db_update_from_array($meta_array, false, true);

				} catch (Exception $e) {

					self::error($e->getMessage(), $field_id, $data_source);
					exit;
				}

				try{

					// Re-publish form
					$ws_form_form = new WS_Form_Form();
					$ws_form_form->id = $form_id;
					$ws_form_form->db_publish(true, false);	// true - bypass security checks, false = Do not reschedule data source schedules

				} catch (Exception $e) {

					self::error($e->getMessage(), $field_id, $data_source);
					exit;
				}
			}
		}

		// Schedule - Add
		public function schedule_add($form_id, $field_id, $recurrence) {

			// Only add if recurrence valid
			if($recurrence === 'wsf_realtime') { return; }
			if(empty($recurrence)) { return; }
			$schedule = wp_get_schedules();
			if(!isset($schedule[$recurrence])) { return; }

			// Schedule args
			$args = array(

				'form_id' => $form_id,
				'field_id' => $field_id
			);

			// Check if schedule already exists
			$schedule_name = wp_get_schedule(WS_FORM_DATA_SOURCE_SCHEDULE_HOOK, $args);

			// Schedule event for data source
			wp_schedule_event(time(), $recurrence, WS_FORM_DATA_SOURCE_SCHEDULE_HOOK, $args);

			// Initial run
//			self::schedule_run($form_id, $field_id, true);
		}

		// Schedule - Clear all for form
		public function schedule_clear_all($form_id) {

			$scheduled_events = _get_cron_array();

			// If there are no scheduled events, return
			if(empty($scheduled_events)) { return; }

			// Run through each scheduled event
			foreach($scheduled_events as $timestamp => $cron) {

				// If this is not a WS Form data source hook, skip it
				if(!isset($cron[WS_FORM_DATA_SOURCE_SCHEDULE_HOOK])) { continue; }

				// Check the contents of the scheduled event
				foreach($cron[WS_FORM_DATA_SOURCE_SCHEDULE_HOOK] as $cron_element_id => $cron_element) {

					if(!isset($cron_element['args'])) { continue 2; }
					if(!isset($cron_element['args']['form_id'])) { continue 2; }
					if($cron_element['args']['form_id'] != $form_id) { continue 2; }
				}

				// Delete this scheduled event
				unset($scheduled_events[$timestamp][WS_FORM_DATA_SOURCE_SCHEDULE_HOOK]);

				// If this time stamp is now empty, delete it in its entirety
				if(empty($scheduled_events[$timestamp])) {

					unset($scheduled_events[$timestamp]);
				}
			}

			// Save the scheduled events back
			_set_cron_array($scheduled_events);
		}

		// Schedule - Register additional schedules
		public function cron_schedules() {

			add_filter('cron_schedules', function($schedules) {

				$schedules['wsf_minute'] = array(

					'interval' => 60,
					'display' => esc_html__( 'Once Every Minute' ),
				);

				$schedules['wsf_quarter_hour'] = array(

					'interval' => 900,
					'display' => esc_html__( 'Once Every 15 Minutes' ),
				);

				$schedules['wsf_half_hour'] = array(

					'interval' => 1800,
					'display' => esc_html__( 'Once Every 30 Minutes' ),
				);

				return $schedules;
			});
		}
	}

	new WS_Form_Data_Source_Cron();
