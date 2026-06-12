<?php
/**
 * Robot Custom Post Type Registration
 *
 * @package Astra Child
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the Robot custom post type.
 *
 * @since 1.0.0
 */
function na_register_cpt_robot() {
	$labels = array(
		'name'                  => 'Robots',
		'singular_name'         => 'Robot',
		'menu_name'             => 'Robots',
		'name_admin_bar'        => 'Robot',
		'archives'              => 'Robot Archives',
		'attributes'            => 'Robot Attributes',
		'parent_item_colon'     => 'Parent Robot:',
		'all_items'             => 'All Robots',
		'all_items'             => 'All Robots',
		'add_new_item'          => 'Add New Robot',
		'add_new'               => 'Add New',
		'new_item'              => 'New Robot',
		'new_item'              => 'New Robot',
		'edit_item'             => 'Edit Robot',
		'edit_item'             => 'Edit Robot',
		'update_item'           => 'Update Robot',
		'update_item'           => 'Update Robot',
		'view_item'             => 'View Robot',
		'search_items'          => 'Search Robot',
		'search_items'          => 'Search Robot',
		'not_found'             => 'Not found',
		'not_found_in_trash'    => 'Not found in Trash',
		'featured_image'        => 'Featured Image',
		'set_featured_image'    => 'Set featured image',
		'remove_featured_image' => 'Remove featured image',
		'use_featured_image'    => 'Use as featured image',
		'insert_into_item'      => 'Insert into Robot',
		'uploaded_to_this_item'  => 'Uploaded to this Robot',
		'items_list'            => 'Robots list',
		'items_list_navigation' => 'Robots list navigation',
		'filter_items_list'     => 'Filter Robots list',
	);

	$args = array(
		'label'                 => 'Robot',
		'description'           => 'License products representing automated trading systems.',
		'labels'                => $labels,
		'supports'              => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ),
		'taxonomies'            => array( 'market', 'asset_class', 'timeframe', 'strategy_type', 'risk_level', 'product_type', 'topic', 'status', 'release_version' ),
		'hierarchical'          => false,
		'public'                => true,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'menu_position'         => 6,
		'menu_icon'             => 'dashicons-robot',
		'show_in_admin_bar'     => true,
		'show_in_nav_menus'     => true,
		'can_export'            => true,
		'has_archive'          => true,
		'exclude_from_search'   => false,
		'publicly_queryable'    => true,
		'capability_type'       => 'post',
		'rewrite'               => array(
			'slug'       => 'robot',
			'with_front' => false,
			'feeds'      => false,
		),
	);

	register_post_type( 'robot', $args );
}
add_action( 'init', 'na_register_cpt_robot', 10 );