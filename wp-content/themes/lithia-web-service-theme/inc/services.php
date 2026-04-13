<?php
/**
 * Service-page meta configuration and data helpers.
 *
 * @package LithiaWebServiceTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Determine whether the theme can sync managed JetEngine content.
 *
 * @return bool
 */
function lithia_can_sync_managed_content(): bool {
	return current_user_can( 'manage_options' ) || ( defined( 'WP_CLI' ) && WP_CLI );
}

/**
 * Build a JetEngine meta field config for the Services CPT.
 *
 * @param int    $id        Field ID.
 * @param string $title     Field label.
 * @param string $name      Meta key.
 * @param string $type      Field type.
 * @param array  $overrides Additional field settings.
 * @return array
 */
function lithia_build_service_meta_field( int $id, string $title, string $name, string $type = 'text', array $overrides = array() ): array {
	$field = array_merge(
		array(
			'id'             => $id,
			'title'          => $title,
			'name'           => $name,
			'object_type'    => 'field',
			'type'           => $type,
			'width'          => '100%',
			'isNested'       => false,
			'is_nested'      => false,
			'options'        => array(),
			'quick_editable' => false,
			'is_required'    => false,
			'default'        => '',
			'description'    => '',
			'placeholder'    => '',
			'args'           => array(),
			'conditions'     => array(),
			'repeater-fields' => array(),
		),
		$overrides
	);

	if ( 'media' === $type && empty( $field['value_format'] ) ) {
		$field['value_format'] = 'id';
	}

	if ( 'repeater' === $type && ! empty( $field['fields'] ) && is_array( $field['fields'] ) ) {
		$field['repeater-fields'] = array_values( $field['fields'] );
		unset( $field['fields'] );
	}

	return $field;
}

/**
 * Return the theme-managed JetEngine meta box config for Services.
 *
 * @return array
 */
function lithia_get_service_meta_box_config(): array {
	return array(
		'args' => array(
			'name'                 => 'Service Page Fields',
			'object_type'          => 'post',
			'show_edit_link'       => false,
			'hide_field_names'     => false,
			'delete_metadata'      => false,
			'allowed_post_type'    => array( 'services' ),
			'allowed_tax'          => array(),
			'allowed_user_screens' => '',
			'allowed_posts'        => array(),
			'excluded_posts'       => array(),
			'include_roles'        => array(),
			'exclude_roles'        => array(),
			'post_has_terms__tax'  => '',
			'post_has_terms__terms' => array(),
			'active_conditions'    => array(),
			'position'             => 'normal',
			'priority'             => 'high',
			'lithia_key'           => 'service_page_fields',
		),
		'meta_fields' => array(
			lithia_build_service_meta_field(
				1101,
				'Hero Eyebrow',
				'service_hero_eyebrow',
				'text',
				array(
					'width'       => '33%',
					'placeholder' => 'Service spotlight',
				)
			),
			lithia_build_service_meta_field(
				1102,
				'Hero Title',
				'service_hero_title',
				'text',
				array(
					'width'       => '67%',
					'placeholder' => 'Ongoing Website Service for Small Businesses',
				)
			),
			lithia_build_service_meta_field(
				1103,
				'Hero Paragraph',
				'service_hero_text',
				'textarea',
				array(
					'placeholder' => 'Short supporting paragraph for the service hero.',
				)
			),
			lithia_build_service_meta_field(
				1104,
				'Hero Background Image',
				'service_hero_image',
				'media',
				array(
					'description'  => 'Used as the full-width background image in the service hero.',
					'button_label' => 'Choose image',
				)
			),
			lithia_build_service_meta_field(
				1105,
				'Primary CTA Label',
				'service_primary_cta_label',
				'text',
				array(
					'width'       => '50%',
					'placeholder' => 'Book Appointment',
				)
			),
			lithia_build_service_meta_field(
				1106,
				'Primary CTA URL',
				'service_primary_cta_url',
				'text',
				array(
					'width'       => '50%',
					'placeholder' => '/book-appointment/',
				)
			),
			lithia_build_service_meta_field(
				1107,
				'Secondary CTA Label',
				'service_secondary_cta_label',
				'text',
				array(
					'width'       => '50%',
					'placeholder' => 'Contact Us',
				)
			),
			lithia_build_service_meta_field(
				1108,
				'Secondary CTA URL',
				'service_secondary_cta_url',
				'text',
				array(
					'width'       => '50%',
					'placeholder' => '/contact/',
				)
			),
			lithia_build_service_meta_field(
				1109,
				'Overview Heading',
				'service_overview_heading',
				'text',
				array(
					'placeholder' => 'What this service is for',
				)
			),
			lithia_build_service_meta_field(
				1110,
				'Overview Text',
				'service_overview_text',
				'textarea',
				array(
					'placeholder' => 'Add the core overview copy for this service page.',
				)
			),
			lithia_build_service_meta_field(
				1116,
				'Timeline',
				'service_timeline',
				'text',
				array(
					'width'       => '50%',
					'placeholder' => '2-6 weeks',
				)
			),
			lithia_build_service_meta_field(
				1117,
				'Delivery Mode',
				'service_delivery_mode',
				'text',
				array(
					'width'       => '50%',
					'placeholder' => 'Remote with async reviews',
				)
			),
			lithia_build_service_meta_field(
				1118,
				'Platform / Stack',
				'service_platform',
				'text',
				array(
					'width'       => '50%',
					'placeholder' => 'WordPress / WooCommerce',
				)
			),
			lithia_build_service_meta_field(
				1119,
				'Engagement Type',
				'service_engagement_type',
				'text',
				array(
					'width'       => '50%',
					'placeholder' => 'Fixed-scope project',
				)
			),
			lithia_build_service_meta_field(
				1111,
				'Highlights Heading',
				'service_highlights_heading',
				'text',
				array(
					'placeholder' => 'What’s included',
				)
			),
			lithia_build_service_meta_field(
				1112,
				'Highlights',
				'service_highlights',
				'repeater',
				array(
					'fields' => array(
						lithia_build_service_meta_field(
							11121,
							'Highlight Title',
							'item_title',
							'text',
							array(
								'placeholder' => 'Monthly updates',
							)
						),
						lithia_build_service_meta_field(
							11122,
							'Highlight Text',
							'item_text',
							'textarea',
							array(
								'placeholder' => 'Explain what is included in this part of the service.',
							)
						),
					),
				)
			),
			lithia_build_service_meta_field(
				1113,
				'Process Heading',
				'service_process_heading',
				'text',
				array(
					'placeholder' => 'How the process works',
				)
			),
			lithia_build_service_meta_field(
				1114,
				'Process Steps',
				'service_process_steps',
				'repeater',
				array(
					'fields' => array(
						lithia_build_service_meta_field(
							11141,
							'Step Title',
							'step_title',
							'text',
							array(
								'placeholder' => 'Audit and intake',
							)
						),
						lithia_build_service_meta_field(
							11142,
							'Step Text',
							'step_text',
							'textarea',
							array(
								'placeholder' => 'Describe this step in the service process.',
							)
						),
					),
				)
			),
			lithia_build_service_meta_field(
				1115,
				'Booking Note',
				'service_booking_note',
				'textarea',
				array(
					'placeholder' => 'Reusable note near the closing call to action.',
				)
			),
		),
	);
}

/**
 * Create the Services meta box if the theme-managed version is missing.
 *
 * @param array $config Meta box configuration.
 * @return void
 */
function lithia_ensure_service_meta_box( array $config ): void {
	if ( ! function_exists( 'jet_engine' ) || ! lithia_can_sync_managed_content() ) {
		return;
	}

	$jet_engine = jet_engine();

	if ( empty( $jet_engine->meta_boxes ) || empty( $jet_engine->meta_boxes->data ) ) {
		return;
	}

	$target_key   = $config['args']['lithia_key'] ?? '';
	$target_name  = $config['args']['name'] ?? '';
	$target_types = $config['args']['allowed_post_type'] ?? array();
	$meta_boxes   = $jet_engine->meta_boxes->data->get_raw();

	foreach ( $meta_boxes as $meta_box ) {
		$args = $meta_box['args'] ?? array();

		if ( $target_key && ! empty( $args['lithia_key'] ) && $target_key === $args['lithia_key'] ) {
			$config['id'] = $meta_box['id'] ?? '';

			if ( wp_json_encode( $meta_box ) !== wp_json_encode( $config ) ) {
				$jet_engine->meta_boxes->data->update_item_in_db( $config );
				$jet_engine->meta_boxes->data->reset_raw_cache();
			}

			return;
		}

		if ( $target_name && ! empty( $args['name'] ) && $target_name === $args['name'] ) {
			$existing_types = $args['allowed_post_type'] ?? array();

			if ( array_values( $target_types ) === array_values( $existing_types ) ) {
				$config['id'] = $meta_box['id'] ?? '';

				if ( wp_json_encode( $meta_box ) !== wp_json_encode( $config ) ) {
					$jet_engine->meta_boxes->data->update_item_in_db( $config );
					$jet_engine->meta_boxes->data->reset_raw_cache();
				}

				return;
			}
		}
	}

	$jet_engine->meta_boxes->data->update_item_in_db( $config );
	$jet_engine->meta_boxes->data->reset_raw_cache();
}

