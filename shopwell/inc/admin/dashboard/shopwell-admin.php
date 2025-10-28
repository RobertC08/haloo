<?php
/**
 * Admin class.
 *
 * This class ties together all admin classes.
 *
 * @package     Shopwell
 * @author      Peregrine Themes
 * @since       1.0.0
 */
namespace Shopwell\Admin\Dashboard;

/**
 * Do not allow direct script access.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Shopwell_Admin' ) ) :

	/**
	 * Admin Class
	 */
	class Shopwell_Admin {

		/**
		 * Singleton instance of the class.
		 *
		 * @since 1.0.0
		 * @var object
		 */
		private static $instance;

		/**
		 * Main Shopwell Dashboard Instance.
		 *
		 * @since 1.0.0
		 * @return Shopwell_Dashboard
		 */
		public static function instance() {

			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Primary class constructor.
		 *
		 * @since 1.0.0
		 */
		public function __construct() {

			/**
			 * Include admin files.
			 */
			$this->includes();

			/**
			 * Load admin assets.
			 */
			add_action( 'admin_enqueue_scripts', array( $this, 'load_assets' ) );

			/**
			 * Add filters for WordPress header and footer text.
			 */
			add_filter( 'update_footer', array( $this, 'filter_update_footer' ), 50 );
			add_filter( 'admin_footer_text', array( $this, 'filter_admin_footer_text' ), 50 );

			/**
			 * Admin page header.
			 */
			add_action( 'in_admin_header', array( $this, 'admin_header' ), 100 );

			/**
			 * Admin page footer.
			 */
			add_action( 'in_admin_footer', array( $this, 'admin_footer' ), 100 );

			/**
			 * Add notices.
			 */
			add_action( 'admin_notices', array( $this, 'admin_notices' ) );

			/**
			 * After admin loaded
			 */
			do_action( 'shopwell_admin_loaded' );
		}

		/**
		 * Includes files.
		 *
		 * @since 1.0.0
		 */
		private function includes() {

			/**
			 * Include helper functions.
			 */
			require_once SHOPWELL_THEME_PATH . '/inc/admin/dashboard/helpers.php'; // phpcs:ignore

			/**
			 * Include Shopwell welcome page.
			 */
			require_once SHOPWELL_THEME_PATH . '/inc/admin/dashboard/shopwell-dashboard.php'; // phpcs:ignore
		}

		/**
		 * Load our required assets on admin pages.
		 *
		 * @since 1.0.0
		 * @param string $hook it holds the information about the current page.
		 */
		public function load_assets( $hook ) {

			/**
			 * Do not enqueue if we are not on one of our pages.
			 */
			if ( ! shopwell_is_admin_page( $hook ) ) {
				return;
			}

			// Script debug.
			$prefix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? 'dev/' : '';
			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

			/**
			 * Enqueue admin pages stylesheet.
			 */
			wp_enqueue_style(
				'shopwell-admin-styles',
				SHOPWELL_THEME_URI . '/inc/admin/dashboard/assets/css/shopwell-admin' . $suffix . '.css',
				false,
				SHOPWELL_THEME_VERSION
			);

			/**
			 * Enqueue admin pages script.
			 */
			wp_enqueue_script(
				'shopwell-admin-script',
				SHOPWELL_THEME_URI . '/inc/admin/dashboard/assets/js/' . $prefix . 'shopwell-admin' . $suffix . '.js',
				array( 'jquery', 'wp-util', 'updates' ),
				SHOPWELL_THEME_VERSION,
				true
			);

			/**
			 * Localize admin strings.
			 */
			$texts = array(
				'install'               => esc_html__( 'Install', 'shopwell' ),
				'install-inprogress'    => esc_html__( 'Installing...', 'shopwell' ),
				'activate-inprogress'   => esc_html__( 'Activating...', 'shopwell' ),
				'deactivate-inprogress' => esc_html__( 'Deactivating...', 'shopwell' ),
				'active'                => esc_html__( 'Active', 'shopwell' ),
				'retry'                 => esc_html__( 'Retry', 'shopwell' ),
				'please_wait'           => esc_html__( 'Please Wait...', 'shopwell' ),
				'importing'             => esc_html__( 'Importing... Please Wait...', 'shopwell' ),
				'currently_processing'  => esc_html__( 'Currently processing: ', 'shopwell' ),
				'import'                => esc_html__( 'Import', 'shopwell' ),
				'import_demo'           => esc_html__( 'Import Demo', 'shopwell' ),
				'importing_notice'      => esc_html__( 'The demo importer is still working. Closing this window may result in failed import.', 'shopwell' ),
				'import_complete'       => esc_html__( 'Import Complete!', 'shopwell' ),
				'import_complete_desc'  => esc_html__( 'The demo has been imported.', 'shopwell' ) . ' <a href="' . esc_url( get_home_url() ) . '">' . esc_html__( 'Visit site.', 'shopwell' ) . '</a>',
			);

			$strings = array(
				'ajaxurl'       => admin_url( 'admin-ajax.php' ),
				'wpnonce'       => wp_create_nonce( 'shopwell_nonce' ),
				'texts'         => $texts,
				'color_pallete' => array( '#0554f2', '#06cca6', '#2c2e3a', '#e4e7ec', '#f0b849', '#ffffff', '#000000' ),
			);

			$strings = apply_filters( 'shopwell_admin_strings', $strings );

			wp_localize_script( 'shopwell-admin-script', 'hester_strings', $strings );
		}

		/**
		 * Filters WordPress footer right text to hide all text.
		 *
		 * @since 1.0.0
		 * @param string $text Text that we're going to replace.
		 */
		public function filter_update_footer( $text ) {

			$base = get_current_screen()->base;

			/**
			 * Only do this if we are on one of our plugin pages.
			 */
			if ( shopwell_is_admin_page( $base ) ) {
				return apply_filters( 'shopwell_footer_version', esc_html__( 'Shopwell Theme', 'shopwell' ) . ' ' . SHOPWELL_THEME_VERSION . '<br/><a href="' . esc_url( 'https://twitter.com/peregrine-themes' ) . '" target="_blank" rel="noopener noreferrer"><span class="dashicons dashicons-twitter"></span></a><a href="' . esc_url( 'https://facebook.com/peregrine-themes' ) . '" target="_blank" rel="noopener noreferrer"><span class="dashicons dashicons-facebook"></span></a>' );
			} else {
				return $text;
			}
		}

		/**
		 * Filter WordPress footer left text to display our text.
		 *
		 * @since 1.0.0
		 * @param string $text Text that we're going to replace.
		 */
		public function filter_admin_footer_text( $text ) {

			if ( shopwell_is_admin_page() ) {
				return;
			}

			return $text;
		}

		/**
		 * Outputs the page admin header.
		 *
		 * @since 1.0.0
		 */
		public function admin_header() {

			$base = get_current_screen()->base;

			if ( ! shopwell_is_admin_page( $base ) ) {
				return;
			}
			?>

			<div id="hester-header">
				<div class="hester-container">

					<a href="<?php echo esc_url( admin_url( 'admin.php?page=shopwell-dashboard' ) ); ?>" class="hester-logo">
						<img src="<?php echo esc_url( SHOPWELL_THEME_URI . '/assets/images/shopwell-logo.svg' ); ?>" alt="<?php echo esc_html( 'Shopwell' ); ?>" />
					</a>

					<span class="hester-header-action">
						<a href="<?php echo esc_url( admin_url( 'customize.php' ) ); ?>"><?php esc_html_e( 'Customize', 'shopwell' ); ?></a>
						<a href="<?php echo esc_url( 'http://docs.peregrine-themes.com/' ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Help Articles', 'shopwell' ); ?></a>
					</span>

				</div>
			</div><!-- END #hester-header -->
			<?php
		}

		/**
		 * Outputs the page admin footer.
		 *
		 * @since 1.0.0
		 */
		public function admin_footer() {

			$base = get_current_screen()->base;

			if ( ! shopwell_is_admin_page( $base ) || shopwell_is_admin_page( $base, 'hester_wizard' ) ) {
				return;
			}
			?>
			<div id="hester-footer">
			<ul>
				<li><a href="<?php echo esc_url( 'http://docs.peregrine-themes.com/' ); ?>" target="_blank" rel="noopener noreferrer"><span><?php esc_html_e( 'Help Articles', 'shopwell' ); ?></span></span></a></li>
				<li><a href="<?php echo esc_url( 'https://www.facebook.com/groups/peregrine-themes/' ); ?>" target="_blank" rel="noopener noreferrer"><span><?php esc_html_e( 'Join Facebook Group', 'shopwell' ); ?></span></span></a></li>
				<li><a href="<?php echo esc_url( 'https://wordpress.org/support/theme/shopwell/reviews/#new-post' ); ?>" target="_blank" rel="noopener noreferrer"><span class="dashicons dashicons-heart" aria-hidden="true"></span><span><?php esc_html_e( 'Leave a Review', 'shopwell' ); ?></span></a></li>
			</ul>
			</div><!-- END #hester-footer -->

			<?php
		}

		/**
		 * Admin Notices
		 *
		 * @since 1.0.0
		 */
		public function admin_notices() {

			$screen = get_current_screen();

			// Display on Dashboard, Themes and Shopwell admin pages.
			if ( ! in_array( $screen->base, array( 'dashboard', 'themes' ), true ) && ! shopwell_is_admin_page() ) {
				return;
			}

			// Display if not dismissed and not on Shopwell plugins page.
			if ( ! shopwell_is_notice_dismissed( 'shopwell_notice_recommended-plugins' ) && ! shopwell_is_admin_page( false, 'shopwell-plugins' ) ) {

				$plugins = shopwell_plugin_utilities()->get_recommended_plugins();
				$plugins = shopwell_plugin_utilities()->get_deactivated_plugins( $plugins );

				$plugin_list = '';

				if ( is_array( $plugins ) && ! empty( $plugins ) ) {

					foreach ( $plugins as $slug => $plugin ) {

						$url = admin_url( 'plugin-install.php?tab=plugin-information&plugin=' . esc_attr( $slug ) . '&TB_iframe=true&width=990&height=500' );

						$plugin_list .= '<a href="' . esc_url( $url ) . '" class="thickbox">' . esc_html( $plugin['name'] ) . '</a>, ';
					}

					wp_enqueue_script( 'plugin-install' );
					add_thickbox();

					$plugin_list = trim( $plugin_list, ', ' );

					/* translators: %1$s <strong> tag, %2$s </strong> tag */
					$message = sprintf( wp_kses_post( __( 'Shopwell theme recommends the following plugins: %1$s.', 'shopwell' ) ), $plugin_list );

					$navigation_items = shopwell_dashboard()->get_navigation_items();

					shopwell_print_notice(
						array(
							'type'        => 'info',
							'message'     => $message,
							'message_id'  => 'recommended-plugins',
							'expires'     => 7 * 24 * 60 * 60,
							'action_link' => $navigation_items['plugins']['url'],
							'action_text' => esc_html__( 'Install Now', 'shopwell' ),
						)
					);
				}
			}
		}
	}
endif;
