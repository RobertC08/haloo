<?php
/**
 * Posts functions and definitions.
 *
 * @package Shopwell
 */

namespace Shopwell\Header;

use Shopwell\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Posts initial
 */
class Hamburger {
	/**
	 * Instantiate the object.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function __construct() {
	}

	/**
	 * Custom template tags of header
	 *
	 * @package Shopwell
	 *
	 * @since 1.0.0
	 *
	 * @param $items
	 */
	public static function items( $args ) {
		if ( empty( $args ) ) {
			return;
		}
		if ( empty( $args['list_items'] ) ) {
			return;
		}
		$items = $args['list_items'];
		foreach ( $items as  $item ) {

			if ( empty( $item['item'] ) ) {
				continue;
			}
			switch ( $item['item'] ) {
				case 'divider':
					echo '<hr class="mobile-menu__divider divider">';
					break;

				case 'account':
					\Shopwell\Helper::account_links();
					break;

				case 'wishlist':
					if ( $wishlist_html = Helper::wishlist_link() ) {
						printf( '<div class="hamburger-panel__item">%s</div>', $wishlist_html ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					}
					break;

				case 'compare':
					if ( $compare_html = Helper::compare_link() ) {
						printf( '<div class="hamburger-panel__item">%s</div>', $compare_html ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					}
					break;

				case 'track-order':
					if ( $track_html = Helper::track_order_link() ) {
						printf( '<div class="hamburger-panel__item">%s</div>', $track_html ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					}
					break;

				case 'help-center':
					if ( $help_html = Helper::help_center_link() ) {
						printf( '<div class="hamburger-panel__item">%s</div>', $help_html ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					}
					break;

				case 'custom-menu':
					$mega_menu  = false;
					$menu_class = 'custom-menu-navigation main-navigation';
					$menu_id    = ! empty( $args['custom_menu_id'] ) ? $args['custom_menu_id'] : '';
					if ( ! empty( $menu_id ) ) {
						Helper::navigation_menu_by_id( $mega_menu, $menu_id, $menu_class );
					}
					break;

				case 'primary-menu':
					$mega_menu  = true;
					$menu_class = 'main-navigation primary-navigation';
					$menu_id    = ! empty( $args['primary_menu_id'] ) ? $args['primary_menu_id'] : '';
					if ( has_nav_menu( 'primary-menu' ) ) {
						if ( ! empty( $menu_id ) ) {
							Helper::navigation_menu_by_id( $mega_menu, $menu_id, $menu_class );
						} else {
							Helper::navigation_menu_by_location( $mega_menu, $item['item'], $menu_class );
						}
					} else {
						wp_page_menu(
							array(
								'menu_class' => 'main-navigation primary-navigation',
								'show_home'  => true,
								'container'  => 'nav',
								'before'     => '<ul class="menu">',
								'after'      => '</ul>',
							)
						);
					}
					break;

				case 'category-menu':
					$mega_menu  = true;
					$menu_class = 'header-category__menu';
					$menu_id    = ! empty( $args['category_menu_id'] ) ? $args['category_menu_id'] : '';
					echo '<div class="header-category-menu header-category--hamburger"><div class="header-category__title " tabindex="0">';
					printf( '<span class="header-category__name">%s</span>', esc_html__( 'Shop by Category', 'shopwell' ) );
					if ( function_exists( 'wc_get_page_permalink' ) ) {
						printf(
							'<a class="shopwell-button shopwell-button--subtle shopwell-button--color-black shopwell-button--medium" href="%s">
								<span class="shopwell-button__text">%s</span>
							</a>',
							wc_get_page_permalink( 'shop' ),
							esc_html__( 'See All', 'shopwell' )
						);
					} else {
						echo '<span class="shopwell-button__text">' . esc_html__( 'Shop Not Available', 'shopwell' ) . '</span>';
					}
					echo '</div>';
					if ( ! empty( $menu_id ) ) {
						Helper::navigation_menu_by_id( $mega_menu, $menu_id, $menu_class );
					} else {
						Helper::navigation_menu_by_location( $mega_menu, $item['item'], $menu_class );
					}
					echo '</div>';
					break;

				case 'search':
					$args['search_class']                = 'shopwell-skin--base';
					$args['search_items_input_class']    = 'shopwell-type--input-text';
					$args['search_items_button_display'] = 'none';
					$args['search_items']                = array( 'icon', 'search-field' );
					get_template_part( 'template-parts/header/search-form', '', $args );
					break;

				case 'socials':
					get_template_part( 'template-parts/header/socials', '', $args );
					break;

				case 'preferences':
					$languages = Helper::language_status();

					if ( ! empty( $languages ) ) {
						foreach ( (array) $languages as $key => $language ) {
							if ( $language['active'] ) {
								$args['language'] = $language['native_name'];
							} else {
								$key              = key( $languages );
								$args['language'] = $languages[ $key ]['native_name'];
							}
						}
					}

					$currencies_code = \Shopwell\Helper::get_option( 'header_currency_code' );
					if ( ! $currencies_code ) {

						$currencies = \Shopwell\WooCommerce\Currency::currency_status();

						if ( ! empty( $currencies ) ) {
							$args['currency'] = $currencies['current_currency'];
						}
					}

					if ( ! empty( $args['language'] ) || ! empty( $args['currency'] ) ) :

						get_template_part( 'template-parts/panels/preferences-menu', '', $args );

					endif;

					break;

				default:
					do_action( 'shopwell_mobile_menu_items', $item );
					break;
			}
		}
	}
}
