<!-- Footer Widget -->
<div class="footer-widget">
    <div class="footer-container">
        <!-- Subscription Section -->
        <div class="subscription-section">
            <h2>Abonează-te și primește 20% reducere</h2>
            <p>Primește recomandări, sfaturi, actualizări și multe altele</p>
            <div class="subscription-form">
                <input type="email" placeholder="Adresa ta de email">
                <button type="button">Abonează-te</button>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="footer-content">
            <div class="footer-section">
                <h4>Contactează-ne</h4>
                <div class="contact-item">
                    <span class="contact-icon">📞</span>
                    <div>
                        <p>Luni-Vineri: 08:00-21:00</p>
                        <p><a href="tel:+401234567890" class="phone-link">+40 (123) 456-7890</a></p>
                    </div>
                </div>
                <div class="contact-item-mail">
                    <span class="contact-icon">✉️</span>
                    <div>
                        <p>Ai nevoie de ajutor cu comanda?</p>
                        <p><a href="mailto:admin@thisisfruit.com" class="email-link">admin@thisisfruit.com</a></p>
                    </div>
                </div>
            </div>
            
            <div class="footer-section">
                <h4>Informații & Companie</h4>
                <ul>
                    <li><a href="/despre-noi">Despre Haloo</a></li>
                    <li><a href="/blog">Blog</a></li>
                    <li><a href="/contact">Contact Haloo</a></li>
                    <li><a href="/faq">FAQ</a></li>
                    <li><a href="/termeni-si-conditii">Termeni și condiții</a></li>
                    <li><a href="/politica-de-confidentialitate">Politica de Confidențialitate</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h4>Ghiduri & Parteneriate</h4>
                <ul>
                    <li><a href="/alege-telefonul-potrivit">Găsește telefonul potrivit</a></li>
                    <li><a href="/alege-telefonul-pentru-tine">Program Affiliate</a></li>
                    <li><a href="/regulament-afiliere">Regulament Affiliate</a></li>
                    <li><a href="/termeni-afiliere/">Termeni Affiliate</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h4>Suport Clienți</h4>
                <ul>
                    <li><a href="/contul-meu/comenzile-mele/">Comenzile Tale</a></li>
                    <li><a href="/retururi-si-inlocuiri">Retururi și Înlocuiri</a></li>
                    <li><a href="/tarife-si-politici-de-livrare">Tarife și Politici de Livrare</a></li>
                    <li><a href="/politica-de-rambursare">Politica de Rambursare</a></li>
                    <li><a href="/urmareste-comanda">Urmărește Comanda</a></li>
                    <li><a href="/livrare-si-delivery">Livrare și Delivery</a></li>
                    <li><a href="/politica-banii-inapoi">Politica Banii Înapoi</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h4>Urmărește-ne</h4>
                <div class="social-icons">
                    <a href="#" title="Facebook">
                        <img src="https://haloo.ro/wp-content/uploads/2025/10/facebook.svg" alt="Facebook" width="20" height="20">
                    </a>
                    <a href="#" title="Instagram">
                        <img src="https://haloo.ro/wp-content/uploads/2025/10/instagram.svg" alt="Instagram" width="20" height="20">
                    </a>
                    <a href="#" title="WhatsApp">
                        <img src="https://haloo.ro/wp-content/uploads/2025/10/whatsapp.svg" alt="WhatsApp" width="20" height="20">
                    </a>
                </div>
                
                <h5>Acceptăm</h5>
                <div class="payment-icons">
                    <img src="https://haloo.ro/wp-content/uploads/2025/10/visa.svg" alt="Visa" width="40" height="25">
                    <img src="https://haloo.ro/wp-content/uploads/2025/10/mastercard.svg" alt="Mastercard" width="40" height="25">
                    <img src="https://haloo.ro/wp-content/uploads/2025/10/applepay.svg" alt="Apple Pay" width="40" height="25">
                    <img src="https://haloo.ro/wp-content/uploads/2025/10/googlepay.svg" alt="Google Pay" width="40" height="25">
                </div>
            </div>
        </div>
        
        <!-- Copyright Section -->
        <div class="copyright-section">
            <div class="copyright-content">
                <p>Copyright © 2025 Haloo, Toate drepturile rezervate.</p>
                <div class="copyright-links">
                    <a href="/baza-cunostinte">Baza de Cunoștințe</a>
                    <a href="/urmarire-comanda">Urmărește Comanda</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CSS moved to external file: assets/css/layout/footer.css -->

<script>
// JavaScript pentru actualizarea ultimei sincronizări
document.addEventListener('DOMContentLoaded', function() {
    const lastSyncElement = document.getElementById('last-sync');
    if (lastSyncElement) {
        // Aici poți adăuga logica pentru a obține data ultimei sincronizări
        // De exemplu, prin AJAX sau din localStorage
        lastSyncElement.textContent = 'Încărcare...';
        
        // Simulare - înlocuiește cu logica reală
        setTimeout(() => {
            lastSyncElement.textContent = 'Azi, 14:30';
        }, 1000);
    }
    
    // Mobile Footer Accordion - Only for sections with lists
    const footerSections = document.querySelectorAll('.footer-section:has(ul) h4');
    footerSections.forEach(function(header) {
        header.addEventListener('click', function() {
            const section = this.parentElement;
            const ul = section.querySelector('ul');
            
            if (ul) {
                // Toggle active class on header
                this.classList.toggle('active');
                
                // Toggle active class on ul
                ul.classList.toggle('active');
            }
        });
    });
});
</script>

<?php wp_footer(); ?>
</body>
</html>
