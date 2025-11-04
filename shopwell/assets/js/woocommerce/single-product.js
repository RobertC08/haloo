(function ($) {
    'use strict';

    var shopwell = shopwell || {};
    shopwell.init = function () {
        shopwell.$body = $(document.body),
            shopwell.$window = $(window),
            shopwell.$header = $('#site-header');

        this.fractionsProductSlider();

        // Product Layout
        this.singleProductV1();
        this.singleProductV2();
        this.singleProductV3();
        this.singleProductV4();
        this.singleProductV5();
        this.singleProductV6();

        this.quantityNumber();

        this.productImageZoom();

        this.productVariation();
        this.productTabs();
        this.countDownHandler();

        this.relatedProductCarousel();
        this.upsellsProductCarousel();

        this.productDescriptionMore();

        this.productDegree();
        this.productVideoPopup();
        this.productFullScreen();

        this.updateFreeShippingBar();

        this.stickyHeaderCompact();
        this.fixedProductGallery();

        this.productExtraContent();

        this.reviewProduct();

        // Apply color swatches
        setTimeout(function() {
            if (typeof shopwell !== 'undefined' && typeof shopwell.applyColorSwatches === 'function') {
                shopwell.applyColorSwatches();
            }
        }, 100);
    };

    /**
     * Product Thumbnails
     */
    shopwell.productThumbnails = function ( vertical ) {
        var $gallery = $('.woocommerce-product-gallery');

        // Check if imagesLoaded is available
        var processThumbnails = function() {
            var columns = $gallery.data('columns'),
            $thumbnail = $gallery.find('.flex-control-thumbs');

            $thumbnail.wrap('<div class="woocommerce-product-gallery__thumbs-carousel"></div>');
            $thumbnail.before('<span class="shopwell-svg-icon shopwell-thumbs-button-prev shopwell-swiper-button"><svg viewBox="0 0 19 32"><path d="M13.552 0.72l2.656 1.76-9.008 13.52 9.008 13.52-2.656 1.76-10.192-15.28z"></path></svg></span>');
            $thumbnail.after('<span class="shopwell-svg-icon shopwell-thumbs-button-next shopwell-swiper-button"><svg viewBox="0 0 19 32"><path d="M5.648 31.28l-2.656-1.76 9.008-13.52-9.008-13.52 2.656-1.76 10.192 15.28z"></path></svg></span>');
            $thumbnail.wrap('<div class="swiper-container swiper" style="opacity:0"></div>');
            $thumbnail.addClass('swiper-wrapper');
            $thumbnail.find('li').addClass('swiper-slide');

            var options = {
                loop: false,
                autoplay: false,
                speed: 800,
                spaceBetween: 15,
                slidesPerView: columns,
                slidesPerGroup: 1,
                watchOverflow: true,
                navigation: {
                    nextEl: '.shopwell-thumbs-button-next',
                    prevEl: '.shopwell-thumbs-button-prev',
                },
                on: {
                    init: function () {
                        setTimeout(function () {
                            $thumbnail.parent().css('opacity', 1);
                            $thumbnail.css('opacity', 1);
                        }, 100);
                    }
                },
                breakpoints: {
                    300: {
                        slidesPerView: 3,
                        spaceBetween: 10,
                        allowTouchMove: false,
                    },
                    768: {
                        slidesPerView: 4,
                    },
                    992: {
                        slidesPerView: columns,
                        spaceBetween: 15,
                    },
                }
            };

            if (vertical) {
                options.direction = 'vertical';
            } else {
                options.direction = 'horizontal';
            }

            new Swiper($thumbnail.parent().get(0), options);

            // Add an <span> to thumbnails for responsive bullets.
            $('li', $thumbnail).append('<span/>');
        };
        
        // Check if imagesLoaded is available and use it, otherwise execute immediately
        if (typeof $gallery.imagesLoaded === 'function') {
            $gallery.imagesLoaded(processThumbnails);
        } else {
            // Fallback: execute immediately if imagesLoaded is not available
            setTimeout(processThumbnails, 100);
        }

    };

     /**
     * Product Image Zoom
     */
     shopwell.productImageZoom = function ( vertical ) {
        var $gallery = $('.woocommerce-product-gallery');

        if( shopwellData.product_image_zoom == '1' ) {
            $gallery.find('.woocommerce-product-gallery__image').each(function () {
                shopwell.zoomSingleProductImage(this);
            });
        }
    };

    /**
     * Zoom an image.
     * Copy from WooCommerce single-product.js file.
     */
    shopwell.zoomSingleProductImage = function (zoomTarget) {
        if ( typeof wc_single_product_params == 'undefined' || ! $.fn.zoom ) {
            return;
        }

        var $target = $(zoomTarget),
            width = $target.width(),
            zoomEnabled = false;

        $target.each(function (index, target) {
            var $image = $(target).find('img');
            if ($image.data('large_image_width') > width) {
                zoomEnabled = true;
                return false;
            }
        });

        // Only zoom if the img is larger than its container.
        if (zoomEnabled) {
            var zoom_options = $.extend({
                touch: false
            }, wc_single_product_params.zoom_options);

            if ('ontouchstart' in document.documentElement) {
                zoom_options.on = 'click';
            }

            $target.trigger('zoom.destroy');
            $target.zoom(zoom_options);
        }
    }

    /**
     * Fractions Product Slider
     */
    shopwell.fractionsProductSlider = function () {
        var $gallery = $('.woocommerce-product-gallery__wrapper').children(),
            $total   = $gallery.length,
            $current = 0;

        $gallery.each( function ( key ) {
            $current = key + 1;
            $(this).find('a').append( '<span class="shopwell-product-slider__fractions">' + $current + '/' + $total + '</span>' );
        });
     }

    /**
     * Single Product V1
     */
     shopwell.singleProductV1 = function () {
        var $product = $('div.product').hasClass('layout-1');
        if ( ! $product ) {
            return;
        }
        $('.woocommerce-product-gallery').on('product_thumbnails_slider_horizontal wc-product-gallery-after-init', function(){
            shopwell.productThumbnails(false);
        });
    }

    /**
     * Single Product V2
     */
     shopwell.singleProductV2 = function () {
        var $product = $('div.product').hasClass('layout-2');
        if ( ! $product ) {
            return;
        }

        $('.woocommerce-product-gallery').on('product_thumbnails_slider_vertical wc-product-gallery-after-init', function(){
            shopwell.productThumbnails(true);
        });

    }

    /**
     * Single Product V3
     */
     shopwell.singleProductV3 = function () {
        var $product = $('div.product').hasClass('layout-3');
        if ( ! $product ) {
            return;
        }

        $('.woocommerce-product-gallery').on('product_thumbnails_slider_vertical wc-product-gallery-after-init', function(){
            shopwell.productThumbnails(true);
        });

        $('.woocommerce-tabs:not(.wc-tabs-first--opened').find('.shopwell-dropdown__title').removeClass( 'active' );
    }

    /**
     * Single Product V4
     */
     shopwell.singleProductV4 = function () {
        var $product = $('div.product').hasClass('layout-4');
        if ( ! $product ) {
            return;
        }

        $('.woocommerce-product-gallery').on('product_thumbnails_slider_horizontal  wc-product-gallery-after-init', function(){
            shopwell.productThumbnails(false);
        });
        if( shopwell.$window.width() > 767 ) {
            $('.woocommerce-product-gallery').append($('.shopwell-product-gallery').find('.shopwell-product-images-buttons'));
        }
    }

    /**
     * Single Product V5
     */
     shopwell.singleProductV5 = function () {
        var $product = $('div.product').hasClass('layout-5');
        if ( ! $product ) {
            return;
        }

        $('.woocommerce-product-gallery').on('product_thumbnails_slider_horizontal wc-product-gallery-after-init', function(){
            shopwell.productThumbnails(false);
        });

        $('.woocommerce-tabs:not(.wc-tabs-first--opened').find('.shopwell-dropdown__title').removeClass( 'active' );
    }

    /**
     * Single Product V6
     */
     shopwell.singleProductV6 = function () {
        var $product = $('div.product').hasClass('layout-6');
        if ( ! $product ) {
            return;
        }

        $('.woocommerce-product-gallery').on('product_thumbnails_slider_vertical wc-product-gallery-after-init', function(){
            shopwell.productThumbnails(true);
        });
    }

     /**
     * Quantity Number
     */
     shopwell.quantityNumber = function () {
        var $product = $('div.product').find('.quantity__label-number');
        if ( ! $product ) {
            return;
        }

        var $quantity = $( 'div.product .quantity input[name="quantity"]' );

        $quantity.closest( 'div.product' ).find( '.quantity__label-number' ).text( $quantity.val() );

        $quantity.on( 'change', function() {
            $(this).closest( 'div.product' ).find( '.quantity__label-number' ).text( $(this).val() );
        });

    }

    /**
     * Product Tabs
     */
    shopwell.productTabs = function () {
        var $product      = $('.single-product div.product'),
            $tabs         = $product.find('.woocommerce-tabs'),
            $idTab        = $tabs.find( '.shopwell-tabs-heading .active a' ).attr( 'href' );

        $( '.wc-tabs-wrapper ' + $idTab ).removeAttr('style');

        shopwell.$window.on( 'resize', function () {
            if ( shopwellData.product_layout !== '3' && shopwellData.product_layout !== '5' && shopwellData.product_layout !== '6' ) {
                if( shopwell.$window.width() < 767 ) {
                    $product.addClass('product-wc-tabs-dropdown');
                } else {
                    $product.removeClass('product-wc-tabs-dropdown');
                    $product.find('.shopwell-dropdown__content').removeAttr('style');
                }
            }
        }).trigger('resize');

        $tabs.on('click', '.shopwell-dropdown__title', function (e) {
            e.preventDefault();

            if( ! $product.hasClass('product-wc-tabs-dropdown') ) {
                return;
            }

            if ($(this).hasClass('active')) {
                $(this).removeClass('active');
                $(this).siblings('.shopwell-dropdown__content').stop().slideUp("slow");

            } else {
                $tabs.find('.shopwell-dropdown__title').removeClass('active');
                $tabs.find('.shopwell-dropdown__content').slideUp();
                $(this).addClass('active');
                $(this).siblings('.shopwell-dropdown__content').stop().slideDown("slow");

                $('html, body').animate({
                    scrollTop: $($(this).attr('href')).offset().top - 50
                }, 300);
            }
        });

        $('.button-write-review').on( 'click', function (e) {
            e.preventDefault();

            if( ! $('.shopwell-dropdown__title.tab-title-reviews').hasClass( 'active' ) ) {
                $('.shopwell-dropdown__title.tab-title-reviews').trigger('click');
            }

            $('html, body').stop(true, true).animate({
                scrollTop: $( $(this).find('a').attr('href') ).offset().top - 150
            }, 300);
        });
    }

    /**
     * Product Variation
     */
    shopwell.productVariation = function () {
        var $price = $( '.single-product div.product .product-gallery-summary .variations-attribute-change .price' ).html(),
            $stock = $( '.single-product div.product .product-gallery-summary .variations-attribute-change .stock' ).clone(),
            $sku = $( '.single-product div.product .meta-sku .sku' ),
            sku_value = $sku.html(),
            $date_onsale_to = $( '.single-product div.product .product-gallery-summary .variations-attribute-change .woocommerce-badge--text' ).html();

        $('.single-product div.product .product-gallery-summary .variations_form').on( 'show_variation', function (event, variation) {
            var $container          = $(this).closest( '.product-gallery-summary' ).find( '.variations-attribute-change' ),
                $price_new          = $(this).find( '.woocommerce-variation-price .price' ).html(),
                $stock_new          = $(this).find( '.woocommerce-variation-availability .stock' ).clone(),
                $variation_id       = $(this).find( '.variation_id' ).val(),
                $date_onsale_to_new = $(this).find( '.variation-id-' + $variation_id ).html();

            $container.find( '.price' ).html( $price_new );
            $container.find( '.stock' ).replaceWith( $stock_new );

            if ( variation.sku ) {
                $sku.html( variation.sku );
            } else {
                $sku.html( sku_value );
            }

            if( $date_onsale_to && $variation_id !== '0' ) {
                $container.find( '.woocommerce-badge--text' ).html( $date_onsale_to_new );
            }
        });

        $('.single-product div.product .product-gallery-summary .variations_form').on( 'hide_variation', function () {
            var $container = $(this).closest( '.product-gallery-summary' ).find( '.variations-attribute-change' );

            $container.find( '.price' ).html( $price );
            $container.find( '.stock' ).replaceWith( $stock );
            $sku.html( sku_value );

            if( $date_onsale_to ) {
                $container.find( '.woocommerce-badge--text' ).html( $date_onsale_to );
            }
        });
    }

    /**
     * Related Product Carousel.
     */
     shopwell.relatedProductCarousel = function () {
        var $related = $('.products.related');

        if ( !$related.length ) {
            return;
        }

        var $products = $related.find('ul.products');

        $products.wrap('<div class="related-product__carousel"></div>');
        $products.after('<span class="shopwell-svg-icon swiper-button shopwell-swiper-button-prev shopwell-swiper-button"><svg viewBox="0 0 32 32"><path d="M20.58 2.58l2.84 2.84-10.6 10.58 10.6 10.58-2.84 2.84-13.4-13.42z"></path></svg></span>');
        $products.after('<span class="shopwell-svg-icon swiper-button shopwell-swiper-button-next shopwell-swiper-button"><svg viewBox="0 0 32 32"><path d="M11.42 29.42l-2.84-2.84 10.6-10.58-10.6-10.58 2.84-2.84 13.4 13.42z"></path></svg></span>');
        $products.after('<div class="swiper-pagination"></div>');
        $products.wrap('<div class="swiper-container swiper linked-products-carousel" style="opacity: 0;"></div>');
        $products.addClass('swiper-wrapper');
        $products.find('li.product').addClass('swiper-slide');

        var $spaceBetween = $products.hasClass('product-card-layout-3') || $products.hasClass('product-card-layout-5') ? 24 : 0;

        var options = {
            loop: false,
            autoplay: false,
            speed: 800,
            watchOverflow: true,
            slidesPerView: 5,
            navigation: {
                nextEl: $related.find('.shopwell-swiper-button-next').get(0),
                prevEl: $related.find('.shopwell-swiper-button-prev').get(0),
            },
            pagination: {
                el: $related.find('.swiper-pagination').get(0),
                type: 'bullets',
                clickable: true,
            },
            on: {
                init: function () {
                    this.$el.css('opacity', 1);
                }
            },
            spaceBetween: 0,
            breakpoints: {
                300: {
                    slidesPerView: shopwellData.mobile_product_columns == '' ? 2 : parseInt(shopwellData.mobile_product_columns),
                    slidesPerGroup: shopwellData.mobile_product_columns == '' ? 2 : parseInt(shopwellData.mobile_product_columns),
                },
                768: {
                    slidesPerView: 4,
                    spaceBetween: $spaceBetween,
                },
                1200: {
                    slidesPerView: 5,
                    spaceBetween: $spaceBetween,
                }
            }
        };

        new Swiper( $related.find('.linked-products-carousel').get(0), options );
    }

    /**
     * Upsells Product Carousel.
     */
     shopwell.upsellsProductCarousel = function () {
        var $upsells = $('.products.upsells');

        if ( !$upsells.length ) {
            return;
        }

        var $products = $upsells.find('ul.products');

        $products.wrap('<div class="upsells-product__carousel"></div>');
        $products.after('<span class="shopwell-svg-icon swiper-button shopwell-swiper-button-prev shopwell-swiper-button"><svg viewBox="0 0 32 32"><path d="M20.58 2.58l2.84 2.84-10.6 10.58 10.6 10.58-2.84 2.84-13.4-13.42z"></path></svg></span>');
        $products.after('<span class="shopwell-svg-icon swiper-button shopwell-swiper-button-next shopwell-swiper-button"><svg viewBox="0 0 32 32"><path d="M11.42 29.42l-2.84-2.84 10.6-10.58-10.6-10.58 2.84-2.84 13.4 13.42z"></path></svg></span>');
        $products.after('<div class="swiper-pagination"></div>');
        $products.wrap('<div class="swiper-container swiper linked-products-carousel" style="opacity: 0;"></div>');
        $products.addClass('swiper-wrapper');
        $products.find('li.product').addClass('swiper-slide');

        var $spaceBetween = $products.hasClass('product-card-layout-3') || $products.hasClass('product-card-layout-5') ? 24 : 0;

        var options = {
            loop: false,
            autoplay: false,
            speed: 800,
            watchSlidesVisibility: true,
            watchOverflow: true,
            navigation: {
                nextEl: $upsells.find('.shopwell-swiper-button-next').get(0),
                prevEl: $upsells.find('.shopwell-swiper-button-prev').get(0),
            },
            pagination: {
                el: $upsells.find('.swiper-pagination').get(0),
                type: 'bullets',
                clickable: true,
            },
            on: {
                init: function () {
                    this.$el.css('opacity', 1);
                }
            },
            spaceBetween: $spaceBetween,
            breakpoints: {
                300: {
                    slidesPerView: shopwellData.mobile_product_columns == '' ? 2 : parseInt(shopwellData.mobile_product_columns),
                    slidesPerGroup: shopwellData.mobile_product_columns == '' ? 2 : parseInt(shopwellData.mobile_product_columns),
                },
                768: {
                    slidesPerView: 4,
                    spaceBetween: $spaceBetween,
                },
                1200: {
                    slidesPerView: 5,
                    spaceBetween: $spaceBetween,
                }
            }
        };

        new Swiper($upsells.find('.linked-products-carousel').get(0), options);
    }

    /**
	 * CountDown
	 */
	shopwell.countDownHandler = function () {
        if ( $.fn.shopwell_countdown !== undefined ) {
		    $('.shopwell-single-product-sale').find('.shopwell-countdown').shopwell_countdown();
        }
	};

    shopwell.productDescriptionMore = function() {
        var $content = $('div.product').find('.short-description__content');

        if( ! $content.length ) {
            return;
        }

        var    scrollHeight = $content[0].scrollHeight,
            clientheight = $content[0].clientHeight;
        if( scrollHeight > clientheight ) {
            $('div.product').find('.short-description__more').show();
        }
        $('div.product').on('click', '.short-description__more', function(e) {
			e.preventDefault();

			var $settings = $(this).data( 'settings' ),
				$more     = $settings.more,
				$less     = $settings.less,
                $description = $(this).closest( '.short-description' );

			if(  $description.hasClass( 'activate' ) ) {
				$description.removeClass( 'activate' );
				$(this).text( $more )
			} else {
				$description.addClass( 'activate' );
				$(this).text( $less )
			}
		});
    }

    /**
     * Show product 360 degree
     */
    shopwell.productDegree = function () {
        var $product_degrees = $('.shopwell-product-gallery .shopwell-button--degree');

        if ( $product_degrees.length < 1 ) {
            return;
        }

        if ( shopwellData.product_degree.length < 1 ) {
            return;
        }
        var degree = '',
            $pswp = $('#product-degree-pswp');

        $product_degrees.on('click', function (e) {
            e.preventDefault();

            if ($pswp.hasClass('init')) {
                return;
            }

            $pswp.addClass('init');

            var imgArray = shopwellData.product_degree.split(','),
                images = [];

            for (var i = 0; i < imgArray.length; i++) {
                images.push(imgArray[i]);
            }

            degree = $pswp.find('.shopwell-product-gallery-degree').ThreeSixty({
                totalFrames: images.length, // Total no. of image you have for 360 slider
                endFrame: images.length, // end frame for the auto spin animation
                currentFrame: 1, // This the start frame for auto spin
                imgList: $pswp.find('.product-degree__images'), // selector for image list
                progress: '.shopwell-gallery-degree__spinner', // selector to show the loading progress
                imgArray: images, // path of the image assets
                height: 500,
                width: 830,
                navigation: false
            });

            $pswp.on('click', '.nav-bar__run', function () {
                $(this).addClass('active');
                degree.play();
            });

            $pswp.on('click', '.nav-bar__run.active', function () {
                $(this).removeClass('active');
                degree.stop();
            });

            $pswp.on('click', '.nav-bar__next', function () {
                degree.stop();
                $('.nav-bar__run').removeClass('active');
                degree.next();
            });

            $pswp.on('click', '.nav-bar__prev', function () {
                degree.stop();
                $('.nav-bar__run').removeClass('active');
                degree.previous();
            });

            $pswp.on('click', '.modal__button-close, .modal__backdrop', function () {
                degree.stop();
                $('.nav-bar__run').removeClass('active');
            });
        });
    };

    /**
     * Init product video
     */
    shopwell.productVideoPopup = function () {
        var $video_icon = $('.shopwell-product-gallery').find('.shopwell-button--video');
        if ($video_icon.length < 1) {
            return;
        }

        var options = {
            type: 'iframe',
            mainClass: 'mfp-fade',
            removalDelay: 300,
            preloader: false,
            fixedContentPos: false,
            iframe: {
                markup: '<div class="mfp-iframe-scaler">' +
                        '<div class="mfp-close"></div>' +
                        '<iframe class="mfp-iframe" frameborder="0" allow="autoplay"></iframe>' +
                        '</div>',
                patterns: {
                    youtube: {
                        index: 'youtube.com/', // String that detects type of video (in this case YouTube). Simply via url.indexOf(index).

                        id: 'v=', // String that splits URL in a two parts, second part should be %id%
                        src: 'https://www.youtube.com/embed/%id%?autoplay=1' // URL that will be set as a source for iframe.
                    },
                    youtu: {
                        index: 'youtu.be/', // String that detects type of video (in this case YouTube). Simply via url.indexOf(index).

                        id: '/', // String that splits URL in a two parts, second part should be %id%
                        src: 'https://www.youtube.com/embed/%id%?autoplay=1' // URL that will be set as a source for iframe.
                    },
                    vimeo: {
                        index: 'vimeo.com/',
                        id: '/',
                        src: '//player.vimeo.com/video/%id%?autoplay=1'
                    }
                },

                srcAction: 'iframe_src', // Templating object key. First part defines CSS selector, second attribute. "iframe_src" means: find "iframe" and set attribute "src".
            }
        };

        $video_icon.magnificPopup(options);
    };

    /**
     * Full Screen
     */
    shopwell.productFullScreen = function () {
        $( '.woocommerce-product-gallery__image a' ).on( 'click', function (e) {
            e.preventDefault();
        });

        $( '.shopwell-button--product-lightbox' ).on( 'click', function() {
            if( $( '.woocommerce-product-gallery__trigger' ).length > 0 ) {
                $( '.woocommerce-product-gallery__trigger' ).trigger( 'click' );
            } else {
                $('.flex-viewport .woocommerce-product-gallery__image.flex-active-slide a').trigger( 'click' );
            }
        });
    }

    shopwell.updateFreeShippingBar = function() {
        $( document.body ).on( 'removed_from_cart', function( e, response ) {
            if( $('.single-product div.product').find('.shopwell-free-shipping-bar').length && $(response['div.widget_shopping_cart_content']).length ) {
               if( $(response['div.widget_shopping_cart_content']).find('.shopwell-free-shipping-bar').length  ) {
                    $('.single-product div.product').find('.shopwell-free-shipping-bar').replaceWith($(response['div.widget_shopping_cart_content']).find('.shopwell-free-shipping-bar'));
               } else {
                    $('.single-product div.product').find('.shopwell-free-shipping-bar').hide();
               }
            }
        } );

        $( document.body ).on( 'added_to_cart', function( e, response ) {
            if( $('.single-product div.product').find('.shopwell-free-shipping-bar').length && $(response['div.widget_shopping_cart_content']).length && $(response['div.widget_shopping_cart_content']).find('.shopwell-free-shipping-bar').length ) {
                $('.single-product div.product').find('.shopwell-free-shipping-bar').replaceWith($(response['div.widget_shopping_cart_content']).find('.shopwell-free-shipping-bar'));
            }

        } );
    }

    shopwell.stickyHeaderCompact = function() {
        if (!shopwell.$body.hasClass('mobile-header-compact')) {
            return;
        }

        if( shopwell.$window.width() > 767 ) {
            return;
        }

        var $headerCompact = shopwell.$body.find('.product-header-compact');
		if ( ! $headerCompact.length) {
			return;
		}

        shopwell.$window.on('scroll', function () {
            var scroll 		= shopwell.$window.scrollTop(),
                scrollTop 		= $headerCompact.outerHeight(true);
            if (scroll > scrollTop) {
				$headerCompact.find('.product-sticky-header').addClass('minimized');
            } else {
				$headerCompact.find('.product-sticky-header').removeClass('minimized');
            }
        } );
    }

    shopwell.fixedProductGallery = function() {
        if (!shopwell.$body.hasClass('mobile-fixed-product-gallery')) {
            return;
        }
        var $productGallery = $('div.product .woocommerce-product-gallery');
        shopwell.$window.on('resize', function () {
			if( shopwell.$window.width() > 767 ) {
				$productGallery.removeClass('has-scroll');
				$productGallery.removeAttr('style');
			} else {
				$productGallery.addClass('has-scroll');
			}
		}).trigger('resize');

        var noticeHeight = $('.single-product').find('.woocommerce-notices-wrapper').outerHeight(true);
        if( noticeHeight > 0 ) {
            if( shopwell.$body.hasClass('admin-bar') ) {
                noticeHeight += 46;
            }

            $('.mobile-fixed-product-gallery.admin-bar div.product .woocommerce-product-gallery').css({top: noticeHeight + 10})
        }

        // Check if imagesLoaded is available
        var processGallerySpacing = function() {
            var imageHeight = $('.woocommerce-product-gallery .woocommerce-product-gallery__image > a').height(),
                imageWidth = $('.woocommerce-product-gallery .woocommerce-product-gallery__image > a').width(),
                ratio = imageHeight && imageWidth ? imageHeight/imageWidth : 0;
            if( ratio && ratio > 50 ) {
                $('.product-fixed-gallery-spacing').css( '--shopwell-product-fixed-gallery-spacing', (ratio * 100).toFixed(2) + 'vw' );
            }
        };
        
        if (typeof $productGallery.imagesLoaded === 'function') {
            $productGallery.imagesLoaded(processGallerySpacing);
        } else {
            // Fallback: execute immediately if imagesLoaded is not available
            setTimeout(processGallerySpacing, 500);
        }

        shopwell.$window.on('scroll', function () {
            if( ! $productGallery.hasClass('has-scroll')) {
                return;
            }
            var wScrollTop = shopwell.$window.scrollTop(),
                galleryHeight = $productGallery.find('.woocommerce-product-gallery__image').outerHeight(false),
                opacityValue = 0;
            if (wScrollTop > 0) {
                if( galleryHeight < wScrollTop ) {
                    opacityValue = 0;
                } else {
                    opacityValue = (galleryHeight - wScrollTop) / galleryHeight;
                }
            } else {
                opacityValue = 1;
            }

            $('div.product .shopwell-product-gallery').css({opacity: opacityValue});

        }).trigger('scroll');
    }

    shopwell.productExtraContent = function() {
        var $productExtra = shopwell.$body.find('.single-product-extra-content');
		if ( ! $productExtra.length) {
			return;
		}

        $productExtra.on('click', '.shopwell-icon-box-widget__button', function(e){
            var href = $(this).attr('href');
            if( $('.woocommerce-tabs').find('a[href=' + href + ']').length && $('.woocommerce-tabs').find( href ).length ) {
                e.preventDefault();
                var $tab = $(href),
                    offTop = 20;
                if( shopwell.$body.hasClass('admin-bar') ) {
                    offTop += 32;
                }

                if( $('#shopwell-sticky-add-to-cart').length ) {
                    offTop += $('#shopwell-sticky-add-to-cart').outerHeight(true);
                } else if( shopwell.$body.hasClass('.shopwell-header-sticky') ) {
                    offTop += shopwell.$body.find('.header-sticky').outerHeight(true);
                }
                setTimeout( function () {
                    $( 'html,body' ).stop().animate({
                        scrollTop: $tab.offset().top - offTop
                    },
                    'slow' );
                }, 400 );

                $('.woocommerce-tabs').find('a[href=' + href + ']').trigger('click');
            }
        })
    }

	    /**
	 * Handle product reviews
	 */
	shopwell.reviewProduct = function () {
		setTimeout(function () {
			var $hash = window.location.hash;
			$('#respond p.stars a').append('<span class="shopwell-svg-icon"><svg width="24" height="24" aria-hidden="true" role="img" focusable="false" viewBox="0 0 32 32"><path d="M16 1.333l3.467 11.2h11.2l-9.067 6.933 3.467 11.2-9.067-6.933-9.067 6.933 3.467-11.2-9.067-6.933h11.2z"></path></svg></span>');

			if ($hash.toLowerCase().indexOf("comment-") >= 0 || $hash === "#reviews" || $hash === "#tab-reviews") {
				$('.shopwell-dropdown__title.tab-title-reviews').trigger('click');
                if( $( $hash ).length ) {
                    $('html, body').animate({
                        scrollTop: $( $hash ).offset().top - 150
                    }, 300);
                }

			}
		}, 100);
	};

	/**
	 * Apply color swatches to variation buttons
	 *
	 * @since 1.0.0
	 */
	shopwell.applyColorSwatches = function () {
	// Color map directly in JavaScript for Romanian color names (with 20% opacity)
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

		// Normalize function for matching
		function normalizeText(text) {
			if (!text) return '';
			return text.toLowerCase().trim().replace(/[ăâîșțşţ]/g, function(match) {
				var map = {'ă': 'a', 'â': 'a', 'î': 'i', 'ș': 's', 'ş': 's', 'ț': 't', 'ţ': 't'};
				return map[match] || match;
			}).replace(/[^\w\s]/g, '').replace(/\s+/g, ' ');
		}

		// Function to get color by name
		function getColorByName(colorName) {
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
			
			// Try partial match
			for (var key2 in colorMap) {
				var keyNormalized = normalizeText(key2);
				if (keyNormalized.indexOf(normalized) !== -1 || normalized.indexOf(keyNormalized) !== -1) {
					return colorMap[key2];
				}
			}
			
			return null;
		}
		
		// Function to calculate luminance (brightness) of a color
		function getLuminance(color) {
			var r, g, b;
			
			// Handle rgba format
			if (color.indexOf('rgba') === 0 || color.indexOf('rgb') === 0) {
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
			
			// Calculate relative luminance using WCAG formula
			var rsRGB = r / 255;
			var gsRGB = g / 255;
			var bsRGB = b / 255;
			
			var rLinear = rsRGB <= 0.03928 ? rsRGB / 12.92 : Math.pow((rsRGB + 0.055) / 1.055, 2.4);
			var gLinear = gsRGB <= 0.03928 ? gsRGB / 12.92 : Math.pow((gsRGB + 0.055) / 1.055, 2.4);
			var bLinear = bsRGB <= 0.03928 ? bsRGB / 12.92 : Math.pow((bsRGB + 0.055) / 1.055, 2.4);
			
			return 0.2126 * rLinear + 0.7152 * gLinear + 0.0722 * bLinear;
		}
		
		// Function to determine if color is dark (returns true if dark)
		function isDarkColor(color) {
			var luminance = getLuminance(color);
			// If luminance is less than 0.5, it's considered dark
			return luminance < 0.5;
		}
		
		// Expose helper functions globally for reuse in other scripts
		shopwell.getColorByName = getColorByName;
		shopwell.normalizeText = normalizeText;
		shopwell.colorMap = colorMap;
		shopwell.getLuminance = getLuminance;
		shopwell.isDarkColor = isDarkColor;

		// Function to apply colors to swatches
		function applyColors() {
			// Try multiple selectors to find color swatch buttons
			// First try with color-specific selector
			var $swatches = $('.single-product div.product .wcboost-variation-swatches--color .wcboost-variation-swatches__item');
			
			// Also try all variation swatch items and filter by class pattern
			if ($swatches.length === 0) {
				$swatches = $('.single-product div.product .wcboost-variation-swatches__item').filter(function() {
					var classes = this.className || '';
					return classes.indexOf('wcboost-variation-swatches__item-') !== -1;
				});
			}
			
			// Try in variations table
			if ($swatches.length === 0) {
				$swatches = $('.single-product div.product table.variations .wcboost-variation-swatches__item').filter(function() {
					var classes = this.className || '';
					return classes.indexOf('wcboost-variation-swatches__item-') !== -1;
				});
			}
			
			// Try to find by attribute name "Culoare" in table rows
			if ($swatches.length === 0) {
				$('.single-product div.product table.variations tr').each(function() {
					var $row = $(this);
					var $label = $row.find('.label');
					if ($label.text().toLowerCase().indexOf('culoare') !== -1) {
						var $rowSwatches = $row.find('.wcboost-variation-swatches__item');
						$swatches = $swatches.add($rowSwatches);
					}
				});
			}

			// Apply colors to found swatches

			$swatches.each(function() {
				var $item = $(this);
				var matchedColor = null;
				var colorName = '';
				
				// Method 1: Extract color name from CSS class (e.g., wcboost-variation-swatches__item-auriu)
				$item.each(function() {
					var classes = this.className.split(/\s+/);
					for (var i = 0; i < classes.length; i++) {
						var className = classes[i];
						if (className.indexOf('wcboost-variation-swatches__item-') === 0) {
							colorName = className.replace('wcboost-variation-swatches__item-', '').trim();
							if (colorName) {
							matchedColor = getColorByName(colorName);
							if (matchedColor) {
								return false; // break
							}
							}
						}
					}
				});
				
				// Method 2: Get from data-value attribute
				if (!matchedColor) {
					var dataValue = $item.attr('data-value') || $item.data('value') || '';
					if (dataValue) {
						colorName = dataValue.toLowerCase().trim();
						matchedColor = getColorByName(colorName);
					}
				}
				
				// Method 3: Get from item text (fallback)
				if (!matchedColor) {
					var itemText = $item.text().trim() || 
								   $item.find('.wcboost-variation-swatches__name').text().trim() || 
								   $item.attr('aria-label') || 
								   $item.attr('title') || 
								   $item.data('name') || '';
					
					if (!itemText) {
						// Try to get from nested elements
						$item.find('span, div, a, button').each(function() {
							var $el = $(this);
							var text = $el.text().trim();
							if (text && text.length < 50 && itemText.length < text.length) {
								itemText = text;
							}
						});
					}
					
					// Remove any numbers or extra text, keep only color name
					itemText = itemText.replace(/\(\d+\)/g, '').trim();
					
					if (itemText) {
						colorName = itemText;
						matchedColor = getColorByName(itemText);
					}
				}
				
			if (matchedColor) {
				// Find the color swatch span (not the name span)
				var $colorSpan = $item.find('span:not(.wcboost-variation-swatches__name)').first();
				
				if ($colorSpan.length === 0) {
					$colorSpan = $('<span></span>');
					$item.prepend($colorSpan);
				}
				
				// Apply background color ONLY to the color swatch circle span
				$colorSpan.css({
					'background-color': matchedColor,
					'display': 'block',
					'width': '100%',
					'height': '100%',
					'border-radius': '50%'
				});
				
				// Set via style attribute for maximum priority
				if ($colorSpan[0]) {
					$colorSpan[0].style.setProperty('background-color', matchedColor, 'important');
				}
				
				// Apply to item itself as backup
				$item.css('background-color', matchedColor);
				if ($item[0]) {
					$item[0].style.setProperty('background-color', matchedColor, 'important');
				}
				
				// Remove background and border-radius from name span explicitly
				var $nameSpan = $item.find('.wcboost-variation-swatches__name');
				if ($nameSpan.length > 0) {
					$nameSpan.css({
						'background-color': 'transparent',
						'border-radius': '0'
					});
					if ($nameSpan[0]) {
						$nameSpan[0].style.setProperty('background-color', 'transparent', 'important');
						$nameSpan[0].style.setProperty('border-radius', '0', 'important');
					}
				}
					
				// Since colors are at 20% opacity, always use dark text for better contrast
				var textColor = '#1d2128';
				
				// Apply text color to item itself
				$item.css('color', textColor);
				if ($item[0]) {
					$item[0].style.setProperty('color', textColor, 'important');
				}
				
				// Find and apply to all text elements inside (excluding the color swatch span)
				var $textElements = $item.find('.wcboost-variation-swatches__name, span:not(.wcboost-variation-swatches__color):not([style*="background-color"]), div:not([style*="background-color"]), a, button');
				$textElements.css('color', textColor);
				$textElements.each(function() {
					if (this.style) {
						this.style.setProperty('color', textColor, 'important');
					}
				});
					
					// Also apply to direct text content if no child elements found
					if ($textElements.length === 0 && $item.text().trim()) {
						// Create a wrapper if needed or just set on the item itself (already done above)
						$item.css('color', textColor);
					}
					
					// Set data attribute for reference
					$item.attr('data-color-applied', matchedColor);
					$item.attr('data-text-color', textColor);
				}
			});
		}

		// Apply colors multiple times with delays to catch plugin initialization
		function applyColorsWithRetry(attempts) {
			attempts = attempts || 0;
			if (attempts < 5) {
				applyColors();
				if (attempts < 4) {
					setTimeout(function() {
						applyColorsWithRetry(attempts + 1);
					}, 300 + (attempts * 200));
				}
			}
		}

		// Apply colors immediately and with retries
		applyColorsWithRetry();
		
		// Also apply on document ready
		$(document).ready(function() {
			setTimeout(function() {
				applyColorsWithRetry();
			}, 500);
		});

		// Reapply when variations are updated (for AJAX updates)
		$(document.body).on('wc_variation_form updated_wc_div init_variation_swatches', function() {
			setTimeout(function() {
				applyColors();
			}, 300);
		});

		// Use MutationObserver to catch DOM changes
		if (typeof MutationObserver !== 'undefined') {
			var observer = new MutationObserver(function(mutations) {
				setTimeout(applyColors, 100);
			});
			
			var $product = $('.single-product div.product');
			if ($product.length) {
				observer.observe($product[0], {
					childList: true,
					subtree: true
				});
			}
		}
		
		// Trigger after a longer delay to ensure plugin has initialized
		setTimeout(function() {
			applyColors();
		}, 2000);
	};

    /**
     * Document ready
     */
    shopwell.init();

})(jQuery);