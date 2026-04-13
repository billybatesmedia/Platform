<?php
/**
 * Theme bootstrap for Lithia Web Service Theme.
 *
 * @package LithiaWebServiceTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Version theme assets by file modification time to avoid stale browser caches.
 *
 * @param string $relative_path Theme-relative asset path.
 * @return string
 */
function lithia_get_theme_asset_version( string $relative_path ): string {
	$absolute_path = get_theme_file_path( ltrim( $relative_path, '/' ) );

	if ( is_readable( $absolute_path ) ) {
		$modified = filemtime( $absolute_path );

		if ( false !== $modified ) {
			return (string) $modified;
		}
	}

	return (string) wp_get_theme()->get( 'Version' );
}

require_once get_theme_file_path( 'inc/site-styles.php' );
require_once get_theme_file_path( 'inc/site-docs.php' );
require_once get_theme_file_path( 'inc/services.php' );
require_once get_theme_file_path( 'inc/pages.php' );
require_once get_theme_file_path( 'inc/wp-all-import.php' );
require_once get_theme_file_path( 'inc/seed-sync.php' );
require_once get_theme_file_path( 'inc/project-importer.php' );
require_once get_theme_file_path( 'inc/launch-wizard.php' );
require_once get_theme_file_path( 'inc/project-admin.php' );
require_once get_theme_file_path( 'inc/template-hardening.php' );
require_once get_theme_file_path( 'inc/blocks.php' );

function lithia_web_service_theme_setup(): void {
	add_theme_support( 'woocommerce' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'responsive-embeds' );
	add_editor_style( 'assets/css/editor.css' );
}
add_action( 'after_setup_theme', 'lithia_web_service_theme_setup' );

/**
 * Enqueue the shared theme stylesheet for both frontend and block editor.
 */
function lithia_enqueue_theme_base_styles(): void {
	$dependencies  = array();

	if ( wp_style_is( 'lithia-block-primitives', 'registered' ) ) {
		$dependencies[] = 'lithia-block-primitives';
	}

	if ( wp_style_is( 'global-styles', 'registered' ) ) {
		$dependencies[] = 'global-styles';
	}

	wp_enqueue_style(
		'lithia-theme-base',
		get_theme_file_uri( 'style.css' ),
		$dependencies,
		lithia_get_theme_asset_version( 'style.css' )
	);
}
add_action( 'enqueue_block_assets', 'lithia_enqueue_theme_base_styles', 30 );

/**
 * Allow SVG uploads for site administrators.
 *
 * SVG files can contain active content, so this stays limited to trusted admins.
 *
 * @param array<string, string> $mimes Allowed mime types.
 * @return array<string, string>
 */
function lithia_allow_svg_uploads( array $mimes ): array {
	if ( ! current_user_can( 'manage_options' ) ) {
		return $mimes;
	}

	$mimes['svg']  = 'image/svg+xml';
	$mimes['svgz'] = 'image/svg+xml';

	return $mimes;
}
add_filter( 'upload_mimes', 'lithia_allow_svg_uploads' );

/**
 * Normalize SVG file type detection during uploads.
 *
 * @param array<string, mixed> $data     File type data.
 * @param string               $file     Full path to the uploaded file.
 * @param string               $filename Original filename.
 * @param array<string, string>|null $mimes Allowed mime types.
 * @return array<string, mixed>
 */
function lithia_fix_svg_filetype( array $data, string $file, string $filename, ?array $mimes ): array {
	$extension = strtolower( (string) pathinfo( $filename, PATHINFO_EXTENSION ) );

	if ( ! in_array( $extension, array( 'svg', 'svgz' ), true ) ) {
		return $data;
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		return $data;
	}

	$data['ext']  = $extension;
	$data['type'] = 'image/svg+xml';

	if ( empty( $data['proper_filename'] ) ) {
		$data['proper_filename'] = wp_basename( $file );
	}

	return $data;
}
add_filter( 'wp_check_filetype_and_ext', 'lithia_fix_svg_filetype', 10, 4 );

/**
 * Read basic SVG dimensions from the file markup.
 *
 * @param string $file_path Absolute SVG path.
 * @return array{width:int,height:int}
 */
