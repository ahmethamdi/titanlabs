<?php
/**
 * Titan Labs theme bootstrap.
 *
 * @package TitanLabs
 */

defined( 'ABSPATH' ) || exit;

define( 'TITAN_VERSION', '1.0.0' );
define( 'TITAN_DIR', get_template_directory() );
define( 'TITAN_URI', get_template_directory_uri() );

/**
 * Theme supports, menus and image sizes.
 */
function titan_setup() {
	load_theme_textdomain( 'titan-labs', TITAN_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'customize-selective-refresh-widgets' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'custom-logo', array(
		'height'      => 80,
		'width'       => 260,
		'flex-height' => true,
		'flex-width'  => true,
	) );
	add_theme_support( 'html5', array(
		'search-form', 'comment-form', 'comment-list',
		'gallery', 'caption', 'style', 'script',
	) );

	// WooCommerce.
	add_theme_support( 'woocommerce', array(
		'thumbnail_image_width' => 600,
		'single_image_width'    => 1000,
		'product_grid'          => array(
			'default_rows'    => 3,
			'min_rows'        => 1,
			'default_columns' => 4,
			'min_columns'     => 2,
			'max_columns'     => 5,
		),
	) );
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );

	register_nav_menus( array(
		'primary'      => __( 'Primary Menu', 'titan-labs' ),
		'footer-shop'  => __( 'Footer — Shop', 'titan-labs' ),
		'footer-goal'  => __( 'Footer — Shop by Goal', 'titan-labs' ),
		'footer-help'  => __( 'Footer — Help', 'titan-labs' ),
		'footer-legal' => __( 'Footer — Company', 'titan-labs' ),
	) );

	add_image_size( 'titan-card', 600, 600, true );
	add_image_size( 'titan-wide', 1200, 675, true );
}
add_action( 'after_setup_theme', 'titan_setup' );

/**
 * Content width.
 */
function titan_content_width() {
	$GLOBALS['content_width'] = 1240;
}
add_action( 'after_setup_theme', 'titan_content_width', 0 );

/**
 * Front-end assets.
 */
function titan_assets() {
	wp_enqueue_style(
		'titan-fonts',
		'https://fonts.googleapis.com/css2?family=Montserrat:wght@500;600;700;800;900&family=Inter:wght@400;500;600;700&display=swap',
		array(),
		null
	);

	wp_enqueue_style( 'titan-app', TITAN_URI . '/assets/css/app.css', array(), TITAN_VERSION );
	wp_enqueue_style( 'titan-style', get_stylesheet_uri(), array( 'titan-app' ), TITAN_VERSION );

	wp_enqueue_script( 'titan-app', TITAN_URI . '/assets/js/app.js', array(), TITAN_VERSION, true );
	wp_localize_script( 'titan-app', 'titanData', array(
		'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
		'nonce'    => wp_create_nonce( 'titan_nonce' ),
		'ageGate'  => (bool) get_theme_mod( 'titan_age_gate', true ),
		'minAge'   => (int) get_theme_mod( 'titan_min_age', 21 ),
	) );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'titan_assets' );

/**
 * Preconnect to Google Fonts.
 */
function titan_resource_hints( $hints, $relation ) {
	if ( 'preconnect' === $relation ) {
		$hints[] = array( 'href' => 'https://fonts.gstatic.com', 'crossorigin' );
	}
	return $hints;
}
add_filter( 'wp_resource_hints', 'titan_resource_hints', 10, 2 );

/**
 * Theme/age-gate boot script — runs before paint to avoid a flash.
 */
function titan_boot_script() {
	$gate = get_theme_mod( 'titan_age_gate', true ) ? 'true' : 'false';
	?>
	<script id="titan-boot">
	(function () {
		try {
			var t = localStorage.getItem('titanTheme');
			if (!t) { t = matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'; }
			if (t === 'dark') { document.documentElement.classList.add('dark'); }
			if (<?php echo $gate; ?> && !localStorage.getItem('titanAgeVerified')) {
				document.documentElement.classList.add('tl-gated');
			}
		} catch (e) {}
	})();
	</script>
	<?php
}
add_action( 'wp_head', 'titan_boot_script', 1 );

/**
 * Widget areas.
 */
function titan_widgets() {
	register_sidebar( array(
		'name'          => __( 'Shop Sidebar', 'titan-labs' ),
		'id'            => 'shop-sidebar',
		'before_widget' => '<div id="%1$s" class="tl-widget %2$s">',
		'after_widget'  => '</div>',
		'before_title'  => '<h4 class="tl-widget__title">',
		'after_title'   => '</h4>',
	) );
}
add_action( 'widgets_init', 'titan_widgets' );

/* -------------------------------------------------------------------------
 * WooCommerce integration
 * ---------------------------------------------------------------------- */

// Wrapper markup.
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );

