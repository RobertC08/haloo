<?php
/**
 * Shopwell Blog Header functions and definitions.
 *
 * @package Shopwell
 */

namespace Shopwell\Blog;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shopwell Post
 */
class Page_Header {

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
		add_filter( 'shopwell_page_header_classes', array( $this, 'classes' ) );
		add_filter( 'shopwell_get_default_page_header_elements', array( $this, 'elements' ) );
		add_filter( 'shopwell_page_header_description', array( $this, 'description' ) );
	}

	/**
	 * Page Header Classes
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function classes( $classes ) {
		if ( ( is_archive() || is_author() || is_category() || is_search() || is_tag() ) && 'post' == get_post_type() ) {
			$classes .= ' page-header--archive-post';
		}

		return $classes;
	}

	/**
	 * Page Header Elements
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function elements( $items ) {
		if ( is_home() ) {
			$items = \Shopwell\Helper::get_option( 'blog_header' ) ? (array) \Shopwell\Helper::get_option( 'blog_header_els' ) : array();
		} else {
			$items = array( 'title', 'breadcrumb' );
		}

		return apply_filters( 'shopwell_blog_header_elements', $items );
	}


	/**
	 * Get description
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function description() {
		$description = '';

		if ( \Shopwell\Helper::is_blog() ) {
			if ( is_home() ) {
				$length      = apply_filters( 'shopwell_blog_header_description_length', 20 );
				$post_id     = get_option( 'page_for_posts' );
				$description = \Shopwell\Helper::get_content_limit( $length, '', get_post_field( 'post_content', $post_id ) );
			} elseif ( is_archive() ) {
				$description = get_the_archive_description();
			}

			if ( $description ) {
				return '<div class="page-header__description">' . wp_kses_post( $description ) . '</div>';
			}
		}
	}
}
