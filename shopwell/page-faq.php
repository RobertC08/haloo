<?php
/**
 * Template Name: FAQ
 * 
 * Custom template for FAQ page
 */

get_header();
?>

<!-- CSS moved to external file: assets/css/pages/faq.css -->

<!-- Hero Section -->
<div class="faq-hero">
    <h1>Întrebări Frecvente</h1>
    <p class="faq-hero-subtitle">Găsește răspunsuri rapide la cele mai frecvente întrebări despre produsele și serviciile noastre</p>
</div>

<!-- FAQ Content -->
<div class="faq-content">
    
    <!-- General Questions Section -->
    <div class="faq-section">
        <h2 class="faq-section-title">🛍️ Întrebări Generale</h2>
        <div class="faq-accordion">
            <div class="faq-item">
                <button class="faq-question">
                    Ce înseamnă "refurbished" sau "recondiționat"?
                </button>
                <div class="faq-answer">
                    <div class="faq-answer-content">
                        <p>Un telefon refurbished sau recondiționat este un dispozitiv care a fost folosit anterior, dar a fost verificat, reparat (dacă a fost necesar) și adus la standarde de funcționare optimă. Toate telefoanele noastre trec printr-un proces strict de testare și curățare profesională.</p>
                        <p>Produsele refurbished reprezintă o alternativă ecologică și economică la telefoanele noi, având aceleași funcționalități, dar la un preț mai avantajos.</p>
                    </div>
                </div>
            </div>
            
            <div class="faq-item">
                <button class="faq-question">
                    Produsele vin cu garanție?
                </button>
                <div class="faq-answer">
                    <div class="faq-answer-content">
                        <p>Da! Toate produsele noastre vin cu garanție de 12 luni. Garanția acoperă defectele de fabricație și problemele hardware care nu sunt cauzate de utilizare necorespunzătoare.</p>
                        <p>În plus, ai dreptul de a returna produsul în primele 14 zile dacă nu ești mulțumit, conform legislației în vigoare.</p>
                    </div>
                </div>
            </div>
            
            <div class="faq-item">
                <button class="faq-question">
                    Ce grad de stare înseamnă A, B, C?
                </button>
                <div class="faq-answer">
                    <div class="faq-answer-content">
                        <p><strong>Grad A (Excelent):</strong> Dispozitivul arată ca nou, fără zgârieturi vizibile sau semne de uzură. Funcționează perfect.</p>
                        <p><strong>Grad B (Foarte Bun):</strong> Dispozitivul poate avea mici zgârieturi superficiale pe carcasă, dar ecranul este intact. Funcționează perfect.</p>
                        <p><strong>Grad C (Bun):</strong> Dispozitivul poate avea urme de uzură vizibile pe carcasă și/sau ecran, dar funcționează perfect din punct de vedere tehnic.</p>
                    </div>
                </div>
            </div>
            
            <div class="faq-item">
                <button class="faq-question">
                    Cum verific autenticitatea produsului?
                </button>
                <div class="faq-answer">
                    <div class="faq-answer-content">
                        <p>Toate telefoanele noastre sunt originale și verificate. Poți verifica autenticitatea astfel:</p>
                        <ul>
                            <li>Verifică numărul IMEI pe site-ul producătorului</li>
                            <li>Controlează sigiliul original de pe cutie (dacă vine cu cutie)</li>
                            <li>Consultă certificatul de autenticitate furnizat de noi</li>
                            <li>Apelează la service-ul oficial pentru verificare suplimentară</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Ordering & Payment Section -->
    <div class="faq-section">
        <h2 class="faq-section-title">💳 Comenzi și Plată</h2>
        <div class="faq-accordion">
            <div class="faq-item">
                <button class="faq-question">
                    Ce metode de plată acceptați?
                </button>
                <div class="faq-answer">
                    <div class="faq-answer-content">
                        <p>Acceptăm următoarele metode de plată:</p>
                        <ul>
                            <li>Card bancar (Visa, Mastercard)</li>
                            <li>Apple Pay</li>
                            <li>Google Pay</li>
                            <li>Ramburs la livrare (+ 15 RON)</li>
                            <li>Transfer bancar</li>
                        </ul>
                        <p>Toate plățile online sunt procesate securizat prin gateway-uri certificate.</p>
                    </div>
                </div>
            </div>
            
            <div class="faq-item">
                <button class="faq-question">
                    Cum plasez o comandă?
                </button>
                <div class="faq-answer">
                    <div class="faq-answer-content">
                        <p>Procesul de comandă este simplu:</p>
                        <ul>
                            <li>1. Alege produsul dorit și adaugă-l în coș</li>
                            <li>2. Verifică coșul și introdu codul de reducere (dacă ai)</li>
                            <li>3. Completează datele de livrare și facturare</li>
                            <li>4. Alege metoda de plată și finalizează comanda</li>
                            <li>5. Vei primi un email de confirmare cu detaliile comenzii</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="faq-item">
                <button class="faq-question">
                    Pot anula sau modifica comanda?
                </button>
                <div class="faq-answer">
                    <div class="faq-answer-content">
                        <p>Da, poți anula sau modifica comanda în primele 2 ore de la plasare, contactându-ne urgent la:</p>
                        <ul>
                            <li>Telefon: +40 (123) 456-7890</li>
                            <li>Email: admin@thisisfruit.com</li>
                            <li>WhatsApp: +40 (123) 456-7890</li>
                        </ul>
                        <p>După ce produsul a fost expediat, nu mai putem anula comanda, dar poți returna produsul gratuit în 14 zile.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Shipping & Delivery Section -->
    <div class="faq-section">
        <h2 class="faq-section-title">🚚 Livrare și Transport</h2>
        <div class="faq-accordion">
            <div class="faq-item">
                <button class="faq-question">
                    Cât durează livrarea?
                </button>
                <div class="faq-answer">
                    <div class="faq-answer-content">
                        <p><strong>Livrare standard:</strong> 2-3 zile lucrătoare în orașele mari</p>
                        <p><strong>Livrare în localități:</strong> 3-5 zile lucrătoare</p>
                        <p><strong>Livrare express (doar în București):</strong> În aceeași zi sau a doua zi (disponibilă pentru comenzi plasate până la ora 14:00)</p>
                        <p>Vei primi un email cu numărul de tracking pentru a urmări coletul în timp real.</p>
                    </div>
                </div>
            </div>
            
            <div class="faq-item">
                <button class="faq-question">
                    Care sunt costurile de livrare?
                </button>
                <div class="faq-answer">
                    <div class="faq-answer-content">
                        <p><strong>Livrare gratuită</strong> pentru comenzi peste 500 RON</p>
                        <p><strong>20 RON</strong> - Livrare standard pentru comenzi sub 500 RON</p>
                        <p><strong>35 RON</strong> - Livrare express (doar București)</p>
                        <p><strong>15 RON</strong> - Taxa suplimentară pentru ramburs</p>
                    </div>
                </div>
            </div>
            
            <div class="faq-item">
                <button class="faq-question">
                    Cum urmăresc coletul?
                </button>
                <div class="faq-answer">
                    <div class="faq-answer-content">
                        <p>După expedierea comenzii, vei primi un email cu numărul AWB și link direct către sistemul de tracking al curierului.</p>
                        <p>De asemenea, poți accesa secțiunea "Comenzile Mele" din cont pentru a vedea statusul comenzii și informațiile de tracking.</p>
                    </div>
                </div>
            </div>
            
            <div class="faq-item">
                <button class="faq-question">
                    Livrați în toată țara?
                </button>
                <div class="faq-answer">
                    <div class="faq-answer-content">
                        <p>Da, livrăm în toată România prin curierat rapid. Folosim serviciile FAN Courier și DPD pentru a asigura livrări rapide și sigure.</p>
                        <p>Momentan nu livrăm în afara României, dar lucrăm la extinderea serviciilor noastre.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Returns & Warranty Section -->
    <div class="faq-section">
        <h2 class="faq-section-title">🔄 Retururi și Garanție</h2>
        <div class="faq-accordion">
            <div class="faq-item">
                <button class="faq-question">
                    Cum returnez un produs?
                </button>
                <div class="faq-answer">
                    <div class="faq-answer-content">
                        <p>Procesul de retur este simplu:</p>
                        <ul>
                            <li>1. Contactează-ne în termen de 14 zile de la primirea produsului</li>
                            <li>2. Explică motivul returului (opțional)</li>
                            <li>3. Primești un AWB de retur gratuit</li>
                            <li>4. Împachetează produsul în ambalajul original (dacă este posibil)</li>
                            <li>5. Predă coletul curierului</li>
                            <li>6. Vei fi rambursat în 5-7 zile lucrătoare după verificarea produsului</li>
                        </ul>
                        <p><strong>Important:</strong> Produsul trebuie să fie în starea în care l-ai primit, cu toate accesoriile incluse.</p>
                    </div>
                </div>
            </div>
            
            <div class="faq-item">
                <button class="faq-question">
                    Ce acopere garanția?
                </button>
                <div class="faq-answer">
                    <div class="faq-answer-content">
                        <p>Garanția de 12 luni acoperă:</p>
                        <ul>
                            <li>Defecte de fabricație</li>
                            <li>Probleme hardware (display, baterie, butoane, etc.)</li>
                            <li>Probleme software (dacă nu sunt cauzate de utilizator)</li>
                        </ul>
                        <p><strong>Garanția NU acoperă:</strong></p>
                        <ul>
                            <li>Daune fizice cauzate de scăpări sau lovituri</li>
                            <li>Daune cauzate de apă sau lichide</li>
                            <li>Probleme cauzate de instalare software neautorizat</li>
                            <li>Uzura normală (zgârieturi superficiale)</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="faq-item">
                <button class="faq-question">
                    Cât durează reparația în garanție?
                </button>
                <div class="faq-answer">
                    <div class="faq-answer-content">
                        <p>În medie, reparațiile în garanție durează 5-10 zile lucrătoare, în funcție de problema identificată și disponibilitatea pieselor.</p>
                        <p>Vei fi informat constant despre statusul reparației prin email sau telefon.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Account & Support Section -->
    <div class="faq-section">
        <h2 class="faq-section-title">👤 Cont și Suport</h2>
        <div class="faq-accordion">
            <div class="faq-item">
                <button class="faq-question">
                    Trebuie să am cont pentru a cumpăra?
                </button>
                <div class="faq-answer">
                    <div class="faq-answer-content">
                        <p>Nu, poți plasa o comandă și ca guest (fără cont). Însă, crearea unui cont îți oferă avantaje:</p>
                        <ul>
                            <li>Acces rapid la istoricul comenzilor</li>
                            <li>Salvarea adreselor de livrare</li>
                            <li>Oferte exclusive pentru membri</li>
                            <li>Urmărirea mai ușoară a statusului comenzilor</li>
                            <li>Wishlist pentru produsele favorite</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="faq-item">
                <button class="faq-question">
                    Cum resetez parola?
                </button>
                <div class="faq-answer">
                    <div class="faq-answer-content">
                        <p>Pentru a reseta parola:</p>
                        <ul>
                            <li>1. Click pe "Contul Meu" în meniu</li>
                            <li>2. Click pe "Ai uitat parola?"</li>
                            <li>3. Introdu adresa de email asociată contului</li>
                            <li>4. Verifică email-ul și urmează instrucțiunile</li>
                            <li>5. Creează o parolă nouă</li>
                        </ul>
                        <p>Dacă întâmpini probleme, contactează-ne la admin@thisisfruit.com</p>
                    </div>
                </div>
            </div>
            
            <div class="faq-item">
                <button class="faq-question">
                    Cum vă pot contacta?
                </button>
                <div class="faq-answer">
                    <div class="faq-answer-content">
                        <p>Ne poți contacta prin mai multe canale:</p>
                        <ul>
                            <li><strong>Telefon:</strong> +40 (123) 456-7890 (Luni-Vineri: 08:00-21:00)</li>
                            <li><strong>Email:</strong> admin@thisisfruit.com</li>
                            <li><strong>WhatsApp:</strong> +40 (123) 456-7890</li>
                            <li><strong>Chat Live:</strong> Disponibil pe site în timpul programului</li>
                            <li><strong>Formular Contact:</strong> <a href="/contact" style="color: #66fa95;">Accesează aici</a></li>
                        </ul>
                        <p>Răspundem de obicei în mai puțin de 2 ore în timpul programului.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- CTA Section -->
    <div class="faq-cta-section">
        <p class="faq-cta-subtitle">Nu ai găsit răspunsul?</p>
        <h2 class="faq-cta-title">Echipa noastră de suport<br>este gata să te ajute!</h2>
        <a href="<?php echo home_url('/contact'); ?>" class="faq-cta-button">Contactează-ne</a>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const faqQuestions = document.querySelectorAll('.faq-question');
    
    faqQuestions.forEach(function(question) {
        question.addEventListener('click', function() {
            const answer = this.nextElementSibling;
            const isActive = this.classList.contains('active');
            
            // Close all other open questions
            faqQuestions.forEach(function(q) {
                q.classList.remove('active');
                q.nextElementSibling.classList.remove('active');
            });
            
            // Toggle current question
            if (!isActive) {
                this.classList.add('active');
                answer.classList.add('active');
            }
        });
    });
});
</script>

<?php get_footer(); ?>

