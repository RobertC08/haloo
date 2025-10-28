<?php
/**
 * Template part for displaying the hamburger modal
 *
 * @package Shopwell
 */
?>

<div id="search-modal" class="modal search-modal">
	<div class="modal__backdrop"></div>
	<div class="modal__container">
		<div class="modal__header">
			<form class="search-modal__form" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
				<input type="text" name="s" class="search-modal__field shopwell-input--medium  shopwell-input--raised" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php echo esc_attr__( 'Search for anything', 'shopwell' ); ?>" autocomplete="off">
				<button class="search-modal__submit mt-button__icon--subtle shopwell-button--text" type="submit"><?php echo \Shopwell\Icon::get_svg( 'search' ); ?></button>
				<?php
				if ( boolval( \Shopwell\Helper::get_option( 'header_search_ajax' ) ) ) {
					echo \Shopwell\Icon::get_svg( 'close', 'ui', 'class=close-search-results' );
					echo '<div class="header-search__results search-results woocommerce"></div>';
				}
				?>
				<input type="hidden" name="post_type" class="search-modal__post-type" value="<?php echo isset( $_GET['post_type'] ) && ! empty( $_GET['post_type'] ) ? esc_attr( $_GET['post_type'] ) : esc_attr( \Shopwell\Header\Search::type() ); ?>">
			</form>
		</div>

		<div class="modal__content">
			<?php
			if ( \Shopwell\Helper::get_option( 'header_mobile_search_trending_searches' ) ) {
				$args['trending_searches'] = true;
				\Shopwell\Header\Search::trendings( $args );
			}
			?>
		</div>
		<button class="modal__button-close shopwell-button--text" type="submit"><?php echo \Shopwell\Icon::get_svg( 'close' ); ?> <?php esc_html_e( 'Close', 'shopwell' ); ?></button>
	</div>
</div>
