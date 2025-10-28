<?php

/**
 * Shopwell Options Class.
 *
 * @package  Shopwell
 * @author   Peregrine Themes
 * @since    1.0.0
 */

namespace Shopwell;

/**
 * Do not allow direct script access.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Shopwell Options Class.
 */
class Options {


	/**
	 * Singleton instance of the class.
	 *
	 * @since 1.0.0
	 * @var object
	 */
	private static $instance;

	/**
	 * Options variable.
	 *
	 * @since 1.0.0
	 * @var mixed $options
	 */
	private static $options;

	/**
	 * Main Options Instance.
	 *
	 * @since 1.0.0
	 * @return Options
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

		// Refresh options.
		add_action( 'after_setup_theme', array( $this, 'refresh' ) );
	}

	/**
	 * Set default option values.
	 *
	 * @since  1.0.0
	 * @return array Default values.
	 */
	public function get_defaults() {

		$defaults = array(
			'shopwell_page_header_version'                 => '',
			'shopwell_page_primary_menu'                   => '',
			'shopwell_header_page_hide_topbar'             => true,
			'shopwell_header_page_hide_campaign_bar'       => true,
			'shopwell_header_page_transparent'             => false,
			'shopwell_header_page_text_color'              => 'dark',
			'shopwell_header_page_logo_type'               => 'default',
			'shopwell_header_page_logo_text'               => get_bloginfo( 'name' ),
			'shopwell_header_page_logo_svg'                => '',
			'shopwell_header_page_logo_svg_light'          => '',
			'shopwell_header_page_logo'                    => '',
			'shopwell_header_page_logo_light'              => '',
			'shopwell_header_page_logo_width'              => '',
			'shopwell_header_page_logo_height'             => '',
			'shopwell_page_footer_version'                 => '',
			'shopwell_page_mobile_footer_version'          => '',
			'shopwell_page_404_layout'                     => 'v1',
			'shopwell_header_page_404_version'             => '',
			'shopwell_page_404_primary_menu'               => '',
			'shopwell_header_page_404_hide_topbar'         => false,
			'shopwell_header_page_404_hide_campaign_bar'   => false,
			'shopwell_header_page_404_logo_type'           => 'default',
			'shopwell_header_page_404_logo_text'           => get_bloginfo( 'name' ),
			'shopwell_header_page_404_logo_svg'            => '',
			'shopwell_header_page_404_logo'                => '',
			'shopwell_header_page_404_logo_width'          => '',
			'shopwell_header_page_404_logo_height'         => '',
			'shopwell_footer_page_404_version'             => '',
			// Styling
			'shopwell_shape_style'                         => 'default',
			'shopwell_primary_custom_color'                => '#0068c8',
			'shopwell_primary_text_color'                  => 'light',
			'shopwell_primary_text_custom_color'           => '#fff',
			// General > Back To Top
			'shopwell_backtotop'                           => true,

			// General > Share socials
			'shopwell_post_sharing_socials'                => array(
				'facebook'   => true,
				'twitter'    => true,
				'googleplus' => true,
				'pinterest'  => true,
				'tumblr'     => true,
				'reddit'     => true,
				'telegram'   => true,
				'email'      => true,
			),
			'shopwell_post_sharing_whatsapp_number'        => '',

			// Header > Header layout
			'shopwell_header_present'                      => 'prebuild',
			'shopwell_header_version'                      => 'v11',
			'shopwell_header_navigation_cutoff'            => true,
			'shopwell_header_navigation_cutoff_upto'       => '7',
			'shopwell_header_navigation_cutoff_text'       => '',
			'shopwell_header_present_search'               => true,
			'shopwell_header_present_account'              => true,
			'shopwell_header_present_compare'              => true,
			'shopwell_header_present_wishlist'             => true,
			'shopwell_header_present_cart'                 => true,
			'shopwell_header_present_hamburger'            => false,
			'shopwell_header_present_category_menu'        => true,
			'shopwell_header_present_primary_menu'         => true,
			'shopwell_header_present_secondary_menu'       => true,
			'shopwell_header_present_custom_text'          => true,
			'shopwell_header_present_preferences'          => false,
			'shopwell_header_present_view_history'         => true,
			'shopwell_header_container'                    => 'container',

			// Header > Main header
			'shopwell_header_main_height'                  => 100,
			'shopwell_header_main_left_heading'            => true,
			'shopwell_header_main_center_heading'          => true,
			'shopwell_header_main_right_heading'           => true,

			// Header > Bottom header
			'shopwell_header_bottom_height'                => 60,
			'shopwell_header_bottom_left_heading'          => true,
			'shopwell_header_bottom_center_heading'        => true,
			'shopwell_header_bottom_right_heading'         => true,

			// Header > Sticky header
			'shopwell_header_sticky'                       => 'normal',
			'shopwell_header_sticky_on'                    => 'down',
			'shopwell_header_sticky_el'                    => 'header_main',
			'shopwell_header_sticky_height'                => 80,
			'shopwell_header_sticky_left_heading'          => true,
			'shopwell_header_sticky_center_heading'        => true,
			'shopwell_header_sticky_right_heading'         => true,

			// Header > Header background
			'shopwell_header_custom_background_color'      => '',
			'shopwell_header_custom_background_text_color' => '',
			'shopwell_header_custom_background_border_color' => '',

			// Header > Campaign
			'shopwell_campaign_bar'                        => false,
			'shopwell_campaign_bar_position'               => 'before',
			'shopwell_campaign_height'                     => 44,
			'shopwell_campaign_text_size'                  => 14,
			'shopwell_campaign_text_weight'                => '400',
			'shopwell_campaign_button_spacing'             => 31,
			'shopwell_campaign_bg'                         => \Shopwell\Helper::design_options_defaults(
				array(
					'background' => array(
						'color'    => array(
							'background-color' => '#0068C8',
						),
						'gradient' => array(
							'gradient-color-1' => '',
							'gradient-color-2' => '',
						),
						'image'    => array(),
					),
				),
			),
			'shopwell_campaign_color'                      => \Shopwell\Helper::design_options_defaults(
				array(
					'color' => array(
						'text-color'       => '#ffffff',
						'link-color'       => '#ffffff',
						'link-hover-color' => '#ffffff',
					),
				),
			),
			'shopwell_campaign_border'                     => \Shopwell\Helper::design_options_defaults(
				array(
					'border' => array(
						'border-top-width'    => '',
						'border-bottom-width' => '',
						'border-style'        => 'solid',
						'border-color'        => '',
						// 'separator-color'     => '',
					),
				)
			),

			// Header > Logo
			'shopwell_logo_type'                           => 'image',
			'shopwell_logo_text'                           => get_bloginfo( 'name' ),
			'shopwell_logo_svg'                            => '',
			'shopwell_logo'                                => '',
			'shopwell_logo_width'                          => 256,
			'shopwell_logo_height'                         => 0,
			// Header > Search
			'shopwell_header_search_bar'                   => 'default',
			'shopwell_header_search_shortcode'             => '',
			'shopwell_header_search_type'                  => 'adaptive',
			'shopwell_search_columns'                      => array( 'post_title', 'post_excerpt', 'post_content' ),
			'shopwell_header_search_style'                 => 'form',
			'shopwell_header_search_form_width'            => 655,
			'shopwell_header_search_items'                 => array(
				'search-field' => true,
				'divider'      => true,
				'categories'   => true,
				'icon'         => false,
			),
			'shopwell_header_search_items_button_display'  => 'icon',
			'shopwell_header_search_items_button_position' => 'outside',
			'shopwell_header_search_items_button_spacing'  => true,
			'shopwell_header_search_product_cats'          => '',
			'shopwell_header_search_post_cats'             => '',
			'shopwell_header_search_cats_top'              => false,
			'shopwell_header_search_cats_empty'            => false,
			'shopwell_header_search_skins'                 => 'base',
			'shopwell_header_search_ajax'                  => true,
			'shopwell_header_search_number'                => 3,
			'shopwell_header_search_trending_searches'     => false,
			'shopwell_header_search_trending_searches_position' => 'outside',
			'shopwell_header_search_skins_background_color' => '',
			'shopwell_header_search_skins_color'           => '',
			'shopwell_header_search_skins_border_color'    => '',
			'shopwell_header_search_skins_button_color'    => '',
			'shopwell_header_search_skins_button_icon_color' => '',

			// Header > Account
			'shopwell_header_account_icon_behaviour'       => 'panel',
			'shopwell_header_signin_icon_behaviour'        => 'page',

			// Header > hamburger
			'shopwell_header_hamburger_menu_items'         => array(
				array(
					'item' => 'track-order',
				),
				array(
					'item' => 'help-center',
				),
			),
			'shopwell_header_hamburger_custom_menu'        => '',
			'shopwell_header_hamburger_primary_menu'       => '',
			'shopwell_header_hamburger_category_menu'      => '',
			'shopwell_header_hamburger_account_info'       => true,
			'shopwell_header_hamburger_spacing'            => array(
				'desktop' => array(
					'left'  => '',
					'right' => '',
				),
				'tablet'  => array(
					'left'  => '',
					'right' => '',
				),
				'mobile'  => array(
					'left'  => '',
					'right' => '',
				),
				'unit'    => 'px',
			),

			// Header > Primary menu
			'shopwell_header_primary_menu_caret'           => true,
			'shopwell_header_primary_menu_dividers'        => false,
			'shopwell_header_primary_menu_font_size_parent_item' => 14,
			'shopwell_header_primary_menu_spacing_parent_item' => 12,

			// Header > Secondary menu
			'shopwell_header_secondary_menu_caret'         => false,
			'shopwell_header_secondary_menu_font_size_parent_item' => 14,
			'shopwell_header_secondary_menu_spacing_parent_item' => 12,

			// Header > Category menu
			'shopwell_header_category_display'             => 'both',
			'shopwell_header_category_type'                => 'ghost',
			'shopwell_header_category_icon'                => 'v1',
			'shopwell_header_category_space'               => 0,
			'shopwell_header_category_arrow_spacing'       => 50,
			'shopwell_header_category_content_spacing'     => 0,

			// Header > Preferences
			'shopwell_header_preferences_display'          => 'icon',
			'shopwell_header_preferences_flag'             => false,
			'shopwell_header_preferences_type'             => 'text',
			// Header > View history
			'shopwell_header_view_history_link'            => '',
			// Header > Wishlist
			'shopwell_header_wishlist_display'             => 'icon',
			'shopwell_header_wishlist_type'                => 'text',
			'shopwell_header_wishlist_icon_position'       => 'icon-left',
			'shopwell_header_wishlist_counter'             => true,
			'shopwell_header_wishlist_counter_background_color' => '',
			'shopwell_header_wishlist_counter_color'       => '',
			// Header > compare
			'shopwell_header_compare_display'              => 'icon',
			'shopwell_header_compare_type'                 => 'text',
			'shopwell_header_compare_icon_position'        => 'icon-left',
			'shopwell_header_compare_counter'              => true,
			'shopwell_header_compare_counter_background_color' => '',
			'shopwell_header_compare_counter_color'        => '',
			// Header > Cart
			'shopwell_header_cart_display'                 => 'icon',
			'shopwell_header_cart_type'                    => 'text',
			'shopwell_header_cart_icon'                    => 'bag',
			'shopwell_header_cart_icon_custom'             => '',
			'shopwell_header_cart_icon_position'           => 'icon-left',
			'shopwell_header_cart_icon_behaviour'          => 'panel',
			'shopwell_header_cart_background_color'        => '',
			'shopwell_header_cart_color'                   => '',
			'shopwell_header_cart_counter_background_color' => '',
			'shopwell_header_cart_counter_color'           => '',
			// Header > Custom text
			'shopwell_header_custom_text'                  => '',
			'shopwell_header_custom_text_color'            => '',
			'shopwell_header_custom_text_font_size'        => 14,
			'shopwell_header_custom_text_font_weight'      => '500',
			// Header empty space
			'shopwell_header_empty_space'                  => 266,
			'shopwell_header_return_button_link'           => '',

			// Mobile > Topbar
			'shopwell_mobile_topbar'                       => false,
			'shopwell_mobile_topbar_section'               => 'left',
			// Mobile > campaign bar
			'shopwell_campaign_mobile_text_size'           => 14,
			// Mobile > Header layout
			'shopwell_header_mobile_breakpoint'            => 1024,
			'shopwell_header_mobile_present'               => 'prebuild',
			'shopwell_header_mobile_version'               => 'v11',
			'shopwell_header_mobile_present_search'        => true,
			'shopwell_header_mobile_present_hamburger'     => true,
			'shopwell_header_mobile_present_account'       => true,
			'shopwell_header_mobile_present_wishlist'      => true,
			'shopwell_header_mobile_present_preferences'   => true,
			'shopwell_header_mobile_present_primary_menu'  => true,
			'shopwell_header_mobile_present_cart'          => true,
			'shopwell_header_mobile_main_height'           => 62,
			'shopwell_header_mobile_bottom_height'         => 48,
			// Mobile > Header sticky
			'shopwell_header_mobile_sticky'                => 'normal',
			'shopwell_header_mobile_sticky_left'           => array(),
			'shopwell_header_mobile_sticky_center'         => array(),
			'shopwell_header_mobile_sticky_right'          => array(),
			'shopwell_header_mobile_sticky_height'         => 64,
			// Mobile > Header Logo
			'shopwell_mobile_logo_type'                    => 'default',
			'shopwell_mobile_logo_text'                    => get_bloginfo( 'name' ),
			'shopwell_mobile_logo_svg'                     => '',
			'shopwell_mobile_logo_image'                   => '',
			'shopwell_mobile_logo_width'                   => 146,
			'shopwell_mobile_logo_height'                  => 0,
			// Mobile > Header hamburger
			'shopwell_header_mobile_menu_items'            => array(
				array(
					'item' => 'track-order',
				),
				array(
					'item' => 'help-center',
				),
				array(
					'item' => 'divider',
				),
				array(
					'item' => 'category-menu',
				),
				array(
					'item' => 'divider',
				),
				array(
					'item' => 'primary-menu',
				),
				array(
					'item' => 'divider',
				),
				array(
					'item' => 'preferences',
				),
			),
			'shopwell_header_mobile_custom_menu'           => '',
			'shopwell_header_mobile_category_menu'         => '',
			'shopwell_header_mobile_open_submenus'         => 'icon',
			'shopwell_header_mobile_account_info'          => true,
			// Mobile > Header search
			'shopwell_header_mobile_search_style_prebuild' => 'form',
			'shopwell_header_mobile_search_style'          => 'form',
			'shopwell_header_mobile_search_items'          => array(
				'icon'         => false,
				'search-field' => true,
			),
			'shopwell_header_mobile_search_items_button_display' => 'icon',
			'shopwell_header_mobile_search_items_button_position' => 'outside',
			'shopwell_header_mobile_search_items_button_spacing' => true,
			'shopwell_header_mobile_search_icon_type'      => 'text',
			'shopwell_header_mobile_search_trending_searches' => false,
			// Mobile > Header wishlist
			'shopwell_header_mobile_wishlist_type'         => 'text',
			// Mobile > Header account
			'shopwell_header_mobile_account_type'          => 'text',
			// Mobile > Header cart
			'shopwell_header_mobile_cart_display'          => 'icon',
			'shopwell_header_mobile_cart_type'             => 'subtle',
			// Mobile > Footer
			'shopwell_footer_mobile_breakpoint'            => '',
			'shopwell_footer_mobile_version'               => '',
			// Mobile > Product card
			'shopwell_mobile_product_card_atc'             => false,
			'shopwell_mobile_product_card_featured_icons'  => 'load',
			// Mobile > Product catalog
			'shopwell_shop_page_header_mobile_height'      => 130,
			'shopwell_catalog_toolbar_sticky'              => false,
			'shopwell_mobile_product_columns'              => '2',
			'shopwell_mobile_product_list_desc'            => false,
			// Mobile > Single product
			'shopwell_mobile_product_header'               => 'default',
			'shopwell_mobile_product_gallery_fixed'        => true,
			// Mobile > Navigation bar
			'shopwell_mobile_navigation_bar'               => 'none',
			'shopwell_mobile_navigation_bar_items'         => array(
				'home'     => true,
				'shop'     => true,
				'cart'     => true,
				'wishlist' => true,
				'account'  => true,
			),
			'shopwell_mobile_navigation_bar_category_menu' => '',
			'shopwell_mobile_navigation_bar_background_color' => '',
			'shopwell_mobile_navigation_bar_color'         => '',
			'shopwell_mobile_navigation_bar_box_shadow_color' => '',
			'shopwell_mobile_navigation_bar_spacing'       => 0,
			'shopwell_mobile_navigation_bar_spacing_bottom' => 0,
			'shopwell_mobile_navigation_bar_counter_background_color' => '',
			'shopwell_mobile_navigation_bar_counter_color' => '',
			// Page > Page header
			'shopwell_page_header'                         => true,
			'shopwell_page_header_els'                     => array( 'breadcrumb', 'title' ),
			// Blog > Prebuild header
			'shopwell_header_blog_version'                 => '',
			'shopwell_blog_primary_menu'                   => '',
			'shopwell_header_blog_hide_topbar'             => false,
			'shopwell_header_blog_hide_campaign_bar'       => false,
			'shopwell_header_blog_hide_header_main'        => true,
			'shopwell_header_blog_logo_type'               => 'default',
			'shopwell_header_blog_logo_text'               => get_bloginfo( 'name' ),
			'shopwell_header_blog_logo_svg'                => '',
			'shopwell_header_blog_logo'                    => '',
			'shopwell_header_blog_logo_width'              => '',
			'shopwell_header_blog_logo_height'             => '',
			// Blog > Blog header
			'shopwell_blog_header'                         => false,
			'shopwell_blog_header_els'                     => array( 'title' ),
			// Blog > Blog archive
			'shopwell_blog_trending_posts'                 => true,
			'shopwell_blog_trending_tag'                   => '',
			'shopwell_blog_trending_layout'                => '1',
			'shopwell_blog_trending_carousel_number'       => 3,
			'shopwell_blog_trending_length'                => 17,
			'shopwell_blog_featured_posts'                 => true,
			'shopwell_blog_featured_tag'                   => '',
			'shopwell_blog_featured_link_url'              => '#',
			'shopwell_blog_featured_posts_columns'         => '4',
			'shopwell_blog_featured_posts_total'           => 6,
			'shopwell_blog_featured_position'              => 'under',
			'shopwell_blog_posts_heading'                  => false,
			'shopwell_blog_posts_heading_type'             => 'group',
			'shopwell_blog_posts_heading_menu'             => '',
			'shopwell_blog_layout'                         => 'classic',
			'shopwell_excerpt_length'                      => 30,
			'shopwell_blog_nav_type'                       => 'numeric',
			'shopwell_blog_nav_ajax_url_change'            => true,
			// Blog > Blog single
			'shopwell_post_layout'                         => 'no-sidebar',
			'shopwell_post_featured_image_position'        => '',
			'shopwell_post_author_box'                     => false,
			'shopwell_post_navigation'                     => true,
			'shopwell_post_related_posts'                  => true,
			'shopwell_post_sharing'                        => false,
			// Help center > Header layout
			'shopwell_help_center_header'                  => '',
			'shopwell_help_center_primary_menu'            => '',
			'shopwell_help_center_header_transparent'      => false,
			'shopwell_header_help_center_color'            => 'dark',
			'shopwell_header_help_center_logo_type'        => 'default',
			'shopwell_header_help_center_logo_text'        => get_bloginfo( 'name' ),
			'shopwell_header_help_center_logo_svg'         => 'default',
			'shopwell_header_help_center_logo'             => '',
			'shopwell_header_help_center_logo_light_svg'   => '',
			'shopwell_header_help_center_logo_light'       => '',
			'shopwell_header_help_center_logo_width'       => '',
			'shopwell_header_help_center_logo_height'      => '',
			// Help center > search bar
			'shopwell_help_center_search'                  => array( 'archive', 'single' ),
			'shopwell_help_center_search_bg'               => '',
			'shopwell_help_center_search_color'            => 'dark',
			'shopwell_help_center_search_space_top'        => 50,
			'shopwell_help_center_search_space_bottom'     => 50,
			'sw_help_article_length'                       => 17,
			'shopwell_help_center_single_hide_sidebar'     => false,
			'shopwell_help_center_single_sidebar_posts_number' => 10,
			'shopwell_help_center_single_hide_title'       => false,
			// Shop > Product card
			'shopwell_product_card_layout'                 => '1',
			'shopwell_product_card_hover'                  => 'fadein',
			'shopwell_product_card_title_lines'            => '',
			'shopwell_product_card_title_tag'              => 'h2',
			'shopwell_product_card_taxonomy'               => 'product_cat',
			'shopwell_product_card_stars_rating'           => true,
			'shopwell_product_card_add_to_cart_button'     => true,
			'shopwell_product_card_quick_view_button'      => true,
			'shopwell_product_card_quickview_behaviour'    => 'modal',
			'shopwell_product_card_attribute'              => 'none',
			'shopwell_product_card_attribute_in'           => array( 'variable', 'simple' ),
			'shopwell_product_card_attribute_number'       => 4,
			'shopwell_product_card_vendor_name'            => true,
			'shopwell_wcfm_dashboard_custom_fields'        => array(),
			'shopwell_product_card_wishlist'               => true,
			'shopwell_product_card_compare'                => true,
			'shopwell_compare_page_columns'                => array( 'rating', 'price', 'stock', 'sku', 'dimensions', 'weight', 'add-to-cart' ),

			// Shop > Page header
			'shopwell_shop_page_header'                    => 'minimal',
			'shopwell_shop_page_header_image'              => '',
			'shopwell_shop_page_header_background_overlay' => '',
			'shopwell_shop_page_header_textcolor'          => 'dark',
			'shopwell_shop_page_header_textcolor_custom'   => '',
			'shopwell_shop_page_header_height'             => 260,
			'shopwell_shop_page_header_title_align'        => 'center',
			// Shop > Shop header
			'shopwell_shop_header'                         => false,
			'shopwell_shop_header_template_id'             => '',
			// Shop > Top categories
			'shopwell_top_categories'                      => false,
			'shopwell_top_categories_layout'               => '1',
			'shopwell_top_categories_status_product'       => array( 'new', 'sale' ),
			'shopwell_top_categories_limit'                => 0,
			'shopwell_top_categories_order'                => 'order',
			'shopwell_taxonomy_description_enable'         => true,
			'shopwell_taxonomy_description_position'       => 'pageheader',
			'shopwell_taxonomy_description_html'           => false,
			// Shop > Catalog toolbar
			'shopwell_catalog_toolbar'                     => true,
			'shopwell_catalog_toolbar_layout'              => '1',
			'shopwell_catalog_toolbar_view'                => array( 'sortby', 'view' ),
			'shopwell_catalog_toolbar_view_els'            => array( 'grid-2', 'grid-3', 'grid-4', 'grid-5', 'list' ),
			'shopwell_catalog_toolbar_default_view'        => 'grid-3',
			// Shop > Product catalog
			'shopwell_catalog_sidebar'                     => 'sidebar-content',
			'shopwell_catalog_sticky_sidebar'              => true,
			'shopwell_catalog_nav'                         => 'numeric',
			'shopwell_catalog_nav_ajax_url_change'         => true,
			'shopwell_catalog_grid_border'                 => '',
			'shopwell_catalog_product_description'         => true,
			'shopwell_catalog_product_description_lines'   => 3,
			// Shop > Badges
			'shopwell_badges_sale'                         => true,
			'shopwell_badges_sale_type'                    => 'percent',
			'shopwell_badges_sale_text'                    => esc_attr__( 'Sale', 'shopwell' ),
			'shopwell_badges_sale_bg'                      => '#dd2831',
			'shopwell_badges_sale_text_color'              => '#fff',
			'shopwell_badges_new'                          => true,
			'shopwell_badges_new_text'                     => esc_attr__( 'New', 'shopwell' ),
			'shopwell_badges_newness'                      => 3,
			'shopwell_badges_new_bg'                       => '#3fb981',
			'shopwell_badges_new_text_color'               => '#ffffff',
			'shopwell_badges_featured'                     => true,
			'shopwell_badges_featured_text'                => esc_attr__( 'Hot', 'shopwell' ),
			'shopwell_badges_featured_bg'                  => '#0068c8',
			'shopwell_badges_featured_text_color'          => '#ffffff',
			'shopwell_badges_soldout'                      => false,
			'shopwell_badges_soldout_text'                 => esc_attr__( 'Out Of Stock', 'shopwell' ),
			'shopwell_badges_soldout_bg'                   => '#e0e0e0',
			'shopwell_badges_soldout_text_color'           => '#ffffff',
			'shopwell_badges_custom_bg'                    => '',
			'shopwell_badges_custom_color'                 => '',
			// Shop > Product notifications
			'shopwell_added_to_cart_notice'                => 'none',
			'shopwell_added_to_cart_notice_products'       => 'related_products',
			'shopwell_added_to_cart_notice_products_limit' => 8,
			'shopwell_added_to_wishlist_notice'            => 0,
			'shopwell_wishlist_notice_auto_hide'           => 3,
			'shopwell_added_to_compare_notice'             => 0,
			'shopwell_compare_notice_auto_hide'            => 3,
			// Single product > Product layout
			'shopwell_product_layout'                      => '1',
			'shopwell_product_image_zoom'                  => false,
			'shopwell_product_image_lightbox'              => true,
			'shopwell_product_add_to_cart_ajax'            => true,
			'shopwell_product_sale_type'                   => 'text',
			'shopwell_product_sale_bg'                     => \Shopwell\Helper::design_options_defaults(
				array(
					'background' => array(
						'color'    => array(
							'background-color' => '#dd2831',
						),
						'gradient' => array(
							'gradient-color-1' => '#dd2831',
							'gradient-color-2' => '#ed8209',
						),
						'image'    => array(),
					),
				)
			),
			'shopwell_product_sale_color'                  => \Shopwell\Helper::design_options_defaults(
				array(
					'color' => array(
						'text-color' => '#ffffff',
					),
				)
			),
			'shopwell_product_taxonomy'                    => 'product_cat',
			'shopwell_product_tabs_status'                 => 'close',
			'shopwell_product_description'                 => true,
			'shopwell_product_description_lines'           => 6,
			'shopwell_product_single_tags'                 => false,
			'shopwell_product_single_categories'           => false,
			'shopwell_product_single_brands'               => false,
			'shopwell_product_side_products_enable'        => true,
			'shopwell_product_side_products'               => 'best_selling_products',
			'shopwell_product_side_products_limit'         => 5,
			// Single product > Product sharing
			'shopwell_product_sharing'                     => true,
			// Single product > Related products
			'shopwell_related_products'                    => true,
			'shopwell_related_products_by_cats'            => true,
			'shopwell_related_products_by_tags'            => true,
			'shopwell_related_products_numbers'            => 10,
			// Single product > Upsell products
			'shopwell_upsells_products'                    => true,
			'shopwell_upsells_products_numbers'            => 10,
			// Vendord > Store list
			'shopwell_store_list_page_header'              => array( 'breadcrumb' ),
			'shopwell_store_page_page_header'              => array( 'breadcrumb' ),
			'shopwell_vendor_store_style_theme'            => true,
			'shopwell_product_tab_vendor_info'             => true,
			'shopwell_product_tab_more_products'           => true,
			// Footer
			'shopwell_footer_options'                      => '1',
			'shopwell_footer_version'                      => '',
			'shopwell_footer_copyright_textarea'           => wp_kses_post( 'Copyright {{the_year}} &mdash; <b>{{site_title}}</b>. All rights reserved. <b>{{theme_link}}</b>' ),
			'shopwell_footer_bg'                           => '',
			'shopwell_footer_text_color'                   => '',

			// Typography
			// Base Typography.
			'shopwell_html_base_font_size'                 => array(
				'desktop' => 62.5,
				'tablet'  => 53,
				'mobile'  => 50,
			),
			'shopwell_typo_body_heading'                   => true,
			'shopwell_typo_h_heading'                      => true,
			'shopwell_typo_body'                           => \Shopwell\Helper::typography_defaults(
				array(
					'font-family'         => 'Inter',
					'font-weight'         => 400,
					'font-size-desktop'   => '17',
					'font-size-unit'      => 'px',
					'line-height-desktop' => '1.5',
				)
			),
			'shopwell_typo_h1'                             => \Shopwell\Helper::typography_defaults(
				array(
					'font-weight'         => 600,
					'color'               => '#030712',
					'font-size-desktop'   => '72',
					'font-size-unit'      => 'px',
					'line-height-desktop' => '1.2',
				)
			),
			'shopwell_typo_h2'                             => \Shopwell\Helper::typography_defaults(
				array(
					'font-weight'         => 600,
					'color'               => '#030712',
					'font-size-desktop'   => '54',
					'font-size-unit'      => 'px',
					'line-height-desktop' => '1.2',
				)
			),
			'shopwell_typo_h3'                             => \Shopwell\Helper::typography_defaults(
				array(
					'font-weight'         => 600,
					'color'               => '#030712',
					'font-size-desktop'   => '36',
					'font-size-unit'      => 'px',
					'line-height-desktop' => '1.2',
				)
			),
			'shopwell_typo_h4'                             => \Shopwell\Helper::typography_defaults(
				array(
					'font-weight'         => 600,
					'color'               => '#030712',
					'font-size-desktop'   => '28',
					'font-size-unit'      => 'px',
					'line-height-desktop' => '1.2',
				)
			),
			'shopwell_typo_h5'                             => \Shopwell\Helper::typography_defaults(
				array(
					'font-weight'         => 600,
					'color'               => '#030712',
					'font-size-desktop'   => '18',
					'font-size-unit'      => 'px',
					'line-height-desktop' => '1.2',
				)
			),
			'shopwell_typo_h6'                             => \Shopwell\Helper::typography_defaults(
				array(
					'font-weight'         => 600,
					'color'               => '#030712',
					'font-size-desktop'   => '16',
					'font-size-unit'      => 'px',
					'line-height-desktop' => '1.2',
				)
			),
			'shopwell_logo_font'                           => \Shopwell\Helper::typography_defaults(
				array(
					'font-weight'       => 700,
					'font-size-desktop' => '30',
					'font-size-unit'    => 'px',
				)
			),
			'shopwell_typo_menu'                           => \Shopwell\Helper::typography_defaults(
				array(
					'font-weight'       => 500,
					'font-size-desktop' => '16',
					'font-size-unit'    => 'px',
				)
			),
			'shopwell_typo_submenu'                        => \Shopwell\Helper::typography_defaults(
				array(
					'font-weight'       => 'inherit',
					'font-size-desktop' => '15',
					'font-size-unit'    => 'px',
				)
			),
			'shopwell_typo_secondary_menu'                 => \Shopwell\Helper::typography_defaults(
				array(
					'font-weight'       => 500,
					'font-size-desktop' => '16',
					'font-size-unit'    => 'px',
				)
			),
			'shopwell_typo_sub_secondary_menu'             => \Shopwell\Helper::typography_defaults(
				array(
					'font-weight'       => 'inherit',
					'font-size-desktop' => '16',
					'font-size-unit'    => 'px',
				)
			),
			'shopwell_typo_category_menu_title'            => \Shopwell\Helper::typography_defaults(
				array(
					'font-weight'       => 'inherit',
					'font-size-desktop' => '16',
					'font-size-unit'    => 'px',
				)
			),
			'shopwell_typo_category_menu'                  => \Shopwell\Helper::typography_defaults(
				array(
					'font-weight'         => 'inherit',
					'font-size-desktop'   => '14',
					'font-size-unit'      => 'px',
					'line-height-desktop' => '2.5',
				)
			),
			'shopwell_typo_sub_category_menu'              => \Shopwell\Helper::typography_defaults(
				array(
					'font-weight'         => 400,
					'font-size-desktop'   => '14',
					'font-size-unit'      => 'px',
					'line-height-desktop' => '2',
				)
			),
			'shopwell_typo_page_title'                     => \Shopwell\Helper::typography_defaults(
				array(
					'font-weight'         => 700,
					'color'               => '#1d2128',
					'font-size-desktop'   => '40',
					'font-size-unit'      => 'px',
					'line-height-desktop' => '1.2',
				)
			),
			'shopwell_typo_blog_header_title'              => \Shopwell\Helper::typography_defaults(
				array(
					'font-weight'         => 700,
					'color'               => '#1d2128',
					'font-size-desktop'   => '54',
					'font-size-unit'      => 'px',
					'line-height-desktop' => '1.2',
				)
			),
			'shopwell_typo_blog_header_description'        => \Shopwell\Helper::typography_defaults(
				array(
					'font-weight'         => 400,
					'color'               => '#1d2128',
					'font-size-desktop'   => '18',
					'font-size-unit'      => 'px',
					'line-height-desktop' => '1.75',
				)
			),
			'shopwell_typo_blog_post_title'                => \Shopwell\Helper::typography_defaults(
				array(
					'font-weight'         => 700,
					'color'               => '',
					'font-size-desktop'   => '',
					'font-size-unit'      => 'px',
					'line-height-desktop' => '',
				)
			),
			'shopwell_typo_blog_post_excerpt'              => \Shopwell\Helper::typography_defaults(
				array(
					'font-weight'         => 400,
					'color'               => '#7d828a',
					'font-size-desktop'   => '14',
					'font-size-unit'      => 'px',
					'line-height-desktop' => '1.5',
				)
			),
			'shopwell_typo_widget_title'                   => \Shopwell\Helper::typography_defaults(
				array(
					'font-weight'         => 700,
					'color'               => '#1d2128',
					'font-size-desktop'   => '20',
					'font-size-unit'      => 'px',
					'line-height-desktop' => '1.2',
					'text-transform'      => 'uppercase',
				)
			),
			'shopwell_typo_catalog_page_title'             => \Shopwell\Helper::typography_defaults(
				array(
					'font-weight'         => 700,
					'color'               => 'inherit',
					'font-size-desktop'   => '32',
					'font-size-tablet'    => '24',
					'font-size-mobile'    => '20',
					'font-size-unit'      => 'px',
					'line-height-desktop' => '1.2',
					'text-transform'      => 'none',
				)
			),
			'shopwell_typo_catalog_page_description'       => \Shopwell\Helper::typography_defaults(
				array(
					'font-weight'         => 400,
					'color'               => 'inherit',
					'font-size-desktop'   => '14',
					'font-size-unit'      => 'px',
					'line-height-desktop' => '1.5',
					'text-transform'      => 'none',
				)
			),
			'shopwell_typo_catalog_product_title'          => \Shopwell\Helper::typography_defaults(
				array(
					'font-weight'         => 400,
					'color'               => '#1d2128',
					'font-size-desktop'   => '16',
					'font-size-unit'      => 'px',
					'line-height-desktop' => '1.2',
					'text-transform'      => 'none',
				)
			),
			'shopwell_typo_product_title'                  => \Shopwell\Helper::typography_defaults(
				array(
					'font-weight'         => 500,
					'color'               => '#1d2128',
					'font-size-desktop'   => '24',
					'font-size-unit'      => 'px',
					'line-height-desktop' => '1.2',
					'text-transform'      => 'none',
				)
			),

		);

		$defaults = apply_filters( 'shopwell_default_options_values', $defaults );
		return $defaults;
	}

