<?php
/**
 * Canonical project importer for Lithia template payloads.
 *
 * @package LithiaWebServiceTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return the current project payload schema version.
 *
 * @return string
 */
function lithia_get_project_import_schema_version(): string {
	return '1.0.0';
}

/**
 * Return the default template key for V1.
 *
 * @return string
 */
function lithia_get_project_import_default_template_key(): string {
	return 'service-business-v1';
}

/**
 * Return the ordered list of supported review states.
 *
 * @return array
 */
function lithia_get_project_review_states(): array {
	return array(
		'intake',
		'approved',
		'imported',
		'qa',
		'launched',
	);
}

/**
 * Normalize a review state.
 *
 * @param mixed $state Raw state.
 * @return string
 */
function lithia_normalize_project_review_state( $state ): string {
	$state = sanitize_key( lithia_seed_normalize_scalar( $state ) );

	return in_array( $state, lithia_get_project_review_states(), true ) ? $state : 'intake';
}

/**
 * Return the rank of a review state.
 *
 * @param string $state Review state.
 * @return int
 */
function lithia_get_project_review_state_rank( string $state ): int {
	$index = array_search( lithia_normalize_project_review_state( $state ), lithia_get_project_review_states(), true );

	return false === $index ? 0 : (int) $index;
}

/**
 * Return the option name used for project context.
 *
 * @return string
 */
function lithia_get_project_context_option_name(): string {
	return 'lithia_project_context';
}

/**
 * Return default project context values.
 *
 * @return array
 */
function lithia_get_project_context_defaults(): array {
	return array(
		'schema_version'    => lithia_get_project_import_schema_version(),
		'template_key'      => lithia_get_project_import_default_template_key(),
		'industry'          => '',
		'site_key'          => '',
		'review_state'      => 'intake',
		'source_review_state' => 'intake',
		'last_import_source' => '',
		'last_imported_at'  => '',
		'payload_hash'      => '',
	);
}

/**
 * Return the saved project context.
 *
 * @return array
 */
function lithia_get_project_context(): array {
	$saved = get_option( lithia_get_project_context_option_name(), array() );
	$saved = is_array( $saved ) ? $saved : array();

	return wp_parse_args( $saved, lithia_get_project_context_defaults() );
}

/**
 * Update and return the project context.
 *
 * @param array $values         Context values.
 * @param bool  $merge_existing Whether to merge with existing values.
 * @return array
 */
function lithia_update_project_context( array $values, bool $merge_existing = true ): array {
	$base      = $merge_existing ? lithia_get_project_context() : lithia_get_project_context_defaults();
	$sanitized = array();

	foreach ( lithia_get_project_context_defaults() as $key => $default_value ) {
		if ( ! array_key_exists( $key, $values ) ) {
			continue;
		}

		switch ( $key ) {
			case 'site_key':
				$sanitized[ $key ] = lithia_seed_normalize_record_key( $values[ $key ] );
				break;
			case 'review_state':
			case 'source_review_state':
				$sanitized[ $key ] = lithia_normalize_project_review_state( $values[ $key ] );
				break;
			case 'template_key':
				$sanitized[ $key ] = sanitize_title( lithia_seed_normalize_scalar( $values[ $key ] ) );
				break;
			case 'schema_version':
			case 'industry':
			case 'last_import_source':
			case 'last_imported_at':
			case 'payload_hash':
			default:
				$sanitized[ $key ] = sanitize_text_field( lithia_seed_normalize_scalar( $values[ $key ] ) );
				break;
		}
	}

	$updated = array_merge( $base, $sanitized );
	update_option( lithia_get_project_context_option_name(), $updated, false );

	return $updated;
}

/**
 * Set and return the current site review state.
 *
 * @param string $state Review state.
 * @return string
 */
function lithia_set_project_review_state( string $state ): string {
	$state = lithia_normalize_project_review_state( $state );
	lithia_update_project_context(
		array(
			'review_state' => $state,
		)
	);

	return $state;
}

/**
 * Return whether two values are equivalent for import tracking.
 *
 * @param mixed $left  Left value.
 * @param mixed $right Right value.
 * @return bool
 */
function lithia_project_import_values_equal( $left, $right ): bool {
	return wp_json_encode( $left ) === wp_json_encode( $right );
}

/**
 * Return whether a value should be treated as empty for import purposes.
 *
 * @param mixed $value Value to inspect.
 * @return bool
 */
function lithia_project_import_is_empty_value( $value ): bool {
	if ( is_string( $value ) ) {
		return '' === trim( $value );
	}

	return empty( $value );
}

/**
 * Return the saved managed import snapshot for a post.
 *
 * @param int $post_id Post ID.
 * @return array
 */
function lithia_project_import_get_post_snapshot( int $post_id ): array {
	$snapshot = get_post_meta( $post_id, '_lithia_import_snapshot', true );

	return is_array( $snapshot ) ? $snapshot : array();
}

/**
 * Merge new snapshot values into the stored post snapshot.
 *
 * @param int   $post_id  Post ID.
 * @param array $updates  Snapshot updates.
 * @return void
 */
function lithia_project_import_update_post_snapshot( int $post_id, array $updates ): void {
	$snapshot = lithia_project_import_get_post_snapshot( $post_id );

	foreach ( $updates as $key => $value ) {
		$snapshot[ $key ] = $value;
	}

	update_post_meta( $post_id, '_lithia_import_snapshot', $snapshot );
}

/**
 * Return whether a post is locked from importer writes.
 *
 * @param int $post_id Post ID.
 * @return bool
 */
function lithia_project_import_is_post_locked( int $post_id ): bool {
	return 'yes' === lithia_seed_normalize_flag( get_post_meta( $post_id, '_lithia_sync_lock', true ) );
}

/**
 * Return whether a managed value may be overwritten.
 *
 * @param int    $post_id       Post ID.
 * @param string $snapshot_key  Snapshot key.
 * @param mixed  $current_value Current value.
 * @param bool   $force         Whether to force writes.
 * @return bool
 */
function lithia_project_import_can_write_managed_value( int $post_id, string $snapshot_key, $current_value, bool $force = false ): bool {
	if ( $force || $post_id <= 0 ) {
		return true;
	}

	if ( lithia_project_import_is_post_locked( $post_id ) ) {
		return false;
	}

	if ( lithia_project_import_is_empty_value( $current_value ) ) {
		return true;
	}

	$snapshot = lithia_project_import_get_post_snapshot( $post_id );

	if ( ! array_key_exists( $snapshot_key, $snapshot ) ) {
		return false;
	}

	return lithia_project_import_values_equal( $snapshot[ $snapshot_key ], $current_value );
}

/**
 * Join labels into a readable phrase.
 *
 * @param array $labels Labels.
 * @return string
 */
function lithia_project_import_join_labels( array $labels ): string {
	$labels = array_values(
		array_filter(
			array_map(
				static function ( $value ): string {
					return sanitize_text_field( lithia_seed_normalize_scalar( $value ) );
				},
				$labels
			)
		)
	);

	$count = count( $labels );

	if ( 0 === $count ) {
		return '';
	}

	if ( 1 === $count ) {
		return $labels[0];
	}

	if ( 2 === $count ) {
		return $labels[0] . ' and ' . $labels[1];
	}

	$last = array_pop( $labels );

	return implode( ', ', $labels ) . ', and ' . $last;
}

/**
 * Normalize a slug list from string or array input.
 *
 * @param mixed $value Raw value.
 * @return array
 */
function lithia_project_import_normalize_slug_list( $value ): array {
	if ( is_array( $value ) ) {
		$parts = $value;
	} else {
		$raw   = lithia_seed_normalize_scalar( $value );
		$parts = '' === $raw ? array() : preg_split( '/[\s,\|\n\r]+/', $raw );
	}

	$parts = array_filter( array_map( 'sanitize_title', (array) $parts ) );

	return array_values( array_unique( $parts ) );
}

/**
 * Normalize a human-readable label list from string or array input.
 *
 * @param mixed $value Raw value.
 * @return array
 */
function lithia_project_import_normalize_label_list( $value ): array {
	if ( is_array( $value ) ) {
		$parts = $value;
	} else {
		$raw   = lithia_seed_normalize_scalar( $value );
		$parts = '' === $raw ? array() : preg_split( '/[\n\r\|,]+/', $raw );
	}

	$labels = array_values(
		array_filter(
			array_map(
				static function ( $item ): string {
					return sanitize_text_field( lithia_seed_normalize_scalar( $item ) );
				},
				(array) $parts
			)
		)
	);

	return array_values( array_unique( $labels ) );
}

/**
 * Normalize FAQ rows into a stable question/answer format.
 *
 * @param mixed $rows Raw rows.
 * @return array
 */
function lithia_project_import_normalize_faq_rows( $rows ): array {
	if ( ! is_array( $rows ) ) {
		return array();
	}

	$normalized = array();

	foreach ( $rows as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}

		$question = sanitize_text_field( lithia_seed_normalize_scalar( $row['question'] ?? '' ) );
		$answer   = sanitize_textarea_field( lithia_seed_normalize_scalar( $row['answer_seed'] ?? $row['answer'] ?? '' ) );
		$scope    = sanitize_key( lithia_seed_normalize_scalar( $row['page_scope'] ?? '' ) );

		if ( '' === $question && '' === $answer ) {
			continue;
		}

		$normalized[] = array(
			'question'    => $question,
			'answer_seed' => $answer,
			'page_scope'  => $scope,
		);
	}

	return $normalized;
}

/**
 * Normalize testimonial rows into a stable format.
 *
 * @param mixed $rows Raw rows.
 * @return array
 */
function lithia_project_import_normalize_testimonial_rows( $rows ): array {
	if ( ! is_array( $rows ) ) {
		return array();
	}

	$normalized = array();

	foreach ( $rows as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}

		$name     = sanitize_text_field( lithia_seed_normalize_scalar( $row['name'] ?? '' ) );
		$quote    = sanitize_textarea_field( lithia_seed_normalize_scalar( $row['quote'] ?? '' ) );
		$role     = sanitize_text_field( lithia_seed_normalize_scalar( $row['role'] ?? '' ) );
		$location = sanitize_text_field( lithia_seed_normalize_scalar( $row['location'] ?? '' ) );

		if ( '' === $name && '' === $quote && '' === $role && '' === $location ) {
			continue;
		}

		$normalized[] = array(
			'name'     => $name,
			'quote'    => $quote,
			'role'     => $role,
			'location' => $location,
		);
	}

	return $normalized;
}

/**
 * Normalize proof payload fields.
 *
 * @param mixed $proof Raw proof payload.
 * @return array
 */
function lithia_project_import_normalize_proof_payload( $proof ): array {
	$proof = is_array( $proof ) ? $proof : array();

	return array(
		'years_experience' => sanitize_text_field( lithia_seed_normalize_scalar( $proof['years_experience'] ?? '' ) ),
		'credentials'      => lithia_project_import_normalize_label_list( $proof['credentials'] ?? array() ),
		'highlights'       => lithia_project_import_normalize_label_list( $proof['highlights'] ?? array() ),
		'awards'           => lithia_project_import_normalize_label_list( $proof['awards'] ?? array() ),
		'testimonials'     => lithia_project_import_normalize_testimonial_rows( $proof['testimonials'] ?? array() ),
	);
}

/**
 * Normalize repeater rows into a stable title/text format.
 *
 * @param mixed  $rows     Raw rows.
 * @param string $title_key Title key.
 * @param string $text_key  Text key.
 * @return array
 */