function lithia_get_svg_dimensions( string $file_path ): array {
	$dimensions = array(
		'width'  => 0,
		'height' => 0,
	);

	if ( ! is_readable( $file_path ) ) {
		return $dimensions;
	}

	$svg = file_get_contents( $file_path );

	if ( false === $svg || '' === $svg ) {
		return $dimensions;
	}

	if ( preg_match( '/\bwidth=["\']([\d.]+)(px)?["\']/i', $svg, $width_match ) ) {
		$dimensions['width'] = (int) round( (float) $width_match[1] );
	}

	if ( preg_match( '/\bheight=["\']([\d.]+)(px)?["\']/i', $svg, $height_match ) ) {
		$dimensions['height'] = (int) round( (float) $height_match[1] );
	}

	if (
		( 0 === $dimensions['width'] || 0 === $dimensions['height'] ) &&
		preg_match( '/\bviewBox=["\']\s*[-\d.]+\s+[-\d.]+\s+([\d.]+)\s+([\d.]+)\s*["\']/i', $svg, $viewbox_match )
	) {
		if ( 0 === $dimensions['width'] ) {
			$dimensions['width'] = (int) round( (float) $viewbox_match[1] );
		}

		if ( 0 === $dimensions['height'] ) {
			$dimensions['height'] = (int) round( (float) $viewbox_match[2] );
		}
	}

	if ( $dimensions['width'] <= 0 ) {
		$dimensions['width'] = 512;
	}

	if ( $dimensions['height'] <= 0 ) {
		$dimensions['height'] = 512;
	}

	return $dimensions;
}

/**
 * Populate SVG attachments with dimensions so the media library can preview them.
 *
 * @param array<string, mixed> $response   Attachment response for the media modal.
 * @param WP_Post              $attachment Attachment post object.
 * @param mixed                $meta       Attachment meta.
 * @return array<string, mixed>
 */
function lithia_prepare_svg_for_media_modal( array $response, WP_Post $attachment, $meta ): array {
	if ( 'image/svg+xml' !== get_post_mime_type( $attachment ) ) {
		return $response;
	}

	$file_path = get_attached_file( $attachment->ID );

	if ( ! $file_path ) {
		return $response;
	}

	$dimensions = lithia_get_svg_dimensions( $file_path );
	$src        = wp_get_attachment_url( $attachment->ID );

	if ( ! $src ) {
		return $response;
	}

	$response['icon']    = $src;
	$response['type']    = 'image';
	$response['subtype'] = 'svg+xml';
	$response['width']   = $dimensions['width'];
	$response['height']  = $dimensions['height'];
	$response['sizes']   = array(
		'full' => array(
			'url'         => $src,
			'width'       => $dimensions['width'],
			'height'      => $dimensions['height'],
			'orientation' => $dimensions['width'] > $dimensions['height']
				? 'landscape'
				: ( $dimensions['width'] < $dimensions['height'] ? 'portrait' : 'square' ),
		),
	);

	return $response;
}
add_filter( 'wp_prepare_attachment_for_js', 'lithia_prepare_svg_for_media_modal', 10, 3 );

/**
 * Return the default JetEngine Business Details options page configuration.
 */
