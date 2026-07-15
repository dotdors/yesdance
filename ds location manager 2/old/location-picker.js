/**
 * Location Picker Navigation
 * DS Location Manager Plugin
 * 
 * Handles dropdown functionality and localStorage persistence
 * Shows user's last-selected location in the nav button
 */

(function() {
    'use strict';
    
    const STORAGE_KEY = 'yycd_current_location';
    const STORAGE_ID_KEY = 'yycd_current_location_id';
    
    /**
     * Initialize location picker
     */
    function initLocationPicker() {
        const picker = document.querySelector('[data-location-picker]');
        if (!picker) return;
        
        const button = picker.querySelector('.location-picker__button');
        const currentSpan = picker.querySelector('.location-picker__current');
        const dropdown = picker.querySelector('.location-picker__dropdown');
        const items = picker.querySelectorAll('.location-picker__item');
        
        if (!button || !currentSpan || !dropdown) return;
        
        // Load saved location from localStorage
        loadSavedLocation(currentSpan, items);
        
        // Toggle dropdown on button click
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            toggleDropdown(picker, button);
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!picker.contains(e.target)) {
                closeDropdown(picker, button);
            }
        });
        
        // Handle item selection
        items.forEach(item => {
            item.addEventListener('click', function(e) {
                // Don't prevent default - let it navigate
                // But save the selection first
                const locationName = this.dataset.locationName;
                const locationId = this.dataset.locationId;
                
                if (locationName) {
                    saveLocation(locationName, locationId);
                }
            });
        });
        
        // Keyboard navigation
        button.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                toggleDropdown(picker, button);
            } else if (e.key === 'Escape') {
                closeDropdown(picker, button);
            }
        });
        
        // Arrow key navigation in dropdown
        dropdown.addEventListener('keydown', function(e) {
            if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
                e.preventDefault();
                navigateItems(items, e.key === 'ArrowDown');
            } else if (e.key === 'Escape') {
                closeDropdown(picker, button);
                button.focus();
            }
        });
    }
    
    /**
     * Toggle dropdown open/close
     */
    function toggleDropdown(picker, button) {
        const isOpen = picker.classList.contains('is-open');
        
        if (isOpen) {
            closeDropdown(picker, button);
        } else {
            openDropdown(picker, button);
        }
    }
    
    /**
     * Open dropdown
     */
    function openDropdown(picker, button) {
        picker.classList.add('is-open');
        button.setAttribute('aria-expanded', 'true');
        
        // Focus first item
        const firstItem = picker.querySelector('.location-picker__item');
        if (firstItem) {
            setTimeout(() => firstItem.focus(), 10);
        }
    }
    
    /**
     * Close dropdown
     */
    function closeDropdown(picker, button) {
        picker.classList.remove('is-open');
        button.setAttribute('aria-expanded', 'false');
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
     * Load saved location from localStorage
     */
    function loadSavedLocation(currentSpan, items) {
        const savedLocation = localStorage.getItem(STORAGE_KEY);
        const savedLocationId = localStorage.getItem(STORAGE_ID_KEY);
        
        if (savedLocation) {
            currentSpan.textContent = savedLocation;
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
    function saveLocation(locationName, locationId) {
        try {
            localStorage.setItem(STORAGE_KEY, locationName);
            if (locationId) {
                localStorage.setItem(STORAGE_ID_KEY, locationId);
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
