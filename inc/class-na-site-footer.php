<?php
namespace NeuronAlgo\Theme\Footer;

use function add_action;
use function apply_filters;
use function filemtime;
use function get_stylesheet_directory;
use function get_stylesheet_directory_uri;
use function get_template_part;
use function home_url;
use function remove_all_actions;
use function wp_enqueue_style;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manages the custom site footer functionality.
 */
class NA_Site_Footer {

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Replace Astra\'s footer on `wp` (not after_setup_theme): by the time the
		// front-end query is set up, every default/footer-builder callback hooked
		// to `astra_footer` is registered, so we can reliably strip them all.
		add_action( 'wp', array( $this, 'setup_footer' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ), 20 );
	}

	/**
	 * Remove every default Astra footer output (classic markup AND footer
	 * builder), then register only our custom footer renderer.
	 */
	public function setup_footer() {
		remove_all_actions( 'astra_footer' );
		add_action( 'astra_footer', array( $this, 'render_footer' ) );
	}

	/**
	 * Renders the custom site footer partial.
	 */
	public function render_footer() {
		get_template_part( 'template-parts/footer/site-footer' );
	}

	/**
	 * Enqueues the necessary styles for the site footer.
	 */
	public function enqueue_assets() {
		$base_uri = get_stylesheet_directory_uri();
		$base_dir = get_stylesheet_directory();

		wp_enqueue_style(
			'na-site-footer',
			$base_uri . '/assets/css/sections/site-footer.css',
			array(),
			filemtime( $base_dir . '/assets/css/sections/site-footer.css' )
		);
	}

	/**
	 * Provides the filterable CTA label.
	 *
	 * @return string
	 */
	public static function get_cta_label(): string {
		return apply_filters( 'na_footer_cta_label', __( 'Free Access', 'astra-child' ) );
	}

	/**
	 * Provides the filterable CTA URL.
	 *
	 * @return string
	 */
	public static function get_cta_url(): string {
		return apply_filters( 'na_footer_cta_url', home_url( '/free-access' ) );
	}
}

// Instantiate the class.
new NA_Site_Footer();
