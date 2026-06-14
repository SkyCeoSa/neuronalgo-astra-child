<?php
/**
 * Single Strategy template (FE-3.2).
 *
 * Renders one strategy: hero + taxonomy chips, flagship KPI grid (DS-B metric
 * tiles), flagship equity curve, methodology (post content), risk profile, and
 * related strategies. Read-only presentation built on DS-B components
 * (.na-card, .na-btn, .na-metric) and the global design tokens.
 *
 * Percent metas are stored as whole numbers (e.g. 11.57 => 11.57%) and rendered
 * directly — no x100 scaling.
 *
 * @package astra-child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Normalize a backtest's equity curve into the chart payload the
 * na-backtest-charts bundle expects: { series: [ { t:<int seconds>, v:<float> } ] }.
 *
 * Tolerates the canonical { "schema_version":"1.0", "series":[{t,v}] } shape as
 * well as a { timestamp, equity_value } fallback. Returns an empty series when
 * nothing usable is found.
 *
 * @param int $bt_id Backtest post ID.
 * @return array
 */
if ( ! function_exists( 'na_strategy_equity_payload' ) ) {
	function na_strategy_equity_payload( $bt_id ) {
		$out = array( 'series' => array() );
		if ( ! $bt_id ) {
			return $out;
		}

		$raw = get_post_meta( $bt_id, 'equity_curve_json', true );
		if ( '' === $raw || null === $raw ) {
			return $out;
		}

		$data = is_string( $raw ) ? json_decode( $raw, true ) : $raw;
		if ( ! is_array( $data ) ) {
			return $out;
		}

		$series = ( isset( $data['series'] ) && is_array( $data['series'] ) ) ? $data['series'] : array();
		foreach ( $series as $point ) {
			if ( ! is_array( $point ) ) {
				continue;
			}

			if ( isset( $point['t'] ) ) {
				$t = $point['t'];
			} elseif ( isset( $point['timestamp'] ) ) {
				$t = $point['timestamp'];
			} else {
				continue;
			}

			if ( isset( $point['v'] ) ) {
				$v = $point['v'];
			} elseif ( isset( $point['equity_value'] ) ) {
				$v = $point['equity_value'];
			} else {
				continue;
			}

			if ( ! is_numeric( $t ) || ! is_numeric( $v ) ) {
				continue;
			}

			$out['series'][] = array(
				't' => (int) $t,
				'v' => (float) $v,
			);
		}

		return $out;
	}
}

get_header();

