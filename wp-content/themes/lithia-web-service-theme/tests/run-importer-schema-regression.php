<?php
/**
 * Schema-focused importer regression runner (3.b).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( 1 );
}

$results = array();

$assert = static function ( bool $condition, string $name, array $meta = array() ) use ( &$results ): void {
	$results[] = array(
		'name' => $name,
		'pass' => $condition,
		'meta' => $meta,
	);
};

$payload_path = ABSPATH . 'Docs/template-system/sample-payloads/service-business-v1-starter.json';
$payload      = json_decode( (string) file_get_contents( $payload_path ), true );

if ( ! is_array( $payload ) ) {
	echo wp_json_encode(
		array(
			'runner' => 'lithia-importer-schema-regression-3b',
			'error'  => 'Could not decode starter payload JSON.',
		),
		JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
	) . "\n";
	exit( 1 );
}

$payload['project']['review_state'] = 'approved';

if ( empty( $payload['offers'][0] ) || ! is_array( $payload['offers'][0] ) ) {
	$payload['offers'][0] = array(
		'title' => 'Schema Test Offer',
		'slug'  => 'schema-test-offer',
	);
}

$payload['offers'][0]['service_price_from']        = '$325';
$payload['offers'][0]['service_price_notes']       = 'Starting price before optional add-ons.';
$payload['offers'][0]['service_audience']          = array( 'Small business owners', 'Nonprofits' );
$payload['offers'][0]['service_outcomes']          = array( 'Clear roadmap', 'Prioritized next steps' );
$payload['offers'][0]['service_primary_cta_label'] = 'Book Discovery';
$payload['offers'][0]['service_primary_cta_url']   = '/book-discovery/';
$payload['offers'][0]['provider_slugs']            = array( 'billy' );

$payload['faq'] = array(
	array(
		'question'    => 'Do you work with small teams?',
		'answer_seed' => 'Yes, we support lean teams with practical scope and phased delivery.',
		'page_scope'  => 'home',
	),
	array(
		'question'    => 'Can you help after launch?',
		'answer_seed' => 'Yes, ongoing support and iterative improvements are available.',
		'page_scope'  => 'contact',
	),
);

$payload['proof'] = array(
	'years_experience' => '12+',
	'credentials'      => array( 'WordPress specialist', 'Accessibility-first process' ),
	'highlights'       => array( '100+ launches', 'Long-term support retainers' ),
	'awards'           => array( 'Regional small business partner award' ),
	'testimonials'     => array(
		array(
			'name'     => 'Alex',
			'quote'    => 'Clear plan, faster decisions, better outcomes.',
			'role'     => 'Founder',
			'location' => 'Portland',
		),
	),
);

$payload['pages'][0]['headline_seed']        = 'Schema Regression Home Headline';
$payload['pages'][0]['summary_seed']         = 'Schema Regression Home Summary';
$payload['pages'][0]['cta_label']            = 'Start Project';
$payload['pages'][0]['cta_target']           = '/contact/';
$payload['pages'][0]['seo_title_seed']       = 'Schema Regression Home SEO Title';
$payload['pages'][0]['seo_description_seed'] = 'Schema Regression Home SEO Description';

$normalized = lithia_normalize_project_payload( $payload );

$assert(
	! empty( $normalized['project']['schema_version'] ),
	'schema_version_is_present_after_normalization',
	array( 'schema_version' => (string) ( $normalized['project']['schema_version'] ?? '' ) )
);

$normalized_offer = lithia_project_import_normalize_offer_payload( (array) $payload['offers'][0], $payload );

$assert(
	'$325' === (string) $normalized_offer['service_price_from'],
	'service_fields_map_price_from'
);

$assert(
	'Starting price before optional add-ons.' === (string) $normalized_offer['service_price_notes'],
	'service_fields_map_price_notes'
);

$assert(
	array( 'Small business owners', 'Nonprofits' ) === (array) $normalized_offer['service_audience'],
	'service_fields_map_audience'
);

$assert(
	array( 'Clear roadmap', 'Prioritized next steps' ) === (array) $normalized_offer['service_outcomes'],
	'service_fields_map_outcomes'
);

$assert(
	'Book Discovery' === (string) $normalized_offer['service_primary_cta_label'],
	'service_fields_map_primary_cta_fields'
);

$validation = lithia_validate_project_payload( $payload );

$assert(
	true === (bool) ( $validation['is_valid'] ?? false ),
	'validation_accepts_schema_payload',
	array( 'error_count' => count( (array) ( $validation['errors'] ?? array() ) ) )
);

$dry_run = lithia_import_project_payload(
	$payload,
	array(
		'dry_run' => true,
		'source'  => 'regression_3b_dry',
	)
);

$assert(
	true === (bool) ( $dry_run['success'] ?? false ),
	'dry_run_import_succeeds'
);

$assert(
	isset( $dry_run['page_seeds']['count'] ) && 5 <= (int) $dry_run['page_seeds']['count'],
	'dry_run_returns_page_seeds_summary',
	array( 'page_seeds_count' => (int) ( $dry_run['page_seeds']['count'] ?? 0 ) )
);

$assert(
	2 === (int) ( $dry_run['faq']['count'] ?? 0 ),
	'dry_run_returns_faq_summary_count',
	array( 'faq_count' => (int) ( $dry_run['faq']['count'] ?? 0 ) )
);

$assert(
	1 === (int) ( $dry_run['proof']['testimonials_count'] ?? 0 ),
	'dry_run_returns_proof_testimonial_count',
	array( 'testimonials_count' => (int) ( $dry_run['proof']['testimonials_count'] ?? 0 ) )
);

$assert(
	isset( $dry_run['summary']['page_seeds']['count'] ),
	'dry_run_summary_contains_page_seeds'
);

$original_page_seeds = get_option( lithia_get_project_page_seeds_option_name(), null );
$original_faq        = get_option( lithia_get_project_faq_option_name(), null );
$original_proof      = get_option( lithia_get_project_proof_option_name(), null );

$page_seed_write = lithia_project_import_page_seeds( (array) $payload['pages'], array( 'dry_run' => false ) );
$faq_write       = lithia_project_import_faq( (array) $payload['faq'], array( 'dry_run' => false ) );
$proof_write     = lithia_project_import_proof( (array) $payload['proof'], array( 'dry_run' => false ) );

$stored_page_seeds = get_option( lithia_get_project_page_seeds_option_name(), array() );
$stored_faq        = get_option( lithia_get_project_faq_option_name(), array() );
$stored_proof      = get_option( lithia_get_project_proof_option_name(), array() );

$assert(
	'page_seeds' === (string) ( $page_seed_write['type'] ?? '' ) && ! empty( $stored_page_seeds ),
	'page_seeds_importer_writes_option',
	array( 'count' => count( (array) $stored_page_seeds ) )
);

$assert(
	'faq' === (string) ( $faq_write['type'] ?? '' ) && 2 === count( (array) $stored_faq ),
	'faq_importer_writes_option',
	array( 'count' => count( (array) $stored_faq ) )
);

$assert(
	'proof' === (string) ( $proof_write['type'] ?? '' )
	&& 1 === count( (array) ( $stored_proof['testimonials'] ?? array() ) ),
	'proof_importer_writes_option',
	array( 'testimonials_count' => count( (array) ( $stored_proof['testimonials'] ?? array() ) ) )
);

if ( null === $original_page_seeds ) {
	delete_option( lithia_get_project_page_seeds_option_name() );
} else {
	update_option( lithia_get_project_page_seeds_option_name(), $original_page_seeds, false );
}

if ( null === $original_faq ) {
	delete_option( lithia_get_project_faq_option_name() );
} else {
	update_option( lithia_get_project_faq_option_name(), $original_faq, false );
}

if ( null === $original_proof ) {
	delete_option( lithia_get_project_proof_option_name() );
} else {
	update_option( lithia_get_project_proof_option_name(), $original_proof, false );
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
	'runner'       => 'lithia-importer-schema-regression-3b',
	'generated_at' => gmdate( 'c' ),
	'totals'       => array(
		'tests'  => count( $results ),
		'passed' => $passed,
		'failed' => $failed,
	),
	'tests'        => $results,
);

echo wp_json_encode( $output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) . "\n";

if ( $failed > 0 ) {
	exit( 1 );
}
