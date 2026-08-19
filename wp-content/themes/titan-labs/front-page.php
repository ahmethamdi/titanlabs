<?php
/**
 * Homepage template.
 *
 * @package TitanLabs
 */

defined( 'ABSPATH' ) || exit;

get_header();

$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
?>

<main id="main">

	<?php get_template_part( 'template-parts/home/hero' ); ?>
	<?php get_template_part( 'template-parts/home/categories' ); ?>
	<?php get_template_part( 'template-parts/home/bestsellers' ); ?>
	<?php get_template_part( 'template-parts/home/spotlight' ); ?>
	<?php get_template_part( 'template-parts/home/stack' ); ?>
	<?php get_template_part( 'template-parts/home/reviews' ); ?>
	<?php get_template_part( 'template-parts/home/quality' ); ?>
	<?php get_template_part( 'template-parts/home/coa' ); ?>

</main>

<?php
get_footer();
