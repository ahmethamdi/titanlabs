<?php
/**
 * Navigation fallbacks and helpers.
 *
 * @package TitanLabs
 */

defined( 'ABSPATH' ) || exit;

/**
 * Fallback primary menu shown until a menu is assigned in Appearance → Menus.
 */
function titan_primary_fallback() {
	$shop = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );

	$items = array(
		array( 'label' => __( 'Shop Peptides', 'titan-labs' ), 'url' => $shop ),
		array( 'label' => __( 'Build a Stack', 'titan-labs' ), 'url' => home_url( '/stack/' ) ),
		array( 'label' => __( 'Bestsellers', 'titan-labs' ), 'url' => home_url( '/product-category/most-popular/' ) ),
		array( 'label' => __( 'Lab Results (COA)', 'titan-labs' ), 'url' => home_url( '/lab-results/' ) ),
		array( 'label' => __( 'Partner Program', 'titan-labs' ), 'url' => home_url( '/affiliate-program/' ) ),
		array( 'label' => __( 'Contact', 'titan-labs' ), 'url' => home_url( '/contact-us/' ) ),
	);

	echo '<ul class="tl-nav__list">';
	foreach ( $items as $item ) {
		printf(
			'<li><a href="%s">%s</a></li>',
			esc_url( $item['url'] ),
			esc_html( $item['label'] )
		);
	}
	echo '</ul>';
}

/**
 * Renders the primary navigation.
 */
function titan_primary_nav() {
	wp_nav_menu( array(
		'theme_location' => 'primary',
		'container'      => false,
		'menu_class'     => 'tl-nav__list',
		'depth'          => 2,
		'fallback_cb'    => 'titan_primary_fallback',
		'walker'         => new Titan_Mega_Walker(),
	) );
}

/**
 * Renders one footer menu column, falling back to product categories.
 *
 * @param string $location Menu location slug.
 * @param string $title    Column heading.
 * @param array  $fallback Fallback links as label => url.
 */
function titan_footer_menu( $location, $title, $fallback = array() ) {
	echo '<div>';
	printf( '<h4>%s</h4>', esc_html( $title ) );

	if ( has_nav_menu( $location ) ) {
		wp_nav_menu( array(
			'theme_location' => $location,
			'container'      => false,
			'menu_class'     => '',
			'depth'          => 1,
		) );
	} elseif ( $fallback ) {
		echo '<ul>';
		foreach ( $fallback as $label => $url ) {
			printf( '<li><a href="%s">%s</a></li>', esc_url( $url ), esc_html( $label ) );
		}
		echo '</ul>';
	}

	echo '</div>';
}

/**
 * Mega-menu walker for the primary navigation.
 *
 * A top-level item with children renders its panel full width, splitting the
 * children into titled columns and finishing with a promo card. The grouping
 * is data-driven: a child whose title matches a heading in the map below
 * starts a new column, so editors can restructure the panel from the Menus
 * screen without touching code.
 */
class Titan_Mega_Walker extends Walker_Nav_Menu {

	/** @var bool Whether the current top-level item opens a mega panel. */
	protected $in_mega = false;

	/** @var int Index of the child currently being written. */
	protected $child_index = 0;

	/** @var string Title of the top-level item whose panel is open. */
	protected $mega_parent_title = '';

	/** @var bool Whether the open panel lists shop categories. */
	protected $mega_is_shop = false;

	/**
	 * Column headings, keyed by the child menu item that should start them.
	 * Anything before the first match goes into the opening column.
	 *
	 * @return array<string, string>
	 */
	protected function column_map() {
		return array(
			'Peptide Vials'     => __( 'By format', 'titan-labs' ),
			'Recovery & Repair' => __( 'By research goal', 'titan-labs' ),
		);
	}

	public function start_lvl( &$output, $depth = 0, $args = null ) {
		if ( 0 === $depth && $this->in_mega ) {
			$this->child_index = 0;
			$output .= '<div class="tl-mega"><div class="tl-mega__inner"><ul class="tl-mega__cols">';
			return;
		}
		$output .= '<ul class="sub-menu">';
	}

