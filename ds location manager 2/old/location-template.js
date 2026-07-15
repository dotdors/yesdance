/**
 * DS Location Manager - Template JavaScript
 * Initialize Leaflet map for location display
 * 
 * File location: /ds_location_manager_v2/assets/location-template.js
 */

(function($) {
    'use strict';

    /**
     * Initialize location map
     */
    function initLocationMap() {
        const mapContainer = document.getElementById('ds-location-map');
        
        if (!mapContainer) {
            return; // No map container on this page
        }

        // Check if we have location data from PHP
        if (typeof dsLocationData === 'undefined') {
            console.warn('DS Location: No location data available for map');
            return;
        }

        if (!dsLocationData.hasCoordinates) {
            mapContainer.innerHTML = '<p style="padding: 2rem; text-align: center; color: #666;">Map coordinates not set. Please add latitude and longitude in the location editor.</p>';
            return;
        }

        const lat = parseFloat(dsLocationData.latitude);
        const lng = parseFloat(dsLocationData.longitude);
        const name = dsLocationData.name || 'Location';

        // Validate coordinates
        if (isNaN(lat) || isNaN(lng)) {
            console.error('DS Location: Invalid coordinates', {lat, lng});
            mapContainer.innerHTML = '<p style="padding: 2rem; text-align: center; color: #666;">Invalid map coordinates.</p>';
            return;
        }

        // Initialize Leaflet map
        const map = L.map('ds-location-map', {
            center: [lat, lng],
            zoom: 15,
            scrollWheelZoom: false // Disable scroll zoom to prevent accidental zooming
        });

        // Add OpenStreetMap tile layer
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 19
        }).addTo(map);

        // Add marker for location
        const marker = L.marker([lat, lng]).addTo(map);
        
        // Add popup with location name
        marker.bindPopup('<strong>' + name + '</strong>').openPopup();

        // Re-enable scroll zoom on click
        map.on('click', function() {
            map.scrollWheelZoom.enable();
        });

        // Disable scroll zoom when mouse leaves map
        map.on('mouseout', function() {
            map.scrollWheelZoom.disable();
        });
    }

    /**
     * Initialize when document is ready
     */
    $(document).ready(function() {
        // Wait a bit for Leaflet to fully load
        setTimeout(initLocationMap, 100);
    });

})(jQuery);
