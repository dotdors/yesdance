<?php
/**
 * YYCD Child Theme Functions
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enqueue parent and child theme styles
 */
function yycd_child_enqueue_styles() {
    // Enqueue parent theme styles
    wp_enqueue_style(
        'parent-style',
        get_template_directory_uri() . '/style.css',
        [],
        wp_get_theme()->get('Version')
    );
    
    // Enqueue child theme styles
    wp_enqueue_style(
        'child-style',
        get_stylesheet_directory_uri() . '/style.css',
        ['parent-style'],
        wp_get_theme()->get('Version')
    );
}
add_action('wp_enqueue_scripts', 'yycd_child_enqueue_styles');

/**
 * Enqueue front-page specific scripts
 */
function yycd_homepage_scripts() {
    if (is_front_page()) {
        wp_enqueue_script(
            'yycd-homepage',
            get_stylesheet_directory_uri() . '/assets/homepage.js',
            ['jquery'],
            wp_get_theme()->get('Version'),
            true
        );
    }
}
add_action('wp_enqueue_scripts', 'yycd_homepage_scripts');

/**
 * Get inline SVG for header logo (small version)
 * Returns inline SVG with CSS-controllable fills
 */
function dsp_get_header_logo_svg() {
    $svg = '<svg class="header-logo-svg" width="186" height="175" viewBox="0 0 186 175" fill="none" xmlns="http://www.w3.org/2000/svg" aria-label="' . esc_attr(get_bloginfo('name')) . '">
        <path fill-rule="evenodd" clip-rule="evenodd" d="M6.43865 87.2834C15.8379 90.3997 23.9757 94.7765 33.257 98.0108C24.7159 101.984 16.0482 105.833 6.43865 108.738C7.39445 105.403 10.0226 103.74 10.7296 100.156C9.2417 97.7104 3.26336 99.7561 0.00225167 99.0835C-0.150076 94.6402 7.46633 97.9657 10.7296 96.938C10.4732 92.5452 7.08551 91.2847 6.43865 87.2834Z"/>
        <path fill-rule="evenodd" clip-rule="evenodd" d="M185.585 94.7898C186.462 98.2547 177.488 97.3064 178.076 96.9352C177.621 93.8929 185.363 93.6087 185.585 94.7898Z"/>
        <path fill-rule="evenodd" clip-rule="evenodd" d="M169.496 95.8652C169.556 99.859 164.723 98.9589 160.914 99.0834C160.197 94.4331 165.893 96.1956 169.496 95.8652Z"/>
        <path fill-rule="evenodd" clip-rule="evenodd" d="M137.312 95.864C127.021 95.4284 120.166 91.5559 111.567 89.4276C117.252 78.2014 111.043 65.3436 111.567 51.8819C112.308 32.813 130.103 3.31071 142.676 0.390733C154.136 -2.26964 159.542 9.14745 160.913 19.6999C164.957 50.8231 151.731 79.8212 137.312 95.864Z"/>
        <path fill-rule="evenodd" clip-rule="evenodd" d="M133.022 106.59C127.297 122.096 118.254 148.569 95.4768 136.626C88.7465 120.676 100.544 107.366 106.204 96.9351C113.267 102.031 122.84 104.615 133.022 106.59Z"/>
        <path fill-rule="evenodd" clip-rule="evenodd" d="M81.5307 122.682C74.5354 124.282 63.4358 127.455 55.7851 124.828C39.8711 108.609 31.8921 37.2821 59.0033 29.3546C73.4069 25.143 88.571 60.8007 89.0398 77.6276C89.558 96.2706 77.8759 105.763 81.5307 122.682Z"/>
        <path fill-rule="evenodd" clip-rule="evenodd" d="M57.9314 136.627C69.1125 137.081 77.0293 134.271 85.8224 132.336C90.5457 142.793 99.2563 165.6 86.8952 174.173C62.291 176.608 62.0904 154.639 57.9314 136.627Z"/>
    </svg>';
    
    return $svg;
}

/**
 * Display header logo - uses inline SVG for header, keeps existing logo system
 * This replaces dsp_display_logo() call in header.php only
 */
function dsp_display_header_logo() {
    echo '<a href="' . esc_url(home_url('/')) . '" rel="home" class="header-logo-link">';
    echo dsp_get_header_logo_svg();
    echo '</a>';
}