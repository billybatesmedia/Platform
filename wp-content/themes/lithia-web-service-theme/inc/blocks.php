<?php
/**
 * Theme block registrations.
 *
 * @package LithiaWebServiceTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return the shared editor category slug for Lithia blocks.
 *
 * @return string
 */
function lithia_get_block_category_slug(): string {
	return 'lithai-blocks';
}

/**
 * Register the shared editor category for custom Lithia blocks.
 *
 * @param array $categories Existing categories.
 * @return array
 */
function lithia_register_block_category( array $categories ): array {
	$slug = lithia_get_block_category_slug();

	foreach ( $categories as $category ) {
		if ( ! empty( $category['slug'] ) && $slug === $category['slug'] ) {
			return $categories;
		}
	}

	array_unshift(
		$categories,
		array(
			'slug'  => $slug,
			'title' => __( 'Lithai Blocks', 'lithia-web-service-theme' ),
			'icon'  => null,
		)
	);

	return $categories;
}
add_filter( 'block_categories_all', 'lithia_register_block_category' );

/**
 * Register theme-specific custom blocks.
 */
function lithia_register_custom_blocks(): void {
	wp_register_style(
		'lithia-block-primitives',
		get_theme_file_uri( 'assets/css/block-primitives.css' ),
		array(),
		lithia_get_theme_asset_version( 'assets/css/block-primitives.css' )
	);

	wp_register_style(
		'lithia-business-hero-style',
		get_theme_file_uri( 'assets/css/blocks-business-hero.css' ),
		array( 'lithia-block-primitives' ),
		lithia_get_theme_asset_version( 'assets/css/blocks-business-hero.css' )
	);

	wp_register_style(
		'lithia-svg-background-style',
		get_theme_file_uri( 'assets/css/blocks-svg-background.css' ),
		array( 'lithia-block-primitives' ),
		lithia_get_theme_asset_version( 'assets/css/blocks-svg-background.css' )
	);

	wp_register_style(
		'lithia-brand-content-style',
		get_theme_file_uri( 'assets/css/blocks-brand-content.css' ),
		array( 'lithia-block-primitives' ),
		lithia_get_theme_asset_version( 'assets/css/blocks-brand-content.css' )
	);

	wp_register_style(
		'lithia-service-page-style',
		get_theme_file_uri( 'assets/css/blocks-service-page.css' ),
		array( 'lithia-block-primitives' ),
		lithia_get_theme_asset_version( 'assets/css/blocks-service-page.css' )
	);

	wp_register_script(
		'lithia-business-hero-block',
		get_theme_file_uri( 'assets/js/blocks/business-hero.js' ),
		array(
			'wp-blocks',
			'wp-block-editor',
			'wp-components',
			'wp-element',
			'wp-i18n',
			'wp-server-side-render',
		),
		lithia_get_theme_asset_version( 'assets/js/blocks/business-hero.js' ),
		true
	);

	wp_register_script(
		'lithia-svg-background-block',
		get_theme_file_uri( 'assets/js/blocks/svg-background.js' ),
		array(
			'wp-blocks',
			'wp-block-editor',
			'wp-components',
			'wp-element',
			'wp-i18n',
		),
		lithia_get_theme_asset_version( 'assets/js/blocks/svg-background.js' ),
		true
	);

	wp_register_script(
		'lithia-brand-content-blocks',
		get_theme_file_uri( 'assets/js/blocks/brand-content.js' ),
		array(
			'wp-blocks',
			'wp-block-editor',
			'wp-components',
			'wp-element',
			'wp-i18n',
			'wp-server-side-render',
		),
		lithia_get_theme_asset_version( 'assets/js/blocks/brand-content.js' ),
		true
	);

	wp_register_script(
		'lithia-service-page-block',
		get_theme_file_uri( 'assets/js/blocks/service-page.js' ),
		array(
			'wp-blocks',
			'wp-block-editor',
			'wp-components',
			'wp-element',
			'wp-i18n',
			'wp-server-side-render',
		),
		lithia_get_theme_asset_version( 'assets/js/blocks/service-page.js' ),
		true
	);

	wp_register_script(
		'lithia-service-page-view',
		get_theme_file_uri( 'assets/js/blocks/service-page-view.js' ),
		array(),
		lithia_get_theme_asset_version( 'assets/js/blocks/service-page-view.js' ),
		true
	);

	register_block_type(
		'lithia/business-hero',
		array(
			'api_version'   => 2,
			'style'         => 'lithia-business-hero-style',
			'editor_style'  => 'lithia-business-hero-style',
			'editor_script' => 'lithia-business-hero-block',
			'render_callback' => 'lithia_render_business_hero_block',
			'attributes'    => array(
				'align' => array(
					'type'    => 'string',
					'default' => 'full',
				),
				'tone' => array(
					'type'    => 'string',
					'default' => 'dark',
				),
				'eyebrow' => array(
					'type'    => 'string',
					'default' => 'Service-based business',
				),
				'useBusinessName' => array(
					'type'    => 'boolean',
					'default' => true,
				),
				'headline' => array(
					'type'    => 'string',
					'default' => '',
				),
				'useCity' => array(
					'type'    => 'boolean',
					'default' => true,
				),
				'subheading' => array(
					'type'    => 'string',
					'default' => '',
				),
				'text' => array(
					'type'    => 'string',
					'default' => 'Use this hero to introduce your business and drive visitors into the booking flow.',
				),
				'buttonText' => array(
					'type'    => 'string',
					'default' => 'Book Appointment',
				),
				'buttonUrl' => array(
					'type'    => 'string',
					'default' => '/book-appointment/',
				),
				'backgroundImageId' => array(
					'type'    => 'number',
					'default' => 0,
				),
				'backgroundImageUrl' => array(
					'type'    => 'string',
					'default' => '',
				),
				'backgroundType' => array(
					'type'    => 'string',
					'default' => 'image',
				),
				'youtubeUrl' => array(
					'type'    => 'string',
					'default' => '',
				),
			),
			'supports'      => array(
				'align' => array( 'full', 'wide' ),
				'anchor' => true,
				'html' => false,
			),
		)
	);

	register_block_type(
		'lithia/svg-background',
		array(
			'api_version'   => 2,
			'style'         => 'lithia-svg-background-style',
			'editor_style'  => 'lithia-svg-background-style',
			'editor_script' => 'lithia-svg-background-block',
			'render_callback' => 'lithia_render_svg_background_block',
			'attributes'    => array(
				'align' => array(
					'type'    => 'string',
					'default' => 'full',
				),
				'tone' => array(
					'type'    => 'string',
					'default' => 'light',
				),
				'svgId' => array(
					'type'    => 'number',
					'default' => 0,
				),
				'svgUrl' => array(
					'type'    => 'string',
					'default' => '',
				),
				'colorMode' => array(
					'type'    => 'string',
					'default' => 'theme-tones',
				),
				'singleColorToken' => array(
					'type'    => 'string',
					'default' => 'primary',
				),
				'position' => array(
					'type'    => 'string',
					'default' => 'center',
				),
				'opacity' => array(
					'type'    => 'number',
					'default' => 44,
				),
				'scale' => array(
					'type'    => 'number',
					'default' => 100,
				),
				'minHeight' => array(
					'type'    => 'string',
					'default' => 'clamp(420px, 58vh, 760px)',
				),
			),
			'supports'      => array(
				'align' => array( 'full', 'wide' ),
				'anchor' => true,
				'html' => false,
			),
		)
	);

	register_block_type(
		'lithia/brand-intro',
		array(
			'api_version'   => 2,
			'style'         => 'lithia-brand-content-style',
			'editor_style'  => 'lithia-brand-content-style',
			'editor_script' => 'lithia-brand-content-blocks',
			'render_callback' => 'lithia_render_brand_intro_block',
			'attributes'    => array(
				'align' => array(
					'type'    => 'string',
					'default' => 'wide',
				),
				'tone' => array(
					'type'    => 'string',
					'default' => 'light',
				),
				'eyebrow' => array(
					'type'    => 'string',
					'default' => '',
				),
				'heading' => array(
					'type'    => 'string',
					'default' => '',
				),
				'text' => array(
					'type'    => 'string',
					'default' => '',
				),
				'showPrimaryCta' => array(
					'type'    => 'boolean',
					'default' => true,
				),
				'showSecondaryCta' => array(
					'type'    => 'boolean',
					'default' => true,
				),
				'primaryLabel' => array(
					'type'    => 'string',
					'default' => '',
				),
				'primaryUrl' => array(
					'type'    => 'string',
					'default' => '',
				),
				'secondaryLabel' => array(
					'type'    => 'string',
					'default' => '',
				),
				'secondaryUrl' => array(
					'type'    => 'string',
					'default' => '',
				),
			),
			'supports'      => array(
				'align' => array( 'full', 'wide' ),
				'anchor' => true,
				'html' => false,
			),
		)
	);

	register_block_type(
		'lithia/service-spotlight-loop',
		array(
			'api_version'   => 2,
			'style'         => 'lithia-brand-content-style',
			'editor_style'  => 'lithia-brand-content-style',
			'editor_script' => 'lithia-service-page-block',
			'view_script'   => 'lithia-service-page-view',
			'render_callback' => 'lithia_render_service_spotlight_loop_block',
			'attributes'    => array(
				'align' => array(
					'type'    => 'string',
					'default' => 'wide',
				),
				'tone' => array(
					'type'    => 'string',
					'default' => 'light',
				),
				'eyebrow' => array(
					'type'    => 'string',
					'default' => 'Service Spotlight',
				),
				'heading' => array(
					'type'    => 'string',
					'default' => 'Start with one focused service',
				),
				'intro' => array(
					'type'    => 'string',
					'default' => 'Rotate selected services here using each service excerpt as the slide copy.',
				),
				'showArchiveCta' => array(
					'type'    => 'boolean',
					'default' => true,
				),
				'archiveLabel' => array(
					'type'    => 'string',
					'default' => 'View All Services',
				),
				'archiveUrl' => array(
					'type'    => 'string',
					'default' => '/services/',
				),
			),
			'supports'      => array(
				'align' => array( 'full', 'wide' ),
				'anchor' => true,
				'html' => false,
			),
		)
	);

	register_block_type(
		'lithia/mission-statement',
		array(
			'api_version'   => 2,
			'style'         => 'lithia-brand-content-style',
			'editor_style'  => 'lithia-brand-content-style',
			'editor_script' => 'lithia-brand-content-blocks',
			'render_callback' => 'lithia_render_mission_statement_block',
			'attributes'    => array(
				'align' => array(
					'type'    => 'string',
					'default' => 'wide',
				),
				'tone' => array(
					'type'    => 'string',
					'default' => 'light',
				),
				'label' => array(
					'type'    => 'string',
					'default' => 'Mission',
				),
				'missionText' => array(
					'type'    => 'string',
					'default' => '',
				),
				'imageId' => array(
					'type'    => 'number',
					'default' => 0,
				),
				'imageUrl' => array(
					'type'    => 'string',
					'default' => '',
				),
				'imageAlt' => array(
					'type'    => 'string',
					'default' => '',
				),
			),
			'supports'      => array(
				'align' => array( 'full', 'wide' ),
				'anchor' => true,
				'html' => false,
			),
		)
	);

	register_block_type(
		'lithia/about-summary',
		array(
			'api_version'   => 2,
			'style'         => 'lithia-brand-content-style',
			'editor_style'  => 'lithia-brand-content-style',
			'editor_script' => 'lithia-brand-content-blocks',
			'render_callback' => 'lithia_render_about_summary_block',
			'attributes'    => array(
				'align' => array(
					'type'    => 'string',
					'default' => 'wide',
				),
				'tone' => array(
					'type'    => 'string',
					'default' => 'light',
				),
				'eyebrow' => array(
					'type'    => 'string',
					'default' => 'About Us',
				),
				'heading' => array(
					'type'    => 'string',
					'default' => 'A closer look at the business',
				),
				'text' => array(
					'type'    => 'string',
					'default' => '',
				),
				'imageId' => array(
					'type'    => 'number',
					'default' => 0,
				),
				'imageUrl' => array(
					'type'    => 'string',
					'default' => '',
				),
				'imageAlt' => array(
					'type'    => 'string',
					'default' => '',
				),
				'showButton' => array(
					'type'    => 'boolean',
					'default' => true,
				),
				'buttonLabel' => array(
					'type'    => 'string',
					'default' => '',
				),
				'buttonUrl' => array(
					'type'    => 'string',
					'default' => '',
				),
			),
			'supports'      => array(
				'align' => array( 'full', 'wide' ),
				'anchor' => true,
				'html' => false,
			),
		)
	);

	register_block_type(
		'lithia/brand-cta-pair',
		array(
			'api_version'   => 2,
			'style'         => 'lithia-brand-content-style',
			'editor_style'  => 'lithia-brand-content-style',
			'editor_script' => 'lithia-brand-content-blocks',
			'render_callback' => 'lithia_render_brand_cta_pair_block',
			'attributes'    => array(
				'align' => array(
					'type'    => 'string',
					'default' => 'wide',
				),
				'tone' => array(
					'type'    => 'string',
					'default' => 'light',
				),
				'showPrimaryCta' => array(
					'type'    => 'boolean',
					'default' => true,
				),
				'showSecondaryCta' => array(
					'type'    => 'boolean',
					'default' => true,
				),
				'primaryLabel' => array(
					'type'    => 'string',
					'default' => '',
				),
				'primaryUrl' => array(
					'type'    => 'string',
					'default' => '',
				),
				'secondaryLabel' => array(
					'type'    => 'string',
					'default' => '',
				),
				'secondaryUrl' => array(
					'type'    => 'string',
					'default' => '',
				),
			),
			'supports'      => array(
				'align' => array( 'full', 'wide' ),
				'anchor' => true,
				'html' => false,
			),
		)
	);

	register_block_type(
		'lithia/contact-details',
		array(
			'api_version'   => 2,
			'style'         => 'lithia-brand-content-style',
			'editor_style'  => 'lithia-brand-content-style',
			'editor_script' => 'lithia-brand-content-blocks',
			'render_callback' => 'lithia_render_contact_details_block',
			'attributes'    => array(
				'align' => array(
					'type'    => 'string',
					'default' => 'wide',
				),
				'tone' => array(
					'type'    => 'string',
					'default' => 'light',
				),
				'heading' => array(
					'type'    => 'string',
					'default' => 'Contact Details',
				),
				'intro' => array(
					'type'    => 'string',
					'default' => '',
				),
				'showHours' => array(
					'type'    => 'boolean',
					'default' => true,
				),
				'showNotice' => array(
					'type'    => 'boolean',
					'default' => true,
				),
			),
			'supports'      => array(
				'align' => array( 'full', 'wide' ),
				'anchor' => true,
				'html' => false,
			),
		)
	);

	register_block_type(
		'lithia/contact-form',
		array(
			'api_version'   => 2,
			'style'         => 'lithia-brand-content-style',
			'editor_style'  => 'lithia-brand-content-style',
			'editor_script' => 'lithia-brand-content-blocks',
			'render_callback' => 'lithia_render_contact_form_block',
			'attributes'    => array(
				'align' => array(
					'type'    => 'string',
					'default' => 'wide',
				),
				'tone' => array(
					'type'    => 'string',
					'default' => 'dark',
				),
				'heading' => array(
					'type'    => 'string',
					'default' => 'Send a Message',
				),
				'intro' => array(
					'type'    => 'string',
					'default' => '',
				),
			),
			'supports'      => array(
				'align' => array( 'full', 'wide' ),
				'anchor' => true,
				'html' => false,
			),
		)
	);

	register_block_type(
		'lithia/services-dropdown',
		array(
			'api_version' => 2,
			'render_callback' => 'lithia_render_services_dropdown_block',
			'attributes' => array(
				'label' => array(
					'type'    => 'string',
					'default' => 'Services',
				),
			),
			'supports' => array(
				'html' => false,
			),
		)
	);

	register_block_type(
		'lithia/service-page',
		array(
			'api_version'   => 2,
			'style'         => 'lithia-service-page-style',
			'editor_style'  => 'lithia-service-page-style',
			'editor_script' => 'lithia-service-page-block',
			'view_script'   => 'lithia-service-page-view',
			'render_callback' => 'lithia_render_service_page_block',
			'attributes'    => array(
				'align' => array(
					'type'    => 'string',
					'default' => 'full',
				),
			),
			'supports'      => array(
				'align' => array( 'full', 'wide' ),
				'html'  => false,
			),
		)
	);

	register_block_type(
		'lithia/provider-page',
		array(
			'api_version'   => 2,
			'style'         => 'lithia-service-page-style',
			'editor_style'  => 'lithia-service-page-style',
			'editor_script' => 'lithia-service-page-block',
			'render_callback' => 'lithia_render_provider_page_block',
			'attributes'    => array(
				'align' => array(
					'type'    => 'string',
					'default' => 'full',
				),
			),
			'supports'      => array(
				'align' => array( 'full', 'wide' ),
				'html'  => false,
			),
		)
	);
}
add_action( 'init', 'lithia_register_custom_blocks' );

