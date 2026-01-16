<?php
/**
 * Functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Shopwell
 */

// Increase memory limit to handle WooCommerce operations
ini_set('memory_limit', '512M');

// Optimize WordPress for better performance
if (!defined('WP_MEMORY_LIMIT')) {
    define('WP_MEMORY_LIMIT', '512M');
}

/**
 * Log messages to file in uploads folder
 * Replaces console.log and error_log for better debugging
 */
function shopwell_log_to_file( $message, $data = null, $type = 'js' ) {
    // Always log for pagination debugging (can be disabled later by uncommenting the check below)
    // if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
    //     return;
    // }
    
    $upload_dir = wp_upload_dir();
    $log_dir = $upload_dir['basedir'] . '/shopwell-logs';
    
    // Create log directory if it doesn't exist
    if ( ! file_exists( $log_dir ) ) {
        wp_mkdir_p( $log_dir );
        // Add .htaccess to protect log files
        $htaccess_content = "deny from all\n";
        @file_put_contents( $log_dir . '/.htaccess', $htaccess_content );
    }
    
    $log_file = $log_dir . '/' . $type . '-debug.log';
    
    // Format message with type prefix
    $type_prefix = strtoupper( $type );
    $log_message = '[' . date( 'Y-m-d H:i:s' ) . '] [' . $type_prefix . '] ' . $message;
    
    if ( $data !== null ) {
        if ( is_array( $data ) || is_object( $data ) ) {
            $log_message .= ' | Data: ' . json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
        } else {
            $log_message .= ' | Data: ' . $data;
        }
    }
    
    $log_message .= PHP_EOL;
    
    // Write to file (append mode)
    @file_put_contents( $log_file, $log_message, FILE_APPEND | LOCK_EX );
    
    // Rotate log file if it gets too large (max 5MB)
    if ( file_exists( $log_file ) && filesize( $log_file ) > 5 * 1024 * 1024 ) {
        $backup_file = $log_dir . '/' . $type . '-debug-' . date( 'Y-m-d-His' ) . '.log';
        @rename( $log_file, $backup_file );
    }
}

/**
 * AJAX handler for JavaScript logging
 */
function shopwell_ajax_log_message() {
    check_ajax_referer( 'shopwell_log_nonce', 'nonce' );
    
    $message = isset( $_POST['message'] ) ? sanitize_text_field( $_POST['message'] ) : '';
    $data = isset( $_POST['data'] ) ? $_POST['data'] : null;
    
    if ( ! empty( $message ) ) {
        shopwell_log_to_file( $message, $data );
        wp_send_json_success();
    } else {
        wp_send_json_error( 'No message provided' );
    }
}
add_action( 'wp_ajax_shopwell_log_message', 'shopwell_ajax_log_message' );
add_action( 'wp_ajax_nopriv_shopwell_log_message', 'shopwell_ajax_log_message' );

// Start session for general functionality
if (!session_id()) {
    session_start();
}

// Close session before REST API requests to avoid blocking
// This allows session to be used for reading/writing, but closes it before HTTP requests
add_action('rest_api_init', function() {
    if (session_id()) {
        session_write_close();
    }
}, 1);

// Close session before AJAX requests that might make HTTP calls
add_action('wp_ajax_send_contact_email', function() {
    if (session_id()) {
        session_write_close();
    }
}, 1);

add_action('wp_ajax_nopriv_send_contact_email', function() {
    if (session_id()) {
        session_write_close();
    }
}, 1);

/**
 * Very early hook to bypass rate limiting and fix COOP for Google Site Kit
 * Runs before most plugins load to catch security headers early
 */
function shopwell_very_early_googlesitekit_fix() {
    // Check if this is a Google Site Kit authentication request
    $request_uri = $_SERVER['REQUEST_URI'] ?? '';
    $is_googlesitekit = (
        strpos($request_uri, 'googlesitekit_auth') !== false ||
        (isset($_GET['action']) && $_GET['action'] === 'googlesitekit_auth') ||
        (isset($_REQUEST['action']) && $_REQUEST['action'] === 'googlesitekit_auth')
    );
    
    if ($is_googlesitekit) {
        // Prevent 429 errors by setting status early
        if (!headers_sent()) {
            http_response_code(200);
        }
        
        // Remove restrictive security headers that block OAuth
        add_filter('wp_headers', function($headers) {
            // Remove restrictive COOP
            if (isset($headers['Cross-Origin-Opener-Policy'])) {
                unset($headers['Cross-Origin-Opener-Policy']);
            }
            if (isset($headers['cross-origin-opener-policy'])) {
                unset($headers['cross-origin-opener-policy']);
            }
            
            // Set permissive COOP for Google Site Kit OAuth popup
            $headers['Cross-Origin-Opener-Policy'] = 'same-origin-allow-popups';
            
            return $headers;
        }, 999, 1);
        
        // Bypass rate limiting filters early
        add_filter('pre_http_request', function($preempt, $args, $url) {
            // Don't block Google Site Kit requests
            if (strpos($url, 'googlesitekit') !== false || strpos($url, 'google-site-kit') !== false) {
                return false; // Allow the request
            }
            return $preempt;
        }, 999, 3);
    }
}
add_action('muplugins_loaded', 'shopwell_very_early_googlesitekit_fix', 1);
add_action('plugins_loaded', 'shopwell_very_early_googlesitekit_fix', 1);

/**
 * Whitelist Google Site Kit authentication to bypass rate limiting
 * Fixes 429 (Too Many Requests) error when authenticating with Google Site Kit
 * Also fixes Cross-Origin-Opener-Policy blocking postMessage
 */
function shopwell_whitelist_googlesitekit_auth() {
    // Check if this is a Google Site Kit authentication request
    $is_googlesitekit = (
        (isset($_GET['action']) && $_GET['action'] === 'googlesitekit_auth') ||
        (isset($_REQUEST['action']) && $_REQUEST['action'] === 'googlesitekit_auth') ||
        (strpos($_SERVER['REQUEST_URI'] ?? '', 'googlesitekit_auth') !== false) ||
        (strpos($_SERVER['REQUEST_URI'] ?? '', 'wp-login.php') !== false && isset($_GET['action']) && $_GET['action'] === 'googlesitekit_auth')
    );
    
    if ($is_googlesitekit) {
        // Set HTTP status to 200 early to prevent 429 errors
        if (!headers_sent()) {
            http_response_code(200);
        }
        
        // Whitelist for Wordfence
        if (class_exists('wordfence')) {
            // Remove IP from Wordfence blocking
            add_filter('wordfence_isWhitelisted', '__return_true', 999);
            add_filter('wordfence_ls_is_json_request', '__return_true', 999);
            add_filter('wordfence_rateLimit', '__return_false', 999);
        }
        
        // Whitelist for iThemes Security
        if (class_exists('ITSEC_Core')) {
            add_filter('itsec_lockout_modules', function($modules) {
                return array();
            }, 999, 1);
            add_filter('itsec_is_ip_whitelisted', '__return_true', 999);
            add_filter('itsec_brute_force_is_whitelisted', '__return_true', 999);
        }
        
        // Whitelist for All In One WP Security
        if (class_exists('AIO_WP_Security')) {
            add_filter('aiowps_is_locked', '__return_false', 999);
            add_filter('aiowps_is_ip_whitelisted', '__return_true', 999);
        }
        
        // Whitelist for Limit Login Attempts Reloaded
        if (class_exists('Limit_Login_Attempts_Reloaded')) {
            add_filter('llar_should_block_request', '__return_false', 999);
            add_filter('llar_is_ip_whitelisted', '__return_true', 999);
        }
        
        // Whitelist for WP Limit Login Attempts
        if (function_exists('wp_limit_login_attempts_check')) {
            add_filter('wp_limit_login_attempts_check', '__return_false', 999);
        }
        
        // Whitelist for Cerber Security
        if (defined('CERBER_VER')) {
            add_filter('cerber_is_ip_allowed', '__return_true', 999);
            add_filter('cerber_block', '__return_false', 999);
        }
        
        // Increase timeout for Google Site Kit requests
        add_filter('http_request_timeout', function($timeout) {
            return 60; // 60 seconds
        }, 999, 1);
        
        // Remove security headers that block OAuth popup communication
        add_action('send_headers', function() {
            if (!headers_sent()) {
                header_remove('Cross-Origin-Opener-Policy');
                header('Cross-Origin-Opener-Policy: same-origin-allow-popups');
            }
        }, 999);
        
        // Log for debugging (can be removed in production)
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Google Site Kit auth request detected - bypassing rate limits and COOP restrictions');
        }
    }
}
add_action('init', 'shopwell_whitelist_googlesitekit_auth', 1);

/**
 * Early hook to catch Google Site Kit requests before plugins load
 * Also fixes Cross-Origin-Opener-Policy (COOP) blocking postMessage
 */
function shopwell_early_googlesitekit_whitelist() {
    $is_googlesitekit = (
        strpos($_SERVER['REQUEST_URI'] ?? '', 'googlesitekit_auth') !== false ||
        (isset($_GET['action']) && $_GET['action'] === 'googlesitekit_auth') ||
        (isset($_REQUEST['action']) && $_REQUEST['action'] === 'googlesitekit_auth')
    );
    
    if ($is_googlesitekit) {
        // Set headers to prevent rate limiting
        if (!headers_sent()) {
            header('X-Google-Site-Kit-Auth: 1');
            
            // Remove or relax Cross-Origin-Opener-Policy to allow postMessage
            // Google Site Kit needs this for OAuth popup communication
            header_remove('Cross-Origin-Opener-Policy');
            header('Cross-Origin-Opener-Policy: same-origin-allow-popups');
            
            // Also set CORS headers if needed
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Authorization');
        }
        
        // Remove rate limiting early
        remove_all_actions('wp_login_failed');
        remove_all_actions('authenticate');
    }
}
add_action('plugins_loaded', 'shopwell_early_googlesitekit_whitelist', 1);

/**
 * Remove security headers that block Google Site Kit OAuth flow
 * Fixes Cross-Origin-Opener-Policy blocking postMessage
 */
function shopwell_remove_blocking_headers_for_googlesitekit() {
    $is_googlesitekit = (
        strpos($_SERVER['REQUEST_URI'] ?? '', 'googlesitekit_auth') !== false ||
        (isset($_GET['action']) && $_GET['action'] === 'googlesitekit_auth') ||
        (isset($_REQUEST['action']) && $_REQUEST['action'] === 'googlesitekit_auth')
    );
    
    if ($is_googlesitekit && !headers_sent()) {
        // Remove restrictive COOP headers set by security plugins
        header_remove('Cross-Origin-Opener-Policy');
        header_remove('Cross-Origin-Embedder-Policy');
        
        // Set permissive COOP for Google Site Kit OAuth
        header('Cross-Origin-Opener-Policy: same-origin-allow-popups');
        
        // Allow CORS for OAuth callback
        if (isset($_SERVER['HTTP_ORIGIN'])) {
            header('Access-Control-Allow-Origin: ' . esc_url_raw($_SERVER['HTTP_ORIGIN']));
            header('Access-Control-Allow-Credentials: true');
        }
    }
}
add_action('send_headers', 'shopwell_remove_blocking_headers_for_googlesitekit', 999);
add_action('wp_headers', 'shopwell_remove_blocking_headers_for_googlesitekit', 999);

/**
 * Bypass rate limiting for Google Site Kit REST API endpoints
 */
function shopwell_bypass_rate_limit_googlesitekit($result, $server, $request) {
    $route = $request->get_route();
    
    // Check if this is a Google Site Kit route
    if (strpos($route, '/google-site-kit/') !== false || 
        strpos($route, '/googlesitekit/') !== false) {
        
        // Remove rate limiting for Google Site Kit API calls
        remove_all_filters('rest_authentication_errors');
        remove_all_filters('rest_pre_serve_request');
        
        return $result;
    }
    
    return $result;
}
add_filter('rest_pre_dispatch', 'shopwell_bypass_rate_limit_googlesitekit', 10, 3);

// Check if theme file exists before requiring it
if (function_exists('get_template_directory')) {
    $theme_file = get_template_directory() . '/inc/theme.php';
    if (file_exists($theme_file)) {
        require_once $theme_file;
        
        // Initialize theme only if class exists
        if (class_exists('\Shopwell\Theme')) {
            \Shopwell\Theme::instance()->init();
        }
    }
}

/**
 * Move filter sidebar to content area instead of footer
 */
function shopwell_move_filter_sidebar_to_content() {
    // Check if WooCommerce is active
    if (!class_exists('WooCommerce')) {
        return;
    }
    
    // Remove filter sidebar from footer
    if (class_exists('\Shopwell\WooCommerce\Catalog')) {
        remove_action( 'wp_footer', array( '\Shopwell\WooCommerce\Catalog', 'filter_sidebar' ) );
    }
    
    // Add it to the beginning of the shop loop
    add_action( 'woocommerce_before_shop_loop', 'shopwell_render_filter_sidebar', 5 );
    
    // Add closing wrapper after shop loop
    add_action( 'woocommerce_after_shop_loop', 'shopwell_close_filter_products_wrapper', 5 );
}
add_action( 'wp_loaded', 'shopwell_move_filter_sidebar_to_content' );

/**
 * Fix price filter widget to update range based on active filters
 */
function shopwell_fix_price_filter_range() {
    // Only on shop pages
    if ( ! function_exists( 'is_shop' ) || ! function_exists( 'is_product_category' ) ) {
        return;
    }
    
    if ( ! ( is_shop() || is_product_category() || is_product_tag() || is_product_taxonomy() ) ) {
        return;
    }
    
    // Hook into WooCommerce price filter widget to modify the price range
    add_filter( 'woocommerce_price_filter_widget_min_amount', 'shopwell_adjust_price_filter_min', 10, 2 );
    add_filter( 'woocommerce_price_filter_widget_max_amount', 'shopwell_adjust_price_filter_max', 10, 2 );
}
add_action( 'wp_loaded', 'shopwell_fix_price_filter_range' );

/**
 * Adjust minimum price for price filter widget based on active filters
 */
function shopwell_adjust_price_filter_min( $min_price, $widget ) {
    return shopwell_get_filtered_price_range( 'min' );
}

/**
 * Adjust maximum price for price filter widget based on active filters
 */
function shopwell_adjust_price_filter_max( $max_price, $widget ) {
    return shopwell_get_filtered_price_range( 'max' );
}

/**
 * Get filtered price range based on active filters
 * Excludes price filters when calculating range for the slider
 */
function shopwell_get_filtered_price_range( $type = 'both' ) {
    // Check if we have active filters (excluding price filters for calculating the range)
    $has_filters = isset( $_GET['product_cat'] ) || 
                   isset( $_GET['filter_pa_stare'] ) || 
                   isset( $_GET['filter_pa_culoare'] ) || 
                   isset( $_GET['filter_pa_capacitate'] ) || 
                   isset( $_GET['filter_pa_memorie'] ) || 
                   isset( $_GET['filter_pa_marca'] );
    
    // Create a new query to get products with current filters applied (excluding price filters)
    // OPTIMIZATION: Use fields => 'ids' to reduce memory usage, then batch load prices
    $args = array(
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => 500, // Limit to 500 products max instead of -1
        'fields'         => 'ids', // Only get IDs to reduce memory
        'meta_query'     => array(
            array(
                'key'     => '_price',
                'value'   => '',
                'compare' => '!='
            )
        )
    );
    
    // Add category filter if present
    if ( isset( $_GET['product_cat'] ) && ! empty( $_GET['product_cat'] ) ) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'product_cat',
                'field'    => 'slug',
                'terms'    => sanitize_text_field( $_GET['product_cat'] ),
            ),
        );
    }
    
    // Add attribute filters if present (EXCLUDE price filters - min_price and max_price)
    $attribute_filters = array();
    foreach ( $_GET as $key => $value ) {
        // Skip price filters when calculating the range for the slider
        if ( $key === 'min_price' || $key === 'max_price' ) {
            continue;
        }
        
        if ( strpos( $key, 'filter_pa_' ) === 0 ) {
            $attribute_name = str_replace( 'filter_pa_', '', $key );
            $attribute_filters[$attribute_name] = sanitize_text_field( $value );
        }
    }
    
    if ( ! empty( $attribute_filters ) ) {
        $tax_query = isset( $args['tax_query'] ) ? $args['tax_query'] : array();
        
        foreach ( $attribute_filters as $attribute_name => $attribute_value ) {
            $taxonomy = 'pa_' . $attribute_name;
            
            if ( taxonomy_exists( $taxonomy ) ) {
                $tax_query[] = array(
                    'taxonomy' => $taxonomy,
                    'field'    => 'slug',
                    'terms'    => $attribute_value,
                );
            }
        }
        
        if ( ! empty( $tax_query ) ) {
            $args['tax_query'] = $tax_query;
        }
    }
    
    // Use WP_Query instead of get_posts to properly handle tax_query
    $query = new WP_Query( $args );
    
    if ( empty( $query->posts ) ) {
        return $type === 'min' ? 0 : 999999;
    }
    
    $prices = array();
    
    // OPTIMIZATION: Use direct meta queries for simple products, batch load variations
    $product_ids = $query->posts;
    
    // Get simple product prices directly from meta (faster than loading full product objects)
    global $wpdb;
    if ( ! empty( $product_ids ) ) {
        $placeholders = implode( ',', array_fill( 0, count( $product_ids ), '%d' ) );
        $simple_product_prices = $wpdb->get_col( $wpdb->prepare(
            "SELECT meta_value FROM {$wpdb->postmeta} 
            WHERE post_id IN ($placeholders)
            AND meta_key = '_price'
            AND CAST(meta_value AS DECIMAL(10,2)) > 0",
            $product_ids
        ) );
    } else {
        $simple_product_prices = array();
    }
    
    if ( $simple_product_prices ) {
        $prices = array_merge( $prices, array_map( 'floatval', $simple_product_prices ) );
    }
    
    // For variable products, we need to check variations
    // Get variable product IDs
    if ( ! empty( $product_ids ) ) {
        $placeholders = implode( ',', array_fill( 0, count( $product_ids ), '%d' ) );
        $variable_product_ids = $wpdb->get_col( $wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta}
            WHERE post_id IN ($placeholders)
            AND meta_key = '_product_type'
            AND meta_value = 'variable'",
            $product_ids
        ) );
    } else {
        $variable_product_ids = array();
    }
    
    // Get variation prices for variable products (batch query)
    if ( ! empty( $variable_product_ids ) ) {
        foreach ( $variable_product_ids as $product_id ) {
            $product = wc_get_product( $product_id );
            if ( $product && $product->is_type( 'variable' ) ) {
                $variation_prices = $product->get_variation_prices();
                if ( ! empty( $variation_prices['price'] ) ) {
                    $prices = array_merge( $prices, array_values( $variation_prices['price'] ) );
                }
            }
        }
    }
    
    wp_reset_postdata();
    
    if ( empty( $prices ) ) {
        return $type === 'min' ? 0 : 999999;
    }
    
    $min_price = min( $prices );
    $max_price = max( $prices );
    
    if ( $type === 'min' ) {
        return $min_price;
    } elseif ( $type === 'max' ) {
        return $max_price;
    } else {
        return array( 'min' => $min_price, 'max' => $max_price );
    }
}

/**
 * Render filter sidebar in content area
 */
function shopwell_render_filter_sidebar() {
    // Check if WooCommerce functions exist
    if ( ! function_exists( 'is_shop' ) || ! function_exists( 'is_product_category' ) || ! function_exists( 'is_product_tag' ) || ! function_exists( 'is_product_taxonomy' ) ) {
        return;
    }
    
    // Only render on shop pages
    if ( ! ( is_shop() || is_product_category() || is_product_tag() || is_product_taxonomy() ) ) {
        return;
    }
    
    // Only render if catalog toolbar layout is 2
    if ( class_exists( '\Shopwell\Helper' ) && \Shopwell\Helper::get_option( 'catalog_toolbar_layout' ) != '2' ) {
        return;
    }
    
    echo '<div class="shopwell-filter-products-wrapper">';
    
    // Safely load template part
    $template_path = 'template-parts/panels/filter-sidebar';
    if (locate_template($template_path . '.php')) {
        get_template_part( $template_path );
    } else {
        echo '<p>Filter sidebar placeholder</p>'; // Fallback if template doesn't exist
    }
}

/**
 * Close the filter-products wrapper after shop loop
 */
function shopwell_close_filter_products_wrapper() {
    // Check if WooCommerce functions exist
    if ( ! function_exists( 'is_shop' ) || ! function_exists( 'is_product_category' ) || ! function_exists( 'is_product_tag' ) || ! function_exists( 'is_product_taxonomy' ) ) {
        return;
    }
    
    // Only on shop pages
    if ( ! ( is_shop() || is_product_category() || is_product_tag() || is_product_taxonomy() ) ) {
        return;
    }
    
    // Only if catalog toolbar layout is 2
    if ( class_exists( '\Shopwell\Helper' ) && \Shopwell\Helper::get_option( 'catalog_toolbar_layout' ) != '2' ) {
        return;
    }
    
    echo '</div>';
}

/**
 * Position filter sidebar on left and hide button on desktop
 * JavaScript functionality for filter sidebar responsive behavior
 */
function shopwell_disable_filter_toggle_desktop() {
    // Only add JavaScript on shop pages to avoid conflicts
    if ( ! function_exists( 'is_shop' ) || ! function_exists( 'is_product_category' ) || ! function_exists( 'is_product_tag' ) || ! function_exists( 'is_product_taxonomy' ) ) {
        return;
    }
    
    if ( ! ( is_shop() || is_product_category() || is_product_tag() || is_product_taxonomy() ) ) {
        return;
    }
    
    ?>
    <script>
    jQuery(document).ready(function($) {
        // Check if we're on a shop page before running any JavaScript
        var isShopPage = $('body').hasClass('woocommerce-shop') || 
                       $('body').hasClass('tax-product_cat') || 
                       $('body').hasClass('tax-product_tag') || 
                       $('body').hasClass('post-type-archive-product');
        
        if (!isShopPage) {
            return;
        }
        
        // Handle responsive behavior
        function handleFilterSidebarResponsive() {
            if ($(window).width() >= 992) {
                // Desktop: Position inside content area
                $('#filter-sidebar-panel, #mobile-filter-sidebar-panel').removeClass('offscreen-panel offscreen-panel--side-left offscreen-panel--open').show();
                $('body').removeClass('offcanvas-opened');
                
                // Ensure sidebar is properly positioned inside content area
                $('#filter-sidebar-panel, #mobile-filter-sidebar-panel').css({
                    'position': 'static',
                    'width': '280px',
                    'height': 'auto',
                    'float': 'left',
                    'background': '#fff',
                    'border-right': '1px solid #e0e0e0',
                    'overflow-y': 'visible',
                    'box-shadow': 'none'
                });
                
                // Ensure filter-products wrapper has block layout
                $('.shopwell-filter-products-wrapper').css({
                    'display': 'block',
                    'overflow': 'hidden'
                });
                
                // Adjust main content
                $('.woocommerce-loop').css({
                    'margin-left': '0',
                    'width': 'auto',
                    'min-width': '0',
                    'overflow': 'hidden'
                });
                
                // Ensure products grid displays properly
                $('.woocommerce-loop ul.products').css({
                    'display': 'grid',
                    'grid-template-columns': 'repeat(auto-fill, minmax(250px, 1fr))',
                    'gap': '20px',
                    'width': '100%'
                });
            } else {
                // Mobile/Tablet: Restore off-canvas behavior
                $('#filter-sidebar-panel, #mobile-filter-sidebar-panel').addClass('offscreen-panel offscreen-panel--side-left').removeClass('offscreen-panel--open');
                $('#filter-sidebar-panel, #mobile-filter-sidebar-panel').css({
                    'position': 'fixed',
                    'top': '0',
                    'left': '0',
                    'width': '100%',
                    'height': '100vh',
                    'z-index': '9999',
                    'background': '#fff',
                    'transform': 'translateX(-100%)',
                    'float': 'none',
                    'margin-right': '0'
                });
                
                // Reset layout for mobile
                $('.shopwell-filter-products-wrapper').css({
                    'display': 'block',
                    'overflow': 'visible'
                });
                
                $('.woocommerce-loop').css({
                    'width': '100%',
                    'min-width': 'auto',
                    'overflow': 'visible'
                });
                
                // Reset products grid for mobile
                $('.woocommerce-loop ul.products').css({
                    'display': 'grid',
                    'grid-template-columns': 'repeat(auto-fill, minmax(200px, 1fr))',
                    'gap': '15px',
                    'width': '100%'
                });
            }
        }
        
        // Run on load and resize
        handleFilterSidebarResponsive();
        $(window).on('resize', handleFilterSidebarResponsive);
    });
    </script>
    <?php
}
add_action('wp_footer', 'shopwell_disable_filter_toggle_desktop', 20);





