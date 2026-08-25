<?php
/**
 * Shop filtering.
 *
 * The catalogue models two orthogonal axes — format (vial/pen/spray/oral) and
 * research goal (metabolic, longevity, …) — as flat sibling product_cat terms.
 * Rather than migrate 71 products to attributes (a data change that would not
 * travel through git and would have to be repeated on the server), the groups
 * are declared here and translated into a tax_query.
 *
 * @package TitanLabs
 */

defined( 'ABSPATH' ) || exit;

/**
 * Filter groups, in the order they appear in the sidebar.
 *
 * @return array<string, array{label:string, type:string, terms?:array<int,string>, bands?:array}>
 */
function titan_filter_groups() {
	return array(
		'format' => array(
			'label' => __( 'Format', 'titan-labs' ),
			'type'  => 'tax',
			'terms' => array( 'peptide-vials', 'peptide-pens', 'peptide-sprays', 'peptide-orals' ),
		),
		'goal'   => array(
			'label' => __( 'Research goal', 'titan-labs' ),
			'type'  => 'tax',
			'terms' => array(
				'metabolic-weight',
				'longevity-immune',
				'recovery-repair',
				'cognitive-neuro',
				'skin-beauty',
				'growth-body',
				'peptide-blends',
			),
		),
		'price'  => array(
			'label' => __( 'Price', 'titan-labs' ),
			'type'  => 'price',
			// Bands chosen from the actual distribution: 18 / 23 / 21 / 9.
			'bands' => array(
				'0-75'    => array( 0, 74.99, __( 'Under 75 €', 'titan-labs' ) ),
				'75-150'  => array( 75, 149.99, __( '75 – 150 €', 'titan-labs' ) ),
				'150-200' => array( 150, 199.99, __( '150 – 200 €', 'titan-labs' ) ),
				'200-'    => array( 200, 999999, __( '200 € and up', 'titan-labs' ) ),
			),
		),
		'lab'    => array(
			'label' => __( 'Lab data', 'titan-labs' ),
			'type'  => 'lab',
		),
	);
}

/**
 * Slug of the category archive being viewed, or '' on the main shop.
 *
 * During an admin-ajax request is_product_taxonomy() is false, so the handler
 * records the archive here and every helper reads it from one place.
 *
 * @param string|null $set Internal: value to remember for this request.
 * @return string
 */
function titan_current_archive_term( $set = null ) {
	static $slug = null;

	if ( null !== $set ) {
		$slug = sanitize_title( $set );
	}

	if ( null === $slug ) {
		$slug = '';
		if ( function_exists( 'is_product_taxonomy' ) && is_product_taxonomy() ) {
			$term = get_queried_object();
			if ( $term && ! empty( $term->slug ) ) {
				$slug = $term->slug;
			}
		}
	}

	return $slug;
}

/**
 * Base URL for filter links — the archive being viewed, or the shop page.
 *
 * @return string
 */
function titan_filter_base_url() {
	$slug = titan_current_archive_term();

	if ( $slug ) {
		$link = get_term_link( $slug, 'product_cat' );
		if ( ! is_wp_error( $link ) ) {
			return $link;
		}
	}

	return function_exists( 'wc_get_page_permalink' )
		? wc_get_page_permalink( 'shop' )
		: home_url( '/shop/' );
}

/**
 * Reads the active filters from the query string.
 *
 * @return array<string, array<int, string>>
 */
function titan_active_filters() {
	$active = array();

	foreach ( titan_filter_groups() as $key => $group ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only browse state.
		$raw = isset( $_GET[ 'f_' . $key ] ) ? sanitize_text_field( wp_unslash( $_GET[ 'f_' . $key ] ) ) : '';
		if ( '' === $raw ) {
			continue;
		}

		$values = array_filter( array_map( 'sanitize_title', explode( ',', $raw ) ) );

		// Only accept values this group actually offers.
		if ( 'tax' === $group['type'] ) {
			$values = array_intersect( $values, $group['terms'] );
		} elseif ( 'price' === $group['type'] ) {
			$values = array_intersect( $values, array_keys( $group['bands'] ) );
		} elseif ( 'lab' === $group['type'] ) {
			$values = array_intersect( $values, array( 'tested' ) );
		}

		if ( $values ) {
			$active[ $key ] = array_values( $values );
		}
	}

	return $active;
}

