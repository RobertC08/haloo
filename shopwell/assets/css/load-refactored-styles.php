<?php
/**
 * Load Refactored Styles
 * This file handles the loading of all refactored CSS files
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enqueue refactored styles based on page type
 */
function shopwell_enqueue_refactored_styles() {
    // Get the current page template or page type
    $is_shop_page = function_exists('is_shop') && (is_shop() || is_product_category() || is_product_tag() || is_product_taxonomy());
    $is_single_post = is_single() && get_post_type() == 'post';
    $is_category_page = is_category() || is_tag() || is_archive();
    $is_front_page = function_exists('is_front_page') && is_front_page();
    $is_search_page = is_search();
    
    // Check for custom page templates
    $current_template = get_page_template_slug();
    $is_faq_page = $current_template === 'page-faq.php';
    $is_blog_page = $current_template === 'page-blog.php';
    $is_about_page = $current_template === 'page-about.php';
    $is_knowledge_base_page = $current_template === 'page-knowledge-base.php';
    $is_contact_page = $current_template === 'page-contact.php' || 
                       (is_page() && get_the_ID() && get_page_template_slug(get_the_ID()) === 'page-contact.php') ||
                       (is_page() && strpos(get_page_template(), 'page-contact.php') !== false);
    $is_quiz_page = $current_template === 'page-quiz.php';
    $is_terms_page = $current_template === 'page-termeni-si-conditii.php';
    $is_returns_page = $current_template === 'page-retururi-si-inlocuiri.php';
    $is_shipping_page = $current_template === 'page-tarife-si-politici-de-livrare.php';
    $is_refund_page = $current_template === 'page-politica-de-rambursare.php';
    $is_privacy_page = $current_template === 'page-politica-de-confidentialitate.php';
    $is_delivery_page = $current_template === 'page-livrare-si-delivery.php';
    $is_moneyback_page = $current_template === 'page-politica-banii-inapoi.php';
    
    // Base styles that are always loaded
    wp_enqueue_style(
        'shopwell-refactored-base',
        get_template_directory_uri() . '/assets/css/refactored-styles.css',
        array(),
        '1.0.10'
    );
    
    // Load specific styles based on page type
    if ($is_shop_page) {
        wp_enqueue_style(
            'shopwell-woocommerce-styles',
            get_template_directory_uri() . '/assets/css/woocommerce/filter-sidebar.css',
            array('shopwell-refactored-base'),
            '1.0.10'
        );
    }
    
    if ($is_single_post) {
        wp_enqueue_style(
            'shopwell-single-post-styles',
            get_template_directory_uri() . '/assets/css/pages/single-post.css',
            array('shopwell-refactored-base'),
            '1.0.14'
        );
    }
    
    // Single product page
    if (is_product()) {
        wp_enqueue_style(
            'shopwell-single-product-styles',
            get_template_directory_uri() . '/assets/css/pages/single-product.css',
            array('shopwell-refactored-base'),
            '1.0.10'
        );
    }
    
    if ($is_category_page) {
        wp_enqueue_style(
            'shopwell-category-styles',
            get_template_directory_uri() . '/assets/css/pages/category.css',
            array('shopwell-refactored-base'),
            '1.0.14'
        );
    }

    if ($is_front_page) {
        wp_enqueue_style(
            'shopwell-homepage-styles',
            get_template_directory_uri() . '/assets/css/pages/homepage.css',
            array('shopwell-refactored-base'),
            '1.0.11'
        );
    }

    if ($is_search_page) {
        wp_enqueue_style(
            'shopwell-search-styles',
            get_template_directory_uri() . '/assets/css/pages/search.css',
            array('shopwell-refactored-base'),
            '1.0.10'
        );
    }
    
    // Load custom page template styles
    if ($is_faq_page) {
        wp_enqueue_style(
            'shopwell-faq-styles',
            get_template_directory_uri() . '/assets/css/pages/faq.css',
            array('shopwell-refactored-base'),
            '1.0.10'
        );
    }
    
    if ($is_blog_page) {
        wp_enqueue_style(
            'shopwell-blog-styles',
            get_template_directory_uri() . '/assets/css/pages/blog.css',
            array('shopwell-refactored-base'),
            '1.0.14'
        );
    }
    
    if ($is_about_page) {
        wp_enqueue_style(
            'shopwell-about-styles',
            get_template_directory_uri() . '/assets/css/pages/about.css',
            array('shopwell-refactored-base'),
            '1.0.14'
        );
    }
    
    if ($is_knowledge_base_page) {
        wp_enqueue_style(
            'shopwell-knowledge-base-styles',
            get_template_directory_uri() . '/assets/css/pages/knowledge-base.css',
            array('shopwell-refactored-base'),
            '1.0.9'
        );
    }
    
    if ($is_contact_page) {
        wp_enqueue_style(
            'shopwell-contact-styles',
            get_template_directory_uri() . '/assets/css/pages/contact.css',
            array('shopwell-refactored-base'),
            '1.0.14'
        );
    } 
    
    if ($is_quiz_page) {
        wp_enqueue_style(
            'shopwell-quiz-styles',
            get_template_directory_uri() . '/assets/css/pages/quiz.css',
            array('shopwell-refactored-base'),
            '1.0.10'
        );
    }
    
    if ($is_terms_page) {
        wp_enqueue_style(
            'shopwell-terms-styles',
            get_template_directory_uri() . '/assets/css/pages/termeni-si-conditii.css',
            array('shopwell-refactored-base'),
            '1.0.1'
        );
    }
    
    if ($is_returns_page) {
        wp_enqueue_style(
            'shopwell-returns-styles',
            get_template_directory_uri() . '/assets/css/pages/retururi-si-inlocuiri.css',
            array('shopwell-refactored-base'),
            '1.0.0'
        );
    }
    
    if ($is_shipping_page) {
        wp_enqueue_style(
            'shopwell-shipping-styles',
            get_template_directory_uri() . '/assets/css/pages/tarife-si-politici-de-livrare.css',
            array('shopwell-refactored-base'),
            '1.0.0'
        );
    }
    
    if ($is_refund_page) {
        wp_enqueue_style(
            'shopwell-refund-styles',
            get_template_directory_uri() . '/assets/css/pages/politica-de-rambursare.css',
            array('shopwell-refactored-base'),
            '1.0.0'
        );
    }
    
    if ($is_privacy_page) {
        wp_enqueue_style(
            'shopwell-privacy-styles',
            get_template_directory_uri() . '/assets/css/pages/politica-de-confidentialitate.css',
            array('shopwell-refactored-base'),
            '1.0.0'
        );
    }
    
    if ($is_delivery_page) {
        wp_enqueue_style(
            'shopwell-delivery-styles',
            get_template_directory_uri() . '/assets/css/pages/livrare-si-delivery.css',
            array('shopwell-refactored-base'),
            '1.0.0'
        );
    }
    
    if ($is_moneyback_page) {
        wp_enqueue_style(
            'shopwell-moneyback-styles',
            get_template_directory_uri() . '/assets/css/pages/politica-banii-inapoi.css',
            array('shopwell-refactored-base'),
            '1.0.0'
        );
    }
    
    // Footer styles are loaded on all pages
    wp_enqueue_style(
        'shopwell-footer-styles',
        get_template_directory_uri() . '/assets/css/layout/footer.css',
        array('shopwell-refactored-base'),
        '1.0.20'
    );
}

/**
 * Enqueue custom fixes CSS
 * This function loads custom CSS fixes that override theme styles
 */
function shopwell_enqueue_custom_fixes() {
    // Only load on frontend
    if (is_admin()) {
        return;
    }
    
    // Check if homepage styles are loaded (only on front page)
    $dependencies = array('shopwell-refactored-base');
    if (is_front_page() && wp_style_is('shopwell-homepage-styles', 'enqueued')) {
        $dependencies[] = 'shopwell-homepage-styles';
    }
    
    // Enqueue custom fixes CSS with high priority to override theme styles
    wp_enqueue_style(
        'shopwell-custom-fixes',
        get_template_directory_uri() . '/assets/css/custom-fixes.css',
        $dependencies, // Load after theme styles
        '1.0.14' // Version number - increment this when you update the file
    );
}

// Hook into WordPress (high priority to override theme styles)
add_action('wp_enqueue_scripts', 'shopwell_enqueue_refactored_styles', 100);
add_action('wp_enqueue_scripts', 'shopwell_enqueue_custom_fixes', 999); // High priority to load last
