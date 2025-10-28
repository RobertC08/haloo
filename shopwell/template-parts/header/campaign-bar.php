<?php
/**
 * Template part for displaying the campaign bar
 *
 * @package Shopwell
 */

?>
<div id="campaign-bar" class="campaign-bar campaign-bar--<?php echo \Shopwell\Helper::get_option( 'campaign_bar_position' ); ?>">
	<div class="campaign-bar__container container">
		<?php \Shopwell\Header\Campaign_Bar::campaign_items( $args ); ?>
	</div>
</div>
