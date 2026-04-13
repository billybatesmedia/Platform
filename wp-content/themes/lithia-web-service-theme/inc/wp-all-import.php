<?php
/**
 * WP All Import helper functions for the Services CPT.
 *
 * @package LithiaWebServiceTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Normalize a string value received from WP All Import.
 *
 * @param mixed $value Raw value.
 * @return string
 */
function lithia_wpai_normalize_value( $value ): string {
	if ( is_array( $value ) || is_object( $value ) ) {
		return '';
	}

	return trim( wp_unslash( (string) $value ) );
}

/**
 * Convert an attachment URL into a WordPress attachment ID.
 *
 * Accepts either a URL or a numeric ID string. Returns an empty string when
 * nothing can be resolved so WP All Import saves a blank custom field.
 *
 * @param mixed $value Attachment URL or ID.
 * @return string
 */
function lithia_wpai_attachment_id_from_url( $value ): string {
	global $wpdb;

	$value = lithia_wpai_normalize_value( $value );

	if ( '' === $value ) {
		return '';
	}

	if ( is_numeric( $value ) ) {
		return (string) absint( $value );
	}

	$attachment_id = attachment_url_to_postid( $value );

	if ( $attachment_id ) {
		return (string) $attachment_id;
	}

	$uploads = wp_get_upload_dir();
	$path    = wp_parse_url( $value, PHP_URL_PATH );

	if ( ! is_string( $path ) || '' === $path ) {
		return '';
	}

	$relative_path = '';

	if ( ! empty( $uploads['baseurl'] ) && str_starts_with( $value, $uploads['baseurl'] ) ) {
		$relative_path = ltrim( str_replace( trailingslashit( $uploads['baseurl'] ), '', $value ), '/' );
	} elseif ( str_contains( $path, '/uploads/' ) ) {
		$relative_path = ltrim( substr( $path, strpos( $path, '/uploads/' ) + 9 ), '/' );
	}

	if ( '' !== $relative_path ) {
		$attachment_id = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT post_id
				FROM {$wpdb->postmeta}
				WHERE meta_key = '_wp_attached_file'
				AND meta_value = %s
				LIMIT 1",
				$relative_path
			)
		);

		if ( $attachment_id ) {
			return (string) $attachment_id;
		}

		$attachment_id = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT post_id
				FROM {$wpdb->postmeta}
				WHERE meta_key = '_wp_attached_file'
				AND meta_value LIKE %s
				ORDER BY post_id DESC
				LIMIT 1",
				'%' . $wpdb->esc_like( basename( $relative_path ) )
			)
		);

		if ( $attachment_id ) {
			return (string) $attachment_id;
		}
	}

	return '';
}

/**
 * Build a serialized JetEngine repeater payload from title/text pairs.
 *
 * @param string $title_key Repeater title key.
 * @param string $text_key  Repeater text key.
 * @param array  $values    Alternating title/text values.
 * @return string
 */
function lithia_wpai_build_repeater_payload( string $title_key, string $text_key, array $values ): string {
	$rows = array();

	for ( $index = 0; $index < count( $values ); $index += 2 ) {
		$title = lithia_wpai_normalize_value( $values[ $index ] ?? '' );
		$text  = lithia_wpai_normalize_value( $values[ $index + 1 ] ?? '' );

		if ( '' === $title && '' === $text ) {
			continue;
		}

		$rows[ 'item-' . count( $rows ) ] = array(
			$title_key => $title,
			$text_key  => $text,
		);
	}

	return maybe_serialize( $rows );
}

/**
 * Build the Services highlights repeater payload for WP All Import.
 *
 * @param mixed ...$values Alternating highlight title/text values.
 * @return string
 */
function lithia_wpai_service_highlights( ...$values ): string {
	return lithia_wpai_build_repeater_payload( 'item_title', 'item_text', $values );
}

/**
 * Build the Services process repeater payload for WP All Import.
 *
 * @param mixed ...$values Alternating step title/text values.
 * @return string
 */
function lithia_wpai_service_process_steps( ...$values ): string {
	return lithia_wpai_build_repeater_payload( 'step_title', 'step_text', $values );
}

/**
 * Build Rank Math robots meta from a comma/pipe/newline separated string.
 *
 * @param mixed $value Raw robots value.
 * @return string
 */
function lithia_wpai_rank_math_robots( $value = 'index,follow' ): string {
	$value = lithia_wpai_normalize_value( $value );

	if ( '' === $value ) {
		$value = 'index,follow';
	}

	$tokens = preg_split( '/[\s,\|]+/', $value );
	$tokens = array_filter(
		array_map( 'sanitize_key', (array) $tokens )
	);

	if ( empty( $tokens ) ) {
		$tokens = array( 'index', 'follow' );
	}

	return maybe_serialize( array_values( array_unique( $tokens ) ) );
}
