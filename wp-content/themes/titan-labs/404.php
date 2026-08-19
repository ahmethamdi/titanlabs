<?php
/**
 * 404 template.
 *
 * @package TitanLabs
 */

defined( 'ABSPATH' ) || exit;

get_header();

$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
?>

<main id="main" class="tl-container tl-section tl-text-center">
	<p class="tl-eyebrow"><?php esc_html_e( 'Error 404', 'titan-labs' ); ?></p>
	<h1><?php esc_html_e( 'This page could not be found.', 'titan-labs' ); ?></h1>
	<p class="tl-lede" style="margin-inline:auto">
		<?php esc_html_e( 'The page you are looking for may have moved. Try the shop, or search for a peptide.', 'titan-labs' ); ?>
	</p>

	<div class="tl-flex tl-gap" style="justify-content:center;flex-wrap:wrap;margin-top:1.5rem">
		<a class="tl-btn tl-btn--primary" href="<?php echo esc_url( $shop_url ); ?>">
			<?php esc_html_e( 'Shop Research Peptides', 'titan-labs' ); ?>
		</a>
		<a class="tl-btn tl-btn--ghost" href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<?php esc_html_e( 'Back to homepage', 'titan-labs' ); ?>
		</a>
	</div>
</main>

<?php
get_footer();
