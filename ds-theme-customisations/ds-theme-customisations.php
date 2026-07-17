<?php
/**
 * Plugin Name: DS Theme Customizations
 * Description: Site-specific customizations and overrides
 * Version: 3.1.0
 * Author: Dabbled Studios
 * Author URI: https://dabbledstudios.com/
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main Theme Customizations Class
 */
final class DS_Theme_Customizations {

    /**
     * Plugin version
     */
    const VERSION = '3.1.0';

    /**
     * Plugin directory path
     */
    private $plugin_path;

    /**
     * Plugin directory URL
     */
    private $plugin_url;

    /**
     * Set up the plugin
     */
    public function __construct() {
        $this->plugin_path = plugin_dir_path(__FILE__);
        $this->plugin_url = plugin_dir_url(__FILE__);
        
        add_action('init', [$this, 'init'], -1);
        
        // Load custom functions if file exists
        $functions_file = $this->plugin_path . 'includes/functions.php';
        if (file_exists($functions_file)) {
            require_once $functions_file;
        }
    }

    /**
     * Initialize plugin
     */
    public function init() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_styles'], 999);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_filter('template_include', [$this, 'load_custom_templates'], 11);
        
        // Only add WooCommerce filter if WooCommerce is active
        if (class_exists('WooCommerce')) {
            add_filter('wc_get_template', [$this, 'load_wc_templates'], 11, 5);
        }
    }

    /**
     * Enqueue custom styles
     */
    public function enqueue_styles() {
        $css_file = $this->plugin_path . 'assets/plugin-style.css';
        
        if (file_exists($css_file)) {
            wp_enqueue_style(
                'ds-custom-styles',
                $this->plugin_url . 'assets/plugin-style.css',
                ['dsp-style'], // Ensure it loads after theme styles
                filemtime($css_file)
            );
        }
    }

    /**
     * Enqueue custom scripts
     */
    public function enqueue_scripts() {
        // Main custom JS
        $js_file = $this->plugin_path . 'assets/custom.js';
        if (file_exists($js_file)) {
            wp_enqueue_script(
                'ds-custom-js',
                $this->plugin_url . 'assets/custom.js',
                ['jquery'],
                filemtime($js_file),
                true
            );
        }

        // Homepage-specific JS
        if (is_front_page()) {
            $home_js = $this->plugin_path . 'assets/home.js';
            if (file_exists($home_js)) {
                wp_enqueue_script(
                    'ds-home-js',
                    $this->plugin_url . 'assets/home.js',
                    ['jquery'],
                    filemtime($home_js),
                    true
                );
            }
        }
    }

    /**
     * Load custom templates
     */
    public function load_custom_templates($template) {
        $custom_template = $this->plugin_path . 'templates/' . basename($template);
        
        if (file_exists($custom_template)) {
            return $custom_template;
        }

        return $template;
    }

    /**
     * Load custom WooCommerce templates
     */
    public function load_wc_templates($located, $template_name, $args, $template_path, $default_path) {
        $custom_template = $this->plugin_path . 'templates/woocommerce/' . $template_name;

        if (file_exists($custom_template)) {
            return $custom_template;
        }

        return $located;
    }

    /**
     * Get plugin version
     */
    public function get_version() {
        return self::VERSION;
    }

    /**
     * Get plugin path
     */
    public function get_plugin_path() {
        return $this->plugin_path;
    }

    /**
     * Get plugin URL
     */
    public function get_plugin_url() {
        return $this->plugin_url;
    }
}

/**
 * Initialize the plugin
 */
function ds_theme_customizations() {
    return new DS_Theme_Customizations();
}

// Start the plugin
add_action('plugins_loaded', 'ds_theme_customizations');

/**
 * Global access function
 */
function get_ds_customizations() {
    return ds_theme_customizations();
}

