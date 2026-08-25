<?php
/**
 * Tabbed bestsellers widget.
 *
 * @package TitanLabs
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Repeater;

/**
 * Product grid with a tab per format (vials, pens, sprays, orals).
 */
class Titan_Bestsellers_Widget extends Titan_Widget_Base {

	public function get_name() {
		return 'titan-bestsellers';
	}

	public function get_title() {
		return __( 'Product Tabs', 'titan-labs' );
	}

	public function get_icon() {
		return 'eicon-tabs';
	}

	protected function register_controls() {

		$this->start_controls_section(
			'content',
			array( 'label' => __( 'Content', 'titan-labs' ) )
		);

		$this->add_heading_controls(
			__( 'Most ordered in Europe', 'titan-labs' ),
			__( 'Bestselling Research Peptides', 'titan-labs' ),
			''
		);

		$this->end_controls_section();

		/* ---------------------------------------------------------------
		 * Tabs
		 * ------------------------------------------------------------ */
		$this->start_controls_section(
			'tabs',
			array( 'label' => __( 'Tabs', 'titan-labs' ) )
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'label',
			array(
				'label'   => __( 'Tab label', 'titan-labs' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Vials', 'titan-labs' ),
			)
		);

		$repeater->add_control(
			'category',
			array(
				'label'   => __( 'Product category', 'titan-labs' ),
				'type'    => Controls_Manager::SELECT,
				'options' => $this->product_category_options(),
				'default' => 'peptide-vials',
			)
		);

		$this->add_control(
			'tabs_list',
			array(
				'label'       => __( 'Tabs', 'titan-labs' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{{ label }}}',
				'default'     => array(
					array( 'label' => __( 'Vials', 'titan-labs' ), 'category' => 'peptide-vials' ),
					array( 'label' => __( 'Pens', 'titan-labs' ), 'category' => 'peptide-pens' ),
					array( 'label' => __( 'Sprays', 'titan-labs' ), 'category' => 'peptide-sprays' ),
					array( 'label' => __( 'Orals', 'titan-labs' ), 'category' => 'peptide-orals' ),
				),
			)
		);

		$this->end_controls_section();

		/* ---------------------------------------------------------------
		 * Query
		 * ------------------------------------------------------------ */
		$this->start_controls_section(
			'query',
			array( 'label' => __( 'Products', 'titan-labs' ) )
		);

		$this->add_control(
			'limit',
			array(
				'label'   => __( 'Products per tab', 'titan-labs' ),
				'type'    => Controls_Manager::NUMBER,
				'min'     => 2,
				'max'     => 24,
				'default' => 10,
			)
		);

		$this->add_control(
			'orderby',
			array(
				'label'   => __( 'Order by', 'titan-labs' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'popularity',
				'options' => array(
					'popularity' => __( 'Best selling', 'titan-labs' ),
					'rating'     => __( 'Top rated', 'titan-labs' ),
					'date'       => __( 'Newest', 'titan-labs' ),
					'price'      => __( 'Price: low to high', 'titan-labs' ),
					'price-desc' => __( 'Price: high to low', 'titan-labs' ),
					'title'      => __( 'Name (A–Z)', 'titan-labs' ),
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
		$s = $this->get_settings_for_display();

		if ( ! function_exists( 'wc_get_products' ) ) {
			$this->render_placeholder( __( 'WooCommerce is required for this widget.', 'titan-labs' ) );
			return;
		}

		$limit   = (int) ( $s['limit'] ?? 10 );
		$orderby = $s['orderby'] ?? 'popularity';

		$args_order = array( 'orderby' => $orderby, 'order' => 'DESC' );
		if ( 'price' === $orderby ) {
			$args_order = array( 'orderby' => 'price', 'order' => 'ASC' );
		} elseif ( 'price-desc' === $orderby ) {
			$args_order = array( 'orderby' => 'price', 'order' => 'DESC' );
		} elseif ( 'title' === $orderby ) {
			$args_order = array( 'orderby' => 'title', 'order' => 'ASC' );
		}

		// Build the sets, dropping empty tabs.
		$sets = array();
		foreach ( (array) ( $s['tabs_list'] ?? array() ) as $index => $tab ) {
			if ( empty( $tab['category'] ) ) {
				continue;
			}

			$products = wc_get_products( array_merge(
				array(
					'category' => array( $tab['category'] ),
					'limit'    => $limit,
					'status'   => 'publish',
				),
				$args_order
			) );

			if ( $products ) {
				$sets[] = array(
					'key'      => sanitize_title( $tab['category'] . '-' . $index ),
					'label'    => $tab['label'],
					'products' => $products,
				);
			}
		}

		if ( ! $sets ) {
			$this->render_placeholder( __( 'No products found in the selected categories.', 'titan-labs' ) );
			return;
		}

		$uid = 'tl-tabs-' . $this->get_id();

		$this->open_section( $s );
		$this->render_heading( $s );
		?>

		<div data-tabs>
			<div class="tl-tabs" role="tablist" aria-label="<?php esc_attr_e( 'Product formats', 'titan-labs' ); ?>">
				<?php foreach ( $sets as $i => $set ) : ?>
					<button type="button"
						role="tab"
						id="<?php echo esc_attr( "{$uid}-tab-{$set['key']}" ); ?>"
						aria-controls="<?php echo esc_attr( "{$uid}-panel-{$set['key']}" ); ?>"
						aria-selected="<?php echo 0 === $i ? 'true' : 'false'; ?>"
						tabindex="<?php echo 0 === $i ? '0' : '-1'; ?>">
						<?php echo esc_html( $set['label'] ); ?>
					</button>
				<?php endforeach; ?>
			</div>

			<?php foreach ( $sets as $i => $set ) : ?>
				<div class="tl-tabpanel"
					role="tabpanel"
					id="<?php echo esc_attr( "{$uid}-panel-{$set['key']}" ); ?>"
					aria-labelledby="<?php echo esc_attr( "{$uid}-tab-{$set['key']}" ); ?>"
					<?php echo 0 === $i ? '' : 'hidden'; ?>>

					<div class="tl-grid tl-grid--<?php echo esc_attr( $s['columns'] ?? '4' ); ?> tl-grid--scroll-mobile">
						<?php foreach ( $set['products'] as $product ) : ?>
							<?php
							set_query_var( 'titan_product', $product );
							get_template_part( 'template-parts/product-card' );
							?>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<?php
		$this->close_section();
	}
}
