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
class Track_Order {
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
		// Option of order tracking page.
		add_filter( 'woocommerce_get_settings_checkout', array( $this, 'order_tracking_setting' ) );
		add_filter( 'woocommerce_get_settings_advanced', array( $this, 'order_tracking_setting' ) );
	}

	/*
	 * Add setting for order tracking page.
	 *
	 * @param array $settings Checkout settings.
	 * @return array
	 */
	public static function order_tracking_setting( $settings ) {
		$new_settings = array();

		foreach ( $settings as $index => $setting ) {
			$new_settings[ $index ] = $setting;

			if ( isset( $setting['id'] ) && 'woocommerce_terms_page_id' == $setting['id'] ) {
				$new_settings['order_tracking_page_id'] = array(
					'title'    => esc_html__( 'Order Tracking Page', 'shopwell' ),
					'desc'     => esc_html__( 'Page content: [woocommerce_order_tracking]', 'shopwell' ),
					'id'       => 'order_tracking_page_id',
					'type'     => 'single_select_page',
					'class'    => 'wc-enhanced-select-nostd',
					'css'      => 'min-width:300px;',
					'desc_tip' => true,
				);
			}
		}

		return $new_settings;
	}
}
