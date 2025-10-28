<?php
/**
 * Template Name: Homepage with Products
 * 
 * The template for displaying the homepage with featured products
 * This template can be selected in the page editor
 *
 * @package Shopwell Theme
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

// Add catalog functionality
use Shopwell\WooCommerce\Catalog;

// Initialize catalog class
$catalog = Catalog::instance();

get_header();

/**
 * Hook: woocommerce_before_main_content.
 *
 * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
 * @hooked woocommerce_breadcrumb - 20
 * @hooked WC_Structured_Data::generate_website_data() - 30
 */
do_action( 'woocommerce_before_main_content' );

// Homepage Hero Section
?>
<section class="homepage-hero">
    <div class="hero-content">
        <h1><?php echo get_theme_mod('homepage_hero_title', 'Welcome to Our Store'); ?></h1>
        <p><?php echo get_theme_mod('homepage_hero_subtitle', 'Discover amazing products at great prices'); ?></p>
        <a href="<?php echo wc_get_page_permalink('shop'); ?>" class="btn btn-primary">Shop Now</a>
    </div>
</section>

<?php
// Top Categories Section (if enabled)
if ( class_exists('Shopwell\Helper') && \Shopwell\Helper::get_option( 'top_categories' ) ) {
    $catalog->top_categories();
}
// Featured Products Section
$featured_products = wc_get_featured_product_ids();
if ( !empty($featured_products) ) {
    $args = array(
        'post_type' => 'product',
        'posts_per_page' => 8,
        'post__in' => $featured_products,
        'orderby' => 'post__in'
    );
    
    $featured_query = new WP_Query($args);
    
    if ( $featured_query->have_posts() ) {
        ?>
        <section class="featured-products">
            <div class="container">
                <h2 class="section-title"><?php echo get_theme_mod('homepage_featured_title', 'Featured Products'); ?></h2>
                
                <?php
                // Add catalog toolbar for featured products
                if ( class_exists('Shopwell\Helper') && \Shopwell\Helper::get_option( 'catalog_toolbar' ) ) {
                    echo '<div class="catalog-toolbar catalog-toolbar--homepage">';
                        echo '<div class="catalog-toolbar__toolbar">';
                            // Add view options
                            if ( in_array( 'view', (array) \Shopwell\Helper::get_option( 'catalog_toolbar_view' ) ) ) {
                                $catalog->toolbar_view();
                            }
                        echo '</div>';
                    echo '</div>';
                }
                
                /**
                 * Hook: woocommerce_before_shop_loop.
                 *
                 * @hooked woocommerce_output_all_notices - 10
                 */
                do_action( 'woocommerce_before_shop_loop' );

                woocommerce_product_loop_start();

                while ( $featured_query->have_posts() ) {
                    $featured_query->the_post();

                    /**
                     * Hook: woocommerce_shop_loop.
                     */
                    do_action( 'woocommerce_shop_loop' );

                    wc_get_template_part( 'content', 'product' );
                }

                woocommerce_product_loop_end();
                
                // Add pagination if needed
                if ( class_exists('Shopwell\Helper') ) {
                    $catalog->pagination();
                }
                
                wp_reset_postdata();
                ?>
                
                <div class="view-all-products">
                    <a href="<?php echo wc_get_page_permalink('shop'); ?>" class="btn btn-outline">View All Products</a>
                </div>
            </div>
        </section>
        <?php
    }
}

// Latest Products Section
$latest_args = array(
    'post_type' => 'product',
    'posts_per_page' => 6,
    'orderby' => 'date',
    'order' => 'DESC'
);

$latest_query = new WP_Query($latest_args);

if ( $latest_query->have_posts() ) {
    ?>
    <section class="latest-products">
        <div class="container">
            <h2 class="section-title"><?php echo get_theme_mod('homepage_latest_title', 'Latest Products'); ?></h2>
            
            <?php
            // Add catalog toolbar for latest products
            if ( class_exists('Shopwell\Helper') && \Shopwell\Helper::get_option( 'catalog_toolbar' ) ) {
                echo '<div class="catalog-toolbar catalog-toolbar--homepage">';
                    echo '<div class="catalog-toolbar__toolbar">';
                        // Add view options
                        if ( in_array( 'view', (array) \Shopwell\Helper::get_option( 'catalog_toolbar_view' ) ) ) {
                            $catalog->toolbar_view();
                        }
                    echo '</div>';
                echo '</div>';
            }
            
            woocommerce_product_loop_start();

            while ( $latest_query->have_posts() ) {
                $latest_query->the_post();

                do_action( 'woocommerce_shop_loop' );
                wc_get_template_part( 'content', 'product' );
            }

            woocommerce_product_loop_end();
            
            // Add pagination if needed
            if ( class_exists('Shopwell\Helper') ) {
                $catalog->pagination();
            }
            
            wp_reset_postdata();
            ?>
        </div>
    </section>
    <?php
}

// Homepage Content Area
if ( have_posts() ) {
    while ( have_posts() ) {
        the_post();
        ?>
        <section class="homepage-content">
            <div class="container">
                <?php the_content(); ?>
            </div>
        </section>
        <?php
    }
}

/**
 * Hook: woocommerce_after_main_content.
 *
 * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
 */
do_action( 'woocommerce_after_main_content' );

// Add filter sidebar if enabled
if ( class_exists('Shopwell\Helper') && \Shopwell\Helper::get_option( 'catalog_toolbar_layout' ) == '2' ) {
    $catalog->filter_sidebar();
}

/**
 * Hook: woocommerce_sidebar.
 *
 * @hooked woocommerce_get_sidebar - 10
 */
do_action( 'woocommerce_sidebar' );

// Add orderby list modal
if ( class_exists('Shopwell\Helper') && in_array( 'sortby', (array) \Shopwell\Helper::get_option( 'catalog_toolbar_view' ) ) ) {
    $catalog->orderby_list();
}

get_footer();
