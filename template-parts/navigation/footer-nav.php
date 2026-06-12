<?php
/**
 * Footer Navigation Template
 *
 * Displays footer navigation columns: About, Contact, FAQ, Terms, Privacy, Refund Policy, Risk Disclosure, Methodology, Support.
 *
 * @package Astra Child
 * @since 1.0.0
 */

namespace NeuronAlgo\Theme\Navigation;
?>

<footer class="na-footer-nav" role="contentinfo">
	<div class="na-container">
		<div class="na-footer-nav__columns">
			<?php
			// Column 1: About
			$about_pages = get_pages( array( 'child_of' => 0, 'parent' => 0, 'sort_column' => 'menu_order' ) );
			?>
			<div class="na-footer-nav__column">
				<h3 class="na-footer-nav__heading"><?php esc_html_e( 'About', 'astra-child' ); ?></h3>
				<ul class="na-footer-nav__list">
					<li><a href="<?php echo esc_url( home_url( '/about' ) ); ?>" class="na-footer-nav__link"><?php esc_html_e( 'Our Story', 'astra-child' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/methodology' ) ); ?>" class="na-footer-nav__link"><?php esc_html_e( 'Methodology', 'astra-child' ); ?></a></li>
				</ul>
			</div>

			<div class="na-footer-nav__column">
				<h3 class="na-footer-nav__heading"><?php esc_html_e( 'Products', 'astra-child' ); ?></h3>
				<?php
				wp_nav_menu( array(
					'theme_location' => 'na-footer',
					'container'      => false,
					'menu_class'     => 'na-footer-nav__list',
					'fallback_cb'    => false,
					'depth'          => 1,
					'link_before'    => '<span class="na-footer-nav__link">',
					'link_after'     => '</span>',
					'items_wrap'     => '<ul class="na-footer-nav__list">%3$s</ul>',
				) );
				?>
			</div>

			<div class="na-footer-nav__column">
				<h3 class="na-footer-nav__heading"><?php esc_html_e( 'Legal', 'astra-child' ); ?></h3>
				<ul class="na-footer-nav__list">
					<li><a href="<?php echo esc_url( home_url( '/terms' ) ); ?>" class="na-footer-nav__link"><?php esc_html_e( 'Terms', 'astra-child' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/privacy' ) ); ?>" class="na-footer-nav__link"><?php esc_html_e( 'Privacy', 'astra-child' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/refund-policy' ) ); ?>" class="na-footer-nav__link"><?php esc_html_e( 'Refund Policy', 'astra-child' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/risk-disclosure' ) ); ?>" class="na-footer-nav__link"><?php esc_html_e( 'Risk Disclosure', 'astra-child' ); ?></a></li>
				</ul>
			</div>

			<div class="na-footer-nav__column">
				<h3 class="na-footer-nav__heading"><?php esc_html_e( 'Support', 'astra-child' ); ?></h3>
				<ul class="na-footer-nav__list">
					<li><a href="<?php echo esc_url( home_url( '/faq' ) ); ?>" class="na-footer-nav__link"><?php esc_html_e( 'FAQ', 'astra-child' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/contact' ) ); ?>" class="na-footer-nav__link"><?php esc_html_e( 'Contact', 'astra-child' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/support' ) ); ?>" class="na-footer-nav__link"><?php esc_html_e( 'Support', 'astra-child' ); ?></a></li>
				</ul>
			</div>
		</div>

		<div class="na-footer-nav__cta-strip">
			<a href="<?php echo esc_url( home_url( '/pricing' ) ); ?>" class="na-btn na-btn--primary">
				<?php esc_html_e( 'Explore Our Strategies', 'astra-child' ); ?>
			</a>
		</div>
	</div>
</footer>