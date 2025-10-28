<?php
/**
 * Admin functions and definitions.
 *
 * @package Shopwell
 */

namespace Shopwell;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Mobile initial
 */
class Admin {
	/**
	 * Instance
	 *
	 * @var $instance
	 */
	protected static $instance = null;

	/**
	 * Initiator
	 *
	 * @since 1.0.0
	 * @return object
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}



	/**
	 * Instantiate the object.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function __construct() {
		if ( ! is_admin() ) {
			return;
		}

		\Shopwell\Admin\Block_Editor::instance();
		\Shopwell\Admin\Meta_Boxes::instance();
		\Shopwell\Admin\Transients::instance();
		\Shopwell\Admin\Dashboard\Shopwell_Admin::instance();
	}
}
