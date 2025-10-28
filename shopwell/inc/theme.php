<?php
/**
 * Shopwell init
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Shopwell
 */

namespace Shopwell;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


/**
 * Shopwell theme init
 */
final class Theme {

	/**
	 * Instance
	 *
	 * @var $instance
	 */
	private static $instance = null;

	/**
	 * Blog manager instance.
	 *
	 * @var $blog_manager
	 */
	public $blog_manager = null;

	/**
	 * Theme version.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $version = '1.0.9';

	/**
	 * Initiator
	 *
	 * @since 1.0.0
	 * @return object
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) {
			$instance = new self();
			$instance->constants();
			self::$instance = $instance;
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
		$this->include_files();
	}

	/**
	 * Setup constants.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function constants() {
		if ( ! defined( 'SHOPWELL_THEME_VERSION' ) ) {
			define( 'SHOPWELL_THEME_VERSION', $this->version );
		}
		if ( ! defined( 'SHOPWELL_THEME_URI' ) ) {
			define( 'SHOPWELL_THEME_URI', get_parent_theme_file_uri() );
		}
		if ( ! defined( 'SHOPWELL_THEME_PATH' ) ) {
			define( 'SHOPWELL_THEME_PATH', get_parent_theme_file_path() );
		}
	}

	/**
	 * Function to include files
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function include_files() {
		require_once get_template_directory() . '/inc/autoload.php';
		require_once get_template_directory() . '/inc/common.php';
		require_once get_template_directory() . '/inc/customizer/customizer-callbacks.php';
	}

	/**
	 * Hooks to init
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function init() {
		// Before init action.
		do_action( 'before_shopwell_init' );

		add_action( 'after_setup_theme', array( $this, 'setup_theme' ) );
		add_action( 'after_setup_theme', array( $this, 'setup_content_width' ), 0 );
		add_action( 'after_switch_theme', array( $this, 'shopwell_theme_activated' ), 0 );
		add_action( 'widgets_init', array( $this, 'widgets_init' ) );

		add_action( 'init', array( $this, 'loads' ), 50 );
		add_action( 'template_redirect', array( $this, 'load_post_types' ), 30 );
		\Shopwell\Admin::instance();

		if ( class_exists( 'WooCommerce' ) ) {
			\Shopwell\WooCommerce::instance();
		}

		\Shopwell\Dynamic_CSS::instance();

		// Init action.
		do_action( 'after_shopwell_init' );
	}

	/**
	 * Hooks to loads
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function loads() {
		\Shopwell\Customizer\Settings::instance();
		\Shopwell\Frontend::instance();

		\Shopwell\Header\Manager::instance();

		\Shopwell\Page_Header::instance();
		\Shopwell\Search_Ajax::instance();

		\Shopwell\Blog\Manager::instance();

		\Shopwell\Comments::instance();

		\Shopwell\Footer\Manager::instance();

		\Shopwell\Mobile\Navigation_bar::instance();

		\Shopwell\Languages\WPML::instance();

		if ( class_exists( 'TRP_Translate_Press' ) ) {
			\Shopwell\Languages\TRP::instance();
		}
	}

	/**
	 * Add Actions
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function load_post_types() {
		\Shopwell\Help_Center::instance();
		if ( is_page() && ! \Shopwell\Helper::is_help_center_page() ) {
			\Shopwell\Page::instance();
		}

		if ( is_404() ) {
			\Shopwell\Page_404::instance();
		}
	}

	/**
	 * Get class instance
	 *
	 * @since 1.0.0
	 *
	 * @return object
	 */
	public function get( $class ) {
		switch ( $class ) {
			default:
				$class = ucwords( $class );
				$class = '\Shopwell\\' . $class;
				if ( class_exists( $class ) ) {
					return $class::instance();
				}
				break;
		}
	}

	/**
	 * Setup theme
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function setup_theme() {
		/*
		* Make theme available for translation.
		* Translations can be filed in the /lang/ directory.
		* If you're building a theme based on shopwell, use a find and replace
		* to change  'shopwell' to the name of your theme in all the template files.
		*/
		load_theme_textdomain( 'shopwell', get_template_directory() . '/lang' );

		// Theme supports
		add_theme_support( 'automatic-feed-links' );
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support(
			'html5',
			array(
				'search-form',
				'comment-form',
				'comment-list',
				'gallery',
				'caption',
			)
		);
		add_theme_support(
			'custom-logo',
			apply_filters(
				'shopwell_custom_logo_args',
				array(
					'height'               => 80,
					'width'                => 180,
					'flex-height'          => true,
					'flex-width'           => true,
					'unlink-homepage-logo' => false,
				)
			)
		);

		add_theme_support( 'customize-selective-refresh-widgets' );

		// Load regular editor styles into the new block-based editor.
		add_theme_support( 'editor-styles' );
		add_editor_style( 'assets/css/editor-style.css' );