function lithia_get_business_details_options_page_config(): array {
	return array(
		'general_settings' => array(
			'name'             => 'Business Details',
			'slug'             => 'business-details',
			'menu_name'        => 'Business Details',
			'parent'           => 'jet-engine',
			'icon'             => 'dashicons-building',
			'capability'       => 'manage_options',
			'position'         => '',
			'storage_type'     => 'default',
			'option_prefix'    => true,
			'autoload_option'  => true,
			'hide_field_names' => false,
		),
		'fields' => array(
			array(
				'object_type' => 'field',
				'title'       => 'Business Name',
				'name'        => 'business_name',
				'type'        => 'text',
				'placeholder' => 'Lithia Web Service',
			),
			array(
				'object_type' => 'field',
				'title'       => 'Business Phone',
				'name'        => 'business_phone',
				'type'        => 'text',
				'placeholder' => '(555) 555-5555',
			),
			array(
				'object_type' => 'field',
				'title'       => 'Business Email',
				'name'        => 'business_email',
				'type'        => 'text',
				'placeholder' => 'hello@example.com',
			),
			array(
				'object_type' => 'field',
				'title'       => 'Street Address',
				'name'        => 'street_address',
				'type'        => 'text',
				'placeholder' => '123 Main Street',
			),
			array(
				'object_type' => 'field',
				'title'       => 'Address Line 2',
				'name'        => 'address_line_2',
				'type'        => 'text',
				'placeholder' => 'Suite 200',
			),
			array(
				'object_type' => 'field',
				'title'       => 'City',
				'name'        => 'city',
				'type'        => 'text',
				'placeholder' => 'Portland',
			),
			array(
				'object_type' => 'field',
				'title'       => 'State / Region',
				'name'        => 'state_region',
				'type'        => 'text',
				'placeholder' => 'Oregon',
			),
			array(
				'object_type' => 'field',
				'title'       => 'Postal Code',
				'name'        => 'postal_code',
				'type'        => 'text',
				'placeholder' => '97201',
			),
			array(
				'object_type' => 'field',
				'title'       => 'Country',
				'name'        => 'country',
				'type'        => 'text',
				'placeholder' => 'United States',
			),
			array(
				'object_type' => 'field',
				'title'       => 'Business Hours',
				'name'        => 'business_hours',
				'type'        => 'textarea',
				'placeholder' => "Mon-Fri: 8am-5pm\nSat: By appointment",
			),
			array(
				'object_type' => 'field',
				'title'       => 'Booking Notice',
				'name'        => 'booking_notice',
				'type'        => 'textarea',
				'placeholder' => 'Add a short note that can be reused near appointment forms.',
			),
		),
	);
}

/**
 * Return the default JetEngine Brand Content options page configuration.
 */
