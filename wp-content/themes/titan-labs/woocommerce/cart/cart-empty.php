<?php
/**
 * Empty cart page.
 *
 * Overrides WooCommerce's default, which renders a crying-face graphic and a
 * bare "Return to shop" link in a visual language nothing else here uses.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package TitanLabs
 */

defined( 'ABSPATH' ) || exit;

/*
 * @hooked wc_empty_cart_message - 10
 * Removed in functions.php: the notice duplicates the panel below.
 */
do_action( 'woocommerce_cart_is_empty' );

$shop_url = wc_get_page_permalink( 'shop' );

// A few real categories beat a generic "keep shopping" link.
$terms = get_terms( array(
	'taxonomy'   => 'product_cat',
	'hide_empty' => true,
	'number'     => 4,
	'orderby'    => 'count',
	'order'      => 'DESC',
	'exclude'    => array( get_option( 'default_product_cat' ) ),
) );
?>

<div class="tl-emptycart">

	<span class="tl-emptycart__icon" aria-hidden="true">
		<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"
			stroke-linecap="round" stroke-linejoin="round">
			<circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
			<path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
		</svg>
	</span>

	<h2 class="tl-emptycart__title"><?php esc_html_e( 'Your cart is empty', 'titan-labs' ); ?></h2>

	<p class="tl-emptycart__text">
		<?php esc_html_e( 'Every Titan Labs peptide is third-party HPLC tested to ≥99% purity and ships with its Certificate of Analysis.', 'titan-labs' ); ?>
	</p>

	<div class="tl-emptycart__actions">
		<a class="tl-btn tl-btn--primary" href="<?php echo esc_url( $shop_url ); ?>">
			<?php esc_html_e( 'Shop Research Peptides', 'titan-labs' ); ?>
		</a>
		<a class="tl-btn tl-btn--ghost" href="<?php echo esc_url( home_url( '/lab-results/' ) ); ?>">
			<?php esc_html_e( 'View Lab Results', 'titan-labs' ); ?>
		</a>
	</div>

	<?php if ( ! is_wp_error( $terms ) && $terms ) : ?>
		<div class="tl-emptycart__cats">
			<p class="tl-emptycart__catlabel"><?php esc_html_e( 'Popular categories', 'titan-labs' ); ?></p>
			<div class="tl-emptycart__catlinks">
				<?php foreach ( $terms as $term ) : ?>
					<a href="<?php echo esc_url( get_term_link( $term ) ); ?>">
						<?php echo esc_html( $term->name ); ?>
						<span><?php echo esc_html( $term->count ); ?></span>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endif; ?>

	<ul class="tl-emptycart__trust">
		<li>
			<strong><?php esc_html_e( '≥99%', 'titan-labs' ); ?></strong>
			<span><?php esc_html_e( 'Tested purity', 'titan-labs' ); ?></span>
		</li>
		<li>
			<strong><?php esc_html_e( 'COA', 'titan-labs' ); ?></strong>
			<span><?php esc_html_e( 'On every batch', 'titan-labs' ); ?></span>
		</li>
		<li>
			<strong><?php esc_html_e( 'DHL', 'titan-labs' ); ?></strong>
			<span><?php esc_html_e( 'Tracked EU shipping', 'titan-labs' ); ?></span>
		</li>
	</ul>

</div>
