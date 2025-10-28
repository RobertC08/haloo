<?php
/**
 * WooCommerce product attribute template hooks.
 *
 * @package Shopwell
 */

namespace Shopwell\WooCommerce;

use Shopwell\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class of Product_Attribute
 */
class Product_Attribute {
	/**
	 * Instance
	 *
	 * @var $instance
	 */
	protected static $instance = null;

	/**
	 * Product Attribute Types
	 *
	 * @var $product_attr_types
	 */
	protected static $product_attr_types = null;

	/**
	 * Product Attribute
	 *
	 * @var $product_attribute
	 */
	protected static $product_attribute = null;


	/**
	 * Product Attribute Number
	 *
	 * @var $product_attribute_number
	 */
	protected static $product_attribute_number = null;


	/**
	 * Product Card Hover
	 *
	 * @var $product_card_hover
	 */
	protected static $product_card_hover = null;

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
		add_action( 'wp', array( $this, 'actions' ) );
	}

	/**
	 * Product Card layout
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function actions() {
		$loop_layout = apply_filters( 'shopwell_product_card_layout', Helper::get_option( 'product_card_layout' ) );

		switch ( $loop_layout ) {
			// Layout 1
			case '1':
			case '3':
			case '4':
				add_action( 'woocommerce_before_shop_loop_item_title', array( $this, 'product_attribute' ), 50 );
				break;

			// Layout 2
			case '2':
				add_action( 'woocommerce_after_shop_loop_item_title', array( $this, 'product_attribute' ), 50 );
				break;

			default:
				break;
		}
	}

	/**
	 * Display product attribute
	 *
	 * @since 1.0
	 */
	public static function product_attribute() {
		global $product;

		if ( empty( self::$product_attribute ) ) {
			self::$product_attribute = Helper::get_option( 'product_card_attribute' );
		}

		if ( 'none' == self::$product_attribute ) {
			return;
		}

		if ( empty( self::$product_attr_types ) ) {
			self::$product_attr_types = (array) Helper::get_option( 'product_card_attribute_in' );
		}

		if ( empty( self::$product_attr_types ) ) {
			return;
		}

		if ( ! in_array( $product->get_type(), self::$product_attr_types ) ) {
			if ( Helper::get_option( 'product_card_layout' ) == '2' ) {
				return;
			} else {
				echo '<div class="product-variation-items"></div>';
				return;
			}
		}

		if ( empty( self::$product_attribute_number ) ) {
			$attrs_number                   = get_post_meta( $product->get_id(), 'shopwell_product_attribute_number', true );
			self::$product_attribute_number = empty( $attrs_number ) ? Helper::get_option( 'product_card_attribute_number' ) : $attrs_number;
		}

		if ( empty( self::$product_card_hover ) ) {
			self::$product_card_hover = Helper::get_option( 'product_card_hover' );
		}

		$attribute_taxonomy = maybe_unserialize( get_post_meta( $product->get_id(), 'shopwell_product_attribute', true ) );
		$attribute_taxonomy = empty( $attribute_taxonomy ) ? 'pa_' . sanitize_title( self::$product_attribute ) : $attribute_taxonomy;
		if ( $attribute_taxonomy == 'none' ) {
			return;
		}

		$product_attributes = $product->get_attributes();
		if ( ! $product_attributes ) {
			if ( Helper::get_option( 'product_card_layout' ) == '2' ) {
				return;
			} else {
				echo '<div class="product-variation-items"></div>';
				return;
			}
		}
		$product_attribute = isset( $product_attributes[ $attribute_taxonomy ] ) ? $product_attributes[ $attribute_taxonomy ] : '';
		if ( ! $product_attribute ) {
			if ( Helper::get_option( 'product_card_layout' ) == '2' ) {
				return;
			} else {
				echo '<div class="product-variation-items"></div>';
				return;
			}
		}

		$output         = $class_attr = '';
		$swatches_args  = array();
		$variation_args = array();
		if ( function_exists( 'wcboost_variation_swatches' ) ) {
			$swatches_args = self::get_product_data( $product_attribute['name'], $product->get_id() );
		}

		$swatches_args['taxonomy'] = $attribute_taxonomy;
		if ( ! $product_attribute['is_taxonomy'] ) {
			$attr_options = $product_attribute->get_options();
			if ( $attr_options ) {
				$output = sprintf( '<span class="product-variation-attrs">%s</span>', implode( ', ', $attr_options ) );
			}
		} elseif ( $product->get_type() ) {
			$count_attr           = ! empty( $product_attributes[ $attribute_taxonomy ]['options'] ) ? count( $product_attributes[ $attribute_taxonomy ]['options'] ) : 0;
			$attribute_label_name = ( $count_attr > 1 ) ? wc_attribute_label( $attribute_taxonomy ) . 's' : wc_attribute_label( $attribute_taxonomy );

			$output_text = ( $count_attr > 0 ) ? sprintf(
				'<div class="product-variation-items--text">%d %s</div>',
				intval( $count_attr ),
				esc_html( $attribute_label_name )
			) : '';

			if ( isset( $product_attribute['is_variation'] ) && $product_attribute['is_variation'] ) {
				$class_attr           = 'product-variation-items--hover';
				$available_variations = $product->get_type() == 'variable' ? $product->get_available_variations() : '';
				$index                = 0;
				$variation_counts     = $available_variations ? count( $available_variations ) : 0;
				if ( $available_variations ) {
					$variations_exist = array();
					foreach ( $available_variations as $variation ) {
						if ( ! $variation['attributes'] ) {
							continue;
						}

						$v_attribute = $variation['attributes'];

						if ( ! isset( $v_attribute[ 'attribute_' . $attribute_taxonomy ] ) ) {
							continue;
						}
						$v_attribute_name                = $v_attribute[ 'attribute_' . $attribute_taxonomy ];
						$swatches_args['attribute_name'] = sanitize_title( $v_attribute_name );
						if ( empty( $swatches_args['attribute_name'] ) ) {
							continue;
						}

						if ( in_array( $v_attribute_name, $variations_exist ) ) {
							continue;
						}
						$variations_exist[] = $v_attribute[ 'attribute_' . $attribute_taxonomy ];

						if ( $index >= self::$product_attribute_number ) {
							$count_more = $variation_counts - $index;
							$output    .= sprintf( '<a href="%s" class="product-variation-item-more">+%s</a>', esc_url( $product->get_permalink() ), $count_more );
							break;
						}

						++$index;

						if ( $attachment_id = $variation['image_id'] ) {
							$gallery_thumbnail            = wc_get_image_size( 'woocommerce_thumbnail' );
							$gallery_thumbnail_size       = apply_filters( 'woocommerce_gallery_thumbnail_size', array( $gallery_thumbnail['width'], $gallery_thumbnail['height'] ) );
							$thumbnail                    = wp_get_attachment_image_src( $attachment_id, $gallery_thumbnail_size );
							$variation_args['img_src']    = $thumbnail ? $thumbnail[0] : '';
							$variation_args['img_srcset'] = function_exists( 'wp_get_attachment_image_srcset' ) ? wp_get_attachment_image_srcset( $attachment_id, $gallery_thumbnail_size ) : '';
							if ( self::$product_card_hover == 'zoom' ) {
								$thumbnail_zoom                 = wp_get_attachment_image_src( $attachment_id, 'full' );
								$variation_args['img_zoom_src'] = $thumbnail_zoom ? $thumbnail_zoom[0] : '';
							}
						}
						$variation_args['price'] = isset( $variation['price_html'] ) && $variation['price_html'] ? $variation['price_html'] : '';

						$output .= self::swatch_html( $swatches_args, $variation_args );
					}

					if ( ! empty( $output ) ) {
						$output = $output_text . '<div class="product-variation-items--item">' . $output . '</div>';
					} else {
						$output     = $output_text;
						$class_attr = '';
					}
				}
			} else {
				$output = $output_text;
			}
		} else {
			$post_terms = wp_get_post_terms( $product->get_id(), $product_attribute['name'] );
			if ( $post_terms ) {
				foreach ( $post_terms as $term ) {
					if ( is_wp_error( $term ) ) {
						continue;
					}
					$swatches_args['attribute_name'] = $term->name;
					$swatches_args['term']           = $term;
					$output                         .= self::swatch_html( $swatches_args, $variation_args );
				}
			}
		}

		if ( $output ) {
			printf( '<div class="product-variation-items %s">%s</div>', esc_attr( $class_attr ), $output );
		}
	}

	/**
	 * Print HTML of a single swatch
	 *
	 * @since  1.0.0
	 * @return string
	 */
	public static function swatch_html( $swatches_args, $variation_args ) {
		$html           = '';
		$term           = isset( $swatches_args['term'] ) && $swatches_args['term'] ? $swatches_args['term'] : get_term_by( 'slug', $swatches_args['attribute_name'], $swatches_args['taxonomy'] );
		$key            = is_object( $term ) ? $term->term_id : sanitize_title( $term );
		$attribute_name = is_object( $term ) ? $term->name : $swatches_args['attribute_name'];
		$type           = isset( $swatches_args['type'] ) ? $swatches_args['type'] : 'label';
		$type           = in_array( $type, array( 'select', 'button' ) ) ? 'label' : $type;
		if ( isset( $swatches_args['attributes'][ $key ] ) && isset( $swatches_args['attributes'][ $key ][ $type ] ) ) {
			$swatches_value = $swatches_args['attributes'][ $key ][ $type ];
		} else {
			$swatches_value = is_object( $term ) ? self::get_attribute_swatches( $term->term_id, $type ) : '';
		}
		$css_class = $variation_attrs = '';
		if ( $variation_args ) {
			$variation_json  = wp_json_encode( $variation_args );
			$variation_attrs = function_exists( 'wc_esc_json' ) ? wc_esc_json( $variation_json ) : _wp_specialchars( $variation_json, ENT_QUOTES, 'UTF-8', true );
			$variation_attrs = $variation_args ? sprintf( 'data-product_variations="%s"', $variation_attrs ) : '';
			$css_class       = $variation_args ? 'product-variation-item--attrs' : '';
		}

		switch ( $type ) {
			case 'color':
				$html = sprintf(
					'<span class="product-variation-item product-variation-item--color %s" %s data-text="%s"><span class="product-variation-item__color" style="background-color:%s;"></span></span>',
					esc_attr( $css_class ),
					$variation_attrs,
					esc_attr( $attribute_name ),
					esc_attr( $swatches_value )
				);
				break;

			case 'image':
				if ( $swatches_value ) {
					$gallery_thumbnail      = wc_get_image_size( 'gallery_thumbnail' );
					$gallery_thumbnail_size = apply_filters( 'woocommerce_gallery_thumbnail_size', array( $gallery_thumbnail['width'], $gallery_thumbnail['height'] ) );
					$image                  = wp_get_attachment_image( $swatches_value, $gallery_thumbnail_size );
					$html                   = sprintf(
						'<span class="product-variation-item product-variation-item--image %s" %s data-text="%s">%s</span>',
						esc_attr( $css_class ),
						$variation_attrs,
						esc_attr( $attribute_name ),
						$image
					);
				}

				break;

			default:
				$label = $swatches_value ? $swatches_value : $attribute_name;

				$html = sprintf(
					'<span class="product-variation-item product-variation-item--label %s" %s data-text="%s">%s</span>',
					esc_attr( $css_class ),
					$variation_attrs,
					esc_attr( $attribute_name ),
					esc_html( $label )
				);
				break;

		}

		return $html;
	}

	public static function get_attribute_swatches( $term_id, $type = 'color' ) {
		if ( class_exists( '\WCBoost\VariationSwatches\Admin\Term_Meta' ) ) {
			$data = \WCBoost\VariationSwatches\Admin\Term_Meta::instance()->get_meta( $term_id, $type );
		} else {
			$data = get_term_meta( $term_id, $type, true );
		}

		return $data;
	}

	/**
	 * Get product type
	 *
	 * @since 1.0.0
	 *
	 * @param string $attribute
	 *
	 * @return object
	 */
	protected static function get_product_data( $attribute_name, $product_id ) {
		if ( class_exists( '\WCBoost\VariationSwatches\Admin\Product_Data' ) ) {
			$swatches_meta = \WCBoost\VariationSwatches\Admin\Product_Data::instance()->get_meta( $product_id );
			$attribute_key = sanitize_title( $attribute_name );
			$swatches_args = array();
			if ( $swatches_meta && ! empty( $swatches_meta[ $attribute_key ] ) ) {
				$swatches_args = array(
					'type'       => $swatches_meta[ $attribute_key ]['type'],
					'attributes' => $swatches_meta[ $attribute_key ]['swatches'],
				);
			}

			if ( ! $swatches_args || ( isset( $swatches_args['type'] ) && ! $swatches_args['type'] ) ) {
				$attribute_slug     = wc_attribute_taxonomy_slug( $attribute_name );
				$taxonomies         = wc_get_attribute_taxonomies();
				$attribute_taxonomy = wp_list_filter( $taxonomies, array( 'attribute_name' => $attribute_slug ) );
				$attribute_taxonomy = ! empty( $attribute_taxonomy ) ? array_shift( $attribute_taxonomy ) : null;

				if ( $attribute_taxonomy ) {
					$swatches_args = array(
						'type' => $attribute_taxonomy->attribute_type,
					);
				}
			}

			return $swatches_args;
		}
	}
}
