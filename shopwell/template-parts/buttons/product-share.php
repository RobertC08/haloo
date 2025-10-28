<?php

/**
 * Template part for displaying the product share button
 *
 * @package Shopwell
 */
$icon = 'share-mini';
if ( ! empty( $args ) && ! empty( $args['icon'] ) ) {
	$icon = $args['icon'];
}
$text = esc_html__( 'Share', 'shopwell' );
if ( ! empty( $args ) && ! empty( $args['text'] ) ) {
	$text = $args['text'];
}
?>

<a href="#" class="shopwell-button  shopwell-button--text shopwell-button--product-share" data-toggle="modal" data-target="socials-popup">
	<span class="shopwell-button__icon"><?php echo \Shopwell\Icon::get_svg( $icon ); ?></span>
	<span class="shopwell-button__text "><?php echo esc_html( $text ); ?></span>
</a>
