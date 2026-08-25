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
/**
 * Cache-busting version for a theme asset: its mtime, so edits land without a
 * hard refresh. Falls back to TITAN_VERSION if the file is unreadable.
 *
 * @param string $relative Path relative to the theme root, e.g. '/assets/css/app.css'.
 * @return string
 */
function titan_asset_version( $relative ) {
	$path = TITAN_DIR . $relative;
	return file_exists( $path ) ? (string) filemtime( $path ) : TITAN_VERSION;
}

function titan_assets() {
	wp_enqueue_style(
		'titan-fonts',
		'https://fonts.googleapis.com/css2?family=Montserrat:wght@500;600;700;800;900&family=Inter:wght@400;500;600;700&display=swap',
		array(),
		null
	);

	wp_enqueue_style( 'titan-app', TITAN_URI . '/assets/css/app.css', array(), titan_asset_version( '/assets/css/app.css' ) );
	wp_enqueue_style( 'titan-style', get_stylesheet_uri(), array( 'titan-app' ), titan_asset_version( '/style.css' ) );

	wp_enqueue_script( 'titan-app', TITAN_URI . '/assets/js/app.js', array(), titan_asset_version( '/assets/js/app.js' ), true );
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
 * Whether we are rendering inside the Elementor editor canvas or preview.
 *
 * @return bool
 */
function titan_is_elementor_canvas() {
	if ( ! did_action( 'elementor/loaded' ) || ! class_exists( '\Elementor\Plugin' ) ) {
		return false;
	}

	$plugin = \Elementor\Plugin::$instance;

	return ( isset( $plugin->editor ) && $plugin->editor->is_edit_mode() )
		|| ( isset( $plugin->preview ) && $plugin->preview->is_preview_mode() );
}

/**
 * Theme/age-gate boot script — runs before paint to avoid a flash.
 */
function titan_boot_script() {
	// Never gate the Elementor canvas — it would block editing.
	$gate = ( get_theme_mod( 'titan_age_gate', true ) && ! titan_is_elementor_canvas() ) ? 'true' : 'false';
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

// The page hero already prints a breadcrumb; WooCommerce's own hook would
// repeat it directly above the grid.
remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );

// The hero states the catalogue size, so "Showing 1-24 of 71 results" is
// pagination mechanics leaking into the page. The pager already says it.
remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );

/**
 * Turns off the gallery zoom overlay.
 *
 * Zoom absolutely positions a full-resolution copy of the image over the
 * frame at its natural size, which with object-fit: contain spills outside
 * the container and reads as a cropped, broken photo. Lightbox and the
 * thumbnail slider still work.
 */
function titan_gallery_support() {
	remove_theme_support( 'wc-product-gallery-zoom' );
}
add_action( 'after_setup_theme', 'titan_gallery_support', 99 );

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

	// The drawer redraws from the same fragment mechanism, so it stays in sync
	// with the counter no matter what changed the cart.
	ob_start();
	get_template_part( 'template-parts/cart-drawer' );
	$fragments['div[data-cart-content]'] = '<div class="tl-cartdrawer__content" data-cart-content>'
		. ob_get_clean() . '</div>';

	return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'titan_cart_fragment' );

/**
 * Sets a cart item's quantity from the drawer, then returns refreshed
 * fragments. Quantity 0 removes the line.
 */
function titan_ajax_set_qty() {
	check_ajax_referer( 'titan_nonce', 'nonce' );

	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		wp_send_json_error( array( 'message' => __( 'Cart unavailable.', 'titan-labs' ) ), 500 );
	}

	$key = isset( $_POST['key'] ) ? sanitize_text_field( wp_unslash( $_POST['key'] ) ) : '';
	$qty = isset( $_POST['qty'] ) ? (int) $_POST['qty'] : -1;

	if ( '' === $key || $qty < 0 || ! WC()->cart->get_cart_item( $key ) ) {
		wp_send_json_error( array( 'message' => __( 'Invalid cart item.', 'titan-labs' ) ), 400 );
	}

	if ( 0 === $qty ) {
		WC()->cart->remove_cart_item( $key );
	} else {
		WC()->cart->set_quantity( $key, $qty, true );
	}

	WC()->cart->calculate_totals();

	// WooCommerce builds the fragment payload from this filter.
	ob_start();
	$fragments = apply_filters( 'woocommerce_add_to_cart_fragments', array() );
	ob_end_clean();

	wp_send_json_success( array(
		'fragments' => $fragments,
		'count'     => WC()->cart->get_cart_contents_count(),
	) );
}
add_action( 'wp_ajax_titan_set_qty', 'titan_ajax_set_qty' );
add_action( 'wp_ajax_nopriv_titan_set_qty', 'titan_ajax_set_qty' );

