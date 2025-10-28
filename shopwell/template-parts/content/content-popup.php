<?php
/**
 * Template part for displaying page content in page.php
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Shopwell
 */
$css_class = 'shopwell-single-popup shopwell-popup-' . get_the_ID();
?>

<div id="post-<?php the_ID(); ?>" class="<?php echo esc_attr( $css_class ); ?>" >
	<div class="shopwell-popup__backdrop"></div>
	<div class="shopwell-popup__content">
		<?php the_content(); ?>
		<?php echo \Shopwell\Icon::get_svg( 'close', 'ui', 'class=shopwell-popup__close' ); ?>
	</div>
</div>
