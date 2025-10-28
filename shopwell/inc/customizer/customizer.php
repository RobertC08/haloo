<?php
/**
 * Shopwell Customizer class
 *
 * @package     Shopwell
 * @author      Peregrine Themes
 * @since       1.0.0
 */

namespace Shopwell\Customizer;

/**
 * Do not allow direct script access.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shopwell Customizer class
 */
class Customizer {

	/**
	 * Singleton instance of the class.
	 *
	 * @since 1.0.0
	 * @var object
	 */
	private static $instance;

	/**
	 * Customizer options.
	 *
	 * @since 1.0.0
	 * @var Array
	 */
	private static $options;

	/**
	 * Main Customizer Instance.
	 *
	 * @since 1.0.0
	 * @return Customizer
	 */
	public static function instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Loads our Customizer custom controls.
		add_action( 'customize_register', array( $this, 'load_custom_controls' ) );

		// Loads our Customizer helper functions.
		add_action( 'customize_register', array( $this, 'load_customizer_helpers' ) );

		// Tweak inbuilt sections.
		add_action( 'customize_register', array( $this, 'customizer_tweak' ), 11 );

		// Registers our Customizer options.
		add_action( 'customize_register', array( $this, 'register_options_new' ) );

		// Loads our Customizer controls assets.
		add_action( 'customize_controls_enqueue_scripts', array( $this, 'load_assets' ), 10 );

		// Enqueues our Customizer preview assets.
		add_action( 'customize_preview_init', array( $this, 'load_preview_assets' ) );

