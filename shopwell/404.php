<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @link https://codex.wordpress.org/Creating_an_Error_404_Page
 *
 * @package Shopwell
 */

$layout = \Shopwell\Helper::get_option( 'page_404_layout' );

get_header();
?>
<?php if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'single' ) ) { ?>
	<div id="primary" class="content-area">
			<?php if ( $layout == 'v1' ) : ?>
				<div class="error-404 error-404__v1 not-found">
					<?php echo \Shopwell\Icon::get_svg( 'error', 'ui', 'class=error-404__icon' ); ?>
					<h1 class="error-404__title"><?php esc_html_e( 'Oops!', 'shopwell' ); ?></h1>
					<div class="error-404__content">
						<?php esc_html_e( 'We&rsquo;re sorry! This page is currently unavailable. Please try again later.', 'shopwell' ); ?>
					</div>
					<a href="<?php echo esc_url( get_home_url() ); ?>"
					class="shopwell-button error-404__button shopwell-shape--base shopwell-button--bg-color-black shopwell-button--large"><?php echo esc_html__( 'Go to Homepage', 'shopwell' ); ?></a>
				</div>
			<?php else : ?>
				<div class="error-404 error-404__v2 not-found">
					<div class="error-404__image">
						<img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/shopwell-404-v2.webp' ) ); ?>" alt="<?php echo esc_attr__( '404 Image', 'shopwell' ); ?>">
					</div>
					<div class="error-404__wrapper">
						<h1 class="error-404__title"><?php esc_html_e( '404', 'shopwell' ); ?></h1>
						<div class="error-404__content">
							<h3><?php esc_html_e( 'Page Not Found', 'shopwell' ); ?></h3>
							<?php esc_html_e( 'We&rsquo;re sorry! This page is currently unavailable. Please try again later.', 'shopwell' ); ?>
						</div>
						<a href="<?php echo esc_url( get_home_url() ); ?>"
						class="shopwell-button error-404__button shopwell-shape--base shopwell-button--bg-color-black shopwell-button--large"><?php echo esc_html__( 'Go to Homepage', 'shopwell' ); ?></a>
					</div>
				</div>
			<?php endif; ?>

	</div><!-- #primary -->
<?php } ?>
<?php
get_footer();
