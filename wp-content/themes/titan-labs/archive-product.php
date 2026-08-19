<?php
/**
 * Product archive entry point.
 *
 * WordPress resolves `archive-product.php` in the theme root before it ever
 * reaches WooCommerce's own loader, and would otherwise fall through to this
 * theme's generic `archive.php` — which renders the blog loop and no products.
 *
 * Delegating to the override in `woocommerce/` keeps the markup in one place.
 *
 * @package TitanLabs
 */

defined( 'ABSPATH' ) || exit;

require __DIR__ . '/woocommerce/archive-product.php';