function lithia_project_import_normalize_repeater_rows( $rows, string $title_key, string $text_key ): array {
	if ( ! is_array( $rows ) ) {
		return array();
	}

	$normalized = array();

	foreach ( $rows as $row ) {
		$title = '';
		$text  = '';

		if ( is_string( $row ) || is_numeric( $row ) ) {
			$title = sanitize_text_field( lithia_seed_normalize_scalar( $row ) );
		} elseif ( is_array( $row ) ) {
			$title = sanitize_text_field(
				lithia_seed_normalize_scalar(
					$row[ $title_key ] ?? $row['title'] ?? $row['label'] ?? ''
				)
			);
			$text  = sanitize_textarea_field(
				lithia_seed_normalize_scalar(
					$row[ $text_key ] ?? $row['text'] ?? $row['description'] ?? ''
				)
			);
		}

		if ( '' === $title && '' === $text ) {
			continue;
		}

		$normalized[] = array(
			$title_key => $title,
			$text_key  => $text,
		);
	}

	return $normalized;
}

/**
 * Build a simple paragraph block from plain text.
 *
 * @param string $text Paragraph text.
 * @return string
 */
function lithia_project_import_build_paragraph_content( string $text ): string {
	$text = trim( $text );

	if ( '' === $text ) {
		return '';
	}

	return sprintf(
		"<!-- wp:paragraph -->\n<p>%s</p>\n<!-- /wp:paragraph -->",
		esc_html( $text )
	);
}

/**
 * Resolve an attachment reference to an attachment ID.
 *
 * @param mixed $reference Attachment reference.
 * @return int
 */
function lithia_project_import_resolve_attachment_id( $reference ): int {
	$attachment_id = 0;
	$asset_key     = '';
	$url           = '';

	if ( is_numeric( $reference ) ) {
		return absint( $reference );
	}

	if ( is_string( $reference ) ) {
		$value = lithia_seed_normalize_scalar( $reference );

		if ( filter_var( $value, FILTER_VALIDATE_URL ) ) {
			$url = $value;
		} else {
			$asset_key = $value;
		}
	} elseif ( is_array( $reference ) ) {
		$attachment_id = absint( $reference['id'] ?? 0 );
		$asset_key     = lithia_seed_normalize_record_key( $reference['asset_key'] ?? '' );
		$url           = esc_url_raw( (string) ( $reference['url'] ?? '' ) );
	}

	if ( $attachment_id ) {
		return $attachment_id;
	}

	if ( $asset_key ) {
		$attachment_id = lithia_get_attachment_id_by_asset_key( $asset_key );

		if ( $attachment_id ) {
			return $attachment_id;
		}
	}

	if ( $url && function_exists( 'lithia_wpai_attachment_id_from_url' ) ) {
		return (int) lithia_wpai_attachment_id_from_url( $url );
	}

	return 0;
}

/**
 * Return the current payload brand name.
 *
 * @param array $payload Payload.
 * @return string
 */
function lithia_project_import_get_brand_name( array $payload ): string {
	return sanitize_text_field(
		lithia_seed_normalize_scalar(
			$payload['business']['brand_name']
			?? $payload['business_details']['business_name']
			?? $payload['site_settings']['site_name']
			?? get_option( 'blogname', '' )
		)
	);
}

/**
 * Return the primary payload city.
 *
 * @param array $payload Payload.
 * @return string
 */
function lithia_project_import_get_primary_city( array $payload ): string {
	return sanitize_text_field(
		lithia_seed_normalize_scalar(
			$payload['location']['city']
			?? $payload['business_details']['city']
			?? ''
		)
	);
}

/**
 * Return up to a given number of offer titles.
 *
 * @param array $payload Payload.
 * @param int   $limit   Max number of titles.
 * @return array
 */
function lithia_project_import_get_offer_titles( array $payload, int $limit = 3 ): array {
	$source = array();

	if ( ! empty( $payload['offers'] ) && is_array( $payload['offers'] ) ) {
		$source = $payload['offers'];
	} elseif ( ! empty( $payload['services'] ) && is_array( $payload['services'] ) ) {
		$source = $payload['services'];
	}

	$titles = array();

	foreach ( $source as $offer ) {
		if ( ! is_array( $offer ) ) {
			continue;
		}

		$title = sanitize_text_field(
			lithia_seed_normalize_scalar(
				$offer['title'] ?? $offer['post_title'] ?? ''
			)
		);

		if ( '' === $title ) {
			continue;
		}

		$titles[] = $title;

		if ( count( $titles ) >= $limit ) {
			break;
		}
	}

	return $titles;
}

/**
 * Generate brand-content values from canonical payload fields.
 *
 * @param array $payload Payload.
 * @return array
 */
function lithia_project_import_generate_brand_content( array $payload ): array {
	$brand_name    = lithia_project_import_get_brand_name( $payload );
	$city          = lithia_project_import_get_primary_city( $payload );
	$tagline       = sanitize_text_field( lithia_seed_normalize_scalar( $payload['business']['short_tagline'] ?? '' ) );
	$business_type = sanitize_text_field( lithia_seed_normalize_scalar( $payload['business']['business_type'] ?? '' ) );
	$audiences     = lithia_project_import_join_labels( (array) ( $payload['audience']['primary_audiences'] ?? array() ) );
	$offer_titles  = lithia_project_import_join_labels( lithia_project_import_get_offer_titles( $payload ) );
	$primary_cta   = sanitize_text_field( lithia_seed_normalize_scalar( $payload['business']['primary_cta_label'] ?? 'Book Appointment' ) );
	$primary_url   = esc_url_raw( (string) ( $payload['business']['primary_cta_target'] ?? '/book-appointment/' ) );
	$secondary_cta = sanitize_text_field( lithia_seed_normalize_scalar( $payload['business']['secondary_cta_label'] ?? 'Contact' ) );
	$secondary_url = esc_url_raw( (string) ( $payload['business']['secondary_cta_target'] ?? '/contact/' ) );

	$intro_heading = $tagline;

	if ( '' === $intro_heading ) {
		if ( $business_type && $city ) {
			$intro_heading = $business_type . ' in ' . $city;
		} elseif ( $business_type ) {
			$intro_heading = $business_type;
		} elseif ( $offer_titles ) {
			$intro_heading = $offer_titles;
		} else {
			$intro_heading = $brand_name;
		}
	}

	$intro_paragraph = '';

	if ( $brand_name && $offer_titles ) {
		$intro_paragraph = sprintf(
			'%1$s helps %2$s with %3$s%4$s.',
			$brand_name,
			$audiences ? $audiences : 'clients',
			$offer_titles,
			$city ? ' in ' . $city : ''
		);
	} elseif ( $brand_name && $tagline ) {
		$intro_paragraph = $brand_name . ' provides ' . strtolower( $tagline ) . '.';
	} elseif ( $brand_name && $business_type ) {
		$intro_paragraph = $brand_name . ' provides ' . strtolower( $business_type ) . '.';
	}

	$mission_statement = $brand_name;

	if ( $offer_titles ) {
		$mission_statement = sprintf(
			'%1$s provides %2$s with a focus on clear structure, practical SEO, and dependable long-term service.',
			$brand_name,
			strtolower( $offer_titles )
		);
	} elseif ( $tagline ) {
		$mission_statement = $brand_name . ' provides ' . strtolower( $tagline ) . '.';
	}

	$about_summary = $intro_paragraph ? $intro_paragraph : $mission_statement;

	return array(
		'intro_eyebrow'       => $brand_name,
		'intro_heading'       => $intro_heading,
		'intro_paragraph'     => $intro_paragraph,
		'mission_statement'   => $mission_statement,
		'about_summary'       => $about_summary,
		'primary_cta_label'   => $primary_cta,
		'primary_cta_url'     => $primary_url,
		'secondary_cta_label' => $secondary_cta,
		'secondary_cta_url'   => $secondary_url,
	);
}

/**
 * Return default page-role definitions.
 *
 * @return array
 */
function lithia_get_project_page_role_defaults(): array {
	return array(
		'home' => array(
			'title' => 'Home',
			'slug'  => 'home',
		),
		'about' => array(
			'title' => 'About',
			'slug'  => 'about',
		),
		'contact' => array(
			'title' => 'Contact',
			'slug'  => 'contact',
		),
		'booking' => array(
			'title' => 'Book Appointment',
			'slug'  => 'book-appointment',
		),
		'platform' => array(
			'title' => 'Platform',
			'slug'  => 'platform',
		),
		'posts' => array(
			'title' => 'Blog',
			'slug'  => 'blog',
		),
	);
}

/**
 * Return a page ID for a page role.
 *
 * @param string $page_role Page role.
 * @return int
 */
function lithia_project_import_get_page_id_by_role( string $page_role ): int {
	$page_role = sanitize_key( $page_role );

	if ( '' === $page_role ) {
		return 0;
	}

	$page_ids = get_posts(
		array(
			'post_type'      => 'page',
			'post_status'    => array( 'publish', 'draft', 'private' ),
			'posts_per_page' => 1,
			'meta_key'       => '_lithia_page_role',
			'meta_value'     => $page_role,
			'fields'         => 'ids',
			'orderby'        => 'ID',
			'order'          => 'ASC',
		)
	);

	if ( ! empty( $page_ids[0] ) ) {
		return (int) $page_ids[0];
	}

	switch ( $page_role ) {
		case 'home':
			return absint( get_option( 'page_on_front', 0 ) );
		case 'posts':
			return absint( get_option( 'page_for_posts', 0 ) );
	}

	$defaults = lithia_get_project_page_role_defaults();
	$slug     = $defaults[ $page_role ]['slug'] ?? '';

	if ( '' === $slug ) {
		return 0;
	}

	$existing = get_page_by_path( $slug, OBJECT, 'page' );

	return $existing instanceof WP_Post ? (int) $existing->ID : 0;
}

/**
 * Return default fallback page content for a page role.
 *
 * @param string $page_role Page role.
 * @param array  $payload   Payload.
 * @return string
 */
function lithia_project_import_get_default_page_content( string $page_role, array $payload ): string {
	switch ( $page_role ) {
		case 'about':
			return function_exists( 'lithia_get_default_about_page_content' )
				? lithia_get_default_about_page_content()
				: '';
		case 'contact':
			return function_exists( 'lithia_get_default_contact_page_content' )
				? lithia_get_default_contact_page_content()
				: '';
		case 'booking':
			return lithia_project_import_build_paragraph_content(
				sanitize_textarea_field(
					lithia_seed_normalize_scalar(
						$payload['booking']['booking_notice']
						?? $payload['business_details']['booking_notice']
						?? 'Use this page to request an appointment.'
					)
				)
			);
		default:
			return '';
	}
}

/**
 * Build default page payloads from canonical project data.
 *
 * @param array $payload Payload.
 * @return array
 */
