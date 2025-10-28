<?php

/**
 * WooCommerce Product additional settings.
 *
 * @package Shopwell
 */

namespace Shopwell\WooCommerce;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class of Product Settings
 */
class Product {
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
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ), 50 );

		// Advanced tab
		add_action( 'woocommerce_product_options_advanced', array( $this, 'product_advanced_options' ) );

		// Save product meta
		add_action( 'woocommerce_process_product_meta', array( $this, 'product_meta_fields_save' ) );

		add_action( 'wp_ajax_shopwell_wc_product_attributes', array( $this, 'wc_get_product_attributes' ) );
		add_action( 'wp_ajax_nopriv_shopwell_wc_product_attributes', array( $this, 'wc_get_product_attributes' ) );
	}

	/**
	 * Enqueue Scripts
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function enqueue_scripts( $hook ) {
		$screen = get_current_screen();
		if ( in_array( $hook, array( 'post.php', 'post-new.php' ) ) && $screen->post_type == 'product' ) {
			wp_enqueue_script( 'shopwell_wc_settings_js', get_template_directory_uri() . '/assets/js/backend/woocommerce.js', array( 'jquery' ), '20250105', true );
			wp_localize_script(
				'shopwell_wc_settings_js',
				'shopwell_wc_settings',
				array(
					'search_tag_nonce' => wp_create_nonce( 'search-tags' ),
				)
			);
		}
	}

	/**
	 * Add more options to advanced tab.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function product_advanced_options() {
		echo '<div class="options_group product-attributes" id="shopwell-product-attributes">';
			$this->get_product_attributes( get_the_ID() );
		echo '</div>';
	}

	/**
	 * product_meta_fields_save function.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $post_id
	 *
	 * @return void
	 */
	public function product_meta_fields_save( $post_id ) {
		if ( isset( $_POST['shopwell_product_attribute'] ) ) {
			$woo_data = $_POST['shopwell_product_attribute'];
			update_post_meta( $post_id, 'shopwell_product_attribute', $woo_data );
		}

		if ( isset( $_POST['shopwell_product_attribute_number'] ) ) {
			$woo_data = intval( $_POST['shopwell_product_attribute_number'] );
			$woo_data = ! $woo_data ? '' : $woo_data;
			update_post_meta( $post_id, 'shopwell_product_attribute_number', $woo_data );
		}
	}

	/**
	 * Get Product Attributes AJAX function.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function wc_get_product_attributes() {
		$post_id = $_POST['post_id'];

		if ( empty( $post_id ) ) {
			return;
		}
		ob_start();
		$this->get_product_attributes( $post_id );
		$response = ob_get_clean();
		wp_send_json_success( $response );
		die();
	}

	/**
	 * Get Product Attributes function.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function get_product_attributes( $post_id ) {
		$product_object = wc_get_product( $post_id );
		if ( ! $product_object ) {
			return;
		}
		$attributes = $product_object->get_attributes();

		if ( ! $attributes ) {
			return;
		}
		$options         = array();
		$options['']     = esc_html__( 'Default', 'shopwell' );
		$options['none'] = esc_html__( 'None', 'shopwell' );
		foreach ( $attributes as $attribute ) {
			$options[ sanitize_title( $attribute['name'] ) ] = wc_attribute_label( $attribute['name'] );
		}
		woocommerce_wp_radio(
			array(
				'id'          => 'shopwell_product_attribute',
				'label'       => esc_html__( 'Product Attribute', 'shopwell' ),
				'desc_tip'    => true,
				'description' => esc_html__( 'Show the product attribute in the product card', 'shopwell' ),
				'options'     => $options,
			)
		);

		woocommerce_wp_text_input(
			array(
				'id'          => 'shopwell_product_attribute_number',
				'label'       => esc_html__( 'Product Attribute Number', 'shopwell' ),
				'desc_tip'    => true,
				'description' => esc_html__( 'Show number of the product attribute in the product card', 'shopwell' ),
				'options'     => $options,
			)
		);
	}
}
