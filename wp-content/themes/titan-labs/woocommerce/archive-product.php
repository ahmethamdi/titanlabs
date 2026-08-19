<?php
/**
 * Product archive (shop, category, tag).
 *
 * @package TitanLabs
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );
?>

<div class="tl-pagehero">
	<div class="tl-container">
		<?php woocommerce_breadcrumb(); ?>

		<?php if ( apply_filters( 'woocommerce_show_page_title', true ) ) : ?>
			<h1 class="tl-mb-0"><?php woocommerce_page_title(); ?></h1>
		<?php endif; ?>

		<?php
		$term = is_product_taxonomy() ? get_queried_object() : null;
		if ( $term && ! empty( $term->description ) ) {
			printf(
				'<p class="tl-lede" style="margin-top:.75rem">%s</p>',
				esc_html( wp_strip_all_tags( $term->description ) )
			);
		}
		?>
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
