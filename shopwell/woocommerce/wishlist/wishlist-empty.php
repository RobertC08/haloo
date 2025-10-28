<?php
/**
 * Template for displaying the empty wishlist.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/wishlist/wishlist-empty.php.
 *
 * @author  WCBoost
 * @package WCBoost\Wishlist\Templates
 * @version 1.0.10
 */

defined( 'ABSPATH' ) || exit;

do_action( 'wcboost_wishlist_before_wishlist' ); ?>
<div class="wishlist-empty-form">
	<div class="wishlist-empty-image">
		<img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/shopwell-wishlist-empty.svg' ) ); ?>" alt="<?php esc_attr_e( 'empty wishlist.', 'shopwell' ); ?>">
	</div>

	<div class="wishlist-empty woocommerce-info">
		<?php echo wp_kses_post( apply_filters( 'wcboost_wishlist_empty_message', __( 'Looks like you don&rsquo;t have anything saved', 'shopwell' ) ) ); ?>
	</div>
	<div class="wishlist-empty woocommerce-info-description">
		<?php echo wp_kses_post( apply_filters( 'wcboost_wishlist_empty_message_description', __( 'Sign in to sync your Saved Items across all your devices.', 'shopwell' ) ) ); ?>
	</div>

	<p class="return-to-shop">
		<?php
		echo wp_kses_post(
			apply_filters(
				'wcboost_wishlist_return_to_shop_link',
				sprintf(
					'<a href="%s" class="button wc-backward">%s</a>',
					esc_url( wc_get_page_permalink( 'myaccount' ) ),
					esc_html__( 'Sign in', 'shopwell' )
				),
				$args
			)
		);
		?>
	</p>
</div>

<?php do_action( 'wcboost_wishlist_after_wishlist' ); ?>
