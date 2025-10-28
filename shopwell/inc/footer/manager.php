<?php
/**
 * Footer functions and definitions.
 *
 * @package Shopwell
 */

namespace Shopwell\Footer;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Footer initial
 */
class Manager {
		/**
		 * Instance
		 *
		 * @var $instance
		 */
	protected static $instance = null;


	/**
	 * Footer ID
	 *
	 * @var $post_id
	 */
	protected static $footer_id = null;


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
		add_action( 'shopwell_footer', array( $this, 'footer' ) );

		add_action( 'wp_footer', array( $this, 'panel_items' ) );
		add_action( 'shopwell_after_site', array( $this, 'modals_items' ) );

		// Scripts and styles.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		add_action( 'shopwell_after_close_site_footer', array( $this, 'gotop_button' ) );
	}


	/**
	 * Get the footer.
	 *
	 * @return string
	 */
	public function footer() {
		if ( ! class_exists( 'Elementor\Plugin' ) ) {
			return;
		}

		$show_footer = ! get_post_meta( \Shopwell\Helper::get_post_ID(), 'shopwell_hide_footer_section', true );
		if ( ! apply_filters( 'shopwell_get_footer', $show_footer ) ) {
			return;
		}

		$elementor_instance = \Elementor\Plugin::instance();

		$footer_id        = $this->get_layout();
		$footer_mobile_id = $this->get_mobile_layout();

		if ( ! empty( $footer_id ) && get_post_type( $footer_id ) == 'shopwell_footer' ) {
			printf(
				'<div class="footer-main %s">%s</div>',
				empty( $footer_mobile_id ) ? 'show-on-mobile' : '',
				$elementor_instance->frontend->get_builder_content_for_display( intval( $footer_id ) )
			);
		}

		if ( ! empty( $footer_mobile_id ) && get_post_type( $footer_mobile_id ) == 'shopwell_footer' ) {
			printf( '<div class="footer-mobile">%s</div>', $elementor_instance->frontend->get_builder_content_for_display( intval( $footer_mobile_id ) ) );
		}
	}

	/**
	 * Panel items
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function panel_items() {
		$items = (array) \Shopwell\Theme::get_prop( 'panels' );

		if ( empty( $items ) ) {
			return;
		}

		foreach ( $items as $item ) {
			$args = array();

			switch ( $item ) {
				case 'mobile-menu':
					$args['list_items']       = (array) \Shopwell\Helper::get_option( 'header_mobile_menu_items' );
					$args['custom_menu_id']   = \Shopwell\Helper::get_option( 'header_mobile_custom_menu' );
					$args['primary_menu_id']  = \Shopwell\Helper::get_option( 'header_mobile_primary_menu' );
					$args['category_menu_id'] = \Shopwell\Helper::get_option( 'header_mobile_category_menu' );
					$args['data_target']      = $item . '-panel';
					$args['open_submenus']    = \Shopwell\Helper::get_option( 'header_mobile_open_submenus' );
					$args['account_info']     = \Shopwell\Helper::get_option( 'header_mobile_account_info' ) ? true : false;
					$item                     = 'hamburger';
					break;

				case 'mobile-header-v11-menu':
					$list_items              = array(
						//array( 'item' => 'search' ),
						array( 'item' => 'primary-menu' ),
						array( 'item' => 'socials' ),
						array( 'item' => 'preferences' ),
					);
					$args['list_items']      = apply_filters( 'shopwell_blog_mobile_menu_items', $list_items );
					$args['primary_menu_id'] = apply_filters( 'shopwell_get_primary_menu', '' );
					$args['data_target']     = $item . '-panel';
					$item                    = 'hamburger-header-v11';
					break;

				case 'mobile-header-v12-menu':
					$list_items              = array(
						array( 'item' => 'primary-menu' ),
					);
					$args['list_items']      = apply_filters( 'shopwell_page_mobile_menu_items', $list_items );
					$args['primary_menu_id'] = apply_filters( 'shopwell_get_primary_menu', '' );
					$args['data_target']     = $item . '-panel';
					$item                    = 'hamburger-header-v12';
					break;

				case 'hamburger':
					$args['list_items']       = (array) \Shopwell\Helper::get_option( 'header_hamburger_menu_items' );
					$args['custom_menu_id']   = \Shopwell\Helper::get_option( 'header_hamburger_custom_menu' );
					$args['primary_menu_id']  = \Shopwell\Helper::get_option( 'header_hamburger_primary_menu' );
					$args['category_menu_id'] = \Shopwell\Helper::get_option( 'header_hamburger_category_menu' );
					$args['data_target']      = $item . '-panel';
					$args['account_info']     = \Shopwell\Helper::get_option( 'header_hamburger_account_info' ) ? true : false;
					break;

				case 'category-menu':
					$args['list_items']       = array( array( 'item' => 'category-menu' ) );
					$args['category_menu_id'] = \Shopwell\Helper::get_option( 'mobile_navigation_bar_category_menu' );
					$args['data_target']      = $item . '-panel';
					$args['account_info']     = false;
					$item                     = 'hamburger';
					break;
			}

			get_template_part( 'template-parts/panels/' . $item, '', $args );
		}
	}


	/**
	 * Modal items
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function modals_items() {
		$items = (array) \Shopwell\Theme::get_prop( 'modals' );

		if ( empty( $items ) ) {
			return;
		}

		foreach ( $items as $item ) {
			get_template_part( 'template-parts/modals/' . $item );
		}

		if ( in_array( 'preferences', $items ) ) {
			$this->enqueue_select2();
		}
	}

	private function enqueue_select2() {
		wp_enqueue_script( 'select2' );
		wp_enqueue_style( 'select2' );
	}

	/**
	 * Enqueue styles and scripts.
	 */
	public function enqueue_scripts() {
		$footer_id        = $this->get_layout();
		$footer_mobile_id = $this->get_mobile_layout();

		if ( ! empty( $footer_id ) ) {
			$css_file = '';
			if ( class_exists( '\Elementor\Core\Files\CSS\Post' ) ) {
				$css_file = new \Elementor\Core\Files\CSS\Post( $footer_id );
			} elseif ( class_exists( '\Elementor\Post_CSS_File' ) ) {
				$css_file = new \Elementor\Post_CSS_File( $footer_id );
			}
			if ( $css_file ) {
				$css_file->enqueue();
			}
		}

		if ( ! empty( $footer_mobile_id ) && $footer_mobile_id != $footer_id ) {
			$css_file = '';
			if ( class_exists( '\Elementor\Core\Files\CSS\Post' ) ) {
				$css_file = new \Elementor\Core\Files\CSS\Post( $footer_mobile_id );
			} elseif ( class_exists( '\Elementor\Post_CSS_File' ) ) {
				$css_file = new \Elementor\Post_CSS_File( $footer_mobile_id );
			}

			if ( $css_file ) {
				$css_file->enqueue();
			}
		}
	}

	/**
	 * Get the footer layout.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public static function get_layout() {
		if ( isset( self::$footer_id ) ) {
			return self::$footer_id;
		}

		$footer_id = \Shopwell\Helper::get_option( 'footer_version' );

		self::$footer_id = apply_filters( 'shopwell_get_footer_layout', $footer_id );

		return self::$footer_id;
	}

	/**
	 * Get the Mobile Footer layout.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_mobile_layout() {
		$layout = \Shopwell\Helper::get_option( 'footer_mobile_version' );
		return apply_filters( 'shopwell_get_footer_mobile_layout', $layout );
	}

	/**
	 * Add this back-to-top button to footer
	 *
	 * @since 1.0.0
	 *
	 * @return  void
	 */
	public function gotop_button() {
		if ( apply_filters( 'shopwell_get_back_to_top', \Shopwell\Helper::get_option( 'backtotop' ) ) ) {
			echo '<a href="#page" id="gotop">' . \Shopwell\Icon::get_svg( 'top' ) . '</a>';
		}
	}
}