/**
 * Handle contact form submission with Resend
 */
function handle_contact_form_submission() {
    // Close session before making HTTP requests to avoid blocking
    if (session_id()) {
        session_write_close();
    }
    
    // Verify nonce for security
    if (!wp_verify_nonce($_POST['_wpnonce'], 'contact_form_nonce')) {
        wp_die('Security check failed');
    }
    
    // Sanitize and validate input
    $name = sanitize_text_field($_POST['name']);
    $lastname = sanitize_text_field($_POST['lastname']);
    $email = sanitize_email($_POST['email']);
    $phone = sanitize_text_field($_POST['phone']);
    $country = sanitize_text_field($_POST['country']);
    $subject = sanitize_text_field($_POST['subject']);
    $message = sanitize_textarea_field($_POST['message']);
    $services = isset($_POST['services']) ? array_map('sanitize_text_field', $_POST['services']) : [];
    $privacy = isset($_POST['privacy']) ? true : false;

    // Validate required fields
    if (empty($name) || empty($lastname) || empty($email) || empty($subject) || empty($message) || !$privacy) {
        wp_send_json_error('Toate cĂ˘mpurile obligatorii trebuie completate.');
        return;
    }
    
    // Validate email format
    if (!is_email($email)) {
        wp_send_json_error('Adresa de email nu este validÄ.');
        return;
    }
    
    // Prepare email content
    $subject_map = [
        'general' => 'ĂŽntrebare generalÄ',
        'product' => 'ĂŽntrebare despre produs',
        'warranty' => 'GaranČ›ie Č™i service',
        'delivery' => 'Livrare Č™i retur',
        'technical' => 'Suport tehnic',
        'partnership' => 'Parteneriat',
        'other' => 'Altele'
    ];
    
    $subject_text = isset($subject_map[$subject]) ? $subject_map[$subject] : 'Contact Form';
    
    $services_text = !empty($services) ? implode(', ', $services) : 'Nu a fost selectat';
    $phone_display = $phone ? "{$country} {$phone}" : 'Nu a fost furnizat';
    
    $email_content = "
    <h2>Mesaj nou de pe site-ul Haloo</h2>
    <p><strong>Nume:</strong> {$name} {$lastname}</p>
    <p><strong>Email:</strong> {$email}</p>
    <p><strong>Telefon:</strong> {$phone_display}</p>
    <p><strong>Subiect:</strong> {$subject_text}</p>
    <p><strong>Interesat de:</strong> {$services_text}</p>
    <p><strong>Mesaj:</strong></p>
    <p>" . nl2br($message) . "</p>
    <hr>
    <p><em>Acest mesaj a fost trimis de pe pagina de contact a site-ului Haloo.</em></p>
    ";
    
    // Send email using Resend
    $resend_api_key = defined('RESEND_API_KEY') ? RESEND_API_KEY : get_option('resend_api_key', '');
    
    if (empty($resend_api_key)) {
        // Fallback to WordPress mail if Resend API key is not set
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <noreply@' . $_SERVER['HTTP_HOST'] . '>',
            'Reply-To: ' . $name . ' <' . $email . '>'
        ];
        
        $sent = wp_mail(
            'contact@haloo.ro',
            'Mesaj nou: ' . $subject_text,
            $email_content,
            $headers
        );
        
        if ($sent) {
                // Send auto-reply to customer
                $auto_reply_content = "
                <h2>MulČ›umim pentru mesajul tÄu!</h2>
                <p>BunÄ {$name} {$lastname},</p>
                <p>Am primit mesajul tÄu despre {$subject_text} Č™i Ă®Č›i vom rÄspunde Ă®n cel mai scurt timp posibil.</p>
                <p><strong>Detaliile mesajului tÄu:</strong></p>
                <ul>
                    <li>Subiect: {$subject_text}</li>
                    <li>Interesat de: {$services_text}</li>
                    <li>Data: " . date('d.m.Y H:i') . "</li>
                </ul>
                <p>ĂŽn cazul Ă®n care ai o Ă®ntrebare urgentÄ despre telefoanele noastre refurbished, ne poČ›i contacta direct la:</p>
                <ul>
                    <li>Telefon: +40 721 234 567</li>
                    <li>WhatsApp: +40 721 234 567</li>
                    <li>Email: contact@haloo.ro</li>
                </ul>
                <p>Cu respect,<br>Echipa Haloo</p>
                ";
            
            wp_mail(
                $email,
                'Confirmare primire mesaj - Haloo',
                $auto_reply_content,
                ['Content-Type: text/html; charset=UTF-8']
            );
            
            wp_send_json_success('Mesajul a fost trimis cu succes!');
        } else {
            wp_send_json_error('A apÄrut o problemÄ la trimiterea mesajului. Te rugÄm sÄ Ă®ncerci din nou.');
        }
    } else {
        // Use Resend API
        $resend_data = [
            'from' => 'Haloo <noreply@haloo.ro>',
            'to' => ['contact@haloo.ro'],
            'subject' => 'Mesaj nou: ' . $subject_text,
            'html' => $email_content,
            'reply_to' => $email
        ];
        
        $response = wp_remote_post('https://api.resend.com/emails', [
            'headers' => [
                'Authorization' => 'Bearer ' . $resend_api_key,
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode($resend_data),
            'timeout' => 30
        ]);
        
        if (is_wp_error($response)) {
            wp_send_json_error('A apÄrut o problemÄ la trimiterea mesajului. Te rugÄm sÄ Ă®ncerci din nou.');
            return;
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        
        if ($response_code === 200) {
                // Send auto-reply to customer using Resend
                $auto_reply_data = [
                    'from' => 'Haloo <noreply@haloo.ro>',
                    'to' => [$email],
                    'subject' => 'Confirmare primire mesaj - Haloo',
                    'html' => "
                    <h2>MulČ›umim pentru mesajul tÄu!</h2>
                    <p>BunÄ {$name} {$lastname},</p>
                    <p>Am primit mesajul tÄu despre {$subject_text} Č™i Ă®Č›i vom rÄspunde Ă®n cel mai scurt timp posibil.</p>
                    <p><strong>Detaliile mesajului tÄu:</strong></p>
                    <ul>
                        <li>Subiect: {$subject_text}</li>
                        <li>Interesat de: {$services_text}</li>
                        <li>Data: " . date('d.m.Y H:i') . "</li>
                    </ul>
                    <p>ĂŽn cazul Ă®n care ai o Ă®ntrebare urgentÄ despre telefoanele noastre refurbished, ne poČ›i contacta direct la:</p>
                    <ul>
                        <li>Telefon: +40 721 234 567</li>
                        <li>WhatsApp: +40 721 234 567</li>
                        <li>Email: contact@haloo.ro</li>
                    </ul>
                    <p>Cu respect,<br>Echipa Haloo</p>
                    "
                ];
            
            wp_remote_post('https://api.resend.com/emails', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $resend_api_key,
                    'Content-Type' => 'application/json',
                ],
                'body' => json_encode($auto_reply_data),
                'timeout' => 30
            ]);
            
            wp_send_json_success('Mesajul a fost trimis cu succes!');
        } else {
            wp_send_json_error('A apÄrut o problemÄ la trimiterea mesajului. Te rugÄm sÄ Ă®ncerci din nou.');
        }
    }
}

// Hook for AJAX contact form submission
add_action('wp_ajax_send_contact_email', 'handle_contact_form_submission');
add_action('wp_ajax_nopriv_send_contact_email', 'handle_contact_form_submission');

/**
 * Add Resend API key setting to WordPress admin
 */
function add_resend_settings() {
    add_options_page(
        'Resend Settings',
        'Resend Email',
        'manage_options',
        'resend-settings',
        'resend_settings_page'
    );
}
add_action('admin_menu', 'add_resend_settings');

function resend_settings_page() {
    if (isset($_POST['submit'])) {
        update_option('resend_api_key', sanitize_text_field($_POST['resend_api_key']));
        echo '<div class="notice notice-success"><p>SetÄrile au fost salvate!</p></div>';
    }
    
    $api_key = get_option('resend_api_key', '');
    ?>
    <div class="wrap">
        <h1>SetÄri Resend Email</h1>
        <form method="post" action="">
            <table class="form-table">
                <tr>
                    <th scope="row">Resend API Key</th>
                    <td>
                        <input type="password" name="resend_api_key" value="<?php echo esc_attr($api_key); ?>" class="regular-text" />
                        <p class="description">IntroduceČ›i cheia API de la Resend. DacÄ nu este setatÄ, se va folosi funcČ›ia de email standard WordPress.</p>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
        <p><strong>InstrucČ›iuni:</strong></p>
        <ol>
            <li>ĂŽnregistraČ›i-vÄ pe <a href="https://resend.com" target="_blank">resend.com</a></li>
            <li>ObČ›ineČ›i cheia API din dashboard-ul Resend</li>
            <li>IntroduceČ›i cheia API Ă®n cĂ˘mpul de mai sus</li>
            <li>SalvaČ›i setÄrile</li>
        </ol>
    </div>
    <?php
}

/**
 * Blog functionality and customizations
 */

// Blog functionality is handled inline in templates

// Customize excerpt length for blog posts
function custom_excerpt_length($length) {
    if (is_home() || is_category() || is_tag() || is_archive()) {
        return 20;
    }
    return $length;
}
add_filter('excerpt_length', 'custom_excerpt_length');

// Customize excerpt more text
function custom_excerpt_more($more) {
    return '...';
}
add_filter('excerpt_more', 'custom_excerpt_more');

// Add custom post meta for blog posts
function add_blog_post_meta() {
    add_meta_box(
        'blog_post_meta',
        'Blog Post Settings',
        'blog_post_meta_callback',
        'post',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'add_blog_post_meta');

function blog_post_meta_callback($post) {
    wp_nonce_field('blog_post_meta_nonce', 'blog_post_meta_nonce');
    $featured = get_post_meta($post->ID, '_blog_featured', true);
    $read_time = get_post_meta($post->ID, '_blog_read_time', true);
    ?>
    <p>
        <label for="blog_featured">
            <input type="checkbox" id="blog_featured" name="blog_featured" value="1" <?php checked($featured, 1); ?>>
            Featured Post
        </label>
    </p>
    <p>
        <label for="blog_read_time">Reading Time (minutes):</label>
        <input type="number" id="blog_read_time" name="blog_read_time" value="<?php echo esc_attr($read_time); ?>" min="1" max="60">
    </p>
    <?php
}

function save_blog_post_meta($post_id) {
    if (!isset($_POST['blog_post_meta_nonce']) || !wp_verify_nonce($_POST['blog_post_meta_nonce'], 'blog_post_meta_nonce')) {
        return;
    }
    
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    $featured = isset($_POST['blog_featured']) ? 1 : 0;
    $read_time = sanitize_text_field($_POST['blog_read_time']);
    
    update_post_meta($post_id, '_blog_featured', $featured);
    update_post_meta($post_id, '_blog_read_time', $read_time);
}
add_action('save_post', 'save_blog_post_meta');

// Add reading time to post meta
function add_reading_time_to_post_meta($post_id) {
    $content = get_post_field('post_content', $post_id);
    $word_count = str_word_count(strip_tags($content));
    $reading_time = ceil($word_count / 200); // Average reading speed: 200 words per minute
    
    // Only update if not manually set
    $manual_read_time = get_post_meta($post_id, '_blog_read_time', true);
    if (empty($manual_read_time)) {
        update_post_meta($post_id, '_blog_read_time', $reading_time);
    }
}
add_action('save_post', 'add_reading_time_to_post_meta');

// Customize blog post query
function customize_blog_query($query) {
    if (!is_admin() && $query->is_main_query()) {
        if (is_home() || is_category() || is_tag() || is_archive()) {
            $query->set('posts_per_page', 6);
        }
    }
}
add_action('pre_get_posts', 'customize_blog_query');

// Add custom body classes for blog pages
function add_blog_body_classes($classes) {
    if (is_page_template('page-blog.php')) {
        $classes[] = 'blog-page';
    }
    if (is_single() && get_post_type() == 'post') {
        $classes[] = 'single-post-page';
    }
    return $classes;
}
add_filter('body_class', 'add_blog_body_classes');

// Hide blog widgets on Help Center search pages
function hide_blog_widgets_on_help_center_search() {
    if (is_search() && get_query_var('post_type') === 'sw_help_article') {
        ?>
        <style>
            /* Hide blog categories and recent posts on Help Center search pages */
            .blog-sidebar{
                display: none !important;
            }

            .blog-header{
                display: none !important;
            }

            #page-header{
                display: none !important;
            }
            
            /* Set max-width to 1440px for Help Center search pages */
            .shopwell-help-archive .site-content > .container {
                max-width: 1440px !important;
            }
            
            /* Grid layout for Help Center search results */
            .blog-posts {
                max-width: 100% !important;
                width: 1440px !important;
                display: grid !important;
                grid-template-columns: repeat(3, 1fr) !important;
                gap: 2rem !important;
                margin: 0 auto !important;
                margin-top: 2rem !important;
            }
            
            /* Responsive grid */
            @media (max-width: 1024px) {
                .blog-posts {
                    grid-template-columns: repeat(2, 1fr) !important;
                    gap: 1.5rem !important;
                }
                
                .shopwell-help-archive .site-content > .container {
                    padding: 0 1.5rem !important;
                }
            }
            
            @media (max-width: 768px) {
                .blog-posts {
                    grid-template-columns: 1fr !important;
                    gap: 1.5rem !important;
                }
                
                .shopwell-help-archive .site-content > .container {
                    padding: 0 1rem !important;
                }
            }
            
            /* Style the article cards */
            .shopwell-help-archive .articles .article,
            .shopwell-help-archive .article,
            .shopwell-help-archive .help-article,
            .shopwell-help-archive article,
            .blog-posts .blog-post {
                background: white;
                border-radius: 12px;
                overflow: hidden;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
                transition: all 0.3s ease;
            }
            
            .shopwell-help-archive .articles .article:hover,
            .shopwell-help-archive .article:hover,
            .shopwell-help-archive .help-article:hover,
            .shopwell-help-archive article:hover,
            .blog-posts .blog-post:hover {
                box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
                transform: translateY(-4px);
            }
            
            /* Mobile-specific adjustments */
            @media (max-width: 768px) {
                .blog-posts .blog-post {
                    border-radius: 8px;
                }
                
                .blog-posts .blog-post:hover {
                    transform: none;
                }
                
                /* Make featured images responsive */
                .blog-posts .blog-post img {
                    width: 100%;
                    height: auto;
                    max-height: 200px;
                    object-fit: cover;
                }
            }
        </style>
        <?php
    }
}
add_action('wp_head', 'hide_blog_widgets_on_help_center_search');

// Add social sharing for blog posts
function add_social_sharing() {
    if (is_single() && get_post_type() == 'post') {
        $post_url = get_permalink();
        $post_title = get_the_title();
        $post_excerpt = get_the_excerpt();
        ?>
        <div class="social-sharing" style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid #e2e8f0;">
            <h4 style="color: #111827; margin-bottom: 1rem; font-size: 1.1rem; font-weight: 600;">Distribuie acest articol:</h4>
            <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($post_url); ?>" 
                   target="_blank" 
                   rel="noopener noreferrer"
                   style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1rem; background: #1877f2; color: white; text-decoration: none; border-radius: 8px; font-weight: 600; transition: all 0.3s ease;">
                    đź“ Facebook
                </a>
                <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode($post_url); ?>&text=<?php echo urlencode($post_title); ?>" 
                   target="_blank" 
                   rel="noopener noreferrer"
                   style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1rem; background: #1da1f2; color: white; text-decoration: none; border-radius: 8px; font-weight: 600; transition: all 0.3s ease;">
                    đź¦ Twitter
                </a>
                <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo urlencode($post_url); ?>" 
                   target="_blank" 
                   rel="noopener noreferrer"
                   style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1rem; background: #0077b5; color: white; text-decoration: none; border-radius: 8px; font-weight: 600; transition: all 0.3s ease;">
                    đź’Ľ LinkedIn
                </a>
                <a href="https://wa.me/?text=<?php echo urlencode($post_title . ' - ' . $post_url); ?>" 
                   target="_blank" 
                   rel="noopener noreferrer"
                   style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1rem; background: #25d366; color: white; text-decoration: none; border-radius: 8px; font-weight: 600; transition: all 0.3s ease;">
                    đź’¬ WhatsApp
                </a>
            </div>
        </div>
        <style>
        .social-sharing a:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            text-decoration: none;
        }
        </style>
        <?php
    }
}
add_action('wp_footer', 'add_social_sharing');

// Custom single post template override
function haloo_single_post_template($template) {
    if (is_single() && get_post_type() == 'post') {
        $custom_template = locate_template('single-haloo.php');
        if ($custom_template) {
            return $custom_template;
        }
    }
    return $template;
}
add_filter('template_include', 'haloo_single_post_template');

// Add related posts functionality (now handled in single-haloo.php template)
function add_related_posts() {
    // This function is now handled directly in the single-haloo.php template
    // Keeping this for backward compatibility but not using it
}
add_action('wp_footer', 'add_related_posts');

/**
 * Change number of products displayed per page in shop
 */
function custom_products_per_page($cols) {
    return 20; // Display 24 products per page
}
add_filter('loop_shop_per_page', 'custom_products_per_page', 999);

/**
 * Ensure WooCommerce shop settings respect our custom product count
 * OPTIMIZARE: AsigurÄ cÄ doar prima paginÄ (20 produse) se Ă®ncarcÄ iniČ›ial
 */
function ensure_shop_products_per_page($query) {
    // Doar pentru frontend și query-ul principal
    if (!is_admin() && $query->is_main_query()) {
        // Doar pentru paginile de shop/categorii
        if (is_shop() || is_product_category() || is_product_tag() || is_product_taxonomy()) {
            // OPTIMIZARE: Limitează strict la 20 produse per pagină
            // Nu permite modificarea acestui număr pentru performanță optimă
            $query->set('posts_per_page', 20);
            $query->set('no_found_rows', false); // Permite paginare corectă
            
            // FIX: Nu suprascrie paged dacă este deja setat corect din URL
            // WordPress parsează corect /page/2/ din URL și setează query var-ul 'paged'
            // Nu trebuie să verificăm $_GET['paged'] pentru că cu permalink-uri frumoase
            // parametrul este în query vars, nu în $_GET
            $paged = get_query_var('paged');
            if (!$paged) {
                // Doar dacă nu există deloc, setăm la 1
                $query->set('paged', 1);
            }
            // Altfel, lăsăm WordPress să gestioneze paginarea corect
        }
    }
}
add_action('pre_get_posts', 'ensure_shop_products_per_page', 20);

/**
 * Log pagination information for debugging
 */
function shopwell_log_pagination_info() {
    if ( ! is_shop() && ! is_product_category() && ! is_product_tag() && ! is_product_taxonomy() ) {
        return;
    }
    
    global $wp_query, $wp_rewrite;
    
    $paged_from_query_var = get_query_var('paged');
    $paged_from_query = $wp_query->get('paged');
    $paged_from_get = isset($_GET['paged']) ? $_GET['paged'] : null;
    
    $pagination_info = array(
        'pagination_base' => $wp_rewrite->pagination_base,
        'current_page_query_var' => $paged_from_query_var ? $paged_from_query_var : 1,
        'current_page_query' => $paged_from_query ? $paged_from_query : 1,
        'current_page_get' => $paged_from_get,
        'wp_query_paged' => $wp_query->get('paged'),
        'max_pages' => $wp_query->max_num_pages,
        'found_posts' => $wp_query->found_posts,
        'post_count' => $wp_query->post_count,
        'request_uri' => $_SERVER['REQUEST_URI'] ?? '',
        'query_string' => $_SERVER['QUERY_STRING'] ?? '',
        'is_shop' => is_shop(),
        'is_product_category' => is_product_category(),
        'is_product_tag' => is_product_tag(),
    );
    
    shopwell_log_to_file( 'Pagination Info', $pagination_info, 'pagination' );
}
add_action('wp', 'shopwell_log_pagination_info');

// Handle product_cat query parameter for shop page filtering
function handle_shop_category_filtering($query) {
    // Only run on frontend and main query
    if (is_admin() || !$query->is_main_query()) {
        return;
    }
    
    // Only run on shop page, not on category pages (WooCommerce handles those)
    if (!is_shop()) {
        return;
    }
    
    // Check if product_cat parameter is set and we're not already on a category page
    if (isset($_GET['product_cat']) && !empty($_GET['product_cat']) && !is_product_category()) {
        $category_slug = sanitize_text_field($_GET['product_cat']);
        
        // Verify the category exists
        $category = get_term_by('slug', $category_slug, 'product_cat');
        if (!$category || is_wp_error($category)) {
            return;
        }
        
        // Set up taxonomy query for product category
        $tax_query = $query->get('tax_query') ?: [];
        
        $tax_query[] = array(
            'taxonomy' => 'product_cat',
            'field'    => 'slug',
            'terms'    => $category_slug,
        );
        
        $query->set('tax_query', $tax_query);
        
        // Ensure we're querying products
        $query->set('post_type', 'product');
        $query->set('post_status', 'publish');
    }
}
add_action('pre_get_posts', 'handle_shop_category_filtering', 5);

/**
 * Handle WooCommerce attribute filters for layered nav widgets
 */
function handle_woocommerce_attribute_filters($query) {
    // Only run on frontend and main query
    if (is_admin() || !$query->is_main_query()) {
        return;
    }
    
    // Only run on shop pages
    if (!(is_shop() || is_product_category() || is_product_tag() || is_product_taxonomy())) {
        return;
    }
    
    // Check for attribute filters
    $attribute_filters = array();
    
    // Get all filter_pa_ parameters
    foreach ($_GET as $key => $value) {
        if (strpos($key, 'filter_pa_') === 0) {
            $attribute_name = str_replace('filter_pa_', '', $key);
            $attribute_filters[$attribute_name] = sanitize_text_field($value);
        }
    }
    
    // If we have attribute filters, apply them
    if (!empty($attribute_filters)) {
        $tax_query = $query->get('tax_query') ?: array();
        
        foreach ($attribute_filters as $attribute_name => $attribute_value) {
            // Convert attribute name to taxonomy name
            $taxonomy = 'pa_' . $attribute_name;
            
            // Verify the taxonomy exists
            if (taxonomy_exists($taxonomy)) {
                $tax_query[] = array(
                    'taxonomy' => $taxonomy,
                    'field'    => 'slug',
                    'terms'    => $attribute_value,
                );
            }
        }
        
        if (!empty($tax_query)) {
            $query->set('tax_query', $tax_query);
        }
    }
}
add_action('pre_get_posts', 'handle_woocommerce_attribute_filters', 10);


/**
 * Fix variable product price display on shop pages
 * Sets price ranges for variable products so they show proper prices instead of 0,00 lei
 */
