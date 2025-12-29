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
<?php
// Enqueue homepage-specific script moved from inline block.
wp_enqueue_script(
    'shopwell-homepage',
    get_template_directory_uri() . '/assets/js/homepage.js',
    array(),
    '20250107',
    true
);
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
                        <div class="benefits-ribbon">
                            <div class="subtle-benefits">
                                <div class="benefit-item-subtle">
                                    <div class="benefit-icon"><i class="fa-solid fa-percent" aria-hidden="true"></i></div>
                                    <span>Prețuri accesibile</span>
                                </div>
                                <div class="benefit-item-subtle">
                                    <div class="benefit-icon"><i class="fa-regular fa-calendar-check" aria-hidden="true"></i></div>
                                    <span>Până la 60 de rate</span>
                                </div>
                                <div class="benefit-item-subtle">
                                    <div class="benefit-icon"><i class="fa-solid fa-shield-halved" aria-hidden="true"></i></div>
                                    <span>Garanție 2 ani</span>
                                </div>
                                <div class="benefit-item-subtle">
                                    <div class="benefit-icon"><i class="fa-solid fa-rotate-left" aria-hidden="true"></i></div>
                                    <span>Retur 30 zile</span>
                                </div>
                                <div class="benefit-item-subtle">
                                    <div class="benefit-icon"><i class="fa-solid fa-truck-fast" aria-hidden="true"></i></div>
                                    <span>Livrare 1-2 zile</span>
                                </div>
                                <div class="benefit-item-subtle">
                                    <div class="benefit-icon"><i class="fa-solid fa-leaf" aria-hidden="true"></i></div>
                                    <span>Protejăm planeta</span>
                                </div>
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
                        <?php
                        // Get top recommended products directly from database
                        global $wpdb;
                        $table_name = $wpdb->prefix . 'quiz_recommendations';
                        $limit = 8;
                        
                        // Query to get top recommendations with product validation
                        $recommendations = $wpdb->get_results($wpdb->prepare(
                            "SELECT 
                                r.*,
                                p.post_title as product_name,
                                p.post_status
                            FROM {$table_name} r
                            INNER JOIN {$wpdb->posts} p ON r.product_id = p.ID
                            WHERE p.post_status = 'publish' 
                                AND p.post_type = 'product'
                            ORDER BY r.recommendation_count DESC, r.last_recommended DESC
                            LIMIT %d",
                            $limit
                        ));
                        
                        if ($recommendations && !empty($recommendations)) {
                            foreach ($recommendations as $rec) {
                                $product = wc_get_product($rec->product_id);
                                if (!$product || !$product->is_visible()) {
                                    continue; // Skip deleted or hidden products
                                }
                                
                                $variation = $rec->variation_id ? wc_get_product($rec->variation_id) : null;
                                
                                // Get product details
                                $product_name = $product->get_name();
                                $product_url = $variation ? $variation->get_permalink() : get_permalink($rec->product_id);
                                $product_price = $variation ? $variation->get_price_html() : $product->get_price_html();
                                $product_image_id = $variation ? $variation->get_image_id() : $product->get_image_id();
                                $product_image = $product_image_id 
                                    ? wp_get_attachment_image_url($product_image_id, 'woocommerce_thumbnail') 
                                    : wc_placeholder_img_src();
                                
                                // Recommendation badge text
                                $recommendation_text = $rec->recommendation_count === 1 
                                    ? 'O recomandare' 
                                    : $rec->recommendation_count . ' recomandări';
                                ?>
                                <li class="product type-product">
                                    <div class="product-inner">
                                        <!-- Product image -->
                                        <div class="product-thumbnail">
                                            <a href="<?php echo esc_url($product_url); ?>" class="woocommerce-LoopProduct-link woocommerce-loop-product__link">
                                                <img src="<?php echo esc_url($product_image); ?>" alt="<?php echo esc_attr($product_name); ?>" class="attachment-woocommerce_thumbnail size-woocommerce_thumbnail wp-post-image" />
                                            </a>
                                        </div>
                                        
                                        <!-- Product summary -->
                                        <div class="product-summary">
                                            <!-- Product title -->
                                            <h2 class="woocommerce-loop-product__title">
                                                <a href="<?php echo esc_url($product_url); ?>"><?php echo esc_html($product_name); ?></a>
                                            </h2>
                                            
                                            <!-- Recommendation badge -->
                                            <div class="recommendation-badge">
                                                <?php echo esc_html($recommendation_text); ?>
                                            </div>
                                            
                                            <!-- Product price -->
                                            <span class="price"><?php echo $product_price; ?></span>
                                            
                                            <!-- View product button -->
                                            <a href="<?php echo esc_url($product_url); ?>" class="button product_type_simple">Vezi detalii</a>
                                        </div>
                                    </div>
                                </li>
                                <?php
                            }
                        } else {
                            // No recommendations found
                            ?>
                            <li class="no-recommendations" style="text-align: center; padding: 40px; grid-column: 1 / -1;">
                                <p>Nu există recomandări încă.</p>
                            </li>
                            <?php
                        }
                        ?>
                    </ul>
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