	public function end_lvl( &$output, $depth = 0, $args = null ) {
		if ( 0 === $depth && $this->in_mega ) {
			/*
			 * Close the last column's own <ul> and <li> before closing the
			 * column list — without this the promo card ended up nested inside
			 * the final column instead of sitting beside the grid.
			 */
			if ( $this->child_index > 0 ) {
				$output .= '</ul></li>';
			}
			$output .= '</ul>';
			$output .= $this->promo_card();
			$output .= '</div></div>';
			return;
		}
		$output .= '</ul>';
	}

	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		$classes = empty( $item->classes ) ? array() : (array) $item->classes;
		$has_children = in_array( 'menu-item-has-children', $classes, true );

		if ( 0 === $depth ) {
			$this->in_mega           = $has_children;
			$this->mega_parent_title = (string) $item->title;
			// Only a panel of shop categories earns the product card; a Help
			// panel showing a peptide for sale would be a non sequitur.
			$this->mega_is_shop = (bool) preg_match( '/shop|peptide|product/i', $item->title );
			$li_class = $has_children ? 'menu-item menu-item-has-children tl-has-mega' : 'menu-item';
			$output  .= '<li class="' . esc_attr( $li_class ) . '">';
			$output  .= sprintf(
				'<a href="%s"%s>%s</a>',
				esc_url( $item->url ),
				$has_children ? ' aria-expanded="false"' : '',
				esc_html( $item->title )
			);
			return;
		}

		if ( $this->in_mega ) {
			// A heading in the map opens a new column; the first child opens the first.
			$map = $this->column_map();
			$heading = $map[ $item->title ] ?? null;

			if ( 0 === $this->child_index ) {
				$output .= '<li class="tl-mega__col">';
				// Fall back to the parent's own label rather than a generic
				// "Shop", which was wrong on panels like Help.
				$output .= '<p class="tl-mega__title">' . esc_html( $heading ?: $this->mega_parent_title ) . '</p>';
				$output .= '<ul>';
			} elseif ( $heading ) {
				$output .= '</ul></li><li class="tl-mega__col">';
				$output .= '<p class="tl-mega__title">' . esc_html( $heading ) . '</p>';
				$output .= '<ul>';
			}

			$this->child_index++;
			$output .= sprintf(
				'<li><a href="%s">%s</a></li>',
				esc_url( $item->url ),
				esc_html( $item->title )
			);
			return;
		}

		$output .= sprintf(
			'<li class="menu-item"><a href="%s">%s</a></li>',
			esc_url( $item->url ),
			esc_html( $item->title )
		);
	}

	public function end_el( &$output, $item, $depth = 0, $args = null ) {
		if ( 0 === $depth ) {
			$output .= '</li>';
			$this->in_mega = false;
		}
	}

	/**
	 * The panel's closing card: a real product, so the menu shows the goods
	 * rather than a decorative block.
	 */
	protected function promo_card() {
		if ( ! $this->mega_is_shop || ! function_exists( 'wc_get_products' ) ) {
			return '';
		}

		$products = wc_get_products( array(
			'limit'      => 1,
			'status'     => 'publish',
			'orderby'    => 'popularity',
			'meta_key'   => '_titan_purity',
			'meta_compare' => 'EXISTS',
		) );

		if ( ! $products ) {
			return '';
		}

		$product = $products[0];
		$img     = $product->get_image_id()
			? wp_get_attachment_image( $product->get_image_id(), 'titan-card', false, array( 'alt' => '', 'loading' => 'lazy' ) )
			: '';
		$purity  = get_post_meta( $product->get_id(), '_titan_purity', true );

		ob_start();
		?>
		<a class="tl-mega__promo" href="<?php echo esc_url( get_permalink( $product->get_id() ) ); ?>">
			<?php if ( $img ) : ?>
				<span class="tl-mega__promo-media"><?php echo $img; // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
			<?php endif; ?>
			<span class="tl-mega__promo-body">
				<?php if ( $purity ) : ?>
					<span class="tl-mega__promo-chip"><?php echo esc_html( $purity ); ?></span>
				<?php endif; ?>
				<strong><?php echo esc_html( $product->get_name() ); ?></strong>
				<span class="tl-mega__promo-cta">
					<?php esc_html_e( 'View peptide', 'titan-labs' ); ?> <span aria-hidden="true">&rarr;</span>
				</span>
			</span>
		</a>
		<?php
		return (string) ob_get_clean();
	}
}
