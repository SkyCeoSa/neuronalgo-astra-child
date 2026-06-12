<?php
/**
 * Event Custom Post Type Registration
 *
 * @package Astra Child
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the Event custom post type.
 *
 * @since 1.0.0
 */
function na_register_cpt_event() {
	$labels = array(
		'name'                  => 'Events',
		'singular_name'         => 'Event',
		'menu_name'             => 'Events',
		'name_admin_bar'        => 'Event',
		'archives'              => 'Event Archives',
		'attributes'            => 'Event Attributes',
		'parent_item_colon'     => 'Parent Event:',
		'all_items'             => 'All Events',
		'all_items'             => 'All Events',
		'add_new_item'          => 'Add New Event',
		'add_new'               => 'Add New',
		'new_item'              => 'New Event',
		'new_item'              => 'New Event',
		'edit_item'             => 'Edit Event',
		'edit_item'             => 'Edit Event',
		'update_item'           => 'Update Event',
		'update_item'           => 'Update Event',
		'view_item'             => 'View Event',
		'search_items'          => 'Search Event',
		'search_items'          => 'Search Event',
		'not_found'             => 'Not found',
		'not_found_in_trash'    => 'Not found in Trash',
		'featured_image'        => 'Featured Image',
		'set_featured_image'    => 'Set featured image',
		'remove_featured_image' => 'Remove featured image',
		'use_featured_image'    => 'Use as featured image',
		'insert_into_item'      => 'Insert into Event',
		'uploaded_to_this_item'  => 'Uploaded to this Event',
		'items_list'            => 'Events list',
		'items_list_navigation' => 'Events list navigation',
		'filter_items_list'     => 'Filter Events list',
	);

	$args = array(
		'label'                 => 'Event',
		'description'           => 'Webinars, live sessions, and community events.',
		'labels'                => $labels,
		'supports'              => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ),
		'taxonomies'            => array( 'topic' ),
		'hierarchical'          => false,
		'public'                => true,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'menu_position'         => 14,
		'menu_icon'             => 'dashicons-calendar-alt',
		'show_in_admin_bar'     => true,
		'show_in_nav_menus'     => true,
		'can_export'            => true,
		'has_archive'          => true,
		'exclude_from_search'   => false,
		'publicly_queryable'    => true,
		'capability_type'       => 'post',
		'rewrite'               => array(
			'slug'       => 'event',
			'with_front' => false,
			'feeds'      => false,
		),
	);

	register_post_type( 'event', $args );
}
add_action( 'init', 'na_register_cpt_event', 10 );