function fix_variable_product_price_display() {
    // Only apply on shop/category pages
    if (!is_shop() && !is_product_category() && !is_product_tag() && !is_search()) {
        return;
    }
    
    // Hook into the product loop to fix prices for each variable product
    add_action('woocommerce_shop_loop_item_title', function() {
        global $product;
        
        if (!$product || !$product->is_type('variable')) {
            return;
        }
        
        // Get all published variations
        $variations = $product->get_children();
        $prices = [];
        
        foreach ($variations as $variation_id) {
            $variation = wc_get_product($variation_id);
            if ($variation && $variation->is_purchasable() && $variation->is_in_stock()) {
                $price = $variation->get_price();
                if ($price > 0) {
                    $prices[] = $price;
                }
            }
        }
        
        if (!empty($prices)) {
            $min_price = min($prices);
            $max_price = max($prices);
            
            // Set price range meta for proper display
            update_post_meta($product->get_id(), '_price', $min_price);
            update_post_meta($product->get_id(), '_min_variation_price', $min_price);
            update_post_meta($product->get_id(), '_max_variation_price', $max_price);
            
            // Set min/max regular price as well
            update_post_meta($product->get_id(), '_min_variation_regular_price', $min_price);
            update_post_meta($product->get_id(), '_max_variation_regular_price', $max_price);
        }
    }, 5);
}
add_action('woocommerce_before_shop_loop', 'fix_variable_product_price_display');


/**
 * Product Search Autocomplete Shortcode
 */
require_once get_template_directory() . '/inc/product-search-autocomplete.php';

/**
 * Load Refactored Styles
 */
require_once get_template_directory() . '/assets/css/load-refactored-styles.php';

/**
 * Auto-select first variation on single product and filter-selected variations on catalog
 */
