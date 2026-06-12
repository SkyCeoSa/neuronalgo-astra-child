<?php
/**
 * Component: Button
 * Reusable button component with configurable variants
 *
 * @param array $args {
 *     Button configuration.
 *
 *     @type string $text        Button text.
 *     @type string $url         Button URL.
 *     @type string $style       Button style: 'primary', 'secondary', 'ghost', 'cta'. Default 'primary'.
 *     @type string $size        Button size: 'sm', 'md', 'lg'. Default 'md'.
 *     @type string $target      Link target: '_blank', '_self'. Default '_self'.
 *     @type bool   $disabled    Whether the button is disabled. Default false.
 *     @type string $class       Additional CSS classes.
 *     @type string $id          Button element ID.
 * }
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Default arguments.
$defaults = array(
	'text'     => '',
	'url'      => '#',
	'style'    => 'primary',
	'size'     => 'md',
	'target'   => '_self',
	'disabled' => false,
	'class'    => '',
	'id'       => '',
);

// Parse arguments.
$args = wp_parse_args( $args, $defaults );

// Extract variables.
$text     = esc_html( $args['text'] );
$url      = esc_url( $args['url'] );
$style    = esc_attr( $args['style'] );
$size     = esc_attr( $args['size'] );
$target   = esc_attr( $args['target'] );
$disabled = $args['disabled'] ? 'disabled' : '';
$class    = esc_attr( $args['class'] );
$id_attr  = ! empty( $args['id'] ) ? ' id="' . esc_attr( $args['id'] ) . '"' : '';

// Build CSS classes.
$button_classes = array( 'na-button' );
$button_classes[] = 'na-button--' . $style;
if ( 'md' !== $size ) {
	$button_classes[] = 'na-button--' . $size;
}
if ( $class ) {
	$button_classes[] = $class;
}
$button_class_string = implode( ' ', $button_classes );

// Render button.
if ( $text && $url ) : ?>
	<a class="<?php echo $button_class_string; ?>" href="<?php echo $url; ?>" target="<?php echo $target; ?>" <?php echo $id_attr; ?> <?php echo $disabled; ?>>
		<?php echo $text; ?>
	</a>
<?php endif; ?>