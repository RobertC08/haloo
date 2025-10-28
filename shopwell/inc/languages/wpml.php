<?php
/**
 * WPML compatibility functions
 *
 * @package Shopwell
 */

namespace Shopwell\Languages;

use Shopwell\Helper;

class WPML {
	const CAMPAIGNS_DOMAIN    = 'Campaign Bar';
	const CAMPAIGN_PREFIX     = 'campaign_';
	const SEARCH_LINKS_DOMAIN = 'Search Links';
	const SEARCH_LINK_PREFIX  = 'search_link_';

	/**
	 * The single instance of the class
	 *
	 * @var WPML
	 */
	protected static $instance = null;

	/**
	 * Main instance
	 *
	 * @return WPML
	 */
	public static function instance() {
		if ( null == self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'customize_save_after', array( $this, 'register_strings' ) );

		add_filter( 'shopwell_campaign_item_args', array( $this, 'translate_campaign_item_args' ), 10, 2 );
		add_filter( 'shopwell_search_quicklinks', array( $this, 'translate_search_quicklinks' ) );

		add_filter( 'wpml_pb_shortcode_encode', array( $this, 'shortcode_encode_urlencoded_json' ), 10, 3 );
		add_filter( 'wpml_pb_shortcode_decode', array( $this, 'shortcode_decode_urlencoded_json' ), 10, 3 );
	}

	/**
	 * Register special theme strings for translation
	 *
	 * @return void
	 */
	public function register_strings() {
		$this->register_campaign_strings();
		$this->register_search_link_strings();
	}

	/**
	 * Register campaign strings for translation
	 */
	public function register_campaign_strings() {
		$campaigns = array_filter( (array) Helper::get_option( 'campaign_items' ) );

		if ( empty( $campaigns ) || ! $campaigns ) {
			return;
		}

		foreach ( $campaigns as $id => $campaign ) {
			$count = $id + 1;

			do_action( 'wpml_register_single_string', self::CAMPAIGNS_DOMAIN, self::CAMPAIGN_PREFIX . $count . '_text', $campaign['text'] );
		}
	}

	/**
	 * Register header search links for translation
	 */
	public function register_search_link_strings() {
		$links = Helper::get_option( 'header_search_links' );

		// Check if $links is an array before proceeding
		if ( ! is_array( $links ) || empty( $links ) ) {
			return;
		}

		foreach ( $links as $id => $link ) {
			$count = $id + 1;

			do_action( 'wpml_register_single_string', self::SEARCH_LINKS_DOMAIN, self::SEARCH_LINK_PREFIX . $count . '_text', $link['text'] );
			do_action( 'wpml_register_single_string', self::SEARCH_LINKS_DOMAIN, self::SEARCH_LINK_PREFIX . $count . '_url', $link['url'] );
		}
	}

	/**
	 * Apply the WPML translation for campaign items
	 *
	 * @param array $args
	 * @param int   $id
	 *
	 * @return array
	 */
	public function translate_campaign_item_args( $args, $id ) {
		if ( ! $args ) {
			return;
		}
		$count = $id + 1;

		$args['text'] = apply_filters( 'wpml_translate_single_string', $args['text'], self::CAMPAIGNS_DOMAIN, self::CAMPAIGN_PREFIX . $count . '_text' );

		return $args;
	}

	/**
	 * Apply the WPML translation for search quick links
	 *
	 * @param array $links
	 *
	 * @return array
	 */
	public function translate_search_quicklinks( $links ) {
		if ( empty( $links ) ) {
			return $links;
		}

		foreach ( $links as $id => $link ) {
			$count = $id + 1;

			$links[ $id ]['text'] = apply_filters( 'wpml_translate_single_string', $link['text'], self::SEARCH_LINKS_DOMAIN, self::SEARCH_LINK_PREFIX . $count . '_text' );
			$links[ $id ]['url']  = apply_filters( 'wpml_translate_single_string', $link['url'], self::SEARCH_LINKS_DOMAIN, self::SEARCH_LINK_PREFIX . $count . '_url' );
		}

		return $links;
	}

	/**
	 * Encode the param_groups type of js_composer
	 *
	 * @param string $string
	 * @param string $encoding
	 * @param array  $original_string
	 * @return string
	 */
	public function shortcode_encode_urlencoded_json( $string, $encoding, $original_string ) {
		if ( 'urlencoded_json' === $encoding ) {
			$output = array();

			foreach ( $original_string as $combined_key => $value ) {
				$parts = explode( '_', $combined_key );
				$i     = array_pop( $parts );
				$key   = implode( '_', $parts );

				$output[ $i ][ $key ] = $value;
			}

			$string = urlencode( json_encode( $output ) );
		}

		return $string;
	}

	/**
	 * Decode urleconded string of param_groups type of js_composer
	 *
	 * @param string $string
	 * @param string $encoding
	 * @param string $original_string
	 * @return string
	 */
	public function shortcode_decode_urlencoded_json( $string, $encoding, $original_string ) {
		if ( 'urlencoded_json' === $encoding ) {
			$rows   = json_decode( urldecode( $original_string ), true );
			$string = array();
			$atts   = array( 'title', 'label', 'value' );

			foreach ( (array) $rows as $i => $row ) {
				foreach ( $row as $key => $value ) {
					if ( in_array( $key, $atts ) ) {
						$string[ $key . '_' . $i ] = array(
							'value'     => $value,
							'translate' => true,
						);
					} else {
						$string[ $key . '_' . $i ] = array(
							'value'     => $value,
							'translate' => false,
						);
					}
				}
			}
		}

		return $string;
	}
}
