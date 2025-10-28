/**
 * Update Customizer settings live.
 *
 * @since 1.0.0
 */
( function( $ ) {
	'use strict';

	// Declare variables
	var api = wp.customize,
		$style_tag,
		$link_tag,
		shopwell_style_tag_collection = [],
		shopwell_link_tag_collection = [];

	/**
	 * Helper function to get style tag with id.
	 */
	function shopwell_get_style_tag( id ) {
		if ( shopwell_style_tag_collection[id]) {
			return shopwell_style_tag_collection[id];
		}

		$style_tag = $( 'head' ).find( '#shopwell-dynamic-' + id );

		if ( ! $style_tag.length ) {
			$( 'head' ).append( '<style id="shopwell-dynamic-' + id + '" type="text/css" href="#"></style>' );
			$style_tag = $( 'head' ).find( '#shopwell-dynamic-' + id );
		}

		shopwell_style_tag_collection[id] = $style_tag;

		return $style_tag;
	}

	/**
	 * Helper function to get link tag with id.
	 */
	function shopwell_get_link_tag( id, url ) {
		if ( shopwell_link_tag_collection[id]) {
			return shopwell_link_tag_collection[id];
		}

		$link_tag = $( 'head' ).find( '#shopwell-dynamic-link-' + id );

		if ( ! $link_tag.length ) {
			$( 'head' ).append( '<link id="shopwell-dynamic-' + id + '" type="text/css" rel="stylesheet" href="' + url + '"/>' );
			$link_tag = $( 'head' ).find( '#shopwell-dynamic-link-' + id );
		} else {
			$link_tag.attr( 'href', url );
		}

		shopwell_link_tag_collection[id] = $link_tag;

		return $link_tag;
	}

	/*
	 * Helper function to convert hex to rgba.
	 */
	function shopwell_hex2rgba( hex, opacity ) {
		if ( 'rgba' === hex.substring( 0, 4 ) ) {
			return hex;
		}

		// Expand shorthand form (e.g. "03F") to full form (e.g. "0033FF").
		var shorthandRegex = /^#?([a-f\d])([a-f\d])([a-f\d])$/i;

		hex = hex.replace( shorthandRegex, function( m, r, g, b ) {
			return r + r + g + g + b + b;
		});

		var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec( hex );

		if ( opacity ) {
			if ( 1 < opacity ) {
				opacity = 1;
			}

			opacity = ',' + opacity;
		}

		if ( result ) {
			return 'rgba(' + parseInt( result[1], 16 ) + ',' + parseInt( result[2], 16 ) + ',' + parseInt( result[3], 16 ) + opacity + ')';
		}

		return false;
	}

	/**
	 * Spacing field CSS.
	 */
	function shopwell_spacing_field_css( selector, property, setting, responsive ) {
		if ( ! Array.isArray( setting ) && 'object' !== typeof setting ) {
			return;
		}

		// Set up unit.
		var unit = 'px',
			css = '';

		if ( 'unit' in setting ) {
			unit = setting.unit;
		}

		var before = '',
			after = '';

		Object.keys( setting ).forEach( function( index, el ) {
			if ( 'unit' === index ) {
				return;
			}

			if ( responsive ) {
				if ( 'tablet' === index ) {
					before = '@media only screen and (max-width: 768px) {';
					after = '}';
				} else if ( 'mobile' === index ) {
					before = '@media only screen and (max-width: 480px) {';
					after = '}';
				} else {
					before = '';
					after = '';
				}

				css += before + selector + '{';

				Object.keys( setting[index]).forEach( function( position ) {
					if ( 'border' === property ) {
						position += '-width';
					}

					if ( setting[index][position]) {
						css += property + '-' + position + ': ' + setting[index][position] + unit + ';';
					}
				});

				css += '}' + after;
			} else {
				if ( 'border' === property ) {
					index += '-width';
				}

				css += property + '-' + index + ': ' + setting[index] + unit + ';';
			}
		});

		if ( ! responsive ) {
			css = selector + '{' + css + '}';
		}

		return css;
	}

	/**
	 * Typography field CSS.
	 */
	function shopwell_typography_field_css( selector, setting ) {
		var css = '';

		css += selector + '{';

		if ( 'default' === setting['font-family']) {
			css += 'font-family: ' + shopwell_customizer_preview.default_system_font + ';';
		} else if ( setting['font-family'] in shopwell_customizer_preview.fonts.standard_fonts.fonts ) {
			css += 'font-family: ' + shopwell_customizer_preview.fonts.standard_fonts.fonts[setting['font-family']].fallback + ';';
		} else if ( 'inherit' !== setting['font-family']) {
			css += 'font-family: "' + setting['font-family'] + '";';
		}

		css += 'font-weight:' + setting['font-weight'] + ';';
		css += 'font-style:' + setting['font-style'] + ';';
		css += 'text-transform:' + setting['text-transform'] + ';';

		if ( 'text-decoration' in setting ) {
			css += 'text-decoration:' + setting['text-decoration'] + ';';
		}
		if ( 'color' in setting ) {
			css += 'color:' + setting['color'] + ';';
		}

		if ( 'letter-spacing' in setting ) {
			css += 'letter-spacing:' + setting['letter-spacing'] + setting['letter-spacing-unit'] + ';';
		}

		if ( 'line-height-desktop' in setting ) {
			css += 'line-height:' + setting['line-height-desktop'] + ';';
		}

		if ( 'font-size-desktop' in setting && 'font-size-unit' in setting ) {
			css += 'font-size:' + setting['font-size-desktop'] + setting['font-size-unit'] + ';';
		}

		css += '}';

		if ( 'font-size-tablet' in setting && setting['font-size-tablet']) {
			css += '@media only screen and (max-width: 768px) {' + selector + '{' + 'font-size: ' + setting['font-size-tablet'] + setting['font-size-unit'] + ';' + '}' + '}';
		}

		if ( 'line-height-tablet' in setting && setting['line-height-tablet']) {
			css += '@media only screen and (max-width: 768px) {' + selector + '{' + 'line-height:' + setting['line-height-tablet'] + ';' + '}' + '}';
		}

		if ( 'font-size-mobile' in setting && setting['font-size-mobile']) {
			css += '@media only screen and (max-width: 480px) {' + selector + '{' + 'font-size: ' + setting['font-size-mobile'] + setting['font-size-unit'] + ';' + '}' + '}';
		}

		if ( 'line-height-mobile' in setting && setting['line-height-mobile']) {
			css += '@media only screen and (max-width: 480px) {' + selector + '{' + 'line-height:' + setting['line-height-mobile'] + ';' + '}' + '}';
		}

		return css;
	}

	/**
	 * Load google font.
	 */
	function shopwell_enqueue_google_font( font ) {
		if ( shopwell_customizer_preview.fonts.google_fonts.fonts[font]) {
			var id = 'google-font-' + font.trim().toLowerCase().replace( ' ', '-' );
			var url = shopwell_customizer_preview.google_fonts_url + '/css?family=' + font + ':' + shopwell_customizer_preview.google_font_weights;

			var tag = shopwell_get_link_tag( id, url );
		}
	}

	/**
	 * Design Options field CSS.
	 */
	function shopwell_design_options_css( selector, setting, type ) {
		var css = '',
			before = '',
			after = '';

		if ( 'background' === type ) {
			var bg_type = setting['background-type'];

			css += selector + '{';

			if ( 'color' === bg_type ) {
				setting['background-color'] = setting['background-color'] ? setting['background-color'] : 'inherit';
				css += 'background: ' + setting['background-color'] + ';';
			} else if ( 'gradient' === bg_type ) {
				css += 'background: ' + setting['gradient-color-1'] + ';';

				if ( 'linear' === setting['gradient-type']) {
					css +=
						'background: -webkit-linear-gradient(' +
						setting['gradient-linear-angle'] +
						'deg, ' +
						setting['gradient-color-1'] +
						' ' +
						setting['gradient-color-1-location'] +
						'%, ' +
						setting['gradient-color-2'] +
						' ' +
						setting['gradient-color-2-location'] +
						'%);' +
						'background: -o-linear-gradient(' +
						setting['gradient-linear-angle'] +
						'deg, ' +
						setting['gradient-color-1'] +
						' ' +
						setting['gradient-color-1-location'] +
						'%, ' +
						setting['gradient-color-2'] +
						' ' +
						setting['gradient-color-2-location'] +
						'%);' +
						'background: linear-gradient(' +
						setting['gradient-linear-angle'] +
						'deg, ' +
						setting['gradient-color-1'] +
						' ' +
						setting['gradient-color-1-location'] +
						'%, ' +
						setting['gradient-color-2'] +
						' ' +
						setting['gradient-color-2-location'] +
						'%);';
				} else if ( 'radial' === setting['gradient-type']) {
					css +=
						'background: -webkit-radial-gradient(' +
						setting['gradient-position'] +
						', circle, ' +
						setting['gradient-color-1'] +
						' ' +
						setting['gradient-color-1-location'] +
						'%, ' +
						setting['gradient-color-2'] +
						' ' +
						setting['gradient-color-2-location'] +
						'%);' +
						'background: -o-radial-gradient(' +
						setting['gradient-position'] +
						', circle, ' +
						setting['gradient-color-1'] +
						' ' +
						setting['gradient-color-1-location'] +
						'%, ' +
						setting['gradient-color-2'] +
						' ' +
						setting['gradient-color-2-location'] +
						'%);' +
						'background: radial-gradient(circle at ' +
						setting['gradient-position'] +
						', ' +
						setting['gradient-color-1'] +
						' ' +
						setting['gradient-color-1-location'] +
						'%, ' +
						setting['gradient-color-2'] +
						' ' +
						setting['gradient-color-2-location'] +
						'%);';
				}
			} else if ( 'image' === bg_type ) {
				css +=
					'position: relative; z-index: 0;' +
					'background-image: url(' +
					setting['background-image'] +
					');' +
					'background-size: ' +
					setting['background-size'] +
					';' +
					'background-attachment: ' +
					setting['background-attachment'] +
					';' +
					'background-position: ' +
					setting['background-position-x'] +
					'% ' +
					setting['background-position-y'] +
					'%;' +
					'background-repeat: ' +
					setting['background-repeat'] +
					';';
			}

			css += '}';

			// Background image color overlay.
			if ( 'image' === bg_type && setting['background-color-overlay'] && setting['background-image']) {
				css += selector + '::before {content: ""; position: absolute; inset: 0; z-index: -1; background-color: ' + setting['background-color-overlay'] + '; }';
			}
		} else if ( 'color' === type ) {
			setting['text-color'] = setting['text-color'] ? setting['text-color'] : 'inherit';
			setting['link-color'] = setting['link-color'] ? setting['link-color'] : 'inherit';
			setting['link-hover-color'] = setting['link-hover-color'] ? setting['link-hover-color'] : 'inherit';

			css += selector + ' { color: ' + setting['text-color'] + '; }';
			css += selector + ' a { color: ' + setting['link-color'] + '; }';
			css += selector + ' a:hover { color: ' + setting['link-hover-color'] + ' !important; }';
		} else if ( 'border' === type ) {
			setting['border-color'] = setting['border-color'] ? setting['border-color'] : 'inherit';
			setting['border-style'] = setting['border-style'] ? setting['border-style'] : 'solid';
			setting['border-left-width'] = setting['border-left-width'] ? setting['border-left-width'] : 0;
			setting['border-top-width'] = setting['border-top-width'] ? setting['border-top-width'] : 0;
			setting['border-right-width'] = setting['border-right-width'] ? setting['border-right-width'] : 0;
			setting['border-bottom-width'] = setting['border-bottom-width'] ? setting['border-bottom-width'] : 0;

			css += selector + '{';
			css += 'border-color: ' + setting['border-color'] + ';';
			css += 'border-style: ' + setting['border-style'] + ';';
			css += 'border-left-width: ' + setting['border-left-width'] + 'px;';
			css += 'border-top-width: ' + setting['border-top-width'] + 'px;';
			css += 'border-right-width: ' + setting['border-right-width'] + 'px;';
			css += 'border-bottom-width: ' + setting['border-bottom-width'] + 'px;';
			css += '}';
		}

		return css;
	}

	/**
	 * Body font.
	 */
	api( 'shopwell_typo_body', function( value ) {
		value.bind( function( newval ) {
			$style_tag = shopwell_get_style_tag( 'shopwell_typo_body' );
			var style_css = shopwell_typography_field_css( 'body', newval );

			shopwell_enqueue_google_font( newval['font-family']);

			$style_tag.html( style_css );
		});
	});

	/**
	 * Heading 1 font.
	 */
	api( 'shopwell_typo_h1', function( value ) {
		value.bind( function( newval ) {
			$style_tag = shopwell_get_style_tag( 'shopwell_typo_h1' );

			var style_css = shopwell_typography_field_css( 'h1, .h1', newval );

			shopwell_enqueue_google_font( newval['font-family']);

			$style_tag.html( style_css );
		});
	});

	/**
	 * Heading 2 font.
	 */
	api( 'shopwell_typo_h2', function( value ) {
		value.bind( function( newval ) {
			$style_tag = shopwell_get_style_tag( 'shopwell_typo_h2' );

			var style_css = shopwell_typography_field_css( 'h2, .h2', newval );

			shopwell_enqueue_google_font( newval['font-family']);

			$style_tag.html( style_css );
		});
	});

	/**
	 * Heading 3 font.
	 */
	api( 'shopwell_typo_h3', function( value ) {
		value.bind( function( newval ) {
			$style_tag = shopwell_get_style_tag( 'shopwell_typo_h3' );

			var style_css = shopwell_typography_field_css( 'h3, .h3', newval );

			shopwell_enqueue_google_font( newval['font-family']);

			$style_tag.html( style_css );
		});
	});

	/**
	 * Heading 4 font.
	 */
	api( 'shopwell_typo_h4', function( value ) {
		value.bind( function( newval ) {
			$style_tag = shopwell_get_style_tag( 'shopwell_typo_h4' );

			var style_css = shopwell_typography_field_css( 'h4, .h4', newval );

			shopwell_enqueue_google_font( newval['font-family']);

			$style_tag.html( style_css );
		});
	});

	/**
	 * Heading 5 font.
	 */
	api( 'shopwell_typo_h5', function( value ) {
		value.bind( function( newval ) {
			$style_tag = shopwell_get_style_tag( 'shopwell_typo_h5' );
			var style_css = shopwell_typography_field_css( 'h5, .h5', newval );

			shopwell_enqueue_google_font( newval['font-family']);

			$style_tag.html( style_css );
		});
	});

	/**
	 * Heading 6 font.
	 */
	api( 'shopwell_typo_h6', function( value ) {
		value.bind( function( newval ) {
			$style_tag = shopwell_get_style_tag( 'shopwell_typo_h6' );
			var style_css = shopwell_typography_field_css( 'h6, .h6', newval );

			shopwell_enqueue_google_font( newval['font-family']);

			$style_tag.html( style_css );
		});
	});
	/**
	 * Header logo font
	 */
	api( 'shopwell_logo_font', function( value ) {
		value.bind( function( newval ) {
			$style_tag = shopwell_get_style_tag( 'shopwell_logo_font' );
			var style_css = shopwell_typography_field_css( '.site-header .header-logo', newval );

			shopwell_enqueue_google_font( newval['font-family']);

			$style_tag.html( style_css );
		});
	});
	/**
	 * Primary menu font
	 */
	api( 'shopwell_typo_menu', function( value ) {
		value.bind( function( newval ) {
			$style_tag = shopwell_get_style_tag( 'shopwell_typo_menu' );
			var style_css = shopwell_typography_field_css( '.primary-navigation .nav-menu > li > a, .header-v2 .primary-navigation .nav-menu > li > a, .header-v4 .primary-navigation .nav-menu > li > a, .header-v6 .primary-navigation .nav-menu > li > a, .header-v8 .primary-navigation .nav-menu > li > a, .header-v9 .primary-navigation .nav-menu > li > a, .header-v10 .primary-navigation .nav-menu > li > a, .header-v13 .primary-navigation .nav-menu > li > a', newval );

			shopwell_enqueue_google_font( newval['font-family']);

			$style_tag.html( style_css );
		});
	});
	/**
	 * Primary menu's sub-menu font
	 */
	api( 'shopwell_typo_submenu', function( value ) {
		value.bind( function( newval ) {
			$style_tag = shopwell_get_style_tag( 'shopwell_typo_submenu' );
			var style_css = shopwell_typography_field_css( '.primary-navigation li li a, .primary-navigation li li span:not(.badge), .primary-navigation li li h6', newval );

			shopwell_enqueue_google_font( newval['font-family']);

			$style_tag.html( style_css );
		});
	});

	/**
	 * Secondary menu font
	 */
	api( 'shopwell_typo_secondary_menu', function( value ) {
		value.bind( function( newval ) {
			$style_tag = shopwell_get_style_tag( 'shopwell_typo_secondary_menu' );
			var style_css = shopwell_typography_field_css( '.secondary-navigation .nav-menu > li > a, .header-v2 .secondary-navigation .nav-menu > li > a, .header-v3 .secondary-navigation .nav-menu > li > a, .header-v5 .secondary-navigation .nav-menu > li > a, .header-v6 .secondary-navigation .nav-menu > li > a, .header-v8 .secondary-navigation .nav-menu > li > a, .header-v9 .secondary-navigation .nav-menu > li > a, .header-v10 .secondary-navigation .nav-menu > li > a, .header-v13 .secondary-navigation .nav-menu > li > a', newval );

			shopwell_enqueue_google_font( newval['font-family']);

			$style_tag.html( style_css );
		});
	});
	/**
	 * Secondary menu's sub-menu font
	 */
	api( 'shopwell_typo_sub_secondary_menu', function( value ) {
		value.bind( function( newval ) {
			$style_tag = shopwell_get_style_tag( 'shopwell_typo_sub_secondary_menu' );
			var style_css = shopwell_typography_field_css( '.secondary-navigation li li a, .secondary-navigation li li span, .secondary-navigation li li h6', newval );

			shopwell_enqueue_google_font( newval['font-family']);

			$style_tag.html( style_css );
		});
	});
	/**
	 * Typography - header category menu | Title font.
	 */
	api( 'shopwell_typo_category_menu_title', function( value ) {
		value.bind( function( newval ) {
			$style_tag = shopwell_get_style_tag( 'shopwell_typo_category_menu_title' );
			var style_css = shopwell_typography_field_css( '.header-category__name, .header-category-menu > .shopwell-button--text .header-category__name', newval );

			shopwell_enqueue_google_font( newval['font-family']);

			$style_tag.html( style_css );
		});
	});
	// Category menu
	api( 'shopwell_typo_category_menu', function( value ) {
		value.bind( function( newval ) {
			$style_tag = shopwell_get_style_tag( 'shopwell_typo_category_menu' );
			var style_css = shopwell_typography_field_css( '.header-category__menu > ul > li > a', newval );

			shopwell_enqueue_google_font( newval['font-family']);

			$style_tag.html( style_css );
		});
	});
	// Category sub menu
	api( 'shopwell_typo_sub_category_menu', function( value ) {
		value.bind( function( newval ) {
			$style_tag = shopwell_get_style_tag( 'shopwell_typo_sub_category_menu' );
			var style_css = shopwell_typography_field_css( '.header-category__menu ul ul li > *', newval );

			shopwell_enqueue_google_font( newval['font-family']);

			$style_tag.html( style_css );
		});
	});
	// Page Title typography
	api( 'shopwell_typo_page_title', function( value ) {
		value.bind( function( newval ) {
			$style_tag = shopwell_get_style_tag( 'shopwell_typo_page_title' );
			var style_css = shopwell_typography_field_css( '.page-header .page-header__title', newval );

			shopwell_enqueue_google_font( newval['font-family']);

			$style_tag.html( style_css );
		});
	});
	// Blog header Title typography
	api( 'shopwell_typo_blog_header_title', function( value ) {
		value.bind( function( newval ) {
			$style_tag = shopwell_get_style_tag( 'shopwell_typo_blog_header_title' );
			var style_css = shopwell_typography_field_css( '.shopwell-blog-page .page-header__title', newval );

			shopwell_enqueue_google_font( newval['font-family']);

			$style_tag.html( style_css );
		});
	});
	// Blog header description typography
	api( 'shopwell_typo_blog_header_description', function( value ) {
		value.bind( function( newval ) {
			$style_tag = shopwell_get_style_tag( 'shopwell_typo_blog_header_description' );
			var style_css = shopwell_typography_field_css( '.shopwell-blog-page .page-header__description', newval );

			shopwell_enqueue_google_font( newval['font-family']);

			$style_tag.html( style_css );
		});
	});
	// Blog post title typography
	api( 'shopwell_typo_blog_post_title', function( value ) {
		value.bind( function( newval ) {
			$style_tag = shopwell_get_style_tag( 'shopwell_typo_blog_post_title' );
			var style_css = shopwell_typography_field_css( '.hfeed .hentry .entry-title', newval );

			shopwell_enqueue_google_font( newval['font-family']);

			$style_tag.html( style_css );
		});
	});
	// Blog post excerpt typography
	api( 'shopwell_typo_blog_post_excerpt', function( value ) {
		value.bind( function( newval ) {
			$style_tag = shopwell_get_style_tag( 'shopwell_typo_blog_post_excerpt' );
			var style_css = shopwell_typography_field_css('.hfeed .hentry .entry-excerpt', newval);

			shopwell_enqueue_google_font( newval['font-family']);

			$style_tag.html( style_css );
		});
	});
	api( 'shopwell_typo_widget_title', function( value ) {
		value.bind( function( newval ) {
			$style_tag = shopwell_get_style_tag( 'shopwell_typo_widget_title' );
			var style_css = shopwell_typography_field_css('.blog-sidebar .widget .widget-title, .blog-sidebar .widget .widgettitle, .blog-sidebar .widget .wp-block-search__label, .single-sidebar .widget .widget-title, .single-sidebar .widget .widgettitle, .single-sidebar .widget .wp-block-search__label,.single-sidebar .wp-block-heading,.blog-sidebar .wp-block-heading,.blog-sidebar .wp-block-group .wp-block-group__inner-container > h2,.single-sidebar .wp-block-group .wp-block-group__inner-container > h2', newval);

			shopwell_enqueue_google_font( newval['font-family']);

			$style_tag.html( style_css );
		});
	});

	api( 'shopwell_typo_catalog_page_title', function( value ) {
		value.bind( function( newval ) {
			$style_tag = shopwell_get_style_tag( 'shopwell_typo_catalog_page_title' );
			var style_css = shopwell_typography_field_css('.page-header--products h1.page-header__title', newval);

			shopwell_enqueue_google_font( newval['font-family']);

			$style_tag.html( style_css );
		});
	});
	api( 'shopwell_typo_catalog_page_description', function( value ) {
		value.bind( function( newval ) {
			$style_tag = shopwell_get_style_tag( 'shopwell_typo_catalog_page_description' );
			var style_css = shopwell_typography_field_css('.page-header--products div.page-header__description', newval);

			shopwell_enqueue_google_font( newval['font-family']);

			$style_tag.html( style_css );
		});
	});
	api( 'shopwell_typo_catalog_product_title', function( value ) {
		value.bind( function( newval ) {
			$style_tag = shopwell_get_style_tag( 'shopwell_typo_catalog_product_title' );
			var style_css = shopwell_typography_field_css('ul.products li.product h2.woocommerce-loop-product__title a', newval);

			shopwell_enqueue_google_font( newval['font-family']);

			$style_tag.html( style_css );
		});
	});
	api( 'shopwell_typo_product_title', function( value ) {
		value.bind( function( newval ) {
			$style_tag = shopwell_get_style_tag( 'shopwell_typo_product_title' );
			var style_css = shopwell_typography_field_css('.single-product div.product h1.product_title, .single-product div.product.layout-4 h1.product_title, .single-product div.product.layout-5 h1.product_title, .single-product div.product.layout-6 .product-summary-wrapper h1.product_title', newval);

			shopwell_enqueue_google_font( newval['font-family']);

			$style_tag.html( style_css );
		});
	});
	/**
	 *	Header > Topbar
	 *
	 *  */

	//  Topbar background color
	api( 'shopwell_topbar_background_color', function( value ) {
		value.bind( function( newval ) {
			var $topbar = $( '.topbar' );

			if ( ! $topbar.length ) {
				return;
			}

			$style_tag = shopwell_get_style_tag( 'shopwell_topbar_background_color' );
			var style_css = shopwell_design_options_css( '.topbar', newval, 'background' );

			$style_tag.html( style_css );
		});
	});

	// Topbar font color
	api( 'shopwell_topbar_color', function( value ) {
		value.bind( function( newval ) {
			var $topbar = $( '.topbar' );

			if ( ! $topbar.length ) {
				return;
			}

			$style_tag = shopwell_get_style_tag( 'shopwell_topbar_color' );
			var style_css = '';

			newval['text-color'] = newval['text-color'] ? newval['text-color'] : 'inherit';
			newval['link-color'] = newval['link-color'] ? newval['link-color'] : 'inherit';
			newval['link-hover-color'] = newval['link-hover-color'] ? newval['link-hover-color'] : 'inherit';

			// Text color.
			style_css += '.topbar { color: ' + newval['text-color'] + '; }';
			style_css += '.shopwell-location { color: ' + newval['text-color'] + '; }';
			style_css += '.topbar .header-preferences { color: ' + newval['text-color'] + '; }';

			// Link color.
			style_css += '.topbar-navigation .nav-menu > li > a' + '{ color: ' + newval['link-color'] + '; }';

			// Link hover color.
			style_css +=
				'.topbar-navigation .nav-menu > li > a:hover, ' +
				'.shopwell-location a:hover, ' +
				'.shopwell-location a:focus, ' +
				'.topbar .header-preferences a:hover, ' +
				'.topbar .header-preferences a:focus, ' +
				'.topbar-navigation .nav-menu > li > a:focus{ color: ' + newval['link-hover-color'] +'; }';

			$style_tag.html( style_css );
		});
	});

	/**
	 * Topbar border.
	 */
	api( 'shopwell_topbar_border', function( value ) {
		value.bind( function( newval ) {
			var $topbar = $( '.topbar' );
			
			if ( ! $topbar.length ) {
				return;
			}

			$style_tag = shopwell_get_style_tag( 'shopwell_topbar_border' );
			var style_css = shopwell_design_options_css( '.topbar', newval, 'border' );

			//style_css += shopwell_design_options_css( '.topbar > ELEMENTS_CLASS_HERE', newval, 'separator_color' );

			$style_tag.html( style_css );
		});
	});

	/**
	 * Header > Header Main
	 */

	// height
	api( 'shopwell_header_main_height', function( value ) {
		value.bind( function( newval ) {
			var $main_header = $( '.site-header__desktop .header-main' );

			if ( ! $main_header.length ) {
				return;
			}

			$style_tag = shopwell_get_style_tag( 'shopwell_header_main_height' );
			var style_css = '.site-header__desktop .header-main{height:'+newval.value+'px;}';

			$style_tag.html( style_css );
		});
	});

	/**
	 * Header > Header Bottom
	 */

	// height
	api( 'shopwell_header_bottom_height', function( value ) {
		value.bind( function( newval ) {
			var $bottom_header = $( '.site-header__desktop .header-bottom' );

			if ( ! $bottom_header.length ) {
				return;
			}

			$style_tag = shopwell_get_style_tag( 'shopwell_header_bottom_height' );
			var style_css = '.site-header__desktop .header-bottom{height:'+newval.value+'px;}';

			$style_tag.html( style_css );
		});
	});


	/**
	 * Header > Background
	 */

	api( 'shopwell_header_custom_background_color', function( value ) {
		value.bind( function( newval ) {
			var $main_header = $( '.header-main' );

			if ( ! $main_header.length ) {
				return;
			}
			$style_tag = shopwell_get_style_tag( 'shopwell_header_custom_background_color' );
			
			var header_version = '.site-header__section.header-' + api._value.shopwell_header_version();
			if( wp.customize.has('shopwell_header_present') && api._value.shopwell_header_present() === 'custom' ){
				header_version = '.site-header__section.header-custom';
			}
			if( newval ){
				var style_css = header_version+'{--shopwell-header-bc:'+newval+';}';
				style_css += header_version+' .header-main{--shopwell-header-main-background-color:'+newval+';}';
				style_css += '.site-header__section.header-v9 .header-sticky:not(.header-bottom) {--shopwell-header-bc:'+newval+';}';
				style_css += '.site-header__section.header-v9 .header-mobile-bottom {--shopwell-header-mobile-bottom-bc:'+newval+';}';
				$style_tag.html( style_css );
			}else{
				$style_tag.html( '' );
			}
		});
	});

	api( 'shopwell_header_custom_background_text_color', function( value ) {
		value.bind( function( newval ) {
			var $main_header = $( '.header-main' );

			if ( ! $main_header.length ) {
				return;
			}
			$style_tag = shopwell_get_style_tag( 'shopwell_header_custom_background_text_color' );
			var header_version = '.site-header__section.header-' + api._value.shopwell_header_version();
			if( wp.customize.has('shopwell_header_present') && api._value.shopwell_header_present() === 'custom' ){
				header_version = '.site-header__section.header-custom';
			}
			if( newval ){
				var header_sub_tet_color = shopwell_hex2rgba(newval,0.8);
				var style_css = header_version+'{--shopwell-header-color:'+newval+';--shopwell-header-sub-text-color:'+header_sub_tet_color+'}';
				style_css += header_version+' .header-main{--shopwell-header-main-text-color:'+newval+';}';
				style_css += '.site-header__section.header-v9 .header-sticky:not(.header-bottom){--shopwell-header-color:'+newval+';}';
				style_css += '.site-header__section.header-v9 .header-mobile-bottom{--shopwell-header-mobile-bottom-tc:'+newval+';}';
				$style_tag.html( style_css );
			}else{
				$style_tag.html( '' );
			}
		});
	});
	api( 'shopwell_header_custom_background_border_color', function( value ) {
		value.bind( function( newval ) {
			var $main_header = $( '.header-main' );

			if ( ! $main_header.length ) {
				return;
			}
			$style_tag = shopwell_get_style_tag( 'shopwell_header_custom_background_border_color' );
			var header_version = '.site-header__section.header-' + api._value.shopwell_header_version();
			if( wp.customize.has('shopwell_header_present') && api._value.shopwell_header_present() === 'custom' ){
				header_version = '.site-header__section.header-custom';
			}
			if( newval ){
				var style_css = header_version+'{--shopwell-header-border-color:'+newval+';}';
				style_css += header_version+' .header-items .header-category-menu.shopwell-open > .shopwell-button--ghost{border-color:'+newval+';box-shadow: none;}';
				$style_tag.html( style_css );
			}else{
				$style_tag.html( '' );
			}
		});
	});

	/**
	 * Header > Sticky header
	 */

	// height
	api( 'shopwell_header_sticky_height', function( value ) {
		value.bind( function( newval ) {
			var $main_header = $( '.header-sticky' );

			if ( ! $main_header.length ) {
				return;
			}

			$style_tag = shopwell_get_style_tag( 'shopwell_header_sticky_height' );
			var style_css = '.header-sticky{height:'+newval.value+'px;}';

			$style_tag.html( style_css );
		});
	});

	/**
	 * Header > Campaign
	 */
	// Background
	api( 'shopwell_campaign_bg', function( value ) {
		value.bind( function( newval ) {
			var $campaign = $( '.campaign-bar' );

			if ( ! $campaign.length ) {
				return;
			}

			$style_tag = shopwell_get_style_tag( 'shopwell_campaign_bg' );
			var style_css = shopwell_design_options_css( '.campaign-bar', newval, 'background' );

			$style_tag.html( style_css );
		});
	});
	// Font color
	api( 'shopwell_campaign_color', function( value ) {
		value.bind( function( newval ) {
			var $topbar = $( '.campaign-bar' );

			if ( ! $topbar.length ) {
				return;
			}

			$style_tag = shopwell_get_style_tag( 'shopwell_campaign_color' );
			var style_css = '';

			newval['text-color'] = newval['text-color'] ? newval['text-color'] : 'inherit';
			newval['link-color'] = newval['link-color'] ? newval['link-color'] : 'inherit';
			newval['link-hover-color'] = newval['link-hover-color'] ? newval['link-hover-color'] : 'inherit';

			// Text color.
			style_css += '.campaign-bar .campaign-bar__item { color: ' + newval['text-color'] + '; }';

			// Link color.
			style_css += '.campaign-bar .campaign-bar__item a { color: ' + newval['link-color'] + '; }';

			// Link hover color.
			style_css +=
				'.campaign-bar .campaign-bar__item a:hover, .campaign-bar .campaign-bar__item a:focus { color: ' + newval['link-hover-color'] +'; }';

			$style_tag.html( style_css );
		});
	});
	// Height
	api( 'shopwell_campaign_height', function( value ) {
		value.bind( function( newval ) {
			var $campaign = $( '.campaign-bar' );

			if ( ! $campaign.length ) {
				return;
			}

			$style_tag = shopwell_get_style_tag( 'shopwell_campaign_height' );
			var style_css = '.campaign-bar .campaign-bar__container{height:'+newval.value+'px;}';

			$style_tag.html( style_css );
		});
	});
	// Font size
	api( 'shopwell_campaign_text_size', function( value ) {
		value.bind( function( newval ) {
			var $campaign = $( '.campaign-bar' );

			if ( ! $campaign.length ) {
				return;
			}

			$style_tag = shopwell_get_style_tag( 'shopwell_campaign_text_size' );
			var style_css = '.campaign-bar .campaign-bar__item{font-size:'+newval.value+'px;}';

			$style_tag.html( style_css );
		});
	});
	// Link spacing
	api( 'shopwell_campaign_button_spacing', function( value ) {
		value.bind( function( newval ) {
			var $campaign = $( '.campaign-bar' );

			if ( ! $campaign.length ) {
				return;
			}

			$style_tag = shopwell_get_style_tag( 'shopwell_campaign_button_spacing' );
			var style_css = '.campaign-bar .campaign-bar__button{margin-left:'+newval.value+'px;}'+
			'.rtl .campaign-bar .campaign-bar__button{margin-right:'+newval.value+'px;}';

			$style_tag.html( style_css );
		});
	});
	/**
	 * Border
	 */
	api( 'shopwell_campaign_border', function( value ) {
		value.bind( function( newval ) {
			var $campaign = $( '.campaign-bar' );
			
			if ( ! $campaign.length ) {
				return;
			}

			$style_tag = shopwell_get_style_tag( 'shopwell_campaign_border' );
			var style_css = shopwell_design_options_css( '.campaign-bar', newval, 'border' );

			//style_css += shopwell_design_options_css( '.campaign-bar > ELEMENTS_CLASS_HERE', newval, 'separator_color' );

			$style_tag.html( style_css );
		});
	});

	// Header > Search
	api( 'shopwell_header_search_form_width', function( value ) {
		value.bind( function( newval ) {
			var $search_form = $( '.header-search' );

			if ( ! $search_form.length ) {
				return;
			}
			
			$style_tag = shopwell_get_style_tag( 'shopwell_header_search_form_width' );
			var style_css = '.header-contents .header-search--form{max-width:'+newval.value+'px;}';

			$style_tag.html( style_css );
		});
	});

	api( 'shopwell_header_search_skins_background_color', function( value ) {
		value.bind( function( newval ) {
			var $search_form = $( '.header-search' );

			if ( ! $search_form.length ) {
				return;
			}

			$style_tag = shopwell_get_style_tag( 'shopwell_header_search_skins_background_color' );

			if ( ! newval ) {
				$style_tag.html('');
				return;
			}

			var style_css = '.header-search--form.shopwell-skin--smooth{--shopwell-input__background-color:'+newval+';}' +
			'.header-search--form .shopwell-button--smooth{--shopwell-color__primary--gray:'+newval+';}';

			$style_tag.html( style_css );
		});
	});

	api( 'shopwell_header_search_skins_color', function( value ) {
		value.bind( function( newval ) {
			var $search_form = $( '.header-search' );

			if ( ! $search_form.length ) {
				return;
			}

			$style_tag = shopwell_get_style_tag( 'shopwell_header_search_skins_color' );

			if ( ! newval ) {
				$style_tag.html('');
				return;
			}

			var style_css = '.header-search__categories-label span{color:'+newval+';}' +
			'.header-search--form .shopwell-type--input-text .header-search__field::placeholder{color:'+newval+';}' +
			'.header-search__icon span{color:'+newval+';}'+
			'.header-search--form .shopwell-button--smooth{--shopwell-color__primary:'+newval+';}';

			$style_tag.html( style_css );
		});
	});
	api( 'shopwell_header_search_skins_border_color', function( value ) {
		value.bind( function( newval ) {
			var $search_form = $( '.header-search' );

			if ( ! $search_form.length ) {
				return;
			}

			$style_tag = shopwell_get_style_tag( 'shopwell_header_search_skins_border_color' );

			if ( ! newval ) {
				$style_tag.html('');
				return;
			}

			var style_css = '.header-search--form .shopwell-type--input-text{border-color:'+newval+';}' +
			'.header-search--form.header-search--outside .header-search__button{border-color:'+newval+';}';

			$style_tag.html( style_css );
		});
	});
	api( 'shopwell_header_search_skins_button_color', function( value ) {
		value.bind( function( newval ) {
			var $search_form = $( '.header-search' );

			if ( ! $search_form.length ) {
				return;
			}
			
			$style_tag = shopwell_get_style_tag( 'shopwell_header_search_skins_button_color' );

			if ( ! newval ) {
				$style_tag.html('');
				return;
			}

			var style_css = '.header-search--form .header-search__button{--shopwell-color__primary:'+newval+';--shopwell-color__primary--dark:'+newval+';--shopwell-color__primary--darker:'+newval+'}' +
			'.header-search--form .header-search__button.shopwell-button--raised{--shopwell-color__primary--box-shadow:'+newval+';}';

			$style_tag.html( style_css );
		});
	});
	api( 'shopwell_header_search_skins_button_icon_color', function( value ) {
		value.bind( function( newval ) {
			var $search_form = $( '.header-search' );

			if ( ! $search_form.length ) {
				return;
			}

			$style_tag = shopwell_get_style_tag( 'shopwell_header_search_skins_button_icon_color' );

			if ( ! newval ) {
				$style_tag.html('');
				return;
			}

			var style_css = '.header-search--form .header-search__button{--shopwell-color__primary--light:'+newval+';}';

			$style_tag.html( style_css );
		});
	});

	// Header > Hamburger
	api( 'shopwell_header_hamburger_spacing', function( value ) {
		value.bind( function( newval ) {
			var $hamburger = $( '.header-hamburger' );

			if ( ! $hamburger.length ) {
				return;
			}

			$style_tag = shopwell_get_style_tag( 'shopwell_header_hamburger_spacing' );

			var style_css = shopwell_spacing_field_css( '.header-items .header-hamburger, .header-hamburger, .header-v3 .header-hamburger', 'margin', newval, true );

			$style_tag.html( style_css );
		});
	});

	// Header > Primary menu
	api( 'shopwell_header_primary_menu_font_size_parent_item', function( value ) {
		value.bind( function( newval ) {
			var $primary_menu = $( '.site-header .primary-navigation' );

			if ( ! $primary_menu.length ) {
				return;
			}

			$style_tag = shopwell_get_style_tag( 'shopwell_header_primary_menu_font_size_parent_item' );
			var style_css = '.site-header .primary-navigation .nav-menu > li > a{font-size:'+newval.value+'px;}';

			$style_tag.html( style_css );
		});
	});
	api( 'shopwell_header_primary_menu_spacing_parent_item', function( value ) {
		value.bind( function( newval ) {
			var $primary_menu = $( '.site-header .primary-navigation' );

			if ( ! $primary_menu.length ) {
				return;
			}

			$style_tag = shopwell_get_style_tag( 'shopwell_header_primary_menu_spacing_parent_item' );
			var style_css = '.site-header .primary-navigation .nav-menu > li:not(:first-child) > a{padding-left:'+newval.value+'px;}'+
			'.site-header .primary-navigation .nav-menu > li:not(:last-child) > a{padding-right:'+newval.value+'px;}';

			$style_tag.html( style_css );
		});
	});

	// Header > Secondary menu
	api( 'shopwell_header_secondary_menu_font_size_parent_item', function( value ) {
		value.bind( function( newval ) {
			var $secondary_menu = $( '.site-header .secondary-navigation' );

			if ( ! $secondary_menu.length ) {
				return;
			}

			$style_tag = shopwell_get_style_tag( 'shopwell_header_secondary_menu_font_size_parent_item' );
			var style_css = '.site-header .secondary-navigation .nav-menu > li > a{font-size:'+newval.value+'px;}';

			$style_tag.html( style_css );
		});
	});

	api( 'shopwell_header_secondary_menu_spacing_parent_item', function( value ) {
		value.bind( function( newval ) {
			var $secondary_menu = $( '.site-header .secondary-navigation' );

			if ( ! $secondary_menu.length ) {
				return;
			}

			$style_tag = shopwell_get_style_tag( 'shopwell_header_secondary_menu_spacing_parent_item' );
			var style_css = '.site-header .secondary-navigation .nav-menu > li:not(:first-child) > a{padding-left:'+newval.value+'px;}'+
			'.site-header .secondary-navigation .nav-menu > li:not(:last-child) > a{padding-right:'+newval.value+'px;}';

			$style_tag.html( style_css );
		});
	});

	// Header > Category menu
	api( 'shopwell_header_category_space', function( value ) {
		value.bind( function( newval ) {
			var $category_menu = $( '.header-category-menu' );

			if ( ! $category_menu.length ) {
				return;
			}

			$style_tag = shopwell_get_style_tag( 'shopwell_header_category_space' );
			var style_css = '.header-category-menu{margin-left:'+newval.value+'px;}';

			$style_tag.html( style_css );
		});
	});

	api( 'shopwell_header_category_arrow_spacing', function( value ) {
		value.bind( function( newval ) {

			$style_tag = shopwell_get_style_tag( 'shopwell_header_category_arrow_spacing' );
			var style_css = '.header-category-menu.header-category--both > .shopwell-button--subtle:after{left:'+newval.value+'%;}'+
			'.header-category--text .shopwell-button--text:before{left:'+newval.value+'%;}';

			$style_tag.html( style_css );
		});
	});

	api( 'shopwell_header_category_content_spacing', function( value ) {
		value.bind( function( newval ) {
			var $category_menu = $( '.header-category-menu .header-category__content' );

			if ( ! $category_menu.length ) {
				return;
			}

			$style_tag = shopwell_get_style_tag( 'shopwell_header_category_content_spacing' );
			var style_css = '.header-category-menu .header-category__content{left:'+newval.value+'px;}';

			$style_tag.html( style_css );
		});
	});
	// Header > Wishlist
	api( 'shopwell_header_wishlist_counter_background_color', function( value ) {
		value.bind( function( newval ) {
			var $wishlist = $( '.header-wishlist__counter' );

			if ( ! $wishlist.length ) {
				return;
			}

			$style_tag = shopwell_get_style_tag( 'shopwell_header_wishlist_counter_background_color' );
			var style_css = '.header-wishlist__counter{background-color:'+newval+';}';

			$style_tag.html( style_css );
		});
	});
	api( 'shopwell_header_wishlist_counter_color', function( value ) {
		value.bind( function( newval ) {
			var $wishlist = $( '.header-wishlist__counter' );

			if ( ! $wishlist.length ) {
				return;
			}

			$style_tag = shopwell_get_style_tag( 'shopwell_header_wishlist_counter_color' );
			var style_css = '.header-wishlist__counter{color:'+newval+';}';

			$style_tag.html( style_css );
		});
	});
	// Header > compare
	api( 'shopwell_header_compare_counter_background_color', function( value ) {
		value.bind( function( newval ) {
			var $compare = $( '.header-compare__counter' );

			if ( ! $compare.length ) {
				return;
			}

			$style_tag = shopwell_get_style_tag( 'shopwell_header_compare_counter_background_color' );
			var style_css = '.header-compare__counter{background-color:'+newval+';}';

			$style_tag.html( style_css );
		});
	});
	api( 'shopwell_header_compare_counter_color', function( value ) {
		value.bind( function( newval ) {
			var $compare = $( '.header-compare__counter' );

			if ( ! $compare.length ) {
				return;
			}

			$style_tag = shopwell_get_style_tag( 'shopwell_header_compare_counter_color' );
			var style_css = '.header-compare__counter{color:'+newval+';}';

			$style_tag.html( style_css );
		});
	});
	// Header > cart
	api( 'shopwell_header_cart_background_color', function( value ) {
		value.bind( function( newval ) {
			var $cart = $( '.header-cart .shopwell-button--base' );

			if ( ! $cart.length ) {
				return;
			}

			$style_tag = shopwell_get_style_tag( 'shopwell_header_cart_background_color' );
			var style_css = '.header-cart .shopwell-button--base{--shopwell-color__primary:'+newval+'; --shopwell-color__primary--dark:'+newval+'; --shopwell-color__primary--darker'+newval+';}';

			$style_tag.html( style_css );
		});
	});
	api( 'shopwell_header_cart_color', function( value ) {
		value.bind( function( newval ) {
			var $cart = $( '.header-cart' );

			if ( ! $cart.length ) {
				return;
			}

			$style_tag = shopwell_get_style_tag( 'shopwell_header_cart_color' );
			var style_css = '.header-cart{color:'+newval+';}';

			$style_tag.html( style_css );
		});
	});

	api( 'shopwell_header_cart_counter_background_color', function( value ) {
		value.bind( function( newval ) {
			var $cart_counter = $( '.header-cart__counter' );

			if ( ! $cart_counter.length ) {
				return;
			}

			$style_tag = shopwell_get_style_tag( 'shopwell_header_cart_counter_background_color' );
			var style_css = '.header-cart__counter,.header-cart .shopwell-button--base .header-cart__counter{background-color:'+newval+';}';

			$style_tag.html( style_css );
		});
	});

	api( 'shopwell_header_cart_counter_color', function( value ) {
		value.bind( function( newval ) {
			var $cart_counter = $( '.header-cart__counter' );

			if ( ! $cart_counter.length ) {
				return;
			}

			$style_tag = shopwell_get_style_tag( 'shopwell_header_cart_counter_color' );
			var style_css = '.header-cart__counter,.header-cart .shopwell-button--base .header-cart__counter{color:'+newval+';}';

			$style_tag.html( style_css );
		});
	});
	// Header > Custom text
	api( 'shopwell_header_custom_text_color', function( value ) {
		value.bind( function( newval ) {
			var $header_custom_text = $( '.header-custom-text' );

			if ( ! $header_custom_text.length ) {
				return;
			}

			$style_tag = shopwell_get_style_tag( 'shopwell_header_custom_text_color' );
			var style_css = '.header-custom-text{color:'+newval+';}';

			$style_tag.html( style_css );
		});
	});

	api( 'shopwell_header_custom_text_font_size', function( value ) {
		value.bind( function( newval ) {
			var $header_custom_text = $( '.header-custom-text' );

			if ( ! $header_custom_text.length ) {
				return;
			}

			$style_tag = shopwell_get_style_tag( 'shopwell_header_custom_text_font_size' );
			var style_css = '.header-custom-text{font-size:'+newval+'px;}';

			$style_tag.html( style_css );
		});
	});
	api( 'shopwell_header_custom_text_font_weight', function( value ) {
		value.bind( function( newval ) {
			var $header_custom_text = $( '.header-custom-text' );

			if ( ! $header_custom_text.length ) {
				return;
			}

			$style_tag = shopwell_get_style_tag( 'shopwell_header_custom_text_font_weight' );
			var style_css = '.header-custom-text{font-weight:'+newval+';}';

			$style_tag.html( style_css );
		});
	});
	// Header empty space
	api( 'shopwell_header_empty_space', function( value ) {
		value.bind( function( newval ) {
			var $header_empty_space = $( '.header-empty-space' );

			if ( ! $header_empty_space.length ) {
				return;
			}

			$style_tag = shopwell_get_style_tag( 'shopwell_header_empty_space' );
			var style_css = '.header-empty-space{min-width:'+newval.value+'px;}';

			$style_tag.html( style_css );
		});
	});

	/**
	 * Mobile
	 */
	// Mobile > Header layout
	api( 'shopwell_header_mobile_main_height', function( value ) {
		value.bind( function( newval ) {
			var $header_empty_space = $( '.site-header__mobile .header-mobile-bottom' );

			if ( ! $header_empty_space.length ) {
				return;
			}

			$style_tag = shopwell_get_style_tag( 'shopwell_header_mobile_main_height' );
			var style_css = '.site-header__mobile .header-mobile-bottom{height:'+newval.value+'px;}';

			$style_tag.html( style_css );
		});
	});
	// Mobile > Header sticky
	api( 'shopwell_header_mobile_sticky_height', function( value ) {
		value.bind( function( newval ) {
			var $header_empty_space = $( '.header-mobile-sticky' );

			if ( ! $header_empty_space.length ) {
				return;
			}

			$style_tag = shopwell_get_style_tag( 'shopwell_header_mobile_sticky_height' );
			var style_css = '.header-mobile-sticky{height:'+newval.value+'px;}';

			$style_tag.html( style_css );
		});
	});

	// Mobile >  Product catalog > Page header height
	api( 'shopwell_shop_page_header_mobile_height', function( value ) {
		value.bind( function( newval ) {
			var $header_empty_space = $( '.page-header--products .page-header__content' );

			if ( ! $header_empty_space.length ) {
				return;
			}

			$style_tag = shopwell_get_style_tag( 'shopwell_shop_page_header_mobile_height' );
			var style_css = '.page-header--products .page-header__content{height:'+newval.value+'px;}';

			$style_tag.html( style_css );
		});
	});

	// Mobile > Navigation bar
	api( 'shopwell_mobile_navigation_bar_background_color', function( value ) {
		value.bind( function( newval ) {
			var $header_custom_text = $( '.shopwell-mobile-navigation-bar' );

			if ( ! $header_custom_text.length ) {
				return;
			}

			$style_tag = shopwell_get_style_tag( 'shopwell_mobile_navigation_bar_background_color' );
			var style_css = '.shopwell-mobile-navigation-bar{background-color:'+newval+';}';

			$style_tag.html( style_css );
		});
	});
	api( 'shopwell_mobile_navigation_bar_color', function( value ) {
		value.bind( function( newval ) {
			var $header_custom_text = $( '.shopwell-mobile-navigation-bar .shopwell-mobile-navigation-bar__icon' );

			if ( ! $header_custom_text.length ) {
				return;
			}

			$style_tag = shopwell_get_style_tag( 'shopwell_mobile_navigation_bar_color' );
			var style_css = '.shopwell-mobile-navigation-bar .shopwell-mobile-navigation-bar__icon{color:'+newval+';}';

			$style_tag.html( style_css );
		});
	});
	api( 'shopwell_mobile_navigation_bar_box_shadow_color', function( value ) {
		value.bind( function( newval ) {
			var $header_custom_text = $( '.shopwell-mobile-navigation-bar' );

			if ( ! $header_custom_text.length ) {
				return;
			}

			$style_tag = shopwell_get_style_tag( 'shopwell_mobile_navigation_bar_box_shadow_color' );
			var style_css = '.shopwell-mobile-navigation-bar{--shopwell-color__navigation-bar--box-shadow:'+newval+';}';

			$style_tag.html( style_css );
		});
	});
	api( 'shopwell_mobile_navigation_bar_spacing', function( value ) {
		value.bind( function( newval ) {
			var $header_empty_space = $( '.shopwell-mobile-navigation-bar' );

			if ( ! $header_empty_space.length ) {
				return;
			}

			$style_tag = shopwell_get_style_tag( 'shopwell_mobile_navigation_bar_spacing' );
			var style_css = '.shopwell-mobile-navigation-bar{margin-left:'+newval.value+'px;margin-right:'+newval.value+'px;}';

			$style_tag.html( style_css );
		});
	});
	api( 'shopwell_mobile_navigation_bar_spacing_bottom', function( value ) {
		value.bind( function( newval ) {
			var $header_empty_space = $( '.shopwell-mobile-navigation-bar' );

			if ( ! $header_empty_space.length ) {
				return;
			}

			$style_tag = shopwell_get_style_tag( 'shopwell_mobile_navigation_bar_spacing_bottom' );
			var style_css = '.shopwell-mobile-navigation-bar{margin-bottom:'+newval.value+'px;}';

			$style_tag.html( style_css );
		});
	});
	api( 'shopwell_mobile_navigation_bar_counter_background_color', function( value ) {
		value.bind( function( newval ) {
			var $header_custom_text = $( '.shopwell-mobile-navigation-bar .shopwell-mobile-navigation-bar__icon .counter' );

			if ( ! $header_custom_text.length ) {
				return;
			}

			$style_tag = shopwell_get_style_tag( 'shopwell_mobile_navigation_bar_counter_background_color' );
			var style_css = '.shopwell-mobile-navigation-bar .shopwell-mobile-navigation-bar__icon .counter{background-color:'+newval+';}';

			$style_tag.html( style_css );
		});
	});
	api( 'shopwell_mobile_navigation_bar_counter_color', function( value ) {
		value.bind( function( newval ) {
			var $header_custom_text = $( '.shopwell-mobile-navigation-bar .shopwell-mobile-navigation-bar__icon .counter' );

			if ( ! $header_custom_text.length ) {
				return;
			}

			$style_tag = shopwell_get_style_tag( 'shopwell_mobile_navigation_bar_counter_color' );
			var style_css = '.shopwell-mobile-navigation-bar .shopwell-mobile-navigation-bar__icon .counter{:'+newval+';}';

			$style_tag.html( style_css );
		});
	});
	// Help center > Search bar
	api( 'shopwell_help_center_search_bg', function( value ) {
		value.bind( function( newval ) {
			var $search_header = $( '.search-bar-hc' );

			if ( ! $search_header.length ) {
				return;
			}

			$style_tag = shopwell_get_style_tag( 'shopwell_help_center_search_bg' );
			var style_css = shopwell_design_options_css( '.search-bar-hc', newval, 'background' );
			//console.log(style_css)
			$style_tag.html( style_css );
		});
	});

	api( 'shopwell_help_center_search_space_top', function( value ) {
		value.bind( function( newval ) {
			var $header_empty_space = $( '.search-bar-hc' );

			if ( ! $header_empty_space.length ) {
				return;
			}

			$style_tag = shopwell_get_style_tag( 'shopwell_help_center_search_space_top' );
			var style_css = '.search-bar-hc{padding-top:'+newval.value+'px;}';

			$style_tag.html( style_css );
		});
	});
	api( 'shopwell_help_center_search_space_bottom', function( value ) {
		value.bind( function( newval ) {
			var $header_empty_space = $( '.search-bar-hc' );

			if ( ! $header_empty_space.length ) {
				return;
			}

			$style_tag = shopwell_get_style_tag( 'shopwell_help_center_search_space_bottom' );
			var style_css = '.search-bar-hc{padding-bottom:'+newval.value+'px;}';

			$style_tag.html( style_css );
		});
	});

	// Shop > Page hader
	api( 'shopwell_shop_page_header_background_overlay', function( value ) {
		value.bind( function( newval ) {
			var $header_custom_text = $( '.page-header--standard .page-header__image-overlay' );

			if ( ! $header_custom_text.length ) {
				return;
			}

			$style_tag = shopwell_get_style_tag( 'shopwell_shop_page_header_background_overlay' );
			var style_css = '.page-header--standard .page-header__image-overlay{background-color:'+newval+';}';

			$style_tag.html( style_css );
		});
	});
	api( 'shopwell_shop_page_header_textcolor_custom', function( value ) {
		value.bind( function( newval ) {
			var $header_custom_text = $( '.page-header--products' );

			if ( ! $header_custom_text.length ) {
				return;
			}

			$style_tag = shopwell_get_style_tag( 'shopwell_shop_page_header_textcolor_custom' );
			var style_css = '.page-header--products{--shopwell-text-color:'+newval+';}';

			$style_tag.html( style_css );
		});
	});
	api( 'shopwell_shop_page_header_height', function( value ) {
		value.bind( function( newval ) {
			var $header_empty_space = $( '.page-header--products .page-header__content' );

			if ( ! $header_empty_space.length ) {
				return;
			}

			$style_tag = shopwell_get_style_tag( 'shopwell_shop_page_header_height' );
			var style_css = '.page-header--products .page-header__content{height:'+newval.value+'px;}';

			$style_tag.html( style_css );
		});
	});
	// Shop > Badges
	api( 'shopwell_badges_sale_bg', function( value ) {
		value.bind( function( newval ) {
			var $header_custom_text = $( '.woocommerce-badges .onsale' );

			if ( ! $header_custom_text.length ) {
				return;
			}

			$style_tag = shopwell_get_style_tag( 'shopwell_badges_sale_bg' );
			var style_css = '.woocommerce-badges .onsale{background-color:'+newval+';}';

			$style_tag.html( style_css );
		});
	});
	api( 'shopwell_badges_sale_text_color', function( value ) {
		value.bind( function( newval ) {
			var $header_custom_text = $( '.woocommerce-badges .onsale' );

			if ( ! $header_custom_text.length ) {
				return;
			}

			$style_tag = shopwell_get_style_tag( 'shopwell_badges_sale_text_color' );
			var style_css = '.woocommerce-badges .onsale{color:'+newval+';}';

			$style_tag.html( style_css );
		});
	});
	api( 'shopwell_badges_new_bg', function( value ) {
		value.bind( function( newval ) {
			var $header_custom_text = $( '.woocommerce-badges .new' );

			if ( ! $header_custom_text.length ) {
				return;
			}

			$style_tag = shopwell_get_style_tag( 'shopwell_badges_new_bg' );
			var style_css = '.woocommerce-badges .new{background-color:'+newval+';}';

			$style_tag.html( style_css );
		});
	});
	api( 'shopwell_badges_new_text_color', function( value ) {
		value.bind( function( newval ) {
			var $header_custom_text = $( '.woocommerce-badges .new' );

			if ( ! $header_custom_text.length ) {
				return;
			}

			$style_tag = shopwell_get_style_tag( 'shopwell_badges_new_text_color' );
			var style_css = '.woocommerce-badges .new{color:'+newval+';}';

			$style_tag.html( style_css );
		});
	});

	api( 'shopwell_badges_featured_bg', function( value ) {
		value.bind( function( newval ) {
			var $header_custom_text = $( '.woocommerce-badges .featured' );

			if ( ! $header_custom_text.length ) {
				return;
			}

			$style_tag = shopwell_get_style_tag( 'shopwell_badges_featured_bg' );
			var style_css = '.woocommerce-badges .featured{background-color:'+newval+';}';

			$style_tag.html( style_css );
		});
	});
	api( 'shopwell_badges_featured_text_color', function( value ) {
		value.bind( function( newval ) {
			var $header_custom_text = $( '.woocommerce-badges .featured' );

			if ( ! $header_custom_text.length ) {
				return;
			}

			$style_tag = shopwell_get_style_tag( 'shopwell_badges_featured_text_color' );
			var style_css = '.woocommerce-badges .featured{color:'+newval+';}';

			$style_tag.html( style_css );
		});
	});
	api( 'shopwell_badges_soldout_bg', function( value ) {
		value.bind( function( newval ) {
			var $header_custom_text = $( '.woocommerce-badges .sold-out' );

			if ( ! $header_custom_text.length ) {
				return;
			}

			$style_tag = shopwell_get_style_tag( 'shopwell_badges_soldout_bg' );
			var style_css = '.woocommerce-badges .sold-out{background-color:'+newval+';}';

			$style_tag.html( style_css );
		});
	});
	api( 'shopwell_badges_soldout_text_color', function( value ) {
		value.bind( function( newval ) {
			var $header_custom_text = $( '.woocommerce-badges .sold-out' );

			if ( ! $header_custom_text.length ) {
				return;
			}

			$style_tag = shopwell_get_style_tag( 'shopwell_badges_soldout_text_color' );
			var style_css = '.woocommerce-badges .sold-out{color:'+newval+';}';

			$style_tag.html( style_css );
		});
	});
	api( 'shopwell_badges_custom_bg', function( value ) {
		value.bind( function( newval ) {
			var $header_custom_text = $( '.woocommerce-badges .custom' );

			if ( ! $header_custom_text.length ) {
				return;
			}

			$style_tag = shopwell_get_style_tag( 'shopwell_badges_custom_bg' );
			var style_css = '.woocommerce-badges .custom{background-color:'+newval+';}';

			$style_tag.html( style_css );
		});
	});
	api( 'shopwell_badges_custom_color', function( value ) {
		value.bind( function( newval ) {
			var $header_custom_text = $( '.woocommerce-badges .custom' );

			if ( ! $header_custom_text.length ) {
				return;
			}

			$style_tag = shopwell_get_style_tag( 'shopwell_badges_custom_color' );
			var style_css = '.woocommerce-badges .custom{color:'+newval+';}';

			$style_tag.html( style_css );
		});
	});
	// Single product > Product layout
	api( 'shopwell_product_sale_bg', function( value ) {
		value.bind( function( newval ) {
			var $productSale = $( '.shopwell-single-product-sale' );

			if ( ! $productSale.length ) {
				return;
			}

			$style_tag = shopwell_get_style_tag( 'shopwell_product_sale_bg' );
			var style_css = shopwell_design_options_css( '.shopwell-single-product-sale', newval, 'background' );

			$style_tag.html( style_css );
		});
	});	
	api( 'shopwell_product_sale_color', function( value ) {
		value.bind( function( newval ) {
			var $productSaleText = $( '.shopwell-single-product-sale' );

			if ( ! $productSaleText.length ) {
				return;
			}

			$style_tag = shopwell_get_style_tag( 'shopwell_product_sale_color' );
			var style_css = '';

			newval['text-color'] = newval['text-color'] ? newval['text-color'] : 'inherit';

			// Text color.
			style_css += '.shopwell-single-product-sale { color: ' + newval['text-color'] + '; }';

			$style_tag.html( style_css );
		});
	});

	/*
	 * Helper function to convert hex to rgba.
	 */
	function shopwell_hex2rgba( hex, opacity ) {
		if ( 'rgba' === hex.substring( 0, 4 ) ) {
			return hex;
		}

		// Expand shorthand form (e.g. "03F") to full form (e.g. "0033FF").
		var shorthandRegex = /^#?([a-f\d])([a-f\d])([a-f\d])$/i;

		hex = hex.replace( shorthandRegex, function( m, r, g, b ) {
			return r + r + g + g + b + b;
		});

		var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec( hex );

		if ( opacity ) {
			if ( 1 < opacity ) {
				opacity = 1;
			}

			opacity = ',' + opacity;
		}

		if ( result ) {
			return 'rgba(' + parseInt( result[1], 16 ) + ',' + parseInt( result[2], 16 ) + ',' + parseInt( result[3], 16 ) + opacity + ')';
		}

		return false;
	}

	/**
	 * Helper function to lighten or darken the provided hex color.
	 */
	function shopwell_luminance( hex, percent ) {

		// Convert RGB color to HEX.
		if ( hex.includes( 'rgb' ) ) {
			hex = shopwell_rgba2hex( hex );
		}

		// Expand shorthand form (e.g. "03F") to full form (e.g. "0033FF").
		var shorthandRegex = /^#?([a-f\d])([a-f\d])([a-f\d])$/i;

		hex = hex.replace( shorthandRegex, function( m, r, g, b ) {
			return r + r + g + g + b + b;
		});

		var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec( hex );

		var isColor = /^#[0-9A-F]{6}$/i.test( hex );

		if ( ! isColor ) {
			return hex;
		}

		var from, to;

		for ( var i = 1; 3 >= i; i++ ) {
			result[i] = parseInt( result[i], 16 );
			from = 0 > percent ? 0 : result[i];
			to = 0 > percent ? result[i] : 255;
			result[i] = result[i] + Math.ceil( ( to - from ) * percent );
		}

		result = '#' + shopwell_dec2hex( result[1]) + shopwell_dec2hex( result[2]) + shopwell_dec2hex( result[3]);

		return result;
	}

	/**
	 * Convert dec to hex.
	 */
	function shopwell_dec2hex( c ) {
		var hex = c.toString( 16 );
		return 1 == hex.length ? '0' + hex : hex;
	}

	/**
	 * Convert rgb to hex.
	 */
	function shopwell_rgba2hex( c ) {
		var a, x;

		a = c.split( '(' )[1].split( ')' )[0].trim();
		a = a.split( ',' );

		var result = '';

		for ( var i = 0; 3 > i; i++ ) {
			x = parseInt( a[i]).toString( 16 );
			result += 1 === x.length ? '0' + x : x;
		}

		if ( result ) {
			return '#' + result;
		}

		return false;
	}

	/**
	 * Check if is light color.
	 */
	function shopwell_is_light_color( color = '' ) {
		var r, g, b, brightness;

		if ( color.match( /^rgb/ ) ) {
			color = color.match( /^rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*(\d+(?:\.\d+)?))?\)$/ );
			r = color[1];
			g = color[2];
			b = color[3];
		} else {
			color = +( '0x' + color.slice( 1 ).replace( 5 > color.length && /./g, '$&$&' ) );
			r = color >> 16;
			g = ( color >> 8 ) & 255;
			b = color & 255;
		}

		brightness = ( r * 299 + g * 587 + b * 114 ) / 1000;

		return 137 < brightness;
	}

	/**
	 * Detect if we should use a light or dark color on a background color.
	 */
	function shopwell_light_or_dark( color, dark = '#000000', light = '#FFFFFF' ) {
		return shopwell_is_light_color( color ) ? dark : light;
	}

	/**
	 * Footer Custom
	 */
	//Background
	api( 'shopwell_footer_bg', function( value ) {
		value.bind( function( newval ) {
			var $campaign = $( '.site-footer-widget' );

			if ( ! $campaign.length ) {
				return;
			}

			$style_tag = shopwell_get_style_tag( 'shopwell_footer_bg' );
			var style_css = shopwell_design_options_css( '.site-footer-widget', newval, 'background' );

			$style_tag.html( style_css );
		});
	});
	// Font color
	api( 'shopwell_footer_text_color', function( value ) {
		value.bind( function( newval ) {
			var $topbar = $( '.site-footer-widget' );

			if ( ! $topbar.length ) {
				return;
			}

			// Copyright separator color.
			var copyright_separator_color = shopwell_light_or_dark( newval['text-color'], 'rgba(255,255,255,0.1)', 'rgba(0,0,0,0.1)' );

			$style_tag = shopwell_get_style_tag( 'shopwell_footer_text_color' );
			var style_css = '';

			newval['text-color'] = newval['text-color'] ? newval['text-color'] : 'inherit';
			newval['link-color'] = newval['link-color'] ? newval['link-color'] : 'inherit';
			newval['link-hover-color'] = newval['link-hover-color'] ? newval['link-hover-color'] : 'inherit';

			// Text color.
			style_css += '.site-footer-widget { color: ' + newval['text-color'] + '; }';

			// Link color.
			style_css += '.site-footer-widget a' + '{ color: ' + newval['link-color'] + '; }';

			// Link hover color.
			style_css +=
				'.site-footer-widget a:hover, ' +
				'.site-footer-widget a:focus { color: ' + newval['link-hover-color'] +'; }';

			style_css += '.site-footer-widget .footer-copyright { border-top-color: ' + copyright_separator_color + '; }';

			$style_tag.html( style_css );
		});
	});
}( jQuery ) );
