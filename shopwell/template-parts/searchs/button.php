<?php
/**
 * Template part for displaying the search items
 *
 * @package Shopwell
 */

$class                       = isset( $args['search_items_button_class'] ) && ! empty( $args['search_items_button_class'] ) ? $args['search_items_button_class'] : '';
$search_items_button_display = isset( $args['search_items_button_display'] ) && ! empty( $args['search_items_button_display'] ) ? $args['search_items_button_display'] : '';

?>

<button class="header-search__button shopwell-button <?php echo esc_attr( $class ); ?>" type="submit" aria-label="<?php esc_attr__( 'Search Button', 'shopwell' ); ?>">
	<?php
	if ( $search_items_button_display !== 'icon' ) {
		esc_html_e( 'Search', 'shopwell' );
	} else {

		echo '<span class="shopwell-button__icon">' . \Shopwell\Icon::get_svg( 'search' ) . '</span>';
	}
	?>
</button>
