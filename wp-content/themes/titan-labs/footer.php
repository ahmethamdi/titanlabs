<?php
/**
 * Site footer.
 *
 * @package TitanLabs
 */

defined( 'ABSPATH' ) || exit;

$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
?>

<footer class="tl-footer">
	<div class="tl-container">

		<div class="tl-footer__cols">

			<div class="tl-footer__brand">
				<a class="tl-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" style="margin-bottom:.9rem">
					<?php if ( has_custom_logo() ) : ?>
						<?php the_custom_logo(); ?>
					<?php else : ?>
						<span class="tl-logo__mark" aria-hidden="true">TL</span>
						<span><?php bloginfo( 'name' ); ?></span>
					<?php endif; ?>
				</a>
				<p><?php echo wp_kses_post( get_theme_mod( 'titan_footer_about', __( 'Lab-verified research peptides for Europe. Purity measured, identity confirmed, and a Certificate of Analysis published on every peptide — discreetly & securely delivered across Europe.', 'titan-labs' ) ) ); ?></p>
			</div>

			<?php
			titan_footer_menu( 'footer-shop', __( 'Shop', 'titan-labs' ), array(
				__( 'Build a Stack', 'titan-labs' )  => home_url( '/stack/' ),
				__( 'Bestsellers', 'titan-labs' )    => home_url( '/product-category/most-popular/' ),
				__( 'Peptide Vials', 'titan-labs' )  => home_url( '/product-category/peptide-vials/' ),
				__( 'Peptide Pens', 'titan-labs' )   => home_url( '/product-category/peptide-pens/' ),
				__( 'Nasal Sprays', 'titan-labs' )   => home_url( '/product-category/peptide-sprays/' ),
				__( 'Peptide Orals', 'titan-labs' )  => home_url( '/product-category/peptide-orals/' ),
				__( 'Blends & Stacks', 'titan-labs' ) => home_url( '/product-category/peptide-blends/' ),
			) );

			titan_footer_menu( 'footer-goal', __( 'Shop by Goal', 'titan-labs' ), array(
				__( 'Recovery & Repair', 'titan-labs' )   => home_url( '/product-category/recovery-repair/' ),
				__( 'Metabolic & Weight', 'titan-labs' )  => home_url( '/product-category/metabolic-weight/' ),
				__( 'Longevity & Immune', 'titan-labs' )  => home_url( '/product-category/longevity-immune/' ),
				__( 'Skin & Beauty', 'titan-labs' )       => home_url( '/product-category/skin-beauty/' ),
				__( 'Cognitive & Neuro', 'titan-labs' )   => home_url( '/product-category/cognitive-neuro/' ),
				__( 'Growth & Body', 'titan-labs' )       => home_url( '/product-category/growth-body/' ),
			) );

			titan_footer_menu( 'footer-help', __( 'Help', 'titan-labs' ), array(
				__( 'Delivery & Shipping', 'titan-labs' ) => home_url( '/shipping-policy/' ),
				__( 'FAQ', 'titan-labs' )                 => home_url( '/faq/' ),
				__( 'Refunds', 'titan-labs' )             => home_url( '/refund-policy/' ),
				__( 'Peptide Calculator', 'titan-labs' )  => home_url( '/peptide-calculator/' ),
				__( 'Track Order', 'titan-labs' )         => home_url( '/track-order/' ),
				__( 'Contact Us', 'titan-labs' )          => home_url( '/contact-us/' ),
			) );

			titan_footer_menu( 'footer-legal', __( 'Company', 'titan-labs' ), array(
				__( 'About Us', 'titan-labs' )         => home_url( '/about-us/' ),
				__( 'Lab Results (COA)', 'titan-labs' ) => home_url( '/lab-results/' ),
				__( 'Customer Reviews', 'titan-labs' ) => home_url( '/reviews/' ),
				__( 'Blog', 'titan-labs' )             => home_url( '/blog/' ),
				__( 'Wholesale', 'titan-labs' )        => home_url( '/wholesale/' ),
				__( 'Partner Program', 'titan-labs' )  => home_url( '/affiliate-program/' ),
			) );
			?>

		</div>

		<div class="tl-footer__pay">
			<div class="tl-footer__methods">
				<span class="tl-badge tl-badge--outline"><?php esc_html_e( 'SEPA Bank Transfer', 'titan-labs' ); ?></span>
				<span class="tl-badge tl-badge--outline"><?php esc_html_e( 'Crypto', 'titan-labs' ); ?></span>
				<span class="tl-badge tl-badge--outline"><?php esc_html_e( 'Tracked DHL Shipping', 'titan-labs' ); ?></span>
			</div>
			<p class="tl-footer__disclaimer">
				<?php echo wp_kses_post( get_theme_mod( 'titan_footer_disclaimer', __( 'Products from this website are NOT intended to be used to diagnose or treat any medical condition or disease. Products on this website are sold for laboratory research purposes only and are for in-vitro lab research use only. The products are not medicines or drugs and have not been approved to prevent, treat, diagnose, mitigate, or cure any disease, ailment or medical condition.', 'titan-labs' ) ) ); ?>
			</p>
		</div>

		<div class="tl-footer__bottom">
			<span>
				<?php
				printf(
					/* translators: 1: year, 2: site name */
					esc_html__( '© %1$s %2$s. All rights reserved.', 'titan-labs' ),
					esc_html( gmdate( 'Y' ) ),
					esc_html( get_bloginfo( 'name' ) )
				);
				?>
			</span>
			<span>
				<a href="<?php echo esc_url( home_url( '/terms-of-service/' ) ); ?>"><?php esc_html_e( 'Terms', 'titan-labs' ); ?></a> ·
				<a href="<?php echo esc_url( home_url( '/privacy-policy/' ) ); ?>"><?php esc_html_e( 'Privacy', 'titan-labs' ); ?></a> ·
				<a href="<?php echo esc_url( home_url( '/legal-notice/' ) ); ?>"><?php esc_html_e( 'Legal Notice', 'titan-labs' ); ?></a>
			</span>
			<span><?php esc_html_e( 'For research use only', 'titan-labs' ); ?></span>
		</div>

	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
