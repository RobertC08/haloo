<?php
/**
 * Template part for displaying the search bar help center
 *
 * @package Shopwell
 */

?>
<div id="search-bar-hc" class="search-bar-hc">
	<div class="container">
		<div class="search-bar-hc__title"><?php esc_html_e( 'How can we help you?', 'shopwell' ); ?></div>
		<form class="search-bar-hc__form" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
			<input type="text" name="s" class="search-bar-hc__field shopwell-input--raised" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'Search help articles', 'shopwell' ); ?>" autocomplete="off">
			<input type="hidden" name="post_type" class="search-bar-hc__post-type" value="sw_help_article">
			<button type="submit" class="shopwell-svg-icon search-bar-hc__submit shopwell-button--subtle">
				<svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32">
					<path d="M28.8 26.544l-5.44-5.44c1.392-1.872 2.24-4.192 2.24-6.704 0-6.176-5.024-11.2-11.2-11.2s-11.2 5.024-11.2 11.2 5.024 11.2 11.2 11.2c2.512 0 4.832-0.848 6.688-2.24l5.44 5.44 2.272-2.256zM6.4 14.4c0-4.416 3.584-8 8-8s8 3.584 8 8-3.584 8-8 8-8-3.584-8-8z"></path>
				</svg>
			</button>
		</form>
	</div>
</div>
