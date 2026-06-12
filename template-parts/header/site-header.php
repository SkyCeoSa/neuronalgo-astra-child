<?php
/**
 * Custom site header markup (HF-1.3 — B3 “Command Deck”: centered ⌘K search).
 *
 * @package NeuronAlgo
 */

namespace NeuronAlgo\Theme\Header;

use function esc_attr;
use function esc_attr__;
use function esc_html;
use function esc_html__;
use function esc_url;
use function get_stylesheet_directory_uri;
use function home_url;
use function wp_nav_menu;

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

$cta_label = NA_Site_Header::get_cta_label();
$cta_url   = NA_Site_Header::get_cta_url();
?>
<header class="na-site-header" id="na-site-header" data-na-header>
	<div class="na-site-header__inner">
		<a class="na-site-header__brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr__( 'NeuronAlgo — Home', 'astra-child' ); ?>">
			<img class="na-site-header__mark" src="<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/img/brand/neuronalgo-mark.svg' ); ?>" alt="" width="32" height="32" aria-hidden="true">
			<span class="na-site-header__wordmark"><?php echo esc_html__( 'NeuronAlgo', 'astra-child' ); ?></span>
		</a>

		<button class="na-cmdk-search na-cmdk-trigger" type="button" data-na-cmdk-open aria-haspopup="dialog" aria-controls="na-cmdk" aria-label="<?php echo esc_attr__( 'Open command menu', 'astra-child' ); ?>">
			<span class="na-cmdk-search__ico" aria-hidden="true">⌕</span>
			<span class="na-cmdk-search__ph"><?php echo esc_html__( 'Search the desk…', 'astra-child' ); ?></span>
			<span class="na-cmdk-search__caret" aria-hidden="true"></span>
			<span class="na-cmdk-search__sp"></span>
			<kbd class="na-kbd">⌘K</kbd>
		</button>

		<nav class="na-site-header__nav" aria-label="<?php echo esc_attr__( 'Primary', 'astra-child' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'na-primary',
					'container'      => false,
					'menu_class'     => 'na-nav__menu',
					'fallback_cb'    => false,
					'depth'          => 2,
				)
			);
			?>
		</nav>

		<div class="na-site-header__actions">
			<a class="na-btn na-btn--primary na-btn--sm na-site-header__cta" href="<?php echo esc_url( $cta_url ); ?>"><?php echo esc_html( $cta_label ); ?></a>
			<button class="na-site-header__toggle" type="button" aria-label="<?php echo esc_attr__( 'Open menu', 'astra-child' ); ?>" aria-expanded="false" aria-controls="na-site-header-mobile">
				<span></span><span></span><span></span>
			</button>
		</div>
	</div>

	<div class="na-site-header__mobile" id="na-site-header-mobile" hidden>
		<button class="na-cmdk-search na-cmdk-search--mobile na-cmdk-trigger" type="button" data-na-cmdk-open aria-haspopup="dialog" aria-controls="na-cmdk">
			<span class="na-cmdk-search__ico" aria-hidden="true">⌕</span>
			<span class="na-cmdk-search__ph"><?php echo esc_html__( 'Search the desk…', 'astra-child' ); ?></span>
			<span class="na-cmdk-search__sp"></span>
			<kbd class="na-kbd">⌘K</kbd>
		</button>
		<?php
		wp_nav_menu(
			array(
				'theme_location' => 'na-primary',
				'container'      => false,
				'menu_class'     => 'na-nav__mobile-menu',
				'fallback_cb'    => false,
				'depth'          => 2,
			)
		);
		?>
		<a class="na-btn na-btn--primary na-btn--block" href="<?php echo esc_url( $cta_url ); ?>"><?php echo esc_html( $cta_label ); ?></a>
	</div>
</header>

<div class="na-cmdk" id="na-cmdk" hidden aria-hidden="true" data-cta-label="<?php echo esc_attr( $cta_label ); ?>" data-cta-url="<?php echo esc_url( $cta_url ); ?>">
	<div class="na-cmdk__backdrop" data-na-cmdk-close></div>
	<div class="na-cmdk__panel" role="dialog" aria-modal="true" aria-label="<?php echo esc_attr__( 'Command menu', 'astra-child' ); ?>">
		<div class="na-cmdk__input-row">
			<span class="na-cmdk__prompt" aria-hidden="true">~</span>
			<input class="na-cmdk__input" type="text" autocomplete="off" spellcheck="false" placeholder="<?php echo esc_attr__( 'Search the desk — strategies, signals, docs…', 'astra-child' ); ?>" aria-label="<?php echo esc_attr__( 'Search', 'astra-child' ); ?>">
			<span class="na-cmdk__esc" aria-hidden="true">ESC</span>
		</div>
		<ul class="na-cmdk__list" role="listbox"></ul>
		<div class="na-cmdk__footer" aria-hidden="true">
			<span><kbd>↑</kbd><kbd>↓</kbd> navigate</span>
			<span><kbd>↵</kbd> open</span>
			<span><kbd>esc</kbd> close</span>
		</div>
	</div>
</div>
