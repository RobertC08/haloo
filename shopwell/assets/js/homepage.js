// Mobile carousel with simple, reliable touch handling
document.addEventListener('DOMContentLoaded', function() {
    const track = document.querySelector('.testimonials-carousel-right .carousel-track');
    if (!track) {
        return;
    }
    
    
    // Mouse events for desktop testing
    let isDown = false;
    let startX, scrollLeft;

    track.addEventListener('mousedown', e => {
        isDown = true;
        track.classList.add('grabbing');
        startX = e.pageX - track.offsetLeft;
        scrollLeft = track.scrollLeft;
    });
    
    track.addEventListener('mouseleave', () => { 
        isDown = false; 
        track.classList.remove('grabbing');
    });
    
    track.addEventListener('mouseup', () => { 
        isDown = false; 
        track.classList.remove('grabbing');
    });
    
    track.addEventListener('mousemove', e => {
        if(!isDown) return;
        e.preventDefault();
        const x = e.pageX - track.offsetLeft;
        const walk = (x - startX) * 1.5; // scroll-fastness
        track.scrollLeft = scrollLeft - walk;
    });

    // Simple touch swipe for mobile - only move on clear swipe
    let touchStartX = 0;
    let touchStartY = 0;
    
    track.addEventListener('touchstart', e => {
        touchStartX = e.touches[0].clientX;
        touchStartY = e.touches[0].clientY;
    }, { passive: true });

    track.addEventListener('touchend', e => {
        const touchEndX = e.changedTouches[0].clientX;
        const touchEndY = e.changedTouches[0].clientY;
        const diffX = touchStartX - touchEndX;
        const diffY = touchStartY - touchEndY;
        
        // Only respond to clear horizontal swipes (not vertical scrolling)
        if (Math.abs(diffX) > Math.abs(diffY) && Math.abs(diffX) > 80) {
            const cardWidth = track.querySelector('.testimonial-item').offsetWidth;
            const newScrollLeft = track.scrollLeft + (diffX > 0 ? cardWidth : -cardWidth);
            
            track.scrollTo({
                left: newScrollLeft,
                behavior: 'smooth'
            });
        } else {
        }
    }, { passive: true });
    
    // Add active class to centered item
    track.addEventListener('scroll', () => {
        const items = track.querySelectorAll('.testimonial-item');
        const trackRect = track.getBoundingClientRect();
        const trackCenter = trackRect.left + trackRect.width / 2;
        
        items.forEach(item => {
            item.classList.remove('active');
            const itemRect = item.getBoundingClientRect();
            const itemCenter = itemRect.left + itemRect.width / 2;
            
            if (Math.abs(itemCenter - trackCenter) < 50) {
                item.classList.add('active');
            }
        });
    });
    
});

