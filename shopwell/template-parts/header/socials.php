<?php
/**
 * Template part for displaying the socials
 *
 * @package Shopwell
 */

?>
<?php
if ( ! has_nav_menu( 'socials' ) ) {
	return;
}

	wp_nav_menu(
		apply_filters(
			'shopwell_navigation_socials_menu_content',
			array(
				'theme_location'  => 'socials',
				'container'       => 'nav',
				'container_id'    => 'socials-navigation',
				'container_class' => 'socials-navigation',
				'menu_class'      => 'nav-menu menu',
				'depth'           => 1,
			)
		)
	);
