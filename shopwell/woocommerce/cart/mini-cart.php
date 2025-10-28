<?php
/**
 * Mini-cart
 *
 * Contains the markup for the mini-cart, used by the cart widget.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/mini-cart.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 10.0.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_mini_cart' );

if ( class_exists( 'WeDevs_Dokan' ) || class_exists( 'WCFMmp' ) ) {
	$args['list_class'] = 'show-vendor';
}

?>

<?php if ( WC()->cart && ! WC()->cart->is_empty() ) : ?>

	<ul class="woocommerce-mini-cart cart_list product_list_widget <?php echo esc_attr( $args['list_class'] ); ?>">
		<?php
		do_action( 'woocommerce_before_mini_cart_contents' );

		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
			$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

			if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_widget_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
				/**
				 * This filter is documented in woocommerce/templates/cart/cart.php.
				 *
				 * @param string $product_name Name of the product in the cart.
				 */
				$product_name      = apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key );
				$thumbnail         = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image( 'woocommerce_gallery_thumbnail' ), $cart_item, $cart_item_key );
				$product_price     = apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key );
				$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
				?>
				<li class="woocommerce-mini-cart-item mini-cart-item-<?php echo esc_attr( $_product->get_id() ); ?> <?php echo esc_attr( apply_filters( 'woocommerce_mini_cart_item_class', 'mini_cart_item', $cart_item, $cart_item_key ) ); ?>">
					<div class="woocommerce-mini-cart-item__thumbnail">
						<?php if ( $product_permalink ) : ?>
							<a href="<?php echo esc_url( $product_permalink ); ?>">
						<?php endif; ?>
							<?php echo wp_kses_post( $thumbnail ); ?>
						<?php if ( $product_permalink ) : ?>
							</a>
						<?php endif; ?>
					</div>
					<div class="woocommerce-mini-cart-item__summary">
						<div class="woocommerce-mini-cart-item__box">
							<span class="woocommerce-mini-cart-item__data">
								<span class="woocommerce-mini-cart-item__name">
								<?php if ( $product_permalink ) : ?>
									<a href="<?php echo esc_url( $product_permalink ); ?>">
								<?php endif; ?>
										<?php echo wp_kses_post( $product_name ); ?>
									<?php if ( $product_permalink ) : ?>
										</a>
									<?php endif; ?>
								</span>
								<?php echo wc_get_formatted_cart_item_data( $cart_item ); ?>
								<span class="woocommerce-Price-amount amount">
									<bdi><?php echo ! empty( $product_price ) ? $product_price : ''; ?></bdi>
								</span>
								<span class="woocommerce-mini-cart-item__qty--text hidden">
									<?php esc_html_e( 'QTY:', 'shopwell' ); ?>
									<?php echo wp_kses_post( $cart_item['quantity'] ); ?>
								</span>
							</span>
							<span class="woocommerce-mini-cart-item__qty" data-nonce="<?php echo wp_create_nonce( 'shopwell-update-cart-qty--' . $cart_item_key ); ?>">
								<?php echo apply_filters( 'woocommerce_widget_cart_item_quantity', '<span class="quantity">' . sprintf( '%s &times;', $cart_item['quantity'] ) . '</span>', $cart_item, $cart_item_key ); ?>
							</span>
						</div>
						<div class="woocommerce-mini-cart-item__remove">
							<?php
							echo apply_filters(
								'woocommerce_cart_item_remove_link',
								sprintf(
									'<a role="button" href="%s" class="remove remove_from_cart_button" aria-label="%s" data-product_id="%s" data-cart_item_key="%s" data-product_sku="%s">%s</a>',
									esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
									esc_attr__( 'Remove this item', 'shopwell' ),
									esc_attr( $product_id ),
									esc_attr( $cart_item_key ),
									esc_attr( $_product->get_sku() ),
									\Shopwell\Icon::get_svg( 'trash' )
								),
								$cart_item_key
							);
							?>
						</div>
					</div>

				</li>
				<?php
			}
		}

		do_action( 'woocommerce_mini_cart_contents' );
		?>
	</ul>

	<div class="widget_shopping_cart_footer">

		<p class="woocommerce-mini-cart__total total">
			<?php
			/**
			 * Hook: woocommerce_widget_shopping_cart_total.
			 *
			 * @hooked woocommerce_widget_shopping_cart_subtotal - 10
			 */
			do_action( 'woocommerce_widget_shopping_cart_total' );
			?>
		</p>

		<?php do_action( 'woocommerce_widget_shopping_cart_before_buttons' ); ?>

		<p class="woocommerce-mini-cart__buttons buttons"><?php do_action( 'woocommerce_widget_shopping_cart_buttons' ); ?></p>

		<?php do_action( 'woocommerce_widget_shopping_cart_after_buttons' ); ?>
	</div>

<?php else : ?>

	<?php if ( \Shopwell\Helper::get_option( 'header_cart_icon_behaviour' ) == 'dropdown' ) : ?>
		<div class="woocommerce-mini-cart__empty-message woocommerce-mini-cart__empty--dropdown">
			<?php echo \Shopwell\Icon::get_svg( 'cart-trolley' ); ?>
			<h4><?php echo esc_html__( 'Cart is empty', 'shopwell' ); ?></h4>
			<p><?php echo esc_html__( 'Don&#39;t miss out on amazing deals! Sign in to view products added or start shopping now.', 'shopwell' ); ?></p>
			<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="shopwell-button shopwell-button--medium shopwell-button--base shopwell-button--bg-color-black"><?php echo esc_html__( 'Shop What&#39;s New', 'shopwell' ); ?></a>
			<?php $singin_text = is_user_logged_in() ? esc_html__( 'My Account', 'shopwell' ) : esc_html__( 'Sign in', 'shopwell' ); ?>
			<a href="<?php echo esc_url( wc_get_account_endpoint_url( 'dashboard' ) ); ?>" class="shopwell-button shopwell-button--medium shopwell-button--base shopwell-button--bg-color-black"><?php echo esc_html( $singin_text ); ?></a>
		</div>
	<?php else : ?>
		<p class="woocommerce-mini-cart__empty-message woocommerce-mini-cart__empty--panel">
			<img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/shopwell-cart-empty.svg' ) ); ?>" alt="<?php esc_attr_e( 'Cart is empty', 'shopwell' ); ?>">
			<?php esc_html_e( 'No products in the cart.', 'shopwell' ); ?>
		</p>
	<?php endif; ?>

<?php endif; ?>

<?php do_action( 'woocommerce_after_mini_cart' ); ?>
