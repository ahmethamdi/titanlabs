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

$size = get_post_meta( $product->get_id(), '_titan_size', true );
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

		<a href="<?php echo esc_url( $product->get_permalink() ); ?>" aria-hidden="true" tabindex="-1">
			<?php echo wp_kses_post( $product->get_image( 'titan-card' ) ); ?>
		</a>
	</div>

	<div class="tl-product-card__body">

		<?php titan_rating_markup( $product ); ?>

		<h3 class="tl-product-card__title">
			<a href="<?php echo esc_url( $product->get_permalink() ); ?>">
				<?php echo esc_html( $product->get_name() ); ?>
			</a>
		</h3>

		<?php if ( $size ) : ?>
			<div class="tl-product-card__size"><?php echo esc_html( $size ); ?></div>
		<?php endif; ?>

		<div class="tl-product-card__price">
			<?php echo wp_kses_post( $product->get_price_html() ); ?>
		</div>

		<?php
		$add_url   = $product->add_to_cart_url();
		$add_label = $product->add_to_cart_text();
		?>
		<a class="tl-btn tl-btn--primary tl-btn--sm tl-btn--block <?php echo $product->is_purchasable() && $product->is_in_stock() ? 'ajax_add_to_cart' : ''; ?> add_to_cart_button"
			href="<?php echo esc_url( $add_url ); ?>"
			data-product_id="<?php echo esc_attr( $product->get_id() ); ?>"
			data-quantity="1"
			rel="nofollow">
			<?php echo esc_html( $add_label ); ?>
		</a>

	</div>
</div>
