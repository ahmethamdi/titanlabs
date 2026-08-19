<?php
/**
 * Homepage template.
 *
 * When the front page has been built in Elementor, its content is rendered
 * instead — the coded sections below stay as a fallback so the homepage
 * still works if Elementor is deactivated.
 *
 * @package TitanLabs
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<main id="main">
	<?php
	if ( titan_is_built_with_elementor() ) {

		while ( have_posts() ) :
			the_post();
			the_content();
		endwhile;

	} else {

		get_template_part( 'template-parts/home/hero' );
		get_template_part( 'template-parts/home/categories' );
		get_template_part( 'template-parts/home/bestsellers' );
		get_template_part( 'template-parts/home/spotlight' );
		get_template_part( 'template-parts/home/stack' );
		get_template_part( 'template-parts/home/reviews' );
		get_template_part( 'template-parts/home/quality' );
		get_template_part( 'template-parts/home/coa' );

	}
	?>
</main>

<?php
get_footer();
