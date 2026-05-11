<?php
/**
 * SEO dedicat paginii șablon Concurs (Ziua Copilului). Reutilizabil: ajustează shopwell_concurs_seo_config().
 *
 * @package Shopwell
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Pagina curentă folosește șablonul page-concurs.php.
 */
function shopwell_concurs_is_contest_page(): bool {
	if ( ! is_singular( 'page' ) ) {
		return false;
	}
	$slug = get_page_template_slug();
	return $slug === 'page-concurs.php' || strpos( (string) get_page_template(), 'page-concurs.php' ) !== false;
}

/**
 * @return array{title: string, description: string, og_type: string, url?: string}
 */
function shopwell_concurs_seo_config(): array {
	return array(
		'title'       => __( 'Concurs iPhone 17 Pro de Ziua Copilului 2026 | Înscrieri 1–30 iunie | Haloo', 'shopwell' ),
		'description' => __( 'Concurs gratuit Haloo de 1 Iunie — Ziua Copilului: câștigă un iPhone 17 Pro recondiționat (256 GB, 2 ani garanție). Participă până pe 30 iunie 2026; extragerea pe 3 iulie. Pentru persoane majore (18+), România.', 'shopwell' ),
		'og_type'     => 'article',
	);
}

/**
 * @return array<string, string>
 */
function shopwell_concurs_seo_active(): array {
	if ( ! shopwell_concurs_is_contest_page() ) {
		return array();
	}
	$c        = shopwell_concurs_seo_config();
	$c['url'] = get_permalink() ?: home_url( '/' );
	return $c;
}

/**
 * @param mixed $title .
 * @return mixed
 */
function shopwell_concurs_filter_document_title( $title ) {
	$c = shopwell_concurs_seo_active();
	return $c ? $c['title'] : $title;
}
add_filter( 'pre_get_document_title', 'shopwell_concurs_filter_document_title', 50 );

/**
 * @param string $title .
 * @return string
 */
function shopwell_concurs_filter_rankmath_title( $title ) {
	$c = shopwell_concurs_seo_active();
	return $c ? $c['title'] : $title;
}
add_filter( 'rank_math/frontend/title', 'shopwell_concurs_filter_rankmath_title', 50 );

/**
 * @param string $desc .
 * @return string
 */
function shopwell_concurs_filter_rankmath_description( $desc ) {
	$c = shopwell_concurs_seo_active();
	return $c ? $c['description'] : $desc;
}
add_filter( 'rank_math/frontend/description', 'shopwell_concurs_filter_rankmath_description', 50 );

/**
 * @param string $title .
 * @return string
 */
function shopwell_concurs_filter_rankmath_og_title( $title ) {
	$c = shopwell_concurs_seo_active();
	return $c ? $c['title'] : $title;
}
add_filter( 'rank_math/opengraph/facebook/title', 'shopwell_concurs_filter_rankmath_og_title', 50 );

/**
 * @param string $desc .
 * @return string
 */
function shopwell_concurs_filter_rankmath_og_description( $desc ) {
	$c = shopwell_concurs_seo_active();
	return $c ? $c['description'] : $desc;
}
add_filter( 'rank_math/opengraph/facebook/description', 'shopwell_concurs_filter_rankmath_og_description', 50 );

/**
 * @param string $title .
 * @return string
 */
function shopwell_concurs_filter_wpseo_title( $title ) {
	$c = shopwell_concurs_seo_active();
	return $c ? $c['title'] : $title;
}
add_filter( 'wpseo_title', 'shopwell_concurs_filter_wpseo_title', 50 );

/**
 * @param string $desc .
 * @return string
 */
function shopwell_concurs_filter_wpseo_metadesc( $desc ) {
	$c = shopwell_concurs_seo_active();
	return $c ? $c['description'] : $desc;
}
add_filter( 'wpseo_metadesc', 'shopwell_concurs_filter_wpseo_metadesc', 50 );

/**
 * @param string $title .
 * @return string
 */
function shopwell_concurs_filter_wpseo_og_title( $title ) {
	$c = shopwell_concurs_seo_active();
	return $c ? $c['title'] : $title;
}
add_filter( 'wpseo_opengraph_title', 'shopwell_concurs_filter_wpseo_og_title', 50 );

/**
 * @param string $desc .
 * @return string
 */
function shopwell_concurs_filter_wpseo_og_desc( $desc ) {
	$c = shopwell_concurs_seo_active();
	return $c ? $c['description'] : $desc;
}
add_filter( 'wpseo_opengraph_desc', 'shopwell_concurs_filter_wpseo_og_desc', 50 );
add_filter( 'wpseo_opengraph_description', 'shopwell_concurs_filter_wpseo_og_desc', 50 );

/**
 * Meta + OG de rezervă dacă nu există Yoast / Rank Math.
 */
function shopwell_concurs_print_fallback_meta(): void {
	if ( ! shopwell_concurs_is_contest_page() ) {
		return;
	}
	if ( defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) ) {
		return;
	}
	$c = shopwell_concurs_seo_active();
	if ( ! $c ) {
		return;
	}
	echo '<meta name="description" content="' . esc_attr( $c['description'] ) . '">' . "\n";
	echo '<link rel="canonical" href="' . esc_url( $c['url'] ) . '">' . "\n";
	echo '<meta property="og:title" content="' . esc_attr( $c['title'] ) . '">' . "\n";
	echo '<meta property="og:description" content="' . esc_attr( $c['description'] ) . '">' . "\n";
	echo '<meta property="og:url" content="' . esc_url( $c['url'] ) . '">' . "\n";
	echo '<meta property="og:type" content="' . esc_attr( $c['og_type'] ) . '">' . "\n";
	echo '<meta property="og:locale" content="ro_RO">' . "\n";
	echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
	echo '<meta name="twitter:title" content="' . esc_attr( $c['title'] ) . '">' . "\n";
	echo '<meta name="twitter:description" content="' . esc_attr( $c['description'] ) . '">' . "\n";
}
add_action( 'wp_head', 'shopwell_concurs_print_fallback_meta', 2 );

/**
 * JSON-LD WebPage pentru crawlere.
 */
function shopwell_concurs_print_jsonld(): void {
	if ( ! shopwell_concurs_is_contest_page() ) {
		return;
	}
	$c = shopwell_concurs_seo_active();
	if ( ! $c ) {
		return;
	}
	$schema = array(
		'@context'    => 'https://schema.org',
		'@type'       => 'WebPage',
		'name'        => $c['title'],
		'description' => $c['description'],
		'url'         => $c['url'],
		'inLanguage'  => 'ro-RO',
		'isPartOf'    => array(
			'@type' => 'WebSite',
			'name'  => get_bloginfo( 'name' ),
			'url'   => home_url( '/' ),
		),
	);
	echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
}
add_action( 'wp_head', 'shopwell_concurs_print_jsonld', 99 );
