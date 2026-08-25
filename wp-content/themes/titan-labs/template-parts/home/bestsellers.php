<?php
/**
 * Homepage — bestsellers, tabbed by product format.
 *
 * @package TitanLabs
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'wc_get_products' ) ) {
	return;
}

$tabs = array(
	'peptide-vials'   => __( 'Vials', 'titan-labs' ),
	'peptide-pens'    => __( 'Pens', 'titan-labs' ),
	'peptide-sprays'  => __( 'Sprays', 'titan-labs' ),
	'peptide-orals'   => __( 'Orals', 'titan-labs' ),
);

$sets = array();
foreach ( $tabs as $slug => $label ) {
	$products = wc_get_products( array(
		'category' => array( $slug ),
		'limit'    => 10,
		'status'   => 'publish',
		'orderby'  => 'popularity',
	) );
	if ( $products ) {
		$sets[ $slug ] = array( 'label' => $label, 'products' => $products );
	}
}

if ( ! $sets ) {
	return;
}
?>

<section class="tl-section tl-section--panel">
	<div class="tl-container">

		<div class="tl-sectionhead">
			<div>
				<p class="tl-eyebrow"><?php esc_html_e( 'Most ordered in Europe', 'titan-labs' ); ?></p>
				<h2 class="tl-mb-0"><?php esc_html_e( 'Bestselling Research Peptides', 'titan-labs' ); ?></h2>
			</div>
		</div>

		<div data-tabs>
			<div class="tl-tabs" role="tablist" aria-label="<?php esc_attr_e( 'Product formats', 'titan-labs' ); ?>">
				<?php $i = 0; foreach ( $sets as $slug => $set ) : ?>
					<button type="button"
						role="tab"
						id="tab-<?php echo esc_attr( $slug ); ?>"
						aria-controls="panel-<?php echo esc_attr( $slug ); ?>"
						aria-selected="<?php echo 0 === $i ? 'true' : 'false'; ?>"
						tabindex="<?php echo 0 === $i ? '0' : '-1'; ?>">
						<?php echo esc_html( $set['label'] ); ?>
					</button>
				<?php $i++; endforeach; ?>
			</div>

			<?php $i = 0; foreach ( $sets as $slug => $set ) : ?>
				<div class="tl-tabpanel"
					role="tabpanel"
					id="panel-<?php echo esc_attr( $slug ); ?>"
					aria-labelledby="tab-<?php echo esc_attr( $slug ); ?>"
					<?php echo 0 === $i ? '' : 'hidden'; ?>>

					<div class="tl-grid tl-grid--4 tl-grid--scroll-mobile">
						<?php foreach ( $set['products'] as $product ) : ?>
							<?php
							set_query_var( 'titan_product', $product );
							get_template_part( 'template-parts/product-card' );
							?>
						<?php endforeach; ?>
					</div>
				</div>
			<?php $i++; endforeach; ?>
		</div>

	</div>
</section>
