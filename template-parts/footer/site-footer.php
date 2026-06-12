<?php
/**
 * NeuronAlgo custom site footer.
 *
 * Design: C2 — terminal status bar with an inline single CTA, mono link
 * columns, risk disclaimer, and a bottom legal/region bar.
 *
 * @package Astra Child
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$na_ft_cta_label = \NeuronAlgo\Theme\Footer\NA_Site_Footer::get_cta_label();
$na_ft_cta_url   = \NeuronAlgo\Theme\Footer\NA_Site_Footer::get_cta_url();

/*
 * Build link columns from the `na-footer` menu:
 *   top-level items -> column titles
 *   their sub-items -> column links
 * Falls back to sensible defaults when no footer menu is assigned so the
 * footer never renders empty.
 */
$na_ft_columns = array();
$na_ft_items   = array();
$na_ft_locs    = get_nav_menu_locations();

if ( ! empty( $na_ft_locs['na-footer'] ) ) {
	$na_ft_menu = wp_get_nav_menu_object( $na_ft_locs['na-footer'] );
	if ( $na_ft_menu ) {
		$na_ft_items = wp_get_nav_menu_items( $na_ft_menu->term_id );
	}
}

if ( ! empty( $na_ft_items ) ) {
	$na_ft_tops = array();
	foreach ( $na_ft_items as $na_ft_item ) {
		if ( 0 === (int) $na_ft_item->menu_item_parent ) {
			$na_ft_tops[ $na_ft_item->ID ] = array(
				'title' => $na_ft_item->title,
				'links' => array(),
			);
		}
	}
	foreach ( $na_ft_items as $na_ft_item ) {
		$na_ft_parent = (int) $na_ft_item->menu_item_parent;
		if ( $na_ft_parent && isset( $na_ft_tops[ $na_ft_parent ] ) ) {
			$na_ft_tops[ $na_ft_parent ]['links'][] = array(
				'title' => $na_ft_item->title,
				'url'   => $na_ft_item->url,
			);
		}
	}
	$na_ft_columns = array_values( $na_ft_tops );
}

if ( empty( $na_ft_columns ) ) {
	$na_ft_columns = array(
		array(
			'title' => __( 'Product', 'astra-child' ),
			'links' => array(
				array( 'title' => 'strategies', 'url' => home_url( '/strategy/' ) ),
				array( 'title' => 'robots', 'url' => home_url( '/robot/' ) ),
				array( 'title' => 'indicators', 'url' => home_url( '/indicator/' ) ),
				array( 'title' => 'backtests', 'url' => home_url( '/backtest/' ) ),
			),
		),
		array(
			'title' => __( 'Learn', 'astra-child' ),
			'links' => array(
				array( 'title' => 'courses', 'url' => home_url( '/course/' ) ),
				array( 'title' => 'glossary', 'url' => home_url( '/glossary/' ) ),
				array( 'title' => 'resources', 'url' => home_url( '/resource/' ) ),
			),
		),
		array(
			'title' => __( 'Company', 'astra-child' ),
			'links' => array(
				array( 'title' => 'about', 'url' => home_url( '/about/' ) ),
				array( 'title' => 'pricing', 'url' => home_url( '/pricing/' ) ),
				array( 'title' => 'contact', 'url' => home_url( '/contact/' ) ),
			),
		),
	);
}

