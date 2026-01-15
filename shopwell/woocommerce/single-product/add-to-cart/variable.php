<?php
/**
 * Variable product add to cart
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/add-to-cart/variable.php.
 */

defined( 'ABSPATH' ) || exit;

global $product;

$attribute_keys  = array_keys( $attributes );
$variations_json = wp_json_encode( $available_variations );

// Process URL parameters for pre-selection and verify availability
$url_selections = array();
$simplified_map = array(
    'culoare' => array('attribute_pa_culoare', 'attribute_culoare'),
    'stare' => array('attribute_pa_stare', 'attribute_stare'),
    'memorie' => array('attribute_pa_memorie', 'attribute_memorie', 'attribute_pa_stocare', 'attribute_stocare'),
);

$attribute_hierarchy = array(
    'attribute_pa_culoare' => 1,
    'attribute_culoare' => 1,
    'attribute_pa_stare' => 2,
    'attribute_stare' => 2,
    'attribute_pa_memorie' => 3,
    'attribute_memorie' => 3,
    'attribute_pa_stocare' => 3,
    'attribute_stocare' => 3,
);

// First, collect all URL parameters
// Note: $attributes keys are like "pa_culoare", but variations use "attribute_pa_culoare"
$url_params_raw = array();
foreach ($simplified_map as $param => $attrs) {
    if (isset($_GET[$param]) && !empty($_GET[$param])) {
        foreach ($attrs as $attr) {
            // Convert attribute_xxx to xxx for checking against $attributes
            $attr_without_prefix = str_replace('attribute_', '', $attr);
            if (isset($attributes[$attr_without_prefix])) {
                // Store with full prefix for variation lookup
                $url_params_raw[$attr] = sanitize_text_field($_GET[$param]);
                break;
            }
        }
    }
}

// Then verify availability for each attribute in hierarchy order
foreach ($attributes as $attribute_name => $options) {
    // Convert to full attribute name for variation lookup
    $full_attr_name = 'attribute_' . $attribute_name;
    $attr_level = isset($attribute_hierarchy[$full_attr_name]) ? $attribute_hierarchy[$full_attr_name] : 999;
    
    // Get higher level selections
    $selected_attrs = array();
    foreach ($url_params_raw as $attr => $value) {
        $other_level = isset($attribute_hierarchy[$attr]) ? $attribute_hierarchy[$attr] : 999;
        if ($other_level < $attr_level) {
            $selected_attrs[$attr] = $value;
        }
    }
    
    // Check if URL has a value for this attribute
    if (isset($url_params_raw[$full_attr_name])) {
        $url_value = $url_params_raw[$full_attr_name];
        
        // Verify availability
        $is_available = false;
        if ($attr_level === 1) {
            // Level 1: check if ANY variation exists with this value
            foreach ($available_variations as $variation) {
                if (!$variation['is_in_stock']) continue;
                $v_val = isset($variation['attributes'][$full_attr_name]) ? $variation['attributes'][$full_attr_name] : '';
                if ($v_val && (strcasecmp($v_val, $url_value) === 0)) {
                    $is_available = true;
                    break;
                }
            }
        } else {
            // Other levels: check if variation exists matching all higher level selections
            $test_attrs = $selected_attrs;
            $test_attrs[$full_attr_name] = $url_value;
            
            foreach ($available_variations as $variation) {
                if (!$variation['is_in_stock']) continue;
                
                $matches = true;
                foreach ($test_attrs as $test_attr => $test_val) {
                    $v_val = isset($variation['attributes'][$test_attr]) ? $variation['attributes'][$test_attr] : '';
                    if ($v_val !== '' && strcasecmp($v_val, $test_val) !== 0) {
                        $matches = false;
                        break;
                    }
                }
                
                if ($matches) {
                    $is_available = true;
                    break;
                }
            }
        }
        
        // Only add to selections if available
        // Store with simple attribute name for dropdown matching
        if ($is_available) {
            $url_selections[$attribute_name] = $url_value;
        }
    }
}

do_action( 'woocommerce_before_add_to_cart_form' ); ?>

<form class="variations_form cart" action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ); ?>" method="post" enctype='multipart/form-data' data-product_id="<?php echo absint( $product->get_id() ); ?>" data-product_variations="<?php echo wc_esc_json( $variations_json ); ?>">
	<?php do_action( 'woocommerce_before_variations_form' ); ?>

	<?php if ( empty( $available_variations ) && false !== $available_variations ) : ?>
		<p class="stock out-of-stock"><?php echo esc_html( apply_filters( 'woocommerce_out_of_stock_message', __( 'This product is currently out of stock and unavailable.', 'woocommerce' ) ) ); ?></p>
	<?php else : ?>
		<table class="variations" cellspacing="0">
			<tbody>
				<?php foreach ( $attributes as $attribute_name => $options ) : 
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
						<td class="label"><label for="<?php echo esc_attr( sanitize_title( $attribute_name ) ); ?>"><?php echo $label_text; ?></label></td>
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
do_action( 'woocommerce_after_add_to_cart_form' );
