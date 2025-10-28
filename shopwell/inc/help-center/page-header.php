<?php
/**
 * Page Header functions and definitions.
 *
 * @package Shopwell
 */

namespace Shopwell\Help_Center;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Page_Header initial
 *
 * @since 1.0.0
 */
class Page_Header {
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
		if ( ! \Shopwell\Helper::is_help_center_page() ) {
			add_filter( 'shopwell_get_page_header_elements', array( $this, 'page_header_elements' ) );

			add_filter( 'shopwell_breadcrumbs_home_url', array( $this, 'breadcrumbs_home_url' ) );
			add_filter( 'shopwell_breadcrumbs_args', array( $this, 'breadcrumbs_args' ) );
			add_filter( 'shopwell_breadcrumbs_search_item', array( $this, 'breadcrumbs_search_item' ) );

			add_action( 'get_the_archive_title', array( $this, 'get_archive_title' ), 30 );

			add_filter( 'shopwell_page_header_classes', array( $this, 'page_header_classes' ) );

			if ( is_singular( 'sw_help_article' ) ) {
				add_filter( 'shopwell_page_header_title', array( $this, 'help_single_title' ) );
				add_filter( 'shopwell_breadcrumbs_get_parent_post', array( $this, 'get_parent_help_single' ), 20, 3 );
			}
		} else {
			add_filter( 'shopwell_get_page_header_elements', array( $this, 'hc_page_header_elements' ) );
		}
	}

	/**
	 * layout
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function page_header_classes( $classes ) {
		$classes .= ' page-header-hc';

		return $classes;
	}

	/**
	 * layout
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function page_header_elements( $item ) {
		$item[] = 'breadcrumb';

		return $item;
	}

	/**
	 * Breadcrumbs Args
	 *
	 * @since  1.0.0
	 *
	 * @return string
	 */
	public function breadcrumbs_args( $args ) {
		$page_id = get_option( 'help_center_page_id' );
		if ( ! empty( $page_id ) ) {
			$title                  = get_the_title( $page_id );
			$args['labels']['home'] = $title;
		}

		return $args;
	}

	/**
	 * Breadcrumbs Home Url
	 *
	 * @since  1.0.0
	 *
	 * @return string
	 */
	public function breadcrumbs_home_url( $url ) {
		$page_id = get_option( 'help_center_page_id' );
		if ( ! empty( $page_id ) ) {
			$url = get_permalink( $page_id );
		}

		return $url;
	}


	/**
	 * Breadcrumbs Search Item
	 *
	 * @since  1.0.0
	 *
	 * @return string
	 */
	public function breadcrumbs_search_item( $item ) {
		$item = esc_html__( 'Search', 'shopwell' );
		return $item;
	}

	/**
	 * Show archive title
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_archive_title( $title ) {
		if ( is_search() ) {
			global $wp_query;
			$search_for = esc_html__( 'Search Results for', 'shopwell' );
			$title      = sprintf( '%s "%s"', $search_for, get_search_query() );
			if ( $wp_query->found_posts > 0 ) {
				$title .= sprintf( '<small>%s %s %s</small>', $wp_query->found_posts, $search_for, get_search_query() );
			}
		} elseif ( is_tax() ) {
			$term      = get_queried_object();
			$icon_html = $this->get_help_tax_media( $term->term_id, $term->name );
			$title     = $icon_html . single_term_title( '', false );
		}

		return $title;
	}

	/**
	 * Page header title
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function help_single_title( $title ) {
		$terms = get_the_terms( get_the_ID(), 'shopwell_help_cat' );
		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			return $title;
		}
		$icon_html = $this->get_help_tax_media( $terms[0]->term_id, $terms[0]->name );
		$term_name = $icon_html . $terms[0]->name;
		$title     = '<h2 class="page-header__title">' . $term_name . '</h2>';
		return $title;
	}

	/**
	 * Help single tax breadcrumb
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function get_parent_help_single( $content, $args ) {
		$terms = get_the_terms( get_the_ID(), 'shopwell_help_cat' );
		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			return $content;
		}
		$content = sprintf( $args, get_term_link( $terms[0], 'shopwell_help_cat' ), $terms[0]->name );
		return $content;
	}


	/**
	 * layout
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function get_help_tax_media( $term_id, $term_name ) {
		$icon_type  = get_term_meta( $term_id, 'sw_help_article_icon_type', true );
		$icon_image = absint( get_term_meta( $term_id, 'sw_help_article_icon_image_id', true ) );
		$icon_svg   = get_term_meta( $term_id, 'sw_help_article_icon_svg', true );

		$icon_html = '';

		if ( ! ( empty( $icon_svg ) ) || ! ( empty( $icon_image ) ) ) {
			if ( $icon_type == 'svg' ) {
				$icon_html = '<span class="shopwell-svg-icon">' . \Shopwell\Icon::sanitize_svg( $icon_svg ) . '</span>';
			} elseif ( $icon_type == 'image' ) {
				if ( $icon_image ) {
					$image     = wp_get_attachment_image_src( $icon_image, 'full' );
					$icon_html = '<span class="shopwell-svg-icon"><img src="' . esc_url( $image['0'] ) . '" alt="' . esc_attr( $term_name ) . '"/></span>';
				}
			}
		}

		return $icon_html;
	}

		/**
		 * layout
		 *
		 * @since 1.0.0
		 *
		 * @return bool
		 */
	public function hc_page_header_elements( $item ) {
		return false;
	}
}
