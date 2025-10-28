### Shopwell Theme – Page to Files Mapping

This document maps each major page/view to its primary PHP template(s), related template parts/includes, and the CSS/JS enqueued for that context.

### Global assets and loaders
- **Core styles**:
  - `assets/css/all.css` (global)
  - `style.css` / `style-rtl.css` (theme)
  - `assets/css/swiper.css` (registered/enqueued globally)
  - `assets/css/refactored-styles.css` (base for refactored pages)
  - `assets/css/layout/footer.css` (enqueued on all pages)
- **Core scripts**:
  - `assets/js/scripts.min.js` (or `scripts.js` depending on debug)
  - Plugins: `assets/js/plugins/headroom.min.js`, `respond.min.js`, `html5shiv.min.js`, `notify.min.js`, `swiper.min.js`
  - `comment-reply` (on singular with comments open)
- **Where enqueued**:
  - Global: `inc/frontend.php::enqueue_scripts()`
  - Refactored page-level CSS: `assets/css/load-refactored-styles.php::shopwell_enqueue_refactored_styles()` (hooked in `functions.php`)

### Homepage
- **Template**: `front-page.php` (Template Name: Homepage)
- **Template parts/includes**:
  - Uses `get_header()` / `get_footer()`
  - Shortcode: `[haloo_hero_slider]`
  - Woo products via `[products]` shortcode
  - Inline JS within `front-page.php` for testimonials and categories sliders
- **Styles**:
  - Global styles (see above)
  - `assets/css/pages/homepage.css` (from refactored loader on front page)
- **Scripts**:
  - Global JS (see above)
  - Swiper is globally enqueued and available

### Contact Page
- **Template**: `page-contact.php` (Template Name: Contact Page)
- **Template parts/includes**: `get_header()` / `get_footer()`
- **Styles**:
  - Global styles (see above)
  - `assets/css/pages/contact.css` (refactored loader detects page template)
- **Scripts**:
  - Global JS (see above)
  - Inline JS in `page-contact.php` for FAQ accordion and AJAX form
  - AJAX endpoint handled in `functions.php::handle_contact_form_submission()` via `admin-ajax.php`

### FAQ Page
- **Template**: `page-faq.php` (Template Name: FAQ)
- **Styles**:
  - Global styles
  - `assets/css/pages/faq.css`

### Blog Page (custom page template)
- **Template**: `page-blog.php` (Template Name: Blog)
- **Related blog templates**: `inc/blog/*` modules, theme blog templates `archive.php`, `single.php` (see below)
- **Styles**:
  - Global styles
  - `assets/css/pages/blog.css`

### About Page
- **Template**: `page-about.php` (Template Name: About Page)
- **Styles**:
  - Global styles
  - `assets/css/pages/about.css`

### Knowledge Base Page
- **Template**: `page-knowledge-base.php` (Template Name: Knowledge Base)
- **Related**: `inc/help-center.php` and `inc/help-center/*` for structure and templates
- **Styles**:
  - Global styles
  - `assets/css/pages/knowledge-base.css`

### Quiz Page
- **Template**: `page-quiz.php` (Template Name: Smartphone Quiz)
- **Styles**:
  - Global styles
  - `assets/css/pages/quiz.css`

### Generic Page
- **Template**: `page.php` (fallback for standard pages without a specific template)
- **Styles**:
  - Global styles
  - Page-specific refactored styles only if matched by loader’s conditions

### Homepage (alternative template)
- **Template**: `page-homepage.php` (Template Name: Homepage with Products)
- **Styles**:
  - Global styles
  - Likely uses `assets/css/pages/homepage.css` (if front page), else base + any in-template styles

### Blog Archive / Category / Tag / General Archive
- **Templates**:
  - `archive.php` (generic archive)
  - `category.php` (category archive)
  - `inc/blog/archive.php` (blog archive renderer)
- **Styles**:
  - Global styles
  - `assets/css/pages/category.css` (refactored loader for `is_category() || is_tag() || is_archive()`)

### Single Blog Post
- **Templates**:
  - `single-haloo.php` (forced via `functions.php::haloo_single_post_template`)
  - Related: `inc/blog/single.php`, `inc/blog/post.php`, `inc/blog/post-related.php`, `template-parts/post/*`
- **Styles**:
  - Global styles
  - `assets/css/pages/single-post.css` (refactored loader for single posts)
- **Scripts**:
  - `comment-reply` when applicable

### Search Results
- **Template**: `search.php`
- **Styles**:
  - Global styles
  - `assets/css/pages/search.css` (refactored loader for `is_search()`)

### 404 Page
- **Templates**:
  - `404.php`
  - Alternate/custom: `inc/page-404.php` may provide helper functions/content
- **Styles**:
  - Global styles

### Taxonomy – Product Category Landing
- **Template**: `taxonomy-product-cat.php` (custom taxonomy template)
- **Woo context**: Also handled by Woo archive template overrides (see Shop/Archive below)
- **Styles**:
  - Global styles
  - Woo styles (see Shop & Catalog)

### WooCommerce – Global
- **Core Woo enqueue**:
  - `inc/woocommerce/general.php` enqueues Woo base CSS (theme `woocommerce.css` or `woocommerce-rtl.css`) and scripts like `zoom`, `wc-cart-fragments`
  - `inc/woocommerce/product.php`, `inc/woocommerce/catalog.php`, `inc/woocommerce/single-product.php`, `inc/woocommerce/quickview.php` enqueue contextual assets
- **Refactored Woo CSS** (from loader):
  - `assets/css/woocommerce/filter-sidebar.css` (for shop/category/tag/tax-product pages)
  - Additional Woo UI CSS:
    - `assets/css/woocommerce/container-layout.css`
    - `assets/css/woocommerce/pagination.css`
    - `assets/css/woocommerce/shop-layout.css`
    - `assets/css/woocommerce/select2-styles.css`
    - `assets/css/woocommerce/sort-dropdown.css`