function titan_wc_wrapper_start() {
	echo '<main id="main" class="tl-container tl-section">';
}
add_action( 'woocommerce_before_main_content', 'titan_wc_wrapper_start', 10 );

function titan_wc_wrapper_end() {
	echo '</main>';
}
add_action( 'woocommerce_after_main_content', 'titan_wc_wrapper_end', 10 );

// Sidebar off by default — the design uses full-width grids.
remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );

/**
 * Products per row / per page.
 */
function titan_loop_columns() {
	return 4;
}
add_filter( 'loop_shop_columns', 'titan_loop_columns' );

function titan_products_per_page() {
	return 24;
}
add_filter( 'loop_shop_per_page', 'titan_products_per_page', 20 );

/**
 * Cart fragment for the header counter.
 */
function titan_cart_fragment( $fragments ) {
	ob_start();
	titan_cart_count_markup();
	$fragments['span.tl-cart__count'] = ob_get_clean();
	return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'titan_cart_fragment' );

/**
 * Renders the cart counter badge.
 */
function titan_cart_count_markup() {
	$count = function_exists( 'WC' ) && WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
	printf(
		'<span class="tl-cart__count"%s>%s</span>',
		$count ? '' : ' hidden',
		esc_html( $count )
	);
}

/* -------------------------------------------------------------------------
 * Stack builder — automatic volume discount
 * 4+ items = 5% off, 10+ items = 10% off.
 * ---------------------------------------------------------------------- */

/**
 * Returns the discount rate for a given item count.
 *
 * @param int $count Number of items in the cart.
 * @return float Discount rate, e.g. 0.05.
 */
function titan_stack_rate( $count ) {
	if ( $count >= (int) get_theme_mod( 'titan_stack_tier2_qty', 10 ) ) {
		return (float) get_theme_mod( 'titan_stack_tier2_pct', 10 ) / 100;
	}
	if ( $count >= (int) get_theme_mod( 'titan_stack_tier1_qty', 4 ) ) {
		return (float) get_theme_mod( 'titan_stack_tier1_pct', 5 ) / 100;
	}
	return 0.0;
}

/**
 * Applies the stack discount as a negative fee.
 *
 * @param WC_Cart $cart Cart object.
 */
function titan_apply_stack_discount( $cart ) {
	if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
		return;
	}
	if ( ! get_theme_mod( 'titan_stack_enabled', true ) ) {
		return;
	}

	$count = $cart->get_cart_contents_count();
	$rate  = titan_stack_rate( $count );

	if ( $rate <= 0 ) {
		return;
	}

	$subtotal = 0.0;
	foreach ( $cart->get_cart() as $item ) {
		$subtotal += (float) $item['line_subtotal'];
	}

	if ( $subtotal <= 0 ) {
		return;
	}

	$cart->add_fee(
		sprintf(
			/* translators: %s: discount percentage */
			__( 'Stack discount (−%s%%)', 'titan-labs' ),
			round( $rate * 100 )
		),
		-1 * round( $subtotal * $rate, 2 ),
		false
	);
}
add_action( 'woocommerce_cart_calculate_fees', 'titan_apply_stack_discount' );

/**
 * Notice nudging the customer toward the next discount tier.
 */
function titan_stack_notice() {
	if ( ! get_theme_mod( 'titan_stack_enabled', true ) || ! function_exists( 'WC' ) || ! WC()->cart ) {
		return;
	}

	$count = WC()->cart->get_cart_contents_count();
	$t1    = (int) get_theme_mod( 'titan_stack_tier1_qty', 4 );
	$t2    = (int) get_theme_mod( 'titan_stack_tier2_qty', 10 );

	if ( $count > 0 && $count < $t1 ) {
		wc_print_notice(
			sprintf(
				/* translators: 1: items remaining, 2: discount percentage */
				esc_html__( 'Add %1$d more item(s) to unlock −%2$d%% on your stack.', 'titan-labs' ),
				$t1 - $count,
				(int) get_theme_mod( 'titan_stack_tier1_pct', 5 )
			),
			'notice'
		);
	} elseif ( $count >= $t1 && $count < $t2 ) {
		wc_print_notice(
			sprintf(
				/* translators: 1: items remaining, 2: discount percentage */
				esc_html__( 'Add %1$d more item(s) to unlock −%2$d%% on your stack.', 'titan-labs' ),
				$t2 - $count,
				(int) get_theme_mod( 'titan_stack_tier2_pct', 10 )
			),
			'notice'
		);
	}
}
add_action( 'woocommerce_before_cart', 'titan_stack_notice' );

