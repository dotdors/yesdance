<?php
/**
 * Dandysite Scroll Child Theme Functions
 * 
 * @author NC Dorsner
 * @link https://dandysite.com
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enqueue parent theme styles and child theme assets
 */
add_action('wp_enqueue_scripts', 'dandysite_scroll_enqueue_styles');
function dandysite_scroll_enqueue_styles() {
    // Enqueue parent theme styles
    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');
    
    // Enqueue child theme styles
    wp_enqueue_style('child-style', get_stylesheet_uri());
    
    // Enqueue compiled LESS styles
    wp_enqueue_style(
        'scroll-animations', 
        get_stylesheet_directory_uri() . '/assets/css/custom.css',
        ['parent-style'],
        filemtime(get_stylesheet_directory() . '/assets/css/custom.css')
    );
    
    // Enqueue scroll controller JavaScript
    wp_enqueue_script(
        'scroll-controller',
        get_stylesheet_directory_uri() . '/assets/js/scroll-controller.js',
        [],
        filemtime(get_stylesheet_directory() . '/assets/js/scroll-controller.js'),
        true
    );
}

/**
 * Enhance group blocks for scroll sections
 */
add_filter('render_block', 'enhance_section_blocks', 10, 2);
function enhance_section_blocks($block_content, $block) {
    if ($block['blockName'] !== 'core/group') {
        return $block_content;
    }
    
    $classes = $block['attrs']['className'] ?? '';
    
    // Convert group blocks with 'full-section' class to sections
    if (strpos($classes, 'full-section') !== false) {
        // Add section class while preserving existing classes
        $block_content = str_replace(
            '<div class=',
            '<div class="section ',
            $block_content
        );
        
        // Extract animation type from class name (e.g., 'anim-parallax')
        if (preg_match('/anim-(\w+)/', $classes, $matches)) {
            $animType = $matches[1];
            $block_content = str_replace(
                '<div class="section',
                '<div data-anim="' . esc_attr($animType) . '" class="section',
                $block_content
            );
        }
    }
    
    return $block_content;
}

/**
 * Add page container wrapper for scroll snap
 */
add_action('wp_footer', 'add_scroll_container_script');
function add_scroll_container_script() {
    if (!is_page()) return;
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const body = document.body;
        if (body.classList.contains('page-template-default') || body.classList.contains('page')) {
            body.classList.add('scroll-page');
        }
    });
    </script>
    <?php
}