		// Load default block styles.
		add_theme_support( 'wp-block-styles' );

		// Add support for responsive embeds.
		add_theme_support( 'responsive-embeds' );

		add_theme_support( 'align-wide' );

		add_theme_support( 'align-full' );

		add_theme_support(
			'starter-content',
			array(
				'widgets' => array(
					'footer-widget' => array(
						'recent-posts',
						'categories',
						'archives',
						'meta',
					),
				),
			)
		);

		// Enable support for common post formats
		add_theme_support( 'post-formats', array( 'gallery', 'video' ) );

		add_image_size( 'shopwell-post-thumbnail-small', 100, 70, true );
		add_image_size( 'shopwell-post-thumbnail-medium', 364, 205, true );
		add_image_size( 'shopwell-post-thumbnail-large', 752, 420, true );
		add_image_size( 'shopwell-post-slider-widget', 276, 160, true );

		// This theme uses wp_nav_menu() in one location.
		register_nav_menus(
			array(
				'primary-menu'   => esc_html__( 'Primary Menu', 'shopwell' ),
				'secondary-menu' => esc_html__( 'Secondary Menu', 'shopwell' ),
				'category-menu'  => esc_html__( 'Category Menu', 'shopwell' ),
				'socials'        => esc_html__( 'Socials Menu', 'shopwell' ),
			)
		);
	}

	/**
	 * @return void
	 */
	public function shopwell_theme_activated() {
		// Delete theme demos transient
		delete_transient( 'hester_core_demo_templates' );
	}

	/**
	 * Set the $content_width global variable used by WordPress to set image dimennsions.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function setup_content_width() {
		$GLOBALS['content_width'] = apply_filters( 'shopwell_content_width', 640 );
	}

	/**
	 * Register widget area.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function widgets_init() {
		// Get footer options with a default value
		$footer_options = \Shopwell\Helper::get_option( 'footer_options' );

		// Define sidebars
		$sidebars = array(
			'blog-sidebar'   => esc_html__( 'Blog Sidebar', 'shopwell' ),
			'single-sidebar' => esc_html__( 'Single Sidebar', 'shopwell' ),
		);

		// Define footers
		$footers = array(
			'footer-widget' => esc_html__( 'Footer Widget', 'shopwell' ),
		);

		// Conditionally merge footers if the footer option is set to '2'
		if ( $footer_options == '1' ) {
			$sidebars = array_merge( $sidebars, $footers );
		}

		// Register all sidebars
		foreach ( $sidebars as $id => $name ) {
			register_sidebar(
				array(
					'name'          => $name,
					'id'            => $id,
					'before_widget' => '<div id="%1$s" class="widget %2$s">',
					'after_widget'  => '</div>',
					'before_title'  => '<h2 class="widget-title">',
					'after_title'   => '</h2>',
				)
			);
		}
	}
	/**
	 * Setup the theme global variable.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function setup_prop( $args = array() ) {
		$default = array(
			'panels'        => array(),
			'modals'        => array(),
			'modals-addons' => array(),
		);

		if ( isset( $GLOBALS['shopwell'] ) ) {
			$default = array_merge( $default, $GLOBALS['shopwell'] );
		}

		$GLOBALS['shopwell'] = wp_parse_args( $args, $default );
	}

	/**
	 * Get a propery from the global variable.
	 *
	 * @param string $prop Prop to get.
	 * @param string $default Default if the prop does not exist.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public static function get_prop( $prop, $default = '' ) {
		self::setup_prop(); // Ensure the global variable is setup.

		return isset( $GLOBALS['shopwell'], $GLOBALS['shopwell'][ $prop ] ) ? $GLOBALS['shopwell'][ $prop ] : $default;
	}

	/**
	 * Sets a property in the global variable.
	 *
	 * @param string $prop Prop to set.
	 * @param string $value Value to set.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function set_prop( $prop, $value = '' ) {
		if ( ! isset( $GLOBALS['shopwell'] ) ) {
			self::setup_prop();
		}

		if ( ! isset( $GLOBALS['shopwell'][ $prop ] ) ) {
			$GLOBALS['shopwell'][ $prop ] = $value;

			return;
		}

		if ( array_search( $value, self::get_prop( $prop ) ) !== false ) {
			return;
		}

		if ( is_array( $GLOBALS['shopwell'][ $prop ] ) ) {
			if ( is_array( $value ) ) {
				$GLOBALS['shopwell'][ $prop ] = array_merge( $GLOBALS['shopwell'][ $prop ], $value );
			} else {
				$GLOBALS['shopwell'][ $prop ][] = $value;
				array_unique( $GLOBALS['shopwell'][ $prop ] );
			}
		} else {
			$GLOBALS['shopwell'][ $prop ] = $value;
		}
	}
}