/**
 * Seed the Services meta box in wp-admin when JetEngine is active.
 *
 * @return void
 */
function lithia_maybe_seed_service_meta_box(): void {
	if ( ! is_admin() ) {
		return;
	}

	lithia_ensure_service_meta_box( lithia_get_service_meta_box_config() );
}
add_action( 'admin_init', 'lithia_maybe_seed_service_meta_box', 21 );

/**
 * Return the service homepage spotlight meta keys.
 *
 * @return array
 */
function lithia_get_service_homepage_spotlight_meta_keys(): array {
	return array(
		'enabled' => 'service_homepage_spotlight_enabled',
		'order'   => 'service_homepage_spotlight_order',
	);
}

/**
 * Determine whether a service is enabled for the homepage spotlight loop.
 *
 * @param int $post_id Service post ID.
 * @return bool
 */
function lithia_is_service_homepage_spotlight_enabled( int $post_id ): bool {
	$keys = lithia_get_service_homepage_spotlight_meta_keys();

	return 'yes' === lithia_seed_normalize_flag( get_post_meta( $post_id, $keys['enabled'], true ) );
}

/**
 * Return the homepage spotlight sort order for a service.
 *
 * @param int $post_id Service post ID.
 * @return int
 */
function lithia_get_service_homepage_spotlight_order( int $post_id ): int {
	$keys = lithia_get_service_homepage_spotlight_meta_keys();

	return max( 0, absint( get_post_meta( $post_id, $keys['order'], true ) ) );
}

/**
 * Render the homepage spotlight meta box.
 *
 * @param WP_Post $post Current post object.
 * @return void
 */
function lithia_render_service_homepage_spotlight_meta_box( WP_Post $post ): void {
	$keys    = lithia_get_service_homepage_spotlight_meta_keys();
	$enabled = lithia_is_service_homepage_spotlight_enabled( (int) $post->ID );
	$order   = lithia_get_service_homepage_spotlight_order( (int) $post->ID );

	wp_nonce_field( 'lithia_service_homepage_spotlight', 'lithia_service_homepage_spotlight_nonce' );
	?>
	<p><?php esc_html_e( 'Use the excerpt as slide copy and the primary CTA as the main action.', 'lithia-web-service-theme' ); ?></p>
	<p>
		<label>
			<input type="checkbox" name="<?php echo esc_attr( $keys['enabled'] ); ?>" value="1" <?php checked( $enabled ); ?> />
			<?php esc_html_e( 'Show in homepage spotlight loop', 'lithia-web-service-theme' ); ?>
		</label>
	</p>
	<p>
		<label for="<?php echo esc_attr( $keys['order'] ); ?>">
			<?php esc_html_e( 'Slide Order', 'lithia-web-service-theme' ); ?>
		</label>
		<input
			type="number"
			min="0"
			step="1"
			class="small-text"
			id="<?php echo esc_attr( $keys['order'] ); ?>"
			name="<?php echo esc_attr( $keys['order'] ); ?>"
			value="<?php echo esc_attr( (string) $order ); ?>"
		/>
	</p>
	<?php
}

/**
 * Register the homepage spotlight meta box on Services posts.
 *
 * @return void
 */
function lithia_register_service_homepage_spotlight_meta_box(): void {
	add_meta_box(
		'lithia-service-homepage-spotlight',
		__( 'Homepage Spotlight', 'lithia-web-service-theme' ),
		'lithia_render_service_homepage_spotlight_meta_box',
		'services',
		'side',
		'default'
	);
}
add_action( 'add_meta_boxes_services', 'lithia_register_service_homepage_spotlight_meta_box' );

/**
 * Save the homepage spotlight settings for Services posts.
 *
 * @param int $post_id Post ID.
 * @return void
 */