function lithia_project_import_build_default_page_payloads( array $payload ): array {
	$brand_name   = lithia_project_import_get_brand_name( $payload );
	$city         = lithia_project_import_get_primary_city( $payload );
	$brand_copy   = lithia_project_import_generate_brand_content( $payload );
	$business     = $payload['business'] ?? array();
	$offer_titles = lithia_project_import_join_labels( lithia_project_import_get_offer_titles( $payload ) );
	$booking_note = sanitize_textarea_field(
		lithia_seed_normalize_scalar(
			$payload['booking']['booking_notice']
			?? $payload['business_details']['booking_notice']
			?? ''
		)
	);

	$home_title = $brand_name;

	if ( $offer_titles && $city ) {
		$home_title = sprintf( '%1$s | %2$s in %3$s', $brand_name, $offer_titles, $city );
	} elseif ( $offer_titles ) {
		$home_title = sprintf( '%1$s | %2$s', $brand_name, $offer_titles );
	}

	return array(
		array(
			'page_role' => 'home',
			'record_key' => 'page_home',
			'title'     => 'Home',
			'slug'      => 'home',
			'excerpt'   => $brand_copy['intro_paragraph'],
			'seo'       => array(
				'title'            => $home_title,
				'description'      => $brand_copy['intro_paragraph'],
				'focus_keyword'    => sanitize_text_field( lithia_seed_normalize_scalar( $payload['seo']['brand_keyword'] ?? $brand_name ) ),
				'facebook_title'   => $home_title,
				'facebook_description' => $brand_copy['intro_paragraph'],
				'twitter_title'    => $home_title,
				'twitter_description' => $brand_copy['intro_paragraph'],
			),
		),
		array(
			'page_role' => 'about',
			'record_key' => 'page_about',
			'title'     => 'About',
			'slug'      => 'about',
			'excerpt'   => $brand_copy['about_summary'],
			'content'   => '',
			'seo'       => array(
				'title'       => sprintf( 'About %s', $brand_name ),
				'description' => $brand_copy['about_summary'],
			),
		),
		array(
			'page_role' => 'contact',
			'record_key' => 'page_contact',
			'title'     => 'Contact',
			'slug'      => 'contact',
			'excerpt'   => $booking_note ? $booking_note : 'Reach out with questions, project details, or next steps.',
			'content'   => '',
			'seo'       => array(
				'title'       => sprintf( 'Contact %s', $brand_name ),
				'description' => $booking_note ? $booking_note : 'Contact ' . $brand_name . ' to discuss your project or next steps.',
			),
		),
		array(
			'page_role' => 'booking',
			'record_key' => 'page_booking',
			'title'     => 'Book Appointment',
			'slug'      => 'book-appointment',
			'excerpt'   => $booking_note,
			'content'   => '',
			'seo'       => array(
				'title'       => sprintf( 'Book Appointment | %s', $brand_name ),
				'description' => $booking_note ? $booking_note : 'Book an appointment with ' . $brand_name . '.',
			),
		),
		array(
			'page_role' => 'platform',
			'record_key' => 'page_platform',
			'title'     => 'Platform',
			'slug'      => 'platform',
			'excerpt'   => sanitize_textarea_field( lithia_seed_normalize_scalar( $business['short_tagline'] ?? '' ) ),
			'content'   => '',
		),
	);
}

/**
 * Merge generated default pages with explicit page payloads.
 *
 * @param array $defaults Generated defaults.
 * @param array $pages    Explicit pages.
 * @return array
 */
function lithia_project_import_merge_page_payloads( array $defaults, array $pages ): array {
	$merged = array();

	foreach ( $defaults as $page ) {
		if ( ! is_array( $page ) ) {
			continue;
		}

		$key            = sanitize_key( (string) ( $page['page_role'] ?? $page['record_key'] ?? $page['slug'] ?? '' ) );
		$merged[ $key ] = $page;
	}

	foreach ( $pages as $page ) {
		if ( ! is_array( $page ) ) {
			continue;
		}

		$key = sanitize_key( (string) ( $page['page_role'] ?? $page['record_key'] ?? $page['slug'] ?? wp_generate_uuid4() ) );

		if ( isset( $merged[ $key ] ) ) {
			$merged[ $key ] = array_replace_recursive( $merged[ $key ], $page );
		} else {
			$merged[ $key ] = $page;
		}
	}

	return array_values( $merged );
}

/**
 * Normalize a page payload.
 *
 * @param array $page    Raw page data.
 * @param array $payload Full payload.
 * @return array
 */
function lithia_project_import_normalize_page_payload( array $page, array $payload ): array {
	$page_role = sanitize_key( (string) ( $page['page_role'] ?? '' ) );
	$defaults  = lithia_get_project_page_role_defaults();
	$title     = sanitize_text_field( lithia_seed_normalize_scalar( $page['title'] ?? $page['post_title'] ?? ( $defaults[ $page_role ]['title'] ?? '' ) ) );
	$slug      = sanitize_title( lithia_seed_normalize_scalar( $page['slug'] ?? $page['post_name'] ?? ( $defaults[ $page_role ]['slug'] ?? '' ) ) );
	$excerpt   = sanitize_textarea_field( lithia_seed_normalize_scalar( $page['excerpt'] ?? $page['summary_seed'] ?? '' ) );
	$content   = (string) ( $page['content'] ?? $page['post_content'] ?? '' );
	$headline_seed = sanitize_text_field( lithia_seed_normalize_scalar( $page['headline_seed'] ?? $title ) );
	$summary_seed  = sanitize_textarea_field( lithia_seed_normalize_scalar( $page['summary_seed'] ?? $excerpt ) );
	$cta_label     = sanitize_text_field( lithia_seed_normalize_scalar( $page['cta_label'] ?? '' ) );
	$cta_target    = esc_url_raw( (string) ( $page['cta_target'] ?? '' ) );
	$seo_title_seed = sanitize_text_field( lithia_seed_normalize_scalar( $page['seo_title_seed'] ?? '' ) );
	$seo_description_seed = sanitize_textarea_field( lithia_seed_normalize_scalar( $page['seo_description_seed'] ?? '' ) );

	if ( '' === $content ) {
		$content = lithia_project_import_get_default_page_content( $page_role, $payload );
	}

	$seo = is_array( $page['seo'] ?? null ) ? $page['seo'] : array();

	return array(
		'page_role'     => $page_role,
		'record_key'    => lithia_seed_normalize_record_key( $page['record_key'] ?? 'page_' . sanitize_key( $slug ? $slug : $page_role ) ),
		'title'         => $title,
		'slug'          => $slug,
		'status'        => sanitize_key( (string) ( $page['status'] ?? $page['post_status'] ?? 'publish' ) ),
		'excerpt'       => $excerpt,
		'content'       => $content,
		'headline_seed' => $headline_seed,
		'summary_seed'  => $summary_seed,
		'cta_label'     => $cta_label,
		'cta_target'    => $cta_target,
		'seo_title_seed' => $seo_title_seed,
		'seo_description_seed' => $seo_description_seed,
		'featured_image' => $page['featured_image'] ?? $page['featured_image_url'] ?? $page['featured_image_asset_key'] ?? null,
		'seo'           => array(
			'title'                => sanitize_text_field( lithia_seed_normalize_scalar( $seo['title'] ?? $page['rank_math_title'] ?? $seo_title_seed ) ),
			'description'          => sanitize_textarea_field( lithia_seed_normalize_scalar( $seo['description'] ?? $page['rank_math_description'] ?? $seo_description_seed ) ),
			'focus_keyword'        => sanitize_text_field( lithia_seed_normalize_scalar( $seo['focus_keyword'] ?? $page['rank_math_focus_keyword'] ?? '' ) ),
			'facebook_title'       => sanitize_text_field( lithia_seed_normalize_scalar( $seo['facebook_title'] ?? $page['rank_math_facebook_title'] ?? '' ) ),
			'facebook_description' => sanitize_textarea_field( lithia_seed_normalize_scalar( $seo['facebook_description'] ?? $page['rank_math_facebook_description'] ?? '' ) ),
			'twitter_title'        => sanitize_text_field( lithia_seed_normalize_scalar( $seo['twitter_title'] ?? $page['rank_math_twitter_title'] ?? '' ) ),
			'twitter_description'  => sanitize_textarea_field( lithia_seed_normalize_scalar( $seo['twitter_description'] ?? $page['rank_math_twitter_description'] ?? '' ) ),
			'robots'               => sanitize_text_field( lithia_seed_normalize_scalar( $seo['robots'] ?? $page['rank_math_robots'] ?? '' ) ),
			'social_image'         => $seo['social_image'] ?? $page['seo_image'] ?? $page['seo_image_url'] ?? $page['seo_image_asset_key'] ?? null,
		),
	);
}

/**
 * Normalize a provider payload.
 *
 * @param array $provider Raw provider payload.
 * @return array
 */
function lithia_project_import_normalize_provider_payload( array $provider ): array {
	$title = sanitize_text_field( lithia_seed_normalize_scalar( $provider['title'] ?? $provider['post_title'] ?? '' ) );
	$slug  = sanitize_title( lithia_seed_normalize_scalar( $provider['slug'] ?? $provider['post_name'] ?? '' ) );

	if ( '' === $slug && '' !== $title ) {
		$slug = sanitize_title( $title );
	}

	return array(
		'record_key' => lithia_seed_normalize_record_key( $provider['record_key'] ?? 'provider_' . sanitize_key( $slug ) ),
		'title'      => $title,
		'slug'       => $slug,
		'status'     => sanitize_key( (string) ( $provider['status'] ?? $provider['post_status'] ?? 'publish' ) ),
		'excerpt'    => sanitize_textarea_field( lithia_seed_normalize_scalar( $provider['excerpt'] ?? $provider['summary'] ?? '' ) ),
		'content'    => (string) ( $provider['content'] ?? $provider['post_content'] ?? '' ),
		'email'      => sanitize_email( lithia_seed_normalize_scalar( $provider['email'] ?? '' ) ),
		'phone'      => sanitize_text_field( lithia_seed_normalize_scalar( $provider['phone'] ?? '' ) ),
	);
}

/**
 * Generate baseline SEO values for an offer.
 *
 * @param array $offer   Normalized offer.
 * @param array $payload Full payload.
 * @return array
 */
function lithia_project_import_generate_offer_seo( array $offer, array $payload ): array {
	$brand_name = lithia_project_import_get_brand_name( $payload );
	$city       = lithia_project_import_get_primary_city( $payload );
	$title      = $offer['title'];
	$summary    = $offer['excerpt'] ? $offer['excerpt'] : $offer['service_hero_text'];

	$seo_title = $brand_name ? $title . ' | ' . $brand_name : $title;

	if ( $brand_name && $city ) {
		$seo_title = sprintf( '%1$s in %2$s | %3$s', $title, $city, $brand_name );
	}

	$description = $summary;

	if ( '' === $description && $brand_name ) {
		$description = sprintf( '%1$s offers %2$s%3$s.', $brand_name, strtolower( $title ), $city ? ' in ' . $city : '' );
	}

	$focus_keyword = trim( $title . ( $city ? ' ' . $city : '' ) );

	return array(
		'title'                => $seo_title,
		'description'          => $description,
		'focus_keyword'        => $focus_keyword,
		'facebook_title'       => $seo_title,
		'facebook_description' => $description,
		'twitter_title'        => $seo_title,
		'twitter_description'  => $description,
		'robots'               => 'index,follow',
	);
}

/**
 * Parse a price-like value into a positive float amount.
 *
 * @param string $value Raw price value.
 * @return float|null
 */
function lithia_project_import_parse_price_amount( string $value ): ?float {
	$value = trim( $value );

	if ( '' === $value ) {
		return null;
	}

	$normalized = preg_replace( '/[^0-9.\-]/', '', $value );

	if ( ! is_string( $normalized ) || '' === $normalized || ! is_numeric( $normalized ) ) {
		return null;
	}

	$amount = (float) $normalized;

	return $amount < 0 ? 0.0 : $amount;
}

/**
 * Sync appointment price meta when service pricing is managed by importer.
 *
 * @param int        $post_id Post ID.
 * @param float|null $amount  Price amount.
 * @return void
 */
function lithia_project_import_sync_service_appointment_price( int $post_id, ?float $amount ): void {
	if ( $post_id <= 0 ) {
		return;
	}

	$appointment_meta = get_post_meta( $post_id, 'jet_apb_post_meta', true );

	if ( ! is_array( $appointment_meta ) || ! isset( $appointment_meta['meta_settings'] ) || ! is_array( $appointment_meta['meta_settings'] ) ) {
		return;
	}

	if ( null === $amount ) {
		unset( $appointment_meta['meta_settings']['_app_price'] );
	} else {
		$appointment_meta['meta_settings']['_app_price'] = (string) $amount;

		if ( empty( $appointment_meta['meta_settings']['price_type'] ) ) {
			$appointment_meta['meta_settings']['price_type'] = '_app_price';
		}
	}

	update_post_meta( $post_id, 'jet_apb_post_meta', $appointment_meta );
}

/**
 * Normalize an offer payload.
 *
 * @param array $offer   Raw offer payload.
 * @param array $payload Full payload.
 * @return array
 */
