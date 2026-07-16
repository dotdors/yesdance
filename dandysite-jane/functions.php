<?php
/**
 * Theme Name: Dandysite Portfolio
 * Description: A minimalist portfolio theme showcasing projects with modern WordPress practices
 * Author: Dandysite
 * Version: 1.0.0
 * Requires at least: 6.0
 * Tested up to: 6.4
 * Requires PHP: 8.0
 * Text Domain: dandysite-portfolio
 * Domain Path: /languages
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Theme constants
define('DSP_THEME_VERSION', '1.0.0');
define('DSP_THEME_DIR', get_template_directory());
define('DSP_THEME_URI', get_template_directory_uri());

/**
 * Theme Setup
 */
function dsp_theme_setup() {
    // Add theme support
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo', [
        'height'      => 100,
        'width'       => 300,
        'flex-height' => true,
        'flex-width'  => true,
    ]);
    add_theme_support('html5', [
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script'
    ]);
    add_theme_support('customize-selective-refresh-widgets');
    add_theme_support('responsive-embeds');
    add_theme_support('wp-block-styles');
    add_theme_support('align-wide');
    add_theme_support('editor-styles');
    
    // Add editor stylesheet
    add_editor_style('assets/css/editor-style.css');
    
    // Register navigation menus
    register_nav_menus([
        'primary' => __('Primary Menu', 'dandysite-portfolio'),
        'footer' => __('Footer Menu', 'dandysite-portfolio'),
    ]);
    
    // Add custom image sizes
    add_image_size('dsp-project-thumb', 400, 300, true);
    add_image_size('dsp-project-large', 800, 600, true);
    
    // Load text domain
    load_theme_textdomain('dandysite-portfolio', DSP_THEME_DIR . '/languages');
}
add_action('after_setup_theme', 'dsp_theme_setup');

/**
 * Enqueue Scripts and Styles
 */
function dsp_enqueue_assets() {
    // Main stylesheet
    wp_enqueue_style(
        'dsp-style',
        get_stylesheet_uri(),
        [],
        DSP_THEME_VERSION
    );
    
    // Main JavaScript
    wp_enqueue_script(
        'dsp-script',
        DSP_THEME_URI . '/assets/js/main.js',
        [],
        DSP_THEME_VERSION,
        true
    );
    
    // Localize script for AJAX if needed
    wp_localize_script('dsp-script', 'dspAjax', [
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('dsp_nonce')
    ]);
}
add_action('wp_enqueue_scripts', 'dsp_enqueue_assets');

/**
 * Register Custom Post Types
 */
function dsp_register_post_types() {
    // Projects CPT
    register_post_type('dsp_project', [
        'labels' => [
            'name' => __('Projects', 'dandysite-portfolio'),
            'singular_name' => __('Project', 'dandysite-portfolio'),
            'menu_name' => __('Projects', 'dandysite-portfolio'),
            'add_new' => __('Add New Project', 'dandysite-portfolio'),
            'add_new_item' => __('Add New Project', 'dandysite-portfolio'),
            'edit_item' => __('Edit Project', 'dandysite-portfolio'),
            'new_item' => __('New Project', 'dandysite-portfolio'),
            'view_item' => __('View Project', 'dandysite-portfolio'),
            'search_items' => __('Search Projects', 'dandysite-portfolio'),
            'not_found' => __('No projects found', 'dandysite-portfolio'),
            'not_found_in_trash' => __('No projects found in trash', 'dandysite-portfolio'),
        ],
        'public' => true,
        'has_archive' => true,
        'rewrite' => ['slug' => 'projects'],
        'supports' => ['title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'],
        'menu_icon' => 'dashicons-portfolio',
        'show_in_rest' => true, // Enable block editor
        'show_in_nav_menus' => true, // This makes it appear in menu editor
        'publicly_queryable' => true,
    ]);
}


/**
 * Register Taxonomies
 */
function dsp_register_taxonomies() {
    // Project Types
    register_taxonomy('dsp_project_type', 'dsp_project', [
        'labels' => [
            'name' => __('Project Types', 'dandysite-portfolio'),
            'singular_name' => __('Project Type', 'dandysite-portfolio'),
            'menu_name' => __('Project Types', 'dandysite-portfolio'),
        ],
        'hierarchical' => true,
        'public' => true,
        'rewrite' => ['slug' => 'project-type'],
        'show_in_rest' => true,
        'show_in_nav_menus' => true, // Enable in nav menus
        'show_admin_column' => true, // Show in admin project list
    ]);
    
    // Add default terms
    if (!term_exists('Art', 'dsp_project_type')) {
        wp_insert_term('Art', 'dsp_project_type');
    }
    if (!term_exists('Games', 'dsp_project_type')) {
        wp_insert_term('Games', 'dsp_project_type');
    }
    if (!term_exists('Other', 'dsp_project_type')) {
        wp_insert_term('Other', 'dsp_project_type');
    }
}


