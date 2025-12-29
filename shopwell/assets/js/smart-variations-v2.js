/**
 * Smart Variation Selection v2.4
 * Uses capturing phase for guaranteed interception
 */
(function($) {
    "use strict";
    
    // Wait for WooCommerce to initialize first
    $(window).on("load", function() {
        var form = $(".variations_form");
        if (!form.length) return;
        
        var variations = form.data("product_variations");
        if (!variations) return;
        
        function findVariation(attrs) {
            for (var i = 0; i < variations.length; i++) {
                var v = variations[i];
                if (!v.is_in_stock) continue;
                
                var matches = true;
                for (var attr in attrs) {
                    var vVal = v.attributes[attr];
                    if (vVal !== "" && vVal !== attrs[attr]) {
                        matches = false;
                        break;
                    }
                }
                if (matches) return v;
            }
            return null;
        }
        
        function applyVariation(variation) {
            var changed = false;
            form.find("select").each(function() {
                var name = $(this).attr("name");
                var val = variation.attributes[name] || "";
                if ($(this).val() !== val) {
                    $(this).val(val);
                    changed = true;
                }
            });
            if (changed) {
                form.trigger("woocommerce_variation_select_change");
                form.trigger("check_variations");
            }
        }
        
        function formatVariationPrice(variation) {
            if (!variation) return "";
            if (variation.price_html) return variation.price_html;
            if (typeof wc_price === "function") return wc_price(variation.display_price);
            if (variation.display_price) return variation.display_price;
            return "";
        }

        function updateSwatchPillPrices() {
            form.find(".wcboost-variation-swatches__item").each(function() {
                var $item = $(this);
                var $select = $item.closest(".value").find("select");
                if (!$select.length) return;
                
                var attrName = $select.attr("name");
                var swatchValue = $item.data("value");
                
                // Build a hypothetical selection using this swatch + current other selects
                var testAttrs = {};
                form.find("select").each(function() {
                    var name = $(this).attr("name");
                    var val = $(this).val();
                    if (name === attrName) {
                        testAttrs[name] = swatchValue;
                    } else if (val) {
                        testAttrs[name] = val;
                    }
                });
                
                var variation = findVariation(testAttrs);
                var priceHtml = formatVariationPrice(variation);
                
                var $name = $item.find(".wcboost-variation-swatches__name");
                if (!$name.length) return;
                
                var $price = $item.find(".sv-pill-price");
                if (!$price.length) {
                    $price = $("<span/>", { "class": "sv-pill-price" }).appendTo($name);
                }
                $price.html(priceHtml);
            });
        }
        
        function hideDisabledSwatches() {
            form.find(".wcboost-variation-swatches__item.disabled, .wcboost-variation-swatches__item.is-invalid").hide();
            form.find(".wcboost-variation-swatches__item").not(".disabled, .is-invalid").show();
        }
        
        var hideTimer = null;
        function scheduleHideSwatches() {
            if (hideTimer) {
                clearTimeout(hideTimer);
            }
            hideTimer = setTimeout(hideDisabledSwatches, 0);
        }
        
        var priceTimer = null;
        function scheduleSwatchPriceUpdate() {
            if (priceTimer) {
                clearTimeout(priceTimer);
            }
            priceTimer = setTimeout(updateSwatchPillPrices, 0);
        }
        
        // Keep disabled/invalid swatches hidden as Woo updates availability, and keep prices in pills in sync
        hideDisabledSwatches();
        scheduleSwatchPriceUpdate();
        form.on("woocommerce_variation_has_changed check_variations woocommerce_update_variation_values found_variation reset_data", function() {
            scheduleHideSwatches();
            scheduleSwatchPriceUpdate();
        });
        
        // Observe class changes applied by WooBoost/WooCommerce on swatches and hide newly disabled ones
        var swatchGroups = form.find(".wcboost-variation-swatches");
        if (window.MutationObserver) {
            swatchGroups.each(function() {
                var target = this;
                var observer = new MutationObserver(scheduleHideSwatches);
                observer.observe(target, { attributes: true, subtree: true, attributeFilter: ["class"] });
            });
        }
        
        // Use capturing phase with native event listener
        document.addEventListener("click", function(e) {
            var swatch = $(e.target).closest(".wcboost-variation-swatches__item");
            if (!swatch.length) return;
            
            var $select = swatch.closest(".value").find("select");
            if (!$select.length) return;
            
            var clickedAttr = $select.attr("name");
            var clickedValue = swatch.data("value");
            
            e.preventDefault();
            e.stopPropagation();
            
            // Force the clicked attribute onto its select
            if ($select.val() !== clickedValue) {
                $select.val(clickedValue).trigger("change");
            }
            
            // Build what the selection would be after the click
            var testAttrs = {};
            form.find("select").each(function() {
                var name = $(this).attr("name");
                var val = $(this).val();
                if (name === clickedAttr) {
                    testAttrs[name] = clickedValue;
                } else if (val) {
                    testAttrs[name] = val;
                }
            });
            
            // 1) Perfect match with current selection (after click)
            var matchingVar = findVariation(testAttrs);
            if (matchingVar) {
                applyVariation(matchingVar);
                scheduleHideSwatches();
                scheduleSwatchPriceUpdate();
                return;
            }
            
            // 2) Try to steer to an in-stock variation that has the clicked attribute
            var singleAttr = {};
            singleAttr[clickedAttr] = clickedValue;
            matchingVar = findVariation(singleAttr);
            
            if (matchingVar) {
                applyVariation(matchingVar);
                scheduleHideSwatches();
                scheduleSwatchPriceUpdate();
                return;
            }
            
            // 3) No in-stock variation has that attribute value: keep the choice visible, let WC handle availability UI
            scheduleHideSwatches();
            scheduleSwatchPriceUpdate();
        }, true); // true = capturing phase
    });
})(jQuery);
