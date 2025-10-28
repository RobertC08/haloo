<?php
/**
 * Template part for displaying the notices modal
 *
 * @package Shopwell
 */

if ( ! function_exists( 'WC' ) ) {
	return;
}
?>

<div id="shopwell-popup-add-to-cart" class="shopwell-popup-add-to-cart-modal modal woocommerce">
	<div class="modal__backdrop"></div>
	<div class="modal__notices woocommerce">
		<div class="modal__button-close"><?php echo \Shopwell\Icon::get_svg( 'close' ); ?></div>

		<div class="modal__content product-modal-content">
			<div class="shopwell-product-popup-atc__notice">
				<?php esc_html_e( 'Successfully added to your cart', 'shopwell' ); ?>
			</div>
			<div class="widget_shopping_cart_content"></div>
			<?php do_action( 'shopwell_product_popup_atc_recommendation' ); ?>
		</div>

		<span class="modal__loader"><span class="shopwellSpinner"></span></span>
	</div>
</div>
