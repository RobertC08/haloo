<?php
/**
 * Page_Header functions and definitions.
 *
 * @package Shopwell
 */

namespace Shopwell;

use Shopwell\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Header initial
 */
class Page_Header {
	/**
	 * Instance
	 *
	 * @var $instance
	 */
	protected static $instance = null;

	/**
	 * Page Header items
	 *
	 * @var $items
	 */
	protected static $items = null;


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
		add_action( 'get_the_archive_title', array( $this, 'get_archive_title' ), 30 );
		add_action( 'shopwell_after_header', array( $this, 'show_page_header' ), 99 );

		add_action( 'shopwell_before_page_header_content', array( $this, 'breadcrumb' ) );
		add_action( 'shopwell_page_header_content', array( $this, 'title' ), 10 );
		add_action( 'shopwell_page_header_content', array( $this, 'description' ), 20 );
	}

	/**
	 * Show page header
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function show_page_header() {
		if ( is_404() ) {
			return;
		}

		if ( ! $this->get_items() ) {
			return;
		}

		get_template_part( 'template-parts/page-header/page-header' );
	}

	/**
	 * Show page header
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public static function get_items() {
		if ( ! isset( self::$items ) ) {
			$items = array();

			if ( Helper::get_option( 'page_header' ) ) {
				$items = (array) Helper::get_option( 'page_header_els' );
			}

			$items = apply_filters( 'shopwell_get_default_page_header_elements', $items );

			$items = self::custom_items( $items );

			self::$items = $items;
		}

		return apply_filters( 'shopwell_get_page_header_elements', self::$items );
	}

	/**
	 * Custom page header
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public static function custom_items( $items ) {
		if ( empty( $items ) ) {
			return array();
		}

		$get_id = Helper::get_post_ID();

		if ( get_post_meta( $get_id, 'shopwell_hide_page_header', true ) ) {
			$items = array();
		}

		if ( get_post_meta( $get_id, 'shopwell_hide_breadcrumb', true ) ) {
			$key = array_search( 'breadcrumb', $items );
			if ( $key !== false ) {
				unset( $items[ $key ] );
			}
		}

		if ( get_post_meta( $get_id, 'shopwell_hide_title', true ) ) {
			$key = array_search( 'title', $items );
			if ( $key !== false ) {
				unset( $items[ $key ] );
			}
		}

		return $items;
	}

	/**
	 * Show classes
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function classes( $classes ) {
		if ( ! in_array( 'title', self::get_items() ) ) {
			$classes .= ' hide-title';
		}

		echo apply_filters( 'shopwell_page_header_classes', $classes );
	}

	/**
	 * Show content
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function breadcrumb() {
		if ( in_array( 'breadcrumb', self::get_items() ) ) {
			\Shopwell\Breadcrumb::instance()->breadcrumb();
		}
	}

	/**
	 * Show content
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function title() {
		if ( ! in_array( 'title', self::get_items() ) ) {
			return;
		}

		$title = '<h1 class="page-header__title">' . get_the_archive_title() . '</h1>';
		echo apply_filters( 'shopwell_page_header_title', $title );
	}

		/**
		 * Show content
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
	public static function description() {
		if ( ! in_array( 'title', self::get_items() ) ) {
			return;
		}

		echo apply_filters( 'shopwell_page_header_description', '' );
	}

	/**
	 * Show archive title
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_archive_title( $title ) {
		if ( is_search() ) {
			$title = sprintf( esc_html__( 'Search Results', 'shopwell' ) );
		} elseif ( is_404() ) {
			$title = sprintf( esc_html__( 'Page Not Found', 'shopwell' ) );
		} elseif ( is_page() ) {
			$title = get_the_title( \Shopwell\Helper::get_post_ID() );
		} elseif ( is_home() && is_front_page() ) {
			$title = esc_html__( 'The Latest Posts', 'shopwell' );
		} elseif ( is_home() && ! is_front_page() ) {
			$title = get_the_title( intval( get_option( 'page_for_posts' ) ) );
		} elseif ( function_exists( 'is_shop' ) && is_shop() ) {
			$current_term = get_queried_object();
			if ( $current_term && isset( $current_term->term_id ) && ( $current_term->taxonomy == 'product_cat' || $current_term->taxonomy == 'product_brand' ) ) {
				$title = $current_term->name;
			} else {
				$title = get_the_title( intval( get_option( 'woocommerce_shop_page_id' ) ) );
			}
		} elseif ( is_single() ) {
			$title = get_the_title();
		} elseif ( is_tax() || is_category() ) {
			$title = single_term_title( '', false );
		} elseif ( function_exists( 'dokan_is_store_page' ) && dokan_is_store_page() ) {
			$author    = get_user_by( 'id', get_query_var( 'author' ) );
			$shop_info = get_user_meta( get_query_var( 'author' ), 'dokan_profile_settings', true );
			$shop_name = $author->display_name;
			if ( $shop_info && isset( $shop_info['store_name'] ) && $shop_info['store_name'] ) {
				$shop_name = $shop_info['store_name'];
			}
			$title = $shop_name;
		}

		return $title;
	}
}
