<?php
/**
 * Template part for displaying the preferences menu
 *
 * @package Shopwell
 */

?>
<nav class="preferences-menu">
	<div class="preferences-menu__heading"><?php esc_html_e( 'Preferences', 'shopwell' ); ?></div>
	<ul>
		<?php if ( ! empty( $args['language'] ) ) : ?>
			<li class="preferences-menu__item shopwell-language">
				<a href="#" data-title="<?php esc_attr_e( 'Language', 'shopwell' ); ?>">
					<span class="shopwell-button shopwell-button--text">
						<span class="shopwell-button__icon">
							<?php echo \Shopwell\Icon::get_svg( 'language' ); ?>
						</span>
						<span class="shopwell-button__text">
							<?php echo esc_html( $args['language'] ); ?>
						</span>
					</span>
					<?php echo \Shopwell\Icon::get_svg( 'right' ); ?>
				</a>
				<?php \Shopwell\Helper::language_switcher(); ?>
			</li>
		<?php endif; ?>

		<?php if ( ! empty( $args['currency'] ) ) : ?>
			<li class="preferences-menu__item shopwell-currency">
				<a href="#" data-title="<?php esc_attr_e( 'Currency', 'shopwell' ); ?>">
					<span class="shopwell-button shopwell-button--text">
						<span class="shopwell-button__icon">
							<?php echo \Shopwell\Icon::get_svg( 'currency' ); ?>
						</span>
						<span class="shopwell-button__text">
							<?php echo esc_html( $args['currency'] ); ?>
						</span>
					</span>
					<?php echo \Shopwell\Icon::get_svg( 'right' ); ?>
				</a>
				<?php \Shopwell\WooCommerce\Currency::currency_switcher(); ?>
			</li>
		<?php endif; ?>
	</ul>
</nav>
