<?php
/**
 * Homepage Template
 *
 * Template for displaying the homepage with custom sections
 *
 * @package Shopwell
 */

get_header();
?>

<?php if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'archive' ) ) { ?>
<div class="site-content-container">
    <?php do_action( 'shopwell_before_open_blog_main' ); ?>
    
    <div id="primary" class="content-area">
        <main id="main" class="site-main">
            
            <!-- Hero Section -->
            <section class="homepage-hero">
                <div class="container">
                    <div class="hero-content">
                        <h1 class="hero-title"><?php echo get_theme_mod('homepage_hero_title', 'Welcome to Our Store'); ?></h1>
                        <p class="hero-description"><?php echo get_theme_mod('homepage_hero_description', 'Discover amazing products at great prices'); ?></p>
                        <a href="<?php echo get_theme_mod('homepage_hero_button_url', '#'); ?>" class="hero-button">
                            <?php echo get_theme_mod('homepage_hero_button_text', 'Shop Now'); ?>
                        </a>
                    </div>
                </div>
            </section>

            <!-- Featured Products Section -->
            <section class="homepage-featured-products">
                <div class="container">
                    <h2 class="section-title"><?php echo get_theme_mod('homepage_featured_title', 'Featured Products'); ?></h2>
                    <?php
                    $featured_products = get_theme_mod('homepage_featured_products', '');
                    if ($featured_products) {
                        echo do_shortcode('[products ids="' . $featured_products . '" columns="4"]');
                    } else {
                        echo do_shortcode('[featured_products limit="4" columns="4"]');
                    }
                    ?>
                </div>
            </section>

            <!-- Categories Section -->
            <section class="homepage-categories">
                <div class="container">
                    <h2 class="section-title"><?php echo get_theme_mod('homepage_categories_title', 'Shop by Category'); ?></h2>
                    <div class="categories-grid">
                        <?php
                        $product_categories = get_terms(array(
                            'taxonomy' => 'product_cat',
                            'hide_empty' => true,
                            'number' => 6
                        ));
                        
                        if ($product_categories && !is_wp_error($product_categories)) {
                            foreach ($product_categories as $category) {
                                $thumbnail_id = get_term_meta($category->term_id, 'thumbnail_id', true);
                                $image_url = $thumbnail_id ? wp_get_attachment_url($thumbnail_id) : wc_placeholder_img_src();
                                ?>
                                <div class="category-item">
                                    <a href="<?php echo get_term_link($category); ?>">
                                        <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($category->name); ?>">
                                        <h3><?php echo esc_html($category->name); ?></h3>
                                    </a>
                                </div>
                                <?php
                            }
                        }
                        ?>
                    </div>
                </div>
            </section>

            <!-- Latest Blog Posts -->
            <section class="homepage-blog">
                <div class="container">
                    <h2 class="section-title"><?php echo get_theme_mod('homepage_blog_title', 'Latest News'); ?></h2>
                    <div class="blog-posts">
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
                                <article class="blog-post">
                                    <?php if (has_post_thumbnail()) : ?>
                                        <div class="post-thumbnail">
                                            <a href="<?php the_permalink(); ?>">
                                                <?php the_post_thumbnail('medium'); ?>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                    <div class="post-content">
                                        <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                                        <div class="post-meta">
                                            <span class="post-date"><?php echo get_the_date(); ?></span>
                                        </div>
                                        <p><?php echo wp_trim_words(get_the_excerpt(), 20); ?></p>
                                    </div>
                                </article>
                                <?php
                            }
                            wp_reset_postdata();
                        }
                        ?>
                    </div>
                </div>
            </section>

            <!-- Newsletter Section -->
            <section class="homepage-newsletter">
                <div class="container">
                    <div class="newsletter-content">
                        <h2><?php echo get_theme_mod('homepage_newsletter_title', 'Subscribe to Our Newsletter'); ?></h2>
                        <p><?php echo get_theme_mod('homepage_newsletter_description', 'Get the latest updates and exclusive offers'); ?></p>
                        <?php
                        if (function_exists('mc4wp_show_form')) {
                            mc4wp_show_form();
                        } else {
                            ?>
                            <form class="newsletter-form">
                                <input type="email" placeholder="Enter your email" required>
                                <button type="submit">Subscribe</button>
                            </form>
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
<?php } ?>

<?php
get_footer();