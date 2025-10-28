<?php
/**
 * Badges hooks.
 *
 * @package Shopwell
 */

namespace Shopwell\WooCommerce;

use Shopwell\Helper, Shopwell\WooCommerce;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class of Badges
 */
class Badges {
	/**
	 * Instance
	 *
	 * @var $instance
	 */
	protected static $instance = null;

	/**
	 * Initiator
	 *
	 * @since 1.0.0
	 * @return object
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Instantiate the object.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function __construct() {
	}

	/**
	 * Product badges.
	 */
	public static function badges() {
		global $product;
		$badges        = array();
		$custom_badges = maybe_unserialize( get_post_meta( $product->get_id(), 'custom_badges_text', true ) );
		if ( $custom_badges ) {
			$style               = '';
			$custom_badges_bg    = get_post_meta( $product->get_id(), 'custom_badges_bg', true );
			$custom_badges_color = get_post_meta( $product->get_id(), 'custom_badges_color', true );
			$bg_color            = ! empty( $custom_badges_bg ) ? '--id--badge-custom-bg:' . $custom_badges_bg . ';' : '';
			$color               = ! empty( $custom_badges_color ) ? '--id--badge-custom-color:' . $custom_badges_color . ';' : '';

			if ( $bg_color || $color ) {
				$style = 'style="' . $color . $bg_color . '"';
			}

			$badges['custom'] = '<span class="custom woocommerce-badge"' . $style . '>' . esc_html( $custom_badges ) . '</span>';
		} else {
			$badges = self::get_badges();
		}

		if ( $badges ) {
			printf( '<span class="woocommerce-badges">%s</span>', implode( '', $badges ) );
		}
	}

	/**
	 * Get product badges.
	 *
	 * @return array
	 */
	public static function get_badges( $product = array(), $args = array() ) {
		if ( empty( $product ) ) {
			global $product;
		}

		$args = wp_parse_args(
			$args,
			array(
				'badges_soldout'       => Helper::get_option( 'badges_soldout' ),
				'badges_soldout_text'  => Helper::get_option( 'badges_soldout_text' ),
				'badges_sale'          => Helper::get_option( 'badges_sale' ),
				'badges_sale_text'     => Helper::get_option( 'badges_sale_text' ),
				'badges_new'           => Helper::get_option( 'badges_new' ),
				'badges_new_text'      => Helper::get_option( 'badges_new_text' ),
				'badges_featured'      => Helper::get_option( 'badges_featured' ),
				'badges_featured_text' => Helper::get_option( 'badges_featured_text' ),
				'is_product'           => is_singular( 'product' ),
			)
		);

		$badges = array();

		if ( $args['badges_soldout'] && ! $product->is_in_stock() ) {
			$in_stock = false;

			// Double check if this is a variable product.
			if ( $product->is_type( 'variable' ) ) {
				$variations = $product->get_available_variations();

				foreach ( $variations as $variation ) {
					if ( $variation['is_in_stock'] ) {
						$in_stock = true;
						break;
					}
				}
			}

			if ( ! $in_stock ) {
				$text               = ! empty( $args['badges_soldout_text'] ) ? $args['badges_soldout_text'] : esc_html__( 'Out Of Stock', 'shopwell' );
				$badges['sold-out'] = '<span class="sold-out woocommerce-badge">' . esc_html( $text ) . '</span>';
			}
		} else {
			if ( $product->is_on_sale() && $args['badges_sale'] ) {
				$badges['sale'] = self::sale_flash( $product, $args );
			}

			if ( $args['badges_new'] && in_array( $product->get_id(), WooCommerce\General::shopwell_woocommerce_get_new_product_ids() ) ) {
				$text          = $args['badges_new_text'];
				$text          = empty( $text ) ? esc_html__( 'New', 'shopwell' ) : $text;
				$badges['new'] = '<span class="new woocommerce-badge">' . esc_html( $text ) . '</span>';
			} elseif ( $product->is_featured() && $args['badges_featured'] ) {
				$text               = $args['badges_featured_text'];
				$text               = empty( $text ) ? esc_html__( 'Hot', 'shopwell' ) : $text;
				$badges['featured'] = '<span class="featured woocommerce-badge">' . esc_html( $text ) . '</span>';
			}
		}

		$badges = apply_filters( 'shopwell_product_badges', $badges, $product );

		return $badges;
	}

