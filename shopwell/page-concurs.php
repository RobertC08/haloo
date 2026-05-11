<?php
/**
 * Template Name: Concurs Ziua Copilului
 *
 * Concurs promoțional iPhone 17 Pro — campanie 1 Iunie (Ziua Copilului).
 *
 * @package Shopwell
 */

get_header();
?>

<!-- Stiluri: assets/css/pages/concurs.css (încărcat din load-refactored-styles.php) -->

<div class="site-content-container">
    <div id="primary" class="content-area">
        <main id="main" class="site-main" role="main">

<div class="concurs-container">
    <!-- Hero Section -->
    <div class="concurs-hero">
        <div class="concurs-hero-stickers" aria-hidden="true">
            <span class="concurs-hero-sticker concurs-hero-sticker--star">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 80 80" width="80" height="80" fill="none"><path fill="#FDE047" d="M40 4l9.2 22.4L74 30l-18 15.4L60 76 40 62.4 20 76l4-30.6L6 30l24.8-3.6L40 4z"/><path fill="#FACC15" d="M40 18l5.6 13.6L60 34l-11 9.4L52 62l-12-8.2L28 62l3-18.6L20 34l14.4-2.4L40 18z" opacity=".35"/></svg>
            </span>
            <span class="concurs-hero-sticker concurs-hero-sticker--balloon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 72 100" width="72" height="100" fill="none"><path fill="#FDA4AF" d="M36 4C18 4 4 22 4 42c0 18 12 34 28 38v12H18v8h36v-8H40V80c16-4 28-20 28-38C68 22 54 4 36 4z"/><path fill="#2A322F" opacity=".15" d="M34 92h4v8h-4z"/><path fill="#FCA5A5" d="M24 32c4-8 20-8 24 0-6 4-18 4-24 0z" opacity=".4"/></svg>
            </span>
            <span class="concurs-hero-sticker concurs-hero-sticker--gift">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 88 88" width="88" height="88" fill="none"><rect x="12" y="28" width="64" height="48" rx="6" fill="#86EFAC"/><rect x="12" y="20" width="64" height="16" rx="4" fill="#66FA95"/><path fill="#2A322F" opacity=".12" d="M44 20v56"/><rect x="36" y="12" width="16" height="16" rx="4" fill="#FDA4AF"/></svg>
            </span>
        </div>
        <div class="concurs-hero-content">
            <h1>Concurs Haloo de Ziua Copilului <span class="concurs-hero-date">1 iunie 2026</span></h1>
            <div class="iphone-title">iPhone 17 Pro</div>
            <div class="iphone-specs">256GB · CA NOU · 2 ANI GARANȚIE</div>
            <p class="subtitle">Înscrieri deschise <strong>1–30 iunie 2026</strong>. Câștigă un telefon recondiționat premium pentru familia ta — participare gratuită, extragere pe <strong>3 iulie 2026</strong>. Condiții: vârstă minimă <strong>18 ani</strong>, rezidență în România.</p>
        </div>
    </div>

    <!-- Form Section -->
    <div class="concurs-form-container">
        <form id="concursForm" class="concurs-form">
            <?php wp_nonce_field('concurs_form_nonce', '_wpnonce'); ?>
            
            <h2>Completează formularul pentru a participa</h2>
            
            <div class="form-row">
                <div class="form-group form-group-half">
                    <label for="nume">Nume complet *</label>
                    <input type="text" id="nume" name="nume" required>
                </div>
                
                <div class="form-group form-group-half">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group form-group-half">
                    <label for="telefon">Telefon *</label>
                    <input type="tel" id="telefon" name="telefon" required>
                </div>
                
                <div class="form-group form-group-half">
                    <label for="oras">Oraș *</label>
                    <input type="text" id="oras" name="oras" required>
                </div>
            </div>
            
            <div class="checkbox-group">
                <input type="checkbox" id="terms" name="terms" required>
                <label for="terms">
                    Am citit și accept <a href="#termeni">termenii și condițiile</a> concursului *
                </label>
            </div>
            
            <div class="checkbox-group">
                <input type="checkbox" id="privacy" name="privacy" required>
                <label for="privacy">
                    Am citit și accept <a href="https://haloo.ro/politica-de-confidentialitate/" target="_blank">politica de confidențialitate</a> *
                </label>
            </div>
            
            <div class="checkbox-group">
                <input type="checkbox" id="newsletter" name="newsletter">
                <label for="newsletter">
                    Vreau să primesc oferte exclusive și noutăți de la Haloo
                </label>
            </div>
            
            <div class="form-group form-group--motiv">
                <label for="motiv" class="concurs-label--optional">De ce ți-ar fi util acest iPhone de Ziua Copilului? (opțional)</label>
                <textarea id="motiv" name="motiv" rows="3" placeholder="Ex.: pentru copil, cadou în familie sau înlocuire telefon…"></textarea>
                <p class="concurs-hint">Răspunsul tău te ajută să câștigi 1 șansă bonus!</p>
            </div>
            
            <!-- Bonus Activities Section -->
            <div class="bonus-activities">
                <h3>🎁 Câștigă șanse extra!</h3>
                <p>Completează aceste activități pentru a-ți mări șansele de câștig:</p>
                
                <div class="activity-item activity-item-inline">
                    <div class="activity-checkbox-label">
                        <input type="checkbox" id="follow_instagram" name="follow_instagram" class="bonus-checkbox">
                        <label for="follow_instagram">
                            <strong>+2 șanse:</strong> Urmează-ne pe Instagram 
                            <a href="https://instagram.com/haloo.ro" target="_blank">@haloo.ro</a>
                        </label>
                    </div>
                    <input type="text" id="instagram_username" name="instagram_username" placeholder="Username Instagram tău (opțional)" class="activity-input">
                </div>
                
                <div class="activity-item activity-item-inline">
                    <div class="activity-checkbox-label">
                        <input type="checkbox" id="follow_facebook" name="follow_facebook" class="bonus-checkbox">
                        <label for="follow_facebook">
                            <strong>+2 șanse:</strong> Urmează-ne pe Facebook 
                            <a href="https://facebook.com/haloo.ro" target="_blank">Haloo</a>
                        </label>
                    </div>
                    <input type="text" id="facebook_profile" name="facebook_profile" placeholder="Link profil Facebook (opțional)" class="activity-input">
                </div>
                
                <div class="activity-item activity-item-stacked">
                    <div class="activity-checkbox-label">
                        <input type="checkbox" id="share_story" name="share_story" class="bonus-checkbox">
                        <label for="share_story">
                            <strong>+3 șanse:</strong> Share pe Instagram Story cu tag 
                            <span class="highlight-tag">@haloo.ro</span>
                            și hashtag <span class="highlight-tag">#halooconcurs</span>
                        </label>
                    </div>
                    <input type="text" id="story_screenshot" name="story_screenshot" placeholder="Link către story sau screenshot (opțional)" class="activity-input">
                </div>
                
                <div class="activity-item activity-item-stacked">
                    <div class="activity-checkbox-label">
                        <input type="checkbox" id="tag_friends" name="tag_friends" class="bonus-checkbox">
                        <label for="tag_friends">
                            <strong>+1 șansă:</strong> Tag 3 prieteni în comentariul de pe postarea concursului pe Instagram/Facebook
                        </label>
                    </div>
                    <input type="text" id="comment_link" name="comment_link" placeholder="Link către comentariul tău (opțional)" class="activity-input">
                </div>
                
                <div class="bonus-info-box">
                    <p>
                        <strong>💡 Total posibil:</strong> Până la <strong>8 șanse bonus</strong> (1 motiv + 2 Instagram + 2 Facebook + 3 Story + 1 tag friends)!
                    </p>
                </div>
            </div>
            
            <button type="submit" class="submit-btn" id="submitBtn">
                PARTICIPĂ LA CONCURS
            </button>
            
            <div class="form-success" id="formSuccess">
                ✓ Mulțumim pentru participare! Te vom contacta dacă ești câștigătorul.
            </div>
            
            <div class="form-error" id="formError"></div>
        </form>
        
        <!-- Share Bonus Section -->
        <div class="share-bonus" id="shareBonus">
            <h4>🎁 Bonus: Câștigă șanse extra!</h4>
            <p>Împărtășește concursul și primești 2 șanse suplimentare</p>
            <div class="share-buttons">
                <a href="#" class="share-btn share-btn-wa" id="shareWhatsApp" target="_blank">
                    📱 WhatsApp
                </a>
                <a href="#" class="share-btn share-btn-fb" id="shareFacebook" target="_blank">
                    📘 Facebook
                </a>
            </div>
            <p class="share-bonus__note">
                După ce împărtășești, revino și completează formularul pentru a-ți confirma șansele bonus
            </p>
        </div>
    </div>

    <!-- Regulament: restrâns; click pe titlu deschide tot textul -->
    <details class="terms-section concurs-terms-details" id="termeni">
        <summary class="concurs-terms-summary">
            <span class="concurs-terms-summary__row">
                <span class="concurs-terms-summary__title">Regulament — Concurs Ziua Copilului (iPhone 17 Pro)</span>
                <span class="concurs-terms-summary__chevron" aria-hidden="true"></span>
            </span>
            <span class="concurs-terms-summary__hint">Apasă pentru a citi regulamentul complet (16 secțiuni)</span>
        </summary>
        <div class="terms-content">
            <h4>1. ORGANIZATOR</h4>
            <p>Concursul este organizat de <strong>FRUIT CREATIVE SRL</strong>, CUI 39066744, Nr. Reg. Com. J2020005512236, România, operator al platformei haloo.ro, denumit în continuare "Organizator" sau "Haloo".</p>
            
            <h4>1.1. AUTENTIFICARE NOTARIALĂ ȘI DEPUNERE LA AUTORITĂȚI</h4>
            <p>Acest regulament a fost autentificat de către un notar public înainte de începerea concursului, conform prevederilor Ordonanței Guvernului nr. 99/2000 privind comercializarea produselor și serviciilor de piață.</p>
            <p>Un exemplar al regulamentului autentificat a fost depus la <strong>Ministerul Finanțelor Publice</strong> înainte de lansarea campaniei, pentru a asigura conformitatea cu legislația aplicabilă și pentru a preveni jocurile de noroc deghizate.</p>
            <p>Orice modificare ulterioară a prezentului regulament va fi autentificată notarial și depusă la autoritățile competente, conform procedurii legale.</p>
            
            <h4>2. PERIOADA CONCURSULUI</h4>
            <p>Concursul începe la data de <strong>1 iunie 2026, ora 00:00:00</strong> și se încheie la data de <strong>30 iunie 2026, ora 23:59:59</strong> (ora României). Orice participare primită după această dată nu va fi luată în considerare.</p>
            
            <h4>3. PREMIUL</h4>
            <p>Premiul constă într-un telefon <strong>iPhone 17 Pro, 256GB, stare ca nou</strong>, cu garanție comercială de 2 ani oferită de Haloo. Valoarea estimată a premiului: 8.000 RON (opt mii lei).</p>
            <p>Premiul nu poate fi schimbat cu bani sau alte produse. Nu se acordă premiu echivalent în bani. Premiul este personal și necedabil.</p>
            <p>Produsul este un telefon refurbished (recondiționat profesional) care a fost verificat complet pentru funcționalitate, curățat profesional și testat conform standardelor Haloo.</p>
            
            <h4>4. CONDIȚII DE PARTICIPARE</h4>
            <p>Participarea este <strong>gratuită</strong> și deschisă persoanelor fizice care îndeplinesc următoarele condiții:</p>
            <ul>
                <li>Vârsta minimă de <strong>18 ani</strong> la data participării</li>
                <li>Rezidență în <strong>România</strong></li>
                <li>Fiecare participant poate participa <strong>o singură dată</strong> pe adresă de email</li>
                <li>Participarea se face exclusiv prin completarea formularului de pe pagina de concurs de pe haloo.ro</li>
                <li>Este necesară <strong>acceptarea obligatorie</strong> a termenilor și condițiilor</li>
                <li>Furnizarea de date corecte și reale</li>
            </ul>
            <p><strong>Nu pot participa:</strong> angajații FRUIT CREATIVE SRL, membrii familiilor acestora (soț/soție, copii, părinți, frați/surori), partenerii comerciali direct implicați în organizarea concursului, precum și orice persoană care a contribuit la organizarea sau promovarea acestui concurs.</p>
            
            <h4>5. MECANISMUL DE PARTICIPARE</h4>
            <p>Pentru a participa la concurs, trebuie să:</p>
            <ul>
                <li>Completezi formularul de participare cu datele tale reale (nume complet, email, telefon, oraș)</li>
                <li>Accepti termenii și condițiile concursului</li>
                <li>Opțional: să te abonezi la newsletter pentru a primi oferte exclusive</li>
            </ul>
            <p><strong>Bonus șanse extra:</strong> Poți câștiga până la 10 șanse bonus prin:</p>
            <ul>
                <li>Completarea câmpului motiv (opțional, legat de Ziua Copilului / utilizarea telefonului) (+1 șansă)</li>
                <li>Follow pe Instagram @haloo.ro (+2 șanse)</li>
                <li>Follow pe Facebook Haloo (+2 șanse)</li>
                <li>Share pe Instagram Story cu tag @haloo.ro și hashtag #halooconcurs (+3 șanse)</li>
                <li>Tag 3 prieteni în comentariul de pe postarea concursului (+1 șansă)</li>
                <li>Share concursul pe WhatsApp/Facebook și revenire pe pagină (+2 șanse)</li>
            </ul>
            <p>Fiecare activitate bonus trebuie confirmată prin bifarea căsuței corespunzătoare în formular. Organizatorul se rezervă dreptul de a verifica autenticitatea activităților bonus.</p>
            
            <h4>6. SELECTAREA CÂȘTIGĂTORULUI</h4>
            <p>Câștigătorul va fi selectat <strong>aleatoriu</strong> dintre toți participanții valizi, în ziua de <strong>3 iulie 2026</strong>, printr-un sistem de extragere aleatoare computerizat.</p>
            <p>Extragerea va fi <strong>filmată și documentată</strong> pentru transparență. Înregistrarea extragerei va fi păstrată și poate fi solicitată pentru verificare.</p>
            <p><strong>Autentificare notarială a extragerii:</strong> Organizatorul poate solicita prezența unui notar public la extragerea câștigătorului pentru a autentifica procesul și a valida rezultatul, conform legislației române aplicabile loteriilor publicitare.</p>
            
            <h4>6.1. Mecanismul de șanse și extragere</h4>
            <p><strong>Cum funcționează sistemul de șanse:</strong></p>
            <ul>
                <li>Fiecare participant primește automat <strong>1 șansă de bază</strong> pentru participarea la concurs</li>
                <li>Participanții pot câștiga <strong>șanse bonus</strong> prin completarea activităților opționale:
                    <ul>
                        <li><strong>+1 șansă:</strong> Completarea câmpului motiv (Ziua Copilului / utilizare) cu un răspuns relevant</li>
                        <li><strong>+2 șanse:</strong> Follow pe Instagram @haloo.ro (participantul confirmă prin bifarea căsuței și poate furniza username-ul pentru verificare)</li>
                        <li><strong>+2 șanse:</strong> Follow pe Facebook Haloo (participantul confirmă prin bifarea căsuței și poate furniza link-ul profilului pentru verificare)</li>
                        <li><strong>+3 șanse:</strong> Share pe Instagram Story cu tag @haloo.ro și hashtag #halooconcurs (participantul poate furniza link sau screenshot pentru verificare)</li>
                        <li><strong>+1 șansă:</strong> Tag 3 prieteni în comentariul de pe postarea concursului pe Instagram/Facebook (participantul poate furniza link către comentariu pentru verificare)</li>
                        <li><strong>+2 șanse:</strong> Share concursul pe WhatsApp/Facebook și revenire pe pagină prin link-ul de share</li>
                    </ul>
                </li>
                <li><strong>Total maxim posibil: 11 șanse</strong> (1 bază + 10 bonus)</li>
                <li>Fiecare activitate bonus este confirmată de participant prin bifarea căsuței corespunzătoare în formular</li>
            </ul>
            
            <p><strong>Cum funcționează extragerea:</strong></p>
            <ul>
                <li>În extragere, fiecare participant primește un număr de <strong>"bilete"</strong> egal cu numărul total de șanse acumulate</li>
                <li>Exemplu: Un participant cu 5 șanse totale va avea 5 bilete în extragere</li>
                <li>Câștigătorul este extras aleatoriu din toate biletele, nu din numărul de participanți</li>
                <li>Aceasta înseamnă că participanții cu mai multe șanse au o probabilitate mai mare de câștig, proporțional cu numărul de șanse</li>
            </ul>
            
            <h4>6.2. Verificarea activităților bonus</h4>
            <p><strong>Organizatorul se rezervă dreptul de a verifica autenticitatea activităților bonus:</strong></p>
            <ul>
                <li>Toate activitățile bonus pot fi verificate manual de către Organizator înainte de extragere</li>
                <li>Participanții sunt încurajați să furnizeze informații de verificare (username Instagram, link profil Facebook, link către story sau comentariu) pentru a facilita verificarea</li>
                <li><strong>Verificare Follow Instagram:</strong> Organizatorul verifică manual pe contul @haloo.ro dacă username-ul indicat de participant ne urmărește efectiv</li>
                <li><strong>Verificare Follow Facebook:</strong> Organizatorul verifică manual pe pagina Haloo dacă profilul indicat ne urmărește efectiv</li>
                <li><strong>Verificare Share Story:</strong> Organizatorul verifică link-ul sau screenshot-ul furnizat pentru a confirma că story-ul a fost publicat cu tag-ul @haloo.ro și hashtag-ul #halooconcurs</li>
                <li><strong>Verificare Tag Friends:</strong> Organizatorul verifică link-ul către comentariu pentru a confirma că participantul a tag-uit efectiv 3 prieteni</li>
            </ul>
            
            <p><strong>Consecințe ale neconfirmării activităților:</strong></p>
            <ul>
                <li>Dacă o activitate bonus nu poate fi verificată sau se dovedește a fi falsă, Organizatorul se rezervă dreptul de a exclude acele șanse bonus din calculul total</li>
                <li>Participantul va rămâne în concurs cu șansele verificate și confirmate</li>
                <li>În cazul detectării de activități false sau frauduloase, Organizatorul se rezervă dreptul de a descalifica complet participantul din concurs</li>
            </ul>
            
            <p><strong>Transparență:</strong></p>
            <ul>
                <li>Organizatorul va marca activitățile ca verificate în sistemul intern după verificare manuală</li>
                <li>Participanții pot solicita informații despre statusul verificării activităților lor prin email la contact@haloo.ro</li>
                <li>Toate verificările se fac înainte de extragerea câștigătorului pentru a asigura corectitudinea procesului</li>
            </ul>
            
            <h4>7. NOTIFICAREA CÂȘTIGĂTORULUI</h4>
            <p>Câștigătorul va fi notificat în <strong>maximum 7 zile lucrătoare</strong> de la data extragerii, prin:</p>
            <ul>
                <li>Email la adresa furnizată în formular</li>
                <li>Telefon la numărul furnizat în formular</li>
            </ul>
            <p>Câștigătorul trebuie să răspundă la notificare în <strong>maximum 14 zile calendaristice</strong> de la data notificării. Dacă câștigătorul nu răspunde în acest termen sau nu poate fi contactat, premiul va fi redistribuit printr-o nouă extragere aleatoare dintre participanții rămași.</p>
            <p>Câștigătorul trebuie să confirme acceptarea premiului și să furnizeze o adresă de livrare validă în România.</p>
            
            <h4>8. LIVRAREA PREMIULUI</h4>
            <p>Premiul va fi livrat <strong>gratuit</strong> în România, în <strong>maximum 14 zile lucrătoare</strong> de la confirmarea câștigătorului și primirea adresei de livrare.</p>
            <p>Câștigătorul trebuie să:</p>
            <ul>
                <li>Furnizeze o adresă de livrare validă și completă în România</li>
                <li>Fie disponibil pentru primirea coletului (să fie prezent la adresa indicată sau să autorizeze o altă persoană)</li>
                <li>Verifice coletul la primire (recomandăm filmarea deschiderii coletului pentru protecție)</li>
            </ul>
            <p>Livrarea se face prin curier partener Haloo. Câștigătorul va primi AWB-ul pentru tracking.</p>
            <p><strong>IMPORTANT:</strong> Recomandăm FERM să filmați procesul de deschidere a coletului pentru protecția dumneavoastră, conform politicii Haloo pentru toate produsele.</p>
            
            <h4>9. GARANȚIA PREMIULUI</h4>
            <p>Premiul beneficiază de <strong>garanție comercială de 2 ani</strong> oferită de Haloo, conform termenilor și condițiilor generale Haloo.ro. Garanția acoperă defecte de fabricație, probleme hardware și software (dacă nu sunt cauzate de utilizator).</p>
            <p>Detalii complete despre garanție sunt disponibile în secțiunea "Garanția Produselor" din Termenii și Condițiile generale haloo.ro.</p>
            
            <h4>10. DATE PERSONALE ȘI CONFIDENȚIALITATE</h4>
            <p>Datele personale colectate (nume, email, telefon, oraș) vor fi utilizate exclusiv în scopul:</p>
            <ul>
                <li>Desfășurării concursului și selectării câștigătorului</li>
                <li>Comunicării cu câștigătorul pentru notificare și livrare</li>
                <li>Abonării la newsletter (doar dacă participantul a bifat opțiunea corespunzătoare)</li>
            </ul>
            <p>Prelucrarea datelor se face conform <strong>Legii 677/2001</strong> și <strong>GDPR (Regulamentul General privind Protecția Datelor)</strong>. Detalii complete despre prelucrarea datelor personale sunt disponibile în <strong>Politica de Confidențialitate</strong> haloo.ro.</p>
            <p>Participanții au dreptul la acces, rectificare, ștergere sau opoziție față de prelucrarea datelor, conform GDPR.</p>
            
            <h4>11. RESPONSABILITATE ȘI LIMITĂRI</h4>
            <p>Organizatorul nu își asumă responsabilitatea pentru:</p>
            <ul>
                <li>Erori tehnice, întreruperi de serviciu sau probleme de conexiune care ar putea afecta participarea</li>
                <li>Pierderea sau întârzierea mesajelor de notificare din cauza unor probleme tehnice ale furnizorilor de servicii (email, telefonie)</li>
                <li>Modificări sau anulări ale concursului din cauza unor evenimente de forță majoră</li>
            </ul>
            <p>Organizatorul își rezervă dreptul de a:</p>
            <ul>
                <li>Modifica, suspenda sau anula concursul în caz de fraudă, abuz sau alte motive justificate</li>
                <li>Descalifica participanți care nu respectă termenii și condițiile sau care furnizează date false</li>
                <li>Verifica autenticitatea datelor furnizate de participanți</li>
            </ul>
            
            <h4>12. FRAUDĂ ȘI ABUZ</h4>
            <p>Orice tentativă de fraudă sau abuz (participări multiple cu email-uri false, utilizare de scripturi automate, manipulare a sistemului de extragere etc.) va duce la descalificarea imediată și eliminarea tuturor participărilor asociate.</p>
            <p>Organizatorul se rezervă dreptul de a lua măsuri legale împotriva persoanelor care încercă să fraudeze concursul.</p>
            
            <h4>13. ACCEPTAREA REGULAMENTULUI</h4>
            <p>Prin participarea la acest concurs și prin completarea formularului, confirmi că:</p>
            <ul>
                <li>Ai citit, înțeles și acceptat în totalitate acest regulament</li>
                <li>Îndeplinești toate condițiile de participare</li>
                <li>Ai furnizat date corecte și reale</li>
                <li>Accepti decizia finală a Organizatorului privind selectarea câștigătorului</li>
                <li>Accepti că premiul nu poate fi schimbat cu bani sau alte produse</li>
            </ul>
            <p>Orice încălcare a acestor termeni poate duce la descalificarea imediată a participantului.</p>
            
            <h4>14. CONTACT ȘI INFORMĂRI</h4>
            <p>Pentru întrebări despre concurs sau pentru a solicita informații suplimentare:</p>
            <ul>
                <li><strong>Email:</strong> contact@haloo.ro</li>
                <li><strong>Telefon:</strong> +40 754 025 905</li>
                <li><strong>Program:</strong> Luni-Vineri: 09:00-17:00</li>
            </ul>
            <p>Pentru informații despre produse, garanție, livrare sau retururi, consultă <a href="https://haloo.ro/termeni-si-conditii/" target="_blank">Termenii și Condițiile generale</a> haloo.ro.</p>
            
            <h4>15. MODIFICĂRI ALE REGULAMENTULUI</h4>
            <p>Organizatorul își rezervă dreptul de a modifica acest regulament în orice moment, cu respectarea următoarelor condiții legale:</p>
            <ul>
                <li>Orice modificare a regulamentului va fi <strong>autentificată notarial</strong> înainte de intrarea în vigoare</li>
                <li>Modificările vor fi <strong>depuse la Ministerul Finanțelor Publice</strong> conform procedurii legale</li>
                <li>Modificările vor fi publicate pe această pagină și vor produce efecte doar pentru viitor</li>
                <li>Participările deja înregistrate rămân valide conform regulamentului în vigoare la momentul participării</li>
            </ul>
            
            <h4>16. CONFORMITATE LEGALĂ</h4>
            <p>Acest concurs este organizat în conformitate cu:</p>
            <ul>
                <li><strong>Ordonanța Guvernului nr. 99/2000</strong> privind comercializarea produselor și serviciilor de piață</li>
                <li><strong>Legea nr. 190/2018</strong> privind măsuri de implementare a Regulamentului (UE) 2016/679</li>
                <li><strong>Codul de Consum</strong> și legislația română aplicabilă</li>
                <li>Toate prevederile legale privind loteriile publicitare și concursurile promoționale</li>
            </ul>
            <p>Organizatorul se angajează să respecte toate obligațiile legale, inclusiv autentificarea notarială a regulamentului și depunerea acestuia la autoritățile competente.</p>
            
            <p class="terms-footer-meta">
                Ultima actualizare: 1 iunie 2026<br>
                Organizator: FRUIT CREATIVE SRL, CUI 39066744, Nr. Reg. Com. J2020005512236
            </p>
        </div>
    </details>
