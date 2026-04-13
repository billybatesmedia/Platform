<?php
/**
 * Seed-site sync helpers for sheet/CSV-driven launches.
 *
 * @package LithiaWebServiceTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return the supported one-row sync scopes for phase 1.
 *
 * @return array
 */
function lithia_get_seed_sync_supported_scopes(): array {
	return array(
		'site_settings',
		'business_details',
		'brand_content',
		'site_styles',
	);
}

/**
 * Return the meta key used for stable content record keys.
 *
 * @return string
 */
function lithia_get_record_key_meta_key(): string {
	return '_lithia_record_key';
}

/**
 * Return the meta key used for stable asset keys.
 *
 * @return string
 */
function lithia_get_asset_key_meta_key(): string {
	return '_lithia_asset_key';
}

/**
 * Normalize a scalar sync value.
 *
 * @param mixed $value Raw value.
 * @return string
 */
function lithia_seed_normalize_scalar( $value ): string {
	if ( is_array( $value ) || is_object( $value ) ) {
		return '';
	}

	return trim( wp_unslash( (string) $value ) );
}

/**
 * Normalize a record key.
 *
 * @param mixed $value Raw key value.
 * @return string
 */
function lithia_seed_normalize_record_key( $value ): string {
	return sanitize_key( lithia_seed_normalize_scalar( $value ) );
}

/**
 * Normalize a yes/no style flag.
 *
 * @param mixed $value Raw flag value.
 * @return string
 */
function lithia_seed_normalize_flag( $value ): string {
	$value = strtolower( lithia_seed_normalize_scalar( $value ) );

	return in_array( $value, array( '1', 'true', 'yes', 'on' ), true ) ? 'yes' : 'no';
}

/**
 * Extract a single row from a payload scope.
 *
 * @param array  $payload Full payload.
 * @param string $scope   Scope name.
 * @return array
 */
function lithia_seed_extract_scope_row( array $payload, string $scope ): array {
	$row = $payload[ $scope ] ?? array();

	if ( ! is_array( $row ) ) {
		return array();
	}

	if ( array_is_list( $row ) ) {
		foreach ( $row as $candidate ) {
			if ( is_array( $candidate ) ) {
				return $candidate;
			}
		}

		return array();
	}

	return $row;
}

/**
 * Capture changes between two arrays.
 *
 * @param array $before Existing values.
 * @param array $after  Proposed values.
 * @return array
 */
function lithia_seed_capture_changes( array $before, array $after ): array {
	$changes = array();
	$keys    = array_unique( array_merge( array_keys( $before ), array_keys( $after ) ) );

	foreach ( $keys as $key ) {
		$before_value = $before[ $key ] ?? null;
		$after_value  = $after[ $key ] ?? null;

		if ( $before_value === $after_value ) {
			continue;
		}

		$changes[ $key ] = array(
			'from' => $before_value,
			'to'   => $after_value,
		);
	}

	return $changes;
}

/**
 * Return the supported site-settings columns for phase 1.
 *
 * @return array
 */
function lithia_get_seed_site_settings_fields(): array {
	return array(
		'sheet_version'                => 'text',
		'site_key'                     => 'record_key',
		'site_name'                    => 'text',
		'site_tagline'                 => 'text',
		'primary_domain'               => 'text',
		'local_domain'                 => 'text',
		'locale'                       => 'text',
		'timezone'                     => 'text',
		'admin_email'                  => 'email',
		'homepage_page_key'            => 'record_key',
		'about_page_key'               => 'record_key',
		'contact_page_key'             => 'record_key',
		'booking_page_key'             => 'record_key',
		'posts_page_key'               => 'record_key',
		'default_social_image_asset_key' => 'record_key',
		'booking_enabled'              => 'flag',
		'commerce_enabled'             => 'flag',
		'provider_pages_enabled'       => 'flag',
		'style_preset_key'             => 'record_key',
	);
}

/**
 * Sanitize a site-settings row.
 *
 * @param array $row Raw row data.
 * @return array
 */
function lithia_seed_sanitize_site_settings_row( array $row ): array {
	$sanitized = array();

	foreach ( lithia_get_seed_site_settings_fields() as $key => $type ) {
		if ( ! array_key_exists( $key, $row ) ) {
			continue;
		}

		switch ( $type ) {
			case 'email':
				$sanitized[ $key ] = sanitize_email( lithia_seed_normalize_scalar( $row[ $key ] ) );
				break;
			case 'flag':
				$sanitized[ $key ] = lithia_seed_normalize_flag( $row[ $key ] );
				break;
			case 'record_key':
				$sanitized[ $key ] = lithia_seed_normalize_record_key( $row[ $key ] );
				break;
			default:
				$sanitized[ $key ] = sanitize_text_field( lithia_seed_normalize_scalar( $row[ $key ] ) );
				break;
		}
	}

	return $sanitized;
}

