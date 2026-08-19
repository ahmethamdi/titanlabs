<?php
/**
 * Template Name: Full Width (Elementor)
 *
 * No page hero, no container — the page content controls its own layout.
 * Use this for pages built with Titan Labs Elementor widgets, which each
 * bring their own full-bleed section and inner container.
 *
 * @package TitanLabs
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<main id="main">
	<?php
	while ( have_posts() ) :
		the_post();
		the_content();
	endwhile;
	?>
</main>

<?php
get_footer();
