<?php
/**
 * Template part for displaying the page header
 *
 * @package Shopwell
 */

?>

<div id="page-header" class="<?php \Shopwell\Page_Header::classes( 'page-header' ); ?>">
	<div class="container clearfix">
		<?php do_action( 'shopwell_before_page_header_content' ); ?>
		<div class="page-header__content">
			<?php do_action( 'shopwell_page_header_content' ); ?>
		</div>
		<?php do_action( 'shopwell_after_page_header_content' ); ?>
	</div>
</div>