function lithia_save_service_homepage_spotlight_meta_box( int $post_id ): void {
	if ( empty( $_POST['lithia_service_homepage_spotlight_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['lithia_service_homepage_spotlight_nonce'] ) ), 'lithia_service_homepage_spotlight' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	if ( 'services' !== get_post_type( $post_id ) ) {
		return;
	}

	$keys    = lithia_get_service_homepage_spotlight_meta_keys();
	$enabled = ! empty( $_POST[ $keys['enabled'] ] ) ? 'yes' : 'no';
	$order   = isset( $_POST[ $keys['order'] ] ) ? max( 0, absint( wp_unslash( $_POST[ $keys['order'] ] ) ) ) : 0;

	update_post_meta( $post_id, $keys['enabled'], $enabled );
	update_post_meta( $post_id, $keys['order'], $order );
}
add_action( 'save_post_services', 'lithia_save_service_homepage_spotlight_meta_box' );

/**
 * Return the managed single-service booking form title.
 *
 * @return string
 */
function lithia_get_single_service_booking_form_title(): string {
	return 'Single Service Booking Form';
}

/**
 * Return the block markup for the managed single-service booking form.
 *
 * @return string
 */
function lithia_get_single_service_booking_form_content(): string {
	return implode(
		"\n\n",
		array(
			'<!-- wp:jet-forms/hidden-field {"name":"service_id","field_value":"post_id"} /-->',
			'<!-- wp:jet-forms/appointment-provider {"appointment_service_field":"form_field","appointment_form_field":"service_id","label":"Select provider","name":"provider_id","required":true} /-->',
			'<!-- wp:jet-forms/appointment-date {"appointment_service_field":"form_field","appointment_form_field":"service_id","appointment_provider_field":"form_field","appointment_provider_form_field":"provider_id","label":"Select appointment time","name":"appointment_date","required":true} /-->',
			'<!-- wp:jet-forms/text-field {"field_type":"email","label":"Email address","name":"user_email","required":true} /-->',
			'<!-- wp:jet-forms/submit-field {"label":"Book Appointment"} /-->',
		)
	) . "\n";
}

/**
 * Return the meta payload for the managed single-service booking form.
 *
 * @return array
 */
function lithia_get_single_service_booking_form_meta(): array {
	return array(
		'_jf_actions'               => '[{"type":"insert_appointment","id":8591,"settings":{"insert_appointment":{"appointment_service_field":"service_id","appointment_provider_field":"provider_id","appointment_date_field":"appointment_date","appointment_email_field":"user_email"}}}]',
		'_lithia_theme_managed_key' => 'single_service_booking_form',
	);
}

/**
 * Return Services sheet sync settings defaults.
 *
 * @return array
 */
function lithia_get_services_sheet_sync_defaults(): array {
	return array(
		'feed_url' => '',
		'api_key'  => '',
	);
}

/**
 * Return Services sheet sync settings merged with defaults.
 *
 * @return array
 */
function lithia_get_services_sheet_sync_settings(): array {
	$saved = get_option( 'lithia_services_sheet_sync', array() );

	return wp_parse_args( is_array( $saved ) ? $saved : array(), lithia_get_services_sheet_sync_defaults() );
}

/**
 * Sanitize Services sheet sync settings.
 *
 * @param mixed $input Raw settings input.
 * @return array
 */
function lithia_sanitize_services_sheet_sync_settings( $input ): array {
	$defaults  = lithia_get_services_sheet_sync_defaults();
	$sanitized = $defaults;

	if ( ! is_array( $input ) ) {
		return $sanitized;
	}

	$feed_url = isset( $input['feed_url'] ) ? esc_url_raw( trim( (string) $input['feed_url'] ) ) : '';
	$api_key  = isset( $input['api_key'] ) ? sanitize_text_field( trim( (string) $input['api_key'] ) ) : '';

	$sanitized['feed_url'] = $feed_url;
	$sanitized['api_key']  = $api_key;

	return $sanitized;
}

/**
 * Register Services sheet sync settings.
 *
 * @return void
 */
function lithia_register_services_sheet_sync_setting(): void {
	register_setting(
		'lithia_services_sheet_sync_group',
		'lithia_services_sheet_sync',
		array(
			'type'              => 'array',
			'sanitize_callback' => 'lithia_sanitize_services_sheet_sync_settings',
			'default'           => lithia_get_services_sheet_sync_defaults(),
		)
	);
}
add_action( 'admin_init', 'lithia_register_services_sheet_sync_setting' );

/**
 * Register the Services Sync admin page.
 *
 * @return void
 */
function lithia_register_services_sheet_sync_page(): void {
	add_theme_page(
		__( 'Services Sync', 'lithia-web-service-theme' ),
		__( 'Services Sync', 'lithia-web-service-theme' ),
		'edit_theme_options',
		'lithia-services-sheet-sync',
		'lithia_render_services_sheet_sync_page'
	);
}
add_action( 'admin_menu', 'lithia_register_services_sheet_sync_page' );

/**
 * Build the sheet feed URL with auth parameters.
 *
 * @return string
 */
function lithia_get_services_sheet_sync_feed_request_url(): string {
	$settings = lithia_get_services_sheet_sync_settings();
	$feed_url = trim( (string) ( $settings['feed_url'] ?? '' ) );
	$api_key  = trim( (string) ( $settings['api_key'] ?? '' ) );

	if ( '' === $feed_url ) {
		return '';
	}

	if ( '' === $api_key ) {
		return $feed_url;
	}

	return (string) add_query_arg( 'key', $api_key, $feed_url );
}

/**
 * Normalize a scalar sheet value.
 *
 * @param mixed $value Raw value.
 * @return string
 */
function lithia_services_sheet_value( $value ): string {
	if ( is_array( $value ) || is_object( $value ) ) {
		return '';
	}

	return trim( wp_unslash( (string) $value ) );
}

/**
 * Build JetEngine repeater rows from flat sheet columns.
 *
 * @param array  $row        Raw row data.
 * @param string $prefix     Field prefix.
 * @param string $key_a      Repeater key A.
 * @param string $key_b      Repeater key B.
 * @param int    $max_items  Max row count.
 * @return array
 */
function lithia_build_services_sheet_repeater_rows( array $row, string $prefix, string $key_a, string $key_b, int $max_items = 6 ): array {
	$items = array();

	for ( $index = 1; $index <= $max_items; $index++ ) {
		$title = lithia_services_sheet_value( $row[ "{$prefix}_{$index}_title" ] ?? '' );
		$text  = lithia_services_sheet_value( $row[ "{$prefix}_{$index}_text" ] ?? '' );

		if ( '' === $title && '' === $text ) {
			continue;
		}

		$items[ 'item-' . count( $items ) ] = array(
			$key_a => $title,
			$key_b => $text,
		);
	}

	return $items;
}

/**
 * Parse Rank Math robots tokens from a sheet value.
 *
 * @param string $value Raw robots string.
 * @return array
 */
function lithia_parse_services_sheet_robots( string $value ): array {
	$value = lithia_services_sheet_value( $value );

	if ( '' === $value ) {
		$value = 'index,follow';
	}

	$tokens = preg_split( '/[\s,\|]+/', $value );
	$tokens = array_filter( array_map( 'sanitize_key', (array) $tokens ) );

	return array_values( array_unique( $tokens ) );
}

/**
 * Normalize one Google Sheets row to the Services sync schema.
 *
 * @param array $row Raw row payload.
 * @return array
 */
function lithia_normalize_services_sheet_row( array $row ): array {
	$slug       = lithia_services_sheet_value( $row['post_name'] ?? '' );
	$post_title = lithia_services_sheet_value( $row['post_title'] ?? '' );

	if ( '' === $slug && '' !== $post_title ) {
		$slug = sanitize_title( $post_title );
	}

	$slug = sanitize_title( $slug );

	return array(
		'post_title'                 => $post_title,
		'post_name'                  => $slug,
		'post_status'                => lithia_services_sheet_value( $row['post_status'] ?? 'publish' ),
		'post_excerpt'               => lithia_services_sheet_value( $row['post_excerpt'] ?? '' ),
		'post_content'               => (string) ( $row['post_content'] ?? '' ),
		'featured_image_url'         => lithia_services_sheet_value( $row['featured_image_url'] ?? '' ),
		'service_hero_eyebrow'       => lithia_services_sheet_value( $row['service_hero_eyebrow'] ?? '' ),
		'service_hero_title'         => lithia_services_sheet_value( $row['service_hero_title'] ?? '' ),
		'service_hero_text'          => lithia_services_sheet_value( $row['service_hero_text'] ?? '' ),
		'service_hero_image_url'     => lithia_services_sheet_value( $row['service_hero_image_url'] ?? '' ),
		'service_primary_cta_label'  => lithia_services_sheet_value( $row['service_primary_cta_label'] ?? '' ),
		'service_primary_cta_url'    => lithia_services_sheet_value( $row['service_primary_cta_url'] ?? '' ),
		'service_secondary_cta_label'=> lithia_services_sheet_value( $row['service_secondary_cta_label'] ?? '' ),
		'service_secondary_cta_url'  => lithia_services_sheet_value( $row['service_secondary_cta_url'] ?? '' ),
		'service_homepage_spotlight_enabled' => lithia_services_sheet_value( $row['service_homepage_spotlight_enabled'] ?? '' ),
		'service_homepage_spotlight_order'   => lithia_services_sheet_value( $row['service_homepage_spotlight_order'] ?? '' ),
		'service_overview_heading'   => lithia_services_sheet_value( $row['service_overview_heading'] ?? '' ),
		'service_overview_text'      => lithia_services_sheet_value( $row['service_overview_text'] ?? '' ),
		'service_highlights_heading' => lithia_services_sheet_value( $row['service_highlights_heading'] ?? '' ),
		'service_highlights'         => lithia_build_services_sheet_repeater_rows( $row, 'service_highlight', 'item_title', 'item_text' ),
		'service_process_heading'    => lithia_services_sheet_value( $row['service_process_heading'] ?? '' ),
		'service_process_steps'      => lithia_build_services_sheet_repeater_rows( $row, 'service_process', 'step_title', 'step_text' ),
		'service_booking_note'       => lithia_services_sheet_value( $row['service_booking_note'] ?? '' ),
		'rank_math_title'            => lithia_services_sheet_value( $row['rank_math_title'] ?? '' ),
		'rank_math_description'      => lithia_services_sheet_value( $row['rank_math_description'] ?? '' ),
		'rank_math_focus_keyword'    => lithia_services_sheet_value( $row['rank_math_focus_keyword'] ?? '' ),
		'rank_math_facebook_title'   => lithia_services_sheet_value( $row['rank_math_facebook_title'] ?? '' ),
		'rank_math_facebook_description' => lithia_services_sheet_value( $row['rank_math_facebook_description'] ?? '' ),
		'rank_math_twitter_title'    => lithia_services_sheet_value( $row['rank_math_twitter_title'] ?? '' ),
		'rank_math_twitter_description' => lithia_services_sheet_value( $row['rank_math_twitter_description'] ?? '' ),
		'rank_math_robots'           => lithia_parse_services_sheet_robots( (string) ( $row['rank_math_robots'] ?? '' ) ),
		'seo_image_url'              => lithia_services_sheet_value( $row['seo_image_url'] ?? '' ),
	);
}

/**
 * Fetch the remote sheet payload.
 *
 * @return array
 */
function lithia_fetch_services_sheet_rows(): array {
	$request_url = lithia_get_services_sheet_sync_feed_request_url();

	if ( '' === $request_url ) {
		return array(
			'ok'    => false,
			'error' => __( 'Set the sheet feed URL before running a sync.', 'lithia-web-service-theme' ),
			'rows'  => array(),
		);
	}

	$response = wp_remote_get(
		$request_url,
		array(
			'timeout' => 20,
			'headers' => array(
				'Accept' => 'application/json',
			),
		)
	);

	if ( is_wp_error( $response ) ) {
		return array(
			'ok'    => false,
			'error' => $response->get_error_message(),
			'rows'  => array(),
		);
	}

	$status = (int) wp_remote_retrieve_response_code( $response );
	$body   = wp_remote_retrieve_body( $response );
	$data   = json_decode( $body, true );

	if ( $status < 200 || $status >= 300 ) {
		return array(
			'ok'    => false,
			'error' => sprintf(
				/* translators: %d: HTTP status code */
				__( 'The sheet feed returned HTTP %d.', 'lithia-web-service-theme' ),
				$status
			),
			'rows'  => array(),
		);
	}

	if ( ! is_array( $data ) ) {
		return array(
			'ok'    => false,
			'error' => __( 'The sheet feed did not return valid JSON.', 'lithia-web-service-theme' ),
			'rows'  => array(),
		);
	}

	if ( array_key_exists( 'ok', $data ) && empty( $data['ok'] ) ) {
		return array(
			'ok'    => false,
			'error' => lithia_services_sheet_value( $data['error'] ?? __( 'The sheet feed reported an error.', 'lithia-web-service-theme' ) ),
			'rows'  => array(),
		);
	}

	$rows = $data['data'] ?? $data;

	if ( ! is_array( $rows ) ) {
		return array(
			'ok'    => false,
			'error' => __( 'The sheet feed payload did not include a data array.', 'lithia-web-service-theme' ),
			'rows'  => array(),
		);
	}

	return array(
		'ok'    => true,
		'error' => '',
		'rows'  => $rows,
	);
}

/**
 * Upsert one service row from the Google Sheets sync feed.
 *
 * @param array $service Service payload.
 * @return array
 */
function lithia_upsert_service_from_sheet_row( array $service ): array {
	$slug       = $service['post_name'] ?? '';
	$post_title = $service['post_title'] ?? '';

	if ( '' === $slug || '' === $post_title ) {
		return array(
			'ok'      => false,
			'post_id' => 0,
			'action'  => '',
			'message' => __( 'A row is missing post_name or post_title.', 'lithia-web-service-theme' ),
		);
	}

	$payload = array(
		'project' => array(
			'schema_version' => lithia_get_project_import_schema_version(),
			'template_key'   => lithia_get_project_import_default_template_key(),
			'review_state'   => 'approved',
			'site_key'       => sanitize_title( get_option( 'blogname', 'lithia-project' ) ),
			'industry'       => 'service-business',
		),
		'offers' => array(
			array(
				'record_key'               => 'service_' . sanitize_key( $slug ),
				'title'                    => $post_title,
				'slug'                     => $slug,
				'status'                   => $service['post_status'],
				'excerpt'                  => $service['post_excerpt'],
				'content'                  => $service['post_content'],
				'featured_image_url'       => $service['featured_image_url'],
				'service_hero_eyebrow'     => $service['service_hero_eyebrow'],
				'service_hero_title'       => $service['service_hero_title'],
				'service_hero_text'        => $service['service_hero_text'],
				'service_hero_image_url'   => $service['service_hero_image_url'],
				'service_primary_cta_label' => $service['service_primary_cta_label'],
				'service_primary_cta_url'  => $service['service_primary_cta_url'],
				'service_secondary_cta_label' => $service['service_secondary_cta_label'],
				'service_secondary_cta_url' => $service['service_secondary_cta_url'],
				'service_homepage_spotlight_enabled' => $service['service_homepage_spotlight_enabled'],
				'service_homepage_spotlight_order' => $service['service_homepage_spotlight_order'],
				'service_overview_heading' => $service['service_overview_heading'],
				'service_overview_text'    => $service['service_overview_text'],
				'service_highlights_heading' => $service['service_highlights_heading'],
				'service_highlights'       => $service['service_highlights'],
				'service_process_heading'  => $service['service_process_heading'],
				'service_process_steps'    => $service['service_process_steps'],
				'service_booking_note'     => $service['service_booking_note'],
				'seo_image_url'            => $service['seo_image_url'],
				'seo'                      => array(
					'title'                => $service['rank_math_title'],
					'description'          => $service['rank_math_description'],
					'focus_keyword'        => $service['rank_math_focus_keyword'],
					'facebook_title'       => $service['rank_math_facebook_title'],
					'facebook_description' => $service['rank_math_facebook_description'],
					'twitter_title'        => $service['rank_math_twitter_title'],
					'twitter_description'  => $service['rank_math_twitter_description'],
					'robots'               => $service['rank_math_robots'],
				),
			),
		),
	);

	$report = lithia_import_project_payload(
		$payload,
		array(
			'source' => 'services_sheet',
		)
	);

	if ( empty( $report['success'] ) ) {
		return array(
			'ok'      => false,
			'post_id' => 0,
			'action'  => '',
			'message' => $report['error'] ?? __( 'The importer did not return a success state.', 'lithia-web-service-theme' ),
		);
	}

	$result = $report['offers'][0] ?? array();

	if ( empty( $result ) || 'failed' === ( $result['status'] ?? '' ) ) {
		return array(
			'ok'      => false,
			'post_id' => 0,
			'action'  => '',
			'message' => $result['message'] ?? __( 'The importer did not return an offer result.', 'lithia-web-service-theme' ),
		);
	}

	return array(
		'ok'      => true,
		'post_id' => absint( $result['post_id'] ?? 0 ),
		'action'  => (string) ( $result['status'] ?? 'noop' ),
		'message' => '',
	);
}

/**
 * Sync Services CPT entries from the configured Google Sheets feed.
 *
 * @return array
 */
function lithia_sync_services_from_sheet(): array {
	$fetched = lithia_fetch_services_sheet_rows();

	if ( empty( $fetched['ok'] ) ) {
		return array(
			'ok'      => false,
			'message' => $fetched['error'],
			'created' => 0,
			'updated' => 0,
			'noop'    => 0,
			'locked'  => 0,
			'failed'  => 0,
			'rows'    => 0,
			'errors'  => array(),
		);
	}

	$summary = array(
		'ok'      => true,
		'message' => '',
		'created' => 0,
		'updated' => 0,
		'noop'    => 0,
		'locked'  => 0,
		'failed'  => 0,
		'rows'    => 0,
		'errors'  => array(),
	);

	foreach ( $fetched['rows'] as $index => $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}

		$service = lithia_normalize_services_sheet_row( $row );

		if ( '' === $service['post_name'] && '' === $service['post_title'] ) {
			continue;
		}

		$summary['rows']++;
		$result = lithia_upsert_service_from_sheet_row( $service );

		if ( empty( $result['ok'] ) ) {
			$summary['failed']++;
			$summary['errors'][] = sprintf(
				/* translators: 1: row number, 2: error message */
				__( 'Row %1$d: %2$s', 'lithia-web-service-theme' ),
				$index + 2,
				$result['message']
			);
			continue;
		}

		if ( 'created' === $result['action'] ) {
			$summary['created']++;
		} elseif ( 'updated' === $result['action'] ) {
			$summary['updated']++;
		} elseif ( 'locked' === $result['action'] ) {
			$summary['locked']++;
		} else {
			$summary['noop']++;
		}
	}

	$summary['message'] = sprintf(
		/* translators: 1: created count, 2: updated count, 3: failed count */
		__( 'Sync complete. Created %1$d, updated %2$d, failed %3$d.', 'lithia-web-service-theme' ),
		$summary['created'],
		$summary['updated'],
		$summary['failed']
	);

	return $summary;
}

/**
 * Handle the admin post request for syncing services.
 *
 * @return void
 */
function lithia_handle_services_sheet_sync_post(): void {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to run this sync.', 'lithia-web-service-theme' ) );
	}

	check_admin_referer( 'lithia_services_sheet_sync' );

	$summary = lithia_sync_services_from_sheet();
	update_option(
		'lithia_services_sheet_sync_last_result',
		array(
			'ran_at'  => current_time( 'mysql' ),
			'summary' => $summary,
		),
		false
	);

	$args = array(
		'page' => 'lithia-services-sheet-sync',
		'sync' => $summary['ok'] ? 'success' : 'error',
	);

	wp_safe_redirect( add_query_arg( $args, admin_url( 'themes.php' ) ) );
	exit;
}
add_action( 'admin_post_lithia_sync_services_sheet', 'lithia_handle_services_sheet_sync_post' );

