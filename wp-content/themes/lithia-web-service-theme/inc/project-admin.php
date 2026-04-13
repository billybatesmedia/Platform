<?php
/**
 * Project admin UI for V1 intake and import workflow.
 *
 * @package LithiaWebServiceTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return the Project admin page slug.
 *
 * @return string
 */
function lithia_get_project_admin_page_slug(): string {
	return 'lithia-project-manager';
}

/**
 * Return the option name for the saved payload draft JSON.
 *
 * @return string
 */
function lithia_get_project_payload_draft_option_name(): string {
	return 'lithia_project_payload_draft_json';
}

/**
 * Return the option name for the last project admin report.
 *
 * @return string
 */
function lithia_get_project_admin_last_report_option_name(): string {
	return 'lithia_project_admin_last_report';
}

/**
 * Return the option name for import history.
 *
 * @return string
 */
function lithia_get_project_import_history_option_name(): string {
	return 'lithia_project_import_history';
}

/**
 * Return the max number of import history entries to keep.
 *
 * @return int
 */
function lithia_get_project_import_history_limit(): int {
	return 12;
}

/**
 * Return available sample payload definitions.
 *
 * @return array
 */
function lithia_get_project_admin_sample_payload_definitions(): array {
	return array(
		'service-business-v1-starter' => array(
			'label' => __( 'Service Business Starter', 'lithia-web-service-theme' ),
			'path'  => trailingslashit( ABSPATH ) . 'Docs/template-system/sample-payloads/service-business-v1-starter.json',
		),
	);
}

/**
 * Return the transient key used for one-time admin notices.
 *
 * @return string
 */
function lithia_get_project_admin_notice_transient_key(): string {
	return 'lithia_project_admin_notice_' . get_current_user_id();
}

/**
 * Return the transient key used to preserve invalid JSON edits.
 *
 * @return string
 */
function lithia_get_project_admin_pending_payload_transient_key(): string {
	return 'lithia_project_admin_pending_payload_' . get_current_user_id();
}

/**
 * Persist a one-time project admin notice.
 *
 * @param string $message Notice text.
 * @param string $type    Notice type.
 * @return void
 */
function lithia_set_project_admin_notice( string $message, string $type = 'success' ): void {
	set_transient(
		lithia_get_project_admin_notice_transient_key(),
		array(
			'message' => sanitize_text_field( $message ),
			'type'    => in_array( $type, array( 'success', 'error', 'warning', 'info' ), true ) ? $type : 'success',
		),
		MINUTE_IN_SECONDS
	);
}

/**
 * Return and clear the current project admin notice.
 *
 * @return array
 */
function lithia_get_project_admin_notice(): array {
	$key    = lithia_get_project_admin_notice_transient_key();
	$notice = get_transient( $key );

	delete_transient( $key );

	return is_array( $notice ) ? $notice : array();
}

/**
 * Save a temporary payload draft for the current user.
 *
 * @param string $json Raw JSON.
 * @return void
 */
function lithia_set_project_admin_pending_payload( string $json ): void {
	set_transient(
		lithia_get_project_admin_pending_payload_transient_key(),
		$json,
		15 * MINUTE_IN_SECONDS
	);
}

/**
 * Return the current pending payload draft, if any.
 *
 * @return string
 */
function lithia_get_project_admin_pending_payload(): string {
	$pending = get_transient( lithia_get_project_admin_pending_payload_transient_key() );

	return is_string( $pending ) ? $pending : '';
}

/**
 * Clear the current pending payload draft.
 *
 * @return void
 */
function lithia_clear_project_admin_pending_payload(): void {
	delete_transient( lithia_get_project_admin_pending_payload_transient_key() );
}

/**
 * Encode a payload for admin editing.
 *
 * @param array $payload Payload.
 * @return string
 */
