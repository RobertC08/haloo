<?php
/**
 * Template part for displaying side product content
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Shopwell
 */
?>

<div class="side-products">
	<h2><?php echo esc_html__( 'People Also Viewed', 'shopwell' ); ?></h2>
		<ul class="products <?php echo esc_attr( str_replace( '_', '-', $args['type'] ) ); ?>">
	<?php
	$original_post = $GLOBALS['product'];
	foreach ( $args['query'] as $product_id ) {
		$_product           = is_numeric( $product_id ) ? wc_get_product( $product_id ) : $product_id;
		$GLOBALS['product'] = $_product; // WPCS: override ok.
		?>
		<li>
			<a href="<?php echo esc_url( $_product->get_permalink() ); ?>">
				<?php echo wp_kses_post( $_product->get_image( 'woocommerce_gallery_thumbnail' ) ); ?>
				<span class="product-info">
					<span class="product-title"><?php echo wp_kses_post( $_product->get_name() ); ?></span>
					<?php
					if ( $_product->get_rating_count() ) {
						echo '<span class="shopwell-rating">';
							echo wc_get_rating_html( $_product->get_average_rating() );
						if ( intval( $_product->get_review_count() ) > 0 ) {
							?>
								<span class="review-count"><?php printf( '(%s)', esc_html( $_product->get_review_count() ) ); ?></span>
								<?php
						}
						echo '</span>';
					}
					?>
					<span class="product-price"><?php echo wp_kses_post( $_product->get_price_html() ); ?></span>
				</span>
			</a>
		</li>
		<?php
	}
	$GLOBALS['product'] = $original_post; // WPCS: override ok.
	wp_reset_postdata();
	?>
	</ul>
</div>