function lithia_project_import_normalize_offer_payload( array $offer, array $payload ): array {
	$title = sanitize_text_field( lithia_seed_normalize_scalar( $offer['title'] ?? $offer['post_title'] ?? '' ) );
	$slug  = sanitize_title( lithia_seed_normalize_scalar( $offer['slug'] ?? $offer['post_name'] ?? '' ) );

	if ( '' === $slug && '' !== $title ) {
		$slug = sanitize_title( $title );
	}

	$excerpt          = sanitize_textarea_field( lithia_seed_normalize_scalar( $offer['excerpt'] ?? $offer['summary'] ?? $offer['post_excerpt'] ?? '' ) );
	$service_hero_text = sanitize_textarea_field( lithia_seed_normalize_scalar( $offer['service_hero_text'] ?? $excerpt ) );
	$service_overview  = sanitize_textarea_field( lithia_seed_normalize_scalar( $offer['service_overview_text'] ?? $excerpt ) );
	$content           = (string) ( $offer['content'] ?? $offer['post_content'] ?? '' );

	if ( '' === $content ) {
		$content = lithia_project_import_build_paragraph_content( $excerpt ? $excerpt : $service_overview );
	}

	$highlights = $offer['service_highlights'] ?? $offer['highlights'] ?? $offer['outcomes'] ?? array();
	$process    = $offer['service_process_steps'] ?? $offer['process_steps'] ?? array();
	$primary_cta_label = sanitize_text_field(
		lithia_seed_normalize_scalar(
			$offer['service_primary_cta_label']
				?? $offer['primary_cta_label']
			?? $offer['cta_label']
			?? ( $payload['business']['primary_cta_label'] ?? 'Book Appointment' )
		)
	);
	$primary_cta_url = esc_url_raw(
		(string) (
			$offer['service_primary_cta_url']
				?? $offer['primary_cta_url']
			?? $offer['cta_target']
			?? ( $payload['business']['primary_cta_target'] ?? '/book-appointment/' )
		)
	);
	$secondary_cta_label = sanitize_text_field(
		lithia_seed_normalize_scalar(
			$offer['service_secondary_cta_label']
				?? $offer['secondary_cta_label']
			?? ( $payload['business']['secondary_cta_label'] ?? '' )
		)
	);
	$secondary_cta_url = esc_url_raw(
		(string) (
			$offer['service_secondary_cta_url']
				?? $offer['secondary_cta_url']
			?? ( $payload['business']['secondary_cta_target'] ?? '' )
		)
	);
	$homepage_spotlight_enabled = 'yes' === lithia_seed_normalize_flag(
		$offer['service_homepage_spotlight_enabled']
			?? $offer['homepage_spotlight_enabled']
		?? 'no'
	);
	$homepage_spotlight_order = max(
		0,
		absint(
			lithia_seed_normalize_scalar(
				$offer['service_homepage_spotlight_order']
					?? $offer['homepage_spotlight_order']
				?? 0
			)
		)
	);
	$price_from_raw = sanitize_text_field(
		lithia_seed_normalize_scalar(
			$offer['service_price_from']
				?? $offer['price_from']
			?? ''
		)
	);
	$price_amount = lithia_project_import_parse_price_amount( $price_from_raw );
	$price_notes = sanitize_textarea_field(
		lithia_seed_normalize_scalar(
			$offer['service_price_notes']
				?? $offer['price_notes']
			?? ''
		)
	);
	$service_audience = lithia_project_import_normalize_label_list(
		$offer['service_audience']
		?? $offer['audience']
		?? array()
	);
	$service_outcomes = lithia_project_import_normalize_label_list(
		$offer['service_outcomes']
		?? $offer['outcomes']
		?? array()
	);

	$normalized = array(
		'record_key' => lithia_seed_normalize_record_key( $offer['record_key'] ?? 'service_' . sanitize_key( $slug ) ),
		'title'      => $title,
		'slug'       => $slug,
		'status'     => sanitize_key( (string) ( $offer['status'] ?? $offer['post_status'] ?? 'publish' ) ),
		'excerpt'    => $excerpt,
		'content'    => $content,
		'category'   => sanitize_text_field( lithia_seed_normalize_scalar( $offer['category'] ?? 'service' ) ),
		'featured_image' => $offer['featured_image'] ?? $offer['featured_image_url'] ?? $offer['featured_image_asset_key'] ?? null,
		'service_hero_eyebrow' => sanitize_text_field( lithia_seed_normalize_scalar( $offer['service_hero_eyebrow'] ?? ucfirst( (string) ( $offer['category'] ?? 'Service' ) ) ) ),
		'service_hero_title'   => sanitize_text_field( lithia_seed_normalize_scalar( $offer['service_hero_title'] ?? $title ) ),
		'service_hero_text'    => $service_hero_text,
		'service_hero_image'   => $offer['service_hero_image'] ?? $offer['service_hero_image_url'] ?? $offer['hero_image'] ?? $offer['hero_image_asset_key'] ?? null,
		'service_primary_cta_label'   => $primary_cta_label,
		'service_primary_cta_url'     => $primary_cta_url,
		'service_secondary_cta_label' => $secondary_cta_label,
		'service_secondary_cta_url'   => $secondary_cta_url,
		'service_homepage_spotlight_enabled' => $homepage_spotlight_enabled ? 'yes' : 'no',
		'service_homepage_spotlight_order'   => $homepage_spotlight_order,
		'service_overview_heading'    => sanitize_text_field( lithia_seed_normalize_scalar( $offer['service_overview_heading'] ?? 'What This Includes' ) ),
		'service_overview_text'       => $service_overview,
		'service_highlights_heading'  => sanitize_text_field( lithia_seed_normalize_scalar( $offer['service_highlights_heading'] ?? 'What’s Included' ) ),
		'service_highlights'          => lithia_project_import_normalize_repeater_rows( $highlights, 'item_title', 'item_text' ),
		'service_process_heading'     => sanitize_text_field( lithia_seed_normalize_scalar( $offer['service_process_heading'] ?? 'Process' ) ),
		'service_process_steps'       => lithia_project_import_normalize_repeater_rows( $process, 'step_title', 'step_text' ),
		'service_booking_note'        => sanitize_textarea_field( lithia_seed_normalize_scalar( $offer['service_booking_note'] ?? $payload['booking']['booking_notice'] ?? '' ) ),
		'service_timeline'            => sanitize_text_field( lithia_seed_normalize_scalar( $offer['service_timeline'] ?? $offer['duration'] ?? $offer['timeline'] ?? '' ) ),
		'service_delivery_mode'       => sanitize_text_field( lithia_seed_normalize_scalar( $offer['service_delivery_mode'] ?? $offer['delivery_mode'] ?? '' ) ),
		'service_platform'            => sanitize_text_field( lithia_seed_normalize_scalar( $offer['service_platform'] ?? $offer['platform_stack'] ?? $offer['platform'] ?? '' ) ),
		'service_engagement_type'     => sanitize_text_field( lithia_seed_normalize_scalar( $offer['service_engagement_type'] ?? $offer['engagement_type'] ?? '' ) ),
		'service_price_from'          => $price_from_raw,
		'service_price_amount'        => $price_amount,
		'service_price_notes'         => $price_notes,
		'service_audience'            => $service_audience,
		'service_outcomes'            => $service_outcomes,
		'provider_slugs'              => lithia_project_import_normalize_slug_list( $offer['provider_slugs'] ?? array() ),
		'seo_image'                   => $offer['seo_image'] ?? $offer['seo_image_url'] ?? $offer['seo_image_asset_key'] ?? null,
	);

	$seo         = lithia_project_import_generate_offer_seo( $normalized, $payload );
	$input_seo   = is_array( $offer['seo'] ?? null ) ? $offer['seo'] : array();
	$normalized['seo'] = array(
		'title'                => sanitize_text_field( lithia_seed_normalize_scalar( $input_seo['title'] ?? $offer['rank_math_title'] ?? $seo['title'] ) ),
		'description'          => sanitize_textarea_field( lithia_seed_normalize_scalar( $input_seo['description'] ?? $offer['rank_math_description'] ?? $seo['description'] ) ),
		'focus_keyword'        => sanitize_text_field( lithia_seed_normalize_scalar( $input_seo['focus_keyword'] ?? $offer['rank_math_focus_keyword'] ?? $seo['focus_keyword'] ) ),
		'facebook_title'       => sanitize_text_field( lithia_seed_normalize_scalar( $input_seo['facebook_title'] ?? $offer['rank_math_facebook_title'] ?? $seo['facebook_title'] ) ),
		'facebook_description' => sanitize_textarea_field( lithia_seed_normalize_scalar( $input_seo['facebook_description'] ?? $offer['rank_math_facebook_description'] ?? $seo['facebook_description'] ) ),
		'twitter_title'        => sanitize_text_field( lithia_seed_normalize_scalar( $input_seo['twitter_title'] ?? $offer['rank_math_twitter_title'] ?? $seo['twitter_title'] ) ),
		'twitter_description'  => sanitize_textarea_field( lithia_seed_normalize_scalar( $input_seo['twitter_description'] ?? $offer['rank_math_twitter_description'] ?? $seo['twitter_description'] ) ),
		'robots'               => sanitize_text_field( lithia_seed_normalize_scalar( $input_seo['robots'] ?? $offer['rank_math_robots'] ?? $seo['robots'] ) ),
	);

	return $normalized;
}

/**
 * Normalize the full project payload.
 *
 * @param array $payload Raw payload.
 * @return array
 */
function lithia_normalize_project_payload( array $payload ): array {
	$project = is_array( $payload['project'] ?? null ) ? $payload['project'] : array();

	$normalized = array(
		'project' => array(
			'schema_version' => sanitize_text_field( lithia_seed_normalize_scalar( $project['schema_version'] ?? $payload['schema_version'] ?? lithia_get_project_import_schema_version() ) ),
			'template_key'   => sanitize_title( lithia_seed_normalize_scalar( $project['template_key'] ?? $payload['template_key'] ?? lithia_get_project_import_default_template_key() ) ),
			'industry'       => sanitize_title( lithia_seed_normalize_scalar( $project['industry'] ?? $payload['industry'] ?? '' ) ),
			'site_key'       => lithia_seed_normalize_record_key( $project['site_key'] ?? $payload['site_key'] ?? '' ),
			'review_state'   => lithia_normalize_project_review_state( $project['review_state'] ?? $payload['review_state'] ?? 'approved' ),
		),
		'business'       => is_array( $payload['business'] ?? null ) ? $payload['business'] : array(),
		'location'       => is_array( $payload['location'] ?? null ) ? $payload['location'] : array(),
		'audience'       => is_array( $payload['audience'] ?? null ) ? $payload['audience'] : array(),
		'pricing'        => is_array( $payload['pricing'] ?? null ) ? $payload['pricing'] : array(),
		'proof'          => lithia_project_import_normalize_proof_payload( $payload['proof'] ?? array() ),
		'booking'        => is_array( $payload['booking'] ?? null ) ? $payload['booking'] : array(),
		'faq'            => lithia_project_import_normalize_faq_rows( $payload['faq'] ?? array() ),
		'seo'            => is_array( $payload['seo'] ?? null ) ? $payload['seo'] : array(),
		'media'          => is_array( $payload['media'] ?? null ) ? $payload['media'] : array(),
		'site_settings'  => is_array( $payload['site_settings'] ?? null ) ? $payload['site_settings'] : array(),
		'business_details' => is_array( $payload['business_details'] ?? null ) ? $payload['business_details'] : array(),
		'brand_content'  => is_array( $payload['brand_content'] ?? null ) ? $payload['brand_content'] : array(),
		'site_styles'    => is_array( $payload['site_styles'] ?? null ) ? $payload['site_styles'] : array(),
		'pages'          => is_array( $payload['pages'] ?? null ) ? $payload['pages'] : array(),
		'providers'      => is_array( $payload['providers'] ?? null ) ? $payload['providers'] : array(),
		'offers'         => is_array( $payload['offers'] ?? null ) ? $payload['offers'] : ( is_array( $payload['services'] ?? null ) ? $payload['services'] : array() ),
	);

	$brand_name = lithia_project_import_get_brand_name( $normalized );
	$tagline    = sanitize_text_field( lithia_seed_normalize_scalar( $normalized['business']['short_tagline'] ?? '' ) );
	$generated_brand = lithia_project_import_generate_brand_content( $normalized );

	$normalized['site_settings'] = wp_parse_args(
		$normalized['site_settings'],
		array(
			'site_name'          => $brand_name,
			'site_tagline'       => $tagline,
			'site_key'           => $normalized['project']['site_key'],
			'booking_enabled'    => ! empty( $normalized['booking']['calendar_enabled'] ) ? 'yes' : 'no',
			'provider_pages_enabled' => ! empty( $normalized['providers'] ) ? 'yes' : 'no',
		)
	);

	$normalized['business_details'] = wp_parse_args(
		$normalized['business_details'],
		array(
			'business_name'  => $brand_name,
			'business_phone' => sanitize_text_field( lithia_seed_normalize_scalar( $normalized['business']['phone'] ?? '' ) ),
			'business_email' => sanitize_email( lithia_seed_normalize_scalar( $normalized['business']['email'] ?? '' ) ),
			'city'           => sanitize_text_field( lithia_seed_normalize_scalar( $normalized['location']['city'] ?? '' ) ),
			'state_region'   => sanitize_text_field( lithia_seed_normalize_scalar( $normalized['location']['state_region'] ?? '' ) ),
			'booking_notice' => sanitize_textarea_field( lithia_seed_normalize_scalar( $normalized['booking']['booking_notice'] ?? '' ) ),
		)
	);

	$normalized['brand_content'] = wp_parse_args( $normalized['brand_content'], $generated_brand );

	$normalized['pages'] = lithia_project_import_merge_page_payloads(
		lithia_project_import_build_default_page_payloads( $normalized ),
		$normalized['pages']
	);

	return $normalized;
}

