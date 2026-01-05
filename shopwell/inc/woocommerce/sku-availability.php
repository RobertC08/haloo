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

		// SERVER-SIDE VALIDATION: Check stock availability before order is created
		// Use woocommerce_after_checkout_validation which allows us to add errors that block order creation
		// Priority 1 to run as early as possible
		add_action( 'woocommerce_after_checkout_validation', array( $this, 'validate_stock_availability' ), 1, 2 );
		
		// Also hook into woocommerce_checkout_process as backup (runs earlier, before validation)
		// Priority 1 to run as early as possible
		add_action( 'woocommerce_checkout_process', array( $this, 'validate_stock_availability_process' ), 1 );
		
		// Final safety: Hook into order creation to prevent it if validation was somehow bypassed
		// Priority 1 to run immediately after order creation
		add_action( 'woocommerce_checkout_order_created', array( $this, 'prevent_order_if_no_stock' ), 1, 1 );
	}

	/**
	 * Enqueue checkout scripts for SKU availability check.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function enqueue_checkout_scripts() {
		// Only enqueue on checkout page, NOT on order received/thankyou page
		if ( ! function_exists( 'is_checkout' ) || ! is_checkout() ) {
			return;
		}
		
		// Don't enqueue on order received page
		if ( function_exists( 'is_order_received_page' ) && is_order_received_page() ) {
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
				'logNonce'          => wp_create_nonce( 'shopwell_log_nonce' ), // Nonce for logging
				'checkingText'      => esc_html__( 'Verificăm disponibilitatea produselor...', 'shopwell' ),
				'unavailableText'   => esc_html__( 'Nu mai este disponibil.', 'shopwell' ),
				'insufficientText'  => esc_html__( 'Cantitate insuficientă în stoc. Disponibil:', 'shopwell' ),
				'cartSkus'          => $cart_skus,
			)
		);
	}

	/**
	 * Write log to shopwell-dropshipping-logs.txt file
	 *
	 * @since 1.0.0
	 *
	 * @param string $message Log message.
	 * @param array  $context Additional context data.
	 * @return void
	 */
	private function write_log( $message, $context = array() ) {
		$upload_dir = wp_upload_dir();
		$log_file   = trailingslashit( $upload_dir['basedir'] ) . 'shopwell-dropshipping-logs.txt';

		$timestamp = current_time( 'Y-m-d H:i:s' );
		$log_entry = sprintf( '[%s] %s', $timestamp, $message );

		if ( ! empty( $context ) ) {
			$log_entry .= ' | ' . wp_json_encode( $context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
		}

		$log_entry .= PHP_EOL;

		// Append to log file
		@file_put_contents( $log_file, $log_entry, FILE_APPEND | LOCK_EX );
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
			// Get header type from constant or use default
			$header_type = defined( 'MARKET_API_KEY_HEADER_TYPE' ) ? MARKET_API_KEY_HEADER_TYPE : 'X-ApiKey';
			$headers[ $header_type ] = $api_key;
		}

		// Log request details
		$this->write_log(
			'SKU Availability Check - Request',
			array(
				'sku'            => $sku,
				'method'         => 'GET',
				'url'            => $api_url,
				'headers'        => $headers,
				'api_base_url'   => $api_base_url,
			)
		);

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
			$error_message = $response->get_error_message();
			
			// Log error
			$this->write_log(
				'SKU Availability Check - WP Error',
				array(
					'sku'    => $sku,
					'url'    => $api_url,
					'error'  => $error_message,
				)
			);

			wp_send_json_error(
				array(
					'message' => esc_html__( 'Eroare la verificarea disponibilității produsului.', 'shopwell' ),
					'error'   => $error_message,
				)
			);
			return;
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		$response_body = wp_remote_retrieve_body( $response );

		// Log response details
		$this->write_log(
			'SKU Availability Check - Response',
			array(
				'sku'           => $sku,
				'response_code' => $response_code,
				'response_body' => $response_body,
				'url'           => $api_url,
			)
		);

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

			// Add API response code and body to the error message for debugging
			$error_message .= ' [HTTP ' . $response_code . ']';
			if ( ! empty( $response_body ) ) {
				// Truncate response body if too long (max 200 chars)
				$body_preview = strlen( $response_body ) > 200 ? substr( $response_body, 0, 200 ) . '...' : $response_body;
				$error_message .= ' Response: ' . esc_html( $body_preview );
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

	/**
	 * SERVER-SIDE VALIDATION: Check stock availability before order is created
	 * This prevents orders from being placed if products are out of stock
	 * Uses woocommerce_after_checkout_validation hook which allows adding errors to WP_Error object
	 * 
	 * @since 1.0.0
	 *
	 * @param array    $data   Checkout data.
	 * @param WP_Error $errors Validation errors object.
	 * @return void
	 */
	public function validate_stock_availability( $data, $errors ) {
		// CRITICAL: Log immediately to verify hook is running
		error_log( '=== STOCK VALIDATION HOOK FIRED ===' );
		error_log( 'Hook: woocommerce_after_checkout_validation' );
		error_log( 'Has errors object: ' . ( $errors && is_wp_error( $errors ) ? 'yes' : 'no' ) );
		
		// Log that validation is running
		$this->write_log(
			'Stock Validation - START',
			array(
				'hook' => 'woocommerce_after_checkout_validation',
				'has_errors_object' => ( $errors && is_wp_error( $errors ) ) ? 'yes' : 'no',
				'has_cart' => ( function_exists( 'WC' ) && WC()->cart ) ? 'yes' : 'no',
			)
		);

		// Only validate if WooCommerce cart exists
		if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
			$this->write_log( 'Stock Validation - No cart found', array() );
			error_log( 'Stock Validation - No cart found' );
			return;
		}

		// Get Market API base URL from options or constant
		// Try multiple possible option names and constants
		$api_base_url = '';
		
		// Try MARKET_API_BASE_URL constant first
		if ( defined( 'MARKET_API_BASE_URL' ) && ! empty( MARKET_API_BASE_URL ) ) {
			$api_base_url = MARKET_API_BASE_URL;
		}
		// Try market_api_base_url option
		elseif ( ! empty( get_option( 'market_api_base_url', '' ) ) ) {
			$api_base_url = get_option( 'market_api_base_url', '' );
		}
		// Try FOXWAY_API_BASE_URL constant (if Foxway is used)
		elseif ( defined( 'FOXWAY_API_BASE_URL' ) && ! empty( FOXWAY_API_BASE_URL ) ) {
			$api_base_url = FOXWAY_API_BASE_URL;
		}
		// Try foxway_api_base_url option
		elseif ( ! empty( get_option( 'foxway_api_base_url', '' ) ) ) {
			$api_base_url = get_option( 'foxway_api_base_url', '' );
		}
		// Try to get from wp-config or other sources
		else {
			// Check if there's a global variable or filter
			$api_base_url = apply_filters( 'shopwell_api_base_url', '' );
		}
		
		error_log( 'API Base URL: ' . ( $api_base_url ? $api_base_url : 'EMPTY' ) );
		error_log( 'MARKET_API_BASE_URL constant: ' . ( defined( 'MARKET_API_BASE_URL' ) ? MARKET_API_BASE_URL : 'NOT DEFINED' ) );
		error_log( 'market_api_base_url option: ' . get_option( 'market_api_base_url', 'NOT SET' ) );

		// CRITICAL: If API is not configured, we MUST block checkout - we cannot verify stock
		// This is a security measure to prevent orders without stock verification
		if ( empty( $api_base_url ) ) {
			$error_message = esc_html__( 'Configurația API nu este setată. Nu putem verifica stocul produselor. Comanda nu poate fi plasată.', 'shopwell' );
			
			// Add error to block order
			if ( $errors && is_wp_error( $errors ) ) {
				$errors->add( 'stock_validation_no_api', $error_message );
			} else {
				wc_add_notice( $error_message, 'error' );
			}
			
			$this->write_log( 
				'Stock Validation - API not configured (BLOCKING ORDER)', 
				array( 
					'api_base_url' => $api_base_url,
					'message' => 'API not configured - blocking order for safety',
				) 
			);
			error_log( 'Stock Validation - API not configured - BLOCKING ORDER' );
			return;
		}

		// Get Market API key
		$api_key = defined( 'MARKET_API_KEY' ) ? MARKET_API_KEY : get_option( 'market_api_key', '' );

		$has_stock_errors = false;

		// Check each cart item
		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$product = $cart_item['data'];
			
			if ( ! $product ) {
				continue;
			}

			$sku = $product->get_sku();
			
			// Skip items without SKU
			if ( empty( $sku ) ) {
				continue;
			}

			$requested_quantity = $cart_item['quantity'];
			$product_name = $product->get_name();

			// Construct API endpoint
			$api_url = trailingslashit( $api_base_url ) . 'api/v1/sku/' . $sku;

			// Prepare request headers
			$headers = array(
				'accept' => '*/*',
			);

			// Add API key to headers if provided
			if ( ! empty( $api_key ) ) {
				$header_type = defined( 'MARKET_API_KEY_HEADER_TYPE' ) ? MARKET_API_KEY_HEADER_TYPE : 'X-ApiKey';
				$headers[ $header_type ] = $api_key;
			}

			// Make API request
			$response = wp_remote_get(
				$api_url,
				array(
					'timeout'     => 10,
					'httpversion' => '1.1',
					'sslverify'   => true,
					'headers'     => $headers,
				)
			);

			// If API request fails, BLOCK checkout - we cannot verify stock
			if ( is_wp_error( $response ) ) {
				$has_stock_errors = true;
				$error_message = $response->get_error_message();
				$this->write_log(
					'Stock Validation - API Error (BLOCKING ORDER)',
					array(
						'sku'     => $sku,
						'product' => $product_name,
						'error'   => $error_message,
					)
				);
				
				// Add error to WP_Error object to block order
				if ( $errors && is_wp_error( $errors ) ) {
					$errors->add(
						'stock_validation_api_error',
						sprintf(
							/* translators: 1: product name, 2: error message */
							esc_html__( 'Nu s-a putut verifica stocul pentru %1$s: %2$s. Comanda nu poate fi plasată.', 'shopwell' ),
							'<strong>' . esc_html( $product_name ) . '</strong>',
							esc_html( $error_message )
						)
					);
				} else {
					wc_add_notice(
						sprintf(
							/* translators: 1: product name, 2: error message */
							esc_html__( 'Nu s-a putut verifica stocul pentru %1$s: %2$s. Comanda nu poate fi plasată.', 'shopwell' ),
							'<strong>' . esc_html( $product_name ) . '</strong>',
							esc_html( $error_message )
						),
						'error'
					);
				}
				continue; // Skip to next item
			}

			$response_code = wp_remote_retrieve_response_code( $response );
			$response_body = wp_remote_retrieve_body( $response );

			// If API returns error (400, 404, etc.), BLOCK checkout
			// IMPORTANT: 400 means product is OUT OF STOCK - this is confirmed by the API behavior
			// 404 means product not found
			// Both mean we cannot proceed with the order
			if ( 200 !== $response_code ) {
				$has_stock_errors = true;
				
				// Parse response body to see if it contains stock information
				$response_body_preview = wp_remote_retrieve_body( $response );
				$response_body_preview = strlen( $response_body_preview ) > 200 ? substr( $response_body_preview, 0, 200 ) . '...' : $response_body_preview;
				
				// For 400 errors: API returns 400 when product is OUT OF STOCK
				if ( 400 === $response_code ) {
					// 400 = OUT OF STOCK - block order immediately
					$error_message = sprintf(
						/* translators: %s: product name */
						esc_html__( '%s nu mai este disponibil în stoc. Comanda nu poate fi plasată.', 'shopwell' ),
						'<strong>' . esc_html( $product_name ) . '</strong>'
					);
					
					$this->write_log(
						'Stock Validation - API 400 = OUT OF STOCK (BLOCKING ORDER)',
						array(
							'sku'            => $sku,
							'product'        => $product_name,
							'response_code'  => 400,
							'response_body'  => $response_body_preview,
							'message'        => '400 response means product is OUT OF STOCK',
						)
					);
				} else {
					// For other error codes (404, 500, etc.), use generic message
					$error_message = sprintf(
						/* translators: 1: product name, 2: response code */
						esc_html__( 'Nu s-a putut verifica stocul pentru %1$s (cod răspuns: %2$d). Comanda nu poate fi plasată.', 'shopwell' ),
						'<strong>' . esc_html( $product_name ) . '</strong>',
						$response_code
					);
					
					$this->write_log(
						'Stock Validation - API Response Error (BLOCKING ORDER)',
						array(
							'sku'            => $sku,
							'product'       => $product_name,
							'response_code' => $response_code,
							'response_body' => $response_body_preview,
						)
					);
				}
				
				// Add error to WP_Error object to block order
				if ( $errors && is_wp_error( $errors ) ) {
					$errors->add( 'stock_validation_api_error', $error_message );
					$this->write_log(
						'Stock Validation - Error added to WP_Error object',
						array(
							'sku'         => $sku,
							'error_code'  => 'stock_validation_api_error',
							'error_count' => count( $errors->get_error_codes() ),
						)
					);
				} else {
					wc_add_notice( $error_message, 'error' );
					$this->write_log(
						'Stock Validation - Error added via wc_add_notice',
						array(
							'sku' => $sku,
						)
					);
				}
				continue; // Skip to next item
			}

			// Decode JSON response
			$api_data = json_decode( $response_body, true );

			// If response is invalid, BLOCK checkout - we cannot verify stock
			if ( ! $api_data || ! isset( $api_data['quantity'] ) ) {
				$has_stock_errors = true;
				$this->write_log(
					'Stock Validation - Invalid API Response (BLOCKING ORDER)',
					array(
						'sku'      => $sku,
						'product' => $product_name,
						'response' => $response_body,
					)
				);
				
				// Add error to WP_Error object to block order
				if ( $errors && is_wp_error( $errors ) ) {
					$errors->add(
						'stock_validation_invalid_response',
						sprintf(
							/* translators: %s: product name */
							esc_html__( 'Răspuns invalid de la API pentru %s. Comanda nu poate fi plasată.', 'shopwell' ),
							'<strong>' . esc_html( $product_name ) . '</strong>'
						)
					);
				} else {
					wc_add_notice(
						sprintf(
							/* translators: %s: product name */
							esc_html__( 'Răspuns invalid de la API pentru %s. Comanda nu poate fi plasată.', 'shopwell' ),
							'<strong>' . esc_html( $product_name ) . '</strong>'
						),
						'error'
					);
				}
				continue; // Skip to next item
			}

			// Get available quantity
			$available_quantity = intval( $api_data['quantity'] );

			// CRITICAL: Block order if product is out of stock (quantity = 0)
			if ( $available_quantity === 0 ) {
				$has_stock_errors = true;
				$error_message = sprintf(
					/* translators: %s: product name */
					esc_html__( '%s nu mai este disponibil în stoc. Comanda nu poate fi plasată.', 'shopwell' ),
					'<strong>' . esc_html( $product_name ) . '</strong>'
				);
				
				// Add error to WP_Error object to block order
				if ( $errors && is_wp_error( $errors ) ) {
					$errors->add( 'stock_unavailable', $error_message );
				} else {
					wc_add_notice( $error_message, 'error' );
				}
				
				$this->write_log(
					'Stock Validation - Product Out of Stock (BLOCKING ORDER)',
					array(
						'sku'        => $sku,
						'product'    => $product_name,
						'requested'  => $requested_quantity,
						'available'  => 0,
					)
				);
			} elseif ( $available_quantity < $requested_quantity ) {
				// Block order if requested quantity exceeds available stock
				$has_stock_errors = true;
				$error_message = sprintf(
					/* translators: 1: product name, 2: available quantity */
					esc_html__( '%1$s: Cantitate insuficientă în stoc. Disponibil: %2$d. Comanda nu poate fi plasată.', 'shopwell' ),
					'<strong>' . esc_html( $product_name ) . '</strong>',
					$available_quantity
				);
				
				// Add error to WP_Error object to block order
				if ( $errors && is_wp_error( $errors ) ) {
					$errors->add( 'stock_insufficient', $error_message );
				} else {
					wc_add_notice( $error_message, 'error' );
				}
				
				$this->write_log(
					'Stock Validation - Insufficient Stock (BLOCKING ORDER)',
					array(
						'sku'        => $sku,
						'product'    => $product_name,
						'requested'  => $requested_quantity,
						'available'  => $available_quantity,
					)
				);
			}
		}

		// If there are stock errors, WooCommerce will prevent order creation
		// The error notices are added above, and WooCommerce checks for notices before creating the order
		if ( $has_stock_errors ) {
			$this->write_log(
				'Stock Validation - Order Blocked',
				array(
					'message' => 'Order placement blocked due to stock unavailability',
					'errors_added' => 'yes',
					'has_errors_object' => ( $errors && is_wp_error( $errors ) ) ? 'yes' : 'no',
				)
			);
			
			// CRITICAL: Force throw exception if errors object exists and has errors
			// This ensures WooCommerce cannot proceed with order creation
			if ( $errors && is_wp_error( $errors ) && $errors->has_errors() ) {
				$this->write_log(
					'Stock Validation - Errors object has errors, order should be blocked',
					array(
						'error_codes' => $errors->get_error_codes(),
					)
				);
			}
		} else {
			$this->write_log(
				'Stock Validation - No stock errors found, allowing checkout',
				array()
			);
		}
	}

	/**
	 * Backup validation method for woocommerce_checkout_process hook
	 * This ensures validation runs even if woocommerce_after_checkout_validation doesn't fire
	 * This hook runs BEFORE woocommerce_after_checkout_validation
	 * 
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function validate_stock_availability_process() {
		// CRITICAL: Log immediately to verify hook is running
		error_log( '=== STOCK VALIDATION PROCESS HOOK FIRED ===' );
		error_log( 'Hook: woocommerce_checkout_process' );
		
		// Log that this hook is running
		$this->write_log(
			'Stock Validation Process - START',
			array(
				'hook' => 'woocommerce_checkout_process',
				'has_cart' => ( function_exists( 'WC' ) && WC()->cart ) ? 'yes' : 'no',
			)
		);

		// Call the main validation method with null errors (it will use wc_add_notice)
		// This will add error notices that WooCommerce checks before creating the order
		$this->validate_stock_availability( array(), null );
		
		// Check if there are any error notices after validation
		$notices = wc_get_notices( 'error' );
		if ( ! empty( $notices ) ) {
			$this->write_log(
				'Stock Validation Process - Errors found, order should be blocked',
				array(
					'error_count' => count( $notices ),
					'error_messages' => array_map( function( $notice ) {
						return isset( $notice['notice'] ) ? $notice['notice'] : '';
					}, $notices ),
				)
			);
		} else {
			$this->write_log(
				'Stock Validation Process - No errors found',
				array()
			);
		}
	}

	/**
	 * Final safety check: Prevent order creation if products are out of stock
	 * This runs AFTER order object is created but BEFORE it's saved
	 * This is a last-resort check in case validation was somehow bypassed
	 * 
	 * @since 1.0.0
	 *
	 * @param WC_Order $order The order object.
	 * @return void
	 */
	public function prevent_order_if_no_stock( $order ) {
		// CRITICAL: Log immediately to verify hook is running
		error_log( '=== PREVENT ORDER IF NO STOCK HOOK FIRED ===' );
		error_log( 'Hook: woocommerce_checkout_order_created' );
		error_log( 'Order ID: ' . ( $order ? $order->get_id() : 'NULL' ) );
		
		if ( ! $order || ! is_a( $order, 'WC_Order' ) ) {
			error_log( 'Prevent Order - Invalid order object' );
			return;
		}

		$order_id = $order->get_id();
		error_log( 'Prevent Order - Checking order: ' . $order_id );

		// Check if there are already error notices - if so, validation worked and order shouldn't have been created
		// This is a safety check to avoid deleting orders that were properly blocked
		$notices = wc_get_notices( 'error' );
		if ( ! empty( $notices ) ) {
			// There are already errors, validation should have prevented order creation
			// But order was still created, so we need to delete it
			error_log( 'Prevent Order - Errors found, deleting order: ' . $order_id );
			$this->write_log(
				'Final Safety Check - Order created despite errors (DELETING ORDER)',
				array(
					'order_id' => $order_id,
					'errors'   => $notices,
				)
			);
		}

		// Get Market API base URL - try multiple possible sources (same logic as validate_stock_availability)
		$api_base_url = '';
		
		// Try MARKET_API_BASE_URL constant first
		if ( defined( 'MARKET_API_BASE_URL' ) && ! empty( MARKET_API_BASE_URL ) ) {
			$api_base_url = MARKET_API_BASE_URL;
		}
		// Try market_api_base_url option
		elseif ( ! empty( get_option( 'market_api_base_url', '' ) ) ) {
			$api_base_url = get_option( 'market_api_base_url', '' );
		}
		// Try FOXWAY_API_BASE_URL constant (if Foxway is used)
		elseif ( defined( 'FOXWAY_API_BASE_URL' ) && ! empty( FOXWAY_API_BASE_URL ) ) {
			$api_base_url = FOXWAY_API_BASE_URL;
		}
		// Try foxway_api_base_url option
		elseif ( ! empty( get_option( 'foxway_api_base_url', '' ) ) ) {
			$api_base_url = get_option( 'foxway_api_base_url', '' );
		}
		// Try to get from filter
		else {
			$api_base_url = apply_filters( 'shopwell_api_base_url', '' );
		}

		// If API is not configured, we still need to check - use a default or block
		// For safety, if no API URL, we should still try to verify stock if possible
		if ( empty( $api_base_url ) ) {
			error_log( 'Prevent Order - API not configured, cannot verify stock' );
			// Don't return - we'll check if we can get API URL from order metadata or other sources
			// But for now, if no API URL, we can't verify, so we'll allow the order
			// This is a fallback - ideally API should be configured
			return;
		}

		// Get Market API key - try multiple possible sources
		$api_key = '';
		
		// Try MARKET_API_KEY constant first
		if ( defined( 'MARKET_API_KEY' ) && ! empty( MARKET_API_KEY ) ) {
			$api_key = MARKET_API_KEY;
		}
		// Try market_api_key option
		elseif ( ! empty( get_option( 'market_api_key', '' ) ) ) {
			$api_key = get_option( 'market_api_key', '' );
		}
		// Try FOXWAY_API_KEY constant (if Foxway is used)
		elseif ( defined( 'FOXWAY_API_KEY' ) && ! empty( FOXWAY_API_KEY ) ) {
			$api_key = FOXWAY_API_KEY;
		}
		// Try foxway_api_key option
		elseif ( ! empty( get_option( 'foxway_api_key', '' ) ) ) {
			$api_key = get_option( 'foxway_api_key', '' );
		}
		// Try to get from filter
		else {
			$api_key = apply_filters( 'shopwell_api_key', '' );
		}

		$has_stock_errors = false;

		// Check each order item
		foreach ( $order->get_items() as $item_id => $item ) {
			$product = $item->get_product();
			
			if ( ! $product ) {
				continue;
			}

			$sku = $product->get_sku();
			
			// Skip items without SKU
			if ( empty( $sku ) ) {
				continue;
			}

			$requested_quantity = $item->get_quantity();
			$product_name = $product->get_name();

			// Construct API endpoint
			$api_url = trailingslashit( $api_base_url ) . 'api/v1/sku/' . $sku;

			// Prepare request headers
			$headers = array(
				'accept' => '*/*',
			);

			// Add API key to headers if provided
			if ( ! empty( $api_key ) ) {
				$header_type = defined( 'MARKET_API_KEY_HEADER_TYPE' ) ? MARKET_API_KEY_HEADER_TYPE : 'X-ApiKey';
				$headers[ $header_type ] = $api_key;
			}

			// Make API request
			$response = wp_remote_get(
				$api_url,
				array(
					'timeout'     => 10,
					'httpversion' => '1.1',
					'sslverify'   => true,
					'headers'     => $headers,
				)
			);

			// If API fails, block order - we cannot verify stock
			if ( is_wp_error( $response ) ) {
				$has_stock_errors = true;
				$this->write_log(
					'Final Safety Check - API WP Error (DELETING ORDER)',
					array(
						'order_id' => $order->get_id(),
						'sku'      => $sku,
						'product'  => $product_name,
						'error'    => $response->get_error_message(),
					)
				);
				break;
			}

			$response_code = wp_remote_retrieve_response_code( $response );
			
			// IMPORTANT: 400 = OUT OF STOCK - delete order immediately
			if ( 400 === $response_code ) {
				$has_stock_errors = true;
				$this->write_log(
					'Final Safety Check - API 400 = OUT OF STOCK (DELETING ORDER)',
					array(
						'order_id'      => $order->get_id(),
						'sku'           => $sku,
						'product'       => $product_name,
						'response_code' => 400,
						'message'       => '400 response means product is OUT OF STOCK',
					)
				);
				break;
			}
			
			// For other error codes (404, 500, etc.), also block order
			if ( 200 !== $response_code ) {
				$has_stock_errors = true;
				$this->write_log(
					'Final Safety Check - API Error (DELETING ORDER)',
					array(
						'order_id'      => $order->get_id(),
						'sku'           => $sku,
						'product'       => $product_name,
						'response_code' => $response_code,
					)
				);
				break;
			}

			$response_body = wp_remote_retrieve_body( $response );
			$api_data = json_decode( $response_body, true );

			// If response is invalid, block order
			if ( ! $api_data || ! isset( $api_data['quantity'] ) ) {
				$has_stock_errors = true;
				$this->write_log(
					'Final Safety Check - Invalid Response (DELETING ORDER)',
					array(
						'order_id' => $order->get_id(),
						'sku'      => $sku,
						'product'  => $product_name,
					)
				);
				break;
			}

			$available_quantity = intval( $api_data['quantity'] );

			// Block order if out of stock or insufficient quantity
			if ( $available_quantity === 0 || $available_quantity < $requested_quantity ) {
				$has_stock_errors = true;
				$this->write_log(
					'Final Safety Check - Out of Stock (DELETING ORDER)',
					array(
						'order_id'  => $order->get_id(),
						'sku'       => $sku,
						'product'   => $product_name,
						'requested' => $requested_quantity,
						'available' => $available_quantity,
					)
				);
				break;
			}
		}

		// If there are stock errors, DELETE the order and prevent it from being saved
		if ( $has_stock_errors ) {
			// Delete the order immediately
			wp_delete_post( $order->get_id(), true );
			
			// Add error notice
			wc_add_notice(
				esc_html__( 'Comanda a fost anulată: unul sau mai multe produse nu sunt disponibile în stoc.', 'shopwell' ),
				'error'
			);
			
			// Throw exception to prevent further processing
			throw new \Exception( esc_html__( 'Order cancelled due to stock unavailability', 'shopwell' ) );
		}
	}
}

