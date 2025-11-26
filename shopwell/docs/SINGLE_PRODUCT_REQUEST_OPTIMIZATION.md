# 🚨 Optimizare Request-uri - Single Product Page

**Data:** 2025-01-06  
**Problema:** Prea multe request-uri pe pagina single product care încetinesc încărcarea

---

## 📊 Analiza Request-urilor

Din waterfall chart observăm:

### Request-uri Problemice:

1. **hCaptcha Scripts** (1.79s, 956ms) - 🔴 CRITIC
   - Blob URLs către `newassets.hcaptcha.com`
   - 2 request-uri cu status `(pending)`
   - Impact: Blochează rendering-ul paginii

2. **Google Pay Request** (canceled) - 🟡 MEDIUM
   - Request către `GooglePay.html` care este anulat
   - Se încarcă dar nu e folosit

3. **Multiple XHR/Fetch Requests** - 🟡 MEDIUM
   - Multe request-uri mici (`b`, `6`) către hcaptcha
   - Request-uri de preflight pentru CORS
   - Impact: Overhead de network

---

## 🛠️ Soluții Recomandate

### 1. Lazy Load Payment Gateway Scripts
**Problema:** Scripturile de payment (Stripe, PayPal) se încarcă imediat, chiar dacă utilizatorul nu ajunge la checkout.

**Soluție:**
- Încarcă scripturile de payment doar când utilizatorul apasă "Add to Cart"
- Sau doar când ajunge la checkout page
- Folosește `defer` sau `async` pentru scripturile externe

### 2. Defer hCaptcha Loading
**Problema:** hCaptcha se încarcă imediat și blochează rendering-ul.

**Soluție:**
- Încarcă hCaptcha doar când formularul de contact/review este vizibil
- Folosește Intersection Observer pentru lazy loading
- Defer scripturile hCaptcha

### 3. Optimizează Google Pay
**Problema:** Google Pay se încarcă dar request-ul este anulat.

**Soluție:**
- Verifică dacă Google Pay este activ folosit
- Dacă nu, dezactivează complet încărcarea
- Sau încarcă doar când utilizatorul ajunge la checkout

### 4. Reduce Preflight Requests
**Problema:** Multe request-uri OPTIONS (preflight) pentru CORS.

**Soluție:**
- Configurează server-ul pentru CORS headers corecte
- Reduce numărul de request-uri către domenii externe
- Folosește proxy pentru request-uri externe dacă e posibil

---

## 📝 Implementare

### Optimizare Payment Gateway Scripts

```php
/**
 * PERFORMANCE FIX: Lazy load payment gateway scripts
 * Only load when user interacts with checkout
 */
function shopwell_lazy_load_payment_scripts() {
    // Only on single product pages
    if ( ! is_product() ) {
        return;
    }
    
    // Defer Stripe payment request button scripts
    add_filter( 'script_loader_tag', function( $tag, $handle ) {
        if ( strpos( $handle, 'stripe' ) !== false || 
             strpos( $handle, 'payment-request-button' ) !== false ||
             strpos( $handle, 'google-pay' ) !== false ) {
            // Replace with async/defer
            $tag = str_replace( ' src', ' defer src', $tag );
        }
        return $tag;
    }, 10, 2 );
}
add_action( 'wp', 'shopwell_lazy_load_payment_scripts' );
```

### Optimizare hCaptcha

```php
/**
 * PERFORMANCE FIX: Defer hCaptcha loading
 * Load only when form is visible
 */
function shopwell_defer_hcaptcha() {
    if ( ! is_product() ) {
        return;
    }
    
    // Defer hCaptcha scripts
    add_filter( 'script_loader_tag', function( $tag, $handle ) {
        if ( strpos( $handle, 'hcaptcha' ) !== false || 
             strpos( $tag, 'hcaptcha.com' ) !== false ) {
            $tag = str_replace( ' src', ' defer src', $tag );
            // Add data attribute for lazy loading
            $tag = str_replace( '<script ', '<script data-lazy="true" ', $tag );
        }
        return $tag;
    }, 10, 2 );
}
add_action( 'wp', 'shopwell_defer_hcaptcha' );
```

### Disable Google Pay dacă nu e folosit

```php
/**
 * PERFORMANCE FIX: Disable Google Pay if not actively used
 */
function shopwell_disable_unused_payment_methods() {
    // Check if Google Pay is actually being used
    $stripe_settings = get_option( 'woocommerce_stripe_settings' );
    
    if ( isset( $stripe_settings['payment_request'] ) && 
         $stripe_settings['payment_request'] === 'no' ) {
        // Disable Google Pay scripts
        add_filter( 'woocommerce_gateway_stripe_payment_request_button_locale', '__return_false' );
    }
}
add_action( 'init', 'shopwell_disable_unused_payment_methods' );
```

---

## 📊 Impact Estimat

| Optimizare | Request-uri Eliminate | Timp Economit |
|------------|----------------------|---------------|
| Lazy Load Payment Scripts | 3-5 request-uri | 500ms-1s |
| Defer hCaptcha | 2-4 request-uri | 1-2s |
| Disable Google Pay | 1-2 request-uri | 200-500ms |
| **TOTAL** | **6-11 request-uri** | **1.7-3.5s** |

---

## ⚠️ Note

- Testează că toate funcționalitățile de payment funcționează corect după optimizări
- Verifică că hCaptcha se încarcă când e necesar
- Monitorizează erorile în consolă după implementare

