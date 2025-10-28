<?php
/**
 * Hooks of QuickView.
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
class QuickView {
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
		if ( ! Helper::get_option( 'product_card_quick_view_button' ) ) {
			return;
		}

		add_action( 'wp_enqueue_scripts', array( $this, 'scripts' ), 20 );
		add_filter( 'shopwell_wp_script_data', array( $this, 'quickview_script_data' ), 10, 3 );

		// Quick view modal.
		add_action( 'wc_ajax_product_quick_view', array( $this, 'quick_view' ) );

		add_action( 'wp_footer', array( $this, 'quick_view_type' ) );

		// Summary
		add_action( 'shopwell_woocommerce_before_product_quickview_summary', 'woocommerce_show_product_images' );

		add_action( 'shopwell_woocommerce_product_quickview_summary', array( '\Shopwell\WooCommerce\Single_Product', 'badges' ), 1 );
		add_action( 'shopwell_woocommerce_product_quickview_summary', array( $this, 'product_title' ), 5 );

		// Meta + Rating
		add_action( 'shopwell_woocommerce_product_quickview_summary', array( $this, 'open_meta_wrapper' ), 5 );
		add_action( 'shopwell_woocommerce_product_quickview_summary', array( $this, 'close_meta_wrapper' ), 30 );

		add_action( 'shopwell_woocommerce_product_quickview_summary', array( '\Shopwell\WooCommerce\Single_Product', 'product_taxonomy' ), 10 );
		add_action( 'shopwell_woocommerce_product_quickview_summary', 'woocommerce_template_single_rating', 15 );

		// Price
		add_action( 'shopwell_woocommerce_product_quickview_summary', array( $this, 'open_product_price_stock' ), 35 );
		add_action( 'shopwell_woocommerce_product_quickview_summary', array( $this, 'close_product_price_stock' ), 40 );

		if ( apply_filters( 'shopwell_quickview_product_show_price', true ) ) {
			add_action( 'shopwell_woocommerce_product_quickview_summary', array( $this, 'template_single_price' ), 36 );
		}

		add_action( 'shopwell_woocommerce_product_quickview_summary', array( $this, 'stock' ), 38 );
		add_filter( 'woocommerce_get_availability', array( '\Shopwell\WooCommerce\Single_Product', 'change_text_stock' ), 1, 2 );

		// Add to cart
		if ( apply_filters( 'shopwell_quick_view_show_atc', true ) ) {
			add_action( 'shopwell_woocommerce_product_quickview_summary', 'woocommerce_template_single_add_to_cart', 50 );
		}

		add_action( 'shopwell_woocommerce_product_quickview_summary', array( '\Shopwell\WooCommerce\Single_Product', 'product_featured_buttons' ), 55 );
	}

	/**
	 * WooCommerce specific scripts & stylesheets.
	 *
	 * @return void
	 */
	public static function scripts() {
		wp_enqueue_script( 'shopwell-countdown', get_template_directory_uri() . '/assets/js/plugins/countdown.js', array(), '1.0' );

		if ( wp_script_is( 'wc-add-to-cart-variation', 'registered' ) ) {
			wp_enqueue_script( 'wc-add-to-cart-variation' );
		}

		if ( wp_script_is( 'flexslider', 'registered' ) ) {
			wp_enqueue_script( 'flexslider' );
		}
	}

	/**
	 * Quickview script data.
	 *
	 * @since 1.0.0
	 *
	 * @param $data
	 *
	 * @return array
	 */
	public function quickview_script_data( $data ) {
		$data['product_quickview_nonce'] = wp_create_nonce( 'shopwell-product-quickview' );

		return $data;
	}

	/**
	 * Product quick view template.
	 *
	 * @return string
	 */
	public static function quick_view() {
		if ( empty( $_POST['product_id'] ) ) {
			wp_send_json_error( esc_html__( 'No product.', 'shopwell' ) );
			exit;
		}

		$post_object = get_post( $_POST['product_id'] );
		if ( ! $post_object || ! in_array( $post_object->post_type, array( 'product', 'product_variation', true ) ) ) {
			wp_send_json_error( esc_html__( 'Invalid product.', 'shopwell' ) );
			exit;
		}

		$GLOBALS['post'] = $post_object;
		wc_setup_product_data( $post_object );
		ob_start();
		wc_get_template(
			'content-product-quickview.php',
			array(
				'post_object' => $post_object,
			)
		);
		wp_reset_postdata();
		wc_setup_product_data( $GLOBALS['post'] );
		$output = ob_get_clean();

		wp_send_json_success( $output );
		exit;
	}

	/**
	 * Quick view type.
	 */
	public static function quick_view_type() {
		if ( 'modal' == Helper::get_option( 'product_card_quickview_behaviour' ) ) {
			get_template_part( 'template-parts/modals/quickview' );
		} else {
			get_template_part( 'template-parts/panels/quickview' );
		}
	}

	/**
	 * Open meta wrapper
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function open_meta_wrapper() {
		echo '<div class="product-meta-wrapper">';
	}

	/**
	 * Close meta wrapper
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function close_meta_wrapper() {
		echo '</div>';
	}

	/**
	 * Product title
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function product_title() {
		the_title( '<h1 class="product_title entry-title"><a href="' . esc_url( get_permalink() ) . '">', '</a></h1>' );
	}

	/**
	 * Open product price stock
	 *
	 * @static
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function open_product_price_stock() {
		echo '<div class="shopwell-price-stock variations-attribute-change">';
	}

	/**
	 * Close product price stock
	 *
	 * @static
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function close_product_price_stock() {
		echo '</div>';
	}

	/**
	 * Change template price.
	 *
	 * @return void
	 */
	public function template_single_price() {
		add_filter( 'woocommerce_format_sale_price', array( $this, 'format_sale_price' ), 10, 3 );
		woocommerce_template_single_price();
		remove_filter( 'woocommerce_format_sale_price', array( $this, 'format_sale_price' ), 10, 3 );
	}

	/**
	 * Format a sale price for display.
	 *
	 * @since  3.0.0
	 * @param  string $regular_price Regular price.
	 * @param  string $sale_price    Sale price.
	 * @return string
	 */
	public function format_sale_price( $price, $regular_price, $sale_price ) {
		global $product;
		$price = '<ins>' . ( is_numeric( $sale_price ) ? wc_price( $sale_price ) : $sale_price ) . '</ins> <del aria-hidden="true">' . ( is_numeric( $regular_price ) ? wc_price( $regular_price ) : $regular_price ) . '</del>';
		if ( $product->is_type( 'variable' ) ) {
			$sale_price    = $product->get_variation_sale_price( 'min', true );
			$regular_price = $product->get_variation_regular_price( 'max', true );
		}
		$price .= \Shopwell\WooCommerce\Helper::price_save( $regular_price, $sale_price );

		return $price;
	}

	/**
	 * Show stock
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function stock() {
		global $product;

		if ( $product->is_type( 'grouped' ) ) {
			return;
		}

		echo wc_get_stock_html( $product );
	}

	/**
	 * Get Quick view icon
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function quick_view_icon() {
		$this->quick_view_button_html( true );
	}

	/**
	 * Get Quick view button
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function quick_view_button() {
		$this->quick_view_button_html();
	}

	/**
	 * Get Quick view icon
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function quick_view_button_html( $icon = false ) {
		global $product;

		$classes = Helper::get_option( 'product_card_add_to_cart_button' ) ? 'shopwell-button--ghost' : 'shopwell-button--base';
		$classes = $icon ? 'shopwell-button--text' : $classes;

		printf(
			'<a href="%s" class="button shopwell-button shopwell-button--quickview %s" data-toggle="%s" data-target="%s" data-id="%d" data-text="%s" rel="nofollow">
				<span>%s</span>
			</a>',
			is_customize_preview() ? '#' : esc_url( get_permalink() ),
			esc_attr( $classes ),
			'modal' == Helper::get_option( 'product_card_quickview_behaviour' ) ? 'modal' : 'off-canvas',
			'modal' == Helper::get_option( 'product_card_quickview_behaviour' ) ? 'quick-view-modal' : 'quick-view-panel',
			esc_attr( $product->get_id() ),
			esc_attr__( 'Quick View', 'shopwell' ),
			$icon ? '<span class="shopwell-button__icon">' . \Shopwell\Icon::get_svg( 'eye' ) . '</span>' : esc_html__( 'Quick View', 'shopwell' )
		);
	}
}
