/**
 * Smart Variation Selection v3.0
 * Hierarchical selection system: Color → State → Memory
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
        
        // Attribute hierarchy definition
        var attributeHierarchy = {
            'attribute_pa_culoare': 1,
            'attribute_culoare': 1,
            'attribute_pa_stare': 2,
            'attribute_stare': 2,
            'attribute_pa_memorie': 3,
            'attribute_memorie': 3,
            'attribute_pa_stocare': 3,
            'attribute_stocare': 3
        };
        
        // Map simplified names to attribute names
        var simplifiedToAttribute = {
            'culoare': ['attribute_pa_culoare', 'attribute_culoare'],
            'stare': ['attribute_pa_stare', 'attribute_stare'],
            'memorie': ['attribute_pa_memorie', 'attribute_memorie', 'attribute_pa_stocare', 'attribute_stocare']
        };
        
        // Map attribute names to simplified names
        var attributeToSimplified = {
            'attribute_pa_culoare': 'culoare',
            'attribute_culoare': 'culoare',
            'attribute_pa_stare': 'stare',
            'attribute_stare': 'stare',
            'attribute_pa_memorie': 'memorie',
            'attribute_memorie': 'memorie',
            'attribute_pa_stocare': 'memorie',
            'attribute_stocare': 'memorie'
        };
        
        // ============================================
        // HIERARCHY MANAGEMENT FUNCTIONS
        // ============================================
        
        /**
         * Get attribute hierarchy mapping
         */
        function getAttributeHierarchy() {
            return attributeHierarchy;
        }
        
        /**
         * Get the level of an attribute in the hierarchy
         */
        function getAttributeLevel(attrName) {
            return attributeHierarchy[attrName] || 999; // Unknown attributes go to bottom
        }
        
        /**
         * Get all currently selected attributes
         */
        function getSelectedAttributes() {
            var selected = {};
            form.find("select[name^='attribute_']").each(function() {
                var $select = $(this);
                var name = $select.attr("name");
                var val = $select.val();
                if (val && val !== "") {
                    selected[name] = val;
                }
            });
            return selected;
        }
        
        /**
         * Get all attributes below a certain level
         */
        function getAttributesBelowLevel(level) {
            var belowAttrs = [];
            form.find("select[name^='attribute_']").each(function() {
                var name = $(this).attr("name");
                if (getAttributeLevel(name) > level) {
                    belowAttrs.push(name);
                }
            });
            return belowAttrs;
        }
        
        /**
         * Deselect all attributes below a certain level
         */
        function deselectAttributesBelowLevel(level) {
            var belowAttrs = getAttributesBelowLevel(level);
            belowAttrs.forEach(function(attrName) {
                var $select = form.find("select[name='" + attrName + "']");
                if ($select.length && $select.val() !== "") {
                    $select.val("").trigger("change");
                }
                // Also deselect swatches
                var $container = $select.closest(".value");
                if (!$container.length) {
                    $container = $select.closest("tr").find(".value");
                }
                if ($container.length) {
                    $container.find(".wcboost-variation-swatches__item.selected, .product-variation-item.selected")
                        .removeClass("selected active is-selected")
                        .attr("aria-pressed", "false");
                }
            });
        }
        
        // ============================================
        // AVAILABILITY CHECKING FUNCTIONS
        // ============================================
        
        /**
         * Check if an attribute value is available given current selections
         * For level 1 (colors), checks if ANY variation exists with that color
         * For other levels, checks if variation exists matching all higher level selections
         */
        function isAttributeValueAvailable(attrName, attrValue, selectedAttrs, attrLevel) {
            // For level 1 (colors), check if ANY variation exists with this color value
            // Independent of other selections - colors should always be available if they exist
            if (attrLevel === 1) {
                for (var i = 0; i < variations.length; i++) {
                    var v = variations[i];
                    if (!v.is_in_stock) continue;
                    
                    var vVal = v.attributes[attrName];
                    // Check if variation has this color value (case-insensitive)
                    if (vVal && (vVal === attrValue || vVal.toLowerCase() === attrValue.toLowerCase())) {
                        return true;
                    }
                }
                return false;
            }
            
            // For other levels, check if variation exists matching all higher level selections
            var testAttrs = $.extend({}, selectedAttrs);
            testAttrs[attrName] = attrValue;
            
            // Check if there's any in-stock variation matching this combination
            for (var i = 0; i < variations.length; i++) {
                var v = variations[i];
                if (!v.is_in_stock) continue;
                
                var matches = true;
                // Check all attributes in testAttrs
                for (var attr in testAttrs) {
                    var vVal = v.attributes[attr];
                    var testVal = testAttrs[attr];
                    
                    // If variation has this attribute set (not empty), check if it matches
                    // Empty value in variation means "any" - it matches all
                    if (vVal && vVal !== "") {
                        // Case-insensitive comparison
                        var vValLower = vVal.toLowerCase();
                        var testValLower = testVal ? testVal.toLowerCase() : "";
                        
                        if (vValLower !== testValLower) {
                            matches = false;
                            break;
                        }
                    }
                }
                
                if (matches) {
                    return true;
                }
            }
            
            return false;
        }
        
        /**
         * Update availability for all attribute swatches based on current selections
         * Uses isAttributeValueAvailable which checks against actual variations
         */
        function updateAttributeAvailability() {
            var selectedAttrs = getSelectedAttributes();
            
            // Process each attribute group
            form.find("select[name^='attribute_']").each(function() {
                var $select = $(this);
                var attrName = $select.attr("name");
                var attrLevel = getAttributeLevel(attrName);
                
                // Find the container for swatches
                var $container = $select.closest(".value");
                if (!$container.length) {
                    $container = $select.closest("tr").find(".value");
                }
                if (!$container.length) return;
                
                // Get higher level selections for availability check
                var higherLevelAttrs = {};
                var hasHigherLevelSelection = false;
                for (var selAttr in selectedAttrs) {
                    if (getAttributeLevel(selAttr) < attrLevel) {
                        higherLevelAttrs[selAttr] = selectedAttrs[selAttr];
                        hasHigherLevelSelection = true;
                    }
                }
                
                // Get all swatches for this attribute
                var $swatches = $container.find(".wcboost-variation-swatches__item, .product-variation-item");
                
                $swatches.each(function() {
                    var $swatch = $(this);
                    var swatchValue = $swatch.data("value") || $swatch.attr("data-value");
                    if (!swatchValue) return;
                    
                    // Check if this value should be available
                    var isAvailable = false;
                    
                    if (attrLevel === 1) {
                        // Level 1: always check availability
                        isAvailable = isAttributeValueAvailable(attrName, swatchValue, {}, 1);
                    } else if (hasHigherLevelSelection) {
                        // Other levels: need higher level selection
                        isAvailable = isAttributeValueAvailable(attrName, swatchValue, higherLevelAttrs, attrLevel);
                    }
                    // If no higher level selection, isAvailable remains false
                    
                    // Update swatch state
                    if (isAvailable) {
                        $swatch.removeClass("disabled is-invalid shopwell-disabled");
                        $swatch.attr("aria-pressed", $swatch.hasClass("selected") ? "true" : "false");
                    } else {
                        // If swatch is not available, disable it
                        $swatch.addClass("disabled is-invalid shopwell-disabled");
                        $swatch.attr("aria-pressed", "false");
                        
                        // If it was selected but is no longer available, deselect it
                        if ($swatch.hasClass("selected")) {
                            $swatch.removeClass("selected active is-selected");
                            // Also clear the select value
                            if ($select.val() === swatchValue) {
                                $select.val("").trigger("change");
                                // Clear label display
                                clearSelectedAttributeDisplay(attrName);
                            }
                        }
                    }
                });
                
                // Update prices for swatches in this group
                updateSwatchPricesForAttribute($select, attrName, attrLevel, higherLevelAttrs);
            });
        }
        
        /**
         * Update prices for swatches of a specific attribute
         * Uses ALL currently selected attributes to ensure prices sync across all swatches
         */
        function updateSwatchPricesForAttribute($select, attrName, attrLevel, higherLevelAttrs) {
            var $container = $select.closest(".value");
            if (!$container.length) {
                $container = $select.closest("tr").find(".value");
            }
            if (!$container.length) return;
            
            $container.find(".wcboost-variation-swatches__item, .product-variation-item").each(function() {
                var $swatch = $(this);
                var swatchValue = $swatch.data("value") || $swatch.attr("data-value");
                if (!swatchValue) return;
                
                var priceHtml = "";
                var isDisabled = $swatch.hasClass("disabled") || $swatch.hasClass("shopwell-disabled");
                
                // Priority 1: Build test attributes with ALL currently selected attributes + this option
                // This ensures prices sync: if Negru+Bun+256gb selected, all show same price
                var testAttrsAll = {};
                
                // Add ALL other selected attributes (not just higher level)
                form.find("select[name^='attribute_']").each(function() {
                    var $sel = $(this);
                    var selName = $sel.attr("name");
                    var selVal = $sel.val();
                    if (selName !== attrName && selVal) {
                        testAttrsAll[selName] = selVal;
                    }
                });
                
                // Add the current option we're calculating price for
                testAttrsAll[attrName] = swatchValue;
                
                // Find variation matching ALL these attributes
                var variation = findVariation(testAttrsAll);
                
                if (variation && variation.price_html) {
                    priceHtml = variation.price_html;
                }
                
                // Priority 2: If no match with all selections, try with only higher level selections + this option
                if (!priceHtml && !isDisabled) {
                    var testAttrsHigher = $.extend({}, higherLevelAttrs);
                    testAttrsHigher[attrName] = swatchValue;
                    
                    variation = findVariation(testAttrsHigher);
                    if (variation && variation.price_html) {
                        priceHtml = variation.price_html;
                    }
                }
                
                // Priority 3: Fallback - try to find ANY variation with this value
                if (!priceHtml && !isDisabled) {
                    for (var i = 0; i < variations.length; i++) {
                        var v = variations[i];
                        if (!v.is_in_stock) continue;
                        
                        var vVal = v.attributes[attrName];
                        if (vVal && vVal.toLowerCase() === swatchValue.toLowerCase() && v.price_html) {
                            priceHtml = v.price_html;
                            break;
                        }
                    }
                }
                
                if (!priceHtml && isDisabled) {
                    priceHtml = '<span class="sv-pill-price-unavailable">indisponibil</span>';
                }
                
                // Apply price to swatch
                var $name = $swatch.find(".wcboost-variation-swatches__name, .product-variation-item__name");
                if ($name.length) {
                    var $price = $swatch.find(".sv-pill-price");
                    if (!$price.length) {
                        $price = $("<span/>", { "class": "sv-pill-price" }).appendTo($name);
                    }
                    $price.html(priceHtml);
                }
            });
        }
        
        // ============================================
        // UI DISPLAY FUNCTIONS
        // ============================================
        
        /**
         * Update display of selected attribute next to label
         */
        function updateSelectedAttributeDisplay() {
            form.find("select[name^='attribute_']").each(function() {
                var $select = $(this);
                var attrName = $select.attr("name");
                var selectedValue = $select.val();
                
                // Find the label container
                var $labelContainer = $select.closest("tr").find(".label");
                if (!$labelContainer.length) {
                    $labelContainer = $select.closest(".value").siblings(".label");
                }
                if (!$labelContainer.length) return;
                
                var $label = $labelContainer.find("label");
                if (!$label.length) {
                    $label = $labelContainer;
                }
                
                // Remove ALL existing selected value displays (including duplicates)
                $label.find(".selected-attribute-value").remove();
                
                // Check if WooBoost/WooCommerce already displays the selected value
                var $existingDisplay = $label.find(".wcboost-variation-swatches__selected-label, .selected-label");
                var hasExistingDisplay = $existingDisplay.length > 0;
                
                // Get the base label text from the original HTML (before any JS modifications)
                // This is the text that was in the label when page loaded
                var $labelClone = $label.clone();
                $labelClone.find(".selected-attribute-value, .wcboost-variation-swatches__selected-label, .selected-label").remove();
                var baseLabelText = $labelClone.text().trim();
                
                // Also check current label text to see if value is already there
                var currentLabelText = $label.text().trim();
                
                // If there's a selected value, display it (only if WooBoost doesn't already display it)
                if (selectedValue && selectedValue !== "" && !hasExistingDisplay) {
                    // Get the display name from the swatch or option
                    var displayName = selectedValue;
                    
                    // Try to get display name from swatch
                    var $container = $select.closest(".value");
                    if (!$container.length) {
                        $container = $select.closest("tr").find(".value");
                    }
                    if ($container.length) {
                        var $selectedSwatch = $container.find(".wcboost-variation-swatches__item.selected, .product-variation-item.selected");
                        if ($selectedSwatch.length) {
                            var $nameEl = $selectedSwatch.find(".wcboost-variation-swatches__name, .product-variation-item__name");
                            if ($nameEl.length) {
                                // Get only the text from the name element, excluding price
                                var $nameClone = $nameEl.clone();
                                // Remove price elements
                                $nameClone.find(".sv-pill-price, .price").remove();
                                var fullText = $nameClone.text().trim();
                                
                                // Remove price patterns that might still be in text
                                displayName = fullText
                                    .replace(/\d+[\.,]\d+\s*lei/gi, '') // Remove prices like "1.171 lei"
                                    .replace(/\d+\s*lei/gi, '') // Remove prices like "171 lei"
                                    .replace(/indisponibil/gi, '') // Remove "indisponibil"
                                    .replace(/\s+/g, ' ') // Normalize whitespace
                                    .trim();
                                
                                // If after cleaning we have nothing, use the original value
                                if (!displayName) {
                                    displayName = selectedValue;
                                }
                            }
                        }
                    }
                    
                    // Try to get from select option
                    if (displayName === selectedValue) {
                        var $option = $select.find("option[value='" + selectedValue + "']");
                        if ($option.length) {
                            var optionText = $option.text().trim();
                            // Remove price information from option text
                            displayName = optionText
                                .replace(/\d+[\.,]\d+\s*lei/gi, '')
                                .replace(/\d+\s*lei/gi, '')
                                .replace(/indisponibil/gi, '')
                                .replace(/\s+/g, ' ')
                                .trim();
                            
                            if (!displayName) {
                                displayName = selectedValue;
                            }
                        }
                    }
                    
                    // Check if label already contains this value (check both base and current text)
                    // Look for patterns like "Label: Value" or "Label Value"
                    var baseHasValue = baseLabelText.indexOf(": " + displayName) !== -1 || 
                                      baseLabelText.indexOf(":" + displayName) !== -1 ||
                                      baseLabelText === displayName;
                    var currentHasValue = currentLabelText.indexOf(": " + displayName) !== -1 || 
                                         currentLabelText.indexOf(":" + displayName) !== -1;
                    
                    // Only add if not already present
                    if (!baseHasValue && !currentHasValue) {
                        var $display = $("<span/>", {
                            "class": "selected-attribute-value",
                            "text": ": " + displayName
                        });
                        $label.append($display);
                    }
                }
            });
        }
        
        /**
         * Clear selected attribute display for a specific attribute
         */
        function clearSelectedAttributeDisplay(attrName) {
            var $select = form.find("select[name='" + attrName + "']");
            if (!$select.length) return;
            
            var $labelContainer = $select.closest("tr").find(".label");
            if (!$labelContainer.length) {
                $labelContainer = $select.closest(".value").siblings(".label");
            }
            if (!$labelContainer.length) return;
            
            var $label = $labelContainer.find("label");
            if (!$label.length) {
                $label = $labelContainer;
            }
            
            $label.find(".selected-attribute-value").remove();
        }
        
        // ============================================
        // URL SYNCHRONIZATION FUNCTIONS
        // ============================================
        
        /**
         * Update URL parameters based on current selections
         */
        function updateUrlParameters() {
            if (typeof window.history === 'undefined' || typeof window.history.pushState === 'undefined') {
                return;
            }
            
            var selectedAttrs = getSelectedAttributes();
            var urlParams = new URLSearchParams(window.location.search);
            var hasChanges = false;
            
            // Update URL parameters for selected attributes
            for (var attrName in selectedAttrs) {
                var simplifiedName = attributeToSimplified[attrName];
                if (simplifiedName) {
                    var currentValue = urlParams.get(simplifiedName);
                    if (currentValue !== selectedAttrs[attrName]) {
                        urlParams.set(simplifiedName, selectedAttrs[attrName]);
                        hasChanges = true;
                    }
                }
            }
            
            // Remove parameters for unselected attributes
            urlParams.forEach(function(value, key) {
                if (key === 'culoare' || key === 'stare' || key === 'memorie') {
                    var possibleAttrs = simplifiedToAttribute[key];
                    var isSelected = false;
                    for (var i = 0; i < possibleAttrs.length; i++) {
                        if (selectedAttrs[possibleAttrs[i]] === value) {
                            isSelected = true;
                            break;
                        }
                    }
                    if (!isSelected) {
                        urlParams.delete(key);
                        hasChanges = true;
                    }
                }
            });
            
            // Update URL if there are changes
            if (hasChanges) {
                var newUrl = window.location.pathname;
                var paramString = urlParams.toString();
                if (paramString) {
                    newUrl += '?' + paramString;
                }
                if (window.location.hash) {
                    newUrl += window.location.hash;
                }
                window.history.pushState({}, '', newUrl);
            }
        }
        
        /**
         * Sync selections from URL parameters
         */
        function syncFromUrlParameters() {
            var urlParams = new URLSearchParams(window.location.search);
            var hasSelections = false;
            
            urlParams.forEach(function(value, key) {
                if (key === 'culoare' || key === 'stare' || key === 'memorie') {
                    var possibleAttrs = simplifiedToAttribute[key];
                    if (possibleAttrs) {
                        // Find which attribute name exists in the form
                        for (var i = 0; i < possibleAttrs.length; i++) {
                            var $select = form.find("select[name='" + possibleAttrs[i] + "']");
                            if ($select.length) {
                                // Check if value exists in options
                                var $option = $select.find("option[value='" + value + "']");
                                if ($option.length) {
                                    $select.val(value).trigger("change");
                                    hasSelections = true;
                                    break;
                                }
                            }
                        }
                    }
                }
            });
            
            return hasSelections;
        }
        
        // ============================================
        // CORE VARIATION FUNCTIONS
        // ============================================
        
        function findVariation(attrs) {
            for (var i = 0; i < variations.length; i++) {
                var v = variations[i];
                if (!v.is_in_stock) continue;
                
                var matches = true;
                for (var attr in attrs) {
                    var vVal = v.attributes[attr];
                    var testVal = attrs[attr];
                    
                    // Empty value in variation means "any" - it matches all
                    if (vVal && vVal !== "") {
                        // Case-insensitive comparison
                        var vValLower = vVal.toLowerCase();
                        var testValLower = testVal ? testVal.toLowerCase() : "";
                        
                        if (vValLower !== testValLower) {
                            matches = false;
                            break;
                        }
                    }
                }
                if (matches) return v;
            }
            return null;
        }
        
        function applyVariation(variation) {
            // Don't auto-select attributes - only update what user has already selected
            // This prevents auto-selection of lower level attributes when selecting a color
            var changed = false;
            var selectedAttrs = getSelectedAttributes();
            
            form.find("select").each(function() {
                var name = $(this).attr("name");
                var currentVal = $(this).val();
                var variationVal = variation.attributes[name] || "";
                
                // Only update if:
                // 1. User has already selected this attribute, OR
                // 2. This is the same value that's already selected
                // Don't auto-select new attributes
                if (selectedAttrs[name] && variationVal && variationVal !== "") {
                    // User has selected this, so we can update it to match variation
                    if (currentVal !== variationVal) {
                        $(this).val(variationVal);
                        changed = true;
                    }
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

        // Prices are now updated by updateSwatchPricesForAttribute in updateAttributeAvailability
        function updateSwatchPillPrices() {
            // This function is kept for compatibility but processing is done in updateAttributeAvailability
        }
        
        function hideDisabledSwatches() {
            // All swatches should remain visible, but we ensure color swatches are always visible
            form.find(".wcboost-variation-swatches__item.has-color-circle").show();
            form.find(".product-variation-item").show();
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
        
        // ============================================
        // CLICK HANDLER WITH HIERARCHICAL LOGIC
        // ============================================
        
        // Use capturing phase with native event listener
        document.addEventListener("click", function(e) {
            var swatch = $(e.target).closest(".wcboost-variation-swatches__item, .product-variation-item");
            if (!swatch.length) return;
            
            var $select = swatch.closest(".value").find("select");
            if (!$select.length) {
                $select = swatch.closest("tr").find("select[name^='attribute_']");
            }
            if (!$select.length) return;
            
            var clickedAttr = $select.attr("name");
            var clickedValue = swatch.data("value") || swatch.attr("data-value");
            if (!clickedValue) return;
            
            // Check if swatch is disabled
            if (swatch.hasClass("disabled") || swatch.hasClass("is-invalid") || swatch.hasClass("shopwell-disabled")) {
                e.preventDefault();
                e.stopPropagation();
                return;
            }
            
            var clickedLevel = getAttributeLevel(clickedAttr);
            
            // Prevent selecting level 3 attributes without level 2 selection
            if (clickedLevel === 3) {
                var selectedAttrs = getSelectedAttributes();
                var hasLevel2Selection = false;
                
                // Check if any level 2 attribute is selected
                for (var attrName in selectedAttrs) {
                    if (getAttributeLevel(attrName) === 2) {
                        hasLevel2Selection = true;
                        break;
                    }
                }
                
                if (!hasLevel2Selection) {
                    e.preventDefault();
                    e.stopPropagation();
                    return;
                }
            }
            
            e.preventDefault();
            e.stopPropagation();
            
            var currentValue = $select.val();
            var isCurrentlySelected = (currentValue === clickedValue) || swatch.hasClass("selected");
            
            // If clicking on already selected attribute, unselect it
            if (isCurrentlySelected) {
                $select.val("").trigger("change");
                swatch.removeClass("selected active is-selected");
                swatch.attr("aria-pressed", "false");
                
                // Deselect all attributes below this level
                deselectAttributesBelowLevel(clickedLevel);
                
                // Update availability
                updateAttributeAvailability();
                
                // Update UI display
                clearSelectedAttributeDisplay(clickedAttr);
                updateSelectedAttributeDisplay();
                
                // Update URL
                updateUrlParameters();
                
                // If color was deselected (level 1), reset product thumbnail to default
                if (clickedLevel === 1) {
                    resetProductThumbnail();
                }
                
                // Update prices
                scheduleSwatchPriceUpdate();
                return;
            }
            
            // Deselect all attributes below the clicked level
            deselectAttributesBelowLevel(clickedLevel);
            
            // Set the selected value
            $select.val(clickedValue).trigger("change");
            
            // Update swatch visual state
            var $container = $select.closest(".value");
            if (!$container.length) {
                $container = $select.closest("tr").find(".value");
            }
            if ($container.length) {
                $container.find(".wcboost-variation-swatches__item, .product-variation-item")
                    .removeClass("selected active is-selected")
                    .attr("aria-pressed", "false");
                swatch.addClass("selected active is-selected");
                swatch.attr("aria-pressed", "true");
            }
            
            // Update availability for all attributes
            updateAttributeAvailability();
            
            // Update UI display
            updateSelectedAttributeDisplay();
            
            // Update URL
            updateUrlParameters();
            
            // If color was selected (level 1), update product thumbnail
            if (clickedLevel === 1) {
                updateProductThumbnailForColor(clickedAttr, clickedValue);
            }
            
            // DO NOT auto-select other attributes - let user select them manually
            // Only update prices, don't apply full variation which would auto-select attributes
            
            // Update prices
            scheduleSwatchPriceUpdate();
        }, true); // true = capturing phase
        
        // Store original thumbnail data for reset
        var originalThumbnail = null;
        
        /**
         * Store the original thumbnail data on first load
         */
        function storeOriginalThumbnail() {
            if (originalThumbnail) return; // Already stored
            
            var $gallery = $(".woocommerce-product-gallery, .product-gallery");
            if (!$gallery.length) return;
            
            var $mainImage = $gallery.find(".woocommerce-product-gallery__image img, .product-gallery__image img, .wp-post-image").first();
            if ($mainImage.length) {
                originalThumbnail = {
                    src: $mainImage.attr("src"),
                    srcset: $mainImage.attr("srcset") || "",
                    dataSrc: $mainImage.attr("data-src") || "",
                    dataLargeImage: $mainImage.attr("data-large_image") || "",
                    href: $mainImage.closest("a").attr("href") || ""
                };
            }
        }
        
        // Store original thumbnail on load
        storeOriginalThumbnail();
        
        /**
         * Reset product thumbnail to original
         */
        function resetProductThumbnail() {
            if (!originalThumbnail) return;
            
            var $gallery = $(".woocommerce-product-gallery, .product-gallery");
            if (!$gallery.length) return;
            
            var $mainImage = $gallery.find(".woocommerce-product-gallery__image img, .product-gallery__image img, .wp-post-image").first();
            if ($mainImage.length) {
                $mainImage.attr("src", originalThumbnail.src);
                $mainImage.attr("srcset", originalThumbnail.srcset);
                $mainImage.attr("data-src", originalThumbnail.dataSrc);
                $mainImage.attr("data-large_image", originalThumbnail.dataLargeImage);
                
                var $link = $mainImage.closest("a");
                if ($link.length && originalThumbnail.href) {
                    $link.attr("href", originalThumbnail.href);
                }
            }
            
            // Trigger gallery update event
            $gallery.trigger("woocommerce_gallery_reset_slide_position");
        }
        
        /**
         * Update product thumbnail when a color is selected
         * Finds any variation with that color and uses its thumbnail
         */
        function updateProductThumbnailForColor(attrName, colorValue) {
            if (!colorValue) return;
            
            // Store original thumbnail if not already stored
            storeOriginalThumbnail();
            
            // Find any variation with this color
            var variation = null;
            for (var i = 0; i < variations.length; i++) {
                var v = variations[i];
                var vVal = v.attributes[attrName];
                
                // Case-insensitive comparison
                if (vVal && vVal.toLowerCase() === colorValue.toLowerCase()) {
                    variation = v;
                    break;
                }
            }
            
            if (!variation || !variation.image) return;
            
            var image = variation.image;
            
            // Update main product image
            var $gallery = $(".woocommerce-product-gallery, .product-gallery");
            if (!$gallery.length) return;
            
            // Update main image
            var $mainImage = $gallery.find(".woocommerce-product-gallery__image img, .product-gallery__image img, .wp-post-image").first();
            if ($mainImage.length && image.full_src) {
                $mainImage.attr("src", image.full_src);
                $mainImage.attr("srcset", image.srcset || "");
                $mainImage.attr("data-src", image.full_src);
                $mainImage.attr("data-large_image", image.full_src);
                
                // Update parent link if exists
                var $link = $mainImage.closest("a");
                if ($link.length) {
                    $link.attr("href", image.full_src);
                }
            }
            
            // Update thumbnail in gallery if exists
            var $thumbnail = $gallery.find(".flex-control-thumbs img, .woocommerce-product-gallery__trigger, .gallery-thumbnail img").first();
            if ($thumbnail.length && image.gallery_thumbnail_src) {
                $thumbnail.attr("src", image.gallery_thumbnail_src);
            }
            
            // Update Shopwell specific gallery elements
            var $shopwellMain = $gallery.find(".shopwell-product-gallery__image img, .product-gallery-image img").first();
            if ($shopwellMain.length && image.full_src) {
                $shopwellMain.attr("src", image.full_src);
                $shopwellMain.attr("srcset", image.srcset || "");
            }
            
            // Trigger gallery update event for plugins that listen
            $gallery.trigger("woocommerce_gallery_reset_slide_position");
            $(document.body).trigger("wc-product-gallery-after-init", [$gallery]);
        }
        
        // ============================================
        // INITIALIZATION
        // ============================================
        
        // Backend has already processed URL parameters and set initial state
        // We only need to handle interactions from here on
        
        // Update availability based on current state (backend may have set some values)
        updateAttributeAvailability();
        
        // Update UI display (backend may have set some values)
        updateSelectedAttributeDisplay();
        
        // Update URL if needed (sync with backend state)
        updateUrlParameters();
        
        // Keep prices updated
        scheduleSwatchPriceUpdate();
        
        // Update thumbnail if a color is already selected (from URL)
        var selectedAttrs = getSelectedAttributes();
        for (var attrName in selectedAttrs) {
            if (getAttributeLevel(attrName) === 1) {
                // This is a color attribute, update thumbnail
                updateProductThumbnailForColor(attrName, selectedAttrs[attrName]);
                break;
            }
        }
        
        // Listen to WooCommerce events
        // Prevent auto-selection by intercepting variation changes
        form.on("woocommerce_variation_has_changed check_variations woocommerce_update_variation_values found_variation reset_data", function(e) {
            // Don't let WooCommerce auto-select attributes - only update what user selected
            var selectedAttrs = getSelectedAttributes();
            
            // Restore user selections if WooCommerce tried to change them
            setTimeout(function() {
                var currentAttrs = getSelectedAttributes();
                var needsRestore = false;
                
                for (var attr in selectedAttrs) {
                    if (!currentAttrs[attr] || currentAttrs[attr] !== selectedAttrs[attr]) {
                        // WooCommerce changed this, restore it if user had it selected
                        var $select = form.find("select[name='" + attr + "']");
                        if ($select.length && $select.val() !== selectedAttrs[attr]) {
                            $select.val(selectedAttrs[attr]);
                            needsRestore = true;
                        }
                    }
                }
                
                if (needsRestore) {
                    // Restore visual state
                    form.find("select[name^='attribute_']").each(function() {
                        var $select = $(this);
                        var val = $select.val();
                        if (val) {
                            var $container = $select.closest(".value");
                            if (!$container.length) {
                                $container = $select.closest("tr").find(".value");
                            }
                            if ($container.length) {
                                $container.find(".wcboost-variation-swatches__item, .product-variation-item")
                                    .removeClass("selected active is-selected")
                                    .attr("aria-pressed", "false");
                                $container.find(".wcboost-variation-swatches__item[data-value='" + val + "'], .product-variation-item[data-value='" + val + "']")
                                    .addClass("selected active is-selected")
                                    .attr("aria-pressed", "true");
                            }
                        }
                    });
                }
            }, 10);
            
            updateAttributeAvailability();
            updateSelectedAttributeDisplay();
            updateUrlParameters();
            scheduleHideSwatches();
            scheduleSwatchPriceUpdate();
        });
        
        // Also listen to select changes
        form.on("change", "select[name^='attribute_']", function() {
            var $select = $(this);
            var attrName = $select.attr("name");
            var attrLevel = getAttributeLevel(attrName);
            var selectedValue = $select.val();
            
            // If a value is selected, verify it's still available
            if (selectedValue && selectedValue !== "") {
                var selectedAttrs = getSelectedAttributes();
                
                // Get higher level selections
                var higherLevelAttrs = {};
                for (var selAttr in selectedAttrs) {
                    if (getAttributeLevel(selAttr) < attrLevel) {
                        higherLevelAttrs[selAttr] = selectedAttrs[selAttr];
                    }
                }
                
                // Check if selected value is still available
                var isAvailable = isAttributeValueAvailable(attrName, selectedValue, higherLevelAttrs, attrLevel);
                
                if (!isAvailable) {
                    // Value is no longer available, deselect it
                    $select.val("").trigger("change");
                    
                    // Also deselect the swatch
                    var $container = $select.closest(".value");
                    if (!$container.length) {
                        $container = $select.closest("tr").find(".value");
                    }
                    if ($container.length) {
                        $container.find(".wcboost-variation-swatches__item.selected, .product-variation-item.selected")
                            .removeClass("selected active is-selected")
                            .attr("aria-pressed", "false");
                    }
                    
                    // Clear label display
                    clearSelectedAttributeDisplay(attrName);
                    
                    // Deselect attributes below this level
                    deselectAttributesBelowLevel(attrLevel);
                    
                    // Update availability and UI
                    updateAttributeAvailability();
                    updateSelectedAttributeDisplay();
                    updateUrlParameters();
                    return;
                }
            }
            
            // Deselect attributes below this level
            deselectAttributesBelowLevel(attrLevel);
            
            // Update availability
            updateAttributeAvailability();
            
            // Update UI display
            updateSelectedAttributeDisplay();
            
            // Update URL
            updateUrlParameters();
        });
        
        // Observe class changes applied by WooBoost/WooCommerce on swatches
        var swatchGroups = form.find(".wcboost-variation-swatches");
        if (window.MutationObserver) {
            swatchGroups.each(function() {
                var target = this;
                var observer = new MutationObserver(function() {
                    updateAttributeAvailability();
                    updateSelectedAttributeDisplay();
                });
                observer.observe(target, { attributes: true, subtree: true, attributeFilter: ["class"] });
            });
        }
    });
})(jQuery);
