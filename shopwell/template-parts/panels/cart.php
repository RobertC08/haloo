<?php
/**
 * Template part for displaying the cart panel
 *
 * @package Shopwell
 */

if ( ! function_exists( 'WC' ) ) {
	return;
}
if ( \Shopwell\Helper::get_option( 'header_cart_icon_behaviour' ) == 'page' ) {
	return;
}
$counter = ! empty( WC()->cart ) ? WC()->cart->get_cart_contents_count() : 0;
?>

<div id="cart-panel" class="offscreen-panel cart-panel woocommerce">
	<div class="panel__backdrop"></div>
	<div class="panel__container">
		<?php echo \Shopwell\Icon::get_svg( 'close', 'ui', 'class=panel__button-close' ); ?>

		<div class="panel__header">
			<?php echo esc_html__( 'Shopping Cart ', 'shopwell' ); ?><span class="cart-panel__counter"><?php echo '(' . intval( $counter ) . ')'; ?></span>
		</div>

		<div class="panel__content shopwell-skin--subtle shopwell-qty-medium">
			<div class="widget_shopping_cart_content ">
				<?php woocommerce_mini_cart(); ?>
			</div>
		</div>
	</div>
</div>