- **Woo scripts**:
  - `assets/js/woocommerce/product-catalog.js`
  - `assets/js/woocommerce/single-product.js`
  - Plugins as used: `jquery.magnific-popup.js`, `countdown.js`, `threesixty.min.js`, `select2.min.js`, `sticky-kit.min.js`

### WooCommerce – Shop / Product Archives
- **Templates**:
  - `woocommerce/archive-product.php` (main shop and archives override)
  - Loop parts: `woocommerce/loop/*.php`
  - Global pieces: `woocommerce/global/quantity-input.php`
- **Theme helper modules**:
  - `inc/woocommerce/catalog.php` (toolbar, filters, etc.)
  - Filter sidebar: moved via `functions.php` hooks to `woocommerce_before_shop_loop` and uses `template-parts/panels/filter-sidebar.php`
- **Styles**:
  - Global styles
  - Woo base styles (`woocommerce.css` / `woocommerce-rtl.css`)
  - Refactored: `assets/css/woocommerce/filter-sidebar.css`
  - Additional: `assets/css/woocommerce/{shop-layout.css, container-layout.css, pagination.css, select2-styles.css, sort-dropdown.css}`
- **Scripts**:
  - `assets/js/woocommerce/product-catalog.js`
  - Select2 (`inc/woocommerce/catalog.php`), Sticky Kit, others per hooks

### WooCommerce – Single Product
- **Templates**:
  - `woocommerce/single-product/*.php` (image, thumbnails, meta, tabs, rating, review, side-product)
  - Add-to-cart templates: `woocommerce/single-product/add-to-cart/*.php`
  - Reviews: `woocommerce/single-product-reviews.php`
- **Theme helper modules**: `inc/woocommerce/single-product.php`
- **Styles**:
  - Global styles
  - `assets/css/pages/single-product.css` (refactored loader on is_product())
  - Woo base styles
- **Scripts**:
  - `assets/js/woocommerce/single-product.js`
  - Magnific Popup CSS/JS, Countdown, ThreeSixty (conditionally from `inc/woocommerce/single-product.php`)

### WooCommerce – Cart / Mini-cart
- **Templates**:
  - `woocommerce/cart/cart.php`, `cart-empty.php`, `cart-shipping.php`, `mini-cart.php`
- **Styles**:
  - Global + Woo base styles; additional cart styling may be in `assets/css/woocommerce/*` (layout/pagination)
- **Scripts**:
  - Woo standard scripts + any theme JS via global enqueue

### WooCommerce – Checkout
- **Templates**:
  - Core Woo templates (not overridden here), with theme styling
- **Styles**:
  - Global + Woo base styles; layout via `assets/css/woocommerce/*` where relevant

### WooCommerce – My Account
- **Templates**:
  - `woocommerce/myaccount/*.php` (dashboard, login, lost password, my-address, orders)
- **Styles**:
  - Global + Woo base styles; may inherit from shop layout CSS

### WooCommerce – Order Tracking
- **Template**: `woocommerce/order/form-tracking.php`
- **Styles**:
  - Global + Woo base styles

### Wishlist
- **Templates**:
  - `woocommerce/wishlist/*.php`
- **Styles**:
  - Global + Woo base styles

### Modals, Template Parts and Shared UI
- **Template parts**: `template-parts/**` (buttons, header, modals, panels, navigation-bar, etc.) are included across pages via `get_template_part()` from helpers like `inc/header/*`, `inc/footer/manager.php`, Woo modules, or templates directly.
- **Header/Footer/Sidebar**:
  - Header management: `inc/header/manager.php`, `template-parts/header/*`
  - Footer widgets and assets: `inc/footer/manager.php`
  - Sidebar: `sidebar.php` and dynamic areas per page

### Search AJAX & Autocomplete
- **Modules**:
  - `inc/search-ajax.php` (search endpoint)
  - `inc/product-search-autocomplete.php` (shortcode/assets; enqueues jQuery)
- **Styles/JS**: inherited global + Woo where relevant

### Vendors and Integrations
- **Assets**:
  - `assets/css/vendors/*.css` (AWS Search, Dokan, Fibo Search, Marketking, WCFM) enqueued in corresponding `inc/vendors/*.php`
- **Woo-related vendor modules**:
  - See `inc/vendors/*.php`

### Blog System Helpers
- **Modules**: `inc/blog/*` (headers, navigation, related, trending)
- **Customization hooks**: defined in `functions.php` (excerpt length/more, query, body classes, social sharing)

### Special Templates
- **`single-shopwell_builder.php` / `single-shopwell_popup.php`**: custom single templates for internal CPTs
- **`homepage.php`**: legacy/alternate homepage template (not the active `front-page.php`)

### Summary – Page to CSS quick index (refactored loader)
- Front page → `assets/css/pages/homepage.css`
- Contact → `assets/css/pages/contact.css`
- FAQ → `assets/css/pages/faq.css`
- Blog page template → `assets/css/pages/blog.css`
- About → `assets/css/pages/about.css`
- Knowledge Base → `assets/css/pages/knowledge-base.css`
- Quiz → `assets/css/pages/quiz.css`
- Category/Tag/Archive → `assets/css/pages/category.css`
- Single Post → `assets/css/pages/single-post.css`
- Single Product → `assets/css/pages/single-product.css`
- Search → `assets/css/pages/search.css`
- Shop/Category/Tag/Tax Product (Woo) → `assets/css/woocommerce/filter-sidebar.css` (+ other Woo CSS)

If you add new page templates, register their CSS in `assets/css/load-refactored-styles.php` following the existing pattern.


