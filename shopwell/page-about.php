<?php
/**
 * Template Name: About Page
 * 
 * About Page Template for Haloo Refurbished Phones
 *
 * @package Shopwell
 */

get_header();
?>

<!-- CSS moved to external file: assets/css/pages/about.css -->

<div class="site-content-container">
    <div id="primary" class="content-area">
        <main id="main" class="site-main">
            
            <!-- Hero Section -->
            <section class="about-hero">
                <div class="about-container">
                    <div class="about-header">
                        <h1 class="about-title">Despre Haloo</h1>
                        <p class="about-subtitle">Povestea noastră despre revoluționarea pieței de telefoane refurbished din România</p>
                        
                        <!-- Brand Logos Slider -->
                        <div class="brand-slider">
                            <div class="brand-slider-track">
                                <div class="brand-logo">
                                    <span>Samsung</span>
                                </div>
                                <div class="brand-logo">
                                    <span>Apple</span>
                                </div>
                                <div class="brand-logo">
                                    <span>Huawei</span>
                                </div>
                                <div class="brand-logo">
                                    <span>Xiaomi</span>
                                </div>
                                <div class="brand-logo">
                                    <span>OnePlus</span>
                                </div>
                                <div class="brand-logo">
                                    <span>Google</span>
                                </div>
                                <div class="brand-logo">
                                    <span>Motorola</span>
                                </div>
                                <div class="brand-logo">
                                    <span>LG</span>
                                </div>
                                <!-- Duplicate for seamless loop -->
                                <div class="brand-logo">
                                    <span>Samsung</span>
                                </div>
                                <div class="brand-logo">
                                    <span>Apple</span>
                                </div>
                                <div class="brand-logo">
                                    <span>Huawei</span>
                                </div>
                                <div class="brand-logo">
                                    <span>Xiaomi</span>
                                </div>
                                <div class="brand-logo">
                                    <span>OnePlus</span>
                                </div>
                                <div class="brand-logo">
                                    <span>Google</span>
                                </div>
                                <div class="brand-logo">
                                    <span>Motorola</span>
                                </div>
                                <div class="brand-logo">
                                    <span>LG</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Our Story Section -->
            <section class="story-section">
                <div class="about-container">
                    <div class="story-content">
                        <div class="story-text">
                            <h2 class="section-title">Povestea noastră</h2>
                            <p class="story-intro">Haloo a fost înființată în 2024 cu o viziune clară: să democratizeze accesul la tehnologie de calitate prin telefoane refurbished de încredere. Am pornit cu convingerea că fiecare român merită să aibă acces la tehnologie de ultimă generație la prețuri accesibile, fără să compromită calitatea sau să afecteze mediul înconjurător.</p>
                            
                            <p class="story-second">În doar câteva luni de la lansare, am reușit să construim o rețea solidă de furnizori și parteneri de încredere, să dezvoltăm un proces riguros de refurbishing și să câștigăm încrederea primilor noștri clienți. Viziunea noastră este să devenim principala destinație pentru telefoane refurbished din România, cunoscuți pentru transparența, calitatea produselor și serviciilor noastre excepționale.</p>
                            
                            <div class="story-cta">
                                <a href="<?php echo home_url('/shop'); ?>" class="btn-primary">Descoperă produsele noastre</a>
                                <a href="<?php echo home_url('/contact'); ?>" class="btn-secondary">Contactează-ne</a>
                            </div>
                        </div>
                        
                        <div class="story-image">
                            <img src="https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=600&h=400&fit=crop" alt="Telefoane refurbished Haloo" class="phones-showcase-image">
                        </div>
                    </div>
                </div>
            </section>

            <!-- Mission & Values Section -->
            <section class="mission-section">
                <div class="about-container">
                    <div class="mission-grid">
                        <div class="mission-card">
                            <div class="mission-icon">
                                <i class="fas fa-bullseye"></i>
                            </div>
                            <h3>Misiunea noastră</h3>
                            <p>Să oferim telefoane refurbished de calitate superioară la prețuri accesibile, contribuind la o tehnologie mai sustenabilă și accesibilă pentru toți românii.</p>
                        </div>
                        
                        <div class="mission-card">
                            <div class="mission-icon">
                                <i class="fas fa-eye"></i>
                            </div>
                            <h3>Viziunea noastră</h3>
                            <p>Să devenim principala destinație pentru telefoane refurbished din România, cunoscuți pentru calitatea produselor și serviciilor noastre excepționale.</p>
                        </div>
                        
                        <div class="mission-card">
                            <div class="mission-icon">
                                <i class="fas fa-heart"></i>
                            </div>
                            <h3>Valorile noastre</h3>
                            <p>Transparență, calitate, sustenabilitate și încredere - acestea sunt valorile care ne ghidează în fiecare zi și în fiecare decizie pe care o luăm.</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Why Choose Us Section -->
            <section class="why-choose-section">
                <div class="about-container">
                    <h2 class="section-title">De ce să ne alegi pe noi?</h2>
                    
                    <div class="features-grid">
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <h3>Garanție de 2 ani</h3>
                            <p>Toate telefoanele noastre vin cu garanție extinsă de 2 ani, cea mai lungă din piață pentru produse refurbished.</p>
                        </div>
                        
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="fas fa-tools"></i>
                            </div>
                            <h3>Proces de refurbishing riguros</h3>
                            <p>Fiecare telefon trece prin 50+ de teste și verificări pentru a ne asigura că funcționează perfect.</p>
                        </div>
                        
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="fas fa-leaf"></i>
                            </div>
                            <h3>Sustenabilitate</h3>
                            <p>Contribuim la protejarea mediului prin reutilizarea telefoanelor și reducerea deșeurilor electronice.</p>
                        </div>
                        
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="fas fa-euro-sign"></i>
                            </div>
                            <h3>Prețuri accesibile</h3>
                            <p>Oferim telefoane de calitate superioară la prețuri cu 30-50% mai mici decât cele noi.</p>
                        </div>
                        
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="fas fa-truck"></i>
                            </div>
                            <h3>Livrare rapidă</h3>
                            <p>Livrăm în 1-2 zile în București și 2-3 zile în restul țării, cu ambalare sigură și profesională.</p>
                        </div>
                        
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="fas fa-headset"></i>
                            </div>
                            <h3>Suport tehnic dedicat</h3>
                            <p>Echipa noastră de suport tehnic este disponibilă pentru a te ajuta cu orice întrebare sau problemă.</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Featured Image Section -->
            <section class="stats-section">
                <div class="about-container">
                    <div class="featured-image-container">
                        <img src="https://haloo.ro/wp-content/uploads/2025/10/Smartphone-ER-iPhone-15-removebg-preview.png" alt="iPhone 15 Refurbished" class="featured-image">
                    </div>
                </div>
            </section>

            <!-- CTA Section -->
            <section class="cta-section">
                <div class="about-container">
                    <div class="cta-content">
                        <h2>Gata să descoperi diferența Haloo?</h2>
                        <p>Explorează colecția noastră de telefoane refurbished de calitate superioară și găsește telefonul perfect pentru tine.</p>
                        <div class="cta-buttons">
                            <a href="<?php echo home_url('/shop'); ?>" class="btn-primary">Vezi produsele</a>
                            <a href="<?php echo home_url('/contact'); ?>" class="btn-secondary">Contactează-ne</a>
                        </div>
                    </div>
                </div>
            </section>

        </main>
    </div>
</div>

<script>
// Brand slider animation
document.addEventListener('DOMContentLoaded', function() {
    const track = document.querySelector('.brand-slider-track');
    if (track) {
        // Pause animation on hover
        track.addEventListener('mouseenter', function() {
            track.style.animationPlayState = 'paused';
        });
        
        track.addEventListener('mouseleave', function() {
            track.style.animationPlayState = 'running';
        });
    }
});
</script>

<?php
get_footer();
?>
