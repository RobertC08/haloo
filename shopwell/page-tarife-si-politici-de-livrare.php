<?php
/**
 * Template Name: Tarife și Politici de Livrare
 * 
 * Shipping Rates and Policies Page Template
 *
 * @package Shopwell
 */

get_header();
?>

<!-- CSS moved to external file: assets/css/pages/tarife-si-politici-de-livrare.css -->

<div class="site-content-container">
    <div id="primary" class="content-area">
        <main id="main" class="site-main">
            
            <!-- Hero Section -->
            <section class="shipping-hero">
                <div class="shipping-container">
                    <div class="shipping-header">
                        <h1 class="shipping-title">Tarife și Politici de Livrare</h1>
                        <p class="shipping-subtitle">Ultima actualizare: <?php echo date('d.m.Y'); ?></p>
                    </div>
                </div>
            </section>

            <!-- Content -->
            <div class="shipping-content">
                <div class="shipping-container">
                    
                    <!-- Introduction Section -->
                    <section class="shipping-section">
                        <h2 class="shipping-section-title">1. Introducere</h2>
                        <div class="shipping-section-content">
                            <p>La Haloo, ne străduim să livrăm produsele dvs. rapid și sigur. Această pagină conține toate informațiile despre tarifele de livrare, termenele de livrare și politicile noastre de transport.</p>
                            <p>Toate comenzile sunt procesate și expediate în cel mai scurt timp posibil, iar veți primi notificări prin email la fiecare etapă a procesului de livrare.</p>
                        </div>
                    </section>

                    <!-- Shipping Rates Section -->
                    <section class="shipping-section">
                        <h2 class="shipping-section-title">2. Tarife de Livrare</h2>
                        <div class="shipping-section-content">
                            <h3 class="shipping-subsection-title">2.1. Livrare Standard</h3>
                            <ul class="shipping-list">
                                <li><strong>Livrare gratuită</strong> pentru comenzi peste 500 RON</li>
                                <li><strong>20 RON</strong> pentru comenzi sub 500 RON</li>
                                <li><strong>Termen de livrare:</strong> 2-3 zile lucrătoare în orașele mari, 3-5 zile lucrătoare în restul țării</li>
                            </ul>

                            <h3 class="shipping-subsection-title">2.2. Livrare Express (doar București)</h3>
                            <ul class="shipping-list">
                                <li><strong>35 RON</strong> - Livrare express</li>
                                <li><strong>Termen de livrare:</strong> În aceeași zi sau a doua zi (pentru comenzi plasate până la ora 14:00)</li>
                                <li><strong>Disponibil:</strong> Doar în București și împrejurimi</li>
                            </ul>

                            <h3 class="shipping-subsection-title">2.3. Ramburs la Livrare</h3>
                            <ul class="shipping-list">
                                <li><strong>15 RON</strong> - Taxa suplimentară pentru ramburs</li>
                                <li><strong>Notă:</strong> Taxa de ramburs se adaugă la costul de livrare standard sau express</li>
                            </ul>

                            <h3 class="shipping-subsection-title">2.4. Calcularea Costurilor</h3>
                            <p>Costurile de livrare sunt calculate automat în coșul de cumpărături în funcție de:</p>
                            <ul class="shipping-list">
                                <li>Valoarea comenzii</li>
                                <li>Adresa de livrare</li>
                                <li>Metoda de livrare selectată (standard sau express)</li>
                                <li>Metoda de plată selectată (ramburs sau plată online)</li>
                            </ul>
                        </div>
                    </section>

                    <!-- Delivery Terms Section -->
                    <section class="shipping-section">
                        <h2 class="shipping-section-title">3. Termene de Livrare</h2>
                        <div class="shipping-section-content">
                            <h3 class="shipping-subsection-title">3.1. Procesarea Comenzilor</h3>
                            <p>Toate comenzile sunt procesate în termen de <strong>24 de ore</strong> de la confirmarea plății (pentru plățile online) sau de la confirmarea comenzii (pentru ramburs).</p>
                            <p>În perioadele de vârf (sărbători, promoții), procesarea poate dura până la 48 de ore.</p>

                            <h3 class="shipping-subsection-title">3.2. Termene de Livrare Standard</h3>
                            <ul class="shipping-list">
                                <li><strong>București și orașele mari:</strong> 2-3 zile lucrătoare</li>
                                <li><strong>Restul țării:</strong> 3-5 zile lucrătoare</li>
                                <li><strong>Zonele rurale:</strong> 5-7 zile lucrătoare</li>
                            </ul>
                            <p><strong>Notă:</strong> Termenele sunt estimate și pot varia în funcție de condițiile meteorologice, sărbători legale sau alte circumstanțe neprevăzute.</p>

                            <h3 class="shipping-subsection-title">3.3. Livrare Express</h3>
                            <p>Livrarea express este disponibilă doar în București și împrejurimi:</p>
                            <ul class="shipping-list">
                                <li><strong>Comandă plasată până la ora 14:00:</strong> Livrare în aceeași zi sau a doua zi</li>
                                <li><strong>Comandă plasată după ora 14:00:</strong> Livrare a doua zi</li>
                            </ul>

                            <h3 class="shipping-subsection-title">3.4. Zile Lucrătoare</h3>
                            <p>Zilele lucrătoare sunt de luni până vineri, excluzând sărbătorile legale. Comenzile plasate în weekend vor fi procesate de luni.</p>
                        </div>
                    </section>

                    <!-- Shipping Areas Section -->
                    <section class="shipping-section">
                        <h2 class="shipping-section-title">4. Zone de Livrare</h2>
                        <div class="shipping-section-content">
                            <h3 class="shipping-subsection-title">4.1. Livrare în România</h3>
                            <p>Livrăm în <strong>toată România</strong> prin serviciile de curierat rapid. Folosim următorii parteneri de transport:</p>
                            <ul class="shipping-list">
                                <li>FAN Courier</li>
                                <li>DPD Romania</li>
                                <li>GLS Romania</li>
                            </ul>

                            <h3 class="shipping-subsection-title">4.2. Livrare Internațională</h3>
                            <p>Momentan nu livrăm în afara României, dar lucrăm la extinderea serviciilor noastre. Vă vom anunța când acest serviciu devine disponibil.</p>

                            <h3 class="shipping-subsection-title">4.3. Adrese de Livrare</h3>
                            <p>Puteți alege să livrăm la:</p>
                            <ul class="shipping-list">
                                <li>Adresa dvs. de domiciliu</li>
                                <li>Adresa dvs. de birou</li>
                                <li>Un punct de ridicare partener (unde este disponibil)</li>
                            </ul>
                        </div>
                    </section>

                    <!-- Tracking Section -->
                    <section class="shipping-section">
                        <h2 class="shipping-section-title">5. Urmărirea Coletului</h2>
                        <div class="shipping-section-content">
                            <h3 class="shipping-subsection-title">5.1. Număr de Tracking</h3>
                            <p>După expedierea comenzii, veți primi un email cu:</p>
                            <ul class="shipping-list">
                                <li>Numărul AWB (Air Waybill)</li>
                                <li>Link direct către sistemul de tracking al curierului</li>
                                <li>Informații despre statusul comenzii</li>
                            </ul>

                            <h3 class="shipping-subsection-title">5.2. Accesarea Informațiilor de Tracking</h3>
                            <p>Puteți accesa informațiile de tracking în mai multe moduri:</p>
                            <ul class="shipping-list">
                                <li>Prin email-ul de confirmare</li>
                                <li>Din secțiunea "Comenzile Mele" din contul dvs.</li>
                                <li>Direct pe site-ul curierului folosind numărul AWB</li>
                            </ul>

                            <h3 class="shipping-subsection-title">5.3. Actualizări în Timp Real</h3>
                            <p>Sistemul de tracking vă oferă actualizări în timp real despre locația coletului dvs., inclusiv:</p>
                            <ul class="shipping-list">
                                <li>Confirmarea expedierii</li>
                                <li>Statusul în tranzit</li>
                                <li>Confirmarea livrării</li>
                            </ul>
                        </div>
                    </section>

                    <!-- Delivery Issues Section -->
                    <section class="shipping-section">
                        <h2 class="shipping-section-title">6. Probleme la Livrare</h2>
                        <div class="shipping-section-content">
                            <h3 class="shipping-subsection-title">6.1. Coletul Nu a Ajuns</h3>
                            <p>Dacă coletul nu a ajuns în termenul estimat:</p>
                            <ol class="shipping-list">
                                <li>Verificați statusul tracking-ului</li>
                                <li>Contactați curierul direct folosind numărul AWB</li>
                                <li>Dacă problema persistă, contactați-ne la <a href="mailto:contact@haloo.ro" class="shipping-link">contact@haloo.ro</a></li>
                            </ol>

                            <h3 class="shipping-subsection-title">6.2. Coletul a Fost Deteriorat</h3>
                            <p>Dacă coletul a fost deteriorat la livrare:</p>
                            <ul class="shipping-list">
                                <li><strong>Nu acceptați coletul</strong> dacă ambalajul este deteriorat vizibil</li>
                                <li>Contactați-ne imediat la <a href="mailto:contact@haloo.ro" class="shipping-link">contact@haloo.ro</a></li>
                                <li>Faceți fotografii ale ambalajului deteriorat</li>
                                <li>Vă vom trimite un produs de înlocuire sau vă vom rambursa suma</li>
                            </ul>

                            <h3 class="shipping-subsection-title">6.3. Coletul a Fost Livrat Greșit</h3>
                            <p>Dacă ați primit un produs diferit de cel comandat:</p>
                            <ul class="shipping-list">
                                <li>Contactați-ne imediat</li>
                                <li>Nu deschideți ambalajul produsului greșit</li>
                                <li>Vă vom trimite produsul corect și vă vom oferi un AWB gratuit pentru returnarea produsului greșit</li>
                            </ul>
                        </div>
                    </section>

                    <!-- Packaging Section -->
                    <section class="shipping-section">
                        <h2 class="shipping-section-title">7. Ambalare și Siguranță</h2>
                        <div class="shipping-section-content">
                            <h3 class="shipping-subsection-title">7.1. Ambalare Profesională</h3>
                            <p>Toate produsele sunt ambalate profesional pentru a asigura protecție maximă în timpul transportului:</p>
                            <ul class="shipping-list">
                                <li>Cutii rezistente și adaptate dimensiunii produsului</li>
                                <li>Materiale de protecție (bule de aer, polistiren, etc.)</li>
                                <li>Sigilare sigură pentru a preveni deteriorarea</li>
                            </ul>

                            <h3 class="shipping-subsection-title">7.2. Asigurare</h3>
                            <p>Toate coletele sunt asigurate împotriva pierderii sau deteriorării în timpul transportului. În cazul unor probleme, veți fi despăgubiți integral.</p>
                        </div>
                    </section>

                    <!-- Contact Section -->
                    <section class="shipping-section">
                        <h2 class="shipping-section-title">8. Contact</h2>
                        <div class="shipping-section-content">
                            <p>Pentru întrebări despre livrare, vă rugăm să ne contactați:</p>
                            <ul class="shipping-list">
                                <li><strong>Email:</strong> <a href="mailto:contact@haloo.ro" class="shipping-link">contact@haloo.ro</a></li>
                                <li><strong>Telefon:</strong> <a href="tel:+40123456789" class="shipping-link">+40 (123) 456-7890</a> (Luni-Vineri: 08:00-21:00)</li>
                                <li><strong>WhatsApp:</strong> <a href="https://wa.me/40123456789" class="shipping-link">+40 (123) 456-7890</a></li>
                            </ul>
                        </div>
                    </section>

                </div>
            </div>

        </main>
    </div>
</div>

<?php
get_footer();
?>

