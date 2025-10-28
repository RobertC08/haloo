<?php
/**
 * Hooks of Wishlist.
 *
 * @package Shopwell
 */

namespace Shopwell\WooCommerce;

use Shopwell\Icon;

use Shopwell\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class of Wishlist template.
 */
class Wishlist {
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
		add_filter( 'wcboost_wishlist_add_to_wishlist_fragments', array( $this, 'update_wishlist_count' ), 10, 1 );

		// Change the button wishlist
		add_filter( 'wcboost_wishlist_button_template_args', array( $this, 'wishlist_button_template_args' ), 20, 3 );
		add_filter( 'wcboost_wishlist_svg_icon', array( $this, 'wishlist_svg_icon' ), 20, 3 );
		add_filter( 'wcboost_wishlist_loop_add_to_wishlist_link', array( $this, 'wishlist_button_product_loop' ), 20, 2 );

		if ( apply_filters( 'shopwell_change_wishlist_button_settings', true ) ) {
			add_filter( 'wcboost_wishlist_button_add_text', array( $this, 'wishlist_button_add_text' ) );
			add_filter( 'wcboost_wishlist_button_remove_text', array( $this, 'wishlist_button_remove_text' ) );
			add_filter( 'wcboost_wishlist_button_view_text', array( $this, 'wishlist_button_view_text' ) );
		}
	}

	/**
	 * Ajaxify update count wishlist
	 *
	 * @since 1.0
	 *
	 * @param array $fragments
	 *
	 * @return array
	 */
	public function update_wishlist_count( $data ) {
		$wishlist_counter = intval( \WCBoost\Wishlist\Helper::get_wishlist()->count_items() );
		$wishlist_class   = $wishlist_counter == 0 ? ' hidden' : '';

		$data['.header-wishlist .header-wishlist__counter']              = '<span class="header-counter header-wishlist__counter' . $wishlist_class . '">' . $wishlist_counter . '</span>';
		$data['.shopwell-mobile-navigation-bar__icon .wishlist-counter'] = '<span class="counter wishlist-counter' . $wishlist_class . '">' . $wishlist_counter . '</span>';

		return $data;
	}

	/**
	 * Wishlist icon
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function wishlist_svg_icon( $svg, $icon ) {
		if ( $icon == 'heart' ) {
			$svg = Icon::get_svg( 'wishlist' );
		} elseif ( $icon == 'heart-filled' ) {
			$svg = Icon::get_svg( 'wishlist-filled' );
		}

		return $svg;
	}

	/**
	 * Change button args: button title
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function wishlist_button_template_args( $args, $wishlist, $product ) {
		$args['class'][] = 'shopwell-button shopwell-button--text shopwell-button--wishlist';

		switch ( get_option( 'wcboost_wishlist_exists_item_button_behaviour', 'view_wishlist' ) ) {
			case 'remove':
				$args['class'][] = 'shopwell-button-wishlist--remove';
				break;

			case 'view_wishlist':
				$args['class'][] = 'shopwell-button-wishlist--view';
				break;
		}

		$args['product_title'] = $product->get_title();

		return $args;
	}

	/**
	 * Update wishlist text
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function wishlist_button_add_text() {
		return esc_html__( 'Wishlist', 'shopwell' );
	}

	/**
	 * Update wishlist text
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function wishlist_button_remove_text() {
		return esc_html__( 'Remove', 'shopwell' );
	}

	/**
	 * Update wishlist text
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function wishlist_button_view_text() {
		return esc_html__( 'View', 'shopwell' );
	}


	/**
	 * Change wishlist button product loop
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function wishlist_button_product_loop( $html, $args ) {
		global $product;
		$default = array(
			'add'   => esc_html__( 'Add To Wishlist', 'shopwell' ),
			'remove'=> esc_html__( 'Remove From Wishlist', 'shopwell' ),
			'view'  => esc_html__( 'View Wishlist', 'shopwell' ),
		);
		$button_text = get_option('wcboost_wishlist_button_text', $default );
		$html = sprintf(
			'<a href="%s" data-quantity="%s" data-product_id="%s"  data-product_title="%s" data-variations="%s" class="%s" aria-label="%s">
				%s
				<span class="shopwell-button__text add-to-wishlist-button__text wcboost-wishlist-button__text" data-add="%s" data-remove="%s" data-view="%s">%s</span>
			</a>',
			esc_url( isset( $args['url'] ) ? $args['url'] : add_query_arg( array( 'add-to-wishlist' => $product->get_id() ) ) ),
			esc_attr( isset( $args['quantity'] ) ? $args['quantity'] : 1 ),
			esc_attr( isset( $args['product_id'] ) ? $args['product_id'] : $product->get_id() ),
			esc_attr( isset( $args['product_title'] ) ? $args['product_title'] : $product->get_title() ),
			esc_attr( isset( $args['variations_data'] ) ? json_encode( $args['variations_data'] ) : '' ),
			esc_attr( isset( $args['class'] ) ? $args['class'] : '' ),
			/* translators: %s - product title */
			esc_attr( isset( $args['aria-label'] ) ? $args['aria-label'] : sprintf( __( 'Add %s to the wishlist', 'shopwell' ), '&ldquo;' . $product->get_title() . '&rdquo;' ) ),
			empty( $args['icon'] ) ? '' : '<span class="shopwell-button__icon add-to-wishlist-button__icon wcboost-wishlist-button__icon">' . $args['icon'] . '</span>',
			esc_attr( $button_text['add'] ) ?? esc_attr__( 'Add To Wishlist', 'shopwell' ),
			esc_attr( $button_text['remove'] ) ?? esc_attr__( 'Remove Wishlist', 'shopwell' ),
			esc_attr( $button_text['view'] ) ?? esc_attr__( 'View Wishlist', 'shopwell' ),
			esc_html( isset( $args['label'] ) ? $args['label'] : __( 'Add to wishlist', 'shopwell' ) )
		);

		return $html;
	}
}
