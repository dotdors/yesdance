/**
 * Dandysite Portfolio - Main JavaScript
 * Minimal, modern JavaScript for enhanced functionality
 */

(function() {
    'use strict';

    // DOM ready function
    function ready(fn) {
        if (document.readyState !== 'loading') {
            fn();
        } else {
            document.addEventListener('DOMContentLoaded', fn);
        }
    }

    // Smooth scrolling for anchor links
    function initSmoothScrolling() {
        const links = document.querySelectorAll('a[href^="#"]');
        
        links.forEach(link => {
            link.addEventListener('click', function(e) {
                const targetId = this.getAttribute('href');
                const targetElement = document.querySelector(targetId);
                
                if (targetElement) {
                    e.preventDefault();
                    targetElement.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    }

    // Mobile menu toggle (enhanced)
    function initMobileMenu() {
        const menuToggle = document.querySelector('.mobile-menu-toggle');
        const navigation = document.querySelector('.main-navigation');
        const body = document.body;
        
        if (menuToggle && navigation) {
            menuToggle.addEventListener('click', function() {
                const isOpen = navigation.classList.contains('mobile-menu-open');
                
                if (isOpen) {
                    // Close menu
                    navigation.classList.remove('mobile-menu-open');
                    menuToggle.classList.remove('active');
                    body.classList.remove('mobile-menu-open');
                    menuToggle.setAttribute('aria-expanded', 'false');
                } else {
                    // Open menu
                    navigation.classList.add('mobile-menu-open');
                    menuToggle.classList.add('active');
                    body.classList.add('mobile-menu-open');
                    menuToggle.setAttribute('aria-expanded', 'true');
                }
            });
            
            // Close menu when clicking on a link
            const menuLinks = navigation.querySelectorAll('a');
            menuLinks.forEach(link => {
                link.addEventListener('click', () => {
                    navigation.classList.remove('mobile-menu-open');
                    menuToggle.classList.remove('active');
                    body.classList.remove('mobile-menu-open');
                    menuToggle.setAttribute('aria-expanded', 'false');
                });
            });
            
            // Close menu on escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && navigation.classList.contains('mobile-menu-open')) {
                    navigation.classList.remove('mobile-menu-open');
                    menuToggle.classList.remove('active');
                    body.classList.remove('mobile-menu-open');
                    menuToggle.setAttribute('aria-expanded', 'false');
                }
            });
            
            // Close menu on window resize if desktop
            window.addEventListener('resize', function() {
                if (window.innerWidth > 768 && navigation.classList.contains('mobile-menu-open')) {
                    navigation.classList.remove('mobile-menu-open');
                    menuToggle.classList.remove('active');
                    body.classList.remove('mobile-menu-open');
                    menuToggle.setAttribute('aria-expanded', 'false');
                }
            });
        }
    }

    // Lazy loading for images (modern browsers)
    function initLazyLoading() {
        if ('IntersectionObserver' in window) {
            const images = document.querySelectorAll('img[loading="lazy"]');
            
            images.forEach(img => {
                if (img.complete) {
                    img.classList.add('loaded');
                } else {
                    img.addEventListener('load', () => {
                        img.classList.add('loaded');
                    });
                }
            });
        }
    }

    // Project filter functionality (if using AJAX)
    function initProjectFilters() {
        const filterButtons = document.querySelectorAll('.filter-btn');
        
        filterButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                // Remove active class from all buttons
                filterButtons.forEach(btn => btn.classList.remove('active'));
                // Add active class to clicked button
                this.classList.add('active');
                
                // Here you could add AJAX functionality if needed
                // For now, we're using standard WordPress navigation
            });
        });
    }

    // Add scroll-based header styling
    function initScrollHeader() {
        const header = document.querySelector('.site-header');
        let lastScrollY = window.scrollY;
        
        if (header) {
            window.addEventListener('scroll', () => {
                const currentScrollY = window.scrollY;
                
                if (currentScrollY > 100) {
                    header.classList.add('scrolled');
                } else {
                    header.classList.remove('scrolled');
                }
                
                // Hide/show header on scroll (optional)
                if (currentScrollY > lastScrollY && currentScrollY > 200) {
                    header.classList.add('header-hidden');
                } else {
                    header.classList.remove('header-hidden');
                }
                
                lastScrollY = currentScrollY;
            });
        }
    }

    // Initialize all functions
    ready(function() {
        initSmoothScrolling();
        initMobileMenu();
        initLazyLoading();
        initProjectFilters();
        initScrollHeader();
        
        // Add loaded class to body for CSS animations
        document.body.classList.add('loaded');
        
        console.log('Dandysite Portfolio theme loaded successfully!');
    });

})();
