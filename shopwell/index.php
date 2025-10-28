<?php
/**
 * The main template file
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Shopwell
 */

get_header();
?>
<?php if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'archive' ) ) { ?>
	<div class="site-content-container">
		<?php do_action( 'shopwell_before_open_blog_main' ); ?>
		<div id="primary" class="content-area">
			<main id="main" class="site-main">
				<?php if ( have_posts() ) : ?>
					<?php do_action( 'shopwell_before_blog_main_content' ); ?>
					<?php
					/* Start the Loop */
					while ( have_posts() ) :
						the_post();
						$content_template = apply_filters( 'shopwell_content_template_part', get_post_type() );
						/*
							* Include the Post-Type-specific template for the content.
							* If you want to override this in a child theme, then include a file
							* called content-___.php (where ___ is the Post Type name) and that will be used instead.
							*/
						get_template_part( 'template-parts/content/content', $content_template );

					endwhile;
					?>
					<?php do_action( 'shopwell_after_blog_main_content' ); ?>
					<?php
				else :
					get_template_part( 'template-parts/content/content', 'none' );
				endif;
				?>
			</main>
		</div>
		<?php do_action( 'shopwell_after_close_blog_main' ); ?>
	<?php
	get_sidebar();
	?>
	</div>
<?php } ?>
<?php
get_footer();
