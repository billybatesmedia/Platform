<?php
/**
 * Launch Wizard admin flow.
 *
 * @package LithiaWebServiceTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return the Launch Wizard page slug.
 *
 * @return string
 */
function lithia_get_launch_wizard_page_slug(): string {
	return 'lithia-launch-wizard';
}

/**
 * Return the Launch Wizard draft option name.
 *
 * @return string
 */
function lithia_get_launch_wizard_draft_option_name(): string {
	return 'lithia_launch_wizard_draft';
}

/**
 * Return the Launch Wizard steps.
 *
 * @return array
 */
function lithia_get_launch_wizard_steps(): array {
	return array(
		'basics'   => array(
			'label'       => __( 'Site Basics', 'lithia-web-service-theme' ),
			'description' => __( 'Set the site name, core business details, and launch flags.', 'lithia-web-service-theme' ),
		),
		'brand'    => array(
			'label'       => __( 'Brand', 'lithia-web-service-theme' ),
			'description' => __( 'Set reusable messaging and a few core design tokens.', 'lithia-web-service-theme' ),
		),
		'services' => array(
			'label'       => __( 'Services', 'lithia-web-service-theme' ),
			'description' => __( 'Create the starter service pages and basic facts for each one.', 'lithia-web-service-theme' ),
		),
		'providers' => array(
			'label'       => __( 'Providers', 'lithia-web-service-theme' ),
			'description' => __( 'Optional staff or provider records used for booking and profile pages.', 'lithia-web-service-theme' ),
		),
		'review'   => array(
			'label'       => __( 'Review', 'lithia-web-service-theme' ),
			'description' => __( 'Review the launch setup and apply it to the live site.', 'lithia-web-service-theme' ),
		),
	);
}

/**
 * Return the normalized Launch Wizard step key.
 *
 * @param string $step Requested step.
 * @return string
 */
function lithia_get_launch_wizard_step( string $step = '' ): string {
	$steps = lithia_get_launch_wizard_steps();
	$step  = sanitize_key( $step );

	return isset( $steps[ $step ] ) ? $step : 'basics';
}

/**
 * Return the URL for a Launch Wizard step.
 *
 * @param string $step Step slug.
 * @param array  $args Extra query args.
 * @return string
 */
function lithia_get_launch_wizard_step_url( string $step, array $args = array() ): string {
	$query_args = array_merge(
		array(
			'page' => lithia_get_launch_wizard_page_slug(),
			'step' => lithia_get_launch_wizard_step( $step ),
		),
		$args
	);

	return add_query_arg( $query_args, admin_url( 'themes.php' ) );
}

/**
 * Return the field defaults for a service row.
 *
 * @return array
 */
function lithia_get_launch_wizard_service_row_defaults(): array {
	return array(
		'record_key'      => '',
		'title'           => '',
		'slug'            => '',
		'excerpt'         => '',
		'timeline'        => '',
		'delivery_mode'   => '',
		'platform_stack'  => '',
		'engagement_type' => '',
		'provider_slugs'  => '',
	);
}

/**
 * Return the field defaults for a provider row.
 *
 * @return array
 */
function lithia_get_launch_wizard_provider_row_defaults(): array {
	return array(
		'record_key' => '',
		'title'      => '',
		'slug'       => '',
		'excerpt'    => '',
		'email'      => '',
		'phone'      => '',
	);
}

/**
 * Return the current site data as Launch Wizard defaults.
 *
 * @return array
 */
function lithia_get_launch_wizard_defaults(): array {
	$service_rows  = array();
	$provider_rows = array();

	$services = get_posts(
		array(
			'post_type'      => 'services',
			'post_status'    => array( 'publish', 'draft', 'private' ),
			'posts_per_page' => -1,
			'orderby'        => 'menu_order title',
			'order'          => 'ASC',
		)
	);

	foreach ( $services as $service ) {
		if ( ! $service instanceof WP_Post ) {
			continue;
		}

		$record_key = lithia_get_post_record_key( (int) $service->ID );
		$providers  = lithia_get_related_relation_posts( (int) $service->ID, 'providers' );
		$provider_slugs = array_map(
			static function ( WP_Post $provider ): string {
				return (string) $provider->post_name;
			},
			$providers
		);

		$service_rows[] = array(
			'record_key'      => $record_key ? $record_key : 'service_' . sanitize_key( $service->post_name ),
			'title'           => $service->post_title,
			'slug'            => $service->post_name,
			'excerpt'         => (string) lithia_get_service_meta( (int) $service->ID, 'service_hero_text', lithia_get_post_fallback_summary( $service, 24 ) ),
			'timeline'        => (string) lithia_get_service_meta( (int) $service->ID, 'service_timeline', '' ),
			'delivery_mode'   => (string) lithia_get_service_meta( (int) $service->ID, 'service_delivery_mode', '' ),
			'platform_stack'  => (string) lithia_get_service_meta( (int) $service->ID, 'service_platform', '' ),
			'engagement_type' => (string) lithia_get_service_meta( (int) $service->ID, 'service_engagement_type', '' ),
			'provider_slugs'  => implode( ', ', array_filter( $provider_slugs ) ),
		);
	}

	$providers = get_posts(
		array(
			'post_type'      => 'providers',
			'post_status'    => array( 'publish', 'draft', 'private' ),
			'posts_per_page' => -1,
			'orderby'        => 'menu_order title',
			'order'          => 'ASC',
		)
	);

	foreach ( $providers as $provider ) {
		if ( ! $provider instanceof WP_Post ) {
			continue;
		}

		$record_key = lithia_get_post_record_key( (int) $provider->ID );

		$provider_rows[] = array(
			'record_key' => $record_key ? $record_key : 'provider_' . sanitize_key( $provider->post_name ),
			'title'      => $provider->post_title,
			'slug'       => $provider->post_name,
			'excerpt'    => lithia_get_post_fallback_summary( $provider, 20 ),
			'email'      => (string) get_post_meta( (int) $provider->ID, 'lithia_provider_email', true ),
			'phone'      => (string) get_post_meta( (int) $provider->ID, 'lithia_provider_phone', true ),
		);
	}

	return array(
		'basics' => array(
			'site_name'              => get_option( 'blogname', '' ),
			'site_tagline'           => get_option( 'blogdescription', '' ),
			'business_name'          => (string) lithia_get_business_detail( 'business_name', get_option( 'blogname', '' ) ),
			'business_phone'         => (string) lithia_get_business_detail( 'business_phone', '' ),
			'business_email'         => (string) lithia_get_business_detail( 'business_email', get_option( 'admin_email', '' ) ),
			'city'                   => (string) lithia_get_business_detail( 'city', '' ),
			'state_region'           => (string) lithia_get_business_detail( 'state_region', '' ),
			'booking_notice'         => (string) lithia_get_business_detail( 'booking_notice', '' ),
			'booking_enabled'        => 'yes',
			'provider_pages_enabled' => post_type_exists( 'providers' ) ? 'yes' : 'no',
		),
		'brand' => array(
			'intro_eyebrow'       => (string) lithia_get_brand_content( 'intro_eyebrow', '' ),
			'intro_heading'       => (string) lithia_get_brand_content( 'intro_heading', '' ),
			'intro_paragraph'     => (string) lithia_get_brand_content( 'intro_paragraph', '' ),
			'mission_statement'   => (string) lithia_get_brand_content( 'mission_statement', '' ),
			'about_summary'       => (string) lithia_get_brand_content( 'about_summary', '' ),
			'primary_cta_label'   => (string) lithia_get_brand_content( 'primary_cta_label', 'Book Appointment' ),
			'primary_cta_url'     => (string) lithia_get_brand_content( 'primary_cta_url', '/book-appointment/' ),
			'secondary_cta_label' => (string) lithia_get_brand_content( 'secondary_cta_label', 'Contact Us' ),
			'secondary_cta_url'   => (string) lithia_get_brand_content( 'secondary_cta_url', '/contact/' ),
			'background_color'    => (string) lithia_get_site_style( 'background_color', '#F7F4EE' ),
			'surface_color'       => (string) lithia_get_site_style( 'surface_color', '#EEE7DB' ),
			'text_color'          => (string) lithia_get_site_style( 'text_color', '#1F1F1F' ),
			'primary_color'       => (string) lithia_get_site_style( 'primary_color', '#2F4F46' ),
			'accent_color'        => (string) lithia_get_site_style( 'accent_color', '#B6864A' ),
		),
		'services'           => $service_rows,
		'providers'          => $provider_rows,
		'service_row_count'  => max( 6, count( $service_rows ) ),
		'provider_row_count' => max( 4, count( $provider_rows ) ),
	);
}