/**
 * Builds the tax_query and meta_query clauses for a set of active filters.
 *
 * Kept separate from the pre_get_posts hook so the AJAX handler can apply the
 * identical clauses without depending on is_shop(), which is false during an
 * admin-ajax request.
 *
 * @param array $active Active filters.
 * @return array{tax:array, meta:array}
 */
function titan_filter_clauses( $active ) {
	$groups = titan_filter_groups();
	$tax    = array();
	$meta   = array();

	// Within a group the terms are alternatives (OR); across groups they
	// narrow (AND) — a pen AND for metabolic research.
	foreach ( array( 'format', 'goal' ) as $key ) {
		if ( empty( $active[ $key ] ) ) {
			continue;
		}
		$tax[] = array(
			'taxonomy' => 'product_cat',
			'field'    => 'slug',
			'terms'    => $active[ $key ],
			'operator' => 'IN',
		);
	}

	if ( ! empty( $active['price'] ) ) {
		$bands = array( 'relation' => 'OR' );
		foreach ( $active['price'] as $band ) {
			if ( ! isset( $groups['price']['bands'][ $band ] ) ) {
				continue;
			}
			list( $min, $max ) = $groups['price']['bands'][ $band ];
			$bands[] = array(
				'key'     => '_price',
				'value'   => array( $min, $max ),
				'compare' => 'BETWEEN',
				'type'    => 'DECIMAL(10,2)',
			);
		}
		$meta[] = $bands;
	}

	if ( ! empty( $active['lab'] ) ) {
		// "—" means not applicable, so it must not count as tested.
		$meta[] = array(
			'key'     => '_titan_purity',
			'value'   => '[0-9]',
			'compare' => 'REGEXP',
		);
	}

	return array( 'tax' => $tax, 'meta' => $meta );
}

/**
 * Applies the active filters to the shop query.
 *
 * @param WP_Query $q Query object.
 */
function titan_apply_filters( $q ) {
	if ( is_admin() || ! $q->is_main_query() ) {
		return;
	}
	if ( ! ( function_exists( 'is_shop' ) && ( is_shop() || is_product_taxonomy() ) ) ) {
		return;
	}

	$active = titan_active_filters();
	if ( ! $active ) {
		return;
	}

	$clauses = titan_filter_clauses( $active );

	if ( $clauses['tax'] ) {
		$tax_query = array_merge( (array) $q->get( 'tax_query' ), $clauses['tax'] );
		if ( count( $tax_query ) > 1 ) {
			$tax_query['relation'] = 'AND';
		}
		$q->set( 'tax_query', $tax_query );
	}

	if ( $clauses['meta'] ) {
		$meta_query = array_merge( (array) $q->get( 'meta_query' ), $clauses['meta'] );
		if ( count( $meta_query ) > 1 ) {
			$meta_query['relation'] = 'AND';
		}
		$q->set( 'meta_query', $meta_query );
	}
}
add_action( 'pre_get_posts', 'titan_apply_filters' );

/**
 * Counts how many published products match one filter option, respecting the
 * other groups already applied. Disabled-but-visible options teach the shape
 * of the catalogue; hidden ones make the sidebar feel unstable.
 *
 * @param string $group_key Group being counted.
 * @param string $value     Option value.
 * @param array  $active    Currently active filters.
 * @return int
 */
function titan_filter_count( $group_key, $value, $active ) {
	$groups = titan_filter_groups();

	$args = array(
		'post_type'      => 'product',
		'post_status'    => 'publish',
		'posts_per_page' => 1,
		'fields'         => 'ids',
		'no_found_rows'  => false,
	);

	$tax  = array();
	$meta = array();

	// Every other group stays applied; this group is replaced by the option
	// being counted, so counts read as "what if I also picked this".
	foreach ( array( 'format', 'goal' ) as $key ) {
		$terms = ( $key === $group_key ) ? array( $value ) : ( $active[ $key ] ?? array() );
		if ( $terms ) {
			$tax[] = array(
				'taxonomy' => 'product_cat',
				'field'    => 'slug',
				'terms'    => $terms,
				'operator' => 'IN',
			);
		}
	}

	$prices = ( 'price' === $group_key ) ? array( $value ) : ( $active['price'] ?? array() );
	if ( $prices ) {
		$bands = array( 'relation' => 'OR' );
		foreach ( $prices as $band ) {
			if ( ! isset( $groups['price']['bands'][ $band ] ) ) {
				continue;
			}
			list( $min, $max ) = $groups['price']['bands'][ $band ];
			$bands[] = array(
				'key'     => '_price',
				'value'   => array( $min, $max ),
				'compare' => 'BETWEEN',
				'type'    => 'DECIMAL(10,2)',
			);
		}
		$meta[] = $bands;
	}

	$lab = ( 'lab' === $group_key ) ? array( $value ) : ( $active['lab'] ?? array() );
	if ( $lab ) {
		$meta[] = array(
			'key'     => '_titan_purity',
			'value'   => '[0-9]',
			'compare' => 'REGEXP',
		);
	}

	$archive = titan_current_archive_term();
	if ( $archive ) {
		$tax[] = array(
			'taxonomy' => 'product_cat',
			'field'    => 'slug',
			'terms'    => array( $archive ),
		);
	}

	if ( $tax ) {
		$tax['relation'] = 'AND';
		$args['tax_query'] = $tax;
	}
	if ( $meta ) {
		$meta['relation'] = 'AND';
		$args['meta_query'] = $meta;
	}

	$q = new WP_Query( $args );

	return (int) $q->found_posts;
}

