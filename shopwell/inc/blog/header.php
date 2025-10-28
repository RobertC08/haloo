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
class Header {

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
		add_filter( 'shopwell_get_header_layout', array( $this, 'header_layout' ) );
		add_filter( 'shopwell_get_header_mobile_layout', array( $this, 'header_layout' ) );
		add_filter( 'shopwell_get_primary_menu', array( $this, 'primary_menu' ) );
		add_filter( 'shopwell_get_topbar', array( $this, 'get_topbar' ) );
		add_filter( 'shopwell_get_campaign_bar', array( $this, 'get_campaign_bar' ) );

		add_filter( 'shopwell_header_logo_type', array( $this, 'logo_type' ) );
		add_filter( 'shopwell_header_logo', array( $this, 'logo' ), 20, 2 );
		add_filter( 'shopwell_header_logo_dimension', array( $this, 'logo_dimension' ) );
	}

	/**
	 * Header logo Type
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function logo_type( $type ) {
		$blog_type = \Shopwell\Helper::get_option( 'header_blog_logo_type' );
		$type      = $blog_type != 'default' ? $blog_type : $type;
		return $type;
	}

	/**
	 * Header logo
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function logo( $logo, $type ) {
		$type = \Shopwell\Helper::get_option( 'header_blog_logo_type' );

		if ( 'text' == $type ) {
			$logo = \Shopwell\Helper::get_option( 'header_blog_logo_text' );
		} elseif ( 'svg' == $type ) {
			$logo = \Shopwell\Helper::get_option( 'header_blog_logo_svg' );
		} elseif ( 'image' == $type ) {
			$logo_blog = \Shopwell\Helper::get_option( 'header_blog_logo' );
			$logo      = empty( $logo_blog ) ? $logo : $logo_blog;
		}

		return $logo;
	}

	/**
	 * Header logo Type
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function logo_dimension( $dimension ) {
		$logo_width     = \Shopwell\Helper::get_option( 'header_blog_logo_width' );
		$logo_height    = \Shopwell\Helper::get_option( 'header_blog_logo_height' );
		$blog_dimension = array(
			'width'  => $logo_width,
			'height' => $logo_height,
		);
		if ( ! empty( $blog_dimension ) && ( ! empty( $blog_dimension['width'] ) || ! empty( $blog_dimension['height'] ) ) ) {
			if ( isset( $blog_dimension['width'] ) ) {
				$dimension['width'] = $blog_dimension['width'];
			}

			if ( isset( $blog_dimension['height'] ) ) {
				$dimension['height'] = $blog_dimension['height'];
			}
		}
		return $dimension;
	}

		/**
		 * Header Layout
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
	public function header_layout( $layout ) {
		if ( \Shopwell\Helper::is_blog() || is_singular( 'post' ) ) {
			$blog_version = \Shopwell\Helper::get_option( 'header_blog_version' );
			$layout       = $blog_version && $blog_version !== '' ? $blog_version : $layout;
		}

		return $layout;
	}

	/**
	 *  Primary Menu
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function primary_menu( $menu_id ) {
		if ( \Shopwell\Helper::is_blog() || is_singular( 'post' ) ) {
			$primary_menu_id = \Shopwell\Helper::get_option( 'blog_primary_menu' );
			$menu_id         = ! empty( $primary_menu_id ) ? $primary_menu_id : $menu_id;
		}

		return $menu_id;
	}


		/**
		 * Footer Layout
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
	public function get_topbar( $topbar ) {
		if ( ( \Shopwell\Helper::is_blog() || is_singular( 'post' ) ) && \Shopwell\Helper::get_option( 'header_blog_hide_topbar' ) ) {
			$topbar = false;
		}

		return $topbar;
	}

		/**
		 * Footer Layout
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
	public function get_campaign_bar( $campaign_bar ) {
		if ( ( \Shopwell\Helper::is_blog() || is_singular( 'post' ) ) && \Shopwell\Helper::get_option( 'header_blog_hide_campaign_bar' ) ) {
			$campaign_bar = false;
		}

		return $campaign_bar;
	}
}
