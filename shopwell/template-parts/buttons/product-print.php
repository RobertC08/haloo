<?php

/**
 * Template part for displaying the product print button
 *
 * @package Shopwell
 */
?>

<a href="#" class="shopwell-button  shopwell-button--text shopwell-button--product-print">
	<span class="shopwell-button__icon"><?php echo \Shopwell\Icon::get_svg( 'print' ); ?></span>
	<span class="shopwell-button__text "><?php esc_html_e( 'Print', 'shopwell' ); ?></span>
</a>
