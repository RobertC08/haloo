<?php
/**
 * Template part for displaying the custom html
 *
 * @package Shopwell
 */

if ( empty( \Shopwell\Helper::get_option( 'header_custom_text' ) ) ) {
	return;
}
?>

<div id="header-custom-text" class="header-custom-text">
	<?php echo do_shortcode( wp_kses( \Shopwell\Helper::get_option( 'header_custom_text' ), shopwell_get_allowed_html_tags( 'basic' ) ) ); ?>
</div>
