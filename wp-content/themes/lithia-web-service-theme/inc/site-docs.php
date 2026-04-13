<?php
/**
 * Site Docs content model.
 *
 * @package LithiaWebServiceTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the Site Docs content model.
 *
 * @return void
 */
function lithia_register_site_docs_content_model(): void {
	register_post_type(
		'site_docs',
		array(
			'labels' => array(
				'name'                  => __( 'Site Docs', 'lithia-web-service-theme' ),
				'singular_name'         => __( 'Site Doc', 'lithia-web-service-theme' ),
				'menu_name'             => __( 'Site Docs', 'lithia-web-service-theme' ),
				'name_admin_bar'        => __( 'Site Doc', 'lithia-web-service-theme' ),
				'add_new'               => __( 'Add New', 'lithia-web-service-theme' ),
				'add_new_item'          => __( 'Add New Site Doc', 'lithia-web-service-theme' ),
				'edit_item'             => __( 'Edit Site Doc', 'lithia-web-service-theme' ),
				'new_item'              => __( 'New Site Doc', 'lithia-web-service-theme' ),
				'view_item'             => __( 'View Site Doc', 'lithia-web-service-theme' ),
				'view_items'            => __( 'View Site Docs', 'lithia-web-service-theme' ),
				'search_items'          => __( 'Search Site Docs', 'lithia-web-service-theme' ),
				'not_found'             => __( 'No site docs found.', 'lithia-web-service-theme' ),
				'not_found_in_trash'    => __( 'No site docs found in Trash.', 'lithia-web-service-theme' ),
				'all_items'             => __( 'All Site Docs', 'lithia-web-service-theme' ),
				'archives'              => __( 'Site Docs Archive', 'lithia-web-service-theme' ),
				'attributes'            => __( 'Site Doc Attributes', 'lithia-web-service-theme' ),
				'insert_into_item'      => __( 'Insert into site doc', 'lithia-web-service-theme' ),
				'uploaded_to_this_item' => __( 'Uploaded to this site doc', 'lithia-web-service-theme' ),
			),
			'public'              => true,
			'show_in_rest'        => true,
			'menu_position'       => 26,
			'menu_icon'           => 'dashicons-media-document',
			'has_archive'         => 'site-docs',
			'rewrite'             => array(
				'slug'       => 'site-docs',
				'with_front' => false,
			),
			'supports'            => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'author', 'page-attributes' ),
			'taxonomies'          => array( 'site_doc_type', 'site_doc_audience' ),
			'publicly_queryable'  => true,
			'show_in_nav_menus'   => true,
			'show_in_admin_bar'   => true,
			'exclude_from_search' => false,
			'hierarchical'        => false,
			'map_meta_cap'        => true,
		)
	);

	register_taxonomy(
		'site_doc_type',
		array( 'site_docs' ),
		array(
			'labels' => array(
				'name'              => __( 'Doc Types', 'lithia-web-service-theme' ),
				'singular_name'     => __( 'Doc Type', 'lithia-web-service-theme' ),
				'search_items'      => __( 'Search Doc Types', 'lithia-web-service-theme' ),
				'all_items'         => __( 'All Doc Types', 'lithia-web-service-theme' ),
				'edit_item'         => __( 'Edit Doc Type', 'lithia-web-service-theme' ),
				'update_item'       => __( 'Update Doc Type', 'lithia-web-service-theme' ),
				'add_new_item'      => __( 'Add New Doc Type', 'lithia-web-service-theme' ),
				'new_item_name'     => __( 'New Doc Type Name', 'lithia-web-service-theme' ),
				'menu_name'         => __( 'Doc Types', 'lithia-web-service-theme' ),
			),
			'public'            => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'hierarchical'      => true,
			'rewrite'           => array(
				'slug'       => 'site-doc-type',
				'with_front' => false,
			),
		)
	);

	register_taxonomy(
		'site_doc_audience',
		array( 'site_docs' ),
		array(
			'labels' => array(
				'name'              => __( 'Audiences', 'lithia-web-service-theme' ),
				'singular_name'     => __( 'Audience', 'lithia-web-service-theme' ),
				'search_items'      => __( 'Search Audiences', 'lithia-web-service-theme' ),
				'all_items'         => __( 'All Audiences', 'lithia-web-service-theme' ),
				'edit_item'         => __( 'Edit Audience', 'lithia-web-service-theme' ),
				'update_item'       => __( 'Update Audience', 'lithia-web-service-theme' ),
				'add_new_item'      => __( 'Add New Audience', 'lithia-web-service-theme' ),
				'new_item_name'     => __( 'New Audience Name', 'lithia-web-service-theme' ),
				'menu_name'         => __( 'Audiences', 'lithia-web-service-theme' ),
			),
			'public'            => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'hierarchical'      => true,
			'rewrite'           => array(
				'slug'       => 'site-doc-audience',
				'with_front' => false,
			),
		)
	);
}
add_action( 'init', 'lithia_register_site_docs_content_model' );

/**
 * Return the default Site Docs taxonomy terms.
 *
 * @return array
 */
function lithia_get_site_docs_default_terms(): array {
	return array(
		'site_doc_type' => array(
			array(
				'name'        => 'How To',
				'slug'        => 'how-to',
				'description' => 'Client-facing instructions for making routine site updates.',
			),
			array(
				'name'        => 'Build Notes',
				'slug'        => 'build-notes',
				'description' => 'Project setup decisions, build steps, and implementation notes.',
			),
			array(
				'name'        => 'Tech Notes',
				'slug'        => 'tech-notes',
				'description' => 'Technical references, sync details, and engineering documentation.',
			),
		),
		'site_doc_audience' => array(
			array(
				'name'        => 'End Client',
				'slug'        => 'end-client',
				'description' => 'Docs meant for day-to-day client use.',
			),
			array(
				'name'        => 'Admin',
				'slug'        => 'admin',
				'description' => 'Docs meant for site admins, builders, or support staff.',
			),
		),
	);
}

/**
 * Create the default Site Docs terms if they do not already exist.
 *
 * @return void
 */
function lithia_ensure_site_docs_terms(): void {
	foreach ( lithia_get_site_docs_default_terms() as $taxonomy => $terms ) {
		if ( ! taxonomy_exists( $taxonomy ) ) {
			continue;
		}

		foreach ( $terms as $term_config ) {
			$existing_term = term_exists( $term_config['slug'], $taxonomy );

			if ( ! $existing_term ) {
				wp_insert_term(
					$term_config['name'],
					$taxonomy,
					array(
						'slug'        => $term_config['slug'],
						'description' => $term_config['description'],
					)
				);
			}
		}
	}
}

/**
 * Seed the default Site Docs terms in admin and CLI contexts.
 *
 * @return void
 */
function lithia_maybe_seed_site_docs_terms(): void {
	$can_manage = current_user_can( 'manage_options' ) || ( defined( 'WP_CLI' ) && WP_CLI );

	if ( ! $can_manage ) {
		return;
	}

	lithia_ensure_site_docs_terms();
}
add_action( 'admin_init', 'lithia_maybe_seed_site_docs_terms', 24 );