/**
 * Render the Services sheet sync admin page.
 *
 * @return void
 */
function lithia_render_services_sheet_sync_page(): void {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}

	$settings    = lithia_get_services_sheet_sync_settings();
	$last_result = get_option( 'lithia_services_sheet_sync_last_result', array() );
	$summary     = is_array( $last_result['summary'] ?? null ) ? $last_result['summary'] : array();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Services Sync', 'lithia-web-service-theme' ); ?></h1>
		<p class="description"><?php esc_html_e( 'Connect a Google Sheets JSON feed and manually sync rows into the Services post type.', 'lithia-web-service-theme' ); ?></p>

		<?php if ( ! empty( $_GET['sync'] ) && 'success' === $_GET['sync'] && ! empty( $summary['message'] ) ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php echo esc_html( $summary['message'] ); ?></p></div>
		<?php elseif ( ! empty( $_GET['sync'] ) && 'error' === $_GET['sync'] && ! empty( $summary['message'] ) ) : ?>
			<div class="notice notice-error"><p><?php echo esc_html( $summary['message'] ); ?></p></div>
		<?php endif; ?>

		<?php settings_errors(); ?>

		<form action="options.php" method="post">
			<?php settings_fields( 'lithia_services_sheet_sync_group' ); ?>
			<table class="form-table" role="presentation">
				<tbody>
					<tr>
						<th scope="row"><label for="lithia-services-sheet-feed-url"><?php esc_html_e( 'Feed URL', 'lithia-web-service-theme' ); ?></label></th>
						<td>
							<input
								type="url"
								class="regular-text code"
								id="lithia-services-sheet-feed-url"
								name="lithia_services_sheet_sync[feed_url]"
								value="<?php echo esc_attr( $settings['feed_url'] ?? '' ); ?>"
								placeholder="https://script.google.com/macros/s/your-id/exec"
							/>
							<p class="description"><?php esc_html_e( 'Use the deployed Apps Script web app URL for your doGet JSON feed.', 'lithia-web-service-theme' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="lithia-services-sheet-api-key"><?php esc_html_e( 'API Key', 'lithia-web-service-theme' ); ?></label></th>
						<td>
							<input
								type="text"
								class="regular-text code"
								id="lithia-services-sheet-api-key"
								name="lithia_services_sheet_sync[api_key]"
								value="<?php echo esc_attr( $settings['api_key'] ?? '' ); ?>"
							/>
							<p class="description"><?php esc_html_e( 'Optional. If your Apps Script expects ?key=..., enter the shared secret here.', 'lithia-web-service-theme' ); ?></p>
						</td>
					</tr>
				</tbody>
			</table>
			<?php submit_button( __( 'Save Sync Settings', 'lithia-web-service-theme' ) ); ?>
		</form>

		<hr />

		<h2><?php esc_html_e( 'Run Sync', 'lithia-web-service-theme' ); ?></h2>
		<p><?php esc_html_e( 'This will fetch the current sheet feed and upsert Services posts by the post_name column.', 'lithia-web-service-theme' ); ?></p>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'lithia_services_sheet_sync' ); ?>
			<input type="hidden" name="action" value="lithia_sync_services_sheet" />
			<?php submit_button( __( 'Sync Services from Sheet', 'lithia-web-service-theme' ), 'primary', 'submit', false ); ?>
		</form>

		<?php if ( ! empty( $last_result['ran_at'] ) ) : ?>
			<hr />
			<h2><?php esc_html_e( 'Last Sync', 'lithia-web-service-theme' ); ?></h2>
			<p>
				<?php
				printf(
					/* translators: %s: timestamp */
					esc_html__( 'Last run: %s', 'lithia-web-service-theme' ),
					esc_html( (string) $last_result['ran_at'] )
				);
				?>
			</p>

			<?php if ( ! empty( $summary['message'] ) ) : ?>
				<p><?php echo esc_html( $summary['message'] ); ?></p>
			<?php endif; ?>

			<ul style="list-style: disc; margin-left: 1.2rem;">
				<li><?php echo esc_html( sprintf( __( 'Rows processed: %d', 'lithia-web-service-theme' ), (int) ( $summary['rows'] ?? 0 ) ) ); ?></li>
				<li><?php echo esc_html( sprintf( __( 'Created: %d', 'lithia-web-service-theme' ), (int) ( $summary['created'] ?? 0 ) ) ); ?></li>
				<li><?php echo esc_html( sprintf( __( 'Updated: %d', 'lithia-web-service-theme' ), (int) ( $summary['updated'] ?? 0 ) ) ); ?></li>
				<li><?php echo esc_html( sprintf( __( 'Failed: %d', 'lithia-web-service-theme' ), (int) ( $summary['failed'] ?? 0 ) ) ); ?></li>
			</ul>

			<?php if ( ! empty( $summary['errors'] ) && is_array( $summary['errors'] ) ) : ?>
				<h3><?php esc_html_e( 'Errors', 'lithia-web-service-theme' ); ?></h3>
				<ul style="list-style: disc; margin-left: 1.2rem;">
					<?php foreach ( $summary['errors'] as $error ) : ?>
						<li><?php echo esc_html( (string) $error ); ?></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Get the existing managed single-service booking form ID.
 *
 * @return int
 */
function lithia_get_single_service_booking_form_id(): int {
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
			'meta_value'     => 'single_service_booking_form',
			'fields'         => 'ids',
			'orderby'        => 'ID',
			'order'          => 'ASC',
		)
	);

	if ( ! empty( $managed_forms[0] ) ) {
		$cached_id = (int) $managed_forms[0];
		return $cached_id;
	}

	$title = lithia_get_single_service_booking_form_title();
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
 * Ensure the managed single-service booking form exists.
 *
 * @return int
 */
