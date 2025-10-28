<?php
/**
 * Template part for displaying the language currency
 *
 * @package Shopwell
 */

$preferences_position   = isset( $args['preferences_position'] ) && ! empty( $args['preferences_position'] ) ? $args['preferences_position'] : 'default';
$preferences_display    = isset( $args['preferences_display'] ) && ! empty( $args['preferences_display'] ) ? $args['preferences_display'] : 'icon-text';
$preferences_subtext    = isset( $args['preferences_subtext'] ) && ! empty( $args['preferences_subtext'] ) ? $args['preferences_subtext'] : '';
$preferences_classes    = isset( $args['preferences_classes'] ) ? $args['preferences_classes'] : 'shopwell-button--text';
$preferences_text_class = isset( $args['preferences_text_class'] ) ? $args['preferences_text_class'] : '';

?>
<div class="header-preferences header-preferences--<?php echo esc_attr( $preferences_position ); ?>">
	<a href="#" data-toggle="modal" data-target="preferences-modal" class="shopwell-button <?php echo esc_attr( $preferences_classes ); ?>">
		<span class="shopwell-button__icon">
			<?php if ( empty( $args['flag'] ) ) : ?>
				<?php echo \Shopwell\Icon::get_svg( esc_html( $args['icon'] ) ); ?>
			<?php else : ?>
				<img src="<?php echo esc_url( $args['flag'] ); ?>" alt="<?php echo esc_attr( $args['name'] ); ?>" />
			<?php endif; ?>
		</span>
		<span class="shopwell-button__text <?php echo esc_attr( $preferences_text_class ); ?>">
			<?php if ( ! empty( $preferences_subtext ) ) : ?>
				<span class="shopwell-button__subtext"><?php echo esc_html( $preferences_subtext ); ?></span>
			<?php endif; ?>
			<span class="shopwell-button__text--preferences"><?php echo wp_kses_post( $args['name'] ); ?></span>
		</span>
	</a>
</div>
