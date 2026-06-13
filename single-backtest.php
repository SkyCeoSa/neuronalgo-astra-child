<?php
/**
 * Single Backtest template (FE-3.3).
 *
 * Renders one backtest in the terminal-desk aesthetic: hero, at-a-glance
 * (net-profit banner + 4 CSS gauges), equity + drawdown charts (auto-discovered
 * by the na-backtest-charts bundle), trade breakdown (win/loss donut, avg/largest
 * comparison bars, streaks), full-statistics tabs, monthly/yearly returns tables,
 * run config, risk disclaimer, and CTA.
 *
 * Percent metas are stored as whole numbers (e.g. 11.57 => 11.57%) and rendered
 * directly with no x100 scaling, mirroring single-strategy.php.
 *
 * Markup is scoped under .na-backtest-single (see assets/css/sections/single-backtest.css).
 *
 * @package astra-child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Build the equity chart payload { series:[{t:<int s>, v:<float>}] } from
 * equity_curve_json. Tolerates {series:[{t,v}]} and {timestamp,equity_value}.
 */
if ( ! function_exists( 'na_backtest_equity_payload' ) ) {
	function na_backtest_equity_payload( $bt_id ) {
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
			$t = isset( $point['t'] ) ? $point['t'] : ( isset( $point['timestamp'] ) ? $point['timestamp'] : null );
			$v = isset( $point['v'] ) ? $point['v'] : ( isset( $point['equity_value'] ) ? $point['equity_value'] : null );
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

/**
 * Build the drawdown chart payload { series:[{t:<int s>, dd:<float fraction>}] }
 * from drawdown_series_json. Tolerates {series:[{t,dd}]}.
 */
if ( ! function_exists( 'na_backtest_drawdown_payload' ) ) {
	function na_backtest_drawdown_payload( $bt_id ) {
		$out = array( 'series' => array() );
		if ( ! $bt_id ) {
			return $out;
		}
		$raw = get_post_meta( $bt_id, 'drawdown_series_json', true );
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
			$t  = isset( $point['t'] ) ? $point['t'] : ( isset( $point['timestamp'] ) ? $point['timestamp'] : null );
			$dd = isset( $point['dd'] ) ? $point['dd'] : ( isset( $point['drawdown'] ) ? $point['drawdown'] : null );
			if ( ! is_numeric( $t ) || ! is_numeric( $dd ) ) {
				continue;
			}
			$out['series'][] = array(
				't'  => (int) $t,
				'dd' => (float) $dd,
			);
		}
		return $out;
	}
}

get_header();

while ( have_posts() ) :
	the_post();

	$na_id = get_the_ID();

	// ---- meta helpers -------------------------------------------------
	$na_raw = function ( $key ) use ( $na_id ) {
		$val = get_post_meta( $na_id, $key, true );
		return ( null === $val ) ? '' : $val;
	};
	$na_num = function ( $key ) use ( $na_id ) {
		$val = get_post_meta( $na_id, $key, true );
		return ( '' === $val || null === $val || ! is_numeric( $val ) ) ? null : (float) $val;
	};
	$na_money = function ( $v, $dec = 0 ) {
		if ( null === $v ) {
			return '—';
		}
		$sign = ( $v < 0 ) ? '-$' : '$';
		return $sign . number_format( abs( $v ), $dec );
	};
	$na_pct = function ( $v, $dec = 2 ) {
		return ( null === $v ) ? '—' : number_format( $v, $dec ) . '%';
	};
	$na_rat = function ( $v, $dec = 2 ) {
		return ( null === $v ) ? '—' : number_format( $v, $dec );
	};
	$na_int = function ( $v ) {
		return ( null === $v ) ? '—' : number_format( $v, 0 );
	};
	$clamp = function ( $v, $min = 0, $max = 100 ) {
		if ( null === $v ) {
			return 0;
		}
		return max( $min, min( $max, $v ) );
	};

	// ---- core values --------------------------------------------------
	$na_instrument = $na_raw( 'instrument_meta_field' );
	$na_tf         = $na_raw( 'time_frame_meta_field' );
	$na_p_start    = $na_raw( 'backtest_period_start_meta_field' );
	$na_p_end      = $na_raw( 'backtest_period_end_meta_field' );
	$na_pips       = $na_raw( 'profit_in_pips_meta_field' );

	$na_profit  = $na_num( 'total_profit_meta_field' );
	$na_gprofit = $na_num( 'gross_profit_meta_field' );
	$na_gloss   = $na_num( 'gross_loss_meta_field' );
	$na_cagr    = $na_num( 'cagr_meta_field' );
	$na_win     = $na_num( 'winning_percentage_meta_field' );
	$na_pf      = $na_num( 'profit_factor_meta_field' );
	$na_dd_pct  = $na_num( 'drawdown_percent_meta_field' );
	$na_dd_amt  = $na_num( 'drawdown_meta_field' );
	$na_sharpe  = $na_num( 'sharpe_ratio_meta_field' );
	$na_sortino = $na_num( 'sortino_ratio' );

	$na_trades  = $na_num( 'number_of_trades_meta_field' );
	$na_wins    = $na_num( 'number_of_wins_meta_field' );
	$na_losses  = $na_num( 'number_of_losses_meta_field' );
	$na_avg_win = $na_num( 'avg_win_meta_field' );
	$na_avg_los = $na_num( 'avg_loss_meta_field' );
	$na_lg_win  = $na_num( 'largest_win_meta_field' );
	$na_lg_los  = $na_num( 'largest_loss_meta_field' );
	$na_wlr     = $na_num( 'win_loss_ratio_meta_field' );
	$na_payout  = $na_num( 'payout_ratio_meta_field' );
	$na_expect  = $na_num( 'expectancy_meta_field' );
	$na_avgtr   = $na_num( 'avg_trade_meta_field' );
	$na_bit     = $na_num( 'bars_in_trade_meta_field' );
	$na_mcw     = $na_num( 'max_consec_wins_meta_field' );
	$na_mcl     = $na_num( 'max_consec_losses_meta_field' );
	$na_acw     = $na_num( 'avg_consec_win_meta_field' );
	$na_acl     = $na_num( 'avg_consec_loss_meta_field' );

	$na_strategy_id  = $na_raw( 'strategy_id' );
	$na_strategy_url = ( $na_strategy_id && is_numeric( $na_strategy_id ) ) ? get_permalink( (int) $na_strategy_id ) : '';

	// ---- chart payloads ----------------------------------------------
	$na_eq      = na_backtest_equity_payload( $na_id );
	$na_dds     = na_backtest_drawdown_payload( $na_id );
	$na_has_eq  = ! empty( $na_eq['series'] );
	$na_has_dd  = ! empty( $na_dds['series'] );
	$na_inst    = 'na-chart-bt' . $na_id;

	$g_win = $clamp( $na_win );
	$g_dd  = $clamp( $na_dd_pct );
	$g_pf  = $clamp( ( null === $na_pf ) ? 0 : ( $na_pf / 3 ) * 100 );
	$g_cag = $clamp( ( null === $na_cagr ) ? 0 : ( $na_cagr / 30 ) * 100 );

	$na_total_wl = ( ( $na_wins ? $na_wins : 0 ) + ( $na_losses ? $na_losses : 0 ) );
	$g_winshare  = ( $na_total_wl > 0 ) ? ( $na_wins / $na_total_wl ) * 100 : 0;

	$cmp_avg_max = max( abs( $na_avg_win ? $na_avg_win : 0 ), abs( $na_avg_los ? $na_avg_los : 0 ), 1 );
	$cmp_lg_max  = max( abs( $na_lg_win ? $na_lg_win : 0 ), abs( $na_lg_los ? $na_lg_los : 0 ), 1 );

	$na_month_html = $na_raw( 'each_month_profits_meta_field' );
	$na_year_html  = $na_raw( 'each_year_profits_meta_field' );
	?>
	<main class="na-backtest-single" id="na-backtest-single">

		<section class="win glow hero">
			<div class="winbar">
				<span class="dots"><i></i><i></i><i></i></span>
				<span class="fname">~/backtests/<b><?php echo esc_html( sanitize_title( get_the_title() ) ); ?></b></span>
				<span class="wstat">live</span>
			</div>
			<div class="winbody">
				<p class="crumb"><span class="p">$</span> cd /backtests &nbsp;·&nbsp; <a href="<?php echo esc_url( get_post_type_archive_link( 'backtest' ) ); ?>">all runs</a></p>
				<p class="tagline">Backtest Report</p>
				<h1><?php the_title(); ?></h1>
				<div class="subline">
					<?php if ( $na_instrument ) : ?><span class="k"><?php echo esc_html( $na_instrument ); ?></span><?php endif; ?>
					<?php if ( $na_tf ) : ?><span class="k"><?php echo esc_html( $na_tf ); ?></span><?php endif; ?>
					<?php if ( $na_p_start && $na_p_end ) : ?><span class="k"><?php echo esc_html( $na_p_start . ' → ' . $na_p_end ); ?></span><?php endif; ?>
				</div>
				<div class="statusline">
					<?php if ( null !== $na_profit ) : ?><span class="chip">net <b class="<?php echo $na_profit >= 0 ? 'pos' : 'neg'; ?>"><?php echo esc_html( $na_money( $na_profit ) ); ?></b></span><?php endif; ?>
					<?php if ( null !== $na_trades ) : ?><span class="chip">trades <b><?php echo esc_html( $na_int( $na_trades ) ); ?></b></span><?php endif; ?>
					<?php if ( null !== $na_win ) : ?><span class="chip">win <b><?php echo esc_html( $na_pct( $na_win ) ); ?></b></span><?php endif; ?>
				</div>
				<?php if ( $na_strategy_url ) : ?>
					<div class="hero-cta"><a class="btn btn-primary" href="<?php echo esc_url( $na_strategy_url ); ?>">view strategy</a></div>
				<?php endif; ?>
			</div>
		</section>

		<section class="win">
			<div class="winbar"><span class="dots"><i></i><i></i><i></i></span><span class="fname"><b>at-a-glance</b> --summary</span><span class="wstat">ok</span></div>
			<div class="winbody">
				<p class="cmd"><span class="p">$</span> stats <span class="arg">--glance</span><span class="caret"></span></p>
				<p class="note">headline performance at a glance</p>
				<div class="glance">
					<div class="netlet">
						<div class="glet-bar"><span>net_profit</span><span class="s"></span></div>
						<div class="netlet-in">
							<span class="nlabel">Total net profit</span>
							<span class="nval <?php echo ( null !== $na_profit && $na_profit < 0 ) ? 'neg' : 'pos'; ?>"><?php echo esc_html( $na_money( $na_profit ) ); ?></span>
							<div class="nrow"><span>gross profit</span><span class="v pos"><?php echo esc_html( $na_money( $na_gprofit ) ); ?></span></div>
							<div class="nrow"><span>gross loss</span><span class="v neg"><?php echo esc_html( $na_money( $na_gloss ) ); ?></span></div>
						</div>
					</div>
					<div class="gauges">
						<?php
						$na_gauges = array(
							array( 'bar' => 'win_rate', 'fill' => $g_win, 'color' => 'var(--bt-green)', 'val' => $na_pct( $na_win ), 'sub' => 'WIN', 'label' => 'Win rate', 'gsub' => ( null !== $na_wins && null !== $na_losses ) ? ( $na_int( $na_wins ) . 'W / ' . $na_int( $na_losses ) . 'L' ) : '' ),
							array( 'bar' => 'profit_factor', 'fill' => $g_pf, 'color' => 'var(--bt-cyan)', 'val' => $na_rat( $na_pf ), 'sub' => 'PF', 'label' => 'Profit factor', 'gsub' => 'gross P / gross L' ),
							array( 'bar' => 'cagr', 'fill' => $g_cag, 'color' => 'var(--bt-accent)', 'val' => $na_pct( $na_cagr ), 'sub' => 'CAGR', 'label' => 'CAGR', 'gsub' => 'annualized' ),
							array( 'bar' => 'max_drawdown', 'fill' => $g_dd, 'color' => 'var(--bt-red)', 'val' => ( null !== $na_dd_pct ? '-' . number_format( abs( $na_dd_pct ), 2 ) . '%' : '—' ), 'sub' => 'MAX DD', 'label' => 'Max drawdown', 'gsub' => ( null !== $na_dd_amt ? $na_money( $na_dd_amt ) : '' ) ),
						);
						foreach ( $na_gauges as $gx ) :
							$deg       = round( $gx['fill'] * 3.6 );
							$style     = 'background:conic-gradient(' . $gx['color'] . ' ' . $deg . 'deg,var(--bt-border-soft) 0);';
							$blocks    = (int) round( $gx['fill'] / 10 );
							$ascii     = str_repeat( '█', $blocks );
							$ascii_dim = str_repeat( '░', 10 - $blocks );
							?>
							<div class="glet">
								<div class="glet-bar"><span><?php echo esc_html( $gx['bar'] ); ?></span><span class="s"></span></div>
								<div class="glet-in">
									<div class="gauge" style="<?php echo esc_attr( $style ); ?>">
										<div class="gc"><span class="gv"><?php echo esc_html( $gx['val'] ); ?></span><span class="gs"><?php echo esc_html( $gx['sub'] ); ?></span></div>
									</div>
									<div class="gmeta">
										<div class="asciibar"><?php echo esc_html( $ascii ); ?><span class="dim"><?php echo esc_html( $ascii_dim ); ?></span></div>
										<div class="glabel"><?php echo esc_html( $gx['label'] ); ?></div>
										<?php if ( $gx['gsub'] ) : ?><div class="gsub"><?php echo esc_html( $gx['gsub'] ); ?></div><?php endif; ?>
									</div>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		</section>

		<?php if ( $na_has_eq ) : ?>
		<section class="win">
			<div class="winbar"><span class="dots"><i></i><i></i><i></i></span><span class="fname"><b>equity_curve</b> --render</span><span class="wstat">ok</span></div>
			<div class="winbody">
				<p class="cmd"><span class="p">$</span> plot <span class="arg">--equity</span><span class="caret"></span></p>
				<p class="note">cumulative account equity over the test window</p>
				<div class="chart-box">
					<div id="<?php echo esc_attr( $na_inst ); ?>-equity"><div class="na-chart-content-area"></div></div>
				</div>
				<div class="chart-cap"><span>x: time · y: equity (USD)</span><span class="ok">● rendered by na-backtest-charts</span></div>
				<script type="application/json" id="<?php echo esc_attr( $na_inst ); ?>-equity-data"><?php echo wp_json_encode( $na_eq, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ); ?></script>

				<?php if ( $na_has_dd ) : ?>
					<p class="cmd" style="margin-top:1.4rem"><span class="p">$</span> plot <span class="arg">--drawdown</span><span class="caret"></span></p>
					<p class="note">underwater equity (peak-to-trough)</p>
					<div class="chart-box">
						<div id="<?php echo esc_attr( $na_inst ); ?>-drawdown"><div class="na-chart-content-area"></div></div>
					</div>
					<div class="chart-cap"><span>x: time · y: drawdown (%)</span><span class="ok">● rendered by na-backtest-charts</span></div>
					<script type="application/json" id="<?php echo esc_attr( $na_inst ); ?>-drawdown-data"><?php echo wp_json_encode( $na_dds, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ); ?></script>
				<?php endif; ?>
			</div>
		</section>
		<?php endif; ?>

		<section class="win">
			<div class="winbar"><span class="dots"><i></i><i></i><i></i></span><span class="fname"><b>trade_breakdown</b> --analyze</span><span class="wstat warn">partial</span></div>
			<div class="winbody">
				<p class="cmd"><span class="p">$</span> trades <span class="arg">--breakdown</span><span class="caret"></span></p>
				<p class="note">win/loss split, averages, and streaks</p>
				<div class="tb-grid">
					<div class="tlet">
						<div class="glet-bar"><span>win_loss_split</span><span class="s"></span></div>
						<div class="tlet-in">
							<div class="donut-wrap">
								<div class="donut" style="<?php echo esc_attr( 'background:conic-gradient(var(--bt-green) ' . round( $g_winshare * 3.6 ) . 'deg,var(--bt-red) 0);' ); ?>">
									<div class="dc"><b><?php echo esc_html( $na_pct( $na_win ) ); ?></b><span>win</span></div>
								</div>
								<div class="legend">
									<div class="row"><span class="dot" style="background:var(--bt-green)"></span>Wins<span class="v"><?php echo esc_html( $na_int( $na_wins ) ); ?></span></div>
									<div class="row"><span class="dot" style="background:var(--bt-red)"></span>Losses<span class="v"><?php echo esc_html( $na_int( $na_losses ) ); ?></span></div>
									<div class="row"><span class="dot" style="background:var(--bt-border)"></span>Total<span class="v"><?php echo esc_html( $na_int( $na_trades ) ); ?></span></div>
								</div>
							</div>
						</div>
					</div>
					<div class="tlet">
						<div class="glet-bar"><span>win_vs_loss_size</span><span class="s"></span></div>
						<div class="tlet-in">
							<div class="cmp">
								<div class="line"><div class="top"><span>Avg win</span><span class="num pos"><?php echo esc_html( $na_money( $na_avg_win, 2 ) ); ?></span></div><div class="bar"><i style="<?php echo esc_attr( 'width:' . round( abs( $na_avg_win ? $na_avg_win : 0 ) / $cmp_avg_max * 100 ) . '%;background:var(--bt-green)' ); ?>"></i></div></div>
								<div class="line"><div class="top"><span>Avg loss</span><span class="num neg"><?php echo esc_html( $na_money( $na_avg_los, 2 ) ); ?></span></div><div class="bar"><i style="<?php echo esc_attr( 'width:' . round( abs( $na_avg_los ? $na_avg_los : 0 ) / $cmp_avg_max * 100 ) . '%;background:var(--bt-red)' ); ?>"></i></div></div>
								<div class="line"><div class="top"><span>Largest win</span><span class="num pos"><?php echo esc_html( $na_money( $na_lg_win, 2 ) ); ?></span></div><div class="bar"><i style="<?php echo esc_attr( 'width:' . round( abs( $na_lg_win ? $na_lg_win : 0 ) / $cmp_lg_max * 100 ) . '%;background:var(--bt-green)' ); ?>"></i></div></div>
								<div class="line"><div class="top"><span>Largest loss</span><span class="num neg"><?php echo esc_html( $na_money( $na_lg_los, 2 ) ); ?></span></div><div class="bar"><i style="<?php echo esc_attr( 'width:' . round( abs( $na_lg_los ? $na_lg_los : 0 ) / $cmp_lg_max * 100 ) . '%;background:var(--bt-red)' ); ?>"></i></div></div>
							</div>
						</div>
					</div>
					<div class="tlet">
						<div class="glet-bar"><span>key_ratios</span><span class="s"></span></div>
						<div class="tlet-in">
							<div class="mini"><span class="k">Profit factor</span><span class="v"><?php echo esc_html( $na_rat( $na_pf ) ); ?></span></div>
							<div class="mini"><span class="k">Win/Loss ratio</span><span class="v"><?php echo esc_html( $na_rat( $na_wlr ) ); ?></span></div>
							<div class="mini"><span class="k">Payout ratio</span><span class="v"><?php echo esc_html( $na_rat( $na_payout ) ); ?></span></div>
							<div class="mini"><span class="k">Expectancy</span><span class="v"><?php echo esc_html( $na_money( $na_expect, 2 ) ); ?></span></div>
							<div class="mini"><span class="k">Avg trade</span><span class="v"><?php echo esc_html( $na_money( $na_avgtr, 2 ) ); ?></span></div>
							<div class="mini"><span class="k">Bars in trade</span><span class="v"><?php echo esc_html( $na_rat( $na_bit ) ); ?></span></div>
						</div>
					</div>
					<div class="tlet">
						<div class="glet-bar"><span>streaks</span><span class="s"></span></div>
						<div class="tlet-in">
							<div class="mini"><span class="k">Max consec wins</span><span class="v pos"><?php echo esc_html( $na_int( $na_mcw ) ); ?></span></div>
							<div class="mini"><span class="k">Max consec losses</span><span class="v neg"><?php echo esc_html( $na_int( $na_mcl ) ); ?></span></div>
							<div class="mini"><span class="k">Avg consec wins</span><span class="v"><?php echo esc_html( $na_rat( $na_acw ) ); ?></span></div>
							<div class="mini"><span class="k">Avg consec losses</span><span class="v"><?php echo esc_html( $na_rat( $na_acl ) ); ?></span></div>
							<div class="flag">Long vs short breakdown and per-trade distributions are not in the ingested dataset yet.</div>
						</div>
					</div>
				</div>
			</div>
		</section>

		<section class="win">
			<div class="winbar"><span class="dots"><i></i><i></i><i></i></span><span class="fname"><b>full_statistics</b> --all</span><span class="wstat">ok</span></div>
			<div class="winbody">
				<p class="cmd"><span class="p">$</span> stats <span class="arg">--full</span><span class="caret"></span></p>
				<p class="note">complete metric set, grouped by category</p>
				<div class="tabs" role="tablist">
					<button class="tab active" data-tab="perf"><span class="p">&gt;</span> performance</button>
					<button class="tab" data-tab="risk"><span class="p">&gt;</span> risk</button>
					<button class="tab" data-tab="trade"><span class="p">&gt;</span> trade</button>
				</div>
				<?php
				$na_metric_card = function ( $label, $value ) {
					echo '<div class="metric"><div class="v">' . esc_html( $value ) . '</div><div class="l">' . esc_html( $label ) . '</div></div>';
				};
				?>
				<div class="tabpane active" data-pane="perf">
					<div class="mgrid">
						<?php
						$na_metric_card( 'Net profit', $na_money( $na_profit ) );
						$na_metric_card( 'Gross profit', $na_money( $na_gprofit ) );
						$na_metric_card( 'Gross loss', $na_money( $na_gloss ) );
						$na_metric_card( 'CAGR', $na_pct( $na_cagr ) );
						$na_metric_card( 'AHPR', $na_rat( $na_num( 'ahpr_meta_field' ) ) );
						$na_metric_card( 'Yearly avg profit', $na_money( $na_num( 'yearly_avg_profit_meta_field' ) ) );
						$na_metric_card( 'Monthly avg profit', $na_money( $na_num( 'monthly_avg_profit_meta_field' ) ) );
						$na_metric_card( 'Daily avg profit', $na_money( $na_num( 'daily_avg_profit_meta_field' ), 2 ) );
						$na_metric_card( 'Yearly avg return', $na_pct( $na_num( 'yearly_avg_return_meta_field' ) ) );
						$na_metric_card( 'Exposure', $na_pct( $na_num( 'exposure_meta_field' ) ) );
						$na_metric_card( 'Trades', $na_int( $na_trades ) );
						$na_metric_card( 'Avg trade', $na_money( $na_avgtr, 2 ) );
						?>
					</div>
				</div>
				<div class="tabpane" data-pane="risk">
					<div class="mgrid">
						<?php
						$na_metric_card( 'Max drawdown', ( null !== $na_dd_pct ? '-' . number_format( abs( $na_dd_pct ), 2 ) . '%' : '—' ) );
						$na_metric_card( 'Max drawdown ($)', $na_money( $na_dd_amt ) );
						$na_metric_card( 'Sharpe ratio', $na_rat( $na_sharpe ) );
						$na_metric_card( 'Sortino ratio', $na_rat( $na_sortino ) );
						$na_metric_card( 'Profit factor', $na_rat( $na_pf ) );
						$na_metric_card( 'Return / DD', $na_rat( $na_num( 'return_devide_dd_meta_field' ) ) );
						$na_metric_card( 'Annual ret / maxDD', $na_rat( $na_num( 'annual_return_divide_maxdd_meta_field' ) ) );
						$na_metric_card( 'Stagnation (days)', $na_int( $na_num( 'stagnation_in_days_meta_field' ) ) );
						$na_metric_card( 'Stagnation (%)', $na_pct( $na_num( 'stagnation_in_percent_meta_field' ) ) );
						$na_metric_card( 'Deviation', $na_rat( $na_num( 'deviation_meta_field' ) ) );
						$na_metric_card( 'Z-score', $na_rat( $na_num( 'z_score_meta_field' ) ) );
						$na_metric_card( 'Z-probability', $na_pct( $na_num( 'z_probability_meta_field' ) ) );
						?>
					</div>
				</div>
				<div class="tabpane" data-pane="trade">
					<div class="mgrid">
						<?php
						$na_metric_card( 'Trades', $na_int( $na_trades ) );
						$na_metric_card( 'Wins', $na_int( $na_wins ) );
						$na_metric_card( 'Losses', $na_int( $na_losses ) );
						$na_metric_card( 'Win rate', $na_pct( $na_win ) );
						$na_metric_card( 'Win/Loss ratio', $na_rat( $na_wlr ) );
						$na_metric_card( 'Payout ratio', $na_rat( $na_payout ) );
						$na_metric_card( 'Avg win', $na_money( $na_avg_win, 2 ) );
						$na_metric_card( 'Avg loss', $na_money( $na_avg_los, 2 ) );
						$na_metric_card( 'Largest win', $na_money( $na_lg_win, 2 ) );
						$na_metric_card( 'Largest loss', $na_money( $na_lg_los, 2 ) );
						$na_metric_card( 'Max consec wins', $na_int( $na_mcw ) );
						$na_metric_card( 'Max consec losses', $na_int( $na_mcl ) );
						?>
					</div>
				</div>
			</div>
		</section>

		<?php if ( $na_month_html || $na_year_html ) : ?>
		<section class="win">
			<div class="winbar"><span class="dots"><i></i><i></i><i></i></span><span class="fname"><b>periodic_returns</b> --table</span><span class="wstat">ok</span></div>
			<div class="winbody">
				<p class="cmd"><span class="p">$</span> returns <span class="arg">--by-period</span><span class="caret"></span></p>
				<p class="note">profit broken down by month and year</p>
				<?php if ( $na_year_html ) : ?>
					<h3 class="subh">by year</h3>
					<div class="tbl-wrap"><table><?php echo wp_kses_post( $na_year_html ); ?></table></div>
				<?php endif; ?>
				<?php if ( $na_month_html ) : ?>
					<h3 class="subh">by month</h3>
					<div class="tbl-wrap"><table><?php echo wp_kses_post( $na_month_html ); ?></table></div>
				<?php endif; ?>
			</div>
		</section>
		<?php endif; ?>

		<section class="win">
			<div class="winbar"><span class="dots"><i></i><i></i><i></i></span><span class="fname"><b>run.config</b></span><span class="wstat">ok</span></div>
			<div class="winbody">
				<p class="cmd"><span class="p">$</span> cat <span class="arg">run.config</span><span class="caret"></span></p>
				<p class="note">parameters used for this run</p>
				<div class="params">
					<div class="param"><span class="k">instrument</span><span class="v"><?php echo esc_html( $na_instrument ? $na_instrument : '—' ); ?></span></div>
					<div class="param"><span class="k">timeframe</span><span class="v"><?php echo esc_html( $na_tf ? $na_tf : '—' ); ?></span></div>
					<div class="param"><span class="k">period start</span><span class="v"><?php echo esc_html( $na_p_start ? $na_p_start : '—' ); ?></span></div>
					<div class="param"><span class="k">period end</span><span class="v"><?php echo esc_html( $na_p_end ? $na_p_end : '—' ); ?></span></div>
					<div class="param"><span class="k">profit in pips</span><span class="v"><?php echo esc_html( $na_pips ? $na_pips : '—' ); ?></span></div>
					<div class="param"><span class="k">strategy</span><span class="v"><?php echo $na_strategy_url ? '<a href="' . esc_url( $na_strategy_url ) . '" style="color:var(--bt-cyan);text-decoration:none">view →</a>' : '—'; ?></span></div>
				</div>
			</div>
		</section>

		<section class="win">
			<div class="winbody">
				<div class="disc">Trading financial instruments carries a high level of risk and may not be suitable for all investors. Past performance — including backtested and simulated results — is not indicative of future results. NeuronAlgo provides research and tooling, not financial advice.</div>
			</div>
		</section>

		<section class="win glow cta">
			<div class="winbar"><span class="dots"><i></i><i></i><i></i></span><span class="fname"><b>next</b></span><span class="wstat">ok</span></div>
			<div class="winbody">
				<h2>Explore the strategy behind this run</h2>
				<p>See the full methodology, risk profile, and live track record — or browse every backtest in the library.</p>
				<div class="cta-actions">
					<?php if ( $na_strategy_url ) : ?><a class="btn btn-primary" href="<?php echo esc_url( $na_strategy_url ); ?>">view strategy</a><?php endif; ?>
					<a class="btn" href="<?php echo esc_url( get_post_type_archive_link( 'backtest' ) ); ?>">all backtests</a>
				</div>
			</div>
		</section>

		<p class="footnote">Generated from stored backtest metrics · NeuronAlgo research desk</p>

	</main>

	<script>
	( function () {
		var root = document.getElementById( 'na-backtest-single' );
		if ( ! root ) { return; }
		var tabs = root.querySelectorAll( '.tab[data-tab]' );
		var panes = root.querySelectorAll( '.tabpane[data-pane]' );
		tabs.forEach( function ( tab ) {
			tab.addEventListener( 'click', function () {
				var key = tab.getAttribute( 'data-tab' );
				tabs.forEach( function ( t ) { t.classList.remove( 'active' ); } );
				panes.forEach( function ( p ) { p.classList.toggle( 'active', p.getAttribute( 'data-pane' ) === key ); } );
				tab.classList.add( 'active' );
			} );
		} );
	} )();
	</script>

	<?php
endwhile;

get_footer();