function lithia_project_admin_encode_payload( array $payload ): string {
	return (string) wp_json_encode( $payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
}

/**
 * Format a scalar or array value as a multi-line textarea string.
 *
 * @param mixed $value Raw value.
 * @return string
 */
function lithia_project_admin_format_multiline_list( $value ): string {
	if ( ! is_array( $value ) ) {
		$value = array( $value );
	}

	$lines = array_values(
		array_filter(
			array_map(
				static function ( $item ): string {
					return sanitize_text_field( lithia_seed_normalize_scalar( $item ) );
				},
				$value
			)
		)
	);

	return implode( "\n", array_unique( $lines ) );
}

/**
 * Parse a multi-line textarea value into a normalized list.
 *
 * @param string $value Raw textarea value.
 * @return array
 */
function lithia_project_admin_parse_multiline_list( string $value ): array {
	$lines = preg_split( '/\r\n|\r|\n/', trim( $value ) );

	if ( ! is_array( $lines ) ) {
		return array();
	}

	$lines = array_values(
		array_filter(
			array_map(
				static function ( string $item ): string {
					return sanitize_text_field( trim( $item ) );
				},
				$lines
			)
		)
	);

	return array_values( array_unique( $lines ) );
}

/**
 * Return offer rows for the structured editor.
 *
 * @param array $payload Canonical payload.
 * @return array
 */
function lithia_project_admin_get_offer_editor_rows( array $payload ): array {
	$rows = array();

	foreach ( (array) ( $payload['offers'] ?? array() ) as $offer ) {
		if ( ! is_array( $offer ) ) {
			continue;
		}

		$rows[] = array(
			'record_key'        => lithia_seed_normalize_record_key( $offer['record_key'] ?? '' ),
			'title'             => sanitize_text_field( lithia_seed_normalize_scalar( $offer['title'] ?? $offer['post_title'] ?? '' ) ),
			'slug'              => sanitize_title( lithia_seed_normalize_scalar( $offer['slug'] ?? $offer['post_name'] ?? '' ) ),
				'summary'           => sanitize_textarea_field(
					lithia_seed_normalize_scalar(
						$offer['excerpt']
						?? $offer['summary']
						?? $offer['service_hero_text']
						?? $offer['service_overview_text']
						?? ''
					)
				),
				'delivery_mode'     => sanitize_text_field( lithia_seed_normalize_scalar( $offer['service_delivery_mode'] ?? $offer['delivery_mode'] ?? '' ) ),
				'timeline'          => sanitize_text_field( lithia_seed_normalize_scalar( $offer['service_timeline'] ?? $offer['duration'] ?? '' ) ),
				'price_from'        => sanitize_text_field( lithia_seed_normalize_scalar( $offer['service_price_from'] ?? $offer['price_from'] ?? '' ) ),
				'price_notes'       => sanitize_textarea_field( lithia_seed_normalize_scalar( $offer['service_price_notes'] ?? $offer['price_notes'] ?? '' ) ),
				'audience'          => lithia_project_admin_format_multiline_list( $offer['service_audience'] ?? $offer['audience'] ?? array() ),
				'outcomes'          => lithia_project_admin_format_multiline_list( $offer['service_outcomes'] ?? $offer['outcomes'] ?? array() ),
				'primary_cta_label' => sanitize_text_field(
					lithia_seed_normalize_scalar(
						$offer['service_primary_cta_label']
						?? $offer['primary_cta_label']
						?? $offer['cta_label']
						?? ''
					)
				),
				'primary_cta_url'   => esc_url_raw(
					(string) (
						$offer['service_primary_cta_url']
						?? $offer['primary_cta_url']
						?? $offer['cta_target']
						?? ''
					)
				),
				'homepage_spotlight_enabled' => 'yes' === lithia_seed_normalize_flag(
					$offer['service_homepage_spotlight_enabled']
					?? $offer['homepage_spotlight_enabled']
					?? 'no'
				),
				'homepage_spotlight_order'   => max(
					0,
					absint(
						lithia_seed_normalize_scalar(
							$offer['service_homepage_spotlight_order']
							?? $offer['homepage_spotlight_order']
							?? 0
						)
					)
				),
				'provider_slugs'    => lithia_project_admin_format_multiline_list( $offer['provider_slugs'] ?? array() ),
			);
		}

	$rows[] = array(
		'record_key'        => '',
		'title'             => '',
		'slug'              => '',
		'summary'           => '',
		'delivery_mode'     => '',
		'timeline'          => '',
		'price_from'        => '',
		'price_notes'       => '',
		'audience'          => '',
		'outcomes'          => '',
		'primary_cta_label' => '',
		'primary_cta_url'   => '',
		'homepage_spotlight_enabled' => false,
		'homepage_spotlight_order'   => 0,
		'provider_slugs'    => '',
	);

	return $rows;
}

/**
 * Return FAQ rows for the structured editor.
 *
 * @param array $payload Canonical payload.
 * @return array
 */
function lithia_project_admin_get_faq_editor_rows( array $payload ): array {
	$rows = array();

	foreach ( (array) ( $payload['faq'] ?? array() ) as $faq_row ) {
		if ( ! is_array( $faq_row ) ) {
			continue;
		}

		$rows[] = array(
			'question' => sanitize_text_field( lithia_seed_normalize_scalar( $faq_row['question'] ?? '' ) ),
			'answer'   => sanitize_textarea_field( lithia_seed_normalize_scalar( $faq_row['answer_seed'] ?? $faq_row['answer'] ?? '' ) ),
		);
	}

	$rows[] = array(
		'question' => '',
		'answer'   => '',
	);

	return $rows;
}

/**
 * Merge the structured Project Manager fields into the canonical payload.
 *
 * @param array $payload Existing payload.
 * @param array $request Raw request data.
 * @return array
 */
function lithia_project_admin_apply_structured_fields( array $payload, array $request ): array {
	$payload = lithia_normalize_project_payload( $payload );
	$request = wp_unslash( $request );

	$payload['project']['site_key'] = lithia_seed_normalize_record_key( $request['project_site_key'] ?? $payload['project']['site_key'] ?? '' );
	$payload['project']['industry'] = sanitize_title( lithia_seed_normalize_scalar( $request['project_industry'] ?? $payload['project']['industry'] ?? '' ) );

	$payload['business']['brand_name']           = sanitize_text_field( lithia_seed_normalize_scalar( $request['business_brand_name'] ?? $payload['business']['brand_name'] ?? '' ) );
	$payload['business']['business_type']        = sanitize_text_field( lithia_seed_normalize_scalar( $request['business_type'] ?? $payload['business']['business_type'] ?? '' ) );
	$payload['business']['short_tagline']        = sanitize_text_field( lithia_seed_normalize_scalar( $request['business_short_tagline'] ?? $payload['business']['short_tagline'] ?? '' ) );
	$payload['business']['email']                = sanitize_email( lithia_seed_normalize_scalar( $request['business_email'] ?? $payload['business']['email'] ?? '' ) );
	$payload['business']['phone']                = sanitize_text_field( lithia_seed_normalize_scalar( $request['business_phone'] ?? $payload['business']['phone'] ?? '' ) );
	$payload['business']['primary_cta_label']    = sanitize_text_field( lithia_seed_normalize_scalar( $request['business_primary_cta_label'] ?? $payload['business']['primary_cta_label'] ?? '' ) );
	$payload['business']['primary_cta_target']   = esc_url_raw( (string) ( $request['business_primary_cta_target'] ?? $payload['business']['primary_cta_target'] ?? '' ) );
	$payload['business']['secondary_cta_label']  = sanitize_text_field( lithia_seed_normalize_scalar( $request['business_secondary_cta_label'] ?? $payload['business']['secondary_cta_label'] ?? '' ) );
	$payload['business']['secondary_cta_target'] = esc_url_raw( (string) ( $request['business_secondary_cta_target'] ?? $payload['business']['secondary_cta_target'] ?? '' ) );

	$payload['location']['city']           = sanitize_text_field( lithia_seed_normalize_scalar( $request['location_city'] ?? $payload['location']['city'] ?? '' ) );
	$payload['location']['state_region']   = sanitize_text_field( lithia_seed_normalize_scalar( $request['location_state_region'] ?? $payload['location']['state_region'] ?? '' ) );
	$payload['location']['service_area']   = lithia_project_admin_parse_multiline_list( (string) ( $request['location_service_area'] ?? '' ) );
	$payload['location']['delivery_modes'] = lithia_project_admin_parse_multiline_list( (string) ( $request['location_delivery_modes'] ?? '' ) );

	$payload['seo']['brand_keyword']   = sanitize_text_field( lithia_seed_normalize_scalar( $request['seo_brand_keyword'] ?? $payload['seo']['brand_keyword'] ?? '' ) );
	$payload['seo']['primary_terms']   = lithia_project_admin_parse_multiline_list( (string) ( $request['seo_primary_terms'] ?? '' ) );
	$payload['seo']['secondary_terms'] = lithia_project_admin_parse_multiline_list( (string) ( $request['seo_secondary_terms'] ?? '' ) );
	$payload['seo']['locations']       = lithia_project_admin_parse_multiline_list( (string) ( $request['seo_locations'] ?? '' ) );
	$payload['seo']['tone']            = sanitize_text_field( lithia_seed_normalize_scalar( $request['seo_tone'] ?? $payload['seo']['tone'] ?? '' ) );

	$payload['booking']['booking_mode']    = sanitize_key( (string) ( $request['booking_mode'] ?? $payload['booking']['booking_mode'] ?? '' ) );
	$payload['booking']['calendar_enabled'] = ! empty( $request['booking_calendar_enabled'] );
	$payload['booking']['booking_notice']  = sanitize_textarea_field( lithia_seed_normalize_scalar( $request['booking_notice'] ?? $payload['booking']['booking_notice'] ?? '' ) );

	$faq_questions = (array) ( $request['faq_question'] ?? array() );
	$faq_answers   = (array) ( $request['faq_answer'] ?? array() );
	$faq_count     = max( count( $faq_questions ), count( $faq_answers ) );
	$faq_rows      = array();

	for ( $index = 0; $index < $faq_count; $index++ ) {
		$question = sanitize_text_field( lithia_seed_normalize_scalar( $faq_questions[ $index ] ?? '' ) );
		$answer   = sanitize_textarea_field( lithia_seed_normalize_scalar( $faq_answers[ $index ] ?? '' ) );

		if ( '' === $question && '' === $answer ) {
			continue;
		}

		$faq_rows[] = array(
			'question'    => $question,
			'answer_seed' => $answer,
		);
	}

	$payload['faq'] = $faq_rows;

	$existing_offers = array();

	foreach ( (array) ( $payload['offers'] ?? array() ) as $offer ) {
		if ( ! is_array( $offer ) ) {
			continue;
		}

		$offer_key = lithia_seed_normalize_record_key( $offer['record_key'] ?? '' );
		$slug_key  = sanitize_title( (string) ( $offer['slug'] ?? '' ) );

		if ( '' !== $offer_key ) {
			$existing_offers[ $offer_key ] = $offer;
		}

		if ( '' !== $slug_key ) {
			$existing_offers[ 'slug:' . $slug_key ] = $offer;
		}
	}

	$offer_record_keys        = (array) ( $request['service_record_key'] ?? array() );
	$offer_titles             = (array) ( $request['service_title'] ?? array() );
	$offer_slugs              = (array) ( $request['service_slug'] ?? array() );
	$offer_summaries          = (array) ( $request['service_summary'] ?? array() );
	$offer_delivery_modes     = (array) ( $request['service_delivery_mode'] ?? array() );
	$offer_timelines          = (array) ( $request['service_timeline'] ?? array() );
	$offer_price_froms        = (array) ( $request['service_price_from'] ?? array() );
	$offer_price_notes        = (array) ( $request['service_price_notes'] ?? array() );
	$offer_audiences          = (array) ( $request['service_audience'] ?? array() );
	$offer_outcomes           = (array) ( $request['service_outcomes'] ?? array() );
	$offer_primary_cta_labels = (array) ( $request['service_primary_cta_label'] ?? array() );
	$offer_primary_cta_urls   = (array) ( $request['service_primary_cta_url'] ?? array() );
	$offer_homepage_spotlights = (array) ( $request['service_homepage_spotlight'] ?? array() );
	$offer_homepage_spotlight_orders = (array) ( $request['service_homepage_spotlight_order'] ?? array() );
	$offer_provider_slugs     = (array) ( $request['service_provider_slugs'] ?? array() );
	$offer_count              = max(
		count( $offer_record_keys ),
		count( $offer_titles ),
		count( $offer_slugs ),
		count( $offer_summaries ),
		count( $offer_delivery_modes ),
		count( $offer_timelines ),
		count( $offer_price_froms ),
		count( $offer_price_notes ),
		count( $offer_audiences ),
		count( $offer_outcomes ),
		count( $offer_primary_cta_labels ),
		count( $offer_primary_cta_urls ),
		count( $offer_homepage_spotlight_orders ),
		count( $offer_provider_slugs )
	);
	$offers                   = array();

	for ( $index = 0; $index < $offer_count; $index++ ) {
		$record_key        = lithia_seed_normalize_record_key( $offer_record_keys[ $index ] ?? '' );
		$title             = sanitize_text_field( lithia_seed_normalize_scalar( $offer_titles[ $index ] ?? '' ) );
		$slug              = sanitize_title( lithia_seed_normalize_scalar( $offer_slugs[ $index ] ?? '' ) );
		$summary           = sanitize_textarea_field( lithia_seed_normalize_scalar( $offer_summaries[ $index ] ?? '' ) );
		$delivery_mode     = sanitize_text_field( lithia_seed_normalize_scalar( $offer_delivery_modes[ $index ] ?? '' ) );
		$timeline          = sanitize_text_field( lithia_seed_normalize_scalar( $offer_timelines[ $index ] ?? '' ) );
		$price_from        = sanitize_text_field( lithia_seed_normalize_scalar( $offer_price_froms[ $index ] ?? '' ) );
		$price_note        = sanitize_textarea_field( lithia_seed_normalize_scalar( $offer_price_notes[ $index ] ?? '' ) );
		$audience          = lithia_project_admin_parse_multiline_list( (string) ( $offer_audiences[ $index ] ?? '' ) );
		$outcomes          = lithia_project_admin_parse_multiline_list( (string) ( $offer_outcomes[ $index ] ?? '' ) );
		$primary_cta_label = sanitize_text_field( lithia_seed_normalize_scalar( $offer_primary_cta_labels[ $index ] ?? '' ) );
		$primary_cta_url   = esc_url_raw( (string) ( $offer_primary_cta_urls[ $index ] ?? '' ) );
		$homepage_spotlight_enabled = ! empty( $offer_homepage_spotlights[ $index ] );
		$homepage_spotlight_order   = max( 0, absint( $offer_homepage_spotlight_orders[ $index ] ?? 0 ) );
		$provider_slugs    = lithia_project_import_normalize_slug_list( $offer_provider_slugs[ $index ] ?? '' );

		if ( '' === $record_key && '' === $slug && '' === $title && '' === $summary && '' === $delivery_mode && '' === $timeline && '' === $price_from && '' === $price_note && empty( $audience ) && empty( $outcomes ) ) {
			continue;
		}

		$base_offer = array();

		if ( '' !== $record_key && isset( $existing_offers[ $record_key ] ) ) {
			$base_offer = (array) $existing_offers[ $record_key ];
		} elseif ( '' !== $slug && isset( $existing_offers[ 'slug:' . $slug ] ) ) {
			$base_offer = (array) $existing_offers[ 'slug:' . $slug ];
		}

		if ( '' === $slug && '' !== $title ) {
			$slug = sanitize_title( $title );
		}

		if ( '' === $record_key && '' !== $slug ) {
			$record_key = 'service_' . sanitize_key( $slug );
		}

		$base_offer['record_key']               = $record_key;
		$base_offer['title']                    = $title;
		$base_offer['slug']                     = $slug;
		$base_offer['excerpt']                  = $summary;
		$base_offer['service_hero_text']        = $summary;
		$base_offer['service_overview_text']    = $summary;
		$base_offer['service_delivery_mode']    = $delivery_mode;
		$base_offer['service_timeline']         = $timeline;
		$base_offer['service_price_from']       = $price_from;
		$base_offer['service_price_notes']      = $price_note;
		$base_offer['service_audience']         = $audience;
		$base_offer['service_outcomes']         = $outcomes;
		$base_offer['service_primary_cta_label'] = $primary_cta_label;
		$base_offer['service_primary_cta_url']  = $primary_cta_url;
		$base_offer['service_homepage_spotlight_enabled'] = $homepage_spotlight_enabled ? 'yes' : 'no';
		$base_offer['service_homepage_spotlight_order']   = $homepage_spotlight_order;
		$base_offer['provider_slugs']           = $provider_slugs;

		$offers[] = $base_offer;
	}

	$payload['offers'] = $offers;

	return lithia_normalize_project_payload( $payload );
}

/**
 * Decode and validate payload JSON.
 *
 * @param string $json Raw JSON.
 * @return array|WP_Error
 */
function lithia_project_admin_decode_payload_json( string $json ) {
	$json = trim( $json );

	if ( '' === $json ) {
		return new WP_Error( 'empty_payload', __( 'Paste a canonical project payload before saving or importing.', 'lithia-web-service-theme' ) );
	}

	$decoded = json_decode( $json, true );

	if ( JSON_ERROR_NONE !== json_last_error() || ! is_array( $decoded ) ) {
		return new WP_Error(
			'invalid_payload',
			sprintf(
				/* translators: %s JSON error message. */
				__( 'Payload JSON could not be parsed: %s', 'lithia-web-service-theme' ),
				json_last_error_msg()
			)
		);
	}

	return $decoded;
}

/**
 * Build the default admin payload draft.
 *
 * @return array
 */
function lithia_project_admin_build_default_payload(): array {
	if ( function_exists( 'lithia_build_project_payload_from_launch_wizard_state' ) ) {
		return lithia_build_project_payload_from_launch_wizard_state( lithia_get_launch_wizard_state() );
	}

	return array(
		'project' => array(
			'schema_version' => lithia_get_project_import_schema_version(),
			'template_key'   => lithia_get_project_import_default_template_key(),
			'review_state'   => 'approved',
		),
	);
}

/**
 * Return the stored payload draft JSON, with a launch-wizard fallback.
 *
 * @return string
 */
function lithia_get_project_payload_draft_json(): string {
	$pending = lithia_get_project_admin_pending_payload();

	if ( '' !== $pending ) {
		return $pending;
	}

	$stored = get_option( lithia_get_project_payload_draft_option_name(), '' );

	if ( is_string( $stored ) && '' !== trim( $stored ) ) {
		return $stored;
	}

	return lithia_project_admin_encode_payload( lithia_project_admin_build_default_payload() );
}

/**
 * Save a normalized payload draft JSON string.
 *
 * @param array $payload Payload.
 * @return string
 */
function lithia_update_project_payload_draft_json( array $payload ): string {
	$json = lithia_project_admin_encode_payload( lithia_normalize_project_payload( $payload ) );
	update_option( lithia_get_project_payload_draft_option_name(), $json, false );
	lithia_clear_project_admin_pending_payload();

	return $json;
}

/**
 * Persist the latest project admin report.
 *
 * @param array $report Report payload.
 * @return void
 */
function lithia_update_project_admin_last_report( array $report ): void {
	update_option( lithia_get_project_admin_last_report_option_name(), $report, false );
}

/**
 * Return the saved project admin report.
 *
 * @return array
 */
function lithia_get_project_admin_last_report(): array {
	$report = get_option( lithia_get_project_admin_last_report_option_name(), array() );

	return is_array( $report ) ? $report : array();
}

/**
 * Return the saved import history.
 *
 * @return array
 */
function lithia_get_project_import_history(): array {
	$history = get_option( lithia_get_project_import_history_option_name(), array() );

	return is_array( $history ) ? $history : array();
}

/**
 * Append one import history entry.
 *
 * @param array $entry History entry.
 * @return array
 */
function lithia_project_admin_append_history_entry( array $entry ): array {
	$history = lithia_get_project_import_history();
	$user    = wp_get_current_user();

	$entry = wp_parse_args(
		$entry,
		array(
			'id'                  => wp_generate_uuid4(),
			'recorded_at'         => current_time( 'mysql' ),
			'intent'              => 'apply_import',
			'success'             => false,
			'dry_run'             => false,
			'user_id'             => get_current_user_id(),
			'user_login'          => $user instanceof WP_User ? (string) $user->user_login : '',
			'project'             => array(),
			'payload_hash'        => '',
			'payload'             => array(),
			'summary'             => array(),
			'relationships_synced'=> 0,
			'context_before'      => array(),
			'context_after'       => array(),
			'validation'          => array(),
		)
	);

	array_unshift( $history, $entry );
	$history = array_slice( $history, 0, lithia_get_project_import_history_limit() );

	update_option( lithia_get_project_import_history_option_name(), $history, false );

	return $history;
}

/**
 * Return one import history entry by ID.
 *
 * @param string $entry_id Entry ID.
 * @return array
 */
function lithia_get_project_import_history_entry( string $entry_id ): array {
	$entry_id = sanitize_text_field( $entry_id );

	foreach ( lithia_get_project_import_history() as $entry ) {
		if ( ! is_array( $entry ) ) {
			continue;
		}

		if ( $entry_id === (string) ( $entry['id'] ?? '' ) ) {
			return $entry;
		}
	}

	return array();
}

/**
 * Return a download filename for one payload snapshot.
 *
 * @param array  $payload Payload.
 * @param string $suffix  Filename suffix.
 * @return string
 */
function lithia_project_admin_get_download_filename( array $payload, string $suffix = 'payload' ): string {
	$payload  = lithia_normalize_project_payload( $payload );
	$site_key = sanitize_title( (string) ( $payload['project']['site_key'] ?? '' ) );
	$site_key = $site_key ? $site_key : 'lithia-project';
	$suffix   = sanitize_title( $suffix ? $suffix : 'payload' );

	return sanitize_file_name(
		sprintf(
			'%1$s-%2$s-%3$s.json',
			$site_key,
			$suffix,
			gmdate( 'Ymd-His' )
		)
	);
}

/**
 * Send one payload JSON download and exit.
 *
 * @param array  $payload Payload.
 * @param string $suffix  Filename suffix.
 * @return void
 */
function lithia_project_admin_send_payload_download( array $payload, string $suffix = 'payload' ): void {
	$payload = lithia_normalize_project_payload( $payload );
	$json    = lithia_project_admin_encode_payload( $payload );

	if ( '' === $json ) {
		wp_die( esc_html__( 'Unable to export payload JSON.', 'lithia-web-service-theme' ) );
	}

	nocache_headers();
	header( 'Content-Type: application/json; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename="' . lithia_project_admin_get_download_filename( $payload, $suffix ) . '"' );

	echo $json; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	exit;
}

/**
 * Return whether the current request targets the project admin page.
 *
 * @return bool
 */
function lithia_is_project_admin_page_request(): bool {
	return is_admin()
		&& ! empty( $_GET['page'] )
		&& lithia_get_project_admin_page_slug() === sanitize_key( (string) $_GET['page'] );
}

/**
 * Return a list of importer-managed entities.
 *
 * @return array
 */
function lithia_get_project_admin_entities(): array {
	$entities = array(
		'pages'     => array(),
		'services'  => array(),
		'providers' => array(),
	);

	$page_ids = array();

	foreach ( array_keys( lithia_get_project_page_role_defaults() ) as $page_role ) {
		$page_id = lithia_project_import_get_page_id_by_role( $page_role );

		if ( $page_id > 0 ) {
			$page_ids[] = $page_id;
		}
	}

	$page_ids = array_merge(
		$page_ids,
		get_posts(
			array(
				'post_type'      => 'page',
				'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'meta_query'     => array(
					'relation' => 'OR',
					array(
						'key'     => '_lithia_generated_source',
						'value'   => 'project_importer',
						'compare' => '=',
					),
					array(
						'key'     => '_lithia_sync_lock',
						'value'   => 'yes',
						'compare' => '=',
					),
					array(
						'key'     => lithia_get_record_key_meta_key(),
						'compare' => 'EXISTS',
					),
				),
			)
		)
	);

	$page_ids = array_values( array_unique( array_map( 'absint', $page_ids ) ) );

	foreach ( $page_ids as $page_id ) {
		$post = get_post( $page_id );

		if ( ! $post instanceof WP_Post ) {
			continue;
		}

		$entities['pages'][] = array(
			'post_id'    => (int) $post->ID,
			'title'      => $post->post_title,
			'status'     => $post->post_status,
			'subtype'    => (string) get_post_meta( $post->ID, '_lithia_page_role', true ),
			'record_key' => lithia_get_post_record_key( (int) $post->ID ),
			'locked'     => lithia_project_import_is_post_locked( (int) $post->ID ),
			'edit_url'   => get_edit_post_link( $post->ID, '' ),
			'view_url'   => get_permalink( $post->ID ),
		);
	}

	foreach ( array( 'services', 'providers' ) as $post_type ) {
		$post_ids = get_posts(
			array(
				'post_type'      => $post_type,
				'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'meta_query'     => array(
					'relation' => 'OR',
					array(
						'key'     => '_lithia_generated_source',
						'value'   => 'project_importer',
						'compare' => '=',
					),
					array(
						'key'     => '_lithia_sync_lock',
						'value'   => 'yes',
						'compare' => '=',
					),
					array(
						'key'     => lithia_get_record_key_meta_key(),
						'compare' => 'EXISTS',
					),
				),
			)
		);

		foreach ( array_map( 'absint', $post_ids ) as $post_id ) {
			$post = get_post( $post_id );

			if ( ! $post instanceof WP_Post ) {
				continue;
			}

			$entities[ $post_type ][] = array(
				'post_id'    => (int) $post->ID,
				'title'      => $post->post_title,
				'status'     => $post->post_status,
				'subtype'    => $post_type,
				'record_key' => lithia_get_post_record_key( (int) $post->ID ),
				'locked'     => lithia_project_import_is_post_locked( (int) $post->ID ),
				'edit_url'   => get_edit_post_link( $post->ID, '' ),
				'view_url'   => get_permalink( $post->ID ),
			);
		}
	}

	foreach ( $entities as $entity_type => $rows ) {
		usort(
			$rows,
			static function ( array $left, array $right ): int {
				return strcasecmp( $left['title'] ?? '', $right['title'] ?? '' );
			}
		);

		$entities[ $entity_type ] = $rows;
	}

	return $entities;
}

