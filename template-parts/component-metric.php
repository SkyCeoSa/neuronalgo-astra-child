<?php
/**
 * Component: Metric
 * Performance metric display for trading strategies
 *
 * @param array $args {
 *     Metric configuration.
 *
 *     @type string $label       Metric label (e.g., 'Sharpe Ratio').
 *     @type string $value       Metric value.
 *     @type string $trend       Trend value (e.g., '+12.5%').
 *     @type string $trend_state Trend state: 'positive', 'negative', 'warning', 'info'.
 *     @type string $size         Size: 'sm', 'md', 'lg'. Default 'md'.
 *     @type string $format       Format: 'number', 'percentage', 'currency'.
 *     @type string $class        Additional CSS classes.
 * }
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Default arguments.
$defaults = array(
	'label'       => '',
	'value'       => '',
	'trend'       => '',
	'trend_state' => 'positive',
	'size'        => 'md',
	'format'      => 'number',
	'class'       => '',
);

// Parse arguments.
$args = wp_parse_args( $args, $defaults );

// Extract variables.
$label     = esc_html( $args['label'] );
$value     = esc_html( $args['value'] );
$trend     = esc_html( $args['trend'] );
$trend_state = esc_attr( $args['trend_state'] );
$size      = esc_attr( $args['size'] );
$format    = esc_attr( $args['format'] );
$class     = esc_attr( $args['class'] );

// Build CSS classes.
$metric_classes = array( 'na-metric' );
if ( 'sm' !== $size ) {
	$metric_classes[] = 'na-metric--' . $size;
}
if ( $class ) {
	$metric_classes[] = $class;
}
$metric_class_string = implode( ' ', $metric_classes );

// Format value based on type.
$formatted_value = $value;
if ( 'percentage' === $format && strpos( $value, '%' ) === false ) {
	$formatted_value = $value . '%';
} elseif ( 'currency' === $format ) {
	$formatted_value = '$' . $value;
}

// Determine value state class.
$value_state_class = '';
if ( $trend_state ) {
	$value_state_class = ' na-metric__value--' . $trend_state;
}

// Render metric.
?>
<div class="<?php echo $metric_class_string; ?>">
	<?php if ( $label ) : ?>
		<div class="na-metric__label"><?php echo $label; ?></div>
	<?php endif; ?>
	
	<div class="na-metric__value<?php echo $value_state_class; ?>">
		<?php echo $formatted_value; ?>
	</div>
	
	<?php if ( $trend ) : ?>
		<div class="na-metric__trend na-metric__trend--<?php echo $trend_state; ?>">
			<span class="na-metric__trend-icon" aria-hidden="true">
				<?php echo ( 'positive' === $trend_state ) ? '↗' : ( ( 'negative' === $trend_state ) ? '↘' : '→' ); ?>
			</span>
			<?php echo $trend; ?>
		</div>
	<?php endif; ?>
</div>