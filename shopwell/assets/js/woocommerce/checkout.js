/**
 * Checkout SKU Availability Check
 * Verifies product availability via Market API before order submission
 *
 * @package Shopwell
 */

(function($) {
    'use strict';

    // Ensure shopwellLog is available (fallback if not defined in functions.php)
    // This ensures logging works even if checkout.js loads before the inline script in functions.php
    if (typeof window.shopwellLog === 'undefined') {
        window.shopwellLog = function(message, data) {
            // Only log if WP_DEBUG is enabled (check via ajaxurl availability)
            if (typeof ajaxurl === 'undefined') {
                return; // Don't log if ajaxurl is not available
            }
            
            // Try to get nonce from various sources
            var nonce = '';
            if (typeof shopwellCheckoutSku !== 'undefined' && shopwellCheckoutSku.logNonce) {
                nonce = shopwellCheckoutSku.logNonce;
            } else if (typeof shopwellData !== 'undefined' && shopwellData.logNonce) {
                nonce = shopwellData.logNonce;
            } else if (typeof wp !== 'undefined' && wp.ajax && wp.ajax.settings && wp.ajax.settings.nonce) {
                nonce = wp.ajax.settings.nonce;
            }
            
            // If no nonce found, try to get it via AJAX or skip logging
            if (!nonce) {
                // Silently skip if no nonce available
                return;
            }
            
            $.post(ajaxurl, {
                action: 'shopwell_log_message',
                nonce: nonce,
                message: message,
                data: data ? JSON.stringify(data) : null
            }).fail(function() {
                // Silently fail - don't break functionality if logging fails
            });
        };
    }

    $(document).ready(function() {
        const checkoutForm = $('form.checkout');
        
        if (!checkoutForm.length || typeof shopwellCheckoutSku === 'undefined') {
            return;
        }

        // Ensure all required fields have HTML5 required attribute for browser validation
        // WooCommerce uses aria-required="true" for accessibility, we need to add HTML5 required too
        function addRequiredAttribute() {
            // Check fields with aria-required="true" (WooCommerce standard)
            checkoutForm.find('input[aria-required="true"], select[aria-required="true"], textarea[aria-required="true"]').each(function() {
                const $field = $(this);
                if (!$field.attr('required')) {
                    $field.attr('required', 'required');
                }
            });
            
            // Check fields with validate-required class
            checkoutForm.find('.validate-required input, .validate-required select, .validate-required textarea').each(function() {
                const $field = $(this);
                if (!$field.attr('required')) {
                    $field.attr('required', 'required');
                }
            });
            
            // Check fields in required form-rows
            checkoutForm.find('.form-row.validate-required input, .form-row.validate-required select, .form-row.validate-required textarea').each(function() {
                const $field = $(this);
                if (!$field.attr('required')) {
                    $field.attr('required', 'required');
                }
            });
        }
        
        // Add required attributes on page load
        addRequiredAttribute();
        
        // Also add required attributes when checkout updates (AJAX updates)
        $(document.body).on('updated_checkout', function() {
            addRequiredAttribute();
        });

        // Store original submit handler
        let isCheckingAvailability = false;
        let checkoutSubmitBlocked = false;
        let validationPassed = false; // Flag to indicate our validation passed
        let stockCheckCompleted = false; // Flag to track if stock check was completed successfully
        let stockCheckTimestamp = 0; // Timestamp of last successful stock check

        /**
         * Get all SKUs from cart items
         * Uses pre-loaded cart SKUs from localized script or falls back to AJAX
         */
        function getCartSkus() {
            // First, try to use pre-loaded SKUs from localized script
            if (shopwellCheckoutSku.cartSkus && Array.isArray(shopwellCheckoutSku.cartSkus) && shopwellCheckoutSku.cartSkus.length > 0) {
                return shopwellCheckoutSku.cartSkus.map(function(item) {
                    return {
                        sku: item.sku,
                        quantity: item.quantity,
                        productName: item.product_name || 'Produs'
                    };
                });
            }

            // Fallback: try to get from DOM
            const skus = [];
            $('.woocommerce-checkout-review-order-table tbody tr.cart_item').each(function() {
                const $row = $(this);
                const productName = $row.find('.product-name').text().trim();
                const quantityText = $row.find('.product-quantity').text().trim();
                const quantity = parseInt(quantityText) || 1;

                // Try to get SKU from data attributes
                let sku = $row.data('sku') || $row.find('[data-sku]').first().data('sku');

                if (sku) {
                    skus.push({
                        sku: sku,
                        quantity: quantity,
                        productName: productName || 'Produs'
                    });
                }
            });

            return skus;
        }

        /**
         * Get SKUs from WooCommerce checkout data via AJAX
         */
        function getCartSkusFromWooCommerce() {
            return new Promise(function(resolve, reject) {
                $.ajax({
                    url: shopwellCheckoutSku.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'get_checkout_cart_skus',
                        nonce: shopwellCheckoutSku.nonce
                    },
                    success: function(response) {
                        if (response.success && response.data && Array.isArray(response.data.skus)) {
                            const skus = response.data.skus.map(function(item) {
                                return {
                                    sku: item.sku,
                                    quantity: item.quantity,
                                    productName: item.product_name || 'Produs'
                                };
                            });
                            resolve(skus);
                        } else {
                            reject('Could not retrieve cart SKUs');
                        }
                    },
                    error: function() {
                        reject('Error retrieving cart SKUs');
                    }
                });
            });
        }

        /**
         * Check single SKU availability
         */
        function checkSkuAvailability(sku) {
            return new Promise(function(resolve, reject) {
                $.ajax({
                    url: shopwellCheckoutSku.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'check_sku_availability',
                        nonce: shopwellCheckoutSku.nonce,
                        sku: sku
                    },
                    success: function(response) {
                        // Check if error is about missing configuration first (before logging)
                        if (!response.success) {
                            const errorMessage = response.data?.message || '';
                            if (errorMessage.includes('Market API configuration missing') || 
                                errorMessage.includes('configuration missing')) {
                                // Silently skip - don't log or show error
                                // Resolve with a default response to allow checkout
                                resolve({ quantity: 999, price: 0 });
                                return;
                            }
                        }
                        
                        // Log to console for debugging (only for non-configuration errors)
                        shopwellLog('=== Market API Check SKU Availability ===');
                        shopwellLog('SKU:', sku);
                        shopwellLog('Full Response:', response);
                        
                        if (response.data && response.data.debug_info) {
                            shopwellLog('--- Request Details ---');
                            shopwellLog('URL:', response.data.debug_info.request_url);
                            shopwellLog('Headers:', response.data.debug_info.request_headers);
                            shopwellLog('Method:', response.data.debug_info.request_method);
                            shopwellLog('--- Response Details ---');
                            shopwellLog('Response Code:', response.data.debug_info.response_code);
                            shopwellLog('Response Body:', response.data.debug_info.response_body);
                        }
                        
                        if (response.success) {
                            shopwellLog('✅ Success - Availability:', response.data);
                            resolve(response.data);
                        } else {
                            shopwellLog('❌ Error:', response.data);
                            reject(response.data || { message: 'Unknown error' });
                        }
                        shopwellLog('========================================');
                    },
                    error: function(xhr, status, error) {
                        shopwellLog('=== Market API AJAX Error ===');
                        shopwellLog('SKU:', sku);
                        shopwellLog('Status:', status);
                        shopwellLog('Error:', error);
                        shopwellLog('XHR:', xhr);
                        shopwellLog('======================================');
                        reject({ message: error || 'Network error' });
                    }
                });
            });
        }

        /**
         * Check all SKUs availability
         */
        async function checkAllSkusAvailability(cartSkus) {
            const results = [];
            const errors = [];

            // Show loading state
            const $placeOrderBtn = $('button[name="woocommerce_checkout_place_order"]');
            const originalBtnText = $placeOrderBtn.text();
            $placeOrderBtn.prop('disabled', true).text(shopwellCheckoutSku.checkingText);

            try {
                // Check each SKU
                for (const item of cartSkus) {
                    try {
                        const availability = await checkSkuAvailability(item.sku);
                        results.push({
                            ...item,
                            available: availability.quantity,
                            price: availability.price
                        });

                        // Validate quantity response - STRICT CHECK for stock availability
                        // Ensure we have a valid quantity value
                        let availableQty = 0;
                        if (availability.quantity !== null && availability.quantity !== undefined) {
                            availableQty = parseInt(availability.quantity);
                            // If parsing fails or results in NaN, treat as 0 (out of stock)
                            if (isNaN(availableQty) || availableQty < 0) {
                                availableQty = 0;
                            }
                        }
                        const requestedQty = parseInt(item.quantity) || 1;
                        
                        // STRICT VALIDATION: Block order if product is out of stock (quantity = 0)
                        if (availableQty === 0) {
                            errors.push({
                                sku: item.sku,
                                productName: item.productName,
                                requested: requestedQty,
                                available: 0,
                                type: 'unavailable'
                            });
                            shopwellLog('Product out of stock - blocking order:', {
                                sku: item.sku,
                                productName: item.productName,
                                requested: requestedQty
                            });
                        } else if (availableQty < requestedQty) {
                            errors.push({
                                sku: item.sku,
                                productName: item.productName,
                                requested: requestedQty,
                                available: availableQty,
                                type: 'insufficient'
                            });
                        }
                    } catch (error) {
                        // Ignore configuration errors and generic "not found" errors - allow checkout to proceed
                        const errorMessage = (error.message || error.error || JSON.stringify(error) || '').toString();
                        if (errorMessage.includes('Market API configuration missing') || 
                            errorMessage.includes('configuration missing') ||
                            errorMessage.includes('Produsul nu a fost găsit în sistem') ||
                            errorMessage.includes('nu a fost găsit')) {
                            shopwellLog('Market API error ignored, skipping availability check for SKU: ' + item.sku);
                            // Don't add to errors - allow checkout to proceed
                            // Skip this item and continue with next
                        } else {
                            // Only add non-configuration errors
                            errors.push({
                                sku: item.sku,
                                productName: item.productName,
                                error: error.message || error.error || 'Error checking availability',
                                type: 'error'
                            });
                        }
                    }
                }

                // Restore button
                $placeOrderBtn.prop('disabled', false).text(originalBtnText);

                return { results: results, errors: errors };
            } catch (error) {
                // Restore button
                $placeOrderBtn.prop('disabled', false).text(originalBtnText);
                throw error;
            }
        }

        /**
         * Display WooCommerce error notices
         */
        function displayAvailabilityErrors(errors) {
            // NEVER filter out stock errors (unavailable/insufficient) - these must always be shown
            // Only filter out configuration errors and generic "not found" errors for non-stock errors
            errors = errors.filter(function(error) {
                // Always show stock-related errors
                if (error.type === 'unavailable' || error.type === 'insufficient') {
                    return true;
                }
                
                // For other errors, filter out configuration errors
                const errorMessage = error.error || error.message || '';
                if (errorMessage.includes('Market API configuration missing') || 
                    errorMessage.includes('configuration missing') ||
                    errorMessage.includes('Produsul nu a fost găsit în sistem') ||
                    errorMessage.includes('nu a fost găsit')) {
                    return false; // Don't display this error
                }
                return true;
            });

            // If no errors left after filtering, don't display anything
            if (errors.length === 0) {
                return;
            }

            // Remove existing notices
            $('.woocommerce-error, .woocommerce-info').remove();

            // Create notice container if it doesn't exist
            if (!$('.woocommerce-notices-wrapper').length) {
                checkoutForm.before('<ul class="woocommerce-error" role="alert"></ul>');
            }

            let $errorList = $('.woocommerce-error').first();
            if (!$errorList.length) {
                checkoutForm.before('<ul class="woocommerce-error" role="alert"></ul>');
                $errorList = $('.woocommerce-error').first();
            }

            // Add error messages
            errors.forEach(function(error) {
                let message = '';
                
                if (error.type === 'unavailable') {
                    message = '<strong>' + error.productName + '</strong> ' + shopwellCheckoutSku.unavailableText;
                } else if (error.type === 'insufficient') {
                    message = '<strong>' + error.productName + '</strong> ' + shopwellCheckoutSku.insufficientText + ' ' + error.available;
                } else {
                    message = '<strong>' + error.productName + '</strong> ' + (error.error || 'Eroare la verificare');
                }

                $errorList.append('<li>' + message + '</li>');
            });

            // Scroll to errors
            $('html, body').animate({
                scrollTop: $errorList.offset().top - 100
            }, 500);
        }


        /**
         * Validate checkout form - required fields and terms
         */
        function validateCheckoutForm() {
            let isValid = true;
            const errors = [];
            const checkedFields = {}; // Track which fields we've already checked
            
            // First, try to use WooCommerce's validation if available
            if (typeof $ !== 'undefined' && $.fn.validate && checkoutForm.validate) {
                try {
                    const wcValid = checkoutForm.valid();
                    if (!wcValid) {
                        // WooCommerce validation failed, but we'll do our own check too
                        isValid = false;
                    }
                } catch (e) {
                    // WooCommerce validation not available, continue with our validation
                }
            }

            // Helper function to check a single field
            function checkField($field) {
                const fieldId = $field.attr('id') || $field.attr('name');
                if (checkedFields[fieldId]) {
                    return true; // Already checked
                }
                checkedFields[fieldId] = true;

                const fieldType = $field.attr('type');
                const fieldName = $field.attr('name');
                let fieldValue = '';

                // Skip hidden fields (but not terms checkbox which might be hidden)
                if ($field.is(':hidden') && !$field.is('#terms, input[name="terms"], input#terms-field, input[name="terms-field"]')) {
                    return true;
                }

                // Get field value based on type
                if (fieldType === 'checkbox' || fieldType === 'radio') {
                    // For checkboxes/radios, check if any in the group is checked
                    const name = $field.attr('name');
                    if (name) {
                        const isChecked = checkoutForm.find('input[name="' + name.replace(/[\[\]]/g, '\\$&') + '"]:checked').length > 0;
                        if (!isChecked) {
                            isValid = false;
                            const label = $field.closest('.form-row').find('label').first().text().trim();
                            const labelText = label || $field.closest('label').text().trim() || 'Acest câmp';
                            // Store field reference for error display
                            errors.push({
                                field: name,
                                fieldElement: $field,
                                message: labelText + ' este obligatoriu.'
                            });
                        }
                    }
                } else {
                    fieldValue = $field.val();
                    if (!fieldValue || (typeof fieldValue === 'string' && fieldValue.trim() === '')) {
                        isValid = false;
                        const label = $field.closest('.form-row').find('label').first().text().trim() || 
                                     $field.closest('label').text().trim() || 
                                     $field.attr('placeholder') || 
                                     'Acest câmp';
                        // Store field reference for error display
                        errors.push({
                            field: fieldName || $field.attr('id') || $field.attr('name'),
                            fieldElement: $field,
                            message: label + ' este obligatoriu.'
                        });
                    }
                }
                return true;
            }

            // Check required fields (HTML5 required attribute)
            checkoutForm.find('input[required], select[required], textarea[required]').each(function() {
                checkField($(this));
            });

            // Check fields with aria-required="true" (WooCommerce uses this for accessibility)
            checkoutForm.find('input[aria-required="true"], select[aria-required="true"], textarea[aria-required="true"]').each(function() {
                checkField($(this));
            });

            // Check for fields with validate-required class (WooCommerce standard)
            checkoutForm.find('.validate-required input, .validate-required select, .validate-required textarea').each(function() {
                checkField($(this));
            });

            // Also check fields marked as required by WooCommerce data attributes
            checkoutForm.find('[data-required="1"], [data-required="true"]').each(function() {
                checkField($(this));
            });
            
            // Check WooCommerce billing and shipping required fields
            // WooCommerce marks required fields with validate-required class on the form-row
            checkoutForm.find('.form-row.validate-required').each(function() {
                const $row = $(this);
                const $field = $row.find('input, select, textarea').first();
                if ($field.length) {
                    checkField($field);
                }
            });
            
            // Also check for any input/select/textarea in a required form-row that might be missed
            checkoutForm.find('.form-row.required input, .form-row.required select, .form-row.required textarea').each(function() {
                checkField($(this));
            });

            // Check terms and conditions checkbox
            // WooCommerce uses different selectors for terms checkbox
            const termsCheckbox = checkoutForm.find('#terms, input[name="terms"], input#terms-field, input[name="terms-field"]');
            if (termsCheckbox.length > 0) {
                // Check if any terms checkbox is checked
                const isTermsChecked = termsCheckbox.filter(':checked').length > 0;
                if (!isTermsChecked) {
                    isValid = false;
                    errors.push({
                        field: 'terms',
                        fieldElement: termsCheckbox,
                        message: 'Trebuie să acceptați termenii și condițiile pentru a continua.'
                    });
                }
            }

            return { isValid: isValid, errors: errors };
        }

        /**
         * Display validation errors under each field
         */
        function displayValidationErrors(errors) {
            // Remove existing error messages from fields
            checkoutForm.find('.woocommerce-invalid-required-field').removeClass('woocommerce-invalid woocommerce-invalid-required-field');
            checkoutForm.find('.woocommerce-invalid-field').removeClass('woocommerce-invalid woocommerce-invalid-field');
            checkoutForm.find('.woocommerce-error-message').remove();
            
            // Remove general error notices
            $('.woocommerce-error, .woocommerce-info').remove();

            let firstErrorField = null;

            // Add error messages under each field
            errors.forEach(function(error) {
                if (error.field === 'terms' || (error.fieldElement && error.fieldElement.is('#terms, input[name="terms"], input#terms-field, input[name="terms-field"]'))) {
                    // Handle terms checkbox error
                    const $termsWrapper = checkoutForm.find('.woocommerce-terms-and-conditions-wrapper, .form-row.woocommerce-terms-and-conditions');
                    if ($termsWrapper.length) {
                        // Remove existing error
                        $termsWrapper.find('.woocommerce-error-message').remove();
                        // Add error message
                        const $errorMsg = $('<div class="woocommerce-error-message" role="alert" style="color: #e2401c; font-size: 0.875em; margin-top: 0.5em;">' + error.message + '</div>');
                        $termsWrapper.append($errorMsg);
                        if (!firstErrorField) {
                            firstErrorField = $termsWrapper;
                        }
                    }
                } else {
                    // Use stored field element if available, otherwise find by field name/ID
                    let $field = error.fieldElement;
                    
                    if (!$field || !$field.length) {
                        // Try to find by name attribute
                        $field = checkoutForm.find('[name="' + error.field.replace(/[\[\]]/g, '\\$&') + '"]');
                        
                        // If not found, try by ID
                        if (!$field.length) {
                            $field = checkoutForm.find('#' + error.field);
                        }
                        
                        // If still not found, try partial name match
                        if (!$field.length && error.field) {
                            const fieldName = error.field.replace(/\[.*$/, '');
                            $field = checkoutForm.find('[name^="' + fieldName + '"]').first();
                        }
                    }
                    
                    if ($field && $field.length) {
                        const $formRow = $field.closest('.form-row');
                        if ($formRow.length) {
                            // Remove existing error classes and messages
                            $formRow.removeClass('woocommerce-invalid woocommerce-invalid-required-field');
                            $formRow.find('.woocommerce-error-message').remove();
                            
                            // Add error class to field and form row
                            $field.addClass('woocommerce-invalid woocommerce-invalid-required-field');
                            $formRow.addClass('woocommerce-invalid woocommerce-invalid-required-field');
                            
                            // Add error message below the field
                            const $errorMsg = $('<div class="woocommerce-error-message" role="alert" style="color: #e2401c; font-size: 0.875em; margin-top: 0.5em;">' + error.message + '</div>');
                            $formRow.append($errorMsg);
                            
                            if (!firstErrorField) {
                                firstErrorField = $formRow;
                            }
                        }
                    }
                }
            });

            // Scroll to first error field
            if (firstErrorField && firstErrorField.length) {
                try {
                    const errorTop = firstErrorField.offset().top;
                    if (errorTop) {
                        $('html, body').animate({
                            scrollTop: errorTop - 100
                        }, 500);
                    }
                } catch (scrollError) {
                    // Ignore scroll errors
                }
            }
        }

        /**
         * Intercept checkout form submission - PRIMARY VALIDATION POINT
         * This runs BEFORE WooCommerce's AJAX submission
         */
        $(document.body).on('checkout_place_order', checkoutForm, function(e, $form) {
            // This event is triggered by WooCommerce BEFORE AJAX submission
            // If we're checking availability, block it
            if (isCheckingAvailability) {
                e.preventDefault();
                return false;
            }
            
            // SAFETY CHECK: If checkout is blocked due to stock errors, prevent submission
            if (checkoutSubmitBlocked) {
                e.preventDefault();
                e.stopImmediatePropagation();
                e.stopPropagation();
                // Unblock the button
                const $submitButton = $('button[name="woocommerce_checkout_place_order"]', checkoutForm);
                $submitButton.removeClass('processing').prop('disabled', false);
                validationPassed = false;
                shopwellLog('Order submission blocked due to stock validation');
                return false;
            }
            
            // ALWAYS validate required fields and terms - this is the PRIMARY check
            // Don't trust the validationPassed flag here - validate again to be sure
            const validation = validateCheckoutForm();
            if (!validation.isValid) {
                e.preventDefault();
                e.stopImmediatePropagation();
                e.stopPropagation();
                displayValidationErrors(validation.errors);
                // Unblock the button
                const $submitButton = $('button[name="woocommerce_checkout_place_order"]', checkoutForm);
                $submitButton.removeClass('processing').prop('disabled', false);
                // Reset validation flag
                validationPassed = false;
                return false;
            }
            
            // If we get here, validation passed - set the flag
            validationPassed = true;
        });
        
        // Intercept button click - validate BEFORE WooCommerce processes it
        $(document.body).on('click', 'button[name="woocommerce_checkout_place_order"]', function(e) {
            const $button = $(this);
            const $form = $button.closest('form.checkout');
            
            // Only validate if button is in checkout form and we haven't already validated
            if ($form.length && !validationPassed && !isCheckingAvailability && !checkoutSubmitBlocked) {
                const validation = validateCheckoutForm();
                if (!validation.isValid) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    e.stopPropagation();
                    // Prevent WooCommerce from processing
                    $button.removeClass('processing').prop('disabled', false);
                    displayValidationErrors(validation.errors);
                    return false;
                }
            }
        });

        // Intercept form submit - this is a fallback for non-AJAX submissions
        const submitHandler = async function(e) {
            // CRITICAL FIX: Only allow bypass if stock was checked successfully very recently (within 2 seconds)
            // This prevents the bypass while still allowing WooCommerce's triggered submission to proceed
            const now = Date.now();
            const recentStockCheck = stockCheckCompleted && (now - stockCheckTimestamp) < 2000;
            
            if (validationPassed && recentStockCheck && !isCheckingAvailability && !checkoutSubmitBlocked) {
                // Stock was checked successfully very recently, allow submission
                validationPassed = false; // Reset flag
                stockCheckCompleted = false; // Reset stock check flag
                // Don't prevent default - let WooCommerce handle it
                return true;
            }
            
            // If validation passed but stock check is old or missing, force re-check
            if (validationPassed && !recentStockCheck) {
                validationPassed = false;
                stockCheckCompleted = false;
            }

            // If we're already checking, block submission
            if (isCheckingAvailability || checkoutSubmitBlocked) {
                e.preventDefault();
                e.stopImmediatePropagation();
                e.stopPropagation();
                return false;
            }

            // Validate required fields and terms - this is a fallback check
            // Primary validation happens in checkout_place_order event
            const validation = validateCheckoutForm();
            if (!validation.isValid) {
                e.preventDefault();
                e.stopImmediatePropagation();
                e.stopPropagation();
                // Also disable submit button to prevent any other submission attempts
                const $submitButton = $('button[name="woocommerce_checkout_place_order"], button[type="submit"]', checkoutForm);
                $submitButton.removeClass('processing').prop('disabled', false);
                displayValidationErrors(validation.errors);
                return false;
            }

            // Prevent default submission so we can do SKU check first
            e.preventDefault();
            e.stopImmediatePropagation();
            e.stopPropagation();

            isCheckingAvailability = true;

            try {
                // Try to get SKUs from page first (from localized script)
                let cartSkus = getCartSkus();

                // If no SKUs found, try AJAX method
                if (cartSkus.length === 0) {
                    try {
                        cartSkus = await getCartSkusFromWooCommerce();
                    } catch (error) {
                        shopwellLog('Could not retrieve cart SKUs via AJAX', error);
                        // As a fallback, we'll allow submission if we can't get SKUs
                        shopwellLog('Could not retrieve cart SKUs, allowing submission');
                        isCheckingAvailability = false;
                        
                        // Re-validate before allowing submission
                        const finalValidation = validateCheckoutForm();
                        if (!finalValidation.isValid) {
                            displayValidationErrors(finalValidation.errors);
                            const $submitButton = $('button[name="woocommerce_checkout_place_order"]', checkoutForm);
                            $submitButton.removeClass('processing').prop('disabled', false);
                            return false;
                        }
                        
                        validationPassed = true;
                        // Remove our handler temporarily
                        checkoutForm.off('submit', submitHandler);
                        // Trigger WooCommerce's checkout
                        $(document.body).trigger('checkout_place_order', [checkoutForm]);
                        const $submitButton = $('button[name="woocommerce_checkout_place_order"]', checkoutForm);
                        if ($submitButton.length) {
                            setTimeout(function() {
                                $submitButton.trigger('click');
                            }, 10);
                        } else {
                            checkoutForm[0].submit();
                        }
                        return false;
                    }
                }

                // Filter out items without SKU
                cartSkus = cartSkus.filter(item => item.sku && item.sku.trim() !== '');

                if (cartSkus.length === 0) {
                    shopwellLog('No valid SKUs found, allowing submission');
                    isCheckingAvailability = false;
                    
                    // Re-validate before allowing submission
                    const finalValidation = validateCheckoutForm();
                    if (!finalValidation.isValid) {
                        displayValidationErrors(finalValidation.errors);
                        const $submitButton = $('button[name="woocommerce_checkout_place_order"]', checkoutForm);
                        $submitButton.removeClass('processing').prop('disabled', false);
                        return false;
                    }
                    
                    validationPassed = true;
                    // Remove our handler temporarily
                    checkoutForm.off('submit', submitHandler);
                    // Trigger WooCommerce's checkout
                    $(document.body).trigger('checkout_place_order', [checkoutForm]);
                    const $submitButton = $('button[name="woocommerce_checkout_place_order"]', checkoutForm);
                    if ($submitButton.length) {
                        setTimeout(function() {
                            $submitButton.trigger('click');
                        }, 10);
                    } else {
                        checkoutForm[0].submit();
                    }
                    return false;
                }

                // Check all SKUs
                const checkResult = await checkAllSkusAvailability(cartSkus);

                isCheckingAvailability = false;

                // STRICT VALIDATION: If there are ANY stock errors, BLOCK order placement
                if (checkResult.errors && checkResult.errors.length > 0) {
                    // Filter to only show actual stock errors (unavailable or insufficient)
                    const stockErrors = checkResult.errors.filter(function(error) {
                        return error.type === 'unavailable' || error.type === 'insufficient';
                    });
                    
                    // If there are stock-related errors, block the order
                    if (stockErrors.length > 0) {
                        shopwellLog('Order blocked due to stock issues:', stockErrors);
                        displayAvailabilityErrors(stockErrors);
                        checkoutSubmitBlocked = true;
                        validationPassed = false; // Ensure validation flag is reset
                        stockCheckCompleted = false; // Reset stock check flag
                        
                        // Re-enable submit button after a short delay
                        setTimeout(function() {
                            checkoutSubmitBlocked = false;
                        }, 1000);
                        
                        // Prevent any further submission attempts
                        return false;
                    }
                    
                    // If there are other errors (not stock-related), still block but log them
                    if (checkResult.errors.length > stockErrors.length) {
                        displayAvailabilityErrors(checkResult.errors);
                        checkoutSubmitBlocked = true;
                        validationPassed = false;
                        stockCheckCompleted = false; // Reset stock check flag
                        
                        setTimeout(function() {
                            checkoutSubmitBlocked = false;
                        }, 1000);
                        
                        return false;
                    }
                }

                // All checks passed, allow submission
                checkoutSubmitBlocked = false;
                
                // Remove any configuration error messages before submission (safety measure)
                $('.woocommerce-error li').each(function() {
                    const $li = $(this);
                    const text = $li.text();
                    if (text.includes('Market API configuration missing') || 
                        text.includes('configuration missing')) {
                        $li.remove();
                    }
                });
                
                // Remove empty error lists
                $('.woocommerce-error').each(function() {
                    if ($(this).find('li').length === 0) {
                        $(this).remove();
                    }
                });
                
                // IMPORTANT: Re-validate one more time before allowing submission
                // This ensures fields are still valid after SKU check
                const finalValidation = validateCheckoutForm();
                if (!finalValidation.isValid) {
                    displayValidationErrors(finalValidation.errors);
                    const $submitButton = $('button[name="woocommerce_checkout_place_order"]', checkoutForm);
                    $submitButton.removeClass('processing').prop('disabled', false);
                    return false;
                }
                
                // Set flags to indicate validation and stock check passed
                validationPassed = true;
                stockCheckCompleted = true;
                stockCheckTimestamp = Date.now();
                
                // Remove our handler temporarily to avoid loop
                checkoutForm.off('submit', submitHandler);
                
                // Trigger WooCommerce's checkout by triggering checkout_place_order event
                // This is the proper way to trigger WooCommerce's AJAX checkout
                $(document.body).trigger('checkout_place_order', [checkoutForm]);
                
                // Also click the button as fallback (WooCommerce listens to button click)
                const $submitButton = $('button[name="woocommerce_checkout_place_order"]', checkoutForm);
                if ($submitButton.length) {
                    setTimeout(function() {
                        $submitButton.trigger('click');
                    }, 10);
                } else {
                    // Last resort: submit the form directly
                    checkoutForm[0].submit();
                }
                return false;

            } catch (error) {
                shopwellLog('Error checking SKU availability', error);
                isCheckingAvailability = false;
                checkoutSubmitBlocked = false;
                
                // On error, validate before allowing submission
                const finalValidation = validateCheckoutForm();
                if (!finalValidation.isValid) {
                    displayValidationErrors(finalValidation.errors);
                    const $submitButton = $('button[name="woocommerce_checkout_place_order"]', checkoutForm);
                    $submitButton.removeClass('processing').prop('disabled', false);
                    return false;
                }
                
                validationPassed = true;
                // Remove our handler temporarily
                checkoutForm.off('submit', submitHandler);
                // Trigger WooCommerce's checkout
                $(document.body).trigger('checkout_place_order', [checkoutForm]);
                const $submitButton = $('button[name="woocommerce_checkout_place_order"]', checkoutForm);
                if ($submitButton.length) {
                    setTimeout(function() {
                        $submitButton.trigger('click');
                    }, 10);
                } else {
                    checkoutForm[0].submit();
                }
                return false;
            }
        };
        
        checkoutForm.on('submit', submitHandler);
    });

})(jQuery);

