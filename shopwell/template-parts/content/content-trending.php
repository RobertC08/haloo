<?php
/**
 * Template part for displaying trending posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Shopwell
 */

?>
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<?php \Shopwell\Blog\Post_Trending::thumbnail(); ?>
	<div class="entry-summary">
		<?php \Shopwell\Blog\Post_Trending::category(); ?>
		<?php \Shopwell\Blog\Post_Trending::title(); ?>
		<?php \Shopwell\Blog\Post_Trending::excerpt(); ?>
		<?php \Shopwell\Blog\Post_Trending::button(); ?>
		<div class="entry-meta">
			<?php \Shopwell\Blog\Post_Trending::author(); ?>
			<div class="entry-meta__content">
				<?php \Shopwell\Blog\Post_Trending::date(); ?>
				<?php \Shopwell\Blog\Post_Trending::comment(); ?>
			</div>
		</div>
	</div>
</article>