/**
 * Import page payloads.
 *
 * @param array $pages Pages.
 * @param array $payload Full payload.
 * @param array $args Import args.
 * @return array
 */
function lithia_project_import_pages( array $pages, array $payload, array $args = array() ): array {
	$dry_run      = ! empty( $args['dry_run'] );
	$force        = ! empty( $args['force'] );
	$results      = array();
	$pages_by_role = array();

	foreach ( $pages as $page ) {
		if ( ! is_array( $page ) ) {
			continue;
		}

		$page = lithia_project_import_normalize_page_payload( $page, $payload );

		if ( '' === $page['title'] && '' === $page['slug'] && '' === $page['page_role'] ) {
			continue;
		}

		$existing = null;
		$page_id  = 0;

		if ( '' !== $page['page_role'] ) {
			$page_id = lithia_project_import_get_page_id_by_role( $page['page_role'] );
		}

		if ( ! $page_id && '' !== $page['record_key'] ) {
			$page_id = lithia_get_post_id_by_record_key( $page['record_key'], 'page' );
		}

		if ( ! $page_id && '' !== $page['slug'] ) {
			$existing = get_page_by_path( $page['slug'], OBJECT, 'page' );
			$page_id  = $existing instanceof WP_Post ? (int) $existing->ID : 0;
		}

		if ( $page_id && ! $existing ) {
			$existing = get_post( $page_id );
		}

		if ( $existing instanceof WP_Post && lithia_project_import_is_post_locked( (int) $existing->ID ) && ! $force ) {
			$results[] = array(
				'type'   => 'page',
				'role'   => $page['page_role'],
				'slug'   => $page['slug'],
				'post_id' => (int) $existing->ID,
				'status' => 'locked',
			);
			continue;
		}

		$postarr          = array(
			'post_type'   => 'page',
		);
		$snapshot_updates = array();
		$preserved_fields = array();
		$managed_fields   = array(
			'post_status'  => in_array( $page['status'], array( 'publish', 'draft', 'pending', 'private' ), true ) ? $page['status'] : 'publish',
			'post_title'   => $page['title'],
			'post_name'    => $page['slug'],
			'post_excerpt' => $page['excerpt'],
			'post_content' => $page['content'],
		);

		foreach ( $managed_fields as $field => $value ) {
			if ( ! $existing instanceof WP_Post ) {
				$postarr[ $field ]                = $value;
				$snapshot_updates[ 'post:' . $field ] = $value;
				continue;
			}

			$current_value = $existing->{$field};
			$snapshot_key  = 'post:' . $field;

			if ( lithia_project_import_can_write_managed_value( (int) $existing->ID, $snapshot_key, $current_value, $force ) ) {
				if ( ! lithia_project_import_values_equal( $current_value, $value ) ) {
					$postarr[ $field ] = $value;
				}
				$snapshot_updates[ $snapshot_key ] = $value;
			} else {
				$preserved_fields[] = $field;
			}
		}

		$featured_image_id = lithia_project_import_resolve_attachment_id( $page['featured_image'] );
		$seo_image_id      = lithia_project_import_resolve_attachment_id( $page['seo']['social_image'] ?? null );
		$meta_updates      = array(
			'_lithia_page_role'          => $page['page_role'],
			'_lithia_generated_source'   => 'project_importer',
			'_lithia_page_seed_headline' => $page['headline_seed'],
			'_lithia_page_seed_summary'  => $page['summary_seed'],
			'_lithia_page_seed_cta_label' => $page['cta_label'],
			'_lithia_page_seed_cta_target' => $page['cta_target'],
			'_lithia_page_seed_seo_title' => $page['seo_title_seed'],
			'_lithia_page_seed_seo_description' => $page['seo_description_seed'],
			'rank_math_title'            => $page['seo']['title'],
			'rank_math_description'      => $page['seo']['description'],
			'rank_math_focus_keyword'    => $page['seo']['focus_keyword'],
			'rank_math_facebook_title'   => $page['seo']['facebook_title'],
			'rank_math_facebook_description' => $page['seo']['facebook_description'],
			'rank_math_twitter_title'    => $page['seo']['twitter_title'],
			'rank_math_twitter_description' => $page['seo']['twitter_description'],
			'rank_math_robots'           => $page['seo']['robots'],
			'rank_math_facebook_image_id' => $seo_image_id ? $seo_image_id : '',
			'rank_math_twitter_image_id'  => $seo_image_id ? $seo_image_id : '',
		);

		if ( $page['record_key'] ) {
			$meta_updates[ lithia_get_record_key_meta_key() ] = $page['record_key'];
		}

		$meta_to_write      = array();
		$meta_snapshot      = array();

		foreach ( $meta_updates as $meta_key => $meta_value ) {
			if ( ! $existing instanceof WP_Post ) {
				$meta_to_write[ $meta_key ]             = $meta_value;
				$meta_snapshot[ 'meta:' . $meta_key ]   = $meta_value;
				continue;
			}

			$current_value = get_post_meta( (int) $existing->ID, $meta_key, true );
			$snapshot_key  = 'meta:' . $meta_key;

			if ( lithia_project_import_can_write_managed_value( (int) $existing->ID, $snapshot_key, $current_value, $force ) ) {
				if ( ! lithia_project_import_values_equal( $current_value, $meta_value ) ) {
					$meta_to_write[ $meta_key ] = $meta_value;
				}
				$meta_snapshot[ $snapshot_key ] = $meta_value;
			} else {
				$preserved_fields[] = $meta_key;
			}
		}

		$should_set_thumbnail = true;

		if ( $existing instanceof WP_Post ) {
			$current_thumbnail = (int) get_post_thumbnail_id( $existing->ID );

			if ( ! lithia_project_import_can_write_managed_value( (int) $existing->ID, 'meta:_thumbnail_id', $current_thumbnail, $force ) ) {
				$should_set_thumbnail = false;
				$preserved_fields[]   = '_thumbnail_id';
			} else {
				$meta_snapshot['meta:_thumbnail_id'] = $featured_image_id;
			}
		} else {
			$meta_snapshot['meta:_thumbnail_id'] = $featured_image_id;
		}

		$action = 'created';

		if ( $existing instanceof WP_Post ) {
			$action = ( ! empty( $postarr ) && count( $postarr ) > 1 ) || ! empty( $meta_to_write ) || ( $featured_image_id || 0 === $featured_image_id ) ? 'updated' : 'noop';
		}

		if ( $dry_run ) {
			$results[] = array(
				'type'    => 'page',
				'role'    => $page['page_role'],
				'slug'    => $page['slug'],
				'post_id' => $existing instanceof WP_Post ? (int) $existing->ID : 0,
				'status'  => $action,
				'preserved_fields' => array_values( array_unique( $preserved_fields ) ),
			);
			continue;
		}

		if ( $existing instanceof WP_Post ) {
			$postarr['ID'] = (int) $existing->ID;
			$result        = wp_update_post( wp_slash( $postarr ), true );
		} else {
			$result = wp_insert_post( wp_slash( $postarr ), true );
		}

		if ( is_wp_error( $result ) || ! $result ) {
			$results[] = array(
				'type'    => 'page',
				'role'    => $page['page_role'],
				'slug'    => $page['slug'],
				'post_id' => 0,
				'status'  => 'failed',
				'message' => is_wp_error( $result ) ? $result->get_error_message() : 'Page import failed.',
			);
			continue;
		}

		$post_id = (int) $result;

		foreach ( $meta_to_write as $meta_key => $meta_value ) {
			update_post_meta( $post_id, $meta_key, $meta_value );
		}

		if ( $should_set_thumbnail ) {
			if ( $featured_image_id ) {
				set_post_thumbnail( $post_id, $featured_image_id );
			} else {
				delete_post_thumbnail( $post_id );
			}
		}

		lithia_project_import_update_post_snapshot( $post_id, array_merge( $snapshot_updates, $meta_snapshot ) );
		clean_post_cache( $post_id );

		if ( $page['page_role'] ) {
			$pages_by_role[ $page['page_role'] ] = array(
				'post_id'    => $post_id,
				'record_key' => $page['record_key'],
			);
		}

		$results[] = array(
			'type'    => 'page',
			'role'    => $page['page_role'],
			'slug'    => $page['slug'],
			'post_id' => $post_id,
			'status'  => $action,
			'preserved_fields' => array_values( array_unique( $preserved_fields ) ),
		);
	}

	if ( ! $dry_run ) {
		$stored_settings = get_option( 'lithia_seed_site_settings', array() );
		$stored_settings = is_array( $stored_settings ) ? $stored_settings : array();

		foreach ( $pages_by_role as $page_role => $page_data ) {
			switch ( $page_role ) {
				case 'home':
					update_option( 'show_on_front', 'page' );
					update_option( 'page_on_front', (int) $page_data['post_id'] );
					$stored_settings['homepage_page_key'] = $page_data['record_key'];
					break;
				case 'about':
					$stored_settings['about_page_key'] = $page_data['record_key'];
					break;
				case 'contact':
					$stored_settings['contact_page_key'] = $page_data['record_key'];
					break;
				case 'booking':
					$stored_settings['booking_page_key'] = $page_data['record_key'];
					break;
				case 'posts':
					update_option( 'page_for_posts', (int) $page_data['post_id'] );
					$stored_settings['posts_page_key'] = $page_data['record_key'];
					break;
			}
		}

		update_option( 'lithia_seed_site_settings', $stored_settings, false );
	}

	return $results;
}

/**
 * Import provider payloads.
 *
 * @param array $providers Providers.
 * @param array $args      Import args.
 * @return array
 */
