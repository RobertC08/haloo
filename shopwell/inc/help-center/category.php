<?php
/**
 * Help Center functions and definitions.
 *
 * @package Shopwell
 */

namespace Shopwell\Help_Center;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Header initial
 */
class Category {
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
		add_filter( 'body_class', array( $this, 'body_class' ) );

		add_filter( 'shopwell_site_layout', array( $this, 'layout' ) );
		add_filter( 'shopwell_get_sidebar', '__return_false' );
		add_filter( 'shopwell_content_template_part', array( $this, 'content_template_part' ) );

		if ( is_search() ) {
			add_filter( 'sw_help_article_title', array( $this, 'get_highlight' ) );
			add_filter( 'sw_help_article_content', array( $this, 'get_highlight' ) );
		}
	}

	/**
	 * Body Class
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function body_class( $classes ) {
		$classes[] = 'shopwell-help-archive';

		return $classes;
	}

	/**
	 * Content Template part
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function content_template_part() {
		return 'help';
	}

	/**
	 * layout
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function layout() {
		return 'no-sidebar';
	}


	/**
	 * Content hightlight
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function get_highlight( $content ) {
		$s = get_query_var( 's' );
		if ( empty( $s ) ) {
			return $content;
		}
		$keys    = explode( ' ', $s );
		$content = preg_replace( '/(' . implode( '|', $keys ) . ')/iu', '<span class="search-hightlight">\0</span>', $content );
		return $content;
	}
}
