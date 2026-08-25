<?php
/**
 * Homepage — quality assurance.
 *
 * @package TitanLabs
 */

defined( 'ABSPATH' ) || exit;

$features = array(
	array(
		'icon'  => '<path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>',
		'title' => __( 'Independent labs', 'titan-labs' ),
		'text'  => __( 'Analysed by accredited third-party laboratories — never self-reported.', 'titan-labs' ),
	),
	array(
		'icon'  => '<path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/>',
		'title' => __( '≥99% purity', 'titan-labs' ),
		'text'  => __( 'HPLC quantifies purity and mass-spectrometry confirms peptide identity.', 'titan-labs' ),
	),
	array(
		'icon'  => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M9 15h6M9 11h2"/>',
		'title' => __( 'Full traceability', 'titan-labs' ),
		'text'  => __( 'Every peptide carries a unique identifier tied to its Certificate of Analysis.', 'titan-labs' ),
	),
	array(
		'icon'  => '<rect x="1" y="3" width="15" height="13" rx="2"/><path d="M16 8h4l3 3v5h-7z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/>',
		'title' => __( 'Discreet & secure delivery', 'titan-labs' ),
		'text'  => __( 'Tracked dispatch across Europe in plain, unbranded packaging.', 'titan-labs' ),
	),
);
?>

<section class="tl-section">
	<div class="tl-container">

		<div class="tl-sectionhead tl-sectionhead--center">
			<div>
				<p class="tl-eyebrow"><?php esc_html_e( 'Quality assurance', 'titan-labs' ); ?></p>
				<h2><?php esc_html_e( 'Tested. Verified. Documented.', 'titan-labs' ); ?></h2>
				<p class="tl-lede tl-mb-0">
					<?php esc_html_e( 'Purity is not a marketing word at Titan Labs — it is a measured value. Every research peptide is screened by accredited third-party laboratories across Europe, and the full analysis is published.', 'titan-labs' ); ?>
				</p>
			</div>
		</div>

		<dl class="tl-qa__stats">
			<div>
				<dt>≥99%</dt>
				<dd><?php esc_html_e( 'HPLC purity', 'titan-labs' ); ?></dd>
			</div>
			<div>
				<dt>100%</dt>
				<dd><?php esc_html_e( 'Peptides with COA', 'titan-labs' ); ?></dd>
			</div>
			<div>
				<dt>EU</dt>
				<dd><?php esc_html_e( 'Tracked shipping', 'titan-labs' ); ?></dd>
			</div>
		</dl>

		<div class="tl-grid tl-grid--4">
			<?php foreach ( $features as $feature ) : ?>
				<div class="tl-feature">
					<div class="tl-feature__icon">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
							stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
							<?php echo wp_kses( $feature['icon'], array( 'path' => array( 'd' => true ), 'rect' => array( 'x' => true, 'y' => true, 'width' => true, 'height' => true, 'rx' => true ), 'circle' => array( 'cx' => true, 'cy' => true, 'r' => true ) ) ); ?>
						</svg>
					</div>
					<h3><?php echo esc_html( $feature['title'] ); ?></h3>
					<p><?php echo esc_html( $feature['text'] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>

	</div>
</section>
