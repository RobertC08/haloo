<?php
/**
 * Shopwell helper functions and definitions.
 *
 * @package Shopwell
 */

namespace Shopwell\WooCommerce;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shopwell Helper initial
 */
class Helper {

	/**
	 * Price Save
	 *
	 * @param string $regular_price
	 * @param string $sale_price
	 * @return void
	 */
	public static function price_save( $regular_price, $sale_price ) {
		$html = '';

		if ( $regular_price && $sale_price && intval( $regular_price ) > intval( $sale_price ) ) {
			$price_save      = intval( $regular_price ) - intval( $sale_price );
			$text            = '<span class="text">' . esc_html__( 'Save:', 'shopwell' ) . '</span>';
			$sale_percentage = round( ( ( ( $regular_price - $sale_price ) / $regular_price ) * 100 ) );
			$sale_percentage = apply_filters( 'shopwell_sale_percentage', '(' . round( ( ( ( $regular_price - $sale_price ) / $regular_price ) * 100 ) ) . '%' . ')', $sale_percentage );

			$html = '<span class="price__save">' . $text . wc_price( $price_save ) . '<span class="percentage">' . $sale_percentage . '</span></span>';
		}

		return $html;
	}


	/**
	 * Whether the current view is a cart screen (WC cart page, or page with cart block/shortcode).
	 *
	 * @return bool
	 */
	public static function is_cart_page_context() {
		if ( function_exists( 'is_cart' ) && is_cart() ) {
			return true;
		}

		if ( ! function_exists( 'is_singular' ) || ! is_singular() ) {
			return false;
		}

		global $post;
		if ( ! $post instanceof \WP_Post ) {
			return false;
		}

		if ( function_exists( 'has_block' ) && has_block( 'woocommerce/cart', $post ) ) {
			return true;
		}

		if ( function_exists( 'wc_post_content_has_shortcode' ) && wc_post_content_has_shortcode( $post->post_content, 'woocommerce_cart' ) ) {
			return true;
		}

		return false;
	}

	public static function is_cartflows_template() {
		if ( ! class_exists( 'Cartflows_Loader' ) || ! function_exists( '_get_wcf_step_id' ) ) {
			return false;
		}

		$page_template = get_post_meta( _get_wcf_step_id(), '_wp_page_template', true );

		if ( ! $page_template || $page_template == 'default' ) {
			return false;
		}

		return true;
	}
}
