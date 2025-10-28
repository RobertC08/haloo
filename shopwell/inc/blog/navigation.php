<?php
/**
 * Navigation
 *
 * @package Shopwell
 */

namespace Shopwell\Blog;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Navigation initial
 */
class Navigation {

	/**
	 * Instantiate the object.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function __construct() {
		add_filter( 'next_posts_link_attributes', array( $this, 'posts_link_attributes' ) );
	}


	/**
	 * Navigation numberic
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public static function numberic() {
		the_posts_pagination(
			array(
				'end_size'  => 3,
				'prev_text' => \Shopwell\Icon::get_svg( 'left', 'ui', 'class=shopwell-pagination__arrow' ),
				'next_text' => \Shopwell\Icon::get_svg( 'right', 'ui', 'class=shopwell-pagination__arrow' ),
			)
		);
	}

	/**
	 * Navigation Load More
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public static function load_more() {
		self::posts_found();
		$link = get_next_posts_link( esc_html__( 'Load More', 'shopwell' ) );

		if ( empty( $link ) ) {
			return;
		}

		printf(
			'
			<nav class="navigation pagination next-posts-navigation">
				<h4 class="screen-reader-text">%s</h4>
				%s
				<div class="shopwell-pagination--loading">
					<div class="shopwell-pagination--loading-dots">
						<span></span>
						<span></span>
						<span></span>
						<span></span>
					</div>
					<div class="shopwell-pagination--loading-text">%s</div>
				</div>
			</nav>',
			esc_html__( 'Next posts navigation', 'shopwell' ),
			$link,
			esc_html__( 'Loading more....', 'shopwell' )
		);
	}

	/**
	 * Add class button next navigation
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function posts_link_attributes() {
		return 'class="nav-links shopwell-button shopwell-button--base shopwell-button--bg-color-black shopwell-button--large"';
	}

	/**
	 * Get post found
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function posts_found() {
		global $wp_query;

		if ( $wp_query && $wp_query->found_posts ) {

			$post_text = $wp_query->found_posts > 1 ? esc_html__( 'Posts', 'shopwell' ) : esc_html__( 'Post', 'shopwell' );

			printf(
				'<div class="shopwell-posts-found shopwell-progress">
								<div class="shopwell-posts-found__inner shopwell-progress__inner">
								%s
								<span class="current-post">%s</span>
								%s
								<span class="found-post">%s</span>
								%s
								<span class="count-bar shopwell-progress__count-bar"></span>
							</div>
						</div>',
				esc_html__( 'Showing', 'shopwell' ),
				esc_html( $wp_query->post_count ),
				esc_html__( 'of', 'shopwell' ),
				esc_html( $wp_query->found_posts ),
				esc_html( $post_text )
			);

		}
	}
}
