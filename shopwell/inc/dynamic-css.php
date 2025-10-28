<?php
/**
 * Style functions and definitions.
 *
 * @package Shopwell
 */

namespace Shopwell;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Style initial
 *
 * @since 1.0.0
 */
class Dynamic_CSS {
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
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) {
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
		// Remove Customizer Custom CSS from wp_head, we will include it in our dynamic css.
		if ( ! is_customize_preview() ) {
			remove_action( 'wp_head', 'wp_custom_css_cb', 101 );
		}
		add_action( 'shopwell_after_enqueue_style', array( $this, 'print_dynamic_style' ) );
	}

	/**
	 * Prints inline dynamic styles while customizing website.
	 *
	 * @since 1.0.0
	 */
	public function print_dynamic_style() {
		if ( is_customize_preview() ) {
			$dynamic_css = $this->get_css();
		} else {
			$dynamic_css = $this->get_css( true );
		}
		wp_add_inline_style( 'shopwell', $dynamic_css );
	}

	/**
	 * Get get style data
	 *
	 * @since  1.0.0
	 *
	 * @return string
	 */
	public function get_css( $custom_css = false ) {
		// Refresh options.
		\Shopwell\Options::instance()->refresh();

		// Delete google fonts enqueue transients.
		delete_transient( 'shopwell_google_fonts_enqueue' );

		$css  = $this->primary_color_static_css(); // no
		$css .= $this->typography_css(); // no

		$css .= $this->header_static_css();
		$css .= $this->header_color_static_css(); // no
		$css .= $this->header_mobile_static_css();
		$css .= $this->help_center_css();
		$css .= $this->page_static_css();
		$css .= $this->footer_mobile_static_css();
		$css .= $this->topbar_static_css();
		$css .= $this->campaign_bar_static_css();
		$css .= $this->footer_static_css();
		$css .= $this->mobile_static_css();

		// Allow CSS to be filtered.
		$css = apply_filters( 'shopwell_dynamic_styles', $css );

		// Add user custom CSS.
		if ( $custom_css || ! is_customize_preview() ) {
			$css .= wp_get_custom_css();
		}

		// Minify the CSS code.
		$css = $this->minify( $css );

		return $css;
	}

	/**
	 * Get Color Scheme style data
	 *
	 * @since  1.0.0
	 *
	 * @return string
	 */
	protected function primary_color_static_css() {
		$color_style        = '';
		$primary_text_color = Helper::get_option( 'primary_text_color' );
		if ( $primary_text_color != 'light' ) {
			$custom_color = $primary_text_color == 'custom' ? Helper::get_option( 'primary_text_custom_color' ) : '#1d2128';
			$color_style .= '--shopwell-color__primary--light:' . $custom_color;
		}

		$primary_color = Helper::get_option( 'primary_custom_color' );
		if ( empty( $primary_color ) || $primary_color == '#0068c8' ) {
			return 'body{' . $color_style . '}';
		}
		$color_hsl = $this->hex_to_hsl( $primary_color );

		if ( $color_hsl && count( $color_hsl ) > 2 ) {
			$color_dark   = $this->color_hsl( $color_hsl[0], $color_hsl[1] - 3, $color_hsl[2] - 9 );
			$color_darken = $this->color_hsl( $color_hsl[0], $color_hsl[1] - 9, $color_hsl[2] - 18 );
			$color_style .= ';--shopwell-color__primary:' . $primary_color . ';--shopwell-color__primary--dark:' . $color_dark . ';--shopwell-color__primary--darker: ' . $color_darken;
		}

		$color_gray    = $this->hex_to_rgba( $primary_color, 0.12 );
		$color_grayer  = $this->hex_to_rgba( $primary_color, 0.24 );
		$color_grayest = $this->hex_to_rgba( $primary_color, 0.48 );
		$color_style  .= ';--shopwell-color__primary--gray:' . $color_gray . ';--shopwell-color__primary--grayer:' . $color_grayer . ';--shopwell-color__primary--grayest: ' . $color_grayest;

		if ( $color_style ) {
			$color_style = 'body{' . $color_style . '}';
		}

		$color_boxshadow = $this->hex_to_rgba( $primary_color, 0.4 );

		if ( $color_boxshadow ) {
			$color_style .= '.shopwell-button--raised, .shopwell-skin--raised{--shopwell-color__primary--box-shadow:' . $color_boxshadow . '}';
		}

		return $color_style;
	}

	/**
	 * Get Color Scheme Light
	 *
	 * @since  1.0.0
	 *
	 * $color: color
	 *
	 * @return boolean
	 */
	protected function color_light( $hex ) {
		// Remove the "#" symbol from the beginning of the color.
		$hex = ltrim( $hex, '#' );

		// Make sure there are 6 digits for the below calculations.
		if ( 3 === strlen( $hex ) ) {
			$hex = substr( $hex, 0, 1 ) . substr( $hex, 0, 1 ) . substr( $hex, 1, 1 ) . substr( $hex, 1, 1 ) . substr( $hex, 2, 1 ) . substr( $hex, 2, 1 );
		}

		// Get red, green, blue.
		$red   = hexdec( substr( $hex, 0, 2 ) );
		$green = hexdec( substr( $hex, 2, 2 ) );
		$blue  = hexdec( substr( $hex, 4, 2 ) );

		// Calculate the luminance.
		$lum = ( 0.2126 * $red ) + ( 0.7152 * $green ) + ( 0.0722 * $blue );
		return (int) round( $lum ) > 127 ? true : false;
	}

	/**
	 * Get Color Scheme style data
	 *
	 * @since  1.0.0
	 *
	 * $color_h: color hue
	 * $color_s: color saturation
	 * $color_l: color lightness
	 * $color_l_max: max of color lightness
	 * $color_l_min: if color lightness is than 90%, set color lightness again
	 *
	 * @return string
	 */
	protected function color_hsl( $color_h, $color_s, $color_l, $color_l_max = 0, $color_l_min = 0 ) {
		if ( $color_l_max && $color_l_min ) {
			$color_l = $color_l > $color_l_max ? $color_l_min : $color_l;
		}
		return 'hsl(' . $color_h . ', ' . $color_s . '%,' . $color_l . '%' . ')';
	}

	/**
	 * Convert hex to hsl
	 *
	 * @since  1.0.0
	 *
	 * @return array
	 */
	function hex_to_hsl( $hex ) {
		$hex   = str_replace( '#', '', $hex );
		$red   = hexdec( substr( $hex, 0, 2 ) ) / 255;
		$green = hexdec( substr( $hex, 2, 2 ) ) / 255;
		$blue  = hexdec( substr( $hex, 4, 2 ) ) / 255;

		$cmin  = min( $red, $green, $blue );
		$cmax  = max( $red, $green, $blue );
		$delta = $cmax - $cmin;

		if ( $delta == 0 ) {
			$hue = 0;
		} elseif ( $cmax === $red ) {
			$hue = ( ( $green - $blue ) / $delta );
		} elseif ( $cmax === $green ) {
			$hue = ( $blue - $red ) / $delta + 2;
		} else {
			$hue = ( $red - $green ) / $delta + 4;
		}

		$hue = round( $hue * 60 );
		if ( $hue < 0 ) {
			$hue += 360;
		}

		$lightness  = ( ( $cmax + $cmin ) / 2 );
		$saturation = $delta === 0 ? 0 : ( $delta / ( 1 - abs( 2 * $lightness - 1 ) ) );
		if ( $saturation < 0 ) {
			$saturation += 1;
		}

		$lightness  = round( $lightness * 100 );
		$saturation = round( $saturation * 100 );

		return array( $hue, $saturation, $lightness );
	}

	/**
	 * Convert hex to rgba
	 *
	 * @since  1.0.0
	 *
	 * @return string
	 */
	protected function hex_to_rgba( $color, $opacity = false ) {
		if ( isset( $color[0] ) && $color[0] == '#' ) {
			$color = substr( $color, 1 );
		}

		if ( strlen( $color ) == 6 ) {
				$hex = array( $color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5] );
		} elseif ( strlen( $color ) == 3 ) {
				$hex = array( $color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2] );
		}

		if ( empty( $hex ) || ! is_array( $hex ) ) {
			return;
		}

		$rgb = array_map( 'hexdec', $hex );

		if ( $opacity ) {
			if ( abs( $opacity ) > 1 ) {
				$opacity = 1.0;
			}

			$output = 'rgba(' . implode( ',', $rgb ) . ',' . $opacity . ')';
		} else {
			$output = 'rgb(' . implode( ',', $rgb ) . ')';
		}

		return $output;
	}

	/**
	 * Get typography CSS base on settings
	 */
	protected function typography_css() {
		$settings = array(
			'typo_body' => 'body, .block-editor .editor-styles-wrapper',
			'typo_h1'   => 'h1, .h1',
			'typo_h2'   => 'h2, .h2',
			'typo_h3'   => 'h3, .h3',
			'typo_h4'   => 'h4, .h4',
			'typo_h5'   => 'h5, .h5',
			'typo_h6'   => 'h6, .h6',
			'logo_font' => '.site-header .header-logo',
		);

		$settings = apply_filters( 'shopwell_dynamic_typography_settings', $settings );
		return $this->get_typography_css( $settings );
	}

	/**
	 * Prints spacing field CSS based on passed params.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $css_selector CSS selector.
	 * @param  string $css_property CSS property, such as 'margin', 'padding' or 'border'.
	 * @param  string $setting_id The ID of the customizer setting containing all information about the setting.
	 * @param  bool   $responsive Has responsive values.
	 * @return string  Generated CSS.
	 */
	public function get_spacing_field_css( $css_selector, $css_property, $setting_id, $responsive = true ) {

		// Get the saved setting.
		$setting = \Shopwell\Helper::get_option( $setting_id );

		// If setting doesn't exist, return.
		if ( ! is_array( $setting ) ) {
			return;
		}

		// Get the unit. Defaults to px.
		$unit = 'px';

		if ( isset( $setting['unit'] ) ) {
			if ( $setting['unit'] ) {
				$unit = $setting['unit'];
			}

			unset( $setting['unit'] );
		}

		// CSS buffer.
		$css_buffer = '';

		// Loop through options.
		foreach ( $setting as $key => $value ) {

			// Check if responsive options are available.
			if ( is_array( $value ) ) {

				if ( 'desktop' === $key ) {
					$mq_open  = '';
					$mq_close = '';
				} elseif ( 'tablet' === $key ) {
					$mq_open  = '@media only screen and (max-width: 768px) {';
					$mq_close = '}';
				} elseif ( 'mobile' === $key ) {
					$mq_open  = '@media only screen and (max-width: 480px) {';
					$mq_close = '}';
				} else {
					$mq_open  = '';
					$mq_close = '';
				}

				// Add media query prefix.
				$css_buffer .= $mq_open . $css_selector . '{';

				// Loop through all choices.
				foreach ( $value as $pos => $val ) {

					if ( empty( $val ) ) {
						continue;
					}

					if ( 'border' === $css_property ) {
						$pos .= '-width';
					}

					$css_buffer .= $css_property . '-' . $pos . ': ' . intval( $val ) . $unit . ';';
				}

				$css_buffer .= '}' . $mq_close;
			} else {

				if ( 'border' === $css_property ) {
					$key .= '-width';
				}

				$css_buffer .= $css_property . '-' . $key . ': ' . intval( $value ) . $unit . ';';
			}
		}

		// Check if field is has responsive values.
		if ( ! $responsive ) {
			$css_buffer = $css_selector . '{' . $css_buffer . '}';
		}

		// Finally, return the generated CSS code.
		return $css_buffer;
	}

	/**
	 * Prints design options field CSS based on passed params.
	 *
	 * @since 1.0.0
	 * @param string       $css_selector CSS selector.
	 * @param string|mixed $setting The ID of the customizer setting containing all information about the setting.
	 * @param string       $type Design options field type.
	 * @return string      Generated CSS.
	 */
	public function get_design_options_field_css( $css_selector, $setting, $type ) {

		if ( is_string( $setting ) ) {
			// Get the saved setting.
			$setting = \Shopwell\Helper::get_option( $setting );
		}

		// Setting has to be array.
		if ( ! is_array( $setting ) || empty( $setting ) ) {
			return;
		}

		// CSS buffer.
		$css_buffer = '';

		// Background.
		if ( 'background' === $type ) {

			// Background type.
			$background_type = $setting['background-type'];

			if ( 'color' === $background_type ) {
				if ( isset( $setting['background-color'] ) && ! empty( $setting['background-color'] ) ) {
					$css_buffer .= 'background: ' . shopwell_sanitize_color( $setting['background-color'] ) . ';';
				}
			} elseif ( 'gradient' === $background_type ) {

				$css_buffer .= 'background: ' . shopwell_sanitize_color( $setting['gradient-color-1'] ) . ';';

				if ( 'linear' === $setting['gradient-type'] ) {
					$css_buffer .= '
							background: -webkit-linear-gradient(' . shopwell_sanitize_number( $setting['gradient-linear-angle'] ) . 'deg, ' . shopwell_sanitize_color( $setting['gradient-color-1'] ) . ' ' . shopwell_sanitize_number( $setting['gradient-color-1-location'] ) . '%, ' . shopwell_sanitize_color( $setting['gradient-color-2'] ) . ' ' . shopwell_sanitize_number( $setting['gradient-color-2-location'] ) . '%);
							background: -o-linear-gradient(' . shopwell_sanitize_number( $setting['gradient-linear-angle'] ) . 'deg, ' . shopwell_sanitize_color( $setting['gradient-color-1'] ) . ' ' . shopwell_sanitize_number( $setting['gradient-color-1-location'] ) . '%, ' . shopwell_sanitize_color( $setting['gradient-color-2'] ) . ' ' . shopwell_sanitize_number( $setting['gradient-color-2-location'] ) . '%);
							background: linear-gradient(' . shopwell_sanitize_number( $setting['gradient-linear-angle'] ) . 'deg, ' . shopwell_sanitize_color( $setting['gradient-color-1'] ) . ' ' . shopwell_sanitize_number( $setting['gradient-color-1-location'] ) . '%, ' . shopwell_sanitize_color( $setting['gradient-color-2'] ) . ' ' . shopwell_sanitize_number( $setting['gradient-color-2-location'] ) . '%);

						';
				} elseif ( 'radial' === $setting['gradient-type'] ) {
					$css_buffer .= '
							background: -webkit-radial-gradient(' . sanitize_text_field( $setting['gradient-position'] ) . ', circle, ' . shopwell_sanitize_color( $setting['gradient-color-1'] ) . ' ' . shopwell_sanitize_number( $setting['gradient-color-1-location'] ) . '%, ' . shopwell_sanitize_color( $setting['gradient-color-2'] ) . ' ' . shopwell_sanitize_number( $setting['gradient-color-2-location'] ) . '%);
							background: -o-radial-gradient(' . sanitize_text_field( $setting['gradient-position'] ) . ', circle, ' . shopwell_sanitize_color( $setting['gradient-color-1'] ) . ' ' . shopwell_sanitize_number( $setting['gradient-color-1-location'] ) . '%, ' . shopwell_sanitize_color( $setting['gradient-color-2'] ) . ' ' . shopwell_sanitize_number( $setting['gradient-color-2-location'] ) . '%);
							background: radial-gradient(circle at ' . sanitize_text_field( $setting['gradient-position'] ) . ', ' . shopwell_sanitize_color( $setting['gradient-color-1'] ) . ' ' . shopwell_sanitize_number( $setting['gradient-color-1-location'] ) . '%, ' . shopwell_sanitize_color( $setting['gradient-color-2'] ) . ' ' . shopwell_sanitize_number( $setting['gradient-color-2-location'] ) . '%);
						';
				}
			} elseif ( 'image' === $background_type ) {
				$css_buffer .= '
						position: relative;
						z-index: 0;
						background-image: url(' . esc_url( $setting['background-image'] ) . ');
						background-size: ' . ( isset( $setting['background-size'] ) ? sanitize_text_field( $setting['background-size'] ) : 'auto' ) . ';
						background-attachment: ' . ( isset( $setting['background-attachment'] ) ? sanitize_text_field( $setting['background-attachment'] ) : 'scroll' ) . ';
						background-position: ' . ( isset( $setting['background-position-x'] ) ? shopwell_sanitize_number( $setting['background-position-x'] ) : 0 ) . '% ' . ( isset( $setting['background-position-y'] ) ? shopwell_sanitize_number( $setting['background-position-y'] ) : 0 ) . '%;
						background-repeat: ' . ( isset( $setting['background-repeat'] ) ? sanitize_text_field( $setting['background-repeat'] ) : 'repeat' ) . ';
					';
			}

			$css_buffer = ! empty( $css_buffer ) ? $css_selector . '{' . $css_buffer . '}' : '';

			if ( 'image' === $background_type && isset( $setting['background-color-overlay'] ) && $setting['background-color-overlay'] && isset( $setting['background-image'] ) && $setting['background-image'] ) {
				$css_buffer .= $css_selector . '::before {content: ""; position: absolute; inset: 0; z-index: -1; background-color: ' . shopwell_sanitize_color( $setting['background-color-overlay'] ) . '; }';
			}
		} elseif ( 'color' === $type ) {

			// Text color.
			if ( isset( $setting['text-color'] ) && ! empty( $setting['text-color'] ) ) {
				$css_buffer .= $css_selector . ' { color: ' . shopwell_sanitize_color( $setting['text-color'] ) . '; }';
			}

			// Link Color.
			if ( isset( $setting['link-color'] ) && ! empty( $setting['link-color'] ) ) {
				$css_buffer .= $css_selector . ' a { color: ' . shopwell_sanitize_color( $setting['link-color'] ) . '; }';
			}

			// Link Hover Color.
			if ( isset( $setting['link-hover-color'] ) && ! empty( $setting['link-hover-color'] ) ) {
				$css_buffer .= $css_selector . ' a:focus, ' . $css_selector . ' a:hover { color: ' . shopwell_sanitize_color( $setting['link-hover-color'] ) . '; }';
			}
		} elseif ( 'border' === $type ) {

			// Color.
			if ( isset( $setting['border-color'] ) && ! empty( $setting['border-color'] ) ) {
				$css_buffer .= 'border-color:' . shopwell_sanitize_color( $setting['border-color'] ) . ';';
			}

			// Style.
			if ( isset( $setting['border-style'] ) && ! empty( $setting['border-style'] ) ) {
				$css_buffer .= 'border-style: ' . sanitize_text_field( $setting['border-style'] ) . ';';
			}

			// Width.
			$positions = array( 'top', 'right', 'bottom', 'left' );

			foreach ( $positions as $position ) {
				if ( isset( $setting[ 'border-' . $position . '-width' ] ) && ! empty( $setting[ 'border-' . $position . '-width' ] ) ) {
					$css_buffer .= 'border-' . sanitize_text_field( $position ) . '-width: ' . $setting[ 'border-' . sanitize_text_field( $position ) . '-width' ] . 'px;';
				}
			}

			$css_buffer = ! empty( $css_buffer ) ? $css_selector . '{' . $css_buffer . '}' : '';
		}

		// Finally, return the generated CSS code.
		return $css_buffer;
	}

	/**
	 * Prints typography field CSS based on passed params.
	 *
	 * @since  1.0.0
	 * @param  string       $css_selector CSS selector.
	 * @param  string|mixed $setting The ID of the customizer setting containing all information about the setting.
	 * @return string       Generated CSS.
	 */
	protected function get_typography_css( $settings ) {

		if ( empty( $settings ) ) {
			return '';
		}

		// CSS buffer.
		$css_buffer = '';

		// Properties.
		$properties = array(
			'font-weight',
			'font-style',
			'text-transform',
			'text-decoration',
			'color',
		);

		foreach ( $settings as $setting_id => $css_selector ) {
			if ( ! is_string( $setting_id ) ) {
				continue;
			}

			// Get the saved setting.
			$setting = Helper::get_option( $setting_id );

			// Setting has to be array.
			if ( ! is_array( $setting ) || empty( $setting ) ) {
				continue;
			}

			// Reset CSS buffer for this selector
			$setting_css_buffer = '';

			foreach ( $properties as $property ) {
				if ( isset( $setting[ $property ] ) && $setting[ $property ] !== 'inherit' ) {
					$setting_css_buffer .= $property . ':' . sanitize_text_field( $setting[ $property ] ) . ';';
				}
			}

			// Font family.
			if ( 'inherit' !== $setting['font-family'] ) {
				$font_family         = Helper::fonts()->get_font_family( $setting['font-family'] );
				$setting_css_buffer .= 'font-family: ' . sanitize_text_field( $font_family ) . ';';
			}

			// Letter spacing.
			if ( ! empty( $setting['letter-spacing'] ) ) {
				$setting_css_buffer .= 'letter-spacing:' . shopwell_sanitize_number( $setting['letter-spacing'] ) . sanitize_text_field( $setting['letter-spacing-unit'] ) . ';';
			}

			// Font size.
			if ( ! empty( $setting['font-size-desktop'] ) ) {
				$setting_css_buffer .= 'font-size:' . shopwell_sanitize_number( $setting['font-size-desktop'] ) . sanitize_text_field( $setting['font-size-unit'] ) . ';';
			}

			// Line Height.
			if ( ! empty( $setting['line-height-desktop'] ) ) {
				$setting_css_buffer .= 'line-height:' . shopwell_sanitize_number( $setting['line-height-desktop'] ) . ';';
			}

			$setting_css_buffer = $setting_css_buffer ? $css_selector . '{' . $setting_css_buffer . '}' : '';

			// Responsive options - tablet.
			$tablet = '';

			if ( ! empty( $setting['font-size-tablet'] ) ) {
				$tablet .= 'font-size:' . shopwell_sanitize_number( $setting['font-size-tablet'] ) . sanitize_text_field( $setting['font-size-unit'] ) . ';';
			}

			if ( ! empty( $setting['line-height-tablet'] ) ) {
				$tablet .= 'line-height:' . shopwell_sanitize_number( $setting['line-height-tablet'] ) . ';';
			}

			$tablet = ! empty( $tablet ) ? '@media only screen and (max-width: 768px) {' . $css_selector . '{' . $tablet . '} }' : '';

			$setting_css_buffer .= $tablet;

			// Responsive options - mobile.
			$mobile = '';

			if ( ! empty( $setting['font-size-mobile'] ) ) {
				$mobile .= 'font-size:' . shopwell_sanitize_number( $setting['font-size-mobile'] ) . sanitize_text_field( $setting['font-size-unit'] ) . ';';
			}

			if ( ! empty( $setting['line-height-mobile'] ) ) {
				$mobile .= 'line-height:' . shopwell_sanitize_number( $setting['line-height-mobile'] ) . ';';
			}

			$mobile = ! empty( $mobile ) ? '@media only screen and (max-width: 480px) {' . $css_selector . '{' . $mobile . '} }' : '';

			$setting_css_buffer .= $mobile;

			$css_buffer .= $setting_css_buffer;

			// Enqueue google fonts.
			if ( Helper::fonts()->is_google_font( $setting['font-family'] ) ) {

				$params = array();

				if ( 'inherit' !== $setting['font-weight'] ) {
					$params['weight'] = $setting['font-weight'];
				}

				if ( 'inherit' !== $setting['font-style'] ) {
					$params['style'] = $setting['font-style'];
				}

				if ( ! empty( $setting['font-subsets'] ) ) {
					$params['subsets'] = $setting['font-subsets'];
				}

				Helper::fonts()->enqueue_google_font(
					$setting['font-family'],
					$params
				);
			}
		}

		// Finally, return the generated CSS code.
		return $css_buffer;
	}


	/**
	 * Header static css
	 *
	 * @since  1.0.0
	 *
	 * @return string
	 */
	protected function header_static_css() {
		$static_css     = '';
		$header_present = \Shopwell\Helper::get_option( 'header_present' );
		$header_version = \Shopwell\Helper::get_option( 'header_version' );

		// Header height.
		$height_main = \Shopwell\Helper::get_option( 'header_main_height' );
		if ( $height_main && $height_main != 100 ) {
			$static_css .= '.site-header__desktop .header-main { height: ' . $height_main . 'px; }';
		}

		$height_bottom = \Shopwell\Helper::get_option( 'header_bottom_height' );
		if ( $height_bottom && $height_bottom != 60 ) {
			$static_css .= '.site-header__desktop .header-bottom { height: ' . $height_bottom . 'px; }';
		}

		$height_sticky = \Shopwell\Helper::get_option( 'header_sticky_height' );
		if ( $height_sticky && $height_sticky != 80 ) {
			$static_css .= '.site-header__desktop .header-sticky { height: ' . $height_sticky . 'px; }';
		}

		// Logo dimension.
		$logo_width     = \Shopwell\Helper::get_option( 'logo_width' );
		$logo_height    = \Shopwell\Helper::get_option( 'logo_height' );
		$logo_dimension = array(
			'width'  => $logo_width,
			'height' => $logo_height,
		);
		$logo_dimension = apply_filters( 'shopwell_header_logo_dimension', $logo_dimension );

		$logo_width  = ! empty( $logo_dimension['width'] ) ? $logo_dimension['width'] : '';
		$logo_height = ! empty( $logo_dimension['height'] ) ? $logo_dimension['height'] : '';

		$unit_width  = $logo_width != 'auto' ? 'px;' : ';';
		$unit_height = $logo_height != 'auto' ? 'px;' : ';';

		$width  = ! empty( $logo_width ) ? 'width: ' . shopwell_sanitize_number( $logo_width ) . $unit_width : '';
		$height = ! empty( $logo_height ) ? 'height: ' . shopwell_sanitize_number( $logo_height ) . $unit_height : '';
		if ( $width || $height ) {
			$static_css .= '.site-header .header-logo > a img, .site-header .header-logo > a svg {' . $width . $height . '}';
		}

		$logo_width     = \Shopwell\Helper::get_option( 'mobile_logo_width' );
		$logo_height    = \Shopwell\Helper::get_option( 'mobile_logo_height' );
		$logo_dimension = array(
			'width'  => $logo_width,
			'height' => $logo_height,
		);

		$logo_dimension = apply_filters( 'shopwell_header_logo_dimension', $logo_dimension );
		$logo_width     = ! empty( $logo_dimension['width'] ) ? $logo_dimension['width'] : '';
		$logo_height    = ! empty( $logo_dimension['height'] ) ? $logo_dimension['height'] : '';
		$unit_width     = $logo_width != 'auto' ? 'px;' : ';';
		$unit_height    = $logo_height != 'auto' ? 'px;' : ';';
		$width          = ! empty( $logo_width ) ? 'width: ' . shopwell_sanitize_number( $logo_width ) . $unit_width : '';
		$height         = ! empty( $logo_height ) ? 'height: ' . shopwell_sanitize_number( $logo_height ) . $unit_height : '';
		if ( $width || $height ) {
			$static_css .= '.site-header__mobile .header-logo > a img,.site-header__mobile .header-logo > a svg {' . $width . $height . '}';
		}

		// Hamburger Menu
		if ( $header_present == 'custom' && \Shopwell\Helper::get_option( 'header_hamburger_spacing' ) ) {
			$static_css .= $this->get_spacing_field_css( '.header-items .header-hamburger, .header-hamburger, .header-v3 .header-hamburger', 'margin', 'header_hamburger_spacing' );
		}

		// Primary Menu
		if ( $header_present == 'custom' && ( $font_size = \Shopwell\Helper::get_option( 'header_primary_menu_font_size_parent_item' ) ) != 14 ) {
			$static_css .= '.site-header .primary-navigation .nav-menu > li > a { font-size: ' . shopwell_sanitize_number( $font_size ) . 'px; }';
		}
		if ( $header_present == 'custom' && ( $space = \Shopwell\Helper::get_option( 'header_primary_menu_spacing_parent_item' ) ) != 12 ) {
			$static_css .= '.site-header .primary-navigation .nav-menu > li > a { padding-left: ' . shopwell_sanitize_number( $space ) . 'px; padding-right: ' . shopwell_sanitize_number( $space ) . 'px; }';
			$static_css .= '.site-header .primary-navigation--dividers .nav-menu > li > a { padding-left: 0; padding-right: 0; }';
			$static_css .= '.site-header .primary-navigation--dividers .nav-menu > li > a:before { right: -' . shopwell_sanitize_number( $space ) . 'px; }';
			$static_css .= '.site-header .primary-navigation--dividers .nav-menu > li:not( :first-child ) { padding-left: ' . shopwell_sanitize_number( $space ) . 'px; }';
			$static_css .= '.site-header .primary-navigation--dividers .nav-menu > li:not( :last-child ) { padding-right: ' . shopwell_sanitize_number( $space ) . 'px; }';
		}

		// Secondary Menu
		if ( $header_present == 'custom' && ( $font_size = \Shopwell\Helper::get_option( 'header_secondary_menu_font_size_parent_item' ) ) != 14 ) {
			$static_css .= '.site-header .secondary-navigation .nav-menu > li > a { font-size: ' . shopwell_sanitize_number( $font_size ) . 'px; }';
		}
		if ( $header_present == 'custom' && ( $space = \Shopwell\Helper::get_option( 'header_secondary_menu_spacing_parent_item' ) ) != 12 ) {
			$static_css .= '.site-header .secondary-navigation .nav-menu > li:not(:first-child) > a { padding-left: ' . shopwell_sanitize_number( $space ) . 'px; }';
			$static_css .= '.site-header .secondary-navigation .nav-menu > li:not(:last-child) > a { padding-right: ' . shopwell_sanitize_number( $space ) . 'px; }';
		}

		// Category Menu
		if ( $header_present == 'custom' && $space = \Shopwell\Helper::get_option( 'header_category_space' ) ) {
			$static_css .= '.header-category-menu { margin-left: ' . shopwell_sanitize_number( $space ) . 'px; }';
		}

		$category_arrow_spacing = \Shopwell\Helper::get_option( 'header_category_arrow_spacing' );
		if ( isset( $category_arrow_spacing ) && $category_arrow_spacing != 50 ) {
			$static_css .= '.header-category-menu.header-category--both > .shopwell-button--subtle:after,
							.header-category--text .shopwell-button--text:before { left: ' . shopwell_sanitize_number( $category_arrow_spacing ) . '%; }';
		}
		if ( $space = \Shopwell\Helper::get_option( 'header_category_content_spacing' ) ) {
			$static_css .= '.header-category-menu .header-category__content { left: ' . shopwell_sanitize_number( $space ) . 'px; }';
			$static_css .= '.header-category--icon .header-category__content { left: auto; right: calc( ' . shopwell_sanitize_number( $space ) . 'px * -1 ); }';
		}

		// Search
		$header_search_skins = 'text';
		if ( $header_present == 'custom' ) {
			$header_search_skins = \Shopwell\Helper::get_option( 'header_search_skins' );
		} elseif ( in_array( $header_version, array( 'v1', 'v5' ) ) ) {
				$header_search_skins = 'raised';
		} elseif ( $header_version == 'v4' ) {
			$header_search_skins = 'ghost';
		} elseif ( in_array( $header_version, array( 'v8', 'v10' ) ) ) {
			$header_search_skins = 'smooth';
		} elseif ( in_array( $header_version, array( 'v2', 'v3', 'v13' ) ) ) {
			$header_search_skins = 'base';
		}

		if ( $header_search_skins == 'smooth' ) {
			if ( $background_color = \Shopwell\Helper::get_option( 'header_search_skins_background_color' ) ) {
				$static_css .= '.header-search--form.shopwell-skin--smooth { --shopwell-input__background-color: ' . shopwell_sanitize_color( $background_color ) . '; }';
				$static_css .= '.header-search--form .shopwell-button--smooth { --shopwell-color__primary--gray: ' . shopwell_sanitize_color( $background_color ) . '; }';
			}

			if ( $color = \Shopwell\Helper::get_option( 'header_search_skins_color' ) ) {
				$static_css .= '.header-search__categories-label span,
								.header-search--form .shopwell-type--input-text .header-search__field::placeholder,
								.header-search__icon span { color: ' . shopwell_sanitize_color( $color ) . '; }';
				$static_css .= '.header-search--form .shopwell-button--smooth { --shopwell-color__primary: ' . shopwell_sanitize_color( $color ) . '; }';
			}
		}
		if ( ! in_array( $header_search_skins, array( 'base', 'raised', 'smooth' ) ) && ( $border_color = \Shopwell\Helper::get_option( 'header_search_skins_border_color' ) ) ) {
			$static_css .= '.header-search--form .shopwell-type--input-text,
							.header-search--form.header-search--outside .header-search__button { border-color: ' . shopwell_sanitize_color( $border_color ) . '; }';
		}
		if ( in_array( $header_search_skins, array( 'base', 'raised', 'ghost' ) ) && ( $button_color = \Shopwell\Helper::get_option( 'header_search_skins_button_color' ) ) ) {
			$static_css .= '.header-search--form .header-search__button { --shopwell-color__primary: ' . shopwell_sanitize_color( $button_color ) . ';
																			--shopwell-color__primary--dark: ' . shopwell_sanitize_color( $button_color ) . ';
																			--shopwell-color__primary--darker: ' . shopwell_sanitize_color( $button_color ) . '; }';
			$static_css .= '.header-search--form .header-search__button.shopwell-button--raised { --shopwell-color__primary--box-shadow: ' . shopwell_sanitize_color( $this->hex_to_rgba( $button_color, 0.4 ) ) . '; }';
		}
		if ( in_array( $header_search_skins, array( 'base', 'raised', 'ghost' ) ) && ( $button_icon_color = \Shopwell\Helper::get_option( 'header_search_skins_button_icon_color' ) ) ) {
			$static_css .= '.header-search--form .header-search__button { --shopwell-color__primary--light: ' . shopwell_sanitize_color( $button_icon_color ) . '; }';
			$static_css .= '.header-search--form.header-search--inside .header-search__button { color: ' . shopwell_sanitize_color( $button_icon_color ) . '; }';
		}

		// Cart
		$header_cart_skins = '';
		if ( $header_present == 'custom' ) {
			$header_cart_skins = \Shopwell\Helper::get_option( 'header_cart_type' );
		} elseif ( $header_version == 'v4' ) {
			$header_cart_skins = 'base';
		}
		if ( in_array( $header_cart_skins, array( 'base' ) ) ) {
			if ( $background_color = \Shopwell\Helper::get_option( 'header_cart_background_color' ) ) {
				$static_css .= '.header-cart .shopwell-button--base,
								.header-cart .shopwell-button--smooth { --shopwell-color__primary: ' . shopwell_sanitize_color( $background_color ) . ';--shopwell-color__primary--dark: ' . shopwell_sanitize_color( $background_color ) . ';--shopwell-color__primary--darker: ' . shopwell_sanitize_color( $background_color ) . '; }';
			}
			if ( $color = \Shopwell\Helper::get_option( 'header_cart_color' ) ) {
				$static_css .= '.header-cart { color: ' . shopwell_sanitize_color( $color ) . '; }';
			}
		}
		if ( $background_color = \Shopwell\Helper::get_option( 'header_cart_counter_background_color' ) ) {
			$static_css .= '.header-cart__counter, .header-cart .shopwell-button--base .header-cart__counter { background-color: ' . shopwell_sanitize_color( $background_color ) . '; }';
		}
		if ( $color = \Shopwell\Helper::get_option( 'header_cart_counter_color' ) ) {
			$static_css .= '.header-cart__counter, .header-cart .shopwell-button--base .header-cart__counter { color: ' . shopwell_sanitize_color( $color ) . '; }';
		}

		// Wishlist
		if ( \Shopwell\Helper::get_option( 'header_wishlist_counter' ) ) {
			if ( $background_color = \Shopwell\Helper::get_option( 'header_wishlist_counter_background_color' ) ) {
				$static_css .= '.header-wishlist__counter { background-color: ' . shopwell_sanitize_color( $background_color ) . '; }';
			}
			if ( $color = \Shopwell\Helper::get_option( 'header_wishlist_counter_color' ) ) {
				$static_css .= '.header-wishlist__counter { color: ' . shopwell_sanitize_color( $color ) . '; }';
			}
		}

		// Compare
		if ( \Shopwell\Helper::get_option( 'header_compare_counter' ) ) {
			if ( $background_color = \Shopwell\Helper::get_option( 'header_compare_counter_background_color' ) ) {
				$static_css .= '.header-compare__counter { background-color: ' . shopwell_sanitize_color( $background_color ) . '; }';
			}
			if ( $color = \Shopwell\Helper::get_option( 'header_compare_counter_color' ) ) {
				$static_css .= '.header-compare__counter { color: ' . shopwell_sanitize_color( $color ) . '; }';
			}
		}

		// Custom HTML
		if ( $color = \Shopwell\Helper::get_option( 'header_custom_text_color' ) ) {
			$static_css .= '.header-custom-text { color: ' . shopwell_sanitize_color( $color ) . '; }';
		}
		$font_weight = \Shopwell\Helper::get_option( 'header_custom_text_font_weight' );
		if ( ! empty( $font_weight ) && $font_weight != 500 ) {
			$static_css .= '.header-custom-text { font-weight: ' . shopwell_sanitize_number( $font_weight ) . '; }';
		}
		$font_size = \Shopwell\Helper::get_option( 'header_custom_text_font_size' );
		if ( ! empty( $font_size ) && $font_size != 14 ) {
			$static_css .= '.header-custom-text { font-size: ' . shopwell_sanitize_number( $font_size ) . 'px; }';
		}

		// Empty Space
		$width = \Shopwell\Helper::get_option( 'header_empty_space' );
		if ( ! empty( $width ) && $width != 14 ) {
			$static_css .= '.header-empty-space { min-width: ' . shopwell_sanitize_number( $width ) . 'px; }';
		}

		// Hide main content for header layout v11
		if ( intval( \Shopwell\Helper::get_option( 'header_blog_hide_header_main' ) ) ) {
			$static_css .= '#site-header .header-v11 .header-main { display: none; }';
		}

		return $static_css;
	}

		/**
		 * Header static css
		 *
		 * @since  1.0.0
		 *
		 * @return string
		 */
	protected function header_color_static_css() {
		$header_layout        = 'prebuild' == Helper::get_option( 'header_present' ) ? Helper::get_option( 'header_version' ) : 'custom';
		$header_section       = '.site-header__section.header-' . $header_layout;
		$static_css           = $this->header_background_color( $header_section );
		$header_mobile_layout = 'prebuild' == Helper::get_option( 'header_mobile_present' ) ? Helper::get_option( 'header_mobile_version' ) : 'custom';
		if ( $header_mobile_layout != $header_layout ) {
			$header_mobile = '.site-header__mobile.header-' . $header_mobile_layout;
			$static_css   .= $this->header_background_color( $header_mobile );
		}

		return $static_css;
	}

	protected function header_background_color( $header_section ) {
		$static_css = '';

		$header_bc           = \Shopwell\Helper::get_option( 'header_custom_background_color' );
		$header_color        = \Shopwell\Helper::get_option( 'header_custom_background_text_color' );
		$header_border_color = \Shopwell\Helper::get_option( 'header_custom_background_border_color' );

		// Generate sub-text color
		$header_sub_text_color = $this->hex_to_rgba( $header_color, 0.8 );

		// Check if header colors are not blank
		if ( ! empty( $header_bc ) || ! empty( $header_color ) || ! empty( $header_border_color ) ) {
			if ( ! empty( $header_border_color ) ) {
				$static_css .= $header_section . ' .header-items .header-category-menu.shopwell-open > .shopwell-button--ghost{border-color:' . shopwell_sanitize_color( $header_border_color ) . ';box-shadow: none;}';
			}
			$static_css .= $header_section . ' .header-search--form.shopwell-skin--ghost{--shopwell-input__border-width: 0}';

			// Prepare variable CSS with individual checks
			$variable_css = '';
			if ( ! empty( $header_bc ) ) {
				$variable_css .= '--shopwell-header-bc:' . shopwell_sanitize_color( $header_bc ) . ';';
			}
			if ( ! empty( $header_color ) ) {
				$variable_css .= '--shopwell-header-color: ' . shopwell_sanitize_color( $header_color ) . ';';
			}
			if ( ! empty( $header_border_color ) ) {
				$variable_css .= '--shopwell-header-border-color:' . shopwell_sanitize_color( $header_border_color ) . ';';
			}
			if ( ! empty( $header_sub_text_color ) ) {
				$variable_css .= '--shopwell-header-sub-text-color:' . shopwell_sanitize_color( $header_sub_text_color ) . ';';
			}
			if ( ! empty( $variable_css ) ) {
				$static_css .= $header_section . ' {' . $variable_css . '}';
			}

			// Additional styles for header-v9 with individual checks
			if ( ! empty( $header_bc ) || ! empty( $header_color ) ) {
				if ( ! empty( $header_bc ) ) {
					$static_css .= '.site-header__section.header-v9 .header-sticky:not(.header-bottom)  {--shopwell-header-bc:' . shopwell_sanitize_color( $header_bc ) . ';}';
					$static_css .= $header_section . ' .header-main {--shopwell-header-main-background-color:' . shopwell_sanitize_color( $header_bc ) . ';}';
					$static_css .= '.site-header__section.header-v9 .header-mobile-bottom {--shopwell-header-mobile-bottom-bc:' . shopwell_sanitize_color( $header_bc ) . ';}';
				}
				if ( ! empty( $header_color ) ) {
					$static_css .= '.site-header__section.header-v9 .header-sticky:not(.header-bottom)  {--shopwell-header-color: ' . shopwell_sanitize_color( $header_color ) . ';}';
					$static_css .= $header_section . ' .header-main {--shopwell-header-main-text-color:' . shopwell_sanitize_color( $header_color ) . ';}';
					$static_css .= '.site-header__section.header-v9 .header-mobile-bottom {--shopwell-header-mobile-bottom-tc:' . shopwell_sanitize_color( $header_color ) . ';}';
				}
			}
		}

		return $static_css;
	}



	/**
	 * Header mobile static css
	 *
	 * @since  1.0.0
	 *
	 * @return string
	 */
	protected function header_mobile_static_css() {
		$static_css = '';

		$header_breakpoint = \Shopwell\Helper::get_option( 'header_mobile_breakpoint' );
		$header_breakpoint = ! empty( $header_breakpoint ) ? $header_breakpoint : '1199';

		if ( intval( $header_breakpoint ) ) {
			$static_css .= '@media (max-width: ' . $header_breakpoint . 'px) { .site-header__mobile { display: block; } }';
			$static_css .= '@media (max-width: ' . $header_breakpoint . 'px) { .site-header__desktop { display: none; } }';
		}

		// Header height.
		$height_main = \Shopwell\Helper::get_option( 'header_mobile_main_height' );
		if ( $height_main && $height_main != 62 ) {
			$static_css .= '.site-header__mobile .header-mobile-main { height: ' . shopwell_sanitize_number( $height_main ) . 'px; }';
		}

		$height_bottom = \Shopwell\Helper::get_option( 'header_mobile_bottom_height' );
		if ( $height_bottom && $height_bottom != 48 ) {
			$static_css .= '.site-header__mobile .header-mobile-bottom { height: ' . shopwell_sanitize_number( $height_bottom ) . 'px; }';
		}

		$height_sticky = \Shopwell\Helper::get_option( 'header_mobile_sticky_height' );
		if ( $height_sticky && $height_sticky != 64 ) {
			$static_css .= '.header-mobile-sticky { height: ' . shopwell_sanitize_number( $height_sticky ) . 'px; }';
		}

		return $static_css;
	}

	/**
	 * Help center CSS
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function help_center_css() {
		$static_css   = '';
		$space_top    = \Shopwell\Helper::get_option( 'help_center_search_space_top' );
		$space_bottom = \Shopwell\Helper::get_option( 'help_center_search_space_bottom' );
		$search_color = \Shopwell\Helper::get_option( 'help_center_search_color' );

		if ( $search_color == 'light' ) {
			$static_css .= '.search-bar-hc .search-bar-hc__title{color: #fff;}';
		}
		$static_css .= $this->get_design_options_field_css( '.search-bar-hc', 'help_center_search_bg', 'background' );
		$static_css .= '.search-bar-hc{padding-top:' . shopwell_sanitize_number( $space_top ) . 'px;}';
		$static_css .= '.search-bar-hc{padding-bottom:' . shopwell_sanitize_number( $space_bottom ) . 'px;}';

		return $static_css;
	}
	/**
	 * Page static css
	 *
	 * @since  1.0.0
	 *
	 * @return string
	 */
	protected function page_static_css() {
		$static_css = '';

		if ( $top = get_post_meta( Helper::get_post_ID(), 'shopwell_content_top_padding', true ) ) {
			$static_css .= '.site-content-custom-top-spacing #site-content { padding-top: ' . $top . 'px; }';
		}

		if ( $bottom = get_post_meta( Helper::get_post_ID(), 'shopwell_content_bottom_padding', true ) ) {
			$static_css .= '.site-content-custom-bottom-spacing #site-content { padding-bottom: ' . $bottom . 'px; }';
		}

		return $static_css;
	}

	/**
	 * Mobile Footer static css
	 *
	 * @since  1.0.0
	 *
	 * @return string
	 */
	protected function footer_mobile_static_css() {
		$static_css = '';

		$footer_breakpoint = \Shopwell\Helper::get_option( 'footer_mobile_breakpoint' );

		if ( intval( $footer_breakpoint ) !== 0 ) {
			$static_css .= '@media (max-width: ' . $footer_breakpoint . 'px) { .footer-mobile { display: block; } }';
			$static_css .= '@media (max-width: ' . $footer_breakpoint . 'px) { .footer-main:not( .show-on-mobile ) { display: none; } }';
		}

		return $static_css;
	}

	/**
	 * Topbar static css
	 *
	 * @since  1.0.0
	 *
	 * @return string
	 */
	protected function topbar_static_css() {
		$static_css = '';

		$background_color = \Shopwell\Helper::get_option( 'topbar_background_color' );
		if ( is_array( $background_color ) && ( $background_color['background-color'] !== '' || $background_color['gradient-color-1'] !== '' || $background_color['gradient-color-2'] !== '' ) ) {
			$static_css .= $this->get_design_options_field_css( '.topbar', 'topbar_background_color', 'background' );
			$static_css .= '.topbar:before { display: none; }';
		}

		if ( \Shopwell\Helper::get_option( 'topbar_border' ) ) {
			$static_css .= $this->get_design_options_field_css( '.topbar', 'topbar_border', 'border' );
		}

		$color = \Shopwell\Helper::get_option( 'topbar_color' );
		// Topbar text color
		if ( isset( $color['text-color'] ) && $color['text-color'] ) {
			$static_css .= '.topbar .shopwell-location,
							.topbar .header-preferences { color: ' . $color['text-color'] . '; }';
		}
		// Topbar link color
		if ( isset( $color['link-color'] ) && $color['link-color'] ) {
			$static_css .= '.topbar,.topbar-navigation .nav-menu > li > a,
							.topbar .socials-navigation .nav-menu a { color: ' . $color['link-color'] . '; }';
		}

		if ( isset( $color['link-hover-color'] ) && $color['link-hover-color'] ) {
			$static_css .= '.topbar-navigation .nav-menu > li > a:hover,
							.topbar .shopwell-location a:hover,
							.topbar .socials-navigation .nav-menu a:hover,
							.topbar .header-preferences a:hover { color: ' . $color['link-hover-color'] . '; }';
		}

		return $static_css;
	}

	/**
	 * Campaign bar static css
	 *
	 * @since  1.0.0
	 *
	 * @return string
	 */
	protected function campaign_bar_static_css() {
		$static_css = '';

		if ( \Shopwell\Helper::get_option( 'campaign_bar' ) ) {
			if ( \Shopwell\Helper::get_option( 'campaign_bg' ) ) {
				$static_css .= $this->get_design_options_field_css( '.campaign-bar', 'campaign_bg', 'background' );
			}

			if ( \Shopwell\Helper::get_option( 'campaign_border' ) ) {
				$static_css .= $this->get_design_options_field_css( '.campaign-bar', 'campaign_border', 'border' );
			}

			if ( \Shopwell\Helper::get_option( 'campaign_color' ) ) {
				$static_css .= $this->get_design_options_field_css( '.campaign-bar .campaign-bar__item', 'campaign_color', 'color' );
			}

			if ( intval( \Shopwell\Helper::get_option( 'campaign_height' ) ) != 44 ) {
				$static_css .= '.campaign-bar .campaign-bar__container { min-height: ' . shopwell_sanitize_number( \Shopwell\Helper::get_option( 'campaign_height' ) ) . 'px; }';
			}

			if ( intval( \Shopwell\Helper::get_option( 'campaign_text_size' ) ) != 14 ) {
				$static_css .= '.campaign-bar .campaign-bar__item { --shopwell-campaign-bar-size: ' . shopwell_sanitize_number( \Shopwell\Helper::get_option( 'campaign_text_size' ) ) . 'px; }';
			}

			if ( intval( \Shopwell\Helper::get_option( 'campaign_mobile_text_size' ) ) != 14 ) {
				$static_css .= '.campaign-bar .campaign-bar__item { --shopwell-campaign-bar-mobile-size: ' . shopwell_sanitize_number( \Shopwell\Helper::get_option( 'campaign_mobile_text_size' ) ) . 'px; }';
			}

			if ( intval( \Shopwell\Helper::get_option( 'campaign_text_weight' ) ) != 700 ) {
				$static_css .= '.campaign-bar .campaign-bar__item { font-weight: ' . shopwell_sanitize_number( \Shopwell\Helper::get_option( 'campaign_text_weight' ) ) . '; }';
			}

			if ( intval( \Shopwell\Helper::get_option( 'campaign_button_spacing' ) ) != 31 ) {
				$static_css .= '.campaign-bar .campaign-bar__button { margin-left: ' . shopwell_sanitize_number( \Shopwell\Helper::get_option( 'campaign_button_spacing' ) ) . 'px; }';
				$static_css .= '.rtl .campaign-bar .campaign-bar__button { margin-right: ' . shopwell_sanitize_number( \Shopwell\Helper::get_option( 'campaign_button_spacing' ) ) . 'px; }';
			}
		}

		return $static_css;
	}

	/**
	 * Footer static css
	 *
	 * @since  1.0.0
	 *
	 * @return string
	 */
	protected function footer_static_css() {
		$static_css                = '';
		$footer_text_color         = \Shopwell\Helper::get_option( 'footer_text_color' );
		$copyright_separator_color = shopwell_light_or_dark( $footer_text_color, 'rgba(255, 255, 255, 0.1)', 'rgba(185, 185, 185, 0.4)' );

		if ( \Shopwell\Helper::get_option( 'footer_options' ) == '1' ) {
			if ( \Shopwell\Helper::get_option( 'footer_bg' ) ) {
				$static_css .= $this->get_design_options_field_css( '.site-footer-widget', 'footer_bg', 'background' );
			}

			if ( \Shopwell\Helper::get_option( 'footer_text_color' ) ) {
				$static_css .= '.site-footer-widget .footer-copyright {
					border-top-color: ' . shopwell_sanitize_color( $copyright_separator_color ) . ';
				}';
			}

			if ( \Shopwell\Helper::get_option( 'footer_text_color' ) ) {
				$static_css .= $this->get_design_options_field_css( '.site-footer-widget', 'footer_text_color', 'color' );
			}
		}

		return $static_css;
	}

	/**
	 * Mobile static css
	 *
	 * @since  1.0.0
	 *
	 * @return string
	 */
	protected function mobile_static_css() {
		$static_css = '';

		if ( \Shopwell\Helper::get_option( 'mobile_navigation_bar' ) !== 'none' ) {
			if ( \Shopwell\Helper::get_option( 'mobile_navigation_bar_background_color' ) ) {
				$static_css .= '.shopwell-mobile-navigation-bar { background-color: ' . \Shopwell\Helper::get_option( 'mobile_navigation_bar_background_color' ) . '; }';
			}

			if ( \Shopwell\Helper::get_option( 'mobile_navigation_bar_color' ) ) {
				$static_css .= '.shopwell-mobile-navigation-bar .shopwell-mobile-navigation-bar__icon { color: ' . \Shopwell\Helper::get_option( 'mobile_navigation_bar_color' ) . '; }';
			}

			if ( \Shopwell\Helper::get_option( 'mobile_navigation_bar_box_shadow_color' ) ) {
				$static_css .= '.shopwell-mobile-navigation-bar { --shopwell-color__navigation-bar--box-shadow: ' . \Shopwell\Helper::get_option( 'mobile_navigation_bar_box_shadow_color' ) . '; }';
			}

			if ( \Shopwell\Helper::get_option( 'mobile_navigation_bar_spacing' ) ) {
				$static_css .= '.shopwell-mobile-navigation-bar { margin-left: ' . \Shopwell\Helper::get_option( 'mobile_navigation_bar_spacing' ) . 'px; margin-right: ' . \Shopwell\Helper::get_option( 'mobile_navigation_bar_spacing' ) . 'px; }';
			}

			if ( \Shopwell\Helper::get_option( 'mobile_navigation_bar_spacing_bottom' ) ) {
				$static_css .= '.shopwell-mobile-navigation-bar { margin-bottom: ' . \Shopwell\Helper::get_option( 'mobile_navigation_bar_spacing_bottom' ) . 'px; }';
			}

			if ( \Shopwell\Helper::get_option( 'mobile_navigation_bar_counter_background_color' ) ) {
				$static_css .= '.shopwell-mobile-navigation-bar .shopwell-mobile-navigation-bar__icon .counter { background-color: ' . \Shopwell\Helper::get_option( 'mobile_navigation_bar_counter_background_color' ) . '; }';
			}

			if ( \Shopwell\Helper::get_option( 'mobile_navigation_bar_counter_color' ) ) {
				$static_css .= '.shopwell-mobile-navigation-bar .shopwell-mobile-navigation-bar__icon .counter { color: ' . \Shopwell\Helper::get_option( 'mobile_navigation_bar_counter_color' ) . '; }';
			}
		}

		return $static_css;
	}


	/**
	 * Simple CSS code minification.
	 *
	 * @param  string $css code to be minified.
	 * @return string, minifed code
	 * @since  1.0.0
	 */
	private function minify( $css ) {
		$css = preg_replace( '/\s+/', ' ', $css );
		$css = preg_replace( '/\/\*[^\!](.*?)\*\//', '', $css );
		$css = preg_replace( '/(,|:|;|\{|}) /', '$1', $css );
		$css = preg_replace( '/ (,|;|\{|})/', '$1', $css );
		$css = preg_replace( '/(:| )0\.([0-9]+)(%|em|ex|px|in|cm|mm|pt|pc)/i', '${1}.${2}${3}', $css );
		$css = preg_replace( '/(:| )(\.?)0(%|em|ex|px|in|cm|mm|pt|pc)/i', '${1}0', $css );

		return trim( $css );
	}
}
