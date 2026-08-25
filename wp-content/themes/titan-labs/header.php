<?php
/**
 * Site header.
 *
 * @package TitanLabs
 */

defined( 'ABSPATH' ) || exit;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link" href="#main"><?php esc_html_e( 'Skip to content', 'titan-labs' ); ?></a>

<?php if ( get_theme_mod( 'titan_announce_enabled', true ) ) : ?>
	<div class="tl-announce">
		<?php echo wp_kses_post( get_theme_mod( 'titan_announce_text', __( 'Third-party tested to ≥99% purity · Tracked DHL shipping across Europe', 'titan-labs' ) ) ); ?>
	</div>
<?php endif; ?>

<header class="tl-header">
	<div class="tl-container tl-header__bar">

		<a class="tl-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php else : ?>
				<span class="tl-logo__mark" aria-hidden="true">TL</span>
				<span><?php bloginfo( 'name' ); ?></span>
			<?php endif; ?>
		</a>

		<nav class="tl-nav" aria-label="<?php esc_attr_e( 'Primary', 'titan-labs' ); ?>">
			<?php titan_primary_nav(); ?>
		</nav>

		<div class="tl-header__actions">

			<?php if ( function_exists( 'wc_get_page_permalink' ) ) : ?>
				<?php
				$account_url = wc_get_page_permalink( 'myaccount' );
				$logged_in   = is_user_logged_in();
				?>
				<a class="tl-iconbtn tl-account" href="<?php echo esc_url( $account_url ); ?>"
					aria-label="<?php echo esc_attr(
						$logged_in
							? __( 'Your account', 'titan-labs' )
							: __( 'Sign in', 'titan-labs' )
					); ?>">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
						stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
						<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
						<circle cx="12" cy="7" r="4"/>
					</svg>
					<?php if ( $logged_in ) : ?>
						<span class="tl-account__dot" aria-hidden="true"></span>
					<?php endif; ?>
				</a>
			<?php endif; ?>

			<?php if ( function_exists( 'wc_get_cart_url' ) ) : ?>
				<a class="tl-iconbtn tl-cart" href="<?php echo esc_url( wc_get_cart_url() ); ?>"
					data-cart-open
					aria-controls="tl-cartdrawer"
					aria-label="<?php esc_attr_e( 'View cart', 'titan-labs' ); ?>">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
						stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
						<circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
						<path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
					</svg>
					<?php titan_cart_count_markup(); ?>
				</a>
			<?php endif; ?>

			<button type="button" class="tl-iconbtn tl-iconbtn--menu" data-drawer-open aria-expanded="false"
				aria-controls="tl-drawer"
				aria-label="<?php esc_attr_e( 'Open menu', 'titan-labs' ); ?>">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
					stroke-linecap="round" aria-hidden="true">
					<path d="M3 6h18M3 12h18M3 18h18"/>
				</svg>
			</button>

		</div>
	</div>
</header>

<div class="tl-drawer" id="tl-drawer" data-drawer>
	<div class="tl-drawer__scrim"></div>
	<div class="tl-drawer__panel" role="dialog" aria-modal="true"
		aria-label="<?php esc_attr_e( 'Site menu', 'titan-labs' ); ?>">

		<div class="tl-drawer__head">
			<strong><?php esc_html_e( 'Menu', 'titan-labs' ); ?></strong>
			<button type="button" class="tl-iconbtn" data-drawer-close
				aria-label="<?php esc_attr_e( 'Close menu', 'titan-labs' ); ?>">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
					stroke-linecap="round" aria-hidden="true">
					<path d="M18 6 6 18M6 6l12 12"/>
				</svg>
			</button>
		</div>

		<nav aria-label="<?php esc_attr_e( 'Mobile', 'titan-labs' ); ?>">
			<?php titan_primary_nav(); ?>
		</nav>
	</div>
</div>

<?php if ( function_exists( 'WC' ) ) : ?>
	<div class="tl-cartdrawer" id="tl-cartdrawer" data-cart-drawer>
		<div class="tl-cartdrawer__scrim" data-cart-close></div>
		<div class="tl-cartdrawer__panel" role="dialog" aria-modal="true"
			aria-label="<?php esc_attr_e( 'Shopping cart', 'titan-labs' ); ?>">

			<div class="tl-cartdrawer__head">
				<strong><?php esc_html_e( 'Your cart', 'titan-labs' ); ?></strong>
				<button type="button" class="tl-iconbtn" data-cart-close
					aria-label="<?php esc_attr_e( 'Close cart', 'titan-labs' ); ?>">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
						stroke-linecap="round" aria-hidden="true">
						<path d="M18 6 6 18M6 6l12 12"/>
					</svg>
				</button>
			</div>

			<div class="tl-cartdrawer__content" data-cart-content>
				<?php get_template_part( 'template-parts/cart-drawer' ); ?>
			</div>

			<div class="tl-cartdrawer__busy" aria-hidden="true"><span></span></div>
		</div>
	</div>
<?php endif; ?>

<?php if ( get_theme_mod( 'titan_age_gate', true ) && ! titan_is_elementor_canvas() ) : ?>
	<div class="tl-agegate" role="dialog" aria-modal="true" aria-labelledby="tl-agegate-title">
		<div class="tl-agegate__box">
			<span class="tl-logo__mark" aria-hidden="true" style="margin:0 auto .9rem">TL</span>
			<h2 id="tl-agegate-title" style="font-size:1.4rem">
				<?php
				printf(
					/* translators: %d: minimum age */
					esc_html__( 'Are you %d or older?', 'titan-labs' ),
					(int) get_theme_mod( 'titan_min_age', 21 )
				);
				?>
			</h2>
			<p>
				<?php esc_html_e( 'All products on this site are sold for laboratory research purposes only. They are not medicines and are not for human consumption.', 'titan-labs' ); ?>
			</p>
			<div class="tl-agegate__actions">
				<button type="button" class="tl-btn tl-btn--primary" data-age-confirm>
					<?php esc_html_e( 'Yes, I confirm', 'titan-labs' ); ?>
				</button>
				<button type="button" class="tl-btn tl-btn--ghost" data-age-decline>
					<?php esc_html_e( 'No, exit', 'titan-labs' ); ?>
				</button>
			</div>
		</div>
	</div>
<?php endif; ?>