/**
 * Builds the URL for toggling one filter option.
 *
 * @param string $group  Group key.
 * @param string $value  Option value.
 * @param array  $active Active filters.
 * @return string
 */
function titan_filter_url( $group, $value, $active ) {
	$current = $active[ $group ] ?? array();

	if ( in_array( $value, $current, true ) ) {
		$current = array_diff( $current, array( $value ) );
	} else {
		$current[] = $value;
	}

	$base   = titan_filter_base_url();
	$params = array();
	foreach ( $active as $k => $v ) {
		if ( $k !== $group && $v ) {
			$params[ 'f_' . $k ] = implode( ',', $v );
		}
	}
	if ( $current ) {
		$params[ 'f_' . $group ] = implode( ',', $current );
	}

	return $params ? add_query_arg( $params, $base ) : $base;
}

/**
 * URL that clears every filter.
 *
 * @return string
 */
function titan_filter_clear_url() {
	return titan_filter_base_url();
}

/**
 * Human label for one option, used by the sidebar and the active chips.
 *
 * @param string $group Group key.
 * @param string $value Option value.
 * @return string
 */
function titan_filter_label( $group, $value ) {
	$groups = titan_filter_groups();

	if ( 'price' === $group ) {
		return $groups['price']['bands'][ $value ][2] ?? $value;
	}
	if ( 'lab' === $group ) {
		return __( 'Lab tested', 'titan-labs' );
	}

	$term = get_term_by( 'slug', $value, 'product_cat' );

	return $term ? $term->name : $value;
}

/**
 * Renders the filter sidebar.
 */
function titan_render_filters() {
	$groups = titan_filter_groups();
	$active = titan_active_filters();
	?>
	<form class="tl-filters" data-filters
		action="<?php echo esc_url( titan_filter_clear_url() ); ?>" method="get">

		<div class="tl-filters__head">
			<strong><?php esc_html_e( 'Filter', 'titan-labs' ); ?></strong>
			<?php if ( $active ) : ?>
				<a class="tl-filters__clear" href="<?php echo esc_url( titan_filter_clear_url() ); ?>">
					<?php esc_html_e( 'Clear all', 'titan-labs' ); ?>
				</a>
			<?php endif; ?>
		</div>

		<?php
		foreach ( $groups as $key => $group ) :
			// On a category archive its own axis is already fixed.
			if ( 'tax' === $group['type']
				&& in_array( titan_current_archive_term(), $group['terms'], true ) ) {
				continue;
			}

			$options = array();
			if ( 'tax' === $group['type'] ) {
				foreach ( $group['terms'] as $slug ) {
					$options[ $slug ] = titan_filter_label( $key, $slug );
				}
			} elseif ( 'price' === $group['type'] ) {
				foreach ( $group['bands'] as $band => $spec ) {
					$options[ $band ] = $spec[2];
				}
			} else {
				$options['tested'] = __( 'Lab tested', 'titan-labs' );
			}
			?>
			<fieldset class="tl-filter">
				<legend class="tl-filter__title"><?php echo esc_html( $group['label'] ); ?></legend>
				<ul class="tl-filter__list">
					<?php
					foreach ( $options as $value => $label ) :
						$on    = in_array( $value, $active[ $key ] ?? array(), true );
						$count = titan_filter_count( $key, $value, $active );
						?>
						<li>
							<a class="tl-filter__opt<?php echo $on ? ' is-on' : ''; ?><?php echo ( ! $count && ! $on ) ? ' is-empty' : ''; ?>"
								href="<?php echo esc_url( titan_filter_url( $key, $value, $active ) ); ?>"
								data-filter-opt
								<?php echo ( ! $count && ! $on ) ? 'aria-disabled="true"' : ''; ?>
								role="checkbox" aria-checked="<?php echo $on ? 'true' : 'false'; ?>">
								<span class="tl-filter__box" aria-hidden="true"></span>
								<span class="tl-filter__label"><?php echo esc_html( $label ); ?></span>
								<span class="tl-filter__count"><?php echo esc_html( $count ); ?></span>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</fieldset>
		<?php endforeach; ?>
	</form>
	<?php
}