while ( have_posts() ) :
	the_post();

	$na_sid = get_the_ID();
	$na_bt  = function_exists( 'na_strategy_flagship_backtest' ) ? na_strategy_flagship_backtest( $na_sid ) : 0;

	// Eyebrow: primary strategy_type term, else a neutral label.
	$na_eyebrow    = 'Strategy';
	$na_type_terms = get_the_terms( $na_sid, 'strategy_type' );
	if ( ! is_wp_error( $na_type_terms ) && ! empty( $na_type_terms ) ) {
		$na_eyebrow = $na_type_terms[0]->name;
	}

	// Hero taxonomy chips (label => primary term name).
	$na_chip_taxes = array(
		'market'      => 'Market',
		'asset_class' => 'Asset class',
		'timeframe'   => 'Timeframe',
		'risk_level'  => 'Risk',
	);
	$na_chips = array();
	foreach ( $na_chip_taxes as $na_tax => $na_lbl ) {
		if ( ! taxonomy_exists( $na_tax ) ) {
			continue;
		}
		$na_terms = get_the_terms( $na_sid, $na_tax );
		if ( ! is_wp_error( $na_terms ) && ! empty( $na_terms ) ) {
			$na_chips[] = array(
				'label' => $na_lbl,
				'name'  => $na_terms[0]->name,
			);
		}
	}

	// Build the flagship KPI tiles from backtest meta (percents are whole numbers).
	$na_tiles = array();
	if ( $na_bt ) {
		$na_meta_num = function ( $key ) use ( $na_bt ) {
			$val = get_post_meta( $na_bt, $key, true );
			return ( '' === $val || null === $val ) ? null : (float) $val;
		};

		$cagr = $na_meta_num( 'cagr_meta_field' );
		if ( null !== $cagr ) {
			$na_tiles[] = array(
				'label'       => 'CAGR',
				'value'       => ( $cagr >= 0 ? '+' : '' ) . number_format( $cagr, 2 ) . '%',
				'trend_state' => $cagr >= 0 ? 'positive' : 'negative',
			);
		}

		$sharpe = $na_meta_num( 'sharpe_ratio_meta_field' );
		if ( null !== $sharpe ) {
			$na_tiles[] = array(
				'label'       => 'Sharpe ratio',
				'value'       => number_format( $sharpe, 2 ),
				'trend_state' => 'info',
			);
		}

		$dd = $na_meta_num( 'drawdown_percent_meta_field' );
		if ( null !== $dd ) {
			$na_tiles[] = array(
				'label'       => 'Max drawdown',
				'value'       => '-' . number_format( abs( $dd ), 2 ) . '%',
				'trend_state' => 'negative',
			);
		}

		$win = $na_meta_num( 'winning_percentage_meta_field' );
		if ( null !== $win ) {
			$na_tiles[] = array(
				'label'       => 'Win rate',
				'value'       => number_format( $win, 2 ) . '%',
				'trend_state' => 'info',
			);
		}

		$pf = $na_meta_num( 'profit_factor_meta_field' );
		if ( null !== $pf ) {
			$na_tiles[] = array(
				'label'       => 'Profit factor',
				'value'       => number_format( $pf, 2 ),
				'trend_state' => 'info',
			);
		}

		$sortino = $na_meta_num( 'sortino_ratio' );
		if ( null !== $sortino ) {
			$na_tiles[] = array(
				'label'       => 'Sortino ratio',
				'value'       => number_format( $sortino, 2 ),
				'trend_state' => 'info',
			);
		}

		$trades = $na_meta_num( 'number_of_trades_meta_field' );
		if ( null !== $trades ) {
			$na_tiles[] = array(
				'label'       => 'Trades',
				'value'       => number_format( $trades, 0 ),
				'trend_state' => 'info',
			);
		}

		$profit = $na_meta_num( 'total_profit_meta_field' );
		if ( null !== $profit ) {
			$na_tiles[] = array(
				'label'       => 'Net profit',
				'value'       => ( $profit >= 0 ? '+$' : '-$' ) . number_format( abs( $profit ), 0 ),
				'trend_state' => $profit >= 0 ? 'positive' : 'negative',
			);
		}
	}

	// Flagship equity payload for the chart.
	$na_equity    = na_strategy_equity_payload( $na_bt );
	$na_has_chart = ! empty( $na_equity['series'] );
	$na_chart_id  = 'na-strategy-equity-' . $na_sid;

	// Instrument / period subline from the flagship backtest.
	$na_instrument = $na_bt ? get_post_meta( $na_bt, 'instrument_meta_field', true ) : '';
	$na_tf         = $na_bt ? get_post_meta( $na_bt, 'time_frame_meta_field', true ) : '';
	$na_p_start    = $na_bt ? get_post_meta( $na_bt, 'backtest_period_start_meta_field', true ) : '';
	$na_p_end      = $na_bt ? get_post_meta( $na_bt, 'backtest_period_end_meta_field', true ) : '';
	?>
	<main class="na-strategy-single" id="na-strategy-single">
		<article <?php post_class( 'na-strategy-single-inner' ); ?>>

			<header class="na-strategy-hero na-panel na-glow-border">
				<p class="na-eyebrow"><?php echo esc_html( $na_eyebrow ); ?></p>
				<h1 class="na-strategy-title"><?php the_title(); ?></h1>

				<?php if ( has_excerpt() ) : ?>
					<p class="na-strategy-lead na-section-lead"><?php echo esc_html( get_the_excerpt() ); ?></p>
				<?php endif; ?>

				<?php if ( ! empty( $na_chips ) ) : ?>
					<ul class="na-strategy-chips">
						<?php foreach ( $na_chips as $na_chip ) : ?>
							<li class="na-strategy-chip">
								<span class="na-strategy-chip-label na-micro"><?php echo esc_html( $na_chip['label'] ); ?></span>
								<span class="na-strategy-chip-value"><?php echo esc_html( $na_chip['name'] ); ?></span>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>

				<?php if ( $na_bt ) : ?>
					<div class="na-strategy-hero-cta">
						<a class="na-btn na-btn-primary" href="<?php echo esc_url( get_permalink( $na_bt ) ); ?>">View full backtest</a>
					</div>
				<?php endif; ?>
			</header>

			<?php if ( ! empty( $na_tiles ) ) : ?>
				<section class="na-strategy-section na-strategy-kpis" aria-label="Flagship performance">
					<h2 class="na-h2 na-strategy-section-title">Flagship performance</h2>
					<?php if ( $na_instrument || $na_tf || ( $na_p_start && $na_p_end ) ) : ?>
						<p class="na-strategy-section-intro na-section-intro">
							<?php
							$na_bits = array();
							if ( $na_instrument ) {
								$na_bits[] = $na_instrument;
							}
							if ( $na_tf ) {
								$na_bits[] = $na_tf;
							}
							if ( $na_p_start && $na_p_end ) {
								$na_bits[] = $na_p_start . ' → ' . $na_p_end;
							}
							echo esc_html( implode( '  ·  ', $na_bits ) );
							?>
						</p>
					<?php endif; ?>

					<div class="na-strategy-kpi-grid">
						<?php
						foreach ( $na_tiles as $na_tile ) {
							get_template_part(
								'template-parts/component-metric',
								null,
								array(
									'label'       => $na_tile['label'],
									'value'       => $na_tile['value'],
									'trend_state' => $na_tile['trend_state'],
									'size'        => 'md',
									'format'      => 'number',
									'class'       => 'na-strategy-kpi',
								)
							);
						}
						?>
					</div>
				</section>
			<?php endif; ?>

			<?php get_template_part( 'template-parts/strategy-spec', null, array( 'sid' => $na_sid ) ); ?>

			<?php if ( $na_has_chart ) : ?>
				<section class="na-strategy-section na-strategy-chart-section" aria-label="Equity curve">
					<h2 class="na-h2 na-strategy-section-title">Equity curve</h2>
					<div class="na-strategy-equity-chart na-panel" id="<?php echo esc_attr( $na_chart_id ); ?>"></div>
					<script id="<?php echo esc_attr( $na_chart_id ); ?>-data" type="application/json"><?php echo wp_json_encode( $na_equity, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ); ?></script>
					<script>
					( function () {
						function naInitEquity() {
							var NA = window.NeuronAlgo;
							if ( ! NA || typeof window.ApexCharts === 'undefined' ) {
								return;
							}
							var dataEl = document.getElementById( '<?php echo esc_js( $na_chart_id ); ?>-data' );
							var canvas = document.getElementById( '<?php echo esc_js( $na_chart_id ); ?>' );
							if ( ! dataEl || ! canvas || canvas.getAttribute( 'data-na-rendered' ) ) {
								return;
							}
							var payload;
							try {
								payload = JSON.parse( dataEl.textContent || dataEl.innerText );
							} catch ( e ) {
								return;
							}
							if ( ! NA.Validator.validate( payload, 'equity' ) ) {
								return;
							}
							var data = NA.Transformer.transform( payload, 'equity' );
							if ( ! data.length ) {
								return;
							}
							var options = NA.EquityChartConfig.getOptions( data );
							new window.ApexCharts( canvas, options ).render();
							canvas.setAttribute( 'data-na-rendered', '1' );
						}
						if ( 'loading' === document.readyState ) {
							document.addEventListener( 'DOMContentLoaded', naInitEquity );
						} else {
							naInitEquity();
						}
					} )();
					</script>
				</section>
			<?php endif; ?>

			<?php if ( get_the_content() ) : ?>
				<section class="na-strategy-section na-strategy-methodology" aria-label="Methodology">
					<h2 class="na-h2 na-strategy-section-title">Methodology</h2>
					<div class="na-strategy-prose">
						<?php the_content(); ?>
					</div>
				</section>
			<?php endif; ?>

			<?php
			$na_risk_terms = get_the_terms( $na_sid, 'risk_level' );
			$na_risk_label = ( ! is_wp_error( $na_risk_terms ) && ! empty( $na_risk_terms ) ) ? $na_risk_terms[0]->name : '';
			$na_dd_stat    = $na_bt ? get_post_meta( $na_bt, 'drawdown_percent_meta_field', true ) : '';
			if ( $na_risk_label || '' !== $na_dd_stat ) :
				?>
				<section class="na-strategy-section na-strategy-risk" aria-label="Risk profile">
					<h2 class="na-h2 na-strategy-section-title">Risk profile</h2>
					<div class="na-strategy-risk-grid">
						<?php if ( $na_risk_label ) : ?>
							<div class="na-strategy-risk-item na-card">
								<span class="na-strategy-risk-key na-micro">Risk level</span>
								<span class="na-strategy-risk-val"><?php echo esc_html( $na_risk_label ); ?></span>
							</div>
						<?php endif; ?>
						<?php if ( '' !== $na_dd_stat ) : ?>
							<div class="na-strategy-risk-item na-card">
								<span class="na-strategy-risk-key na-micro">Max drawdown</span>
								<span class="na-strategy-risk-val na-tab na-neg">-<?php echo esc_html( number_format( abs( (float) $na_dd_stat ), 2 ) ); ?>%</span>
							</div>
						<?php endif; ?>
					</div>
				</section>
			<?php endif; ?>

			<?php
			if ( ! is_wp_error( $na_type_terms ) && ! empty( $na_type_terms ) ) :
				$na_related = new WP_Query(
					array(
						'post_type'           => 'strategy',
						'post_status'         => 'publish',
						'posts_per_page'      => 3,
						'post__not_in'        => array( $na_sid ),
						'ignore_sticky_posts' => true,
						'no_found_rows'       => true,
						'tax_query'           => array(
							array(
								'taxonomy' => 'strategy_type',
								'field'    => 'term_id',
								'terms'    => $na_type_terms[0]->term_id,
							),
						),
					)
				);
				if ( $na_related->have_posts() ) :
					?>
					<section class="na-strategy-section na-strategy-related" aria-label="Related strategies">
						<h2 class="na-h2 na-strategy-section-title">Related strategies</h2>
						<div class="na-strategy-related-grid">
							<?php
							while ( $na_related->have_posts() ) :
								$na_related->the_post();
								get_template_part( 'template-parts/cards/strategy-card' );
							endwhile;
							?>
						</div>
					</section>
					<?php
				endif;
				wp_reset_postdata();
			endif;
			?>

			<section class="na-strategy-section na-strategy-cta na-panel na-glow-border" aria-label="Get started">
				<h2 class="na-h2 na-strategy-section-title">Put this strategy to work</h2>
				<p class="na-section-lead">Explore the full backtest, methodology, and live track record — or browse the complete library.</p>
				<div class="na-strategy-cta-actions">
					<?php if ( $na_bt ) : ?>
						<a class="na-btn na-btn-primary" href="<?php echo esc_url( get_permalink( $na_bt ) ); ?>">View full backtest</a>
					<?php endif; ?>
					<a class="na-btn na-btn-ghost" href="<?php echo esc_url( get_post_type_archive_link( 'strategy' ) ); ?>">Browse all strategies</a>
				</div>
			</section>

		</article>
	</main>
	<?php

endwhile;

get_footer();
