<?php
/**
 * Concurs page → Reusable_Ajax_Form_Entries (CPT concurs_entry, action submit_concurs).
 *
 * @package Shopwell
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once get_template_directory() . '/inc/concurs-seo.php';
require_once get_template_directory() . '/inc/reusable-ajax-form-entries/class-reusable-ajax-form-entries.php';

add_filter(
	'rafe_sanitized_values_concurs',
	static function ( array $values, array $_unused_config ): array {
		if ( ! empty( $values['email'] ) && ! is_email( $values['email'] ) ) {
			wp_send_json_error( __( 'Adresa de email nu este validă.', 'shopwell' ) );
		}
		$values['shared'] = ( isset( $values['shared'] ) && ( $values['shared'] === '1' || $values['shared'] === 1 ) ) ? '1' : '0';
		return $values;
	},
	5,
	2
);

add_filter(
	'rafe_extra_meta_concurs',
	static function ( array $extra, array $values, int $post_id, array $config ): array {
		unset( $post_id, $config );
		$total = 1;
		if ( ! empty( $values['motiv'] ) ) {
			++$total;
		}
		if ( ! empty( $values['follow_instagram'] ) && $values['follow_instagram'] === '1' ) {
			$total += 2;
		}
		if ( ! empty( $values['follow_facebook'] ) && $values['follow_facebook'] === '1' ) {
			$total += 2;
		}
		if ( ! empty( $values['share_story'] ) && $values['share_story'] === '1' ) {
			$total += 3;
		}
		if ( ! empty( $values['tag_friends'] ) && $values['tag_friends'] === '1' ) {
			++$total;
		}
		if ( ! empty( $values['shared'] ) && $values['shared'] === '1' ) {
			$total += 2;
		}
		$extra['total_chances'] = $total;
		return $extra;
	},
	10,
	4
);

Reusable_Ajax_Form_Entries::register(
	array(
		'id'                => 'concurs',
		'post_type'         => 'concurs_entry',
		'ajax_action'       => 'submit_concurs',
		'nonce_action'      => 'concurs_form_nonce',
		'nonce_field'       => '_wpnonce',
		'meta_prefix'       => '_concurs_',
		'success_message'   => __( 'Înscrierea a fost înregistrată.', 'shopwell' ),
		'duplicate_check'   => array(
			'field'   => 'email',
			'message' => __( 'Există deja o participare cu acest email.', 'shopwell' ),
		),
		'title_builder'     => static function ( array $v ): string {
			$nume  = isset( $v['nume'] ) ? (string) $v['nume'] : '';
			$email = isset( $v['email'] ) ? (string) $v['email'] : '';
			$base  = trim( $nume ) !== '' ? $nume : __( 'Participant', 'shopwell' );
			if ( $email !== '' ) {
				$base .= ' — ' . $email;
			}
			return function_exists( 'mb_substr' ) ? mb_substr( $base, 0, 200 ) : substr( $base, 0, 200 );
		},
		'post_type_args'    => array(
			'labels'        => array(
				'name'          => __( 'Înscrieri concurs', 'shopwell' ),
				'singular_name' => __( 'Înscriere concurs', 'shopwell' ),
				'menu_name'     => __( 'Concurs', 'shopwell' ),
				'add_new'       => __( 'Adaugă', 'shopwell' ),
				'add_new_item'  => __( 'Adaugă înscriere', 'shopwell' ),
				'edit_item'     => __( 'Editează înscrierea', 'shopwell' ),
				'view_item'     => __( 'Vezi înscrierea', 'shopwell' ),
				'search_items'  => __( 'Caută înscrieri', 'shopwell' ),
				'not_found'     => __( 'Nu există înscrieri.', 'shopwell' ),
			),
			'menu_icon'     => 'dashicons-awards',
			'menu_position' => 26,
		),
		'admin_columns'     => array(
			'email'            => __( 'Email', 'shopwell' ),
			'telefon'          => __( 'Telefon', 'shopwell' ),
			'oras'             => __( 'Oraș', 'shopwell' ),
			'total_chances'    => __( 'Șanse totale', 'shopwell' ),
			'follow_instagram' => __( 'Instagram', 'shopwell' ),
			'follow_facebook'  => __( 'Facebook', 'shopwell' ),
		),
		'fields'            => array(
			'nume'               => array( 'type' => 'text', 'required' => true ),
			'email'              => array( 'type' => 'email', 'required' => true ),
			'telefon'            => array( 'type' => 'text', 'required' => true ),
			'oras'               => array( 'type' => 'text', 'required' => true ),
			'motiv'              => array( 'type' => 'textarea', 'required' => false ),
			'terms'              => array( 'type' => 'checkbox', 'required' => true ),
			'privacy'            => array( 'type' => 'checkbox', 'required' => true ),
			'newsletter'         => array( 'type' => 'checkbox', 'required' => false ),
			'follow_instagram'   => array( 'type' => 'checkbox', 'required' => false ),
			'follow_facebook'    => array( 'type' => 'checkbox', 'required' => false ),
			'share_story'        => array( 'type' => 'checkbox', 'required' => false ),
			'tag_friends'        => array( 'type' => 'checkbox', 'required' => false ),
			'instagram_username' => array( 'type' => 'text', 'required' => false ),
			'facebook_profile'   => array( 'type' => 'text', 'required' => false ),
			'story_screenshot'   => array( 'type' => 'text', 'required' => false ),
			'comment_link'       => array( 'type' => 'text', 'required' => false ),
			'shared'             => array( 'type' => 'text', 'required' => false ),
		),
	)
);
