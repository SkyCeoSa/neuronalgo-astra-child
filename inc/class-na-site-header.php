<?php

namespace NeuronAlgo\Theme\Header;

use function add_action;
use function add_filter;
use function apply_filters;
use function filemtime;
use function get_stylesheet_directory;
use function get_stylesheet_directory_uri;
use function get_template_part;
use function home_url;
use function remove_action;
use function wp_enqueue_script;
use function wp_enqueue_style;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manages the custom site header functionality.
 */
class NA_Site_Header {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'after_setup_theme', array( $this, 'setup_header' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ), 20 );
	}

	/**
	 * Set up the custom header by removing Astra's default and adding our own.
	 */
	public function setup_header() {
		// Remove Astra's default header markup.
		if ( function_exists( 'astra_header_markup' ) ) {
			remove_action( 'astra_header', 'astra_header_markup' );
		}

		// Add our custom header renderer.
		add_action( 'astra_header', array( $this, 'render_header' ) );
	}

	/**
	 * Renders the custom site header partial.
	 */
	public function render_header() {
		get_template_part( 'template-parts/header/site-header' );
	}

	/**
	 * Enqueues the necessary styles and scripts for the site header.
	 */
	public function enqueue_assets() {
		$base_uri = get_stylesheet_directory_uri();
		$base_dir = get_stylesheet_directory();

		// Enqueue header styles.
		wp_enqueue_style(
			'na-site-header',
			$base_uri . '/assets/css/sections/site-header.css',
			array(),
			filemtime( $base_dir . '/assets/css/sections/site-header.css' )
		);

		// Enqueue header script.
		wp_enqueue_script(
			'na-site-header',
			$base_uri . '/assets/js/site-header.js',
			array(),
			filemtime( $base_dir . '/assets/js/site-header.js' ),
			true // In footer.
		);
	}

	/**
	 * Provides the filterable CTA label.
	 *
	 * @return string
	 */
	public static function get_cta_label(): string {
		return apply_filters( 'na_header_cta_label', __( 'Free Access', 'astra-child' ) );
	}

	/**
	 * Provides the filterable CTA URL.
	 *
	 * @return string
	 */
	public static function get_cta_url(): string {
		return apply_filters( 'na_header_cta_url', home_url( '/free-access' ) );
	}
}

// Instantiate the class.
new NA_Site_Header();