/**
 * Get a post ID by stable record key.
 *
 * @param string $record_key Record key.
 * @param string $post_type  Optional post type constraint.
 * @return int
 */
function lithia_get_post_id_by_record_key( string $record_key, string $post_type = '' ): int {
	$record_key = lithia_seed_normalize_record_key( $record_key );

	if ( '' === $record_key ) {
		return 0;
	}

	$post_ids = get_posts(
		array(
			'post_type'      => $post_type ?: 'any',
			'post_status'    => array( 'publish', 'draft', 'private', 'pending', 'future' ),
			'posts_per_page' => 1,
			'meta_key'       => lithia_get_record_key_meta_key(),
			'meta_value'     => $record_key,
			'fields'         => 'ids',
			'orderby'        => 'ID',
			'order'          => 'ASC',
		)
	);

	return ! empty( $post_ids[0] ) ? (int) $post_ids[0] : 0;
}

/**
 * Return the saved record key for a post.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function lithia_get_post_record_key( int $post_id ): string {
	return lithia_seed_normalize_record_key( get_post_meta( $post_id, lithia_get_record_key_meta_key(), true ) );
}

/**
 * Assign a stable record key to a post.
 *
 * @param int    $post_id     Post ID.
 * @param string $record_key  Record key.
 * @return bool
 */
function lithia_assign_post_record_key( int $post_id, string $record_key ): bool {
	$record_key = lithia_seed_normalize_record_key( $record_key );

	if ( $post_id <= 0 || '' === $record_key ) {
		return false;
	}

	return false !== update_post_meta( $post_id, lithia_get_record_key_meta_key(), $record_key );
}

/**
 * Get an attachment ID by stable asset key.
 *
 * @param string $asset_key Asset key.
 * @return int
 */
function lithia_get_attachment_id_by_asset_key( string $asset_key ): int {
	$asset_key = lithia_seed_normalize_record_key( $asset_key );

	if ( '' === $asset_key ) {
		return 0;
	}

	$post_ids = get_posts(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => 1,
			'meta_key'       => lithia_get_asset_key_meta_key(),
			'meta_value'     => $asset_key,
			'fields'         => 'ids',
			'orderby'        => 'ID',
			'order'          => 'ASC',
		)
	);

	return ! empty( $post_ids[0] ) ? (int) $post_ids[0] : 0;
}

/**
 * Assign a stable asset key to an attachment.
 *
 * @param int    $attachment_id Attachment ID.
 * @param string $asset_key     Asset key.
 * @return bool
 */
function lithia_assign_attachment_asset_key( int $attachment_id, string $asset_key ): bool {
	$asset_key = lithia_seed_normalize_record_key( $asset_key );

	if ( $attachment_id <= 0 || '' === $asset_key ) {
		return false;
	}

	return false !== update_post_meta( $attachment_id, lithia_get_asset_key_meta_key(), $asset_key );
}

/**
 * Sync the phase-1 site settings scope.
 *
 * @param array $row     Raw row data.
 * @param bool  $dry_run Whether this is a dry run.
 * @return array
 */
function lithia_seed_sync_site_settings_scope( array $row, bool $dry_run = false ): array {
	$before_raw  = get_option( 'lithia_seed_site_settings', array() );
	$before_raw  = is_array( $before_raw ) ? $before_raw : array();
	$sanitized   = lithia_seed_sanitize_site_settings_row( $row );
	$after_raw   = array_merge( $before_raw, $sanitized );
	$raw_changes = lithia_seed_capture_changes( $before_raw, $after_raw );

	$core_map = array(
		'site_name'    => 'blogname',
		'site_tagline' => 'blogdescription',
		'admin_email'  => 'admin_email',
		'locale'       => 'WPLANG',
		'timezone'     => 'timezone_string',
	);

	$core_before  = array();
	$core_after   = array();
	$core_changes = array();

	foreach ( $core_map as $source_key => $option_name ) {
		if ( ! array_key_exists( $source_key, $sanitized ) ) {
			continue;
		}

		if ( 'admin_email' === $option_name && '' === $sanitized[ $source_key ] ) {
			continue;
		}

		$core_before[ $option_name ] = get_option( $option_name, '' );
		$core_after[ $option_name ]  = $sanitized[ $source_key ];
	}

	$core_changes = lithia_seed_capture_changes( $core_before, $core_after );

	if ( ! $dry_run ) {
		update_option( 'lithia_seed_site_settings', $after_raw );

		foreach ( $core_after as $option_name => $value ) {
			update_option( $option_name, $value );
		}
	}

	$changes = array(
		'stored_settings' => $raw_changes,
		'wp_options'      => $core_changes,
	);

	return array(
		'scope'        => 'site_settings',
		'status'       => empty( $raw_changes ) && empty( $core_changes ) ? 'noop' : ( $dry_run ? 'dry-run' : 'updated' ),
		'changes'      => array_filter( $changes ),
		'applied_keys' => array_keys( $sanitized ),
	);
}

