<?php
/**
 * NeuronAlgo Internal Linking Framework
 *
 * Provides contextual internal linking helpers for navigation.
 *
 * @package Astra Child
 * @since 1.0.0
 */

namespace NeuronAlgo\Theme\Navigation;

/**
 * Class NA_Internal_Links
 *
 * Generates contextual internal link clusters based on post type.
 */
class NA_Internal_Links {

	/**
	 * Get related strategies link.
	 *
	 * @param int $post_id Current post ID.
	 * @return string URL to strategy archive.
	 */
	public static function get_related_strategies_url( $post_id = 0 ) {
		return esc_url( home_url( '/strategies' ) );
	}

	/**
	 * Get related backtests link.
	 *
	 * @param int $post_id Current post ID.
	 * @return string URL to backtests hub.
	 */
	public static function get_related_backtests_url( $post_id = 0 ) {
		return esc_url( home_url( '/backtests' ) );
	}

	/**
	 * Get pricing link.
	 *
	 * @return string URL to pricing page.
	 */
	public static function get_pricing_url() {
		return esc_url( home_url( '/pricing' ) );
	}

	/**
	 * Get contact link.
	 *
	 * @return string URL to contact page.
	 */
	public static function get_contact_url() {
		return esc_url( home_url( '/contact' ) );
	}

	/**
	 * Get FAQ link.
	 *
	 * @return string URL to FAQ page.
	 */
	public static function get_faq_url() {
		return esc_url( home_url( '/faq' ) );
	}

	/**
	 * Render a contextual next-step CTA based on current context.
	 *
	 * @param string $context Optional context override ('strategy', 'robot', 'indicator', 'course', 'default').
	 * @return void
	 */
	public static function render_contextual_cta( $context = '' ) {
		if ( empty( $context ) ) {
			$context = self::get_current_context();
		}

		$cta_data = self::get_cta_for_context( $context );
		?>
		<div class="na-contextual-cta">
			<a href="<?php echo esc_url( $cta_data['url'] ); ?>" class="na-btn na-btn--primary">
				<?php echo esc_html( $cta_data['label'] ); ?>
			</a>
		</div>
		<?php
	}

	/**
	 * Determine current context from global post.
	 *
	 * @return string Context identifier.
	 */
	private static function get_current_context() {
		if ( ! is_singular() ) {
			return 'default';
		}

		$post_type = get_post_type();

		switch ( $post_type ) {
			case 'strategy':
				return 'strategy';
			case 'robot':
				return 'robot';
			case 'indicator':
				return 'indicator';
			case 'course':
				return 'course';
			case 'backtest':
				return 'backtest';
			default:
				return 'default';
		}
	}

	/**
	 * Get CTA button data for a given context.
	 *
	 * @param string $context Context identifier.
	 * @return array {label, url}.
	 */
	private static function get_cta_for_context( $context ) {
		$ctas = array(
			'strategy'  => array(
				'label' => __( 'View Related Strategies', 'astra-child' ),
				'url'   => self::get_related_strategies_url(),
			),
			'robot'     => array(
				'label' => __( 'Get Robot License', 'astra-child' ),
				'url'   => self::get_pricing_url(),
			),
			'indicator' => array(
				'label' => __( 'View Pricing', 'astra-child' ),
				'url'   => self::get_pricing_url(),
			),
			'course'    => array(
				'label' => __( 'Enroll Now', 'astra-child' ),
				'url'   => self::get_pricing_url(),
			),
			'backtest'  => array(
				'label' => __( 'View Full Backtest Archive', 'astra-child' ),
				'url'   => self::get_related_backtests_url(),
			),
			'default'   => array(
				'label' => __( 'Explore Strategies', 'astra-child' ),
				'url'   => self::get_related_strategies_url(),
			),
		);

		return $ctas[ $context ] ?? $ctas['default'];
	}
}

// Initialize helper functions for template use.
if ( ! function_exists( 'na_render_contextual_cta' ) ) {
	/**
	 * Template helper for contextual CTA.
	 *
	 * @param string $context Optional context override.
	 */
	function na_render_contextual_cta( $context = '' ) {
		NeuronAlgo\Theme\Navigation\NA_Internal_Links::render_contextual_cta( $context );
	}
}

if ( ! function_exists( 'na_get_related_strategies_url' ) ) {
	/**
	 * Template helper for related strategies URL.
	 */
	function na_get_related_strategies_url() {
		return NeuronAlgo\Theme\Navigation\NA_Internal_Links::get_related_strategies_url();
	}
}

if ( ! function_exists( 'na_get_pricing_url' ) ) {
	/**
	 * Template helper for pricing URL.
	 */
	function na_get_pricing_url() {
		return NeuronAlgo\Theme\Navigation\NA_Internal_Links::get_pricing_url();
	}
}