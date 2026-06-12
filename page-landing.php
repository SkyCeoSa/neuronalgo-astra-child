<?php
/**
 * Template Name: NeuronAlgo Landing (The Desk)
 * Template Post Type: page
 *
 * Bespoke, full-canvas landing page ("The Desk"). It renders its own document
 * chrome (the custom na-topbar) instead of Astra's header/footer, so it
 * intentionally does NOT call get_header() / get_footer(). All assets load via
 * wp_head() / wp_footer(); the conditional enqueue is registered in
 * inc/enqueue/class-conditional-assets.php (see the FE-2 Cline prompt).
 *
 * Dynamic data (read-only presentation; all business logic stays in
 * neuronalgo-core): the hero "flagship" is the published `backtest` with the
 * highest `sharpe_ratio_meta_field`, and the hero equity chart is hydrated
 * from that backtest's `equity_curve_json` via a JSON island.
 *
 * @package astra-child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ----------------------------------------------------------------------- *
 * 1) Data layer — read-only reads of CPT meta registered by neuronalgo-core.
 * ----------------------------------------------------------------------- */

if ( ! function_exists( 'na_landing_flagship_backtest' ) ) {
	/** Flagship backtest = published `backtest` with the highest Sharpe ratio. */
	function na_landing_flagship_backtest() {
		$q = new WP_Query(
			array(
				'post_type'      => 'backtest',
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'orderby'        => 'meta_value_num',
				'order'          => 'DESC',
				'meta_key'       => 'sharpe_ratio_meta_field',
				'no_found_rows'  => true,
				'meta_query'     => array(
					array(
						'key'     => 'sharpe_ratio_meta_field',
						'compare' => 'EXISTS',
					),
				),
			)
		);
		$id = $q->have_posts() ? (int) $q->posts[0]->ID : 0;
		wp_reset_postdata();
		return $id;
	}
}

if ( ! function_exists( 'na_landing_equity_series' ) ) {
	/**
	 * Normalize `equity_curve_json` into a compact [{t,v}] array.
	 * Contract: {"schema":"1.0","series":[{"t":<sec>,"v":<float>}]}.
	 * Also tolerates the JetEngine field shape {timestamp, equity_value}.
	 */
	function na_landing_equity_series( $bt_id ) {
		if ( ! $bt_id ) {
			return array();
		}
		$raw = get_post_meta( $bt_id, 'equity_curve_json', true );
		if ( empty( $raw ) ) {
			return array();
		}
		$data = json_decode( is_string( $raw ) ? $raw : wp_json_encode( $raw ), true );
		if ( ! is_array( $data ) || empty( $data['series'] ) || ! is_array( $data['series'] ) ) {
			return array();
		}
		$out = array();
		foreach ( $data['series'] as $pt ) {
			if ( isset( $pt['v'] ) ) {
				$v = (float) $pt['v'];
			} elseif ( isset( $pt['equity_value'] ) ) {
				$v = (float) $pt['equity_value'];
			} else {
				continue;
			}
			$t = isset( $pt['t'] ) ? (int) $pt['t'] : ( isset( $pt['timestamp'] ) ? (string) $pt['timestamp'] : null );
			$out[] = array(
				't' => $t,
				'v' => $v,
			);
		}
		return $out;
	}
}

if ( ! function_exists( 'na_landing_total_trades' ) ) {
	/** Sum of executed trades across every backtest. */
	function na_landing_total_trades() {
		global $wpdb;
		$sum = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT SUM(CAST(meta_value AS UNSIGNED)) FROM {$wpdb->postmeta} WHERE meta_key = %s",
				'number_of_trades_meta_field'
			)
		);
		return (int) $sum;
	}
}

if ( ! function_exists( 'na_landing_countup_parts' ) ) {
	/** Returns [to, decimals, suffix] for an animated compact counter. */
	function na_landing_countup_parts( $n ) {
		$n = (int) $n;
		if ( $n >= 1000000 ) {
			return array( round( $n / 1000000, 1 ), 1, 'M+' );
		}
		if ( $n >= 1000 ) {
			return array( round( $n / 1000, 1 ), 1, 'k+' );
		}
		return array( $n, 0, '' );
	}
}

/* ----------------------------------------------------------------------- *
 * 2) Resolve values (with prototype-matching fallbacks for an empty DB).
 * ----------------------------------------------------------------------- */

