<?php
/**
 * Template part for displaying the search icon
 *
 * @package Shopwell
 */

$search_icon_class = isset( $args['search_icon_class'] ) && ! empty( $args['search_icon_class'] ) ? $args['search_icon_class'] : '';

?>

<div class="header-mobile-search">
	<a href="<?php echo esc_url( home_url( '/' ) ); ?>" data-toggle="modal" data-target="search-modal" class="<?php echo esc_attr( $search_icon_class ); ?>">
		<?php echo \Shopwell\Icon::get_svg( 'search' ); ?>
	</a>
</div>
