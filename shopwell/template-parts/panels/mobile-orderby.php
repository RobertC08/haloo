<?php
/**
 * Template part for displaying the filter sidebar panel
 *
 * @package Shopwell
 */

if ( ! function_exists( 'WC' ) ) {
	return;
}

?>

<div id="mobile-orderby-modal" class="modal mobile-orderby-modal">
	<div class="modal__backdrop"></div>
	<div class="modal__container">
		<?php echo \Shopwell\Icon::get_svg( 'close', 'ui', 'class=modal__button-close' ); ?>
		<div class="modal__header">
			<?php echo esc_html__( 'Sort by', 'shopwell' ); ?>
		</div>
		<div class="modal__content">
			<ul class="mobile-orderby-list">
				<?php foreach ( $args as $id => $name ) : ?>
					<li><a href="#" data-id="<?php echo esc_attr( $id ); ?>" data-title="<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $name ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>
</div>
