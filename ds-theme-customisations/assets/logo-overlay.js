// JavaScript for Homepage Logo Overlay
// Updated to check plugin directory first, then theme directory

(function() {
    'use strict';

    // Only run on homepage
    if (!document.body.classList.contains('home') && !document.body.classList.contains('front-page')) {
        return;
    }

    let logoOverlay = null;
    let hasInteracted = false;
    const header = document.getElementById('masthead');

    function forceHideHeader() {
        // Hide header immediately
        if (header) {
            header.style.transform = 'translateY(-100%)';
            header.style.opacity = '0';
            header.style.visibility = 'hidden';
            header.style.transition = 'all 0.6s ease-in-out';
            header.classList.add('header-hidden');
        }
    }

    function showHeader() {
        // Show header with slide down animation
        if (header) {
            setTimeout(() => {
                header.style.transform = 'translateY(0)';
                header.style.opacity = '1';
                header.style.visibility = 'visible';
                header.classList.remove('header-hidden');
            }, 300); // Small delay after logo starts shrinking
        }
    }

    function createLogoOverlay() {
        // Get mode from PHP data
        const mode = (typeof operaLogoData !== 'undefined' && operaLogoData.mode) 
            ? operaLogoData.mode 
            : 'center';
        
        // Create the overlay container
        logoOverlay = document.createElement('div');
        logoOverlay.className = `opera-logo-overlay opera-logo-${mode}`;
        
        // Create the logo container
        const logoContainer = document.createElement('div');
        logoContainer.className = 'logo-container';
        
        // Create the logo image
        const logoImg = document.createElement('img');
        logoImg.alt = 'Logo';
        logoImg.style.width = '100%';
        logoImg.style.height = 'auto';
        
        // Use the logo URL passed from PHP
        if (typeof operaLogoData !== 'undefined' && operaLogoData.logoUrl) {
            logoImg.src = operaLogoData.logoUrl;
            console.log('Loading logo from:', operaLogoData.logoUrl);
        } else {
            console.error('No logo URL provided from PHP');
            return null;
        }
        
        // Simple error handling
        logoImg.onerror = function() {
            console.error('Logo failed to load from:', logoImg.src);
        };
        
        logoImg.onload = function() {
            console.log('Logo successfully loaded');
        };
        
        logoContainer.appendChild(logoImg);
        logoOverlay.appendChild(logoContainer);
        
        // Add to body
        document.body.appendChild(logoOverlay);
        document.body.classList.add('opera-homepage', `opera-${mode}`);
        
        return logoOverlay;
    }

    function hideLogoOverlay() {
        if (!logoOverlay || hasInteracted) return;
        
        hasInteracted = true;
        logoOverlay.classList.add('hidden');
        document.body.classList.add('logo-hidden');
        
        // Show header after logo starts shrinking
        showHeader();
        
        // Remove overlay after animation completes
        setTimeout(() => {
            if (logoOverlay && logoOverlay.parentNode) {
                logoOverlay.parentNode.removeChild(logoOverlay);
            }
        }, 800); // Match CSS transition duration
    }

    // Initialize when DOM is ready
    function init() {
        // Force hide header immediately
        forceHideHeader();
        
        createLogoOverlay();
        
        // Set up interaction listeners
        const interactionEvents = [
            'click', 
            'touchstart', 
            'keydown', 
            'wheel', 
            'scroll'
        ];
        
        interactionEvents.forEach(event => {
            document.addEventListener(event, hideLogoOverlay, { once: true, passive: true });
        });
        
        // Auto-hide after 8 seconds as fallback
        setTimeout(() => {
            hideLogoOverlay();
        }, 8000);
    }

    // Also try to hide header even before DOM ready
    const earlyHeader = document.getElementById('masthead');
    if (earlyHeader) {
        earlyHeader.style.transform = 'translateY(-100%)';
        earlyHeader.style.opacity = '0';
        earlyHeader.style.visibility = 'hidden';
    }

    // Wait for DOM to be ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();