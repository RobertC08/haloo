<?php
/**
 * Header Main functions and definitions.
 *
 * @package Shopwell
 */

namespace Shopwell\Header;

use Shopwell\Helper;

use function WPML\FP\Strings\replace;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Header Main initial
 */
class Main {
	/**
	 * Instance
	 *
	 * @var $instance
	 */
	protected static $instance = null;

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
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function render() {
		$layout = self::get_layout();

		if ( 'custom' != $layout ) {
			$this->prebuild( $layout );
		} else {
			$options = array();

			// Header main.
			$sections = array(
				'left'   => Helper::get_option( 'header_main_left' ),
				'center' => Helper::get_option( 'header_main_center' ),
				'right'  => Helper::get_option( 'header_main_right' ),
			);

			$classes = $this->header_classes( 'main', array( 'header-main', 'header-contents' ) );

			$this->contents( $sections, $options, array( 'class' => $classes ) );

			// Header bottom.
			$sections = array(
				'left'   => Helper::get_option( 'header_bottom_left' ),
				'center' => Helper::get_option( 'header_bottom_center' ),
				'right'  => Helper::get_option( 'header_bottom_right' ),
			);

			$classes = $this->header_classes( 'bottom', array( 'header-bottom', 'header-contents' ) );

			$this->contents( $sections, $options, array( 'class' => $classes ) );

			// Header sticky.
			$header_sticky = Helper::get_option( 'header_sticky' );
			if ( $header_sticky && 'none' !== $header_sticky ) {
				$sections = array(
					'left'   => Helper::get_option( 'header_sticky_left' ),
					'center' => Helper::get_option( 'header_sticky_center' ),
					'right'  => Helper::get_option( 'header_sticky_right' ),
				);

				$classes = $this->header_classes( 'sticky', array( 'header-sticky', 'header-contents' ) );

				$this->contents( $sections, $options, array( 'class' => $classes ) );
			}
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

		$present = Helper::get_option( 'header_present' );
		if ( $present ) {
			self::$header_layout = 'prebuild' == $present ? Helper::get_option( 'header_version' ) : 'custom';
		} else {
			self::$header_layout = 'v11';
		}

		self::$header_layout = apply_filters( 'shopwell_get_header_layout', self::$header_layout );

		return self::$header_layout;
	}

	/**
	 * Display pre-build header
	 *
	 * @since 1.0.0
	 *
	 * @param string $version
	 */
	public function prebuild( $version = 'v1' ) {
		$sections = $this->get_prebuild( $version );

		$classes = $this->header_classes( 'main', array( 'header-main', 'header-contents' ) );
		$this->contents( $sections['main'], $sections['main_options'], array( 'class' => $classes ) );

		$classes = $this->header_classes( 'bottom', array( 'header-bottom', 'header-contents' ) );
		$this->contents( $sections['bottom'], $sections['bottom_options'], array( 'class' => $classes ) );
	}

	/**
	 * Display pre-build header
	 *
	 * @since 1.0.0
	 *
	 * @param string $version
	 */
	public function get_prebuild( $version = 'v1' ) {
		switch ( $version ) {
			case 'v1':
				$main_sections   = array(
					'left'   => array(
						array( 'item' => 'logo' ),
					),
					'center' => array(),
					'right'  => $this->get_header_items( array( 'search', 'compare', 'account', 'cart', 'wishlist' ) ),
				);
				$main_options    = array(
					'search'   => array(
						'search_style'                 => 'form',
						'search_form_width'            => 655,
						'search_items'                 => array( 'search-field', 'divider', 'categories' ),
						'search_items_button_display'  => 'icon',
						'search_items_button_position' => 'inside',
						'search_items_button_spacing'  => true,
						'trending_searches_position'   => 'inside',
						'search_items_button_type'     => 'smooth',
						'search_categories_label'      => esc_html__( 'All Categories', 'shopwell' ),
					),
					'compare'  => array(
						'compare_display' => 'icon',
						'compare_type'    => 'text',
					),
					'account'  => array(
						'account_display'       => 'icon',
						'account_type'          => 'text',
						'account_icon_position' => 'icon-left',
					),
					'cart'     => array(
						'cart_display'       => 'icon',
						'cart_icon'          => 'bag',
						'cart_type'          => 'text',
						'cart_icon_position' => 'icon-left',
					),
					'wishlist' => array(
						'wishlist_display' => 'icon',
						'wishlist_type'    => 'text',
					),
				);
				$bottom_sections = array(
					'left'   => $this->get_header_items( array( 'category-menu', 'primary-menu' ) ),
					'center' => array(),
					'right'  => $this->get_header_items( array( 'custom-text' ) ),
				);
				$bottom_options  = array(
					'category_menu' => array(
						'display'   => 'both',
						'type'      => 'ghost',
						'icon'      => 'v1',
						'mega_menu' => true,
					),
					'primary_menu'  => array(
						'mega_menu'  => true,
						'menu_class' => true,
						'dividers'   => false,

					),
				);
				break;
			case 'v11':
				$main_sections   = array(
					'left'   => array(
						array( 'item' => 'return' ),
					),
					'center' => array(),
					'right'  => array(
						array( 'item' => 'socials' ),
					),
				);
				$main_options    = array();
				$bottom_sections = array(
					'left'   => array(
						array( 'item' => 'logo' ),
						array( 'item' => 'primary-menu' ),
					),
					'center' => array(),
					'right'  => array(
						array( 'item' => 'search' ),
					),
				);
				$bottom_options  = array(
					'logo'         => array(
						'display' => 'blog',
					),
					'primary_menu' => array(
						'dividers' => false,
					),
					'search'       => array(
						'search_style'      => 'icon',
						'trending_searches' => false,
					),
				);
				break;
			default:
				$main_sections   = array();
				$main_options    = array();
				$bottom_sections = array();
				$bottom_options  = array();
				break;
		}

		return apply_filters(
			'shopwell_prebuild_header',
			array(
				'main'           => $main_sections,
				'main_options'   => $main_options,
				'bottom'         => $bottom_sections,
				'bottom_options' => $bottom_options,
			),
			$version
		);
	}

	/**
	 * Display header attributes
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function get_header_items( $atts = array( 'search' ) ) {
		$items = array();
		foreach ( $atts as $item ) {
			if ( 'logo' === $item ) {
				$items[] = array( 'item' => 'logo' );
			}
			if ( 'empty-space' === $item ) {
				$items[] = array( 'item' => 'empty-space' );
			}
			$key = str_replace( '-', '_', $item );
			if ( Helper::get_option( 'header_present_' . $key ) ) {
				$items[] = array( 'item' => $item );
			}
		}

		return $items;
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

		if ( isset( $options['search'] ) ) {
			$search_style = $options['search']['search_style'];
		} else {
			$search_style = Helper::get_option( 'header_search_style' );
		}

		if ( $search_style == 'form' ) {
			if ( ! empty( $sections['center'] ) ) {
				$center_items = wp_list_pluck( $sections['center'], 'item' );
				if ( in_array( 'search', $center_items ) ) {
					$classes[] = 'has-search-item';
				}
			}

			if ( ! empty( $sections['left'] ) ) {
				$left_items = wp_list_pluck( $sections['left'], 'item' );
				if ( in_array( 'search', $left_items ) ) {
					$classes[] = 'has-search-item';
				}
			}

			if ( ! empty( $sections['right'] ) ) {
				$right_items = wp_list_pluck( $sections['right'], 'item' );
				if ( in_array( 'search', $right_items ) ) {
					$classes[] = 'has-search-item';
				}
			}
		}

		$attr = 'class="' . esc_attr( implode( ' ', $classes ) ) . '"';
		foreach ( $atts as $name => $value ) {
			$attr .= ' ' . $name . '="' . esc_attr( $value ) . '"';
		}
		?>
		<div <?php echo ! empty( $attr ) ? $attr : ''; ?>>
			<div class="site-header__container <?php echo esc_attr( apply_filters( 'shopwell_header_container_classes', 'container' ) ); ?>">
				<?php foreach ( $sections as $section => $items ) : ?>
					<?php
					$class      = array();
					$item_names = wp_list_pluck( $items, 'item' );

					if ( in_array( 'primary-menu', $item_names ) ) {
						$class[] = 'has-menu';
					}

					if ( in_array( 'secondary-menu', $item_names ) ) {
						$class[] = 'has-menu';
					}

					if ( in_array( 'search', $item_names ) ) {
						$class[] = 'has-search';
					}
					?>

					<div class="header-<?php echo esc_attr( $section ); ?>-items header-items <?php echo esc_attr( implode( ' ', $class ) ); ?>">
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
			$item['item']  = $item['item'] ? : key( \Shopwell\Customizer\Settings::header_items_option() );
			$template_file = $item['item'];
			$args          = array();
			$load_file     = true;
			switch ( $item['item'] ) {
				case 'logo':
					$args = $this->logo_options( $options );
					break;

				case 'search':
					$args = $this->search_options( $options );
					if ( isset( $args['template_file'] ) ) {
						$template_file = $args['template_file'];
					}

					break;

				case 'language':
					$args = $this->language_options( $options );
					if ( isset( $args['template_file'] ) ) {
						$template_file = $args['template_file'];
					}
					break;

				case 'currency':
					$args = $this->currency_options( $options );
					if ( isset( $args['template_file'] ) ) {
						$template_file = $args['template_file'];
					}
					break;

				case 'preferences':
					\Shopwell\Theme::set_prop( 'modals', 'preferences' );
					$args      = $this->preferences_options( $options );
					$load_file = empty( $args ) ? false : true;
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

				case 'compare':
					$args = $this->compare_options( $options );
					break;

				case 'category-menu':
					$args = $this->category_menu_options( $options );
					break;

				case 'primary-menu':
					$args            = $this->primary_menu_options( $options );
					$primary_menu_id = '';
					$primary_menu_id = apply_filters( 'shopwell_get_primary_menu', $primary_menu_id );
					if ( ! empty( $primary_menu_id ) ) {
						$args['menu_id'] = $primary_menu_id;
						$template_file   = 'navigation-menu';
					}
					break;

				case 'hamburger':
					\Shopwell\Theme::set_prop( 'panels', $item['item'] );
					$args['data_target'] = $item['item'] . '-panel';
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
		$args            = array();
		$args['title']   = ! empty( $options ) && isset( $options['logo_title'] ) ? $options['logo_title'] : true;
		$options         = isset( $options['logo'] ) ? $options['logo'] : '';
		$args['type']    = ! empty( $options ) && isset( $options['type'] ) ? $options['type'] : Helper::get_option( 'logo_type' );
		$args['type']    = apply_filters( 'shopwell_header_logo_type', $args['type'] );
		$args['display'] = ! empty( $options ) && isset( $options['display'] ) ? $options['display'] : 'dark';
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

		$args['search_style']      = ! empty( $options ) && isset( $options['search_style'] ) ? $options['search_style'] : Helper::get_option( 'header_search_style' );
		$args['trending_searches'] = ! empty( $options ) && isset( $options['trending_searches'] ) ? $options['trending_searches'] : Helper::get_option( 'header_search_trending_searches' );
		$args['search_class']      = 'header-search--' . $args['search_style'];

		if ( Helper::get_option( 'header_present' ) == 'custom' ) {
			$args['search_class'] .= ' header-search--custom';
		}

		if ( $args['search_style'] == 'form' ) {
			$args['template_file'] = 'search-form';

			$args['search_style_css'] = ! empty( $options ) && isset( $options['search_form_width'] ) ? intval( $options['search_form_width'] ) : intval( Helper::get_option( 'header_search_form_width' ) );

			if ( ! empty( $options ) && isset( $options['search_form_width'] ) ) {
				if ( intval( $options['search_form_width'] ) < 440 ) {
					$args['search_class'] .= ' header-search--small';
				} elseif ( intval( $options['search_form_width'] ) > 570 ) {
					$args['search_class'] .= ' header-search--large';
				}
			} elseif ( intval( Helper::get_option( 'header_search_form_width' ) ) < 440 ) {
					$args['search_class'] .= ' header-search--small';
			} elseif ( intval( Helper::get_option( 'header_search_form_width' ) ) > 570 ) {
				$args['search_class'] .= ' header-search--large';
			}

			$args['search_items'] = ! empty( $options ) && isset( $options['search_items'] ) ? $options['search_items'] : (array) array_keys( (array) Helper::get_option( 'header_search_items' ), true, true );

			if ( count( $args['search_items'] ) == 1 ) {
				$args['search_class'] .= ' header-search--simple';
			}

			$args['search_items_button_display']  = ! empty( $options ) && isset( $options['search_items_button_display'] ) ? $options['search_items_button_display'] : Helper::get_option( 'header_search_items_button_display' );
			$args['search_items_button_position'] = ! empty( $options ) && isset( $options['search_items_button_position'] ) ? $options['search_items_button_position'] : Helper::get_option( 'header_search_items_button_position' );
			$args['search_items_button_spacing']  = isset( $options['search_items_button_spacing'] ) ? $options['search_items_button_spacing'] : Helper::get_option( 'header_search_items_button_spacing' );
			$args['search_items_button_type']     = ! empty( $options ) && isset( $options['search_items_button_type'] ) ? $options['search_items_button_type'] : Helper::get_option( 'header_search_skins' );
			$args['search_class']                .= ' shopwell-skin--' . $args['search_items_button_type'];
			if ( $args['search_items_button_display'] !== 'none' ) {
				$args['search_class'] .= ' header-search--' . $args['search_items_button_position'];
			}

			$args['search_items_button_class'] = '';
			if ( $args['search_items_button_display'] == 'icon' ) {
				$args['search_items_button_class'] .= ' shopwell-button--icon';
			}

			$args['search_items_input_class'] = $args['search_items_form_class'] = '';
			if ( $args['search_items_button_position'] == 'outside' ) {
				if ( ! $args['search_items_button_spacing'] ) {
					$args['search_class'] .= ' shopwell-search--button-no-spacing';
				}

				$args['search_items_input_class'] .= ' shopwell-type--input-text';
			} else {
				if ( $args['search_items_button_display'] == 'icon' ) {
					$args['search_items_button_type'] = 'text';
				}

				$args['search_items_form_class'] .= ' shopwell-type--input-text';
			}

			if ( $args['search_items_button_type'] ) {
				if ( $args['search_items_button_type'] == 'ghost' ) {
					$args['search_items_button_class'] .= ' shopwell-button--base';
				} else {
					$args['search_items_button_class'] .= ' shopwell-button--' . $args['search_items_button_type'];
				}
			}

			$args['search_categories_label'] = ! empty( $options ) && isset( $options['search_categories_label'] ) ? $options['search_categories_label'] : esc_html__( 'All Categories', 'shopwell' );

			$args['show_categories'] = in_array( 'categories', $args['search_items'] ) ? true : false;
			if ( $args['show_categories'] ) {
				$args['show_categories'] = $args['search_categories_label'];
			}

			if ( Helper::is_blog() || is_singular( 'post' ) ) {
				$args['show_categories'] = false;
				$args['post_type']       = 'post';
				$args['search_items']    = array( 'search-field' );
			}
			$args['taxonomy'] = ( Helper::get_option( 'header_search_type' ) == 'product' || Helper::get_option( 'header_search_type' ) == 'adaptive' ) ? 'product_cat' : 'category';

			$args['trending_searches_position'] = ! empty( $options ) && isset( $options['trending_searches_position'] ) ? $options['trending_searches_position'] : Helper::get_option( 'header_search_trending_searches_position' );
		} else {
			$args['template_file']              = 'search-icon';
			$args['trending_searches_position'] = 'outside';
		}

		return $args;
	}

	/**
	 * Language options
	 *
	 * @since 1.0.0
	 *
	 * @param array $options
	 * @return array $args
	 */
	public static function language_options( $options ) {
		$options = isset( $options['language'] ) ? $options['language'] : '';
		$args    = array();

		$args = Helper::get_languages( $args );
		if ( $args ) {
			\Shopwell\Theme::set_prop( 'modals', 'preferences' );
			$args['template_file'] = 'preferences';
		}

		return $args;
	}

	/**
	 * Currency options
	 *
	 * @since 1.0.0
	 *
	 * @param array $options
	 * @return array $args
	 */
	public static function currency_options( $options ) {
		$options = isset( $options['language'] ) ? $options['language'] : '';
		$args    = array();

		$args = \Shopwell\WooCommerce\Currency::get_currencies( $args );
		if ( $args ) {
			\Shopwell\Theme::set_prop( 'modals', 'preferences' );
			$args['template_file'] = 'preferences';
		}

		$args['template_file'] = 'preferences';

		return $args;
	}


	/**
	 * Preferences options
	 *
	 * @since 1.0.0
	 *
	 * @param array $options
	 * @return array $args
	 */
	public static function preferences_options( $options ) {
		$options = isset( $options['preferences'] ) ? $options['preferences'] : '';
		$args    = array();

		$args['preferences_position'] = 'header';

		$args['preferences_display'] = ! empty( $options ) && isset( $options['preferences_display'] ) ? $options['preferences_display'] : Helper::get_option( 'header_preferences_display' );
		$args['preferences_type']    = ! empty( $options ) && isset( $options['preferences_type'] ) ? $options['preferences_type'] : Helper::get_option( 'header_preferences_type' );
		$args['preferences_flag']    = ! empty( $options ) && isset( $options['preferences_flag'] ) ? $options['preferences_flag'] : Helper::get_option( 'header_preferences_flag' );
		$args['translated_name']     = ! empty( $options ) && isset( $options['translated_name'] ) ? $options['translated_name'] : true;
		$args['language_code']       = ! empty( $options ) && isset( $options['language_code'] ) ? $options['language_code'] : false;

		$args['preferences_classes'] = $args['preferences_text_class'] = $args['preferences_data_toggle'] = $args['preferences_text'] = $args['preferences_subtext'] = '';

		if ( $args['preferences_display'] == 'icon' ) {
			$args['preferences_classes']    = 'shopwell-button--icon';
			$args['preferences_text_class'] = 'screen-reader-text';
		}

		if ( $args['preferences_display'] == 'icon-subtext' ) {
			$args['preferences_subtext'] = esc_html__( 'Region', 'shopwell' );
		}

		if ( $args['preferences_type'] ) {
			$args['preferences_classes'] .= ' shopwell-button--' . $args['preferences_type'];
		}

		if ( $args['language_code'] ) {
			$args['preferences_text_class'] = ' language-code';
		}

		$languages = \Shopwell\Helper::language_status();
		if ( $languages ) {
			$args = Helper::get_languages( $args, $args['preferences_flag'], $args['translated_name'], $args['language_code'] );
		}

		$currency = \Shopwell\WooCommerce\Currency::currency_status();
		if ( $currency ) {
			$args = \Shopwell\WooCommerce\Currency::get_currencies( $args, 'flag' );
		}

		if ( ! $languages && ! $currency ) {
			return false;
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

		$args['account_display']       = ! empty( $options ) && isset( $options['account_display'] ) ? $options['account_display'] : Helper::get_option( 'header_account_display' );
		$args['account_icon_position'] = ! empty( $options ) && isset( $options['account_icon_position'] ) ? $options['account_icon_position'] : Helper::get_option( 'header_account_icon_position' );
		$args['account_type']          = ! empty( $options ) && isset( $options['account_type'] ) ? $options['account_type'] : Helper::get_option( 'header_account_type' );

		$args['account_classes'] = $args['account_text_class'] = $args['account_data_toggle'] = $args['account_text'] = $args['account_subtext'] = '';

		switch ( $args['account_display'] ) {
			case 'icon':
				$args['account_classes']    = 'shopwell-button--icon';
				$args['account_text_class'] = 'screen-reader-text';
				$args['account_text']       = esc_html__( 'Account', 'shopwell' );
				break;

			case 'text':
				if ( is_user_logged_in() ) {
					$args['account_text'] = esc_html__( 'Account', 'shopwell' );
				} else {
					$args['account_text'] = esc_html__( 'Sign in', 'shopwell' );
				}
				break;

			case 'icon-text':
				$args['account_text'] = esc_html__( 'Account', 'shopwell' );

				if ( $args['account_icon_position'] == 'icon-top' ) {
					$args['account_classes'] .= ' shopwell-button--icon-top';
				}
				break;

			case 'icon-subtext':
				$args['account_subtext'] = esc_html__( 'Welcome', 'shopwell' );
				$args['account_text']    = esc_html__( 'Sign in / Register', 'shopwell' );

				if ( is_user_logged_in() ) {
					$current_user         = wp_get_current_user();
					$args['account_text'] = $current_user->display_name;
				}
				break;
		}

		if ( $args['account_type'] ) {
			$args['account_classes'] .= ' shopwell-button--' . $args['account_type'];
		}

		if ( Helper::get_option( 'header_account_icon_behaviour' ) == 'panel' ) {
			$args['account_data_toggle'] = 'off-canvas';
			\Shopwell\Theme::set_prop( 'panels', 'account' );
		} else {
			$args['account_data_toggle'] = 'dropdown';
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

		$args['wishlist_display']       = ! empty( $options ) && isset( $options['wishlist_display'] ) ? $options['wishlist_display'] : Helper::get_option( 'header_wishlist_display' );
		$args['wishlist_icon_position'] = ! empty( $options ) && isset( $options['wishlist_icon_position'] ) ? $options['wishlist_icon_position'] : Helper::get_option( 'header_wishlist_icon_position' );
		$args['wishlist_type']          = ! empty( $options ) && isset( $options['wishlist_type'] ) ? $options['wishlist_type'] : Helper::get_option( 'header_wishlist_type' );

		$args['wishlist_classes'] = $args['wishlist_text_class'] = $args['wishlist_text'] = '';

		switch ( $args['wishlist_display'] ) {
			case 'icon':
				$args['wishlist_classes']    = 'shopwell-button--icon';
				$args['wishlist_text']       = esc_html__( 'Wishlist', 'shopwell' );
				$args['wishlist_text_class'] = 'screen-reader-text';
				break;

			case 'icon-text':
				$args['wishlist_text'] = esc_html__( 'Wishlist', 'shopwell' );

				if ( $args['wishlist_icon_position'] == 'icon-top' ) {
					$args['wishlist_classes'] .= ' shopwell-button--icon-top';
				}
				break;
		}

		if ( $args['wishlist_type'] ) {
			$args['wishlist_classes'] .= ' shopwell-button--' . $args['wishlist_type'];
		}

		return $args;
	}

	/**
	 * Compare options
	 *
	 * @since 1.0.0
	 *
	 * @param array $options
	 * @return array $args
	 */
	public function compare_options( $options ) {
		$options = isset( $options['compare'] ) ? $options['compare'] : '';
		$args    = array();

		$args['compare_display']       = ! empty( $options ) && isset( $options['compare_display'] ) ? $options['compare_display'] : Helper::get_option( 'header_compare_display' );
		$args['compare_icon_position'] = ! empty( $options ) && isset( $options['compare_icon_position'] ) ? $options['compare_icon_position'] : Helper::get_option( 'header_compare_icon_position' );
		$args['compare_type']          = ! empty( $options ) && isset( $options['compare_type'] ) ? $options['compare_type'] : Helper::get_option( 'header_compare_type' );

		$args['compare_classes'] = $args['compare_classes'] = $args['compare_classes'] = '';

		switch ( $args['compare_display'] ) {
			case 'icon':
				$args['compare_classes']    = 'shopwell-button--icon';
				$args['compare_text']       = esc_html__( 'Compare', 'shopwell' );
				$args['compare_text_class'] = 'screen-reader-text';
				break;

			case 'icon-text':
				$args['compare_text'] = esc_html__( 'Compare', 'shopwell' );

				if ( $args['compare_icon_position'] == 'icon-top' ) {
					$args['compare_classes'] .= ' shopwell-button--icon-top';
				}
				break;
		}

		if ( $args['compare_type'] ) {
			$args['compare_classes'] .= ' shopwell-button--' . $args['compare_type'];
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

		$args['cart_display']       = ! empty( $options ) && isset( $options['cart_display'] ) ? $options['cart_display'] : Helper::get_option( 'header_cart_display' );
		$args['cart_icon']          = ! empty( $options ) && isset( $options['cart_icon'] ) ? $options['cart_icon'] : Helper::get_option( 'header_cart_icon' );
		$args['cart_icon_position'] = ! empty( $options ) && isset( $options['cart_icon_position'] ) ? $options['cart_icon_position'] : Helper::get_option( 'header_cart_icon_position' );
		$args['cart_type']          = ! empty( $options ) && isset( $options['cart_type'] ) ? $options['cart_type'] : Helper::get_option( 'header_cart_type' );

		if ( $args['cart_icon'] == 'custom' ) {
			$args['cart_icon'] = ! empty( $options ) && isset( $options['cart_icon_custom'] ) ? $options['cart_icon_custom'] : Helper::get_option( 'header_cart_icon_custom' );
		}

		$args['cart_classes'] = $args['cart_text_class'] = $args['cart_text'] = '';

		switch ( $args['cart_display'] ) {
			case 'icon':
				$args['cart_classes']    = 'shopwell-button--icon';
				$args['cart_text_class'] = 'screen-reader-text';
				$args['cart_text']       = esc_html__( 'Cart', 'shopwell' );
				break;

			case 'icon-text':
				$args['cart_text'] = esc_html__( 'Cart', 'shopwell' );

				if ( $args['cart_icon_position'] == 'icon-top' ) {
					$args['cart_classes'] .= ' shopwell-button--icon-top';
					$args['cart_text']     = esc_html__( 'My Cart', 'shopwell' );
				}
				break;
		}

		if ( $args['cart_type'] ) {
			$args['cart_classes'] .= ' shopwell-button--' . $args['cart_type'];
		}

		if ( Helper::get_option( 'header_cart_icon_behaviour' ) == 'panel' ) {
			$args['cart_data_toggle'] = 'off-canvas';
			\Shopwell\Theme::set_prop( 'panels', 'cart' );
		} if ( Helper::get_option( 'header_cart_icon_behaviour' ) == 'page' ) {
			$args['cart_data_toggle'] = 'off-canvas';
		} else {
			$args['cart_classes'] .= ' header-button-dropdown';
		}

		return $args;
	}

	/**
	 * Category Menu options
	 *
	 * @since 1.0.0
	 *
	 * @param array $options
	 * @return array $args
	 */
	public function category_menu_options( $options ) {
		$options = isset( $options['category_menu'] ) ? $options['category_menu'] : '';
		$args    = array();

		$args['display']   = ! empty( $options ) && isset( $options['display'] ) ? $options['display'] : Helper::get_option( 'header_category_display' );
		$args['type']      = ! empty( $options ) && isset( $options['type'] ) ? $options['type'] : Helper::get_option( 'header_category_type' );
		$args['icon']      = ! empty( $options ) && isset( $options['icon'] ) ? $options['icon'] : Helper::get_option( 'header_category_icon' );
		$args['title']     = ! empty( $options ) && isset( $options['title'] ) ? $options['title'] : '';
		$args['mega_menu'] = ! empty( $options ) && isset( $options['mega_menu'] ) ? $options['mega_menu'] : true;
		$args['spacing']   = ! empty( $options ) && isset( $options['spacing'] ) ? $options['spacing'] : '';

		$args['classes']      = 'header-category--' . $args['display'];
		$args['class_button'] = 'shopwell-button--' . $args['type'];

		if ( ! $args['title'] ) {
			switch ( $args['type'] ) {
				case 'ghost':
					$args['title'] = esc_html__( 'Shop by Category', 'shopwell' );
					break;

				case 'subtle':
				case 'text':
					$args['title'] = esc_html__( 'Categories', 'shopwell' );
					break;
			}
		}

		return $args;
	}

	/**
	 * Primary Menu options
	 *
	 * @since 1.0.0
	 *
	 * @param array $options
	 * @return array $args
	 */
	public function primary_menu_options( $options ) {
		$options = isset( $options['primary_menu'] ) ? $options['primary_menu'] : '';
		$args    = array();

		$args['mega_menu']       = ! empty( $options ) && isset( $options['mega_menu'] ) ? $options['mega_menu'] : true;
		$args['menu_class']      = ! empty( $options ) && isset( $options['menu_class'] ) ? $options['menu_class'] : true;
		$args['dividers']        = ! empty( $options ) && isset( $options['dividers'] ) ? $options['dividers'] : Helper::get_option( 'header_primary_menu_dividers' );
		$args['container_class'] = $args['dividers'] == true ? ' primary-navigation--dividers' : '';

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
				$args['return_button_class'] = 'shopwell-button shopwell-button--base shopwell-button--large';
				$args['return_text']         = esc_html__( 'Return the Shop', 'shopwell' );
				break;
		}

		return $args;
	}

	/**
	 * Return classe
	 *
	 * @since 1.0.0
	 *
	 * @param array $classes
	 * @return array $args
	 */
	public function header_classes( $section, $classes = array() ) {
		return implode( ' ', $classes );
	}


	/**
	 * Header Custom logo
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function custom_logo( $type = '' ) {
		$custom_logo = '';
		$type        = empty( $type ) ? $type : get_post_meta( \Shopwell\Helper::get_post_ID(), 'header_logo_type', true );
		if ( 'text' == $type ) {
			$custom_logo = get_post_meta( \Shopwell\Helper::get_post_ID(), 'header_logo_text', true );
		} elseif ( 'svg' == $type ) {
			$custom_logo = get_post_meta( \Shopwell\Helper::get_post_ID(), 'header_logo_svg', true );
		} elseif ( $image_id = absint( get_post_meta( \Shopwell\Helper::get_post_ID(), 'header_logo_image', true ) ) ) {
				$image       = wp_get_attachment_image_src( $image_id, 'full' );
				$custom_logo = $image ? $image[0] : '';
		}
		return $custom_logo;
	}


	/**
	 * Display the site branding title
	 *
	 * @since 1.0.0
	 *
	 * @param array $args
	 * @return void
	 */
	public static function site_branding_title( $args = array() ) {
		$args = wp_parse_args(
			$args,
			array(
				'class' => '',
				'echo'  => true,
			)
		);

		// Ensure included a space at beginning.
		$class = ' site-title';

		// HTML tag for this title.
		$tag = is_front_page() || is_home() ? 'h1' : 'p';
		$tag = apply_filters( 'shopwell_site_branding_title_tag', $tag, $args );

		if ( is_array( $args['class'] ) ) {
			$class = implode( ' ', $args['class'] ) . $class;
		} elseif ( is_string( $args['class'] ) ) {
			$class = $args['class'] . $class;
		}

		$title = sprintf(
			'<%1$s class="%2$s"><a href="%3$s" rel="home">%4$s</a></%1$s>',
			$tag,
			esc_attr( trim( $class ) ),
			esc_url( home_url( '/' ) ),
			get_bloginfo( 'name' )
		);

		if ( ! $args['echo'] ) {
			return $title;
		}

		echo apply_filters( 'shopwell_site_branding_title_html', $title );
	}

	/**
	 * Display the site branding description
	 *
	 * @since 1.0.0
	 *
	 * @param array $args
	 * @return void
	 */
	public static function site_branding_description( $args = array() ) {
		$text            = get_bloginfo( 'description', 'display' );
		$display_tagline = \Shopwell\Helper::get_option( 'display_tagline' );

		if ( empty( $text ) ) {
			return '';
		}

		$args = wp_parse_args(
			$args,
			array(
				'class' => '',
				'echo'  => true,
			)
		);

		// Ensure included a space at beginning.
		$class = ' site-description';

		$class .= $display_tagline ? ' show-tagline' : ' hide-tagline';

		if ( is_array( $args['class'] ) ) {
			$class = implode( ' ', $args['class'] ) . $class;
		} elseif ( is_string( $args['class'] ) ) {
			$class = $args['class'] . $class;
		}

		$description = sprintf(
			'<p class="%s">%s</p>',
			esc_attr( trim( $class ) ),
			wp_kses_post( $text )
		);

		if ( ! $args['echo'] ) {
			return $description;
		}

		echo apply_filters( 'site_branding_description_html', $description );
	}

	/**
	 * Get the sticky header.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function sticky_render() {
		$layout = Helper::get_option( 'header_sticky' );

		if ( 'none' == $layout ) {
			return;
		}

		if ( get_post_meta( \Shopwell\Helper::get_post_ID(), 'shopwell_hide_header_section', true ) ) {
			return;
		}

		if ( get_option( 'shopwell_sticky_add_to_cart_toggle', 'yes' ) == 'yes' && is_singular( 'product' ) ) {
			return;
		}
		$options  = array();
		$sections = array();
		$classes  = array();
		if ( 'normal' == $layout ) {
			$header_layout    = self::get_layout();
			$header_sticky_el = Helper::get_option( 'header_sticky_el' );
			if ( $header_sticky_el ) {
				$classes = array( 'header-sticky', 'header-contents' );
				if ( $header_layout == 'custom' ) {
					if ( $header_sticky_el == 'header_main' ) {
						$sections = array(
							'left'   => Helper::get_option( 'header_main_left' ),
							'center' => Helper::get_option( 'header_main_center' ),
							'right'  => Helper::get_option( 'header_main_right' ),
						);
					} elseif ( $header_sticky_el == 'header_bottom' ) {
						$sections = array(
							'left'   => Helper::get_option( 'header_bottom_left' ),
							'center' => Helper::get_option( 'header_bottom_center' ),
							'right'  => Helper::get_option( 'header_bottom_right' ),
						);
					}
				} else {
					$sections = $this->get_prebuild( $header_layout );
					if ( $header_layout == 'v11' ) {
						$options  = $sections['bottom_options'];
						$sections = $sections['bottom'];
					} elseif ( $header_layout == 'v3' || $header_layout == 'v7' || $header_layout == 'v12' ) {
						$options  = $sections['main_options'];
						$sections = $sections['main'];
					} elseif ( $header_sticky_el == 'header_main' ) {
							$options  = ! empty( $sections['main_options'] ) ? $sections['main_options'] : $options;
							$sections = $sections['main'];
					} elseif ( $header_sticky_el == 'header_bottom' ) {
						$options   = ! empty( $sections['bottom_options'] ) ? $sections['bottom_options'] : $options;
						$sections  = $sections['bottom'];
						$classes[] = 'header-bottom';
					}
				}
			}
		} elseif ( 'custom' == $layout ) {
			$sections = array(
				'left'   => Helper::get_option( 'header_sticky_left' ),
				'center' => Helper::get_option( 'header_sticky_center' ),
				'right'  => Helper::get_option( 'header_sticky_right' ),
			);
			$classes  = array( 'header-sticky', 'header-contents' );
		}

		if ( empty( $sections ) ) {
			return;
		}
		$options['logo_title'] = false;
		$this->contents( $sections, $options, array( 'class' => $classes ) );
	}

	/**
	 * Get the sticky header classes.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function sticky_classes() {
		$sticky  = \Shopwell\Helper::get_option( 'header_sticky' );
		$classes = '';
		if ( 'none' !== $sticky ) {
			$classes .= ' shopwell-header-sticky';
			$classes .= ' header-sticky--' . $sticky;

			$sticky_on = \Shopwell\Helper::get_option( 'header_sticky_on' );
			if ( $sticky_on == 'up' ) {
				$classes .= ' header-sticky-on--' . $sticky_on;
			}
		}

		return $classes;
	}
}
