<?php
/**
 * NeuronAlgo Backtest Chart Elementor Widget
 *
 * Custom Elementor widget for rendering equity and drawdown charts.
 * Registered under "NeuronAlgo" category in Elementor editor.
 *
 * @package Astra Child
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register NeuronAlgo widget category for Elementor
 */
function na_register_elementor_category( $elements_manager ) {
    $elements_manager->add_category(
        'na-widgets',
        array(
            'title' => 'NeuronAlgo',
            'icon'  => 'fa fa-chart-line',
        )
    );
}
add_action( 'elementor/elements/categories_registered', 'na_register_elementor_category' );

/**
 * Register the Backtest Chart widget
 */
function na_register_backtest_chart_widget( $widgets_manager ) {
    require_once __DIR__ . '/widgets/class-backtest-chart.php';
    $widgets_manager->register( new NA_Backtest_Chart_Widget() );
}
add_action( 'elementor/widgets/register', 'na_register_backtest_chart_widget' );