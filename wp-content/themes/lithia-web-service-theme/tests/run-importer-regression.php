<?php
/**
 * Lightweight importer regression runner (WP-CLI via eval-file).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( 1 );
}

$results = array();
$temp_ids = array();

$assert = static function ( bool $condition, string $name, array $meta = array() ) use ( &$results ): void {
	$results[] = array(
		'name'   => $name,
		'pass'   => $condition,
		'meta'   => $meta,
	);
};

$make_payload = static function () {
	$payload_path = ABSPATH . 'Docs/template-system/sample-payloads/service-business-v1-starter.json';
	$payload      = json_decode( (string) file_get_contents( $payload_path ), true );

	if ( ! is_array( $payload ) ) {
		return array();
	}

	return $payload;
};

try {
	$assert(
		array( 'alpha', 'beta', 'gamma' ) === lithia_project_import_normalize_slug_list( "Alpha, beta | Gamma\nbeta" ),
		'normalize_slug_list_dedupes_and_sanitizes'
	);

	$assert(
		array( 'One', 'Two', 'Three' ) === lithia_project_import_normalize_label_list( "One|Two\nTwo,Three" ),
		'normalize_label_list_dedupes_and_keeps_labels'
	);

	$assert(
		1234.5 === lithia_project_import_parse_price_amount( '$1,234.50' ),
		'parse_price_amount_numeric'
	);

	$assert(
		null === lithia_project_import_parse_price_amount( 'TBD' ),
		'parse_price_amount_invalid_returns_null'
	);

	$normalized_offer = lithia_project_import_normalize_offer_payload(
		array(
			'title'                     => 'Service Offer',
			'slug'                      => 'service-offer',
			'service_primary_cta_label' => 'Book Now',
			'service_primary_cta_url'   => '/book-now/',
			'service_delivery_mode'     => 'Video call',
			'service_price_from'        => '$249',
			'provider_slugs'            => "sam|taylor",
			'service_overview_text'     => 'Overview text',
		),
		array( 'booking' => array() )
	);

	$assert(
		'Book Now' === (string) $normalized_offer['service_primary_cta_label'],
		'service_fields_map_primary_cta_label'
	);

	$assert(
		'Video call' === (string) $normalized_offer['service_delivery_mode'],
		'service_fields_map_delivery_mode'
	);

	$assert(
		array( 'sam', 'taylor' ) === (array) $normalized_offer['provider_slugs'],
		'service_fields_map_provider_slugs'
	);

	$validation_payload = $make_payload();
	unset( $validation_payload['project']['site_key'] );
	$validation_missing_site_key = lithia_validate_project_payload( $validation_payload );
	$missing_site_key_codes      = wp_list_pluck( (array) $validation_missing_site_key['errors'], 'code' );

	$assert(
		in_array( 'missing_site_key', $missing_site_key_codes, true ),
		'validation_flags_missing_site_key',
		array( 'codes' => $missing_site_key_codes )
	);

	$validation_duplicate_offers = $make_payload();
	$validation_duplicate_offers['project']['review_state'] = 'approved';
	$validation_duplicate_offers['offers'][] = array(
		'record_key' => 'service_duplicate_slug_test',
		'title'      => 'Duplicate Slug Offer',
		'slug'       => 'existing-site-work',
	);
	$duplicate_offer_result = lithia_validate_project_payload( $validation_duplicate_offers );
	$duplicate_offer_codes  = wp_list_pluck( (array) $duplicate_offer_result['errors'], 'code' );

	$assert(
		in_array( 'duplicate_slug', $duplicate_offer_codes, true ),
		'validation_flags_duplicate_offer_slug',
		array( 'codes' => $duplicate_offer_codes )
	);

	$provider_warning_payload = $make_payload();
	$provider_warning_payload['project']['review_state'] = 'approved';
	$provider_warning_payload['providers'] = array();
	$provider_warning_payload['offers']    = array(
		array(
			'record_key'      => 'service_warning_test',
			'title'           => 'Warning Service',
			'slug'            => 'warning-service',
			'provider_slugs'  => array( 'missing-provider' ),
		),
	);
	$provider_warning_result = lithia_validate_project_payload( $provider_warning_payload );
	$provider_warning_codes  = wp_list_pluck( (array) $provider_warning_result['warnings'], 'code' );

	$assert(
		in_array( 'missing_provider_reference', $provider_warning_codes, true ),
		'validation_warns_missing_provider_reference',
		array( 'codes' => $provider_warning_codes )
	);

	$locked_post_id = wp_insert_post(
		array(
			'post_type'    => 'services',
			'post_title'   => 'Locked Offer Test',
			'post_name'    => 'locked-offer-test',
			'post_status'  => 'publish',
		),
		true
	);

	if ( ! is_wp_error( $locked_post_id ) && $locked_post_id ) {
		$locked_post_id = (int) $locked_post_id;
		$temp_ids[] = $locked_post_id;
		update_post_meta( $locked_post_id, lithia_get_record_key_meta_key(), 'service_locked_test' );
		update_post_meta( $locked_post_id, '_lithia_sync_lock', 'yes' );

		$assert(
			true === lithia_project_import_is_post_locked( $locked_post_id ),
			'post_lock_flag_detected'
		);

		$locked_payload = $make_payload();
		$locked_payload['project']['review_state'] = 'approved';
		$locked_payload['offers'] = array(
			array(
				'record_key' => 'service_locked_test',
				'title'      => 'Should Not Update',
				'slug'       => 'locked-offer-test',
			),
		);

		$locked_import = lithia_import_project_payload(
			$locked_payload,
			array(
				'dry_run' => true,
				'source'  => 'regression_3a_locked',
			)
		);

		$locked_status = '';
		if ( ! empty( $locked_import['offers'][0]['status'] ) ) {
			$locked_status = (string) $locked_import['offers'][0]['status'];
		}

		$assert(
			'locked' === $locked_status,
			'import_respects_lock_flag_in_dry_run',
			array( 'status' => $locked_status )
		);
	}

	$snapshot_post_id = wp_insert_post(
		array(
			'post_type'    => 'services',
			'post_title'   => 'Snapshot Guard Test',
			'post_name'    => 'snapshot-guard-test',
			'post_status'  => 'publish',
		),
		true
	);

	if ( ! is_wp_error( $snapshot_post_id ) && $snapshot_post_id ) {
		$snapshot_post_id = (int) $snapshot_post_id;
		$temp_ids[] = $snapshot_post_id;
		update_post_meta(
			$snapshot_post_id,
			'_lithia_import_snapshot',
			array(
				'post:post_title' => 'Importer Title',
			)
		);

		$can_write_mismatch = lithia_project_import_can_write_managed_value(
			$snapshot_post_id,
			'post:post_title',
			'Manually Edited Title',
			false
		);

		$assert(
			false === $can_write_mismatch,
			'managed_snapshot_blocks_mismatched_write'
		);

		$can_write_force = lithia_project_import_can_write_managed_value(
			$snapshot_post_id,
			'post:post_title',
			'Manually Edited Title',
			true
		);

		$assert(
			true === $can_write_force,
			'force_overrides_snapshot_guard'
		);

		$can_write_match = lithia_project_import_can_write_managed_value(
			$snapshot_post_id,
			'post:post_title',
			'Importer Title',
			false
		);

		$assert(
			true === $can_write_match,
			'snapshot_allows_matching_write'
		);
	}

	$intake_payload = $make_payload();
	$intake_payload['project']['review_state'] = 'intake';

	$intake_apply = lithia_import_project_payload(
		$intake_payload,
		array(
			'dry_run' => false,
			'source'  => 'regression_3a_intake_apply',
		)
	);

	$assert(
		false === (bool) ( $intake_apply['success'] ?? false )
		&& false !== strpos( (string) ( $intake_apply['error'] ?? '' ), 'must be approved before import' ),
		'review_state_blocks_non_dry_import_when_intake'
	);

	$intake_dry = lithia_import_project_payload(
		$intake_payload,
		array(
			'dry_run' => true,
			'source'  => 'regression_3a_intake_dry',
		)
	);

	$assert(
		true === (bool) ( $intake_dry['success'] ?? false ),
		'review_state_allows_dry_import_when_intake'
	);
} finally {
	foreach ( $temp_ids as $temp_id ) {
		wp_delete_post( (int) $temp_id, true );
	}
}

$passed = 0;
$failed = 0;

foreach ( $results as $test_result ) {
	if ( ! empty( $test_result['pass'] ) ) {
		$passed++;
	} else {
		$failed++;
	}
}

$output = array(
	'runner'      => 'lithia-importer-regression-3a',
	'generated_at' => gmdate( 'c' ),
	'totals'      => array(
		'tests'  => count( $results ),
		'passed' => $passed,
		'failed' => $failed,
	),
	'tests'       => $results,
);

echo wp_json_encode( $output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) . "\n";

if ( $failed > 0 ) {
	exit( 1 );
}
