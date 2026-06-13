<?php
/**
 * Backtest Runs — archive template for the `backtest` CPT (FE-3.4).
 *
 * Institutional run-discovery grid using Astra chrome (header/footer) with the
 * DS-A tokens + DS-B components. Filtering is server-side via ?na_instrument /
 * ?na_timeframe / ?na_strategy query params (meta-based; see
 * inc/query/backtest-archive.php) plus ?na_sort ordering. Read-only
 * presentation; all business logic stays in neuronalgo-core.
 *
 * Mirrors the FE-3.1 strategy library terminal-flavored filter rail.
 *
 * @package astra-child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

get_header();

$na_archive_link = get_post_type_archive_link( 'backtest' );
$na_total        = isset( $GLOBALS['wp_query']->found_posts ) ? (int) $GLOBALS['wp_query']->found_posts : 0;

$na_instrument_opts = function_exists( 'na_backtest_distinct_meta' ) ? na_backtest_distinct_meta( 'instrument_meta_field' ) : array();
$na_timeframe_opts  = function_exists( 'na_backtest_distinct_meta' ) ? na_backtest_distinct_meta( 'time_frame_meta_field' ) : array();
$na_strategy_opts   = function_exists( 'na_backtest_strategy_options' ) ? na_backtest_strategy_options() : array();

$na_cur_instrument = isset( $_GET['na_instrument'] ) ? sanitize_text_field( wp_unslash( $_GET['na_instrument'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$na_cur_timeframe  = isset( $_GET['na_timeframe'] ) ? sanitize_text_field( wp_unslash( $_GET['na_timeframe'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$na_cur_strategy   = isset( $_GET['na_strategy'] ) ? absint( wp_unslash( $_GET['na_strategy'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$na_cur_sort       = isset( $_GET['na_sort'] ) ? sanitize_key( wp_unslash( $_GET['na_sort'] ) ) : 'newest'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

$na_sort_labels = array(
	'newest' => 'Newest',
	'net'    => 'Net P/L',
	'win'    => 'Win rate',
	'pf'     => 'Profit factor',
	'dd'     => 'Max drawdown',
);
if ( ! isset( $na_sort_labels[ $na_cur_sort ] ) ) {
	$na_cur_sort = 'newest';
}

$na_active_count = 0;
if ( '' !== $na_cur_instrument ) {
	$na_active_count++;
}
if ( '' !== $na_cur_timeframe ) {
	$na_active_count++;
}
if ( $na_cur_strategy ) {
	$na_active_count++;
}
?>
<main id="primary" class="na-backtest-archive">
	<section class="na-bta-intro">
		<div class="na-bta-intro-inner">
			<span class="na-eyebrow">BACKTEST RUNS</span>
			<h1 class="na-bta-title">Every run, fully audited</h1>
			<p class="na-bta-lead">Browse every backtest run across the desk&rsquo;s strategies. Each run is computed over 15+ years of data &mdash; filter by instrument and timeframe, then open the full equity curve, drawdown and 40+ stats.</p>
		</div>
	</section>

	<div class="na-bta-layout">
		<aside class="na-bta-filters" aria-label="Filter runs">
			<div class="na-bta-filters-head">
				<span class="na-bta-filters-prompt"><span class="na-bta-filters-tilde">~</span>/runs <span class="na-bta-filters-cmd">$ ./filter</span><span class="na-bta-caret" aria-hidden="true"></span></span>
				<span class="na-bta-filters-count"><span class="na-bta-filters-dot" aria-hidden="true"></span><?php echo esc_html( number_format_i18n( $na_active_count ) ); ?> active</span>
			</div>
			<form class="na-bta-filter-form" method="get" action="<?php echo esc_url( $na_archive_link ); ?>">
				<div class="na-bta-fgroup<?php echo '' !== $na_cur_instrument ? ' is-active' : ''; ?>">
					<label class="na-bta-flabel na-micro" for="na_instrument">Instrument</label>
					<select class="na-bta-select" id="na_instrument" name="na_instrument">
						<option value=""><?php esc_html_e( 'All', 'astra-child' ); ?></option>
						<?php foreach ( $na_instrument_opts as $na_opt ) : ?>
							<option value="<?php echo esc_attr( $na_opt ); ?>" <?php selected( $na_cur_instrument, $na_opt ); ?>><?php echo esc_html( $na_opt ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="na-bta-fgroup<?php echo '' !== $na_cur_timeframe ? ' is-active' : ''; ?>">
					<label class="na-bta-flabel na-micro" for="na_timeframe">Timeframe</label>
					<select class="na-bta-select" id="na_timeframe" name="na_timeframe">
						<option value=""><?php esc_html_e( 'All', 'astra-child' ); ?></option>
						<?php foreach ( $na_timeframe_opts as $na_opt ) : ?>
							<option value="<?php echo esc_attr( $na_opt ); ?>" <?php selected( $na_cur_timeframe, $na_opt ); ?>><?php echo esc_html( $na_opt ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<?php if ( ! empty( $na_strategy_opts ) ) : ?>
					<div class="na-bta-fgroup<?php echo $na_cur_strategy ? ' is-active' : ''; ?>">
						<label class="na-bta-flabel na-micro" for="na_strategy">Strategy</label>
						<select class="na-bta-select" id="na_strategy" name="na_strategy">
							<option value=""><?php esc_html_e( 'All', 'astra-child' ); ?></option>
							<?php foreach ( $na_strategy_opts as $na_sid => $na_sname ) : ?>
								<option value="<?php echo esc_attr( (string) $na_sid ); ?>" <?php selected( $na_cur_strategy, (int) $na_sid ); ?>><?php echo esc_html( $na_sname ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				<?php endif; ?>
				<div class="na-bta-fgroup">
					<label class="na-bta-flabel na-micro" for="na_sort">Sort by</label>
					<select class="na-bta-select" id="na_sort" name="na_sort">
						<?php foreach ( $na_sort_labels as $na_sval => $na_slabel ) : ?>
							<option value="<?php echo esc_attr( $na_sval ); ?>" <?php selected( $na_cur_sort, $na_sval ); ?>><?php echo esc_html( $na_slabel ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="na-bta-actions">
					<button type="submit" class="na-btn na-btn-primary na-bta-apply">Apply</button>
					<a class="na-btn na-btn-ghost na-bta-reset" href="<?php echo esc_url( $na_archive_link ); ?>">Reset</a>
				</div>
			</form>
		</aside>

		<div class="na-bta-main">
			<div class="na-bta-toolbar">
				<span class="na-bta-count na-tab"><b><?php echo esc_html( number_format_i18n( $na_total ) ); ?></b> <?php echo esc_html( _n( 'run', 'runs', $na_total, 'astra-child' ) ); ?></span>
				<span class="na-bta-sortline">sort: <span class="na-bta-sv"><?php echo esc_html( strtolower( $na_sort_labels[ $na_cur_sort ] ) ); ?></span></span>
			</div>

			<?php if ( have_posts() ) : ?>
				<div class="na-bta-grid">
					<?php
					while ( have_posts() ) :
						the_post();
						get_template_part( 'template-parts/cards/backtest-card' );
					endwhile;
					?>
				</div>

				<nav class="na-bta-pagination" aria-label="Backtest run pages">
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
				<div class="na-card na-bta-empty">
					<h2 class="na-bta-empty-title">No runs match these filters</h2>
					<p class="na-bta-empty-text">Try widening your selection, or reset the filters to see every backtest run.</p>
					<a class="na-btn na-btn-primary" href="<?php echo esc_url( $na_archive_link ); ?>">Reset filters</a>
				</div>
			<?php endif; ?>
		</div>
	</div>
</main>
<?php
get_footer();