// Enqueue logo overlay assets on homepage only
function opera_enqueue_logo_overlay_assets() {
    // Only load on homepage
    if (is_front_page() || is_home()) {
        
        // Get plugin directory info
        $plugin_path = plugin_dir_path(__FILE__);
        $plugin_url = plugin_dir_url(__FILE__);
        
        // CSS file
        $css_file = $plugin_path . 'assets/logo-overlay.css';
        if (file_exists($css_file)) {
            wp_enqueue_style(
                'opera-logo-overlay',
                $plugin_url . 'assets/logo-overlay.css',
                array(),
                filemtime($css_file)
            );
        }
        
        // JavaScript file
        $js_file = $plugin_path . 'assets/logo-overlay.js';
        if (file_exists($js_file)) {
            wp_enqueue_script(
                'opera-logo-overlay',
                $plugin_url . 'assets/logo-overlay.js',
                array(),
                filemtime($js_file),
                true
            );
            
            // Determine logo path - plugin first, then theme fallback
            $logo_url = null;
            
            // Check plugin images directory first
            if (file_exists($plugin_path . 'images/logo.svg')) {
                $logo_url = $plugin_url . 'images/logo.svg';
            } elseif (file_exists($plugin_path . 'images/logo.png')) {
                $logo_url = $plugin_url . 'images/logo.png';
            } elseif (file_exists($plugin_path . 'images/logo.jpg')) {
                $logo_url = $plugin_url . 'images/logo.jpg';
            } elseif (file_exists($plugin_path . 'images/logo.jpeg')) {
                $logo_url = $plugin_url . 'images/logo.jpeg';
            }
            // Fallback to theme directory
            elseif (file_exists(get_stylesheet_directory() . '/assets/images/logo.svg')) {
                $logo_url = get_stylesheet_directory_uri() . '/assets/images/logo.svg';
            } elseif (file_exists(get_stylesheet_directory() . '/assets/images/logo.png')) {
                $logo_url = get_stylesheet_directory_uri() . '/assets/images/logo.png';
            }
         // Pass the found logo URL and options to JavaScript
            wp_localize_script('opera-logo-overlay', 'operaLogoData', array(
                'logoUrl' => $logo_url,
                'mode' => 'slide-up', // Change to 'center' or 'slide-up' for testing
            ));
        }
    }
}
//add_action('wp_enqueue_scripts', 'opera_enqueue_logo_overlay_assets');

function custom_spotlight_scripts() {
    wp_enqueue_style(
        'custom-spotlight-css',
        plugin_dir_url(__FILE__) . 'assets/spotlight.css',
        array(),
        '1.0'
    );

    wp_enqueue_script(
        'custom-spotlight-js',
        plugin_dir_url(__FILE__) . 'assets/spotlight.js',
        array(),
        '1.0',
        true
    );
}
//add_action('wp_enqueue_scripts', 'custom_spotlight_scripts');
/**
 * Inline script for theme switching (runs immediately to prevent flash)
 */
function ds_theme_init_script() {
    ?>
    <script>
    (function() {
        // Check for saved theme preference or system preference
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        const savedTheme = localStorage.getItem('theme');
        const theme = savedTheme || (prefersDark ? 'dark' : 'light');
        
        // Apply immediately to prevent flash
        document.documentElement.setAttribute('data-theme', theme);
    })();
    </script>
    <?php
}
add_action('wp_head', 'ds_theme_init_script', 1); // Priority 1 = runs early

/**
 * Theme toggle functionality
 */
function ds_theme_toggle_script() {
    ?>
    <script>
    function toggleTheme() {
        const currentTheme = document.documentElement.getAttribute('data-theme');
        const newTheme = currentTheme === 'light' ? 'dark' : 'light';
        
        document.documentElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        
        // Optional: Trigger custom event for other scripts
        document.dispatchEvent(new CustomEvent('themeChanged', { detail: { theme: newTheme } }));
    }
    
    // Optional: Add keyboard shortcut (Ctrl/Cmd + Shift + D)
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'D') {
            e.preventDefault();
            toggleTheme();
        }
    });
    </script>
    <?php
}
add_action('wp_footer', 'ds_theme_toggle_script');

