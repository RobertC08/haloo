<?php
/**
 * Hooks of Products Recently Viewed.
 *
 * @package Shopwell
 */

namespace Shopwell\WooCommerce;

use Shopwell\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class of Products Recently Viewed template.
 */
class Products_Recently_Viewed {
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
	 * Instance
	 *
	 * @var $instance
	 */
	private $product_ids;

	/**
	 * Instantiate the object.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function __construct() {
		$viewed_products   = ! empty( $_COOKIE['woocommerce_recently_viewed'] ) ? (array) explode( '|', $_COOKIE['woocommerce_recently_viewed'] ) : array();
		$this->product_ids = array_reverse( array_filter( array_map( 'absint', $viewed_products ) ) );

		// Track Product View
		add_action( 'template_redirect', array( $this, 'track_product_view' ) );
		add_action( 'wc_ajax_shopwell_recently_viewed_products', array( $this, 'recently_viewed_products' ) );
	}

	/**
	 * Track product views
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function track_product_view() {
		if ( ! is_singular( 'product' ) ) {
			return;
		}

		global $post;

		if ( empty( $_COOKIE['woocommerce_recently_viewed'] ) ) {
			$viewed_products = array();
		} else {
			$viewed_products = (array) explode( '|', $_COOKIE['woocommerce_recently_viewed'] );
		}

		if ( ! empty( $post->ID ) && ! in_array( $post->ID, $viewed_products ) ) {
			$viewed_products[] = $post->ID;
		}

		if ( sizeof( $viewed_products ) > 15 ) {
			array_shift( $viewed_products );
		}

		// Store for session only
		wc_setcookie( 'woocommerce_recently_viewed', implode( '|', $viewed_products ), time() + 60 * 60 * 24 * 30 );
	}

	/**
	 * Recently Viewed Products
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function recently_viewed_products() {
		self::get_recently_viewed_products();
	}


	/**
	 * Get recently viewed ids
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public static function get_product_recently_viewed_ids() {
		$viewed_products = ! empty( $_COOKIE['woocommerce_recently_viewed'] ) ? (array) explode( '|', $_COOKIE['woocommerce_recently_viewed'] ) : array();

		return array_reverse( array_filter( array_map( 'absint', $viewed_products ) ) );
	}

	/**
	 * Get products recently viewed
	 *
	 * @return void
	 */
	public static function get_recently_viewed_products() {
		$products_ids = self::get_product_recently_viewed_ids();

		if ( empty( $products_ids ) ) {
			?>
				<div class="no-products">
					<p><?php echo esc_html__( 'No products in recent viewing history.', 'shopwell' ); ?></p>
				</div>

			<?php
		} else {
			// PERFORMANCE FIX: Use WP_Query for batch loading instead of individual get_post() calls
			// This reduces database queries from 15+ to 1
			$args = array(
				'post_type'           => 'product',
				'post__in'            => array_slice( $products_ids, 0, 15 ), // Limit to 15 products
				'posts_per_page'      => 15,
				'orderby'             => 'post__in', // Maintain order from cookie
				'ignore_sticky_posts' => 1,
				'no_found_rows'       => true, // Skip pagination count for better performance
			);

			$query = new \WP_Query( $args );

			if ( $query->have_posts() ) {
				woocommerce_product_loop_start();

				while ( $query->have_posts() ) {
					$query->the_post();
					wc_get_template_part( 'content', 'product' );
				}

				woocommerce_product_loop_end();
			} else {
				?>
					<div class="no-products">
						<p><?php echo esc_html__( 'No products in recent viewing history.', 'shopwell' ); ?></p>
					</div>
				<?php
			}

			wp_reset_postdata();
			wc_reset_loop();
		}
	}
}
