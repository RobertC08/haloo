<?php
/**
 * Template Name: Livrare și Delivery
 * 
 * Shipping and Delivery Page Template
 *
 * @package Shopwell
 */

get_header();
?>

<!-- CSS moved to external file: assets/css/pages/livrare-si-delivery.css -->

<div class="site-content-container">
    <div id="primary" class="content-area">
        <main id="main" class="site-main">
            
            <!-- Hero Section -->
            <section class="delivery-hero">
                <div class="delivery-container">
                    <div class="delivery-header">
                        <h1 class="delivery-title">Livrare și Delivery</h1>
                        <p class="delivery-subtitle">Ultima actualizare: <?php echo date('d.m.Y'); ?></p>
                    </div>
                </div>
            </section>

            <!-- Content -->
            <div class="delivery-content">
                <div class="delivery-container">
                    
                    <!-- Introduction Section -->
                    <section class="delivery-section">
                        <h2 class="delivery-section-title">1. Informații Generale</h2>
                        <div class="delivery-section-content">
                            <p>La Haloo, livrăm produsele dvs. rapid și sigur în toată România. Oferim multiple opțiuni de livrare pentru a vă oferi flexibilitate maximă și experiență optimă.</p>
                            <p>Toate comenzile sunt procesate și expediate în cel mai scurt timp posibil, iar veți primi notificări prin email la fiecare etapă a procesului de livrare.</p>
                        </div>
                    </section>

                    <!-- Shipping Options Section -->
                    <section class="delivery-section">
                        <h2 class="delivery-section-title">2. Opțiuni de Livrare</h2>
                        <div class="delivery-section-content">
                            <h3 class="delivery-subsection-title">2.1. Livrare Standard</h3>
                            <p>Livrarea standard este disponibilă în toată România:</p>
                            <ul class="delivery-list">
                                <li><strong>Cost:</strong> 20 RON (gratuită pentru comenzi peste 500 RON)</li>
                                <li><strong>Termen:</strong> 2-3 zile lucrătoare în orașele mari, 3-5 zile lucrătoare în restul țării</li>
                                <li><strong>Curier:</strong> FAN Courier, DPD sau GLS</li>
                                <li><strong>Tracking:</strong> Da, veți primi număr AWB pentru urmărire</li>
                            </ul>

                            <h3 class="delivery-subsection-title">2.2. Livrare Express (București)</h3>
                            <p>Livrare rapidă disponibilă doar în București și împrejurimi:</p>
                            <ul class="delivery-list">
                                <li><strong>Cost:</strong> 35 RON</li>
                                <li><strong>Termen:</strong> În aceeași zi sau a doua zi (pentru comenzi plasate până la ora 14:00)</li>
                                <li><strong>Disponibilitate:</strong> Doar în București</li>
                                <li><strong>Tracking:</strong> Da, urmărire în timp real</li>
                            </ul>

                            <h3 class="delivery-subsection-title">2.3. Ramburs la Livrare</h3>
                            <p>Opțiunea de plată la livrare:</p>
                            <ul class="delivery-list">
                                <li><strong>Taxă suplimentară:</strong> 15 RON</li>
                                <li><strong>Disponibil:</strong> Pentru toate metodele de livrare</li>
                                <li><strong>Notă:</strong> Taxa de ramburs se adaugă la costul de livrare</li>
                            </ul>
                        </div>
                    </section>

                    <!-- Delivery Process Section -->
                    <section class="delivery-section">
                        <h2 class="delivery-section-title">3. Procesul de Livrare</h2>
                        <div class="delivery-section-content">
                            <h3 class="delivery-subsection-title">3.1. Procesarea Comenzii</h3>
                            <p>După plasarea comenzii:</p>
                            <ol class="delivery-list">
                                <li>Vă trimitem email de confirmare a comenzii</li>
                                <li>Procesăm comanda în termen de 24 de ore (sau 48 de ore în perioade de vârf)</li>
                                <li>Împachetăm produsul sigur și profesional</li>
                                <li>Expediem coletul către curier</li>
                            </ol>

                            <h3 class="delivery-subsection-title">3.2. Urmărirea Coletului</h3>
                            <p>După expediere, veți primi:</p>
                            <ul class="delivery-list">
                                <li>Email cu numărul AWB (Air Waybill)</li>
                                <li>Link direct către sistemul de tracking al curierului</li>
                                <li>Actualizări despre statusul comenzii în contul dvs.</li>
                            </ul>

                            <h3 class="delivery-subsection-title">3.3. Primirea Coletului</h3>
                            <p>La livrare:</p>
                            <ul class="delivery-list">
                                <li>Verificați coletul înainte de semnare</li>
                                <li>Dacă ambalajul este deteriorat, nu acceptați coletul și contactați-ne imediat</li>
                                <li>Dacă totul este în regulă, semnați primirea</li>
                                <li>În cazul plății la livrare, plătiți suma către curier</li>
                            </ul>
                        </div>
                    </section>

                    <!-- Delivery Areas Section -->
                    <section class="delivery-section">
                        <h2 class="delivery-section-title">4. Zone de Livrare</h2>
                        <div class="delivery-section-content">
                            <h3 class="delivery-subsection-title">4.1. Livrare în România</h3>
                            <p>Livrăm în <strong>toată România</strong> prin serviciile de curierat rapid. Folosim următorii parteneri de transport:</p>
                            <ul class="delivery-list">
                                <li><strong>FAN Courier</strong> - pentru toate zonele</li>
                                <li><strong>DPD Romania</strong> - pentru zonele principale</li>
                                <li><strong>GLS Romania</strong> - pentru zonele rurale</li>
                            </ul>

                            <h3 class="delivery-subsection-title">4.2. Termene de Livrare pe Zone</h3>
                            <ul class="delivery-list">
                                <li><strong>București și orașele mari:</strong> 2-3 zile lucrătoare</li>
                                <li><strong>Orașe medii:</strong> 3-4 zile lucrătoare</li>
                                <li><strong>Restul țării:</strong> 4-5 zile lucrătoare</li>
                                <li><strong>Zonele rurale:</strong> 5-7 zile lucrătoare</li>
                            </ul>

                            <h3 class="delivery-subsection-title">4.3. Livrare Internațională</h3>
                            <p>Momentan livrăm doar în România. Lucrăm la extinderea serviciilor noastre pentru livrare internațională în viitorul apropiat.</p>
                        </div>
                    </section>

                    <!-- Delivery Costs Section -->
                    <section class="delivery-section">
                        <h2 class="delivery-section-title">5. Costuri de Livrare</h2>
                        <div class="delivery-section-content">
                            <h3 class="delivery-subsection-title">5.1. Calcularea Costurilor</h3>
                            <p>Costurile de livrare sunt calculate automat în coșul de cumpărături în funcție de:</p>
                            <ul class="delivery-list">
                                <li>Valoarea comenzii</li>
                                <li>Adresa de livrare</li>
                                <li>Metoda de livrare selectată</li>
                                <li>Metoda de plată selectată</li>
                            </ul>

                            <h3 class="delivery-subsection-title">5.2. Livrare Gratuită</h3>
                            <p>Oferim <strong>livrare gratuită</strong> pentru:</p>
                            <ul class="delivery-list">
                                <li>Comenzi cu valoare peste 500 RON</li>
                                <li>Toate metodele de livrare standard</li>
                            </ul>
                            <p><strong>Notă:</strong> Livrarea express și taxa de ramburs nu sunt incluse în livrarea gratuită.</p>

                            <h3 class="delivery-subsection-title">5.3. Tabel de Costuri</h3>
                            <div class="delivery-table-wrapper">
                                <table class="delivery-table">
                                    <thead>
                                        <tr>
                                            <th>Metodă de Livrare</th>
                                            <th>Cost</th>
                                            <th>Termen</th>
                                            <th>Zonă</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Livrare Standard</td>
                                            <td>20 RON<br><small>(gratuită peste 500 RON)</small></td>
                                            <td>2-5 zile lucrătoare</td>
                                            <td>Toată România</td>
                                        </tr>
                                        <tr>
                                            <td>Livrare Express</td>
                                            <td>35 RON</td>
                                            <td>În aceeași zi / a doua zi</td>
                                            <td>Doar București</td>
                                        </tr>
                                        <tr>
                                            <td>Ramburs</td>
                                            <td>+15 RON</td>
                                            <td>La livrare</td>
                                            <td>Toată România</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>

                    <!-- Packaging Section -->
                    <section class="delivery-section">
                        <h2 class="delivery-section-title">6. Ambalare și Siguranță</h2>
                        <div class="delivery-section-content">
                            <h3 class="delivery-subsection-title">6.1. Ambalare Profesională</h3>
                            <p>Toate produsele sunt ambalate profesional pentru a asigura protecție maximă:</p>
                            <ul class="delivery-list">
                                <li>Cutii rezistente adaptate dimensiunii produsului</li>
                                <li>Materiale de protecție (bule de aer, polistiren, carton ondulat)</li>
                                <li>Sigilare sigură pentru a preveni deteriorarea</li>
                                <li>Etichete clare cu informații despre conținut</li>
                            </ul>

                            <h3 class="delivery-subsection-title">6.2. Asigurare</h3>
                            <p>Toate coletele sunt asigurate împotriva:</p>
                            <ul class="delivery-list">
                                <li>Pierderii în timpul transportului</li>
                                <li>Deteriorării în timpul transportului</li>
                                <li>Furtului sau dispariției</li>
                            </ul>
                            <p>În cazul unor probleme, veți fi despăgubiți integral.</p>
                        </div>
                    </section>

                    <!-- Delivery Issues Section -->
                    <section class="delivery-section">
                        <h2 class="delivery-section-title">7. Probleme la Livrare</h2>
                        <div class="delivery-section-content">
                            <h3 class="delivery-subsection-title">7.1. Coletul Nu a Ajuns</h3>
                            <p>Dacă coletul nu a ajuns în termenul estimat:</p>
                            <ol class="delivery-list">
                                <li>Verificați statusul tracking-ului folosind numărul AWB</li>
                                <li>Contactați curierul direct folosind numărul de telefon de pe AWB</li>
                                <li>Dacă problema persistă, contactați-ne la <a href="mailto:contact@haloo.ro" class="delivery-link">contact@haloo.ro</a></li>
                            </ol>

                            <h3 class="delivery-subsection-title">7.2. Coletul a Fost Deteriorat</h3>
                            <p>Dacă coletul a fost deteriorat la livrare:</p>
                            <ul class="delivery-list">
                                <li><strong>NU acceptați coletul</strong> dacă ambalajul este deteriorat vizibil</li>
                                <li>Faceți fotografii ale ambalajului deteriorat</li>
                                <li>Contactați-ne imediat la <a href="mailto:contact@haloo.ro" class="delivery-link">contact@haloo.ro</a></li>
                                <li>Vă vom trimite un produs de înlocuire sau vă vom rambursa suma</li>
                            </ul>

                            <h3 class="delivery-subsection-title">7.3. Coletul a Fost Livrat Greșit</h3>
                            <p>Dacă ați primit un produs diferit de cel comandat:</p>
                            <ul class="delivery-list">
                                <li>Contactați-ne imediat</li>
                                <li>Nu deschideți ambalajul produsului greșit</li>
                                <li>Vă vom trimite produsul corect și vă oferim AWB gratuit pentru returnarea produsului greșit</li>
                            </ul>
                        </div>
                    </section>

                    <!-- Contact Section -->
                    <section class="delivery-section">
                        <h2 class="delivery-section-title">8. Contact</h2>
                        <div class="delivery-section-content">
                            <p>Pentru întrebări despre livrare, vă rugăm să ne contactați:</p>
                            <ul class="delivery-list">
                                <li><strong>Email:</strong> <a href="mailto:contact@haloo.ro" class="delivery-link">contact@haloo.ro</a></li>
                                <li><strong>Telefon:</strong> <a href="tel:+40123456789" class="delivery-link">+40 (123) 456-7890</a> (Luni-Vineri: 08:00-21:00)</li>
                                <li><strong>WhatsApp:</strong> <a href="https://wa.me/40123456789" class="delivery-link">+40 (123) 456-7890</a></li>
                            </ul>
                            <p>Răspundem de obicei în mai puțin de 2 ore în timpul programului.</p>
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