/**
 * Renders the toolbar, active chips and product grid.
 *
 * Shared by the template and the AJAX handler so a filtered page and a
 * filtered fetch can never drift apart.
 */
function titan_render_shop_results() {
	$total = (int) wc_get_loop_prop( 'total' );
	?>
	<div class="tl-shoplayout__toolbar">
		<p class="tl-shoplayout__count" aria-live="polite" data-result-count>
			<?php
			printf(
				/* translators: %s: number of products */
				esc_html( _n( '%s product', '%s products', $total, 'titan-labs' ) ),
				esc_html( number_format_i18n( $total ) )
			);
			?>
		</p>
		<?php woocommerce_catalog_ordering(); ?>
	</div>

	<?php titan_render_active_chips(); ?>

	<?php
	if ( woocommerce_product_loop() ) {

		woocommerce_product_loop_start();

		if ( $total ) {
			while ( have_posts() ) {
				the_post();
				do_action( 'woocommerce_shop_loop' );
				wc_get_template_part( 'content', 'product' );
			}
		}

		woocommerce_product_loop_end();

		do_action( 'woocommerce_after_shop_loop' );

	} else {
		?>
		<div class="tl-shoplayout__empty">
			<p class="tl-shoplayout__emptytitle"><?php esc_html_e( 'No products match those filters', 'titan-labs' ); ?></p>
			<p class="tl-shoplayout__emptytext">
				<?php esc_html_e( 'Try widening the price range or clearing a filter.', 'titan-labs' ); ?>
			</p>
			<a class="tl-btn tl-btn--primary tl-btn--sm" href="<?php echo esc_url( titan_filter_clear_url() ); ?>">
				<?php esc_html_e( 'Clear all filters', 'titan-labs' ); ?>
			</a>
		</div>
		<?php
	}
}

/**
 * Returns the filtered grid and sidebar for a fetch from the client.
 */