function shopwell_auto_select_variations() {
    // Add cache-busting version
    $version = '2.0.' . time();
    ?>
    <style type="text/css">
    /* Show disabled swatches instead of hiding them */
    .wcboost-variation-swatches__item.shopwell-disabled,
    .product-variation-item.shopwell-disabled {
        opacity: 0.4 !important;
        pointer-events: auto !important;
        cursor: not-allowed !important;
        display: inline-flex !important;
        visibility: visible !important;
        position: relative !important;
    }
    .wcboost-variation-swatches__item.shopwell-disabled::after,
    .product-variation-item.shopwell-disabled::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 0;
        right: 0;
        height: 1px;
        background: rgba(0,0,0,0.5);
        transform: rotate(-45deg);
    }
    /* Ensure swatches are never hidden */
    .wcboost-variation-swatches__item,
    .product-variation-item {
        display: inline-flex !important;
        visibility: visible !important;
    }
    /* Override any hiding of unavailable options */
    .wcboost-variation-swatches__item[style*="display: none"],
    .product-variation-item[style*="display: none"] {
        display: inline-flex !important;
    }
    /* Style for "indisponibil" text to maintain height */
    .sv-pill-price-unavailable {
        display: block !important;
        color: #999 !important;
        font-size: 0.85em !important;
        font-weight: normal !important;
        line-height: 1.2 !important;
        margin-top: 4px !important;
        min-height: 1.2em !important;
    }
    /* Ensure price container is visible and styled properly */
    .sv-pill-price {
        min-height: 1.2em !important;
        display: block !important;
        font-size: 0.85em !important;
        color: #666 !important;
        margin-top: 4px !important;
        text-align: center !important;
    }
    /* Price inside swatch name element */
    .wcboost-variation-swatches__name .sv-pill-price,
    .product-variation-item__name .sv-pill-price {
        display: block !important;
        margin-top: 2px !important;
    }
    /* Price directly on swatch (when no name element exists) */
    .wcboost-variation-swatches__item > .sv-pill-price,
    .product-variation-item > .sv-pill-price {
        display: block !important;
        margin-top: 4px !important;
        width: 100% !important;
    }
    .wcboost-variation-swatches__name,
    .product-variation-item__name {
        min-height: auto !important;
    }
    /* Ensure swatch items can display prices properly */
    .wcboost-variation-swatches__item,
    .product-variation-item {
        flex-direction: column !important;
        align-items: center !important;
    }
    </style>
    <script type="text/javascript" data-version="<?php echo esc_attr($version); ?>">
    /* Shopwell Variations Script v<?php echo esc_html($version); ?> */
    
    // Firefox fix: Ensure JavaScript runs after browser back/forward navigation
    // This fixes an issue where Firefox doesn't properly restore JavaScript state
    // when using pushState and navigating back in history
    window.onunload = function(){};
    
    // Flag to track if handlers have been initialized
    window.shopwellVariationHandlersInitialized = false;
    
    // Handle page restore from bfcache (back/forward navigation)
    window.addEventListener('pageshow', function(event) {
        // If page was restored from bfcache, reinitialize handlers
        if (event.persisted || (window.performance && window.performance.navigation.type === 2)) {
            window.shopwellVariationHandlersInitialized = false;
            // Trigger reinitialization
            if (typeof jQuery !== 'undefined') {
                jQuery(document).trigger('shopwell_reinitialize');
            }
        }
    });
    
    // Handle tab focus - reinitialize when user returns to tab
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden && typeof jQuery !== 'undefined') {
            // Small delay to ensure page is fully active
            setTimeout(function() {
                if (jQuery('body').hasClass('single-product')) {
                    // Re-register click handler
                    window.shopwellVariationHandlersInitialized = false;
                    jQuery(document).trigger('shopwell_reinitialize');
                }
            }, 100);
        }
    });
    
    // Handle window focus
    window.addEventListener('focus', function() {
        if (typeof jQuery !== 'undefined' && jQuery('body').hasClass('single-product')) {
            setTimeout(function() {
                // Just sync visual state, don't reinitialize
                if (typeof window.shopwellSyncSwatchVisualState === 'function') {
                    window.shopwellSyncSwatchVisualState();
                }
            }, 100);
        }
    });
    
    // Logging function that writes to file instead of console
    // Make it globally available
    window.shopwellLog = function(message, data) {
        // Only log if WP_DEBUG is enabled or debug_logging is set in shopwellData
        var shouldLog = false;
        if (typeof shopwellData !== 'undefined' && shopwellData.debug_logging) {
            shouldLog = true;
        } else if (typeof wp !== 'undefined' && wp.debug && wp.debug === true) {
            shouldLog = true;
        }
        
        if (shouldLog && typeof ajaxurl !== 'undefined') {
            jQuery.post(ajaxurl, {
                action: 'shopwell_log_message',
                nonce: '<?php echo wp_create_nonce( 'shopwell_log_nonce' ); ?>',
                message: message,
                data: data ? JSON.stringify(data) : null
            }).fail(function() {
                // Silently fail - don't break functionality if logging fails
            });
        }
    };
    
    jQuery(document).ready(function($) {
        // Helper function to convert simplified URL parameter to attribute name
        function urlParamToAttributeName(paramName) {
            var paramMap = {
                'culoare': ['attribute_pa_culoare', 'attribute_culoare'],
                'memorie': ['attribute_pa_memorie', 'attribute_memorie', 'attribute_pa_stocare', 'attribute_stocare'],
                'stare': ['attribute_pa_stare', 'attribute_stare']
            };
            
            return paramMap[paramName] || null;
        }
        
        // Helper function to convert attribute name to simplified URL parameter
        function simplifyAttributeName(attrName) {
            var attributeMap = {
                'attribute_pa_culoare': 'culoare',
                'attribute_culoare': 'culoare',
                'attribute_pa_memorie': 'memorie',
                'attribute_memorie': 'memorie',
                'attribute_pa_stocare': 'memorie',
                'attribute_stocare': 'memorie',
                'attribute_pa_stare': 'stare',
                'attribute_stare': 'stare'
            };
            
            return attributeMap[attrName] || null;
        }
        
        // Function to auto-select first variation on single product page
        function autoSelectFirstVariation() {
            if (!$('body').hasClass('single-product')) {
                return;
            }
            
            var filterAttributes = {};
            
            // Check URL parameters first
            var urlParams = new URLSearchParams(window.location.search);
            urlParams.forEach(function(value, key) {
                // Check for simplified parameters (culoare, memorie, stare)
                if (key === 'culoare' || key === 'memorie' || key === 'stare') {
                    var possibleAttrNames = urlParamToAttributeName(key);
                    if (possibleAttrNames) {
                        // Store with first possible name, will be matched later
                        possibleAttrNames.forEach(function(attrName) {
                            filterAttributes[attrName] = value;
                        });
                    }
                }
                // Also support old format for backward compatibility
                else if (key.startsWith('attribute_') || key.startsWith('pa_')) {
                    filterAttributes[key] = value;
                }
            });
            
            // Check referer for active filters
            if (Object.keys(filterAttributes).length === 0 && document.referrer) {
                try {
                    var referrerUrl = new URL(document.referrer);
                    var referrerParams = new URLSearchParams(referrerUrl.search);
                    
                    referrerParams.forEach(function(value, key) {
                        if (key.startsWith('filter_') || key.startsWith('pa_')) {
                            var attrName = key.replace('filter_', '');
                            filterAttributes['attribute_' + attrName] = value.split(',')[0];
                        }
                    });
                } catch(e) {
                    shopwellLog('Could not parse referrer');
                }
            }
            
            
            function selectVariations() {
                var $variationForm = $('form.variations_form');
                
                if (!$variationForm.length) {
                    shopwellLog('Variation form not found');
                    return false;
                }
                
                var $selects = $variationForm.find('select[name^="attribute_"]');
                
                if (!$selects.length) {
                    shopwellLog('No variation selects found');
                    return false;
                }
                
                // Get available variations data
                var variationsData = $variationForm.data('product_variations');
                shopwellLog('Available variations data', variationsData);
                
                var allSelected = true;
                var madeSelection = false;
                
                // Find a valid combination that includes filter attributes
                var targetCombination = {};
                
                if (variationsData && variationsData.length > 0 && Object.keys(filterAttributes).length > 0) {
                    shopwellLog('Looking for valid combination with filters', filterAttributes);
                    
                    // Find variations that match our filter attributes
                    var matchingVariations = variationsData.filter(function(variation) {
                        if (!variation.is_in_stock && !variation.is_purchasable) {
                            return false;
                        }
                        
                        var matches = true;
                        Object.keys(filterAttributes).forEach(function(filterKey) {
                            var filterValue = filterAttributes[filterKey];
                            
                            // Check all possible attribute name formats
                            var possibleKeys = [
                                filterKey,
                                filterKey.replace('attribute_', 'attribute_pa_'),
                                filterKey.replace('attribute_pa_', 'attribute_')
                            ];
                            
                            var found = false;
                            possibleKeys.forEach(function(key) {
                                if (variation.attributes[key]) {
                                    var varValue = variation.attributes[key];
                                    var normalizedVar = varValue.toLowerCase().replace(/[^a-z0-9]/g, '');
                                    var normalizedFilter = filterValue.toLowerCase().replace(/[^a-z0-9]/g, '');
                                    
                                    if (normalizedVar === normalizedFilter) {
                                        found = true;
                                    }
                                }
                            });
                            
                            if (!found) {
                                matches = false;
                            }
                        });
                        
                        return matches;
                    });
                    
                    shopwellLog('Found ' + matchingVariations.length + ' matching variations');
                    
                    if (matchingVariations.length > 0) {
                        // Use the first matching variation
                        targetCombination = matchingVariations[0].attributes;
                        shopwellLog('Target combination', targetCombination);
                    }
                }
                
                $selects.each(function() {
                    var $select = $(this);
                    var selectName = $select.attr('name');
                    var $options = $select.find('option:not([value=""])');
                    
                    // Skip if no options available
                    if (!$options.length) {
                        shopwellLog('No options for ' + selectName);
                        return;
                    }
                    
                    var valueToSelect = null;
                    
                    // First, check if we have a valid combination from variations data
                    if (Object.keys(targetCombination).length > 0) {
                        // Try to find value from target combination
                        if (targetCombination[selectName]) {
                            valueToSelect = targetCombination[selectName];
                            shopwellLog('Using value from valid combination: ' + valueToSelect + ' for ' + selectName);
                        }
                    }
                    
                    // If no value from combination, try to match filter directly
                    if (!valueToSelect) {
                        // Try to select from filter first (check multiple variations of attribute name)
                        var filterValue = null;
                        var filterKey = null;
                        
                        // Check exact match: attribute_pa_culoare
                        if (filterAttributes[selectName]) {
                            filterValue = filterAttributes[selectName];
                            filterKey = selectName;
                        }
                        // Check without pa_ prefix: attribute_culoare -> attribute_pa_culoare
                        else if (selectName.startsWith('attribute_pa_')) {
                            var withoutPa = selectName.replace('attribute_pa_', 'attribute_');
                            if (filterAttributes[withoutPa]) {
                                filterValue = filterAttributes[withoutPa];
                                filterKey = withoutPa;
                            }
                        }
                        // Check with pa_ prefix: attribute_pa_culoare -> attribute_culoare
                        else if (selectName.startsWith('attribute_')) {
                            var withPa = selectName.replace('attribute_', 'attribute_pa_');
                            if (filterAttributes[withPa]) {
                                filterValue = filterAttributes[withPa];
                                filterKey = withPa;
                            }
                        }
                        
                        if (filterValue) {
                            valueToSelect = filterValue;
                            shopwellLog('Using filter value: ' + valueToSelect + ' for ' + selectName);
                        }
                    }
                    
                    // Try to select the determined value
                    if (valueToSelect) {
                        var $matchingOption = $options.filter(function() {
                            var optVal = $(this).val();
                            
                            // Try exact match
                            if (optVal === valueToSelect) return true;
                            
                            // Try normalized match (lowercase, no special chars)
                            var normalizedOpt = optVal.toLowerCase().replace(/[^a-z0-9]/g, '');
                            var normalizedTarget = valueToSelect.toLowerCase().replace(/[^a-z0-9]/g, '');
                            
                            return normalizedOpt === normalizedTarget;
                        });
                        
                        if ($matchingOption.length) {
                            shopwellLog('Found match: ' + $matchingOption.first().val() + ' for ' + selectName);
                            $select.val($matchingOption.first().val()).trigger('change');
                            madeSelection = true;
                            return; // Skip first option selection
                        } else {
                            shopwellLog('No match found for ' + valueToSelect + ' in ' + selectName);
                            shopwellLog('Available options', $options.map(function() { return $(this).val(); }).get());
                        }
                    }
                    
                    // Skip if already selected (check after filter attempt)
                    if ($select.val() && $select.val() !== '') {
                        shopwellLog(selectName + ' already selected: ' + $select.val());
                        return;
                    }
                    
                    // Find first available option only if no filter was specified for this attribute
                    var $firstOption = $options.first();
                    
                    if ($firstOption.length) {
                        shopwellLog('Selecting first option: ' + $firstOption.val() + ' for ' + selectName);
                        $select.val($firstOption.val()).trigger('change');
                        madeSelection = true;
                    } else {
                        allSelected = false;
                    }
                });
                
                // Trigger update to show correct price and availability
                if (madeSelection) {
                    setTimeout(function() {
                        $variationForm.trigger('check_variations');
                        $variationForm.trigger('woocommerce_variation_select_change');
                        // Sync visual state of swatches
                        syncSwatchVisualState();
                    }, 100);
                }
                
                return madeSelection;
            }
            
            // Try multiple times with increasing delays
            var attempts = 0;
            var maxAttempts = 5;
            
            function trySelect() {
                attempts++;
                shopwellLog('Attempt ' + attempts + ' to select variations');
                
                if (selectVariations()) {
                    shopwellLog('Successfully selected variations');
                    return;
                }
                
                if (attempts < maxAttempts) {
                    setTimeout(trySelect, 500 * attempts);
                }
            }
            
            // Start after WooCommerce initializes
            setTimeout(trySelect, 500);
            
            // Also listen to WooCommerce events
            $(document).on('wc_variation_form', function() {
                shopwellLog('WC variation form event fired');
                setTimeout(selectVariations, 200);
            });
        }
        
        // Function to handle variation selection from filters on catalog pages
        function handleFilterVariations() {
            if ($('body').hasClass('woocommerce-shop') || 
                $('body').hasClass('tax-product_cat') || 
                $('body').hasClass('tax-product_tag') || 
                $('body').hasClass('post-type-archive-product')) {
                
                shopwellLog('HandleFilterVariations running on catalog page');
                shopwellLog('Current URL: ' + window.location.href);
                
                // Get URL parameters for active filters
                var urlParams = new URLSearchParams(window.location.search);
                var filterAttributes = {};
                
                shopwellLog('All URL params', Array.from(urlParams.entries()));
                
                // Collect all attribute filters from URL
                urlParams.forEach(function(value, key) {
                    shopwellLog('Checking param: ' + key + ' = ' + value);
                    if (key.startsWith('filter_') || key.startsWith('pa_')) {
                        var attrName = key.replace('filter_', '').replace('pa_', '');
                        filterAttributes[attrName] = value.split(',');
                        shopwellLog('Added filter attribute: ' + attrName, filterAttributes[attrName]);
                    }
                });
                
                shopwellLog('Final filterAttributes', filterAttributes);
                
                // Add filter parameters to product links
                if (Object.keys(filterAttributes).length > 0) {
                    shopwellLog('Adding filter params to product links', filterAttributes);
                    
                    // Find all product links with various possible selectors
                    var $productLinks = $('ul.products li.product a').filter(function() {
                        var href = $(this).attr('href');
                        // Include links that go to product pages (not cart, compare, etc)
                        return href && 
                               href.indexOf('#') !== 0 && 
                               href.indexOf('add-to-cart') === -1 &&
                               href.indexOf('?add-to-cart') === -1 &&
                               !$(this).hasClass('add_to_cart_button') &&
                               !$(this).hasClass('product_type_') &&
                               !$(this).hasClass('button');
                    });
                    
                    shopwellLog('Found ' + $productLinks.length + ' product links');
                    
                    $productLinks.each(function() {
                        var $link = $(this);
                        var href = $link.attr('href');
                        
                        try {
                            var url = new URL(href, window.location.origin);
                            
                            // Add filter attributes to product link
                            Object.keys(filterAttributes).forEach(function(attrName) {
                                var filterValue = filterAttributes[attrName][0]; // Get first value
                                url.searchParams.set('attribute_' + attrName, filterValue);
                            });
                            
                            var newHref = url.toString();
                            $link.attr('href', newHref);
                            shopwellLog('Updated link: ' + href + ' -> ' + newHref);
                        } catch(e) {
                            shopwellLog('Error updating link: ' + href, e.message);
                        }
                    });
                }
                
                // If we have active filters, pre-select matching variations on product cards
                if (Object.keys(filterAttributes).length > 0) {
                    $('.product-variation-items--item .product-variation-item').each(function() {
                        var $item = $(this);
                        var itemText = $item.data('text') || $item.find('.product-variation-item__color').attr('title') || $item.text().trim();
                        
                        // Check if this variation matches any active filter
                        Object.keys(filterAttributes).forEach(function(attrName) {
                            var filterValues = filterAttributes[attrName];
                            
                            filterValues.forEach(function(filterValue) {
                                var normalizedFilterValue = filterValue.toLowerCase().replace(/[^a-z0-9]/g, '');
                                var normalizedItemText = itemText.toLowerCase().replace(/[^a-z0-9]/g, '');
                                
                                if (normalizedItemText === normalizedFilterValue) {
                                    // Trigger click on this variation to pre-select it
                                    setTimeout(function() {
                                        if (!$item.hasClass('selected')) {
                                            $item.trigger('click');
                                        }
                                    }, 100);
                                }
                            });
                        });
                    });
                }
                
                // Also auto-select first variation if no filter is active
                if (Object.keys(filterAttributes).length === 0) {
                    $('.product-variation-items--item').each(function() {
                        var $container = $(this);
                        var $firstVariation = $container.find('.product-variation-item').first();
                        
                        if ($firstVariation.length && !$container.find('.product-variation-item.selected').length) {
                            setTimeout(function() {
                                $firstVariation.trigger('click');
                            }, 100);
                        }
                    });
                }
            }
        }
        
        // Function to update URL when variations are selected on single product page
        function updateUrlWithVariations() {
            if (!$('body').hasClass('single-product')) {
                return;
            }
            
            var $variationForm = $('form.variations_form');
            if (!$variationForm.length) {
                return;
            }
            
            // Function to convert simplified URL parameter back to attribute name
            function getAttributeNameFromUrlParam(simpleName) {
                // Try to find the actual attribute name in the form
                var possibleNames = [];
                if (simpleName === 'culoare') {
                    possibleNames = ['attribute_pa_culoare', 'attribute_culoare'];
                } else if (simpleName === 'memorie') {
                    possibleNames = ['attribute_pa_memorie', 'attribute_memorie', 'attribute_pa_stocare', 'attribute_stocare'];
                } else if (simpleName === 'stare') {
                    possibleNames = ['attribute_pa_stare', 'attribute_stare'];
                }
                
                // Find which one exists in the form
                for (var i = 0; i < possibleNames.length; i++) {
                    if ($variationForm.find('[name="' + possibleNames[i] + '"]').length) {
                        return possibleNames[i];
                    }
                }
                
                // Fallback to first possible name
                return possibleNames[0] || null;
            }
            
            // Function to restore form state from URL (for browser back button)
            function restoreFormFromUrl() {
                var urlParams = new URLSearchParams(window.location.search);
                var hasChanges = false;
                
                // Process each URL parameter
                urlParams.forEach(function(value, key) {
                    // Only process simplified attribute names
                    if (key === 'culoare' || key === 'memorie' || key === 'stare') {
                        var attrName = getAttributeNameFromUrlParam(key);
                        if (attrName) {
                            var $field = $variationForm.find('[name="' + attrName + '"]');
                            
                            if ($field.length) {
                                // Handle different field types
                                if ($field.is('select')) {
                                    if ($field.val() !== value) {
                                        $field.val(value).trigger('change');
                                        hasChanges = true;
                                    }
                                } else if ($field.is('input[type="radio"]')) {
                                    var $radio = $variationForm.find('[name="' + attrName + '"][value="' + value + '"]');
                                    if ($radio.length && !$radio.is(':checked')) {
                                        $radio.prop('checked', true).trigger('change');
                                        hasChanges = true;
                                    }
                                } else if ($field.is('input[type="hidden"]')) {
                                    if ($field.val() !== value) {
                                        $field.val(value).trigger('change');
                                        hasChanges = true;
                                    }
                                }
                                } else {
                                    // Try clicking on swatch buttons
                                    var $swatch = $variationForm.find('.wcboost-variation-swatches__item[data-value="' + value + '"], .product-variation-item[data-value="' + value + '"]');
                                    if ($swatch.length && !$swatch.hasClass('selected')) {
                                        $swatch.trigger('click');
                                        hasChanges = true;
                                    }
                                }
                                
                                // Sync visual state after setting value
                                setTimeout(function() {
                                    syncSwatchVisualState();
                                }, 50);
                        }
                    }
                });
                
                // Clear fields that are not in URL
                $variationForm.find('select[name^="attribute_"], input[name^="attribute_"]').each(function() {
                    var $field = $(this);
                    var fieldName = $field.attr('name');
                    var simpleName = simplifyAttributeName(fieldName);
                    
                    if (simpleName && !urlParams.has(simpleName)) {
                        if ($field.is('select')) {
                            $field.val('').trigger('change');
                        } else if ($field.is('input[type="radio"]')) {
                            $field.prop('checked', false);
                        } else if ($field.is('input[type="hidden"]')) {
                            $field.val('');
                        }
                    }
                });
                
                return hasChanges;
            }
            
            // Function to update URL with current variation selections
            function updateUrl() {
                console.log('[SHOPWELL DEBUG] updateUrl called');
                
                // Don't update URL if we're restoring from browser navigation
                if (isRestoringFromUrl) {
                    console.log('[SHOPWELL DEBUG] updateUrl: skipping, restoring from URL');
                    return;
                }
                
                var urlParams = new URLSearchParams(window.location.search);
                var hasChanges = false;
                
                // First, remove all old attribute_pa_* parameters and other unwanted params
                var paramsToRemove = [];
                urlParams.forEach(function(value, key) {
                    if (key.startsWith('attribute_')) {
                        paramsToRemove.push(key);
                    }
                });
                paramsToRemove.forEach(function(key) {
                    urlParams.delete(key);
                    hasChanges = true;
                });
                
                // Get selected attributes from the form (only culoare, memorie, stare)
                var fieldsFound = [];
                $variationForm.find('select[name^="attribute_"], input[name^="attribute_"][type="hidden"], input[name^="attribute_"][type="radio"]:checked').each(function() {
                    var $field = $(this);
                    var fieldName = $field.attr('name');
                    var fieldValue = $field.val();
                    
                    fieldsFound.push({name: fieldName, value: fieldValue});
                    
                    // Get simplified name
                    var simpleName = simplifyAttributeName(fieldName);
                    
                    // Only process allowed attributes (culoare, memorie, stare)
                    if (!simpleName) {
                        return; // Skip this attribute
                    }
                    
                    if (fieldValue && fieldValue !== '') {
                        // Use simplified name in URL
                        urlParams.set(simpleName, fieldValue);
                        hasChanges = true;
                    } else {
                        // Remove parameter if no value selected
                        if (urlParams.has(simpleName)) {
                            urlParams.delete(simpleName);
                            hasChanges = true;
                        }
                    }
                });
                
                console.log('[SHOPWELL DEBUG] updateUrl: fields found:', fieldsFound);
                console.log('[SHOPWELL DEBUG] updateUrl: hasChanges:', hasChanges);
                
                // Update URL if there are changes
                if (hasChanges) {
                    var newUrl = window.location.pathname;
                    var queryString = urlParams.toString();
                    if (queryString) {
                        newUrl += '?' + queryString;
                    }
                    
                    console.log('[SHOPWELL DEBUG] updateUrl: new URL:', newUrl);
                    
                    // Use replaceState instead of pushState to avoid creating history entries
                    // This allows the back button to work properly
                    if (window.history && window.history.replaceState) {
                        window.history.replaceState({}, '', newUrl);
                        console.log('[SHOPWELL DEBUG] updateUrl: URL updated successfully');
                    }
                } else {
                    console.log('[SHOPWELL DEBUG] updateUrl: no changes, URL not updated');
                }
            }
            
            // Expose updateUrl globally
            window.shopwellUpdateUrl = updateUrl;
            
            // Initial URL update
            setTimeout(updateUrl, 500);
                                $swatch.css('border', '2px solid #1d2128');
                                return false;
                            }
                        });
                    }
                }
                
                // Also run full sync
                syncSwatchVisualState(true); // Force sync
                setTimeout(function() {
                    syncSwatchVisualState(true);
                    updateUrl();
                }, 50);
                setTimeout(function() {
                    syncSwatchVisualState(true);
                    updateUrl();
                }, 150);
            });
            
            // Listen to clicks on variation swatches (color buttons, etc.) - this is a backup
            // The main handler is in enableVariationSelectDeselect
            // NOTE: This backup is disabled to prevent race condition with manual click handling
            // The enableVariationSelectDeselect function handles all visual updates
            
            // Also listen to WooCommerce variation events
            $variationForm.on('found_variation', function(event, variation) {
                // Update URL when a valid variation is found
                // Skip if manual click is in progress
                if (skipSyncAfterClick) return;
                setTimeout(function() {
                    if (skipSyncAfterClick) return;
                    syncSwatchVisualState();
                    updateUrl();
                }, 100);
            });
            
            // Listen to show_variation event (when variation is displayed)
            $variationForm.on('show_variation', function(event, variation) {
                // Skip if manual click is in progress
                if (skipSyncAfterClick) return;
                setTimeout(function() {
                    if (skipSyncAfterClick) return;
                    syncSwatchVisualState();
                    updateUrl();
                }, 100);
            });
            
            // Listen to WooCommerce variation form updates
            $(document.body).on('wc_variation_form', function() {
                // Skip if manual click is in progress
                if (skipSyncAfterClick) return;
                setTimeout(function() {
                    if (skipSyncAfterClick) return;
                    syncSwatchVisualState();
                    updateUrl();
                }, 200);
            });
            
            // Listen to variation select change events
            $variationForm.on('woocommerce_variation_select_change', function() {
                // Skip if manual click is in progress
                if (skipSyncAfterClick) return;
                setTimeout(function() {
                    if (skipSyncAfterClick) return;
                    syncSwatchVisualState();
                    updateUrl();
                }, 50);
            });
            
            // Listen to check_variations event
            $variationForm.on('check_variations', function() {
                // Skip if manual click is in progress
                if (skipSyncAfterClick) return;
                setTimeout(function() {
                    if (skipSyncAfterClick) return;
                    syncSwatchVisualState();
                }, 100);
            });
            
            // Use MutationObserver to detect when hidden inputs are updated by swatch plugins
            if (typeof MutationObserver !== 'undefined') {
                var observer = new MutationObserver(function(mutations) {
                    // Skip if manual click is in progress - our click handler manages visual state
                    if (skipSyncAfterClick) {
                        return;
                    }
                    
                    mutations.forEach(function(mutation) {
                        if (mutation.type === 'attributes' && mutation.attributeName === 'value') {
                            // Double check the flag in case it was set during iteration
                            if (skipSyncAfterClick) return;
                            
                            var $target = $(mutation.target);
                            if ($target.is('input[name^="attribute_"]') || $target.is('select[name^="attribute_"]')) {
                                // Update visual state IMMEDIATELY when value changes
                                var fieldValue = $target.val();
                                var $valueContainer = $target.closest('.value');
                                if (!$valueContainer.length) {
                                    var $row = $target.closest('tr');
                                    if ($row.length) {
                                        $valueContainer = $row.find('.value');
                                    }
                                }
                                
                                if ($valueContainer.length) {
                                    var $allSwatches = $valueContainer.find('.wcboost-variation-swatches__item, .product-variation-item');
                                    $allSwatches.removeClass('selected active is-selected');
                                    $allSwatches.attr('aria-pressed', 'false');
                                    $allSwatches.css('border', '');
                                    
                                    if (fieldValue && fieldValue !== '') {
                                        $allSwatches.each(function() {
                                            var $swatch = $(this);
                                            var swatchValue = $swatch.attr('data-value') || $swatch.data('value');
                                            if (swatchValue && (swatchValue === fieldValue || swatchValue.toLowerCase() === fieldValue.toLowerCase())) {
                                                $swatch.addClass('selected active is-selected');
                                                $swatch.attr('aria-pressed', 'true');
                                                $swatch.css('border', '2px solid #1d2128');
                                                return false;
                                            }
                                        });
                                    }
                                }
                                
                                // Also run full sync (only if not skipping)
                                if (!skipSyncAfterClick) {
                                    syncSwatchVisualState();
                                    updateUrl();
                                }
                            }
                        }
                    });
                });
                
                // Observe the variation form for attribute changes
                var formElement = $variationForm[0];
                if (formElement) {
                    observer.observe(formElement, {
                        attributes: true,
                        attributeFilter: ['value'],
                        subtree: true
                    });
                }
            }
            
            // Initial URL update if there are already selected attributes
            setTimeout(function() {
                updateUrl();
                syncSwatchVisualState();
            }, 500);
            
            // Also sync after a longer delay to catch late-loading swatches
            setTimeout(syncSwatchVisualState, 1000);
            setTimeout(syncSwatchVisualState, 2000);
        }
        
        // Function to sync visual state of swatches with form field values
        function syncSwatchVisualState(force) {
            // Skip sync if we just manually updated visual state (unless forced)
            if (!force && skipSyncAfterClick) {
                return;
            }
            
            if (!$('body').hasClass('single-product')) {
                return;
            }
            
            var $variationForm = $('form.variations_form');
            if (!$variationForm.length) {
                return;
            }
            
            // Update visual state for each attribute
            $variationForm.find('select[name^="attribute_"], input[name^="attribute_"][type="hidden"]').each(function() {
                var $field = $(this);
                var fieldName = $field.attr('name');
                var fieldValue = $field.val();
                
                // Find the value container for this field
                var $valueContainer = $field.closest('.value');
                if (!$valueContainer.length) {
                    // Try to find by looking for the label
                    var $row = $field.closest('tr');
                    if ($row.length) {
                        $valueContainer = $row.find('.value');
                    }
                }
                
                // Also try finding by attribute name matching
                if (!$valueContainer.length) {
                    // Find all value containers and match by attribute name
                    $variationForm.find('.value').each(function() {
                        var $vc = $(this);
                        var $vcSelect = $vc.find('select[name^="attribute_"]');
                        if ($vcSelect.length && $vcSelect.attr('name') === fieldName) {
                            $valueContainer = $vc;
                            return false;
                        }
                    });
                }
                
                if (!$valueContainer.length) {
                    return;
                }
                
                // Find all swatches/items in this attribute group
                var $allSwatches = $valueContainer.find('.wcboost-variation-swatches__item, .product-variation-item');
                
                if (!$allSwatches.length) {
                    return;
                }
                
                // Remove selected state from all items in this group first
                $allSwatches.removeClass('selected active is-selected');
                $allSwatches.attr('aria-pressed', 'false');
                $allSwatches.removeAttr('aria-pressed');
                
                // If a value is selected, mark the corresponding swatch as selected
                if (fieldValue && fieldValue !== '') {
                    var $selectedSwatch = null;
                    
                    // Method 1: Try exact match with data-value
                    $allSwatches.each(function() {
                        var $swatch = $(this);
                        var swatchValue = $swatch.attr('data-value') || $swatch.data('value');
                        
                        if (swatchValue) {
                            // Exact match
                            if (swatchValue === fieldValue) {
                                $selectedSwatch = $swatch;
                                return false;
                            }
                            // Case-insensitive match
                            if (swatchValue.toLowerCase() === fieldValue.toLowerCase()) {
                                $selectedSwatch = $swatch;
                                return false;
                            }
                        }
                    });
                    
                    // Method 2: Try normalized matching (remove special chars)
                    if (!$selectedSwatch || !$selectedSwatch.length) {
                        var normalizedFieldValue = fieldValue.toLowerCase().replace(/[^a-z0-9]/g, '');
                        $allSwatches.each(function() {
                            var $swatch = $(this);
                            var swatchValue = $swatch.attr('data-value') || $swatch.data('value');
                            if (swatchValue) {
                                var normalizedSwatchValue = swatchValue.toLowerCase().replace(/[^a-z0-9]/g, '');
                                if (normalizedSwatchValue === normalizedFieldValue && normalizedFieldValue.length > 0) {
                                    $selectedSwatch = $swatch;
                                    return false;
                                }
                            }
                        });
                    }
                    
                    // Method 3: Try matching by text content (for product-variation-item)
                    if (!$selectedSwatch || !$selectedSwatch.length) {
                        var normalizedFieldValue = fieldValue.toLowerCase().replace(/[^a-z0-9]/g, '');
                        $allSwatches.each(function() {
                            var $swatch = $(this);
                            var swatchText = $swatch.attr('data-text') || $swatch.data('text') || $swatch.text().trim();
                            if (swatchText) {
                                var normalizedText = swatchText.toLowerCase().replace(/[^a-z0-9]/g, '');
                                if (normalizedText === normalizedFieldValue && normalizedFieldValue.length > 0) {
                                    $selectedSwatch = $swatch;
                                    return false;
                                }
                            }
                        });
                    }
                    
                    // Update visual state of selected swatch - use !important via inline style if needed
                    if ($selectedSwatch && $selectedSwatch.length) {
                        $selectedSwatch.addClass('selected active is-selected');
                        $selectedSwatch.attr('aria-pressed', 'true');
                        // Force the border style if needed
                        $selectedSwatch.css('border', '2px solid #1d2128');
                    }
                } else {
                    // No value selected - ensure no swatches have border
                    $allSwatches.css('border', '');
                }
            });
        }
        
        // Expose sync function globally for external access
        window.shopwellSyncSwatchVisualState = syncSwatchVisualState;
        
        // Direct watcher for select field changes - runs immediately
        function watchSelectFields() {
            if (!$('body').hasClass('single-product')) {
                return;
            }
            
            var $variationForm = $('form.variations_form');
            if (!$variationForm.length) {
                return;
            }
            
            // Watch for changes on select fields - use both change event and value property changes
            $variationForm.find('select[name^="attribute_"]').each(function() {
                var $select = $(this);
                var lastValue = $select.val();
                
                // Also watch the value property directly with polling
                var checkValue = function() {
                    // Skip if we just manually updated
                    if (skipSyncAfterClick) {
                        return;
                    }
                    
                    var currentValue = $select.val();
                    if (currentValue !== lastValue) {
                        lastValue = currentValue;
                        // Immediately update visual state
                        var $valueContainer = $select.closest('.value');
                        if (!$valueContainer.length) {
                            var $row = $select.closest('tr');
                            if ($row.length) {
                                $valueContainer = $row.find('.value');
                            }
                        }
                        
                        if ($valueContainer.length) {
                            var $allSwatches = $valueContainer.find('.wcboost-variation-swatches__item, .product-variation-item');
                            $allSwatches.removeClass('selected active is-selected');
                            $allSwatches.attr('aria-pressed', 'false');
                            $allSwatches.css('border', '');
                            
                            if (currentValue && currentValue !== '') {
                                $allSwatches.each(function() {
                                    var $swatch = $(this);
                                    var swatchValue = $swatch.attr('data-value') || $swatch.data('value');
                                    if (swatchValue && (swatchValue === currentValue || swatchValue.toLowerCase() === currentValue.toLowerCase())) {
                                        $swatch.addClass('selected active is-selected');
                                        $swatch.attr('aria-pressed', 'true');
                                        $swatch.css('border', '2px solid #1d2128');
                                        return false;
                                    }
                                });
                            }
                        }
                        
                        syncSwatchVisualState(true); // Force sync when value actually changes
                    }
                };
                
                // Check frequently to catch changes immediately
                setInterval(checkValue, 50);
            });
            
            // Also watch hidden inputs
            $variationForm.find('input[name^="attribute_"][type="hidden"]').each(function() {
                var $input = $(this);
                var lastValue = $input.val();
                
                // Watch for value changes
                var checkValue = function() {
                    // Skip if we just manually updated
                    if (skipSyncAfterClick) {
                        return;
                    }
                    
                    var currentValue = $input.val();
                    if (currentValue !== lastValue) {
                        lastValue = currentValue;
                        if (!skipSyncAfterClick) {
                            syncSwatchVisualState();
                        }
                    }
                };
                
                // Check frequently
                setInterval(checkValue, 50);
            });
        }
        
        // Flag to prevent sync from running immediately after manual click
        var skipSyncAfterClick = false;
        var skipSyncTimeout = null;
        
        // SIMPLE click handler for variation swatches
        function enableVariationSelectDeselect() {
            if (!$('body').hasClass('single-product')) return;
            
            var $form = $('form.variations_form');
            if (!$form.length) return;
            
            console.log('[SHOPWELL] Setting up simple click handler');
            
            // Remove old handler if exists
            if (window.shopwellClickHandler) {
                document.removeEventListener('click', window.shopwellClickHandler, true);
            }
            
            window.shopwellClickHandler = function(e) {
                var $target = $(e.target);
                var $swatch = $target.closest('.wcboost-variation-swatches__item, .product-variation-item');
                
                if (!$swatch.length) return;
                
                // Stop all other handlers
                e.preventDefault();
                e.stopImmediatePropagation();
                
                // Set flag to prevent other sync functions from interfering
                skipSyncAfterClick = true;
                if (skipSyncTimeout) {
                    clearTimeout(skipSyncTimeout);
                }
                skipSyncTimeout = setTimeout(function() {
                    skipSyncAfterClick = false;
                }, 500);
                
                // Check if swatch is disabled - don't allow selection
                if ($swatch.hasClass('disabled') || $swatch.hasClass('shopwell-disabled')) {
                    console.log('[SHOPWELL] Swatch is disabled, ignoring click');
                    skipSyncAfterClick = false;
                    return;
                }
                
                // Get swatch value
                var swatchValue = $swatch.data('value') || $swatch.attr('data-value');
                console.log('[SHOPWELL] Clicked swatch value:', swatchValue);
                
                if (!swatchValue) {
                    console.log('[SHOPWELL] No swatch value found');
                    return;
                }
                
                // Find the select field in the same row/container
                var $select = $swatch.closest('.value').find('select[name^="attribute_"]');
                
                // If not found, try parent row
                if (!$select.length) {
                    $select = $swatch.closest('tr').find('select[name^="attribute_"]');
                }
                
                console.log('[SHOPWELL] Select found:', $select.length > 0, $select.attr('name'));
                
                if (!$select.length) {
                    console.log('[SHOPWELL] ERROR: No select field found');
                    return;
                }
                
                // Get current value
                var currentValue = $select.val() || '';
                console.log('[SHOPWELL] Current value:', currentValue, 'Swatch value:', swatchValue);
                
                // Check if swatch is visually selected
                var isVisuallySelected = $swatch.hasClass('selected') || 
                                        $swatch.hasClass('active') || 
                                        $swatch.hasClass('is-selected');
                
                // Decide: select or deselect
                // If value matches OR swatch is visually selected, then deselect
                var isSameValue = currentValue && currentValue.toLowerCase() === swatchValue.toLowerCase();
                var shouldDeselect = isSameValue || isVisuallySelected;
                
                console.log('[SHOPWELL] isSameValue:', isSameValue, 'isVisuallySelected:', isVisuallySelected, 'shouldDeselect:', shouldDeselect);
                
                if (shouldDeselect) {
                    // DESELECT - clear the field
                    console.log('[SHOPWELL] DESELECTING');
                    
                    // Clear select immediately - multiple times to ensure it sticks
                    $select.val('');
                    $select.prop('selectedIndex', 0);
                    $select.trigger('change');
                    
                    // Clear visual state immediately
                    $swatch.removeClass('selected active is-selected');
                    $swatch.css('border', '');
                    
                    // Also clear from all swatches in this container
                    var $container = $swatch.closest('.value');
                    if (!$container.length) {
                        $container = $swatch.closest('tr').find('.value');
                    }
                    if ($container.length) {
                        $container.find('.wcboost-variation-swatches__item, .product-variation-item')
                            .removeClass('selected active is-selected')
                            .css('border', '');
                    }
                    
                    // Force clear multiple times to prevent other scripts from re-setting
                    setTimeout(function() {
                        $select.val('');
                        $select.prop('selectedIndex', 0);
                        $swatch.removeClass('selected active is-selected').css('border', '');
                    }, 50);
                    
                    setTimeout(function() {
                        $select.val('');
                        $swatch.removeClass('selected active is-selected').css('border', '');
                    }, 150);
                    
                    setTimeout(function() {
                        $select.val('');
                        $swatch.removeClass('selected active is-selected').css('border', '');
                    }, 300);
                    
                } else {
                    // SELECT - set the new value
                    console.log('[SHOPWELL] SELECTING:', swatchValue);
                    
                    // Set select value
                    $select.val(swatchValue).trigger('change');
                    
                    // Update visual - remove from others, add to this one
                    var $container = $swatch.closest('.value');
                    if (!$container.length) {
                        $container = $swatch.closest('tr').find('.value');
                    }
                    if ($container.length) {
                        $container.find('.wcboost-variation-swatches__item, .product-variation-item')
                            .removeClass('selected active is-selected')
                            .css('border', '');
                    }
                    $swatch.addClass('selected active is-selected').css('border', '2px solid #1d2128');
                    
                    // Force set after a small delay to ensure it sticks
                    setTimeout(function() {
                        $select.val(swatchValue);
                        $swatch.addClass('selected active is-selected').css('border', '2px solid #1d2128');
                    }, 50);
                }
                
                // Update URL
                setTimeout(function() {
                    if (typeof window.shopwellUpdateUrl === 'function') {
                        console.log('[SHOPWELL] Updating URL');
                        window.shopwellUpdateUrl();
                    }
                }, 100);
                
                // Update disabled states after selection change
                setTimeout(updateDisabledSwatches, 150);
                
                console.log('[SHOPWELL] Done. New select value:', $select.val());
            };
            
            document.addEventListener('click', window.shopwellClickHandler, true);
            console.log('[SHOPWELL] Click handler registered');
        }
        
        // Function to update disabled state of swatches based on available variations
        function updateDisabledSwatches() {
            var $form = $('form.variations_form');
            if (!$form.length) return;
            
            // Get all select fields
            $form.find('select[name^="attribute_"]').each(function() {
                var $select = $(this);
                var $container = $select.closest('.value');
                
                if (!$container.length) {
                    $container = $select.closest('tr').find('.value');
                }
                
                if (!$container.length) return;
                
                var $swatches = $container.find('.wcboost-variation-swatches__item, .product-variation-item');
                
                // Check each swatch against available options in select
                $swatches.each(function() {
                    var $swatch = $(this);
                    var swatchValue = $swatch.data('value') || $swatch.attr('data-value');
                    
                    if (!swatchValue) return;
                    
                    // Check if this value exists in the select options
                    var $option = $select.find('option[value="' + swatchValue + '"]');
                    var optionExists = $option.length > 0;
                    
                    // Also check case-insensitive
                    if (!optionExists) {
                        $select.find('option').each(function() {
                            if ($(this).val().toLowerCase() === swatchValue.toLowerCase()) {
                                optionExists = true;
                                return false;
                            }
                        });
                    }
                    
                    if (optionExists) {
                        // Enable swatch
                        $swatch.removeClass('disabled shopwell-disabled');
                    } else {
                        // Disable swatch
                        $swatch.addClass('shopwell-disabled');
                        $swatch.removeClass('selected active is-selected');
                    }
                });
            });
        }
        
        // ============================================
        // SIMPLIFIED INITIALIZATION - No complex syncing
        // ============================================
        
        // Run on page load
        console.log('[SHOPWELL] Starting simple initialization');
        autoSelectFirstVariation();
        handleFilterVariations();
        updateUrlWithVariations();
        enableVariationSelectDeselect();
        
        // Update disabled states initially and after a delay
        setTimeout(updateDisabledSwatches, 200);
        setTimeout(updateDisabledSwatches, 500);
        
        console.log('[SHOPWELL] Initialization complete');
        
        // Also run click handler setup on window load (after smart-variations loads)
        $(window).on('load', function() {
            setTimeout(function() {
                enableVariationSelectDeselect();
                updateDisabledSwatches();
                console.log('[SHOPWELL] Re-registered click handler after window load');
            }, 100);
        });
        
        // Re-enable when WooCommerce variation form is initialized
        $(document.body).on('wc_variation_form', function() {
            setTimeout(enableVariationSelectDeselect, 200);
            setTimeout(updateDisabledSwatches, 300);
        });
        
        // Listen to WooCommerce variation events to update disabled states
        $('form.variations_form').on('woocommerce_variation_select_change check_variations', function() {
            setTimeout(updateDisabledSwatches, 50);
        });
        
        // Also listen for any change on select fields
        $('form.variations_form').on('change', 'select[name^="attribute_"]', function() {
            setTimeout(updateDisabledSwatches, 50);
        });
    });
    </script>
    <?php
}
add_action('wp_footer', 'shopwell_auto_select_variations', 999);

/**
 * Fix Google Pay locale issue
 * Google Pay doesn't support Romanian locale ("ro"), so we force English
 * This prevents console errors when payment gateways try to use "ro" locale
 */
function shopwell_fix_google_pay_locale( $locale ) {
	// Google Pay supported locales: en, de, fr, it, pl, pt, ru, es, tr, etc.
	// Romanian (ro) is not supported, so we use English as fallback
	if ( $locale === 'ro' || $locale === 'ro_RO' ) {
		return 'en';
	}
	
	// Map other unsupported locales to supported ones
	$locale_map = array(
		'ro'     => 'en',
		'ro_RO'  => 'en',
		'ro_MD'  => 'en',
	);
	
	if ( isset( $locale_map[ $locale ] ) ) {
		return $locale_map[ $locale ];
	}
	
	return $locale;
}

