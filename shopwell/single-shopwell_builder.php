<?php
/**
 * Template for displaying single wc template
 *
 * @package Shopwell
 */

get_header(); ?>
<?php
$classes = apply_filters( 'shopwell_builder_classes', '' );
$classes = ! empty( $classes ) ? implode( ' ', $classes ) : '';
?>

<?php
while ( have_posts() ) :
	the_post();
	?>
	<div class="<?php echo esc_attr( $classes ); ?>">
		<?php the_content(); ?>
	</div>

<?php endwhile; ?>

<?php get_footer(); ?>