/**
 * Drops WooCommerce's "Your cart is currently empty" notice — the themed empty
 * state in woocommerce/cart/cart-empty.php already says it, better.
 */
function titan_remove_empty_cart_notice() {
	remove_action( 'woocommerce_cart_is_empty', 'wc_empty_cart_message', 10 );
}
add_action( 'init', 'titan_remove_empty_cart_notice' );

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
 * Reads a product's lab data, keeping only the fields that were filled in.
 *
 * Not every product has been through the lab yet, so callers must render
 * nothing rather than showing blank rows — an empty lab value reads worse
 * than no lab section at all.
 *
 * @param int $product_id Product ID.
 * @return array<string, string> Populated meta values, unprefixed keys.
 */
function titan_lab_data( $product_id ) {
	$out = array();

	foreach ( array_keys( titan_lab_fields() ) as $key ) {
		$value = trim( (string) get_post_meta( $product_id, $key, true ) );

		/*
		 * Consumables such as bacteriostatic water store "—" to mean "not
		 * applicable". Treat those as absent so no panel claims a result that
		 * was never measured.
		 */
		if ( '' === $value || '' === trim( $value, "—-–  \t" ) ) {
			continue;
		}

		$out[ substr( $key, 7 ) ] = $value;
	}

	return $out;
}

/**
 * Returns the assay rows for a product, pairing each result with the method
 * used. Naming the method is what separates a lab report from a claim.
 *
 * @param array<string, string> $lab Lab data from titan_lab_data().
 * @return array<int, array<string, string>>
 */
function titan_lab_assays( $lab ) {
	$rows = array();

	if ( ! empty( $lab['purity'] ) ) {
		$rows[] = array(
			'test'   => __( 'Purity', 'titan-labs' ),
			'method' => 'RP-HPLC',
			'result' => $lab['purity'],
		);
	}
	if ( ! empty( $lab['heavy_metals'] ) ) {
		$rows[] = array(
			'test'   => __( 'Heavy metals', 'titan-labs' ),
			'method' => 'ICP-MS',
			'result' => $lab['heavy_metals'],
		);
	}
	if ( ! empty( $lab['endotoxins'] ) ) {
		$rows[] = array(
			'test'   => __( 'Endotoxins', 'titan-labs' ),
			'method' => 'LAL',
			'result' => $lab['endotoxins'],
		);
	}
	if ( ! empty( $lab['sterility'] ) ) {
		$rows[] = array(
			'test'   => __( 'Sterility', 'titan-labs' ),
			'method' => 'USP <71>',
			'result' => $lab['sterility'],
		);
	}
	if ( ! empty( $lab['net_content'] ) ) {
		$rows[] = array(
			'test'   => __( 'Net content', 'titan-labs' ),
			'method' => __( 'Gravimetric', 'titan-labs' ),
			'result' => $lab['net_content'],
		);
	}

	return $rows;
}

/**
 * True when a result string reads as a passing assay.
 *
 * @param string $result Result value.
 * @return bool
 */
function titan_lab_is_pass( $result ) {
	return (bool) preg_match( '/^\s*pass\b/i', $result );
}

/**
 * Verification panel on the single product page.
 *
 * Sits above the price: the measured batch data is the reason someone buys
 * here rather than from an anonymous reseller, so it leads.
 */
