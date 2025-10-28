<?php
/**
 * Plugin utilities class.
 *
 * This class has functions to install, activate & deactivate plugins.
 *
 * @package Shopwell
 * @author Peregrine Themes
 * @since   1.0.0
 */
namespace Shopwell\Admin\Utilities;

/**
 * Do not allow direct script access.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin utilities.
 * Class that contains methods for changing plugin status.
 *
 * @since 1.0.0
 */
class Shopwell_Plugin_Utilities {

	/**
	 * Singleton instance of the class.
	 *
	 * @since 1.0.0
	 * @var object
	 */
	private static $instance;

	/**
	 * Main Shopwell Plugin Utilities Instance.
	 *
	 * @since 1.0.0
	 * @return Shopwell_Plugin_Utilities
	 */
	public static function instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Shopwell_Plugin_Utilities ) ) {
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
	}

	/**
	 * Activate array of plugins.
	 *
	 * @param array, $plugins Plugins to be activated.
	 * @since 1.0.0
	 */
	public function activate_plugins( $plugins ) {

		$status = array();

		wp_clean_plugins_cache( false );

		// Activate plugins.
		foreach ( $plugins as $plugin ) {
			$status[ $plugin['slug'] ]['activate'] = $this->activate_plugin( $plugin['slug'] );
		}

		return $status;
	}

	/**
	 * Activate individual plugin.
	 *
	 * @param array, $plugin Plugin to be activated.
	 * @return void|WP_Error
	 * @since 1.0.0
	 */
	public function activate_plugin( $plugin ) {

		// Check permissions.
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return new WP_Error(
				'plugin_activation_failed',
				esc_html__( 'Current user can\'t activate plugins', 'shopwell' )
			);
		}

		// Validate plugin data.
		if ( empty( $plugin ) ) {
			return new WP_Error(
				'plugin_activation_failed',
				esc_html__( 'Missing plugin data.', 'shopwell' )
			);
		}

		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php'; // phpcs:ignore
		}

		$plugin_data = get_plugins( '/' . $plugin );

		if ( empty( $plugin_data ) ) {
			return new WP_Error(
				'plugin_activation_failed',
				sprintf(
					// translators: %s is plugin name.
					esc_html__( 'Plugin %s is not installed.', 'shopwell' ),
					$plugin
				)
			);
		}

		$plugin_file_array  = array_keys( $plugin_data );
		$plugin_file        = $plugin_file_array[0];
		$plugin_to_activate = $plugin . '/' . $plugin_file;
		$activate           = activate_plugin( $plugin_to_activate );

		if ( is_wp_error( $activate ) ) {
			return $activate;
		}

		do_action( 'shopwell_plugin_activated_' . $plugin );
	}

	/**
	 * Deactivate individual plugin
	 *
	 * @param array, $plugin Plugin to be deactivated.
	 * @return void|WP_Error
	 * @since 1.0.0
	 */
	public function deactivate_plugin( $plugin ) {

		// Check permissions.
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return new WP_Error(
				'plugin_activation_failed',
				esc_html__( 'Current user can\'t activate plugins', 'shopwell' )
			);
		}

		// Validate plugin data.
		if ( empty( $plugin ) ) {
			return new WP_Error(
				'plugin_activation_failed',
				esc_html__( 'Missing plugin data.', 'shopwell' )
			);
		}

		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php'; // phpcs:ignore
		}

		$plugin_data = get_plugins( '/' . $plugin );

		if ( empty( $plugin_data ) ) {
			return new WP_Error(
				'plugin_deactivation_failed',
				sprintf(
					// translators: %s is plugin name.
					esc_html__( 'Plugin %s is not active.', 'shopwell' ),
					$plugin
				)
			);
		}

		$plugin_file_array    = array_keys( $plugin_data );
		$plugin_file          = $plugin_file_array[0];
		$plugin_to_deactivate = $plugin . '/' . $plugin_file;

		deactivate_plugins( $plugin_to_deactivate );

		do_action( 'shopwell_plugin_deactivated_' . $plugin );
	}

	/**
	 * Check if plugin has a pending update.
	 *
	 * @param array,   $plugin Plugin to be activated.
	 * @param boolean, $strict Force plugin to update. Optional. Default is false.
	 * @since 1.0.0
	 */
	public function has_update( $plugin, $strict = false ) {

		$installed_plugin = $this->is_installed( $plugin['slug'] );

		if ( is_array( $installed_plugin ) && ! empty( $installed_plugin ) ) {

			$plugin_name = array_keys( $installed_plugin );
			$plugin_name = $plugin_name[0];

			$plugin_version = $installed_plugin ? $installed_plugin[ $plugin_name ]['Version'] : null;

			if ( $plugin_name && ! empty( $plugin_version ) ) {
				if ( isset( $plugin['version'] ) ) {
					return version_compare( $plugin_version, $plugin['version'], '<' );
				} elseif ( $strict ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Check if plugin is installed.
	 *
	 * @param array, $plugin Check if plugin is installed.
	 * @since 1.0.0
	 */
	public function is_installed( $plugin ) {

		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php'; // phpcs:ignore
		}

		$installed = get_plugins( '/' . $plugin );

		if ( ! empty( $installed ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if plugin is activated.
	 *
	 * @param array, $plugin Check if plugin is activated.
	 * @since 1.0.0
	 * @return bool Whether the plugin is activated.
	 */
	public function is_activated( $plugin ) {
		// Prevent a suppressed error within `get_plugins()` when the plugin is not installed.
		if ( ! file_exists( WP_PLUGIN_DIR . '/' . $plugin ) ) {
			return false;
		}

		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php'; // phpcs:ignore
		}

		$installed_plugin = get_plugins( '/' . $plugin );

		if ( $installed_plugin ) {
			$plugin_name = array_keys( $installed_plugin );
			return is_plugin_active( $plugin . '/' . $plugin_name[0] );
		}

		return false;
	}

	/**
	 * Recommended plugins.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function get_recommended_plugins() {

		$plugins = array(
			'hester-core'                => array(
				'name'  => 'Hester Core',
				'slug'  => 'hester-core',
				'desc'  => 'The Hester Core plugin adds extra functionality to hester theme, such as Demo Library, widgets, custom blocks and more.',
				'thumb' => 'https://ps.w.org/hester-core/assets/icon-256x256.png',
			),
			'meta-box'                   => array(
				'name'  => 'Meta Box',
				'slug'  => 'meta-box',
				'desc'  => 'Meta Box is a powerful, professional, and lightweight toolkit for developers to create custom meta boxes and custom fields for any custom post type in WordPress.',
				'thumb' => 'https://ps.w.org/meta-box/assets/icon-128x128.png',
			),
			'woocommerce'                => array(
				'name'     => 'WooCommerce',
				'slug'     => 'woocommerce',
				'desc'     => 'WooCommerce is the open-source ecommerce platform for WordPress.',
				'required' => true,
				'thumb'    => 'https://ps.w.org/woocommerce/assets/icon-256x256.gif',
			),
			'wcboost-variation-swatches' => array(
				'name'  => 'WCBoost - Variation Swatches',
				'slug'  => 'wcboost-variation-swatches',
				'desc'  => 'WCBoost - Variation Swatches is a WooCommerce extension that enhances the appearance and functionality of variable products.',
				'thumb' => 'https://ps.w.org/wcboost-variation-swatches/assets/icon-128x128.png',
			),
			'wcboost-wishlist'           => array(
				'name'  => 'WCBoost - Wishlist',
				'slug'  => 'wcboost-wishlist',
				'desc'  => 'Wishlist is a key feature in e-commerce websites. These websites benefit from increased conversion rates, optimized revenues, and simplified consumer buying processes.',
				'thumb' => 'https://ps.w.org/wcboost-wishlist/assets/icon-128x128.png',
			),
			'wcboost-products-compare'   => array(
				'name'  => 'WCBoost - Products Compare',
				'slug'  => 'wcboost-products-compare',
				'desc'  => 'When dealing with numerous similar products, customers frequently find it difficult to make a purchase decision. As a result, making it simple for buyers to compare similar products is an important aspect of e-commerce websites. ',
				'thumb' => 'https://ps.w.org/wcboost-products-compare/assets/icon-128x128.png',
			),
			'elementor'                  => array(
				'name'  => 'Elementor Page Builder',
				'slug'  => 'elementor',
				'desc'  => 'The #1 no code drag & drop wordpress website builder powering over 16m websites worldwide, now with ai.',
				'thumb' => 'https://ps.w.org/elementor/assets/icon-256x256.gif',
			),
			'contact-form-7'             => array(
				'name'  => 'Contact Form 7',
				'slug'  => 'contact-form-7',
				'desc'  => 'Contact Form 7 can manage multiple contact forms, plus you can customize the form and the mail contents flexibly with simple markup.',
				'thumb' => 'https://ps.w.org/contact-form-7/assets/icon.svg',
			),
			'mailchimp-for-wp'           => array(
				'name'  => 'MC4WP: Mailchimp for WordPress',
				'slug'  => 'mailchimp-for-wp',
				'desc'  => 'Allowing your visitors to subscribe to your newsletter should be easy. With this plugin, it finally is.',
				'thumb' => 'https://ps.w.org/mailchimp-for-wp/assets/icon-256x256.png',
			),
		);

		return apply_filters( 'shopwell_recommended_plugins', $plugins );
	}

	/**
	 * Get non activated plugins from an array.
	 *
	 * @param array, $plugins Filter non active plugins.
	 * @since 1.0.0
	 */
	public function get_deactivated_plugins( $plugins ) {

		if ( is_array( $plugins ) && ! empty( $plugins ) ) {
			foreach ( $plugins as $slug => $plugin ) {
				if ( $this->is_activated( $slug ) ) {
					unset( $plugins[ $slug ] );
				}
			}
		}

		return $plugins;
	}

	/**
	 * Get plugin object based on slug.
	 *
	 * @since 1.0.0
	 * @param string $slug Plugin slug.
	 * @param array  $plugins Array of available plugins.
	 */
	public function get_plugin_by_slug( $slug, $plugins ) {

		if ( ! empty( $plugins ) ) {
			foreach ( $plugins as $plugin ) {
				if ( $plugin['slug'] === $slug ) {
					return $plugin;
				}
			}
		}

		return false;
	}
}
