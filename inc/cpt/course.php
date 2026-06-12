<?php
/**
 * Course Custom Post Type Registration
 *
 * @package Astra Child
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the Course custom post type.
 *
 * @since 1.0.0
 */
function na_register_cpt_course() {
	$labels = array(
		'name'                  => 'Courses',
		'singular_name'         => 'Course',
		'menu_name'             => 'Courses',
		'name_admin_bar'        => 'Course',
		'archives'              => 'Course Archives',
		'attributes'            => 'Course Attributes',
		'parent_item_colon'     => 'Parent Course:',
		'all_items'             => 'All Courses',
		'all_items'             => 'All Courses',
		'add_new_item'          => 'Add New Course',
		'add_new'               => 'Add New',
		'new_item'              => 'New Course',
		'new_item'              => 'New Course',
		'edit_item'             => 'Edit Course',
		'edit_item'             => 'Edit Course',
		'update_item'           => 'Update Course',
		'update_item'           => 'Update Course',
		'view_item'             => 'View Course',
		'search_items'          => 'Search Course',
		'search_items'          => 'Search Course',
		'not_found'             => 'Not found',
		'not_found_in_trash'    => 'Not found in Trash',
		'featured_image'        => 'Featured Image',
		'set_featured_image'    => 'Set featured image',
		'remove_featured_image' => 'Remove featured image',
		'use_featured_image'    => 'Use as featured image',
		'insert_into_item'      => 'Insert into Course',
		'uploaded_to_this_item'  => 'Uploaded to this Course',
		'items_list'            => 'Courses list',
		'items_list_navigation' => 'Courses list navigation',
		'filter_items_list'     => 'Filter Courses list',
	);

	$args = array(
		'label'                 => 'Course',
		'description'           => 'Educational products and training modules.',
		'labels'                => $labels,
		'supports'              => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ),
		'taxonomies'            => array( 'topic', 'course_level', 'product_type' ),
		'hierarchical'          => false,
		'public'                => true,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'menu_position'         => 8,
		'menu_icon'             => 'dashicons-welcome-learn-more',
		'show_in_admin_bar'     => true,
		'show_in_nav_menus'     => true,
		'can_export'            => true,
		'has_archive'          => true,
		'exclude_from_search'   => false,
		'publicly_queryable'    => true,
		'capability_type'       => 'post',
		'rewrite'               => array(
			'slug'       => 'course',
			'with_front' => false,
			'feeds'      => false,
		),
	);

	register_post_type( 'course', $args );
}
add_action( 'init', 'na_register_cpt_course', 10 );