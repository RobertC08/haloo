<?php
/**
 * Single Product hooks.
 *
 * @package Shopwell
 */

namespace Shopwell\WooCommerce;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Shopwell\Helper;

/**
 * Class of Single Product
 */
class Single_Product {
	/**
	 * Instance
	 *
	 * @var $instance
	 */
	protected static $instance = null;

	/**
	 * Tabs unset
	 *
	 * @var $unset_tabs
	 */
	protected static $unset_tabs = array();

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
		add_action( 'wp_enqueue_scripts', array( $this, 'scripts' ), 20 );
		add_filter( 'shopwell_wp_script_data', array( $this, 'single_product_script_data' ), 10, 3 );
		add_filter( 'body_class', array( $this, 'body_classes' ) );
		add_filter( 'post_class', array( $this, 'product_class' ), 10, 3 );

		// Add html to footer
		add_action( 'wp_footer', array( $this, 'modal_360' ) );
		add_action( 'wp_footer', array( $this, 'modal_socials' ) );
		add_action( 'wp_footer', array( $this, 'modal_product_more' ) );

		// Page Header
		add_filter( 'shopwell_get_page_header_elements', array( $this, 'page_header_elements' ) );

		// Edit breadcrum.
		remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );

		// Add Social
		add_filter( 'shopwell_share_link_text', '__return_false' );

		// Gallery summary wrapper
		add_action( 'woocommerce_before_single_product_summary', array( $this, 'open_gallery_summary_wrapper' ), 1 );
		add_action( 'woocommerce_after_single_product_summary', array( $this, 'close_gallery_summary_wrapper' ), 2 );

		// Gallery Content
		add_action( 'woocommerce_before_single_product_summary', array( $this, 'open_shopwell_product_gallery' ), 19 );
		add_action( 'woocommerce_before_single_product_summary', array( $this, 'close_shopwell_product_gallery' ), 21 );

		// open product features buttons
		add_action( 'woocommerce_before_single_product_summary', array( $this, 'open_product_buttons' ), 20 );
		// Product 360 button
		add_action( 'woocommerce_before_single_product_summary', array( $this, 'product_360_button' ), 20 );

		// Product Video button
		add_action( 'woocommerce_before_single_product_summary', array( $this, 'product_video_button' ), 20 );

		// Product Lightbox button
		add_action( 'woocommerce_before_single_product_summary', array( $this, 'product_lightbox_button' ), 20 );

		// close product features buttons
		add_action( 'woocommerce_before_single_product_summary', array( $this, 'close_product_buttons' ), 20 );

		// Share button
		if ( Helper::get_option( 'mobile_product_header' ) != 'compact' ) {
			add_action( 'woocommerce_before_single_product_summary', array( $this, 'share_button' ), 20 );
		}
		// Featured Buttons
		add_action( 'woocommerce_before_single_product_summary', array( $this, 'product_featured_buttons' ), 20 );

		if ( Helper::get_option( 'mobile_product_header' ) == 'compact' && ! empty( Helper::get_option( 'mobile_product_gallery_fixed' ) ) ) {
			add_action( 'woocommerce_before_single_product_summary', array( $this, 'product_gallery_fixed_spacing' ), 20 );
		}

		// Change the product thumbnails columns
		add_filter( 'woocommerce_product_thumbnails_columns', array( $this, 'product_thumbnails_columns' ) );
		add_filter( 'woocommerce_single_product_carousel_options', array( $this, 'product_thumbnails_carousel_options' ), 10 );

		// Replace the default sale flash.
		remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash' );

		// Change price
		add_action( 'woocommerce_single_product_summary', array( $this, 'open_product_price_stock' ), 11 );
		add_action( 'woocommerce_single_product_summary', array( $this, 'close_product_price_stock' ), 19 );

		// Change Text In Stock
		add_filter( 'woocommerce_get_availability', array( $this, 'change_text_stock' ), 1, 2 );

		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );

		if ( apply_filters( 'shopwell_product_show_price', true ) ) {
			add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 12 );
		}

		// Remove excerpt
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );

		// Featured button
		add_action( 'woocommerce_single_product_summary', array( $this, 'product_featured_buttons' ), 30 );

		// Change grouped product columns
		add_filter( 'woocommerce_grouped_product_columns', array( $this, 'grouped_product_columns' ) );

		// Upsells Products
		if ( ! intval( Helper::get_option( 'upsells_products' ) ) ) {
			remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15 );
		}
		add_filter( 'woocommerce_product_upsells_products_heading', array( $this, 'upsells_title' ) );
		add_filter( 'woocommerce_upsells_columns', array( $this, 'upsells_columns' ) );
		add_filter( 'woocommerce_upsells_total', array( $this, 'upsells_total' ) );

		$this->product_layout();

		// Add data sale date
		add_action( 'woocommerce_single_variation', array( $this, 'data_badges' ) );

		add_action( 'template_redirect', array( $this, 'template_hooks_wishlist' ) );

		// Mobile Header
		if ( Helper::get_option( 'mobile_product_header' ) == 'compact' ) {
			add_action( 'woocommerce_before_single_product_summary', array( $this, 'mobile_header' ), 0 );
		}

		if ( empty( Helper::get_option( 'product_single_brands' ) ) && class_exists( 'WC_Brands' ) ) {
			$global_brand = $GLOBALS['WC_Brands'];
			remove_action( 'woocommerce_product_meta_end', array( $global_brand, 'show_brand' ) );
		}
	}

	/**
	 *
	 * Template hooks wishlist
	 */
	public function template_hooks_wishlist() {
		if ( class_exists( '\WCBoost\Wishlist\Frontend' ) && method_exists( '\WCBoost\Wishlist\Frontend', 'single_add_to_wishlist_button' ) ) {
			remove_action( 'woocommerce_before_single_product', array( \WCBoost\Wishlist\Frontend::instance(), 'display_wishlist_button' ) );
		}
	}

	/**
	 * WooCommerce specific scripts & stylesheets.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function scripts() {
		wp_enqueue_script(
			'shopwell-single-product',
			get_template_directory_uri() . '/assets/js/woocommerce/single-product.js',
			array(
				'jquery',
			),
			'20250105',
			array( 'strategy' => 'defer' )
		);

		wp_enqueue_script( 'shopwell-countdown', get_template_directory_uri() . '/assets/js/plugins/countdown.js', array(), '1.0' );
		wp_register_script( 'threesixty', get_template_directory_uri() . '/assets/js/plugins/threesixty.min.js', array(), '2.0.5', true );

		wp_enqueue_style( 'magnific', get_template_directory_uri() . '/assets/css/magnific-popup.css', array(), '1.0' );
		wp_enqueue_script( 'magnific', get_template_directory_uri() . '/assets/js/plugins/jquery.magnific-popup.js', array(), '1.0' );

		if ( ! empty( $this->check_360() ) ) {
			wp_enqueue_script( 'threesixty' );
		}
	}

	/**
	 * Single product script data.
	 *
	 * @since 1.0.0
	 *
	 * @param $data
	 *
	 * @return array
	 */
	public function single_product_script_data( $data ) {
		$data['product_layout']         = Helper::get_option( 'product_layout' );
		$data['product_image_zoom']     = Helper::get_option( 'product_image_zoom' );
		$data['product_image_lightbox'] = Helper::get_option( 'product_image_lightbox' );

		if ( ! empty( $product_images_dg = $this->check_360() ) ) {
			$data['product_degree'] = $product_images_dg;
		}

		return $data;
	}

	/**
	 * Adds custom classes to the array of body classes.
	 *
	 * @since 1.0.0
	 *
	 * @param array $classes Classes for the body element.
	 *
	 * @return array
	 */
	public function body_classes( $classes ) {
		if ( Helper::get_option( 'mobile_product_header' ) == 'compact' ) {
			$classes[] = 'mobile-header-compact';

			if ( ! empty( Helper::get_option( 'mobile_product_gallery_fixed' ) ) ) {
				$classes[] = 'mobile-fixed-product-gallery';
			}
		}

		return $classes;
	}

	/**
	 * Adds classes to products
	 *
	 * @since 1.0.0
	 *
	 * @param string $class Post class.
	 *
	 * @return array
	 */
	public function product_class( $classes ) {
		if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
			return $classes;
		}

		$classes[] = 'layout-' . Helper::get_option( 'product_layout' );

		if ( in_array( Helper::get_option( 'product_layout' ), array( '3', '5', '6' ) ) ) {
			$classes[] = 'product-wc-tabs-dropdown';
		}

		if ( Helper::get_option( 'product_add_to_cart_ajax' ) ) {
			$classes[] = 'product-add-to-cart-ajax';
		}

		return $classes;
	}

	/**
	 * Product card layout
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function product_layout() {
		$product_layout = Helper::get_option( 'product_layout' );

		switch ( $product_layout ) {
			// Layout 1
			case '1':
				// Add wrapper breadcrumb social
				add_action( 'woocommerce_before_main_content', array( $this, 'open_breadcrumb_social_wrapper' ), 19 );
				add_action( 'woocommerce_before_main_content', array( $this, 'close_breadcrumb_social_wrapper' ), 29 );

				add_action( 'woocommerce_before_main_content', array( \Shopwell\Theme::instance()->get( 'breadcrumb' ), 'breadcrumb' ), 20, 0 );

				add_action( 'woocommerce_before_main_content', array( $this, 'product_quick_links' ), 25 );

				add_action( 'woocommerce_single_product_summary', array( $this, 'badges' ), 1 );

				// Add meta wrapper
				add_action( 'woocommerce_single_product_summary', array( $this, 'open_meta_wrapper' ), 5 );
				add_action( 'woocommerce_single_product_summary', array( $this, 'close_meta_wrapper' ), 10 );

				// Add in shopwell product meta wrapper
				add_action( 'woocommerce_single_product_summary', array( $this, 'product_taxonomy' ), 6 );
				add_action( 'woocommerce_single_product_summary', array( $this, 'product_sku' ), 7 );

				add_action( 'woocommerce_single_product_summary', array( $this, 'stock' ), 18 );

				add_action( 'woocommerce_single_product_summary', array( $this, 'short_description' ), 20 );

				add_action( 'woocommerce_single_product_summary', array( $this, 'product_extra_content' ), 60 );

				break;

			// Layout 2
			case '2':
				// Add wrapper breadcrumb social
				add_action( 'woocommerce_before_main_content', array( $this, 'open_breadcrumb_social_wrapper' ), 19 );
				add_action( 'woocommerce_before_main_content', array( $this, 'close_breadcrumb_social_wrapper' ), 29 );

				add_action( 'woocommerce_before_main_content', array( \Shopwell\Theme::instance()->get( 'breadcrumb' ), 'breadcrumb' ), 20, 0 );

				add_action( 'woocommerce_before_main_content', array( $this, 'product_quick_links' ), 25 );

				// Add meta wrapper
				add_action( 'woocommerce_single_product_summary', array( $this, 'open_meta_wrapper' ), 5 );
				add_action( 'woocommerce_single_product_summary', array( $this, 'close_meta_wrapper' ), 10 );

				add_action( 'woocommerce_single_product_summary', array( $this, 'badges' ), 1 );

				// Add in shopwell product meta wrapper
				add_action( 'woocommerce_single_product_summary', array( $this, 'product_taxonomy' ), 6 );
				add_action( 'woocommerce_single_product_summary', array( $this, 'product_sku' ), 7 );

				add_action( 'woocommerce_single_product_summary', array( $this, 'stock' ), 18 );

				add_action( 'woocommerce_single_product_summary', array( $this, 'short_description' ), 20 );

				add_action( 'woocommerce_single_product_summary', array( $this, 'product_extra_content' ), 60 );

				add_action( 'shopwell_after_sticky_add_to_cart__product_tabs', array( $this, 'shopwell_sticky_add_to_cart__review_tab' ), 10 );

				add_filter( 'woocommerce_product_tabs', array( $this, 'unset_review_tab' ), 98 );
				add_action( 'woocommerce_after_single_product_summary', array( $this, 'show_reviews' ), 14 );

				remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );
				add_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 2 );

				remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15 );
				add_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 20 );
				break;

			// Layout 3
			case '3':
				// Add wrapper breadcrumb social
				add_action( 'woocommerce_before_main_content', array( $this, 'open_breadcrumb_social_wrapper' ), 19 );
				add_action( 'woocommerce_before_main_content', array( $this, 'close_breadcrumb_social_wrapper' ), 29 );

				add_action( 'woocommerce_before_main_content', array( \Shopwell\Theme::instance()->get( 'breadcrumb' ), 'breadcrumb' ), 20, 0 );

				// Social
				add_action( 'woocommerce_before_main_content', array( $this, 'share_icons' ), 25 );

				add_action( 'woocommerce_single_product_summary', array( $this, 'badges' ), 1 );

				add_action( 'woocommerce_single_product_summary', array( $this, 'short_description' ), 20 );

				add_action( 'woocommerce_single_product_summary', array( $this, 'product_extra_content' ), 60 );

				add_action( 'woocommerce_before_single_product_summary', array( $this, 'open_summary_wrapper' ), 5 );
				add_action( 'woocommerce_before_single_product_summary', array( $this, 'close_summary_wrapper' ), 21 );

				remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5 );
				add_action( 'woocommerce_before_single_product_summary', 'woocommerce_template_single_title', 6 );

				// Add meta wrapper
				add_action( 'woocommerce_before_single_product_summary', array( $this, 'open_meta_wrapper' ), 7 );
				add_action( 'woocommerce_before_single_product_summary', array( $this, 'close_meta_wrapper' ), 18 );

				// Add in shopwell product meta wrapper
				add_action( 'woocommerce_before_single_product_summary', array( $this, 'product_taxonomy' ), 8 );
				add_action( 'woocommerce_before_single_product_summary', array( $this, 'product_sku' ), 10 );

				// Change rating
				remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );
				add_action( 'woocommerce_before_single_product_summary', 'woocommerce_template_single_rating', 15 );

				// Add button write review
				add_action( 'woocommerce_before_single_product_summary', array( $this, 'button_write_review' ), 17 );

				if ( Helper::get_option( 'mobile_product_header' ) == 'compact' ) {
					add_action( 'woocommerce_single_product_summary', array( $this, 'open_meta_wrapper' ), 5 );
					add_action( 'woocommerce_single_product_summary', array( $this, 'close_meta_wrapper' ), 10 );
					add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 6 );
					add_action( 'woocommerce_single_product_summary', array( $this, 'product_taxonomy' ), 7 );
					add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 8 );
				}

				// Add Quantity Wrapper
				add_action( 'woocommerce_before_add_to_cart_quantity', array( $this, 'quantity_label' ) );
				add_action( 'woocommerce_before_add_to_cart_quantity', array( $this, 'open_quantity_wrapper' ) );
				add_action( 'woocommerce_after_add_to_cart_quantity', array( $this, 'stock' ) );
				add_action( 'woocommerce_after_add_to_cart_quantity', array( $this, 'close_quantity_wrapper' ) );

				add_action( 'woocommerce_single_product_summary', array( $this, 'outofstock' ), 18 );
				break;

			// Layout 4
			case '4':
				add_action( 'woocommerce_before_single_product_summary', array( $this, 'open_summary_wrapper' ), 2 );
				add_action( 'woocommerce_after_single_product_summary', array( $this, 'close_summary_wrapper' ), 1 );

				// Add meta wrapper
				add_action( 'woocommerce_single_product_summary', array( $this, 'open_meta_wrapper' ), 5 );
				add_action( 'woocommerce_single_product_summary', array( $this, 'close_meta_wrapper' ), 10 );

				// Add wrapper breadcrumb social
				add_action( 'woocommerce_before_single_product_summary', array( $this, 'open_breadcrumb_social_wrapper' ), 3 );
				add_action( 'woocommerce_before_single_product_summary', array( $this, 'close_breadcrumb_social_wrapper' ), 6 );

				// Change Breadcrumb
				add_action( 'woocommerce_before_single_product_summary', array( \Shopwell\Theme::instance()->get( 'breadcrumb' ), 'breadcrumb' ), 4, 0 );

				add_action( 'woocommerce_before_single_product_summary', array( $this, 'product_extra_content' ), 21 );

				add_action( 'woocommerce_single_product_summary', array( $this, 'badges' ), 1 );

				// Social
				add_action( 'woocommerce_before_single_product_summary', array( $this, 'product_quick_links' ), 5 );

				// Add in shopwell product meta wrapper
				add_action( 'woocommerce_single_product_summary', array( $this, 'product_taxonomy' ), 2 );
				add_action( 'woocommerce_single_product_summary', array( $this, 'product_sku' ), 9 );

				add_action( 'woocommerce_single_product_summary', array( $this, 'short_description' ), 20 );

				// Add Quantity Wrapper
				add_action( 'woocommerce_before_add_to_cart_quantity', array( $this, 'open_quantity_wrapper' ) );
				add_action( 'woocommerce_after_add_to_cart_quantity', array( $this, 'stock' ) );
				add_action( 'woocommerce_after_add_to_cart_quantity', array( $this, 'close_quantity_wrapper' ) );

				// Add side product
				add_action( 'woocommerce_after_single_product_summary', array( $this, 'side_products' ), 1 );

				add_action( 'woocommerce_single_product_summary', array( $this, 'outofstock' ), 18 );

				break;

			// Layout 5
			case '5':
				// Add wrapper breadcrumb social
				add_action( 'woocommerce_before_main_content', array( $this, 'open_breadcrumb_social_wrapper' ), 19 );
				add_action( 'woocommerce_before_main_content', array( $this, 'close_breadcrumb_social_wrapper' ), 29 );

				add_action( 'woocommerce_before_main_content', array( \Shopwell\Theme::instance()->get( 'breadcrumb' ), 'breadcrumb' ), 20, 0 );

				add_action( 'woocommerce_before_main_content', array( $this, 'product_quick_links' ), 25 );

				remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );
				add_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 2 );

				add_action( 'woocommerce_before_single_product_summary', array( $this, 'open_summary_wrapper' ), 21 );
				add_action( 'woocommerce_before_single_product_summary', array( $this, 'close_summary_wrapper' ), 99 );

				add_action( 'woocommerce_before_single_product_summary', array( $this, 'badges' ), 23 );

				remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5 );
				add_action( 'woocommerce_before_single_product_summary', 'woocommerce_template_single_title', 27 );

				add_action( 'woocommerce_before_single_product_summary', array( $this, 'open_meta_wrapper' ), 28 );
				add_action( 'woocommerce_before_single_product_summary', array( $this, 'close_meta_wrapper' ), 30 );

				// Add in shopwell product meta wrapper
				add_action( 'woocommerce_before_single_product_summary', array( $this, 'product_taxonomy' ), 29 );
				add_action( 'woocommerce_before_single_product_summary', array( $this, 'product_sku' ), 29 );

				remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );
				add_action( 'woocommerce_before_single_product_summary', array( $this, 'ratings_button_reviews' ), 31 );

				add_action( 'woocommerce_before_single_product_summary', array( $this, 'product_extra_content' ), 32 );
				add_action( 'woocommerce_before_single_product_summary', array( $this, 'short_description' ), 33 );

				// Add Quantity Wrapper
				add_action( 'woocommerce_before_add_to_cart_quantity', array( $this, 'quantity_label' ) );
				add_action( 'woocommerce_before_add_to_cart_quantity', array( $this, 'open_quantity_wrapper' ) );
				add_action( 'woocommerce_after_add_to_cart_quantity', array( $this, 'stock' ) );
				add_action( 'woocommerce_after_add_to_cart_quantity', array( $this, 'close_quantity_wrapper' ) );

				add_action( 'woocommerce_single_product_summary', array( $this, 'outofstock' ), 18 );

				break;

			// Layout 6
			case '6':
				// Add wrapper breadcrumb social
				add_action( 'woocommerce_before_main_content', array( $this, 'open_breadcrumb_social_wrapper' ), 19 );
				add_action( 'woocommerce_before_main_content', array( $this, 'close_breadcrumb_social_wrapper' ), 29 );

				add_action( 'woocommerce_before_main_content', array( \Shopwell\Theme::instance()->get( 'breadcrumb' ), 'breadcrumb' ), 20, 0 );

				add_action( 'woocommerce_before_main_content', array( $this, 'product_quick_links' ), 25 );

				add_action( 'woocommerce_single_product_summary', array( $this, 'badges' ), 1 );

				add_action( 'woocommerce_single_product_summary', array( $this, 'stock' ), 18 );

				add_action( 'woocommerce_single_product_summary', array( $this, 'short_description' ), 20 );

				add_action( 'shopwell_before_sticky_add_to_cart_product_tabs', array( $this, 'shopwell_sticky_add_to_cart__description_tab' ), 10 );
				add_action( 'shopwell_after_sticky_add_to_cart__product_tabs', array( $this, 'shopwell_sticky_add_to_cart__review_tab' ), 10 );

				add_action( 'woocommerce_before_single_product_summary', array( $this, 'open_summary_wrapper' ), 21 );
				add_action( 'woocommerce_after_single_product_summary', array( $this, 'close_summary_wrapper' ), 3 );

				// Add side product
				add_action( 'woocommerce_after_single_product_summary', array( $this, 'product_extra_content' ), 0 );

				if ( ! empty( Helper::get_option( 'product_side_products_enable' ) ) ) {
					add_action( 'woocommerce_after_single_product_summary', array( $this, 'side_products' ), 1 );
				}

				remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );
				add_filter( 'woocommerce_product_tabs', array( $this, 'unset_description_tab' ), 98 );
				add_filter( 'woocommerce_product_tabs', array( $this, 'unset_review_tab' ), 99 );
				add_action( 'woocommerce_after_single_product_summary', array( $this, 'show_tabs' ), 2 );

				// Add in shopwell product meta wrapper
				add_action( 'woocommerce_single_product_summary', array( $this, 'product_taxonomy' ), 2 );

				break;

			default:
				break;
		}
	}

	/**
	 * Products header.
	 *
	 *  @return void
	 */
	public function page_header_elements( $items ) {
		$items = array();

		return $items;
	}

	/**
	 * Open breadcrumb social wrapper
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function open_breadcrumb_social_wrapper() {
		echo '<div class="shopwell-breadcrumb-social-wrapper">';
	}

	/**
	 * Close breadcrumb social wrapper
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function close_breadcrumb_social_wrapper() {
		echo '</div>';
	}

	/**
	 * Open gallery summary wrapper
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function open_gallery_summary_wrapper() {
		$classes = Helper::get_option( 'product_image_zoom' ) ? 'product-image-zoom' : '';
		if ( in_array( Helper::get_option( 'product_layout' ), array( '2', '3', '6' ) ) ) {
			$classes .= ' product-thumbnails-vertical';
		}
		echo '<div class="product-gallery-summary ' . $classes . '">';
	}

	/**
	 * Close gallery summary wrapper
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function close_gallery_summary_wrapper() {
		echo '</div>';
	}

	/**
	 * Open shopwell gallery summary wrapper
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function open_shopwell_product_gallery() {
		global $product;

		$classes = 'shopwell-product-gallery';

		if ( count( $product->get_gallery_image_ids() ) <= 1 ) {
			$classes .= ' shopwell-product-gallery--single';
		}

		echo '<div class="' . $classes . '">';
	}

	/**
	 * Close shopwell gallery summary wrapper
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function close_shopwell_product_gallery() {
		echo '</div>';
	}


	/**
	 * Open summary wrapper
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function open_summary_wrapper() {
		echo '<div class="product-summary-wrapper">';
	}

	/**
	 * Close summary wrapper
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function close_summary_wrapper() {
		echo '</div>';
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
	 * Open quantity wrapper
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function open_quantity_wrapper() {
		$class = in_array( Helper::get_option( 'product_layout' ), array( '3', '4', '5', '6' ) ) ? 'shopwell-qty-medium' : '';
		printf( '<div class="shopwell-quantity-wrapper %s variations-attribute-change">', esc_attr( $class ) );
	}

	/**
	 * Close quantity wrapper
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function close_quantity_wrapper() {
		echo '</div>';
	}

	/**
	 * Quantity label
	 *
	 * @return void
	 */
	public function quantity_label() {
		global $product;
		if ( ! $product->is_sold_individually() ) {
			echo '<div class="quantity__label">' . esc_html__( 'Quantity:', 'shopwell' ) . '<span class="quantity__label-number"></span></div>';
		}
	}

	/**
	 * Product thumbnails columns
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function product_thumbnails_columns() {
		return 5;
	}

	/**
	 * Change Flex of Woocommerce
	 *
	 * @param array $options
	 * @return array
	 */
	public function product_thumbnails_carousel_options( $options ) {
		$options['directionNav'] = false;
		return $options;
	}

	/**
	 * Badges
	 *
	 * @return void
	 */
	public static function badges() {
		global $product;
		$custom_badges = maybe_unserialize( get_post_meta( $product->get_id(), 'custom_badges_text', true ) );
		if ( $custom_badges ) {
			$style               = '';
			$custom_badges_bg    = get_post_meta( $product->get_id(), 'custom_badges_bg', true );
			$custom_badges_color = get_post_meta( $product->get_id(), 'custom_badges_color', true );
			$bg_color            = ! empty( $custom_badges_bg ) ? '--id--badge-custom-bg:' . shopwell_sanitize_color( $custom_badges_bg ) . ';' : '';
			$color               = ! empty( $custom_badges_color ) ? '--id--badge-custom-color:' . shopwell_sanitize_color( $custom_badges_color ) . ';' : '';

			if ( $bg_color || $color ) {
				$style = 'style="' . $color . $bg_color . '"';
			}

			echo '<div class="woocommerce-badges"><span class="custom woocommerce-badge"' . $style . '>' . esc_html( $custom_badges ) . '</span></div>';
			return;
		}

		$sale_date = \Shopwell\WooCommerce\Badges::get_date_on_sale_to( $product );

		if ( $product->is_on_sale() && Helper::get_option( 'product_sale_type' ) == 'countdown' && ! empty( $sale_date ) ) {
			echo \Shopwell\WooCommerce\Badges::get_product_sale_countdown();
		} else {
			$badges = \Shopwell\WooCommerce\Badges::get_badges();
			if ( ! $badges ) {
				return;
			}
			if ( in_array( Helper::get_option( 'product_layout' ), array( '3', '4', '5' ) ) && empty( $sale_date ) ) {
				return;
			}
			printf( '<span class="woocommerce-badges variations-attribute-change">%s</span>', implode( '', $badges ) );
		}
	}

	/**
	 * Featured button open
	 *
	 * @static
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function product_featured_buttons() {
		if ( self::enable_wishlist_button() || self::enable_compare_button() ) {
			echo '<div class="product-featured-icons">';
			self::product_compare_button();
			self::product_wishlist_button();
			echo '</div>';
		}
	}

	/**
	 * Grouped Product Columns
	 *
	 * @since 1.4.9
	 *
	 * @return array
	 */
	public function grouped_product_columns( $columns ) {
		$leading = array();

		if ( in_array( 'label', $columns ) ) {
			$leading[] = 'label';
		}

		if ( in_array( 'quantity', $columns ) ) {
			$leading[] = 'quantity';
		}

		if ( empty( $leading ) ) {
			return $columns;
		}

		$columns = array_merge(
			$leading,
			array_diff( $columns, $leading )
		);

		return $columns;
	}

	/**
	 * Check Wishlist Button
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function enable_wishlist_button() {
		if ( ! function_exists( 'wcboost_wishlist' ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Wishlist Button
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function product_wishlist_button() {
		if ( shortcode_exists( 'wcboost_wishlist_button' ) ) {
			echo do_shortcode( '[wcboost_wishlist_button]' );
		}
	}

	/**
	 * Check Compare Button
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function enable_compare_button() {
		if ( ! function_exists( 'wcboost_products_compare' ) ) {
			return false;
		}

		return true;
	}

	/**
	 *  Compare button
	 */
	public static function product_compare_button() {
		\Shopwell\WooCommerce\Helper::product_compare_button();
	}

	/**
	 * Get product taxonomy
	 *
	 * @static
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function product_taxonomy( $taxonomy = 'product_cat' ) {
		global $product;

		$taxonomy = Helper::get_option( 'product_taxonomy' );
		if ( empty( $taxonomy ) ) {
			return;
		}

		$taxonomy = empty( $taxonomy ) ? 'product_cat' : $taxonomy;
		$terms    = get_the_terms( $product->get_id(), $taxonomy );

		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
			printf(
				'<div class="meta meta-cat">%s <a href="%s">%s</a></div>',
				esc_html__( 'in', 'shopwell' ),
				esc_url( get_term_link( $terms[0] ), $taxonomy ),
				esc_html( $terms[0]->name )
			);
		}
	}

	/**
	 * Get product sku
	 *
	 * @static
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function product_sku() {
		global $product;
		if ( $product->get_sku() ) {
			printf(
				'<div class="meta meta-sku">%s <span class="sku">%s</span></div>',
				esc_html__( 'Sku:', 'shopwell' ),
				esc_html( $product->get_sku() ),
			);
		}
	}

	/**
	 * Get product sku and tags
	 *
	 * @static
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function product_sku_tags() {
		global $product;
		$tags = Helper::get_option( 'product_tags' ) ? wc_get_product_tag_list( $product->get_id(), ', ', '<div class="meta-tags">' . _n( 'Tag:', 'Tags:', count( $product->get_tag_ids() ), 'shopwell' ) . ' ', '</div>' ) : '';

		if ( ! empty( $product->get_sku() ) || ! empty( $tags ) ) {
			echo '<div class="meta-sku-tags">';
				$this->product_sku();
				echo ! empty( $tags ) ? $tags : '';
			echo '</div>';
		}
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
	 * Format a sale price for display.
	 *
	 * @since  3.0.0
	 * @param  string $regular_price Regular price.
	 * @param  string $sale_price    Sale price.
	 * @return string
	 */
	public function format_sale_price( $price, $regular_price, $sale_price ) {
		global $product, $wp_query;

		$price = '<ins>' . ( is_numeric( $sale_price ) ? wc_price( $sale_price ) : $sale_price ) . '</ins> <del aria-hidden="true">' . ( is_numeric( $regular_price ) ? wc_price( $regular_price ) : $regular_price ) . '</del>';

		if ( is_object( $product ) && $product->get_id() == $wp_query->post->ID ) {
			$price .= \Shopwell\WooCommerce\Helper::price_save( $regular_price, $sale_price );
		}

		return $price;
	}

	/**
	 * Product ratings and button reviews
	 *
	 * @return void
	 */
	public function ratings_button_reviews() {
		echo '<div class="product-meta-wrapper">';
			woocommerce_template_single_rating();
			$this->button_write_review();
		echo '</div>';
	}

	/**
	 * Show stock
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function stock() {
		global $product, $wp_query;
		$_product = wc_get_product( $wp_query->post->ID );

		if ( $_product->is_type( 'grouped' ) ) {
			return;
		}

		echo wc_get_stock_html( $product );
	}

	/**
	 * Show Out of stock
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function outofstock() {
		global $wp_query;
		$_product = wc_get_product( $wp_query->post->ID );

		if ( $_product->is_type( 'grouped' ) ) {
			return;
		}

		if ( $_product->get_stock_status() != 'outofstock' ) {
			return;
		}

		$this->stock();
	}

	/**
	 * Change Text In Stock
	 *
	 * @return array
	 */
	public static function change_text_stock( $availability, $product ) {
		if ( $product->is_in_stock() ) {
			if ( empty( $availability['availability'] ) ) {
				$availability['availability'] = __( 'Available in stock', 'shopwell' );
			}
		}

		return $availability;
	}

	/**
	 * Product Short Description
	 *
	 * @return  void
	 */
	public function short_description() {
		if ( ! Helper::get_option( 'product_description' ) ) {
			return;
		}

		global $product;

		$content = $product->get_short_description();
		if ( empty( $content ) ) {
			return;
		}
		echo '<div class="short-description">';
		echo '<label class="short-description__label">' . esc_html__( 'Features', 'shopwell' ) . '</label>';
		if ( has_shortcode( $content, 'shopwell_more' ) ) {
				echo wp_kses_post( do_shortcode( $content ) );
		} else {
			$option = array(
				'more' => esc_html__( 'Show More', 'shopwell' ),
				'less' => esc_html__( 'Show Less', 'shopwell' ),
			);

			printf( '<div class="short-description__content">%s</div>', wp_kses_post( do_shortcode( $content ) ) );
			printf(
				'
				<button class="short-description__more shopwell-button--subtle shopwell-button--color-black" data-settings="%s">%s</button>',
				htmlspecialchars( json_encode( $option ) ),
				esc_html__( 'Show More', 'shopwell' )
			);
		}
		echo '</div>';
	}

	/**
	 * Upsells Product Title
	 *
	 * @return void
	 */
	public function upsells_title() {
		return esc_html__( 'You Might Also Like', 'shopwell' );
	}

	/**
	 * Change columns upsell
	 *
	 * @return void
	 */
	public function upsells_columns( $columns ) {
		$columns = 5;

		return $columns;
	}

	/**
	 * Change limit upsell
	 *
	 * @return void
	 */
	public function upsells_total( $limit ) {
		$limit = \Shopwell\Helper::get_option( 'upsells_products_numbers' );

		return $limit;
	}

	/**
	 * Show button write review
	 *
	 * @return void
	 */
	public function button_write_review() {
		printf( '<div class="button-write-review"><a class="shopwell-button shopwell-button--subtle shopwell-button--color-black" href="#tab-reviews">%s</a></div>', esc_html__( 'Write a Review', 'shopwell' ) );
	}

	/**
	 * Unset description tab
	 *
	 * @return array
	 */
	public function unset_description_tab( $tabs ) {
		if ( isset( $tabs['description'] ) ) {
			self::$unset_tabs['description'] = $tabs['description'];
			unset( $tabs['description'] );
		}

		return $tabs;
	}

	/**
	 * Unset review tab
	 *
	 * @return array
	 */
	public function unset_review_tab( $tabs ) {
		if ( isset( $tabs['reviews'] ) ) {
			self::$unset_tabs['reviews'] = $tabs['reviews'];
			unset( $tabs['reviews'] );
		}

		return $tabs;
	}

	/**
	 * Review
	 *
	 * @return void
	 */
	public function show_reviews() {
		if ( empty( self::$unset_tabs ) || empty( self::$unset_tabs['reviews'] ) ) {
			return;
		}

		echo '<div id="tab-reviews" class="woocommerce-tabs woocommerce-tabs--reviews">';
			printf( '<h4 class="woocommerce-tabs--reviews-title">%s</h4>', self::$unset_tabs['reviews']['title'] );
			call_user_func( self::$unset_tabs['reviews']['callback'], 'reviews', self::$unset_tabs['reviews'] );
		echo '</div>';
	}

	/**
	 * Description
	 *
	 * @return void
	 */
	public function show_description() {
		if ( empty( self::$unset_tabs ) || empty( self::$unset_tabs['description'] ) ) {
			return;
		}

		echo '<div id="tab-description" class="woocommerce-tabs woocommerce-tabs--description">';
			printf( '<h4 class="woocommerce-tabs--description-title">%s</h4>', self::$unset_tabs['description']['title'] );
			echo '<div class="woocommerce-tabs--description-content">';
				call_user_func( self::$unset_tabs['description']['callback'], 'description', self::$unset_tabs['description'] );
			echo '</div>';
		echo '</div>';
	}

	/**
	 * Show Tabs
	 *
	 * @return void
	 */
	public function show_tabs() {
		echo '<div class="shopwell-tabs-wrapper">';
			woocommerce_output_product_data_tabs();
			$this->show_description();
			$this->show_reviews();
		echo '</div>';
	}

	/**
	 *
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function shopwell_sticky_add_to_cart__review_tab() {
		if ( empty( self::$unset_tabs ) || empty( self::$unset_tabs['reviews'] ) ) {
			return;
		}

		printf( '<li class="reviews-tab" aria-controls="tab-reviews"><a href="#tab-reviews">%s</a></li>', self::$unset_tabs['reviews']['title'] );
	}

	/**
	 *
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function shopwell_sticky_add_to_cart__description_tab() {
		if ( empty( self::$unset_tabs ) || empty( self::$unset_tabs['description'] ) ) {
			return;
		}

		printf( '<li class="description-tab" aria-controls="tab-description"><a href="#tab-description">%s</a></li>', self::$unset_tabs['description']['title'] );
	}

	/**
	 * Get entry social
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function product_quick_links() {
		echo '<div class="shopwell-product-quick-links">';
			$this->share_button();
		if ( Helper::get_option( 'product_layout' ) !== '4' ) {
			$this->print_button();
		}
		echo '</div>';
	}

	/**
	 * Share social
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function share_button( $icon = 'share-mini', $text = '' ) {
		if ( ! class_exists( '\Shopwell\Addons\Helper' ) && ! method_exists( '\Shopwell\Addons\Helper', 'share_link' ) ) {
			return;
		}

		if ( ! Helper::get_option( 'product_sharing' ) ) {
			return;
		}
		get_template_part(
			'template-parts/buttons/product',
			'share',
			array(
				'icon' => $icon,
				'text' => $text,
			)
		);
	}

	/**
	 * Button Share
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function share_icons() {
		if ( ! Helper::get_option( 'product_sharing' ) ) {
			return;
		}

		echo \Shopwell\Helper::share_socials();
	}

	/**
	 * Print social
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function print_button() {
		get_template_part( 'template-parts/buttons/product', 'print' );
	}

	/**
	 * Open product buttons
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function open_product_buttons() {
		echo '<div class="shopwell-product-images-buttons">';
	}


	/**
	 * Close product buttons
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function close_product_buttons() {
		echo '</div>';
	}


	/**
	 * Check 360
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function check_360() {
		$product_images_dg = '';
		$images_dg         = get_post_meta( get_the_ID(), 'product_360_view', false );

		if ( $images_dg ) {
			foreach ( $images_dg as $image ) {
				$image_dg           = wp_get_attachment_image_src( $image, 'full' );
				$product_images_dg .= $product_images_dg ? ',' : '';
				$product_images_dg .= $image_dg ? $image_dg[0] : '';
			}
		}

		return $product_images_dg;
	}

	/**
	 * Product 360 button
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function product_360_button() {
		if ( empty( $this->check_360() ) ) {
			return;
		}

		get_template_part( 'template-parts/buttons/product', '360' );
	}

	/**
	 * Modal 360
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function modal_360() {
		if ( empty( $this->check_360() ) ) {
			return;
		}

		get_template_part( 'template-parts/modals/360' );
	}

	/**
	 * Modal socials
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function modal_socials() {
		if ( ! Helper::get_option( 'product_sharing' ) ) {
			return;
		}

		$output = \Shopwell\Helper::share_socials();

		get_template_part( 'template-parts/modals/socials', '', $output );
	}

	/**
	 * Modal socials
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function modal_product_more() {
		if ( Helper::get_option( 'mobile_product_header' ) != 'compact' ) {
			return;
		}

		get_template_part( 'template-parts/modals/product', 'more' );
	}



	/**
	 * Check Video
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public static function get_video_url() {
		$video_url = get_post_meta( get_the_ID(), 'video_url', true );
		$video_url = ! empty( $video_url ) ? esc_url( $video_url ) : false;
		return $video_url;
	}

	/**
	 * Product video
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function product_video_button() {
		if ( empty( $this->get_video_url() ) ) {
			return;
		}

		get_template_part( 'template-parts/buttons/product', 'video' );
	}

	/**
	 * Product zoom button
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function product_lightbox_button() {
		if ( empty( Helper::get_option( 'product_image_lightbox' ) ) ) {
			return;
		}

		get_template_part( 'template-parts/buttons/product', 'lightbox' );
	}

	/**
	 * Display side products on prduct page.
	 */
	public function side_products() {
		if ( ! class_exists( 'WC_Shortcode_Products' ) ) {
			return;
		}

		global $product;

		$args  = array();
		$limit = Helper::get_option( 'product_side_products_limit' );
		$type  = Helper::get_option( 'product_side_products' );

		if ( 'related_products' == $type ) {
			$query = new \stdClass();

			$related_products = array_filter( array_map( 'wc_get_product', wc_get_related_products( $product->get_id(), $limit, $product->get_upsell_ids() ) ), 'wc_products_array_filter_visible' );
			$related_products = wc_products_array_orderby( $related_products, 'rand', 'desc' );

			$args['query'] = $related_products ? $related_products : '';
		} else {
			$atts = array(
				'per_page'     => intval( $limit ),
				'category'     => '',
				'cat_operator' => 'IN',
			);

			switch ( $type ) {
				case 'sale_products':
				case 'top_rated_products':
					$atts = array_merge(
						array(
							'orderby' => 'title',
							'order'   => 'ASC',
						),
						$atts
					);
					break;

				case 'recent_products':
					$atts = array_merge(
						array(
							'orderby' => 'date',
							'order'   => 'DESC',
						),
						$atts
					);
					break;

				case 'featured_products':
					$atts               = array_merge(
						array(
							'orderby' => 'date',
							'order'   => 'DESC',
							'orderby' => 'rand',
						),
						$atts
					);
					$atts['visibility'] = 'featured';
					break;
			}

			$args = new \WC_Shortcode_Products( $atts, $type );
			$args = $args->get_query_args();

			if ( isset( $args['post__in'] ) ) {
				$index = array_search( $product->get_id(), $args['post__in'] );

				if ( $index !== false ) {
					unset( $args['post__in'][ $index ] );
				}
			} elseif ( ! isset( $args['post__not_in'] ) ) {
					$args['post__not_in'] = array( $product->get_id() );
			} else {
				$args['post__not_in'][] = $product->get_id();
			}

			$query         = new \WP_Query( $args );
			$args['query'] = $query ? $query->posts : '';
		}

		$args['type'] = $type;
		if ( empty( $args['query'] ) ) {
			return;
		}
		wc_get_template( 'single-product/side-product.php', $args );
	}

	/**
	 * Data badges
	 *
	 * @return void
	 */
	public function data_badges() {
		global $product;

		if ( $product->is_type( 'variable' ) ) {
			$variation_ids = $product->get_visible_children();
			echo '<div class="shopwell-date-onsale-to">';
			foreach ( $variation_ids as $variation_id ) {
				$variation = wc_get_product( $variation_id );

				if ( $variation->is_on_sale() ) {
					$date_on_sale_to = $variation->get_date_on_sale_to();

					if ( ! empty( $date_on_sale_to ) ) {
						$days = strtotime( $date_on_sale_to ) - strtotime( current_time( 'Y-m-d H:i:s' ) );

						$day_text = date( 'd', $days ) . esc_html__( ' Day', 'shopwell' );
						if ( date( 'd', $days ) > 1 ) {
							$day_text = date( 'd', $days ) . esc_html__( ' Days', 'shopwell' );
						}

						printf(
							'<span class="shopwell-date-onsale-to__item variation-id-%d">%s%s</span>',
							$variation_id,
							esc_html__( 'Sale Ends in ', 'shopwell' ),
							esc_html( $day_text )
						);
					}
				}
			}
			echo '</div>';
		}
	}

	/**
	 * Add product extra content
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function product_extra_content() {
		$sidebar = 'single-product-extra-content';
		if ( is_active_sidebar( $sidebar ) ) {
			echo '<div class="single-product-extra-content">';
				ob_start();
				dynamic_sidebar( $sidebar );
				$output = ob_get_clean();
				echo apply_filters( 'shopwell_single_product_extra_content', $output );
			echo '</div>';
		}
	}

	/**
	 * Add product mobile header
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function mobile_header() {
		echo '<div class="product-header-compact">';
			get_template_part( 'template-parts/mobile/header', 'compact' );
		if ( ! empty( Helper::get_option( 'header_sticky' ) ) ) {
			get_template_part( 'template-parts/mobile/sticky-header', 'compact' );
		}
		echo '</div>';
	}

	/**
	 * Add product gallery fixed spacing
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function product_gallery_fixed_spacing() {
		echo '<div class="product-fixed-gallery-spacing"></div>';
	}
}
