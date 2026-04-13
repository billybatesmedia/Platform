<?php
/**
 * Site Styles settings and design token output.
 *
 * @package LithiaWebServiceTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return the legacy site style tokens.
 *
 * @return array
 */
function lithia_get_legacy_site_style_defaults(): array {
	return array(
		'background_color'     => '#F7F4EE',
		'surface_color'        => '#EEE7DB',
		'text_color'           => '#1F1F1F',
		'muted_text_color'     => '#6B665F',
		'primary_color'        => '#2F4F46',
		'secondary_color'      => '#3F675C',
		'accent_color'         => '#B6864A',
		'border_color'         => '#D9D2C3',
		'light_bg_color'       => '#F7F4EE',
		'light_text_color'     => '#1F1F1F',
		'dark_bg_color'        => '#1F2C2A',
		'dark_text_color'      => '#F8F5EF',
		'button_text_color'    => '#FFFFFF',
		'button_bg_color'      => '#2F4F46',
		'button_border_color'  => '#2F4F46',
		'font_heading'         => '"Cormorant Garamond", Georgia, serif',
		'font_body'            => 'Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif',
		'font_size_base'       => '1.125rem',
		'font_size_small'      => '1rem',
		'font_size_h1'         => 'clamp(2.75rem, 6vw, 4.75rem)',
		'font_size_h2'         => 'clamp(2rem, 4vw, 3.25rem)',
		'font_size_h3'         => 'clamp(1.5rem, 3vw, 2.25rem)',
		'line_height_body'     => '1.6',
		'line_height_heading'  => '1.1',
		'font_weight_heading'  => '600',
		'font_weight_body'     => '400',
		'content_width'        => '100%',
		'wide_width'           => '100%',
		'section_padding_y'    => 'clamp(3rem, 6vw, 6rem)',
		'section_padding_x'    => 'clamp(1rem, 3vw, 2rem)',
		'block_gap'            => '1.5rem',
		'border_radius_small'  => '4px',
		'border_radius_medium' => '10px',
		'border_radius_large'  => '18px',
		'button_padding_y'     => '0.9rem',
		'button_padding_x'     => '1.25rem',
		'button_radius'        => '4px',
		'button_font_size'     => '1rem',
		'button_font_weight'   => '600',
		'shadow_soft'          => '0 10px 30px rgba(0,0,0,0.06)',
		'shadow_panel'         => '0 18px 60px rgba(0,0,0,0.08)',
		'header_blur_bg'       => 'rgba(247,244,238,0.72)',
	);
}

/**
 * Return the default site style tokens.
 *
 * @return array
 */
function lithia_get_site_style_defaults(): array {
	$defaults = lithia_get_legacy_site_style_defaults();

	$defaults['font_size_base']    = '1.0625rem';
	$defaults['font_size_small']   = '0.95rem';
	$defaults['font_size_h1']      = 'clamp(2.25rem, 5vw, 4rem)';
	$defaults['font_size_h2']      = 'clamp(1.7rem, 3.4vw, 2.65rem)';
	$defaults['font_size_h3']      = 'clamp(1.25rem, 2.2vw, 1.8rem)';
	$defaults['content_width']     = '820px';
	$defaults['wide_width']        = '1260px';
	$defaults['section_padding_y'] = 'clamp(1rem, 2vw, 2rem)';
	$defaults['section_padding_x'] = 'clamp(0.9rem, 2.4vw, 1.5rem)';
	$defaults['block_gap']         = '1.125rem';

	return $defaults;
}

/**
 * Return section definitions for the Site Styles screen.
 *
 * @return array
 */
function lithia_get_site_style_sections(): array {
	return array(
		'colors' => array(
			'title'       => __( 'Colors', 'lithia-web-service-theme' ),
			'description' => __( 'Foundation colors used across the theme shell, cards, links, and general text.', 'lithia-web-service-theme' ),
		),
		'contexts' => array(
			'title'       => __( 'Light / Dark Context', 'lithia-web-service-theme' ),
			'description' => __( 'Reusable contrast pairs for dark CTA bands, footers, and flexible section wrappers.', 'lithia-web-service-theme' ),
		),
		'typography' => array(
			'title'       => __( 'Typography', 'lithia-web-service-theme' ),
			'description' => __( 'Heading and body font stacks, base sizes, display sizes, and line-height controls.', 'lithia-web-service-theme' ),
		),
		'layout' => array(
			'title'       => __( 'Layout', 'lithia-web-service-theme' ),
			'description' => __( 'Content width, wide width, spacing rhythm, and reusable radius tokens.', 'lithia-web-service-theme' ),
		),
		'buttons' => array(
			'title'       => __( 'Buttons', 'lithia-web-service-theme' ),
			'description' => __( 'Default button colors, padding, radius, and typography for theme buttons and CTAs.', 'lithia-web-service-theme' ),
		),
		'effects' => array(
			'title'       => __( 'Effects', 'lithia-web-service-theme' ),
			'description' => __( 'Reusable shadows and header surface treatment values.', 'lithia-web-service-theme' ),
		),
	);
}

/**
 * Return Site Styles field definitions.
 *
 * @return array
 */
