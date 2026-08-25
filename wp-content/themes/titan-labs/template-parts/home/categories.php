<?php
/**
 * Homepage — shop by category.
 *
 * @package TitanLabs
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'wc_get_page_permalink' ) ) {
	return;
}

$goal_slugs = array(
	'recovery-repair',
	'metabolic-weight',
	'longevity-immune',
	'skin-beauty',
	'cognitive-neuro',
	'growth-body',
);

$terms = get_terms( array(
	'taxonomy'   => 'product_cat',
	'slug'       => $goal_slugs,
	'hide_empty' => false,
) );

if ( is_wp_error( $terms ) || ! $terms ) {
	return;
}

// Preserve the intended display order.
usort(
	$terms,
	static function ( $a, $b ) use ( $goal_slugs ) {
		return array_search( $a->slug, $goal_slugs, true ) <=> array_search( $b->slug, $goal_slugs, true );
	}
);
?>

<section class="tl-section">
	<div class="tl-container">

		<div class="tl-sectionhead">
			<div>
				<p class="tl-eyebrow"><?php esc_html_e( 'Research peptide categories', 'titan-labs' ); ?></p>
				<h2><?php esc_html_e( 'Shop Peptides by Category', 'titan-labs' ); ?></h2>
				<p class="tl-lede tl-mb-0">
					<?php esc_html_e( 'Browse every research peptide family — each curated for a clear research target, from recovery and metabolic studies to cognition. Lab-tested, COA-backed and shipped across Europe.', 'titan-labs' ); ?>
				</p>
			</div>
			<a class="tl-btn tl-btn--ghost tl-btn--sm" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">
				<?php esc_html_e( 'View all peptides', 'titan-labs' ); ?>
			</a>
		</div>

		<div class="tl-grid tl-grid--3 tl-grid--scroll-mobile">
			<?php foreach ( $terms as $term ) : ?>
				<?php
				$products = wc_get_products( array(
					'category' => array( $term->slug ),
					'limit'    => 8,
					'status'   => 'publish',
					'orderby'  => 'title',
					'order'    => 'ASC',
				) );
				?>
				<div class="tl-cat-card">
					<div class="tl-cat-card__head">
						<h3>
							<a href="<?php echo esc_url( get_term_link( $term ) ); ?>">
								<?php echo esc_html( $term->name ); ?>
							</a>
						</h3>
						<span class="tl-cat-card__count">
							<?php
							printf(
								/* translators: %d: product count */
								esc_html( _n( '%d peptide', '%d peptides', (int) $term->count, 'titan-labs' ) ),
								(int) $term->count
							);
							?>
						</span>
					</div>

					<?php if ( $products ) : ?>
						<ul class="tl-cat-card__list">
							<?php foreach ( $products as $product ) : ?>
								<li>
									<a href="<?php echo esc_url( $product->get_permalink() ); ?>">
										<?php echo esc_html( $product->get_name() ); ?>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>

	</div>
</section>
