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
class Footer {
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
		add_filter( 'shopwell_get_footer_layout', array( $this, 'layout' ) );
		add_filter( 'shopwell_get_footer_mobile_layout', array( $this, 'mobile_layout' ) );
	}

	/**
	 * Footer Layout
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function layout( $layout ) {
		$hc_version = \Shopwell\Helper::get_option( 'help_center_footer' );
		$layout     = ! empty( $hc_version ) ? $hc_version : $layout;

		return $layout;
	}

	/**
	 * Mobile Footer Layout
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function mobile_layout( $layout ) {
		$hc_version        = \Shopwell\Helper::get_option( 'help_center_footer' );
		$hc_mobile_version = \Shopwell\Helper::get_option( 'help_center_footer_mobile' );
		if ( ! empty( $hc_version ) ) {
			$layout = $hc_mobile_version;
		} else {
			$layout = ! empty( $hc_mobile_version ) ? $hc_mobile_version : $layout;
		}

		return $layout;
	}
}
