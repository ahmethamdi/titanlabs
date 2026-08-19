<?php
/**
 * Homepage — featured product spotlight.
 *
 * @package TitanLabs
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'wc_get_products' ) ) {
	return;
}

$featured = wc_get_products( array(
	'featured' => true,
	'limit'    => 1,
	'status'   => 'publish',
) );

if ( ! $featured ) {
	return;
}

$product = $featured[0];
$purity  = get_post_meta( $product->get_id(), '_titan_purity', true );
$size    = get_post_meta( $product->get_id(), '_titan_size', true );
?>

<section class="tl-spotlight">
	<div class="tl-container tl-spotlight__inner">

		<div>
			<p class="tl-eyebrow"><?php esc_html_e( 'Featured research peptide', 'titan-labs' ); ?></p>
			<h2><?php echo esc_html( $product->get_name() ); ?></h2>

			<p style="opacity:.85">
				<?php echo esc_html( wp_strip_all_tags( $product->get_short_description() ?: $product->get_description() ) ); ?>
			</p>

			<dl class="tl-spotlight__specs">
				<?php if ( $purity ) : ?>
					<div>
						<dt><?php esc_html_e( 'Tested purity', 'titan-labs' ); ?></dt>
						<dd><?php echo esc_html( $purity ); ?></dd>
					</div>
				<?php endif; ?>
				<div>
					<dt><?php esc_html_e( 'Form', 'titan-labs' ); ?></dt>
					<dd><?php esc_html_e( 'Lyophilized powder', 'titan-labs' ); ?></dd>
				</div>
				<?php if ( $size ) : ?>
					<div>
						<dt><?php esc_html_e( 'Size', 'titan-labs' ); ?></dt>
						<dd><?php echo esc_html( $size ); ?></dd>
					</div>
				<?php endif; ?>
			</dl>

			<div class="tl-spotlight__price"><?php echo wp_kses_post( $product->get_price_html() ); ?></div>

			<a class="tl-btn tl-btn--primary" href="<?php echo esc_url( $product->get_permalink() ); ?>">
				<?php
				printf(
					/* translators: %s: product name */
					esc_html__( 'Explore %s', 'titan-labs' ),
					esc_html( $product->get_name() )
				);
				?>
			</a>

			<p class="tl-spotlight__note" style="margin-top:1rem">
				<?php esc_html_e( 'For laboratory research use only. Not for human consumption.', 'titan-labs' ); ?>
			</p>
		</div>

		<div class="tl-spotlight__media">
			<?php echo wp_kses_post( $product->get_image( 'titan-wide' ) ); ?>
		</div>

	</div>
</section>
