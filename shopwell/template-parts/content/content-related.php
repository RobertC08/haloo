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
	<?php \Shopwell\Blog\Post_Related::thumbnail(); ?>
	<?php \Shopwell\Blog\Post_Related::title(); ?>
	<div class="entry-meta">
		<?php \Shopwell\Blog\Post_Related::date(); ?>
		<?php \Shopwell\Blog\Post_Related::comment(); ?>
	</div>
</article>
