<?php
/**
 * Reusable pattern: AJAX form → validate → save as private CPT + meta.
 * Copy folder `reusable-ajax-form-entries` into another theme or a small plugin
 * and call Reusable_Ajax_Form_Entries::register( $config ) from init or earlier.
 *
 * @package Shopwell
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Configuration keys (merge with defaults in ::register):
 *
 * - id (string)                Unique id for filters; default = post_type.
 * - post_type (string)         CPT slug (lowercase, underscores ok).
 * - post_type_args (array)     Passed to register_post_type (labels merged with generic).
 * - ajax_action (string)       admin-ajax action name (must match JS).
 * - nonce_action (string)      wp_nonce_field action.
 * - nonce_field (string)       POST key for nonce; default _wpnonce.
 * - meta_prefix (string)       Prefix for all post meta keys; default _rafe_.
 * - fields (array)             Field definitions (see README).
 * - title_builder (callable)   function( array $values ): string — post_title.
 * - duplicate_check (array|null) Optional: [ 'field' => 'email', 'message' => '...' ].
 * - success_message (string)   wp_send_json_success payload (string).
 * - filter_prefix (string)     For apply_filters "{$prefix}_..." ; default rafe.
 * - admin_columns (array)      [ 'meta_key_suffix' => 'Column label', ... ] — suffix without meta_prefix.
 * - show_details_metabox (bool) Show read-only metabox on single entry edit screen.
 */
final class Reusable_Ajax_Form_Entries {

	/**
	 * @var array<int, array<string, mixed>>
	 */
	private static $configs = array();

	/**
	 * @var bool
	 */
	private static $boot_hooked = false;

	/**
	 * @param array<string, mixed> $config .
	 * @return void
	 */
	public static function register( array $config ) {
		self::$configs[] = self::normalize_config( $config );

		if ( ! self::$boot_hooked ) {
			self::$boot_hooked = true;
			add_action( 'init', array( __CLASS__, 'boot' ), 9 );
		}
	}

	/**
	 * @return array<string, mixed>
	 */
	private static function default_config() {
		return array(
			'id'                   => '',
			'post_type'            => 'form_entry',
			'post_type_args'       => array(),
			'ajax_action'          => 'submit_form_entry',
			'nonce_action'         => 'form_entry_nonce',
			'nonce_field'          => '_wpnonce',
			'meta_prefix'          => '_rafe_',
			'fields'               => array(),
			'title_builder'        => null,
			'duplicate_check'      => null,
			'success_message'      => 'OK',
			'filter_prefix'        => 'rafe',
			'admin_columns'        => array(),
			'show_details_metabox' => true,
		);
	}

	/**
	 * @param array<string, mixed> $config .
	 * @return array<string, mixed>
	 */
	private static function normalize_config( array $config ) {
		$c = wp_parse_args( $config, self::default_config() );
		if ( $c['id'] === '' ) {
			$c['id'] = $c['post_type'];
		}
		if ( ! is_callable( $c['title_builder'] ) ) {
			$c['title_builder'] = static function ( array $values ) {
				$first = reset( $values );
				return is_string( $first ) && $first !== ''
					? sanitize_text_field( $first )
					: __( 'Form entry', 'shopwell' );
			};
		}
		return $c;
	}

	/**
	 * @return void
	 */
	public static function boot() {
		foreach ( self::$configs as $config ) {
			self::register_post_type( $config );
			add_action( 'wp_ajax_' . $config['ajax_action'], self::make_handler( $config ) );
			add_action( 'wp_ajax_nopriv_' . $config['ajax_action'], self::make_handler( $config ) );

			$pt = $config['post_type'];
			add_filter( "manage_{$pt}_posts_columns", self::make_columns_filter( $config ) );
			add_action( "manage_{$pt}_posts_custom_column", self::make_column_render( $config ), 10, 2 );

			if ( ! empty( $config['show_details_metabox'] ) ) {
				add_action(
					'add_meta_boxes',
					static function () use ( $config ) {
						add_meta_box(
							'rafe-details-' . $config['id'],
							__( 'Submitted data', 'shopwell' ),
							array( __CLASS__, 'render_metabox' ),
							$config['post_type'],
							'normal',
							'high',
							$config
						);
					}
				);
			}
		}
	}