/**
 * Sync the phase-1 business details scope.
 *
 * @param array $row     Raw row data.
 * @param bool  $dry_run Whether this is a dry run.
 * @return array
 */
function lithia_seed_sync_business_details_scope( array $row, bool $dry_run = false ): array {
	$before  = get_option( 'business-details', array() );
	$before  = is_array( $before ) ? $before : array();
	$after   = lithia_prepare_options_page_values( 'business-details', $row, true );
	$changes = lithia_seed_capture_changes( $before, $after );

	if ( ! $dry_run ) {
		lithia_update_business_details( $row, true );
	}

	return array(
		'scope'        => 'business_details',
		'status'       => empty( $changes ) ? 'noop' : ( $dry_run ? 'dry-run' : 'updated' ),
		'changes'      => $changes,
		'applied_keys' => array_keys( $after ),
	);
}

/**
 * Sync the phase-1 brand content scope.
 *
 * @param array $row     Raw row data.
 * @param bool  $dry_run Whether this is a dry run.
 * @return array
 */
function lithia_seed_sync_brand_content_scope( array $row, bool $dry_run = false ): array {
	$before  = get_option( 'brand-content', array() );
	$before  = is_array( $before ) ? $before : array();
	$after   = lithia_prepare_options_page_values( 'brand-content', $row, true );
	$changes = lithia_seed_capture_changes( $before, $after );

	if ( ! $dry_run ) {
		lithia_update_brand_content( $row, true );
	}

	return array(
		'scope'        => 'brand_content',
		'status'       => empty( $changes ) ? 'noop' : ( $dry_run ? 'dry-run' : 'updated' ),
		'changes'      => $changes,
		'applied_keys' => array_keys( $after ),
	);
}

/**
 * Sync the phase-1 site styles scope.
 *
 * @param array $row     Raw row data.
 * @param bool  $dry_run Whether this is a dry run.
 * @return array
 */
function lithia_seed_sync_site_styles_scope( array $row, bool $dry_run = false ): array {
	$before  = lithia_get_site_styles();
	$after   = lithia_sanitize_site_styles( array_merge( $before, $row ) );
	$changes = lithia_seed_capture_changes( $before, $after );

	if ( ! $dry_run ) {
		lithia_update_site_styles( $row, true );
	}

	return array(
		'scope'        => 'site_styles',
		'status'       => empty( $changes ) ? 'noop' : ( $dry_run ? 'dry-run' : 'updated' ),
		'changes'      => $changes,
		'applied_keys' => array_keys( $after ),
	);
}

/**
 * Store the last sync report.
 *
 * @param array $report Sync report.
 * @return void
 */
function lithia_seed_store_sync_report( array $report ): void {
	update_option( 'lithia_seed_sync_last_result', $report, false );
	update_option( 'lithia_seed_sync_last_run', $report['ran_at'] ?? current_time( 'mysql' ), false );

	$log = get_option( 'lithia_seed_sync_log', array() );

	if ( ! is_array( $log ) ) {
		$log = array();
	}

	array_unshift( $log, $report );
	$log = array_slice( $log, 0, 20 );

	update_option( 'lithia_seed_sync_log', $log, false );
}

/**
 * Sync the phase-1 one-row scopes.
 *
 * @param array $payload Full payload.
 * @param array $args    Sync args.
 * @return array
 */
