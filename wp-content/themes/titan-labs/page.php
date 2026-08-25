<?php
/**
 * Static page template.
 *
 * @package TitanLabs
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	?>

	<div class="tl-pagehero">
		<div class="tl-container">
			<?php if ( function_exists( 'woocommerce_breadcrumb' ) ) { woocommerce_breadcrumb(); } ?>
			<h1 class="tl-mb-0"><?php the_title(); ?></h1>
		</div>
	</div>

	<?php
	/*
	 * .tl-prose caps the measure at 74ch, which is right for reading but wrong
	 * for the cart and checkout: their two-column block layouts were being
	 * squeezed into the left half of the page. Those get the full container.
	 */
	$titan_wide = function_exists( 'is_cart' ) && ( is_cart() || is_checkout() || is_account_page() );
	?>

	<main id="main" class="tl-container" style="padding-bottom:4rem">
		<div class="<?php echo $titan_wide ? 'tl-pagewide' : 'tl-prose'; ?>">
			<?php
			the_content();
			wp_link_pages( array(
				'before' => '<div class="tl-small">' . esc_html__( 'Pages:', 'titan-labs' ) . ' ',
				'after'  => '</div>',
			) );
			?>
		</div>
	</main>

	<?php
endwhile;

get_footer();
