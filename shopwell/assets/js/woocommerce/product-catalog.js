(function ($) {
    'use strict';

    var shopwell = shopwell || {};
    
    // Request deduplication cache
    shopwell.requestCache = {};
    shopwell.activeRequests = {};
    
    // Clean up old cache entries every 5 minutes
    setInterval(function() {
        var now = Date.now();
        for (var key in shopwell.requestCache) {
            if (now - shopwell.requestCache[key].timestamp > 300000) { // 5 minutes
                delete shopwell.requestCache[key];
            }
        }
    }, 300000);
    
    shopwell.init = function () {
        shopwell.$body = $(document.body),
            shopwell.$window = $(window),
            shopwell.$header = $('#site-header');

        // Catalog
        this.topCategories();
        this.catalogView();
        this.catalogToolbar();
        this.productsFilterActivated();
        this.loadMoreProducts();

		this.changeCatalogElementsFiltered();
		this.catalogToolBar();
		this.catalogOrderBy();
		this.scrollFilterSidebar();


		this.stickySidebar();
		this.priceFilterSlider();
		
		// Apply color swatches to filter sidebar
		this.applyFilterColorSwatches();
    };

    // Top Categories
    shopwell.topCategories = function () {
		if (typeof Swiper === 'undefined') {
			return;
		}

		if( shopwellData.top_categories_layout !== '1' ) {
			return;
		}

		var $container = $( '.catalog-top-categories .catalog-top-categories__wrapper' );

		$container.addClass( 'swiper-container' ).wrapInner( '<div class="swiper-wrapper"></div>' );
		$container.append( '<div class="swiper-pagination"></div>' );
		$container.find( '.catalog-top-categories__item' ).addClass( 'swiper-slide' );
		$container.after('<span class="shopwell-svg-icon shopwell-swiper-button-prev shopwell-swiper-button swiper-button"><svg viewBox="0 0 19 32"><path d="M13.552 0.72l2.656 1.76-9.008 13.52 9.008 13.52-2.656 1.76-10.192-15.28z"></path></svg></span>');
        $container.after('<span class="shopwell-svg-icon shopwell-swiper-button-next shopwell-swiper-button swiper-button"><svg viewBox="0 0 19 32"><path d="M5.648 31.28l-2.656-1.76 9.008-13.52-9.008-13.52 2.656-1.76 10.192 15.28z"></path></svg></span>');

		var $container,
			options = {
				observer: true,
    			observeParents: true,
				slidesPerView: "auto",
				spaceBetween: 0,
				navigation: {
					nextEl: '.shopwell-swiper-button-next',
					prevEl: '.shopwell-swiper-button-prev',
				},
				pagination: {
					el: $container.find( '.swiper-pagination' ).get(0),
					clickable: true,
				},
			};

		new Swiper( $container.get(0), options );

		shopwell.$window.resize( function () {
			$container.find( '.swiper-pagination .swiper-pagination-bullet' ).first().trigger( 'click' );
		}).trigger( 'resize' );

		if( $container.find( '.active' ).length == 0 ) {
			$container.find( '.catalog-top-categories__item' ).first().addClass( 'active' );
		}
	};

    // Catalog View
    shopwell.catalogView = function () {
        $('#shopwell-toolbar-view').on('click', 'a', function (e) {
            e.preventDefault();
            var $el = $(this),
                view = $el.data('view');

            if ($el.hasClass( 'current' )) {
                return;
            }

            $el.addClass( 'current' ).siblings().removeClass( 'current' );

			if( ! $el.hasClass( 'list' ) ) {
				$el.closest( '.site-main' ).find( '.product-actions' ).addClass( 'hidden' );
			}

            shopwell.$body.removeClass('catalog-view-grid-2 catalog-view-grid-3 catalog-view-grid-4 catalog-view-default catalog-view-grid-5 catalog-view-list').addClass('catalog-view-' + view);

            shopwell.catalogViewSwich();

			if( ! $el.hasClass( 'list' ) ) {
				setTimeout( function() {
					$el.closest( '.site-main' ).find( '.product-actions' ).removeClass( 'hidden' );
				}, 100 );
			}

            document.cookie = 'catalog_view=' + view + ';domain=' + window.location.host + ';path=/';
        });

        shopwell.catalogViewSwich();
    }

    shopwell.catalogViewSwich = function () {
        if ( shopwell.$body.hasClass( 'catalog-view-grid-2' ) ) {
            shopwell.$body.find( 'ul.products' ).removeClass( 'columns-1 columns-3 columns-4 columns-5').addClass( 'columns-2'  );
		} else if ( shopwell.$body.hasClass( 'catalog-view-grid-3' ) ) {
            shopwell.$body.find( 'ul.products' ).removeClass( 'columns-1 columns-2 columns-4 columns-5').addClass( 'columns-3' );
        } else if( shopwell.$body.hasClass( 'catalog-view-default' ) || shopwell.$body.hasClass( 'catalog-view-grid-4' ) ) {
            shopwell.$body.find( 'ul.products' ).removeClass( 'columns-1 columns-2 columns-3 columns-5' ).addClass( 'columns-4' );
        } else if( shopwell.$body.hasClass( 'catalog-view-grid-5' ) ) {
            shopwell.$body.find( 'ul.products' ).removeClass( 'columns-1 columns-2 columns-3 columns-4' ).addClass( 'columns-5' );
        } else if( shopwell.$body.hasClass( 'catalog-view-list' ) ) {
            shopwell.$body.find( 'ul.products' ).removeClass( 'columns-2 columns-3 columns-4 columns-5' ).addClass( 'columns-1' );
        }
    }

	shopwell.catalogToolbar = function() {
		if( shopwellData.catalog_toolbar_layout !== '2' ) {
			return;
		}

		var $tools = $( '.catalog-toolbar--top' );

		// Products ordering.
		if ( $.fn.select2 ) {
			$tools.find( '.woocommerce-ordering select' ).select2( {
				width                  : 'auto',
				minimumResultsForSearch: -1,
				selectionCssClass      : 'shopwell-input--default',
				dropdownCssClass	   : 'product-order',
				dropdownParent         : $tools.find( '.woocommerce-ordering' )
			} );
		}
	}

	shopwell.productsFilterActivated = function () {
        var $primaryFilter = $( '.catalog-toolbar__filters-actived' ),
			$panelFilter = $( '.filter-sidebar-panel' ),
            $widgetFilter = $panelFilter.find( '.products-filter__activated-items' ),
			$removeAll = '<a href="#" class="remove-filtered-all shopwell-button shopwell-button--subtle">Șterge tot</a>';

        	if( $.trim( $widgetFilter.html() ) ) {
				$primaryFilter.html('');
				$primaryFilter.removeClass( 'active' );
				$primaryFilter.prepend( $widgetFilter.html() + $removeAll );
				$primaryFilter.addClass( 'active' );
			}

        shopwell.$body.on( 'shopwell_products_filter_widget_updated', function (e, form) {
            var $panel = $(form).closest('.filter-sidebar-panel'),
				$widgetNewFilter = $panel.find('.products-filter__activated-items');

				if( $.trim( $widgetNewFilter.html() ) ) {
					$primaryFilter.removeClass('hidden');
					$primaryFilter.html('');
					$primaryFilter.removeClass( 'active' );
					$primaryFilter.prepend( $widgetNewFilter.html() + $removeAll );
					$primaryFilter.addClass( 'active' );
				}
        });

        $primaryFilter.on( 'click', '.remove-filtered', function (e) {
            e.preventDefault();
            var $this = $(this);
            var filterType = $this.data('filter-type');
            var filterValue = $this.data('filter-value');
            var value = $this.data('value');
            
            // Remove from URL based on filter type
            var currentUrl = new URL(window.location.href);
            var params = new URLSearchParams(currentUrl.search);
            var removed = false;
            
			// Remove the specific filter parameter based on type
			if (filterType === 'stare') {
				params.delete('filter_pa_stare');
				removed = true;
			} else if (filterType === 'culoare') {
				params.delete('filter_pa_culoare');
				removed = true;
			} else if (filterType === 'categorie') {
				params.delete('product_cat');
				removed = true;
			} else if (filterType === 'capacitate') {
				params.delete('filter_pa_capacitate');
				removed = true;
			} else if (filterType === 'memorie') {
				params.delete('filter_pa_memorie');
				removed = true;
			} else if (filterType === 'marca') {
				params.delete('filter_pa_marca');
				removed = true;
			} else if (filterType === 'model') {
				params.delete('filter_pa_model');
				removed = true;
			} else if (filterType === 'pret') {
				params.delete('min_price');
				params.delete('max_price');
				removed = true;
			} else {
                // Fallback: try to find by value
                for (var [key, val] of params.entries()) {
                    if (val === value) {
                        params.delete(key);
                        removed = true;
                        break;
                    }
                }
            }
            
            if (removed) {
                currentUrl.search = params.toString();
                currentUrl.searchParams.set('paged', '1'); // Reset to first page
                window.location.href = currentUrl.toString();
            }

            return false;
        });

		$primaryFilter.on( 'click', '.remove-filtered-all', function (e) {
			e.preventDefault();
			$primaryFilter.html('');
			$primaryFilter.removeClass( 'active' );
			
			// Auto-apply clear all filters
			var currentUrl = new URL(window.location.href);
			var cleanUrl = currentUrl.origin + currentUrl.pathname;
			cleanUrl += '?paged=1'; // Reset to first page
			window.location.href = cleanUrl;
		});
    };

    /**
	 * Ajax load more products.
	 */
	shopwell.loadMoreProducts = function() {
		// Infinite scroll.
		if ( $( '.woocommerce-navigation' ).hasClass( 'ajax-infinite' ) ) {
			var waiting = false,
				scrollTimeout;

			$( window ).on( 'scroll', function() {
				if ( waiting ) {
					return;
				}

				// Clear any existing timeout
				clearTimeout( scrollTimeout );

				// Throttle scroll events - only check after user stops scrolling
				scrollTimeout = setTimeout( function() {
					if ( !waiting ) {
						waiting = true;
						infiniteScoll();
						
						// Reset waiting state after a short delay
						setTimeout( function() {
							waiting = false;
						}, 300 );
					}
				}, 150 );
			});

		}

		function infiniteScoll() {
			var $navigation = $( '.woocommerce-navigation.ajax-navigation' ),
				$button = $( 'a', $navigation );

			if ( shopwell.isVisible( $navigation ) && $button.length && !$navigation.hasClass( 'loading' ) ) {
                $navigation.addClass( 'loading' );

				loadProducts( $button, function( respond ) {
					$button = $navigation.find( 'a' );
				});
			}
		}

		//Load More
		if ( $( '.woocommerce-navigation' ).hasClass( 'ajax-loadmore' ) ) {
			shopwell.$body.on( 'click', '.woocommerce-navigation.ajax-loadmore a', function (event) {
				event.preventDefault();
				loadMore();

			});
		}

		function loadMore() {
			var $navigation = $( '.woocommerce-navigation.ajax-navigation' ),
				$button = $( 'a', $navigation );

			if ( shopwell.isVisible( $navigation ) && $button.length && !$navigation.hasClass( 'loading' ) ) {
                $navigation.addClass( 'loading' );

				loadProducts( $button, function( respond ) {
					$button = $navigation.find( 'a' );
				});
			}
		}

		/**
		 * Ajax load products.
		 *
		 * @param jQuery $el Button element.
		 * @param function callback The callback function.
		 */
		function loadProducts( $el, callback ) {
			var $nav = $el.closest( '.woocommerce-navigation' ),
				totalProduct = $nav.closest('#main').children().find('.product').length,
				url = $el.attr( 'href' );

			// Check if request is already in progress
			if ( shopwell.activeRequests[url] ) {
				return;
			}

			// Check cache first (with 30 second expiry)
			var cacheKey = url + '_' + totalProduct;
			var cachedData = shopwell.requestCache[cacheKey];
			if ( cachedData && (Date.now() - cachedData.timestamp) < 30000 ) {
				if ( 'function' === typeof callback ) {
					callback( cachedData.response );
				}
				return;
			}

			// Mark request as active
			shopwell.activeRequests[url] = true;

			$.get( url, function( response ) {
				var $content = $( '#main', response ),
					$list = $( 'ul.products', $content ),
					numberPosts = $list.find( '.product' ).length + totalProduct,
					$products = $list.children(),
					$found = $('.shopwell-posts-found'),
					$newNav = $( '.woocommerce-navigation.ajax-navigation', $content );

				if (shopwell.$window.width() > 768) {
					$products.each( function( index, product ) {
						$( product ).css( 'animation-delay', index * 100 + 'ms' );
					} );
					$products.addClass( 'animated shopwellFadeInUp' );
				}

				$products.appendTo( $nav.parent().find( 'ul.products' ) );

				if ( $newNav.length ) {
					$el.replaceWith( $( 'a', $newNav ) );
				} else {
					$nav.fadeOut( function() {
						$nav.remove();
					} );
				}

				if ( 'function' === typeof callback ) {
					callback( response );
				}

				shopwell.$body.trigger( 'shopwell_products_loaded', [$products, true] );

				$found.find('.current-post').html(' ' + numberPosts);

				shopwell.postsFound();

				$nav.removeClass( 'loading' );

				if ( shopwellData.shop_nav_ajax_url_change ) {
					window.history.pushState( null, '', url );
				}

				// Cache the response
				shopwell.requestCache[cacheKey] = {
					response: response,
					timestamp: Date.now()
				};

				// Clean up active request
				delete shopwell.activeRequests[url];
			}).fail(function() {
				// Clean up active request on failure
				delete shopwell.activeRequests[url];
				$nav.removeClass( 'loading' );
			});
		}
	};

    /**
	 * Check if an element is in view-port or not
	 *
	 * @param jQuery el Targe element to check.
	 * @return boolean
	 */
	shopwell.isVisible = function( el ) {
		if ( el instanceof jQuery ) {
			el = el[0];
		}

		if ( ! el ) {
			return false;
		}

		var rect = el.getBoundingClientRect();

		return rect.bottom > 0 &&
			rect.right > 0 &&
			rect.left < (window.innerWidth || document.documentElement.clientWidth) &&
			rect.top < (window.innerHeight || document.documentElement.clientHeight);
	};

	shopwell.changeCatalogElementsFiltered = function () {
			
		// Function to clean filter parameters before adding new ones
		function cleanFilterParams(params) {
			// Remove all existing filter parameters
			params.delete('filter_pa_culoare');
			params.delete('filter_pa_stare');
			params.delete('filter_pa_capacitate');
			params.delete('filter_pa_memorie');
			params.delete('filter_pa_marca');
			// Add more filter parameters as needed
			return params;
		}
		
		// Function to clean specific filter type parameters
		function cleanSpecificFilterType(params, filterType) {
			if (filterType === 'culoare') {
				params.delete('filter_pa_culoare');
			} else if (filterType === 'stare') {
				params.delete('filter_pa_stare');
			} else if (filterType === 'categorie') {
				params.delete('product_cat');
			} else if (filterType === 'capacitate') {
				params.delete('filter_pa_capacitate');
			} else if (filterType === 'memorie') {
				params.delete('filter_pa_memorie');
			} else if (filterType === 'marca') {
				params.delete('filter_pa_marca');
			} else if (filterType === 'model') {
				params.delete('filter_pa_model');
			}
			return params;
		}
		
		// Function to determine filter type and set appropriate parameter
		function setFilterParameter(params, classes, dataValue, dataSlug, $element) {
			
			// Check if this element is inside a model filter widget
			var isModelFilter = false;
			if ($element && $element.length) {
				var $widget = $element.closest('.shopwell-model-filter-widget');
				if ($widget.length > 0) {
					isModelFilter = true;
				}
			}
			
			// Priority 1: Use data-value if available
			if (dataValue && dataValue !== 'button' && dataValue !== '') {
				// Check if it's a model filter (must be checked first if element is in model widget)
				if (isModelFilter) {
					params.set('filter_pa_model', dataValue);
					return { success: true, filterType: 'model' };
				}
				
				// Check if it's a condition filter
				if (dataValue.includes('ca-nou') || dataValue.includes('excelent') || 
					dataValue.includes('foarte-bun') || dataValue.includes('bun')) {
					params.set('filter_pa_stare', dataValue);
					return { success: true, filterType: 'stare' };
				}
				// Check if it's a memory filter
				else if (dataValue.includes('gb') || dataValue.includes('tb')) {
					params.set('filter_pa_memorie', dataValue);
					return { success: true, filterType: 'memorie' };
				}
				// Check if it's a color filter (any color that's not a condition or memory)
				else if (!dataValue.includes('ca-nou') && !dataValue.includes('excelent') && 
					!dataValue.includes('foarte-bun') && !dataValue.includes('bun') &&
					!dataValue.includes('gb') && !dataValue.includes('tb') &&
					dataValue.length > 0) {
					params.set('filter_pa_culoare', dataValue);
					return { success: true, filterType: 'culoare' };
				}
			}
			
			// Priority 2: Use data-slug if available
			if (dataSlug && dataSlug !== 'button' && dataSlug !== '') {
				// Check if it's a model filter (must be checked first if element is in model widget)
				if (isModelFilter) {
					params.set('filter_pa_model', dataSlug);
					return { success: true, filterType: 'model' };
				}
				
				// Check if it's a condition filter
				if (dataSlug.includes('ca-nou') || dataSlug.includes('excelent') || 
					dataSlug.includes('foarte-bun') || dataSlug.includes('bun')) {
					params.set('filter_pa_stare', dataSlug);
					return { success: true, filterType: 'stare' };
				}
				// Check if it's a memory filter
				else if (dataSlug.includes('gb') || dataSlug.includes('tb')) {
					params.set('filter_pa_memorie', dataSlug);
					return { success: true, filterType: 'memorie' };
				}
				// Check if it's a color filter (any color that's not a condition or memory)
				else if (!dataSlug.includes('ca-nou') && !dataSlug.includes('excelent') && 
					!dataSlug.includes('foarte-bun') && !dataSlug.includes('bun') &&
					!dataSlug.includes('gb') && !dataSlug.includes('tb') &&
					dataSlug.length > 0) {
					params.set('filter_pa_culoare', dataSlug);
					return { success: true, filterType: 'culoare' };
				}
			}
			
			// Priority 3: Extract from classes as fallback
			// Check for condition filters first
			if (classes.includes('swatch-ca-nou') || classes.includes('swatch-excelent') || 
				classes.includes('swatch-foarte-bun') || classes.includes('swatch-bun')) {
				
				var conditionMatch = classes.match(/swatch-([a-z-]+)/);
				if (conditionMatch) {
					params.set('filter_pa_stare', conditionMatch[1]);
					return { success: true, filterType: 'stare' };
				}
			}
			
			// Check for memory filters
			if (classes.includes('swatch-16gb') || classes.includes('swatch-32gb') || 
				classes.includes('swatch-64gb') || classes.includes('swatch-128gb') ||
				classes.includes('swatch-256gb') || classes.includes('swatch-512gb') || 
				classes.includes('swatch-1tb')) {
				
				var memoryMatch = classes.match(/swatch-([a-z0-9]+)/);
				if (memoryMatch) {
					params.set('filter_pa_memorie', memoryMatch[1]);
					return { success: true, filterType: 'memorie' };
				}
			}
			
			// Check for color filters
			if (classes.includes('swatch-alb') || classes.includes('swatch-albastru') || 
				classes.includes('swatch-argintiu') || classes.includes('swatch-auriu') ||
				classes.includes('swatch-bronz') || classes.includes('swatch-galben') || 
				classes.includes('swatch-gri') || classes.includes('swatch-negru') || 
				classes.includes('swatch-portocaliu') || classes.includes('swatch-rosu') || 
				classes.includes('swatch-roz') || classes.includes('swatch-verde') || 
				classes.includes('swatch-violet')) {
				
				var colorMatch = classes.match(/swatch-([a-z-]+)/);
				if (colorMatch) {
					params.set('filter_pa_culoare', colorMatch[1]);
					return { success: true, filterType: 'culoare' };
				}
			}
			
			return { success: false, filterType: null };
		}
		
		// Function to update active filters display
		// OPTIMIZATION: Removed console.log statements for production performance
		function updateActiveFiltersDisplay() {
			var $primaryFilter = $('.catalog-toolbar__filters-actived');
			var $panelFilter = $('.filter-sidebar-panel');
			var $widgetFilter = $panelFilter.find('.products-filter__activated-items');
			
			if ($primaryFilter.length && $widgetFilter.length) {
				if ($.trim($widgetFilter.html())) {
					$primaryFilter.html('');
					$primaryFilter.removeClass('active');
					$primaryFilter.prepend($widgetFilter.html() + '<a href="#" class="remove-filtered-all shopwell-button shopwell-button--subtle">Șterge tot</a>');
					$primaryFilter.addClass('active');
				} else {
					$primaryFilter.html('');
					$primaryFilter.removeClass('active');
				}
			}
		}
		
		// Debounce timer for filter sidebar refresh
		var refreshFilterSidebarTimeout;
		
		// Function to refresh filter sidebar after any filter change
		// OPTIMIZATION: Added debouncing to prevent multiple rapid calls
		function refreshFilterSidebar() {
			// Clear existing timeout
			clearTimeout(refreshFilterSidebarTimeout);
			
			// Debounce: only execute after 300ms of no changes
			refreshFilterSidebarTimeout = setTimeout(function() {
				// Force update of filter widgets via AJAX (now optimized)
				refreshFilterWidgets();
				
				// Force update of active filters display
				setTimeout(function() {
					updateActiveFiltersDisplay();
				}, 200); // Reduced from 500ms to 200ms
			}, 300); // Wait 300ms before executing
		}
		
		// Function to refresh filter widgets via AJAX
		// OPTIMIZATION: Disabled full page reload - widgets update automatically via WooCommerce
		// This was causing major performance issues by reloading entire page on every filter change
		function refreshFilterWidgets() {
			// Widgets are already updated by WooCommerce's AJAX filtering system
			// No need to reload the entire page - this was causing 2-5 second delays
			// Just trigger WooCommerce update event if needed
			$(document.body).trigger('updated_wc_div');
			return;
		}
		
		
	// Function to clean filter text
	function cleanFilterText(text) {
		if (!text) return '';
		
		// Remove trailing numbers
		text = text.replace(/\d+$/, '');
		
		// Remove word duplicates (e.g., "Blackberry2Blackberry2" -> "Blackberry2")
		// But preserve consecutive letters like "apple", "blackberry"
		text = text.replace(/(\w+)\1/g, '$1');
		
		// Capitalize first letter
		text = text.charAt(0).toUpperCase() + text.slice(1);
		
		return text.trim();
	}
	
	// Function to add active filter to the display
	function addActiveFilter(filterType, filterValue, filterText) {
		var $panelFilter = $('.filter-sidebar-panel');
		var $widgetFilter = $panelFilter.find('.products-filter__activated-items');
		
		if ($widgetFilter.length === 0) {
			// Create the activated items container if it doesn't exist
			$widgetFilter = $('<div class="products-filter__activated-items"></div>');
			$panelFilter.find('.panel__content').prepend($widgetFilter);
		}
		
		// Use the filter text directly (already cleaned from URL)
		var cleanText = filterText;
		
		// Create the active filter element
		var filterElement = '<a href="#" class="remove-filtered" data-filter-type="' + filterType + '" data-filter-value="' + filterValue + '" data-value="' + filterValue + '">' + 
			cleanText + ' <span class="shopwell-svg-icon">×</span></a>';
		
		// Add to the widget
		$widgetFilter.append(filterElement);
		
		// Update the display
		updateActiveFiltersDisplay();
	}
	
	// Function to load existing active filters from URL on page load
	shopwell.loadExistingActiveFilters = function() {
		var urlParams = new URLSearchParams(window.location.search);
		var $panelFilter = $('.filter-sidebar-panel');
		var $widgetFilter = $panelFilter.find('.products-filter__activated-items');
		
		// Clear existing filters
		if ($widgetFilter.length) {
			$widgetFilter.html('');
		}
		
		// Load category filter
		var category = urlParams.get('product_cat');
		if (category) {
			
			// Use the category slug from URL directly, just capitalize it
			var categoryName = category.charAt(0).toUpperCase() + category.slice(1);
			
			addActiveFilter('categorie', category, categoryName);
		}
		
		// Load color filter
		var color = urlParams.get('filter_pa_culoare');
		if (color) {
			// Use the color slug from URL directly, just capitalize it
			var colorName = color.charAt(0).toUpperCase() + color.slice(1);
			addActiveFilter('culoare', color, colorName);
		}
		
		// Load condition filter
		var condition = urlParams.get('filter_pa_stare');
		if (condition) {
			
			// Use the condition slug from URL directly, just capitalize it
			var conditionName = condition.charAt(0).toUpperCase() + condition.slice(1);
			
			addActiveFilter('stare', condition, conditionName);
		}
		
		// Load memory filter
		var memory = urlParams.get('filter_pa_memorie');
		if (memory) {
			
			// Use the memory slug from URL directly, just capitalize it
			var memoryName = memory.charAt(0).toUpperCase() + memory.slice(1);
			
			addActiveFilter('memorie', memory, memoryName);
		}
		
		// Load model filter
		var model = urlParams.get('filter_pa_model');
		if (model) {
			
			// Use the model slug from URL directly, just capitalize it
			var modelName = model.charAt(0).toUpperCase() + model.slice(1);
			
			addActiveFilter('model', model, modelName);
		}
		
		// Load price filter
		var minPrice = urlParams.get('min_price');
		var maxPrice = urlParams.get('max_price');
		if (minPrice || maxPrice) {
			
			// Format price range for display
			var priceText = '';
			if (minPrice && maxPrice) {
				priceText = minPrice + ' - ' + maxPrice + ' lei';
			} else if (minPrice) {
				priceText = 'De la ' + minPrice + ' lei';
			} else if (maxPrice) {
				priceText = 'Până la ' + maxPrice + ' lei';
			}
			
			addActiveFilter('pret', minPrice + '-' + maxPrice, priceText);
		}
	}
	
		
		// Hide Clear and Apply buttons completely
		$('.products-filter__button .reset-button, .products-filter__button .filter-button').hide();
		
		// Add CSS to hide buttons permanently
		$('<style>')
			.prop('type', 'text/css')
			.html(`
				.products-filter__button .reset-button,
				.products-filter__button .filter-button,
				.woocommerce-widget-price-filter button[type="submit"],
				.woocommerce-widget-price-filter .button {
					display: none !important;
				}
			`)
			.appendTo('head');
		
		// Auto-apply filters on interaction - more specific selectors
		shopwell.$body.on('click', '.woocommerce-widget-layered-nav-list a', function(e) {
			var href = $(this).attr('href');
			
			if (href && href.indexOf('product_cat=') !== -1) {
				// Navigate to filtered URL immediately
				window.location.href = href;
				return false;
			}
		});

		// Also handle direct filter links
		shopwell.$body.on('click', 'a[href*="filter_"], a[href*="pa_"], a[href*="product_cat="]', function(e) {
			var href = $(this).attr('href');
			
			if (href) {
				window.location.href = href;
				return false;
			}
		});

		// General filter click handler - catch all filter interactions
		shopwell.$body.on('click', '.woocommerce-widget-layered-nav-list a, .woocommerce-widget-layered-nav-list__item a', function(e) {
			var href = $(this).attr('href');
			
			if (href && (href.indexOf('filter_') !== -1 || href.indexOf('pa_') !== -1 || href.indexOf('product_cat=') !== -1)) {
				e.preventDefault();
				window.location.href = href;
				
				return false;
			}
		});

		// Handle category filters (products-filter__option filter-list-item)
		shopwell.$body.on('click', '.products-filter__option.filter-list-item', function(e) {
			var $this = $(this);
			var href = $this.attr('href');
			var dataValue = $this.data('value');
			var dataSlug = $this.data('slug');
			var text = $this.text().trim();
			
			// Try to find the link inside this element
			var $link = $this.find('a');
			if ($link.length) {
				href = $link.attr('href');
			}
			
			if (href) {
				e.preventDefault();
				window.location.href = href;
				return false;
			} else if (dataValue || dataSlug) {
				// Build URL manually if we have data attributes
				var currentUrl = new URL(window.location.href);
				var params = new URLSearchParams(currentUrl.search);
				
				// Clean only the category filter type
				params = cleanSpecificFilterType(params, 'categorie');
				
				// Set the new category
				if (dataSlug) {
					params.set('product_cat', dataSlug);
				} else if (dataValue) {
					params.set('product_cat', dataValue);
				}
				
				// Get the category text for display
				var categoryText = cleanFilterText(text || dataValue || dataSlug);
				
				// Add to active filters display
				addActiveFilter('categorie', dataValue || dataSlug, categoryText);
				
				currentUrl.search = params.toString();
				currentUrl.searchParams.set('paged', '1'); // Reset to first page
				
				// Update model filter visibility before navigation
				updateModelFilterVisibility();
				
				window.location.href = currentUrl.toString();
				
				return false;
			}
		});
		
		// Function to update model filter visibility based on category selection
		function updateModelFilterVisibility() {
			var $modelWidgets = $('.shopwell-model-filter-widget');
			
			if ($modelWidgets.length === 0) {
				// Also try to find model widgets by checking for model-related attributes
				$modelWidgets = $('.widget').filter(function() {
					var $widget = $(this);
					var widgetId = $widget.attr('id') || '';
					var widgetClass = $widget.attr('class') || '';
					return widgetId.toLowerCase().indexOf('model') !== -1 || 
						   widgetId.toLowerCase().indexOf('marca') !== -1 ||
						   widgetClass.toLowerCase().indexOf('model') !== -1 ||
						   widgetClass.toLowerCase().indexOf('marca') !== -1;
				});
			}
			
			// Also find model filter elements by class
			var $modelFilters = $('.filter_model, .filter.filter_model, .products-filter__filter.filter_model, [class*="filter_model"]');
			
			// Check if a category is selected - ONLY check URL parameter product_cat
			var selectedCategory = null;
			var urlParams = new URLSearchParams(window.location.search);
			
			// Only check URL parameter, not page type
			if (urlParams.has('product_cat')) {
				selectedCategory = urlParams.get('product_cat');
			}
			
			if (selectedCategory) {
				// Show model widgets and all their options
				if ($modelWidgets.length > 0) {
					$modelWidgets.show();
					$modelWidgets.find('.products-filter__option').show();
					$modelWidgets.attr('data-category', selectedCategory);
				}
				
				// Show model filter elements
				$modelFilters.show();
				$modelFilters.find('.products-filter__option').show();
				
			} else {
				// Hide model widgets completely
				if ($modelWidgets.length > 0) {
					$modelWidgets.hide();
					$modelWidgets.find('.products-filter__option').hide();
				}
				
				// Hide model filter elements
				$modelFilters.hide();
				$modelFilters.find('.products-filter__option').hide();
				
			}
		}
		
		// Run on page load
		setTimeout(function() {
			updateModelFilterVisibility();
		}, 200);
		
		// Run when filters are updated
		shopwell.$body.on('shopwell_products_filter_widget_updated', function() {
			setTimeout(updateModelFilterVisibility, 100);
		});
		
		// Also run when URL changes (for browser navigation)
		$(window).on('popstate', function() {
			setTimeout(updateModelFilterVisibility, 100);
		});
		
		// Monitor for any navigation that might change the category
		var originalPushState = history.pushState;
		history.pushState = function() {
			originalPushState.apply(history, arguments);
			setTimeout(updateModelFilterVisibility, 100);
		};

		// Handle model filters FIRST - check if element is inside model widget by checking parent widgets
		// Use a more general selector and check for model widget inside
		shopwell.$body.on('click', '.products-filter__option', function(e) {
			var $this = $(this);
			
			// Check if this element is inside a model filter widget
			var $modelWidget = $this.closest('.shopwell-model-filter-widget, [class*="model"], [id*="model"]');
			
			// Also check if parent widget has model-related classes or if it's a model attribute widget
			if ($modelWidget.length === 0) {
				// Check parent widgets for model-related attributes
				var $parentWidget = $this.closest('.widget');
				if ($parentWidget.length) {
					var widgetId = $parentWidget.attr('id') || '';
					var widgetClass = $parentWidget.attr('class') || '';
					
					// Check if widget ID or class contains "model" or "marca"
					if (widgetId.toLowerCase().indexOf('model') !== -1 || 
						widgetId.toLowerCase().indexOf('marca') !== -1 ||
						widgetClass.toLowerCase().indexOf('model') !== -1 ||
						widgetClass.toLowerCase().indexOf('marca') !== -1) {
						$modelWidget = $parentWidget;
					}
				}
			}
			
			// If not a model widget, let other handlers process it
			if ($modelWidget.length === 0) {
				return;
			}
			
			// This is a model filter - handle it
			var href = $this.attr('href');
			var dataValue = $this.data('value');
			var dataSlug = $this.data('slug');
			var text = $this.text().trim();
			
			// Stop event propagation to prevent other handlers from running
			e.stopImmediatePropagation();
			e.preventDefault();
			
			// Try to find the link inside this element
			var $link = $this.find('a');
			if ($link.length) {
				href = $link.attr('href');
			}
			
			if (href) {
				// Parse the href and replace filter_pa_culoare with filter_pa_model if present
				try {
					var url = new URL(href, window.location.origin);
					var params = new URLSearchParams(url.search);
					
					// Check if it has filter_pa_culoare (wrong filter type)
					if (params.has('filter_pa_culoare')) {
						var value = params.get('filter_pa_culoare');
						params.delete('filter_pa_culoare');
						params.set('filter_pa_model', value);
						url.search = params.toString();
						href = url.toString();
					}
					// If it doesn't have filter_pa_model but we have dataValue/dataSlug, add it
					else if (!params.has('filter_pa_model') && (dataValue || dataSlug)) {
						params.set('filter_pa_model', dataSlug || dataValue);
						url.search = params.toString();
						href = url.toString();
					}
				} catch (err) {
				}
				
				window.location.href = href;
				return false;
			} else if (dataValue || dataSlug) {
				// Build URL manually if we have data attributes
				var currentUrl = new URL(window.location.href);
				var params = new URLSearchParams(currentUrl.search);
				
				// Clean only the model filter type
				params = cleanSpecificFilterType(params, 'model');
				
				// Set the new model filter
				if (dataSlug) {
					params.set('filter_pa_model', dataSlug);
				} else if (dataValue) {
					params.set('filter_pa_model', dataValue);
				}
				
				// Get the model text for display
				var modelText = cleanFilterText(text || dataValue || dataSlug);
				
				// Add to active filters display
				addActiveFilter('model', dataValue || dataSlug, modelText);
				
				currentUrl.search = params.toString();
				currentUrl.searchParams.set('paged', '1'); // Reset to first page		
				window.location.href = currentUrl.toString();
				
				return false;
			}
		});

		// Handle memory filters SECOND (products-filter__option swatch swatch-button with memory) - must be before color handler
		shopwell.$body.on('click', '.products-filter__option.swatch.swatch-button', function(e) {
			var $this = $(this);
			var href = $this.attr('href');
			var dataValue = $this.data('value');
			var dataSlug = $this.data('slug');
			var classes = $this.attr('class');
			var text = $this.text().trim();
			
			// Check if this is a memory filter by checking data-value, data-slug, or classes
			var isMemory = false;
			if (dataValue && (dataValue.includes('gb') || dataValue.includes('tb'))) {
				isMemory = true;
			} else if (dataSlug && (dataSlug.includes('gb') || dataSlug.includes('tb'))) {
				isMemory = true;
			} else if (classes && (classes.includes('gb') || classes.includes('tb'))) {
				isMemory = true;
			}
			
			// If not a memory filter, skip (let color handler process it)
			if (!isMemory) {
				return;
			}
			
			// Stop event propagation to prevent color handler from running
			e.stopImmediatePropagation();

			// Try to find the link inside this element
			var $link = $this.find('a');
			if ($link.length) {
				href = $link.attr('href');
			}
			
			if (href) {
				e.preventDefault();
				window.location.href = href;
				return false;
			} else if (dataValue || dataSlug || classes.includes('swatch-')) {
				// Build URL manually if we have data attributes or classes
				var currentUrl = new URL(window.location.href);
				var params = new URLSearchParams(currentUrl.search);
				
				// Use the smart filter function to get filter type
				var filterResult = setFilterParameter(params, classes, dataValue, dataSlug, $this);
				
				if (filterResult.success) {
					// Clean only the specific filter type that's being replaced
					params = cleanSpecificFilterType(params, filterResult.filterType);
					
					// Re-apply the filter parameter
					if (filterResult.filterType === 'memorie') {
						params.set('filter_pa_memorie', dataValue || dataSlug);
					}
					
					// Get the filter text for display
					var filterText = cleanFilterText(text || dataValue || dataSlug);
					
					// Add to active filters display
					addActiveFilter('memorie', dataValue || dataSlug, filterText);
					
					currentUrl.search = params.toString();
					currentUrl.searchParams.set('paged', '1'); // Reset to first page
					window.location.href = currentUrl.toString();
					
					return false;
				}
			}
		});

		// Handle color filters THIRD (products-filter__option swatch swatch-button) - excludes memory and model filters
		shopwell.$body.on('click', '.products-filter__option.swatch.swatch-button', function(e) {
			var $this = $(this);
			
			// Check if this is a model filter (inside model widget) - MUST CHECK FIRST
			var $modelWidget = $this.closest('.shopwell-model-filter-widget');
			var isModel = $modelWidget.length > 0;
			
			if (isModel) {
				return;
			}
			
			// Skip if already handled by memory filter handler
			// (memory handler is registered first, so if it's memory it will have stopPropagation)
			var dataValue = $this.data('value');
			var dataSlug = $this.data('slug');
			var classes = $this.attr('class');
			
			// Check if this is a memory filter
			var isMemory = false;
			if (dataValue && (dataValue.includes('gb') || dataValue.includes('tb'))) {
				isMemory = true;
			} else if (dataSlug && (dataSlug.includes('gb') || dataSlug.includes('tb'))) {
				isMemory = true;
			} else if (classes && (classes.includes('gb') || classes.includes('tb'))) {
				isMemory = true;
			}
			
			// If it's a memory filter, skip
			if (isMemory) {
				return;
			}
			
			// Continue with color filter logic (rest of the handler code)
			var href = $this.attr('href');
			var text = $this.text().trim();
			
			// Try to find the link inside this element
			var $link = $this.find('a');
			if ($link.length) {
				href = $link.attr('href');
			}
			
			if (href) {
				e.preventDefault();
				window.location.href = href;
				return false;
			} else if (dataValue || dataSlug || classes.includes('swatch-')) {
				// Build URL manually if we have data attributes or classes
				var currentUrl = new URL(window.location.href);
				var params = new URLSearchParams(currentUrl.search);
				
				// Use the smart filter function to get filter type
				var filterResult = setFilterParameter(params, classes, dataValue, dataSlug, $this);
				
				if (filterResult.success) {
					// Clean only the specific filter type that's being replaced
					params = cleanSpecificFilterType(params, filterResult.filterType);
					
					// Re-apply the filter parameter
					if (filterResult.filterType === 'stare') {
						params.set('filter_pa_stare', dataValue || dataSlug);
					} else if (filterResult.filterType === 'culoare') {
						params.set('filter_pa_culoare', dataValue || dataSlug);
					} else if (filterResult.filterType === 'memorie') {
						params.set('filter_pa_memorie', dataValue || dataSlug);
					} else if (filterResult.filterType === 'model') {
						params.set('filter_pa_model', dataValue || dataSlug);
					}
					
					// Get the filter text for display - clean it up
					var filterText = cleanFilterText(text || dataValue || dataSlug || 'Filter');
					
					// Add to active filters display
					addActiveFilter(filterResult.filterType, dataValue || dataSlug || 'unknown', filterText);
					
					currentUrl.search = params.toString();
					currentUrl.searchParams.set('paged', '1'); // Reset to first page
					window.location.href = currentUrl.toString();
					
					return false;
				}
			}
		});

		// Note: Condition filters are now handled by the general swatch filter handler above

		// General filter click handler for all products-filter__option elements
		// Exclude model filters (handled separately above)
		shopwell.$body.on('click', '.products-filter__option', function(e) {
			var $this = $(this);
			
			// Skip if this is a model filter (already handled above)
			if ($this.closest('.shopwell-model-filter-widget').length > 0) {
				return;
			}
			
			var href = $this.attr('href');
			var classes = $this.attr('class');
			
			if (href) {
				e.preventDefault();
				window.location.href = href;
				
				return false;
			}
		});

		

		// Auto-apply attribute filters on change
		shopwell.$body.on('change', '.woocommerce-widget-layered-nav-list input[type="checkbox"]', function(e) {
			var $this = $(this);
			var name = $this.attr('name');
			var value = $this.val();
			var checked = $this.is(':checked');
			
			// Build URL with current filter state
			var currentUrl = new URL(window.location.href);
			var params = new URLSearchParams(currentUrl.search);
			
			if (checked) {
				params.set(name, value);
			} else {
				params.delete(name);
			}
			
			currentUrl.search = params.toString();
			currentUrl.searchParams.set('paged', '1'); // Reset to first page
			
			// Navigate to filtered URL immediately
			window.location.href = currentUrl.toString();
		});

		// Also handle checkboxes in other locations
		shopwell.$body.on('change', 'input[type="checkbox"][name*="filter_"], input[type="checkbox"][name*="pa_"]', function(e) {
			var $this = $(this);
			var name = $this.attr('name');
			var value = $this.val();
			var checked = $this.is(':checked');
			
			// Build URL with current filter state
			var currentUrl = new URL(window.location.href);
			var params = new URLSearchParams(currentUrl.search);
			
			if (checked) {
				params.set(name, value);
			} else {
				params.delete(name);
			}
			
			currentUrl.search = params.toString();
			currentUrl.searchParams.set('paged', '1'); // Reset to first page
			
			// Navigate to filtered URL immediately
			window.location.href = currentUrl.toString();
		});

		// Auto-apply radio button filters
		shopwell.$body.on('change', '.woocommerce-widget-layered-nav-list input[type="radio"]', function(e) {
			var $this = $(this);
			var name = $this.attr('name');
			var value = $this.val();
			
			// Build URL with current filter state
			var currentUrl = new URL(window.location.href);
			var params = new URLSearchParams(currentUrl.search);
			
			if (value) {
				params.set(name, value);
			} else {
				params.delete(name);
			}
			
			currentUrl.search = params.toString();
			currentUrl.searchParams.set('paged', '1'); // Reset to first page
			
			// Navigate to filtered URL immediately
			window.location.href = currentUrl.toString();
		});

		// Auto-apply select filters
		shopwell.$body.on('change', '.woocommerce-widget-layered-nav-list select', function(e) {
			var $this = $(this);
			var name = $this.attr('name');
			var value = $this.val();
			
			// Build URL with current filter state
			var currentUrl = new URL(window.location.href);
			var params = new URLSearchParams(currentUrl.search);
			
			if (value && value !== '') {
				params.set(name, value);
			} else {
				params.delete(name);
			}
			
			currentUrl.search = params.toString();
			currentUrl.searchParams.set('paged', '1'); // Reset to first page
			
			// Navigate to filtered URL immediately
			window.location.href = currentUrl.toString();
		});

		// Auto-apply color/attribute button filters
		shopwell.$body.on('click', '.woocommerce-widget-layered-nav-list .woocommerce-widget-layered-nav-list__item a', function(e) {
			e.preventDefault();
			var $this = $(this);
			var href = $this.attr('href');
			
			if (href) {
				// Navigate to filtered URL immediately
				window.location.href = href;
			}
		});

		// Auto-apply price filter changes
		shopwell.$body.on('input change', '.woocommerce-widget-price-filter input[name="min_price"], .woocommerce-widget-price-filter input[name="max_price"]', function(e) {
			var $this = $(this);
			var $form = $this.closest('form');
			var minPrice = $form.find('input[name="min_price"]').val();
			var maxPrice = $form.find('input[name="max_price"]').val();
			
			// Debounce the price filter application
			clearTimeout(shopwell.priceFilterTimeout);
			shopwell.priceFilterTimeout = setTimeout(function() {
				// Build URL with price filters
				var currentUrl = new URL(window.location.href);
				var params = new URLSearchParams(currentUrl.search);
				
				if (minPrice && minPrice !== '0') {
					params.set('min_price', minPrice);
				} else {
					params.delete('min_price');
				}
				
				if (maxPrice && maxPrice !== '0') {
					params.set('max_price', maxPrice);
				} else {
					params.delete('max_price');
				}
				
				currentUrl.search = params.toString();
				currentUrl.searchParams.set('paged', '1'); // Reset to first page
				
				// Navigate to filtered URL immediately
				window.location.href = currentUrl.toString();
			}, 500); // 500ms debounce
		});

		// Auto-apply when price slider changes
		shopwell.$body.on('slidechange', '.price_slider', function(e, ui) {
			// Build URL with price filters
			var currentUrl = new URL(window.location.href);
			var params = new URLSearchParams(currentUrl.search);
			
			params.set('min_price', ui.values[0]);
			params.set('max_price', ui.values[1]);
			
			currentUrl.search = params.toString();
			currentUrl.searchParams.set('paged', '1'); // Reset to first page
			
			// Navigate to filtered URL immediately
			window.location.href = currentUrl.toString();
		});
		
		// Note: Load existing active filters is called in document ready with setTimeout below
	};


	shopwell.postsFound = function () {
		var $found = $( '.shopwell-posts-found__inner' ),
			$foundEls = $found.find( '.count-bar' ),
			$current = $found.find( '.current-post' ).html(),
			$total = $found.find( '.found-post' ).html(),
			pecent = ($current / $total) * 100;

		$foundEls.css( 'width', pecent + '%' );
	};

	shopwell.catalogToolBar = function () {
        var $selector = $('#mobile-filter-sidebar-panel');

        if ($selector.length < 1) {
            return;
        }

        var resizeTimeout;
        shopwell.$window.on('resize', function () {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(function() {
                if (shopwell.$window.width() > 991) {
                    if ($selector.hasClass('offscreen-panel')) {
                        $selector.removeClass('offscreen-panel offscreen-panel--side-left').removeAttr('style');
                    }
                } else {
                    $selector.addClass('offscreen-panel offscreen-panel--side-left');
                }
            }, 100);
        }).trigger('resize');

		// Add count filter activated
		var item = $selector.find('.products-filter__activated-items > a').length;

		if ( item > 0 ) {
			$('.mobile-catalog-toolbar__filter-button').append('<span class="count">(' + item + ')</span>');
		}

    };

	shopwell.catalogOrderBy = function () {
		var $selector = $('#mobile-orderby-modal'),
			$orderForm = $('.catalog-toolbar__toolbar .woocommerce-ordering, .catalog-toolbar--top .woocommerce-ordering');

		$selector.find('.mobile-orderby-list').on('click', 'a', function (e) {
            e.preventDefault();

			var value = $(this).data('id'),
				title = $(this).data('title');

			// Click selectd item popup order list
			$selector.find('.mobile-orderby-list .selected').removeClass('selected');
			$(this).addClass( 'selected' );

			// Change text button sort by
			$('.mobile-catalog-toolbar__sort-button .name').html(title);

			// Select content form order
			$orderForm.find('option:selected').attr("selected", false);
			$orderForm.find('option[value='+ value +']').attr("selected", "selected");

			$orderForm.trigger( 'submit' );
        });

		// Active Item
		var activeName = $orderForm.find('option:selected').text(),
			activeVal = $orderForm.find('option:selected').val();

		$('.mobile-catalog-toolbar__sort-button .name').html(activeName);
		$selector.find('.mobile-orderby-list a[data-id='+ activeVal +']').addClass('selected');

    };

	shopwell.scrollFilterSidebar = function () {
        shopwell.$body.on('shopwell_products_filter_before_send_request', function () {
            if( ! $(".woocommerce-shop .content-area").length ) {
                return;
            }

			var $height = 0;

			var resizeTimeout2;
			shopwell.$window.on( 'resize', function () {
				clearTimeout(resizeTimeout2);
				resizeTimeout2 = setTimeout(function() {
					if ( shopwell.$window.width() < 991 ) {
						$( '#mobile-filter-sidebar-panel' ).removeClass( 'offscreen-panel--open' ).fadeOut();
					} else {
						var $sticky 	= $( document.body ).hasClass('shopwell-header-sticky') ? $( '#site-header .header-sticky' ).outerHeight() : 0,
							$wpadminbar = $('#wpadminbar').is(":visible") ? $('#wpadminbar').height() : 0;

							$height 	= $sticky + $wpadminbar;
					}
				}, 100);
			}).trigger( 'resize' );

			$( document.body ).removeAttr('style');
			$( document.body ).removeClass( 'offcanvas-opened' );

            $('html,body').stop().animate({
                    scrollTop: $(".woocommerce-shop .content-area").offset().top - $height
                },
                'slow');
        });
    };

	shopwell.stickySidebar = function() {
        if( ! $.fn.stick_in_parent ) {
            return;
        }

        var offset_top = 0;

        if( shopwell.$body.hasClass('admin-bar') ) {
            offset_top += 32;
        }

        if( shopwell.$header.find('.site-header__section').hasClass('shopwell-header-sticky') ) {
            offset_top +=shopwell.$header.find('.header-sticky').height();
        }

        var resizeTimeout3;
        shopwell.$window.on('resize', function () {
            clearTimeout(resizeTimeout3);
            resizeTimeout3 = setTimeout(function() {
                if (shopwell.$window.width() < 992) {
                    $( '#mobile-filter-sidebar-panel' ).trigger("sticky_kit:detach");
                } else {
                    $( '#mobile-filter-sidebar-panel' ).stick_in_parent({
                        offset_top: offset_top,
    					inner_scrolling: false
                    });
                }
            }, 100);
        }).trigger('resize');
    }

    /**
     * Initialize price filter slider
     */
    shopwell.priceFilterSlider = function() {
        var $form = $('.woocommerce-widget-price-filter form');
        var $minPrice = $form.find('input[name="min_price"]');
        var $maxPrice = $form.find('input[name="max_price"]');
        var $priceSlider = $('.price_slider');
        
        if ($minPrice.length === 0 || $maxPrice.length === 0) {
            return;
        }
        
        var originalMinPrice = parseFloat($minPrice.data('min')) || 0;
        var originalMaxPrice = parseFloat($maxPrice.data('max')) || 0;
        
        var currentMinPrice = parseFloat($minPrice.val());
        var currentMaxPrice = parseFloat($maxPrice.val());
        
        // If no filters applied or values are 0, use the full range
        if (!currentMinPrice || currentMinPrice === 0) {
            currentMinPrice = originalMinPrice;
        }
        if (!currentMaxPrice || currentMaxPrice === 0) {
            currentMaxPrice = originalMaxPrice;
        }
        
        // Track if price range has been modified from original
        var priceModified = false;
        
        // Initialize price slider if it exists
        if ($priceSlider.length && typeof $.fn.slider !== 'undefined') {
            $priceSlider.slider({
                range: true,
                min: originalMinPrice,
                max: originalMaxPrice,
                values: [currentMinPrice, currentMaxPrice],
                step: 1,
                slide: function(event, ui) {
                    $minPrice.val(ui.values[0]);
                    $maxPrice.val(ui.values[1]);
                    priceModified = true;
                },
                stop: function(event, ui) {
                    // Auto-apply filter when slider stops
                    applyPriceFilter();
                }
            });
        }
        
        // Auto-apply filter on input change with debounce
        var priceTimeout;
        $minPrice.add($maxPrice).on('input', function() {
            clearTimeout(priceTimeout);
            priceTimeout = setTimeout(function() {
                var minVal = parseFloat($minPrice.val()) || originalMinPrice;
                var maxVal = parseFloat($maxPrice.val()) || originalMaxPrice;
                
                // Check if price range has actually changed from original
                if (minVal !== originalMinPrice || maxVal !== originalMaxPrice) {
                    priceModified = true;
                    applyPriceFilter();
                } else if (priceModified) {
                    // If user resets to original values, remove price filter
                    removePriceFilter();
                }
            }, 500); // 500ms debounce
        });
        
        function applyPriceFilter() {
            var minVal = parseFloat($minPrice.val()) || originalMinPrice;
            var maxVal = parseFloat($maxPrice.val()) || originalMaxPrice;
            
            // Validate inputs
            if (minVal > maxVal && maxVal > originalMinPrice) {
                minVal = maxVal;
                $minPrice.val(minVal);
            }
            
            if (maxVal < minVal && minVal < originalMaxPrice) {
                maxVal = minVal;
                $maxPrice.val(maxVal);
            }
            
            // Only apply if values are different from original range
            if (minVal !== originalMinPrice || maxVal !== originalMaxPrice) {
                // Build URL with price filters
                var currentUrl = new URL(window.location.href);
                currentUrl.searchParams.set('min_price', minVal);
                currentUrl.searchParams.set('max_price', maxVal);
                currentUrl.searchParams.set('paged', '1'); // Reset to first page
                
                // Navigate to filtered URL
                window.location.href = currentUrl.toString();
            }
        }
        
        function removePriceFilter() {
            // Build URL without price filters
            var currentUrl = new URL(window.location.href);
            currentUrl.searchParams.delete('min_price');
            currentUrl.searchParams.delete('max_price');
            currentUrl.searchParams.set('paged', '1'); // Reset to first page
            
            // Navigate to clean URL
            window.location.href = currentUrl.toString();
        }
        
        // Update slider when inputs change
        $minPrice.add($maxPrice).on('change', function() {
            if ($priceSlider.length && typeof $.fn.slider !== 'undefined') {
                var minVal = parseFloat($minPrice.val()) || originalMinPrice;
                var maxVal = parseFloat($maxPrice.val()) || originalMaxPrice;
                $priceSlider.slider('values', [minVal, maxVal]);
            }
        });
    };


    /**
     * Document ready
     */
    $(function () {
        shopwell.init();
        
        // Load existing active filters from URL on page load
        // Use setTimeout to ensure DOM is fully ready
        setTimeout(function() {
            if (typeof shopwell.loadExistingActiveFilters === 'function') {
                shopwell.loadExistingActiveFilters();
            } else {
                console.error('❌ shopwell.loadExistingActiveFilters is not defined');
            }
        }, 100);
    });

	/**
	 * Apply color swatches to filter sidebar
	 * Reuses the color dictionary from single-product.js
	 * 
	 * @since 1.0.0
	 */
	/**
	 * Sort color filters alphabetically
	 * 
	 * @since 1.0.0
	 */
	shopwell.sortColorFiltersAlphabetically = function() {
		$('.products-filter--swatches.swatches-button').each(function() {
			var $container = $(this);
			var $buttons = $container.find('.products-filter__option.swatch.swatch-button').get();
			
			// Sort buttons alphabetically by their text content
			$buttons.sort(function(a, b) {
				var textA = $(a).text().trim().toLowerCase();
				var textB = $(b).text().trim().toLowerCase();
				
				// Remove numbers in parentheses for sorting
				textA = textA.replace(/\s*\(\d+\)\s*$/, '');
				textB = textB.replace(/\s*\(\d+\)\s*$/, '');
				
				return textA.localeCompare(textB, 'ro');
			});
			
			// Reorder the buttons in the container
			$.each($buttons, function(idx, button) {
				$container.append(button);
			});
		});
	};
	
	shopwell.applyFilterColorSwatches = function() {
		// Define helper functions if not already available from single-product.js
		if (typeof shopwell.getColorByName !== 'function') {
			// Color map for Romanian color names (with 20% opacity)
			var colorMap = {
				'alb': 'rgba(255, 255, 255, 0.2)',
				'albastru': 'rgba(0, 0, 255, 0.2)',
				'albastru aura': 'rgba(173, 216, 230, 0.2)',
				'albastru-aura': 'rgba(173, 216, 230, 0.2)',
				'albastru gheață': 'rgba(224, 255, 255, 0.2)',
				'albastru gheata': 'rgba(224, 255, 255, 0.2)',
				'albastru-gheata': 'rgba(224, 255, 255, 0.2)',
				'amurg': 'rgba(106, 90, 205, 0.2)',
				'argintiu': 'rgba(192, 192, 192, 0.2)',
				'auriu': 'rgba(255, 215, 0, 0.2)',
				'auriu roz': 'rgba(183, 110, 121, 0.2)',
				'auriu-roz': 'rgba(183, 110, 121, 0.2)',
				'bej': 'rgba(245, 245, 220, 0.2)',
				'bronz': 'rgba(205, 127, 50, 0.2)',
				'galben': 'rgba(255, 255, 0, 0.2)',
				'gri': 'rgba(128, 128, 128, 0.2)',
				'maro': 'rgba(165, 42, 42, 0.2)',
				'negru': 'rgba(0, 0, 0, 0.2)',
				'portocaliu': 'rgba(255, 165, 0, 0.2)',
				'roșu': 'rgba(255, 0, 0, 0.2)',
				'rosu': 'rgba(255, 0, 0, 0.2)',
				'roz': 'rgba(255, 192, 203, 0.2)',
				'transparent': 'rgba(245, 245, 245, 0.2)',
				'turcoaz': 'rgba(64, 224, 208, 0.2)',
				'verde': 'rgba(0, 128, 0, 0.2)',
				'violet': 'rgba(238, 130, 238, 0.2)',
				'violetă': 'rgba(238, 130, 238, 0.2)',
				'violete': 'rgba(238, 130, 238, 0.2)'
			};
			
			function normalizeText(text) {
				if (!text) return '';
				return text.toLowerCase().trim().replace(/[ăâîșțşţ]/g, function(match) {
					var map = {'ă': 'a', 'â': 'a', 'î': 'i', 'ș': 's', 'ş': 's', 'ț': 't', 'ţ': 't'};
					return map[match] || match;
				}).replace(/[^\w\s]/g, '').replace(/\s+/g, ' ');
			}
			
			shopwell.getColorByName = function(colorName) {
				if (!colorName) return null;
				
				var normalized = normalizeText(colorName);
				var lowerName = colorName.toLowerCase().trim();
				
				// Try exact match first
				if (colorMap[lowerName]) {
					return colorMap[lowerName];
				}
				
				// Try normalized match
				for (var key in colorMap) {
					if (normalizeText(key) === normalized) {
						return colorMap[key];
					}
				}
				
				// Try partial match (for first color in multi-color names)
				if (lowerName.indexOf('-') !== -1) {
					var firstColor = lowerName.split('-')[0];
					if (colorMap[firstColor]) {
						return colorMap[firstColor];
					}
				}
				
				return null;
			};
			
			shopwell.isDarkColor = function(color) {
				var r, g, b;
				
				// Handle rgba format
				if (color.indexOf('rgba') === 0) {
					var match = color.match(/rgba?\((\d+),\s*(\d+),\s*(\d+)/);
					if (match) {
						r = parseInt(match[1]);
						g = parseInt(match[2]);
						b = parseInt(match[3]);
					}
				} 
				// Handle hex format
				else {
					var hex = color.replace('#', '');
					r = parseInt(hex.substr(0, 2), 16);
					g = parseInt(hex.substr(2, 2), 16);
					b = parseInt(hex.substr(4, 2), 16);
				}
				
				var rsRGB = r / 255;
				var gsRGB = g / 255;
				var bsRGB = b / 255;
				var rLinear = rsRGB <= 0.03928 ? rsRGB / 12.92 : Math.pow((rsRGB + 0.055) / 1.055, 2.4);
				var gLinear = gsRGB <= 0.03928 ? gsRGB / 12.92 : Math.pow((gsRGB + 0.055) / 1.055, 2.4);
				var bLinear = bsRGB <= 0.03928 ? bsRGB / 12.92 : Math.pow((bsRGB + 0.055) / 1.055, 2.4);
				var luminance = 0.2126 * rLinear + 0.7152 * gLinear + 0.0722 * bLinear;
				return luminance < 0.5;
			};
		}
		
		// Wait for DOM to be ready
		setTimeout(function() {
			// Target filter buttons with color classes: .products-filter__option.swatch.swatch-button
			$('.products-filter__option.swatch.swatch-button').each(function() {
				var $button = $(this);
				var colorName = '';
				
				// Method 1: Extract color name from CSS class (e.g., swatch-alb, swatch-albastru)
				var classes = $button.attr('class') || '';
				var match = classes.match(/swatch-([a-zA-Z0-9ăâîșțşţ\-]+)/g);
				if (match && match.length > 2) {
					// match will have: swatch, swatch-button, swatch-{color}
					// We want the last one (the color)
					var colorClass = match[match.length - 1]; // e.g., "swatch-alb"
					colorName = colorClass.replace('swatch-', '').trim();
				}
				
				// Method 2: Get from data-value attribute (fallback)
				if (!colorName) {
					colorName = $button.attr('data-value') || $button.data('value') || '';
				}
				
				// Method 3: Get from text content (fallback)
				if (!colorName) {
					colorName = $button.text().trim();
					// Remove count if present, e.g., "Alb (105)" -> "Alb"
					colorName = colorName.replace(/\s*\(\d+\)\s*$/, '');
				}
				
				// Try to get color from the shared dictionary
				if (colorName && typeof shopwell.getColorByName === 'function') {
					var color = shopwell.getColorByName(colorName);
					if (color) {
					// Apply background color directly to button
					$button.css({
						'background-color': color,
						'border-color': color
					});
					
					// Since colors are at 20% opacity, always use dark text for better contrast
					$button.css('color', '#1d2128');
						
						// Mark as colored
						$button.attr('data-color-applied', 'true');
					}
				}
			});
			
			// Sort colors alphabetically after applying colors
			if (typeof shopwell.sortColorFiltersAlphabetically === 'function') {
				shopwell.sortColorFiltersAlphabetically();
			}
		}, 200);
		
		// Reapply when filters are updated via AJAX
		$(document.body).on('shopwell_ajax_filter_updated', function() {
			setTimeout(function() {
				shopwell.applyFilterColorSwatches();
			}, 300);
		});
	};

})(jQuery);