function lithia_ensure_single_service_booking_form(): int {
	if ( ! post_type_exists( 'jet-form-builder' ) || ! lithia_can_sync_managed_content() ) {
		return 0;
	}

	$form_id      = lithia_get_single_service_booking_form_id();
	$form_payload = array(
		'post_type'    => 'jet-form-builder',
		'post_status'  => 'publish',
		'post_title'   => lithia_get_single_service_booking_form_title(),
		'post_name'    => 'single-service-booking-form',
		'post_content' => lithia_get_single_service_booking_form_content(),
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

	foreach ( lithia_get_single_service_booking_form_meta() as $meta_key => $meta_value ) {
		update_post_meta( $form_id, $meta_key, $meta_value );
	}

	clean_post_cache( $form_id );

	return $form_id;
}

/**
 * Seed the managed single-service booking form in wp-admin.
 *
 * @return void
 */
function lithia_maybe_seed_single_service_booking_form(): void {
	if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
		return;
	}

	lithia_ensure_single_service_booking_form();
}
add_action( 'admin_init', 'lithia_maybe_seed_single_service_booking_form', 22 );

/**
 * Render the managed single-service booking form for a Services entry.
 *
 * @param int $service_post_id Service post ID.
 * @return string
 */
function lithia_render_single_service_booking_form( int $service_post_id ): string {
	if ( $service_post_id <= 0 || ! post_type_exists( 'jet-form-builder' ) || ! function_exists( 'do_blocks' ) ) {
		return '';
	}

	$form_id = lithia_get_single_service_booking_form_id();

	if ( ! $form_id && current_user_can( 'manage_options' ) ) {
		$form_id = lithia_ensure_single_service_booking_form();
	}

	if ( ! $form_id ) {
		return '';
	}

	$form_block = sprintf(
		'<!-- wp:jet-forms/form-block {"form_id":%d} /-->',
		$form_id
	);

	$service_post = get_post( $service_post_id );

	if ( ! $service_post instanceof WP_Post || 'services' !== $service_post->post_type ) {
		return '';
	}

	global $post;

	$previous_post = $post instanceof WP_Post ? $post : null;
	$should_reset  = ! $previous_post || (int) $previous_post->ID !== $service_post_id;

	if ( $should_reset ) {
		$post = $service_post;
		setup_postdata( $service_post );
	}

	$form_html = do_blocks( $form_block );

	if ( $should_reset ) {
		if ( $previous_post instanceof WP_Post ) {
			$post = $previous_post;
			setup_postdata( $previous_post );
		} else {
			wp_reset_postdata();
		}
	}

	return is_string( $form_html ) ? $form_html : '';
}

/**
 * Get a raw Services CPT meta value.
 *
 * @param int    $post_id Post ID.
 * @param string $key     Meta key.
 * @param mixed  $default Default value.
 * @return mixed
 */
function lithia_get_service_meta( int $post_id, string $key, $default = '' ) {
	$value = get_post_meta( $post_id, $key, true );

	if ( '' === $value || array() === $value || null === $value ) {
		return $default;
	}

	return $value;
}

/**
 * Return the appointment-module price data for a service.
 *
 * @param int $post_id Service post ID.
 * @return array
 */
function lithia_get_service_appointment_price_data( int $post_id ): array {
	$label = __( 'Price per slot', 'lithia-web-service-theme' );
	$type  = '_app_price';
	$raw   = get_post_meta( $post_id, '_app_price', true );

	$appointment_meta = get_post_meta( $post_id, 'jet_apb_post_meta', true );

	if ( is_array( $appointment_meta ) && ! empty( $appointment_meta['meta_settings'] ) && is_array( $appointment_meta['meta_settings'] ) ) {
		$meta_settings = $appointment_meta['meta_settings'];
		$type          = ! empty( $meta_settings['price_type'] ) ? (string) $meta_settings['price_type'] : '_app_price';

		if ( isset( $meta_settings[ $type ] ) && '' !== (string) $meta_settings[ $type ] ) {
			$raw = $meta_settings[ $type ];
		}
	}

	switch ( $type ) {
		case '_app_price_hour':
			$label = __( 'Price per hour', 'lithia-web-service-theme' );
			break;
		case '_app_price_minute':
			$label = __( 'Price per minute', 'lithia-web-service-theme' );
			break;
		default:
			$type  = '_app_price';
			$label = __( 'Price per slot', 'lithia-web-service-theme' );
			break;
	}

	if ( '' === $raw || ! is_numeric( $raw ) ) {
		return array(
			'type'        => $type,
			'label'       => $label,
			'raw'         => '',
			'amount'      => null,
			'display'     => '',
			'has_price'   => false,
		);
	}

	$amount   = max( 0, (float) $raw );
	$decimals = floor( $amount ) === $amount ? 0 : 2;
	$symbol   = function_exists( 'get_woocommerce_currency_symbol' ) ? get_woocommerce_currency_symbol() : '$';

	return array(
		'type'      => $type,
		'label'     => $label,
		'raw'       => (string) $raw,
		'amount'    => $amount,
		'display'   => $symbol . number_format_i18n( $amount, $decimals ),
		'has_price' => true,
	);
}

/**
 * Return the provider posts related to a service.
 *
 * @param int $post_id Service post ID.
 * @return array
 */
function lithia_get_service_related_provider_posts( int $post_id ): array {
	return lithia_get_related_relation_posts( $post_id, 'providers' );
}

/**
 * Return the related provider IDs for a service.
 *
 * @param int $post_id Service post ID.
 * @return array
 */
function lithia_get_service_related_provider_ids( int $post_id ): array {
	return lithia_get_related_relation_post_ids( $post_id, 'providers' );
}

/**
 * Return the relation key used between services and providers.
 *
 * @return string
 */
function lithia_get_service_provider_relation_key(): string {
	return 'relation_ac5b39e88f77f9e13996cbffebb4efdb';
}

/**
 * Return related post IDs stored in the theme's service/provider relation meta.
 *
 * @param int    $post_id            Source post ID.
 * @param string $expected_post_type Target post type.
 * @return array
 */
function lithia_get_related_relation_post_ids( int $post_id, string $expected_post_type ): array {
	$raw_ids = get_post_meta( $post_id, lithia_get_service_provider_relation_key(), false );

	if ( empty( $raw_ids ) || ! is_array( $raw_ids ) ) {
		return array();
	}

	$related_ids = array();

	foreach ( $raw_ids as $raw_id ) {
		$related_id = absint( $raw_id );

		if ( ! $related_id || get_post_type( $related_id ) !== $expected_post_type ) {
			continue;
		}

		$related_ids[] = $related_id;
	}

	return array_values( array_unique( $related_ids ) );
}

