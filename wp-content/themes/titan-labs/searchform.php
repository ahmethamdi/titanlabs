<?php
/**
 * Search form.
 *
 * @package TitanLabs
 */

defined( 'ABSPATH' ) || exit;
?>
<form role="search" method="get" class="tl-coa__controls" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="tl-search"><?php esc_html_e( 'Search', 'titan-labs' ); ?></label>
	<input type="search" id="tl-search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>"
		placeholder="<?php esc_attr_e( 'Search peptides…', 'titan-labs' ); ?>">
	<button type="submit" class="tl-btn tl-btn--primary tl-btn--sm"><?php esc_html_e( 'Search', 'titan-labs' ); ?></button>
</form>
