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
        console.log('Carousel track not found');
        return;
    }
    
    console.log('Carousel initialized');
    
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
        console.log('Touch start:', touchStartX, touchStartY);
    }, { passive: true });

    track.addEventListener('touchend', e => {
        const touchEndX = e.changedTouches[0].clientX;
        const touchEndY = e.changedTouches[0].clientY;
        const diffX = touchStartX - touchEndX;
        const diffY = touchStartY - touchEndY;
        
        console.log('Touch end - diffX:', diffX, 'diffY:', diffY);
        
        // Only respond to clear horizontal swipes (not vertical scrolling)
        if (Math.abs(diffX) > Math.abs(diffY) && Math.abs(diffX) > 80) {
            const cardWidth = track.querySelector('.testimonial-item').offsetWidth;
            const newScrollLeft = track.scrollLeft + (diffX > 0 ? cardWidth : -cardWidth);
            console.log('Swipe detected - scrolling to:', newScrollLeft);
            
            track.scrollTo({
                left: newScrollLeft,
                behavior: 'smooth'
            });
        } else {
            console.log('No clear swipe detected - staying in place');
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
        console.log('Categories slider elements not found');
        return;
    }
    
    console.log('All slider elements found, initializing...');
    
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
        // Ensure we don't overscroll past the last category
        // If we have fewer items than itemsPerView, maxSlide should be 0
        if (totalItems <= itemsPerView) {
            return 0;
        }
        // Otherwise, allow scrolling until the last item is fully visible
        return totalItems - itemsPerView;
    }
    
    
    // Update slider position
    function updateSlider() {
        // Clamp currentSlide to valid range
        const maxSlide = getMaxSlide();
        currentSlide = Math.max(0, Math.min(currentSlide, maxSlide));
        
        const itemWidth = items[0].offsetWidth + 36;
        const translateX = -currentSlide * itemWidth;
        slider.style.transform = `translateX(${translateX}px)`;
        slider.style.transition = 'transform 0.1s ease-out';
        
        // Use CSS responsive widths instead of dynamic calculation
        // This ensures proper responsive behavior
        
        
        // Update button states
        prevBtn.style.opacity = currentSlide === 0 ? '0.5' : '1';
        nextBtn.style.opacity = currentSlide >= maxSlide ? '0.5' : '1';
        
        // Disable buttons at limits
        prevBtn.disabled = currentSlide === 0;
        nextBtn.disabled = currentSlide >= maxSlide;
    }
    
    // Go to specific slide
    function goToSlide(slideIndex) {
        const maxSlide = getMaxSlide();
        currentSlide = Math.max(0, Math.min(slideIndex, maxSlide));
        updateSlider();
    }
    
    // Next slide
    function nextSlide() {
        const maxSlide = getMaxSlide();
        if (currentSlide < maxSlide) {
            currentSlide++;
            updateSlider();
        }
    }
    
    // Previous slide
    function prevSlide() {
        if (currentSlide > 0) {
            currentSlide--;
            updateSlider();
        }
    }
    
    // Event listeners
    prevBtn.addEventListener('click', prevSlide);
    nextBtn.addEventListener('click', nextSlide);
    
    // Fast mouse drag support for desktop
    let mouseDown = false;
    let mouseStartX = 0;
    let mouseCurrentX = 0;
    
    wrapper.addEventListener('mousedown', (e) => {
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
    
    console.log('Categories slider initialized');
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
                <div class="container">
                    <h2 class="section-title">Telefoane Recomandate</h2>
                    <?php
                    // Use WooCommerce shortcode to get the exact same layout as category page
                    echo do_shortcode('[products limit="8" columns="4" orderby="featured" order="desc"]');
                    ?>
                </div>
            </section>

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
