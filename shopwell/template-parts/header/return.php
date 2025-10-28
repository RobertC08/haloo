<?php
/**
 * Template part for displaying the return button
 *
 * @package Shopwell
 */

$return_class        = isset( $args['return_class'] ) ? $args['return_class'] : '';
$return_button_class = isset( $args['return_button_class'] ) ? $args['return_button_class'] : '';
$return_text         = isset( $args['return_text'] ) && ! empty( $args['return_text'] ) ? $args['return_text'] : '';
$return_type         = isset( $args['return_type'] ) && ! empty( $args['return_type'] ) ? $args['return_type'] : 'default';
$return_link         = isset( $args['return_link'] ) && ! empty( $args['return_link'] ) ? $args['return_link'] : get_home_url();

?>
<div class="header-return-button <?php echo esc_attr( $return_class ); ?>">
	<a href="<?php echo esc_url( $return_link ); ?>" class="<?php echo esc_attr( $return_button_class ); ?>">
		<?php if ( $return_type == 'default' ) : ?>
			<?php echo \Shopwell\Icon::get_svg( 'left' ); ?>
		<?php endif; ?>
		<?php echo esc_html( $return_text ); ?>
	</a>
</div>
