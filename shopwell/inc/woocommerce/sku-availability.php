<?php
/**
 * SKU Availability Check
 * Verifies product availability via Market API before checkout submission
 *
 * @package Shopwell
 */

namespace Shopwell\WooCommerce;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class for SKU availability checking
 */
class Sku_Availability {
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
		// Enqueue scripts only on checkout page.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_checkout_scripts' ), 20 );

		// Register AJAX handlers.
		add_action( 'wp_ajax_check_sku_availability', array( $this, 'check_sku_availability' ) );
		add_action( 'wp_ajax_nopriv_check_sku_availability', array( $this, 'check_sku_availability' ) );
		add_action( 'wp_ajax_get_checkout_cart_skus', array( $this, 'get_checkout_cart_skus' ) );
		add_action( 'wp_ajax_nopriv_get_checkout_cart_skus', array( $this, 'get_checkout_cart_skus' ) );
	}

	/**
	 * Enqueue checkout scripts for SKU availability check.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function enqueue_checkout_scripts() {
		if ( ! function_exists( 'is_checkout' ) || ! is_checkout() ) {
			return;
		}

		wp_enqueue_script(
			'shopwell-checkout-sku-check',
			get_template_directory_uri() . '/assets/js/woocommerce/checkout.js',
			array( 'jquery', 'wc-checkout' ),
			'1.0.1',
			true
		);

		// Get cart SKUs for JavaScript.
		$cart_skus = array();
		if ( function_exists( 'WC' ) && WC()->cart ) {
			foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
				$product = $cart_item['data'];
				if ( $product ) {
					$sku = $product->get_sku();
					if ( ! empty( $sku ) ) {
						$cart_skus[] = array(
							'sku'          => $sku,
							'quantity'     => $cart_item['quantity'],
							'product_name' => $product->get_name(),
						);
					}
				}
			}
		}

		wp_localize_script(
			'shopwell-checkout-sku-check',
			'shopwellCheckoutSku',
			array(
				'ajaxUrl'           => admin_url( 'admin-ajax.php' ),
				'nonce'             => wp_create_nonce( 'check_sku_availability_nonce' ),
				'checkingText'      => esc_html__( 'Verificăm disponibilitatea produselor...', 'shopwell' ),
				'unavailableText'   => esc_html__( 'Nu mai este disponibil.', 'shopwell' ),
				'insufficientText'  => esc_html__( 'Cantitate insuficientă în stoc. Disponibil:', 'shopwell' ),
				'cartSkus'          => $cart_skus,
			)
		);
	}

	/**
	 * AJAX handler for checking SKU availability via Market API.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function check_sku_availability() {
		// Verify nonce.
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'check_sku_availability_nonce' ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Security check failed.', 'shopwell' ) ) );
			return;
		}

		// Get SKU from POST data.
		if ( ! isset( $_POST['sku'] ) || empty( $_POST['sku'] ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'SKU is required.', 'shopwell' ) ) );
			return;
		}

		$sku = sanitize_text_field( wp_unslash( $_POST['sku'] ) );

		// Get Market API base URL from options or constant.
		$api_base_url = defined( 'MARKET_API_BASE_URL' ) ? MARKET_API_BASE_URL : get_option( 'market_api_base_url', '' );

		if ( empty( $api_base_url ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Market API configuration missing.', 'shopwell' ) ) );
			return;
		}

		// Get Market API key from constant or option.
		$api_key = defined( 'MARKET_API_KEY' ) ? MARKET_API_KEY : get_option( 'market_api_key', '' );

		// Construct API endpoint.
		$api_url = trailingslashit( $api_base_url ) . 'api/v1/sku/' . $sku;

		// Prepare request headers.
		$headers = array(
			'accept' => '*/*',
		);

		// Add API key to headers if provided.
		if ( ! empty( $api_key ) ) {
			// Market API uses X-ApiKey header (case-sensitive, exact match with curl example)
			$headers['X-ApiKey'] = $api_key;
		}

		// Make API request.
		$response = wp_remote_get(
			$api_url,
			array(
				'timeout'     => 10,
				'httpversion' => '1.1',
				'sslverify'   => true,
				'headers'     => $headers,
			)
		);

		// Check for errors.
		if ( is_wp_error( $response ) ) {
			wp_send_json_error(
				array(
					'message' => esc_html__( 'Eroare la verificarea disponibilității produsului.', 'shopwell' ),
					'error'   => $response->get_error_message(),
				)
			);
			return;
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		$response_body = wp_remote_retrieve_body( $response );

		// Debug info for console logging.
		$debug_info = array(
			'request_url'     => $api_url,
			'request_headers' => $headers,
			'request_method' => 'GET',
			'sku'             => $sku,
			'response_code'   => $response_code,
			'response_body'   => $response_body,
		);

		// Handle different response codes.
		if ( 200 !== $response_code ) {
			$error_message = esc_html__( 'Produsul nu a fost găsit în sistem.', 'shopwell' );
			
			// Provide more specific error messages based on response code.
			switch ( $response_code ) {
				case 401:
					$error_message = esc_html__( 'Eroare de autentificare API. Verifică API key-ul.', 'shopwell' );
					break;
				case 403:
					$error_message = esc_html__( 'Acces interzis la API. Verifică permisiunile API key-ului.', 'shopwell' );
					break;
				case 404:
					$error_message = esc_html__( 'Produsul nu a fost găsit în sistem.', 'shopwell' );
					break;
				case 500:
				case 502:
				case 503:
					$error_message = esc_html__( 'Eroare server API. Te rugăm să încerci mai târziu.', 'shopwell' );
					break;
			}

			wp_send_json_error(
				array(
					'message'    => $error_message,
					'code'       => $response_code,
					'body'       => $response_body,
					'debug_info' => $debug_info, // Include debug info in error response.
				)
			);
			return;
		}

		// Decode JSON response.
		$api_data = json_decode( $response_body, true );

		if ( ! $api_data || ! isset( $api_data['quantity'] ) || ! isset( $api_data['price'] ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Răspuns invalid de la API.', 'shopwell' ) ) );
			return;
		}

		// Return success with availability data.
		wp_send_json_success(
			array(
				'sku'        => $sku,
				'quantity'   => intval( $api_data['quantity'] ),
				'price'      => floatval( $api_data['price'] ),
				'debug_info' => $debug_info, // Include debug info in success response.
			)
		);
	}

	/**
	 * AJAX handler for getting cart SKUs.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function get_checkout_cart_skus() {
		// Verify nonce.
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'check_sku_availability_nonce' ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Security check failed.', 'shopwell' ) ) );
			return;
		}

		if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Cart not available.', 'shopwell' ) ) );
			return;
		}

		$cart_skus = array();

		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$product = $cart_item['data'];
			
			if ( ! $product ) {
				continue;
			}

			$sku = $product->get_sku();
			
			if ( empty( $sku ) ) {
				continue;
			}

			$cart_skus[] = array(
				'sku'          => $sku,
				'quantity'     => $cart_item['quantity'],
				'product_id'   => $cart_item['product_id'],
				'variation_id' => isset( $cart_item['variation_id'] ) ? $cart_item['variation_id'] : 0,
				'product_name' => $product->get_name(),
			);
		}

		wp_send_json_success(
			array(
				'skus' => $cart_skus,
			)
		);
	}
}

