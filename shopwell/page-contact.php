<?php
/**
 * Template Name: Contact Page
 * 
 * Contact Page Template for Haloo Refurbished Phones
 *
 * @package Shopwell
 */

get_header();
?>

<!-- CSS moved to external file: assets/css/pages/contact.css -->

<div class="site-content-container">
    <div id="primary" class="content-area">
        <main id="main" class="site-main">
            
            <!-- Hero Section -->
            <section class="contact-hero">
                <div class="contact-container">
                    <div class="contact-header">
                        <h1 class="contact-title">Contactează echipa noastră de specialiști</h1>
                        <p class="contact-subtitle">Te vom ajuta să găsești telefonul refurbished perfect pentru nevoile tale, cu garanție de 2 ani și prețuri accesibile.</p>
                        
                        <!-- Brand Logos Slider -->
                        <div class="brand-slider">
                            <div class="brand-slider-track">
                                <div class="brand-logo">
                                    <span>Samsung</span>
                                </div>
                                <div class="brand-logo">
                                    <span>Apple</span>
                                </div>
                                <div class="brand-logo">
                                    <span>Huawei</span>
                                </div>
                                <div class="brand-logo">
                                    <span>Xiaomi</span>
                                </div>
                                <div class="brand-logo">
                                    <span>OnePlus</span>
                                </div>
                                <div class="brand-logo">
                                    <span>Google</span>
                                </div>
                                <div class="brand-logo">
                                    <span>Motorola</span>
                                </div>
                                <div class="brand-logo">
                                    <span>LG</span>
                                </div>
                                <!-- Duplicate for seamless loop -->
                                <div class="brand-logo">
                                    <span>Samsung</span>
                                </div>
                                <div class="brand-logo">
                                    <span>Apple</span>
                                </div>
                                <div class="brand-logo">
                                    <span>Huawei</span>
                                </div>
                                <div class="brand-logo">
                                    <span>Xiaomi</span>
                                </div>
                                <div class="brand-logo">
                                    <span>OnePlus</span>
                                </div>
                                <div class="brand-logo">
                                    <span>Google</span>
                                </div>
                                <div class="brand-logo">
                                    <span>Motorola</span>
                                </div>
                                <div class="brand-logo">
                                    <span>LG</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="contact-grid">
                        <!-- Contact Form -->
                        <div class="contact-content">
                            <form class="contact-form" id="contactForm" method="POST" action="">
                                <?php wp_nonce_field('contact_form_nonce', '_wpnonce'); ?>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="name">Nume *</label>
                                        <input type="text" id="name" name="name" placeholder="Nume" required>
                                        <span class="error-message" id="nameError"></span>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="lastname">Prenume *</label>
                                        <input type="text" id="lastname" name="lastname" placeholder="Prenume" required>
                                        <span class="error-message" id="lastnameError"></span>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="email">Email *</label>
                                    <input type="email" id="email" name="email" placeholder="you@company.com" required>
                                    <span class="error-message" id="emailError"></span>
                                </div>
                                
                                <div class="form-group">
                                    <label for="phone">Număr de telefon</label>
                                    <div class="phone-input-group">
                                        <select class="country-select" name="country">
                                            <option value="RO">RO</option>
                                            <option value="US">US</option>
                                            <option value="UK">UK</option>
                                        </select>
                                        <input type="tel" class="phone-input" id="phone" name="phone" placeholder="+40 721 234 567">
                                    </div>
                                    <span class="error-message" id="phoneError"></span>
                                </div>
                                
                                <div class="form-group">
                                    <label for="subject">Subiect *</label>
                                    <select id="subject" name="subject" required>
                                        <option value="">Selectează subiectul</option>
                                        <option value="general">Întrebare generală</option>
                                        <option value="product">Întrebare despre produs</option>
                                        <option value="warranty">Garanție și service</option>
                                        <option value="delivery">Livrare și retur</option>
                                        <option value="technical">Suport tehnic</option>
                                        <option value="partnership">Parteneriat</option>
                                        <option value="other">Altele</option>
                                    </select>
                                    <span class="error-message" id="subjectError"></span>
                                </div>
                                
                                <div class="form-group">
                                    <label for="message">Mesaj *</label>
                                    <textarea id="message" name="message" placeholder="Descrie întrebarea sau problema ta..." required></textarea>
                                    <span class="error-message" id="messageError"></span>
                                </div>
                                
                                <div class="form-group checkbox-group">
                                    <label class="checkbox-label">
                                        <input type="checkbox" id="privacy" name="privacy" required>
                                        <span class="checkmark"></span>
                                        Sunt de acord cu <a href="/privacy-policy" target="_blank">politica de confidențialitate</a> *
                                    </label>
                                    <span class="error-message" id="privacyError"></span>
                                </div>
                                
                                <button type="submit" class="btn-primary" id="submitBtn">
                                    <span class="btn-text">Trimite mesajul</span>
                                    <span class="btn-loading" style="display: none;">
                                        <i class="fas fa-spinner fa-spin"></i>
                                        Se trimite...
                                    </span>
                                </button>
                            </form>
                            
                            <div class="form-success" id="formSuccess" style="display: none;">
                                <i class="fas fa-check-circle"></i>
                                <h3>Mesajul a fost trimis cu succes!</h3>
                                <p>Îți vom răspunde în cel mai scurt timp posibil.</p>
                            </div>
                            
                            <div class="form-error" id="formError" style="display: none;">
                                <i class="fas fa-exclamation-circle"></i>
                                <h3>Eroare la trimiterea mesajului</h3>
                                <p>Te rugăm să încerci din nou sau să ne contactezi direct.</p>
                            </div>
                        </div>

                        <!-- Contact Information -->
                        <div class="contact-info">
                            <div class="contact-method">
                                <h3>💬 Chat cu noi</h3>
                                <p>Vorbește cu specialiștii noștri despre telefoanele refurbished prin chat live. Răspundem în câteva minute!</p>
                                <div class="contact-links">
                                    <a href="mailto:contact@haloo.ro" class="contact-link">
                                        <i class="fas fa-paper-plane"></i>
                                        <div>
                                            <div class="contact-link-title">Trimite-ne un email</div>
                                            <div class="contact-link-subtitle">contact@haloo.ro</div>
                                        </div>
                                    </a>
                                    <a href="https://wa.me/40721234567" class="contact-link">
                                        <i class="fab fa-whatsapp"></i>
                                        <div>
                                            <div class="contact-link-title">WhatsApp</div>
                                            <div class="contact-link-subtitle">Mesaj instant</div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                            
                            <div class="contact-method">
                                <h3>📞 Sună-ne</h3>
                                <p>Sună specialiștii noștri Luni-Vineri de la 9:00 la 18:00. Consultație gratuită pentru toate telefoanele!</p>
                                <div class="phone-numbers">
                                    <div class="phone-hours">
                                        <i class="fas fa-clock"></i>
                                        <div>
                                            <div class="program-label">Program</div>
                                            <div class="program-time">Luni-Vineri: 9:00 - 18:00</div>
                                        </div>
                                    </div>
                                    <a href="tel:+40721234567" class="phone-number">
                                        <i class="fas fa-phone"></i>
                                        <div>
                                            <div class="phone-number-title">+40 721 234 567</div>
                                            <div class="phone-number-subtitle">Linia principală</div>
                                        </div>
                                    </a>
                                    <a href="tel:+40721234568" class="phone-number">
                                        <i class="fas fa-phone"></i>
                                        <div>
                                            <div class="phone-number-title">+40 721 234 568</div>
                                            <div class="phone-number-subtitle">Suport tehnic</div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                </div>
            </section>

            <!-- FAQ Section -->
            <section class="faq-section">
                <div class="faq-container">
                    <div class="faq-content">
                        <div class="faq-text">
                            <div class="faq-header">
                                <h2>Întrebări frecvente</h2>
                            </div>
                            
                            <div class="faq-list">
                                <div class="faq-item">
                                    <div class="faq-question">
                                        <h3 class="faq-question-title">Ce înseamnă "refurbished" și de ce să aleg un telefon refurbished?</h3>
                                        <i class="fas fa-plus"></i>
                                    </div>
                                    <div class="faq-answer">
                                        <p>Un telefon refurbished este un dispozitiv care a fost folosit anterior, dar a fost restaurat la o stare aproape nouă prin înlocuirea pieselor defecte, curățare completă și testare riguroasă. Alegi un telefon refurbished pentru că oferă aceeași calitate ca unul nou, dar la un preț mult mai accesibil, cu garanție de 2 ani.</p>
                                    </div>
                                </div>
                                
                                <div class="faq-item">
                                    <div class="faq-question">
                                        <h3 class="faq-question-title">Cât durează livrarea și care sunt costurile?</h3>
                                        <i class="fas fa-plus"></i>
                                    </div>
                                    <div class="faq-answer">
                                        <p>Livrarea se face în 1-2 zile lucrătoare pentru București și 2-3 zile lucrătoare pentru restul țării. Livrarea este gratuită pentru comenzi peste 500 lei. Pentru comenzi mai mici, costul livrării este de 25 lei.</p>
                                    </div>
                                </div>
                                
                                <div class="faq-item">
                                    <div class="faq-question">
                                        <h3 class="faq-question-title">Ce garanție oferiți pentru telefoanele refurbished?</h3>
                                        <i class="fas fa-plus"></i>
                                    </div>
                                    <div class="faq-answer">
                                        <p>Toate telefoanele noastre refurbished vin cu garanție de 2 ani pentru defecte de fabricație. Garanția acoperă componentele interne și funcționalitatea de bază a dispozitivului. În plus, oferim suport tehnic gratuit pe durata garanției.</p>
                                    </div>
                                </div>
                                
                                <div class="faq-item">
                                    <div class="faq-question">
                                        <h3 class="faq-question-title">Pot returna telefonul dacă nu sunt mulțumit?</h3>
                                        <i class="fas fa-plus"></i>
                                    </div>
                                    <div class="faq-answer">
                                        <p>Da, oferim perioada de retur de 30 de zile calendaristice. Telefonul trebuie să fie în aceeași stare în care l-ai primit, cu toate accesoriile și ambalajul original. Costurile de retur sunt suportate de noi.</p>
                                    </div>
                                </div>
                                
                                <div class="faq-item">
                                    <div class="faq-question">
                                        <h3 class="faq-question-title">Acceptați plata în rate fără dobândă?</h3>
                                        <i class="fas fa-plus"></i>
                                    </div>
                                    <div class="faq-answer">
                                        <p>Da, oferim posibilitatea de plată în rate fără dobândă până la 24 de luni, în funcție de valoarea produsului și partenerul bancar ales. Procesul de aprobare este rapid și simplu.</p>
                                    </div>
                                </div>
                                
                                <div class="faq-item">
                                    <div class="faq-question">
                                        <h3 class="faq-question-title">Pot schimba telefonul vechi cu unul refurbished?</h3>
                                        <i class="fas fa-plus"></i>
                                    </div>
                                    <div class="faq-answer">
                                        <p>Da, oferim serviciul de schimb pentru telefoanele tale vechi. Evaluăm telefonul tău și îți oferim o reducere la prețul unui telefon refurbished nou. Contactează-ne pentru o evaluare gratuită.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="faq-image">
                            <img src="https://haloo.lemon.thisisfruit.com/wp-content/uploads/2025/09/mobile.png" alt="Telefon refurbished" class="mobile-phone-image">
                        </div>
                    </div>
                </div>
            </section>

        </main>
    </div>
