<?php
/**
 * Lost password form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/form-lost-password.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.2.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_lost_password_form' );
?>
<div class="woocommerce-account__summary">
<div class="woocommerce-account__heading">
		<h2 class="active" class="woocommerce-column__title"><?php esc_html_e( 'Forgot Password?', 'shopwell' ); ?></h2>
	</div>
	<form method="post" class="woocommerce-ResetPassword lost_reset_password">

		<p class="reset-depscription"><?php echo apply_filters( 'woocommerce_lost_password_message', esc_html__( 'Please enter your Email Address. You will receive a link to create a new password via Email.', 'shopwell' ) ); ?></p><?php // @codingStandardsIgnoreLine ?>

		<p class="woocommerce-form-row woocommerce-form-row--first form-row form-row-wide">
			<label for="user_login"><?php echo esc_html__( 'Email', 'shopwell' ); ?></label>
			<input class="woocommerce-Input woocommerce-Input--text input-text" type="text" name="user_login" id="user_login" autocomplete="username" required aria-required="true"/>
		</p>

		<div class="clear"></div>

		<?php do_action( 'woocommerce_lostpassword_form' ); ?>

		<p class="woocommerce-form-row form-row form-row-wide woocommerce-form__submit">
			<input type="hidden" name="wc_reset_password" value="true" />
			<button type="submit" class="woocommerce-Button button<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" value="<?php esc_attr_e( 'Reset password', 'shopwell' ); ?>"><?php esc_html_e( 'Reset password', 'shopwell' ); ?></button>
		</p>

		<?php wp_nonce_field( 'lost_password', 'woocommerce-lost-password-nonce' ); ?>

	</form>
</div>
<?php
do_action( 'woocommerce_after_lost_password_form' );