function lithia_get_site_style_fields(): array {
	return array(
		'background_color' => array(
			'section'     => 'colors',
			'label'       => __( 'Background Color', 'lithia-web-service-theme' ),
			'type'        => 'color',
			'description' => __( 'Main page background color.', 'lithia-web-service-theme' ),
		),
		'surface_color' => array(
			'section'     => 'colors',
			'label'       => __( 'Surface Color', 'lithia-web-service-theme' ),
			'type'        => 'color',
			'description' => __( 'Cards, shells, and soft panel backgrounds.', 'lithia-web-service-theme' ),
		),
		'text_color' => array(
			'section'     => 'colors',
			'label'       => __( 'Text Color', 'lithia-web-service-theme' ),
			'type'        => 'color',
			'description' => __( 'Primary body and heading text color.', 'lithia-web-service-theme' ),
		),
		'muted_text_color' => array(
			'section'     => 'colors',
			'label'       => __( 'Muted Text Color', 'lithia-web-service-theme' ),
			'type'        => 'color',
			'description' => __( 'Secondary body copy, meta text, and softer supporting copy.', 'lithia-web-service-theme' ),
		),
		'primary_color' => array(
			'section'     => 'colors',
			'label'       => __( 'Primary Color', 'lithia-web-service-theme' ),
			'type'        => 'color',
			'description' => __( 'Primary brand color for links, dark panels, and emphasis.', 'lithia-web-service-theme' ),
		),
		'secondary_color' => array(
			'section'     => 'colors',
			'label'       => __( 'Secondary Color', 'lithia-web-service-theme' ),
			'type'        => 'color',
			'description' => __( 'Secondary interactive and hover color.', 'lithia-web-service-theme' ),
		),
		'accent_color' => array(
			'section'     => 'colors',
			'label'       => __( 'Accent Color', 'lithia-web-service-theme' ),
			'type'        => 'color',
			'description' => __( 'Accent highlight for eyebrows and special emphasis.', 'lithia-web-service-theme' ),
		),
		'border_color' => array(
			'section'     => 'colors',
			'label'       => __( 'Border Color', 'lithia-web-service-theme' ),
			'type'        => 'color',
			'description' => __( 'Default border color for panels, fields, and dividers.', 'lithia-web-service-theme' ),
		),
		'light_bg_color' => array(
			'section'     => 'contexts',
			'label'       => __( 'Light Background Color', 'lithia-web-service-theme' ),
			'type'        => 'color',
			'description' => __( 'Background used by `.lw-section-light`.', 'lithia-web-service-theme' ),
		),
		'light_text_color' => array(
			'section'     => 'contexts',
			'label'       => __( 'Light Text Color', 'lithia-web-service-theme' ),
			'type'        => 'color',
			'description' => __( 'Text used inside `.lw-section-light`.', 'lithia-web-service-theme' ),
		),
		'dark_bg_color' => array(
			'section'     => 'contexts',
			'label'       => __( 'Dark Background Color', 'lithia-web-service-theme' ),
			'type'        => 'color',
			'description' => __( 'Background used by `.lw-section-dark` and dark hero surfaces.', 'lithia-web-service-theme' ),
		),
		'dark_text_color' => array(
			'section'     => 'contexts',
			'label'       => __( 'Dark Text Color', 'lithia-web-service-theme' ),
			'type'        => 'color',
			'description' => __( 'Readable text color for dark contexts.', 'lithia-web-service-theme' ),
		),
		'font_heading' => array(
			'section'     => 'typography',
			'label'       => __( 'Heading Font Family', 'lithia-web-service-theme' ),
			'type'        => 'font_family',
			'description' => __( 'CSS font-family stack used for headings and display text.', 'lithia-web-service-theme' ),
		),
		'font_body' => array(
			'section'     => 'typography',
			'label'       => __( 'Body Font Family', 'lithia-web-service-theme' ),
			'type'        => 'font_family',
			'description' => __( 'CSS font-family stack used for body copy and UI text.', 'lithia-web-service-theme' ),
		),
		'font_size_base' => array(
			'section'     => 'typography',
			'label'       => __( 'Base Font Size', 'lithia-web-service-theme' ),
			'type'        => 'size',
			'description' => __( 'Accepts values like `1.125rem`.', 'lithia-web-service-theme' ),
		),
		'font_size_small' => array(
			'section'     => 'typography',
			'label'       => __( 'Small Font Size', 'lithia-web-service-theme' ),
			'type'        => 'size',
			'description' => __( 'Used for smaller body copy and support text.', 'lithia-web-service-theme' ),
		),
		'font_size_h1' => array(
			'section'     => 'typography',
			'label'       => __( 'H1 Font Size', 'lithia-web-service-theme' ),
			'type'        => 'size',
			'description' => __( 'Supports responsive values like `clamp(...)`.', 'lithia-web-service-theme' ),
		),
		'font_size_h2' => array(
			'section'     => 'typography',
			'label'       => __( 'H2 Font Size', 'lithia-web-service-theme' ),
			'type'        => 'size',
			'description' => __( 'Supports responsive values like `clamp(...)`.', 'lithia-web-service-theme' ),
		),
		'font_size_h3' => array(
			'section'     => 'typography',
			'label'       => __( 'H3 Font Size', 'lithia-web-service-theme' ),
			'type'        => 'size',
			'description' => __( 'Supports responsive values like `clamp(...)`.', 'lithia-web-service-theme' ),
		),
		'line_height_body' => array(
			'section'     => 'typography',
			'label'       => __( 'Body Line Height', 'lithia-web-service-theme' ),
			'type'        => 'number',
			'description' => __( 'Use a unitless value like `1.6`.', 'lithia-web-service-theme' ),
			'step'        => '0.05',
		),
		'line_height_heading' => array(
			'section'     => 'typography',
			'label'       => __( 'Heading Line Height', 'lithia-web-service-theme' ),
			'type'        => 'number',
			'description' => __( 'Use a unitless value like `1.1`.', 'lithia-web-service-theme' ),
			'step'        => '0.05',
		),
		'font_weight_heading' => array(
			'section'     => 'typography',
			'label'       => __( 'Heading Font Weight', 'lithia-web-service-theme' ),
			'type'        => 'weight',
			'description' => __( 'Use numeric values such as `600`.', 'lithia-web-service-theme' ),
			'step'        => '100',
		),
		'font_weight_body' => array(
			'section'     => 'typography',
			'label'       => __( 'Body Font Weight', 'lithia-web-service-theme' ),
			'type'        => 'weight',
			'description' => __( 'Use numeric values such as `400`.', 'lithia-web-service-theme' ),
			'step'        => '100',
		),
		'content_width' => array(
			'section'     => 'layout',
			'label'       => __( 'Content Width', 'lithia-web-service-theme' ),
			'type'        => 'size',
			'description' => __( 'Default constrained content width. Example: `720px`.', 'lithia-web-service-theme' ),
		),
		'wide_width' => array(
			'section'     => 'layout',
			'label'       => __( 'Wide Width', 'lithia-web-service-theme' ),
			'type'        => 'size',
			'description' => __( 'Wide layout width for shells and alignwide content.', 'lithia-web-service-theme' ),
		),
		'section_padding_y' => array(
			'section'     => 'layout',
			'label'       => __( 'Section Padding Y', 'lithia-web-service-theme' ),
			'type'        => 'size',
			'description' => __( 'Vertical section spacing. `clamp(...)` is supported.', 'lithia-web-service-theme' ),
		),
		'section_padding_x' => array(
			'section'     => 'layout',
			'label'       => __( 'Section Padding X', 'lithia-web-service-theme' ),
			'type'        => 'size',
			'description' => __( 'Horizontal shell padding. `clamp(...)` is supported.', 'lithia-web-service-theme' ),
		),
		'block_gap' => array(
			'section'     => 'layout',
			'label'       => __( 'Block Gap', 'lithia-web-service-theme' ),
			'type'        => 'size',
			'description' => __( 'Default spacing gap used across layouts.', 'lithia-web-service-theme' ),
		),
		'border_radius_small' => array(
			'section'     => 'layout',
			'label'       => __( 'Small Radius', 'lithia-web-service-theme' ),
			'type'        => 'size',
			'description' => __( 'Used for subtle UI rounding.', 'lithia-web-service-theme' ),
		),
		'border_radius_medium' => array(
			'section'     => 'layout',
			'label'       => __( 'Medium Radius', 'lithia-web-service-theme' ),
			'type'        => 'size',
			'description' => __( 'Used for fields and archive cards.', 'lithia-web-service-theme' ),
		),
		'border_radius_large' => array(
			'section'     => 'layout',
			'label'       => __( 'Large Radius', 'lithia-web-service-theme' ),
			'type'        => 'size',
			'description' => __( 'Used for shells, panels, and larger surfaces.', 'lithia-web-service-theme' ),
		),
		'button_text_color' => array(
			'section'     => 'buttons',
			'label'       => __( 'Button Text Color', 'lithia-web-service-theme' ),
			'type'        => 'color',
			'description' => __( 'Default button text color.', 'lithia-web-service-theme' ),
		),
		'button_bg_color' => array(
			'section'     => 'buttons',
			'label'       => __( 'Button Background Color', 'lithia-web-service-theme' ),
			'type'        => 'color',
			'description' => __( 'Default button background color.', 'lithia-web-service-theme' ),
		),
		'button_border_color' => array(
			'section'     => 'buttons',
			'label'       => __( 'Button Border Color', 'lithia-web-service-theme' ),
			'type'        => 'color',
			'description' => __( 'Default button border color.', 'lithia-web-service-theme' ),
		),
		'button_padding_y' => array(
			'section'     => 'buttons',
			'label'       => __( 'Button Padding Y', 'lithia-web-service-theme' ),
			'type'        => 'size',
			'description' => __( 'Vertical padding applied to theme buttons.', 'lithia-web-service-theme' ),
		),
		'button_padding_x' => array(
			'section'     => 'buttons',
			'label'       => __( 'Button Padding X', 'lithia-web-service-theme' ),
			'type'        => 'size',
			'description' => __( 'Horizontal padding applied to theme buttons.', 'lithia-web-service-theme' ),
		),
		'button_radius' => array(
			'section'     => 'buttons',
			'label'       => __( 'Button Radius', 'lithia-web-service-theme' ),
			'type'        => 'size',
			'description' => __( 'Corner radius used by theme buttons.', 'lithia-web-service-theme' ),
		),
		'button_font_size' => array(
			'section'     => 'buttons',
			'label'       => __( 'Button Font Size', 'lithia-web-service-theme' ),
			'type'        => 'size',
			'description' => __( 'Font size used by theme buttons.', 'lithia-web-service-theme' ),
		),
		'button_font_weight' => array(
			'section'     => 'buttons',
			'label'       => __( 'Button Font Weight', 'lithia-web-service-theme' ),
			'type'        => 'weight',
			'description' => __( 'Numeric weight for buttons, such as `600`.', 'lithia-web-service-theme' ),
			'step'        => '100',
		),
		'shadow_soft' => array(
			'section'     => 'effects',
			'label'       => __( 'Soft Shadow', 'lithia-web-service-theme' ),
			'type'        => 'shadow',
			'description' => __( 'A reusable soft shadow value such as `0 10px 30px rgba(0,0,0,0.06)`.', 'lithia-web-service-theme' ),
		),
		'shadow_panel' => array(
			'section'     => 'effects',
			'label'       => __( 'Panel Shadow', 'lithia-web-service-theme' ),
			'type'        => 'shadow',
			'description' => __( 'Used for shells, hero glass panels, and larger cards.', 'lithia-web-service-theme' ),
		),
		'header_blur_bg' => array(
			'section'     => 'effects',
			'label'       => __( 'Header Blur Background', 'lithia-web-service-theme' ),
			'type'        => 'css_color',
			'description' => __( 'Accepts hex or rgba values such as `rgba(247,244,238,0.72)`.', 'lithia-web-service-theme' ),
		),
	);
}

