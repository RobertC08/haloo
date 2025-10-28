<?php
/**
 * Posts functions and definitions.
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
 * Posts initial
 */
class Main {
	/**
	 * Instance
	 *
	 * @var $instance
	 */
	protected static $instance = null;


	/**
	 * $Post
	 *
	 * @var int $post
	 */
	protected $trending_count = 0;


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
		$this->load_sections();
	}

	/**
	 * Instantiate the object.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function load_sections() {
		// Blog content layout
		add_filter( 'shopwell_site_layout', array( $this, 'layout' ) );

		// Trending Posts
		$this->trending_posts();

		// Featured Posts
		if ( Helper::get_option( 'blog_featured_posts' ) ) {
			if ( \Shopwell\Helper::get_option( 'blog_featured_position' ) == 'under' ) {
				add_action( 'shopwell_before_site_content_close', array( $this, 'featured_posts' ), 10 );
			} else {
				add_action( 'shopwell_after_site_content_open', array( $this, 'featured_posts' ), 30 );
			}
		}

		// Post Tabs
		if ( Helper::get_option( 'blog_posts_heading' ) ) {
			add_action( 'shopwell_before_blog_main_content', array( $this, 'posts_heading' ) );
		}

		// Blog Posts
		add_filter( 'post_class', array( $this, 'post_classes' ), 10, 3 );
		add_filter( 'shopwell_get_post_thumbnail_size', array( $this, 'post_thumbnail_size' ) );

		// Set attributes for post loop
		\Shopwell\Blog\Post::set_display( 'category' );
		if ( Helper::get_option( 'blog_layout' ) == 'default' ) {
			\Shopwell\Blog\Post::set_display( 'excerpt', Helper::get_option( 'excerpt_length' ) );
		} elseif ( Helper::get_option( 'blog_layout' ) == 'classic' ) {
			\Shopwell\Blog\Post::set_display( 'excerpt', Helper::get_option( 'excerpt_length' ) );
			\Shopwell\Blog\Post::set_display( 'author' );
			\Shopwell\Blog\Post::set_display( 'button' );
		}

		// Recent Post Heading
		if ( Helper::get_option( 'blog_recent_posts_heading' ) ) {
			add_action( 'shopwell_before_blog_main_content', array( $this, 'recent_posts_heading' ) );
		}

		// Navigation
		add_action( 'shopwell_after_blog_main_content', array( $this, 'navigation' ), 30 );

		// Sidebar
		add_filter( 'shopwell_get_sidebar', array( $this, 'sidebar' ), 10 );

		// Body Class
		add_filter( 'body_class', array( $this, 'body_classes' ) );
	}


	/**
	 * Post Tabs
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function posts_heading() {
		switch ( \Shopwell\Helper::get_option( 'blog_posts_heading_type' ) ) {
			case 'recent':
				\Shopwell\Blog\Posts_Heading::recent_heading();
				break;

			case 'group':
				\Shopwell\Blog\Posts_Heading::group();
				break;

			case 'menu':
				\Shopwell\Blog\Posts_Heading::menu();
				break;
		}
	}

	/**
	 * Layout
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function layout( $layout ) {
		if ( 'grid' !== Helper::get_option( 'blog_layout' ) && is_active_sidebar( 'blog-sidebar' ) ) {
			$layout = 'content-sidebar';
		}

		return $layout;
	}

	/**
	 * Get Classes
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function trending_posts() {
		if ( ! Helper::get_option( 'blog_trending_posts' ) ) {
			return;
		}

		add_action( 'shopwell_after_site_content_open', array( $this, 'get_trending_posts' ), 20 );

		if ( \Shopwell\Helper::get_option( 'blog_trending_layout' ) == 2 ) {
			add_action( 'shopwell_before_trending_posts_content', array( $this, 'open_box_carousel_wrapper' ) );
			add_action( 'shopwell_after_trending_posts_content', array( $this, 'close_box_carousel_wrapper' ) );
		}

		if ( \Shopwell\Helper::get_option( 'blog_trending_layout' ) == 1 ) {
			add_action( 'shopwell_before_trending_post_loop_content', array( $this, 'open_trending_posts_small' ) );
			add_action( 'shopwell_after_trending_post_loop_content', array( $this, 'close_trending_posts_small' ) );

			\Shopwell\Blog\Post::set_display( 'button_class', 'small' );
		}
	}

	/**
	 * Trending Posts
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function get_trending_posts() {
		$layout      = \Shopwell\Helper::get_option( 'blog_trending_layout' );
		$number_item = 1;

		if ( $layout == 1 ) {
			$number_item = 3;
		} elseif ( $layout == 2 ) {
			$number_item = \Shopwell\Helper::get_option( 'blog_trending_carousel_number' );
		}

		$args = \Shopwell\Blog\Helper::get_post_ids_by_tags(
			array(
				'posts_per_page' => $number_item,
				'tag'            => \Shopwell\Helper::get_option( 'blog_trending_tag' ),
			)
		);

		// Ensure $args is an array
		if ( ! is_array( $args ) || empty( $args ) ) {
			$query_args = array(
				'post_type'              => 'post',
				'post_status'            => 'publish',
				'fields'                 => 'ids',
				'orderby'                => 'rand',
				'posts_per_page'         => $number_item,
				'no_found_rows'          => true,
				'update_post_term_cache' => false,
				'update_post_meta_cache' => false,
				'cache_results'          => false,
				'ignore_sticky_posts'    => true,
				'suppress_filters'       => false,
			);

			$query = new \WP_Query( $query_args );

			$args = $query->posts;
		}

		if ( in_array( $layout, array( '2', '3' ) ) ) {
			\Shopwell\Blog\Post::set_display( 'td_excerpt', \Shopwell\Helper::get_option( 'blog_trending_length' ) );
			\Shopwell\Blog\Post::set_display( 'author' );
		}

		get_template_part( 'template-parts/post/trending', 'posts', $args );
	}

	/**
	 * Open box trending posts small
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function open_box_carousel_wrapper() {
		echo '<div class="swiper-container"><div class="trending-posts__wrapper swiper-wrapper">';
	}

	/**
	 * Open box trending posts small
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function close_box_carousel_wrapper() {
		echo '</div></div>';

		echo \Shopwell\Icon::get_svg( 'arrow-left-long', 'ui', array( 'class' => 'swiper-button shopwell-swiper-button-prev swiper-button--raised' ) );
		echo \Shopwell\Icon::get_svg( 'arrow-right-long', 'ui', array( 'class' => 'swiper-button shopwell-swiper-button-next swiper-button--raised' ) );
	}

	/**
	 * Open box trending posts small
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function open_trending_posts_small() {
		if ( $this->trending_count == 1 ) {
			echo '<div class="trending-posts--small">';
		}
	}

	/**
	 * Close box trending posts small
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function close_trending_posts_small( $trending ) {
		$count_item = $trending->post_count == 2 ? 1 : 2;

		if ( $this->trending_count == $count_item ) {
			echo '</div>';
		}

		++$this->trending_count;
	}

	/**
	 * Featured Posts
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function featured_posts() {
		// Initialize $args as an empty array
		$args = array();

		// Get post IDs by tags for featured posts
		$featured_post_ids = \Shopwell\Blog\Helper::get_post_ids_by_tags(
			array(
				'posts_per_page' => \Shopwell\Helper::get_option( 'blog_featured_posts_total' ),
				'tag'            => \Shopwell\Helper::get_option( 'blog_featured_tag' ),
			)
		);

		// If the result is valid, merge it into $args
		if ( ! empty( $featured_post_ids ) ) {
			$args['post__in'] = $featured_post_ids;
		}

		// Check for duplicate posts and trending posts condition
		if ( ! apply_filters( 'shopwell_featured_posts_duplicate', false ) && \Shopwell\Helper::get_option( 'blog_trending_posts' ) ) {
			$trending_post_ids = \Shopwell\Blog\Helper::get_post_ids_by_tags(
				array(
					'tag' => \Shopwell\Helper::get_option( 'blog_trending_tag' ),
				)
			);

			// Ensure trending post IDs are valid before excluding them
			if ( ! empty( $trending_post_ids ) ) {
				$args['post__not_in'] = $trending_post_ids;
			}
		}

		// Pass the args array to the template part
		get_template_part( 'template-parts/post/featured', 'posts', $args );
	}

	/**
	 * Recent Post Heading
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function recent_posts_heading() {
		echo '<h4 class="shopwell-recent-post__heading">' . apply_filters( 'shopwell_recent_posts_heading', esc_html__( 'Recent Posts', 'shopwell' ) ) . '</h4>';
	}


	/**
	 * Navigation Posts
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function navigation() {
		$navigation = new \Shopwell\Blog\Navigation();
		if ( 'loadmore' == \Shopwell\Helper::get_option( 'blog_nav_type' ) ) {
			$navigation::load_more();
		} else {
			$navigation::numberic();
		}
	}

	/**
	 * Add a class of blog layout to posts
	 *
	 * @param array $classes
	 * @param array $class
	 * @param int   $post_id
	 *
	 * @return mixed
	 */
	public function post_classes( $classes, $class, $post_id ) {
		if ( 'post' != get_post_type( $post_id ) || ! is_main_query() ) {
			return $classes;
		}

		if ( $this->get_post_large() ) {
			$classes[] = 'post-large';
		}

		return $classes;
	}

	/**
	 * Get post thumbnail size
	 *
	 * @param $size
	 *
	 * @return mixed
	 */
	public function post_thumbnail_size( $size ) {
		$size = $this->get_post_large() ? 'shopwell-post-thumbnail-large' : $size;

		return $size;
	}


	/**
	 * Get post large
	 *
	 * @return bool
	 */
	public function get_post_large() {
		if ( 'classic' == \Shopwell\Helper::get_option( 'blog_layout' ) ) {
			global $wp_query;
			$current_post = $wp_query->current_post;

			if ( 'loadmore' == \Shopwell\Helper::get_option( 'blog_nav_type' ) ) {
				$paged         = get_query_var( 'paged' );
				$paged         = min( 0, $paged - 1 );
				$current_post += $paged * get_query_var( 'posts_per_page' );
			}

			if ( $current_post == 0 || 0 === $current_post % 5 ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get Sidebar
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function sidebar() {
		if ( ! is_active_sidebar( 'blog-sidebar' ) ) {
			return false;
		}

		if ( \Shopwell\Helper::get_option( 'blog_layout' ) == 'grid' ) {
			return false;
		}

		return true;
	}

	/**
	 * Classes Body
	 */
	public function body_classes( $classes ) {
		$classes[] = 'shopwell-blog-page';
		$classes[] = 'blog--' . \Shopwell\Helper::get_option( 'blog_layout' );

		if ( ! is_active_sidebar( 'blog-sidebar' ) ) {
			$classes[] = 'no-sidebar';
		}

		return $classes;
	}
}
