/**
 * Fix: Gallery image replacement on variation change
 * Prevents images from stacking instead of replacing
 */
(function(jQuery) {
    "use strict";
    
    jQuery(document).ready(function() {
        var originalImage = null;
        var gallery = jQuery(".woocommerce-product-gallery");
        
        // Store original main image on page load
        if (gallery.length) {
            var firstSlide = gallery.find(".woocommerce-product-gallery__image").first();
            if (firstSlide.length) {
                originalImage = firstSlide.clone();
            }
        }
        
        // Fix image stacking on variation change
        jQuery(".variations_form").on("show_variation", function(event, variation) {
            if (variation && variation.image && variation.image.src) {
                // Remove any duplicate images that were added
                var wrapper = gallery.find(".woocommerce-product-gallery__wrapper");
                var images = wrapper.find(".woocommerce-product-gallery__image");
                
                // Keep only the first image (which WooCommerce updates)
                if (images.length > 1) {
                    images.not(":first").remove();
                }
            }
        });
        
        // Reset to original on variation reset
        jQuery(".variations_form").on("reset_image", function() {
            if (originalImage) {
                var wrapper = gallery.find(".woocommerce-product-gallery__wrapper");
                var images = wrapper.find(".woocommerce-product-gallery__image");
                
                // Remove all except first and restore original
                images.not(":first").remove();
            }
        });
        
        // Clean up on page load
        jQuery(window).on("load", function() {
            setTimeout(function() {
                var wrapper = gallery.find(".woocommerce-product-gallery__wrapper");
                var images = wrapper.find(".woocommerce-product-gallery__image");
                if (images.length > 1) {
                    images.not(":first").remove();
                }
            }, 500);
        });
    });
})(jQuery);
