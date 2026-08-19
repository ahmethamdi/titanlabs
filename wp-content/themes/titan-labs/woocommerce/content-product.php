<?php
/**
 * Product card in loops — routes through the shared Titan card.
 *
 * @package TitanLabs
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( empty( $product ) || ! $product->is_visible() ) {
	return;
}
?>
<li <?php wc_product_class( '', $product ); ?>>
	<?php
	set_query_var( 'titan_product', $product );
	get_template_part( 'template-parts/product-card' );
	?>
</li>
