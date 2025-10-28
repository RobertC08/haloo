<?php
/**
 * Template part for displaying the primary menu
 *
 * @package Shopwell
 */
?>

<?php

	$container_class  = 'main-navigation primary-navigation';
	$container_class .= isset( $args['container_class'] ) && $args['container_class'] ? $args['container_class'] : '';

	$menu_class = isset( $args['menu_class'] ) && $args['menu_class'] ? 'nav-menu menu' : 'menu';
if ( has_nav_menu( 'primary-menu' ) ) {
	if ( isset( $args['mega_menu'] ) && $args['mega_menu'] && class_exists( '\Shopwell\Addons\Modules\Mega_Menu\Walker' ) ) {
		wp_nav_menu(
			apply_filters(
				'shopwell_navigation_primary_menu_content',
				array(
					'theme_location'  => 'primary-menu',
					'container'       => 'nav',
					'container_class' => $container_class,
					'menu_class'      => $menu_class,
					'walker'          => new \Shopwell\Addons\Modules\Mega_Menu\Walker(),
				)
			)
		);
	} else {
		wp_nav_menu(
			apply_filters(
				'shopwell_navigation_primary_menu_content',
				array(
					'theme_location'  => 'primary-menu',
					'container'       => 'nav',
					'container_class' => $container_class,
					'menu_class'      => $menu_class,
				)
			)
		);
	}
} else {
	wp_page_menu(
		array(
			'menu_class' => $container_class,
			'show_home'  => true,
			'container'  => 'nav',
			'before'     => '<ul class="' . $menu_class . '">',
			'after'      => '</ul>',
		)
	);
}
