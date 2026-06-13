<?php
/**
 * Backtest Runs — query layer + helpers (FE-3.4).
 *
 * Presentation-only support for the `backtest` CPT archive:
 *  - Server-side filtering via ?na_instrument / ?na_timeframe / ?na_strategy
 *    (meta-based, since instrument + strategy are stored as post meta, not
 *    taxonomy terms) plus ?na_sort ordering by headline metrics.
 *  - Helpers to populate the filter dropdowns from distinct meta values and
 *    to build a tiny inline-SVG equity sparkline for the run cards.
 *
 * All business logic stays in neuronalgo-core; this only shapes WP_Query and
 * reads meta written by the ingestion pipeline.
 *
 * @package astra-child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'na_backtest_archive_filter_map' ) ) {
	/**
	 * Map of public query vars -> backtest meta keys used on the archive.
	 *
	 * @return array<string,string>
	 */
	function na_backtest_archive_filter_map() {
		return array(
			'na_instrument' => 'instrument_meta_field',
			'na_timeframe'  => 'time_frame_meta_field',
		);
	}
}

if ( ! function_exists( 'na_backtest_archive_sort_map' ) ) {
	/**
	 * Map of ?na_sort values -> numeric meta keys for ordering.
	 *
	 * @return array<string,string>
	 */
	function na_backtest_archive_sort_map() {
		return array(
			'net' => 'total_profit_meta_field',
			'win' => 'winning_percentage_meta_field',
			'pf'  => 'profit_factor_meta_field',
			'dd'  => 'drawdown_percent_meta_field',
		);
	}
}