function titan_single_lab_badges() {
	global $product;
	if ( ! $product ) {
		return;
	}

	$lab = titan_lab_data( $product->get_id() );

	if ( ! $lab ) {
		return;
	}

	$headline = array();
	if ( ! empty( $lab['purity'] ) ) {
		$headline[] = array( __( 'Tested purity', 'titan-labs' ), $lab['purity'] );
	}
	if ( ! empty( $lab['batch'] ) ) {
		$headline[] = array( __( 'Batch', 'titan-labs' ), $lab['batch'] );
	}
	if ( ! empty( $lab['tested_date'] ) ) {
		$headline[] = array( __( 'Tested', 'titan-labs' ), $lab['tested_date'] );
	}

	if ( ! $headline ) {
		return;
	}

	$assays  = titan_lab_assays( $lab );
	$results = wp_list_pluck( $assays, 'result' );
	$all_ok  = $results && count( array_filter( $results, 'titan_lab_is_pass' ) )
		=== count( array_filter( $results, function ( $r ) {
			return preg_match( '/^\s*(pass|fail)\b/i', $r );
		} ) );
	?>
	<div class="tl-verified">
		<div class="tl-verified__head">
			<span class="tl-verified__icon" aria-hidden="true">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"
					stroke-linecap="round" stroke-linejoin="round">
					<path d="M12 3 4 6v6c0 4.4 3.4 8.3 8 9 4.6-.7 8-4.6 8-9V6l-8-3Z"/>
					<path d="m9 12 2 2 4-4"/>
				</svg>
			</span>
			<strong><?php esc_html_e( 'Third-party verified', 'titan-labs' ); ?></strong>
			<?php if ( $all_ok ) : ?>
				<span class="tl-verified__pass">
					<?php esc_html_e( 'All assays pass', 'titan-labs' ); ?>
				</span>
			<?php endif; ?>
		</div>

		<dl class="tl-verified__specs">
			<?php foreach ( $headline as $pair ) : ?>
				<div>
					<dt><?php echo esc_html( $pair[0] ); ?></dt>
					<dd><?php echo esc_html( $pair[1] ); ?></dd>
				</div>
			<?php endforeach; ?>
		</dl>

		<?php if ( ! empty( $lab['coa_url'] ) ) : ?>
			<a class="tl-btn tl-btn--ghost tl-btn--sm tl-verified__coa"
				href="<?php echo esc_url( $lab['coa_url'] ); ?>" target="_blank" rel="noopener">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
					stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
					<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
					<path d="M14 2v6h6M12 18v-6M9 15l3 3 3-3"/>
				</svg>
				<?php esc_html_e( 'Certificate of Analysis', 'titan-labs' ); ?>
			</a>
		<?php endif; ?>
	</div>
	<?php
}
add_action( 'woocommerce_single_product_summary', 'titan_single_lab_badges', 6 );

/**
 * Full assay table below the product summary.
 */
