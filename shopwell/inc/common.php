<?php

/**
 * Common functions used in backend and frontend of the theme.
 *
 * @package     Shopwell
 * @author      Peregrine Themes
 * @since       1.0.0
 */

/**
 * Insert dynamic text into content.
 *
 * @since 1.0.0
 * @param string $content Text to be modified.
 * @return string Modified text.
 */
function shopwell_dynamic_strings( $content ) {
	$content = str_replace( '{{the_year}}', date_i18n( 'Y' ), $content );
	$content = str_replace( '{{the_date}}', date_i18n( get_option( 'date_format' ) ), $content );
	$content = str_replace( '{{site_title}}', get_bloginfo( 'name' ), $content );
	$content = str_replace( '{{theme_link}}', '<a href="https://wordpress.org/themes/shopwell/" class="imprint" target="_blank" rel="noopener noreferrer">Shopwell WordPress Theme</a>', $content );

	if ( false !== strpos( $content, '{{current_user}}' ) ) {
		$current_user = wp_get_current_user();
		$content      = str_replace( '{{current_user}}', apply_filters( 'shopwell_logged_out_user_name', $current_user->display_name ), $content );
	}

	return apply_filters( 'shopwell_parse_dynamic_strings', $content );
}

// Register the filter for dynamic strings
add_filter( 'shopwell_dynamic_strings', 'shopwell_dynamic_strings' );

/**
 * Convert rgb(a) color string to hex string.
 *
 * @since  1.0.0
 * @param  string $color rgb(a) color code.
 * @return string|false color in HEX format or false on failure.
 */
function shopwell_rgba2hex( $color ) {
	preg_match( '/rgba?\(\s?([0-9]{1,3}),\s?([0-9]{1,3}),\s?([0-9]{1,3})/i', $color, $matches );

	if ( ! is_array( $matches ) || count( $matches ) < 4 ) {
		return false; // Explicitly return false on failure
	}

	$hex = '';
	for ( $i = 1; $i <= 3; $i++ ) {
		$x    = dechex( (int) $matches[ $i ] );
		$hex .= ( 1 === strlen( $x ) ) ? '0' . $x : $x;
	}

	return '#' . $hex;
}

/**
 * Lightens/darkens a given color (in hex format).
 *
 * @since 1.0.0
 * @param string $hexcolor Color as hexadecimal (with or without hash).
 * @param float  $percent Decimal ( 0.2 = lighten by 20%, -0.4 = darken by 40% ).
 * @return string Lightened/Darkened color as hexadecimal (with hash).
 */
function shopwell_luminance( $hexcolor, $percent ) {
	if ( empty( $hexcolor ) ) {
		return; // Explicit handling for empty input
	}

	// Check if color is in RGB format and convert to HEX.
	if ( false !== strpos( $hexcolor, 'rgb' ) ) {
		$hexcolor = shopwell_rgba2hex( $hexcolor );
	}

	// Validate hex color
	if ( strlen( $hexcolor ) < 6 ) {
		return false; // Handle invalid hex format
	}

	$hexcolor = array_map( 'hexdec', str_split( str_pad( str_replace( '#', '', $hexcolor ), 6, '0' ), 2 ) );

	foreach ( $hexcolor as $i => $color ) {
		$from           = $percent < 0 ? 0 : $color;
		$to             = $percent < 0 ? $color : 255;
		$pvalue         = ceil( ( $to - $from ) * $percent );
		$hexcolor[ $i ] = str_pad( dechex( $color + $pvalue ), 2, '0', STR_PAD_LEFT );
	}

	return '#' . implode( '', $hexcolor ); // Added empty string to implode
}

/**
 * Determine whether a hex color is light.
 *
 * @param mixed $color Color.
 * @return bool True if a light color.
 */
