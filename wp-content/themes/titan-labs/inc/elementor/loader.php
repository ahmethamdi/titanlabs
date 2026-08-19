<?php
/**
 * Elementor integration.
 *
 * Registers a "Titan Labs" widget category and loads every widget that
 * wraps one of the theme's homepage sections, so the same markup can be
 * dragged, reordered and edited from the Elementor canvas.
 *
 * @package TitanLabs
 */

defined( 'ABSPATH' ) || exit;

/**
 * Minimum Elementor version this integration supports.
 */
const TITAN_ELEMENTOR_MIN = '3.5.0';

/**
 * Whether Elementor is active and new enough.
 *
 * @return bool
 */
function titan_elementor_ready() {
	return did_action( 'elementor/loaded' )
		&& defined( 'ELEMENTOR_VERSION' )
		&& version_compare( ELEMENTOR_VERSION, TITAN_ELEMENTOR_MIN, '>=' );
}

/**
 * Adds the Titan Labs widget category to the panel.
 *
 * @param \Elementor\Elements_Manager $elements_manager Elementor elements manager.
 */
function titan_elementor_category( $elements_manager ) {
	$elements_manager->add_category(
		'titan-labs',
		array(
			'title' => __( 'Titan Labs', 'titan-labs' ),
			'icon'  => 'eicon-flask',
		)
	);
}
add_action( 'elementor/elements/categories_registered', 'titan_elementor_category' );

/**
 * Loads and registers every Titan Labs widget.
 *
 * @param \Elementor\Widgets_Manager $widgets_manager Elementor widgets manager.
 */
function titan_elementor_widgets( $widgets_manager ) {
	if ( ! titan_elementor_ready() ) {
		return;
	}

	require_once __DIR__ . '/class-titan-widget-base.php';

	$widgets = array(
		'hero',
		'categories',
		'bestsellers',
		'spotlight',
		'stack',
		'reviews',
		'quality',
		'coa',
	);

	foreach ( $widgets as $widget ) {
		$file = __DIR__ . '/widgets/class-titan-' . $widget . '-widget.php';
		if ( ! file_exists( $file ) ) {
			continue;
		}

		require_once $file;

		$class = 'Titan_' . str_replace( ' ', '_', ucwords( str_replace( '-', ' ', $widget ) ) ) . '_Widget';

		if ( class_exists( $class ) ) {
			$widgets_manager->register( new $class() );
		}
	}
}
add_action( 'elementor/widgets/register', 'titan_elementor_widgets' );

/**
 * Loads the theme stylesheet inside the Elementor editor and preview so
 * widgets look identical to the front end while editing.
 */
function titan_elementor_editor_styles() {
	wp_enqueue_style(
		'titan-fonts',
		'https://fonts.googleapis.com/css2?family=Montserrat:wght@500;600;700;800;900&family=Inter:wght@400;500;600;700&display=swap',
		array(),
		null
	);
	wp_enqueue_style( 'titan-app', TITAN_URI . '/assets/css/app.css', array(), TITAN_VERSION );
}
add_action( 'elementor/editor/after_enqueue_styles', 'titan_elementor_editor_styles' );
add_action( 'elementor/preview/enqueue_styles', 'titan_elementor_editor_styles' );

/**
 * Makes the theme's JS available in the editor preview so tabs and the
 * COA filter stay interactive while editing.
 */
function titan_elementor_preview_scripts() {
	wp_enqueue_script( 'titan-app', TITAN_URI . '/assets/js/app.js', array(), TITAN_VERSION, true );
}
add_action( 'elementor/preview/enqueue_scripts', 'titan_elementor_preview_scripts' );

/**
 * Re-runs the theme's widget scripts after Elementor renders a widget in
 * the editor, so newly dropped widgets are interactive immediately.
 */
function titan_elementor_frontend_init() {
	if ( ! is_admin() ) {
		return;
	}
	?>
	<script>
	window.addEventListener('elementor/frontend/init', function () {
		if (!window.elementorFrontend) { return; }
		elementorFrontend.hooks.addAction('frontend/element_ready/global', function () {
			document.dispatchEvent(new CustomEvent('titan:rebind'));
		});
	});
	</script>
	<?php
}
add_action( 'elementor/editor/footer', 'titan_elementor_frontend_init' );

/**
 * Admin notice when Elementor is missing or outdated.
 */
function titan_elementor_notice() {
	if ( titan_elementor_ready() || ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	// Only nag on plugin/theme screens.
	$screen = get_current_screen();
	if ( ! $screen || ! in_array( $screen->id, array( 'plugins', 'themes', 'dashboard' ), true ) ) {
		return;
	}

	printf(
		'<div class="notice notice-warning"><p>%s</p></div>',
		esc_html__( 'Titan Labs: install and activate Elementor 3.5 or newer to edit homepage sections visually. The site works without it — sections fall back to the coded template.', 'titan-labs' )
	);
}
add_action( 'admin_notices', 'titan_elementor_notice' );
