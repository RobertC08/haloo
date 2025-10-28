<?php
/**
 * Template part for displaying posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Shopwell
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<header class="entry-header">
		<?php if ( \Shopwell\Helper::get_option( 'post_featured_image_position' ) == 'top' ) { ?>
			<?php \Shopwell\Blog\Post::image(); ?>
		<?php } ?>
		<?php \Shopwell\Blog\Post::category(); ?>
		<?php \Shopwell\Blog\Post::title( 'h1' ); ?>
		<div class="entry-meta">
			<?php \Shopwell\Blog\Post::author(); ?>
			<?php \Shopwell\Blog\Post::date(); ?>
			<?php \Shopwell\Blog\Post::comment(); ?>
			<?php \Shopwell\Blog\Post::share(); ?>
		</div>
		<?php if ( \Shopwell\Helper::get_option( 'post_featured_image_position' ) != 'top' ) { ?>
			<?php \Shopwell\Blog\Post::image(); ?>
		<?php } ?>
	</header>
	<div class="entry-content">
		<?php the_content(); ?>
	</div>
	<footer class="entry-footer">
		<?php \Shopwell\Blog\Post::tags(); ?>
		<?php \Shopwell\Blog\Post::share_link(); ?>
	</footer>
</article>
