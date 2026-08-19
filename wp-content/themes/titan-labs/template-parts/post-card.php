<?php
/**
 * Blog post card.
 *
 * @package TitanLabs
 */

defined( 'ABSPATH' ) || exit;
?>
<article <?php post_class( 'tl-post-card' ); ?>>
	<?php if ( has_post_thumbnail() ) : ?>
		<a class="tl-post-card__media" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
			<?php the_post_thumbnail( 'titan-wide' ); ?>
		</a>
	<?php endif; ?>

	<div class="tl-post-card__body">
		<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
		<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
		<p><?php echo esc_html( get_the_excerpt() ); ?></p>
	</div>
</article>