function lithia_project_import_providers( array $providers, array $args = array() ): array {
	$dry_run = ! empty( $args['dry_run'] );
	$force   = ! empty( $args['force'] );
	$results = array();

	foreach ( $providers as $provider ) {
		if ( ! is_array( $provider ) ) {
			continue;
		}

		$provider = lithia_project_import_normalize_provider_payload( $provider );

		if ( '' === $provider['title'] && '' === $provider['slug'] ) {
			continue;
		}

		$existing = null;
		$post_id  = 0;

		if ( $provider['record_key'] ) {
			$post_id = lithia_get_post_id_by_record_key( $provider['record_key'], 'providers' );
		}

		if ( ! $post_id && $provider['slug'] ) {
			$existing = get_page_by_path( $provider['slug'], OBJECT, 'providers' );
			$post_id  = $existing instanceof WP_Post ? (int) $existing->ID : 0;
		}

		if ( $post_id && ! $existing ) {
			$existing = get_post( $post_id );
		}

		if ( $existing instanceof WP_Post && lithia_project_import_is_post_locked( (int) $existing->ID ) && ! $force ) {
			$results[] = array(
				'type'    => 'provider',
				'slug'    => $provider['slug'],
				'post_id' => (int) $existing->ID,
				'status'  => 'locked',
			);
			continue;
		}

		$postarr          = array(
			'post_type' => 'providers',
		);
		$snapshot_updates = array();
		$preserved_fields = array();
		$managed_fields   = array(
			'post_status'  => in_array( $provider['status'], array( 'publish', 'draft', 'pending', 'private' ), true ) ? $provider['status'] : 'publish',
			'post_title'   => $provider['title'],
			'post_name'    => $provider['slug'],
			'post_excerpt' => $provider['excerpt'],
			'post_content' => $provider['content'] ? $provider['content'] : lithia_project_import_build_paragraph_content( $provider['excerpt'] ),
		);

		foreach ( $managed_fields as $field => $value ) {
			if ( ! $existing instanceof WP_Post ) {
				$postarr[ $field ]                = $value;
				$snapshot_updates[ 'post:' . $field ] = $value;
				continue;
			}

			$current_value = $existing->{$field};
			$snapshot_key  = 'post:' . $field;

			if ( lithia_project_import_can_write_managed_value( (int) $existing->ID, $snapshot_key, $current_value, $force ) ) {
				if ( ! lithia_project_import_values_equal( $current_value, $value ) ) {
					$postarr[ $field ] = $value;
				}
				$snapshot_updates[ $snapshot_key ] = $value;
			} else {
				$preserved_fields[] = $field;
			}
		}

		$meta_updates = array(
			'_lithia_generated_source' => 'project_importer',
			'lithia_provider_email'    => $provider['email'],
			'lithia_provider_phone'    => $provider['phone'],
		);

		if ( $provider['record_key'] ) {
			$meta_updates[ lithia_get_record_key_meta_key() ] = $provider['record_key'];
		}

		$meta_to_write = array();
		$meta_snapshot = array();

		foreach ( $meta_updates as $meta_key => $meta_value ) {
			if ( ! $existing instanceof WP_Post ) {
				$meta_to_write[ $meta_key ]           = $meta_value;
				$meta_snapshot[ 'meta:' . $meta_key ] = $meta_value;
				continue;
			}

			$current_value = get_post_meta( (int) $existing->ID, $meta_key, true );
			$snapshot_key  = 'meta:' . $meta_key;

			if ( lithia_project_import_can_write_managed_value( (int) $existing->ID, $snapshot_key, $current_value, $force ) ) {
				if ( ! lithia_project_import_values_equal( $current_value, $meta_value ) ) {
					$meta_to_write[ $meta_key ] = $meta_value;
				}
				$meta_snapshot[ $snapshot_key ] = $meta_value;
			} else {
				$preserved_fields[] = $meta_key;
			}
		}

		$action = 'created';

		if ( $existing instanceof WP_Post ) {
			$action = ( ! empty( $postarr ) && count( $postarr ) > 1 ) || ! empty( $meta_to_write ) ? 'updated' : 'noop';
		}

		if ( $dry_run ) {
			$results[] = array(
				'type'    => 'provider',
				'slug'    => $provider['slug'],
				'post_id' => $existing instanceof WP_Post ? (int) $existing->ID : 0,
				'status'  => $action,
				'record_key' => $provider['record_key'],
				'preserved_fields' => array_values( array_unique( $preserved_fields ) ),
			);
			continue;
		}

		if ( $existing instanceof WP_Post ) {
			$postarr['ID'] = (int) $existing->ID;
			$result        = wp_update_post( wp_slash( $postarr ), true );
		} else {
			$result = wp_insert_post( wp_slash( $postarr ), true );
		}

		if ( is_wp_error( $result ) || ! $result ) {
			$results[] = array(
				'type'    => 'provider',
				'slug'    => $provider['slug'],
				'post_id' => 0,
				'status'  => 'failed',
				'message' => is_wp_error( $result ) ? $result->get_error_message() : 'Provider import failed.',
				'record_key' => $provider['record_key'],
			);
			continue;
		}

		$post_id = (int) $result;

		foreach ( $meta_to_write as $meta_key => $meta_value ) {
			update_post_meta( $post_id, $meta_key, $meta_value );
		}

		lithia_project_import_update_post_snapshot( $post_id, array_merge( $snapshot_updates, $meta_snapshot ) );
		clean_post_cache( $post_id );

		$results[] = array(
			'type'    => 'provider',
			'slug'    => $provider['slug'],
			'post_id' => $post_id,
			'status'  => $action,
			'record_key' => $provider['record_key'],
			'preserved_fields' => array_values( array_unique( $preserved_fields ) ),
		);
	}

	return $results;
}

/**
 * Import offer payloads into the Services CPT.
 *
 * @param array $offers  Offers.
 * @param array $payload Full payload.
 * @param array $args    Import args.
 * @return array
 */
function lithia_project_import_offers( array $offers, array $payload, array $args = array() ): array {
	$dry_run = ! empty( $args['dry_run'] );
	$force   = ! empty( $args['force'] );
	$results = array();

	foreach ( $offers as $offer ) {
		if ( ! is_array( $offer ) ) {
			continue;
		}

		$offer = lithia_project_import_normalize_offer_payload( $offer, $payload );

		if ( '' === $offer['title'] && '' === $offer['slug'] ) {
			continue;
		}

		$existing = null;
		$post_id  = 0;

		if ( $offer['record_key'] ) {
			$post_id = lithia_get_post_id_by_record_key( $offer['record_key'], 'services' );
		}

		if ( ! $post_id && $offer['slug'] ) {
			$existing = get_page_by_path( $offer['slug'], OBJECT, 'services' );
			$post_id  = $existing instanceof WP_Post ? (int) $existing->ID : 0;
		}

		if ( $post_id && ! $existing ) {
			$existing = get_post( $post_id );
		}

		if ( $existing instanceof WP_Post && lithia_project_import_is_post_locked( (int) $existing->ID ) && ! $force ) {
			$results[] = array(
				'type'    => 'offer',
				'slug'    => $offer['slug'],
				'post_id' => (int) $existing->ID,
				'status'  => 'locked',
			);
			continue;
		}

		$postarr          = array(
			'post_type' => 'services',
		);
		$snapshot_updates = array();
		$preserved_fields = array();
		$managed_fields   = array(
			'post_status'  => in_array( $offer['status'], array( 'publish', 'draft', 'pending', 'private' ), true ) ? $offer['status'] : 'publish',
			'post_title'   => $offer['title'],
			'post_name'    => $offer['slug'],
			'post_excerpt' => $offer['excerpt'],
			'post_content' => $offer['content'],
		);

		foreach ( $managed_fields as $field => $value ) {
			if ( ! $existing instanceof WP_Post ) {
				$postarr[ $field ]                = $value;
				$snapshot_updates[ 'post:' . $field ] = $value;
				continue;
			}

			$current_value = $existing->{$field};
			$snapshot_key  = 'post:' . $field;

			if ( lithia_project_import_can_write_managed_value( (int) $existing->ID, $snapshot_key, $current_value, $force ) ) {
				if ( ! lithia_project_import_values_equal( $current_value, $value ) ) {
					$postarr[ $field ] = $value;
				}
				$snapshot_updates[ $snapshot_key ] = $value;
			} else {
				$preserved_fields[] = $field;
			}
		}

		$featured_image_id = lithia_project_import_resolve_attachment_id( $offer['featured_image'] );
		$hero_image_id     = lithia_project_import_resolve_attachment_id( $offer['service_hero_image'] );
		$seo_image_id      = lithia_project_import_resolve_attachment_id( $offer['seo_image'] );
		$meta_updates      = array(
			'_lithia_generated_source'      => 'project_importer',
			'_lithia_offer_category'        => $offer['category'],
			'service_hero_eyebrow'         => $offer['service_hero_eyebrow'],
			'service_hero_title'           => $offer['service_hero_title'],
			'service_hero_text'            => $offer['service_hero_text'],
			'service_primary_cta_label'    => $offer['service_primary_cta_label'],
			'service_primary_cta_url'      => $offer['service_primary_cta_url'],
			'service_secondary_cta_label'  => $offer['service_secondary_cta_label'],
			'service_secondary_cta_url'    => $offer['service_secondary_cta_url'],
			'service_homepage_spotlight_enabled' => $offer['service_homepage_spotlight_enabled'],
			'service_homepage_spotlight_order'   => $offer['service_homepage_spotlight_order'],
			'service_overview_heading'     => $offer['service_overview_heading'],
			'service_overview_text'        => $offer['service_overview_text'],
			'service_highlights_heading'   => $offer['service_highlights_heading'],
			'service_highlights'           => $offer['service_highlights'],
			'service_process_heading'      => $offer['service_process_heading'],
			'service_process_steps'        => $offer['service_process_steps'],
			'service_booking_note'         => $offer['service_booking_note'],
			'service_timeline'             => $offer['service_timeline'],
			'service_delivery_mode'        => $offer['service_delivery_mode'],
			'service_platform'             => $offer['service_platform'],
			'service_engagement_type'      => $offer['service_engagement_type'],
			'service_price_from'           => $offer['service_price_from'],
			'service_price_notes'          => $offer['service_price_notes'],
			'service_audience'             => $offer['service_audience'],
			'service_outcomes'             => $offer['service_outcomes'],
			'_app_price'                   => null === $offer['service_price_amount'] ? '' : (string) $offer['service_price_amount'],
			'service_hero_image'           => $hero_image_id ? $hero_image_id : 0,
			'rank_math_title'              => $offer['seo']['title'],
			'rank_math_description'        => $offer['seo']['description'],
			'rank_math_focus_keyword'      => $offer['seo']['focus_keyword'],
			'rank_math_facebook_title'     => $offer['seo']['facebook_title'],
			'rank_math_facebook_description' => $offer['seo']['facebook_description'],
			'rank_math_twitter_title'      => $offer['seo']['twitter_title'],
			'rank_math_twitter_description' => $offer['seo']['twitter_description'],
			'rank_math_robots'             => $offer['seo']['robots'],
			'rank_math_facebook_image_id'  => $seo_image_id ? $seo_image_id : '',
			'rank_math_twitter_image_id'   => $seo_image_id ? $seo_image_id : '',
		);

		if ( $offer['record_key'] ) {
			$meta_updates[ lithia_get_record_key_meta_key() ] = $offer['record_key'];
		}

		$meta_to_write = array();
		$meta_snapshot = array();

		foreach ( $meta_updates as $meta_key => $meta_value ) {
			if ( ! $existing instanceof WP_Post ) {
				$meta_to_write[ $meta_key ]           = $meta_value;
				$meta_snapshot[ 'meta:' . $meta_key ] = $meta_value;
				continue;
			}

			$current_value = get_post_meta( (int) $existing->ID, $meta_key, true );
			$snapshot_key  = 'meta:' . $meta_key;

			if ( lithia_project_import_can_write_managed_value( (int) $existing->ID, $snapshot_key, $current_value, $force ) ) {
				if ( ! lithia_project_import_values_equal( $current_value, $meta_value ) ) {
					$meta_to_write[ $meta_key ] = $meta_value;
				}
				$meta_snapshot[ $snapshot_key ] = $meta_value;
			} else {
				$preserved_fields[] = $meta_key;
			}
		}

		$should_set_thumbnail = true;

		if ( $existing instanceof WP_Post ) {
			$current_thumbnail = (int) get_post_thumbnail_id( $existing->ID );

			if ( ! lithia_project_import_can_write_managed_value( (int) $existing->ID, 'meta:_thumbnail_id', $current_thumbnail, $force ) ) {
				$should_set_thumbnail = false;
				$preserved_fields[]   = '_thumbnail_id';
			} else {
				$meta_snapshot['meta:_thumbnail_id'] = $featured_image_id;
			}
		} else {
			$meta_snapshot['meta:_thumbnail_id'] = $featured_image_id;
		}

		$action = 'created';

		if ( $existing instanceof WP_Post ) {
			$action = ( ! empty( $postarr ) && count( $postarr ) > 1 ) || ! empty( $meta_to_write ) || ( $featured_image_id || 0 === $featured_image_id ) ? 'updated' : 'noop';
		}

		if ( $dry_run ) {
			$results[] = array(
				'type'    => 'offer',
				'slug'    => $offer['slug'],
				'post_id' => $existing instanceof WP_Post ? (int) $existing->ID : 0,
				'status'  => $action,
				'record_key' => $offer['record_key'],
				'provider_slugs' => $offer['provider_slugs'],
				'preserved_fields' => array_values( array_unique( $preserved_fields ) ),
			);
			continue;
		}

		if ( $existing instanceof WP_Post ) {
			$postarr['ID'] = (int) $existing->ID;
			$result        = wp_update_post( wp_slash( $postarr ), true );
		} else {
			$result = wp_insert_post( wp_slash( $postarr ), true );
		}

		if ( is_wp_error( $result ) || ! $result ) {
			$results[] = array(
				'type'    => 'offer',
				'slug'    => $offer['slug'],
				'post_id' => 0,
				'status'  => 'failed',
				'message' => is_wp_error( $result ) ? $result->get_error_message() : 'Offer import failed.',
				'record_key' => $offer['record_key'],
			);
			continue;
		}

		$post_id = (int) $result;

		foreach ( $meta_to_write as $meta_key => $meta_value ) {
			update_post_meta( $post_id, $meta_key, $meta_value );
		}

		if ( array_key_exists( '_app_price', $meta_to_write ) ) {
			lithia_project_import_sync_service_appointment_price( $post_id, $offer['service_price_amount'] );
		}

		if ( $should_set_thumbnail ) {
			if ( $featured_image_id ) {
				set_post_thumbnail( $post_id, $featured_image_id );
			} else {
				delete_post_thumbnail( $post_id );
			}
		}

		lithia_project_import_update_post_snapshot( $post_id, array_merge( $snapshot_updates, $meta_snapshot ) );
		clean_post_cache( $post_id );

		$results[] = array(
			'type'    => 'offer',
			'slug'    => $offer['slug'],
			'post_id' => $post_id,
			'status'  => $action,
			'record_key' => $offer['record_key'],
			'provider_slugs' => $offer['provider_slugs'],
			'preserved_fields' => array_values( array_unique( $preserved_fields ) ),
		);
	}

	return $results;
}

