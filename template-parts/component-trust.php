<?php
/**
 * Component: Trust Block
 * Testimonial and certification displays for building credibility
 *
 * @param array $args {
 *     Trust block configuration.
 *
 *     @type string $type      Block type: 'testimonial', 'logos', 'stats'. Default 'testimonial'.
 *     @type array  $items     Array of items (testimonials, logos, or stats).
 *     @type string $title     Optional heading for the trust block.
 *     @type string $class     Additional CSS classes.
 * }
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Default arguments.
$defaults = array(
	'type'  => 'testimonial',
	'items' => array(),
	'title' => '',
	'class' => '',
);

// Parse arguments.
$args = wp_parse_args( $args, $defaults );

// Extract variables.
$type     = esc_attr( $args['type'] );
$title    = esc_html( $args['title'] );
$class    = esc_attr( $args['class'] );

// Build CSS classes.
$trust_classes = array();
if ( 'testimonial' === $type ) {
	$trust_classes[] = 'na-testimonial';
} elseif ( 'stats' === $type ) {
	$trust_classes[] = 'na-stats';
}
if ( $class ) {
	$trust_classes[] = $class;
}

// Render based on type.
if ( 'testimonial' === $type && ! empty( $args['items'] ) ) : ?>
	<?php foreach ( $args['items'] as $testimonial ) : ?>
		<div class="na-testimonial">
			<div class="na-testimonial__quote">
				<?php echo wp_kses_post( $testimonial['quote'] ?? '' ); ?>
			</div>
			<div class="na-testimonial__author">
				<?php if ( ! empty( $testimonial['avatar'] ) ) : ?>
					<img class="na-testimonial__avatar" src="<?php echo esc_url( $testimonial['avatar'] ); ?>" alt="<?php echo esc_attr( $testimonial['author'] ?? '' ); ?>">
				<?php endif; ?>
				<div class="na-testimonial__author-info">
					<?php if ( ! empty( $testimonial['author'] ) ) : ?>
						<div class="na-testimonial__author-name"><?php echo esc_html( $testimonial['author'] ); ?></div>
					<?php endif; ?>
					<?php if ( ! empty( $testimonial['title'] ) ) : ?>
						<div class="na-testimonial__author-title"><?php echo esc_html( $testimonial['title'] ); ?></div>
					<?php endif; ?>
				</div>
			</div>
		</div>
	<?php endforeach; ?>

<?php elseif ( 'logos' === $type && ! empty( $args['items'] ) ) : ?>
	<div class="na-trust-grid na-trust-grid--logos">
		<?php foreach ( $args['items'] as $logo ) : ?>
			<div class="na-logo-item">
				<img src="<?php echo esc_url( $logo['image'] ); ?>" alt="<?php echo esc_attr( $logo['name'] ?? '' ); ?>">
			</div>
		<?php endforeach; ?>
	</div>

<?php elseif ( 'stats' === $type && ! empty( $args['items'] ) ) : ?>
	<div class="na-stats">
		<?php foreach ( $args['items'] as $stat ) : ?>
			<div class="na-stats__item">
				<div class="na-stats__value"><?php echo esc_html( $stat['value'] ?? '' ); ?></div>
				<div class="na-stats__label"><?php echo esc_html( $stat['label'] ?? '' ); ?></div>
			</div>
		<?php endforeach; ?>
	</div>

<?php endif; ?>