/**
 * Add Custom Fields Meta Box
 */
function dsp_add_project_meta_box() {
    add_meta_box(
        'dsp_project_details',
        __('Project Details', 'dandysite-portfolio'),
        'dsp_project_meta_box_callback',
        'dsp_project',
        'normal',
        'high'
    );
}


/**
 * Meta Box Callback
 */
function dsp_project_meta_box_callback($post) {
    wp_nonce_field('dsp_save_project_meta', 'dsp_project_meta_nonce');
    
    $date = get_post_meta($post->ID, 'dsp_project_date', true);
    $platform = get_post_meta($post->ID, 'dsp_project_platform', true);
    
    echo '<table class="form-table">';
    echo '<tr>';
    echo '<th><label for="dsp_project_date">' . __('Project Date', 'dandysite-portfolio') . '</label></th>';
    echo '<td><input type="date" id="dsp_project_date" name="dsp_project_date" value="' . esc_attr($date) . '" /></td>';
    echo '</tr>';
    echo '<tr>';
    echo '<th><label for="dsp_project_platform">' . __('Platform', 'dandysite-portfolio') . '</label></th>';
    echo '<td><input type="text" id="dsp_project_platform" name="dsp_project_platform" value="' . esc_attr($platform) . '" class="regular-text" /></td>';
    echo '</tr>';
    echo '</table>';
}

/**
 * Save Meta Box Data
 */
function dsp_save_project_meta($post_id) {
    if (!isset($_POST['dsp_project_meta_nonce']) || 
        !wp_verify_nonce($_POST['dsp_project_meta_nonce'], 'dsp_save_project_meta')) {
        return;
    }
    
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    if (isset($_POST['dsp_project_date'])) {
        update_post_meta($post_id, 'dsp_project_date', sanitize_text_field($_POST['dsp_project_date']));
    }
    
    if (isset($_POST['dsp_project_platform'])) {
        update_post_meta($post_id, 'dsp_project_platform', sanitize_text_field($_POST['dsp_project_platform']));
    }
}
add_action('save_post', 'dsp_save_project_meta');
/**
 * Fallback menu for when no menu is assigned
 */
function dsp_fallback_menu() {
    $pages = get_pages();
    if ($pages) {
        echo '<ul id="primary-menu" class="menu">';
        echo '<li><a href="' . home_url('/') . '">' . __('Home', 'dandysite-portfolio') . '</a></li>';
        
        // Only add projects if enabled
        if (dsp_projects_enabled()) {
            $projects_link = get_post_type_archive_link('dsp_project');
            if ($projects_link) {
                echo '<li><a href="' . $projects_link . '">' . __('Projects', 'dandysite-portfolio') . '</a></li>';
            }
        }
        
        // Add other pages
        foreach ($pages as $page) {
            if ($page->post_title !== 'Home') {
                echo '<li><a href="' . get_permalink($page->ID) . '">' . $page->post_title . '</a></li>';
            }
        }
        echo '</ul>';
    }
}
/**
 * Add featured project meta box
 */
function dsp_add_featured_meta_box() {
    add_meta_box(
        'dsp_featured_project',
        __('Featured Project', 'dandysite-portfolio'),
        'dsp_featured_meta_box_callback',
        'dsp_project',
        'side',
        'high'
    );
}


/**
 * Featured project meta box callback
 */
function dsp_featured_meta_box_callback($post) {
    wp_nonce_field('dsp_save_featured_meta', 'dsp_featured_meta_nonce');
    
    $featured = get_post_meta($post->ID, 'dsp_featured', true);
    
    echo '<label>';
    echo '<input type="checkbox" name="dsp_featured" value="1"' . checked($featured, '1', false) . '>';
    echo ' ' . __('Feature this project on the homepage', 'dandysite-portfolio');
    echo '</label>';
}

/**
 * Save featured project meta
 */
