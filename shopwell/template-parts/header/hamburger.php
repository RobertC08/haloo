<?php
/**
 * Template part for displaying the hamburger menu
 *
 * @package Shopwell
 */

$data_target = ( isset( $args['data_target'] ) && ! empty( $args['data_target'] ) ) ? $args['data_target'] : '';
?>

<div class="header-hamburger hamburger-menu" data-toggle="off-canvas" data-target="<?php echo esc_attr( $data_target ); ?>" tabindex="0">
	<?php echo \Shopwell\Icon::get_svg( 'hamburger', 'ui', 'class=hamburger__icon' ); ?>
</div>
