<?php
/**
 * Glossary Custom Post Type Registration
 *
 * @package Astra Child
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the Glossary custom post type.
 *
 * @since 1.0.0
 */
function na_register_cpt_glossary() {
	$labels = array(
		'name'                  => 'Glossary',
		'singular_name'         => 'Glossary Entry',
		'menu_name'             => 'Glossary',
		'name_admin_bar'        => 'Glossary Entry',
		'archives'              => 'Glossary Archives',
		'attributes'            => 'Glossary Attributes',
		'parent_item_colon'     => 'Parent Glossary:',
		'all_items'             => 'All Glossary Entries',
		'all_items'             => 'All Glossary Entries',
		'add_new_item'          => 'Add New Glossary Entry',
		'add_new'               => 'Add New',
		'new_item'              => 'New Glossary Entry',
		'new_item'              => 'New Glossary Entry',
		'edit_item'             => 'Edit Glossary Entry',
		'edit_item'             => 'Edit Glossary Entry',
		'update_item'           => 'Update Glossary Entry',
		'update_item'           => 'Update Glossary Entry',
		'view_item'             => 'View Glossary Entry',
		'search_items'          => 'Search Glossary',
		'search_items'          => 'Search Glossary',
		'not_found'             => 'Not found',
		'not_found_in_trash'    => 'Not found in Trash',
		'featured_image'        => 'Featured Image',
		'set_featured_image'    => 'Set featured image',
		'remove_featured_image' => 'Remove featured image',
		'use_featured_image'    => 'Use as featured image',
		'insert_into_item'      => 'Insert into Glossary',
		'uploaded_to_this_item'  => 'Uploaded to this Glossary',
		'items_list'            => 'Glossary list',
		'items_list_navigation' => 'Glossary list navigation',
		'filter_items_list'     => 'Filter Glossary list',
	);

	$args = array(
		'label'                 => 'Glossary',
		'description'           => 'Definitions of trading and quantitative finance terms.',
		'labels'                => $labels,
		'supports'              => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ),
		'taxonomies'            => array( 'topic' ),
		'hierarchical'          => false,
		'public'                => true,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'menu_position'         => 12,
		'menu_icon'             => 'dashicons-book-alt',
		'show_in_admin_bar'     => true,
		'show_in_nav_menus'     => true,
		'can_export'            => true,
		'has_archive'          => true,
		'exclude_from_search'   => false,
		'publicly_queryable'    => true,
		'capability_type'       => 'post',
		'rewrite'               => array(
			'slug'       => 'glossary',
			'with_front' => false,
			'feeds'      => false,
		),
	);

	register_post_type( 'glossary', $args );
}
add_action( 'init', 'na_register_cpt_glossary', 10 );