<?php
/**
 * Backtest Custom Post Type Registration
 *
 * @package Astra Child
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the Backtest custom post type.
 *
 * @since 1.0.0
 */
function na_register_cpt_backtest() {
	$labels = array(
		'name'                  => 'Backtests',
		'singular_name'         => 'Backtest',
		'menu_name'             => 'Backtests',
		'name_admin_bar'        => 'Backtest',
		'archives'              => 'Backtest Archives',
		'attributes'            => 'Backtest Attributes',
		'parent_item_colon'     => 'Parent Backtest:',
		'all_items'             => 'All Backtests',
		'all_items'             => 'All Backtests',
		'add_new_item'          => 'Add New Backtest',
		'add_new'               => 'Add New',
		'new_item'              => 'New Backtest',
		'new_item'              => 'New Backtest',
		'edit_item'             => 'Edit Backtest',
		'edit_item'             => 'Edit Backtest',
		'update_item'           => 'Update Backtest',
		'update_item'           => 'Update Backtest',
		'view_item'             => 'View Backtest',
		'search_items'          => 'Search Backtest',
		'search_items'          => 'Search Backtest',
		'not_found'             => 'Not found',
		'not_found_in_trash'    => 'Not found in Trash',
		'featured_image'        => 'Featured Image',
		'set_featured_image'    => 'Set featured image',
		'remove_featured_image' => 'Remove featured image',
		'use_featured_image'    => 'Use as featured image',
		'insert_into_item'      => 'Insert into Backtest',
		'uploaded_to_this_item'  => 'Uploaded to this Backtest',
		'items_list'            => 'Backtests list',
		'items_list_navigation' => 'Backtests list navigation',
		'filter_items_list'     => 'Filter Backtests list',
	);

	$args = array(
		'label'                 => 'Backtest',
		'description'           => 'Backtest records and result pages linked to strategies.',
		'labels'                => $labels,
		'supports'              => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ),
		'taxonomies'            => array( 'market', 'asset_class', 'timeframe', 'topic' ),
		'hierarchical'          => false,
		'public'                => true,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'menu_position'         => 9,
		'menu_icon'             => 'dashicons-analytics',
		'show_in_admin_bar'     => true,
		'show_in_nav_menus'     => true,
		'can_export'            => true,
		'has_archive'          => true,
		'exclude_from_search'   => false,
		'publicly_queryable'    => true,
		'capability_type'       => 'post',
		'rewrite'               => array(
			'slug'       => 'backtest',
			'with_front' => false,
			'feeds'      => false,
		),
	);

	register_post_type( 'backtest', $args );
}
add_action( 'init', 'na_register_cpt_backtest', 10 );