/**
 * Pad an array of rows to a target size.
 *
 * @param array $rows      Existing rows.
 * @param array $defaults  Row defaults.
 * @param int   $row_count Target row count.
 * @return array
 */
function lithia_launch_wizard_pad_rows( array $rows, array $defaults, int $row_count ): array {
	$normalized = array();

	foreach ( $rows as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}

		$normalized[] = wp_parse_args( $row, $defaults );
	}

	while ( count( $normalized ) < $row_count ) {
		$normalized[] = $defaults;
	}

	return array_slice( $normalized, 0, max( $row_count, count( $normalized ) ) );
}

/**
 * Return the current Launch Wizard state.
 *
 * @return array
 */
function lithia_get_launch_wizard_state(): array {
	$defaults = lithia_get_launch_wizard_defaults();
	$saved    = get_option( lithia_get_launch_wizard_draft_option_name(), array() );

	if ( ! is_array( $saved ) ) {
		$saved = array();
	}

	$state = array(
		'basics'  => wp_parse_args( $saved['basics'] ?? array(), $defaults['basics'] ),
		'brand'   => wp_parse_args( $saved['brand'] ?? array(), $defaults['brand'] ),
	);

	$service_row_count  = max( 6, absint( $saved['service_row_count'] ?? $defaults['service_row_count'] ?? 6 ) );
	$provider_row_count = max( 4, absint( $saved['provider_row_count'] ?? $defaults['provider_row_count'] ?? 4 ) );

	$state['services']           = lithia_launch_wizard_pad_rows( $saved['services'] ?? $defaults['services'], lithia_get_launch_wizard_service_row_defaults(), $service_row_count );
	$state['providers']          = lithia_launch_wizard_pad_rows( $saved['providers'] ?? $defaults['providers'], lithia_get_launch_wizard_provider_row_defaults(), $provider_row_count );
	$state['service_row_count']  = $service_row_count;
	$state['provider_row_count'] = $provider_row_count;

	return $state;
}

/**
 * Parse a slug list string.
 *
 * @param mixed $value Raw value.
 * @return array
 */
function lithia_launch_wizard_parse_slug_list( $value ): array {
	$value = lithia_seed_normalize_scalar( $value );

	if ( '' === $value ) {
		return array();
	}

	$parts = preg_split( '/[\s,\|\n\r]+/', $value );
	$parts = array_filter( array_map( 'sanitize_title', (array) $parts ) );

	return array_values( array_unique( $parts ) );
}

/**
 * Normalize a slug list into a display string.
 *
 * @param mixed $value Raw value.
 * @return string
 */
function lithia_launch_wizard_normalize_slug_list_string( $value ): string {
	return implode( ', ', lithia_launch_wizard_parse_slug_list( $value ) );
}

/**
 * Sanitize the Launch Wizard basics step values.
 *
 * @param array $input Raw values.
 * @return array
 */
function lithia_sanitize_launch_wizard_basics( array $input ): array {
	return array(
		'site_name'              => sanitize_text_field( lithia_seed_normalize_scalar( $input['site_name'] ?? '' ) ),
		'site_tagline'           => sanitize_text_field( lithia_seed_normalize_scalar( $input['site_tagline'] ?? '' ) ),
		'business_name'          => sanitize_text_field( lithia_seed_normalize_scalar( $input['business_name'] ?? '' ) ),
		'business_phone'         => sanitize_text_field( lithia_seed_normalize_scalar( $input['business_phone'] ?? '' ) ),
		'business_email'         => sanitize_email( lithia_seed_normalize_scalar( $input['business_email'] ?? '' ) ),
		'city'                   => sanitize_text_field( lithia_seed_normalize_scalar( $input['city'] ?? '' ) ),
		'state_region'           => sanitize_text_field( lithia_seed_normalize_scalar( $input['state_region'] ?? '' ) ),
		'booking_notice'         => sanitize_textarea_field( lithia_seed_normalize_scalar( $input['booking_notice'] ?? '' ) ),
		'booking_enabled'        => lithia_seed_normalize_flag( $input['booking_enabled'] ?? 'no' ),
		'provider_pages_enabled' => lithia_seed_normalize_flag( $input['provider_pages_enabled'] ?? 'no' ),
	);
}

/**
 * Sanitize the Launch Wizard brand step values.
 *
 * @param array $input Raw values.
 * @return array
 */
function lithia_sanitize_launch_wizard_brand( array $input ): array {
	$defaults = lithia_get_site_style_defaults();

	return array(
		'intro_eyebrow'       => sanitize_text_field( lithia_seed_normalize_scalar( $input['intro_eyebrow'] ?? '' ) ),
		'intro_heading'       => sanitize_text_field( lithia_seed_normalize_scalar( $input['intro_heading'] ?? '' ) ),
		'intro_paragraph'     => sanitize_textarea_field( lithia_seed_normalize_scalar( $input['intro_paragraph'] ?? '' ) ),
		'mission_statement'   => sanitize_textarea_field( lithia_seed_normalize_scalar( $input['mission_statement'] ?? '' ) ),
		'about_summary'       => sanitize_textarea_field( lithia_seed_normalize_scalar( $input['about_summary'] ?? '' ) ),
		'primary_cta_label'   => sanitize_text_field( lithia_seed_normalize_scalar( $input['primary_cta_label'] ?? '' ) ),
		'primary_cta_url'     => esc_url_raw( lithia_seed_normalize_scalar( $input['primary_cta_url'] ?? '' ) ),
		'secondary_cta_label' => sanitize_text_field( lithia_seed_normalize_scalar( $input['secondary_cta_label'] ?? '' ) ),
		'secondary_cta_url'   => esc_url_raw( lithia_seed_normalize_scalar( $input['secondary_cta_url'] ?? '' ) ),
		'background_color'    => lithia_sanitize_css_color_value( $input['background_color'] ?? '', $defaults['background_color'] ),
		'surface_color'       => lithia_sanitize_css_color_value( $input['surface_color'] ?? '', $defaults['surface_color'] ),
		'text_color'          => lithia_sanitize_css_color_value( $input['text_color'] ?? '', $defaults['text_color'] ),
		'primary_color'       => lithia_sanitize_css_color_value( $input['primary_color'] ?? '', $defaults['primary_color'] ),
		'accent_color'        => lithia_sanitize_css_color_value( $input['accent_color'] ?? '', $defaults['accent_color'] ),
	);
}