	/**
	 * Sale badge.
	 *
	 * @param string $output  The sale flash HTML.
	 * @param object $post    The post object.
	 * @param object $product The product object.
	 *
	 * @return string
	 */
	public static function sale_flash( $product, $args ) {
		if ( 'grouped' == $product->get_type() ) {
			return '';
		}
		$output     = '';
		$type       = $args['is_product'] ? 'percent' : Helper::get_option( 'badges_sale_type' );
		$text       = ! empty( $args['badges_sale_text'] ) ? $args['badges_sale_text'] : esc_html__( 'Sale', 'shopwell' );
		$percentage = 0;
		$saved      = 0;
		$sale_date  = get_post_meta( $product->get_id(), '_sale_price_dates_to', true );
		$now        = strtotime( current_time( 'Y-m-d H:i:s' ) );

		$sale = array(
			'weeks'   => esc_html__( 'w', 'shopwell' ),
			'days'    => esc_html__( 'd', 'shopwell' ),
			'hours'   => esc_html__( 'h', 'shopwell' ),
			'minutes' => esc_html__( 'm', 'shopwell' ),
			'seconds' => esc_html__( 's', 'shopwell' ),
		);

		if ( $args['is_product'] ) {
			$sale_date = self::get_date_on_sale_to( $product );
		}

		if ( 'percent' == $type || 'text-price' == $type || 'text-countdown' == $type || false !== strpos( $text, '{%}' ) || false !== strpos( $text, '{$}' ) ) {
			if ( $product->get_type() == 'variable' ) {
				$available_variations = $product->get_available_variations();
				$max_percentage       = 0;
				$max_saved            = 0;
				$total_variations     = count( $available_variations );

				for ( $i = 0; $i < $total_variations; $i++ ) {
					$variation_id        = $available_variations[ $i ]['variation_id'];
					$variable_product    = new \WC_Product_Variation( $variation_id );
					$regular_price       = $variable_product->get_regular_price();
					$sales_price         = $variable_product->get_sale_price();
					$variable_saved      = $regular_price && $sales_price ? ( $regular_price - $sales_price ) : 0;
					$variable_percentage = $regular_price && $sales_price ? round( ( ( ( $regular_price - $sales_price ) / $regular_price ) * 100 ) ) : 0;

					if ( $variable_saved > $max_saved ) {
						$max_saved = $variable_saved;
					}

					if ( $variable_percentage > $max_percentage ) {
						$max_percentage = $variable_percentage;
					}
				}

				$saved      = $max_saved ? $max_saved : $saved;
				$percentage = $max_percentage ? $max_percentage : $percentage;
			} elseif ( $product->get_regular_price() != 0 ) {
				$saved      = $product->get_regular_price() - $product->get_sale_price();
				$percentage = round( ( $saved / $product->get_regular_price() ) * 100 );
			}
		}

		if ( 'percent' == $type ) {
			$output = '<span class="onsale woocommerce-badge">-' . esc_html( $percentage ) . '%</span>';
		} elseif ( 'text-price' == $type || 'text-countdown' == $type ) {
			if ( 'text-countdown' == $type && $sale_date ) {
				$days = $sale_date - $now;

				if ( $days > 0 ) {
					$output = '<span class="onsale woocommerce-badge"><span class="woocommerce-badge--label">' . esc_html__( 'Sale Ends in', 'shopwell' ) . '</span><span class="shopwell-countdown" data-expire="' . esc_attr( $days ) . '" data-text="' . esc_attr( wp_json_encode( $sale ) ) . '"></span>' . '</span>';
				}
			} else {
				$class  = empty( $text ) ? 'no-text' : '';
				$output = '<span class="onsale woocommerce-badge ' . esc_attr( $class ) . '"><span class="woocommerce-badge--label">' . wp_kses_post( $text ) . '</span>' . wc_price( $saved ) . '</span>';
			}
		} else {
			$output = '<span class="onsale woocommerce-badge">' . wp_kses_post( $text ) . '</span>';
		}

		if ( $args['is_product'] && $sale_date ) {
			$days = $sale_date - $now;

			$day_text = date( 'd', $days ) . esc_html__( ' Day', 'shopwell' );
			if ( date( 'd', $days ) > 1 ) {
				$day_text = date( 'd', $days ) . esc_html__( ' Days', 'shopwell' );
			}

			if ( $days > 0 ) {
				$output .= '<span class="woocommerce-badge--text">' . esc_html__( 'Sale Ends in ', 'shopwell' ) . esc_html( $day_text ) . ' ' . self::get_sold() . '</span>';
			}
		}

		return $output;
	}

