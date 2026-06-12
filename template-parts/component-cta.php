<?php
/**
 * Component: CTA Block
 * High-impact call-to-action sections
 *
 * @param array $args {
 *     CTA configuration.
 *
 *     @type string $variant  CTA variant: 'default', 'primary', 'warning'. Default 'default'.
 *     @type string $title    CTA heading.
 *     @type string $text     CTA body text.
 *     @type array  $buttons  Array of button configurations.
 *     @type string $bg_image URL for background image.
 *     @type string $class    Additional CSS classes.
 * }
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Default arguments.
$defaults = array(
	'variant' => 'default',
	'title'   => '',
	'text'    => '',
	'buttons' => array(),
	'bg_image' => '',
	'class'   => '',
);

// Parse arguments.
$args = wp_parse_args( $args, $defaults );

// Extract variables.
$variant = esc_attr( $args['variant'] );
$title   = esc_html( $args['title'] );
$text    = esc_html( $args['text'] );
$bg_image = esc_url( $args['bg_image'] );
$class   = esc_attr( $args['class'] );

// Build CSS classes.
$cta_classes = array( 'na-cta' );
if ( 'default' !== $variant ) {
	$cta_classes[] = 'na-cta--' . $variant;
}
if ( $bg_image ) {
	$cta_classes[] = 'na-cta--with-image';
}
if ( $class ) {
	$cta_classes[] = $class;
}
$cta_class_string = implode( ' ', $cta_classes );

// Inline styles for background image.
$bg_style = $bg_image ? ' style="background-image: url(' . $bg_image . ')"' : '';

// Render CTA.
?>
<div class="<?php echo $cta_class_string; ?>"<?php echo $bg_style; ?>>
	<div class="na-cta__content">
		<?php if ( $title ) : ?>
			<h2 class="na-cta__title na-h2"><?php echo $title; ?></h2>
		<?php endif; ?>
		
		<?php if ( $text ) : ?>
			<p class="na-cta__text na-body"><?php echo $text; ?></p>
		<?php endif; ?>
		
		<?php if ( ! empty( $args['buttons'] ) ) : ?>
			<div class="na-cta__actions">
				<?php foreach ( $args['buttons'] as $button ) : ?>
					<?php 
					// Ensure required button data.
					$button_defaults = array(
						'text'    => 'Button',
						'url'     => '#',
						'style'   => 'primary',
						'size'    => 'md',
						'target'  => '_self',
					);
					$button_args = wp_parse_args( $button, $button_defaults );
					get_template_part( 'template-parts/component', 'button', $button_args );
					?>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</div>