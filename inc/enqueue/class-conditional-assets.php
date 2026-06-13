<?php
/**
 * NeuronAlgo Conditional Assets Registration
 *
 * Registers chart assets for Elementor widget conditional loading.
 * Assets are registered but NOT enqueued globally - Elementor loads them
 * only where the widget is placed via get_script_depends()/get_style_depends().
 *
 * @package Astra Child
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register chart-related scripts and styles.
 * These are NOT enqueued globally - Elementor handles conditional loading.
 */
function na_register_chart_assets() {
    $version = CHILD_THEME_ASTRA_CHILD_VERSION;

    // ApexCharts vendor library (bundled locally for offline dev)
    wp_register_script(
        'na-apexcharts',
        get_stylesheet_directory_uri() . '/assets/js/vendors/apexcharts.min.js',
        array(),
        $version,
        true
    );

    // Chart bundle JS (will contain loader, validator, transformer, renderers)
    wp_register_script(
        'na-backtest-charts',
        get_stylesheet_directory_uri() . '/assets/js/charts/backtest-charts.js',
        array( 'na-apexcharts' ),
        $version,
        true
    );

    // Chart CSS
    wp_register_style(
        'na-backtest-charts',
        get_stylesheet_directory_uri() . '/assets/css/charts/backtest-charts.css',
        array(),
        $version,
        'all'
    );
}
add_action( 'wp_enqueue_scripts', 'na_register_chart_assets', 10 );

/**
 * Enqueue Strategy Library styles on the strategy CPT archive (FE-3.1).
 *
 * Loaded only on is_post_type_archive( 'strategy' ). Depends on the global
 * design tokens + components handles. Versioned by filemtime for cache-busting
 * during active development.
 */
function na_enqueue_strategy_library_assets() {
    if ( ! is_post_type_archive( 'strategy' ) ) {
        return;
    }

    $base = get_stylesheet_directory();
    $rel  = '/assets/css/sections/strategy-library.css';
    $ver  = file_exists( $base . $rel ) ? filemtime( $base . $rel ) : CHILD_THEME_ASTRA_CHILD_VERSION;

    wp_enqueue_style(
        'na-strategy-library',
        get_stylesheet_directory_uri() . $rel,
        array( 'na-design-tokens', 'na-components' ),
        $ver,
        'all'
    );
}
add_action( 'wp_enqueue_scripts', 'na_enqueue_strategy_library_assets', 20 );

/**
 * Enqueue Single Strategy styles + flagship equity chart assets (FE-3.2).
 *
 * Loaded only on is_singular( 'strategy' ). The chart bundle (na-apexcharts +
 * na-backtest-charts) is registered at priority 10 above; here we enqueue it for
 * the single-strategy equity curve. Section CSS is versioned by filemtime. The
 * strategy-library stylesheet is also enqueued so the related-strategy cards
 * (which reuse the FE-3.1 strategy-card markup) are styled on this template.
 */
function na_enqueue_single_strategy_assets() {
    if ( ! is_singular( 'strategy' ) ) {
        return;
    }

    $base = get_stylesheet_directory();
    $rel  = '/assets/css/sections/single-strategy.css';
    $ver  = file_exists( $base . $rel ) ? filemtime( $base . $rel ) : CHILD_THEME_ASTRA_CHILD_VERSION;

    wp_enqueue_style(
        'na-single-strategy',
        get_stylesheet_directory_uri() . $rel,
        array( 'na-design-tokens', 'na-components' ),
        $ver,
        'all'
    );

    // Related-strategy cards reuse the FE-3.1 strategy-card styles.
    $lib_rel = '/assets/css/sections/strategy-library.css';
    $lib_ver = file_exists( $base . $lib_rel ) ? filemtime( $base . $lib_rel ) : CHILD_THEME_ASTRA_CHILD_VERSION;

    wp_enqueue_style(
        'na-strategy-library',
        get_stylesheet_directory_uri() . $lib_rel,
        array( 'na-design-tokens', 'na-components' ),
        $lib_ver,
        'all'
    );

    // Flagship equity curve chart (reuses the registered chart bundle).
    wp_enqueue_script( 'na-apexcharts' );
    wp_enqueue_script( 'na-backtest-charts' );
    wp_enqueue_style( 'na-backtest-charts' );
}
add_action( 'wp_enqueue_scripts', 'na_enqueue_single_strategy_assets', 20 );