	/**
	 * Get sold of product deal
	 */
	public static function get_sold() {
		global $product;

		$limit = get_post_meta( $product->get_id(), '_deal_quantity', true );
		$sold  = intval( get_post_meta( $product->get_id(), '_deal_sales_counts', true ) );

		$output = ! empty( $limit ) ? '(' . esc_html( $sold ) . '/' . esc_html( $limit ) . ' ' . esc_html__( 'sold', 'shopwell' ) . ')' : '';

		return $output;
	}

	public static function get_date_on_sale_to( $product, $args = array() ) {
		$sale_date = get_post_meta( $product->get_id(), '_sale_price_dates_to', true );

		if ( ! $product->is_type( 'variable' ) ) {
			return $sale_date;
		}
		if ( ! isset( $args['is_product'] ) || ! $args['is_product'] ) {
			return $sale_date;
		}
		$variation_ids = $product->get_visible_children();

		if ( empty( $variation_ids ) ) {
			return $sale_date;
		}

		$sale_dates = array();
		foreach ( $variation_ids as $variation_id ) {
			$variation = wc_get_product( $variation_id );

			if ( $variation->is_on_sale() ) {
				$date_on_sale_to = $variation->get_date_on_sale_to();

				if ( ! empty( $date_on_sale_to ) ) {
					$sale_dates[] = $date_on_sale_to;
				}
			}
		}

		if ( ! empty( $sale_dates ) ) {
			$sale_date = strtotime( max( $sale_dates ) );
		}

		$sale_date = apply_filters( 'shopwell_product_sale_dates_to', $sale_date );

		return $sale_date;
	}

	/**
	 * Show Sale
	 */
	public static function get_product_sale_countdown() {
		global $product;

		$now         = strtotime( current_time( 'Y-m-d H:i:s' ) );
		$expire_date = self::get_date_on_sale_to( $product );
		$expire_date = ! empty( $expire_date ) ? date_i18n( $expire_date ) : '';
		$expire_date = apply_filters( 'shopwell_product_sale_dates_to', $expire_date );
		$expire      = ! empty( $expire_date ) ? $expire_date - $now : -1;

		$sale = array(
			'weeks'   => esc_html__( 'w', 'shopwell' ),
			'days'    => esc_html__( 'd', 'shopwell' ),
			'hours'   => esc_html__( 'h', 'shopwell' ),
			'minutes' => esc_html__( 'm', 'shopwell' ),
			'seconds' => esc_html__( 's', 'shopwell' ),
		);

		if ( $expire < 0 ) {
			return;
		}

		return sprintf(
			'<div class="shopwell-single-product-sale">
						<div class="shopwell-single-product-sale__text">%s %s</div>
						<div class="shopwell-single-product-sale__countdown">
							<div class="shopwell-single-product-sale__text">%s</div>
							<div class="shopwell-countdown" data-expire="%s" data-text="%s"></div>
						</div>
					</div>',
			esc_html__( 'Limited Time Offer', 'shopwell' ),
			self::get_sold(),
			esc_html__( 'Ends in', 'shopwell' ),
			esc_attr( $expire ),
			esc_attr( wp_json_encode( $sale ) )
		);
	}
}