/**
 * Summarize import results by entity.
 *
 * @param array $results Result rows.
 * @return array
 */
function lithia_project_import_summarize_results( array $results ): array {
	$summary = array(
		'created' => 0,
		'updated' => 0,
		'noop'    => 0,
		'locked'  => 0,
		'failed'  => 0,
	);

	foreach ( $results as $result ) {
		$status = $result['status'] ?? 'noop';

		if ( isset( $summary[ $status ] ) ) {
			$summary[ $status ]++;
		}
	}

	return $summary;
}

/**
 * Add one payload validation issue row.
 *
 * @param array  $issues  Issues bucket.
 * @param string $code    Issue code.
 * @param string $message Issue message.
 * @param array  $context Extra context.
 * @return void
 */
function lithia_project_import_add_validation_issue( array &$issues, string $code, string $message, array $context = array() ): void {
	$issues[] = array_merge(
		array(
			'code'    => sanitize_key( $code ),
			'message' => sanitize_text_field( $message ),
		),
		$context
	);
}

/**
 * Validate one unique field across a list of entity rows.
 *
 * @param array  $rows        Entity rows.
 * @param string $entity_type Entity type label.
 * @param string $field_key   Field key.
 * @param string $field_label Human-readable field label.
 * @param array  $errors      Error bucket.
 * @return void
 */
function lithia_project_import_validate_unique_field( array $rows, string $entity_type, string $field_key, string $field_label, array &$errors ): void {
	$seen = array();

	foreach ( $rows as $index => $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}

		$value = lithia_seed_normalize_scalar( $row[ $field_key ] ?? '' );

		if ( '' === $value ) {
			continue;
		}

		switch ( $field_key ) {
			case 'slug':
			case 'page_role':
				$normalized_value = sanitize_title( $value );
				break;
			case 'record_key':
				$normalized_value = lithia_seed_normalize_record_key( $value );
				break;
			default:
				$normalized_value = sanitize_text_field( $value );
				break;
		}

		if ( '' === $normalized_value ) {
			continue;
		}

		if ( isset( $seen[ $normalized_value ] ) ) {
			lithia_project_import_add_validation_issue(
				$errors,
				'duplicate_' . $field_key,
				sprintf(
					'Duplicate %1$s %2$s "%3$s" found in rows %4$d and %5$d.',
					$entity_type,
					$field_label,
					$normalized_value,
					(int) $seen[ $normalized_value ],
					(int) $index + 1
				),
				array(
					'entity_type' => $entity_type,
					'field'       => $field_key,
					'value'       => $normalized_value,
				)
			);
			continue;
		}

		$seen[ $normalized_value ] = (int) $index + 1;
	}
}

/**
 * Return the option name used for imported FAQ rows.
 *
 * @return string
 */
function lithia_get_project_faq_option_name(): string {
	return 'lithia_project_faq';
}

/**
 * Import FAQ rows into project option storage.
 *
 * @param array $faq_rows Normalized FAQ rows.
 * @param array $args     Import args.
 * @return array
 */
function lithia_project_import_faq( array $faq_rows, array $args = array() ): array {
	$dry_run  = ! empty( $args['dry_run'] );
	$existing = get_option( lithia_get_project_faq_option_name(), array() );
	$existing = is_array( $existing ) ? $existing : array();
	$changed  = ! lithia_project_import_values_equal( $existing, $faq_rows );
	$status   = $changed ? 'updated' : 'noop';

	if ( ! $dry_run && $changed ) {
		update_option( lithia_get_project_faq_option_name(), $faq_rows, false );
	}

	return array(
		'type'           => 'faq',
		'status'         => $status,
		'count'          => count( $faq_rows ),
		'changed'        => $changed,
		'option_name'    => lithia_get_project_faq_option_name(),
	);
}

/**
 * Return the option name used for imported proof/testimonial rows.
 *
 * @return string
 */
function lithia_get_project_proof_option_name(): string {
	return 'lithia_project_proof';
}

/**
 * Import normalized proof payload into option storage.
 *
 * @param array $proof_payload Normalized proof payload.
 * @param array $args          Import args.
 * @return array
 */
function lithia_project_import_proof( array $proof_payload, array $args = array() ): array {
	$dry_run  = ! empty( $args['dry_run'] );
	$existing = get_option( lithia_get_project_proof_option_name(), array() );
	$existing = is_array( $existing ) ? $existing : array();
	$changed  = ! lithia_project_import_values_equal( $existing, $proof_payload );
	$status   = $changed ? 'updated' : 'noop';

	if ( ! $dry_run && $changed ) {
		update_option( lithia_get_project_proof_option_name(), $proof_payload, false );
	}

	return array(
		'type'                 => 'proof',
		'status'               => $status,
		'changed'              => $changed,
		'option_name'          => lithia_get_project_proof_option_name(),
		'testimonials_count'   => count( (array) ( $proof_payload['testimonials'] ?? array() ) ),
		'credentials_count'    => count( (array) ( $proof_payload['credentials'] ?? array() ) ),
		'highlights_count'     => count( (array) ( $proof_payload['highlights'] ?? array() ) ),
		'awards_count'         => count( (array) ( $proof_payload['awards'] ?? array() ) ),
	);
}

/**
 * Return the option name used for imported page seed records.
 *
 * @return string
 */
function lithia_get_project_page_seeds_option_name(): string {
	return 'lithia_project_page_seeds';
}

/**
 * Import normalized page seed records into option storage.
 *
 * @param array $pages Normalized page payload rows.
 * @param array $args  Import args.
 * @return array
 */
function lithia_project_import_page_seeds( array $pages, array $args = array() ): array {
	$dry_run  = ! empty( $args['dry_run'] );
	$seeds    = array();

	foreach ( $pages as $page ) {
		if ( ! is_array( $page ) ) {
			continue;
		}

		$page_role = sanitize_key( (string) ( $page['page_role'] ?? '' ) );

		if ( '' === $page_role ) {
			continue;
		}

		$seeds[] = array(
			'record_key'           => lithia_seed_normalize_record_key( $page['record_key'] ?? '' ),
			'page_role'            => $page_role,
			'headline_seed'        => sanitize_text_field( lithia_seed_normalize_scalar( $page['headline_seed'] ?? '' ) ),
			'summary_seed'         => sanitize_textarea_field( lithia_seed_normalize_scalar( $page['summary_seed'] ?? '' ) ),
			'cta_label'            => sanitize_text_field( lithia_seed_normalize_scalar( $page['cta_label'] ?? '' ) ),
			'cta_target'           => esc_url_raw( (string) ( $page['cta_target'] ?? '' ) ),
			'seo_title_seed'       => sanitize_text_field( lithia_seed_normalize_scalar( $page['seo_title_seed'] ?? '' ) ),
			'seo_description_seed' => sanitize_textarea_field( lithia_seed_normalize_scalar( $page['seo_description_seed'] ?? '' ) ),
		);
	}

	$existing = get_option( lithia_get_project_page_seeds_option_name(), array() );
	$existing = is_array( $existing ) ? $existing : array();
	$changed  = ! lithia_project_import_values_equal( $existing, $seeds );
	$status   = $changed ? 'updated' : 'noop';

	if ( ! $dry_run && $changed ) {
		update_option( lithia_get_project_page_seeds_option_name(), $seeds, false );
	}

	return array(
		'type'        => 'page_seeds',
		'status'      => $status,
		'changed'     => $changed,
		'count'       => count( $seeds ),
		'option_name' => lithia_get_project_page_seeds_option_name(),
	);
}

/**
 * Validate a canonical project payload.
 *
 * @param array $payload Raw payload.
 * @return array
 */
