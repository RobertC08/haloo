<?php
/**
 * Shopwell helper functions and definitions.
 *
 * @package Shopwell
 */

namespace Shopwell;

use Shopwell\Theme;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shopwell Helper initial
 */
class Helper {
	/**
	 * Post ID
	 *
	 * @var $post_id
	 */
	protected static $post_id = null;

	/**
	 * is_build_elementor
	 *
	 * @var $is_build_elementor
	 */
	protected static $is_build_elementor = null;

	/**
	 * is_product_build_elementor
	 *
	 * @var $is_product_build_elementor
	 */
	protected static $is_product_build_elementor = null;

	/**
	 * Get theme option
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public static function get_option( $id, $prefix = 'shopwell_', $type = 'theme_mod' ) {
		if ( 'theme_mod' === $type ) {
			return \Shopwell\Options::instance()->get( $prefix . $id );
		} else {
			return get_option( $prefix . $id, \Shopwell\Options::instance()->get( $prefix . $id ) );
		}
	}

	/**
	 * Get theme option default
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public static function get_option_default( $id ) {
		return \Shopwell\Options::instance()->get_default( $id );
	}

	/**
	 * Check is catalog
	 *
	 * @since 1.0.0
	 *
	 * @return boolean
	 */
	public static function is_catalog() {
		if ( function_exists( 'is_shop' ) && ( is_shop() || is_product_category() || is_product_tag() || is_tax( 'product_brand' ) || is_tax( 'product_collection' ) || is_tax( 'product_condition' ) || ( function_exists( 'is_product_taxonomy' ) && is_product_taxonomy() ) ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check is blog
	 *
	 * @since 1.0.0
	 *
	 * @return boolean
	 */
	public static function is_blog() {
		if ( ( is_archive() || is_author() || is_category() || is_home() || is_tag() ) && 'post' == get_post_type() ) {
			return true;
		}

		return false;
	}

	/**
	 * Get Post ID
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public static function get_post_ID() {
		if ( isset( self::$post_id ) ) {
			return self::$post_id;
		}

		if ( self::is_catalog() ) {
			self::$post_id = intval( get_option( 'woocommerce_shop_page_id' ) );
		} elseif ( self::is_blog() ) {
			self::$post_id = intval( get_option( 'page_for_posts' ) );
		} else {
			self::$post_id = get_the_ID();
		}

		return self::$post_id;
	}


	/**
	 * Content limit
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public static function get_content_limit( $num_words, $more = '&hellip;', $content = '' ) {
		$content = empty( $content ) ? get_the_excerpt() : $content;

		// Strip tags and shortcodes so the content truncation count is done correctly
		$content = strip_tags(
			strip_shortcodes( $content ),
			apply_filters(
				'shopwell_content_limit_allowed_tags',
				'<script>,<style>'
			)
		);

		// Remove inline styles / scripts
		$content = trim( preg_replace( '#<(s(cript|tyle)).*?</\1>#si', '', $content ) );

		// Truncate $content to $max_char
		$content = wp_trim_words( $content, $num_words );

		if ( $more ) {
			return sprintf(
				'<p>%s <a href="%s" class="more-link" title="%s">%s</a></p>',
				$content,
				get_permalink(),
				/* translators: %s: Name of current post */
				sprintf( esc_html__( 'Continue reading &quot;%s&quot;', 'shopwell' ), the_title_attribute( 'echo=0' ) ),
				esc_html( $more )
			);
		} else {
			return sprintf( '<p>%s</p>', $content );
		}
	}

	/**
	 * Get counter wishlist
	 *
	 * @since 1.0.0
	 *
	 * @param string $account
	 *
	 * @return string
	 */
	public static function wishlist_counter() {
		if ( ! function_exists( 'WC' ) ) {
			return;
		}

		if ( ! class_exists( 'WCBoost\Wishlist\Helper' ) ) {
			return;
		}

		$wishlist = \WCBoost\Wishlist\Helper::get_wishlist();
		$class    = '';

		if ( intval( self::get_option( 'header_wishlist_counter' ) ) ) {
			$wishlist_counter = intval( $wishlist->count_items() );

			if ( $wishlist_counter == 0 ) {
				$class = ' hidden';
			}

			return sprintf( '<span class="header-counter header-wishlist__counter%s">%s</span>', $class, $wishlist_counter );
		}
	}