	/**
	 * Get the options from static array()
	 *
	 * @since  1.0.0
	 * @return array    Return array of theme options.
	 */
	public function get_options() {
		return self::$options;
	}

	/**
	 * Get the options from static array().
	 *
	 * @since  1.0.0
	 * @param string $id Options jet to get.
	 * @return array Return array of theme options.
	 */
	public function get( $id ) {
		$value = isset( self::$options[ $id ] ) ? self::$options[ $id ] : self::get_default( $id );
		$value = get_theme_mod($id, $value); // phpcs:ignore
		return $value;
	}

	/**
	 * Set option.
	 *
	 * @since  1.0.0
	 * @param string $id Option key.
	 * @param any    $value Option value.
	 * @return void
	 */
	public function set( $id, $value ) {
		set_theme_mod( $id, $value );
		self::$options[ $id ] = $value;
	}

	/**
	 * Refresh options.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function refresh() {
		self::$options = wp_parse_args(
			get_theme_mods(),
			self::get_defaults()
		);
	}

	/**
	 * Returns the default value for option.
	 *
	 * @since  1.0.0
	 * @param  string $id Option ID.
	 * @return mixed      Default option value.
	 */
	public function get_default( $id ) {
		$defaults = self::get_defaults();
		return isset( $defaults[ $id ] ) ? $defaults[ $id ] : false;
	}
}
