<?php

/**
 * Theme Settings
 *
 * @package Shopwell
 */

namespace Shopwell\Customizer;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Settings {


	/**
	 * Instance
	 *
	 * @var $instance
	 */
	protected static $instance = null;

	/**
	 * $shopwell_customize
	 *
	 * @var $shopwell_customize
	 */
	protected static $shopwell_customize = null;

	/**
	 * Initiator
	 *
	 * @return object
	 * @since 1.0.0
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * The class constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_filter( 'shopwell_customizer_options', array( $this, 'customize_settings' ) );
		self::$shopwell_customize = \Shopwell\Customizer\Customizer::instance();
	}

	/**
	 * Options of topbar items
	 *
	 * @return array
	 */
	public static function topbar_items_option() {
		return apply_filters(
			'shopwell_topbar_items_option',
			array(
				''                  => esc_html__( 'Select an Item', 'shopwell' ),
				'primary-menu'      => esc_html__( 'Primary Menu', 'shopwell' ),
				'secondary-menu'    => esc_html__( 'Secondary Menu', 'shopwell' ),
				'language'          => esc_html__( 'Language', 'shopwell' ),
				'currency'          => esc_html__( 'Currency', 'shopwell' ),
				'language-currency' => esc_html__( 'Language/Currency', 'shopwell' ),
				'socials'           => esc_html__( 'Socials', 'shopwell' ),
				'hamburger'         => esc_html__( 'Hamburger Menu', 'shopwell' ),
			)
		);
	}

	/**
	 *
	 *
	 * @retun array
	 */
	/**
	 * Header variation options
	 *
	 * @param $include_default boolean
	 * @return mixed|array
	 */
	public function header_options( $include_default = true ) {
		$variations = apply_filters(
			'shopwell_header_options',
			array(
				''    => esc_html__( 'Default', 'shopwell' ),
				'v11' => esc_html__( 'Header V1', 'shopwell' ),
				'v1'  => esc_html__( 'Header V2', 'shopwell' ),
			)
		);

		if ( ! $include_default ) {
			array_shift( $variations );
		}

		return $variations;
	}

	/**
	 * Options of header items
	 *
	 * @return array
	 */
	public static function header_items_option() {
		return apply_filters(
			'shopwell_header_items_option',
			array(
				''               => esc_html__( 'Select an Item', 'shopwell' ),
				'logo'           => esc_html__( 'Logo', 'shopwell' ),
				'cart'           => esc_html__( 'Cart', 'shopwell' ),
				'wishlist'       => esc_html__( 'Wishlist', 'shopwell' ),
				'compare'        => esc_html__( 'Compare', 'shopwell' ),
				'account'        => esc_html__( 'Account', 'shopwell' ),
				'search'         => esc_html__( 'Search', 'shopwell' ),
				'primary-menu'   => esc_html__( 'Primary Menu', 'shopwell' ),
				'secondary-menu' => esc_html__( 'Secondary Menu', 'shopwell' ),
				'category-menu'  => esc_html__( 'Category Menu', 'shopwell' ),
				'hamburger'      => esc_html__( 'Hamburger Menu', 'shopwell' ),
				'socials'        => esc_html__( 'Socials', 'shopwell' ),
				'return'         => esc_html__( 'Return Button', 'shopwell' ),
				'custom-text'    => esc_html__( 'Custom Text', 'shopwell' ),
				'empty-space'    => esc_html__( 'Empty Space', 'shopwell' ),
				'language'       => esc_html__( 'Language', 'shopwell' ),
				'currency'       => esc_html__( 'Currency', 'shopwell' ),
				'preferences'    => esc_html__( 'Preferences', 'shopwell' ),
				'view-history'   => esc_html__( 'View History', 'shopwell' ),
			)
		);
	}

	/**
	 * Options of header mobile items
	 *
	 * @return array
	 */
	public static function header_mobile_items_option() {
		return apply_filters(
			'shopwell_header_mobile_items_option',
			array(
				''             => esc_html__( 'Select an Item', 'shopwell' ),
				'logo'         => esc_html__( 'Logo', 'shopwell' ),
				'cart'         => esc_html__( 'Cart', 'shopwell' ),
				'wishlist'     => esc_html__( 'Wishlist', 'shopwell' ),
				'compare'      => esc_html__( 'Compare', 'shopwell' ),
				'account'      => esc_html__( 'Account', 'shopwell' ),
				'hamburger'    => esc_html__( 'Hamburger Menu', 'shopwell' ),
				'search'       => esc_html__( 'Search', 'shopwell' ),
				'language'     => esc_html__( 'Language', 'shopwell' ),
				'currency'     => esc_html__( 'Currency', 'shopwell' ),
				'preferences'  => esc_html__( 'Preferences', 'shopwell' ),
				'view-history' => esc_html__( 'View History', 'shopwell' ),
			)
		);
	}


