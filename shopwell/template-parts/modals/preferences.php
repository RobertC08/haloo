<?php
/**
 * Template part for displaying Preferences
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Shopwell
 */
?>
<?php $items = (array) \Shopwell\Theme::get_prop( 'modals' ); ?>
<?php if ( in_array( 'preferences', $items ) ) : ?>
<div id="preferences-modal" class="preferences-modal modal">
	<div class="modal__backdrop"></div>
	<div class="modal__preferences">
		<div class="modal__header">
			<div class="modal__heading"><?php echo esc_html__( 'Preferences', 'shopwell' ); ?></div>
			<div class="modal__button-close"><?php echo \Shopwell\Icon::get_svg( 'close' ); ?></div>
		</div>

		<div class="modal__content">
			<form class="shopwell-preferences" method="get">
				<p class="form-row-preferences language_field" id="language_field">
					<?php \Shopwell\Helper::language_switcher( 'select' ); ?>
				</p>

				<p class="form-row-preferences currency_field" id="currency_field">
					<?php \Shopwell\WooCommerce\Currency::currency_switcher( 'select' ); ?>
				</p>
				<button type="submit" class="button update-preferences"><?php esc_html_e( 'Update', 'shopwell' ); ?></button>
			</form>
		</div>
	</div>
</div>
<?php endif; ?>
