<?php
/**
 * Template file for displaying cart mobile
 *
 * @package Shopwell
 */

$cart_icon = \Shopwell\Helper::get_option( 'header_cart_icon' );

if ( $cart_icon == 'custom' ) {
	$cart_icon = \Shopwell\Helper::get_option( 'header_cart_icon_custom' );
}

$counter = ! empty( WC()->cart ) ? WC()->cart->get_cart_contents_count() : 0;
$hidden  = ! $counter ? 'hidden' : '';
?>

<a href="<?php echo esc_url( wc_get_cart_url() ); ?>" class="shopwell-mobile-navigation-bar__icon cart-icon" data-toggle="off-canvas" data-target="cart-panel">
	<span>
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
		<span class="counter cart-counter <?php echo esc_attr( $hidden ); ?>"><?php echo intval( $counter ); ?></span>
	</span>
	<em><?php echo esc_html__( 'Cart', 'shopwell' ); ?></em>
</a>
