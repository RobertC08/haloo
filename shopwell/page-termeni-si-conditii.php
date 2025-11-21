<?php
/**
 * Template Name: Termeni și Condiții
 * 
 * Terms and Conditions Page Template
 *
 * @package Shopwell
 */

get_header();
?>

<!-- CSS moved to external file: assets/css/pages/termeni-si-conditii.css -->

<div class="site-content-container">
    <div id="primary" class="content-area">
        <main id="main" class="site-main">
            
            <!-- Hero Section -->
            <section class="terms-hero">
                <div class="terms-container">
                    <div class="terms-header">
                        <h1 class="terms-title">Termeni și Condiții</h1>
                        <p class="terms-subtitle">Ultima actualizare: <?php echo date('d.m.Y'); ?></p>
                    </div>
                </div>
            </section>

            <!-- Terms Content -->
            <div class="terms-content">
                <div class="terms-container">
                    
                    <!-- Introduction Section -->
                    <section class="terms-section">
                        <h2 class="terms-section-title">1. Introducere</h2>
                        <div class="terms-section-content">
                            <p>Bun venit pe haloo.ro! Prin accesarea și utilizarea acestui site web, acceptați să respectați și să fiți obligați de următorii termeni și condiții. Dacă nu sunteți de acord cu acești termeni, vă rugăm să nu utilizați site-ul nostru.</p>
                            <p>Haloo.ro este operat de [Numele Companiei], cu sediul în [Adresa], România. Ne rezervăm dreptul de a modifica acești termeni în orice moment, iar utilizarea continuă a site-ului după modificări constituie acceptarea noilor termeni.</p>
                        </div>
                    </section>

                    <!-- Definitions Section -->
                    <section class="terms-section">
                        <h2 class="terms-section-title">2. Definiții</h2>
                        <div class="terms-section-content">
                            <ul class="terms-list">
                                <li><strong>"Site-ul"</strong> se referă la haloo.ro și toate paginile, funcționalitățile și serviciile oferite prin intermediul acestuia.</li>
                                <li><strong>"Noi", "nostru", "al nostru"</strong> se referă la Haloo și echipa noastră.</li>
                                <li><strong>"Voi", "dvs.", "utilizator"</strong> se referă la persoana care accesează sau utilizează site-ul.</li>
                                <li><strong>"Produse refurbished"</strong> se referă la dispozitive electronice care au fost folosite anterior, verificate, reparate (dacă a fost necesar) și aduse la standarde de funcționare optimă.</li>
                                <li><strong>"Comandă"</strong> se referă la solicitarea dvs. de a cumpăra un produs de pe site-ul nostru.</li>
                            </ul>
                        </div>
                    </section>

                    <!-- Products Section -->
                    <section class="terms-section">
                        <h2 class="terms-section-title">3. Despre Produse</h2>
                        <div class="terms-section-content">
                            <h3 class="terms-subsection-title">3.1. Produse Refurbished</h3>
                            <p>Toate produsele vândute pe haloo.ro sunt telefoane refurbished (recondiționate). Aceste produse au fost:</p>
                            <ul class="terms-list">
                                <li>Verificate complet pentru funcționalitate</li>
                                <li>Curățate profesional</li>
                                <li>Reparate (dacă a fost necesar) de către tehnicieni calificați</li>
                                <li>Testate pentru a ne asigura că funcționează conform specificațiilor</li>
                            </ul>

                            <h3 class="terms-subsection-title">3.2. Gradul de Stare</h3>
                            <p>Produsele noastre sunt clasificate în funcție de gradul de stare:</p>
                            <ul class="terms-list">
                                <li><strong>Grad A (Excelent):</strong> Dispozitivul arată ca nou, fără zgârieturi vizibile sau semne de uzură.</li>
                                <li><strong>Grad B (Foarte Bun):</strong> Dispozitivul poate avea mici zgârieturi superficiale pe carcasă, dar ecranul este intact.</li>
                                <li><strong>Grad C (Bun):</strong> Dispozitivul poate avea urme de uzură vizibile pe carcasă și/sau ecran, dar funcționează perfect din punct de vedere tehnic.</li>
                            </ul>

                            <h3 class="terms-subsection-title">3.3. Disponibilitatea Produselor</h3>
                            <p>Ne străduim să menținem stocurile actualizate, dar disponibilitatea produselor este limitată. Rezervăm dreptul de a modifica, suspenda sau întrerupe vânzarea oricărui produs în orice moment, fără notificare prealabilă.</p>
                        </div>
                    </section>

                    <!-- Orders Section -->
                    <section class="terms-section">
                        <h2 class="terms-section-title">4. Comenzi și Prețuri</h2>
                        <div class="terms-section-content">
                            <h3 class="terms-subsection-title">4.1. Plasarea Comenzilor</h3>
                            <p>Prin plasarea unei comenzi, vă angajați să cumpărați produsul la prețul afișat. Toate comenzile sunt supuse confirmării și acceptării de către noi. Ne rezervăm dreptul de a refuza sau anula orice comandă din orice motiv.</p>

                            <h3 class="terms-subsection-title">4.2. Prețuri</h3>
                            <p>Prețurile afișate pe site sunt exprimate în RON (Lei românești) și includ TVA, unde este cazul. Ne rezervăm dreptul de a modifica prețurile în orice moment, dar modificările nu vor afecta comenzile deja confirmate.</p>

                            <h3 class="terms-subsection-title">4.3. Erori de Preț</h3>
                            <p>În cazul în care un produs este listat cu un preț incorect din cauza unei erori tehnice, ne rezervăm dreptul de a refuza sau anula orice comandă plasată pentru acel produs. Vă vom contacta pentru a vă informa despre eroare și vă vom oferi opțiunea de a plasa comanda la prețul corect sau de a anula comanda.</p>
                        </div>
                    </section>

                    <!-- Payment Section -->
                    <section class="terms-section">
                        <h2 class="terms-section-title">5. Plata</h2>
                        <div class="terms-section-content">
                            <h3 class="terms-subsection-title">5.1. Metode de Plată</h3>
                            <p>Acceptăm următoarele metode de plată:</p>
                            <ul class="terms-list">
                                <li>Card bancar (Visa, Mastercard) - procesat securizat prin gateway-uri certificate</li>
                                <li>Apple Pay și Google Pay</li>
                                <li>Ramburs la livrare (contra cost suplimentar de 15 RON)</li>
                                <li>Transfer bancar</li>
                            </ul>

                            <h3 class="terms-subsection-title">5.2. Securitatea Plăților</h3>
                            <p>Toate plățile online sunt procesate securizat prin gateway-uri certificate. Nu stocăm informațiile cardului dvs. de credit. Toate datele financiare sunt procesate prin intermediul unor servicii de plată securizate și certificate.</p>

                            <h3 class="terms-subsection-title">5.3. Confirmarea Plății</h3>
                            <p>Comanda dvs. va fi procesată doar după confirmarea plății. În cazul plății prin transfer bancar, comanda va fi procesată după confirmarea primirii fondurilor în contul nostru.</p>
                        </div>
                    </section>

                    <!-- Shipping Section -->
                    <section class="terms-section">
                        <h2 class="terms-section-title">6. Livrare</h2>
                        <div class="terms-section-content">
                            <h3 class="terms-subsection-title">6.1. Termene de Livrare</h3>
                            <p>Termenele estimate de livrare sunt:</p>
                            <ul class="terms-list">
                                <li><strong>Livrare standard:</strong> 2-3 zile lucrătoare în orașele mari</li>
                                <li><strong>Livrare în localități:</strong> 3-5 zile lucrătoare</li>
                                <li><strong>Livrare express (doar București):</strong> În aceeași zi sau a doua zi (pentru comenzi plasate până la ora 14:00)</li>
                            </ul>
                            <p>Termenele de livrare sunt estimate și pot varia în funcție de disponibilitatea produsului și de condițiile de transport.</p>

                            <h3 class="terms-subsection-title">6.2. Costuri de Livrare</h3>
                            <ul class="terms-list">
                                <li><strong>Livrare gratuită</strong> pentru comenzi peste 500 RON</li>
                                <li><strong>20 RON</strong> - Livrare standard pentru comenzi sub 500 RON</li>
                                <li><strong>35 RON</strong> - Livrare express (doar București)</li>
                                <li><strong>15 RON</strong> - Taxa suplimentară pentru ramburs</li>
                            </ul>

                            <h3 class="terms-subsection-title">6.3. Zona de Livrare</h3>
                            <p>Momentan livrăm doar în România. Nu livrăm în afara țării.</p>

                            <h3 class="terms-subsection-title">6.4. Risc de Pierdere sau Deteriorare</h3>
                            <p>Riscul de pierdere sau deteriorare a produsului se transferă către dvs. în momentul predării produsului către curier. În cazul în care produsul este deteriorat la livrare, vă rugăm să ne contactați imediat.</p>
                        </div>
                    </section>

                    <!-- Returns Section -->
                    <section class="terms-section">
                        <h2 class="terms-section-title">7. Retururi și Rambursări</h2>
                        <div class="terms-section-content">
                            <h3 class="terms-subsection-title">7.1. Dreptul de Retur</h3>
                            <p>Conform legislației în vigoare, aveți dreptul de a returna produsul în termen de 14 zile calendaristice de la data primirii, fără a fi nevoie să indicați motivul returului.</p>

                            <h3 class="terms-subsection-title">7.2. Condiții pentru Retur</h3>
                            <p>Pentru ca returul să fie acceptat, produsul trebuie să fie:</p>
                            <ul class="terms-list">
                                <li>În starea în care l-ați primit (fără deteriorări suplimentare)</li>
                                <li>Cu toate accesoriile incluse (cablu, încărcător, cutie, manuale, etc.)</li>
                                <li>Cu ambalajul original (dacă este posibil)</li>
                                <li>Fără a fi fost folosit în mod excesiv sau deteriorat</li>
                            </ul>

                            <h3 class="terms-subsection-title">7.3. Costuri de Retur</h3>
                            <p>Retururile sunt gratuite dacă produsul este defect sau nu corespunde descrierii. În alte cazuri, costurile de retur sunt suportate de dvs., cu excepția cazurilor în care am convenit altfel.</p>

                            <h3 class="terms-subsection-title">7.4. Rambursarea</h3>
                            <p>După primirea și verificarea produsului returnat, vă vom rambursa suma plătită în termen de 14 zile lucrătoare, folosind aceeași metodă de plată utilizată pentru comandă. Rambursarea va include costul produsului, dar nu și costurile inițiale de livrare (dacă nu este vorba despre un produs defect sau care nu corespunde descrierii).</p>
                        </div>
                    </section>

                    <!-- Warranty Section -->
                    <section class="terms-section">
                        <h2 class="terms-section-title">8. Garanție</h2>
                        <div class="terms-section-content">
                            <h3 class="terms-subsection-title">8.1. Perioada de Garanție</h3>
                            <p>Toate produsele noastre beneficiază de garanție de 12 luni de la data achiziției.</p>

                            <h3 class="terms-subsection-title">8.2. Acoperirea Garanției</h3>
                            <p>Garanția acoperă:</p>
                            <ul class="terms-list">
                                <li>Defecte de fabricație</li>
                                <li>Probleme hardware (display, baterie, butoane, etc.)</li>
                                <li>Probleme software (dacă nu sunt cauzate de utilizator)</li>
                            </ul>

                            <h3 class="terms-subsection-title">8.3. Excluderi din Garanție</h3>
                            <p>Garanția NU acoperă:</p>
                            <ul class="terms-list">
                                <li>Daune fizice cauzate de scăpări sau lovituri</li>
                                <li>Daune cauzate de apă sau lichide</li>
                                <li>Probleme cauzate de instalare software neautorizat sau modificări hardware</li>
                                <li>Uzura normală (zgârieturi superficiale, uzură a bateriei în timp)</li>
                                <li>Daune cauzate de utilizare necorespunzătoare</li>
                            </ul>

                            <h3 class="terms-subsection-title">8.4. Procedura de Garanție</h3>
                            <p>Pentru a solicita servicii în garanție, vă rugăm să ne contactați la adresa de email sau telefon indicată pe site. Vă vom furniza instrucțiuni detaliate pentru returnarea produsului și procesarea cererii de garanție.</p>
                        </div>
                    </section>

                    <!-- Privacy Section -->
                    <section class="terms-section">
                        <h2 class="terms-section-title">9. Confidențialitate și Protecția Datelor</h2>
                        <div class="terms-section-content">
                            <p>Protecția datelor dvs. personale este importantă pentru noi. Procesarea datelor dvs. personale se face în conformitate cu Regulamentul General privind Protecția Datelor (GDPR) și legislația română în vigoare.</p>
                            <p>Pentru detalii complete despre modul în care colectăm, utilizăm și protejăm datele dvs. personale, vă rugăm să consultați <a href="<?php echo home_url('/politica-de-confidentialitate'); ?>" class="terms-link">Politica noastră de Confidențialitate</a>.</p>
                        </div>
                    </section>

                    <!-- Intellectual Property Section -->
                    <section class="terms-section">
                        <h2 class="terms-section-title">10. Proprietate Intelectuală</h2>
                        <div class="terms-section-content">
                            <p>Toate conținuturile site-ului, inclusiv dar fără a se limita la texte, imagini, logo-uri, grafice, software și alte materiale, sunt proprietatea Haloo sau a licențiatorilor săi și sunt protejate de legile române și internaționale privind drepturile de autor și proprietatea intelectuală.</p>
                            <p>Nu aveți dreptul de a reproduce, distribui, modifica sau crea lucrări derivate din conținutul site-ului fără acordul nostru scris prealabil.</p>
                        </div>
                    </section>

                    <!-- Limitation of Liability Section -->
                    <section class="terms-section">
                        <h2 class="terms-section-title">11. Limitarea Răspunderii</h2>
                        <div class="terms-section-content">
                            <p>În măsura permisă de lege, Haloo nu va fi răspunzător pentru:</p>
                            <ul class="terms-list">
                                <li>Daune indirecte, accidentale, speciale sau consecvente rezultate din utilizarea sau imposibilitatea utilizării site-ului sau produselor</li>
                                <li>Pierderi de date, profit sau alte pierderi financiare</li>
                                <li>Interruperi ale serviciului sau erori tehnice</li>
                            </ul>
                            <p>Răspunderea noastră totală față de dvs. nu va depăși suma plătită pentru produsul respectiv.</p>
                        </div>
                    </section>

                    <!-- Changes to Terms Section -->
                    <section class="terms-section">
                        <h2 class="terms-section-title">12. Modificări ale Termenilor</h2>
                        <div class="terms-section-content">
                            <p>Ne rezervăm dreptul de a modifica acești termeni și condiții în orice moment. Modificările vor intra în vigoare imediat după publicarea pe site. Este responsabilitatea dvs. să verificați periodic acești termeni pentru a fi la curent cu orice modificări.</p>
                            <p>Utilizarea continuă a site-ului după publicarea modificărilor constituie acceptarea noilor termeni.</p>
                        </div>
                    </section>

                    <!-- Contact Section -->
                    <section class="terms-section">
                        <h2 class="terms-section-title">13. Contact</h2>
                        <div class="terms-section-content">
                            <p>Pentru întrebări sau nelămuriri privind acești termeni și condiții, vă rugăm să ne contactați:</p>
                            <ul class="terms-list">
                                <li><strong>Email:</strong> <a href="mailto:contact@haloo.ro" class="terms-link">contact@haloo.ro</a></li>
                                <li><strong>Telefon:</strong> <a href="tel:+40123456789" class="terms-link">+40 (123) 456-7890</a></li>
                                <li><strong>Adresă:</strong> [Adresa completă a companiei]</li>
                            </ul>
                        </div>
                    </section>

                    <!-- Governing Law Section -->
                    <section class="terms-section">
                        <h2 class="terms-section-title">14. Legea Aplicabilă și Jurisdicția</h2>
                        <div class="terms-section-content">
                            <p>Acești termeni și condiții sunt guvernați și interpretați în conformitate cu legile României. Orice dispută care ar putea apărea în legătură cu acești termeni sau cu utilizarea site-ului va fi rezolvată de instanțele competente din România.</p>
                        </div>
                    </section>

                    <!-- Acceptance Section -->
                    <section class="terms-section">
                        <h2 class="terms-section-title">15. Acceptarea Termenilor</h2>
                        <div class="terms-section-content">
                            <p>Prin accesarea și utilizarea site-ului haloo.ro, confirmați că ați citit, înțeles și acceptat acești termeni și condiții. Dacă nu sunteți de acord cu oricare dintre acești termeni, vă rugăm să nu utilizați site-ul nostru.</p>
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

