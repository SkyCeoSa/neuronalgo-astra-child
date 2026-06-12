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