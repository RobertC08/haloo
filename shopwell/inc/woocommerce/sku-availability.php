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

		// Validate stock via Market API before adding to cart.
		add_filter( 'woocommerce_add_to_cart_validation', array( $this, 'validate_add_to_cart' ), 10, 5 );

		// Sync reservations after cart changes.
		add_action( 'woocommerce_add_to_cart', array( $this, 'on_cart_item_added' ), 10, 6 );
		add_action( 'woocommerce_cart_item_removed', array( $this, 'sync_all_session_reservations' ) );
		add_action( 'woocommerce_after_cart_item_quantity_update', array( $this, 'sync_all_session_reservations' ) );
		add_action( 'woocommerce_cart_emptied', array( $this, 'on_cart_emptied' ) );
		add_action( 'woocommerce_checkout_order_created', array( $this, 'on_order_created' ) );

		// Register AJAX handlers.
		add_action( 'wp_ajax_check_sku_availability', array( $this, 'check_sku_availability' ) );
		add_action( 'wp_ajax_nopriv_check_sku_availability', array( $this, 'check_sku_availability' ) );
		add_action( 'wp_ajax_get_checkout_cart_skus', array( $this, 'get_checkout_cart_skus' ) );
		add_action( 'wp_ajax_nopriv_get_checkout_cart_skus', array( $this, 'get_checkout_cart_skus' ) );
		add_action( 'wp_ajax_shopwell_sku_reservation_state', array( $this, 'ajax_sku_reservation_state' ) );
		add_action( 'wp_ajax_nopriv_shopwell_sku_reservation_state', array( $this, 'ajax_sku_reservation_state' ) );

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_product_reservation_data' ), 30 );
	}

	/**
	 * Get the current WooCommerce session ID.
	 *
	 * @return string
	 */
	private function get_session_id() {
		if ( function_exists( 'WC' ) && WC()->session ) {
			return (string) WC()->session->get_customer_id();
		}
		return '';
	}

	/**
	 * Get the transient key for a SKU's reservation map.
	 *
	 * @param string $sku
	 * @return string
	 */
	private function reservation_key( $sku ) {
		return 'shopwell_rsv_' . md5( $sku );
	}

	/**
	 * Get all reservations for a SKU: ['session_id' => qty, ...].
	 *
	 * @param string $sku
	 * @return array
	 */
	private function get_reservations( $sku ) {
		return get_transient( $this->reservation_key( $sku ) ) ?: array();
	}

	/**
	 * Set (or clear) the reservation for a session + SKU.
	 *
	 * @param string $sku
	 * @param string $session_id
	 * @param int    $qty 0 removes the reservation.
	 */
	private function set_reservation( $sku, $session_id, $qty ) {
		if ( empty( $session_id ) ) {
			return;
		}

		$reservations = $this->get_reservations( $sku );

		if ( $qty <= 0 ) {
			unset( $reservations[ $session_id ] );
		} else {
			$reservations[ $session_id ] = $qty;
		}

		if ( empty( $reservations ) ) {
			delete_transient( $this->reservation_key( $sku ) );
		} else {
			set_transient( $this->reservation_key( $sku ), $reservations, HOUR_IN_SECONDS );
		}
	}

	/**
	 * Sum of reserved quantities by all sessions except the current one.
	 *
	 * @param string $sku
	 * @param string $session_id Current session to exclude.
	 * @return int
	 */
	private function get_reserved_by_others( $sku, $session_id ) {
		$total = 0;
		foreach ( $this->get_reservations( $sku ) as $sid => $qty ) {
			if ( $sid !== $session_id ) {
				$total += $qty;
			}
		}
		return $total;
	}

	/**
	 * Get the total quantity of a SKU currently in the session's cart.
	 *
	 * @param string $sku
	 * @return int
	 */
	private function get_cart_qty_for_sku( $sku ) {
		if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
			return 0;
		}
		$total = 0;
		foreach ( WC()->cart->get_cart() as $item ) {
			if ( $item['data'] && $item['data']->get_sku() === $sku ) {
				$total += $item['quantity'];
			}
		}
		return $total;
	}

	/**
	 * Recalculate and persist all SKU reservations for the current session
	 * based on what's actually in the cart right now.
	 * Also clears reservations for SKUs no longer in the cart.
	 *
	 * @return void
	 */
	public function sync_all_session_reservations() {
		$session_id = $this->get_session_id();
		if ( empty( $session_id ) || ! function_exists( 'WC' ) || ! WC()->cart ) {
			return;
		}

		$sku_qtys   = array();
		$prev_skus  = get_transient( 'shopwell_sess_skus_' . md5( $session_id ) ) ?: array();

		foreach ( WC()->cart->get_cart() as $item ) {
			$product = $item['data'];
			if ( ! $product ) {
				continue;
			}
			$sku = $product->get_sku();
			if ( empty( $sku ) ) {
				continue;
			}
			$sku_qtys[ $sku ] = ( isset( $sku_qtys[ $sku ] ) ? $sku_qtys[ $sku ] : 0 ) + $item['quantity'];
		}

		foreach ( $sku_qtys as $sku => $qty ) {
			$this->set_reservation( $sku, $session_id, $qty );
		}

		foreach ( $prev_skus as $sku ) {
			if ( ! isset( $sku_qtys[ $sku ] ) ) {
				$this->set_reservation( $sku, $session_id, 0 );
			}
		}

		if ( ! empty( $sku_qtys ) ) {
			set_transient( 'shopwell_sess_skus_' . md5( $session_id ), array_keys( $sku_qtys ), HOUR_IN_SECONDS );
		} else {
			delete_transient( 'shopwell_sess_skus_' . md5( $session_id ) );
		}
	}

	/**
	 * Sync reservation after an item is successfully added to cart.
	 *
	 * @param string $cart_item_key
	 * @param int    $product_id
	 * @param int    $quantity
	 * @param int    $variation_id
	 * @param array  $variation
	 * @param array  $cart_item_data
	 * @return void
	 */
	public function on_cart_item_added( $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data ) {
		$this->sync_all_session_reservations();
	}

	/**
	 * Release all reservations for this session when the cart is emptied.
	 *
	 * @return void
	 */
	public function on_cart_emptied() {
		$session_id = $this->get_session_id();
		if ( empty( $session_id ) ) {
			return;
		}

		$prev_skus = get_transient( 'shopwell_sess_skus_' . md5( $session_id ) ) ?: array();
		foreach ( $prev_skus as $sku ) {
			$this->set_reservation( $sku, $session_id, 0 );
		}
		delete_transient( 'shopwell_sess_skus_' . md5( $session_id ) );
	}

	/**
	 * Release all reservations for this session when an order is created.
	 *
	 * @param \WC_Order $order
	 * @return void
	 */
	public function on_order_created( $order ) {
		$this->on_cart_emptied();
	}

	/**
	 * Fetch SKU data from Market API.
	 *
	 * @param string $sku The product SKU.
	 * @return array|WP_Error Array with 'quantity' and 'price' on success, WP_Error on failure.
	 */
	private function fetch_sku_from_api( $sku ) {
		$api_base_url = defined( 'MARKET_API_BASE_URL' ) ? MARKET_API_BASE_URL : get_option( 'market_api_base_url', '' );

		if ( empty( $api_base_url ) ) {
			return new \WP_Error( 'missing_config', __( 'Market API configuration missing.', 'shopwell' ) );
		}

		$api_key = defined( 'MARKET_API_KEY' ) ? MARKET_API_KEY : get_option( 'market_api_key', '' );
		$api_url = trailingslashit( $api_base_url ) . 'api/v1/sku/' . rawurlencode( $sku );

		$headers = array( 'accept' => '*/*' );

		if ( ! empty( $api_key ) ) {
			$header_type             = defined( 'MARKET_API_KEY_HEADER_TYPE' ) ? MARKET_API_KEY_HEADER_TYPE : 'X-ApiKey';
			$headers[ $header_type ] = $api_key;
		}

		$response = wp_remote_get(
			$api_url,
			array(
				'timeout'     => 10,
				'httpversion' => '1.1',
				'sslverify'   => true,
				'headers'     => $headers,
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		$response_body = wp_remote_retrieve_body( $response );

		if ( 200 !== $response_code ) {
			return new \WP_Error( 'api_error', sprintf( 'API returned HTTP %d', $response_code ) );
		}

		$data = json_decode( $response_body, true );

		if ( ! $data || ! isset( $data['quantity'] ) || ! isset( $data['price'] ) ) {
			return new \WP_Error( 'invalid_response', __( 'Invalid API response.', 'shopwell' ) );
		}

		return array(
			'quantity' => intval( $data['quantity'] ),
			'price'    => floatval( $data['price'] ),
		);
	}

	/**
	 * Effective quantity available for this visitor (Market API minus other sessions' cart reservations).
	 *
	 * @param string $sku Product SKU.
	 * @return array|null Keys: api_qty, reserved_by_others, effective. Null if SKU empty or API unavailable (fail-open).
	 */
	private function get_effective_availability_for_sku( $sku ) {
		if ( empty( $sku ) ) {
			return null;
		}

		$api_data = $this->fetch_sku_from_api( $sku );
		if ( is_wp_error( $api_data ) ) {
			$this->write_log(
				'get_effective_availability_for_sku - API fail (fail-open)',
				array(
					'sku'   => $sku,
					'error' => $api_data->get_error_message(),
				)
			);
			return null;
		}

		$session_id          = $this->get_session_id();
		$api_qty             = (int) $api_data['quantity'];
		$reserved_by_others  = $this->get_reserved_by_others( $sku, $session_id );
		$effective           = $api_qty - $reserved_by_others;

		return array(
			'api_qty'            => $api_qty,
			'reserved_by_others' => $reserved_by_others,
			'effective'          => $effective,
		);
	}

	/**
	 * AJAX: current reservation-aware availability for a SKU (product page UI).
	 *
	 * @return void
	 */
	public function ajax_sku_reservation_state() {
		check_ajax_referer( 'shopwell_sku_reservation', 'nonce' );

		$sku = isset( $_POST['sku'] ) ? sanitize_text_field( wp_unslash( $_POST['sku'] ) ) : '';
		if ( '' === $sku ) {
			wp_send_json_success(
				array(
					'skip' => true,
				)
			);
			return;
		}

		$info = $this->get_effective_availability_for_sku( $sku );
		if ( null === $info ) {
			wp_send_json_success(
				array(
					'skip' => true,
				)
			);
			return;
		}

		$cannot_add      = $info['effective'] <= 0;
		$held_elsewhere  = $info['reserved_by_others'] > 0;

		wp_send_json_success(
			array(
				'skip'               => false,
				'api_qty'            => $info['api_qty'],
				'reserved_by_others' => $info['reserved_by_others'],
				'effective'          => $info['effective'],
				'cannot_add'         => $cannot_add,
				'held_elsewhere'     => $held_elsewhere,
			)
		);
	}

	/**
	 * Pass reservation check config to single-product script on product pages.
	 *
	 * @return void
	 */
	public function enqueue_product_reservation_data() {
		if ( ! function_exists( 'is_product' ) || ! is_product() ) {
			return;
		}

		if ( ! wp_script_is( 'shopwell-single-product', 'enqueued' ) ) {
			return;
		}

		$product_id = get_queried_object_id();
		$simple_sku  = '';
		if ( $product_id && function_exists( 'wc_get_product' ) ) {
			$product = wc_get_product( $product_id );
			if ( $product && $product->is_type( 'simple' ) ) {
				$simple_sku = (string) $product->get_sku();
			}
		}

		wp_localize_script(
			'shopwell-single-product',
			'shopwellSkuReservation',
			array(
				'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
				'nonce'     => wp_create_nonce( 'shopwell_sku_reservation' ),
				'simpleSku' => $simple_sku,
				'pollMs'    => 30000,
				'i18n'      => array(
					'reservedTitle'   => esc_html__( 'Momentan indisponibil pentru comandă', 'shopwell' ),
					'reservedBody'    => esc_html__( 'Acest exemplar este deja în coșul altui client. Reîmprospătează pagina peste câteva minute sau alege altă configurație.', 'shopwell' ),
					'stockForYou'     => esc_html__( 'Disponibil pentru tine acum: %s', 'shopwell' ),
					'heldInCarts'     => esc_html__( 'Reținut în alte coșuri: %d', 'shopwell' ),
					'ajaxBlockedHint' => esc_html__( 'Nu se poate adăuga: exemplarul este deja rezervat în alt coș.', 'shopwell' ),
					'supplierEmpty'   => esc_html__( 'Stoc indisponibil la furnizor pentru această configurație.', 'shopwell' ),
				),
			)
		);
	}

	/**
	 * Validate stock via Market API before adding a product to cart.
	 * Fails open: if the API is unreachable, the add-to-cart is allowed.
	 *
	 * @param bool  $passed       Whether validation has passed so far.
	 * @param int   $product_id   The product (parent) ID being added.
	 * @param int   $quantity     The quantity being added.
	 * @param int   $variation_id The variation ID (0 for simple products).
	 * @param array $variation    The variation attributes.
	 * @return bool
	 */
	public function validate_add_to_cart( $passed, $product_id, $quantity, $variation_id = 0, $variation = array() ) {
		if ( ! $passed ) {
			return $passed;
		}

		// For variable products, use the variation to get the correct SKU.
		$product_to_check = $variation_id ? wc_get_product( $variation_id ) : wc_get_product( $product_id );

		if ( ! $product_to_check ) {
			return $passed;
		}

		$sku = $product_to_check->get_sku();

		// Fallback to parent SKU if variation has none.
		if ( empty( $sku ) && $variation_id ) {
			$parent = wc_get_product( $product_id );
			if ( $parent ) {
				$sku = $parent->get_sku();
			}
		}

		if ( empty( $sku ) ) {
			return $passed;
		}

		$product = $product_to_check;

		$availability = $this->get_effective_availability_for_sku( $sku );

		if ( null === $availability ) {
			return $passed;
		}

		$session_id             = $this->get_session_id();
		$api_qty                = $availability['api_qty'];
		$reserved_by_others     = $availability['reserved_by_others'];
		$effective_available    = $availability['effective'];

		$this->write_log(
			'Add-to-cart validation',
			array(
				'sku'                 => $sku,
				'session_id'          => $session_id,
				'requested'           => $quantity,
				'api_qty'             => $api_qty,
				'reserved_by_others'  => $reserved_by_others,
				'all_reservations'    => $this->get_reservations( $sku ),
				'effective_available' => $effective_available,
			)
		);

		if ( $effective_available <= 0 ) {
			if ( $api_qty > 0 && $reserved_by_others > 0 ) {
				wc_add_notice(
					sprintf(
						/* translators: %s: product name */
						esc_html__( '"%1$s" — exemplarul este momentan în coșul altui client și nu poate fi adăugat. Încearcă din nou mai târziu sau alege altă configurație.', 'shopwell' ),
						$product->get_name()
					),
					'error'
				);
			} else {
				wc_add_notice(
					sprintf(
						/* translators: %s: product name */
						esc_html__( '"%s" nu mai este disponibil în stoc.', 'shopwell' ),
						$product->get_name()
					),
					'error'
				);
			}
			return false;
		}

		if ( $quantity > $effective_available ) {
			wc_add_notice(
				sprintf(
					/* translators: 1: product name, 2: available quantity */
					esc_html__( 'Cantitate insuficientă pentru "%1$s". Disponibil: %2$d.', 'shopwell' ),
					$product->get_name(),
					$effective_available
				),
				'error'
			);
			return false;
		}

		// Optimistically reserve immediately so concurrent requests see this reservation.
		$current_in_cart = $this->get_cart_qty_for_sku( $sku );
		$this->set_reservation( $sku, $session_id, $current_in_cart + $quantity );

		return $passed;
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
			'1.0.4',
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
				'placeOrderLockedTitle' => esc_attr__( 'Completează toate câmpurile obligatorii (inclusiv bifările obligatorii) înainte de a plasa comanda.', 'shopwell' ),
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
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'check_sku_availability_nonce' ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Security check failed.', 'shopwell' ) ) );
			return;
		}

		if ( ! isset( $_POST['sku'] ) || empty( $_POST['sku'] ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'SKU is required.', 'shopwell' ) ) );
			return;
		}

		$sku      = sanitize_text_field( wp_unslash( $_POST['sku'] ) );
		$api_data = $this->fetch_sku_from_api( $sku );

		if ( is_wp_error( $api_data ) ) {
			$this->write_log(
				'SKU Availability Check - Error',
				array(
					'sku'   => $sku,
					'error' => $api_data->get_error_message(),
				)
			);

			wp_send_json_error(
				array(
					'message' => esc_html__( 'Eroare la verificarea disponibilității produsului.', 'shopwell' ),
					'error'   => $api_data->get_error_message(),
				)
			);
			return;
		}

		$this->write_log(
			'SKU Availability Check - Success',
			array(
				'sku'      => $sku,
				'quantity' => $api_data['quantity'],
				'price'    => $api_data['price'],
			)
		);

		wp_send_json_success(
			array(
				'sku'      => $sku,
				'quantity' => $api_data['quantity'],
				'price'    => $api_data['price'],
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

