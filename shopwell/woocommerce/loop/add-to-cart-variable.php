<?php
/**
 * Variable product card add to cart
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/loop/add-to-cart-variable.php.
 */

defined( 'ABSPATH' ) || exit;

global $product;

$attribute_keys  = array_keys( $attributes );
$variations_json = wp_json_encode( $available_variations );

do_action( 'woocommerce_before_add_to_cart_form' ); ?>

	<form class="variations_form cart variations_form_loop"
			action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ); ?>"
			method="post" enctype='multipart/form-data'
			data-product_id="<?php echo absint( $product->get_id() ); ?>"
			data-product_variations="<?php echo wc_esc_json( $variations_json ); // WPCS: XSS ok. ?>">
		<?php do_action( 'woocommerce_before_variations_form' ); ?>

		<?php if ( empty( $available_variations ) && false !== $available_variations ) : ?>
			<p class="stock out-of-stock"><?php echo esc_html( apply_filters( 'woocommerce_out_of_stock_message', __( 'This product is currently out of stock and unavailable.', 'shopwell' ) ) ); ?></p>
		<?php else : ?>
			<table class="variations" cellspacing="0">
				<tbody>
				<?php 
				// Process URL parameters for pre-selection
				$url_selections = array();
				$simplified_map = array(
					'culoare' => array('attribute_pa_culoare', 'attribute_culoare'),
					'stare' => array('attribute_pa_stare', 'attribute_stare'),
					'memorie' => array('attribute_pa_memorie', 'attribute_memorie', 'attribute_pa_stocare', 'attribute_stocare'),
				);
				
				foreach ($simplified_map as $param => $attrs) {
					if (isset($_GET[$param]) && !empty($_GET[$param])) {
						foreach ($attrs as $attr) {
							if (isset($attributes[$attr])) {
								$url_selections[$attr] = sanitize_text_field($_GET[$param]);
								break;
							}
						}
					}
				}
				
				foreach ( $attributes as $attribute_name => $options ) : 
					$selected_value = isset($url_selections[$attribute_name]) ? $url_selections[$attribute_name] : '';
					$label_text = wc_attribute_label( $attribute_name );
					
					// Add selected value to label if exists
					if ($selected_value) {
						// Get display name
						$display_name = $selected_value;
						$taxonomy = str_replace('attribute_', '', $attribute_name);
						if (taxonomy_exists($taxonomy)) {
							$term = get_term_by('slug', $selected_value, $taxonomy);
							if ($term && !is_wp_error($term)) {
								$display_name = $term->name;
							}
						}
						$label_text .= ': ' . esc_html($display_name);
					}
				?>
					<tr>
						<td class="label"><label
									for="<?php echo esc_attr( sanitize_title( $attribute_name ) ); ?>"><?php echo $label_text; // WPCS: XSS ok. ?></label>
						</td>
						<td class="value">
							<?php
							$dropdown_args = array(
								'options'   => $options,
								'attribute' => $attribute_name,
								'product'   => $product,
							);
							
							// Pre-select value from URL
							if ($selected_value) {
								$dropdown_args['selected'] = $selected_value;
							}
							
							wc_dropdown_variation_attribute_options($dropdown_args);
							?>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>

			<div class="single_variation_wrap">
				<?php

				/**
				 * Hook: woocommerce_single_variation. Used to output the cart button and placeholder for variation data.
				 *
				 * @since 2.4.0
				 * @hooked woocommerce_single_variation - 10 Empty div for variation data.
				 * @hooked woocommerce_single_variation_add_to_cart_button - 20 Qty and cart button.
				 */
				do_action( 'woocommerce_single_variation' );

				?>
			</div>
		<?php endif; ?>

	</form>

<?php