function lithia_get_brand_content_options_page_config(): array {
	return array(
		'general_settings' => array(
			'name'             => 'Brand Content',
			'slug'             => 'brand-content',
			'menu_name'        => 'Brand Content',
			'parent'           => 'jet-engine',
			'icon'             => 'dashicons-format-status',
			'capability'       => 'manage_options',
			'position'         => '',
			'storage_type'     => 'default',
			'option_prefix'    => true,
			'autoload_option'  => true,
			'hide_field_names' => false,
		),
		'fields' => array(
			array(
				'object_type' => 'field',
				'title'       => 'Intro Eyebrow',
				'name'        => 'intro_eyebrow',
				'type'        => 'text',
				'placeholder' => 'Who we are',
			),
			array(
				'object_type' => 'field',
				'title'       => 'Intro Heading',
				'name'        => 'intro_heading',
				'type'        => 'text',
				'placeholder' => 'Service built around real relationships',
			),
			array(
				'object_type' => 'field',
				'title'       => 'Intro Paragraph',
				'name'        => 'intro_paragraph',
				'type'        => 'textarea',
				'placeholder' => 'Write the core intro copy you want to reuse on the homepage and About page.',
			),
			array(
				'object_type' => 'field',
				'title'       => 'Mission Statement',
				'name'        => 'mission_statement',
				'type'        => 'textarea',
				'placeholder' => 'State the mission in a concise, repeatable way.',
			),
			array(
				'object_type' => 'field',
				'title'       => 'About Summary',
				'name'        => 'about_summary',
				'type'        => 'textarea',
				'placeholder' => 'Shorter About Us summary for teasers and supporting sections.',
			),
			array(
				'object_type' => 'field',
				'title'       => 'Primary CTA Label',
				'name'        => 'primary_cta_label',
				'type'        => 'text',
				'placeholder' => 'Book Appointment',
			),
			array(
				'object_type' => 'field',
				'title'       => 'Primary CTA URL',
				'name'        => 'primary_cta_url',
				'type'        => 'text',
				'placeholder' => '/book-appointment/',
			),
			array(
				'object_type' => 'field',
				'title'       => 'Secondary CTA Label',
				'name'        => 'secondary_cta_label',
				'type'        => 'text',
				'placeholder' => 'About Us',
			),
			array(
				'object_type' => 'field',
				'title'       => 'Secondary CTA URL',
				'name'        => 'secondary_cta_url',
				'type'        => 'text',
				'placeholder' => '/about-us/',
			),
			array(
				'object_type' => 'field',
				'title'       => 'Homepage About Eyebrow',
				'name'        => 'homepage_about_eyebrow',
				'type'        => 'text',
				'placeholder' => 'Who Lithia Web Helps',
			),
			array(
				'object_type' => 'field',
				'title'       => 'Homepage About Heading',
				'name'        => 'homepage_about_heading',
				'type'        => 'text',
				'placeholder' => 'A WordPress partner for the full website lifecycle',
			),
			array(
				'object_type' => 'field',
				'title'       => 'Homepage About Text',
				'name'        => 'homepage_about_text',
				'type'        => 'textarea',
				'placeholder' => 'Short summary for the homepage about section.',
			),
			array(
				'object_type' => 'field',
				'title'       => 'Homepage About Image',
				'name'        => 'homepage_about_image_id',
				'type'        => 'media',
				'value_format' => 'id',
			),
			array(
				'object_type' => 'field',
				'title'       => 'Homepage About Image Alt',
				'name'        => 'homepage_about_image_alt',
				'type'        => 'text',
				'placeholder' => 'Descriptive image alt text',
			),
			array(
				'object_type' => 'field',
				'title'       => 'Homepage About Button Label',
				'name'        => 'homepage_about_button_label',
				'type'        => 'text',
				'placeholder' => 'View Services',
			),
			array(
				'object_type' => 'field',
				'title'       => 'Homepage About Button URL',
				'name'        => 'homepage_about_button_url',
				'type'        => 'text',
				'placeholder' => '/services/',
			),
			array(
				'object_type' => 'field',
				'title'       => 'Homepage Spotlight Eyebrow',
				'name'        => 'homepage_spotlight_eyebrow',
				'type'        => 'text',
				'placeholder' => 'Service Spotlight',
			),
			array(
				'object_type' => 'field',
				'title'       => 'Homepage Spotlight Heading',
				'name'        => 'homepage_spotlight_heading',
				'type'        => 'text',
				'placeholder' => 'Start with one focused service',
			),
			array(
				'object_type' => 'field',
				'title'       => 'Homepage Spotlight Intro',
				'name'        => 'homepage_spotlight_intro',
				'type'        => 'textarea',
				'placeholder' => 'Intro copy for the homepage spotlight slider.',
			),
			array(
				'object_type' => 'field',
				'title'       => 'Homepage Spotlight Archive Label',
				'name'        => 'homepage_spotlight_archive_label',
				'type'        => 'text',
				'placeholder' => 'View All Services',
			),
			array(
				'object_type' => 'field',
				'title'       => 'Homepage Spotlight Archive URL',
				'name'        => 'homepage_spotlight_archive_url',
				'type'        => 'text',
				'placeholder' => '/services/',
			),
			array(
				'object_type' => 'field',
				'title'       => 'Homepage Services Eyebrow',
				'name'        => 'homepage_services_eyebrow',
				'type'        => 'text',
				'placeholder' => 'Services',
			),
			array(
				'object_type' => 'field',
				'title'       => 'Homepage Services Heading',
				'name'        => 'homepage_services_heading',
				'type'        => 'text',
				'placeholder' => 'Support for Every Stage of Your Website',
			),
			array(
				'object_type' => 'field',
				'title'       => 'Homepage Services Intro',
				'name'        => 'homepage_services_intro',
				'type'        => 'textarea',
				'placeholder' => 'Intro copy above the homepage services grid.',
			),
			array(
				'object_type' => 'field',
				'title'       => 'Homepage Mission Label',
				'name'        => 'homepage_mission_label',
				'type'        => 'text',
				'placeholder' => 'How Lithia Web Works',
			),
			array(
				'object_type' => 'field',
				'title'       => 'Homepage Mission Text',
				'name'        => 'homepage_mission_text',
				'type'        => 'textarea',
				'placeholder' => 'Mission copy for the homepage statement section.',
			),
			array(
				'object_type' => 'field',
				'title'       => 'Homepage Mission Image',
				'name'        => 'homepage_mission_image_id',
				'type'        => 'media',
				'value_format' => 'id',
			),
			array(
				'object_type' => 'field',
				'title'       => 'Homepage Mission Image Alt',
				'name'        => 'homepage_mission_image_alt',
				'type'        => 'text',
				'placeholder' => 'Descriptive image alt text',
			),
			array(
				'object_type' => 'field',
				'title'       => 'Homepage Primary CTA Label',
				'name'        => 'homepage_primary_cta_label',
				'type'        => 'text',
				'placeholder' => 'Book Appointment',
			),
			array(
				'object_type' => 'field',
				'title'       => 'Homepage Primary CTA URL',
				'name'        => 'homepage_primary_cta_url',
				'type'        => 'text',
				'placeholder' => '/book-appointment/',
			),
			array(
				'object_type' => 'field',
				'title'       => 'Homepage Secondary CTA Label',
				'name'        => 'homepage_secondary_cta_label',
				'type'        => 'text',
				'placeholder' => 'Contact Us',
			),
			array(
				'object_type' => 'field',
				'title'       => 'Homepage Secondary CTA URL',
				'name'        => 'homepage_secondary_cta_url',
				'type'        => 'text',
				'placeholder' => '/contact/',
			),
		),
	);
}

