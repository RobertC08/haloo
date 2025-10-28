<?php
/**
 * Meta boxes functions
 *
 * @package Shopwell
 */

namespace Shopwell\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Meta boxes initial
 */
class Meta_Boxes {
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
		add_filter( 'rwmb_meta_boxes', array( $this, 'register_meta_boxes' ) );
	}

	/**
	 * Registering meta boxes
	 *
	 * @since 1.0.0
	 *
	 * Using Meta Box plugin: http://www.deluxeblogtips.com/meta-box/
	 *
	 * @see http://www.deluxeblogtips.com/meta-box/docs/define-meta-boxes
	 *
	 * @param array $meta_boxes Default meta boxes. By default, there are no meta boxes.
	 *
	 * @return array All registered meta boxes
	 */
	public function register_meta_boxes( $meta_boxes ) {
		// Header
		$meta_boxes[] = $this->register_display_settings();

		$meta_boxes = apply_filters( 'shopwell_metaboxes_settings', $meta_boxes );

		return $meta_boxes;
	}

	/**
	 * Register header settings
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function register_display_settings() {
		if ( isset( $_GET['post'] ) && $_GET['post'] == get_option( 'page_for_posts' ) ) {
			return;
		}

		if ( isset( $_GET['post'] ) && $_GET['post'] == get_option( 'woocommerce_shop_page_id' ) ) {
			return;
		}

		if ( isset( $_GET['post'] ) && \Shopwell\Helper::is_help_center_page( intval( $_GET['post'] ) ) ) {
			return;
		}
		return array(
			'id'       => 'display-settings',
			'title'    => esc_html__( 'Display Settings', 'shopwell' ),
			'pages'    => array( 'page' ),
			'context'  => 'normal',
			'priority' => 'high',
			'fields'   => array(
				array(
					'name'  => esc_html__( 'Meta Options', 'shopwell' ),
					'id'    => 'shopwell_heading_site_page_header',
					'class' => 'page-header-heading',
					'type'  => 'heading',
				),
				array(
					'name' => esc_html__( 'Hide Page Header', 'shopwell' ),
					'id'   => 'shopwell_hide_page_header',
					'type' => 'checkbox',
					'std'  => false,
				),
				array(
					'name'  => esc_html__( 'Hide Title', 'shopwell' ),
					'id'    => 'shopwell_hide_title',
					'type'  => 'checkbox',
					'std'   => false,
					'class' => 'page-header-hide-title',
				),
				array(
					'name'  => esc_html__( 'Hide Breadcrumb', 'shopwell' ),
					'id'    => 'shopwell_hide_breadcrumb',
					'type'  => 'checkbox',
					'std'   => false,
					'class' => 'page-header-hide-breadcrumb',
				),
				array(
					'name'    => esc_html__( 'Content Top Spacing', 'shopwell' ),
					'id'      => 'shopwell_content_top_spacing',
					'type'    => 'select',
					'options' => array(
						'default' => esc_html__( 'Default', 'shopwell' ),
						'no'      => esc_html__( 'No spacing', 'shopwell' ),
					),
				),
				array(
					'name'    => esc_html__( 'Content Bottom Spacing', 'shopwell' ),
					'id'      => 'shopwell_content_bottom_spacing',
					'type'    => 'select',
					'options' => array(
						'default' => esc_html__( 'Default', 'shopwell' ),
						'no'      => esc_html__( 'No spacing', 'shopwell' ),
					),
				),
				array(
					'name'    => esc_html__( 'Category Menu Display', 'shopwell' ),
					'id'      => 'header_category_menu_display',
					'type'    => 'select',
					'options' => array(
						'default'    => esc_html__( 'On Click', 'shopwell' ),
						'onpageload' => esc_html__( 'On Page Load', 'shopwell' ),
					),
				),
			),
		);
	}
}
