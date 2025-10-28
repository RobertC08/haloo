<?php
/**
 * Template part for displaying the language currency
 *
 * @package Shopwell
 */

if ( ! function_exists( 'WC' ) ) {
	return;
}

$button_url = \Shopwell\Helper::get_option( 'header_view_history_link' );
$button_url = ! empty( $button_url ) ? $button_url : wc_get_page_permalink( 'shop' );

?>
<div class="header-view-history">
	<div class="header-view-history__title shopwell-button--text">
		<?php
			echo \Shopwell\Icon::get_svg( 'select-arrow', 'ui', 'class=header-view-history__arrow' );
			echo '<span class="header-view-history__name">' . esc_html__( 'Browsing History', 'shopwell' ) . '</span>';
		?>
	</div>
	<div class="header-view-history__content">
		<div class="header-view-history__wrapper container">
			<div class="header-view-history__content-heading">
				<?php
					echo '<span class="header-view-history__content-title">' . esc_html__( 'Browsing History', 'shopwell' ) . '</span>';
					echo '<a href="' . esc_url( $button_url ) . '" class="header-view-history__button shopwell-button shopwell-button--subtle shopwell-button--color-black shopwell-button--medium"><span class="shopwell-button__text">' . esc_html__( 'See All History', 'shopwell' ) . '</span></a>';
				?>
			</div>
			<?php
			echo '<div class="shopwell-pagination--loading">
							<div class="shopwell-pagination--loading-dots">
								<span></span>
								<span></span>
								<span></span>
								<span></span>
							</div>
							<div class="shopwell-pagination--loading-text">' . esc_html__( 'Loading more...', 'shopwell' ) . '</div>
						</div>';
			?>
			<div class="header-view-history__content-products"></div>

		</div>
	</div>
</div>
