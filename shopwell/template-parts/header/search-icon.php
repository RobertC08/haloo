<?php
/**
 * Template part for displaying the search icon.
 *
 * @package Shopwell
 */

$placeholder = esc_attr__( 'Search for anything', 'shopwell' );
if ( \Shopwell\Helper::get_option( 'header_search_type' ) == 'adaptive' ) {
	if ( \Shopwell\Header\Search::type() == 'post' ) {
		$placeholder = esc_attr__( 'Search the blog...', 'shopwell' );
	} else {
		$placeholder = esc_attr__( 'Search products...', 'shopwell' );
	}
}

$search_type = \Shopwell\Helper::is_blog() || is_singular( 'post' ) ? 'post' : \Shopwell\Helper::get_option( 'header_search_type' );

?>

<div class="header-search header-search--icon">
	<form class="header-search__form header-search__form--medium" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
		<div class="header-search__icon" role="button" tabindex="0" aria-label="<?php esc_attr_e( 'Search', 'shopwell' ); ?>" aria-expanded="false">
			<?php echo \Shopwell\Icon::get_svg( 'search' ); ?>
		</div>

		<div class="header-search__container">
			<input type="text" name="s" class="header-search__field shopwell-input--medium" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php echo esc_attr( $placeholder ); ?>" autocomplete="off">

			<button class="header-search__button shopwell-button shopwell-button--icon shopwell-button--medium shopwell-button--subtle shopwell-button--no-spacing shopwell-button--color-black" type="submit">
				<?php echo '<span class="shopwell-button__icon">' . \Shopwell\Icon::get_svg( 'search' ) . '</span>'; ?>
			</button>

			<div class="header-search__close shopwell-button--icon shopwell-button--medium" role="button" tabindex="0" aria-label="<?php esc_attr_e( 'Search Close', 'shopwell' ); ?>">
				<?php echo \Shopwell\Icon::get_svg( 'close' ); ?>
			</div>

			<?php echo \Shopwell\Header\Search::trendings( $args ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>

		<?php if ( $search_type ) : ?>
			<input type="hidden" name="post_type" value="<?php echo isset( $_GET['post_type'] ) && ! empty( $_GET['post_type'] ) ? esc_attr( $_GET['post_type'] ) : esc_attr( \Shopwell\Header\Search::type() ); ?>">
		<?php endif; ?>
	</form>
</div>
