<?php
/**
 * Astra Child Theme functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Astra Child
 * @since 1.0.0
 */

/**
 * Define Constants
 */
define( 'CHILD_THEME_ASTRA_CHILD_VERSION', '1.0.0' );

/**
 * Enqueue styles
 */
function child_theme_astra_child_enqueue_styles() {

	wp_enqueue_style( 'astra-theme-css', get_template_directory_uri() . '/style.css', array(), CHILD_THEME_ASTRA_CHILD_VERSION );

	// NeuronAlgo Design Tokens - Must load before main stylesheet
	wp_enqueue_style( 'na-design-tokens', get_stylesheet_directory_uri() . '/assets/css/base/variables.css', array( 'astra-theme-css' ), CHILD_THEME_ASTRA_CHILD_VERSION, 'all' );

	// NeuronAlgo Base Styles - Registered but NOT enqueued globally
	wp_register_style( 'na-reset', get_stylesheet_directory_uri() . '/assets/css/base/reset.css', array(), CHILD_THEME_ASTRA_CHILD_VERSION, 'all' );
	wp_register_style( 'na-typography', get_stylesheet_directory_uri() . '/assets/css/base/typography.css', array( 'na-design-tokens' ), CHILD_THEME_ASTRA_CHILD_VERSION, 'all' );
	// wp_register_style( 'na-utilities', get_stylesheet_directory_uri() . '/assets/css/base/utilities.css', array( 'na-design-tokens' ), CHILD_THEME_ASTRA_CHILD_VERSION, 'all' );
	wp_enqueue_style( 'na-utilities', get_stylesheet_directory_uri() . '/assets/css/base/utilities.css', array( 'na-design-tokens' ), CHILD_THEME_ASTRA_CHILD_VERSION, 'all' );

	// NeuronAlgo Components - Must load after design tokens
	wp_enqueue_style( 'na-components', get_stylesheet_directory_uri() . '/assets/css/components/index.css', array( 'na-design-tokens' ), CHILD_THEME_ASTRA_CHILD_VERSION, 'all' );

	// NeuronAlgo Navigation Styles - Must load after core base styles and components (if they are enqueued)
	// wp_enqueue_style( 'na-navigation', get_stylesheet_directory_uri() . '/assets/css/navigation.css', array( 'na-typography', 'na-utilities' ), CHILD_THEME_ASTRA_CHILD_VERSION, 'all' );

	wp_enqueue_style( 'astra-child-css', get_stylesheet_directory_uri() . '/style.css', array(), CHILD_THEME_ASTRA_CHILD_VERSION, 'all' );

}

/**
 * Include navigation menu class.
 */
require_once get_stylesheet_directory() . '/inc/class-na-menus.php';

/**
 * Include custom site header class.
 */
require_once get_stylesheet_directory() . '/inc/class-na-site-header.php';

/**
 * Include internal linking framework.
 */
require_once get_stylesheet_directory() . '/inc/class-na-internal-links.php';

/**
 * Include conditional assets registration.
 */
require_once get_stylesheet_directory() . '/inc/enqueue/class-conditional-assets.php';

/**
 * Include Strategy Library query layer + helpers (FE-3.1).
 */
require_once get_stylesheet_directory() . '/inc/query/strategy-archive.php';

/**
 * Include Elementor widgets.
 */
require_once get_stylesheet_directory() . '/inc/integrations/elementor/class-backtest-chart-widget.php';

/**
 * Include Custom Post Type registrations.
 */
require_once get_stylesheet_directory() . '/inc/cpt/strategy.php';
require_once get_stylesheet_directory() . '/inc/cpt/robot.php';
require_once get_stylesheet_directory() . '/inc/cpt/indicator.php';
require_once get_stylesheet_directory() . '/inc/cpt/course.php';
require_once get_stylesheet_directory() . '/inc/cpt/backtest.php';
require_once get_stylesheet_directory() . '/inc/cpt/report.php';
require_once get_stylesheet_directory() . '/inc/cpt/resource.php';
require_once get_stylesheet_directory() . '/inc/cpt/glossary.php';
require_once get_stylesheet_directory() . '/inc/cpt/testimonial.php';
require_once get_stylesheet_directory() . '/inc/cpt/event.php';

add_action( 'wp_enqueue_scripts', 'child_theme_astra_child_enqueue_styles', 15 );

/**
 * Enqueue scripts
 */
function child_theme_astra_child_enqueue_scripts() {

	// FAQ Accordion functionality
	wp_enqueue_script( 'na-faq-accordion', get_stylesheet_directory_uri() . '/assets/js/faq-accordion.js', array(), CHILD_THEME_ASTRA_CHILD_VERSION, true );

}

add_action( 'wp_enqueue_scripts', 'child_theme_astra_child_enqueue_scripts', 20 );
