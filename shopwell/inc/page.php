<?php
/**
 * Header Main functions and definitions.
 *
 * @package Shopwell
 */

namespace Shopwell;

use Shopwell\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Header Main initial
 */
class Page {

	/**
	 * Instance
	 *
	 * @var $instance
	 */
	protected static $instance = null;

	protected static $custom_header_layout = null;

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
		add_filter( 'shopwell_get_header_layout', array( $this, 'header_layout' ) );
		add_filter( 'shopwell_get_header_mobile_layout', array( $this, 'header_layout' ) );
		add_filter( 'shopwell_get_primary_menu', array( $this, 'primary_menu' ) );
		add_filter( 'shopwell_header_logo_type', array( $this, 'logo_type' ) );
		add_filter( 'shopwell_header_logo', array( $this, 'logo' ), 20, 2 );
		add_filter( 'shopwell_header_logo_light', array( $this, 'logo_light' ), 20, 2 );
		add_filter( 'shopwell_header_logo_dimension', array( $this, 'logo_dimension' ) );
		add_filter( 'shopwell_get_footer_layout', array( $this, 'footer_layout' ) );
		add_filter( 'shopwell_get_footer_mobile_layout', array( $this, 'mobile_footer_layout' ) );
		add_filter( 'shopwell_get_topbar', array( $this, 'get_topbar' ) );
		add_filter( 'shopwell_get_campaign_bar', array( $this, 'get_campaign_bar' ) );
	}

	/**
	 * Header background
	 *
	 * @since  1.0.0
	 *
	 * @return string
	 */
	public function header_background() {
		$header_background = get_post_meta( \Shopwell\Helper::get_post_ID(), 'shopwell_header_background', true );
		$header_layout     = $this->custom_header_layout();
		if ( $header_layout == 'page' ) {
			if ( empty( $header_background ) ) {
				$header_background = Helper::get_option( 'header_page_transparent' ) ? 'transparent' : $header_background;
			}
		}

		return $header_background;
	}

	/**
	 * Header Color Text
	 *
	 * @since  1.0.0
	 *
	 * @return string
	 */
	public function header_text_color( $text_color ) {
		$text_color    = get_post_meta( \Shopwell\Helper::get_post_ID(), 'shopwell_header_text_color', true );
		$header_layout = $this->custom_header_layout();
		if ( $header_layout == 'page' ) {
			$header_background = get_post_meta( \Shopwell\Helper::get_post_ID(), 'shopwell_header_background', true );
			$text_color        = empty( $text_color ) || empty( $header_background ) ? Helper::get_option( 'header_page_text_color' ) : $text_color;
		}

		return $text_color;
	}

	/**
	 * Get custom header Layout
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function custom_header_layout() {
		if ( isset( self::$custom_header_layout ) ) {
			return self::$custom_header_layout;
		}

		self::$custom_header_layout = get_post_meta( \Shopwell\Helper::get_post_ID(), 'header_layout', true );

		return self::$custom_header_layout;
	}


	/**
	 * Header Layout
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function header_layout( $layout ) {
		$header_layout = $this->custom_header_layout();
		if ( $header_layout == 'page' ) {
			$header_layout = Helper::get_option( 'page_header_version' );
			$layout        = ! empty( $header_layout ) ? $header_layout : $layout;
		} elseif ( ! empty( $header_layout ) && $header_layout != 'global' ) {
			$layout = $header_layout;
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
		$header_layout = $this->custom_header_layout();
		if ( $header_layout == 'page' ) {
			$primary_menu_id = Helper::get_option( 'page_primary_menu' );
		} else {
			$primary_menu_id = get_post_meta( Helper::get_post_ID(), 'shopwell_page_primary_menu', true );
		}
		$menu_id = ! empty( $primary_menu_id ) ? $primary_menu_id : $menu_id;

		return $menu_id;
	}

	/**
	 * Header logo Type
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function logo_type( $type ) {
		$header_layout = $this->custom_header_layout();
		$logo_type     = get_post_meta( \Shopwell\Helper::get_post_ID(), 'header_logo_type', true );
		if ( $header_layout == 'page' ) {
			$logo_type = Helper::get_option( 'header_page_logo_type' );
			$logo_type = $logo_type != 'default' ? $logo_type : $type;
		}

		$type = ! empty( $logo_type ) ? $logo_type : $type;

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
		$header_layout = $this->custom_header_layout();
		$logo_type     = get_post_meta( \Shopwell\Helper::get_post_ID(), 'header_logo_type', true );
		if ( $header_layout == 'page' ) {
			$logo_type = Helper::get_option( 'header_page_logo_type' );
		}
		$custom_logo = $this->custom_logo( $logo, $logo_type );
		$logo        = empty( $custom_logo ) ? $logo : $custom_logo;
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
		if ( $this->header_background() != 'transparent' ) {
			return $logo_light;
		}
		$header_layout = $this->custom_header_layout();
		if ( 'svg' == $type ) {
			$logo_light = get_post_meta( \Shopwell\Helper::get_post_ID(), 'header_logo_svg_light', true );
			if ( $header_layout == 'page' ) {
				$logo_light = ! empty( $logo_light ) ? $logo_light : Helper::get_option( 'header_page_logo_svg_light' );
			}
		} elseif ( 'image' == $type ) {
			$image_id = get_post_meta( \Shopwell\Helper::get_post_ID(), 'header_logo_image_light', true );
			if ( $image_id ) {
				$image      = wp_get_attachment_image_src( $image_id, 'full' );
				$logo_light = $image ? $image[0] : '';
			}

			if ( $header_layout == 'page' ) {
				$logo_light = ! empty( $logo_light ) ? $logo_light : Helper::get_option( 'header_page_logo_light' );
			}
		}

		return $logo_light;
	}

	/**
	 * Header Custom logo
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function custom_logo( $type = '' ) {
		$custom_logo   = '';
		$type          = $this->logo_type( $type );
		$header_layout = $this->custom_header_layout();
		if ( 'text' == $type ) {
			$custom_logo = get_post_meta( \Shopwell\Helper::get_post_ID(), 'header_logo_text', true );
			if ( $header_layout == 'page' ) {
				$custom_logo = empty( $custom_logo ) ? Helper::get_option( 'header_page_logo_text' ) : $custom_logo;
			}
		} elseif ( 'svg' == $type ) {
			$custom_logo = get_post_meta( \Shopwell\Helper::get_post_ID(), 'header_logo_svg', true );
			if ( $header_layout == 'page' ) {
				$custom_logo = empty( $custom_logo ) ? Helper::get_option( 'header_page_logo_svg' ) : $custom_logo;
			}
		} elseif ( 'image' == $type ) {
			if ( $image_id = absint( get_post_meta( \Shopwell\Helper::get_post_ID(), 'header_logo_image', true ) ) ) {
				$image       = wp_get_attachment_image_src( $image_id, 'full' );
				$custom_logo = $image ? $image[0] : '';
			}
			if ( $header_layout == 'page' ) {
				$custom_logo = empty( $custom_logo ) ? Helper::get_option( 'header_page_logo' ) : $custom_logo;
			}
		}
		return $custom_logo;
	}

	/**
	 * Header logo Type
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function logo_dimension( $dimension ) {
		$header_layout = $this->custom_header_layout();
		if ( $header_layout == 'page' ) {
			$logo_width  = Helper::get_option( 'header_page_logo_width' );
			$logo_height = Helper::get_option( 'header_page_logo_height' );
			if ( $logo_width ) {
				$dimension['width'] = $logo_width;
			}
			if ( $logo_height ) {
				$dimension['height'] = $logo_height;
			}
		} else {
			$custom_logo_width = get_post_meta( \Shopwell\Helper::get_post_ID(), 'header_logo_width', true );
			if ( ! empty( $custom_logo_width ) ) {
				$dimension['width'] = $custom_logo_width;
			}

			$custom_logo_height = get_post_meta( \Shopwell\Helper::get_post_ID(), 'header_logo_height', true );
			if ( ! empty( $custom_logo_height ) ) {
				$dimension['height'] = $custom_logo_height;
			}
		}

		return $dimension;
	}

	/**
	 * Footer Layout
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function footer_layout( $layout ) {
		$footer_layout = get_post_meta( \Shopwell\Helper::get_post_ID(), 'footer_layout', true );
		if ( $footer_layout == 'page' ) {
			$footer_layout = Helper::get_option( 'page_footer_version' );
			$layout        = ! empty( $footer_layout ) ? $footer_layout : $layout;
		} elseif ( ! empty( $footer_layout ) && $footer_layout != 'global' ) {
			$layout = $footer_layout;
		}

		return $layout;
	}

	/**
	 * Footer Layout
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function mobile_footer_layout( $layout ) {
		$footer_layout = get_post_meta( \Shopwell\Helper::get_post_ID(), 'footer_mobile_layout', true );
		if ( $footer_layout == 'page' ) {
			$footer_layout = Helper::get_option( 'page_mobile_footer_version' );
			$layout        = ! empty( $footer_layout ) ? $footer_layout : $layout;
		} elseif ( ! empty( $footer_layout ) && $footer_layout != 'global' ) {
			$layout = $footer_layout;
		}

		return $layout;
	}

		/**
		 * Footer Layout
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
	public function get_topbar( $topbar ) {
		$header_layout = $this->custom_header_layout();
		if ( $header_layout == 'page' ) {
			if ( ! empty( Helper::get_option( 'header_page_hide_topbar' ) ) ) {
				$topbar = false;
			}
		} elseif ( get_post_meta( \Shopwell\Helper::get_post_ID(), 'shopwell_hide_topbar', true ) ) {
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
		$header_layout = $this->custom_header_layout();
		if ( $header_layout == 'page' ) {
			if ( ! empty( Helper::get_option( 'header_page_hide_campaign_bar' ) ) ) {
				$campaign_bar = false;
			}
		} elseif ( get_post_meta( \Shopwell\Helper::get_post_ID(), 'shopwell_hide_campain_bar', true ) ) {
				$campaign_bar = false;
		}

		return $campaign_bar;
	}
}
