<?php
/**
 * Hooks of Compare.
 *
 * @package Shopwell
 */

namespace Shopwell\WooCommerce;

use Shopwell\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class of QuickView template.
 */
class Compare {
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
		add_filter( 'wcboost_products_compare_button_template_args', array( $this, 'products_compare_button_template_args' ), 10, 2 );
		add_filter( 'wcboost_products_compare_add_to_compare_fragments', array( $this, 'products_compare_add_to_compare_fragments' ), 10, 1 );
		add_filter( 'wcboost_products_compare_single_add_to_compare_link', array( $this, 'add_to_compare_link' ), 20, 2 );
		add_filter( 'wcboost_products_compare_loop_add_to_compare_link', array( $this, 'add_to_compare_link' ), 20, 2 );
		if ( apply_filters( 'shopwell_change_compare_button_settings', true ) ) {
			add_filter( 'wcboost_products_compare_button_add_text', array( $this, 'compare_button_add_text' ) );
			add_filter( 'wcboost_products_compare_button_remove_text', array( $this, 'compare_button_remove_text' ) );
			add_filter( 'wcboost_products_compare_button_view_text', array( $this, 'compare_button_view_text' ) );
		}
		// Compare button.
		$compare = \WCBoost\ProductsCompare\Frontend::instance();
		remove_action( 'woocommerce_after_add_to_cart_form', array( $compare, 'single_add_to_compare_button' ) );
		remove_action( 'woocommerce_after_shop_loop_item', array( $compare, 'loop_add_to_compare_button' ), 15 );

		add_filter( 'wcboost_products_compare_fields', array( $this, 'products_compare_fields' ) );
		add_action( 'wcboost_products_compare_custom_field', array( $this, 'products_compare_custom_field' ), 20, 3 );

		add_action( 'customize_register', array( $this, 'register' ), 20 );
	}


	/**
	 * Show button compare.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function products_compare_button_template_args( $args, $product ) {
		$args['class'][] = 'shopwell-button shopwell-button--text';

		switch ( get_option( 'wcboost_products_compare_exists_item_button_behaviour', 'remove' ) ) {
			case 'remove':
				$args['class'][] = 'shopwell-button-compare--remove';
				break;

			case 'view':
				$args['class'][] = 'shopwell-button-compare--view';
				break;

			case 'popup':
				$args['class'][] = 'shopwell-button-compare--view';
				break;
		}

		$args['product_title'] = $product->get_title();

		return $args;
	}

	/**
	 * Ajaxify update count compare
	 *
	 * @since 1.0
	 *
	 * @param array $fragments
	 *
	 * @return array
	 */
	public static function products_compare_add_to_compare_fragments( $data ) {
		$compare_counter                                  = intval( \WCBoost\ProductsCompare\Plugin::instance()->list->count_items() );
		$compare_class                                    = $compare_counter == 0 ? ' hidden' : '';
		$data['.header-compare .header-compare__counter'] = '<span class="header-counter header-compare__counter' . $compare_class . '">' . $compare_counter . '</span>';
		$data['.shopwell-mobile-navigation-bar__icon .compare-counter'] = '<span class="counter compare-counter' . $compare_class . '">' . $compare_counter . '</span>';

		return $data;
	}


	/**
	 * Update compare text
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function compare_button_add_text() {
		return esc_html__( 'Compare', 'shopwell' );
	}

	/**
	 * Update compare text
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function compare_button_remove_text() {
		return esc_html__( 'Remove', 'shopwell' );
	}

	/**
	 * Update compare text
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function compare_button_view_text() {
		return esc_html__( 'Browse', 'shopwell' );
	}


	/**
	 * Change compare link
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function add_to_compare_link( $html, $args ) {
		global $product;
		$default = array(
			'add'   => esc_html__( 'Compare', 'shopwell' ),
			'remove'=> esc_html__( 'Remove Compare', 'shopwell' ),
			'view'  => esc_html__( 'Browse Compare', 'shopwell' ),
		);
		$button_text = get_option( 'wcboost_products_compare_button_text', $default );
		
		return sprintf(
			'<a href="%s" data-product_id="%s" data-product_title="%s" class="%s" aria-label="%s" role="button">
				%s
				<span class="wcboost-products-compare-button__text" data-add="%s" data-remove="%s" data-view="%s">%s</span>
			</a>',
			esc_url( isset( $args['url'] ) ? $args['url'] : add_query_arg( array( 'add-to-compare' => $product->get_id() ) ) ),
			esc_attr( isset( $args['product_id'] ) ? $args['product_id'] : $product->get_id() ),
			esc_attr( isset( $args['product_title'] ) ? $args['product_title'] : $product->get_title() ),
			esc_attr( isset( $args['class'] ) ? $args['class'] : 'wcboost-products-compare-button wcboost-products-compare-button--loop shopwell-button shopwell-button--text' ),
			/* translators: %s - product title */
			esc_attr( isset( $args['aria-label'] ) ? $args['aria-label'] : sprintf( __( 'Compare %s', 'shopwell' ), '&ldquo;' . $product->get_title() . '&rdquo;' ) ),
			'<span class="wcboost-products-compare-button__icon">' . \Shopwell\Icon::get_svg( 'compare' ) . '</span>',
			esc_attr( $button_text['add'] ) ?? esc_attr__( 'Add To Compare', 'shopwell' ),
			esc_attr( $button_text['remove'] ) ?? esc_attr__( 'Remove Compare', 'shopwell' ),
			esc_attr( $button_text['view'] ) ?? esc_attr__( 'Browse Compare', 'shopwell' ),
			esc_html( isset( $args['label'] ) ? $args['label'] : __( 'Compare', 'shopwell' ) )
		);
	}

	public function products_compare_fields( $fields ) {
		$attributes = $this->attribute_taxonomies();
		if ( $attributes ) {
			unset( $fields['add-to-cart'] );
			$attributes['add-to-cart'] = '';
			$fields                    = array_merge( $fields, $attributes );
		}

		return $fields;
	}

	/**
	 * Get Woocommerce Attribute Taxonomies
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function attribute_taxonomies() {

		$attributes = array();

		$attribute_taxonomies = wc_get_attribute_taxonomies();
		if ( empty( $attribute_taxonomies ) ) {
			return array();
		}
		foreach ( $attribute_taxonomies as $attribute ) {
			$tax = wc_attribute_taxonomy_name( $attribute->attribute_name );
			if ( taxonomy_exists( $tax ) ) {
				$attributes[ $tax ] = ucfirst( $attribute->attribute_name );
			}
		}

		return $attributes;
	}

	public function products_compare_custom_field( $field, $product, $key ) {
		if ( taxonomy_exists( $field ) ) {
			$attributes = array();
			$terms      = get_the_terms( $product->get_id(), $field );
			if ( ! empty( $terms ) ) {
				foreach ( $terms as $term ) {
					$term         = sanitize_term( $term, $field );
					$attributes[] = $term->name;
				}
			}
			echo implode( ', ', $attributes );
		}
	}
}
