<?php
/**
 * Quality assurance widget.
 *
 * @package TitanLabs
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Repeater;

/**
 * Headline stats plus a grid of trust features.
 */
class Titan_Quality_Widget extends Titan_Widget_Base {

	public function get_name() {
		return 'titan-quality';
	}

	public function get_title() {
		return __( 'Quality Assurance', 'titan-labs' );
	}

	public function get_icon() {
		return 'eicon-shield-check';
	}

	/**
	 * Icon presets — key => inner SVG paths.
	 *
	 * @return array<string, array{label: string, svg: string}>
	 */
	private function icons() {
		return array(
			'check'   => array(
				'label' => __( 'Checklist', 'titan-labs' ),
				'svg'   => '<path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>',
			),
			'chart'   => array(
				'label' => __( 'Chart', 'titan-labs' ),
				'svg'   => '<path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/>',
			),
			'doc'     => array(
				'label' => __( 'Document', 'titan-labs' ),
				'svg'   => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M9 15h6M9 11h2"/>',
			),
			'truck'   => array(
				'label' => __( 'Shipping', 'titan-labs' ),
				'svg'   => '<rect x="1" y="3" width="15" height="13" rx="2"/><path d="M16 8h4l3 3v5h-7z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/>',
			),
			'flask'   => array(
				'label' => __( 'Flask', 'titan-labs' ),
				'svg'   => '<path d="M9 2v7L4 19a2 2 0 0 0 2 3h12a2 2 0 0 0 2-3l-5-10V2"/><path d="M8 2h8M7 15h10"/>',
			),
			'shield'  => array(
				'label' => __( 'Shield', 'titan-labs' ),
				'svg'   => '<path d="M12 2 4 6v6c0 5 3.4 8.9 8 10 4.6-1.1 8-5 8-10V6z"/><path d="m9 12 2 2 4-4"/>',
			),
		);
	}

