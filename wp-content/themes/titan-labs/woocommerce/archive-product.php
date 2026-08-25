<?php
/**
 * Product archive (shop, category, tag).
 *
 * @package TitanLabs
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );
?>

<?php
$term  = is_product_taxonomy() ? get_queried_object() : null;
$total = (int) wc_get_loop_prop( 'total' );
?>
<div class="tl-pagehero tl-pagehero--shop">
	<div class="tl-container">
		<?php woocommerce_breadcrumb(); ?>

		<?php if ( apply_filters( 'woocommerce_show_page_title', true ) ) : ?>
			<h1 class="tl-mb-0">
				<?php
				/*
				 * The breadcrumb already ends in "Shop", so repeating it as the
				 * H1 spends the page's most prominent line on a word the reader
				 * just read. Categories keep their own name — "Nasal Sprays" is
				 * genuinely the heading there.
				 */
				if ( $term ) {
					woocommerce_page_title();
				} else {
					esc_html_e( 'Research peptides, batch-tested and documented', 'titan-labs' );
				}
				?>
			</h1>
		<?php endif; ?>

		<?php if ( $term && ! empty( $term->description ) ) : ?>
			<p class="tl-lede tl-pagehero__lede"><?php echo wp_kses_post( $term->description ); ?></p>
		<?php endif; ?>

		<?php if ( $total ) : ?>
			<p class="tl-pagehero__count">
				<?php
				printf(
					/* translators: %s: number of products */
					esc_html( _n( '%s product', '%s products', $total, 'titan-labs' ) ),
					esc_html( number_format_i18n( $total ) )
				);
				?>
			</p>
		<?php endif; ?>

		<ul class="tl-pagehero__trust">
			<li><?php esc_html_e( 'Third-party tested', 'titan-labs' ); ?></li>
			<li><?php esc_html_e( 'COA on every batch', 'titan-labs' ); ?></li>
			<li><?php esc_html_e( 'Tracked EU dispatch', 'titan-labs' ); ?></li>
		</ul>
	</div>
</div>

<?php
do_action( 'woocommerce_before_main_content' );

if ( woocommerce_product_loop() ) {

	do_action( 'woocommerce_before_shop_loop' );

	woocommerce_product_loop_start();

	if ( wc_get_loop_prop( 'total' ) ) {
		while ( have_posts() ) {
			the_post();
			do_action( 'woocommerce_shop_loop' );
			wc_get_template_part( 'content', 'product' );
		}
	}

	woocommerce_product_loop_end();

	do_action( 'woocommerce_after_shop_loop' );

} else {
	do_action( 'woocommerce_no_products_found' );
}

do_action( 'woocommerce_after_main_content' );

get_footer( 'shop' );