function shopwell_is_light_color( $color ) {
	// If $color is an array, check each color and return true if any is light.
	if ( is_array( $color ) ) {
		foreach ( $color as $value ) {
			if ( ! empty( $value ) ) {
				$hexColor = false !== strpos( $value, 'rgb' ) ? shopwell_rgba2hex( $value ) : $value;
				if ( shopwell_is_light_color( $hexColor ) ) {
					return true; // At least one color is light
				}
			}
		}
		return false; // No light colors found
	}

	// If $color is a single string, process it as before
	if ( false !== strpos( $color, 'rgb' ) ) {
		$color = shopwell_rgba2hex( $color );
	}

	$hex = str_replace( '#', '', $color );
	if ( strlen( $hex ) < 6 ) {
		return false; // Handle invalid hex
	}

	$c_r        = hexdec( substr( $hex, 0, 2 ) );
	$c_g        = hexdec( substr( $hex, 2, 2 ) );
	$c_b        = hexdec( substr( $hex, 4, 2 ) );
	$brightness = ( ( $c_r * 299 ) + ( $c_g * 587 ) + ( $c_b * 114 ) ) / 1000;

	return $brightness > 155;
}

/**
 * Detect if we should use a light or dark color on a background color.
 *
 * @param mixed  $color Color.
 * @param string $dark  Darkest reference. Defaults to '#000000'.
 * @param string $light Lightest reference. Defaults to '#FFFFFF'.
 * @return string
 */
function shopwell_light_or_dark( $color, $dark = '#000000', $light = '#FFFFFF' ) {
	return shopwell_is_light_color( $color ) ? $dark : $light;
}

/**
 * Insert into array before specified key.
 *
 * @since 1.0.0
 * @param array  $array     Array to be modified.
 * @param array  $pairs     Array of key => value pairs to insert.
 * @param mixed  $key       Key of $array to insert before or after.
 * @param string $position  Before or after $key.
 * @return array $result    Array with inserted $new value.
 */
function shopwell_array_insert( $array, $pairs, $key, $position = 'after' ) {
	$key_pos = array_search( $key, array_keys( $array ), true );

	if ( 'after' === $position ) {
		++$key_pos;
	}

	if ( false !== $key_pos ) {
		$result = array_slice( $array, 0, $key_pos );
		$result = array_merge( $result, $pairs );
		$result = array_merge( $result, array_slice( $array, $key_pos ) );
	} else {
		$result = array_merge( $array, $pairs );
	}

	return $result;
}

