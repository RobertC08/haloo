<?php

/**
 * Template part for displaying the sign-in
 *
 * @package Shopwell
 */

if ( ! function_exists( 'WC' ) ) {
	return;
}

if ( ! function_exists( 'wc_get_account_endpoint_url' ) ) {
	return;
}


$account_display     = isset( $args['account_display'] ) && ! empty( $args['account_display'] ) ? $args['account_display'] : 'icon';
$account_text        = isset( $args['account_text'] ) && ! empty( $args['account_text'] ) ? $args['account_text'] : '';
$account_subtext     = isset( $args['account_subtext'] ) && ! empty( $args['account_subtext'] ) ? $args['account_subtext'] : '';
$account_classes     = isset( $args['account_classes'] ) ? $args['account_classes'] : '';
$account_text_class  = isset( $args['account_text_class'] ) ? $args['account_text_class'] : '';
$account_data_toggle = isset( $args['account_data_toggle'] ) ? $args['account_data_toggle'] : 'off-canvas';
$account_icon        = \Shopwell\Icon::get_svg( 'account' );
if ( is_user_logged_in() && \Shopwell\Helper::get_option( 'header_account_avatar_icon' ) ) {
	$account_icon = get_avatar( get_current_user_id(), 20 );

	if ( $account_display == 'text' ) {
		$account_text = $account_icon;
	}
}

$attributes           = 'data-toggle=' . esc_attr( $account_data_toggle ) . ' data-target=account-panel';
$account_toggle_class = $account_data_toggle == 'dropdown' ? ' header-button-dropdown' : '';
if ( ! is_user_logged_in() && \Shopwell\Helper::get_option( 'header_signin_icon_behaviour' ) == 'page' ) {
	$attributes           = '';
	$account_toggle_class = '';
}
$account_classes .= $account_toggle_class;
?>
<div class="header-account">
	<a href="<?php echo esc_url( wc_get_account_endpoint_url( 'dashboard' ) ); ?>" <?php echo esc_attr( $attributes ); ?> class="shopwell-button <?php echo esc_attr( $account_classes ); ?>">
		<?php if ( $account_display != 'text' ) : ?>
			<span class="shopwell-button__icon"><?php echo $account_icon; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
		<?php endif; ?>
		<span class="shopwell-button__text <?php echo esc_attr( $account_text_class ); ?>">
			<?php if ( ! empty( $account_subtext ) ) : ?>
				<span class="shopwell-button__subtext"><?php echo esc_html( $account_subtext ); ?></span>
			<?php endif; ?>
			<span class="shopwell-button__text--account"><?php echo $account_text; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
		</span>
	</a>
	<?php if ( $account_data_toggle == 'dropdown' ) : ?>
		<div class="account-dropdown dropdown-content">
			<div class="account-dropdown__header">
				<?php
				if ( is_user_logged_in() ) :
					$current_user = wp_get_current_user();
					?>
					<div class="account-dropdown__avatar "><?php echo get_avatar( get_current_user_id(), 44 ); ?></div>
					<div class="account-dropdown__name "><?php echo esc_html( $current_user->display_name ); ?></div>
				<?php else : ?>
					<div class="account-dropdown__avatar "><?php echo \Shopwell\Icon::get_svg( 'account' ); ?></div>
					<div class="account-dropdown__name "><?php echo esc_html__( 'Account', 'shopwell' ); ?></div>
				<?php endif; ?>
			</div>
			<div class="account-dropdown__content">
				<?php \Shopwell\Helper::account_links(); ?>
			</div>
		</div>
	<?php endif; ?>
</div>
