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
