<?php
/**
 * Hero widget.
 *
 * @package TitanLabs
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use Elementor\Utils;

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

				<div class="tl-hero__visual">
					<?php if ( ! empty( $s['image']['url'] ) ) : ?>
						<img src="<?php echo esc_url( $s['image']['url'] ); ?>"
							alt="<?php echo esc_attr( Utils::get_image_alt( $s['image'] ) ); ?>">
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
