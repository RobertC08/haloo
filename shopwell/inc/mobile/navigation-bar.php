<?php
/**
 * Navigation bar functions and definitions.
 *
 * @package Shopwell
 */

namespace Shopwell\Mobile;

use Shopwell\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Woocommerce initial
 */
class Navigation_bar {
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
		if ( Helper::get_option( 'mobile_navigation_bar' ) == 'none' ) {
			return;
		}

		add_filter( 'body_class', array( $this, 'body_classes' ) );

		add_filter( 'wp_footer', array( $this, 'navigation_bar' ), 0 );
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
		if ( get_post_meta( Helper::get_post_ID(), 'shopwell_hide_navigation_bar', true ) ) {
			return $classes;
		}

		if ( get_option( 'shopwell_sticky_add_to_cart_toggle', 'yes' ) == 'yes' && is_singular( 'product' ) ) {
			return $classes;
		}

		$classes[] = 'shopwell-navigation-bar-show';

		return $classes;
	}

	/**
	 * Displays header content
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function navigation_bar() {
		if ( \Shopwell\WooCommerce\Helper::is_cartflows_template() ) {
			return;
		}

		if ( get_post_meta( Helper::get_post_ID(), 'shopwell_hide_navigation_bar', true ) ) {
			return;
		}

		$items = (array) Helper::get_option( 'mobile_navigation_bar_items' );

		if ( ! $items ) {
			return;
		}

		$class = Helper::get_option( 'mobile_navigation_bar' );

		?>
		<div id="shopwell-mobile-navigation-bar" class="shopwell-mobile-navigation-bar <?php echo esc_attr( $class ); ?>">
			<?php $this->navigation_bar_template_item( $items ); ?>
		</div>
		<?php
	}

	/**
	 * Display header items
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function navigation_bar_template_item( $items ) {
		$items = array_keys( $items, true, true ); // Get only enabled items
		foreach ( $items as $item ) {

			if ( empty( $item ) ) {
				continue;
			}

			$template_file = $item;

			switch ( $item ) {
				case 'shop':
					if ( ! class_exists( 'WooCommerce' ) ) {
						$template_file = '';
						break;
					}
					\Shopwell\Theme::set_prop( 'panels', 'cart' );
					break;

				case 'cart':
					if ( ! class_exists( 'WooCommerce' ) ) {
						$template_file = '';
						break;
					}
					break;

				case 'compare':
					if ( ! function_exists( 'wcboost_products_compare' ) ) {
						$template_file = '';
						break;
					}
					break;
				case 'wishlist':
					if ( ! function_exists( 'wcboost_wishlist' ) ) {
						$template_file = '';
						break;
					}
					break;
				case 'account':
					if ( ! class_exists( 'WooCommerce' ) ) {
						$template_file = '';
						break;
					}
					\Shopwell\Theme::set_prop( 'panels', 'account' );
					break;
				case 'categories':
					\Shopwell\Theme::set_prop( 'panels', 'category-menu' );
					break;
			}

			if ( $template_file ) {
				get_template_part( 'template-parts/navigation-bar/' . $template_file );
			}
		}
	}
}