function titan_single_lab_report() {
	global $product;
	if ( ! $product ) {
		return;
	}

	$lab    = titan_lab_data( $product->get_id() );
	$assays = $lab ? titan_lab_assays( $lab ) : array();

	if ( ! $assays ) {
		return;
	}
	?>
	<section class="tl-labreport">
		<div class="tl-sectionhead">
			<div>
				<p class="tl-eyebrow"><?php esc_html_e( 'Batch analysis', 'titan-labs' ); ?></p>
				<h2 class="tl-mb-0"><?php esc_html_e( 'Lab Report', 'titan-labs' ); ?></h2>
			</div>
		</div>

		<div class="tl-labreport__grid">
			<div class="tl-labreport__tablewrap">
				<table class="tl-labreport__table">
					<thead>
						<tr>
							<th scope="col"><?php esc_html_e( 'Test', 'titan-labs' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Method', 'titan-labs' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Result', 'titan-labs' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $assays as $row ) : ?>
							<tr>
								<th scope="row"><?php echo esc_html( $row['test'] ); ?></th>
								<td class="tl-labreport__method"><?php echo esc_html( $row['method'] ); ?></td>
								<td class="tl-labreport__result">
									<?php if ( titan_lab_is_pass( $row['result'] ) ) : ?>
										<span class="tl-labreport__pass">
											<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
												stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"
												aria-hidden="true"><path d="m5 13 4 4L19 7"/></svg>
											<?php echo esc_html( $row['result'] ); ?>
										</span>
									<?php else : ?>
										<span class="tl-mono"><?php echo esc_html( $row['result'] ); ?></span>
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>

			<aside class="tl-labreport__side">
				<?php if ( ! empty( $lab['batch'] ) ) : ?>
					<p class="tl-labreport__batch">
						<span><?php esc_html_e( 'Batch', 'titan-labs' ); ?></span>
						<strong><?php echo esc_html( $lab['batch'] ); ?></strong>
					</p>
				<?php endif; ?>
				<?php if ( ! empty( $lab['tested_date'] ) ) : ?>
					<p class="tl-labreport__batch">
						<span><?php esc_html_e( 'Tested', 'titan-labs' ); ?></span>
						<strong><?php echo esc_html( $lab['tested_date'] ); ?></strong>
					</p>
				<?php endif; ?>

				<?php if ( ! empty( $lab['coa_url'] ) ) : ?>
					<a class="tl-btn tl-btn--primary tl-btn--sm tl-btn--block"
						href="<?php echo esc_url( $lab['coa_url'] ); ?>" target="_blank" rel="noopener">
						<?php esc_html_e( 'Download COA (PDF)', 'titan-labs' ); ?>
					</a>
				<?php endif; ?>

				<?php if ( ! empty( $lab['verify_url'] ) ) : ?>
					<a class="tl-labreport__verify" href="<?php echo esc_url( $lab['verify_url'] ); ?>"
						target="_blank" rel="noopener">
						<?php esc_html_e( 'Verify this batch independently', 'titan-labs' ); ?> &rarr;
					</a>
				<?php endif; ?>

				<p class="tl-labreport__note">
					<?php esc_html_e( 'Every batch is screened by an accredited third-party laboratory before release. Certificates are published for each lot.', 'titan-labs' ); ?>
				</p>
			</aside>
		</div>
	</section>
	<?php
}
add_action( 'woocommerce_after_single_product_summary', 'titan_single_lab_report', 12 );

/**
 * Reassurance rows in the buy column.
 */
function titan_single_trust_rows() {
	$rows = array(
		array(
			__( 'Third-party tested', 'titan-labs' ),
			__( 'HPLC and mass-spec on every batch', 'titan-labs' ),
			'M12 3 4 6v6c0 4.4 3.4 8.3 8 9 4.6-.7 8-4.6 8-9V6l-8-3Z',
		),
		array(
			__( 'Tracked EU delivery', 'titan-labs' ),
			__( 'DHL, dispatched from Europe', 'titan-labs' ),
			'M1 3h15v13H1zM16 8h4l3 3v5h-7z',
		),
		array(
			__( 'Discreet packaging', 'titan-labs' ),
			__( 'Plain outer, no product markings', 'titan-labs' ),
			'M21 8v13H3V8M1 3h22v5H1zM10 12h4',
		),
	);
	?>
	<ul class="tl-buytrust">
		<?php foreach ( $rows as $row ) : ?>
			<li>
				<span class="tl-buytrust__icon" aria-hidden="true">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
						stroke-linecap="round" stroke-linejoin="round">
						<path d="<?php echo esc_attr( $row[2] ); ?>"/>
					</svg>
				</span>
				<span>
					<strong><?php echo esc_html( $row[0] ); ?></strong>
					<em><?php echo esc_html( $row[1] ); ?></em>
				</span>
			</li>
		<?php endforeach; ?>
	</ul>
	<?php
}
// After the meta line (priority 40), so SKU and categories don't land in the
// middle of the reassurance rows.
add_action( 'woocommerce_single_product_summary', 'titan_single_trust_rows', 41 );

/**
 * Research-use-only notice on the single product page.
 */