/**
 * Return the saved Site Styles merged with defaults.
 *
 * @return array
 */
function lithia_get_site_styles(): array {
	$saved = get_option( 'lithia_site_styles', array() );

	if ( ! is_array( $saved ) ) {
		$saved = array();
	}

	return wp_parse_args( $saved, lithia_get_site_style_defaults() );
}

/**
 * Bulk update Site Styles values.
 *
 * @param array $values Incoming style tokens.
 * @param bool  $merge_existing Whether to merge into the current saved values.
 * @return array
 */
function lithia_update_site_styles( array $values, bool $merge_existing = true ): array {
	$base_styles = $merge_existing ? lithia_get_site_styles() : lithia_get_site_style_defaults();
	$merged      = array_merge( $base_styles, $values );
	$sanitized   = lithia_sanitize_site_styles( $merged );

	update_option( 'lithia_site_styles', $sanitized );

	return $sanitized;
}

/**
 * Return a single Site Styles token.
 *
 * @param string $key     Style key.
 * @param mixed  $default Default fallback.
 * @return mixed
 */
function lithia_get_site_style( string $key, $default = '' ) {
	$styles = lithia_get_site_styles();

	return $styles[ $key ] ?? $default;
}

/**
 * Normalize a CSS-like string.
 *
 * @param mixed $value Raw value.
 * @return string
 */
