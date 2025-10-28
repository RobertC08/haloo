<?php
/**
 * Template file for displaying wishlist mobile
 *
 * @package Shopwell
 */

if ( ! function_exists( 'WC' ) ) {
	return;
}

if ( ! class_exists( '\WCBoost\Wishlist\Helper' ) ) {
	return;
}
?>

<a href="<?php echo esc_url( wc_get_page_permalink( 'wishlist' ) ); ?>" class="shopwell-mobile-navigation-bar__icon wishlist-icon">
	<span>
		<?php echo \Shopwell\Icon::get_svg( 'wishlist' ); ?>
		<span class="counter wishlist-counter"><?php echo intval( \WCBoost\Wishlist\Helper::get_wishlist()->count_items() ); ?></span>
	</span>
	<em><?php echo esc_html__( 'Wishlist', 'shopwell' ); ?></em>
</a>