/**
 * Enqueue shared block primitives in both editor and front-end block contexts.
 */
function lithia_enqueue_block_primitives(): void {
	wp_enqueue_style( 'lithia-block-primitives' );
}
add_action( 'enqueue_block_assets', 'lithia_enqueue_block_primitives' );

/**
 * Return merged brand content values with sensible fallbacks.
 *
 * @return array
 */
function lithia_get_brand_content_defaults(): array {
	return array(
		'intro_eyebrow'        => lithia_get_brand_content( 'intro_eyebrow', 'Who we are' ),
		'intro_heading'        => lithia_get_brand_content( 'intro_heading', 'Service built around real relationships' ),
		'intro_paragraph'      => lithia_get_brand_content( 'intro_paragraph', 'Write reusable intro copy in the Brand Content options page.' ),
		'mission_statement'    => lithia_get_brand_content( 'mission_statement', 'State the mission in a concise, repeatable way.' ),
		'about_summary'        => lithia_get_brand_content( 'about_summary', 'Use this summary for home, about, and supporting page sections.' ),
		'primary_cta_label'    => lithia_get_brand_content( 'primary_cta_label', 'Book Appointment' ),
		'primary_cta_url'      => lithia_get_brand_content( 'primary_cta_url', '/book-appointment/' ),
		'secondary_cta_label'  => lithia_get_brand_content( 'secondary_cta_label', 'About Us' ),
		'secondary_cta_url'    => lithia_get_brand_content( 'secondary_cta_url', '/about-us/' ),
	);
}

/**
 * Build a CTA link array.
 *
 * @param string $label Button label.
 * @param string $url   Button URL.
 * @param string $class Extra class.
 * @return array
 */
function lithia_get_cta_data( string $label, string $url, string $class = '' ): array {
	return array(
		'label' => $label,
		'url'   => $url,
		'class' => trim( $class ),
	);
}

/**
 * Normalize a block tone value.
 *
 * @param array  $attributes Block attributes.
 * @param string $default    Fallback tone.
 * @return string
 */
function lithia_get_block_tone( array $attributes, string $default = 'light' ): string {
	$tone = isset( $attributes['tone'] ) ? strtolower( (string) $attributes['tone'] ) : $default;

	return in_array( $tone, array( 'light', 'dark' ), true ) ? $tone : $default;
}

/**
 * Resolve the current post ID from block context or the main query.
 *
 * @param object|null $block Parsed block object.
 * @return int
 */
function lithia_get_current_block_post_id( $block = null ): int {
	if ( is_object( $block ) && ! empty( $block->context['postId'] ) ) {
		return absint( $block->context['postId'] );
	}

	return get_the_ID() ? absint( get_the_ID() ) : 0;
}

/**
 * Determine whether a block is rendering in the homepage context.
 *
 * @param object|null $block Parsed block object.
 * @return bool
 */
function lithia_is_homepage_block_context( $block = null ): bool {
	$front_page_id = absint( get_option( 'page_on_front', 0 ) );
	$post_id       = lithia_get_current_block_post_id( $block );

	return $front_page_id > 0 && $post_id === $front_page_id;
}

/**
 * Return homepage-specific content defaults from Brand Content options.
 *
 * @return array
 */
function lithia_get_homepage_content_defaults(): array {
	$brand = lithia_get_brand_content_defaults();

	return array(
		'about_eyebrow'           => (string) lithia_get_brand_content( 'homepage_about_eyebrow', 'Who Lithia Web Helps' ),
		'about_heading'           => (string) lithia_get_brand_content( 'homepage_about_heading', 'A WordPress partner for the full website lifecycle' ),
		'about_text'              => (string) lithia_get_brand_content( 'homepage_about_text', $brand['about_summary'] ),
		'about_image_id'          => absint( lithia_get_brand_content( 'homepage_about_image_id', 0 ) ),
		'about_image_alt'         => (string) lithia_get_brand_content( 'homepage_about_image_alt', '' ),
		'about_button_label'      => (string) lithia_get_brand_content( 'homepage_about_button_label', 'View Services' ),
		'about_button_url'        => (string) lithia_get_brand_content( 'homepage_about_button_url', '/services/' ),
		'spotlight_eyebrow'       => (string) lithia_get_brand_content( 'homepage_spotlight_eyebrow', 'Service Spotlight' ),
		'spotlight_heading'       => (string) lithia_get_brand_content( 'homepage_spotlight_heading', 'Start with one focused service' ),
		'spotlight_intro'         => (string) lithia_get_brand_content( 'homepage_spotlight_intro', 'Selected services rotate here using their excerpts, details, and primary CTA fields.' ),
		'spotlight_archive_label' => (string) lithia_get_brand_content( 'homepage_spotlight_archive_label', 'View All Services' ),
		'spotlight_archive_url'   => (string) lithia_get_brand_content( 'homepage_spotlight_archive_url', '/services/' ),
		'services_eyebrow'        => (string) lithia_get_brand_content( 'homepage_services_eyebrow', 'Services' ),
		'services_heading'        => (string) lithia_get_brand_content( 'homepage_services_heading', 'Support for Every Stage of Your Website' ),
		'services_intro'          => (string) lithia_get_brand_content( 'homepage_services_intro', 'Browse the options below to find the right fit, whether you need a focused audit, a clear consultation, a full build, or steady ongoing support.' ),
		'mission_label'           => (string) lithia_get_brand_content( 'homepage_mission_label', 'How Lithia Web Works' ),
		'mission_text'            => (string) lithia_get_brand_content( 'homepage_mission_text', $brand['mission_statement'] ),
		'mission_image_id'        => absint( lithia_get_brand_content( 'homepage_mission_image_id', 0 ) ),
		'mission_image_alt'       => (string) lithia_get_brand_content( 'homepage_mission_image_alt', '' ),
		'primary_cta_label'       => (string) lithia_get_brand_content( 'homepage_primary_cta_label', $brand['primary_cta_label'] ),
		'primary_cta_url'         => (string) lithia_get_brand_content( 'homepage_primary_cta_url', $brand['primary_cta_url'] ),
		'secondary_cta_label'     => (string) lithia_get_brand_content( 'homepage_secondary_cta_label', $brand['secondary_cta_label'] ),
		'secondary_cta_url'       => (string) lithia_get_brand_content( 'homepage_secondary_cta_url', $brand['secondary_cta_url'] ),
	);
}

/**
 * Build the shared inner Services dropdown markup.
 *
 * @param array $attributes Block attributes.
 * @return string
 */
function lithia_get_services_dropdown_inner_markup( array $attributes = array() ): string {
	$services = get_posts(
		array(
			'post_type'      => 'services',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'menu_order title',
			'order'          => 'ASC',
		)
	);

	if ( empty( $services ) ) {
		return '';
	}

	$label          = ! empty( $attributes['label'] ) ? (string) $attributes['label'] : 'Services';
	$archive_link   = get_post_type_archive_link( 'services' );
	$current_post   = get_queried_object_id();
	$is_archive     = is_post_type_archive( 'services' );
	$is_service     = is_singular( 'services' );
	if ( ! $archive_link ) {
		$archive_link = home_url( '/services/' );
	}

	ob_start();
	?>
	<details class="lithia-services-dropdown<?php echo ( $is_archive || $is_service ) ? ' is-current' : ''; ?>">
		<summary class="lithia-services-dropdown__summary">
			<span><?php echo esc_html( $label ); ?></span>
		</summary>

		<div class="lithia-services-dropdown__menu" role="menu">
			<a class="lithia-services-dropdown__link<?php echo $is_archive ? ' is-current' : ''; ?>" href="<?php echo esc_url( $archive_link ); ?>" role="menuitem">
				All Services
			</a>

			<?php foreach ( $services as $service ) : ?>
				<?php
				$is_current_item = $is_service && (int) $current_post === (int) $service->ID;
				?>
				<a class="lithia-services-dropdown__link<?php echo $is_current_item ? ' is-current' : ''; ?>" href="<?php echo esc_url( get_permalink( $service ) ); ?>" role="menuitem">
					<?php echo esc_html( get_the_title( $service ) ); ?>
				</a>
			<?php endforeach; ?>
		</div>
	</details>
	<?php

	return (string) ob_get_clean();
}

/**
 * Build the shared inner Site Docs dropdown markup.
 *
 * @param array $attributes Block attributes.
 * @return string
 */
function lithia_get_site_docs_dropdown_inner_markup( array $attributes = array() ): string {
	$docs = get_posts(
		array(
			'post_type'      => 'site_docs',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'menu_order title',
			'order'          => 'ASC',
		)
	);

	if ( empty( $docs ) ) {
		return '';
	}

	$label        = ! empty( $attributes['label'] ) ? (string) $attributes['label'] : 'Site Docs';
	$archive_link = get_post_type_archive_link( 'site_docs' );
	$current_post = get_queried_object_id();
	$is_archive   = is_post_type_archive( 'site_docs' ) || is_tax( array( 'site_doc_type', 'site_doc_audience' ) );
	$is_single    = is_singular( 'site_docs' );

	if ( ! $archive_link ) {
		$archive_link = home_url( '/site-docs/' );
	}

	ob_start();
	?>
	<details class="lithia-services-dropdown lithia-site-docs-dropdown<?php echo ( $is_archive || $is_single ) ? ' is-current' : ''; ?>">
		<summary class="lithia-services-dropdown__summary">
			<span><?php echo esc_html( $label ); ?></span>
		</summary>

		<div class="lithia-services-dropdown__menu lithia-site-docs-dropdown__menu" role="menu">
			<a class="lithia-services-dropdown__link<?php echo $is_archive ? ' is-current' : ''; ?>" href="<?php echo esc_url( $archive_link ); ?>" role="menuitem">
				All Docs
			</a>

			<?php foreach ( $docs as $doc ) : ?>
				<?php
				$is_current_item = $is_single && (int) $current_post === (int) $doc->ID;
				?>
				<a class="lithia-services-dropdown__link<?php echo $is_current_item ? ' is-current' : ''; ?>" href="<?php echo esc_url( get_permalink( $doc ) ); ?>" role="menuitem">
					<?php echo esc_html( get_the_title( $doc ) ); ?>
				</a>
			<?php endforeach; ?>
		</div>
	</details>
	<?php

	return (string) ob_get_clean();
}

/**
 * Build the wrapped Services dropdown markup for block usage.
 *
 * @param string $wrapper_attributes Wrapper attributes string.
 * @param array  $attributes         Block attributes.
 * @return string
 */
function lithia_get_services_dropdown_markup( string $wrapper_attributes = '', array $attributes = array() ): string {
	$inner_markup = lithia_get_services_dropdown_inner_markup( $attributes );

	if ( '' === $inner_markup ) {
		return '';
	}

	ob_start();
	?>
	<div <?php echo $wrapper_attributes ? $wrapper_attributes : 'class="lithia-services-dropdown-block"'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		<?php echo $inner_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</div>
	<?php

	return (string) ob_get_clean();
}

/**
 * Render the header Services dropdown block.
 *
 * @param array $attributes Block attributes.
 * @return string
 */
function lithia_render_services_dropdown_block( array $attributes = array() ): string {
	$wrapper_attributes = get_block_wrapper_attributes(
		array(
			'class' => 'lithia-services-dropdown-block',
		)
	);

	return lithia_get_services_dropdown_markup( $wrapper_attributes, $attributes );
}

/**
 * Inject the Services and Site Docs dropdowns into the first front-end navigation block.
 *
 * @param string $block_content Block HTML.
 * @param array  $block         Parsed block data.
 * @return string
 */
function lithia_inject_navigation_dropdowns_into_navigation( string $block_content, array $block ): string {
	static $has_injected = false;

	if ( is_admin() || $has_injected ) {
		return $block_content;
	}

	if ( empty( $block['blockName'] ) || 'core/navigation' !== $block['blockName'] ) {
		return $block_content;
	}

	$dropdown_items = array();

	if ( false === strpos( $block_content, 'lithia-services-dropdown-item' ) ) {
		$services_dropdown = lithia_get_services_dropdown_inner_markup();

		if ( '' !== $services_dropdown ) {
			$dropdown_items[] = '<li class="wp-block-navigation-item lithia-services-dropdown-item">' . $services_dropdown . '</li>';
		}
	}

	if ( false === strpos( $block_content, 'lithia-site-docs-dropdown-item' ) ) {
		$docs_dropdown = lithia_get_site_docs_dropdown_inner_markup();

		if ( '' !== $docs_dropdown ) {
			$dropdown_items[] = '<li class="wp-block-navigation-item lithia-services-dropdown-item lithia-site-docs-dropdown-item">' . $docs_dropdown . '</li>';
		}
	}

	if ( empty( $dropdown_items ) ) {
		return $block_content;
	}

	$updated_content = preg_replace(
		'/(<ul\b[^>]*class="[^"]*wp-block-navigation__container[^"]*"[^>]*>)/',
		'$1' . implode( '', $dropdown_items ),
		$block_content,
		1
	);

	if ( is_string( $updated_content ) && $updated_content !== $block_content ) {
		$has_injected = true;
		return $updated_content;
	}

	return $block_content;
}
add_filter( 'render_block', 'lithia_inject_navigation_dropdowns_into_navigation', 10, 2 );

/**
 * Normalize a parsed block class name string.
 *
 * @param string $class_name Raw class name.
 * @return string
 */
function lithia_normalize_block_class_name( string $class_name ): string {
	return str_replace(
		array( '\\u002d', 'u002d' ),
		'-',
		$class_name
	);
}

/**
 * Exclude spotlighted services from the homepage services query block.
 *
 * @param array    $query Query vars.
 * @param WP_Block $block Block instance.
 * @param int      $page  Current page.
 * @return array
 */
function lithia_filter_home_services_query_vars( array $query, WP_Block $block, int $page ): array {
	if ( is_admin() ) {
		return $query;
	}

	$parsed_block = $block->parsed_block ?? array();
	$class_name   = lithia_normalize_block_class_name( (string) ( $parsed_block['attrs']['className'] ?? '' ) );
	$post_type    = (string) ( $query['post_type'] ?? '' );

	if ( false === strpos( $class_name, 'lithia-services-feed--home' ) || 'services' !== $post_type ) {
		return $query;
	}

	$spotlight_ids = array_values(
		array_filter(
			array_map(
				'absint',
				wp_list_pluck( lithia_get_homepage_spotlight_services(), 'post_id' )
			)
		)
	);

	if ( empty( $spotlight_ids ) ) {
		return $query;
	}

	$query['post__not_in'] = array_values(
		array_unique(
			array_merge(
				array_map( 'absint', (array) ( $query['post__not_in'] ?? array() ) ),
				$spotlight_ids
			)
		)
	);

	return $query;
}
add_filter( 'query_loop_block_query_vars', 'lithia_filter_home_services_query_vars', 10, 3 );

/**
 * Remove spotlighted service cards from the homepage services feed markup.
 *
 * @param string $block_content Query block HTML.
 * @return string
 */
