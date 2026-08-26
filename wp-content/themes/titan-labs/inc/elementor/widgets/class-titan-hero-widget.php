<?php
/**
 * Hero widget.
 *
 * @package TitanLabs
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Repeater;

/**
 * The homepage hero: eyebrow, headline, sub-headline, CTAs and trust stats.
 */
class Titan_Hero_Widget extends Titan_Widget_Base {

	public function get_name() {
		return 'titan-hero';
	}

	public function get_title() {
		return __( 'Hero', 'titan-labs' );
	}

	public function get_icon() {
		return 'eicon-banner';
	}

	protected function register_controls() {

		/* ---------------------------------------------------------------
		 * Content
		 * ------------------------------------------------------------ */
		$this->start_controls_section(
			'content',
			array( 'label' => __( 'Content', 'titan-labs' ) )
		);

		$this->add_control(
			'eyebrow',
			array(
				'label'   => __( 'Eyebrow', 'titan-labs' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Research peptides · Europe', 'titan-labs' ),
				'dynamic' => array( 'active' => true ),
			)
		);

		$this->add_control(
			'title',
			array(
				'label'   => __( 'Headline', 'titan-labs' ),
				'type'    => Controls_Manager::TEXTAREA,
				'rows'    => 2,
				'default' => __( 'Lab-verified research peptides for Europe.', 'titan-labs' ),
				'dynamic' => array( 'active' => true ),
			)
		);

		$this->add_control(
			'subtitle',
			array(
				'label'   => __( 'Sub-headline', 'titan-labs' ),
				'type'    => Controls_Manager::TEXTAREA,
				'rows'    => 4,
				'default' => __( 'Buy research-grade peptides built for the lab. Every Titan Labs peptide is third-party HPLC and mass-spec tested to ≥99% purity, with a Certificate of Analysis on every peptide — discreetly & securely delivered across Europe.', 'titan-labs' ),
				'dynamic' => array( 'active' => true ),
			)
		);

		$this->end_controls_section();

		/* ---------------------------------------------------------------
		 * Buttons
		 * ------------------------------------------------------------ */
		$this->start_controls_section(
			'buttons',
			array( 'label' => __( 'Buttons', 'titan-labs' ) )
		);

		$this->add_control(
			'cta_text',
			array(
				'label'   => __( 'Primary button', 'titan-labs' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Shop Research Peptides', 'titan-labs' ),
			)
		);

		$this->add_control(
			'cta_link',
			array(
				'label'   => __( 'Primary link', 'titan-labs' ),
				'type'    => Controls_Manager::URL,
				'default' => array( 'url' => function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : '' ),
			)
		);

		$this->add_control(
			'cta2_text',
			array(
				'label'   => __( 'Secondary button', 'titan-labs' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'View Lab Results', 'titan-labs' ),
			)
		);

		$this->add_control(
			'cta2_link',
			array(
				'label'   => __( 'Secondary link', 'titan-labs' ),
				'type'    => Controls_Manager::URL,
				'default' => array( 'url' => home_url( '/lab-results/' ) ),
			)
		);

		$this->end_controls_section();

		/* ---------------------------------------------------------------
		 * Trust stats
		 * ------------------------------------------------------------ */
		$this->start_controls_section(
			'stats',
			array( 'label' => __( 'Trust Stats', 'titan-labs' ) )
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'value',
			array(
				'label'   => __( 'Value', 'titan-labs' ),
				'type'    => Controls_Manager::TEXT,
				'default' => '≥99%',
			)
		);

		$repeater->add_control(
			'label',
			array(
				'label'   => __( 'Label', 'titan-labs' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Tested purity', 'titan-labs' ),
			)
		);

		$this->add_control(
			'stats_list',
			array(
				'label'       => __( 'Stats', 'titan-labs' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{{ value }}} — {{{ label }}}',
				'default'     => array(
					array( 'value' => '≥99%', 'label' => __( 'Tested purity', 'titan-labs' ) ),
					array( 'value' => '100%', 'label' => __( '3rd-party verified', 'titan-labs' ) ),
					array( 'value' => __( 'Europe', 'titan-labs' ), 'label' => __( 'Tracked DHL shipping', 'titan-labs' ) ),
				),
			)
		);

		$this->end_controls_section();

		/* ---------------------------------------------------------------
		 * Media
		 * ------------------------------------------------------------ */
		$this->start_controls_section(
			'media',
			array( 'label' => __( 'Image', 'titan-labs' ) )
		);

		$this->add_control(
			'image',
			array(
				'label' => __( 'Hero image', 'titan-labs' ),
				'type'  => Controls_Manager::MEDIA,
			)
		);

		$this->add_control(
			'show_pattern',
			array(
				'label'        => __( 'Molecule backdrop', 'titan-labs' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();

		$classes = 'tl-hero';
		if ( 'yes' === ( $s['show_pattern'] ?? 'yes' ) ) {
			$classes .= ' tl-molecule-bg';
		}
		?>
		<section class="<?php echo esc_attr( $classes ); ?>">
			<div class="tl-container tl-hero__inner">

				<div>
					<?php if ( ! empty( $s['eyebrow'] ) ) : ?>
						<p class="tl-eyebrow"><?php echo esc_html( $s['eyebrow'] ); ?></p>
					<?php endif; ?>

					<?php if ( ! empty( $s['title'] ) ) : ?>
						<h1><?php echo esc_html( $s['title'] ); ?></h1>
					<?php endif; ?>

					<?php if ( ! empty( $s['subtitle'] ) ) : ?>
						<p class="tl-hero__sub"><?php echo esc_html( $s['subtitle'] ); ?></p>
					<?php endif; ?>

					<div class="tl-hero__cta">
						<?php if ( ! empty( $s['cta_text'] ) ) : ?>
							<a class="tl-btn tl-btn--primary"
								href="<?php echo esc_url( $s['cta_link']['url'] ?? '#' ); ?>"
								<?php echo ! empty( $s['cta_link']['is_external'] ) ? 'target="_blank" rel="noopener"' : ''; ?>>
								<?php echo esc_html( $s['cta_text'] ); ?>
							</a>
						<?php endif; ?>

						<?php if ( ! empty( $s['cta2_text'] ) ) : ?>
							<a class="tl-btn tl-btn--ghost"
								href="<?php echo esc_url( $s['cta2_link']['url'] ?? '#' ); ?>"
								<?php echo ! empty( $s['cta2_link']['is_external'] ) ? 'target="_blank" rel="noopener"' : ''; ?>>
								<?php echo esc_html( $s['cta2_text'] ); ?>
							</a>
						<?php endif; ?>
					</div>

					<?php if ( ! empty( $s['stats_list'] ) ) : ?>
						<dl class="tl-hero__trust">
							<?php foreach ( $s['stats_list'] as $stat ) : ?>
								<div>
									<dt><?php echo esc_html( $stat['value'] ); ?></dt>
									<dd><?php echo esc_html( $stat['label'] ); ?></dd>
								</div>
							<?php endforeach; ?>
						</dl>
					<?php endif; ?>
				</div>

				<?php
				/*
				 * With no image set the hero showed a bare "TL" monogram — the
				 * most valuable area on the site rendering as a placeholder.
				 * Fall back to a real product shot, which the catalogue always
				 * has, and label it with the lab data that justifies the claim
				 * in the headline.
				 */
				$hero_img  = ! empty( $s['image']['url'] ) ? $s['image']['url'] : '';
				/*
				 * Read the alt text from WordPress rather than
				 * Elementor\Utils::get_image_alt(), which no longer exists in
				 * current Elementor and threw a fatal the moment a hero image
				 * was set — taking the whole Elementor render down with it and
				 * silently falling back to the coded template.
				 */
				$hero_alt = '';
				if ( ! empty( $s['image']['id'] ) ) {
					$hero_alt = (string) get_post_meta( (int) $s['image']['id'], '_wp_attachment_image_alt', true );
				}
				$hero_prod = null;

				if ( ! $hero_img && function_exists( 'wc_get_product' ) ) {
					/*
					 * Only part of the catalogue has been through the lab, and the
					 * chips are the whole point of showing a product here — so
					 * require the purity meta rather than taking the most popular
					 * product and hoping it has one. WP_Query, not
					 * wc_get_products(), because the latter silently drops
					 * meta_query and would hand back an unlabelled product.
					 */
					$hero_q = new WP_Query( array(
						'post_type'           => 'product',
						'post_status'         => 'publish',
						'posts_per_page'      => 1,
						'meta_key'            => 'total_sales',
						'orderby'             => 'meta_value_num',
						'order'               => 'DESC',
						'ignore_sticky_posts' => true,
						'no_found_rows'       => true,
						'meta_query'          => array(
							array(
								'key'     => '_titan_purity',
								'value'   => '',
								'compare' => '!=',
							),
						),
					) );

					if ( ! empty( $hero_q->posts ) ) {
						$hero_prod = wc_get_product( $hero_q->posts[0] );
					} else {
						$fallback = wc_get_products( array(
							'limit'   => 1,
							'status'  => 'publish',
							'orderby' => 'popularity',
						) );
						$hero_prod = $fallback ? $fallback[0] : null;
					}

					if ( $hero_prod ) {
						$hero_img = wp_get_attachment_image_url( $hero_prod->get_image_id(), 'large' );
						$hero_alt = $hero_prod->get_name();
					}
				}
				?>
				<?php
				/*
				 * An image chosen in the editor is a cut-out render on a
				 * transparent background, so it stands on its own instead of
				 * sitting in the framed card the catalogue fallback needs.
				 */
				$hero_visual_class = 'tl-hero__visual';
				if ( $hero_prod ) {
					$hero_visual_class .= ' tl-hero__visual--product';
				} elseif ( $hero_img ) {
					$hero_visual_class .= ' tl-hero__visual--cutout';
				}
				?>
				<div class="<?php echo esc_attr( $hero_visual_class ); ?>">
					<?php if ( $hero_img ) : ?>
						<img src="<?php echo esc_url( $hero_img ); ?>"
							alt="<?php echo esc_attr( $hero_alt ); ?>">

						<?php
						if ( $hero_prod ) :
							$hero_lab = function_exists( 'titan_lab_data' )
								? titan_lab_data( $hero_prod->get_id() )
								: array();
							?>
							<?php if ( ! empty( $hero_lab['purity'] ) ) : ?>
								<div class="tl-hero__labchip">
									<span class="tl-hero__labchip-dot" aria-hidden="true"></span>
									<span>
										<strong><?php echo esc_html( $hero_lab['purity'] ); ?></strong>
										<em><?php esc_html_e( 'Tested purity', 'titan-labs' ); ?></em>
									</span>
								</div>
							<?php endif; ?>
							<?php if ( ! empty( $hero_lab['batch'] ) ) : ?>
								<div class="tl-hero__batchchip">
									<?php esc_html_e( 'Batch', 'titan-labs' ); ?>
									<strong><?php echo esc_html( $hero_lab['batch'] ); ?></strong>
								</div>
							<?php endif; ?>
						<?php endif; ?>

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
		<?php
	}
}
