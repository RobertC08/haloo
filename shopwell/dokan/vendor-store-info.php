<?php
/**
 * Dokan vendor information template on product page
 *
 * @since   3.3.7
 *
 * @param Object $vendor
 * @param Array  $store_info
 * @param Array  $store_rating
 *
 * @package dokan
 */

$count = $store_rating['count'];
?>

<div class="dokan-vendor-info-wrap">
	<div class="dokan-vendor-image">
		<img src="<?php echo esc_url( $vendor->get_avatar() ); ?>" alt="<?php echo esc_attr( $store_info['store_name'] ); ?>">
	</div>
	<div class="dokan-vendor-info">
		<div class="dokan-vendor-name">
			<a href="<?php echo esc_attr( $vendor->get_shop_url() ); ?>"><h5><?php echo esc_html( $store_info['store_name'] ); ?></h5></a>
			<?php apply_filters( 'dokan_product_single_after_store_name', $vendor ); ?>
		</div>
		<div class="dokan-vendor-rating product">
			<?php echo wp_kses_post( dokan_generate_ratings( $store_rating['rating'], 5 ) ); ?>
			<?php if ( $count ) : ?>
				<?php // translators: %d reviews count ?>
				<p class="dokan-ratings-count"><?php echo esc_html( $store_rating['rating'] ); ?> (<?php printf( _n( '%s Review', '%s Reviews', $count, 'shopwell' ), esc_html( number_format_i18n( $count ) ) ); ?>)</p>
			<?php endif; ?>
		</div>
	</div>
</div>