	protected function register_controls() {

		$this->start_controls_section(
			'content',
			array( 'label' => __( 'Content', 'titan-labs' ) )
		);

		$this->add_heading_controls(
			__( 'Quality assurance', 'titan-labs' ),
			__( 'Tested. Verified. Documented.', 'titan-labs' ),
			__( 'Purity is not a marketing word at Titan Labs — it is a measured value. Every research peptide is screened by accredited third-party laboratories across Europe, and the full analysis is published.', 'titan-labs' )
		);

		$this->end_controls_section();

		/* ---------------------------------------------------------------
		 * Stats
		 * ------------------------------------------------------------ */
		$this->start_controls_section(
			'stats',
			array( 'label' => __( 'Headline Stats', 'titan-labs' ) )
		);

		$stat = new Repeater();

		$stat->add_control(
			'value',
			array(
				'label'   => __( 'Value', 'titan-labs' ),
				'type'    => Controls_Manager::TEXT,
				'default' => '≥99%',
			)
		);

		$stat->add_control(
			'label',
			array(
				'label'   => __( 'Label', 'titan-labs' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'HPLC purity', 'titan-labs' ),
			)
		);

		$this->add_control(
			'stats_list',
			array(
				'label'       => __( 'Stats', 'titan-labs' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $stat->get_controls(),
				'title_field' => '{{{ value }}} — {{{ label }}}',
				'default'     => array(
					array( 'value' => '≥99%', 'label' => __( 'HPLC purity', 'titan-labs' ) ),
					array( 'value' => '100%', 'label' => __( 'Peptides with COA', 'titan-labs' ) ),
					array( 'value' => 'EU', 'label' => __( 'Tracked shipping', 'titan-labs' ) ),
				),
			)
		);

		$this->end_controls_section();

		/* ---------------------------------------------------------------
		 * Features
		 * ------------------------------------------------------------ */
		$this->start_controls_section(
			'features',
			array( 'label' => __( 'Features', 'titan-labs' ) )
		);

		$icon_options = array();
		foreach ( $this->icons() as $key => $icon ) {
			$icon_options[ $key ] = $icon['label'];
		}

		$feature = new Repeater();

		$feature->add_control(
			'icon',
			array(
				'label'   => __( 'Icon', 'titan-labs' ),
				'type'    => Controls_Manager::SELECT,
				'options' => $icon_options,
				'default' => 'check',
			)
		);

		$feature->add_control(
			'title',
			array(
				'label'   => __( 'Title', 'titan-labs' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Independent labs', 'titan-labs' ),
			)
		);

		$feature->add_control(
			'text',
			array(
				'label'   => __( 'Text', 'titan-labs' ),
				'type'    => Controls_Manager::TEXTAREA,
				'rows'    => 3,
				'default' => __( 'Analysed by accredited third-party laboratories — never self-reported.', 'titan-labs' ),
			)
		);

		$this->add_control(
			'features_list',
			array(
				'label'       => __( 'Features', 'titan-labs' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $feature->get_controls(),
				'title_field' => '{{{ title }}}',
				'default'     => array(
					array(
						'icon'  => 'check',
						'title' => __( 'Independent labs', 'titan-labs' ),
						'text'  => __( 'Analysed by accredited third-party laboratories — never self-reported.', 'titan-labs' ),
					),
					array(
						'icon'  => 'chart',
						'title' => __( '≥99% purity', 'titan-labs' ),
						'text'  => __( 'HPLC quantifies purity and mass-spectrometry confirms peptide identity.', 'titan-labs' ),
					),
					array(
						'icon'  => 'doc',
						'title' => __( 'Full traceability', 'titan-labs' ),
						'text'  => __( 'Every peptide carries a unique identifier tied to its Certificate of Analysis.', 'titan-labs' ),
					),
					array(
						'icon'  => 'truck',
						'title' => __( 'Discreet & secure delivery', 'titan-labs' ),
						'text'  => __( 'Tracked dispatch across Europe in plain, unbranded packaging.', 'titan-labs' ),
					),
				),
			)
		);

		$this->add_control(
			'columns',
			array(
				'label'   => __( 'Columns', 'titan-labs' ),
				'type'    => Controls_Manager::SELECT,
				'default' => '4',
				'options' => array(
					'2' => __( '2 columns', 'titan-labs' ),
					'3' => __( '3 columns', 'titan-labs' ),
					'4' => __( '4 columns', 'titan-labs' ),
				),
			)
		);

		$this->add_background_control();

		$this->end_controls_section();
	}

	protected function render() {
		$s     = $this->get_settings_for_display();
		$icons = $this->icons();

		$allowed_svg = array(
			'path'   => array( 'd' => true ),
			'rect'   => array( 'x' => true, 'y' => true, 'width' => true, 'height' => true, 'rx' => true ),
			'circle' => array( 'cx' => true, 'cy' => true, 'r' => true ),
		);

		$this->open_section( $s );
		$this->render_heading( $s );

		if ( ! empty( $s['stats_list'] ) ) : ?>
			<dl class="tl-qa__stats">
				<?php foreach ( $s['stats_list'] as $stat ) : ?>
					<div>
						<dt><?php echo esc_html( $stat['value'] ); ?></dt>
						<dd><?php echo esc_html( $stat['label'] ); ?></dd>
					</div>
				<?php endforeach; ?>
			</dl>
		<?php endif;

		if ( ! empty( $s['features_list'] ) ) : ?>
			<div class="tl-grid tl-grid--<?php echo esc_attr( $s['columns'] ?? '4' ); ?>">
				<?php foreach ( $s['features_list'] as $feature ) : ?>
					<div class="tl-feature">
						<div class="tl-feature__icon">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
								stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
								<?php
								$key = $feature['icon'] ?? 'check';
								echo wp_kses( $icons[ $key ]['svg'] ?? $icons['check']['svg'], $allowed_svg );
								?>
							</svg>
						</div>
						<h3><?php echo esc_html( $feature['title'] ); ?></h3>
						<p><?php echo esc_html( $feature['text'] ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif;

		$this->close_section();
	}
}
