<?php
/**
 * Homepage hero.
 *
 * @package TitanLabs
 */

defined( 'ABSPATH' ) || exit;

$hero_image = (int) get_theme_mod( 'titan_hero_image', 0 );
$cta_url    = get_theme_mod( 'titan_hero_cta_url', '/shop/' );
$cta_url    = $cta_url && str_starts_with( $cta_url, 'http' ) ? $cta_url : home_url( $cta_url );
?>

<section class="tl-hero tl-molecule-bg">
	<div class="tl-container tl-hero__inner">

		<div>
			<p class="tl-eyebrow"><?php echo esc_html( get_theme_mod( 'titan_hero_eyebrow', __( 'Research peptides · Europe', 'titan-labs' ) ) ); ?></p>

			<h1><?php echo wp_kses_post( get_theme_mod( 'titan_hero_title', __( 'Lab-verified research peptides for Europe.', 'titan-labs' ) ) ); ?></h1>

			<p class="tl-hero__sub">
				<?php echo wp_kses_post( get_theme_mod( 'titan_hero_sub', __( 'Buy research-grade peptides built for the lab. Every Titan Labs peptide is third-party HPLC and mass-spec tested to ≥99% purity, with a Certificate of Analysis on every peptide — discreetly & securely delivered across Europe.', 'titan-labs' ) ) ); ?>
			</p>

			<div class="tl-hero__cta">
				<a class="tl-btn tl-btn--primary" href="<?php echo esc_url( $cta_url ); ?>">
					<?php echo esc_html( get_theme_mod( 'titan_hero_cta_text', __( 'Shop Research Peptides', 'titan-labs' ) ) ); ?>
				</a>
				<a class="tl-btn tl-btn--ghost" href="<?php echo esc_url( home_url( '/lab-results/' ) ); ?>">
					<?php esc_html_e( 'View Lab Results', 'titan-labs' ); ?>
				</a>
			</div>

			<dl class="tl-hero__trust">
				<?php for ( $i = 1; $i <= 3; $i++ ) : ?>
					<div>
						<dt><?php echo esc_html( get_theme_mod( "titan_trust_{$i}_value", '' ) ); ?></dt>
						<dd><?php echo esc_html( get_theme_mod( "titan_trust_{$i}_label", '' ) ); ?></dd>
					</div>
				<?php endfor; ?>
			</dl>
		</div>

		<div class="tl-hero__visual">
			<?php if ( $hero_image ) : ?>
				<?php echo wp_get_attachment_image( $hero_image, 'titan-wide', false, array( 'alt' => '' ) ); ?>
			<?php else : ?>
				<div class="tl-text-center" style="padding:2rem">
					<span class="tl-logo__mark" aria-hidden="true"
						style="width:72px;height:72px;font-size:1.6rem;margin:0 auto 1rem">TL</span>
					<p class="tl-muted tl-small tl-mb-0">
						<?php esc_html_e( 'HPLC · Mass spectrometry · Certificate of Analysis', 'titan-labs' ); ?>
					</p>
				</div>
			<?php endif; ?>
		</div>

	</div>
</section>
