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
    // Only log if WP_DEBUG is enabled
    if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
        return;
    }
    
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
        wp_send_json_error('Toate câmpurile obligatorii trebuie completate.');
        return;
    }
    
    // Validate email format
    if (!is_email($email)) {
        wp_send_json_error('Adresa de email nu este validă.');
        return;
    }
    
    // Prepare email content
    $subject_map = [
        'general' => 'Întrebare generală',
        'product' => 'Întrebare despre produs',
        'warranty' => 'Garanție și service',
        'delivery' => 'Livrare și retur',
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
                <h2>Mulțumim pentru mesajul tău!</h2>
                <p>Bună {$name} {$lastname},</p>
                <p>Am primit mesajul tău despre {$subject_text} și îți vom răspunde în cel mai scurt timp posibil.</p>
                <p><strong>Detaliile mesajului tău:</strong></p>
                <ul>
                    <li>Subiect: {$subject_text}</li>
                    <li>Interesat de: {$services_text}</li>
                    <li>Data: " . date('d.m.Y H:i') . "</li>
                </ul>
                <p>În cazul în care ai o întrebare urgentă despre telefoanele noastre refurbished, ne poți contacta direct la:</p>
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
            wp_send_json_error('A apărut o problemă la trimiterea mesajului. Te rugăm să încerci din nou.');
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
            wp_send_json_error('A apărut o problemă la trimiterea mesajului. Te rugăm să încerci din nou.');
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
                    <h2>Mulțumim pentru mesajul tău!</h2>
                    <p>Bună {$name} {$lastname},</p>
                    <p>Am primit mesajul tău despre {$subject_text} și îți vom răspunde în cel mai scurt timp posibil.</p>
                    <p><strong>Detaliile mesajului tău:</strong></p>
                    <ul>
                        <li>Subiect: {$subject_text}</li>
                        <li>Interesat de: {$services_text}</li>
                        <li>Data: " . date('d.m.Y H:i') . "</li>
                    </ul>
                    <p>În cazul în care ai o întrebare urgentă despre telefoanele noastre refurbished, ne poți contacta direct la:</p>
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
            wp_send_json_error('A apărut o problemă la trimiterea mesajului. Te rugăm să încerci din nou.');
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
        echo '<div class="notice notice-success"><p>Setările au fost salvate!</p></div>';
    }
    
    $api_key = get_option('resend_api_key', '');
    ?>
    <div class="wrap">
        <h1>Setări Resend Email</h1>
        <form method="post" action="">
            <table class="form-table">
                <tr>
                    <th scope="row">Resend API Key</th>
                    <td>
                        <input type="password" name="resend_api_key" value="<?php echo esc_attr($api_key); ?>" class="regular-text" />
                        <p class="description">Introduceți cheia API de la Resend. Dacă nu este setată, se va folosi funcția de email standard WordPress.</p>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
        <p><strong>Instrucțiuni:</strong></p>
        <ol>
            <li>Înregistrați-vă pe <a href="https://resend.com" target="_blank">resend.com</a></li>
            <li>Obțineți cheia API din dashboard-ul Resend</li>
            <li>Introduceți cheia API în câmpul de mai sus</li>
            <li>Salvați setările</li>
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
                    📘 Facebook
                </a>
                <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode($post_url); ?>&text=<?php echo urlencode($post_title); ?>" 
                   target="_blank" 
                   rel="noopener noreferrer"
                   style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1rem; background: #1da1f2; color: white; text-decoration: none; border-radius: 8px; font-weight: 600; transition: all 0.3s ease;">
                    🐦 Twitter
                </a>
                <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo urlencode($post_url); ?>" 
                   target="_blank" 
                   rel="noopener noreferrer"
                   style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1rem; background: #0077b5; color: white; text-decoration: none; border-radius: 8px; font-weight: 600; transition: all 0.3s ease;">
                    💼 LinkedIn
                </a>
                <a href="https://wa.me/?text=<?php echo urlencode($post_title . ' - ' . $post_url); ?>" 
                   target="_blank" 
                   rel="noopener noreferrer"
                   style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1rem; background: #25d366; color: white; text-decoration: none; border-radius: 8px; font-weight: 600; transition: all 0.3s ease;">
                    💬 WhatsApp
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
 * OPTIMIZARE: Asigură că doar prima pagină (20 produse) se încarcă inițial
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
            
            // OPTIMIZARE: Asigură că se folosește paginarea corectă
            // Dacă nu există parametru 'paged', înseamnă că e prima pagină
            if (!isset($_GET['paged']) || empty($_GET['paged'])) {
                $query->set('paged', 1);
            }
        }
    }
}
add_action('pre_get_posts', 'ensure_shop_products_per_page', 20);

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
    ?>
    <script type="text/javascript">
    // Firefox fix: Ensure JavaScript runs after browser back/forward navigation
    // This fixes an issue where Firefox doesn't properly restore JavaScript state
    // when using pushState and navigating back in history
    window.onunload = function(){};
    
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
                // Don't update URL if we're restoring from browser navigation
                if (isRestoringFromUrl) {
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
                $variationForm.find('select[name^="attribute_"], input[name^="attribute_"][type="hidden"], input[name^="attribute_"][type="radio"]:checked').each(function() {
                    var $field = $(this);
                    var fieldName = $field.attr('name');
                    var fieldValue = $field.val();
                    
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
                
                // Update URL if there are changes
                if (hasChanges) {
                    var newUrl = window.location.pathname;
                    var queryString = urlParams.toString();
                    if (queryString) {
                        newUrl += '?' + queryString;
                    }
                    
                    // Use replaceState instead of pushState to avoid creating history entries
                    // This allows the back button to work properly
                    if (window.history && window.history.replaceState) {
                        window.history.replaceState({}, '', newUrl);
                    }
                }
            }
            
            // Handle browser back/forward buttons
            var isRestoringFromUrl = false;
            $(window).on('popstate', function(e) {
                if (!$('body').hasClass('single-product')) {
                    return;
                }
                
                // Prevent infinite loop by flagging that we're restoring
                isRestoringFromUrl = true;
                
                // Restore form state from URL
                restoreFormFromUrl();
                
                // Reset flag after a short delay
                setTimeout(function() {
                    isRestoringFromUrl = false;
                }, 500);
            });
            
            // Listen to variation form changes (dropdowns and inputs)
            $variationForm.on('change', 'select[name^="attribute_"], input[name^="attribute_"]', function() {
                // Small delay to ensure WooCommerce has processed the change
                setTimeout(updateUrl, 100);
            });
            
            // Listen to clicks on variation swatches (color buttons, etc.)
            $variationForm.on('click', '.wcboost-variation-swatches__item, .product-variation-item', function() {
                // Delay to allow WooCommerce to update the form fields
                setTimeout(updateUrl, 200);
            });
            
            // Also listen to WooCommerce variation events
            $variationForm.on('found_variation', function(event, variation) {
                // Update URL when a valid variation is found
                setTimeout(updateUrl, 100);
            });
            
            // Listen to show_variation event (when variation is displayed)
            $variationForm.on('show_variation', function(event, variation) {
                setTimeout(updateUrl, 100);
            });
            
            // Listen to WooCommerce variation form updates
            $(document.body).on('wc_variation_form', function() {
                setTimeout(updateUrl, 200);
            });
            
            // Use MutationObserver to detect when hidden inputs are updated by swatch plugins
            if (typeof MutationObserver !== 'undefined') {
                var observer = new MutationObserver(function(mutations) {
                    var shouldUpdate = false;
                    mutations.forEach(function(mutation) {
                        if (mutation.type === 'attributes' && mutation.attributeName === 'value') {
                            var $target = $(mutation.target);
                            if ($target.is('input[name^="attribute_"]') || $target.is('select[name^="attribute_"]')) {
                                shouldUpdate = true;
                            }
                        }
                    });
                    if (shouldUpdate) {
                        setTimeout(updateUrl, 100);
                    }
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
            setTimeout(updateUrl, 500);
        }
        
        // Run on page load
        autoSelectFirstVariation();
        handleFilterVariations();
        updateUrlWithVariations();
        
        // Re-run after AJAX updates (for filtered/sorted products)
        $(document).on('shopwell_ajax_update_complete', function() {
            handleFilterVariations();
        });
        
        // Re-run when WooCommerce updates the product list
        $(document.body).on('updated_wc_div', function() {
            handleFilterVariations();
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
    // OPTIMIZARE: Limitează la 500 produse în loc de -1 pentru performanță mai bună
    $args = array(
        'post_type' => 'product',
        'posts_per_page' => 500, // Limitează la 500 pentru performanță (înainte: -1)
        'post_status' => 'publish',
        'tax_query' => array(
            array(
                'taxonomy' => 'product_cat',
                'field' => 'term_id',
                'terms' => $category_term->term_id,
            ),
        ),
        'fields' => 'ids', // Doar ID-uri pentru performanță optimă
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
 * Acest cod dezactivează complet funcționalitatea de compare în toată tema
 * Va rămâne la update-uri deoarece este în functions.php
 */
function shopwell_disable_compare_feature() {
	// Previne inițializarea clasei Compare prin eliminarea condiției
	// Hook înainte de add_actions pentru a preveni inițializarea
	add_action( 'wp', function() {
		// Elimină inițializarea clasei Compare din WooCommerce
		if ( class_exists( '\Shopwell\WooCommerce\Compare' ) ) {
			// Elimină toate acțiunile și filtrele din clasa Compare
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
	
	// Dezactivează clasa Compare din tema
	add_filter( 'shopwell_change_compare_button_settings', '__return_false', 999 );
	
	// Elimină compare din header items
	add_filter( 'shopwell_get_header_items', function( $items ) {
		if ( isset( $items['compare'] ) ) {
			unset( $items['compare'] );
		}
		return $items;
	}, 999 );
	
	// Elimină compare din mobile navigation bar items
	add_filter( 'shopwell_get_mobile_navigation_bar_items', function( $items ) {
		if ( isset( $items['compare'] ) ) {
			unset( $items['compare'] );
		}
		return $items;
	}, 999 );
	
	// Dezactivează butonul compare pe paginile single product
	add_filter( 'shopwell_enable_compare_button', '__return_false', 999 );
	
	// Dezactivează butonul compare pe product cards
	add_filter( 'shopwell_product_card_compare', '__return_false', 999 );
	
	// Elimină compare din hamburger menu
	add_filter( 'shopwell_get_hamburger_items', function( $items ) {
		if ( isset( $items['compare'] ) ) {
			unset( $items['compare'] );
		}
		return $items;
	}, 999 );
	
	// Elimină compare din account panel
	add_filter( 'shopwell_get_account_panel_items', function( $items ) {
		if ( isset( $items['compare'] ) ) {
			unset( $items['compare'] );
		}
		return $items;
	}, 999 );
	
	// Elimină wishlist-ul din galerie (păstrăm doar cel din summary)
	// Astfel wishlist-ul va apărea o singură dată pe pagină
	remove_action( 'woocommerce_before_single_product_summary', array( '\Shopwell\WooCommerce\Single_Product', 'product_featured_buttons' ), 20 );
	
	// Ascunde template-urile compare prin CSS (pentru siguranță)
	add_action( 'wp_head', function() {
		?>
		<style>
		/* Ascunde butoanele și linkurile de compare */
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

// Dezactivează funcționalitatea JavaScript pentru compare
function shopwell_disable_compare_js() {
	?>
	<script>
	// Dezactivează funcționalitatea compare
	if (typeof shopwell !== 'undefined') {
		if (typeof shopwell.addCompare === 'function') {
			shopwell.addCompare = function() {};
		}
		if (typeof shopwell.addedToCompareNotice === 'function') {
			shopwell.addedToCompareNotice = function() {};
		}
	}
	// Elimină event listeners pentru compare
	jQuery(document).ready(function($) {
		$('body').off('click', 'a.compare, .compare-button, [class*="compare"]');
		$('body').off('added_to_compare');
	});
	</script>
	<?php
}
add_action( 'wp_footer', 'shopwell_disable_compare_js', 999 );


