<?php
/**
 * The template for displaying all single posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package Shopwell
 */

get_header();
?>
<?php if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'single' ) ) { ?>
	<div class="site-content-container">
	<div id="primary" class="content-area">
		<?php
		while ( have_posts() ) :
			the_post();
			$content_template = apply_filters( 'shopwell_content_single_template_part', get_post_type() );
			get_template_part( 'template-parts/content/content-single', $content_template );

			do_action( 'shopwell_after_post_content' );

			// If comments are open or we have at least one comment, load up the comment template.
			if ( comments_open() || get_comments_number() ) :
				comments_template();
			endif;

		endwhile; // End of the loop.
		?>

	</div>
	<?php
	get_sidebar();
	?>
	</div>
<?php } ?>
<?php
get_footer();
