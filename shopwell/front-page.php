<?php
/**
 * Template Name: Homepage
 * 
 * Homepage Template
 *
 * Template for displaying the homepage with custom sections
 *
 * @package Shopwell
 */

get_header();
?>
<!-- CSS moved to external file: assets/css/pages/homepage.css -->

<script>

// Mobile carousel with simple, reliable touch handling
document.addEventListener('DOMContentLoaded', function() {
    const track = document.querySelector('.testimonials-carousel-right .carousel-track');
    if (!track) {
        return;
    }
    
    
    // Mouse events for desktop testing
    let isDown = false;
    let startX, scrollLeft;

    track.addEventListener('mousedown', e => {
        isDown = true;
        track.classList.add('grabbing');
        startX = e.pageX - track.offsetLeft;
        scrollLeft = track.scrollLeft;
    });
    
    track.addEventListener('mouseleave', () => { 
        isDown = false; 
        track.classList.remove('grabbing');
    });
    
    track.addEventListener('mouseup', () => { 
        isDown = false; 
        track.classList.remove('grabbing');
    });
    
    track.addEventListener('mousemove', e => {
        if(!isDown) return;
        e.preventDefault();
        const x = e.pageX - track.offsetLeft;
        const walk = (x - startX) * 1.5; // scroll-fastness
        track.scrollLeft = scrollLeft - walk;
    });

    // Simple touch swipe for mobile - only move on clear swipe
    let touchStartX = 0;
    let touchStartY = 0;
    
    track.addEventListener('touchstart', e => {
        touchStartX = e.touches[0].clientX;
        touchStartY = e.touches[0].clientY;
    }, { passive: true });

    track.addEventListener('touchend', e => {
        const touchEndX = e.changedTouches[0].clientX;
        const touchEndY = e.changedTouches[0].clientY;
        const diffX = touchStartX - touchEndX;
        const diffY = touchStartY - touchEndY;
        
        // Only respond to clear horizontal swipes (not vertical scrolling)
        if (Math.abs(diffX) > Math.abs(diffY) && Math.abs(diffX) > 80) {
            const cardWidth = track.querySelector('.testimonial-item').offsetWidth;
            const newScrollLeft = track.scrollLeft + (diffX > 0 ? cardWidth : -cardWidth);
            
            track.scrollTo({
                left: newScrollLeft,
                behavior: 'smooth'
            });
        } else {
        }
    }, { passive: true });
    
    // Add active class to centered item
    track.addEventListener('scroll', () => {
        const items = track.querySelectorAll('.testimonial-item');
        const trackRect = track.getBoundingClientRect();
        const trackCenter = trackRect.left + trackRect.width / 2;
        
        items.forEach(item => {
            item.classList.remove('active');
            const itemRect = item.getBoundingClientRect();
            const itemCenter = itemRect.left + itemRect.width / 2;
            
            if (Math.abs(itemCenter - trackCenter) < 50) {
                item.classList.add('active');
            }
        });
    });
    
});