</div>

<script>
// FAQ Toggle Functionality
document.addEventListener('DOMContentLoaded', function() {
    const faqItems = document.querySelectorAll('.faq-item');
    
    faqItems.forEach(item => {
        const question = item.querySelector('.faq-question');
        
        question.addEventListener('click', () => {
            // Close other FAQ items
            faqItems.forEach(otherItem => {
                if (otherItem !== item) {
                    otherItem.classList.remove('active');
                }
            });
            
            // Toggle current item
            item.classList.toggle('active');
        });
    });
});

// Contact Form Handling with Resend
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('contactForm');
    const submitBtn = document.getElementById('submitBtn');
    const formSuccess = document.getElementById('formSuccess');
    const formError = document.getElementById('formError');
    
    if (form) {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Clear previous errors
            clearErrors();
            
            // Validate form
            if (!validateForm()) {
                return;
            }
            
            // Show loading state
            setLoadingState(true);
            
            try {
                       // Get form data
                       const formData = new FormData(form);
                       const data = {
                           name: formData.get('name'),
                           lastname: formData.get('lastname'),
                           email: formData.get('email'),
                           phone: formData.get('phone'),
                           country: formData.get('country'),
                           subject: formData.get('subject'),
                           message: formData.get('message'),
                           services: formData.getAll('services[]'),
                           privacy: formData.get('privacy')
                       };
                
                // Send to Resend API
                const response = await fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        action: 'send_contact_email',
                        ...data
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showSuccess();
                    form.reset();
                } else {
                    showError(result.message || 'A apărut o eroare la trimiterea mesajului.');
                }
                
            } catch (error) {
                console.error('Error:', error);
                showError('A apărut o eroare la trimiterea mesajului. Te rugăm să încerci din nou.');
            } finally {
                setLoadingState(false);
            }
        });
    }
    
    function validateForm() {
        let isValid = true;
        
        // Validate name
        const name = document.getElementById('name').value.trim();
        if (!name) {
            showError('name', 'Numele este obligatoriu.');
            isValid = false;
        }
        
        // Validate lastname
        const lastname = document.getElementById('lastname').value.trim();
        if (!lastname) {
            showError('lastname', 'Prenumele este obligatoriu.');
            isValid = false;
        }
        
        // Validate email
        const email = document.getElementById('email').value.trim();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!email) {
            showError('email', 'Email-ul este obligatoriu.');
            isValid = false;
        } else if (!emailRegex.test(email)) {
            showError('email', 'Introduceți o adresă de email validă.');
            isValid = false;
        }
        
        // Validate phone (optional but if provided, should be valid)
        const phone = document.getElementById('phone').value.trim();
        if (phone && phone.length < 10) {
            showError('phone', 'Numărul de telefon trebuie să aibă cel puțin 10 cifre.');
            isValid = false;
        }
        
        // Validate subject
        const subject = document.getElementById('subject').value;
        if (!subject) {
            showError('subject', 'Subiectul este obligatoriu.');
            isValid = false;
        }
        
        // Validate message
        const message = document.getElementById('message').value.trim();
        if (!message) {
            showError('message', 'Mesajul este obligatoriu.');
            isValid = false;
        }
        
        // Validate privacy
        const privacy = document.getElementById('privacy').checked;
        if (!privacy) {
            showError('privacy', 'Trebuie să fiți de acord cu politica de confidențialitate.');
            isValid = false;
        }
        
        return isValid;
    }
    
    function showError(field, message) {
        const errorElement = document.getElementById(field + 'Error');
        const formGroup = errorElement.closest('.form-group');
        
        if (errorElement && formGroup) {
            errorElement.textContent = message;
            errorElement.classList.add('show');
            formGroup.classList.add('error');
        }
    }
    
    function clearErrors() {
        const errorMessages = document.querySelectorAll('.error-message');
        const formGroups = document.querySelectorAll('.form-group');
        
        errorMessages.forEach(error => {
            error.classList.remove('show');
            error.textContent = '';
        });
        
        formGroups.forEach(group => {
            group.classList.remove('error');
        });
        
        formSuccess.style.display = 'none';
        formError.style.display = 'none';
    }
    
    function setLoadingState(loading) {
        if (loading) {
            submitBtn.disabled = true;
            submitBtn.classList.add('loading');
        } else {
            submitBtn.disabled = false;
            submitBtn.classList.remove('loading');
        }
    }
    
    function showSuccess() {
        formSuccess.style.display = 'block';
        formError.style.display = 'none';
        form.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
    
    function showError(message) {
        formError.querySelector('p').textContent = message;
        formError.style.display = 'block';
        formSuccess.style.display = 'none';
        form.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
});
</script>

<?php
get_footer();
?>
