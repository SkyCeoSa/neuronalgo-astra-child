<?php
/**
 * NeuronAlgo — Single Backtest enhancements enqueue + hero data (FE-3.6).
 *
 * Loads assets/js/backtest-enhance.js + assets/css/sections/backtest-enhance.css
 * on single backtests at priority 23 (after the section, chart and
 * window-controls assets). The script is additive and non-destructive: it splits
 * the shared equity_curve card into two .win cards (Equity + Drawdown) for
 * independent zoom, and ports the approved terminal-desk hero (badges, animated
 * KPI strip, faint equity sparkline, secondary CTA). Clean hero values are
 * injected as a window.NA_HERO object; the long/short split also comes from
 * window.NA_TD (trade-distribution module). Percent metas are whole numbers and
 * are passed through as-is. Versioned by filemtime for cache-busting.
 *
 * Kept as a standalone enqueue file (instead of growing class-conditional-assets.php)
 * and wired via a require_once in functions.php.
 *
 * @package Astra Child
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function na_enqueue_backtest_enhance() {
    if ( ! is_singular( 'backtest' ) ) {
        return;
    }

    $base = get_stylesheet_directory();
    $uri  = get_stylesheet_directory_uri();

    $css_rel = '/assets/css/sections/backtest-enhance.css';
    $css_ver = file_exists( $base . $css_rel ) ? filemtime( $base . $css_rel ) : CHILD_THEME_ASTRA_CHILD_VERSION;
    wp_enqueue_style(
        'na-backtest-enhance',
        $uri . $css_rel,
        array( 'na-single-backtest' ),
        $css_ver,
        'all'
    );

    $js_rel = '/assets/js/backtest-enhance.js';
    $js_ver = file_exists( $base . $js_rel ) ? filemtime( $base . $js_rel ) : CHILD_THEME_ASTRA_CHILD_VERSION;
    wp_enqueue_script(
        'na-backtest-enhance',
        $uri . $js_rel,
        array(),
        $js_ver,
        true
    );

    // Inject clean hero values for the current backtest.
    $bt_id = get_queried_object_id();

    $na_num = function ( $key ) use ( $bt_id ) {
        $v = get_post_meta( $bt_id, $key, true );
        return is_numeric( $v ) ? (float) $v : null;
    };
    $na_str = function ( $key ) use ( $bt_id ) {
        $v = get_post_meta( $bt_id, $key, true );
        return ( null === $v ) ? '' : $v;
    };

    $long_only = false;
    $ls_raw    = get_post_meta( $bt_id, 'long_short_json', true );
    if ( is_string( $ls_raw ) && '' !== $ls_raw ) {
        $ls = json_decode( $ls_raw, true );
        if ( JSON_ERROR_NONE === json_last_error() && isset( $ls['long']['trades'], $ls['short']['trades'] ) ) {
            $long_only = ( 0 == $ls['short']['trades'] && $ls['long']['trades'] > 0 );
        }
    }

    $hero = array(
        'net'        => $na_num( 'total_profit_meta_field' ),
        'winRate'    => $na_num( 'winning_percentage_meta_field' ),
        'pf'         => $na_num( 'profit_factor_meta_field' ),
        'cagr'       => $na_num( 'cagr_meta_field' ),
        'trades'     => $na_num( 'number_of_trades_meta_field' ),
        'wins'       => $na_num( 'number_of_wins_meta_field' ),
        'losses'     => $na_num( 'number_of_losses_meta_field' ),
        'instrument' => $na_str( 'instrument_meta_field' ),
        'tf'         => $na_str( 'time_frame_meta_field' ),
        'pStart'     => $na_str( 'backtest_period_start_meta_field' ),
        'pEnd'       => $na_str( 'backtest_period_end_meta_field' ),
        'longOnly'   => $long_only,
    );

    wp_add_inline_script(
        'na-backtest-enhance',
        'window.NA_HERO = ' . wp_json_encode( $hero ) . ';',
        'before'
    );
}
add_action( 'wp_enqueue_scripts', 'na_enqueue_backtest_enhance', 23 );
