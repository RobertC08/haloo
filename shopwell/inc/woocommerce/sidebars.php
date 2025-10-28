<?php
/**
 * Woocommerce Widgets functions and definitions.
 *
 * @package Shopwell
 */

namespace Shopwell\WooCommerce;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Woocommerce Widgets initial
 */
class Sidebars {
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
		add_action( 'widgets_init', array( $this, 'woocommerce_widgets_register' ) );
		add_filter( 'dynamic_sidebar_params', array( $this, 'woocommerce_dynamic_sidebar_params' ) );
	}

	/**
	 * Register widget areas.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function woocommerce_widgets_register() {
		if ( apply_filters( 'shopwell_product_archive_widgets_register', true ) ) {
			$after_title = '</h4>';

			register_sidebar(
				array(
					'name'          => esc_html__( 'Catalog Sidebar', 'shopwell' ),
					'id'            => 'catalog-sidebar',
					'description'   => esc_html__( 'Add sidebar for the catalog page', 'shopwell' ),
					'before_widget' => '<section id="%1$s" class="widget %2$s">',
					'after_widget'  => '</section>',
					'before_title'  => '<h4 class="widget-title">',
					'after_title'   => $after_title,
				)
			);

			register_sidebar(
				array(
					'name'          => esc_html__( ' Catalog Filters Sidebar', 'shopwell' ),
					'id'            => 'catalog-filters-sidebar',
					'description'   => esc_html__( 'Add sidebar for panel filter in the catalog page', 'shopwell' ),
					'before_widget' => '<section id="%1$s" class="widget %2$s">',
					'after_widget'  => '</section>',
					'before_title'  => '<h4 class="widget-title">',
					'after_title'   => $after_title,
				)
			);
		}

		if ( apply_filters( 'shopwell_single_product_widgets_register', true ) ) {
			register_sidebar(
				array(
					'name'          => esc_html__( 'Single Product Extra Content', 'shopwell' ),
					'id'            => 'single-product-extra-content',
					'description'   => esc_html__( 'Add sidebar for the product page', 'shopwell' ),
					'before_widget' => '<section id="%1$s" class="widget %2$s">',
					'after_widget'  => '</section>',
					'before_title'  => '<h4 class="widget-title">',
					'after_title'   => '</h4>',
				)
			);
		}
	}

	/**
	 * Dynamic Sidebar Params.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function woocommerce_dynamic_sidebar_params( $params ) {
		global $wp_registered_widgets;

		$settings_getter = $wp_registered_widgets[ $params[0]['widget_id'] ]['callback'][0];

		if ( ! is_object( $settings_getter ) ) {
			return $params;
		}

		$settings = $settings_getter->get_settings();
		$settings = ! empty( $settings ) ? $settings[ $params[1]['number'] ] : '';

		if ( ! empty( $settings ) ) {
			if ( $params[0]['after_widget'] == '</div></div>' && isset( $settings['title'] ) && empty( $settings['title'] ) ) {
				$params[0]['before_widget'] .= '<div class="content">';
			}
		}

		return $params;
	}
}
