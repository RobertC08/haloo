<?php
/**
 * The template part for displaying related posts
 *
 * @package Shopwell
 */

$related_posts = new WP_Query(
	apply_filters(
		'shopwell_related_posts_args',
		array(
			'post_type'              => 'post',
			'posts_per_page'         => 3,
			'ignore_sticky_posts'    => 1,
			'no_found_rows'          => 1,
			'order'                  => 'rand',
			'post__not_in'           => array( $post->ID ),
			'tax_query'              => array(
				'relation' => 'OR',
				array(
					'taxonomy' => 'category',
					'field'    => 'term_id',
					'terms'    => \Shopwell\Blog\Helper::get_related_terms( 'category', $post->ID ),
					'operator' => 'IN',
				),
				array(
					'taxonomy' => 'post_tag',
					'field'    => 'term_id',
					'terms'    => \Shopwell\Blog\Helper::get_related_terms( 'post_tag', $post->ID ),
					'operator' => 'IN',
				),
			),
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false,
			'cache_results'          => false,
			'ignore_sticky_posts'    => true,
		)
	)
);

if ( ! $related_posts->have_posts() ) {
	return;
}

?>
	<div class="shopwell-posts-related">
		<h3 class="shopwell-posts-related__heading"><?php esc_html_e( 'You Might Also Like', 'shopwell' ); ?></h3>
		<div class="shopwell-posts-related__content">
			<?php
			while ( $related_posts->have_posts() ) :
				$related_posts->the_post();

				get_template_part( 'template-parts/content/content', 'related' );

				endwhile;
			?>
		</div>
	</div>
<?php
wp_reset_postdata();