/**
 * Enqueue Single Backtest styles + equity/drawdown chart assets (FE-3.3).
 *
 * Loaded only on is_singular( 'backtest' ). The section stylesheet is
 * self-contained (its own scoped tokens under .na-backtest-single plus the
 * body.single-backtest shell neutralizer) so it has no style dependencies. A
 * tiny overrides stylesheet (na-single-backtest-overrides) is layered on top
 * for surgical post-launch fixes. The chart bundle (na-apexcharts +
 * na-backtest-charts), registered at priority 10 above, is enqueued so the
 * equity and drawdown charts auto-render via the na-backtest-charts loader.
 * Section CSS is versioned by filemtime for cache-busting during active
 * development.
 */
function na_enqueue_single_backtest_assets() {
    if ( ! is_singular( 'backtest' ) ) {
        return;
    }

    $base = get_stylesheet_directory();
    $rel  = '/assets/css/sections/single-backtest.css';
    $ver  = file_exists( $base . $rel ) ? filemtime( $base . $rel ) : CHILD_THEME_ASTRA_CHILD_VERSION;

    wp_enqueue_style(
        'na-single-backtest',
        get_stylesheet_directory_uri() . $rel,
        array(),
        $ver,
        'all'
    );

    // Surgical overrides / patches layered on top of na-single-backtest.
    $ov_rel = '/assets/css/sections/single-backtest-overrides.css';
    $ov_ver = file_exists( $base . $ov_rel ) ? filemtime( $base . $ov_rel ) : CHILD_THEME_ASTRA_CHILD_VERSION;
    wp_enqueue_style(
        'na-single-backtest-overrides',
        get_stylesheet_directory_uri() . $ov_rel,
        array( 'na-single-backtest' ),
        $ov_ver,
        'all'
    );

    // Equity + drawdown charts (reuse the registered chart bundle).
    wp_enqueue_script( 'na-apexcharts' );
    wp_enqueue_script( 'na-backtest-charts' );
    wp_enqueue_style( 'na-backtest-charts' );
}
add_action( 'wp_enqueue_scripts', 'na_enqueue_single_backtest_assets', 20 );

/**
 * Enqueue the Trade Distribution module on single backtests (FE-3.3b).
 *
 * Loaded only on is_singular( 'backtest' ), at priority 21 so the section
 * stylesheet enqueued by na_enqueue_single_backtest_assets() (priority 20) is
 * already in the queue and can be declared as a style dependency. The module
 * reads the four per-trade distribution metas for the current backtest
 * (long/short split, hourly, weekday, duration), injects them as a window.NA_TD
 * object via wp_add_inline_script, and assets/js/trade-distribution.js builds
 * the smart direction card + ApexCharts. Single-direction strategies render
 * only the active side; empty sides are never shown. Depends on na-apexcharts
 * (registered at priority 10). Versioned by filemtime for cache-busting.
 */
function na_enqueue_backtest_trade_distribution() {
    if ( ! is_singular( 'backtest' ) ) {
        return;
    }

    $base = get_stylesheet_directory();
    $uri  = get_stylesheet_directory_uri();

    $css_rel = '/assets/css/sections/backtest-trade-distribution.css';
    $css_ver = file_exists( $base . $css_rel ) ? filemtime( $base . $css_rel ) : CHILD_THEME_ASTRA_CHILD_VERSION;
    wp_enqueue_style(
        'na-backtest-trade-distribution',
        $uri . $css_rel,
        array( 'na-single-backtest' ),
        $css_ver,
        'all'
    );

    $js_rel = '/assets/js/trade-distribution.js';
    $js_ver = file_exists( $base . $js_rel ) ? filemtime( $base . $js_rel ) : CHILD_THEME_ASTRA_CHILD_VERSION;
    wp_enqueue_script(
        'na-trade-distribution',
        $uri . $js_rel,
        array( 'na-apexcharts' ),
        $js_ver,
        true
    );

    // Inject the per-trade distribution metas for the current backtest.
    $bt_id = get_queried_object_id();
    $keys  = array(
        'longShort' => 'long_short_json',
        'hourly'    => 'hourly_dist_json',
        'weekday'   => 'weekday_dist_json',
        'duration'  => 'duration_dist_json',
    );
    $payload = array();
    foreach ( $keys as $out_key => $meta_key ) {
        $raw = get_post_meta( $bt_id, $meta_key, true );
        $val = null;
        if ( is_string( $raw ) && '' !== $raw ) {
            $decoded = json_decode( $raw, true );
            $val     = ( JSON_ERROR_NONE === json_last_error() ) ? $decoded : null;
        } elseif ( is_array( $raw ) ) {
            $val = $raw;
        }
        $payload[ $out_key ] = $val;
    }

    wp_add_inline_script(
        'na-trade-distribution',
        'window.NA_TD = ' . wp_json_encode( $payload ) . ';',
        'before'
    );
}
add_action( 'wp_enqueue_scripts', 'na_enqueue_backtest_trade_distribution', 21 );