/**
 * Sanitize the Launch Wizard service rows.
 *
 * @param array $rows Raw rows.
 * @return array
 */
function lithia_sanitize_launch_wizard_service_rows( array $rows ): array {
	$sanitized_rows = array();

	foreach ( $rows as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}

		$title = sanitize_text_field( lithia_seed_normalize_scalar( $row['title'] ?? '' ) );
		$slug  = sanitize_title( lithia_seed_normalize_scalar( $row['slug'] ?? '' ) );

		if ( '' === $slug && '' !== $title ) {
			$slug = sanitize_title( $title );
		}

		$sanitized = array(
			'record_key'      => lithia_seed_normalize_record_key( $row['record_key'] ?? '' ),
			'title'           => $title,
			'slug'            => $slug,
			'excerpt'         => sanitize_textarea_field( lithia_seed_normalize_scalar( $row['excerpt'] ?? '' ) ),
			'timeline'        => sanitize_text_field( lithia_seed_normalize_scalar( $row['timeline'] ?? '' ) ),
			'delivery_mode'   => sanitize_text_field( lithia_seed_normalize_scalar( $row['delivery_mode'] ?? '' ) ),
			'platform_stack'  => sanitize_text_field( lithia_seed_normalize_scalar( $row['platform_stack'] ?? '' ) ),
			'engagement_type' => sanitize_text_field( lithia_seed_normalize_scalar( $row['engagement_type'] ?? '' ) ),
			'provider_slugs'  => lithia_launch_wizard_normalize_slug_list_string( $row['provider_slugs'] ?? '' ),
		);

		if ( '' === $sanitized['record_key'] && '' !== $sanitized['slug'] ) {
			$sanitized['record_key'] = 'service_' . sanitize_key( $sanitized['slug'] );
		}

		if ( '' === implode( '', $sanitized ) ) {
			continue;
		}

		$sanitized_rows[] = $sanitized;
	}

	return $sanitized_rows;
}

/**
 * Sanitize the Launch Wizard provider rows.
 *
 * @param array $rows Raw rows.
 * @return array
 */
function lithia_sanitize_launch_wizard_provider_rows( array $rows ): array {
	$sanitized_rows = array();

	foreach ( $rows as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}

		$title = sanitize_text_field( lithia_seed_normalize_scalar( $row['title'] ?? '' ) );
		$slug  = sanitize_title( lithia_seed_normalize_scalar( $row['slug'] ?? '' ) );

		if ( '' === $slug && '' !== $title ) {
			$slug = sanitize_title( $title );
		}

		$sanitized = array(
			'record_key' => lithia_seed_normalize_record_key( $row['record_key'] ?? '' ),
			'title'      => $title,
			'slug'       => $slug,
			'excerpt'    => sanitize_textarea_field( lithia_seed_normalize_scalar( $row['excerpt'] ?? '' ) ),
			'email'      => sanitize_email( lithia_seed_normalize_scalar( $row['email'] ?? '' ) ),
			'phone'      => sanitize_text_field( lithia_seed_normalize_scalar( $row['phone'] ?? '' ) ),
		);

		if ( '' === $sanitized['record_key'] && '' !== $sanitized['slug'] ) {
			$sanitized['record_key'] = 'provider_' . sanitize_key( $sanitized['slug'] );
		}

		if ( '' === implode( '', $sanitized ) ) {
			continue;
		}

		$sanitized_rows[] = $sanitized;
	}

	return $sanitized_rows;
}

/**
 * Build basic Gutenberg paragraph content for generated posts.
 *
 * @param string $text Content text.
 * @return string
 */
