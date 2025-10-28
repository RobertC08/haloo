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
	<?php \Shopwell\Blog\Post::thumbnail(); ?>
	<div class="entry-summary">
		<?php \Shopwell\Blog\Post::category(); ?>
		<?php \Shopwell\Blog\Post::title(); ?>
		<?php \Shopwell\Blog\Post::excerpt(); ?>
		<div class="entry-meta">
			<?php \Shopwell\Blog\Post::author(); ?>
			<?php \Shopwell\Blog\Post::date(); ?>
			<?php \Shopwell\Blog\Post::comment(); ?>
		</div>
		<?php \Shopwell\Blog\Post::button(); ?>
	</div>
</article>
