<?php
/**
 * Template part for displaying the search items
 *
 * @package Shopwell
 */

$placeholder = esc_attr__( 'Search for anything', 'shopwell' );
if ( \Shopwell\Helper::get_option( 'header_search_type' ) == 'adaptive' ) {
	if ( \Shopwell\Header\Search::type() == 'post' ) {
		$placeholder = esc_attr__( 'Search the blog...', 'shopwell' );
	} else {
		$placeholder = esc_attr__( 'Search products...', 'shopwell' );
	}
}

$classes = ! empty( $args ) && isset( $args['trending_searches_position'] ) && $args['trending_searches_position'] == 'inside' ? ' header-search__field--trending-inside' : '';

?>

<input type="text" name="s" class="header-search__field<?php echo esc_attr( $classes ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php echo esc_attr( $placeholder ); ?>" autocomplete="off">