		add_action( 'customize_controls_print_footer_scripts', array( 'Shopwell_Customizer_Control', 'template_units' ) );
	}

	/**
	 * Loads our Customizer custom controls.
	 *
	 * @since 1.0.0
	 * @param WP_Customize_Manager $customizer Instance of WP_Customize_Manager class.
	 */
	public function load_custom_controls( $customizer ) {

		// Directory where each custom control is located.
		$path = __DIR__ . '/controls/';

		// Require base control class.
		require $path . '/class-shopwell-customizer-control.php'; // phpcs:ignore

		$controls = $this->get_custom_controls();

		// Load custom controls classes.
		foreach ( $controls as $control => $class ) {
			$control_path = $path . '/' . $control . '/class-shopwell-customizer-control-' . $control . '.php';
			if ( file_exists( $control_path ) ) {
				require_once $control_path; // phpcs:ignore
				$customizer->register_control_type( $class );
			}
		}
	}

	/**
	 * Loads Customizer helper functions and sanitization callbacks.
	 *
	 * @since 1.0.0
	 */
	public function load_customizer_helpers() {
		require_once dirname(__FILE__) . '/customizer-callbacks.php'; // phpcs:ignore
		require_once dirname(__FILE__) . '/customizer-partials.php'; // phpcs:ignore
	}


	/**
	 * Move inbuilt panels into our sections.
	 *
	 * @since 1.0.0
	 * @param WP_Customize_Manager $customizer Instance of WP_Customize_Manager class.
	 */
	public static function customizer_tweak( $customizer ) {

		// Site Identity to Logo.
		$customizer->get_section( 'title_tagline' )->title = esc_html__( 'Logos &amp; Site Title', 'shopwell' );

		// Custom logo.
		if ( $customizer->get_control( 'custom_logo' ) ) {
			$customizer->get_control( 'custom_logo' )->description = esc_html__( 'Upload your logo image here.', 'shopwell' );
			$customizer->get_setting( 'custom_logo' )->transport   = 'postMessage';

			// Add selective refresh partial for Custom Logo.
			$customizer->selective_refresh->add_partial(
				'custom_logo',
				array(
					'selector'            => '.site-header .header-logo',
					'render_callback'     => '\Shopwell\Header\Main::instance()->render',
					'container_inclusive' => false,
					'fallback_refresh'    => true,
				)
			);
		}

		// Site title.
		if ( $customizer->get_control( 'blogname' ) ) {
			$customizer->get_setting( 'blogname' )->transport   = 'postMessage';
			$customizer->get_control( 'blogname' )->description = esc_html__( 'Enter the name of your site here.', 'shopwell' );
		}

		// Site description.
		if ( $customizer->get_control( 'blogdescription' ) ) {
			$customizer->get_setting( 'blogdescription' )->transport   = 'postMessage';
			$customizer->get_control( 'blogdescription' )->description = esc_html__( 'A tagline is a short phrase, or sentence, used to convey the essence of the site.', 'shopwell' );
		}

		$customizer->get_section( 'title_tagline' )->panel     = 'shopwell_general';
		$customizer->get_section( 'static_front_page' )->panel = 'shopwell_general';
	}


	/**
	 * Registers our Customizer options.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Customize_Manager $customizer instance of WP_Customize_Manager.
	 *
	 * @return void
	 */
	public function register_options_new( $customizer ) {

		$options = $this->get_customizer_options();

		if ( isset( $options['panels'] ) && ! empty( $options['panels'] ) ) {
			foreach ( $options['panels'] as $id => $args ) {
				$this->add_panel( $id, $args, $customizer );
			}
		}

		if ( isset( $options['sections'] ) && ! empty( $options['sections'] ) ) {
			foreach ( $options['sections'] as $id => $args ) {
				$this->add_section( $id, $args, $customizer );
			}
		}

		if ( isset( $options['settings'] ) && ! empty( $options['settings'] ) ) {
			foreach ( $options['settings'] as $section => $settings ) {
				foreach ( $settings as $id => $args ) {

					if ( strpos( $id, 'shopwell_' ) === false ) {
						$id = 'shopwell_' . $id;
					}

					if ( strpos( $section, 'shopwell_' ) === false ) {
						$section = 'shopwell_' . $section;
					}

					if ( empty( $args['section'] ) ) {
						$args['section'] = $section;
					}

					if ( empty( $args['settings'] ) ) {
						$args['settings'] = $id;
					}
					$this->add_setting( $id, $args, $customizer );
					$this->add_control( $id, $args, $customizer );
				}
			}
		}
	}

	/**
	 * Filter and return Customizer options.
	 *
	 * @since 1.0.0
	 *
	 * @return Array Customizer options for registering Sections/Panels/Controls.
	 */
	public function get_customizer_options() {
		if ( ! is_null( self::$options ) ) {
			return self::$options;
		}

		return apply_filters( 'shopwell_customizer_options', array() );
	}

	/**
	 * Register Customizer Panel
	 *
	 * @param string $id Panel id.
	 * @param Array  $args Panel settings.
	 * @param [type] $customizer instance of WP_Customize_Manager.
	 * @return void
	 */
	private function add_panel( $id, $args, $customizer ) {
		$class = \Shopwell\Helper::get_prop( $args, 'class', 'WP_Customize_Panel' );

		$customizer->add_panel( new $class( $customizer, $id, $args ) );
	}

	/**
	 * Register Customizer Section.
	 *
	 * @since 1.0.0
	 *
	 * @param string               $id Section id.
	 * @param Array                $args Section settings.
	 * @param WP_Customize_Manager $customizer instance of WP_Customize_Manager.
	 *
	 * @return void
	 */
	private function add_section( $id, $args, $customizer ) {
		$class = \Shopwell\Helper::get_prop( $args, 'class', 'WP_Customize_Section' );
		$customizer->add_section( new $class( $customizer, $id, $args ) );
	}

	/**
	 * Register Customizer Control.
	 *
	 * @since 1.0.0
	 *
	 * @param string               $id Control id.
	 * @param Array                $args Control settings.
	 * @param WP_Customize_Manager $customizer instance of WP_Customize_Manager.
	 *
	 * @return void
	 */
	private function add_control( $id, $args, $customizer ) {

		if ( isset( $args['class'] ) ) {
			$class = $args['class'];
		} else {
			$class = $this->get_control_class( \Shopwell\Helper::get_prop( $args, 'type' ) );
		}
		$args['setting'] = $id;
		if ( false !== $class ) {
			$customizer->add_control( new $class( $customizer, $id, $args ) );
		} else {
			$customizer->add_control( $id, $args );
		}
	}

	/**
	 * Register Customizer Setting.
	 *
	 * @since 1.0.0
	 * @param string               $id Control setting id.
	 * @param Array                $setting Settings.
	 * @param WP_Customize_Manager $customizer instance of WP_Customize_Manager.
	 *
	 * @return void
	 */
	private function add_setting( $id, $setting, $customizer ) {
		unset( $setting['type'] );
		$setting = wp_parse_args( $setting, $this->get_customizer_defaults( 'setting' ) );
		$default = \Shopwell\Helper::get_prop( $setting, 'default' );
		$customizer->add_setting(
			$id,
			array(
				'default'           => $default != null ? $default : \Shopwell\Helper::get_option_default( $id ),
				'type'              => \Shopwell\Helper::get_prop( $setting, 'type' ),
				'transport'         => \Shopwell\Helper::get_prop( $setting, 'transport' ),
				'sanitize_callback' => \Shopwell\Helper::get_prop( $setting, 'sanitize_callback', 'shopwell_no_sanitize' ),
			)
		);

		$partial = \Shopwell\Helper::get_prop( $setting, 'partial', false );

		if ( $partial && isset( $customizer->selective_refresh ) ) {

			$customizer->selective_refresh->add_partial(
				$id,
				array(
					'selector'            => \Shopwell\Helper::get_prop( $partial, 'selector' ),
					'container_inclusive' => \Shopwell\Helper::get_prop( $partial, 'container_inclusive' ),
					'render_callback'     => \Shopwell\Helper::get_prop( $partial, 'render_callback' ),
					'fallback_refresh'    => \Shopwell\Helper::get_prop( $partial, 'fallback_refresh' ),
				)
			);
		}
	}

	/**
	 * Return custom controls.
	 *
	 * @since 1.0.0
	 *
	 * @return Array custom control slugs & classnames.
	 */
	private function get_custom_controls() {
		return apply_filters(
			'shopwell_custom_customizer_controls',
			array(
				'toggle'              => 'Shopwell_Customizer_Control_Toggle',
				'select'              => 'Shopwell_Customizer_Control_Select',
				'heading'             => 'Shopwell_Customizer_Control_Heading',
				'color'               => 'Shopwell_Customizer_Control_Color',
				'range'               => 'Shopwell_Customizer_Control_Range',
				'radio'               => 'Shopwell_Customizer_Control_Radio',
				'spacing'             => 'Shopwell_Customizer_Control_Spacing',
				'widget'              => 'Shopwell_Customizer_Control_Widget',
				'radio-buttonset'     => 'Shopwell_Customizer_Control_Radio_Buttonset',
				'radio-image'         => 'Shopwell_Customizer_Control_Radio_Image',
				'background'          => 'Shopwell_Customizer_Control_Background',
				'image'               => 'Shopwell_Customizer_Control_Image',
				'text'                => 'Shopwell_Customizer_Control_Text',
				'number'              => 'Shopwell_Customizer_Control_Number',
				'textarea'            => 'Shopwell_Customizer_Control_Textarea',
				'typography'          => 'Shopwell_Customizer_Control_Typography',
				'button'              => 'Shopwell_Customizer_Control_Button',
				'sortable'            => 'Shopwell_Customizer_Control_Sortable',
				'info'                => 'Shopwell_Customizer_Control_Info',
				'design-options'      => 'Shopwell_Customizer_Control_Design_Options',
				'alignment'           => 'Shopwell_Customizer_Control_Alignment',
				'checkbox-group'      => 'Shopwell_Customizer_Control_Checkbox_Group',
				'repeater'            => 'Shopwell_Customizer_Control_Repeater',
				'editor'              => 'Shopwell_Customizer_Control_Editor',
				'generic-notice'      => 'Shopwell_Customizer_Control_Generic_Notice',
				'gallery'             => 'Shopwell_Customizer_Control_Gallery',
				'datetime'            => 'Shopwell_Customizer_Control_Datetime',
				'section-group-title' => 'Shopwell_Customizer_Control_Section_Group_Title',
				'section-pro' 		  => 'Shopwell_Customizer_Control_Section_Pro',
			)
		);
	}

	/**
	 * Return default values for customizer parts.
	 *
	 * @param  String $type setting or control.
	 * @return Array  default values for the Customizer Configurations.
	 */
	private function get_customizer_defaults( $type ) {

		$defaults = array();

		switch ( $type ) {
			case 'setting':
				$defaults = array(
					'type'      => 'theme_mod',
					'transport' => 'refresh',
				);
				break;

			case 'control':
				$defaults = array();
				break;

			default:
				break;
		}

		return apply_filters(
			'shopwell_customizer_configuration_defaults',
			$defaults,
			$type
		);
	}

	/**
	 * Get custom control classname.
	 *
	 * @since 1.0.0
	 *
	 * @param string $type Control ID.
	 *
	 * @return string Control classname.
	 */
	private function get_control_class( $type ) {

		if ( false !== strpos( $type, 'shopwell-' ) ) {

			$controls = $this->get_custom_controls();
			$type     = trim( str_replace( 'shopwell-', '', $type ) );
			if ( isset( $controls[ $type ] ) ) {
				return $controls[ $type ];
			}
		}

		return false;
	}

	/**
	 * Loads our own Customizer assets.
	 *
	 * @since 1.0.0
	 */
	public function load_assets() {

		// Script debug.
		$shopwell_dir    = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? 'dev/' : '';
		$shopwell_suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		/**
		 * Enqueue our Customizer styles.
		 */
		wp_enqueue_style(
			'shopwell-customizer-styles',
			SHOPWELL_THEME_URI . '/inc/customizer/assets/css/shopwell-customizer' . $shopwell_suffix . '.css',
			false,
			SHOPWELL_THEME_VERSION
		);

		/**
		 * Enqueue our Customizer controls script.
		 */
		wp_enqueue_script(
			'shopwell-customizer-js',
			SHOPWELL_THEME_URI . '/inc/customizer/assets/js/' . $shopwell_dir . 'customize-controls' . $shopwell_suffix . '.js',
			array( 'wp-color-picker', 'jquery', 'customize-base' ),
			SHOPWELL_THEME_VERSION,
			true
		);

		/**
		 * Enqueue Customizer controls dependency script.
		 */
		wp_enqueue_script(
			'shopwell-control-dependency-js',
			SHOPWELL_THEME_URI . '/inc/customizer/assets/js/' . $shopwell_dir . 'customize-dependency' . $shopwell_suffix . '.js',
			array( 'jquery' ),
			SHOPWELL_THEME_VERSION,
			true
		);

		/**
		 * Localize JS variables
		 */
		$shopwell_customizer_localized = array(
			'ajaxurl'                 => admin_url( 'admin-ajax.php' ),
			'wpnonce'                 => wp_create_nonce( 'shopwell_customizer' ),
			'color_palette'           => array( '#ffffff', '#000000', '#e4e7ec', '#0068c8', '#f7b40b', '#e04b43', '#30373e', '#8a63d4' ),
			'preview_url_for_section' => $this->get_preview_urls_for_section(),
			'strings'                 => array(
				'selectCategory' => esc_html__( 'Select a category', 'shopwell' ),
			),
		);

		/**
		 * Allow customizer localized vars to be filtered.
		 */
		$shopwell_customizer_localized = apply_filters( 'shopwell_customizer_localized', $shopwell_customizer_localized );

		wp_localize_script(
			'shopwell-customizer-js',
			'shopwell_customizer_localized',
			$shopwell_customizer_localized
		);
	}

	/**
	 * Loads customizer preview assets
	 *
	 * @since 1.0.0
	 */
	public function load_preview_assets() {

		// Script debug.
		$shopwell_dir    = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? 'dev/' : '';
		$shopwell_suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		$version         = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? time() : SHOPWELL_THEME_VERSION;

		wp_enqueue_script(
			'shopwell-customizer-preview-js',
			SHOPWELL_THEME_URI . '/inc/customizer/assets/js/' . $shopwell_dir . 'customize-preview' . $shopwell_suffix . '.js',
			array( 'customize-preview', 'customize-selective-refresh', 'jquery' ),
			$version,
			true
		);

		// Enqueue Customizer preview styles.
		wp_enqueue_style(
			'shopwell-customizer-preview-styles',
			SHOPWELL_THEME_URI . '/inc/customizer/assets/css/shopwell-customizer-preview' . $shopwell_suffix . '.css',
			false,
			SHOPWELL_THEME_VERSION
		);

		/**
		 * Localize JS variables.
		 */
		$shopwell_customizer_localized = array(
			'default_system_font' => \Shopwell\Helper::fonts()->get_default_system_font(),
			'fonts'               => \Shopwell\Helper::fonts()->get_fonts(),
			'google_fonts_url'    => '//fonts.googleapis.com',
			'google_font_weights' => '100,100i,200,200i,300,300i,400,400i,500,500i,600,600i,700,700i,800,800i,900,900i',
		);

		/**
		 * Allow customizer localized vars to be filtered.
		 */
		$shopwell_customizer_localized = apply_filters( 'shopwell_customize_preview_localized', $shopwell_customizer_localized );

		wp_localize_script(
			'shopwell-customizer-preview-js',
			'shopwell_customizer_preview',
			$shopwell_customizer_localized
		);
	}


	/**
	 * Get preview URL for a section. The URL will load when the section is opened.
	 *
	 * @return string
	 */
	public function get_preview_urls_for_section() {

		$return = array();

		// Preview a random single post for Single Post section.
		$posts = get_posts(
			array(
				'post_type'      => 'post',
				'posts_per_page' => 1,
				'orderby'        => 'rand',
			)
		);

		if ( count( $posts ) ) {
			$return['shopwell_section_blog_single_post'] = get_permalink( $posts[0] );
		}

		// Preview blog page.
		$return['shopwell_section_blog_page'] = \Shopwell\Helper::get_blog_url();

		return $return;
	}
}
