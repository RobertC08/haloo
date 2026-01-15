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
                    // If variation has this attribute set and it doesn't match, skip
                    if (vVal !== "" && vVal !== testAttrs[attr]) {
                        matches = false;
                        break;
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
                
                // Get all swatches for this attribute
                var $swatches = $container.find(".wcboost-variation-swatches__item, .product-variation-item");
                
                $swatches.each(function() {
                    var $swatch = $(this);
                    var swatchValue = $swatch.data("value") || $swatch.attr("data-value");
                    if (!swatchValue) return;
                    
                    // Check if this value should be available
                    var isAvailable = false;
                    
                    // For level 1 (color), check if it has any in-stock variation
                    // Colors should always be available if they exist, independent of other selections
                    if (attrLevel === 1) {
                        isAvailable = isAttributeValueAvailable(attrName, swatchValue, {}, 1);
                    } else {
                        // For other levels, check availability based on selected attributes from higher levels
                        var higherLevelAttrs = {};
                        var hasHigherLevelSelection = false;
                        for (var selAttr in selectedAttrs) {
                            if (getAttributeLevel(selAttr) < attrLevel) {
                                higherLevelAttrs[selAttr] = selectedAttrs[selAttr];
                                hasHigherLevelSelection = true;
                            }
                        }
                        
                        // Level 2 and 3 require selections from higher levels to be enabled
                        if (hasHigherLevelSelection) {
                            isAvailable = isAttributeValueAvailable(attrName, swatchValue, higherLevelAttrs, attrLevel);
                        } else {
                            // No higher level selection, so this should be disabled
                            isAvailable = false;
                        }
                    }
                    
                    // Update swatch state
                    if (isAvailable) {
                        $swatch.removeClass("disabled is-invalid shopwell-disabled");
                        $swatch.attr("aria-pressed", $swatch.hasClass("selected") ? "true" : "false");
                    } else {
                        // Don't disable if it's already selected (user might be changing)
                        if (!$swatch.hasClass("selected")) {
                            $swatch.addClass("disabled is-invalid shopwell-disabled");
                            $swatch.attr("aria-pressed", "false");
                            $swatch.removeClass("selected active is-selected");
                        }
                    }
                });
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

        function updateSwatchPillPrices() {
            // Update wcboost swatches
            form.find(".wcboost-variation-swatches__item").each(function() {
                var $item = $(this);
                var $select = $item.closest(".value").find("select");
                if (!$select.length) return;
                
                var attrName = $select.attr("name");
                var attrLevel = getAttributeLevel(attrName);
                var swatchValue = $item.data("value");
                
                var variation = null;
                var priceHtml = "";
                
                // For level 1 (colors), find ANY variation with this color, regardless of other selections
                if (attrLevel === 1) {
                    // Find first available variation with this color
                    for (var i = 0; i < variations.length; i++) {
                        var v = variations[i];
                        if (!v.is_in_stock) continue;
                        
                        var vVal = v.attributes[attrName];
                        if (vVal && (vVal === swatchValue || vVal.toLowerCase() === swatchValue.toLowerCase())) {
                            variation = v;
                            break;
                        }
                    }
                } else {
                    // For other levels, build test attributes with current selections from higher levels
                    var testAttrs = {};
                    form.find("select").each(function() {
                        var name = $(this).attr("name");
                        var val = $(this).val();
                        var level = getAttributeLevel(name);
                        // Only include selections from same or higher levels
                        if (name === attrName) {
                            testAttrs[name] = swatchValue;
                        } else if (val && level < attrLevel) {
                            testAttrs[name] = val;
                        }
                    });
                    variation = findVariation(testAttrs);
                }
                
                priceHtml = formatVariationPrice(variation);
                
                // Only show "indisponibil" if swatch is actually disabled
                // If swatch is enabled, it should show a price (even if it's the first available variation)
                var isDisabled = $item.hasClass("disabled") || $item.hasClass("is-invalid") || $item.hasClass("shopwell-disabled");
                if ((!priceHtml || priceHtml.trim() === "") && isDisabled) {
                    priceHtml = '<span class="sv-pill-price-unavailable">indisponibil</span>';
                } else if (!priceHtml || priceHtml.trim() === "") {
                    // If enabled but no price found, try to find any variation with this value
                    if (attrLevel === 1) {
                        // Already tried above, so show "indisponibil" only if disabled
                        if (isDisabled) {
                            priceHtml = '<span class="sv-pill-price-unavailable">indisponibil</span>';
                        }
                    } else {
                        priceHtml = '<span class="sv-pill-price-unavailable">indisponibil</span>';
                    }
                }
                
                var $name = $item.find(".wcboost-variation-swatches__name");
                if (!$name.length) return;
                
                var $price = $item.find(".sv-pill-price");
                if (!$price.length) {
                    $price = $("<span/>", { "class": "sv-pill-price" }).appendTo($name);
                }
                $price.html(priceHtml);
            });
            
            // Update product-variation-item swatches
            form.find(".product-variation-item").each(function() {
                var $item = $(this);
                var $select = $item.closest(".value").find("select");
                if (!$select.length) {
                    $select = $item.closest("tr").find("select[name^='attribute_']");
                }
                if (!$select.length) return;
                
                var attrName = $select.attr("name");
                var attrLevel = getAttributeLevel(attrName);
                var swatchValue = $item.data("value") || $item.attr("data-value");
                if (!swatchValue) return;
                
                var variation = null;
                var priceHtml = "";
                
                // For level 1 (colors), find ANY variation with this color, regardless of other selections
                if (attrLevel === 1) {
                    // Find first available variation with this color
                    for (var i = 0; i < variations.length; i++) {
                        var v = variations[i];
                        if (!v.is_in_stock) continue;
                        
                        var vVal = v.attributes[attrName];
                        if (vVal && (vVal === swatchValue || vVal.toLowerCase() === swatchValue.toLowerCase())) {
                            variation = v;
                            break;
                        }
                    }
                } else {
                    // For other levels, build test attributes with current selections from higher levels
                    var testAttrs = {};
                    form.find("select").each(function() {
                        var name = $(this).attr("name");
                        var val = $(this).val();
                        var level = getAttributeLevel(name);
                        // Only include selections from same or higher levels
                        if (name === attrName) {
                            testAttrs[name] = swatchValue;
                        } else if (val && level < attrLevel) {
                            testAttrs[name] = val;
                        }
                    });
                    variation = findVariation(testAttrs);
                }
                
                priceHtml = formatVariationPrice(variation);
                
                // Only show "indisponibil" if swatch is actually disabled
                var isDisabled = $item.hasClass("disabled") || $item.hasClass("is-invalid") || $item.hasClass("shopwell-disabled");
                if ((!priceHtml || priceHtml.trim() === "") && isDisabled) {
                    priceHtml = '<span class="sv-pill-price-unavailable">indisponibil</span>';
                } else if (!priceHtml || priceHtml.trim() === "") {
                    // If enabled but no price found, try to find any variation with this value
                    if (attrLevel === 1) {
                        // Already tried above, so show "indisponibil" only if disabled
                        if (isDisabled) {
                            priceHtml = '<span class="sv-pill-price-unavailable">indisponibil</span>';
                        }
                    } else {
                        priceHtml = '<span class="sv-pill-price-unavailable">indisponibil</span>';
                    }
                }
                
                // Find or create price element
                var $price = $item.find(".product-variation-item__price, .sv-pill-price, .price");
                if (!$price.length) {
                    // Try to find where price should be (usually after name or in a specific container)
                    var $name = $item.find(".product-variation-item__name, .wcboost-variation-swatches__name");
                    if ($name.length) {
                        $price = $("<span/>", { "class": "sv-pill-price" }).appendTo($name);
                    } else {
                        // Append to item itself
                        $price = $("<span/>", { "class": "sv-pill-price" }).appendTo($item);
                    }
                }
                $price.html(priceHtml);
            });
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
            
            // DO NOT auto-select other attributes - let user select them manually
            // Only update prices, don't apply full variation which would auto-select attributes
            
            // Update prices
            scheduleSwatchPriceUpdate();
        }, true); // true = capturing phase
        
        // ============================================
        // INITIALIZATION
        // ============================================
        
        // Sync from URL parameters first
        var urlSynced = syncFromUrlParameters();
        
        // Initialize availability: all colors enabled, others disabled
        if (!urlSynced) {
            form.find("select[name^='attribute_']").each(function() {
                var $select = $(this);
                var attrName = $select.attr("name");
                var attrLevel = getAttributeLevel(attrName);
                
                // Level 1 (color) should be enabled, others disabled initially
                if (attrLevel > 1) {
                    $select.val("").trigger("change");
                }
            });
        }
        
        // Update availability based on current state
        updateAttributeAvailability();
        
        // Update UI display
        updateSelectedAttributeDisplay();
        
        // Update URL if needed
        updateUrlParameters();
        
        // Keep prices updated
        scheduleSwatchPriceUpdate();
        
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
