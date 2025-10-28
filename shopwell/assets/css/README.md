# CSS Organization Structure

This directory contains the refactored CSS files that were previously inline in PHP files.

## Directory Structure

```
assets/css/
├── components/          # Reusable UI components
├── layout/             # Layout-specific styles (header, footer, etc.)
├── pages/              # Page-specific styles
├── woocommerce/        # WooCommerce-specific styles
├── vendors/            # Third-party library styles
└── refactored-styles.css  # Main CSS file that imports all refactored styles
```

## File Organization

### WooCommerce Styles (`woocommerce/`)
- `filter-sidebar.css` - Filter sidebar positioning and layout
- `shop-layout.css` - Shop page layout and product grid
- `container-layout.css` - Container positioning and responsive layout
- `pagination.css` - Pagination positioning and layout
- `select2-styles.css` - Select2 dropdown customizations
- `sort-dropdown.css` - Sort dropdown styling

### Page Styles (`pages/`)
- `single-product.css` - Individual product page styles
- `single-post.css` - Individual blog post page styles
- `category.css` - Blog category page styles

### Layout Styles (`layout/`)
- `footer.css` - Footer styles and layout

## Usage

The main `refactored-styles.css` file imports all the individual CSS files. This file is enqueued in the PHP files that previously had inline CSS.

## Benefits of This Organization

1. **Maintainability** - CSS is now organized by functionality and purpose
2. **Reusability** - Components can be easily reused across different pages
3. **Performance** - CSS files can be cached by browsers
4. **Debugging** - Easier to locate and fix styling issues
5. **Collaboration** - Multiple developers can work on different CSS files without conflicts

## Migration Notes

- All inline CSS has been moved to external files
- PHP files now use `wp_enqueue_style()` to load CSS files
- Original functionality is preserved
- Responsive design is maintained
- All WooCommerce customizations are preserved