function dsp_save_featured_meta($post_id) {
    if (!isset($_POST['dsp_featured_meta_nonce']) || 
        !wp_verify_nonce($_POST['dsp_featured_meta_nonce'], 'dsp_save_featured_meta')) {
        return;
    }
    
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    if (isset($_POST['dsp_featured'])) {
        update_post_meta($post_id, 'dsp_featured', '1');
    } else {
        delete_post_meta($post_id, 'dsp_featured');
    }
}
add_action('save_post', 'dsp_save_featured_meta');

/**
 * Customize excerpt length
 */
function dsp_excerpt_length($length) {
    return 25;
}
add_filter('excerpt_length', 'dsp_excerpt_length');

/**
 * Custom excerpt more
 */
function dsp_excerpt_more($more) {
    return '...';
}
add_filter('excerpt_more', 'dsp_excerpt_more');
/**
 * Add body classes based on page type
 */
function dsp_body_classes($classes) {
    if (dsp_projects_enabled()) {
        if (is_post_type_archive('dsp_project') || is_tax('dsp_project_type')) {
            $classes[] = 'projects-archive';
        }
        
        if (is_singular('dsp_project')) {
            $classes[] = 'single-project';
        }
    }
    
    return $classes;
}
add_filter('body_class', 'dsp_body_classes');

/**
 * Modify archive titles
 */
function dsp_archive_title($title) {
    if (is_post_type_archive('dsp_project')) {
        $title = __('Projects', 'dandysite-portfolio');
    } elseif (is_tax('dsp_project_type')) {
        $title = single_term_title('', false) . ' ' . __('Projects', 'dandysite-portfolio');
    }
    
    return $title;
}
add_filter('get_the_archive_title', 'dsp_archive_title');

/**
 * Add theme support for editor color palette
 */
function dsp_editor_color_palette() {
    add_theme_support('editor-color-palette', [
        [
            'name' => __('Primary Black', 'dandysite-portfolio'),
            'slug' => 'primary-black',
            'color' => '#000000',
        ],
        [
            'name' => __('White', 'dandysite-portfolio'),
            'slug' => 'white',
            'color' => '#ffffff',
        ],
        [
            'name' => __('Accent Orange', 'dandysite-portfolio'),
            'slug' => 'accent-orange',
            'color' => '#ff6b35',
        ],
        [
            'name' => __('Text Gray', 'dandysite-portfolio'),
            'slug' => 'text-gray',
            'color' => '#333333',
        ],
        [
            'name' => __('Light Gray', 'dandysite-portfolio'),
            'slug' => 'light-gray',
            'color' => '#f8f8f8',
        ],
    ]);
}
add_action('after_setup_theme', 'dsp_editor_color_palette');

/**
 * Security: Remove WordPress version from head
 */
remove_action('wp_head', 'wp_generator');

/**
 * Custom Logo Function with SVG Support
 */
function dsp_get_custom_logo() {
    // Check for custom uploaded logo first
    if (has_custom_logo()) {
        $custom_logo_id = get_theme_mod('custom_logo');
        $logo_url = wp_get_attachment_image_url($custom_logo_id, 'full');
        $logo_alt = get_post_meta($custom_logo_id, '_wp_attachment_image_alt', true);
        
        if (empty($logo_alt)) {
            $logo_alt = get_bloginfo('name', 'display');
        }
        
        return '<img src="' . esc_url($logo_url) . '" alt="' . esc_attr($logo_alt) . '" class="custom-logo">';
    }
    
    // Check for manual SVG logo in theme directory
    $svg_logo_path = get_template_directory() . '/assets/images/logo.svg';
    $svg_logo_url = get_template_directory_uri() . '/assets/images/logo.svg';
    
    if (file_exists($svg_logo_path)) {
        return '<img src="' . esc_url($svg_logo_url) . '" alt="' . esc_attr(get_bloginfo('name')) . '" class="custom-logo svg-logo">';
    }
    
    // Check for fallback PNG/JPG logos
    $fallback_extensions = ['png', 'jpg', 'jpeg'];
    foreach ($fallback_extensions as $ext) {
        $fallback_path = get_template_directory() . '/assets/images/logo.' . $ext;
        $fallback_url = get_template_directory_uri() . '/assets/images/logo.' . $ext;
        
        if (file_exists($fallback_path)) {
            return '<img src="' . esc_url($fallback_url) . '" alt="' . esc_attr(get_bloginfo('name')) . '" class="custom-logo fallback-logo">';
        }
    }
    
    // No logo found, return site title
    return false;
}

/**
 * Display Custom Logo or Site Title
 */
