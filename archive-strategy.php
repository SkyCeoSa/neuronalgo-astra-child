<?php
/**
 * Strategy Library — archive template for the `strategy` CPT (FE-3.1).
 *
 * Institutional discovery grid using Astra chrome (header/footer) with the
 * DS-A tokens + DS-B components. Filtering is server-side via ?na_<taxonomy>
 * query params (see inc/query/strategy-archive.php). Read-only presentation;
 * all business logic stays in neuronalgo-core.
 *
 * FE-3.1 polish: terminal-flavored filter rail (console header, mono
 * `>`-prefixed labels, active-state highlight) to match the C2 site footer.
 *
 * @package astra-child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

get_header();

$na_archive_link = get_post_type_archive_link( 'strategy' );
$na_total        = isset( $GLOBALS['wp_query']->found_posts ) ? (int) $GLOBALS['wp_query']->found_posts : 0;

$na_filter_taxonomies = array(
	'na_market'        => array(
		'tax'   => 'market',
		'label' => 'Market',
	),
	'na_asset_class'   => array(
		'tax'   => 'asset_class',
		'label' => 'Asset class',
	),
	'na_timeframe'     => array(
		'tax'   => 'timeframe',
		'label' => 'Timeframe',
	),
	'na_strategy_type' => array(
		'tax'   => 'strategy_type',
		'label' => 'Type',
	),
	'na_risk_level'    => array(
		'tax'   => 'risk_level',
		'label' => 'Risk',
	),
);

// Count active filters for the console header (terminal-flavored rail).
$na_active_count = 0;
foreach ( $na_filter_taxonomies as $na_param => $na_cfg ) {
	if ( ! empty( $_GET[ $na_param ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$na_active_count++;
	}
}
?>
<main id="primary" class="na-strategy-archive">
	<section class="na-sl-intro">
		<div class="na-sl-intro-inner">
			<span class="na-eyebrow">STRATEGY LIBRARY</span>
			<h1 class="na-sl-title">Browse the desk&rsquo;s strategies</h1>
			<p class="na-sl-lead">Every strategy is backtested over 15+ years and tracked live. Filter by market, timeframe and risk &mdash; then inspect the full equity curve and drawdown.</p>
		</div>
	</section>

	<div class="na-sl-layout">
		<aside class="na-sl-filters" aria-label="Filter strategies">
			<div class="na-sl-filters-head">
				<span class="na-sl-filters-prompt"><span class="na-sl-filters-tilde">~</span>/library <span class="na-sl-filters-cmd">$ ./filter</span><span class="na-sl-caret" aria-hidden="true"></span></span>
				<span class="na-sl-filters-count"><span class="na-sl-filters-dot" aria-hidden="true"></span><?php echo esc_html( number_format_i18n( $na_active_count ) ); ?> active</span>
			</div>
			<form class="na-sl-filter-form" method="get" action="<?php echo esc_url( $na_archive_link ); ?>">
				<?php
				foreach ( $na_filter_taxonomies as $na_param => $na_cfg ) :
					$na_tax = $na_cfg['tax'];
					if ( ! taxonomy_exists( $na_tax ) ) {
						continue;
					}
					$na_terms = get_terms(
						array(
							'taxonomy'   => $na_tax,
							'hide_empty' => true,
						)
					);
					if ( is_wp_error( $na_terms ) || empty( $na_terms ) ) {
						continue;
					}
					$na_current = isset( $_GET[ $na_param ] ) ? sanitize_title( wp_unslash( $_GET[ $na_param ] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					?>
					<div class="na-sl-filter-group<?php echo $na_current ? ' is-active' : ''; ?>">
						<label class="na-sl-filter-label na-micro" for="<?php echo esc_attr( $na_param ); ?>"><?php echo esc_html( $na_cfg['label'] ); ?></label>
						<select class="na-sl-select" id="<?php echo esc_attr( $na_param ); ?>" name="<?php echo esc_attr( $na_param ); ?>">
							<option value=""><?php esc_html_e( 'All', 'astra-child' ); ?></option>
							<?php foreach ( $na_terms as $na_term ) : ?>
								<option value="<?php echo esc_attr( $na_term->slug ); ?>" <?php selected( $na_current, $na_term->slug ); ?>><?php echo esc_html( $na_term->name ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				<?php endforeach; ?>
				<div class="na-sl-filter-actions">
					<button type="submit" class="na-btn na-btn-primary na-sl-apply">Apply filters</button>
					<a class="na-btn na-btn-ghost na-sl-reset" href="<?php echo esc_url( $na_archive_link ); ?>">Reset</a>
				</div>
			</form>
		</aside>

		<div class="na-sl-main">
			<div class="na-sl-toolbar">
				<span class="na-sl-count na-tab"><?php echo esc_html( number_format_i18n( $na_total ) . ' ' . _n( 'strategy', 'strategies', $na_total, 'astra-child' ) ); ?></span>
			</div>

			<?php if ( have_posts() ) : ?>
				<div class="na-sl-grid">
					<?php
					while ( have_posts() ) :
						the_post();
						get_template_part( 'template-parts/cards/strategy-card' );
					endwhile;
					?>
				</div>

				<nav class="na-sl-pagination" aria-label="Strategy pages">
					<?php
					echo wp_kses_post(
						paginate_links(
							array(
								'mid_size'  => 1,
								'prev_text' => '‹ Prev',
								'next_text' => 'Next ›',
							)
						)
					);
					?>
				</nav>
			<?php else : ?>
				<div class="na-card na-sl-empty">
					<h2 class="na-sl-empty-title">No strategies match these filters</h2>
					<p class="na-sl-empty-text">Try widening your selection, or reset the filters to see the full library.</p>
					<a class="na-btn na-btn-primary" href="<?php echo esc_url( $na_archive_link ); ?>">Reset filters</a>
				</div>
			<?php endif; ?>
		</div>
	</div>
</main>
<?php
get_footer();
