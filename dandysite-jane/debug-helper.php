<?php
/**
 * Debug Helper Functions
 * 
 * Create this file: /wp-content/themes/dandysite-portfolio/debug-helper.php
 * Only include when needed for debugging
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Debug post type registration
 * Usage: Add ?debug_projects=1 to any URL
 */
function dsp_debug_post_type() {
    if (current_user_can('manage_options') && isset($_GET['debug_projects'])) {
        $post_type = get_post_type_object('dsp_project');
        echo '<pre style="background: #f0f0f0; padding: 20px; margin: 20px;">';
        echo "=== POST TYPE DEBUG ===\n";
        echo "Post type exists: " . (post_type_exists('dsp_project') ? 'YES' : 'NO') . "\n";
        echo "Show in nav menus: " . ($post_type->show_in_nav_menus ? 'YES' : 'NO') . "\n";
        echo "Has archive: " . ($post_type->has_archive ? 'YES' : 'NO') . "\n";
        echo "Public: " . ($post_type->public ? 'YES' : 'NO') . "\n";
        echo "Archive link: " . get_post_type_archive_link('dsp_project') . "\n";
        echo "Rewrite slug: " . ($post_type->rewrite['slug'] ?? 'Not set') . "\n";
        echo '</pre>';
        
        flush_rewrite_rules();
        echo '<p style="color: green; margin: 20px;">✅ Rewrite rules flushed!</p>';
        
        wp_die();
    }
}
add_action('wp_loaded', 'dsp_debug_post_type');

/**
 * Debug menu items
 * Usage: Add ?debug_menus=1 to any URL  
 */
function dsp_debug_menus() {
    if (current_user_can('manage_options') && isset($_GET['debug_menus'])) {
        $menus = wp_get_nav_menus();
        echo '<pre style="background: #f0f0f0; padding: 20px; margin: 20px;">';
        echo "=== MENU DEBUG ===\n";
        
        foreach ($menus as $menu) {
            echo "Menu: " . $menu->name . " (ID: " . $menu->term_id . ")\n";
            $items = wp_get_nav_menu_items($menu->term_id);
            if ($items) {
                foreach ($items as $item) {
                    echo "  - " . $item->title . " (" . $item->url . ")\n";
                }
            }
            echo "\n";
        }
        echo '</pre>';
        wp_die();
    }
}
add_action('wp_loaded', 'dsp_debug_menus');

/**
 * Debug custom fields
 * Usage: Add ?debug_fields=POST_ID to any URL
 */
function dsp_debug_custom_fields() {
    if (current_user_can('manage_options') && isset($_GET['debug_fields'])) {
        $post_id = intval($_GET['debug_fields']);
        $post = get_post($post_id);
        
        if ($post) {
            $meta = get_post_meta($post_id);
            echo '<pre style="background: #f0f0f0; padding: 20px; margin: 20px;">';
            echo "=== CUSTOM FIELDS DEBUG ===\n";
            echo "Post: " . $post->post_title . " (ID: $post_id)\n\n";
            
            foreach ($meta as $key => $values) {
                echo "$key: " . print_r($values, true) . "\n";
            }
            echo '</pre>';
        } else {
            echo '<p style="color: red; margin: 20px;">❌ Post not found</p>';
        }
        wp_die();
    }
}
add_action('wp_loaded', 'dsp_debug_custom_fields');

/**
 * Debug theme info
 * Usage: Add ?debug_theme=1 to any URL
 */
function dsp_debug_theme_info() {
    if (current_user_can('manage_options') && isset($_GET['debug_theme'])) {
        echo '<pre style="background: #f0f0f0; padding: 20px; margin: 20px;">';
        echo "=== THEME DEBUG ===\n";
        echo "Theme: " . get_template() . "\n";
        echo "Theme URI: " . get_template_directory_uri() . "\n";
        echo "Theme Dir: " . get_template_directory() . "\n";
        echo "Child theme: " . (is_child_theme() ? 'YES' : 'NO') . "\n";
        echo "WordPress version: " . get_bloginfo('version') . "\n";
        echo "PHP version: " . PHP_VERSION . "\n";
        echo '</pre>';
        wp_die();
    }
}
add_action('wp_loaded', 'dsp_debug_theme_info');