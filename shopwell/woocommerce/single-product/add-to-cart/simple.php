<?php
/**
 * Simple product add to cart
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/add-to-cart/simple.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 7.0.1
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! $product->is_purchasable() ) {
	return;
}

if ( $product->is_in_stock() ) : 
	// Verificăm stocul disponibil vs. cantitatea din coș
	$stock_quantity = $product->get_stock_quantity();
	$cart_quantity = 0;
	
	if ( function_exists( 'WC' ) && WC()->cart ) {
		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			if ( $cart_item['product_id'] == $product->get_id() && ( ! isset( $cart_item['variation_id'] ) || $cart_item['variation_id'] == 0 ) ) {
				$cart_quantity += $cart_item['quantity'];
			}
		}
	}
	
	$available_stock = null;
	if ( $stock_quantity !== null ) {
		$available_stock = max( 0, $stock_quantity - $cart_quantity );
	}
	
	$can_add_to_cart = true;
	$stock_message = '';
	
	if ( $available_stock !== null && $available_stock <= 0 ) {
		$can_add_to_cart = false;
		$stock_message = esc_html__( 'Nu mai poți adăuga produse în coș. Ai atins limita disponibilă în stoc.', 'shopwell' );
	}
	?>

	<?php do_action( 'woocommerce_before_add_to_cart_form' ); ?>

	<form class="cart" action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ); ?>" method="post" enctype='multipart/form-data'>
		<?php do_action( 'woocommerce_before_add_to_cart_button' ); ?>

		<?php if ( ! empty( $stock_message ) ) : ?>
			<div class="shopwell-stock-limit-message" style="margin-bottom: 15px; padding: 12px; background-color: #fff3cd; border-left: 4px solid #ffc107; color: #856404; border-radius: 4px;">
				<?php echo esc_html( $stock_message ); ?>
			</div>
		<?php endif; ?>

		<?php
		do_action( 'woocommerce_before_add_to_cart_quantity' );

		$max_value = apply_filters( 'woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product );
		if ( $available_stock !== null && $max_value !== '' ) {
			$max_value = min( $max_value, $available_stock );
		} elseif ( $available_stock !== null ) {
			$max_value = $available_stock;
		}

		woocommerce_quantity_input(
			array(
				'min_value'   => apply_filters( 'woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product ),
				'max_value'   => $max_value,
				'input_value' => isset( $_POST['quantity'] ) ? wc_stock_amount( wp_unslash( $_POST['quantity'] ) ) : $product->get_min_purchase_quantity(), // WPCS: CSRF ok, input var ok.
			)
		);

		do_action( 'woocommerce_after_add_to_cart_quantity' );
		?>

		<button type="submit" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>" class="single_add_to_cart_button button alt<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?><?php echo ! $can_add_to_cart ? ' disabled' : ''; ?>" <?php echo ! $can_add_to_cart ? 'disabled="disabled"' : ''; ?> data-product-id="<?php echo esc_attr( $product->get_id() ); ?>" data-available-stock="<?php echo esc_attr( $available_stock !== null ? $available_stock : '' ); ?>">
			<?php echo \Shopwell\Icon::get_svg( 'cart-trolley', '', array( 'class' => 'single_add_to_cart_button--icon' ) ); ?>
			<?php echo '<span class="single_add_to_cart_button--text">' . esc_html( $product->single_add_to_cart_text() ) . '</span>'; ?>
		</button>

		<?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>
	</form>

	<?php do_action( 'woocommerce_after_add_to_cart_form' ); ?>

<?php endif; ?>
