<?php
/**
 * The sidebar containing the main widget area
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Shopwell
 */

$has_sidebar = apply_filters( 'shopwell_get_sidebar', false );

if ( ! $has_sidebar ) {
	return;
}

if ( is_single() ) {
	$sidebar = 'single-sidebar';
} else {
	$sidebar = 'blog-sidebar';
}

if ( \Shopwell\Helper::is_catalog() ) {
	$sidebar = 'catalog-sidebar';
}

if ( ! is_active_sidebar( $sidebar ) ) {
	return;
}

$sidebar_class = apply_filters( 'shopwell_primary_sidebar_classes', $sidebar );

$sidebar_ID = $sidebar == 'catalog-sidebar' ? 'mobile-filter-sidebar-panel' : 'primary-sidebar';
$sidebar_ID = apply_filters( 'shopwell_primary_sidebar_ID', $sidebar_ID );

?>

<aside id=<?php echo esc_attr( $sidebar_ID ); ?>
		class="widget-area primary-sidebar <?php echo esc_attr( $sidebar_class ); ?>">
	<?php dynamic_sidebar( $sidebar ); ?>
</aside><!-- #primary -->
