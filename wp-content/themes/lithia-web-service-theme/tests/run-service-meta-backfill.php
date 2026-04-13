<?php
/**
 * Backfill canonical service meta from legacy offer meta where canonical keys are missing.
 *
 * Usage:
 *   wp eval-file wp-content/themes/lithia-web-service-theme/tests/run-service-meta-backfill.php --path=/path/to/wp
 *
 * Optional env vars:
 *   LITHIA_BACKFILL_APPLY=1   Apply updates. Defaults to dry-run.
 *   LITHIA_BACKFILL_LIMIT=100 Limit number of service posts scanned. Defaults to all.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( 1 );
}

/**
 * Return true when meta value should be treated as empty for backfill decisions.
 *
 * @param mixed $value Meta value.
 * @return bool
 */
function lithia_backfill_meta_is_empty( $value ): bool {
	if ( is_array( $value ) ) {
		return empty( $value );
	}

	if ( is_bool( $value ) ) {
		return false === $value;
	}

	if ( null === $value ) {
		return true;
	}

	return '' === trim( (string) $value );
}

/**
 * Find the first non-empty value for a post meta key.
 *
 * @param int    $post_id  Post ID.
 * @param string $meta_key Meta key.
 * @return array{exists:bool,has_non_empty:bool,value:mixed}
 */
function lithia_backfill_find_non_empty_meta_value( int $post_id, string $meta_key ): array {
	$exists = metadata_exists( 'post', $post_id, $meta_key );
	if ( ! $exists ) {
		return array(
			'exists'        => false,
			'has_non_empty' => false,
			'value'         => null,
		);
	}

	$values = get_post_meta( $post_id, $meta_key, false );
	if ( ! is_array( $values ) ) {
		$values = array( get_post_meta( $post_id, $meta_key, true ) );
	}

	foreach ( $values as $value ) {
		if ( ! lithia_backfill_meta_is_empty( $value ) ) {
			return array(
				'exists'        => true,
				'has_non_empty' => true,
				'value'         => $value,
			);
		}
	}

	return array(
		'exists'        => true,
		'has_non_empty' => false,
		'value'         => null,
	);
}

$apply = '1' === (string) getenv( 'LITHIA_BACKFILL_APPLY' );
$limit = absint( (string) getenv( 'LITHIA_BACKFILL_LIMIT' ) );

$meta_map = array(
	'offer_hero_title'          => 'service_hero_title',
	'offer_hero_text'           => 'service_hero_text',
	'offer_primary_cta_label'   => 'service_primary_cta_label',
	'offer_primary_cta_url'     => 'service_primary_cta_url',
	'offer_secondary_cta_label' => 'service_secondary_cta_label',
	'offer_secondary_cta_url'   => 'service_secondary_cta_url',
	'offer_overview_heading'    => 'service_overview_heading',
	'offer_overview_text'       => 'service_overview_text',
	'offer_highlights_heading'  => 'service_highlights_heading',
	'offer_highlights'          => 'service_highlights',
	'offer_process_heading'     => 'service_process_heading',
	'offer_process_steps'       => 'service_process_steps',
	'offer_booking_note'        => 'service_booking_note',
	'offer_timeline'            => 'service_timeline',
	'offer_delivery_mode'       => 'service_delivery_mode',
	'offer_platform'            => 'service_platform',
	'offer_engagement_type'     => 'service_engagement_type',
	'offer_price_from'          => 'service_price_from',
	'offer_price_notes'         => 'service_price_notes',
	'offer_audience'            => 'service_audience',
	'offer_outcomes'            => 'service_outcomes',
	'offer_provider_slugs'      => 'provider_slugs',
	'offer_price_amount'        => 'service_price_amount',
);

$query_args = array(
	'post_type'      => 'services',
	'post_status'    => array( 'publish', 'draft', 'pending', 'private', 'future', 'trash' ),
	'posts_per_page' => $limit > 0 ? $limit : -1,
	'fields'         => 'ids',
	'orderby'        => 'ID',
	'order'          => 'ASC',
	'no_found_rows'  => true,
);

$service_ids = get_posts( $query_args );

$totals = array(
	'posts_scanned'        => count( $service_ids ),
	'posts_with_updates'   => 0,
	'canonical_keys_added' => 0,
	'legacy_keys_seen'     => 0,
	'canonical_present'    => 0,
	'empty_legacy_values'  => 0,
	'update_failures'      => 0,
);

$updates = array();

foreach ( $service_ids as $service_id ) {
	$service_id      = (int) $service_id;
	$post_updates    = array();
	$post_skips      = array();
	$legacy_keys_for_post = 0;

	foreach ( $meta_map as $legacy_key => $canonical_key ) {
		$legacy_meta = lithia_backfill_find_non_empty_meta_value( $service_id, $legacy_key );
		if ( ! $legacy_meta['exists'] ) {
			continue;
		}

		$legacy_keys_for_post++;

		if ( ! $legacy_meta['has_non_empty'] ) {
			$totals['empty_legacy_values']++;
			continue;
		}
		$legacy_value = $legacy_meta['value'];

		$canonical_meta  = lithia_backfill_find_non_empty_meta_value( $service_id, $canonical_key );
		$canonical_empty = ! $canonical_meta['exists'] || ! $canonical_meta['has_non_empty'];

		if ( ! $canonical_empty ) {
			$totals['canonical_present']++;
			$post_skips[] = array(
				'legacy_key'    => $legacy_key,
				'canonical_key' => $canonical_key,
				'reason'        => 'canonical_already_present',
			);
			continue;
		}

		$did_update = true;
		if ( $apply ) {
			$did_update = (bool) update_post_meta( $service_id, $canonical_key, $legacy_value );
		}

		if ( ! $did_update && $apply ) {
			$totals['update_failures']++;
			$post_skips[] = array(
				'legacy_key'    => $legacy_key,
				'canonical_key' => $canonical_key,
				'reason'        => 'update_failed',
			);
			continue;
		}

		$totals['canonical_keys_added']++;
		$post_updates[] = array(
			'legacy_key'    => $legacy_key,
			'canonical_key' => $canonical_key,
		);
	}

	$totals['legacy_keys_seen'] += $legacy_keys_for_post;

	if ( ! empty( $post_updates ) ) {
		$totals['posts_with_updates']++;
		$updates[] = array(
			'post_id'      => $service_id,
			'post_title'   => (string) get_the_title( $service_id ),
			'update_count' => count( $post_updates ),
			'updates'      => $post_updates,
		);
		continue;
	}

	if ( ! empty( $post_skips ) ) {
		$updates[] = array(
			'post_id'      => $service_id,
			'post_title'   => (string) get_the_title( $service_id ),
			'update_count' => 0,
			'skips'        => $post_skips,
		);
	}
}

$output = array(
	'runner'       => 'lithia-service-meta-backfill-slice3',
	'generated_at' => gmdate( 'c' ),
	'mode'         => $apply ? 'apply' : 'dry-run',
	'totals'       => $totals,
	'pass'         => 0 === (int) $totals['update_failures'],
	'updates'      => $updates,
);

echo wp_json_encode( $output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) . "\n";

if ( ! $output['pass'] ) {
	exit( 1 );
}
