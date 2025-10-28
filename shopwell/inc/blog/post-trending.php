<?php
/**
 * Trending Post
 *
 * @package Shopwell
 */

namespace Shopwell\Blog;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Single initial
 */
class Post_Trending extends Post {
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
	 * Get entry thumbnail
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function thumbnail() {
		$icon = self::get_format_icon();

		printf(
			'<a href="%s" class="trending-posts__thumbnail" style="background-image: url(%s)">%s</a>',
			esc_url( get_the_permalink() ),
			wp_get_attachment_image_url( get_post_thumbnail_id( get_the_ID() ), 'full' ),
			$icon
		);
	}

	/**
	 * Get Excerpt
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function excerpt() {
		if ( ! self::get_display( 'td_excerpt' ) ) {
			return;
		}

		$length = self::get_display( 'td_excerpt' );

		if ( empty( $length ) ) {
			return;
		}

		self::get_excerpt( $length );
	}

	/**
	 * Get entry link
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function button() {
		$class_button = 'shopwell-button--medium';

		if ( self::get_display( 'button_class' ) ) {
			$class_button = 'shopwell-button--' . self::get_display( 'button_class' );
		}

		echo '<a class="shopwell-button ' . esc_attr( $class_button ) . ' shopwell-button--ghost trending-posts__button" href="' . get_the_permalink() . '">' . esc_html__( 'Read More', 'shopwell' ) . '</a>';
	}

	/**
	 * Get entry link
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function link() {
		echo '<a class="trending-posts__link" href="' . get_the_permalink() . '"></a>';
	}
}
