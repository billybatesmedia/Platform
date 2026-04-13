<?php
/**
 * Managed standard pages and contact-form helpers.
 *
 * @package LithiaWebServiceTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get the managed contact form title.
 *
 * @return string
 */
function lithia_get_contact_form_title(): string {
	return 'Contact Page Form';
}

/**
 * Get the managed contact form block content.
 *
 * @return string
 */
function lithia_get_contact_form_content(): string {
	return implode(
		"\n\n",
		array(
			'<!-- wp:jet-forms/text-field {"label":"Name","name":"contact_name","required":true} /-->',
			'<!-- wp:jet-forms/text-field {"field_type":"email","label":"Email address","name":"contact_email","required":true} /-->',
			'<!-- wp:jet-forms/text-field {"field_type":"tel","label":"Phone","name":"contact_phone"} /-->',
			'<!-- wp:jet-forms/textarea-field {"label":"How can we help?","name":"contact_message","required":true} /-->',
			'<!-- wp:jet-forms/submit-field {"label":"Send Message"} /-->',
		)
	) . "\n";
}

/**
 * Get the managed contact form action meta.
 *
 * @return array
 */
function lithia_get_contact_form_meta(): array {
	$target_email = sanitize_email( (string) lithia_get_business_detail( 'business_email', get_option( 'admin_email', '' ) ) );
	$from_email   = sanitize_email( (string) get_option( 'admin_email', $target_email ) );

	if ( ! $from_email ) {
		$from_email = $target_email;
	}

	$actions = array(
		array(
			'type'     => 'send_email',
			'id'       => 9102,
			'settings' => array(
				'send_email' => array(
					'mail_to'          => $target_email ? 'custom' : 'admin',
					'custom_email'     => $target_email,
					'reply_to'         => 'form',
					'reply_from_field' => 'contact_email',
					'subject'          => 'New contact request from %contact_name%',
					'from_name'        => wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ),
					'from_address'     => $from_email,
					'content_type'     => 'text/html',
					'content'          => implode(
						'',
						array(
							'<p><strong>Name:</strong> %contact_name%</p>',
							'<p><strong>Email:</strong> %contact_email%</p>',
							'<p><strong>Phone:</strong> %contact_phone%</p>',
							'<p><strong>Message:</strong></p>',
							'<p>%contact_message%</p>',
						)
					),
				),
			),
		),
	);

	return array(
		'_jf_actions'               => wp_json_encode( $actions ),
		'_lithia_theme_managed_key' => 'contact_page_form',
	);
}

/**
 * Get the existing managed contact form ID.
 *
 * @return int
 */
function lithia_get_contact_form_id(): int {
	static $cached_id = null;

	if ( null !== $cached_id ) {
		return $cached_id;
	}

	if ( ! post_type_exists( 'jet-form-builder' ) ) {
		$cached_id = 0;
		return $cached_id;
	}

	$managed_forms = get_posts(
		array(
			'post_type'      => 'jet-form-builder',
			'post_status'    => array( 'publish', 'draft', 'private' ),
			'posts_per_page' => 1,
			'meta_key'       => '_lithia_theme_managed_key',
			'meta_value'     => 'contact_page_form',
			'fields'         => 'ids',
			'orderby'        => 'ID',
			'order'          => 'ASC',
		)
	);

	if ( ! empty( $managed_forms[0] ) ) {
		$cached_id = (int) $managed_forms[0];
		return $cached_id;
	}

	$title = lithia_get_contact_form_title();
	$forms = get_posts(
		array(
			'post_type'      => 'jet-form-builder',
			'post_status'    => array( 'publish', 'draft', 'private' ),
			'posts_per_page' => -1,
			'orderby'        => 'ID',
			'order'          => 'ASC',
		)
	);

	foreach ( $forms as $form ) {
		if ( $form instanceof WP_Post && $title === $form->post_title ) {
			$cached_id = (int) $form->ID;
			return $cached_id;
		}
	}

	$cached_id = 0;
	return $cached_id;
}

/**
 * Ensure the managed contact form exists.
 *
 * @return int
 */