/* -------------------------------------------------------------------------
 * Product meta — size, batch, purity, COA
 * ---------------------------------------------------------------------- */

/**
 * Registers the COA / lab-result meta box.
 */
function titan_product_metabox() {
	add_meta_box(
		'titan_lab_meta',
		__( 'Titan Labs — Lab Data', 'titan-labs' ),
		'titan_product_metabox_render',
		'product',
		'normal',
		'default'
	);
}
add_action( 'add_meta_boxes', 'titan_product_metabox' );

/**
 * Fields shown in the lab-data meta box.
 *
 * @return array<string, string>
 */
function titan_lab_fields() {
	return array(
		'_titan_size'         => __( 'Size (e.g. 10 mg)', 'titan-labs' ),
		'_titan_batch'        => __( 'Batch #', 'titan-labs' ),
		'_titan_purity'       => __( 'Tested purity (e.g. 99.48%)', 'titan-labs' ),
		'_titan_net_content'  => __( 'Net content (e.g. 10.32 mg)', 'titan-labs' ),
		'_titan_tested_date'  => __( 'Tested date', 'titan-labs' ),
		'_titan_heavy_metals' => __( 'Heavy metals (Pass/Fail)', 'titan-labs' ),
		'_titan_endotoxins'   => __( 'Endotoxins (Pass/Fail)', 'titan-labs' ),
		'_titan_sterility'    => __( 'Sterility (Pass/Fail)', 'titan-labs' ),
		'_titan_coa_url'      => __( 'COA file URL', 'titan-labs' ),
		'_titan_verify_url'   => __( 'Independent verification URL', 'titan-labs' ),
	);
}

/**
 * Renders the lab-data meta box.
 *
 * @param WP_Post $post Current post.
 */
function titan_product_metabox_render( $post ) {
	wp_nonce_field( 'titan_lab_meta', 'titan_lab_meta_nonce' );
	echo '<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:12px">';
	foreach ( titan_lab_fields() as $key => $label ) {
		printf(
			'<p><label for="%1$s"><strong>%2$s</strong></label><br><input type="text" id="%1$s" name="%1$s" value="%3$s" style="width:100%%"></p>',
			esc_attr( $key ),
			esc_html( $label ),
			esc_attr( (string) get_post_meta( $post->ID, $key, true ) )
		);
	}
	echo '</div>';
}

/**
 * Saves the lab-data meta box.
 *
 * @param int $post_id Post ID.
 */
function titan_product_metabox_save( $post_id ) {
	if ( ! isset( $_POST['titan_lab_meta_nonce'] )
		|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['titan_lab_meta_nonce'] ) ), 'titan_lab_meta' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	foreach ( array_keys( titan_lab_fields() ) as $key ) {
		if ( ! isset( $_POST[ $key ] ) ) {
			continue;
		}
		$value = sanitize_text_field( wp_unslash( $_POST[ $key ] ) );
		if ( in_array( $key, array( '_titan_coa_url', '_titan_verify_url' ), true ) ) {
			$value = esc_url_raw( $value );
		}
		if ( '' === $value ) {
			delete_post_meta( $post_id, $key );
		} else {
			update_post_meta( $post_id, $key, $value );
		}
	}
}
add_action( 'save_post_product', 'titan_product_metabox_save' );

/**
 * Shows the size under the product title in the loop.
 */
function titan_loop_size() {
	global $product;
	if ( ! $product ) {
		return;
	}
	$size = get_post_meta( $product->get_id(), '_titan_size', true );
	if ( $size ) {
		printf( '<div class="tl-product-card__size">%s</div>', esc_html( $size ) );
	}
}
add_action( 'woocommerce_after_shop_loop_item_title', 'titan_loop_size', 8 );

/**
 * Purity / COA badges on the single product page.
 */
