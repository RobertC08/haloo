<?php
/**
 * Template part for displaying the secondary menu
 *
 * @package Shopwell
 */
?>

<?php
if ( ! has_nav_menu( 'secondary-menu' ) ) {
	return;
}

if ( class_exists( '\Shopwell\Addons\Modules\Mega_Menu\Walker' ) ) {
	wp_nav_menu(
		apply_filters(
			'shopwell_navigation_secondary_menu_content',
			array(
				'theme_location'  => 'secondary-menu',
				'container'       => 'nav',
				'container_id'    => 'secondary-navigation',
				'container_class' => 'main-navigation secondary-navigation',
				'menu_class'      => 'nav-menu menu',
				'walker'          => new \Shopwell\Addons\Modules\Mega_Menu\Walker(),
			)
		)
	);
} else {
	wp_nav_menu(
		apply_filters(
			'shopwell_navigation_secondary_menu_content',
			array(
				'theme_location'  => 'secondary-menu',
				'container'       => 'nav',
				'container_id'    => 'secondary-navigation',
				'container_class' => 'main-navigation secondary-navigation',
				'menu_class'      => 'nav-menu menu',
			)
		)
	);
}
