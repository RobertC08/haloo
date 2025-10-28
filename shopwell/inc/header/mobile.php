<?php
/**
 * Header Main functions and definitions.
 *
 * @package Shopwell
 */

namespace Shopwell\Header;

use Shopwell\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Header mobile initial
 */
class Mobile {
	/**
	 * Instance
	 *
	 * @var $instance
	 */
	protected static $instance = null;

	/**
	 * header layout
	 *
	 * @var $instance
	 */
	protected static $header_layout = null;

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
	}

	/**
	 * Get the header.
	 *
	 * @return string
	 */
	public function render() {
		$show_header = ! get_post_meta( \Shopwell\Helper::get_post_ID(), 'shopwell_hide_header_section', true );
		if ( ! apply_filters( 'shopwell_get_header', $show_header ) ) {
			return;
		}

		$layout = self::get_layout();

		if ( 'custom' != $layout ) {
			$this->prebuild( $layout );
		} else {
			$options = array();

			// Header main.
			$sections = array(
				'left'   => \Shopwell\Helper::get_option( 'header_mobile_main_left' ),
				'center' => \Shopwell\Helper::get_option( 'header_mobile_main_center' ),
				'right'  => \Shopwell\Helper::get_option( 'header_mobile_main_right' ),
			);

			$classes = array( 'header-mobile-main', 'header-mobile-contents' );

			$this->contents( $sections, $options, array( 'class' => $classes ) );

			// Header bottom.
			$sections = array(
				'left'   => Helper::get_option( 'header_mobile_bottom_left' ),
				'center' => Helper::get_option( 'header_mobile_bottom_center' ),
				'right'  => Helper::get_option( 'header_mobile_bottom_right' ),
			);

			$classes = array( 'header-mobile-bottom', 'header-mobile-contents' );

			$this->contents( $sections, $options, array( 'class' => $classes ) );
		}
	}

	/**
	 * Get the header layout.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function get_layout() {
		if ( isset( self::$header_layout ) ) {
			return self::$header_layout;
		}

		$present = Helper::get_option( 'header_mobile_present' );

		if ( $present ) {
			self::$header_layout = 'prebuild' == $present ? Helper::get_option( 'header_mobile_version' ) : 'custom';
		} else {
			self::$header_layout = 'v11';
		}

		self::$header_layout = apply_filters( 'shopwell_get_header_mobile_layout', self::$header_layout );

		return self::$header_layout;
	}

	/**
	 * Display pre-build header
	 *
	 * @param string $version
	 */
	public function prebuild( $version = 'v1' ) {
		$sections = $this->get_prebuild( $version );

		$classes = array( 'header-mobile-main', 'header-mobile-contents' );
		$this->contents( $sections['main'], $sections['main_options'], array( 'class' => $classes ) );

		$classes = array( 'header-mobile-bottom', 'header-mobile-contents' );
		$this->contents( $sections['bottom'], $sections['bottom_options'], array( 'class' => $classes ) );
	}

	/**
	 * Display pre-build header
	 *
	 * @param string $version
	 */
	public function get_prebuild( $version = 'v1' ) {
		$search_prebuild = Helper::get_option( 'header_mobile_search_style_prebuild' );
		switch ( $version ) {
			case 'v1':
				$main_sections = array(
					'left'   => $this->get_header_items( array( 'hamburger', 'logo' ) ),
					'center' => array(),
					'right'  => $this->get_header_items( array( 'account', 'cart' ) ),
				);

				$main_options    = array(
					'account' => array(
						'account_type' => 'text',
					),
					'cart'    => array(
						'cart_display' => 'icon',
						'cart_icon'    => 'bag',
						'cart_type'    => 'subtle',
					),
				);
				$bottom_sections = array(
					'left'   => $this->get_header_items( array( 'search' ) ),
					'center' => array(),
					'right'  => array(),
				);
				$bottom_options  = array(
					'search' => array(
						'search_style'                 => 'form',
						'search_items'                 => array( 'search-field' ),
						'search_items_button_display'  => 'icon',
						'search_items_button_position' => 'outside',
						'search_items_button_spacing'  => true,
						'search_items_button_type'     => 'raised',
					),
				);

				if ( $search_prebuild == 'icon' ) {
					$search_item = array( 'item' => 'search' );
					array_unshift( $main_sections['right'], $search_item );

					$main_options['search'] = array(
						'search_style'     => 'icon',
						'search_icon_type' => 'text',
					);

					$bottom_sections['left'] = array();
					$bottom_options          = array();
				}

				$sticky_sections = array(
					'left'   => $this->get_header_items( array( 'hamburger', 'logo' ) ),
					'center' => array(),
					'right'  => $this->get_header_items( array( 'search', 'account', 'cart' ) ),
				);
				$sticky_options  = array(
					'account' => array(
						'account_type' => 'text',
					),
					'cart'    => array(
						'cart_display' => 'icon',
						'cart_icon'    => 'trolley',
						'cart_type'    => 'subtle',
					),
					'search'  => array(
						'search_style'     => 'icon',
						'search_icon_type' => 'text',
					),
				);
				break;

			case 'v11':
				$main_sections   = array(
					'left'   => array(
						array( 'item' => 'hamburger-v11' ),
						array( 'item' => 'logo' ),
					),
					'center' => array(),
					'right'  => array(
						array( 'item' => 'search' ),
						array( 'item' => 'preferences' ),
					),
				);
				$main_options    = array(
					'logo'        => array(
						'display' => 'blog',
					),
					'preferences' => array(
						'preferences_display' => 'icon',
						'preferences_type'    => 'text',
						'preferences_flag'    => true,
					),
					'search'      => array(
						'search_style' => 'icon',
					),
				);
				$bottom_sections = array();
				$bottom_options  = array();
				$sticky_sections = array(
					'left'   => array(
						array( 'item' => 'hamburger-v11' ),
						array( 'item' => 'logo' ),
					),
					'center' => array(),
					'right'  => array(
						array( 'item' => 'search' ),
						array( 'item' => 'preferences' ),
					),
				);
				$sticky_options  = array(
					'logo'        => array(
						'display' => 'blog',
					),
					'preferences' => array(
						'preferences_display' => 'icon',
						'preferences_type'    => 'text',
						'preferences_flag'    => true,
					),
					'search'      => array(
						'search_style' => 'icon',
					),
				);
				break;

			default:
				$main_sections   = array();
				$main_options    = array();
				$bottom_sections = array();
				$bottom_options  = array();
				$sticky_sections = array();
				$sticky_options  = array();
				break;
		}

		return apply_filters(
			'shopwell_prebuild_header_mobile',
			array(
				'main'           => $main_sections,
				'main_options'   => $main_options,
				'bottom'         => $bottom_sections,
				'bottom_options' => $bottom_options,
				'sticky'         => $sticky_sections,
				'sticky_options' => $sticky_options,
			),
			$version
		);
	}

	/**
	 * Display header items
	 *
	 * @since 1.0.0
	 *
	 * @param string $sections, $atts
	 */
	public function contents( $sections, $options, $atts = array() ) {
		if ( false == array_filter( $sections ) ) {
			return;
		}

		$classes = array();
		if ( isset( $atts['class'] ) ) {
			$classes = (array) $atts['class'];
			unset( $atts['class'] );
		}

		if ( empty( $sections['left'] ) && empty( $sections['right'] ) ) {
			unset( $sections['left'] );
			unset( $sections['right'] );
		}

		if ( ! empty( $sections['center'] ) ) {
			$classes[] = 'has-center';

			if ( empty( $sections['left'] ) && empty( $sections['right'] ) ) {
				$classes[] = 'no-sides';
			}
		} else {
			$classes[] = 'no-center';
			unset( $sections['center'] );

			if ( empty( $sections['left'] ) ) {
				unset( $sections['left'] );
			}

			if ( empty( $sections['right'] ) ) {
				unset( $sections['right'] );
			}
		}

		$attr = 'class="' . esc_attr( implode( ' ', $classes ) ) . '"';

		foreach ( $atts as $name => $value ) {
			$attr .= ' ' . $name . '="' . esc_attr( $value ) . '"';
		}
		?>
		<div <?php echo ! empty( $attr ) ? $attr : ''; ?>>
			<div class="site-header__container container">
				<?php foreach ( $sections as $section => $items ) : ?>
					<?php
					$class      = '';
					$item_names = wp_list_pluck( $items, 'item' );

					if ( in_array( 'primary-menu', $item_names ) ) {
						$class = 'has-menu';
					}
					?>

					<div class="header-<?php echo esc_attr( $section ); ?>-items header-items <?php echo esc_attr( $class ); ?>">
						<?php $this->items( $items, $options ); ?>
					</div>

				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Display header items
	 *
	 * @since 1.0.0
	 *
	 * @param array $items
	 * @param array $options
	 */
	public function items( $items, $options ) {
		if ( empty( $items ) ) {
			return;
		}

		foreach ( $items as $item ) {
			$item['item']  = $item['item'] ? $item['item'] : key( \Shopwell\Customizer\Settings::header_mobile_items_option() );
			$template_file = $item['item'];
			$args          = array();
			$load_file     = true;

			switch ( $item['item'] ) {
				case 'logo':
					$args = $this->logo_options( $options );
					break;

				case 'hamburger':
					\Shopwell\Theme::set_prop( 'panels', 'mobile-menu' );
					$args['data_target'] = 'mobile-menu-panel';
					break;

				case 'hamburger-v11':
					\Shopwell\Theme::set_prop( 'panels', 'mobile-header-v11-menu' );
					$args['data_target'] = 'mobile-header-v11-menu-panel';
					$template_file       = 'hamburger';
					break;

				case 'hamburger-v12':
					\Shopwell\Theme::set_prop( 'panels', 'mobile-header-v12-menu' );
					$args['data_target'] = 'mobile-header-v12-menu-panel';
					$template_file       = 'hamburger';
					break;

				case 'account':
					$args = $this->account_options( $options );
					break;

				case 'cart':
					$args = $this->cart_options( $options );
					break;

				case 'wishlist':
					$args = $this->wishlist_options( $options );
					break;

				case 'search':
					$args = $this->search_options( $options );
					if ( isset( $args['template_file'] ) ) {
						$template_file = $args['template_file'];
					}
					break;

				case 'language':
					$args = \Shopwell\Header\Main::language_options( $options );
					if ( isset( $args['template_file'] ) ) {
						$template_file = $args['template_file'];
					}
					break;

				case 'currency':
					$args = \Shopwell\Header\Main::currency_options( $options );
					if ( isset( $args['template_file'] ) ) {
						$template_file = $args['template_file'];
					}
					break;

				case 'primary-menu':
					$args['menu_class'] = true;
					break;

				case 'preferences':
					\Shopwell\Theme::set_prop( 'modals', 'preferences' );
					$args      = \Shopwell\Header\Main::preferences_options( $options );
					$load_file = empty( $args ) ? false : true;
					break;

				case 'return':
					$args = $this->return_options( $options );
					break;
			}

			if ( $template_file && ! empty( $load_file ) ) {
				get_template_part( 'template-parts/header/' . $template_file, '', $args );
			}
		}
	}

	/**
	 * Logo options
	 *
	 * @since 1.0.0
	 *
	 * @param array $options
	 * @return array $args
	 */
	public function logo_options( $options ) {
		$options         = isset( $options['logo'] ) ? $options['logo'] : '';
		$args            = array();
		$mobile_type     = Helper::get_option( 'mobile_logo_type' );
		$mobile_type     = $mobile_type != 'default' ? $mobile_type : \Shopwell\Helper::get_option( 'logo_type' );
		$args['type']    = ! empty( $options ) && isset( $options['type'] ) ? $options['type'] : $mobile_type;
		$args['type']    = apply_filters( 'shopwell_header_logo_type', $args['type'] );
		$args['display'] = ! empty( $options ) && isset( $options['display'] ) ? $options['display'] : 'dark';
		$args['title']   = true;
		$mobile_logo     = '';
		switch ( $args['type'] ) {
			case 'text':
				$mobile_logo = Helper::get_option( 'mobile_logo_text' );
				break;
			case 'image':
				$mobile_logo = Helper::get_option( 'mobile_logo_image' );
				break;
			case 'svg':
				$mobile_logo = Helper::get_option( 'mobile_logo_svg' );
				break;
			default:
				break;
		}
		if ( ! empty( $mobile_logo ) ) {
			$args['logo'] = $mobile_logo;
		}
		return $args;
	}

	/**
	 * Search options
	 *
	 * @since 1.0.0
	 *
	 * @param array $options
	 * @return array $args
	 */
	public function search_options( $options ) {
		$options = isset( $options['search'] ) ? $options['search'] : '';
		$args    = array();

		$args['trending_searches'] = Helper::get_option( 'header_mobile_search_trending_searches' );
		$args['search_style']      = ! empty( $options ) && isset( $options['search_style'] ) ? $options['search_style'] : Helper::get_option( 'header_mobile_search_style' );

		if ( $args['search_style'] == 'form' ) {
			$args['template_file']   = 'search-form';
			$args['search_style']    = 'form';
			$args['search_class']    = 'header-search--form';
			$args['show_categories'] = false;

			$args['search_items']                 = ! empty( $options ) && isset( $options['search_items'] ) ? $options['search_items'] : (array) Helper::get_option( 'header_mobile_search_items' );
			$args['search_items_button_display']  = ! empty( $options ) && isset( $options['search_items_button_display'] ) ? $options['search_items_button_display'] : Helper::get_option( 'header_mobile_search_items_button_display' );
			$args['search_items_button_position'] = ! empty( $options ) && isset( $options['search_items_button_position'] ) ? $options['search_items_button_position'] : Helper::get_option( 'header_mobile_search_items_button_position' );
			$args['search_items_button_spacing']  = ! empty( $options ) && isset( $options['search_items_button_spacing'] ) ? $options['search_items_button_spacing'] : Helper::get_option( 'header_mobile_search_items_button_spacing' );
			$args['search_items_button_type']     = ! empty( $options ) && isset( $options['search_items_button_type'] ) ? $options['search_items_button_type'] : Helper::get_option( 'header_search_skins' );

			$args['search_class'] .= ' shopwell-skin--' . $args['search_items_button_type'];

			if ( Helper::get_option( 'header_mobile_present' ) == 'custom' ) {
				$args['search_class'] .= ' header-search--custom';
			}

			if ( $args['search_items_button_display'] !== 'none' ) {
				$args['search_class'] .= ' header-search--' . $args['search_items_button_position'];
			}

			if ( count( $args['search_items'] ) == 1 ) {
				$args['search_class'] .= ' header-search--simple';
			}

			$args['search_items_button_class'] = '';

			if ( $args['search_items_button_display'] == 'icon' ) {
				$args['search_items_button_class'] .= ' shopwell-button--icon';
			} else {
				$args['search_items_button_class'] .= ' shopwell-button--display-text';
			}

			$args['search_items_input_class'] = $args['search_items_form_class'] = '';
			if ( $args['search_items_button_position'] == 'outside' ) {
				if ( ! $args['search_items_button_spacing'] ) {
					$args['search_class'] .= ' shopwell-search--button-no-spacing';
				}

				if ( $args['search_items_button_type'] ) {
				}

				$args['search_items_input_class'] .= ' shopwell-type--input-text';
			} else {
				if ( $args['search_items_button_display'] == 'icon' ) {
					$args['search_items_button_type'] = 'text';
				}

				$args['search_items_form_class'] .= ' shopwell-type--input-text';
			}

			$args['search_items_button_class'] .= ' shopwell-button--' . $args['search_items_button_type'];

			$args['trending_searches_position'] = 'outside';

		} else {

			$args['template_file'] = 'search-icon-modal';

			$args['search_style']      = ! empty( $options ) && isset( $options['search_style'] ) ? $options['search_style'] : Helper::get_option( 'header_mobile_search_style' );
			$args['search_icon_type']  = ! empty( $options ) && isset( $options['search_icon_type'] ) ? $options['search_icon_type'] : Helper::get_option( 'header_mobile_search_icon_type' );
			$args['search_icon_class'] = '';
			if ( $args['search_style'] ) {
				$args['search_icon_class'] = 'shopwell-button--icon shopwell-button shopwell-button--' . $args['search_icon_type'];
			}
			$args['search_modal'] = 'search';
			$args                 = apply_filters( 'shopwell_header_search_icon_args', $args );
			\Shopwell\Theme::set_prop( 'modals', $args['search_modal'] );
		}

		return $args;
	}

	/**
	 * Account options
	 *
	 * @since 1.0.0
	 *
	 * @param array $options
	 * @return array $args
	 */
	public function account_options( $options ) {
		$options = isset( $options['account'] ) ? $options['account'] : '';
		$args    = array();

		$args['account_display']     = 'icon';
		$args['account_classes']     = 'shopwell-button--icon';
		$args['account_text_class']  = ' screen-reader-text';
		$args['account_data_toggle'] = 'off-canvas';
		\Shopwell\Theme::set_prop( 'panels', 'account' );

		$args['account_type'] = ! empty( $options ) && isset( $options['account_type'] ) ? $options['account_type'] : Helper::get_option( 'header_mobile_account_type' );

		if ( $args['account_type'] ) {
			$args['account_classes'] .= ' shopwell-button--' . $args['account_type'];
		}

		return $args;
	}

	/**
	 * Wishlist options
	 *
	 * @since 1.0.0
	 *
	 * @param array $options
	 * @return array $args
	 */
	public function wishlist_options( $options ) {
		$options = isset( $options['wishlist'] ) ? $options['wishlist'] : '';
		$args    = array();

		$args['wishlist_display']    = 'icon';
		$args['wishlist_classes']    = 'shopwell-button--icon';
		$args['wishlist_text_class'] = ' screen-reader-text';

		$args['wishlist_type'] = ! empty( $options ) && isset( $options['wishlist_type'] ) ? $options['wishlist_type'] : Helper::get_option( 'header_mobile_wishlist_type' );

		if ( $args['wishlist_type'] ) {
			$args['wishlist_classes'] .= ' shopwell-button--' . $args['wishlist_type'];
		}

		return $args;
	}

	/**
	 * Cart options
	 *
	 * @since 1.0.0
	 *
	 * @param array $options
	 * @return array $args
	 */
	public function cart_options( $options ) {
		$options = isset( $options['cart'] ) ? $options['cart'] : '';
		$args    = array();

		$args['cart_classes']     = $args['cart_text_class'] = '';
		$args['cart_data_toggle'] = 'off-canvas';
		\Shopwell\Theme::set_prop( 'panels', 'cart' );

		$args['cart_display']       = ! empty( $options ) && isset( $options['cart_display'] ) ? $options['cart_display'] : Helper::get_option( 'header_mobile_cart_display' );
		$args['cart_icon_position'] = ! empty( $options ) && isset( $options['cart_icon_position'] ) ? $options['cart_icon_position'] : '';

		$args['cart_type'] = ! empty( $options ) && isset( $options['cart_type'] ) ? $options['cart_type'] : Helper::get_option( 'header_mobile_cart_type' );

		if ( $args['cart_display'] == 'icon' ) {
			$args['cart_classes']    .= 'shopwell-button--icon';
			$args['cart_text_class'] .= ' screen-reader-text';
		}

		if ( $args['cart_type'] ) {
			$args['cart_classes'] .= ' shopwell-button--' . $args['cart_type'];
		}

		if ( $args['cart_icon_position'] == 'icon-top' ) {
			$args['cart_classes'] .= ' shopwell-button--icon-top';
		}

		$args['cart_text']        = esc_html__( 'Cart', 'shopwell' );
		$args['cart_icon']        = ! empty( $options ) && isset( $options['cart_icon'] ) ? $options['cart_icon'] : Helper::get_option( 'header_cart_icon' );
		$args['cart_icon_custom'] = ! empty( $options ) && isset( $options['cart_icon_custom'] ) ? $options['cart_icon_custom'] : Helper::get_option( 'header_cart_icon_custom' );
		$args['cart_icon']        = $args['cart_icon'] == 'custom' ? $args['cart_icon_custom'] : $args['cart_icon'];

		return $args;
	}

	/**
	 * Return options
	 *
	 * @since 1.0.0
	 *
	 * @param array $options
	 * @return array $args
	 */
	public function return_options( $options ) {
		$options = isset( $options['return'] ) ? $options['return'] : '';
		$args    = array();

		$args['return_type']  = ! empty( $options ) && isset( $options['return_type'] ) ? $options['return_type'] : 'default';
		$args['return_class'] = $args['return_button_class'] = $args['return_text'] = '';
		$args['return_link']  = Helper::get_option( 'header_return_button_link' );

		switch ( $args['return_type'] ) {
			case 'default':
				$args['return_class']        = 'shopwell-return-button--default';
				$args['return_button_class'] = '';
				$args['return_text']         = esc_html__( 'Return the Shop', 'shopwell' );
				break;

			case 'base':
				$args['return_class']        = 'shopwell-return-button--base';
				$args['return_button_class'] = 'shopwell-button shopwell-button--base shopwell-button--bg-color-black shopwell-button--large';
				$args['return_text']         = esc_html__( 'Shop', 'shopwell' );
				break;
		}

		return $args;
	}

	/**
	 * Get the sticky header.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function sticky_render() {
		$layout = Helper::get_option( 'header_mobile_sticky' );

		if ( 'none' == $layout ) {
			return;
		}

		if ( get_post_meta( \Shopwell\Helper::get_post_ID(), 'shopwell_hide_header_section', true ) ) {
			return;
		}

		$product_header = is_singular( 'product' ) && Helper::get_option( 'mobile_product_header' ) == 'compact';
		if ( apply_filters( 'shopwell_get_product_header_mobile', $product_header ) ) {
			return;
		}

		if ( 'normal' == $layout ) {
			$header_layout = self::get_layout();
			$sections      = $this->get_prebuild( $header_layout );

			$classes = array( 'header-mobile-sticky', 'header-mobile-contents' );
			$this->contents( $sections['sticky'], $sections['sticky_options'], array( 'class' => $classes ) );

		} elseif ( 'custom' == $layout ) {
			$options = array();

			$sections = array(
				'left'   => Helper::get_option( 'header_mobile_sticky_left' ),
				'center' => Helper::get_option( 'header_mobile_sticky_center' ),
				'right'  => Helper::get_option( 'header_mobile_sticky_right' ),
			);

			$classes = array( 'header-mobile-sticky', 'header-mobile-contents' );

			$this->contents( $sections, $options, array( 'class' => $classes ) );
		}
	}

	/**
	 * Get the sticky header classes.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function sticky_classes() {
		$sticky  = \Shopwell\Helper::get_option( 'header_mobile_sticky' );
		$classes = '';
		if ( 'none' !== $sticky ) {
			$classes .= ' shopwell-header-sticky';
			$classes .= ' header-sticky--' . $sticky;
		}

		return $classes;
	}

	/**
	 * Display header attributes
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function get_header_items( $atts = array( 'search' ) ) {
		$items = array();
		foreach ( $atts as $item ) {
			if ( 'logo' === $item ) {
				$items[] = array( 'item' => 'logo' );
			}
			$key = str_replace( '-', '_', $item );
			if ( Helper::get_option( 'header_mobile_present_' . $key ) ) {
				$items[] = array( 'item' => $item );
			}
		}

		return $items;
	}
}
