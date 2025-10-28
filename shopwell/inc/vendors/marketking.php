<?php

/**
 * WooCommerce Markeking functions
 *
 * @package shopwell
 */

namespace Shopwell\Vendors;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


/**
 * Class of Vendor Dokan
 *
 * @version 1.0
 */
class Marketking {
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
	 * Construction function
	 *
	 * @since  1.0
	 * @return Shopwell_Vendor
	 */
	public function __construct() {
		add_filter( 'shopwell_product_summary_open_classes', array( $this, 'summary_classes' ) );

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 30 );

		add_action( 'woocommerce_before_shop_loop', array( $this, 'open_result_ordering' ), 19 );
		add_action( 'woocommerce_before_shop_loop', array( $this, 'close_result_ordering' ), 31 );

		add_filter( 'marketking_show_vendor_product_page', '__return_false' );

		$this->product_card_layout();

		// Add vendor information
		add_action( 'shopwell_woocommerce_product_quickview_summary', array( $this, 'vendor_information' ), 60 );

		if ( \Shopwell\Helper::get_option( 'product_layout' ) == '6' ) {
			add_action( 'woocommerce_after_single_product_summary', array( $this, 'vendor_information' ), 1 );
		} else {
			add_action( 'woocommerce_product_meta_end', array( $this, 'vendor_information' ), 1 );
		}
	}


	/**
	 * Adds custom classes to the array of product summary classes.
	 *
	 * @since 1.0.0
	 *
	 * @param array $classes Classes for the product summary element.
	 *
	 * @return array
	 */
	public function summary_classes( $classes ) {
		$classes = ' show-vendor';

		return $classes;
	}

	/**
	 * Enqueue styles and scripts.
	 */
	public function enqueue_scripts() {
		wp_enqueue_style( 'shopwell-marketking', get_template_directory_uri() . '/assets/css/vendors/marketking.css', array(), '20231023' );
	}

	/**
	 * Product Card layout
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function product_card_layout() {
		$loop_layout = apply_filters( 'shopwell_product_card_layout', \Shopwell\Helper::get_option( 'product_card_layout' ) );

		switch ( $loop_layout ) {
			// Layout 1
			case '1':
				add_action( 'woocommerce_after_shop_loop_item_title', array( $this, 'vendor_name' ), 15 );
				break;

			// Layout 2
			case '2':
				add_action( 'woocommerce_after_shop_loop_item_title', array( $this, 'vendor_name' ), 51 );
				break;

			// Layout 3
			case '3':
				add_action( 'woocommerce_after_shop_loop_item', array( $this, 'vendor_name' ), 0 );
				break;

			// Layout 4
			case '4':
				add_action( 'woocommerce_shop_loop_item_title', array( $this, 'vendor_name' ), 15 );
				break;

			// Layout 5
			case '5':
				add_action( 'woocommerce_after_shop_loop_item_title', array( $this, 'vendor_name' ), 30 );
				break;

			default:
				break;
		}
	}

	/**
	 * Vendor name.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function vendor_name() {
		global $product;

		if ( ! function_exists( 'marketking' ) ) {
			return;
		}

		if ( ! intval( \Shopwell\Helper::get_option( 'product_card_vendor_name' ) ) ) {
			return;
		}

		global $post;
		$vendor_id = marketking()->get_product_vendor( $post->ID );
		if ( empty( $vendor_id ) ) {
			return;
		}
		$store_name = marketking()->get_store_name_display( $vendor_id );
		$store_img  = marketking()->get_display_icon_image( $vendor_id );
		$classes    = $product->get_attributes() ? 'show-attributes' : '';

		?>

		<div class="sold-by-meta <?php echo esc_attr( $classes ); ?>">
			<a href="<?php echo esc_url( marketking()->get_store_link( $vendor_id ) ); ?>">
				<?php if ( $store_img ) { ?>
					<img src="<?php echo esc_url( $store_img ); ?>" alt="<?php echo esc_attr( $store_name ); ?>" width="150" height="150">
				<?php } ?>
				<span class="vendor-name"><?php echo esc_html( $store_name ); ?></span>
			</a>
		</div>

		<?php
	}

	public function is_vendor_page() {
		global $post;
		if ( ! $post || empty( $post->ID ) ) {
			return false;
		}
		if ( intval( $post->ID ) === intval( apply_filters( 'wpml_object_id', get_option( 'marketking_stores_page_setting', 'none' ), 'post', true ) ) ) {
			return true;
		}

		return false;
	}

	public function open_result_ordering() {
		if ( ! $this->is_vendor_page() ) {
			return;
		}
		echo '<div class="shopwell-vendor-result-ordering">';
	}

	public function close_result_ordering() {
		if ( ! $this->is_vendor_page() ) {
			return;
		}
		echo '</div>';
	}

	/**
	 * Vendor information box
	 *
	 * @return void
	 */
	public static function vendor_information() {
		global $post;
		$vendor_id = marketking()->get_product_vendor( $post->ID );
		if ( empty( $vendor_id ) ) {
			return;
		}
		$store_name = marketking()->get_store_name_display( $vendor_id );
		$store_img  = marketking()->get_display_icon_image( $vendor_id );
		?>
		<div class="shopwell-vendor-info">
			<a href="<?php echo esc_url( marketking()->get_store_link( $vendor_id ) ); ?>" class="shopwell-vendor-info__link">
				<?php if ( $store_img ) { ?>
					<div class="shopwell-vendor-info__image">
						<img src="<?php echo esc_url( $store_img ); ?>" alt="<?php echo esc_attr( $store_name ); ?>" width="150" height="150">
					</div>
				<?php } ?>

				<div class="shopwell-vendor-info__content">
					<span class="vendor-text"><?php esc_html_e( 'Store', 'shopwell' ); ?></span>
					<h5 class="vendor-name"><?php echo esc_html( $store_name ); ?></h5>
				</div>
			</a>
			<div class="shopwell-vendor-info__rating">
				<?php
					$rating = marketking()->get_vendor_rating( $vendor_id );
					self::generate_ratings_vendor( $rating );
				?>
			</div>
		</div>

		<?php
	}

	/**
	 * Change star ratings
	 *
	 * @return string
	 */
	public static function generate_ratings_vendor( $store_rating ) {
		$html  = '<span class="max-rating rating-stars">'
				. \Shopwell\Icon::get_svg( 'star' )
				. \Shopwell\Icon::get_svg( 'star' )
				. \Shopwell\Icon::get_svg( 'star' )
				. \Shopwell\Icon::get_svg( 'star' )
				. \Shopwell\Icon::get_svg( 'star' )
				. '</span>';
		$html .= '<span class="user-rating rating-stars" style="width:' . ( ( $store_rating['rating'] / 5 ) * 100 ) . '%">'
				. \Shopwell\Icon::get_svg( 'star' )
				. \Shopwell\Icon::get_svg( 'star' )
				. \Shopwell\Icon::get_svg( 'star' )
				. \Shopwell\Icon::get_svg( 'star' )
				. \Shopwell\Icon::get_svg( 'star' )
				. '</span>';

		$count       = $store_rating['count'];
		$rating      = $store_rating['rating'];
		/* translators: 1: rating, 2: count */
		$review_html = sprintf( _n( '%1$s (%2$s Review)', '%1$s (%2$s Reviews)', $count, 'shopwell' ), $rating, $count );
		echo '<div class="star-rating">' . $html . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<p class="ratings-count">' . $review_html . '</p>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
