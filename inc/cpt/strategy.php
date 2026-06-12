<?php
/**
 * Strategy Custom Post Type Registration
 *
 * @package Astra Child
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the Strategy custom post type.
 *
 * @since 1.0.0
 */
function na_register_cpt_strategy() {
	$labels = array(
		'name'                  => 'Strategies',
		'singular_name'         => 'Strategy',
		'menu_name'             => 'Strategies',
		'name_admin_bar'        => 'Strategy',
		'archives'              => 'Strategy Archives',
		'attributes'            => 'Strategy Attributes',
		'parent_item_colon'     => 'Parent Strategy:',
		'all_items'             => 'All Strategies',
		'all_items'             => 'All Strategies',
		'add_new_item'          => 'Add New Strategy',
		'add_new'               => 'Add New',
		'new_item'              => 'New Strategy',
		'new_item'              => 'New Strategy',
		'edit_item'             => 'Edit Strategy',
		'edit_item'             => 'Edit Strategy',
		'update_item'           => 'Update Strategy',
		'update_item'           => 'Update Strategy',
		'view_item'             => 'View Strategy',
		'search_items'          => 'Search Strategy',
		'search_items'          => 'Search Strategy',
		'not_found'             => 'Not found',
		'not_found_in_trash'    => 'Not found in Trash',
		'featured_image'        => 'Featured Image',
		'set_featured_image'    => 'Set featured image',
		'remove_featured_image' => 'Remove featured image',
		'use_featured_image'    => 'Use as featured image',
		'insert_into_item'      => 'Insert into Strategy',
		'uploaded_to_this_item'  => 'Uploaded to this Strategy',
		'items_list'            => 'Strategies list',
		'items_list_navigation' => 'Strategies list navigation',
		'filter_items_list'     => 'Filter Strategies list',
	);

	$args = array(
		'label'                 => 'Strategy',
		'description'           => 'Quantified trading systems with performance metrics, methodology, and proof.',
		'labels'                => $labels,
		'supports'              => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ),
		'taxonomies'            => array( 'market', 'asset_class', 'timeframe', 'strategy_type', 'risk_level', 'topic', 'status', 'release_version' ),
		'hierarchical'          => false,
		'public'                => true,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'menu_position'         => 5,
		'menu_icon'             => 'dashicons-chart-line',
		'show_in_admin_bar'     => true,
		'show_in_nav_menus'     => true,
		'can_export'            => true,
		'has_archive'          => true,
		'exclude_from_search'   => false,
		'publicly_queryable'    => true,
		'capability_type'       => 'post',
		'rewrite'               => array(
			'slug'       => 'strategy',
			'with_front' => false,
			'feeds'      => false,
		),
	);

	register_post_type( 'strategy', $args );
}
add_action( 'init', 'na_register_cpt_strategy', 10 );