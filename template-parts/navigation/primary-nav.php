<?php
/**
 * Primary Navigation Template
 *
 * Displays the main site navigation: Home, Strategies, Robots, Indicators, Courses, Community, Insights, About, Contact + CTA.
 *
 * @package Astra Child
 * @since 1.0.0
 */

namespace NeuronAlgo\Theme\Navigation;

$menu_args = array(
	'theme_location'  => 'na-primary',
	'container'       => 'nav',
	'container_class' => 'na-primary-nav',
	'container_id'    => 'na-primary-nav',
	'menu_class'      => 'na-primary-nav__menu na-menu',
	'fallback_cb'     => array( 'NeuronAlgo\Theme\Navigation\NA_Menus', 'fallback_menu' ),
	'walker'          => new NA_Menu_Walker(),
	'link_before'     => '<span class="na-menu__link-wrapper">',
	'link_after'      => '</span>',
);
?>

<nav id="na-primary-nav" class="na-primary-nav">
	<div class="na-primary-nav__brand">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="na-primary-nav__logo" aria-label="<?php esc_attr_e( 'NeuronAlgo Home', 'astra-child' ); ?>">
			<span class="na-primary-nav__logo-text">NeuronAlgo</span>
		</a>
	</div>

	<?php
	wp_nav_menu( $menu_args );
	?>

	<div class="na-primary-nav__cta">
		<a href="<?php echo esc_url( home_url( '/pricing' ) ); ?>" class="na-btn na-btn--primary na-btn--sm">
			<?php esc_html_e( 'View Pricing', 'astra-child' ); ?>
		</a>
	</div>

	<button class="na-primary-nav__toggle" aria-label="<?php esc_attr_e( 'Toggle navigation menu', 'astra-child' ); ?>" aria-expanded="false" aria-controls="na-mobile-menu">
		<span class="na-primary-nav__toggle-bar"></span>
		<span class="na-primary-nav__toggle-bar"></span>
		<span class="na-primary-nav__toggle-bar"></span>
	</button>
</nav>

<div id="na-mobile-menu" class="na-mobile-menu" aria-hidden="true">
	<ul class="na-mobile-menu__list">
		<?php
		wp_nav_menu( array(
			'theme_location'  => 'na-primary',
			'container'       => false,
			'menu_class'      => '',
			'fallback_cb'     => false,
			'walker'          => new NA_Menu_Walker(),
			'items_wrap'      => '%3$s',
			'link_before'     => '<span class="na-mobile-menu__link-wrapper">',
			'link_after'      => '</span>',
		) );
		?>
	</ul>
</div>