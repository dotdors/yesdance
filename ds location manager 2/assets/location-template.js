/**
 * DS Location Manager - Template JavaScript
 * Initialize Leaflet map and hero curve for location display
 * 
 * File location: /ds_location_manager_v2/assets/location-template.js
 */

(function($) {
    'use strict';

    /**
     * Generate SVG path for hero curve based on CSS custom properties
     * 
     * CSS Variables:
     * --hero-curve-start: Where curve begins on edges (0=top, 600=bottom of viewBox)
     * --hero-curve-depth: How much it dips (positive=down, negative=up)
     * 
     * Examples:
     *   start: 330, depth: 80  → curve dips DOWN (cream bulges down)
     *   start: 330, depth: -80 → curve dips UP (cream bulges up)
     *   start: 400, depth: 0   → straight horizontal line
     */
    function initHeroCurve() {
        const hero = document.querySelector('.ds-location-hero');
        const curvePath = document.querySelector('.ds-location-hero__overlay-shape');
        
        if (!hero || !curvePath) {
            return;
        }
        
        const styles = getComputedStyle(hero);
        const startY = parseFloat(styles.getPropertyValue('--hero-curve-start')) || 330;
        const depth = parseFloat(styles.getPropertyValue('--hero-curve-depth')) || 80;
        
        // Calculate control point (middle of curve)
        const controlY = startY + depth;
        
        // ViewBox is 1200 x 600
        // Path: start at left edge, curve across top, then fill to bottom
        const path = `M 0,${startY} Q 600,${controlY} 1200,${startY} L 1200,600 L 0,600 Z`;
        
        curvePath.setAttribute('d', path);
    }

    /**
     * Initialize location map
     */
    function initLocationMap() {
        const mapContainer = document.getElementById('ds-location-map');
        
        if (!mapContainer) {
            return;
        }

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

        if (isNaN(lat) || isNaN(lng)) {
            console.error('DS Location: Invalid coordinates', {lat, lng});
            mapContainer.innerHTML = '<p style="padding: 2rem; text-align: center; color: #666;">Invalid map coordinates.</p>';
            return;
        }

        const map = L.map('ds-location-map', {
            center: [lat, lng],
            zoom: 15,
            scrollWheelZoom: false
        });

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 19
        }).addTo(map);

        const marker = L.marker([lat, lng]).addTo(map);
        marker.bindPopup('<strong>' + name + '</strong>').openPopup();

        map.on('click', function() {
            map.scrollWheelZoom.enable();
        });

        map.on('mouseout', function() {
            map.scrollWheelZoom.disable();
        });
    }

    /**
     * Initialize when document is ready
     */
    $(document).ready(function() {
        initHeroCurve();
        setTimeout(initLocationMap, 100);
    });

})(jQuery);
