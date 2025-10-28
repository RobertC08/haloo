<?php
/**
 * Custom Single Post Template for Haloo Theme
 * This template provides a modern, clean design for single blog posts
 * while maintaining all functionality from functions.php
 */

get_header();
?>

<!-- CSS moved to external file: assets/css/pages/single-post.css -->

<script>
function toggleAccordion(element) {
    // Only work on mobile/tablet
    if (window.innerWidth <= 1024) {
        element.classList.toggle('active');
        const content = element.nextElementSibling;
        content.classList.toggle('active');
    }
}

// Initialize accordion state on page load and resize
function initAccordion() {
    const titles = document.querySelectorAll('.sidebar-widget-title');
    const contents = document.querySelectorAll('.sidebar-widget-content');
    
    if (window.innerWidth <= 1024) {
        // Mobile: start collapsed
        contents.forEach(content => {
            if (!content.classList.contains('active')) {
                content.classList.remove('active');
            }
        });
    } else {
        // Desktop: always open
        titles.forEach(title => title.classList.add('active'));
        contents.forEach(content => content.classList.add('active'));
    }
}

// Run on page load
document.addEventListener('DOMContentLoaded', initAccordion);

// Run on window resize
window.addEventListener('resize', initAccordion);
</script>

<div class="single-post-page">
    <div class="single-post-container">
        <?php while (have_posts()) : the_post(); ?>
            
            <!-- Single Post Header -->
            <header class="single-post-header">
            <h1 class="single-post-title"><?php the_title(); ?></h1>
            <div class="single-post-meta">
                <div class="meta-item">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                    </svg>
                    <span><?php the_author(); ?></span>
                </div>
                <div class="meta-item">
                    <svg viewBox="0 0 24 24">
                        <path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM7 10h5v5H7z"/>
                    </svg>
                    <span><?php echo get_the_date('d.m.Y'); ?></span>
                </div>
                <?php 
                $read_time = get_post_meta(get_the_ID(), '_blog_read_time', true);
                if ($read_time) : ?>
                <div class="meta-item">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                    </svg>
                    <span><?php echo $read_time; ?> min citire</span>
                </div>
                <?php endif; ?>
                <div class="meta-item">
                    <svg viewBox="0 0 24 24">
                        <path d="M21.99 4c0-1.1-.89-2-2-2H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h14l4 4-.01-18zM18 14H6v-2h12v2zm0-3H6V9h12v2zm0-3H6V6h12v2z"/>
                    </svg>
                    <span><?php comments_number('0 comentarii', '1 comentariu', '% comentarii'); ?></span>
                </div>
            </div>
        </header>

            <!-- Single Post Content -->
            <div class="single-post-content">
                <!-- Sidebar -->
                <aside class="single-post-sidebar">
                    <!-- Recent Posts -->
                    <div class="sidebar-widget">
                        <h3 class="sidebar-widget-title" onclick="toggleAccordion(this)">Articole recente</h3>
                        <div class="sidebar-widget-content">
                            <ul>
                                <?php
                                $recent_posts = get_posts(array(
                                    'numberposts' => 5,
                                    'post_status' => 'publish',
                                    'post__not_in' => array(get_the_ID())
                                ));
                                
                                foreach ($recent_posts as $post) :
                                    setup_postdata($post);
                                ?>
                                    <li>
                                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                        <div style="color: #6b7280; font-size: 0.875rem; margin-top: 0.25rem;">
                                            <?php echo get_the_date('d.m.Y'); ?>
                                        </div>
                                    </li>
                                <?php
                                endforeach;
                                wp_reset_postdata();
                                ?>
                            </ul>
                        </div>
                    </div>

                    <!-- Categories -->
                    <div class="sidebar-widget">
                        <h3 class="sidebar-widget-title" onclick="toggleAccordion(this)">Categorii</h3>
                        <div class="sidebar-widget-content">
                            <ul>
                                <?php
                                $categories = get_categories(array(
                                    'hide_empty' => true,
                                    'number' => 10
                                ));
                                
                                foreach ($categories as $category) :
                                ?>
                                    <li>
                                        <a href="<?php echo get_category_link($category->term_id); ?>">
                                            <?php echo esc_html($category->name); ?>
                                            <span style="color: #6b7280; font-size: 0.875rem;">(<?php echo $category->count; ?>)</span>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </aside>

                <main class="single-post-main">
                <?php if (has_post_thumbnail()) : ?>
                    <div class="single-post-featured-image">
                        <?php the_post_thumbnail('large'); ?>
                    </div>
                <?php endif; ?>

                <!-- Post Categories -->
                <?php 
                $categories = get_the_category();
                if ($categories) : ?>
                    <div class="post-categories">
                        <?php foreach ($categories as $category) : ?>
                            <a href="<?php echo get_category_link($category->term_id); ?>" class="post-category">
                                <?php echo esc_html($category->name); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Post Content -->
                <div class="single-post-content-text">
                    <?php the_content(); ?>
                </div>

                <!-- Post Tags -->
                <?php 
                $tags = get_the_tags();
                if ($tags) : ?>
                    <div class="post-tags">
                        <?php foreach ($tags as $tag) : ?>
                            <a href="<?php echo get_tag_link($tag->term_id); ?>" class="post-tag">
                                #<?php echo esc_html($tag->name); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>


                    <!-- Comments -->
                    <?php
                    if (comments_open() || get_comments_number()) :
                        comments_template();
                    endif;
                    ?>
                </main>
            </div>

            <!-- Related Posts -->
            <?php
            $post_id = get_the_ID();
            $categories = wp_get_post_categories($post_id);

            if (!empty($categories)) {
                $related_posts = get_posts(array(
                    'category__in' => $categories,
                    'post__not_in' => array($post_id),
                    'posts_per_page' => 3,
                    'orderby' => 'rand'
                ));

                if (!empty($related_posts)) :
            ?>
                    <div class="related-posts">
                        <h3>Articole similare</h3>
                        <div class="related-posts-grid">
                            <?php foreach ($related_posts as $related_post) : 
                                $featured_image = get_the_post_thumbnail_url($related_post->ID, 'medium');
                                $excerpt = get_the_excerpt($related_post->ID);
                                if (empty($excerpt)) {
                                    $excerpt = wp_trim_words(get_post_field('post_content', $related_post->ID), 20, '...');
                                }
                            ?>
                                <div class="related-post-item">
                                    <?php if ($featured_image) : ?>
                                        <img src="<?php echo esc_url($featured_image); ?>" alt="<?php echo esc_attr(get_the_title($related_post->ID)); ?>" style="width: 100%; height: 150px; object-fit: cover; border-radius: 8px; margin-bottom: 1rem;">
                                    <?php endif; ?>
                                    <h4>
                                        <a href="<?php echo get_permalink($related_post->ID); ?>">
                                            <?php echo get_the_title($related_post->ID); ?>
                                        </a>
                                    </h4>
                                    <p style="color: #6b7280; font-size: 0.875rem; margin-bottom: 0.5rem;">
                                        <?php echo wp_trim_words(strip_tags($excerpt), 15, '...'); ?>
                                    </p>
                                    <p class="related-post-date">
                                        <?php echo get_the_date('d.m.Y', $related_post->ID); ?>
                                    </p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
            <?php
                endif;
            }
            ?>

            <!-- Social Sharing -->
            <div class="social-sharing">
                <h4>Distribuie acest articol:</h4>
                <div class="social-sharing-buttons">
                    <?php
                    $post_url = get_permalink();
                    $post_title = get_the_title();
                    ?>
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($post_url); ?>" 
                       target="_blank" rel="noopener noreferrer" class="facebook" title="Share on Facebook">
                        <img src="https://haloo.lemon.thisisfruit.com/wp-content/uploads/2025/10/facebook.svg" alt="Facebook">
                    </a>
                    <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode($post_url); ?>&text=<?php echo urlencode($post_title); ?>" 
                       target="_blank" rel="noopener noreferrer" class="twitter" title="Share on X">
                        <img src="https://haloo.lemon.thisisfruit.com/wp-content/uploads/2025/10/twitter.png" alt="X">
                    </a>
                    <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo urlencode($post_url); ?>" 
                       target="_blank" rel="noopener noreferrer" class="linkedin" title="Share on LinkedIn">
                        <img src="https://haloo.lemon.thisisfruit.com/wp-content/uploads/2025/10/linkedin.png" alt="LinkedIn">
                    </a>
                    <a href="https://wa.me/?text=<?php echo urlencode($post_title . ' - ' . $post_url); ?>" 
                       target="_blank" rel="noopener noreferrer" class="whatsapp" title="Share on WhatsApp">
                        <img src="https://haloo.lemon.thisisfruit.com/wp-content/uploads/2025/10/whatsapp.svg" alt="WhatsApp">
                    </a>
                </div>
            </div>

        <?php endwhile; ?>
    </div>
</div>

<?php get_footer(); ?>
