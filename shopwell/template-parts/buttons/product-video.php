<?php

/**
 * Template part for displaying the product video button
 *
 * @package Shopwell
 */
?>

<a href="<?php echo esc_url( \Shopwell\WooCommerce\Single_Product::get_video_url() ); ?>" class="shopwell-button shopwell-button--icon shopwell-button--raised shopwell-shape--circle shopwell-button--video">
	<?php echo \Shopwell\Icon::get_svg( 'video' ); ?>
</a>
