<?php
/**
 * Category grid widget.
 *
 * @package TitanLabs
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;

/**
 * A grid of product categories, each listing the peptides it contains.
 */
class Titan_Categories_Widget extends Titan_Widget_Base {

	public function get_name() {
		return 'titan-categories';
	}

	public function get_title() {
		return __( 'Category Grid', 'titan-labs' );
	}

	public function get_icon() {
		return 'eicon-gallery-grid';
	}

	protected function register_controls() {

		$this->start_controls_section(
			'content',
			array( 'label' => __( 'Content', 'titan-labs' ) )
		);

		$this->add_heading_controls(
			__( 'Research peptide categories', 'titan-labs' ),
			__( 'Shop Peptides by Category', 'titan-labs' ),
			__( 'Browse every research peptide family — each curated for a clear research target, from recovery and metabolic studies to cognition. Lab-tested, COA-backed and shipped across Europe.', 'titan-labs' )
		);

		$this->add_control(
			'link_label',
			array(
				'label'   => __( 'Action link text', 'titan-labs' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'View all peptides', 'titan-labs' ),
			)
		);

		$this->add_control(
			'link_url',
			array(
				'label'   => __( 'Action link URL', 'titan-labs' ),
				'type'    => Controls_Manager::URL,
				'default' => array( 'url' => function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : '' ),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'source',
			array( 'label' => __( 'Categories', 'titan-labs' ) )
		);

		$this->add_control(
			'categories',
			array(
				'label'       => __( 'Show these categories', 'titan-labs' ),
				'type'        => Controls_Manager::SELECT2,
				'multiple'    => true,
				'label_block' => true,
				'options'     => $this->product_category_options(),
				'default'     => array(
					'recovery-repair',
					'metabolic-weight',
					'longevity-immune',
					'skin-beauty',
					'cognitive-neuro',
					'growth-body',
				),
				'description' => __( 'Drag to reorder. Leave empty to show all categories.', 'titan-labs' ),
			)
		);

		$this->add_control(
			'products_per_card',
			array(
				'label'   => __( 'Peptides listed per category', 'titan-labs' ),
				'type'    => Controls_Manager::NUMBER,
				'min'     => 0,
				'max'     => 30,
				'default' => 8,
			)
		);

		$this->add_control(
			'columns',
			array(
				'label'   => __( 'Columns', 'titan-labs' ),
				'type'    => Controls_Manager::SELECT,
				'default' => '3',
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

		$slugs = array_filter( (array) ( $s['categories'] ?? array() ) );

		$args = array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => false,
		);
		if ( $slugs ) {
			$args['slug'] = $slugs;
		}

		$terms = get_terms( $args );

		if ( is_wp_error( $terms ) || ! $terms ) {
			$this->render_placeholder( __( 'No product categories found.', 'titan-labs' ) );
			return;
		}

		// Honour the order chosen in the panel.
		if ( $slugs ) {
			usort(
				$terms,
				static function ( $a, $b ) use ( $slugs ) {
					return array_search( $a->slug, $slugs, true ) <=> array_search( $b->slug, $slugs, true );
				}
			);
		}

		$per_card = (int) ( $s['products_per_card'] ?? 8 );

		$this->open_section( $s );

		$this->render_heading(
			$s,
			$s['link_url']['url'] ?? '',
			$s['link_label'] ?? ''
		);

		printf( '<div class="tl-grid tl-grid--%s tl-grid--scroll-mobile">', esc_attr( $s['columns'] ?? '3' ) );

		foreach ( $terms as $term ) {
			$products = $per_card > 0
				? wc_get_products( array(
					'category' => array( $term->slug ),
					'limit'    => $per_card,
					'status'   => 'publish',
					'orderby'  => 'title',
					'order'    => 'ASC',
				) )
				: array();
			?>
			<div class="tl-cat-card">
				<div class="tl-cat-card__head">
					<h3>
						<a href="<?php echo esc_url( get_term_link( $term ) ); ?>">
							<?php echo esc_html( $term->name ); ?>
						</a>
					</h3>
					<span class="tl-cat-card__count">
						<?php
						printf(
							/* translators: %d: product count */
							esc_html( _n( '%d peptide', '%d peptides', (int) $term->count, 'titan-labs' ) ),
							(int) $term->count
						);
						?>
					</span>
				</div>

				<?php if ( $products ) : ?>
					<?php
					/*
					 * The peptide names run as a single marquee line rather than
					 * wrapping: a card lists a sample of a category, so the row
					 * should read as "and more like this" instead of squaring off
					 * into a block. The list is printed twice so the track can
					 * loop seamlessly — the copy is hidden from assistive tech.
					 */
					?>
					<div class="tl-cat-card__marquee">
						<ul class="tl-cat-card__list">
							<?php foreach ( $products as $product ) : ?>
								<li>
									<a href="<?php echo esc_url( $product->get_permalink() ); ?>">
										<?php echo esc_html( $product->get_name() ); ?>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
						<ul class="tl-cat-card__list" aria-hidden="true">
							<?php foreach ( $products as $product ) : ?>
								<li>
									<a href="<?php echo esc_url( $product->get_permalink() ); ?>" tabindex="-1">
										<?php echo esc_html( $product->get_name() ); ?>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endif; ?>
			</div>
			<?php
		}

		echo '</div>';

		$this->close_section();
	}
}