// Categories Slider
document.addEventListener('DOMContentLoaded', function() {
    const slider = document.querySelector('.catalog-top-categories__slider');
    const prevBtn = document.querySelector('.catalog-top-categories__nav--prev');
    const nextBtn = document.querySelector('.catalog-top-categories__nav--next');
    const wrapper = document.querySelector('.catalog-top-categories__wrapper');
    
    if (!slider || !prevBtn || !nextBtn || !wrapper) {
        return;
    }
    
    
    const items = slider.querySelectorAll('.catalog-top-categories__item');
    const totalItems = items.length;
    let currentSlide = 0;
    let itemsPerView = 9; // Default for desktop
    
    // Calculate items per view based on screen size
    function getItemsPerView() {
        if (window.innerWidth <= 360) return 4;
        if (window.innerWidth <= 480) return 5;
        if (window.innerWidth <= 768) return 6;
        return 9; // Show 9 items on desktop
    }
    
    // Check if mobile
    function isMobile() {
        return window.innerWidth <= 768;
    }
    
    // Get maximum slide position
    function getMaxSlide() {
        // Calculate based on wrapper width and slider width to allow item-by-item scrolling
        const wrapperWidth = wrapper.offsetWidth;
        const sliderWidth = slider.scrollWidth;
        const itemWidth = items[0] ? items[0].offsetWidth + 36 : 126; // 90px + 36px gap
        
        // Calculate how many items can fit in the visible area
        const visibleItems = Math.floor(wrapperWidth / itemWidth);
        
        // If all items fit, no need to scroll
        if (sliderWidth <= wrapperWidth) {
            return 0;
        }
        
        // Calculate maximum slide position to scroll one item at a time
        // Allow scrolling until the last item is visible
        const maxScroll = totalItems - visibleItems;
        return Math.max(0, maxScroll);
    }
    
    
    // Update slider position
    function updateSlider() {
        const itemWidth = items[0] ? items[0].offsetWidth + 36 : 126;
        
        // Get actual dimensions accounting for padding
        const wrapperRect = wrapper.getBoundingClientRect();
        const sliderRect = slider.getBoundingClientRect();
        const wrapperWidth = wrapperRect.width;
        const sliderWidth = slider.scrollWidth;
        
        // Calculate the maximum translate to show the last item fully
        // This aligns the right edge of the slider content with the right edge of the visible wrapper area
        const maxPossibleTranslate = -(sliderWidth - wrapperWidth);
        const maxSlide = getMaxSlide();
        
        // Ensure currentSlide doesn't go negative
        if (currentSlide < 0) {
            currentSlide = 0;
        }
        
        // Calculate translateX based on currentSlide (one item per slide)
        let translateX = -currentSlide * itemWidth;
        
        // Check if we need to show the last item fully
        const isAtEnd = currentSlide >= maxSlide && maxSlide > 0;
        
        if (isAtEnd) {
            // When at the end, ensure the last item is fully visible
            // Use maxPossibleTranslate which aligns the right edge of slider with right edge of wrapper
            translateX = maxPossibleTranslate;
        }
        
        // Clamp translateX to prevent scrolling beyond bounds
        if (translateX < maxPossibleTranslate) {
            translateX = maxPossibleTranslate;
        }
        
        // Ensure we don't go past the start
        if (translateX > 0) {
            translateX = 0;
            currentSlide = 0;
        }
        
        slider.style.transform = `translateX(${translateX}px)`;
        slider.style.transition = 'transform 0.1s ease-out';
        
        // Update button states - check if we're at the actual end position
        const isAtStart = currentSlide === 0 || translateX >= -1; // Allow 1px tolerance
        const isAtActualEnd = Math.abs(translateX - maxPossibleTranslate) < 2; // Check if we're at max position
        
        prevBtn.style.opacity = isAtStart ? '0.5' : '1';
        nextBtn.style.opacity = isAtActualEnd ? '0.5' : '1';
        
        // Disable buttons at limits
        prevBtn.disabled = isAtStart;
        nextBtn.disabled = isAtActualEnd;
    }
    
    // Go to specific slide
    function goToSlide(slideIndex) {
        const maxSlide = getMaxSlide();
        currentSlide = Math.max(0, Math.min(slideIndex, maxSlide));
        updateSlider();
    }
    
    // Next slide - move one category box at a time
    function nextSlide(e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        
        const itemWidth = items[0] ? items[0].offsetWidth + 36 : 126;
        const wrapperWidth = wrapper.offsetWidth;
        const sliderWidth = slider.scrollWidth;
        const maxPossibleTranslate = -(sliderWidth - wrapperWidth);
        const currentTranslate = -currentSlide * itemWidth;
        
        // Move one item forward
        currentSlide++;
        
        // updateSlider will clamp if needed
        updateSlider();
    }
    
    // Previous slide - move one category box at a time
    function prevSlide(e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        
        // Move one item backward
        if (currentSlide > 0) {
            currentSlide--;
        }
        
        updateSlider();
    }
    
    // Event listeners
    prevBtn.addEventListener('click', prevSlide);
    nextBtn.addEventListener('click', nextSlide);
    
    // Fast mouse drag support for desktop
    let mouseDown = false;
    let mouseStartX = 0;
    let mouseCurrentX = 0;
    
    wrapper.addEventListener('mousedown', (e) => {
        // Don't trigger drag if clicking on a category item link
        const target = e.target;
        const categoryItem = target.closest('.catalog-top-categories__item');
        if (categoryItem) {
            // Allow the link to work normally
            return;
        }
        
        mouseDown = true;
        mouseStartX = e.clientX;
        slider.style.transition = 'none';
        wrapper.style.cursor = 'grabbing';
        e.preventDefault();
    });
    
    wrapper.addEventListener('mousemove', (e) => {
        if (mouseDown) {
            mouseCurrentX = e.clientX;
            const itemWidth = items[0].offsetWidth + 36;
            const dragOffset = (mouseCurrentX - mouseStartX) / itemWidth;
            const baseTranslateX = -currentSlide * itemWidth;
            const dragTranslateX = baseTranslateX + (dragOffset * itemWidth);
            slider.style.transform = `translateX(${dragTranslateX}px)`;
        }
    });
    
    wrapper.addEventListener('mouseup', (e) => {
        if (mouseDown) {
            mouseDown = false;
            const deltaX = mouseStartX - mouseCurrentX;
            const itemWidth = items[0].offsetWidth + 36;
            const threshold = 50;
            
            // Calculate how many categories to move based on drag distance
            if (Math.abs(deltaX) > threshold) {
                const categoriesToMove = Math.round(deltaX / itemWidth);
                const newSlide = currentSlide + categoriesToMove;
                const maxSlide = getMaxSlide();
                
                // Clamp to valid range
                currentSlide = Math.max(0, Math.min(newSlide, maxSlide));
                updateSlider();
            }
            // If no significant drag, stay exactly where it is - no snap back
            
            slider.style.transition = 'transform 0.1s ease-out';
            wrapper.style.cursor = 'grab';
        }
    });
    
    wrapper.addEventListener('mouseleave', () => {
        if (mouseDown) {
            mouseDown = false;
            // Don't snap back - stay where it is
            slider.style.transition = 'transform 0.1s ease-out';
            wrapper.style.cursor = 'grab';
        }
    });
    
    // Touch/swipe support for mobile - really fast scrolling
    let startX = 0;
    let startY = 0;
    let isScrolling = false;
    let touchStartTime = 0;
    let isDragging = false;
    let dragStartX = 0;
    let currentDragX = 0;
    
    wrapper.addEventListener('touchstart', (e) => {
        // Don't trigger drag if clicking on a category item link
        const target = e.target;
        const categoryItem = target.closest('.catalog-top-categories__item');
        if (categoryItem) {
            // Allow the link to work normally
            return;
        }
        
        startX = e.touches[0].clientX;
        startY = e.touches[0].clientY;
        isScrolling = false;
        touchStartTime = Date.now();
        isDragging = true;
        dragStartX = startX;
        // Disable transition during touch for instant response
        slider.style.transition = 'none';
    });
    
    wrapper.addEventListener('touchmove', (e) => {
        if (!isScrolling) {
            const deltaX = Math.abs(e.touches[0].clientX - startX);
            const deltaY = Math.abs(e.touches[0].clientY - startY);
            isScrolling = deltaY > deltaX;
        }
        
        if (isDragging && !isScrolling) {
            currentDragX = e.touches[0].clientX;
            const itemWidth = items[0].offsetWidth + 36;
            const dragOffset = (currentDragX - dragStartX) / itemWidth;
            const baseTranslateX = -currentSlide * itemWidth;
            const dragTranslateX = baseTranslateX + (dragOffset * itemWidth);
            slider.style.transform = `translateX(${dragTranslateX}px)`;
        }
        
        // Prevent default scrolling
        e.preventDefault();
    }, { passive: false });
    
    wrapper.addEventListener('touchend', (e) => {
        isDragging = false;
        
        if (!isScrolling) {
            const endX = e.changedTouches[0].clientX;
            const deltaX = startX - endX;
            const itemWidth = items[0].offsetWidth + 36;
            const threshold = 30;
            
            // Calculate how many categories to move based on drag distance
            if (Math.abs(deltaX) > threshold) {
                const categoriesToMove = Math.round(deltaX / itemWidth);
                const newSlide = currentSlide + categoriesToMove;
                const maxSlide = getMaxSlide();
                
                // Clamp to valid range
                currentSlide = Math.max(0, Math.min(newSlide, maxSlide));
                updateSlider();
            }
            // If no significant swipe, stay exactly where it is - no snap back
        }
        
        // Re-enable fast transition
        slider.style.transition = 'transform 0.1s ease-out';
    });
    
    // Handle window resize
    window.addEventListener('resize', () => {
        const newItemsPerView = getItemsPerView();
        if (newItemsPerView !== itemsPerView) {
            itemsPerView = newItemsPerView;
            // Don't reset to 0, keep current position if possible
            const maxSlide = getMaxSlide();
            currentSlide = Math.min(currentSlide, maxSlide);
            updateSlider();
        }
    });
    
    // Initialize
    itemsPerView = getItemsPerView();
    updateSlider();
    
});

