<?php
/**
 * Shopwell Blog Post functions and definitions.
 *
 * @package Shopwell
 */

namespace Shopwell\Blog;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shopwell Post
 */
class Post {

	/**
	 * Instance
	 *
	 * @var $instance
	 */
	protected static $instance = null;


	/**
	 * $fields
	 *
	 * @var $fields
	 */
	protected static $fields = array();

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
	}

	/**
	 * Set Display
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function set_display( $name, $value = true ) {
		self::$fields[ $name ] = $value;
	}


	/**
	 * Get Display
	 *
	 * @since 1.0.0
	 *
	 * @return boolean
	 */
	public static function get_display( $name ) {
		if ( isset( self::$fields[ $name ] ) ) {
			return self::$fields[ $name ];
		}

		return false;
	}

	/**
	 * Remove Display
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function remove_display( $name ) {
		if ( isset( self::$fields[ $name ] ) ) {
			unset( self::$fields[ $name ] );
		}
	}

	/**
	 * Get entry thumbmail
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function thumbnail() {
		if ( ! has_post_thumbnail() ) {
			return;
		}

		$size = 'shopwell-post-thumbnail-medium';
		$size = apply_filters( 'shopwell_get_post_thumbnail_size', $size );

		$get_image = wp_get_attachment_image( get_post_thumbnail_id( get_the_ID() ), $size );

		if ( empty( $get_image ) ) {
			return;
		}

		printf(
			'<a class="post-thumbnail" href="%s" aria-hidden="true" tabindex="-1">%s%s</a>',
			esc_url( get_permalink() ),
			$get_image,
			self::get_format_icon()
		);
	}

	/**
	 * Get format
	 *
	 * @since 1.0.0
	 *
	 * @return SVG
	 */
	public static function get_format_icon() {
		$icon = '';
		switch ( get_post_format() ) {
			case 'video':
				$icon = \Shopwell\Icon::get_svg( 'video', 'ui', array( 'class' => 'post-format-icon icon-video' ) );
				break;

			case 'gallery':
				$icon = \Shopwell\Icon::get_svg( 'gallery', 'ui', array( 'class' => 'post-format-icon icon-gallery' ) );
				break;
		}

		return $icon;
	}

	/**
	 * Get post image
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function image() {
		if ( ! self::get_display( 'image' ) ) {
			return;
		}

		if ( ! has_post_thumbnail() ) {
			return;
		}
		$html = '';
		switch ( get_post_format() ) {
			case 'gallery':
				$images = get_post_meta( get_the_ID(), 'images' );

				if ( empty( $images ) ) {
					break;
				}

				$gallery = array();
				foreach ( $images as $image ) {
					$gallery[] = wp_get_attachment_image( $image, 'full', null, array( 'class' => 'swiper-slide' ) );
				}
				$html .= sprintf(
					'<div class="entry-thumbnail entry-gallery swiper-container"><div class="swiper-wrapper">%s</div><div class="shopwell-swiper-pagination swiper-pagination--background swiper-pagination--light"></div>%s%s</div>',
					implode( '', $gallery ),
					\Shopwell\Icon::get_svg( 'left', 'ui', array( 'class' => 'swiper-button shopwell-swiper-button-prev' ) ),
					\Shopwell\Icon::get_svg( 'right', 'ui', array( 'class' => 'swiper-button shopwell-swiper-button-next' ) )
				);
				break;

			case 'video':
				$video = get_post_meta( get_the_ID(), 'video', true );
				if ( ! $video ) {
					break;
				}

				// If URL: show oEmbed HTML
				if ( filter_var( $video, FILTER_VALIDATE_URL ) ) {
					if ( $oembed = @wp_oembed_get( $video, array( 'width' => 1140 ) ) ) {
						$html .= '<div class="entry-thumbnail entry-video">' . $oembed . '</div>';
					} else {
						$atts = array(
							'src'   => $video,
							'width' => 1140,
						);

						if ( has_post_thumbnail() ) {
							$atts['poster'] = get_the_post_thumbnail_url( get_the_ID(), 'full' );
						}
						$html .= '<div class="entry-thumbnail entry-video">' . wp_video_shortcode( $atts ) . '</div>';
					}
				} // If embed code: just display
				else {
					$html .= '<div class="entry-thumbnail entry-video">' . $video . '</div>';
				}
				break;

			default:
				$html = '<div class="entry-thumbnail">' . get_the_post_thumbnail( get_the_ID(), 'full' ) . '</div>';

				break;
		}

		echo apply_filters( __FUNCTION__, $html, get_post_format() );
	}


	/**
	 * Get entry title
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function title( $tag = 'h2' ) {
		if ( is_single() ) {
			// Single post page ke liye title bina link ke
			echo '<' . $tag . ' class="entry-title">' . get_the_title() . '</' . $tag . '>';
		} else {
			// Baaki pages ke liye title link ke saath
			the_title( '<' . $tag . ' class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></' . $tag . '>' );
		}
	}

	/**
	 * Get category
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function category() {
		if ( ! self::get_display( 'category' ) ) {
			return;
		}

		$categories         = get_the_category();
		$limited_categories = array_slice( $categories, 0, 4 ); // Limit to 5
		echo '<div class="entry-category">';
		foreach ( $limited_categories as $category ) {
			// Display category link
			echo '<a href="' . esc_url( get_category_link( $category->term_id ) ) . '">' . esc_html( $category->name ) . '</a> ';
		}
		echo '</div>';

		/*
		Backup Code
		echo '<div class="entry-category">';
		the_category( ' ' );
		echo '</div>';
		*/
	}


	/**
	 * Meta author
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function author() {
		if ( ! self::get_display( 'author' ) ) {
			return;
		}

		$byline = sprintf(
		/* translators: %s: post author. */
			esc_html_x( 'By %s', 'post author', 'shopwell' ),
			'<a class="url fn n" href="' . esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) . '">' . esc_html( get_the_author() ) . '</a>'
		);

		printf( '<div class="entry-meta__author">%s</div>', $byline );
	}

	/**
	 * Meta date
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function date() {
		printf( '<div class="entry-meta__date">%s</div>', esc_html( get_the_date() ) );
	}

	/**
	 * Meta comment
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function comment() {
		echo '<div class="entry-meta__comments">' . \Shopwell\Icon::get_svg( 'comment-mini' ) . get_comments_number() . '</div>';
	}

	/**
	 * Get Excerpt
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function excerpt() {
		if ( ! self::get_display( 'excerpt' ) ) {
			return;
		}

		$length = self::get_display( 'excerpt' );

		if ( empty( $length ) ) {
			return;
		}

		self::get_excerpt( $length );
	}

	/**
	 * Get Excerpt
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function get_excerpt( $length ) {
		echo '<div class="entry-excerpt">';

		echo \Shopwell\Helper::get_content_limit( $length, '' );

		echo '</div>';
	}

	/**
	 * Readmore button
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function button() {
		if ( ! self::get_display( 'button' ) ) {
			return;
		}

		printf( '<div class="entry-read-more"><a class="shopwell-button shopwell-button--base shopwell-button--medium shopwell-button--bg-color-black" href="%s">%s</a></div>', get_permalink(), esc_html__( 'Read More', 'shopwell' ) );
	}

	/**
	 * Meta tag
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function tags() {
		if ( has_tag() == false ) {
			return;
		}

		if ( has_tag() ) :
			the_tags( '<div class="entry-tags">', ' ', '</div>' );
		endif;
	}

	/**
	 * Get entry share social
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function share() {
		if ( ! self::get_display( 'share' ) ) {
			return;
		}
		echo '<div class="entry-meta__share">';
		echo \Shopwell\Icon::get_svg( 'share-mini' );
		esc_html_e( 'Share', 'shopwell' );
		self::share_link();
		echo '</div>';
	}

	/**
	 * Share social
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function share_link() {
		if ( ! self::get_display( 'share' ) ) {
			return;
		}

		echo \Shopwell\Helper::share_socials();
	}
}
