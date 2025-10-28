<?php
/**
 * Template part for displaying help center
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Shopwell
 */

?>
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<a class="help-title" href="<?php the_permalink(); ?>"><?php \Shopwell\Help_Center\Article::get_loop_title(); ?></a>
	<?php \Shopwell\Help_Center\Article::get_loop_content(); ?>
</article>
