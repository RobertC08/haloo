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
	 *  Add the link to compare
	 *
	 * @since 1.0.0
	 * @author Francesco Licandro
	 * @param mixed $product_id The product ID.
	 * @param array $args An array of link arguments.
	 */
	public static function product_compare_button() {
		if ( shortcode_exists( 'wcboost_compare_button' ) ) {
			echo do_shortcode( '[wcboost_compare_button]' );
		}
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
