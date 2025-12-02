<?php
/**
 * Template Name: Smartphone Quiz
 * 
 * Smartphone Quiz Template
 *
 * Interactive quiz to help users find the perfect refurbished smartphone
 *
 * @package Shopwell
 */

get_header();
?>

<!-- CSS moved to external file: assets/css/pages/quiz.css -->

<div class="quiz-container">
    <div class="quiz-header">
        <h1 class="quiz-title">Găsește telefonul perfect pentru tine</h1>
        <p class="quiz-subtitle">Răspunde la câteva întrebări simple și îți vom recomanda cel mai potrivit smartphone refurbished</p>
        <div class="quiz-progress">
            <div class="quiz-progress-bar" id="progressBar"></div>
        </div>
    </div>

    <!-- Question 1 -->
    <div class="question-container active" data-question="1">
        <h2 class="question-title">Care este bugetul tău pentru un telefon?</h2>
        <div class="options-container">
            <div class="option" data-value="budget">
                <p class="option-text">Sub 800 lei - Telefon de bază</p>
            </div>
            <div class="option" data-value="mid">
                <p class="option-text">800-1500 lei - Telefon mid-range</p>
            </div>
            <div class="option" data-value="premium">
                <p class="option-text">1500-2500 lei - Telefon premium</p>
            </div>
            <div class="option" data-value="flagship">
                <p class="option-text">Peste 2500 lei - Flagship</p>
            </div>
        </div>
    </div>

    <!-- Question 2 -->
    <div class="question-container" data-question="2">
        <h2 class="question-title">Ce dimensiune de ecran preferi?</h2>
        <div class="options-container">
            <div class="option" data-value="small">
                <p class="option-text">Sub 6 inch - Compact și ușor de folosit</p>
            </div>
            <div class="option" data-value="medium">
                <p class="option-text">6-6.5 inch - Echilibrat</p>
            </div>
            <div class="option" data-value="large">
                <p class="option-text">6.5-7 inch - Mare pentru media</p>
            </div>
            <div class="option" data-value="xlarge">
                <p class="option-text">Peste 7 inch - Foarte mare</p>
            </div>
        </div>
    </div>

    <!-- Question 3 -->
    <div class="question-container" data-question="3">
        <h2 class="question-title">Care este utilizarea principală?</h2>
        <div class="options-container">
            <div class="option" data-value="basic">
                <p class="option-text">Apeluri, mesaje, internet de bază</p>
            </div>
            <div class="option" data-value="social">
                <p class="option-text">Social media, fotografie, streaming</p>
            </div>
            <div class="option" data-value="gaming">
                <p class="option-text">Gaming și aplicații intensive</p>
            </div>
            <div class="option" data-value="business">
                <p class="option-text">Muncă, productivitate, securitate</p>
            </div>
        </div>
    </div>

    <!-- Question 4 -->
    <div class="question-container" data-question="4">
        <h2 class="question-title">Cât de importantă este camera foto?</h2>
        <div class="options-container">
            <div class="option" data-value="low">
                <p class="option-text">Nu e importantă - doar selfie-uri ocazionale</p>
            </div>
            <div class="option" data-value="medium">
                <p class="option-text">Moderat importantă - poze de familie</p>
            </div>
            <div class="option" data-value="high">
                <p class="option-text">Foarte importantă - fotografie hobby</p>
            </div>
            <div class="option" data-value="professional">
                <p class="option-text">Critică - fotografie profesională</p>
            </div>
        </div>
    </div>

    <!-- Question 5 -->
    <div class="question-container" data-question="5">
        <h2 class="question-title">Ce brand preferi?</h2>
        <div class="options-container">
            <div class="option" data-value="apple">
                <p class="option-text">Apple iPhone</p>
            </div>
            <div class="option" data-value="samsung">
                <p class="option-text">Samsung Galaxy</p>
            </div>
            <div class="option" data-value="huawei">
                <p class="option-text">Huawei</p>
            </div>
            <div class="option" data-value="any">
                <p class="option-text">Nu am preferințe - orice brand bun</p>
            </div>
        </div>
    </div>

    <!-- Question 6 -->
    <div class="question-container" data-question="6">
        <h2 class="question-title">Cât de mult folosești telefonul într-o zi?</h2>
        <div class="options-container">
            <div class="option" data-value="light">
                <p class="option-text">Sub 3 ore - utilizare ușoară</p>
            </div>
            <div class="option" data-value="moderate">
                <p class="option-text">3-6 ore - utilizare moderată</p>
            </div>
            <div class="option" data-value="heavy">
                <p class="option-text">6-10 ore - utilizare intensă</p>
            </div>
            <div class="option" data-value="extreme">
                <p class="option-text">Peste 10 ore - utilizare extremă</p>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <div class="quiz-navigation">
        <button class="btn-quiz btn-prev" id="prevBtn" disabled>Înapoi</button>
        <span class="quiz-counter">
            <span id="currentQuestion">1</span> din <span id="totalQuestions">6</span>
        </span>
        <button class="btn-quiz btn-next" id="nextBtn" disabled>Următorul</button>
    </div>

    <!-- Email Step (Optional) -->
    <div class="email-container" id="emailStep" style="display: none; animation: fadeUp 0.6s ease-out;">
        <div style="text-align: center; margin-bottom: 40px;">
            <div style="font-size: 3rem; margin-bottom: 20px;">📧</div>
            <h2 class="question-title">Vrei să primești recomandarea pe email?</h2>
            <p style="color: #666; font-size: 1rem; max-width: 500px; margin: 15px auto;">
                Opțional - îți vom trimite recomandarea personalizată și oferte exclusive pentru telefonul tău ideal
            </p>
        </div>
        
        <div style="max-width: 450px; margin: 0 auto;">
            <input 
                type="email" 
                id="userEmail" 
                placeholder="adresa@email.com"
                style="width: 100%; padding: 18px 24px; border: 2px solid #e9ecef; border-radius: 12px; font-size: 1rem; margin-bottom: 20px; transition: all 0.3s ease; box-sizing: border-box;"
                onfocus="this.style.borderColor='#66fa95'; this.style.boxShadow='0 0 0 4px rgba(102, 250, 149, 0.1)'"
                onblur="this.style.borderColor='#e9ecef'; this.style.boxShadow='none'"
            />
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <button class="btn-quiz" onclick="skipEmail()" style="background: #fff; color: #666; border: 2px solid #e9ecef; padding: 16px;">
                    Sari peste
                </button>
                <button class="btn-quiz btn-next" onclick="saveEmail()" style="padding: 16px;">
                    Continuă →
                </button>
            </div>
        </div>
        
        <p style="text-align: center; font-size: 0.9rem; color: #999; margin-top: 30px; display: flex; align-items: center; justify-content: center; gap: 8px;">
            <span style="font-size: 1.2rem;">🔒</span>
            <span>Nu vom trimite spam. Poți să te dezabonezi oricând.</span>
        </p>
    </div>

    <!-- Results -->
    <div class="results-container" id="results">
        <h2 class="results-title">Recomandarea noastră pentru tine</h2>
        
        <div class="recommended-phone" id="recommendedPhone">
            <!-- Dynamic content will be inserted here -->
        </div>

        <div class="alternative-phones">
            <h3 class="alternative-title">Alternative recomandate</h3>
            <div class="alternative-grid" id="alternativePhones">
                <!-- Dynamic content will be inserted here -->
            </div>
        </div>

        <a href="#" class="restart-quiz" onclick="restartQuiz()">Începe din nou</a>
    </div>
