<?php
/**
 * WooCommerce Customizer functions and definitions.
 *
 * @package shopwell
 */

namespace Shopwell\WooCommerce;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * The shopwell WooCommerce Customizer class
 */
class Customizer {

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
		add_filter( 'shopwell_customize_panels', array( $this, 'get_customize_panels' ) );
		add_filter( 'shopwell_customize_sections', array( $this, 'get_customize_sections' ) );
		add_filter( 'shopwell_customize_settings', array( $this, 'get_customize_settings' ) );
	}

	/**
	 * Adds theme options panels of WooCommerce.
	 *
	 * @since 1.0.0
	 *
	 * @param array $panels Theme options panels.
	 *
	 * @return array
	 */
	public function get_customize_panels( $panels ) {
		$panels['woocommerce'] = array(
			'priority' => 50,
			'title'    => esc_html__( 'Woocommerce', 'shopwell' ),
		);

		$panels['shopwell_shop'] = array(
			'priority' => 50,
			'title'    => esc_html__( 'Shop', 'shopwell' ),
		);

		if ( apply_filters( 'shopwell_get_single_product_settings', true ) ) {
			$panels['shopwell_single_product'] = array(
				'priority' => 55,
				'title'    => esc_html__( 'Single Product', 'shopwell' ),
			);
		}

		$panels['shopwell_vendors'] = array(
			'priority' => 60,
			'title'    => esc_html__( 'Vendors', 'shopwell' ),
		);

		return $panels;
	}

	/**
	 * Adds theme options sections of WooCommerce.
	 *
	 * @since 1.0.0
	 *
	 * @param array $sections Theme options sections.
	 *
	 * @return array
	 */
	public function get_customize_sections( $sections ) {
		// Typography
		$sections['shopwell_typo_catalog'] = array(
			'title' => esc_html__( 'Product Catalog', 'shopwell' ),
			'panel' => 'shopwell_typography',
		);
		$sections['shopwell_typo_product'] = array(
			'title' => esc_html__( 'Product', 'shopwell' ),
			'panel' => 'shopwell_typography',
		);

		if ( apply_filters( 'shopwell_get_product_archive_settings', true ) ) {
			// Page Header
			$sections['shopwell_shop_catalog_header'] = array(
				'title' => esc_html__( 'Page Header', 'shopwell' ),
				'panel' => 'shopwell_shop',
			);

			// Catalog toolbar
			$sections['shopwell_shop_catalog_toolbar'] = array(
				'title' => esc_html__( 'Catalog Toolbar', 'shopwell' ),
				'panel' => 'shopwell_shop',
			);

			// Catalog Layout
			$sections['shopwell_shop_catalog'] = array(
				'title' => esc_html__( 'Product Catalog', 'shopwell' ),
				'panel' => 'shopwell_shop',
			);
		}

		// Product Card
		$sections['shopwell_product_card'] = array(
			'title' => esc_html__( 'Product Card', 'shopwell' ),
			'panel' => 'shopwell_shop',
		);

		// Badges
		$sections['shopwell_badges'] = array(
			'title' => esc_html__( 'Badges', 'shopwell' ),
			'panel' => 'shopwell_shop',
		);

		// Quick View
		$sections['shopwell_quickview'] = array(
			'title' => esc_html__( 'Quick View', 'shopwell' ),
			'panel' => 'shopwell_shop',
		);

		// Single Product
		$sections['shopwell_product'] = array(
			'title' => esc_html__( 'Product Layout', 'shopwell' ),
			'panel' => 'shopwell_single_product',
		);

		// Related Product
		$sections['shopwell_product_sharing'] = array(
			'title' => esc_html__( 'Product Sharing', 'shopwell' ),
			'panel' => 'shopwell_single_product',
		);

		// Related Product
		$sections['shopwell_related_products'] = array(
			'title' => esc_html__( 'Related Products', 'shopwell' ),
			'panel' => 'shopwell_single_product',
		);

		// Upsells Product
		$sections['shopwell_upsells_products'] = array(
			'title' => esc_html__( 'Up-Sells  Products', 'shopwell' ),
			'panel' => 'shopwell_single_product',
		);

		// Store style active when use wcfm
		$sections['shopwell_vendors_store_style'] = array(
			'title' => esc_html__( 'Store Style', 'shopwell' ),
			'panel' => 'shopwell_vendors',
		);

		$sections['shopwell_vendors_store_manager'] = array(
			'title' => esc_html__( 'Store Manager', 'shopwell' ),
			'panel' => 'shopwell_vendors',
		);

		// Store List
		$sections['shopwell_vendors_store_list'] = array(
			'title' => esc_html__( 'Store List', 'shopwell' ),
			'panel' => 'shopwell_vendors',
		);

		// Store Page
		$sections['shopwell_vendors_store_page'] = array(
			'title' => esc_html__( 'Store Page', 'shopwell' ),
			'panel' => 'shopwell_vendors',
		);

		$sections['shopwell_vendors_product_page'] = array(
			'title' => esc_html__( 'Product Page', 'shopwell' ),
			'panel' => 'shopwell_vendors',
		);

		return $sections;
	}

	/**
	 * Adds theme options of WooCommerce.
	 *
	 * @since 1.0.0
	 *
	 * @param array $fields Theme options fields.
	 *
	 * @return array
	 */
	public function get_customize_settings( $settings ) {
		// Product Card
		$settings['shopwell_product_card'] = array(
			'shopwell_product_card_attribute'        => array(
				'sanitize_callback' => 'shopwell_sanitize_select',
				'type'              => 'shopwell-select',
				'label'             => esc_html__( 'Product Attribute', 'shopwell' ),
				'choices'           => $this->get_product_attributes(),
				'description'       => esc_html__( 'Show product attribute in the product card', 'shopwell' ),
				'required'          => array(
					array(
						'control'  => 'shopwell_product_card_layout',
						'operator' => '!=',
						'value'    => '5',
					),
				),
				'priority'          => 70,
			),
			'shopwell_product_card_attribute_in'     => array(
				'sanitize_callback' => 'shopwell_no_sanitize',
				'type'              => 'shopwell-checkbox-group',
				'label'             => esc_html__( 'Product Attribute In', 'shopwell' ),
				'choices'           => array(
					'variable' => array(
						'title' => esc_html__( 'Variable Product', 'shopwell' ),
					),
					'simple'   => array(
						'title' => esc_html__( 'Simple Product', 'shopwell' ),
					),
				),
				'required'          => array(
					array(
						'control'  => 'shopwell_product_card_layout',
						'operator' => '!=',
						'value'    => '5',
					),
					array(
						'control'  => 'shopwell_product_card_attribute',
						'operator' => '!=',
						'value'    => 'none',
					),
				),
				'priority'          => 75,
			),
			'shopwell_product_card_attribute_number' => array(
				'sanitize_callback' => 'shopwell_sanitize_number',
				'type'              => 'shopwell-number',
				'description'       => esc_html__( 'Product Attribute Number', 'shopwell' ),
				'required'          => array(
					array(
						'control'  => 'shopwell_product_card_layout',
						'operator' => '!=',
						'value'    => '5',
					),
					array(
						'control'  => 'shopwell_product_card_attribute',
						'operator' => '!=',
						'value'    => 'none',
					),
				),
				'priority'          => 80,
			),
		);

		if ( class_exists( 'WeDevs_Dokan' ) || class_exists( 'WCFMmp' ) || class_exists( 'Marketkingcore' ) ) {
			$settings['shopwell_product_card']['shopwell_product_card_vendor_name'] = array(
				'sanitize_callback' => 'shopwell_sanitize_toggle',
				'type'              => 'shopwell-toggle',
				'label'             => esc_html__( 'Vendor Name', 'shopwell' ),
				'priority'          => 42,
			);

			$settings['shopwell_vendors_store_manager']['shopwell_wcfm_dashboard_custom_fields'] = array(
				'sanitize_callback' => 'shopwell_no_sanitize',
				'type'              => 'shopwell-checkbox-group',
				'label'             => esc_html__( 'Custom Fields', 'shopwell' ),
				'choices'           => array(
					'video' => array(
						'title' => esc_html__( 'Product Video', 'shopwell' ),
					),
				),
			);
		}

		if ( function_exists( 'wcboost_wishlist' ) ) {
			$settings['shopwell_product_card']['shopwell_product_card_wishlist'] = array(
				'sanitize_callback' => 'shopwell_sanitize_toggle',
				'type'              => 'shopwell-toggle',
				'label'             => esc_html__( 'Wishlist', 'shopwell' ),
				'priority'          => 42,
				'required'          => array(
					array(
						'control'  => 'shopwell_product_card_layout',
						'operator' => '!=',
						'value'    => '5',
					),
				),
			);
		}

		if ( function_exists( 'wcboost_products_compare' ) ) {
			$settings['shopwell_product_card']['shopwell_product_card_compare'] = array(
				'sanitize_callback' => 'shopwell_sanitize_toggle',
				'type'              => 'shopwell-toggle',
				'label'             => esc_html__( 'Compare', 'shopwell' ),
				'priority'          => 42,
				'required'          => array(
					array(
						'control'  => 'shopwell_product_card_layout',
						'operator' => '!=',
						'value'    => '5',
					),
				),
			);
		}

		// Page Header
		$settings['shopwell_shop_catalog_header'] = array(
			'shopwell_shop_page_header_title_align' => array(
				'sanitize_callback' => 'shopwell_sanitize_radio',
				'type'              => 'shopwell-radio',
				'label'             => esc_html__( 'Title Align', 'shopwell' ),
				'choices'           => array(
					'center' => esc_attr__( 'Center', 'shopwell' ),
					'left'   => esc_attr__( 'Left', 'shopwell' ),
				),
				'required'          => array(
					array(
						'control'  => 'shopwell_shop_page_header',
						'operator' => '==',
						'value'    => 'minimal',
					),
				),
			),
		);

		// Catalog toolbar.
		$settings['shopwell_shop_catalog_toolbar'] = array(
			'shopwell_catalog_toolbar'              => array(
				'sanitize_callback' => 'shopwell_sanitize_toggle',
				'type'              => 'shopwell-toggle',
				'label'             => esc_html__( 'Catalog Toolbar', 'shopwell' ),
			),
			'shopwell_catalog_toolbar_default_view' => array(
				'sanitize_callback' => 'shopwell_sanitize_select',
				'type'              => 'shopwell-select',
				'label'             => esc_html__( 'Default View', 'shopwell' ),
				'choices'           => array(
					'grid-2' => esc_html__( 'Grid 2 Columns', 'shopwell' ),
					'grid-3' => esc_html__( 'Grid 3 Columns', 'shopwell' ),
					'grid-4' => esc_html__( 'Grid 4 Columns', 'shopwell' ),
				),
				'required'          => array(
					array(
						'control'  => 'shopwell_catalog_toolbar',
						'operator' => '==',
						'value'    => true,
					),
				),
			),
		);

		// Product Catalog
		$settings['shopwell_shop_catalog'] = array(
			'shopwell_catalog_sidebar'        => array(
				'sanitize_callback' => 'shopwell_sanitize_select',
				'type'              => 'shopwell-select',
				'label'             => esc_html__( 'Sidebar', 'shopwell' ),
				'description'       => esc_html__( 'Go to appearance > widgets find to catalog sidebar to edit your sidebar', 'shopwell' ),
				'choices'           => array(
					'content-sidebar' => esc_html__( 'Right Sidebar', 'shopwell' ),
					'sidebar-content' => esc_html__( 'Left Sidebar', 'shopwell' ),
					'no-sidebar'      => esc_html__( 'No Sidebar', 'shopwell' ),
				),
			),
			'shopwell_catalog_sticky_sidebar' => array(
				'sanitize_callback' => 'shopwell_sanitize_toggle',
				'type'              => 'shopwell-toggle',
				'label'             => esc_html__( 'Sticky Sidebar', 'shopwell' ),
				'description'       => esc_html__( 'Attachs the sidebar to the page when the user scrolls', 'shopwell' ),
				'required'          => array(
					array(
						'control'  => 'shopwell_catalog_sidebar',
						'operator' => '!=',
						'value'    => 'no-sidebar',
					),
				),
			),
		);

		// Badges
		$settings['shopwell_badges'] = array(
			'shopwell_badges_sale'                => array(
				'sanitize_callback' => 'shopwell_sanitize_toggle',
				'type'              => 'shopwell-toggle',
				'label'             => esc_html__( 'Sale Badge', 'shopwell' ),
				'description'       => esc_html__( 'Display a badge for sale products.', 'shopwell' ),
			),
			'shopwell_badges_sale_type'           => array(
				'sanitize_callback' => 'shopwell_sanitize_select',
				'type'              => 'shopwell-select',
				'label'             => esc_html__( 'Sale Badge Type', 'shopwell' ),
				'choices'           => array(
					'percent'        => esc_html__( 'Percentage', 'shopwell' ),
					'text'           => esc_html__( 'Text', 'shopwell' ),
					'text-price'     => esc_html__( 'Text And Price', 'shopwell' ),
					'text-countdown' => esc_html__( 'Text And Countdown', 'shopwell' ),
				),
				'required'          => array(
					array(
						'control'  => 'shopwell_badges_sale',
						'operator' => '=',
						'value'    => true,
					),
				),
			),
			'shopwell_badges_sale_text'           => array(
				'sanitize_callback' => 'sanitize_text_field',
				'type'              => 'shopwell-text',
				'label'             => esc_html__( 'Sale Badge Text', 'shopwell' ),
				'required'          => array(
					array(
						'control'  => 'shopwell_badges_sale',
						'operator' => '=',
						'value'    => true,
					),
					array(
						'control'  => 'shopwell_badges_sale_type',
						'operator' => '!=',
						'value'    => 'percent',
					),
				),
			),
			'shopwell_badges_sale_bg'             => array(
				'sanitize_callback' => 'shopwell_sanitize_color',
				'type'              => 'shopwell-color',
				'transport'         => 'postMessage',
				'label'             => esc_html__( 'Sale Badge Background', 'shopwell' ),
				'required'          => array(
					array(
						'control'  => 'shopwell_badges_sale',
						'operator' => '=',
						'value'    => true,
					),
				),
			),
			'shopwell_badges_sale_text_color'     => array(
				'sanitize_callback' => 'shopwell_sanitize_color',
				'type'              => 'shopwell-color',
				'transport'         => 'postMessage',
				'label'             => esc_html__( 'Sale Badge Text Color', 'shopwell' ),
				'required'          => array(
					array(
						'control'  => 'shopwell_badges_sale',
						'operator' => '=',
						'value'    => true,
					),
				),
			),

			'shopwell_badges_new'                 => array(
				'sanitize_callback' => 'shopwell_sanitize_toggle',
				'type'              => 'shopwell-toggle',
				'label'             => esc_html__( 'New Badge', 'shopwell' ),
				'description'       => esc_html__( 'Display a badge for new products.', 'shopwell' ),
			),
			'shopwell_badges_new_text'            => array(
				'sanitize_callback' => 'sanitize_text_field',
				'type'              => 'shopwell-text',
				'label'             => esc_html__( 'New Badge Text', 'shopwell' ),
				'required'          => array(
					array(
						'control'  => 'shopwell_badges_new',
						'operator' => '=',
						'value'    => true,
					),
				),
			),
			'shopwell_badges_newness'             => array(
				'sanitize_callback' => 'shopwell_sanitize_number',
				'type'              => 'shopwell-number',
				'description'       => esc_html__( 'Display the "New" badge for how many days?', 'shopwell' ),
				'tooltip'           => esc_html__( 'You can also add the NEW badge to each product in the Advanced setting tab of them.', 'shopwell' ),
				'required'          => array(
					array(
						'control'  => 'shopwell_badges_new',
						'operator' => '=',
						'value'    => true,
					),
				),
			),
			'shopwell_badges_new_bg'              => array(
				'sanitize_callback' => 'shopwell_sanitize_color',
				'type'              => 'shopwell-color',
				'transport'         => 'postMessage',
				'label'             => esc_html__( 'New Badge Background', 'shopwell' ),
				'choices'           => array(
					'alpha' => true,
				),
				'required'          => array(
					array(
						'control'  => 'shopwell_badges_new',
						'operator' => '=',
						'value'    => true,
					),
				),
			),
			'shopwell_badges_new_text_color'      => array(
				'sanitize_callback' => 'shopwell_sanitize_color',
				'type'              => 'shopwell-color',
				'transport'         => 'postMessage',
				'label'             => esc_html__( 'New Badge Text Color', 'shopwell' ),
				'choices'           => array(
					'alpha' => true,
				),
				'required'          => array(
					array(
						'control'  => 'shopwell_badges_new',
						'operator' => '=',
						'value'    => true,
					),
				),
			),
			'shopwell_badges_featured'            => array(
				'sanitize_callback' => 'shopwell_sanitize_toggle',
				'type'              => 'shopwell-toggle',
				'label'             => esc_html__( 'Featured Badge', 'shopwell' ),
				'description'       => esc_html__( 'Display a badge for featured products.', 'shopwell' ),
			),
			'shopwell_badges_featured_text'       => array(
				'sanitize_callback' => 'sanitize_text_field',
				'type'              => 'shopwell-text',
				'label'             => esc_html__( 'Featured Badge Text', 'shopwell' ),
				'required'          => array(
					array(
						'control'  => 'shopwell_badges_featured',
						'operator' => '=',
						'value'    => true,
					),
				),
			),
			'shopwell_badges_featured_bg'         => array(
				'sanitize_callback' => 'shopwell_sanitize_color',
				'type'              => 'shopwell-color',
				'transport'         => 'postMessage',
				'label'             => esc_html__( 'Featured Badge Background', 'shopwell' ),
				'required'          => array(
					array(
						'control'  => 'shopwell_badges_featured',
						'operator' => '=',
						'value'    => true,
					),
				),
			),
			'shopwell_badges_featured_text_color' => array(
				'sanitize_callback' => 'shopwell_sanitize_color',
				'type'              => 'shopwell-color',
				'transport'         => 'postMessage',
				'label'             => esc_html__( 'Featured Badge Text Color', 'shopwell' ),
				'required'          => array(
					array(
						'control'  => 'shopwell_badges_featured',
						'operator' => '=',
						'value'    => true,
					),
				),
			),

			'shopwell_badges_soldout'             => array(
				'sanitize_callback' => 'shopwell_sanitize_toggle',
				'type'              => 'shopwell-toggle',
				'label'             => esc_html__( 'Sold Out Badge', 'shopwell' ),
				'description'       => esc_html__( 'Display a badge for out of stock products.', 'shopwell' ),
			),
			'shopwell_badges_soldout_text'        => array(
				'sanitize_callback' => 'sanitize_text_field',
				'type'              => 'shopwell-text',
				'label'             => esc_html__( 'Sold Out Badge Text', 'shopwell' ),
				'required'          => array(
					array(
						'control'  => 'shopwell_badges_soldout',
						'operator' => '=',
						'value'    => true,
					),
				),
			),
			'shopwell_badges_soldout_bg'          => array(
				'sanitize_callback' => 'shopwell_sanitize_color',
				'type'              => 'shopwell-color',
				'transport'         => 'postMessage',
				'label'             => esc_html__( 'Sold Out Badge Background', 'shopwell' ),
				'required'          => array(
					array(
						'control'  => 'shopwell_badges_soldout',
						'operator' => '=',
						'value'    => true,
					),
				),
			),
			'shopwell_badges_soldout_text_color'  => array(
				'sanitize_callback' => 'shopwell_sanitize_color',
				'type'              => 'shopwell-color',
				'transport'         => 'postMessage',
				'label'             => esc_html__( 'Sold Out Badge Text Color', 'shopwell' ),
				'required'          => array(
					array(
						'control'  => 'shopwell_badges_soldout',
						'operator' => '=',
						'value'    => true,
					),
				),
			),

			'shopwell_badges_custom_bg'           => array(
				'sanitize_callback' => 'shopwell_sanitize_color',
				'type'              => 'shopwell-color',
				'transport'         => 'postMessage',
				'label'             => esc_html__( 'Custom badge Background', 'shopwell' ),
			),

			'shopwell_badges_custom_color'        => array(
				'sanitize_callback' => 'shopwell_sanitize_color',
				'type'              => 'shopwell-color',
				'transport'         => 'postMessage',
				'label'             => esc_html__( 'Custom badge Color', 'shopwell' ),
			),

		);

		// Single Product
		$settings['shopwell_product'] = array(
			'shopwell_product_single_tags'       => array(
				'sanitize_callback' => 'shopwell_sanitize_toggle',
				'type'              => 'shopwell-toggle',
				'label'             => esc_html__( 'Product Tags', 'shopwell' ),
			),
			'shopwell_product_single_categories' => array(
				'sanitize_callback' => 'shopwell_sanitize_toggle',
				'type'              => 'shopwell-toggle',
				'label'             => esc_html__( 'Product Categories', 'shopwell' ),
			),
		);

		$settings['shopwell_product_sharing'] = array(
			'shopwell_product_sharing' => array(
				'sanitize_callback' => 'shopwell_sanitize_toggle',
				'type'              => 'shopwell-toggle',
				'label'             => esc_html__( 'Product Sharing', 'shopwell' ),
				'description'       => esc_html__( 'Enable post sharing.', 'shopwell' ),
			),
		);

		$settings['shopwell_related_products'] = array(
			'shopwell_related_products' => array(
				'sanitize_callback' => 'shopwell_sanitize_toggle',
				'type'              => 'shopwell-toggle',
				'label'             => esc_html__( 'Related Products', 'shopwell' ),
			),
		);

		$settings['shopwell_upsells_products'] = array(
			'shopwell_upsells_products' => array(
				'sanitize_callback' => 'shopwell_sanitize_toggle',
				'type'              => 'shopwell-toggle',
				'label'             => esc_html__( 'Upsells Products', 'shopwell' ),
			),
		);

		// vendor Store List
		if ( class_exists( 'WeDevs_Dokan' ) || class_exists( 'WCFMmp' ) ) {
			$settings['shopwell_vendors_store_list']['shopwell_store_list_page_header'] = array(
				'sanitize_callback' => 'shopwell_no_sanitize',
				'type'              => 'shopwell-checkbox-group',
				'label'             => esc_html__( 'Page Header Elements', 'shopwell' ),
				'choices'           => array(
					'breadcrumb' => array(
						'title' => esc_html__( 'BreadCrumb', 'shopwell' ),
					),
					'title'      => array(
						'title' => esc_html__( 'Title', 'shopwell' ),
					),
				),
			);

			$settings['shopwell_vendors_store_page']['shopwell_store_page_page_header'] = array(
				'sanitize_callback' => 'shopwell_no_sanitize',
				'type'              => 'shopwell-checkbox-group',
				'label'             => esc_html__( 'Page Header Elements', 'shopwell' ),
				'choices'           => array(
					'breadcrumb' => array(
						'title' => esc_html__( 'BreadCrumb', 'shopwell' ),
					),
					'title'      => array(
						'title' => esc_html__( 'Title', 'shopwell' ),
					),
				),
			);
		}

		if ( class_exists( 'WCFMmp' ) ) {
			$settings['shopwell_vendors_store_style']['shopwell_vendor_store_style_theme'] = array(
				'sanitize_callback' => 'shopwell_sanitize_toggle',
				'type'              => 'shopwell-toggle',
				'label'             => esc_html__( 'Enable Style From Theme', 'shopwell' ),
				'description'       => esc_html__( 'Enable the store list and store page style from theme.', 'shopwell' ),
			);
		}

		if ( class_exists( 'WeDevs_Dokan' ) ) {
			$settings['shopwell_vendors_product_page']['shopwell_product_tab_vendor_info'] = array(
				'sanitize_callback' => 'shopwell_sanitize_toggle',
				'type'              => 'shopwell-toggle',
				'label'             => esc_html__( 'Hide Vendor Info tab', 'shopwell' ),
			);

			$settings['shopwell_vendors_product_page']['shopwell_product_tab_more_products'] = array(
				'sanitize_callback' => 'shopwell_sanitize_toggle',
				'type'              => 'shopwell-toggle',
				'label'             => esc_html__( 'Hide More Product tab', 'shopwell' ),
			);
		}

		return $settings;
	}

	/**
	 * Get product attributes
	 *
	 * @return string
	 */
	public function get_product_attributes() {
		$output = array();
		if ( function_exists( 'wc_get_attribute_taxonomies' ) ) {
			$attributes_tax = wc_get_attribute_taxonomies();
			if ( $attributes_tax ) {
				$output['none'] = esc_html__( 'None', 'shopwell' );

				foreach ( $attributes_tax as $attribute ) {
					$output[ $attribute->attribute_name ] = ucfirst( $attribute->attribute_label );
				}
			}
		}

		return $output;
	}
}