/**
 * Add theme toggle button to header
 * Hook into wp_body_open or your header
 */
function ds_theme_toggle_button() {
    ?>
    <button 
        onclick="toggleTheme()" 
        class="theme-toggle" 
        aria-label="Toggle dark mode"
        title="Toggle theme (Ctrl+Shift+D)">
        <span class="theme-toggle__icon" aria-hidden="true"></span>
        <span class="sr-only">Toggle theme</span>
    </button>
    <?php
}
// Add to header - adjust hook based on your theme structure
add_action('wp_body_open', 'ds_theme_toggle_button');
// OR hook into a specific theme location if available

/**
 * Enqueue Google Fonts
 */
function ds_enqueue_google_fonts() {
    wp_enqueue_style(
        'ds-google-fonts',
        'https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&family=Fraunces:ital,opsz,wght@0,9..144,100..900;1,9..144,100..900&display=swap',
        [],
        null
        
    );
}
add_action('wp_enqueue_scripts', 'ds_enqueue_google_fonts');

function ds_google_fonts_preconnect() {
    echo '<link rel="preconnect" href="https://fonts.googleapis.com">';
    echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>';
}
add_action('wp_head', 'ds_google_fonts_preconnect', 1);

/**
 * Register custom block styles for Group block
 * Add this to ds-theme-customizations.php
 */

/**
 * Register block styles for sections
 */
function ds_register_block_styles() {
    // Standard section style
    register_block_style(
        'core/group',
        [
            'name' => 'section-standard',
            'label' => __('Standard Section', 'ds-customizations'),
        ]
    );
    
    // Alternate background section style
    register_block_style(
        'core/group',
        [
            'name' => 'section-alt',
            'label' => __('Alternate Section', 'ds-customizations'),
        ]
    );
    
    // Bleed section style (edge-to-edge)
    register_block_style(
        'core/group',
        [
            'name' => 'section-bleed',
            'label' => __('Bleed Section', 'ds-customizations'),
        ]
    );
    
    // Compact section style
    register_block_style(
        'core/group',
        [
            'name' => 'section-compact',
            'label' => __('Compact Section', 'ds-customizations'),
        ]
    );
}
add_action('init', 'ds_register_block_styles');

/**
 * Register editor-specific styles so they load correctly inside the
 * block editor's iframe.
 *
 * WP 5.9+ renders the editor canvas in an iframe. wp_enqueue_style() on
 * enqueue_block_editor_assets attaches the stylesheet to the admin <head>
 * instead of the iframe, which is why the console was warning
 * "ds-editor-styles-css was added to the iframe incorrectly" and drag/drop
 * was misbehaving. add_editor_style() is the WP-native way to get a
 * stylesheet into the iframe itself, and it accepts an absolute URL so it
 * works fine called from a plugin (not just a theme's functions.php).
 */
function ds_add_editor_styles() {
    $editor_css_path = plugin_dir_path(__FILE__) . 'assets/editor-style.css';

    if (file_exists($editor_css_path)) {
        add_editor_style(plugin_dir_url(__FILE__) . 'assets/editor-style.css?ver=' . filemtime($editor_css_path));
    }
}
add_action('after_setup_theme', 'ds_add_editor_styles');

/**
 * Accent text shortcode for highlighted words in headings
 * Usage: [accent]Word[/accent] ex: Our Latest [accent]News[/accent]
 * Output: <span class="text-accent">Word</span>
 */
function ds_accent_shortcode( $atts, $content = null ) {
    if ( empty( $content ) ) {
        return '';
    }
    return '<span class="text-accent">' . do_shortcode( $content ) . '</span>';
}
add_shortcode( 'accent', 'ds_accent_shortcode' );
