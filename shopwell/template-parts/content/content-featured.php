<?php
/**
 * Template part for displaying featured loop content
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Shopwell
 */

?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'swiper-slide' ); ?>>
	<?php \Shopwell\Blog\Post_Featured::thumbnail(); ?>
	<div class="entry-summary">
		<?php \Shopwell\Blog\Post_Featured::category(); ?>
		<?php \Shopwell\Blog\Post_Featured::title(); ?>
		<div class="entry-meta">
			<?php \Shopwell\Blog\Post_Featured::date(); ?>
			<?php \Shopwell\Blog\Post_Featured::comment(); ?>
		</div>
	</div>
</article>