/**
 * Return all theme-managed JetEngine options page configurations.
 */
function lithia_get_options_page_configs(): array {
	return array(
		'business-details' => lithia_get_business_details_options_page_config(),
		'brand-content'    => lithia_get_brand_content_options_page_config(),
	);
}

/**
 * Determine whether the current request can manage theme seed data.
 *
 * @return bool
 */
function lithia_can_manage_theme_seed_data(): bool {
	return current_user_can( 'manage_options' ) || ( defined( 'WP_CLI' ) && WP_CLI );
}

/**
 * Return a single theme-managed options page config by slug.
 *
 * @param string $page_slug Options page slug.
 * @return array
 */
function lithia_get_options_page_config( string $page_slug ): array {
	$configs = lithia_get_options_page_configs();

	return $configs[ $page_slug ] ?? array();
}

/**
 * Return the defined fields for a managed options page.
 *
 * @param string $page_slug Options page slug.
 * @return array
 */
function lithia_get_options_page_fields( string $page_slug ): array {
	$config = lithia_get_options_page_config( $page_slug );
	$fields = $config['fields'] ?? array();

	return is_array( $fields ) ? $fields : array();
}

/**
 * Sanitize a managed options page field value.
 *
 * @param array $field Field configuration.
 * @param mixed $value Raw field value.
 * @return string
 */
function lithia_sanitize_options_page_field_value( array $field, $value ): string {
	if ( is_array( $value ) || is_object( $value ) ) {
		return '';
	}

	$type  = $field['type'] ?? 'text';
	$value = wp_unslash( (string) $value );

	if ( in_array( $type, array( 'textarea', 'wysiwyg' ), true ) ) {
		return sanitize_textarea_field( $value );
	}

	return sanitize_text_field( $value );
}

/**
 * Prepare an options page payload for saving.
 *
 * @param string $page_slug       Options page slug.
 * @param array  $values          Incoming field values.
 * @param bool   $merge_existing  Whether to merge into the current option value.
 * @return array
 */
function lithia_prepare_options_page_values( string $page_slug, array $values, bool $merge_existing = true ): array {
	$prepared = $merge_existing ? get_option( $page_slug, array() ) : array();

	if ( ! is_array( $prepared ) ) {
		$prepared = array();
	}

	foreach ( lithia_get_options_page_fields( $page_slug ) as $field ) {
		$field_name = $field['name'] ?? '';

		if ( ! $field_name || ! array_key_exists( $field_name, $values ) ) {
			continue;
		}

		$prepared[ $field_name ] = lithia_sanitize_options_page_field_value( $field, $values[ $field_name ] );
	}

	return $prepared;
}

/**
 * Bulk update a theme-managed options page.
 *
 * @param string $page_slug       Options page slug.
 * @param array  $values          Incoming field values.
 * @param bool   $merge_existing  Whether to merge into the current option value.
 * @return array
 */
function lithia_update_options_page_values( string $page_slug, array $values, bool $merge_existing = true ): array {
	$config = lithia_get_options_page_config( $page_slug );

	if ( empty( $config ) ) {
		return array();
	}

	lithia_ensure_options_page( $config );

	$prepared = lithia_prepare_options_page_values( $page_slug, $values, $merge_existing );

	update_option( $page_slug, $prepared );

	return $prepared;
}

/**
 * Bulk update the Business Details values.
 *
 * @param array $values Incoming field values.
 * @param bool  $merge_existing Whether to merge into the current option value.
 * @return array
 */
function lithia_update_business_details( array $values, bool $merge_existing = true ): array {
	return lithia_update_options_page_values( 'business-details', $values, $merge_existing );
}

/**
 * Bulk update the Brand Content values.
 *
 * @param array $values Incoming field values.
 * @param bool  $merge_existing Whether to merge into the current option value.
 * @return array
 */