function lithia_strip_spotlighted_services_from_home_feed_block( string $block_content ): string {
	$spotlight_urls = array_values(
		array_filter(
			array_map(
				static function ( array $service ): string {
					return ! empty( $service['url'] ) ? untrailingslashit( (string) $service['url'] ) : '';
				},
				lithia_get_homepage_spotlight_services()
			)
		)
	);

	if ( empty( $spotlight_urls ) || '' === trim( $block_content ) || ! class_exists( 'DOMDocument' ) ) {
		return $block_content;
	}

	$document = new DOMDocument();
	$previous = libxml_use_internal_errors( true );
	$loaded   = $document->loadHTML(
		'<div id="lithia-home-services-feed-root">' . $block_content . '</div>',
		LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
	);
	libxml_clear_errors();
	libxml_use_internal_errors( $previous );

	if ( ! $loaded ) {
		return $block_content;
	}

	$xpath = new DOMXPath( $document );
	$cards = $xpath->query(
		'//*[contains(concat(" ", normalize-space(@class), " "), " lithia-services-feed-card ")]'
	);

	if ( ! $cards instanceof DOMNodeList || 0 === $cards->length ) {
		return $block_content;
	}

	foreach ( iterator_to_array( $cards ) as $card ) {
		$link = $xpath->query( './/a[@href]', $card )->item( 0 );

		if ( ! $link instanceof DOMElement ) {
			continue;
		}

		$href = untrailingslashit( (string) $link->getAttribute( 'href' ) );

		if ( '' === $href || ! in_array( $href, $spotlight_urls, true ) ) {
			continue;
		}

		$remove_node = $card->parentNode instanceof DOMElement && 'li' === strtolower( $card->parentNode->nodeName )
			? $card->parentNode
			: $card;

		if ( $remove_node->parentNode ) {
			$remove_node->parentNode->removeChild( $remove_node );
		}
	}

	$root = $document->getElementById( 'lithia-home-services-feed-root' );

	if ( ! $root instanceof DOMElement ) {
		return $block_content;
	}

	$output = '';

	foreach ( $root->childNodes as $child ) {
		$output .= $document->saveHTML( $child );
	}

	return $output;
}

/**
 * Prepend an intro block to the homepage services feed query.
 *
 * @param string $block_content Block HTML.
 * @param array  $block         Parsed block data.
 * @return string
 */
function lithia_prepend_home_services_feed_intro( string $block_content, array $block ): string {
	if ( is_admin() ) {
		return $block_content;
	}

	if ( empty( $block['blockName'] ) || 'core/query' !== $block['blockName'] ) {
		return $block_content;
	}

	$class_name = lithia_normalize_block_class_name( (string) ( $block['attrs']['className'] ?? '' ) );

	if ( false === strpos( $class_name, 'lithia-services-feed--home' ) ) {
		return $block_content;
	}

	$block_content = lithia_strip_spotlighted_services_from_home_feed_block( $block_content );

	if ( false === strpos( $block_content, 'lithia-services-feed-card' ) ) {
		return '';
	}

	if ( false !== strpos( $block_content, 'lithia-services-feed__intro' ) ) {
		return $block_content;
	}

	$homepage_content = lithia_get_homepage_content_defaults();
	$intro_eyebrow    = trim( (string) ( $homepage_content['services_eyebrow'] ?? '' ) );
	$intro_heading    = trim( (string) ( $homepage_content['services_heading'] ?? '' ) );
	$intro_text       = trim( (string) ( $homepage_content['services_intro'] ?? '' ) );

	$intro_markup = '
		<div class="lithia-services-feed__intro lithia-tone-light">
			<p class="lithia-eyebrow">' . esc_html( $intro_eyebrow ) . '</p>
			<h2 class="lithia-heading-xl">' . esc_html( $intro_heading ) . '</h2>
			<div class="lithia-copy">
				<p>' . esc_html( $intro_text ) . '</p>
			</div>
		</div>
	';

	return $intro_markup . $block_content;
}
add_filter( 'render_block', 'lithia_prepend_home_services_feed_intro', 10, 2 );

/**
 * Return the supported SVG background theme color token map.
 *
 * @return array<string, string>
 */
function lithia_get_svg_background_color_token_map(): array {
	return array(
		'primary'   => 'primary_color',
		'secondary' => 'secondary_color',
		'accent'    => 'accent_color',
		'dark'      => 'dark_bg_color',
		'light'     => 'light_bg_color',
		'text'      => 'text_color',
	);
}

/**
 * Normalize a hex color into 6-digit lowercase form.
 *
 * @param string $color Raw hex color.
 * @return string
 */
function lithia_normalize_hex_color( string $color ): string {
	$color = trim( strtolower( $color ) );

	if ( '' === $color ) {
		return '';
	}

	if ( '#' !== $color[0] ) {
		$color = '#' . $color;
	}

	$sanitized = sanitize_hex_color( $color );

	if ( ! $sanitized ) {
		return '';
	}

	if ( 4 === strlen( $sanitized ) ) {
		return sprintf(
			'#%1$s%1$s%2$s%2$s%3$s%3$s',
			$sanitized[1],
			$sanitized[2],
			$sanitized[3]
		);
	}

	return strtolower( $sanitized );
}

/**
 * Convert a hex color into RGB channels.
 *
 * @param string $color Hex color.
 * @return array<int, int>
 */
function lithia_get_hex_color_rgb_channels( string $color ): array {
	$color = lithia_normalize_hex_color( $color );

	if ( '' === $color ) {
		return array( 0, 0, 0 );
	}

	return array(
		hexdec( substr( $color, 1, 2 ) ),
		hexdec( substr( $color, 3, 2 ) ),
		hexdec( substr( $color, 5, 2 ) ),
	);
}

/**
 * Return the WCAG relative luminance for a hex color.
 *
 * @param string $color Hex color.
 * @return float
 */
function lithia_get_hex_color_luminance( string $color ): float {
	$channels = lithia_get_hex_color_rgb_channels( $color );
	$values   = array();

	foreach ( $channels as $channel ) {
		$value    = $channel / 255;
		$values[] = ( $value <= 0.03928 ) ? ( $value / 12.92 ) : pow( ( $value + 0.055 ) / 1.055, 2.4 );
	}

	return ( 0.2126 * $values[0] ) + ( 0.7152 * $values[1] ) + ( 0.0722 * $values[2] );
}

/**
 * Sanitize inline SVG CSS blocks.
 *
 * @param string $css Raw CSS.
 * @return string
 */
function lithia_sanitize_inline_svg_css( string $css ): string {
	$css = preg_replace( '/@import[^;]+;?/i', '', $css );
	$css = preg_replace( '/expression\s*\([^)]*\)/i', '', (string) $css );
	$css = preg_replace( '/javascript:/i', '', (string) $css );
	$css = preg_replace( '/url\(\s*([\'"]?)(?!#)[^)]+\1\s*\)/i', 'none', (string) $css );

	return trim( (string) $css );
}

/**
 * Sanitize an inline SVG style attribute.
 *
 * @param string $style Raw style attribute value.
 * @return string
 */
function lithia_sanitize_inline_svg_style_attribute( string $style ): string {
	$style = lithia_sanitize_inline_svg_css( $style );

	if ( '' === $style ) {
		return '';
	}

	$style = preg_replace( '/[<>]/', '', $style );

	return trim( (string) $style );
}

/**
 * Sanitize inline SVG markup before rendering it in the page.
 *
 * @param string $svg Raw SVG markup.
 * @return string
 */
function lithia_sanitize_inline_svg_markup( string $svg ): string {
	$svg = trim( $svg );

	if ( '' === $svg || ! class_exists( 'DOMDocument' ) ) {
		return '';
	}

	$svg = preg_replace( '/<\?xml[^>]*\?>/i', '', $svg );
	$svg = preg_replace( '/<!DOCTYPE[^>]*>/i', '', (string) $svg );

	$allowed_tags = array_flip(
		array(
			'svg',
			'g',
			'defs',
			'style',
			'title',
			'desc',
			'symbol',
			'use',
			'path',
			'rect',
			'circle',
			'ellipse',
			'line',
			'polyline',
			'polygon',
			'clippath',
			'mask',
			'pattern',
			'lineargradient',
			'radialgradient',
			'stop',
		)
	);

	$allowed_attributes = array_flip(
		array(
			'xmlns',
			'xmlns:xlink',
			'version',
			'viewbox',
			'width',
			'height',
			'x',
			'y',
			'x1',
			'y1',
			'x2',
			'y2',
			'cx',
			'cy',
			'r',
			'rx',
			'ry',
			'd',
			'points',
			'fill',
			'stroke',
			'stroke-width',
			'stroke-linecap',
			'stroke-linejoin',
			'stroke-miterlimit',
			'stroke-dasharray',
			'stroke-dashoffset',
			'fill-opacity',
			'stroke-opacity',
			'fill-rule',
			'clip-rule',
			'opacity',
			'transform',
			'class',
			'id',
			'href',
			'xlink:href',
			'gradientunits',
			'gradienttransform',
			'offset',
			'stop-color',
			'stop-opacity',
			'maskunits',
			'maskcontentunits',
			'clippathunits',
			'patternunits',
			'patterncontentunits',
			'patterntransform',
			'preserveaspectratio',
			'style',
			'role',
			'aria-hidden',
			'focusable',
		)
	);

	$document = new DOMDocument();
	$previous = libxml_use_internal_errors( true );
	$loaded   = $document->loadXML( $svg, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_COMPACT );
	libxml_clear_errors();
	libxml_use_internal_errors( $previous );

	if ( ! $loaded || ! $document->documentElement || 'svg' !== strtolower( $document->documentElement->tagName ) ) {
		return '';
	}

	$nodes = array();
	foreach ( $document->getElementsByTagName( '*' ) as $node ) {
		$nodes[] = $node;
	}

	for ( $index = count( $nodes ) - 1; $index >= 0; $index-- ) {
		$node = $nodes[ $index ];
		$tag  = strtolower( $node->tagName );

		if ( ! isset( $allowed_tags[ $tag ] ) ) {
			if ( $node->parentNode ) {
				$node->parentNode->removeChild( $node );
			}
			continue;
		}

		if ( 'style' === $tag ) {
			$css = lithia_sanitize_inline_svg_css( (string) $node->textContent );

			while ( $node->firstChild ) {
				$node->removeChild( $node->firstChild );
			}

			if ( '' !== $css ) {
				$node->appendChild( $document->createTextNode( $css ) );
			}

			continue;
		}

		if ( ! $node->hasAttributes() ) {
			continue;
		}

		for ( $attribute_index = $node->attributes->length - 1; $attribute_index >= 0; $attribute_index-- ) {
			$attribute = $node->attributes->item( $attribute_index );

			if ( ! $attribute ) {
				continue;
			}

			$name       = $attribute->nodeName;
			$lower_name = strtolower( $name );
			$value      = (string) $attribute->nodeValue;

			if ( 0 === strpos( $lower_name, 'on' ) ) {
				$node->removeAttributeNode( $attribute );
				continue;
			}

			if ( 0 === strpos( $lower_name, 'xmlns' ) ) {
				continue;
			}

			if ( ! isset( $allowed_attributes[ $lower_name ] ) ) {
				$node->removeAttributeNode( $attribute );
				continue;
			}

			if ( in_array( $lower_name, array( 'href', 'xlink:href' ), true ) && '' !== $value && '#' !== $value[0] ) {
				$node->removeAttributeNode( $attribute );
				continue;
			}

			if ( preg_match( '/javascript:/i', $value ) ) {
				$node->removeAttributeNode( $attribute );
				continue;
			}

			if ( 'style' === $lower_name ) {
				$sanitized_style = lithia_sanitize_inline_svg_style_attribute( $value );

				if ( '' === $sanitized_style ) {
					$node->removeAttributeNode( $attribute );
					continue;
				}

				$node->setAttribute( $name, $sanitized_style );
			}
		}
	}

	$root = $document->documentElement;

	if ( ! $root ) {
		return '';
	}

	return trim( (string) $document->saveXML( $root ) );
}

/**
 * Return the current theme palette used for SVG tone remapping.
 *
 * @return array<int, string>
 */
function lithia_get_svg_background_theme_palette(): array {
	$styles  = lithia_get_site_styles();
	$palette = array(
		$styles['dark_bg_color'] ?? '',
		$styles['primary_color'] ?? '',
		$styles['secondary_color'] ?? '',
		$styles['accent_color'] ?? '',
		$styles['border_color'] ?? '',
		$styles['light_bg_color'] ?? '',
	);

	$palette = array_values(
		array_unique(
			array_filter(
				array_map( 'lithia_normalize_hex_color', $palette )
			)
		)
	);

	usort(
		$palette,
		static function( string $left, string $right ): int {
			return lithia_get_hex_color_luminance( $left ) <=> lithia_get_hex_color_luminance( $right );
		}
	);

	return $palette;
}

/**
 * Replace all SVG hex colors with a palette remapped by luminance rank.
 *
 * @param string            $svg_markup Inline SVG markup.
 * @param array<int, string> $palette   Target palette.
 * @return string
 */
function lithia_map_svg_hex_colors_to_palette( string $svg_markup, array $palette ): string {
	$palette = array_values(
		array_unique(
			array_filter(
				array_map( 'lithia_normalize_hex_color', $palette )
			)
		)
	);

	if ( '' === $svg_markup || empty( $palette ) ) {
		return $svg_markup;
	}

	preg_match_all( '/#[0-9a-fA-F]{3,6}\b/', $svg_markup, $matches );
	$colors = array_values(
		array_unique(
			array_filter(
				array_map( 'lithia_normalize_hex_color', $matches[0] ?? array() )
			)
		)
	);

	if ( empty( $colors ) ) {
		return $svg_markup;
	}

	usort(
		$colors,
		static function( string $left, string $right ): int {
			return lithia_get_hex_color_luminance( $left ) <=> lithia_get_hex_color_luminance( $right );
		}
	);

	$color_count   = count( $colors );
	$palette_count = count( $palette );
	$color_map     = array();

	foreach ( $colors as $index => $color ) {
		$palette_index = ( 1 === $palette_count || 1 === $color_count )
			? 0
			: (int) round( ( $palette_count - 1 ) * ( $index / ( $color_count - 1 ) ) );

		$color_map[ $color ] = $palette[ $palette_index ];
	}

	return (string) preg_replace_callback(
		'/#[0-9a-fA-F]{3,6}\b/',
		static function( array $match ) use ( $color_map ): string {
			$normalized = lithia_normalize_hex_color( $match[0] );

			return $color_map[ $normalized ] ?? $match[0];
		},
		$svg_markup
	);
}

/**
 * Return a single theme color for monochrome SVG remapping.
 *
 * @param string $token Theme color token.
 * @return string
 */
function lithia_get_svg_background_single_color( string $token ): string {
	$map       = lithia_get_svg_background_color_token_map();
	$styles    = lithia_get_site_styles();
	$style_key = $map[ $token ] ?? $map['primary'];
	$color     = isset( $styles[ $style_key ] ) ? (string) $styles[ $style_key ] : '';

	return lithia_normalize_hex_color( $color );
}

/**
 * Convert a position option into an SVG preserveAspectRatio value.
 *
 * @param string $position Block position.
 * @return string
 */
function lithia_get_svg_background_preserve_aspect_ratio( string $position ): string {
	$map = array(
		'center'       => 'xMidYMid slice',
		'top'          => 'xMidYMin slice',
		'bottom'       => 'xMidYMax slice',
		'left'         => 'xMinYMid slice',
		'right'        => 'xMaxYMid slice',
		'top-left'     => 'xMinYMin slice',
		'top-right'    => 'xMaxYMin slice',
		'bottom-left'  => 'xMinYMax slice',
		'bottom-right' => 'xMaxYMax slice',
	);

	return $map[ $position ] ?? $map['center'];
}

/**
 * Build the inline SVG markup for the SVG Background block.
 *
 * @param array $attributes Block attributes.
 * @return string
 */
