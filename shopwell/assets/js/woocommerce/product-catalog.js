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
            
            console.log('🗑️ Removing filter:', filterType, '=', filterValue, 'value:', value);
            
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
                console.log('🔄 Navigating to clean URL:', currentUrl.toString());
                window.location.href = currentUrl.toString();
            } else {
                console.log('⚠️ No matching parameter found to remove');
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
		console.log('🔧 Initializing auto-apply filters...');
		
		// Debug: Check what filter elements exist
		console.log('🔍 Found filter links:', $('.woocommerce-widget-layered-nav-list a').length);
		console.log('🔍 Found filter checkboxes:', $('.woocommerce-widget-layered-nav-list input[type="checkbox"]').length);
		console.log('🔍 Found filter buttons:', $('.products-filter__button .reset-button, .products-filter__button .filter-button').length);
		console.log('🔍 Found category filters:', $('.products-filter__option.filter-list-item').length);
		console.log('🔍 Found color filters:', $('.products-filter__option.swatch.swatch-button').length);
		console.log('🔍 Found condition filters:', $('.products-filter__option.swatch.swatch-button[class*="swatch-"]').length);
		
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
			}
			return params;
		}
		
		// Function to determine filter type and set appropriate parameter
		function setFilterParameter(params, classes, dataValue, dataSlug) {
			console.log('🔍 Analyzing filter:', {
				classes: classes,
				dataValue: dataValue,
				dataSlug: dataSlug
			});
			
			// Priority 1: Use data-value if available
			if (dataValue && dataValue !== 'button' && dataValue !== '') {
				console.log('🔍 USING dataValue:', dataValue, 'Type:', typeof dataValue, 'Length:', dataValue.length);
				console.log('🔍 Checking includes - gb:', dataValue.includes('gb'), 'tb:', dataValue.includes('tb'));
				console.log('🔍 String representation:', JSON.stringify(dataValue));
				
				// Check if it's a condition filter
				if (dataValue.includes('ca-nou') || dataValue.includes('excelent') || 
					dataValue.includes('foarte-bun') || dataValue.includes('bun')) {
					params.set('filter_pa_stare', dataValue);
					console.log('⭐ Setting condition filter from data-value:', dataValue);
					return { success: true, filterType: 'stare' };
				}
				// Check if it's a memory filter
				else if (dataValue.includes('gb') || dataValue.includes('tb')) {
					console.log('💾 MEMORY CONDITION MATCHED! dataValue:', dataValue);
					console.log('💾 FOUND MEMORY VALUE:', dataValue);
					params.set('filter_pa_memorie', dataValue);
					console.log('💾 Setting memory filter from data-value:', dataValue);
					return { success: true, filterType: 'memorie' };
				}
				// Check if it's a color filter (any color that's not a condition or memory)
				else if (!dataValue.includes('ca-nou') && !dataValue.includes('excelent') && 
					!dataValue.includes('foarte-bun') && !dataValue.includes('bun') &&
					!dataValue.includes('gb') && !dataValue.includes('tb') &&
					dataValue.length > 0) {
					console.log('🎨 COLOR CONDITION MATCHED! dataValue:', dataValue);
					params.set('filter_pa_culoare', dataValue);
					console.log('🎨 Setting color filter from data-value:', dataValue);
					return { success: true, filterType: 'culoare' };
				}
			}
			
			// Priority 2: Use data-slug if available
			if (dataSlug && dataSlug !== 'button' && dataSlug !== '') {
				// Check if it's a condition filter
				if (dataSlug.includes('ca-nou') || dataSlug.includes('excelent') || 
					dataSlug.includes('foarte-bun') || dataSlug.includes('bun')) {
					params.set('filter_pa_stare', dataSlug);
					console.log('⭐ Setting condition filter from data-slug:', dataSlug);
					return { success: true, filterType: 'stare' };
				}
				// Check if it's a memory filter
				else if (dataSlug.includes('gb') || dataSlug.includes('tb')) {
					params.set('filter_pa_memorie', dataSlug);
					console.log('💾 Setting memory filter from data-slug:', dataSlug);
					return { success: true, filterType: 'memorie' };
				}
				// Check if it's a color filter (any color that's not a condition or memory)
				else if (!dataSlug.includes('ca-nou') && !dataSlug.includes('excelent') && 
					!dataSlug.includes('foarte-bun') && !dataSlug.includes('bun') &&
					!dataSlug.includes('gb') && !dataSlug.includes('tb') &&
					dataSlug.length > 0) {
					params.set('filter_pa_culoare', dataSlug);
					console.log('🎨 Setting color filter from data-slug:', dataSlug);
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
					console.log('⭐ Setting condition filter from class:', conditionMatch[1]);
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
					console.log('💾 Setting memory filter from class:', memoryMatch[1]);
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
					console.log('🎨 Setting color filter from class:', colorMatch[1]);
					return { success: true, filterType: 'culoare' };
				}
			}
			
			console.log('⚠️ No valid filter value found');
			return { success: false, filterType: null };
		}
		
		// Function to update active filters display
		function updateActiveFiltersDisplay() {
			console.log('🔄 updateActiveFiltersDisplay called');
			var $primaryFilter = $('.catalog-toolbar__filters-actived');
			var $panelFilter = $('.filter-sidebar-panel');
			var $widgetFilter = $panelFilter.find('.products-filter__activated-items');
			
			console.log('🔍 Primary filter found:', $primaryFilter.length);
			console.log('🔍 Panel filter found:', $panelFilter.length);
			console.log('🔍 Widget filter found:', $widgetFilter.length);
			console.log('🔍 Widget content:', $widgetFilter.html());
			
			if ($primaryFilter.length && $widgetFilter.length) {
				if ($.trim($widgetFilter.html())) {
					console.log('🔧 Updating primary filter with content');
					$primaryFilter.html('');
					$primaryFilter.removeClass('active');
					$primaryFilter.prepend($widgetFilter.html() + '<a href="#" class="remove-filtered-all shopwell-button shopwell-button--subtle">Șterge tot</a>');
					$primaryFilter.addClass('active');
					console.log('✅ Primary filter updated and activated');
				} else {
					console.log('🔧 Clearing primary filter - no widget content');
					$primaryFilter.html('');
					$primaryFilter.removeClass('active');
				}
			} else {
				console.log('⚠️ Missing elements - Primary:', $primaryFilter.length, 'Widget:', $widgetFilter.length);
			}
		}
		
		// Function to refresh filter sidebar after any filter change
		function refreshFilterSidebar() {
			console.log('🔄 Refreshing filter sidebar...');
			
			// Force update of filter widgets via AJAX
			refreshFilterWidgets();
			
			// Force update of active filters display
			setTimeout(function() {
				updateActiveFiltersDisplay();
			}, 500);
		}
		
		// Function to refresh filter widgets via AJAX
		function refreshFilterWidgets() {
			console.log('🔄 Refreshing filter widgets via AJAX...');
			
			var $filterPanel = $('.filter-sidebar-panel');
			if ($filterPanel.length === 0) {
				console.log('⚠️ Filter panel not found');
				return;
			}
			
			// Get current URL parameters
			var currentUrl = new URL(window.location.href);
			var params = currentUrl.search;
			
			// Make AJAX request to refresh filter widgets
			$.ajax({
				url: window.location.href,
				type: 'GET',
				data: params + '&ajax=1',
				success: function(response) {
					console.log('✅ Filter widgets refreshed successfully');
					
					// Extract the filter panel content from the response
					var $response = $(response);
					var $newFilterPanel = $response.find('.filter-sidebar-panel');
					
					if ($newFilterPanel.length > 0) {
						// Update the filter panel content
						$filterPanel.html($newFilterPanel.html());
						
						// Re-initialize any JavaScript components
						$(document.body).trigger('updated_wc_div');
					}
				},
				error: function(xhr, status, error) {
					console.log('❌ Error refreshing filter widgets:', error);
					
					// Fallback: reload the page
					setTimeout(function() {
						window.location.reload();
					}, 1000);
				}
			});
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
		console.log('➕ addActiveFilter called with:', filterType, filterValue, filterText);
		var $panelFilter = $('.filter-sidebar-panel');
		var $widgetFilter = $panelFilter.find('.products-filter__activated-items');
		
		console.log('🔍 Panel filter found:', $panelFilter.length);
		console.log('🔍 Widget filter found:', $widgetFilter.length);
		
		if ($widgetFilter.length === 0) {
			// Create the activated items container if it doesn't exist
			console.log('🔧 Creating products-filter__activated-items container');
			$widgetFilter = $('<div class="products-filter__activated-items"></div>');
			$panelFilter.find('.panel__content').prepend($widgetFilter);
		}
		
		// Use the filter text directly (already cleaned from URL)
		var cleanText = filterText;
		
		// Create the active filter element
		var filterElement = '<a href="#" class="remove-filtered" data-filter-type="' + filterType + '" data-filter-value="' + filterValue + '" data-value="' + filterValue + '">' + 
			cleanText + ' <span class="shopwell-svg-icon">×</span></a>';
		
		console.log('🔧 Created filter element:', filterElement);
		
		// Add to the widget
		$widgetFilter.append(filterElement);
		
		console.log('🔧 Filter added to widget. Widget content:', $widgetFilter.html());
		
		// Update the display
		console.log('🔄 Calling updateActiveFiltersDisplay');
		updateActiveFiltersDisplay();
		console.log('✅ updateActiveFiltersDisplay completed');
	}
	
	// Function to load existing active filters from URL on page load
	shopwell.loadExistingActiveFilters = function() {
		console.log('🔄 Loading existing active filters from URL...');
		console.log('🔄 Current URL:', window.location.href);
		var urlParams = new URLSearchParams(window.location.search);
		var $panelFilter = $('.filter-sidebar-panel');
		var $widgetFilter = $panelFilter.find('.products-filter__activated-items');
		
		console.log('🔍 Found panel filter:', $panelFilter.length);
		console.log('🔍 Found widget filter:', $widgetFilter.length);
		console.log('🔍 All URL params:', Array.from(urlParams.entries()));
		
		// Clear existing filters
		if ($widgetFilter.length) {
			$widgetFilter.html('');
		}
		
		// Load category filter
		var category = urlParams.get('product_cat');
		if (category) {
			console.log('📂 Found category filter:', category);
			
			// Use the category slug from URL directly, just capitalize it
			var categoryName = category.charAt(0).toUpperCase() + category.slice(1);
			
			console.log('📂 Category name:', categoryName);
			addActiveFilter('categorie', category, categoryName);
		}
		
		// Load color filter
		var color = urlParams.get('filter_pa_culoare');
		if (color) {
			console.log('🎨 Found color filter:', color);
			
			// Use the color slug from URL directly, just capitalize it
			var colorName = color.charAt(0).toUpperCase() + color.slice(1);
			
			console.log('🎨 Color name:', colorName);
			addActiveFilter('culoare', color, colorName);
		}
		
		// Load condition filter
		var condition = urlParams.get('filter_pa_stare');
		if (condition) {
			console.log('⭐ Found condition filter:', condition);
			
			// Use the condition slug from URL directly, just capitalize it
			var conditionName = condition.charAt(0).toUpperCase() + condition.slice(1);
			
			console.log('⭐ Condition name:', conditionName);
			addActiveFilter('stare', condition, conditionName);
		}
		
		// Load memory filter
		var memory = urlParams.get('filter_pa_memorie');
		console.log('💾 Memory filter check:', memory);
		if (memory) {
			console.log('💾 Found memory filter:', memory);
			
			// Use the memory slug from URL directly, just capitalize it
			var memoryName = memory.charAt(0).toUpperCase() + memory.slice(1);
			
			console.log('💾 Memory name:', memoryName);
			console.log('💾 About to call addActiveFilter with:', 'memorie', memory, memoryName);
			addActiveFilter('memorie', memory, memoryName);
			console.log('💾 addActiveFilter called for memory');
		} else {
			console.log('💾 No memory filter found in URL');
		}
		
		// Load price filter
		var minPrice = urlParams.get('min_price');
		var maxPrice = urlParams.get('max_price');
		if (minPrice || maxPrice) {
			console.log('💰 Found price filter:', minPrice, '-', maxPrice);
			
			// Format price range for display
			var priceText = '';
			if (minPrice && maxPrice) {
				priceText = minPrice + ' - ' + maxPrice + ' lei';
			} else if (minPrice) {
				priceText = 'De la ' + minPrice + ' lei';
			} else if (maxPrice) {
				priceText = 'Până la ' + maxPrice + ' lei';
			}
			
			console.log('💰 Price text:', priceText);
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
			console.log('🔗 Category filter clicked, navigating to:', href);
			
			if (href && href.indexOf('product_cat=') !== -1) {
				// Navigate to filtered URL immediately
				window.location.href = href;
				return false;
			}
		});

		// Also handle direct filter links
		shopwell.$body.on('click', 'a[href*="filter_"], a[href*="pa_"], a[href*="product_cat="]', function(e) {
			var href = $(this).attr('href');
			console.log('🔗 Direct filter link clicked:', href);
			
			if (href) {
				window.location.href = href;
				return false;
			}
		});

		// General filter click handler - catch all filter interactions
		shopwell.$body.on('click', '.woocommerce-widget-layered-nav-list a, .woocommerce-widget-layered-nav-list__item a', function(e) {
			var href = $(this).attr('href');
			console.log('🔗 General filter link clicked:', href);
			
			if (href && (href.indexOf('filter_') !== -1 || href.indexOf('pa_') !== -1 || href.indexOf('product_cat=') !== -1)) {
				e.preventDefault();
				console.log('🔄 Preventing default and navigating to:', href);
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
			
			console.log('📂 Category filter clicked:', {
				href: href,
				dataValue: dataValue,
				dataSlug: dataSlug,
				text: text
			});
			
			// Try to find the link inside this element
			var $link = $this.find('a');
			if ($link.length) {
				href = $link.attr('href');
				console.log('📂 Found link inside:', href);
			}
			
			if (href) {
				e.preventDefault();
				console.log('🔄 Navigating to category:', href);
				window.location.href = href;
				return false;
			} else if (dataValue || dataSlug) {
				// Build URL manually if we have data attributes
				var currentUrl = new URL(window.location.href);
				var params = new URLSearchParams(currentUrl.search);
				
				console.log('🔄 Replacing category filter');
				console.log('🔄 Current URL params before:', Array.from(params.entries()));
				
				// Clean only the category filter type
				params = cleanSpecificFilterType(params, 'categorie');
				
				console.log('🔄 URL params after cleaning category:', Array.from(params.entries()));
				
				// Set the new category
				if (dataSlug) {
					params.set('product_cat', dataSlug);
				} else if (dataValue) {
					params.set('product_cat', dataValue);
				}
				
				console.log('🔄 URL params after setting new category:', Array.from(params.entries()));
				
				// Get the category text for display
				var categoryText = cleanFilterText(text || dataValue || dataSlug);
				
				// Add to active filters display
				addActiveFilter('categorie', dataValue || dataSlug, categoryText);
				
					currentUrl.search = params.toString();
					currentUrl.searchParams.set('paged', '1'); // Reset to first page
					console.log('🔄 Building category URL:', currentUrl.toString());
					window.location.href = currentUrl.toString();
					
					return false;
			}
		});

		// Handle memory filters FIRST (products-filter__option swatch swatch-button with memory) - must be before color handler
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
			
			console.log('💾 Memory swatch filter clicked:', {
				href: href,
				dataValue: dataValue,
				dataSlug: dataSlug,
				classes: classes,
				text: text
			});
			
			// Try to find the link inside this element
			var $link = $this.find('a');
			if ($link.length) {
				href = $link.attr('href');
				console.log('💾 Found link inside:', href);
			}
			
			if (href) {
				e.preventDefault();
				console.log('🔄 Navigating to memory filter:', href);
				window.location.href = href;
				return false;
			} else if (dataValue || dataSlug || classes.includes('swatch-')) {
				// Build URL manually if we have data attributes or classes
				var currentUrl = new URL(window.location.href);
				var params = new URLSearchParams(currentUrl.search);
				
				// Use the smart filter function to get filter type
				var filterResult = setFilterParameter(params, classes, dataValue, dataSlug);
				
				if (filterResult.success) {
					console.log('🔄 Replacing memory filter type:', filterResult.filterType);
					console.log('🔄 Current URL params before:', Array.from(params.entries()));
					
					// Clean only the specific filter type that's being replaced
					params = cleanSpecificFilterType(params, filterResult.filterType);
					
					console.log('🔄 URL params after cleaning:', Array.from(params.entries()));
					
					// Re-apply the filter parameter
					if (filterResult.filterType === 'memorie') {
						params.set('filter_pa_memorie', dataValue || dataSlug);
					}
					
					console.log('🔄 URL params after setting new filter:', Array.from(params.entries()));
					
					// Get the filter text for display
					var filterText = cleanFilterText(text || dataValue || dataSlug);
					
					// Add to active filters display
					addActiveFilter('memorie', dataValue || dataSlug, filterText);
					
					currentUrl.search = params.toString();
					currentUrl.searchParams.set('paged', '1'); // Reset to first page
					console.log('🔄 Building memory filter URL:', currentUrl.toString());
					window.location.href = currentUrl.toString();
					
					return false;
				}
			}
		});

		// Handle color filters SECOND (products-filter__option swatch swatch-button) - excludes memory filters
		shopwell.$body.on('click', '.products-filter__option.swatch.swatch-button', function(e) {
			var $this = $(this);
			
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
			
			// If it's a memory filter, skip (memory handler will process it first)
			if (isMemory) {
				console.log('🎨 Color handler skipping memory filter');
				return;
			}
			
			// Continue with color filter logic (rest of the handler code)
			var href = $this.attr('href');
			var text = $this.text().trim();
			
			console.log('🎨 Swatch filter clicked:', {
				href: href,
				dataValue: dataValue,
				dataSlug: dataSlug,
				classes: classes,
				text: text
			});
			
			// Try to find the link inside this element
			var $link = $this.find('a');
			if ($link.length) {
				href = $link.attr('href');
				console.log('🎨 Found link inside:', href);
			}
			
			if (href) {
				e.preventDefault();
				console.log('🔄 Navigating to filter:', href);
				window.location.href = href;
				return false;
			} else if (dataValue || dataSlug || classes.includes('swatch-')) {
				// Build URL manually if we have data attributes or classes
				var currentUrl = new URL(window.location.href);
				var params = new URLSearchParams(currentUrl.search);
				
				// Use the smart filter function to get filter type
				var filterResult = setFilterParameter(params, classes, dataValue, dataSlug);
				
				if (filterResult.success) {
					console.log('🔄 Replacing filter type:', filterResult.filterType);
					console.log('🔄 Current URL params before:', Array.from(params.entries()));
					
					// Clean only the specific filter type that's being replaced
					params = cleanSpecificFilterType(params, filterResult.filterType);
					
					console.log('🔄 URL params after cleaning:', Array.from(params.entries()));
					
					// Re-apply the filter parameter
					if (filterResult.filterType === 'stare') {
						params.set('filter_pa_stare', dataValue || dataSlug);
					} else if (filterResult.filterType === 'culoare') {
						params.set('filter_pa_culoare', dataValue || dataSlug);
					} else if (filterResult.filterType === 'memorie') {
						params.set('filter_pa_memorie', dataValue || dataSlug);
					}
					
					console.log('🔄 URL params after setting new filter:', Array.from(params.entries()));
					
					// Get the filter text for display - clean it up
					var filterText = cleanFilterText(text || dataValue || dataSlug || 'Filter');
					
					// Add to active filters display
					addActiveFilter(filterResult.filterType, dataValue || dataSlug || 'unknown', filterText);
					
					currentUrl.search = params.toString();
					currentUrl.searchParams.set('paged', '1'); // Reset to first page
					console.log('🔄 Building filter URL:', currentUrl.toString());
					window.location.href = currentUrl.toString();
					
					return false;
				}
			}
		});

		// Note: Condition filters are now handled by the general swatch filter handler above

		// General filter click handler for all products-filter__option elements
		shopwell.$body.on('click', '.products-filter__option', function(e) {
			var $this = $(this);
			var href = $this.attr('href');
			var classes = $this.attr('class');
			
			console.log('🔧 General filter option clicked:', href, 'Classes:', classes);
			
			if (href) {
				e.preventDefault();
				console.log('🔄 Navigating to filter:', href);
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
			
			console.log('☑️ Attribute filter changed:', name, '=', value, 'checked:', checked);
			
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
			console.log('🔄 Navigating to:', currentUrl.toString());
			
			// Navigate to filtered URL immediately
			window.location.href = currentUrl.toString();
		});

		// Also handle checkboxes in other locations
		shopwell.$body.on('change', 'input[type="checkbox"][name*="filter_"], input[type="checkbox"][name*="pa_"]', function(e) {
			var $this = $(this);
			var name = $this.attr('name');
			var value = $this.val();
			var checked = $this.is(':checked');
			
			console.log('☑️ Other checkbox filter changed:', name, '=', value, 'checked:', checked);
			
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
			console.log('🔄 Navigating to:', currentUrl.toString());
			
			// Navigate to filtered URL immediately
			window.location.href = currentUrl.toString();
		});

		// Auto-apply radio button filters
		shopwell.$body.on('change', '.woocommerce-widget-layered-nav-list input[type="radio"]', function(e) {
			var $this = $(this);
			var name = $this.attr('name');
			var value = $this.val();
			
			console.log('🔘 Radio filter changed:', name, '=', value);
			
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
			
			console.log('📋 Select filter changed:', name, '=', value);
			
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
			
			console.log('🎨 Color/Attribute filter clicked:', href);
			
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
			
			console.log('💰 Price filter changed:', 'min:', minPrice, 'max:', maxPrice);
			
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
			console.log('🎚️ Price slider changed:', ui.values);
			
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
        console.log('🚀 Document ready - initializing shopwell');
        shopwell.init();
        
        // Load existing active filters from URL on page load
        // Use setTimeout to ensure DOM is fully ready
        console.log('⏰ Setting timeout to load existing active filters');
        setTimeout(function() {
            console.log('⏰ Timeout reached - calling loadExistingActiveFilters');
            if (typeof shopwell.loadExistingActiveFilters === 'function') {
                shopwell.loadExistingActiveFilters();
            } else {
                console.error('❌ shopwell.loadExistingActiveFilters is not defined');
            }
        }, 100);
    });

})(jQuery);