function lithia_update_brand_content( array $values, bool $merge_existing = true ): array {
	return lithia_update_options_page_values( 'brand-content', $values, $merge_existing );
}

/**
 * Create a JetEngine options page if it does not exist yet.
 *
 * @param array $config Options page configuration.
 */
function lithia_ensure_options_page( array $config ): void {
	if ( ! function_exists( 'jet_engine' ) || ! lithia_can_manage_theme_seed_data() ) {
		return;
	}

	$jet_engine = jet_engine();

	if ( empty( $jet_engine->options_pages ) || empty( $jet_engine->options_pages->data ) ) {
		return;
	}

	$slug = $config['general_settings']['slug'] ?? '';

	if ( ! $slug ) {
		return;
	}

	$request = array(
		'name'             => $config['general_settings']['name'],
		'slug'             => $config['general_settings']['slug'],
		'menu_name'        => $config['general_settings']['menu_name'],
		'parent'           => $config['general_settings']['parent'],
		'icon'             => $config['general_settings']['icon'],
		'capability'       => $config['general_settings']['capability'],
		'position'         => $config['general_settings']['position'],
		'storage_type'     => $config['general_settings']['storage_type'],
		'option_prefix'    => $config['general_settings']['option_prefix'],
		'autoload_option'  => $config['general_settings']['autoload_option'],
		'hide_field_names' => $config['general_settings']['hide_field_names'],
		'fields'           => $config['fields'],
	);

	$jet_engine->options_pages->data->set_request( $request );
	$desired_item = $jet_engine->options_pages->data->sanitize_item_from_request();

	if ( empty( $desired_item ) || ! is_array( $desired_item ) ) {
		return;
	}

	foreach ( $jet_engine->options_pages->data->get_items() as $item ) {
		if ( ! empty( $item['slug'] ) && $slug === $item['slug'] ) {
			$desired_with_id       = $desired_item;
			$desired_with_id['id'] = $item['id'] ?? '';

			if ( wp_json_encode( $item ) !== wp_json_encode( $desired_with_id ) ) {
				$jet_engine->options_pages->data->update_item_in_db( $desired_with_id );

				if ( method_exists( $jet_engine->options_pages->data, 'reset_raw_cache' ) ) {
					$jet_engine->options_pages->data->reset_raw_cache();
				}
			}

			return;
		}
	}

	$jet_engine->options_pages->data->set_request( $request );

	$jet_engine->options_pages->data->create_item( false );

	if ( method_exists( $jet_engine->options_pages->data, 'reset_raw_cache' ) ) {
		$jet_engine->options_pages->data->reset_raw_cache();
	}
}

/**
 * Create all theme-managed JetEngine options pages if they do not exist yet.
 */
function lithia_ensure_theme_options_pages(): void {
	foreach ( lithia_get_options_page_configs() as $config ) {
		lithia_ensure_options_page( $config );
	}
}

/**
 * Seed JetEngine options pages only inside wp-admin.
 */
function lithia_maybe_seed_theme_options_pages(): void {
	if ( ! is_admin() ) {
		return;
	}

	lithia_ensure_theme_options_pages();
}
add_action( 'admin_init', 'lithia_maybe_seed_theme_options_pages', 20 );

/**
 * Generic helper to retrieve option values from a JetEngine options page.
 *
 * @param string $page_slug Options page slug.
 * @param string $key       Option key.
 * @param mixed  $default   Default fallback value.
 * @return mixed
 */
function lithia_get_options_page_value( string $page_slug, string $key, $default = '' ) {
	$options = get_option( $page_slug, array() );

	return $options[ $key ] ?? $default;
}

/**
 * Helper to retrieve Business Details option values from JetEngine.
 *
 * @param string $key     Option key.
 * @param mixed  $default Default fallback value.
 * @return mixed
 */
function lithia_get_business_detail( string $key, $default = '' ) {
	return lithia_get_options_page_value( 'business-details', $key, $default );
}

/**
 * Helper to retrieve Brand Content option values from JetEngine.
 *
 * @param string $key     Option key.
 * @param mixed  $default Default fallback value.
 * @return mixed
 */
function lithia_get_brand_content( string $key, $default = '' ) {
	return lithia_get_options_page_value( 'brand-content', $key, $default );
}
