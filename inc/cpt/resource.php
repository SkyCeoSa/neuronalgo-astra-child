<?php
/**
 * Resource Custom Post Type Registration
 *
 * @package Astra Child
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the Resource custom post type.
 *
 * @since 1.0.0
 */
function na_register_cpt_resource() {
	$labels = array(
		'name'                  => 'Resources',
		'singular_name'         => 'Resource',
		'menu_name'             => 'Resources',
		'name_admin_bar'        => 'Resource',
		'archives'              => 'Resource Archives',
		'attributes'            => 'Resource Attributes',
		'parent_item_colon'     => 'Parent Resource:',
		'all_items'             => 'All Resources',
		'all_items'             => 'All Resources',
		'add_new_item'          => 'Add New Resource',
		'add_new'               => 'Add New',
		'new_item'              => 'New Resource',
		'new_item'              => 'New Resource',
		'edit_item'             => 'Edit Resource',
		'edit_item'             => 'Edit Resource',
		'update_item'           => 'Update Resource',
		'update_item'           => 'Update Resource',
		'view_item'             => 'View Resource',
		'search_items'          => 'Search Resource',
		'search_items'          => 'Search Resource',
		'not_found'             => 'Not found',
		'not_found_in_trash'    => 'Not found in Trash',
		'featured_image'        => 'Featured Image',
		'set_featured_image'    => 'Set featured image',
		'remove_featured_image' => 'Remove featured image',
		'use_featured_image'    => 'Use as featured image',
		'insert_into_item'      => 'Insert into Resource',
		'uploaded_to_this_item'  => 'Uploaded to this Resource',
		'items_list'            => 'Resources list',
		'items_list_navigation' => 'Resources list navigation',
		'filter_items_list'     => 'Filter Resources list',
	);

	$args = array(
		'label'                 => 'Resource',
		'description'           => 'Downloadable assets and support materials.',
		'labels'                => $labels,
		'supports'              => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ),
		'taxonomies'            => array( 'topic', 'product_type' ),
		'hierarchical'          => false,
		'public'                => true,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'menu_position'         => 11,
		'menu_icon'             => 'dashicons-download',
		'show_in_admin_bar'     => true,
		'show_in_nav_menus'     => true,
		'can_export'            => true,
		'has_archive'          => true,
		'exclude_from_search'   => false,
		'publicly_queryable'    => true,
		'capability_type'       => 'post',
		'rewrite'               => array(
			'slug'       => 'resource',
			'with_front' => false,
			'feeds'      => false,
		),
	);

	register_post_type( 'resource', $args );
}
add_action( 'init', 'na_register_cpt_resource', 10 );