$na_bt = na_landing_flagship_backtest();

$na_sharpe = $na_bt ? (float) get_post_meta( $na_bt, 'sharpe_ratio_meta_field', true ) : 1.39;
$na_cagr   = $na_bt ? (float) get_post_meta( $na_bt, 'cagr_meta_field', true ) : 0.247;
$na_dd     = $na_bt ? (float) get_post_meta( $na_bt, 'drawdown_percent_meta_field', true ) : 0.1069;
$na_win    = $na_bt ? (float) get_post_meta( $na_bt, 'winning_percentage_meta_field', true ) : 0.58;
$na_profit = $na_bt ? (float) get_post_meta( $na_bt, 'total_profit_meta_field', true ) : 41230;

/* The contract stores CAGR / DD / win as fractions (0.1157, 0.1069, 0.6805).
   Guard against values already expressed as percentages. */
$na_cagr_pct = ( $na_cagr <= 1.5 ) ? $na_cagr * 100 : $na_cagr;
$na_dd_pct   = ( $na_dd <= 1.5 ) ? $na_dd * 100 : $na_dd;
$na_win_pct  = ( $na_win <= 1.5 ) ? $na_win * 100 : $na_win;

$na_strategy_count = (int) wp_count_posts( 'strategy' )->publish;
if ( $na_strategy_count < 1 ) {
	$na_strategy_count = 12;
}

$na_market_count = 0;
if ( taxonomy_exists( 'market' ) ) {
	$terms = get_terms(
		array(
			'taxonomy'   => 'market',
			'hide_empty' => true,
			'fields'     => 'ids',
		)
	);
	if ( ! is_wp_error( $terms ) ) {
		$na_market_count = count( $terms );
	}
}
if ( $na_market_count < 1 ) {
	$na_market_count = 8;
}

$na_total_trades = na_landing_total_trades();
if ( $na_total_trades < 1 ) {
	$na_total_trades = 1200000;
}
list( $na_tr_to, $na_tr_dec, $na_tr_suf ) = na_landing_countup_parts( $na_total_trades );
$na_tr_text = number_format( $na_tr_to, $na_tr_dec ) . $na_tr_suf;

$na_equity = na_landing_equity_series( $na_bt );

$na_bootstrap = array(
	'equity' => $na_equity,
	'kpis'   => array(
		'cagr'   => round( $na_cagr_pct, 1 ),
		'sharpe' => round( $na_sharpe, 2 ),
		'maxdd'  => round( -1 * abs( $na_dd_pct ), 2 ),
		'win'    => (int) round( $na_win_pct ),
		'pnl'    => (int) round( $na_profit ),
	),
	'source' => $na_bt ? array(
		'backtest_id' => $na_bt,
		'title'       => get_the_title( $na_bt ),
	) : null,
);

