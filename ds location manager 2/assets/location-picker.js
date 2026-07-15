/**
 * Location Picker Navigation
 * DS Location Manager Plugin
 * 
 * Split-button design:
 * - Click location name → navigates directly to location page
 * - Click dropdown arrow → opens menu to see all locations
 * - Persists user's last selection in localStorage
 */

(function() {
    'use strict';
    
    const STORAGE_KEY = 'yycd_current_location';
    const STORAGE_ID_KEY = 'yycd_current_location_id';
    const STORAGE_URL_KEY = 'yycd_current_location_url';
    
    /**
     * Initialize location picker
     */
    function initLocationPicker() {
        const picker = document.querySelector('[data-location-picker]');
        if (!picker) return;
        
        const link = picker.querySelector('.location-picker__link');
        const currentSpan = picker.querySelector('.location-picker__current');
        const toggleBtn = picker.querySelector('.location-picker__toggle');
        const dropdown = picker.querySelector('.location-picker__dropdown');
        const items = picker.querySelectorAll('.location-picker__item');
        
        if (!link || !currentSpan || !toggleBtn || !dropdown) return;
        
        // Load saved location from localStorage
        loadSavedLocation(link, currentSpan, items);
        
        // Toggle dropdown on arrow button click
        toggleBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            toggleDropdown(picker, toggleBtn);
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!picker.contains(e.target)) {
                closeDropdown(picker, toggleBtn);
            }
        });
        
        // Handle item selection from dropdown
        items.forEach(item => {
            item.addEventListener('click', function(e) {
                // Save the selection before navigating
                const locationName = this.dataset.locationName;
                const locationId = this.dataset.locationId;
                const locationUrl = this.href;
                
                if (locationName) {
                    saveLocation(locationName, locationId, locationUrl);
                }
                // Let the link navigate normally
            });
        });
        
        // Keyboard navigation for toggle button
        toggleBtn.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                toggleDropdown(picker, toggleBtn);
            } else if (e.key === 'Escape') {
                closeDropdown(picker, toggleBtn);
            }
        });
        
        // Arrow key navigation in dropdown
        dropdown.addEventListener('keydown', function(e) {
            if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
                e.preventDefault();
                navigateItems(items, e.key === 'ArrowDown');
            } else if (e.key === 'Escape') {
                closeDropdown(picker, toggleBtn);
                toggleBtn.focus();
            }
        });
    }
    
    /**
     * Toggle dropdown open/close
     */
    function toggleDropdown(picker, toggleBtn) {
        const isOpen = picker.classList.contains('is-open');
        
        if (isOpen) {
            closeDropdown(picker, toggleBtn);
        } else {
            openDropdown(picker, toggleBtn);
        }
    }
    
    /**
     * Open dropdown
     */
    function openDropdown(picker, toggleBtn) {
        picker.classList.add('is-open');
        toggleBtn.setAttribute('aria-expanded', 'true');
        
        // Focus first item
        const firstItem = picker.querySelector('.location-picker__item');
        if (firstItem) {
            setTimeout(() => firstItem.focus(), 10);
        }
    }
    
    /**
     * Close dropdown
     */
    function closeDropdown(picker, toggleBtn) {
        picker.classList.remove('is-open');
        toggleBtn.setAttribute('aria-expanded', 'false');
    }
    
    /**
     * Navigate dropdown items with arrow keys
     */
    function navigateItems(items, moveDown) {
        const currentIndex = Array.from(items).findIndex(item => item === document.activeElement);
        
        let nextIndex;
        if (currentIndex === -1) {
            nextIndex = moveDown ? 0 : items.length - 1;
        } else {
            nextIndex = moveDown ? currentIndex + 1 : currentIndex - 1;
            
            // Wrap around
            if (nextIndex >= items.length) nextIndex = 0;
            if (nextIndex < 0) nextIndex = items.length - 1;
        }
        
        items[nextIndex].focus();
    }
    
    /**
     * Load saved location from localStorage and update the link + text
     */
    function loadSavedLocation(link, currentSpan, items) {
        const savedLocation = localStorage.getItem(STORAGE_KEY);
        const savedLocationId = localStorage.getItem(STORAGE_ID_KEY);
        const savedLocationUrl = localStorage.getItem(STORAGE_URL_KEY);
        
        if (savedLocation && savedLocationUrl) {
            // Update displayed name
            currentSpan.textContent = savedLocation;
            // Update the link href
            link.href = savedLocationUrl;
        }
        
        // Highlight current location in dropdown
        if (savedLocationId) {
            items.forEach(item => {
                if (item.dataset.locationId === savedLocationId) {
                    item.classList.add('is-current');
                    item.setAttribute('aria-current', 'page');
                }
            });
        }
    }
    
    /**
     * Save location selection to localStorage
     */
    function saveLocation(locationName, locationId, locationUrl) {
        try {
            localStorage.setItem(STORAGE_KEY, locationName);
            if (locationId) {
                localStorage.setItem(STORAGE_ID_KEY, locationId);
            }
            if (locationUrl) {
                localStorage.setItem(STORAGE_URL_KEY, locationUrl);
            }
        } catch (e) {
            // localStorage might be disabled
            console.warn('Could not save location preference:', e);
        }
    }
    
    /**
     * Initialize when DOM is ready
     */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initLocationPicker);
    } else {
        initLocationPicker();
    }
    
})();
