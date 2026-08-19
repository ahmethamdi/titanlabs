<?php
/**
 * Stack builder promo widget.
 *
 * @package TitanLabs
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;

/**
 * Promotes the automatic volume discount, reading its live tiers.
 */
class Titan_Stack_Widget extends Titan_Widget_Base {

	public function get_name() {
		return 'titan-stack';
	}

	public function get_title() {
		return __( 'Stack Promo', 'titan-labs' );
	}

	public function get_icon() {
		return 'eicon-price-table';
	}

	protected function register_controls() {

		$this->start_controls_section(
			'content',
			array( 'label' => __( 'Content', 'titan-labs' ) )
		);

		$this->add_control(
			'eyebrow',
			array(
				'label'   => __( 'Eyebrow', 'titan-labs' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Mix & match', 'titan-labs' ),
			)
		);

		$this->add_control(
			'title',
			array(
				'label'       => __( 'Heading', 'titan-labs' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Build your own stack, save up to {max}%', 'titan-labs' ),
				'description' => __( 'Use {max} to insert the highest discount percentage.', 'titan-labs' ),
			)
		);

		$this->add_control(
			'lede',
			array(
				'label'   => __( 'Intro text', 'titan-labs' ),
				'type'    => Controls_Manager::TEXTAREA,
				'rows'    => 3,
				'default' => __( 'Combine any research peptides — vials, pens, sprays & orals in one pack. Applied automatically to every item.', 'titan-labs' ),
			)
		);

		$this->add_control(
			'cta_text',
			array(
				'label'   => __( 'Button text', 'titan-labs' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Build a stack', 'titan-labs' ),
			)
		);

		$this->add_control(
			'cta_link',
			array(
				'label'   => __( 'Button link', 'titan-labs' ),
				'type'    => Controls_Manager::URL,
				'default' => array( 'url' => home_url( '/stack/' ) ),
			)
		);

		$this->add_control(
			'show_tiers',
			array(
				'label'        => __( 'Show discount tiers', 'titan-labs' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'tiers_note',
			array(
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => sprintf(
					'<div style="font-size:12px;line-height:1.5">%s <a href="%s" target="_blank">%s</a></div>',
					esc_html__( 'Tiers are managed in', 'titan-labs' ),
					esc_url( admin_url( 'customize.php' ) ),
					esc_html__( 'Customizer → Titan Labs → Stack Builder Discount', 'titan-labs' )
				),
				'content_classes' => 'elementor-descriptor',
				'condition'       => array( 'show_tiers' => 'yes' ),
			)
		);

		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();

		$t1_qty = (int) get_theme_mod( 'titan_stack_tier1_qty', 4 );
		$t1_pct = (int) get_theme_mod( 'titan_stack_tier1_pct', 5 );
		$t2_qty = (int) get_theme_mod( 'titan_stack_tier2_qty', 10 );
		$t2_pct = (int) get_theme_mod( 'titan_stack_tier2_pct', 10 );

		$title = str_replace( '{max}', (string) max( $t1_pct, $t2_pct ), $s['title'] ?? '' );
		?>
		<section class="tl-section" style="padding-block:clamp(2rem,1.2rem+3vw,4rem)">
			<div class="tl-container">
				<div class="tl-stack-promo">

					<div>
						<?php if ( ! empty( $s['eyebrow'] ) ) : ?>
							<p class="tl-eyebrow"><?php echo esc_html( $s['eyebrow'] ); ?></p>
						<?php endif; ?>

						<?php if ( $title ) : ?>
							<h2><?php echo esc_html( $title ); ?></h2>
						<?php endif; ?>

						<?php if ( ! empty( $s['lede'] ) ) : ?>
							<p class="tl-lede tl-mb-0"><?php echo esc_html( $s['lede'] ); ?></p>
						<?php endif; ?>

						<?php if ( 'yes' === ( $s['show_tiers'] ?? 'yes' ) ) : ?>
							<div class="tl-stack-promo__tiers">
								<span class="tl-badge">
									<?php
									printf(
										/* translators: 1: item count, 2: discount percentage */
										esc_html__( '%1$d items · −%2$d%%', 'titan-labs' ),
										$t1_qty,
										$t1_pct
									);
									?>
								</span>
								<span class="tl-badge">
									<?php
									printf(
										/* translators: 1: item count, 2: discount percentage */
										esc_html__( '%1$d items · −%2$d%%', 'titan-labs' ),
										$t2_qty,
										$t2_pct
									);
									?>
								</span>
							</div>
						<?php endif; ?>
					</div>

					<div>
						<?php if ( ! empty( $s['cta_text'] ) ) : ?>
							<a class="tl-btn tl-btn--primary tl-btn--block"
								href="<?php echo esc_url( $s['cta_link']['url'] ?? '#' ); ?>">
								<?php echo esc_html( $s['cta_text'] ); ?>
							</a>
						<?php endif; ?>
					</div>

				</div>
			</div>
		</section>
		<?php
	}
}