	/**
	 * Get counter compare
	 *
	 * @since 1.0.0
	 *
	 * @param string $account
	 *
	 * @return string
	 */
	public static function compare_counter() {
		if ( ! function_exists( 'WC' ) ) {
			return;
		}

		if ( ! class_exists( '\WCBoost\ProductsCompare\Plugin' ) ) {
			return;
		}

		$class = '';

		if ( intval( self::get_option( 'header_compare_counter' ) ) ) {
			$compare_counter = \WCBoost\ProductsCompare\Plugin::instance()->list->count_items();

			if ( $compare_counter == 0 ) {
				$class = 'hidden';
			}

			return sprintf( '<span class="header-counter header-compare__counter %s">%s</span>', $class, $compare_counter );
		}
	}

	/**
	 * Get the account link
	 *
	 * @since 1.0.0
	 *
	 * @param string $account
	 *
	 * @return string
	 */
	public static function account_links() {
		$accounts = (array) self::get_option( 'header_account_links' );

		if ( empty( $accounts ) ) {
			return;
		}

		if ( ! function_exists( 'wc_get_account_endpoint_url' ) ) {
			return;
		}

		$output = array();

		foreach ( $accounts as $account => $value ) {
			$url     = '#';
			$text    = '';
			$icon    = $value;
			$class   = '';
			$counter = '';

			if ( ! $value ) {
				continue;
			}

			if ( $account == 'my-account' ) {
				if ( is_user_logged_in() ) {
					$url      = esc_url( wc_get_account_endpoint_url( 'dashboard' ) );
					$text     = esc_html__( 'My Account', 'shopwell' );
					$icon     = \Shopwell\Icon::get_svg( 'account', 'ui' );
					$output[] = sprintf(
						'<li class="account-panel__link"><a class="shopwell-button  shopwell-button--text" href="%s">
							<span class="shopwell-button__icon">%s</span>
							<span class="shopwell-button__text">%s</span>
						</a></li>',
						esc_url( $url ),
						\Shopwell\Icon::sanitize_svg( $icon ),
						$text
					);
				}
			} elseif ( $account == 'sign-in' ) {
				if ( ! is_user_logged_in() ) {
					$url      = esc_url( wc_get_account_endpoint_url( 'dashboard' ) );
					$text     = esc_html__( 'Sign In', 'shopwell' );
					$icon     = \Shopwell\Icon::get_svg( 'account', 'ui' );
					$output[] = sprintf(
						'<li class="account-panel__link"><a class="shopwell-button  shopwell-button--text" href="%s">
							<span class="shopwell-button__icon">%s</span>
							<span class="shopwell-button__text">%s</span>
						</a></li>',
						esc_url( $url ),
						\Shopwell\Icon::sanitize_svg( $icon ),
						$text
					);
				}
			} elseif ( $account == 'create-account' ) {
				if ( ! is_user_logged_in() && 'yes' === get_option( 'woocommerce_enable_myaccount_registration' ) ) {
					$url      = esc_url( wc_get_account_endpoint_url( 'dashboard' ) . '#register' );
					$text     = esc_html__( 'Create Account', 'shopwell' );
					$icon     = \Shopwell\Icon::get_svg( 'account', 'ui' );
					$output[] = sprintf(
						'<li class="account-panel__link"><a class="shopwell-button  shopwell-button--text" href="%s">
							<span class="shopwell-button__icon">%s</span>
							<span class="shopwell-button__text">%s</span>
						</a></li>',
						esc_url( $url ),
						\Shopwell\Icon::sanitize_svg( $icon ),
						$text
					);
				}
			} elseif ( $account == 'wishlist' ) {
				$output[] = '<li class="account-panel__link">' . self::wishlist_link() . '</li>';
			} elseif ( $account == 'compare' ) {
				$output[] = '<li class="account-panel__link">' . self::compare_link() . '</li>';
			} elseif ( $account == 'track-order' ) {
				$output[] = '<li class="account-panel__link">' . self::track_order_link() . '</li>';
			} elseif ( $account == 'help-center' ) {
				$output[] = '<li class="account-panel__link">' . self::help_center_link() . '</li>';
			} elseif ( $value == 'custom-menu' ) {
					$menu_id = self::get_option( 'header_account_custom_menu' );
				if ( ! empty( $menu_id ) ) {
					ob_start();
					self::navigation_menu_by_id( false, $menu_id, '' );
					$menu_html = ob_get_clean();
					$output[]  = '<li class="account-panel__link">' . $menu_html . '</li>';
				}
			} elseif ( $account == 'sign-out' ) {
				if ( is_user_logged_in() ) {
					$url      = function_exists( 'wc_get_account_endpoint_url' ) ? wc_get_account_endpoint_url( 'customer-logout' ) : wp_logout_url();
					$text     = esc_html__( 'Sign Out', 'shopwell' );
					$icon     = \Shopwell\Icon::get_svg( $icon, 'ui' );
					$output[] = sprintf(
						'<li class="account-panel__link"><a class="shopwell-button  shopwell-button--text" href="%s">
							<span class="shopwell-button__icon">%s</span>
							<span class="shopwell-button__text">%s</span>
						</a></li>',
						esc_url( $url ),
						\Shopwell\Icon::sanitize_svg( $icon ),
						$text
					);
				}
			}
		}

		if ( $output ) {
			printf( '<ul class="account-panel__links">%s</ul>', implode( '', $output ) );
		}
	}