function lithia_get_svg_background_markup( array $attributes ): string {
	$attachment_id = ! empty( $attributes['svgId'] ) ? absint( $attributes['svgId'] ) : 0;

	if ( ! $attachment_id ) {
		return '';
	}

	$file_path = get_attached_file( $attachment_id );

	if ( ! is_string( $file_path ) || '' === $file_path || ! is_readable( $file_path ) ) {
		return '';
	}

	$extension = strtolower( (string) pathinfo( $file_path, PATHINFO_EXTENSION ) );
	$mime_type = (string) get_post_mime_type( $attachment_id );

	if ( 'image/svg+xml' !== $mime_type && ! in_array( $extension, array( 'svg', 'svgz' ), true ) ) {
		return '';
	}

	$color_mode = isset( $attributes['colorMode'] ) ? (string) $attributes['colorMode'] : 'theme-tones';
	if ( ! in_array( $color_mode, array( 'theme-tones', 'single', 'original' ), true ) ) {
		$color_mode = 'theme-tones';
	}

	$single_color_token = isset( $attributes['singleColorToken'] ) ? (string) $attributes['singleColorToken'] : 'primary';
	$position           = isset( $attributes['position'] ) ? (string) $attributes['position'] : 'center';
	$file_mtime         = file_exists( $file_path ) ? (string) filemtime( $file_path ) : '0';
	$styles_hash        = md5( wp_json_encode( lithia_get_site_styles() ) );
	$cache_key          = 'svg_background:' . md5(
		wp_json_encode(
			array(
				$attachment_id,
				$file_mtime,
				$color_mode,
				$single_color_token,
				$position,
				$styles_hash,
			)
		)
	);
	$cached_markup      = wp_cache_get( $cache_key, 'lithia_blocks' );

	if ( is_string( $cached_markup ) ) {
		return $cached_markup;
	}

	$svg_markup = file_get_contents( $file_path );

	if ( false === $svg_markup || '' === $svg_markup ) {
		return '';
	}

	if ( 'svgz' === $extension && function_exists( 'gzdecode' ) ) {
		$decoded_markup = gzdecode( $svg_markup );

		if ( false !== $decoded_markup && '' !== $decoded_markup ) {
			$svg_markup = $decoded_markup;
		}
	}

	$svg_markup = lithia_sanitize_inline_svg_markup( (string) $svg_markup );

	if ( '' === $svg_markup ) {
		return '';
	}

	if ( 'theme-tones' === $color_mode ) {
		$svg_markup = lithia_map_svg_hex_colors_to_palette( $svg_markup, lithia_get_svg_background_theme_palette() );
	} elseif ( 'single' === $color_mode ) {
		$single_color = lithia_get_svg_background_single_color( $single_color_token );

		if ( '' !== $single_color ) {
			$svg_markup = lithia_map_svg_hex_colors_to_palette( $svg_markup, array( $single_color ) );
		}
	}

	$document = new DOMDocument();
	$previous = libxml_use_internal_errors( true );
	$loaded   = $document->loadXML( $svg_markup, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_COMPACT );
	libxml_clear_errors();
	libxml_use_internal_errors( $previous );

	if ( ! $loaded || ! $document->documentElement ) {
		return '';
	}

	$root          = $document->documentElement;
	$current_class = trim( (string) $root->getAttribute( 'class' ) );

	$root->setAttribute( 'xmlns', 'http://www.w3.org/2000/svg' );
	$root->setAttribute( 'class', trim( $current_class . ' lithia-svg-background__svg' ) );
	$root->setAttribute( 'preserveAspectRatio', lithia_get_svg_background_preserve_aspect_ratio( $position ) );
	$root->setAttribute( 'width', '100%' );
	$root->setAttribute( 'height', '100%' );
	$root->setAttribute( 'aria-hidden', 'true' );
	$root->setAttribute( 'focusable', 'false' );

	$svg_markup = trim( (string) $document->saveXML( $root ) );

	wp_cache_set( $cache_key, $svg_markup, 'lithia_blocks', HOUR_IN_SECONDS );

	return $svg_markup;
}

/**
 * Render the SVG Background block.
 *
 * @param array  $attributes Block attributes.
 * @param string $content    Inner blocks content.
 * @return string
 */
function lithia_render_svg_background_block( array $attributes, string $content = '' ): string {
	$tone       = lithia_get_block_tone( $attributes, 'light' );
	$svg_markup = lithia_get_svg_background_markup( $attributes );
	$opacity    = isset( $attributes['opacity'] ) ? max( 0, min( 100, (int) $attributes['opacity'] ) ) : 44;
	$scale      = isset( $attributes['scale'] ) ? max( 50, min( 200, (int) $attributes['scale'] ) ) : 100;
	$min_height = isset( $attributes['minHeight'] )
		? lithia_sanitize_css_size_value( $attributes['minHeight'], 'clamp(420px, 58vh, 760px)' )
		: 'clamp(420px, 58vh, 760px)';

	if ( '' === trim( $content ) && '' === $svg_markup ) {
		return '';
	}

	$classes = array(
		'lithia-svg-background',
		'lithia-tone-' . $tone,
		'lw-section-' . $tone,
	);

	if ( '' !== $svg_markup ) {
		$classes[] = 'lithia-svg-background--has-svg';
	}

	$wrapper_attributes = get_block_wrapper_attributes(
		array(
			'class' => implode( ' ', $classes ),
		)
	);

	$style_attribute = sprintf(
		' style="%s"',
		esc_attr(
			'--lithia-svg-background-opacity:' . number_format( $opacity / 100, 2, '.', '' ) . '; ' .
			'--lithia-svg-background-scale:' . number_format( $scale / 100, 2, '.', '' ) . '; ' .
			'--lithia-svg-background-min-height:' . $min_height . ';'
		)
	);

	ob_start();
	?>
	<section <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php echo $style_attribute; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		<?php if ( '' !== $svg_markup ) : ?>
			<div class="lithia-svg-background__media" aria-hidden="true">
				<?php echo $svg_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
		<?php endif; ?>
		<div class="lithia-svg-background__inner lithia-shell">
			<div class="lithia-svg-background__content">
				<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
		</div>
	</section>
	<?php

	return (string) ob_get_clean();
}

/**
 * Extract a YouTube video ID from a supported URL or raw ID.
 *
 * @param string $value YouTube URL or raw video ID.
 * @return string
 */
function lithia_get_youtube_video_id( string $value ): string {
	$value = trim( $value );

	if ( '' === $value ) {
		return '';
	}

	if ( 1 === preg_match( '/^[A-Za-z0-9_-]{11}$/', $value ) ) {
		return $value;
	}

	$parsed_url = wp_parse_url( $value );

	if ( empty( $parsed_url['host'] ) ) {
		return '';
	}

	$host      = strtolower( $parsed_url['host'] );
	$path      = isset( $parsed_url['path'] ) ? trim( $parsed_url['path'], '/' ) : '';
	$video_id  = '';

	if ( in_array( $host, array( 'youtu.be', 'www.youtu.be' ), true ) && '' !== $path ) {
		$segments = explode( '/', $path );
		$video_id = $segments[0] ?? '';
	} elseif ( in_array( $host, array( 'youtube.com', 'www.youtube.com', 'm.youtube.com', 'music.youtube.com', 'youtube-nocookie.com', 'www.youtube-nocookie.com' ), true ) ) {
		if ( ! empty( $parsed_url['query'] ) ) {
			parse_str( $parsed_url['query'], $query_args );
			$video_id = isset( $query_args['v'] ) ? (string) $query_args['v'] : '';
		}

		if ( '' === $video_id && '' !== $path ) {
			$segments = explode( '/', $path );

			if ( isset( $segments[0], $segments[1] ) && in_array( $segments[0], array( 'embed', 'shorts', 'live' ), true ) ) {
				$video_id = $segments[1];
			}
		}
	}

	return 1 === preg_match( '/^[A-Za-z0-9_-]{11}$/', $video_id ) ? $video_id : '';
}

/**
 * Build the privacy-enhanced YouTube embed URL used for hero backgrounds.
 *
 * @param string $value YouTube URL or raw video ID.
 * @return string
 */
function lithia_get_youtube_background_embed_url( string $value ): string {
	$video_id = lithia_get_youtube_video_id( $value );

	if ( '' === $video_id ) {
		return '';
	}

	return (string) add_query_arg(
		array(
			'autoplay'        => '1',
			'controls'        => '0',
			'disablekb'       => '1',
			'fs'              => '0',
			'iv_load_policy'  => '3',
			'loop'            => '1',
			'modestbranding'  => '1',
			'mute'            => '1',
			'playlist'        => $video_id,
			'playsinline'     => '1',
			'rel'             => '0',
		),
		'https://www.youtube-nocookie.com/embed/' . rawurlencode( $video_id )
	);
}

/**
 * Render the Business Hero block.
 *
 * @param array  $attributes Block attributes.
 * @param string $content    Block content.
 * @param object $block      Parsed block data.
 * @return string
 */
function lithia_render_business_hero_block( array $attributes, string $content = '', $block = null ): string {
	$business_name = lithia_get_business_detail( 'business_name', '' );
	$city          = lithia_get_business_detail( 'city', '' );
	$tone          = lithia_get_block_tone( $attributes, 'dark' );
	$background_type = isset( $attributes['backgroundType'] ) && 'youtube' === $attributes['backgroundType']
		? 'youtube'
		: 'image';

	$eyebrow     = isset( $attributes['eyebrow'] ) ? $attributes['eyebrow'] : '';
	$text        = isset( $attributes['text'] ) ? $attributes['text'] : '';
	$button_text = isset( $attributes['buttonText'] ) ? $attributes['buttonText'] : '';
	$button_url  = isset( $attributes['buttonUrl'] ) ? $attributes['buttonUrl'] : '';
	$youtube_url = isset( $attributes['youtubeUrl'] ) ? (string) $attributes['youtubeUrl'] : '';

	$headline = ! empty( $attributes['useBusinessName'] )
		? $business_name
		: ( $attributes['headline'] ?? '' );

	$subheading = ! empty( $attributes['useCity'] )
		? $city
		: ( $attributes['subheading'] ?? '' );

	if ( '' === $headline ) {
		$headline = 'Business Name';
	}

	if ( '' === $subheading ) {
		$subheading = 'City';
	}

	$background_image_url = '';

	if ( ! empty( $attributes['backgroundImageId'] ) ) {
		$background_image_url = wp_get_attachment_image_url( (int) $attributes['backgroundImageId'], 'full' );
	}

	if ( ! $background_image_url && ! empty( $attributes['backgroundImageUrl'] ) ) {
		$background_image_url = $attributes['backgroundImageUrl'];
	}

	$youtube_embed_url = 'youtube' === $background_type
		? lithia_get_youtube_background_embed_url( $youtube_url )
		: '';

	$classes = array(
		'lithia-business-hero',
		'lithia-tone-' . $tone,
	);

	if ( '' !== $youtube_embed_url ) {
		$classes[] = 'lithia-business-hero--has-video';
	}

	$content_classes = array(
		'lithia-business-hero__content',
		'lithia-surface',
		'lithia-surface--padded',
	);

	$eyebrow_classes = array(
		'lithia-business-hero__eyebrow',
		'lithia-eyebrow',
	);

	$subheading_classes = array(
		'lithia-business-hero__subheading',
		'lithia-subheading',
	);

	$text_classes = array(
		'lithia-business-hero__text',
		'lithia-copy',
	);

	$button_classes = array(
		'wp-element-button',
		'lithia-business-hero__button',
		'lithia-button-link',
	);

	if ( 'dark' === $tone ) {
		$content_classes[]  = 'lithia-surface--glass';
		$eyebrow_classes[]  = 'lithia-eyebrow--light';
		$subheading_classes[] = 'lithia-subheading--light';
		$text_classes[]     = 'lithia-copy--light';
		$button_classes[]   = 'lithia-button-link--light';
	}

	$wrapper_attributes = get_block_wrapper_attributes(
		array(
			'class' => implode( ' ', $classes ),
		)
	);

	$style_attribute = '';
	if ( $background_image_url ) {
		$style_attribute = sprintf(
			' style="%s"',
			esc_attr( '--lithia-business-hero-image: url(' . esc_url_raw( $background_image_url ) . ');' )
		);
	}

	ob_start();
	?>
	<section <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php echo $style_attribute; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		<?php if ( '' !== $youtube_embed_url ) : ?>
			<div class="lithia-business-hero__media" aria-hidden="true">
				<iframe
					class="lithia-business-hero__video"
					src="<?php echo esc_url( $youtube_embed_url ); ?>"
					title="<?php echo esc_attr__( 'Decorative background video', 'lithia-web-service-theme' ); ?>"
					loading="eager"
					allow="autoplay; encrypted-media"
					referrerpolicy="strict-origin-when-cross-origin"
					tabindex="-1"
				></iframe>
			</div>
		<?php endif; ?>
		<div class="lithia-business-hero__inner lithia-shell">
			<div class="<?php echo esc_attr( implode( ' ', $content_classes ) ); ?>">
				<?php if ( $eyebrow ) : ?>
					<p class="<?php echo esc_attr( implode( ' ', $eyebrow_classes ) ); ?>"><?php echo esc_html( $eyebrow ); ?></p>
				<?php endif; ?>

				<h1 class="lithia-business-hero__headline lithia-heading-hero"><?php echo esc_html( $headline ); ?></h1>
				<p class="<?php echo esc_attr( implode( ' ', $subheading_classes ) ); ?>"><?php echo esc_html( $subheading ); ?></p>

				<?php if ( $text ) : ?>
					<div class="<?php echo esc_attr( implode( ' ', $text_classes ) ); ?>"><?php echo wp_kses_post( wpautop( $text ) ); ?></div>
				<?php endif; ?>

				<?php if ( $button_text && $button_url ) : ?>
					<div class="lithia-business-hero__actions lithia-actions">
						<a class="<?php echo esc_attr( implode( ' ', $button_classes ) ); ?>" href="<?php echo esc_url( $button_url ); ?>">
							<?php echo esc_html( $button_text ); ?>
						</a>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</section>
	<?php
	return (string) ob_get_clean();
}

/**
 * Render the Brand Intro block.
 *
 * @param array $attributes Block attributes.
 * @return string
 */
function lithia_render_brand_intro_block( array $attributes ): string {
	$brand = lithia_get_brand_content_defaults();
	$tone  = lithia_get_block_tone( $attributes );

	$eyebrow = ! empty( $attributes['eyebrow'] ) ? $attributes['eyebrow'] : $brand['intro_eyebrow'];
	$heading = ! empty( $attributes['heading'] ) ? $attributes['heading'] : $brand['intro_heading'];
	$text    = ! empty( $attributes['text'] ) ? $attributes['text'] : $brand['intro_paragraph'];

	$primary_cta = lithia_get_cta_data(
		! empty( $attributes['primaryLabel'] ) ? $attributes['primaryLabel'] : $brand['primary_cta_label'],
		! empty( $attributes['primaryUrl'] ) ? $attributes['primaryUrl'] : $brand['primary_cta_url'],
		'lithia-brand-intro__button lithia-button-link'
	);

	$secondary_cta = lithia_get_cta_data(
		! empty( $attributes['secondaryLabel'] ) ? $attributes['secondaryLabel'] : $brand['secondary_cta_label'],
		! empty( $attributes['secondaryUrl'] ) ? $attributes['secondaryUrl'] : $brand['secondary_cta_url'],
		'lithia-brand-intro__button lithia-button-link lithia-button-link--secondary lithia-brand-intro__button--secondary'
	);

	$wrapper_attributes = get_block_wrapper_attributes(
		array(
			'class' => 'lithia-brand-intro lithia-section lithia-tone-' . $tone,
		)
	);

	ob_start();
	?>
	<section <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		<div class="lithia-brand-intro__inner lithia-shell">
			<div class="lithia-brand-intro__content lithia-surface lithia-surface--padded">
				<?php if ( $eyebrow ) : ?>
					<p class="lithia-brand-intro__eyebrow lithia-eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
				<?php endif; ?>

				<h2 class="lithia-brand-intro__heading lithia-heading-xl"><?php echo esc_html( $heading ); ?></h2>

				<?php if ( $text ) : ?>
					<div class="lithia-brand-intro__text lithia-copy"><?php echo wp_kses_post( wpautop( $text ) ); ?></div>
				<?php endif; ?>

				<?php if ( ! empty( $attributes['showPrimaryCta'] ) || ! empty( $attributes['showSecondaryCta'] ) ) : ?>
					<div class="lithia-brand-intro__actions lithia-actions">
						<?php if ( ! empty( $attributes['showPrimaryCta'] ) && $primary_cta['label'] && $primary_cta['url'] ) : ?>
							<a class="<?php echo esc_attr( $primary_cta['class'] ); ?>" href="<?php echo esc_url( $primary_cta['url'] ); ?>">
								<?php echo esc_html( $primary_cta['label'] ); ?>
							</a>
						<?php endif; ?>

						<?php if ( ! empty( $attributes['showSecondaryCta'] ) && $secondary_cta['label'] && $secondary_cta['url'] ) : ?>
							<a class="<?php echo esc_attr( $secondary_cta['class'] ); ?>" href="<?php echo esc_url( $secondary_cta['url'] ); ?>">
								<?php echo esc_html( $secondary_cta['label'] ); ?>
							</a>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</section>
	<?php
	return (string) ob_get_clean();
}

/**
 * Render the Service Spotlight Loop block.
 *
 * @param array       $attributes Block attributes.
 * @param string      $content    Block content.
 * @param object|null $block      Parsed block object.
 * @return string
 */
