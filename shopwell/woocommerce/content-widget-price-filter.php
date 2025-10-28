<?php
/**
 * Content template for WooCommerce Price Filter Widget
 * 
 * This template customizes the price filter widget to apply filters automatically
 * without a submit button and only when price range is actually modified.
 *
 * @package WooCommerce\Templates
 * @version 3.7.0
 */

defined( 'ABSPATH' ) || exit;

global $wp;

$min_price = isset( $_GET['min_price'] ) ? floatval( wp_unslash( $_GET['min_price'] ) ) : 0;
$max_price = isset( $_GET['max_price'] ) ? floatval( wp_unslash( $_GET['max_price'] ) ) : 0;

// Get the price range using our custom function (replaces protected method)
$price_range = shopwell_get_price_filter_range();

if ( empty( $price_range ) || ! isset( $price_range->min_price ) || ! isset( $price_range->max_price ) ) {
    return;
}

$min_price_range = $price_range->min_price;
$max_price_range = $price_range->max_price;

if ( $min_price_range === $max_price_range ) {
    return;
}

// Get the current price range from the widget
// If no price filters are active, set to full range by default
$current_min_price = $min_price > 0 ? $min_price : $min_price_range;
$current_max_price = $max_price > 0 ? $max_price : $max_price_range;

$form_action = wc_get_page_permalink( 'shop' );
if ( is_product_category() ) {
    $form_action = get_term_link( get_queried_object() );
} elseif ( is_product_tag() ) {
    $form_action = get_term_link( get_queried_object() );
}

$form_action = add_query_arg( 'filtering', '1', $form_action );

?>
<div class="price_slider_wrapper">
    <div class="price_slider" style="display:none;"></div>
    <div class="price_slider_amount">
        <input type="text" id="min_price" name="min_price" value="<?php echo esc_attr( $current_min_price ); ?>" data-min="<?php echo esc_attr( $min_price_range ); ?>" placeholder="<?php echo esc_attr__( 'Min price', 'woocommerce' ); ?>" />
        <input type="text" id="max_price" name="max_price" value="<?php echo esc_attr( $current_max_price ); ?>" data-max="<?php echo esc_attr( $max_price_range ); ?>" placeholder="<?php echo esc_attr__( 'Max price', 'woocommerce' ); ?>" />
        <div class="price_label" style="display:none;">
            <?php echo esc_html__( 'Price:', 'woocommerce' ); ?> <span class="from"></span> &mdash; <span class="to"></span>
        </div>
        <?php echo wc_query_string_form_fields( null, array( 'min_price', 'max_price', 'paged' ), '', true ); ?>
        <div class="clear"></div>
    </div>
</div>