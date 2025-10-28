<?php
/**
 * Template file for displaying menu mobile
 *
 * @package Shopwell
 */
?>

<a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>" class="shopwell-mobile-navigation-bar__icon menu-icon">
	<?php echo \Shopwell\Icon::get_svg( 'categories-v2' ); ?>
	<em><?php echo esc_html__( 'Shop', 'shopwell' ); ?></em>
</a>
