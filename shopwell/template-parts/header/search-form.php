<?php
/**
 * Template part for displaying the search icon
 *
 * @package Shopwell
 */

$search_class            = isset( $args['search_class'] ) ? $args['search_class'] : '';
$show_categories         = isset( $args['show_categories'] ) ? $args['show_categories'] : false;
$search_items_form_class = isset( $args['search_items_form_class'] ) ? $args['search_items_form_class'] : '';
$search_style_css        = isset( $args['search_style_css'] ) ? esc_attr( $args['search_style_css'] ) : '';

$attributes = $search_style_css ? 'data-width=' . $search_style_css . ' style="max-width:' . $search_style_css . 'px"' : '';
?>

<div class="header-search <?php echo esc_attr( $search_class ); ?>" <?php echo esc_attr( $attributes ); ?>>
	<?php if ( \Shopwell\Helper::get_option( 'header_search_bar' ) == 'default' ) { ?>
		<form class="header-search__form <?php echo esc_attr( $search_items_form_class ); ?>" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
			<div class="header-search__container <?php echo esc_attr( $args['search_items_input_class'] ); ?>">
				<?php \Shopwell\Header\Search::items( $args ); ?>

				<?php echo \Shopwell\Header\Search::trendings( $args ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

				<?php
				if ( ! empty( $show_categories ) ) :
					if ( ( \Shopwell\Helper::get_option( 'header_search_type' ) && \Shopwell\Helper::get_option( 'header_search_type' ) !== 'adaptive' ) || ( \Shopwell\Helper::get_option( 'header_search_type' ) == 'adaptive' && \Shopwell\Header\Search::type() == 'product' ) ) {
						\Shopwell\Header\Search::categories_items( $show_categories );
					}
				endif;
				?>

				<?php
				if ( \Shopwell\Helper::get_option( 'header_search_ajax' ) ) {
					echo \Shopwell\Icon::get_svg( 'close', 'ui', 'class=close-search-results' );
					echo '<div class="header-search__results search-results woocommerce"></div>';
				}
				?>
			</div>

			<?php if ( $args['search_items_button_display'] !== 'none' ) : ?>
				<?php echo get_template_part( 'template-parts/searchs/button', '', $args ); ?>
			<?php endif; ?>

			<input type="hidden" name="post_type" class="header-search__post-type" value="<?php echo isset( $_GET['post_type'] ) && ! empty( $_GET['post_type'] ) ? esc_attr( $_GET['post_type'] ) : esc_attr( \Shopwell\Header\Search::type() ); ?>">
		</form>
		<?php
	} else {
		echo do_shortcode( \Shopwell\Helper::get_option( 'header_search_shortcode' ) );
		?>
	<?php } ?>
</div>
