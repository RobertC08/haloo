<?php
/**
 * Template part for displaying the hamburger panel
 *
 * @package Shopwell
 */

$data_target  = ( isset( $args['data_target'] ) && ! empty( $args['data_target'] ) ) ? $args['data_target'] : '';
$account_info = isset( $args['account_info'] ) ? $args['account_info'] : true;
$account_icon = '';
$account_name = esc_html__( 'Hello', 'shopwell' );
if ( $account_info ) {
	if ( is_user_logged_in() ) {
		$account_icon  = get_avatar( get_current_user_id(), 44 );
		$current_user  = wp_get_current_user();
		$account_name .= ', ' . $current_user->display_name;
	} else {
		$account_icon  = \Shopwell\Icon::get_svg( 'account' );
		$account_name .= ', ' . esc_html__( 'Sign in', 'shopwell' );
	}
}
$submenus_classes = isset( $args['open_submenus'] ) ? 'open-submenus-' . $args['open_submenus'] : '';
?>

<div id="<?php echo esc_attr( $data_target ); ?>" class="offscreen-panel offscreen-panel--side-left hamburger-panel">
	<div class="panel__backdrop"></div>
	<div class="panel__container">
		<?php
		echo \Shopwell\Icon::get_svg(
			'close',
			'ui',
			array(
				'class'    => 'panel__button-close',
				'tabindex' => '0',
			)
		);
		?>

		<div class="panel__header">
			<div class="header-category__box">
				<div class="header-category__box-image"></div>
				<div class="header-category__top">
					<span class="header-category__back" tabindex="0" role="button">
						<?php echo \Shopwell\Icon::get_svg( 'left' ) . esc_html__( 'Main Menu', 'shopwell' ); ?>
					</span>
					<?php
					echo \Shopwell\Icon::get_svg(
						'close',
						'ui',
						array(
							'class'    => 'panel__button-close',
							'tabindex' => '0',
						)
					);
					?>
				</div>
				<div class="header-category__sub-title"></div>
			</div>
			<?php if ( function_exists( 'wc_get_account_endpoint_url' ) && $account_info ) { ?>
				<a href="<?php echo esc_url( wc_get_account_endpoint_url( 'dashboard' ) ); ?>" class="shopwell-button  shopwell-button-text hamburger-panel__name">
					<span class="shopwell-button__icon"><?php echo $account_icon; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<span class="shopwell-button__text"><?php echo esc_html( $account_name ); ?></span>
				</a>
			<?php } ?>
		</div>

		<div class="panel__content <?php echo esc_attr( $submenus_classes ); ?>">
			<?php \Shopwell\Header\Hamburger::items( $args ); ?>
		</div>
	</div>
</div>
