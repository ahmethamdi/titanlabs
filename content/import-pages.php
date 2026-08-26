<?php
/**
 * Imports the designed content pages, replacing the placeholder text.
 *
 * Each page is stored as raw HTML in a single Gutenberg custom-HTML block,
 * so it renders exactly as authored and stays editable in the block editor.
 *
 * Run with: wp eval-file import-pages.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Run through WP-CLI.' );
}

$dir = __DIR__ . '/pages';

/** file => [page slug, SEO title]. */
$map = array(
	'about'     => array( 'about-us', 'About Titan Labs — Quality Peptides for Research' ),
	'faq'       => array( 'faq', 'Frequently Asked Questions' ),
	'contact'   => array( 'contact-us', 'Contact Titan Labs' ),
	'shipping'  => array( 'shipping-policy', 'Shipping & Delivery' ),
	'refund'    => array( 'refund-policy', 'Returns & Refunds' ),
	'rou'       => array( 'research-use-only-policy', 'Research Use Only Policy' ),
	'privacy'   => array( 'privacy-policy', 'Privacy Policy' ),
	'terms'     => array( 'terms-of-service', 'Terms & Conditions' ),
	'legal'     => array( 'legal-notice', 'Legal Notice' ),
	'wholesale' => array( 'wholesale', 'Wholesale & Institutional Supply' ),
	'affiliate' => array( 'affiliate-program', 'Partner Programme' ),
	'certificates' => array( 'certificates', 'Certificates & Quality Documentation' ),
);

$done    = 0;
$missing = array();

foreach ( $map as $file => $meta ) {
	list( $slug, $title ) = $meta;

	$path = "{$dir}/{$file}.html";
	if ( ! file_exists( $path ) ) {
		$missing[] = $file;
		continue;
	}

	$html = trim( (string) file_get_contents( $path ) );
	if ( ! $html ) {
		$missing[] = $file;
		continue;
	}

	$page = get_page_by_path( $slug, OBJECT, 'page' );
	if ( ! $page ) {
		$missing[] = "{$slug} (page not found)";
		continue;
	}

	// Wrap in a custom-HTML block so the block editor keeps it intact.
	$content = "<!-- wp:html -->\n{$html}\n<!-- /wp:html -->";

	$result = wp_update_post(
		array(
			'ID'           => $page->ID,
			'post_content' => $content,
			'post_title'   => $title,
		),
		true
	);

	if ( is_wp_error( $result ) ) {
		WP_CLI::warning( "{$slug}: " . $result->get_error_message() );
		continue;
	}

	// These pages bring their own hero and full-bleed sections.
	update_post_meta( $page->ID, '_wp_page_template', 'page-full-width.php' );

	$done++;
	WP_CLI::log( sprintf( '  [%d] %-26s %6d bytes', $done, $slug, strlen( $html ) ) );
}

WP_CLI::success( sprintf(
	'Updated %d pages.%s',
	$done,
	$missing ? ' Skipped: ' . implode( ', ', $missing ) : ''
) );
