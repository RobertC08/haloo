<?php
/**
 * Template part for displaying the hamburger panel
 *
 * @package Shopwell
 */

$data_target = ( isset( $args['data_target'] ) && ! empty( $args['data_target'] ) ) ? $args['data_target'] : '';

?>

<div id="<?php echo esc_attr( $data_target ); ?>" class="offscreen-panel offscreen-panel--side-left hamburger-panel hamburger-header-v11-panel">
	<div class="panel__backdrop"></div>
	<div class="panel__container">
		<div class="panel__header">
			<div class="hamburger-header">
				<a class="hamburger-header__back" href="<?php echo esc_url( get_home_url() ); ?>">
					<?php echo \Shopwell\Icon::get_svg( 'left' ) . esc_html__( 'Return to Shop', 'shopwell' ); ?>
				</a>
				<?php echo \Shopwell\Icon::get_svg( 'close', 'ui', array( 'class' => 'panel__button-close', 'tabindex' => '0') ); ?>
			</div>
			<div class="header-category__box">
				<div class="header-category__box-image"></div>
				<div class="header-category__top">
					<span class="header-category__back" tabindex="0" role="button">
						<?php echo \Shopwell\Icon::get_svg( 'left' ) . esc_html__( 'Main Menu', 'shopwell' ); ?>
					</span>
					<?php echo \Shopwell\Icon::get_svg( 'close', 'ui', array( 'class' => 'panel__button-close', 'tabindex' => '0') ); ?>
				</div>
				<div class="header-category__sub-title"></div>
			</div>
		</div>

		<div class="panel__content">
			<?php \Shopwell\Header\Hamburger::items( $args ); ?>
		</div>
	</div>
</div>
