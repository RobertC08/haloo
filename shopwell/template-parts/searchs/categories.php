<?php
/**
 * Template part for displaying the search items
 *
 * @package Shopwell
 */

if ( \Shopwell\Helper::get_option( 'header_search_type' ) == '' ) {
	return;
}

$show_categories = isset( $args['show_categories'] ) ? $args['show_categories'] : false;
$taxonomy        = isset( $args['taxonomy'] ) ? $args['taxonomy'] : 'category';

$type      = \Shopwell\Helper::get_option( 'header_search_type' ) == 'product' || \Shopwell\Helper::get_option( 'header_search_type' ) == 'adaptive' ? 'product_cat' : 'category_name';
$term_slug = 0;

if ( isset( $_GET['product_cat'] ) ) {
	$term_slug = $_GET['product_cat'];
}

if ( isset( $_GET['category_name'] ) ) {
	$term_slug = $_GET['category_name'];
}

$term_name     = get_term_by( 'slug', $term_slug, $taxonomy ) ? get_term_by( 'slug', $term_slug, $taxonomy )->name : '';
$category_name = ! empty( $term_name ) ? $term_name : $show_categories;
?>

<?php if ( ! empty( $show_categories ) ) : ?>
	<div class="header-search__categories-label" tabindex="0">
		<span class="header-search__categories-text"><?php echo esc_html( $category_name ); ?></span><?php echo \Shopwell\Icon::get_svg( 'select-arrow' ); ?>
	</div>
	<?php if ( \Shopwell\Helper::get_option( 'header_search_type' ) ) : ?>
		<input class="category-name" type="hidden" name="<?php echo esc_attr( $type ); ?>" value="<?php echo isset( $term_slug ) ? $term_slug : 0; ?>">
	<?php endif; ?>
<?php endif; ?>
