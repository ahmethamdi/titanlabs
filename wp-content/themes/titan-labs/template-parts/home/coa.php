<?php
/**
 * Homepage — Certificate of Analysis batch table.
 *
 * The table itself lives in template-parts/coa-table.php so the Lab Results
 * page can render the same markup through the [titan_coa] shortcode.
 *
 * @package TitanLabs
 */

defined( 'ABSPATH' ) || exit;

if ( ! titan_get_coa_rows( 1 ) ) {
	return;
}
?>

<section class="tl-section" id="coa" style="scroll-margin-top:6rem">
	<div class="tl-container">

		<div class="tl-sectionhead">
			<div>
				<p class="tl-eyebrow"><?php esc_html_e( 'Certificate of Analysis', 'titan-labs' ); ?></p>
				<h2><?php esc_html_e( 'Batch Testing Results', 'titan-labs' ); ?></h2>
				<p class="tl-lede tl-mb-0">
					<?php esc_html_e( 'Every batch is independently tested before release. Search any peptide or batch number, open its Certificate of Analysis, and verify it independently with the testing lab.', 'titan-labs' ); ?>
				</p>
			</div>
		</div>

		<?php get_template_part( 'template-parts/coa-table', null, array( 'limit' => 60 ) ); ?>

	</div>
</section>