function lithia_normalize_css_value( $value ): string {
	if ( is_array( $value ) || is_object( $value ) ) {
		return '';
	}

	$value = wp_strip_all_tags( (string) $value );
	$value = preg_replace( '/\s+/', ' ', trim( $value ) );

	return is_string( $value ) ? $value : '';
}

/**
 * Determine whether a CSS-like value contains unsafe tokens.
 *
 * @param string $value CSS-like value.
 * @return bool
 */
function lithia_css_value_is_unsafe( string $value ): bool {
	return 1 === preg_match( '/[{};<>\\\\]|(?:url|expression|javascript:|data:|@import)/i', $value );
}

/**
 * Validate function usage inside a CSS-like value.
 *
 * @param string $value             CSS-like value.
 * @param array  $allowed_functions Allowed function names.
 * @return bool
 */
function lithia_css_functions_are_allowed( string $value, array $allowed_functions ): bool {
	preg_match_all( '/([a-zA-Z_][a-zA-Z0-9_-]*)\s*\(/', $value, $matches );

	foreach ( $matches[1] as $function_name ) {
		if ( ! in_array( strtolower( $function_name ), $allowed_functions, true ) ) {
			return false;
		}
	}

	return true;
}

/**
 * Sanitize a CSS color value.
 *
 * @param mixed  $value   Raw value.
 * @param string $default Default fallback.
 * @return string
 */
function lithia_sanitize_css_color_value( $value, string $default ): string {
	$value = lithia_normalize_css_value( $value );

	if ( '' === $value ) {
		return $default;
	}

	$hex = sanitize_hex_color( $value );

	if ( $hex ) {
		return $hex;
	}

	if ( 'transparent' === strtolower( $value ) ) {
		return 'transparent';
	}

	if ( preg_match( '/^rgba?\(\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})(?:\s*,\s*(0|0?\.\d+|1(?:\.0+)?)\s*)?\)$/i', $value, $matches ) ) {
		$channels = array( (int) $matches[1], (int) $matches[2], (int) $matches[3] );

		foreach ( $channels as $channel ) {
			if ( $channel < 0 || $channel > 255 ) {
				return $default;
			}
		}

		return $value;
	}

	return $default;
}

/**
 * Sanitize a CSS size value.
 *
 * @param mixed  $value   Raw value.
 * @param string $default Default fallback.
 * @return string
 */
