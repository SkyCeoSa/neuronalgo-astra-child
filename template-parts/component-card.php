<?php
/**
 * Component: Card
 * Reusable card component for content, features, and pricing
 *
 * @param array $args {
 *     Card configuration.
 *
 *     @type string $variant      Card type: 'default', 'feature', 'pricing'. Default 'default'.
 *     @type string $title        Card title.
 *     @type string $content      Card content (HTML allowed).
 *     @type string $image        Image URL.
 *     @type string $link         Card link URL.
 *     @type array  $features     Features list (for pricing cards).
 *     @type bool   $featured     Whether pricing card is featured. Default false.
 *     @type array  $price        Price config: value, period, currency.
 *     @type string $class        Additional CSS classes.
 * }
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Default arguments.
$defaults = array(
	'variant'  => 'default',
	'title'    => '',
	'content'  => '',
	'image'    => '',
	'link'     => '',
	'features' => array(),
	'featured' => false,
	'price'    => array(),
	'class'    => '',
);

// Parse arguments.
$args = wp_parse_args( $args, $defaults );

// Extract variables.
$variant  = esc_attr( $args['variant'] );
$title    = esc_html( $args['title'] );
$content  = wp_kses_post( $args['content'] );
$image    = esc_url( $args['image'] );
$link     = esc_url( $args['link'] );
$featured = $args['featured'] ? 'na-card--featured' : '';
$class    = esc_attr( $args['class'] );

// Build CSS classes.
$card_classes = array( 'na-card' );
if ( 'feature' === $variant ) {
	$card_classes[] = 'na-card--feature';
} elseif ( 'pricing' === $variant ) {
	$card_classes[] = 'na-card--pricing';
	$card_classes[] = $featured;
}
if ( $class ) {
	$card_classes[] = $class;
}
$card_class_string = implode( ' ', $card_classes );

// Render card.
?>
<div class="<?php echo $card_class_string; ?>">
	<?php if ( 'pricing' === $variant && ! empty( $args['price'] ) ) : ?>
		<div class="na-card__price">
			<span class="na-card__price-value">
				<?php 
				$currency = isset( $args['price']['currency'] ) ? esc_html( $args['price']['currency'] ) : '$';
				echo $currency . esc_html( $args['price']['value'] ); 
				?>
			</span>
			<span class="na-card__price-period">
				<?php echo isset( $args['price']['period'] ) ? esc_html( $args['price']['period'] ) : ''; ?>
			</span>
		</div>
	<?php endif; ?>

	<?php if ( $title ) : ?>
		<?php if ( $link ) : ?>
			<h3 class="na-card__title na-h3">
				<a href="<?php echo $link; ?>" class="na-card__link"><?php echo $title; ?></a>
			</h3>
		<?php else : ?>
			<h3 class="na-card__title na-h3"><?php echo $title; ?></h3>
		<?php endif; ?>
	<?php endif; ?>

	<?php if ( $image ) : ?>
		<div class="na-card__image">
			<img src="<?php echo $image; ?>" alt="<?php echo $title ? esc_attr( $title ) : ''; ?>">
		</div>
	<?php endif; ?>

	<?php if ( $content ) : ?>
		<div class="na-card__body na-body">
			<?php echo $content; ?>
		</div>
	<?php endif; ?>

	<?php if ( 'pricing' === $variant && ! empty( $args['features'] ) ) : ?>
		<ul class="na-card__features">
			<?php foreach ( $args['features'] as $feature ) : ?>
				<li><?php echo esc_html( $feature ); ?></li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>

	<?php if ( $link && 'pricing' !== $variant ) : ?>
		<div class="na-card__footer">
			<a href="<?php echo $link; ?>" class="na-button na-button--ghost na-button--sm">
				Learn More
			</a>
		</div>
	<?php endif; ?>
</div>