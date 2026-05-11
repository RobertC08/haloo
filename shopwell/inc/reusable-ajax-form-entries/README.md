# Reusable AJAX form → CPT entries

Small PHP pattern (no Composer) for WordPress: front-end `fetch` to `admin-ajax.php`, nonce, sanitize, optional duplicate check, save one `private` post + meta, list in admin with extra columns.

## Copy to another project

1. Copy the folder `reusable-ajax-form-entries/` (only `class-reusable-ajax-form-entries.php` is required).
2. `require_once` the class from `functions.php` or your plugin main file.
3. Call `Reusable_Ajax_Form_Entries::register( $config )` once (see class docblock for keys).

## Minimal new form

```php
Reusable_Ajax_Form_Entries::register( array(
    'id'              => 'my_lead_form',
    'post_type'       => 'my_lead',
    'ajax_action'     => 'submit_my_lead', // same as JS FormData action
    'nonce_action'    => 'my_lead_nonce',  // same as wp_nonce_field()
    'meta_prefix'     => '_ml_',
    'success_message' => 'Thanks.',
    'post_type_args'  => array(
        'labels' => array(
            'name' => 'Leads',
            'menu_name' => 'Leads',
        ),
        'menu_icon' => 'dashicons-email',
    ),
    'title_builder' => function ( array $v ) {
        return $v['name'] ?? 'Lead';
    },
    'duplicate_check' => array(
        'field'   => 'email',
        'message' => 'Already subscribed.',
    ),
    'admin_columns' => array(
        'email' => 'Email',
        'phone' => 'Phone',
    ),
    'fields' => array(
        'name'  => array( 'type' => 'text', 'required' => true ),
        'email' => array( 'type' => 'email', 'required' => true ),
        'phone' => array( 'type' => 'text', 'required' => false ),
    ),
) );
```

JS must POST the same field names, plus `_wpnonce` (or set `nonce_field` in config).

## Extension hooks

With `filter_prefix` `rafe` and `id` `my_lead_form`:

- `rafe_sanitized_values_my_lead_form` — adjust `$values` after sanitize.
- `rafe_extra_meta_my_lead_form` — return `array( 'meta_suffix' => $value )` merged into post meta (suffix becomes `meta_prefix` + key).
- `rafe_after_save_my_lead_form` — `( $post_id, $values, $config )` e.g. send email.
- `rafe_entry_title_my_lead_form` — adjust post title string.

## Haloo concurs

Project-specific wiring: `inc/concurs-entries-bootstrap.php` (CPT `concurs_entry`, action `submit_concurs`) and `inc/concurs-seo.php` (titlu, meta, JSON-LD pe șablonul `page-concurs.php`).