// Categories Slider
document.addEventListener('DOMContentLoaded', function() {
    const slider = document.querySelector('.catalog-top-categories__slider');
    const prevBtn = document.querySelector('.catalog-top-categories__nav--prev');
    const nextBtn = document.querySelector('.catalog-top-categories__nav--next');
    const wrapper = document.querySelector('.catalog-top-categories__wrapper');
    
    if (!slider || !prevBtn || !nextBtn || !wrapper) {
        return;
    }
    
    
    const items = slider.querySelectorAll('.catalog-top-categories__item');
    const totalItems = items.length;
    let currentSlide = 0;
    let itemsPerView = 9; // Default for desktop
    
    // Calculate items per view based on screen size
    function getItemsPerView() {
        if (window.innerWidth <= 360) return 4;
        if (window.innerWidth <= 480) return 5;
        if (window.innerWidth <= 768) return 6;
        return 9; // Show 9 items on desktop
    }
    
    // Check if mobile
    function isMobile() {
        return window.innerWidth <= 768;
    }
    
    // Get maximum slide position
    function getMaxSlide() {
        // Calculate based on wrapper width and slider width to allow item-by-item scrolling
        const wrapperWidth = wrapper.offsetWidth;
        const sliderWidth = slider.scrollWidth;
        const itemWidth = items[0] ? items[0].offsetWidth + 36 : 126; // 90px + 36px gap
        
        // Calculate how many items can fit in the visible area
        const visibleItems = Math.floor(wrapperWidth / itemWidth);
        
        // If all items fit, no need to scroll
        if (sliderWidth <= wrapperWidth) {
            return 0;
        }
        
        // Calculate maximum slide position to scroll one item at a time
        // Allow scrolling until the last item is visible
        const maxScroll = totalItems - visibleItems;
        return Math.max(0, maxScroll);
    }
    
    
    // Update slider position
    function updateSlider() {
        const itemWidth = items[0] ? items[0].offsetWidth + 36 : 126;
        
        // Get actual dimensions accounting for padding
        const wrapperRect = wrapper.getBoundingClientRect();
        const sliderRect = slider.getBoundingClientRect();
        const wrapperWidth = wrapperRect.width;
        const sliderWidth = slider.scrollWidth;
        
        // Calculate the maximum translate to show the last item fully
        // This aligns the right edge of the slider content with the right edge of the visible wrapper area
        const maxPossibleTranslate = -(sliderWidth - wrapperWidth);
        const maxSlide = getMaxSlide();
        
        // Ensure currentSlide doesn't go negative
        if (currentSlide < 0) {
            currentSlide = 0;
        }
        
        // Calculate translateX based on currentSlide (one item per slide)
        let translateX = -currentSlide * itemWidth;
        
        // Check if we need to show the last item fully
        const isAtEnd = currentSlide >= maxSlide && maxSlide > 0;
        
        if (isAtEnd) {
            // When at the end, ensure the last item is fully visible
            // Use maxPossibleTranslate which aligns the right edge of slider with right edge of wrapper
            translateX = maxPossibleTranslate;
        }
        
        // Clamp translateX to prevent scrolling beyond bounds
        if (translateX < maxPossibleTranslate) {
            translateX = maxPossibleTranslate;
        }
        
        // Ensure we don't go past the start
        if (translateX > 0) {
            translateX = 0;
            currentSlide = 0;
        }
        
        slider.style.transform = `translateX(${translateX}px)`;
        slider.style.transition = 'transform 0.1s ease-out';
        
        // Update button states - check if we're at the actual end position
        const isAtStart = currentSlide === 0 || translateX >= -1; // Allow 1px tolerance
        const isAtActualEnd = Math.abs(translateX - maxPossibleTranslate) < 2; // Check if we're at max position
        
        prevBtn.style.opacity = isAtStart ? '0.5' : '1';
        nextBtn.style.opacity = isAtActualEnd ? '0.5' : '1';
        
        // Disable buttons at limits
        prevBtn.disabled = isAtStart;
        nextBtn.disabled = isAtActualEnd;
    }
    
    // Go to specific slide
    function goToSlide(slideIndex) {
        const maxSlide = getMaxSlide();
        currentSlide = Math.max(0, Math.min(slideIndex, maxSlide));
        updateSlider();
    }
    
    // Next slide - move one category box at a time
    function nextSlide(e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        
        const itemWidth = items[0] ? items[0].offsetWidth + 36 : 126;
        const wrapperWidth = wrapper.offsetWidth;
        const sliderWidth = slider.scrollWidth;
        const maxPossibleTranslate = -(sliderWidth - wrapperWidth);
        const currentTranslate = -currentSlide * itemWidth;
        
        // Move one item forward
        currentSlide++;
        
        // updateSlider will clamp if needed
        updateSlider();
    }
    
    // Previous slide - move one category box at a time
    function prevSlide(e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        
        // Move one item backward
        if (currentSlide > 0) {
            currentSlide--;
        }
        
        updateSlider();
    }
    
    // Event listeners
    prevBtn.addEventListener('click', prevSlide);
    nextBtn.addEventListener('click', nextSlide);
    
    // Fast mouse drag support for desktop
    let mouseDown = false;
    let mouseStartX = 0;
    let mouseCurrentX = 0;
    
    wrapper.addEventListener('mousedown', (e) => {
        // Don't trigger drag if clicking on a category item link
        const target = e.target;
        const categoryItem = target.closest('.catalog-top-categories__item');
        if (categoryItem) {
            // Allow the link to work normally
            return;
        }
        
        mouseDown = true;
        mouseStartX = e.clientX;
        slider.style.transition = 'none';
        wrapper.style.cursor = 'grabbing';
        e.preventDefault();
    });
    
    wrapper.addEventListener('mousemove', (e) => {
        if (mouseDown) {
            mouseCurrentX = e.clientX;
            const itemWidth = items[0].offsetWidth + 36;
            const dragOffset = (mouseCurrentX - mouseStartX) / itemWidth;
            const baseTranslateX = -currentSlide * itemWidth;
            const dragTranslateX = baseTranslateX + (dragOffset * itemWidth);
            slider.style.transform = `translateX(${dragTranslateX}px)`;
        }
    });
    
    wrapper.addEventListener('mouseup', (e) => {
        if (mouseDown) {
            mouseDown = false;
            const deltaX = mouseStartX - mouseCurrentX;
            const itemWidth = items[0].offsetWidth + 36;
            const threshold = 50;
            
            // Calculate how many categories to move based on drag distance
            if (Math.abs(deltaX) > threshold) {
                const categoriesToMove = Math.round(deltaX / itemWidth);
                const newSlide = currentSlide + categoriesToMove;
                const maxSlide = getMaxSlide();
                
                // Clamp to valid range
                currentSlide = Math.max(0, Math.min(newSlide, maxSlide));
                updateSlider();
            }
            // If no significant drag, stay exactly where it is - no snap back
            
            slider.style.transition = 'transform 0.1s ease-out';
            wrapper.style.cursor = 'grab';
        }
    });
    
    wrapper.addEventListener('mouseleave', () => {
        if (mouseDown) {
            mouseDown = false;
            // Don't snap back - stay where it is
            slider.style.transition = 'transform 0.1s ease-out';
            wrapper.style.cursor = 'grab';
        }
    });
    
    // Touch/swipe support for mobile - really fast scrolling
    let startX = 0;
    let startY = 0;
    let isScrolling = false;
    let touchStartTime = 0;
    let isDragging = false;
    let dragStartX = 0;
    let currentDragX = 0;
    
    wrapper.addEventListener('touchstart', (e) => {
        // Don't trigger drag if clicking on a category item link
        const target = e.target;
        const categoryItem = target.closest('.catalog-top-categories__item');
        if (categoryItem) {
            // Allow the link to work normally
            return;
        }
        
        startX = e.touches[0].clientX;
        startY = e.touches[0].clientY;
        isScrolling = false;
        touchStartTime = Date.now();
        isDragging = true;
        dragStartX = startX;
        // Disable transition during touch for instant response
        slider.style.transition = 'none';
    });
    
    wrapper.addEventListener('touchmove', (e) => {
        if (!isScrolling) {
            const deltaX = Math.abs(e.touches[0].clientX - startX);
            const deltaY = Math.abs(e.touches[0].clientY - startY);
            isScrolling = deltaY > deltaX;
        }
        
        if (isDragging && !isScrolling) {
            currentDragX = e.touches[0].clientX;
            const itemWidth = items[0].offsetWidth + 36;
            const dragOffset = (currentDragX - dragStartX) / itemWidth;
            const baseTranslateX = -currentSlide * itemWidth;
            const dragTranslateX = baseTranslateX + (dragOffset * itemWidth);
            slider.style.transform = `translateX(${dragTranslateX}px)`;
        }
        
        // Prevent default scrolling
        e.preventDefault();
    }, { passive: false });
    
    wrapper.addEventListener('touchend', (e) => {
        isDragging = false;
        
        if (!isScrolling) {
            const endX = e.changedTouches[0].clientX;
            const deltaX = startX - endX;
            const itemWidth = items[0].offsetWidth + 36;
            const threshold = 30;
            
            // Calculate how many categories to move based on drag distance
            if (Math.abs(deltaX) > threshold) {
                const categoriesToMove = Math.round(deltaX / itemWidth);
                const newSlide = currentSlide + categoriesToMove;
                const maxSlide = getMaxSlide();
                
                // Clamp to valid range
                currentSlide = Math.max(0, Math.min(newSlide, maxSlide));
                updateSlider();
            }
            // If no significant swipe, stay exactly where it is - no snap back
        }
        
        // Re-enable fast transition
        slider.style.transition = 'transform 0.1s ease-out';
    });
    
    // Handle window resize
    window.addEventListener('resize', () => {
        const newItemsPerView = getItemsPerView();
        if (newItemsPerView !== itemsPerView) {
            itemsPerView = newItemsPerView;
            // Don't reset to 0, keep current position if possible
            const maxSlide = getMaxSlide();
            currentSlide = Math.min(currentSlide, maxSlide);
            updateSlider();
        }
    });
    
    // Initialize
    itemsPerView = getItemsPerView();
    updateSlider();
    
});


