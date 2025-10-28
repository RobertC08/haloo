<?php
/**
 * Campaign Bar functions and definitions.
 *
 * @package Shopwell
 */

namespace Shopwell\Header;

use Shopwell\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Campaign Bar initial
 */
class Campaign_Bar {
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
	 * Display campaign bar item.
	 *
	 * @since 1.0.0
	 *
	 * @param array $items
	 */
	public static function campaign_items( $items ) {
		if ( empty( $items ) || ! $items ) {
			return;
		}

		foreach ( $items as $id => $item ) {
			$campaign = apply_filters( 'shopwell_campaign_item_args', $item, $id );
			$args     = wp_parse_args(
				$item,
				array(
					'icon' => '',
					'text' => '',
					'link' => array(),
				)
			);

			$button = '';
			if ( ! empty( $args['link']['title'] ) ) {
				$link   = ! empty( $args['link']['url'] ) ? $args['link']['url'] : '#';
				$button = sprintf(
					'<a href="%1$s" target="%2$s" class="campaign-bar__button shopwell-button shopwell-button--subtle">%3$s</a>',
					esc_url( $link ),
					esc_attr( $args['link']['target'] ),
					esc_html( $args['link']['title'] )
				);
			}

			echo '<div class="campaign-bar__item">';
			if ( $args['icon'] ) {
				echo '<div class="campaign-bar__icon">' . \Shopwell\Icon::sanitize_svg( $args['icon'] ) . '</div>';
			}

			if ( $args['text'] ) {
				echo '<div class="campaign-bar__text">' . wp_kses_post( $args['text'] ) . '</div>';
			}
				echo ! empty( $button ) ? $button : '';
			echo '</div>';
		}
	}
}
