<?php
/**
 * Backtest archive card (FE-3.4).
 *
 * Renders inside the backtest archive loop (expects the_post() context).
 * Surfaces the run's headline metrics (Net P/L, Profit Factor, CAGR, Max DD,
 * Win rate) plus a tiny inline-SVG equity sparkline. Percent metrics are
 * stored as whole numbers (e.g. 11.57 => 11.57%), so they are NOT multiplied.
 * Read-only presentation; relies on DS-B components (.na-card, .na-btn).
 *
 * @package astra-child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$na_id        = get_the_ID();
$na_permalink = get_permalink();

$na_meta = function ( $key ) use ( $na_id ) {
	$v = get_post_meta( $na_id, $key, true );
	return ( null === $v ) ? '' : $v;
};

$na_inst   = $na_meta( 'instrument_meta_field' );
$na_tf     = $na_meta( 'time_frame_meta_field' );
$na_trades = $na_meta( 'number_of_trades_meta_field' );
$na_start  = $na_meta( 'backtest_period_start_meta_field' );
$na_end    = $na_meta( 'backtest_period_end_meta_field' );

$na_net  = $na_meta( 'total_profit_meta_field' );
$na_pf   = $na_meta( 'profit_factor_meta_field' );
$na_cagr = $na_meta( 'cagr_meta_field' );
$na_dd   = $na_meta( 'drawdown_percent_meta_field' );
$na_win  = $na_meta( 'winning_percentage_meta_field' );

// Strategy link.
$na_strat_id   = (int) $na_meta( 'strategy_id' );
$na_strat_name = $na_meta( 'strategy_name_meta_field' );
$na_strat_link = $na_strat_id ? get_permalink( $na_strat_id ) : '';

// Net P/L (money, signed).
if ( '' !== $na_net ) {
	$na_net_num   = (float) $na_net;
	$na_net_str   = ( $na_net_num < 0 ? '-' : '+' ) . '$' . number_format( abs( $na_net_num ), 2 );
	$na_net_class = $na_net_num < 0 ? 'na-neg' : 'na-pos';
} else {
	$na_net_str   = '—';
	$na_net_class = '';
}

// Profit factor.
$na_pf_str = ( '' !== $na_pf ) ? number_format( (float) $na_pf, 2 ) : '—';

// CAGR (whole-number percent, signed).
if ( '' !== $na_cagr ) {
	$na_cagr_num   = (float) $na_cagr;
	$na_cagr_str   = ( $na_cagr_num >= 0 ? '+' : '' ) . number_format( $na_cagr_num, 2 ) . '%';
	$na_cagr_class = $na_cagr_num >= 0 ? 'na-pos' : 'na-neg';
} else {
	$na_cagr_str   = '—';
	$na_cagr_class = '';
}

// Max drawdown (stored positive whole-number percent; shown as a loss).
$na_dd_str = ( '' !== $na_dd ) ? '-' . number_format( abs( (float) $na_dd ), 2 ) . '%' : '—';

// Win rate (whole-number percent).
if ( '' !== $na_win ) {
	$na_win_num   = (float) $na_win;
	$na_win_str   = number_format( $na_win_num, 2 ) . '%';
	$na_win_width = max( 0, min( 100, round( $na_win_num ) ) );
} else {
	$na_win_num   = null;
	$na_win_str   = '—';
	$na_win_width = 0;
}

// Equity sparkline.
$na_spark = function_exists( 'na_backtest_equity_spark' ) ? na_backtest_equity_spark( $na_id ) : null;
?>
<article class="na-card na-bt-card">
	<header class="na-bt-card-head">
		<span class="na-bt-run-id">RUN #<?php echo esc_html( (string) $na_id ); ?></span>
		<h3 class="na-bt-card-title"><a href="<?php echo esc_url( $na_permalink ); ?>"><?php the_title(); ?></a></h3>
		<?php if ( '' !== $na_strat_name ) : ?>
			<span class="na-bt-card-strat">strategy:
				<?php if ( $na_strat_link ) : ?>
					<a href="<?php echo esc_url( $na_strat_link ); ?>"><?php echo esc_html( $na_strat_name ); ?></a>
				<?php else : ?>
					<?php echo esc_html( $na_strat_name ); ?>
				<?php endif; ?>
			</span>
		<?php endif; ?>
		<div class="na-bt-badges">
			<?php if ( '' !== $na_inst ) : ?><span class="na-bt-badge"><?php echo esc_html( $na_inst ); ?></span><?php endif; ?>
			<?php if ( '' !== $na_tf ) : ?><span class="na-bt-badge"><?php echo esc_html( $na_tf ); ?></span><?php endif; ?>
			<?php if ( '' !== $na_trades ) : ?><span class="na-bt-badge"><?php echo esc_html( number_format_i18n( (int) $na_trades ) . ' trades' ); ?></span><?php endif; ?>
		</div>
	</header>

	<?php if ( $na_spark ) : ?>
		<svg class="na-bt-spark" viewBox="0 0 300 46" preserveAspectRatio="none" role="img" aria-label="Equity curve">
			<polyline fill="none" stroke="<?php echo $na_spark['up'] ? '#22c55e' : '#ef4444'; ?>" stroke-width="2" points="<?php echo esc_attr( $na_spark['line'] ); ?>" />
			<polyline fill="<?php echo $na_spark['up'] ? 'rgba(34,197,94,.10)' : 'rgba(239,68,68,.10)'; ?>" stroke="none" points="<?php echo esc_attr( $na_spark['area'] ); ?>" />
		</svg>
	<?php endif; ?>

	<?php if ( '' !== $na_start || '' !== $na_end ) : ?>
		<div class="na-bt-period"><?php echo esc_html( $na_start ); ?> <span class="na-bt-arrow" aria-hidden="true">&rarr;</span> <?php echo esc_html( $na_end ); ?></div>
	<?php endif; ?>

	<div class="na-bt-mgrid">
		<div class="na-bt-metric"><span class="na-bt-mval na-tab <?php echo esc_attr( $na_net_class ); ?>"><?php echo esc_html( $na_net_str ); ?></span><span class="na-bt-mlbl">Net P/L</span></div>
		<div class="na-bt-metric"><span class="na-bt-mval na-tab"><?php echo esc_html( $na_pf_str ); ?></span><span class="na-bt-mlbl">Profit factor</span></div>
		<div class="na-bt-metric"><span class="na-bt-mval na-tab <?php echo esc_attr( $na_cagr_class ); ?>"><?php echo esc_html( $na_cagr_str ); ?></span><span class="na-bt-mlbl">CAGR</span></div>
		<div class="na-bt-metric"><span class="na-bt-mval na-tab na-neg"><?php echo esc_html( $na_dd_str ); ?></span><span class="na-bt-mlbl">Max DD</span></div>
	</div>

	<div class="na-bt-foot">
		<div class="na-bt-winbar">
			<div class="na-bt-wlabel">Win rate &middot; <?php echo esc_html( $na_win_str ); ?></div>
			<div class="na-bt-track"><div class="na-bt-fill<?php echo ( null !== $na_win_num && $na_win_num < 50 ) ? ' is-low' : ''; ?>" style="width:<?php echo esc_attr( (string) $na_win_width ); ?>%"></div></div>
		</div>
		<a class="na-bt-cta" href="<?php echo esc_url( $na_permalink ); ?>">view run &rarr;</a>
	</div>
</article>
