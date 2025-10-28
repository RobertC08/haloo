<?php

/**
 * Template part for displaying the header compact for mobile
 *
 * @package Shopwell
 */

?>
<div class="product-header-main">
	<a href="<?php echo esc_url( get_home_url() ); ?>" class="shopwell-button  shopwell-button--text shopwell-button--history">
		<span class="shopwell-button__icon"><?php echo \Shopwell\Icon::get_svg( 'left' ); ?></span>
	</a>
	<?php \Shopwell\WooCommerce\Single_Product::share_button( 'share' ); ?>
</div>
