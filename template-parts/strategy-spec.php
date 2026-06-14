<?php
/**
 * Strategy Spec section (FE-3.6).
 *
 * Renders the qualitative strategy specification for a single strategy as a
 * terminal-desk "spec sheet":
 *
 *   1. Public GENERAL card  - symbol, timeframe, direction, holding horizon,
 *      strategy type and risk model, plus the qualitative long/short entry
 *      logic. These come from the STRATEGY post meta written by the parser
 *      (strategy_*_meta_field). They are deliberately number-free so the full
 *      strategy is never leaked on the free tier.
 *   2. Locked PRO card      - a teaser for the optimized parameter set. Only the
 *      parameter COUNT is known here (values are never stored), so the rows are
 *      blurred placeholders with an upgrade CTA.
 *
 * Premium gating + the upgrade URL are filterable so a membership plugin can
 * wire them later without touching this template:
 *   - apply_filters( 'na_strategy_spec_is_premium', false, $sid )
 *   - apply_filters( 'na_strategy_unlock_url', '', $sid )
 *
 * Expects $args['sid'] (strategy post ID); falls back to the current post.
 *
 * @package astra-child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$na_sid = ( isset( $args['sid'] ) && $args['sid'] ) ? (int) $args['sid'] : (int) get_the_ID();
if ( ! $na_sid ) {
	return;
}

$na_spec_keys = array(
	'symbol'      => 'strategy_symbol_meta_field',
	'timeframe'   => 'strategy_timeframe_meta_field',
	'direction'   => 'strategy_direction_meta_field',
	'horizon'     => 'strategy_holding_horizon_meta_field',
	'entry_style' => 'strategy_entry_style_meta_field',
	'type'        => 'strategy_type_meta_field',
	'risk_model'  => 'strategy_risk_model_meta_field',
	'logic_long'  => 'strategy_entry_logic_long_meta_field',
	'logic_short' => 'strategy_entry_logic_short_meta_field',
	'param_count' => 'strategy_param_count_meta_field',
);

$na_spec = array();
foreach ( $na_spec_keys as $na_k => $na_meta_key ) {
	$na_val          = get_post_meta( $na_sid, $na_meta_key, true );
	$na_spec[ $na_k ] = is_string( $na_val ) ? trim( $na_val ) : $na_val;
}

// GENERAL grid (label => value), matching the approved prototype. Empty values
// are dropped so the grid only shows what the parser actually extracted.
$na_general = array_filter(
	array(
		'Symbol'          => $na_spec['symbol'],
		'Timeframe'       => $na_spec['timeframe'],
		'Direction'       => $na_spec['direction'],
		'Holding Horizon' => $na_spec['horizon'],
		'Entry Style'     => ( '' !== (string) $na_spec['type'] ) ? $na_spec['type'] : $na_spec['entry_style'],
		'Risk Model'      => $na_spec['risk_model'],
	),
	function ( $v ) {
		return '' !== (string) $v && null !== $v;
	}
);

$na_logic_long  = (string) $na_spec['logic_long'];
$na_logic_short = (string) $na_spec['logic_short'];

// Nothing to show -> render nothing (keeps the template clean for bare posts).
if ( empty( $na_general ) && '' === $na_logic_long && '' === $na_logic_short ) {
	return;
}

$na_param_count = ( '' !== (string) $na_spec['param_count'] && null !== $na_spec['param_count'] )
	? max( 0, (int) $na_spec['param_count'] )
	: 0;
$na_is_premium  = (bool) apply_filters( 'na_strategy_spec_is_premium', false, $na_sid );
$na_unlock_url  = (string) apply_filters( 'na_strategy_unlock_url', '', $na_sid );
?>
<section class="na-strategy-section na-strategy-spec" aria-label="Strategy specification">
	<h2 class="na-h2 na-strategy-section-title">Strategy spec</h2>

	<div class="na-spec-card na-spec-public">
		<div class="na-spec-bar">
			<span class="na-spec-dots" aria-hidden="true"><i class="na-spec-dot r"></i><i class="na-spec-dot y"></i><i class="na-spec-dot g"></i></span>
			<span class="na-spec-bar-title">strategy.spec</span>
			<span class="na-spec-bar-sp"></span>
			<span class="na-spec-tag">GENERAL</span>
		</div>
		<div class="na-spec-in">
			<?php if ( ! empty( $na_general ) ) : ?>
				<div class="na-spec-grid">
					<?php foreach ( $na_general as $na_g_label => $na_g_val ) : ?>
						<div class="na-spec-g">
							<span class="na-spec-gk"><?php echo esc_html( $na_g_label ); ?></span>
							<span class="na-spec-gv"><?php echo esc_html( $na_g_val ); ?></span>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<?php if ( '' !== $na_logic_long ) : ?>
				<div class="na-spec-logic">
					<span class="na-spec-logic-h">// entry logic &mdash; long</span>
					<p><?php echo esc_html( $na_logic_long ); ?></p>
				</div>
			<?php endif; ?>

			<?php if ( '' !== $na_logic_short ) : ?>
				<div class="na-spec-logic">
					<span class="na-spec-logic-h">// entry logic &mdash; short</span>
					<p><?php echo esc_html( $na_logic_short ); ?></p>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<?php if ( $na_param_count > 0 ) : ?>
		<div class="na-spec-card na-spec-locked">
			<div class="na-spec-bar">
				<span class="na-spec-dots" aria-hidden="true"><i class="na-spec-dot r"></i><i class="na-spec-dot y"></i><i class="na-spec-dot g"></i></span>
				<span class="na-spec-bar-title">parameters.locked</span>
				<span class="na-spec-bar-sp"></span>
				<span class="na-spec-protag">PRO</span>
			</div>
			<div class="na-spec-in">
				<p class="na-spec-locked-lead">
					<?php
					echo esc_html(
						sprintf(
							/* translators: %d: number of optimized strategy parameters. */
							_n( '%d optimized parameter', '%d optimized parameters', $na_param_count, 'astra-child' ),
							$na_param_count
						)
					);
					?>
				</p>
				<div class="na-spec-lkgrid">
					<?php
					$na_show = min( $na_param_count, 6 );
					for ( $na_i = 1; $na_i <= $na_show; $na_i++ ) :
						?>
						<div class="na-spec-lk">
							<span><?php echo esc_html( sprintf( 'Parameter %02d', $na_i ) ); ?></span>
							<b class="na-spec-blur" aria-hidden="true">&bull;&bull;&bull;</b>
						</div>
					<?php endfor; ?>
				</div>
				<?php if ( ! $na_is_premium ) : ?>
					<?php if ( '' !== $na_unlock_url ) : ?>
						<a class="na-spec-ubtn" href="<?php echo esc_url( $na_unlock_url ); ?>">&#128274; Unlock full parameters with Pro</a>
					<?php else : ?>
						<button class="na-spec-ubtn" type="button">&#128274; Unlock full parameters with Pro</button>
					<?php endif; ?>
				<?php endif; ?>
			</div>
		</div>
	<?php endif; ?>
</section>