function titan_single_lab_badges() {
	global $product;
	if ( ! $product ) {
		return;
	}

	$purity = get_post_meta( $product->get_id(), '_titan_purity', true );
	$coa    = get_post_meta( $product->get_id(), '_titan_coa_url', true );

	if ( ! $purity && ! $coa ) {
		return;
	}

	echo '<div class="tl-flex tl-gap tl-items-center" style="flex-wrap:wrap;margin:.75rem 0 1.25rem">';
	if ( $purity ) {
		printf(
			'<span class="tl-badge">%s %s</span>',
			esc_html__( 'Tested purity', 'titan-labs' ),
			esc_html( $purity )
		);
	}
	if ( $coa ) {
		printf(
			'<a class="tl-badge tl-badge--outline" href="%s" target="_blank" rel="noopener">%s</a>',
			esc_url( $coa ),
			esc_html__( 'View Certificate of Analysis', 'titan-labs' )
		);
	}
	echo '</div>';
}
add_action( 'woocommerce_single_product_summary', 'titan_single_lab_badges', 25 );

/**
 * Research-use-only notice on the single product page.
 */
function titan_single_rou_notice() {
	printf(
		'<p class="tl-small tl-muted" style="margin-top:1rem">%s</p>',
		esc_html__( 'For laboratory research use only. Not for human consumption.', 'titan-labs' )
	);
}
add_action( 'woocommerce_single_product_summary', 'titan_single_rou_notice', 45 );

/* -------------------------------------------------------------------------
 * Helpers
 * ---------------------------------------------------------------------- */

/**
 * Returns COA rows for every product carrying a batch number.
 *
 * @param int $limit Maximum rows.
 * @return array<int, array<string, string>>
 */
function titan_get_coa_rows( $limit = 200 ) {
	$query = new WP_Query( array(
		'post_type'      => 'product',
		'post_status'    => 'publish',
		'posts_per_page' => $limit,
		'meta_query'     => array(
			array( 'key' => '_titan_batch', 'compare' => 'EXISTS' ),
		),
		'orderby'        => 'title',
		'order'          => 'ASC',
		'no_found_rows'  => true,
	) );

	$rows = array();
	foreach ( $query->posts as $post ) {
		$batch = get_post_meta( $post->ID, '_titan_batch', true );
		if ( ! $batch ) {
			continue;
		}
		$rows[] = array(
			'name'         => get_the_title( $post ),
			'size'         => (string) get_post_meta( $post->ID, '_titan_size', true ),
			'batch'        => (string) $batch,
			'tested'       => (string) get_post_meta( $post->ID, '_titan_tested_date', true ),
			'purity'       => (string) get_post_meta( $post->ID, '_titan_purity', true ),
			'net'          => (string) get_post_meta( $post->ID, '_titan_net_content', true ),
			'heavy_metals' => (string) get_post_meta( $post->ID, '_titan_heavy_metals', true ),
			'endotoxins'   => (string) get_post_meta( $post->ID, '_titan_endotoxins', true ),
			'sterility'    => (string) get_post_meta( $post->ID, '_titan_sterility', true ),
			'coa'          => (string) get_post_meta( $post->ID, '_titan_coa_url', true ),
			'verify'       => (string) get_post_meta( $post->ID, '_titan_verify_url', true ),
		);
	}
	wp_reset_postdata();

	return $rows;
}

/**
 * Star-rating markup for a product.
 *
 * @param WC_Product $product Product.
 */
function titan_rating_markup( $product ) {
	if ( ! $product instanceof WC_Product ) {
		return;
	}
	$count = $product->get_rating_count();
	if ( ! $count ) {
		return;
	}
	$avg    = (float) $product->get_average_rating();
	$filled = (int) round( $avg );
	printf(
		'<span class="tl-stars"><span class="tl-stars__glyphs" aria-hidden="true">%s</span><span>%s (%d)</span></span>',
		esc_html( str_repeat( '★', $filled ) . str_repeat( '☆', 5 - $filled ) ),
		esc_html( number_format_i18n( $avg, 1 ) ),
		(int) $count
	);
}

/**
 * Excerpt length.
 */
function titan_excerpt_length() {
	return 24;
}
add_filter( 'excerpt_length', 'titan_excerpt_length' );

function titan_excerpt_more() {
	return '…';
}
add_filter( 'excerpt_more', 'titan_excerpt_more' );

/**
 * Body classes.
 *
 * @param array $classes Body classes.
 * @return array
 */
function titan_body_class( $classes ) {
	$classes[] = 'tl-body';
	if ( function_exists( 'is_woocommerce' ) && is_woocommerce() ) {
		$classes[] = 'tl-shop';
	}
	return $classes;
}
add_filter( 'body_class', 'titan_body_class' );

require_once TITAN_DIR . '/inc/customizer.php';
require_once TITAN_DIR . '/inc/nav-walker.php';