function lithia_launch_wizard_build_paragraph_content( string $text ): string {
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
 * Find an existing post for a Launch Wizard row.
 *
 * @param string $post_type   Post type.
 * @param string $record_key  Stable record key.
 * @param string $slug        Post slug.
 * @return int
 */
function lithia_launch_wizard_find_post_id( string $post_type, string $record_key, string $slug ): int {
	if ( '' !== $record_key ) {
		$post_id = lithia_get_post_id_by_record_key( $record_key, $post_type );

		if ( $post_id ) {
			return $post_id;
		}
	}

	if ( '' !== $slug ) {
		$existing = get_page_by_path( $slug, OBJECT, $post_type );

		if ( $existing instanceof WP_Post ) {
			return (int) $existing->ID;
		}
	}

	return 0;
}

/**
 * Upsert provider rows from the Launch Wizard.
 *
 * @param array $rows Provider rows.
 * @return array
 */
function lithia_launch_wizard_upsert_provider_rows( array $rows ): array {
	$results = array();

	foreach ( $rows as $row ) {
		$title = trim( (string) ( $row['title'] ?? '' ) );
		$slug  = trim( (string) ( $row['slug'] ?? '' ) );

		if ( '' === $title && '' === $slug ) {
			continue;
		}

		if ( '' === $slug && '' !== $title ) {
			$slug = sanitize_title( $title );
		}

		if ( '' === $title && '' !== $slug ) {
			$title = ucwords( str_replace( '-', ' ', $slug ) );
		}

		$record_key = lithia_seed_normalize_record_key( $row['record_key'] ?? '' );
		$post_id    = lithia_launch_wizard_find_post_id( 'providers', $record_key, $slug );
		$content    = lithia_launch_wizard_build_paragraph_content( (string) ( $row['excerpt'] ?? '' ) );

		$postarr = array(
			'post_type'    => 'providers',
			'post_status'  => 'publish',
			'post_title'   => $title,
			'post_name'    => $slug,
			'post_excerpt' => (string) ( $row['excerpt'] ?? '' ),
		);

		if ( $post_id ) {
			$postarr['ID'] = $post_id;
			$current_post  = get_post( $post_id );

			if ( $current_post instanceof WP_Post && '' === trim( (string) $current_post->post_content ) && '' !== $content ) {
				$postarr['post_content'] = $content;
			}

			$result = wp_update_post( wp_slash( $postarr ), true );
		} else {
			$postarr['post_content'] = $content;
			$result                  = wp_insert_post( wp_slash( $postarr ), true );
		}

		if ( is_wp_error( $result ) || ! $result ) {
			continue;
		}

		$post_id = (int) $result;

		if ( $record_key ) {
			lithia_assign_post_record_key( $post_id, $record_key );
		}

		update_post_meta( $post_id, 'lithia_provider_email', sanitize_email( (string) ( $row['email'] ?? '' ) ) );
		update_post_meta( $post_id, 'lithia_provider_phone', sanitize_text_field( (string) ( $row['phone'] ?? '' ) ) );

		$results[ $record_key ? $record_key : 'provider_' . sanitize_key( $slug ) ] = array(
			'post_id' => $post_id,
			'slug'    => $slug,
			'title'   => $title,
		);
	}

	return $results;
}

/**
 * Upsert service rows from the Launch Wizard.
 *
 * @param array $rows Service rows.
 * @return array
 */
function lithia_launch_wizard_upsert_service_rows( array $rows ): array {
	$results = array();

	foreach ( $rows as $row ) {
		$title = trim( (string) ( $row['title'] ?? '' ) );
		$slug  = trim( (string) ( $row['slug'] ?? '' ) );

		if ( '' === $title && '' === $slug ) {
			continue;
		}

		if ( '' === $slug && '' !== $title ) {
			$slug = sanitize_title( $title );
		}

		if ( '' === $title && '' !== $slug ) {
			$title = ucwords( str_replace( '-', ' ', $slug ) );
		}

		$record_key = lithia_seed_normalize_record_key( $row['record_key'] ?? '' );
		$post_id    = lithia_launch_wizard_find_post_id( 'services', $record_key, $slug );
		$excerpt    = (string) ( $row['excerpt'] ?? '' );
		$content    = lithia_launch_wizard_build_paragraph_content( $excerpt );

		$postarr = array(
			'post_type'    => 'services',
			'post_status'  => 'publish',
			'post_title'   => $title,
			'post_name'    => $slug,
			'post_excerpt' => $excerpt,
		);

		if ( $post_id ) {
			$postarr['ID'] = $post_id;
			$current_post  = get_post( $post_id );

			if ( $current_post instanceof WP_Post && '' === trim( (string) $current_post->post_content ) && '' !== $content ) {
				$postarr['post_content'] = $content;
			}

			$result = wp_update_post( wp_slash( $postarr ), true );
		} else {
			$postarr['post_content'] = $content;
			$result                  = wp_insert_post( wp_slash( $postarr ), true );
		}

		if ( is_wp_error( $result ) || ! $result ) {
			continue;
		}

		$post_id = (int) $result;

		if ( $record_key ) {
			lithia_assign_post_record_key( $post_id, $record_key );
		}

		update_post_meta( $post_id, 'service_hero_title', $title );
		update_post_meta( $post_id, 'service_hero_eyebrow', 'Service' );
		update_post_meta( $post_id, 'service_hero_text', $excerpt );
		update_post_meta( $post_id, 'service_overview_heading', 'What This Service Covers' );
		update_post_meta( $post_id, 'service_overview_text', $excerpt );
		update_post_meta( $post_id, 'service_timeline', sanitize_text_field( (string) ( $row['timeline'] ?? '' ) ) );
		update_post_meta( $post_id, 'service_delivery_mode', sanitize_text_field( (string) ( $row['delivery_mode'] ?? '' ) ) );
		update_post_meta( $post_id, 'service_platform', sanitize_text_field( (string) ( $row['platform_stack'] ?? '' ) ) );
		update_post_meta( $post_id, 'service_engagement_type', sanitize_text_field( (string) ( $row['engagement_type'] ?? '' ) ) );

		$results[ $record_key ? $record_key : 'service_' . sanitize_key( $slug ) ] = array(
			'post_id'        => $post_id,
			'slug'           => $slug,
			'title'          => $title,
			'provider_slugs' => lithia_launch_wizard_parse_slug_list( $row['provider_slugs'] ?? '' ),
		);
	}

	return $results;
}

/**
 * Build a canonical project payload from Launch Wizard state.
 *
 * @param array $state Launch Wizard state.
 * @return array
 */
function lithia_build_project_payload_from_launch_wizard_state( array $state ): array {
	$site_name = sanitize_text_field( (string) ( $state['basics']['site_name'] ?? '' ) );
	$site_key  = $site_name ? sanitize_title( $site_name ) : sanitize_title( get_option( 'blogname', 'lithia-project' ) );

	$offers = array();

	foreach ( (array) ( $state['services'] ?? array() ) as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}

		$title = sanitize_text_field( (string) ( $row['title'] ?? '' ) );
		$slug  = sanitize_title( (string) ( $row['slug'] ?? '' ) );

		if ( '' === $title && '' === $slug ) {
			continue;
		}

		if ( '' === $slug && '' !== $title ) {
			$slug = sanitize_title( $title );
		}

		$offers[] = array(
			'record_key'      => lithia_seed_normalize_record_key( $row['record_key'] ?? 'service_' . sanitize_key( $slug ) ),
			'title'           => $title,
			'slug'            => $slug,
			'summary'         => sanitize_textarea_field( (string) ( $row['excerpt'] ?? '' ) ),
			'duration'        => sanitize_text_field( (string) ( $row['timeline'] ?? '' ) ),
			'delivery_mode'   => sanitize_text_field( (string) ( $row['delivery_mode'] ?? '' ) ),
			'platform_stack'  => sanitize_text_field( (string) ( $row['platform_stack'] ?? '' ) ),
			'engagement_type' => sanitize_text_field( (string) ( $row['engagement_type'] ?? '' ) ),
			'provider_slugs'  => lithia_project_import_normalize_slug_list( $row['provider_slugs'] ?? '' ),
		);
	}

	$providers = array();

	foreach ( (array) ( $state['providers'] ?? array() ) as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}

		$title = sanitize_text_field( (string) ( $row['title'] ?? '' ) );
		$slug  = sanitize_title( (string) ( $row['slug'] ?? '' ) );

		if ( '' === $title && '' === $slug ) {
			continue;
		}

		if ( '' === $slug && '' !== $title ) {
			$slug = sanitize_title( $title );
		}

		$providers[] = array(
			'record_key' => lithia_seed_normalize_record_key( $row['record_key'] ?? 'provider_' . sanitize_key( $slug ) ),
			'title'      => $title,
			'slug'       => $slug,
			'summary'    => sanitize_textarea_field( (string) ( $row['excerpt'] ?? '' ) ),
			'email'      => sanitize_email( (string) ( $row['email'] ?? '' ) ),
			'phone'      => sanitize_text_field( (string) ( $row['phone'] ?? '' ) ),
		);
	}

	return array(
		'project' => array(
			'schema_version' => lithia_get_project_import_schema_version(),
			'template_key'   => lithia_get_project_import_default_template_key(),
			'site_key'       => $site_key,
			'industry'       => 'service-business',
			'review_state'   => 'approved',
		),
		'business' => array(
			'brand_name'           => sanitize_text_field( (string) ( $state['basics']['business_name'] ?? $site_name ) ),
			'short_tagline'        => sanitize_text_field( (string) ( $state['brand']['intro_heading'] ?? '' ) ),
			'phone'                => sanitize_text_field( (string) ( $state['basics']['business_phone'] ?? '' ) ),
			'email'                => sanitize_email( (string) ( $state['basics']['business_email'] ?? '' ) ),
			'primary_cta_label'    => sanitize_text_field( (string) ( $state['brand']['primary_cta_label'] ?? '' ) ),
			'primary_cta_target'   => esc_url_raw( (string) ( $state['brand']['primary_cta_url'] ?? '' ) ),
			'secondary_cta_label'  => sanitize_text_field( (string) ( $state['brand']['secondary_cta_label'] ?? '' ) ),
			'secondary_cta_target' => esc_url_raw( (string) ( $state['brand']['secondary_cta_url'] ?? '' ) ),
		),
		'location' => array(
			'city'         => sanitize_text_field( (string) ( $state['basics']['city'] ?? '' ) ),
			'state_region' => sanitize_text_field( (string) ( $state['basics']['state_region'] ?? '' ) ),
		),
		'booking' => array(
			'calendar_enabled' => 'yes' === ( $state['basics']['booking_enabled'] ?? 'no' ),
			'booking_notice'   => sanitize_textarea_field( (string) ( $state['basics']['booking_notice'] ?? '' ) ),
		),
		'site_settings' => array(
			'site_name'              => $state['basics']['site_name'] ?? '',
			'site_tagline'           => $state['basics']['site_tagline'] ?? '',
			'site_key'               => $site_key,
			'booking_enabled'        => $state['basics']['booking_enabled'] ?? 'no',
			'provider_pages_enabled' => $state['basics']['provider_pages_enabled'] ?? 'no',
		),
		'business_details' => array(
			'business_name'  => $state['basics']['business_name'] ?? '',
			'business_phone' => $state['basics']['business_phone'] ?? '',
			'business_email' => $state['basics']['business_email'] ?? '',
			'city'           => $state['basics']['city'] ?? '',
			'state_region'   => $state['basics']['state_region'] ?? '',
			'booking_notice' => $state['basics']['booking_notice'] ?? '',
		),
		'brand_content' => array(
			'intro_eyebrow'       => $state['brand']['intro_eyebrow'] ?? '',
			'intro_heading'       => $state['brand']['intro_heading'] ?? '',
			'intro_paragraph'     => $state['brand']['intro_paragraph'] ?? '',
			'mission_statement'   => $state['brand']['mission_statement'] ?? '',
			'about_summary'       => $state['brand']['about_summary'] ?? '',
			'primary_cta_label'   => $state['brand']['primary_cta_label'] ?? '',
			'primary_cta_url'     => $state['brand']['primary_cta_url'] ?? '',
			'secondary_cta_label' => $state['brand']['secondary_cta_label'] ?? '',
			'secondary_cta_url'   => $state['brand']['secondary_cta_url'] ?? '',
		),
		'site_styles' => array(
			'background_color' => $state['brand']['background_color'] ?? '',
			'surface_color'    => $state['brand']['surface_color'] ?? '',
			'text_color'       => $state['brand']['text_color'] ?? '',
			'primary_color'    => $state['brand']['primary_color'] ?? '',
			'accent_color'     => $state['brand']['accent_color'] ?? '',
		),
		'providers' => $providers,
		'offers'    => $offers,
	);
}

