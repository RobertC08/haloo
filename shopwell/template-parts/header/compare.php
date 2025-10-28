<?php

/**
 * Template part for displaying the compare
 *
 * @package Shopwell
 */

use Shopwell\Helper;

if ( ! function_exists( 'WC' ) ) {
	return;
}

if ( ! class_exists( '\WCBoost\ProductsCompare\Plugin' ) ) {
	return;
}

$compare_display    = isset( $args['compare_display'] ) ? $args['compare_display'] : 'icon';
$compare_text       = isset( $args['compare_text'] ) && ! empty( $args['compare_text'] ) ? $args['compare_text'] : '';
$compare_classes    = isset( $args['compare_classes'] ) ? $args['compare_classes'] : '';
$compare_text_class = isset( $args['compare_text_class'] ) ? $args['compare_text_class'] : '';

?>
<div class="header-compare">
	<a href="<?php echo esc_url( wc_get_page_permalink( 'compare' ) ); ?>" class="shopwell-button wcboost-products-compare--button <?php echo esc_attr( $compare_classes ); ?>">
		<span class="shopwell-button__icon"><?php echo \Shopwell\Icon::get_svg( 'compare' ); ?></span>
		<span class="shopwell-button__text <?php echo esc_attr( $compare_text_class ); ?>"><?php echo esc_html( $compare_text ); ?></span>
		<?php echo Helper::compare_counter(); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</a>
</div>