/**
 * Register the Project Manager page under Appearance.
 *
 * @return void
 */
function lithia_register_project_admin_page(): void {
	add_theme_page(
		__( 'Project Manager', 'lithia-web-service-theme' ),
		__( 'Project Manager', 'lithia-web-service-theme' ),
		'edit_theme_options',
		lithia_get_project_admin_page_slug(),
		'lithia_render_project_admin_page'
	);
}
add_action( 'admin_menu', 'lithia_register_project_admin_page' );

/**
 * Enqueue Project Manager assets.
 *
 * @param string $hook_suffix Current admin page hook.
 * @return void
 */
function lithia_enqueue_project_admin_assets( string $hook_suffix ): void {
	if ( 'appearance_page_' . lithia_get_project_admin_page_slug() !== $hook_suffix ) {
		return;
	}

	wp_enqueue_style(
		'lithia-project-admin',
		get_theme_file_uri( 'assets/css/admin-project-manager.css' ),
		array(),
		lithia_get_theme_asset_version( 'assets/css/admin-project-manager.css' )
	);
}
add_action( 'admin_enqueue_scripts', 'lithia_enqueue_project_admin_assets' );

/**
 * Handle Project Manager form submissions.
 *
 * @return void
 */
function lithia_handle_project_admin_post(): void {
	if ( empty( $_POST['action'] ) || 'lithia_project_admin_action' !== $_POST['action'] ) {
		return;
	}

	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to manage project imports.', 'lithia-web-service-theme' ) );
	}

	check_admin_referer( 'lithia_project_admin' );

	$intent       = sanitize_key( (string) ( $_POST['intent'] ?? 'save_payload' ) );
	$redirect_url = add_query_arg(
		array(
			'page' => lithia_get_project_admin_page_slug(),
		),
		admin_url( 'themes.php' )
	);

	if ( 'build_from_launch_wizard' === $intent ) {
		$payload = lithia_project_admin_build_default_payload();
		lithia_update_project_payload_draft_json( $payload );
		lithia_set_project_admin_notice( __( 'Payload draft rebuilt from the Launch Wizard state.', 'lithia-web-service-theme' ) );

		wp_safe_redirect( $redirect_url );
		exit;
	}

	if ( 'load_sample_payload' === $intent ) {
		$sample_key = sanitize_key( (string) ( $_POST['sample_key'] ?? 'service-business-v1-starter' ) );
		$samples    = lithia_get_project_admin_sample_payload_definitions();
		$sample     = $samples[ $sample_key ] ?? array();

		if ( empty( $sample['path'] ) ) {
			lithia_set_project_admin_notice( __( 'Unknown sample payload.', 'lithia-web-service-theme' ), 'error' );

			wp_safe_redirect( $redirect_url );
			exit;
		}

		$payload = lithia_seed_load_payload_from_json_file( (string) $sample['path'] );

		if ( is_wp_error( $payload ) ) {
			lithia_set_project_admin_notice( $payload->get_error_message(), 'error' );

			wp_safe_redirect( $redirect_url );
			exit;
		}

		lithia_update_project_payload_draft_json( $payload );
		lithia_set_project_admin_notice( __( 'Sample payload loaded into the draft editor.', 'lithia-web-service-theme' ) );

		wp_safe_redirect( $redirect_url );
		exit;
	}

	if ( 'update_review_state' === $intent ) {
		$state = lithia_set_project_review_state( (string) ( $_POST['review_state'] ?? 'intake' ) );
		lithia_set_project_admin_notice(
			sprintf(
				/* translators: %s review state. */
				__( 'Site review state updated to %s.', 'lithia-web-service-theme' ),
				$state
			)
		);

		wp_safe_redirect( $redirect_url );
		exit;
	}

	if ( 'update_payload_review_state' === $intent ) {
		$payload = lithia_project_admin_decode_payload_json( lithia_get_project_payload_draft_json() );

		if ( is_wp_error( $payload ) ) {
			lithia_set_project_admin_notice( $payload->get_error_message(), 'error' );

			wp_safe_redirect( $redirect_url );
			exit;
		}

		$payload                           = lithia_normalize_project_payload( $payload );
		$payload['project']['review_state'] = lithia_normalize_project_review_state( (string) ( $_POST['payload_review_state'] ?? 'intake' ) );
		lithia_update_project_payload_draft_json( $payload );
		lithia_set_project_admin_notice(
			sprintf(
				/* translators: %s review state. */
				__( 'Payload review state updated to %s.', 'lithia-web-service-theme' ),
				$payload['project']['review_state']
			)
		);

		wp_safe_redirect( $redirect_url );
		exit;
	}

	if ( 'save_locks' === $intent ) {
		$post_ids = array_map( 'absint', (array) ( $_POST['managed_post_ids'] ?? array() ) );
		$locks    = array_map( 'absint', array_keys( (array) ( $_POST['locks'] ?? array() ) ) );

		foreach ( array_unique( array_filter( $post_ids ) ) as $post_id ) {
			if ( in_array( $post_id, $locks, true ) ) {
				update_post_meta( $post_id, '_lithia_sync_lock', 'yes' );
			} else {
				delete_post_meta( $post_id, '_lithia_sync_lock' );
			}
		}

		lithia_set_project_admin_notice( __( 'Sync locks updated.', 'lithia-web-service-theme' ) );

		wp_safe_redirect( $redirect_url );
		exit;
	}

	if ( in_array( $intent, array( 'restore_history_payload', 'download_history_payload' ), true ) ) {
		$history_id = sanitize_text_field( (string) ( $_POST['history_id'] ?? '' ) );
		$entry      = lithia_get_project_import_history_entry( $history_id );

		if ( empty( $entry ) || empty( $entry['payload'] ) || ! is_array( $entry['payload'] ) ) {
			lithia_set_project_admin_notice( __( 'History snapshot not found.', 'lithia-web-service-theme' ), 'error' );

			wp_safe_redirect( $redirect_url );
			exit;
		}

		if ( 'restore_history_payload' === $intent ) {
			lithia_update_project_payload_draft_json( $entry['payload'] );
			lithia_set_project_admin_notice( __( 'History snapshot restored to the payload draft.', 'lithia-web-service-theme' ) );

			wp_safe_redirect( $redirect_url );
			exit;
		}

		lithia_project_admin_send_payload_download( $entry['payload'], 'snapshot-' . sanitize_title( $history_id ) );
	}

	$payload_json = wp_unslash( (string) ( $_POST['payload_json'] ?? '' ) );
	$decoded      = lithia_project_admin_decode_payload_json( $payload_json );

	if ( is_wp_error( $decoded ) ) {
		lithia_set_project_admin_pending_payload( $payload_json );
		lithia_set_project_admin_notice( $decoded->get_error_message(), 'error' );

		wp_safe_redirect( $redirect_url );
		exit;
	}

	$payload = lithia_normalize_project_payload( $decoded );

	if ( isset( $_POST['payload_review_state'] ) ) {
		$payload['project']['review_state'] = lithia_normalize_project_review_state( (string) $_POST['payload_review_state'] );
	}

	if ( ! empty( $_POST['structured_form'] ) ) {
		$payload = lithia_project_admin_apply_structured_fields( $payload, $_POST );
	}

	$validation = lithia_validate_project_payload( $payload );
	$payload    = $validation['payload'];

	lithia_update_project_payload_draft_json( $payload );

	if ( 'download_payload' === $intent ) {
		lithia_project_admin_send_payload_download( $payload, 'draft' );
	}

	if ( 'save_payload' === $intent ) {
		if ( ! empty( $validation['errors'] ) ) {
			lithia_set_project_admin_notice(
				sprintf(
					/* translators: %d error count. */
					__( 'Payload draft saved, but %d validation errors remain. Fix them before import.', 'lithia-web-service-theme' ),
					count( $validation['errors'] )
				),
				'warning'
			);
		} elseif ( ! empty( $validation['warnings'] ) ) {
			lithia_set_project_admin_notice(
				sprintf(
					/* translators: %d warning count. */
					__( 'Payload draft saved with %d warnings.', 'lithia-web-service-theme' ),
					count( $validation['warnings'] )
				),
				'warning'
			);
		} else {
			lithia_set_project_admin_notice( __( 'Payload draft saved.', 'lithia-web-service-theme' ) );
		}

		wp_safe_redirect( $redirect_url );
		exit;
	}

	if ( ! in_array( $intent, array( 'dry_run_import', 'apply_import' ), true ) ) {
		lithia_set_project_admin_notice( __( 'Unknown project admin action.', 'lithia-web-service-theme' ), 'error' );

		wp_safe_redirect( $redirect_url );
		exit;
	}

	if ( ! empty( $validation['errors'] ) ) {
		lithia_update_project_admin_last_report(
			array(
				'intent'      => $intent,
				'recorded_at' => current_time( 'mysql' ),
				'report'      => array(
					'success'    => false,
					'validation' => $validation,
				),
			)
		);

		$error_messages = array_slice( wp_list_pluck( $validation['errors'], 'message' ), 0, 3 );
		lithia_set_project_admin_notice(
			sprintf(
				/* translators: 1: error count, 2: condensed message list. */
				__( 'Import blocked by %1$d validation errors. %2$s', 'lithia-web-service-theme' ),
				count( $validation['errors'] ),
				implode( ' ', $error_messages )
			),
			'error'
		);

		wp_safe_redirect( $redirect_url );
		exit;
	}

	$context_before = lithia_get_project_context();
	$report = lithia_import_project_payload(
		$payload,
		array(
			'dry_run' => 'dry_run_import' === $intent,
			'force'   => ! empty( $_POST['force_import'] ),
			'source'  => 'project_admin',
			)
		);

	lithia_update_project_admin_last_report(
		array(
			'intent'      => $intent,
			'recorded_at' => current_time( 'mysql' ),
			'report'      => $report,
		)
	);

	lithia_project_admin_append_history_entry(
		array(
			'intent'               => $intent,
			'success'              => ! empty( $report['success'] ),
			'dry_run'              => 'dry_run_import' === $intent,
			'project'              => $payload['project'],
			'payload_hash'         => (string) ( $report['context']['payload_hash'] ?? md5( wp_json_encode( $payload ) ) ),
			'payload'              => $payload,
			'summary'              => (array) ( $report['summary'] ?? array() ),
			'relationships_synced' => (int) ( $report['relationships_synced'] ?? 0 ),
			'context_before'       => $context_before,
			'context_after'        => (array) ( $report['context'] ?? array() ),
			'validation'           => $validation,
		)
	);

	if ( empty( $report['success'] ) ) {
		lithia_set_project_admin_notice( $report['error'] ?? __( 'Project import failed.', 'lithia-web-service-theme' ), 'error' );
	} else {
		lithia_set_project_admin_notice(
			'dry_run_import' === $intent
				? __( 'Dry run completed. Review the summary below before importing.', 'lithia-web-service-theme' )
				: __( 'Project payload imported successfully.', 'lithia-web-service-theme' )
		);
	}

	wp_safe_redirect( $redirect_url );
	exit;
}
add_action( 'admin_post_lithia_project_admin_action', 'lithia_handle_project_admin_post' );

