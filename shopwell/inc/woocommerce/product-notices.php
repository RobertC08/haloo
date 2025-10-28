<?php
/**
 * WooCommerce Notices template hooks.
 *
 * @package Shopwell
 */

namespace Shopwell\WooCommerce;

use Shopwell\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class of WooCommerce Notices
 */

class Product_Notices {
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
		add_filter( 'shopwell_wp_script_data', array( $this, 'notices_script_data' ) );

		// Popup add to cart HTML
		add_action( 'wp_footer', array( $this, 'popup_add_to_cart' ) );

		// Popup add to cart Template
		add_action( 'wc_ajax_shopwell_product_popup_recommended', array( $this, 'product_template_recommended' ) );

		add_action( 'shopwell_product_popup_atc_recommendation', array( $this, 'products_recommendation' ), 5 );
	}

	/**
	 * Get notices script data
	 *
	 * @since 1.0.0
	 *
	 * @param $data
	 *
	 * @return array
	 */
	public function notices_script_data( $data ) {
		$data['added_to_cart_notice'] = array(
			'header_cart_icon_behaviour'  => Helper::get_option( 'header_cart_icon_behaviour' ),
			'added_to_cart_notice_layout' => Helper::get_option( 'added_to_cart_notice' ),
		);

		if ( intval( Helper::get_option( 'added_to_wishlist_notice' ) ) && shortcode_exists( 'wcboost_wishlist_button' ) ) {
			if ( shortcode_exists( 'wcboost_wishlist_button' ) ) {
				$url = wc_get_page_permalink( 'wishlist' );
			}

			$data['added_to_wishlist_notice'] = array(
				'added_to_wishlist_text'    => esc_html__( 'has been added to your wishlist.', 'shopwell' ),
				'added_to_wishlist_texts'   => esc_html__( 'have been added to your wishlist.', 'shopwell' ),
				'wishlist_view_text'        => esc_html__( 'View Wishlist', 'shopwell' ),
				'wishlist_view_link'        => esc_url( $url ),
				'wishlist_notice_auto_hide' => intval( Helper::get_option( 'wishlist_notice_auto_hide' ) ) > 0 ? intval( Helper::get_option( 'wishlist_notice_auto_hide' ) ) * 1000 : 0,
			);
		}

		if ( intval( Helper::get_option( 'added_to_compare_notice' ) ) && function_exists( 'wcboost_products_compare' ) && get_option( 'wcboost_products_compare_added_behavior' ) != 'popup' ) {
			$url = wc_get_page_permalink( 'compare' );

			$data['added_to_compare_notice'] = array(
				'added_to_compare_text'    => esc_html__( 'has been added to your compare.', 'shopwell' ),
				'added_to_compare_texts'   => esc_html__( 'have been added to your compare.', 'shopwell' ),
				'compare_view_text'        => esc_html__( 'View Compare', 'shopwell' ),
				'compare_view_link'        => esc_url( $url ),
				'compare_notice_auto_hide' => intval( Helper::get_option( 'compare_notice_auto_hide' ) ) > 0 ? intval( Helper::get_option( 'compare_notice_auto_hide' ) ) * 1000 : 0,
			);
		}

		return $data;
	}

	/**
	 * Get popup add to cart
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function popup_add_to_cart() {
		if ( \Shopwell\WooCommerce\Helper::is_cartflows_template() ) {
			return;
		}

		if ( is_404() ) {
			return;
		}

		if ( Helper::get_option( 'added_to_cart_notice' ) != 'popup' ) {
			return;
		}

		get_template_part( 'template-parts/modals/notices' );
	}

	/**
	 * Get product recommended
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function product_template_recommended() {
		ob_start();

		if ( isset( $_POST['product_id'] ) && ! empty( $_POST['product_id'] ) ) {
			$product_id = $_POST['product_id'];
			$product    = wc_get_product( $product_id );

			$limit = Helper::get_option( 'added_to_cart_notice_products_limit' );
			$type  = Helper::get_option( 'added_to_cart_notice_products' );

			$query = new \stdClass();
			if ( 'related_products' == $type ) {
				$product_ids = maybe_unserialize( get_post_meta( $product_id, 'shopwell_related_product_ids', true ) );
				if ( ! empty( $product_ids ) ) {
					$related_products = array_filter( array_map( 'wc_get_product', $product_ids ), 'wc_products_array_filter_visible' );
				} else {
					$related_products = array_filter( array_map( 'wc_get_product', wc_get_related_products( $product_id, $limit, $product->get_upsell_ids() ) ), 'wc_products_array_filter_visible' );
					$related_products = wc_products_array_orderby( $related_products, 'rand', 'desc' );
				}

				$query->posts = $related_products;
			} elseif ( 'upsells_products' == $type ) {
				$upsells = wc_products_array_orderby( array_filter( array_map( 'wc_get_product', $product->get_upsell_ids() ), 'wc_products_array_filter_visible' ), 'rand', 'desc' );

				$query->posts = $upsells;
			}

			if ( count( $query->posts ) ) {
				$this->products_recommended_content( $query->posts );
			}
		}

		$output = ob_get_clean();
		wp_send_json_success( $output );
		die();
	}

	/**
	 * Get products recommended
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function products_recommendation() {
		if ( ! class_exists( 'WC_Shortcode_Products' ) ) {
			return;
		}

		$limit = Helper::get_option( 'added_to_cart_notice_products_limit' );
		$type  = Helper::get_option( 'added_to_cart_notice_products' );

		if ( 'none' == $type ) {
			return;
		}

		if ( 'related_products' == $type || 'upsells_products' == $type ) {
			echo '<div class="shopwell-product-popup-atc__recommendation"></div>';
			return;
		}

		$atts = array(
			'per_page'     => intval( $limit ),
			'category'     => '',
			'cat_operator' => 'IN',
		);

		switch ( $type ) {
			case 'sale_products':
			case 'top_rated_products':
				$atts = array_merge(
					array(
						'orderby' => 'title',
						'order'   => 'ASC',
					),
					$atts
				);
				break;

			case 'recent_products':
				$atts = array_merge(
					array(
						'orderby' => 'date',
						'order'   => 'DESC',
					),
					$atts
				);
				break;

			case 'featured_products':
				$atts = array_merge(
					array(
						'orderby' => 'date',
						'order'   => 'DESC',
					),
					$atts
				);
				break;
		}

		$args  = new \WC_Shortcode_Products( $atts, $type );
		$args  = $args->get_query_args();
		$query = new \WP_Query( $args );

		if ( ! count( $query->posts ) ) {
			return;
		}

		echo '<div class="shopwell-product-popup-atc__recommendation loaded">';
		$this->products_recommended_content( $query->posts );
		wp_reset_postdata();
		echo '</div>';
	}

	/**
	 * Get products recommended content
	 *
	 * @since 1.0.0
	 *
	 * @param $query_posts
	 *
	 * @return void
	 */
	public function products_recommended_content( $query_posts ) {
		?>
		<div class="recommendation-heading">
			<h2 class="product-heading"> <?php echo esc_html__( 'You may also like', 'shopwell' ); ?> </h2>
			<div class="shopwell-swiper-buttons">
				<?php echo \Shopwell\Icon::get_svg( 'left', 'ui', array( 'class' => 'swiper-button shopwell-swiper-button-prev' ) ); ?>
				<?php echo \Shopwell\Icon::get_svg( 'right', 'ui', array( 'class' => 'swiper-button shopwell-swiper-button-next' ) ); ?>
			</div>
		</div>
		<div class="swiper-container linked-products-carousel">
			<ul class="products swiper-wrapper">
			<?php
			foreach ( $query_posts as $product_id ) {
				$_product = is_numeric( $product_id ) ? wc_get_product( $product_id ) : $product_id;
				?>

				<li class="product">
					<a href="<?php echo esc_url( $_product->get_permalink() ); ?>">
						<?php echo wp_kses_post( $_product->get_image( 'woocommerce_thumbnail' ) ); ?>
						<span class="woocommerce-loop-product__title"><?php echo wp_kses_post( $_product->get_name() ); ?></span>
						<span class="price"><?php echo wp_kses_post( $_product->get_price_html() ); ?></span>
					</a>
				</li>

				<?php
			}
			?>

			</ul>
		</div>
		<?php
	}
}
