<?php
/**
 * Frontend functions and definitions.
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
class Frontend {
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
		add_filter( 'body_class', array( $this, 'body_classes' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		add_action( 'shopwell_after_site_content_open', array( $this, 'open_site_content_container' ) );
		add_action( 'shopwell_before_site_content_close', array( $this, 'close_site_content_container' ), 30 );

		add_filter( 'shopwell_get_sidebar', array( $this, 'has_sidebar' ) );

		add_action( 'elementor/theme/register_locations', array( $this, 'register_elementor_locations' ) );
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
		// Adds a class of hfeed to non-singular pages.
		if ( ! is_singular() ) {
			$classes[] = 'hfeed';
		}

		$classes[] = $this->content_layout();

		if ( Helper::get_option( 'shape_style' ) != 'default' ) {
			$classes[] = 'shopwell-shape--' . Helper::get_option( 'shape_style' );
		}

		if ( is_page() || \Shopwell\Helper::is_blog() || \Shopwell\Helper::is_catalog() ) {
			$top_spacing = get_post_meta( \Shopwell\Helper::get_post_ID(), 'shopwell_content_top_spacing', true );
			$top_spacing = \Shopwell\Helper::is_help_center_page() ? 'no' : $top_spacing;
			if ( ! empty( $top_spacing ) && $top_spacing != 'default' ) {
				$classes[] = sprintf( ' site-content-%s-top-spacing', $top_spacing );
			}

			$bottom_spacing = get_post_meta( \Shopwell\Helper::get_post_ID(), 'shopwell_content_bottom_spacing', true );
			$bottom_spacing = \Shopwell\Helper::is_help_center_page() ? 'no' : $bottom_spacing;
			if ( ! empty( $bottom_spacing ) && $bottom_spacing != 'default' ) {
				$classes[] = sprintf( ' site-content-%s-bottom-spacing', $bottom_spacing );
			}
		}

		if ( get_post_meta( \Shopwell\Helper::get_post_ID(), 'shopwell_hide_header_border', true ) ) {
			$classes[] = 'header-hide-border';
		}

		// Add a class of rtl background
		if ( intval( Helper::get_option( 'rtl_smart' ) ) && is_rtl() ) {
			$classes[] = 'shopwell-rtl-smart';
		}

		return $classes;
	}

	/**
	 * Check has sidebar
	 *
	 * @since 1.0.0
	 *
	 * @return boolean
	 */
	public function has_sidebar() {
		if ( $this->content_layout() != 'no-sidebar' ) {
			return true;
		}

		return false;
	}

	/**
	 * Get site layout
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function content_layout() {
		if ( is_search() ) {
			if ( ! is_active_sidebar( 'blog-sidebar' ) ) {
				$layout = 'no-sidebar';
			} else {
				$layout = 'content-sidebar';
			}
		} else {
			$layout = 'no-sidebar';
		}

		return apply_filters( 'shopwell_site_layout', $layout );
	}

	/**
	 * Print the open tags of site content container
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function open_site_content_container() {
		if ( Helper::is_built_with_elementor() ) {
			return;
		}
		$classes = '';
		if ( Helper::is_catalog() ) {
			$classes .= ' site-content-container';
		}
		echo '<div class="container clearfix ' . esc_attr( $classes ) . '">';
	}

	/**
	 * Print the close tags of site content container
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function close_site_content_container() {
		if ( Helper::is_built_with_elementor() ) {
			return;
		}
		echo '</div>';
	}


	/**
	 * Enqueue scripts and styles.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		$elementor_page = (bool) get_post_meta( get_the_ID(), '_elementor_edit_mode', true );

		if ( ! is_admin() && is_page() && is_active_sidebar( 'footer-widget' ) && class_exists( 'Elementor\Plugin' ) && $elementor_page ) {
			wp_enqueue_style( 'shopwell-block-library', get_template_directory_uri() . '/assets/css/block-style.css', array(), '20250107' );
		}

		if ( ! is_admin() ) {
			wp_enqueue_style( 'shopwell-all', get_template_directory_uri() . '/assets/css/all.css', array(), '20250107' );
		}

		// register swiper style if not already registered
		if ( ! wp_style_is( 'swiper', 'registered' ) ) {
			wp_register_style( 'swiper', get_template_directory_uri() . '/assets/css/swiper.css', array(), '20250107' );
		}
		wp_enqueue_style( 'swiper' );

		$style_file = is_rtl() ? 'style-rtl.css' : 'style.css';
		wp_enqueue_style( 'shopwell', apply_filters( 'shopwell_get_style_directory_uri', get_template_directory_uri() ) . '/' . $style_file, array(), '20250107' );

		do_action( 'shopwell_after_enqueue_style' );

		/**
		 * Register and enqueue scripts
		 */
		$debug = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		wp_enqueue_script( 'html5shiv', get_template_directory_uri() . '/assets/js/plugins/html5shiv.min.js', array(), '3.7.2' );
		wp_script_add_data( 'html5shiv', 'conditional', 'lt IE 9' );

		wp_enqueue_script( 'respond', get_template_directory_uri() . '/assets/js/plugins/respond.min.js', array(), '1.4.2' );
		wp_script_add_data( 'respond', 'conditional', 'lt IE 9' );

		wp_register_script( 'headroom', get_template_directory_uri() . '/assets/js/plugins/headroom.min.js', array(), '0.9.3', true );

		if ( 'none' != \Shopwell\Helper::get_option( 'header_sticky' ) && 'up' == \Shopwell\Helper::get_option( 'header_sticky_on' ) ) {
			wp_enqueue_script( 'headroom' );
		}
		wp_enqueue_script( 'notify', get_template_directory_uri() . '/assets/js/plugins/notify.min.js', array(), '1.0.0', true );

		// register swiper script if not already registered
		if ( ! wp_script_is( 'swiper', 'registered' ) ) {
			wp_register_script( 'swiper', get_template_directory_uri() . '/assets/js/plugins/swiper.min.js', array(), '20250105', true );
		}
		wp_enqueue_script( 'swiper' );

		wp_enqueue_script(
			'shopwell',
			get_template_directory_uri() . '/assets/js/scripts' . $debug . '.js',
			array(
				'jquery',
			),
			'20250105',
			true
		);

		if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
			wp_enqueue_script( 'comment-reply' );
		}
		$shop_url = '';
		if ( function_exists( 'wc_get_page_permalink' ) ) {
			$shop_url = wc_get_page_permalink( 'shop' );
		}
		if ( empty( $shop_url ) ) {
			$shop_url = home_url( '/shop/' );
		}

		$shopwell_data = array(
			'direction'                        => is_rtl() ? 'true' : 'false',
			'ajax_url'                         => class_exists( 'WC_AJAX' ) ? \WC_AJAX::get_endpoint( '%%endpoint%%' ) : '',
			'nonce'                            => wp_create_nonce( '_shopwell_nonce' ),
			'sticky_header'                    => \Shopwell\Header\Manager::has_sticky() ? true : false,
			'sticky_header_on'                 => \Shopwell\Helper::get_option( 'header_sticky_on' ),
			'blog_nav_ajax_url_change'         => Helper::get_option( 'blog_nav_ajax_url_change' ),
			'blog_archive_nav_ajax_url_change' => Helper::get_option( 'blog_archive_nav_ajax_url_change' ),
			'header_search_type'               => Helper::get_option( 'header_search_type' ),
			'header_ajax_search'               => boolval( Helper::get_option( 'header_search_ajax' ) ),
			'header_search_number'             => Helper::get_option( 'header_search_number' ),
			'header_breakpoint'                => intval( Helper::get_option( 'header_mobile_breakpoint' ) ),
			'navigation_cutoff'                => Helper::get_option( 'header_navigation_cutoff' ),
			'navigation_cutoff_upto'           => Helper::get_option( 'header_navigation_cutoff_upto' ),
			'navigation_cutoff_text'           => Helper::get_option( 'header_navigation_cutoff_text' ),
			'post_type'                        => \Shopwell\Header\Search::type(),
			'mobile_product_columns'           => Helper::get_option( 'mobile_product_columns' ),
			'shop_url'                         => $shop_url,
		);

		$shopwell_data = apply_filters( 'shopwell_wp_script_data', $shopwell_data );

		wp_localize_script(
			'shopwell',
			'shopwellData',
			$shopwell_data
		);

		// Enqueue google fonts.
		Helper::fonts()->enqueue_google_fonts();

		// Add additional theme styles.
		do_action( 'shopwell_enqueue_scripts' );
	}

	function register_elementor_locations( $elementor_theme_manager ) {
		$elementor_theme_manager->register_all_core_location();
	}
}