function lithia_render_service_spotlight_loop_block( array $attributes, string $content = '', $block = null ): string {
	$tone             = lithia_get_block_tone( $attributes );
	$is_homepage      = lithia_is_homepage_block_context( $block );
	$homepage_content = $is_homepage ? lithia_get_homepage_content_defaults() : array();
	$eyebrow          = $is_homepage && '' !== ( $homepage_content['spotlight_eyebrow'] ?? '' )
		? (string) $homepage_content['spotlight_eyebrow']
		: ( ! empty( $attributes['eyebrow'] ) ? (string) $attributes['eyebrow'] : 'Service Spotlight' );
	$heading          = $is_homepage && '' !== ( $homepage_content['spotlight_heading'] ?? '' )
		? (string) $homepage_content['spotlight_heading']
		: ( ! empty( $attributes['heading'] ) ? (string) $attributes['heading'] : 'Start with one focused service' );
	$intro            = $is_homepage && '' !== ( $homepage_content['spotlight_intro'] ?? '' )
		? (string) $homepage_content['spotlight_intro']
		: ( ! empty( $attributes['intro'] ) ? (string) $attributes['intro'] : '' );
	$show_archive_cta = ! empty( $attributes['showArchiveCta'] );
	$archive_label    = $is_homepage && '' !== ( $homepage_content['spotlight_archive_label'] ?? '' )
		? (string) $homepage_content['spotlight_archive_label']
		: ( ! empty( $attributes['archiveLabel'] ) ? (string) $attributes['archiveLabel'] : 'View All Services' );
	$archive_url      = $is_homepage && '' !== ( $homepage_content['spotlight_archive_url'] ?? '' )
		? (string) $homepage_content['spotlight_archive_url']
		: ( ! empty( $attributes['archiveUrl'] ) ? (string) $attributes['archiveUrl'] : '/services/' );
	$slides           = lithia_get_homepage_spotlight_services();

	if ( '' === trim( $archive_label ) || '' === trim( $archive_url ) ) {
		$show_archive_cta = false;
	}

	$wrapper_attributes = get_block_wrapper_attributes(
		array(
			'class' => 'lithia-service-spotlight-loop lithia-section lithia-tone-' . $tone,
		)
	);

	ob_start();
	?>
	<section <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		<div class="lithia-service-spotlight-loop__inner lithia-shell">
			<div class="lithia-service-spotlight-loop__frame lithia-surface lithia-surface--padded">
				<div class="lithia-service-spotlight-loop__header">
					<div class="lithia-service-spotlight-loop__header-copy">
						<?php if ( $eyebrow ) : ?>
							<p class="lithia-service-spotlight-loop__eyebrow lithia-eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
						<?php endif; ?>

						<?php if ( $heading ) : ?>
							<h2 class="lithia-service-spotlight-loop__heading lithia-heading-xl"><?php echo esc_html( $heading ); ?></h2>
						<?php endif; ?>

						<?php if ( $intro ) : ?>
							<div class="lithia-service-spotlight-loop__intro lithia-copy"><?php echo wp_kses_post( wpautop( $intro ) ); ?></div>
						<?php endif; ?>
					</div>

					<?php if ( $show_archive_cta && $archive_label && $archive_url ) : ?>
						<a class="lithia-service-spotlight-loop__archive lithia-button-link lithia-button-link--secondary" href="<?php echo esc_url( $archive_url ); ?>">
							<?php echo esc_html( $archive_label ); ?>
						</a>
					<?php endif; ?>
				</div>

				<?php if ( empty( $slides ) ) : ?>
					<div class="lithia-service-spotlight-loop__empty lithia-copy">
						<p><?php esc_html_e( 'Enable Homepage Spotlight on one or more services to populate this loop.', 'lithia-web-service-theme' ); ?></p>
					</div>
				<?php else : ?>
					<div class="lithia-service-spotlight-loop__viewport" data-lithia-service-spotlight-viewport>
						<?php foreach ( $slides as $index => $slide ) : ?>
							<?php
							$primary_cta = lithia_get_cta_data(
								! empty( $slide['primary_cta_label'] ) ? (string) $slide['primary_cta_label'] : 'View Service',
								! empty( $slide['primary_cta_url'] ) ? (string) $slide['primary_cta_url'] : (string) $slide['url'],
								'lithia-service-spotlight-loop__button lithia-button-link'
							);
							$show_view_service = (string) $slide['url'] !== (string) $primary_cta['url'];
							$slide_details = array_slice( array_values( (array) ( $slide['details'] ?? array() ) ), 0, 2 );
							$is_active = 0 === $index;
							?>
							<article
								class="lithia-service-spotlight-loop__slide<?php echo $is_active ? ' is-active' : ''; ?>"
								data-lithia-service-spotlight-slide
								data-slide-index="<?php echo esc_attr( (string) $index ); ?>"
								aria-hidden="<?php echo $is_active ? 'false' : 'true'; ?>"
							>
								<?php if ( ! empty( $slide['image_url'] ) ) : ?>
									<div class="lithia-service-spotlight-loop__media">
										<img
											class="lithia-service-spotlight-loop__image"
											src="<?php echo esc_url( (string) $slide['image_url'] ); ?>"
											alt=""
											loading="lazy"
										/>
									</div>
								<?php endif; ?>

								<div class="lithia-service-spotlight-loop__content">
									<?php if ( ! empty( $slide['eyebrow'] ) ) : ?>
										<p class="lithia-service-spotlight-loop__service-eyebrow lithia-eyebrow"><?php echo esc_html( (string) $slide['eyebrow'] ); ?></p>
									<?php endif; ?>

									<h3 class="lithia-service-spotlight-loop__title lithia-heading-xl"><?php echo esc_html( (string) $slide['title'] ); ?></h3>

									<?php if ( ! empty( $slide['has_price'] ) && ! empty( $slide['price_display'] ) ) : ?>
										<p class="lithia-service-spotlight-loop__price">
											<span class="lithia-service-spotlight-loop__price-label"><?php echo esc_html( (string) $slide['price_label'] ); ?></span>
											<span class="lithia-service-spotlight-loop__price-value"><?php echo esc_html( (string) $slide['price_display'] ); ?></span>
										</p>
									<?php endif; ?>

									<?php if ( ! empty( $slide['excerpt'] ) ) : ?>
										<div class="lithia-service-spotlight-loop__text lithia-copy"><?php echo wp_kses_post( wpautop( (string) $slide['excerpt'] ) ); ?></div>
									<?php endif; ?>

									<?php if ( ! empty( $slide_details ) ) : ?>
										<ul class="lithia-service-spotlight-loop__details" aria-label="<?php esc_attr_e( 'Service details', 'lithia-web-service-theme' ); ?>">
											<?php foreach ( $slide_details as $detail ) : ?>
												<?php if ( empty( $detail['label'] ) || empty( $detail['value'] ) ) : ?>
													<?php continue; ?>
												<?php endif; ?>
												<li class="lithia-service-spotlight-loop__detail">
													<span class="lithia-service-spotlight-loop__detail-label"><?php echo esc_html( (string) $detail['label'] ); ?></span>
													<span class="lithia-service-spotlight-loop__detail-value"><?php echo esc_html( (string) $detail['value'] ); ?></span>
												</li>
											<?php endforeach; ?>
										</ul>
									<?php endif; ?>

									<div class="lithia-service-spotlight-loop__actions lithia-actions">
										<?php if ( $primary_cta['label'] && $primary_cta['url'] ) : ?>
											<a class="<?php echo esc_attr( $primary_cta['class'] ); ?>" href="<?php echo esc_url( $primary_cta['url'] ); ?>">
												<?php echo esc_html( $primary_cta['label'] ); ?>
											</a>
										<?php endif; ?>

										<?php if ( $show_view_service && ! empty( $slide['url'] ) ) : ?>
											<a class="lithia-service-spotlight-loop__button lithia-button-link lithia-button-link--secondary" href="<?php echo esc_url( (string) $slide['url'] ); ?>">
												<?php esc_html_e( 'View Service', 'lithia-web-service-theme' ); ?>
											</a>
										<?php endif; ?>
									</div>
								</div>
							</article>
						<?php endforeach; ?>
					</div>

					<?php if ( count( $slides ) > 1 ) : ?>
						<div class="lithia-service-spotlight-loop__controls" aria-label="<?php esc_attr_e( 'Service spotlight navigation', 'lithia-web-service-theme' ); ?>">
							<button type="button" class="lithia-service-spotlight-loop__nav lithia-service-spotlight-loop__nav--prev" data-lithia-service-spotlight-prev>
								<?php esc_html_e( 'Previous', 'lithia-web-service-theme' ); ?>
							</button>
							<div class="lithia-service-spotlight-loop__dots">
								<?php foreach ( $slides as $index => $slide ) : ?>
									<button
										type="button"
										class="lithia-service-spotlight-loop__dot<?php echo 0 === $index ? ' is-active' : ''; ?>"
										data-lithia-service-spotlight-dot
										data-slide-index="<?php echo esc_attr( (string) $index ); ?>"
										aria-label="<?php echo esc_attr( sprintf( __( 'Show %s', 'lithia-web-service-theme' ), (string) $slide['title'] ) ); ?>"
										aria-current="<?php echo 0 === $index ? 'true' : 'false'; ?>"
									></button>
								<?php endforeach; ?>
							</div>
							<button type="button" class="lithia-service-spotlight-loop__nav lithia-service-spotlight-loop__nav--next" data-lithia-service-spotlight-next>
								<?php esc_html_e( 'Next', 'lithia-web-service-theme' ); ?>
							</button>
						</div>
					<?php endif; ?>
				<?php endif; ?>
			</div>
		</div>
	</section>
	<?php

	return (string) ob_get_clean();
}

/**
 * Render the Mission Statement block.
 *
 * @param array       $attributes Block attributes.
 * @param string      $content    Block content.
 * @param object|null $block      Parsed block object.
 * @return string
 */
function lithia_render_mission_statement_block( array $attributes, string $content = '', $block = null ): string {
	$brand            = lithia_get_brand_content_defaults();
	$is_homepage      = lithia_is_homepage_block_context( $block );
	$homepage_content = $is_homepage ? lithia_get_homepage_content_defaults() : array();
	$label            = $is_homepage && '' !== ( $homepage_content['mission_label'] ?? '' )
		? (string) $homepage_content['mission_label']
		: ( ! empty( $attributes['label'] ) ? $attributes['label'] : 'Mission' );
	$mission          = $is_homepage && '' !== ( $homepage_content['mission_text'] ?? '' )
		? (string) $homepage_content['mission_text']
		: ( ! empty( $attributes['missionText'] ) ? $attributes['missionText'] : $brand['mission_statement'] );
	$tone             = lithia_get_block_tone( $attributes );
	$image_id         = $is_homepage && ! empty( $homepage_content['mission_image_id'] )
		? (int) $homepage_content['mission_image_id']
		: ( ! empty( $attributes['imageId'] ) ? (int) $attributes['imageId'] : 0 );
	$image_url        = ! empty( $attributes['imageUrl'] ) ? (string) $attributes['imageUrl'] : '';
	$image_alt        = $is_homepage && '' !== ( $homepage_content['mission_image_alt'] ?? '' )
		? (string) $homepage_content['mission_image_alt']
		: ( ! empty( $attributes['imageAlt'] ) ? (string) $attributes['imageAlt'] : '' );

	$image_html = '';

	if ( $image_id ) {
		$image_args = array(
			'class' => 'lithia-mission-statement__image',
		);

		if ( '' !== $image_alt ) {
			$image_args['alt'] = $image_alt;
		}

		$image_html = wp_get_attachment_image( $image_id, 'large', false, $image_args );
	}

	if ( ! $image_html && $image_url ) {
		$image_html = sprintf(
			'<img class="lithia-mission-statement__image" src="%1$s" alt="%2$s" loading="lazy" decoding="async" />',
			esc_url( $image_url ),
			esc_attr( $image_alt )
		);
	}

	$inner_classes = array(
		'lithia-mission-statement__inner',
		'lithia-shell',
		'lithia-surface',
		'lithia-surface--padded',
	);

	if ( $image_html ) {
		$inner_classes[] = 'has-mission-image';
	}

	$wrapper_attributes = get_block_wrapper_attributes(
		array(
			'class' => 'lithia-mission-statement lithia-section lithia-tone-' . $tone,
		)
	);

	ob_start();
	?>
	<section <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		<div class="<?php echo esc_attr( implode( ' ', $inner_classes ) ); ?>">
			<?php if ( $image_html ) : ?>
				<div class="lithia-mission-statement__media">
					<?php echo $image_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
			<?php endif; ?>
			<div class="lithia-mission-statement__content">
				<?php if ( $label ) : ?>
					<p class="lithia-mission-statement__label lithia-eyebrow"><?php echo esc_html( $label ); ?></p>
				<?php endif; ?>
				<div class="lithia-mission-statement__text lithia-display-statement"><?php echo wp_kses_post( wpautop( $mission ) ); ?></div>
			</div>
		</div>
	</section>
	<?php
	return (string) ob_get_clean();
}

/**
 * Render the About Summary block.
 *
 * @param array       $attributes Block attributes.
 * @param string      $content    Block content.
 * @param object|null $block      Parsed block object.
 * @return string
 */
function lithia_render_about_summary_block( array $attributes, string $content = '', $block = null ): string {
	$brand            = lithia_get_brand_content_defaults();
	$is_homepage      = lithia_is_homepage_block_context( $block );
	$homepage_content = $is_homepage ? lithia_get_homepage_content_defaults() : array();
	$tone             = lithia_get_block_tone( $attributes );

	$eyebrow = $is_homepage && '' !== ( $homepage_content['about_eyebrow'] ?? '' )
		? (string) $homepage_content['about_eyebrow']
		: ( ! empty( $attributes['eyebrow'] ) ? $attributes['eyebrow'] : 'About Us' );
	$heading = $is_homepage && '' !== ( $homepage_content['about_heading'] ?? '' )
		? (string) $homepage_content['about_heading']
		: ( ! empty( $attributes['heading'] ) ? $attributes['heading'] : 'A closer look at the business' );
	$text    = $is_homepage && '' !== ( $homepage_content['about_text'] ?? '' )
		? (string) $homepage_content['about_text']
		: ( ! empty( $attributes['text'] ) ? $attributes['text'] : $brand['about_summary'] );
	$image_id = $is_homepage && ! empty( $homepage_content['about_image_id'] )
		? (int) $homepage_content['about_image_id']
		: ( ! empty( $attributes['imageId'] ) ? (int) $attributes['imageId'] : 0 );
	$image_url = ! empty( $attributes['imageUrl'] ) ? (string) $attributes['imageUrl'] : '';
	$image_alt = $is_homepage && '' !== ( $homepage_content['about_image_alt'] ?? '' )
		? (string) $homepage_content['about_image_alt']
		: ( ! empty( $attributes['imageAlt'] ) ? (string) $attributes['imageAlt'] : '' );
	$button_label = $is_homepage && '' !== ( $homepage_content['about_button_label'] ?? '' )
		? (string) $homepage_content['about_button_label']
		: ( ! empty( $attributes['buttonLabel'] ) ? $attributes['buttonLabel'] : $brand['secondary_cta_label'] );
	$button_url   = $is_homepage && '' !== ( $homepage_content['about_button_url'] ?? '' )
		? (string) $homepage_content['about_button_url']
		: ( ! empty( $attributes['buttonUrl'] ) ? $attributes['buttonUrl'] : $brand['secondary_cta_url'] );

	$image_html = '';

	if ( $image_id ) {
		$image_args = array(
			'class' => 'lithia-about-summary__image',
		);

		if ( '' !== $image_alt ) {
			$image_args['alt'] = $image_alt;
		}

		$image_html = wp_get_attachment_image( $image_id, 'large', false, $image_args );
	}

	if ( ! $image_html && $image_url ) {
		$image_html = sprintf(
			'<img class="lithia-about-summary__image" src="%1$s" alt="%2$s" loading="lazy" decoding="async" />',
			esc_url( $image_url ),
			esc_attr( $image_alt )
		);
	}

	$inner_classes = array(
		'lithia-about-summary__inner',
		'lithia-shell',
		'lithia-surface',
		'lithia-surface--padded',
	);

	if ( $image_html ) {
		$inner_classes[] = 'has-about-image';
	}

	$wrapper_attributes = get_block_wrapper_attributes(
		array(
			'class' => 'lithia-about-summary lithia-section lithia-tone-' . $tone,
		)
	);

	ob_start();
	?>
	<section <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		<div class="<?php echo esc_attr( implode( ' ', $inner_classes ) ); ?>">
			<?php if ( $image_html ) : ?>
				<div class="lithia-about-summary__media">
					<?php echo $image_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
			<?php endif; ?>

			<div class="lithia-about-summary__body">
				<?php if ( $eyebrow ) : ?>
					<p class="lithia-about-summary__eyebrow lithia-eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
				<?php endif; ?>
				<h2 class="lithia-about-summary__heading lithia-heading-xl"><?php echo esc_html( $heading ); ?></h2>
				<div class="lithia-about-summary__text lithia-copy"><?php echo wp_kses_post( wpautop( $text ) ); ?></div>

				<?php if ( ! empty( $attributes['showButton'] ) && $button_label && $button_url ) : ?>
					<div class="lithia-about-summary__actions lithia-actions">
						<a class="lithia-about-summary__button lithia-button-link" href="<?php echo esc_url( $button_url ); ?>">
							<?php echo esc_html( $button_label ); ?>
						</a>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</section>
	<?php
	return (string) ob_get_clean();
}