/**
 * Apply the Launch Wizard draft to the live site.
 *
 * @param array $state Launch Wizard state.
 * @return array
 */
function lithia_apply_launch_wizard_draft( array $state ): array {
	$payload = lithia_build_project_payload_from_launch_wizard_state( $state );
	$report  = lithia_import_project_payload(
		$payload,
		array(
			'source' => 'launch_wizard',
		)
	);

	if ( empty( $report['success'] ) ) {
		return array(
			'applied_at'         => current_time( 'mysql' ),
			'error'              => $report['error'] ?? 'Project import failed.',
			'services_upserted'  => 0,
			'providers_upserted' => 0,
			'pages_imported'     => 0,
			'relationships_synced' => 0,
			'review_state'       => lithia_get_project_context()['review_state'] ?? 'intake',
		);
	}

	$offer_summary    = $report['summary']['offers'] ?? array();
	$provider_summary = $report['summary']['providers'] ?? array();
	$page_summary     = $report['summary']['pages'] ?? array();

	$services_upserted = (int) ( $offer_summary['created'] ?? 0 ) + (int) ( $offer_summary['updated'] ?? 0 ) + (int) ( $offer_summary['noop'] ?? 0 );
	$providers_upserted = (int) ( $provider_summary['created'] ?? 0 ) + (int) ( $provider_summary['updated'] ?? 0 ) + (int) ( $provider_summary['noop'] ?? 0 );
	$pages_imported     = (int) ( $page_summary['created'] ?? 0 ) + (int) ( $page_summary['updated'] ?? 0 ) + (int) ( $page_summary['noop'] ?? 0 );

	$summary = array(
		'applied_at'         => current_time( 'mysql' ),
		'sync_report'        => $report['site_sync'] ?? array(),
		'project_report'     => $report,
		'services_upserted'  => $services_upserted,
		'providers_upserted' => $providers_upserted,
		'pages_imported'     => $pages_imported,
		'relationships_synced' => (int) ( $report['relationships_synced'] ?? 0 ),
		'review_state'       => sanitize_text_field( (string) ( $report['context']['review_state'] ?? 'imported' ) ),
	);

	update_option( 'lithia_launch_wizard_last_summary', $summary, false );

	return $summary;
}

/**
 * Register the Launch Wizard page under Appearance.
 *
 * @return void
 */
function lithia_register_launch_wizard_page(): void {
	add_theme_page(
		__( 'Launch Wizard', 'lithia-web-service-theme' ),
		__( 'Launch Wizard', 'lithia-web-service-theme' ),
		'edit_theme_options',
		lithia_get_launch_wizard_page_slug(),
		'lithia_render_launch_wizard_page'
	);
}
add_action( 'admin_menu', 'lithia_register_launch_wizard_page' );

/**
 * Enqueue Launch Wizard admin assets.
 *
 * @param string $hook_suffix Current admin page hook.
 * @return void
 */
function lithia_enqueue_launch_wizard_assets( string $hook_suffix ): void {
	if ( 'appearance_page_' . lithia_get_launch_wizard_page_slug() !== $hook_suffix ) {
		return;
	}

	wp_enqueue_style(
		'lithia-launch-wizard',
		get_theme_file_uri( 'assets/css/admin-launch-wizard.css' ),
		array(),
		wp_get_theme()->get( 'Version' )
	);
}
add_action( 'admin_enqueue_scripts', 'lithia_enqueue_launch_wizard_assets' );

/**
 * Handle Launch Wizard form submissions.
 *
 * @return void
 */
