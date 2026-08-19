<?php
/**
 * Verified reviews widget.
 *
 * @package TitanLabs
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;

/**
 * Aggregate rating summary plus a grid of recent verified reviews.
 */
class Titan_Reviews_Widget extends Titan_Widget_Base {

	public function get_name() {
		return 'titan-reviews';
	}

	public function get_title() {
		return __( 'Reviews Summary', 'titan-labs' );
	}

	public function get_icon() {
		return 'eicon-review';
	}

	protected function register_controls() {

		$this->start_controls_section(
			'content',
			array( 'label' => __( 'Content', 'titan-labs' ) )
		);

		$this->add_heading_controls(
			__( 'Verified reviews', 'titan-labs' ),
			__( 'Trusted by researchers across Europe', 'titan-labs' ),
			__( 'Independent, verified-buyer reviews from researchers buying peptides across Europe — the same lab-grade transparency we apply to every Certificate of Analysis.', 'titan-labs' )
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'display',
			array( 'label' => __( 'Display', 'titan-labs' ) )
		);

		$this->add_control(
			'show_summary',
			array(
				'label'        => __( 'Show score & breakdown', 'titan-labs' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'show_cards',
			array(
				'label'        => __( 'Show review cards', 'titan-labs' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'card_count',
			array(
				'label'     => __( 'Number of reviews', 'titan-labs' ),
				'type'      => Controls_Manager::NUMBER,
				'min'       => 1,
				'max'       => 12,
				'default'   => 6,
				'condition' => array( 'show_cards' => 'yes' ),
			)
		);

		$this->add_control(
			'min_rating',
			array(
				'label'     => __( 'Minimum rating shown', 'titan-labs' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => '4',
				'options'   => array(
					'1' => __( 'All reviews', 'titan-labs' ),
					'3' => __( '3 stars and up', 'titan-labs' ),
					'4' => __( '4 stars and up', 'titan-labs' ),
					'5' => __( '5 stars only', 'titan-labs' ),
				),
				'condition' => array( 'show_cards' => 'yes' ),
			)
		);

		$this->add_control(
			'columns',
			array(
				'label'     => __( 'Columns', 'titan-labs' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => '3',
				'options'   => array(
					'2' => __( '2 columns', 'titan-labs' ),
					'3' => __( '3 columns', 'titan-labs' ),
					'4' => __( '4 columns', 'titan-labs' ),
				),
				'condition' => array( 'show_cards' => 'yes' ),
			)
		);

		$this->add_background_control();

		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();

		$comments = get_comments( array(
			'post_type' => 'product',
			'status'    => 'approve',
			'parent'    => 0,
			'number'    => 200,
			'meta_key'  => 'rating',
			'orderby'   => 'comment_date_gmt',
			'order'     => 'DESC',
		) );

		if ( ! $comments ) {
			$this->render_placeholder( __( 'No product reviews yet — this section appears once reviews are published.', 'titan-labs' ) );
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
			$this->render_placeholder( __( 'No rated reviews found.', 'titan-labs' ) );
			return;
		}

		$average = round( $sum / $total, 1 );
		$min     = (int) ( $s['min_rating'] ?? 4 );

		$recent = array_filter(
			$comments,
			static function ( $comment ) use ( $min ) {
				return (int) get_comment_meta( $comment->comment_ID, 'rating', true ) >= $min;
			}
		);
		$recent = array_slice( $recent, 0, (int) ( $s['card_count'] ?? 6 ) );

		$this->open_section( $s );
		$this->render_heading( $s );

		if ( 'yes' === ( $s['show_summary'] ?? 'yes' ) ) :
			?>
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
			<?php
		endif;

		if ( 'yes' === ( $s['show_cards'] ?? 'yes' ) && $recent ) :
			?>
			<div class="tl-grid tl-grid--<?php echo esc_attr( $s['columns'] ?? '3' ); ?>">
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
			<?php
		endif;

		$this->close_section();
	}
}
