<?php

/**
 * WooCommerce dokan functions
 *
 * @package shopwell
 */

namespace Shopwell\Vendors;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


/**
 * Class of Vendor Dokan
 *
 * @version 1.0
 */
class Fibo_Search {
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
	 * Construction function
	 *
	 * @since  1.0
	 * @return Shopwell_Vendor
	 */
	public function __construct() {
		add_filter( 'dgwt/wcas/icon', array( $this, 'search_icon' ), 20, 4 );

		add_action( 'dgwt/wcas/form', array( $this, 'search_trending' ) );

		add_filter( 'shopwell_header_search_icon_args', array( $this, 'search_icon_args' ) );

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 30 );
	}

	/**
	 * Enqueue styles and scripts.
	 */
	public function enqueue_scripts() {
		wp_enqueue_style( 'shopwell-fibo-search', get_template_directory_uri() . '/assets/css/vendors/fibo-search.css', array(), '20230203' );
	}


	/**
	 * Search icon
	 *
	 *  @return void
	 */
	public function search_icon( $svg, $name, $class, $color ) {
		if ( $name == 'magnifier-thin' ) {
			$svg = \Shopwell\Icon::get_svg( 'search' );
		}

		return $svg;
	}

	public function search_trending() {
		$args['trending_searches'] = \Shopwell\Helper::get_option( 'header_search_trending_searches' );
		\Shopwell\Header\Search::trendings( $args );
	}

	public function search_icon_args( $args ) {
		if ( \Shopwell\Helper::get_option( 'header_search_bar' ) == 'shortcode' ) {
			$args['search_icon_class']  = isset( $args['search_icon_class'] ) ? $args['search_icon_class'] : '';
			$args['search_icon_class'] .= ' fibo-search-icon';

			$args['search_modal'] = 'fibo-search';
		}

		return $args;
	}
}
