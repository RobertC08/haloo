<?php
/**
 * Template Name: Blog
 * 
 * Blog Template
 *
 * Template for displaying the blog page with posts listing
 *
 * @package Haloo
 */

get_header();
?>

<!-- CSS moved to external file: assets/css/pages/blog.css -->

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

<div class="blog-page">
    <div class="blog-container">
        <!-- Blog Header -->
        <div class="blog-header">
            <h1 class="blog-title">Blog</h1>
            <p class="blog-subtitle">Descoperă ultimele știri, ghiduri și informații despre telefoanele refurbished</p>
        </div>

        <!-- Blog Content -->
        <div class="blog-content">

        <!-- Blog Sidebar -->
        <aside class="blog-sidebar">
                <!-- Search Widget -->
                <div class="sidebar-widget search-widget">
                    <form class="sidebar-search" method="get" action="<?php echo home_url(); ?>">
                        <input type="search" name="s" placeholder="Caută în blog..." value="<?php echo get_search_query(); ?>">
                        <input type="hidden" name="post_type" value="post">
                        <input type="hidden" name="search_source" value="blog">
                        <button type="submit">🔍</button>
                    </form>
                </div>

                <!-- Categories Widget -->
                <div class="sidebar-widget sidebar-categories">
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
                                        <span class="count"><?php echo $category->count; ?></span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>

                <!-- Recent Posts Widget -->
                <div class="sidebar-widget sidebar-recent-posts">
                    <h3 class="sidebar-widget-title" onclick="toggleAccordion(this)">Articole recente</h3>
                    <div class="sidebar-widget-content">
                        <ul>
                            <?php
                            $recent_posts = get_posts(array(
                                'numberposts' => 5,
                                'post_status' => 'publish'
                            ));
                            
                            foreach ($recent_posts as $post) :
                                setup_postdata($post);
                            ?>
                                <li>
                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                    <div class="post-date"><?php echo get_the_date('d.m.Y'); ?></div>
                                </li>
                            <?php
                            endforeach;
                            wp_reset_postdata();
                            ?>
                        </ul>
                    </div>
                </div>
            </aside>
            <!-- Blog Posts -->
            <div class="blog-posts">
                <?php
                // Query for blog posts
                $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
                $blog_query = new WP_Query(array(
                    'post_type' => 'post',
                    'posts_per_page' => 6,
                    'paged' => $paged,
                    'post_status' => 'publish'
                ));

                if ($blog_query->have_posts()) :
                    while ($blog_query->have_posts()) : $blog_query->the_post();
                        $post_id = get_the_ID();
                        $featured_image = get_the_post_thumbnail_url($post_id, 'large');
                        $categories = get_the_category();
                        $category_name = !empty($categories) ? $categories[0]->name : 'Blog';
                        $excerpt = get_the_excerpt();
                        if (empty($excerpt)) {
                            $excerpt = wp_trim_words(get_the_content(), 20, '...');
                        }
                ?>
                    <article class="blog-post-card">
                        <?php if ($featured_image) : ?>
                            <img src="<?php echo esc_url($featured_image); ?>" alt="<?php the_title_attribute(); ?>" class="blog-post-image">
                        <?php else : ?>
                            <div class="blog-post-image" style="background: linear-gradient(135deg, #66fa95, #5ae885); display: flex; align-items: center; justify-content: center; color: #2A322F; font-size: 3rem;">📱</div>
                        <?php endif; ?>
                        
                        <div class="blog-post-content">
                            <div class="blog-post-meta">
                                <div class="blog-post-date">
                                    <span>📅</span>
                                    <span><?php echo get_the_date('d.m.Y'); ?></span>
                                </div>
                                <div class="blog-post-category"><?php echo esc_html($category_name); ?></div>
                            </div>
                            
                            <h2 class="blog-post-title">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h2>
                            
                            <div class="blog-post-excerpt">
                                <?php echo wp_kses_post($excerpt); ?>
                            </div>
                            
                            <a href="<?php the_permalink(); ?>" class="blog-post-read-more">
                                Citește mai mult
                                <span>→</span>
                            </a>
                        </div>
                    </article>
                <?php
                    endwhile;
                    wp_reset_postdata();
                else :
                ?>
                    <div class="no-posts">
                        <h3>Nu există articole încă</h3>
                        <p>Încă nu am publicat articole pe blog. Revino în curând pentru conținut nou!</p>
                        <a href="<?php echo home_url(); ?>" class="btn-primary">Înapoi la pagina principală</a>
                    </div>
                <?php endif; ?>
            </div>

        </div>

        <!-- Pagination -->
        <?php if ($blog_query->max_num_pages > 1) : ?>
            <div class="blog-pagination">
                <?php
                echo paginate_links(array(
                    'total' => $blog_query->max_num_pages,
                    'current' => $paged,
                    'format' => '?paged=%#%',
                    'show_all' => false,
                    'type' => 'list',
                    'end_size' => 2,
                    'mid_size' => 1,
                    'prev_text' => '← Anterior',
                    'next_text' => 'Următor →',
                    'add_args' => false,
                    'add_fragment' => '',
                ));
                ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php get_footer(); ?>