	/**
	 * Get customize settings
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function customize_settings() {
		$theme = wp_get_theme();

		$settings = array(
			'theme' => 'shopwell',
		);

		$panels = array(
			'shopwell_general'    => array(
				'priority' => 10,
				'title'    => esc_html__( 'General', 'shopwell' ),
			),
			'shopwell_typography' => array(
				'priority' => 20,
				'title'    => esc_html__( 'Typography', 'shopwell' ),
			),
			'shopwell_header'     => array(
				'priority' => 30,
				'title'    => esc_html__( 'Header', 'shopwell' ),
			),
			'shopwell_blog'       => array(
				'priority' => 50,
				'title'    => esc_html__( 'Blog', 'shopwell' ),
			),
			'shopwell_page'       => array(
				'priority' => 50,
				'title'    => esc_html__( 'Page', 'shopwell' ),
			),
			'shopwell_mobile'     => array(
				'priority' => 90,
				'title'    => esc_html__( 'Mobile', 'shopwell' ),
			)
		);

		$sections = array(
			'shopwell_styling'               => array(
				'priority'   => 10,
				'title'      => esc_html__( 'Styling', 'shopwell' ),
				'capability' => 'edit_theme_options',
			),
			'shopwell_api_keys'              => array(
				'title' => esc_html__( 'API Keys', 'shopwell' ),
				'panel' => 'shopwell_general',
			),
			'shopwell_backtotop'             => array(
				'title' => esc_html__( 'Back To Top', 'shopwell' ),
				'panel' => 'shopwell_general',
			),
			'shopwell_share_socials'         => array(
				'title' => esc_html__( 'Share Socials', 'shopwell' ),
				'panel' => 'shopwell_general',
			),
			'shopwell_typo_main'             => array(
				'title' => esc_html__( 'Main', 'shopwell' ),
				'panel' => 'shopwell_typography',
			),
			'shopwell_typo_headings'         => array(
				'title' => esc_html__( 'Headings', 'shopwell' ),
				'panel' => 'shopwell_typography',
			),
			'shopwell_typo_header_logo'      => array(
				'title' => esc_html__( 'Header Logo Text', 'shopwell' ),
				'panel' => 'shopwell_typography',
			),
			'shopwell_header_top'            => array(
				'title' => esc_html__( 'Topbar', 'shopwell' ),
				'panel' => 'shopwell_header',
			),
			'shopwell_header_layout'         => array(
				'title' => esc_html__( 'Header Layout', 'shopwell' ),
				'panel' => 'shopwell_header',
			),
			'shopwell_header_main'           => array(
				'title' => esc_html__( 'Header Main', 'shopwell' ),
				'panel' => 'shopwell_header',
			),
			'shopwell_header_bottom'         => array(
				'title' => esc_html__( 'Header Bottom', 'shopwell' ),
				'panel' => 'shopwell_header',
			),
			'shopwell_header_sticky'         => array(
				'title' => esc_html__( 'Sticky Header', 'shopwell' ),
				'panel' => 'shopwell_header',
			),
			'shopwell_header_background'     => array(
				'title' => esc_html__( 'Header Background', 'shopwell' ),
				'panel' => 'shopwell_header',
			),
			'shopwell_header_campaign'       => array(
				'title' => esc_html__( 'Campaign Bar', 'shopwell' ),
				'panel' => 'shopwell_header',
			),
			'shopwell_logo'                  => array(
				'title' => esc_html__( 'Logo', 'shopwell' ),
				'panel' => 'shopwell_header',
			),
			'shopwell_header_search'         => array(
				'title' => esc_html__( 'Search', 'shopwell' ),
				'panel' => 'shopwell_header',
			),
			'shopwell_header_account'        => array(
				'title' => esc_html__( 'Account', 'shopwell' ),
				'panel' => 'shopwell_header',
			),
			'shopwell_header_wishlist'       => array(
				'title' => esc_html__( 'Wishlist', 'shopwell' ),
				'panel' => 'shopwell_header',
			),
			'shopwell_header_compare'        => array(
				'title' => esc_html__( 'Compare', 'shopwell' ),
				'panel' => 'shopwell_header',
			),
			'shopwell_header_cart'           => array(
				'title' => esc_html__( 'Cart', 'shopwell' ),
				'panel' => 'shopwell_header',
			),
			'shopwell_header_hamburger'      => array(
				'title' => esc_html__( 'Hamburger', 'shopwell' ),
				'panel' => 'shopwell_header',
			),
			'shopwell_header_primary_menu'   => array(
				'title' => esc_html__( 'Primary Menu', 'shopwell' ),
				'panel' => 'shopwell_header',
			),
			'shopwell_header_secondary_menu' => array(
				'title' => esc_html__( 'Secondary Menu', 'shopwell' ),
				'panel' => 'shopwell_header',
			),
			'shopwell_header_category_menu'  => array(
				'title' => esc_html__( 'Category Menu', 'shopwell' ),
				'panel' => 'shopwell_header',
			),
			'shopwell_header_preferences'    => array(
				'title' => esc_html__( 'Preferences', 'shopwell' ),
				'panel' => 'shopwell_header',
			),
			'shopwell_header_view_history'   => array(
				'title' => esc_html__( 'View History', 'shopwell' ),
				'panel' => 'shopwell_header',
			),
			'shopwell_header_custom_text'    => array(
				'title' => esc_html__( 'Custom Text', 'shopwell' ),
				'panel' => 'shopwell_header',
			),
			'shopwell_header_empty_space'    => array(
				'title' => esc_html__( 'Empty Space', 'shopwell' ),
				'panel' => 'shopwell_header',
			),
			'shopwell_header_return_button'  => array(
				'title' => esc_html__( 'Return Button', 'shopwell' ),
				'panel' => 'shopwell_header',
			),

			// Footer
			'shopwell_footer_layout'         => array(
				'title'      => esc_html__( 'Footer', 'shopwell' ),
				'capability' => 'edit_theme_options',
				'priority'   => 45,
			),

			// Blog
			'shopwell_blog_prebuilt_header'  => array(
				'title' => esc_html__( 'Prebuilt Header', 'shopwell' ),
				'panel' => 'shopwell_blog',
			),
			'shopwell_blog_header'           => array(
				'title' => esc_html__( 'Blog Header', 'shopwell' ),
				'panel' => 'shopwell_blog',
			),
			'shopwell_blog_archive'          => array(
				'title' => esc_html__( 'Blog Archive', 'shopwell' ),
				'panel' => 'shopwell_blog',
			),
			'shopwell_blog_single'           => array(
				'title' => esc_html__( 'Blog Single', 'shopwell' ),
				'panel' => 'shopwell_blog',
			),

			// Page
			'shopwell_page_prebuilt_header'  => array(
				'title'       => esc_html__( 'Prebuilt Header', 'shopwell' ),
				'description' => '',
				'priority'    => 10,
				'capability'  => 'edit_theme_options',
				'panel'       => 'shopwell_page',
			),
			// Page
			'shopwell_page_header'           => array(
				'title'       => esc_html__( 'Page Header', 'shopwell' ),
				'description' => '',
				'priority'    => 10,
				'capability'  => 'edit_theme_options',
				'panel'       => 'shopwell_page',
			),

			// RTL
			'shopwell_rtl'                   => array(
				'title' => esc_html__( 'RTL', 'shopwell' ),
				'panel' => 'shopwell_general',
			),

			'shopwell_section_upsell_button' => array(
				'class'    => 'Shopwell_Customizer_Control_Section_Pro',
				'title'    => esc_html__( 'Need more features?', 'shopwell' ),
				'pro_url'  => sprintf( esc_url_raw( 'https://peregrine-themes.com/%s' ), strtolower( $theme->name ) ),
				'pro_text' => esc_html__( 'Upgrade to pro', 'shopwell' ),
				'priority' => 200,
			),
			'shopwell_section_docs_button' => array(
				'class'    => 'Shopwell_Customizer_Control_Section_Pro',
				'title'    => esc_html__( 'Need Help?', 'shopwell' ),
				'pro_url'  => esc_url_raw( 'http://docs.peregrine-themes.com/docs-category/shopwell-pro/' ),
				'pro_text' => esc_html__( 'See the docs', 'shopwell' ),
				'priority' => 200,
			),
		);

		if( class_exists( \Shopwell\Addons::class ) ){
			unset( $sections['shopwell_section_upsell_button'] );
		}else{
			$sections['shopwell_header_mobile_layout'] = array(
				'title' => esc_html__('Header Layout', 'shopwell'),
				'panel' => 'shopwell_mobile',
			);
		}
		$settings = array();

		$settings['shopwell_page_prebuilt_header'] = array(
			'shopwell_page_header_version' => array(
				'transport'         => 'refresh',
				'sanitize_callback' => 'shopwell_sanitize_select',
				'type'              => 'shopwell-select',
				'label'             => esc_html__( 'Prebuilt Header', 'shopwell' ),
				'description'       => esc_html__( 'Select a prebuilt header for pages', 'shopwell' ),
				'choices'           => $this->header_options(),
				'priority'          => 20,
			),
			'shopwell_page_primary_menu'   => array(
				'transport'         => 'refresh',
				'sanitize_callback' => 'shopwell_sanitize_select',
				'type'              => 'shopwell-select',
				'label'             => esc_html__( 'Primary Menu', 'shopwell' ),
				'choices'           => $this->get_menus(),
				'priority'          => 25,

			),
		);

		$settings['shopwell_page_header'] = array(
			'shopwell_page_header'     => array(
				'sanitize_callback' => 'shopwell_sanitize_toggle',
				'type'              => 'shopwell-toggle',
				'label'             => esc_html__( 'Enable Page Header', 'shopwell' ),
				'description'       => esc_html__( 'Enable to show a page header for the page below the site header', 'shopwell' ),
				'priority'          => 10,
			),
			'shopwell_page_header_els' => array(
				'sanitize_callback' => 'shopwell_no_sanitize',
				'type'              => 'shopwell-checkbox-group',
				'label'             => esc_html__( 'Page Header Elements', 'shopwell' ),
				'priority'          => 10,
				'choices'           => array(
					'breadcrumb' => array(
						'title' => esc_html__( 'BreadCrumb', 'shopwell' ),
					),
					'title'      => array(
						'title' => esc_html__( 'Title', 'shopwell' ),
					),
				),
				'description'       => esc_html__( 'Select which elements you want to show.', 'shopwell' ),
				'required'          => array(
					array(
						'control'  => 'shopwell_page_header',
						'operator' => '==',
						'value'    => true,
					),
				),

			),
		);

		// Header mobile.
        $settings['shopwell_header_mobile_layout'] = array(
            'shopwell_header_mobile_version'              => array(
                'sanitize_callback' => 'shopwell_sanitize_select',
                'type'              => 'shopwell-select',
                'label'             => esc_html__( 'Prebuilt Header', 'shopwell' ),
                'choices'           => $this->header_options(false),
                'required'          => array(
                    array(
                        'control'  => 'shopwell_header_mobile_present',
                        'operator' => '==',
                        'value'    => 'prebuild',
                    ),
                ),
            ),
		);

		$settings['title_tagline'] = array(
			'shopwell_logo_width'      => array(
				'sanitize_callback' => 'shopwell_sanitize_range',
				'type'              => 'shopwell-range',
				'label'             => esc_html__( 'Logo Width', 'shopwell' ),
				'section'           => 'title_tagline',
				'min'               => 0,
				'unit'              => 'px',
				'required'          => array(
					array(
						'control'  => 'shopwell_logo_type',
						'operator' => '!=',
						'value'    => 'text',
					),
				),
			),
			'shopwell_logo_height'     => array(
				'sanitize_callback' => 'shopwell_sanitize_range',
				'type'              => 'shopwell-range',
				'label'             => esc_html__( 'Logo Height', 'shopwell' ),
				'section'           => 'title_tagline',
				'min'               => 0,
				'unit'              => 'px',
				'required'          => array(
					array(
						'control'  => 'shopwell_logo_type',
						'operator' => '!=',
						'value'    => 'text',
					),
				),
			),
			'shopwell_display_tagline' => array(
				'transport'         => 'postMessage',
				'sanitize_callback' => 'shopwell_sanitize_toggle',
				'type'              => 'shopwell-toggle',
				'label'             => esc_html__( 'Display Tagline', 'shopwell' ),
				'section'           => 'title_tagline',
				'priority'          => 50,
				'partial'           => array(
					'selector'            => '.shopwell-logo',
					'render_callback'     => 'shopwell_logo',
					'container_inclusive' => false,
					'fallback_refresh'    => true,
				),
			),
		);

		$settings['shopwell_styling'] = array(
			'shopwell_shape_style'               => array(
				'sanitize_callback' => 'shopwell_sanitize_radio',
				'type'              => 'shopwell-radio-buttonset',
				'label'             => esc_html__( 'Shape', 'shopwell' ),
				'choices'           => array(
					'default' => esc_html__( 'Default', 'shopwell' ),
					'sharp'   => esc_html__( 'Sharp', 'shopwell' ),
					'smooth'  => esc_html__( 'Smooth', 'shopwell' ),
					'round'   => esc_html__( 'Round', 'shopwell' ),
					'circle'  => esc_html__( 'Circle', 'shopwell' ),
				),
			),
			'shopwell_primary_custom_color'      => array(
				'sanitize_callback' => 'shopwell_sanitize_color',
				'type'              => 'shopwell-color',
				'label'             => esc_html__( 'Primary Color', 'shopwell' ),
			),
			'shopwell_primary_text_color'        => array(
				'sanitize_callback' => 'shopwell_sanitize_select',
				'type'              => 'shopwell-select',
				'label'             => esc_html__( 'Text on Primary Color', 'shopwell' ),
				'choices'           => array(
					'light'  => esc_html__( 'Light', 'shopwell' ),
					'dark'   => esc_html__( 'Dark', 'shopwell' ),
					'custom' => esc_html__( 'Custom', 'shopwell' ),
				),
			),
			'shopwell_primary_text_custom_color' => array(
				'sanitize_callback' => 'shopwell_sanitize_color',
				'type'              => 'shopwell-color',
				'label'             => esc_html__( 'Custom Color', 'shopwell' ),
				'required'          => array(
					array(
						'control'  => 'shopwell_primary_text_color',
						'operator' => '==',
						'value'    => 'custom',
					),
				),
			),
		);

		// Back To Top.
		$settings['shopwell_backtotop'] = array(
			'shopwell_backtotop' => array(
				'sanitize_callback' => 'shopwell_sanitize_toggle',
				'type'              => 'shopwell-toggle',
				'label'             => esc_html__( 'Back To Top', 'shopwell' ),
				'description'       => esc_html__( 'Check this to show back to top.', 'shopwell' ),
			),
		);

		// Typography - body.
		$settings['shopwell_typo_main'] = array(
			'shopwell_typo_body_heading' => array(
				'transport'         => 'postMessage',
				'sanitize_callback' => 'shopwell_sanitize_toggle',
				'type'              => 'shopwell-heading',
				'label'             => esc_html__( 'Body & Content', 'shopwell' ),
			),
			'shopwell_typo_body'         => array(
				'sanitize_callback' => 'shopwell_sanitize_typography',
				'transport'         => 'postMessage',
				'type'              => 'shopwell-typography',
				'label'             => esc_html__( 'Body', 'shopwell' ),
				'description'       => esc_html__( 'Customize the body font', 'shopwell' ),
				'required'          => array(
					array(
						'control'  => 'shopwell_typo_body_heading',
						'value'    => true,
						'operator' => '==',
					),
				),
			),
			// Headings
			'shopwell_typo_h_heading'    => array(
				'transport'         => 'postMessage',
				'sanitize_callback' => 'shopwell_sanitize_toggle',
				'type'              => 'shopwell-heading',
				'label'             => esc_html__( 'HEADINGS (H1 - H6)', 'shopwell' ),

			),
			'shopwell_typo_h1'           => array(
				'sanitize_callback' => 'shopwell_sanitize_typography',
				'transport'         => 'postMessage',
				'type'              => 'shopwell-typography',
				'label'             => esc_html__( 'Heading 1', 'shopwell' ),
				'description'       => esc_html__( 'Customize the H1 font', 'shopwell' ),
				'required'          => array(
					array(
						'control'  => 'shopwell_typo_h_heading',
						'value'    => true,
						'operator' => '==',
					),
				),

			),
			'shopwell_typo_h2'           => array(
				'sanitize_callback' => 'shopwell_sanitize_typography',
				'transport'         => 'postMessage',
				'type'              => 'shopwell-typography',
				'label'             => esc_html__( 'Heading 2', 'shopwell' ),
				'description'       => esc_html__( 'Customize the H2 font', 'shopwell' ),
				'required'          => array(
					array(
						'control'  => 'shopwell_typo_h_heading',
						'value'    => true,
						'operator' => '==',
					),
				),
			),
			'shopwell_typo_h3'           => array(
				'sanitize_callback' => 'shopwell_sanitize_typography',
				'transport'         => 'postMessage',
				'type'              => 'shopwell-typography',
				'label'             => esc_html__( 'Heading 3', 'shopwell' ),
				'description'       => esc_html__( 'Customize the H3 font', 'shopwell' ),
				'required'          => array(
					array(
						'control'  => 'shopwell_typo_h_heading',
						'value'    => true,
						'operator' => '==',
					),
				),
			),
			'shopwell_typo_h4'           => array(
				'sanitize_callback' => 'shopwell_sanitize_typography',
				'transport'         => 'postMessage',
				'type'              => 'shopwell-typography',
				'label'             => esc_html__( 'Heading 4', 'shopwell' ),
				'description'       => esc_html__( 'Customize the H4 font', 'shopwell' ),
				'required'          => array(
					array(
						'control'  => 'shopwell_typo_h_heading',
						'value'    => true,
						'operator' => '==',
					),
				),
			),
			'shopwell_typo_h5'           => array(
				'sanitize_callback' => 'shopwell_sanitize_typography',
				'transport'         => 'postMessage',
				'type'              => 'shopwell-typography',
				'label'             => esc_html__( 'Heading 5', 'shopwell' ),
				'description'       => esc_html__( 'Customize the H5 font', 'shopwell' ),
				'required'          => array(
					array(
						'control'  => 'shopwell_typo_h_heading',
						'value'    => true,
						'operator' => '==',
					),
				),
			),
			'shopwell_typo_h6'           => array(
				'sanitize_callback' => 'shopwell_sanitize_typography',
				'transport'         => 'postMessage',
				'type'              => 'shopwell-typography',
				'label'             => esc_html__( 'Heading 6', 'shopwell' ),
				'description'       => esc_html__( 'Customize the H6 font', 'shopwell' ),
				'required'          => array(
					array(
						'control'  => 'shopwell_typo_h_heading',
						'value'    => true,
						'operator' => '==',
					),
				),
			),
		);

		// Typography - header primary menu.
		$settings['shopwell_typo_header_logo'] = array(
			'shopwell_logo_font' => array(
				'sanitize_callback' => 'shopwell_sanitize_typography',
				'transport'         => 'postMessage',
				'type'              => 'shopwell-typography',
				'label'             => esc_html__( 'Logo Font', 'shopwell' ),
				'display'           => array(
					'font-family'    => array(),
					'font-size'      => array(),
					'font-subsets'   => array(),
					'font-weight'    => array(),
					'font-style'     => array(),
					'text-transform' => array(),
				),
			),
		);

		// Header layout settings
		$settings['shopwell_header_layout'] = array(
			'shopwell_header_version'                => array(
				'sanitize_callback' => 'shopwell_sanitize_select',
				'type'              => 'shopwell-select',
				'label'             => esc_html__( 'Prebuilt Header', 'shopwell' ),
				'description'       => esc_html__( 'Select a prebuilt header present', 'shopwell' ),
				'choices'           => $this->header_options( false ),
				'priority'          => 10,
				'required'          => array(
					array(
						'control'  => 'shopwell_header_present',
						'operator' => '==',
						'value'    => 'prebuild',
					),
				),
			),
			'shopwell_header_navigation_cutoff'      => array(
				'sanitize_callback' => 'shopwell_sanitize_toggle',
				'type'              => 'shopwell-toggle',
				'label'             => esc_html__( 'Enable menu cutoff', 'shopwell' ),
				'priority'          => 75,
				'required'          => array(
					array(
						'control'  => 'shopwell_header_present',
						'operator' => '==',
						'value'    => 'prebuild',
					),
				),
			),
			'shopwell_header_navigation_cutoff_upto' => array(
				'sanitize_callback' => 'shopwell_sanitize_range',
				'type'              => 'shopwell-range',
				'label'             => esc_html__( 'Menu cutoff up to', 'shopwell' ),
				'min'               => 4,
				'max'               => 15,
				'unit'              => '',
				'priority'          => 75,
				'required'          => array(
					array(
						'control'  => 'shopwell_header_present',
						'operator' => '==',
						'value'    => 'prebuild',
					),
					array(
						'control'  => 'shopwell_header_navigation_cutoff',
						'operator' => '==',
						'value'    => true,
					),
				),
			),
		);

		// Header bottom settings ( sticky ).
		$settings['shopwell_header_sticky'] = array(
			'shopwell_header_sticky' => array(
				'sanitize_callback' => 'shopwell_sanitize_select',
				'type'              => 'shopwell-select',
				'label'             => esc_html__( 'Sticky Header', 'shopwell' ),
				'choices'           => array(
					'none'   => esc_html__( 'No sticky', 'shopwell' ),
					'normal' => esc_html__( 'Sticky', 'shopwell' ),
				),
			),
		);

		// Header Background
		$settings['shopwell_header_background'] = array(
			'shopwell_header_custom_background_color'      => array(
				'sanitize_callback' => 'shopwell_sanitize_color',
				'transport'         => 'postMessage',
				'type'              => 'shopwell-color',
				'label'             => esc_html__( 'Background Color', 'shopwell' ),
			),

			'shopwell_header_custom_background_text_color' => array(
				'sanitize_callback' => 'shopwell_sanitize_color',
				'transport'         => 'postMessage',
				'type'              => 'shopwell-color',
				'label'             => esc_html__( 'Text Color', 'shopwell' ),
			),

			'shopwell_header_custom_background_border_color' => array(
				'sanitize_callback' => 'shopwell_sanitize_color',
				'transport'         => 'postMessage',
				'type'              => 'shopwell-color',
				'label'             => esc_html__( 'Border Color', 'shopwell' ),
			),

		);

		// Campaign bar.
		$settings['shopwell_header_campaign'] = array(
			'shopwell_campaign_bar'   => array(
				'sanitize_callback' => 'shopwell_sanitize_toggle',
				'label'             => esc_html__( 'Campaign Bar', 'shopwell' ),
				'description'       => esc_html__( 'Display a bar before, after the site header.', 'shopwell' ),
				'type'              => 'shopwell-toggle',
				'priority'          => 0,
			),
			'shopwell_campaign_items' => array(
				'sanitize_callback' => 'shopwell_repeater_sanitize',
				'type'              => 'shopwell-repeater',
				'label'             => esc_html__( 'Campaign Items', 'shopwell' ),
				'live_title_id'     => 'text',
				'title_format'      => esc_html__( '[live_title]', 'shopwell' ),
				'add_text'          => esc_html__( 'Add Campaign', 'shopwell' ),
				'limited_msg'       => wp_kses_post(
					sprintf(
						__( 'Upgrade to %s to add more items and unlock additional premium features!', 'shopwell' ),
						'<a target="_blank" href="https://peregrine-themes.com/shopwell/?utm_medium=customizer&utm_source=campaign_bar&utm_campaign=upgradeToPro">Shopwell Pro</a>'
					)
				),

				'max_item'          => 1,
				'fields'            => array(
					'icon' => array(
						'type'  => 'textarea',
						'title' => esc_html__( 'Icon', 'shopwell' ),
					),
					'text' => array(
						'type'  => 'textarea',
						'title' => esc_html__( 'Text', 'shopwell' ),
					),
					'link' => array(
						'type'  => 'link',
						'title' => esc_html__( 'Link', 'shopwell' ),
					),
				),
				'required'          => array(
					array(
						'control'  => 'shopwell_campaign_bar',
						'operator' => '==',
						'value'    => true,
					),
				),
				'priority'          => 5,
			),

			'shopwell_campaign_bg'    => array(
				'sanitize_callback' => 'shopwell_sanitize_design_options',
				'transport'         => 'postMessage',
				'type'              => 'shopwell-design-options',
				'label'             => esc_html__( 'Background', 'shopwell' ),
				'display'           => array(
					'background' => array(
						'color'    => esc_html__( 'Solid Color', 'shopwell' ),
						'gradient' => esc_html__( 'Gradient', 'shopwell' ),
						'image'    => esc_html__( 'Image', 'shopwell' ),
					),
				),
				'required'          => array(
					array(
						'control'  => 'shopwell_campaign_bar',
						'operator' => '==',
						'value'    => true,
					),
				),
				'priority'          => 15,
			),

			'shopwell_campaign_color' => array(
				'sanitize_callback' => 'shopwell_sanitize_design_options',
				'transport'         => 'postMessage',
				'type'              => 'shopwell-design-options',
				'label'             => esc_html__( 'Font Color', 'shopwell' ),
				'display'           => array(
					'color' => array(
						'text-color'       => esc_html__( 'Text Color', 'shopwell' ),
						'link-color'       => esc_html__( 'Link Color', 'shopwell' ),
						'link-hover-color' => esc_html__( 'Link Hover Color', 'shopwell' ),
					),
				),
				'required'          => array(
					array(
						'control'  => 'shopwell_campaign_bar',
						'operator' => '==',
						'value'    => true,
					),
				),
				'priority'          => 20,
			),
		);

		// Header search.
		$settings['shopwell_header_search'] = array(
			'shopwell_header_search_bar'       => array(
				'sanitize_callback' => 'shopwell_sanitize_select',
				'type'              => 'shopwell-select',
				'label'             => esc_html__( 'Search Bar', 'shopwell' ),
				'choices'           => array(
					'default'   => esc_html__( 'Default of the theme', 'shopwell' ),
					'shortcode' => esc_html__( 'Using a shortcode', 'shopwell' ),
				),
				'priority'          => 10,
			),
			'shopwell_header_search_shortcode' => array(
				'sanitize_callback' => 'sanitize_text_field',
				'type'              => 'shopwell-text',
				'label'             => esc_html__( 'Shortcode', 'shopwell' ),
				'description'       => esc_html__( 'Add search form using shortcode such as [fibosearch]', 'shopwell' ),
				'required'          => array(
					array(
						'control'  => 'shopwell_header_search_bar',
						'operator' => '==',
						'value'    => 'shortcode',
					),
				),
				'priority'          => 15,
			),
		);
		// Header Custom Text
		$settings['shopwell_header_custom_text'] = array(
			'shopwell_header_custom_text' => array(
				'type'            => 'shopwell-textarea',
				'label'           => esc_html__( 'Content', 'shopwell' ),
				'description'     => esc_html__( 'The content of the Header Custom Text', 'shopwell' ),
				'partial'         => array(
					array(
						'element' => '.header-custom-text',
					),
				),
				'active_callback' => function () {
					return $this->display_header_custom_text();
				},
			),
		);

		// Footer layout settings.
		$settings['shopwell_footer_layout'] = array(
			'shopwell_footer_copyright_textarea' => array(
				'sanitize_callback' => 'shopwell_sanitize_textarea',
				'type'              => 'shopwell-textarea',
				'label'             => esc_html__( 'Footer Copyright', 'shopwell' ),
				'required'          => array(
					array(
						'control'  => 'shopwell_footer_options',
						'operator' => '==',
						'value'    => '1',
					),
				),
			),
			'shopwell_footer_bg'                 => array(
				'sanitize_callback' => 'shopwell_sanitize_design_options',
				'transport'         => 'postMessage',
				'type'              => 'shopwell-design-options',
				'label'             => esc_html__( 'Background', 'shopwell' ),
				'display'           => array(
					'background' => array(
						'color'    => esc_html__( 'Solid Color', 'shopwell' ),
						'gradient' => esc_html__( 'Gradient', 'shopwell' ),
						'image'    => esc_html__( 'Image', 'shopwell' ),
					),
				),
				'required'          => array(
					array(
						'control'  => 'shopwell_footer_options',
						'operator' => '==',
						'value'    => '1',
					),
				),
			),
			'shopwell_footer_text_color'         => array(
				'sanitize_callback' => 'shopwell_sanitize_design_options',
				'transport'         => 'postMessage',
				'type'              => 'shopwell-design-options',
				'label'             => esc_html__( 'Font Color', 'shopwell' ),
				'display'           => array(
					'color' => array(
						'text-color'       => esc_html__( 'Text Color', 'shopwell' ),
						'link-color'       => esc_html__( 'Link Color', 'shopwell' ),
						'link-hover-color' => esc_html__( 'Link Hover Color', 'shopwell' ),
					),
				),
				'required'          => array(
					array(
						'control'  => 'shopwell_footer_options',
						'operator' => '==',
						'value'    => '1',
					),
				),
			),
		);

		// Blog Header
		$settings['shopwell_blog_prebuilt_header'] = array(
			'shopwell_header_blog_version' => array(
				'sanitize_callback' => 'shopwell_sanitize_select',
				'type'              => 'shopwell-select',
				'label'             => esc_html__( 'Prebuilt Header Blog', 'shopwell' ),
				'description'       => esc_html__( 'Select a prebuilt header for blog page', 'shopwell' ),
				'choices'           => $this->header_options(),
			),
			'shopwell_blog_primary_menu'   => array(
				'sanitize_callback' => 'shopwell_sanitize_select',
				'type'              => 'shopwell-select',
				'label'             => esc_html__( 'Primary Menu', 'shopwell' ),
				'choices'           => $this->get_menus(),
			),
		);

		// Blog Header
		$settings['shopwell_blog_header'] = array(
			'shopwell_blog_header'     => array(
				'sanitize_callback' => 'shopwell_sanitize_toggle',
				'type'              => 'shopwell-toggle',
				'label'             => esc_html__( 'Enable Blog Header', 'shopwell' ),
				'description'       => esc_html__( 'Enable the blog header on blog pages.', 'shopwell' ),
			),
			'shopwell_blog_header_els' => array(
				'sanitize_callback' => 'shopwell_no_sanitize',
				'type'              => 'shopwell-checkbox-group',
				'label'             => esc_html__( 'Blog Header Elements', 'shopwell' ),
				'priority'          => 10,
				'choices'           => array(
					'breadcrumb' => array(
						'title' => esc_html__( 'BreadCrumb', 'shopwell' ),
					),
					'title'      => array(
						'title' => esc_html__( 'Title', 'shopwell' ),
					),
				),
				'description'       => esc_html__( 'Select which elements you want to show.', 'shopwell' ),
				'required'          => array(
					array(
						'control'  => 'shopwell_blog_header',
						'operator' => '==',
						'value'    => true,
					),
				),

			),
		);

		// Blog Archive
		$settings['shopwell_blog_archive'] = array(
			'shopwell_blog_trending_posts'       => array(
				'sanitize_callback' => 'shopwell_sanitize_toggle',
				'type'              => 'shopwell-toggle',
				'label'             => esc_html__( 'Trending Posts', 'shopwell' ),
				'description'       => esc_html__( 'Display the trending posts section on blog page', 'shopwell' ),
			),
			'shopwell_blog_trending_tag'         => array(
				'sanitize_callback' => 'shopwell_sanitize_select',
				'type'              => 'shopwell-select',
				'multiple'          => true,
				'is_select2'        => true,
				'data_source'       => 'tags',
				'label'             => esc_html__( 'Trending Tag', 'shopwell' ),
				'description'       => esc_html__( 'Specify the tag you will use on posts to be displayed as Trending Content', 'shopwell' ),
				'required'          => array(
					array(
						'control'  => 'shopwell_blog_trending_posts',
						'operator' => '==',
						'value'    => true,
					),
				),
			),
			'shopwell_blog_featured_posts'       => array(
				'sanitize_callback' => 'shopwell_sanitize_toggle',
				'type'              => 'shopwell-toggle',
				'label'             => esc_html__( 'Featured Posts', 'shopwell' ),
				'description'       => esc_html__( 'Display the Featured Posts section on blog page', 'shopwell' ),
			),
			'shopwell_blog_featured_tag'         => array(
				'sanitize_callback' => 'shopwell_sanitize_select',
				'type'              => 'shopwell-select',
				'multiple'          => true,
				'is_select2'        => true,
				'data_source'       => 'tags',
				'label'             => esc_html__( 'Featured Tag', 'shopwell' ),
				'description'       => esc_html__( 'Specify the tag you will use on posts to be displayed as Featured Posts', 'shopwell' ),
				'required'          => array(
					array(
						'control'  => 'shopwell_blog_featured_posts',
						'operator' => '==',
						'value'    => true,
					),
				),
			),
			'shopwell_blog_featured_link_url'    => array(
				'type'     => 'shopwell-text',
				'label'    => esc_html__( 'See All Link', 'shopwell' ),
				'required' => array(
					array(
						'control'  => 'shopwell_blog_featured_posts',
						'operator' => '==',
						'value'    => true,
					),
				),
			),
			'shopwell_blog_featured_posts_total' => array(
				'sanitize_callback' => 'shopwell_sanitize_number',
				'type'              => 'shopwell-number',
				'label'             => esc_html__( 'Total Number', 'shopwell' ),
				'description'       => esc_html__( 'Total number of the post', 'shopwell' ),
				'min'               => 3,
				'max'               => 10,
				'step'              => 1,
				'unit'              => 'px',
				'required'          => array(
					array(
						'control'  => 'shopwell_blog_featured_posts',
						'operator' => '==',
						'value'    => true,
					),
				),
			),

			'shopwell_blog_layout'               => array(
				'sanitize_callback' => 'shopwell_sanitize_radio',
				'type'              => 'shopwell-radio',
				'label'             => esc_html__( 'Blog Layout', 'shopwell' ),
				'description'       => esc_html__( 'The layout of blog posts', 'shopwell' ),
				'choices'           => array(
					'default' => esc_html__( 'Default', 'shopwell' ),
					'classic' => esc_html__( 'Classic', 'shopwell' ),
				),
			),
			'shopwell_excerpt_length'            => array(
				'sanitize_callback' => 'shopwell_sanitize_number',
				'type'              => 'shopwell-number',
				'label'             => esc_html__( 'Excerpt Length', 'shopwell' ),
				'description'       => esc_html__( 'The number of words of the post excerpt', 'shopwell' ),
				'required'          => array(
					array(
						'control'  => 'shopwell_blog_layout',
						'operator' => '==',
						'value'    => 'default',
					),
				),
			),
		);

		// Blog single.
		$settings['shopwell_blog_single'] = array(
			'shopwell_post_layout'                  => array(
				'sanitize_callback' => 'shopwell_sanitize_select',
				'type'              => 'shopwell-select',
				'label'             => esc_html__( 'Post Layout', 'shopwell' ),
				'description'       => esc_html__( 'The layout of single posts', 'shopwell' ),
				'choices'           => array(
					'no-sidebar'      => esc_html__( 'No Sidebar', 'shopwell' ),
					'content-sidebar' => esc_html__( 'Right Sidebar', 'shopwell' ),
					'sidebar-content' => esc_html__( 'Left Sidebar', 'shopwell' ),
				),
			),
			'shopwell_post_featured_image_position' => array(
				'sanitize_callback' => 'shopwell_sanitize_select',
				'type'              => 'shopwell-select',
				'label'             => esc_html__( 'Featured Image Position', 'shopwell' ),
				'choices'           => array(
					''    => esc_html__( 'Default', 'shopwell' ),
					'top' => esc_html__( 'Above the category', 'shopwell' ),
				),
			),
			'shopwell_post_author_box'              => array(
				'sanitize_callback' => 'shopwell_sanitize_toggle',
				'type'              => 'shopwell-toggle',
				'label'             => esc_html__( 'Author Box', 'shopwell' ),
				'description'       => esc_html__( 'Display the post author box', 'shopwell' ),
			),
			'shopwell_post_navigation'              => array(
				'sanitize_callback' => 'shopwell_sanitize_toggle',
				'type'              => 'shopwell-toggle',
				'label'             => esc_html__( 'Post Navigation', 'shopwell' ),
				'description'       => esc_html__( 'Display the next and previous posts', 'shopwell' ),
			),
		);

		// Upsell section
		$settings['shopwell_section_upsell_button'] = array(
			'shopwell_section_upsell_heading' => array(
				'type'    => 'hidden',
			)
		);
		// Docs link
		$settings['shopwell_section_docs_button'] = array(
			'shopwell_section_docs_heading' => array(
				'type'    => 'hidden',
			)
		);

		return array(
			'panels'   => apply_filters( 'shopwell_customize_panels', $panels ),
			'sections' => apply_filters( 'shopwell_customize_sections', $sections ),
			'settings' => apply_filters( 'shopwell_customize_settings', $settings ),
		);
	}

	/**
	 * Get nav menus
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public static function get_menus() {
		if ( ! is_admin() ) {
			return array();
		}

		$menus = wp_get_nav_menus();
		if ( ! $menus ) {
			return array();
		}

		$output = array(
			0 => esc_html__( 'Select Menu', 'shopwell' ),
		);
		foreach ( $menus as $menu ) {
			$output[ $menu->slug ] = $menu->name;
		}

		return $output;
	}

	/**
	 * Repeater Santitize Icon
	 *
	 * @return $sanitized_value
	 * @since 1.0.0
	 */
	public static function repeater_sanitize_icon( $value ) {
		$sanitized_value = array();
		$value           = ( is_array( $value ) ) ? $value : json_decode( urldecode( $value ), true );

		foreach ( $value as $key => $subvalue ) {
			$sanitized_value[ $key ] = $subvalue;

			if ( isset( $sanitized_value[ $key ]['icon'] ) ) {
				$sanitized_value[ $key ]['icon'] = \Shopwell\Icon::sanitize_svg( $sanitized_value[ $key ]['icon'] );
			}
		}

		return $sanitized_value;
	}

