<?php
/**
 * Hooks of checkout.
 *
 * @package Shopwell
 */

namespace Shopwell\WooCommerce;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( '\Shopwell\WooCommerce\Checkout' ) ) {
	/**
	 * Class of checkout template.
	 */
	class Checkout {
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
			// Wrap checkout login and coupon notices.
			remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_login_form', 10 );
			remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );
			add_action( 'woocommerce_before_checkout_form', array( $this, 'before_login_form' ), 10 );
			add_action( 'woocommerce_before_checkout_form', array( $this, 'login_form' ), 10 );
			add_action( 'woocommerce_before_checkout_form', array( $this, 'coupon_form' ), 10 );
			add_action( 'woocommerce_before_checkout_form', array( $this, 'after_login_form' ), 10 );

			add_filter( 'woocommerce_checkout_coupon_message', array( $this, 'coupon_form_name' ), 10 );

			add_filter( 'woocommerce_cart_item_name', array( $this, 'review_product_name_html' ), 10, 3 );

			// Validate checkout fields and terms - run at priority 5 to catch errors early
			add_action( 'woocommerce_checkout_process', array( $this, 'validate_checkout_fields' ), 5 );
			
			// Also validate before checkout update - extra safety
			add_action( 'woocommerce_after_checkout_validation', array( $this, 'validate_checkout_fields_after' ), 10, 2 );
			
