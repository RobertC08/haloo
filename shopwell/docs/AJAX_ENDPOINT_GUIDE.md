# Smartphone Quiz - AJAX Endpoint Guide

## Overview

Acest ghid explică cum să folosești endpoint-ul AJAX pentru a obține primele 8 produse recomandate din quiz-ul de smartphone.

## Endpoint: `quiz_get_top_recommendations`

Acest endpoint returnează primele 8 produse cele mai recomandate, sortate după numărul de recomandări (descrescător).

---

## Detalii Endpoint

**Action:** `quiz_get_top_recommendations`  
**Method:** `POST`  
**URL:** `/wp-admin/admin-ajax.php` (sau folosește variabila `ajaxurl` în WordPress)  
**Authentication:** Nu este necesară (disponibil și pentru utilizatori neautentificați)

---

## Format Request

### Parametri

| Parametru | Tip | Obligatoriu | Descriere |
|-----------|-----|-------------|-----------|
| `action` | string | Da | Trebuie să fie `quiz_get_top_recommendations` |

### Exemplu Request

```javascript
{
    action: 'quiz_get_top_recommendations'
}
```

---

## Format Response

### Success Response

```json
{
    "success": true,
    "data": {
        "recommendations": [
            {
                "product_id": 123,
                "variation_id": 456,
                "name": "iPhone 13 Pro 128GB",
                "price": "4.999,00 lei",
                "price_raw": 4999.00,
                "url": "https://example.com/product/iphone-13-pro/",
                "image": "https://example.com/wp-content/uploads/2024/01/iphone-13-pro.jpg",
                "stock_status": "În stoc (5 buc.)",
                "features": [
                    "Telefon refurbished certificat",
                    "Garanție 12 luni",
                    "Culoare: Graphite",
                    "Stare: Grade A",
                    "Memorie: 128GB"
                ],
                "recommendation_count": 42
            }
        ],
        "total": 8
    }
}
```

### Error Response

```json
{
    "success": false,
    "data": {
        "message": "Error message here"
    }
}
```

---

## Exemple de Implementare

### 1. jQuery (Recomandat pentru WordPress)

```javascript
jQuery(document).ready(function($) {
    $.ajax({
        url: ajaxurl, // Variabilă disponibilă în WordPress admin
        // SAU url: '/wp-admin/admin-ajax.php', // Pentru frontend
        type: 'POST',
        data: {
            action: 'quiz_get_top_recommendations'
        },
        success: function(response) {
            if (response.success) {
                console.log('Total recommendations:', response.data.total);
                response.data.recommendations.forEach(function(product) {
                    console.log(product.name, '-', product.recommendation_count, 'recomandări');
                });
            } else {
                console.error('Error:', response.data.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', error);
        }
    });
});
```

### 2. Vanilla JavaScript (Fetch API)

```javascript
fetch('/wp-admin/admin-ajax.php', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
    },
    body: new URLSearchParams({
        action: 'quiz_get_top_recommendations'
    })
})
.then(response => response.json())
.then(data => {
    if (data.success) {
        console.log('Total recommendations:', data.data.total);
        data.data.recommendations.forEach(product => {
            console.log(product.name, '-', product.recommendation_count, 'recomandări');
        });
    } else {
        console.error('Error:', data.data.message);
    }
})
.catch(error => {
    console.error('Fetch Error:', error);
});
```

### 3. Axios

```javascript
import axios from 'axios';

axios.post('/wp-admin/admin-ajax.php', {
    action: 'quiz_get_top_recommendations'
})
.then(response => {
    if (response.data.success) {
        console.log('Recommendations:', response.data.data.recommendations);
    }
})
.catch(error => {
    console.error('Error:', error);
});
```

### 4. React Hook Example

```javascript
import { useState, useEffect } from 'react';

function useTopRecommendations() {
    const [recommendations, setRecommendations] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        fetch('/wp-admin/admin-ajax.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'quiz_get_top_recommendations'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                setRecommendations(data.data.recommendations);
            } else {
                setError(data.data.message);
            }
            setLoading(false);
        })
        .catch(err => {
            setError(err.message);
            setLoading(false);
        });
    }, []);

    return { recommendations, loading, error };
}

// Usage in component
function TopRecommendations() {
    const { recommendations, loading, error } = useTopRecommendations();

    if (loading) return <div>Loading...</div>;
    if (error) return <div>Error: {error}</div>;

    return (
        <div>
            <h2>Top 8 Produse Recomandate</h2>
            {recommendations.map(product => (
                <div key={product.product_id}>
                    <img src={product.image} alt={product.name} />
                    <h3>{product.name}</h3>
                    <p>{product.price}</p>
                    <p>{product.recommendation_count} recomandări</p>
                    <a href={product.url}>Vezi Produs</a>
                </div>
            ))}
        </div>
    );
}
```

---

## Structura Datelor Returnate

### Recommendation Object

| Câmp | Tip | Descriere |
|------|-----|-----------|
| `product_id` | integer | ID-ul produsului WooCommerce |
| `variation_id` | integer\|null | ID-ul variației (null pentru produse simple) |
| `name` | string | Numele complet al produsului |
| `price` | string | Prețul formatat (ex: "4.999,00 lei") |
| `price_raw` | float | Prețul numeric (ex: 4999.00) |
| `url` | string | URL-ul produsului |
| `image` | string | URL-ul imaginii produsului (medium size) |
| `stock_status` | string | Statusul stocului (ex: "În stoc (5 buc.)") |
| `features` | array | Array de string-uri cu caracteristicile produsului |
| `recommendation_count` | integer | Numărul de recomandări primite |

