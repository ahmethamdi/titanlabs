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
?>

<div class="tl-shoplayout">

	<aside class="tl-shoplayout__sidebar">
		<?php titan_render_filters(); ?>
	</aside>

	<div class="tl-shoplayout__main" data-shop-main>
		<?php titan_render_shop_results(); ?>
	</div>

	<button type="button" class="tl-shoplayout__mobilebar" data-filters-open
		aria-controls="tl-filtersheet" aria-expanded="false">
		<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"
			stroke-linecap="round" aria-hidden="true">
			<path d="M3 6h18M7 12h10M10 18h4"/>
		</svg>
		<?php esc_html_e( 'Filter', 'titan-labs' ); ?>
		<span class="tl-shoplayout__mobilecount" data-filter-badge<?php echo titan_active_filters() ? '' : ' hidden'; ?>>
			<?php echo esc_html( (string) count( titan_active_filters(), COUNT_RECURSIVE ) - count( titan_active_filters() ) ); ?>
		</span>
	</button>

</div>

<div class="tl-filtersheet" id="tl-filtersheet" data-filtersheet>
	<div class="tl-filtersheet__scrim" data-filters-close></div>
	<div class="tl-filtersheet__panel" role="dialog" aria-modal="true"
		aria-label="<?php esc_attr_e( 'Filters', 'titan-labs' ); ?>">
		<div class="tl-filtersheet__head">
			<strong><?php esc_html_e( 'Filter', 'titan-labs' ); ?></strong>
			<button type="button" class="tl-iconbtn" data-filters-close
				aria-label="<?php esc_attr_e( 'Close filters', 'titan-labs' ); ?>">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
					stroke-linecap="round" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
			</button>
		</div>
		<div class="tl-filtersheet__body" data-filtersheet-body></div>
		<div class="tl-filtersheet__foot">
			<a class="tl-btn tl-btn--ghost tl-btn--sm" href="<?php echo esc_url( titan_filter_clear_url() ); ?>">
				<?php esc_html_e( 'Clear all', 'titan-labs' ); ?>
			</a>
			<button type="button" class="tl-btn tl-btn--primary tl-btn--sm" data-filters-close>
				<?php esc_html_e( 'Show results', 'titan-labs' ); ?>
			</button>
		</div>
	</div>
</div>

<?php
do_action( 'woocommerce_after_main_content' );

get_footer( 'shop' );