/**
 * Return related posts stored in the theme's service/provider relation meta.
 *
 * @param int    $post_id            Source post ID.
 * @param string $expected_post_type Target post type.
 * @return array
 */
function lithia_get_related_relation_posts( int $post_id, string $expected_post_type ): array {
	$related_ids = lithia_get_related_relation_post_ids( $post_id, $expected_post_type );

	if ( empty( $related_ids ) ) {
		return array();
	}

	return get_posts(
		array(
			'post_type'      => $expected_post_type,
			'post_status'    => 'publish',
			'posts_per_page' => count( $related_ids ),
			'post__in'       => $related_ids,
			'orderby'        => 'post__in',
			'order'          => 'ASC',
		)
	);
}

/**
 * Synchronize the mirrored service/provider relation meta.
 *
 * @param array $service_to_provider_ids Map of service IDs to provider ID arrays.
 * @return void
 */
function lithia_sync_service_provider_relationships( array $service_to_provider_ids ): void {
	$relation_key      = lithia_get_service_provider_relation_key();
	$normalized_map    = array();
	$touched_service_ids  = array();
	$touched_provider_ids = array();

	foreach ( $service_to_provider_ids as $service_id => $provider_ids ) {
		$service_id = absint( $service_id );

		if ( ! $service_id || 'services' !== get_post_type( $service_id ) ) {
			continue;
		}

		$normalized_provider_ids = array();

		foreach ( (array) $provider_ids as $provider_id ) {
			$provider_id = absint( $provider_id );

			if ( ! $provider_id || 'providers' !== get_post_type( $provider_id ) ) {
				continue;
			}

			$normalized_provider_ids[] = $provider_id;
			$touched_provider_ids[]    = $provider_id;
		}

		$normalized_provider_ids          = array_values( array_unique( $normalized_provider_ids ) );
		$normalized_map[ $service_id ]    = $normalized_provider_ids;
		$touched_service_ids[]            = $service_id;

		foreach ( get_post_meta( $service_id, $relation_key, false ) as $existing_related_id ) {
			$existing_related_id = absint( $existing_related_id );

			if ( $existing_related_id && 'providers' === get_post_type( $existing_related_id ) ) {
				$touched_provider_ids[] = $existing_related_id;
			}
		}
	}

	foreach ( array_values( array_unique( $touched_provider_ids ) ) as $provider_id ) {
		foreach ( get_post_meta( $provider_id, $relation_key, false ) as $existing_related_id ) {
			$existing_related_id = absint( $existing_related_id );

			if ( $existing_related_id && 'services' === get_post_type( $existing_related_id ) ) {
				$touched_service_ids[] = $existing_related_id;
			}
		}
	}

	$touched_service_ids  = array_values( array_unique( array_map( 'absint', $touched_service_ids ) ) );
	$touched_provider_ids = array_values( array_unique( array_map( 'absint', $touched_provider_ids ) ) );

	foreach ( $touched_service_ids as $service_id ) {
		delete_post_meta( $service_id, $relation_key );
	}

	foreach ( $touched_provider_ids as $provider_id ) {
		delete_post_meta( $provider_id, $relation_key );
	}

	foreach ( $normalized_map as $service_id => $provider_ids ) {
		foreach ( $provider_ids as $provider_id ) {
			add_post_meta( $service_id, $relation_key, $provider_id, false );
			add_post_meta( $provider_id, $relation_key, $service_id, false );
		}
	}
}

/**
 * Update one service/provider relationship set without dropping neighbor mappings.
 *
 * @param int   $service_id    Service post ID.
 * @param array $provider_ids  Provider post IDs.
 * @return void
 */
function lithia_update_single_service_provider_relationships( int $service_id, array $provider_ids ): void {
	$service_id = absint( $service_id );

	if ( ! $service_id || 'services' !== get_post_type( $service_id ) ) {
		return;
	}

	$touched_provider_ids = array_merge(
		lithia_get_service_related_provider_ids( $service_id ),
		array_map( 'absint', $provider_ids )
	);
	$touched_provider_ids = array_values( array_unique( array_filter( $touched_provider_ids ) ) );
	$relation_map         = array();
	$touched_service_ids  = array( $service_id );

	foreach ( $touched_provider_ids as $provider_id ) {
		foreach ( lithia_get_related_relation_post_ids( $provider_id, 'services' ) as $related_service_id ) {
			$touched_service_ids[] = $related_service_id;
		}
	}

	$touched_service_ids = array_values( array_unique( array_filter( array_map( 'absint', $touched_service_ids ) ) ) );

	foreach ( $touched_service_ids as $related_service_id ) {
		$relation_map[ $related_service_id ] = $related_service_id === $service_id
			? array_values( array_unique( array_filter( array_map( 'absint', $provider_ids ) ) ) )
			: lithia_get_service_related_provider_ids( $related_service_id );
	}

	lithia_sync_service_provider_relationships( $relation_map );
}

/**
 * Build a fallback summary for a post object.
 *
 * @param WP_Post $post       Post object.
 * @param int     $word_count Maximum word count.
 * @return string
 */
function lithia_get_post_fallback_summary( WP_Post $post, int $word_count = 32 ): string {
	$excerpt = trim( (string) $post->post_excerpt );

	if ( '' !== $excerpt ) {
		return $excerpt;
	}

	$content = trim( wp_strip_all_tags( strip_shortcodes( (string) $post->post_content ) ) );

	if ( '' !== $content ) {
		return wp_trim_words( $content, $word_count );
	}

	return '';
}

/**
 * Normalize repeater rows saved by JetEngine.
 *
 * @param mixed $rows Raw repeater value.
 * @return array
 */
function lithia_get_service_repeater_rows( $rows ): array {
	if ( ! is_array( $rows ) ) {
		return array();
	}

	$normalized = array();

	foreach ( $rows as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}

		$clean_row = array();

		foreach ( $row as $key => $value ) {
			if ( ! is_scalar( $value ) ) {
				continue;
			}

			$clean_row[ $key ] = trim( (string) $value );
		}

		if ( ! empty( array_filter( $clean_row ) ) ) {
			$normalized[] = $clean_row;
		}
	}

	return $normalized;
}

/**
 * Build a fallback summary for a service post.
 *
 * @param WP_Post $post Service post object.
 * @return string
 */
function lithia_get_service_fallback_summary( WP_Post $post ): string {
	return lithia_get_post_fallback_summary( $post, 32 );
}

/**
 * Normalize legacy placeholder values so theme fallbacks can take over.
 *
 * @param string $value    Raw meta value.
 * @param array  $invalids Values that should be treated as empty.
 * @return string
 */
function lithia_normalize_service_meta_string( string $value, array $invalids = array() ): string {
	$value = trim( $value );

	if ( '' === $value || in_array( $value, $invalids, true ) ) {
		return '';
	}

	return $value;
}

/**
 * Return normalized service-page data for the current Services post.
 *
 * @param int $post_id Post ID.
 * @return array
 */