function lithia_sanitize_css_size_value( $value, string $default ): string {
	$value = lithia_normalize_css_value( $value );

	if ( '' === $value ) {
		return $default;
	}

	if ( lithia_css_value_is_unsafe( $value ) ) {
		return $default;
	}

	if ( ! preg_match( '/^[0-9a-zA-Z.,%()+\-*\/\s]+$/', $value ) ) {
		return $default;
	}

	if ( ! lithia_css_functions_are_allowed( $value, array( 'clamp', 'min', 'max', 'calc', 'var' ) ) ) {
		return $default;
	}

	return $value;
}

/**
 * Sanitize a numeric CSS token.
 *
 * @param mixed  $value   Raw value.
 * @param string $default Default fallback.
 * @return string
 */
function lithia_sanitize_css_number_value( $value, string $default ): string {
	$value = lithia_normalize_css_value( $value );

	if ( '' === $value ) {
		return $default;
	}

	if ( preg_match( '/^\d+(?:\.\d+)?$/', $value ) ) {
		return $value;
	}

	return $default;
}

/**
 * Sanitize a font weight value.
 *
 * @param mixed  $value   Raw value.
 * @param string $default Default fallback.
 * @return string
 */
function lithia_sanitize_css_weight_value( $value, string $default ): string {
	$value = lithia_normalize_css_value( $value );

	if ( '' === $value ) {
		return $default;
	}

	if ( preg_match( '/^(100|200|300|400|500|600|700|800|900)$/', $value ) ) {
		return $value;
	}

	if ( in_array( strtolower( $value ), array( 'normal', 'bold' ), true ) ) {
		return strtolower( $value );
	}

	return $default;
}

/**
 * Sanitize a font-family stack.
 *
 * @param mixed  $value   Raw value.
 * @param string $default Default fallback.
 * @return string
 */
function lithia_sanitize_font_family_value( $value, string $default ): string {
	$value = lithia_normalize_css_value( $value );

	if ( '' === $value ) {
		return $default;
	}

	if ( preg_match( '/[{};<>\\\\]/', $value ) ) {
		return $default;
	}

	if ( ! preg_match( '/^[a-zA-Z0-9,\-"\'\s]+$/', $value ) ) {
		return $default;
	}

	return $value;
}

/**
 * Sanitize a shadow token.
 *
 * @param mixed  $value   Raw value.
 * @param string $default Default fallback.
 * @return string
 */
function lithia_sanitize_css_shadow_value( $value, string $default ): string {
	$value = lithia_normalize_css_value( $value );

	if ( '' === $value ) {
		return $default;
	}

	if ( lithia_css_value_is_unsafe( $value ) ) {
		return $default;
	}

	if ( ! preg_match( '/^[0-9a-zA-Z#.,()%+\-\s]+$/', $value ) ) {
		return $default;
	}

	if ( ! lithia_css_functions_are_allowed( $value, array( 'rgb', 'rgba' ) ) ) {
		return $default;
	}

	return $value;
}

/**
 * Sanitize Site Styles settings.
 *
 * @param mixed $input Raw settings input.
 * @return array
 */
function lithia_sanitize_site_styles( $input ): array {
	$defaults  = lithia_get_site_style_defaults();
	$fields    = lithia_get_site_style_fields();
	$sanitized = array();

	if ( ! is_array( $input ) ) {
		$input = array();
	}

	foreach ( $fields as $key => $field ) {
		$default = $defaults[ $key ] ?? '';
		$value   = $input[ $key ] ?? $default;

		switch ( $field['type'] ) {
			case 'color':
			case 'css_color':
				$sanitized[ $key ] = lithia_sanitize_css_color_value( $value, $default );
				break;
			case 'size':
				$sanitized[ $key ] = lithia_sanitize_css_size_value( $value, $default );
				break;
			case 'number':
				$sanitized[ $key ] = lithia_sanitize_css_number_value( $value, $default );
				break;
			case 'weight':
				$sanitized[ $key ] = lithia_sanitize_css_weight_value( $value, $default );
				break;
			case 'font_family':
				$sanitized[ $key ] = lithia_sanitize_font_family_value( $value, $default );
				break;
			case 'shadow':
				$sanitized[ $key ] = lithia_sanitize_css_shadow_value( $value, $default );
				break;
			default:
				$sanitized[ $key ] = sanitize_text_field( (string) $value );
				break;
		}
	}

	return $sanitized;
}

/**
 * Register the Site Styles settings.
 */
function lithia_register_site_styles_setting(): void {
	register_setting(
		'lithia_site_styles_group',
		'lithia_site_styles',
		array(
			'type'              => 'array',
			'sanitize_callback' => 'lithia_sanitize_site_styles',
			'default'           => lithia_get_site_style_defaults(),
			'show_in_rest'      => false,
		)
	);
}
add_action( 'admin_init', 'lithia_register_site_styles_setting' );

/**
 * Migrate untouched legacy layout defaults to the tighter scale.
 */