/**
 * Render the Contact Details block.
 *
 * @param array $attributes Block attributes.
 * @return string
 */
function lithia_render_contact_details_block( array $attributes ): string {
	$tone          = lithia_get_block_tone( $attributes );
	$heading       = ! empty( $attributes['heading'] ) ? (string) $attributes['heading'] : 'Contact Details';
	$intro         = ! empty( $attributes['intro'] ) ? (string) $attributes['intro'] : '';
	$business_name = trim( (string) lithia_get_business_detail( 'business_name', get_bloginfo( 'name' ) ) );
	$address_lines = lithia_get_business_address_lines();
	$phone_raw     = trim( (string) lithia_get_business_detail( 'business_phone', '' ) );
	$phone_display = $phone_raw ? lithia_format_phone_number( $phone_raw ) : '';
	$phone_href    = $phone_raw ? 'tel:' . preg_replace( '/[^\d+]/', '', $phone_raw ) : '';
	$email         = sanitize_email( (string) lithia_get_business_detail( 'business_email', '' ) );
	$hours         = trim( (string) lithia_get_business_detail( 'business_hours', '' ) );
	$notice        = trim( (string) lithia_get_business_detail( 'booking_notice', '' ) );
	$show_hours    = ! empty( $attributes['showHours'] );
	$show_notice   = ! empty( $attributes['showNotice'] );

	$wrapper_attributes = get_block_wrapper_attributes(
		array(
			'class' => 'lithia-contact-details lithia-section lithia-tone-' . $tone,
		)
	);

	ob_start();
	?>
	<section <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		<div class="lithia-contact-details__inner lithia-shell">
			<div class="lithia-contact-details__content lithia-surface lithia-surface--padded">
				<?php if ( $heading ) : ?>
					<h2 class="lithia-contact-details__heading lithia-heading-xl"><?php echo esc_html( $heading ); ?></h2>
				<?php endif; ?>

				<?php if ( $intro ) : ?>
					<div class="lithia-contact-details__intro lithia-copy"><?php echo wp_kses_post( wpautop( $intro ) ); ?></div>
				<?php endif; ?>

				<div class="lithia-contact-details__grid">
					<?php if ( $business_name || ! empty( $address_lines ) ) : ?>
						<div class="lithia-contact-details__item">
							<p class="lithia-contact-details__label lithia-eyebrow">Address</p>
							<address class="lithia-contact-details__value lithia-copy">
								<?php if ( $business_name ) : ?>
									<strong class="lithia-contact-details__business-name"><?php echo esc_html( $business_name ); ?></strong><br />
								<?php endif; ?>
								<?php foreach ( $address_lines as $line ) : ?>
									<span><?php echo esc_html( $line ); ?></span><br />
								<?php endforeach; ?>
							</address>
						</div>
					<?php endif; ?>

					<?php if ( $phone_display && $phone_href ) : ?>
						<div class="lithia-contact-details__item">
							<p class="lithia-contact-details__label lithia-eyebrow">Phone</p>
							<p class="lithia-contact-details__value lithia-copy">
								<a href="<?php echo esc_url( $phone_href ); ?>"><?php echo esc_html( $phone_display ); ?></a>
							</p>
						</div>
					<?php endif; ?>

					<?php if ( $email ) : ?>
						<div class="lithia-contact-details__item">
							<p class="lithia-contact-details__label lithia-eyebrow">Email</p>
							<p class="lithia-contact-details__value lithia-copy">
								<a href="<?php echo esc_url( 'mailto:' . $email ); ?>"><?php echo esc_html( $email ); ?></a>
							</p>
						</div>
					<?php endif; ?>

					<?php if ( $show_hours && $hours ) : ?>
						<div class="lithia-contact-details__item">
							<p class="lithia-contact-details__label lithia-eyebrow">Hours</p>
							<div class="lithia-contact-details__value lithia-copy"><?php echo wp_kses_post( wpautop( $hours ) ); ?></div>
						</div>
					<?php endif; ?>

					<?php if ( $show_notice && $notice ) : ?>
						<div class="lithia-contact-details__item">
							<p class="lithia-contact-details__label lithia-eyebrow">Booking Notice</p>
							<div class="lithia-contact-details__value lithia-copy"><?php echo wp_kses_post( wpautop( $notice ) ); ?></div>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</section>
	<?php

	return (string) ob_get_clean();
}

/**
 * Render the Contact Form block.
 *
 * @param array $attributes Block attributes.
 * @return string
 */
function lithia_render_contact_form_block( array $attributes ): string {
	$tone    = lithia_get_block_tone( $attributes, 'dark' );
	$heading = ! empty( $attributes['heading'] ) ? (string) $attributes['heading'] : 'Send a Message';
	$intro   = ! empty( $attributes['intro'] ) ? (string) $attributes['intro'] : '';
	$form    = lithia_render_managed_contact_form();

	$wrapper_attributes = get_block_wrapper_attributes(
		array(
			'class' => 'lithia-contact-form lithia-section lithia-tone-' . $tone,
		)
	);

	ob_start();
	?>
	<section <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		<div class="lithia-contact-form__inner lithia-shell">
			<div class="lithia-contact-form__content lithia-surface lithia-surface--padded">
				<?php if ( $heading ) : ?>
					<h2 class="lithia-contact-form__heading lithia-heading-xl"><?php echo esc_html( $heading ); ?></h2>
				<?php endif; ?>

				<?php if ( $intro ) : ?>
					<div class="lithia-contact-form__intro lithia-copy"><?php echo wp_kses_post( wpautop( $intro ) ); ?></div>
				<?php endif; ?>

				<div class="lithia-contact-form__form">
					<?php if ( $form ) : ?>
						<?php echo $form; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php else : ?>
						<p class="lithia-contact-form__empty lithia-copy">The contact form is currently unavailable.</p>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</section>
	<?php

	return (string) ob_get_clean();
}

/**
 * Render the Brand CTA Pair block.
 *
 * @param array       $attributes Block attributes.
 * @param string      $content    Block content.
 * @param object|null $block      Parsed block object.
 * @return string
 */
function lithia_render_brand_cta_pair_block( array $attributes, string $content = '', $block = null ): string {
	$brand            = lithia_get_brand_content_defaults();
	$is_homepage      = lithia_is_homepage_block_context( $block );
	$homepage_content = $is_homepage ? lithia_get_homepage_content_defaults() : array();
	$tone             = lithia_get_block_tone( $attributes );

	$primary_cta = lithia_get_cta_data(
		$is_homepage && '' !== ( $homepage_content['primary_cta_label'] ?? '' )
			? (string) $homepage_content['primary_cta_label']
			: ( ! empty( $attributes['primaryLabel'] ) ? $attributes['primaryLabel'] : $brand['primary_cta_label'] ),
		$is_homepage && '' !== ( $homepage_content['primary_cta_url'] ?? '' )
			? (string) $homepage_content['primary_cta_url']
			: ( ! empty( $attributes['primaryUrl'] ) ? $attributes['primaryUrl'] : $brand['primary_cta_url'] ),
		'lithia-brand-cta-pair__button lithia-button-link'
	);

	$secondary_cta = lithia_get_cta_data(
		$is_homepage && '' !== ( $homepage_content['secondary_cta_label'] ?? '' )
			? (string) $homepage_content['secondary_cta_label']
			: ( ! empty( $attributes['secondaryLabel'] ) ? $attributes['secondaryLabel'] : $brand['secondary_cta_label'] ),
		$is_homepage && '' !== ( $homepage_content['secondary_cta_url'] ?? '' )
			? (string) $homepage_content['secondary_cta_url']
			: ( ! empty( $attributes['secondaryUrl'] ) ? $attributes['secondaryUrl'] : $brand['secondary_cta_url'] ),
		'lithia-brand-cta-pair__button lithia-button-link lithia-button-link--secondary lithia-brand-cta-pair__button--secondary'
	);

	$show_primary   = ! empty( $attributes['showPrimaryCta'] ) && $primary_cta['label'] && $primary_cta['url'];
	$show_secondary = ! empty( $attributes['showSecondaryCta'] ) && $secondary_cta['label'] && $secondary_cta['url'];

	if ( ! $show_primary && ! $show_secondary ) {
		return '';
	}

	$wrapper_attributes = get_block_wrapper_attributes(
		array(
			'class' => 'lithia-brand-cta-pair lithia-section lithia-tone-' . $tone,
		)
	);

	ob_start();
	?>
	<section <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		<div class="lithia-brand-cta-pair__inner lithia-shell lithia-surface">
			<div class="lithia-brand-cta-pair__actions lithia-actions lithia-actions--center">
				<?php if ( $show_primary && $primary_cta['label'] && $primary_cta['url'] ) : ?>
					<a class="<?php echo esc_attr( $primary_cta['class'] ); ?>" href="<?php echo esc_url( $primary_cta['url'] ); ?>">
						<?php echo esc_html( $primary_cta['label'] ); ?>
					</a>
				<?php endif; ?>

				<?php if ( $show_secondary && $secondary_cta['label'] && $secondary_cta['url'] ) : ?>
					<a class="<?php echo esc_attr( $secondary_cta['class'] ); ?>" href="<?php echo esc_url( $secondary_cta['url'] ); ?>">
						<?php echo esc_html( $secondary_cta['label'] ); ?>
					</a>
				<?php endif; ?>
			</div>
		</div>
	</section>
	<?php
	return (string) ob_get_clean();
}

/**
 * Build a TOC entry payload for the service page TOC.
 *
 * @param string $id      Heading or section anchor.
 * @param string $content Heading label.
 * @param int    $level   Heading level.
 * @return array
 */
function lithia_get_service_toc_entry( string $id, string $content, int $level = 2 ): array {
	return array(
		'key'             => $id,
		'content'         => $content,
		'level'           => $level,
		'link'            => '#' . $id,
		'disable'         => false,
		'isUpdated'       => false,
		'isGeneratedLink' => true,
	);
}

/**
 * Generate a unique anchor for service page sections and headings.
 *
 * @param string $base     Anchor seed.
 * @param array  $used_ids Anchors already in use.
 * @return string
 */
function lithia_get_unique_service_anchor( string $base, array &$used_ids ): string {
	$base = sanitize_title( $base );

	if ( '' === $base ) {
		$base = 'service-section';
	}

	$anchor  = $base;
	$counter = 2;

	while ( in_array( $anchor, $used_ids, true ) ) {
		$anchor = $base . '-' . $counter;
		++$counter;
	}

	$used_ids[] = $anchor;

	return $anchor;
}

/**
 * Add stable heading IDs to service content and return TOC entries.
 *
 * @param string $content_html Rendered service content.
 * @param array  $reserved_ids Section IDs already claimed in the template.
 * @return array{content_html:string,headings:array}
 */
function lithia_get_service_content_toc_data( string $content_html, array $reserved_ids = array() ): array {
	$content_html = trim( $content_html );

	if ( '' === $content_html || ! class_exists( 'DOMDocument' ) ) {
		return array(
			'content_html' => $content_html,
			'headings'     => array(),
		);
	}

	$internal_errors = libxml_use_internal_errors( true );
	$dom             = new DOMDocument( '1.0', 'UTF-8' );
	$loaded          = $dom->loadHTML(
		'<?xml encoding="utf-8" ?><div id="lithia-service-content-root">' . $content_html . '</div>',
		LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
	);
	libxml_clear_errors();
	libxml_use_internal_errors( $internal_errors );

	if ( ! $loaded ) {
		return array(
			'content_html' => $content_html,
			'headings'     => array(),
		);
	}

	$root = $dom->getElementById( 'lithia-service-content-root' );

	if ( ! $root instanceof DOMElement ) {
		return array(
			'content_html' => $content_html,
			'headings'     => array(),
		);
	}

	$xpath    = new DOMXPath( $dom );
	$nodes    = $xpath->query( './/h2 | .//h3 | .//h4 | .//h5 | .//h6', $root );
	$used_ids = array_values( array_filter( array_map( 'sanitize_title', $reserved_ids ) ) );
	$headings = array();

	if ( false === $nodes ) {
		return array(
			'content_html' => $content_html,
			'headings'     => array(),
		);
	}

	foreach ( $nodes as $node ) {
		if ( ! $node instanceof DOMElement ) {
			continue;
		}

		$heading_text = trim( wp_strip_all_tags( $node->textContent ) );

		if ( '' === $heading_text ) {
			continue;
		}

		$existing_id = sanitize_title( $node->getAttribute( 'id' ) );
		$anchor      = '' !== $existing_id ? $existing_id : $heading_text;
		$anchor      = lithia_get_unique_service_anchor( $anchor, $used_ids );

		$node->setAttribute( 'id', $anchor );

		$level      = (int) preg_replace( '/[^0-9]/', '', strtolower( $node->tagName ) );
		$headings[] = lithia_get_service_toc_entry( $anchor, $heading_text, $level > 0 ? $level : 2 );
	}

	$updated_html = '';

	foreach ( $root->childNodes as $child ) {
		$updated_html .= $dom->saveHTML( $child );
	}

	return array(
		'content_html' => $updated_html,
		'headings'     => $headings,
	);
}

/**
 * Render a grouped service content section.
 *
 * @param string $body_html    Section body markup.
 * @param string $heading_html Optional section heading markup.
 * @return string
 */
function lithia_render_service_content_section_markup( string $body_html, string $heading_html = '' ): string {
	$body_html    = trim( $body_html );
	$heading_html = trim( $heading_html );

	if ( '' === trim( wp_strip_all_tags( $heading_html . ' ' . $body_html ) ) ) {
		return '';
	}

	ob_start();
	?>
	<section class="lithia-service-content__section lithia-surface lithia-surface--padded<?php echo '' === $heading_html ? ' is-intro' : ''; ?>">
		<div class="lithia-service-content__section-grid<?php echo '' === $heading_html ? ' is-intro' : ''; ?>">
			<?php if ( '' !== $heading_html ) : ?>
				<div class="lithia-service-content__section-header">
					<?php echo $heading_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
			<?php endif; ?>

			<?php if ( '' !== $body_html ) : ?>
				<div class="lithia-service-content__section-body lithia-copy">
					<?php echo $body_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
			<?php endif; ?>
		</div>
	</section>
	<?php

	return (string) ob_get_clean();
}

/**
 * Split rendered service content into grouped sections based on top-level headings.
 *
 * @param string $content_html Rendered service content with heading anchors.
 * @return string
 */