			// Ensure required fields are marked as required
			add_filter( 'woocommerce_checkout_fields', array( $this, 'ensure_required_fields' ), 999 );
		}

		/**
		 * Checkout Before login form.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function before_login_form() {
			echo '<div class="row-flex checkout-form-cols">';
		}

		/**
		 * Checkout After login form.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function after_login_form() {
			echo '</div>';
		}

		/**
		 * Checkout login form.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function login_form() {
			if ( is_user_logged_in() || 'no' === get_option( 'woocommerce_enable_checkout_login_reminder' ) ) {
				return;
			}

			echo '<div class="checkout-login checkout-form-col col-flex col-flex-md-6 col-flex-xs-12">';
			woocommerce_checkout_login_form();
			echo '</div>';
		}

		/**
		 * Checkout coupon form.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function coupon_form() {
			if ( ! wc_coupons_enabled() ) {
				return;
			}

			echo '<div class="checkout-coupon checkout-form-col col-flex col-flex-md-6 col-flex-xs-12">';
			woocommerce_checkout_coupon_form();
			echo '</div>';
		}

		/**
		 * Checkout coupon form.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function coupon_form_name( $html ) {
			if ( ! wc_coupons_enabled() ) {
				return;
			}

			return esc_html__( 'Have a coupon?', 'shopwell' ) . ' <a href="#" class="showcoupon">' . esc_html__( 'Enter your code', 'shopwell' ) . '</a>';
		}

		/**
		 * Review product name html
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function review_product_name_html( $name, $cart_item, $cart_item_key ) {
			if ( ! function_exists( 'is_checkout' ) || ! is_checkout() ) {
				return $name;
			}

			$product   = $cart_item['data'];
			$thumbnail = $product->get_image( 'thumbnail' );
			return '<span class="checkout-review-product-name">' . $thumbnail . $name . '</span>';
		}

		/**
		 * Validate checkout required fields and terms
		 * This runs during checkout processing and prevents order creation if validation fails
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function validate_checkout_fields() {
			$has_errors = false;

			// Get checkout fields
			if ( ! function_exists( 'WC' ) || ! WC()->checkout() ) {
				return;
			}

			$checkout = WC()->checkout();
			$fields   = $checkout->get_checkout_fields();

			// Validate billing fields
			if ( ! empty( $fields['billing'] ) ) {
				foreach ( $fields['billing'] as $key => $field ) {
					// Check if field is required
					if ( ! empty( $field['required'] ) ) {
						$value = isset( $_POST[ $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) : '';

						if ( empty( $value ) ) {
							$field_label = ! empty( $field['label'] ) ? $field['label'] : $key;
							/* translators: %s: field name */
							wc_add_notice( sprintf( __( '%s is a required field.', 'shopwell' ), '<strong>' . esc_html( $field_label ) . '</strong>' ), 'error' );
							$has_errors = true;
						} elseif ( 'billing_email' === $key && ! is_email( $value ) ) {
							wc_add_notice( __( 'Please enter a valid email address.', 'shopwell' ), 'error' );
							$has_errors = true;
						}
					}
				}
			}

			// Validate shipping fields (if shipping is needed)
			if ( WC()->cart->needs_shipping() && ! empty( $fields['shipping'] ) ) {
				foreach ( $fields['shipping'] as $key => $field ) {
					// Check if field is required
					if ( ! empty( $field['required'] ) ) {
						$value = isset( $_POST[ $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) : '';

						if ( empty( $value ) ) {
							$field_label = ! empty( $field['label'] ) ? $field['label'] : $key;
							/* translators: %s: field name */
							wc_add_notice( sprintf( __( '%s is a required field.', 'shopwell' ), '<strong>' . esc_html( $field_label ) . '</strong>' ), 'error' );
							$has_errors = true;
						}
					}
				}
			}

			// Validate order fields
			if ( ! empty( $fields['order'] ) ) {
				foreach ( $fields['order'] as $key => $field ) {
					// Check if field is required
					if ( ! empty( $field['required'] ) ) {
						$value = isset( $_POST[ $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) : '';

						if ( empty( $value ) ) {
							$field_label = ! empty( $field['label'] ) ? $field['label'] : $key;
							/* translators: %s: field name */
							wc_add_notice( sprintf( __( '%s is a required field.', 'shopwell' ), '<strong>' . esc_html( $field_label ) . '</strong>' ), 'error' );
							$has_errors = true;
						}
					}
				}
			}

			// Check if terms and conditions checkbox is checked
			$terms_checked = false;
			if ( isset( $_POST['terms'] ) && ! empty( $_POST['terms'] ) ) {
				$terms_checked = true;
			} elseif ( isset( $_POST['terms-field'] ) && ! empty( $_POST['terms-field'] ) ) {
				$terms_checked = true;
			}

			$terms_page_id = wc_terms_and_conditions_page_id();
			if ( $terms_page_id && ! $terms_checked ) {
				wc_add_notice( __( 'You must accept the terms and conditions to proceed.', 'shopwell' ), 'error' );
				$has_errors = true;
			}

			// If there are any errors, WooCommerce will prevent order creation
			// The errors are added above, and WooCommerce checks for notices before creating the order
		}

		/**
		 * Additional validation after WooCommerce's validation
		 * This is a backup check to ensure nothing slips through
		 *
		 * @since 1.0.0
		 *
		 * @param array    $data   Checkout data.
		 * @param WP_Error $errors Validation errors.
		 * @return void
		 */
		public function validate_checkout_fields_after( $data, $errors ) {
			// Check terms again as a backup
			$terms_checked = false;
			if ( isset( $_POST['terms'] ) && ! empty( $_POST['terms'] ) ) {
				$terms_checked = true;
			} elseif ( isset( $_POST['terms-field'] ) && ! empty( $_POST['terms-field'] ) ) {
				$terms_checked = true;
			}

			$terms_page_id = wc_terms_and_conditions_page_id();
			if ( $terms_page_id && ! $terms_checked ) {
				$errors->add( 'terms', __( 'You must accept the terms and conditions to proceed.', 'shopwell' ) );
			}

			// Check critical required fields
			$required_fields = array(
				'billing_first_name' => __( 'First name', 'shopwell' ),
				'billing_last_name'  => __( 'Last name', 'shopwell' ),
				'billing_email'       => __( 'Email address', 'shopwell' ),
				'billing_phone'      => __( 'Phone number', 'shopwell' ),
				'billing_address_1'   => __( 'Address', 'shopwell' ),
				'billing_city'       => __( 'City', 'shopwell' ),
				'billing_postcode'   => __( 'Postcode', 'shopwell' ),
				'billing_country'    => __( 'Country', 'shopwell' ),
			);

			foreach ( $required_fields as $field_key => $field_label ) {
				if ( empty( $data[ $field_key ] ) ) {
					/* translators: %s: field name */
					$errors->add( $field_key, sprintf( __( '%s is a required field.', 'shopwell' ), '<strong>' . esc_html( $field_label ) . '</strong>' ) );
				}
			}

			// Validate email format
			if ( ! empty( $data['billing_email'] ) && ! is_email( $data['billing_email'] ) ) {
				$errors->add( 'billing_email', __( 'Please enter a valid email address.', 'shopwell' ) );
			}
		}

		/**
		 * Ensure required fields are marked as required
		 * This filter ensures fields can't be made optional accidentally
		 *
		 * @since 1.0.0
		 *
		 * @param array $fields Checkout fields.
		 * @return array Modified checkout fields.
		 */
		public function ensure_required_fields( $fields ) {
			// Ensure critical billing fields are always required
			$critical_fields = array(
				'billing_first_name',
				'billing_last_name',
				'billing_email',
				'billing_phone',
				'billing_address_1',
				'billing_city',
				'billing_postcode',
				'billing_country',
			);

			foreach ( $critical_fields as $field_key ) {
				if ( isset( $fields['billing'][ $field_key ] ) ) {
					$fields['billing'][ $field_key ]['required'] = true;
				}
			}

			return $fields;
		}

	}
}