function lithia_maybe_migrate_site_style_defaults(): void {
	$styles = get_option( 'lithia_site_styles', null );

	if ( ! is_array( $styles ) ) {
		return;
	}

	$legacy_defaults = lithia_get_legacy_site_style_defaults();
	$new_defaults    = lithia_get_site_style_defaults();
	$scale_keys      = array(
		'font_size_base',
		'font_size_small',
		'font_size_h1',
		'font_size_h2',
		'font_size_h3',
		'section_padding_y',
		'section_padding_x',
		'block_gap',
	);
	$has_legacy_scale = true;

	foreach ( $scale_keys as $key ) {
		if ( ( $styles[ $key ] ?? null ) !== $legacy_defaults[ $key ] ) {
			$has_legacy_scale = false;
			break;
		}
	}

	$has_legacy_widths = in_array( $styles['content_width'] ?? null, array( '720px', '100%' ), true )
		&& in_array( $styles['wide_width'] ?? null, array( '1200px', '100%' ), true );

	$updated = false;

	if ( $has_legacy_scale && $has_legacy_widths ) {
		foreach ( $scale_keys as $key ) {
			if ( isset( $styles[ $key ] ) && $styles[ $key ] === $legacy_defaults[ $key ] ) {
				$styles[ $key ] = $new_defaults[ $key ];
				$updated        = true;
			}
		}

		if ( in_array( $styles['content_width'] ?? null, array( '720px', '100%' ), true ) ) {
			$styles['content_width'] = $new_defaults['content_width'];
			$updated                 = true;
		}

		if ( in_array( $styles['wide_width'] ?? null, array( '1200px', '100%' ), true ) ) {
			$styles['wide_width'] = $new_defaults['wide_width'];
			$updated              = true;
		}
	}

	if ( '#6F6A63' === strtoupper( (string) ( $styles['muted_text_color'] ?? '' ) ) ) {
		$styles['muted_text_color'] = $new_defaults['muted_text_color'];
		$updated                    = true;
	}

	if ( $updated ) {
		update_option( 'lithia_site_styles', lithia_sanitize_site_styles( $styles ), false );
	}
}
add_action( 'after_setup_theme', 'lithia_maybe_migrate_site_style_defaults', 5 );

/**
 * Add the Site Styles page under Appearance.
 */
function lithia_register_site_styles_page(): void {
	add_theme_page(
		__( 'Site Styles', 'lithia-web-service-theme' ),
		__( 'Site Styles', 'lithia-web-service-theme' ),
		'edit_theme_options',
		'lithia-site-styles',
		'lithia_render_site_styles_page'
	);
}
add_action( 'admin_menu', 'lithia_register_site_styles_page' );

/**
 * Reset Site Styles to defaults when requested.
 */
function lithia_maybe_reset_site_styles(): void {
	if ( ! is_admin() || ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}

	if ( empty( $_GET['page'] ) || 'lithia-site-styles' !== $_GET['page'] ) {
		return;
	}

	if ( empty( $_GET['lithia-reset-site-styles'] ) ) {
		return;
	}

	check_admin_referer( 'lithia_reset_site_styles' );

	delete_option( 'lithia_site_styles' );

	wp_safe_redirect(
		add_query_arg(
			array(
				'page'                      => 'lithia-site-styles',
				'lithia-site-styles-reset'  => '1',
			),
			admin_url( 'themes.php' )
		)
	);
	exit;
}
add_action( 'admin_init', 'lithia_maybe_reset_site_styles', 30 );

/**
 * Enqueue Site Styles admin assets.
 *
 * @param string $hook_suffix Current admin page hook.
 */
function lithia_enqueue_site_styles_admin_assets( string $hook_suffix ): void {
	if ( 'appearance_page_lithia-site-styles' !== $hook_suffix ) {
		return;
	}

	$theme_version = wp_get_theme()->get( 'Version' );

	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_style(
		'lithia-site-styles-admin',
		get_theme_file_uri( 'assets/css/admin-site-styles.css' ),
		array( 'wp-color-picker' ),
		$theme_version
	);
	wp_enqueue_script(
		'lithia-site-styles-admin',
		get_theme_file_uri( 'assets/js/admin-site-styles.js' ),
		array( 'jquery', 'wp-color-picker' ),
		$theme_version,
		true
	);
}
add_action( 'admin_enqueue_scripts', 'lithia_enqueue_site_styles_admin_assets' );

/**
 * Render a Site Styles field.
 *
 * @param string $key   Field key.
 * @param array  $field Field configuration.
 * @param array  $value Current merged values.
 * @param array  $defaults Default values.
 */
function lithia_render_site_styles_field( string $key, array $field, array $value, array $defaults ): void {
	$current_value = $value[ $key ] ?? '';
	$default_value = $defaults[ $key ] ?? '';
	$input_name    = sprintf( 'lithia_site_styles[%s]', $key );
	$input_id      = sprintf( 'lithia-site-style-%s', $key );
	$type          = $field['type'] ?? 'text';
	$description   = $field['description'] ?? '';
	$step          = $field['step'] ?? '0.01';
	$classes       = array( 'regular-text' );

	if ( 'color' === $type ) {
		$classes[] = 'lw-color-field';
	}

	if ( in_array( $type, array( 'size', 'shadow', 'font_family', 'css_color' ), true ) ) {
		$classes[] = 'code';
	}

	if ( in_array( $type, array( 'number', 'weight' ), true ) ) :
		?>
		<input
			type="number"
			id="<?php echo esc_attr( $input_id ); ?>"
			name="<?php echo esc_attr( $input_name ); ?>"
			value="<?php echo esc_attr( $current_value ); ?>"
			class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>"
			step="<?php echo esc_attr( $step ); ?>"
		/>
		<?php
	else :
		?>
		<input
			type="text"
			id="<?php echo esc_attr( $input_id ); ?>"
			name="<?php echo esc_attr( $input_name ); ?>"
			value="<?php echo esc_attr( $current_value ); ?>"
			class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>"
			placeholder="<?php echo esc_attr( $default_value ); ?>"
		/>
		<?php
	endif;

	if ( $description ) :
		?>
		<span class="lw-site-styles-helper"><?php echo esc_html( $description ); ?></span>
		<?php
	endif;
	?>
	<span class="lw-site-styles-default">
		<?php
		printf(
			/* translators: %s: default style token value */
			esc_html__( 'Default: %s', 'lithia-web-service-theme' ),
			esc_html( $default_value )
		);
		?>
	</span>
	<?php
}