// Apply to common payment gateway filters
add_filter( 'woocommerce_stripe_payment_request_button_locale', 'shopwell_fix_google_pay_locale', 10, 1 );
add_filter( 'woocommerce_gateway_stripe_payment_request_button_locale', 'shopwell_fix_google_pay_locale', 10, 1 );
add_filter( 'wc_stripe_payment_request_button_locale', 'shopwell_fix_google_pay_locale', 10, 1 );
add_filter( 'woocommerce_paypal_locale', 'shopwell_fix_google_pay_locale', 10, 1 );
add_filter( 'woocommerce_paypal_payment_request_button_locale', 'shopwell_fix_google_pay_locale', 10, 1 );

// Generic filter for any payment gateway that uses locale
add_filter( 'woocommerce_payment_gateway_locale', 'shopwell_fix_google_pay_locale', 10, 1 );

/**
 * Register WooCommerce Memory Attribute
 * Creates a "Memorie" attribute for products with common memory values
 * 
 * @return void
 */
function shopwell_register_memory_attribute() {
    // Check if WooCommerce is active
    if (!class_exists('WooCommerce')) {
        return;
    }
    
    $attribute_name = 'memorie';
    $attribute_label = 'Memorie';
    $taxonomy_name = 'pa_memorie';
    
    // Check if attribute already exists
    $existing_attributes = wc_get_attribute_taxonomies();
    $attribute_exists = false;
    
    foreach ($existing_attributes as $attribute) {
        if ($attribute->attribute_name === $attribute_name) {
            $attribute_exists = true;
            break;
        }
    }
    
    // Create attribute if it doesn't exist
    if (!$attribute_exists) {
        $args = array(
            'slug'    => $attribute_name,
            'name'    => $attribute_label,
            'type'    => 'select',
            'orderby' => 'menu_order',
            'has_archives' => false,
        );
        
        $result = wc_create_attribute($args);
        
        if (is_wp_error($result)) {
            shopwell_log_to_file('Failed to create memory attribute: ' . $result->get_error_message(), null, 'php');
            return;
        }
        
        // Register taxonomy
        register_taxonomy($taxonomy_name, 'product');
        
        // Flush rewrite rules to make the taxonomy available
        flush_rewrite_rules();
    }
    
    // Ensure taxonomy is registered
    if (!taxonomy_exists($taxonomy_name)) {
        register_taxonomy($taxonomy_name, 'product');
    }
    
    // Add common memory values if taxonomy is empty
    if (taxonomy_exists($taxonomy_name)) {
        $memory_values = array('16gb', '32gb', '64gb', '128gb', '256gb', '512gb', '1tb');
        
        foreach ($memory_values as $value) {
            $term_exists = term_exists($value, $taxonomy_name);
            if (!$term_exists) {
                wp_insert_term(
                    ucfirst($value), // Display name
                    $taxonomy_name,
                    array(
                        'slug' => $value,
                        'description' => 'Memorie ' . ucfirst($value)
                    )
                );
            }
        }
    }
}

// Run on theme activation and admin init
add_action('admin_init', 'shopwell_register_memory_attribute');

/**
 * Get min and max price for price filter widget
 * This function replaces the protected method from WC_Widget_Price_Filter
 */
