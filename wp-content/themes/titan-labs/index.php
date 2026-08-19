<?php
/**
 * Blog index / fallback template.
 *
 * @package TitanLabs
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<div class="tl-pagehero">
	<div class="tl-container">
		<h1 class="tl-mb-0">
			<?php
			if ( is_home() && ! is_front_page() ) {
				echo esc_html( get_the_title( (int) get_option( 'page_for_posts' ) ) );
			} elseif ( is_search() ) {
				printf(
					/* translators: %s: search term */
					esc_html__( 'Search results for “%s”', 'titan-labs' ),
					esc_html( get_search_query() )
				);
			} else {
				esc_html_e( 'Latest articles', 'titan-labs' );
			}
			?>
		</h1>
	</div>
</div>

<main id="main" class="tl-container" style="padding-bottom:4rem">

	<?php if ( have_posts() ) : ?>
		<div class="tl-grid tl-grid--3">
			<?php
			while ( have_posts() ) :
				the_post();
				get_template_part( 'template-parts/post-card' );
			endwhile;
			?>
		</div>

		<div style="margin-top:2.5rem">
			<?php
			the_posts_pagination( array(
				'mid_size'  => 2,
				'prev_text' => __( '← Previous', 'titan-labs' ),
				'next_text' => __( 'Next →', 'titan-labs' ),
			) );
			?>
		</div>
	<?php else : ?>
		<p class="tl-muted"><?php esc_html_e( 'Nothing found.', 'titan-labs' ); ?></p>
		<?php get_search_form(); ?>
	<?php endif; ?>

</main>

<?php
get_footer();
