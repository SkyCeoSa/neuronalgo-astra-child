<?php
/**
 * Report Custom Post Type Registration
 *
 * @package Astra Child
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the Report custom post type.
 *
 * @since 1.0.0
 */
function na_register_cpt_report() {
	$labels = array(
		'name'                  => 'Reports',
		'singular_name'         => 'Report',
		'menu_name'             => 'Reports',
		'name_admin_bar'        => 'Report',
		'archives'              => 'Report Archives',
		'attributes'            => 'Report Attributes',
		'parent_item_colon'     => 'Parent Report:',
		'all_items'             => 'All Reports',
		'all_items'             => 'All Reports',
		'add_new_item'          => 'Add New Report',
		'add_new'               => 'Add New',
		'new_item'              => 'New Report',
		'new_item'              => 'New Report',
		'edit_item'             => 'Edit Report',
		'edit_item'             => 'Edit Report',
		'update_item'           => 'Update Report',
		'update_item'           => 'Update Report',
		'view_item'             => 'View Report',
		'search_items'          => 'Search Report',
		'search_items'          => 'Search Report',
		'not_found'             => 'Not found',
		'not_found_in_trash'    => 'Not found in Trash',
		'featured_image'        => 'Featured Image',
		'set_featured_image'    => 'Set featured image',
		'remove_featured_image' => 'Remove featured image',
		'use_featured_image'    => 'Use as featured image',
		'insert_into_item'      => 'Insert into Report',
		'uploaded_to_this_item'  => 'Uploaded to this Report',
		'items_list'            => 'Reports list',
		'items_list_navigation' => 'Reports list navigation',
		'filter_items_list'     => 'Filter Reports list',
	);

	$args = array(
		'label'                 => 'Report',
		'description'           => 'Periodic reports and research summaries.',
		'labels'                => $labels,
		'supports'              => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ),
		'taxonomies'            => array( 'topic', 'market' ),
		'hierarchical'          => false,
		'public'                => true,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'menu_position'         => 10,
		'menu_icon'             => 'dashicons-media-document',
		'show_in_admin_bar'     => true,
		'show_in_nav_menus'     => true,
		'can_export'            => true,
		'has_archive'          => true,
		'exclude_from_search'   => false,
		'publicly_queryable'    => true,
		'capability_type'       => 'post',
		'rewrite'               => array(
			'slug'       => 'report',
			'with_front' => false,
			'feeds'      => false,
		),
	);

	register_post_type( 'report', $args );
}
add_action( 'init', 'na_register_cpt_report', 10 );