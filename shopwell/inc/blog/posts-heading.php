<?php
/**
 * Post Tabs
 *
 * @package Shopwell
 */

namespace Shopwell\Blog;

use Shopwell\Helper;
use WP_Query;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Single initial
 */
class Posts_Heading {

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
	 * Post Tabs
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function recent_heading() {
		printf(
			'<h4 class="shopwell-recent-post__heading">%s</h4>',
			apply_filters( 'shopwell_recent_posts_heading', esc_html__( 'Recent Posts', 'shopwell' ) )
		);
	}

	/**
	 * Post Tabs
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function group() {
		$current_tab = $term_link = '';
		if ( isset( $_GET['posts_group'] ) && ! empty( $_GET['posts_group'] ) ) {
			$current_tab = $_GET['posts_group'];
		}

		if ( is_category() ) {
			$term_id   = get_queried_object()->term_id;
			$term_link = get_term_link( $term_id, 'category' );
		} else {
			$term_link = get_permalink( get_option( 'page_for_posts' ) );
		}

		printf(
			'<div id="shopwell-posts-group" class="shopwell-posts-group">
				<ul class="shopwell-posts-group__items">
					<li><a class="%s" href="%s">%s</a></li>
					<li><a class="popular %s" href="%s">%s</a></li>
					<li><a class="featured %s" href="%s">%s</a></li>
				</ul>
			</div>',
			$current_tab == '' ? 'active' : '',
			esc_url( $term_link ),
			esc_html__( 'Recent Posts', 'shopwell' ),
			$current_tab == 'popular' ? 'active' : '',
			esc_url( $term_link ) . '?posts_group=popular',
			esc_html__( 'Popular Posts', 'shopwell' ),
			$current_tab == 'featured' ? 'active' : '',
			esc_url( $term_link ) . '?posts_group=featured',
			esc_html__( 'Featured Posts', 'shopwell' )
		);
	}

	/**
	 * Posts menu
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function menu() {
		$menu_slug = \Shopwell\Helper::get_option( 'blog_posts_heading_menu' );
		if ( empty( $menu_slug ) ) {
			return;
		}

		wp_nav_menu(
			array(
				'theme_location'  => '__no_such_location',
				'menu'            => esc_attr( $menu_slug ), // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
				'container'       => 'div',
				'container_class' => 'shopwell-posts-group',
				'container_id'    => 'shopwell-posts-group',
				'menu_id'         => 'shopwell-posts-group-menu',
				'menu_class'      => 'shopwell-posts-group__items shopwell-posts-group--menu nav-menu menu',
				'depth'           => 1,
			)
		);
	}
}
