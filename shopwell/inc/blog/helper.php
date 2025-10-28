<?php
/**
 * Shopwell Blog helper functions and definitions.
 *
 * @package Shopwell
 */

namespace Shopwell\Blog;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shopwell Blog Helper initial
 */
class Helper {
	/**
	 * Get featured posts ids.
	 * Use transitent to cache the result.
	 * The transient will be deleted when updating a post.
	 *
	 * @param $args Query args
	 *
	 * @return array
	 */
	public static function get_post_ids_by_tags( $args = array() ) {
		// Only allow getting by tag.
		if ( empty( $args['tag'] ) ) {
			return false;
		}

		$query_args = wp_parse_args(
			$args,
			array(
				'post_type'              => 'post',
				'post_status'            => 'publish',
				'no_found_rows'          => true,
				'update_post_term_cache' => false,
				'update_post_meta_cache' => false,
				'cache_results'          => false,
				'ignore_sticky_posts'    => true,
				'suppress_filters'       => false,
			)
		);

		$query_args['fields'] = 'ids';
		$query_hash           = md5( serialize( $query_args ) );
		$transient_key        = 'shopwell_featured_post_ids';
		$cache                = (array) get_transient( $transient_key );

		if ( ! isset( $cache[ $query_hash ] ) ) {
			$query                = new \WP_Query( $query_args );
			$cache[ $query_hash ] = $query->posts;

			set_transient( $transient_key, $cache, DAY_IN_SECONDS );
			wp_reset_postdata();
		}

		return apply_filters( __FUNCTION__, $cache[ $query_hash ] );
	}

	/**
	 * Related post
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public static function get_related_terms( $term, $post_id = null ) {
		$post_id     = $post_id ? $post_id : get_the_ID();
		$terms_array = array( 0 );

		$terms = wp_get_post_terms( $post_id, $term );
		foreach ( $terms as $term ) {
			$terms_array[] = $term->term_id;
		}

		return array_map( 'absint', $terms_array );
	}
}
