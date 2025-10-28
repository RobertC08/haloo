<?php
/**
 * Hooks of Account.
 *
 * @package Shopwell
 */

namespace Shopwell\WooCommerce;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class of Account template.
 */
class Account {
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
		add_filter( 'shopwell_get_campaign_bar', array( $this, 'campaign_bar' ) );
		add_filter( 'shopwell_get_topbar', array( $this, 'topbar' ) );

		add_filter( 'shopwell_get_header_layout', array( $this, 'header_layout' ) );
		add_filter( 'shopwell_get_header_mobile_layout', array( $this, 'header_layout' ) );
		add_filter( 'shopwell_get_footer_layout', array( $this, 'footer_layout' ) );
		add_filter( 'shopwell_get_footer_mobile_layout', array( $this, 'footer_mobile_layout' ) );
		add_filter( 'shopwell_get_page_header_elements', array( $this, 'page_header_elements' ) );

		// header logo
		add_filter( 'shopwell_header_logo_dimension', array( $this, 'logo_dimension' ) );

		add_action( 'woocommerce_before_customer_login_form', array( $this, 'logo' ), 1 );

		add_action( 'woocommerce_before_account_navigation', array( $this, 'open_my_account_wrapper' ), 1 );
		add_action( 'woocommerce_before_account_navigation', array( $this, 'my_account' ) );

		add_action( 'woocommerce_after_account_navigation', array( $this, 'close_my_account_wrapper' ), 99 );

		add_filter( 'shopwell_wp_script_data', array( $this, 'account_script_data' ) );
	}

	/**
	 * Campaign Bar
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function campaign_bar( $status ) {
		if ( $this->is_login_page() ) {
			$status = false;
		}

		return $status;
	}

	/**
	 * TopBar
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function topbar( $status ) {
		if ( $this->is_login_page() ) {
			$status = false;
		}

		return $status;
	}

	/**
	 * Header Layout
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function header_layout( $layout ) {
		if ( $this->is_login_page() ) {
			$layout = false;
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
	public function footer_layout( $layout ) {
		if ( $this->is_login_page() ) {
			$not_log_in_footer_layout = \Shopwell\Helper::get_option( 'not_log_in_footer_layout' );
			$layout                   = ! empty( $not_log_in_footer_layout ) ? $not_log_in_footer_layout : $layout;
		}

		return $layout;
	}

	/**
	 * Mobile Footer Layout
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function footer_mobile_layout( $layout ) {
		if ( $this->is_login_page() ) {
			$not_log_in_footer_mobile = \Shopwell\Helper::get_option( 'not_log_in_footer_mobile' );
			$layout                   = ! empty( $not_log_in_footer_mobile ) ? $not_log_in_footer_mobile : $layout;
		}

		return $layout;
	}

	/**
	 * Page Header Elements
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function page_header_elements( $items ) {
		if ( $this->is_login_page() ) {
			$items = array();
		}

		return $items;
	}

	/**
	 * Show Logo
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function logo() {
		if ( $this->is_login_page() ) {
			$args = array();

			$args['type']    = \Shopwell\Helper::get_option( 'header_sign_in_logo_type' );
			$type            = \Shopwell\Helper::get_option( 'logo_type' );
			$args['type']    = $args['type'] != 'default' ? $args['type'] : $type;
			$args['display'] = 'dark';
			switch ( $args['type'] ) {
				case 'text':
					$args['logo'] = \Shopwell\Helper::get_option( 'header_sign_in_logo_text' );
					break;
				case 'svg':
					$args['logo'] = \Shopwell\Helper::get_option( 'header_sign_in_logo_svg' );
					break;
				case 'image':
					$args['logo'] = \Shopwell\Helper::get_option( 'header_sign_in_logo' );
					break;
			}

			get_template_part( 'template-parts/header/logo', '', $args );
		}
	}

	/**
	 * Header logo Type
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function logo_dimension( $dimension ) {
		if ( $this->is_login_page() ) {
			$logo_width     = \Shopwell\Helper::get_option( 'header_sign_in_logo_width' );
			$logo_height    = \Shopwell\Helper::get_option( 'header_sign_in_logo_height' );
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
		}

		return $dimension;
	}

	/**
	 * Open my account wrapper
	 *
	 * @return void
	 */
	public function open_my_account_wrapper() {
		echo '<div class="shopwell-myaccount__wrapper">';
	}

	/**
	 * Close my account wrapper
	 *
	 * @return void
	 */
	public function close_my_account_wrapper() {
		echo '</div>';
	}

	/**
	 * My account login
	 *
	 * @return void
	 */
	public function my_account() {
		$current_user = wp_get_current_user();

		if ( empty( $current_user ) ) {
			return;
		}

		printf(
			'<div class="shopwell-myaccount">
					%s
					<div class="shopwell-myaccount__user">
						<span class="text">%s</span>
						<span class="name">%s</span>
					</div>
				</div>',
			get_avatar( $current_user->ID, 60 ),
			esc_html__( 'Hello!', 'shopwell' ),
			esc_html( $current_user->display_name )
		);
	}


	/**
	 * Account script data.
	 *
	 * @since 1.0.0
	 *
	 * @param $data
	 *
	 * @return array
	 */
	public function account_script_data( $data ) {
		if ( $this->is_login_page() ) {
			$data['show_text'] = esc_html__( 'Show', 'shopwell' );
			$data['hide_text'] = esc_html__( 'Hide', 'shopwell' );
		}

		return $data;
	}

	public function is_login_page() {
		if ( ( function_exists( 'is_account_page' ) && is_account_page() ) && ! is_user_logged_in() && ( function_exists( 'is_lost_password_page' ) && ! is_lost_password_page() ) ) {
			return true;
		}

		return false;
	}
}
