<?php
/**
 * Component: FAQ Block
 * Accordion-style interactive FAQ sections
 *
 * @param array $args {
 *     FAQ configuration.
 *
 *     @type array  $items   Array of FAQ items with question/answer pairs.
 *     @type string $variant FAQ variant: 'default', 'compact', 'spacious', 'borderless'. Default 'default'.
 *     @type bool   $single_open Whether only one item can be open at a time. Default false.
 *     @type string $class   Additional CSS classes.
 * }
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Default arguments.
$defaults = array(
	'items'      => array(),
	'variant'    => 'default',
	'single_open' => false,
	'class'      => '',
);

// Parse arguments.
$args = wp_parse_args( $args, $defaults );

// Extract variables.
$variant     = esc_attr( $args['variant'] );
$single_open = $args['single_open'];
$class       = esc_attr( $args['class'] );

// Build CSS classes.
$faq_classes = array( 'na-faq' );
if ( 'default' !== $variant ) {
	$faq_classes[] = 'na-faq--' . $variant;
}
if ( $class ) {
	$faq_classes[] = $class;
}
$faq_class_string = implode( ' ', $faq_classes );

// Generate unique ID for ARIA.
$faq_id = 'na-faq-' . uniqid();

// Render FAQ if items exist.
if ( ! empty( $args['items'] ) ) : ?>
	<div class="<?php echo $faq_class_string; ?>" role="tablist" aria-label="Frequently Asked Questions">
		<?php foreach ( $args['items'] as $index => $item ) : 
			$item_id = $faq_id . '-item-' . $index;
			$answer_id = $item_id . '-answer';
		?>
			<div class="na-faq-item">
				<button 
					id="<?php echo esc_attr( $item_id ); ?>"
					class="na-faq-question"
					aria-expanded="false"
					aria-controls="<?php echo esc_attr( $answer_id ); ?>"
					type="button"
				>
					<span class="na-faq-question__text na-body"><?php echo esc_html( $item['question'] ?? '' ); ?></span>
					<span class="na-faq-question__icon" aria-hidden="true"></span>
				</button>
				<div 
					id="<?php echo esc_attr( $answer_id ); ?>"
					class="na-faq-answer"
					role="region"
					aria-labelledby="<?php echo esc_attr( $item_id ); ?>"
				>
					<div class="na-faq-answer-inner na-body">
						<?php echo wp_kses_post( $item['answer'] ?? '' ); ?>
					</div>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
<?php endif; ?>

<script>
// Initialize FAQ accordion when available
if (window.NeuronAlgoFAQ) {
	document.addEventListener('DOMContentLoaded', function() {
		var faqContainers = document.querySelectorAll('.na-faq');
		faqContainers.forEach(function(container) {
			// Re-initialize since this is rendered after JS load
			if (typeof NeuronAlgoFAQ.init === 'function') {
				NeuronAlgoFAQ.init(container);
			}
		});
	});
}
</script>