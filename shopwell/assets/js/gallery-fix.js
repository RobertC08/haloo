/**
 * Fix: Gallery image replacement on variation change
 * Prevents images from stacking while keeping all gallery images visible for all variants
 */
(function(jQuery) {
    "use strict";
    
    jQuery(document).ready(function() {
        var gallery = jQuery(".woocommerce-product-gallery");
        var originalGalleryImages = [];
        var originalThumbnails = [];
        var isInitialized = false;
        
        // Store original gallery images and thumbnails on page load
        function storeOriginalGallery() {
            if (gallery.length && !isInitialized) {
                var wrapper = gallery.find(".woocommerce-product-gallery__wrapper");
                var images = wrapper.find(".woocommerce-product-gallery__image");
                var thumbnails = gallery.find(".flex-control-thumbs li");
                
                // Store all original images
                images.each(function(index) {
                    var $img = jQuery(this);
                    originalGalleryImages.push({
                        html: $img.clone(),
                        index: index,
                        attachmentId: $img.find('img').data('attachment_id') || $img.find('img').attr('data-attachment-id') || null
                    });
                });
                
                // Store all original thumbnails
                thumbnails.each(function(index) {
                    var $thumb = jQuery(this);
                    originalThumbnails.push({
                        html: $thumb.clone(),
                        index: index
                    });
                });
                
                isInitialized = true;
            }
        }
        
        // Store original gallery after a short delay to ensure it's fully loaded
        setTimeout(storeOriginalGallery, 500);
        jQuery(window).on("load", function() {
            setTimeout(storeOriginalGallery, 300);
        });
        
        // Function to restore missing gallery images
        function restoreMissingGalleryImages() {
            if (!isInitialized || originalGalleryImages.length <= 1) {
                return;
            }
            
            var wrapper = gallery.find(".woocommerce-product-gallery__wrapper");
            var currentImages = wrapper.find(".woocommerce-product-gallery__image");
            var currentSrcs = [];
            
            // Get all current image sources
            currentImages.each(function() {
                var src = jQuery(this).find('img').attr('src') || jQuery(this).find('img').attr('data-src') || '';
                if (src) {
                    currentSrcs.push(src);
                }
            });
            
            // Check if any original images are missing
            originalGalleryImages.forEach(function(originalImg, index) {
                if (index > 0) { // Skip first image (WooCommerce manages it)
                    var originalSrc = originalImg.html.find('img').attr('src') || originalImg.html.find('img').attr('data-src') || '';
                    if (originalSrc && currentSrcs.indexOf(originalSrc) === -1) {
                        // This original image is missing, restore it
                        var $newImg = originalImg.html.clone();
                        // Insert after the first image (which is the variation image)
                        if (currentImages.length > 0) {
                            currentImages.first().after($newImg);
                        } else {
                            wrapper.append($newImg);
                        }
                    }
                }
            });
        }
        
        // Fix image stacking on variation change - only remove true duplicates, keep all gallery images
        jQuery(".variations_form").on("show_variation", function(event, variation) {
            if (variation && variation.image && variation.image.src) {
                var wrapper = gallery.find(".woocommerce-product-gallery__wrapper");
                var images = wrapper.find(".woocommerce-product-gallery__image");
                var variationImageSrc = variation.image.src;
                
                // Wait a bit for WooCommerce to update the image
                setTimeout(function() {
                    var currentImages = wrapper.find(".woocommerce-product-gallery__image");
                    var imageSrcs = [];
                    var duplicatesToRemove = [];
                    
                    // Identify duplicates (same src appearing multiple times)
                    currentImages.each(function(index) {
                        var $img = jQuery(this);
                        var imgSrc = $img.find('img').attr('src') || $img.find('img').attr('data-src') || '';
                        
                        if (imgSrc) {
                            // Check if we've seen this src before
                            if (imageSrcs.indexOf(imgSrc) !== -1) {
                                // This is a duplicate - mark for removal (but keep the first occurrence)
                                duplicatesToRemove.push($img);
                            } else {
                                imageSrcs.push(imgSrc);
                            }
                        }
                    });
                    
                    // Remove only true duplicates (not the first occurrence)
                    duplicatesToRemove.forEach(function($duplicate) {
                        $duplicate.remove();
                    });
                    
                    // Ensure all images are visible (not hidden by CSS)
                    currentImages.css({
                        'display': '',
                        'opacity': '',
                        'visibility': ''
                    });
                    
                    // Restore any missing original gallery images
                    restoreMissingGalleryImages();
                    
                    // Re-initialize FlexSlider to ensure it recognizes all images
                    var flexslider = gallery.data('flexslider');
                    if (flexslider && typeof flexslider.flexslider === 'function') {
                        setTimeout(function() {
                            try {
                                flexslider.flexslider('update');
                            } catch(e) {
                                // If update fails, try to reinitialize
                                console.log('FlexSlider update failed, gallery should still work');
                            }
                        }, 200);
                    }
                }, 100);
            }
        });
        
        // Reset to original on variation reset
        jQuery(".variations_form").on("reset_image", function() {
            // WooCommerce handles the reset, we just ensure no duplicates
            var wrapper = gallery.find(".woocommerce-product-gallery__wrapper");
            var images = wrapper.find(".woocommerce-product-gallery__image");
            
            // Remove any duplicates that might have been added
            var seenSrcs = [];
            images.each(function() {
                var $img = jQuery(this);
                var imgSrc = $img.find('img').attr('src') || '';
                if (imgSrc && seenSrcs.indexOf(imgSrc) !== -1) {
                    // This is a duplicate, remove it
                    $img.remove();
                } else if (imgSrc) {
                    seenSrcs.push(imgSrc);
                }
            });
        });
    });
})(jQuery);