$na_theme_uri = get_stylesheet_directory_uri();
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="icon" type="image/svg+xml" href="<?php echo esc_url( $na_theme_uri . '/assets/img/brand/favicon.svg' ); ?>">
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'na-landing-page' ); ?>>
<?php wp_body_open(); ?>
	<div id="na-scroll-progress" class="na-scroll-progress"></div>
	<div class="na-grain" aria-hidden="true"></div>

	<!-- Market tape -->
	<div class="na-ticker" aria-hidden="true">
		<div class="na-ticker-viewport">
			<div id="na-ticker-track" class="na-ticker-track"></div>
		</div>
	</div>

	<!-- Desk top bar -->
	<header class="na-topbar">
		<div class="na-topbar-inner">
			<a class="na-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<img class="na-brand-mark" src="<?php echo esc_url( $na_theme_uri . '/assets/img/brand/neuronalgo-mark.svg' ); ?>" alt="NeuronAlgo" width="28" height="28">
				<span class="na-brand-name">NeuronAlgo</span>
			</a>
			<div class="na-topbar-status">
				<span class="na-live-badge"><span class="na-live-dot"></span>LIVE</span>
				<span class="na-topbar-clock na-tab" id="na-clock">00:00:00 UTC</span>
			</div>
			<div class="na-topbar-actions">
				<a class="na-navlink" href="#story">How it works</a>
				<a class="na-navlink" href="#transparency">Transparency</a>
				<button class="na-cmdk-trigger" data-cmdk-open type="button">
					<span>Open terminal</span>
					<kbd class="na-kbd">⌘K</kbd>
				</button>
			</div>
		</div>
	</header>

	<!-- ============================ THE DESK (hero) ====================== -->
	<section class="na-desk" id="desk">
		<div class="na-desk-inner">
			<div class="na-desk-head" data-reveal>
				<span class="na-eyebrow">INSTITUTIONAL QUANT INFRASTRUCTURE</span>
				<h1 class="na-desk-title">This is the desk.<br><span class="na-desk-title-accent">Live, transparent, relentless.</span></h1>
				<p class="na-desk-sub"><?php echo esc_html( $na_strategy_count ); ?> algorithmic strategies. <?php echo esc_html( $na_tr_text ); ?> executed trades. Every signal, equity curve and drawdown &mdash; open for you to inspect, as it happens.</p>
				<div class="na-desk-cta">
					<button class="na-btn na-btn-primary" data-cmdk-open type="button">Open the terminal <kbd class="na-kbd na-kbd-on">⌘K</kbd></button>
					<a class="na-btn na-btn-ghost" href="#transparency">See the proof</a>
				</div>
			</div>

			<div class="na-desk-grid">
				<!-- Main: live equity terminal -->
				<div class="na-panel na-deck-main na-glow-border" data-reveal>
					<div class="na-panel-chrome">
						<span class="na-dots"><i></i><i></i><i></i></span>
						<span class="na-panel-title na-tab">~/neuronalgo/portfolio.live</span>
						<span class="na-live-badge na-live-badge-sm"><span class="na-live-dot"></span>LIVE</span>
					</div>
					<div class="na-deck-statline">
						<div class="na-deck-pnl-wrap">
							<span class="na-deck-pnl-label na-micro">Portfolio P&amp;L</span>
							<span class="na-deck-pnl na-tab" id="na-deck-pnl">$<?php echo esc_html( number_format( (float) $na_profit ) ); ?></span>
						</div>
						<span class="na-deck-change na-tab is-pos" id="na-eq-change">+18.4% &middot; 1Y</span>
						<div class="na-tf-group">
							<button class="na-tf" data-tf="1D" type="button">1D</button>
							<button class="na-tf" data-tf="1W" type="button">1W</button>
							<button class="na-tf" data-tf="1M" type="button">1M</button>
							<button class="na-tf is-active" data-tf="1Y" type="button">1Y</button>
							<button class="na-tf" data-tf="ALL" type="button">ALL</button>
						</div>
					</div>
					<div class="na-deck-chart-wrap">
						<svg class="na-deck-chart" viewBox="0 0 560 260" preserveAspectRatio="none">
							<defs>
								<linearGradient id="eqLine" x1="0" y1="0" x2="1" y2="0"><stop offset="0%" stop-color="#3D7DFF"></stop><stop offset="100%" stop-color="#38BDF8"></stop></linearGradient>
								<linearGradient id="eqFill" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#3D7DFF" stop-opacity="0.22"></stop><stop offset="100%" stop-color="#3D7DFF" stop-opacity="0"></stop></linearGradient>
							</defs>
							<g class="na-grid-lines" stroke="rgba(154,167,194,0.10)" stroke-width="0.5">
								<line x1="0" y1="65" x2="560" y2="65"></line>
								<line x1="0" y1="130" x2="560" y2="130"></line>
								<line x1="0" y1="195" x2="560" y2="195"></line>
							</g>
							<path id="na-eq-area" fill="url(#eqFill)" d=""></path>
							<polyline id="na-eq-line" fill="none" stroke="url(#eqLine)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" points=""></polyline>
							<circle id="na-eq-dot" r="4" fill="#fff" stroke="#3D7DFF" stroke-width="2"></circle>
							<line id="na-eq-now" class="na-eq-now" x1="0" y1="20" x2="0" y2="240" stroke="#38BDF8" stroke-width="1" stroke-dasharray="2 6"></line>
							<line id="na-eq-cross" x1="0" y1="24" x2="0" y2="236" stroke="rgba(154,167,194,0.55)" stroke-width="0.75" stroke-dasharray="3 4" style="opacity:0"></line>
							<circle id="na-eq-cursor" r="4.5" fill="#fff" stroke="#3D7DFF" stroke-width="2" style="opacity:0"></circle>
						</svg>
						<div id="na-eq-tip" class="na-chart-tip na-tab"></div>
					</div>
					<div class="na-deck-metrics">
						<div class="na-deck-metric"><span class="na-deck-mv na-tab na-pos" data-countup data-to="<?php echo esc_attr( round( $na_cagr_pct, 1 ) ); ?>" data-decimals="1" data-prefix="+" data-suffix="%">+<?php echo esc_html( number_format( $na_cagr_pct, 1 ) ); ?>%</span><span class="na-deck-ml na-micro">CAGR</span></div>
						<div class="na-deck-metric"><span class="na-deck-mv na-tab" data-countup data-to="<?php echo esc_attr( round( $na_sharpe, 2 ) ); ?>" data-decimals="2"><?php echo esc_html( number_format( $na_sharpe, 2 ) ); ?></span><span class="na-deck-ml na-micro">Sharpe</span></div>
						<div class="na-deck-metric"><span class="na-deck-mv na-tab na-neg" data-countup data-to="<?php echo esc_attr( round( -1 * abs( $na_dd_pct ), 2 ) ); ?>" data-decimals="2" data-suffix="%">-<?php echo esc_html( number_format( abs( $na_dd_pct ), 2 ) ); ?>%</span><span class="na-deck-ml na-micro">Max DD</span></div>
						<div class="na-deck-metric"><span class="na-deck-mv na-tab" data-countup data-to="<?php echo esc_attr( (int) round( $na_win_pct ) ); ?>" data-suffix="%"><?php echo esc_html( (int) round( $na_win_pct ) ); ?>%</span><span class="na-deck-ml na-micro">Win rate</span></div>
					</div>
				</div>

				<!-- Side: live signals + heatmap -->
				<div class="na-deck-side">
					<div class="na-panel na-deck-signals" data-reveal>
						<div class="na-panel-chrome">
							<span class="na-panel-title na-tab">signal.stream</span>
							<span class="na-panel-meta na-micro">live</span>
						</div>
						<ul id="na-signal-feed" class="na-sig-list" aria-hidden="true"></ul>
					</div>
					<div class="na-panel na-deck-heat" data-reveal>
						<div class="na-panel-chrome">
							<span class="na-panel-title na-tab">market.heatmap</span>
							<span class="na-panel-meta na-micro">24h</span>
						</div>
						<div id="na-heatmap" class="na-heatmap" aria-hidden="true"></div>
					</div>
				</div>
			</div>

			<div class="na-desk-trust" data-reveal>
				<span class="na-tab"><b data-countup data-to="<?php echo esc_attr( $na_strategy_count ); ?>"><?php echo esc_html( $na_strategy_count ); ?></b> strategies</span>
				<span class="na-trust-sep"></span>
				<span class="na-tab"><b data-countup data-to="<?php echo esc_attr( $na_tr_to ); ?>" data-decimals="<?php echo esc_attr( $na_tr_dec ); ?>" data-suffix="<?php echo esc_attr( $na_tr_suf ); ?>"><?php echo esc_html( $na_tr_text ); ?></b> trades</span>
				<span class="na-trust-sep"></span>
				<span class="na-tab"><b data-countup data-to="<?php echo esc_attr( $na_market_count ); ?>"><?php echo esc_html( $na_market_count ); ?></b> markets</span>
				<span class="na-trust-sep"></span>
				<span class="na-tab"><b data-countup data-to="15" data-suffix="+">15+</b> years backtested</span>
			</div>
		</div>
	</section>

	<!-- ====================== STORY (morphing chart) ===================== -->
	<section class="na-story" id="story">
		<div class="na-story-inner">
			<div class="na-story-stage">
				<div class="na-panel na-morph-panel na-glow-border">
					<div class="na-panel-chrome">
						<span class="na-dots"><i></i><i></i><i></i></span>
						<span class="na-panel-title na-tab">strategy.pipeline</span>
						<span class="na-morph-stagelabel na-micro" id="na-morph-stagelabel">RAW DATA</span>
					</div>
					<div id="na-morph" class="na-morph" data-stage="0">
						<div class="na-morph-val">+<span class="na-tab" id="na-morph-val">312</span>%</div>
						<svg class="na-morph-svg" viewBox="0 0 600 320" preserveAspectRatio="none">
							<defs>
								<linearGradient id="morphFill" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#3D7DFF" stop-opacity="0.28"></stop><stop offset="100%" stop-color="#3D7DFF" stop-opacity="0"></stop></linearGradient>
								<linearGradient id="morphLine" x1="0" y1="0" x2="1" y2="0"><stop offset="0%" stop-color="#3D7DFF"></stop><stop offset="100%" stop-color="#38BDF8"></stop></linearGradient>
							</defs>
							<g id="na-morph-dots" class="na-morph-dots"></g>
							<path id="na-morph-dd" class="na-morph-dd" d="" fill="rgba(239,68,68,0.18)"></path>
							<path id="na-morph-area" class="na-morph-area" d="" fill="url(#morphFill)"></path>
							<polyline id="na-morph-line" class="na-morph-line" fill="none" stroke="url(#morphLine)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" points=""></polyline>
							<circle id="na-morph-live" class="na-morph-live" r="5" fill="#22C55E"></circle>
						</svg>
					</div>
				</div>
			</div>
			<div class="na-story-steps">
				<div class="na-story-step" data-step="0">
					<span class="na-story-num na-tab">01</span>
					<h3 class="na-h3">It starts as noise.</h3>
					<p>Millions of raw ticks across <?php echo esc_html( $na_market_count ); ?> markets. Messy, fast, unforgiving &mdash; the same firehose every desk drinks from.</p>
				</div>
				<div class="na-story-step" data-step="1">
					<span class="na-story-num na-tab">02</span>
					<h3 class="na-h3">We model the edge.</h3>
					<p>Quant research isolates a repeatable inefficiency and fits a robust model &mdash; not a curve-fit fantasy.</p>
				</div>
				<div class="na-story-step" data-step="2">
					<span class="na-story-num na-tab">03</span>
					<h3 class="na-h3">We stress the backtest.</h3>
					<p>15+ years, walk-forward, full drawdown profile. If it breaks, it breaks here &mdash; before a dollar is at risk.</p>
				</div>
				<div class="na-story-step" data-step="3">
					<span class="na-story-num na-tab">04</span>
					<h3 class="na-h3">It goes live.</h3>
					<p>Signals fire in real time and route to execution. You watch every fill &mdash; nothing hidden behind a black box.</p>
				</div>
				<div class="na-story-step" data-step="4">
					<span class="na-story-num na-tab">05</span>
					<h3 class="na-h3">Your edge compounds.</h3>
					<p>Disciplined, transparent, relentless. The same curve the desk trades is the one you follow.</p>
					<button class="na-btn na-btn-primary" data-cmdk-open type="button">Open the terminal <kbd class="na-kbd na-kbd-on">⌘K</kbd></button>
				</div>
			</div>
		</div>
	</section>

	<!-- ====================== TRANSPARENCY (live) ======================= -->
	<section class="na-tp" id="transparency">
		<div class="na-tp-inner">
			<div class="na-section-intro" data-reveal>
				<span class="na-eyebrow">TRANSPARENCY</span>
				<h2 class="na-h2">No black boxes. Watch it trade.</h2>
				<p class="na-section-lead">The equity curve, the drawdown, and the live trade log &mdash; the exact view we use on the desk.</p>
			</div>
			<div class="na-tp-grid">
				<div class="na-tp-charts">
					<div class="na-panel na-tp-card" data-reveal>
						<div class="na-panel-chrome"><span class="na-panel-title na-tab">equity.curve</span><span class="na-deck-change na-tab is-pos">+312% all-time</span></div>
						<svg class="na-tp-chart" viewBox="0 0 520 150" preserveAspectRatio="none">
							<defs><linearGradient id="tpEquityFill" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#3D7DFF" stop-opacity="0.25"></stop><stop offset="100%" stop-color="#3D7DFF" stop-opacity="0"></stop></linearGradient></defs>
							<path d="M0,135 L40,120 L80,128 L120,100 L160,108 L200,82 L240,70 L280,78 L320,52 L360,44 L400,50 L440,30 L480,22 L520,14 L520,150 L0,150 Z" fill="url(#tpEquityFill)"></path>
							<polyline fill="none" stroke="url(#eqLine)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" points="0,135 40,120 80,128 120,100 160,108 200,82 240,70 280,78 320,52 360,44 400,50 440,30 480,22 520,14"></polyline>
						</svg>
					</div>
					<div class="na-panel na-tp-card" data-reveal>
						<div class="na-panel-chrome"><span class="na-panel-title na-tab">drawdown</span><span class="na-deck-change na-tab is-neg">-15.5% max</span></div>
						<svg class="na-tp-chart na-tp-chart-sm" viewBox="0 0 520 110" preserveAspectRatio="none">
							<defs><linearGradient id="tpDdFill" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#EF4444" stop-opacity="0.28"></stop><stop offset="100%" stop-color="#EF4444" stop-opacity="0"></stop></linearGradient></defs>
							<path d="M0,4 L40,30 L80,18 L120,55 L160,40 L200,70 L240,48 L280,92 L320,62 L360,38 L400,58 L440,28 L480,42 L520,16 L520,0 L0,0 Z" fill="url(#tpDdFill)"></path>
							<polyline fill="none" stroke="#EF4444" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" points="0,4 40,30 80,18 120,55 160,40 200,70 240,48 280,92 320,62 360,38 400,58 440,28 480,42 520,16"></polyline>
						</svg>
					</div>
				</div>
				<div class="na-panel na-tp-log" data-reveal>
					<div class="na-panel-chrome"><span class="na-panel-title na-tab">trades.live</span><span class="na-panel-meta na-micro">streaming</span></div>
					<table class="na-trade-table na-tab">
						<thead><tr><th>Time</th><th>Symbol</th><th>Side</th><th class="na-ta-r">Return</th></tr></thead>
						<tbody id="na-trade-feed"></tbody>
					</table>
				</div>
			</div>
		</div>
	</section>

	<!-- ============================ FINAL CTA =========================== -->
	<section class="na-final" id="cta">
		<div class="na-final-inner" data-reveal>
			<span class="na-eyebrow">READY?</span>
			<h2 class="na-final-title">Pull up a seat at the desk.</h2>
			<p class="na-section-lead">Open the terminal and explore every strategy, signal and backtest &mdash; in real time.</p>
			<button class="na-btn na-btn-primary na-btn-lg" data-cmdk-open type="button">Open the terminal <kbd class="na-kbd na-kbd-on">⌘K</kbd></button>
		</div>
	</section>

	<footer class="na-foot">
		<div class="na-foot-inner">
			<span class="na-tab">NeuronAlgo</span>
			<span class="na-micro na-text-muted">Past performance does not guarantee future results.</span>
		</div>
	</footer>

	<!-- ====================== COMMAND PALETTE (CTA) ===================== -->
	<div id="na-cmdk" class="na-cmdk" aria-hidden="true" role="dialog" aria-modal="true" aria-label="Command palette">
		<div class="na-cmdk-panel">
			<div class="na-cmdk-search">
				<span class="na-cmdk-ic">⌘</span>
				<input id="na-cmdk-input" class="na-cmdk-input" type="text" placeholder="Type a command or search…" autocomplete="off" spellcheck="false">
				<button class="na-cmdk-esc" data-cmdk-close type="button">esc</button>
			</div>
			<ul id="na-cmdk-list" class="na-cmdk-list">
				<li class="na-cmdk-item is-active" data-target="#desk"><span class="na-cmdk-em">◉</span> View the live desk <span class="na-cmdk-hint na-tab">enter</span></li>
				<li class="na-cmdk-item" data-target="#story"><span class="na-cmdk-em">➜</span> How a strategy is built</li>
				<li class="na-cmdk-item" data-target="#transparency"><span class="na-cmdk-em">▦</span> Inspect equity, drawdown &amp; trades</li>
				<li class="na-cmdk-item" data-target="#cta"><span class="na-cmdk-em">↗</span> Get started with NeuronAlgo</li>
			</ul>
			<div class="na-cmdk-foot">
				<span><kbd class="na-kbd">↑</kbd><kbd class="na-kbd">↓</kbd> navigate</span>
				<span><kbd class="na-kbd">↵</kbd> select</span>
				<span><kbd class="na-kbd">esc</kbd> close</span>
			</div>
		</div>
	</div>

	<script id="na-landing-bootstrap" type="application/json"><?php echo wp_json_encode( $na_bootstrap, JSON_HEX_TAG | JSON_HEX_AMP ); ?></script>
	<?php wp_footer(); ?>
</body>
</html>
