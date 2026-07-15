<?php
/**
 * Location Grid Shortcode
 * 
 * Displays a grid of locations with featured images
 * Shortcode: [ds_location_grid]
 * 
 * @package DS_Location_Manager
 */

if (!defined('ABSPATH')) {
    exit;
}

class DS_Location_Grid_Shortcode {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_shortcode('ds_location_grid', array($this, 'render_shortcode'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
    }
    
    /**
     * Enqueue CSS and JS
     */
    public function enqueue_assets() {
        wp_enqueue_style(
            'ds-location-grid',
            plugin_dir_url(__FILE__) . '../assets/location-grid.css',
            array(),
            filemtime(plugin_dir_path(__FILE__) . '../assets/location-grid.css')
        );
    }
    
    /**
     * Render shortcode
     */
    public function render_shortcode($atts) {
        $atts = shortcode_atts(array(
            'limit' => 3,
            'order' => 'menu_order',
            'orderby' => 'menu_order',
            'show_link' => 'true',
            'link_text' => 'See more Locations',
            'link_url' => '', // Auto-generate if empty
            'heading' => 'Find a Program',
            'heading_word_split' => '2', // Which word gets accent color (0 = none)
            'subtitle' => 'We\'re proud to serve communities across the country'
        ), $atts);
        
        // Build query
        $query_args = array(
            'post_type' => 'ds_location',
            'posts_per_page' => intval($atts['limit']),
            'orderby' => $atts['orderby'],
            'order' => strtoupper($atts['order']),
            'post_status' => 'publish'
        );
        
        $locations = new WP_Query($query_args);
        
        if (!$locations->have_posts()) {
            return '';
        }
        
        // Auto-generate link URL if not provided
        if (empty($atts['link_url'])) {
            $post_type_obj = get_post_type_object('ds_location');
            if ($post_type_obj && $post_type_obj->has_archive) {
                $atts['link_url'] = get_post_type_archive_link('ds_location');
            }
        }
        
        // Start output buffering
        ob_start();
        
        include plugin_dir_path(__FILE__) . '../templates/location-grid.php';
        
        wp_reset_postdata();
        
        return ob_get_clean();
    }
    
    /**
     * Get location meta data
     */
    public static function get_location_data($post_id) {
        return array(
            'name' => get_post_meta($post_id, 'ds_location_name', true) ?: get_the_title($post_id),
            'city' => get_post_meta($post_id, 'ds_location_city', true),
            'state' => get_post_meta($post_id, 'ds_location_state', true),
            'logo' => get_post_meta($post_id, 'ds_location_logo', true),
            'featured_image' => get_the_post_thumbnail_url($post_id, 'large')
        );
    }
}

// Initialize
new DS_Location_Grid_Shortcode();
