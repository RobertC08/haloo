<?php
/**
 * Template part for displaying the compare icon.
 *
 * @package Shopwell
 */

use Shopwell\Helper;

if ( ! function_exists( 'WC' ) ) {
	return;
}

$link = wc_get_page_permalink( 'compare' );

if ( Helper::get_option( 'header_compare_link' ) ) {
	$link = Helper::get_option( 'header_compare_link' );
}

?>

<a class="shopwell-mobile-navigation-bar__icon compare-icon" href="<?php echo esc_url( $link ); ?>">
	<span>
		<?php echo \Shopwell\Icon::get_svg( 'compare' ); ?>
		<span class="counter compare-counter"><?php echo intval( \WCBoost\ProductsCompare\Plugin::instance()->list->count_items() ); ?></span>
	</span>
	<em><?php echo esc_html__( 'Compare', 'shopwell' ); ?></em>
</a>