</div>

<script>
let currentQuestion = 1;
const totalQuestions = 6;
let answers = {};

// Initialize quiz
document.addEventListener('DOMContentLoaded', function() {
    updateProgress();
    setupEventListeners();
});

function setupEventListeners() {
    // Option selection
    document.querySelectorAll('.option').forEach(option => {
        option.addEventListener('click', function() {
            const questionContainer = this.closest('.question-container');
            const questionNumber = parseInt(questionContainer.dataset.question);
            
            // Remove previous selection
            questionContainer.querySelectorAll('.option').forEach(opt => opt.classList.remove('selected'));
            
            // Add selection to clicked option
            this.classList.add('selected');
            
            // Store answer
            answers[questionNumber] = this.dataset.value;
            
            // Enable next button
            document.getElementById('nextBtn').disabled = false;
        });
    });

    // Navigation buttons
    document.getElementById('nextBtn').addEventListener('click', nextQuestion);
    document.getElementById('prevBtn').addEventListener('click', prevQuestion);
}

function nextQuestion() {
    if (currentQuestion < totalQuestions) {
        currentQuestion++;
        showQuestion(currentQuestion);
        updateProgress();
        updateNavigation();
    } else {
        showEmailStep();
    }
}

function showEmailStep() {
    // Hide quiz
    document.querySelectorAll('.question-container').forEach(q => q.style.display = 'none');
    document.querySelector('.quiz-navigation').style.display = 'none';
    document.querySelector('.quiz-progress').style.display = 'none';
    
    // Show email step with animation
    const emailStep = document.getElementById('emailStep');
    emailStep.style.display = 'block';
    emailStep.style.animation = 'fadeUp 0.6s ease-out';
}

