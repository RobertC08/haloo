<?php
/**
 * Template file for displaying Home mobile
 *
 * @package Shopwell
 */
?>

<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="shopwell-mobile-navigation-bar__icon home-icon">
	<?php echo \Shopwell\Icon::get_svg( 'home' ); ?>
	<em><?php echo esc_html__( 'Home', 'shopwell' ); ?></em>
</a>
