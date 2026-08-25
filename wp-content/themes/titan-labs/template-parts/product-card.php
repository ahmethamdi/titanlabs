<?php
/**
 * Reusable product card.
 * Expects a WC_Product in the `titan_product` query var.
 *
 * @package TitanLabs
 */

defined( 'ABSPATH' ) || exit;

$product = get_query_var( 'titan_product' );

if ( ! $product instanceof WC_Product ) {
	return;
}

$size   = get_post_meta( $product->get_id(), '_titan_size', true );
$purity = get_post_meta( $product->get_id(), '_titan_purity', true );

/*
 * Consumables such as bacteriostatic water carry "—" for purity, meaning not
 * applicable. Only a real measurement earns the chip.
 */
if ( ! preg_match( '/\d/', (string) $purity ) ) {
	$purity = '';
}

// Format is the one attribute that differs at thumbnail size, where every
// packshot looks alike.
$format = '';
foreach ( wp_get_post_terms( $product->get_id(), 'product_cat' ) as $term ) {
	if ( in_array( $term->slug, array( 'peptide-vials', 'peptide-pens', 'peptide-sprays', 'peptide-orals' ), true ) ) {
		$format = str_replace( array( 'Peptide ', 'Nasal ' ), '', $term->name );
		$format = rtrim( $format, 's' );
		break;
	}
}
?>

<div class="tl-product-card">

	<div class="tl-product-card__media">
		<div class="tl-product-card__flags">
			<?php if ( $product->is_on_sale() ) : ?>
				<span class="tl-badge tl-badge--sale"><?php esc_html_e( 'Sale', 'titan-labs' ); ?></span>
			<?php endif; ?>
			<?php if ( ! $product->is_in_stock() ) : ?>
				<span class="tl-badge tl-badge--outline"><?php esc_html_e( 'Sold out', 'titan-labs' ); ?></span>
			<?php endif; ?>
		</div>

		<?php if ( $format ) : ?>
			<span class="tl-product-card__format"><?php echo esc_html( $format ); ?></span>
		<?php endif; ?>

		<a href="<?php echo esc_url( $product->get_permalink() ); ?>" aria-hidden="true" tabindex="-1">
			<?php echo wp_kses_post( $product->get_image( 'titan-card' ) ); ?>
		</a>
	</div>

	<div class="tl-product-card__body">

		<?php
		/*
		 * Fixed slot: a rating only renders on some products, and letting it
		 * push the title down leaves names and prices at different heights
		 * across a row. The slot is emitted either way.
		 */
		?>
		<div class="tl-product-card__meta">
			<?php if ( $purity ) : ?>
				<span class="tl-purity"><?php echo esc_html( $purity ); ?> <?php esc_html_e( 'pure', 'titan-labs' ); ?></span>
			<?php else : ?>
				<?php titan_rating_markup( $product ); ?>
			<?php endif; ?>
		</div>

		<h3 class="tl-product-card__title">
			<a href="<?php echo esc_url( $product->get_permalink() ); ?>">
				<?php echo esc_html( $product->get_name() ); ?>
			</a>
		</h3>

		<div class="tl-product-card__size"><?php echo $size ? esc_html( $size ) : '&nbsp;'; ?></div>

		<div class="tl-product-card__foot">
			<div class="tl-product-card__price">
				<?php echo wp_kses_post( $product->get_price_html() ); ?>
			</div>

			<?php
			$add_url   = $product->add_to_cart_url();
			$add_label = $product->add_to_cart_text();
			?>
			<a class="tl-product-card__add <?php echo $product->is_purchasable() && $product->is_in_stock() ? 'ajax_add_to_cart' : ''; ?> add_to_cart_button"
				href="<?php echo esc_url( $add_url ); ?>"
				data-product_id="<?php echo esc_attr( $product->get_id() ); ?>"
				data-quantity="1"
				aria-label="<?php echo esc_attr( sprintf( /* translators: %s: product name */ __( 'Add %s to cart', 'titan-labs' ), $product->get_name() ) ); ?>"
				rel="nofollow">
				<?php echo esc_html( $add_label ); ?>
			</a>
		</div>

	</div>
</div>
