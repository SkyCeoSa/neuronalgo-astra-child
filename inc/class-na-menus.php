<?php
/**
 * NeuronAlgo Theme Navigation Menus
 *
 * Registers navigation menu locations for the theme.
 *
 * @package Astra Child
 * @since 1.0.0
 */

namespace NeuronAlgo\Theme\Navigation;

/**
 * Class NA_Menus
 *
 * Registers and manages navigation menu locations.
 */
class NA_Menus {

	/**
	 * Initialize menu registration.
	 */
	public function init() {
		add_action( 'after_setup_theme', array( $this, 'register_menus' ) );
	}

	/**
	 * Register navigation menu locations.
	 */
	public function register_menus() {
		register_nav_menus( array(
			'na-primary'   => esc_html__( 'Primary Navigation', 'astra-child' ),
			'na-secondary' => esc_html__( 'Secondary Navigation', 'astra-child' ),
			'na-footer'    => esc_html__( 'Footer Navigation', 'astra-child' ),
		) );
	}

	/**
	 * Render a navigation menu with fallback.
	 *
	 * @param string $location Menu location slug.
	 * @param array  $args     wp_nav_menu arguments.
	 * @return void
	 */
	public static function render_menu( $location, $args = array() ) {
		$defaults = array(
			'theme_location' => $location,
			'container'      => 'nav',
			'container_class' => 'na-' . $location . '-nav',
			'container_id'   => 'na-' . $location . '-nav',
			'menu_class'     => 'na-menu',
			'fallback_cb'    => array( __CLASS__, 'fallback_menu' ),
			'walker'         => new NA_Menu_Walker(),
		);

		$args = wp_parse_args( $args, $defaults );

		wp_nav_menu( $args );
	}

	/**
	 * Fallback menu when no menu is assigned.
	 *
	 * @param array $args wp_nav_menu arguments.
	 * @return void
	 */
	public static function fallback_menu( $args ) {
		if ( ! current_user_can( 'edit_theme_options' ) ) {
			return;
		}

		echo '<div class="na-menu-fallback">';
		esc_html_e( 'Assign a menu in Appearance > Menus.', 'astra-child' );
		echo '</div>';
	}
}

/**
 * Walker class for custom menu markup.
 */
class NA_Menu_Walker extends \Walker_Nav_Menu {

	/**
	 * Start each element.
	 */
	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		$classes = empty( $item->classes ) ? array() : (array) $item->classes;
		$classes[] = 'na-menu__item';

		$output .= '<li class="' . esc_attr( implode( ' ', $classes ) ) . '">';

		$output .= '<a href="' . esc_url( $item->url ) . '" class="na-menu__link">';
		$output .= apply_filters( 'the_title', $item->title, $item->ID );
		$output .= '</a>';
	}
}

// Initialize on theme load.
$na_menus = new NA_Menus();
$na_menus->init();