<?php
/**
 * Block Editor functions
 *
 * @package Shopwell
 */

namespace Shopwell\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Block Editor
 */
class Transients {
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
		add_action( 'save_post', array( $this, 'flush_transients' ) );
		add_action( 'wp_trash_post', array( $this, 'flush_transients' ) );
		add_action( 'before_delete_post', array( $this, 'flush_transients' ) );
	}

	/**
	 * Flush caches while updating a post
	 *
	 * @param int $post_id
	 */
	function flush_transients( $post_id ) {
		if ( $parent_id = wp_is_post_revision( $post_id ) ) {
			$post_id = $parent_id;
		}

		if ( 'post' == get_post_type( $post_id ) ) {
			delete_transient( 'shopwell_featured_post_ids' );
		}
	}
}
