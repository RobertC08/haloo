<?php
/**
 * Template Name: Knowledge Base
 * 
 * Custom template for Knowledge Base page
 */

get_header();
?>

<!-- CSS moved to external file: assets/css/pages/knowledge-base.css -->

<!-- Hero Section -->
<div class="kb-hero">
    <h1>Cum te putem ajuta?</h1>
    <p class="kb-hero-subtitle">Caută în baza de cunoștințe sau contactează echipa noastră de suport pentru asistență</p>
    
    <!-- Search Form -->
    <div class="kb-search-wrapper">
    <form id="kb-search-form" class="kb-search-container" method="get" action="<?php echo home_url(); ?>">
        <input 
            type="search" 
            name="s" 
            class="kb-search-input" 
            placeholder="Caută în baza de cunoștințe" 
            value="<?php echo get_search_query(); ?>"
        >
        <select name="shopwell_help_cat" class="kb-category-select">
            <option value="">Toate categoriile</option>
            <option value="account">Account</option>
            <option value="fees-billing">Fees & billing</option>
            <option value="returns-refunds">Returns & Refunds</option>
            <option value="shipping-tracking">Shipping & Tracking</option>
            <option value="orders-purchases">Orders & Purchases</option>
            <option value="other">Other</option>
        </select>
        <input type="hidden" name="post_type" value="sw_help_article">
    </form>
    <button type="submit" form="kb-search-form" class="kb-search-button">🔍</button>
    </div>
    
    <!-- Popular Sections -->
    <div class="kb-popular-sections">
        <span>Secțiuni populare:</span>
        <a href="https://haloo.lemon.thisisfruit.com/help-article/shop-with-an-expert/">Cumpără cu un expert</a>,
        <a href="https://haloo.lemon.thisisfruit.com/help-article/help-with-password/">Ajutor cu parola</a>,
        <a href="https://haloo.lemon.thisisfruit.com/help-article/tracking-your-item/">Urmărește produsul tău</a>
    </div>
</div>

