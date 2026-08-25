<?php
/**
 * Shared base for every Titan Labs Elementor widget.
 *
 * @package TitanLabs
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

/**
 * Base class: category registration, shared control groups and helpers
 * for rendering the theme's existing template parts.
 */
abstract class Titan_Widget_Base extends Widget_Base {

	/**
	 * Widget category.
	 *
	 * @return array
	 */
	public function get_categories() {
		return array( 'titan-labs' );
	}

	/**
	 * Keywords for the panel search.
	 *
	 * @return array
	 */
	public function get_keywords() {
		return array( 'titan', 'peptide', 'shop', 'lab' );
	}

	/**
	 * The theme stylesheet already carries all widget styling.
	 *
	 * @return array
	 */
	public function get_style_depends() {
		return array( 'titan-app' );
	}

	/**
	 * The theme script drives tabs, the COA filter and the drawer.
	 *
	 * @return array
	 */
	public function get_script_depends() {
		return array( 'titan-app' );
	}

	/**
	 * Returns product categories as an id => name map for select controls.
	 *
	 * @return array<string, string>
	 */
	protected function product_category_options() {
		$terms = get_terms( array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => false,
		) );

		$options = array();
		if ( ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				if ( 'uncategorized' === $term->slug ) {
					continue;
				}
				$options[ $term->slug ] = $term->name;
			}
		}

		return $options;
	}

	/**
	 * Returns published products as a slug => name map.
	 *
	 * @return array<string, string>
	 */
	protected function product_options() {
		$products = wc_get_products( array(
			'limit'   => -1,
			'status'  => 'publish',
			'orderby' => 'title',
			'order'   => 'ASC',
		) );

		$options = array();
		foreach ( $products as $product ) {
			$options[ $product->get_slug() ] = $product->get_name();
		}

		return $options;
	}

	/**
	 * Adds the standard "Section heading" control group.
	 *
	 * @param string $eyebrow Default eyebrow text.
	 * @param string $title   Default title text.
	 * @param string $lede    Default lede text.
	 */
	protected function add_heading_controls( $eyebrow = '', $title = '', $lede = '' ) {
		$this->add_control(
			'eyebrow',
			array(
				'label'   => __( 'Eyebrow', 'titan-labs' ),
				'type'    => Controls_Manager::TEXT,
				'default' => $eyebrow,
				'dynamic' => array( 'active' => true ),
			)
		);

		$this->add_control(
			'title',
			array(
				'label'   => __( 'Heading', 'titan-labs' ),
				'type'    => Controls_Manager::TEXT,
				'default' => $title,
				'dynamic' => array( 'active' => true ),
			)
		);

		$this->add_control(
			'lede',
			array(
				'label'   => __( 'Intro text', 'titan-labs' ),
				'type'    => Controls_Manager::TEXTAREA,
				'rows'    => 3,
				'default' => $lede,
				'dynamic' => array( 'active' => true ),
			)
		);
	}

	/**
	 * Adds a background-style control shared by full-width sections.
	 */
	protected function add_background_control() {
		$this->add_control(
			'bg_style',
			array(
				'label'   => __( 'Background', 'titan-labs' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'canvas',
				'options' => array(
					'canvas' => __( 'Page background', 'titan-labs' ),
					'panel'  => __( 'Tinted panel', 'titan-labs' ),
				),
			)
		);

		$this->add_control(
			'head_align',
			array(
				'label'       => __( 'Heading alignment', 'titan-labs' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'start',
				'options'     => array(
					'start'  => __( 'Left', 'titan-labs' ),
					'center' => __( 'Centred', 'titan-labs' ),
				),
				'description' => __( 'Centre a heading to break up a run of left-aligned sections.', 'titan-labs' ),
			)
		);
	}

	/**
	 * Renders a standard section header block.
	 *
	 * @param array  $settings   Widget settings.
	 * @param string $link_url   Optional action link URL.
	 * @param string $link_label Optional action link label.
	 */
	protected function render_heading( $settings, $link_url = '', $link_label = '' ) {
		$eyebrow = $settings['eyebrow'] ?? '';
		$title   = $settings['title'] ?? '';
		$lede    = $settings['lede'] ?? '';

		if ( ! $eyebrow && ! $title && ! $lede ) {
			return;
		}

		$head_classes = array( 'tl-sectionhead' );
		if ( 'center' === ( $settings['head_align'] ?? 'start' ) ) {
			$head_classes[] = 'tl-sectionhead--center';
		}

		printf( '<div class="%s"><div>', esc_attr( implode( ' ', $head_classes ) ) );

		if ( $eyebrow ) {
			printf( '<p class="tl-eyebrow">%s</p>', esc_html( $eyebrow ) );
		}
		if ( $title ) {
			printf( '<h2%s>%s</h2>', $lede ? '' : ' class="tl-mb-0"', esc_html( $title ) );
		}
		if ( $lede ) {
			printf( '<p class="tl-lede tl-mb-0">%s</p>', esc_html( $lede ) );
		}

		echo '</div>';

		if ( $link_url && $link_label ) {
			printf(
				'<a class="tl-btn tl-btn--ghost tl-btn--sm" href="%s">%s</a>',
				esc_url( $link_url ),
				esc_html( $link_label )
			);
		}

		echo '</div>';
	}

	/**
	 * Opens a section wrapper.
	 *
	 * Elementor already provides its own outer container, so inside the
	 * editor we skip the theme's vertical padding to avoid doubling it.
	 *
	 * @param array  $settings Widget settings.
	 * @param string $extra    Extra classes.
	 * @param string $id       Optional element id.
	 */
	protected function open_section( $settings, $extra = '', $id = '' ) {
		$classes = array( 'tl-section' );

		if ( 'panel' === ( $settings['bg_style'] ?? 'canvas' ) ) {
			$classes[] = 'tl-section--panel';
		}
		if ( $extra ) {
			$classes[] = $extra;
		}

		printf(
			'<section class="%s"%s>',
			esc_attr( implode( ' ', $classes ) ),
			$id ? ' id="' . esc_attr( $id ) . '"' : ''
		);

		echo '<div class="tl-container">';
	}

	/**
	 * Closes a section wrapper.
	 */
	protected function close_section() {
		echo '</div></section>';
	}

	/**
	 * Renders an editor-only placeholder when a widget has nothing to show.
	 *
	 * @param string $message Message to display.
	 */
	protected function render_placeholder( $message ) {
		if ( ! \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
			return;
		}

		printf(
			'<div style="padding:2rem;text-align:center;border:1px dashed var(--color-line);border-radius:12px;color:var(--color-muted);font-family:var(--font-sans)">%s</div>',
			esc_html( $message )
		);
	}
}
