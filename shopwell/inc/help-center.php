<?php
/**
 * Help Center functions and definitions.
 *
 * @package Shopwell
 */

namespace Shopwell;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Header initial
 */
class Help_Center {
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
		if ( ! is_singular( 'sw_help_article' ) && ( 'sw_help_article' == get_query_var( 'post_type' ) || is_tax( 'shopwell_help_cat' ) ) ) {
			\Shopwell\Help_Center\Category::instance();
		}

		if ( 'sw_help_article' == get_query_var( 'post_type' ) || is_tax( 'shopwell_help_cat' ) || \Shopwell\Helper::is_help_center_page() ) {
			\Shopwell\Help_Center\Header::instance();
			\Shopwell\Help_Center\Footer::instance();

			\Shopwell\Help_Center\Page_Header::instance();

		}

		if ( is_singular( 'sw_help_article' ) ) {
			\Shopwell\Help_Center\Article::instance();
		}
	}
}
