<?php
/**
 * Featured product spotlight widget.
 *
 * @package TitanLabs
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;

/**
 * A dark, full-width spotlight for one hand-picked product.
 */
class Titan_Spotlight_Widget extends Titan_Widget_Base {

	public function get_name() {
		return 'titan-spotlight';
	}

	public function get_title() {
		return __( 'Product Spotlight', 'titan-labs' );
	}

	public function get_icon() {
		return 'eicon-single-post';
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
				'default' => __( 'Featured research peptide', 'titan-labs' ),
			)
		);

		$this->add_control(
			'source',
			array(
				'label'   => __( 'Product', 'titan-labs' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'featured',
				'options' => array(
					'featured' => __( 'First featured product', 'titan-labs' ),
					'pick'     => __( 'Pick a specific product', 'titan-labs' ),
				),
			)
		);

		$this->add_control(
			'product',
			array(
				'label'       => __( 'Choose product', 'titan-labs' ),
				'type'        => Controls_Manager::SELECT2,
				'label_block' => true,
				'options'     => $this->product_options(),
				'condition'   => array( 'source' => 'pick' ),
			)
		);

		$this->add_control(
			'description',
			array(
				'label'       => __( 'Description override', 'titan-labs' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 4,
				'description' => __( 'Leave empty to use the product short description.', 'titan-labs' ),
			)
		);

		$this->add_control(
			'cta_text',
			array(
				'label'       => __( 'Button text', 'titan-labs' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Explore {product}', 'titan-labs' ),
				'description' => __( 'Use {product} to insert the product name.', 'titan-labs' ),
			)
		);

		$this->add_control(
			'note',
			array(
				'label'   => __( 'Small print', 'titan-labs' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'For laboratory research use only. Not for human consumption.', 'titan-labs' ),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'specs',
			array( 'label' => __( 'Specs', 'titan-labs' ) )
		);

		$this->add_control(
			'show_purity',
			array(
				'label'        => __( 'Show tested purity', 'titan-labs' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'form_label',
			array(
				'label'   => __( 'Form', 'titan-labs' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Lyophilized powder', 'titan-labs' ),
			)
		);

		$this->add_control(
			'show_size',
			array(
				'label'        => __( 'Show size', 'titan-labs' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Resolves the product to spotlight.
	 *
	 * @param array $s Settings.
	 * @return WC_Product|null
	 */
	private function resolve_product( $s ) {
		if ( 'pick' === ( $s['source'] ?? 'featured' ) && ! empty( $s['product'] ) ) {
			$post = get_page_by_path( $s['product'], OBJECT, 'product' );
			return $post ? wc_get_product( $post->ID ) : null;
		}

		$featured = wc_get_products( array(
			'featured' => true,
			'limit'    => 1,
			'status'   => 'publish',
		) );

		return $featured ? $featured[0] : null;
	}

	protected function render() {
		$s = $this->get_settings_for_display();

		if ( ! function_exists( 'wc_get_products' ) ) {
			$this->render_placeholder( __( 'WooCommerce is required for this widget.', 'titan-labs' ) );
			return;
		}

		$product = $this->resolve_product( $s );

		if ( ! $product ) {
			$this->render_placeholder( __( 'Pick a product, or mark one as Featured in WooCommerce.', 'titan-labs' ) );
			return;
		}

		$purity = get_post_meta( $product->get_id(), '_titan_purity', true );
		$size   = get_post_meta( $product->get_id(), '_titan_size', true );

		$body = $s['description'] ?: wp_strip_all_tags(
			$product->get_short_description() ?: $product->get_description()
		);

		$cta = str_replace( '{product}', $product->get_name(), $s['cta_text'] ?: __( 'Explore {product}', 'titan-labs' ) );
		?>
		<section class="tl-spotlight">
			<div class="tl-container tl-spotlight__inner">

				<div>
					<?php if ( ! empty( $s['eyebrow'] ) ) : ?>
						<p class="tl-eyebrow"><?php echo esc_html( $s['eyebrow'] ); ?></p>
					<?php endif; ?>

					<h2><?php echo esc_html( $product->get_name() ); ?></h2>

					<?php if ( $body ) : ?>
						<p style="opacity:.85"><?php echo esc_html( $body ); ?></p>
					<?php endif; ?>

					<dl class="tl-spotlight__specs">
						<?php if ( 'yes' === ( $s['show_purity'] ?? 'yes' ) && $purity ) : ?>
							<div>
								<dt><?php esc_html_e( 'Tested purity', 'titan-labs' ); ?></dt>
								<dd><?php echo esc_html( $purity ); ?></dd>
							</div>
						<?php endif; ?>

						<?php if ( ! empty( $s['form_label'] ) ) : ?>
							<div>
								<dt><?php esc_html_e( 'Form', 'titan-labs' ); ?></dt>
								<dd><?php echo esc_html( $s['form_label'] ); ?></dd>
							</div>
						<?php endif; ?>

						<?php if ( 'yes' === ( $s['show_size'] ?? 'yes' ) && $size ) : ?>
							<div>
								<dt><?php esc_html_e( 'Size', 'titan-labs' ); ?></dt>
								<dd><?php echo esc_html( $size ); ?></dd>
							</div>
						<?php endif; ?>
					</dl>

					<div class="tl-spotlight__price"><?php echo wp_kses_post( $product->get_price_html() ); ?></div>

					<a class="tl-btn tl-btn--primary" href="<?php echo esc_url( $product->get_permalink() ); ?>">
						<?php echo esc_html( $cta ); ?>
					</a>

					<?php if ( ! empty( $s['note'] ) ) : ?>
						<p class="tl-spotlight__note" style="margin-top:1rem"><?php echo esc_html( $s['note'] ); ?></p>
					<?php endif; ?>
				</div>

				<div class="tl-spotlight__media">
					<?php echo wp_kses_post( $product->get_image( 'titan-wide' ) ); ?>
				</div>

			</div>
		</section>
		<?php
	}
}
