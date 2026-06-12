<?php
/**
 * Breadcrumbs Template
 *
 * Renders breadcrumb trail compatible with Rank Math, with fallback.
 *
 * @package Astra Child
 * @since 1.0.0
 */

namespace NeuronAlgo\Theme\Navigation;

// Check if Rank Math breadcrumbs exist and use them.
if ( function_exists( 'rank_math_the_breadcrumbs' ) ) {
	rank_math_the_breadcrumbs();
	return;
}
?>

<nav class="na-breadcrumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'astra-child' ); ?>">
	<ol class="na-breadcrumbs__list" itemscope itemtype="https://schema.org/BreadcrumbList">
		<li class="na-breadcrumbs__item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
			<a class="na-breadcrumbs__link" itemprop="item" href="<?php echo esc_url( home_url() ); ?>">
				<span itemprop="name"><?php esc_html_e( 'Home', 'astra-child' ); ?></span>
			</a>
			<meta itemprop="position" content="1">
		</li>

		<?php
		$position = 2;
		if ( is_category() || is_single() ) {
			if ( is_category() ) {
				$category = get_queried_object();
				?>
				<li class="na-breadcrumbs__item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
					<a class="na-breadcrumbs__link" itemprop="item" href="<?php echo esc_url( get_category_link( $category->term_id ) ); ?>">
						<span itemprop="name"><?php echo esc_html( $category->name ); ?></span>
					</a>
					<meta itemprop="position" content="<?php echo (int) $position; ?>">
				</li>
				<?php
				$position++;
			} elseif ( is_single() ) {
				$categories = get_the_category( get_the_ID() );
				if ( ! empty( $categories ) ) {
					$category = $categories[0];
					?>
					<li class="na-breadcrumbs__item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
						<a class="na-breadcrumbs__link" itemprop="item" href="<?php echo esc_url( get_category_link( $category->term_id ) ); ?>">
							<span itemprop="name"><?php echo esc_html( $category->name ); ?></span>
						</a>
						<meta itemprop="position" content="<?php echo (int) $position; ?>">
					</li>
					<?php
					$position++;
				}
				?>
				<li class="na-breadcrumbs__item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
					<span class="na-breadcrumbs__current" itemprop="item" aria-current="page">
						<span itemprop="name"><?php echo esc_html( get_the_title() ); ?></span>
					</span>
					<meta itemprop="position" content="<?php echo (int) $position; ?>">
				</li>
				<?php
				$position++;
			}
		} elseif ( is_page() ) {
			$ancestors = get_post_ancestors( get_the_ID() );
			$ancestors = array_reverse( $ancestors );
			foreach ( $ancestors as $ancestor_id ) {
				$ancestor = get_post( $ancestor_id );
				?>
				<li class="na-breadcrumbs__item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
					<a class="na-breadcrumbs__link" itemprop="item" href="<?php echo esc_url( get_permalink( $ancestor_id ) ); ?>">
						<span itemprop="name"><?php echo esc_html( $ancestor->post_title ); ?></span>
					</a>
					<meta itemprop="position" content="<?php echo (int) $position; ?>">
				</li>
				<?php
				$position++;
			}
			?>
			<li class="na-breadcrumbs__item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
				<span class="na-breadcrumbs__current" itemprop="item" aria-current="page">
					<span itemprop="name"><?php echo esc_html( get_the_title() ); ?></span>
				</span>
				<meta itemprop="position" content="<?php echo (int) $position; ?>">
			</li>
		<?php } elseif ( is_search() ) {
			?>
			<li class="na-breadcrumbs__item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
				<span class="na-breadcrumbs__current" itemprop="item" aria-current="page">
					<span itemprop="name"><?php esc_html_e( 'Search Results', 'astra-child' ); ?></span>
				</span>
				<meta itemprop="position" content="<?php echo (int) $position; ?>">
			</li>
		<?php } elseif ( is_404() ) {
			?>
			<li class="na-breadcrumbs__item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
				<span class="na-breadcrumbs__current" itemprop="item" aria-current="page">
					<span itemprop="name"><?php esc_html_e( 'Page Not Found', 'astra-child' ); ?></span>
				</span>
				<meta itemprop="position" content="<?php echo (int) $position; ?>">
			</li>
		<?php } ?>
	</ol>
</nav>

<style>
.na-breadcrumbs {
	margin-bottom: var(--na-space-md, 1rem);
	font-size: var(--na-font-size-sm, 0.875rem);
}

.na-breadcrumbs__list {
	display: flex;
	flex-wrap: wrap;
	align-items: center;
	list-style: none;
	margin: 0;
	padding: 0;
	gap: var(--na-space-xs, 0.25rem);
}

.na-breadcrumbs__item:not(:last-child)::after {
	content: ' / ';
	color: var(--na-text-secondary, #666666);
	margin: 0 var(--na-space-xs, 0.25rem);
}

.na-breadcrumbs__link {
	color: var(--na-text-secondary, #666666);
	text-decoration: none;
}

.na-breadcrumbs__link:hover {
	color: var(--na-text-accent, #0066cc);
	text-decoration: underline;
}

.na-breadcrumbs__current {
	color: var(--na-text-primary, #1a1a1a);
	font-weight: var(--na-font-weight-medium, 500);
}</style>