---

## Exemplu de Afișare în Frontend

### HTML Template

```html
<div id="top-recommendations" class="recommendations-grid">
    <!-- Recommendations will be loaded here -->
</div>
```

### JavaScript pentru Afișare

```javascript
jQuery(document).ready(function($) {
    function loadTopRecommendations() {
        $.ajax({
            url: '/wp-admin/admin-ajax.php',
            type: 'POST',
            data: {
                action: 'quiz_get_top_recommendations'
            },
            success: function(response) {
                if (response.success && response.data.recommendations.length > 0) {
                    renderRecommendations(response.data.recommendations);
                } else {
                    $('#top-recommendations').html('<p>Nu există recomandări încă.</p>');
                }
            },
            error: function() {
                $('#top-recommendations').html('<p>Eroare la încărcarea recomandărilor.</p>');
            }
        });
    }

    function renderRecommendations(recommendations) {
        let html = '<div class="recommendations-grid">';
        
        recommendations.forEach(function(product) {
            html += `
                <div class="recommendation-card">
                    <img src="${product.image}" alt="${product.name}" />
                    <h3>${product.name}</h3>
                    <div class="price">${product.price}</div>
                    <div class="stock">${product.stock_status}</div>
                    <ul class="features">
                        ${product.features.map(f => `<li>${f}</li>`).join('')}
                    </ul>
                    <div class="recommendation-badge">
                        ${product.recommendation_count} recomandări
                    </div>
                    <a href="${product.url}" class="btn">Vezi Produs</a>
                </div>
            `;
        });
        
        html += '</div>';
        $('#top-recommendations').html(html);
    }

    // Load on page load
    loadTopRecommendations();
});
```

### CSS Example

```css
.recommendations-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin: 20px 0;
}

.recommendation-card {
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 15px;
    text-align: center;
    transition: box-shadow 0.3s;
}

.recommendation-card:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.recommendation-card img {
    width: 100%;
    height: 200px;
    object-fit: cover;
    border-radius: 4px;
    margin-bottom: 10px;
}

.recommendation-badge {
    background: #0073aa;
    color: white;
    padding: 5px 10px;
    border-radius: 15px;
    display: inline-block;
    margin: 10px 0;
    font-size: 12px;
}
```

---

## Error Handling

### Verificare Completă cu Error Handling

```javascript
jQuery(document).ready(function($) {
    $.ajax({
        url: '/wp-admin/admin-ajax.php',
        type: 'POST',
        data: {
            action: 'quiz_get_top_recommendations'
        },
        timeout: 10000, // 10 seconds timeout
        success: function(response) {
            // Check if response is valid
            if (!response || typeof response !== 'object') {
                console.error('Invalid response format');
                return;
            }

            if (response.success) {
                const recommendations = response.data.recommendations;
                
                if (recommendations && recommendations.length > 0) {
                    // Process recommendations
                    console.log('Loaded', recommendations.length, 'recommendations');
                } else {
                    console.log('No recommendations available');
                }
            } else {
                // Handle API error
                const errorMsg = response.data?.message || 'Unknown error';
                console.error('API Error:', errorMsg);
            }
        },
        error: function(xhr, status, error) {
            if (status === 'timeout') {
                console.error('Request timeout');
            } else if (xhr.status === 0) {
                console.error('Network error - check connection');
            } else {
                console.error('AJAX Error:', error, 'Status:', xhr.status);
            }
        }
    });
});
```

---

## Use Cases

### 1. Afișare pe Homepage
Afișează primele 8 produse recomandate pe pagina principală pentru a atrage atenția vizitatorilor.

### 2. Widget Sidebar
Creează un widget care afișează produsele recomandate în sidebar.

### 3. Popup/Modal
Afișează recomandările într-un popup după ce utilizatorul completează quiz-ul.

### 4. Email Marketing
Folosește datele pentru a trimite emailuri cu produsele cele mai recomandate.

### 5. Analytics Dashboard
Folosește `recommendation_count` pentru a analiza care produse sunt cele mai populare.

---

## Notes

- Endpoint-ul returnează maxim 8 produse
- Produsele sunt sortate după `recommendation_count` (descrescător), apoi după `last_recommended` (descrescător)
- Dacă un produs a fost șters din WooCommerce, va fi omis din răspuns
- Prețul este returnat atât formatat (`price`) cât și numeric (`price_raw`) pentru flexibilitate
- Imaginea este returnată în mărimea "medium" - poți modifica în cod dacă ai nevoie de altă mărime

---

## Troubleshooting

### Problem: Endpoint returnează eroare 400
**Soluție:** Verifică că parametrul `action` este exact `quiz_get_top_recommendations`

### Problem: Nu returnează niciun produs
**Soluție:** 
- Verifică că există recomandări salvate în baza de date (Admin → Smartphone Quiz → Statistici Recomandări)
- Asigură-te că produsele există și sunt publicate în WooCommerce

### Problem: CORS Error
**Soluție:** Dacă faci request de pe alt domeniu, trebuie să configurezi CORS în WordPress sau să folosești un proxy

### Problem: Timeout
**Soluție:** Mărește timeout-ul sau verifică performanța bazei de date

---

## Support

Pentru probleme sau întrebări, verifică:
1. Console-ul browser-ului pentru erori JavaScript
2. Network tab pentru a vedea request-ul și răspunsul
3. Pagina de statistici din admin pentru a verifica dacă există recomandări salvate

