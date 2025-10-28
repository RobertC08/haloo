<?php
/**
 * Template for displaying wishlist table.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/wishlist/wishlist-table.php.
 *
 * @author  WCBoost
 * @package WCBoost\Wishlist\Templates
 * @version 1.1.5
 */

defined( 'ABSPATH' ) || exit;

if ( ! isset( $wishlist ) ) {
	return;
}

?>

<?php do_action( 'wcboost_wishlist_before_wishlist_table', $wishlist ); ?>

<form class="wcboost-wishlist-form" action="<?php echo esc_url( wc_get_page_permalink( 'wishlist' ) ); ?>" method="post">
	<ul class="products columns-4 shop_table shop_table_responsive wishlist_table wishlist" cellspacing="0">

			<?php
			foreach ( $wishlist->get_items() as $item_key => $item ) :
				/** @var WC_Product */
				$_product = $item->get_product();

				if ( ! $_product || ! $_product->exists() ) {
					continue;
				}

				$product_permalink = $_product->is_visible() ? $_product->get_permalink() : '';

				if ( ! $_product->is_in_stock() ) {
					$class = 'outofstock';
				} else {
					$class = '';
				}

				?>
				<li class="product <?php echo esc_attr( $class ); ?> <?php echo esc_attr( apply_filters( 'wcboost_wishlist_item_class', 'wcboost-wishlist-item', $item, $item_key ) ); ?>">
					<div class="product-inner">
						<?php if ( $wishlist->can_edit() ) : ?>
							<div class="product-remove">
								<?php
								echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									'wcboost_wishlist_item_remove_link',
									sprintf(
										'<a href="%s" class="remove swiper-button" aria-label="%s" data-product_id="%s" data-product_sku="%s">%s</a>',
										esc_url( $item->get_remove_url() ),
										esc_html__( 'Remove this item', 'shopwell' ),
										esc_attr( $_product->get_id() ),
										esc_attr( $_product->get_sku() ),
										\Shopwell\Icon::get_svg( 'trash' )
									),
									$item_key
								);
								?>
							</div>
						<?php endif; ?>

						<div class="product-thumbnail">
							<?php
							if ( ! $product_permalink ) {
								echo wp_kses_post( $_product->get_image() );
							} else {
								echo wp_kses_post( sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $_product->get_image() ) );
							}
							?>
						</div>

						<div class="product-summary">

							<h2 class="woocommerce-loop-product__title product-name" data-title="<?php esc_attr_e( 'Product', 'shopwell' ); ?>">
								<?php
								if ( ! $product_permalink ) {
									echo wp_kses_post( $_product->get_name() );
								} else {
									echo wp_kses_post( sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $_product->get_name() ) );
								}

								do_action( 'wcboost_wishlist_after_item_name', $item, $item_key, $wishlist );

								if ( $args['show_variation_data'] && $_product->is_type( 'variation' ) ) {
									echo wp_kses_post( wc_get_formatted_variation( $_product ) );
								}
								?>
							</h2>

							<?php
							$product = wc_get_product( $_product->get_id() );
							$rating  = $product->get_average_rating();
							$count   = $product->get_rating_count();

							if ( $rating ) {
								echo '<div class="shopwell-rating">';
								echo wc_get_rating_html( $rating, $count );

								if ( intval( $product->get_review_count() ) > 0 ) {
									?>
										<div class="review-count"><span class="average"><?php echo esc_html( $product->get_average_rating() ); ?></span> (<?php printf( _n( '%s<span class="text"> Review</span>', '%s<span class="text"> Reviews</span>', esc_html( $product->get_review_count() ), 'shopwell' ), '<span class="count">' . esc_html( $product->get_review_count() ) . '</span>' ); ?>)</div>
										<?php
								}
								echo '</div>';
							}
							?>

							<?php if ( $args['columns']['price'] ) : ?>
								<span class="price product-price" data-title="<?php esc_attr_e( 'Price', 'shopwell' ); ?>">
									<?php echo wp_kses_post( $_product->get_price_html() ); ?>
								</span>
							<?php endif; ?>

							<?php if ( $args['columns']['quantity'] && $wishlist->can_edit() ) : ?>
								<div class="product-quantity shopwell-qty-medium" data-title="<?php esc_attr_e( 'Quantity', 'shopwell' ); ?>">
									<?php
									if ( $_product->is_sold_individually() ) {
										printf( '1 <input type="hidden" name="wishlist_item[%s][qty]" value="1" />', $item_key );
									} else {
										woocommerce_quantity_input(
											array(
												'input_name'   => "wishlist_item[{$item_key}][qty]",
												'input_value'  => $item->get_quantity(),
												'max_value'    => $_product->get_max_purchase_quantity(),
												'min_value'    => '0',
												'product_name' => $_product->get_name(),
											),
											$_product
										);
									}
									?>
								</div>
							<?php endif; ?>

							<?php if ( $args['columns']['stock'] ) : ?>
								<div class="product-stock-status" data-title="<?php esc_attr_e( 'Stock status', 'shopwell' ); ?>">
									<?php
									$availability = $_product->get_availability();
									printf( '<span class="%s">%s</span>', esc_attr( $availability['class'] ), $availability['availability'] ? esc_html( $availability['availability'] ) : esc_html__( 'In Stock', 'shopwell' ) );
									?>
								</div>
							<?php endif; ?>

							<?php if ( $args['columns']['date'] ) : ?>
								<div class="product-date"><?php echo esc_html( $item->get_date_added()->format( get_option( 'date_format' ) ) ); ?></div>
							<?php endif; ?>

						</div>

						<?php if ( $args['columns']['purchase'] ) : ?>
							<div class="product-add-to-cart product-actions">
								<?php
								if ( $_product->is_purchasable() ) {
									$GLOBALS['product'] = $_product;

									woocommerce_template_loop_add_to_cart( array( 'quantity' => max( 1, $item->get_quantity() ) ) );

									wc_setup_product_data( $GLOBALS['post'] );
								}
								?>
							</div>
						<?php endif; ?>
					</div>

				</li>
				<?php
			endforeach;
			?>
	</ul>

	<?php do_action( 'wcboost_wishlist_after_wishlist_table', $wishlist ); ?>

	<?php if ( $wishlist->can_edit() && $args['columns']['quantity'] ) : ?>
		<div class="wcboost-wishlist-actions">
			<button type="submit" class="button alt" name="update_wishlist" value="<?php esc_attr_e( 'Update wishlist', 'shopwell' ); ?>"><?php esc_html_e( 'Update wishlist', 'shopwell' ); ?></button>
			<input type="hidden" name="wishlist_id" value="<?php echo esc_attr( $wishlist->get_id() ); ?>" />
			<?php wp_nonce_field( 'wcboost-wishlist-update' ); ?>
		</div>
	<?php endif; ?>

</form>
