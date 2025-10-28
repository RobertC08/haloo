<?php
/**
 * Template for displaying single Popup
 *
 * @package Shopwell
 */

get_header(); ?>

<div id="primary" class="content-area">
	<?php
	while ( have_posts() ) :
		the_post();
		?>

		<?php get_template_part( 'template-parts/content/content', 'popup' ); ?>


	<?php endwhile; ?>

</div>
<?php get_footer(); ?>