function shopwell_get_price_filter_range() {
    global $wpdb;
    
    // Always use our filtered price range function to get prices from filtered products
    // This ensures the slider always reflects the actual min/max prices of visible products
    $range = shopwell_get_filtered_price_range( 'both' );
    
    // If we don't have a valid range, fall back to global prices
    // Only fallback if range is empty or max is 999999 (which indicates no filtered products found)
    if ( empty( $range ) || ! isset( $range['min'] ) || ! isset( $range['max'] ) || $range['max'] === 999999 ) {
        // Return global min/max prices using a simpler approach
        $price_min = $wpdb->get_var( "
            SELECT MIN(meta_value + 0)
            FROM {$wpdb->postmeta} pm
            INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
            WHERE pm.meta_key = '_price'
            AND p.post_type = 'product'
            AND p.post_status = 'publish'
        " );
        
        $price_max = $wpdb->get_var( "
            SELECT MAX(meta_value + 0)
            FROM {$wpdb->postmeta} pm
            INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
            WHERE pm.meta_key = '_price'
            AND p.post_type = 'product'
            AND p.post_status = 'publish'
        " );
        
        // Ensure we have valid prices
        if ( empty( $price_min ) || empty( $price_max ) ) {
            return (object) array(
                'min_price' => 0,
                'max_price' => 1000,
            );
        }
        
        return (object) array(
            'min_price' => floor( $price_min ),
            'max_price' => ceil( $price_max ),
        );
    }
    
    return (object) array(
        'min_price' => floor( $range['min'] ),
        'max_price' => ceil( $range['max'] ),
    );
}

/**
 * Fix memory filter association issue
 * Ensures memory filter uses correct attribute taxonomy
 */
function shopwell_fix_memory_filter_association() {
    // Check if we're on a shop page with memory filter
    if (!(is_shop() || is_product_category() || is_product_tag() || is_product_taxonomy())) {
        return;
    }
    
    // Check if memory filter is incorrectly associated with color attribute
    if (isset($_GET['filter_pa_culoare']) && strpos($_GET['filter_pa_culoare'], 'gb') !== false) {
        // This is likely a memory value in the color filter
        $memory_value = sanitize_text_field($_GET['filter_pa_culoare']);
        
        // Redirect to correct memory filter
        $current_url = remove_query_arg('filter_pa_culoare');
        $correct_url = add_query_arg('filter_pa_memorie', $memory_value, $current_url);
        
        wp_redirect($correct_url);
        exit;
    }
}
add_action('template_redirect', 'shopwell_fix_memory_filter_association', 5);

/**
 * Enqueue custom CSS fixes
 * This loads custom CSS that won't be overwritten during theme updates
 */
/**
 * Hide model filter widget when no category is selected
 * Show only models that belong to the selected category
 */
function shopwell_conditionally_show_model_filter($params) {
    global $wp_registered_widgets;
    
    // Check if we have valid params
    if (empty($params) || !isset($params[0]['widget_id'])) {
        return $params;
    }
    
    $widget_id = $params[0]['widget_id'];
    
    // Check if widget is registered
    if (!isset($wp_registered_widgets[$widget_id])) {
        return $params;
    }
    
    // Get widget instance
    $widget_obj = $wp_registered_widgets[$widget_id];
    
    // Check if this is a WooCommerce layered nav widget
    if (!isset($widget_obj['callback']) || !is_array($widget_obj['callback'])) {
        return $params;
    }
    
    // Get widget settings
    $settings_getter = $widget_obj['callback'][0];
    
    if (!is_object($settings_getter)) {
        return $params;
    }
    
    // Get widget settings
    $settings = $settings_getter->get_settings();
    $widget_number = isset($params[1]['number']) ? $params[1]['number'] : 0;
    $widget_instance = !empty($settings) && isset($settings[$widget_number]) ? $settings[$widget_number] : array();
    
    // Only apply to layered nav widgets
    if (empty($widget_instance) || !isset($widget_instance['attribute']) || empty($widget_instance['attribute'])) {
        return $params;
    }
    
    // Check if this is the model attribute (adjust attribute name as needed)
    // Common attribute names: pa_model, pa_marca, model, marca
    $attribute_name = $widget_instance['attribute'];
    $is_model_filter = false;
    
    // Check if this widget is for models (adjust these checks based on your actual attribute name)
    if (in_array($attribute_name, array('model', 'marca', 'pa_model', 'pa_marca'))) {
        $is_model_filter = true;
    } else {
        // Also check by widget title
        $widget_title = isset($widget_instance['title']) ? strtolower($widget_instance['title']) : '';
        if (strpos($widget_title, 'model') !== false || strpos($widget_title, 'marca') !== false) {
            $is_model_filter = true;
        }
    }
    
    if (!$is_model_filter) {
        return $params;
    }
    
    // Check if a category is selected
    $selected_category = null;
    
    // Check URL parameter
    if (isset($_GET['product_cat']) && !empty($_GET['product_cat'])) {
        $selected_category = sanitize_text_field($_GET['product_cat']);
    }
    
    // Check if we're on a category page
    if (!$selected_category && is_product_category()) {
        $queried_object = get_queried_object();
        if ($queried_object && isset($queried_object->slug)) {
            $selected_category = $queried_object->slug;
        }
    }
    
    // If no category is selected, hide the widget
    if (!$selected_category) {
        // Add CSS class to hide the widget
        if (isset($params[0]['before_widget'])) {
            // Add class and inline style to hide
            $params[0]['before_widget'] = str_replace('class="', 'class="shopwell-model-filter-widget ', $params[0]['before_widget']);
            // Add inline style to hide if not already present
            if (strpos($params[0]['before_widget'], 'style=') === false) {
                $params[0]['before_widget'] = str_replace('>', ' style="display:none;">', $params[0]['before_widget']);
            } else {
                // If style already exists, add display:none to it
                $params[0]['before_widget'] = preg_replace('/style="([^"]*)"/', 'style="$1 display:none;"', $params[0]['before_widget']);
            }
        }
        
        return $params;
    }
    
    // Category is selected, show the widget but filter terms
    // Add data attribute to identify this as model filter
    if (isset($params[0]['before_widget'])) {
        $params[0]['before_widget'] = str_replace('class="', 'class="shopwell-model-filter-widget ', $params[0]['before_widget']);
        // Add data attribute
        if (strpos($params[0]['before_widget'], 'data-category=') === false) {
            $params[0]['before_widget'] = str_replace('>', ' data-category="' . esc_attr($selected_category) . '">', $params[0]['before_widget']);
        }
    }
    
    return $params;
}
add_filter('dynamic_sidebar_params', 'shopwell_conditionally_show_model_filter', 20);

/**
 * Filter model terms to show only those that belong to the selected category
 * But keep all terms visible even when a model filter is applied
 */
function shopwell_filter_model_terms_by_category($terms, $taxonomy, $query_type) {
    // Only filter model/marca attributes
    if (strpos($taxonomy, 'pa_model') === false && strpos($taxonomy, 'pa_marca') === false && 
        strpos($taxonomy, 'model') === false && strpos($taxonomy, 'marca') === false) {
        return $terms;
    }
    
    // Get selected category
    $selected_category = null;
    
    if (isset($_GET['product_cat']) && !empty($_GET['product_cat'])) {
        $selected_category = sanitize_text_field($_GET['product_cat']);
    } elseif (is_product_category()) {
        $queried_object = get_queried_object();
        if ($queried_object && isset($queried_object->slug)) {
            $selected_category = $queried_object->slug;
        }
    }
    
    if (!$selected_category) {
        return array(); // Return empty array if no category selected
    }
    
    // Get category term
    $category_term = get_term_by('slug', $selected_category, 'product_cat');
    if (!$category_term) {
        return $terms;
    }
    
    // Check if a model filter is currently active
    $model_filter_active = isset($_GET['filter_pa_model']) && !empty($_GET['filter_pa_model']);
    
    // Get products in this category (without model filter to show all models)
    // IMPORTANT: Don't include model filter in this query
    // OPTIMIZARE: LimiteazÄ la 500 produse Ă®n loc de -1 pentru performanČ›Ä mai bunÄ
    $args = array(
        'post_type' => 'product',
        'posts_per_page' => 500, // LimiteazÄ la 500 pentru performanČ›Ä (Ă®nainte: -1)
        'post_status' => 'publish',
        'tax_query' => array(
            array(
                'taxonomy' => 'product_cat',
                'field' => 'term_id',
                'terms' => $category_term->term_id,
            ),
        ),
        'fields' => 'ids', // Doar ID-uri pentru performanČ›Ä optimÄ
    );
    
    // Add other active filters (except model filter) to get accurate counts
    if (isset($_GET['filter_pa_culoare']) && !empty($_GET['filter_pa_culoare'])) {
        $args['tax_query'][] = array(
            'taxonomy' => 'pa_culoare',
            'field' => 'slug',
            'terms' => sanitize_text_field($_GET['filter_pa_culoare']),
        );
    }
    if (isset($_GET['filter_pa_stare']) && !empty($_GET['filter_pa_stare'])) {
        $args['tax_query'][] = array(
            'taxonomy' => 'pa_stare',
            'field' => 'slug',
            'terms' => sanitize_text_field($_GET['filter_pa_stare']),
        );
    }
    if (isset($_GET['filter_pa_memorie']) && !empty($_GET['filter_pa_memorie'])) {
        $args['tax_query'][] = array(
            'taxonomy' => 'pa_memorie',
            'field' => 'slug',
            'terms' => sanitize_text_field($_GET['filter_pa_memorie']),
        );
    }
    // DO NOT add filter_pa_model here - we want to show all models
    
    $product_ids = get_posts($args);
    
    if (empty($product_ids)) {
        return array();
    }
    
    // ALWAYS get ALL terms from taxonomy that have products in the selected category
    // This ensures all models remain visible even when one is selected
    // Get all terms for this taxonomy
    $all_terms = get_terms(array(
        'taxonomy' => $taxonomy,
        'hide_empty' => false,
    ));
    
    if (is_wp_error($all_terms) || empty($all_terms)) {
        // Fallback to original terms if we can't get all terms
        return $terms;
    }
    
    // Filter to only include terms that have products in the selected category
    // IMPORTANT: Don't include model filter in this check - we want all models visible
    $filtered_terms = array();
    foreach ($all_terms as $term) {
        // Check if this term has products in the category (without model filter)
        $has_products = get_posts(array(
            'post_type' => 'product',
            'posts_per_page' => 1,
            'post_status' => 'publish',
            'post__in' => $product_ids,
            'tax_query' => array(
                array(
                    'taxonomy' => $taxonomy,
                    'field' => 'term_id',
                    'terms' => $term->term_id,
                ),
            ),
            'fields' => 'ids',
        ));
        
        if (!empty($has_products)) {
            $filtered_terms[] = $term;
        }
    }
    
    return $filtered_terms;
}
add_filter('woocommerce_layered_nav_get_terms', 'shopwell_filter_model_terms_by_category', 10, 3);

/**
 * Sort product categories by count in descending order
 * This ensures categories with more products appear first in the filter list
 */
function shopwell_sort_categories_by_count($terms, $taxonomy, $query_type) {
    // Only apply to product categories
    if ($taxonomy !== 'product_cat') {
        return $terms;
    }
    
    // Check if terms is valid
    if (empty($terms) || !is_array($terms)) {
        return $terms;
    }
    
    // Sort terms by count in descending order
    usort($terms, function($a, $b) {
        $count_a = isset($a->count) ? (int)$a->count : 0;
        $count_b = isset($b->count) ? (int)$b->count : 0;
        return $count_b - $count_a; // Descending order
    });
    
    return $terms;
}
add_filter('woocommerce_layered_nav_get_terms', 'shopwell_sort_categories_by_count', 20, 3);

/**
 * Sort product categories by count using JavaScript
 * This ensures categories are sorted by count in the DOM after rendering
 */
function shopwell_sort_categories_by_count_js() {
    // Only on shop pages
    if (!function_exists('is_shop') || !(is_shop() || is_product_category() || is_product_tag() || is_product_taxonomy())) {
        return;
    }
    ?>
    <script type="text/javascript">
    jQuery(document).ready(function($) {
        var isSorting = false;
        var lastSortTime = 0;
        var sortCooldown = 1000; // Minimum time between sorts (1 second)
        var observer = null;
        
        function getCount($item) {
            var $countEl = $item.find('.products-filter__count.counter, .counter, span.counter');
            if ($countEl.length) {
                return parseInt($countEl.first().text().trim()) || 0;
            }
            return 0;
        }
        
        function isAlreadySorted($items) {
            // Check if items are already sorted by count (descending)
            var prevCount = Infinity;
            for (var i = 0; i < $items.length; i++) {
                var count = getCount($($items[i]));
                if (count > prevCount) {
                    return false; // Not sorted
                }
                prevCount = count;
            }
            return true; // Already sorted
        }
        
        function sortCategoriesByCount() {
            // Prevent multiple simultaneous sorts
            if (isSorting) {
                return;
            }
            
            // Cooldown check
            var now = Date.now();
            if (now - lastSortTime < sortCooldown) {
                return;
            }
            
            isSorting = true;
            var sorted = false;
            
            // Temporarily disconnect observer to prevent infinite loop
            if (observer) {
                observer.disconnect();
            }
            
            // Find all category filter lists
            $('ul.products-filter__options.filter-list, ul.products-filter--list.filter-list').each(function() {
                var $list = $(this);
                var $items = $list.find('> li.products-filter__option.filter-list-item');
                
                if ($items.length <= 1) {
                    return; // Skip if no items or only one item
                }
                
                // Check if already sorted
                if (isAlreadySorted($items)) {
                    return; // Skip if already sorted
                }
                
                // Sort items by count (descending order)
                var sortedItems = $items.toArray().sort(function(a, b) {
                    var countA = getCount($(a));
                    var countB = getCount($(b));
                    return countB - countA; // Descending order
                });
                
                // Only update if order changed
                var orderChanged = false;
                for (var i = 0; i < sortedItems.length; i++) {
                    if (sortedItems[i] !== $items[i]) {
                        orderChanged = true;
                        break;
                    }
                }
                
                if (orderChanged) {
                    // Detach items, clear list, then append sorted items
                    $list.empty().append(sortedItems);
                    sorted = true;
                }
            });
            
            lastSortTime = Date.now();
            isSorting = false;
            
            // Reconnect observer after a short delay
            if (observer) {
                setTimeout(function() {
                    var $filterSidebar = $('#filter-sidebar-panel, .catalog-filters-sidebar, .shopwell-filter-products-wrapper');
                    if ($filterSidebar.length) {
                        observer.observe($filterSidebar[0], {
                            childList: true,
                            subtree: true
                        });
                    }
                }, 500);
            }
            
            return sorted;
        }
        
        // Sort on page load (with delay to ensure DOM is ready)
        setTimeout(function() {
            sortCategoriesByCount();
        }, 1000);
        
        // Sort after AJAX updates (for filtered products) - but only once per event
        var ajaxSortTimeout = null;
        $(document).on('shopwell_ajax_update_complete updated_wc_div', function() {
            if (ajaxSortTimeout) {
                clearTimeout(ajaxSortTimeout);
            }
            ajaxSortTimeout = setTimeout(function() {
                sortCategoriesByCount();
                ajaxSortTimeout = null;
            }, 500);
        });
        
        // Use MutationObserver - but only for new content, not our own changes
        if (typeof MutationObserver !== 'undefined') {
            observer = new MutationObserver(function(mutations) {
                // Don't react if we're currently sorting
                if (isSorting) {
                    return;
                }
                
                var shouldSort = false;
                mutations.forEach(function(mutation) {
                    // Only react to added nodes, not removed nodes (which we do when sorting)
                    if (mutation.addedNodes.length && mutation.removedNodes.length === 0) {
                        for (var i = 0; i < mutation.addedNodes.length; i++) {
                            var node = mutation.addedNodes[i];
                            if (node.nodeType === 1) { // Element node
                                var $node = $(node);
                                // Only sort if new filter list items are added, not if we're just reordering
                                if ($node.hasClass('products-filter__options') || 
                                    ($node.hasClass('filter-list-item') && $node.closest('.products-filter__options').length)) {
                                    shouldSort = true;
                                    break;
                                }
                            }
                        }
                    }
                });
                
                if (shouldSort) {
                    setTimeout(function() {
                        sortCategoriesByCount();
                    }, 300);
                }
            });
            
            // Observe the filter sidebar container
            var $filterSidebar = $('#filter-sidebar-panel, .catalog-filters-sidebar, .shopwell-filter-products-wrapper');
            if ($filterSidebar.length) {
                observer.observe($filterSidebar[0], {
                    childList: true,
                    subtree: true
                });
            }
        }
    });
    </script>
    <?php
}
add_action('wp_footer', 'shopwell_sort_categories_by_count_js', 999);

/**
 * Prevent WooCommerce from hiding model terms when a model filter is applied
 * This keeps all model options visible even after selecting one
 */
function shopwell_keep_model_terms_visible($hide_empty, $taxonomy) {
    // Only apply to model/marca attributes
    if (strpos($taxonomy, 'pa_model') !== false || strpos($taxonomy, 'pa_marca') !== false || 
        strpos($taxonomy, 'model') !== false || strpos($taxonomy, 'marca') !== false) {
        // Don't hide empty terms for model filters - keep all visible
        return false;
    }
    
    return $hide_empty;
}
add_filter('woocommerce_layered_nav_count_hide_empty', 'shopwell_keep_model_terms_visible', 10, 2);

/**
 * Modify the query used to calculate term counts for model filters
 * ALWAYS exclude model filter from the count query so all models remain visible
 */
function shopwell_model_term_counts_query($query_args, $taxonomy, $query_type) {
    // Only apply to model/marca attributes
    if (strpos($taxonomy, 'pa_model') === false && strpos($taxonomy, 'pa_marca') === false && 
        strpos($taxonomy, 'model') === false && strpos($taxonomy, 'marca') === false) {
        return $query_args;
    }
    
    // ALWAYS remove model filter from tax_query so counts are calculated without it
    // This ensures all models remain visible even when one is selected
    if (isset($query_args['tax_query']) && is_array($query_args['tax_query'])) {
        foreach ($query_args['tax_query'] as $key => $tax_query_item) {
            if (isset($tax_query_item['taxonomy']) && 
                ($tax_query_item['taxonomy'] === 'pa_model' || 
                 $tax_query_item['taxonomy'] === 'pa_marca' ||
                 strpos($tax_query_item['taxonomy'], 'model') !== false ||
                 strpos($tax_query_item['taxonomy'], 'marca') !== false)) {
                unset($query_args['tax_query'][$key]);
            }
        }
        // Re-index array
        $query_args['tax_query'] = array_values($query_args['tax_query']);
    }
    
    return $query_args;
}
add_filter('woocommerce_layered_nav_term_counts_query', 'shopwell_model_term_counts_query', 10, 3);

/**
 * Add model filter to breadcrumbs when a model is selected
 */
function shopwell_add_model_to_breadcrumbs($crumbs, $breadcrumb) {
    // Only on shop pages
    if (!function_exists('is_shop') || !(is_shop() || is_product_category() || is_product_tag() || is_product_taxonomy())) {
        return $crumbs;
    }
    
    // Check if a model filter is active
    if (isset($_GET['filter_pa_model']) && !empty($_GET['filter_pa_model'])) {
        $model_slug = sanitize_text_field($_GET['filter_pa_model']);
        
        // Get the model term
        $model_term = get_term_by('slug', $model_slug, 'pa_model');
        if (!$model_term || is_wp_error($model_term)) {
            // Try pa_marca if pa_model doesn't work
            $model_term = get_term_by('slug', $model_slug, 'pa_marca');
        }
        
        if ($model_term && !is_wp_error($model_term)) {
            // Build current URL without model filter for the breadcrumb link
            $current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
            $parsed_url = parse_url($current_url);
            $query_params = array();
            if (isset($parsed_url['query'])) {
                parse_str($parsed_url['query'], $query_params);
            }
            
            // Remove model filter from query params
            unset($query_params['filter_pa_model']);
            
            // Rebuild URL
            $model_url = $parsed_url['scheme'] . '://' . $parsed_url['host'] . $parsed_url['path'];
            if (!empty($query_params)) {
                $model_url .= '?' . http_build_query($query_params);
            }
            
            // Format model name (capitalize first letter)
            $model_name = ucfirst($model_term->name);
            
            // Ensure $crumbs is an array
            if (!is_array($crumbs)) {
                $crumbs = array();
            }
            
            // Add model to breadcrumbs before the last item
            if (!empty($crumbs)) {
                $last_item = array_pop($crumbs);
                $crumbs[] = array(
                    0 => $model_name,
                    1 => esc_url($model_url)
                );
                $crumbs[] = $last_item;
            } else {
                // If no crumbs, just add the model
                $crumbs[] = array(
                    0 => $model_name,
                    1 => esc_url($model_url)
                );
            }
        }
    }
    
    return $crumbs;
}
add_filter('woocommerce_breadcrumb_items', 'shopwell_add_model_to_breadcrumbs', 10, 2);

/**
 * Convert product category breadcrumb links to use query parameter format
 */
function shopwell_convert_category_breadcrumb_links($crumbs, $breadcrumb) {
    shopwell_log_to_file('[BREADCRUMB FILTER] shopwell_convert_category_breadcrumb_links called', null, 'php');
    shopwell_log_to_file('[BREADCRUMB FILTER] Crumbs count: ' . (is_array($crumbs) ? count($crumbs) : 'not array'), null, 'php');
    
    if (!is_array($crumbs)) {
        shopwell_log_to_file('[BREADCRUMB FILTER] Crumbs is not an array', null, 'php');
        return $crumbs;
    }
    
    // Get shop page URL
    $shop_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : '';
    if (empty($shop_url)) {
        shopwell_log_to_file('[BREADCRUMB FILTER] Shop URL is empty', null, 'php');
        return $crumbs;
    }
    shopwell_log_to_file('[BREADCRUMB FILTER] Shop URL: ' . $shop_url, null, 'php');
    
    // Get all product categories with their slugs
    $all_categories = get_terms(array(
        'taxonomy' => 'product_cat',
        'hide_empty' => false
    ));
    
    if (is_wp_error($all_categories) || empty($all_categories)) {
        shopwell_log_to_file('[BREADCRUMB FILTER] No categories found or error: ' . (is_wp_error($all_categories) ? $all_categories->get_error_message() : 'empty'), null, 'php');
        return $crumbs;
    }
    shopwell_log_to_file('[BREADCRUMB FILTER] Found ' . count($all_categories) . ' categories', null, 'php');
    
    // Create a map of slugs to term objects for quick lookup
    $category_map = array();
    foreach ($all_categories as $cat) {
        $category_map[$cat->slug] = $cat;
    }
    
    // Process each breadcrumb item
    foreach ($crumbs as $key => $crumb) {
        if (!is_array($crumb) || count($crumb) < 2) {
            continue;
        }
        
        $url = isset($crumb[1]) ? $crumb[1] : '';
        if (empty($url)) {
            continue;
        }
        
        shopwell_log_to_file('[BREADCRUMB FILTER] Processing crumb #' . $key . ': ' . $url, null, 'php');
        
        // Skip if already using query parameter format
        if (strpos($url, '?product_cat=') !== false) {
            shopwell_log_to_file('[BREADCRUMB FILTER] Already using query param format, skipping', null, 'php');
            continue;
        }
        
        // Parse URL
        $url_parts = parse_url($url);
        if (!isset($url_parts['path']) || empty($url_parts['path'])) {
            shopwell_log_to_file('[BREADCRUMB FILTER] No path in URL, skipping', null, 'php');
            continue;
        }
        
        $path = trim($url_parts['path'], '/');
        $path_parts = explode('/', $path);
        $possible_slug = end($path_parts);
        
        shopwell_log_to_file('[BREADCRUMB FILTER] Path: ' . $path . ', Possible slug: ' . $possible_slug, null, 'php');
        
        // Check if this slug matches any product category
        if (isset($category_map[$possible_slug])) {
            $category = $category_map[$possible_slug];
            shopwell_log_to_file('[BREADCRUMB FILTER] Found matching category: ' . $category->slug, null, 'php');
            
            // More aggressive check: if URL contains category-related terms or slug matches
            $url_lower = strtolower($url);
            $has_category_indicator = (
                strpos($url_lower, 'categorie') !== false ||
                strpos($url_lower, 'category') !== false ||
                strpos($url_lower, 'product-cat') !== false
            );
            
            shopwell_log_to_file('[BREADCRUMB FILTER] Has category indicator: ' . ($has_category_indicator ? 'yes' : 'no') . ', Path parts: ' . count($path_parts), null, 'php');
            
            // If we have a category match and URL suggests it's a category page
            if ($has_category_indicator || count($path_parts) >= 2) {
                // Build new URL with query parameter
                $new_url = $shop_url . '?product_cat=' . $category->slug;
                
                // Preserve any existing query parameters from current URL
                if (isset($url_parts['query']) && !empty($url_parts['query'])) {
                    $new_url .= '&' . $url_parts['query'];
                }
                
                shopwell_log_to_file('[BREADCRUMB FILTER] Converting URL from: ' . $url . ' to: ' . $new_url, null, 'php');
                $crumbs[$key][1] = esc_url($new_url);
            } else {
                shopwell_log_to_file('[BREADCRUMB FILTER] Category match but conditions not met, skipping', null, 'php');
            }
        } else {
            shopwell_log_to_file('[BREADCRUMB FILTER] Slug ' . $possible_slug . ' not found in category map', null, 'php');
        }
    }
    
    shopwell_log_to_file('[BREADCRUMB FILTER] Returning ' . count($crumbs) . ' crumbs', null, 'php');
    return $crumbs;
}
add_filter('woocommerce_breadcrumb_items', 'shopwell_convert_category_breadcrumb_links', 5, 2);

/**
 * Also modify breadcrumb HTML output directly using output buffer
 * This catches cases where the filter might not work
 */
function shopwell_start_breadcrumb_category_buffer() {
    if (!function_exists('is_woocommerce') || !is_woocommerce()) {
        return;
    }
    
    ob_start('shopwell_modify_breadcrumb_category_output');
}
add_action('woocommerce_before_main_content', 'shopwell_start_breadcrumb_category_buffer', 15);

function shopwell_stop_breadcrumb_category_buffer() {
    if (!function_exists('is_woocommerce') || !is_woocommerce()) {
        return;
    }
    
    if (ob_get_level() > 0) {
        ob_end_flush();
    }
}
add_action('woocommerce_before_main_content', 'shopwell_stop_breadcrumb_category_buffer', 25);


/**
 * Add inline JavaScript to intercept breadcrumb category links
 */
function shopwell_add_breadcrumb_category_js() {
    if (!function_exists('is_woocommerce') || !is_woocommerce()) {
        return;
    }
    
    $shop_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : '';
    if (empty($shop_url)) {
        return;
    }
    
    ?>
    <script type="text/javascript">
    (function($) {
        shopwellLog('[BREADCRUMB JS] Script loaded');
        
        var shopUrl = '<?php echo esc_js($shop_url); ?>';
        shopwellLog('[BREADCRUMB JS] Shop URL: ' + shopUrl);
        
        function convertCategoryUrl(href) {
            if (!href) return null;
            
            // Check if this is a category URL
            var isCategoryUrl = (
                href.indexOf('/categorie-produs/') !== -1 || 
                href.indexOf('/product-category/') !== -1 ||
                href.indexOf('/category/') !== -1
            );
            
            if (!isCategoryUrl) return null;
            
            // Extract category slug from URL
            var match = href.match(/\/(?:categorie-produs|product-category|category)\/([^\/\?]+)/);
            
            if (match && match[1]) {
                var categorySlug = match[1];
                var newUrl = shopUrl + '?product_cat=' + categorySlug;
                
                // Preserve any existing query parameters
                var urlParts = href.split('?');
                if (urlParts.length > 1) {
                    newUrl += '&' + urlParts[1];
                }
                
                return newUrl;
            }
            
            return null;
        }
        
        function processBreadcrumbLinks() {
            shopwellLog('[BREADCRUMB JS] Processing breadcrumb links');
            
            // Find all breadcrumb containers
            var breadcrumbContainers = $('.woocommerce-breadcrumb, .site-breadcrumb');
            shopwellLog('[BREADCRUMB JS] Found ' + breadcrumbContainers.length + ' breadcrumb containers');
            
            // Find ALL links inside breadcrumbs (including nested ones)
            var allLinks = breadcrumbContainers.find('a');
            shopwellLog('[BREADCRUMB JS] Found ' + allLinks.length + ' links in breadcrumbs');
            
            allLinks.each(function(index) {
                var $link = $(this);
                var href = $link.attr('href');
                
                if (!href) return;
                
                shopwellLog('[BREADCRUMB JS] Link #' + index + ': ' + href);
                
                var newUrl = convertCategoryUrl(href);
                if (newUrl) {
                    shopwellLog('[BREADCRUMB JS] Converting: ' + href + ' -> ' + newUrl);
                    $link.attr('href', newUrl);
                }
            });
        }
        
        // Use event delegation on document to catch ALL clicks, even on dynamically added links
        $(document).on('click', 'a', function(e) {
            var $link = $(this);
            var href = $link.attr('href');
            
            if (!href) return;
            
            // Check if this is a category URL
            var isCategoryUrl = (
                href.indexOf('/categorie-produs/') !== -1 || 
                href.indexOf('/product-category/') !== -1 ||
                href.indexOf('/category/') !== -1
            );
            
            if (!isCategoryUrl) return;
            
            // Check if link is in breadcrumbs, meta-cat, or product meta
            var isInBreadcrumb = $link.closest('.woocommerce-breadcrumb, .site-breadcrumb').length > 0;
            var isInMetaCat = $link.closest('.meta-cat, .product_meta, .posted_in').length > 0;
            
            if (isInBreadcrumb || isInMetaCat) {
                shopwellLog('[BREADCRUMB JS] Clicked on category link: ' + href);
                shopwellLog('[BREADCRUMB JS] In breadcrumb: ' + isInBreadcrumb + ', In meta-cat: ' + isInMetaCat);
                
                var newUrl = convertCategoryUrl(href);
                if (newUrl) {
                    shopwellLog('[BREADCRUMB JS] Intercepting and converting to: ' + newUrl);
                    e.preventDefault();
                    e.stopPropagation();
                    window.location.href = newUrl;
                    return false;
                }
            }
        });
        
        // Process links on document ready
        $(document).ready(function() {
            shopwellLog('[BREADCRUMB JS] Document ready');
            processBreadcrumbLinks();
        });
        
        // Also process after a delay in case breadcrumbs load dynamically
        setTimeout(function() {
            shopwellLog('[BREADCRUMB JS] Delayed processing');
            processBreadcrumbLinks();
        }, 500);
        
        // Process on any DOM changes (MutationObserver)
        if (typeof MutationObserver !== 'undefined') {
            var observer = new MutationObserver(function(mutations) {
                var shouldProcess = false;
                mutations.forEach(function(mutation) {
                    if (mutation.addedNodes.length > 0) {
                        for (var i = 0; i < mutation.addedNodes.length; i++) {
                            var node = mutation.addedNodes[i];
                            if (node.nodeType === 1) { // Element node
                                if ($(node).is('.woocommerce-breadcrumb, .site-breadcrumb') || 
                                    $(node).find('.woocommerce-breadcrumb, .site-breadcrumb').length > 0) {
                                    shouldProcess = true;
                                    break;
                                }
                            }
                        }
                    }
                });
                
                if (shouldProcess) {
                    shopwellLog('[BREADCRUMB JS] Breadcrumb DOM changed, reprocessing');
                    setTimeout(processBreadcrumbLinks, 100);
                }
            });
            
            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
        }
    })(jQuery);
    </script>
    <?php
}
add_action('wp_footer', 'shopwell_add_breadcrumb_category_js', 999);

/**
 * Filter wc_get_product_category_list output to use query parameter format
 */
function shopwell_filter_product_category_list($categories) {
    if (empty($categories) || !is_string($categories)) {
        return $categories;
    }
    
    // Get shop page URL
    $shop_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : '';
    if (empty($shop_url)) {
        return $categories;
    }
    
    // Replace category term links with query parameter format
    $categories = preg_replace_callback(
        '/href=["\']([^"\']*\/categorie-produs\/([^\/\?]+)[^"\']*)["\']/i',
        function($matches) use ($shop_url) {
            $category_slug = $matches[2];
            return 'href="' . esc_url($shop_url . '?product_cat=' . $category_slug) . '"';
        },
        $categories
    );
    
    // Also handle product-category and category patterns
    $categories = preg_replace_callback(
        '/href=["\']([^"\']*\/(?:product-category|category)\/([^\/\?]+)[^"\']*)["\']/i',
        function($matches) use ($shop_url) {
            $category_slug = $matches[2];
            return 'href="' . esc_url($shop_url . '?product_cat=' . $category_slug) . '"';
        },
        $categories
    );
    
    return $categories;
}
add_filter('woocommerce_product_category_list', 'shopwell_filter_product_category_list', 10, 1);

function shopwell_modify_breadcrumb_category_output($output) {
    shopwell_log_to_file('[OUTPUT BUFFER] shopwell_modify_breadcrumb_category_output called', null, 'php');
    shopwell_log_to_file('[OUTPUT BUFFER] Output length: ' . strlen($output), null, 'php');
    
    if (!function_exists('is_woocommerce') || !is_woocommerce()) {
        shopwell_log_to_file('[OUTPUT BUFFER] Not WooCommerce page, skipping', null, 'php');
        return $output;
    }
    
    // Get shop page URL
    $shop_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : '';
    if (empty($shop_url)) {
        shopwell_log_to_file('[OUTPUT BUFFER] Shop URL is empty', null, 'php');
        return $output;
    }
    shopwell_log_to_file('[OUTPUT BUFFER] Shop URL: ' . $shop_url, null, 'php');
    
    // Get all product categories
    $all_categories = get_terms(array(
        'taxonomy' => 'product_cat',
        'hide_empty' => false
    ));
    
    if (is_wp_error($all_categories) || empty($all_categories)) {
        shopwell_log_to_file('[OUTPUT BUFFER] No categories found', null, 'php');
        return $output;
    }
    shopwell_log_to_file('[OUTPUT BUFFER] Found ' . count($all_categories) . ' categories', null, 'php');
    
    // Check if output contains category URLs
    $has_category_url = (
        strpos($output, '/categorie-produs/') !== false ||
        strpos($output, '/product-category/') !== false ||
        strpos($output, '/category/') !== false
    );
    shopwell_log_to_file('[OUTPUT BUFFER] Contains category URLs: ' . ($has_category_url ? 'yes' : 'no'), null, 'php');
    
    // Method 1: Replace by exact term links
    foreach ($all_categories as $category) {
        // Get the term link to find the URL pattern
        $term_link = get_term_link($category, 'product_cat');
        if (is_wp_error($term_link)) {
            continue;
        }
        
        shopwell_log_to_file('[OUTPUT BUFFER] Checking term link: ' . $term_link, null, 'php');
        
        // Escape special regex characters in URL
        $term_link_escaped = preg_quote($term_link, '/');
        
        // Replace category URLs with query parameter format
        $pattern = '/href=["\']' . $term_link_escaped . '["\']/i';
        $replacement = 'href="' . esc_url($shop_url . '?product_cat=' . $category->slug) . '"';
        $before = $output;
        $output = preg_replace($pattern, $replacement, $output);
        if ($before !== $output) {
            shopwell_log_to_file('[OUTPUT BUFFER] Replaced term link: ' . $term_link, null, 'php');
        }
        
        // Also try without trailing slash
        $term_link_no_slash = rtrim($term_link, '/');
        $term_link_escaped_no_slash = preg_quote($term_link_no_slash, '/');
        $pattern_no_slash = '/href=["\']' . $term_link_escaped_no_slash . '["\']/i';
        $before = $output;
        $output = preg_replace($pattern_no_slash, $replacement, $output);
        if ($before !== $output) {
            shopwell_log_to_file('[OUTPUT BUFFER] Replaced term link (no slash): ' . $term_link_no_slash, null, 'php');
        }
    }
    
    // Method 2: Generic pattern matching for /categorie-produs/ or /product-category/
    // This catches any URL structure that matches the pattern
    foreach ($all_categories as $category) {
        $category_slug = $category->slug;
        $category_slug_escaped = preg_quote($category_slug, '/');
        
        // Match URLs like /categorie-produs/slug/ or /product-category/slug/
        $patterns = array(
            '/href=["\']([^"\']*\/categorie-produs\/' . $category_slug_escaped . '\/?[^"\']*)["\']/i',
            '/href=["\']([^"\']*\/product-category\/' . $category_slug_escaped . '\/?[^"\']*)["\']/i',
            '/href=["\']([^"\']*\/category\/' . $category_slug_escaped . '\/?[^"\']*)["\']/i',
        );
        
        $replacement = 'href="' . esc_url($shop_url . '?product_cat=' . $category->slug) . '"';
        
        foreach ($patterns as $pattern_index => $pattern) {
            $before = $output;
            $output = preg_replace($pattern, $replacement, $output);
            if ($before !== $output) {
                shopwell_log_to_file('[OUTPUT BUFFER] Replaced pattern #' . $pattern_index . ' for category: ' . $category->slug, null, 'php');
            }
        }
    }
    
    shopwell_log_to_file('[OUTPUT BUFFER] Final output length: ' . strlen($output), null, 'php');
    return $output;
}

/**
 * Alternative method: Modify breadcrumb output using output buffer
 */
function shopwell_start_breadcrumb_buffer() {
    // Only on shop pages
    if (!function_exists('is_shop') || !(is_shop() || is_product_category() || is_product_tag() || is_product_taxonomy())) {
        return;
    }
    
    // Check if a model filter is active
    if (isset($_GET['filter_pa_model']) && !empty($_GET['filter_pa_model'])) {
        ob_start('shopwell_modify_breadcrumb_output');
    }
}
add_action('woocommerce_before_main_content', 'shopwell_start_breadcrumb_buffer', 5);

function shopwell_stop_breadcrumb_buffer() {
    // Only on shop pages
    if (!function_exists('is_shop') || !(is_shop() || is_product_category() || is_product_tag() || is_product_taxonomy())) {
        return;
    }
    
    // Check if a model filter is active
    if (isset($_GET['filter_pa_model']) && !empty($_GET['filter_pa_model'])) {
        if (ob_get_level() > 0) {
            ob_end_flush();
        }
    }
}
add_action('woocommerce_before_main_content', 'shopwell_stop_breadcrumb_buffer', 25);

function shopwell_modify_breadcrumb_output($output) {
    // Only on shop pages
    if (!function_exists('is_shop') || !(is_shop() || is_product_category() || is_product_tag() || is_product_taxonomy())) {
        return $output;
    }
    
    // Check if a model filter is active
    if (isset($_GET['filter_pa_model']) && !empty($_GET['filter_pa_model'])) {
        $model_slug = sanitize_text_field($_GET['filter_pa_model']);
        
        // Get the model term
        $model_term = get_term_by('slug', $model_slug, 'pa_model');
        if (!$model_term || is_wp_error($model_term)) {
            // Try pa_marca if pa_model doesn't work
            $model_term = get_term_by('slug', $model_slug, 'pa_marca');
        }
        
        if ($model_term && !is_wp_error($model_term)) {
            // Build current URL without model filter for the breadcrumb link
            $current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
            $parsed_url = parse_url($current_url);
            $query_params = array();
            if (isset($parsed_url['query'])) {
                parse_str($parsed_url['query'], $query_params);
            }
            
            // Remove model filter from query params
            unset($query_params['filter_pa_model']);
            
            // Rebuild URL
            $model_url = $parsed_url['scheme'] . '://' . $parsed_url['host'] . $parsed_url['path'];
            if (!empty($query_params)) {
                $model_url .= '?' . http_build_query($query_params);
            }
            
            // Format model name (capitalize first letter)
            $model_name = ucfirst($model_term->name);
            
            // Get separator
            $separator = \Shopwell\Icon::get_svg('right');
            if (empty($separator)) {
                $separator = '>';
            }
            
            // Look for breadcrumb structure and insert model before last item
            // Pattern: ...separator<span>Last Item</span></span></nav>
            // We need to find the last separator and insert before the last breadcrumb item
            $last_separator_pos = strrpos($output, $separator);
            if ($last_separator_pos !== false) {
                // Find position after separator
                $insert_pos = $last_separator_pos + strlen($separator);
                
                // Build model breadcrumb HTML matching WooCommerce structure
                $model_breadcrumb = '<span itemscope itemtype="http://schema.org/ListItem"><a href="' . esc_url($model_url) . '"><span>' . esc_html($model_name) . '</span></a></span>' . $separator;
                
                // Insert the model breadcrumb
                $output = substr_replace($output, $model_breadcrumb, $insert_pos, 0);
            } else {
                // Fallback: try to find closing nav tag
                $nav_close_pos = strrpos($output, '</nav>');
                if ($nav_close_pos !== false) {
                    $model_breadcrumb = $separator . '<span itemscope itemtype="http://schema.org/ListItem"><a href="' . esc_url($model_url) . '"><span>' . esc_html($model_name) . '</span></a></span>';
                    $output = substr_replace($output, $model_breadcrumb, $nav_close_pos, 0);
                }
            }
        }
    }
    
    return $output;
}

/**
 * Remove coupon section from checkout page
 * This customization persists through theme updates
 */
function shopwell_remove_checkout_coupon() {
    // Check if WooCommerce is active
    if (!class_exists('WooCommerce')) {
        return;
    }
    
    // Get the Checkout instance if it exists
    if (class_exists('\Shopwell\WooCommerce\Checkout')) {
        $checkout_instance = \Shopwell\WooCommerce\Checkout::instance();
        
        // Remove the coupon form action added by the Checkout class
        remove_action('woocommerce_before_checkout_form', array($checkout_instance, 'coupon_form'), 10);
        
        // Also remove the coupon message filter
        remove_filter('woocommerce_checkout_coupon_message', array($checkout_instance, 'coupon_form_name'), 10);
    }
}
// Hook into 'wp' with priority 20 to ensure Checkout class is instantiated first (it's instantiated at priority 10)
add_action('wp', 'shopwell_remove_checkout_coupon', 20);

/**
 * Wrap catalog-toolbar--top and catalog-toolbar__filters-actived in a flex container
 * This customization persists through theme updates
 */
function shopwell_wrap_catalog_toolbar_elements() {
    // Only on shop pages
    if (!function_exists('is_shop') || !(is_shop() || is_product_category() || is_product_tag() || is_product_taxonomy())) {
        return;
    }
    
    // Open wrapper div before catalog toolbar (priority 39, before catalog_toolbar at 40)
    add_action('woocommerce_before_shop_loop', function() {
        echo '<div class="catalog-toolbar-wrapper">';
    }, 39);
    
    // Close wrapper div after filters_actived (priority 11, after filters_actived at 10)
    add_action('shopwell_woocommerce_after_products_toolbar_top', function() {
        echo '</div>';
    }, 11);
}
add_action('wp', 'shopwell_wrap_catalog_toolbar_elements', 25);

/**
 * AJAX handler to render WooCommerce products shortcode
 * Used to display recommended products from quiz
 */
function shopwell_render_products_shortcode() {
    check_ajax_referer('shopwell_products_shortcode', 'nonce');
    
    if (!isset($_POST['product_ids']) || empty($_POST['product_ids'])) {
        wp_send_json_error(array('message' => 'Product IDs are required'));
        return;
    }
    
    $product_ids = sanitize_text_field($_POST['product_ids']);
    $columns = isset($_POST['columns']) ? intval($_POST['columns']) : 4;
    
    // Use WooCommerce shortcode to render products
    $shortcode = '[products ids="' . esc_attr($product_ids) . '" columns="' . esc_attr($columns) . '" orderby="post__in"]';
    $output = do_shortcode($shortcode);
    
    wp_send_json_success(array('html' => $output));
}
add_action('wp_ajax_shopwell_render_products_shortcode', 'shopwell_render_products_shortcode');
add_action('wp_ajax_nopriv_shopwell_render_products_shortcode', 'shopwell_render_products_shortcode');

/**
 * PERFORMANCE FIX: Add lazy loading to product images in shop loop
 * Adds loading="lazy" attribute to product thumbnails for better performance
 */
function shopwell_add_lazy_loading_to_product_images( $attr, $attachment, $size ) {
	// Only add lazy loading to product thumbnails in shop/archive pages
	if ( is_shop() || is_product_category() || is_product_tag() || is_product_taxonomy() || wc_get_loop_prop( 'is_shortcode' ) ) {
		// Check if this is a product thumbnail size
		if ( in_array( $size, array( 'woocommerce_thumbnail', 'shop_catalog', 'shop_single', 'woocommerce_single' ), true ) ) {
			// Skip lazy loading for first 4 images (above the fold)
			global $woocommerce_loop;
			$current_index = isset( $woocommerce_loop['loop'] ) ? $woocommerce_loop['loop'] : 0;
			
			if ( $current_index > 3 ) {
				$attr['loading'] = 'lazy';
				$attr['decoding'] = 'async';
			}
		}
	}
	
	return $attr;
}
add_filter( 'wp_get_attachment_image_attributes', 'shopwell_add_lazy_loading_to_product_images', 10, 3 );

/**
 * PERFORMANCE FIX: Add lazy loading to WooCommerce product loop images
 * This handles images generated by woocommerce_template_loop_product_thumbnail()
 */
function shopwell_add_lazy_loading_to_wc_product_images( $html, $product, $size ) {
	// Only apply to shop/archive pages
	if ( is_shop() || is_product_category() || is_product_tag() || is_product_taxonomy() || wc_get_loop_prop( 'is_shortcode' ) ) {
		// Check if image doesn't already have loading attribute
		if ( strpos( $html, 'loading=' ) === false ) {
			global $woocommerce_loop;
			$current_index = isset( $woocommerce_loop['loop'] ) ? $woocommerce_loop['loop'] : 0;
			
			// Skip lazy loading for first 4 images (above the fold)
			if ( $current_index > 3 ) {
				// Add loading="lazy" to img tag
				$html = str_replace( '<img ', '<img loading="lazy" decoding="async" ', $html );
			}
		}
	}
	
	return $html;
}
add_filter( 'woocommerce_product_get_image', 'shopwell_add_lazy_loading_to_wc_product_images', 10, 3 );

/**
 * PERFORMANCE FIX: Defer payment gateway scripts on single product pages
 * Payment scripts (Stripe, PayPal, Google Pay) don't need to load immediately
 * They can be deferred until user interacts with checkout
 */
function shopwell_defer_payment_gateway_scripts( $tag, $handle, $src ) {
	// Only on single product pages
	if ( ! is_product() ) {
		return $tag;
	}
	
	// List of payment gateway script handles to defer
	$payment_scripts = array(
		'stripe',
		'payment-request-button',
		'google-pay',
		'apple-pay',
		'woocommerce-stripe',
		'wc-stripe',
	);
	
	// Check if this is a payment gateway script
	foreach ( $payment_scripts as $payment_script ) {
		if ( strpos( $handle, $payment_script ) !== false || strpos( $src, $payment_script ) !== false ) {
			// Add defer attribute if not already present
			if ( strpos( $tag, ' defer' ) === false && strpos( $tag, ' async' ) === false ) {
				$tag = str_replace( ' src', ' defer src', $tag );
			}
			return $tag;
		}
	}
	
	// Also defer scripts from payment gateway domains
	$payment_domains = array(
		'stripe.com',
		'paypal.com',
		'google.com/pay',
		'js.stripe.com',
	);
	
	foreach ( $payment_domains as $domain ) {
		if ( strpos( $src, $domain ) !== false ) {
			if ( strpos( $tag, ' defer' ) === false && strpos( $tag, ' async' ) === false ) {
				$tag = str_replace( ' src', ' defer src', $tag );
			}
			return $tag;
		}
	}
	
	return $tag;
}
add_filter( 'script_loader_tag', 'shopwell_defer_payment_gateway_scripts', 10, 3 );

/**
 * PERFORMANCE FIX: Defer hCaptcha scripts on single product pages
 * hCaptcha scripts are heavy and can be loaded only when form is visible
 */
function shopwell_defer_hcaptcha_scripts( $tag, $handle, $src ) {
	// Only on single product pages
	if ( ! is_product() ) {
		return $tag;
	}
	
	// Check if this is hCaptcha script
	if ( strpos( $handle, 'hcaptcha' ) !== false || 
		 strpos( $src, 'hcaptcha.com' ) !== false ||
		 strpos( $src, 'h-captcha' ) !== false ) {
		// Add defer attribute if not already present
		if ( strpos( $tag, ' defer' ) === false && strpos( $tag, ' async' ) === false ) {
			$tag = str_replace( ' src', ' defer src', $tag );
		}
		// Add data attribute for potential lazy loading
		if ( strpos( $tag, 'data-lazy' ) === false ) {
			$tag = str_replace( '<script ', '<script data-lazy="true" ', $tag );
		}
		return $tag;
	}
	
	return $tag;
}
add_filter( 'script_loader_tag', 'shopwell_defer_hcaptcha_scripts', 10, 3 );

/**
 * PERFORMANCE FIX: Completely disable Google Pay and Pay with Link on single product pages
 * Remove payment request buttons (Google Pay, Apple Pay, Link) from single product pages
 */
function shopwell_disable_payment_request_buttons_single_product() {
	// Only on single product pages
	if ( ! is_product() ) {
		return;
	}
	
	// Remove Stripe Payment Request buttons completely
	// This removes Google Pay, Apple Pay, and Pay with Link buttons
	remove_action( 'woocommerce_single_product_summary', array( 'WC_Stripe_Payment_Request_Button_Handler', 'display_payment_request_button_html' ), 1 );
	remove_action( 'woocommerce_single_product_summary', array( 'WC_Stripe_Payment_Request_Button_Handler', 'display_payment_request_button', 'display_payment_request_button_html' ), 1 );
	
	// Remove via filter - prevent payment request buttons from rendering
	add_filter( 'wc_stripe_show_payment_request_on_product_page', '__return_false', 999 );
	add_filter( 'woocommerce_stripe_show_payment_request_on_product_page', '__return_false', 999 );
	
	// Disable payment request button scripts completely
	add_filter( 'woocommerce_stripe_payment_request_button_locale', '__return_false', 999 );
	add_filter( 'wc_stripe_payment_request_button_locale', '__return_false', 999 );
	
	// Prevent script enqueuing for payment request buttons
	add_filter( 'script_loader_tag', function( $tag, $handle, $src ) {
		// Block payment request button scripts
		if ( strpos( $handle, 'payment-request-button' ) !== false ||
			 strpos( $src, 'payment-request-button' ) !== false ||
			 ( strpos( $src, 'stripe.com/v3' ) !== false && strpos( $src, 'payment-request' ) !== false ) ||
			 strpos( $src, 'js.stripe.com/v3' ) !== false ) {
			return ''; // Return empty to prevent script from loading
		}
		return $tag;
	}, 999, 3 );
	
	// Also prevent script enqueuing via wp_enqueue_script
	add_filter( 'woocommerce_stripe_payment_request_enqueue_scripts', '__return_false', 999 );
	
	// Hide payment request buttons with CSS if they still appear
	add_action( 'wp_head', function() {
		?>
		<style>
		/* Hide Google Pay, Apple Pay, and Pay with Link buttons on single product */
		.single-product .wc-stripe-payment-request-button,
		.single .wc-stripe-payment-request-button-separator,
		.single .payment_request_button,
		.single .stripe-payment-request-button,
		.single-product .payment-request-button,
		.single-product [class*="payment-request"],
		.single-product [class*="google-pay"],
		.single-product [class*="apple-pay"],
		.single-product [id*="payment-request"],
		.single-product button[class*="Stripe"],
		.single-product .stripe-button-el {
			display: none !important;
			visibility: hidden !important;
			opacity: 0 !important;
			height: 0 !important;
			width: 0 !important;
			overflow: hidden !important;
		}
		</style>
		<?php
	}, 999 );
}
add_action( 'wp', 'shopwell_disable_payment_request_buttons_single_product' );

/**
 * PERFORMANCE FIX: Reduce unnecessary AJAX requests on single product
 * Some plugins make multiple requests that can be optimized
 */
function shopwell_reduce_ajax_requests_single_product() {
	// Only on single product pages
	if ( ! is_product() ) {
		return;
	}
	
	// Note: AJAX debouncing is handled at plugin level
	// This function is a placeholder for future optimizations if needed
}
add_action( 'wp', 'shopwell_reduce_ajax_requests_single_product' );

/**
 * Disable Compare Feature Completely
 * 
 * Acest cod dezactiveazÄ complet funcČ›ionalitatea de compare Ă®n toatÄ tema
 * Va rÄmĂ˘ne la update-uri deoarece este Ă®n functions.php
 */
function shopwell_disable_compare_feature() {
	// Previne iniČ›ializarea clasei Compare prin eliminarea condiČ›iei
	// Hook Ă®nainte de add_actions pentru a preveni iniČ›ializarea
	add_action( 'wp', function() {
		// EliminÄ iniČ›ializarea clasei Compare din WooCommerce
		if ( class_exists( '\Shopwell\WooCommerce\Compare' ) ) {
			// EliminÄ toate acČ›iunile Č™i filtrele din clasa Compare
			remove_all_actions( 'wcboost_products_compare_button_template_args' );
			remove_all_actions( 'wcboost_products_compare_add_to_compare_fragments' );
			remove_all_actions( 'wcboost_products_compare_single_add_to_compare_link' );
			remove_all_actions( 'wcboost_products_compare_loop_add_to_compare_link' );
			remove_all_filters( 'wcboost_products_compare_button_add_text' );
			remove_all_filters( 'wcboost_products_compare_button_remove_text' );
			remove_all_filters( 'wcboost_products_compare_button_view_text' );
			remove_all_filters( 'wcboost_products_compare_fields' );
			remove_all_actions( 'wcboost_products_compare_custom_field' );
		}
	}, 1 );
	
	// DezactiveazÄ clasa Compare din tema
	add_filter( 'shopwell_change_compare_button_settings', '__return_false', 999 );
	
	// EliminÄ compare din header items
	add_filter( 'shopwell_get_header_items', function( $items ) {
		if ( isset( $items['compare'] ) ) {
			unset( $items['compare'] );
		}
		return $items;
	}, 999 );
	
	// EliminÄ compare din mobile navigation bar items
	add_filter( 'shopwell_get_mobile_navigation_bar_items', function( $items ) {
		if ( isset( $items['compare'] ) ) {
			unset( $items['compare'] );
		}
		return $items;
	}, 999 );
	
	// DezactiveazÄ butonul compare pe paginile single product
	add_filter( 'shopwell_enable_compare_button', '__return_false', 999 );
	
	// DezactiveazÄ butonul compare pe product cards
	add_filter( 'shopwell_product_card_compare', '__return_false', 999 );
	
	// EliminÄ compare din hamburger menu
	add_filter( 'shopwell_get_hamburger_items', function( $items ) {
		if ( isset( $items['compare'] ) ) {
			unset( $items['compare'] );
		}
		return $items;
	}, 999 );
	
	// EliminÄ compare din account panel
	add_filter( 'shopwell_get_account_panel_items', function( $items ) {
		if ( isset( $items['compare'] ) ) {
			unset( $items['compare'] );
		}
		return $items;
	}, 999 );
	
	// EliminÄ wishlist-ul din galerie (pÄstrÄm doar cel din summary)
	// Astfel wishlist-ul va apÄrea o singurÄ datÄ pe paginÄ
	remove_action( 'woocommerce_before_single_product_summary', array( '\Shopwell\WooCommerce\Single_Product', 'product_featured_buttons' ), 20 );
	
	// Ascunde template-urile compare prin CSS (pentru siguranČ›Ä)
	add_action( 'wp_head', function() {
		?>
		<style>
		/* Ascunde butoanele Č™i linkurile de compare */
		.header-compare,
		.shopwell-mobile-navigation-bar__icon.compare-icon,
		.product-featured-icons .wcboost-products-compare-button,
		.shopwell-button--compare,
		[class*="compare-button"],
		[class*="compare-icon"] {
			display: none !important;
			visibility: hidden !important;
		}
		</style>
		<?php
	}, 999 );
}
add_action( 'init', 'shopwell_disable_compare_feature', 1 );

// DezactiveazÄ funcČ›ionalitatea JavaScript pentru compare
function shopwell_disable_compare_js() {
	?>
	<script>
	// DezactiveazÄ funcČ›ionalitatea compare
	if (typeof shopwell !== 'undefined') {
		if (typeof shopwell.addCompare === 'function') {
			shopwell.addCompare = function() {};
		}
		if (typeof shopwell.addedToCompareNotice === 'function') {
			shopwell.addedToCompareNotice = function() {};
		}
	}
	// EliminÄ event listeners pentru compare
	jQuery(document).ready(function($) {
		$('body').off('click', 'a.compare, .compare-button, [class*="compare"]');
		$('body').off('added_to_compare');
	});
	</script>
	<?php
}
add_action( 'wp_footer', 'shopwell_disable_compare_js', 999 );

/**
 * Change "Buy Now" button text to "Cumpără Acum"
 */
function shopwell_change_buy_now_text() {
	?>
	<script>
	jQuery(document).ready(function($) {
		// Function to replace Buy Now text
		function replaceBuyNowText() {
			// Find all elements containing "Buy Now" text
			$('.shopwell-buy-now-button, button[class*="buy-now"], a[class*="buy-now"]').each(function() {
				var $button = $(this);
				// Check if button or any child element contains "Buy Now"
				if ($button.text().indexOf('Buy Now') !== -1) {
					// Replace text in button itself
					$button.html($button.html().replace(/Buy Now/gi, 'Cumpără Acum'));
					// Also replace in any child elements
					$button.find('*').each(function() {
						if ($(this).text().indexOf('Buy Now') !== -1) {
							$(this).text($(this).text().replace(/Buy Now/gi, 'Cumpără Acum'));
						}
					});
				}
			});
			
			// Also check for buttons by text content
			$('button, a.button').each(function() {
				var $btn = $(this);
				var text = $btn.text().trim();
				if (text === 'Buy Now' || text.indexOf('Buy Now') !== -1) {
					$btn.html($btn.html().replace(/Buy Now/gi, 'Cumpără Acum'));
				}
			});
		}
		
		// Run immediately
		replaceBuyNowText();
		
		// Run after a short delay to catch dynamically loaded content
		setTimeout(replaceBuyNowText, 500);
		setTimeout(replaceBuyNowText, 1000);
		setTimeout(replaceBuyNowText, 2000);
		
		// Also run when AJAX content is loaded (for variations, etc.)
		$(document.body).on('updated_wc_div wc_fragments_refreshed', function() {
			setTimeout(replaceBuyNowText, 100);
		});
		
		// Use MutationObserver to catch dynamically added buttons
		if (typeof MutationObserver !== 'undefined') {
			var observer = new MutationObserver(function(mutations) {
				setTimeout(replaceBuyNowText, 100);
			});
			
			var $product = $('.single-product, .product');
			if ($product.length) {
				$product.each(function() {
					observer.observe(this, {
						childList: true,
						subtree: true
					});
				});
			}
		}
	});
	</script>
	<?php
}
add_action( 'wp_footer', 'shopwell_change_buy_now_text', 20 );

/**
 * Remove halooToastContainer
 */
function shopwell_remove_haloo_toast_container() {
	?>
	<script>
	jQuery(document).ready(function($) {
		// Function to remove halooToastContainer
		function removeHalooToastContainer() {
			// Remove by ID
			$('#halooToastContainer').remove();
			// Remove by class
			$('.halooToastContainer').remove();
			// Also check for any element with halooToast in the name
			$('[id*="halooToast"], [class*="halooToast"]').remove();
		}
		
		// Run immediately
		removeHalooToastContainer();
		
		// Run after a short delay to catch dynamically created elements
		setTimeout(removeHalooToastContainer, 100);
		setTimeout(removeHalooToastContainer, 500);
		setTimeout(removeHalooToastContainer, 1000);
		
		// Use MutationObserver to catch dynamically added containers
		if (typeof MutationObserver !== 'undefined') {
			var observer = new MutationObserver(function(mutations) {
				removeHalooToastContainer();
			});
			
			observer.observe(document.body, {
				childList: true,
				subtree: true
			});
		}
		
		// Also prevent creation by intercepting appendChild if possible
		var originalAppendChild = Element.prototype.appendChild;
		Element.prototype.appendChild = function(child) {
			if (child) {
				// Check ID first (always a string if exists)
				if (child.id === 'halooToastContainer' || 
					(child.id && typeof child.id === 'string' && child.id.indexOf('halooToast') !== -1)) {
					return child; // Don't append, just return
				}
				
				// Check className - handle both string and DOMTokenList
				if (child.className) {
					var classNameStr = '';
					if (typeof child.className === 'string') {
						classNameStr = child.className;
					} else if (child.className.baseVal !== undefined) {
						// SVG elements use className.baseVal
						classNameStr = child.className.baseVal || '';
					} else if (typeof child.className.toString === 'function') {
						classNameStr = child.className.toString();
					} else if (child.classList && typeof child.classList.toString === 'function') {
						// Fallback to classList if available
						classNameStr = child.classList.toString();
					}
					
					if (classNameStr && classNameStr.indexOf('halooToast') !== -1) {
						return child; // Don't append, just return
					}
				}
			}
			return originalAppendChild.call(this, child);
		};
	});
	</script>
	<style>
		/* Hide halooToastContainer via CSS as backup */
		#halooToastContainer,
		.halooToastContainer,
		[id*="halooToast"],
		[class*="halooToast"] {
			display: none !important;
			visibility: hidden !important;
			opacity: 0 !important;
			height: 0 !important;
			width: 0 !important;
			overflow: hidden !important;
			position: absolute !important;
			left: -9999px !important;
		}
	</style>
	<?php
}
add_action( 'wp_footer', 'shopwell_remove_haloo_toast_container', 10 );

/**
 * Backend processing for hierarchical variation selection
 * ALL processing is done here - frontend only handles user interactions
 */
function shopwell_process_variations_backend() {
    // Only on single product pages
    if (!is_product()) {
        return;
    }
    
    global $product;
    
    if (!$product || !$product->is_type('variable')) {
        return;
    }
    
    // Attribute hierarchy
    $attribute_hierarchy = array(
        'attribute_pa_culoare' => 1,
        'attribute_culoare' => 1,
        'attribute_pa_stare' => 2,
        'attribute_stare' => 2,
        'attribute_pa_memorie' => 3,
        'attribute_memorie' => 3,
        'attribute_pa_stocare' => 3,
        'attribute_stocare' => 3,
    );
    
    // Map simplified names to attribute names
    $simplified_to_attribute = array(
        'culoare' => array('attribute_pa_culoare', 'attribute_culoare'),
        'stare' => array('attribute_pa_stare', 'attribute_stare'),
        'memorie' => array('attribute_pa_memorie', 'attribute_memorie', 'attribute_pa_stocare', 'attribute_stocare'),
    );
    
    // Get URL parameters
    $url_params = array();
    if (isset($_GET['culoare']) && !empty($_GET['culoare'])) {
        $url_params['culoare'] = sanitize_text_field($_GET['culoare']);
    }
    if (isset($_GET['stare']) && !empty($_GET['stare'])) {
        $url_params['stare'] = sanitize_text_field($_GET['stare']);
    }
    if (isset($_GET['memorie']) && !empty($_GET['memorie'])) {
        $url_params['memorie'] = sanitize_text_field($_GET['memorie']);
    }
    
    // Get available variations
    $available_variations = $product->get_available_variations();
    $attributes = $product->get_variation_attributes();
    
    // Ensure all variations have price_html - if not, calculate it
    foreach ($available_variations as &$variation) {
        if (empty($variation['price_html']) && isset($variation['variation_id'])) {
            $variation_obj = wc_get_product($variation['variation_id']);
            if ($variation_obj && $variation_obj->is_purchasable()) {
                $variation['price_html'] = $variation_obj->get_price_html();
            }
        }
    }
    unset($variation); // Break reference
    
    // Check if there's only one valid variation - if so, auto-select all its attributes
    $in_stock_variations = array();
    foreach ($available_variations as $variation) {
        if ($variation['is_in_stock']) {
            $in_stock_variations[] = $variation;
        }
    }
    
    // If there's exactly one in-stock variation, auto-select all its attributes
    $auto_select_attrs = array();
    if (count($in_stock_variations) === 1 && empty($url_params)) {
        // Only auto-select if no URL parameters are provided (user hasn't made manual selections)
        $single_variation = $in_stock_variations[0];
        if (isset($single_variation['attributes']) && is_array($single_variation['attributes'])) {
            foreach ($single_variation['attributes'] as $attr_name => $attr_value) {
                if (!empty($attr_value)) {
                    $auto_select_attrs[$attr_name] = $attr_value;
                }
            }
        }
    }
    
    // Calculate validated selections (verify availability)
    $validated_selections = array();
    foreach ($attributes as $attribute_name => $options) {
        // Convert to variation attribute format
        $variation_attr_name = 'attribute_' . $attribute_name;
        $attr_level = shopwell_get_attribute_level($variation_attr_name, $attribute_hierarchy);
        
        // Find URL value for this attribute
        $url_value = null;
        foreach ($simplified_to_attribute as $param_name => $possible_attrs) {
            if (in_array($variation_attr_name, $possible_attrs) && isset($url_params[$param_name])) {
                $url_value = $url_params[$param_name];
                break;
            }
        }
        
        // Priority 1: Use URL parameter if provided
        if ($url_value) {
            // Get higher level selections for availability check
            $higher_level_selections = array();
            foreach ($validated_selections as $sel_attr => $sel_val) {
                if (shopwell_get_attribute_level($sel_attr, $attribute_hierarchy) < $attr_level) {
                    $higher_level_selections[$sel_attr] = $sel_val;
                }
            }
            
            // Verify availability
            $is_available = shopwell_is_attribute_value_available(
                $variation_attr_name,
                $url_value,
                $higher_level_selections,
                $available_variations,
                $attr_level
            );
            
            if ($is_available) {
                $validated_selections[$variation_attr_name] = $url_value;
            }
        }
        // Priority 2: If no URL parameter and we have auto-select attributes, use them
        elseif (isset($auto_select_attrs[$variation_attr_name])) {
            $auto_value = $auto_select_attrs[$variation_attr_name];
            
            // Get higher level selections for availability check
            $higher_level_selections = array();
            foreach ($validated_selections as $sel_attr => $sel_val) {
                if (shopwell_get_attribute_level($sel_attr, $attribute_hierarchy) < $attr_level) {
                    $higher_level_selections[$sel_attr] = $sel_val;
                }
            }
            
            // Verify availability
            $is_available = shopwell_is_attribute_value_available(
                $variation_attr_name,
                $auto_value,
                $higher_level_selections,
                $available_variations,
                $attr_level
            );
            
            if ($is_available) {
                $validated_selections[$variation_attr_name] = $auto_value;
            }
        }
    }
    
    // Calculate availability and prices for ALL swatches
    $swatches_data = array();
    
    // If we have all selections (complete variation), find it once and use its price for all selected attributes
    $complete_variation_price = '';
    if (count($validated_selections) >= 3) {
        // We have all 3 levels selected, find the complete variation
        foreach ($available_variations as $variation) {
            if (!$variation['is_in_stock']) continue;
            if (empty($variation['price_html'])) continue;
            
            $matches_all = true;
            
            // Check if variation matches ALL selected attributes
            foreach ($validated_selections as $sel_attr => $sel_val) {
                $v_val = isset($variation['attributes'][$sel_attr]) ? $variation['attributes'][$sel_attr] : '';
                
                if ($v_val === '' || strcasecmp($v_val, $sel_val) !== 0) {
                    $matches_all = false;
                    break;
                }
            }
            
            // If this is the complete variation, use its price
            if ($matches_all) {
                $complete_variation_price = $variation['price_html'];
                break;
            }
        }
    }
    
    foreach ($attributes as $attribute_name => $options) {
        // Convert attribute name to match variation format (attribute_pa_xxx)
        $variation_attr_name = 'attribute_' . $attribute_name;
        $attr_level = shopwell_get_attribute_level($variation_attr_name, $attribute_hierarchy);
        
        // Get higher level selections (convert to variation format)
        $higher_level_selections = array();
        foreach ($validated_selections as $sel_attr => $sel_val) {
            $sel_level = shopwell_get_attribute_level($sel_attr, $attribute_hierarchy);
            if ($sel_level < $attr_level) {
                $higher_level_selections[$sel_attr] = $sel_val;
            }
        }
        
        $has_higher_level_selection = !empty($higher_level_selections) || $attr_level === 1;
        
        $options_data = array();
        foreach ($options as $option_value) {
            // Check availability using variation attribute format
            $is_available = false;
            if ($attr_level === 1) {
                // Level 1: always check availability
                $is_available = shopwell_is_attribute_value_available(
                    $variation_attr_name, $option_value, array(), $available_variations, $attr_level
                );
            } elseif ($has_higher_level_selection) {
                // Other levels: need higher level selection
                $is_available = shopwell_is_attribute_value_available(
                    $variation_attr_name, $option_value, $higher_level_selections, $available_variations, $attr_level
                );
            }
            
            // Get price for this option
            // Strategy: When calculating price for any attribute, use the price from the MOST SPECIFIC selection
            // If Color + State + Memory are all selected, all should show the complete variation price
            // This ensures prices "trickle down" from higher level to lower level
            $price_html = '';
            
            // Check if this option is currently selected
            $is_currently_selected = isset($validated_selections[$variation_attr_name]) && 
                                    strcasecmp($validated_selections[$variation_attr_name], $option_value) === 0;
            
            // Build test attributes: ALL currently selected attributes + this option
            $test_attrs = array();
            
            // Add ALL other selected attributes (excluding current one we're calculating for)
            foreach ($validated_selections as $sel_attr => $sel_val) {
                if ($sel_attr !== $variation_attr_name) {
                    $test_attrs[$sel_attr] = $sel_val;
                }
            }
            
            // Add the current option we're calculating price for
            $test_attrs[$variation_attr_name] = $option_value;
            
            // Priority 1: If we have complete variation AND this option is selected, use complete variation price
            // This ensures all selected attributes show the same price (the complete variation price)
            if (!empty($complete_variation_price) && $is_currently_selected) {
                $price_html = $complete_variation_price;
            }
            // Priority 2: If test_attrs matches all validated_selections (complete variation), use complete price
            elseif (!empty($complete_variation_price) && count($test_attrs) === count($validated_selections)) {
                $matches_complete = true;
                foreach ($validated_selections as $sel_attr => $sel_val) {
                    if (!isset($test_attrs[$sel_attr]) || strcasecmp($test_attrs[$sel_attr], $sel_val) !== 0) {
                        $matches_complete = false;
                        break;
                    }
                }
                if ($matches_complete) {
                    $price_html = $complete_variation_price;
                }
            }
            
            // Priority 3: Find variation matching ALL selected attributes + this option
            if (empty($price_html) && !empty($test_attrs)) {
                foreach ($available_variations as $variation) {
                    if (!$variation['is_in_stock']) continue;
                    if (empty($variation['price_html'])) continue;
                    
                    $matches_all = true;
                    
                    // Check if variation matches ALL attributes in test_attrs
                    foreach ($test_attrs as $test_attr => $test_val) {
                        $v_val = isset($variation['attributes'][$test_attr]) ? $variation['attributes'][$test_attr] : '';
                        
                        // Variation matches if:
                        // 1. Variation has this attribute AND it matches (case-insensitive), OR
                        // 2. Variation has empty value for this attribute (means "any" - matches all)
                        if ($v_val !== '' && strcasecmp($v_val, $test_val) !== 0) {
                            $matches_all = false;
                            break;
                        }
                        // If $v_val === '', it means variation doesn't care about this attribute, so it matches
                    }
                    
                    // If this variation matches ALL attributes, use it immediately
                    if ($matches_all) {
                        $price_html = $variation['price_html'];
                        break;
                    }
                }
            }
            
            // Priority 3: If still no price, try with only higher level selections + this option
            if (empty($price_html) && !empty($higher_level_selections)) {
                $test_attrs = $higher_level_selections;
                $test_attrs[$variation_attr_name] = $option_value;
                
                foreach ($available_variations as $variation) {
                    if (!$variation['is_in_stock']) continue;
                    
                    $matches = true;
                    foreach ($test_attrs as $test_attr => $test_val) {
                        $v_val = isset($variation['attributes'][$test_attr]) ? $variation['attributes'][$test_attr] : '';
                        if ($v_val !== '' && strcasecmp($v_val, $test_val) !== 0) {
                            $matches = false;
                            break;
                        }
                    }
                    
                    if ($matches && !empty($variation['price_html'])) {
                        $price_html = $variation['price_html'];
                        break;
                    }
                }
            }
            
            // Priority 4: Fallback - find ANY variation with this value (including "any" matches)
            if (empty($price_html)) {
                foreach ($available_variations as $variation) {
                    if (!$variation['is_in_stock']) continue;
                    if (empty($variation['price_html'])) continue;
                    
                    $v_val = isset($variation['attributes'][$variation_attr_name]) ? $variation['attributes'][$variation_attr_name] : '';
                    
                    // Match if: variation has this exact value OR variation has empty value (means "any")
                    if (strcasecmp($v_val, $option_value) === 0 || $v_val === '') {
                        $price_html = $variation['price_html'];
                        break;
                    }
                }
            }
            
            // Priority 5: Ultimate fallback - if option is available but still no price, use first in-stock variation's price
            // This ensures prices ALWAYS display for available options
            // IMPORTANT: This should work even if no selections are made yet
            if (empty($price_html) && $is_available) {
                foreach ($available_variations as $variation) {
                    if ($variation['is_in_stock'] && !empty($variation['price_html'])) {
                        $price_html = $variation['price_html'];
                        break;
                    }
                }
            }
            
            // Priority 6: Last resort - if still no price and option exists, use ANY in-stock variation price
            // This handles edge cases where availability check might have failed
            if (empty($price_html)) {
                foreach ($available_variations as $variation) {
                    if ($variation['is_in_stock'] && !empty($variation['price_html'])) {
                        // Check if this variation could potentially match (has this value or empty)
                        $v_val = isset($variation['attributes'][$variation_attr_name]) ? $variation['attributes'][$variation_attr_name] : '';
                        if (strcasecmp($v_val, $option_value) === 0 || $v_val === '') {
                            $price_html = $variation['price_html'];
                            break;
                        }
                    }
                }
            }
            
            // Priority 7: Absolute last resort - use ANY in-stock variation's price for available options
            // This ensures prices ALWAYS display
            if (empty($price_html) && $is_available) {
                foreach ($available_variations as $variation) {
                    if ($variation['is_in_stock'] && !empty($variation['price_html'])) {
                        $price_html = $variation['price_html'];
                        break;
                    }
                }
            }
            
            $is_selected = isset($validated_selections[$variation_attr_name]) && 
                           strcasecmp($validated_selections[$variation_attr_name], $option_value) === 0;
            
            // #region agent log
            $sample_variations = array_slice($available_variations, 0, 3);
            $sample_variations_data = array();
            foreach ($sample_variations as $idx => $v) {
                $sample_variations_data[] = array(
                    'is_in_stock' => $v['is_in_stock'],
                    'has_price_html' => !empty($v['price_html']),
                    'price_html_preview' => !empty($v['price_html']) ? substr($v['price_html'], 0, 30) : 'EMPTY',
                    'attributes' => isset($v['attributes']) ? array_keys($v['attributes']) : array(),
                    'attr_value' => isset($v['attributes'][$variation_attr_name]) ? $v['attributes'][$variation_attr_name] : 'NOT_SET'
                );
            }
            error_log(json_encode([
                'sessionId' => 'debug-session',
                'runId' => 'run1',
                'hypothesisId' => 'A',
                'location' => 'functions.php:' . __LINE__,
                'message' => 'Backend price calculation result',
                'data' => [
                    'attr_name' => $variation_attr_name,
                    'option_value' => $option_value,
                    'is_available' => $is_available,
                    'is_selected' => $is_selected,
                    'price_html' => $price_html ? substr($price_html, 0, 50) : 'EMPTY',
                    'has_price' => !empty($price_html),
                    'test_attrs_count' => count($test_attrs),
                    'validated_selections_count' => count($validated_selections),
                    'available_variations_count' => count($available_variations),
                    'sample_variations' => $sample_variations_data,
                    'complete_variation_price' => !empty($complete_variation_price) ? substr($complete_variation_price, 0, 30) : 'EMPTY'
                ],
                'timestamp' => time() * 1000
            ]));
            // #endregion
            
            $options_data[$option_value] = array(
                'available' => $is_available,
                'selected' => $is_selected,
                'price_html' => $price_html,
                'disabled' => !$is_available,
            );
        }
        
        // Use attribute_name as key (for JavaScript matching with select name)
        $swatches_data[$variation_attr_name] = array(
            'level' => $attr_level,
            'selected_value' => isset($validated_selections[$variation_attr_name]) ? $validated_selections[$variation_attr_name] : null,
            'options' => $options_data,
        );
    }
    
    // Store processed data for use in filters and templates
    $GLOBALS['shopwell_variation_data'] = array(
        'attribute_hierarchy' => $attribute_hierarchy,
        'simplified_to_attribute' => $simplified_to_attribute,
        'url_params' => $url_params,
        'available_variations' => $available_variations,
        'product' => $product,
        'validated_selections' => $validated_selections,
        'swatches_data' => $swatches_data,
    );
    
    // Output processed data as JSON for frontend (interactions only)
    add_action('wp_footer', 'shopwell_output_variation_data', 5);
}

/**
 * Get attribute level from hierarchy
 */
function shopwell_get_attribute_level($attr_name, $hierarchy) {
    return isset($hierarchy[$attr_name]) ? $hierarchy[$attr_name] : 999;
}

/**
 * Check if attribute value is available given current selections
 */
function shopwell_is_attribute_value_available($attr_name, $attr_value, $selected_attrs, $available_variations, $attr_level) {
    // For level 1 (colors), check if ANY variation exists with this color
    if ($attr_level === 1) {
        foreach ($available_variations as $variation) {
            if (!$variation['is_in_stock']) {
                continue;
            }

            $v_val = isset($variation['attributes'][$attr_name]) ? $variation['attributes'][$attr_name] : '';
            // Match if: variation has this exact value OR variation has empty value (means "any")
            if (($v_val && strcasecmp($v_val, $attr_value) === 0) || $v_val === '') {
                return true;
            }
        }
        return false;
    }

    // For other levels, check if variation exists matching all higher level selections
    $test_attrs = $selected_attrs;
    $test_attrs[$attr_name] = $attr_value;

    foreach ($available_variations as $variation) {
        if (!$variation['is_in_stock']) {
            continue;
        }

        $matches = true;
        foreach ($test_attrs as $test_attr => $test_val) {
            $v_val = isset($variation['attributes'][$test_attr]) ? $variation['attributes'][$test_attr] : '';
            
            // Variation matches if:
            // 1. Variation has this attribute AND it matches (case-insensitive), OR
            // 2. Variation has empty value for this attribute (means "any" - matches all)
            if ($v_val !== '' && strcasecmp($v_val, $test_val) !== 0) {
                $matches = false;
                break;
            }
            // If $v_val === '', it means variation doesn't care about this attribute, so it matches
        }

        if ($matches) {
            return true;
        }
    }

    return false;
}

/**
 * Modify variation dropdown HTML to pre-select values from URL
 * Also verifies availability and deselects unavailable values
 */
function shopwell_modify_variation_dropdown_html($html, $args) {
    if (!isset($GLOBALS['shopwell_variation_data'])) {
        return $html;
    }
    
    $data = $GLOBALS['shopwell_variation_data'];
    $attribute_name = isset($args['attribute']) ? $args['attribute'] : '';
    
    if (!$attribute_name) {
        return $html;
    }
    
    $attr_level = shopwell_get_attribute_level($attribute_name, $data['attribute_hierarchy']);
    
    // Find corresponding URL parameter
    $url_value = null;
    foreach ($data['simplified_to_attribute'] as $param_name => $possible_attrs) {
        if (in_array($attribute_name, $possible_attrs)) {
            if (isset($data['url_params'][$param_name])) {
                $url_value = $data['url_params'][$param_name];
                break;
            }
        }
    }
    
    // Get currently selected attributes from URL (only higher level ones)
    $selected_attrs = array();
    foreach ($data['url_params'] as $param_name => $param_value) {
        $possible_attrs = isset($data['simplified_to_attribute'][$param_name]) ? $data['simplified_to_attribute'][$param_name] : array();
        foreach ($possible_attrs as $possible_attr) {
            $possible_level = shopwell_get_attribute_level($possible_attr, $data['attribute_hierarchy']);
            if ($possible_level < $attr_level) {
                $selected_attrs[$possible_attr] = $param_value;
            }
        }
    }
    
    // Priority 1: If URL parameter exists, verify availability before pre-selecting
    if ($url_value) {
        $is_available = shopwell_is_attribute_value_available(
            $attribute_name,
            $url_value,
            $selected_attrs,
            $data['available_variations'],
            $attr_level
        );
        
        // Only pre-select if available
        if ($is_available) {
            // Modify the select to have the value selected
            $html = preg_replace(
                '/<option\s+value="' . preg_quote($url_value, '/') . '"[^>]*>/i',
                '<option value="' . esc_attr($url_value) . '" selected="selected">',
                $html
            );
        } else {
            // Value is not available, remove it from URL params to prevent selection
            // This will be handled by JavaScript, but we mark it in data
            if (!isset($data['unavailable_selections'])) {
                $data['unavailable_selections'] = array();
            }
            $data['unavailable_selections'][$attribute_name] = $url_value;
            $GLOBALS['shopwell_variation_data'] = $data;
        }
    }
    // Priority 2: If no URL parameter, check if we have auto-selected value from single variation
    elseif (isset($data['validated_selections'][$attribute_name])) {
        $auto_value = $data['validated_selections'][$attribute_name];
        
        // Verify availability
        $is_available = shopwell_is_attribute_value_available(
            $attribute_name,
            $auto_value,
            $selected_attrs,
            $data['available_variations'],
            $attr_level
        );
        
        // Pre-select if available
        if ($is_available) {
            // Modify the select to have the value selected
            $html = preg_replace(
                '/<option\s+value="' . preg_quote($auto_value, '/') . '"[^>]*>/i',
                '<option value="' . esc_attr($auto_value) . '" selected="selected">',
                $html
            );
        }
    }
    
    return $html;
}

/**
 * Output processed variation data for frontend
 * ALL processing is done in backend - this just outputs the results
 */
function shopwell_output_variation_data() {
    if (!isset($GLOBALS['shopwell_variation_data'])) {
        return;
    }
    
    $data = $GLOBALS['shopwell_variation_data'];
    
    if (!isset($data['swatches_data'])) {
        return;
    }
    
    $swatches_data = $data['swatches_data'];
    $validated_selections = isset($data['validated_selections']) ? $data['validated_selections'] : array();
    
    // Debug: output data to console
    ?>
    <script type="text/javascript">
    console.log('[SHOPWELL BACKEND] Swatches data:', <?php echo json_encode($swatches_data); ?>);
    console.log('[SHOPWELL BACKEND] Validated selections:', <?php echo json_encode($validated_selections); ?>);
    console.log('[SHOPWELL BACKEND] Available variations sample:', <?php echo json_encode(array_slice($data['available_variations'], 0, 3)); ?>);
    </script>
    <?php
    
    // Output JavaScript to apply backend-processed state to DOM
    ?>
    <script type="text/javascript">
    (function($) {
        'use strict';
        
        // Store backend data globally for frontend interactions
        window.shopwellVariationData = <?php echo json_encode($swatches_data); ?>;
        window.shopwellValidatedSelections = <?php echo json_encode($validated_selections); ?>;
        
        // #region agent log
        fetch('http://127.0.0.1:7243/ingest/1dc8efae-6dce-4ca7-82b7-cab20cf46244', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                sessionId: 'debug-session',
                runId: 'run1',
                hypothesisId: 'A',
                location: 'functions.php:shopwell_output_variation_data',
                message: 'Backend data sent to frontend',
                data: {
                    swatchesDataKeys: Object.keys(<?php echo json_encode($swatches_data); ?>),
                    validatedSelections: <?php echo json_encode($validated_selections); ?>,
                    sampleSwatchData: <?php 
                        $sample = array_slice($swatches_data, 0, 1, true);
                        echo !empty($sample) ? json_encode($sample) : '{}';
                    ?>
                },
                timestamp: Date.now()
            })
        }).catch(function() {});
        // #endregion
        
        // Apply backend-processed state when DOM is ready
        $(document).ready(function() {
            var form = $('.variations_form');
            if (!form.length) return;
            
            var swatchesData = window.shopwellVariationData;
            
            // Apply state for each attribute
            for (var attrName in swatchesData) {
                var attrData = swatchesData[attrName];
                var $select = form.find('select[name="' + attrName + '"]');
                
                if (!$select.length) continue;
                
                var $container = $select.closest('.value');
                if (!$container.length) {
                    $container = $select.closest('tr').find('.value');
                }
                
                // Apply selected value from backend
                if (attrData.selected_value) {
                    $select.val(attrData.selected_value);
                }
                
                // Apply availability and selection state to swatches
                $container.find('.wcboost-variation-swatches__item, .wcboost-variation-swatches_item, .product-variation-item').each(function() {
                    var $swatch = $(this);
                    var swatchValue = $swatch.data('value') || $swatch.attr('data-value');
                    
                    if (!swatchValue || !attrData.options) return;
                    
                    // Case-insensitive lookup
                    var optionData = null;
                    var swatchValueLower = swatchValue.toLowerCase();
                    for (var optKey in attrData.options) {
                        if (optKey.toLowerCase() === swatchValueLower) {
                            optionData = attrData.options[optKey];
                            break;
                        }
                    }
                    
                    if (!optionData) return;
                    
                    // Apply disabled state
                    if (optionData.disabled || !optionData.available) {
                        $swatch.addClass('disabled is-invalid shopwell-disabled');
                        $swatch.removeClass('selected active is-selected');
                        $swatch.attr('aria-pressed', 'false');
                    } else {
                        $swatch.removeClass('disabled is-invalid shopwell-disabled');
                    }
                    
                    // Apply selected state
                    if (optionData.selected && optionData.available) {
                        $swatch.addClass('selected active is-selected');
                        $swatch.attr('aria-pressed', 'true');
                    }
                    
                    // Apply price from backend - try multiple possible containers
                    var $priceContainer = $swatch.find('.wcboost-variation-swatches__name, .product-variation-item__name');
                    
                    // If no name element found, use the swatch itself as container
                    if (!$priceContainer.length) {
                        $priceContainer = $swatch;
                    }
                    
                    // #region agent log
                    fetch('http://127.0.0.1:7243/ingest/1dc8efae-6dce-4ca7-82b7-cab20cf46244', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({
                            sessionId: 'debug-session',
                            runId: 'run1',
                            hypothesisId: 'A',
                            location: 'functions.php:backend_price_application',
                            message: 'Backend applying price to swatch',
                            data: {
                                attrName: attrName,
                                swatchValue: swatchValue,
                                hasPriceHtml: !!optionData.price_html,
                                priceHtml: optionData.price_html ? optionData.price_html.substring(0, 50) : 'EMPTY',
                                isAvailable: optionData.available,
                                isDisabled: optionData.disabled,
                                priceContainerFound: $priceContainer.length > 0,
                                swatchFound: $swatch.length > 0
                            },
                            timestamp: Date.now()
                        })
                    }).catch(function() {});
                    // #endregion
                    
                    if (optionData.price_html) {
                        var $price = $swatch.find('.sv-pill-price');
                        if (!$price.length) {
                            $price = $('<span/>', { 'class': 'sv-pill-price' }).appendTo($priceContainer);
                        }
                        $price.html(optionData.price_html);
                    } else if (optionData.disabled || !optionData.available) {
                        // Show "indisponibil" for disabled items
                        var $price = $swatch.find('.sv-pill-price');
                        if (!$price.length) {
                            $price = $('<span/>', { 'class': 'sv-pill-price' }).appendTo($priceContainer);
                        }
                        $price.html('<span class="sv-pill-price-unavailable">indisponibil</span>');
                    }
                });
            }
            
            // Update prices for all attributes after applying backend state
            // This ensures prices are displayed even for auto-selected attributes
            // Note: Prices are already applied from backend data above, but we trigger update
            // to ensure all prices are refreshed
            setTimeout(function() {
                // Trigger WooCommerce events to refresh prices
                form.trigger('woocommerce_variation_select_change');
                form.trigger('check_variations');
            }, 100);
            
            // Trigger WooCommerce update
            form.trigger('woocommerce_variation_select_change');
            form.trigger('check_variations');
        });
    })(jQuery);
    </script>
    <?php
}

