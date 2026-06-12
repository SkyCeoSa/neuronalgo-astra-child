<?php
/**
 * NeuronAlgo Backtest Chart Widget
 *
 * Extends Elementor\Widget_Base to provide equity/drawdown chart functionality.
 * Assets loaded via Elementor's get_script_depends()/get_style_depends().
 *
 * @package Astra Child
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Widget class exists check
if ( ! class_exists( 'Elementor\\Widget_Base' ) ) {
    return;
}

/**
 * NeuronAlgo Backtest Chart Widget Class
 */
class NA_Backtest_Chart_Widget extends \Elementor\Widget_Base {

    /**
     * Get widget name.
     *
     * @return string Widget name.
     */
    public function get_name() {
        return 'na_backtest_chart';
    }

    /**
     * Get widget title.
     *
     * @return string Widget title.
     */
    public function get_title() {
        return 'NeuronAlgo Backtest Chart';
    }

    /**
     * Get widget icon.
     *
     * @return string Widget icon.
     */
    public function get_icon() {
        return 'eicon-chart-area';
    }

    /**
     * Get widget categories.
     *
     * @return array Widget categories.
     */
    public function get_categories() {
        return array( 'na-widgets' );
    }

    /**
     * Get widget keywords.
     *
     * @return array Widget keywords.
     */
    public function get_keywords() {
        return array( 'chart', 'graph', 'equity', 'drawdown', 'backtest', 'trading' );
    }

    /**
     * Get style dependencies for Elementor conditional loading.
     *
     * @return array Style handles.
     */
    public function get_style_depends() {
        return array( 'na-backtest-charts' );
    }

    /**
     * Get script dependencies for Elementor conditional loading.
     *
     * @return array Script handles.
     */
    public function get_script_depends() {
        return array( 'na-backtest-charts' );
    }