function lithia_ensure_contact_form(): int {
	if ( ! post_type_exists( 'jet-form-builder' ) || ! lithia_can_sync_managed_content() ) {
		return 0;
	}

	$form_id      = lithia_get_contact_form_id();
	$form_payload = array(
		'post_type'    => 'jet-form-builder',
		'post_status'  => 'publish',
		'post_title'   => lithia_get_contact_form_title(),
		'post_name'    => 'contact-page-form',
		'post_content' => lithia_get_contact_form_content(),
	);

	if ( $form_id ) {
		$form_payload['ID'] = $form_id;
		$result             = wp_update_post( wp_slash( $form_payload ), true );
	} else {
		$result = wp_insert_post( wp_slash( $form_payload ), true );
	}

	if ( is_wp_error( $result ) || ! $result ) {
		return 0;
	}

	$form_id = (int) $result;

	foreach ( lithia_get_contact_form_meta() as $meta_key => $meta_value ) {
		update_post_meta( $form_id, $meta_key, $meta_value );
	}

	clean_post_cache( $form_id );

	return $form_id;
}

/**
 * Seed the managed contact form in wp-admin.
 *
 * @return void
 */
function lithia_maybe_seed_contact_form(): void {
	if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
		return;
	}

	lithia_ensure_contact_form();
}
add_action( 'admin_init', 'lithia_maybe_seed_contact_form', 24 );

/**
 * Render the managed contact form.
 *
 * @return string
 */
function lithia_render_managed_contact_form(): string {
	if ( ! post_type_exists( 'jet-form-builder' ) || ! function_exists( 'do_blocks' ) ) {
		return '';
	}

	$form_id = lithia_get_contact_form_id();

	if ( ! $form_id && current_user_can( 'manage_options' ) ) {
		$form_id = lithia_ensure_contact_form();
	}

	if ( ! $form_id ) {
		return '';
	}

	return (string) do_blocks(
		sprintf(
			'<!-- wp:jet-forms/form-block {"form_id":%d} /-->',
			$form_id
		)
	);
}

/**
 * Format a stored phone number for display.
 *
 * @param string $phone Raw phone number.
 * @return string
 */
function lithia_format_phone_number( string $phone ): string {
	$phone  = trim( $phone );
	$digits = preg_replace( '/\D+/', '', $phone );

	if ( 10 === strlen( $digits ) ) {
		return sprintf(
			'(%s) %s-%s',
			substr( $digits, 0, 3 ),
			substr( $digits, 3, 3 ),
			substr( $digits, 6, 4 )
		);
	}

	if ( 11 === strlen( $digits ) && '1' === $digits[0] ) {
		return sprintf(
			'+1 (%s) %s-%s',
			substr( $digits, 1, 3 ),
			substr( $digits, 4, 3 ),
			substr( $digits, 7, 4 )
		);
	}

	return $phone;
}

/**
 * Build the business address lines from options data.
 *
 * @return array
 */
function lithia_get_business_address_lines(): array {
	$lines = array();

	$street = trim( (string) lithia_get_business_detail( 'street_address', '' ) );
	$line_2 = trim( (string) lithia_get_business_detail( 'address_line_2', '' ) );
	$city   = trim( (string) lithia_get_business_detail( 'city', '' ) );
	$state  = trim( (string) lithia_get_business_detail( 'state_region', '' ) );
	$postal = trim( (string) lithia_get_business_detail( 'postal_code', '' ) );
	$country = trim( (string) lithia_get_business_detail( 'country', '' ) );

	if ( '' !== $street ) {
		$lines[] = $street;
	}

	if ( '' !== $line_2 ) {
		$lines[] = $line_2;
	}

	$city_line_parts = array_filter(
		array(
			$city,
			trim( $state . ' ' . $postal ),
		)
	);

	if ( ! empty( $city_line_parts ) ) {
		$lines[] = implode( ', ', $city_line_parts );
	}

	if ( '' !== $country ) {
		$lines[] = $country;
	}

	return array_values( array_filter( $lines ) );
}

/**
 * Return the default About page block content.
 *
 * @return string
 */
function lithia_get_default_about_page_content(): string {
	return implode(
		"\n\n",
		array(
			'<!-- wp:lithia/brand-intro {"tone":"light","showPrimaryCta":true,"showSecondaryCta":true,"primaryLabel":"Book Appointment","primaryUrl":"/book-appointment/","secondaryLabel":"Contact Us","secondaryUrl":"/contact/"} /-->',
			'<!-- wp:lithia/mission-statement {"tone":"dark","label":"Approach"} /-->',
			'<!-- wp:lithia/about-summary {"tone":"light","eyebrow":"About Lithia Web","heading":"Dependable WordPress support for small businesses","showButton":true,"buttonLabel":"Contact Us","buttonUrl":"/contact/"} /-->',
			'<!-- wp:lithia/brand-cta-pair {"tone":"dark","showPrimaryCta":true,"showSecondaryCta":true,"primaryLabel":"Book Appointment","primaryUrl":"/book-appointment/","secondaryLabel":"Contact Us","secondaryUrl":"/contact/"} /-->',
		)
	) . "\n";
}

