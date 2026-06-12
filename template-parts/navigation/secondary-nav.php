<?php
/**
 * Secondary Navigation Template
 *
 * Displays utility navigation: FAQ, Backtests, Pricing, Methodology, Support, Login/Account.
 *
 * @package Astra Child
 * @since 1.0.0
 */

namespace NeuronAlgo\Theme\Navigation;
?>

<nav class="na-secondary-nav" aria-label="<?php esc_attr_e( 'Secondary navigation', 'astra-child' ); ?>">
	<div class="na-container">
		<?php
		wp_nav_menu( array(
			'theme_location'  => 'na-secondary',
			'container'       => false,
			'menu_class'      => 'na-secondary-nav__menu na-menu',
			'fallback_cb'     => false,
			'depth'           => 1,
		) );
		?>
	</div>
</nav>