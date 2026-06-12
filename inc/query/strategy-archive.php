<?php
/**
 * Strategy Library — query layer + helpers (FE-3.1).
 *
 * Presentation-only support for the `strategy` CPT archive:
 *  - Server-side taxonomy filtering via ?na_<taxonomy> query params.
 *  - A reusable helper to resolve a strategy's flagship backtest so cards
 *    (and, later, single-strategy templates) can surface a headline metric.
 *
 * All business logic stays in neuronalgo-core; this only shapes WP_Query.
 *
 * @package astra-child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'na_strategy_archive_filter_map' ) ) {
	/**
	 * Map of public query vars -> taxonomy slugs used on the archive.
	 *
	 * @return array<string,string>
	 */
	function na_strategy_archive_filter_map() {
		return array(
			'na_market'        => 'market',
			'na_asset_class'   => 'asset_class',
			'na_timeframe'     => 'timeframe',
			'na_strategy_type' => 'strategy_type',
			'na_risk_level'    => 'risk_level',
		);
	}
}

if ( ! function_exists( 'na_strategy_archive_pre_get_posts' ) ) {
	/**
	 * Apply taxonomy filters + paging to the strategy archive main query.
	 *
	 * @param WP_Query $query The query object.
	 * @return void
	 */
	function na_strategy_archive_pre_get_posts( $query ) {
		if ( is_admin() || ! $query->is_main_query() ) {
			return;
		}
		if ( ! $query->is_post_type_archive( 'strategy' ) ) {
			return;
		}

		$tax_query = array();
		foreach ( na_strategy_archive_filter_map() as $param => $taxonomy ) {
			if ( empty( $_GET[ $param ] ) || ! taxonomy_exists( $taxonomy ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				continue;
			}
			$slug = sanitize_title( wp_unslash( $_GET[ $param ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( '' === $slug ) {
				continue;
			}
			$tax_query[] = array(
				'taxonomy' => $taxonomy,
				'field'    => 'slug',
				'terms'    => $slug,
			);
		}

		if ( count( $tax_query ) > 1 ) {
			$tax_query['relation'] = 'AND';
		}
		if ( ! empty( $tax_query ) ) {
			$query->set( 'tax_query', $tax_query ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
		}

		$sort = isset( $_GET['na_sort'] ) ? sanitize_key( wp_unslash( $_GET['na_sort'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( 'title' === $sort ) {
			$query->set( 'orderby', 'title' );
			$query->set( 'order', 'ASC' );
		}

		$query->set( 'posts_per_page', 12 );
	}
	add_action( 'pre_get_posts', 'na_strategy_archive_pre_get_posts' );
}

if ( ! function_exists( 'na_strategy_flagship_backtest' ) ) {
	/**
	 * Resolve a strategy's flagship backtest (best published Sharpe).
	 *
	 * Backtests reference their strategy via the `strategy_id` meta key.
	 * Sharpe ties are broken by ID DESC so the most recent / data-complete
	 * backtest wins. Returns 0 when no published backtest is linked.
	 *
	 * @param int $strategy_id Strategy post ID.
	 * @return int Backtest post ID, or 0.
	 */
	function na_strategy_flagship_backtest( $strategy_id ) {
		$strategy_id = (int) $strategy_id;
		if ( ! $strategy_id ) {
			return 0;
		}

		$cache_key = 'na_flagship_bt_' . $strategy_id;
		$cached    = get_transient( $cache_key );
		if ( false !== $cached ) {
			return (int) $cached;
		}

		$q = new WP_Query(
			array(
				'post_type'      => 'backtest',
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'no_found_rows'  => true,
				'orderby'        => array(
					'meta_value_num' => 'DESC',
					'ID'             => 'DESC',
				),
				'meta_key'       => 'sharpe_ratio_meta_field', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					'relation' => 'AND',
					array(
						'key'     => 'sharpe_ratio_meta_field',
						'compare' => 'EXISTS',
					),
					array(
						'key'     => 'strategy_id',
						'value'   => $strategy_id,
						'compare' => '=',
					),
				),
			)
		);

		$backtest_id = $q->have_posts() ? (int) $q->posts[0]->ID : 0;
		wp_reset_postdata();

		set_transient( $cache_key, $backtest_id, 10 * MINUTE_IN_SECONDS );

		return $backtest_id;
	}
}
