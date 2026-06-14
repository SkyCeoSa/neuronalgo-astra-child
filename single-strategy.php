<?php
/**
 * Single Strategy template (FE-3.7) — terminal-desk hero + spec sheet.
 *
 * Section order (north-star = approved prototype, one level more polished):
 *   1. Hero  — a "trading desk" console card: window top-bar (dots + path + LIVE),
 *      eyebrow, BRAND name + animated caret, the stable code · symbol · timeframe
 *      identity line, and the four headline KPIs INTEGRATED into the hero.
 *   2. Strategy spec (public GENERAL card + locked PRO teaser).
 *   3. Equity curve (moved up, right under the spec).
 *   4. Performance breakdown (the remaining metrics, as a tidy titled grid).
 *   5. Methodology (post content, only when present).
 *   6. Related strategies.
 *   7. CTA.
 *
 * 3-layer naming: the H1 shows the BRAND name (display_title meta), NEVER the raw
 * StrategyQuant filename ("Strategy 2.15.64"). When no brand is assigned yet it
 * falls back to the stable code (e.g. NA-FX-010). The stable code + symbol +
 * timeframe render as the mono identity line beneath the name.
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

	/* ---- 3-layer naming: brand name (never the raw quant filename) ---- */
	$na_code  = (string) get_post_meta( $na_sid, 'strategy_code', true );
	$na_brand = (string) get_post_meta( $na_sid, 'display_title', true );
	$na_title = get_the_title();
	// Suppress raw StrategyQuant filenames like "Strategy 2.15.64".
	$na_is_raw = ( '' === trim( $na_brand ) ) || preg_match( '/^\s*strategy\s+[\d.\s]+$/i', $na_brand );
	if ( $na_is_raw ) {
		$na_brand = ( '' !== $na_code ) ? $na_code : $na_title;
	}

	/* ---- Identity line: code · symbol · timeframe ---- */
	$na_symbol = (string) get_post_meta( $na_sid, 'strategy_symbol_meta_field', true );
	$na_tf     = (string) get_post_meta( $na_sid, 'strategy_timeframe_meta_field', true );
	if ( '' === $na_symbol && $na_bt ) {
		$na_symbol = (string) get_post_meta( $na_bt, 'instrument_meta_field', true );
	}
	if ( '' === $na_tf && $na_bt ) {
		$na_tf = (string) get_post_meta( $na_bt, 'time_frame_meta_field', true );
	}
	$na_id_parts = array_values(
		array_filter(
			array( $na_code, $na_symbol, $na_tf ),
			function ( $v ) {
				return '' !== trim( (string) $v );
			}
		)
	);

	/* ---- Console path slug ---- */
	$na_slug = get_post_field( 'post_name', $na_sid );
	if ( '' === (string) $na_slug ) {
		$na_slug = 'strategy';
	}

	/* ---- Eyebrow: primary strategy_type term, else neutral label ---- */
	$na_eyebrow    = 'Quant Strategy';
	$na_type_terms = get_the_terms( $na_sid, 'strategy_type' );
	if ( ! is_wp_error( $na_type_terms ) && ! empty( $na_type_terms ) ) {
		$na_eyebrow = $na_type_terms[0]->name;
	}

	/* ---- Numeric backtest meta helper (percents are whole numbers) ---- */
	$na_num = function ( $key ) use ( $na_bt ) {
		if ( ! $na_bt ) {
			return null;
		}
		$val = get_post_meta( $na_bt, $key, true );
		return ( '' === $val || null === $val ) ? null : (float) $val;
	};

	$na_cagr    = $na_num( 'cagr_meta_field' );
	$na_pf      = $na_num( 'profit_factor_meta_field' );
	$na_win     = $na_num( 'winning_percentage_meta_field' );
	$na_dd      = $na_num( 'drawdown_percent_meta_field' );
	$na_sharpe  = $na_num( 'sharpe_ratio_meta_field' );
	$na_sortino = $na_num( 'sortino_ratio' );
	$na_trades  = $na_num( 'number_of_trades_meta_field' );
	$na_profit  = $na_num( 'total_profit_meta_field' );

	/* Headline KPIs — integrated INTO the hero (max four, prototype parity). */
	$na_hero_kpis = array();
	if ( null !== $na_cagr ) {
		$na_hero_kpis[] = array(
			'k' => 'CAGR',
			'v' => ( $na_cagr >= 0 ? '+' : '' ) . number_format( $na_cagr, 2 ) . '%',
			's' => $na_cagr >= 0 ? 'pos' : 'neg',
		);
	}
	if ( null !== $na_pf ) {
		$na_hero_kpis[] = array( 'k' => 'Profit Factor', 'v' => number_format( $na_pf, 2 ), 's' => '' );
	}
	if ( null !== $na_win ) {
		$na_hero_kpis[] = array( 'k' => 'Win Rate', 'v' => number_format( $na_win, 2 ) . '%', 's' => '' );
	}
	if ( null !== $na_dd ) {
		$na_hero_kpis[] = array( 'k' => 'Max DD', 'v' => '-' . number_format( abs( $na_dd ), 2 ) . '%', 's' => 'neg' );
	}

	/* Secondary metrics — the performance breakdown grid (no hero duplication). */
	$na_more = array();
	if ( null !== $na_sharpe ) {
		$na_more[] = array( 'k' => 'Sharpe ratio', 'v' => number_format( $na_sharpe, 2 ) );
	}
	if ( null !== $na_sortino ) {
		$na_more[] = array( 'k' => 'Sortino ratio', 'v' => number_format( $na_sortino, 2 ) );
	}
	if ( null !== $na_trades ) {
		$na_more[] = array( 'k' => 'Total trades', 'v' => number_format( $na_trades, 0 ) );
	}
	if ( null !== $na_profit ) {
		$na_more[] = array( 'k' => 'Net profit', 'v' => ( $na_profit >= 0 ? '+$' : '-$' ) . number_format( abs( $na_profit ), 0 ) );
	}

	/* Flagship equity payload for the chart. */
	$na_equity    = na_strategy_equity_payload( $na_bt );
	$na_has_chart = ! empty( $na_equity['series'] );
	$na_chart_id  = 'na-strategy-equity-' . $na_sid;

	/* Period subline for the equity section. */
	$na_p_start = $na_bt ? get_post_meta( $na_bt, 'backtest_period_start_meta_field', true ) : '';
	$na_p_end   = $na_bt ? get_post_meta( $na_bt, 'backtest_period_end_meta_field', true ) : '';
	?>
	<main class="na-strategy-single" id="na-strategy-single">
		<article <?php post_class( 'na-strategy-single-inner' ); ?>>

			<header class="na-strategy-hero na-panel na-glow-border">
				<div class="na-strategy-hero-bar">
					<span class="na-strategy-dots" aria-hidden="true"><i class="na-dot r"></i><i class="na-dot y"></i><i class="na-dot g"></i></span>
					<span class="na-strategy-hero-path">~/strategies/<?php echo esc_html( $na_slug ); ?></span>
					<span class="na-strategy-hero-sp"></span>
					<span class="na-strategy-hero-live"><i></i>LIVE</span>
				</div>
				<div class="na-strategy-hero-body">
					<p class="na-eyebrow"><?php echo esc_html( $na_eyebrow ); ?></p>
					<h1 class="na-strategy-title"><?php echo esc_html( $na_brand ); ?><span class="na-strategy-caret" aria-hidden="true"></span></h1>

					<?php if ( ! empty( $na_id_parts ) ) : ?>
						<p class="na-strategy-id"><?php echo esc_html( implode( '  ·  ', $na_id_parts ) ); ?></p>
					<?php endif; ?>

					<?php if ( has_excerpt() ) : ?>
						<p class="na-strategy-lead"><?php echo esc_html( get_the_excerpt() ); ?></p>
					<?php endif; ?>

					<?php if ( ! empty( $na_hero_kpis ) ) : ?>
						<div class="na-strategy-hero-kpis">
							<?php foreach ( $na_hero_kpis as $na_k ) : ?>
								<div class="na-strategy-hero-kpi">
									<span class="na-strategy-hero-kpi-k"><?php echo esc_html( $na_k['k'] ); ?></span>
									<span class="na-strategy-hero-kpi-v <?php echo esc_attr( $na_k['s'] ); ?>"><?php echo esc_html( $na_k['v'] ); ?></span>
								</div>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>

					<?php if ( $na_bt ) : ?>
						<div class="na-strategy-hero-cta">
							<a class="na-btn na-btn-primary" href="<?php echo esc_url( get_permalink( $na_bt ) ); ?>">View full backtest</a>
						</div>
					<?php endif; ?>
				</div>
			</header>

			<?php get_template_part( 'template-parts/strategy-spec', null, array( 'sid' => $na_sid ) ); ?>

			<?php if ( $na_has_chart ) : ?>
				<section class="na-strategy-section na-strategy-chart-section" aria-label="Equity curve">
					<h2 class="na-h2 na-strategy-section-title">Equity curve</h2>
					<?php if ( $na_p_start && $na_p_end ) : ?>
						<p class="na-strategy-section-intro"><?php echo esc_html( $na_p_start . '  →  ' . $na_p_end ); ?></p>
					<?php endif; ?>
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

			<?php if ( ! empty( $na_more ) ) : ?>
				<section class="na-strategy-section na-strategy-perf" aria-label="Performance breakdown">
					<h2 class="na-h2 na-strategy-section-title">Performance breakdown</h2>
					<div class="na-strategy-perf-grid">
						<?php foreach ( $na_more as $na_m ) : ?>
							<div class="na-strategy-perf-item">
								<span class="na-strategy-perf-k"><?php echo esc_html( $na_m['k'] ); ?></span>
								<span class="na-strategy-perf-v"><?php echo esc_html( $na_m['v'] ); ?></span>
							</div>
						<?php endforeach; ?>
					</div>
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