function titan_single_rou_notice() {
	printf(
		'<p class="tl-rou">%s</p>',
		esc_html__( 'For laboratory research use only. Not for human consumption.', 'titan-labs' )
	);
}
add_action( 'woocommerce_single_product_summary', 'titan_single_rou_notice', 45 );

/**
 * Drops the default product tabs. They introduce a second tab style and
 * advertise "Reviews (0)" on a page where trust is the whole job; the
 * description and lab report are laid out as sections instead.
 */
function titan_remove_product_tabs() {
	remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );
}
add_action( 'init', 'titan_remove_product_tabs' );

/**
 * Splits the product copy into prose, research areas and spec pairs.
 *
 * The catalogue stores structured data inside the sentence — "Research areas:
 * … Format: Lyophilized powder Specs: Purity: >=99% | Form: … | Storage: -20C"
 * — which renders as an unscannable wall. Parsing it here means existing copy
 * restructures itself rather than needing re-entry across 71 products.
 *
 * @param string $text Raw description.
 * @return array{intro:string, areas:array<int,string>, specs:array<int,array<int,string>>}
 */
function titan_parse_description( $text ) {
	$out = array(
		'intro' => '',
		'areas' => array(),
		'specs' => array(),
	);

	$text = trim( wp_strip_all_tags( $text ) );
	if ( '' === $text ) {
		return $out;
	}

	// Slice at the labels, keeping whatever precedes the first one as prose.
	// The colon is inconsistent in the catalogue copy, so treat it as optional.
	$parts = preg_split(
		'/\b(Research areas|Available sizes|Format|Specs|Storage)\s*:?\s+/i',
		$text,
		-1,
		PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY
	);

	$out['intro'] = trim( (string) array_shift( $parts ) );

	for ( $i = 0; $i + 1 < count( $parts ); $i += 2 ) {
		$label = strtolower( trim( $parts[ $i ] ) );
		$value = trim( $parts[ $i + 1 ] );

		if ( 'available sizes' === $label ) {
			$sizes = trim( preg_replace( '/\s*\b(For (research|laboratory)|Not for human)\b.*$/is', '', $value ), " \t.;," );
			if ( '' !== $sizes ) {
				$out['specs'][] = array( __( 'Available sizes', 'titan-labs' ), $sizes );
			}
			continue;
		}

		if ( 'research areas' === $label ) {
			foreach ( preg_split( '/,(?![^(]*\))/', $value ) as $area ) {
				$area = trim( $area, " \t.;" );
				// Trailing fragments such as "and metabolic regulation models".
				$area = preg_replace( '/^and\s+/i', '', $area );
				if ( '' !== $area && strlen( $area ) < 70 ) {
					$out['areas'][] = $area;
				}
			}
			continue;
		}

		// "Purity: >=99% | Form: Lyophilized Powder | Storage: -20C"
		foreach ( explode( '|', $value ) as $pair ) {
			if ( ! str_contains( $pair, ':' ) ) {
				if ( 'specs' !== $label && '' !== trim( $pair ) ) {
					// Same trailing-disclaimer trim as the labelled branch below.
					$bare = preg_replace( '/\s*\b(For (research|laboratory)|Not for human)\b.*$/is', '', $pair );
					$bare = trim( (string) $bare, " \t.;," );
					if ( '' !== $bare ) {
						$out['specs'][] = array( ucfirst( $label ), $bare );
					}
				}
				continue;
			}
			list( $k, $v ) = array_map( 'trim', explode( ':', $pair, 2 ) );

			/*
			 * The last spec runs straight into the site-wide disclaimer, which
			 * is already shown under the buy button. Cut at the first sentence
			 * boundary so a spec value stays a value.
			 */
			$v = preg_replace( '/\s*\b(For (research|laboratory)|Not for human)\b.*$/is', '', $v );
			$v = trim( (string) $v, " \t.;," );

			if ( '' !== $k && '' !== $v ) {
				$out['specs'][] = array( $k, $v );
			}
		}
	}

	$out['areas'] = array_slice( array_unique( $out['areas'] ), 0, 8 );

	/*
	 * "Format: Lyophilized powder" and "Form: Lyophilized Powder" are the same
	 * fact written twice, and the spec-sheet purity floor duplicates the
	 * measured value shown in the verified panel. Keep one of each.
	 */
	$seen  = array();
	$specs = array();
	foreach ( $out['specs'] as $pair ) {
		$key = strtolower( preg_replace( '/^format$/i', 'form', $pair[0] ) );
		$sig = $key . '|' . strtolower( $pair[1] );

		if ( isset( $seen[ $key ] ) || isset( $seen[ $sig ] ) ) {
			continue;
		}

		$seen[ $key ] = true;
		$seen[ $sig ] = true;
		$specs[]      = $pair;
	}
	$out['specs'] = $specs;

	return $out;
}

