<?php
/**
 * Testimonial Custom Post Type Registration
 *
 * @package Astra Child
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the Testimonial custom post type.
 *
 * Note: Testimonials are used as reusable blocks, not browsed directly.
 * No archive or publicly queryable single page.
 *
 * @since 1.0.0
 */
function na_register_cpt_testimonial() {
	$labels = array(
		'name'                  => 'Testimonials',
		'singular_name'         => 'Testimonial',
		'menu_name'             => 'Testimonials',
		'name_admin_bar'        => 'Testimonial',
		'archives'              => 'Testimonial Archives',
		'attributes'            => 'Testimonial Attributes',
		'parent_item_colon'     => 'Parent Testimonial:',
		'all_items'             => 'All Testimonials',
		'all_items'             => 'All Testimonials',
		'add_new_item'          => 'Add New Testimonial',
		'add_new'               => 'Add New',
		'new_item'              => 'New Testimonial',
		'new_item'              => 'New Testimonial',
		'edit_item'             => 'Edit Testimonial',
		'edit_item'             => 'Edit Testimonial',
		'update_item'           => 'Update Testimonial',
		'update_item'           => 'Update Testimonial',
		'view_item'             => 'View Testimonial',
		'search_items'          => 'Search Testimonial',
		'search_items'          => 'Search Testimonial',
		'not_found'             => 'Not found',
		'not_found_in_trash'    => 'Not found in Trash',
		'featured_image'        => 'Featured Image',
		'set_featured_image'    => 'Set featured image',
		'remove_featured_image' => 'Remove featured image',
		'use_featured_image'    => 'Use as featured image',
		'insert_into_item'      => 'Insert into Testimonial',
		'uploaded_to_this_item'  => 'Uploaded to this Testimonial',
		'items_list'            => 'Testimonials list',
		'items_list_navigation' => 'Testimonials list navigation',
		'filter_items_list'     => 'Filter Testimonials list',
	);

	$args = array(
		'label'                 => 'Testimonial',
		'description'           => 'Social proof and user outcomes for product pages.',
		'labels'                => $labels,
		'supports'              => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ),
		'taxonomies'            => array(),
		'hierarchical'          => false,
		'public'                => false,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'menu_position'         => 13,
		'menu_icon'             => 'dashicons-format-quote',
		'show_in_admin_bar'     => false,
		'show_in_nav_menus'     => false,
		'can_export'            => true,
		'has_archive'          => false,
		'exclude_from_search'   => true,
		'publicly_queryable'    => false,
		'capability_type'       => 'post',
		'rewrite'               => false,
	);

	register_post_type( 'testimonial', $args );
}
add_action( 'init', 'na_register_cpt_testimonial', 10 );