/**
 * Add selected value to variation label in variations table
 */
function shopwell_add_selected_value_to_variation_label($label, $name, $product) {
    if (!isset($GLOBALS['shopwell_variation_data'])) {
        return $label;
    }
    
    $data = $GLOBALS['shopwell_variation_data'];
    
    // Find if this attribute is selected (from URL or auto-selected)
    $selected_value = null;
    
    // First check URL parameters
    foreach ($data['simplified_to_attribute'] as $param_name => $possible_attrs) {
        foreach ($possible_attrs as $attr) {
            // Check if this label matches the attribute
            $attr_label = wc_attribute_label($attr);
            if ($label === $attr_label || $name === $attr) {
                if (isset($data['url_params'][$param_name])) {
                    $selected_value = $data['url_params'][$param_name];
                    break 2;
                }
            }
        }
    }
    
    // If not found in URL, check validated selections (includes auto-selected values)
    if (!$selected_value && isset($data['validated_selections'])) {
        foreach ($data['simplified_to_attribute'] as $param_name => $possible_attrs) {
            foreach ($possible_attrs as $attr) {
                $attr_label = wc_attribute_label($attr);
                if (($label === $attr_label || $name === $attr) && isset($data['validated_selections'][$attr])) {
                    $selected_value = $data['validated_selections'][$attr];
                    break 2;
                }
            }
        }
    }
    
    if ($selected_value) {
        // Get display name for the value
        $display_name = $selected_value;
        
        // Try to get term name if it's a taxonomy
        foreach ($data['simplified_to_attribute'] as $param_name => $possible_attrs) {
            if (isset($data['url_params'][$param_name]) && $data['url_params'][$param_name] === $selected_value) {
                foreach ($possible_attrs as $attr) {
                    $taxonomy = str_replace('attribute_', '', $attr);
                    if (taxonomy_exists($taxonomy)) {
                        $term = get_term_by('slug', $selected_value, $taxonomy);
                        if ($term && !is_wp_error($term)) {
                            $display_name = $term->name;
                            break 2;
                        }
                    }
                }
            }
        }
        
        // Add selected value to label if not already present
        // Check both the display name and the original value to avoid duplicates
        $label_has_value = false;
        if (strpos($label, $display_name) !== false) {
            $label_has_value = true;
        } elseif (strpos($label, $selected_value) !== false) {
            $label_has_value = true;
        } elseif (preg_match('/:\s*' . preg_quote($display_name, '/') . '\s*$/i', $label)) {
            $label_has_value = true;
        } elseif (preg_match('/:\s*' . preg_quote($selected_value, '/') . '\s*$/i', $label)) {
            $label_has_value = true;
        }
        
        if (!$label_has_value) {
            $label .= ': ' . esc_html($display_name);
        }
    }
    
    return $label;
}

// Hook into single product page
add_action('woocommerce_before_single_product', 'shopwell_process_variations_backend', 5);

