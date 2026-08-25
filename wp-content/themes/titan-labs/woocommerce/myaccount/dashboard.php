<?php
/**
 * My Account dashboard.
 *
 * Replaces WooCommerce's two paragraphs of links with cards and a recent-order
 * summary, so the landing screen of an account says something.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package TitanLabs
 */

defined( 'ABSPATH' ) || exit;

$current_user = wp_get_current_user();

$order_count = 0;
$last_order  = null;

if ( function_exists( 'wc_get_orders' ) ) {
	$orders = wc_get_orders( array(
		'customer' => get_current_user_id(),
		'limit'    => 1,
		'orderby'  => 'date',
		'order'    => 'DESC',
	) );
	$last_order = $orders ? $orders[0] : null;

	$order_count = (int) wc_get_customer_order_count( get_current_user_id() );
}

$cards = array(
	array(
		'url'   => wc_get_account_endpoint_url( 'orders' ),
		'label' => __( 'Orders', 'titan-labs' ),
		'desc'  => __( 'Track and review past orders', 'titan-labs' ),
		'icon'  => 'M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4zM3 6h18M16 10a4 4 0 0 1-8 0',
	),
	array(
		'url'   => wc_get_account_endpoint_url( 'edit-address' ),
		'label' => __( 'Addresses', 'titan-labs' ),
		'desc'  => __( 'Shipping and billing details', 'titan-labs' ),
		'icon'  => 'M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z',
	),
	array(
		'url'   => wc_get_account_endpoint_url( 'edit-account' ),
		'label' => __( 'Account details', 'titan-labs' ),
		'desc'  => __( 'Name, email and password', 'titan-labs' ),
		'icon'  => 'M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2 M12 3a4 4 0 1 0 0 8 4 4 0 0 0 0-8z',
	),
);
?>

<div class="tl-dash">

	<div class="tl-dash__head">
		<h2 class="tl-dash__hello">
			<?php
			printf(
				/* translators: %s: customer first name */
				esc_html__( 'Hello, %s', 'titan-labs' ),
				esc_html( $current_user->display_name )
			);
			?>
		</h2>
		<p class="tl-dash__sub">
			<?php
			if ( $order_count ) {
				printf(
					/* translators: %s: number of orders */
					esc_html( _n( '%s order placed with Titan Labs.', '%s orders placed with Titan Labs.', $order_count, 'titan-labs' ) ),
					esc_html( number_format_i18n( $order_count ) )
				);
			} else {
				esc_html_e( 'No orders yet — every peptide ships with its Certificate of Analysis.', 'titan-labs' );
			}
			?>
		</p>
	</div>

	<?php if ( $last_order ) : ?>
		<div class="tl-dash__last">
			<div class="tl-dash__lasthead">
				<span class="tl-eyebrow"><?php esc_html_e( 'Most recent order', 'titan-labs' ); ?></span>
				<span class="tl-dash__status tl-dash__status--<?php echo esc_attr( $last_order->get_status() ); ?>">
					<?php echo esc_html( wc_get_order_status_name( $last_order->get_status() ) ); ?>
				</span>
			</div>
			<div class="tl-dash__lastbody">
				<div>
					<strong>#<?php echo esc_html( $last_order->get_order_number() ); ?></strong>
					<span><?php echo esc_html( wc_format_datetime( $last_order->get_date_created() ) ); ?></span>
				</div>
				<div class="tl-dash__lasttotal">
					<?php echo wp_kses_post( $last_order->get_formatted_order_total() ); ?>
				</div>
				<a class="tl-btn tl-btn--ghost tl-btn--sm"
					href="<?php echo esc_url( $last_order->get_view_order_url() ); ?>">
					<?php esc_html_e( 'View order', 'titan-labs' ); ?>
				</a>
			</div>
		</div>
	<?php endif; ?>

	<div class="tl-dash__cards">
		<?php foreach ( $cards as $card ) : ?>
			<a class="tl-dash__card" href="<?php echo esc_url( $card['url'] ); ?>">
				<span class="tl-dash__cardicon" aria-hidden="true">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
						stroke-linecap="round" stroke-linejoin="round">
						<path d="<?php echo esc_attr( $card['icon'] ); ?>"/>
					</svg>
				</span>
				<strong><?php echo esc_html( $card['label'] ); ?></strong>
				<em><?php echo esc_html( $card['desc'] ); ?></em>
			</a>
		<?php endforeach; ?>
	</div>

	<p class="tl-dash__logout">
		<?php
		printf(
			/* translators: %s: logout URL */
			wp_kses( __( 'Not you? <a href="%s">Log out</a>', 'titan-labs' ), array( 'a' => array( 'href' => array() ) ) ),
			esc_url( wc_logout_url() )
		);
		?>
	</p>

</div>

<?php
/**
 * Kept so extensions hooking the dashboard still render.
 *
 * @since 2.6.0
 */
do_action( 'woocommerce_account_dashboard' );
