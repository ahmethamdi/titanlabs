<?php
/**
 * Homepage — stack builder promo.
 *
 * @package TitanLabs
 */

defined( 'ABSPATH' ) || exit;

if ( ! get_theme_mod( 'titan_stack_enabled', true ) ) {
	return;
}

$t1_qty = (int) get_theme_mod( 'titan_stack_tier1_qty', 4 );
$t1_pct = (int) get_theme_mod( 'titan_stack_tier1_pct', 5 );
$t2_qty = (int) get_theme_mod( 'titan_stack_tier2_qty', 10 );
$t2_pct = (int) get_theme_mod( 'titan_stack_tier2_pct', 10 );
?>

<section class="tl-section" style="padding-block:clamp(2rem,1.2rem+3vw,4rem)">
	<div class="tl-container">
		<div class="tl-stack-promo">

			<div>
				<p class="tl-eyebrow"><?php esc_html_e( 'Mix & match', 'titan-labs' ); ?></p>
				<h2>
					<?php
					printf(
						/* translators: %d: maximum discount percentage */
						esc_html__( 'Build your own stack, save up to %d%%', 'titan-labs' ),
						$t2_pct
					);
					?>
				</h2>
				<p class="tl-lede tl-mb-0">
					<?php esc_html_e( 'Combine any research peptides — vials, pens, sprays & orals in one pack. Applied automatically to every item.', 'titan-labs' ); ?>
				</p>

				<div class="tl-stack-promo__tiers">
					<span class="tl-badge">
						<?php
						printf(
							/* translators: 1: item count, 2: discount percentage */
							esc_html__( '%1$d items · −%2$d%%', 'titan-labs' ),
							$t1_qty,
							$t1_pct
						);
						?>
					</span>
					<span class="tl-badge">
						<?php
						printf(
							/* translators: 1: item count, 2: discount percentage */
							esc_html__( '%1$d items · −%2$d%%', 'titan-labs' ),
							$t2_qty,
							$t2_pct
						);
						?>
					</span>
				</div>
			</div>

			<div>
				<a class="tl-btn tl-btn--primary tl-btn--block" href="<?php echo esc_url( home_url( '/stack/' ) ); ?>">
					<?php esc_html_e( 'Build a stack', 'titan-labs' ); ?>
				</a>
			</div>

		</div>
	</div>
</section>
