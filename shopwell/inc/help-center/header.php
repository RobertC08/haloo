<?php
/**
 * Style functions and definitions.
 *
 * @package Shopwell
 */

namespace Shopwell\Help_Center;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Style initial
 *
 * @since 1.0.0
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
		add_filter( 'shopwell_get_header_background', array( $this, 'header_background' ) );
		add_filter( 'shopwell_get_header_text_color', array( $this, 'header_text_color' ) );
		add_action( 'shopwell_after_header', array( $this, 'search_bar' ) );
		add_filter( 'shopwell_get_header_layout', array( $this, 'header_layout' ) );
		add_filter( 'shopwell_get_header_mobile_layout', array( $this, 'header_layout' ) );
		add_filter( 'shopwell_get_primary_menu', array( $this, 'primary_menu' ) );
		add_filter( 'shopwell_get_topbar', '__return_false' );
		add_filter( 'shopwell_get_campaign_bar', '__return_false' );
		add_filter( 'shopwell_header_logo_type', array( $this, 'logo_type' ) );
		add_filter( 'shopwell_header_logo', array( $this, 'logo' ), 20, 2 );
		add_filter( 'shopwell_header_logo_light', array( $this, 'logo_light' ), 20, 2 );
		add_filter( 'shopwell_header_logo_dimension', array( $this, 'logo_dimension' ) );
	}


	/**
	 * Header background
	 *
	 * @since  1.0.0
	 *
	 * @return string
	 */
	public function header_background( $header_transparent ) {
		if ( ! $this->has_search_bar() && ! \Shopwell\Helper::is_help_center_page() ) {
			return $header_transparent;
		}
		$header_transparent = intval( \Shopwell\Helper::get_option( 'help_center_header_transparent' ) ) ? 'transparent' : $header_transparent;
		return $header_transparent;
	}

		/**
		 * Header Layout
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
	public function header_layout( $layout ) {
		$hc_version = \Shopwell\Helper::get_option( 'help_center_header' );
		$layout     = ! empty( $hc_version ) ? $hc_version : $layout;

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
		$primary_menu_id = \Shopwell\Helper::get_option( 'help_center_primary_menu' );
		$menu_id         = ! empty( $primary_menu_id ) ? $primary_menu_id : $menu_id;

		return $menu_id;
	}

	/**
	 * Header Color Text
	 *
	 * @since  1.0.0
	 *
	 * @return string
	 */
	public function header_text_color( $header_color ) {
		if ( ! $this->has_search_bar() && ! \Shopwell\Helper::is_help_center_page() ) {
			return $header_color;
		}

		if ( ! intval( \Shopwell\Helper::get_option( 'help_center_header_transparent' ) ) ) {
			return $header_color;
		}
		$header_color = \Shopwell\Helper::get_option( 'header_help_center_color' );

		return $header_color;
	}

	/**
	 * Search Bar
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function search_bar() {
		if ( ! $this->has_search_bar() ) {
			return;
		}
		get_template_part( 'template-parts/header/search-bar', 'hc' );
	}

	/**
	 * Check Has Search Bar
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function has_search_bar() {
		$hc_search = (array) \Shopwell\Helper::get_option( 'help_center_search' );
		if ( is_singular( 'sw_help_article' ) ) {
			if ( ! in_array( 'single', $hc_search ) ) {
				return false;
			}
		} elseif ( ! in_array( 'archive', $hc_search ) ) {
			return false;
		} elseif ( \Shopwell\Helper::is_help_center_page() ) {
			return false;
		}
		return true;
	}

	/**
	 * Header logo Type
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function logo_type( $type ) {
		$logo_type = \Shopwell\Helper::get_option( 'header_help_center_logo_type' );
		$type      = $logo_type != 'default' ? $logo_type : $type;
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
		if ( 'default' == \Shopwell\Helper::get_option( 'header_help_center_logo_type' ) ) {
			return $logo;
		}
		if ( 'text' == $type ) {
			$logo = \Shopwell\Helper::get_option( 'header_help_center_logo_text' );
		} elseif ( 'svg' == $type ) {
			$logo = \Shopwell\Helper::get_option( 'header_help_center_logo_svg' );
		} else {
			$hc_blog = \Shopwell\Helper::get_option( 'header_help_center_logo' );
			$logo    = empty( $hc_blog ) ? $logo : $hc_blog;
		}

		return $logo;
	}

	/**
	 * Header logo
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function logo_light( $logo_light, $type ) {
		if ( 'svg' == $type ) {
			$logo_light = \Shopwell\Helper::get_option( 'header_help_center_logo_light_svg' );
		} elseif ( 'image' == $type ) {
			$logo_light = \Shopwell\Helper::get_option( 'header_help_center_logo_light' );
		}

		return $logo_light;
	}

	/**
	 * Header logo Type
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function logo_dimension( $dimension ) {
		$logo_width     = \Shopwell\Helper::get_option( 'header_help_center_logo_width' );
		$logo_height    = \Shopwell\Helper::get_option( 'header_help_center_logo_height' );
		$logo_dimension = array(
			'width'  => $logo_width,
			'height' => $logo_height,
		);
		if ( ! empty( $logo_dimension ) && ( ! empty( $logo_dimension['width'] ) || ! empty( $logo_dimension['height'] ) ) ) {
			if ( isset( $logo_dimension['width'] ) ) {
				$dimension['width'] = $logo_dimension['width'];
			}
			if ( isset( $logo_dimension['height'] ) ) {
				$dimension['height'] = $logo_dimension['height'];
			}
		}
		return $dimension;
	}
}
