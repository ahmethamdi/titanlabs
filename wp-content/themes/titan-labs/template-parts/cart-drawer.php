<?php
/**
 * Contents of the slide-in cart drawer.
 *
 * Re-rendered on every add/remove through woocommerce_add_to_cart_fragments,
 * so it must stay self-contained and read the cart fresh each time.
 *
 * @package TitanLabs
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
	return;
}

$cart  = WC()->cart;
$items = $cart->get_cart();
$count = $cart->get_cart_contents_count();
?>
<div class="tl-cartdrawer__body">

	<?php if ( ! $items ) : ?>

		<div class="tl-cartdrawer__empty">
			<span class="tl-cartdrawer__emptyicon" aria-hidden="true">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
					stroke-linecap="round" stroke-linejoin="round">
					<circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
					<path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
				</svg>
			</span>
			<p class="tl-cartdrawer__emptytitle"><?php esc_html_e( 'Your cart is empty', 'titan-labs' ); ?></p>
			<p class="tl-cartdrawer__emptytext">
				<?php esc_html_e( 'Browse lab-tested research peptides, each shipped with its Certificate of Analysis.', 'titan-labs' ); ?>
			</p>
			<a class="tl-btn tl-btn--primary tl-cartdrawer__emptycta"
				href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">
				<?php esc_html_e( 'Shop Research Peptides', 'titan-labs' ); ?>
			</a>
		</div>

	<?php else : ?>

		<ul class="tl-cartdrawer__list">
			<?php
			foreach ( $items as $key => $item ) :
				$product = $item['data'];

				if ( ! $product || ! $product->exists() || $item['quantity'] <= 0 ) {
					continue;
				}

				$permalink = $product->is_visible() ? $product->get_permalink( $item ) : '';
				$thumb     = $product->get_image( 'woocommerce_thumbnail' );
				?>
				<li class="tl-cartitem" data-cart-item="<?php echo esc_attr( $key ); ?>">

					<div class="tl-cartitem__media">
						<?php if ( $permalink ) : ?>
							<a href="<?php echo esc_url( $permalink ); ?>"><?php echo wp_kses_post( $thumb ); ?></a>
						<?php else : ?>
							<?php echo wp_kses_post( $thumb ); ?>
						<?php endif; ?>
					</div>

					<div class="tl-cartitem__info">
						<p class="tl-cartitem__name">
							<?php if ( $permalink ) : ?>
								<a href="<?php echo esc_url( $permalink ); ?>">
									<?php echo wp_kses_post( $product->get_name() ); ?>
								</a>
							<?php else : ?>
								<?php echo wp_kses_post( $product->get_name() ); ?>
							<?php endif; ?>
						</p>

						<?php
						$meta = wc_get_formatted_cart_item_data( $item, true );
						if ( $meta ) :
							?>
							<p class="tl-cartitem__meta"><?php echo wp_kses_post( $meta ); ?></p>
						<?php endif; ?>

						<div class="tl-cartitem__row">
							<div class="tl-qty" data-qty>
								<button type="button" class="tl-qty__btn" data-qty-down
									aria-label="<?php esc_attr_e( 'Decrease quantity', 'titan-labs' ); ?>">&minus;</button>
								<input class="tl-qty__input" type="number" inputmode="numeric"
									value="<?php echo esc_attr( $item['quantity'] ); ?>"
									min="0" step="1"
									data-cart-qty="<?php echo esc_attr( $key ); ?>"
									aria-label="<?php esc_attr_e( 'Quantity', 'titan-labs' ); ?>">
								<button type="button" class="tl-qty__btn" data-qty-up
									aria-label="<?php esc_attr_e( 'Increase quantity', 'titan-labs' ); ?>">+</button>
							</div>

							<span class="tl-cartitem__price">
								<?php echo wp_kses_post( apply_filters( 'woocommerce_cart_item_subtotal', $cart->get_product_subtotal( $product, $item['quantity'] ), $item, $key ) ); ?>
							</span>
						</div>
					</div>

					<button type="button" class="tl-cartitem__remove" data-cart-remove="<?php echo esc_attr( $key ); ?>"
						aria-label="<?php esc_attr_e( 'Remove item', 'titan-labs' ); ?>">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
							stroke-linecap="round" aria-hidden="true">
							<path d="M18 6 6 18M6 6l12 12"/>
						</svg>
					</button>
				</li>
			<?php endforeach; ?>
		</ul>

		<?php
		// Surface how close the cart is to the next volume discount tier.
		if ( function_exists( 'titan_stack_rate' ) && get_theme_mod( 'titan_stack_enabled', true ) ) :
			$rate  = titan_stack_rate( $count );
			$tier1 = (int) get_theme_mod( 'titan_stack_tier1_qty', 4 );
			$tier2 = (int) get_theme_mod( 'titan_stack_tier2_qty', 10 );
			$next  = $count < $tier1 ? $tier1 : ( $count < $tier2 ? $tier2 : 0 );
			?>
			<?php if ( $rate ) : ?>
				<p class="tl-cartdrawer__perk tl-cartdrawer__perk--on">
					<?php
					printf(
						/* translators: %s: discount percentage */
						esc_html__( 'Stack discount applied — %s off', 'titan-labs' ),
						esc_html( round( $rate * 100 ) . '%' )
					);
					?>
				</p>
			<?php endif; ?>
			<?php if ( $next ) : ?>
				<p class="tl-cartdrawer__perk">
					<?php
					printf(
						/* translators: %d: number of additional items needed */
						esc_html( _n( 'Add %d more item for a bigger stack discount', 'Add %d more items for a bigger stack discount', $next - $count, 'titan-labs' ) ),
						(int) ( $next - $count )
					);
					?>
				</p>
			<?php endif; ?>
		<?php endif; ?>

	<?php endif; ?>
</div>

<?php if ( $items ) : ?>
	<div class="tl-cartdrawer__foot">
		<div class="tl-cartdrawer__total">
			<span><?php esc_html_e( 'Subtotal', 'titan-labs' ); ?></span>
			<strong><?php echo wp_kses_post( $cart->get_cart_subtotal() ); ?></strong>
		</div>
		<p class="tl-cartdrawer__note">
			<?php esc_html_e( 'Shipping and taxes calculated at checkout.', 'titan-labs' ); ?>
		</p>
		<a class="tl-btn tl-btn--primary tl-cartdrawer__checkout"
			href="<?php echo esc_url( wc_get_checkout_url() ); ?>">
			<?php esc_html_e( 'Proceed to Checkout', 'titan-labs' ); ?>
		</a>
		<a class="tl-cartdrawer__viewcart" href="<?php echo esc_url( wc_get_cart_url() ); ?>">
			<?php esc_html_e( 'View full cart', 'titan-labs' ); ?>
		</a>
	</div>
<?php endif; ?>
