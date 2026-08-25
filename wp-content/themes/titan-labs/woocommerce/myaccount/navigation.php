<?php
/**
 * My Account navigation.
 *
 * Overrides WooCommerce's bare bulleted list with a sidebar that matches the
 * rest of the theme. On phones it becomes a horizontal scroller so the menu
 * does not push the actual content off the first screen.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package TitanLabs
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_account_navigation' );

$current_user = wp_get_current_user();

// One glyph per endpoint; anything unknown falls back to a neutral dot.
$icons = array(
	'dashboard'       => 'M3 12h7V3H3zM14 21h7v-9h-7zM14 3v6h7V3zM3 21h7v-6H3z',
	'orders'          => 'M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4zM3 6h18M16 10a4 4 0 0 1-8 0',
	'downloads'       => 'M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3',
	'edit-address'    => 'M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z M12 8a3 3 0 1 0 0 6 3 3 0 0 0 0-6z',
	'payment-methods' => 'M1 4h22v16H1zM1 10h22',
	'edit-account'    => 'M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2 M12 3a4 4 0 1 0 0 8 4 4 0 0 0 0-8z',
	'customer-logout' => 'M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9',
);
?>

<nav class="tl-account-nav" aria-label="<?php esc_attr_e( 'Account pages', 'titan-labs' ); ?>">

	<div class="tl-account-nav__user">
		<span class="tl-account-nav__avatar" aria-hidden="true">
			<?php echo esc_html( strtoupper( substr( $current_user->display_name, 0, 1 ) ) ); ?>
		</span>
		<span class="tl-account-nav__who">
			<strong><?php echo esc_html( $current_user->display_name ); ?></strong>
			<em><?php echo esc_html( $current_user->user_email ); ?></em>
		</span>
	</div>

	<ul class="tl-account-nav__list">
		<?php foreach ( wc_get_account_menu_items() as $endpoint => $label ) : ?>
			<li class="<?php echo esc_attr( wc_get_account_menu_item_classes( $endpoint ) ); ?>">
				<a href="<?php echo esc_url( wc_get_account_endpoint_url( $endpoint ) ); ?>"
					<?php echo wc_is_current_account_menu_item( $endpoint ) ? 'aria-current="page"' : ''; ?>>
					<span class="tl-account-nav__icon" aria-hidden="true">
						<?php if ( isset( $icons[ $endpoint ] ) ) : ?>
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
								stroke-linecap="round" stroke-linejoin="round">
								<path d="<?php echo esc_attr( $icons[ $endpoint ] ); ?>"/>
							</svg>
						<?php else : ?>
							<svg viewBox="0 0 24 24" fill="currentColor" stroke="none">
								<circle cx="12" cy="12" r="3"/>
							</svg>
						<?php endif; ?>
					</span>
					<?php echo esc_html( $label ); ?>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</nav>

<?php do_action( 'woocommerce_after_account_navigation' ); ?>
