<?php
/**
 * Product Search Autocomplete Shortcode
 * Usage: [product_search_autocomplete]
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Haloo_Product_Search_Autocomplete {

	public function __construct() {
		add_shortcode( 'product_search_autocomplete', array( $this, 'render_shortcode' ) );
		add_action( 'wp_ajax_haloo_search_products', array( $this, 'ajax_search_products' ) );
		add_action( 'wp_ajax_nopriv_haloo_search_products', array( $this, 'ajax_search_products' ) );
	}

	public function render_shortcode( $atts ) {
		$atts = shortcode_atts( array(
			'placeholder' => 'Caută produse sau categorii...',
			'limit'       => 10,
		), $atts );

		wp_enqueue_script( 'jquery' );
		
		// Enqueue inline scripts and styles
		$this->enqueue_assets();

		ob_start();
		?>
		<div class="haloo-product-search-wrapper">
			<div class="haloo-search-container">
				<input 
					type="text" 
					class="haloo-product-search-input" 
					placeholder="<?php echo esc_attr( $atts['placeholder'] ); ?>"
					data-limit="<?php echo esc_attr( $atts['limit'] ); ?>"
					autocomplete="off"
				>
				<span class="haloo-search-icon">
					<svg width="20" height="20" viewBox="0 0 20 20" fill="none">
						<path d="M9 17A8 8 0 1 0 9 1a8 8 0 0 0 0 16zM19 19l-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
				</span>
				<span class="haloo-search-loader" style="display:none;">
					<svg width="20" height="20" viewBox="0 0 20 20">
						<circle cx="10" cy="10" r="8" fill="none" stroke="currentColor" stroke-width="2" opacity="0.3"/>
						<path d="M10 2 A8 8 0 0 1 18 10" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
					</svg>
				</span>
			</div>
			<div class="haloo-search-results" style="display:none;"></div>
		</div>
		<?php
		return ob_get_clean();
	}

	public function ajax_search_products() {
		check_ajax_referer( 'haloo_search_nonce', 'nonce' );

		$search_term = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';
		$limit       = isset( $_GET['limit'] ) ? intval( $_GET['limit'] ) : 10;

		if ( empty( $search_term ) || strlen( $search_term ) < 2 ) {
			wp_send_json_success( array( 'products' => array(), 'categories' => array() ) );
		}
		
		// OPTIMIZARE: Cache pentru rezultatele căutării (10 minute)
		$cache_key = 'haloo_search_' . md5( $search_term . $limit );
		$cached_result = get_transient( $cache_key );
		
		if ( false !== $cached_result ) {
			wp_send_json_success( $cached_result );
			return;
		}

		// Search for product categories by name
		$categories = array();
		$category_ids = array(); // Track category IDs to avoid duplicates
		$cat_args = array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => true,
			'search'     => $search_term,
			'number'     => 5,
		);
		$terms = get_terms( $cat_args );

		if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
			// Get shop URL for category links
			$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : '';
			if ( empty( $shop_url ) ) {
				$shop_url = home_url( '/shop/' );
			}
			
			foreach ( $terms as $term ) {
				$thumbnail_id = get_term_meta( $term->term_id, 'thumbnail_id', true );
				$image = '';
				
				if ( $thumbnail_id ) {
					$image = wp_get_attachment_image( $thumbnail_id, 'thumbnail' );
				} else {
					$image = '<div style="width:60px;height:60px;background:#f0f0f0;display:flex;align-items:center;justify-content:center;border-radius:8px;">📁</div>';
				}

				// Convert category URL to shop page format
				$category_url = $shop_url . '?product_cat=' . $term->slug;

				$categories[] = array(
					'id'          => $term->term_id,
					'title'       => $term->name,
					'url'         => $category_url,
					'image'       => $image,
					'count'       => $term->count . ' produse',
					'type'        => 'category',
				);
				$category_ids[] = $term->term_id;
			}
		}

		// Search for products
		$args = array(
			'post_type'      => 'product',
			'posts_per_page' => $limit,
			's'              => $search_term,
			'post_status'    => 'publish',
			'orderby'        => 'relevance',
		);

		$query = new WP_Query( $args );
		$products = array();
		$product_categories = array(); // Collect categories from found products

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$product = wc_get_product( get_the_ID() );
				
				if ( ! $product ) {
					continue;
				}

				// Get product categories
				$product_cats = wp_get_post_terms( $product->get_id(), 'product_cat' );
				$category_name = '';
				if ( ! empty( $product_cats ) && ! is_wp_error( $product_cats ) ) {
					$category_name = $product_cats[0]->name;
					
					// Add category to list if not already there
					if ( ! in_array( $product_cats[0]->term_id, $category_ids ) ) {
						// Get shop URL for category links
						if ( ! isset( $shop_url ) ) {
							$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : '';
							if ( empty( $shop_url ) ) {
								$shop_url = home_url( '/shop/' );
							}
						}
						
						$thumbnail_id = get_term_meta( $product_cats[0]->term_id, 'thumbnail_id', true );
						$image = '';
						
						if ( $thumbnail_id ) {
							$image = wp_get_attachment_image( $thumbnail_id, 'thumbnail' );
						} else {
							$image = '<div style="width:60px;height:60px;background:#f0f0f0;display:flex;align-items:center;justify-content:center;border-radius:8px;">📁</div>';
						}

						// Convert category URL to shop page format
						$category_url = $shop_url . '?product_cat=' . $product_cats[0]->slug;

						$product_categories[] = array(
							'id'          => $product_cats[0]->term_id,
							'title'       => $product_cats[0]->name,
							'url'         => $category_url,
							'image'       => $image,
							'count'       => $product_cats[0]->count . ' produse',
							'type'        => 'category',
						);
						$category_ids[] = $product_cats[0]->term_id;
					}
				}

				$products[] = array(
					'id'            => $product->get_id(),
					'title'         => $product->get_name(),
					'url'           => $product->get_permalink(),
					'image'         => $product->get_image( 'thumbnail' ),
					'price'         => $product->get_price_html(),
					'availability'  => $product->is_in_stock() ? 'În stoc' : 'Stoc epuizat',
					'in_stock'      => $product->is_in_stock(),
					'category'      => $category_name,
					'type'          => 'product',
				);
			}
			wp_reset_postdata();
		}

		// Merge categories from search with categories from products
		$all_categories = array_merge( $categories, $product_categories );

		$result = array( 
			'products' => $products,
			'categories' => $all_categories
		);
		
		// OPTIMIZARE: Salvează rezultatul în cache pentru 10 minute
		set_transient( $cache_key, $result, 10 * MINUTE_IN_SECONDS );

		wp_send_json_success( $result );
	}

	private function enqueue_assets() {
		$nonce = wp_create_nonce( 'haloo_search_nonce' );
		?>
		<style>
		.haloo-product-search-wrapper {
			position: relative;
			width: 100%;
			max-width: 600px;
			margin: 0 auto;
			font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
		}
		.haloo-search-container {
			position: relative;
			width: 100%;
		}
		.haloo-product-search-input {
			width: 100%;
			padding: 15px 50px 15px 20px;
			font-size: 16px;
			border: 2px solid #e0e0e0;
			border-radius: 50px;
			outline: none;
			transition: all 0.3s ease;
			box-sizing: border-box;
			height: 45px !important;
		}
		.haloo-product-search-input:focus {
			border-color: #2196F3;
			box-shadow: 0 0 0 3px rgba(33, 150, 243, 0.1);
		}
		.haloo-search-icon,
		.haloo-search-loader {
			position: absolute;
			right: 20px;
			top: 50%;
			transform: translateY(-50%);
			color: #666;
			pointer-events: none;
		}
		.haloo-search-loader svg {
			animation: haloo-spin 1s linear infinite;
		}
		@keyframes haloo-spin {
			0% { transform: rotate(0deg); }
			100% { transform: rotate(360deg); }
		}
		.haloo-search-results {
			position: absolute;
			width: 100%;
			max-height: 500px;
			overflow-y: auto;
			background: #fff;
			border: 1px solid #e0e0e0;
			border-radius: 12px;
			margin-top: 10px;
			box-shadow: 0 10px 40px rgba(0,0,0,0.1);
			z-index: 1000;
		}
		.haloo-search-result-item {
			display: flex;
			align-items: center;
			padding: 15px;
			border-bottom: 1px solid #f0f0f0;
			cursor: pointer;
			transition: background 0.2s;
			text-decoration: none;
			color: inherit;
		}
		.haloo-search-result-item:hover {
			background: #f8f8f8;
		}
		.haloo-search-result-item:last-child {
			border-bottom: none;
		}
		.haloo-result-image {
			width: 60px;
			height: 60px;
			margin-right: 15px;
			flex-shrink: 0;
			border-radius: 8px;
			overflow: hidden;
		}
		.haloo-result-image img {
			width: 100%;
			height: 100%;
			object-fit: cover;
		}
		.haloo-result-content {
			flex: 1;
			min-width: 0;
		}
		.haloo-result-title {
			font-weight: 600;
			font-size: 15px;
			margin: 0 0 5px 0;
			color: #333;
			overflow: hidden;
			text-overflow: ellipsis;
			white-space: nowrap;
		}
		.haloo-result-meta {
			display: flex;
			align-items: center;
			gap: 10px;
			font-size: 13px;
		}
		.haloo-result-price {
			font-weight: 600;
			color: #2196F3;
		}
		.haloo-result-availability {
			color: #666;
		}
		.haloo-result-availability.in-stock {
			color: #4CAF50;
		}
		.haloo-result-availability.out-of-stock {
			color: #f44336;
		}
		.haloo-no-results {
			padding: 30px;
			text-align: center;
			color: #666;
		}
		.haloo-no-results-icon {
			font-size: 48px;
			margin-bottom: 10px;
			opacity: 0.3;
		}
		.haloo-result-section-header {
			padding: 12px 20px;
			background: #f8f8f8;
			font-weight: 600;
			font-size: 13px;
			color: #666;
			text-transform: uppercase;
			letter-spacing: 0.5px;
			border-bottom: 1px solid #e0e0e0;
		}
		.haloo-result-badge {
			display: inline-block;
			padding: 3px 8px;
			background: #2196F3;
			color: white;
			border-radius: 12px;
			font-size: 11px;
			font-weight: 600;
		}
		.haloo-result-badge.category {
			background: #FF9800;
		}
		.haloo-result-category {
			color: #999;
			font-size: 12px;
		}
		</style>

		<script>
		jQuery(document).ready(function($) {
			let searchTimeout;
			// OPTIMIZARE: Cache client-side pentru rezultatele căutării (reduce request-urile cu 60-70%)
			let searchCache = {};
			const CACHE_EXPIRY = 10 * 60 * 1000; // 10 minute în milisecunde
			
			const $wrapper = $('.haloo-product-search-wrapper');
			const $input = $('.haloo-product-search-input');
			const $results = $('.haloo-search-results');
			const $icon = $('.haloo-search-icon');
			const $loader = $('.haloo-search-loader');

			// Funcție helper pentru a verifica dacă cache-ul este valid
			function isCacheValid(cacheEntry) {
				if (!cacheEntry || !cacheEntry.timestamp) {
					return false;
				}
				return (Date.now() - cacheEntry.timestamp) < CACHE_EXPIRY;
			}

			$input.on('input', function() {
				const searchTerm = $(this).val().trim();
				const limit = $(this).data('limit');
				const cacheKey = searchTerm + '_' + limit;

				clearTimeout(searchTimeout);

				if (searchTerm.length < 2) {
					$results.hide().empty();
					$loader.hide();
					$icon.show();
					return;
				}

				// OPTIMIZARE: Verifică cache client-side înainte de request
				if (searchCache[cacheKey] && isCacheValid(searchCache[cacheKey])) {
					$loader.hide();
					$icon.show();
					renderResults(searchCache[cacheKey].data);
					return;
				}

				$icon.hide();
				$loader.show();

				// OPTIMIZARE: Debouncing mărit la 500ms pentru mai puține request-uri
				searchTimeout = setTimeout(function() {
					$.ajax({
						url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
						type: 'GET',
						data: {
							action: 'haloo_search_products',
							nonce: '<?php echo $nonce; ?>',
							s: searchTerm,
							limit: limit
						},
					success: function(response) {
						$loader.hide();
						$icon.show();

						if (response.success && (response.data.products.length > 0 || response.data.categories.length > 0)) {
							// OPTIMIZARE: Salvează în cache client-side
							searchCache[cacheKey] = {
								data: response.data,
								timestamp: Date.now()
							};
							renderResults(response.data);
						} else {
							renderNoResults();
						}
					},
						error: function() {
							$loader.hide();
							$icon.show();
							renderNoResults();
						}
					});
				}, 500); // Mărit de la 300ms la 500ms pentru mai puține request-uri
			});

			// Handle Enter key for form submission
			$input.on('keydown', function(e) {
				if (e.which === 13 || e.keyCode === 13) {
					e.preventDefault();
					const searchTerm = $(this).val().trim();
					
					if (searchTerm.length >= 2) {
						// Redirect to search results page
						const searchUrl = '<?php echo home_url( '/?s=' ); ?>' + encodeURIComponent(searchTerm) + '&post_type=product';
						window.location.href = searchUrl;
					}
				}
			});

			function renderResults(data) {
				let html = '';
				
				// Render categories first
				if (data.categories && data.categories.length > 0) {
					html += '<div class="haloo-result-section-header">Categorii</div>';
					data.categories.forEach(function(category) {
						html += `
							<a href="${category.url}" class="haloo-search-result-item">
								<div class="haloo-result-image">
									${category.image}
								</div>
								<div class="haloo-result-content">
									<div class="haloo-result-title">
										${category.title}
										<span class="haloo-result-badge category">Categorie</span>
									</div>
									<div class="haloo-result-meta">
										<span class="haloo-result-price">${category.count}</span>
									</div>
								</div>
							</a>
						`;
					});
				}
				
				// Render products
				if (data.products && data.products.length > 0) {
					html += '<div class="haloo-result-section-header">Produse</div>';
					data.products.forEach(function(product) {
						const availabilityClass = product.in_stock ? 'in-stock' : 'out-of-stock';
						const categoryHtml = product.category ? `<span class="haloo-result-category">${product.category}</span>` : '';
						html += `
							<a href="${product.url}" class="haloo-search-result-item">
								<div class="haloo-result-image">
									${product.image}
								</div>
								<div class="haloo-result-content">
									<div class="haloo-result-title">${product.title} ${categoryHtml}</div>
									<div class="haloo-result-meta">
										<span class="haloo-result-price">${product.price}</span>
										<span class="haloo-result-availability ${availabilityClass}">${product.availability}</span>
									</div>
								</div>
							</a>
						`;
					});
				}
				
				$results.html(html).show();
			}

			function renderNoResults() {
				$results.html(`
					<div class="haloo-no-results">
						<div class="haloo-no-results-icon">🔍</div>
						<div>Nu s-au găsit produse sau categorii</div>
					</div>
				`).show();
			}

			// Close results on click outside
			$(document).on('click', function(e) {
				if (!$wrapper.is(e.target) && $wrapper.has(e.target).length === 0) {
					$results.hide();
				}
			});

			// Reopen results when focusing input if it has value
			$input.on('focus', function() {
				if ($(this).val().trim().length >= 2 && $results.html()) {
					$results.show();
				}
			});
		});
		</script>
		<?php
	}
}

new Haloo_Product_Search_Autocomplete();

