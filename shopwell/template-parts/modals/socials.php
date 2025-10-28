<?php
/**
 * Template part for displaying socials content
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Shopwell
 */
?>
<div id="socials-popup" class="product-socials__popup modal">
	<div class="modal__backdrop"></div>
	<div class="product-socials__content">
		<div class="product-socials__top">
			<div class="product-socials__heading"><?php echo esc_html__( 'Share', 'shopwell' ); ?></div>
			<div class="product-socials__close modal__button-close"><?php echo \Shopwell\Icon::get_svg( 'close' ); ?></div>
		</div>
		<div class="product-socials__share"><?php echo $args; ?></div>
		<div class="product-socials__copylink">
			<div class="product-socials__copylink-heading"><?php echo esc_html__( 'Copy Link', 'shopwell' ); ?></div>
			<form>
				<input class="product-socials__copylink--link shopwell-copylink__link" type="text" value="<?php echo esc_url( get_permalink( get_the_ID() ) ); ?>" readonly="readonly" />
				<button class="product-socials__copylink--button shopwell-copylink__button shopwell-button shopwell-button--bg-color-black"><?php echo esc_html__( 'Copy', 'shopwell' ); ?></button>
			</form>
		</div>
	</div>
</div>
