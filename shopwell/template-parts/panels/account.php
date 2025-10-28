<?php
/**
 * Template part for displaying the account panel
 *
 * @package Shopwell
 */

if ( ! function_exists( 'WC' ) ) {
	return;
}
$current_user = wp_get_current_user();

if ( empty( $current_user ) ) {
	return;
}
?>

<div id="account-panel" class="offscreen-panel account-panel">
	<div class="panel__backdrop"></div>
	<div class="panel__container">
		<?php echo \Shopwell\Icon::get_svg( 'close', 'ui', 'class=panel__button-close' ); ?>

		<div class="panel__header">
			<?php if ( is_user_logged_in() ) : ?>
				<div class="account-panel__avatar logged"><?php echo get_avatar( $current_user->ID, 44 ); ?></div>
				<div class="account-panel__name"><?php echo esc_html( $current_user->display_name ); ?></div>
			<?php else : ?>
				<div class="account-panel__avatar"><?php echo \Shopwell\Icon::get_svg( 'account' ); ?></div>
				<div class="account-panel__name"><?php echo esc_html__( 'Account', 'shopwell' ); ?></div>
			<?php endif; ?>
		</div>

		<div class="panel__content">
			<?php \Shopwell\Helper::account_links(); ?>
		</div>
	</div>
</div>