function lithia_validate_project_payload( array $payload ): array {
	$raw_payload = $payload;
	$payload     = lithia_normalize_project_payload( $payload );
	$errors      = array();
	$warnings    = array();
	$brand_name  = lithia_project_import_get_brand_name( $payload );
	$raw_pages   = is_array( $raw_payload['pages'] ?? null ) ? $raw_payload['pages'] : array();
	$raw_offers  = is_array( $raw_payload['offers'] ?? null ) ? $raw_payload['offers'] : ( is_array( $raw_payload['services'] ?? null ) ? $raw_payload['services'] : array() );

	if ( '' === trim( (string) $payload['project']['site_key'] ) ) {
		lithia_project_import_add_validation_issue( $errors, 'missing_site_key', 'Project site_key is required before import.' );
	}

	if ( '' === trim( (string) $payload['project']['industry'] ) ) {
		lithia_project_import_add_validation_issue( $errors, 'missing_industry', 'Project industry is required before import.' );
	}

	if ( '' === trim( $brand_name ) ) {
		lithia_project_import_add_validation_issue( $errors, 'missing_brand_name', 'A business brand name is required before import.' );
	}

	if ( empty( $raw_pages ) ) {
		lithia_project_import_add_validation_issue(
			$warnings,
			'generated_pages_only',
			'No explicit page payloads were supplied. Generated default pages will be used.'
		);
	}

	if ( empty( $payload['offers'] ) ) {
		lithia_project_import_add_validation_issue(
			$warnings,
			'no_offers',
			'No offers/services were supplied. Import will only update settings, pages, and providers.'
		);
	}

	if ( empty( $raw_offers ) ) {
		lithia_project_import_add_validation_issue(
			$warnings,
			'no_explicit_offers',
			'No explicit offer rows were supplied in the payload draft.'
		);
	}

	foreach ( (array) $payload['offers'] as $index => $offer ) {
		if ( ! is_array( $offer ) ) {
			continue;
		}

		if ( '' === trim( (string) ( $offer['title'] ?? '' ) ) ) {
			lithia_project_import_add_validation_issue(
				$errors,
				'missing_offer_title',
				sprintf( 'Offer row %d is missing a title.', (int) $index + 1 )
			);
		}
	}

	foreach ( (array) $payload['providers'] as $index => $provider ) {
		if ( ! is_array( $provider ) ) {
			continue;
		}

		if ( '' === trim( (string) ( $provider['title'] ?? '' ) ) ) {
			lithia_project_import_add_validation_issue(
				$errors,
				'missing_provider_title',
				sprintf( 'Provider row %d is missing a title.', (int) $index + 1 )
			);
		}
	}

	lithia_project_import_validate_unique_field( (array) $payload['pages'], 'page', 'record_key', 'record key', $errors );
	lithia_project_import_validate_unique_field( (array) $payload['pages'], 'page', 'slug', 'slug', $errors );
	lithia_project_import_validate_unique_field( (array) $payload['pages'], 'page', 'page_role', 'page role', $errors );
	lithia_project_import_validate_unique_field( (array) $payload['offers'], 'offer', 'record_key', 'record key', $errors );
	lithia_project_import_validate_unique_field( (array) $payload['offers'], 'offer', 'slug', 'slug', $errors );
	lithia_project_import_validate_unique_field( (array) $payload['providers'], 'provider', 'record_key', 'record key', $errors );
	lithia_project_import_validate_unique_field( (array) $payload['providers'], 'provider', 'slug', 'slug', $errors );

	$provider_slugs = array();

	foreach ( (array) $payload['providers'] as $provider ) {
		if ( ! is_array( $provider ) ) {
			continue;
		}

		$slug = sanitize_title( (string) ( $provider['slug'] ?? '' ) );

		if ( '' !== $slug ) {
			$provider_slugs[ $slug ] = true;
		}
	}

	foreach ( (array) $payload['offers'] as $index => $offer ) {
		if ( ! is_array( $offer ) ) {
			continue;
		}

		foreach ( (array) ( $offer['provider_slugs'] ?? array() ) as $provider_slug ) {
			$provider_slug = sanitize_title( (string) $provider_slug );

			if ( '' === $provider_slug || isset( $provider_slugs[ $provider_slug ] ) ) {
				continue;
			}

			lithia_project_import_add_validation_issue(
				$warnings,
				'missing_provider_reference',
				sprintf(
					'Offer row %1$d references missing provider slug "%2$s".',
					(int) $index + 1,
					$provider_slug
				),
				array(
					'entity_type' => 'offer',
					'field'       => 'provider_slugs',
					'value'       => $provider_slug,
				)
			);
		}
	}

	return array(
		'payload'    => $payload,
		'errors'     => $errors,
		'warnings'   => $warnings,
		'is_valid'   => empty( $errors ),
		'has_issues' => ! empty( $errors ) || ! empty( $warnings ),
	);
}

/**
 * Import a canonical project payload.
 *
 * @param array $payload Raw payload.
 * @param array $args    Import args.
 * @return array
 */
function lithia_import_project_payload( array $payload, array $args = array() ): array {
	$validation      = lithia_validate_project_payload( $payload );
	$payload         = $validation['payload'];
	$dry_run         = ! empty( $args['dry_run'] );
	$current_context = lithia_get_project_context();
	$force           = ! empty( $args['force'] ) || '' === trim( (string) $current_context['last_imported_at'] );
	$source          = sanitize_key( (string) ( $args['source'] ?? 'manual' ) );
	$incoming_state  = lithia_normalize_project_review_state( $payload['project']['review_state'] ?? 'approved' );

	if ( ! empty( $validation['errors'] ) ) {
		return array(
			'success'    => false,
			'error'      => implode( ' ', wp_list_pluck( $validation['errors'], 'message' ) ),
			'validation' => $validation,
		);
	}

	if ( ! $dry_run && lithia_get_project_review_state_rank( $incoming_state ) < lithia_get_project_review_state_rank( 'approved' ) ) {
		return array(
			'success' => false,
			'error'   => sprintf(
				'Project payload must be approved before import. Current state: %s',
				$incoming_state
			),
		);
	}

	$site_scope_payload = array(
		'site_settings'    => $payload['site_settings'],
		'business_details' => $payload['business_details'],
		'brand_content'    => $payload['brand_content'],
		'site_styles'      => $payload['site_styles'],
	);

	$site_sync = lithia_seed_sync(
		$site_scope_payload,
		array(
			'dry_run' => $dry_run,
		)
	);

	$page_results     = lithia_project_import_pages(
		$payload['pages'],
		$payload,
		array(
			'dry_run' => $dry_run,
			'force'   => $force,
		)
	);
	$provider_results = lithia_project_import_providers(
		$payload['providers'],
		array(
			'dry_run' => $dry_run,
			'force'   => $force,
		)
	);
	$offer_results    = lithia_project_import_offers(
		$payload['offers'],
		$payload,
		array(
			'dry_run' => $dry_run,
			'force'   => $force,
		)
	);
	$page_seeds_result = lithia_project_import_page_seeds(
		(array) $payload['pages'],
		array(
			'dry_run' => $dry_run,
		)
	);
	$faq_result       = lithia_project_import_faq(
		(array) $payload['faq'],
		array(
			'dry_run' => $dry_run,
		)
	);
	$proof_result     = lithia_project_import_proof(
		(array) $payload['proof'],
		array(
			'dry_run' => $dry_run,
		)
	);

	$provider_slug_map = array();
	$relation_map      = array();

	foreach ( $provider_results as $provider_result ) {
		if ( empty( $provider_result['post_id'] ) || in_array( $provider_result['status'], array( 'failed', 'locked' ), true ) ) {
			continue;
		}

		$slug = sanitize_title( (string) ( $provider_result['slug'] ?? '' ) );

		if ( '' !== $slug ) {
			$provider_slug_map[ $slug ] = (int) $provider_result['post_id'];
		}
	}

	foreach ( $offer_results as $offer_result ) {
		if ( empty( $offer_result['post_id'] ) || in_array( $offer_result['status'], array( 'failed', 'locked' ), true ) ) {
			continue;
		}

		$provider_ids = array();

		foreach ( (array) ( $offer_result['provider_slugs'] ?? array() ) as $provider_slug ) {
			$provider_slug = sanitize_title( (string) $provider_slug );

			if ( isset( $provider_slug_map[ $provider_slug ] ) ) {
				$provider_ids[] = $provider_slug_map[ $provider_slug ];
			}
		}

		if ( ! empty( $provider_ids ) ) {
			$relation_map[ (int) $offer_result['post_id'] ] = array_values( array_unique( $provider_ids ) );
		}
	}

	if ( ! $dry_run && ! empty( $relation_map ) ) {
		lithia_sync_service_provider_relationships( $relation_map );
	}

	$final_state = lithia_get_project_review_state_rank( $incoming_state ) > lithia_get_project_review_state_rank( 'imported' )
		? $incoming_state
		: 'imported';

	$payload_hash = md5( wp_json_encode( $payload ) );
	$context      = array_merge(
		lithia_get_project_context_defaults(),
		$current_context,
		array(
			'schema_version'      => $payload['project']['schema_version'],
			'template_key'        => $payload['project']['template_key'],
			'industry'            => sanitize_text_field( lithia_seed_normalize_scalar( $payload['project']['industry'] ?? '' ) ),
			'site_key'            => $payload['project']['site_key'],
			'review_state'        => $final_state,
			'source_review_state' => $incoming_state,
			'last_import_source'  => $source,
			'last_imported_at'    => current_time( 'mysql' ),
			'payload_hash'        => $payload_hash,
		)
	);

	if ( ! $dry_run ) {
		lithia_update_project_context( $context );
	}

	return array(
		'success'    => true,
		'dry_run'    => $dry_run,
		'source'     => $source,
		'validation' => $validation,
		'project'    => $payload['project'],
		'context'    => $context,
		'site_sync'  => $site_sync,
		'pages'      => $page_results,
		'providers'  => $provider_results,
		'offers'     => $offer_results,
		'page_seeds' => $page_seeds_result,
		'faq'        => $faq_result,
		'proof'      => $proof_result,
		'relationships_synced' => $dry_run ? count( array_filter( $relation_map ) ) : count( array_filter( $relation_map ) ),
		'summary'    => array(
			'pages'     => lithia_project_import_summarize_results( $page_results ),
			'providers' => lithia_project_import_summarize_results( $provider_results ),
			'offers'    => lithia_project_import_summarize_results( $offer_results ),
			'page_seeds' => array(
				'updated' => 'updated' === (string) $page_seeds_result['status'] ? 1 : 0,
				'noop'    => 'noop' === (string) $page_seeds_result['status'] ? 1 : 0,
				'count'   => (int) ( $page_seeds_result['count'] ?? 0 ),
			),
			'faq'       => array(
				'updated' => 'updated' === (string) $faq_result['status'] ? 1 : 0,
				'noop'    => 'noop' === (string) $faq_result['status'] ? 1 : 0,
				'count'   => (int) ( $faq_result['count'] ?? 0 ),
			),
			'proof'     => array(
				'updated'            => 'updated' === (string) $proof_result['status'] ? 1 : 0,
				'noop'               => 'noop' === (string) $proof_result['status'] ? 1 : 0,
				'testimonials_count' => (int) ( $proof_result['testimonials_count'] ?? 0 ),
				'credentials_count'  => (int) ( $proof_result['credentials_count'] ?? 0 ),
				'highlights_count'   => (int) ( $proof_result['highlights_count'] ?? 0 ),
				'awards_count'       => (int) ( $proof_result['awards_count'] ?? 0 ),
			),
		),
	);
}

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	/**
	 * WP-CLI command for canonical project imports.
	 */
	class Lithia_Project_Import_Command {
		/**
		 * Import a canonical project payload from JSON.
		 *
		 * ## OPTIONS
		 *
		 * --file=<path>
		 * : Path to the JSON payload file.
		 *
		 * [--dry-run]
		 * : Return the proposed changes without saving them.
		 *
		 * [--force]
		 * : Force importer writes even when a managed field differs from its last imported snapshot.
		 *
		 * [--source=<source>]
		 * : Tag the import source in project context.
		 *
		 * ## EXAMPLES
		 *
		 *     wp lithia import-project --file=/path/to/project.json
		 *     wp lithia import-project --file=/path/to/project.json --dry-run
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

			$report = lithia_import_project_payload(
				$payload,
				array(
					'dry_run' => \WP_CLI\Utils\get_flag_value( $assoc_args, 'dry-run', false ),
					'force'   => \WP_CLI\Utils\get_flag_value( $assoc_args, 'force', false ),
					'source'  => $assoc_args['source'] ?? 'wp_cli',
				)
			);

			if ( empty( $report['success'] ) ) {
				WP_CLI::error( $report['error'] ?? 'Project import failed.' );
			}

			WP_CLI::line( wp_json_encode( $report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
			WP_CLI::success( empty( $assoc_args['dry-run'] ) ? 'Project import completed.' : 'Dry run completed.' );
		}
	}

	WP_CLI::add_command( 'lithia import-project', 'Lithia_Project_Import_Command' );
}
