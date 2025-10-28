<?php
/**
 * Template part for displaying the quickview modal
 *
 * @package Shopwell
 */

if ( ! function_exists( 'WC' ) ) {
	return;
}
if ( \Shopwell\WooCommerce\Helper::is_cartflows_template() ) {
	return;
}
?>

<div id="quick-view-modal" class="quick-view-modal modal single-product">
	<div class="modal__backdrop"></div>
	<div class="modal__quickview">
		<?php echo \Shopwell\Icon::get_svg( 'close', 'ui', 'class=modal__button-close' ); ?>
		<div class="modal__content woocommerce">
			<div class="modal__product product-quickview"></div>
		</div>
	</div>
	<span class="modal__loader"><span class="shopwellSpinner"></span></span>
</div>