function lithia_handle_launch_wizard_post(): void {
	if ( empty( $_POST['action'] ) || 'lithia_save_launch_wizard' !== $_POST['action'] ) {
		return;
	}

	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to manage the Launch Wizard.', 'lithia-web-service-theme' ) );
	}

	check_admin_referer( 'lithia_launch_wizard' );

	$step   = lithia_get_launch_wizard_step( (string) ( $_POST['step'] ?? 'basics' ) );
	$intent = sanitize_key( (string) ( $_POST['intent'] ?? 'save' ) );
	$state  = lithia_get_launch_wizard_state();

	switch ( $step ) {
		case 'basics':
			$state['basics'] = lithia_sanitize_launch_wizard_basics( $_POST['wizard']['basics'] ?? array() );
			break;
		case 'brand':
			$state['brand'] = lithia_sanitize_launch_wizard_brand( $_POST['wizard']['brand'] ?? array() );
			break;
		case 'services':
			$state['service_row_count'] = max( 6, absint( $_POST['service_row_count'] ?? $state['service_row_count'] ?? 6 ) );
			$state['services']          = lithia_sanitize_launch_wizard_service_rows( $_POST['wizard']['services'] ?? array() );
			break;
		case 'providers':
			$state['provider_row_count'] = max( 4, absint( $_POST['provider_row_count'] ?? $state['provider_row_count'] ?? 4 ) );
			$state['providers']          = lithia_sanitize_launch_wizard_provider_rows( $_POST['wizard']['providers'] ?? array() );
			break;
	}

	$steps      = array_keys( lithia_get_launch_wizard_steps() );
	$step_index = array_search( $step, $steps, true );
	$redirect_step = $step;
	$notice_args   = array();

	if ( 'add_service_row' === $intent ) {
		$state['service_row_count'] = max( $state['service_row_count'] + 1, 6 );
		$redirect_step              = 'services';
		$notice_args['wizard_saved'] = '1';
	} elseif ( 'add_provider_row' === $intent ) {
		$state['provider_row_count'] = max( $state['provider_row_count'] + 1, 4 );
		$redirect_step               = 'providers';
		$notice_args['wizard_saved'] = '1';
	} elseif ( 'back' === $intent ) {
		$redirect_step              = $steps[ max( 0, $step_index - 1 ) ];
		$notice_args['wizard_saved'] = '1';
	} elseif ( 'next' === $intent ) {
		$redirect_step              = $steps[ min( count( $steps ) - 1, $step_index + 1 ) ];
		$notice_args['wizard_saved'] = '1';
	} elseif ( 'apply' === $intent ) {
		update_option( lithia_get_launch_wizard_draft_option_name(), $state, false );
		lithia_apply_launch_wizard_draft( $state );

		wp_safe_redirect(
			lithia_get_launch_wizard_step_url(
				'review',
				array(
					'wizard_applied' => '1',
				)
			)
		);
		exit;
	} elseif ( 'reset' === $intent ) {
		delete_option( lithia_get_launch_wizard_draft_option_name() );

		wp_safe_redirect(
			lithia_get_launch_wizard_step_url(
				'basics',
				array(
					'wizard_reset' => '1',
				)
			)
		);
		exit;
	} else {
		$notice_args['wizard_saved'] = '1';
	}

	update_option( lithia_get_launch_wizard_draft_option_name(), $state, false );

	wp_safe_redirect( lithia_get_launch_wizard_step_url( $redirect_step, $notice_args ) );
	exit;
}
add_action( 'admin_post_lithia_save_launch_wizard', 'lithia_handle_launch_wizard_post' );

/**
 * Render a Launch Wizard text input field.
 *
 * @param string $name  Field name.
 * @param string $value Field value.
 * @param string $label Field label.
 * @param string $type  Input type.
 * @return void
 */
function lithia_render_launch_wizard_input( string $name, string $value, string $label, string $type = 'text' ): void {
	?>
	<label class="lw-wizard-field">
		<span class="lw-wizard-field__label"><?php echo esc_html( $label ); ?></span>
		<input type="<?php echo esc_attr( $type ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>" class="regular-text" />
	</label>
	<?php
}

/**
 * Render a Launch Wizard textarea field.
 *
 * @param string $name  Field name.
 * @param string $value Field value.
 * @param string $label Field label.
 * @param int    $rows  Row count.
 * @return void
 */
function lithia_render_launch_wizard_textarea( string $name, string $value, string $label, int $rows = 4 ): void {
	?>
	<label class="lw-wizard-field">
		<span class="lw-wizard-field__label"><?php echo esc_html( $label ); ?></span>
		<textarea name="<?php echo esc_attr( $name ); ?>" rows="<?php echo esc_attr( (string) $rows ); ?>" class="large-text"><?php echo esc_textarea( $value ); ?></textarea>
	</label>
	<?php
}

/**
 * Render a yes/no select field.
 *
 * @param string $name  Field name.
 * @param string $value Field value.
 * @param string $label Field label.
 * @return void
 */
function lithia_render_launch_wizard_yes_no_field( string $name, string $value, string $label ): void {
	?>
	<label class="lw-wizard-field">
		<span class="lw-wizard-field__label"><?php echo esc_html( $label ); ?></span>
		<select name="<?php echo esc_attr( $name ); ?>">
			<option value="yes" <?php selected( $value, 'yes' ); ?>><?php esc_html_e( 'Yes', 'lithia-web-service-theme' ); ?></option>
			<option value="no" <?php selected( $value, 'no' ); ?>><?php esc_html_e( 'No', 'lithia-web-service-theme' ); ?></option>
		</select>
	</label>
	<?php
}

/**
 * Render the Launch Wizard actions.
 *
 * @param string $step Current step.
 * @return void
 */
function lithia_render_launch_wizard_actions( string $step ): void {
	$steps      = array_keys( lithia_get_launch_wizard_steps() );
	$step_index = array_search( $step, $steps, true );
	$is_first   = 0 === $step_index;
	$is_last    = ( count( $steps ) - 1 ) === $step_index;
	?>
	<div class="lw-wizard-actions">
		<?php if ( ! $is_first ) : ?>
			<button type="submit" class="button button-secondary" name="intent" value="back"><?php esc_html_e( 'Save & Go Back', 'lithia-web-service-theme' ); ?></button>
		<?php endif; ?>

		<button type="submit" class="button button-secondary" name="intent" value="save"><?php esc_html_e( 'Save Draft', 'lithia-web-service-theme' ); ?></button>

		<?php if ( ! $is_last ) : ?>
			<button type="submit" class="button button-primary" name="intent" value="next"><?php esc_html_e( 'Save & Continue', 'lithia-web-service-theme' ); ?></button>
		<?php else : ?>
			<button type="submit" class="button button-primary" name="intent" value="apply"><?php esc_html_e( 'Apply To Site', 'lithia-web-service-theme' ); ?></button>
		<?php endif; ?>

		<button type="submit" class="button-link-delete" name="intent" value="reset" onclick="return confirm('<?php echo esc_js( __( 'Reset the Launch Wizard draft back to the current site values?', 'lithia-web-service-theme' ) ); ?>');"><?php esc_html_e( 'Reset Draft', 'lithia-web-service-theme' ); ?></button>
	</div>
	<?php
}

/**
 * Render the Launch Wizard page.
 *
 * @return void
 */
