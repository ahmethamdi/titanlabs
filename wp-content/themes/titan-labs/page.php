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

	<main id="main" class="tl-container" style="padding-bottom:4rem">
		<div class="tl-prose">
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
