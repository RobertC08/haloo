<?php
/**
 * Single Product tabs
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/tabs/tabs.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.8.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Filter tabs and allow third parties to add their own.
 *
 * Each tab is an array containing title, callback and priority.
 *
 * @see woocommerce_default_product_tabs()
 */
$product_tabs = apply_filters( 'woocommerce_product_tabs', array() );
$count        = 0;

$tab = '';

if ( in_array( \Shopwell\Helper::get_option( 'product_layout' ), array( '3', '5', '6' ) ) ) {
	$icon = \Shopwell\Icon::get_svg( 'select-arrow', '', array( 'class' => 'icon-arrow' ) );
} else {
	$icon = \Shopwell\Icon::get_svg( 'plus', '', array( 'class' => 'icon-plus icon-noactive' ) ) . \Shopwell\Icon::get_svg( 'minus', '', array( 'class' => 'icon-minus icon-active' ) );
	$tab  = 'tabs wc-tabs';
}
$icon      = apply_filters( 'shopwell_product_tabs_icon', $icon );
$tab       = apply_filters( 'shopwell_product_tabs_heading_classes', $tab );
$index     = 0;
$dropdown  = in_array( \Shopwell\Helper::get_option( 'product_layout' ), array( '3', '5' ) );
$dropdown  = apply_filters( 'shopwell_product_tabs_dropdown', $dropdown );
$tab_first = false;

if ( \Shopwell\Helper::get_option( 'product_tabs_status' ) == 'first' ) {
	$tab_first = true;
	if ( $dropdown ) {
		$tabs_class = 'wc-tabs-first--opened';
	} else {
		$tabs_class = 'wc-tabs-first-mobile--opened';
	}
} else {
	$tabs_class = '';
}

if ( ! empty( $product_tabs ) ) : ?>

	<div class="woocommerce-tabs wc-tabs-wrapper <?php echo esc_attr( $tabs_class ); ?>">
		<ul class="shopwell-tabs-heading <?php echo esc_attr( $tab ); ?>" role="tablist">
			<?php foreach ( $product_tabs as $key => $product_tab ) : ?>
				<li role="presentation" class="<?php echo esc_attr( $key ); ?>_tab" id="tab-title-<?php echo esc_attr( $key ); ?>">
					<a href="#tab-<?php echo esc_attr( $key ); ?>" role="tab" aria-controls="tab-<?php echo esc_attr( $key ); ?>">
						<?php echo wp_kses_post( apply_filters( 'woocommerce_product_' . $key . '_tab_title', $product_tab['title'], $key ) ); ?>
					</a>
				</li>
				<?php
				++$count;
endforeach;
			?>
		</ul>
		<?php
		foreach ( $product_tabs as $key => $product_tab ) :
			$tab_class = $index == 0 && $tab_first ? 'active' : '';
			?>
			<div class="woocommerce-Tabs-panel woocommerce-Tabs-panel--<?php echo esc_attr( $key ); ?> panel entry-content wc-tab" id="tab-<?php echo esc_attr( $key ); ?>" role="tabpanel" aria-labelledby="tab-title-<?php echo esc_attr( $key ); ?>">
				<a id="tab-panel-title-<?php echo esc_attr( $key ); ?>" data-tab="tab-title-<?php echo esc_attr( $key ); ?>" href="#tab-<?php echo esc_attr( $key ); ?>"
						class="shopwell-dropdown__title tab-title-<?php echo esc_attr( $key ); ?> <?php echo esc_attr( $tab_class ); ?>">
						<?php echo apply_filters( 'woocommerce_product_' . $key . '_tab_title', esc_html( $product_tab['title'] ), $key ); ?>
						<?php echo ! empty( $icon ) ? $icon : ''; ?>
				</a>
				<div class="shopwell-dropdown__content">
					<?php
					if ( isset( $product_tab['callback'] ) ) {
						call_user_func( $product_tab['callback'], $key, $product_tab );
					}
					++$index;
					?>
				</div>
			</div>
		<?php endforeach; ?>

		<?php do_action( 'woocommerce_product_after_tabs' ); ?>
	</div>

<?php endif; ?>
