<?php
/**
 * Woocommerce Setup functions and definitions.
 *
 * @package Shopwell
 */

namespace Shopwell\WooCommerce;

use Shopwell\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Woocommerce initial
 */
class General {
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
		add_filter( 'body_class', array( $this, 'body_class' ) );
		add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );
		add_action( 'wp_enqueue_scripts', array( $this, 'scripts' ), 20 );

		// Update counter via ajax.
		add_filter( 'woocommerce_add_to_cart_fragments', array( $this, 'cart_link_fragment' ) );

		// Change mini cart button
		remove_action( 'woocommerce_widget_shopping_cart_buttons', 'woocommerce_widget_shopping_cart_button_view_cart', 10 );
		remove_action( 'woocommerce_widget_shopping_cart_buttons', 'woocommerce_widget_shopping_cart_proceed_to_checkout', 20 );

		if ( apply_filters( 'shopwell_mini_cart_button_proceed_to_checkout', true ) ) {
			add_action( 'woocommerce_widget_shopping_cart_buttons', array( $this, 'button_proceed_to_checkout' ), 10 );
		}

		if ( apply_filters( 'shopwell_mini_cart_button_view_cart', true ) ) {
			add_action( 'woocommerce_widget_shopping_cart_buttons', array( $this, 'button_view_cart' ), 20 );
		}

		// Ajax update mini cart.
		add_action( 'wc_ajax_update_cart_item', array( $this, 'update_cart_item' ) );

		// Change the quantity format of the cart widget.
		add_filter( 'woocommerce_widget_cart_item_quantity', array( $this, 'cart_item_quantity' ), 10, 3 );

		add_action( 'woocommerce_before_quantity_input_field', array( $this, 'quantity_icon_decrease' ) );
		add_action( 'woocommerce_after_quantity_input_field', array( $this, 'quantity_icon_increase' ) );

		// Change total price in mini cart default
		remove_action( 'woocommerce_widget_shopping_cart_total', 'woocommerce_widget_shopping_cart_subtotal' );
		add_action( 'woocommerce_widget_shopping_cart_total', array( $this, 'widget_shopping_cart_subtotal' ) );

		// Get products by group.
		if ( ! is_admin() ) {
			add_action( 'pre_get_posts', array( __CLASS__, 'get_products_query' ) );
		}

		// Change star rating HTML.
		add_filter( 'woocommerce_get_star_rating_html', array( $this, 'star_rating_html' ), 10, 3 );

		add_action( 'woocommerce_scheduled_sales', array( $this, 'shopwell_woocommerce_clear_cache_daily' ) );
		add_action( 'customize_save_after', array( $this, 'shopwell_woocommerce_clear_cache_daily' ) );
		add_action( 'save_post', array( $this, 'shopwell_woocommerce_clear_cache' ) );
		add_action( 'wp_trash_post', array( $this, 'shopwell_woocommerce_clear_cache' ) );
		add_action( 'before_delete_post', array( $this, 'shopwell_woocommerce_clear_cache' ) );

		// Change pagination
		add_filter( 'woocommerce_pagination_args', array( $this, 'pagination_args' ) );

		// Get products on sale.
		add_action( 'woocommerce_product_query', array( $this, 'products_on_sale' ) );

		// Product Brands
		add_filter(
			'woocommerce_shortcode_products_query',
			array(
				$this,
				'shortcode_products_orderby',
			),
			20,
			2
		);

		add_filter( 'woocommerce_cart_shipping_method_full_label', array( $this, 'cart_shipping_method_full_label' ), 20, 2 );

		// Add product id input hidden
		add_action(
			'woocommerce_before_add_to_cart_button',
			array(
				$this,
				'product_id_hidden',
			)
		);

		add_filter( 'woocommerce_format_sale_price', array( $this, 'format_sale_price' ), 10, 3 );

		// Comment Note
		add_filter( 'comment_form_defaults', array( $this, 'change_add_comment_form_before' ) );

		// Reviews
		add_filter( 'woocommerce_product_description_heading', '__return_null' );
		add_filter( 'woocommerce_product_additional_information_heading', '__return_null' );

		add_filter( 'woocommerce_reviews_title', array( $this, 'reviews_title' ) );

		add_action( 'woocommerce_review_before', array( $this, 'open_comment_wrapper' ), 1 );
		// Change position rating
		remove_action( 'woocommerce_review_before_comment_meta', 'woocommerce_review_display_rating' );
		add_action( 'shopwell_review_after', 'woocommerce_review_display_rating', 1 );
		add_action( 'shopwell_review_after', array( $this, 'close_comment_wrapper' ), 10 );

		// Change description
		remove_action( 'woocommerce_review_comment_text', 'woocommerce_review_display_comment_text' );
		add_action( 'shopwell_review_after', 'woocommerce_review_display_comment_text', 15 );

		add_action( 'shopwell_woocommerce_reviews', array( $this, 'table_ratings' ) );
	}

	/**
	 * WooCommerce specific scripts & stylesheets.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function scripts() {
		$style_file = is_rtl() ? 'woocommerce-rtl.css' : 'woocommerce.css';
		wp_enqueue_style( 'shopwell-woocommerce-style', apply_filters( 'shopwell_get_style_directory_uri', get_template_directory_uri() ) . '/' . $style_file, array(), '20250107' );

		$parse_css = apply_filters( 'shopwell_wc_inline_style', false );
		if ( $parse_css ) {
			wp_add_inline_style( 'shopwell-woocommerce-style', $parse_css );
		}

		if ( 'zoom' == Helper::get_option( 'product_card_hover' ) && wp_script_is( 'zoom', 'registered' ) ) {
			wp_enqueue_script( 'zoom' );
		}

		wp_enqueue_script( 'wc-cart-fragments' );
	}

	/**
	 * Add 'woocommerce-active' class to the body tag.
	 *
	 * @since 1.0.0
	 *
	 * @param  array $classes CSS classes applied to the body tag.
	 *
	 * @return array $classes modified to include 'woocommerce-active' class.
	 */
	public function body_class( $classes ) {
		$loop_layout = apply_filters( 'shopwell_product_card_layout', Helper::get_option( 'product_card_layout' ) );

		$classes[] = 'product-card-layout-' . $loop_layout;

		if ( intval( Helper::get_option( 'mobile_product_card_atc' ) ) ) {
			$classes[] = 'product-card-mobile-show-atc';
		}

		return $classes;
	}

	/**
	 * Ensure cart contents update when products are added to the cart via AJAX.
	 *
	 * @since 1.0.0
	 *
	 * @param array $fragments Fragments to refresh via AJAX.
	 *
	 * @return array Fragments to refresh via AJAX.
	 */
	public function cart_link_fragment( $fragments ) {
		$hidden = WC()->cart->is_empty() ? 'hidden' : '';

		$fragments['span.header-cart__counter']   = '<span class="header-cart__counter header-counter ' . esc_attr( $hidden ) . '">' . intval( WC()->cart->get_cart_contents_count() ) . '</span>';
		$fragments['span.cart-panel__counter']    = '<span class="cart-panel__counter ' . esc_attr( $hidden ) . '"> (' . intval( WC()->cart->get_cart_contents_count() ) . ') </span>';
		$fragments['span.cart-dropdown__counter'] = '<span class="cart-dropdown__counter ' . esc_attr( $hidden ) . '"> (' . intval( WC()->cart->get_cart_contents_count() ) . ') </span>';
		$fragments['span.cart-counter']           = '<span class="counter cart-counter ' . esc_attr( $hidden ) . '">' . intval( WC()->cart->get_cart_contents_count() ) . '</span>';

		return $fragments;
	}

	/**
	 * Update a cart item.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function update_cart_item() {
		if ( empty( $_POST['cart_item_key'] ) || ! isset( $_POST['qty'] ) ) {
			wp_send_json_error();
			exit;
		}

		$cart_item_key = wc_clean( $_POST['cart_item_key'] );
		$qty           = floatval( $_POST['qty'] );

		check_admin_referer( 'shopwell-update-cart-qty--' . $cart_item_key, 'security' );

		ob_start();
		WC()->cart->set_quantity( $cart_item_key, $qty );

		if ( $cart_item_key && false !== WC()->cart->set_quantity( $cart_item_key, $qty ) ) {
			\WC_AJAX::get_refreshed_fragments();
		} else {
			wp_send_json_error();
		}
	}

	/**
	 * Change the quantity HTML of widget cart.
	 *
	 * @since 1.0.0
	 *
	 * @param string $product_quantity
	 * @param array  $cart_item
	 * @param string $cart_item_key
	 *
	 * @return string
	 */
	public function cart_item_quantity( $product_quantity, $cart_item, $cart_item_key ) {
		$_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );

		if ( $_product->is_sold_individually() ) {
			$quantity = '<span class="quantity">1</span>';
		} else {
			$quantity = woocommerce_quantity_input(
				array(
					'input_name'   => "cart[{$cart_item_key}][qty]",
					'input_value'  => $cart_item['quantity'],
					'max_value'    => $_product->get_max_purchase_quantity(),
					'min_value'    => '0',
					'product_name' => $_product->get_name(),
				),
				$_product,
				false
			);
		}

		return $quantity;
	}

	/**
	 * Quantity Decrease Icon
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function quantity_icon_decrease() {
		echo \Shopwell\Icon::get_svg(
			'minus',
			'ui',
			array(
				'class'    => 'shopwell-qty-button decrease',
				'tabindex' => '0',
			)
		);
	}

		/**
		 * Quantity Increase Icon
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
	public function quantity_icon_increase() {
		echo \Shopwell\Icon::get_svg(
			'plus',
			'ui',
			array(
				'class'    => 'shopwell-qty-button increase',
				'tabindex' => '0',
			)
		);
	}

	/**
	 * Change cart button view cart
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function button_view_cart() {
		echo '<a href="' . esc_url( wc_get_cart_url() ) . '" class="shopwell-button view-cart wc-forward shopwell-button--subtle">' . esc_html__( 'View cart', 'shopwell' ) . '</a>';
	}

	/**
	 * Change cart button procees to checkout
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function button_proceed_to_checkout() {
		echo '<a href="' . esc_url( wc_get_checkout_url() ) . '" class="shopwell-button checkout wc-forward shopwell-button--large">' . esc_html__( 'Checkout', 'shopwell' ) . '</a>';
	}

	/**
	 * Output to view cart subtotal.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function widget_shopping_cart_subtotal() {
		$count = ! empty( WC()->cart ) ? intval( WC()->cart->get_cart_contents_count() ) : 0;
		$item  = esc_html__( 'item', 'shopwell' );
		if ( $count > 1 ) {
			$item = esc_html__( 'items', 'shopwell' );
		}
		echo '<span class="widget_shopping_cart_subtotal">' . esc_html__( 'Subtotal', 'shopwell' ) . ' (' . $count . ' ' . $item . ') </span>' . WC()->cart->get_cart_subtotal();
	}

	/**
	 * Change the main query to get products by group
	 *
	 * @param object $query
	 */
	public static function get_products_query( $query ) {
		if ( ! $query->is_main_query() ) {
			return;
		}

		if ( isset( $_GET['products_group'] ) && ! empty( $_GET['products_group'] ) ) {
			switch ( $_GET['products_group'] ) {
				case 'featured':
					$tax_query   = $query->get( 'tax_query' );
					$tax_query   = $tax_query ? $tax_query : WC()->query->get_tax_query();
					$tax_query[] = array(
						'taxonomy' => 'product_visibility',
						'field'    => 'name',
						'terms'    => 'featured',
						'operator' => 'IN',
					);
					$query->set( 'tax_query', $tax_query );
					break;

				case 'sale':
					$query->set( 'post__in', array_merge( array( 0 ), wc_get_product_ids_on_sale() ) );
					break;

				case 'new':
					$query->set( 'post__in', array_merge( array( 0 ), self::shopwell_woocommerce_get_new_product_ids() ) );
					break;

				case 'best_sellers':
					$query->set( 'meta_key', 'total_sales' );
					$query->set( 'order', 'DESC' );
					$query->set( 'orderby', 'meta_value_num' );
					break;
			}
		}

		if ( isset( $_GET['product_status'] ) && ! empty( $_GET['product_status'] ) ) {
			$product_status = explode( ',', $_GET['product_status'] );

			if ( in_array( 'on_sale', $product_status ) ) {
				$query->set( 'post__in', array_merge( array( 0 ), wc_get_product_ids_on_sale() ) );
			}

			if ( in_array( 'in_stock', $product_status ) ) {
				$meta_query[] = array(
					'key'     => '_stock_status',
					'value'   => 'outofstock',
					'compare' => 'NOT IN',
				);
				$query->set( 'meta_query', $meta_query );
			}

			if ( in_array( 'out_of_stock', $product_status ) ) {
				$meta_query[] = array(
					'key'     => '_stock_status',
					'value'   => 'instock',
					'compare' => 'NOT IN',
				);
				$query->set( 'meta_query', $meta_query );
			}
		}

		$query->set( 'search_columns', Helper::get_option( 'search_columns' ) );
	}

	/**
	 * Star rating HTML.
	 *
	 * @since 1.0.0
	 *
	 * @param string $html Star rating HTML.
	 * @param int    $rating Rating value.
	 * @param int    $count Rated count.
	 *
	 * @return string
	 */
	public function star_rating_html( $html, $rating, $count ) {
		$html  = '<span class="max-rating rating-stars">'
				. \Shopwell\Icon::get_svg( 'star' )
				. \Shopwell\Icon::get_svg( 'star' )
				. \Shopwell\Icon::get_svg( 'star' )
				. \Shopwell\Icon::get_svg( 'star' )
				. \Shopwell\Icon::get_svg( 'star' )
				. '</span>';
		$html .= '<span class="user-rating rating-stars" style="width:' . ( ( $rating / 5 ) * 100 ) . '%">'
				. \Shopwell\Icon::get_svg( 'star' )
				. \Shopwell\Icon::get_svg( 'star' )
				. \Shopwell\Icon::get_svg( 'star' )
				. \Shopwell\Icon::get_svg( 'star' )
				. \Shopwell\Icon::get_svg( 'star' )
				. '</span>';

		$html .= '<span class="screen-reader-text">';

		if ( 0 < $count ) {
			/* translators: 1: rating 2: rating count */
			$html .= sprintf( _n( 'Rated %1$s out of 5 based on %2$s customer rating', 'Rated %1$s out of 5 based on %2$s customer ratings', $count, 'shopwell' ), '<strong class="rating">' . esc_html( $rating ) . '</strong>', '<span class="rating">' . esc_html( $count ) . '</span>' );
		} else {
			/* translators: %s: rating */
			$html .= sprintf( esc_html__( 'Rated %s out of 5', 'shopwell' ), '<strong class="rating">' . esc_html( $rating ) . '</strong>' );
		}

		$html .= '</span>';

		return $html;
	}

	/**
	 * Get IDs of the products that are set as new ones.
	 *
	 * @return array
	 */
	public static function shopwell_woocommerce_get_new_product_ids() {
		// Load from cache.
		$product_ids = get_transient( 'shopwell_woocommerce_products_new' );

		// Valid cache found.
		if ( false !== $product_ids ) {
			return $product_ids;
		}

		$product_ids = array();

		// Get products which are set as new.
		$meta_query   = WC()->query->get_meta_query();
		$meta_query[] = array(
			'key'   => '_is_new',
			'value' => 'yes',
		);
		$new_products = new \WP_Query(
			array(
				'posts_per_page'   => -1,
				'post_type'        => 'product',
				'fields'           => 'ids',
				'suppress_filters' => true,
				'meta_query'       => $meta_query,
			)
		);

		if ( $new_products->have_posts() ) {
			$product_ids = array_merge( $product_ids, $new_products->posts );
		}

		// Get products after selected days.
		if ( Helper::get_option( 'badges_new' ) ) {
			$newness = intval( Helper::get_option( 'badges_newness' ) );

			if ( $newness > 0 ) {
				$new_products = new \WP_Query(
					array(
						'posts_per_page'   => -1,
						'post_type'        => 'product',
						'fields'           => 'ids',
						'suppress_filters' => true,
						'date_query'       => array(
							'after' => date( 'Y-m-d', strtotime( '-' . $newness . ' days' ) ),
						),
					)
				);

				if ( $new_products->have_posts() ) {
					$product_ids = array_merge( $product_ids, $new_products->posts );
				}
			}
		}

		set_transient( 'shopwell_woocommerce_products_new', $product_ids, DAY_IN_SECONDS );

		return $product_ids;
	}

	/**
	 * Clear new product ids cache with the sale schedule which is run daily.
	 */
	public static function shopwell_woocommerce_clear_cache_daily() {
		delete_transient( 'shopwell_woocommerce_products_new' );
		delete_transient( 'shopwell_woocommerce_last_product_id' );
	}

	/**
	 * Clear new product ids cache when update/trash/delete products.
	 *
	 * @param int $post_id
	 */
	public static function shopwell_woocommerce_clear_cache( $post_id ) {
		if ( 'product' != get_post_type( $post_id ) ) {
			return;
		}

		delete_transient( 'shopwell_woocommerce_products_new' );
		delete_transient( 'shopwell_woocommerce_last_product_id' );
	}

	/**
	 * WooCommerce pagination arguments.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args The pagination args.
	 *
	 * @return array
	 */
	public function pagination_args( $args ) {
		$args['prev_text'] = \Shopwell\Icon::get_svg( 'left', 'ui', '' );
		$args['next_text'] = \Shopwell\Icon::get_svg( 'right', 'ui', '' );

		return $args;
	}

	/**
	 * Show product on sale
	 *
	 * @return void
	 */
	public function products_on_sale( $q ) {
		if ( is_admin() || ! isset( $_GET['on_sale'] ) || empty( $_GET['on_sale'] ) ) {
			return;
		}

		if ( $_GET['on_sale'] == 1 ) {
			$q->set( 'post__in', (array) wc_get_product_ids_on_sale() );
		}
	}

	/**
	 * Changes shortcode products orderby
	 *
	 * @since 1.0.0
	 *
	 * @param array $args The query.
	 * @param array $attributes The attributes.
	 *
	 * @return array
	 */
	public function shortcode_products_orderby( $args, $attributes ) {
		if ( ! empty( $attributes['class'] ) ) {
			$classes = explode( ',', $attributes['class'] );

			// Product Brands
			if ( in_array( 'sc_brand', $classes ) ) {
				$args['tax_query'][] = array(
					'taxonomy' => 'product_brand',
					'terms'    => array_map( 'sanitize_title', $classes ),
					'field'    => 'slug',
					'operator' => 'IN',
				);
			}

			if ( in_array( 'sc_outofstock', $classes ) ) {
				$args['meta_query'] = apply_filters(
					'shopwell_product_outofstock_meta_query',
					array_merge(
						WC()->query->get_meta_query(),
						array(
							array(
								'key'     => '_stock_status',
								'value'   => 'outofstock',
								'compare' => 'NOT IN',
							),
						)
					)
				);
			}
		}

		return $args;
	}

	/**
	 * Change shipping label
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function cart_shipping_method_full_label( $label, $method ) {
		$has_cost  = 0 < $method->cost;
		$free_cost = ! $has_cost && in_array( $method->get_method_id(), array( 'flat_rate' ), true );

		if ( $free_cost ) {
			$label .= ':' . esc_html__( ' Free Shipping', 'shopwell' );
		}

		return $label;
	}

		/**
		 * Display product id hidden
		 *
		 * @since 1.0
		 *
		 * @return void
		 */
	public function product_id_hidden() {
		global $product;
		echo '<input class="shopwell_product_id" type="hidden" data-title="' . esc_attr( $product->get_title() ) . '" value="' . esc_attr( $product->get_id() ) . '">';
	}

	/**
	 * Format a sale price for display.
	 *
	 * @since  3.0.0
	 * @param  string $regular_price Regular price.
	 * @param  string $sale_price    Sale price.
	 * @return string
	 */
	public function format_sale_price( $price, $regular_price, $sale_price ) {
		global $product, $wp_query;

		if ( ! is_singular( 'product' ) && ! is_singular( 'shopwell_builder' ) ) {
			return $price;
		}

		$price = '<ins>' . ( is_numeric( $sale_price ) ? wc_price( $sale_price ) : $sale_price ) . '</ins> <del aria-hidden="true">' . ( is_numeric( $regular_price ) ? wc_price( $regular_price ) : $regular_price ) . '</del>';

		if ( $product && is_object( $product ) && $product->get_id() == $wp_query->post->ID ) {
			$price .= \Shopwell\WooCommerce\Helper::price_save( $regular_price, $sale_price );
		}

		return $price;
	}

	/**
	 * Change comment note
	 *
	 * @return void
	 */
	public function change_add_comment_form_before( $defaults ) {
		if ( ! is_singular( 'product' ) ) {
			return;
		}
		$defaults['comment_notes_before'] = '<p class="comment-notes">' . esc_html__( 'Please complete all the fields below to tell us about your experience with this product.', 'shopwell' ) . '</p>';
		return $defaults;
	}

		/**
		 * Reviews title
		 *
		 * @return array
		 */
	public function reviews_title() {
		global $product;
		return esc_html__( 'Customer Reviews', 'shopwell' ) . ' (' . $product->get_review_count() . ')';
	}

	/**
	 * Open comment wrapper
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function open_comment_wrapper() {
		echo '<div class="comment-wrapper">';
	}

	/**
	 * Close comment wrapper
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function close_comment_wrapper() {
		echo '</div>';
	}

	/**
	 * Table Ratings
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function table_ratings() {
		global $product;

		$rating_count   = $product->get_rating_count();
		$rating_arr     = $product->get_rating_counts();
		$review_count   = $product->get_review_count();
		$average        = $product->get_average_rating();
		$average_rating = round( $average, 2 );
		$bar_rating     = array();
		for ( $i = 5; $i > 0; $i-- ) {
			$count = 0;
			if ( isset( $rating_arr[ $i ] ) ) {
				$count = $rating_arr[ $i ];
			}

			$rating_label = $i . ' ' . \Shopwell\Icon::get_svg( 'star' );

			$bar_rating[] = sprintf(
				'<div class="star-item %s-stars">' .
					'<div class="slabel">' .
					'%s' .
					'</div>' .
					'<div class="sbar">' .
						'<div class="bar-content">' .
							'<span class="bar-star bar-star--%s %s"></span>' .
						'</div>' .
					'</div>' .
					'<div class="svalue">' .
					'%s' .
					'</div>' .
				'</div>',
				esc_attr( $i ),
				$rating_label,
				esc_attr( $i ),
				$count > 0 ? 'active' : '',
				esc_attr( $count )
			);
		}

		echo '<div class="shopwell-product-rating">';
			echo '<div class="shopwell-product-rating__title">' . esc_html__( 'Ratings', 'shopwell' ) . '</div>';
			echo '<div class="shopwell-product-rating__wrapper">';
				printf(
					'<div class="shopwell-product-rating__heading">
									<h3 class="shopwell-product-rating__average-value">%s</h3>
									<div class="shopwell-product-rating__rating-count">
									%s
									<div class="shopwell-product-rating__count">%s</div>
									</div>
								</div>
								<div class="shopwell-product-rating__bar">%s</div>
								<div class="shopwell-product-rating__summary">
									<h4>%s</h4>
									<p>%s</p>
									<div class="shopwell-form-review shopwell-button shopwell-button--bg-color-black" data-toggle="modal" data-target="shopwell-review-form">%s</div>
								</div>',
					number_format( $average_rating, 1 ),
					wc_get_rating_html( $average, $rating_count ),
					/* translators: %s: Review count. */
					sprintf( _n( '%s Product Rating', '%s Product Ratings', $review_count, 'shopwell' ), esc_html( $review_count ) ),
					implode( '', $bar_rating ),
					esc_html__( 'Review this product', 'shopwell' ),
					esc_html__( 'Share your thoughts with other customers', 'shopwell' ),
					esc_html__( 'Write a review', 'shopwell' )
				);
			echo '</div>';
		echo '</div>';
	}
}