function lithia_render_launch_wizard_page(): void {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}

	$state  = lithia_get_launch_wizard_state();
	$step   = lithia_get_launch_wizard_step( (string) ( $_GET['step'] ?? 'basics' ) );
	$steps  = lithia_get_launch_wizard_steps();
	$summary = get_option( 'lithia_launch_wizard_last_summary', array() );
	?>
	<div class="wrap lw-launch-wizard-page">
		<h1><?php esc_html_e( 'Launch Wizard', 'lithia-web-service-theme' ); ?></h1>
		<p class="description"><?php esc_html_e( 'Use this wizard to stage a quick-launch setup for the starter site. It saves a draft as you go and applies the final values to the live site structure.', 'lithia-web-service-theme' ); ?></p>

		<?php if ( ! empty( $_GET['wizard_saved'] ) ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Launch Wizard draft saved.', 'lithia-web-service-theme' ); ?></p></div>
		<?php endif; ?>

		<?php if ( ! empty( $_GET['wizard_applied'] ) ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Launch Wizard values were applied to the site.', 'lithia-web-service-theme' ); ?></p></div>
		<?php endif; ?>

		<?php if ( ! empty( $_GET['wizard_reset'] ) ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Launch Wizard draft reset to the current site values.', 'lithia-web-service-theme' ); ?></p></div>
		<?php endif; ?>

		<nav class="lw-wizard-steps" aria-label="<?php esc_attr_e( 'Launch Wizard Steps', 'lithia-web-service-theme' ); ?>">
			<?php foreach ( $steps as $step_key => $step_config ) : ?>
				<a class="lw-wizard-step<?php echo $step === $step_key ? ' is-current' : ''; ?>" href="<?php echo esc_url( lithia_get_launch_wizard_step_url( $step_key ) ); ?>">
					<span class="lw-wizard-step__label"><?php echo esc_html( $step_config['label'] ); ?></span>
					<span class="lw-wizard-step__description"><?php echo esc_html( $step_config['description'] ); ?></span>
				</a>
			<?php endforeach; ?>
		</nav>

		<form class="lw-wizard-panel" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'lithia_launch_wizard' ); ?>
			<input type="hidden" name="action" value="lithia_save_launch_wizard" />
			<input type="hidden" name="step" value="<?php echo esc_attr( $step ); ?>" />

			<header class="lw-wizard-panel__header">
				<h2><?php echo esc_html( $steps[ $step ]['label'] ); ?></h2>
				<p><?php echo esc_html( $steps[ $step ]['description'] ); ?></p>
			</header>

			<?php if ( 'basics' === $step ) : ?>
				<div class="lw-wizard-grid lw-wizard-grid--two">
					<?php lithia_render_launch_wizard_input( 'wizard[basics][site_name]', $state['basics']['site_name'], 'Site Name' ); ?>
					<?php lithia_render_launch_wizard_input( 'wizard[basics][site_tagline]', $state['basics']['site_tagline'], 'Site Tagline' ); ?>
					<?php lithia_render_launch_wizard_input( 'wizard[basics][business_name]', $state['basics']['business_name'], 'Business Name' ); ?>
					<?php lithia_render_launch_wizard_input( 'wizard[basics][business_phone]', $state['basics']['business_phone'], 'Business Phone' ); ?>
					<?php lithia_render_launch_wizard_input( 'wizard[basics][business_email]', $state['basics']['business_email'], 'Business Email', 'email' ); ?>
					<?php lithia_render_launch_wizard_input( 'wizard[basics][city]', $state['basics']['city'], 'City' ); ?>
					<?php lithia_render_launch_wizard_input( 'wizard[basics][state_region]', $state['basics']['state_region'], 'State / Region' ); ?>
					<?php lithia_render_launch_wizard_yes_no_field( 'wizard[basics][booking_enabled]', $state['basics']['booking_enabled'], 'Enable Booking' ); ?>
					<?php lithia_render_launch_wizard_yes_no_field( 'wizard[basics][provider_pages_enabled]', $state['basics']['provider_pages_enabled'], 'Enable Provider Pages' ); ?>
				</div>
				<?php lithia_render_launch_wizard_textarea( 'wizard[basics][booking_notice]', $state['basics']['booking_notice'], 'Booking Notice', 3 ); ?>

			<?php elseif ( 'brand' === $step ) : ?>
				<div class="lw-wizard-grid lw-wizard-grid--two">
					<?php lithia_render_launch_wizard_input( 'wizard[brand][intro_eyebrow]', $state['brand']['intro_eyebrow'], 'Intro Eyebrow' ); ?>
					<?php lithia_render_launch_wizard_input( 'wizard[brand][intro_heading]', $state['brand']['intro_heading'], 'Intro Heading' ); ?>
				</div>
				<?php lithia_render_launch_wizard_textarea( 'wizard[brand][intro_paragraph]', $state['brand']['intro_paragraph'], 'Intro Paragraph', 4 ); ?>
				<?php lithia_render_launch_wizard_textarea( 'wizard[brand][mission_statement]', $state['brand']['mission_statement'], 'Mission Statement', 4 ); ?>
				<?php lithia_render_launch_wizard_textarea( 'wizard[brand][about_summary]', $state['brand']['about_summary'], 'About Summary', 4 ); ?>
				<div class="lw-wizard-grid lw-wizard-grid--two">
					<?php lithia_render_launch_wizard_input( 'wizard[brand][primary_cta_label]', $state['brand']['primary_cta_label'], 'Primary CTA Label' ); ?>
					<?php lithia_render_launch_wizard_input( 'wizard[brand][primary_cta_url]', $state['brand']['primary_cta_url'], 'Primary CTA URL', 'url' ); ?>
					<?php lithia_render_launch_wizard_input( 'wizard[brand][secondary_cta_label]', $state['brand']['secondary_cta_label'], 'Secondary CTA Label' ); ?>
					<?php lithia_render_launch_wizard_input( 'wizard[brand][secondary_cta_url]', $state['brand']['secondary_cta_url'], 'Secondary CTA URL', 'url' ); ?>
				</div>
				<div class="lw-wizard-grid lw-wizard-grid--five">
					<?php lithia_render_launch_wizard_input( 'wizard[brand][background_color]', $state['brand']['background_color'], 'Background Color', 'text' ); ?>
					<?php lithia_render_launch_wizard_input( 'wizard[brand][surface_color]', $state['brand']['surface_color'], 'Surface Color', 'text' ); ?>
					<?php lithia_render_launch_wizard_input( 'wizard[brand][text_color]', $state['brand']['text_color'], 'Text Color', 'text' ); ?>
					<?php lithia_render_launch_wizard_input( 'wizard[brand][primary_color]', $state['brand']['primary_color'], 'Primary Color', 'text' ); ?>
					<?php lithia_render_launch_wizard_input( 'wizard[brand][accent_color]', $state['brand']['accent_color'], 'Accent Color', 'text' ); ?>
				</div>

			<?php elseif ( 'services' === $step ) : ?>
				<input type="hidden" name="service_row_count" value="<?php echo esc_attr( (string) $state['service_row_count'] ); ?>" />
				<div class="lw-wizard-card-list">
					<?php foreach ( $state['services'] as $index => $row ) : ?>
						<div class="lw-wizard-card">
							<input type="hidden" name="<?php echo esc_attr( "wizard[services][{$index}][record_key]" ); ?>" value="<?php echo esc_attr( $row['record_key'] ); ?>" />
							<div class="lw-wizard-card__header">
								<h3><?php printf( esc_html__( 'Service %d', 'lithia-web-service-theme' ), (int) $index + 1 ); ?></h3>
							</div>
							<div class="lw-wizard-grid lw-wizard-grid--two">
								<?php lithia_render_launch_wizard_input( "wizard[services][{$index}][title]", $row['title'], 'Title' ); ?>
								<?php lithia_render_launch_wizard_input( "wizard[services][{$index}][slug]", $row['slug'], 'Slug' ); ?>
								<?php lithia_render_launch_wizard_input( "wizard[services][{$index}][timeline]", $row['timeline'], 'Timeline' ); ?>
								<?php lithia_render_launch_wizard_input( "wizard[services][{$index}][delivery_mode]", $row['delivery_mode'], 'Delivery Mode' ); ?>
								<?php lithia_render_launch_wizard_input( "wizard[services][{$index}][platform_stack]", $row['platform_stack'], 'Platform / Stack' ); ?>
								<?php lithia_render_launch_wizard_input( "wizard[services][{$index}][engagement_type]", $row['engagement_type'], 'Engagement Type' ); ?>
								<?php lithia_render_launch_wizard_input( "wizard[services][{$index}][provider_slugs]", $row['provider_slugs'], 'Provider Slugs' ); ?>
							</div>
							<?php lithia_render_launch_wizard_textarea( "wizard[services][{$index}][excerpt]", $row['excerpt'], 'Short Summary', 3 ); ?>
						</div>
					<?php endforeach; ?>
				</div>
				<div class="lw-wizard-inline-actions">
					<button type="submit" class="button button-secondary" name="intent" value="add_service_row"><?php esc_html_e( 'Add Service Row', 'lithia-web-service-theme' ); ?></button>
				</div>

			<?php elseif ( 'providers' === $step ) : ?>
				<input type="hidden" name="provider_row_count" value="<?php echo esc_attr( (string) $state['provider_row_count'] ); ?>" />
				<div class="lw-wizard-card-list">
					<?php foreach ( $state['providers'] as $index => $row ) : ?>
						<div class="lw-wizard-card">
							<input type="hidden" name="<?php echo esc_attr( "wizard[providers][{$index}][record_key]" ); ?>" value="<?php echo esc_attr( $row['record_key'] ); ?>" />
							<div class="lw-wizard-card__header">
								<h3><?php printf( esc_html__( 'Provider %d', 'lithia-web-service-theme' ), (int) $index + 1 ); ?></h3>
							</div>
							<div class="lw-wizard-grid lw-wizard-grid--two">
								<?php lithia_render_launch_wizard_input( "wizard[providers][{$index}][title]", $row['title'], 'Name' ); ?>
								<?php lithia_render_launch_wizard_input( "wizard[providers][{$index}][slug]", $row['slug'], 'Slug' ); ?>
								<?php lithia_render_launch_wizard_input( "wizard[providers][{$index}][email]", $row['email'], 'Email', 'email' ); ?>
								<?php lithia_render_launch_wizard_input( "wizard[providers][{$index}][phone]", $row['phone'], 'Phone' ); ?>
							</div>
							<?php lithia_render_launch_wizard_textarea( "wizard[providers][{$index}][excerpt]", $row['excerpt'], 'Short Summary', 3 ); ?>
						</div>
					<?php endforeach; ?>
				</div>
				<div class="lw-wizard-inline-actions">
					<button type="submit" class="button button-secondary" name="intent" value="add_provider_row"><?php esc_html_e( 'Add Provider Row', 'lithia-web-service-theme' ); ?></button>
				</div>

			<?php else : ?>
				<div class="lw-wizard-review">
					<div class="lw-wizard-review__panel">
						<h3><?php esc_html_e( 'Site Basics', 'lithia-web-service-theme' ); ?></h3>
						<ul>
							<li><strong><?php esc_html_e( 'Site Name:', 'lithia-web-service-theme' ); ?></strong> <?php echo esc_html( $state['basics']['site_name'] ); ?></li>
							<li><strong><?php esc_html_e( 'Business Name:', 'lithia-web-service-theme' ); ?></strong> <?php echo esc_html( $state['basics']['business_name'] ); ?></li>
							<li><strong><?php esc_html_e( 'City:', 'lithia-web-service-theme' ); ?></strong> <?php echo esc_html( $state['basics']['city'] ); ?></li>
							<li><strong><?php esc_html_e( 'Booking Enabled:', 'lithia-web-service-theme' ); ?></strong> <?php echo esc_html( ucfirst( $state['basics']['booking_enabled'] ) ); ?></li>
						</ul>
					</div>

					<div class="lw-wizard-review__panel">
						<h3><?php esc_html_e( 'Brand', 'lithia-web-service-theme' ); ?></h3>
						<ul>
							<li><strong><?php esc_html_e( 'Intro Heading:', 'lithia-web-service-theme' ); ?></strong> <?php echo esc_html( $state['brand']['intro_heading'] ); ?></li>
							<li><strong><?php esc_html_e( 'Primary CTA:', 'lithia-web-service-theme' ); ?></strong> <?php echo esc_html( $state['brand']['primary_cta_label'] ); ?></li>
							<li><strong><?php esc_html_e( 'Primary Color:', 'lithia-web-service-theme' ); ?></strong> <?php echo esc_html( $state['brand']['primary_color'] ); ?></li>
						</ul>
					</div>

					<div class="lw-wizard-review__panel">
						<h3><?php esc_html_e( 'Services', 'lithia-web-service-theme' ); ?></h3>
						<ul>
							<?php foreach ( array_filter( $state['services'], static fn( $row ) => ! empty( $row['title'] ) || ! empty( $row['slug'] ) ) as $row ) : ?>
								<li><?php echo esc_html( $row['title'] ? $row['title'] : $row['slug'] ); ?></li>
							<?php endforeach; ?>
						</ul>
					</div>

					<div class="lw-wizard-review__panel">
						<h3><?php esc_html_e( 'Providers', 'lithia-web-service-theme' ); ?></h3>
						<ul>
							<?php foreach ( array_filter( $state['providers'], static fn( $row ) => ! empty( $row['title'] ) || ! empty( $row['slug'] ) ) as $row ) : ?>
								<li><?php echo esc_html( $row['title'] ? $row['title'] : $row['slug'] ); ?></li>
							<?php endforeach; ?>
						</ul>
					</div>

					<?php if ( ! empty( $summary['applied_at'] ) ) : ?>
						<div class="lw-wizard-review__panel">
							<h3><?php esc_html_e( 'Last Apply', 'lithia-web-service-theme' ); ?></h3>
							<ul>
								<li><strong><?php esc_html_e( 'Applied At:', 'lithia-web-service-theme' ); ?></strong> <?php echo esc_html( $summary['applied_at'] ); ?></li>
								<li><strong><?php esc_html_e( 'Review State:', 'lithia-web-service-theme' ); ?></strong> <?php echo esc_html( (string) ( $summary['review_state'] ?? 'imported' ) ); ?></li>
								<li><strong><?php esc_html_e( 'Pages Imported:', 'lithia-web-service-theme' ); ?></strong> <?php echo esc_html( (string) ( $summary['pages_imported'] ?? 0 ) ); ?></li>
								<li><strong><?php esc_html_e( 'Services Upserted:', 'lithia-web-service-theme' ); ?></strong> <?php echo esc_html( (string) ( $summary['services_upserted'] ?? 0 ) ); ?></li>
								<li><strong><?php esc_html_e( 'Providers Upserted:', 'lithia-web-service-theme' ); ?></strong> <?php echo esc_html( (string) ( $summary['providers_upserted'] ?? 0 ) ); ?></li>
								<li><strong><?php esc_html_e( 'Relationships Synced:', 'lithia-web-service-theme' ); ?></strong> <?php echo esc_html( (string) ( $summary['relationships_synced'] ?? 0 ) ); ?></li>
								<?php if ( ! empty( $summary['error'] ) ) : ?>
									<li><strong><?php esc_html_e( 'Error:', 'lithia-web-service-theme' ); ?></strong> <?php echo esc_html( (string) $summary['error'] ); ?></li>
								<?php endif; ?>
							</ul>
						</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<?php lithia_render_launch_wizard_actions( $step ); ?>
		</form>
	</div>
	<?php
}