/**
 * Render the Site Styles settings page.
 */
function lithia_render_site_styles_page(): void {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}

	$sections   = lithia_get_site_style_sections();
	$fields     = lithia_get_site_style_fields();
	$styles     = lithia_get_site_styles();
	$defaults   = lithia_get_site_style_defaults();
	$reset_url  = wp_nonce_url(
		add_query_arg(
			array(
				'page'                     => 'lithia-site-styles',
				'lithia-reset-site-styles' => '1',
			),
			admin_url( 'themes.php' )
		),
		'lithia_reset_site_styles'
	);
	?>
	<div class="wrap lw-site-styles-page">
		<h1><?php esc_html_e( 'Site Styles', 'lithia-web-service-theme' ); ?></h1>
		<p class="description">
			<?php esc_html_e( 'Edit the theme design tokens here. These values drive the frontend CSS variables and the custom block styling system.', 'lithia-web-service-theme' ); ?>
		</p>

		<?php if ( ! empty( $_GET['lithia-site-styles-reset'] ) ) : ?>
			<div class="notice notice-success is-dismissible">
				<p><?php esc_html_e( 'Site Styles were reset to the theme defaults.', 'lithia-web-service-theme' ); ?></p>
			</div>
		<?php endif; ?>

		<?php settings_errors(); ?>

		<form action="options.php" method="post">
			<?php settings_fields( 'lithia_site_styles_group' ); ?>

			<div class="lw-site-styles-grid">
				<?php foreach ( $sections as $section_key => $section ) : ?>
					<section class="lw-site-styles-panel">
						<h2><?php echo esc_html( $section['title'] ); ?></h2>
						<?php if ( ! empty( $section['description'] ) ) : ?>
							<p class="description"><?php echo esc_html( $section['description'] ); ?></p>
						<?php endif; ?>

						<table class="form-table" role="presentation">
							<tbody>
								<?php foreach ( $fields as $field_key => $field ) : ?>
									<?php if ( $section_key !== $field['section'] ) : ?>
										<?php continue; ?>
									<?php endif; ?>
									<tr>
										<th scope="row">
											<label for="<?php echo esc_attr( 'lithia-site-style-' . $field_key ); ?>">
												<?php echo esc_html( $field['label'] ); ?>
											</label>
										</th>
										<td>
											<?php lithia_render_site_styles_field( $field_key, $field, $styles, $defaults ); ?>
										</td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</section>
				<?php endforeach; ?>
			</div>

			<div class="lw-site-styles-actions">
				<?php submit_button( __( 'Save Site Styles', 'lithia-web-service-theme' ), 'primary', 'submit', false ); ?>
				<a class="button button-secondary" href="<?php echo esc_url( $reset_url ); ?>">
					<?php esc_html_e( 'Reset To Defaults', 'lithia-web-service-theme' ); ?>
				</a>
			</div>
		</form>
	</div>
	<?php
}

/**
 * Return Site Styles CSS custom properties.
 *
 * @return array
 */
function lithia_get_site_style_css_variables(): array {
	$styles = lithia_get_site_styles();

	return array(
		'--lw-color-background'     => $styles['background_color'],
		'--lw-color-surface'        => $styles['surface_color'],
		'--lw-color-text'           => $styles['text_color'],
		'--lw-color-muted-text'     => $styles['muted_text_color'],
		'--lw-color-primary'        => $styles['primary_color'],
		'--lw-color-secondary'      => $styles['secondary_color'],
		'--lw-color-accent'         => $styles['accent_color'],
		'--lw-color-border'         => $styles['border_color'],
		'--lw-color-light-bg'       => $styles['light_bg_color'],
		'--lw-color-light-text'     => $styles['light_text_color'],
		'--lw-color-dark-bg'        => $styles['dark_bg_color'],
		'--lw-color-dark-text'      => $styles['dark_text_color'],
		'--lw-button-text'          => $styles['button_text_color'],
		'--lw-button-bg'            => $styles['button_bg_color'],
		'--lw-button-border'        => $styles['button_border_color'],
		'--lw-font-heading'         => $styles['font_heading'],
		'--lw-font-body'            => $styles['font_body'],
		'--lw-font-size-base'       => $styles['font_size_base'],
		'--lw-font-size-small'      => $styles['font_size_small'],
		'--lw-font-size-h1'         => $styles['font_size_h1'],
		'--lw-font-size-h2'         => $styles['font_size_h2'],
		'--lw-font-size-h3'         => $styles['font_size_h3'],
		'--lw-line-height-body'     => $styles['line_height_body'],
		'--lw-line-height-heading'  => $styles['line_height_heading'],
		'--lw-font-weight-heading'  => $styles['font_weight_heading'],
		'--lw-font-weight-body'     => $styles['font_weight_body'],
		'--lw-content-width'        => $styles['content_width'],
		'--lw-wide-width'           => $styles['wide_width'],
		'--lw-section-padding-y'    => $styles['section_padding_y'],
		'--lw-section-padding-x'    => $styles['section_padding_x'],
		'--lw-block-gap'            => $styles['block_gap'],
		'--lw-radius-sm'            => $styles['border_radius_small'],
		'--lw-radius-md'            => $styles['border_radius_medium'],
		'--lw-radius-lg'            => $styles['border_radius_large'],
		'--lw-button-padding-y'     => $styles['button_padding_y'],
		'--lw-button-padding-x'     => $styles['button_padding_x'],
		'--lw-button-radius'        => $styles['button_radius'],
		'--lw-button-font-size'     => $styles['button_font_size'],
		'--lw-button-font-weight'   => $styles['button_font_weight'],
		'--lw-shadow-soft'          => $styles['shadow_soft'],
		'--lw-shadow-panel'         => $styles['shadow_panel'],
		'--lw-header-blur-bg'       => $styles['header_blur_bg'],
		'--wp--style--global--content-size' => $styles['content_width'],
		'--wp--style--global--wide-size'    => $styles['wide_width'],
		'--wp--style--block-gap'            => $styles['block_gap'],
	);
}