function lithia_get_service_page_data( int $post_id ): array {
	$post = get_post( $post_id );

	if ( ! $post instanceof WP_Post || 'services' !== $post->post_type ) {
		return array();
	}

	$hero_image_raw = lithia_get_service_meta( $post_id, 'service_hero_image', 0 );
	$hero_image_id  = is_numeric( $hero_image_raw ) ? (int) $hero_image_raw : 0;
	$price_data     = lithia_get_service_appointment_price_data( $post_id );

	if ( ! $hero_image_id ) {
		$hero_image_id = (int) get_post_thumbnail_id( $post_id );
	}

	$hero_image_url = $hero_image_id ? wp_get_attachment_image_url( $hero_image_id, 'full' ) : '';

	$content_html = '';
	$post_content = trim( (string) $post->post_content );

	if ( '' !== $post_content ) {
		$content_html = apply_filters( 'the_content', $post->post_content );
		$content_html = preg_replace( '/^\s*<h1\b[^>]*>.*?<\/h1>\s*/is', '', (string) $content_html, 1 );
	}

	$primary_cta_label = lithia_normalize_service_meta_string(
		(string) lithia_get_service_meta( $post_id, 'service_primary_cta_label', '' ),
		array( 'Primary CTA Label' )
	);
	$primary_cta_url   = lithia_normalize_service_meta_string(
		(string) lithia_get_service_meta( $post_id, 'service_primary_cta_url', '' ),
		array( '#' )
	);
	$secondary_cta_label = lithia_normalize_service_meta_string(
		(string) lithia_get_service_meta( $post_id, 'service_secondary_cta_label', '' ),
		array( 'Secondary CTA Label' )
	);
	$secondary_cta_url   = lithia_normalize_service_meta_string(
		(string) lithia_get_service_meta( $post_id, 'service_secondary_cta_url', '' ),
		array( '#' )
	);
	$brand_primary_label = lithia_normalize_service_meta_string(
		(string) lithia_get_brand_content( 'primary_cta_label', 'Book Appointment' ),
		array( 'Primary CTA Label' )
	);
	$brand_primary_url   = lithia_normalize_service_meta_string(
		(string) lithia_get_brand_content( 'primary_cta_url', '/book-appointment/' ),
		array( '#' )
	);
	$brand_secondary_label = lithia_normalize_service_meta_string(
		(string) lithia_get_brand_content( 'secondary_cta_label', 'Contact Us' ),
		array( 'Secondary CTA Label' )
	);
	$brand_secondary_url   = lithia_normalize_service_meta_string(
		(string) lithia_get_brand_content( 'secondary_cta_url', '/contact/' ),
		array( '#' )
	);
	$timeline = lithia_normalize_service_meta_string(
		(string) lithia_get_service_meta( $post_id, 'service_timeline', '' )
	);
	$delivery_mode = lithia_normalize_service_meta_string(
		(string) lithia_get_service_meta( $post_id, 'service_delivery_mode', '' )
	);
	$platform = lithia_normalize_service_meta_string(
		(string) lithia_get_service_meta( $post_id, 'service_platform', '' )
	);
	$engagement_type = lithia_normalize_service_meta_string(
		(string) lithia_get_service_meta( $post_id, 'service_engagement_type', '' )
	);
	$details = array();

	if ( $timeline ) {
		$details[] = array(
			'label' => 'Timeline',
			'value' => $timeline,
		);
	}

	if ( $delivery_mode ) {
		$details[] = array(
			'label' => 'Delivery Mode',
			'value' => $delivery_mode,
		);
	}

	if ( $platform ) {
		$details[] = array(
			'label' => 'Platform / Stack',
			'value' => $platform,
		);
	}

	if ( $engagement_type ) {
		$details[] = array(
			'label' => 'Engagement Type',
			'value' => $engagement_type,
		);
	}

	return array(
		'post_id'             => $post_id,
		'title'               => (string) lithia_get_service_meta( $post_id, 'service_hero_title', get_the_title( $post ) ),
		'eyebrow'             => (string) lithia_get_service_meta( $post_id, 'service_hero_eyebrow', 'Service' ),
		'excerpt'             => lithia_get_service_fallback_summary( $post ),
		'hero_text'           => (string) lithia_get_service_meta( $post_id, 'service_hero_text', lithia_get_service_fallback_summary( $post ) ),
		'hero_image_id'       => $hero_image_id,
		'hero_image_url'      => $hero_image_url ? $hero_image_url : '',
		'primary_cta_label'   => $primary_cta_label ? $primary_cta_label : ( $brand_primary_label ? $brand_primary_label : 'Book Appointment' ),
		'primary_cta_url'     => $primary_cta_url ? $primary_cta_url : ( $brand_primary_url ? $brand_primary_url : '/book-appointment/' ),
		'secondary_cta_label' => $secondary_cta_label ? $secondary_cta_label : ( $brand_secondary_label ? $brand_secondary_label : 'Contact Us' ),
		'secondary_cta_url'   => $secondary_cta_url ? $secondary_cta_url : ( $brand_secondary_url ? $brand_secondary_url : '/contact/' ),
		'price_label'         => $price_data['label'],
		'price_raw'           => $price_data['raw'],
		'price_display'       => $price_data['display'],
		'has_price'           => $price_data['has_price'],
		'homepage_spotlight_enabled' => lithia_is_service_homepage_spotlight_enabled( $post_id ),
		'homepage_spotlight_order'   => lithia_get_service_homepage_spotlight_order( $post_id ),
		'details'             => $details,
		'overview_heading'    => (string) lithia_get_service_meta( $post_id, 'service_overview_heading', 'What This Service Covers' ),
		'overview_text'       => (string) lithia_get_service_meta( $post_id, 'service_overview_text', '' ),
		'highlights_heading'  => (string) lithia_get_service_meta( $post_id, 'service_highlights_heading', 'What’s Included' ),
		'highlights'          => lithia_get_service_repeater_rows( lithia_get_service_meta( $post_id, 'service_highlights', array() ) ),
		'process_heading'     => (string) lithia_get_service_meta( $post_id, 'service_process_heading', 'How It Works' ),
		'process_steps'       => lithia_get_service_repeater_rows( lithia_get_service_meta( $post_id, 'service_process_steps', array() ) ),
		'booking_note'        => (string) lithia_get_service_meta( $post_id, 'service_booking_note', lithia_get_business_detail( 'booking_notice', '' ) ),
		'content_html'        => $content_html,
	);
}

/**
 * Return services selected for the homepage spotlight loop.
 *
 * @return array
 */
function lithia_get_homepage_spotlight_services(): array {
	$keys     = lithia_get_service_homepage_spotlight_meta_keys();
	$services = get_posts(
		array(
			'post_type'      => 'services',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'menu_order title',
			'order'          => 'ASC',
			'meta_query'     => array(
				array(
					'key'     => $keys['enabled'],
					'value'   => 'yes',
					'compare' => '=',
				),
			),
		)
	);

	if ( empty( $services ) ) {
		return array();
	}

	usort(
		$services,
		static function ( WP_Post $left, WP_Post $right ): int {
			$left_order  = lithia_get_service_homepage_spotlight_order( (int) $left->ID );
			$right_order = lithia_get_service_homepage_spotlight_order( (int) $right->ID );

			if ( $left_order !== $right_order ) {
				return $left_order <=> $right_order;
			}

			if ( (int) $left->menu_order !== (int) $right->menu_order ) {
				return (int) $left->menu_order <=> (int) $right->menu_order;
			}

			return strnatcasecmp( $left->post_title, $right->post_title );
		}
	);

	return array_values(
		array_filter(
			array_map(
				static function ( WP_Post $service ): array {
					$service_data = lithia_get_service_page_data( (int) $service->ID );

					if ( empty( $service_data ) ) {
						return array();
					}

					return array(
						'post_id'           => (int) $service->ID,
						'title'             => $service_data['title'],
						'eyebrow'           => $service_data['eyebrow'],
						'excerpt'           => $service_data['excerpt'],
						'url'               => get_permalink( $service ),
						'image_url'         => $service_data['hero_image_url'],
						'price_label'       => $service_data['price_label'],
						'price_display'     => $service_data['price_display'],
						'has_price'         => $service_data['has_price'],
						'primary_cta_label' => $service_data['primary_cta_label'],
						'primary_cta_url'   => $service_data['primary_cta_url'],
						'details'           => $service_data['details'],
						'order'             => $service_data['homepage_spotlight_order'],
					);
				},
				$services
			)
		)
	);
}

/**
 * Add service-specific admin columns to the Services list table.
 *
 * @param array $columns Existing columns.
 * @return array
 */
function lithia_manage_services_columns( array $columns ): array {
	$updated = array();

	foreach ( $columns as $key => $label ) {
		$updated[ $key ] = $label;

		if ( 'title' === $key ) {
			$updated['lithia_service_price']    = __( 'Price', 'lithia-web-service-theme' );
			$updated['lithia_service_homepage'] = __( 'Homepage', 'lithia-web-service-theme' );
			$updated['lithia_service_providers'] = __( 'Related Providers', 'lithia-web-service-theme' );
		}
	}

	return $updated;
}
add_filter( 'manage_edit-services_columns', 'lithia_manage_services_columns' );

/**
 * Render custom Services admin columns.
 *
 * @param string $column  Column key.
 * @param int    $post_id Post ID.
 * @return void
 */