/**
 * Return the default Contact page block content.
 *
 * @return string
 */
function lithia_get_default_contact_page_content(): string {
	return implode(
		"\n",
		array(
			'<!-- wp:columns {"align":"wide","className":"lithia-contact-page-columns"} -->',
			'<div class="wp-block-columns alignwide lithia-contact-page-columns">',
			'<!-- wp:column -->',
			'<div class="wp-block-column">',
			'<!-- wp:lithia/contact-details {"tone":"light","heading":"Contact Details","intro":"Reach out with questions, project details, or next steps. We will follow up with the right recommendation for your website.","showHours":true,"showNotice":true} /-->',
			'</div>',
			'<!-- /wp:column -->',
			'<!-- wp:column -->',
			'<div class="wp-block-column">',
			'<!-- wp:lithia/contact-form {"tone":"dark","heading":"Send a Message","intro":"Tell us what you need and we will follow up by email."} /-->',
			'</div>',
			'<!-- /wp:column -->',
			'</div>',
			'<!-- /wp:columns -->',
		)
	) . "\n";
}

/**
 * Return managed standard page definitions.
 *
 * @return array
 */
function lithia_get_managed_standard_pages(): array {
	return array(
		'about_page'   => array(
			'managed_key' => 'managed_about_page',
			'title'       => 'About',
			'slug'        => 'about',
			'content'     => lithia_get_default_about_page_content(),
		),
		'contact_page' => array(
			'managed_key' => 'managed_contact_page',
			'title'       => 'Contact',
			'slug'        => 'contact',
			'content'     => lithia_get_default_contact_page_content(),
		),
	);
}

/**
 * Find a managed page ID by internal key.
 *
 * @param string $managed_key Managed page key.
 * @return int
 */
function lithia_get_managed_page_id( string $managed_key ): int {
	$page_ids = get_posts(
		array(
			'post_type'      => 'page',
			'post_status'    => array( 'publish', 'draft', 'private' ),
			'posts_per_page' => 1,
			'meta_key'       => '_lithia_theme_managed_key',
			'meta_value'     => $managed_key,
			'fields'         => 'ids',
			'orderby'        => 'ID',
			'order'          => 'ASC',
		)
	);

	return ! empty( $page_ids[0] ) ? (int) $page_ids[0] : 0;
}

/**
 * Ensure a standard managed page exists.
 *
 * @param array $page_config Managed page configuration.
 * @return int
 */
function lithia_ensure_managed_standard_page( array $page_config ): int {
	if ( ! lithia_can_sync_managed_content() ) {
		return 0;
	}

	$managed_key = (string) ( $page_config['managed_key'] ?? '' );
	$title       = (string) ( $page_config['title'] ?? '' );
	$slug        = (string) ( $page_config['slug'] ?? '' );
	$content     = (string) ( $page_config['content'] ?? '' );

	if ( '' === $managed_key || '' === $title || '' === $slug ) {
		return 0;
	}

	$page_id = lithia_get_managed_page_id( $managed_key );

	if ( ! $page_id ) {
		$existing_page = get_page_by_path( $slug, OBJECT, 'page' );

		if ( $existing_page instanceof WP_Post ) {
			$page_id = (int) $existing_page->ID;

			if ( '' === trim( (string) $existing_page->post_content ) ) {
				wp_update_post(
					wp_slash(
						array(
							'ID'           => $page_id,
							'post_content' => $content,
						)
					)
				);
			}
		}
	}

	if ( ! $page_id ) {
		$page_id = wp_insert_post(
			wp_slash(
				array(
					'post_type'    => 'page',
					'post_status'  => 'publish',
					'post_title'   => $title,
					'post_name'    => $slug,
					'post_content' => $content,
				)
			),
			true
		);

		if ( is_wp_error( $page_id ) || ! $page_id ) {
			return 0;
		}

		$page_id = (int) $page_id;
	}

	update_post_meta( $page_id, '_lithia_theme_managed_key', $managed_key );

	clean_post_cache( $page_id );

	return $page_id;
}

/**
 * Ensure the default About and Contact pages exist.
 *
 * @return void
 */
function lithia_ensure_standard_pages(): void {
	foreach ( lithia_get_managed_standard_pages() as $page_config ) {
		lithia_ensure_managed_standard_page( $page_config );
	}
}

/**
 * Seed standard pages in wp-admin when missing.
 *
 * @return void
 */
function lithia_maybe_seed_standard_pages(): void {
	if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
		return;
	}

	lithia_ensure_standard_pages();
}
add_action( 'admin_init', 'lithia_maybe_seed_standard_pages', 25 );