if ( ! function_exists( 'shopwell_get_allowed_html_tags' ) ) {
	/**
	 * Retrieve allowed HTML tags with enhanced security and flexibility.
	 *
	 * This function provides a secure and extensible way to define allowed HTML tags
	 * for different contexts in the WordPress theme. It helps prevent XSS attacks
	 * by strictly controlling which HTML tags and attributes are permitted.
	 *
	 * @since 1.0.0
	 * @param string $type Predefined HTML tags group name.
	 * @return array Sanitized and allowed HTML tags with their permitted attributes.
	 */
	function shopwell_get_allowed_html_tags( $type = 'post' ) {
		// Common attributes that can be applied to multiple tags
		$common_attributes = array(
			'class' => true,
			'id'    => true,
			'style' => true,
		);

		// Basic text-level semantic tags
		$text_tags = array(
			'strong' => $common_attributes,
			'em'     => $common_attributes,
			'b'      => $common_attributes,
			'i'      => $common_attributes,
			'br'     => array(),
			'span'   => $common_attributes,
		);

		// Link and media tags
		$link_media_tags = array(
			'a'   => array_merge(
				$common_attributes,
				array(
					'href'     => true,
					'rel'      => true,
					'target'   => true,
					'title'    => true,
					'download' => true,
					'role'     => true,
				),
				array(
					// Nested tags allowed inside anchor
					'strong' => $common_attributes,
					'em'     => $common_attributes,
					'span'   => array_merge(
						$common_attributes,
						array(
							// Support for SVG icon spans
							'svg'  => array(
								'class'       => true,
								'xmlns'       => true,
								'width'       => true,
								'height'      => true,
								'viewbox'     => true,
								'aria-hidden' => true,
								'role'        => true,
								'focusable'   => true,
								'fill'        => true,
							),
							'path' => array(
								'fill'         => true,
								'fill-rule'    => true,
								'd'            => true,
								'transform'    => true,
								'stroke'       => true,
								'stroke-width' => true,
							),
						)
					),
					'i'      => $common_attributes,
					'svg'    => array(
						'class'       => true,
						'xmlns'       => true,
						'width'       => true,
						'height'      => true,
						'viewbox'     => true,
						'aria-hidden' => true,
						'role'        => true,
						'focusable'   => true,
						'fill'        => true,
					),
					'path'   => array(
						'fill'         => true,
						'fill-rule'    => true,
						'd'            => true,
						'transform'    => true,
						'stroke'       => true,
						'stroke-width' => true,
					),
				)
			),
			'img' => array_merge(
				$common_attributes,
				array(
					'src'     => true,
					'alt'     => true,
					'width'   => true,
					'height'  => true,
					'loading' => true,
					'srcset'  => true,
					'sizes'   => true,
				)
			),
		);

		// SVG and vector graphics tags
		$svg_tags = array(
			'svg'     => array(
				'class'       => true,
				'xmlns'       => true,
				'width'       => true,
				'height'      => true,
				'viewbox'     => true,
				'aria-hidden' => true,
				'role'        => true,
				'focusable'   => true,
			),
			'path'    => array(
				'fill'         => true,
				'fill-rule'    => true,
				'd'            => true,
				'transform'    => true,
				'stroke'       => true,
				'stroke-width' => true,
			),
			'polygon' => array(
				'fill'      => true,
				'fill-rule' => true,
				'points'    => true,
				'transform' => true,
				'focusable' => true,
			),
			'title'   => array(),
		);

		// Embedded content tags
		$embed_tags = array(
			'iframe' => array(
				'title'           => true,
				'src'             => true,
				'width'           => true,
				'height'          => true,
				'loading'         => true,
				'frameborder'     => true,
				'allowfullscreen' => true,
				'sandbox'         => true,
			),
			'time'   => array(
				'class'    => true,
				'datetime' => true,
			),
		);

		// Button and interactive tags
		$button_tags = array(
			'button' => array(
				'type'     => true,
				'class'    => true,
				'disabled' => true,
				'id'       => true,
			),
		);

		// Semantic HTML5 tags
		$semantic_tags = array(
			'article' => $common_attributes,
			'section' => $common_attributes,
			'nav'     => $common_attributes,
		);

		// Determine tags based on type
		switch ( $type ) {
			case 'basic':
				$tags = array_merge(
					$text_tags,
					$link_media_tags,
					$svg_tags,
					array( 'iframe' => $embed_tags['iframe'] )
				);
				break;

			case 'button':
				$tags = array_merge(
					$text_tags,
					$button_tags
				);
				break;

			case 'post':
				$tags = wp_kses_allowed_html( 'post' );
				$tags = array_merge(
					$tags,
					$semantic_tags,
					$svg_tags,
					$embed_tags,
					array( 'data' => array( 'value' => true ) )
				);
				break;

			default:
				$tags = array_merge(
					$text_tags,
					$link_media_tags
				);
				break;
		}

		/**
		 * Filter the allowed HTML tags and their attributes.
		 *
		 * This filter allows theme and plugin developers to modify the
		 * allowed HTML tags dynamically based on specific requirements.
		 *
		 * @param array  $tags Allowed HTML tags and attributes.
		 * @param string $type Context or group of HTML tags.
		 */
		return apply_filters( 'shopwell_allowed_html_tags', $tags, $type );
	}
}

/**
 * The function which returns the one Shopwell_Plugin_Utilities instance.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $shopwell_plugin_utilities = shopwell_plugin_utilities(); ?>
 *
 * @since 1.0.0
 * @return object
 */
function shopwell_plugin_utilities() {
	return \Shopwell\Admin\Utilities\Shopwell_Plugin_Utilities::instance();
}

/**
 * Return Dynamic_CSS class instance.
 *
 * @since 1.0.0
 * @return Object
 */
function shopwell_dynamic_styles() {
	return \Shopwell\Dynamic_CSS::instance();
}

// Fix the SVG rendering issue in the media library
function shopwell_custom_svg_sanitization( $file ) {
	if ( isset( $file['name'] ) && strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) ) === 'svg' ) {
		// Sanitize SVG file
		$file['type'] = 'image/svg+xml';
	}
	return $file;
}
add_filter( 'wp_handle_upload_prefilter', 'shopwell_custom_svg_sanitization' );