	/**
	 * @param array<string, mixed> $config .
	 * @return void
	 */
	private static function register_post_type( array $config ) {
		$pt   = $config['post_type'];
		$args = wp_parse_args(
			$config['post_type_args'],
			array(
				'labels'              => array(
					'name'          => __( 'Form entries', 'shopwell' ),
					'singular_name' => __( 'Form entry', 'shopwell' ),
					'menu_name'     => __( 'Form entries', 'shopwell' ),
					'add_new'       => __( 'Add new', 'shopwell' ),
				),
				'public'              => false,
				'publicly_queryable'  => false,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'show_in_nav_menus'   => false,
				'show_in_admin_bar'   => false,
				'exclude_from_search' => true,
				'has_archive'         => false,
				'rewrite'             => false,
				'query_var'           => false,
				'capability_type'     => 'post',
				'map_meta_cap'        => true,
				'hierarchical'        => false,
				'supports'            => array( 'title' ),
				'menu_icon'           => 'dashicons-feedback',
			)
		);

		register_post_type( $pt, $args );
	}

	/**
	 * @param array<string, mixed> $config .
	 * @return callable(): void
	 */
	private static function make_handler( array $config ) {
		return static function () use ( $config ) {
			self::handle_ajax( $config );
		};
	}

	/**
	 * @param array<string, mixed> $config .
	 * @return callable(array<string, string>): array<string, string>
	 */
	private static function make_columns_filter( array $config ) {
		return static function ( $columns ) use ( $config ) {
			if ( ! is_array( $columns ) ) {
				return $columns;
			}
			$new = array();
			if ( isset( $columns['cb'] ) ) {
				$new['cb'] = $columns['cb'];
			}
			$new['title'] = __( 'Title', 'shopwell' );
			$prefix       = $config['filter_prefix'];
			foreach ( $config['admin_columns'] as $suffix => $label ) {
				$key          = 'rafe_' . sanitize_key( $suffix );
				$new[ $key ] = apply_filters( "{$prefix}_admin_column_label_{$config['id']}", $label, $suffix, $config );
			}
			$new['date'] = __( 'Date', 'shopwell' );
			return $new;
		};
	}

	/**
	 * @param array<string, mixed> $config .
	 * @return callable(string, int): void
	 */
	private static function make_column_render( array $config ) {
		return static function ( $column, $post_id ) use ( $config ) {
			if ( strpos( $column, 'rafe_' ) !== 0 ) {
				return;
			}
			$suffix = substr( $column, 5 );
			$meta   = get_post_meta( (int) $post_id, $config['meta_prefix'] . $suffix, true );
			if ( is_array( $meta ) ) {
				echo esc_html( wp_json_encode( $meta, JSON_UNESCAPED_UNICODE ) );
				return;
			}
			echo esc_html( is_scalar( $meta ) ? (string) $meta : '' );
		};
	}

