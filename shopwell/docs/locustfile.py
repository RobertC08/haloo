"""
Load Testing Script pentru Shopwell Theme
Tool: Locust (https://locust.io)

Instalare:
    pip install locust

Utilizare:
    1. Editează HOST cu domeniul tău
    2. Rulează: locust -f locustfile.py --host=https://haloo.ro
    3. Deschide browser-ul la http://localhost:8089
    4. Configurează numărul de utilizatori și rate-ul de spawn
    5. Click "Start swarming"

Sau rulează fără UI:
    locust -f locustfile.py --host=https://domeniul-tau.com --headless -u 50 -r 5 -t 2m
"""

from locust import HttpUser, task, between
import random

# CONFIGURARE - Editează aici
HOST = "https://haloo.ro"

# Lista de termeni de căutare pentru testare
SEARCH_TERMS = [
    "apple",
    "Samsung Galaxy",
    "Xiaomi",
    "Apple 11 Pro",
    "Htc",
    "oferta",
]

# Lista de URL-uri de produse (editează cu URL-uri reale)
PRODUCT_URLS = [
    "/produs/apple-iphone-11-pro/?culoare=argintiu&stare=excelent&memorie=256gb",
    "/produs/samsung-galaxy-xcover-5-g525f-ds/?culoare=negru&stare=ca-nou&memorie=64gb",
    "/produs/samsung-galaxy-a50-a505fn-ds/?culoare=alb&stare=excelent&memorie=128gb",
]

# Lista de categorii (editează cu URL-uri reale)
CATEGORY_URLS = [
    "/shop/?product_cat=samsung",
    "/shop/?product_cat=xiaomi",
]


class ShopwellUser(HttpUser):
    """
    Simulează comportamentul unui utilizator real pe site-ul Shopwell.
    """
    
    # Timpul de așteptare între request-uri (1-3 secunde)
    wait_time = between(1, 3)
    
    def on_start(self):
        """
        Executat la începutul fiecărui utilizator virtual.
        Simulează un utilizator care intră pe site.
        """
        self.client.get("/", name="Homepage")
    
    @task(5)
    def view_homepage(self):
        """
        Vizitează homepage-ul.
        Prioritate: 5 (cel mai frecvent)
        """
        response = self.client.get("/", name="Homepage")
        self._check_response(response, "Homepage")
    
    @task(3)
    def search_products(self):
        """
        Caută produse.
        Prioritate: 3
        """
        search_term = random.choice(SEARCH_TERMS)
        response = self.client.get(
            f"/?s={search_term}",
            name="Search Products"
        )
        self._check_response(response, "Search")
    
    @task(2)
    def view_product(self):
        """
        Vizitează pagina unui produs.
        Prioritate: 2
        """
        if PRODUCT_URLS:
            product_url = random.choice(PRODUCT_URLS)
            response = self.client.get(product_url, name="Product Page")
            self._check_response(response, "Product")
    
    @task(2)
    def view_category(self):
        """
        Vizitează o categorie de produse.
        Prioritate: 2
        """
        if CATEGORY_URLS:
            category_url = random.choice(CATEGORY_URLS)
            response = self.client.get(category_url, name="Category Page")
            self._check_response(response, "Category")
    
    @task(1)
    def view_blog(self):
        """
        Vizitează pagina de blog.
        Prioritate: 1
        """
        response = self.client.get("/blog/", name="Blog")
        self._check_response(response, "Blog")
    
    @task(1)
    def ajax_search(self):
        """
        Testează căutarea AJAX (dacă există).
        Prioritate: 1
        """
        search_term = random.choice(SEARCH_TERMS)
        response = self.client.post(
            "/wp-admin/admin-ajax.php",
            data={
                "action": "shopwell_search",
                "search_term": search_term,
            },
            name="AJAX Search"
        )
        self._check_response(response, "AJAX Search")
    
    def _check_response(self, response, page_name):
        """
        Helper pentru verificarea răspunsurilor.
        """
        if response.status_code != 200:
            print(f"⚠️  Eroare la {page_name}: Status {response.status_code}")
        
        # Verifică timpul de răspuns
        if response.elapsed.total_seconds() > 5:
            print(f"⚠️  {page_name} este lent: {response.elapsed.total_seconds():.2f}s")


class QuickTestUser(HttpUser):
    """
    Variantă simplificată pentru teste rapide.
    Doar homepage și search.
    """
    wait_time = between(1, 2)
    
    @task(3)
    def view_homepage(self):
        self.client.get("/", name="Homepage")
    
    @task(1)
    def search(self):
        search_term = random.choice(SEARCH_TERMS)
        self.client.get(f"/?s={search_term}", name="Search")