</script>

<?php
?>

<?php if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'archive' ) ) { ?>

<!-- Hero Slider Section - Full Width -->
<?php echo do_shortcode('[haloo_hero_slider]'); ?>

<div class="site-content-container">
    <?php do_action( 'shopwell_before_open_blog_main' ); ?>
    
    <div id="primary" class="content-area">
        <main id="main" class="site-main">
            
            <!-- Hero Section -->
            <section class="page-header">
            <div class="page-header-content">
                        <?php
                        // Display the page content from WordPress editor
                        if (have_posts()) {
                            while (have_posts()) {
                                the_post();
                                ?>
                                <div class="entry-content">
                                    <?php the_content(); ?>
                                </div>
                                <?php
                            }
                            wp_reset_postdata();
                        }
                        ?>
                        
                        <!-- Subtle Benefits Bar -->
                        <div class="subtle-benefits">
                            <div class="benefit-item-subtle">
                                <div class="benefit-icon" style="color: #3fb981">%</div>
                                <span>Prețuri accesibile</span>
                            </div>
                            <div class="benefit-item-subtle">
                                <div class="benefit-icon">📅</div>
                                <span>Până la 60 de rate</span>
                            </div>
                            <div class="benefit-item-subtle">
                                <div class="benefit-icon">🛡️</div>
                                <span>Garanție 2 ani</span>
                            </div>
                            <div class="benefit-item-subtle">
                                <div class="benefit-icon">↩️</div>
                                <span>Retur 30 zile</span>
                            </div>
                            <div class="benefit-item-subtle">
                                <div class="benefit-icon">🚚</div>
                                <span>Livrare 1-2 zile</span>
                            </div>
                            <div class="benefit-item-subtle">
                                <div class="benefit-icon">🌳</div>
                                <span>Protejăm planeta</span>
                            </div>
                        </div>
                    </div>
            </section>

            <!-- Top Categories Bar -->
            <?php if (is_front_page()) : ?>
            <section class="catalog-top-categories catalog-top-categories__layout-v1">
                <div class="catalog-top-categories__wrapper">
                    <button class="catalog-top-categories__nav catalog-top-categories__nav--prev" aria-label="Previous categories">
                        <svg viewBox="0 0 24 24">
                            <path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/>
                        </svg>
                    </button>
                    <button class="catalog-top-categories__nav catalog-top-categories__nav--next" aria-label="Next categories">
                        <svg viewBox="0 0 24 24">
                            <path d="M8.59 16.59L10 18l6-6-6-6-1.41 1.41L13.17 12z"/>
                        </svg>
                    </button>
                    <div class="catalog-top-categories__slider">
                    <?php
                    // "All" category
                    echo sprintf(
                        '<a class="catalog-top-categories__item %s" href="%s">
                            <span class="catalog-top-categories__image all text">%s</span>
                            <span class="catalog-top-categories__text">%s</span>
                        </a>',
                        'active',
                        esc_url( wc_get_page_permalink( 'shop' ) ),
                        esc_html__( 'TOT', 'shopwell' ),
                        esc_html__( 'Cumpără tot', 'shopwell' )
                    );

                    // "New" category
                    echo sprintf(
                        '<a class="catalog-top-categories__item" href="%s">
                            <span class="catalog-top-categories__image new text">%s</span>
                            <span class="catalog-top-categories__text">%s</span>
                        </a>',
                        esc_url( wc_get_page_permalink( 'shop' ) ) . '?orderby=date',
                        esc_html__( 'NOU', 'shopwell' ),
                        esc_html__( 'Produse noi', 'shopwell' )
                    );

                    // "Sale" category
                    echo sprintf(
                        '<a class="catalog-top-categories__item" href="%s">
                            <span class="catalog-top-categories__image sale text">%s</span>
                            <span class="catalog-top-categories__text">%s</span>
                        </a>',
                        esc_url( wc_get_page_permalink( 'shop' ) ) . '?on_sale=1',
                        esc_html__( 'REDUCERE', 'shopwell' ),
                        esc_html__( 'Reducere', 'shopwell' )
                    );

                    // Get product categories ordered by count
                    $product_categories = get_terms(array(
                        'taxonomy' => 'product_cat',
                        'hide_empty' => true,
                        'orderby' => 'count',
                        'order' => 'DESC',
                        'parent' => 0
                    ));

                    if ($product_categories && !is_wp_error($product_categories)) {
                        foreach ($product_categories as $category) {
                            $thumbnail_id = get_term_meta($category->term_id, 'thumbnail_id', true);
                            $image_url = $thumbnail_id ? wp_get_attachment_url($thumbnail_id) : wc_placeholder_img_src();
                            
                            $term_img = !empty($image_url) ? 
                                '<img class="catalog-top-categories__image" src="' . esc_url($image_url) . '" alt="' . esc_attr($category->name) . '" />' : 
                                '<span class="catalog-top-categories__image">' . esc_attr($category->name) . '</span>';

                            // Use the working URL format with query parameters
                            $category_url = wc_get_page_permalink('shop') . '?product_cat=' . $category->slug . '&filter=1';
                            
                            echo sprintf(
                                '<a class="catalog-top-categories__item" href="%s">
                                    %s
                                    <span class="catalog-top-categories__text">%s</span>
                                </a>',
                                esc_url( $category_url ),
                                $term_img,
                                esc_html( $category->name )
                            );
                        }
                    }
                    ?>
                    </div>
                </div>
            </section>
            <?php endif; ?>

            <!-- Featured Products Section -->
            <section class="woocommerce">
                <div class="container" style="max-width: 1440px; margin: 0 auto 24px; padding: 0 24px; box-sizing: border-box;">
                    <h2 class="section-title">Telefoane Recomandate</h2>
                    <ul id="quiz-recommended-products" class="products columns-4">
                        <li class="loading-products" style="text-align: center; padding: 40px; grid-column: 1 / -1;">
                            <p>Se încarcă produsele recomandate...</p>
                        </li>
                    </ul>
                </div>
            </section>
            
            <style>
            #quiz-recommended-products.products {
                display: grid !important;
                grid-template-columns: repeat(4, 1fr) !important;
                gap: 20px !important;
                list-style: none !important;
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
                box-sizing: border-box !important;
            }
            
            #quiz-recommended-products .product {
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
                box-sizing: border-box !important;
            }
            
            #quiz-recommended-products .product-inner {
                background: #fff;
                border-radius: 8px;
                overflow: hidden;
                transition: box-shadow 0.3s ease;
                height: 100%;
                display: flex;
                flex-direction: column;
                width: 100%;
                max-width: 100%;
                box-sizing: border-box;
            }
            
            #quiz-recommended-products .product-inner:hover {
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            }
            
            #quiz-recommended-products .product-thumbnail {
                position: relative;
                padding-top: 100%;
                overflow: hidden;
                background: #f5f5f5;
                width: 100%;
                max-width: 100%;
                box-sizing: border-box;
            }
            
            #quiz-recommended-products .product-thumbnail a {
                display: block;
                width: 100%;
                height: 100%;
                position: absolute;
                top: 0;
                left: 0;
            }
            
            #quiz-recommended-products .product-thumbnail img {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                object-fit: cover;
                max-width: 100%;
                display: block;
            }
            
            #quiz-recommended-products .product-summary {
                padding: 15px;
                flex: 1;
                display: flex;
                flex-direction: column;
                width: 100%;
                box-sizing: border-box;
            }
            
            #quiz-recommended-products .woocommerce-loop-product__title {
                margin: 0;
                font-size: 16px;
                font-weight: 500;
                line-height: 1.4;
                word-wrap: break-word;
                overflow-wrap: break-word;
            }
            
            #quiz-recommended-products .woocommerce-loop-product__title a {
                color: #2A322F;
                text-decoration: none;
                word-wrap: break-word;
                overflow-wrap: break-word;
            }
            
            #quiz-recommended-products .woocommerce-loop-product__title a:hover {
                color: #66fa95;
            }
            
            #quiz-recommended-products .recommendation-badge {
                background: #66fa95;
                color: #2A322F;
                padding: 5px 10px;
                border-radius: 15px;
                display: inline-block;
                margin: 0 0 10px 0;
                font-size: 12px;
                font-weight: 500;
                width: fit-content;
                max-width: 100%;
                box-sizing: border-box;
            }
            
            #quiz-recommended-products .price {
                font-size: 18px;
                font-weight: 600;
                color: #2A322F;
                margin-bottom: 15px;
                word-wrap: break-word;
                overflow-wrap: break-word;
            }
            
            #quiz-recommended-products .button {
                background:rgba(132, 177, 160, 0);
                color: #2A322F;
                border-radius: 8px;
                border: 1px solid #2A322F;
                text-decoration: none;
                display: block;
                text-align: center;
                font-weight: 500;
                transition: background 0.3s ease;
                margin-top: auto;
                min-width: auto !important;
                padding: 0;
                min-height: 44px;
                line-height: 44px;
                box-sizing: border-box;
            }
            
            @media (max-width: 768px) {
                section.woocommerce .container {
                    padding: 0 10px !important;
                    max-width: 100% !important;
                    box-sizing: border-box !important;
                    width: 100% !important;
                }
                
                #quiz-recommended-products.products {
                    grid-template-columns: repeat(2, 1fr) !important;
                    gap: 10px !important;
                    width: 100% !important;
                    max-width: 100% !important;
                    box-sizing: border-box !important;
                    margin: 0 auto !important;
                    padding: 0 !important;
                    justify-items: stretch !important;
                    display: grid !important;
                }
                
                #quiz-recommended-products .product {
                    width: 100% !important;
                    max-width: 100% !important;
                    margin: 0 !important;
                    padding: 0 !important;
                    box-sizing: border-box !important;
                    justify-self: stretch !important;
                    min-width: 0 !important;
                }
                
                #quiz-recommended-products .product-inner {
                    width: 100% !important;
                    max-width: 100% !important;
                    height: auto !important;
                    min-height: 100%;
                    box-sizing: border-box !important;
                    overflow: hidden !important;
                }
                
                #quiz-recommended-products .product-thumbnail {
                    padding-top: 100%;
                    width: 100%;
                    max-width: 100%;
                    min-width: 0;
                }
                
                #quiz-recommended-products .product-summary {
                    padding: 10px;
                    min-height: auto;
                    width: 100%;
                    box-sizing: border-box;
                    overflow: hidden;
                }
                
                #quiz-recommended-products .woocommerce-loop-product__title {
                    font-size: 13px;
                    line-height: 1.3;
                    word-wrap: break-word;
                    overflow-wrap: break-word;
                    margin-bottom: 6px;
                }
                
                #quiz-recommended-products .recommendation-badge {
                    font-size: 10px;
                    padding: 3px 6px;
                    margin: 0 0 6px 0;
                    max-width: 100%;
                    white-space: nowrap;
                    overflow: hidden;
                    text-overflow: ellipsis;
                }
                
                #quiz-recommended-products .price {
                    font-size: 15px;
                    margin-bottom: 10px;
                    word-wrap: break-word;
                    overflow-wrap: break-word;
                }
                
                #quiz-recommended-products .button {
                    font-size: 13px;
                    padding: 0;
                    min-height: 40px;
                    line-height: 40px;
                    min-width: auto !important;
                    box-sizing: border-box;
                    white-space: nowrap;
                    overflow: hidden;
                    text-overflow: ellipsis;
                }
            }
            </style>
            
            <script>
            jQuery(document).ready(function($) {
                // Load top recommended products from quiz using the AJAX endpoint
                function loadTopRecommendations() {
                    $.ajax({
                        url: '<?php echo admin_url('admin-ajax.php'); ?>',
                        type: 'POST',
                        data: {
                            action: 'quiz_get_top_recommendations'
                        },
                        timeout: 10000,
                        success: function(response) {
                            if (response.success && response.data && response.data.recommendations && response.data.recommendations.length > 0) {
                                renderRecommendations(response.data.recommendations);
                            } else {
                                $('#quiz-recommended-products').html('<p style="text-align: center; padding: 40px;">Nu există recomandări încă.</p>');
                            }
                        },
                        error: function() {
                            $('#quiz-recommended-products').html('<p style="text-align: center; padding: 40px;">Eroare la încărcarea recomandărilor.</p>');
                        }
                    });
                }
                
                function renderRecommendations(recommendations) {
                    var html = '';
                    
                    recommendations.forEach(function(product) {
                        html += '<li class="product type-product">';
                        html += '<div class="product-inner">';
                        
                        // Product image
                        html += '<div class="product-thumbnail">';
                        html += '<a href="' + product.url + '" class="woocommerce-LoopProduct-link woocommerce-loop-product__link">';
                        html += '<img src="' + product.image + '" alt="' + product.name + '" class="attachment-woocommerce_thumbnail size-woocommerce_thumbnail wp-post-image" />';
                        html += '</a>';
                        html += '</div>';
                        
                        // Product summary
                        html += '<div class="product-summary">';
                        
                        // Product title
                        html += '<h2 class="woocommerce-loop-product__title">';
                        html += '<a href="' + product.url + '">' + product.name + '</a>';
                        html += '</h2>';
                        
                        // Recommendation badge (between title and price)
                        if (product.recommendation_count > 0) {
                            var recommendationText = product.recommendation_count === 1 
                                ? 'O recomandare' 
                                : product.recommendation_count + ' recomandări';
                            
                            html += '<div class="recommendation-badge" style="background: #66fa95; color: #2A322F; padding: 5px 10px; border-radius: 15px; display: inline-block; margin: 10px 0; font-size: 12px; font-weight: 500;">';
                            html += recommendationText;
                            html += '</div>';
                        }
                        
                        // Product price
                        html += '<span class="price">' + product.price + '</span>';
                        
                        // View product button (not add to cart)
                        html += '<a href="' + product.url + '" class="button product_type_simple">Vezi detalii</a>';
                        
                        html += '</div>'; // .product-summary
                        html += '</div>'; // .product-inner
                        html += '</li>';
                    });
                    
                    $('#quiz-recommended-products').html(html);
                }
                
                // Load on page load
                loadTopRecommendations();
            });
            </script>

            <!-- Testimonials Section -->
            <section class="testimonials-section">
                <div class="container">
                    <div class="testimonials-header">
                        <span class="testimonials-label">RECENZII</span>
                        <h2 class="testimonials-title">Ce spun clienții despre <span class="brand-name">Haloo</span></h2>
                    </div>
                    
                    <!-- First Carousel (Moving Left) -->
                    <div class="testimonials-carousel testimonials-carousel-left">
                        <div class="carousel-track">
                            <div class="testimonial-item">
                                <div class="testimonial-content">
                                    <div class="testimonial-author">
                                        <div class="author-avatar">
                                            <img src="https://images.unsplash.com/photo-1494790108755-2616b612b786?w=60&h=60&fit=crop&crop=face" alt="Maria Popescu">
                                        </div>
                                        <div class="author-info">
                                            <h4 class="author-name">Maria Popescu</h4>
                                            <p class="author-title">Client din București</p>
                                        </div>
                                    </div>
                                    <p class="testimonial-text">"Serviciul excelent și produsele de calitate! Am primit telefonul refurbished exact cum era descris și livrarea a fost foarte rapidă. Recomand cu încredere!"</p>
                                    <div class="testimonial-rating">
                                        <span class="star">★</span>
                                        <span class="star">★</span>
                                        <span class="star">★</span>
                                        <span class="star">★</span>
                                        <span class="star">★</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="testimonial-item">
                                <div class="testimonial-content">
                                    <div class="testimonial-author">
                                        <div class="author-avatar">
                                            <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=60&h=60&fit=crop&crop=face" alt="Alexandru Ionescu">
                                        </div>
                                        <div class="author-info">
                                            <h4 class="author-name">Alexandru Ionescu</h4>
                                            <p class="author-title">Client din Cluj-Napoca</p>
                                        </div>
                                    </div>
                                    <p class="testimonial-text">"Garanția de 2 ani și prețurile accesibile m-au convins să cumpăr de aici. Telefoanele refurbished arată ca noi și funcționează perfect!"</p>
                                    <div class="testimonial-rating">
                                        <span class="star">★</span>
                                        <span class="star">★</span>
                                        <span class="star">★</span>
                                        <span class="star">★</span>
                                        <span class="star">★</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="testimonial-item">
                                <div class="testimonial-content">
                                    <div class="testimonial-author">
                                        <div class="author-avatar">
                                            <img src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=60&h=60&fit=crop&crop=face" alt="Elena Dumitrescu">
                                        </div>
                                        <div class="author-info">
                                            <h4 class="author-name">Elena Dumitrescu</h4>
                                            <p class="author-title">Client din Timișoara</p>
                                        </div>
                                    </div>
                                    <p class="testimonial-text">"Am cumpărat un iPhone refurbished și arată ca nou! Procesul de comandă a fost simplu și am primit confirmarea rapid. Calitatea este excepțională!"</p>
                                    <div class="testimonial-rating">
                                        <span class="star">★</span>
                                        <span class="star">★</span>
                                        <span class="star">★</span>
                                        <span class="star">★</span>
                                        <span class="star">★</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="testimonial-item">
                                <div class="testimonial-content">
                                    <div class="testimonial-author">
                                        <div class="author-avatar">
                                            <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=60&h=60&fit=crop&crop=face" alt="Cristian Popescu">
                                        </div>
                                        <div class="author-info">
                                            <h4 class="author-name">Cristian Popescu</h4>
                                            <p class="author-title">Client din Iași</p>
                                        </div>
                                    </div>
                                    <p class="testimonial-text">"Calitatea telefoanelor refurbished este excepțională! Am fost impresionat de atenția la detalii și de serviciul post-vânzare."</p>
                                    <div class="testimonial-rating">
                                        <span class="star">★</span>
                                        <span class="star">★</span>
                                        <span class="star">★</span>
                                        <span class="star">★</span>
                                        <span class="star">★</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Duplicate content for seamless loop -->
                            <div class="testimonial-item">
                                <div class="testimonial-content">
                                    <div class="testimonial-author">
                                        <div class="author-avatar">
                                            <img src="https://images.unsplash.com/photo-1494790108755-2616b612b786?w=60&h=60&fit=crop&crop=face" alt="Maria Popescu">
                                        </div>
                                        <div class="author-info">
                                            <h4 class="author-name">Maria Popescu</h4>
                                            <p class="author-title">Client din București</p>
                                        </div>
                                    </div>
                                    <p class="testimonial-text">"Serviciul excelent și produsele de calitate! Am primit telefonul refurbished exact cum era descris și livrarea a fost foarte rapidă. Recomand cu încredere!"</p>
                                    <div class="testimonial-rating">
                                        <span class="star">★</span>
                                        <span class="star">★</span>
                                        <span class="star">★</span>
                                        <span class="star">★</span>
                                        <span class="star">★</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="testimonial-item">
                                <div class="testimonial-content">
                                    <div class="testimonial-author">
                                        <div class="author-avatar">
                                            <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=60&h=60&fit=crop&crop=face" alt="Alexandru Ionescu">
                                        </div>
                                        <div class="author-info">
                                            <h4 class="author-name">Alexandru Ionescu</h4>
                                            <p class="author-title">Client din Cluj-Napoca</p>
                                        </div>
                                    </div>
                                    <p class="testimonial-text">"Garanția de 2 ani și prețurile accesibile m-au convins să cumpăr de aici. Telefoanele refurbished arată ca noi și funcționează perfect!"</p>
                                    <div class="testimonial-rating">
                                        <span class="star">★</span>
                                        <span class="star">★</span>
                                        <span class="star">★</span>
                                        <span class="star">★</span>
                                        <span class="star">★</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="testimonial-item">
                                <div class="testimonial-content">
                                    <div class="testimonial-author">
                                        <div class="author-avatar">
                                            <img src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=60&h=60&fit=crop&crop=face" alt="Elena Dumitrescu">
                                        </div>
                                        <div class="author-info">
                                            <h4 class="author-name">Elena Dumitrescu</h4>
                                            <p class="author-title">Client din Timișoara</p>
                                        </div>
                                    </div>
                                    <p class="testimonial-text">"Am cumpărat un iPhone refurbished și arată ca nou! Procesul de comandă a fost simplu și am primit confirmarea rapid. Calitatea este excepțională!"</p>
                                    <div class="testimonial-rating">
                                        <span class="star">★</span>
                                        <span class="star">★</span>
                                        <span class="star">★</span>
                                        <span class="star">★</span>
                                        <span class="star">★</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="testimonial-item">
                                <div class="testimonial-content">
                                    <div class="testimonial-author">
                                        <div class="author-avatar">
                                            <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=60&h=60&fit=crop&crop=face" alt="Cristian Popescu">
                                        </div>
                                        <div class="author-info">
                                            <h4 class="author-name">Cristian Popescu</h4>
                                            <p class="author-title">Client din Iași</p>
                                        </div>
                                    </div>
                                    <p class="testimonial-text">"Calitatea telefoanelor refurbished este excepțională! Am fost impresionat de atenția la detalii și de serviciul post-vânzare."</p>
                                    <div class="testimonial-rating">
                                        <span class="star">★</span>
                                        <span class="star">★</span>
                                        <span class="star">★</span>
                                        <span class="star">★</span>
                                        <span class="star">★</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Second Carousel (Moving Right) -->
                    <div class="testimonials-carousel testimonials-carousel-right">
                        <div class="carousel-track">
                            <div class="testimonial-item">
                                <div class="testimonial-content">
                                    <div class="testimonial-author">
                                        <div class="author-avatar">
                                            <img src="https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=60&h=60&fit=crop&crop=face" alt="Ana Maria">
                                        </div>
                                        <div class="author-info">
                                            <h4 class="author-name">Ana Maria</h4>
                                            <p class="author-title">Client din Constanța</p>
                                        </div>
                                    </div>
                                    <p class="testimonial-text">"Felicitări echipei! Am primit un telefon refurbished de calitate superioară care a depășit toate așteptările mele. Recomand cu încredere!"</p>
                                    <div class="testimonial-rating">
                                        <span class="star">★</span>
                                        <span class="star">★</span>
                                        <span class="star">★</span>
                                        <span class="star">★</span>
                                        <span class="star">★</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="testimonial-item">
                                <div class="testimonial-content">
                                    <div class="testimonial-author">
                                        <div class="author-avatar">
                                            <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=60&h=60&fit=crop&crop=face" alt="Mihai Radu">
                                        </div>
                                        <div class="author-info">
                                            <h4 class="author-name">Mihai Radu</h4>
                                            <p class="author-title">Client din Brașov</p>
                                        </div>
                                    </div>
                                    <p class="testimonial-text">"Impressionat nu descrie suficient! Telefonul refurbished pe care l-am cumpărat este perfect. Această echipă înțelege perfecțiunea."</p>
                                    <div class="testimonial-rating">
                                        <span class="star">★</span>
                                        <span class="star">★</span>
                                        <span class="star">★</span>
                                        <span class="star">★</span>
                                        <span class="star">★</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="testimonial-item">
                                <div class="testimonial-content">
                                    <div class="testimonial-author">
                                        <div class="author-avatar">
                                            <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=60&h=60&fit=crop&crop=face" alt="Ioana Stan">
                                        </div>
                                        <div class="author-info">
                                            <h4 class="author-name">Ioana Stan</h4>
                                            <p class="author-title">Client din Oradea</p>
                                        </div>
                                    </div>
                                    <p class="testimonial-text">"Lucrul cu această echipă a fost fantastic! Am primit un telefon refurbished care mi-a depășit toate așteptările. Recomand cu căldură!"</p>
                                    <div class="testimonial-rating">
                                        <span class="star">★</span>
                                        <span class="star">★</span>
                                        <span class="star">★</span>
                                        <span class="star">★</span>
                                        <span class="star">★</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="testimonial-item">
                                <div class="testimonial-content">
                                    <div class="testimonial-author">
                                        <div class="author-avatar">
                                            <img src="https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?w=60&h=60&fit=crop&crop=face" alt="Andrei Pop">
                                        </div>
                                        <div class="author-info">
                                            <h4 class="author-name">Andrei Pop</h4>
                                            <p class="author-title">Client din Sibiu</p>
                                        </div>
                                    </div>
                                    <p class="testimonial-text">"Serviciu excepțional și atenție la detalii! Echipa a depășit așteptările mele în toate modurile posibile. Telefoanele refurbished sunt minunate!"</p>
                                    <div class="testimonial-rating">
                                        <span class="star">★</span>
                                        <span class="star">★</span>
                                        <span class="star">★</span>
                                        <span class="star">★</span>
                                        <span class="star">★</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Duplicate content for seamless loop -->
                            <div class="testimonial-item">
                                <div class="testimonial-content">
                                    <div class="testimonial-author">
                                        <div class="author-avatar">
                                            <img src="https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=60&h=60&fit=crop&crop=face" alt="Ana Maria">
                                        </div>
                                        <div class="author-info">
                                            <h4 class="author-name">Ana Maria</h4>
                                            <p class="author-title">Client din Constanța</p>
                                        </div>
                                    </div>
                                    <p class="testimonial-text">"Felicitări echipei! Am primit un telefon refurbished de calitate superioară care a depășit toate așteptările mele. Recomand cu încredere!"</p>
                                    <div class="testimonial-rating">
                                        <span class="star">★</span>
                                        <span class="star">★</span>
                                        <span class="star">★</span>
                                        <span class="star">★</span>
                                        <span class="star">★</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="testimonial-item">
                                <div class="testimonial-content">
                                    <div class="testimonial-author">
                                        <div class="author-avatar">
                                            <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=60&h=60&fit=crop&crop=face" alt="Mihai Radu">
                                        </div>
                                        <div class="author-info">
                                            <h4 class="author-name">Mihai Radu</h4>
                                            <p class="author-title">Client din Brașov</p>
                                        </div>
                                    </div>
                                    <p class="testimonial-text">"Impressionat nu descrie suficient! Telefonul refurbished pe care l-am cumpărat este perfect. Această echipă înțelege perfecțiunea."</p>
                                    <div class="testimonial-rating">
                                        <span class="star">★</span>
                                        <span class="star">★</span>
                                        <span class="star">★</span>
                                        <span class="star">★</span>
                                        <span class="star">★</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="testimonial-item">
                                <div class="testimonial-content">
                                    <div class="testimonial-author">
                                        <div class="author-avatar">
                                            <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=60&h=60&fit=crop&crop=face" alt="Ioana Stan">
                                        </div>
                                        <div class="author-info">
                                            <h4 class="author-name">Ioana Stan</h4>
                                            <p class="author-title">Client din Oradea</p>
                                        </div>
                                    </div>
                                    <p class="testimonial-text">"Lucrul cu această echipă a fost fantastic! Am primit un telefon refurbished care mi-a depășit toate așteptările. Recomand cu căldură!"</p>
                                    <div class="testimonial-rating">
                                        <span class="star">★</span>
                                        <span class="star">★</span>
                                        <span class="star">★</span>
                                        <span class="star">★</span>
                                        <span class="star">★</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="testimonial-item">
                                <div class="testimonial-content">
                                    <div class="testimonial-author">
                                        <div class="author-avatar">
                                            <img src="https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?w=60&h=60&fit=crop&crop=face" alt="Andrei Pop">
                                        </div>
                                        <div class="author-info">
                                            <h4 class="author-name">Andrei Pop</h4>
                                            <p class="author-title">Client din Sibiu</p>
                                        </div>
                                    </div>
                                    <p class="testimonial-text">"Serviciu excepțional și atenție la detalii! Echipa a depășit așteptările mele în toate modurile posibile. Telefoanele refurbished sunt minunate!"</p>
                                    <div class="testimonial-rating">
                                        <span class="star">★</span>
                                        <span class="star">★</span>
                                        <span class="star">★</span>
                                        <span class="star">★</span>
                                        <span class="star">★</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                </div>
            </section>

            <!-- Quiz Section -->
            <?php
            // Find the quiz page
            $quiz_pages = get_pages(array(
                'meta_key' => '_wp_page_template',
                'meta_value' => 'page-quiz.php',
                'number' => 1
            ));
            
            $quiz_url = '#';
            if (!empty($quiz_pages)) {
                $quiz_url = get_permalink($quiz_pages[0]->ID);
            } else {
                // Fallback: try to find by slug
                $quiz_page = get_page_by_path('quiz');
                if ($quiz_page) {
                    $quiz_url = get_permalink($quiz_page->ID);
                }
            }
            ?>
            <section class="quiz-cta-section">
                <div class="container">
                    <div class="quiz-cta-content">
                        <div class="quiz-cta-icon">
                            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 17h-2v-2h2v2zm2.07-7.75l-.9.92C13.45 12.9 13 13.5 13 15h-2v-.5c0-1.1.45-2.1 1.17-2.83l1.24-1.26c.37-.36.59-.86.59-1.41 0-1.1-.9-2-2-2s-2 .9-2 2H8c0-2.21 1.79-4 4-4s4 1.79 4 4c0 .88-.36 1.68-.93 2.25z" fill="currentColor"/>
                            </svg>
                        </div>
                        <h2 class="quiz-cta-title">Nu știi ce telefon să alegi?</h2>
                        <p class="quiz-cta-description">Răspunde la câteva întrebări simple și îți vom recomanda cel mai potrivit smartphone refurbished pentru tine</p>
                        <a href="<?php echo esc_url($quiz_url); ?>" class="quiz-cta-button">
                            <span>Începe</span>
                            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M8.59 16.59L10 18l6-6-6-6-1.41 1.41L13.17 12z" fill="currentColor"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </section>

            <!-- Latest Blog Posts -->
            <section class="blog-posts">
                <div class="container">
                    <h2 class="section-title">Ultimele Știri</h2>
                    <div class="posts-grid">
                        <?php
                        $blog_posts = new WP_Query(array(
                            'post_type' => 'post',
                            'posts_per_page' => 3,
                            'post_status' => 'publish'
                        ));
                        
                        if ($blog_posts->have_posts()) {
                            while ($blog_posts->have_posts()) {
                                $blog_posts->the_post();
                                ?>
                                <article class="post">
                                    <?php if (has_post_thumbnail()) : ?>
                                        <div class="post-thumbnail">
                                            <a href="<?php the_permalink(); ?>">
                                                <?php the_post_thumbnail('medium'); ?>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                    <div class="post-content">
                                        <h3 class="entry-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                                        <div class="entry-meta">
                                            <span class="posted-on"><?php echo get_the_date(); ?></span>
                                        </div>
                                        <div class="entry-summary">
                                            <p><?php echo wp_trim_words(get_the_excerpt(), 20); ?></p>
                                        </div>
                                    </div>
                                </article>
                                <?php
                            }
                            wp_reset_postdata();
                        } else {
                            // Fallback content
                            ?>
                            <div class="post">
                                <div class="post-content">
                                    <h3 class="entry-title">Bine ai venit pe site-ul nostru!</h3>
                                    <div class="entry-summary">
                                        <p>Descoperă produsele noastre de calitate și ofertele speciale.</p>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                </div>
            </section>

        </main>
    </div>
    
    <?php do_action( 'shopwell_after_close_blog_main' ); ?>
    <?php get_sidebar(); ?>
</div>
<?php } ?>


<?php
get_footer();
