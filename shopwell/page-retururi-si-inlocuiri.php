<?php
/**
 * Template Name: Retururi și Înlocuiri
 * 
 * Returns and Replacements Page Template
 *
 * @package Shopwell
 */

get_header();
?>

<!-- CSS moved to external file: assets/css/pages/retururi-si-inlocuiri.css -->

<div class="site-content-container">
    <div id="primary" class="content-area">
        <main id="main" class="site-main">
            
            <!-- Hero Section -->
            <section class="returns-hero">
                <div class="returns-container">
                    <div class="returns-header">
                        <h1 class="returns-title">Retururi și Înlocuiri</h1>
                        <p class="returns-subtitle">Ultima actualizare: <?php echo date('d.m.Y'); ?></p>
                    </div>
                </div>
            </section>

            <!-- Content -->
            <div class="returns-content">
                <div class="returns-container">
                    
                    <!-- Introduction Section -->
                    <section class="returns-section">
                        <h2 class="returns-section-title">1. Introducere</h2>
                        <div class="returns-section-content">
                            <p>La Haloo, ne dorim să fii complet mulțumit de achiziția ta. Înțelegem că uneori un produs poate să nu corespundă așteptărilor tale, și de aceea am creat o politică de retur simplă și transparentă.</p>
                            <p>Această politică se aplică tuturor produselor cumpărate de pe haloo.ro și este în conformitate cu legislația română privind drepturile consumatorilor.</p>
                        </div>
                    </section>

                    <!-- Right to Return Section -->
                    <section class="returns-section">
                        <h2 class="returns-section-title">2. Dreptul de Retur</h2>
                        <div class="returns-section-content">
                            <h3 class="returns-subsection-title">2.1. Perioada de Retur</h3>
                            <p>Conform legislației în vigoare, aveți dreptul de a returna produsul în termen de <strong>14 zile calendaristice</strong> de la data primirii, fără a fi nevoie să indicați motivul returului.</p>
                            <p>Perioada de 14 zile începe de la data primirii produsului de către dvs. sau de către o terță persoană (altele decât curierul) desemnată de dvs.</p>

                            <h3 class="returns-subsection-title">2.2. Condiții pentru Retur</h3>
                            <p>Pentru ca returul să fie acceptat, produsul trebuie să fie:</p>
                            <ul class="returns-list">
                                <li>În starea în care l-ați primit (fără deteriorări suplimentare)</li>
                                <li>Cu toate accesoriile incluse (cablu, încărcător, cutie, manuale, etc.)</li>
                                <li>Cu ambalajul original (dacă este posibil)</li>
                                <li>Fără a fi fost folosit în mod excesiv sau deteriorat</li>
                                <li>Fără zgârieturi, lovituri sau alte daune fizice care nu erau prezente la livrare</li>
                            </ul>

                            <h3 class="returns-subsection-title">2.3. Produse Excluse de la Retur</h3>
                            <p>Următoarele produse nu pot fi returnate (conform legislației):</p>
                            <ul class="returns-list">
                                <li>Produse personalizate sau configurate conform specificațiilor dvs.</li>
                                <li>Produse deteriorate din cauza utilizării necorespunzătoare</li>
                                <li>Produse care au fost desigilate și nu pot fi returnate din motive de igienă sau protecție a sănătății</li>
                            </ul>
                        </div>
                    </section>

                    <!-- Return Process Section -->
                    <section class="returns-section">
                        <h2 class="returns-section-title">3. Procesul de Retur</h2>
                        <div class="returns-section-content">
                            <h3 class="returns-subsection-title">3.1. Cum Inițiați un Retur</h3>
                            <p>Pentru a iniția un retur, urmați acești pași:</p>
                            <ol class="returns-list">
                                <li><strong>Contactați-ne:</strong> Trimiteți un email la <a href="mailto:contact@haloo.ro" class="returns-link">contact@haloo.ro</a> sau sunați la <a href="tel:+40123456789" class="returns-link">+40 (123) 456-7890</a> în termen de 14 zile de la primirea produsului</li>
                                <li><strong>Indicați motivul:</strong> Specificați motivul returului (opțional, dar recomandat pentru îmbunătățirea serviciilor noastre)</li>
                                <li><strong>Primiți AWB:</strong> Vă vom trimite un AWB (Air Waybill) gratuit pentru returnarea produsului</li>
                                <li><strong>Împachetați produsul:</strong> Împachetați produsul în ambalajul original (dacă este posibil) sau într-un ambalaj sigur</li>
                                <li><strong>Predați coletul:</strong> Predați coletul curierului sau depuneți-l la un punct de ridicare</li>
                            </ol>

                            <h3 class="returns-subsection-title">3.2. Costuri de Retur</h3>
                            <p><strong>Retururi gratuite:</strong> Retururile sunt gratuite în următoarele cazuri:</p>
                            <ul class="returns-list">
                                <li>Produsul este defect sau nu corespunde descrierii</li>
                                <li>Produsul a fost livrat greșit</li>
                                <li>Produsul a fost deteriorat în timpul transportului</li>
                            </ul>
                            <p><strong>Retururi cu cost:</strong> În alte cazuri (de exemplu, schimbarea deciziei), costurile de retur sunt suportate de dvs., cu excepția cazurilor în care am convenit altfel.</p>

                            <h3 class="returns-subsection-title">3.3. Verificarea Produsului Returnat</h3>
                            <p>După primirea produsului returnat, îl vom verifica pentru a ne asigura că respectă condițiile de retur. Verificarea se face în termen de 3-5 zile lucrătoare de la primirea coletului.</p>
                            <p>Dacă produsul nu respectă condițiile de retur, vă vom contacta pentru a discuta situația. În acest caz, ne rezervăm dreptul de a refuza returul sau de a aplica o taxă pentru deteriorări.</p>
                        </div>
                    </section>

                    <!-- Refund Section -->
                    <section class="returns-section">
                        <h2 class="returns-section-title">4. Rambursarea</h2>
                            <div class="returns-section-content">
                            <h3 class="returns-subsection-title">4.1. Termen de Rambursare</h3>
                            <p>După verificarea produsului returnat, vă vom rambursa suma plătită în termen de <strong>14 zile lucrătoare</strong> de la confirmarea acceptării returului.</p>

                            <h3 class="returns-subsection-title">4.2. Metoda de Rambursare</h3>
                            <p>Rambursarea se face folosind aceeași metodă de plată utilizată pentru comandă:</p>
                            <ul class="returns-list">
                                <li><strong>Card bancar:</strong> Rambursarea va apărea în contul dvs. în termen de 5-7 zile lucrătoare</li>
                                <li><strong>Transfer bancar:</strong> Rambursarea va fi procesată în termen de 3-5 zile lucrătoare</li>
                                <li><strong>Ramburs:</strong> În cazul plății la livrare, rambursarea se face prin transfer bancar sau voucher de cumpărături</li>
                            </ul>

                            <h3 class="returns-subsection-title">4.3. Suma Rambursată</h3>
                            <p>Rambursarea va include:</p>
                            <ul class="returns-list">
                                <li>Costul produsului</li>
                                <li>Costurile inițiale de livrare (dacă produsul este defect sau nu corespunde descrierii)</li>
                            </ul>
                            <p><strong>Notă:</strong> În cazul retururilor din motive personale (schimbarea deciziei), costurile inițiale de livrare nu sunt rambursate.</p>
                        </div>
                    </section>

                    <!-- Replacements Section -->
                    <section class="returns-section">
                        <h2 class="returns-section-title">5. Înlocuiri</h2>
                        <div class="returns-section-content">
                            <h3 class="returns-subsection-title">5.1. Când Oferim Înlocuiri</h3>
                            <p>Oferim înlocuiri în următoarele situații:</p>
                            <ul class="returns-list">
                                <li>Produsul este defect la livrare</li>
                                <li>Produsul nu corespunde descrierii sau specificațiilor</li>
                                <li>Produsul a fost deteriorat în timpul transportului</li>
                                <li>Produsul a fost livrat greșit</li>
                            </ul>

                            <h3 class="returns-subsection-title">5.2. Procesul de Înlocuire</h3>
                            <p>Dacă doriți o înlocuire în loc de rambursare:</p>
                            <ol class="returns-list">
                                <li>Contactați-ne în termen de 14 zile de la primirea produsului</li>
                                <li>Indicați că doriți o înlocuire</li>
                                <li>Returnați produsul defect/greșit (folosind același proces de retur)</li>
                                <li>Vă vom trimite produsul de înlocuire după verificarea returului</li>
                            </ol>

                            <h3 class="returns-subsection-title">5.3. Disponibilitatea Produsului de Înlocuire</h3>
                            <p>Înlocuirea este condiționată de disponibilitatea produsului în stoc. Dacă produsul nu este disponibil, vă vom oferi opțiunea de a alege un alt produs sau de a primi rambursarea.</p>
                        </div>
                    </section>

                    <!-- Contact Section -->
                    <section class="returns-section">
                        <h2 class="returns-section-title">6. Contact</h2>
                        <div class="returns-section-content">
                            <p>Pentru întrebări sau nelămuriri privind retururile și înlocuirile, vă rugăm să ne contactați:</p>
                            <ul class="returns-list">
                                <li><strong>Email:</strong> <a href="mailto:contact@haloo.ro" class="returns-link">contact@haloo.ro</a></li>
                                <li><strong>Telefon:</strong> <a href="tel:+40123456789" class="returns-link">+40 (123) 456-7890</a> (Luni-Vineri: 08:00-21:00)</li>
                                <li><strong>WhatsApp:</strong> <a href="https://wa.me/40123456789" class="returns-link">+40 (123) 456-7890</a></li>
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

