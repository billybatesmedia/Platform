<?php
/**
 * Frontend and template hardening for the V1 blueprint.
 *
 * @package LithiaWebServiceTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Force JetBooking to use cookies instead of PHP sessions for filter storage.
 *
 * @param mixed $value Stored value.
 * @return string
 */
function lithia_force_jet_booking_cookie_store( $value ): string {
	unset( $value );

	return 'cookies';
}
add_filter( 'jet-booking/settings/get/filters_store_type', 'lithia_force_jet_booking_cookie_store' );

/**
 * Remove JetBooking's frontend session bootstrap so cache headers stay usable.
 *
 * The plugin registers the session store globally even when cookies are the
 * active store, so the parse_request hook must be removed manually.
 *
 * @return void
 */
function lithia_disable_jet_booking_frontend_session_bootstrap(): void {
	if ( is_admin() || wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		return;
	}

	if ( ! function_exists( 'jet_abaf' ) ) {
		return;
	}

	$plugin = jet_abaf();

	if ( ! is_object( $plugin ) || empty( $plugin->stores ) || ! method_exists( $plugin->stores, 'get_store' ) ) {
		return;
	}

	$session_store = $plugin->stores->get_store( 'session' );

	if ( is_object( $session_store ) && method_exists( $session_store, 'init_session' ) ) {
		remove_action( 'parse_request', array( $session_store, 'init_session' ) );
	}
}
add_action( 'init', 'lithia_disable_jet_booking_frontend_session_bootstrap', 20 );

/**
 * Suppress WooCommerce hooked utility blocks in the header navigation.
 *
 * @param array|null $parsed_hooked_block Parsed hooked block.
 * @param string     $hooked_block_type   Hooked block type.
 * @param string     $relative_position   Relative insertion position.
 * @param array      $parsed_anchor_block Anchor block data.
 * @return array|null
 */
function lithia_suppress_header_woocommerce_hooked_blocks( $parsed_hooked_block, string $hooked_block_type, string $relative_position, array $parsed_anchor_block ) {
	if ( is_admin() ) {
		return $parsed_hooked_block;
	}

	if ( 'core/navigation' !== (string) ( $parsed_anchor_block['blockName'] ?? '' ) ) {
		return $parsed_hooked_block;
	}

	unset( $hooked_block_type, $relative_position );

	return null;
}
add_filter( 'hooked_block_woocommerce/mini-cart', 'lithia_suppress_header_woocommerce_hooked_blocks', 20, 4 );
add_filter( 'hooked_block_woocommerce/customer-account', 'lithia_suppress_header_woocommerce_hooked_blocks', 20, 4 );

/**
 * Remove header-level WooCommerce utility blocks that hurt accessibility and
 * are not part of the V1 product story.
 *
 * @param string $block_content Rendered block HTML.
 * @param array  $block         Parsed block data.
 * @return string
 */
function lithia_filter_frontend_woocommerce_utility_blocks( string $block_content, array $block ): string {
	if ( is_admin() ) {
		return $block_content;
	}

	$block_name = $block['blockName'] ?? '';

	if ( in_array( $block_name, array( 'woocommerce/customer-account', 'woocommerce/mini-cart' ), true ) ) {
		return '';
	}

	return $block_content;
}
add_filter( 'render_block', 'lithia_filter_frontend_woocommerce_utility_blocks', 10, 2 );

/**
 * Return the clean header template-part content from the theme file.
 *
 * @return string
 */
function lithia_get_clean_header_template_part_content(): string {
	$file_path = get_theme_file_path( 'parts/header.html' );

	if ( ! is_readable( $file_path ) ) {
		return '';
	}

	$content = file_get_contents( $file_path );

	return false === $content ? '' : trim( $content );
}

/**
 * Replace the legacy stored header template part when it still contains the
 * nested landmark / mini-cart version.
 *
 * @return void
 */
function lithia_maybe_repair_header_template_part(): void {
	$option_name = 'lithia_header_template_part_repaired_v1';

	if ( get_option( $option_name ) ) {
		return;
	}

	$header = get_page_by_path( 'header', OBJECT, 'wp_template_part' );

	if ( ! $header instanceof WP_Post ) {
		update_option( $option_name, current_time( 'mysql' ), false );
		return;
	}

	$current_content = (string) $header->post_content;
	$needs_repair    = false !== strpos( $current_content, 'woocommerce/mini-cart' )
		|| false !== strpos( $current_content, '"tagName":"header"' )
		|| false !== strpos( $current_content, '<header class="wp-block-group lithia-site-header"' );

	if ( $needs_repair ) {
		$replacement = lithia_get_clean_header_template_part_content();

		if ( '' !== $replacement ) {
			wp_update_post(
				array(
					'ID'           => (int) $header->ID,
					'post_content' => $replacement,
				)
			);
		}
	}

	update_option( $option_name, current_time( 'mysql' ), false );
}
add_action( 'after_setup_theme', 'lithia_maybe_repair_header_template_part', 20 );
