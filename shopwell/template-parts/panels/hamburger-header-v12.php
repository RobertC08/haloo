<?php
/**
 * Template part for displaying the hamburger panel
 *
 * @package Shopwell
 */

$data_target     = ( isset( $args['data_target'] ) && ! empty( $args['data_target'] ) ) ? $args['data_target'] : '';
$args['type']    = ! empty( $options ) && isset( $options['type'] ) ? $options['type'] : \Shopwell\Helper::get_option( 'logo_type' );
$args['type']    = apply_filters( 'shopwell_header_logo_type', $args['type'] );
$args['display'] = ! empty( $options ) && isset( $options['display'] ) ? $options['display'] : 'dark';

?>

<div id="<?php echo esc_attr( $data_target ); ?>" class="offscreen-panel offscreen-panel--side-left hamburger-panel hamburger-header-v12-panel">
	<div class="panel__backdrop"></div>
	<div class="panel__container">
		<div class="panel__header">
			<div class="hamburger-header">
				<?php get_template_part( 'template-parts/header/logo', '', $args ); ?>
				<?php echo \Shopwell\Icon::get_svg( 'close', 'ui', 'class=panel__button-close' ); ?>
			</div>
			<div class="header-category__box">
				<div class="header-category__box-image"></div>
				<div class="header-category__top">
					<span class="header-category__back">
						<?php echo \Shopwell\Icon::get_svg( 'left' ) . esc_html__( 'Main Menu', 'shopwell' ); ?>
					</span>
					<?php echo \Shopwell\Icon::get_svg( 'close', 'ui', 'class=panel__button-close' ); ?>
				</div>
				<div class="header-category__sub-title"></div>
			</div>
		</div>

		<div class="panel__content">
			<?php \Shopwell\Header\Hamburger::items( $args ); ?>
		</div>
	</div>
</div>
