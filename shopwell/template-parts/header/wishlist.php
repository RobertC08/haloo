<?php

/**
 * Template part for displaying the wishlist icon.
 *
 * @package Shopwell
 */

use Shopwell\Helper;

if ( ! function_exists( 'WC' ) ) {
	return;
}

if ( ! class_exists( 'WCBoost\Wishlist\Helper' ) ) {
	return;
}

$wishlist            = WCBoost\Wishlist\Helper::get_wishlist();
$wishlist_display    = isset( $args['wishlist_display'] ) ? $args['wishlist_display'] : 'icon';
$wishlist_text       = isset( $args['wishlist_text'] ) && ! empty( $args['wishlist_text'] ) ? $args['wishlist_text'] : '';
$wishlist_classes    = isset( $args['wishlist_classes'] ) ? $args['wishlist_classes'] : '';
$wishlist_text_class = isset( $args['wishlist_text_class'] ) ? $args['wishlist_text_class'] : '';


?>
<div class="header-wishlist">
	<a href="<?php echo esc_url( wc_get_page_permalink( 'wishlist' ) ); ?>" class="shopwell-button <?php echo esc_attr( $wishlist_classes ); ?>">
		<span class="shopwell-button__icon"><?php echo \Shopwell\Icon::get_svg( 'wishlist' ); ?></span>
		<span class="shopwell-button__text <?php echo esc_attr( $wishlist_text_class ); ?>"><?php echo esc_html( $wishlist_text ); ?></span>
		<?php echo Helper::wishlist_counter(); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</a>
</div>