function lithia_render_services_custom_column( string $column, int $post_id ): void {
	if ( 'services' !== get_post_type( $post_id ) ) {
		return;
	}

	switch ( $column ) {
		case 'lithia_service_price':
			$price_data = lithia_get_service_appointment_price_data( $post_id );
			echo $price_data['has_price'] ? esc_html( $price_data['display'] ) : '&mdash;'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			break;

		case 'lithia_service_homepage':
			if ( lithia_is_service_homepage_spotlight_enabled( $post_id ) ) {
				$order = lithia_get_service_homepage_spotlight_order( $post_id );
				echo esc_html( $order ? sprintf( __( 'Yes (%d)', 'lithia-web-service-theme' ), $order ) : __( 'Yes', 'lithia-web-service-theme' ) );
			} else {
				echo '&mdash;'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			break;

		case 'lithia_service_providers':
			$providers = lithia_get_service_related_provider_posts( $post_id );

			if ( empty( $providers ) ) {
				echo '&mdash;'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				break;
			}

			echo esc_html(
				implode(
					', ',
					array_map(
						static function ( WP_Post $provider ): string {
							return $provider->post_title;
						},
						$providers
					)
				)
			);
			break;
	}
}
add_action( 'manage_services_posts_custom_column', 'lithia_render_services_custom_column', 10, 2 );

/**
 * Render Services quick edit fields.
 *
 * @param string $column_name Column key.
 * @param string $post_type   Post type.
 * @return void
 */
function lithia_render_services_quick_edit_fields( string $column_name, string $post_type ): void {
	if ( 'services' !== $post_type ) {
		return;
	}

	if ( 'lithia_service_price' === $column_name ) {
		wp_nonce_field( 'lithia_services_quick_edit', 'lithia_services_quick_edit_nonce' );
		?>
		<fieldset class="inline-edit-col-left">
			<div class="inline-edit-col">
				<label>
					<span class="title"><?php esc_html_e( 'Price per slot', 'lithia-web-service-theme' ); ?></span>
					<span class="input-text-wrap">
						<input type="number" min="0" step="0.1" name="lithia_service_price" value="" />
					</span>
				</label>
			</div>
		</fieldset>
		<?php
	}

	if ( 'lithia_service_homepage' === $column_name ) {
		?>
		<fieldset class="inline-edit-col-center">
			<div class="inline-edit-col">
				<label class="alignleft">
					<input type="checkbox" name="lithia_service_homepage_spotlight" value="1" />
					<span class="checkbox-title"><?php esc_html_e( 'Show on homepage', 'lithia-web-service-theme' ); ?></span>
				</label>
				<label>
					<span class="title"><?php esc_html_e( 'Slide order', 'lithia-web-service-theme' ); ?></span>
					<span class="input-text-wrap">
						<input type="number" min="0" step="1" name="lithia_service_homepage_spotlight_order" value="" />
					</span>
				</label>
			</div>
		</fieldset>
		<?php
	}

	if ( 'lithia_service_providers' === $column_name ) {
		$providers = get_posts(
			array(
				'post_type'      => 'providers',
				'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
				'posts_per_page' => -1,
				'orderby'        => 'menu_order title',
				'order'          => 'ASC',
			)
		);
		?>
		<fieldset class="inline-edit-col-right lithia-services-inline-edit">
			<div class="inline-edit-col">
				<span class="title"><?php esc_html_e( 'Related Providers', 'lithia-web-service-theme' ); ?></span>
				<div class="lithia-services-inline-edit__providers">
					<?php if ( empty( $providers ) ) : ?>
						<p class="description"><?php esc_html_e( 'No providers found.', 'lithia-web-service-theme' ); ?></p>
					<?php else : ?>
						<?php foreach ( $providers as $provider ) : ?>
							<label class="lithia-services-inline-edit__provider">
								<input type="checkbox" name="lithia_service_provider_ids[]" value="<?php echo esc_attr( (string) $provider->ID ); ?>" />
								<span><?php echo esc_html( $provider->post_title ); ?></span>
							</label>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>
			</div>
		</fieldset>
		<?php
	}
}
add_action( 'quick_edit_custom_box', 'lithia_render_services_quick_edit_fields', 10, 2 );

/**
 * Save Services quick edit fields.
 *
 * @param int $post_id Post ID.
 * @return void
 */
function lithia_save_services_quick_edit_fields( int $post_id ): void {
	if ( empty( $_POST['lithia_services_quick_edit_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['lithia_services_quick_edit_nonce'] ) ), 'lithia_services_quick_edit' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	if ( 'services' !== get_post_type( $post_id ) ) {
		return;
	}

	$price = isset( $_POST['lithia_service_price'] ) ? trim( (string) wp_unslash( $_POST['lithia_service_price'] ) ) : '';
	$appointment_meta = get_post_meta( $post_id, 'jet_apb_post_meta', true );

	if ( '' === $price || ! is_numeric( $price ) ) {
		delete_post_meta( $post_id, '_app_price' );

		if ( is_array( $appointment_meta ) && ! empty( $appointment_meta['meta_settings'] ) && is_array( $appointment_meta['meta_settings'] ) ) {
			unset( $appointment_meta['meta_settings']['_app_price'] );
			update_post_meta( $post_id, 'jet_apb_post_meta', $appointment_meta );
		}
	} else {
		$normalized_price = max( 0, (float) $price );
		update_post_meta( $post_id, '_app_price', (string) $normalized_price );

		if ( is_array( $appointment_meta ) && ! empty( $appointment_meta['meta_settings'] ) && is_array( $appointment_meta['meta_settings'] ) ) {
			$appointment_meta['meta_settings']['_app_price'] = (string) $normalized_price;

			if ( empty( $appointment_meta['meta_settings']['price_type'] ) ) {
				$appointment_meta['meta_settings']['price_type'] = '_app_price';
			}

			update_post_meta( $post_id, 'jet_apb_post_meta', $appointment_meta );
		}
	}

	$homepage_enabled = ! empty( $_POST['lithia_service_homepage_spotlight'] ) ? 'yes' : 'no';
	$homepage_order   = isset( $_POST['lithia_service_homepage_spotlight_order'] ) ? max( 0, absint( wp_unslash( $_POST['lithia_service_homepage_spotlight_order'] ) ) ) : 0;
	$provider_ids     = array_values(
		array_unique(
			array_filter(
				array_map(
					'absint',
					(array) ( $_POST['lithia_service_provider_ids'] ?? array() )
				)
			)
		)
	);

	update_post_meta( $post_id, 'service_homepage_spotlight_enabled', $homepage_enabled );
	update_post_meta( $post_id, 'service_homepage_spotlight_order', $homepage_order );
	lithia_update_single_service_provider_relationships( $post_id, $provider_ids );
}
add_action( 'save_post_services', 'lithia_save_services_quick_edit_fields' );

/**
 * Enqueue Services quick edit assets.
 *
 * @param string $hook_suffix Current admin hook.
 * @return void
 */
function lithia_enqueue_services_quick_edit_assets( string $hook_suffix ): void {
	if ( 'edit.php' !== $hook_suffix ) {
		return;
	}

	$screen = get_current_screen();

	if ( ! $screen || 'edit-services' !== $screen->id ) {
		return;
	}

	wp_enqueue_script(
		'lithia-services-quick-edit',
		get_theme_file_uri( 'assets/js/admin-services-quick-edit.js' ),
		array( 'jquery', 'inline-edit-post' ),
		lithia_get_theme_asset_version( 'assets/js/admin-services-quick-edit.js' ),
		true
	);

	wp_enqueue_style(
		'lithia-services-quick-edit',
		get_theme_file_uri( 'assets/css/admin-services-quick-edit.css' ),
		array(),
		lithia_get_theme_asset_version( 'assets/css/admin-services-quick-edit.css' )
	);

	$services  = get_posts(
		array(
			'post_type'      => 'services',
			'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
			'posts_per_page' => -1,
			'orderby'        => 'menu_order title',
			'order'          => 'ASC',
		)
	);
	$row_data = array();

	foreach ( $services as $service ) {
		$price_data = lithia_get_service_appointment_price_data( (int) $service->ID );

		$row_data[ (int) $service->ID ] = array(
			'price'                      => $price_data['raw'],
			'homepageSpotlightEnabled'   => lithia_is_service_homepage_spotlight_enabled( (int) $service->ID ),
			'homepageSpotlightOrder'     => lithia_get_service_homepage_spotlight_order( (int) $service->ID ),
			'providerIds'                => lithia_get_service_related_provider_ids( (int) $service->ID ),
		);
	}

	wp_localize_script(
		'lithia-services-quick-edit',
		'lithiaServicesQuickEdit',
		array(
			'rows' => $row_data,
		)
	);
}
add_action( 'admin_enqueue_scripts', 'lithia_enqueue_services_quick_edit_assets' );

/**
 * Return normalized provider-page data for the current Provider post.
 *
 * @param int $post_id Post ID.
 * @return array
 */
function lithia_get_provider_page_data( int $post_id ): array {
	$post = get_post( $post_id );

	if ( ! $post instanceof WP_Post || 'providers' !== $post->post_type ) {
		return array();
	}

	$image_id  = (int) get_post_thumbnail_id( $post_id );
	$image_url = $image_id ? wp_get_attachment_image_url( $image_id, 'large' ) : '';

	$content_html = '';
	$post_content = trim( (string) $post->post_content );

	if ( '' !== $post_content ) {
		$content_html = apply_filters( 'the_content', $post->post_content );
		$content_html = preg_replace( '/^\s*<h1\b[^>]*>.*?<\/h1>\s*/is', '', (string) $content_html, 1 );
	}

	$related_services = array_map(
		static function ( WP_Post $service ): array {
			return array(
				'id'      => (int) $service->ID,
				'title'   => get_the_title( $service ),
				'url'     => get_permalink( $service ),
				'excerpt' => lithia_get_post_fallback_summary( $service, 22 ),
			);
		},
		lithia_get_related_relation_posts( $post_id, 'services' )
	);

	return array(
		'post_id'             => $post_id,
		'title'               => get_the_title( $post ),
		'eyebrow'             => 'Provider',
		'summary'             => lithia_get_post_fallback_summary( $post, 28 ),
		'image_id'            => $image_id,
		'image_url'           => $image_url ? $image_url : '',
		'content_html'        => $content_html,
		'related_services'    => $related_services,
		'primary_cta_label'   => (string) lithia_get_brand_content( 'primary_cta_label', 'Book Appointment' ),
		'primary_cta_url'     => (string) lithia_get_brand_content( 'primary_cta_url', '/book-appointment/' ),
		'secondary_cta_label' => 'Browse Services',
		'secondary_cta_url'   => '/services/',
	);
}
