<?php
/**
 * Template part for displaying the quickview panel
 *
 * @package Shopwell
 */

if ( ! function_exists( 'WC' ) ) {
	return;
}
?>

<div id="quick-view-panel" class="offscreen-panel quick-view-panel single-product">
	<div class="panel__backdrop"></div>
	<div class="panel__container">
		<?php echo \Shopwell\Icon::get_svg( 'close', 'ui', 'class=panel__button-close' ); ?>
		<div class="panel__content woocommerce">
			<div class="panel__product product-quickview"></div>
			<span class="panel__loader"><span class="shopwellSpinner"></span></span>
		</div>
	</div>
</div>
