function displayRecommendations(data) {
    const { recommended, alternatives, share_id, share_url } = data;
    
    const shareText = encodeURIComponent("Am gasit telefonul perfect pe Haloo: " + recommended.name);
    const shareUrlEncoded = encodeURIComponent(share_url || window.location.href);
    const whatsappUrl = "https://wa.me/?text=" + shareText + " " + shareUrlEncoded;
    const facebookUrl = "https://www.facebook.com/sharer/sharer.php?u=" + shareUrlEncoded;
    const emailUrl = "mailto:?subject=" + encodeURIComponent("Recomandare telefon Haloo") + "&body=" + shareText + "%0A%0AVezi: " + shareUrlEncoded;
    
    const recommendedContainer = document.getElementById("recommendedPhone");
    recommendedContainer.style.animation = "fadeUp 0.6s ease-out";
    
    let html = "<style>";
    html += ".qr-card{background:#fff;border:1px solid #e0e0e0;border-radius:12px;overflow:hidden}";
    html += ".qr-product{display:flex;gap:20px;padding:24px;align-items:center}";
    html += ".qr-product-img{flex-shrink:0;width:100px;height:100px;background:#f5f5f5;border-radius:8px;display:flex;align-items:center;justify-content:center}";
    html += ".qr-product-img img{max-width:90%;max-height:90%}";
    html += ".qr-product-info{flex:1;text-align:left}";
    html += ".qr-product-name{font-size:1.1rem;font-weight:600;color:#1a1a1a;margin:0 0 8px 0}";
    html += ".qr-product-price{font-size:1.2rem;color:#16a34a;font-weight:600;margin-bottom:8px}";
    html += ".qr-product-stock{font-size:0.8rem;color:#666;margin-bottom:12px}";
    html += ".qr-btn{display:inline-block;background:#16a34a;color:#fff;padding:10px 24px;border-radius:6px;text-decoration:none;font-weight:500;font-size:0.9rem}";
    html += ".qr-btn:hover{background:#15803d;color:#fff}";
    html += ".qr-share{display:flex;gap:8px;padding:16px 24px;background:#f9fafb;border-top:1px solid #e0e0e0;align-items:center;flex-wrap:wrap}";
    html += ".qr-share-label{font-size:0.8rem;color:#666}";
    html += ".qr-share-btn{padding:8px 14px;border-radius:6px;text-decoration:none;font-size:0.8rem;font-weight:500;border:none;cursor:pointer}";
    html += ".qr-share-wa{background:#25D366;color:#fff}";
    html += ".qr-share-fb{background:#1877F2;color:#fff}";
    html += ".qr-share-em{background:#64748b;color:#fff}";
    html += ".qr-share-cp{background:#e5e7eb;color:#374151}";
    html += "@media(max-width:600px){.qr-product{flex-direction:column;text-align:center}.qr-product-info{text-align:center}}";
    html += "</style>";
    html += "<div class=qr-card>";
    html += "<div class=qr-product>";
    html += "<div class=qr-product-img>" + recommended.image + "</div>";
    html += "<div class=qr-product-info>";
    html += "<h2 class=qr-product-name>" + recommended.name + "</h2>";
    html += "<div class=qr-product-price>" + recommended.price + "</div>";
    html += "<div class=qr-product-stock>✓ " + recommended.stock_status + " · Livrare 1-2 zile</div>";
    html += "<a href=" + recommended.url + " class=qr-btn>Vezi produsul →</a>";
    html += "</div></div>";
    html += "<div class=qr-share>";
    html += "<span class=qr-share-label>Distribuie:</span>";
    html += "<a href=" + whatsappUrl + " target=_blank class=\"qr-share-btn qr-share-wa\">WhatsApp</a>";
    html += "<a href=" + facebookUrl + " target=_blank class=\"qr-share-btn qr-share-fb\">Facebook</a>";
    html += "<a href=" + emailUrl + " class=\"qr-share-btn qr-share-em\">Email</a>";
    html += "<button class=\"qr-share-btn qr-share-cp\" onclick=\"navigator.clipboard.writeText(\x27" + (share_url||window.location.href) + "\x27);this.textContent=\x27Copiat!\x27\">Copiaza link</button>";
    html += "</div></div>";
    
    recommendedContainer.innerHTML = html;
    
    let altHtml = "<div style=\"display:grid;grid-template-columns:repeat(3,1fr);gap:12px\">";
    alternatives.forEach(function(phone) {
        altHtml += "<a href=" + phone.url + " style=\"background:#fff;border:1px solid #e0e0e0;border-radius:8px;padding:16px;text-align:center;text-decoration:none;display:block\">";
        altHtml += "<div style=\"font-size:0.85rem;font-weight:500;color:#1a1a1a;margin-bottom:6px;line-height:1.3\">" + phone.name + "</div>";
        altHtml += "<div style=\"font-size:0.9rem;color:#16a34a;font-weight:600;margin-bottom:8px\">" + phone.price + "</div>";
        altHtml += "<span style=\"font-size:0.8rem;color:#16a34a\">Vezi detalii</span></a>";
    });
    altHtml += "</div>";
    document.getElementById("alternativePhones").innerHTML = altHtml;
}
