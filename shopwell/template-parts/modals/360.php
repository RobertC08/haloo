<?php
/**
 * Template part for displaying 360 content
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Shopwell
 */

$tem = ! empty( $args ) ? $args[0] : '';
$id  = $tem == 'elementor' ? '' : 'id=product-degree-pswp';
?>

<div <?php echo esc_attr( $id ); ?> class="product-degree-pswp modal">
	<div class="modal__backdrop"></div>
	<div class="shopwell-product-gallery-degree">
		<div class="modal__button-close"><?php echo \Shopwell\Icon::get_svg( 'close' ); ?></div>
		<div class="shopwell-gallery-degree__nav-bar">
			<a href="#" class="nav-bar__prev"><?php echo \Shopwell\Icon::get_svg( 'left' ); ?></a>
			<a href="#" class="nav-bar__run"><?php echo \Shopwell\Icon::get_svg( 'video', '', array( 'class' => 'play' ) ); ?> <?php echo \Shopwell\Icon::get_svg( 'pause', '', array( 'class' => 'pause' ) ); ?></a>
			<a href="#" class="nav-bar__next"><?php echo \Shopwell\Icon::get_svg( 'right' ); ?></a>
		</div>
		<div class="shopwell-gallery-degree__spinner"></div>
		<ul class="product-degree__images"></ul>
	</div>
</div>
