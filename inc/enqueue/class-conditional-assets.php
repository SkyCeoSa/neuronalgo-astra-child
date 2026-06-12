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