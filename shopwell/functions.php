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

// Start session for general functionality
if (!session_id()) {
    session_start();
}

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
    $args = array(
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
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
    
    foreach ( $query->posts as $product_post ) {
        $product = wc_get_product( $product_post->ID );
        
        if ( $product && $product->is_purchasable() ) {
            if ( $product->is_type( 'variable' ) ) {
                $variation_prices = $product->get_variation_prices();
                if ( ! empty( $variation_prices['price'] ) ) {
                    $prices = array_merge( $prices, array_values( $variation_prices['price'] ) );
                }
            } else {
                $price = $product->get_price();
                if ( $price > 0 ) {
                    $prices[] = $price;
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
    return 16; // Display 16 products per page
}
add_filter('loop_shop_per_page', 'custom_products_per_page', 999);

/**
 * Ensure WooCommerce shop settings respect our custom product count
 */
function ensure_shop_products_per_page($query) {
    if (!is_admin() && $query->is_main_query()) {
        if (is_shop() || is_product_category() || is_product_tag() || is_product_taxonomy()) {
            $query->set('posts_per_page', 16);
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
 * Ensure all variable products have proper price ranges set
 * This runs after product sync to fix any missing price ranges
 */
function ensure_variable_product_price_ranges() {
    // Get all variable products
    $variable_products = wc_get_products([
        'type' => 'variable',
        'limit' => -1,
        'status' => 'publish'
    ]);
    
    foreach ($variable_products as $product) {
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
            
            // Only update if price ranges are not set or are 0
            $current_price = get_post_meta($product->get_id(), '_price', true);
            if (empty($current_price) || $current_price == 0) {
                // Set price range meta for proper display
                update_post_meta($product->get_id(), '_price', $min_price);
                update_post_meta($product->get_id(), '_min_variation_price', $min_price);
                update_post_meta($product->get_id(), '_max_variation_price', $max_price);
                
                // Set min/max regular price as well
                update_post_meta($product->get_id(), '_min_variation_regular_price', $min_price);
                update_post_meta($product->get_id(), '_max_variation_regular_price', $max_price);
                
                // Clear product cache
                wc_delete_product_transients($product->get_id());
            }
        }
    }
}

// Run this function after product sync (you can call this manually or hook it to sync completion)
// add_action('foxway_product_sync_completed', 'ensure_variable_product_price_ranges');

/**
 * Add admin notice with button to fix variable product prices
 */
function add_fix_prices_admin_notice() {
    if (isset($_GET['fix_variable_prices']) && $_GET['fix_variable_prices'] === '1') {
        if (current_user_can('manage_options')) {
            ensure_variable_product_price_ranges();
            echo '<div class="notice notice-success"><p>Variable product prices have been fixed!</p></div>';
        }
    }
    
    if (current_user_can('manage_options')) {
        echo '<div class="notice notice-info"><p>';
        echo 'If variable products show "0,00 lei" on shop pages, ';
        echo '<a href="' . admin_url('?fix_variable_prices=1') . '" class="button button-primary">Fix Variable Product Prices</a>';
        echo '</p></div>';
    }
}
add_action('admin_notices', 'add_fix_prices_admin_notice');

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
    jQuery(document).ready(function($) {
        // Function to auto-select first variation on single product page
        function autoSelectFirstVariation() {
            if (!$('body').hasClass('single-product')) {
                return;
            }
            
            var filterAttributes = {};
            
            // Check URL parameters first
            var urlParams = new URLSearchParams(window.location.search);
            urlParams.forEach(function(value, key) {
                if (key.startsWith('attribute_') || key.startsWith('pa_')) {
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
                    console.log('Could not parse referrer');
                }
            }
            
            console.log('Filter attributes to apply:', filterAttributes);
            
            function selectVariations() {
                var $variationForm = $('form.variations_form');
                
                if (!$variationForm.length) {
                    console.log('Variation form not found');
                    return false;
                }
                
                var $selects = $variationForm.find('select[name^="attribute_"]');
                
                if (!$selects.length) {
                    console.log('No variation selects found');
                    return false;
                }
                
                // Get available variations data
                var variationsData = $variationForm.data('product_variations');
                console.log('Available variations data:', variationsData);
                
                var allSelected = true;
                var madeSelection = false;
                
                // Find a valid combination that includes filter attributes
                var targetCombination = {};
                
                if (variationsData && variationsData.length > 0 && Object.keys(filterAttributes).length > 0) {
                    console.log('Looking for valid combination with filters:', filterAttributes);
                    
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
                    
                    console.log('Found', matchingVariations.length, 'matching variations');
                    
                    if (matchingVariations.length > 0) {
                        // Use the first matching variation
                        targetCombination = matchingVariations[0].attributes;
                        console.log('Target combination:', targetCombination);
                    }
                }
                
                $selects.each(function() {
                    var $select = $(this);
                    var selectName = $select.attr('name');
                    var $options = $select.find('option:not([value=""])');
                    
                    // Skip if no options available
                    if (!$options.length) {
                        console.log('No options for', selectName);
                        return;
                    }
                    
                    var valueToSelect = null;
                    
                    // First, check if we have a valid combination from variations data
                    if (Object.keys(targetCombination).length > 0) {
                        // Try to find value from target combination
                        if (targetCombination[selectName]) {
                            valueToSelect = targetCombination[selectName];
                            console.log('Using value from valid combination:', valueToSelect, 'for', selectName);
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
                            console.log('Using filter value:', valueToSelect, 'for', selectName);
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
                            console.log('Found match:', $matchingOption.first().val(), 'for', selectName);
                            $select.val($matchingOption.first().val()).trigger('change');
                            madeSelection = true;
                            return; // Skip first option selection
                        } else {
                            console.log('No match found for', valueToSelect, 'in', selectName);
                            console.log('Available options:', $options.map(function() { return $(this).val(); }).get());
                        }
                    }
                    
                    // Skip if already selected (check after filter attempt)
                    if ($select.val() && $select.val() !== '') {
                        console.log(selectName, 'already selected:', $select.val());
                        return;
                    }
                    
                    // Find first available option only if no filter was specified for this attribute
                    var $firstOption = $options.first();
                    
                    if ($firstOption.length) {
                        console.log('Selecting first option:', $firstOption.val(), 'for', selectName);
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
                console.log('Attempt', attempts, 'to select variations');
                
                if (selectVariations()) {
                    console.log('Successfully selected variations');
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
                console.log('WC variation form event fired');
                setTimeout(selectVariations, 200);
            });
        }
        
        // Function to handle variation selection from filters on catalog pages
        function handleFilterVariations() {
            if ($('body').hasClass('woocommerce-shop') || 
                $('body').hasClass('tax-product_cat') || 
                $('body').hasClass('tax-product_tag') || 
                $('body').hasClass('post-type-archive-product')) {
                
                console.log('HandleFilterVariations running on catalog page');
                console.log('Current URL:', window.location.href);
                
                // Get URL parameters for active filters
                var urlParams = new URLSearchParams(window.location.search);
                var filterAttributes = {};
                
                console.log('All URL params:', Array.from(urlParams.entries()));
                
                // Collect all attribute filters from URL
                urlParams.forEach(function(value, key) {
                    console.log('Checking param:', key, '=', value);
                    if (key.startsWith('filter_') || key.startsWith('pa_')) {
                        var attrName = key.replace('filter_', '').replace('pa_', '');
                        filterAttributes[attrName] = value.split(',');
                        console.log('Added filter attribute:', attrName, '=', filterAttributes[attrName]);
                    }
                });
                
                console.log('Final filterAttributes:', filterAttributes);
                
                // Add filter parameters to product links
                if (Object.keys(filterAttributes).length > 0) {
                    console.log('Adding filter params to product links:', filterAttributes);
                    
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
                    
                    console.log('Found', $productLinks.length, 'product links');
                    
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
                            console.log('Updated link:', href, '->', newHref);
                        } catch(e) {
                            console.log('Error updating link:', href, e);
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
        
        // Run on page load
        autoSelectFirstVariation();
        handleFilterVariations();
        
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
            error_log('Shopwell: Failed to create memory attribute - ' . $result->get_error_message());
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