/**
 * Render one project admin stat card.
 *
 * @param string $label Stat label.
 * @param string $value Stat value.
 * @return void
 */
function lithia_render_project_admin_stat( string $label, string $value ): void {
	?>
	<div class="lw-project-stat">
		<span class="lw-project-stat__label"><?php echo esc_html( $label ); ?></span>
		<strong class="lw-project-stat__value"><?php echo esc_html( $value ); ?></strong>
	</div>
	<?php
}

/**
 * Return a compact summary string for one import summary payload.
 *
 * @param array $summary Summary rows.
 * @return string
 */
function lithia_project_admin_get_summary_label( array $summary ): string {
	$parts = array();

	foreach ( array( 'pages', 'offers', 'providers' ) as $entity_key ) {
		$entity_summary = is_array( $summary[ $entity_key ] ?? null ) ? $summary[ $entity_key ] : array();

		if ( empty( $entity_summary ) ) {
			continue;
		}

		$parts[] = sprintf(
			'%1$s c%2$d u%3$d n%4$d l%5$d f%6$d',
			$entity_key,
			(int) ( $entity_summary['created'] ?? 0 ),
			(int) ( $entity_summary['updated'] ?? 0 ),
			(int) ( $entity_summary['noop'] ?? 0 ),
			(int) ( $entity_summary['locked'] ?? 0 ),
			(int) ( $entity_summary['failed'] ?? 0 )
		);
	}

	return empty( $parts ) ? 'n/a' : implode( ' | ', $parts );
}

