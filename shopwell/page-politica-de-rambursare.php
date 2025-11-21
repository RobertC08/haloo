<?php
/**
 * Template Name: Politica de Rambursare
 * 
 * Refund Policy Page Template
 *
 * @package Shopwell
 */

get_header();
?>

<!-- CSS moved to external file: assets/css/pages/politica-de-rambursare.css -->

<div class="site-content-container">
    <div id="primary" class="content-area">
        <main id="main" class="site-main">
            
            <!-- Hero Section -->
            <section class="refund-hero">
                <div class="refund-container">
                    <div class="refund-header">
                        <h1 class="refund-title">Politica de Rambursare</h1>
                        <p class="refund-subtitle">Ultima actualizare: <?php echo date('d.m.Y'); ?></p>
                    </div>
                </div>
            </section>

            <!-- Content -->
            <div class="refund-content">
                <div class="refund-container">
                    
                    <!-- Introduction Section -->
                    <section class="refund-section">
                        <h2 class="refund-section-title">1. Introducere</h2>
                        <div class="refund-section-content">
                            <p>La Haloo, ne angajăm să procesăm toate rambursările rapid și corect. Această politică explică procesul de rambursare, termenele și metodele disponibile.</p>
                            <p>Toate rambursările sunt procesate în conformitate cu legislația română privind drepturile consumatorilor și standardele noastre interne de calitate.</p>
                        </div>
                    </section>

                    <!-- Refund Eligibility Section -->
                    <section class="refund-section">
                        <h2 class="refund-section-title">2. Eligibilitatea pentru Rambursare</h2>
                        <div class="refund-section-content">
                            <h3 class="refund-subsection-title">2.1. Cazuri în Care Oferim Rambursare</h3>
                            <p>Oferim rambursare completă în următoarele situații:</p>
                            <ul class="refund-list">
                                <li>Returul produsului în termen de 14 zile de la primire (conform dreptului de retur)</li>
                                <li>Produsul este defect sau nu corespunde descrierii</li>
                                <li>Produsul a fost deteriorat în timpul transportului</li>
                                <li>Produsul a fost livrat greșit</li>
                                <li>Comanda a fost anulată înainte de expediere</li>
                            </ul>

                            <h3 class="refund-subsection-title">2.2. Cazuri în Care Rambursarea Poate Fi Parțială</h3>
                            <p>În următoarele situații, rambursarea poate fi parțială:</p>
                            <ul class="refund-list">
                                <li>Produsul a fost deteriorat din cauza utilizării necorespunzătoare (se deduce costul reparației)</li>
                                <li>Lipsesc accesoriile sau componentele produsului (se deduce valoarea componentelor lipsă)</li>
                                <li>Ambalajul original a fost deteriorat sau pierdut (se poate deduce o taxă simbolică)</li>
                            </ul>

                            <h3 class="refund-subsection-title">2.3. Cazuri în Care Rambursarea Nu Este Posibilă</h3>
                            <p>Rambursarea nu este posibilă în următoarele situații:</p>
                            <ul class="refund-list">
                                <li>Produsul a fost returnat după termenul de 14 zile (fără un motiv justificat)</li>
                                <li>Produsul a fost deteriorat din cauza utilizării necorespunzătoare sau neglijenței</li>
                                <li>Produsul a fost personalizat sau configurat conform specificațiilor dvs.</li>
                                <li>Produsul nu poate fi returnat din motive de igienă sau protecție a sănătății</li>
                            </ul>
                        </div>
                    </section>

                    <!-- Refund Process Section -->
                    <section class="refund-section">
                        <h2 class="refund-section-title">3. Procesul de Rambursare</h2>
                        <div class="refund-section-content">
                            <h3 class="refund-subsection-title">3.1. Inițierea Rambursării</h3>
                            <p>Pentru a iniția o rambursare:</p>
                            <ol class="refund-list">
                                <li><strong>Returnați produsul:</strong> Urmați procesul de retur descris în <a href="<?php echo home_url('/retururi-si-inlocuiri'); ?>" class="refund-link">Politica de Retururi</a></li>
                                <li><strong>Verificarea produsului:</strong> Vom verifica produsul returnat (3-5 zile lucrătoare)</li>
                                <li><strong>Confirmarea rambursării:</strong> Vă vom confirma acceptarea returului și inițierea procesului de rambursare</li>
                                <li><strong>Procesarea rambursării:</strong> Rambursarea va fi procesată în termen de 14 zile lucrătoare</li>
                            </ol>

                            <h3 class="refund-subsection-title">3.2. Verificarea Produsului Returnat</h3>
                            <p>După primirea produsului returnat, îl vom verifica pentru a ne asigura că respectă condițiile de retur. Verificarea include:</p>
                            <ul class="refund-list">
                                <li>Verificarea stării fizice a produsului</li>
                                <li>Verificarea prezenței tuturor accesoriilor</li>
                                <li>Testarea funcționalității (dacă este cazul)</li>
                                <li>Verificarea ambalajului</li>
                            </ul>
                            <p>Dacă produsul nu respectă condițiile, vă vom contacta pentru a discuta situația și posibilele opțiuni.</p>

                            <h3 class="refund-subsection-title">3.3. Anularea Comenzilor</h3>
                            <p>Dacă doriți să anulați o comandă înainte de expediere:</p>
                            <ul class="refund-list">
                                <li>Contactați-ne imediat la <a href="mailto:contact@haloo.ro" class="refund-link">contact@haloo.ro</a> sau telefonic</li>
                                <li>Dacă comanda nu a fost încă expediată, o putem anula și vă vom rambursa suma imediat</li>
                                <li>Dacă comanda a fost deja expediată, veți trebui să returnați produsul conform procesului de retur</li>
                            </ul>
                        </div>
                    </section>

                    <!-- Refund Methods Section -->
                    <section class="refund-section">
                        <h2 class="refund-section-title">4. Metode de Rambursare</h2>
                        <div class="refund-section-content">
                            <h3 class="refund-subsection-title">4.1. Rambursare pe Card Bancar</h3>
                            <p>Dacă ați plătit cu card bancar:</p>
                            <ul class="refund-list">
                                <li>Rambursarea va fi procesată pe același card folosit pentru plată</li>
                                <li>Termen: 5-7 zile lucrătoare de la confirmarea returului</li>
                                <li>Suma va apărea în contul dvs. bancar</li>
                                <li>Veți primi o notificare prin email când rambursarea este procesată</li>
                            </ul>

                            <h3 class="refund-subsection-title">4.2. Rambursare prin Transfer Bancar</h3>
                            <p>Dacă ați plătit prin transfer bancar sau ramburs:</p>
                            <ul class="refund-list">
                                <li>Rambursarea se face prin transfer bancar în contul dvs.</li>
                                <li>Termen: 3-5 zile lucrătoare de la confirmarea returului</li>
                                <li>Vă vom solicita datele contului bancar (IBAN) pentru rambursare</li>
                                <li>Veți primi o notificare prin email când rambursarea este procesată</li>
                            </ul>

                            <h3 class="refund-subsection-title">4.3. Rambursare prin Voucher de Cumpărături</h3>
                            <p>La cererea dvs., putem oferi rambursarea sub formă de voucher de cumpărături:</p>
                            <ul class="refund-list">
                                <li>Voucherul poate fi folosit pentru orice comandă viitoare</li>
                                <li>Valabilitate: 12 luni de la emitere</li>
                                <li>Voucherul poate fi combinat cu alte promoții</li>
                                <li>Procesarea este instantanee</li>
                            </ul>
                        </div>
                    </section>

                    <!-- Refund Amount Section -->
                    <section class="refund-section">
                        <h2 class="refund-section-title">5. Suma Rambursată</h2>
                        <div class="refund-section-content">
                            <h3 class="refund-subsection-title">5.1. Rambursare Completă</h3>
                            <p>Rambursarea completă include:</p>
                            <ul class="refund-list">
                                <li>Costul produsului</li>
                                <li>Costurile inițiale de livrare (dacă produsul este defect sau nu corespunde descrierii)</li>
                            </ul>

                            <h3 class="refund-subsection-title">5.2. Rambursare Parțială</h3>
                            <p>În cazul retururilor din motive personale (schimbarea deciziei), rambursarea include:</p>
                            <ul class="refund-list">
                                <li>Costul produsului</li>
                                <li><strong>NU include</strong> costurile inițiale de livrare</li>
                            </ul>

                            <h3 class="refund-subsection-title">5.3. Deduceri</h3>
                            <p>Pot fi aplicate următoarele deduceri:</p>
                            <ul class="refund-list">
                                <li>Costul reparației pentru deteriorări cauzate de utilizare necorespunzătoare</li>
                                <li>Valoarea componentelor sau accesoriilor lipsă</li>
                                <li>Taxă pentru ambalaj deteriorat sau pierdut (doar în cazuri excepționale)</li>
                            </ul>
                            <p>Orice deducere va fi comunicată și explicată înainte de procesarea rambursării.</p>
                        </div>
                    </section>

                    <!-- Refund Timeline Section -->
                    <section class="refund-section">
                        <h2 class="refund-section-title">6. Termene de Rambursare</h2>
                        <div class="refund-section-content">
                            <h3 class="refund-subsection-title">6.1. Termenul Standard</h3>
                            <p>Rambursarea standard se face în termen de <strong>14 zile lucrătoare</strong> de la:</p>
                            <ul class="refund-list">
                                <li>Confirmarea acceptării returului (după verificarea produsului)</li>
                                <li>Sau, în cazul anulării comenzii, de la confirmarea anulării</li>
                            </ul>

                            <h3 class="refund-subsection-title">6.2. Factori Care Pot Influența Termenul</h3>
                            <p>Termenul de rambursare poate fi influențat de:</p>
                            <ul class="refund-list">
                                <li>Metoda de plată utilizată inițial</li>
                                <li>Banca dvs. (pentru rambursări pe card)</li>
                                <li>Verificarea produsului returnat (dacă necesită timp suplimentar)</li>
                                <li>Perioadele de sărbători legale</li>
                            </ul>

                            <h3 class="refund-subsection-title">6.3. Notificări</h3>
                            <p>Veți primi notificări prin email la fiecare etapă:</p>
                            <ul class="refund-list">
                                <li>Confirmarea primirii produsului returnat</li>
                                <li>Confirmarea acceptării returului</li>
                                <li>Confirmarea inițierii procesului de rambursare</li>
                                <li>Confirmarea finalizării rambursării</li>
                            </ul>
                        </div>
                    </section>

                    <!-- Disputes Section -->
                    <section class="refund-section">
                        <h2 class="refund-section-title">7. Contestații și Dispute</h2>
                        <div class="refund-section-content">
                            <h3 class="refund-subsection-title">7.1. Contestații privind Rambursarea</h3>
                            <p>Dacă nu sunteți de acord cu suma rambursată sau cu procesul de rambursare:</p>
                            <ul class="refund-list">
                                <li>Contactați-ne imediat la <a href="mailto:contact@haloo.ro" class="refund-link">contact@haloo.ro</a></li>
                                <li>Furnizați detalii despre contestația dvs.</li>
                                <li>Vom analiza cazul și vă vom răspunde în termen de 5 zile lucrătoare</li>
                            </ul>

                            <h3 class="refund-subsection-title">7.2. Rezolvarea Disputelor</h3>
                            <p>Ne angajăm să rezolvăm toate disputele într-un mod echitabil și rapid. În cazul în care nu putem ajunge la un acord, puteți:</p>
                            <ul class="refund-list">
                                <li>Solicita intervenția Autorității Naționale pentru Protecția Consumatorilor (ANPC)</li>
                                <li>Apela la serviciile de mediere disponibile</li>
                            </ul>
                        </div>
                    </section>

                    <!-- Contact Section -->
                    <section class="refund-section">
                        <h2 class="refund-section-title">8. Contact</h2>
                        <div class="refund-section-content">
                            <p>Pentru întrebări despre rambursări, vă rugăm să ne contactați:</p>
                            <ul class="refund-list">
                                <li><strong>Email:</strong> <a href="mailto:contact@haloo.ro" class="refund-link">contact@haloo.ro</a></li>
                                <li><strong>Telefon:</strong> <a href="tel:+40123456789" class="refund-link">+40 (123) 456-7890</a> (Luni-Vineri: 08:00-21:00)</li>
                                <li><strong>WhatsApp:</strong> <a href="https://wa.me/40123456789" class="refund-link">+40 (123) 456-7890</a></li>
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