/**
 * Build the inline Site Styles CSS.
 *
 * @return string
 */
function lithia_get_site_styles_inline_css(): string {
	$variables = lithia_get_site_style_css_variables();
	$lines     = array();

	foreach ( $variables as $name => $value ) {
		$lines[] = sprintf( '%s: %s;', $name, $value );
	}

	return ":root {\n\t" . implode( "\n\t", $lines ) . "\n}\n";
}

/**
 * Return editor-specific CSS aligned to the frontend text system.
 *
 * @return string
 */
function lithia_get_site_styles_editor_inline_css(): string {
	return lithia_get_site_styles_inline_css() . "
\n:where(body),
:where(body.editor-styles-wrapper),
:where(.editor-styles-wrapper),
:where(.editor-styles-wrapper .is-root-container) {
\tcolor: var(--lw-color-text);
\tfont-family: var(--lw-font-body);
\tfont-size: var(--lw-font-size-base);
\tfont-weight: var(--lw-font-weight-body);
\tline-height: var(--lw-line-height-body);
}

\n:where(.editor-styles-wrapper) :where(p, li, dd, figcaption, cite, .wp-block-post-excerpt__excerpt, .wp-block-paragraph, .wp-block-list, .wp-block-quote) {
\tcolor: var(--lw-color-muted-text);
}

\n:where(.editor-styles-wrapper) :where(h1, h2, h3, h4, h5, h6, .wp-block-post-title, .wp-block-query-title) {
\tcolor: var(--lw-color-text);
\tfont-family: var(--lw-font-heading);
\tfont-weight: var(--lw-font-weight-heading);
\tline-height: var(--lw-line-height-heading);
\tletter-spacing: -0.02em;
}

\n:where(.editor-styles-wrapper) :where(h1, .wp-block-post-title) {
\tfont-size: var(--lw-font-size-h1);
}

\n:where(.editor-styles-wrapper) :where(h2, .wp-block-query-title) {
\tfont-size: var(--lw-font-size-h2);
}

\n:where(.editor-styles-wrapper) :where(h3) {
\tfont-size: var(--lw-font-size-h3);
}

\n:where(.editor-styles-wrapper) :where(a) {
\tcolor: var(--lw-color-primary);
}

\n:where(.editor-styles-wrapper) .lw-section-light {
\tbackground: var(--lw-color-light-bg);
\tcolor: var(--lw-color-light-text);
}

\n:where(.editor-styles-wrapper) .lw-section-dark {
\tbackground: var(--lw-color-dark-bg);
\tcolor: var(--lw-color-dark-text);
}

\n:where(.editor-styles-wrapper) .lw-section-light :where(h1, h2, h3, h4, h5, h6, p, li, dt, dd, blockquote, figcaption),
:where(.editor-styles-wrapper) .lw-section-dark :where(h1, h2, h3, h4, h5, h6, p, li, dt, dd, blockquote, figcaption) {
\tcolor: inherit;
}
";
}

/**
 * Enqueue Site Styles CSS variables for frontend and editor block assets.
 */
function lithia_enqueue_site_styles_inline_css(): void {
	if ( ! wp_style_is( 'lithia-block-primitives', 'registered' ) ) {
		return;
	}

	wp_add_inline_style( 'lithia-block-primitives', lithia_get_site_styles_inline_css() );
}
add_action( 'enqueue_block_assets', 'lithia_enqueue_site_styles_inline_css', 20 );

/**
 * Inject site style variables into the block editor iframe styles array.
 *
 * @param array $settings Editor settings.
 * @return array
 */
function lithia_filter_block_editor_settings( array $settings ): array {
	if ( empty( $settings['styles'] ) || ! is_array( $settings['styles'] ) ) {
		$settings['styles'] = array();
	}

	$settings['styles'][] = array(
		'css' => lithia_get_site_styles_editor_inline_css(),
	);

	return $settings;
}
add_filter( 'block_editor_settings_all', 'lithia_filter_block_editor_settings' );
