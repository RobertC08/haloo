<?php
/**
 * Template part for displaying the cart icon
 *
 * @package Shopwell
 */

if ( ! function_exists( 'WC' ) ) {
	return;
}

$cart_display     = isset( $args['cart_display'] ) ? $args['cart_display'] : 'icon-text';
$cart_icon        = isset( $args['cart_icon'] ) ? $args['cart_icon'] : 'bag';
$cart_text        = isset( $args['cart_text'] ) && ! empty( $args['cart_text'] ) ? $args['cart_text'] : '';
$cart_classes     = isset( $args['cart_classes'] ) ? $args['cart_classes'] : '';
$cart_text_class  = isset( $args['cart_text_class'] ) ? $args['cart_text_class'] : '';
$cart_data_toggle = isset( $args['cart_data_toggle'] ) ? $args['cart_data_toggle'] : '';

$hidden  = WC()->cart->is_empty() ? 'hidden' : '';
$counter = ! empty( WC()->cart ) ? WC()->cart->get_cart_contents_count() : 0;
?>
<div class="header-cart">
	<a href="<?php echo esc_url( wc_get_cart_url() ); ?>" data-toggle="<?php echo esc_attr( $cart_data_toggle ); ?>" data-target="cart-panel" class="shopwell-button <?php echo esc_attr( $cart_classes ); ?>">
		<?php if ( $cart_display == 'icon' || $cart_display == 'icon-text' ) : ?>
			<span class="shopwell-button__icon">
				<?php
				switch ( $cart_icon ) {
					case 'trolley':
					case 'bag':
						echo \Shopwell\Icon::get_svg( 'cart-' . $cart_icon );
						break;
					default:
						echo '<span class="shopwell-svg-icon">' . \Shopwell\Icon::sanitize_svg( $cart_icon ) . '</span>';
						break;
				}
				?>
			</span>
		<?php endif; ?>
		<span class="shopwell-button__text <?php echo esc_attr( $cart_text_class ); ?>"><?php echo esc_html( $cart_text ); ?></span>
		<?php if ( $cart_display == 'icon' || $cart_display == 'icon-text' ) : ?>
			<span class="header-counter header-cart__counter <?php echo esc_attr( $hidden ); ?>"><?php echo intval( $counter ); ?></span>
		<?php endif; ?>
	</a>
	<?php if ( \Shopwell\Helper::get_option( 'header_cart_icon_behaviour' ) == 'dropdown' ) : ?>
		<div class="cart-dropdown dropdown-content">
			<?php if ( ! WC()->cart->is_empty() ) : ?>
				<div class="cart-dropdown__header">
					<?php echo esc_html__( 'Shopping Cart ', 'shopwell' ); ?><span class="cart-dropdown__counter"><?php echo '(' . intval( $counter ) . ')'; ?></span>
				</div>
			<?php endif; ?>

			<div class="cart-dropdown__content">
				<div class="widget_shopping_cart_content">
					<?php
						woocommerce_mini_cart();
					?>
				</div>
			</div>
		</div>
	<?php endif; ?>
</div>