function titan_ajax_filter() {
	check_ajax_referer( 'titan_nonce', 'nonce' );

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- nonce checked above.
	$url = isset( $_POST['url'] ) ? esc_url_raw( wp_unslash( $_POST['url'] ) ) : '';

	if ( ! $url || ! str_starts_with( $url, home_url() ) ) {
		wp_send_json_error( array( 'message' => __( 'Bad request.', 'titan-labs' ) ), 400 );
	}

	/*
	 * Replay the URL through WordPress so is_shop(), is_product_taxonomy() and
	 * pre_get_posts behave exactly as on a normal page load — the alternative
	 * is duplicating the query logic and watching the two drift.
	 */
	$parts = wp_parse_url( $url );
	$query = array();
	if ( ! empty( $parts['query'] ) ) {
		parse_str( $parts['query'], $query );
	}
	foreach ( $query as $k => $v ) {
		if ( str_starts_with( (string) $k, 'f_' ) || in_array( $k, array( 'orderby', 'paged' ), true ) ) {
			$_GET[ $k ] = $v;
		}
	}

	$path = untrailingslashit( str_replace( untrailingslashit( home_url() ), '', $parts['path'] ?? '' ) );

	/*
	 * Record the archive before anything reads it — the sidebar hides the axis
	 * the archive already fixes, and link builders use it as their base. The
	 * empty string matters: without it a fetch for /shop/ would inherit the
	 * category a previous fetch in the same request had set, and every link
	 * would come back pointing at that archive.
	 */
	preg_match( '#/product-category/([^/]+)#', $path, $archive_m );
	titan_current_archive_term( $archive_m[1] ?? '' );

	$active = titan_active_filters();

	/*
	 * Build the query directly rather than leaning on pre_get_posts: during an
	 * admin-ajax request is_shop() is false, so the hook would skip and every
	 * fetch would come back unfiltered.
	 */
	$clauses  = titan_filter_clauses( $active );
	$per_page = (int) apply_filters( 'loop_shop_per_page', 24 );

	$wp_q = array(
		'post_type'      => 'product',
		'post_status'    => 'publish',
		'posts_per_page' => $per_page,
		'paged'          => max( 1, (int) ( $query['paged'] ?? 1 ) ),
	);

	$tax = $clauses['tax'];

	// A category archive keeps its own term as a further AND.
	if ( ! empty( $archive_m[1] ) ) {
		$tax[] = array(
			'taxonomy' => 'product_cat',
			'field'    => 'slug',
			'terms'    => array( sanitize_title( $archive_m[1] ) ),
		);
	}

	// Products hidden from the catalogue must stay hidden.
	$tax[] = array(
		'taxonomy' => 'product_visibility',
		'field'    => 'name',
		'terms'    => array( 'exclude-from-catalog' ),
		'operator' => 'NOT IN',
	);

	if ( count( $tax ) > 1 ) {
		$tax['relation'] = 'AND';
	}
	$wp_q['tax_query'] = $tax;

	if ( $clauses['meta'] ) {
		$meta = $clauses['meta'];
		if ( count( $meta ) > 1 ) {
			$meta['relation'] = 'AND';
		}
		$wp_q['meta_query'] = $meta;
	}

	// Reuse WooCommerce's own ordering rather than reimplementing it.
	$orderby = isset( $query['orderby'] ) ? wc_clean( wp_unslash( $query['orderby'] ) ) : '';
	if ( function_exists( 'WC' ) && WC()->query ) {
		$wp_q = array_merge( $wp_q, WC()->query->get_catalog_ordering_args(
			$orderby ? $orderby : get_option( 'woocommerce_default_catalog_orderby', 'menu_order' )
		) );
	}

	$loop = new WP_Query( $wp_q );

	// titan_render_shop_results() and content-product.php read the globals.
	$prev_query = $GLOBALS['wp_query'];
	$prev_post  = $GLOBALS['post'] ?? null;
	$GLOBALS['wp_query'] = $loop;

	wc_set_loop_prop( 'total', (int) $loop->found_posts );
	wc_set_loop_prop( 'total_pages', (int) $loop->max_num_pages );
	wc_set_loop_prop( 'per_page', $per_page );
	wc_set_loop_prop( 'current_page', max( 1, (int) ( $query['paged'] ?? 1 ) ) );
	wc_set_loop_prop( 'columns', (int) apply_filters( 'loop_shop_columns', 4 ) );
	wc_set_loop_prop( 'is_shop', true );
	wc_set_loop_prop( 'name', 'shop' );

	ob_start();
	titan_render_shop_results();
	$results = ob_get_clean();

	ob_start();
	titan_render_filters();
	$filters = ob_get_clean();

	$GLOBALS['wp_query'] = $prev_query;
	$GLOBALS['post']     = $prev_post;
	wp_reset_postdata();

	wp_send_json_success( array(
		'results' => $results,
		'filters' => $filters,
		'count'   => array_sum( array_map( 'count', $active ) ),
	) );
}
add_action( 'wp_ajax_titan_filter', 'titan_ajax_filter' );
add_action( 'wp_ajax_nopriv_titan_filter', 'titan_ajax_filter' );

/**
 * Chips for the filters currently applied.
 */
function titan_render_active_chips() {
	$active = titan_active_filters();

	if ( ! $active ) {
		return;
	}
	?>
	<div class="tl-activefilters" data-active-filters>
		<?php foreach ( $active as $group => $values ) : ?>
			<?php foreach ( $values as $value ) : ?>
				<a class="tl-chip" href="<?php echo esc_url( titan_filter_url( $group, $value, $active ) ); ?>"
					data-filter-opt>
					<?php echo esc_html( titan_filter_label( $group, $value ) ); ?>
					<span aria-hidden="true">&times;</span>
					<span class="screen-reader-text"><?php esc_html_e( 'Remove filter', 'titan-labs' ); ?></span>
				</a>
			<?php endforeach; ?>
		<?php endforeach; ?>
		<a class="tl-chip tl-chip--clear" href="<?php echo esc_url( titan_filter_clear_url() ); ?>" data-filter-opt>
			<?php esc_html_e( 'Clear all', 'titan-labs' ); ?>
		</a>
	</div>
	<?php
}