</div>

        </main>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const termsDetails = document.getElementById('termeni');
    function openTermsIfHash() {
        if (!termsDetails || window.location.hash !== '#termeni') {
            return;
        }
        termsDetails.open = true;
        termsDetails.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
    openTermsIfHash();
    window.addEventListener('hashchange', openTermsIfHash);

    const form = document.getElementById('concursForm');
    const submitBtn = document.getElementById('submitBtn');
    const formSuccess = document.getElementById('formSuccess');
    const formError = document.getElementById('formError');
    const shareBonus = document.getElementById('shareBonus');
    
    // Check if user came from share
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('shared') === 'true') {
        shareBonus.style.display = 'block';
    }
    
    // Setup share buttons
    const shareText = encodeURIComponent('Participă la concursul Haloo de Ziua Copilului — iPhone 17 Pro! Înscrieri până pe 30 iunie.');
    const shareUrl = encodeURIComponent(window.location.href.split('?')[0] + '?shared=true');
    
    document.getElementById('shareWhatsApp').href = `https://wa.me/?text=${shareText}%20${shareUrl}`;
    document.getElementById('shareFacebook').href = `https://www.facebook.com/sharer/sharer.php?u=${shareUrl}`;
    
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Clear previous messages
        formSuccess.style.display = 'none';
        formError.style.display = 'none';
        
        // Validate
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }
        
        // Disable submit button
        submitBtn.disabled = true;
        submitBtn.textContent = 'Se trimite...';
        
        // Get form data
        const formData = new FormData(form);
        const data = {
            action: 'submit_concurs',
            nume: formData.get('nume'),
            email: formData.get('email'),
            telefon: formData.get('telefon'),
            oras: formData.get('oras'),
            motiv: formData.get('motiv'),
            terms: formData.get('terms'),
            privacy: formData.get('privacy'),
            newsletter: formData.get('newsletter'),
            follow_instagram: formData.get('follow_instagram'),
            follow_facebook: formData.get('follow_facebook'),
            share_story: formData.get('share_story'),
            tag_friends: formData.get('tag_friends'),
            instagram_username: formData.get('instagram_username'),
            facebook_profile: formData.get('facebook_profile'),
            story_screenshot: formData.get('story_screenshot'),
            comment_link: formData.get('comment_link'),
            shared: urlParams.get('shared') === 'true' ? '1' : '0',
            _wpnonce: formData.get('_wpnonce')
        };
        
        try {
            const response = await fetch('/wp-admin/admin-ajax.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams(data)
            });
            
            const result = await response.json();
            
            if (result.success) {
                formSuccess.style.display = 'block';
                form.reset();
                form.scrollIntoView({ behavior: 'smooth', block: 'start' });
            } else {
                formError.textContent = result.data || 'A apărut o eroare. Te rugăm să încerci din nou.';
                formError.style.display = 'block';
            }
        } catch (error) {
            formError.textContent = 'A apărut o eroare de conexiune. Te rugăm să încerci din nou.';
            formError.style.display = 'block';
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = 'PARTICIPĂ LA CONCURS';
        }
    });
});
</script>

<?php get_footer(); ?>

