<?php
/**
 * Product Card hooks.
 *
 * @package Shopwell
 */

namespace Shopwell\WooCommerce;

use Shopwell\Helper;
use Shopwell\WooCommerce;
use Shopwell\Icon;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class of Product Card
 */
class Product_Card {
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
		if ( is_admin() ) {
			// add actions for elementor
			add_action( 'admin_init', array( $this, 'actions' ) );
		} else {
			// add actions for frontend
			add_action( 'wp', array( $this, 'actions' ) );
		}
	}

	public function actions() {
		add_filter( 'shopwell_wp_script_data', array( $this, 'card_script_data' ) );

		add_filter( 'woocommerce_product_loop_start', array( $this, 'loop_start' ), 5 );

		// Product inner wrapper
		add_action( 'woocommerce_before_shop_loop_item', array( $this, 'product_wrapper_open' ), 1 );
		add_action( 'woocommerce_after_shop_loop_item', array( $this, 'product_wrapper_close' ), 1000 );

		// Remove wrapper link
		remove_action( 'woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10 );
		remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5 );

		// Replace the default sale flash.
		remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash' );
		add_action( 'woocommerce_before_shop_loop_item', array( '\Shopwell\WooCommerce\Badges', 'badges' ), 2 );

		// Change product thumbnail.
		remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail' );
		add_action( 'woocommerce_before_shop_loop_item_title', array( $this, 'product_loop_thumbnail' ), 1 );

		// Product summarry
		add_action( 'woocommerce_before_shop_loop_item_title', array( $this, 'product_summary_open' ), 5 );
		add_action( 'woocommerce_after_shop_loop_item', array( $this, 'product_summary_close' ), 1 );

		// Add taxonomy
		add_action( 'woocommerce_before_shop_loop_item_title', array( $this, 'meta_wrapper_open' ), 55 );
		add_action( 'woocommerce_before_shop_loop_item_title', array( $this, 'product_taxonomy' ), 55 );
		add_action( 'woocommerce_before_shop_loop_item_title', array( $this, 'product_sku' ), 55 );
		add_action( 'woocommerce_before_shop_loop_item_title', array( $this, 'meta_wrapper_close' ), 55 );

		// Change the product title.
		remove_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title' );
		add_action( 'woocommerce_shop_loop_item_title', array( $this, 'product_card_title' ) );

		remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );

		if ( ! Helper::get_option( 'product_card_add_to_cart_button' ) || ! apply_filters( 'shopwell_product_card_show_atc', true ) ) {
			remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart' );
		}

		add_filter( 'woocommerce_product_add_to_cart_text', array( $this, 'custom_cart_button_text' ) );

		if ( Helper::is_catalog() && Helper::get_option( 'catalog_product_description' ) ) {
			add_action( 'woocommerce_before_shop_loop_item_title', array( $this, 'short_description' ) );
		}

		// Remove vendor in product loop
		add_filter( 'wcfmmp_is_allow_archive_product_sold_by', '__return_false' );

		$this->product_card_layout();
	}

	/**
	 * Loop start.
	 *
	 * @since 1.0.0
	 *
	 * @param string $html Open loop wrapper with the <ul class="products"> tag.
	 *
	 * @return string
	 */
	public function loop_start( $html ) {
		$html    = '';
		$classes = array(
			'products',
		);

		$loop_layout = apply_filters( 'shopwell_product_card_layout', Helper::get_option( 'product_card_layout' ) );

		$classes[] = 'product-card-layout-' . $loop_layout;

		$classes[] = 'columns-' . wc_get_loop_prop( 'columns' );

		if ( in_array( $loop_layout, array( '1', '2' ) ) ) {
			if ( ( ! Helper::get_option( 'product_card_add_to_cart_button' ) || ! apply_filters( 'shopwell_product_card_show_atc', true ) ) && ! Helper::get_option( 'product_card_quick_view_button' ) ) {
				$classes[] = 'product-no-buttons';
			}
		} elseif ( in_array( $loop_layout, array( '3' ) ) ) {
			if ( ! Helper::get_option( 'product_card_add_to_cart_button' ) ) {
				$classes[] = 'product-no-buttons';
			}
		}

		if ( $mobile_pl_col = intval( Helper::get_option( 'mobile_product_columns' ) ) ) {
			$classes[] = 'mobile-col-' . $mobile_pl_col;
		}

		if ( Helper::is_catalog() && ! Helper::get_option( 'mobile_product_list_desc' ) ) {
			$classes[] = 'product-list-no-desc-mobile';
		}

		$classes[] = 'mobile-featured-icons--' . Helper::get_option( 'mobile_product_card_featured_icons' );

		if ( intval( Helper::get_option( 'mobile_product_card_atc' ) ) ) {
			$classes[] = 'mobile-show-atc';
		}

		$html = '<ul class="' . esc_attr( implode( ' ', $classes ) ) . '">';

		return $html;
	}

	/**
	 * Product Card layout
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function product_card_layout() {
		$loop_layout = apply_filters( 'shopwell_product_card_layout', Helper::get_option( 'product_card_layout' ) );

		switch ( $loop_layout ) {
			// Layout 1
			case '1':
				if ( Helper::get_option( 'product_card_stars_rating' ) ) {
					add_action( 'woocommerce_after_shop_loop_item_title', array( $this, 'product_rating' ), 5 );
				}

				// featured icons on product thumbnail
				add_action( 'shopwell_product_loop_thumbnail', array( $this, 'product_featured_icons_open' ), 5 );

				if ( ! Helper::get_option( 'mobile_product_card_atc' ) && Helper::get_option( 'mobile_product_card_featured_icons' ) != 'none' ) {
					add_action( 'shopwell_product_loop_thumbnail', 'woocommerce_template_loop_add_to_cart', 8 );
				}

				if ( Helper::get_option( 'product_card_quick_view_button' ) && Helper::get_option( 'mobile_product_card_featured_icons' ) != 'none' ) {
					add_action( 'shopwell_product_loop_thumbnail', array( \Shopwell\WooCommerce\QuickView::instance(), 'quick_view_icon' ), 9 );
				}

				if ( $this->enable_wishlist_button() ) {
					add_action( 'shopwell_product_loop_thumbnail', array( $this, 'product_wishlist_button' ), 10 );
				}

				if ( $this->enable_compare_button() ) {
					add_action( 'shopwell_product_loop_thumbnail', array( $this, 'product_compare_button' ), 11 );
				}

				add_action( 'shopwell_product_loop_thumbnail', array( $this, 'product_featured_icons_close' ), 15 );

				add_action( 'woocommerce_after_shop_loop_item', array( $this, 'product_actions_open' ), 5 );
				if ( Helper::get_option( 'product_card_quick_view_button' ) ) {
					add_action( 'woocommerce_after_shop_loop_item', array( \Shopwell\WooCommerce\QuickView::instance(), 'quick_view_button' ), 15 );
				}
				if ( Helper::is_catalog() ) {
					if ( apply_filters( 'shopwell_product_loop_show_price', true ) ) {
						add_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_price', 9 );
					}
					// featured icons in product actions
					if ( $this->enable_wishlist_button() || $this->enable_compare_button() ) {
						add_action( 'woocommerce_after_shop_loop_item', array( $this, 'product_featured_icons_open' ), 40 );
						if ( $this->enable_compare_button() ) {
							add_action( 'woocommerce_after_shop_loop_item', array( $this, 'product_compare_button' ), 43 );
						}

						if ( $this->enable_wishlist_button() ) {
							add_action( 'woocommerce_after_shop_loop_item', array( $this, 'product_wishlist_button' ), 45 );
						}

						add_action( 'woocommerce_after_shop_loop_item', array( $this, 'product_featured_icons_close' ), 50 );
					}
				}

				add_action( 'woocommerce_after_shop_loop_item', array( $this, 'product_actions_close' ), 89 );

				break;

			// Layout 2
			case '2':
				if ( Helper::get_option( 'product_card_stars_rating' ) ) {
					add_action( 'woocommerce_after_shop_loop_item_title', array( $this, 'product_rating' ), 25 );
				}

				// featured icons in thumbnail
				add_action( 'shopwell_product_loop_thumbnail', array( $this, 'product_featured_icons_open' ), 5 );

				if ( ! Helper::get_option( 'mobile_product_card_atc' ) && Helper::get_option( 'mobile_product_card_featured_icons' ) != 'none' ) {
					add_action( 'shopwell_product_loop_thumbnail', 'woocommerce_template_loop_add_to_cart', 8 );
				}

				if ( Helper::get_option( 'product_card_quick_view_button' ) && Helper::get_option( 'mobile_product_card_featured_icons' ) != 'none' ) {
					add_action( 'shopwell_product_loop_thumbnail', array( \Shopwell\WooCommerce\QuickView::instance(), 'quick_view_icon' ), 9 );
				}

				if ( $this->enable_wishlist_button() ) {
					add_action( 'shopwell_product_loop_thumbnail', array( $this, 'product_wishlist_button' ), 10 );
				}

				if ( $this->enable_compare_button() ) {
					add_action( 'shopwell_product_loop_thumbnail', array( $this, 'product_compare_button' ), 11 );
				}

				add_action( 'shopwell_product_loop_thumbnail', array( $this, 'product_featured_icons_close' ), 15 );

				add_action( 'woocommerce_after_shop_loop_item', array( $this, 'product_actions_open' ), 5 );
				if ( Helper::get_option( 'product_card_quick_view_button' ) ) {
					add_action( 'woocommerce_after_shop_loop_item', array( \Shopwell\WooCommerce\QuickView::instance(), 'quick_view_button' ), 15 );
				}
				if ( Helper::is_catalog() ) {
					if ( apply_filters( 'shopwell_product_loop_show_price', true ) ) {
						add_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_price', 9 );
					}
					// featured icons in product actions
					if ( $this->enable_wishlist_button() || $this->enable_compare_button() ) {
						add_action( 'woocommerce_after_shop_loop_item', array( $this, 'product_featured_icons_open' ), 40 );
						if ( $this->enable_compare_button() ) {
							add_action( 'woocommerce_after_shop_loop_item', array( $this, 'product_compare_button' ), 43 );
						}

						if ( $this->enable_wishlist_button() ) {
							add_action( 'woocommerce_after_shop_loop_item', array( $this, 'product_wishlist_button' ), 45 );
						}

						add_action( 'woocommerce_after_shop_loop_item', array( $this, 'product_featured_icons_close' ), 50 );
					}
				}
				add_action( 'woocommerce_after_shop_loop_item', array( $this, 'product_actions_close' ), 89 );

				break;

			// Layout 3
			case '3':
				// featured icons in thumbnail
				add_action( 'shopwell_product_loop_thumbnail', array( $this, 'product_featured_icons_open' ), 5 );

				if ( ! Helper::get_option( 'mobile_product_card_atc' ) && Helper::get_option( 'mobile_product_card_featured_icons' ) != 'none' ) {
					add_action( 'shopwell_product_loop_thumbnail', 'woocommerce_template_loop_add_to_cart', 7 );
				}

				if ( Helper::get_option( 'product_card_quick_view_button' ) && apply_filters( 'shopwell_product_card_show_atc', true ) ) {
					add_action( 'shopwell_product_loop_thumbnail', array( \Shopwell\WooCommerce\QuickView::instance(), 'quick_view_icon' ), 8 );
				}

				if ( $this->enable_wishlist_button() ) {
					add_action( 'shopwell_product_loop_thumbnail', array( $this, 'product_wishlist_button' ), 7 );
				}

				if ( $this->enable_compare_button() ) {
					add_action( 'shopwell_product_loop_thumbnail', array( $this, 'product_compare_button' ), 10 );
				}

				add_action( 'shopwell_product_loop_thumbnail', array( $this, 'product_featured_icons_close' ), 15 );

				add_action( 'woocommerce_after_shop_loop_item', array( $this, 'product_price_group_open' ), 0 );

				remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );
				if ( apply_filters( 'shopwell_product_loop_show_price', true ) ) {
					add_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_price', 0 );
				}

				if ( Helper::get_option( 'product_card_stars_rating' ) ) {
					add_action( 'woocommerce_after_shop_loop_item', array( $this, 'product_rating' ), 0 );
				}

				if ( class_exists( '\Shopwell\Addons\Elementor\Products' ) ) {
					remove_action( 'woocommerce_after_shop_loop_item', array( \Shopwell\Addons\Elementor\Products::instance(), 'deal_progress' ), 2 );
					add_action( 'woocommerce_after_shop_loop_item', array( '\Shopwell\Addons\Elementor\Products', 'deal_progress' ), 0 );
				}
				add_action( 'woocommerce_after_shop_loop_item', array( $this, 'product_price_group_close' ), 0 );

				add_action( 'woocommerce_after_shop_loop_item', array( $this, 'product_actions_open' ), 1 );

				if ( Helper::get_option( 'product_card_quick_view_button' ) ) {
					add_action( 'woocommerce_after_shop_loop_item', array( \Shopwell\WooCommerce\QuickView::instance(), 'quick_view_button' ), 15 );
				}

				if ( Helper::is_catalog() ) {
					if ( apply_filters( 'shopwell_product_loop_show_price', true ) ) {
						add_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_price', 9 );
					}
					// featured icons in product actions
					if ( $this->enable_wishlist_button() || $this->enable_compare_button() ) {
						add_action( 'woocommerce_after_shop_loop_item', array( $this, 'product_featured_icons_open' ), 40 );
						if ( $this->enable_compare_button() ) {
							add_action( 'woocommerce_after_shop_loop_item', array( $this, 'product_compare_button' ), 43 );
						}

						if ( $this->enable_wishlist_button() ) {
							add_action( 'woocommerce_after_shop_loop_item', array( $this, 'product_wishlist_button' ), 45 );
						}

						add_action( 'woocommerce_after_shop_loop_item', array( $this, 'product_featured_icons_close' ), 50 );
					}
				}

				add_action( 'woocommerce_after_shop_loop_item', array( $this, 'product_actions_close' ), 80 );
				break;

			// Layout 4
			case '4':
				add_action( 'woocommerce_after_shop_loop_item_title', array( $this, 'product_rating' ), 5 );
				add_action( 'woocommerce_after_shop_loop_item', array( $this, 'product_rating' ), 1 );

				add_action( 'woocommerce_after_shop_loop_item', array( $this, 'product_actions_open' ), 2 );

				if ( Helper::is_catalog() ) {
					if ( Helper::get_option( 'product_card_quick_view_button' ) ) {
						add_action( 'woocommerce_after_shop_loop_item', array( \Shopwell\WooCommerce\QuickView::instance(), 'quick_view_button' ), 15 );
					}

					if ( apply_filters( 'shopwell_product_loop_show_price', true ) ) {
						add_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_price', 9 );
					}
				}

				// featured icons in product actions
				if ( $this->enable_wishlist_button() || $this->enable_compare_button() || Helper::get_option( 'product_card_quick_view_button' ) ) {
					add_action( 'woocommerce_after_shop_loop_item', array( $this, 'product_featured_icons_open' ), 40 );
					if ( Helper::get_option( 'product_card_quick_view_button' ) ) {
						add_action( 'woocommerce_after_shop_loop_item', array( \Shopwell\WooCommerce\QuickView::instance(), 'quick_view_icon' ), 41 );
					}

					if ( $this->enable_compare_button() ) {
						add_action( 'woocommerce_after_shop_loop_item', array( $this, 'product_compare_button' ), 43 );
					}

					if ( $this->enable_wishlist_button() ) {
						add_action( 'woocommerce_after_shop_loop_item', array( $this, 'product_wishlist_button' ), 45 );
					}

					add_action( 'woocommerce_after_shop_loop_item', array( $this, 'product_featured_icons_close' ), 50 );
				}

				add_action( 'woocommerce_after_shop_loop_item', array( $this, 'product_actions_close' ), 80 );

				break;

			// Layout 5
			case '5':
				remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );
				if ( apply_filters( 'shopwell_product_loop_show_price', true ) ) {
					add_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );
				}

				add_action( 'woocommerce_after_shop_loop_item_title', array( $this, 'product_rating' ), 25 );

				remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart' );
				break;

			case '6':
				if ( Helper::get_option( 'product_card_stars_rating' ) ) {
					add_action( 'woocommerce_after_shop_loop_item_title', array( $this, 'product_rating' ), 5 );
				}

				// featured icons in thumbnail
				add_action( 'shopwell_product_loop_thumbnail', array( $this, 'product_featured_icons_open' ), 5 );

				if ( $this->enable_wishlist_button() ) {
					add_action( 'shopwell_product_loop_thumbnail', array( $this, 'product_wishlist_button' ), 10 );
				}

				if ( $this->enable_compare_button() ) {
					add_action( 'shopwell_product_loop_thumbnail', array( $this, 'product_compare_button' ), 11 );
				}

				if ( Helper::get_option( 'product_card_quick_view_button' ) ) {
					add_action( 'shopwell_product_loop_thumbnail', array( \Shopwell\WooCommerce\QuickView::instance(), 'quick_view_icon' ), 12 );
				}

				add_action( 'shopwell_product_loop_thumbnail', array( $this, 'product_featured_icons_close' ), 15 );

				remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );
				add_action( 'woocommerce_after_shop_loop_item', array( $this, 'product_actions_open' ), 5 );
				if ( apply_filters( 'shopwell_product_loop_show_price', true ) ) {
					add_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_price', 9 );
				}

				if ( Helper::is_catalog() ) {

					if ( Helper::get_option( 'product_card_quick_view_button' ) ) {
						add_action( 'woocommerce_after_shop_loop_item', array( \Shopwell\WooCommerce\QuickView::instance(), 'quick_view_button' ), 10 );
					}

					// featured icons in product actions
					if ( $this->enable_wishlist_button() || $this->enable_compare_button() ) {
						add_action( 'woocommerce_after_shop_loop_item', array( $this, 'product_featured_icons_open' ), 40 );
						if ( $this->enable_compare_button() ) {
							add_action( 'woocommerce_after_shop_loop_item', array( $this, 'product_compare_button' ), 43 );
						}

						if ( $this->enable_wishlist_button() ) {
							add_action( 'woocommerce_after_shop_loop_item', array( $this, 'product_wishlist_button' ), 45 );
						}

						add_action( 'woocommerce_after_shop_loop_item', array( $this, 'product_featured_icons_close' ), 50 );
					}
				}
				add_action( 'woocommerce_after_shop_loop_item', array( $this, 'product_actions_close' ), 89 );

				add_filter( 'woocommerce_loop_add_to_cart_link', array( $this, 'product_add_to_cart_link' ), 20, 3 );

				break;

			default:
				break;
		}
	}

	/**
	 * Open product wrapper.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function product_wrapper_open() {
		echo '<div class="product-inner">';
	}

	/**
	 * Close product wrapper.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function product_wrapper_close() {
		echo '</div>';
	}

	/**
	 * Open product summary.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function product_summary_open() {
		echo '<div class="product-summary' . apply_filters( 'shopwell_product_summary_open_classes', '' ) . '">';
	}

	/**
	 * Close product summary.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function product_summary_close() {
		echo '</div>';
	}

	/**
	 * Open product summary wrapper.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function product_summary_wrapper_open() {
		echo '<div class="product-summary-wrapper">';
	}

	/**
	 * Close product summary wrapper.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function product_summary_wrapper_close() {
		echo '</div>';
	}

	/**
	 * Open meta wrapper.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function meta_wrapper_open() {
		echo '<div class="meta-wrapper">';
	}

	/**
	 * Close meta wrapper.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function meta_wrapper_close() {
		echo '</div>';
	}

	/**
	 * Open button_product.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function product_actions_open() {
		echo '<div class="product-actions">';
	}

	/**
	 * Close button_product.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function product_actions_close() {
		echo '</div>';
	}


	/**
	 * Product thumbnail wrapper.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function product_loop_thumbnail() {
		global $product;

		switch ( Helper::get_option( 'product_card_hover' ) ) {
			case 'slider':
				$image_ids  = $product->get_gallery_image_ids();
				$image_size = apply_filters( 'single_product_archive_thumbnail_size', 'woocommerce_thumbnail' );
				echo '<div class="product-thumbnail">';
				if ( $image_ids ) {
					echo '<div class="product-thumbnails--slider swiper-container"><div class="swiper-wrapper">';
				}

					woocommerce_template_loop_product_link_open();
						woocommerce_template_loop_product_thumbnail();
					woocommerce_template_loop_product_link_close();

				foreach ( $image_ids as $image_id ) {
					$src = wp_get_attachment_image_src( $image_id, $image_size );

					if ( ! $src ) {
						continue;
					}

					woocommerce_template_loop_product_link_open();

						printf(
							'<img src="%s" data-src="%s" width="%s" height="%s" alt="%s" class="swiper-lazy">',
							esc_url( $src[0] ),
							esc_url( $src[0] ),
							esc_attr( $src[1] ),
							esc_attr( $src[2] ),
							esc_attr( $product->get_title() )
						);

					woocommerce_template_loop_product_link_close();
				}
				if ( $image_ids ) {
					echo '</div>';
					echo \Shopwell\Icon::get_svg( 'arrow-left-long', 'ui', array( 'class' => 'shopwell-product-card-swiper-prev shopwell-product-card-swiper-button' ) );
					echo \Shopwell\Icon::get_svg( 'arrow-right-long', 'ui', array( 'class' => 'shopwell-product-card-swiper-next shopwell-product-card-swiper-button' ) );
					echo '</div>';
				}
					do_action( 'shopwell_product_loop_thumbnail' );
				echo '</div>';
				break;

			case 'zoom';
				echo '<div class="product-thumbnail product-thumbnails--zoom">';
					$image = wp_get_attachment_image_src( get_post_thumbnail_id(), 'full' );

				if ( $image ) {
					$link = apply_filters( 'woocommerce_loop_product_link', get_the_permalink(), $product );
					echo '<a href="' . esc_url( $link ) . '" class="woocommerce-LoopProduct-link product-thumbnail-zoom" data-zoom_image="' . $image[0] . '">';
				} else {
					woocommerce_template_loop_product_link_open();
				}
						woocommerce_template_loop_product_thumbnail();
					woocommerce_template_loop_product_link_close();
					do_action( 'shopwell_product_loop_thumbnail' );
				echo '</div>';
				break;

			case 'fadein':
				$image_ids = $product->get_gallery_image_ids();

				if ( ! empty( $image_ids ) ) {
					echo '<div class="product-thumbnail product-thumbnails--hover">';
				} else {
					echo '<div class="product-thumbnail">';
				}

					woocommerce_template_loop_product_link_open();
						woocommerce_template_loop_product_thumbnail();

				if ( ! empty( $image_ids ) ) {
					$image_size = apply_filters( 'single_product_archive_thumbnail_size', 'woocommerce_thumbnail' );
					echo wp_get_attachment_image( $image_ids[0], $image_size, false, array( 'class' => 'attachment-woocommerce_thumbnail size-woocommerce_thumbnail hover-image' ) );
				}

						woocommerce_template_loop_product_link_close();
					do_action( 'shopwell_product_loop_thumbnail' );
				echo '</div>';
				break;
			default:
				echo '<div class="product-thumbnail">';
					woocommerce_template_loop_product_link_open();
						woocommerce_template_loop_product_thumbnail();
					woocommerce_template_loop_product_link_close();
					do_action( 'shopwell_product_loop_thumbnail' );
				echo '</div>';
				break;
		}
	}

	/**
	 * Rating count open.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function product_rating() {
		global $product;

		if ( ! $product || ! $product->get_rating_count() ) {
			return;
		}
		?>
		<div class="shopwell-rating">
			<?php woocommerce_template_loop_rating(); ?>
			<?php if( intval( $product->get_review_count() ) > 0 ) : ?>
				<div class="review-count">
					<span class="average">
						<?php echo esc_html( $product->get_average_rating() ); ?>
					</span> 
					(<?php 
					/* translators: 1: Review count */
					printf( _n( '%s<span class="text"> Review</span>', '%s<span class="text"> Reviews</span>', esc_html( $product->get_review_count() ), 'shopwell' ), '<span class="count">' . esc_html( $product->get_review_count() ) . '</span>' ); 
					?>)
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Change text of add to cart button
	 *
	 * @return string
	 */
	public function custom_cart_button_text( $text ) {
		global $product;

		if ( $product && ! $product->is_in_stock() ) {
			$text = esc_html__( 'Out Of Stock', 'shopwell' );
		}

		return $text;
	}

	/**
	 * Get product taxonomy
	 *
	 * @static
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function product_taxonomy( $taxonomy = 'product_cat' ) {
		global $product;

		$taxonomy = Helper::get_option( 'product_card_taxonomy' );

		if ( empty( $taxonomy ) ) {
			return;
		}

		$taxonomy = empty( $taxonomy ) ? '' : $taxonomy;
		$terms    = get_the_terms( $product->get_id(), $taxonomy );

		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
			printf(
				'<div class="meta meta-cat"><span>%s</span> <a href="%s">%s</a></div>',
				esc_html__( 'in', 'shopwell' ),
				esc_url( get_term_link( $terms[0] ), $taxonomy ),
				esc_html( $terms[0]->name )
			);
		}
	}

	/**
	 * Get product sku
	 *
	 * @static
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function product_sku() {
		global $product;
		if ( $product->get_sku() ) {
			printf(
				'<div class="meta meta-sku">%s <span>%s</span></div>',
				esc_html__( 'Sku:', 'shopwell' ),
				esc_html( $product->get_sku() ),
			);
		}
	}

	/**
	 * Get product card title
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function product_card_title() {
		$title_tag = Helper::get_option( 'product_card_title_tag' );
		echo '<' . esc_attr( $title_tag ) . ' class="woocommerce-loop-product__title">';
			woocommerce_template_loop_product_link_open();
				the_title();
			woocommerce_template_loop_product_link_close();
		echo '</' . esc_attr( $title_tag ) . '>';
	}

	/**
	 * Catalog script data.
	 *
	 * @since 1.0.0
	 *
	 * @param $data
	 *
	 * @return array
	 */
	public function card_script_data( $data ) {
		$data['product_card_layout'] = apply_filters( 'shopwell_product_card_layout', Helper::get_option( 'product_card_layout' ) );

		if ( 'zoom' == Helper::get_option( 'product_card_hover' ) && wp_script_is( 'zoom', 'registered' ) ) {
			$data['product_card_hover'] = 'zoom';
		}

		return $data;
	}

	/**
	 * Featured icons open
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function product_featured_icons_open() {
		echo '<div class="product-featured-icons">';
	}

		/**
		 * Featured icons close
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
	public function product_featured_icons_close() {
		echo '</div>';
	}

	/**
	 * Price group open
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function product_price_group_open() {
		echo '<div class="product-price-group">';
	}

	/**
	 * Price group close
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function product_price_group_close() {
		echo '</div>';
	}

	/**
	 * Check Wishlist Button
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function enable_wishlist_button() {
		if ( ! function_exists( 'wcboost_wishlist' ) ) {
			return false;
		}

		if ( ! Helper::get_option( 'product_card_wishlist' ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Wishlist Button
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function product_wishlist_button() {
		echo do_shortcode( '[wcboost_wishlist_button]' );
	}


	/**
	 * Check Compare Button
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function enable_compare_button() {
		if ( ! function_exists( 'wcboost_products_compare' ) ) {
			return false;
		}

		if ( ! Helper::get_option( 'product_card_compare' ) ) {
			return false;
		}

		return true;
	}

	/**
	 *  Compare button
	 */
	public function product_compare_button() {
		\Shopwell\WooCommerce\Helper::product_compare_button();
	}

	/**
	 * Product Short Description
	 *
	 * @return  void
	 */
	public function short_description() {
		global $product;
		$content = $product->get_short_description();
		if ( empty( $content ) ) {
			return;
		}
		if ( has_shortcode( $content, 'shopwell_more' ) ) {
			echo '<div class="short-description">';
				echo wp_kses_post( do_shortcode( $content ) );
			echo '</div>';
		} else {
			echo '<div class="short-description short-description--clamp">';
				echo wp_kses_post( $content );
			echo '</div>';
		}
	}

	public function product_add_to_cart_link( $html, $product, $args ) {
		printf(
			'<a href="%s" data-quantity="%s" class="%s product-add-to-cart_link" %s>%s <span class="button_text">%s</span></a>',
			esc_url( $product->add_to_cart_url() ),
			esc_attr( isset( $args['quantity'] ) ? $args['quantity'] : 1 ),
			esc_attr( isset( $args['class'] ) ? $args['class'] : 'button' ),
			isset( $args['attributes'] ) ? wc_implode_html_attributes( $args['attributes'] ) : '',
			\Shopwell\Icon::get_svg( 'cart-trolley' ),
			esc_html( $product->add_to_cart_text() )
		);
	}
}