function lithia_seed_sync( array $payload, array $args = array() ): array {
	$dry_run       = ! empty( $args['dry_run'] );
	$requested     = sanitize_key( (string) ( $args['scope'] ?? '' ) );
	$supported     = lithia_get_seed_sync_supported_scopes();
	$available     = $requested ? array_intersect( $supported, array( $requested ) ) : $supported;
	$available     = array_values( $available );
	$report        = array(
		'success' => true,
		'dry_run' => $dry_run,
		'scope'   => $requested ?: 'all',
		'ran_at'  => current_time( 'mysql' ),
		'results' => array(),
		'summary' => array(
			'updated' => 0,
			'noop'    => 0,
			'skipped' => 0,
		),
	);

	if ( $requested && empty( $available ) ) {
		return array(
			'success' => false,
			'error'   => sprintf( 'Unsupported sync scope: %s', $requested ),
		);
	}

	if ( ! $dry_run ) {
		lithia_ensure_theme_options_pages();
	}

	foreach ( $available as $scope ) {
		$row = lithia_seed_extract_scope_row( $payload, $scope );

		if ( empty( $row ) ) {
			$report['results'][ $scope ] = array(
				'scope'   => $scope,
				'status'  => 'skipped',
				'message' => 'No row payload was provided for this scope.',
				'changes' => array(),
			);
			$report['summary']['skipped']++;
			continue;
		}

		switch ( $scope ) {
			case 'site_settings':
				$result = lithia_seed_sync_site_settings_scope( $row, $dry_run );
				break;
			case 'business_details':
				$result = lithia_seed_sync_business_details_scope( $row, $dry_run );
				break;
			case 'brand_content':
				$result = lithia_seed_sync_brand_content_scope( $row, $dry_run );
				break;
			case 'site_styles':
				$result = lithia_seed_sync_site_styles_scope( $row, $dry_run );
				break;
			default:
				$result = array(
					'scope'   => $scope,
					'status'  => 'skipped',
					'message' => 'This scope is not implemented yet.',
					'changes' => array(),
				);
				break;
		}

		$report['results'][ $scope ] = $result;
		$status                      = $result['status'] ?? 'skipped';

		if ( isset( $report['summary'][ $status ] ) ) {
			$report['summary'][ $status ]++;
		}
	}

	if ( ! $dry_run ) {
		lithia_seed_store_sync_report( $report );
	}

	return $report;
}

/**
 * Load a sync payload from a JSON file.
 *
 * @param string $file_path JSON file path.
 * @return array|WP_Error
 */
function lithia_seed_load_payload_from_json_file( string $file_path ) {
	$file_path = trim( $file_path );

	if ( '' === $file_path ) {
		return new WP_Error( 'lithia_seed_sync_missing_path', 'Missing JSON file path.' );
	}

	if ( ! file_exists( $file_path ) || ! is_readable( $file_path ) ) {
		return new WP_Error( 'lithia_seed_sync_missing_file', sprintf( 'JSON file not found or unreadable: %s', $file_path ) );
	}

	$contents = file_get_contents( $file_path );

	if ( false === $contents ) {
		return new WP_Error( 'lithia_seed_sync_read_failed', sprintf( 'Unable to read JSON file: %s', $file_path ) );
	}

	$payload = json_decode( $contents, true );

	if ( ! is_array( $payload ) ) {
		return new WP_Error( 'lithia_seed_sync_invalid_json', sprintf( 'Invalid JSON payload in: %s', $file_path ) );
	}

	return $payload;
}

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	/**
	 * WP-CLI helpers for the Lithia seed sync foundation.
	 */
	class Lithia_Seed_Sync_Command {
		/**
		 * Sync one-row seed scopes from a JSON payload file.
		 *
		 * ## OPTIONS
		 *
		 * --file=<path>
		 * : Path to the JSON payload file.
		 *
		 * [--scope=<scope>]
		 * : Limit the sync to one scope: site_settings, business_details, brand_content, site_styles.
		 *
		 * [--dry-run]
		 * : Return the proposed changes without saving them.
		 *
		 * ## EXAMPLES
		 *
		 *     wp lithia seed-sync --file=/path/to/payload.json --dry-run
		 *     wp lithia seed-sync --file=/path/to/payload.json --scope=site_styles
		 *
		 * @param array $args       Positional args.
		 * @param array $assoc_args Associative args.
		 * @return void
		 */
		public function __invoke( array $args, array $assoc_args ): void {
			$payload = lithia_seed_load_payload_from_json_file(
				(string) ( $assoc_args['file'] ?? $assoc_args['json'] ?? '' )
			);

			if ( is_wp_error( $payload ) ) {
				WP_CLI::error( $payload->get_error_message() );
			}

			$report = lithia_seed_sync(
				$payload,
				array(
					'scope'   => $assoc_args['scope'] ?? '',
					'dry_run' => \WP_CLI\Utils\get_flag_value( $assoc_args, 'dry-run', false ),
				)
			);

			if ( empty( $report['success'] ) ) {
				WP_CLI::error( $report['error'] ?? 'Seed sync failed.' );
			}

			WP_CLI::line( wp_json_encode( $report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
			WP_CLI::success( empty( $assoc_args['dry-run'] ) ? 'Seed sync completed.' : 'Dry run completed.' );
		}
	}

	WP_CLI::add_command( 'lithia seed-sync', 'Lithia_Seed_Sync_Command' );
}
