# 🧪 Ghid de Load Testing pentru Shopwell

Acest ghid vă ajută să testați performanța site-ului WordPress/WooCommerce când sunt mai mulți utilizatori simultan.

## 📋 Cuprins

1. [Ce este Load Testing?](#ce-este-load-testing)
2. [Tool-uri Recomandate](#tool-uri-recomandate)
3. [Setup și Configurare](#setup-și-configurare)
4. [Scenarii de Testare](#scenarii-de-testare)
5. [Monitorizare și Metrici](#monitorizare-și-metrici)
6. [Interpretarea Rezultatelor](#interpretarea-rezultatelor)

---

## 🎯 Ce este Load Testing?

Load testing simulează trafic real pentru a identifica:
- **Puncte de eșec** (când site-ul devine lent sau se blochează)
- **Lățimea de bandă necesară**
- **Probleme de performanță** sub sarcină
- **Capacitatea maximă** a serverului

**⚠️ IMPORTANT:** Conform analizei de performanță existente, site-ul are probleme critice care pot cauza crash-uri la **50+ utilizatori simultani**.

---

## 🛠️ Tool-uri Recomandate

### 1. **Apache JMeter** (Recomandat - Gratuit)
- **Descărcare:** https://jmeter.apache.org/download_jmeter.cgi
- **Avantaje:** 
  - Open source și gratuit
  - Interfață grafică
  - Suport pentru HTTP/HTTPS, AJAX, WebSockets
  - Rapoarte detaliate
- **Dezavantaje:** 
  - Necesită Java instalat
  - Consumă resurse pe mașina locală

### 2. **k6** (Rapid și Modern)
- **Descărcare:** https://k6.io/docs/getting-started/installation/
- **Avantaje:**
  - Foarte rapid
  - Scripturi în JavaScript
  - CLI-based (ușor de automatizat)
- **Dezavantaje:**
  - Fără interfață grafică

### 3. **Locust** (Python-based)
- **Descărcare:** `pip install locust`
- **Avantaje:**
  - Scripturi în Python
  - Interfață web pentru monitoring în timp real
  - Distribuit (poate rula pe mai multe mașini)

### 4. **Artillery** (Node.js)
- **Descărcare:** `npm install -g artillery`
- **Avantaje:**
  - Ușor de folosit
  - Suport pentru WebSockets și HTTP

### 5. **Cloud Services** (Pentru teste mai mari)
- **Loader.io** (Gratuit până la 10k requests/lună)
- **BlazeMeter** (Trial gratuit)
- **AWS Load Testing** (Plătit)

---

## ⚙️ Setup și Configurare

### Opțiunea 1: Apache JMeter (Recomandat pentru începători)

#### Instalare:
1. Descarcă JMeter de pe https://jmeter.apache.org/download_jmeter.cgi
2. Extrage arhiva
3. Rulează `bin/jmeter.bat` (Windows) sau `bin/jmeter.sh` (Linux/Mac)

#### Configurare Test Plan:

1. **Creează un Test Plan nou**
   - Right-click pe Test Plan → Add → Threads (Users) → Thread Group

2. **Configurează Thread Group:**
   ```
   Number of Threads (users): 50
   Ramp-up Period (seconds): 60
   Loop Count: 10
   ```
   - **Number of Threads:** Câți utilizatori simultani
   - **Ramp-up Period:** Cât timp să crească treptat numărul de utilizatori
   - **Loop Count:** De câte ori fiecare utilizator execută testul

3. **Adaugă HTTP Request Sampler:**
   - Right-click pe Thread Group → Add → Sampler → HTTP Request
   - Configurează:
     ```
     Server Name or IP: [domeniul-tau.com]
     Protocol: https (sau http)
     Path: / (pentru homepage)
     Method: GET
     ```

4. **Adaugă Listeners pentru Rezultate:**
   - **View Results Tree** (pentru detalii)
   - **Summary Report** (pentru statistici)
   - **Graph Results** (pentru grafice)

5. **Salvează Test Plan:**
   - File → Save As → `shopwell-load-test.jmx`

### Opțiunea 2: k6 (Rapid și Modern)

#### Instalare:
```bash
# Windows (cu Chocolatey)
choco install k6

# Sau descarcă de pe https://k6.io/docs/getting-started/installation/
```

#### Creează script de test (`load-test.js`):

```javascript
import http from 'k6/http';
import { check, sleep } from 'k6';

export const options = {
  stages: [
    { duration: '1m', target: 20 },   // Crește la 20 utilizatori în 1 minut
    { duration: '3m', target: 50 },   // Menține 50 utilizatori timp de 3 minute
    { duration: '1m', target: 0 },   // Scade la 0 utilizatori în 1 minut
  ],
  thresholds: {
    http_req_duration: ['p(95)<2000'], // 95% din request-uri trebuie să fie sub 2 secunde
    http_req_failed: ['rate<0.01'],     // Mai puțin de 1% erori
  },
};

const BASE_URL = 'https://domeniul-tau.com';

export default function () {
  // Test homepage
  let response = http.get(BASE_URL);
  check(response, {
    'homepage status is 200': (r) => r.status === 200,
    'homepage response time < 2s': (r) => r.timings.duration < 2000,
  });

  sleep(1);

  // Test search (dacă există)
  response = http.get(`${BASE_URL}/?s=test`);
  check(response, {
    'search status is 200': (r) => r.status === 200,
  });

  sleep(2);

  // Test product page (înlocuiește cu URL real)
  response = http.get(`${BASE_URL}/product/example-product/`);
  check(response, {
    'product page status is 200': (r) => r.status === 200,
  });

  sleep(1);
}
```

#### Rulează testul:
```bash
k6 run load-test.js
```

### Opțiunea 3: Locust (Python)

#### Instalare:
```bash
pip install locust
```

#### Creează script (`locustfile.py`):

```python
from locust import HttpUser, task, between

class ShopwellUser(HttpUser):
    wait_time = between(1, 3)  # Așteaptă 1-3 secunde între request-uri
    
    def on_start(self):
        """Rulează la începutul fiecărui utilizator"""
        self.client.get("/")
    
    @task(3)
    def view_homepage(self):
        """Vizitează homepage (prioritate 3)"""
        self.client.get("/")
    
    @task(2)
    def search_products(self):
        """Caută produse (prioritate 2)"""
        self.client.get("/?s=test")
    
    @task(1)
    def view_product(self):
        """Vizitează pagina unui produs (prioritate 1)"""
        # Înlocuiește cu URL-uri reale de produse
        self.client.get("/product/example-product/")
    
    @task(1)
    def view_category(self):
        """Vizitează o categorie"""
        self.client.get("/product-category/example/")
```

#### Rulează testul:
```bash
locust -f locustfile.py --host=https://domeniul-tau.com
```

Apoi deschide browser-ul la `http://localhost:8089` pentru interfața web.

---

## 🎬 Scenarii de Testare

### Scenariul 1: Test de Bază (10-20 utilizatori)
**Scop:** Verifică comportamentul normal

```
Utilizatori: 20
Durată: 5 minute
Ramp-up: 1 minut
```

### Scenariul 2: Test de Sarcină Normală (50 utilizatori)
**Scop:** Simulează trafic normal

```
Utilizatori: 50
Durată: 10 minute
Ramp-up: 2 minute
```

### Scenariul 3: Test de Sarcină Maximă (100+ utilizatori)
**Scop:** Identifică punctul de eșec

```
Utilizatori: 100
Durată: 15 minute
Ramp-up: 5 minute
```

### Scenariul 4: Test de Spike (Creștere Bruscă)
**Scop:** Testează comportamentul la creșteri bruște de trafic

```
Utilizatori: 10 → 100 în 30 secunde
Durată: 5 minute
```

### Scenariul 5: Test de Endurance (Durabilitate)
**Scop:** Verifică dacă există memory leaks

```
Utilizatori: 30
Durată: 1 oră
```

---

## 📊 Monitorizare și Metrici

### Metrici Cheie de Monitorizat:

1. **Response Time (Timp de Răspuns)**
   - **Target:** < 2 secunde pentru 95% din request-uri
   - **Acceptabil:** < 5 secunde
   - **Critic:** > 10 secunde

2. **Throughput (Debit)**
   - Numărul de request-uri procesate pe secundă
   - Arată capacitatea serverului

3. **Error Rate (Rata de Erori)**
   - **Target:** < 1%
   - **Acceptabil:** < 5%
   - **Critic:** > 10%

4. **Concurrent Users (Utilizatori Simultani)**
   - Câți utilizatori pot fi susținuți simultan

5. **Server Resources (Resurse Server)**
   - **CPU Usage:** < 80%
   - **Memory Usage:** < 80%
   - **Database Connections:** Monitorizează conexiunile MySQL

### Cum să Monitorizezi Serverul:

#### 1. **WordPress Debug Log**
Activează în `wp-config.php`:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

#### 2. **Server Monitoring Tools:**
- **cPanel/WHM:** Resource Usage
- **New Relic** (trial gratuit)
- **Query Monitor** (plugin WordPress)
- **Server Status** (plugin WordPress)

#### 3. **Database Monitoring:**
```sql
-- Verifică procesele active MySQL
SHOW PROCESSLIST;

-- Verifică conexiunile
SHOW STATUS LIKE 'Threads_connected';
```

---

## 📈 Interpretarea Rezultatelor

### Rezultate Bune:
- ✅ Response time < 2s pentru 95% din request-uri
- ✅ Error rate < 1%
- ✅ CPU și Memory < 80%
- ✅ Site-ul răspunde constant

### Semne de Probleme:
- ⚠️ Response time crește odată cu numărul de utilizatori
- ⚠️ Error rate > 5%
- ⚠️ Timeout-uri frecvente
- ⚠️ CPU sau Memory > 90%

### Probleme Critice:
- 🔴 Site-ul devine inaccesibil
- 🔴 Erori 500 (Internal Server Error)
- 🔴 Database connection errors
- 🔴 Memory limit exceeded

---

## 🔧 Optimizări Recomandate înainte de Testare

Conform analizei de performanță existente (`PERFORMANCE_FLAWS_ANALYSIS.md`), site-ul are probleme critice care trebuie rezolvate:

### 1. **Optimizare Căutare AJAX**
- Problema: 3 query-uri separate pentru fiecare căutare
- Impact: Server crash la 20+ căutări simultane
- **Soluție:** Implementează caching și limitează query-urile

### 2. **Optimizare Database Queries**
- Problema: Query-uri neoptimizate și duplicate
- Impact: Database overload
- **Soluție:** Folosește Query Monitor pentru identificare

### 3. **Cache Implementation**
- Implementează caching (WP Super Cache, W3 Total Cache, sau Redis)
- Activează object cache pentru WooCommerce

### 4. **CDN pentru Assets**
- Folosește CDN pentru imagini, CSS, JS
- Reduce încărcarea serverului

### 5. **Database Optimization**
- Optimizează tabelele MySQL
- Adaugă indexuri pentru query-uri frecvente

---

## 🚀 Quick Start - Test Rapid

### Pasul 1: Pregătește Site-ul
```bash
# Activează debug logging
# Editează wp-config.php și adaugă:
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

### Pasul 2: Instalează k6
```bash
# Windows
choco install k6

# Sau descarcă de pe https://k6.io
```

### Pasul 3: Creează Script Rapid
Salvează ca `quick-test.js`:
```javascript
import http from 'k6/http';
import { check } from 'k6';

export const options = {
  vus: 20,        // 20 utilizatori simultani
  duration: '2m', // Durată: 2 minute
};

export default function () {
  const response = http.get('https://domeniul-tau.com');
  check(response, {
    'status is 200': (r) => r.status === 200,
    'response time < 3s': (r) => r.timings.duration < 3000,
  });
}
```

### Pasul 4: Rulează Testul
```bash
k6 run quick-test.js
```

### Pasul 5: Analizează Rezultatele
- Verifică response time
- Verifică error rate
- Monitorizează resursele serverului

---

## 📝 Checklist Pre-Testare

- [ ] Backup complet al site-ului și bazei de date
- [ ] Debug logging activat
- [ ] Monitoring tools configurate
- [ ] Testează pe staging environment (NU pe producție!)
- [ ] Informează hosting provider (dacă e necesar)
- [ ] Documentează configurația serverului actuală
- [ ] Pregătește plan de rollback

---

## ⚠️ Avertismente Importante

1. **NU testa pe producție direct!** Folosește un environment de staging.
2. **Informează hosting provider** înainte de teste mari (pot considera DDoS).
3. **Monitorizează resursele** pentru a evita suprasolicitarea serverului.
4. **Backup complet** înainte de orice testare.
5. **Testează progresiv** - începe cu puțini utilizatori și crește treptat.

---

## 📚 Resurse Suplimentare

- [JMeter Documentation](https://jmeter.apache.org/usermanual/index.html)
- [k6 Documentation](https://k6.io/docs/)
- [Locust Documentation](https://docs.locust.io/)
- [WordPress Performance Best Practices](https://wordpress.org/support/article/optimization/)

---

## 🆘 Suport

Dacă întâmpinați probleme:
1. Verifică log-urile WordPress (`wp-content/debug.log`)
2. Verifică log-urile serverului (error.log, access.log)
3. Verifică resursele serverului (CPU, Memory, Disk)
4. Consultă documentația tool-ului folosit

---

**Ultima actualizare:** 2025-01-27

