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
class Cart {
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
		// Empty cart.
		add_action( 'woocommerce_cart_actions', array( $this, 'empty_cart_button' ) );
		add_action( 'template_redirect', array( $this, 'empty_cart_action' ) );

		// Add image to empty cart message.
		add_filter( 'wc_empty_cart_message', array( $this, 'empty_cart_message' ) );

		remove_action( 'woocommerce_cart_collaterals', 'woocommerce_cross_sell_display' );
		add_action( 'woocommerce_after_cart', 'woocommerce_cross_sell_display' );

		add_filter( 'woocommerce_cross_sells_columns', array( $this, 'cross_sells_columns' ) );
	}

	/**
	 * Empty cart button.
	 */
	public function empty_cart_button() {
		?>
		<button type="submit" class="button button-empty-cart shopwell-button shopwell-button--subtle shopwell-button--color-black" name="empty_cart" value="<?php esc_attr_e( 'Clear cart', 'shopwell' ); ?>"><?php esc_html_e( 'Clear Cart', 'shopwell' ); ?></button>
		<?php
	}

	/**
	 * Empty cart.
	 */
	public function empty_cart_action() {
		if ( ! empty( $_POST['empty_cart'] ) && wp_verify_nonce( wc_get_var( $_REQUEST['woocommerce-cart-nonce'] ), 'woocommerce-cart' ) ) {
			WC()->cart->empty_cart();
			wc_add_notice( esc_html__( 'Cart is cleared.', 'shopwell' ) );

			$referer = wp_get_referer() ? remove_query_arg(
				array(
					'remove_item',
					'add-to-cart',
					'added-to-cart',
				),
				add_query_arg( 'cart_emptied', '1', wp_get_referer() )
			) : wc_get_cart_url();
			wp_safe_redirect( $referer );
			exit;
		}
	}

	/**
	 * Change columns upsell
	 *
	 * @return void
	 */
	public function cross_sells_columns( $columns ) {
		$columns = 5;

		return $columns;
	}

	/**
	 * Display empty cart image.
	 *
	 * @param string $message
	 * @return string
	 */
	public function empty_cart_message( $message ) {
		$message  = '<img src="' . esc_url( get_theme_file_uri( 'assets/images/shopwell-cart-empty.svg' ) ) . '" width="150" alt="' . esc_attr__( 'Cart is empty', 'shopwell' ) . '">';
		$message .= '<span class="empty-cart__title">' . esc_html__( 'Cart is empty', 'shopwell' ) . '</span>';
		$message .= '<span class="empty-cart__description">' . esc_html__( 'Don&#39;t miss out on amazing deals! Sign in to view products added or start shopping now.', 'shopwell' ) . '</span>';

		return $message;
	}
}
