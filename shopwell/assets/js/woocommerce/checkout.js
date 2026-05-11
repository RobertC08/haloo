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

        // Store original submit handler
        let isCheckingAvailability = false;
        let checkoutSubmitBlocked = false;
        let placeOrderRefreshTimer = null;

        function isCheckoutRowVisible($row) {
            if (!$row.length) {
                return false;
            }
            if ($row.closest('.woocommerce-hidden').length) {
                return false;
            }
            return $row.is(':visible');
        }

        function isCheckoutRequiredFieldsFilled($form) {
            if ($form.find('input[name="payment_method"]').length &&
                !$form.find('input[name="payment_method"]:checked').length) {
                return false;
            }

            let ok = true;

            $form.find('.validate-required').each(function() {
                const $row = $(this);
                if (!isCheckoutRowVisible($row)) {
                    return;
                }

                const $controls = $row.find('input, select, textarea').filter(function() {
                    const el = this;
                    if (el.type === 'hidden' || el.disabled) {
                        return false;
                    }
                    return true;
                });

                if (!$controls.length) {
                    return;
                }

                const seenRadioNames = {};
                $controls.each(function() {
                    const el = this;
                    if (el.type === 'checkbox') {
                        if (!el.checked) {
                            ok = false;
                        }
                    } else if (el.type === 'radio') {
                        if (seenRadioNames[el.name]) {
                            return;
                        }
                        seenRadioNames[el.name] = true;
                        const hasChecked = $form.find('input[type="radio"]').filter(function() {
                            return this.name === el.name && this.checked;
                        }).length > 0;
                        if (!hasChecked) {
                            ok = false;
                        }
                    } else {
                        const v = (el.value || '').toString().trim();
                        if (!v) {
                            ok = false;
                        }
                    }
                });
            });

            $form.find('#terms, input[name="terms"]').each(function() {
                const el = this;
                if (el.disabled) {
                    return;
                }
                const $cb = $(el);
                const $wrap = $cb.closest('.woocommerce-terms-and-conditions-wrapper, p.form-row, .form-row');
                if (!$wrap.length || !isCheckoutRowVisible($wrap)) {
                    return;
                }
                const required = el.required ||
                    $wrap.hasClass('validate-required') ||
                    $cb.attr('aria-required') === 'true';
                if (!required) {
                    return;
                }
                if (!el.checked) {
                    ok = false;
                }
            });

            $form.find('input[required], select[required], textarea[required]').each(function() {
                const el = this;
                if (el.disabled || el.type === 'hidden') {
                    return;
                }
                if ($(el).closest('.validate-required').length) {
                    return;
                }
                const $wrap = $(el).closest('p.form-row, .form-row, li, .woocommerce-additional-fields__field-wrapper');
                if ($wrap.length && !isCheckoutRowVisible($wrap)) {
                    return;
                }
                if (el.type === 'checkbox') {
                    if (!el.checked) {
                        ok = false;
                    }
                } else if (el.type === 'radio') {
                    const name = el.name;
                    if (name) {
                        const has = $form.find('input[type="radio"]').filter(function() {
                            return this.name === name && this.checked;
                        }).length > 0;
                        if (!has) {
                            ok = false;
                        }
                    }
                } else if (!(el.value || '').toString().trim()) {
                    ok = false;
                }
            });

            return ok;
        }

        function refreshPlaceOrderButtonState() {
            const $btn = checkoutForm.find('button[name="woocommerce_checkout_place_order"]');
            if (!$btn.length) {
                return;
            }
            if (isCheckingAvailability || checkoutSubmitBlocked) {
                return;
            }
            if (!isCheckoutRequiredFieldsFilled(checkoutForm)) {
                $btn.prop('disabled', true).addClass('shopwell-checkout-place-order--fields-incomplete');
                if (typeof shopwellCheckoutSku.placeOrderLockedTitle === 'string') {
                    $btn.attr('title', shopwellCheckoutSku.placeOrderLockedTitle);
                }
            } else {
                $btn.prop('disabled', false).removeClass('shopwell-checkout-place-order--fields-incomplete');
                $btn.removeAttr('title');
            }
        }

        function schedulePlaceOrderStateRefresh() {
            clearTimeout(placeOrderRefreshTimer);
            placeOrderRefreshTimer = setTimeout(refreshPlaceOrderButtonState, 80);
        }

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

                        // Validate quantity response
                        const availableQty = parseInt(availability.quantity) || 0;
                        const requestedQty = parseInt(item.quantity) || 1;
                        
                        // Check if quantity is sufficient
                        if (availableQty === 0) {
                            errors.push({
                                sku: item.sku,
                                productName: item.productName,
                                requested: requestedQty,
                                available: 0,
                                type: 'unavailable'
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

                $placeOrderBtn.text(originalBtnText);

                return { results: results, errors: errors };
            } catch (error) {
                $placeOrderBtn.text(originalBtnText);
                throw error;
            }
        }

        /**
         * Display WooCommerce error notices
         */
        function displayAvailabilityErrors(errors) {
            // Filter out configuration errors and generic "not found" errors as a safety measure
            errors = errors.filter(function(error) {
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
         * Continue checkout through WooCommerce and browser validation.
         * HTMLFormElement.prototype.submit() does not fire the submit event and bypasses WC + HTML5 checks.
         */
        function triggerWooCommerceCheckoutSubmit($form) {
            const el = $form[0];
            if (!el) {
                return;
            }
            const btn = $form.find('button[name="woocommerce_checkout_place_order"]')[0];
            if (typeof el.requestSubmit === 'function') {
                try {
                    if (btn) {
                        el.requestSubmit(btn);
                    } else {
                        el.requestSubmit();
                    }
                    return;
                } catch (err) {
                    shopwellLog('Checkout submit stopped (validation or requestSubmit).', err);
                    return;
                }
            }
            if (btn) {
                btn.click();
            }
        }

        /**
         * Intercept checkout form submission
         */
        checkoutForm.on('checkout_place_order', function(e) {
            // This event is triggered by WooCommerce before submission
            if (isCheckingAvailability) {
                e.preventDefault();
                return false;
            }
        });

        checkoutForm.on(
            'input change blur click',
            'input, select, textarea',
            schedulePlaceOrderStateRefresh
        );
        $(document.body).on(
            'updated_checkout init_checkout country_to_state_changed',
            schedulePlaceOrderStateRefresh
        );
        setTimeout(schedulePlaceOrderStateRefresh, 0);
        setTimeout(schedulePlaceOrderStateRefresh, 400);

        // Intercept form submit
        const submitHandler = async function(e) {
            // If we're already checking, block submission
            if (isCheckingAvailability || checkoutSubmitBlocked) {
                e.preventDefault();
                e.stopImmediatePropagation();
                return false;
            }

            if (!isCheckoutRequiredFieldsFilled(checkoutForm)) {
                e.preventDefault();
                e.stopImmediatePropagation();
                const formEl = checkoutForm[0];
                if (formEl && typeof formEl.reportValidity === 'function') {
                    formEl.reportValidity();
                }
                schedulePlaceOrderStateRefresh();
                return false;
            }

            // Prevent default submission
            e.preventDefault();
            e.stopImmediatePropagation();

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
                        checkoutForm.off('submit', submitHandler);
                        schedulePlaceOrderStateRefresh();
                        if (!isCheckoutRequiredFieldsFilled(checkoutForm)) {
                            return false;
                        }
                        triggerWooCommerceCheckoutSubmit(checkoutForm);
                        return false;
                    }
                }

                // Filter out items without SKU
                cartSkus = cartSkus.filter(item => item.sku && item.sku.trim() !== '');

                if (cartSkus.length === 0) {
                    shopwellLog('No valid SKUs found, allowing submission');
                    isCheckingAvailability = false;
                    checkoutForm.off('submit', submitHandler);
                    schedulePlaceOrderStateRefresh();
                    if (!isCheckoutRequiredFieldsFilled(checkoutForm)) {
                        return false;
                    }
                    triggerWooCommerceCheckoutSubmit(checkoutForm);
                    return false;
                }

                // Check all SKUs
                const checkResult = await checkAllSkusAvailability(cartSkus);

                isCheckingAvailability = false;
                schedulePlaceOrderStateRefresh();

                // If there are errors, display them and block submission
                if (checkResult.errors.length > 0) {
                    displayAvailabilityErrors(checkResult.errors);
                    checkoutSubmitBlocked = true;
                    
                    // Re-enable submit after a short delay
                    setTimeout(function() {
                        checkoutSubmitBlocked = false;
                        schedulePlaceOrderStateRefresh();
                    }, 1000);
                    
                    return false;
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
                
                checkoutForm.off('submit', submitHandler);
                schedulePlaceOrderStateRefresh();
                if (!isCheckoutRequiredFieldsFilled(checkoutForm)) {
                    return false;
                }
                triggerWooCommerceCheckoutSubmit(checkoutForm);
                return false;

            } catch (error) {
                shopwellLog('Error checking SKU availability', error);
                isCheckingAvailability = false;
                checkoutSubmitBlocked = false;

                checkoutForm.off('submit', submitHandler);
                schedulePlaceOrderStateRefresh();
                if (!isCheckoutRequiredFieldsFilled(checkoutForm)) {
                    return false;
                }
                triggerWooCommerceCheckoutSubmit(checkoutForm);
                return false;
            }
        };
        
        checkoutForm.on('submit', submitHandler);
    });

})(jQuery);