function lithia_get_service_content_sections_markup( string $content_html ): string {
	$content_html = trim( $content_html );

	if ( '' === $content_html ) {
		return '';
	}

	if ( ! class_exists( 'DOMDocument' ) ) {
		return lithia_render_service_content_section_markup( $content_html );
	}

	$internal_errors = libxml_use_internal_errors( true );
	$dom             = new DOMDocument( '1.0', 'UTF-8' );
	$loaded          = $dom->loadHTML(
		'<?xml encoding="utf-8" ?><div id="lithia-service-content-root">' . $content_html . '</div>',
		LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
	);
	libxml_clear_errors();
	libxml_use_internal_errors( $internal_errors );

	if ( ! $loaded ) {
		return lithia_render_service_content_section_markup( $content_html );
	}

	$root = $dom->getElementById( 'lithia-service-content-root' );

	if ( ! $root instanceof DOMElement ) {
		return lithia_render_service_content_section_markup( $content_html );
	}

	$child_nodes    = array();
	$section_level  = 0;
	$sections_html  = '';
	$current_body   = '';
	$current_heading = '';

	foreach ( $root->childNodes as $child ) {
		$child_nodes[] = $child;

		if ( ! $child instanceof DOMElement || ! preg_match( '/^h([2-6])$/i', $child->tagName, $matches ) ) {
			continue;
		}

		$level = (int) $matches[1];

		if ( 0 === $section_level || $level < $section_level ) {
			$section_level = $level;
		}
	}

	foreach ( $child_nodes as $child ) {
		if ( $child instanceof DOMText && '' === trim( $child->textContent ) ) {
			continue;
		}

		if ( $child instanceof DOMElement && $section_level > 0 && preg_match( '/^h([2-6])$/i', $child->tagName, $matches ) ) {
			$level = (int) $matches[1];

			if ( $level === $section_level ) {
				$sections_html  .= lithia_render_service_content_section_markup( $current_body, $current_heading );
				$current_heading = trim( $dom->saveHTML( $child ) );
				$current_body    = '';
				continue;
			}
		}

		$current_body .= $dom->saveHTML( $child );
	}

	$sections_html .= lithia_render_service_content_section_markup( $current_body, $current_heading );

	if ( '' === trim( $sections_html ) ) {
		return lithia_render_service_content_section_markup( $content_html );
	}

	return $sections_html;
}

/**
 * Build TOC data for a service page.
 *
 * @param array $service_data Normalized service data.
 * @return array{content_html:string,headings:array,section_ids:array}
 */
function lithia_get_service_page_toc_data( array $service_data ): array {
	$headings    = array();
	$section_ids = array();

	if ( ! empty( $service_data['details'] ) ) {
		$section_ids['details'] = 'service-details';
		$headings[]             = lithia_get_service_toc_entry( $section_ids['details'], 'Service Details', 2 );
	}

	if ( '' !== trim( (string) ( $service_data['overview_text'] ?? '' ) ) ) {
		$section_ids['overview'] = 'service-overview';
		$headings[]              = lithia_get_service_toc_entry(
			$section_ids['overview'],
			(string) ( $service_data['overview_heading'] ?? 'Overview' ),
			2
		);
	}

	if ( ! empty( $service_data['process_steps'] ) ) {
		$section_ids['process'] = 'service-process';
		$headings[]             = lithia_get_service_toc_entry(
			$section_ids['process'],
			(string) ( $service_data['process_heading'] ?? 'Process' ),
			2
		);
	}

	if ( ! empty( $service_data['highlights'] ) ) {
		$section_ids['highlights'] = 'service-highlights';
		$headings[]                = lithia_get_service_toc_entry(
			$section_ids['highlights'],
			(string) ( $service_data['highlights_heading'] ?? 'Highlights' ),
			2
		);
	}

	$content_toc_data = lithia_get_service_content_toc_data(
		(string) ( $service_data['content_html'] ?? '' ),
		array_values( $section_ids )
	);

	if ( '' !== trim( $content_toc_data['content_html'] ) && empty( $content_toc_data['headings'] ) ) {
		$section_ids['content'] = 'service-content';
		$headings[]             = lithia_get_service_toc_entry( $section_ids['content'], 'More About This Service', 2 );
	}

	return array(
		'content_html' => $content_toc_data['content_html'],
		'headings'     => array_merge( $headings, $content_toc_data['headings'] ),
		'section_ids'  => $section_ids,
	);
}

/**
 * Render TOC markup for service pages.
 *
 * @param array  $headings     Rank Math-compatible heading payloads.
 * @param string $title        TOC title.
 * @param bool   $with_wrapper Whether to wrap the markup in a service section.
 * @return string
 */
function lithia_render_service_toc_markup( array $headings, string $title = 'Table of Contents', bool $with_wrapper = true ): string {
	$headings = array_values( array_filter( $headings ) );

	if ( empty( $headings ) ) {
		return '';
	}

	$toc_markup = '';

	if ( WP_Block_Type_Registry::get_instance()->is_registered( 'rank-math/toc-block' ) ) {
		$toc_markup = render_block(
			array(
				'blockName' => 'rank-math/toc-block',
				'attrs'     => array(
					'headings'     => $headings,
					'listStyle'    => 'ul',
					'titleWrapper' => 'h2',
					'title'        => $title,
				),
			)
		);
	}

	if ( '' === trim( $toc_markup ) ) {
		ob_start();
		?>
		<div class="wp-block-rank-math-toc-block" id="rank-math-toc">
			<h2><?php echo esc_html( $title ); ?></h2>
			<nav>
				<ul>
					<?php foreach ( $headings as $heading ) : ?>
						<li>
							<a href="<?php echo esc_url( $heading['link'] ); ?>"><?php echo esc_html( $heading['content'] ); ?></a>
						</li>
					<?php endforeach; ?>
				</ul>
			</nav>
		</div>
		<?php
		$toc_markup = (string) ob_get_clean();
	}

	if ( ! $with_wrapper ) {
		return $toc_markup;
	}

	ob_start();
	?>
	<section class="lithia-service-toc lithia-section">
		<div class="lithia-service-toc__inner lithia-shell lithia-surface lithia-surface--padded">
			<?php echo $toc_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
	</section>
	<?php
	return (string) ob_get_clean();
}

/**
 * Group service TOC headings into top-level sections with nested children.
 *
 * @param array $headings Rank Math-compatible heading payloads.
 * @return array
 */
function lithia_get_service_sidebar_toc_groups( array $headings ): array {
	$headings = array_values( array_filter( $headings ) );

	if ( empty( $headings ) ) {
		return array();
	}

	$top_level = 6;

	foreach ( $headings as $heading ) {
		$level = max( 2, (int) ( $heading['level'] ?? 2 ) );

		if ( $level < $top_level ) {
			$top_level = $level;
		}
	}

	$groups = array();

	foreach ( $headings as $heading ) {
		$level = max( 2, (int) ( $heading['level'] ?? 2 ) );

		if ( empty( $groups ) || $level <= $top_level ) {
			$groups[] = array(
				'heading'  => $heading,
				'children' => array(),
			);
			continue;
		}

		$groups[ array_key_last( $groups ) ]['children'][] = $heading;
	}

	return $groups;
}

/**
 * Render grouped TOC markup for the service sidebar.
 *
 * @param array  $headings Rank Math-compatible heading payloads.
 * @param string $title    TOC title.
 * @return string
 */
function lithia_render_service_sidebar_toc_markup( array $headings, string $title = 'Page Guide' ): string {
	$groups = lithia_get_service_sidebar_toc_groups( $headings );

	if ( empty( $groups ) ) {
		return '';
	}

	ob_start();
	?>
	<div class="lithia-service-sidebar__page-guide">
		<h2 class="lithia-service-sidebar__page-guide-title"><?php echo esc_html( $title ); ?></h2>

		<nav class="lithia-service-sidebar__page-guide-nav" aria-label="<?php echo esc_attr( $title ); ?>">
			<div class="lithia-service-sidebar__page-guide-items">
				<?php foreach ( $groups as $group ) : ?>
					<?php
					$heading  = (array) ( $group['heading'] ?? array() );
					$children = array_values( array_filter( (array) ( $group['children'] ?? array() ) ) );

					if ( empty( $heading['content'] ) || empty( $heading['link'] ) ) {
						continue;
					}
					?>
					<div class="lithia-service-sidebar__page-guide-item<?php echo ! empty( $children ) ? ' has-children' : ''; ?>">
						<a class="lithia-service-sidebar__page-guide-link" href="<?php echo esc_url( (string) $heading['link'] ); ?>">
							<?php echo esc_html( (string) $heading['content'] ); ?>
						</a>

						<?php if ( ! empty( $children ) ) : ?>
							<details class="lithia-service-sidebar__page-guide-details">
								<summary class="lithia-service-sidebar__page-guide-summary">
									<span class="lithia-service-sidebar__page-guide-summary-text">
										<?php
										printf(
											/* translators: %s: subsection count. */
											esc_html(
												_n( '%s subsection', '%s subsections', count( $children ), 'lithia-web-service-theme' )
											),
											esc_html( number_format_i18n( count( $children ) ) )
										);
										?>
									</span>
								</summary>

								<ul class="lithia-service-sidebar__page-guide-children">
									<?php foreach ( $children as $child ) : ?>
										<?php if ( empty( $child['content'] ) || empty( $child['link'] ) ) : ?>
											<?php continue; ?>
										<?php endif; ?>

										<li class="lithia-service-sidebar__page-guide-child">
											<a class="lithia-service-sidebar__page-guide-child-link" href="<?php echo esc_url( (string) $child['link'] ); ?>">
												<?php echo esc_html( (string) $child['content'] ); ?>
											</a>
										</li>
									<?php endforeach; ?>
								</ul>
							</details>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
		</nav>
	</div>
	<?php

	return (string) ob_get_clean();
}

/**
 * Filter service TOC entries so the sidebar page guide only points into main content.
 *
 * @param array $headings    Rank Math-compatible heading payloads.
 * @param array $section_ids Named service section anchors.
 * @return array
 */
function lithia_get_service_sidebar_page_guide_headings( array $headings, array $section_ids = array() ): array {
	$excluded_ids = array_values(
		array_filter(
			array(
				(string) ( $section_ids['details'] ?? '' ),
				(string) ( $section_ids['process'] ?? '' ),
				(string) ( $section_ids['highlights'] ?? '' ),
			)
		)
	);

	return array_values(
		array_filter(
			$headings,
			static function ( $heading ) use ( $excluded_ids ): bool {
				$link = (string) ( $heading['link'] ?? '' );
				$id   = ltrim( $link, '#' );

				return '' !== $id && ! in_array( $id, $excluded_ids, true );
			}
		)
	);
}

/**
 * Render a service-page sidebar accordion item.
 *
 * @param string $id           Item ID.
 * @param string $title        Item title.
 * @param string $content_html Item body HTML.
 * @param bool   $is_open      Whether the item should start open.
 * @return string
 */
function lithia_render_service_sidebar_accordion_item( string $id, string $title, string $content_html, bool $is_open = false ): string {
	if ( '' === trim( wp_strip_all_tags( $content_html ) ) ) {
		return '';
	}

	ob_start();
	?>
	<details class="lithia-service-sidebar__accordion-item" id="<?php echo esc_attr( $id ); ?>"<?php echo $is_open ? ' open' : ''; ?>>
		<summary class="lithia-service-sidebar__summary">
			<span class="lithia-service-sidebar__summary-text"><?php echo esc_html( $title ); ?></span>
		</summary>

		<div class="lithia-service-sidebar__panel">
			<?php echo $content_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
	</details>
	<?php

	return (string) ob_get_clean();
}

/**
 * Add the generated service TOC to Rank Math's analyzed content.
 *
 * @param string $content Content collected by Rank Math.
 * @return string
 */
function lithia_add_service_toc_to_rank_math_content( string $content ): string {
	$post_id = 0;

	if ( isset( $_GET['post'] ) ) {
		$post_id = absint( wp_unslash( $_GET['post'] ) );
	} elseif ( isset( $_POST['post_ID'] ) ) {
		$post_id = absint( wp_unslash( $_POST['post_ID'] ) );
	}

	if ( ! $post_id ) {
		global $post;

		if ( $post instanceof WP_Post ) {
			$post_id = (int) $post->ID;
		}
	}

	if ( ! $post_id || 'services' !== get_post_type( $post_id ) ) {
		return $content;
	}

	$service_data = lithia_get_service_page_data( $post_id );

	if ( empty( $service_data ) ) {
		return $content;
	}

	$toc_data = lithia_get_service_page_toc_data( $service_data );

	if ( empty( $toc_data['headings'] ) ) {
		return $content;
	}

	return lithia_render_service_toc_markup( $toc_data['headings'], 'Table of Contents', false ) . $content;
}
add_filter( 'rank_math_content', 'lithia_add_service_toc_to_rank_math_content' );

/**
 * Treat Rank Math as a TOC-capable plugin for Services entries.
 *
 * @param array $plugins Registered TOC plugins.
 * @return array
 */
function lithia_add_rank_math_to_service_toc_plugins( array $plugins ): array {
	$post_type = get_post_type();

	if ( function_exists( 'get_current_screen' ) ) {
		$screen = get_current_screen();

		if ( $screen && ! empty( $screen->post_type ) ) {
			$post_type = $screen->post_type;
		}
	}

	if ( 'services' !== $post_type ) {
		return $plugins;
	}

	$plugins['seo-by-rank-math/rank-math.php'] = 'Rank Math SEO';

	return $plugins;
}
add_filter( 'rank_math/researches/toc_plugins', 'lithia_add_rank_math_to_service_toc_plugins' );

/**
 * Render the Services single-page block.
 *
 * @param array       $attributes Block attributes.
 * @param string      $content    Block content.
 * @param object|null $block      Parsed block object.
 * @return string
 */