$na_ft_year = gmdate( 'Y' );
?>
<footer class="na-site-footer" role="contentinfo">
	<div class="na-ft-inner">

		<div class="na-ft-promptbar">
			<span class="na-ft-pfx">~/neuronalgo</span>
			<span class="na-ft-cmd">$ ./desk --status</span>
			<span class="na-ft-caret" aria-hidden="true"></span>
			<span class="na-ft-spacer"></span>
			<span class="na-ft-status"><span class="na-ft-dot" aria-hidden="true"></span><?php esc_html_e( 'operational · 99.98% uptime', 'astra-child' ); ?></span>
			<a class="na-ft-btn na-ft-btn--primary" href="<?php echo esc_url( $na_ft_cta_url ); ?>"><?php echo esc_html( $na_ft_cta_label ); ?></a>
		</div>

		<div class="na-ft-grid">
			<div class="na-ft-brand">
				<div class="na-ft-brandrow">
					<svg class="na-ft-mark" viewBox="0 0 140 140" aria-hidden="true">
						<defs><linearGradient id="naFtGrad" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#3d7dff"/><stop offset="1" stop-color="#38bdf8"/></linearGradient></defs>
						<rect x="3" y="3" width="134" height="134" rx="32" fill="url(#naFtGrad)" stroke="#2a3550" stroke-width="2"/>
						<path d="M44 92 L44 60 L86 92 L86 44" fill="none" stroke="#08121f" stroke-width="14" stroke-linecap="round" stroke-linejoin="round"/>
						<polygon points="86,27 74,46 98,46" fill="#08121f"/>
					</svg>
					<span class="na-ft-wm">Neuron<span class="ac">Algo</span></span>
					<span class="na-ft-live"><span class="d" aria-hidden="true"></span>LIVE</span>
				</div>
				<p class="na-ft-tag"><?php esc_html_e( 'Live, transparent, relentless. Every strategy is backtested over 15+ years and tracked in the open.', 'astra-child' ); ?></p>
				<div class="na-ft-social">
					<a href="#" aria-label="X (Twitter)"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18.9 2H22l-7.2 8.3L23 22h-6.6l-5.2-6.8L5.3 22H2.2l7.7-8.8L1.5 2h6.8l4.7 6.2L18.9 2Zm-1.2 18h1.7L7.3 3.8H5.5L17.7 20Z"/></svg></a>
					<a href="#" aria-label="YouTube"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M23 7.5a3 3 0 0 0-2.1-2.1C19 4.8 12 4.8 12 4.8s-7 0-8.9.6A3 3 0 0 0 1 7.5 31 31 0 0 0 .5 12 31 31 0 0 0 1 16.5a3 3 0 0 0 2.1 2.1c1.9.6 8.9.6 8.9.6s7 0 8.9-.6a3 3 0 0 0 2.1-2.1A31 31 0 0 0 23.5 12 31 31 0 0 0 23 7.5ZM9.8 15.3V8.7l5.7 3.3-5.7 3.3Z"/></svg></a>
					<a href="#" aria-label="GitHub"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 1.5a10.5 10.5 0 0 0-3.3 20.5c.5.1.7-.2.7-.5v-1.9c-2.9.6-3.5-1.3-3.5-1.3-.5-1.2-1.2-1.5-1.2-1.5-.9-.6.1-.6.1-.6 1 .1 1.6 1 1.6 1 .9 1.6 2.4 1.1 3 .9.1-.7.4-1.1.7-1.4-2.3-.3-4.7-1.1-4.7-5a4 4 0 0 1 1-2.7c-.1-.3-.5-1.3.1-2.7 0 0 .9-.3 2.8 1a9.6 9.6 0 0 1 5 0c1.9-1.3 2.8-1 2.8-1 .6 1.4.2 2.4.1 2.7a4 4 0 0 1 1 2.7c0 3.9-2.4 4.7-4.7 5 .4.3.7.9.7 1.9v2.8c0 .3.2.6.7.5A10.5 10.5 0 0 0 12 1.5Z"/></svg></a>
				</div>
			</div>

			<?php foreach ( $na_ft_columns as $na_ft_col ) : ?>
				<div class="na-ft-col">
					<div class="na-ft-coltitle"><?php echo esc_html( $na_ft_col['title'] ); ?></div>
					<div class="na-ft-links">
						<?php foreach ( $na_ft_col['links'] as $na_ft_link ) : ?>
							<a href="<?php echo esc_url( $na_ft_link['url'] ); ?>"><?php echo esc_html( $na_ft_link['title'] ); ?></a>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<div class="na-ft-riskwrap">
			<p class="na-ft-risk"><?php esc_html_e( 'Trading financial instruments carries a high level of risk and may not be suitable for all investors. Past performance — including backtested and simulated results — is not indicative of future results. NeuronAlgo provides research and tooling, not financial advice.', 'astra-child' ); ?></p>
		</div>

		<div class="na-ft-bottom">
			<div class="na-ft-bl">
				<span class="na-ft-cc">&copy; <?php echo esc_html( $na_ft_year ); ?> NeuronAlgo</span>
				<span class="na-ft-build">v1.3.0</span>
			</div>
			<div class="na-ft-bl">
				<div class="na-ft-legal">
					<a href="<?php echo esc_url( home_url( '/terms/' ) ); ?>">terms</a>
					<a href="<?php echo esc_url( home_url( '/privacy/' ) ); ?>">privacy</a>
					<a href="<?php echo esc_url( home_url( '/risk-disclosure/' ) ); ?>">risk_disclosure</a>
				</div>
				<span class="na-ft-region">&#127760; EN &middot; USD</span>
			</div>
		</div>

	</div>
</footer>