	/**
	 * @param \WP_Post $post    Post object.
	 * @param array    $box    Metabox args (our config in 'args').
	 * @return void
	 */
	public static function render_metabox( $post, $box ) {
		$config = isset( $box['args'] ) && is_array( $box['args'] ) ? $box['args'] : array();
		if ( empty( $config['fields'] ) ) {
			return;
		}
		$prefix = $config['meta_prefix'];
		echo '<table class="widefat striped"><tbody>';
		foreach ( array_keys( $config['fields'] ) as $key ) {
			$val = get_post_meta( $post->ID, $prefix . $key, true );
			if ( is_array( $val ) ) {
				$display = wp_json_encode( $val, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
			} else {
				$display = (string) $val;
			}
			echo '<tr><th style="width:220px;text-align:left;vertical-align:top;">' . esc_html( $key ) . '</th><td><pre style="white-space:pre-wrap;margin:0;">' . esc_html( $display ) . '</pre></td></tr>';
		}
		$total = get_post_meta( $post->ID, $prefix . 'total_chances', true );
		if ( $total !== '' && $total !== false ) {
			echo '<tr><th>' . esc_html__( 'Total chances (computed)', 'shopwell' ) . '</th><td><strong>' . esc_html( (string) $total ) . '</strong></td></tr>';
		}
		echo '</tbody></table>';
	}

	/**
	 * @param array<string, mixed> $config .
	 * @return void
	 */
	private static function handle_ajax( array $config ) {
		$nonce_field = $config['nonce_field'];
		if ( empty( $_POST[ $nonce_field ] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ $nonce_field ] ) ), $config['nonce_action'] ) ) {
			wp_send_json_error( __( 'Security check failed.', 'shopwell' ), 403 );
		}

		$prefix = $config['filter_prefix'];
		$id     = $config['id'];

		$values = array();
		foreach ( $config['fields'] as $name => $def ) {
			$def    = wp_parse_args( is_array( $def ) ? $def : array(), array(
				'type'     => 'text',
				'required' => false,
			) );
			$raw    = isset( $_POST[ $name ] ) ? wp_unslash( $_POST[ $name ] ) : null;
			$values[ $name ] = self::sanitize_field( $def['type'], $raw );

			if ( ! empty( $def['required'] ) && self::is_empty_value( $values[ $name ], $def['type'] ) ) {
				wp_send_json_error( apply_filters( "{$prefix}_validation_error_{$id}", __( 'Please fill in all required fields.', 'shopwell' ), $name, $config ) );
			}
		}

		$values = apply_filters( "{$prefix}_sanitized_values_{$id}", $values, $config );

		if ( ! empty( $config['duplicate_check']['field'] ) ) {
			$df   = $config['duplicate_check']['field'];
			$dval = isset( $values[ $df ] ) ? $values[ $df ] : '';
			$msg  = isset( $config['duplicate_check']['message'] ) ? $config['duplicate_check']['message'] : __( 'Already registered.', 'shopwell' );
			if ( $dval !== '' && $dval !== null && self::find_by_meta( $config['post_type'], $config['meta_prefix'] . $df, $dval ) ) {
				wp_send_json_error( $msg );
			}
		}

		$title = call_user_func( $config['title_builder'], $values );
		$title = apply_filters( "{$prefix}_entry_title_{$id}", $title, $values, $config );
		$title = $title !== '' ? $title : __( 'Form entry', 'shopwell' );

		$post_id = wp_insert_post(
			array(
				'post_type'   => $config['post_type'],
				'post_status' => 'private',
				'post_title'  => $title,
			),
			true
		);

		if ( is_wp_error( $post_id ) ) {
			wp_send_json_error( __( 'Could not save entry.', 'shopwell' ) );
		}

		foreach ( $values as $key => $val ) {
			update_post_meta( (int) $post_id, $config['meta_prefix'] . $key, $val );
		}

		$extra = apply_filters( "{$prefix}_extra_meta_{$id}", array(), $values, (int) $post_id, $config );
		if ( is_array( $extra ) ) {
			foreach ( $extra as $ek => $ev ) {
				update_post_meta( (int) $post_id, $config['meta_prefix'] . sanitize_key( (string) $ek ), $ev );
			}
		}

		do_action( "{$prefix}_after_save_{$id}", (int) $post_id, $values, $config );

		wp_send_json_success( $config['success_message'] );
	}

	/**
	 * @param string       $post_type .
	 * @param string       $meta_key  Full meta key.
	 * @param scalar|null  $value     .
	 * @return bool
	 */
	private static function find_by_meta( $post_type, $meta_key, $value ) {
		$q = new WP_Query(
			array(
				'post_type'      => $post_type,
				'post_status'    => 'any',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'meta_key'       => $meta_key,
				'meta_value'     => $value,
				'no_found_rows'  => true,
			)
		);
		return $q->have_posts();
	}

	/**
	 * @param string $type .
	 * @param mixed  $raw  .
	 * @return mixed
	 */
	private static function sanitize_field( $type, $raw ) {
		switch ( $type ) {
			case 'email':
				return is_string( $raw ) ? sanitize_email( $raw ) : '';
			case 'textarea':
				return is_string( $raw ) ? sanitize_textarea_field( $raw ) : '';
			case 'url':
				return is_string( $raw ) ? esc_url_raw( $raw ) : '';
			case 'checkbox':
				return ( $raw === 'on' || $raw === '1' || $raw === 1 || $raw === true || $raw === 'true' ) ? '1' : '';
			case 'text':
			default:
				return is_string( $raw ) ? sanitize_text_field( $raw ) : ( is_scalar( $raw ) ? (string) $raw : '' );
		}
	}

	/**
	 * @param mixed  $value .
	 * @param string $type  .
	 * @return bool
	 */
	private static function is_empty_value( $value, $type ) {
		if ( $type === 'checkbox' ) {
			return $value !== '1';
		}
		return $value === null || $value === '' || $value === array();
	}
}
