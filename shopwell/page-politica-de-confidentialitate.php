<?php
/**
 * Template Name: Politica de Confidențialitate
 * 
 * Privacy Policy Page Template
 *
 * @package Shopwell
 */

get_header();
?>

<!-- CSS moved to external file: assets/css/pages/politica-de-confidentialitate.css -->

<div class="site-content-container">
    <div id="primary" class="content-area">
        <main id="main" class="site-main">
            
            <!-- Hero Section -->
            <section class="privacy-hero">
                <div class="privacy-container">
                    <div class="privacy-header">
                        <h1 class="privacy-title">Politica de Confidențialitate</h1>
                        <p class="privacy-subtitle">Ultima actualizare: <?php echo date('d.m.Y'); ?></p>
                    </div>
                </div>
            </section>

            <!-- Content -->
            <div class="privacy-content">
                <div class="privacy-container">
                    
                    <!-- Introduction Section -->
                    <section class="privacy-section">
                        <h2 class="privacy-section-title">1. Introducere</h2>
                        <div class="privacy-section-content">
                            <p>La Haloo, protecția datelor dvs. personale este o prioritate. Această Politică de Confidențialitate explică cum colectăm, utilizăm, stocăm și protejăm informațiile dvs. personale când utilizați site-ul haloo.ro.</p>
                            <p>Această politică este în conformitate cu Regulamentul General privind Protecția Datelor (GDPR) și legislația română privind protecția datelor personale.</p>
                        </div>
                    </section>

                    <!-- Data Controller Section -->
                    <section class="privacy-section">
                        <h2 class="privacy-section-title">2. Operatorul de Date</h2>
                        <div class="privacy-section-content">
                            <p>Operatorul de date personale este:</p>
                            <ul class="privacy-list">
                                <li><strong>Denumire:</strong> [Numele Companiei]</li>
                                <li><strong>Adresă:</strong> [Adresa completă]</li>
                                <li><strong>Email:</strong> <a href="mailto:contact@haloo.ro" class="privacy-link">contact@haloo.ro</a></li>
                                <li><strong>Telefon:</strong> <a href="tel:+40123456789" class="privacy-link">+40 (123) 456-7890</a></li>
                            </ul>
                        </div>
                    </section>

                    <!-- Data Collection Section -->
                    <section class="privacy-section">
                        <h2 class="privacy-section-title">3. Datele Colectate</h2>
                        <div class="privacy-section-content">
                            <h3 class="privacy-subsection-title">3.1. Date Colectate Automat</h3>
                            <p>Când accesați site-ul nostru, colectăm automat următoarele informații:</p>
                            <ul class="privacy-list">
                                <li>Adresa IP</li>
                                <li>Tipul de browser și versiunea</li>
                                <li>Sistemul de operare</li>
                                <li>Pagina de referință</li>
                                <li>Data și ora accesării</li>
                                <li>Pagina accesată</li>
                            </ul>

                            <h3 class="privacy-subsection-title">3.2. Date Furnizate de Dvs.</h3>
                            <p>Colectăm următoarele date când vă creați un cont sau plasați o comandă:</p>
                            <ul class="privacy-list">
                                <li><strong>Date de identificare:</strong> Nume, prenume, email, telefon</li>
                                <li><strong>Date de facturare:</strong> Adresă de facturare, CNP (dacă este necesar pentru factură)</li>
                                <li><strong>Date de livrare:</strong> Adresă de livrare</li>
                                <li><strong>Date de plată:</strong> Informații despre cardul de credit (procesate securizat prin gateway-uri certificate, nu stocăm datele cardului)</li>
                            </ul>

                            <h3 class="privacy-subsection-title">3.3. Cookie-uri</h3>
                            <p>Utilizăm cookie-uri pentru a îmbunătăți experiența dvs. pe site. Pentru detalii complete, consultați <a href="<?php echo home_url('/politica-de-cookie-uri'); ?>" class="privacy-link">Politica noastră de Cookie-uri</a>.</p>
                        </div>
                    </section>

                    <!-- Data Usage Section -->
                    <section class="privacy-section">
                        <h2 class="privacy-section-title">4. Utilizarea Datelor</h2>
                        <div class="privacy-section-content">
                            <h3 class="privacy-subsection-title">4.1. Scopuri de Utilizare</h3>
                            <p>Utilizăm datele dvs. personale pentru următoarele scopuri:</p>
                            <ul class="privacy-list">
                                <li><strong>Procesarea comenzilor:</strong> Pentru a procesa și livra comenzile dvs.</li>
                                <li><strong>Comunicare:</strong> Pentru a vă trimite confirmări de comandă, actualizări despre livrare și comunicări legate de serviciile noastre</li>
                                <li><strong>Îmbunătățirea serviciilor:</strong> Pentru a analiza utilizarea site-ului și a îmbunătăți serviciile noastre</li>
                                <li><strong>Marketing:</strong> Pentru a vă trimite oferte și promoții (doar cu consimțământul dvs.)</li>
                                <li><strong>Conformitate legală:</strong> Pentru a respecta obligațiile legale și de reglementare</li>
                                <li><strong>Securitate:</strong> Pentru a preveni frauda și a asigura securitatea site-ului</li>
                            </ul>

                            <h3 class="privacy-subsection-title">4.2. Baza Legală pentru Procesare</h3>
                            <p>Procesăm datele dvs. personale pe baza următoarelor:</p>
                            <ul class="privacy-list">
                                <li><strong>Executarea contractului:</strong> Pentru procesarea și livrarea comenzilor</li>
                                <li><strong>Consimțământul:</strong> Pentru marketing și cookie-uri (unde este cazul)</li>
                                <li><strong>Interesul legitim:</strong> Pentru îmbunătățirea serviciilor și securitatea</li>
                                <li><strong>Obligații legale:</strong> Pentru conformitatea cu legislația aplicabilă</li>
                            </ul>
                        </div>
                    </section>

                    <!-- Data Sharing Section -->
                    <section class="privacy-section">
                        <h2 class="privacy-section-title">5. Partajarea Datelor</h2>
                        <div class="privacy-section-content">
                            <h3 class="privacy-subsection-title">5.1. Parteneri de Încredere</h3>
                            <p>Putem partaja datele dvs. cu următorii parteneri de încredere:</p>
                            <ul class="privacy-list">
                                <li><strong>Furnizori de servicii de livrare:</strong> Pentru livrarea comenzilor (FAN Courier, DPD, GLS)</li>
                                <li><strong>Procesatori de plăți:</strong> Pentru procesarea plăților (gateway-uri certificate)</li>
                                <li><strong>Furnizori de servicii IT:</strong> Pentru hosting, securitate și suport tehnic</li>
                                <li><strong>Servicii de marketing:</strong> Doar cu consimțământul dvs. explicit</li>
                            </ul>

                            <h3 class="privacy-subsection-title">5.2. Obligații ale Partenerilor</h3>
                            <p>Toți partenerii noștri sunt obligați să:</p>
                            <ul class="privacy-list">
                                <li>Proceseze datele doar în conformitate cu instrucțiunile noastre</li>
                                <li>Implementeze măsuri de securitate adecvate</li>
                                <li>Nu partajeze datele cu terți fără consimțământul nostru</li>
                            </ul>

                            <h3 class="privacy-subsection-title">5.3. Partajare Fără Consimțământ</h3>
                            <p>Putem partaja datele dvs. fără consimțământul dvs. doar în următoarele cazuri:</p>
                            <ul class="privacy-list">
                                <li>La cererea autorităților competente (conform legii)</li>
                                <li>Pentru protejarea drepturilor noastre legale</li>
                                <li>În cazul unei fuziuni, achiziții sau vânzări de active</li>
                            </ul>
                        </div>
                    </section>

                    <!-- Data Security Section -->
                    <section class="privacy-section">
                        <h2 class="privacy-section-title">6. Securitatea Datelor</h2>
                        <div class="privacy-section-content">
                            <h3 class="privacy-subsection-title">6.1. Măsuri de Securitate</h3>
                            <p>Implementăm măsuri tehnice și organizatorice pentru a proteja datele dvs. personale:</p>
                            <ul class="privacy-list">
                                <li>Criptare SSL/TLS pentru toate transmisiile de date</li>
                                <li>Acces restricționat la date (doar personal autorizat)</li>
                                <li>Backup-uri regulate și securizate</li>
                                <li>Actualizări de securitate regulate</li>
                                <li>Monitorizare continuă pentru activități suspecte</li>
                            </ul>

                            <h3 class="privacy-subsection-title">6.2. Stocarea Datelor</h3>
                            <p>Datele dvs. sunt stocate pe servere securizate situate în:</p>
                            <ul class="privacy-list">
                                <li>România (preferat)</li>
                                <li>Sau în țări din Spațiul Economic European (SEE) cu nivel adecvat de protecție</li>
                            </ul>

                            <h3 class="privacy-subsection-title">6.3. Perioada de Stocare</h3>
                            <p>Păstrăm datele dvs. personale doar atât timp cât este necesar pentru:</p>
                            <ul class="privacy-list">
                                <li>Executarea contractului (până la finalizarea comenzii și perioada de garanție)</li>
                                <li>Obligații legale (conform legislației contabile și fiscale)</li>
                                <li>Interesul legitim (pentru îmbunătățirea serviciilor)</li>
                            </ul>
                            <p>După expirarea perioadei de stocare, datele vor fi șterse sau anonimizate în siguranță.</p>
                        </div>
                    </section>

                    <!-- User Rights Section -->
                    <section class="privacy-section">
                        <h2 class="privacy-section-title">7. Drepturile Dvs.</h2>
                        <div class="privacy-section-content">
                            <p>Conform GDPR, aveți următoarele drepturi:</p>
                            
                            <h3 class="privacy-subsection-title">7.1. Dreptul de Acces</h3>
                            <p>Aveți dreptul să solicitați o copie a datelor dvs. personale pe care le deținem.</p>

                            <h3 class="privacy-subsection-title">7.2. Dreptul la Rectificare</h3>
                            <p>Aveți dreptul să solicitați corectarea datelor inexacte sau incomplete.</p>

                            <h3 class="privacy-subsection-title">7.3. Dreptul la Ștergere</h3>
                            <p>Aveți dreptul să solicitați ștergerea datelor dvs. personale în anumite circumstanțe (dreptul de a fi uitat).</p>

                            <h3 class="privacy-subsection-title">7.4. Dreptul la Restricționarea Procesării</h3>
                            <p>Aveți dreptul să solicitați restricționarea procesării datelor dvs. în anumite circumstanțe.</p>

                            <h3 class="privacy-subsection-title">7.5. Dreptul la Portabilitatea Datelor</h3>
                            <p>Aveți dreptul să solicitați transferul datelor dvs. către un alt operator.</p>

                            <h3 class="privacy-subsection-title">7.6. Dreptul de Opoziție</h3>
                            <p>Aveți dreptul să vă opuneți procesării datelor dvs. pentru marketing direct sau pentru interes legitim.</p>

                            <h3 class="privacy-subsection-title">7.7. Dreptul de a Retrage Consimțământul</h3>
                            <p>Dacă procesarea se bazează pe consimțământ, aveți dreptul să îl retrageți în orice moment.</p>

                            <h3 class="privacy-subsection-title">7.8. Cum Vă Exercitați Drepturile</h3>
                            <p>Pentru a vă exercita drepturile, contactați-ne la:</p>
                            <ul class="privacy-list">
                                <li><strong>Email:</strong> <a href="mailto:contact@haloo.ro" class="privacy-link">contact@haloo.ro</a></li>
                                <li><strong>Telefon:</strong> <a href="tel:+40123456789" class="privacy-link">+40 (123) 456-7890</a></li>
                            </ul>
                            <p>Vă vom răspunde în termen de 30 de zile de la primirea cererii.</p>
                        </div>
                    </section>

                    <!-- Cookies Section -->
                    <section class="privacy-section">
                        <h2 class="privacy-section-title">8. Cookie-uri</h2>
                        <div class="privacy-section-content">
                            <p>Utilizăm cookie-uri pentru a îmbunătăți experiența dvs. pe site. Pentru informații detaliate despre tipurile de cookie-uri pe care le folosim și cum le puteți gestiona, consultați <a href="<?php echo home_url('/politica-de-cookie-uri'); ?>" class="privacy-link">Politica noastră de Cookie-uri</a>.</p>
                        </div>
                    </section>

                    <!-- Children Privacy Section -->
                    <section class="privacy-section">
                        <h2 class="privacy-section-title">9. Confidențialitatea Copiilor</h2>
                        <div class="privacy-section-content">
                            <p>Site-ul nostru nu este destinat persoanelor sub 18 ani. Nu colectăm în mod intenționat date personale de la copii. Dacă devenim conștienți că am colectat date de la un copil fără consimțământul părinților, vom șterge imediat aceste date.</p>
                        </div>
                    </section>

                    <!-- Changes Section -->
                    <section class="privacy-section">
                        <h2 class="privacy-section-title">10. Modificări ale Politicii</h2>
                        <div class="privacy-section-content">
                            <p>Ne rezervăm dreptul de a modifica această Politică de Confidențialitate în orice moment. Modificările vor intra în vigoare imediat după publicarea pe site. Vă recomandăm să verificați periodic această pagină pentru a fi la curent cu orice modificări.</p>
                            <p>În cazul unor modificări semnificative, vă vom notifica prin email sau prin intermediul site-ului.</p>
                        </div>
                    </section>

                    <!-- Contact Section -->
                    <section class="privacy-section">
                        <h2 class="privacy-section-title">11. Contact</h2>
                        <div class="privacy-section-content">
                            <p>Pentru întrebări sau nelămuriri privind această Politică de Confidențialitate sau pentru a vă exercita drepturile, vă rugăm să ne contactați:</p>
                            <ul class="privacy-list">
                                <li><strong>Email:</strong> <a href="mailto:contact@haloo.ro" class="privacy-link">contact@haloo.ro</a></li>
                                <li><strong>Telefon:</strong> <a href="tel:+40123456789" class="privacy-link">+40 (123) 456-7890</a> (Luni-Vineri: 08:00-21:00)</li>
                                <li><strong>Adresă:</strong> [Adresa completă a companiei]</li>
                            </ul>
                            <p>De asemenea, puteți contacta Autoritatea Națională de Supraveghere a Prelucrării Datelor cu Caracter Personal (ANSPDCP) pentru plângeri privind protecția datelor personale.</p>
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