    /**
     * Register widget controls.
     * Uses register_controls() for Elementor 4.x API.
     */
    protected function register_controls() {
        // Content Tab: Chart Height
        $this->start_controls_section(
            'section_chart_height',
            array(
                'label' => esc_html__( 'Chart Height', 'astra-child' ),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'chart_height',
            array(
                'label'   => esc_html__( 'Height', 'astra-child' ),
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => 'md',
                'options' => array(
                    'sm' => esc_html__( 'Small (250px)', 'astra-child' ),
                    'md' => esc_html__( 'Medium (350px)', 'astra-child' ),
                    'lg' => esc_html__( 'Large (450px)', 'astra-child' ),
                    'xl' => esc_html__( 'Extra Large (500px)', 'astra-child' ),
                ),
            )
        );

        $this->end_controls_section();

        // Content Tab: Title
        $this->start_controls_section(
            'section_chart_title',
            array(
                'label' => esc_html__( 'Title', 'astra-child' ),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'chart_title',
            array(
                'label'       => esc_html__( 'Chart Title', 'astra-child' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => 'Backtest Performance',
                'placeholder' => esc_html__( 'Enter chart title', 'astra-child' ),
            )
        );

        $this->end_controls_section();

        // Content Tab: Chart Toggles
        $this->start_controls_section(
            'section_chart_toggles',
            array(
                'label' => esc_html__( 'Chart Options', 'astra-child' ),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'show_equity',
            array(
                'label'        => esc_html__( 'Show Equity Curve', 'astra-child' ),
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'label_on'     => esc_html__( 'Yes', 'astra-child' ),
                'label_off'    => esc_html__( 'No', 'astra-child' ),
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->add_control(
            'show_drawdown',
            array(
                'label'        => esc_html__( 'Show Drawdown', 'astra-child' ),
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'label_on'     => esc_html__( 'Yes', 'astra-child' ),
                'label_off'    => esc_html__( 'No', 'astra-child' ),
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->end_controls_section();

        // Style Tab: Background (available for future styling)
        $this->start_controls_section(
            'section_style_background',
            array(
                'label' => esc_html__( 'Background', 'astra-child' ),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_control(
            'bg_color',
            array(
                'label'     => esc_html__( 'Background Color', 'astra-child' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => array(
                    '{{WRAPPER}} .na-chart-container' => 'background-color: {{VALUE}};',
                ),
            )
        );

        $this->end_controls_section();
    }

    /**
     * Render widget output on the frontend.
     * Outputs chart container(s) with reserved height and per-instance JSON data.
     */
    protected function render() {
        $settings = $this->get_settings_for_display();

        // Get current post ID for meta lookup
        $post_id = get_the_ID();

        // Retrieve backtest chart data from post meta
        $equity_json   = get_post_meta( $post_id, 'equity_curve_json', true );
        $drawdown_json = get_post_meta( $post_id, 'drawdown_series_json', true );

        // Generate unique instance ID for this widget
        $instance_id = 'na-chart-' . $this->get_id();

        // Determine height class
        $height_class = 'na-chart-wrapper--height-' . esc_attr( $settings['chart_height'] );

        // Output the chart wrapper
        ?>
        <div class="na-backtest-chart-wrapper">

            <?php if ( 'yes' === $settings['show_equity'] ) : ?>
            <div class="na-chart-container <?php echo esc_attr( $height_class ); ?> na-chart-container--equity">
                <?php if ( ! empty( $settings['chart_title'] ) ) : ?>
                    <h3 class="na-chart-title"><?php echo esc_html( $settings['chart_title'] ); ?> - Equity</h3>
                <?php endif; ?>
                <div class="na-chart-canvas" id="<?php echo esc_attr( $instance_id . '-equity' ); ?>">
                    <div class="na-chart-skeleton na-chart-skeleton-pulse"></div>
                </div>
                <?php
                $raw_equity     = get_post_meta($post_id, 'equity_curve_json', true);
                $decoded_equity = html_entity_decode((string) $raw_equity, ENT_QUOTES);
                $data_equity    = json_decode($decoded_equity, true);
                if (json_last_error() !== JSON_ERROR_NONE || empty($data_equity)) {
                    // emit NO <script ...-data> block for this series
                } else {
                    ?>
                    <script type="application/json" id="<?php echo esc_attr( $instance_id . '-equity-data' ); ?>">
                        <?php echo wp_json_encode($data_equity, JSON_HEX_TAG); ?>
                    </script>
                <?php
                }
                ?>
            </div>
            <?php endif; ?>

            <?php if ( 'yes' === $settings['show_drawdown'] ) : ?>
            <div class="na-chart-container <?php echo esc_attr( $height_class ); ?> na-chart-container--drawdown">
                <?php if ( ! empty( $settings['chart_title'] ) ) : ?>
                    <h3 class="na-chart-title"><?php echo esc_html( $settings['chart_title'] ); ?> - Drawdown</h3>
                <?php endif; ?>
                <div class="na-chart-canvas" id="<?php echo esc_attr( $instance_id . '-drawdown' ); ?>">
                    <div class="na-chart-skeleton na-chart-skeleton-pulse"></div>
                </div>
                <?php
                $raw_drawdown     = get_post_meta($post_id, 'drawdown_series_json', true);
                $decoded_drawdown = html_entity_decode((string) $raw_drawdown, ENT_QUOTES);
                $data_drawdown    = json_decode($decoded_drawdown, true);
                if (json_last_error() !== JSON_ERROR_NONE || empty($data_drawdown)) {
                    // emit NO <script ...-data> block for this series
                } else {
                    ?>
                    <script type="application/json" id="<?php echo esc_attr( $instance_id . "-drawdown-data" ); ?>">
                        <?php echo wp_json_encode($data_drawdown, JSON_HEX_TAG); ?>
                    </script>
                <?php
                }
                ?>
            </div>
            <?php endif; ?>

        </div>
        <?php
    }

    /**
     * Render widget output in the editor.
     */
    protected function content_template() {
        ?>
        <div class="na-backtest-chart-wrapper">
            <# if ( settings.show_equity === 'yes' ) { #>
            <div class="na-chart-container na-chart-wrapper--height-{{ settings.chart_height }} na-chart-container--equity">
                <# if ( settings.chart_title ) { #>
                    <h3 class="na-chart-title">{{ settings.chart_title }} - Equity</h3>
                <# } #>
                <div class="na-chart-canvas">
                    <div class="na-chart-skeleton na-chart-skeleton-pulse"></div>
                </div>
            </div>
            <# } #>
            
            <# if ( settings.show_drawdown === 'yes' ) { #>
            <div class="na-chart-container na-chart-wrapper--height-{{ settings.chart_height }} na-chart-container--drawdown">
                <# if ( settings.chart_title ) { #>
                    <h3 class="na-chart-title">{{ settings.chart_title }} - Drawdown</h3>
                <# } #>
                <div class="na-chart-canvas">
                    <div class="na-chart-skeleton na-chart-skeleton-pulse"></div>
                </div>
            </div>
            <# } #>
        </div>
        <?php
    }
}