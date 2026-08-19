<?php
/**
 * Archive template.
 *
 * @package TitanLabs
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<div class="tl-pagehero">
	<div class="tl-container">
		<?php the_archive_title( '<h1 class="tl-mb-0">', '</h1>' ); ?>
		<?php the_archive_description( '<p class="tl-lede" style="margin-top:.75rem">', '</p>' ); ?>
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
			<?php the_posts_pagination( array( 'mid_size' => 2 ) ); ?>
		</div>
	<?php else : ?>
		<p class="tl-muted"><?php esc_html_e( 'Nothing found in this archive.', 'titan-labs' ); ?></p>
	<?php endif; ?>
</main>

<?php
get_footer();
