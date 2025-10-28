<?php
/**
 * Template part for displaying the logo
 *
 * @package Shopwell
 */

$display    = ( isset( $args['display'] ) && ! empty( $args['display'] ) ) ? $args['display'] : 'dark';
$show_title = isset( $args['title'] ) ? $args['title'] : true;
$logo_light = '';

if ( 'text' == $args['type'] ) {
	$logo = ! empty( $args['logo'] ) ? $args['logo'] : \Shopwell\Helper::get_option( 'logo_text' );
} elseif ( 'svg' == $args['type'] ) {
	$logo       = ! empty( $args['logo'] ) ? $args['logo'] : \Shopwell\Helper::get_option( 'logo_svg' );
	$logo_light = ! empty( $args['logo_light'] ) ? $args['logo_light'] : '';

} else {

	$logo       = ! empty( $args['logo'] ) ? $args['logo'] : \Shopwell\Helper::get_option( 'logo' );
	$logo_light = ! empty( $args['logo_light'] ) ? $args['logo_light'] : '';
}
$logo       = apply_filters( 'shopwell_header_logo', $logo, $args['type'] );
$logo_light = apply_filters( 'shopwell_header_logo_light', $logo_light, $args['type'] );
?>
<div class="header-logo <?php echo empty( $logo ) && ! has_custom_logo() ? 'no-logo' : ''; ?> ">
	<?php if ( ! has_custom_logo() || defined( 'SHOPWELL_ADDONS_VER' ) ) : ?>
		<?php if ( $logo ) : ?>
		<a href="<?php echo esc_url( home_url() ); ?>">
			<?php if ( 'text' == $args['type'] ) : ?>
				<span class="header-logo__text"><?php echo esc_html( $logo ); ?></span>
			<?php elseif ( 'svg' == $args['type'] ) : ?>
				<?php if ( ! empty( $logo_light ) ) : ?>
					<span class="header-logo__svg logo-light"><?php echo \Shopwell\Icon::sanitize_svg( $logo_light ); ?></span>
				<?php endif; ?>
				<span class="header-logo__svg logo-dark"><?php echo \Shopwell\Icon::sanitize_svg( $logo ); ?></span>
			<?php else : ?>
				<?php if ( ! empty( $logo_light ) ) : ?>
					<img src="<?php echo esc_url( $logo_light ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" class="logo-light">
				<?php endif; ?>
				<img src="<?php echo esc_url( $logo ); ?>" class="logo-dark" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
			<?php endif; ?>
		</a>
		<?php endif; ?>
		<?php if ( $show_title ) : ?>
			<?php \Shopwell\Header\Main::site_branding_title(); ?>
			<?php \Shopwell\Header\Main::site_branding_description(); ?>
		<?php endif; ?>

	<?php else : ?>
		<?php the_custom_logo(); ?>
		<?php \Shopwell\Header\Main::site_branding_title(); ?>
		<?php \Shopwell\Header\Main::site_branding_description(); ?>

	<?php endif; ?>
</div>
