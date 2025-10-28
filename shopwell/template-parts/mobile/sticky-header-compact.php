<?php

/**
 * Template part for displaying the sticky header compact for mobile
 *
 * @package Shopwell
 */
global $product;
?>
<div class="product-sticky-header">
	<a href="<?php echo esc_url( get_home_url() ); ?>" class="shopwell-button shopwell-button--text shopwell-button--history">
		<?php echo \Shopwell\Icon::get_svg( 'left' ); ?>
	</a>
	<div class="product-info">
		<span class="product-title"><?php the_title(); ?></span>
		<span class="product-price">
		<?php echo wp_kses_post( $product->get_price_html() ); ?>
		</span>
	</div>
	<div class="product-buttons">
		<?php \Shopwell\WooCommerce\Single_Product::share_button( 'share' ); ?>
		<a href="#" class="shopwell-button shopwell-button--text shopwell-button--product-more" data-toggle="modal" data-target="product-more-popup">
			<?php echo \Shopwell\Icon::get_svg( 'more' ); ?>
		</a>
	</div>
</div>