function skipEmail() {
    showResults();
}

function saveEmail() {
    const email = document.getElementById('userEmail').value;
    
    if (email && validateEmail(email)) {
        // Save email with preferences
        fetch('/wp-admin/admin-ajax.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'quiz_save_preferences',
                email: email,
                preferences: JSON.stringify(answers)
            })
        })
        .then(response => response.json())
        .then(data => {
            console.log('Preferences saved:', data);
        })
        .catch(error => {
            console.error('Error saving preferences:', error);
        });
        
        showResults();
    } else if (!email) {
        // If no email, just continue
        showResults();
    } else {
        alert('Te rugăm să introduci o adresă de email validă');
    }
}

function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function prevQuestion() {
    if (currentQuestion > 1) {
        currentQuestion--;
        showQuestion(currentQuestion);
        updateProgress();
        updateNavigation();
    }
}

function showQuestion(questionNumber) {
    // Hide all questions
    document.querySelectorAll('.question-container').forEach(q => q.classList.remove('active'));
    
    // Show current question
    document.querySelector(`[data-question="${questionNumber}"]`).classList.add('active');
    
    // Check if question has been answered
    const hasAnswer = answers[questionNumber];
    document.getElementById('nextBtn').disabled = !hasAnswer;
}

function updateProgress() {
    const progress = (currentQuestion / totalQuestions) * 100;
    document.getElementById('progressBar').style.width = progress + '%';
    document.getElementById('currentQuestion').textContent = currentQuestion;
}

function updateNavigation() {
    document.getElementById('prevBtn').disabled = currentQuestion === 1;
    
    if (currentQuestion === totalQuestions) {
        document.getElementById('nextBtn').textContent = 'Vezi rezultatele';
    } else {
        document.getElementById('nextBtn').textContent = 'Următorul';
    }
}

function showResults() {
    // Hide everything
    document.querySelector('.quiz-header').style.display = 'none';
    document.querySelectorAll('.question-container').forEach(q => q.style.display = 'none');
    document.querySelector('.quiz-navigation').style.display = 'none';
    document.getElementById('emailStep').style.display = 'none';
    
    // Show results
    document.getElementById('results').style.display = 'block';
    
    // Generate recommendations
    generateRecommendations();
}

