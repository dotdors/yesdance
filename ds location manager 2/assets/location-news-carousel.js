/**
 * Location News Carousel Navigation
 * DS Location Manager Plugin
 * 
 * Handles left/right navigation for horizontal scrolling carousel
 */

(function() {
    'use strict';
    
    /**
     * Initialize carousel on DOM ready
     */
    function initCarousels() {
        const carousels = document.querySelectorAll('.ds-news-carousel-wrapper');
        
        carousels.forEach(wrapper => {
            const track = wrapper.querySelector('.ds-news-carousel-track');
            const prevBtn = wrapper.querySelector('.ds-news-carousel-nav--prev');
            const nextBtn = wrapper.querySelector('.ds-news-carousel-nav--next');
            
            if (!track || !prevBtn || !nextBtn) return;
            
            // Calculate scroll amount (one card width + gap)
            const getScrollAmount = () => {
                const card = track.querySelector('.ds-news-card');
                if (!card) return 0;
                
                const cardWidth = card.offsetWidth;
                const gap = parseFloat(getComputedStyle(track).gap) || 24;
                return cardWidth + gap;
            };
            
            // Update button states
            const updateButtons = () => {
                const scrollLeft = track.scrollLeft;
                const maxScroll = track.scrollWidth - track.clientWidth;
                
                // Disable prev if at start
                prevBtn.disabled = scrollLeft <= 0;
                
                // Disable next if at end
                nextBtn.disabled = scrollLeft >= maxScroll - 1; // -1 for rounding
            };
            
            // Scroll prev
            prevBtn.addEventListener('click', () => {
                const scrollAmount = getScrollAmount();
                track.scrollBy({
                    left: -scrollAmount,
                    behavior: 'smooth'
                });
            });
            
            // Scroll next
            nextBtn.addEventListener('click', () => {
                const scrollAmount = getScrollAmount();
                track.scrollBy({
                    left: scrollAmount,
                    behavior: 'smooth'
                });
            });
            
            // Update buttons on scroll
            track.addEventListener('scroll', updateButtons);
            
            // Update buttons on window resize
            window.addEventListener('resize', updateButtons);
            
            // Initial button state
            updateButtons();
            
            // Keyboard navigation
            track.addEventListener('keydown', (e) => {
                if (e.key === 'ArrowLeft') {
                    e.preventDefault();
                    prevBtn.click();
                } else if (e.key === 'ArrowRight') {
                    e.preventDefault();
                    nextBtn.click();
                }
            });
        });
    }
    
    /**
     * Initialize when DOM is ready
     */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initCarousels);
    } else {
        initCarousels();
    }
    
})();