	/**
	 * Get the wishlist link
	 *
	 * @since 1.0.0
	 *
	 * @param string $icon_class
	 *
	 * @return string
	 */
	public static function wishlist_link( $icon_class = '' ) {
		$page_id = wc_get_page_id( 'wishlist' );
		if ( empty( $page_id ) || intval( $page_id ) < 1 ) {
			return;
		}
		$text    = get_the_title( $page_id );
		$url     = get_page_link( $page_id );
		$icon    = \Shopwell\Icon::get_svg( 'wishlist', 'ui', $icon_class );
		$counter = self::wishlist_counter();

		return sprintf(
			'<a class="shopwell-button  shopwell-button--text" href="%s">
				<span class="shopwell-button__icon">%s</span>
				<span class="shopwell-button__text">%s</span>
				%s
			</a>',
			esc_url( $url ),
			\Shopwell\Icon::sanitize_svg( $icon ),
			esc_html( $text ),
			$counter //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		);
	}

	/**
	 * Get the compare link
	 *
	 * @since 1.0.0
	 *
	 * @param string $icon_class
	 *
	 * @return string
	 */
	public static function compare_link( $icon_class = '' ) {
		$page_id = wc_get_page_id( 'compare' );
		if ( empty( $page_id ) || intval( $page_id ) < 1 ) {
			return;
		}
		$text    = get_the_title( $page_id );
		$url     = get_page_link( $page_id );
		$icon    = \Shopwell\Icon::get_svg( 'compare', 'ui', $icon_class );
		$counter = self::compare_counter();
		return sprintf(
			'<a class="shopwell-button  shopwell-button--text" href="%s">
				<span class="shopwell-button__icon">%s</span>
				<span class="shopwell-button__text">%s</span>
				%s
			</a>',
			esc_url( $url ),
			\Shopwell\Icon::sanitize_svg( $icon ),
			esc_html( $text ),
			$counter //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		);
	}

	/**
	 * Get the track order link
	 *
	 * @since 1.0.0
	 *
	 * @param string $icon_class
	 *
	 * @return string
	 */
	public static function track_order_link( $icon_class = '' ) {
		$order_tracking = get_option( 'order_tracking_page_id' );
		if ( empty( $order_tracking ) ) {
			return;
		}
		$text = get_the_title( $order_tracking );
		$url  = get_page_link( $order_tracking );
		$icon = \Shopwell\Icon::get_svg( 'track-order', 'ui', $icon_class );
		return sprintf(
			'<a class="shopwell-button  shopwell-button--text" href="%s" %s>
				<span class="shopwell-button__icon">%s</span>
				<span class="shopwell-button__text">%s</span>
			</a>',
			esc_url( $url ),
			esc_attr( $icon_class ),
			\Shopwell\Icon::sanitize_svg( $icon ),
			esc_html( $text ),
		);
	}

	/**
	 * Get the help center link
	 *
	 * @since 1.0.0
	 *
	 * @param string $icon_class
	 *
	 * @return string
	 */
	public static function help_center_link( $icon_class = '' ) {
		$page_id = get_option( 'help_center_page_id' );
		$text    = $url = '';
		if ( ! empty( $page_id ) ) {
			$text = get_the_title( $page_id );
			$url  = get_permalink( $page_id );
		}
		$text = apply_filters( 'shopwell_help_center_title', $text );
		$url  = apply_filters( 'shopwell_help_center_link', $url );
		if ( empty( $text ) ) {
			return;
		}
		$icon = \Shopwell\Icon::get_svg( 'help-center', 'ui', $icon_class );
		return sprintf(
			'<a class="shopwell-button  shopwell-button--text" href="%s" %s>
				<span class="shopwell-button__icon">%s</span>
				<span class="shopwell-button__text">%s</span>
			</a>',
			esc_url( $url ),
			esc_attr( $icon_class ),
			\Shopwell\Icon::sanitize_svg( $icon ),
			esc_html( $text ),
		);
	}

	/**
	 * Get the menu
	 *
	 * @since 1.0.0
	 *
	 * @param string $account
	 *
	 * @return string
	 */
	public static function navigation_menu_by_id( $mega_menu, $menu_id, $menu_class ) {
		if ( $mega_menu && class_exists( '\Shopwell\Addons\Modules\Mega_Menu\Walker' ) ) {
			wp_nav_menu(
				apply_filters(
					'shopwell_navigation_menu_by_id_content',
					array(
						'theme_location'  => '__no_such_location',
						'menu'            => $menu_id,
						'container'       => 'nav',
						'container_class' => $menu_class,
						'menu_class'      => 'menu',
						'walker'          => new \Shopwell\Addons\Modules\Mega_Menu\Walker(),
					)
				)
			);
		} else {
			wp_nav_menu(
				apply_filters(
					'shopwell_navigation_header_category_content',
					array(
						'theme_location'  => '__no_such_location',
						'menu'            => $menu_id,
						'container'       => 'nav',
						'container_class' => $menu_class,
						'menu_class'      => 'menu',
					)
				)
			);
		}
	}

	/**
	 * Get the menu
	 *
	 * @since 1.0.0
	 *
	 * @param string $account
	 *
	 * @return string
	 */
	public static function navigation_menu_by_location( $mega_menu, $location, $menu_class ) {
		if ( ! has_nav_menu( $location ) ) {
			return;
		}
		if ( $mega_menu && class_exists( '\Shopwell\Addons\Modules\Mega_Menu\Walker' ) ) {
			wp_nav_menu(
				apply_filters(
					'shopwell_navigation_menu_by_location_content',
					array(
						'theme_location'  => $location,
						'container'       => 'nav',
						'container_class' => $menu_class,
						'menu_class'      => 'menu',
						'walker'          => new \Shopwell\Addons\Modules\Mega_Menu\Walker(),
					)
				)
			);
		} else {
			wp_nav_menu(
				apply_filters(
					'shopwell_navigation_header_category_content',
					array(
						'theme_location'  => $location,
						'container'       => 'nav',
						'container_class' => $menu_class,
						'menu_class'      => 'menu',
					)
				)
			);
		}
	}

	/**
	 * Get an array of posts.
	 *
	 * @static
	 * @access public
	 *
	 * @param array $args Define arguments for the get_posts function.
	 *
	 * @return array
	 */
	public static function customizer_get_posts( $args ) {

		if ( ! is_admin() ) {
			return;
		}

		if ( is_string( $args ) ) {
			$args = add_query_arg(
				array(
					'suppress_filters' => false,
				)
			);
		} elseif ( is_array( $args ) && ! isset( $args['suppress_filters'] ) ) {
			$args['suppress_filters'] = false;
		}

		$args['posts_per_page'] = - 1;

		$posts = get_posts( $args );

		// Properly format the array.
		$items  = array();
		$source = isset( $args['source'] ) ? $args['source'] : '';
		if ( $args['post_type'] == 'shopwell_footer' && $source == 'page' ) {
			$items[0]      = esc_html__( 'Default Footer Global', 'shopwell' );
			$items['page'] = esc_html__( 'Default Footer Page', 'shopwell' );
		} else {
			$items[0] = esc_html__( 'Select an item', 'shopwell' );
		}
		foreach ( $posts as $post ) {
			$items[ $post->ID ] = $post->post_title;
		}
		wp_reset_postdata();

		return $items;
	}

	/**
	 * Return boolean currency switcher
	 *
	 * @return void
	 */
	public static function currency_status( $args = array() ) {
		return \Shopwell\WooCommerce\Currency::currency_status();
	}

	/**
	 * Print HTML of currency switcher
	 * It requires plugin WooCommerce Currency Switcher installed
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function currency_switcher( $display = 'list' ) {
		return \Shopwell\WooCommerce\Currency::currency_switcher( $display );
	}

	/**
	 * Return boolean language switcher
	 *
	 * @return array
	 */
	public static function language_status() {
		return apply_filters( 'wpml_active_languages', array() );
	}

	/**
	 * Print HTML of language switcher
	 * It requires plugin WPML installed
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function language_switcher( $display = 'list' ) {
		$languages = self::language_status();
		$lang_list = array();

		if ( empty( $languages ) ) {
			return;
		}

		if ( $display == 'list' ) {
			foreach ( (array) $languages as $code => $language ) {
				if ( $language['active'] ) {
					$lang_list[] = sprintf(
						'<li class="shopwell-language__menu-item %s active"><a href="%s">%s %s</a></li>',
						esc_attr( $code ),
						esc_url( $language['url'] ),
						esc_html( $language['native_name'] ),
						\Shopwell\Icon::get_svg( 'check' )
					);
				} else {
					array_unshift(
						$lang_list,
						sprintf(
							'<li class="shopwell-language__menu-item %s"><a href="%s">%s %s</a></li>',
							esc_attr( $code ),
							esc_url( $language['url'] ),
							esc_html( $language['native_name'] ),
							\Shopwell\Icon::get_svg( 'check' )
						)
					);
				}
			}

			echo '<ul class="preferences-menu__item-child">';
				echo implode( "\n\t", $lang_list );
			echo '</ul>';
		} else {
			?>
			<label><?php esc_html_e( 'Language', 'shopwell' ); ?></label>
			<select name="language" id="shopwell_language" class="language_select preferences_select">
				<?php
				foreach ( (array) $languages as $key => $language ) {
					$current_language = ! empty( $language['active'] ) ? esc_attr( $key ) : '';
					$language_url     = self::get_language_url( $language['url'] );
					echo '<option value="' . esc_url( $language_url ) . '"' . selected( $current_language, esc_attr( $key ), false ) . '>' . esc_html( $language['native_name'] ) . '</option>';
				}
				?>
			</select>
			<?php
		}
	}

	public static function get_language_url( $href ) {
		if ( class_exists( 'TRP_Translate_Press' ) ) {
			$parts = explode( '?', $href );
			if ( $parts ) {
				$href = $parts[0];
			}
		}
		return $href;
	}

	/**
	 * Get languages
	 *
	 * @package Shopwell
	 *
	 * @since 1.0.0
	 *
	 * @param $items
	 */
	public static function get_languages( $args, $flag = false, $translated_name = false, $language_code = false ) {
		$languages = self::language_status();

		if ( empty( $languages ) ) {
			return false;
		}
		$args['icon'] = 'language';
		foreach ( (array) $languages as $key => $language ) {
			if ( $language['active'] ) {
				$args['name'] = $language['native_name'];
				if ( $translated_name && ! empty( $language['translated_name'] ) ) {
					$args['name'] = $language['translated_name'];
				}

				if ( $language_code && $translated_name == false && ! empty( $language['language_code'] ) ) {
					$args['name'] = $language['language_code'];
				}

				if ( $flag && ! empty( $language['country_flag_url'] ) ) {
					$args['flag'] = $language['country_flag_url'];
				}
				break;
			} else {
				$key          = key( $languages );
				$args['name'] = $languages[ $key ]['native_name'];

				if ( $translated_name && ! empty( $languages[ $key ]['translated_name'] ) ) {
					$args['name'] = $languages[ $key ]['translated_name'];
				}

				if ( $language_code && $translated_name == false && ! empty( $language['language_code'] ) ) {
					$args['name'] = $languages[ $key ]['language_code'];
				}

				if ( $flag && ! empty( $languages[ $key ]['country_flag_url'] ) ) {
					$args['flag'] = $languages[ $key ]['country_flag_url'];
				}
			}
		}

		return $args;
	}

	/**
	 * Get currencies
	 *
	 * @package Shopwell
	 *
	 * @since 1.0.0
	 *
	 * @param $items
	 */
	public static function get_currencies( $args, $flag = false ) {
		return \Shopwell\WooCommerce\Currency::get_currencies( $args, $flag );
	}

	/**
	 * Check is built with elementor
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function is_built_with_elementor() {
		if ( isset( self::$is_build_elementor ) ) {
			return self::$is_build_elementor;
		}
		if ( ! class_exists( '\Elementor\Plugin' ) ) {
			self::$is_build_elementor = false;
			return self::$is_build_elementor;
		}

		$document = \Elementor\Plugin::$instance->documents->get( self::get_post_ID() );

		if ( is_page() && $document && $document->is_built_with_elementor() ) {
			self::$is_build_elementor = true;
		}

		self::$is_build_elementor = apply_filters( 'shopwell_is_page_built_with_elementor', self::$is_build_elementor );

		return self::$is_build_elementor;
	}

	/**
	 * Check help center page
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function is_help_center_page( $page_id = '' ) {
		$page_id    = empty( $page_id ) ? get_the_ID() : $page_id;
		$hc_page_id = get_option( 'help_center_page_id' );

		if ( ! $hc_page_id ) {
			return false;
		}

		if ( $hc_page_id == $page_id ) {
			return true;
		}

		return false;
	}

	/**
	 * Button Share
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function share_socials() {
		if ( ! class_exists( '\Shopwell\Addons\Helper' ) && ! method_exists( '\Shopwell\Addons\Helper', 'share_link' ) ) {
			return;
		}

		$args    = array();
		$socials = (array) self::get_option( 'post_sharing_socials' );
		$socials = array_keys( $socials, true, true );
		if ( ( ! empty( $socials ) ) ) {
			$output = array();

			foreach ( $socials as $social => $value ) {
				if ( $value == 'whatsapp' ) {
					$args['whatsapp_number'] = self::get_option( 'post_sharing_whatsapp_number' );
				}

				if ( $value == 'facebook' ) {
					$args[ $value ]['icon'] = 'facebook-f';
				}

				$args[ $value ]['class'] = 'shopwell-socials--bg shopwell-socials--' . $value;

				$output[] = \Shopwell\Addons\Helper::share_link( $value, $args );
			}
			return sprintf( '<div class="post__socials-share">%s</div>', implode( '', $output ) );
		}
	}

	/**
	 * Get a specific property of an array without needing to check if that property exists.
	 *
	 * Provide a default value if you want to return a specific value if the property is not set.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $array   Array from which the property's value should be retrieved.
	 * @param string $prop    Name of the property to be retrieved.
	 * @param string $default Optional. Value that should be returned if the property is not set or empty. Defaults to null.
	 *
	 * @return null|string|mixed The value
	 */
	public static function get_prop( $array, $prop, $default = null ) {

		if ( ! is_array( $array ) && ! ( is_object( $array ) && $array instanceof \ArrayAccess ) ) {
			return $default;
		}

		if ( isset( $array[ $prop ] ) ) {
			$value = $array[ $prop ];
		} else {
			$value = '';
		}

		return empty( $value ) && null !== $default ? $default : $value;
	}

	/**
	 * Returns blog page URL.
	 *
	 * @since 1.0.0
	 * @return String, current page URL.
	 */
	public static function get_blog_url() {

		$blog_url = '';

		// If front page is set to display a static page, get the URL of the posts page.
		if ( 'page' === get_option( 'show_on_front' ) ) {

			$page_for_posts = get_option( 'page_for_posts' );

			if ( $page_for_posts ) {
				$blog_url = get_permalink( $page_for_posts );
			}
		} else {

			// The front page IS the posts page. Get its URL.
			$blog_url = home_url( '/' );
		}

		return apply_filters( 'shopwell_site_url', $blog_url );
	}

	/**
	 * Fonts
	 *
	 * @since 1.0.0
	 * @return \Shopwell\Fonts
	 */
	public static function fonts() {
		return \Shopwell\Fonts::instance();
	}

	/**
	 * Returns array of default values for Design Options field.
	 *
	 * @since  1.0.0
	 * @param  array $options Default options.
	 * @return array $defaults array of default values.
	 */
	public static function design_options_defaults( $options = array() ) {

		$defaults = array();

		// Background options.
		if ( isset( $options['background'] ) ) {

			// Default background type.
			if ( isset( $options['background']['background-type'] ) && in_array( $options['background']['background-type'], array( 'color', 'image', 'gradient' ), true ) ) {
				$defaults['background-type'] = $options['background']['background-type'];
			} else {
				$defaults['background-type'] = 'color';
			}

			// Background color defaults.
			if ( isset( $options['background']['color'] ) ) {
				$defaults += wp_parse_args(
					(array) $options['background']['color'],
					array(
						'background-color' => '',
					)
				);
			}

			// Background image defaults.
			if ( isset( $options['background']['image'] ) ) {
				$defaults += wp_parse_args(
					(array) $options['background']['image'],
					array(
						'background-image'         => '',
						'background-repeat'        => 'no-repeat',
						'background-position-x'    => '50',
						'background-position-y'    => '50',
						'background-size'          => 'cover',
						'background-attachment'    => 'inherit',
						'background-image-id'      => '',
						'background-color-overlay' => 'rgba(0,0,0,0.5)',
					)
				);
			}

			// Background gradient defaults.
			if ( isset( $options['background']['gradient'] ) ) {
				$defaults += wp_parse_args(
					(array) $options['background']['gradient'],
					array(
						'gradient-color-1'          => '#16222A',
						'gradient-color-1-location' => '0',
						'gradient-color-2'          => '#3A6073',
						'gradient-color-2-location' => '100',
						'gradient-type'             => 'linear',
						'gradient-linear-angle'     => '45',
						'gradient-position'         => 'center center',
					)
				);
			}
		}

		// Border default.
		if ( isset( $options['border'] ) ) {
			$defaults += wp_parse_args(
				(array) $options['border'],
				array(
					'border-left-width'   => '',
					'border-top-width'    => '',
					'border-right-width'  => '',
					'border-bottom-width' => '',
					'border-color'        => '',
					'style'               => 'solid',
					'separator-color'     => '',
				)
			);
		}

		// Color default.
		if ( isset( $options['color'] ) ) {
			$defaults += wp_parse_args(
				(array) $options['color'],
				array(
					'text-color'       => '',
					'link-color'       => '',
					'link-hover-color' => '',
				)
			);
		}

		return apply_filters( 'shopwell_design_options_defaults', $defaults, $options );
	}

	/**
	 * Returns array of default values for Typography field.
	 *
	 * @since  1.0.0
	 * @param  array $options Default options.
	 * @return array $defaults array of default values.
	 */
	public static function typography_defaults( $options = array() ) {

		$defaults = apply_filters(
			'shopwell_typography_defaults',
			array(
				'font-family'         => 'inherit',
				'font-subsets'        => array(),
				'font-weight'         => '400',
				'font-style'          => 'inherit',
				'text-transform'      => 'inherit',
				'text-decoration'     => 'inherit',
				'font-size-desktop'   => '',
				'font-size-tablet'    => '',
				'font-size-mobile'    => '',
				'font-size-unit'      => 'px',
				'letter-spacing'      => '0',
				'letter-spacing-unit' => 'px',
				'line-height-desktop' => '',
				'line-height-tablet'  => '',
				'line-height-mobile'  => '',
				'line-height-unit'    => '',
			)
		);

		$options = wp_parse_args( $options, $defaults );

		return $options;
	}
}