if ( ! function_exists( 'na_backtest_archive_pre_get_posts' ) ) {
	/**
	 * Apply meta filters, sorting + paging to the backtest archive main query.
	 *
	 * @param WP_Query $query The query object.
	 * @return void
	 */
	function na_backtest_archive_pre_get_posts( $query ) {
		if ( is_admin() || ! $query->is_main_query() ) {
			return;
		}
		if ( ! $query->is_post_type_archive( 'backtest' ) ) {
			return;
		}

		$meta_query = array();
		foreach ( na_backtest_archive_filter_map() as $param => $meta_key ) {
			if ( empty( $_GET[ $param ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				continue;
			}
			$val = sanitize_text_field( wp_unslash( $_GET[ $param ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( '' === $val ) {
				continue;
			}
			$meta_query[] = array(
				'key'     => $meta_key,
				'value'   => $val,
				'compare' => '=',
			);
		}

		if ( ! empty( $_GET['na_strategy'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$sid = absint( wp_unslash( $_GET['na_strategy'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( $sid ) {
				$meta_query[] = array(
					'key'     => 'strategy_id',
					'value'   => $sid,
					'compare' => '=',
				);
			}
		}

		if ( ! empty( $meta_query ) ) {
			if ( count( $meta_query ) > 1 ) {
				$meta_query['relation'] = 'AND';
			}
			$query->set( 'meta_query', $meta_query ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		}

		$sort     = isset( $_GET['na_sort'] ) ? sanitize_key( wp_unslash( $_GET['na_sort'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$sort_map = na_backtest_archive_sort_map();
		if ( isset( $sort_map[ $sort ] ) ) {
			$query->set( 'meta_key', $sort_map[ $sort ] ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			$query->set( 'orderby', 'meta_value_num' );
			// Lowest drawdown is "best", so ascending; everything else descending.
			$query->set( 'order', ( 'dd' === $sort ) ? 'ASC' : 'DESC' );
		} else {
			$query->set( 'orderby', 'date' );
			$query->set( 'order', 'DESC' );
		}

		$query->set( 'posts_per_page', 12 );
	}
	add_action( 'pre_get_posts', 'na_backtest_archive_pre_get_posts' );
}

if ( ! function_exists( 'na_backtest_distinct_meta' ) ) {
	/**
	 * Collect distinct non-empty meta values for published backtests.
	 *
	 * Used to populate the instrument / timeframe filter dropdowns. Cached for
	 * 10 minutes to avoid repeated scans during browsing.
	 *
	 * @param string $meta_key The meta key to scan.
	 * @return array<int,string> Sorted list of distinct values.
	 */
	function na_backtest_distinct_meta( $meta_key ) {
		global $wpdb;

		$meta_key  = (string) $meta_key;
		$cache_key = 'na_bt_distinct_' . md5( $meta_key );
		$cached    = get_transient( $cache_key );
		if ( false !== $cached && is_array( $cached ) ) {
			return $cached;
		}

		$values = $wpdb->get_col( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prepare(
				"SELECT DISTINCT pm.meta_value
				 FROM {$wpdb->postmeta} pm
				 INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
				 WHERE pm.meta_key = %s
				   AND pm.meta_value <> ''
				   AND p.post_type = 'backtest'
				   AND p.post_status = 'publish'
				 ORDER BY pm.meta_value ASC",
				$meta_key
			)
		);

		$values = is_array( $values ) ? $values : array();
		set_transient( $cache_key, $values, 10 * MINUTE_IN_SECONDS );

		return $values;
	}
}

if ( ! function_exists( 'na_backtest_strategy_options' ) ) {
	/**
	 * Build a [strategy_id => title] map of strategies that have backtests.
	 *
	 * @return array<int,string>
	 */
	function na_backtest_strategy_options() {
		$ids = na_backtest_distinct_meta( 'strategy_id' );
		$out = array();
		foreach ( $ids as $sid ) {
			$sid = (int) $sid;
			if ( ! $sid ) {
				continue;
			}
			$title = get_the_title( $sid );
			if ( '' !== $title ) {
				$out[ $sid ] = $title;
			}
		}
		natcasesort( $out );
		return $out;
	}
}

if ( ! function_exists( 'na_backtest_equity_spark' ) ) {
	/**
	 * Build inline-SVG polyline points for a backtest's equity sparkline.
	 *
	 * Reads the `equity_curve_json` meta ({series:[{t,v}]}), downsamples to at
	 * most $points, and normalises into a $w x $h viewBox (y inverted for SVG).
	 *
	 * @param int $id     Backtest post ID.
	 * @param int $points Max number of sampled points.
	 * @param int $w      viewBox width.
	 * @param int $h      viewBox height.
	 * @return array{line:string,area:string,up:bool}|null Null when no data.
	 */
	function na_backtest_equity_spark( $id, $points = 28, $w = 300, $h = 46 ) {
		$raw = get_post_meta( (int) $id, 'equity_curve_json', true );
		if ( empty( $raw ) ) {
			return null;
		}
		$data = json_decode( $raw, true );
		if ( ! is_array( $data ) || empty( $data['series'] ) || ! is_array( $data['series'] ) ) {
			return null;
		}

		$vals = array();
		foreach ( $data['series'] as $pt ) {
			if ( is_array( $pt ) && isset( $pt['v'] ) && is_numeric( $pt['v'] ) ) {
				$vals[] = (float) $pt['v'];
			}
		}
		$n = count( $vals );
		if ( $n < 2 ) {
			return null;
		}

		if ( $n > $points ) {
			$sampled = array();
			for ( $i = 0; $i < $points; $i++ ) {
				$idx       = (int) round( $i * ( $n - 1 ) / ( $points - 1 ) );
				$sampled[] = $vals[ $idx ];
			}
			$vals = $sampled;
			$n    = count( $vals );
		}

		$min   = min( $vals );
		$max   = max( $vals );
		$range = ( $max - $min );
		if ( $range <= 0 ) {
			$range = 1.0;
		}
		$pad    = 4;
		$coords = array();
		for ( $i = 0; $i < $n; $i++ ) {
			$x        = ( $n > 1 ) ? ( $i * $w / ( $n - 1 ) ) : 0;
			$y        = $pad + ( 1 - ( ( $vals[ $i ] - $min ) / $range ) ) * ( $h - 2 * $pad );
			$coords[] = round( $x, 1 ) . ',' . round( $y, 1 );
		}

		$line = implode( ' ', $coords );
		$area = $line . ' ' . $w . ',' . $h . ' 0,' . $h;
		$up   = ( end( $vals ) >= reset( $vals ) );

		return array(
			'line' => $line,
			'area' => $area,
			'up'   => $up,
		);
	}
}