function generateRecommendations() {
    const budget = answers[1];
    const screenSize = answers[2];
    const usage = answers[3];
    const camera = answers[4];
    const brand = answers[5];
    const usageTime = answers[6];
    
    // Show loading animation with Haloooo
    document.getElementById('recommendedPhone').innerHTML = `
        <div class="loading-animation">
            <div class="loading-text">
                Hal<span class="loading-dots">
                    <span>o</span><span>o</span><span>o</span><span>o</span><span>o</span>
                </span>
            </div>
            <div class="loading-spinner"></div>
            <p class="loading-message">Căutăm telefonul perfect pentru tine...</p>
        </div>
    `;
    document.getElementById('alternativePhones').innerHTML = '';
    
    // Get products from server
    fetch('/wp-admin/admin-ajax.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            action: 'quiz_get_products',
            budget: budget,
            screen_size: screenSize,
            usage: usage,
            camera: camera,
            brand: brand,
            usage_time: usageTime,
            nonce: '<?php echo wp_create_nonce("quiz_nonce"); ?>'
        })
    })
    .then(response => response.json())
    .then(data => {
        console.log('Quiz API Response:', data);
        if (data.success) {
            displayRecommendations(data.data);
        } else {
            console.error('Quiz Error:', data.data);
            displayError(data.data);
        }
    })
    .catch(error => {
        console.error('Quiz Fetch Error:', error);
        displayError();
    });
}

function displayRecommendations(data) {
    const { recommended, alternatives } = data;
    
    // Display recommended phone with animation
    const recommendedContainer = document.getElementById('recommendedPhone');
    recommendedContainer.style.animation = 'fadeUp 0.6s ease-out';
    recommendedContainer.innerHTML = `
        <div class="phone-name">${recommended.name}</div>
        <div class="phone-description">${recommended.price}</div>
        <div class="phone-stock">${recommended.stock_status}</div>
        <ul class="phone-features">
            ${recommended.features.map(feature => `<li>✓ ${feature}</li>`).join('')}
        </ul>
        <a href="${recommended.url}" class="btn-shop">Vezi în magazin →</a>
    `;
    
    // Display alternatives with staggered animation
    const alternativesContainer = document.getElementById('alternativePhones');
    alternativesContainer.innerHTML = alternatives.map(phone => `
        <div class="alternative-phone">
            <h4>${phone.name}</h4>
            <p style="color: #4CAF50; font-weight: 600; font-size: 1.1rem; margin: 10px 0;">${phone.price}</p>
            <p style="font-size: 0.9rem; color: #666;">${phone.stock_status}</p>
            <ul>
                ${phone.features.map(feature => `<li>✓ ${feature}</li>`).join('')}
            </ul>
            <a href="${phone.url}" class="btn-shop" style="font-size: 0.95rem; padding: 12px 30px;">Vezi detalii →</a>
        </div>
    `).join('');
}

function displayError(debugData) {
    let debugHtml = '';
    if (debugData && debugData.debug) {
        debugHtml = `
            <div style="background: #f0f0f0; padding: 15px; margin-top: 20px; border-radius: 5px; text-align: left; font-size: 12px;">
                <strong>Debug Info:</strong><br>
                <pre style="white-space: pre-wrap; word-wrap: break-word;">${JSON.stringify(debugData.debug, null, 2)}</pre>
            </div>
        `;
    }
    
    document.getElementById('recommendedPhone').innerHTML = `
        <div style="text-align: center; padding: 40px; color: #666;">
            <p>Ne pare rău, nu am putut găsi produse potrivite.</p>
            <p style="font-size: 14px; color: #999;">Verifică consola browserului pentru mai multe detalii.</p>
            <a href="/shop/" class="btn-shop">Vezi toate produsele</a>
            ${debugHtml}
        </div>
    `;
}


function restartQuiz() {
    currentQuestion = 1;
    answers = {};
    
    // Reset UI
    document.querySelector('.quiz-header').style.display = 'block';
    document.querySelectorAll('.question-container').forEach(q => q.style.display = 'block');
    document.querySelector('.quiz-navigation').style.display = 'flex';
    document.getElementById('results').style.display = 'none';
    
    // Reset selections
    document.querySelectorAll('.option').forEach(opt => opt.classList.remove('selected'));
    
    // Show first question
    showQuestion(1);
    updateProgress();
    updateNavigation();
}
</script>

<?php get_footer(); ?>