/**
 * Enqueue Backtest Runs archive styles (FE-3.4).
 *
 * Loaded only on is_post_type_archive( 'backtest' ). The section stylesheet is
 * self-contained (its own scoped tokens under .na-backtest-archive plus the
 * body.post-type-archive-backtest shell neutralizer) and reuses the global
 * DS-B components for buttons/cards. Equity sparklines on the run cards are
 * inline SVG, so no chart bundle is needed here. Versioned by filemtime for
 * cache-busting during active development.
 */
function na_enqueue_backtest_archive_assets() {
    if ( ! is_post_type_archive( 'backtest' ) ) {
        return;
    }

    $base = get_stylesheet_directory();
    $rel  = '/assets/css/sections/backtest-archive.css';
    $ver  = file_exists( $base . $rel ) ? filemtime( $base . $rel ) : CHILD_THEME_ASTRA_CHILD_VERSION;

    wp_enqueue_style(
        'na-backtest-archive',
        get_stylesheet_directory_uri() . $rel,
        array( 'na-design-tokens', 'na-components' ),
        $ver,
        'all'
    );
}
add_action( 'wp_enqueue_scripts', 'na_enqueue_backtest_archive_assets', 20 );

/**
 * Enqueue the bespoke landing ("The Desk") assets (FE-2.1).
 *
 * Loaded only when the current page uses the page-landing.php template.
 * landing.css depends on the global design tokens + components handles.
 * landing.js is vanilla ES6 (no deps) and loads in the footer.
 * Versioned by filemtime for cache-busting during active development.
 */
function na_enqueue_landing_assets() {
    if ( ! is_page_template( 'page-landing.php' ) ) {
        return;
    }

    $base = get_stylesheet_directory();
    $uri  = get_stylesheet_directory_uri();

    $css_rel = '/assets/css/sections/landing.css';
    $css_ver = file_exists( $base . $css_rel ) ? filemtime( $base . $css_rel ) : CHILD_THEME_ASTRA_CHILD_VERSION;
    wp_enqueue_style(
        'na-landing',
        $uri . $css_rel,
        array( 'na-design-tokens', 'na-components' ),
        $css_ver,
        'all'
    );

    $js_rel = '/assets/js/landing.js';
    $js_ver = file_exists( $base . $js_rel ) ? filemtime( $base . $js_rel ) : CHILD_THEME_ASTRA_CHILD_VERSION;
    wp_enqueue_script(
        'na-landing',
        $uri . $js_rel,
        array(),
        $js_ver,
        true
    );
}
add_action( 'wp_enqueue_scripts', 'na_enqueue_landing_assets', 20 );

/**
 * Enqueue the global Window Controls + Chart Zoom Reset module (FE-3.5).
 *
 * Loaded on single backtests and single strategies, where the terminal-desk
 * .win cards live. Adds macOS-style minimize/expand behavior to every card
 * (close is intentionally inert on content pages) and a reset-scale button for
 * the drag-zoomable equity/drawdown ApexCharts (which ship with the toolbar
 * hidden, so there is otherwise no way back from a zoom). The script reads the
 * live ApexCharts instance via element.apexcharts and does not touch the chart
 * bundle. Priority 22 so it runs after the section + chart assets. Versioned by
 * filemtime for cache-busting during active development.
 */
function na_enqueue_window_controls() {
    if ( ! is_singular( array( 'backtest', 'strategy' ) ) ) {
        return;
    }

    $base = get_stylesheet_directory();
    $uri  = get_stylesheet_directory_uri();

    $css_rel = '/assets/css/components/window-controls.css';
    $css_ver = file_exists( $base . $css_rel ) ? filemtime( $base . $css_rel ) : CHILD_THEME_ASTRA_CHILD_VERSION;
    wp_enqueue_style(
        'na-window-controls',
        $uri . $css_rel,
        array(),
        $css_ver,
        'all'
    );

    $js_rel = '/assets/js/window-controls.js';
    $js_ver = file_exists( $base . $js_rel ) ? filemtime( $base . $js_rel ) : CHILD_THEME_ASTRA_CHILD_VERSION;
    wp_enqueue_script(
        'na-window-controls',
        $uri . $js_rel,
        array(),
        $js_ver,
        true
    );
}
add_action( 'wp_enqueue_scripts', 'na_enqueue_window_controls', 22 );