/**
 * Render a list of payload validation issues.
 *
 * @param string $title  Section title.
 * @param array  $issues Issue rows.
 * @param string $empty  Empty-state text.
 * @return void
 */
function lithia_render_project_admin_issue_list( string $title, array $issues, string $empty ): void {
	?>
	<div class="lw-project-panel">
		<header class="lw-project-panel__header">
			<h3><?php echo esc_html( $title ); ?></h3>
			<p><?php echo esc_html( empty( $issues ) ? $empty : sprintf( '%d issues', count( $issues ) ) ); ?></p>
		</header>

		<?php if ( empty( $issues ) ) : ?>
			<p class="description"><?php echo esc_html( $empty ); ?></p>
		<?php else : ?>
			<ul class="lw-project-issue-list">
				<?php foreach ( $issues as $issue ) : ?>
					<li>
						<code><?php echo esc_html( (string) ( $issue['code'] ?? 'issue' ) ); ?></code>
						<span><?php echo esc_html( (string) ( $issue['message'] ?? '' ) ); ?></span>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Render import history rows.
 *
 * @param array $history Import history rows.
 * @return void
 */
function lithia_render_project_admin_history_table( array $history ): void {
	?>
	<section class="lw-project-panel">
		<header class="lw-project-panel__header">
			<h2><?php esc_html_e( 'Import History', 'lithia-web-service-theme' ); ?></h2>
			<p><?php esc_html_e( 'Restore or download older drafts.', 'lithia-web-service-theme' ); ?></p>
		</header>

		<?php if ( empty( $history ) ) : ?>
			<p class="description"><?php esc_html_e( 'No import history saved yet.', 'lithia-web-service-theme' ); ?></p>
		<?php else : ?>
			<div class="lw-project-table-wrap">
				<table class="widefat striped">
					<thead>
						<tr>
							<th><?php esc_html_e( 'When', 'lithia-web-service-theme' ); ?></th>
							<th><?php esc_html_e( 'Action', 'lithia-web-service-theme' ); ?></th>
							<th><?php esc_html_e( 'User', 'lithia-web-service-theme' ); ?></th>
							<th><?php esc_html_e( 'Payload State', 'lithia-web-service-theme' ); ?></th>
							<th><?php esc_html_e( 'Summary', 'lithia-web-service-theme' ); ?></th>
							<th><?php esc_html_e( 'Actions', 'lithia-web-service-theme' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $history as $entry ) : ?>
							<?php
							if ( ! is_array( $entry ) ) {
								continue;
							}
							?>
							<tr>
								<td><?php echo esc_html( (string) ( $entry['recorded_at'] ?? 'n/a' ) ); ?></td>
								<td><?php echo esc_html( (string) ( $entry['intent'] ?? 'n/a' ) ); ?></td>
								<td><?php echo esc_html( (string) ( $entry['user_login'] ?? 'n/a' ) ); ?></td>
								<td><?php echo esc_html( (string) ( $entry['project']['review_state'] ?? 'n/a' ) ); ?></td>
								<td><?php echo esc_html( lithia_project_admin_get_summary_label( (array) ( $entry['summary'] ?? array() ) ) ); ?></td>
								<td>
									<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="lw-project-inline-form">
										<?php wp_nonce_field( 'lithia_project_admin' ); ?>
										<input type="hidden" name="action" value="lithia_project_admin_action" />
										<input type="hidden" name="history_id" value="<?php echo esc_attr( (string) ( $entry['id'] ?? '' ) ); ?>" />
										<button type="submit" class="button button-secondary" name="intent" value="restore_history_payload"><?php esc_html_e( 'Restore Draft', 'lithia-web-service-theme' ); ?></button>
										<button type="submit" class="button button-secondary" name="intent" value="download_history_payload"><?php esc_html_e( 'Download JSON', 'lithia-web-service-theme' ); ?></button>
									</form>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		<?php endif; ?>
	</section>
	<?php
}

/**
 * Render one managed-entity table.
 *
 * @param string $title Section title.
 * @param array  $rows  Entity rows.
 * @return void
 */
function lithia_render_project_admin_entity_table( string $title, array $rows ): void {
	?>
	<section class="lw-project-panel">
		<header class="lw-project-panel__header">
			<h3><?php echo esc_html( $title ); ?></h3>
			<p><?php echo esc_html( sprintf( '%d tracked items', count( $rows ) ) ); ?></p>
		</header>

		<?php if ( empty( $rows ) ) : ?>
			<p class="description"><?php esc_html_e( 'No importer-managed items found yet for this section.', 'lithia-web-service-theme' ); ?></p>
		<?php else : ?>
			<div class="lw-project-table-wrap">
				<table class="widefat striped">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Lock', 'lithia-web-service-theme' ); ?></th>
							<th><?php esc_html_e( 'Title', 'lithia-web-service-theme' ); ?></th>
							<th><?php esc_html_e( 'Subtype', 'lithia-web-service-theme' ); ?></th>
							<th><?php esc_html_e( 'Status', 'lithia-web-service-theme' ); ?></th>
							<th><?php esc_html_e( 'Record Key', 'lithia-web-service-theme' ); ?></th>
							<th><?php esc_html_e( 'Links', 'lithia-web-service-theme' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $rows as $row ) : ?>
							<tr>
								<td>
									<input type="hidden" name="managed_post_ids[]" value="<?php echo esc_attr( (string) $row['post_id'] ); ?>" />
									<label>
										<input type="checkbox" name="locks[<?php echo esc_attr( (string) $row['post_id'] ); ?>]" value="1" <?php checked( ! empty( $row['locked'] ) ); ?> />
										<span class="screen-reader-text"><?php esc_html_e( 'Lock importer writes for this item', 'lithia-web-service-theme' ); ?></span>
									</label>
								</td>
								<td><?php echo esc_html( $row['title'] ); ?></td>
								<td><?php echo esc_html( $row['subtype'] ? $row['subtype'] : 'n/a' ); ?></td>
								<td><?php echo esc_html( $row['status'] ); ?></td>
								<td><code><?php echo esc_html( $row['record_key'] ? $row['record_key'] : 'n/a' ); ?></code></td>
								<td class="lw-project-links">
									<?php if ( ! empty( $row['edit_url'] ) ) : ?>
										<a href="<?php echo esc_url( $row['edit_url'] ); ?>"><?php esc_html_e( 'Edit', 'lithia-web-service-theme' ); ?></a>
									<?php endif; ?>
									<?php if ( ! empty( $row['view_url'] ) ) : ?>
										<a href="<?php echo esc_url( $row['view_url'] ); ?>" target="_blank" rel="noreferrer"><?php esc_html_e( 'View', 'lithia-web-service-theme' ); ?></a>
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		<?php endif; ?>
	</section>
	<?php
}

/**
 * Render the Project Manager page.
 *
 * @return void
 */
function lithia_render_project_admin_page(): void {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}

	$notice       = lithia_get_project_admin_notice();
	$context      = lithia_get_project_context();
	$payload_json = lithia_get_project_payload_draft_json();
	$payload      = lithia_project_admin_decode_payload_json( $payload_json );
	$payload      = is_wp_error( $payload ) ? array() : lithia_normalize_project_payload( $payload );
	$validation   = empty( $payload ) ? array(
		'errors'   => array(),
		'warnings' => array(),
	) : lithia_validate_project_payload( $payload );
	$report_state = lithia_get_project_admin_last_report();
	$report       = is_array( $report_state['report'] ?? null ) ? $report_state['report'] : array();
	$history      = lithia_get_project_import_history();
	$entities     = lithia_get_project_admin_entities();
	$samples      = lithia_get_project_admin_sample_payload_definitions();
	$offer_rows   = lithia_project_admin_get_offer_editor_rows( $payload );
	$faq_rows     = lithia_project_admin_get_faq_editor_rows( $payload );
	?>
	<div class="wrap lw-project-admin-page">
		<h1><?php esc_html_e( 'Project Manager', 'lithia-web-service-theme' ); ?></h1>
		<p class="description"><?php esc_html_e( 'Edit, validate, dry run, import.', 'lithia-web-service-theme' ); ?></p>

		<?php if ( ! empty( $notice['message'] ) ) : ?>
			<div class="notice notice-<?php echo esc_attr( $notice['type'] ); ?> is-dismissible"><p><?php echo esc_html( $notice['message'] ); ?></p></div>
		<?php endif; ?>

		<div class="lw-project-grid lw-project-grid--stats">
			<?php lithia_render_project_admin_stat( __( 'Site Review State', 'lithia-web-service-theme' ), (string) $context['review_state'] ); ?>
			<?php lithia_render_project_admin_stat( __( 'Payload Review State', 'lithia-web-service-theme' ), (string) ( $payload['project']['review_state'] ?? 'n/a' ) ); ?>
			<?php lithia_render_project_admin_stat( __( 'Template', 'lithia-web-service-theme' ), (string) ( $payload['project']['template_key'] ?? $context['template_key'] ) ); ?>
			<?php lithia_render_project_admin_stat( __( 'Industry', 'lithia-web-service-theme' ), (string) ( $payload['project']['industry'] ?? $context['industry'] ?: 'n/a' ) ); ?>
			<?php lithia_render_project_admin_stat( __( 'Site Key', 'lithia-web-service-theme' ), (string) ( $payload['project']['site_key'] ?? $context['site_key'] ?: 'n/a' ) ); ?>
			<?php lithia_render_project_admin_stat( __( 'Last Import', 'lithia-web-service-theme' ), (string) ( $context['last_imported_at'] ?: 'never' ) ); ?>
		</div>

		<div class="lw-project-grid lw-project-grid--two">
			<section class="lw-project-panel">
				<header class="lw-project-panel__header">
					<h2><?php esc_html_e( 'Workflow Status', 'lithia-web-service-theme' ); ?></h2>
					<p><?php esc_html_e( 'Site state and payload state.', 'lithia-web-service-theme' ); ?></p>
				</header>

				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="lw-project-inline-form">
					<?php wp_nonce_field( 'lithia_project_admin' ); ?>
					<input type="hidden" name="action" value="lithia_project_admin_action" />
					<input type="hidden" name="intent" value="update_review_state" />
					<label class="lw-project-field">
						<span class="lw-project-field__label"><?php esc_html_e( 'Site Review State', 'lithia-web-service-theme' ); ?></span>
						<select name="review_state">
							<?php foreach ( lithia_get_project_review_states() as $state ) : ?>
								<option value="<?php echo esc_attr( $state ); ?>" <?php selected( $context['review_state'], $state ); ?>><?php echo esc_html( $state ); ?></option>
							<?php endforeach; ?>
						</select>
					</label>
					<button type="submit" class="button button-secondary"><?php esc_html_e( 'Update State', 'lithia-web-service-theme' ); ?></button>
					</form>

					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="lw-project-inline-form">
						<?php wp_nonce_field( 'lithia_project_admin' ); ?>
						<input type="hidden" name="action" value="lithia_project_admin_action" />
						<input type="hidden" name="intent" value="update_payload_review_state" />
						<label class="lw-project-field">
							<span class="lw-project-field__label"><?php esc_html_e( 'Payload Review State', 'lithia-web-service-theme' ); ?></span>
							<select name="payload_review_state">
								<?php foreach ( lithia_get_project_review_states() as $state ) : ?>
									<option value="<?php echo esc_attr( $state ); ?>" <?php selected( (string) ( $payload['project']['review_state'] ?? 'intake' ), $state ); ?>><?php echo esc_html( $state ); ?></option>
								<?php endforeach; ?>
							</select>
						</label>
						<button type="submit" class="button button-secondary"><?php esc_html_e( 'Update Payload State', 'lithia-web-service-theme' ); ?></button>
					</form>

					<div class="lw-project-links-row">
						<a class="button button-link" href="<?php echo esc_url( lithia_get_launch_wizard_step_url( 'basics' ) ); ?>"><?php esc_html_e( 'Open Launch Wizard', 'lithia-web-service-theme' ); ?></a>
					</div>
				</section>

				<section class="lw-project-panel">
					<header class="lw-project-panel__header">
					<h2><?php esc_html_e( 'Payload Snapshot', 'lithia-web-service-theme' ); ?></h2>
					<p><?php esc_html_e( 'Quick project totals.', 'lithia-web-service-theme' ); ?></p>
				</header>

					<div class="lw-project-grid lw-project-grid--stats">
						<?php lithia_render_project_admin_stat( __( 'Pages', 'lithia-web-service-theme' ), (string) count( (array) ( $payload['pages'] ?? array() ) ) ); ?>
						<?php lithia_render_project_admin_stat( __( 'Offers', 'lithia-web-service-theme' ), (string) count( (array) ( $payload['offers'] ?? array() ) ) ); ?>
						<?php lithia_render_project_admin_stat( __( 'Providers', 'lithia-web-service-theme' ), (string) count( (array) ( $payload['providers'] ?? array() ) ) ); ?>
						<?php lithia_render_project_admin_stat( __( 'Schema Version', 'lithia-web-service-theme' ), (string) ( $payload['project']['schema_version'] ?? lithia_get_project_import_schema_version() ) ); ?>
						<?php lithia_render_project_admin_stat( __( 'Validation Errors', 'lithia-web-service-theme' ), (string) count( (array) ( $validation['errors'] ?? array() ) ) ); ?>
						<?php lithia_render_project_admin_stat( __( 'Validation Warnings', 'lithia-web-service-theme' ), (string) count( (array) ( $validation['warnings'] ?? array() ) ) ); ?>
					</div>
				</section>
			</div>

			<section class="lw-project-panel">
				<header class="lw-project-panel__header">
					<h2><?php esc_html_e( 'Current Validation', 'lithia-web-service-theme' ); ?></h2>
					<p><?php esc_html_e( 'Errors block import. Warnings do not.', 'lithia-web-service-theme' ); ?></p>
				</header>

				<div class="lw-project-grid lw-project-grid--two">
					<?php lithia_render_project_admin_issue_list( __( 'Errors', 'lithia-web-service-theme' ), (array) ( $validation['errors'] ?? array() ), __( 'No validation errors.', 'lithia-web-service-theme' ) ); ?>
					<?php lithia_render_project_admin_issue_list( __( 'Warnings', 'lithia-web-service-theme' ), (array) ( $validation['warnings'] ?? array() ), __( 'No validation warnings.', 'lithia-web-service-theme' ) ); ?>
				</div>
			</section>

		<section class="lw-project-panel">
			<header class="lw-project-panel__header">
				<h2><?php esc_html_e( 'Structured Intake Fields', 'lithia-web-service-theme' ); ?></h2>
				<p><?php esc_html_e( 'Edit the basics here.', 'lithia-web-service-theme' ); ?></p>
			</header>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="lw-project-payload-form">
				<?php wp_nonce_field( 'lithia_project_admin' ); ?>
				<input type="hidden" name="action" value="lithia_project_admin_action" />
				<input type="hidden" name="structured_form" value="1" />
				<input type="hidden" name="payload_json" value="<?php echo esc_attr( $payload_json ); ?>" />
				<input type="hidden" name="sample_key" value="" />

				<div class="lw-project-grid lw-project-grid--two">
					<section class="lw-project-panel lw-project-panel--subtle">
						<header class="lw-project-panel__header">
							<h3><?php esc_html_e( 'Business', 'lithia-web-service-theme' ); ?></h3>
							<p><?php esc_html_e( 'Public business details and CTAs.', 'lithia-web-service-theme' ); ?></p>
						</header>

						<div class="lw-project-grid lw-project-grid--two">
							<label class="lw-project-field">
								<span class="lw-project-field__label"><?php esc_html_e( 'Site Key', 'lithia-web-service-theme' ); ?></span>
								<input type="text" name="project_site_key" value="<?php echo esc_attr( (string) ( $payload['project']['site_key'] ?? '' ) ); ?>" class="regular-text" />
							</label>
							<label class="lw-project-field">
								<span class="lw-project-field__label"><?php esc_html_e( 'Industry', 'lithia-web-service-theme' ); ?></span>
								<input type="text" name="project_industry" value="<?php echo esc_attr( (string) ( $payload['project']['industry'] ?? '' ) ); ?>" class="regular-text" />
							</label>
							<label class="lw-project-field">
								<span class="lw-project-field__label"><?php esc_html_e( 'Brand Name', 'lithia-web-service-theme' ); ?></span>
								<input type="text" name="business_brand_name" value="<?php echo esc_attr( (string) ( $payload['business']['brand_name'] ?? '' ) ); ?>" class="regular-text" />
							</label>
							<label class="lw-project-field">
								<span class="lw-project-field__label"><?php esc_html_e( 'Business Type', 'lithia-web-service-theme' ); ?></span>
								<input type="text" name="business_type" value="<?php echo esc_attr( (string) ( $payload['business']['business_type'] ?? '' ) ); ?>" class="regular-text" />
							</label>
							<label class="lw-project-field lw-project-field--full">
								<span class="lw-project-field__label"><?php esc_html_e( 'Short Tagline', 'lithia-web-service-theme' ); ?></span>
								<input type="text" name="business_short_tagline" value="<?php echo esc_attr( (string) ( $payload['business']['short_tagline'] ?? '' ) ); ?>" class="large-text" />
							</label>
							<label class="lw-project-field">
								<span class="lw-project-field__label"><?php esc_html_e( 'Email', 'lithia-web-service-theme' ); ?></span>
								<input type="email" name="business_email" value="<?php echo esc_attr( (string) ( $payload['business']['email'] ?? '' ) ); ?>" class="regular-text" />
							</label>
							<label class="lw-project-field">
								<span class="lw-project-field__label"><?php esc_html_e( 'Phone', 'lithia-web-service-theme' ); ?></span>
								<input type="text" name="business_phone" value="<?php echo esc_attr( (string) ( $payload['business']['phone'] ?? '' ) ); ?>" class="regular-text" />
							</label>
							<label class="lw-project-field">
								<span class="lw-project-field__label"><?php esc_html_e( 'Primary CTA Label', 'lithia-web-service-theme' ); ?></span>
								<input type="text" name="business_primary_cta_label" value="<?php echo esc_attr( (string) ( $payload['business']['primary_cta_label'] ?? '' ) ); ?>" class="regular-text" />
							</label>
							<label class="lw-project-field">
								<span class="lw-project-field__label"><?php esc_html_e( 'Primary CTA URL', 'lithia-web-service-theme' ); ?></span>
								<input type="url" name="business_primary_cta_target" value="<?php echo esc_attr( (string) ( $payload['business']['primary_cta_target'] ?? '' ) ); ?>" class="regular-text" />
							</label>
							<label class="lw-project-field">
								<span class="lw-project-field__label"><?php esc_html_e( 'Secondary CTA Label', 'lithia-web-service-theme' ); ?></span>
								<input type="text" name="business_secondary_cta_label" value="<?php echo esc_attr( (string) ( $payload['business']['secondary_cta_label'] ?? '' ) ); ?>" class="regular-text" />
							</label>
							<label class="lw-project-field">
								<span class="lw-project-field__label"><?php esc_html_e( 'Secondary CTA URL', 'lithia-web-service-theme' ); ?></span>
								<input type="url" name="business_secondary_cta_target" value="<?php echo esc_attr( (string) ( $payload['business']['secondary_cta_target'] ?? '' ) ); ?>" class="regular-text" />
							</label>
							<label class="lw-project-field">
								<span class="lw-project-field__label"><?php esc_html_e( 'City', 'lithia-web-service-theme' ); ?></span>
								<input type="text" name="location_city" value="<?php echo esc_attr( (string) ( $payload['location']['city'] ?? '' ) ); ?>" class="regular-text" />
							</label>
							<label class="lw-project-field">
								<span class="lw-project-field__label"><?php esc_html_e( 'State / Region', 'lithia-web-service-theme' ); ?></span>
								<input type="text" name="location_state_region" value="<?php echo esc_attr( (string) ( $payload['location']['state_region'] ?? '' ) ); ?>" class="regular-text" />
							</label>
							<label class="lw-project-field">
								<span class="lw-project-field__label"><?php esc_html_e( 'Service Area', 'lithia-web-service-theme' ); ?></span>
								<textarea name="location_service_area" rows="4" class="large-text"><?php echo esc_textarea( lithia_project_admin_format_multiline_list( $payload['location']['service_area'] ?? array() ) ); ?></textarea>
								<span class="description"><?php esc_html_e( 'One city or region per line.', 'lithia-web-service-theme' ); ?></span>
							</label>
							<label class="lw-project-field">
								<span class="lw-project-field__label"><?php esc_html_e( 'Delivery Modes', 'lithia-web-service-theme' ); ?></span>
								<textarea name="location_delivery_modes" rows="4" class="large-text"><?php echo esc_textarea( lithia_project_admin_format_multiline_list( $payload['location']['delivery_modes'] ?? array() ) ); ?></textarea>
								<span class="description"><?php esc_html_e( 'Examples: in-person, online, hybrid.', 'lithia-web-service-theme' ); ?></span>
							</label>
						</div>
					</section>

					<section class="lw-project-panel lw-project-panel--subtle">
						<header class="lw-project-panel__header">
							<h3><?php esc_html_e( 'SEO And Booking', 'lithia-web-service-theme' ); ?></h3>
							<p><?php esc_html_e( 'Keywords and booking defaults.', 'lithia-web-service-theme' ); ?></p>
						</header>

						<div class="lw-project-grid lw-project-grid--two">
							<label class="lw-project-field">
								<span class="lw-project-field__label"><?php esc_html_e( 'Brand Keyword', 'lithia-web-service-theme' ); ?></span>
								<input type="text" name="seo_brand_keyword" value="<?php echo esc_attr( (string) ( $payload['seo']['brand_keyword'] ?? '' ) ); ?>" class="regular-text" />
							</label>
							<label class="lw-project-field">
								<span class="lw-project-field__label"><?php esc_html_e( 'SEO Tone', 'lithia-web-service-theme' ); ?></span>
								<input type="text" name="seo_tone" value="<?php echo esc_attr( (string) ( $payload['seo']['tone'] ?? '' ) ); ?>" class="regular-text" />
							</label>
							<label class="lw-project-field">
								<span class="lw-project-field__label"><?php esc_html_e( 'Primary Terms', 'lithia-web-service-theme' ); ?></span>
								<textarea name="seo_primary_terms" rows="5" class="large-text"><?php echo esc_textarea( lithia_project_admin_format_multiline_list( $payload['seo']['primary_terms'] ?? array() ) ); ?></textarea>
							</label>
							<label class="lw-project-field">
								<span class="lw-project-field__label"><?php esc_html_e( 'Secondary Terms', 'lithia-web-service-theme' ); ?></span>
								<textarea name="seo_secondary_terms" rows="5" class="large-text"><?php echo esc_textarea( lithia_project_admin_format_multiline_list( $payload['seo']['secondary_terms'] ?? array() ) ); ?></textarea>
							</label>
							<label class="lw-project-field">
								<span class="lw-project-field__label"><?php esc_html_e( 'SEO Locations', 'lithia-web-service-theme' ); ?></span>
								<textarea name="seo_locations" rows="4" class="large-text"><?php echo esc_textarea( lithia_project_admin_format_multiline_list( $payload['seo']['locations'] ?? array() ) ); ?></textarea>
							</label>
							<label class="lw-project-field">
								<span class="lw-project-field__label"><?php esc_html_e( 'Booking Mode', 'lithia-web-service-theme' ); ?></span>
								<input type="text" name="booking_mode" value="<?php echo esc_attr( (string) ( $payload['booking']['booking_mode'] ?? '' ) ); ?>" class="regular-text" />
							</label>
							<label class="lw-project-checkbox">
								<input type="checkbox" name="booking_calendar_enabled" value="1" <?php checked( ! empty( $payload['booking']['calendar_enabled'] ) ); ?> />
								<span><?php esc_html_e( 'Calendar enabled', 'lithia-web-service-theme' ); ?></span>
							</label>
							<label class="lw-project-field lw-project-field--full">
								<span class="lw-project-field__label"><?php esc_html_e( 'Booking Notice', 'lithia-web-service-theme' ); ?></span>
								<textarea name="booking_notice" rows="6" class="large-text"><?php echo esc_textarea( (string) ( $payload['booking']['booking_notice'] ?? '' ) ); ?></textarea>
							</label>
						</div>
					</section>
				</div>

				<section class="lw-project-panel lw-project-panel--subtle">
					<header class="lw-project-panel__header">
						<h3><?php esc_html_e( 'Offers', 'lithia-web-service-theme' ); ?></h3>
						<p><?php esc_html_e( 'One row per offer.', 'lithia-web-service-theme' ); ?></p>
					</header>

					<div class="lw-project-table-wrap">
						<table class="widefat striped lw-project-offers-table">
							<thead>
								<tr>
									<th><?php esc_html_e( 'Record Key', 'lithia-web-service-theme' ); ?></th>
									<th><?php esc_html_e( 'Title', 'lithia-web-service-theme' ); ?></th>
									<th><?php esc_html_e( 'Slug', 'lithia-web-service-theme' ); ?></th>
									<th><?php esc_html_e( 'Summary', 'lithia-web-service-theme' ); ?></th>
									<th><?php esc_html_e( 'Delivery Mode', 'lithia-web-service-theme' ); ?></th>
									<th><?php esc_html_e( 'Timeline', 'lithia-web-service-theme' ); ?></th>
									<th><?php esc_html_e( 'Price From', 'lithia-web-service-theme' ); ?></th>
									<th><?php esc_html_e( 'Price Notes', 'lithia-web-service-theme' ); ?></th>
									<th><?php esc_html_e( 'Audience', 'lithia-web-service-theme' ); ?></th>
									<th><?php esc_html_e( 'Outcomes', 'lithia-web-service-theme' ); ?></th>
									<th><?php esc_html_e( 'CTA Label', 'lithia-web-service-theme' ); ?></th>
									<th><?php esc_html_e( 'CTA URL', 'lithia-web-service-theme' ); ?></th>
									<th><?php esc_html_e( 'Spotlight', 'lithia-web-service-theme' ); ?></th>
									<th><?php esc_html_e( 'Slide Order', 'lithia-web-service-theme' ); ?></th>
									<th><?php esc_html_e( 'Provider Slugs', 'lithia-web-service-theme' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ( $offer_rows as $offer_index => $offer_row ) : ?>
									<tr>
										<td><input type="text" name="service_record_key[]" value="<?php echo esc_attr( (string) $offer_row['record_key'] ); ?>" class="regular-text code" /></td>
										<td><input type="text" name="service_title[]" value="<?php echo esc_attr( (string) $offer_row['title'] ); ?>" class="regular-text" /></td>
										<td><input type="text" name="service_slug[]" value="<?php echo esc_attr( (string) $offer_row['slug'] ); ?>" class="regular-text code" /></td>
										<td><textarea name="service_summary[]" rows="4" class="large-text"><?php echo esc_textarea( (string) $offer_row['summary'] ); ?></textarea></td>
										<td><input type="text" name="service_delivery_mode[]" value="<?php echo esc_attr( (string) $offer_row['delivery_mode'] ); ?>" class="regular-text" /></td>
										<td><input type="text" name="service_timeline[]" value="<?php echo esc_attr( (string) $offer_row['timeline'] ); ?>" class="regular-text" /></td>
										<td><input type="text" name="service_price_from[]" value="<?php echo esc_attr( (string) $offer_row['price_from'] ); ?>" class="regular-text" placeholder="$120" /></td>
										<td><textarea name="service_price_notes[]" rows="3" class="large-text"><?php echo esc_textarea( (string) $offer_row['price_notes'] ); ?></textarea></td>
										<td><textarea name="service_audience[]" rows="3" class="large-text"><?php echo esc_textarea( (string) $offer_row['audience'] ); ?></textarea></td>
										<td><textarea name="service_outcomes[]" rows="3" class="large-text"><?php echo esc_textarea( (string) $offer_row['outcomes'] ); ?></textarea></td>
										<td><input type="text" name="service_primary_cta_label[]" value="<?php echo esc_attr( (string) $offer_row['primary_cta_label'] ); ?>" class="regular-text" /></td>
										<td><input type="url" name="service_primary_cta_url[]" value="<?php echo esc_attr( (string) $offer_row['primary_cta_url'] ); ?>" class="regular-text" /></td>
										<td>
											<input type="hidden" name="service_homepage_spotlight[<?php echo esc_attr( (string) $offer_index ); ?>]" value="0" />
											<input type="checkbox" name="service_homepage_spotlight[<?php echo esc_attr( (string) $offer_index ); ?>]" value="1" <?php checked( ! empty( $offer_row['homepage_spotlight_enabled'] ) ); ?> />
										</td>
										<td><input type="number" min="0" step="1" name="service_homepage_spotlight_order[]" value="<?php echo esc_attr( (string) $offer_row['homepage_spotlight_order'] ); ?>" class="small-text" /></td>
										<td><textarea name="service_provider_slugs[]" rows="4" class="large-text code"><?php echo esc_textarea( (string) $offer_row['provider_slugs'] ); ?></textarea></td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				</section>

				<section class="lw-project-panel lw-project-panel--subtle">
					<header class="lw-project-panel__header">
						<h3><?php esc_html_e( 'FAQ', 'lithia-web-service-theme' ); ?></h3>
						<p><?php esc_html_e( 'Short answers only.', 'lithia-web-service-theme' ); ?></p>
					</header>

					<div class="lw-project-grid lw-project-grid--stack">
						<?php foreach ( $faq_rows as $faq_row ) : ?>
							<div class="lw-project-grid lw-project-grid--two lw-project-faq-row">
								<label class="lw-project-field">
									<span class="lw-project-field__label"><?php esc_html_e( 'Question', 'lithia-web-service-theme' ); ?></span>
									<input type="text" name="faq_question[]" value="<?php echo esc_attr( (string) $faq_row['question'] ); ?>" class="large-text" />
								</label>
								<label class="lw-project-field">
									<span class="lw-project-field__label"><?php esc_html_e( 'Answer Seed', 'lithia-web-service-theme' ); ?></span>
									<textarea name="faq_answer[]" rows="4" class="large-text"><?php echo esc_textarea( (string) $faq_row['answer'] ); ?></textarea>
								</label>
							</div>
						<?php endforeach; ?>
					</div>

					<label class="lw-project-field">
						<span class="lw-project-field__label"><?php esc_html_e( 'Payload Review State', 'lithia-web-service-theme' ); ?></span>
						<select name="payload_review_state">
							<?php foreach ( lithia_get_project_review_states() as $state ) : ?>
								<option value="<?php echo esc_attr( $state ); ?>" <?php selected( (string) ( $payload['project']['review_state'] ?? 'intake' ), $state ); ?>><?php echo esc_html( $state ); ?></option>
							<?php endforeach; ?>
						</select>
					</label>

					<label class="lw-project-checkbox">
						<input type="checkbox" name="force_import" value="1" />
						<span><?php esc_html_e( 'Force importer writes even when a managed field differs from its last imported snapshot.', 'lithia-web-service-theme' ); ?></span>
					</label>

					<div class="lw-project-actions">
						<button type="submit" class="button button-secondary" name="intent" value="build_from_launch_wizard"><?php esc_html_e( 'Rebuild From Launch Wizard', 'lithia-web-service-theme' ); ?></button>
						<?php foreach ( $samples as $sample_key => $sample ) : ?>
							<button type="submit" class="button button-secondary" name="intent" value="load_sample_payload" onclick="this.form.sample_key.value='<?php echo esc_js( (string) $sample_key ); ?>';"><?php echo esc_html( $sample['label'] ); ?></button>
						<?php endforeach; ?>
						<button type="submit" class="button button-secondary" name="intent" value="save_payload"><?php esc_html_e( 'Save Payload Draft', 'lithia-web-service-theme' ); ?></button>
						<button type="submit" class="button button-secondary" name="intent" value="download_payload"><?php esc_html_e( 'Download Payload JSON', 'lithia-web-service-theme' ); ?></button>
						<button type="submit" class="button button-secondary" name="intent" value="dry_run_import"><?php esc_html_e( 'Dry Run Import', 'lithia-web-service-theme' ); ?></button>
						<button type="submit" class="button button-primary" name="intent" value="apply_import"><?php esc_html_e( 'Apply Import', 'lithia-web-service-theme' ); ?></button>
					</div>
				</section>
			</form>
		</section>

		<section class="lw-project-panel">
			<header class="lw-project-panel__header">
				<h2><?php esc_html_e( 'Canonical Payload', 'lithia-web-service-theme' ); ?></h2>
				<p><?php esc_html_e( 'Advanced JSON only.', 'lithia-web-service-theme' ); ?></p>
			</header>

				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="lw-project-payload-form">
					<?php wp_nonce_field( 'lithia_project_admin' ); ?>
					<input type="hidden" name="action" value="lithia_project_admin_action" />
					<input type="hidden" name="sample_key" value="" />
					<details class="lw-project-report">
						<summary><?php esc_html_e( 'View Or Edit Raw JSON', 'lithia-web-service-theme' ); ?></summary>
						<label class="lw-project-field">
							<span class="lw-project-field__label"><?php esc_html_e( 'Project Payload JSON', 'lithia-web-service-theme' ); ?></span>
							<textarea name="payload_json" rows="30" class="large-text code"><?php echo esc_textarea( $payload_json ); ?></textarea>
						</label>
					</details>

					<div class="lw-project-actions">
						<button type="submit" class="button button-secondary" name="intent" value="save_payload"><?php esc_html_e( 'Save JSON Draft', 'lithia-web-service-theme' ); ?></button>
						<button type="submit" class="button button-secondary" name="intent" value="download_payload"><?php esc_html_e( 'Download Payload JSON', 'lithia-web-service-theme' ); ?></button>
					</div>
			</form>
		</section>

		<section class="lw-project-panel">
			<header class="lw-project-panel__header">
				<h2><?php esc_html_e( 'Managed Content Locks', 'lithia-web-service-theme' ); ?></h2>
				<p><?php esc_html_e( 'Lock hand-edited content.', 'lithia-web-service-theme' ); ?></p>
			</header>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="lw-project-locks-form">
				<?php wp_nonce_field( 'lithia_project_admin' ); ?>
				<input type="hidden" name="action" value="lithia_project_admin_action" />
				<input type="hidden" name="intent" value="save_locks" />

				<div class="lw-project-grid lw-project-grid--stack">
					<?php lithia_render_project_admin_entity_table( __( 'Pages', 'lithia-web-service-theme' ), $entities['pages'] ); ?>
					<?php lithia_render_project_admin_entity_table( __( 'Services', 'lithia-web-service-theme' ), $entities['services'] ); ?>
					<?php lithia_render_project_admin_entity_table( __( 'Providers', 'lithia-web-service-theme' ), $entities['providers'] ); ?>
				</div>

				<div class="lw-project-actions">
					<button type="submit" class="button button-secondary"><?php esc_html_e( 'Save Locks', 'lithia-web-service-theme' ); ?></button>
				</div>
			</form>
		</section>

		<section class="lw-project-panel">
			<header class="lw-project-panel__header">
				<h2><?php esc_html_e( 'Last Import Report', 'lithia-web-service-theme' ); ?></h2>
				<p><?php esc_html_e( 'Most recent run only.', 'lithia-web-service-theme' ); ?></p>
			</header>

			<?php if ( empty( $report ) ) : ?>
				<p class="description"><?php esc_html_e( 'No import report saved yet.', 'lithia-web-service-theme' ); ?></p>
			<?php else : ?>
				<div class="lw-project-grid lw-project-grid--stats">
					<?php lithia_render_project_admin_stat( __( 'Action', 'lithia-web-service-theme' ), (string) ( $report_state['intent'] ?? 'n/a' ) ); ?>
					<?php lithia_render_project_admin_stat( __( 'Recorded At', 'lithia-web-service-theme' ), (string) ( $report_state['recorded_at'] ?? 'n/a' ) ); ?>
					<?php lithia_render_project_admin_stat( __( 'Pages', 'lithia-web-service-theme' ), wp_json_encode( $report['summary']['pages'] ?? array() ) ?: 'n/a' ); ?>
					<?php lithia_render_project_admin_stat( __( 'Page Seeds', 'lithia-web-service-theme' ), wp_json_encode( $report['summary']['page_seeds'] ?? array() ) ?: 'n/a' ); ?>
					<?php lithia_render_project_admin_stat( __( 'Offers', 'lithia-web-service-theme' ), wp_json_encode( $report['summary']['offers'] ?? array() ) ?: 'n/a' ); ?>
					<?php lithia_render_project_admin_stat( __( 'Providers', 'lithia-web-service-theme' ), wp_json_encode( $report['summary']['providers'] ?? array() ) ?: 'n/a' ); ?>
					<?php lithia_render_project_admin_stat( __( 'Relationships', 'lithia-web-service-theme' ), (string) ( $report['relationships_synced'] ?? 0 ) ); ?>
				</div>

				<details class="lw-project-report">
					<summary><?php esc_html_e( 'View Raw Report JSON', 'lithia-web-service-theme' ); ?></summary>
					<pre><?php echo esc_html( lithia_project_admin_encode_payload( $report ) ); ?></pre>
				</details>
				<?php endif; ?>
			</section>

			<?php lithia_render_project_admin_history_table( $history ); ?>
		</div>
	<?php
}
