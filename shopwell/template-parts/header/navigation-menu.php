<?php
/**
 * Template part for displaying the primary menu
 *
 * @package Shopwell
 */
?>

<?php
	$menu_id = isset( $args['menu_id'] ) && $args['menu_id'] ? $args['menu_id'] : '';
if ( empty( $menu_id ) ) {
	return;
}
	$container_class  = 'main-navigation primary-navigation';
	$container_class .= isset( $args['container_class'] ) && $args['container_class'] ? $args['container_class'] : '';

	$menu_class = isset( $args['menu_class'] ) && $args['menu_class'] ? 'nav-menu menu' : 'menu';

if ( isset( $args['mega_menu'] ) && $args['mega_menu'] && class_exists( '\Shopwell\Addons\Modules\Mega_Menu\Walker' ) ) {
	wp_nav_menu(
		apply_filters(
			'shopwell_navigation_menu_by_id_content',
			array(
				'theme_location'  => '__no_such_location',
				'menu'            => $menu_id,
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
			'shopwell_navigation_header_category_content',
			array(
				'theme_location'  => '__no_such_location',
				'menu'            => $menu_id,
				'container'       => 'nav',
				'container_class' => $container_class,
				'menu_class'      => $menu_class,
			)
		)
	);
}