/**
 * Description as its own section, in place of the removed tabs.
 */
function titan_single_description() {
	global $post;

	if ( ! $post || '' === trim( (string) $post->post_content ) ) {
		return;
	}

	$parsed = titan_parse_description( $post->post_content );

	// Nothing to restructure — show the copy as written.
	if ( ! $parsed['areas'] && ! $parsed['specs'] ) {
		?>
		<section class="tl-pdpsection">
			<div class="tl-sectionhead">
				<div>
					<p class="tl-eyebrow"><?php esc_html_e( 'About this peptide', 'titan-labs' ); ?></p>
					<h2 class="tl-mb-0"><?php the_title(); ?></h2>
				</div>
			</div>
			<div class="tl-prose"><?php the_content(); ?></div>
		</section>
		<?php
		return;
	}
	?>
	<section class="tl-pdpsection">
		<div class="tl-sectionhead">
			<div>
				<p class="tl-eyebrow"><?php esc_html_e( 'About this peptide', 'titan-labs' ); ?></p>
				<h2 class="tl-mb-0"><?php the_title(); ?></h2>
			</div>
		</div>

		<div class="tl-about">
			<div class="tl-about__main">
				<?php if ( $parsed['intro'] ) : ?>
					<p class="tl-lede"><?php echo esc_html( $parsed['intro'] ); ?></p>
				<?php endif; ?>

				<?php if ( $parsed['areas'] ) : ?>
					<h3 class="tl-about__subhead"><?php esc_html_e( 'Research areas', 'titan-labs' ); ?></h3>
					<ul class="tl-about__areas">
						<?php foreach ( $parsed['areas'] as $area ) : ?>
							<li><?php echo esc_html( ucfirst( $area ) ); ?></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>

			<?php if ( $parsed['specs'] ) : ?>
				<aside class="tl-about__specs">
					<h3 class="tl-about__subhead"><?php esc_html_e( 'Specifications', 'titan-labs' ); ?></h3>
					<dl>
						<?php foreach ( $parsed['specs'] as $pair ) : ?>
							<div>
								<dt><?php echo esc_html( $pair[0] ); ?></dt>
								<dd><?php echo esc_html( $pair[1] ); ?></dd>
							</div>
						<?php endforeach; ?>
					</dl>
				</aside>
			<?php endif; ?>
		</div>
	</section>
	<?php
}
add_action( 'woocommerce_after_single_product_summary', 'titan_single_description', 10 );

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
 * Whether the given page was built with Elementor.
 *
 * Used by front-page.php to hand rendering over to Elementor when the
 * homepage has been laid out visually, and fall back to the coded
 * template parts otherwise.
 *
 * @param int $post_id Optional post id. Defaults to the queried object.
 * @return bool
 */
function titan_is_built_with_elementor( $post_id = 0 ) {
	if ( ! did_action( 'elementor/loaded' ) || ! class_exists( '\Elementor\Plugin' ) ) {
		return false;
	}

	$post_id = $post_id ? (int) $post_id : (int) get_queried_object_id();
	if ( ! $post_id ) {
		return false;
	}

	$document = \Elementor\Plugin::$instance->documents->get( $post_id );

	return $document instanceof \Elementor\Core\Base\Document
		&& $document->is_built_with_elementor();
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
require_once TITAN_DIR . '/inc/shop-filters.php';
require_once TITAN_DIR . '/inc/elementor/loader.php';
