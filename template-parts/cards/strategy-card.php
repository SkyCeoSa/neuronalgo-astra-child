<?php
/**
 * Strategy archive card (FE-3.1).
 *
 * Renders inside the strategy archive loop (expects the_post() context).
 * Pulls the strategy's flagship backtest for a single headline metric.
 * Read-only presentation; relies on DS-B components (.na-card, .na-btn).
 *
 * @package astra-child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$na_sid       = get_the_ID();
$na_permalink = get_permalink();
$na_bt        = function_exists( 'na_strategy_flagship_backtest' ) ? na_strategy_flagship_backtest( $na_sid ) : 0;

// Headline metric: CAGR (fraction -> %), fall back to Sharpe, else neutral.
$na_metric_label = 'CAGR';
$na_metric_value = '—';
$na_metric_class = '';
if ( $na_bt ) {
	$na_cagr = get_post_meta( $na_bt, 'cagr_meta_field', true );
	if ( '' !== $na_cagr && null !== $na_cagr ) {
		$na_cagr     = (float) $na_cagr;
		$na_cagr_pct = ( abs( $na_cagr ) <= 1.5 ) ? $na_cagr * 100 : $na_cagr;
		$na_metric_value = ( $na_cagr_pct >= 0 ? '+' : '' ) . number_format( $na_cagr_pct, 1 ) . '%';
		$na_metric_class = $na_cagr_pct >= 0 ? 'na-pos' : 'na-neg';
	} else {
		$na_sharpe = get_post_meta( $na_bt, 'sharpe_ratio_meta_field', true );
		if ( '' !== $na_sharpe && null !== $na_sharpe ) {
			$na_metric_label = 'Sharpe';
			$na_metric_value = number_format( (float) $na_sharpe, 2 );
		}
	}
}

// Secondary badges: primary market + timeframe terms.
$na_badges = array();
foreach ( array( 'market', 'timeframe' ) as $na_tax ) {
	if ( ! taxonomy_exists( $na_tax ) ) {
		continue;
	}
	$na_terms = get_the_terms( $na_sid, $na_tax );
	if ( ! is_wp_error( $na_terms ) && ! empty( $na_terms ) ) {
		$na_badges[] = $na_terms[0]->name;
	}
}

$na_summary = has_excerpt()
	? get_the_excerpt()
	: wp_trim_words( wp_strip_all_tags( get_the_content() ), 22, '…' );
?>
<article class="na-card na-sl-card">
	<header class="na-sl-card-head">
		<h3 class="na-sl-card-title"><a href="<?php echo esc_url( $na_permalink ); ?>"><?php the_title(); ?></a></h3>
		<?php if ( ! empty( $na_badges ) ) : ?>
			<div class="na-sl-card-badges">
				<?php foreach ( $na_badges as $na_b ) : ?>
					<span class="na-badge na-sl-badge"><?php echo esc_html( $na_b ); ?></span>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</header>

	<?php if ( $na_summary ) : ?>
		<p class="na-sl-card-summary"><?php echo esc_html( $na_summary ); ?></p>
	<?php endif; ?>

	<div class="na-sl-card-foot">
		<div class="na-sl-card-metric">
			<span class="na-sl-metric-value na-tab <?php echo esc_attr( $na_metric_class ); ?>"><?php echo esc_html( $na_metric_value ); ?></span>
			<span class="na-sl-metric-label na-micro"><?php echo esc_html( $na_metric_label ); ?></span>
		</div>
		<a class="na-btn na-btn-ghost na-sl-card-cta" href="<?php echo esc_url( $na_permalink ); ?>">View strategy</a>
	</div>
</article>