function lithia_render_service_page_block( array $attributes, string $content = '', $block = null ): string {
	$post_id      = lithia_get_current_block_post_id( $block );
	$service_data = $post_id ? lithia_get_service_page_data( $post_id ) : array();

	$wrapper_attributes = get_block_wrapper_attributes(
		array(
			'class' => 'lithia-service-page-block',
		)
	);

	if ( empty( $service_data ) ) {
		ob_start();
		?>
		<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<section class="lithia-service-overview lithia-section">
				<div class="lithia-service-overview__inner lithia-shell lithia-surface lithia-surface--padded">
					<div class="lithia-service-overview__grid">
						<div class="lithia-service-overview__header">
							<p class="lithia-eyebrow">Services</p>
							<h2 class="lithia-heading-xl">Service template preview</h2>
						</div>
						<div class="lithia-service-overview__body lithia-copy">
							<p>This template pulls the hero, CTA buttons, overview copy, highlights, and process steps from the Service Page Fields meta box on each Service entry.</p>
						</div>
					</div>
				</div>
			</section>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	$hero_style = '';

	if ( ! empty( $service_data['hero_image_url'] ) ) {
		$hero_style = sprintf(
			' style="%s"',
			esc_attr( '--lithia-service-hero-image: url(' . esc_url_raw( $service_data['hero_image_url'] ) . ');' )
		);
	}

	$primary_cta = lithia_get_cta_data(
		$service_data['primary_cta_label'],
		$service_data['primary_cta_url'],
		'lithia-service-hero__button lithia-button-link'
	);

	$secondary_cta = lithia_get_cta_data(
		$service_data['secondary_cta_label'],
		$service_data['secondary_cta_url'],
		'lithia-service-hero__button lithia-button-link lithia-button-link--secondary'
	);

	$has_details    = ! empty( $service_data['details'] );
	$has_overview   = '' !== trim( $service_data['overview_text'] );
	$has_highlights = ! empty( $service_data['highlights'] );
	$has_process    = ! empty( $service_data['process_steps'] );
	$booking_form_html = lithia_render_single_service_booking_form( $post_id );
	$has_booking_note  = '' !== trim( $service_data['booking_note'] );
	$has_booking_form  = '' !== trim( $booking_form_html );
	$has_hero_booking  = $has_booking_form || $has_booking_note;
	$booking_drawer_id = 'lithia-service-booking-drawer-' . $post_id;
	$drawer_cta_label  = $primary_cta['label'] ?: __( 'Book Appointment', 'lithia-web-service-theme' );
	$drawer_cta_url    = $primary_cta['url'] ?: home_url( '/book-appointment/' );
	$has_inline_primary_cta = ! $has_booking_form && $primary_cta['label'] && $primary_cta['url'];
	$has_secondary_cta      = $secondary_cta['label'] && $secondary_cta['url'];
	$toc_data                  = lithia_get_service_page_toc_data( $service_data );
	$sidebar_page_guide_headings = lithia_get_service_sidebar_page_guide_headings( $toc_data['headings'], $toc_data['section_ids'] );
	$has_toc                   = ! empty( $sidebar_page_guide_headings );
	$service_data['content_html'] = $toc_data['content_html'];
	$service_content_markup       = lithia_get_service_content_sections_markup( $service_data['content_html'] );
	$has_content                  = '' !== trim( $service_content_markup );
	$sidebar_accordion_markup = '';
	$has_open_sidebar_item    = false;

	if ( $has_details ) {
		ob_start();
		?>
		<div class="lithia-service-sidebar__detail-grid">
			<?php foreach ( $service_data['details'] as $detail ) : ?>
				<div class="lithia-service-sidebar__detail">
					<p class="lithia-service-sidebar__detail-label"><?php echo esc_html( $detail['label'] ); ?></p>
					<p class="lithia-service-sidebar__detail-value"><?php echo esc_html( $detail['value'] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
		<?php
		$sidebar_accordion_markup .= lithia_render_service_sidebar_accordion_item(
			$toc_data['section_ids']['details'] ?? 'service-details',
			'Service Details',
			(string) ob_get_clean(),
			true
		);
		$has_open_sidebar_item = true;
	}

	if ( $has_highlights ) {
		ob_start();
		?>
		<div class="lithia-service-sidebar__stack">
			<?php foreach ( $service_data['highlights'] as $highlight ) : ?>
				<article class="lithia-service-sidebar__stack-item">
					<?php if ( ! empty( $highlight['item_title'] ) ) : ?>
						<h3 class="lithia-service-sidebar__stack-title"><?php echo esc_html( $highlight['item_title'] ); ?></h3>
					<?php endif; ?>

					<?php if ( ! empty( $highlight['item_text'] ) ) : ?>
						<div class="lithia-service-sidebar__stack-text lithia-copy"><?php echo wp_kses_post( wpautop( $highlight['item_text'] ) ); ?></div>
					<?php endif; ?>
				</article>
			<?php endforeach; ?>
		</div>
		<?php
		$sidebar_accordion_markup .= lithia_render_service_sidebar_accordion_item(
			$toc_data['section_ids']['highlights'] ?? 'service-highlights',
			$service_data['highlights_heading'],
			(string) ob_get_clean(),
			! $has_open_sidebar_item
		);
		$has_open_sidebar_item = true;
	}

	if ( $has_process ) {
		ob_start();
		?>
		<ol class="lithia-service-sidebar__steps">
			<?php foreach ( $service_data['process_steps'] as $index => $step ) : ?>
				<li class="lithia-service-sidebar__step">
					<span class="lithia-service-sidebar__step-number"><?php echo esc_html( str_pad( (string) ( $index + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>

					<div class="lithia-service-sidebar__step-body">
						<?php if ( ! empty( $step['step_title'] ) ) : ?>
							<h3 class="lithia-service-sidebar__step-title"><?php echo esc_html( $step['step_title'] ); ?></h3>
						<?php endif; ?>

						<?php if ( ! empty( $step['step_text'] ) ) : ?>
							<div class="lithia-service-sidebar__step-text lithia-copy"><?php echo wp_kses_post( wpautop( $step['step_text'] ) ); ?></div>
						<?php endif; ?>
					</div>
				</li>
			<?php endforeach; ?>
		</ol>
		<?php
		$sidebar_accordion_markup .= lithia_render_service_sidebar_accordion_item(
			$toc_data['section_ids']['process'] ?? 'service-process',
			$service_data['process_heading'],
			(string) ob_get_clean(),
			! $has_open_sidebar_item
		);
	}

	$has_sidebar = $has_toc || '' !== trim( $sidebar_accordion_markup );

		ob_start();
		?>
			<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
				<section class="lithia-service-hero lithia-tone-dark<?php echo $has_hero_booking ? ' has-booking' : ''; ?>"<?php echo $hero_style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
					<div class="lithia-service-hero__inner lithia-shell">
						<div class="lithia-service-hero__grid">
							<div class="lithia-service-hero__content lithia-surface lithia-surface--padded lithia-surface--glass">
								<?php if ( $service_data['eyebrow'] ) : ?>
									<p class="lithia-service-hero__eyebrow lithia-eyebrow"><?php echo esc_html( $service_data['eyebrow'] ); ?></p>
								<?php endif; ?>

								<h1 class="lithia-service-hero__title lithia-heading-hero"><?php echo esc_html( $service_data['title'] ); ?></h1>

								<?php if ( $service_data['hero_text'] ) : ?>
									<div class="lithia-service-hero__text lithia-copy"><?php echo wp_kses_post( wpautop( $service_data['hero_text'] ) ); ?></div>
								<?php endif; ?>

								<?php if ( $has_inline_primary_cta || $has_secondary_cta ) : ?>
									<div class="lithia-service-hero__actions lithia-actions">
										<?php if ( $has_inline_primary_cta ) : ?>
											<a class="<?php echo esc_attr( $primary_cta['class'] ); ?>" href="<?php echo esc_url( $primary_cta['url'] ); ?>">
												<?php echo esc_html( $primary_cta['label'] ); ?>
											</a>
										<?php endif; ?>

										<?php if ( $has_secondary_cta ) : ?>
											<a class="<?php echo esc_attr( $secondary_cta['class'] ); ?>" href="<?php echo esc_url( $secondary_cta['url'] ); ?>">
												<?php echo esc_html( $secondary_cta['label'] ); ?>
											</a>
										<?php endif; ?>
									</div>
								<?php endif; ?>
							</div>

								<?php if ( $has_hero_booking ) : ?>
									<div class="lithia-service-hero__booking">
										<div class="lithia-service-hero__booking-card lithia-surface lithia-surface--padded lithia-surface--glass">
											<p class="lithia-service-hero__booking-eyebrow lithia-eyebrow">Book Now</p>
											<h2 class="lithia-service-hero__booking-heading">Start with a planning call</h2>

											<?php if ( $has_booking_note ) : ?>
												<div class="lithia-service-hero__booking-copy lithia-copy"><?php echo wp_kses_post( wpautop( $service_data['booking_note'] ) ); ?></div>
											<?php endif; ?>

											<?php if ( $has_booking_form ) : ?>
												<div class="lithia-service-hero__booking-actions lithia-actions">
													<a
														class="lithia-service-hero__button lithia-button-link"
														href="<?php echo esc_url( $drawer_cta_url ); ?>"
														data-service-booking-open
														data-service-booking-target="<?php echo esc_attr( $booking_drawer_id ); ?>"
														aria-controls="<?php echo esc_attr( $booking_drawer_id ); ?>"
														aria-expanded="false"
													>
														<?php echo esc_html( $drawer_cta_label ); ?>
													</a>
												</div>
											<?php elseif ( $primary_cta['label'] && $primary_cta['url'] ) : ?>
												<div class="lithia-service-hero__booking-actions lithia-actions">
													<a class="<?php echo esc_attr( $primary_cta['class'] ); ?>" href="<?php echo esc_url( $primary_cta['url'] ); ?>">
														<?php echo esc_html( $primary_cta['label'] ); ?>
													</a>
												</div>
											<?php endif; ?>
										</div>
									</div>
								<?php endif; ?>
							</div>
						</div>
					</section>

				<?php if ( $has_booking_form ) : ?>
					<div
						class="lithia-service-drawer"
						id="<?php echo esc_attr( $booking_drawer_id ); ?>"
						hidden
						aria-hidden="true"
					>
						<div class="lithia-service-drawer__backdrop" data-service-booking-close></div>
						<div class="lithia-service-drawer__panel lithia-tone-dark" role="dialog" aria-modal="true" aria-labelledby="<?php echo esc_attr( $booking_drawer_id ); ?>-title">
							<div class="lithia-service-drawer__header">
								<div class="lithia-service-drawer__header-copy">
									<p class="lithia-eyebrow">Book Now</p>
									<h2 class="lithia-service-drawer__title" id="<?php echo esc_attr( $booking_drawer_id ); ?>-title">Start with a planning call</h2>
								</div>

								<button type="button" class="lithia-service-drawer__close" data-service-booking-close aria-label="<?php esc_attr_e( 'Close booking panel', 'lithia-web-service-theme' ); ?>">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>

							<?php if ( $has_booking_note ) : ?>
								<div class="lithia-service-drawer__intro lithia-copy"><?php echo wp_kses_post( wpautop( $service_data['booking_note'] ) ); ?></div>
							<?php endif; ?>

							<div class="lithia-service-drawer__body">
								<div class="lithia-service-booking__widget">
									<div class="lithia-service-booking__widget-inner">
										<?php echo $booking_form_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
									</div>
								</div>
							</div>
						</div>
					</div>
				<?php endif; ?>

				<?php if ( $has_overview || $has_content || $has_sidebar ) : ?>
					<section class="lithia-service-main lithia-section">
						<div class="lithia-service-main__inner lithia-shell">
							<div class="lithia-service-main__grid">
							<div class="lithia-service-main__content">
								<?php if ( $has_overview ) : ?>
									<section class="lithia-service-main__panel lithia-service-overview lithia-surface lithia-surface--padded">
										<div class="lithia-service-section-heading">
											<p class="lithia-eyebrow">Overview</p>
											<h2 class="lithia-heading-xl" id="<?php echo esc_attr( $toc_data['section_ids']['overview'] ?? 'service-overview' ); ?>"><?php echo esc_html( $service_data['overview_heading'] ); ?></h2>
										</div>

										<div class="lithia-service-overview__body lithia-copy">
											<?php echo wp_kses_post( wpautop( $service_data['overview_text'] ) ); ?>
										</div>
									</section>
								<?php endif; ?>

								<?php if ( $has_content ) : ?>
									<section class="lithia-service-main__panel lithia-service-content" id="<?php echo esc_attr( $toc_data['section_ids']['content'] ?? 'service-content' ); ?>">
										<div class="lithia-service-content__sections">
											<?php echo $service_content_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
										</div>
									</section>
								<?php endif; ?>
							</div>

							<?php if ( $has_sidebar ) : ?>
								<aside class="lithia-service-main__sidebar" aria-label="<?php esc_attr_e( 'Service guide', 'lithia-web-service-theme' ); ?>">
									<div class="lithia-service-main__sidebar-inner">
										<div class="lithia-service-sidebar__page-header lithia-surface lithia-surface--padded">
											<p class="lithia-eyebrow">This Page</p>
											<h2 class="lithia-service-sidebar__page-title"><?php echo esc_html( $service_data['title'] ); ?></h2>
										</div>

										<?php if ( $has_toc ) : ?>
											<div class="lithia-service-sidebar__toc lithia-surface lithia-surface--padded">
												<?php echo lithia_render_service_sidebar_toc_markup( $sidebar_page_guide_headings, 'Page Guide' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
											</div>
										<?php endif; ?>

										<?php if ( '' !== trim( $sidebar_accordion_markup ) ) : ?>
											<div class="lithia-service-sidebar__accordion lithia-surface">
												<?php echo $sidebar_accordion_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
											</div>
										<?php endif; ?>
									</div>
								</aside>
								<?php endif; ?>
							</div>
						</div>
					</section>
				<?php endif; ?>

		</div>
		<?php
	return (string) ob_get_clean();
}

/**
 * Render the Provider single-page block.
 *
 * @param array       $attributes Block attributes.
 * @param string      $content    Block content.
 * @param object|null $block      Parsed block object.
 * @return string
 */
function lithia_render_provider_page_block( array $attributes, string $content = '', $block = null ): string {
	$post_id            = lithia_get_current_block_post_id( $block );
	$provider_data      = $post_id ? lithia_get_provider_page_data( $post_id ) : array();
	$wrapper_attributes = get_block_wrapper_attributes(
		array(
			'class' => 'lithia-provider-page-block',
		)
	);

	if ( empty( $provider_data ) ) {
		ob_start();
		?>
		<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<section class="lithia-provider-hero lithia-section">
				<div class="lithia-provider-hero__inner lithia-shell lithia-surface lithia-surface--padded">
					<div class="lithia-provider-hero__content">
						<p class="lithia-eyebrow">Provider</p>
						<h1 class="lithia-heading-xl">Provider template preview</h1>
						<div class="lithia-copy">
							<p>This template pulls the featured image, bio content, and related services from the Provider post and the service relationship meta.</p>
						</div>
					</div>
				</div>
			</section>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	$primary_cta = lithia_get_cta_data(
		$provider_data['primary_cta_label'],
		$provider_data['primary_cta_url'],
		'lithia-provider-hero__button lithia-button-link'
	);
	$secondary_cta = lithia_get_cta_data(
		$provider_data['secondary_cta_label'],
		$provider_data['secondary_cta_url'],
		'lithia-provider-hero__button lithia-button-link lithia-button-link--secondary'
	);
	$has_content          = '' !== trim( $provider_data['content_html'] );
	$has_related_services = ! empty( $provider_data['related_services'] );

	ob_start();
	?>
	<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		<section class="lithia-provider-hero lithia-section">
			<div class="lithia-provider-hero__inner lithia-shell lithia-surface lithia-surface--padded">
				<div class="lithia-provider-hero__grid">
					<?php if ( $provider_data['image_id'] || $provider_data['image_url'] ) : ?>
						<div class="lithia-provider-hero__media">
							<?php
							if ( $provider_data['image_id'] ) {
								echo wp_get_attachment_image( $provider_data['image_id'], 'large', false, array( 'class' => 'lithia-provider-hero__image' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							} elseif ( $provider_data['image_url'] ) {
								printf(
									'<img class="lithia-provider-hero__image" src="%1$s" alt="%2$s" loading="lazy" decoding="async" />',
									esc_url( $provider_data['image_url'] ),
									esc_attr( $provider_data['title'] )
								);
							}
							?>
						</div>
					<?php endif; ?>

					<div class="lithia-provider-hero__content">
						<p class="lithia-eyebrow"><?php echo esc_html( $provider_data['eyebrow'] ); ?></p>
						<h1 class="lithia-heading-xl"><?php echo esc_html( $provider_data['title'] ); ?></h1>

						<?php if ( $provider_data['summary'] ) : ?>
							<div class="lithia-provider-hero__summary lithia-copy"><?php echo wp_kses_post( wpautop( $provider_data['summary'] ) ); ?></div>
						<?php endif; ?>

						<?php if ( ( $primary_cta['label'] && $primary_cta['url'] ) || ( $secondary_cta['label'] && $secondary_cta['url'] ) ) : ?>
							<div class="lithia-provider-hero__actions lithia-actions">
								<?php if ( $primary_cta['label'] && $primary_cta['url'] ) : ?>
									<a class="<?php echo esc_attr( $primary_cta['class'] ); ?>" href="<?php echo esc_url( $primary_cta['url'] ); ?>">
										<?php echo esc_html( $primary_cta['label'] ); ?>
									</a>
								<?php endif; ?>

								<?php if ( $secondary_cta['label'] && $secondary_cta['url'] ) : ?>
									<a class="<?php echo esc_attr( $secondary_cta['class'] ); ?>" href="<?php echo esc_url( $secondary_cta['url'] ); ?>">
										<?php echo esc_html( $secondary_cta['label'] ); ?>
									</a>
								<?php endif; ?>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</section>

		<?php if ( $has_content ) : ?>
			<section class="lithia-provider-content lithia-section">
				<div class="lithia-provider-content__inner lithia-shell lithia-surface lithia-surface--padded">
					<div class="lithia-provider-content__body">
						<?php echo $provider_data['content_html']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
				</div>
			</section>
		<?php endif; ?>

		<?php if ( $has_related_services ) : ?>
			<section class="lithia-provider-related lithia-section">
				<div class="lithia-provider-related__inner lithia-shell">
					<div class="lithia-service-section-heading">
						<p class="lithia-eyebrow">Available Services</p>
						<h2 class="lithia-heading-xl">Services with <?php echo esc_html( $provider_data['title'] ); ?></h2>
					</div>

					<div class="lithia-provider-related__grid">
						<?php foreach ( $provider_data['related_services'] as $service ) : ?>
							<article class="lithia-services-feed-card">
								<h3 class="wp-block-post-title lithia-services-feed-card__title">
									<a href="<?php echo esc_url( $service['url'] ); ?>"><?php echo esc_html( $service['title'] ); ?></a>
								</h3>
								<div class="wp-block-post-excerpt lithia-services-feed-card__excerpt">
									<?php if ( $service['excerpt'] ) : ?>
										<p class="wp-block-post-excerpt__excerpt"><?php echo esc_html( $service['excerpt'] ); ?></p>
									<?php endif; ?>
									<p class="wp-block-post-excerpt__more-text">
										<a class="wp-block-post-excerpt__more-link" href="<?php echo esc_url( $service['url'] ); ?>">View Service</a>
									</p>
								</div>
							</article>
						<?php endforeach; ?>
					</div>
				</div>
			</section>
		<?php endif; ?>
	</div>
	<?php
	return (string) ob_get_clean();
}