function dsp_display_logo() {
    $logo = dsp_get_custom_logo();
    
    if ($logo) {
        echo '<a href="' . esc_url(home_url('/')) . '" rel="home" class="custom-logo-link">';
        echo $logo;
        echo '</a>';
    } else {
        echo '<h1 class="site-title">';
        echo '<a href="' . esc_url(home_url('/')) . '" rel="home">';
        bloginfo('name');
        echo '</a>';
        echo '</h1>';
    }
}
function dsp_clean_head() {
    remove_action('wp_head', 'rsd_link');
    remove_action('wp_head', 'wlwmanifest_link');
    remove_action('wp_head', 'wp_shortlink_wp_head');
}
add_action('init', 'dsp_clean_head');

// Add this to functions.php

/**
 * Add theme settings page
 */
function dsp_add_theme_settings() {
    add_theme_page(
        'Theme Settings',
        'Theme Settings', 
        'manage_options',
        'dsp-theme-settings',
        'dsp_theme_settings_page'
    );
}
add_action('admin_menu', 'dsp_add_theme_settings');

/**
 * Register theme settings
 */
function dsp_register_theme_settings() {
    register_setting('dsp_theme_settings', 'dsp_enable_projects', [
        'type' => 'boolean',
        'default' => false,
        'sanitize_callback' => 'rest_sanitize_boolean'
    ]);
    
    add_settings_section(
        'dsp_features_section',
        'Theme Features',
        'dsp_features_section_callback',
        'dsp-theme-settings'
    );
    
    add_settings_field(
        'dsp_enable_projects',
        'Enable Projects',
        'dsp_enable_projects_callback',
        'dsp-theme-settings',
        'dsp_features_section'
    );
}
add_action('admin_init', 'dsp_register_theme_settings');

/**
 * Settings page content
 */
function dsp_theme_settings_page() {
    ?>
    <div class="wrap">
        <h1>Dandysite Theme Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('dsp_theme_settings');
            do_settings_sections('dsp-theme-settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

/**
 * Section callback
 */
function dsp_features_section_callback() {
    echo '<p>Enable or disable theme features as needed.</p>';
}

/**
 * Projects enable field
 */
function dsp_enable_projects_callback() {
    $enabled = get_option('dsp_enable_projects', false);
    ?>
    <label>
        <input type="checkbox" name="dsp_enable_projects" value="1" <?php checked($enabled); ?> />
        Enable Projects functionality (portfolio, custom post types, project pages)
    </label>
    <p class="description">When enabled, adds Projects menu item, custom post types, and portfolio templates.</p>
    <?php
}

/**
 * Check if projects are enabled
 */
function dsp_projects_enabled() {
    return get_option('dsp_enable_projects', false);
}

/**
 * Conditional project registration
 */
function dsp_maybe_register_projects() {
    if (dsp_projects_enabled()) {
        dsp_register_post_types();
        dsp_register_taxonomies();
    }
}
add_action('init', 'dsp_maybe_register_projects');

/**
 * Conditional project meta boxes
 */
function dsp_maybe_add_project_meta_boxes() {
    if (dsp_projects_enabled()) {
        dsp_add_project_meta_box();
        dsp_add_featured_meta_box();
    }
}
add_action('add_meta_boxes', 'dsp_maybe_add_project_meta_boxes');



/**
 * Flush rewrite rules when projects setting changes
 */
function dsp_projects_setting_updated($old_value, $value) {
    flush_rewrite_rules();
}
add_action('update_option_dsp_enable_projects', 'dsp_projects_setting_updated', 10, 2);


// ===== DEVELOPMENT & DEBUGGING =====
// Uncomment the line below during development/debugging, comment out for production
// include_once get_template_directory() . '/debug-helper.php';

/**
 * Register Footer Widget Area
 * Uses block-based widgets (WP 5.8+)
 */
function jane_register_footer_widgets() {
    register_sidebar(array(
        'name'          => __('Footer Widgets', 'dandysite-jane'),
        'id'            => 'footer-widgets',
        'description'   => __('Add widgets or blocks here. They will auto-flow into columns.', 'dandysite-jane'),
        'before_widget' => '<div class="footer-widget">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4 class="footer-widget__title">',
        'after_title'   => '</h4>',
    ));
}
add_action('widgets_init', 'jane_register_footer_widgets');

/**
 * Register Footer Menu Location
 */
function jane_register_footer_menu() {
    register_nav_menu('footer', __('Footer Menu', 'dandysite-jane'));
}
add_action('after_setup_theme', 'jane_register_footer_menu');






