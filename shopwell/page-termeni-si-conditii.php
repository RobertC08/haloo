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
                            <p>Haloo.ro este operat de <strong>FRUIT CREATIVE SRL</strong>, CUI 39066744, Nr. Reg. Com. J2020005512236, România. Ne rezervăm dreptul de a modifica acești termeni în orice moment, iar utilizarea continuă a site-ului după modificări constituie acceptarea noilor termeni.</p>
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

                            <h3 class="terms-subsection-title">3.2. Clasificarea după Stare (Grading)</h3>
                            <p>Produsele noastre sunt clasificate în funcție de aspectul exterior:</p>
                            <ul class="terms-list">
                                <li><strong>Ca Nou:</strong> Arată impecabil, fără semne vizibile de uzură. Poate prezenta zgârieturi foarte fine, insesizabile.</li>
                                <li><strong>Excelent:</strong> Stare excelentă cu semne minime care nu sunt vizibile de la distanță. Prezintă zgârieturi fine, sesizabile.</li>
                                <li><strong>Foarte Bun:</strong> Câteva zgârieturi fine pe carcasă sau ramă, ecranul în condiție excelentă. Prezintă mai multe zgârieturi sau urme vizibile.</li>
                                <li><strong>Bun:</strong> Semne moderate de uzură vizibile, funcționalitate 100% garantată. Prezintă mai multe zgârieturi pronunțate sau urme foarte vizibile.</li>
                            </ul>
                            <p><strong>Toate Produsele funcționează la parametri tehnici optimi, conform specificațiilor, indiferent de starea estetică.</strong></p>

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
                                <li><strong>Livrare gratuită</strong> pentru comenzi peste 1000 RON</li>
                                <li><strong>20 RON</strong> - Livrare standard pentru comenzi sub 1000 RON</li>
                                <li><strong>35 RON</strong> - Livrare express (doar București)</li>
                                <li><strong>15 RON</strong> - Taxa suplimentară pentru ramburs</li>
                            </ul>

                            <h3 class="terms-subsection-title">6.3. Zona de Livrare</h3>
                            <p>Momentan livrăm doar în România. Nu livrăm în afara țării.</p>

                            <h3 class="terms-subsection-title">6.4. Verificarea Coletului la Primire</h3>
                            <p><strong>Recomandăm FERM să filmați procesul de deschidere a coletului!</strong></p>
                            <p>Pentru protecția dumneavoastră în cazul unor probleme la livrare, vă recomandăm să realizați o înregistrare video continuă care să includă:</p>
                            <ul class="terms-list">
                                <li>AWB-ul vizibil pe colet (înainte de deschidere)</li>
                                <li>Sigiliul intact/deteriorat al coletului</li>
                                <li>Procesul complet de desfacere (într-o singură înregistrare, fără pauze)</li>
                                <li>Conținutul coletului (produs + accesorii)</li>
                                <li>Verificarea stării produsului (toate laturile)</li>
                                <li>Pornirea produsului și verificarea IMEI-ului (*#06#)</li>
                            </ul>
                            
                            <p class="terms-note"><strong>De ce este important?</strong> Videoclipul vă protejează în cazul în care:</p>
                            <ul class="terms-list">
                                <li>Produsul livrat nu corespunde comenzii</li>
                                <li>Produsul are defecte la livrare</li>
                                <li>Coletul a fost deschis anterior (sigiliu rupt)</li>
                                <li>Lipsesc accesorii din comandă</li>
                            </ul>
                            
                            <p><strong>Fără dovadă video la deschiderea coletului, reclamațiile pentru produse diferite sau deteriorate la livrare vor fi greu de dovedit.</strong></p>

                            <h3 class="terms-subsection-title">6.5. Risc de Pierdere sau Deteriorare</h3>
                            <p>Riscul de pierdere sau deteriorare a produsului se transferă către dvs. în momentul predării produsului de către curier. În cazul în care produsul este deteriorat la livrare sau coletul prezintă semne de deschidere, vă rugăm să:</p>
                            <ul class="terms-list">
                                <li>Refuzați coletul în prezența curierului (dacă este evident deteriorat)</li>
                                <li>Notați pe procesul verbal orice deteriorare a coletului</li>
                                <li>Contactați-ne imediat la <a href="mailto:support@haloo.ro" class="terms-link">support@haloo.ro</a></li>
                                <li>Trimiteți videoclipul de la deschiderea coletului</li>
                            </ul>
                        </div>
                    </section>

                    <!-- Returns Section -->
                    <section class="terms-section">
                        <h2 class="terms-section-title">7. Retururi și Rambursări</h2>
                        <div class="terms-section-content">
                            <h3 class="terms-subsection-title">7.1. Dreptul de Retur</h3>
                            <p>Conform legislației în vigoare, aveți dreptul de a returna produsul în termen de <strong>14 zile</strong> calendaristice de la data primirii, fără a fi nevoie să indicați motivul returului.</p>
                            <p><strong>Haloo vă oferă un termen extins de retur de 30 de zile</strong> de la data livrării produsului, oferindu-vă mai mult timp pentru a vă asigura că produsul corespunde așteptărilor.</p>

                            <h3 class="terms-subsection-title">7.2. Condiții pentru Retur</h3>
                            <p>Pentru ca returul să fie acceptat, produsul trebuie să fie:</p>
                            <ul class="terms-list">
                                <li>În starea în care l-ați primit (fără deteriorări suplimentare)</li>
                                <li>Cu toate accesoriile incluse (cablu, încărcător, cutie, manuale, etc.)</li>
                                <li>Cu ambalajul original (dacă este posibil)</li>
                                <li>Fără utilizare excesivă sau deteriorare</li>
                                <li><strong>Deblocat, fără parole</strong> (resetat la setări fabrică - Factory Reset)</li>
                                <li>Fără modificări software (jailbreak, root) sau hardware neautorizate</li>
                            </ul>
                            
                            <h3 class="terms-subsection-title">7.3. Procedura OBLIGATORIE de Retur cu Dovadă Video</h3>
                            <p class="terms-highlight"><strong>Pentru protecția ambelor părți și prevenirea fraudelor, returnarea produselor TREBUIE însoțită de dovadă video, conform procedurii de mai jos.</strong></p>
                            
                            <p><strong>PASUL 1: Video la Primirea Coletului (recomandat)</strong></p>
                            <p>La primirea comenzii, recomandăm să filmați procesul de deschidere a coletului într-o singură înregistrare video continuă, fără întreruperi, care să includă:</p>
                            <ul class="terms-list">
                                <li>AWB-ul vizibil pe colet (înainte de deschidere)</li>
                                <li>Sigiliul intact al coletului</li>
                                <li>Procesul complet de desfacere a coletului</li>
                                <li>Extragerea produsului din ambalaj</li>
                                <li>Verificarea vizuală a produsului (față, spate, laterale)</li>
                                <li>Pornirea produsului și verificarea funcționalității de bază</li>
                            </ul>
                            <p class="terms-note"><em>Acest video vă protejează în cazul în care produsul are defecte la livrare.</em></p>
                            
                            <p><strong>PASUL 2: Video la Returnare (OBLIGATORIU)</strong></p>
                            <p>În cazul în care doriți să returnați produsul, conform dreptului de retur, sunteți <strong>OBLIGAT</strong> să realizați o înregistrare video continuă, fără întreruperi sau editări, care să conțină:</p>
                            <ol class="terms-list">
                                <li><strong>Identificare comandă:</strong> AWB-ul original vizibil sau numărul comenzii</li>
                                <li><strong>Verificare produs:</strong>
                                    <ul>
                                        <li>Număr IMEI vizibil pe telefon (Setări > Despre telefon sau *#06#)</li>
                                        <li>Număr de serie (dacă există pe dispozitiv)</li>
                                        <li>Starea fizică a produsului (față, spate, laterale) - filmați toate laturile</li>
                                        <li>Produsul pornit și funcțional</li>
                                    </ul>
                                </li>
                                <li><strong>Resetare fabrică:</strong> Procesul complet de Factory Reset filmat (Setări > Reset > Șterge toate datele)</li>
                                <li><strong>Produsul deblocat:</strong> Demonstrați că produsul pornește fără PIN/parole</li>
                                <li><strong>Ambalare:</strong> Plasarea produsului și accesoriilor în cutie/ambalaj</li>
                                <li><strong>Sigilare:</strong> Sigilarea completă a coletului pentru retur</li>
                                <li><strong>AWB retur:</strong> Lipirea etichetei AWB de retur pe colet (vizibilă)</li>
                            </ol>
                            
                            <p class="terms-warning"><strong>⚠️ ATENȚIE:</strong> Înregistrarea video trebuie să fie:</p>
                            <ul class="terms-list">
                                <li>Continuă, fără pauze sau tăieturi (filmați într-o singură înregistrare)</li>
                                <li>Clară și cu lumină suficientă (IMEI și detaliile să fie vizibile)</li>
                                <li>Cu timestamp vizibil (data și ora) - recomandăm aplicații cu watermark</li>
                                <li>Fără editări sau modificări</li>
                            </ul>
                            
                            <p><strong>Upload video:</strong> După realizarea înregistrării, încărcați videoclipul pe:</p>
                            <ul class="terms-list">
                                <li>Google Drive / Dropbox / WeTransfer (link partajat)</li>
                                <li>Sau trimiteți-l prin <a href="mailto:support@haloo.ro" class="terms-link">support@haloo.ro</a> (dacă dimensiunea permite)</li>
                            </ul>
                            
                            <p class="terms-warning"><strong>🚫 IMPORTANT:</strong> Retururile fără dovadă video conformă acestei proceduri <strong>NU VOR FI ACCEPTATE</strong>. Ne rezervăm dreptul de a refuza rambursarea în cazul lipsei dovezii video sau în cazul în care videoclipul nu respectă condițiile menționate (editări, pauze, detalii invizibile).</p>

                            <h3 class="terms-subsection-title">7.4. Verificare Identitate Produs la Retur</h3>
                            <p>La primirea produsului returnat, vom verifica:</p>
                            <ul class="terms-list">
                                <li><strong>IMEI:</strong> Numărul IMEI trebuie să corespundă cu cel înregistrat la comandă</li>
                                <li><strong>Serie:</strong> Numărul de serie trebuie să corespundă cu produsul livrat</li>
                                <li><strong>Stare:</strong> Produsul returnat trebuie să fie în aceeași stare ca la livrare</li>
                                <li><strong>Conformitate video:</strong> Produsul returnat trebuie să corespundă cu cel din videoclipul de returnare</li>
                            </ul>
                            
                            <p class="terms-warning"><strong>🚫 NU SE ACCEPTĂ RETUR dacă:</strong></p>
                            <ul class="terms-list">
                                <li>IMEI-ul nu corespunde cu cel înregistrat la comandă</li>
                                <li>Produsul returnat este diferit de cel livrat (swap fraud)</li>
                                <li>Produsul are deteriorări noi față de starea la livrare</li>
                                <li>Produsul are componente înlocuite neautorizat</li>
                                <li>Lipsește dovada video conform procedurii de la art. 7.3</li>
                                <li>Videoclipul prezintă editări, tăieturi sau este incomplet</li>
                            </ul>
                            
                            <p class="terms-note">În cazul detectării unei tentative de fraudă (returnare produs diferit, componente înlocuite, deteriorare intenționată), ne rezervăm dreptul de a:</p>
                            <ul class="terms-list">
                                <li>Refuza rambursarea sumelor</li>
                                <li>Reține produsul original</li>
                                <li>Sesiza autoritățile competente</li>
                                <li>Solicita acoperirea prejudiciilor suferite</li>
                            </ul>

                            <h3 class="terms-subsection-title">7.5. Costuri de Retur</h3>
                            <p>Retururile sunt gratuite dacă produsul este defect sau nu corespunde descrierii (cu dovadă video la deschiderea coletului). În alte cazuri, costurile de retur pot fi suportate de dvs., conform legislației.</p>

                            <h3 class="terms-subsection-title">7.6. Rambursarea</h3>
                            <p>După primirea, verificarea identității (IMEI) și a stării produsului returnat, conform videoclipului furnizat, vă vom rambursa suma plătită în termen de 14 zile lucrătoare, folosind aceeași metodă de plată utilizată pentru comandă.</p>
                            <p>Rambursarea va include costul produsului, dar nu și costurile inițiale de livrare (dacă nu este vorba despre un produs defect sau care nu corespunde descrierii).</p>
                            
                            <p class="terms-note"><strong>Notă:</strong> În cazul în care produsul returnat prezintă diferențe față de cel livrat sau videoclipul nu respectă procedura, rambursarea va fi suspendată până la clarificarea situației. Dacă se confirmă tentativa de fraudă, rambursarea va fi refuzată.</p>
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
                                <li>Probleme cauzate de instalare software neautorizat (jailbreak, root) sau modificări hardware</li>
                                <li>Uzura normală (zgârieturi superficiale, uzură a bateriei în timp)</li>
                                <li>Daune cauzate de utilizare necorespunzătoare</li>
                                <li>Utilizarea unor încărcătoare și cabluri neoriginale sau incompatibile</li>
                            </ul>
                            
                            <h3 class="terms-subsection-title">8.4. Garanția Bateriilor</h3>
                            <p>Bateriile sunt componente consumabile. Garanția acoperă doar:</p>
                            <ul class="terms-list">
                                <li>Baterii umflate</li>
                                <li>Baterii care nu permit aprinderea dispozitivului</li>
                                <li>Defecte de fabricație sau construcție</li>
                            </ul>
                            <p><strong>Notă:</strong> Diminuarea naturală a capacității bateriei (degradare în timp) este un proces normal și NU reprezintă un defect acoperit de garanție.</p>

                            <h3 class="terms-subsection-title">8.5. Procedura de Garanție</h3>
                            <p>Pentru a solicita servicii în garanție:</p>
                            <ol class="terms-list">
                                <li>Contactați-ne la <a href="mailto:support@haloo.ro" class="terms-link">support@haloo.ro</a> sau <a href="tel:+40754025905" class="terms-link">0754 025 905</a></li>
                                <li>Veți primi instrucțiuni detaliate pentru returnare</li>
                                <li>Trimiteți produsul pentru verificare (livrare gratuită în garanție)</li>
                                <li>Verificăm și reparăm/înlocuim conform termenilor garanției</li>
                                <li>Returnăm produsul reparat (termen: 5-10 zile lucrătoare)</li>
                            </ol>
                            <p><strong>Important:</strong> Înainte de a trimite produsul în garanție, asigurați-vă că ați șters toate datele personale și l-ați deblocat.</p>
                        </div>
                    </section>

                    <!-- Customer Responsibilities Section -->
                    <section class="terms-section">
                        <h2 class="terms-section-title">8A. Obligațiile și Răspunderile Clientului</h2>
                        <div class="terms-section-content">
                            <h3 class="terms-subsection-title">8A.1. Declarații și Garanții ale Clientului</h3>
                            <p>Prin plasarea unei comenzi și prin exercitarea dreptului de retur, Clientul declară și garantează că:</p>
                            <ul class="terms-list">
                                <li>Va furniza informații corecte, complete și reale</li>
                                <li>Va returna exact același produs primit (cu același IMEI)</li>
                                <li>Nu va deteriora intenționat produsul</li>
                                <li>Nu va înlocui componente interne</li>
                                <li>Va respecta procedura video obligatorie la retururi</li>
                                <li>Nu va încerca să fraudeze sau să inducă în eroare Haloo</li>
                            </ul>
                            
                            <h3 class="terms-subsection-title">8A.2. Răspunderea pentru Informații False</h3>
                            <p>Clientul este integral răspunzător pentru:</p>
                            <ul class="terms-list">
                                <li>Corectitudinea datelor furnizate (nume, adresă, telefon, email)</li>
                                <li>Autenticitatea reclamațiilor făcute</li>
                                <li>Veridicitatea informațiilor din cererile de retur</li>
                            </ul>
                            <p>Furnizarea de informații false sau tentativa de fraudă poate atrage:</p>
                            <ul class="terms-list">
                                <li>⚠️ Răspundere civilă (despăgubiri pentru prejudicii)</li>
                                <li>⚠️ Răspundere penală (escrocherie - art. 244 Cod Penal)</li>
                                <li>⚠️ Blocarea permanentă a contului</li>
                                <li>⚠️ Refuzul procesării comenzilor viitoare</li>
                            </ul>
                            
                            <h3 class="terms-subsection-title">8A.3. Înregistrare Video - Protecție Reciprocă</h3>
                            <p><strong>De ce cerem video la retururi?</strong></p>
                            <p>Experiența noastră în e-commerce a identificat cazuri reale de:</p>
                            <ul class="terms-list">
                                <li>Clienți care returnează telefoane diferite (iPhone 11 în loc de iPhone 14 Pro)</li>
                                <li>Returnare cărți, machete sau cutii goale în loc de telefoane (dummy returns)</li>
                                <li>Înlocuirea componentelor interne cu altele defecte</li>
                                <li>Deteriorare intenționată pentru a forța rambursare</li>
                            </ul>
                            <p>Aceste fraude costă mii de lei lunar și cresc prețurile pentru toți clienții onești.</p>
                            
                            <p><strong>Videoclipul vă protejează și pe dumneavoastră:</strong></p>
                            <ul class="terms-list">
                                <li>Demonstrați că ați primit produsul corect sau defect</li>
                                <li>Dovediți starea produsului la primire</li>
                                <li>Confirmați că returnați exact ce ați primit</li>
                                <li>Evitați dispute și întârzieri în procesarea returului</li>
                            </ul>
                            
                            <p class="terms-highlight"><strong>Win-win:</strong> Clienții onești sunt protejați, fraudele sunt prevenite, prețurile rămân accesibile pentru toată lumea.</p>
                        </div>
                    </section>

                    <!-- Privacy Section -->
                    <section class="terms-section">
                        <h2 class="terms-section-title">9. Confidențialitate și Protecția Datelor</h2>
                        <div class="terms-section-content">
                            <p>Protecția datelor dvs. personale este importantă pentru noi. Procesarea datelor dvs. personale se face în conformitate cu Regulamentul General privind Protecția Datelor (GDPR) și legislația română în vigoare.</p>
                            <p>Pentru detalii complete despre modul în care colectăm, utilizăm și protejăm datele dvs. personale, vă rugăm să consultați <a href="<?php echo home_url('/politica-de-confidentialitate'); ?>" class="terms-link">Politica noastră de Confidențialitate</a>.</p>
                            
                            <h3 class="terms-subsection-title">9.1. Stocarea Datelor IMEI și Serie</h3>
                            <p>În scopul prevenirii fraudelor și protejării ambelor părți, stocăm:</p>
                            <ul class="terms-list">
                                <li>Numărul IMEI al fiecărui dispozitiv vândut</li>
                                <li>Numărul de serie</li>
                                <li>Caracteristici hardware unice</li>
                                <li>Fotografii ale produsului la expediere</li>
                                <li>Date despre starea tehnică și estetică</li>
                            </ul>
                            <p>Aceste date sunt utilizate exclusiv pentru verificarea conformității produselor returnate și nu sunt partajate cu terți, cu excepția autorităților competente în cazul investigațiilor de fraudă.</p>
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
                                <li>Retururi refuzate din lipsa dovezii video sau neconformități cu procedura</li>
                                <li>Prejudicii rezultate din furnizarea de informații false sau incomplete</li>
                            </ul>
                            <p>Răspunderea noastră totală față de dvs. nu va depăși suma plătită pentru produsul respectiv.</p>
                            
                            <h3 class="terms-subsection-title">11.1. Disclaimer Legal privind Procedura Video</h3>
                            <p>Procedura obligatorie de înregistrare video la retururi este implementată conform:</p>
                            <ul class="terms-list">
                                <li>Art. 1357 Cod Civil - Sarcina probei în contracte</li>
                                <li>OUG 34/2014 - Drepturile consumatorilor (art. 16 - condiții retur)</li>
                                <li>Necesitatea de a preveni fraude comerciale</li>
                                <li>Protecția ambelor părți contractante</li>
                            </ul>
                            <p><strong>Clarificare importantă:</strong> Cerința dovezii video nu încalcă drepturile consumatorului. Consumatorul păstrează dreptul de retragere din contract în 14 zile (extins de noi la 30 zile), dar trebuie să demonstreze că returnează același produs în aceeași stare, conform art. 16 alin. (1) și (2) din OUG 34/2014.</p>
                            <p>Dovada video este o metodă rezonabilă și proporțională de verificare a identității și stării produsului returnat, având în vedere:</p>
                            <ul class="terms-list">
                                <li>Valoarea ridicată a produselor (1000-5000 RON)</li>
                                <li>Riscul real de fraude în e-commerce (înlocuire produse)</li>
                                <li>Imposibilitatea verificării fizice la distanță</li>
                                <li>Protecția interesului legitim al comerciantului</li>
                            </ul>
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
                                <li><strong>Companie:</strong> FRUIT CREATIVE SRL</li>
                                <li><strong>CUI:</strong> 39066744</li>
                                <li><strong>Nr. Reg. Com.:</strong> J2020005512236</li>
                                <li><strong>Email general:</strong> <a href="mailto:contact@haloo.ro" class="terms-link">contact@haloo.ro</a></li>
                                <li><strong>Suport clienți:</strong> <a href="mailto:support@haloo.ro" class="terms-link">support@haloo.ro</a></li>
                                <li><strong>Telefon:</strong> <a href="tel:+40754025905" class="terms-link">0754 025 905</a></li>
                                <li><strong>Program:</strong> Luni-Vineri: 08:00-21:00</li>
                            </ul>
                        </div>
                    </section>

                    <!-- Governing Law Section -->
                    <section class="terms-section">
                        <h2 class="terms-section-title">14. Legea Aplicabilă și Jurisdicția</h2>
                        <div class="terms-section-content">
                            <p>Acești termeni și condiții sunt guvernați și interpretați în conformitate cu legile României. Orice dispută care ar putea apărea în legătură cu acești termeni sau cu utilizarea site-ului va fi rezolvată de instanțele competente din România.</p>
                            
                            <h3 class="terms-subsection-title">14.1. Soluționare Alternativă a Litigiilor (SAL)</h3>
                            <p>Consumatorii pot apela la proceduri de soluționare alternativă a litigiilor:</p>
                            <ul class="terms-list">
                                <li><strong>Platforma SAL ANPC:</strong> <a href="https://anpc.ro/ce-este-sal/" target="_blank" rel="noopener" class="terms-link">https://anpc.ro/ce-este-sal/</a></li>
                                <li><strong>Platforma SOL Uniunea Europeană:</strong> <a href="https://ec.europa.eu/consumers/odr/" target="_blank" rel="noopener" class="terms-link">https://ec.europa.eu/consumers/odr/</a></li>
                            </ul>
                        </div>
                    </section>

                    <!-- Fraud Prevention Section -->
                    <section class="terms-section">
                        <h2 class="terms-section-title">15. Prevenirea Fraudelor și Protecția Ambelor Părți</h2>
                        <div class="terms-section-content">
                            <h3 class="terms-subsection-title">15.1. Înregistrarea și Verificarea IMEI</h3>
                            <p>Pentru fiecare produs livrat, înregistrăm:</p>
                            <ul class="terms-list">
                                <li>Numărul IMEI unic al dispozitivului</li>
                                <li>Numărul de serie</li>
                                <li>Caracteristicile hardware specifice</li>
                                <li>Starea exactă la momentul expedierii (fotografii, inspecție tehnică)</li>
                            </ul>
                            <p>Aceste date sunt stocate securizat și folosite pentru verificarea produselor returnate.</p>
                            
                            <h3 class="terms-subsection-title">15.2. Cazuri de Fraudă Identificate</h3>
                            <p>Ne rezervăm dreptul de a refuza returul și de a sesiza autoritățile în următoarele situații:</p>
                            <ul class="terms-list">
                                <li><strong>Swap fraud:</strong> Returnare produs diferit (alt model, altă marcă, placă de bază diferită)</li>
                                <li><strong>Component swap:</strong> Înlocuirea componentelor interne (ecran, cameră, baterie) cu altele de calitate inferioară</li>
                                <li><strong>Dummy return:</strong> Returnare obiecte fără valoare (cărți, cutii goale, machete)</li>
                                <li><strong>Damage fraud:</strong> Deteriorare intenționată a produsului pentru a solicita retur</li>
                                <li><strong>IMEI mismatch:</strong> IMEI diferit față de cel înregistrat la comandă</li>
                            </ul>
                            
                            <h3 class="terms-subsection-title">15.3. Consecințele Fraudei</h3>
                            <p>În cazul detectării unei tentative de fraudă:</p>
                            <ul class="terms-list">
                                <li>❌ Returul este refuzat</li>
                                <li>❌ Rambursarea este refuzată</li>
                                <li>⚠️ Produsul original este reținut (dacă diferă de cel returnat)</li>
                                <li>⚠️ Contul utilizatorului este blocat permanent</li>
                                <li>⚠️ Sesizare la autoritățile competente (Poliție, Parchet)</li>
                                <li>⚠️ Acțiune în justiție pentru recuperarea prejudiciilor</li>
                            </ul>
                            
                            <h3 class="terms-subsection-title">15.4. Angajamentul Haloo</h3>
                            <p>Ne angajăm să:</p>
                            <ul class="terms-list">
                                <li>✅ Livrăm exact produsul comandat (verificat, testat, cu IMEI înregistrat)</li>
                                <li>✅ Respectăm grading-ul și specificațiile afișate</li>
                                <li>✅ Procesăm retururile legitime rapid și corect</li>
                                <li>✅ Protejăm clienții onești de creșterea prețurilor cauzate de fraude</li>
                            </ul>
                            
                            <p class="terms-note"><em>Procedura video protejează atât clientul (în caz de probleme la livrare) cât și pe noi (în caz de tentative de fraudă la returnare). Transparency works both ways.</em></p>

                            <h3 class="terms-subsection-title">7.5. Costuri de Retur</h3>
                            <p>Retururile sunt gratuite dacă produsul este defect sau nu corespunde descrierii (cu dovadă video la deschiderea coletului). În alte cazuri (schimbare opțiune, renunțare la achiziție), costurile de retur pot fi suportate de dvs., conform legislației.</p>

                            <h3 class="terms-subsection-title">7.6. Rambursarea</h3>
                            <p>După primirea, verificarea identității (IMEI), a stării produsului returnat și a conformității cu videoclipul furnizat, vă vom rambursa suma plătită în termen de 14 zile lucrătoare, folosind aceeași metodă de plată utilizată pentru comandă.</p>
                            <p>Rambursarea va include costul produsului, dar nu și costurile inițiale de livrare (dacă nu este vorba despre un produs defect sau care nu corespunde descrierii).</p>
                            <p class="terms-note"><strong>Notă:</strong> În cazul detectării unor diferențe între produsul livrat și cel returnat, sau în lipsa dovezii video, rambursarea va fi suspendată până la clarificarea situației. Dacă se confirmă tentativa de fraudă, rambursarea va fi refuzată definitiv.</p>
                        </div>
                    </section>

                    <!-- Acceptance Section -->
                    <section class="terms-section">
                        <h2 class="terms-section-title">16. Acceptarea Termenilor</h2>
                        <div class="terms-section-content">
                            <p>Prin accesarea și utilizarea site-ului haloo.ro, prin crearea unui cont și prin plasarea unei comenzi, confirmați că:</p>
                            <ul class="terms-list">
                                <li>✅ Ați citit, înțeles și acceptat în totalitate acești termeni și condiții</li>
                                <li>✅ Sunteți de acord cu procedura obligatorie de dovadă video la retururi</li>
                                <li>✅ Înțelegeți consecințele tentativelor de fraudă</li>
                                <li>✅ Veți furniza informații corecte și reale</li>
                                <li>✅ Veți utiliza site-ul în mod onest și conform legii</li>
                                <li>✅ Acceptați verificarea IMEI și a identității produselor returnate</li>
                            </ul>
                            <p><strong>Dacă nu sunteți de acord cu oricare dintre acești termeni, inclusiv cu procedura video obligatorie la retururi, vă rugăm să nu utilizați site-ul nostru și să nu plasați comenzi.</strong></p>
                            
                            <p class="terms-highlight">Prin bifarea căsuței "Accept Termenii și Condițiile" la checkout, declarați pe propria răspundere că ați citit și sunteți de acord cu toate prevederile, inclusiv cu obligația de a furniza dovadă video în cazul retururilor.</p>
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