	/**
	 * Display header categories
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function display_header_categories( $post_type = '' ) {
		if ( 'shortcode' == \Shopwell\Helper::get_option( 'header_search_bar' ) ) {
			return false;
		}

		if ( empty( \Shopwell\Helper::get_option( 'header_search_type' ) ) ) {
			return false;
		}

		if ( ! empty( $post_type ) ) {
			if ( is_array( $post_type ) && ! in_array( \Shopwell\Helper::get_option( 'header_search_type' ), $post_type ) ) {
				return false;
			}
		}

		if ( 'custom' == \Shopwell\Helper::get_option( 'header_present' ) ) {
			if ( \Shopwell\Helper::get_option( 'header_search_style' ) != 'form' ) {
				return false;
			}
			$header_search_items = array_keys( (array) \Shopwell\Helper::get_option( 'header_search_items' ), true, true );
			if ( ! in_array( 'categories', $header_search_items ) ) {
				return false;
			}

			return true;
		} else {
			return true;
		}
	}

	/**
	 * Display header categories
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function display_header_cart_custom_color() {
		if ( 'custom' == \Shopwell\Helper::get_option( 'header_present' ) ) {
			if ( in_array( \Shopwell\Helper::get_option( 'header_cart_type' ), array( 'base' ) ) ) {
				return true;
			}

			return false;
		} else {
			if ( in_array( \Shopwell\Helper::get_option( 'header_version' ), array( 'v4' ) ) ) {
				return true;
			}

			return false;
		}
	}

	/**
	 * Display header hamburger
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function display_header_hamburger() {
		if ( 'custom' == \Shopwell\Helper::get_option( 'header_present' ) ) {
			return true;
		} else {
			if ( in_array( \Shopwell\Helper::get_option( 'header_version' ), array( 'v3', 'v5', 'v8' ) ) ) {
				return true;
			}

			return false;
		}
	}


	/**
	 * Display header hamburger
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function header_menu_panel_items() {
		$items = array(
			''              => esc_html__( 'Select an item', 'shopwell' ),
			'divider'       => esc_html__( 'Divider', 'shopwell' ),
			'track-order'   => esc_html__( 'Track Order', 'shopwell' ),
			'help-center'   => esc_html__( 'Help Center', 'shopwell' ),
			'custom-menu'   => esc_html__( 'Custom Menu', 'shopwell' ),
			'primary-menu'  => esc_html__( 'Primary menu', 'shopwell' ),
			'category-menu' => esc_html__( 'Category Menu', 'shopwell' ),
			'preferences'   => esc_html__( 'Preferences', 'shopwell' ),
		);

		if ( function_exists( 'wcboost_wishlist' ) ) {
			$items['wishlist'] = esc_html__( 'Wishlist', 'shopwell' );
		}

		if ( function_exists( 'wcboost_products_compare' ) ) {
			$items['compare'] = esc_html__( 'Compare', 'shopwell' );
		}

		$items = apply_filters( 'shopwell_get_header_menu_panel_items', $items );

		return $items;
	}


	/**
	 * Display header custom text
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function display_header_view_history() {
		if ( 'custom' == \Shopwell\Helper::get_option( 'header_present' ) ) {
			return true;
		} else {
			if ( in_array( \Shopwell\Helper::get_option( 'header_version' ), array( 'v8', 'v9', 'v10' ) ) ) {
				return true;
			}

			return false;
		}
	}

	/**
	 * Display header custom text
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function display_header_custom_text() {
		if ( 'custom' == \Shopwell\Helper::get_option( 'header_present' ) ) {
			return true;
		} else {
			if ( in_array( \Shopwell\Helper::get_option( 'header_version' ), array( 'v1', 'v2' ) ) ) {
				return true;
			}

			return false;
		}
	}

	/**
	 * Display header empty space
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function display_header_empty_space() {
		if ( 'custom' == \Shopwell\Helper::get_option( 'header_present' ) ) {
			return true;
		} else {
			if ( in_array( \Shopwell\Helper::get_option( 'header_version' ), array( 'v13' ) ) ) {
				return true;
			}

			return false;
		}
	}

	/**
	 * Display header search button
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function display_header_search_button() {
		if ( 'shortcode' == \Shopwell\Helper::get_option( 'header_search_bar' ) ) {
			return false;
		}
		if ( 'custom' == \Shopwell\Helper::get_option( 'header_present' ) ) {
			if ( in_array( \Shopwell\Helper::get_option( 'header_search_skins' ), array( 'base', 'raised', 'ghost' ) ) ) {
				return true;
			}
			return false;
		} else {
			if ( in_array( \Shopwell\Helper::get_option( 'header_version' ), array( 'v1', 'v2', 'v3', 'v4', 'v5', 'v9', 'v13' ) ) ) {
				return true;
			}

			return false;
		}
	}


	/**
	 * Display header search
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function display_header_search_custom_color() {
		if ( 'shortcode' == \Shopwell\Helper::get_option( 'header_search_bar' ) ) {
			return false;
		}
		if ( 'custom' == \Shopwell\Helper::get_option( 'header_present' ) ) {
			return true;
		} else {
			if ( in_array( \Shopwell\Helper::get_option( 'header_version' ), array( 'v1', 'v2', 'v3', 'v4', 'v5', 'v9', 'v13' ) ) ) {
				return true;
			}

			return false;
		}
	}
}
