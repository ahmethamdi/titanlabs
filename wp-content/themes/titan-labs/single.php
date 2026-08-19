<?php
/**
 * Single blog post.
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
			<p class="tl-eyebrow"><?php echo esc_html( get_the_date() ); ?></p>
			<h1 class="tl-mb-0"><?php the_title(); ?></h1>
		</div>
	</div>

	<main id="main" class="tl-container" style="padding-bottom:4rem">
		<article <?php post_class( 'tl-prose' ); ?>>
			<?php if ( has_post_thumbnail() ) : ?>
				<?php the_post_thumbnail( 'titan-wide', array( 'style' => 'margin-bottom:1.5rem' ) ); ?>
			<?php endif; ?>

			<?php the_content(); ?>
		</article>

		<?php
		if ( comments_open() || get_comments_number() ) {
			echo '<div class="tl-prose" style="margin-top:3rem">';
			comments_template();
			echo '</div>';
		}
		?>
	</main>

	<?php
endwhile;

get_footer();