<!-- Categories Section -->
<div class="kb-content">
    <div class="kb-categories-grid">
        
        <!-- Account Category -->
        <div class="kb-category-card">
            <div class="kb-category-icon">👤</div>
            <h2 class="kb-category-title">Account</h2>
            <ul class="kb-category-links">
                <li><a href="https://haloo.lemon.thisisfruit.com/help-article/help-with-password/">Ajutor cu parola</a></li>
                <li><a href="https://haloo.lemon.thisisfruit.com/help-article/account-settings/">Setări cont</a></li>
                <li><a href="https://haloo.lemon.thisisfruit.com/help-article/manage-your-account/">Gestionează contul tău</a></li>
                <li><a href="https://haloo.lemon.thisisfruit.com/help-article/manage-your-rewards/">Gestionează recompensele tale</a></li>
                <li><a href="https://haloo.lemon.thisisfruit.com/help-article/manage-payment-methods/">Gestionează metodele de plată</a></li>
            </ul>
            <a href="https://haloo.lemon.thisisfruit.com/help_category/account/" class="kb-view-more">Vezi mai mult</a>
        </div>
        
        <!-- Fees & Billing Category -->
        <div class="kb-category-card">
            <div class="kb-category-icon">💳</div>
            <h2 class="kb-category-title">Fees & billing</h2>
            <ul class="kb-category-links">
                <li><a href="https://haloo.lemon.thisisfruit.com/help-article/getting-started/">Început</a></li>
                <li><a href="https://haloo.lemon.thisisfruit.com/help-article/fees-and-reporting/">Taxe și Raportare</a></li>
                <li><a href="https://haloo.lemon.thisisfruit.com/help-article/getting-paid/">Primește plata</a></li>
                <li><a href="https://haloo.lemon.thisisfruit.com/help-article/refunds-and-disputes/">Rambursări și Dispute</a></li>
            </ul>
            <a href="https://haloo.lemon.thisisfruit.com/help_category/fees-billing/" class="kb-view-more">Vezi mai mult</a>
        </div>
        
        <!-- Returns & Refunds Category -->
        <div class="kb-category-card">
            <div class="kb-category-icon">🕐</div>
            <h2 class="kb-category-title">Returns & Refunds</h2>
            <ul class="kb-category-links">
                <li><a href="https://haloo.lemon.thisisfruit.com/help-article/how-will-i-be-refunded/">Cum voi fi rambursat?</a></li>
                <li><a href="https://haloo.lemon.thisisfruit.com/help-article/ive-received-a-faulty-damaged-item/">Am primit un produs defect/deteriorat</a></li>
                <li><a href="https://haloo.lemon.thisisfruit.com/help-article/how-do-i-cancel-an-order/">Cum anulez o comandă?</a></li>
                <li><a href="https://haloo.lemon.thisisfruit.com/help-article/what-if-my-order-is-damaged/">Ce se întâmplă dacă comanda mea este deteriorată?</a></li>
                <li><a href="https://haloo.lemon.thisisfruit.com/help-article/i-would-like-to-return-my-order/">Aș dori să returnez comanda</a></li>
            </ul>
            <a href="https://haloo.lemon.thisisfruit.com/help_category/returns-refunds/" class="kb-view-more">Vezi mai mult</a>
        </div>
        
        <!-- Shipping & Tracking Category -->
        <div class="kb-category-card">
            <div class="kb-category-icon">✈️</div>
            <h2 class="kb-category-title">Shipping & Tracking</h2>
            <ul class="kb-category-links">
                <li><a href="https://haloo.lemon.thisisfruit.com/help-article/tracking-your-item/">Urmărește produsul tău</a></li>
                <li><a href="https://haloo.lemon.thisisfruit.com/help-article/shipping-rates-for-buyers/">Tarife de transport pentru cumpărători</a></li>
                <li><a href="https://haloo.lemon.thisisfruit.com/help-article/delivery-date-options-for-buyers/">Opțiuni de dată de livrare pentru cumpărători</a></li>
                <li><a href="https://haloo.lemon.thisisfruit.com/help-article/saving-through-combined-shipping/">Economisește prin transport combinat</a></li>
                <li><a href="https://haloo.lemon.thisisfruit.com/help-article/buying-with-local-pickup/">Cumpără cu ridicare locală</a></li>
            </ul>
            <a href="https://haloo.lemon.thisisfruit.com/help_category/shipping-tracking/" class="kb-view-more">Vezi mai mult</a>
        </div>
        
        <!-- Orders & Purchases Category -->
        <div class="kb-category-card">
            <div class="kb-category-icon">🛍️</div>
            <h2 class="kb-category-title">Orders & Purchases</h2>
            <ul class="kb-category-links">
                <li><a href="https://haloo.lemon.thisisfruit.com/help-article/shop-with-an-expert/">Cumpără cu un expert</a></li>
                <li><a href="https://haloo.lemon.thisisfruit.com/help-article/in-store-consultation/">Consultație în magazin</a></li>
                <li><a href="https://haloo.lemon.thisisfruit.com/help-article/track-a-package/">Urmărește un pachet</a></li>
                <li><a href="https://haloo.lemon.thisisfruit.com/help-article/cancel-an-order/">Anulează o comandă</a></li>
                <li><a href="https://haloo.lemon.thisisfruit.com/help-article/store-pickup/">Ridicare din magazin</a></li>
            </ul>
            <a href="https://haloo.lemon.thisisfruit.com/help_category/orders-purchases/" class="kb-view-more">Vezi mai mult</a>
        </div>
        
        <!-- Other Category -->
        <div class="kb-category-card">
            <div class="kb-category-icon">💬</div>
            <h2 class="kb-category-title">Other</h2>
            <ul class="kb-category-links">
                <li><a href="https://haloo.lemon.thisisfruit.com/help-article/in-store-consultation-other/">Consultație în magazin</a></li>
                <li><a href="https://haloo.lemon.thisisfruit.com/help-article/getting-receipt-copies/">Obține copii ale chitanței</a></li>
                <li><a href="https://haloo.lemon.thisisfruit.com/help-article/gift-cards/">Carduri cadou</a></li>
                <li><a href="https://haloo.lemon.thisisfruit.com/help-article/trade-in/">Schimb la tranzacție</a></li>
            </ul>
            <a href="https://haloo.lemon.thisisfruit.com/help_category/other/" class="kb-view-more">Vezi mai mult</a>
        </div>
        
    </div>
    
    <!-- CTA Section -->
    <div class="kb-cta-section">
        <p class="kb-cta-subtitle">Ai nevoie de ajutor?</p>
        <h2 class="kb-cta-title">Găsește răspunsuri la<br>întrebările frecvente sau contactează<br>echipa noastră de suport pentru asistență.</h2>
        <a href="<?php echo home_url('/contact'); ?>" class="kb-cta-button">Contactează-ne</a>
    </div>
</div>

<?php get_footer(); ?>

