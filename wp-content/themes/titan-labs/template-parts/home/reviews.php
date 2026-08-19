<?php
/**
 * Homepage — verified reviews summary.
 * Aggregates real WooCommerce product reviews.
 *
 * @package TitanLabs
 */

defined( 'ABSPATH' ) || exit;

$comments = get_comments( array(
	'post_type'   => 'product',
	'status'      => 'approve',
	'parent'      => 0,
	'number'      => 200,
	'meta_key'    => 'rating',
	'orderby'     => 'comment_date_gmt',
	'order'       => 'DESC',
) );

if ( ! $comments ) {
	return;
}

$buckets = array( 5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0 );
$total   = 0;
$sum     = 0;

foreach ( $comments as $comment ) {
	$rating = (int) get_comment_meta( $comment->comment_ID, 'rating', true );
	if ( $rating < 1 || $rating > 5 ) {
		continue;
	}
	$buckets[ $rating ]++;
	$sum += $rating;
	$total++;
}

if ( ! $total ) {
	return;
}

$average = round( $sum / $total, 1 );
$recent  = array_slice( $comments, 0, 6 );
?>

<section class="tl-section">
	<div class="tl-container">

		<div class="tl-sectionhead">
			<div>
				<p class="tl-eyebrow"><?php esc_html_e( 'Verified reviews', 'titan-labs' ); ?></p>
				<h2><?php esc_html_e( 'Trusted by researchers across Europe', 'titan-labs' ); ?></h2>
				<p class="tl-lede tl-mb-0">
					<?php esc_html_e( 'Independent, verified-buyer reviews from researchers buying peptides across Europe — the same lab-grade transparency we apply to every Certificate of Analysis.', 'titan-labs' ); ?>
				</p>
			</div>
		</div>

		<div class="tl-reviews__summary" style="margin-bottom:2.5rem">

			<div class="tl-reviews__score">
				<b><?php echo esc_html( number_format_i18n( $average, 1 ) ); ?></b>
				<div class="tl-stars__glyphs" aria-hidden="true" style="font-size:1.1rem">
					<?php echo esc_html( str_repeat( '★', (int) round( $average ) ) . str_repeat( '☆', 5 - (int) round( $average ) ) ); ?>
				</div>
				<span>
					<?php
					printf(
						/* translators: %d: review count */
						esc_html( _n( 'Based on %d review', 'Based on %d reviews', $total, 'titan-labs' ) ),
						$total
					);
					?>
				</span>
			</div>

			<div class="tl-reviews__bars">
				<?php foreach ( array( 5, 4, 3, 2, 1 ) as $star ) : ?>
					<?php $pct = $total ? round( ( $buckets[ $star ] / $total ) * 100 ) : 0; ?>
					<div class="tl-reviews__bar">
						<span><?php printf( '%d ★', (int) $star ); ?></span>
						<span class="track">
							<span class="fill" style="width:<?php echo esc_attr( $pct ); ?>%"></span>
						</span>
						<span><?php echo esc_html( $buckets[ $star ] ); ?></span>
					</div>
				<?php endforeach; ?>
			</div>

		</div>

		<div class="tl-grid tl-grid--3">
			<?php foreach ( $recent as $comment ) : ?>
				<?php $rating = (int) get_comment_meta( $comment->comment_ID, 'rating', true ); ?>
				<article class="tl-review-card">
					<div class="tl-review-card__meta">
						<span class="tl-stars__glyphs" aria-hidden="true">
							<?php echo esc_html( str_repeat( '★', $rating ) . str_repeat( '☆', 5 - $rating ) ); ?>
						</span>
						<time datetime="<?php echo esc_attr( $comment->comment_date_gmt ); ?>">
							<?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $comment->comment_date ) ) ); ?>
						</time>
					</div>
					<p><?php echo esc_html( wp_trim_words( $comment->comment_content, 34 ) ); ?></p>
					<p class="tl-small tl-muted" style="margin-top:.6rem">
						<?php echo esc_html( $comment->comment_author ); ?> ·
						<?php echo esc_html( get_the_title( $comment->comment_post_ID ) ); ?>
					</p>
				</article>
			<?php endforeach; ?>
		</div>

	</div>
</section>
