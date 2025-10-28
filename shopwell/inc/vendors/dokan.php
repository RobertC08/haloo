<?php

/**
 * WooCommerce dokan functions
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
class Dokan {
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
		add_filter( 'body_class', array( $this, 'body_classes' ) );
		add_filter( 'shopwell_product_summary_open_classes', array( $this, 'summary_classes' ) );

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 30 );

		// Add vendor information
		add_filter( 'dokan_product_single_after_store_name', array( $this, 'add_text_vendor_information' ) );

		// Add vendor information
		add_action( 'shopwell_woocommerce_product_quickview_summary', array( $this, 'vendor_information' ), 60 );

		if ( \Shopwell\Helper::get_option( 'product_layout' ) == '6' ) {
			add_action( 'woocommerce_after_single_product_summary', array( $this, 'vendor_information' ), 1 );
		}

		// Page header
		add_filter( 'shopwell_get_default_page_header_elements', array( $this, 'page_header_elements' ) );

		// Change icon social
		add_filter( 'dokan_profile_social_fields', array( $this, 'socials' ) );

		$this->product_card_layout();

		if ( \Shopwell\Helper::get_option( 'product_tab_vendor_info' ) ) {
			add_filter( 'woocommerce_product_tabs', array( $this, 'unset_vendor_info_tab' ), 98 );
		}
		if ( \Shopwell\Helper::get_option( 'product_tab_more_products' ) ) {
			add_filter( 'woocommerce_product_tabs', array( $this, 'unset_more_products_tab' ), 98 );
		}
	}

	/**
	 * Adds custom classes to the array of body classes.
	 *
	 * @since 1.0.0
	 *
	 * @param array $classes Classes for the body element.
	 *
	 * @return array
	 */
	public function body_classes( $classes ) {
		// Add class is dokan pro actived
		if ( class_exists( 'Dokan_Pro' ) ) {
			$classes[] = 'shopwell-dokan-pro';
		}

		if ( function_exists( 'dokan_is_store_listing' ) && dokan_is_store_listing() ) {
			$classes[] = 'shopwell-dokan-store-list-page';
		}

		if ( function_exists( 'dokan_is_store_page' ) && dokan_is_store_page() ) {
			$classes[] = 'shopwell-dokan-store-page';
		}

		return $classes;
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
		wp_enqueue_style( 'shopwell-dokan', get_template_directory_uri() . '/assets/css/vendors/dokan.css', array(), '20230203' );
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

			// Layout 6
			case '6':
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

		if ( ! function_exists( 'dokan_get_store_url' ) ) {
			return;
		}

		if ( ! intval( \Shopwell\Helper::get_option( 'product_card_vendor_name' ) ) ) {
			return;
		}

		global $product;
		$author_id = get_post_field( 'post_author', $product->get_id() );
		$author    = get_user_by( 'id', $author_id );
		if ( empty( $author ) ) {
			return;
		}

		$shop_info = get_user_meta( $author_id, 'dokan_profile_settings', true );
		$shop_name = $author->display_name;
		if ( $shop_info && isset( $shop_info['store_name'] ) && $shop_info['store_name'] ) {
			$shop_name = $shop_info['store_name'];
		}

		$classes = $product->get_attributes() ? 'show-attributes' : '';

		?>

		<div class="sold-by-meta <?php echo esc_attr( $classes ); ?>">
			<a href="<?php echo esc_url( dokan_get_store_url( $author_id ) ); ?>">
				<?php echo get_avatar( $author_id ); ?>
				<span class="vendor-name"><?php echo esc_html( $shop_name ); ?></span>
			</a>
		</div>

		<?php
	}

	/**
	 * Vendor information box
	 *
	 * @return void
	 */
	public static function vendor_information() {
		if ( ! class_exists( 'WeDevs_Dokan' ) ) {
			return;
		}

		if ( 'off' === dokan_get_option( 'show_vendor_info', 'dokan_general', 'off' ) ) {
			return;
		}

		global $product;

		$vendor       = dokan_get_vendor_by_product( $product );
		$store_info   = $vendor->get_shop_info();
		$store_rating = $vendor->get_rating();

		dokan_get_template_part(
			'vendor-store-info',
			'',
			array(
				'vendor'       => $vendor,
				'store_info'   => $store_info,
				'store_rating' => $store_rating,
			)
		);
	}

	/**
	 * Add text vendor name
	 *
	 * @return string
	 */
	public static function add_text_vendor_information( $vendor ) {
		if ( ! class_exists( 'WeDevs_Dokan' ) ) {
			return;
		}

		if ( 'off' === dokan_get_option( 'show_vendor_info', 'dokan_general', 'off' ) ) {
			return;
		}

		echo '<span class="vendor-text">' . esc_html__( 'Store', 'shopwell' ) . '</span>';

		return $vendor;
	}


	/**
	 * Vendor header.
	 *
	 *  @return void
	 */
	public function page_header_elements( $items ) {
		if ( function_exists( 'dokan_is_store_listing' ) && dokan_is_store_listing() ) {
			$items = \Shopwell\Helper::get_option( 'store_list_page_header' );
		} elseif ( function_exists( 'dokan_is_store_page' ) && dokan_is_store_page() ) {
			$items = \Shopwell\Helper::get_option( 'store_page_page_header' );
		}

		return $items;
	}

	/**
	 * Socials profile
	 *
	 *  @return void
	 */
	public function socials( $fields ) {
		$fields = array(
			'fb'        => array(
				'icon'  => 'facebook-square',
				'title' => __( 'Facebook', 'shopwell' ),
			),
			'twitter'   => array(
				'icon'  => 'x-twitter',
				'title' => __( 'X', 'shopwell' ),
			),
			'pinterest' => array(
				'icon'  => 'pinterest',
				'title' => __( 'Pinterest', 'shopwell' ),
			),
			'linkedin'  => array(
				'icon'  => 'linkedin',
				'title' => __( 'LinkedIn', 'shopwell' ),
			),
			'youtube'   => array(
				'icon'  => 'youtube',
				'title' => __( 'Youtube', 'shopwell' ),
			),
			'instagram' => array(
				'icon'  => 'instagram',
				'title' => __( 'Instagram', 'shopwell' ),
			),
			'flickr'    => array(
				'icon'  => 'flickr',
				'title' => __( 'Flickr', 'shopwell' ),
			),
		);

		return $fields;
	}

	/**
	 * Unset vendor_info tab
	 *
	 * @return array
	 */
	public function unset_vendor_info_tab( $tabs ) {
		if ( isset( $tabs['seller'] ) ) {
			unset( $tabs['seller'] );
		}

		return $tabs;
	}

	/**
	 * Unset more_products tab
	 *
	 * @return array
	 */
	public function unset_more_products_tab( $tabs ) {
		if ( isset( $tabs['more_seller_product'] ) ) {
			unset( $tabs['more_seller_product'] );
		}

		return $tabs;
	}
}
