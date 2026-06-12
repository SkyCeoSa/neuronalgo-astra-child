<?php
/**
 * Indicator Custom Post Type Registration
 *
 * @package Astra Child
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the Indicator custom post type.
 *
 * @since 1.0.0
 */
function na_register_cpt_indicator() {
	$labels = array(
		'name'                  => 'Indicators',
		'singular_name'         => 'Indicator',
		'menu_name'             => 'Indicators',
		'name_admin_bar'        => 'Indicator',
		'archives'              => 'Indicator Archives',
		'attributes'            => 'Indicator Attributes',
		'parent_item_colon'     => 'Parent Indicator:',
		'all_items'             => 'All Indicators',
		'all_items'             => 'All Indicators',
		'add_new_item'          => 'Add New Indicator',
		'add_new'               => 'Add New',
		'new_item'              => 'New Indicator',
		'new_item'              => 'New Indicator',
		'edit_item'             => 'Edit Indicator',
		'edit_item'             => 'Edit Indicator',
		'update_item'           => 'Update Indicator',
		'update_item'           => 'Update Indicator',
		'view_item'             => 'View Indicator',
		'search_items'          => 'Search Indicator',
		'search_items'          => 'Search Indicator',
		'not_found'             => 'Not found',
		'not_found_in_trash'    => 'Not found in Trash',
		'featured_image'        => 'Featured Image',
		'set_featured_image'    => 'Set featured image',
		'remove_featured_image' => 'Remove featured image',
		'use_featured_image'    => 'Use as featured image',
		'insert_into_item'      => 'Insert into Indicator',
		'uploaded_to_this_item'  => 'Uploaded to this Indicator',
		'items_list'            => 'Indicators list',
		'items_list_navigation' => 'Indicators list navigation',
		'filter_items_list'     => 'Filter Indicators list',
	);

	$args = array(
		'label'                 => 'Indicator',
		'description'           => 'Visual decision-support tools and auxiliary trading products.',
		'labels'                => $labels,
		'supports'              => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ),
		'taxonomies'            => array( 'market', 'asset_class', 'timeframe', 'risk_level', 'product_type', 'topic', 'status', 'release_version' ),
		'hierarchical'          => false,
		'public'                => true,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'menu_position'         => 7,
		'menu_icon'             => 'dashicons-chart-area',
		'show_in_admin_bar'     => true,
		'show_in_nav_menus'     => true,
		'can_export'            => true,
		'has_archive'          => true,
		'exclude_from_search'   => false,
		'publicly_queryable'    => true,
		'capability_type'       => 'post',
		'rewrite'               => array(
			'slug'       => 'indicator',
			'with_front' => false,
			'feeds'      => false,
		),
	);

	register_post_type( 'indicator', $args );
}
add_action( 'init', 'na_register_cpt_indicator', 10 );